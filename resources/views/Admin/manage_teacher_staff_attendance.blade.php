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
    #tsReportTable_wrapper .dataTables_filter input { margin-left: 8px; }
    #tsReportDetailDailyTable thead th { background-color: #f8f9fa; white-space: nowrap; }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid" style="padding: 20px;">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2 class="section-header">
                <i class="bi bi-fingerprint"></i> Teacher and Staff Attendance
            </h2>
            <p class="text-muted mb-0">Fingerprint attendance kwa walimu na wafanyakazi wengine — sync kutoka ZKTeco device.</p>
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
            <a class="nav-link" id="ts-device-tab" data-toggle="tab" href="#ts-device" role="tab">
                <i class="bi bi-cloud-download"></i> Sync from Device
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
                        <button type="button" class="btn btn-primary-custom btn-block" id="tsSyncLiveNow" title="Force sync from device now">
                            <i class="bi bi-cloud-download"></i> Sync Now
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
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Live attendance updates zinapokelewa kutoka Local Agent (school LAN).
                    Ukipiga kidole kwenye kifaa, jina na status itaonekana hapa moja kwa moja.
                </div>
            </div>
        </div>

        <!-- Sync from Device -->
        <div class="tab-pane fade" id="ts-device" role="tabpanel">
            <div class="attendance-device-bar mb-3">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label for="tsSyncDeviceSelect" class="mb-1 text-primary-custom fw-bold">
                            <i class="bi bi-router"></i> Fingerprint Device
                        </label>
                        <select id="tsSyncDeviceSelect" class="form-control">
                            <option value="">Loading devices...</option>
                        </select>
                    </div>
                    <div class="col-md-7 d-flex gap-2 align-items-end">
                        <button type="button" class="btn btn-success flex-grow-1" id="tsSyncFromDevice">
                            <i class="bi bi-cloud-download"></i> Sync All from Device
                        </button>
                        <button type="button" class="btn btn-primary-custom flex-grow-1" id="tsRefreshRecordsQuick">
                            <i class="bi bi-arrow-repeat"></i> View Saved Records
                        </button>
                    </div>
                </div>
            </div>
            <div id="tsSyncResultContent">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Connect the device to the network, select a device, then click <strong>Sync All from Device</strong>.
                    The system will pull all logs from the device and save to the database only records that have not been synced before (teacher + date).
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
                            <option value="day">Day</option>
                            <option value="date_range">Date Range</option>
                            <option value="month" selected>Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportDayWrap" style="display:none;">
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
                    <div class="col-md-2 ts-report-filter" id="tsReportMonthWrap">
                        <label class="mb-1">Month</label>
                        <select id="tsReportMonth" class="form-control">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == (int)date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 ts-report-filter" id="tsReportYearWrap">
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
                    <i class="bi bi-info-circle"></i> Chagua filters hapo juu kisha bonyeza <strong>Generate Report</strong>.
                    Working days zinatoka kwenye school calendar (weekends, holidays, na non-working events).
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
(function () {
    var LIVE_DB_POLL_MS = 3000;
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

    function getCsrf() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function getSelectedLiveDeviceId() {
        return $('#tsLiveDeviceSelect').val() || '';
    }

    function getSelectedSyncDeviceId() {
        return $('#tsSyncDeviceSelect').val() || '';
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
            var syncCurrent = $('#tsSyncDeviceSelect').val();
            var options = '<option value="">-- Select device --</option>';

            devices = devices.filter(function (d) { return d.status !== 'inactive'; });

            if (!devices.length) {
                options = '<option value="">No active devices found</option>';
            } else {
                devices.forEach(function (d) {
                    options += '<option value="' + d.id + '">' + d.label + ' (' + d.ip + ':' + d.port + ')</option>';
                });
            }

            $('#tsLiveDeviceSelect, #tsSyncDeviceSelect').html(options);
            restoreOrDefault($('#tsLiveDeviceSelect'), liveCurrent);
            restoreOrDefault($('#tsSyncDeviceSelect'), syncCurrent);

            if (isTsLiveTabActive() && !tsLiveStopped) {
                startTsLivePolling();
            }
        }).fail(function () {
            $('#tsLiveDeviceSelect, #tsSyncDeviceSelect').html('<option value="">Failed to load devices</option>');
            $('#tsLiveContent').html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Failed to load fingerprint devices. Refresh page and try again.</div>');
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
        meta = meta || {};
        var msg = meta.deviceFailed
            ? 'Device haipatikani. Hakuna rekodi za leo kwenye database bado.'
            : 'Hakuna attendance ya leo bado. Subiri Local Agent itume data baada ya mtu kuweka kidole.';
        $('#tsLiveContent').html(
            '<div class="alert ' + (meta.deviceFailed ? 'alert-warning' : 'alert-info') + ' mb-0">' +
            '<i class="bi bi-info-circle"></i> ' + msg +
            '<div class="small text-muted mt-1">Date: ' + (date || '{{ date("Y-m-d") }}') + '</div></div>'
        );
    }

    function renderTsAttendanceTable(records, date, meta, container) {
        meta = meta || {};
        container = container || '#tsLiveContent';
        if (!records || !records.length) {
            if (container === '#tsLiveContent') {
                renderTsLiveEmpty(date, meta);
            } else {
                $(container).html('<div class="alert alert-info mb-0">No teacher attendance records found.</div>');
            }
            return;
        }

        var html = '<div class="table-responsive"><table class="table table-striped table-hover table-sm mb-0">' +
            '<thead class="bg-primary-custom text-white"><tr>' +
            '<th>#</th><th>Type</th><th>Name</th><th>Role</th><th>Emp. No</th><th>Fingerprint ID</th>' +
            '<th>Check In</th><th>Check Out</th><th>Status</th><th>Device IP</th><th>New</th>' +
            '</tr></thead><tbody>';

        records.forEach(function (rec, index) {
            var checkIn = formatTimeOnly(rec.check_in_time || '');
            var checkOut = formatTimeOnly(rec.check_out_time || '');
            var hasCheckOut = !!(rec.check_out_time || checkOut);
            var status = (hasCheckOut || rec.status === 'Complete' || rec.status === 1 || rec.status === '1')
                ? '<span class="badge bg-success">Complete</span>'
                : '<span class="badge bg-info">Check In Only</span>';
            var newBadge = rec.is_new ? '<span class="badge bg-success">New</span>' : '-';
            var persistedNote = rec.persisted === false ? ' <small class="text-muted">(live)</small>' : '';

            html += '<tr' + (rec.is_new ? ' class="table-success"' : '') + '>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + personTypeBadge(rec.person_type) + '</td>' +
                '<td>' + (rec.user_name || 'N/A') + persistedNote + '</td>' +
                '<td>' + (rec.role_label || '-') + '</td>' +
                '<td>' + (rec.employee_number || '-') + '</td>' +
                '<td>' + (rec.fingerprint_id || rec.enroll_id || '-') + '</td>' +
                '<td>' + (checkIn || '-') + '</td>' +
                '<td>' + (checkOut || '-') + '</td>' +
                '<td>' + status + '</td>' +
                '<td>' + (rec.device_ip || '-') + '</td>' +
                '<td>' + newBadge + '</td></tr>';
        });

        html += '</tbody></table></div>';
        html += '<div class="mt-2 small text-muted">Date: ' + date + ' &bullet; Records: ' + records.length;
        if (meta.source && container !== '#tsLiveContent') html += ' &bullet; ' + meta.source;
        html += '</div>';

        $(container).html(html);
        if (container === '#tsLiveContent') {
            tsLiveHasData = true;
            setLastUpdateLabel();
        }
    }

    function loadTsLiveAttendance(showLoader, syncFromDevice, onComplete) {
        if (typeof showLoader === 'undefined') showLoader = false;
        if (typeof syncFromDevice === 'undefined') syncFromDevice = false;

        var deviceId = getSelectedLiveDeviceId();
        var $container = $('#tsLiveContent');
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
            if (records.length || !isBackground || !hasTable || showLoader) {
                renderTsAttendanceTable(records, response.date, {
                    deviceFailed: response.device_failed
                });
                if (records.length) {
                    tsLiveHasData = true;
                }
            }

            if (!tsLiveStopped) updateLiveBadge('live');
            if (onComplete) onComplete(true);
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

        updateLiveBadge('live');

        loadTsLiveAttendance(true, false);

        tsLiveInterval = setInterval(function () {
            if (!isTsLiveTabActive() || tsLiveStopped) {
                return;
            }
            pollTsDbInBackground();
        }, LIVE_DB_POLL_MS);
    }

    function loadTsSavedRecords(page) {
        page = page || 1;
        var dateFilter = $('#tsRecordsDateFilter').val();
        var container = '#tsRecordsContent';

        $(container).html('<div class="text-center py-4"><div class="spinner-border text-primary-custom"></div><p class="mt-2 mb-0">Loading records...</p></div>');

        $.ajax({
            url: '{{ route("attendance.teacher_staff_fingerprint") }}',
            type: 'GET',
            data: { page: page, date: dateFilter },
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

                var pagination = data.pagination;
                if (pagination && pagination.last_page > 1) {
                    var pagHtml = '<div class="mt-3 d-flex justify-content-center gap-2">';
                    if (pagination.current_page > 1) {
                        pagHtml += '<button class="btn btn-sm btn-outline-secondary" onclick="window.tsLoadRecordsPage(' + (pagination.current_page - 1) + ')">Prev</button>';
                    }
                    pagHtml += '<span class="align-self-center small">Page ' + pagination.current_page + ' / ' + pagination.last_page + '</span>';
                    if (pagination.current_page < pagination.last_page) {
                        pagHtml += '<button class="btn btn-sm btn-outline-secondary" onclick="window.tsLoadRecordsPage(' + (pagination.current_page + 1) + ')">Next</button>';
                    }
                    pagHtml += '</div>';
                    $(container).append(pagHtml);
                }
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

    function initTsReportDataTable(showCompleteCol) {
        destroyTsReportDataTable();
        if (!$('#tsReportTable').length) return;

        var actionCol = showCompleteCol ? 9 : 8;
        var centerCols = showCompleteCol ? [0, 3, 4, 5, 6, 7, 8, 9] : [0, 3, 4, 5, 6, 7, 8];

        tsReportDataTable = $('#tsReportTable').DataTable({
            order: [[2, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            autoWidth: false,
            responsive: true,
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records found',
                infoFiltered: '(filtered from _MAX_ total)',
                zeroRecords: 'No matching records found',
                emptyTable: 'No attendance report data'
            },
            columnDefs: [
                { orderable: false, targets: [0, 1, actionCol] },
                { className: 'text-center align-middle', targets: centerCols },
                { className: 'align-middle', targets: '_all' }
            ],
            drawCallback: function () {
                var api = this.api();
                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1 + api.page.info().start;
                });
            }
        });
    }

    function initTsReportDetailDataTable() {
        destroyTsReportDetailDataTable();
        if (!$('#tsReportDetailDailyTable').length) return;

        tsReportDetailDataTable = $('#tsReportDetailDailyTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            autoWidth: false,
            responsive: true,
            language: {
                search: 'Search date:',
                lengthMenu: 'Show _MENU_ days',
                info: 'Showing _START_ to _END_ of _TOTAL_ days',
                zeroRecords: 'No matching days found'
            },
            columnDefs: [
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
            person_type: $('#tsReportPersonType').val()
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
                metaHtml += '<div class="col-md-3"><strong>Working Days (Month)</strong><br><span class="text-primary-custom fs-5">' + res.total_working_days + '</span></div>';
                if (res.period_incomplete && res.complete_working_days !== undefined) {
                    metaHtml += '<div class="col-md-3"><strong>Complete Working Days</strong><br><span class="fs-5 text-warning">' + res.complete_working_days + '</span><br><small class="text-muted">Siku za kazi zilizopita</small></div>';
                }
                metaHtml += '<div class="col-md-3"><strong>Official Time</strong><br>' + res.work_start_time + ' – ' + res.work_end_time + '</div>';
                metaHtml += '<div class="col-md-3"><strong>Period</strong><br>' + (res.period_incomplete ? '<span class="text-warning">In progress</span>' : 'Complete') + '</div>';
                metaHtml += '</div>';
                if (res.period_incomplete) {
                    metaHtml += '<p class="small text-muted mb-0 mt-2 text-center"><i class="bi bi-info-circle"></i> ' +
                        '<strong>Present</strong> = siku zote ulizoweka kidole (kama Saved Records). ' +
                        '<strong>Absent</strong> = siku za kazi zilizopita bila rekodi ya kidole kwenye database.</p>';
                }
                metaHtml += '</div></div>';
                $meta.html(metaHtml);

                var rows = res.data || [];
                if (!rows.length) {
                    $content.html('<div class="alert alert-info mb-0">No teachers or staff found for this filter.</div>');
                    return;
                }

                var showCompleteCol = !!res.period_incomplete;
                var html = '<div class="table-responsive"><table id="tsReportTable" class="table table-striped table-hover table-sm mb-0 w-100">' +
                    '<thead><tr>' +
                    '<th>#</th><th>Photo</th><th>Name</th><th>Present</th><th>Absent</th>' +
                    '<th>Working Days</th>';
                if (showCompleteCol) {
                    html += '<th>Complete Working Days</th>';
                }
                html += '<th>Late</th><th>Early</th><th>Action</th></tr></thead><tbody>';

                rows.forEach(function (row, i) {
                    var lateClass = row.late_days > 0 ? ' ts-late-cell' : '';
                    var typeBadge = row.person_type === 'staff'
                        ? '<span class="badge bg-info ms-1">Staff</span>'
                        : '<span class="badge bg-primary ms-1">Teacher</span>';
                    html += '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td><img src="' + row.image_url + '" class="ts-person-img" alt=""></td>' +
                        '<td>' + escapeHtml(row.name) + typeBadge + '</td>' +
                        '<td><span class="badge bg-success">' + row.present_days + '</span></td>' +
                        '<td><span class="badge bg-danger">' + row.absent_days + '</span></td>' +
                        '<td>' + row.total_working_days + '</td>';
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
                initTsReportDataTable(showCompleteCol);
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
                html += statBox('Absent', s.absent_days, '');
                html += statBox('Working Days', s.total_working_days, '');
                if (res.period_incomplete) {
                    html += statBox('Complete Working Days', s.complete_working_days || s.elapsed_working_days, '');
                }
                html += statBox('Late Days', s.late_days, 'late');
                html += statBox('Early Days', s.early_days, '');
                html += '</div>';

                if (res.period_incomplete) {
                    html += '<p class="small text-muted"><i class="bi bi-info-circle"></i> Absent = siku za kazi zilizopita bila rekodi ya kidole kwenye database.</p>';
                }

                html += '<p class="small text-muted">Official time: <strong>' + res.work_start_time + ' - ' + res.work_end_time + '</strong></p>';
                html += '<div class="table-responsive"><table id="tsReportDetailDailyTable" class="table table-sm table-bordered table-striped mb-0 w-100">' +
                    '<thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead><tbody>';

                (res.daily_records || []).forEach(function (day) {
                    var rowClass = day.is_late ? ' class="ts-row-late"' : '';
                    var statusBadge = day.status === 'Absent'
                        ? '<span class="badge bg-danger">Absent</span>'
                        : (day.status === 'Late'
                            ? '<span class="badge bg-danger">Late</span>'
                            : (day.status === 'Early'
                                ? '<span class="badge bg-info">Early</span>'
                                : (day.status === 'Present (Off Day)'
                                    ? '<span class="badge bg-secondary">Present (Off Day)</span>'
                                    : '<span class="badge bg-success">Present</span>')));
                    html += '<tr' + rowClass + '>' +
                        '<td>' + day.date_label + '</td>' +
                        '<td>' + (day.check_in_time || '-') + '</td>' +
                        '<td>' + (day.check_out_time || '-') + '</td>' +
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

    function buildDefaultTsSmsMessage(data) {
        var meta = tsLastReportMeta || {};
        var period = meta.period_label || '';
        var workStart = meta.work_start_time || '';
        var workEnd = meta.work_end_time || '';
        return 'Habari ' + (data.name || '') + ', ripoti ya mahudhurio (' + period + '): ' +
            'Present ' + (data.present || 0) + ', Absent ' + (data.absent || 0) + ', ' +
            'Late ' + (data.late || 0) + ', Early ' + (data.early || 0) + '. ' +
            'Siku za kazi (mwezi): ' + (meta.total_working_days || 0) + '. ' +
            (meta.period_incomplete ? ('Siku zilizopita: ' + (meta.complete_working_days || meta.elapsed_working_days || 0) + '. ') : '') +
            'Muda rasmi: ' + workStart + ' - ' + workEnd + '.';
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
                message: ($('#tsSmsMessage').val() || '').trim()
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

    function syncTsFromDevice() {
        var deviceId = getSelectedSyncDeviceId();
        if (!deviceId) {
            Swal.fire('Error', 'Please select a fingerprint device.', 'error');
            return;
        }
        var $btn = $('#tsSyncFromDevice');
        var original = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Syncing all logs...');

        $('#tsSyncResultContent').html(
            '<div class="alert alert-info mb-0"><i class="bi bi-hourglass-split"></i> ' +
            'Inasoma attendance zote kutoka device... Hii inaweza kuchukua dakika kadhaa ikiwa kuna logs nyingi.</div>'
        );

        $.ajax({
            url: '{{ route("zkteco.attendance.teacher-staff-sync") }}',
            type: 'POST',
            data: {
                _token: getCsrf(),
                device_id: deviceId
            },
            dataType: 'json',
            timeout: 300000,
            success: function (res) {
                var html = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle"></i> ' + (res.message || 'Sync completed.') + '</div>';
                if (res.synced !== undefined || res.skipped_existing !== undefined) {
                    html += '<div class="card mt-3"><div class="card-body py-3"><div class="row text-center">';
                    html += '<div class="col-md-3"><h5 class="text-success mb-0">' + (res.synced || 0) + '</h5><small class="text-muted">New saved</small></div>';
                    html += '<div class="col-md-3"><h5 class="text-primary mb-0">' + (res.updated_checkout || 0) + '</h5><small class="text-muted">Check-out updated</small></div>';
                    html += '<div class="col-md-3"><h5 class="text-secondary mb-0">' + (res.skipped_existing || 0) + '</h5><small class="text-muted">Already complete</small></div>';
                    html += '<div class="col-md-3"><h5 class="text-info mb-0">' + (res.device_days_matched || 0) + '</h5><small class="text-muted">Matched on device</small></div>';
                    html += '</div></div></div>';
                }
                $('#tsSyncResultContent').html(html);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Sync failed.';
                if (xhr.statusText === 'timeout') {
                    msg = 'Sync timed out. Device ina logs nyingi — jaribu tena au hakikisha device iko kwenye network.';
                }
                $('#tsSyncResultContent').html('<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> ' + msg + '</div>');
            },
            complete: function () {
                $btn.prop('disabled', false).html(original);
            }
        });
    }

    $(document).ready(function () {
        $('#tsRecordsDateFilter').val('{{ date("Y-m-d") }}');

        loadFingerprintDevices();

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

        $('#tsSyncFromDevice').on('click', syncTsFromDevice);
        $('#tsRefreshRecords, #tsRefreshRecordsQuick').on('click', function () {
            $('a[href="#ts-records"]').tab('show');
            loadTsSavedRecords(1);
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
                loadTsSavedRecords(1);
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

        updateTsReportFilterVisibility();
    });
})();
</script>
