@include('includes.teacher_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .my-attendance-page { font-family: "Century Gothic", Arial, sans-serif; }
    .attendance-card { border: 1px solid #eeeeee; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
    .attendance-header { background: whitesmoke; border-bottom: 2px solid #940000; color: #940000; }
    .btn-brand { background: #940000; border-color: #940000; color: #fff; }
    .btn-brand:hover, .btn-brand:focus { background: #760000; border-color: #760000; color: #fff; }
    .btn-outline-brand { border-color: #940000; color: #940000; background: #fff; }
    .btn-outline-brand:hover, .btn-outline-brand.active { background: #940000; color: #fff; }
    .summary-box { border: 1px solid #eeeeee; background: #fff; padding: 14px; height: 100%; text-align: center; }
    .summary-box strong { display: block; color: #940000; font-size: 1.35rem; }
    .status-badge { display: inline-block; border-radius: 0; padding: 5px 12px; font-weight: 700; }
    .status-present { background: #e8f5e9; color: #207227; }
    .status-early { background: #fff7e6; color: #8a5a00; }
    .status-late, .status-absent { background: #fff0f0; color: #940000; }
    .status-permitted { background: #f4eef8; color: #6f42c1; }
    .status-off-day, .status-present-off-day { background: whitesmoke; color: #555; }
    .status-weekend { background: #f3e8ff; color: #6f42c1; }
    .status-not-a-working-day { background: #fff8e1; color: #856404; }
    #attendanceReportTable thead th { background: whitesmoke; color: #333; border-color: #dee2e6; border-top: 2px solid #940000; }
    #attendanceReportTable tbody tr.is-late { background: #fff5f5; }
    #attendanceReportTable tbody tr.is-weekend { background: #f3e8ff; }
    #attendanceReportTable tbody tr.is-nonwork { background: #fff8e1; }
    .loading-panel { min-height: 180px; display: flex; align-items: center; justify-content: center; color: #666; }
</style>

<div class="container-fluid mt-3 my-attendance-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1" style="color:#940000;"><i class="fa fa-calendar-check-o"></i> My Attendance</h4>
            <small class="text-muted">View today's attendance or your monthly attendance report.</small>
        </div>
        <button type="button" id="downloadMyAttendance" class="btn btn-outline-brand btn-sm">
            <i class="fa fa-file-pdf-o"></i> Download Report
        </button>
    </div>

    <div class="card attendance-card mb-3">
        <div class="card-header attendance-header">
            <strong>Report Filter</strong>
        </div>
        <div class="card-body">
            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-outline-brand active report-type-btn" data-type="day">Today / Day</button>
                <button type="button" class="btn btn-outline-brand report-type-btn" data-type="month">Monthly</button>
            </div>
            <div class="row align-items-end">
                <div class="col-md-4 day-filter">
                    <label for="attendanceDate"><strong>Date</strong></label>
                    <input type="date" id="attendanceDate" class="form-control" value="{{ $today }}">
                </div>
                <div class="col-md-3 month-filter d-none">
                    <label for="attendanceMonth"><strong>Month</strong></label>
                    <select id="attendanceMonth" class="form-control">
                        @foreach(range(1, 12) as $monthNumber)
                            <option value="{{ $monthNumber }}" {{ $monthNumber === (int) date('n') ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($monthNumber)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 month-filter d-none">
                    <label for="attendanceYear"><strong>Year</strong></label>
                    <input type="number" id="attendanceYear" class="form-control" min="2000" max="2100" value="{{ date('Y') }}">
                </div>
                <div class="col-md-2 mt-3 mt-md-0">
                    <button type="button" id="loadMyAttendance" class="btn btn-brand btn-block">
                        <i class="fa fa-search"></i> View
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card attendance-card">
        <div class="card-header attendance-header d-flex justify-content-between align-items-center">
            <strong id="reportTitle">My Attendance</strong>
            <small id="officialTime"></small>
        </div>
        <div class="card-body" id="attendanceReportBody">
            <div class="loading-panel"><i class="fa fa-spinner fa-spin mr-2"></i> Loading today's attendance...</div>
        </div>
    </div>
</div>

<script>
$(function () {
    var reportType = 'day';
    var lastParams = null;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function formatTime(value) {
        if (!value) return '-';
        var match = String(value).trim().match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*$/);
        if (!match) return escapeHtml(value);
        var hour = parseInt(match[1], 10);
        if (isNaN(hour) || hour > 23) return escapeHtml(value);
        var suffix = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return String(hour).padStart(2, '0') + ':' + match[2] + ' ' + suffix;
    }

    function statusBadge(status) {
        status = status || 'Not Available';
        var css = 'status-' + status.toLowerCase().replace(/[^a-z]+/g, '-').replace(/^-|-$/g, '');
        return '<span class="status-badge ' + css + '">' + escapeHtml(status) + '</span>';
    }

    function currentParams() {
        if (reportType === 'month') {
            return {
                filter_type: 'month',
                month: $('#attendanceMonth').val(),
                year: $('#attendanceYear').val()
            };
        }
        return { filter_type: 'day', date: $('#attendanceDate').val() };
    }

    function renderReport(res) {
        var stats = res.statistics || {};
        var html = '<div class="row mb-4">' +
            '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' + (stats.present_days || 0) + '</strong><small>Present</small></div></div>' +
            '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' + (stats.permitted_days || 0) + '</strong><small>Permitted</small></div></div>' +
            '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' + (stats.absent_days || 0) + '</strong><small>Absent</small></div></div>' +
            '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' + (stats.late_days || 0) + '</strong><small>Late</small></div></div>' +
            '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' + (stats.early_days || 0) + '</strong><small>Early</small></div></div>' +
            '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' + (stats.total_working_days || 0) + '</strong><small>Working Days</small></div></div>' +
            (res.period_incomplete
                ? '<div class="col-6 col-md-2 mb-2"><div class="summary-box"><strong>' +
                    (stats.complete_working_days || stats.elapsed_working_days || 0) +
                    '</strong><small>Complete Working Days</small></div></div>'
                : '') +
            '</div>';

        if (res.filter_type === 'day') {
            html += '<div class="p-3 mb-4" style="background:whitesmoke;border-left:4px solid #940000;">' +
                '<div class="row align-items-center"><div class="col-md-4 mb-2"><small class="text-muted d-block">Status</small>' +
                statusBadge(stats.day_status) + '</div>' +
                '<div class="col-md-4 mb-2"><small class="text-muted d-block">Check In</small><strong>' + formatTime(stats.check_in_time) + '</strong></div>' +
                '<div class="col-md-4 mb-2"><small class="text-muted d-block">Check Out</small><strong>' + formatTime(stats.check_out_time) + '</strong></div></div></div>';
        }

        html += '<div class="table-responsive"><table id="attendanceReportTable" class="table table-bordered table-sm mb-0">' +
            '<thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead><tbody>';
        var records = (res.daily_records || []).slice().sort(function (a, b) {
            return String(a.date || '').localeCompare(String(b.date || ''));
        });
        if (!records.length) {
            html += '<tr><td colspan="4" class="text-center text-muted py-4">No attendance dates found for this period.</td></tr>';
        } else {
            records.forEach(function (day) {
                var rowClass = day.is_late ? 'is-late' : (
                    (day.day_kind === 'weekend' || day.status === 'Weekend') ? 'is-weekend' : (
                        (day.day_kind === 'non_working' || day.status === 'Not a working day') ? 'is-nonwork' : ''
                    )
                );
                var note = day.status_note
                    ? '<div class="small mt-1">' + escapeHtml(day.status_note) + '</div>'
                    : (day.status === 'Weekend'
                        ? '<div class="small mt-1">Ni weekend</div>'
                        : (day.status === 'Not a working day' ? '<div class="small mt-1">Sio siku ya kazi</div>' : ''));
                html += '<tr class="' + rowClass + '">' +
                    '<td data-order="' + escapeHtml(day.date || '') + '">' + escapeHtml(day.date_label || day.date) + '</td>' +
                    '<td>' + formatTime(day.check_in_time) + '</td>' +
                    '<td>' + formatTime(day.check_out_time) + '</td>' +
                    '<td>' + statusBadge(day.status) + note + '</td></tr>';
            });
        }
        html += '</tbody></table></div>';
        html += '<p class="small text-muted mt-2 mb-0"><span class="status-badge status-not-a-working-day">Sio siku ya kazi</span> &nbsp; <span class="status-badge status-weekend">Weekend</span></p>';

        $('#reportTitle').text(res.title || 'My Attendance');
        $('#officialTime').text('Official time: ' + formatTime(res.work_start_time) + ' - ' + formatTime(res.work_end_time));
        $('#attendanceReportBody').html(html);
    }

    function loadReport() {
        lastParams = currentParams();
        $('#attendanceReportBody').html('<div class="loading-panel"><i class="fa fa-spinner fa-spin mr-2"></i> Loading attendance report...</div>');
        $('#loadMyAttendance').prop('disabled', true);
        $.ajax({
            url: '{{ route("teacher.my_attendance.data") }}',
            method: 'GET',
            data: lastParams,
            dataType: 'json'
        }).done(function (res) {
            if (!res.success) throw new Error(res.message || 'Unable to load report.');
            renderReport(res);
        }).fail(function (xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to load attendance report.';
            $('#attendanceReportBody').html('<div class="alert alert-danger mb-0">' + escapeHtml(message) + '</div>');
        }).always(function () {
            $('#loadMyAttendance').prop('disabled', false);
        });
    }

    $('.report-type-btn').on('click', function () {
        reportType = $(this).data('type');
        $('.report-type-btn').removeClass('active');
        $(this).addClass('active');
        $('.day-filter').toggleClass('d-none', reportType !== 'day');
        $('.month-filter').toggleClass('d-none', reportType !== 'month');
        loadReport();
    });

    $('#loadMyAttendance').on('click', loadReport);
    $('#downloadMyAttendance').on('click', function () {
        lastParams = currentParams();
        var url = '{{ route("teacher.my_attendance.pdf") }}?' + $.param(lastParams);
        Swal.fire({
            title: 'Generating report...',
            text: 'Please wait while your attendance PDF is prepared.',
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });
        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error('Failed to generate report.');
                return response.blob();
            })
            .then(function (blob) {
                var objectUrl = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = objectUrl;
                link.download = 'My_Attendance_Report.pdf';
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(objectUrl);
                Swal.close();
            })
            .catch(function () {
                Swal.fire('Error', 'Unable to download the attendance report.', 'error');
            });
    });

    loadReport();
});
</script>
