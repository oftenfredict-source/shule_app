@extends('layouts.parent_corp')

@section('title', 'Ruksa za Mwanafunzi - ShuleXpert')

@section('content')
<style>
    .selection-card {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .permission-tabs .nav-link {
        border-radius: 0 !important;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        color: #777;
        padding: 12px 20px;
    }
    .permission-tabs .nav-link.active {
        background: #fff5f5 !important;
        color: #940000 !important;
        border-color: #940000 !important;
        border-bottom: 3px solid #940000 !important;
    }
    .tab-content-area {
        background: white;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .form-control {
        border: 1px solid #ced4da !important;
        padding: 12px 15px !important;
        font-size: 14px !important;
        background-color: #fff !important;
    }
    .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 4px rgba(148, 0, 0, 0.05) !important;
    }
    .form-label {
        color: #444 !important;
        margin-bottom: 8px !important;
    }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-check-shield"></i> Ruksa za Mwanafunzi</div>
    </div>

    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-transparent p-0">
                <ul class="nav nav-tabs permission-tabs border-0 bg-white shadow-sm">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'request']) }}">Omba Ruksa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'pending']) }}">Inasubiri
                            @if($unreadPendingCount > 0) <span class="badge bg-danger ms-1" style="border-radius:0;">{{ $unreadPendingCount }}</span> @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'approved']) }}">Zimekubaliwa
                            @if($unreadApprovedCount > 0) <span class="badge bg-success ms-1" style="border-radius:0;">{{ $unreadApprovedCount }}</span> @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'rejected']) }}">Zimekataliwa
                            @if($unreadRejectedCount > 0) <span class="badge bg-secondary ms-1" style="border-radius:0;">{{ $unreadRejectedCount }}</span> @endif
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0 mt-4 tab-content-area">
                @if($activeTab !== 'request')
                <div class="form-loading" id="parentFilterLoading">
                    <span><i class="bx bx-loader-alt bx-spin text-danger"></i> Inachuja...</span>
                    <div class="form-progress"></div>
                </div>
                <div class="row mb-3 px-3">
                    <div class="col-md-12">
                        <form id="parentFilterForm" method="GET" action="{{ route('parent.permissions') }}" class="p-3 bg-light border shadow-sm">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label small font-weight-bold text-uppercase">Chuja Kwa:</label>
                                    <select name="filter_type" id="filter_type" class="form-control" onchange="toggleFilterInputs()">
                                        <option value="all" {{ ($filterType ?? '') == 'all' ? 'selected' : '' }}>Rekodi Zote</option>
                                        <option value="range" {{ ($filterType ?? '') == 'range' ? 'selected' : '' }}>Muda Maalum</option>
                                        <option value="month" {{ ($filterType ?? '') == 'month' ? 'selected' : '' }}>Mwezi Maalum</option>
                                        <option value="year" {{ ($filterType ?? '') == 'year' ? 'selected' : '' }}>Mwaka Maalum</option>
                                    </select>
                                </div>

                                <div id="range_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'range' ? 'flex' : 'none' }}; gap: 10px;">
                                    <div class="flex-grow-1">
                                        <label class="form-label small font-weight-bold text-uppercase">Kuanzia:</label>
                                        <input type="date" name="date_from" id="filterDateFrom" class="form-control" value="{{ $dateFrom ?? '' }}">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label small font-weight-bold text-uppercase">Hadi:</label>
                                        <input type="date" name="date_to" id="filterDateTo" class="form-control" value="{{ $dateTo ?? '' }}">
                                    </div>
                                </div>

                                <div id="month_inputs" class="col-md-6 filter-input-group" style="display: {{ ($filterType ?? '') == 'month' ? 'flex' : 'none' }}; gap: 10px;">
                                    <div class="flex-grow-1">
                                        <label class="form-label small font-weight-bold text-uppercase">Mwezi:</label>
                                        <select name="filter_month" class="form-control">
                                            @foreach(['Januari', 'Februari', 'Machi', 'Aprili', 'Mei', 'Juni', 'Julai', 'Agosti', 'Septemba', 'Oktoba', 'Novemba', 'Desemba'] as $index => $monthName)
                                                <option value="{{ $index + 1 }}" {{ ($filterMonth ?? date('n')) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label small font-weight-bold text-uppercase">Mwaka:</label>
                                        <select name="filter_month_year" class="form-control">
                                            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                                <option value="{{ $y }}" {{ ($filterMonthYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div id="year_inputs" class="col-md-3 filter-input-group" style="display: {{ ($filterType ?? '') == 'year' ? 'block' : 'none' }};">
                                    <label class="form-label small font-weight-bold text-uppercase">Year:</label>
                                    <select name="filter_year" class="form-control">
                                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                            <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <button class="btn btn-outline-danger w-100 mb-1" style="border-radius: 0; font-weight: bold;" type="submit"><i class="bx bx-filter"></i> CHUJA</button>
                                    <button class="btn btn-secondary w-100" style="border-radius: 0; font-weight: bold;" type="button" onclick="resetParentFilters()"><i class="bx bx-undo"></i> SAFISHA</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($activeTab === 'request')
                    @isset($editPermission)
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 px-3" style="border-radius:0; border-left: 4px solid #ff9800;">
                        <i class="bx bx-edit text-warning me-2" style="font-size:20px;"></i>
                        <div><strong>Unahariri ombi lililokataliwa</strong> &mdash; Fanya mabadiliko hapa chini kisha bonyeza <em>Sasisha na Tuma Tena</em>.</div>
                        <a href="{{ url()->current() }}?tab=request" class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:0;"><i class="bx bx-x"></i> Ghairi</a>
                    </div>
                    @endisset
                    @php
                        $editStudentId = isset($editPermission) ? ($editPermission->student_id ?? $editPermission->studentID ?? '') : '';
                        $editReasonRaw = strtolower($editPermission->reason_type ?? '');
                        $editReason = in_array($editReasonRaw, ['sick', 'medical'], true) ? 'sick' : 'permission';
                    @endphp
                    <h6 class="font-weight-bold mb-4" style="color:#940000; border-left:4px solid #940000; padding-left:15px;">{{ isset($editPermission) ? 'HARIRI NA TUMA TENA RUKSA' : 'OMBA RUKSA MPYA' }}</h6>
                    <form method="POST" action="{{ route('parent.permissions.store') }}" enctype="multipart/form-data" id="parentPermissionForm">
                        @csrf
                        @isset($editPermission)
                        <input type="hidden" name="permission_id" value="{{ $editPermission->permissionID ?? $editPermission->id }}">
                        @endisset
                        <div class="row g-3">
                            <div class="col-md-12 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Mwanafunzi</label>
                                <select class="form-control" name="student_id" required>
                                    <option value="">-- Chagua Mtoto --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->studentID }}" {{ (string)$editStudentId === (string)$student->studentID ? 'selected' : '' }}>{{ $student->first_name }} {{ $student->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Tarehe ya Kuondoka</label>
                                <input type="date" class="form-control" name="start_date" id="parentStartDate"
                                    value="{{ isset($editPermission) && $editPermission->start_date ? \Carbon\Carbon::parse($editPermission->start_date)->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Tarehe ya Kurudi</label>
                                <input type="date" class="form-control" name="end_date" id="parentEndDate"
                                    value="{{ isset($editPermission) && $editPermission->end_date ? \Carbon\Carbon::parse($editPermission->end_date)->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small font-weight-bold text-uppercase">Jumla ya Siku za Kazi (Bila Wikendi)</label>
                                <input type="text" class="form-control" id="parentDaysDisplay" readonly style="background-color: #f8f9fa; font-weight: bold; color: #940000;">
                                <small class="text-danger d-none" id="parentDaysError" style="font-weight: bold;">Siku zimezidi kikomo. Ruksa isizidi siku 7 za kazi.</small>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Aina ya Ruksa</label>
                                <select class="form-control" name="reason_type" id="parentReasonType" required>
                                    <option value="">-- Chagua Aina --</option>
                                    <option value="permission" {{ isset($editPermission) && $editReason === 'permission' ? 'selected' : '' }}>Ruhusa</option>
                                    <option value="sick" {{ isset($editPermission) && $editReason === 'sick' ? 'selected' : '' }}>Mgonjwa</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2" id="medicalAttachmentField">
                                <label class="form-label small font-weight-bold text-uppercase">Kiambatanisho (Hiari, PDF/Picha)</label>
                                <input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Unaweza kuambatisha barua ya daktari au nyaraka nyingine inayounga mkono ombi lako.</small>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label small font-weight-bold text-uppercase">Maelezo ya Kina</label>
                                <textarea class="form-control" name="reason_description" rows="4" placeholder="Eleza kwa ufupi sababu ya kuomba ruksa..." required>{{ isset($editPermission) ? $editPermission->reason_description : '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary px-5" type="submit" id="parentPermissionSubmit">
                                    @isset($editPermission)
                                        <i class="bx bx-refresh"></i> SASISHA NA TUMA TENA
                                    @else
                                        <i class="bx bx-paper-plane"></i> TUMA OMBI
                                    @endisset
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    @include('Parent.partials.permission_table', ['permissions' => ($activeTab==='pending'?$pendingPermissions:($activeTab==='approved'?$approvedPermissions:$rejectedPermissions)), 'studentNames' => $studentNames])
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.permission_progress_modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        function initPermViewToggle() {
            var isMobile = window.innerWidth <= 991;
            var storageKey = 'parent_perm_view_pref';
            var savedView = sessionStorage.getItem(storageKey);
            var activeView = savedView || (isMobile ? 'grid' : 'list');

            function applyView(view) {
                var listEl = document.getElementById('perm-list-view');
                var gridEl = document.getElementById('perm-grid-view');
                var btns = document.querySelectorAll('.perm-view-btn');
                if (!listEl || !gridEl) return;
                listEl.style.display = view === 'list' ? 'block' : 'none';
                gridEl.style.display = view === 'grid' ? 'block' : 'none';
                btns.forEach(function(btn) {
                    var isActive = btn.getAttribute('data-view') === view;
                    btn.classList.toggle('active', isActive);
                    btn.classList.toggle('btn-secondary', isActive);
                    btn.classList.toggle('btn-outline-secondary', !isActive);
                });
                sessionStorage.setItem(storageKey, view);
            }

            applyView(activeView);

            document.querySelectorAll('.perm-view-btn').forEach(function(btn) {
                btn.removeEventListener('click', btn._permViewHandler);
                btn._permViewHandler = function() { applyView(this.getAttribute('data-view')); };
                btn.addEventListener('click', btn._permViewHandler);
            });
        }

        initPermViewToggle();

        const startDateInput = $('#parentStartDate');
        const endDateInput = $('#parentEndDate');
        const daysDisplay = $('#parentDaysDisplay');
        const daysError = $('#parentDaysError');

        function calculateParentDays() {
            const startVal = startDateInput.val();
            const endVal = endDateInput.val();
            if (!startVal || !endVal) {
                daysDisplay.val('');
                daysError.addClass('d-none');
                return;
            }

            const start = new Date(startVal);
            const end = new Date(endVal);
            
            let count = 0;
            let current = new Date(start);
            current.setHours(0, 0, 0, 0);
            let endCopy = new Date(end);
            endCopy.setHours(0, 0, 0, 0);

            while (current <= endCopy) {
                const dayOfWeek = current.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // 0 = Sunday, 6 = Saturday
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }

            daysDisplay.val(count > 0 ? count : '');
            if (count > 7) {
                daysError.removeClass('d-none');
            } else {
                daysError.addClass('d-none');
            }
        }

        startDateInput.on('change', calculateParentDays);
        endDateInput.on('change', calculateParentDays);
        // Recalculate on load if dates are pre-filled (edit mode)
        if (startDateInput.val() && endDateInput.val()) {
            calculateParentDays();
        }

        $('#parentReasonType').on('change', function() {
            // Kiambatanisho ni hiari kwa aina zote mbili
        });

        $('#parentPermissionForm').on('submit', function(e) {
            const daysCount = parseInt(daysDisplay.val() || '0', 10);
            if (daysCount > 7) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Kikomo Kimezidi', text: 'Siku zimezidi kikomo. Ruksa isizidi siku 7 za kazi.', confirmButtonColor: '#940000' });
                return;
            }

            e.preventDefault();
            const btn = $('#parentPermissionSubmit');
            const defaultHtml = btn.data('default-html') || btn.html();
            btn.data('default-html', defaultHtml);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Inatuma...');
            
            let formData = new FormData(this);
            formData.append('days_count', daysCount);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Imetumwa!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Hitilafu', res.message, 'error');
                        btn.prop('disabled', false).html(defaultHtml);
                    }
                },
                error: function(xhr) {
                    let msg = 'Imeshindikana kutuma ombi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire('Hitilafu', msg, 'error');
                    btn.prop('disabled', false).html(defaultHtml);
                }
            });
        });

        const parentFilterForm = $('#parentFilterForm');
        if (parentFilterForm.length) {
            parentFilterForm.on('submit', function() {
                const loadingBar = document.getElementById('parentFilterLoading');
                if (loadingBar) loadingBar.style.display = 'flex';
                const btn = $(this).find('button[type="submit"]');
                btn.html('<i class="bx bx-loader-alt bx-spin"></i> INACHUJA...');
                btn.prop('disabled', true);
            });
        }

        window.toggleFilterInputs = function() {
            const type = $('#filter_type').val();
            $('.filter-input-group').hide();
            if (type === 'range') {
                $('#range_inputs').css('display', 'flex');
            } else if (type === 'month') {
                $('#month_inputs').css('display', 'flex');
            } else if (type === 'year') {
                $('#year_inputs').show();
            }
        };

        window.resetParentFilters = function() {
            window.location.href = "{{ route('parent.permissions') }}?tab={{ $activeTab }}";
        };

        // Fix for dropdowns being cut off inside table-responsive
        $(document).on('show.bs.dropdown', '.table-responsive', function () {
            $(this).css('overflow', 'inherit');
        });

        $(document).on('hide.bs.dropdown', '.table-responsive', function () {
            $(this).css('overflow', 'auto');
        });
    });
</script>
@endpush

@include('partials.permission_details_modal')
