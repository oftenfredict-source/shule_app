@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />

    <style>
        :root { --primary-red: #940000; }
        .select2-container--default .select2-selection--single {
            height: 42px; border-radius: 0 !important; border: 1px solid #ced4da;
            display: flex; align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .kbd-textarea {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #fcfcfc; border: 1px solid #d0d7de; border-radius: 0; padding: 15px; font-size: 0.95rem;
        }
        .kbd-textarea:focus { border-color: var(--primary-red); box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.1); outline: none; }
        .card, .btn, .form-control, .form-select, .alert, .badge, .progress { border-radius: 0 !important; }
        .table thead th { background-color: #f8f9fa; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-red);">System Broadcast Alerts</h4>
            <p class="text-muted small mb-0">Create and manage header notifications for specific institutional roles</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superAdminDashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-grid-alt me-1"></i> Dashboard</a>
            <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-primary-custom btn-sm"><i class="bx bx-building me-1"></i> Schools</a>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bx bx-target-lock me-2 text-primary"></i> 1. SELECT TARGET AUDIENCE</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="small fw-bold text-muted mb-2">INSTITUTION</label>
                    <select id="schoolID" class="form-control">
                        <option value="">-- Search School --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="small fw-bold text-muted mb-2">USER CATEGORY</label>
                    <select id="targetUserType" class="form-select">
                        <option value="">-- Select Category --</option>
                        @foreach($userTypes as $ut)
                            <option value="{{ $ut }}">{{ $ut }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5" id="targetFilterWrap" style="display:none;">
                    <label class="small fw-bold text-muted mb-2" id="targetFilterLabel">SPECIFIC ROLE</label>
                    <select id="targetFilter" class="form-select"></select>
                    <small class="text-muted d-block mt-1" id="targetHelp"></small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4 shadow-sm" style="background: #fafafa;">
        <div class="card-header bg-white py-3 border-bottom-0">
            <h6 class="mb-0 fw-bold"><i class="bx bx-edit-alt me-2 text-primary"></i> 2. COMPOSE ALERT CONTENT</h6>
        </div>
        <div class="card-body p-4 pt-0">
            <form id="alertForm">
                <input type="hidden" id="alertId" value="">
                <div class="row g-3 mb-4">
                    <div class="col-lg-2">
                        <label class="small fw-bold text-muted mb-2">PRESET STYLE</label>
                        <select id="alertType" class="form-select">
                            <option value="info">Info (Blue)</option>
                            <option value="warning">Warning (Amber)</option>
                            <option value="success">Success (Green)</option>
                            <option value="danger">Danger (Red)</option>
                            <option value="custom">Custom Styling</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="small fw-bold text-muted mb-2">FONT SIZE</label>
                        <select id="fontSize" class="form-select">
                            <option value="">Default</option>
                            <option value="12px">12px</option>
                            <option value="14px">14px</option>
                            <option value="16px">16px</option>
                            <option value="18px">18px</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="small fw-bold text-muted mb-2">WIDTH</label>
                        <select id="alertWidth" class="form-select">
                            <option value="">Auto-Fit</option>
                            <option value="100%">Full Width (100%)</option>
                            <option value="80%">Large (80%)</option>
                            <option value="60%">Medium (60%)</option>
                        </select>
                    </div>
                    <div class="col-lg-1">
                        <label class="small fw-bold text-muted mb-2">BG COLOR</label>
                        <input type="color" id="bgColor" class="form-control form-control-color w-100" value="#fff3cd">
                    </div>
                    <div class="col-lg-1">
                        <label class="small fw-bold text-muted mb-2">TEXT COLOR</label>
                        <input type="color" id="textColor" class="form-control form-control-color w-100" value="#000000">
                    </div>
                    <div class="col-lg-4 d-flex align-items-end gap-3 pb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isMarquee">
                            <label class="form-check-label fw-bold small" for="isMarquee">MARQUEE</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isBold">
                            <label class="form-check-label fw-bold small" for="isBold">BOLD</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" checked>
                            <label class="form-check-label fw-bold small" for="isActive">ACTIVE</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="small fw-bold text-muted mb-2">MESSAGE BODY</label>
                    <textarea id="alertMessage" class="kbd-textarea w-100" rows="3" placeholder="Enter the notification message here..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" id="btnReset">RESET FORM</button>
                    <button type="button" class="btn btn-primary-custom px-5" id="btnSave"><i class="bx bx-save me-1"></i> SAVE ALERT</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-2 text-primary"></i> 3. EXISTING ALERTS</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="alertsTable">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th width="120">TYPE</th>
                            <th>TARGET</th>
                            <th>MESSAGE</th>
                            <th class="text-center" width="100">STATUS</th>
                            <th class="text-center" width="180">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="alertsTbody">
                        <tr><td colspan="6" class="text-center py-5 text-muted">Select school and user category above to view alerts.</td></tr>
                    </tbody>
                </table>
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
    let alertsTable = null;
    let currentOptions = [];
    let loadedAlerts = [];
    let lastNonCustomType = 'info';

    $('#schoolID').select2({ width: '100%', placeholder: '-- Search School --', allowClear: true });

    function safeText(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }

    function resetForm() {
        $('#alertId').val(''); $('#alertType').val('info'); $('#alertWidth').val('');
        $('#bgColor').val('#fff3cd'); $('#textColor').val('#000000');
        $('#isMarquee').prop('checked', false); $('#isBold').prop('checked', false);
        $('#isActive').prop('checked', true); $('#fontSize').val(''); $('#alertMessage').val('');
        $('#btnSave').html('<i class="bx bx-save me-1"></i> SAVE ALERT');
        lastNonCustomType = 'info';
    }

    function getTargetSelectionPayload() {
        const userType = $('#targetUserType').val();
        if (userType === 'Admin' || userType === 'parent') return { applies_to_all: 1, target_role_id: null, target_profession_id: null };
        const targetVal = $('#targetFilter').val();
        const appliesToAll = (targetVal === '__all__');
        return { applies_to_all: appliesToAll ? 1 : 0, target_role_id: (userType === 'Teacher' && !appliesToAll) ? targetVal : null, target_profession_id: (userType === 'Staff' && !appliesToAll) ? targetVal : null };
    }

    async function loadOptions() {
        const schoolID = $('#schoolID').val(); const userType = $('#targetUserType').val();
        $('#targetFilterWrap').hide(); $('#targetFilter').empty(); currentOptions = [];
        if (!schoolID || !userType || userType === 'Admin' || userType === 'parent') return;
        try {
            const res = await $.ajax({ url: '{{ route("superadmin.system_alerts.options") }}', type: 'GET', data: { schoolID, user_type: userType } });
            if (!res.success) return;
            currentOptions = (userType === 'Teacher') ? res.roles : res.professions;
            $('#targetFilterLabel').text((userType === 'Teacher') ? 'SPECIFIC TEACHER ROLE' : 'SPECIFIC STAFF POSITION');
            $('#targetHelp').text((userType === 'Teacher') ? 'Broadcasting to a specific role or all teachers' : 'Broadcasting to a specific position or all staff');
            const opts = ['<option value="__all__">ALL (EVERYONE IN CATEGORY)</option>'].concat(currentOptions.map(o => `<option value="${safeText(o.id)}">${safeText(o.name)}</option>`));
            $('#targetFilter').html(opts.join('')); $('#targetFilterWrap').show();
        } catch (e) { }
    }

    function renderTargetText(a) {
        if (a.applies_to_all || a.target_user_type === 'Admin' || a.target_user_type === 'parent') return '<span class="badge bg-primary px-2">ALL ' + safeText(a.target_user_type).toUpperCase() + 'S</span>';
        if (a.target_user_type === 'Teacher') {
            const role = currentOptions.find(x => String(x.id) === String(a.target_role_id));
            return '<span class="badge bg-info text-dark px-2">ROLE: ' + (role ? safeText(role.name) : safeText(a.target_role_id)) + '</span>';
        }
        if (a.target_user_type === 'Staff') {
            const p = currentOptions.find(x => String(x.id) === String(a.target_profession_id));
            return '<span class="badge bg-info text-dark px-2">POSITION: ' + (p ? safeText(p.name) : safeText(a.target_profession_id)) + '</span>';
        }
        return '';
    }

    function initTable() {
        if (alertsTable) { alertsTable.destroy(); alertsTable = null; }
        alertsTable = $('#alertsTable').DataTable({ pageLength: 25, ordering: true, autoWidth: false, columnDefs: [{ orderable: false, targets: [5] }] });
    }

    async function loadAlerts() {
        const schoolID = $('#schoolID').val(); const userType = $('#targetUserType').val();
        resetForm(); if (alertsTable) { alertsTable.clear().destroy(); alertsTable = null; }
        if (!schoolID || !userType) { $('#alertsTbody').html('<tr><td colspan="6" class="text-center py-5 text-muted">Select school and user category above to view alerts.</td></tr>'); return; }
        $('#alertsTbody').html('<tr><td colspan="6" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary"></div> Fetching alerts...</td></tr>');
        try {
            const res = await $.ajax({ url: '{{ route("superadmin.system_alerts.list") }}', type: 'GET', data: { schoolID, user_type: userType } });
            if (!res.success) { $('#alertsTbody').html('<tr><td colspan="6" class="text-center text-danger py-5">Failed to load alerts.</td></tr>'); return; }
            loadedAlerts = res.alerts || [];
            if (loadedAlerts.length === 0) { $('#alertsTbody').html('<tr><td colspan="6" class="text-center py-5 text-muted">No active or inactive alerts found for this selection.</td></tr>'); return; }
            const rows = loadedAlerts.map(a => {
                const badgeClass = (a.alert_type === 'danger') ? 'danger' : (a.alert_type === 'success' ? 'success' : (a.alert_type === 'warning' ? 'warning' : 'info'));
                return `
                    <tr data-id="${safeText(a.id)}">
                        <td><small class="fw-bold text-muted">#${safeText(a.id)}</small></td>
                        <td><span class="badge bg-${badgeClass} text-uppercase px-2">${safeText(a.alert_type)}</span></td>
                        <td>${renderTargetText(a)}</td>
                        <td class="small" style="max-width: 500px; white-space: normal;">${safeText(a.message)}</td>
                        <td class="text-center">${a.is_active ? '<span class="text-success fw-bold"><i class="bx bxs-circle me-1" style="font-size:0.6rem;"></i> ACTIVE</span>' : '<span class="text-muted small">INACTIVE</span>'}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit me-1" data-id="${safeText(a.id)}"><i class="bx bx-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="${safeText(a.id)}"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                `;
            }).join('');
            $('#alertsTbody').html(rows); initTable();
        } catch (e) { $('#alertsTbody').html('<tr><td colspan="6" class="text-center text-danger py-5">Network error.</td></tr>'); }
    }

    const defaultColors = { info: { bg: '#d1ecf1', text: '#0c5460' }, warning: { bg: '#fff3cd', text: '#856404' }, success: { bg: '#d4edda', text: '#000000' }, danger: { bg: '#f8d7da', text: '#721c24' } };

    $('#alertType').on('change', function() {
        const val = $(this).val(); if (val !== 'custom') {
            lastNonCustomType = val; const c = defaultColors[val] || defaultColors.info;
            $('#bgColor').val(c.bg); $('#textColor').val(c.text);
        }
    });

    $('#btnReset').on('click', resetForm);

    $('#btnSave').on('click', async function() {
        const schoolID = $('#schoolID').val(), target_user_type = $('#targetUserType').val(), message = $('#alertMessage').val();
        if (!schoolID || !target_user_type || !message) { Swal.fire({ icon: 'warning', title: 'Missing Information' }); return; }
        const targetPayload = getTargetSelectionPayload();
        const mode = $('#alertType').val(), isCustom = mode === 'custom', alertTypeToSave = isCustom ? lastNonCustomType : mode;
        const pickedBg = $('#bgColor').val(), pickedText = $('#textColor').val(), defaultsForType = defaultColors[alertTypeToSave] || defaultColors.info;
        const shouldPersistCustomColors = isCustom || (pickedBg.toLowerCase() !== (defaultsForType.bg || '').toLowerCase()) || (pickedText.toLowerCase() !== (defaultsForType.text || '').toLowerCase());
        
        const payload = {
            _token: '{{ csrf_token() }}', id: $('#alertId').val(), schoolID, target_user_type, alert_type: alertTypeToSave, message,
            is_marquee: $('#isMarquee').is(':checked') ? 1 : 0, is_bold: $('#isBold').is(':checked') ? 1 : 0,
            font_size: $('#fontSize').val(), width: $('#alertWidth').val(),
            bg_color: shouldPersistCustomColors ? pickedBg : '', text_color: shouldPersistCustomColors ? pickedText : '',
            is_active: $('#isActive').is(':checked') ? 1 : 0, applies_to_all: targetPayload.applies_to_all,
            target_role_id: targetPayload.target_role_id, target_profession_id: targetPayload.target_profession_id
        };

        try {
            const res = await $.ajax({ url: payload.id ? '{{ route("superadmin.system_alerts.update") }}' : '{{ route("superadmin.system_alerts.store") }}', type: 'POST', data: payload });
            if (res.success) { resetForm(); await loadAlerts(); Swal.fire({ icon: 'success', title: 'Alert Saved' }); }
        } catch (e) { Swal.fire({ icon: 'error', title: 'Error Saving' }); }
    });

    $(document).on('click', '.btn-edit', function() {
        const a = loadedAlerts.find(x => String(x.id) === String($(this).data('id'))); if (!a) return;
        $('#alertId').val(a.id); const hasCustom = !!(a.bg_color || a.text_color); lastNonCustomType = a.alert_type || 'info';
        $('#alertType').val(hasCustom ? 'custom' : lastNonCustomType); $('#alertWidth').val(a.width || '');
        $('#bgColor').val(hasCustom ? a.bg_color : defaultColors[lastNonCustomType].bg);
        $('#textColor').val(hasCustom ? a.text_color : defaultColors[lastNonCustomType].text);
        $('#isMarquee').prop('checked', !!a.is_marquee); $('#isBold').prop('checked', !!a.is_bold);
        $('#isActive').prop('checked', !!a.is_active); $('#fontSize').val(a.font_size || ''); $('#alertMessage').val(a.message || '');
        $('#targetFilter').val(a.applies_to_all ? '__all__' : (a.target_role_id || a.target_profession_id || ''));
        $('#btnSave').html('<i class="bx bx-save me-1"></i> UPDATE ALERT');
        $('html, body').animate({ scrollTop: $('#alertForm').offset().top - 80 }, 300);
    });

    $(document).on('click', '.btn-delete', async function() {
        const confirm = await Swal.fire({ title: 'Delete Alert?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33' });
        if (!confirm.isConfirmed) return;
        try {
            const res = await $.ajax({ url: '{{ url("super-admin/system-alerts/delete") }}/' + $(this).data('id'), type: 'DELETE', data: { _token: '{{ csrf_token() }}' } });
            if (res.success) { await loadAlerts(); Swal.fire({ icon: 'success', title: 'Deleted' }); }
        } catch (e) { }
    });

    $('#schoolID, #targetUserType').on('change', async function() { await loadOptions(); await loadAlerts(); });
    resetForm();
});
</script>
@endpush
@endsection
