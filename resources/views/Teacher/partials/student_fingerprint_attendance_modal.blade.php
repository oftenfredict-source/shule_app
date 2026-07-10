{{-- Teacher / coordinator fingerprint attendance (API from Local Agent — same as admin, scoped to class) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    #fingerprintAttendanceModal .attendance-device-bar {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
        border: 1px solid #f0d0d0;
        border-radius: 6px;
        padding: 16px;
    }
    #fingerprintAttendanceModal .sf-report-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #940000;
        text-transform: uppercase;
    }
    #fingerprintAttendanceModal .sf-late-cell { color: #dc3545 !important; font-weight: 600; }
    #fingerprintAttendanceModal #tcSfReportTable thead th {
        background-color: #940000 !important;
        color: #fff !important;
        white-space: nowrap;
    }
    #fingerprintAttendanceModal .pulse-animation { animation: tcSfPulse 1s infinite; }
    @keyframes tcSfPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
</style>

@include('partials.fingerprint_attendance_grid_list')

<div class="modal fade" id="fingerprintAttendanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 96%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-fingerprint"></i> Student Fingerprint Attendance
                    <small class="d-block" style="font-size:0.75rem;opacity:0.9;">Data from Local Agent (school LAN) — {{ $subclassDisplayName ?? 'Your class' }}</small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height: 82vh; overflow-y: auto;">
                <ul class="nav nav-tabs mb-3" id="tcSfTabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tcSf-live"><i class="bi bi-broadcast"></i> Live Today</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tcSf-records"><i class="bi bi-list-ul"></i> Saved Records</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tcSf-report"><i class="bi bi-bar-chart"></i> Report</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tcSf-live">
                        <div class="attendance-device-bar mb-3">
                            <div class="row align-items-end g-2">
                                @if($isCoordinatorView ?? false)
                                <div class="col-md-4">
                                    <label class="mb-1">Subclass</label>
                                    <select id="tcSfLiveClassFilter" class="form-control">
                                        <option value="">All Subclasses</option>
                                        @foreach($fpSubclasses as $sc)
                                        <option value="{{ $sc->subclassID }}">{{ $sc->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary-custom btn-block" id="tcSfRefreshLive"><i class="bi bi-arrow-repeat"></i> Refresh</button>
                                </div>
                                <div class="col text-end">
                                    <span class="badge bg-secondary" id="tcSfLiveBadge"><i class="bi bi-circle-fill"></i> Live</span>
                                    <small class="text-muted ms-2" id="tcSfLiveUpdated"></small>
                                </div>
                            </div>
                        </div>
                        <div id="tcSfLiveArea"><div class="alert alert-info mb-0">Live attendance from database (Local Agent sync).</div></div>
                    </div>

                    <div class="tab-pane fade" id="tcSf-records">
                        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                            @if($isCoordinatorView ?? false)
                            <select id="tcSfRecordsClassFilter" class="form-control" style="max-width:260px;">
                                <option value="">All Subclasses</option>
                                @foreach($fpSubclasses as $sc)
                                <option value="{{ $sc->subclassID }}">{{ $sc->display_name }}</option>
                                @endforeach
                            </select>
                            @endif
                            <input type="date" id="tcSfRecordsDate" class="form-control" style="max-width:180px;" value="{{ date('Y-m-d') }}">
                            <button type="button" class="btn btn-primary-custom btn-sm" id="tcSfLoadRecords"><i class="bi bi-arrow-repeat"></i> Refresh</button>
                        </div>
                        <div id="tcSfRecordsArea"><div class="alert alert-info mb-0">Click Refresh to load saved records.</div></div>
                    </div>

                    <div class="tab-pane fade" id="tcSf-report">
                        <div class="attendance-device-bar mb-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="mb-1 fw-bold text-primary-custom">Report Type</label>
                                    <select id="tcSfReportFilterType" class="form-control">
                                        <option value="day">Day</option>
                                        <option value="date_range">Date Range</option>
                                        <option value="month" selected>Month</option>
                                        <option value="year">Year</option>
                                    </select>
                                </div>
                                <div class="col-md-2 tcSf-report-filter" id="tcSfReportDayWrap" style="display:none;">
                                    <label class="mb-1">Date</label>
                                    <input type="date" id="tcSfReportDate" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-2 tcSf-report-filter" id="tcSfReportFromWrap" style="display:none;">
                                    <label class="mb-1">From</label>
                                    <input type="date" id="tcSfReportFromDate" class="form-control">
                                </div>
                                <div class="col-md-2 tcSf-report-filter" id="tcSfReportToWrap" style="display:none;">
                                    <label class="mb-1">To</label>
                                    <input type="date" id="tcSfReportToDate" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-2 tcSf-report-filter" id="tcSfReportMonthWrap">
                                    <label class="mb-1">Month</label>
                                    <select id="tcSfReportMonth" class="form-control">
                                        @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == (int)date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2 tcSf-report-filter" id="tcSfReportYearWrap">
                                    <label class="mb-1">Year</label>
                                    <input type="number" id="tcSfReportYear" class="form-control" value="{{ date('Y') }}" min="2000" max="2100">
                                </div>
                                @if($isCoordinatorView ?? false)
                                <div class="col-md-2">
                                    <label class="mb-1">Subclass</label>
                                    <select id="tcSfReportSubclass" class="form-control">
                                        <option value="">All Subclasses</option>
                                        @foreach($fpSubclasses as $sc)
                                        <option value="{{ $sc->subclassID }}">{{ $sc->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary-custom btn-block" id="tcSfGenerateReport"><i class="bi bi-funnel"></i> Generate</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-block" id="tcSfDownloadReportPdf" disabled><i class="bi bi-file-pdf"></i> PDF</button>
                                </div>
                            </div>
                        </div>
                        <div id="tcSfReportMeta" class="mb-2"></div>
                        <div id="tcSfFingerprintSmsBar" class="alert alert-light border mb-3 py-2 px-3 d-none">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="small">
                                    <strong><i class="bi bi-chat-dots"></i> Fingerprint Attendance SMS</strong>
                                    <span id="tcSfFpSmsScheduleInfo" class="text-muted ms-1"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary-custom" id="tcSfSendFingerprintSmsBtn" disabled>
                                    <i class="bi bi-send"></i> Send SMS to Parents
                                </button>
                            </div>
                            <div id="tcSfFpSmsStatus" class="small mt-1 text-muted"></div>
                        </div>
                        <div id="tcSfReportArea"><div class="alert alert-info mb-0">Chagua filters kisha Generate Report.</div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tcSfDownloadProgressModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 3rem;"></i>
                <h5 class="mb-3 text-primary-custom fw-bold" id="tcSfPdfProgressTitle">Generating PDF...</h5>
                <div class="progress mb-2" style="height: 12px;">
                    <div id="tcSfPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: 0%;"></div>
                </div>
                <div id="tcSfPdfProgressPercent" class="fw-bold text-muted">0%</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
(function ($) {
    if (!$ || !$('#fingerprintAttendanceModal').length) return;

    var tcSfPollTimer = null;
    var tcSfLiveActive = false;
    var tcSfReportDataTable = null;
    var tcSfLastReportParams = null;
    var tcSfLastReportRows = [];
    var tcSfLastReportReferenceDate = null;
    var tcSfScope = {
        isCoordinator: {!! json_encode($isCoordinatorView ?? false) !!},
        fixedSubclassID: {!! json_encode($decryptedSubclassID ?? null) !!},
        classID: {!! json_encode($decryptedClassID ?? null) !!}
    };

    function tcSfEscape(v) {
        return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function tcSfFormatGender(g) {
        if (!g) return '-';
        var v = String(g).trim().toLowerCase();
        if (v === 'male' || v === 'm') return 'Male';
        if (v === 'female' || v === 'f') return 'Female';
        return g;
    }
    function tcSfFormatAmPm(t) {
        if (!t) return '';
        var p = String(t).split(':');
        if (p.length < 2) return t;
        var h = parseInt(p[0], 10) % 12 || 12;
        return h + ':' + p[1] + ' ' + (parseInt(p[0], 10) >= 12 ? 'PM' : 'AM');
    }
    function tcSfFmtTime(dt) {
        if (!dt) return '-';
        var p = String(dt).split(' ');
        return p.length === 2 ? p[1] : dt;
    }
    function tcSfLiveSubclassParam() {
        if (tcSfScope.isCoordinator) return $('#tcSfLiveClassFilter').val() || '';
        return tcSfScope.fixedSubclassID || '';
    }
    function tcSfRecordsSubclassParam() {
        if (tcSfScope.isCoordinator) return $('#tcSfRecordsClassFilter').val() || '';
        return tcSfScope.fixedSubclassID || '';
    }
    function tcSfScopeParams(subclassValue) {
        var params = {};
        if (subclassValue) {
            params.subclassID = subclassValue;
        } else if (tcSfScope.isCoordinator && tcSfScope.classID) {
            params.classID = tcSfScope.classID;
        } else if (tcSfScope.fixedSubclassID) {
            params.subclassID = tcSfScope.fixedSubclassID;
        }
        return params;
    }

    function tcSfStatusBadge(r) {
        var s = r.attendance_status || r.status || '';
        if (s === 'Permission') return '<span class="badge bg-warning text-dark">Permission</span>';
        if (s === 'Sick') return '<span class="badge bg-secondary">Sick</span>';
        if (s === 'Present') return '<span class="badge bg-success">Present</span>';
        if (s === 'Absent') return '<span class="badge bg-danger">Absent</span>';
        if (r.check_out_time || s === 'Complete') return '<span class="badge bg-success">Complete</span>';
        return '<span class="badge bg-info">Check In Only</span>';
    }

    function tcSfRenderLive(records, date) {
        if (!records.length) {
            $('#tcSfLiveArea').html('<div class="alert alert-info mb-0">No attendance for today yet.</div>');
            return;
        }
        var tableHtml = '<div class="table-responsive"><table id="tcSfLiveTable" class="table table-sm table-striped"><thead class="bg-primary-custom text-white"><tr>' +
            '<th>#</th><th>Class</th><th>Name</th><th>Gender</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead><tbody>';
        records.forEach(function (r, i) {
            var st = tcSfStatusBadge(r);
            tableHtml += '<tr' + (r.is_new ? ' class="table-success"' : '') + '><td>' + (i+1) + '</td><td>' + (r.class_name||'-') + '</td><td>' + (r.user_name||'-') + '</td><td>' + tcSfFormatGender(r.gender) + '</td><td>' + tcSfFmtTime(r.check_in_time) + '</td><td>' + tcSfFmtTime(r.check_out_time) + '</td><td>' + st + '</td></tr>';
        });
        tableHtml += '</tbody></table></div>';
        var footer = '<div class="small text-muted">' + date + ' · ' + records.length + ' record(s)</div>';
        if (window.FpAttView) {
            FpAttView.mount('#tcSfLiveArea', tableHtml, 'tcSfLiveTable', footer);
        } else {
            $('#tcSfLiveArea').html(tableHtml + footer);
        }
    }

    function tcSfLoadLive(showLoader) {
        var params = tcSfScopeParams(tcSfLiveSubclassParam());
        if (showLoader && !$('#tcSfLiveArea table').length) {
            $('#tcSfLiveArea').html('<div class="text-center py-4"><div class="spinner-border text-danger"></div></div>');
        }
        $.get('{{ route("zkteco.attendance.student-live-today-from-db") }}', params)
            .done(function (res) {
                if (!res.success) {
                    $('#tcSfLiveArea').html('<div class="alert alert-danger mb-0">' + tcSfEscape(res.message || 'Failed') + '</div>');
                    return;
                }
                tcSfRenderLive(res.data || [], res.date || '{{ date("Y-m-d") }}');
                $('#tcSfLiveBadge').removeClass('bg-secondary').addClass('bg-success');
                $('#tcSfLiveUpdated').text('Updated ' + new Date().toLocaleTimeString());
            })
            .fail(function () { $('#tcSfLiveArea').html('<div class="alert alert-danger mb-0">Error loading live data.</div>'); });
    }

    function tcSfStartLivePoll() {
        tcSfLiveActive = true;
        tcSfLoadLive(true);
        if (tcSfPollTimer) clearInterval(tcSfPollTimer);
        tcSfPollTimer = setInterval(function () {
            if (tcSfLiveActive && $('#tcSf-live').hasClass('active')) tcSfLoadLive(false);
        }, 5000);
    }
    function tcSfStopLivePoll() {
        tcSfLiveActive = false;
        if (tcSfPollTimer) clearInterval(tcSfPollTimer);
        tcSfPollTimer = null;
    }

    function tcSfLoadRecords() {
        $('#tcSfRecordsArea').html('<div class="text-center py-3"><div class="spinner-border text-danger"></div></div>');
        var params = tcSfScopeParams(tcSfRecordsSubclassParam());
        params.date = $('#tcSfRecordsDate').val();
        $.get('{{ route("attendance.student_fingerprint_saved") }}', params)
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $('#tcSfRecordsArea').html('<div class="alert alert-info mb-0">No records found.</div>');
                    return;
                }
                var tableHtml = '<div class="table-responsive"><table id="tcSfRecordsTable" class="table table-sm table-striped"><thead class="bg-primary-custom text-white"><tr>' +
                    '<th>Date</th><th>Class</th><th>Name</th><th>Gender</th><th>Check In</th><th>Check Out</th></tr></thead><tbody>';
                res.data.forEach(function (r) {
                    tableHtml += '<tr><td>' + (r.attendance_date||'-') + '</td><td>' + (r.class_name||'-') + '</td><td>' + (r.user_name||'-') + '</td><td>' + tcSfFormatGender(r.gender) + '</td><td>' + tcSfFmtTime(r.check_in_time) + '</td><td>' + tcSfFmtTime(r.check_out_time) + '</td></tr>';
                });
                tableHtml += '</tbody></table></div>';
                if (window.FpAttView) {
                    FpAttView.mount('#tcSfRecordsArea', tableHtml, 'tcSfRecordsTable');
                } else {
                    $('#tcSfRecordsArea').html(tableHtml);
                }
            })
            .fail(function () { $('#tcSfRecordsArea').html('<div class="alert alert-danger mb-0">Error loading records.</div>'); });
    }

    function tcSfUpdateReportFilters() {
        var type = $('#tcSfReportFilterType').val();
        $('.tcSf-report-filter').hide();
        if (type === 'day') $('#tcSfReportDayWrap').show();
        else if (type === 'date_range') $('#tcSfReportFromWrap, #tcSfReportToWrap').show();
        else if (type === 'month') $('#tcSfReportMonthWrap, #tcSfReportYearWrap').show();
        else if (type === 'year') $('#tcSfReportYearWrap').show();
    }

    function tcSfGetReportParams() {
        var type = $('#tcSfReportFilterType').val();
        var params = { filter_type: type };
        if (tcSfScope.isCoordinator) {
            params.classID = tcSfScope.classID || '';
            params.subclassID = $('#tcSfReportSubclass').val() || '';
        } else {
            params.subclassID = tcSfScope.fixedSubclassID || '';
        }
        if (type === 'day') params.date = $('#tcSfReportDate').val();
        else if (type === 'date_range') {
            params.from_date = $('#tcSfReportFromDate').val();
            params.to_date = $('#tcSfReportToDate').val();
        } else if (type === 'month') {
            params.month = $('#tcSfReportMonth').val();
            params.year = $('#tcSfReportYear').val();
        } else if (type === 'year') params.year = $('#tcSfReportYear').val();
        return params;
    }

    function tcSfDestroyReportTable() {
        if (tcSfReportDataTable) { try { tcSfReportDataTable.destroy(); } catch (e) {} tcSfReportDataTable = null; }
        else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tcSfReportTable')) $('#tcSfReportTable').DataTable().destroy();
    }

    function tcSfResolveReferenceDate(params) {
        if (params.filter_type === 'day' && params.date) return params.date;
        if (params.filter_type === 'date_range' && params.to_date) return params.to_date;
        if (params.filter_type === 'month') return params.year + '-' + String(params.month).padStart(2, '0') + '-01';
        if (params.filter_type === 'year') return params.year + '-01-01';
        return new Date().toISOString().slice(0, 10);
    }

    function tcSfLoadSmsSchedule() {
        $('#tcSfFingerprintSmsBar').removeClass('d-none');
        $('#tcSfSendFingerprintSmsBtn').prop('disabled', !tcSfLastReportRows.length);
        $.get('{{ route("fingerprint_attendance_sms.schedule_info") }}', {
            reference_date: tcSfLastReportReferenceDate,
            filter_type: tcSfLastReportParams ? tcSfLastReportParams.filter_type : ''
        }).done(function (info) {
            var label = 'Manual send for this report';
            if (info.success) {
                if (info.configured) {
                    label = (info.auto_summary || info.schedule_label || label);
                    if (info.auto_enabled) label += ' · Manual still available';
                } else {
                    label = 'Manual send (no school schedule required)';
                }
                label += ' · Period: ' + (info.period_label || '');
            }
            $('#tcSfFpSmsScheduleInfo').text(label);
        });
    }

    function tcSfLoadReport() {
        var params = tcSfGetReportParams();
        $('#tcSfReportArea').html('<div class="text-center py-4"><div class="spinner-border text-danger"></div></div>');
        $('#tcSfReportMeta').empty();
        tcSfDestroyReportTable();
        $('#tcSfDownloadReportPdf').prop('disabled', true);
        tcSfLastReportParams = null;

        $.get('{{ route("attendance.student_fingerprint_report") }}', params)
            .done(function (res) {
                if (!res.success) {
                    $('#tcSfReportArea').html('<div class="alert alert-danger mb-0">' + tcSfEscape(res.message || 'Failed') + '</div>');
                    return;
                }
                tcSfLastReportParams = params;
                tcSfLastReportRows = res.data || [];
                tcSfLastReportReferenceDate = tcSfResolveReferenceDate(params);

                var meta = '<div class="sf-report-title text-center mb-2">' + tcSfEscape(res.title || 'REPORT') + '</div>';
                meta += '<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2 px-3"><div class="row text-center small g-2">';
                meta += '<div class="col-6 col-md-3"><strong>Working Days</strong><br><span class="text-primary-custom fs-5">' + (res.total_working_days || 0) + '</span></div>';
                if (res.period_incomplete && res.complete_working_days !== undefined) {
                    meta += '<div class="col-6 col-md-3"><strong>Complete W/days</strong><br><span class="fs-5 text-warning">' + res.complete_working_days + '</span></div>';
                }
                meta += '<div class="col-6 col-md-3"><strong>Official Time</strong><br>' + tcSfFormatAmPm(res.work_start_time) + ' – ' + tcSfFormatAmPm(res.work_end_time) + '</div>';
                meta += '<div class="col-6 col-md-3"><strong>Period</strong><br>' + (res.period_incomplete ? '<span class="text-warning">In progress</span>' : 'Complete') + '</div>';
                meta += '</div>';

                if (res.filter_type === 'day' && res.gender_totals) {
                    var gt = res.gender_totals;
                    meta += '<div class="row text-center small g-2 mt-2 pt-2 border-top">';
                    meta += '<div class="col-6 col-md-3"><strong>Male Present</strong><br>' + (gt.male_present || 0) + '</div>';
                    meta += '<div class="col-6 col-md-3"><strong>Female Present</strong><br>' + (gt.female_present || 0) + '</div>';
                    meta += '<div class="col-6 col-md-3"><strong>Male Absent</strong><br>' + (gt.male_absent || 0) + '</div>';
                    meta += '<div class="col-6 col-md-3"><strong>Female Absent</strong><br>' + (gt.female_absent || 0) + '</div>';
                    meta += '</div>';
                }
                if (res.attendance_overview) {
                    var ov = res.attendance_overview;
                    meta += '<div class="row text-center small g-2 mt-2 pt-2 border-top">';
                    meta += '<div class="col-6 col-md-3"><strong>Late</strong><br><span class="text-danger fs-5">' + (ov.late || 0) + '</span></div>';
                    meta += '<div class="col-6 col-md-3"><strong>Early Arrival</strong><br><span class="text-success fs-5">' + (ov.early_arrival || 0) + '</span></div>';
                    meta += '<div class="col-6 col-md-3"><strong>Early Departure</strong><br><span class="text-warning fs-5">' + (ov.early_departure || 0) + '</span></div>';
                    meta += '<div class="col-6 col-md-3"><strong>No Sign Out</strong><br><span class="text-secondary fs-5">' + (ov.no_checkout || 0) + '</span></div>';
                    meta += '</div>';
                }
                meta += '</div></div>';
                $('#tcSfReportMeta').html(meta);

                var rows = res.data || [];
                if (!rows.length) {
                    $('#tcSfReportArea').html('<div class="alert alert-info mb-0">No students found.</div>');
                    return;
                }
                var showCompleteCol = !!res.period_incomplete;
                var tableHtml = '<div class="table-responsive"><table id="tcSfReportTable" class="table table-sm table-striped w-100"><thead class="bg-primary-custom text-white"><tr>';
                tableHtml += '<th>#</th><th>Name</th><th>Class</th><th>Gender</th><th>Present</th><th>Absent</th><th>Working Days</th>';
                if (showCompleteCol) tableHtml += '<th>Complete W/days</th>';
                tableHtml += '<th>Late</th><th>Early Arrival</th><th>Early Departure</th><th>No Sign Out</th></tr></thead><tbody>';
                rows.forEach(function (row, i) {
                    var warnClass = (row.no_checkout_days || 0) > 0 ? ' class="table-warning"' : '';
                    var lateClass = row.late_days > 0 ? ' sf-late-cell' : '';
                    tableHtml += '<tr' + warnClass + '><td>' + (i+1) + '</td><td>' + tcSfEscape(row.name) + '</td><td>' + tcSfEscape(row.class_name) + '</td><td>' + tcSfFormatGender(row.gender) + '</td>';
                    tableHtml += '<td><span class="badge bg-success">' + row.present_days + '</span></td>';
                    tableHtml += '<td><span class="badge bg-danger">' + row.absent_days + '</span></td>';
                    tableHtml += '<td>' + (row.total_working_days || res.total_working_days || 0) + '</td>';
                    if (showCompleteCol) {
                        tableHtml += '<td><span class="badge bg-warning text-dark">' + (row.complete_working_days || row.elapsed_working_days || 0) + '</span></td>';
                    }
                    tableHtml += '<td class="' + lateClass + '">' + row.late_days + '</td>';
                    tableHtml += '<td>' + (row.early_arrival_days || row.early_days || 0) + '</td><td>' + (row.early_departure_days || 0) + '</td><td>' + (row.no_checkout_days || 0) + '</td></tr>';
                });
                tableHtml += '</tbody></table></div>';
                if (window.FpAttView) {
                    FpAttView.mount('#tcSfReportArea', tableHtml, 'tcSfReportTable');
                } else {
                    $('#tcSfReportArea').html(tableHtml);
                }
                if ($.fn.DataTable) {
                    tcSfReportDataTable = $('#tcSfReportTable').DataTable({ pageLength: 25, order: [[1, 'asc']] });
                    if (window.FpAttView) FpAttView.bindDataTable('tcSfReportTable', tcSfReportDataTable);
                }
                $('#tcSfDownloadReportPdf').prop('disabled', false);
                tcSfLoadSmsSchedule();
            })
            .fail(function (xhr) {
                $('#tcSfReportArea').html('<div class="alert alert-danger mb-0">' + tcSfEscape((xhr.responseJSON && xhr.responseJSON.message) || 'Error') + '</div>');
            });
    }

    function tcSfDownloadPdf(url) {
        $('#tcSfDownloadProgressModal').modal('show');
        var progress = 0, interval = setInterval(function () {
            if (progress < 95) { progress += 3; $('#tcSfPdfProgressBar').css('width', progress + '%'); }
        }, 300);
        fetch(url).then(function (r) {
            if (!r.ok) throw new Error(r.statusText);
            return r.blob();
        }).then(function (blob) {
            clearInterval(interval);
            $('#tcSfPdfProgressBar').css('width', '100%');
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'Student_Fingerprint_Report.pdf';
            a.click();
            setTimeout(function () { $('#tcSfDownloadProgressModal').modal('hide'); }, 600);
        }).catch(function (e) {
            clearInterval(interval);
            $('#tcSfDownloadProgressModal').modal('hide');
            alert('PDF failed: ' + e.message);
        });
    }

    tcSfUpdateReportFilters();
    $('#tcSfReportFilterType').on('change', tcSfUpdateReportFilters);
    $('#tcSfRefreshLive, #tcSfLiveClassFilter').on('click change', function (e) {
        if (e.type === 'change' || e.target.id === 'tcSfRefreshLive') tcSfLoadLive(true);
    });
    $('#tcSfTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var t = $(e.target).attr('href');
        if (t === '#tcSf-live') tcSfStartLivePoll();
        else tcSfStopLivePoll();
        if (t === '#tcSf-records') tcSfLoadRecords();
    });
    $('#tcSfLoadRecords, #tcSfRecordsClassFilter').on('click change', function (e) {
        if (e.type === 'click' || e.target.id === 'tcSfRecordsClassFilter') tcSfLoadRecords();
    });
    $('#tcSfGenerateReport').on('click', tcSfLoadReport);
    $('#tcSfDownloadReportPdf').on('click', function () {
        if (!tcSfLastReportParams) return;
        tcSfDownloadPdf('{{ route("attendance.student_fingerprint_report_pdf") }}?' + $.param(tcSfLastReportParams));
    });
    $('#tcSfSendFingerprintSmsBtn').on('click', function () {
        if (!tcSfLastReportRows.length || typeof window.openFingerprintAttendanceSmsModal !== 'function') return;
        var opts = {
            referenceDate: tcSfLastReportReferenceDate,
            filterType: tcSfLastReportParams ? tcSfLastReportParams.filter_type : 'day',
            studentIds: tcSfLastReportRows.map(function (r) { return r.student_id; }),
        };
        if (tcSfScope.isCoordinator) {
            opts.classID = tcSfScope.classID || '';
            var sub = $('#tcSfReportSubclass').val();
            if (sub) opts.subclassID = sub;
        } else {
            opts.subclassID = tcSfScope.fixedSubclassID || '';
        }
        window.openFingerprintAttendanceSmsModal(opts);
    });

    $('#fingerprintAttendanceModal').on('shown.bs.modal', function () {
        if ($('#tcSf-live').hasClass('active')) tcSfStartLivePoll();
    }).on('hidden.bs.modal', function () {
        tcSfStopLivePoll();
    });

    window.openTeacherFingerprintAttendanceModal = function () {
        $('#fingerprintAttendanceModal').modal('show');
        tcSfStartLivePoll();
    };
})(jQuery);
</script>

@include('partials.fingerprint_attendance_sms_modal')
