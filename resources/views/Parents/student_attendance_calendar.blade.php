@extends('layouts.parent_corp')

@section('title', __('parent.attendance') . ' - ' . $student->first_name . ' - ShuleXpert')
@section('page_title', __('parent.attendance'))
@section('page_breadcrumb', trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')))

@section('content')
@php
    $attendanceMode = $attendanceMode ?? 'system';
    $isFingerprint = $attendanceMode === 'fingerprint';
    $overviewRoute = $isFingerprint ? 'parentFingerprintAttendance' : 'parentAttendance';
    $calendarRoute = $isFingerprint ? 'parentStudentFingerprintAttendance' : 'parentStudentAttendance';
    $modeBadge = $isFingerprint ? __('parent.fingerprint_attendance') : __('parent.system_attendance');
    $absentStatLabel = $isFingerprint ? __('parent.absent') : __('parent.missed');
    $childName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    $childInitial = strtoupper(substr($childName !== '' ? $childName : 'S', 0, 1));
    $className = trim(($student->subclass->class->class_name ?? '') . ' ' . ($student->subclass->subclass_name ?? ''));
@endphp
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal span:not(.badge):not(.status-badge):not(.att-day-num):not(.att-day-status):not(.att-hover-tip),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal div:not(.att-cell):not(.legend-swatch):not(.status-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .stat-pill .txt,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .legend-item,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .month-title {
        color: #000 !important;
    }
    .sx-parent-cal .btn-primary,
    .sx-parent-cal a.btn-primary,
    .sx-parent-cal .badge.bg-primary {
        color: #fff !important;
    }
    .sx-parent-cal .card,
    .sx-parent-cal .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-cal .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-cal .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-cal .child-avatar {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        color: #940000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }
    .sx-parent-cal .child-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sx-parent-cal .mode-badge {
        background: #fff5f5;
        border: 1px solid rgba(148, 0, 0, 0.2);
        color: #940000 !important;
        font-weight: 800;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 0.4rem 0.7rem;
    }
    .sx-parent-cal .month-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-bottom: 1rem;
    }
    .sx-parent-cal .month-nav .month-title {
        font-size: 1.1rem;
        font-weight: 800;
        min-width: 160px;
        text-align: center;
        text-transform: capitalize;
    }
    .sx-parent-cal .month-nav a {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e6e6e6;
        color: #940000 !important;
        text-decoration: none;
        background: #fff;
        transition: background 0.15s ease;
    }
    .sx-parent-cal .month-nav a:hover {
        background: #fff5f5;
        color: #940000 !important;
    }
    .sx-parent-cal .cal-hint {
        font-size: 0.78rem;
        color: #888 !important;
        text-align: center;
        margin-bottom: 0.75rem;
    }
    .sx-parent-cal .att-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 4px;
        table-layout: fixed;
    }
    .sx-parent-cal .att-grid td {
        padding: 0;
        width: 12.5%;
    }
    .sx-parent-cal .att-cell {
        aspect-ratio: 1 / 1;
        min-height: 58px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        border-radius: 4px;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
        padding: 4px 2px;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .sx-parent-cal .att-cell:active { transform: scale(0.94); }
    .sx-parent-cal .att-cell.att-cell-selected {
        outline: 3px solid #940000;
        outline-offset: 1px;
        box-shadow: 0 2px 8px rgba(148,0,0,0.25);
    }
    .sx-parent-cal .att-cell-clickable { position: relative; }
    .sx-parent-cal .att-day-num {
        display: block;
        font-size: 0.95rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }
    .sx-parent-cal .att-day-status {
        display: block;
        font-size: 0.58rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        line-height: 1.1;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 0 2px;
        opacity: 0.95;
    }
    .sx-parent-cal .att-cell i {
        font-size: 14px;
        font-weight: 900;
        line-height: 1;
    }
    .sx-parent-cal .att-cell-clickable .att-hover-tip,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .att-cell-clickable .att-hover-tip,
    html[data-sx-theme="dark"] .sx-parent-cal .att-cell-clickable .att-hover-tip,
    html[data-bs-theme="dark"] .sx-parent-cal .att-cell-clickable .att-hover-tip {
        position: absolute;
        left: 50%;
        bottom: calc(100% + 6px);
        transform: translateX(-50%) scale(0.95);
        background: #212529 !important;
        color: #fff !important;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.35;
        padding: 7px 9px;
        border-radius: 4px;
        max-width: 220px;
        white-space: normal;
        text-align: center;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s;
        z-index: 30;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }
    .sx-parent-cal .att-cell-clickable .att-hover-tip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #212529;
    }
    @media (hover: hover) and (pointer: fine) {
        .sx-parent-cal .att-cell-clickable:hover .att-hover-tip,
        .sx-parent-cal .att-cell-clickable:focus-visible .att-hover-tip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) scale(1);
        }
    }
    .sx-parent-cal .att-cell-pad { visibility: hidden; pointer-events: none; }
    .sx-parent-cal .att-cell-present,
    .sx-parent-cal .att-cell-present .att-day-num,
    .sx-parent-cal .att-cell-present .att-day-status,
    .sx-parent-cal .att-cell-present i {
        background: #d4edda;
        color: #0f5132 !important;
    }
    .sx-parent-cal .att-cell-absent,
    .sx-parent-cal .att-cell-absent .att-day-num,
    .sx-parent-cal .att-cell-absent .att-day-status,
    .sx-parent-cal .att-cell-absent i {
        background: #f8d7da;
        color: #842029 !important;
    }
    .sx-parent-cal .att-cell-permission,
    .sx-parent-cal .att-cell-permission .att-day-num,
    .sx-parent-cal .att-cell-permission .att-day-status,
    .sx-parent-cal .att-cell-permission i {
        background: #ffe69c;
        color: #664d03 !important;
    }
    .sx-parent-cal .att-cell-sick,
    .sx-parent-cal .att-cell-sick .att-day-num,
    .sx-parent-cal .att-cell-sick .att-day-status,
    .sx-parent-cal .att-cell-sick i {
        background: #ffc107;
        color: #5c3b00 !important;
    }
    .sx-parent-cal .att-cell-incomplete,
    .sx-parent-cal .att-cell-incomplete .att-day-num,
    .sx-parent-cal .att-cell-incomplete .att-day-status,
    .sx-parent-cal .att-cell-incomplete i {
        background: #cff4fc;
        color: #055160 !important;
    }
    .sx-parent-cal .att-cell-non-school {
        background: #343a40 !important;
    }
    .sx-parent-cal .att-cell-non-school,
    .sx-parent-cal .att-cell-non-school .att-day-num,
    .sx-parent-cal .att-cell-non-school .att-day-status,
    .sx-parent-cal .att-cell-non-school i,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .att-cell-non-school,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .att-cell-non-school .att-day-num,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .att-cell-non-school .att-day-status,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-cal .att-cell-non-school i {
        color: #fff !important;
    }
    .sx-parent-cal .att-cell-future,
    .sx-parent-cal .att-cell-future .att-day-num,
    .sx-parent-cal .att-cell-future .att-day-status,
    .sx-parent-cal .att-cell-future i {
        background: #f1f3f5;
        color: #6c757d !important;
    }

    .sx-parent-cal .stat-pill {
        text-align: center;
        padding: 0.85rem 0.45rem;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        background: #fff;
        height: 100%;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .sx-parent-cal .stat-pill .num {
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1;
    }
    .sx-parent-cal .stat-pill .txt {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
        font-weight: 700;
        margin-top: 0.35rem;
    }
    .sx-parent-cal .stat-pill.is-rate {
        border-top-color: #940000;
        background: #fff8f8;
    }
    .sx-parent-cal .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 4px 12px 4px 0;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .sx-parent-cal .legend-swatch {
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .sx-parent-cal .legend-swatch i { font-size: 14px; font-weight: 900; }
    #attCellModal .modal-content {
        border-radius: 0.35rem;
        border-top: 4px solid #940000;
    }
    #attCellModal .status-badge {
        display: inline-block;
        padding: 8px 16px;
        font-weight: 800;
        font-size: 14px;
        border-radius: 0;
        margin-top: 8px;
    }

    @media (max-width: 576px) {
        .sx-parent-cal .month-nav .month-title { font-size: 1rem; min-width: 130px; }
        .sx-parent-cal .att-grid { border-spacing: 3px; }
        .sx-parent-cal .att-cell { min-height: 48px; }
        .sx-parent-cal .att-day-num { font-size: 0.82rem; }
        .sx-parent-cal .att-day-status { font-size: 0.5rem; }
        .sx-parent-cal .att-cell i { font-size: 12px; }
        .sx-parent-cal .stat-pill .num { font-size: 1rem; }
    }

    html[data-sx-theme="dark"] .sx-parent-cal .card,
    html[data-bs-theme="dark"] .sx-parent-cal .card,
    html[data-sx-theme="dark"] .sx-parent-cal .panel-card,
    html[data-bs-theme="dark"] .sx-parent-cal .panel-card,
    html[data-sx-theme="dark"] .sx-parent-cal .stat-pill,
    html[data-bs-theme="dark"] .sx-parent-cal .stat-pill {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-cal,
    html[data-bs-theme="dark"] .sx-parent-cal,
    html[data-sx-theme="dark"] .sx-parent-cal p,
    html[data-bs-theme="dark"] .sx-parent-cal p,
    html[data-sx-theme="dark"] .sx-parent-cal span:not(.badge):not(.status-badge):not(.att-hover-tip),
    html[data-bs-theme="dark"] .sx-parent-cal span:not(.badge):not(.status-badge):not(.att-hover-tip),
    html[data-sx-theme="dark"] .sx-parent-cal small,
    html[data-bs-theme="dark"] .sx-parent-cal small,
    html[data-sx-theme="dark"] .sx-parent-cal strong,
    html[data-bs-theme="dark"] .sx-parent-cal strong,
    html[data-sx-theme="dark"] .sx-parent-cal h5,
    html[data-bs-theme="dark"] .sx-parent-cal h5,
    html[data-sx-theme="dark"] .sx-parent-cal .text-muted,
    html[data-bs-theme="dark"] .sx-parent-cal .text-muted,
    html[data-sx-theme="dark"] .sx-parent-cal .month-title,
    html[data-bs-theme="dark"] .sx-parent-cal .month-title,
    html[data-sx-theme="dark"] .sx-parent-cal .legend-item,
    html[data-bs-theme="dark"] .sx-parent-cal .legend-item,
    html[data-sx-theme="dark"] .sx-parent-cal .stat-pill .txt,
    html[data-bs-theme="dark"] .sx-parent-cal .stat-pill .txt {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-cal .month-nav a,
    html[data-bs-theme="dark"] .sx-parent-cal .month-nav a,
    html[data-sx-theme="dark"] .sx-parent-cal .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-cal .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-cal .mode-badge,
    html[data-bs-theme="dark"] .sx-parent-cal .mode-badge,
    html[data-sx-theme="dark"] .sx-parent-cal .stat-pill.is-rate,
    html[data-bs-theme="dark"] .sx-parent-cal .stat-pill.is-rate {
        background: rgba(148, 0, 0, 0.22) !important;
        color: #ffb4b4 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
</style>

<div class="sx-parent-cal">
    <div class="row g-3">
        <div class="col-12">
            <a href="{{ route($overviewRoute, ['month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-outline-light btn-shadow waves-effect">
                <i class="fi fi-rr-arrow-left me-1"></i> {{ __('parent.back_to_children') }}
            </a>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="child-avatar">
                                @if($student->photo)
                                    <img src="{{ asset('userImages/' . $student->photo) }}" alt="">
                                @else
                                    {{ $childInitial }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h5 class="mb-0 fw-bold text-truncate">{{ $childName }}</h5>
                                <small class="text-muted d-block text-truncate">
                                    {{ $className ?: __('parent.unknown_class') }}
                                    &bull; {{ __('parent.admission') }}: {{ $student->admission_number ?? '-' }}
                                </small>
                            </div>
                        </div>
                        <span class="mode-badge">{{ $modeBadge }}</span>
                    </div>

                    <div class="month-nav">
                        <a href="{{ route($calendarRoute, ['studentID' => $student->studentID, 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" aria-label="{{ __('parent.prev_month') }}">
                            <i class="fi fi-rr-angle-left"></i>
                        </a>
                        <div class="month-title">{{ $monthLabel }}</div>
                        <a href="{{ route($calendarRoute, ['studentID' => $student->studentID, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" aria-label="{{ __('parent.next_month') }}">
                            <i class="fi fi-rr-angle-right"></i>
                        </a>
                    </div>

                    <p class="cal-hint mb-2 d-none d-md-block"><i class="fi fi-rr-cursor-finger me-1"></i> {{ __('parent.hover_hint') }}</p>
                    <p class="cal-hint mb-2 d-md-none"><i class="fi fi-rr-hand me-1"></i> {{ __('parent.tap_hint') }}</p>

                    <div class="table-responsive">
                        <table class="att-grid">
                            <tbody>
                                @foreach($calendar['rows'] as $row)
                                    <tr>
                                        @foreach($row as $cell)
                                            @php
                                                $dayNum = !empty($cell['date']) ? (int) \Carbon\Carbon::parse($cell['date'])->format('j') : '';
                                            @endphp
                                            <td>
                                                <div class="att-cell {{ $cell['css_class'] }} att-cell-clickable"
                                                     role="button"
                                                     tabindex="0"
                                                     data-date="{{ $cell['date'] }}"
                                                     data-date-label="{{ $cell['date_label'] ?? '' }}"
                                                     data-status-label="{{ $cell['label'] ?? '' }}"
                                                     data-status-key="{{ $cell['status'] ?? '' }}"
                                                     aria-label="{{ ($cell['date_label'] ?? '') . ', ' . ($cell['label'] ?? '') }}">
                                                    <span class="att-hover-tip">{{ $cell['date_label'] ?? '' }}<br>{{ $cell['label'] ?? '' }}</span>
                                                    <span class="att-day-num">{{ $dayNum }}</span>
                                                    <i class="bx {{ $cell['icon'] }}" aria-hidden="true"></i>
                                                    <span class="att-day-status">{{ $cell['label'] ?? '' }}</span>
                                                </div>
                                            </td>
                                        @endforeach
                                        @for($pad = count($row); $pad < 8; $pad++)
                                            <td><div class="att-cell att-cell-future att-cell-pad"><span class="att-day-num">-</span></div></td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        @foreach($calendar['legend'] as $legend)
                            <span class="legend-item">
                                <span class="legend-swatch {{ $legend['class'] }}"><i class="bx {{ $legend['icon'] }}"></i></span>
                                {{ $legend['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-2">
                <div class="col-4 col-md-2">
                    <div class="stat-pill">
                        <div class="num text-success">{{ $stats['present'] }}</div>
                        <div class="txt">{{ __('parent.present') }}</div>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="stat-pill">
                        <div class="num text-danger">{{ $stats['absent'] }}</div>
                        <div class="txt">{{ $absentStatLabel }}</div>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="stat-pill">
                        <div class="num" style="color:#fd7e14 !important;">{{ $stats['sick'] }}</div>
                        <div class="txt">{{ __('parent.sick') }}</div>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="stat-pill">
                        <div class="num text-warning">{{ $stats['permission'] }}</div>
                        <div class="txt">{{ __('parent.permission') }}</div>
                    </div>
                </div>
                @if(!$isFingerprint)
                <div class="col-4 col-md-2">
                    <div class="stat-pill">
                        <div class="num text-info">{{ $stats['incomplete'] ?? 0 }}</div>
                        <div class="txt">{{ __('parent.incomplete') }}</div>
                    </div>
                </div>
                @endif
                <div class="col-4 col-md-2">
                    <div class="stat-pill is-rate">
                        <div class="num" style="color:#940000 !important;">{{ $stats['attendance_rate'] }}%</div>
                        <div class="txt">{{ __('parent.rate') }}</div>
                    </div>
                </div>
            </div>
            <p class="small text-muted mt-3 mb-0">
                {!! __('parent.working_days_note_plain', ['days' => (int) $stats['working_days']]) !!}
            </p>
        </div>
    </div>
</div>

<div class="modal fade" id="attCellModal" tabindex="-1" aria-labelledby="attCellModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="attCellModalLabel" style="color:#940000;">
                    <i class="fi fi-rr-calendar me-1"></i> {{ __('parent.attendance') }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('parent.close') }}"></button>
            </div>
            <div class="modal-body text-center pt-2 pb-4">
                <div id="attModalDate" class="fw-bold" style="font-size:15px;"></div>
                <div id="attModalStatusWrap"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var statusBadgeClass = {
        present: 'att-cell-present',
        absent: 'att-cell-absent',
        permission: 'att-cell-permission',
        sick: 'att-cell-sick',
        incomplete: 'att-cell-incomplete',
        non_school: 'att-cell-non-school',
        future: 'att-cell-future'
    };

    var modalEl = document.getElementById('attCellModal');
    var modal = modalEl && window.bootstrap ? new bootstrap.Modal(modalEl) : null;

    function openCellPopup(cell) {
        if (!cell || cell.classList.contains('att-cell-pad')) return;

        document.querySelectorAll('.att-cell-selected').forEach(function (el) {
            el.classList.remove('att-cell-selected');
        });
        cell.classList.add('att-cell-selected');

        var dateLabel = cell.getAttribute('data-date-label') || cell.getAttribute('data-date') || '';
        var statusLabel = cell.getAttribute('data-status-label') || '';
        var statusKey = cell.getAttribute('data-status-key') || '';
        var badgeClass = statusBadgeClass[statusKey] || 'bg-secondary';

        var dateEl = document.getElementById('attModalDate');
        var statusWrap = document.getElementById('attModalStatusWrap');

        if (dateEl) dateEl.textContent = dateLabel;
        if (statusWrap) {
            statusWrap.innerHTML = statusLabel
                ? '<span class="status-badge ' + badgeClass + '">' + statusLabel + '</span>'
                : '';
        }

        if (modal) {
            modal.show();
        }
    }

    document.querySelectorAll('.att-cell-clickable').forEach(function (cell) {
        cell.addEventListener('click', function () { openCellPopup(cell); });
        cell.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openCellPopup(cell);
            }
        });
    });
})();
</script>
@endpush
