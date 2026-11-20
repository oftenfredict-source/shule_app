@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.duty_book').' - ShuleXpert')
@section('page_title', __('teacher.pages.duty_book'))
@section('page_breadcrumb', __('teacher.pages.duty_book_bc'))

@section('page_actions')
    <button type="button" id="exportRosterBtn" data-from="{{ $fromDate }}" data-to="{{ $toDate }}" class="btn btn-primary btn-sm waves-effect">
        <i class="fa-solid fa-file-pdf me-1"></i> {{ __('teacher.duty.export_roster') }}
    </button>
@endsection

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-duty .duty-day-cell {
        color: #000 !important;
    }
    .sx-teacher-duty .card,
    .sx-teacher-duty .panel-card,
    .sx-teacher-duty .duty-active-card {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    }
    .sx-teacher-duty .card,
    .sx-teacher-duty .panel-card {
        border-top: 4px solid #940000 !important;
    }
    .sx-teacher-duty .panel-card .card-body { padding: 1.15rem 1.25rem; }
    .sx-teacher-duty .panel-card.is-flush .card-body { padding: 0; }
    .sx-teacher-duty .panel-card.accent-maroon { border-top-color: #940000 !important; }
    .sx-teacher-duty .panel-card.accent-info { border-top-color: #17a2b8 !important; }
    .sx-teacher-duty .duty-active-card {
        border-top: 4px solid #207227 !important;
        background: #f1faf2 !important;
        padding: 1rem 1.15rem;
    }
    .sx-teacher-duty .duty-active-card .duty-active-icon {
        font-size: 1.9rem;
        color: #207227 !important;
    }
    .sx-teacher-duty .duty-active-card .duty-active-title { color: #207227 !important; font-weight: 800; }
    .sx-teacher-duty .table thead th {
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
    .sx-teacher-duty .table td { vertical-align: middle; font-size: 0.9rem; }
    .sx-teacher-duty .form-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; }
    .sx-teacher-duty .form-control,
    .sx-teacher-duty .form-select,
    .sx-teacher-duty .btn {
        border-radius: 0 !important;
    }
    .sx-teacher-duty .btn-primary,
    .sx-teacher-duty .btn-primary:focus {
        background: #940000;
        border-color: #940000;
        color: #fff !important;
        font-weight: 800;
    }
    .sx-teacher-duty .btn-primary:hover { background: #7b0000; border-color: #7b0000; }
    .sx-teacher-duty .btn-outline-primary { color: #940000 !important; border-color: #940000; font-weight: 800; }
    .sx-teacher-duty .btn-outline-primary:hover { background: #940000; color: #fff !important; }
    .sx-teacher-duty .btn-success { font-weight: 800; }
    .sx-teacher-duty .card-header {
        background: linear-gradient(135deg, #fcf7f7 0%, #fff 70%) !important;
        border-bottom: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .sx-teacher-duty .card-title {
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
    }

    /* Week rows */
    .sx-teacher-duty .duty-week-row { cursor: pointer; transition: background-color 0.15s ease; }
    .sx-teacher-duty .duty-week-row:hover { background: #faf3f3; }
    .sx-teacher-duty .duty-week-row.active-week { background: #fff5f5; box-shadow: inset 4px 0 0 #940000; }
    .sx-teacher-duty .duty-week-row .sx-duty-chevron { transition: transform 0.25s ease; color: #940000 !important; }
    .sx-teacher-duty .duty-week-row.is-open .sx-duty-chevron { transform: rotate(180deg); }
    .sx-teacher-duty .duty-week-row.is-open { background: #fcf7f7; }
    .sx-teacher-duty .day-view-container > td { background: #fdfafa; }
    .sx-teacher-duty .duty-days-table tr.is-today > td { background: #fff8e1; }

    /* Pills / status */
    .sx-teacher-duty .sx-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 12px;
        font-weight: 700;
        font-size: 0.76rem;
        line-height: 1.2;
    }
    .sx-teacher-duty .sx-pill-week { background: #f5f5f5; color: #940000 !important; letter-spacing: 0.04em; }
    .sx-teacher-duty .active-week .sx-pill-week { background: #940000; color: #fff !important; }
    .sx-teacher-duty .sx-pill-teacher { background: #f5f5f5; color: #333 !important; margin: 0 4px 4px 0; }
    .sx-teacher-duty .sx-pill-teacher.is-me { background: #940000; color: #fff !important; }
    .sx-teacher-duty .sx-pill-approved { background: #e8f5e9; color: #207227 !important; }
    .sx-teacher-duty .sx-pill-sent { background: #e7f1ff; color: #0b5ed7 !important; }
    .sx-teacher-duty .sx-pill-draft { background: #fff7e6; color: #8a5a00 !important; }
    .sx-teacher-duty .sx-pill-upcoming { background: #f5f5f5; color: #555 !important; }
    .sx-teacher-duty .sx-pill-waiting { background: #fff0f0; color: #940000 !important; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-duty,
    html[data-bs-theme="dark"] .sx-teacher-duty,
    html[data-sx-theme="dark"] .sx-teacher-duty .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-duty .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-duty label,
    html[data-bs-theme="dark"] .sx-teacher-duty label,
    html[data-sx-theme="dark"] .sx-teacher-duty td,
    html[data-bs-theme="dark"] .sx-teacher-duty td {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .card,
    html[data-bs-theme="dark"] .sx-teacher-duty .card,
    html[data-sx-theme="dark"] .sx-teacher-duty .panel-card,
    html[data-bs-theme="dark"] .sx-teacher-duty .panel-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .panel-card.accent-info,
    html[data-bs-theme="dark"] .sx-teacher-duty .panel-card.accent-info {
        border-top-color: #17a2b8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-active-card,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-active-card {
        background: rgba(32, 114, 39, 0.16) !important;
        border-color: rgba(120, 220, 130, 0.3) !important;
        border-top-color: #207227 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .card-header,
    html[data-bs-theme="dark"] .sx-teacher-duty .card-header {
        background: rgba(148, 0, 0, 0.16) !important;
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-active-card .duty-active-icon,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-active-card .duty-active-icon,
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-active-card .duty-active-title,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-active-card .duty-active-title {
        color: #7be08a !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .table,
    html[data-bs-theme="dark"] .sx-teacher-duty .table {
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-border-color: rgba(255, 180, 180, 0.14);
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .table thead th,
    html[data-bs-theme="dark"] .sx-teacher-duty .table thead th {
        background: rgba(148, 0, 0, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .form-control,
    html[data-bs-theme="dark"] .sx-teacher-duty .form-control {
        background: #1b1215;
        border-color: rgba(255, 180, 180, 0.25);
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-week-row:hover,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-week-row:hover,
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-week-row.is-open,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-week-row.is-open {
        background: rgba(148, 0, 0, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-week-row.active-week,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-week-row.active-week {
        background: rgba(148, 0, 0, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .day-view-container > td,
    html[data-bs-theme="dark"] .sx-teacher-duty .day-view-container > td,
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-days-table tr > td,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-days-table tr > td {
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .duty-days-table tr.is-today > td,
    html[data-bs-theme="dark"] .sx-teacher-duty .duty-days-table tr.is-today > td {
        background: rgba(255, 193, 7, 0.12) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-week,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-week,
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-teacher,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-teacher,
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-upcoming,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-upcoming {
        background: rgba(255, 255, 255, 0.08);
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .active-week .sx-pill-week,
    html[data-bs-theme="dark"] .sx-teacher-duty .active-week .sx-pill-week,
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-teacher.is-me,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-teacher.is-me {
        background: #940000;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-approved,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-approved { background: rgba(32, 114, 39, 0.28); color: #8ae89a !important; }
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-sent,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-sent { background: rgba(13, 110, 253, 0.25); color: #9ec5fe !important; }
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-draft,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-draft { background: rgba(255, 193, 7, 0.2); color: #ffda6a !important; }
    html[data-sx-theme="dark"] .sx-teacher-duty .sx-pill-waiting,
    html[data-bs-theme="dark"] .sx-teacher-duty .sx-pill-waiting { background: rgba(148, 0, 0, 0.3); color: #ffb4b4 !important; }
    html[data-sx-theme="dark"] .sx-teacher-duty .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-teacher-duty .btn-outline-primary { color: #ffb4b4 !important; border-color: rgba(255, 180, 180, 0.45); }
    html[data-sx-theme="dark"] .sx-teacher-duty .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-teacher-duty .btn-outline-primary:hover { background: #940000; color: #fff !important; }
</style>

<div class="sx-teacher-duty">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card accent-maroon">
                <div class="card-body">
                    <h6 class="card-title mb-1" style="color:#940000; font-weight:800; text-transform:uppercase; letter-spacing:0.04em;">
                        {!! __('teacher.duty.title', ['month' => '<u>'.e($monthTitle).'</u>']) !!}
                    </h6>
                    <small class="text-muted">{{ __('teacher.duty.subtitle') }}</small>
                </div>
            </div>
        </div>

        @if(!empty($isClassTeacher))
        <div class="col-12">
            <div class="card panel-card accent-info is-flush">
                <div class="card-header border-0 bg-transparent pb-2">
                    <h6 class="card-title mb-1"><i class="fa-solid fa-eye me-1"></i> {{ __('teacher.duty.perm_title') }}</h6>
                    <small class="text-muted">{{ __('teacher.duty.perm_hint') }}</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher.duty.perm_student') }}</th>
                                    <th>{{ __('teacher.duty.perm_parent') }}</th>
                                    <th>{{ __('teacher.duty.perm_type') }}</th>
                                    <th>{{ __('teacher.duty.perm_date') }}</th>
                                    <th>{{ __('teacher.duty.perm_status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classTeacherPermissions as $perm)
                                    <tr>
                                        <td>{{ trim($perm->first_name . ' ' . ($perm->last_name ?? '')) }}</td>
                                        <td>{{ trim(($perm->parent_first_name ?? '') . ' ' . ($perm->parent_last_name ?? '')) ?: __('teacher.duty.perm_parent_fallback') }}</td>
                                        <td>{{ ucfirst($perm->reason_type) }}</td>
                                        <td>{{ $perm->start_date }} - {{ $perm->end_date }}</td>
                                        <td><span class="sx-pill sx-pill-approved">{{ __('teacher.approved') }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">{{ __('teacher.duty.perm_empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($currentDuty)
        <div class="col-12">
            <div class="duty-active-card">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-calendar-check duty-active-icon"></i>
                    <div>
                        <h6 class="duty-active-title mb-1">{{ __('teacher.duty.active_title') }}</h6>
                        <p class="mb-0">{!! __('teacher.duty.active_body', [
                            'from' => '<strong>'.e(\Carbon\Carbon::parse($currentDuty->start_date)->format('d M')).'</strong>',
                            'to' => '<strong>'.e(\Carbon\Carbon::parse($currentDuty->end_date)->format('d M Y')).'</strong>',
                        ]) !!}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header border-0 pb-0 d-flex flex-wrap align-items-end justify-content-between gap-3">
                    <form action="{{ route('teacher.duty_book') }}" method="GET" class="row g-2 align-items-end flex-grow-1" style="max-width: 720px;">
                        <div class="col-md-4">
                            <label class="form-label mb-1">{{ __('teacher.duty.from_date') }}</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1">{{ __('teacher.duty.to_date') }}</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> {{ __('teacher.filter') }}</button>
                        </div>
                    </form>
                    <h6 class="card-title mb-0" style="color:#940000; font-weight:800;"><i class="fa-solid fa-list-check me-2"></i>{{ __('teacher.duty.assigned_weeks') }}</h6>
                </div>
                <div class="card-body px-0 pb-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4">{{ __('teacher.duty.week') }}</th>
                                    <th>{{ __('teacher.duty.teachers_on_duty') }}</th>
                                    <th>{{ __('teacher.duty.dates') }}</th>
                                    <th class="px-4 text-end">{{ __('teacher.duty.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedDuties = $duties->groupBy(function($item) {
                                        return $item->start_date . '_' . $item->end_date;
                                    });
                                    $weekCounter = 1;
                                    $todayDate = \Carbon\Carbon::today();
                                    $studentLabel = (isset($school_details) && $school_details->school_type == 'Secondary') ? __('teacher.duty.student') : __('teacher.duty.pupil');
                                    $studentsLabel = (isset($school_details) && $school_details->school_type == 'Secondary') ? __('teacher.duty.students') : __('teacher.duty.pupils');
                                @endphp
                                @foreach($groupedDuties as $key => $weekGroup)
                                    @php
                                        $startDate = \Carbon\Carbon::parse($weekGroup[0]->start_date);
                                        $endDate = \Carbon\Carbon::parse($weekGroup[0]->end_date);
                                        $isActiveWeek = $todayDate->between($startDate->copy()->startOfDay(), $endDate->copy()->endOfDay());
                                    @endphp
                                    <tr class="duty-week-row {{ $isActiveWeek ? 'active-week' : '' }}"
                                        data-start="{{ $startDate->format('Y-m-d') }}"
                                        data-end="{{ $endDate->format('Y-m-d') }}">
                                        <td class="py-3 px-4">
                                            <span class="sx-pill sx-pill-week">{{ __('teacher.duty.week_n', ['n' => $weekCounter++]) }}</span>
                                        </td>
                                        <td class="py-3">
                                            @foreach($weekGroup as $duty)
                                                <span class="sx-pill sx-pill-teacher {{ $duty->teacherID == Session::get('teacherID') ? 'is-me' : '' }}">
                                                    <i class="fa-solid fa-circle-user"></i> {{ $duty->teacher ? $duty->teacher->first_name . ' ' . $duty->teacher->last_name : 'N/A' }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="py-3 fw-semibold text-muted">
                                            {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-end">
                                            <i class="fa-solid fa-chevron-down sx-duty-chevron"></i>
                                        </td>
                                    </tr>
                                    <!-- Expandable Day View -->
                                    <tr class="day-view-container" id="days-{{ $startDate->format('Y-m-d') }}" style="display: none;">
                                        <td colspan="4" class="p-0">
                                            <div class="p-3">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0 duty-days-table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('teacher.duty.day') }}</th>
                                                                <th>{{ __('teacher.date') }}</th>
                                                                <th>{{ __('teacher.duty.report_status') }}</th>
                                                                <th>{{ __('teacher.actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @for($i = 0; $i < 7; $i++)
                                                                @php
                                                                    $currentDay = $startDate->copy()->addDays($i);
                                                                    $isToday = $todayDate->isSameDay($currentDay);
                                                                    $dayName = $currentDay->format('l');
                                                                    $dateStr = $currentDay->format('Y-m-d');
                                                                    $report = $reports[$dateStr] ?? null;
                                                                    $isFuture = $currentDay->isAfter($todayDate);
                                                                @endphp
                                                                <tr class="{{ $isToday ? 'is-today' : '' }}">
                                                                    <td class="duty-day-cell fw-semibold">{{ $dayName }}</td>
                                                                    <td>{{ $currentDay->format('d/m/Y') }}</td>
                                                                    <td>
                                                                        @if($report)
                                                                            @if($report->status === 'Approved')
                                                                                <span class="sx-pill sx-pill-approved"><i class="fa-solid fa-circle-check"></i> {{ __('teacher.approved') }}</span>
                                                                            @elseif($report->status === 'Sent')
                                                                                <span class="sx-pill sx-pill-sent"><i class="fa-solid fa-paper-plane"></i> {{ __('teacher.duty.sent_pending') }}</span>
                                                                            @else
                                                                                <span class="sx-pill sx-pill-draft"><i class="fa-solid fa-pencil"></i> {{ __('teacher.duty.draft') }}</span>
                                                                            @endif
                                                                        @elseif($isFuture)
                                                                            <span class="sx-pill sx-pill-upcoming">{{ __('teacher.duty.upcoming') }}</span>
                                                                        @else
                                                                            <span class="sx-pill sx-pill-waiting">{{ __('teacher.duty.waiting') }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($isFuture)
                                                                            <small class="text-muted"><i class="fa-solid fa-lock"></i> {{ __('teacher.duty.available_on', ['date' => $currentDay->format('d M')]) }}</small>
                                                                        @else
                                                                            <div class="d-flex flex-wrap gap-1">
                                                                                @if($report)
                                                                                    <button type="button" class="btn btn-sm btn-outline-primary view-report" data-date="{{ $dateStr }}" data-day="{{ $dayName }}" data-mode="view">
                                                                                        <i class="fa-solid fa-eye"></i> {{ __('teacher.duty.view_filled') }}
                                                                                    </button>
                                                                                    @if($report->status !== 'Approved' && $report->status !== 'Sent')
                                                                                        <button type="button" class="btn btn-sm btn-primary view-report" data-date="{{ $dateStr }}" data-day="{{ $dayName }}" data-mode="edit">
                                                                                            <i class="fa-solid fa-pen-to-square"></i> {{ __('teacher.duty.fill_form') }}
                                                                                        </button>
                                                                                    @endif
                                                                                @else
                                                                                    <button type="button" class="btn btn-sm btn-primary view-report" data-date="{{ $dateStr }}" data-day="{{ $dayName }}" data-mode="edit">
                                                                                        <i class="fa-solid fa-plus"></i> {{ __('teacher.duty.fill_form') }}
                                                                                    </button>
                                                                                @endif

                                                                                @if(!$report || ($report->status !== 'Approved' && $report->status !== 'Sent'))
                                                                                    <button type="button" class="btn btn-sm btn-success send-to-admin" data-date="{{ $dateStr }}">
                                                                                        <i class="fa-solid fa-paper-plane"></i> {{ __('teacher.duty.send_to_admin') }}
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($groupedDuties->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">{{ __('teacher.duty.no_assignments') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.duty_report_modal', ['classes' => $classes])
@endsection

@push('scripts')
<script>
    var i18n = @json(__('teacher.duty'));
    i18n.approved = @json(__('teacher.approved'));
    i18n.pending = @json(__('teacher.pending'));
    i18n.rejected = @json(__('teacher.rejected'));
    i18n.close = @json(__('teacher.close'));
    i18n.filter = @json(__('teacher.filter'));
    i18n.date = @json(__('teacher.date'));
    i18n.status = @json(__('teacher.status'));
    i18n.actions = @json(__('teacher.actions'));
    i18n.loading = @json(__('teacher.loading'));
    window.dutyI18n = i18n;
    window.currentTeacherName = @json($currentTeacherName ?? '');

    function showDutyReportModal() {
        var el = document.getElementById('dutyReportModal');
        if (!el) return;
        if (el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, keyboard: true }).show();
        } else if (window.jQuery && jQuery.fn.modal) {
            jQuery(el).modal('show');
        }
    }

$(document).ready(function() {
    // Toggle week expansion
    $('.duty-week-row').click(function() {
        let start = $(this).data('start');
        let $child = $('#days-' + start);
        const opening = !$child.is(':visible');
        $('.day-view-container').not($child).hide();
        $('.duty-week-row').not(this).removeClass('is-open');
        $child.toggle(opening);
        $(this).toggleClass('is-open', opening);
    });

    // Handle inputs for auto-totals
    $(document).on('input', '#attendance_table input', function() {
        let $row = $(this).closest('tr');
        
        // Sum boys + girls for each category in the row
        const categories = ['.reg', '.pres', '.shift', '.new', '.abs', '.perm', '.sick'];
        categories.forEach(cat => {
            let b = parseInt($row.find(cat + '-b').val()) || 0;
            let g = parseInt($row.find(cat + '-g').val()) || 0;
            $row.find(cat + '-t').val(b + g);
        });

        calculateFooterTotals();
    });

    function calculateFooterTotals() {
        const columns = [
            '.reg-b', '.reg-g', '.reg-t',
            '.pres-b', '.pres-g', '.pres-t',
            '.shift-b', '.shift-g', '.shift-t',
            '.new-b', '.new-g', '.new-t',
            '.abs-b', '.abs-g', '.abs-t',
            '.perm-b', '.perm-g', '.perm-t',
            '.sick-b', '.sick-g', '.sick-t'
        ];

        columns.forEach((selector, index) => {
            let sum = 0;
            $(selector).each(function() {
                sum += parseInt($(this).val()) || 0;
            });
            $('#total-' + index).text(sum);
        });

        let totalPres = parseInt($('#total-5').text()) || 0;
        let totalReg = parseInt($('#total-2').text()) || 0;

        if (totalReg > 0) {
            let perc = (totalPres / totalReg) * 100;
            $('#attendance_percentage').val(perc.toFixed(2));
        }
    }

    // New helper to refresh both row totals and footer totals
    function refreshTableTotals() {
        $('.class-row').each(function() {
            let $row = $(this);
            const categories = ['.reg', '.pres', '.shift', '.new', '.abs', '.perm', '.sick'];
            categories.forEach(cat => {
                let b = parseInt($row.find(cat + '-b').val()) || 0;
                let g = parseInt($row.find(cat + '-g').val()) || 0;
                $row.find(cat + '-t').val(b + g);
            });
        });
        calculateFooterTotals();
    }

    // View/Fill Form
    $('.view-report').click(function() {
        let date = $(this).data('date');
        let day = $(this).data('day');
        let mode = $(this).data('mode'); // edit or view
        
        $('#report_date').val(date);
        $('#display_day').text(day.toUpperCase());
        $('#display_date').text(date);
        
        // Modal State Reset
        $('#dailyDutyForm')[0].reset();
        $('#modal_status_badge').html('');
        $('#adminFeedbackSection, #btnApproveReport').hide();
        
            if (mode === 'view') {
                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', true);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#f8f9fa');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').hide();
                $('#downloadReportPdf').show();
            } else {
                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', false);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#fff');
                // Row totals + % stay auto-calculated; boy/girl counts are editable
                $('#attendance_table input[class$="-t"]').prop('readonly', true).css('background-color', '#f8f9fa');
                $('#attendance_percentage').prop('readonly', true).css('background-color', '#e8f5e9');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').show();
                $('#downloadReportPdf').hide();
            }

        // Load existing report
        $.get("{{ route('teacher.duty_book.report') }}", { date: date }, function(response) {
            let report = response.report;
            let systemData = response.system_attendance;
            window.totalActiveStudentsInSchool = response.total_active_students || 0;

            if (response.academic_year_label) {
                $('#duty_enrollment_scope_note').html(
                    '<i class="fa fa-info-circle me-1" style="color: #940000;"></i> ' +
                    String(i18n.counts_note || '').replace(':year', response.academic_year_label)
                );
            }

            $('#display_teacher_name').text(
                (report && report.teacher_name) || response.teacher_name || window.currentTeacherName || '---'
            );

            if (report) {
                $('#reportID').val(report.reportID);
                // Set Status Badge
                let badgeClass = 'bg-secondary';
                if (report.status === 'Approved') badgeClass = 'bg-success';
                else if (report.status === 'Sent') badgeClass = 'bg-primary';
                else if (report.status === 'Draft') badgeClass = 'bg-info text-dark';
                
                var statusLabel = report.status;
                if (report.status === 'Approved') statusLabel = i18n.approved;
                else if (report.status === 'Sent') statusLabel = i18n.sent_status;
                else if (report.status === 'Draft') statusLabel = i18n.draft;
                $('#modal_status_badge').html('<span class="badge ' + badgeClass + '">' + statusLabel + '</span>');

                // Load saved report data
                $('input[name="attendance_percentage"]').val(report.attendance_percentage);
                $('input[name="school_environment"]').val(report.school_environment);
                $('input[name="pupils_cleanliness"]').val(report.pupils_cleanliness);
                $('textarea[name="meals"]').val(report.meals);
                $('input[name="timetable_status"]').val(report.timetable_status);
                $('input[name="outside_activities"]').val(report.outside_activities);
                $('input[name="special_events"]').val(report.special_events);
                $('input[name="english_speaking"]').val(report.english_speaking);
                $('textarea[name="sick_students_list"]').val(report.sick_students_list);
                $('textarea[name="teacher_comments"]').val(report.teacher_comments);
                
                // Admin feedback handling
                if (report.signed_by || report.signature_image) {
                    $('#adminFeedbackSection').show();
                    $('#admin_comments').val(report.admin_comments).prop('readonly', true);
                    $('#signed_by').val(report.signed_by).prop('readonly', true);
                    
                    if (report.signature_image) {
                        $('#signature-image-preview').attr('src', report.signature_image);
                        $('#view-only-signature').show();
                        $('#signature-pad, #clear-signature').hide();
                    } else {
                        $('#view-only-signature, #signature-pad, #clear-signature').hide();
                    }

                    if (report.signed_at) {
                        $('#signedAtDate').text(moment(report.signed_at).format('DD/MM/YYYY HH:mm'));
                        $('#signedAtDisplay').show();
                    }
                }
                
                // Load attendance grid from report
                if (report.attendance_data) {
                    let data = typeof report.attendance_data === 'string' ? JSON.parse(report.attendance_data) : report.attendance_data;
                    $('.class-row').each(function() {
                        let cid = $(this).data('class-id');
                        if (data[cid]) {
                            $(this).find('.reg-b').val(data[cid].reg_b);
                            $(this).find('.reg-g').val(data[cid].reg_g);
                            $(this).find('.pres-b').val(data[cid].pres_b);
                            $(this).find('.pres-g').val(data[cid].pres_g);
                            $(this).find('.shift-b').val(data[cid].shift_b || 0);
                            $(this).find('.shift-g').val(data[cid].shift_g || 0);
                            $(this).find('.new-b').val(data[cid].new_b || 0);
                            $(this).find('.new-g').val(data[cid].new_g || 0);
                            $(this).find('.abs-b').val(data[cid].abs_b || 0);
                            $(this).find('.abs-g').val(data[cid].abs_g || 0);
                            $(this).find('.perm-b').val(data[cid].perm_b || 0);
                            $(this).find('.perm-g').val(data[cid].perm_g || 0);
                            $(this).find('.sick-b').val(data[cid].sick_b || 0);
                            $(this).find('.sick-g').val(data[cid].sick_g || 0);
                        }
                    });
                }
            } else {
                $('#modal_status_badge').html('<span class="badge bg-light text-dark">' + i18n.new_report + '</span>');
                if (systemData) {
                    // New report: Pre-fill with system defaults
                    // Set calculated attendance percentage
                    if (response.calculated_attendance_percentage !== undefined) {
                        $('input[name="attendance_percentage"]').val(response.calculated_attendance_percentage);
                    }

                    $('.class-row').each(function() {
                        let cid = $(this).data('class-id');
                        if (systemData[cid]) {
                            $(this).find('.reg-b').val(systemData[cid].reg_b);
                            $(this).find('.reg-g').val(systemData[cid].reg_g);
                            $(this).find('.pres-b').val(systemData[cid].pres_b);
                            $(this).find('.pres-g').val(systemData[cid].pres_g);
                            $(this).find('.abs-b').val(systemData[cid].abs_b);
                            $(this).find('.abs-g').val(systemData[cid].abs_g);
                            $(this).find('.perm-b').val(systemData[cid].perm_b);
                            $(this).find('.perm-g').val(systemData[cid].perm_g);
                            $(this).find('.new-b').val(systemData[cid].new_b);
                            $(this).find('.new-g').val(systemData[cid].new_g);
                            $(this).find('.sick-b').val(systemData[cid].sick_b);
                            $(this).find('.sick-g').val(systemData[cid].sick_g);
                        }
                    });
                }
            }

            // Re-apply edit/view locks after loading data (reset() can leave mixed state)
            if (mode === 'view') {
                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', true);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#f8f9fa');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').hide();
                $('#downloadReportPdf').show();
            } else {
                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', false);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#fff');
                $('#attendance_table input[class$="-t"]').prop('readonly', true).css('background-color', '#f8f9fa');
                $('#attendance_percentage').prop('readonly', true).css('background-color', '#e8f5e9');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').show();
                $('#downloadReportPdf').hide();
            }

            // Refresh all totals and calculations
            refreshTableTotals();
            showDutyReportModal();
        });
    });

    // Sync From Attendance Button Logic
    $('#syncFromAttendance').click(function() {
        let date = $('#report_date').val();
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + i18n.syncing);

        $.get("{{ route('teacher.duty_book.report') }}", { date: date }, function(response) {
            let systemData = response.system_attendance;
            window.totalActiveStudentsInSchool = response.total_active_students || 0; // Store total active students
            if (systemData) {
                // Update attendance percentage
                if (response.calculated_attendance_percentage !== undefined) {
                    $('input[name="attendance_percentage"]').val(response.calculated_attendance_percentage);
                }

                $('.class-row').each(function() {
                    let cid = $(this).data('class-id');
                    if (systemData[cid]) {
                        $(this).find('.reg-b').val(systemData[cid].reg_b);
                        $(this).find('.reg-g').val(systemData[cid].reg_g);
                        $(this).find('.pres-b').val(systemData[cid].pres_b);
                        $(this).find('.pres-g').val(systemData[cid].pres_g);
                        $(this).find('.abs-b').val(systemData[cid].abs_b);
                        $(this).find('.abs-g').val(systemData[cid].abs_g);
                        $(this).find('.perm-b').val(systemData[cid].perm_b);
                        $(this).find('.perm-g').val(systemData[cid].perm_g);
                        $(this).find('.new-b').val(systemData[cid].new_b);
                        $(this).find('.new-g').val(systemData[cid].new_g);
                        $(this).find('.sick-b').val(systemData[cid].sick_b);
                        $(this).find('.sick-g').val(systemData[cid].sick_g);
                    }
                });
                refreshTableTotals();
                Swal.fire({
                    title: i18n.synced,
                    text: (response.attendance_source === 'fingerprint'
                        ? i18n.synced_fp
                        : i18n.synced_sys) +
                        (response.academic_year_label ? ' (' + response.academic_year_label + ').' : '.'),
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        }).fail(function() {
            Swal.fire(i18n.error, i18n.fail_sync, 'error');
        }).always(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
    });

    // Export Roster PDF using AJAX
    $('#exportRosterBtn').click(function() {
        const fromDate = $(this).data('from');
        const toDate = $(this).data('to');
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + i18n.generating_roster);

        fetch("{{ route('teacher.duty_book.export') }}?from_date=" + fromDate + "&to_date=" + toDate)
            .then(response => {
                if(!response.ok) throw new Error('Network response was not ok');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = 'Teacher_Duty_Roster_' + fromDate + '_to_' + toDate + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Download error:', error);
                Swal.fire(i18n.error, i18n.fail_roster, 'error');
            })
            .finally(() => {
                $btn.prop('disabled', false).html(originalHtml);
            });
    });
    
    // Download Individual Report PDF
    $('#downloadReportPdf').click(function() {
        const date = $('#report_date').val();
        const reportID = $('#reportID').val();
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + i18n.generating_pdf);

        fetch("{{ route('teacher.duty_book.export_report') }}?date=" + date + "&reportID=" + reportID)
            .then(response => {
                if(!response.ok) throw new Error('Network response was not ok');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = 'Daily_Duty_Report_' + date + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                
                Swal.fire({ title: i18n.success_short, text: i18n.report_downloaded, icon: 'success', timer: 1500, showConfirmButton: false });
            })
            .catch(error => {
                console.error('Download error:', error);
                Swal.fire(i18n.error, i18n.fail_pdf + ' ' + error.message, 'error');
            })
            .finally(() => {
                $btn.prop('disabled', false).html(originalHtml);
            });
    });

    // Save Draft or Send
    $('#saveDraft, #saveAndSend').click(function() {
        let action = $(this).attr('id') === 'saveAndSend' ? 'send' : 'draft';
        let attendanceData = {};
        
        $('.class-row').each(function() {
            let cid = $(this).data('class-id');
            attendanceData[cid] = {
                reg_b: $(this).find('.reg-b').val(),
                reg_g: $(this).find('.reg-g').val(),
                pres_b: $(this).find('.pres-b').val(),
                pres_g: $(this).find('.pres-g').val(),
                shift_b: $(this).find('.shift-b').val(),
                shift_g: $(this).find('.shift-g').val(),
                new_b: $(this).find('.new-b').val(),
                new_g: $(this).find('.new-g').val(),
                abs_b: $(this).find('.abs-b').val(),
                abs_g: $(this).find('.abs-g').val(),
                perm_b: $(this).find('.perm-b').val(),
                perm_g: $(this).find('.perm-g').val(),
                sick_b: $(this).find('.sick-b').val(),
                sick_g: $(this).find('.sick-g').val()
            };
        });

        let formData = {
            _token: "{{ csrf_token() }}",
            report_date: $('#report_date').val(),
            attendance_data: JSON.stringify(attendanceData),
            attendance_percentage: $('#attendance_percentage').val(),
            school_environment: $('input[name="school_environment"]').val(),
            pupils_cleanliness: $('input[name="pupils_cleanliness"]').val(),
            meals: $('textarea[name="meals"]').val(),
            timetable_status: $('input[name="timetable_status"]').val(),
            outside_activities: $('input[name="outside_activities"]').val(),
            special_events: $('input[name="special_events"]').val(),
            english_speaking: $('input[name="english_speaking"]').val(),
            sick_students_list: $('textarea[name="sick_students_list"]').val(),
            teacher_comments: $('textarea[name="teacher_comments"]').val(),
            action: action
        };

        const $btn = $(this);
        const originalHtml = $btn.html();
        
        if (action === 'send') {
            Swal.fire({
                title: i18n.sending_report,
                html: i18n.sending_wait,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + i18n.processing);

        $.post("{{ route('teacher.duty_book.save') }}", formData, function(response) {
            Swal.fire({
                title: i18n.success,
                text: response.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload(); 
            });
        }).fail(function(err) {
            let msg = i18n.fail_save;
            if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
            Swal.fire(i18n.error, msg, 'error');
        }).always(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
    });
    
    // Quick send from the table
    $('.send-to-admin').click(function() {
        let date = $(this).data('date');
        Swal.fire({
            title: i18n.send_confirm_title,
            text: i18n.send_confirm_text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: i18n.yes_send,
            cancelButtonText: i18n.close
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: i18n.sending_report,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("{{ route('teacher.duty_book.save') }}", {
                    _token: "{{ csrf_token() }}",
                    report_date: date,
                    action: 'send'
                }, function(response) {
                    Swal.fire({
                        title: i18n.sent,
                        text: i18n.sent_ok,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }).fail(function() {
                    Swal.fire(i18n.error, i18n.fail_send, 'error');
                });
            }
        });
    });
});
</script>
@endpush