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
    .section-header { font-size: 1.5rem; font-weight: 600; color: #212529; }
    .section-header i { color: var(--primary-color); margin-right: 8px; }
    .bg-primary-custom { background-color: var(--primary-color) !important; }
    .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .stat-card { border-radius: 6px; border: 1px solid #e9ecef; background: #fff; padding: 16px; text-align: center; }
    .stat-card h4 { color: var(--primary-color); margin-bottom: 4px; font-weight: 700; }
    .nav-tabs .nav-link.active { color: var(--primary-color); font-weight: 600; }
    .pulse-animation { animation: pulse 1s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    .attendance-device-bar { background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%); border: 1px solid #f0d0d0; border-radius: 6px; padding: 16px; }
    .sf-report-title { font-size: 1.05rem; font-weight: 700; color: var(--primary-color); text-transform: uppercase; letter-spacing: 0.3px; }
    .sf-late-cell { color: #dc3545 !important; font-weight: 600; }
    #sfReportTable thead th { background-color: var(--primary-color) !important; color: #fff !important; white-space: nowrap; }
</style>

@include('partials.fingerprint_attendance_grid_list')

<div class="container-fluid" style="padding: 20px;">
    @include('partials.fingerprint_setup_download')

    <div class="row mb-3">
        <div class="col-md-8">
            <h2 class="section-header"><i class="bi bi-fingerprint"></i> Student Fingerprint Attendance</h2>
            <p class="text-muted mb-0">Live na rekodi za wanafunzi, data inapokelewa kutoka Local Agent (school LAN).</p>
            <a href="{{ route('manageAttendance') }}" class="small"><i class="bi bi-arrow-left"></i> Back to Attendance</a>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h4>{{ $studentCount ?? 0 }}</h4>
                <small class="text-muted">Active Students</small>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="sfTabs">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#sf-live"><i class="bi bi-broadcast"></i> Live Today</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sf-records"><i class="bi bi-list-ul"></i> Saved Records</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sf-report"><i class="bi bi-bar-chart"></i> Report</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sf-worktime"><i class="bi bi-clock"></i> Working Time</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="sf-live">
            <div class="attendance-device-bar mb-3">
                <div class="row align-items-end g-2">
                    <div class="col-md-3">
                        <label class="mb-1">Filter Class</label>
                        <select id="sfLiveClassFilter" class="form-control">
                            <option value="">All Classes</option>
                            @foreach($subclasses as $sc)
                            <option value="{{ $sc->subclassID }}">{{ $sc->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary-custom btn-block" id="sfRefreshLive"><i class="bi bi-arrow-repeat"></i> Refresh</button>
                    </div>
                    <div class="col-md-7 text-end">
                        <span class="badge bg-secondary" id="sfLiveBadge"><i class="bi bi-circle-fill"></i> Waiting Local Agent</span>
                        <small class="text-muted ms-2" id="sfLiveUpdated"></small>
                    </div>
                </div>
            </div>
            <div id="sfLiveArea">
                <div class="alert alert-info mb-0">Live updates from Local Agent. Select a class to filter, or leave All Classes.</div>
            </div>
        </div>

        <div class="tab-pane fade" id="sf-records">
            <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                <select id="sfRecordsClassFilter" class="form-control" style="max-width:260px;">
                    <option value="">All Classes</option>
                    @foreach($subclasses as $sc)
                    <option value="{{ $sc->subclassID }}">{{ $sc->display_name }}</option>
                    @endforeach
                </select>
                <input type="date" id="sfRecordsDate" class="form-control" style="max-width:180px;" value="{{ date('Y-m-d') }}">
                <button type="button" class="btn btn-primary-custom btn-sm" id="sfLoadRecords"><i class="bi bi-arrow-repeat"></i> Refresh</button>
            </div>
            <div id="sfRecordsArea"><div class="alert alert-info mb-0">Click Refresh to load saved records.</div></div>
        </div>

        <div class="tab-pane fade" id="sf-report">
            <div class="attendance-device-bar mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="mb-1 fw-bold text-primary-custom">Report Type</label>
                        <select id="sfReportFilterType" class="form-control">
                            <option value="day" selected>Day</option>
                            <option value="date_range">Date Range</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>
                    <div class="col-md-2 sf-report-filter" id="sfReportDayWrap">
                        <label class="mb-1">Date</label>
                        <input type="date" id="sfReportDate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 sf-report-filter" id="sfReportFromWrap" style="display:none;">
                        <label class="mb-1">From Date</label>
                        <input type="date" id="sfReportFromDate" class="form-control">
                    </div>
                    <div class="col-md-2 sf-report-filter" id="sfReportToWrap" style="display:none;">
                        <label class="mb-1">To Date</label>
                        <input type="date" id="sfReportToDate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 sf-report-filter" id="sfReportMonthWrap" style="display:none;">
                        <label class="mb-1">Month</label>
                        <select id="sfReportMonth" class="form-control">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == (int)date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 sf-report-filter" id="sfReportYearWrap" style="display:none;">
                        <label class="mb-1">Year</label>
                        <input type="number" id="sfReportYear" class="form-control" value="{{ date('Y') }}" min="2000" max="2100">
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Main Class</label>
                        <select id="sfReportMainClass" class="form-control">
                            <option value="">All Main Classes</option>
                            @foreach($mainClasses as $mc)
                            <option value="{{ $mc->classID }}">{{ $mc->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Subclass</label>
                        <select id="sfReportSubclass" class="form-control">
                            <option value="">All Subclasses</option>
                            @foreach($subclasses as $sc)
                            <option value="{{ $sc->subclassID }}" data-class-id="{{ $sc->classID }}">{{ $sc->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 fw-bold text-primary-custom">Status</label>
                        <select id="sfReportStatus" class="form-control">
                            <option value="all" selected>All</option>
                            <option value="Present">Present only</option>
                            <option value="Absent">Absent only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary-custom btn-block" id="sfGenerateReport">
                            <i class="bi bi-funnel"></i> Generate Report
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-block" id="sfDownloadReportPdf" disabled>
                            <i class="bi bi-file-pdf"></i> Download PDF
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary-custom btn-block" id="sfSendStatusFilterSmsBtn" style="display:none;" disabled>
                            <i class="bi bi-chat-dots"></i> Send SMS
                        </button>
                    </div>
                </div>
            </div>
            <div id="sfReportMeta" class="mb-2"></div>
            <div id="sfFingerprintSmsBar" class="alert alert-light border mb-3 py-2 px-3 d-none">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small">
                        <strong><i class="bi bi-chat-dots"></i> Fingerprint Attendance SMS</strong>
                        <span id="sfFpSmsScheduleInfo" class="text-muted ms-1">Loading schedule...</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary-custom" id="sfSendFingerprintSmsBtn" disabled>
                        <i class="bi bi-send"></i> Send SMS to Parents
                    </button>
                </div>
                <div id="sfFpSmsStatus" class="small mt-1 text-muted"></div>
            </div>
            <div id="sfReportArea">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Chagua filters hapo juu (Day / Date Range / Month / Year + Main Class / Subclass) kisha bonyeza <strong>Generate Report</strong>.
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="sf-worktime">
            <div class="card" style="max-width:480px;">
                <div class="card-body">
                    <h6 class="text-primary-custom"><i class="bi bi-clock"></i> Student Working Time</h6>
                    <p class="small text-muted">Muda wa kuingia shule kwa wanafunzi (tofauti na walimu).</p>
                    <div class="mb-3">
                        <label>School Start Time</label>
                        <input type="time" id="sfWorkStart" class="form-control" value="{{ substr($workTime->work_start_time ?? '07:30:00', 0, 5) }}">
                    </div>
                    <div class="mb-3">
                        <label>School End Time</label>
                        <input type="time" id="sfWorkEnd" class="form-control" value="{{ substr($workTime->work_end_time ?? '14:30:00', 0, 5) }}">
                    </div>
                    <button type="button" class="btn btn-primary-custom" id="sfSaveWorkTime"><i class="bi bi-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PDF Download Progress Modal -->
<div class="modal fade" id="sfDownloadProgressModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-4 text-primary-custom fw-bold" id="sfPdfProgressTitle">Generating Student Attendance Report</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 6px;">
                    <div id="sfPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                        role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="sfPdfProgressPercent" class="fw-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small mb-0">Please wait, this might take a moment for large reports.</p>
            </div>
        </div>
    </div>
</div>

@include('partials.fingerprint_attendance_sms_modal')

<!-- Fingerprint status-filter SMS modal -->
<div class="modal fade" id="sfStatusSmsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-chat-dots"></i> Send SMS, Fingerprint Status Filter</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="sfStatusSmsDisabled" class="alert alert-warning d-none"></div>
                <div id="sfStatusSmsContent" class="d-none">
                    <div class="alert alert-info mb-3">
                        <div><strong>Status:</strong> <span id="sfStatusSmsStatusLabel">-</span></div>
                        <div><strong>Period:</strong> <span id="sfStatusSmsPeriodLabel">-</span></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold mb-0">SMS Sample</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="sfStatusSmsReset">Reset</button>
                        </div>
                        <div class="small text-muted mb-2">Hariri maandishi. Values zilizofungwa (njano) haziwezi kuhaririwa.</div>
                        <div id="sfStatusSmsEditor" class="form-control" contenteditable="true" style="min-height:130px;white-space:pre-wrap;border-radius:0;"></div>
                        <style>#sfStatusSmsEditor .sms-locked{background:#fff3cd;border:1px solid #f0d78c;color:#7a5b00;padding:0 4px;font-weight:600;user-select:none;}</style>
                        <div id="sfStatusSmsTemplateError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sfStatusSmsSelectAll">
                            <label class="custom-control-label font-weight-bold" for="sfStatusSmsSelectAll">Select All</label>
                        </div>
                        <span class="badge badge-success" id="sfStatusSmsCount">Receivers: 0</span>
                    </div>
                    <div class="table-responsive" style="max-height:280px;">
                        <table class="table table-sm table-hover">
                            <thead class="bg-light sticky-top"><tr><th></th><th>Parent Of</th><th>Days</th><th>Phone</th><th>Status</th></tr></thead>
                            <tbody id="sfStatusSmsList"></tbody>
                        </table>
                    </div>
                    <div id="sfStatusSmsProgress" class="mt-3 d-none">
                        <div class="progress" style="height:20px;"><div id="sfStatusSmsBar" class="progress-bar bg-success" style="width:0%">0%</div></div>
                        <div id="sfStatusSmsSummary" class="small text-center mt-1"></div>
                    </div>
                </div>
                <div id="sfStatusSmsLoading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="sfStatusSmsCancel">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="sfStatusSmsStart" disabled><i class="bi bi-send"></i> Start Sending SMS</button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
(function ($) {
    var sfPollTimer = null;
    var sfLiveActive = false;
    var sfReportDataTable = null;
    var sfAllSubclassOptions = [];
    var sfLastReportParams = null;
    var sfLastReportRows = [];
    var sfLastReportReferenceDate = null;

    function escapeHtml(v) {
        return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function formatAmPm(t) {
        if (!t) return '';
        var p = String(t).split(':');
        if (p.length < 2) return t;
        var h = parseInt(p[0], 10);
        var m = p[1];
        var ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ap;
    }

    function fmtTime(dt) {
        if (!dt) return '-';
        var p = String(dt).split(' ');
        return p.length === 2 ? p[1] : dt;
    }

    function formatGender(g) {
        if (!g) return '-';
        var v = String(g).trim().toLowerCase();
        if (v === 'male' || v === 'm') return 'Male';
        if (v === 'female' || v === 'f') return 'Female';
        return g.charAt(0).toUpperCase() + g.slice(1);
    }

    function studentFpStatusBadge(r) {
        var s = r.attendance_status || r.status || '';
        if (s === 'Permission') return '<span class="badge bg-warning text-dark">Permission</span>';
        if (s === 'Sick') return '<span class="badge bg-secondary">Sick</span>';
        if (s === 'Present') return '<span class="badge bg-success">Present</span>';
        if (s === 'Absent') return '<span class="badge bg-danger">Absent</span>';
        if (r.check_out_time || s === 'Complete') return '<span class="badge bg-success">Complete</span>';
        return '<span class="badge bg-info">Check In Only</span>';
    }

    function renderLiveTable(records, date) {
        if (!records.length) {
            $('#sfLiveArea').html('<div class="alert alert-info mb-0">No student attendance for today yet.</div>');
            return;
        }
        var tableHtml = '<div class="table-responsive"><table id="sfLiveTable" class="table table-sm table-striped"><thead class="bg-primary-custom text-white"><tr>' +
            '<th>#</th><th>Class</th><th>Name</th><th>Gender</th><th>Fingerprint ID</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Device</th></tr></thead><tbody>';
        records.forEach(function (r, i) {
            var st = studentFpStatusBadge(r);
            tableHtml += '<tr' + (r.is_new ? ' class="table-success"' : '') + '><td>' + (i+1) + '</td><td>' + (r.class_name||'-') + '</td><td>' + (r.user_name||'-') + '</td><td>' + formatGender(r.gender) + '</td><td>' + (r.fingerprint_id||r.studentID||'-') + '</td><td>' + fmtTime(r.check_in_time) + '</td><td>' + fmtTime(r.check_out_time) + '</td><td>' + st + '</td><td>' + (r.device_ip||'-') + '</td></tr>';
        });
        tableHtml += '</tbody></table></div>';
        var footer = '<div class="small text-muted">Date: ' + date + ' &bull; ' + records.length + ' record(s)</div>';
        if (window.FpAttView) {
            FpAttView.mount('#sfLiveArea', tableHtml, 'sfLiveTable', footer);
        } else {
            $('#sfLiveArea').html(tableHtml + footer);
        }
    }

    function loadLive(showLoader) {
        var params = {};
        var subclassID = $('#sfLiveClassFilter').val();
        if (subclassID) params.subclassID = subclassID;

        if (showLoader && !$('#sfLiveArea table').length) {
            $('#sfLiveArea').html('<div class="text-center py-4"><div class="spinner-border text-danger"></div></div>');
        }

        $.get('{{ route("zkteco.attendance.student-live-today-from-db") }}', params)
            .done(function (res) {
                if (!res.success) {
                    $('#sfLiveArea').html('<div class="alert alert-danger mb-0">' + (res.message||'Failed') + '</div>');
                    return;
                }
                renderLiveTable(res.data || [], res.date || '{{ date("Y-m-d") }}');
                $('#sfLiveBadge').removeClass('bg-secondary').addClass('bg-success').html('<i class="bi bi-circle-fill pulse-animation"></i> Live');
                $('#sfLiveUpdated').text('Updated ' + new Date().toLocaleTimeString());
            })
            .fail(function () {
                $('#sfLiveArea').html('<div class="alert alert-danger mb-0">Error loading live data.</div>');
            });
    }

    function startLivePoll() {
        sfLiveActive = true;
        loadLive(true);
        if (sfPollTimer) clearInterval(sfPollTimer);
        sfPollTimer = setInterval(function () {
            if (sfLiveActive && $('#sf-live').hasClass('active')) loadLive(false);
        }, 5000);
    }

    function stopLivePoll() {
        sfLiveActive = false;
        if (sfPollTimer) clearInterval(sfPollTimer);
        sfPollTimer = null;
    }

    function loadRecords(page) {
        page = page || 1;
        $('#sfRecordsArea').html('<div class="text-center py-3"><div class="spinner-border text-danger"></div></div>');
        $.get('{{ route("attendance.student_fingerprint_saved") }}', {
            page: page,
            date: $('#sfRecordsDate').val(),
            subclassID: $('#sfRecordsClassFilter').val()
        }).done(function (res) {
            if (!res.success || !res.data.length) {
                $('#sfRecordsArea').html('<div class="alert alert-info mb-0">No records found.</div>');
                return;
            }
            var tableHtml = '<div class="table-responsive"><table id="sfRecordsTable" class="table table-sm table-striped"><thead class="bg-primary-custom text-white"><tr>' +
                '<th>Date</th><th>Class</th><th>Name</th><th>Gender</th><th>Check In</th><th>Check Out</th><th>Device</th></tr></thead><tbody>';
            res.data.forEach(function (r) {
                tableHtml += '<tr><td>' + (r.attendance_date||'-') + '</td><td>' + (r.class_name||'-') + '</td><td>' + (r.user_name||'-') + '</td><td>' + formatGender(r.gender) + '</td><td>' + fmtTime(r.check_in_time) + '</td><td>' + fmtTime(r.check_out_time) + '</td><td>' + (r.device_ip||'-') + '</td></tr>';
            });
            tableHtml += '</tbody></table></div>';
            if (window.FpAttView) {
                FpAttView.mount('#sfRecordsArea', tableHtml, 'sfRecordsTable');
            } else {
                $('#sfRecordsArea').html(tableHtml);
            }
        }).fail(function () {
            $('#sfRecordsArea').html('<div class="alert alert-danger mb-0">Error loading records.</div>');
        });
    }

    function updateSfReportFilterVisibility() {
        var type = $('#sfReportFilterType').val();
        $('.sf-report-filter').hide();
        if (type === 'day') {
            $('#sfReportDayWrap').show();
        } else if (type === 'date_range') {
            $('#sfReportFromWrap, #sfReportToWrap').show();
        } else if (type === 'month') {
            $('#sfReportMonthWrap, #sfReportYearWrap').show();
        } else if (type === 'year') {
            $('#sfReportYearWrap').show();
        }
    }

    function filterSfSubclassOptions() {
        var classId = $('#sfReportMainClass').val();
        var $sub = $('#sfReportSubclass');
        var current = $sub.val();
        $sub.html('<option value="">All Subclasses</option>');
        sfAllSubclassOptions.forEach(function (opt) {
            if (!classId || String(opt.classID) === String(classId)) {
                $sub.append('<option value="' + opt.subclassID + '" data-class-id="' + opt.classID + '">' + escapeHtml(opt.display_name) + '</option>');
            }
        });
        if (current && $sub.find('option[value="' + current + '"]').length) {
            $sub.val(current);
        }
    }

    function getSfReportParams() {
        var type = $('#sfReportFilterType').val();
        var params = {
            filter_type: type,
            classID: $('#sfReportMainClass').val() || '',
            subclassID: $('#sfReportSubclass').val() || '',
            attendance_status: $('#sfReportStatus').val() || 'all'
        };
        if (type === 'day') {
            params.date = $('#sfReportDate').val();
        } else if (type === 'date_range') {
            params.from_date = $('#sfReportFromDate').val();
            params.to_date = $('#sfReportToDate').val();
        } else if (type === 'month') {
            params.month = $('#sfReportMonth').val();
            params.year = $('#sfReportYear').val();
        } else if (type === 'year') {
            params.year = $('#sfReportYear').val();
        }
        return params;
    }

    function destroySfReportDataTable() {
        if (sfReportDataTable) {
            try { sfReportDataTable.destroy(); } catch (e) {}
            sfReportDataTable = null;
        } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sfReportTable')) {
            $('#sfReportTable').DataTable().destroy();
        }
    }

    function initSfReportDataTable(showCompleteCol) {
        destroySfReportDataTable();
        if (!$('#sfReportTable').length) return;
        var centerCols = showCompleteCol ? [0, 4, 5, 6, 7, 8, 9, 10, 11, 12] : [0, 4, 5, 6, 7, 8, 9, 10, 11];
        sfReportDataTable = $('#sfReportTable').DataTable({
            order: [[3, 'asc'], [2, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            autoWidth: false,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [0, 1] },
                { className: 'text-center align-middle', targets: centerCols }
            ],
            drawCallback: function () {
                var api = this.api();
                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1 + api.page.info().start;
                });
                if (window.FpAttView) FpAttView.refreshGrid('sfReportTable');
            }
        });
        if (window.FpAttView) FpAttView.bindDataTable('sfReportTable', sfReportDataTable);
    }

    function loadReport() {
        var params = getSfReportParams();
        $('#sfReportArea').html('<div class="text-center py-4"><div class="spinner-border text-danger"></div><p class="mt-2 mb-0">Generating report...</p></div>');
        $('#sfReportMeta').empty();
        destroySfReportDataTable();
        $('#sfDownloadReportPdf').prop('disabled', true);
        sfLastReportParams = null;

        $.get('{{ route("attendance.student_fingerprint_report") }}', params)
            .done(function (res) {
                if (!res.success) {
                    $('#sfReportArea').html('<div class="alert alert-danger mb-0">' + escapeHtml(res.message || 'Failed') + '</div>');
                    return;
                }

                sfLastReportParams = params;
                sfLastReportRows = res.data || [];
                sfLastReportReferenceDate = resolveSfReportReferenceDate(params);

                var metaHtml = '<div class="sf-report-title text-center mb-2">' + escapeHtml(res.title || 'REPORT') + '</div>';
                metaHtml += '<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2 px-3"><div class="row text-center small g-2">';
                metaHtml += '<div class="col-md-3"><strong>Working Days</strong><br><span class="text-primary-custom fs-5">' + (res.total_working_days || 0) + '</span></div>';
                if (res.period_incomplete && res.complete_working_days !== undefined) {
                    metaHtml += '<div class="col-md-3"><strong>Complete W/days</strong><br><span class="fs-5 text-warning">' + res.complete_working_days + '</span></div>';
                }
                metaHtml += '<div class="col-md-3"><strong>Official Time</strong><br>' + formatAmPm(res.work_start_time) + ' – ' + formatAmPm(res.work_end_time) + '</div>';
                metaHtml += '<div class="col-md-3"><strong>Period</strong><br>' + (res.period_incomplete ? '<span class="text-warning">In progress</span>' : 'Complete') + '</div>';
                metaHtml += '</div>';

                if (res.filter_type === 'day' && res.gender_totals) {
                    var gt = res.gender_totals;
                    metaHtml += '<div class="row text-center small g-2 mt-2 pt-2 border-top">';
                    metaHtml += '<div class="col"><strong>Male Present</strong><br>' + (gt.male_present || 0) + '</div>';
                    metaHtml += '<div class="col"><strong>Female Present</strong><br>' + (gt.female_present || 0) + '</div>';
                    metaHtml += '<div class="col"><strong>Male Absent</strong><br>' + (gt.male_absent || 0) + '</div>';
                    metaHtml += '<div class="col"><strong>Female Absent</strong><br>' + (gt.female_absent || 0) + '</div>';
                    metaHtml += '</div>';
                }
                if (res.attendance_overview) {
                    var ov = res.attendance_overview;
                    metaHtml += '<div class="row text-center small g-2 mt-2 pt-2 border-top">';
                    metaHtml += '<div class="col"><strong>Late</strong><br><span class="text-danger fs-5">' + (ov.late || 0) + '</span></div>';
                    metaHtml += '<div class="col"><strong>Early Arrival</strong><br><span class="text-success fs-5">' + (ov.early_arrival || 0) + '</span></div>';
                    metaHtml += '<div class="col"><strong>Early Departure</strong><br><span class="text-warning fs-5">' + (ov.early_departure || 0) + '</span></div>';
                    metaHtml += '<div class="col"><strong>No Sign Out</strong><br><span class="text-secondary fs-5">' + (ov.no_checkout || 0) + '</span></div>';
                    metaHtml += '</div>';
                }
                metaHtml += '</div></div>';
                $('#sfReportMeta').html(metaHtml);

                var rows = res.data || [];
                var statusFilter = params.attendance_status || 'all';
                if (statusFilter === 'Present') {
                    rows = rows.filter(function (r) { return Number(r.present_days || 0) > 0; });
                } else if (statusFilter === 'Absent') {
                    rows = rows.filter(function (r) { return Number(r.absent_days || 0) > 0; });
                }
                sfLastReportRows = rows;

                var statusSmsBtn = $('#sfSendStatusFilterSmsBtn');
                if (statusFilter !== 'all' && rows.length) {
                    statusSmsBtn.show().prop('disabled', false);
                } else {
                    statusSmsBtn.hide().prop('disabled', true);
                }

                if (!rows.length) {
                    $('#sfReportArea').html('<div class="alert alert-info mb-0">No students found for this filter.</div>');
                    return;
                }

                var showCompleteCol = !!res.period_incomplete;
                var tableHtml = '<div class="table-responsive"><table id="sfReportTable" class="table table-striped table-hover table-sm mb-0 w-100"><thead class="bg-primary-custom text-white"><tr>' +
                    '<th>#</th><th>Photo</th><th>Name</th><th>Class</th><th>Gender</th><th>Present</th><th>Absent</th><th>Working Days</th>';
                if (showCompleteCol) tableHtml += '<th>Complete W/days</th>';
                tableHtml += '<th>Late</th><th>Early Arrival</th><th>Early Departure</th><th>No Sign Out</th></tr></thead><tbody>';

                rows.forEach(function (row) {
                    var lateClass = row.late_days > 0 ? ' sf-late-cell' : '';
                    var noOutClass = (row.no_checkout_days || 0) > 0 ? ' table-warning' : '';
                    tableHtml += '<tr class="' + noOutClass + '">' +
                        '<td></td>' +
                        '<td><img src="' + row.image_url + '" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt=""></td>' +
                        '<td>' + escapeHtml(row.name) + '</td>' +
                        '<td>' + escapeHtml(row.class_name) + '</td>' +
                        '<td>' + escapeHtml(formatGender(row.gender)) + '</td>' +
                        '<td><span class="badge bg-success">' + row.present_days + '</span></td>' +
                        '<td><span class="badge bg-danger">' + row.absent_days + '</span></td>' +
                        '<td>' + row.total_working_days + '</td>';
                    if (showCompleteCol) {
                        tableHtml += '<td><span class="badge bg-warning text-dark">' + (row.complete_working_days || 0) + '</span></td>';
                    }
                    tableHtml += '<td class="' + lateClass + '">' + row.late_days + '</td>' +
                        '<td>' + (row.early_arrival_days || row.early_days || 0) + '</td>' +
                        '<td>' + (row.early_departure_days || 0) + '</td>' +
                        '<td>' + (row.no_checkout_days || 0) + '</td></tr>';
                });
                tableHtml += '</tbody></table></div>';
                if (window.FpAttView) {
                    FpAttView.mount('#sfReportArea', tableHtml, 'sfReportTable');
                } else {
                    $('#sfReportArea').html(tableHtml);
                }
                initSfReportDataTable(showCompleteCol);
                $('#sfDownloadReportPdf').prop('disabled', false);
                loadSfFingerprintSmsSchedule();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error generating report.';
                $('#sfReportArea').html('<div class="alert alert-danger mb-0">' + escapeHtml(msg) + '</div>');
            });
    }

    function resolveSfReportReferenceDate(params) {
        if (params.filter_type === 'day' && params.date) return params.date;
        if (params.filter_type === 'date_range' && params.to_date) return params.to_date;
        if (params.filter_type === 'month') {
            var y = params.year || new Date().getFullYear();
            var m = String(params.month || (new Date().getMonth() + 1)).padStart(2, '0');
            return y + '-' + m + '-01';
        }
        if (params.filter_type === 'year') return (params.year || new Date().getFullYear()) + '-01-01';
        return new Date().toISOString().slice(0, 10);
    }

    function loadSfFingerprintSmsSchedule() {
        $('#sfFingerprintSmsBar').removeClass('d-none');
        $('#sfSendFingerprintSmsBtn').prop('disabled', !sfLastReportRows.length);

        $.get('{{ route("fingerprint_attendance_sms.schedule_info") }}', {
            reference_date: sfLastReportReferenceDate,
            filter_type: sfLastReportParams ? sfLastReportParams.filter_type : ''
        })
            .done(function (info) {
                if (!info.success) {
                    $('#sfFpSmsScheduleInfo').text('Manual send for this report period.');
                    return;
                }
                var label = '';
                if (!info.configured) {
                    label = 'Manual send (report period) · Auto schedule not set in Super Admin';
                } else {
                    label = info.auto_summary || info.schedule_label || '';
                    if (info.auto_enabled) {
                        label += ' · Manual still available';
                    }
                }
                $('#sfFpSmsScheduleInfo').text(label + ' · Period: ' + (info.period_label || ''));
            })
            .fail(function () {
                $('#sfFpSmsScheduleInfo').text('Manual send available for this report.');
            });
    }

    function buildSfReportQueryString() {
        return $.param(sfLastReportParams || {});
    }

    function downloadSfAttendancePdf(url, title, filename) {
        $('#sfPdfProgressTitle').text(title || 'Generating Student Attendance Report');
        $('#sfDownloadProgressModal').modal('show');
        $('#sfPdfProgressBar').css('width', '0%');
        $('#sfPdfProgressPercent').text('0%');

        var progress = 0;
        var interval = setInterval(function () {
            if (progress < 95) {
                var inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                if (progress > 80) inc = 0.5 + Math.random();
                progress += inc;
                if (progress > 95) progress = 95;
                $('#sfPdfProgressBar').css('width', progress + '%');
                $('#sfPdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 400);

        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error('Server error: ' + response.statusText);
                return response.blob();
            })
            .then(function (blob) {
                clearInterval(interval);
                $('#sfPdfProgressBar').css('width', '100%');
                $('#sfPdfProgressPercent').text('100%');

                setTimeout(function () {
                    var downloadUrl = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = filename || ('Student_Fingerprint_Attendance_Report_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.pdf');
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);

                    setTimeout(function () {
                        $('#sfDownloadProgressModal').modal('hide');
                        setTimeout(function () {
                            $('#sfPdfProgressBar').css('width', '0%');
                            $('#sfPdfProgressPercent').text('0%');
                        }, 500);
                    }, 800);
                }, 400);
            })
            .catch(function (error) {
                clearInterval(interval);
                $('#sfDownloadProgressModal').modal('hide');
                alert('Failed to generate PDF: ' + error.message);
            });
    }

    sfAllSubclassOptions = {!! json_encode($subclasses->map(function ($s) {
        return ['subclassID' => $s->subclassID, 'classID' => $s->classID, 'display_name' => $s->display_name];
    })->values()) !!};

    updateSfReportFilterVisibility();
    $('#sfReportFilterType').on('change', updateSfReportFilterVisibility);
    $('#sfReportMainClass').on('change', function () {
        filterSfSubclassOptions();
    });

    $('#sfRefreshLive, #sfLiveClassFilter').on('click change', function (e) {
        if (e.type === 'change' || e.target.id === 'sfRefreshLive') loadLive(true);
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#sf-live') startLivePoll();
        else stopLivePoll();
        if (target === '#sf-records') loadRecords();
    });

    $('#sfLoadRecords').on('click', function () { loadRecords(); });
    $('#sfGenerateReport').on('click', loadReport);
    $('#sfDownloadReportPdf').on('click', function () {
        if (!sfLastReportParams) return;
        var url = '{{ route("attendance.student_fingerprint_report_pdf") }}?' + buildSfReportQueryString();
        downloadSfAttendancePdf(url, 'Generating Student Fingerprint Attendance Report', 'Student_Fingerprint_Attendance_Report.pdf');
    });
    $('#sfSendFingerprintSmsBtn').on('click', function () {
        if (!sfLastReportRows.length || typeof window.openFingerprintAttendanceSmsModal !== 'function') return;
        var opts = {
            referenceDate: sfLastReportReferenceDate,
            filterType: sfLastReportParams ? sfLastReportParams.filter_type : 'day',
            studentIds: sfLastReportRows.map(function (r) { return r.student_id; }),
        };
        if (sfLastReportParams) {
            if (sfLastReportParams.subclassID) opts.subclassID = sfLastReportParams.subclassID;
            if (sfLastReportParams.classID) opts.classID = sfLastReportParams.classID;
        }
        window.openFingerprintAttendanceSmsModal(opts);
    });

    // Status-filter SMS (Present/Absent only)
    var sfStatusSmsSending = false;
    var sfStatusSmsSample = null;
    var sfStatusSmsFilter = null;

    function sfEsc(str) {
        return String(str == null ? '' : str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function sfLocked(token, value) {
        return '<span class="sms-locked" contenteditable="false" data-token="' + sfEsc(token) + '">' + sfEsc(value).replace(/\n/g,'<br>') + '</span>';
    }
    function sfRenderSample(sample) {
        var editor = document.getElementById('sfStatusSmsEditor');
        if (!editor || !sample) return;
        var tokens = sample.sample_tokens || {};
        var html = sfEsc(sample.template || '');
        Object.keys(tokens).forEach(function (k) {
            html = html.split(sfEsc('{' + k + '}')).join(sfLocked(k, tokens[k]));
        });
        editor.innerHTML = html.replace(/\n/g, '<br>');
        $('#sfStatusSmsTemplateError').addClass('d-none');
    }
    function sfSerializeTemplate() {
        var editor = document.getElementById('sfStatusSmsEditor');
        if (!editor) return '';
        function walk(node) {
            var out = '';
            node.childNodes.forEach(function (child) {
                if (child.nodeType === Node.TEXT_NODE) { out += child.nodeValue; return; }
                if (child.nodeType !== Node.ELEMENT_NODE) return;
                if (child.classList && child.classList.contains('sms-locked')) {
                    out += '{' + (child.getAttribute('data-token') || '') + '}';
                    return;
                }
                var tag = child.tagName.toLowerCase();
                if (tag === 'br') { out += '\n'; return; }
                if (tag === 'div' || tag === 'p') {
                    if (out && !out.endsWith('\n')) out += '\n';
                    out += walk(child);
                    if (!out.endsWith('\n')) out += '\n';
                    return;
                }
                out += walk(child);
            });
            return out;
        }
        return walk(editor).replace(/\n{3,}/g, '\n\n').trim();
    }
    function sfValidateTemplate(tpl) {
        var required = (sfStatusSmsSample && sfStatusSmsSample.required_tokens) || ['STUDENT_NAME','PERIOD','STATUS_LINE','DAY_LIST'];
        var missing = required.filter(function (t) { return tpl.indexOf('{' + t + '}') === -1; });
        if (missing.length) {
            $('#sfStatusSmsTemplateError').removeClass('d-none').text('Usifute values: ' + missing.map(function (t){return '{'+t+'}';}).join(', '));
            return false;
        }
        $('#sfStatusSmsTemplateError').addClass('d-none');
        return true;
    }
    function sfStatusUpdateCount() {
        var n = $('#sfStatusSmsList .sf-status-cb:checked:not(:disabled)').length;
        $('#sfStatusSmsCount').text('Receivers: ' + n);
        $('#sfStatusSmsStart').prop('disabled', n === 0 || sfStatusSmsSending);
    }

    $('#sfSendStatusFilterSmsBtn').on('click', function () {
        if (!sfLastReportParams || !sfLastReportRows.length) return;
        var status = sfLastReportParams.attendance_status;
        if (!status || status === 'all') {
            alert('Chagua Status (Present only / Absent only) kisha Generate Report.');
            return;
        }
        sfStatusSmsFilter = Object.assign({}, sfLastReportParams, {
            student_ids: sfLastReportRows.map(function (r) { return r.student_id; })
        });
        sfStatusSmsSample = null;
        $('#sfStatusSmsDisabled').addClass('d-none');
        $('#sfStatusSmsContent').addClass('d-none');
        $('#sfStatusSmsLoading').removeClass('d-none');
        $('#sfStatusSmsProgress').addClass('d-none');
        $('#sfStatusSmsList').empty();
        $('#sfStatusSmsModal').modal('show');

        $.get('{{ route("fingerprint_attendance_status_sms.recipients") }}', sfStatusSmsFilter)
            .done(function (res) {
                $('#sfStatusSmsLoading').addClass('d-none');
                if (!res.success) {
                    $('#sfStatusSmsDisabled').removeClass('d-none').text(res.message || 'Failed');
                    return;
                }
                $('#sfStatusSmsContent').removeClass('d-none');
                $('#sfStatusSmsStatusLabel').text(res.status_label || status);
                $('#sfStatusSmsPeriodLabel').text(res.period_label || '');
                sfStatusSmsSample = res.sample || null;
                if (sfStatusSmsSample) sfRenderSample(sfStatusSmsSample);
                var list = $('#sfStatusSmsList');
                list.empty();
                (res.recipients || []).forEach(function (row) {
                    var phone = String(row.parentPhone || '').trim();
                    var hasPhone = phone && !['null','undefined','n/a'].includes(phone.toLowerCase());
                    var id = 'sf_st_' + row.studentID;
                    list.append(
                        '<tr data-student-id="' + row.studentID + '">' +
                        '<td><input type="checkbox" class="sf-status-cb" id="' + id + '" ' + (hasPhone ? '' : 'disabled') + '></td>' +
                        '<td>Parent of ' + sfEsc(row.studentName) + '</td>' +
                        '<td><span class="badge bg-danger">' + (row.days_count || 0) + '</span></td>' +
                        '<td>' + (hasPhone ? sfEsc(phone) : '<span class="text-danger">No Phone</span>') + '</td>' +
                        '<td class="sf-status-col">' + (hasPhone ? '<span class="text-muted small">Pending</span>' : '<span class="text-danger small">No Phone</span>') + '</td></tr>'
                    );
                });
                if (!(res.recipients || []).length) {
                    list.append('<tr><td colspan="5" class="text-center text-muted">No parents found.</td></tr>');
                }
                $('#sfStatusSmsSelectAll').prop('checked', false);
                sfStatusUpdateCount();
            })
            .fail(function (xhr) {
                $('#sfStatusSmsLoading').addClass('d-none');
                $('#sfStatusSmsDisabled').removeClass('d-none').text((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load.');
            });
    });

    $('#sfStatusSmsReset').on('click', function () { if (sfStatusSmsSample) sfRenderSample(sfStatusSmsSample); });
    $('#sfStatusSmsSelectAll').on('change', function () {
        $('#sfStatusSmsList .sf-status-cb:not(:disabled)').prop('checked', $(this).is(':checked'));
        sfStatusUpdateCount();
    });
    $(document).on('change', '.sf-status-cb', sfStatusUpdateCount);

    $('#sfStatusSmsStart').on('click', async function () {
        var rows = $('#sfStatusSmsList .sf-status-cb:checked').closest('tr');
        if (!rows.length || sfStatusSmsSending || !sfStatusSmsFilter) return;
        var tpl = sfSerializeTemplate();
        if (!sfValidateTemplate(tpl)) {
            alert('Usifute values zilizofungwa kwenye SMS sample.');
            return;
        }
        if (!confirm('Send SMS to ' + rows.length + ' parent(s)?')) return;

        sfStatusSmsSending = true;
        $('#sfStatusSmsStart').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $('#sfStatusSmsCancel').prop('disabled', true);
        $('#sfStatusSmsEditor').attr('contenteditable', 'false');
        $('#sfStatusSmsProgress').removeClass('d-none');
        var total = rows.length, delivered = 0, failed = 0;

        for (var i = 0; i < total; i++) {
            var row = $(rows[i]);
            var col = row.find('.sf-status-col');
            col.html('<span class="spinner-border spinner-border-sm"></span>');
            try {
                var response = await $.ajax({
                    url: '{{ route("fingerprint_attendance_status_sms.send") }}',
                    type: 'POST',
                    data: Object.assign({}, sfStatusSmsFilter, {
                        studentID: row.data('student-id'),
                        message_template: tpl,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                });
                if (response.success) {
                    col.html('<span class="text-success small">Delivered</span>');
                    delivered++;
                } else {
                    col.html('<span class="text-danger small">' + (response.error || 'Failed') + '</span>');
                    failed++;
                }
            } catch (err) {
                col.html('<span class="text-danger small">Error</span>');
                failed++;
            }
            var done = delivered + failed;
            var pct = Math.round((done / total) * 100);
            $('#sfStatusSmsBar').css('width', pct + '%').text(pct + '%');
            $('#sfStatusSmsSummary').html('<span class="text-success">' + delivered + ' Delivered</span> | <span class="text-danger">' + failed + ' Failed</span>');
        }

        sfStatusSmsSending = false;
        $('#sfStatusSmsStart').html('<i class="bi bi-send"></i> Start Sending SMS');
        $('#sfStatusSmsCancel').prop('disabled', false);
        $('#sfStatusSmsEditor').attr('contenteditable', 'true');
        sfStatusUpdateCount();
    });

    $('#sfSaveWorkTime').on('click', function () {
        $.post('{{ route("attendance.student_work_time.save") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            work_start_time: $('#sfWorkStart').val(),
            work_end_time: $('#sfWorkEnd').val()
        }).done(function (res) {
            alert(res.message || (res.success ? 'Saved.' : 'Failed'));
        }).fail(function () { alert('Save failed.'); });
    });

    startLivePoll();
})(jQuery);
</script>
