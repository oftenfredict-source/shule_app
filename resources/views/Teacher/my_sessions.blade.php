@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.my_sessions').' - ShuleXpert')
@section('page_title', __('teacher.pages.my_sessions'))
@section('page_breadcrumb', __('teacher.pages.my_sessions_bc'))

@section('page_actions')
    @if(!isset($message))
    <button type="button" class="btn btn-primary btn-sm waves-effect" id="downloadMySessionsPdfBtn">
        <i class="fa-solid fa-file-pdf me-1"></i> {{ __('teacher.download_pdf') }}
    </button>
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-sessions label {
        color: #000 !important;
    }
    .sx-teacher-sessions .card,
    .sx-teacher-sessions .sx-sessions-toolbar {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
    }
    .sx-teacher-sessions .sx-sessions-toolbar {
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-sessions .sx-sessions-toolbar .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-sessions .form-select,
    .sx-teacher-sessions .form-control,
    .sx-teacher-sessions .btn {
        border-radius: 0 !important;
    }
    .sx-teacher-sessions .form-select {
        min-height: 42px;
        font-weight: 700;
        border: 1px solid #cfcfcf;
    }
    .sx-teacher-sessions .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    .sx-teacher-sessions .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000;
        font-weight: 800;
    }
    .sx-teacher-sessions .btn-outline-primary:hover {
        background: #940000;
        color: #fff !important;
    }
    .sx-teacher-sessions .btn-primary-custom,
    .btn-primary-custom {
        background: #940000;
        border-color: #940000;
        color: #fff;
        font-weight: 800;
    }
    .sx-teacher-sessions .btn-primary-custom:hover,
    .btn-primary-custom:hover {
        background: #7b0000;
        border-color: #7b0000;
        color: #fff;
    }
    .sx-teacher-sessions .sx-day-panel { display: none; }
    .sx-teacher-sessions .sx-day-panel.is-active { display: block; }
    .sx-teacher-sessions .sx-day-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem 0.75rem;
        margin-bottom: 0.75rem;
        font-size: 0.86rem;
        font-weight: 600;
    }
    .sx-teacher-sessions .sx-day-meta .sx-day-title {
        color: #940000 !important;
        font-weight: 800;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-teacher-sessions .table.sx-sessions-table {
        margin-bottom: 0;
        background: #fff;
    }
    .sx-teacher-sessions .table.sx-sessions-table thead th {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #940000 !important;
        background: #fcf7f7;
        border-bottom: 1px solid rgba(148, 0, 0, 0.12);
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.65rem 0.75rem;
    }
    .sx-teacher-sessions .table.sx-sessions-table td {
        vertical-align: middle;
        font-size: 0.9rem;
        padding: 0.65rem 0.75rem;
    }
    .sx-teacher-sessions .table.sx-sessions-table tr.is-active {
        background: #fff8f8;
        box-shadow: inset 3px 0 0 #940000;
    }
    .sx-teacher-sessions .sx-session-subject {
        font-weight: 800;
        color: #111 !important;
        margin: 0;
        line-height: 1.25;
    }
    .sx-teacher-sessions .sx-session-class {
        font-size: 0.8rem;
        color: #666 !important;
        margin: 0.15rem 0 0;
    }
    .sx-teacher-sessions .sx-session-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        justify-content: flex-end;
    }
    .sx-teacher-sessions .btn-session-action {
        background: #fff !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: 0.28rem 0.55rem;
        font-weight: 800;
        font-size: 0.74rem;
        white-space: nowrap;
        width: auto;
    }
    .sx-teacher-sessions .btn-session-action:hover,
    .sx-teacher-sessions .btn-session-action:focus {
        background: #fcf7f7 !important;
        color: #6e0000 !important;
    }
    .sx-teacher-sessions .sx-empty-day {
        border: 1px dashed #ddd;
        background: #fafafa;
        padding: 1.1rem;
        text-align: center;
        color: #777 !important;
        font-size: 0.88rem;
        font-weight: 600;
    }
    .sx-teacher-sessions .text-primary-custom { color: #940000 !important; }
    .sx-teacher-sessions .text-muted { color: #666 !important; }
    @media (max-width: 767.98px) {
        .sx-teacher-sessions .sx-session-actions {
            justify-content: flex-start;
        }
        .sx-teacher-sessions .table.sx-sessions-table thead { display: none; }
        .sx-teacher-sessions .table.sx-sessions-table tr {
            display: block;
            border: 1px solid #e6e6e6;
            margin-bottom: 0.65rem;
            padding: 0.65rem 0.75rem;
        }
        .sx-teacher-sessions .table.sx-sessions-table td {
            display: block;
            border: none;
            padding: 0.2rem 0;
        }
        .sx-teacher-sessions .table.sx-sessions-table td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #940000;
            margin-bottom: 0.15rem;
        }
        .sx-teacher-sessions .table.sx-sessions-table td[data-label=""]::before { display: none; }
    }

    #attendanceTabs .nav-link {
        color: #940000;
        border-radius: 0 !important;
        font-weight: 800;
    }
    #attendanceTabs .nav-link.active {
        background-color: #940000 !important;
        color: #fff !important;
        border-color: #940000 !important;
    }

    #collectAttendanceModal .modal-content,
    #assignTaskModal .modal-content,
    #mySessionsPdfProgressModal .modal-content {
        border: none;
        border-radius: 0;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
    }
    #collectAttendanceModal .modal-header,
    #assignTaskModal .modal-header {
        border: none;
        padding: 1.1rem 1.35rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
    }
    #collectAttendanceModal .modal-title,
    #assignTaskModal .modal-title {
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 800;
        color: #fff !important;
    }
    #assignTaskModal .modal-body {
        background: #faf7f7;
        padding: 1.2rem 1.35rem;
    }
    #assignTaskModal .form-group { margin-bottom: 0.95rem; }
    #assignTaskModal label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.35rem;
    }
    #assignTaskModal .form-control {
        border-radius: 0 !important;
        min-height: 44px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    #assignTaskModal .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    #mySessionsPdfProgressModal { z-index: 1085; }
    @keyframes mySessionsPdfPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-sessions .card,
    html[data-bs-theme="dark"] .sx-teacher-sessions .card,
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-sessions-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-sessions-toolbar {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-sessions-toolbar .form-label,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-sessions-toolbar .form-label,
    html[data-sx-theme="dark"] .sx-teacher-sessions .form-label,
    html[data-bs-theme="dark"] .sx-teacher-sessions .form-label,
    html[data-sx-theme="dark"] .sx-teacher-sessions label,
    html[data-bs-theme="dark"] .sx-teacher-sessions label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .form-select,
    html[data-bs-theme="dark"] .sx-teacher-sessions .form-select,
    html[data-sx-theme="dark"] .sx-teacher-sessions .form-control,
    html[data-bs-theme="dark"] .sx-teacher-sessions .form-control {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-sessions .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-teacher-sessions .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #940000 !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-teacher-sessions .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-day-meta .sx-day-title,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-day-meta .sx-day-title,
    html[data-sx-theme="dark"] .sx-teacher-sessions .text-primary-custom,
    html[data-bs-theme="dark"] .sx-teacher-sessions .text-primary-custom {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-day-meta,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-day-meta,
    html[data-sx-theme="dark"] .sx-teacher-sessions .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-sessions .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-session-class,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-session-class {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table,
    html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table thead th,
    html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table thead th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-bottom-color: #6e0000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table td,
    html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table td {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table tr.is-active,
    html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table tr.is-active,
    html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table tr.is-active td,
    html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table tr.is-active td {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-session-subject,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-session-subject {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .btn-session-action,
    html[data-bs-theme="dark"] .sx-teacher-sessions .btn-session-action {
        background: #1c1417 !important;
        color: #ffb4b4 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .btn-session-action:hover,
    html[data-bs-theme="dark"] .sx-teacher-sessions .btn-session-action:hover,
    html[data-sx-theme="dark"] .sx-teacher-sessions .btn-session-action:focus,
    html[data-bs-theme="dark"] .sx-teacher-sessions .btn-session-action:focus {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .sx-empty-day,
    html[data-bs-theme="dark"] .sx-teacher-sessions .sx-empty-day {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-sessions .table-responsive,
    html[data-bs-theme="dark"] .sx-teacher-sessions .table-responsive,
    html[data-sx-theme="dark"] .sx-teacher-sessions .card-body,
    html[data-bs-theme="dark"] .sx-teacher-sessions .card-body {
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] #attendanceTabs .nav-link,
    html[data-bs-theme="dark"] #attendanceTabs .nav-link {
        color: #ffb4b4 !important;
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #attendanceTabs .nav-link.active,
    html[data-bs-theme="dark"] #attendanceTabs .nav-link.active {
        background: #940000 !important;
        color: #fff !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] #collectAttendanceModal .modal-content,
    html[data-bs-theme="dark"] #collectAttendanceModal .modal-content,
    html[data-sx-theme="dark"] #assignTaskModal .modal-content,
    html[data-bs-theme="dark"] #assignTaskModal .modal-content,
    html[data-sx-theme="dark"] #mySessionsPdfProgressModal .modal-content,
    html[data-bs-theme="dark"] #mySessionsPdfProgressModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] #assignTaskModal .modal-body,
    html[data-bs-theme="dark"] #assignTaskModal .modal-body,
    html[data-sx-theme="dark"] #collectAttendanceModal .modal-body,
    html[data-bs-theme="dark"] #collectAttendanceModal .modal-body {
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] #assignTaskModal label,
    html[data-bs-theme="dark"] #assignTaskModal label,
    html[data-sx-theme="dark"] #collectAttendanceModal label,
    html[data-bs-theme="dark"] #collectAttendanceModal label,
    html[data-sx-theme="dark"] #assignTaskModal .form-label,
    html[data-bs-theme="dark"] #assignTaskModal .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #assignTaskModal .form-control,
    html[data-bs-theme="dark"] #assignTaskModal .form-control,
    html[data-sx-theme="dark"] #assignTaskModal .form-select,
    html[data-bs-theme="dark"] #assignTaskModal .form-select,
    html[data-sx-theme="dark"] #collectAttendanceModal .form-control,
    html[data-bs-theme="dark"] #collectAttendanceModal .form-control,
    html[data-sx-theme="dark"] #collectAttendanceModal .form-select,
    html[data-bs-theme="dark"] #collectAttendanceModal .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #assignTaskModal .modal-footer,
    html[data-bs-theme="dark"] #assignTaskModal .modal-footer,
    html[data-sx-theme="dark"] #collectAttendanceModal .modal-footer,
    html[data-bs-theme="dark"] #collectAttendanceModal .modal-footer {
        background: #1c1417 !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #mySessionsPdfProgressModal .text-muted,
    html[data-bs-theme="dark"] #mySessionsPdfProgressModal .text-muted {
        color: #d7c4c8 !important;
    }
    @media (max-width: 767.98px) {
        html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table tr,
        html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table tr {
            border-color: #3a2a2e !important;
            background: #24181c !important;
        }
        html[data-sx-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table td::before,
        html[data-bs-theme="dark"] .sx-teacher-sessions .table.sx-sessions-table td::before {
            color: #ffb4b4 !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $todayName = \Carbon\Carbon::now(config('app.timezone'))->format('l');
    $defaultDay = in_array($todayName, $days ?? [], true) ? $todayName : (($days[0] ?? 'Monday'));
@endphp
<div class="sx-teacher-sessions">
    @if(isset($message))
        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle"></i> {{ $message }}
        </div>
    @else
        <div class="sx-sessions-toolbar">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <div class="form-label mb-1">{{ __('teacher.week') }}</div>
                    <div class="fw-bold" style="font-size:0.92rem;">
                        {{ $startOfWeek->format('d M Y') }} — {{ $startOfWeek->copy()->endOfWeek()->format('d M Y') }}
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeWeek(-1)">
                            <i class="bi bi-chevron-left"></i> {{ __('teacher.prev_week') }}
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeWeek(0)">
                            <i class="bi bi-calendar"></i> {{ __('teacher.this_week') }}
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeWeek(1)">
                            {{ __('teacher.next_week') }} <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="col-lg-7">
                    <label class="form-label" for="sessionDaySelect">{{ __('teacher.select_day') }}</label>
                    <select id="sessionDaySelect" class="form-select">
                        @foreach($days as $day)
                            @php
                                $optDate = $startOfWeek->copy()->addDays(array_search($day, $days));
                                $optCount = ($sessionsByDay[$day] ?? collect())->count();
                                $optHoliday = in_array($optDate->format('Y-m-d'), $holidayDates ?? []);
                            @endphp
                            <option value="{{ $day }}" @selected($day === $defaultDay)>
                                {{ $day }} · {{ $optDate->format('d M Y') }}
                                ({{ $optCount }} {{ $optCount === 1 ? __('teacher.sess.session') : __('teacher.sess.sessions') }}){{ $optHoliday ? ' · '.__('teacher.sess.holiday') : '' }}{{ $optDate->isToday() ? ' · '.__('teacher.sess.today') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @foreach($days as $day)
            @php
                $dayDate = $startOfWeek->copy()->addDays(array_search($day, $days));
                $isHoliday = in_array($dayDate->format('Y-m-d'), $holidayDates ?? []);
                $daySessions = $sessionsByDay[$day] ?? collect();
                $dayOfWeek = $dayDate->dayOfWeek;
                $isWeekend = ($dayOfWeek === \Carbon\Carbon::SATURDAY || $dayOfWeek === \Carbon\Carbon::SUNDAY);
            @endphp

            <div class="sx-day-panel {{ $day === $defaultDay ? 'is-active' : '' }}" data-day="{{ $day }}">
                <div class="card border-0">
                    <div class="card-body p-3">
                        <div class="sx-day-meta">
                            <span class="sx-day-title">{{ $day }} · {{ $dayDate->format('d M Y') }}</span>
                            @if($dayDate->isToday())
                                <span class="badge bg-success">{{ __('teacher.sess.today') }}</span>
                            @endif
                            @if($isHoliday)
                                <span class="badge bg-warning text-dark">{{ __('teacher.sess.holiday') }}</span>
                            @endif
                            @if($isWeekend)
                                <span class="badge bg-secondary">{{ __('teacher.sess.weekend') }}</span>
                            @endif
                            <span class="text-muted">{{ $daySessions->count() }} {{ $daySessions->count() === 1 ? __('teacher.sess.session') : __('teacher.sess.sessions') }}</span>
                        </div>

                        @if($daySessions->isEmpty())
                            <div class="sx-empty-day">
                                <i class="bi bi-info-circle"></i> {{ __('teacher.no_sessions_day') }}
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover sx-sessions-table align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width:140px;">{{ __('teacher.time') }}</th>
                                            <th>{{ __('teacher.subject') }} / {{ __('teacher.class') }}</th>
                                            <th style="width:110px;">{{ __('teacher.status') }}</th>
                                            <th style="width:280px;" class="text-end">{{ __('teacher.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($daySessions as $session)
                                            @php
                                                $startTimeStr = is_string($session->start_time)
                                                    ? $session->start_time
                                                    : ($session->start_time instanceof \DateTime
                                                        ? $session->start_time->format('H:i:s')
                                                        : '00:00:00');
                                                $endTimeStr = is_string($session->end_time)
                                                    ? $session->end_time
                                                    : ($session->end_time instanceof \DateTime
                                                        ? $session->end_time->format('H:i:s')
                                                        : '00:00:00');
                                                if (strlen($startTimeStr) == 5) { $startTimeStr .= ':00'; }
                                                if (strlen($endTimeStr) == 5) { $endTimeStr .= ':00'; }

                                                $sessionDateTime = $dayDate->copy()->setTimeFromTimeString($startTimeStr);
                                                $sessionEndDateTime = $dayDate->copy()->setTimeFromTimeString($endTimeStr);
                                                $now = \Carbon\Carbon::now(config('app.timezone'));
                                                $isToday = $dayDate->isToday();
                                                $todayDayName = $now->format('l');
                                                $isTodaySession = $isToday && ($session->day === $todayDayName);
                                                $isWithinSessionTime = ($now >= $sessionDateTime) && ($now <= $sessionEndDateTime);
                                                $isSessionTime = $isWithinSessionTime;
                                                $isPast = $now > $sessionEndDateTime;

                                                $task = \App\Models\SessionTask::where('session_timetableID', $session->session_timetableID)
                                                    ->where('task_date', $dayDate->format('Y-m-d'))
                                                    ->first();

                                                $subjectName = 'N/A';
                                                if ($session->classSubject && $session->classSubject->subject && $session->classSubject->subject->subject_name) {
                                                    $subjectName = $session->classSubject->subject->subject_name;
                                                } elseif ($session->subject && $session->subject->subject_name) {
                                                    $subjectName = $session->subject->subject_name;
                                                }
                                                if ($session->is_prepo) {
                                                    $subjectName .= ' (Prepo)';
                                                }
                                                $className = trim(($session->subclass->class->class_name ?? '') . ' - ' . ($session->subclass->subclass_name ?? ''), ' -');
                                                $startTimeStr = $session->start_time ? (\Carbon\Carbon::parse($session->start_time)->format('H:i:s')) : '00:00:00';
                                                $endTimeStr = $session->end_time ? (\Carbon\Carbon::parse($session->end_time)->format('H:i:s')) : '00:00:00';
                                            @endphp
                                            <tr class="{{ $isSessionTime ? 'is-active' : '' }}">
                                                <td data-label="{{ __('teacher.time') }}">
                                                    <strong>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</strong>
                                                    <div class="text-muted" style="font-size:0.78rem;">– {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}</div>
                                                </td>
                                                <td data-label="{{ __('teacher.subject') }} / {{ __('teacher.class') }}">
                                                    <p class="sx-session-subject">{{ $subjectName }}</p>
                                                    <p class="sx-session-class"><i class="bi bi-people"></i> {{ $className }}</p>
                                                    @if($task)
                                                        @php
                                                            $taskStatusLabel = $task->status === 'approved'
                                                                ? __('teacher.approved')
                                                                : ($task->status === 'rejected' ? __('teacher.rejected') : __('teacher.pending'));
                                                        @endphp
                                                        <span class="badge bg-{{ $task->status === 'approved' ? 'success' : ($task->status === 'rejected' ? 'danger' : 'warning') }}{{ $task->status !== 'approved' && $task->status !== 'rejected' ? ' text-dark' : '' }}" style="font-size:0.68rem;">
                                                            {{ __('teacher.sess.task_status', ['status' => $taskStatusLabel]) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td data-label="{{ __('teacher.status') }}">
                                                    @if($isSessionTime)
                                                        <span class="badge bg-success">{{ __('teacher.active') }}</span>
                                                    @elseif($isPast)
                                                        <span class="badge bg-secondary">{{ __('teacher.sess.done') }}</span>
                                                    @else
                                                        <span class="badge bg-light text-dark border">{{ __('teacher.sess.upcoming') }}</span>
                                                    @endif
                                                </td>
                                                <td data-label="" class="text-end">
                                                    <div class="sx-session-actions">
                                                        <button type="button" class="btn btn-session-action btn-sm"
                                                            onclick="collectAttendance('{{ $session->session_timetableID }}', '{{ $dayDate->format('Y-m-d') }}')">
                                                            <i class="bi bi-clipboard-check"></i> {{ __('teacher.collect_attendance') }}
                                                        </button>
                                                        <button type="button" class="btn btn-session-action btn-sm"
                                                            onclick="assignTask('{{ $session->session_timetableID }}', '{{ $dayDate->format('Y-m-d') }}', '{{ $startTimeStr }}', '{{ $endTimeStr }}')">
                                                            <i class="bi bi-journal-plus"></i> {{ __('teacher.assign_task') }}
                                                        </button>
                                                        <button type="button" class="btn btn-session-action btn-sm"
                                                            onclick="openLessonPlan('{{ $session->session_timetableID }}', '{{ $session->day }}', '{{ $startTimeStr }}', '{{ $endTimeStr }}', '{{ addslashes($subjectName) }}', '{{ addslashes($className) }}', '{{ $dayDate->format('Y-m-d') }}')">
                                                            <i class="bi bi-journal-text"></i> {{ __('teacher.sess.plan') }}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Collect Attendance Modal -->
<div class="modal fade" id="collectAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> {{ __('teacher.collect_attendance') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="attendanceModalBody">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist" id="attendanceTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="collect-tab" data-bs-toggle="tab" href="#collect-attendance" role="tab" aria-controls="collect-attendance" aria-selected="true">
                            <i class="bi bi-clipboard-plus"></i> {{ __('teacher.collect_attendance') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="update-tab" data-bs-toggle="tab" href="#update-attendance" role="tab" aria-controls="update-attendance" aria-selected="false">
                            <i class="bi bi-pencil-square"></i> {{ __('teacher.sess.update') }}
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="attendanceTabContent">
                    <!-- Collect Tab -->
                    <div class="tab-pane fade show active" id="collect-attendance" role="tabpanel" aria-labelledby="collect-tab">
                        <div id="collectTabContent">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">{{ __('teacher.loading') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Tab -->
                    <div class="tab-pane fade" id="update-attendance" role="tabpanel" aria-labelledby="update-tab">
                        <div id="updateTabContent">
                            <div id="updateAttendanceTableContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-journal-plus"></i> {{ __('teacher.assign_task') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="assignTaskForm">
                    <input type="hidden" id="task_session_timetableID" name="session_timetableID">
                    <input type="hidden" id="task_task_date" name="task_date">
                    
                    <!-- Date (Auto-filled) -->
                    <div class="form-group">
                        <label for="task_date_display">{{ __('teacher.date') }} <span class="text-muted">{{ __('teacher.sess.auto') }}</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_date_display" 
                            readonly
                            style="background-color: #f5f5f5; cursor: not-allowed;"
                        >
                    </div>
                    
                    <!-- Time (Auto-filled) -->
                    <div class="form-group">
                        <label for="task_time_display">{{ __('teacher.time') }} <span class="text-muted">{{ __('teacher.sess.auto') }}</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_time_display" 
                            readonly
                            style="background-color: #f5f5f5; cursor: not-allowed;"
                        >
                    </div>
                    
                    <!-- Topic -->
                    <div class="form-group">
                        <label for="task_topic">{{ __('teacher.sess.topic') }} <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_topic" 
                            name="topic" 
                            required
                            placeholder="{{ __('teacher.sess.topic_ph') }}"
                            style="border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Subtopic -->
                    <div class="form-group">
                        <label for="task_subtopic">{{ __('teacher.sess.subtopic') }}</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="task_subtopic" 
                            name="subtopic" 
                            placeholder="{{ __('teacher.sess.subtopic_ph') }}"
                            style="border-radius: 0 !important;"
                        >
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label for="task_description">{{ __('teacher.sess.description') }} <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control" 
                            id="task_description" 
                            name="task_description" 
                            rows="5" 
                            required
                            placeholder="{{ __('teacher.sess.description_ph') }}"
                            style="border-radius: 0 !important;"
                        ></textarea>
                        <small class="form-text text-muted">{{ __('teacher.sess.min_chars') }}</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-check-circle"></i> {{ __('teacher.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- My Sessions PDF download progress (same UX as fees structure) -->
<div class="modal fade" id="mySessionsPdfProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="bi bi-file-pdf text-danger" style="font-size: 3rem; animation: mySessionsPdfPulse 1.5s infinite;"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold; font-family: 'Century Gothic', sans-serif;">{{ __('teacher.generating_sessions_pdf') }}</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 0 !important;">
                    <div id="mySessionsPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                        role="progressbar" style="width: 0%; border-radius: 0 !important;"></div>
                </div>
                <div id="mySessionsPdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small">{{ __('teacher.wait_sessions_pdf') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- After #sx-teacher-dark-fix: labels, thead, session text --}}
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .form-label,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions label,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-day-meta .sx-day-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-day-meta .sx-day-title {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .table.sx-sessions-table thead th,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .table.sx-sessions-table thead th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-session-subject,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-session-subject,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .table.sx-sessions-table td,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .table.sx-sessions-table td {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-session-class,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-session-class,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .text-muted,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .text-muted,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-empty-day,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-sessions .sx-empty-day {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] #assignTaskModal label,
html[data-bs-theme="dark"] #assignTaskModal label,
html[data-sx-theme="dark"] #collectAttendanceModal label,
html[data-bs-theme="dark"] #collectAttendanceModal label {
    color: #ffb4b4 !important;
}
</style>
@endsection

@push('scripts')

<script>
var i18nSess = @json(__('teacher.sess'));
i18nSess.status = @json(__('teacher.status'));
i18nSess.actions = @json(__('teacher.actions'));
i18nSess.cancel = @json(__('teacher.cancel'));
i18nSess.loading = @json(__('teacher.loading'));
window.sxShowModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        var inst = bootstrap.Modal.getInstance(node) || bootstrap.Modal.getOrCreateInstance(node);
        inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};
window.sxShowTab = function(selector) {
    var node = document.querySelector(selector);
    if (!node) return;
    if (window.bootstrap && bootstrap.Tab) {
        bootstrap.Tab.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.tab) jQuery(node).tab('show');
};
</script>
<script>
// Define global functions that will use jQuery when available
let currentWeekOffset = {{ (int) ($weekOffset ?? 0) }};

window.changeWeek = function(offset) {
    currentWeekOffset = offset === 0 ? 0 : currentWeekOffset + offset;
    window.location.href = '{{ route("teacher.mySessions") }}?week=' + currentWeekOffset;
};

window.downloadMySessionsPdf = function() {
    function run() {
        if (typeof jQuery === 'undefined') {
            setTimeout(run, 50);
            return;
        }

        sxShowModal('#mySessionsPdfProgressModal');
        $('#mySessionsPdfProgressBar').css('width', '0%');
        $('#mySessionsPdfProgressPercent').text('0%');

        let progress = 0;
        const interval = setInterval(function() {
            if (progress < 95) {
                progress += 2 + Math.random() * 5;
                if (progress > 95) progress = 95;
                $('#mySessionsPdfProgressBar').css('width', progress + '%');
                $('#mySessionsPdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 300);

        const url = '{{ route("teacher.mySessions.pdf") }}?week=' + encodeURIComponent(currentWeekOffset);

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/pdf,application/json'
            }
        })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().catch(function() { return {}; }).then(function(err) {
                        throw new Error(err.error || err.message || ('Server error: ' + response.status));
                    });
                }
                return response.blob();
            })
            .then(function(blob) {
                clearInterval(interval);
                $('#mySessionsPdfProgressBar').css('width', '100%');
                $('#mySessionsPdfProgressPercent').text('100%');

                setTimeout(function() {
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = 'My_Sessions_' + new Date().toISOString().slice(0, 10) + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);

                    setTimeout(function() {
                        sxHideModal('#mySessionsPdfProgressModal');
                    }, 500);
                }, 400);
            })
            .catch(function(error) {
                clearInterval(interval);
                sxHideModal('#mySessionsPdfProgressModal');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: i18nSess.export_failed,
                        text: error.message || i18nSess.fail_pdf,
                        confirmButtonColor: '#940000'
                    });
                }
                console.error('My Sessions PDF Export Error:', error);
            });
    }
    run();
};

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('downloadMySessionsPdfBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            window.downloadMySessionsPdf();
        });
    }

    const daySelect = document.getElementById('sessionDaySelect');
    function showSelectedDay() {
        if (!daySelect) return;
        const day = daySelect.value;
        document.querySelectorAll('.sx-day-panel').forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-day') === day);
        });
    }
    if (daySelect) {
        daySelect.addEventListener('change', showSelectedDay);
        showSelectedDay();
    }
});

// Store current session data for modal
let currentSessionData = {};

window.collectAttendance = function(sessionTimetableID, date) {
    var waitTries = 0;
    function executeWhenJQueryReady() {
        var hasBs = window.bootstrap && bootstrap.Modal;
        var hasJqModal = typeof jQuery !== 'undefined' && jQuery.fn && typeof jQuery.fn.modal === 'function';
        if (typeof jQuery === 'undefined' || (!hasBs && !hasJqModal)) {
            waitTries++;
            if (waitTries > 80) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: i18nSess.could_not_open,
                        text: i18nSess.scripts_failed,
                        confirmButtonColor: '#940000'
                    });
                }
                return;
            }
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }
        
        // Store session data
        currentSessionData = {
            sessionTimetableID: sessionTimetableID,
            date: date
        };
        
    sxShowModal('#collectAttendanceModal');
        $('#collectTabContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">' + i18nSess.loading + '</span></div></div>');
    
        // Reset tabs
        sxShowTab('#collect-tab');
        
        // Load collect tab
        loadCollectTab(sessionTimetableID, date);
        
        // Load update tab
        loadUpdateTab(sessionTimetableID, date);
    }
    executeWhenJQueryReady();
};

function loadCollectTab(sessionTimetableID, date) {
    $.ajax({
        url: '/teacher/get-session-students',
        method: 'GET',
        data: {
            session_timetableID: sessionTimetableID,
            attendance_date: date
        },
        success: function(response) {
            if (response.success === false || response.error_code === 'no_academic_year') {
                $('#collectTabContent').html('<div class="alert alert-warning">' + (response.error || response.message || i18nSess.select_year) + '</div>');
                return;
            }
            if (response.success) {
                if (!response.students || response.students.length === 0) {
                    const emptyMsg = response.error_code === 'no_active_enrollments'
                        ? (response.message || i18nSess.no_active_enrollments)
                        : i18nSess.no_students;
                    $('#collectTabContent').html('<div class="alert alert-info text-center">' + emptyMsg + '</div>');
                    return;
                }

                let html = '<form id="attendanceForm">';
                html += '<input type="hidden" name="session_timetableID" value="' + sessionTimetableID + '">';
                html += '<input type="hidden" name="attendance_date" value="' + date + '">';
                html += '<input type="hidden" name="is_update" value="false">';
                html += '<div class="alert alert-light border small mb-2">' + i18nSess.showing_active + '</div>';
                html += '<table id="collectAttendanceTable" class="table table-bordered table-hover" style="width:100%">';
                html += '<thead><tr><th>' + i18nSess.student_name + '</th><th>' + i18nSess.status + '</th><th>' + i18nSess.remark + '</th></tr></thead>';
                html += '<tbody>';
                
                // Store students count for validation
                const totalStudents = response.students.length;
                
                response.students.forEach(function(student) {
                    const canMark = student.can_mark !== false;
                    html += '<tr class="' + (canMark ? '' : 'table-secondary text-muted') + '">';
                    html += '<td>' + student.name + (!canMark ? ' <span class="badge bg-secondary">' + i18nSess.inactive_enrollment + '</span>' : '') + '</td>';
                    html += '<td>';
                    html += '<select class="form-control form-control-sm attendance-status" name="attendance[' + student.studentID + '][status]" data-student-id="' + student.studentID + '" ' + (canMark ? 'required' : 'disabled') + '>';
                    html += '<option value="Present"' + (student.status === 'Present' ? ' selected' : '') + '>' + i18nSess.present + '</option>';
                    html += '<option value="Absent"' + (student.status === 'Absent' ? ' selected' : '') + '>' + i18nSess.absent + '</option>';
                    html += '<option value="Late"' + (student.status === 'Late' ? ' selected' : '') + '>' + i18nSess.late + '</option>';
                    html += '<option value="Excused"' + (student.status === 'Excused' ? ' selected' : '') + '>' + i18nSess.excused + '</option>';
                    html += '</select>';
                    if (canMark) {
                        html += '<input type="hidden" class="student-id-input" name="attendance[' + student.studentID + '][studentID]" value="' + student.studentID + '">';
                    }
                    html += '</td>';
                    html += '<td><input type="text" class="form-control form-control-sm" name="attendance[' + student.studentID + '][remark]" value="' + (student.remark || '') + '" placeholder="' + i18nSess.optional + '" ' + (canMark ? '' : 'disabled') + '></td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                html += '<button type="submit" class="btn btn-primary-custom btn-block mt-3"' + (response.can_collect === false ? ' disabled' : '') + '>' + i18nSess.save_attendance + '</button>';
                html += '</form>';
                
                $('#collectTabContent').html(html);
                
                // Initialize DataTable with pagination but we'll collect ALL rows on submit
                if ($.fn.DataTable.isDataTable('#collectAttendanceTable')) {
                    $('#collectAttendanceTable').DataTable().destroy();
                }
                const table = $('#collectAttendanceTable').DataTable({
                    paging: true,
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, i18nSess.all]],
                    searching: true,
                    ordering: true,
                    info: true
                });
                
                // Store table reference for later use
                window.collectAttendanceTable = table;
                
                // Handle form submission - collect ALL data including from DataTable
                $('#attendanceForm').on('submit', function(e) {
                    e.preventDefault();
                    window.saveAttendance();
                });
            } else {
                $('#collectTabContent').html('<div class="alert alert-danger">' + response.error + '</div>');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.error || xhr.responseJSON?.message || i18nSess.fail_load_students;
            $('#collectTabContent').html('<div class="alert alert-danger">' + msg + '</div>');
        }
    });
}

function loadUpdateTab(sessionTimetableID, date) {
    $('#updateAttendanceTableContainer').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">' + i18nSess.loading + '</span></div></div>');
    
    $.ajax({
        url: '/teacher/get-session-attendance-for-update',
        method: 'GET',
        data: {
            session_timetableID: sessionTimetableID,
            attendance_date: date
        },
        success: function(response) {
            if (response.success) {
                if (!response.data || response.data.length === 0) {
                    $('#updateAttendanceTableContainer').html('<div class="alert alert-info text-center">' + i18nSess.no_attendance + '</div>');
                    return;
                }
                
                let html = '<table id="updateAttendanceTable" class="table table-bordered table-hover" style="width:100%">';
                html += '<thead><tr><th>' + i18nSess.student_name + '</th><th>' + i18nSess.status + '</th><th>' + i18nSess.remark + '</th><th>' + i18nSess.actions + '</th></tr></thead>';
                html += '<tbody>';
                
                response.data.forEach(function(item) {
                    const statusClass = item.status === 'Present' ? 'success' : 
                                      item.status === 'Absent' ? 'danger' : 
                                      item.status === 'Late' ? 'warning' : 'info';
                    const statusLabel = ({
                        Present: i18nSess.present,
                        Absent: i18nSess.absent,
                        Late: i18nSess.late,
                        Excused: i18nSess.excused
                    })[item.status] || item.status;
                    const canEdit = item.can_edit !== false;
                    html += '<tr class="' + (canEdit ? '' : 'table-secondary text-muted') + '">';
                    html += '<td>' + item.student_name + (!canEdit ? ' <span class="badge bg-secondary">' + (item.enrollment_status || i18nSess.inactive_enrollment) + '</span>' : '') + '</td>';
                    html += '<td><span class="badge bg-' + statusClass + (statusClass === 'warning' || statusClass === 'info' ? ' text-dark' : '') + '">' + statusLabel + '</span></td>';
                    html += '<td>' + (item.remark || '-') + '</td>';
                    if (canEdit) {
                        html += '<td><button class="btn btn-sm btn-primary-custom" onclick="editAttendanceRecord(' + item.attendanceID + ', ' + item.studentID + ', \'' + String(item.student_name).replace(/'/g, "\\'") + '\', \'' + item.status + '\', \'' + String(item.remark || '').replace(/'/g, "\\'") + '\')"><i class="bi bi-pencil"></i> ' + i18nSess.edit + '</button></td>';
                    } else {
                        html += '<td><button class="btn btn-sm btn-secondary" disabled title="' + i18nSess.only_active_edit + '"><i class="bi bi-lock"></i> ' + i18nSess.locked + '</button></td>';
                    }
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#updateAttendanceTableContainer').html(html);
                
                // Initialize DataTable
                if ($.fn.DataTable.isDataTable('#updateAttendanceTable')) {
                    $('#updateAttendanceTable').DataTable().destroy();
                }
                $('#updateAttendanceTable').DataTable({
                    paging: true,
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, i18nSess.all]],
                    searching: true,
                    ordering: true,
                    info: true
                });
            } else {
                $('#updateAttendanceTableContainer').html('<div class="alert alert-danger">' + response.error + '</div>');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.error || xhr.responseJSON?.message || i18nSess.fail_load_attendance;
            $('#updateAttendanceTableContainer').html('<div class="alert alert-danger">' + msg + '</div>');
        }
    });
}

window.saveAttendance = function() {
    function executeWhenJQueryReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }
        
        // Collect ALL attendance data from DataTable (all rows, not just visible page)
        // DataTable stores all data in memory, so we can access all rows
        let attendanceArray = [];
        
        if (window.collectAttendanceTable) {
            // Get ALL rows from DataTable (including those on other pages)
            window.collectAttendanceTable.rows().every(function(rowIdx, tableLoop, rowLoop) {
                const node = this.node();
                const $row = $(node);
                
                const studentID = $row.find('.student-id-input').val();
                const status = $row.find('.attendance-status').val();
                const remark = $row.find('input[name*="[remark]"]').val() || '';
                
                if (studentID && status) {
                    attendanceArray.push({
                        studentID: parseInt(studentID),
                        status: status,
                        remark: remark
                    });
                }
            });
        } else {
            // Fallback: collect from form if DataTable not available
            $('#attendanceForm input.student-id-input').each(function() {
                const studentID = $(this).val();
                const $row = $(this).closest('tr');
                const status = $row.find('.attendance-status').val();
                const remark = $row.find('input[name*="[remark]"]').val() || '';
                
                if (studentID && status) {
                    attendanceArray.push({
                        studentID: parseInt(studentID),
                        status: status,
                        remark: remark
                    });
        }
    });
}

        // Validate we have data
        if (attendanceArray.length === 0) {
            Swal.fire({
                title: i18nSess.error,
                text: i18nSess.no_attendance_data,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }
        
        // Prepare form data in the format Laravel expects for nested arrays
        // Laravel expects: attendance[0][studentID], attendance[0][status], etc.
        const formData = new FormData();
        formData.append('session_timetableID', currentSessionData.sessionTimetableID);
        formData.append('attendance_date', currentSessionData.date);
        formData.append('is_update', false);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Add attendance array with proper indexing
        attendanceArray.forEach(function(item, index) {
            formData.append('attendance[' + index + '][studentID]', item.studentID);
            formData.append('attendance[' + index + '][status]', item.status);
            if (item.remark) {
                formData.append('attendance[' + index + '][remark]', item.remark);
            }
        });
    
    $.ajax({
        url: '{{ route("teacher.collect_session_attendance") }}',
        method: 'POST',
        data: formData,
            processData: false,
            contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: i18nSess.success,
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    // Reload both tabs
                    if (currentSessionData.sessionTimetableID && currentSessionData.date) {
                        loadCollectTab(currentSessionData.sessionTimetableID, currentSessionData.date);
                        loadUpdateTab(currentSessionData.sessionTimetableID, currentSessionData.date);
                    } else {
                    sxHideModal('#collectAttendanceModal');
                    location.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: i18nSess.error,
                    text: response.error || i18nSess.fail_save_attendance,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || i18nSess.fail_save_attendance;
            Swal.fire({
                title: i18nSess.error,
                text: error,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}
    executeWhenJQueryReady();
};

window.editAttendanceRecord = function(attendanceID, studentID, studentName, currentStatus, currentRemark) {
    // Escape HTML in strings
    const escapedName = $('<div>').text(studentName).html();
    const escapedRemark = $('<div>').text(currentRemark || '').html();
    
    // Create edit form HTML
    let html = '<div class="alert alert-info mb-3">' + i18nSess.editing_for + ' <strong>' + escapedName + '</strong></div>';
    html += '<form id="editAttendanceForm">';
    html += '<div class="form-group">';
    html += '<label>' + i18nSess.status + ':</label>';
    html += '<select class="form-control" id="editStatusSelect" required>';
    html += '<option value="Present"' + (currentStatus === 'Present' ? ' selected' : '') + '>' + i18nSess.present + '</option>';
    html += '<option value="Absent"' + (currentStatus === 'Absent' ? ' selected' : '') + '>' + i18nSess.absent + '</option>';
    html += '<option value="Late"' + (currentStatus === 'Late' ? ' selected' : '') + '>' + i18nSess.late + '</option>';
    html += '<option value="Excused"' + (currentStatus === 'Excused' ? ' selected' : '') + '>' + i18nSess.excused + '</option>';
    html += '</select>';
    html += '</div>';
    
    html += '<div class="form-group">';
    html += '<label>' + i18nSess.remark + ':</label>';
    html += '<input type="text" class="form-control" id="editRemarkInput" value="' + escapedRemark + '" placeholder="' + i18nSess.optional + '">';
    html += '</div>';
    html += '</form>';
    
    Swal.fire({
        title: i18nSess.edit_attendance,
        html: html,
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: i18nSess.update_btn,
        cancelButtonText: i18nSess.cancel,
        width: '500px',
        preConfirm: () => {
            // Prepare form data in the format Laravel expects
            const formData = new FormData();
            formData.append('session_timetableID', currentSessionData.sessionTimetableID);
            formData.append('attendance_date', currentSessionData.date);
            formData.append('is_update', true);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            // Add attendance array with proper indexing (Laravel expects attendance[0][studentID], etc.)
            const status = $('#editStatusSelect').val();
            const remark = $('#editRemarkInput').val() || '';
            
            formData.append('attendance[0][studentID]', studentID);
            formData.append('attendance[0][status]', status);
            if (remark) {
                formData.append('attendance[0][remark]', remark);
            }
            
            return $.ajax({
                url: '{{ route("teacher.collect_session_attendance") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                if (!response.success) {
                    throw new Error(response.error || i18nSess.fail_update_attendance);
                }
                return response;
            }).catch(error => {
                const errorMsg = error.responseJSON?.error || error.responseJSON?.message || error.message || i18nSess.fail_update_attendance;
                Swal.showValidationMessage(errorMsg);
                return Promise.reject(error);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
                title: i18nSess.success,
                text: i18nSess.attendance_updated,
                icon: 'success',
                confirmButtonColor: '#940000'
            }).then(() => {
                // Reload update tab
                if (currentSessionData.sessionTimetableID && currentSessionData.date) {
                    loadUpdateTab(currentSessionData.sessionTimetableID, currentSessionData.date);
                }
            });
        }
    });
};

window.assignTask = function(sessionTimetableID, date, startTime, endTime) {
    console.log('assignTask called with:', {sessionTimetableID, date, startTime, endTime});
    
    // Wait for jQuery to be available
    function executeWhenJQueryReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.log('Waiting for jQuery...');
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }
        
        // Now jQuery is available, execute the function
        executeAssignTask(sessionTimetableID, date, startTime, endTime);
    }
    
    function executeAssignTask(sessionTimetableID, date, startTime, endTime) {
    
    try {
        // Set hidden fields
    $('#task_session_timetableID').val(sessionTimetableID);
    $('#task_task_date').val(date);
        $('#task_topic').val('');
        $('#task_subtopic').val('');
    $('#task_description').val('');
        
        // Format and display date
        const dateObj = new Date(date + 'T00:00:00'); // Add time to ensure correct parsing
        if (isNaN(dateObj.getTime())) {
            console.error('Invalid date:', date);
            $('#task_date_display').val(date); // Fallback to original date
        } else {
            const formattedDate = dateObj.toLocaleDateString('en-GB', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            $('#task_date_display').val(formattedDate);
        }
        
        // Format and display time
        const formatTime = (timeStr) => {
            if (!timeStr || timeStr === 'undefined' || timeStr === 'null') return 'N/A';
            try {
                // Handle both "HH:mm:ss" and "HH:mm" formats
                const parts = timeStr.toString().split(':');
                const hours = parseInt(parts[0]) || 0;
                const minutes = parts[1] || '00';
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours % 12 || 12;
                return displayHours + ':' + minutes + ' ' + ampm;
            } catch (e) {
                console.error('Error formatting time:', timeStr, e);
                return timeStr;
            }
        };
        
        const formattedStartTime = formatTime(startTime);
        const formattedEndTime = formatTime(endTime);
        $('#task_time_display').val(formattedStartTime + ' - ' + formattedEndTime);
        
        // Check if modal element exists
        const $modal = $('#assignTaskModal');
        if ($modal.length === 0) {
            console.error('Modal element not found');
            alert('Modal not found. Please refresh the page.');
            return;
        }
        
        sxShowModal($modal);
    } catch (error) {
        console.error('Error in assignTask:', error);
        alert('Error opening task form: ' + error.message);
    }
    }
    
    // Start checking for jQuery
    executeWhenJQueryReady();
};

window.openLessonPlan = function(sessionTimetableID, day, startTime, endTime, subjectName, className, date) {
    // Native fetch, do not depend on jQuery being ready (onclick can fire before $ loads)
    function showNotAllowed(msg) {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: i18nSess.not_allowed,
                text: msg,
                icon: 'warning',
                confirmButtonColor: '#940000'
            });
        } else {
            alert(msg);
        }
    }

    function goToLessonPlan() {
        const params = new URLSearchParams({
            session_timetableID: sessionTimetableID,
            day: day,
            start_time: startTime,
            end_time: endTime,
            subject_name: subjectName,
            class_name: className,
            date: date,
            auto_open: 'true'
        });
        window.location.href = '{{ route("teacher.lessonPlans") }}?' + params.toString();
    }

    const statsUrl = new URL('{{ route("teacher.get_session_attendance_stats") }}', window.location.origin);
    statsUrl.searchParams.set('session_timetableID', sessionTimetableID);
    statsUrl.searchParams.set('date', date);

    fetch(statsUrl.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(res) {
        return res.json().then(function(data) {
            return { ok: res.ok, status: res.status, data: data };
        }).catch(function() {
            return { ok: res.ok, status: res.status, data: {} };
        });
    })
    .then(function(result) {
        const res = result.data || {};
        if (!result.ok || res.success === false || res.error_code === 'no_academic_year' || res.error_code === 'no_active_enrollments') {
            showNotAllowed(
                res.error || res.message || i18nSess.select_year_plan
            );
            return;
        }

        const registeredTotal = (res.data && res.data.registered_total != null)
            ? res.data.registered_total
            : (res.registered_total != null ? res.registered_total : null);
        if (registeredTotal === 0) {
            showNotAllowed(i18nSess.no_enroll_plan);
            return;
        }

        goToLessonPlan();
    })
    .catch(function(err) {
        console.error('openLessonPlan failed:', err);
        showNotAllowed(i18nSess.cannot_open_plan);
    });
};

// Initialize jQuery-dependent code when ready
(function() {
    function initWhenReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(initWhenReady, 50);
            return;
        }
        
        jQuery(document).ready(function($) {
            // Form submit handler
$('#assignTaskForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("teacher.assign_session_task") }}',
        method: 'POST',
        data: $(this).serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: i18nSess.success,
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    sxHideModal('#assignTaskModal');
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: i18nSess.error,
                    text: response.error || i18nSess.fail_assign,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || i18nSess.fail_assign;
            Swal.fire({
                title: i18nSess.error,
                text: error,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
});
        });
    }
    initWhenReady();
})();
</script>


@endpush
