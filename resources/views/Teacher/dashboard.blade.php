@include('includes.teacher_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/teacher-dashboard.css') }}?v=2">

@php
    $teacherDisplayName = trim(($tangeTeacher->first_name ?? '') . ' ' . ($tangeTeacher->last_name ?? ''));
    if ($teacherDisplayName === '') {
        $teacherDisplayName = 'Teacher';
    }
@endphp

<div class="container-fluid mt-3 td">
  @if (session('success'))
    <div class="td-alert ok" role="alert">{{ session('success') }}</div>
  @endif

@php
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
<div class="td-alert session">
    <h5><i class="fa fa-clock-o"></i> Session time alert</h5>
    <p><strong>{{ $sessionNotification['title'] }}</strong></p>
    <p>{{ $sessionNotification['message'] }}</p>
    <p class="mt-2 mb-0">
        <a href="{{ $sessionNotification['link'] ?? route('teacher.mySessions') }}" class="td-btn td-btn-brand td-btn-sm">
            <i class="fa fa-arrow-right"></i> Go to sessions
        </a>
    </p>
</div>
@endif

@if(isset($isOnDuty) && $isOnDuty)
<div class="td-alert duty">
    <h5><i class="fa fa-bullhorn"></i> Duty notification</h5>
    <p>You are <strong>Teacher on Duty</strong> this week. Please complete all duty responsibilities as scheduled.</p>
    <p class="mt-2 mb-0">
        <a href="{{ route('teacher.duty_book') }}" class="td-btn td-btn-outline td-btn-sm">
            <i class="fa fa-book"></i> Open duty book
        </a>
    </p>
</div>
@endif

@if(isset($dashboardStats))
<section class="td-hero">
    <div class="td-hero-inner">
        <div>
            <span class="td-kicker">Today · {{ \Carbon\Carbon::now()->format('l, d M Y') }}</span>
            <h1>Hello, {{ $teacherDisplayName }}</h1>
            <p>Your teaching overview - sessions, plans, attendance, and approvals in one place.</p>
            @if(isset($isNavHOD) && $isNavHOD)
                <span class="td-hod"><i class="fa fa-star"></i> Head of Department: {{ $hodDeptName ?? '' }}</span>
            @endif
        </div>
        <div class="td-actions">
            <a href="{{ route('teacher.mySessions') }}" class="td-btn td-btn-solid"><i class="fa fa-clock-o"></i> My Sessions</a>
            <a href="{{ route('teacher.myTasks') }}" class="td-btn td-btn-ghost"><i class="fa fa-tasks"></i> My Tasks</a>
            @if(\App\Models\School::where('schoolID', Session::get('schoolID'))->value('student_portal_enabled'))
            <a href="{{ route('student_portal.materials') }}" class="td-btn td-btn-ghost"><i class="fa fa-graduation-cap"></i> Learning Portal</a>
            @endif
        </div>
    </div>
</section>

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

<div class="td-panel">
    <div class="td-panel-head">
        <h2><i class="fa fa-calendar-check-o"></i> My attendance today</h2>
        <span class="td-status" style="background: {{ $todayAttendanceColors[0] }}; color: {{ $todayAttendanceColors[1] }};">
            {{ $todayAttendanceLabel }}
        </span>
    </div>
    <div class="td-panel-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:14px;">
            <div class="td-att-grid" style="flex:1;min-width:220px;">
                <div class="td-att-item">
                    <small>Check in</small>
                    <strong>{{ $todayCheckIn ? \Carbon\Carbon::parse($todayCheckIn)->format('h:i A') : '-' }}</strong>
                </div>
                <div class="td-att-item">
                    <small>Check out</small>
                    <strong>{{ $todayCheckOut ? \Carbon\Carbon::parse($todayCheckOut)->format('h:i A') : '-' }}</strong>
                </div>
                <div class="td-att-item">
                    <small>Official time</small>
                    <strong>{{ !empty($teacherTodayAttendance['work_start_time']) ? \Carbon\Carbon::parse($teacherTodayAttendance['work_start_time'])->format('h:i A') : '-' }}</strong>
                </div>
            </div>
            <a href="{{ route('teacher.my_attendance') }}" class="td-btn td-btn-outline td-btn-sm">
                <i class="fa fa-eye"></i> See report
            </a>
        </div>
    </div>
</div>

@if(!empty($tangeTeacher))
    @php
        $dashPct = (int) ($tangeTeacher->tange_completion_percent ?? 0);
        $dashBarClass = $dashPct >= 100 ? 'ok' : ($dashPct >= 50 ? 'mid' : '');
        $dashRetire = $tangeTeacher->retirement_alert ?? null;
    @endphp
    <div class="td-alert {{ $dashPct >= 100 ? 'ok' : 'warn' }}">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:14px;">
            <div style="flex:1;min-width:220px;">
                <h5><i class="fa fa-id-card-o"></i> TANGE profile completeness: {{ $dashPct }}%</h5>
                <div class="td-progress {{ $dashBarClass }}"><span style="width:{{ $dashPct }}%"></span></div>
                @if($dashPct < 100)
                    <p class="mt-2">Your Teachers Particular (TANGE) details are incomplete. Complete them for HR / TSC reports.</p>
                @else
                    <p class="mt-2">Your TANGE profile is complete.</p>
                @endif
                @if($dashRetire)
                    <p class="text-{{ $dashRetire['level'] === 'danger' ? 'danger' : 'dark' }}">
                        <i class="fa fa-exclamation-triangle"></i> {{ $dashRetire['message'] }}
                    </p>
                @endif
            </div>
            <div class="td-actions">
                @if($dashPct < 100)
                    <a href="{{ route('teacher.profile') }}" class="td-btn td-btn-brand td-btn-sm"><i class="fa fa-edit"></i> Complete details</a>
                @else
                    <a href="{{ route('teacher.profile') }}" class="td-btn td-btn-outline td-btn-sm"><i class="fa fa-user"></i> View profile</a>
                @endif
                <a href="{{ route('teacher.profile.download_pdf') }}" class="td-btn td-btn-outline td-btn-sm js-download-tange" data-filename="TANGE_My_Particulars">
                    <i class="fa fa-file-pdf-o"></i> Download TANGE
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

<div class="td-widgets">
    @foreach($cards as $card)
        <div class="td-widget">
            <div class="td-widget-top">
                <div>
                    <div class="td-widget-title">{{ $card['title'] }}</div>
                    <div class="td-widget-stat">{{ $card['count'] }}</div>
                    @if(!empty($card['note']))
                        <div class="td-widget-note">{{ $card['note'] }}</div>
                    @endif
                </div>
                <div class="td-widget-icon"><i class="fa {{ $card['icon'] }}"></i></div>
            </div>
            <div class="td-widget-foot">
                @if(!empty($card['link']))
                    <a href="{{ $card['link'] }}" class="td-btn td-btn-outline td-btn-sm">
                        <i class="fa fa-eye"></i> {{ $card['link_label'] }}
                    </a>
                @else
                    <span class="td-btn td-btn-outline td-btn-sm is-disabled">
                        <i class="fa fa-info-circle"></i> {{ $card['link_label'] }}
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</div>

@if((isset($pendingApprovals) && count($pendingApprovals) > 0) ||
    (isset($specialRoleApprovals) && count($specialRoleApprovals) > 0) ||
    (isset($waitingApprovals) && count($waitingApprovals) > 0))
<div class="td-section-title"><i class="fa fa-check-circle"></i> Exam approvals pending</div>
<div class="td-approval-grid">

@if(isset($pendingApprovals) && count($pendingApprovals) > 0)
    @foreach($pendingApprovals as $approval)
        @php $border = $approval->status === 'rejected' ? '#dc3545' : '#c79100'; @endphp
        <div class="td-approval" style="border-left-color: {{ $border }};">
            <h6 style="color: {{ $border }};">
                <i class="fa fa-{{ $approval->status === 'rejected' ? 'times-circle' : 'exclamation-triangle' }}"></i>
                {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
            </h6>
            <div class="meta"><strong>Role:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}</div>
            <div class="meta"><strong>Step:</strong> {{ $approval->approval_order }}</div>
            @if($approval->status === 'rejected')
                <div class="status-line" style="color:#dc3545;">Status: Rejected</div>
                @if($approval->rejection_reason)
                    <div class="meta"><strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}</div>
                @endif
            @else
                <div class="status-line" style="color:#c79100;">Status: Pending your approval</div>
            @endif
            @if($approval->examination && $approval->examination->examID)
                <a href="{{ route('approve_result', $approval->examination->examID) }}" class="td-btn td-btn-sm" style="background:{{ $border }};color:#fff;">
                    <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Review & Approve' }}
                </a>
            @else
                <button class="td-btn td-btn-sm is-disabled" style="background:{{ $border }};color:#fff;" disabled>
                    <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Review & Approve' }}
                </button>
            @endif
        </div>
    @endforeach
@endif

@if(isset($specialRoleApprovals) && count($specialRoleApprovals) > 0)
    @foreach($specialRoleApprovals as $specialApproval)
        @php
            $approval = $specialApproval['approval'];
            $type = $specialApproval['type'];
            $exam = $specialApproval['exam'];
            $borderColor = $approval->status === 'rejected' ? '#dc3545' : ($type === 'class_teacher' ? '#c79100' : '#0d6efd');
        @endphp
        <div class="td-approval" style="border-left-color: {{ $borderColor }};">
            <h6 style="color: {{ $borderColor }};">
                <i class="fa fa-{{ $approval->status === 'rejected' ? 'times-circle' : ($type === 'class_teacher' ? 'users' : 'sitemap') }}"></i>
                {{ $exam->exam_name ?? 'N/A' }}
            </h6>
            <div class="meta"><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}</div>
            <div class="meta"><strong>Step:</strong> {{ $approval->approval_order }}</div>
            @if($approval->status === 'rejected')
                <div class="status-line" style="color:#dc3545;">Status: Rejected</div>
                @if($approval->rejection_reason)
                    <div class="meta"><strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}</div>
                @endif
            @else
                <div class="status-line" style="color: {{ $borderColor }};">Status: Pending your approval</div>
            @endif
            @if($exam && $exam->examID)
                <a href="{{ route('approve_result', $exam->examID) }}" class="td-btn td-btn-sm" style="background:{{ $borderColor }};color:#fff;">
                    <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Go to Approve' }}
                </a>
            @else
                <button class="td-btn td-btn-sm is-disabled" style="background:{{ $borderColor }};color:#fff;" disabled>
                    <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Go to Approve' }}
                </button>
            @endif
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
        <div class="td-approval" style="border-left-color: #6c757d;">
            <h6 style="color:#6c757d;">
                <i class="fa fa-clock-o"></i>
                {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
            </h6>
            <div class="meta"><strong>Your role:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}</div>
            <div class="meta"><strong>Step:</strong> {{ $approval->approval_order }}</div>
            <div class="status-line" style="color:#6c757d;">Waiting for {{ $pendingCount }} previous approval(s)</div>
            @if($currentApprover)
                <div class="meta">
                    <strong>Current:</strong>
                    @if($currentApprover->role ?? null)
                        {{ $currentApprover->role->name ?? $currentApprover->role->role_name ?? 'N/A' }}
                    @else
                        Step {{ $currentApprover->approval_order ?? 'N/A' }}
                    @endif
                    <span class="badge badge-{{ ($currentApprover->status ?? 'pending') === 'rejected' ? 'danger' : 'warning' }}" style="border-radius:0;">
                        {{ ucfirst($currentApprover->status ?? 'pending') }}
                    </span>
                </div>
            @endif
            @if($approval->examination && $approval->examination->examID)
                <a href="{{ route('approve_result', $approval->examination->examID) }}" class="td-btn td-btn-sm" style="background:#6c757d;color:#fff;">
                    <i class="fa fa-sitemap"></i> View approval chain
                </a>
            @else
                <button class="td-btn td-btn-sm is-disabled" style="background:#6c757d;color:#fff;" disabled>
                    <i class="fa fa-sitemap"></i> View approval chain
                </button>
            @endif
        </div>
    @endforeach
@endif
</div>
@endif

@else
<div class="td-alert duty">
    <p class="mb-0"><i class="fa fa-info-circle"></i> Loading dashboard statistics...</p>
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
    $('.view-class-teacher-approvals').on('click', function() {
        const examID = $(this).data('exam-id');
        $('#classTeacherApprovalsModal').modal('show');

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
                    html += '<thead class="thead-light"><tr>';
                    html += '<th>Class</th><th>Subclass</th><th>Class Teacher</th><th>Phone</th><th>Status</th><th>Action</th>';
                    html += '</tr></thead><tbody>';

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
                            html += '<i class="bi bi-send"></i> Send Reminder</button>';
                        }
                        html += '</td></tr>';
                    });

                    html += '</tbody></table>';
                    $('#classTeacherApprovalsContent').html(html);

                    $('.send-reminder-sms').on('click', function() {
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
                            Swal.fire({ icon: 'info', title: 'Reminder', text: 'Please use the exam approval page to send reminders.', confirmButtonColor: '#940000' });
                        });
                    });
                } else {
                    $('#classTeacherApprovalsContent').html('<div class="alert alert-warning">' + (response.message || 'No data') + '</div>');
                }
            },
            error: function() {
                $('#classTeacherApprovalsContent').html('<div class="alert alert-danger">Error loading class teacher approvals. Please try again.</div>');
            }
        });
    });
});
</script>

@include('includes.footer')
@include('partials.tange_download_progress')
