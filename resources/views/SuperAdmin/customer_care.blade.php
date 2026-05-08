@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />

    <style>
        :root { --primary-red: #940000; }
        .school-logo {
            width: 36px; height: 36px; border-radius: 0;
            object-fit: cover; border: 1px solid rgba(148, 0, 0, 0.15); background: #fff;
        }
        .select2-container--default .select2-selection--single {
            height: 42px; border-radius: 0 !important; border: 1px solid #ced4da;
            display: flex; align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-left: 12px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .card, .btn, .form-control, .select2-container, .alert, .badge, .progress { border-radius: 0 !important; }
        .table thead th { background-color: #f8f9fa; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-red);">Customer Care Center</h4>
            <p class="text-muted small mb-0">Direct communication with school administrators and staff</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superAdminDashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-grid-alt me-1"></i> Dashboard</a>
            <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-primary-custom btn-sm"><i class="bx bx-building me-1"></i> Schools</a>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="small fw-bold text-muted mb-2">TARGET INSTITUTION</label>
                    <select id="schoolID" class="form-control">
                        <option value="">-- Search and Select School --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="small fw-bold text-muted mb-2">FILTER ROLE</label>
                    <select id="userType" class="form-select">
                        @foreach($userTypes as $ut)
                            <option value="{{ $ut }}">{{ $ut }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="small fw-bold text-muted mb-2">SENDER ID</label>
                    <select id="senderID" class="form-select">
                        <option value="ShuleXpert">ShuleXpert</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="small fw-bold text-muted mb-2">SMS BROADCAST MESSAGE</label>
                    <textarea id="smsMessage" class="form-control" rows="2" placeholder="Type your message here..."></textarea>
                    <small class="text-muted" style="font-size: 0.75rem;">Placeholders: {username}, {school}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAllUsers">
                    <label class="form-check-label fw-bold small" for="selectAllUsers">SELECT ALL RECIPIENTS</label>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-dark px-3 py-2" id="selectedCount">RECIPIENTS: 0</span>
                    <button type="button" class="btn btn-primary-custom btn-sm px-4" id="btnSendSms" disabled>
                        <i class="bx bx-send me-1"></i> BROADCAST SMS
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px;">
                <table class="table table-hover table-striped mb-0" id="usersTable">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th width="60">LOGO</th>
                            <th>SCHOOL</th>
                            <th>USERNAME</th>
                            <th>ROLE</th>
                            <th>PHONE</th>
                            <th class="text-center" width="150">STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="usersTbody">
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bx bx-info-circle d-block mb-2" style="font-size: 2rem;"></i>
                                Select a school to load users.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="smsProgressArea" class="p-4 border-top bg-light d-none">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold" style="color: var(--primary-red);">Transmission Progress</span>
                    <span id="smsProgressText" class="small fw-bold">0 / 0</span>
                </div>
                <div class="progress" style="height: 12px; background-color: #e9ecef;">
                    <div id="smsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="smsDeliverySummary" class="text-center small mt-2 fw-bold"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    let loadedUsers = [];
    let usersDataTable = null;

    $('#schoolID').select2({ width: '100%', placeholder: '-- Select School --', allowClear: true });

    function safeText(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }

    function updateSelectedCount() {
        const count = $('.user-checkbox:checked').length;
        $('#selectedCount').text('RECIPIENTS: ' + count);
        $('#btnSendSms').prop('disabled', count === 0);
    }

    function initUsersDataTable() {
        if (usersDataTable) { usersDataTable.destroy(); usersDataTable = null; }
        usersDataTable = $('#usersTable').DataTable({ pageLength: 50, ordering: true, columnDefs: [ { orderable: false, targets: [0, 1, 6] } ] });
    }

    async function loadUsers() {
        const schoolID = $('#schoolID').val();
        const userType = $('#userType').val();
        loadedUsers = [];
        if (usersDataTable) { usersDataTable.clear().destroy(); usersDataTable = null; }
        $('#usersTbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>');

        if (!schoolID) {
            $('#usersTbody').html('<tr><td colspan="7" class="text-center py-5 text-muted">Select a school to load users.</td></tr>');
            updateSelectedCount();
            return;
        }

        try {
            const res = await $.ajax({
                url: '{{ route("superadmin.customer_care.users") }}',
                type: 'GET',
                data: { schoolID: schoolID, user_type: userType }
            });

            if (!res.success) { $('#usersTbody').html('<tr><td colspan="7" class="text-center text-danger py-4">Failed to load users.</td></tr>'); return; }

            loadedUsers = res.users || [];
            $('#senderID').html('<option value="ShuleXpert">ShuleXpert</option>');
            if (res.school && res.school.senderID && res.school.senderID.trim() !== '') {
                const sID = res.school.senderID.trim();
                if (sID.toUpperCase() !== 'SHULEXPERT') $('#senderID').append(`<option value="${safeText(sID)}">${safeText(sID)}</option>`);
            }

            if (loadedUsers.length === 0) {
                $('#usersTbody').html('<tr><td colspan="7" class="text-center py-4 text-muted">No users found.</td></tr>');
                updateSelectedCount();
                return;
            }

            const rowsHtml = loadedUsers.map((u, idx) => {
                const checkboxId = 'u_chk_' + idx + '_' + Math.random().toString(36).substr(2,5);
                const phone = (u.phone || '').toString().trim();
                const hasPhone = phone && !['null','undefined','n/a'].includes(phone.toLowerCase());
                return `
                    <tr data-school-id="${safeText(u.schoolID)}" data-user-type="${safeText(u.user_type)}" data-username="${safeText(u.username)}" data-phone="${safeText(phone)}">
                        <td class="text-center"><input type="checkbox" class="form-check-input user-checkbox" id="${checkboxId}" ${hasPhone ? '' : 'disabled'}></td>
                        <td class="text-center">${u.school_logo ? `<img src="${safeText(u.school_logo)}" class="school-logo">` : '<div class="school-logo bg-light text-muted d-flex align-items-center justify-content-center"><i class="bx bx-building"></i></div>'}</td>
                        <td><div class="fw-bold small">${safeText(u.school_name)}</div></td>
                        <td><div class="fw-bold text-primary">${safeText(u.username)}</div></td>
                        <td><span class="badge bg-light text-dark border">${safeText(u.user_type)}</span></td>
                        <td>${hasPhone ? safeText(phone) : '---'}</td>
                        <td class="text-center status-col">${hasPhone ? '<span class="text-muted small">Ready</span>' : '<span class="text-danger small fw-bold">NO PHONE</span>'}</td>
                    </tr>
                `;
            }).join('');
            $('#usersTbody').html(rowsHtml);
            initUsersDataTable();
            updateSelectedCount();
        } catch (e) { $('#usersTbody').html('<tr><td colspan="7" class="text-center text-danger py-4">Error loading data.</td></tr>'); }
    }

    $('#schoolID').on('change', loadUsers);
    $('#userType').on('change', loadUsers);
    $('#selectAllUsers').on('change', function() { $('.user-checkbox:not(:disabled)').prop('checked', $(this).is(':checked')); updateSelectedCount(); });
    $(document).on('change', '.user-checkbox', updateSelectedCount);

    $('#btnSendSms').on('click', async function() {
        const selectedRows = $('.user-checkbox:checked').closest('tr');
        const msgText = $('#smsMessage').val();
        if (!msgText) { Swal.fire({ icon: 'warning', title: 'Message Required' }); return; }

        const confirmed = await Swal.fire({ title: 'Send Broadcast?', text: `Sending to ${selectedRows.length} users.`, icon: 'question', showCancelButton: true });
        if (!confirmed.isConfirmed) return;

        $('#btnSendSms').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Sending...');
        $('#smsProgressArea').removeClass('d-none');
        const total = selectedRows.length; let delivered = 0, failed = 0;

        for (let i = 0; i < total; i++) {
            const row = $(selectedRows[i]);
            const statusCol = row.find('.status-col'); statusCol.html('<div class="spinner-border spinner-border-sm text-primary"></div>');
            try {
                const res = await $.ajax({
                    url: '{{ route("superadmin.customer_care.send_sms") }}', type: 'POST',
                    data: { schoolID: row.data('school-id'), user_type: row.data('user-type'), username: row.data('username'), phone: row.data('phone'), message: msgText, from: $('#senderID').val(), _token: '{{ csrf_token() }}' }
                });
                if (res.success) { statusCol.html('<i class="bx bxs-check-circle text-success" style="font-size:1.2rem;"></i>'); delivered++; }
                else { statusCol.html('<i class="bx bx-error-circle text-danger"></i>'); failed++; }
            } catch (e) { statusCol.html('<i class="bx bx-error-circle text-danger"></i>'); failed++; }
            const current = delivered + failed; const percent = Math.round((current / total) * 100);
            $('#smsProgressBar').css('width', percent + '%'); $('#smsProgressText').text(`${current} / ${total}`);
            $('#smsDeliverySummary').html(`<span class="text-success">${delivered} Success</span> | <span class="text-danger">${failed} Failed</span>`);
        }
        $('#btnSendSms').html('<i class="bx bx-send me-1"></i> BROADCAST SMS');
        Swal.fire({ title: 'Completed', text: `Broadcast finished.`, icon: 'info' });
    });
});
</script>
@endpush
@endsection
