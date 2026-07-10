@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root { --primary-red: #940000; }
        .card, .btn, .form-control, .form-select, .alert { border-radius: 0 !important; }
        .select2-container--default .select2-selection--single {
            height: 42px;
            border-radius: 0 !important;
            border: 1px solid #ced4da;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border-radius: 0;
            padding: 8px 10px;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: var(--primary-red);
        }
        .class-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border: 1px solid #eee;
            margin-bottom: 8px;
            background: #fff;
        }
        .class-toggle-row:hover { background: #fffafa; }
        .class-toggle-row .form-check-input:checked {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }
        #classesPanel { border-left: 3px solid var(--primary-red); background: #fffafafa; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-red);">Parent Fingerprint Attendance Portal</h4>
            <p class="text-muted small mb-0">Ruhusu wazazi kuona mahudhurio ya alama za vidole kwa kila darasa la shule</p>
        </div>
        <a href="{{ route('superAdminDashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-grid-alt me-1"></i> Dashboard
        </a>
    </div>

    <div class="card border mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="small fw-bold text-muted mb-2">CHAGUA SHULE</label>
                    <select id="schoolID" class="form-control">
                        <option value="">-- Tafuta Shule --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}"
                                data-environment="{{ $school->environment ?? '' }}"
                                data-reg="{{ $school->registration_number ?? '' }}">
                                {{ $school->school_name }}
                                @if($school->environment) ({{ $school->environment }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <button type="button" class="btn btn-outline-secondary w-100" id="btnSelectAll" disabled>
                        <i class="bx bx-check-double me-1"></i> Chagua Zote
                    </button>
                </div>
                <div class="col-lg-3">
                    <button type="button" class="btn btn-primary-custom w-100" id="btnSave" disabled>
                        <i class="bx bx-save me-1"></i> Hifadhi
                    </button>
                </div>
            </div>

            <div id="classesPanel" class="mt-4 p-3 d-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bx bx-group"></i> Madarasa — <span id="schoolNameLabel"></span></h6>
                    <span class="badge bg-light text-dark border" id="enabledCountBadge">0 imeruhusiwa</span>
                </div>
                <p class="text-muted small mb-3">
                    Weka tiki kwa madarasa ambayo wazazi wanaweza kuona mahudhurio ya alama za vidole. Madarasa yasiyoruhusiwa yataonekana kwa kivuli kwenye portal ya mzazi.
                </p>
                <div id="classesList"></div>
                <div id="classesEmpty" class="alert alert-warning mb-0 d-none">Hakuna madarasa yaliyosajiliwa kwa shule hii.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    const classesPanel = document.getElementById('classesPanel');
    const classesList = document.getElementById('classesList');
    const classesEmpty = document.getElementById('classesEmpty');
    const schoolNameLabel = document.getElementById('schoolNameLabel');
    const enabledCountBadge = document.getElementById('enabledCountBadge');
    const btnSave = document.getElementById('btnSave');
    const btnSelectAll = document.getElementById('btnSelectAll');

    $('#schoolID').select2({
        width: '100%',
        placeholder: '-- Tafuta Shule --',
        allowClear: true,
        minimumResultsForSearch: 0,
        language: {
            noResults: function () { return 'Hakuna shule iliyopatikana'; },
            searching: function () { return 'Inatafuta...'; },
            inputTooShort: function () { return 'Andika jina la shule...'; }
        },
        matcher: function (params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (!data.id) {
                return null;
            }
            const term = params.term.toLowerCase();
            const text = (data.text || '').toLowerCase();
            const env = ($(data.element).data('environment') || '').toString().toLowerCase();
            const reg = ($(data.element).data('reg') || '').toString().toLowerCase();
            if (text.indexOf(term) > -1 || env.indexOf(term) > -1 || reg.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    function updateEnabledCount() {
        const checked = classesList.querySelectorAll('input[type="checkbox"]:checked').length;
        const total = classesList.querySelectorAll('input[type="checkbox"]').length;
        enabledCountBadge.textContent = checked + ' / ' + total + ' imeruhusiwa';
    }

    function renderClasses(classes) {
        classesList.innerHTML = '';
        if (!classes.length) {
            classesEmpty.classList.remove('d-none');
            btnSelectAll.disabled = true;
            btnSave.disabled = true;
            return;
        }

        classesEmpty.classList.add('d-none');
        btnSelectAll.disabled = false;
        btnSave.disabled = false;

        classes.forEach(function (cls) {
            const row = document.createElement('div');
            row.className = 'class-toggle-row';
            row.innerHTML =
                '<div class="fw-semibold">' + cls.class_name + '</div>' +
                '<div class="form-check form-switch m-0">' +
                '<input class="form-check-input class-enabled-toggle" type="checkbox" role="switch" ' +
                'id="class_' + cls.classID + '" value="' + cls.classID + '"' + (cls.enabled ? ' checked' : '') + '>' +
                '</div>';
            classesList.appendChild(row);
        });

        classesList.querySelectorAll('.class-enabled-toggle').forEach(function (el) {
            el.addEventListener('change', updateEnabledCount);
        });
        updateEnabledCount();
    }

    $('#schoolID').on('change', async function () {
        const schoolId = $(this).val();
        if (!schoolId) {
            classesPanel.classList.add('d-none');
            btnSave.disabled = true;
            btnSelectAll.disabled = true;
            return;
        }

        classesPanel.classList.remove('d-none');
        classesList.innerHTML = '<div class="text-muted small py-3"><i class="bx bx-loader-alt bx-spin"></i> Inapakia...</div>';
        classesEmpty.classList.add('d-none');

        try {
            const res = await fetch('{{ url("super-admin/parent-fingerprint-portal") }}/' + schoolId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Imeshindwa kupakia');

            schoolNameLabel.textContent = data.school_name || '';
            renderClasses(data.classes || []);
        } catch (e) {
            classesList.innerHTML = '<div class="alert alert-danger mb-0">' + (e.message || 'Hitilafu') + '</div>';
            btnSave.disabled = true;
            btnSelectAll.disabled = true;
        }
    });

    btnSelectAll.addEventListener('click', function () {
        const boxes = classesList.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(boxes).every(function (b) { return b.checked; });
        boxes.forEach(function (b) { b.checked = !allChecked; });
        updateEnabledCount();
    });

    btnSave.addEventListener('click', async function () {
        const schoolID = $('#schoolID').val();
        if (!schoolID) return;

        const enabledIds = Array.from(classesList.querySelectorAll('input[type="checkbox"]:checked'))
            .map(function (el) { return parseInt(el.value, 10); });

        btnSave.disabled = true;
        const original = btnSave.innerHTML;
        btnSave.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Inahifadhi...';

        try {
            const res = await fetch('{{ route("superadmin.parent_fingerprint_portal.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ schoolID: parseInt(schoolID, 10), enabled_class_ids: enabledIds })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Imeshindwa kuhifadhi');

            if (window.Swal) {
                Swal.fire({ icon: 'success', title: 'Imehifadhiwa', text: data.message, timer: 2000, showConfirmButton: false });
            } else {
                alert(data.message);
            }
        } catch (e) {
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Hitilafu', text: e.message || 'Imeshindwa kuhifadhi' });
            } else {
                alert(e.message || 'Imeshindwa kuhifadhi');
            }
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = original;
        }
    });
});
</script>
@endpush
