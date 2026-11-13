@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.dashboard_title').' - ShuleXpert')
@section('page_title', __('teacher.pages.dashboard'))
@section('page_breadcrumb', __('teacher.pages.dashboard'))

@section('content')
@php
    $teacherDisplayName = trim((($tangeTeacher->first_name ?? '') . ' ' . ($tangeTeacher->last_name ?? '')));
    if ($teacherDisplayName === '') {
        $teacherDisplayName = __('teacher.role_teacher');
    }
    $teacherInitial = strtoupper(substr($teacherDisplayName, 0, 1));

    $sessionNotification = null;
    if (isset($teacherNotifications) && $teacherNotifications->count() > 0) {
        $sessionNotifications = $teacherNotifications->filter(function ($n) {
            return isset($n['type']) && $n['type'] === 'session_time';
        });
        if ($sessionNotifications->count() > 0) {
            $sessionNotification = $sessionNotifications->first();
        }
    }

    $todayAttendanceStats = $teacherTodayAttendance['statistics'] ?? [];
    $todayAttendanceStatus = $todayAttendanceStats['day_status'] ?? 'Not Available';
    $attStatusMap = [
        'Present' => __('teacher.dash.on_time'),
        'Early' => __('teacher.dash.early'),
        'Late' => __('teacher.dash.late'),
        'Absent' => __('teacher.dash.absent'),
        'Permitted' => __('teacher.dash.permitted'),
        'Not Available' => __('teacher.dash.not_available'),
    ];
    $todayAttendanceLabel = $attStatusMap[$todayAttendanceStatus] ?? $todayAttendanceStatus;
    $todayCheckIn = $todayAttendanceStats['check_in_time'] ?? null;
    $todayCheckOut = $todayAttendanceStats['check_out_time'] ?? null;
    if (in_array($todayAttendanceStatus, ['Present', 'Early'], true)) {
        $attendanceBadge = 'bg-success';
    } elseif (in_array($todayAttendanceStatus, ['Late', 'Absent'], true)) {
        $attendanceBadge = 'bg-danger';
    } elseif ($todayAttendanceStatus === 'Permitted') {
        $attendanceBadge = 'bg-primary';
    } else {
        $attendanceBadge = 'bg-secondary';
    }

    $dashPct = (int) (optional($tangeTeacher)->tange_completion_percent ?? 0);
    $dashRetire = optional($tangeTeacher)->retirement_alert ?? null;
    $studentPortalEnabled = \App\Models\School::where('schoolID', Session::get('schoolID'))->value('student_portal_enabled');

    $statCards = [];
    if (isset($dashboardStats)) {
        $statCards[] = [
            'label' => __('teacher.nav.subjects'),
            'value' => $dashboardStats['subjects_count'] ?? 0,
            'href' => route('teacherSubjects'),
            'note' => __('teacher.dash.subjects_teaching'),
        ];
        $statCards[] = [
            'label' => __('teacher.nav.classes'),
            'value' => $dashboardStats['classes_count'] ?? 0,
            'href' => (isset($hasAssignedClass) && $hasAssignedClass) ? route('AdmitedClasses') : null,
            'note' => (isset($hasAssignedClass) && $hasAssignedClass) ? __('teacher.dash.subclasses_sessions') : __('teacher.dash.no_class_assigned'),
        ];
        $statCards[] = [
            'label' => __('teacher.dash.sessions_week'),
            'value' => $dashboardStats['sessions_per_week'] ?? 0,
            'href' => route('teacher.mySessions'),
            'note' => __('teacher.dash.mon_fri'),
        ];
        $statCards[] = [
            'label' => __('teacher.nav.lesson_plans'),
            'value' => $dashboardStats['lesson_plans_count'] ?? 0,
            'href' => route('teacher.lessonPlans'),
            'note' => __('teacher.dash.sent_to_admin', ['count' => ($dashboardStats['lesson_plans_sent_count'] ?? 0)]),
        ];

        $moreCards = [];
        if (isset($coordinator) && $coordinator->count() > 0) {
            $moreCards[] = ['label' => __('teacher.dash.class_teacher'), 'value' => $dashboardStats['class_teacher_subclasses_count'] ?? 0, 'href' => route('AdmitedClasses'), 'note' => __('teacher.dash.subclasses_managing')];
        }
        if (($dashboardStats['coordinator_classes_count'] ?? 0) > 0) {
            $moreCards[] = ['label' => __('teacher.dash.coordinator'), 'value' => $dashboardStats['coordinator_classes_count'] ?? 0, 'href' => route('AdmitedClasses', ['coordinator' => 'true']), 'note' => __('teacher.dash.main_classes')];
        }
        $moreCards[] = ['label' => __('teacher.dash.sessions_year'), 'value' => $dashboardStats['sessions_per_year'] ?? 0, 'href' => null, 'note' => __('teacher.dash.excluding_holidays')];
        $moreCards[] = ['label' => __('teacher.dash.sessions_entered'), 'value' => $dashboardStats['approved_sessions_count'] ?? 0, 'href' => route('teacher.mySessions'), 'note' => __('teacher.dash.with_approved_tasks')];
        $moreCards[] = ['label' => __('teacher.nav.scheme_of_work'), 'value' => $dashboardStats['scheme_of_work_count'] ?? 0, 'href' => route('teacher.schemeOfWork'), 'note' => __('teacher.dash.current_year')];
    }

    $hasApprovals = (isset($pendingApprovals) && count($pendingApprovals) > 0)
        || (isset($specialRoleApprovals) && count($specialRoleApprovals) > 0)
        || (isset($waitingApprovals) && count($waitingApprovals) > 0);
@endphp

<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash span:not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .stat-value,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .fw-semibold,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .fw-bold,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .text-dark,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .quick-tool-link,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .list-row,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-dash .btn-link {
        color: #000 !important;
    }
    .sx-teacher-dash .btn-primary,
    .sx-teacher-dash a.btn-primary,
    .sx-teacher-dash .text-white,
    .sx-teacher-dash .badge,
    .sx-teacher-dash .avatar,
    .sx-teacher-dash .avatar span,
    .sx-teacher-dash .avatar i {
        color: #fff !important;
    }
    .sx-teacher-dash .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .sx-teacher-dash a.stat-card,
    .sx-teacher-dash .stat-card {
        display: block;
        text-decoration: none;
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        transition: box-shadow 0.15s ease, transform 0.15s ease;
        color: inherit;
    }
    .sx-teacher-dash a.stat-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
        color: inherit;
    }
    .sx-teacher-dash a.stat-card .card-body,
    .sx-teacher-dash .stat-card .card-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .sx-teacher-dash .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .sx-teacher-dash .stat-value {
        display: block;
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .sx-teacher-dash .stat-note {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.75rem;
        color: #666 !important;
    }
    .sx-teacher-dash .avatar {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        overflow: hidden;
    }
    .sx-teacher-dash .bg-brand { background: #940000 !important; }
    .sx-teacher-dash .panel-card .card-body { padding: 1.25rem 1.35rem; }
    .sx-teacher-dash .btn-link {
        font-weight: 600;
        text-decoration: none;
        color: #940000 !important;
    }
    .sx-teacher-dash .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-teacher-dash .quick-tool-link { text-decoration: none; color: inherit; }
    .sx-teacher-dash .quick-tool-link .list-row:hover {
        background: rgba(148, 0, 0, 0.05);
        border-radius: 0.75rem;
    }
    .sx-teacher-dash .list-row {
        padding: 0.7rem 0.55rem;
        transition: background 0.15s ease;
    }
    .sx-teacher-dash .notif-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-teacher-dash .notif-strip.is-duty { border-left-color: #c79100; }
    .sx-teacher-dash .notif-strip.is-session { border-left-color: #0d6efd; }
    .sx-teacher-dash .env-bar {
        height: 12px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(148, 0, 0, 0.1);
    }
    .sx-teacher-dash .env-bar > span {
        display: block;
        height: 100%;
        background: #940000;
    }
    .sx-teacher-dash .env-bar.is-mid > span { background: #c79100; }
    .sx-teacher-dash .env-bar.is-ok > span { background: #198754; }
    .sx-teacher-dash .att-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .sx-teacher-dash .att-item {
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.08);
        border-radius: 0.35rem;
        padding: 0.7rem 0.8rem;
    }
    .sx-teacher-dash .att-item small {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.25rem;
    }
    .sx-teacher-dash .att-item strong {
        font-size: 1rem;
        font-weight: 800;
    }
    .sx-teacher-dash .approval-card {
        border: 1px solid #e6e6e6;
        border-left: 4px solid #c79100;
        background: #fff;
        height: 100%;
    }
    .sx-teacher-dash .approval-card.is-rejected { border-left-color: #dc3545; }
    .sx-teacher-dash .approval-card.is-waiting { border-left-color: #6c757d; }
    .sx-teacher-dash .approval-card.is-coord { border-left-color: #0d6efd; }
    .sx-teacher-dash .approval-meta {
        font-size: 0.84rem;
        color: #444;
        margin-bottom: 0.25rem;
    }

    html[data-sx-theme="dark"] .sx-teacher-dash .card,
    html[data-bs-theme="dark"] .sx-teacher-dash .card,
    html[data-sx-theme="dark"] .sx-teacher-dash .panel-card,
    html[data-bs-theme="dark"] .sx-teacher-dash .panel-card,
    html[data-sx-theme="dark"] .sx-teacher-dash .notif-strip,
    html[data-bs-theme="dark"] .sx-teacher-dash .notif-strip,
    html[data-sx-theme="dark"] .sx-teacher-dash .approval-card,
    html[data-bs-theme="dark"] .sx-teacher-dash .approval-card,
    html[data-sx-theme="dark"] .sx-teacher-dash .att-item,
    html[data-bs-theme="dark"] .sx-teacher-dash .att-item {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-dash a.stat-card,
    html[data-bs-theme="dark"] .sx-teacher-dash a.stat-card,
    html[data-sx-theme="dark"] .sx-teacher-dash .stat-card,
    html[data-bs-theme="dark"] .sx-teacher-dash .stat-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-dash,
    html[data-bs-theme="dark"] .sx-teacher-dash,
    html[data-sx-theme="dark"] .sx-teacher-dash p,
    html[data-bs-theme="dark"] .sx-teacher-dash p,
    html[data-sx-theme="dark"] .sx-teacher-dash span:not(.badge):not(.avatar),
    html[data-bs-theme="dark"] .sx-teacher-dash span:not(.badge):not(.avatar),
    html[data-sx-theme="dark"] .sx-teacher-dash small,
    html[data-bs-theme="dark"] .sx-teacher-dash small,
    html[data-sx-theme="dark"] .sx-teacher-dash strong,
    html[data-bs-theme="dark"] .sx-teacher-dash strong,
    html[data-sx-theme="dark"] .sx-teacher-dash .stat-label,
    html[data-bs-theme="dark"] .sx-teacher-dash .stat-label,
    html[data-sx-theme="dark"] .sx-teacher-dash .stat-value,
    html[data-bs-theme="dark"] .sx-teacher-dash .stat-value,
    html[data-sx-theme="dark"] .sx-teacher-dash .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-dash .text-muted,
    html[data-sx-theme="dark"] .sx-teacher-dash .approval-meta,
    html[data-bs-theme="dark"] .sx-teacher-dash .approval-meta {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-dash .btn-outline-light,
    html[data-bs-theme="dark"] .sx-teacher-dash .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-dash .env-bar,
    html[data-bs-theme="dark"] .sx-teacher-dash .env-bar {
        background: rgba(255, 180, 180, 0.12);
    }
    @media (max-width: 575.98px) {
        .sx-teacher-dash .att-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="sx-teacher-dash">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($sessionNotification)
        <div class="alert notif-strip is-session alert-dismissible fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="avatar rounded-circle bg-brand flex-shrink-0">
                <i class="fi fi-rr-clock"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold mb-1">{{ $sessionNotification['title'] ?? __('teacher.dash.session_time_alert') }}</div>
                <div class="small mb-2">{{ $sessionNotification['message'] ?? '' }}</div>
                <a href="{{ $sessionNotification['link'] ?? route('teacher.mySessions') }}" class="btn btn-sm btn-primary">
                    <i class="fi fi-rr-arrow-right me-1"></i> {{ __('teacher.dash.go_to_sessions') }}
                </a>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($isOnDuty) && $isOnDuty)
        <div class="alert notif-strip is-duty alert-dismissible fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="avatar rounded-circle bg-brand flex-shrink-0">
                <i class="fi fi-rr-megaphone"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold mb-1">{{ __('teacher.dash.duty_notification') }}</div>
                <div class="small mb-2">{{ __('teacher.dash.duty_message') }}</div>
                <a href="{{ route('teacher.duty_book') }}" class="btn btn-sm btn-outline-light btn-shadow">
                    <i class="fi fi-rr-notebook me-1"></i> {{ __('teacher.dash.open_duty_book') }}
                </a>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($dashboardStats))
    <div class="row g-3">
        {{-- Top metrics --}}
        @foreach($statCards as $card)
            <div class="col-sm-6 col-xl-3">
                @if(!empty($card['href']))
                    <a class="card stat-card h-100" href="{{ $card['href'] }}">
                        <div class="card-body">
                            <span class="stat-label">{{ $card['label'] }}</span>
                            <span class="stat-value">{{ number_format((int) $card['value']) }}</span>
                            @if(!empty($card['note']))
                                <span class="stat-note">{{ $card['note'] }}</span>
                            @endif
                        </div>
                    </a>
                @else
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <span class="stat-label">{{ $card['label'] }}</span>
                            <span class="stat-value">{{ number_format((int) $card['value']) }}</span>
                            @if(!empty($card['note']))
                                <span class="stat-note">{{ $card['note'] }}</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Welcome --}}
        <div class="col-xl-4">
            <div class="card panel-card overflow-hidden z-1 h-100 position-relative">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar rounded-circle bg-brand fw-bold flex-shrink-0" style="width:56px;height:56px;font-size:1.25rem;">
                            <span>{{ $teacherInitial }}</span>
                        </div>
                        <div class="min-w-0 pe-2">
                            <div class="small text-muted mb-1">{{ __('teacher.dash.today', ['date' => \Carbon\Carbon::now()->translatedFormat('l, d M Y')]) }}</div>
                            <h5 class="card-title mb-0">{{ __('teacher.dash.hello', ['name' => $teacherDisplayName]) }}</h5>
                        </div>
                    </div>
                    @if(!empty($upcomingSession))
                        <div class="sx-upcoming-session mb-2">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <span class="small fw-bold text-uppercase" style="letter-spacing:.04em;color:#940000 !important;">
                                    {{ !empty($upcomingSession['is_now']) ? __('teacher.dash.now_in_session') : __('teacher.dash.upcoming_session') }}
                                </span>
                                @if(!empty($upcomingSession['is_now']))
                                    <span class="badge bg-success rounded-pill">{{ __('teacher.dash.live') }}</span>
                                @endif
                            </div>
                            <div class="fw-bold mb-1" style="font-size:1.05rem;line-height:1.25;">
                                {{ $upcomingSession['subject'] ?? '—' }}
                            </div>
                            @if(!empty($upcomingSession['class']))
                                <div class="small text-muted mb-1">
                                    <i class="fi fi-rr-users me-1"></i>{{ $upcomingSession['class'] }}
                                </div>
                            @endif
                            <div class="fw-semibold" style="color:#940000 !important;">
                                <i class="fi fi-rr-clock me-1"></i>
                                {{ $upcomingSession['start'] ?? '—' }}
                                –
                                {{ $upcomingSession['end'] ?? '—' }}
                            </div>
                        </div>
                    @else
                        <div class="sx-upcoming-session mb-2">
                            <div class="small fw-bold text-uppercase mb-1" style="letter-spacing:.04em;color:#940000 !important;">
                                {{ __('teacher.dash.upcoming_session') }}
                            </div>
                            <div class="small text-muted mb-0">{{ __('teacher.dash.no_upcoming') }}</div>
                        </div>
                    @endif
                    @if(isset($isNavHOD) && $isNavHOD)
                        <span class="badge bg-primary rounded-pill mb-2">
                            <i class="fi fi-rr-star me-1"></i> HOD · {{ $hodDeptName ?? '' }}
                        </span>
                    @endif
                    <img src="{{ asset('gxon/images/media/svg/media1.svg') }}" alt="" class="position-absolute bottom-0 end-0 z-n1" style="max-width: 42%; opacity: .88;">
                </div>
                <div class="card-footer border-0 pt-0 d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher.mySessions') }}" class="btn btn-primary waves-effect waves-light btn-sm">
                        <i class="fi fi-rr-clock me-1"></i> {{ __('teacher.nav.sessions') }}
                    </a>
                    <a href="{{ route('teacher.myTasks') }}" class="btn btn-outline-light btn-shadow waves-effect btn-sm">
                        <i class="fi fi-rr-list-check me-1"></i> {{ __('teacher.nav.tasks') }}
                    </a>
                    @if($studentPortalEnabled)
                        <a href="{{ route('student_portal.materials') }}" class="btn btn-outline-light btn-shadow waves-effect btn-sm">
                            <i class="fi fi-rr-graduation-cap me-1"></i> {{ __('teacher.dash.portal') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Attendance today --}}
        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('teacher.dash.attendance_today') }}</h6>
                    <span class="badge {{ $attendanceBadge }} rounded-pill">{{ $todayAttendanceLabel }}</span>
                </div>
                <div class="card-body">
                    <div class="att-grid mb-3">
                        <div class="att-item">
                            <small>{{ __('teacher.dash.check_in') }}</small>
                            <strong>{{ $todayCheckIn ? \Carbon\Carbon::parse($todayCheckIn)->format('h:i A') : '—' }}</strong>
                        </div>
                        <div class="att-item">
                            <small>{{ __('teacher.dash.check_out') }}</small>
                            <strong>{{ $todayCheckOut ? \Carbon\Carbon::parse($todayCheckOut)->format('h:i A') : '—' }}</strong>
                        </div>
                        <div class="att-item">
                            <small>{{ __('teacher.dash.official') }}</small>
                            <strong>{{ !empty($teacherTodayAttendance['work_start_time']) ? \Carbon\Carbon::parse($teacherTodayAttendance['work_start_time'])->format('h:i A') : '—' }}</strong>
                        </div>
                    </div>
                    <a href="{{ route('teacher.my_attendance') }}" class="btn btn-primary waves-effect waves-light w-100 btn-sm">
                        <i class="fi fi-rr-eye me-1"></i> {{ __('teacher.dash.see_report') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- TANGE --}}
        @if(!empty($tangeTeacher))
        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('teacher.dash.tange_profile') }}</h6>
                    <span class="badge {{ $dashPct >= 100 ? 'bg-success' : 'bg-warning' }} rounded-pill">{{ $dashPct }}%</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <div class="text-muted small">{{ __('teacher.dash.completeness') }}</div>
                            <h3 class="mb-0 fw-bold">{{ $dashPct }}%</h3>
                        </div>
                        <div class="text-end small">
                            @if($dashPct < 100)
                                {{ __('teacher.dash.incomplete_hr') }}
                            @else
                                {{ __('teacher.dash.profile_complete') }}
                            @endif
                        </div>
                    </div>
                    <div class="env-bar mb-3 {{ $dashPct >= 100 ? 'is-ok' : ($dashPct >= 50 ? 'is-mid' : '') }}">
                        <span style="width: {{ max(0, min(100, $dashPct)) }}%;"></span>
                    </div>
                    @if($dashRetire)
                        <div class="small mb-3 {{ ($dashRetire['level'] ?? '') === 'danger' ? 'text-danger' : 'text-muted' }}">
                            <i class="fi fi-rr-triangle-warning me-1"></i>{{ $dashRetire['message'] ?? '' }}
                        </div>
                    @endif
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('teacher.profile') }}" class="btn btn-primary waves-effect waves-light btn-sm flex-grow-1">
                            <i class="fi fi-rr-{{ $dashPct < 100 ? 'edit' : 'user' }} me-1"></i>
                            {{ $dashPct < 100 ? __('teacher.dash.complete_details') : __('teacher.view_profile') }}
                        </a>
                        <a href="{{ route('teacher.profile.download_pdf') }}" class="btn btn-outline-light btn-shadow waves-effect btn-sm js-download-tange" data-filename="TANGE_My_Particulars">
                            <i class="fi fi-rr-file me-1"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- More metrics --}}
        @foreach(($moreCards ?? []) as $card)
            <div class="col-sm-6 col-xl-3">
                @if(!empty($card['href']))
                    <a class="card stat-card h-100" href="{{ $card['href'] }}">
                        <div class="card-body">
                            <span class="stat-label">{{ $card['label'] }}</span>
                            <span class="stat-value">{{ number_format((int) $card['value']) }}</span>
                            @if(!empty($card['note']))
                                <span class="stat-note">{{ $card['note'] }}</span>
                            @endif
                        </div>
                    </a>
                @else
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <span class="stat-label">{{ $card['label'] }}</span>
                            <span class="stat-value">{{ number_format((int) $card['value']) }}</span>
                            @if(!empty($card['note']))
                                <span class="stat-note">{{ $card['note'] }}</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Quick tools --}}
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('teacher.dash.quick_tools') }}</h6>
                    <a href="{{ route('teacher.mySessions') }}" class="btn-link">{{ __('teacher.dash.open_sessions') }}</a>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @php
                            $quickTools = [
                                ['href' => route('teacher.mySessions'), 'icon' => 'fi-rr-clock', 'title' => __('teacher.pages.my_sessions'), 'sub' => __('teacher.dash.qt_sessions_sub')],
                                ['href' => route('teacher.myTasks'), 'icon' => 'fi-rr-list-check', 'title' => __('teacher.pages.my_tasks'), 'sub' => __('teacher.dash.qt_tasks_sub')],
                                ['href' => route('teacherSubjects'), 'icon' => 'fi-rr-book', 'title' => __('teacher.pages.my_subjects'), 'sub' => __('teacher.dash.qt_subjects_sub')],
                                ['href' => route('teacher.schemeOfWork'), 'icon' => 'fi-rr-document', 'title' => __('teacher.nav.scheme_of_work'), 'sub' => __('teacher.dash.qt_scheme_sub')],
                                ['href' => route('teacher.lessonPlans'), 'icon' => 'fi-rr-file', 'title' => __('teacher.nav.lesson_plans'), 'sub' => __('teacher.dash.qt_plans_sub')],
                                ['href' => route('teacher.duty_book'), 'icon' => 'fi-rr-notebook', 'title' => __('teacher.nav.duty_book'), 'sub' => __('teacher.dash.qt_duty_sub')],
                            ];
                            if ($studentPortalEnabled) {
                                $quickTools[] = ['href' => route('student_portal.materials'), 'icon' => 'fi-rr-graduation-cap', 'title' => __('teacher.nav.learning_portal'), 'sub' => __('teacher.dash.qt_portal_sub')];
                            }
                            $quickTools[] = ['href' => route('teacher.my_attendance'), 'icon' => 'fi-rr-calendar-check', 'title' => __('teacher.pages.my_attendance'), 'sub' => __('teacher.dash.qt_attendance_sub')];
                        @endphp
                        @foreach($quickTools as $tool)
                            <div class="col-md-6 col-xl-4">
                                <a href="{{ $tool['href'] }}" class="quick-tool-link d-block">
                                    <div class="list-row d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm rounded-circle bg-brand" style="width:42px;height:42px;">
                                                <i class="fi {{ $tool['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $tool['title'] }}</div>
                                                <small class="text-muted">{{ $tool['sub'] }}</small>
                                            </div>
                                        </div>
                                        <i class="fi fi-rr-angle-small-right text-muted"></i>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Approvals --}}
        @if($hasApprovals)
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-header border-0 pb-0">
                        <h6 class="card-title mb-0">{{ __('teacher.dash.exam_approvals') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if(isset($pendingApprovals) && count($pendingApprovals) > 0)
                                @foreach($pendingApprovals as $approval)
                                    @php $isRejected = $approval->status === 'rejected'; @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card approval-card {{ $isRejected ? 'is-rejected' : '' }}">
                                            <div class="card-body">
                                                <div class="fw-bold mb-2" style="color: {{ $isRejected ? '#dc3545' : '#c79100' }};">
                                                    <i class="fi fi-rr-{{ $isRejected ? 'cross-circle' : 'triangle-warning' }} me-1"></i>
                                                    {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
                                                </div>
                                                <div class="approval-meta"><strong>{{ __('teacher.dash.role') }}:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}</div>
                                                <div class="approval-meta"><strong>{{ __('teacher.dash.step') }}:</strong> {{ $approval->approval_order }}</div>
                                                <div class="approval-meta mb-3">
                                                    <strong>{{ __('teacher.status') }}:</strong>
                                                    {{ $isRejected ? __('teacher.rejected') : __('teacher.dash.pending_your_approval') }}
                                                </div>
                                                @if($isRejected && $approval->rejection_reason)
                                                    <div class="approval-meta mb-3"><strong>{{ __('teacher.dash.reason') }}:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}</div>
                                                @endif
                                                @if($approval->examination && $approval->examination->examID)
                                                    <a href="{{ route('approve_result', $approval->examination->examID) }}" class="btn btn-sm {{ $isRejected ? 'btn-danger' : 'btn-primary' }}">
                                                        <i class="fi fi-rr-eye me-1"></i> {{ $isRejected ? __('teacher.dash.view_review') : __('teacher.dash.review_approve') }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fi fi-rr-eye me-1"></i> {{ $isRejected ? __('teacher.dash.view_review') : __('teacher.dash.review_approve') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(isset($specialRoleApprovals) && count($specialRoleApprovals) > 0)
                                @foreach($specialRoleApprovals as $specialApproval)
                                    @php
                                        $approval = $specialApproval['approval'];
                                        $type = $specialApproval['type'];
                                        $exam = $specialApproval['exam'];
                                        $isRejected = $approval->status === 'rejected';
                                        $cardClass = $isRejected ? 'is-rejected' : ($type === 'class_teacher' ? '' : 'is-coord');
                                        $tone = $isRejected ? '#dc3545' : ($type === 'class_teacher' ? '#c79100' : '#0d6efd');
                                    @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card approval-card {{ $cardClass }}">
                                            <div class="card-body">
                                                <div class="fw-bold mb-2" style="color: {{ $tone }};">
                                                    <i class="fi fi-rr-{{ $isRejected ? 'cross-circle' : ($type === 'class_teacher' ? 'users' : 'sitemap') }} me-1"></i>
                                                    {{ $exam->exam_name ?? 'N/A' }}
                                                </div>
                                                <div class="approval-meta"><strong>{{ __('teacher.dash.role') }}:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}</div>
                                                <div class="approval-meta"><strong>{{ __('teacher.dash.step') }}:</strong> {{ $approval->approval_order }}</div>
                                                <div class="approval-meta mb-3">
                                                    <strong>{{ __('teacher.status') }}:</strong>
                                                    {{ $isRejected ? __('teacher.rejected') : __('teacher.dash.pending_your_approval') }}
                                                </div>
                                                @if($isRejected && $approval->rejection_reason)
                                                    <div class="approval-meta mb-3"><strong>{{ __('teacher.dash.reason') }}:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}</div>
                                                @endif
                                                @if($exam && $exam->examID)
                                                    <a href="{{ route('approve_result', $exam->examID) }}" class="btn btn-sm btn-primary" style="background:{{ $tone }};border-color:{{ $tone }};">
                                                        <i class="fi fi-rr-eye me-1"></i> {{ $isRejected ? __('teacher.dash.view_review') : __('teacher.dash.go_approve') }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fi fi-rr-eye me-1"></i> {{ $isRejected ? __('teacher.dash.view_review') : __('teacher.dash.go_approve') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(isset($waitingApprovals) && count($waitingApprovals) > 0)
                                @foreach($waitingApprovals as $waitingApproval)
                                    @php
                                        $approval = $waitingApproval['approval'];
                                        $currentApprover = $waitingApproval['current_approver'] ?? null;
                                        $pendingCount = $waitingApproval['pending_count'] ?? 0;
                                    @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card approval-card is-waiting">
                                            <div class="card-body">
                                                <div class="fw-bold mb-2" style="color:#6c757d;">
                                                    <i class="fi fi-rr-clock me-1"></i>
                                                    {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
                                                </div>
                                                <div class="approval-meta"><strong>{{ __('teacher.dash.your_role') }}:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}</div>
                                                <div class="approval-meta"><strong>{{ __('teacher.dash.step') }}:</strong> {{ $approval->approval_order }}</div>
                                                <div class="approval-meta mb-2">{{ __('teacher.dash.waiting_previous', ['count' => $pendingCount]) }}</div>
                                                @if($currentApprover)
                                                    <div class="approval-meta mb-3">
                                                        <strong>{{ __('teacher.dash.current') }}:</strong>
                                                        @if($currentApprover->role ?? null)
                                                            {{ $currentApprover->role->name ?? $currentApprover->role->role_name ?? 'N/A' }}
                                                        @else
                                                            Step {{ $currentApprover->approval_order ?? 'N/A' }}
                                                        @endif
                                                        <span class="badge {{ ($currentApprover->status ?? 'pending') === 'rejected' ? 'bg-danger' : 'bg-warning' }}">
                                                            {{ ucfirst($currentApprover->status ?? 'pending') }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if($approval->examination && $approval->examination->examID)
                                                    <a href="{{ route('approve_result', $approval->examination->examID) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fi fi-rr-sitemap me-1"></i> {{ __('teacher.dash.view_chain') }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fi fi-rr-sitemap me-1"></i> {{ __('teacher.dash.view_chain') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @else
        <div class="alert notif-strip mb-0">
            <i class="fi fi-rr-info me-2"></i> {{ __('teacher.dash.loading_stats') }}
        </div>
    @endif
</div>

{{-- Class Teacher Approvals Modal (BS5) --}}
<div class="modal fade" id="classTeacherApprovalsModal" tabindex="-1" aria-labelledby="classTeacherApprovalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="classTeacherApprovalsModalLabel">
                    <i class="fi fi-rr-users me-1"></i> {{ __('teacher.dash.ct_approvals_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="classTeacherApprovalsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">{{ __('teacher.dash.loading_ct_approvals') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('teacher.close') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function boot($) {
        if (!$ || typeof $.fn === 'undefined') return;
        $(function () {
            $('.view-class-teacher-approvals').on('click', function () {
                const examID = $(this).data('exam-id');
                var modalEl = document.getElementById('classTeacherApprovalsModal');
                if (window.bootstrap && bootstrap.Modal && modalEl) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if ($.fn.modal) {
                    $('#classTeacherApprovalsModal').modal('show');
                }

                $.ajax({
                    url: '{{ route("get_class_teacher_approvals", ":examID") }}'.replace(':examID', examID),
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            let html = `<h6 class="mb-3"><strong>${response.exam_name}</strong></h6>`;
                            html += '<div class="table-responsive"><table class="table table-bordered table-hover">';
                            html += '<thead><tr>';
                            html += '<th>Class</th><th>Subclass</th><th>Class Teacher</th><th>Phone</th><th>Status</th><th>Action</th>';
                            html += '</tr></thead><tbody>';

                            response.details.forEach(function (detail) {
                                const statusBadge = detail.status === 'approved'
                                    ? '<span class="badge bg-success">Approved</span>'
                                    : detail.status === 'rejected'
                                    ? '<span class="badge bg-danger">Rejected</span>'
                                    : '<span class="badge bg-warning text-dark">Pending</span>';

                                const teacherName = detail.class_teacher ? detail.class_teacher.name : 'Not Assigned';
                                const teacherPhone = detail.class_teacher ? (detail.class_teacher.phone || 'N/A') : 'N/A';

                                html += '<tr>';
                                html += `<td>${detail.class_name}</td>`;
                                html += `<td>${detail.subclass_name}</td>`;
                                html += `<td>${teacherName}</td>`;
                                html += `<td>${teacherPhone}</td>`;
                                html += `<td>${statusBadge}</td>`;
                                html += '<td>';
                                if (detail.status === 'pending' && teacherPhone && teacherPhone !== 'N/A') {
                                    html += `<button class="btn btn-sm btn-primary send-reminder-sms" data-phone="${teacherPhone}" data-subclass="${detail.subclass_name}">`;
                                    html += 'Send Reminder</button>';
                                }
                                html += '</td></tr>';
                            });

                            html += '</tbody></table></div>';
                            $('#classTeacherApprovalsContent').html(html);

                            $('.send-reminder-sms').on('click', function () {
                                const subclass = $(this).data('subclass');
                                Swal.fire({
                                    title: 'Send Reminder SMS',
                                    text: `Send reminder to class teacher of ${subclass}?`,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#940000',
                                    confirmButtonText: 'Send'
                                }).then(function (result) {
                                    if (!result.isConfirmed) return;
                                    Swal.fire({ icon: 'info', title: 'Reminder', text: 'Please use the exam approval page to send reminders.', confirmButtonColor: '#940000' });
                                });
                            });
                        } else {
                            $('#classTeacherApprovalsContent').html('<div class="alert alert-warning">' + (response.message || 'No data') + '</div>');
                        }
                    },
                    error: function () {
                        $('#classTeacherApprovalsContent').html('<div class="alert alert-danger">Error loading class teacher approvals. Please try again.</div>');
                    }
                });
            });
        });
    }

    if (window.jQuery) {
        boot(window.jQuery);
    } else {
        var s = document.createElement('script');
        s.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
        s.onload = function () { boot(window.jQuery); };
        document.head.appendChild(s);
    }
})();
</script>
@endpush

@include('partials.tange_download_progress')
@endsection
