@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.my_attendance').' - ShuleXpert')
@section('page_title', __('teacher.pages.my_attendance'))
@section('page_breadcrumb', __('teacher.pages.my_attendance_bc'))

@section('page_actions')
    <button type="button" id="downloadMyAttendance" class="btn btn-outline-light btn-shadow btn-sm waves-effect">
        <i class="fi fi-rr-file me-1"></i> {{ __('teacher.att.download_report') }}
    </button>
@endsection

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att span:not(.badge):not(.status-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att .stat-value,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-att label {
        color: #000 !important;
    }
    .sx-teacher-att .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .sx-teacher-att .panel-card .card-body { padding: 1.25rem 1.35rem; }
    .sx-teacher-att .stat-card {
        display: block;
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        height: 100%;
    }
    .sx-teacher-att .stat-card .card-body { padding: 1rem 1.1rem; }
    .sx-teacher-att .stat-label {
        display: block;
        margin: 0 0 0.4rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .sx-teacher-att .stat-value {
        display: block;
        margin: 0;
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: #940000 !important;
    }
    .sx-teacher-att .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-teacher-att .report-type-btn {
        border-color: #d9d9d9;
        color: #333 !important;
        background: #fff;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .sx-teacher-att .report-type-btn.active,
    .sx-teacher-att .report-type-btn:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-teacher-att .day-highlight {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fcf7f7;
        padding: 1rem 1.15rem;
        margin-bottom: 1.25rem;
    }
    .sx-teacher-att .status-badge {
        display: inline-block;
        border-radius: 999px;
        padding: 5px 12px;
        font-weight: 700;
        font-size: 0.78rem;
        -webkit-text-fill-color: unset;
    }
    .sx-teacher-att .status-present { background: #e8f5e9 !important; color: #146c2e !important; }
    .sx-teacher-att .status-early { background: #fff7e6 !important; color: #7a4d00 !important; }
    .sx-teacher-att .status-late,
    .sx-teacher-att .status-absent { background: #ffe0e0 !important; color: #7a0000 !important; }
    .sx-teacher-att .status-permitted { background: #f4eef8 !important; color: #5a2d9c !important; }
    .sx-teacher-att .status-off-day,
    .sx-teacher-att .status-present-off-day { background: #f0f0f0 !important; color: #333 !important; }
    .sx-teacher-att .status-weekend { background: #efe5ff !important; color: #5a2d9c !important; }
    .sx-teacher-att .status-not-a-working-day { background: #fff3cd !important; color: #6b5300 !important; }

    /* Dark mode: force badge contrast above global light-text rules */
    html[data-sx-theme="dark"] .sx-teacher-att .status-badge,
    html[data-bs-theme="dark"] .sx-teacher-att .status-badge,
    html[data-sx-theme="dark"] .sx-teacher-att .status-badge *,
    html[data-bs-theme="dark"] .sx-teacher-att .status-badge * {
        -webkit-text-fill-color: unset !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .status-present,
    html[data-bs-theme="dark"] .sx-teacher-att .status-present {
        background: #e8f5e9 !important; color: #146c2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .status-early,
    html[data-bs-theme="dark"] .sx-teacher-att .status-early {
        background: #fff3cd !important; color: #7a4d00 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .status-late,
    html[data-bs-theme="dark"] .sx-teacher-att .status-late,
    html[data-sx-theme="dark"] .sx-teacher-att .status-absent,
    html[data-bs-theme="dark"] .sx-teacher-att .status-absent {
        background: #ffe0e0 !important; color: #7a0000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .status-permitted,
    html[data-bs-theme="dark"] .sx-teacher-att .status-permitted,
    html[data-sx-theme="dark"] .sx-teacher-att .status-weekend,
    html[data-bs-theme="dark"] .sx-teacher-att .status-weekend {
        background: #efe5ff !important; color: #5a2d9c !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .status-off-day,
    html[data-bs-theme="dark"] .sx-teacher-att .status-off-day,
    html[data-sx-theme="dark"] .sx-teacher-att .status-present-off-day,
    html[data-bs-theme="dark"] .sx-teacher-att .status-present-off-day {
        background: #ececec !important; color: #333 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .status-not-a-working-day,
    html[data-bs-theme="dark"] .sx-teacher-att .status-not-a-working-day {
        background: #fff3cd !important; color: #6b5300 !important;
    }
    .sx-teacher-att .table thead th {
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 800;
        color: #940000 !important;
        background: #fcf7f7;
        border-bottom-color: rgba(148, 0, 0, 0.12);
        white-space: nowrap;
        vertical-align: middle;
    }
    .sx-teacher-att .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .sx-teacher-att #attendanceReportTable tbody tr.is-late { background: #fff5f5; }
    .sx-teacher-att #attendanceReportTable tbody tr.is-weekend { background: #f3e8ff; }
    .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork { background: #fff8e1; }

    /* Dark mode: highlighted rows must not keep light bg + white text */
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend {
        background: rgba(111, 66, 193, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork {
        background: rgba(133, 100, 4, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-late,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-late {
        background: rgba(148, 0, 0, 0.22) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend td,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend td,
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork td,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork td,
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-late td,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-late td,
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend td strong,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend td strong,
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork td strong,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork td strong,
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend td small,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend td small,
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork td small,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork td small {
        color: #f8f1ff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend .status-weekend,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-weekend .status-weekend {
        background: #efe5ff !important;
        color: #5a2d9c !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork .status-not-a-working-day,
    html[data-bs-theme="dark"] .sx-teacher-att #attendanceReportTable tbody tr.is-nonwork .status-not-a-working-day {
        background: #fff3cd !important;
        color: #6b5300 !important;
    }
    .sx-teacher-att .loading-panel {
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        gap: 10px;
    }
    .sx-teacher-att .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 12px;
    }

    html[data-sx-theme="dark"] .sx-teacher-att .card,
    html[data-bs-theme="dark"] .sx-teacher-att .card,
    html[data-sx-theme="dark"] .sx-teacher-att .stat-card,
    html[data-bs-theme="dark"] .sx-teacher-att .stat-card,
    html[data-sx-theme="dark"] .sx-teacher-att .day-highlight,
    html[data-bs-theme="dark"] .sx-teacher-att .day-highlight {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .stat-card,
    html[data-bs-theme="dark"] .sx-teacher-att .stat-card {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att,
    html[data-bs-theme="dark"] .sx-teacher-att,
    html[data-sx-theme="dark"] .sx-teacher-att .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-att .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-att label,
    html[data-bs-theme="dark"] .sx-teacher-att label,
    html[data-sx-theme="dark"] .sx-teacher-att .stat-label,
    html[data-bs-theme="dark"] .sx-teacher-att .stat-label {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .table thead th,
    html[data-bs-theme="dark"] .sx-teacher-att .table thead th {
        background: rgba(148, 0, 0, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .btn-outline-light,
    html[data-bs-theme="dark"] .sx-teacher-att .btn-outline-light,
    html[data-sx-theme="dark"] .sx-teacher-att .report-type-btn,
    html[data-bs-theme="dark"] .sx-teacher-att .report-type-btn {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-att .report-type-btn.active,
    html[data-bs-theme="dark"] .sx-teacher-att .report-type-btn.active {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
</style>

<div class="sx-teacher-att">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between border-0 pb-0 gap-2">
                    <div>
                        <h6 class="card-title mb-1">{{ __('teacher.att.report_filter') }}</h6>
                        <small class="text-muted">{{ __('teacher.att.report_filter_sub') }}</small>
                    </div>
                    <div class="btn-group" role="group" aria-label="{{ __('teacher.att.report_type') }}">
                        <button type="button" class="btn report-type-btn active" data-type="day">{{ __('teacher.att.today_day') }}</button>
                        <button type="button" class="btn report-type-btn" data-type="month">{{ __('teacher.att.monthly') }}</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 day-filter">
                            <label for="attendanceDate" class="form-label fw-bold mb-1">{{ __('teacher.date') }}</label>
                            <input type="date" id="attendanceDate" class="form-control" value="{{ $today }}">
                        </div>
                        <div class="col-md-3 month-filter d-none">
                            <label for="attendanceMonth" class="form-label fw-bold mb-1">{{ __('teacher.att.month') }}</label>
                            <select id="attendanceMonth" class="form-select">
                                @foreach(range(1, 12) as $monthNumber)
                                    <option value="{{ $monthNumber }}" {{ $monthNumber === (int) date('n') ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($monthNumber)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 month-filter d-none">
                            <label for="attendanceYear" class="form-label fw-bold mb-1">{{ __('teacher.year') }}</label>
                            <input type="number" id="attendanceYear" class="form-control" min="2000" max="2100" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="loadMyAttendance" class="btn btn-primary waves-effect waves-light w-100">
                                <i class="fi fi-rr-search me-1"></i> {{ __('teacher.att.view') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between border-0 pb-0 gap-2">
                    <h6 class="card-title mb-0" id="reportTitle">{{ __('teacher.pages.my_attendance') }}</h6>
                    <small class="text-muted" id="officialTime"></small>
                </div>
                <div class="card-body" id="attendanceReportBody">
                    <div class="loading-panel">
                        <span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"></span>
                        {{ __('teacher.att.loading_today') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var reportType = 'day';
    var lastParams = null;
    var i18n = @json(__('teacher.att'));
    i18n.my_attendance = @json(__('teacher.pages.my_attendance'));
    i18n.status = @json(__('teacher.status'));
    i18n.date = @json(__('teacher.date'));
    i18n.error = @json(__('teacher.att.fail_download'));

    var statusLabels = {
        'Present': i18n.present,
        'Permitted': i18n.permitted,
        'Absent': i18n.absent,
        'Late': i18n.late,
        'Early': i18n.early,
        'Weekend': i18n.weekend,
        'Not a working day': i18n.not_working_day,
        'Not Available': i18n.not_available,
        'Off day': i18n.off_day,
        'Present (off day)': i18n.present_off_day,
        'Present Off Day': i18n.present_off_day
    };

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function translateStatus(status) {
        status = status || 'Not Available';
        return statusLabels[status] || status;
    }

    function formatTime(value) {
        if (!value) return '—';
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
        return '<span class="status-badge ' + css + '">' + escapeHtml(translateStatus(status)) + '</span>';
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
        var html = '<div class="row g-3 mb-3">' +
            '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.present) + '</span><span class="stat-value">' + (stats.present_days || 0) + '</span></div></div></div>' +
            '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.permitted) + '</span><span class="stat-value">' + (stats.permitted_days || 0) + '</span></div></div></div>' +
            '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.absent) + '</span><span class="stat-value">' + (stats.absent_days || 0) + '</span></div></div></div>' +
            '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.late) + '</span><span class="stat-value">' + (stats.late_days || 0) + '</span></div></div></div>' +
            '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.early) + '</span><span class="stat-value">' + (stats.early_days || 0) + '</span></div></div></div>' +
            '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.working_days) + '</span><span class="stat-value">' + (stats.total_working_days || 0) + '</span></div></div></div>' +
            (res.period_incomplete
                ? '<div class="col-6 col-md-4 col-xl-2"><div class="stat-card"><div class="card-body"><span class="stat-label">' + escapeHtml(i18n.complete_days) + '</span><span class="stat-value">' +
                    (stats.complete_working_days || stats.elapsed_working_days || 0) +
                    '</span></div></div></div>'
                : '') +
            '</div>';

        if (res.filter_type === 'day') {
            html += '<div class="day-highlight">' +
                '<div class="row g-3 align-items-center">' +
                '<div class="col-md-4"><small class="text-muted d-block mb-1">' + escapeHtml(i18n.status) + '</small>' + statusBadge(stats.day_status) + '</div>' +
                '<div class="col-md-4"><small class="text-muted d-block mb-1">' + escapeHtml(i18n.check_in) + '</small><strong>' + formatTime(stats.check_in_time) + '</strong></div>' +
                '<div class="col-md-4"><small class="text-muted d-block mb-1">' + escapeHtml(i18n.check_out) + '</small><strong>' + formatTime(stats.check_out_time) + '</strong></div>' +
                '</div></div>';
        }

        html += '<div class="table-responsive"><table id="attendanceReportTable" class="table table-bordered table-sm mb-0">' +
            '<thead><tr><th>' + escapeHtml(i18n.date) + '</th><th>' + escapeHtml(i18n.check_in) + '</th><th>' + escapeHtml(i18n.check_out) + '</th><th>' + escapeHtml(i18n.status) + '</th></tr></thead><tbody>';
        var records = (res.daily_records || []).slice().sort(function (a, b) {
            return String(a.date || '').localeCompare(String(b.date || ''));
        });
        if (!records.length) {
            html += '<tr><td colspan="4" class="text-center text-muted py-4">' + escapeHtml(i18n.no_dates) + '</td></tr>';
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
                        ? '<div class="small mt-1">' + escapeHtml(i18n.weekend_note) + '</div>'
                        : (day.status === 'Not a working day' ? '<div class="small mt-1">' + escapeHtml(i18n.nonwork_note) + '</div>' : ''));
                html += '<tr class="' + rowClass + '">' +
                    '<td data-order="' + escapeHtml(day.date || '') + '">' + escapeHtml(day.date_label || day.date) + '</td>' +
                    '<td>' + formatTime(day.check_in_time) + '</td>' +
                    '<td>' + formatTime(day.check_out_time) + '</td>' +
                    '<td>' + statusBadge(day.status) + note + '</td></tr>';
            });
        }
        html += '</tbody></table></div>';
        html += '<p class="small text-muted mt-3 mb-0">' +
            '<span class="legend-chip"><span class="status-badge status-not-a-working-day">' + escapeHtml(i18n.not_working_day) + '</span></span>' +
            '<span class="legend-chip"><span class="status-badge status-weekend">' + escapeHtml(i18n.weekend) + '</span></span>' +
            '</p>';

        $('#reportTitle').text(i18n.my_attendance);
        $('#officialTime').text(
            String(i18n.official_time || '')
                .replace(':start', formatTime(res.work_start_time))
                .replace(':end', formatTime(res.work_end_time))
        );
        $('#attendanceReportBody').html(html);
    }

    function loadReport() {
        lastParams = currentParams();
        $('#attendanceReportBody').html(
            '<div class="loading-panel">' +
            '<span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"></span>' +
            escapeHtml(i18n.loading_report) +
            '</div>'
        );
        $('#loadMyAttendance').prop('disabled', true);
        $.ajax({
            url: '{{ route("teacher.my_attendance.data") }}',
            method: 'GET',
            data: lastParams,
            dataType: 'json'
        }).done(function (res) {
            if (!res.success) throw new Error(res.message || i18n.unable_load);
            renderReport(res);
        }).fail(function (xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : i18n.unable_load;
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
            title: i18n.generating_report,
            text: i18n.wait_pdf,
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });
        fetch(url)
            .then(function (response) {
                if (!response.ok) throw new Error(i18n.fail_generate);
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
                Swal.fire('Error', i18n.fail_download, 'error');
            });
    });

    loadReport();
});
</script>
@endpush
