@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root { --primary-color: #940000; }
    body { background-color: #f8f9fa; }
    .section-header {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 0;
    }
    .section-header i { color: var(--primary-color); margin-right: 8px; }
    .bg-primary-custom { background-color: var(--primary-color) !important; }
    .text-primary-custom { color: var(--primary-color) !important; }
    .btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    .attendance-device-bar {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
        border: 1px solid #f0d0d0;
        border-radius: 6px;
        padding: 16px;
    }
    .stat-card {
        border-radius: 6px;
        border: 1px solid #e9ecef;
        background: #fff;
        padding: 16px;
        text-align: center;
    }
    .stat-card h4 { color: var(--primary-color); margin-bottom: 4px; font-weight: 700; }
    .nav-tabs .nav-link.active { color: var(--primary-color); border-color: #dee2e6 #dee2e6 #fff; font-weight: 600; }
    .pulse-animation { animation: pulse 1s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    .ts-report-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .ts-person-img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #eee;
    }
    .ts-late-cell { color: #dc3545 !important; font-weight: 600; }
    .ts-row-late { background-color: #fff5f5 !important; }
    .ts-row-weekend { background-color: #f3e8ff !important; }
    .ts-row-nonwork { background-color: #fff8e1 !important; }
    .ts-action-menu { min-width: 140px; }
    .ts-detail-stat {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        text-align: center;
        background: #fff;
    }
    .ts-detail-stat h5 { margin: 0; font-weight: 700; }
    .ts-detail-stat.late h5 { color: #dc3545; }
    #tsReportTable thead th { background-color: var(--primary-color) !important; color: #fff !important; white-space: nowrap; }
    #tsLiveTable thead th,
    #tsRecordsTable thead th { background-color: var(--primary-color) !important; color: #fff !important; white-space: nowrap; }
    #tsReportTable_wrapper .dataTables_filter input,
    #tsLiveTable_wrapper .dataTables_filter input,
    #tsRecordsTable_wrapper .dataTables_filter input { margin-left: 8px; }
    #tsReportDetailDailyTable thead th { background-color: #f8f9fa; white-space: nowrap; }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input { border-radius: 0; }
    .ts-toast-container {
        position: fixed;
        top: 16px;
        right: 16px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: min(360px, calc(100vw - 32px));
        pointer-events: none;
    }
    .ts-toast {
        pointer-events: auto;
        background: #fff;
        border: 1px solid #e9ecef;
        border-left: 5px solid var(--primary-color);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        padding: 12px 14px;
        animation: tsToastIn 180ms ease-out;
    }
    @keyframes tsToastIn {
        from { transform: translateY(-6px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .ts-toast.signout { border-left-color: #198754; }
    .ts-toast.permitted { border-left-color: #6f42c1; }
    .ts-toast.ready { border-left-color: #0d6efd; }
    .ts-toast.unknown { border-left-color: #fd7e14; }
    .ts-toast-title { font-weight: 700; font-size: 14px; margin: 0; }
    .ts-toast-meta { font-size: 12px; color: #666; margin-top: 3px; }
    .ts-toast-msg { font-size: 12px; font-weight: 600; margin-top: 6px; color: #1e7e45; }
    .ts-toast.ready .ts-toast-msg { color: #0d6efd; }
    .ts-toast.permitted .ts-toast-msg { color: #6f42c1; }
    .ts-toast.unknown .ts-toast-msg { color: #b45309; }
    .ts-people-collapse .collapse-toggle {
        width: 100%;
        text-align: left;
        padding: 10px 14px;
        border: none;
        background: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .ts-people-collapse .collapse-toggle:hover { background: #f8f9fa; }
    .ts-people-collapse .collapse-toggle .chevron {
        transition: transform 0.2s ease;
        font-size: 12px;
    }
    .ts-people-collapse .collapse-toggle[aria-expanded="true"] .chevron {
        transform: rotate(180deg);
    }
    .ts-people-collapse .collapse-panel {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 8px;
        background: #fff;
    }
    .ts-people-collapse .collapse-body {
        max-height: 280px;
        overflow-y: auto;
        padding: 8px 14px 12px;
        border-top: 1px solid #e9ecef;
        background: #fafbfc;
    }
    .ts-people-collapse .collapse-body ul { margin-bottom: 0; padding-left: 1.2rem; }
    .ts-people-collapse .collapse-body li { margin-bottom: 4px; }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="ts-toast-container" id="tsToastContainer" aria-live="polite" aria-atomic="true"></div>

<div class="container-fluid" style="padding: 20px;">
    @include('partials.fingerprint_setup_download')

    <div class="row mb-3">
        <div class="col-md-8">
            <h2 class="section-header">
                <i class="bi bi-fingerprint"></i> Teacher and Staff Attendance
            </h2>
            <p class="text-muted mb-0">Fingerprint attendance kwa walimu na wafanyakazi wengine, sync kutoka ZKTeco device.</p>
        </div>
        <div class="col-md-4">
            <div class="row g-2">
                <div class="col-6">
                    <div class="stat-card">
                        <h4>{{ $teacherCount ?? 0 }}</h4>
                        <small class="text-muted">Active Teachers</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <h4>{{ $staffCount ?? 0 }}</h4>
                        <small class="text-muted">Active Staff</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="tsAttendanceTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="ts-live-tab" data-toggle="tab" href="#ts-live" role="tab">
                <i class="bi bi-broadcast"></i> Live Today
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="ts-records-tab" data-toggle="tab" href="#ts-records" role="tab">
                <i class="bi bi-list-ul"></i> Saved Records
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="ts-report-tab" data-toggle="tab" href="#ts-report" role="tab">
                <i class="bi bi-bar-chart"></i> Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="ts-worktime-tab" data-toggle="tab" href="#ts-worktime" role="tab">
                <i class="bi bi-clock"></i> Working Time
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Live Today -->
        <div class="tab-pane fade show active" id="ts-live" role="tabpanel">
            <div class="attendance-device-bar mb-3">
                <div class="row align-items-end">
                    <div class="col-md-4" style="display:none;">
                        <label for="tsLiveDeviceSelect" class="mb-1 text-primary-custom fw-bold">
                            <i class="bi bi-router"></i> Fingerprint Device
                        </label>
                        <select id="tsLiveDeviceSelect" class="form-control">
                            <option value="">Loading devices...</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Show</label>
                        <select id="tsPersonTypeFilter" class="form-control">
                            <option value="all">All</option>
                            <option value="teacher">Teachers</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary-custom btn-block" id="tsSyncLiveNow" title="Refresh live table">
                            <i class="bi bi-arrow-repeat"></i> Refresh
                        </button>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-end gap-2 flex-wrap">
                        <span class="badge bg-secondary" id="tsLiveStatusBadge">
                            <i class="bi bi-circle-fill"></i> Waiting Local Agent
                        </span>
                        <button type="button" class="btn btn-sm btn-secondary d-none" id="tsStopLive" title="Pause live updates">
                            <i class="bi bi-stop-circle"></i> Stop
                        </button>
                        <small class="text-muted" id="tsLiveLastUpdate"></small>
                    </div>
                </div>
            </div>
            <div id="tsLiveContent">
                <div id="tsLiveTableArea">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Live attendance updates zinapokelewa kutoka Local Agent (school LAN).
                        Historical sync inafanywa kwenye Local Agent, si hapa.
                    </div>
                </div>
            </div>
        </div>

        <!-- Saved Records -->
        <div class="tab-pane fade" id="ts-records" role="tabpanel">
            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0"><i class="bi bi-fingerprint"></i> Teacher Fingerprint Attendance (Database)</h6>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" class="form-control form-control-sm" id="tsRecordsDateFilter" style="width: 180px;">
                    <button type="button" class="btn btn-sm btn-primary-custom" id="tsRefreshRecords">
                        <i class="bi bi-arrow-repeat"></i> Refresh
                    </button>
                </div>
            </div>
            <div id="tsRecordsContent">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Bonyeza <strong>Refresh</strong> kuona rekodi zilizohifadhiwa za walimu.
                </div>
            </div>
        </div>

        <!-- Report -->
        <div class="tab-pane fade" id="ts-report" role="tabpanel">
            <div class="attendance-device-bar mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="mb-1 fw-bold text-primary-custom">Report Type</label>
                        <select id="tsReportFilterType" class="form-control">
                            <option value="day" selected>Day</option>
                            <option value="date_range">Date Range</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportDayWrap">
                        <label class="mb-1">Date</label>
                        <input type="date" id="tsReportDate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportFromWrap" style="display:none;">
                        <label class="mb-1">From Date</label>
                        <input type="date" id="tsReportFromDate" class="form-control">
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportToWrap" style="display:none;">
                        <label class="mb-1">To Date</label>
                        <input type="date" id="tsReportToDate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportMonthWrap" style="display:none;">
                        <label class="mb-1">Month</label>
                        <select id="tsReportMonth" class="form-control">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == (int)date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportYearWrap" style="display:none;">
                        <label class="mb-1">Year</label>
                        <input type="number" id="tsReportYear" class="form-control" value="{{ date('Y') }}" min="2000" max="2100">
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Person Type</label>
                        <select id="tsReportPersonType" class="form-control">
                            <option value="all">All</option>
                            <option value="teacher">Teachers</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 fw-bold text-primary-custom">Status</label>
                        <select id="tsReportStatus" class="form-control">
                            <option value="all" selected>All</option>
                            <option value="Present">Present only</option>
                            <option value="Absent">Absent only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary-custom btn-block" id="tsGenerateReport">
                            <i class="bi bi-funnel"></i> Generate Report
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-block" id="tsDownloadAllReportPdf" disabled>
                            <i class="bi bi-file-pdf"></i> Download All PDF
                        </button>
                    </div>
                </div>
            </div>
            <div id="tsReportMeta" class="mb-2"></div>
            <div id="tsReportContent">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Chagua filters hapo juu (Day / Month / … + Person Type + <strong>Status</strong>) kisha bonyeza <strong>Generate Report</strong>.
                    Status: <strong>All</strong>, <strong>Present only</strong>, au <strong>Absent only (Watoro)</strong>: utaona idadi waliopatikana.
                </div>
            </div>
        </div>

        <!-- Working Time -->
        <div class="tab-pane fade" id="ts-worktime" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="text-primary-custom mb-3"><i class="bi bi-clock"></i> Official Working Time</h5>
                            <p class="text-muted small">Weka muda rasmi wa kuingia na kutoka kazini. Hii inatumika kupima kuchelewa na kuwahi kwenye Report.</p>
                            <div class="alert alert-light border small mb-3">
                                <strong><i class="bi bi-info-circle"></i> Check-in window:</strong>
                                Kuanzia masaa 3 kabla ya muda wa kuingia hadi masaa 2 baada ya muda wa kuingia
                                (mfano 8:00 AM → 5:00 AM-10:00 AM), kidole chochote kinasomwa kama <strong>Sign In</strong> tu,
                                hata mtu akiweka mara 2 au zaidi. Baada ya dirisha hilo, kidole kinasomwa kama <strong>Sign Out</strong>.
                            </div>
                            <div class="form-group">
                                <label>Work Start Time</label>
                                <input type="time" id="tsWorkStartTime" class="form-control"
                                    value="{{ substr($workTime->work_start_time ?? '08:00:00', 0, 5) }}">
                            </div>
                            <div class="form-group">
                                <label>Work End Time</label>
                                <input type="time" id="tsWorkEndTime" class="form-control"
                                    value="{{ substr($workTime->work_end_time ?? '15:00:00', 0, 5) }}">
                            </div>
                            <button type="button" class="btn btn-primary-custom" id="tsSaveWorkTime">
                                <i class="bi bi-save"></i> Save Working Time
                            </button>
                            <div id="tsWorkTimeMsg" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PDF Download Progress Modal -->
<div class="modal fade" id="tsDownloadProgressModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-4 text-primary-custom fw-bold" id="tsPdfProgressTitle">Generating Attendance Report</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 6px;">
                    <div id="tsPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                        role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="tsPdfProgressPercent" class="fw-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small mb-0">Please wait, this might take a moment for large reports.</p>
            </div>
        </div>
    </div>
</div>

<!-- Send SMS Modal -->
<div class="modal fade" id="tsReportSmsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-chat-dots"></i> Send Attendance SMS</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Recipient:</strong> <span id="tsSmsRecipientName"></span></p>
                <p class="mb-3 small text-muted"><strong>Phone:</strong> <span id="tsSmsRecipientPhone"></span></p>
                <div class="form-group mb-0">
                    <label>Message</label>
                    <textarea id="tsSmsMessage" class="form-control" rows="5" placeholder="Leave blank to use default attendance summary message."></textarea>
                </div>
                <div id="tsSmsStatus" class="mt-2 small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="tsSendSmsBtn">
                    <i class="bi bi-send"></i> Send SMS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Report Detail Modal -->
<div class="modal fade" id="tsReportDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-person-lines-fill"></i> Attendance Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="tsReportDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary-custom"></div></div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
(function ($) {
    if (typeof $ !== 'function') {
        if (window.console && console.error) {
            console.error('jQuery is not available for teacher/staff attendance page.');
        }
        return;
    }
    var LIVE_DB_POLL_MS = 2000;
    var LIVE_DEVICE_SYNC_MS = 6000;
    var tsLastDeviceSyncAt = 0;
    var tsLiveInterval = null;
    var tsDeviceSyncInterval = null;
    var tsManualSyncActive = false;
    var tsPendingLiveStart = false;
    var tsLiveDbRequest = null;
    var tsLiveDeviceRequest = null;
    var tsDbPollInFlight = false;
    var tsDeviceSyncInFlight = false;
    var tsLiveStopped = false;
    var tsLiveHasData = false;
    var tsSeenEventIds = {};

    function tsLiveEventsStorageKey() {
        return 'sx_ts_central_events_' + '{{ date("Y-m-d") }}';
    }

    function loadTsSeenEventIds() {
        try {
            var raw = sessionStorage.getItem(tsLiveEventsStorageKey());
            tsSeenEventIds = raw ? JSON.parse(raw) : {};
        } catch (e) {
            tsSeenEventIds = {};
        }
    }

    function saveTsSeenEventIds() {
        try {
            sessionStorage.setItem(tsLiveEventsStorageKey(), JSON.stringify(tsSeenEventIds));
        } catch (e) { /* ignore */ }
    }

    function formatTsLiveTime(recordTime) {
        if (!recordTime) return '';
        var parts = String(recordTime).split(' ');
        return parts.length === 2 ? parts[1] : recordTime;
    }

    function tsLiveEventMessage(item) {
        if (item.action === 'Sign In') return 'Signed in successfully.';
        if (item.action === 'Sign Out') return 'Signed out successfully.';
        if (item.action === 'Permitted') return 'Approved permission, counted as Permitted.';
        if (item.action === 'Ignored') return 'Duplicate punch ignored (check-in window).';
        if (item.action === 'Ready') return 'This user already signed in and signed out.';
        if (item.action === 'Unknown' || item.matched === false) {
            return item.message || 'Enroll ID not found in school teachers/staff.';
        }
        return item.message || 'Recorded.';
    }

    function showTsToast(ev) {
        var container = document.getElementById('tsToastContainer');
        if (!container || !ev) return;

        var action = ev.action || 'Sign In';
        var type = 'signin';
        if (action === 'Sign Out') type = 'signout';
        if (action === 'Permitted') type = 'permitted';
        if (action === 'Ready') type = 'ready';
        if (action === 'Unknown' || ev.matched === false) type = 'unknown';

        var toast = document.createElement('div');
        toast.className = 'ts-toast ' + type;
        toast.innerHTML =
            '<div class="ts-toast-title">' + escapeHtml((ev.user_name || ('ID ' + ev.user_id)) + ', ' + action) + '</div>' +
            '<div class="ts-toast-meta">' + escapeHtml((ev.person_type ? ev.person_type + ' • ' : '') + formatTsLiveTime(ev.record_time)) + '</div>' +
            '<div class="ts-toast-msg">' + escapeHtml(tsLiveEventMessage(ev)) + '</div>';

        container.prepend(toast);
        while (container.children.length > 6) {
            container.removeChild(container.lastElementChild);
        }

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-4px)';
            toast.style.transition = 'opacity 180ms ease, transform 180ms ease';
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 220);
        }, 4500);
    }

    function processTsRecentEvents(events) {
        if (!events || !events.length) return;

        var added = false;
        events.slice().reverse().forEach(function (ev) {
            if (!ev || !ev.id || tsSeenEventIds[ev.id]) return;
            tsSeenEventIds[ev.id] = true;
            showTsToast(ev);
            added = true;
        });

        if (added) {
            saveTsSeenEventIds();
        }
    }

    function seedTsSeenEvents(events) {
        if (!events || !events.length) return;
        events.forEach(function (ev) {
            if (ev && ev.id) tsSeenEventIds[ev.id] = true;
        });
        saveTsSeenEventIds();
    }

    function getCsrf() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function getSelectedLiveDeviceId() {
        return $('#tsLiveDeviceSelect').val() || '';
    }

    function getPersonTypeFilter() {
        return $('#tsPersonTypeFilter').val() || 'all';
    }

    function isTsLiveTabActive() {
        return $('#ts-live').hasClass('active') ||
            $('#ts-live-tab').hasClass('active') ||
            $('a[href="#ts-live"]').hasClass('active');
    }

    function updateLiveBadge(state) {
        var $badge = $('#tsLiveStatusBadge');
        if (state === 'live') {
            $badge.removeClass('bg-secondary bg-warning').addClass('bg-success')
                .html('<i class="bi bi-circle-fill pulse-animation"></i> Live');
        } else if (state === 'syncing') {
            $badge.removeClass('bg-secondary').addClass('bg-warning')
                .html('<i class="bi bi-circle-fill pulse-animation"></i> Syncing...');
        } else if (state === 'stopped') {
            $badge.removeClass('bg-success bg-warning').addClass('bg-secondary')
                .html('<i class="bi bi-circle-fill"></i> Paused');
        } else {
            $badge.removeClass('bg-success bg-warning').addClass('bg-secondary')
                .html('<i class="bi bi-circle-fill"></i> Waiting Local Agent');
        }
    }

    function setLastUpdateLabel() {
        var now = new Date();
        var t = now.toLocaleTimeString();
        $('#tsLiveLastUpdate').text('Updated ' + t);
    }

    function restoreOrDefault($select, previous) {
        if (previous && $select.find('option[value="' + previous + '"]').length) {
            $select.val(previous);
        } else if ($select.find('option[value!=""]').length === 1) {
            $select.val($select.find('option[value!=""]').first().val());
        }
    }

    function abortTsLiveRequests() {
        if (tsLiveDbRequest && tsLiveDbRequest.readyState !== 4) {
            tsLiveDbRequest.abort();
            tsLiveDbRequest = null;
        }
        if (tsLiveDeviceRequest && tsLiveDeviceRequest.readyState !== 4) {
            tsLiveDeviceRequest.abort();
            tsLiveDeviceRequest = null;
        }
    }

    function clearTsLiveQueue() {
        // kept for compatibility with stopTsLivePolling
    }

    function pollTsDbInBackground() {
        if (!isTsLiveTabActive() || tsLiveStopped || tsDbPollInFlight) {
            return;
        }
        tsDbPollInFlight = true;
        loadTsLiveAttendance(false, false, function () {
            tsDbPollInFlight = false;
        });
    }

    function syncTsDeviceInBackground(force) {
        if (!isTsLiveTabActive() || tsLiveStopped) {
            return;
        }
        if (tsDeviceSyncInFlight) {
            if (!force) {
                return;
            }
            if (tsLiveDeviceRequest && tsLiveDeviceRequest.readyState !== 4) {
                return;
            }
        }
        tsDeviceSyncInFlight = true;
        loadTsLiveAttendance(false, true, function () {
            tsDeviceSyncInFlight = false;
        });
    }

    function loadFingerprintDevices() {
        return $.ajax({
            url: '{{ route("zkteco.devices.index") }}',
            type: 'GET',
            dataType: 'json',
            timeout: 15000
        }).done(function (res) {
            var devices = (res && res.success && res.devices) ? res.devices : [];
            var liveCurrent = $('#tsLiveDeviceSelect').val();
            var options = '<option value="">-- Select device --</option>';

            devices = devices.filter(function (d) { return d.status !== 'inactive'; });

            if (!devices.length) {
                options = '<option value="">No active devices found</option>';
            } else {
                devices.forEach(function (d) {
                    options += '<option value="' + d.id + '">' + d.label + ' (' + d.ip + ':' + d.port + ')</option>';
                });
            }

            $('#tsLiveDeviceSelect').html(options);
            restoreOrDefault($('#tsLiveDeviceSelect'), liveCurrent);

            if (isTsLiveTabActive() && !tsLiveStopped) {
                startTsLivePolling();
            }
        }).fail(function () {
            $('#tsLiveTableArea').html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Failed to load fingerprint devices. Refresh page and try again.</div>');
        });
    }

    function formatTimeOnly(datetime) {
        if (!datetime) return '';
        var parts = datetime.split(' ');
        return parts.length === 2 ? parts[1] : datetime;
    }

    function personTypeBadge(type) {
        return type === 'staff'
            ? '<span class="badge bg-info">Staff</span>'
            : '<span class="badge bg-primary">Teacher</span>';
    }

    function renderTsLiveEmpty(date, meta) {
        destroyTsAttendanceDataTable('#tsLiveTable');
        tsLiveDataTable = null;
        meta = meta || {};
        var msg = meta.deviceFailed
            ? 'Device haipatikani. Hakuna rekodi za leo kwenye database bado.'
            : 'Hakuna attendance ya leo bado. Subiri mtu akiweka kidole.';
        $('#tsLiveTableArea').html(
            '<div class="alert ' + (meta.deviceFailed ? 'alert-warning' : 'alert-info') + ' mb-0">' +
            '<i class="bi bi-info-circle"></i> ' + msg +
            '<div class="small text-muted mt-1">Date: ' + (date || '{{ date("Y-m-d") }}') + '</div></div>'
        );
    }

    function tsDataTableLanguage() {
        return {
            search: 'Tafuta:',
            lengthMenu: 'Onyesha _MENU_',
            info: 'Inaonyesha _START_ hadi _END_ kati ya _TOTAL_',
            infoEmpty: 'Hakuna rekodi',
            infoFiltered: '(kuchujwa kutoka _MAX_)',
            zeroRecords: 'Hakuna rekodi zinazolingana',
            emptyTable: 'Hakuna data kwenye jedwali',
            paginate: { first: 'Kwanza', last: 'Mwisho', next: 'Ijayo', previous: 'Iliyopita' }
        };
    }

    function destroyTsAttendanceDataTable(selector) {
        if (!$.fn.DataTable) return;
        if ($.fn.DataTable.isDataTable(selector)) {
            try { $(selector).DataTable().destroy(); } catch (e) {}
        }
    }

    function tsDataTableDrawRowNumbers(api) {
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + api.page.info().start;
        });
    }

    function initTsAttendanceDataTable(selector) {
        if (!$(selector).length || !$.fn.DataTable) return null;
        return $(selector).DataTable({
            order: [[2, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Zote']],
            autoWidth: false,
            responsive: true,
            language: tsDataTableLanguage(),
            columnDefs: [
                { orderable: false, targets: [0] },
                { className: 'align-middle', targets: '_all' }
            ],
            drawCallback: function () {
                tsDataTableDrawRowNumbers(this.api());
            }
        });
    }

    function buildTsAttendanceRowHtml(rec, index) {
        var checkIn = formatTimeOnly(rec.check_in_time || '');
        var checkOut = formatTimeOnly(rec.check_out_time || '');
        var hasCheckOut = !!(rec.check_out_time || checkOut);
        var isPermitted = rec.status === 'Permitted' || rec.attendance_status === 'Permitted';
        var status = isPermitted
            ? '<span class="badge bg-purple" style="background-color:#6f42c1;">Permitted</span>'
            : ((hasCheckOut || rec.status === 'Complete' || rec.status === 1 || rec.status === '1')
            ? '<span class="badge bg-success">Complete</span>'
            : '<span class="badge bg-info">Check In Only</span>');
        var newBadge = rec.is_new ? '<span class="badge bg-success">New</span>' : '-';
        var persistedNote = rec.persisted === false ? ' <small class="text-muted">(live)</small>' : '';

        return '<tr' + (rec.is_new ? ' class="table-success"' : '') + '>' +
            '<td>' + (index + 1) + '</td>' +
            '<td>' + personTypeBadge(rec.person_type) + '</td>' +
            '<td>' + escapeHtml(rec.user_name || 'N/A') + persistedNote + '</td>' +
            '<td>' + escapeHtml(rec.role_label || '-') + '</td>' +
            '<td>' + escapeHtml(rec.employee_number || '-') + '</td>' +
            '<td>' + escapeHtml(rec.fingerprint_id || rec.enroll_id || '-') + '</td>' +
            '<td>' + (checkIn || '-') + '</td>' +
            '<td>' + (checkOut || '-') + '</td>' +
            '<td>' + status + '</td>' +
            '<td>' + escapeHtml(rec.device_ip || '-') + '</td>' +
            '<td>' + newBadge + '</td></tr>';
    }

    var tsLiveDataTable = null;
    var tsRecordsDataTable = null;

    function renderTsAttendanceTable(records, date, meta, container) {
        meta = meta || {};
        container = container || '#tsLiveTableArea';
        var isLive = container === '#tsLiveTableArea';
        var tableId = isLive ? 'tsLiveTable' : 'tsRecordsTable';
        var selector = '#' + tableId;

        if (!records || !records.length) {
            if (isLive) {
                renderTsLiveEmpty(date, meta);
            } else {
                destroyTsAttendanceDataTable(selector);
                tsRecordsDataTable = null;
                $(container).html('<div class="alert alert-info mb-0">No teacher attendance records found.</div>');
            }
            return;
        }

        var savedSearch = '';
        var savedPageLen = 25;
        var savedPage = 0;
        if (isLive && tsLiveDataTable && $.fn.DataTable.isDataTable(selector)) {
            savedSearch = tsLiveDataTable.search();
            savedPageLen = tsLiveDataTable.page.len();
            savedPage = tsLiveDataTable.page();
        } else if (!isLive && tsRecordsDataTable && $.fn.DataTable.isDataTable(selector)) {
            savedSearch = tsRecordsDataTable.search();
            savedPageLen = tsRecordsDataTable.page.len();
            savedPage = tsRecordsDataTable.page();
        }

        destroyTsAttendanceDataTable(selector);

        var bodyHtml = '';
        records.forEach(function (rec, index) {
            bodyHtml += buildTsAttendanceRowHtml(rec, index);
        });

        var html = '<div class="table-responsive"><table id="' + tableId + '" class="table table-striped table-hover table-sm mb-0 w-100">' +
            '<thead><tr>' +
            '<th>#</th><th>Type</th><th>Name</th><th>Role</th><th>Emp. No</th><th>Fingerprint ID</th>' +
            '<th>Check In</th><th>Check Out</th><th>Status</th><th>Device IP</th><th>New</th>' +
            '</tr></thead><tbody>' + bodyHtml + '</tbody></table></div>';
        html += '<div class="mt-2 small text-muted" id="' + tableId + 'Meta">Date: ' + date + ' &bullet; Records: ' + records.length;
        if (meta.source && !isLive) html += ' &bullet; ' + meta.source;
        html += '</div>';

        $(container).html(html);

        var dt = initTsAttendanceDataTable(selector);
        if (isLive) {
            tsLiveDataTable = dt;
            tsLiveHasData = true;
            setLastUpdateLabel();
        } else {
            tsRecordsDataTable = dt;
        }

        if (dt && savedSearch) {
            dt.search(savedSearch).draw(false);
        }
        if (dt) {
            dt.page.len(savedPageLen).page(savedPage).draw(false);
        }
    }

    function loadTsLiveAttendance(showLoader, syncFromDevice, onComplete) {
        if (typeof showLoader === 'undefined') showLoader = false;
        if (typeof syncFromDevice === 'undefined') syncFromDevice = false;

        var deviceId = getSelectedLiveDeviceId();
        var $container = $('#tsLiveTableArea');
        var dbOnly = !syncFromDevice;
        var isBackground = !showLoader;
        var hasTable = $container.find('table').length > 0;

        if (showLoader && !hasTable) {
            $container.html('<div class="text-center py-4"><div class="spinner-border text-primary-custom"></div>' +
                '<p class="mt-2 mb-0">Loading live attendance...</p></div>');
        }

        if (dbOnly) {
            if (tsLiveDbRequest && tsLiveDbRequest.readyState !== 4) {
                if (onComplete) onComplete(false);
                return;
            }
        } else if (tsLiveDeviceRequest && tsLiveDeviceRequest.readyState !== 4) {
            if (onComplete) onComplete(false);
            return;
        }

        var ajaxOptions = {
            url: '{{ route("zkteco.attendance.teacher-staff-live-today-from-db") }}',
            type: 'GET',
            data: {
                person_type: getPersonTypeFilter()
            },
            dataType: 'json',
            timeout: 15000
        };

        var xhr = $.ajax(ajaxOptions);

        if (dbOnly) {
            tsLiveDbRequest = xhr;
        } else {
            tsLiveDeviceRequest = xhr;
        }

        xhr.done(function (response) {
            tsManualSyncActive = false;
            if (syncFromDevice) {
                tsLastDeviceSyncAt = Date.now();
            }
            if (!response || !response.success) {
                if (!isBackground && !hasTable) {
                    $container.html('<div class="alert alert-danger mb-0">' + ((response && response.message) || 'Failed to load live attendance.') + '</div>');
                }
                if (onComplete) onComplete(false);
                return;
            }

            var records = response.data || [];
            processTsRecentEvents(response.recent_events || []);

            if (records.length || !isBackground || !hasTable || showLoader) {
                renderTsAttendanceTable(records, response.date, {
                    deviceFailed: response.device_failed
                });
                if (records.length) {
                    tsLiveHasData = true;
                }
            }

            if (!tsLiveStopped) updateLiveBadge('live');
            if (onComplete) onComplete(true, response);
        }).fail(function (xhr, status) {
            tsManualSyncActive = false;
            if (status === 'abort') {
                if (onComplete) onComplete(false);
                return;
            }
            if (!isBackground && !hasTable) {
                var msg = 'Error loading live attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                $container.html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            }
            if (onComplete) onComplete(false);
        }).always(function () {
            if (dbOnly) tsLiveDbRequest = null;
            else tsLiveDeviceRequest = null;
        });
    }

    function stopTsLivePolling(paused) {
        if (tsLiveInterval) clearInterval(tsLiveInterval);
        if (tsDeviceSyncInterval) clearInterval(tsDeviceSyncInterval);
        tsLiveInterval = null;
        tsDeviceSyncInterval = null;
        abortTsLiveRequests();
        tsDbPollInFlight = false;
        tsDeviceSyncInFlight = false;
        tsManualSyncActive = false;
        tsLiveStopped = !!paused;
        if (paused) updateLiveBadge('stopped');
    }

    function startTsLivePolling() {
        if (!isTsLiveTabActive()) {
            tsPendingLiveStart = true;
            return;
        }

        tsPendingLiveStart = false;
        tsLiveStopped = false;
        tsLiveHasData = false;

        if (tsLiveInterval) clearInterval(tsLiveInterval);
        if (tsDeviceSyncInterval) clearInterval(tsDeviceSyncInterval);
        abortTsLiveRequests();
        tsDbPollInFlight = false;
        tsDeviceSyncInFlight = false;

        loadTsSeenEventIds();
        updateLiveBadge('live');

        loadTsLiveAttendance(true, false, function (ok, response) {
            if (ok && response && response.recent_events) {
                seedTsSeenEvents(response.recent_events);
            }
        });

        tsLiveInterval = setInterval(function () {
            if (!isTsLiveTabActive() || tsLiveStopped) {
                return;
            }
            pollTsDbInBackground();
        }, LIVE_DB_POLL_MS);
    }

    function loadTsSavedRecords() {
        var dateFilter = $('#tsRecordsDateFilter').val();
        var container = '#tsRecordsContent';

        destroyTsAttendanceDataTable('#tsRecordsTable');
        tsRecordsDataTable = null;
        $(container).html('<div class="text-center py-4"><div class="spinner-border text-primary-custom"></div><p class="mt-2 mb-0">Loading records...</p></div>');

        $.ajax({
            url: '{{ route("attendance.teacher_staff_fingerprint") }}',
            type: 'GET',
            data: { page: 1, per_page: 'all', date: dateFilter },
            dataType: 'json',
            success: function (data) {
                if (!data.success) {
                    $(container).html('<div class="alert alert-danger mb-0">' + (data.message || 'Failed to load records.') + '</div>');
                    return;
                }
                var records = data.data || [];
                if (!records.length) {
                    $(container).html('<div class="alert alert-info mb-0">No teacher or staff attendance records found.</div>');
                    return;
                }

                var mapped = records.map(function (r) {
                    var t = r.teacher_info || {};
                    var s = r.staff_info || {};
                    var isStaff = r.person_type === 'staff';
                    var info = isStaff ? s : t;
                    return {
                        person_type: r.person_type || (isStaff ? 'staff' : 'teacher'),
                        user_name: r.user && r.user.name ? r.user.name : (info.full_name || 'N/A'),
                        role_label: info.position || (isStaff ? 'Staff' : 'Teacher'),
                        employee_number: info.employee_number,
                        fingerprint_id: info.fingerprint_id || (r.user && r.user.enroll_id),
                        check_in_time: r.check_in_time,
                        check_out_time: r.check_out_time,
                        status: r.status,
                        device_ip: r.device_ip,
                        is_new: false,
                        persisted: true
                    };
                });

                renderTsAttendanceTable(mapped, dateFilter || '{{ date("Y-m-d") }}', { source: 'Database' }, '#tsRecordsContent');
            },
            error: function () {
                $(container).html('<div class="alert alert-danger mb-0">Error loading saved records.</div>');
            }
        });
    }

    window.tsLoadRecordsPage = loadTsSavedRecords;

    var tsLastReportParams = null;
    var tsLastReportMeta = null;
    var tsReportDataTable = null;
    var tsReportDetailDataTable = null;
    var tsSmsTarget = null;

    function destroyTsReportDataTable() {
        if (tsReportDataTable) {
            try { tsReportDataTable.destroy(); } catch (e) {}
            tsReportDataTable = null;
        } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tsReportTable')) {
            $('#tsReportTable').DataTable().destroy();
        }
    }

    function destroyTsReportDetailDataTable() {
        if (tsReportDetailDataTable) {
            try { tsReportDetailDataTable.destroy(); } catch (e) {}
            tsReportDetailDataTable = null;
        } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tsReportDetailDailyTable')) {
            $('#tsReportDetailDailyTable').DataTable().destroy();
        }
    }

    function initTsReportDataTable(showCompleteCol, isDayReport) {
        destroyTsReportDataTable();
        if (!$('#tsReportTable').length) return;

        if (isDayReport) {
            tsReportDataTable = $('#tsReportTable').DataTable({
                order: [[2, 'asc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Zote']],
                autoWidth: false,
                responsive: true,
                language: tsDataTableLanguage(),
                columnDefs: [
                    { orderable: false, targets: [0, 1, 10] },
                    { className: 'text-center align-middle', targets: [0, 3, 4, 5, 6, 7, 8, 9, 10] }
                ],
                drawCallback: function () {
                    tsDataTableDrawRowNumbers(this.api());
                }
            });
            return;
        }

        var actionCol = showCompleteCol ? 10 : 9;
        var centerCols = showCompleteCol ? [0, 3, 4, 5, 6, 7, 8, 9, 10] : [0, 3, 4, 5, 6, 7, 8, 9];

        tsReportDataTable = $('#tsReportTable').DataTable({
            order: [[2, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Zote']],
            autoWidth: false,
            responsive: true,
            language: tsDataTableLanguage(),
            columnDefs: [
                { orderable: false, targets: [0, 1, actionCol] },
                { className: 'text-center align-middle', targets: centerCols },
                { className: 'align-middle', targets: '_all' }
            ],
            drawCallback: function () {
                tsDataTableDrawRowNumbers(this.api());
            }
        });
    }

    function initTsReportDetailDataTable() {
        destroyTsReportDetailDataTable();
        if (!$('#tsReportDetailDailyTable').length) return;

        tsReportDetailDataTable = $('#tsReportDetailDailyTable').DataTable({
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Zote']],
            autoWidth: false,
            responsive: true,
            language: tsDataTableLanguage(),
            columnDefs: [
                { orderData: [0], targets: 0 },
                { className: 'text-center', targets: [1, 2, 3] }
            ]
        });
    }

    function updateTsReportFilterVisibility() {
        var type = $('#tsReportFilterType').val();
        $('.ts-report-filter').hide();
        if (type === 'day') {
            $('#tsReportDayWrap').show();
        } else if (type === 'date_range') {
            $('#tsReportFromWrap, #tsReportToWrap').show();
        } else if (type === 'month') {
            $('#tsReportMonthWrap, #tsReportYearWrap').show();
        } else if (type === 'year') {
            $('#tsReportYearWrap').show();
        }
    }

    function getTsReportParams() {
        var type = $('#tsReportFilterType').val();
        var params = {
            filter_type: type,
            person_type: $('#tsReportPersonType').val(),
            attendance_status: $('#tsReportStatus').val() || 'all'
        };
        if (type === 'day') {
            params.date = $('#tsReportDate').val();
        } else if (type === 'date_range') {
            params.from_date = $('#tsReportFromDate').val();
            params.to_date = $('#tsReportToDate').val();
        } else if (type === 'month') {
            params.month = $('#tsReportMonth').val();
            params.year = $('#tsReportYear').val();
        } else if (type === 'year') {
            params.year = $('#tsReportYear').val();
        }
        return params;
    }

    function filterTsReportRowsByStatus(rows, statusFilter, isDayReport) {
        if (!statusFilter || statusFilter === 'all') return rows || [];
        return (rows || []).filter(function (row) {
            if (isDayReport) {
                var st = row.day_status || 'Absent';
                if (statusFilter === 'Present') {
                    return ['Present', 'Late', 'Early'].indexOf(st) >= 0;
                }
                if (statusFilter === 'Absent') {
                    return st === 'Absent';
                }
                return true;
            }
            if (statusFilter === 'Present') {
                return Number(row.present_days || 0) > 0;
            }
            if (statusFilter === 'Absent') {
                return Number(row.absent_days || 0) > 0;
            }
            return true;
        });
    }

    function buildTsPeopleCollapsePanel(panelId, title, people, titleClass) {
        if (!people || !people.length) return '';
        var html = '<div class="collapse-panel">';
        html += '<button type="button" class="collapse-toggle ' + (titleClass || '') + '" data-toggle="collapse" data-target="#' + panelId + '" aria-expanded="false" aria-controls="' + panelId + '">';
        html += '<span><i class="bi bi-chevron-down chevron mr-1"></i> ' + title + ' (' + people.length + ')</span>';
        html += '</button>';
        html += '<div id="' + panelId + '" class="collapse"><div class="collapse-body"><ul>';
        people.forEach(function (p) {
            var typeLbl = p.person_type === 'staff' ? 'Staff' : 'Mwalimu';
            var timeLbl = p.check_in_time ? formatAmPm(p.check_in_time) : '';
            html += '<li>' + escapeHtml(p.name) + ' <span class="text-muted">(' + typeLbl + (timeLbl ? ', ' + timeLbl : '') + ')</span></li>';
        });
        html += '</ul></div></div></div>';
        return html;
    }

    function loadTsAttendanceReport() {
        var params = getTsReportParams();
        var $content = $('#tsReportContent');
        var $meta = $('#tsReportMeta');

        destroyTsReportDataTable();
        $content.html('<div class="text-center py-4"><div class="spinner-border text-primary-custom"></div><p class="mt-2 mb-0">Generating report...</p></div>');
        $meta.empty();
        $('#tsDownloadAllReportPdf').prop('disabled', true);

        $.ajax({
            url: '{{ route("attendance.teacher_staff_report") }}',
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $content.html('<div class="alert alert-danger mb-0">' + (res.message || 'Failed to generate report.') + '</div>');
                    return;
                }

                tsLastReportParams = params;
                tsLastReportMeta = res;

                var metaHtml = '<div class="ts-report-title text-center mb-2">' + (res.title || 'ATTENDANCE REPORT') + '</div>';
                metaHtml += '<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2 px-3">';
                metaHtml += '<div class="row text-center small g-2">';

                if (res.filter_type === 'day' && res.day_summary) {
                    var ds = res.day_summary;
                    metaHtml += '<div class="col-md-3"><strong>Tarehe</strong><br><span class="text-primary-custom fw-bold">' + escapeHtml(ds.date_label || ds.date || '') + '</span></div>';
                    metaHtml += '<div class="col-md-3"><strong>Wamehudhuria</strong><br><span class="text-success fs-4 fw-bold">' + (ds.present_count || 0) + '</span> / ' + (ds.total_people || 0) + '</div>';
                    metaHtml += '<div class="col-md-3"><strong>Walio Chelewa</strong><br><span class="text-danger fs-4 fw-bold">' + (ds.late_count || 0) + '</span></div>';
                    metaHtml += '<div class="col-md-3"><strong>Walio Wahi</strong><br><span class="text-info fs-4 fw-bold">' + (ds.early_count || 0) + '</span></div>';
                    metaHtml += '</div><div class="row text-center small g-2 mt-2 pt-2 border-top">';
                    metaHtml += '<div class="col"><strong>Walikosa</strong><br><span class="text-danger fs-5">' + (ds.absent_count || 0) + '</span></div>';
                    metaHtml += '<div class="col"><strong>Walio na Ruhusa</strong><br><span style="color:#6f42c1;" class="fs-5">' + (ds.permitted_count || 0) + '</span></div>';
                    metaHtml += '<div class="col"><strong>Wakati Sahihi</strong><br><span class="text-success fs-5">' + (ds.on_time_count || 0) + '</span></div>';
                    metaHtml += '<div class="col"><strong>Siku ya Kazi</strong><br>' + (ds.is_working_day ? '<span class="text-success">Ndiyo</span>' : '<span class="text-muted">Hapana</span>') + '</div>';
                    metaHtml += '</div>';
                    if ((ds.teachers_present || 0) > 0 || (ds.staff_present || 0) > 0) {
                        metaHtml += '<div class="row text-center small g-2 mt-2 pt-2 border-top">';
                        metaHtml += '<div class="col"><strong>Walimu Waliopo</strong><br>' + (ds.teachers_present || 0) + '</div>';
                        metaHtml += '<div class="col"><strong>Staff Waliopo</strong><br>' + (ds.staff_present || 0) + '</div>';
                        metaHtml += '</div>';
                    }
                    if ((ds.late_people && ds.late_people.length) || (ds.early_people && ds.early_people.length)) {
                        metaHtml += '<div class="ts-people-collapse mt-2 pt-2 border-top text-start small">';
                        metaHtml += buildTsPeopleCollapsePanel('tsLatePeopleList', '<i class="bi bi-clock-history"></i> Walio Chelewa', ds.late_people || [], 'text-danger');
                        metaHtml += buildTsPeopleCollapsePanel('tsEarlyPeopleList', '<i class="bi bi-sunrise"></i> Walio Wahi', ds.early_people || [], 'text-info');
                        metaHtml += '</div>';
                    }
                    metaHtml += '<p class="small text-muted mb-0 mt-2 text-center"><i class="bi bi-info-circle"></i> ' +
                        '<strong>Present</strong> = ameweka kidole (check-in tu inatosha). ' +
                        '<strong>Walio Chelewa</strong> = wameingia baada ya muda rasmi. ' +
                        '<strong>Walio Wahi</strong> = wameingia kabla ya muda rasmi.</p>';
                } else {
                    metaHtml += '<div class="col-md-3"><strong>Working Days (Month)</strong><br><span class="text-primary-custom fs-5">' + res.total_working_days + '</span></div>';
                    if (res.period_incomplete && res.complete_working_days !== undefined) {
                        metaHtml += '<div class="col-md-3"><strong>Complete W/days</strong><br><span class="fs-5 text-warning">' + res.complete_working_days + '</span><br><small class="text-muted">Siku za kazi zilizopita</small></div>';
                    }
                    metaHtml += '<div class="col-md-3"><strong>Official Time</strong><br>' + formatAmPm(res.work_start_time) + ' - ' + formatAmPm(res.work_end_time) + '</div>';
                    metaHtml += '<div class="col-md-3"><strong>Period</strong><br>' + (res.period_incomplete ? '<span class="text-warning">In progress</span>' : 'Complete') + '</div>';
                    metaHtml += '</div>';
                    if (res.period_incomplete) {
                        metaHtml += '<p class="small text-muted mb-0 mt-2 text-center"><i class="bi bi-info-circle"></i> ' +
                            '<strong>Present</strong> = siku ulizoweka kidole. ' +
                            '<strong>Permitted</strong> = siku za kazi ulizo na ruksa iliyoidhinishwa. ' +
                            '<strong>Absent</strong> = siku za kazi zilizopita bila kidole wala ruksa.</p>';
                    }
                }
                metaHtml += '</div></div>';

                var statusFilter = params.attendance_status || 'all';
                var isDayReport = res.filter_type === 'day';
                var allRows = res.data || [];
                var rows = filterTsReportRowsByStatus(allRows, statusFilter, isDayReport);

                if (statusFilter !== 'all') {
                    var statusLabel = statusFilter === 'Absent' ? 'Absent only (Watoro)' : 'Present only';
                    metaHtml += '<div class="alert alert-light border py-2 px-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">';
                    metaHtml += '<div><strong><i class="bi bi-funnel"></i> Status filter:</strong> ' + statusLabel + '</div>';
                    metaHtml += '<div><strong>Waliopatikana:</strong> <span class="badge bg-primary-custom" style="background:#940000;font-size:1rem;">' + rows.length + '</span>';
                    metaHtml += ' <span class="text-muted small">/ ' + allRows.length + ' total</span></div>';
                    metaHtml += '</div>';
                }

                $meta.html(metaHtml);

                if (!rows.length) {
                    $content.html('<div class="alert alert-info mb-0">No teachers or staff found for this filter' +
                        (statusFilter !== 'all' ? ' (Status: ' + statusFilter + ')' : '') + '.</div>');
                    return;
                }

                var showCompleteCol = !!res.period_incomplete && !isDayReport;
                var html = '<div class="table-responsive"><table id="tsReportTable" class="table table-striped table-hover table-sm mb-0 w-100">' +
                    '<thead><tr>' +
                    '<th>#</th><th>Photo</th><th>Name</th>';
                if (isDayReport) {
                    html += '<th>Hali</th><th>Ingia</th><th>Toka</th><th>Walio Chelewa</th><th>Walio Wahi</th>';
                } else {
                    html += '<th>Present</th><th>Permitted</th><th>Absent</th><th>Working Days</th>';
                }
                if (showCompleteCol) {
                    html += '<th>Complete W/days</th>';
                }
                html += '<th>Late</th><th>Early</th><th>Action</th></tr></thead><tbody>';

                rows.forEach(function (row, i) {
                    var lateClass = row.late_days > 0 ? ' ts-late-cell' : '';
                    var typeBadge = row.person_type === 'staff'
                        ? '<span class="badge bg-info ms-1">Staff</span>'
                        : '<span class="badge bg-primary ms-1">Teacher</span>';
                    var statusBadge = '';
                    if (isDayReport) {
                        var st = row.day_status || 'Absent';
                        var stLabel = {
                            'Present': 'Alikuwepo',
                            'Late': 'Amechelewa',
                            'Early': 'Amewahi',
                            'Absent': 'Hayupo',
                            'Permitted': 'Ruhusa',
                            'Off Day': 'Sio Siku ya Kazi'
                        }[st] || st;
                        var badgeClass = 'bg-secondary';
                        if (st === 'Present') badgeClass = 'bg-success';
                        else if (st === 'Late') badgeClass = 'bg-danger';
                        else if (st === 'Early') badgeClass = 'bg-info';
                        else if (st === 'Absent') badgeClass = 'bg-danger';
                        else if (st === 'Permitted') badgeClass = 'bg-purple';
                        else if (st === 'Off Day') badgeClass = 'bg-secondary';
                        var badgeStyle = st === 'Permitted' ? ' style="background-color:#6f42c1;"' : '';
                        statusBadge = '<span class="badge ' + badgeClass + '"' + badgeStyle + '>' + escapeHtml(stLabel) + '</span>';
                    }
                    html += '<tr' + (isDayReport && row.day_status === 'Late' ? ' class="ts-row-late"' : (isDayReport && ['Present', 'Early'].indexOf(row.day_status) >= 0 ? ' class="table-success"' : '')) + '>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td><img src="' + row.image_url + '" class="ts-person-img" alt=""></td>' +
                        '<td>' + escapeHtml(row.name) + typeBadge + '</td>';
                    if (isDayReport) {
                        var isLate = row.day_status === 'Late';
                        var isEarly = row.day_status === 'Early';
                        html += '<td>' + statusBadge + '</td>' +
                            '<td>' + (row.check_in_time ? formatAmPm(row.check_in_time) : '-') + '</td>' +
                            '<td>' + (row.check_out_time ? formatAmPm(row.check_out_time) : '-') + '</td>' +
                            '<td class="text-center">' + (isLate ? '<span class="badge bg-danger">Ndiyo</span>' : '<span class="text-muted">-</span>') + '</td>' +
                            '<td class="text-center">' + (isEarly ? '<span class="badge bg-info">Ndiyo</span>' : '<span class="text-muted">-</span>') + '</td>';
                    } else {
                        html += '<td><span class="badge bg-success">' + row.present_days + '</span></td>' +
                            '<td><span class="badge" style="background-color:#6f42c1;">' + (row.permitted_days || 0) + '</span></td>' +
                            '<td><span class="badge bg-danger">' + row.absent_days + '</span></td>' +
                            '<td>' + row.total_working_days + '</td>';
                    }
                    if (showCompleteCol) {
                        html += '<td><span class="badge bg-warning text-dark">' + (row.complete_working_days || row.elapsed_working_days || 0) + '</span></td>';
                    }
                    html += '<td class="' + lateClass + '">' + row.late_days + '</td>' +
                        '<td>' + row.early_days + '</td>' +
                        '<td class="text-nowrap">' +
                        '<div class="dropdown">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>' +
                        '<div class="dropdown-menu dropdown-menu-right">' +
                        '<a class="dropdown-item ts-action-view-more" href="#" data-person-type="' + row.person_type + '" data-person-id="' + row.person_id + '">' +
                        '<i class="bi bi-eye"></i> View More</a>' +
                        '<a class="dropdown-item ts-action-sms" href="#" data-person-type="' + row.person_type + '" data-person-id="' + row.person_id + '" ' +
                        'data-name="' + escapeHtml(row.name) + '" data-phone="' + escapeHtml(row.phone_number || '') + '" ' +
                        'data-present="' + row.present_days + '" data-absent="' + row.absent_days + '" data-late="' + row.late_days + '" data-early="' + row.early_days + '">' +
                        '<i class="bi bi-chat-dots"></i> Send SMS</a>' +
                        '<a class="dropdown-item ts-action-pdf" href="#" data-person-type="' + row.person_type + '" data-person-id="' + row.person_id + '" data-name="' + escapeHtml(row.name) + '">' +
                        '<i class="bi bi-file-pdf text-danger"></i> Download PDF</a>' +
                        '</div></div></td></tr>';
                });

                html += '</tbody></table></div>';
                $content.html(html);
                initTsReportDataTable(showCompleteCol, isDayReport);
                $('#tsDownloadAllReportPdf').prop('disabled', false);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error generating report.';
                $content.html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            }
        });
    }

    function openTsReportDetail(personType, personId) {
        if (!tsLastReportParams) return;
        destroyTsReportDetailDataTable();
        var params = $.extend({}, tsLastReportParams, {
            person_type: personType,
            person_id: personId
        });

        $('#tsReportDetailBody').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom"></div></div>');
        $('#tsReportDetailModal').modal('show');

        $.ajax({
            url: '{{ route("attendance.teacher_staff_report_detail") }}',
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $('#tsReportDetailBody').html('<div class="alert alert-danger mb-0">' + (res.message || 'Failed to load details.') + '</div>');
                    return;
                }

                var p = res.person || {};
                var s = res.statistics || {};
                var html = '<div class="d-flex align-items-center mb-3 gap-3">' +
                    '<img src="' + (p.image_url || '') + '" class="ts-person-img" style="width:64px;height:64px;" alt="">' +
                    '<div><h5 class="mb-0">' + (p.name || '') + '</h5>' +
                    '<small class="text-muted">' + (p.role_label || '') + ' &bullet; ' + (p.employee_number || '-') + '</small></div></div>';

                html += '<div class="row g-2 mb-3">';
                html += statBox('Present', s.present_days, '');
                html += statBox('Permitted', s.permitted_days, '');
                html += statBox('Absent', s.absent_days, '');
                html += statBox('Working Days', s.total_working_days, '');
                if (res.period_incomplete) {
                    html += statBox('Complete W/days', s.complete_working_days || s.elapsed_working_days, '');
                }
                html += statBox('Late Days', s.late_days, 'late');
                html += statBox('Early Days', s.early_days, '');
                html += '</div>';

                if (res.period_incomplete) {
                    html += '<p class="small text-muted"><i class="bi bi-info-circle"></i> Absent = siku za kazi zilizopita bila rekodi ya kidole kwenye database.</p>';
                }

                html += '<p class="small text-muted">Official time: <strong>' + formatAmPm(res.work_start_time) + ' - ' + formatAmPm(res.work_end_time) + '</strong></p>';
                html += '<p class="small text-muted mb-2"><span class="badge" style="background:#fff3cd;color:#856404;border:1px solid #ffc107;">Sio siku ya kazi</span> &nbsp; <span class="badge" style="background:#f3e8ff;color:#6f42c1;border:1px solid #6f42c1;">Weekend</span></p>';
                html += '<div class="table-responsive"><table id="tsReportDetailDailyTable" class="table table-sm table-bordered table-striped mb-0 w-100">' +
                    '<thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead><tbody>';

                (res.daily_records || []).forEach(function (day) {
                    var rowClass = '';
                    if (day.is_late) rowClass = ' class="ts-row-late"';
                    else if (day.day_kind === 'weekend' || day.status === 'Weekend') rowClass = ' class="ts-row-weekend"';
                    else if (day.day_kind === 'non_working' || day.status === 'Not a working day') rowClass = ' class="ts-row-nonwork"';

                    var statusBadge = day.status === 'Permitted'
                        ? '<span class="badge" style="background-color:#6f42c1;">Permitted</span>'
                        : (day.status === 'Weekend'
                        ? '<span class="badge" style="background:#6f42c1;">Weekend</span><div class="small" style="color:#6f42c1;">Ni weekend</div>'
                        : (day.status === 'Not a working day'
                        ? '<span class="badge" style="background:#ffc107;color:#333;">Not a working day</span><div class="small" style="color:#856404;">Sio siku ya kazi</div>'
                        : (day.status === 'Absent'
                        ? '<span class="badge bg-danger">Absent</span>'
                        : (day.status === 'Late'
                            ? '<span class="badge bg-danger">Late</span>'
                            : (day.status === 'Early'
                                ? '<span class="badge bg-info">Early</span>'
                                : (day.status === 'Present (Off Day)'
                                    ? '<span class="badge bg-secondary">Present (Off Day)</span>'
                                    : '<span class="badge bg-success">Present</span>'))))));
                    html += '<tr' + rowClass + '>' +
                        '<td data-order="' + (day.date || '') + '">' + day.date_label + '</td>' +
                        '<td>' + (day.check_in_time ? formatAmPm(day.check_in_time) : '-') + '</td>' +
                        '<td>' + (day.check_out_time ? formatAmPm(day.check_out_time) : '-') + '</td>' +
                        '<td>' + statusBadge + '</td></tr>';
                });

                html += '</tbody></table></div>';
                $('#tsReportDetailBody').html(html);
                initTsReportDetailDataTable();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error loading details.';
                $('#tsReportDetailBody').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            }
        });
    }

    function statBox(label, value, extraClass) {
        return '<div class="col"><div class="ts-detail-stat ' + (extraClass || '') + '">' +
            '<h5>' + (value || 0) + '</h5><small class="text-muted">' + label + '</small></div></div>';
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildTsReportQueryString(extra) {
        var params = $.extend({}, tsLastReportParams || {}, extra || {});
        return $.param(params);
    }

    function formatAmPm(timeStr) {
        if (!timeStr) return '';
        // Values may arrive as full datetimes (e.g. "2026-07-15 07:23:45");
        // extract only the time portion so the hour is parsed correctly.
        var str = String(timeStr).trim();
        var timeMatch = str.match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*$/);
        if (!timeMatch) return timeStr;
        var h = parseInt(timeMatch[1], 10);
        var m = timeMatch[2];
        if (isNaN(h) || h > 23) return timeStr;
        var ampm = h >= 12 ? 'PM' : 'AM';
        var hr12 = h % 12;
        if (hr12 === 0) hr12 = 12;
        return String(hr12).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + ampm;
    }

    var tsSmsSchoolPrefix = @json($school_details->sms_prefix ?? '');

    function withSchoolSmsPrefix(message) {
        var body = (message || '').trim();
        var prefix = tsSmsSchoolPrefix || '';
        if (!prefix || body.indexOf(prefix) === 0) {
            return body;
        }
        return prefix + body;
    }

    function buildDefaultTsSmsMessage(data) {
        var meta = tsLastReportMeta || {};
        var period = meta.period_label || '';
        var workStart = meta.work_start_time || '';
        var workEnd = meta.work_end_time || '';
        var body = 'Habari ' + (data.name || '') + ', ripoti ya mahudhurio (' + period + '): ' +
            'Present ' + (data.present || 0) + ', Absent ' + (data.absent || 0) + ', ' +
            'Late ' + (data.late || 0) + ', Early ' + (data.early || 0) + '. ' +
            'Siku za kazi (mwezi): ' + (meta.total_working_days || 0) + '. ' +
            (meta.period_incomplete ? ('Siku zilizopita: ' + (meta.complete_working_days || meta.elapsed_working_days || 0) + '. ') : '') +
            'Muda rasmi: ' + workStart + ' - ' + workEnd + '.';
        return withSchoolSmsPrefix(body);
    }

    function downloadTsAttendancePdf(url, title, filename) {
        $('#tsPdfProgressTitle').text(title || 'Generating Attendance Report');
        $('#tsDownloadProgressModal').modal('show');
        $('#tsPdfProgressBar').css('width', '0%');
        $('#tsPdfProgressPercent').text('0%');

        var progress = 0;
        var interval = setInterval(function () {
            if (progress < 95) {
                var inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                if (progress > 80) inc = 0.5 + Math.random();
                progress += inc;
                if (progress > 95) progress = 95;
                $('#tsPdfProgressBar').css('width', progress + '%');
                $('#tsPdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 400);

        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error('Server error: ' + response.statusText);
                return response.blob();
            })
            .then(function (blob) {
                clearInterval(interval);
                $('#tsPdfProgressBar').css('width', '100%');
                $('#tsPdfProgressPercent').text('100%');

                setTimeout(function () {
                    var downloadUrl = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = filename || ('Attendance_Report_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.pdf');
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);

                    setTimeout(function () {
                        $('#tsDownloadProgressModal').modal('hide');
                        setTimeout(function () {
                            $('#tsPdfProgressBar').css('width', '0%');
                            $('#tsPdfProgressPercent').text('0%');
                        }, 500);
                    }, 800);
                }, 400);
            })
            .catch(function (error) {
                clearInterval(interval);
                $('#tsDownloadProgressModal').modal('hide');
                alert('Failed to generate PDF: ' + error.message);
            });
    }

    function openTsSmsModal(personType, personId, $el) {
        if (!tsLastReportParams) return;
        var phone = ($el.data('phone') || '').toString().trim();
        var name = ($el.data('name') || '').toString();
        tsSmsTarget = {
            person_type: personType,
            person_id: personId,
            name: name,
            phone: phone,
            present: $el.data('present'),
            absent: $el.data('absent'),
            late: $el.data('late'),
            early: $el.data('early')
        };

        $('#tsSmsRecipientName').text(name);
        $('#tsSmsRecipientPhone').text(phone || 'Not on file');
        $('#tsSmsMessage').val(buildDefaultTsSmsMessage(tsSmsTarget));
        $('#tsSmsStatus').text('').removeClass('text-danger text-success');
        $('#tsReportSmsModal').modal('show');
    }

    function sendTsAttendanceSms() {
        if (!tsSmsTarget || !tsLastReportParams) return;
        if (!tsSmsTarget.phone) {
            $('#tsSmsStatus').text('No phone number on file for this person.').addClass('text-danger');
            return;
        }

        var $btn = $('#tsSendSmsBtn');
        var $status = $('#tsSmsStatus');
        $btn.prop('disabled', true);
        $status.text('Sending...').removeClass('text-danger text-success').addClass('text-muted');

        $.ajax({
            url: '{{ route("attendance.teacher_staff_report_sms") }}',
            type: 'POST',
            data: $.extend({}, tsLastReportParams, {
                _token: getCsrf(),
                person_type: tsSmsTarget.person_type,
                person_id: tsSmsTarget.person_id,
                message: withSchoolSmsPrefix($('#tsSmsMessage').val() || '')
            }),
            success: function (res) {
                if (res.success) {
                    $status.text(res.message || 'SMS sent.').removeClass('text-muted text-danger').addClass('text-success');
                    setTimeout(function () { $('#tsReportSmsModal').modal('hide'); }, 1200);
                } else {
                    $status.text(res.message || 'Failed to send SMS.').removeClass('text-muted text-success').addClass('text-danger');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error sending SMS.';
                $status.text(msg).removeClass('text-muted text-success').addClass('text-danger');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    }

    function saveTsWorkTime() {
        var $msg = $('#tsWorkTimeMsg');
        var start = $('#tsWorkStartTime').val();
        var end = $('#tsWorkEndTime').val();
        if (!start || !end) {
            $msg.html('<div class="alert alert-warning mb-0">Please set both start and end time.</div>');
            return;
        }
        $('#tsSaveWorkTime').prop('disabled', true);
        $.ajax({
            url: '{{ route("attendance.teacher_staff_work_time.save") }}',
            type: 'POST',
            data: { _token: getCsrf(), work_start_time: start, work_end_time: end },
            success: function (res) {
                if (res.success) {
                    $msg.html('<div class="alert alert-success mb-0">' + (res.message || 'Saved.') + '</div>');
                } else {
                    $msg.html('<div class="alert alert-danger mb-0">' + (res.message || 'Failed.') + '</div>');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error saving.';
                $msg.html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            },
            complete: function () {
                $('#tsSaveWorkTime').prop('disabled', false);
            }
        });
    }

    $(document).ready(function () {
        // Bind all UI handlers FIRST so a failure in optional initialisation
        // (e.g. loading fingerprint devices) can never leave buttons dead.
        $('#tsLiveDeviceSelect').on('change', function () {
            tsLiveStopped = false;
            startTsLivePolling();
        });

        $('#tsPersonTypeFilter').on('change', function () {
            if (isTsLiveTabActive() && !tsLiveStopped) {
                pollTsDbInBackground();
            }
        });

        $('#tsStopLive').on('click', function () { stopTsLivePolling(true); });

        $('#tsSyncLiveNow').on('click', function () {
            tsManualSyncActive = true;
            loadTsLiveAttendance(true, false);
            setTimeout(function () { tsManualSyncActive = false; }, 3000);
        });

        $('#tsRefreshRecords').on('click', function () {
            loadTsSavedRecords();
        });

        $('#tsAttendanceTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetId = e.target ? e.target.id : '';
            if (targetId === 'ts-live-tab') {
                tsLiveStopped = false;
                startTsLivePolling();
            } else {
                stopTsLivePolling(false);
            }
            if (targetId === 'ts-records-tab') {
                loadTsSavedRecords();
            }
            if (targetId === 'ts-report-tab') {
                updateTsReportFilterVisibility();
            }
        });

        $('#tsReportFilterType').on('change', updateTsReportFilterVisibility);
        $('#tsGenerateReport').on('click', loadTsAttendanceReport);
        $(document).on('click', '.ts-action-view-more', function (e) {
            e.preventDefault();
            openTsReportDetail($(this).data('person-type'), $(this).data('person-id'));
        });
        $(document).on('click', '.ts-action-sms', function (e) {
            e.preventDefault();
            openTsSmsModal($(this).data('person-type'), $(this).data('person-id'), $(this));
        });
        $(document).on('click', '.ts-action-pdf', function (e) {
            e.preventDefault();
            if (!tsLastReportParams) return;
            var personType = $(this).data('person-type');
            var personId = $(this).data('person-id');
            var personName = ($(this).data('name') || 'Person').toString().replace(/[^A-Za-z0-9_\-]/g, '_');
            var url = '{{ route("attendance.teacher_staff_report_person_pdf") }}?' +
                buildTsReportQueryString({ person_type: personType, person_id: personId });
            downloadTsAttendancePdf(url, 'Generating Individual Attendance Report', 'Attendance_' + personName + '.pdf');
        });
        $('#tsDownloadAllReportPdf').on('click', function () {
            if (!tsLastReportParams) return;
            var url = '{{ route("attendance.teacher_staff_report_pdf") }}?' + buildTsReportQueryString();
            downloadTsAttendancePdf(url, 'Generating Full Attendance Report', 'Teacher_Staff_Attendance_Report.pdf');
        });
        $('#tsSendSmsBtn').on('click', sendTsAttendanceSms);
        $('#tsSaveWorkTime').on('click', saveTsWorkTime);

        // Non-critical initialisation, guarded so any runtime error here does
        // not stop the handlers above from working.
        try {
            $('#tsRecordsDateFilter').val('{{ date("Y-m-d") }}');
            updateTsReportFilterVisibility();
            loadTsSeenEventIds();
            loadFingerprintDevices();
        } catch (initErr) {
            if (window.console && console.error) {
                console.error('TS attendance init error:', initErr);
            }
        }
    });
})(window.jQuery);
</script>
