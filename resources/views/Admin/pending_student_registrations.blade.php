@php
    $user_type = $user_type ?? Session::get('user_type');
@endphp
@if($user_type == 'Admin')
    @include('includes.Admin_nav')
@elseif($user_type == 'Staff')
    @include('includes.staff_nav')
@else
    @include('includes.Admin_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .table, .form-control { font-family: "Century Gothic", Arial, sans-serif; }
    .sx-pending-wrap { padding: 1rem 1.25rem 2rem; }
    .sx-pending-hero {
        background: linear-gradient(135deg, #940000 0%, #6b0000 100%);
        color: #fff; padding: 1.25rem 1.5rem; margin-bottom: 1rem;
    }
    .sx-pending-hero h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 0.35rem; }
    .sx-pending-hero p { margin: 0; opacity: 0.9; font-size: 0.92rem; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; border-radius: 0 !important; }
    .btn-primary-custom:hover { background: #b30000; border-color: #b30000; color: #fff; }
    .bg-primary-custom { background: #940000 !important; }
    .card, .btn, .form-control, .modal-content { border-radius: 0 !important; }
    .stat-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem; }
    .stat-box { background: #fff; border: 1px solid #eee; padding: 12px 16px; min-width: 140px; }
    .stat-box .label { font-size: 0.75rem; color: #777; text-transform: uppercase; font-weight: 700; }
    .stat-box .value { font-size: 1.4rem; font-weight: 800; color: #940000; }
    .info-banner {
        background: #fff8f0; border: 1px solid #ffe0a3; padding: 12px 14px; margin-bottom: 1rem; font-size: 0.9rem;
    }
</style>

<div class="sx-pending-wrap">
    <div class="sx-pending-hero">
        <h1><i class="fa fa-user-plus"></i> Pending Student Registrations</h1>
        <p>Pre-register students for the next intake. They stay separate until you Confirm &amp; Enroll.</p>
    </div>

    <div class="info-banner">
        <strong><i class="fa fa-info-circle"></i> Important:</strong>
        Pending records are <strong>not enrolled students</strong>. They do not affect current class sizes or official student counts.
        When the term/intake starts, select them and use <strong>Confirm &amp; Enroll</strong>.
    </div>

    <div class="stat-row">
        <div class="stat-box">
            <div class="label">Pending</div>
            <div class="value">{{ $pendingCount }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Confirmed</div>
            <div class="value">{{ $confirmedCount }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total listed</div>
            <div class="value">{{ $pending->count() }}</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-list"></i> Prospective Students
            </h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.pending_students.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-download"></i> Excel Template
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="openImportBtn">
                    <i class="fa fa-upload"></i> Import Excel
                </button>
                <button type="button" class="btn btn-primary-custom btn-sm" id="openAddBtn">
                    <i class="fa fa-plus"></i> Add Manually
                </button>
                <button type="button" class="btn btn-success btn-sm" id="openBatchConfirmBtn">
                    <i class="fa fa-check-double"></i> Confirm Selected
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="pendingTable">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th style="width:36px;"><input type="checkbox" id="selectAllPending"></th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Previous Level</th>
                            <th>Intended Class</th>
                            <th>Parent / Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pending as $i => $row)
                            <tr>
                                <td>
                                    @if($row->status === 'Pending')
                                        <input type="checkbox" class="pending-check" value="{{ $row->pendingID }}">
                                    @endif
                                </td>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $row->full_name }}</strong>
                                    @if($row->previous_school)
                                        <div class="small text-muted">{{ $row->previous_school }}</div>
                                    @endif
                                </td>
                                <td>{{ $row->gender ?: '—' }}</td>
                                <td>{{ $row->previous_level ?: '—' }}</td>
                                <td class="small">
                                    @if($row->intendedClass)
                                        {{ $row->intendedClass->class_name }}
                                        @if($row->intendedSubclass)
                                            {{ $row->intendedSubclass->subclass_name }}
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">
                                    {{ trim(($row->parent_first_name ?? '') . ' ' . ($row->parent_last_name ?? '')) ?: '—' }}
                                    @if($row->parent_phone)
                                        <br>{{ $row->parent_phone }}
                                    @endif
                                </td>
                                <td>
                                    @if($row->status === 'Pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($row->status === 'Confirmed')
                                        <span class="badge bg-success">Confirmed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $row->status }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($row->status === 'Pending')
                                        <button type="button" class="btn btn-sm btn-success confirm-one-btn"
                                            data-id="{{ $row->pendingID }}"
                                            data-name="{{ $row->full_name }}"
                                            data-subclass="{{ $row->intended_subclassID }}">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $row->pendingID }}"
                                            data-first="{{ $row->first_name }}"
                                            data-middle="{{ $row->middle_name }}"
                                            data-last="{{ $row->last_name }}"
                                            data-gender="{{ $row->gender }}"
                                            data-dob="{{ $row->date_of_birth ? $row->date_of_birth->format('Y-m-d') : '' }}"
                                            data-level="{{ $row->previous_level }}"
                                            data-school="{{ $row->previous_school }}"
                                            data-class="{{ $row->intended_classID }}"
                                            data-subclass="{{ $row->intended_subclassID }}"
                                            data-year="{{ $row->intended_academic_yearID }}"
                                            data-pfirst="{{ $row->parent_first_name }}"
                                            data-pmiddle="{{ $row->parent_middle_name }}"
                                            data-plast="{{ $row->parent_last_name }}"
                                            data-pgender="{{ $row->parent_gender }}"
                                            data-pphone="{{ $row->parent_phone }}"
                                            data-pemail="{{ $row->parent_email }}"
                                            data-prel="{{ $row->parent_relationship }}"
                                            data-phone="{{ $row->phone }}"
                                            data-notes="{{ $row->notes }}">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $row->pendingID }}"
                                            data-name="{{ $row->full_name }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="small text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No pending registrations yet. Download the Excel template or add manually.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Modal --}}
<div class="modal fade" id="pendingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="pendingModalTitle">Add Pending Student</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="pendingForm">
                @csrf
                <input type="hidden" name="pendingID" id="pendingID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" id="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" id="middle_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" id="last_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Gender</label>
                            <select class="form-control" name="gender" id="gender">
                                <option value="">—</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" id="date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Previous Level</label>
                            <select class="form-control" name="previous_level" id="previous_level">
                                <option value="">—</option>
                                <option value="Std 7">Std 7</option>
                                <option value="Form 4">Form 4</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Previous School</label>
                            <input type="text" class="form-control" name="previous_school" id="previous_school">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Intended Class</label>
                            <select class="form-control" name="intended_classID" id="intended_classID">
                                <option value="">—</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->classID }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Intended Stream</label>
                            <select class="form-control" name="intended_subclassID" id="intended_subclassID">
                                <option value="">—</option>
                            </select>
                        </div>
                        <div class="col-12"><hr><strong>Parent / Guardian</strong></div>
                        <div class="col-md-4 mb-3">
                            <label>Parent First Name</label>
                            <input type="text" class="form-control" name="parent_first_name" id="parent_first_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Parent Last Name</label>
                            <input type="text" class="form-control" name="parent_last_name" id="parent_last_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Parent Gender</label>
                            <select class="form-control" name="parent_gender" id="parent_gender">
                                <option value="">—</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Parent Phone</label>
                            <input type="text" class="form-control" name="parent_phone" id="parent_phone">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Parent Email</label>
                            <input type="email" class="form-control" name="parent_email" id="parent_email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Relationship</label>
                            <input type="text" class="form-control" name="parent_relationship" id="parent_relationship" placeholder="Mother / Father / Guardian">
                        </div>
                        <div class="col-12 mb-2">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="pendingSubmitBtn">Save Pending</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Import Excel</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted">Use the official template. Imported rows stay <strong>Pending</strong> and do not enroll students.</p>
                    <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Confirm &amp; Enroll</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="confirmForm">
                @csrf
                <input type="hidden" id="confirmPendingID">
                <input type="hidden" id="confirmMode" value="one">
                <div class="modal-body">
                    <p id="confirmText" class="mb-3"></p>
                    <label>Enroll into stream <span class="text-danger">*</span></label>
                    <select class="form-control" id="confirmSubclassID" required>
                        <option value="">— Select class / stream —</option>
                        @foreach($classes as $c)
                            @foreach($c->subclasses as $s)
                                <option value="{{ $s->subclassID }}">
                                    {{ $c->class_name }} {{ $s->subclass_name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    <p class="small text-muted mt-2 mb-0">This creates a real student record and Active enrollment.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm &amp; Enroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const subclassesByClass = @json($classes->mapWithKeys(function ($c) {
        return [$c->classID => $c->subclasses->map(fn ($s) => [
            'id' => $s->subclassID,
            'name' => $s->subclass_name,
        ])->values()];
    }));

    function openModal(el) {
        if (window.jQuery && jQuery.fn.modal) jQuery(el).modal('show');
    }
    function closeModal(el) {
        if (window.jQuery && jQuery.fn.modal) jQuery(el).modal('hide');
    }
    function headers() {
        return { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
    }
    function errMsg(data, fb) {
        if (!data) return fb;
        if (data.message) return data.message;
        if (data.errors) { try { return Object.values(data.errors).flat().join('\n'); } catch (e) {} }
        return fb;
    }

    function fillSubclasses(classId, selected) {
        const sel = document.getElementById('intended_subclassID');
        sel.innerHTML = '<option value="">—</option>';
        const list = subclassesByClass[classId] || [];
        list.forEach(function (s) {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name || '(default)';
            if (selected && String(selected) === String(s.id)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    document.getElementById('intended_classID').addEventListener('change', function () {
        fillSubclasses(this.value, null);
    });

    document.getElementById('selectAllPending').addEventListener('change', function () {
        document.querySelectorAll('.pending-check').forEach(function (cb) { cb.checked = document.getElementById('selectAllPending').checked; });
    });

    function resetForm() {
        document.getElementById('pendingForm').reset();
        document.getElementById('pendingID').value = '';
        fillSubclasses('', null);
        document.getElementById('pendingModalTitle').textContent = 'Add Pending Student';
        document.getElementById('pendingSubmitBtn').textContent = 'Save Pending';
    }

    document.getElementById('openAddBtn').addEventListener('click', function () {
        resetForm();
        openModal(document.getElementById('pendingModal'));
    });

    document.getElementById('openImportBtn').addEventListener('click', function () {
        document.getElementById('importForm').reset();
        openModal(document.getElementById('importModal'));
    });

    document.querySelectorAll('.edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('pendingID').value = btn.dataset.id || '';
            document.getElementById('first_name').value = btn.dataset.first || '';
            document.getElementById('middle_name').value = btn.dataset.middle || '';
            document.getElementById('last_name').value = btn.dataset.last || '';
            document.getElementById('gender').value = btn.dataset.gender || '';
            document.getElementById('date_of_birth').value = btn.dataset.dob || '';
            document.getElementById('previous_level').value = btn.dataset.level || '';
            document.getElementById('previous_school').value = btn.dataset.school || '';
            document.getElementById('intended_classID').value = btn.dataset.class || '';
            fillSubclasses(btn.dataset.class || '', btn.dataset.subclass || '');
            document.getElementById('parent_first_name').value = btn.dataset.pfirst || '';
            document.getElementById('parent_last_name').value = btn.dataset.plast || '';
            document.getElementById('parent_gender').value = btn.dataset.pgender || '';
            document.getElementById('parent_phone').value = btn.dataset.pphone || '';
            document.getElementById('parent_email').value = btn.dataset.pemail || '';
            document.getElementById('parent_relationship').value = btn.dataset.prel || '';
            document.getElementById('notes').value = btn.dataset.notes || '';
            document.getElementById('pendingModalTitle').textContent = 'Edit Pending Student';
            document.getElementById('pendingSubmitBtn').textContent = 'Update';
            openModal(document.getElementById('pendingModal'));
        });
    });

    document.getElementById('pendingForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('pendingID').value;
        const url = id
            ? @json(route('admin.pending_students.update'))
            : @json(route('admin.pending_students.store'));
        fetch(url, { method: 'POST', headers: headers(), body: new FormData(this), credentials: 'same-origin' })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(res => {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', errMsg(res.data, 'Save failed'), 'error');
                    return;
                }
                closeModal(document.getElementById('pendingModal'));
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(() => location.reload());
            })
            .catch(() => Swal.fire('Error', 'Network error', 'error'));
    });

    document.getElementById('importForm').addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(@json(route('admin.pending_students.import')), {
            method: 'POST', headers: headers(), body: new FormData(this), credentials: 'same-origin'
        })
        .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
        .then(res => {
            if (!res.ok || !res.data.success) {
                Swal.fire('Error', errMsg(res.data, 'Import failed'), 'error');
                return;
            }
            let extra = '';
            if (res.data.warnings && res.data.warnings.length) {
                extra = '\n\nWarnings:\n' + res.data.warnings.slice(0, 8).join('\n');
            }
            closeModal(document.getElementById('importModal'));
            Swal.fire('Imported', res.data.message + extra, 'success').then(() => location.reload());
        })
        .catch(() => Swal.fire('Error', 'Network error', 'error'));
    });

    function openConfirm(mode, id, name, subclass) {
        document.getElementById('confirmMode').value = mode;
        document.getElementById('confirmPendingID').value = id || '';
        document.getElementById('confirmSubclassID').value = subclass || '';
        document.getElementById('confirmText').textContent = mode === 'batch'
            ? 'Confirm & enroll all selected pending students into one stream.'
            : ('Confirm & enroll: ' + (name || 'student'));
        openModal(document.getElementById('confirmModal'));
    }

    document.querySelectorAll('.confirm-one-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openConfirm('one', btn.dataset.id, btn.dataset.name, btn.dataset.subclass);
        });
    });

    document.getElementById('openBatchConfirmBtn').addEventListener('click', function () {
        const ids = Array.from(document.querySelectorAll('.pending-check:checked')).map(cb => cb.value);
        if (!ids.length) {
            Swal.fire('Select students', 'Tick one or more pending students first.', 'info');
            return;
        }
        openConfirm('batch', ids.join(','), null, null);
        document.getElementById('confirmPendingID').value = ids.join(',');
    });

    document.getElementById('confirmForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const mode = document.getElementById('confirmMode').value;
        const subclassID = document.getElementById('confirmSubclassID').value;
        if (!subclassID) {
            Swal.fire('Required', 'Select a class/stream.', 'warning');
            return;
        }
        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('subclassID', subclassID);
        let url;
        if (mode === 'batch') {
            url = @json(route('admin.pending_students.confirm_batch'));
            document.getElementById('confirmPendingID').value.split(',').filter(Boolean).forEach(function (id) {
                fd.append('pendingIDs[]', id);
            });
        } else {
            url = @json(route('admin.pending_students.confirm'));
            fd.append('pendingID', document.getElementById('confirmPendingID').value);
        }
        fetch(url, { method: 'POST', headers: headers(), body: fd, credentials: 'same-origin' })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(res => {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', errMsg(res.data, 'Confirm failed'), 'error');
                    return;
                }
                closeModal(document.getElementById('confirmModal'));
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1600, showConfirmButton: false })
                    .then(() => location.reload());
            })
            .catch(() => Swal.fire('Error', 'Network error', 'error'));
    });

    document.querySelectorAll('.delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Remove pending record?',
                text: btn.dataset.name || '',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, remove'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('pendingID', btn.dataset.id || '');
                fetch(@json(route('admin.pending_students.delete')), {
                    method: 'POST', headers: headers(), body: fd, credentials: 'same-origin'
                })
                .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
                .then(res => {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', errMsg(res.data, 'Delete failed'), 'error');
                        return;
                    }
                    location.reload();
                });
            });
        });
    });
})();
</script>
