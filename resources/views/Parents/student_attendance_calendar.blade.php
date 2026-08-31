@extends('layouts.parent_corp')

@section('title', 'Mahudhurio - ' . $student->first_name . ' - ShuleXpert')

@section('content')
@php
    $attendanceMode = $attendanceMode ?? 'system';
    $isFingerprint = $attendanceMode === 'fingerprint';
    $overviewRoute = $isFingerprint ? 'parentFingerprintAttendance' : 'parentAttendance';
    $calendarRoute = $isFingerprint ? 'parentStudentFingerprintAttendance' : 'parentStudentAttendance';
    $modeBadge = $isFingerprint ? 'Alama za Vidole' : 'Mahudhurio ya Mfumo';
    $absentStatLabel = $isFingerprint ? 'Hayupo' : 'Alikosa';
@endphp
<style>
    .cal-wrap {
        background: #fff;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        padding: 18px;
        margin-bottom: 20px;
    }
    .month-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-bottom: 16px;
    }
    .month-nav .month-title {
        font-size: 18px;
        font-weight: 800;
        color: #333;
        min-width: 160px;
        text-align: center;
        text-transform: capitalize;
    }
    .month-nav a {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        color: #940000;
        text-decoration: none;
        font-size: 22px;
        font-weight: 800;
    }
    .month-nav a:hover { background: #fff5f5; color: #940000; }
    .att-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 4px;
        table-layout: fixed;
    }
    .att-grid td {
        padding: 0;
        width: 12.5%;
    }
    .att-cell {
        aspect-ratio: 1 / 1;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .att-cell:active { transform: scale(0.94); }
    .att-cell.att-cell-selected {
        outline: 3px solid #940000;
        outline-offset: 1px;
        box-shadow: 0 2px 8px rgba(148,0,0,0.25);
    }
    .att-cell-clickable {
        position: relative;
    }
    .att-cell-clickable .att-hover-tip {
        position: absolute;
        left: 50%;
        bottom: calc(100% + 6px);
        transform: translateX(-50%) scale(0.95);
        background: #212529;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.35;
        padding: 6px 8px;
        border-radius: 4px;
        white-space: nowrap;
        max-width: 200px;
        white-space: normal;
        text-align: center;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s;
        z-index: 20;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .att-cell-clickable .att-hover-tip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #212529;
    }
    @media (hover: hover) and (pointer: fine) {
        .att-cell-clickable:hover .att-hover-tip,
        .att-cell-clickable:focus-visible .att-hover-tip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) scale(1);
        }
    }
    .att-cell-pad { visibility: hidden; pointer-events: none; }
    .att-cell i {
        font-size: 18px;
        font-weight: 900;
        line-height: 1;
    }
    .att-cell-present { background: #d4edda; color: #155724; }
    .att-cell-absent { background: #f8d7da; color: #000; }
    .att-cell-permission { background: #ffc107; color: #000; }
    .att-cell-sick { background: #fd7e14; color: #000; }
    .att-cell-incomplete { background: #d1ecf1; color: #0c5460; }
    .att-cell-non-school { background: #212529; color: #fff; }
    .att-cell-future { background: #f1f3f5; color: #adb5bd; }
    .stat-pill {
        text-align: center;
        padding: 10px 6px;
        border: 1px solid #eee;
        background: #fff;
        height: 100%;
    }
    .stat-pill .num { font-size: 18px; font-weight: 800; line-height: 1; }
    .stat-pill .txt {
        font-size: 9px;
        text-transform: uppercase;
        color: #777;
        font-weight: 700;
        margin-top: 4px;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 4px 10px 4px 0;
        font-size: 11px;
        font-weight: 600;
        color: #555;
    }
    .legend-swatch {
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .legend-swatch i { font-size: 14px; font-weight: 900; }
    #attCellModal .modal-content { border-radius: 0; border-top: 4px solid #940000; }
    #attCellModal .status-badge {
        display: inline-block;
        padding: 8px 16px;
        font-weight: 800;
        font-size: 14px;
        border-radius: 0;
        margin-top: 8px;
    }
    .cal-hint {
        font-size: 12px;
        color: #888;
        text-align: center;
        margin-bottom: 10px;
    }
    @media (max-width: 576px) {
        .cal-wrap { padding: 12px; }
        .month-nav .month-title { font-size: 16px; min-width: 130px; }
        .att-grid { border-spacing: 3px; }
        .att-cell i { font-size: 15px; }
        .att-cell { min-height: 34px; }
        .stat-pill .num { font-size: 16px; }
    }
</style>

<div class="row">
    <div class="col-12 mb-3">
        <a href="{{ route($overviewRoute, ['month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back"></i> Rudi kwa Watoto
        </a>
    </div>

    <div class="col-12">
        <div class="cal-wrap">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h5 class="mb-0 fw-bold">{{ $student->first_name }} {{ $student->last_name }}</h5>
                    <small class="text-muted">
                        {{ trim(($student->subclass->class->class_name ?? '') . ' ' . ($student->subclass->subclass_name ?? '')) }}
                        &bull; Usajili: {{ $student->admission_number ?? '-' }}
                    </small>
                </div>
                <span class="badge bg-light text-dark border">{{ $modeBadge }}</span>
            </div>

            <div class="month-nav">
                <a href="{{ route($calendarRoute, ['studentID' => $student->studentID, 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" aria-label="Mwezi uliopita">
                    <i class="bx bx-chevron-left"></i>
                </a>
                <div class="month-title">{{ $monthLabel }}</div>
                <a href="{{ route($calendarRoute, ['studentID' => $student->studentID, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" aria-label="Mwezi ujao">
                    <i class="bx bx-chevron-right"></i>
                </a>
            </div>

            <p class="cal-hint mb-2 d-none d-md-block"><i class="bx bx-mouse"></i> Elekeza kipanya juu ya kisanduku kuona tarehe na hali</p>
            <p class="cal-hint mb-2 d-md-none"><i class="bx bx-pointer"></i> Bofya kisanduku kuona tarehe na hali</p>

            <div class="table-responsive">
                <table class="att-grid">
                    <tbody>
                        @foreach($calendar['rows'] as $row)
                            <tr>
                                @foreach($row as $cell)
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
                                            <i class="bx {{ $cell['icon'] }}"></i>
                                        </div>
                                    </td>
                                @endforeach
                                @for($pad = count($row); $pad < 8; $pad++)
                                    <td><div class="att-cell att-cell-future att-cell-pad"><i class="bx bx-minus"></i></div></td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 pt-2 border-top">
                @foreach($calendar['legend'] as $legend)
                    <span class="legend-item">
                        <span class="legend-swatch {{ $legend['class'] }}"><i class="bx {{ $legend['icon'] }}"></i></span>
                        {{ $legend['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="row g-2 mb-4">
            <div class="col-4 col-md-2">
                <div class="stat-pill">
                    <div class="num text-success">{{ $stats['present'] }}</div>
                    <div class="txt">Alikuwepo</div>
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
                    <div class="num" style="color:#fd7e14;">{{ $stats['sick'] }}</div>
                    <div class="txt">Mgonjwa</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="stat-pill">
                    <div class="num text-warning">{{ $stats['permission'] }}</div>
                    <div class="txt">Ruhusa</div>
                </div>
            </div>
            @if(!$isFingerprint)
            <div class="col-4 col-md-2">
                <div class="stat-pill">
                    <div class="num text-info">{{ $stats['incomplete'] ?? 0 }}</div>
                    <div class="txt">Haijakamilika</div>
                </div>
            </div>
            @endif
            <div class="col-4 col-md-2">
                <div class="stat-pill" style="border-bottom: 3px solid #940000;">
                    <div class="num">{{ $stats['attendance_rate'] }}%</div>
                    <div class="txt">Kiwango</div>
                </div>
            </div>
        </div>
        <p class="small text-muted mb-0">
            Takwimu zinahesabiwa kwa <strong>siku za kazi za shule</strong> tu ({{ $stats['working_days'] }} siku mwezi huu).
        </p>
    </div>
</div>

<div class="modal fade" id="attCellModal" tabindex="-1" aria-labelledby="attCellModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="attCellModalLabel" style="color:#940000;">
                    <i class="bx bx-calendar-event"></i> Mahudhurio
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Funga"></button>
            </div>
            <div class="modal-body text-center pt-2 pb-4">
                <div id="attModalDate" class="fw-bold" style="font-size:15px; color:#333;"></div>
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
