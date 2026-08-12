@include('includes.teacher_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #333 !important;
    }
    .btn-primary-custom {
        background-color: #fff;
        border-color: #dee2e6;
        color: #333;
    }
    .btn-primary-custom:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
        color: #212529;
    }
    .btn-outline-primary-custom {
        border-color: #dee2e6;
        color: #555;
        background: #fff;
    }
    .btn-outline-primary-custom:hover {
        background-color: #f5f5f5;
        border-color: #adb5bd;
        color: #212529;
    }
    .dashboard-hero {
        background: #fff;
        border: 1px solid #eeeeee;
        border-top: 3px solid #940000;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .dashboard-hero .hero-title {
        font-size: 1.15rem;
        margin-bottom: 4px;
        color: #222;
    }
    .dashboard-hero .hero-subtitle {
        color: #6c757d;
        margin-bottom: 0;
        font-size: 0.85rem;
    }
    .dashboard-hero .hero-actions .btn {
        margin-left: 6px;
    }
    .dashboard-card {
        border: 1px solid #eeeeee;
        box-shadow: none;
        height: 100%;
        background: #fff;
        overflow: hidden;
    }
    .dashboard-card .card-body {
        padding: 12px 14px;
    }
    .dashboard-card .card-title {
        font-weight: 600;
        color: #444;
        margin-bottom: 4px;
        font-size: 0.82rem;
        line-height: 1.25;
    }
    .dashboard-card .stat {
        font-size: 1.35rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    .dashboard-card .stat-note {
        font-size: 0.72rem;
        color: #6c757d;
        line-height: 1.2;
    }
    .dashboard-card .card-icon {
        font-size: 1.25rem;
        color: #adb5bd;
        flex-shrink: 0;
    }
    .dashboard-card .card-actions {
        margin-top: 8px;
    }
    .dashboard-card .card-actions .btn {
        font-size: 0.72rem;
        padding: 4px 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dashboard-widgets-row {
        margin-left: -6px;
        margin-right: -6px;
    }
    .dashboard-widgets-row > [class*="col-"] {
        padding-left: 6px;
        padding-right: 6px;
        margin-bottom: 12px;
    }
    .section-card {
        border: 1px solid #eeeeee;
        box-shadow: none;
        background: #fff;
    }
    .section-card .section-header {
        background: whitesmoke;
        border-bottom: 1px solid #eeeeee;
        border-left: 3px solid #940000;
        color: #333;
        font-weight: 600;
        padding: 10px 14px;
        font-size: 0.95rem;
    }
    .section-card .section-header small {
        color: #6c757d;
        font-weight: 400;
    }
    @media (max-width: 575.98px) {
        .dashboard-hero { padding: 10px 12px; }
        .dashboard-card .stat { font-size: 1.05rem; }
        .dashboard-card .card-body { padding: 8px 8px; }
        .dashboard-card .card-title { font-size: 0.68rem; }
        .dashboard-card .stat-note { font-size: 0.62rem; }
        .dashboard-card .card-icon { font-size: 1rem; }
        .dashboard-card .card-actions .btn {
            font-size: 0.62rem;
            padding: 3px 5px;
        }
        .dashboard-widgets-row {
            margin-left: -4px;
            margin-right: -4px;
        }
        .dashboard-widgets-row > [class*="col-"] {
            padding-left: 4px;
            padding-right: 4px;
            margin-bottom: 8px;
        }
    }
</style>
<div class="container-fluid mt-3">
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@php
    // Check if there's a session happening now
    $currentSession = null;
    $sessionNotification = null;
    if (isset($teacherNotifications) && $teacherNotifications->count() > 0) {
        $sessionNotifications = $teacherNotifications->filter(function($n) {
            return isset($n['type']) && $n['type'] === 'session_time';
        });
        if ($sessionNotifications->count() > 0) {
            $sessionNotification = $sessionNotifications->first();
        }
    }
@endphp

@if($sessionNotification)
<div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 4px solid #ffc107;">
    <h5 class="alert-heading">
        <i class="fa fa-clock-o"></i> Session Time Alert!
    </h5>
    <p class="mb-2"><strong>{{ $sessionNotification['title'] }}</strong></p>
    <p class="mb-0">{{ $sessionNotification['message'] }}</p>
    <hr>
    <p class="mb-0">
        <a href="{{ $sessionNotification['link'] ?? route('teacher.mySessions') }}" class="btn btn-sm btn-warning">
            <i class="fa fa-arrow-right"></i> Go to Sessions
        </a>
    </p>
</div>
@endif

@if(isset($isOnDuty) && $isOnDuty)
<div class="alert alert-info alert-dismissible fade show" role="alert" style="border-left: 4px solid #17a2b8; background-color: #e3f2fd;">
    <h5 class="alert-heading text-primary">
        <i class="fa fa-bullhorn"></i> Duty Notification
    </h5>
    <p class="mb-2">You are <strong>Teacher on Duty</strong> for this week!</p>
    <p class="mb-0">Please ensure all duty responsibilities are fulfilled as scheduled.</p>
    <hr>
    <div class="d-flex justify-content-between align-items-center">
        <span>Check the duty roster for more details.</span>
        <a href="{{ route('teacher.duty_book') }}" class="btn btn-primary-custom btn-sm">
            <i class="fa fa-book"></i> Go to Manage
        </a>
    </div>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif


<!-- Dashboard Statistics Section -->
@if(isset($dashboardStats))
<div class="dashboard-hero">
                    <div>
        <div class="text-muted" style="font-size: 0.9rem;">Today Â· {{ \Carbon\Carbon::now()->format('l, d M Y') }}</div>
        <div class="hero-title">Hello, Teacher</div>
        @if(isset($isNavHOD) && $isNavHOD)
            <div class="mb-2">
                <span class="badge text-white p-2" style="background: linear-gradient(135deg, #940000, #c0392b); border-radius: 4px; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(148,0,0,0.2);">
                    <i class="fa fa-star"></i> HEAD OF DEPARTMENT: {{ $hodDeptName ?? '' }}
                </span>
            </div>
        @endif
        <p class="hero-subtitle">Quick access to your teaching modules and updates.</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('teacher.mySessions') }}" class="btn btn-primary-custom btn-sm">
            <i class="fa fa-clock-o"></i> My Sessions
        </a>
        <a href="{{ route('teacher.myTasks') }}" class="btn btn-outline-primary-custom btn-sm">
            <i class="fa fa-tasks"></i> My Tasks
        </a>
    </div>
</div>

@php
    $todayAttendanceStats = $teacherTodayAttendance['statistics'] ?? [];
    $todayAttendanceStatus = $todayAttendanceStats['day_status'] ?? 'Not Available';
    $todayAttendanceLabel = $todayAttendanceStatus === 'Present' ? 'On Time' : $todayAttendanceStatus;
    $todayCheckIn = $todayAttendanceStats['check_in_time'] ?? null;
    $todayCheckOut = $todayAttendanceStats['check_out_time'] ?? null;
    $attendanceStatusColors = [
        'Present' => ['#e8f5e9', '#207227'],
        'Early' => ['#fff7e6', '#8a5a00'],
        'Late' => ['#fff0f0', '#940000'],
        'Absent' => ['#fff0f0', '#940000'],
        'Permitted' => ['#f4eef8', '#6f42c1'],
        'Off Day' => ['#f5f5f5', '#555555'],
        'Not Available' => ['#f5f5f5', '#555555'],
    ];
    $todayAttendanceColors = $attendanceStatusColors[$todayAttendanceStatus] ?? $attendanceStatusColors['Not Available'];
@endphp
<div class="card section-card mb-4">
    <div class="section-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-calendar-check-o"></i> My Attendance Today</span>
        <span class="badge px-3 py-2" style="background: {{ $todayAttendanceColors[0] }}; color: {{ $todayAttendanceColors[1] }};">
            {{ $todayAttendanceLabel }}
        </span>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-sm-4 mb-2">
                        <small class="text-muted d-block">Check In</small>
                        <strong>{{ $todayCheckIn ? \Carbon\Carbon::parse($todayCheckIn)->format('h:i A') : '-' }}</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <small class="text-muted d-block">Check Out</small>
                        <strong>{{ $todayCheckOut ? \Carbon\Carbon::parse($todayCheckOut)->format('h:i A') : '-' }}</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <small class="text-muted d-block">Official Time</small>
                        <strong>{{ !empty($teacherTodayAttendance['work_start_time']) ? \Carbon\Carbon::parse($teacherTodayAttendance['work_start_time'])->format('h:i A') : '-' }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <a href="{{ route('teacher.my_attendance') }}" class="btn btn-primary-custom btn-sm">
                    <i class="fa fa-eye"></i> See Report
                </a>
            </div>
        </div>
    </div>
</div>

@if(!empty($tangeTeacher))
    @php
        $dashPct = (int) ($tangeTeacher->tange_completion_percent ?? 0);
        $dashBar = $dashPct >= 100 ? 'bg-success' : ($dashPct >= 50 ? 'bg-warning' : 'bg-danger');
        $dashRetire = $tangeTeacher->retirement_alert ?? null;
    @endphp
    <div class="alert {{ $dashPct >= 100 ? 'alert-success' : 'alert-warning' }} border-0 shadow-sm mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div style="flex:1;min-width:220px;">
                <strong><i class="fa fa-id-card-o"></i> TANGE Profile Completeness: {{ $dashPct }}%</strong>
                <div class="progress mt-2" style="height:10px;max-width:360px;">
                    <div class="progress-bar {{ $dashBar }}" style="width:{{ $dashPct }}%"></div>
                </div>
                @if($dashPct < 100)
                    <div class="small mt-2 mb-0">Your Teachers Particular (TANGE) details are incomplete. Complete them for HR / TSC reports.</div>
                @else
                    <div class="small mt-2 mb-0">Your TANGE profile is complete.</div>
                @endif
                @if($dashRetire)
                    <div class="small text-{{ $dashRetire['level'] === 'danger' ? 'danger' : 'dark' }} mt-1">
                        <i class="fa fa-exclamation-triangle"></i> {{ $dashRetire['message'] }}
                    </div>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($dashPct < 100)
                    <a href="{{ route('teacher.profile') }}" class="btn btn-primary-custom btn-sm">
                        <i class="fa fa-edit"></i> Complete My Details
                    </a>
                @else
                    <a href="{{ route('teacher.profile') }}" class="btn btn-outline-primary-custom btn-sm">
                        <i class="fa fa-user"></i> View Profile
                    </a>
                @endif
                <a href="{{ route('teacher.profile.download_pdf') }}" class="btn btn-outline-danger btn-sm js-download-tange" data-filename="TANGE_My_Particulars">
                    <i class="fa fa-file-pdf-o"></i> Download My TANGE
                </a>
            </div>
        </div>
    </div>
@endif

@php
    $cards = [
        ['title' => 'Subjects Teaching', 'count' => $dashboardStats['subjects_count'] ?? 0, 'icon' => 'fa-book', 'link' => route('teacherSubjects'), 'link_label' => 'View Subjects'],
        ['title' => 'Classes Teaching', 'count' => $dashboardStats['classes_count'] ?? 0, 'icon' => 'fa-users', 'note' => 'Subclasses with sessions', 'link' => (isset($hasAssignedClass) && $hasAssignedClass) ? route('AdmitedClasses') : null, 'link_label' => (isset($hasAssignedClass) && $hasAssignedClass) ? 'View Classes' : 'No Classes Assigned'],
    ];
    if (isset($coordinator) && $coordinator->count() > 0) {
        $cards[] = ['title' => 'Class Teacher', 'count' => $dashboardStats['class_teacher_subclasses_count'] ?? 0, 'icon' => 'fa-user-tie', 'note' => 'Subclasses Managing', 'link' => route('AdmitedClasses'), 'link_label' => 'View Managed Classes'];
    }
    $cards[] = ['title' => 'Sessions Per Week', 'count' => $dashboardStats['sessions_per_week'] ?? 0, 'icon' => 'fa-calendar', 'note' => 'Monday - Friday', 'link' => route('teacher.mySessions'), 'link_label' => 'View Sessions'];
    if (isset($dashboardStats['coordinator_classes_count']) && $dashboardStats['coordinator_classes_count'] > 0) {
        $cards[] = ['title' => 'Coordinator', 'count' => $dashboardStats['coordinator_classes_count'] ?? 0, 'icon' => 'fa-user-graduate', 'note' => 'Main Classes Managing', 'link' => route('AdmitedClasses', ['coordinator' => 'true']), 'link_label' => 'Manage Classes'];
    }
    $cards[] = ['title' => 'Sessions Per Year', 'count' => $dashboardStats['sessions_per_year'] ?? 0, 'icon' => 'fa-calendar', 'note' => 'Excluding holidays', 'link' => null, 'link_label' => 'Annual Count'];
    $cards[] = ['title' => 'Sessions Entered', 'count' => $dashboardStats['approved_sessions_count'] ?? 0, 'icon' => 'fa-check-circle', 'note' => 'With approved tasks', 'link' => route('teacher.mySessions'), 'link_label' => 'View More'];
    $cards[] = ['title' => 'Scheme of Work', 'count' => $dashboardStats['scheme_of_work_count'] ?? 0, 'icon' => 'fa-book-open', 'note' => 'For current year', 'link' => route('teacher.schemeOfWork'), 'link_label' => 'View Scheme of Work'];
    $cards[] = ['title' => 'Lesson Plans', 'count' => $dashboardStats['lesson_plans_count'] ?? 0, 'icon' => 'fa-file-text', 'note' => ($dashboardStats['lesson_plans_sent_count'] ?? 0) . ' sent to admin', 'link' => route('teacher.lessonPlans'), 'link_label' => 'View Lesson Plans'];
@endphp

<div class="row dashboard-widgets-row">
    @foreach($cards as $card)
        <div class="col-4 col-md-4 col-lg-3 col-xl-2">
            <div class="card dashboard-card">
            <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                    <div style="min-width:0;flex:1;">
                            <div class="card-title">{{ $card['title'] }}</div>
                            <div class="stat">{{ $card['count'] }}</div>
                            @if(!empty($card['note']))
                                <div class="stat-note">{{ $card['note'] }}</div>
    @endif
                    </div>
                        <div class="card-icon">
                            <i class="fa {{ $card['icon'] }}"></i>
    </div>
                    </div>
                    <div class="card-actions">
                        @if(!empty($card['link']))
                            <a href="{{ $card['link'] }}" class="btn btn-sm btn-outline-primary-custom w-100">
                                <i class="fa fa-eye"></i> {{ $card['link_label'] }}
                            </a>
                        @else
                            <button class="btn btn-sm btn-outline-primary-custom w-100" disabled>
                                <i class="fa fa-info-circle"></i> {{ $card['link_label'] }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Exam Approval Widgets -->
@if((isset($pendingApprovals) && count($pendingApprovals) > 0) || 
    (isset($specialRoleApprovals) && count($specialRoleApprovals) > 0) || 
    (isset($waitingApprovals) && count($waitingApprovals) > 0))
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h5 class="text-dark" style="font-weight: 600;">
            <i class="fa fa-check-circle"></i> Exam Approvals Pending
        </h5>
        <hr style="border-top: 1px solid #eeeeee;">
    </div>
    
    <!-- Pending Approvals (Regular Roles) -->
@if(isset($pendingApprovals) && count($pendingApprovals) > 0)
        @foreach($pendingApprovals as $approval)
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }} !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                <h6 class="mb-1" style="font-weight: 600; color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }};">
                                    <i class="fa fa-{{ $approval->status === 'rejected' ? 'times-circle' : 'exclamation-triangle' }}"></i> 
                                    {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
                                </h6>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Role:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Step:</strong> {{ $approval->approval_order }}
                                </p>
                        @if($approval->status === 'rejected')
                                    <p class="mb-1" style="color: #dc3545; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Rejected
                                    </p>
                            @if($approval->rejection_reason)
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                            <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}
                                        </p>
                            @endif
                        @else
                                    <p class="mb-0" style="color: #ffc107; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Pending Your Approval
                                    </p>
                        @endif
                            </div>
                        </div>
                        @if($approval->examination && $approval->examination->examID)
                        <a href="{{ route('approve_result', $approval->examination->examID) }}" class="btn btn-sm mt-2 w-100" style="background-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; border-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; color: white; border-radius: 0;">
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Review & Approve' }}
                            </a>
                        @else
                        <button class="btn btn-sm mt-2 w-100" style="background-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; border-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; color: white; border-radius: 0;" disabled>
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Review & Approve' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
@endif

    <!-- Special Role Approvals (Class Teacher & Coordinator) -->
@if(isset($specialRoleApprovals) && count($specialRoleApprovals) > 0)
        @foreach($specialRoleApprovals as $specialApproval)
            @php
                $approval = $specialApproval['approval'];
                $type = $specialApproval['type'];
                $exam = $specialApproval['exam'];
                $borderColor = $approval->status === 'rejected' ? '#dc3545' : ($type === 'class_teacher' ? '#ffc107' : '#17a2b8');
            @endphp
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $borderColor }} !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                <h6 class="mb-1" style="font-weight: 600; color: {{ $borderColor }};">
                                    <i class="fa fa-{{ $approval->status === 'rejected' ? 'times-circle' : ($type === 'class_teacher' ? 'users' : 'diagram-3') }}"></i> 
                                    {{ $exam->exam_name ?? 'N/A' }}
                                </h6>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Step:</strong> {{ $approval->approval_order }}
                                </p>
                        @if($approval->status === 'rejected')
                                    <p class="mb-1" style="color: #dc3545; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Rejected
                                    </p>
                            @if($approval->rejection_reason)
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                            <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}
                                        </p>
                            @endif
                        @else
                                    <p class="mb-0" style="color: {{ $borderColor }}; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Pending Your Approval
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if($exam && $exam->examID)
                        <a href="{{ route('approve_result', $exam->examID) }}" class="btn btn-sm mt-2 w-100" style="background-color: {{ $borderColor }}; border-color: {{ $borderColor }}; color: white; border-radius: 0;">
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Go to Approve' }}
                        </a>
                        @else
                        <button class="btn btn-sm mt-2 w-100" style="background-color: {{ $borderColor }}; border-color: {{ $borderColor }}; color: white; border-radius: 0;" disabled>
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Go to Approve' }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
@endif

    <!-- Waiting Approvals -->
@if(isset($waitingApprovals) && count($waitingApprovals) > 0)
        @foreach($waitingApprovals as $waitingApproval)
            @php
                $approval = $waitingApproval['approval'];
                $currentApprover = $waitingApproval['current_approver'] ?? null;
                $pendingCount = $waitingApproval['pending_count'] ?? 0;
            @endphp
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                <h6 class="mb-1" style="font-weight: 600; color: #6c757d;">
                                    <i class="fa fa-clock-o"></i> {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
                                </h6>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Your Role:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Step:</strong> {{ $approval->approval_order }}
                                </p>
                                <p class="mb-1" style="color: #6c757d; font-size: 0.85rem; font-weight: 600;">
                                    <strong>Status:</strong> Waiting for {{ $pendingCount }} previous approval(s)
                                </p>
                        @if($currentApprover)
                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                        <strong>Current:</strong> 
                                        @if($currentApprover->role ?? null)
                                    {{ $currentApprover->role->name ?? $currentApprover->role->role_name ?? 'N/A' }}
                                @else
                                            Step {{ $currentApprover->approval_order ?? 'N/A' }}
                                        @endif
                                        <span class="badge badge-{{ ($currentApprover->status ?? 'pending') === 'rejected' ? 'danger' : 'warning' }}" style="border-radius: 0;">
                                            {{ ucfirst($currentApprover->status ?? 'pending') }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if($approval->examination && $approval->examination->examID)
                        <a href="{{ route('approve_result', $approval->examination->examID) }}" class="btn btn-sm mt-2 w-100" style="background-color: #6c757d; border-color: #6c757d; color: white; border-radius: 0;">
                            <i class="fa fa-diagram-3"></i> View Approval Chain
                            </a>
                        @else
                        <button class="btn btn-sm mt-2 w-100" style="background-color: #6c757d; border-color: #6c757d; color: white; border-radius: 0;" disabled>
                            <i class="fa fa-diagram-3"></i> View Approval Chain
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    </div>
@endif

@else
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Loading dashboard statistics...
        </div>
    </div>
</div>
@endif
</div>

<!-- Class Teacher Approvals Modal -->
<div class="modal fade" id="classTeacherApprovalsModal" tabindex="-1" role="dialog" aria-labelledby="classTeacherApprovalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="classTeacherApprovalsModalLabel">
                    <i class="bi bi-people"></i> Class Teacher Approvals Status
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="classTeacherApprovalsContent">
                    <div class="text-center">
                        <div class="spinner-border text-warning" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading class teacher approvals...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Handle class teacher approvals button click
    $('.view-class-teacher-approvals').on('click', function() {
        const examID = $(this).data('exam-id');
        $('#classTeacherApprovalsModal').modal('show');
        
        // Load class teacher approvals
        $.ajax({
            url: '{{ route("get_class_teacher_approvals", ":examID") }}'.replace(':examID', examID),
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    let html = `<h6 class="mb-3"><strong>${response.exam_name}</strong></h6>`;
                    html += '<table class="table table-bordered table-hover">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th>Class</th>';
                    html += '<th>Subclass</th>';
                    html += '<th>Class Teacher</th>';
                    html += '<th>Phone</th>';
                    html += '<th>Status</th>';
                    html += '<th>Action</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    response.details.forEach(function(detail) {
                        const statusBadge = detail.status === 'approved' 
                            ? '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>'
                            : detail.status === 'rejected'
                            ? '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>'
                            : '<span class="badge badge-warning"><i class="bi bi-clock"></i> Pending</span>';
                        
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
                            html += '<i class="bi bi-send"></i> Send Reminder';
                            html += '</button>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    
                    $('#classTeacherApprovalsContent').html(html);
                    
                    // Handle send reminder SMS for class teachers
                    $('.send-reminder-sms').on('click', function() {
                        const phone = $(this).data('phone');
                        const subclass = $(this).data('subclass');
                        
                        Swal.fire({
                            title: 'Send Reminder SMS',
                            text: `Send reminder to class teacher of ${subclass}?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'Send'
                        }).then(function(result) {
                            if (!result.isConfirmed) return;
                            // Reminder send handled elsewhere if wired
                            Swal.fire({ icon: 'info', title: 'Reminder', text: 'Please use the exam approval page to send reminders.', confirmButtonColor: '#940000' });
                        });
                    });
                } else {
                    $('#classTeacherApprovalsContent').html('<div class="alert alert-warning">' + (response.message || 'No data') + '</div>');
                }
            },
            error: function(xhr) {
                $('#classTeacherApprovalsContent').html('<div class="alert alert-danger">Error loading class teacher approvals. Please try again.</div>');
            }
        });
    });
});
</script>

@include('includes.footer')
@include('partials.tange_download_progress')
