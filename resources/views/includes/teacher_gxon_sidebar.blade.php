{{-- Teacher GXON sidebar menu (ported from includes/teacher_nav) --}}
@php
    $sessTeacherID = Session::get('teacherID');
    $sessSchoolID = Session::get('schoolID');
    $sessUserType = Session::get('user_type');

    $navPhotoTeacher = (isset($teacher) && $teacher) ? $teacher : null;
    if (!$navPhotoTeacher && $sessUserType === 'Teacher' && $sessTeacherID) {
        $navPhotoTeacher = \App\Models\Teacher::find($sessTeacherID);
    }

    $teacherRoleName = 'Teacher';
    if (isset($role) && $role->count() > 0) {
        $firstRole = $role->first();
        if ($firstRole && !empty($firstRole->role_name)) {
            $teacherRoleName = $firstRole->role_name;
        }
    }

    $isNavHOD = $sessTeacherID
        ? \App\Models\Department::where('head_teacherID', $sessTeacherID)->exists()
        : false;
    $hodDeptName = $isNavHOD
        ? \App\Models\Department::where('head_teacherID', $sessTeacherID)->value('department_name')
        : null;

    $isOnDuty = false;
    if ($sessTeacherID && $sessSchoolID) {
        $today = \Carbon\Carbon::today();
        $isOnDuty = \App\Models\TeacherDuty::where('schoolID', $sessSchoolID)
            ->where('teacherID', $sessTeacherID)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();
    }

    $isStudentRequester = $sessTeacherID && $sessSchoolID
        ? \Illuminate\Support\Facades\DB::table('student_permission_requesters')
            ->where('schoolID', $sessSchoolID)
            ->where('user_id', $sessTeacherID)
            ->where('user_type', 'teacher')
            ->where('is_active', true)
            ->exists()
        : false;

    $teacherHasBlackBookAccess = app(\App\Services\BlackBookAccessService::class)
        ->currentUserHasManageAccess((int) $sessSchoolID);
    $teacherHasInventoryAccess = app(\App\Services\StoreAccessService::class)
        ->currentUserHasStoreAccess((int) $sessSchoolID);
    $studentPortalEnabled = \App\Models\School::where('schoolID', $sessSchoolID)->value('student_portal_enabled');

    $canAddResultsByAssignment = false;
    $globalResultPermission = \App\Models\TeacherResultPermission::where('schoolID', $sessSchoolID)->first();
    $isGlobalEnabled = $globalResultPermission ? $globalResultPermission->result_button_status : true;
    if ($isGlobalEnabled && $sessTeacherID && $sessSchoolID) {
        $canAddResultsByAssignment = \App\Models\TeacherResultAssignment::where('schoolID', $sessSchoolID)
            ->where('teacherID', $sessTeacherID)
            ->where('status', true)
            ->exists();
    }

    $teacherRoleIds = isset($role) ? $role->pluck('id')->toArray() : [];
    $roleCount = !empty($teacherRoleIds)
        ? \App\Models\PaperApprovalLog::whereIn('role_id', $teacherRoleIds)->where('status', 'pending')->count()
        : 0;
    $classTeacherSubclassIds = \App\Models\Subclass::where('teacherID', $sessTeacherID)->pluck('subclassID')->toArray();
    $coordinatorClassIds = \App\Models\ClassModel::where('teacherID', $sessTeacherID)->pluck('classID')->toArray();
    $hodDepartments = \App\Models\Department::where('head_teacherID', $sessTeacherID)->get();
    $hodDepartmentIds = $hodDepartments->pluck('departmentID')->toArray();
    $hodSubjectIds = !empty($hodDepartmentIds)
        ? \Illuminate\Support\Facades\DB::table('department_subjects')->whereIn('departmentID', $hodDepartmentIds)->pluck('subjectID')->toArray()
        : [];
    $specialCount = \App\Models\PaperApprovalLog::where('status', 'pending')
        ->whereNotNull('special_role_type')
        ->get()
        ->filter(function ($log) use ($classTeacherSubclassIds, $coordinatorClassIds, $hodSubjectIds) {
            if ($log->special_role_type === 'class_teacher') {
                return $log->examPaper && in_array($log->examPaper->classSubject->subclassID ?? 0, $classTeacherSubclassIds);
            }
            if ($log->special_role_type === 'coordinator') {
                return $log->examPaper && in_array($log->examPaper->classSubject->classID ?? 0, $coordinatorClassIds);
            }
            if ($log->special_role_type === 'head_of_department') {
                return $log->examPaper && $log->examPaper->classSubject && in_array($log->examPaper->classSubject->subjectID ?? 0, $hodSubjectIds);
            }
            return false;
        })->count();
    $pendingPaperApprovals = $roleCount + $specialCount;

    $canCollectExamAttendance = \App\Models\ExamAttendanceAssignment::where('teacherID', $sessTeacherID)
        ->where('status', true)
        ->exists();

    $permCount = 0;
    if (\Illuminate\Support\Facades\Schema::hasTable('permission_approval_chains') && \Illuminate\Support\Facades\Schema::hasTable('permission_requests')) {
        $navUserID = Session::get('userID');
        $uRoles = $navUserID ? \Illuminate\Support\Facades\DB::table('role_user')->where('user_id', $navUserID)->pluck('role_id')->toArray() : [];
        $query = \App\Models\PermissionRequest::where('schoolID', $sessSchoolID)
            ->where('status', 'pending')
            ->where('current_approval_order', '>', 0);
        $query->whereExists(function ($q) use ($uRoles, $sessUserType) {
            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('permission_approval_chains')
                ->whereColumn('permission_approval_chains.schoolID', 'permission_requests.schoolID')
                ->whereColumn('permission_approval_chains.permission_type', 'permission_requests.requester_type')
                ->whereColumn('permission_approval_chains.approval_order', 'permission_requests.current_approval_order')
                ->where(function ($sub) use ($uRoles, $sessUserType) {
                    $sub->whereIn('role_id', $uRoles);
                    if ($sessUserType === 'Admin') {
                        $sub->orWhere('is_admin', true);
                    }
                });
        });
        $permCount = $query->count();
    }

    $unreadTeacherSuggestions = 0;
    $unreadTeacherIncidents = 0;
    if ($sessTeacherID && $sessSchoolID) {
        $unreadTeacherSuggestions = \App\Models\TeacherFeedback::where('schoolID', $sessSchoolID)
            ->where('teacherID', $sessTeacherID)->where('type', 'suggestion')->where('is_read_by_teacher', false)->count();
        $unreadTeacherIncidents = \App\Models\TeacherFeedback::where('schoolID', $sessSchoolID)
            ->where('teacherID', $sessTeacherID)->where('type', 'incident')->where('is_read_by_teacher', false)->count();
    }

    $hasExaminationPermission = false;
    $hasSubjectPermission = false;
    $hasClassesPermission = false;
    $hasResultPermission = false;
    $hasAttendancePermission = false;
    $hasStudentPermission = false;
    $hasParentPermission = false;
    $hasTimetablePermission = false;
    $hasTeacherPermission = false;
    $hasFeesPermission = false;
    $hasAccommodationPermission = false;
    $hasLibraryPermission = false;
    $hasCalendarPermission = false;
    $hasFingerprintPermission = false;
    $hasTaskPermission = false;
    $hasSmsPermission = false;
    $hasSubjectAnalysisPermission = false;
    $hasPrintingUnitPermission = false;
    $hasWatchmanPermission = false;
    $hasSchoolVisitorsPermission = false;
    $hasSchemeOfWorkPermission = false;
    $hasLessonPlansPermission = false;
    $hasAcademicYearsPermission = false;
    $hasSchoolPermission = false;
    $hasSponsorPermission = false;
    $hasStudentIdCardPermission = false;
    $hasHrPermission = false;
    $hasTeacherDutyPermission = false;
    $hasFeedbackPermission = false;
    $hasStaffFeedbackPermission = false;
    $hasPerformancePermission = false;
    $hasAccountantPermission = false;
    $hasGoalPermission = false;
    $hasDepartmentPermission = false;

    if (isset($teacherPermissionsByCategory) && $teacherPermissionsByCategory->count() > 0) {
        $catMap = [
            'examination' => 'hasExaminationPermission',
            'subject' => 'hasSubjectPermission',
            'classes' => 'hasClassesPermission',
            'result' => 'hasResultPermission',
            'attendance' => 'hasAttendancePermission',
            'student' => 'hasStudentPermission',
            'parent' => 'hasParentPermission',
            'timetable' => 'hasTimetablePermission',
            'teacher' => 'hasTeacherPermission',
            'fees' => 'hasFeesPermission',
            'accommodation' => 'hasAccommodationPermission',
            'library' => 'hasLibraryPermission',
            'calendar' => 'hasCalendarPermission',
            'fingerprint' => 'hasFingerprintPermission',
            'task' => 'hasTaskPermission',
            'sms' => 'hasSmsPermission',
            'subject_analysis' => 'hasSubjectAnalysisPermission',
            'printing_unit' => 'hasPrintingUnitPermission',
            'watchman' => 'hasWatchmanPermission',
            'school_visitors' => 'hasSchoolVisitorsPermission',
            'scheme_of_work' => 'hasSchemeOfWorkPermission',
            'lesson_plans' => 'hasLessonPlansPermission',
            'academic_years' => 'hasAcademicYearsPermission',
            'school' => 'hasSchoolPermission',
            'sponsor' => 'hasSponsorPermission',
            'student_id_card' => 'hasStudentIdCardPermission',
            'hr' => 'hasHrPermission',
            'teacher_duty' => 'hasTeacherDutyPermission',
            'feedback' => 'hasFeedbackPermission',
            'staff_feedback' => 'hasStaffFeedbackPermission',
            'performance' => 'hasPerformancePermission',
            'accountant' => 'hasAccountantPermission',
            'goal' => 'hasGoalPermission',
            'department' => 'hasDepartmentPermission',
        ];
        foreach ($catMap as $cat => $var) {
            if ($teacherPermissionsByCategory->has($cat) && $teacherPermissionsByCategory->get($cat)->count() > 0) {
                $$var = true;
            }
        }
    }

    $hasAnyManagement = $hasExaminationPermission || $hasSubjectPermission || $hasClassesPermission || $hasResultPermission
        || $hasAttendancePermission || $hasStudentPermission || $hasTimetablePermission || $hasFeesPermission
        || $hasAccommodationPermission || $hasLibraryPermission || $hasCalendarPermission || $hasFingerprintPermission
        || $hasTaskPermission || $hasSmsPermission || $hasTeacherPermission || $hasSubjectAnalysisPermission
        || $hasPrintingUnitPermission || $hasWatchmanPermission || $hasSchoolVisitorsPermission || $hasSchemeOfWorkPermission
        || $hasLessonPlansPermission || $hasAcademicYearsPermission || $hasSchoolPermission || $hasSponsorPermission
        || $hasStudentIdCardPermission || $hasHrPermission || $hasTeacherDutyPermission || $hasFeedbackPermission
        || $hasStaffFeedbackPermission || $hasPerformancePermission || $hasAccountantPermission || $hasGoalPermission
        || $hasDepartmentPermission;

    $examsOpen = request()->routeIs(['supervise_exams', 'exam_paper', 'admin.exam_paper_approval', 'admin.add_results', 'teacher.exam_attendance']);
    $teachingOpen = request()->routeIs(['teacher.mySessions', 'teacher.myTasks', 'teacherSubjects', 'teacher.schemeOfWork', 'teacher.lessonPlans', 'teacher.logbook', 'teacher.calendar']);
    $classOpen = request()->routeIs(['AdmitedClasses', 'teacher_time_table']);
    $hrOpen = request()->routeIs(['teacher.permissions', 'admin.hr.permissions.pending_approvals']);
    $goalsOpen = request()->routeIs(['hod.goals.assigned', 'member.goals.myTasks', 'sgpm.tasks.index', 'sgpm.performance.staff']);
    $feedbackOpen = request()->routeIs(['teacher.suggestions', 'teacher.incidents']);
    $mgmtOpen = request()->routeIs([
        'manageExamination', 'manageSubjects', 'admin.subject_analysis', 'manageClasses', 'manageResults',
        'manageAttendance', 'manage_student', 'timeTable', 'manage_fees', 'manage_accomodation', 'manage_library',
        'admin.calendar', 'fingerprint_device_settings', 'taskManagement', 'sms_notification', 'manageTeachers',
        'admin.printing_unit', 'manage_watchman', 'admin.school_visitors', 'admin.schemeOfWork', 'admin.lessonPlans',
        'admin.academicYears', 'school', 'manage_sponsors', 'admin.student_id_cards', 'admin.hr.permission',
        'admin.teacher_duties', 'admin.teacher_duties.report', 'admin.suggestions', 'admin.incidents',
        'admin.staff.suggestions', 'admin.staff.incidents', 'admin.performance', 'accountant.expenses.index',
        'accountant.income.index', 'accountant.budget.index', 'accountant.reports.index', 'admin.goals.index',
        'admin.goals.reports', 'sgpm.departments.index',
    ]);
@endphp

<ul class="menubar" id="teacherMenubar">
    @if($isOnDuty)
        <li class="menu-item px-1 pb-2">
            <div class="sx-duty-chip">
                <i class="fi fi-rr-exclamation"></i> {{ __('teacher.on_duty_week') }}
            </div>
        </li>
    @endif

    <li class="menu-heading"><span class="menu-label">{{ __('teacher.nav.main') }}</span></li>
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teachersDashboard') ? 'active' : '' }}" href="{{ route('teachersDashboard') }}">
            <i class="fi fi-rr-apps"></i><span class="menu-label">{{ __('teacher.nav.dashboard') }}</span>
        </a>
    </li>
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.my_attendance') ? 'active' : '' }}" href="{{ route('teacher.my_attendance') }}">
            <i class="fi fi-rr-calendar-check"></i><span class="menu-label">{{ __('teacher.nav.attendance') }}</span>
        </a>
    </li>
    @if($isStudentRequester)
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.student_permission') ? 'active' : '' }}" href="{{ route('teacher.student_permission') }}">
            <i class="fi fi-rr-user-add"></i><span class="menu-label">{{ __('teacher.nav.student_leave') }}</span>
        </a>
    </li>
    @endif
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.character_assessment') ? 'active' : '' }}" href="{{ route('teacher.character_assessment') }}">
            <i class="fi fi-rr-shield-check"></i><span class="menu-label">{{ __('teacher.nav.character') }}</span>
        </a>
    </li>
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('black_book.report_student') ? 'active' : '' }}" href="{{ route('black_book.report_student') }}">
            <i class="fi fi-rr-triangle-warning"></i><span class="menu-label">{{ __('teacher.nav.report_student') }}</span>
        </a>
    </li>
    @if($teacherHasBlackBookAccess && $sessUserType === 'Teacher')
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('manage_black_book') ? 'active' : '' }}" href="{{ route('manage_black_book') }}">
            <i class="fi fi-rr-book"></i><span class="menu-label">{{ __('teacher.nav.black_book') }}</span>
        </a>
    </li>
    @endif
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.duty_book') ? 'active' : '' }}" href="{{ route('teacher.duty_book') }}">
            <i class="fi fi-rr-notebook"></i><span class="menu-label">{{ __('teacher.nav.duty_book') }}</span>
        </a>
    </li>
    @if($studentPortalEnabled)
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('student_portal.materials') ? 'active' : '' }}" href="{{ route('student_portal.materials') }}">
            <i class="fi fi-rr-graduation-cap"></i><span class="menu-label">{{ __('teacher.nav.learning_portal') }}</span>
        </a>
    </li>
    @endif
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.store_requisitions') ? 'active' : '' }}" href="{{ route('teacher.store_requisitions') }}">
            <i class="fi fi-rr-clipboard"></i><span class="menu-label">{{ __('teacher.nav.requisitions') }}</span>
        </a>
    </li>
    @if($teacherHasInventoryAccess && $sessUserType === 'Teacher')
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('manage_store') ? 'active' : '' }}" href="{{ route('manage_store') }}">
            <i class="fi fi-rr-box"></i><span class="menu-label">{{ __('teacher.nav.inventory') }}</span>
        </a>
    </li>
    @endif

    <li class="menu-heading"><span class="menu-label">{{ __('teacher.nav.teaching') }}</span></li>
    <li class="menu-item menu-arrow {{ $teachingOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $teachingOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-book"></i><span class="menu-label">{{ __('teacher.nav.teaching') }}</span>
        </a>
        <ul class="menu-inner">
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.mySessions') ? 'active' : '' }}" href="{{ route('teacher.mySessions') }}"><span class="menu-label">{{ __('teacher.nav.sessions') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.myTasks') ? 'active' : '' }}" href="{{ route('teacher.myTasks') }}"><span class="menu-label">{{ __('teacher.nav.tasks') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacherSubjects') ? 'active' : '' }}" href="{{ route('teacherSubjects') }}"><span class="menu-label">{{ __('teacher.nav.subjects') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.schemeOfWork') ? 'active' : '' }}" href="{{ route('teacher.schemeOfWork') }}"><span class="menu-label">{{ __('teacher.nav.scheme_of_work') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.lessonPlans') ? 'active' : '' }}" href="{{ route('teacher.lessonPlans') }}"><span class="menu-label">{{ __('teacher.nav.lesson_plans') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.logbook') ? 'active' : '' }}" href="{{ route('teacher.logbook') }}"><span class="menu-label">{{ __('teacher.nav.logbook') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.calendar') ? 'active' : '' }}" href="{{ route('teacher.calendar') }}"><span class="menu-label">{{ __('teacher.nav.calendar') }}</span></a></li>
        </ul>
    </li>

    @if($isNavHOD)
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.departmentLogbook') ? 'active' : '' }}" href="{{ route('teacher.departmentLogbook') }}">
            <i class="fi fi-rr-building"></i><span class="menu-label">{{ __('teacher.nav.dept_logbooks') }}</span>
        </a>
    </li>
    @endif

    <li class="menu-item menu-arrow {{ $examsOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $examsOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-graduation-cap"></i><span class="menu-label">{{ __('teacher.nav.exams') }}</span>
        </a>
        <ul class="menu-inner">
            @if($canAddResultsByAssignment)
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('admin.add_results') ? 'active' : '' }}" href="{{ route('admin.add_results') }}"><span class="menu-label">{{ __('teacher.nav.add_results') }}</span></a></li>
            @endif
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('supervise_exams') ? 'active' : '' }}" href="{{ route('supervise_exams') }}"><span class="menu-label">{{ __('teacher.nav.supervise') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('exam_paper') ? 'active' : '' }}" href="{{ route('exam_paper') }}"><span class="menu-label">{{ __('teacher.nav.exam_papers') }}</span></a></li>
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.exam_paper_approval') ? 'active' : '' }}" href="{{ route('admin.exam_paper_approval') }}">
                    <span class="menu-label">{{ __('teacher.nav.approvals') }}</span>
                    @if($pendingPaperApprovals > 0)<span class="badge bg-danger sx-menu-badge">{{ $pendingPaperApprovals }}</span>@endif
                </a>
            </li>
            @if($canCollectExamAttendance)
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.exam_attendance') ? 'active' : '' }}" href="{{ route('teacher.exam_attendance') }}"><span class="menu-label">{{ __('teacher.nav.attendance') }}</span></a></li>
            @endif
        </ul>
    </li>

    <li class="menu-item menu-arrow {{ $classOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $classOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-users"></i><span class="menu-label">{{ __('teacher.nav.class') }}</span>
        </a>
        <ul class="menu-inner">
            @if(isset($hasAssignedClass) && $hasAssignedClass)
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('AdmitedClasses') ? 'active' : '' }}" href="{{ route('AdmitedClasses') }}"><span class="menu-label">{{ __('teacher.nav.my_class') }}</span></a></li>
            @endif
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher_time_table') ? 'active' : '' }}" href="{{ route('teacher_time_table') }}"><span class="menu-label">{{ __('teacher.nav.timetable') }}</span></a></li>
        </ul>
    </li>

    <li class="menu-heading"><span class="menu-label">{{ __('teacher.nav.hr_goals') }}</span></li>
    <li class="menu-item menu-arrow {{ $hrOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $hrOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-briefcase"></i><span class="menu-label">{{ __('teacher.nav.hr') }}</span>
        </a>
        <ul class="menu-inner">
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('teacher.permissions') ? 'active' : '' }}" href="{{ route('teacher.permissions') }}"><span class="menu-label">{{ __('teacher.nav.my_permission') }}</span></a></li>
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.hr.permissions.pending_approvals') ? 'active' : '' }}" href="{{ route('admin.hr.permissions.pending_approvals') }}">
                    <span class="menu-label">{{ __('teacher.nav.approvals') }}</span>
                    @if($permCount > 0)<span class="badge bg-danger sx-menu-badge">{{ $permCount }}</span>@endif
                </a>
            </li>
        </ul>
    </li>

    @if($isHOD ?? $isNavHOD)
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('hod.goals.assigned') ? 'active' : '' }}" href="{{ route('hod.goals.assigned') }}">
            <i class="fi fi-rr-users"></i><span class="menu-label">{{ __('teacher.nav.team_goals') }}</span>
        </a>
    </li>
    @endif

    <li class="menu-item menu-arrow {{ $goalsOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $goalsOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-star"></i><span class="menu-label">{{ __('teacher.nav.goals') }}</span>
        </a>
        <ul class="menu-inner">
            <li class="menu-item"><a class="menu-link" href="{{ route('member.goals.myTasks', ['type' => 'direct']) }}"><span class="menu-label">{{ __('teacher.nav.direct_goals') }}</span></a></li>
            <li class="menu-item"><a class="menu-link" href="{{ route('member.goals.myTasks') }}"><span class="menu-label">{{ __('teacher.nav.assigned_tasks') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('sgpm.tasks.index') ? 'active' : '' }}" href="{{ route('sgpm.tasks.index') }}"><span class="menu-label">{{ __('teacher.nav.my_tasks') }}</span></a></li>
            <li class="menu-item"><a class="menu-link {{ request()->routeIs('sgpm.performance.staff') ? 'active' : '' }}" href="{{ route('sgpm.performance.staff') }}"><span class="menu-label">{{ __('teacher.nav.scorecard') }}</span></a></li>
        </ul>
    </li>

    <li class="menu-item menu-arrow {{ $feedbackOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $feedbackOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-comment"></i><span class="menu-label">{{ __('teacher.nav.feedback') }}</span>
        </a>
        <ul class="menu-inner">
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('teacher.suggestions') ? 'active' : '' }}" href="{{ route('teacher.suggestions') }}">
                    <span class="menu-label">{{ __('teacher.nav.suggestions') }}</span>
                    @if($unreadTeacherSuggestions > 0)<span class="badge bg-danger sx-menu-badge">{{ $unreadTeacherSuggestions }}</span>@endif
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('teacher.incidents') ? 'active' : '' }}" href="{{ route('teacher.incidents') }}">
                    <span class="menu-label">{{ __('teacher.nav.incidents') }}</span>
                    @if($unreadTeacherIncidents > 0)<span class="badge bg-danger sx-menu-badge">{{ $unreadTeacherIncidents }}</span>@endif
                </a>
            </li>
        </ul>
    </li>

    @if($hasAnyManagement)
    <li class="menu-heading"><span class="menu-label">{{ __('teacher.nav.admin') }}</span></li>
    <li class="menu-item menu-arrow {{ $mgmtOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link {{ $mgmtOpen ? 'open active' : '' }}">
            <i class="fi fi-rr-settings"></i><span class="menu-label">{{ __('teacher.nav.management') }}</span>
        </a>
        <ul class="menu-inner">
            @if($hasExaminationPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manageExamination') }}"><span class="menu-label">{{ __('teacher.nav.examinations') }}</span></a></li>@endif
            @if($hasSubjectPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manageSubjects') }}"><span class="menu-label">{{ __('teacher.nav.subjects') }}</span></a></li>@endif
            @if($hasSubjectAnalysisPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.subject_analysis') }}"><span class="menu-label">{{ __('teacher.nav.topic_analysis') }}</span></a></li>@endif
            @if($hasClassesPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manageClasses') }}"><span class="menu-label">{{ __('teacher.nav.classes') }}</span></a></li>@endif
            @if($hasResultPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manageResults') }}"><span class="menu-label">{{ __('teacher.nav.results') }}</span></a></li>@endif
            @if($hasAttendancePermission)<li class="menu-item"><a class="menu-link" href="{{ route('manageAttendance') }}"><span class="menu-label">{{ __('teacher.nav.attendance') }}</span></a></li>@endif
            @if($hasStudentPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manage_student') }}"><span class="menu-label">{{ __('teacher.nav.students') }}</span></a></li>@endif
            @if($hasTimetablePermission)<li class="menu-item"><a class="menu-link" href="{{ route('timeTable') }}"><span class="menu-label">{{ __('teacher.nav.timetable') }}</span></a></li>@endif
            @if($hasFeesPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manage_fees') }}"><span class="menu-label">{{ __('teacher.nav.fees') }}</span></a></li>@endif
            @if($hasAccommodationPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manage_accomodation') }}"><span class="menu-label">{{ __('teacher.nav.hostel') }}</span></a></li>@endif
            @if($hasLibraryPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manage_library') }}"><span class="menu-label">{{ __('teacher.nav.library') }}</span></a></li>@endif
            @if($hasCalendarPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.calendar') }}"><span class="menu-label">{{ __('teacher.nav.calendar') }}</span></a></li>@endif
            @if($hasFingerprintPermission)<li class="menu-item"><a class="menu-link" href="{{ route('fingerprint_device_settings') }}"><span class="menu-label">{{ __('teacher.nav.fingerprint') }}</span></a></li>@endif
            @if($hasTaskPermission)<li class="menu-item"><a class="menu-link" href="{{ route('taskManagement') }}"><span class="menu-label">{{ __('teacher.nav.tasks') }}</span></a></li>@endif
            @if($hasSmsPermission)<li class="menu-item"><a class="menu-link" href="{{ route('sms_notification') }}"><span class="menu-label">{{ __('teacher.nav.sms') }}</span></a></li>@endif
            @if($hasTeacherPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manageTeachers') }}"><span class="menu-label">{{ __('teacher.nav.staff') }}</span></a></li>@endif
            @if($hasPrintingUnitPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.printing_unit') }}"><span class="menu-label">{{ __('teacher.nav.printing') }}</span></a></li>@endif
            @if($hasWatchmanPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manage_watchman') }}"><span class="menu-label">{{ __('teacher.nav.watchman') }}</span></a></li>@endif
            @if($hasSchoolVisitorsPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.school_visitors') }}"><span class="menu-label">{{ __('teacher.nav.visitors') }}</span></a></li>@endif
            @if($hasSchemeOfWorkPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.schemeOfWork') }}"><span class="menu-label">{{ __('teacher.nav.schemes') }}</span></a></li>@endif
            @if($hasLessonPlansPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.lessonPlans') }}"><span class="menu-label">{{ __('teacher.nav.lesson_plans') }}</span></a></li>@endif
            @if($hasAcademicYearsPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.academicYears') }}"><span class="menu-label">{{ __('teacher.nav.years') }}</span></a></li>@endif
            @if($hasSchoolPermission)<li class="menu-item"><a class="menu-link" href="{{ route('school') }}"><span class="menu-label">{{ __('teacher.nav.school') }}</span></a></li>@endif
            @if($hasSponsorPermission)<li class="menu-item"><a class="menu-link" href="{{ route('manage_sponsors') }}"><span class="menu-label">{{ __('teacher.nav.sponsors') }}</span></a></li>@endif
            @if($hasStudentIdCardPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.student_id_cards') }}"><span class="menu-label">{{ __('teacher.nav.id_cards') }}</span></a></li>@endif
            @if($hasHrPermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.hr.permission') }}"><span class="menu-label">{{ __('teacher.nav.hr_setup') }}</span></a></li>@endif
            @if($hasTeacherDutyPermission)
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.teacher_duties') }}"><span class="menu-label">{{ __('teacher.nav.duties') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.teacher_duties.report') }}"><span class="menu-label">{{ __('teacher.nav.duty_reports') }}</span></a></li>
            @endif
            @if($hasFeedbackPermission)
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.suggestions') }}"><span class="menu-label">{{ __('teacher.nav.suggestions') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.incidents') }}"><span class="menu-label">{{ __('teacher.nav.incidents') }}</span></a></li>
            @endif
            @if($hasStaffFeedbackPermission)
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.staff.suggestions') }}"><span class="menu-label">{{ __('teacher.nav.staff_suggestions') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.staff.incidents') }}"><span class="menu-label">{{ __('teacher.nav.staff_incidents') }}</span></a></li>
            @endif
            @if($hasPerformancePermission)<li class="menu-item"><a class="menu-link" href="{{ route('admin.performance') }}"><span class="menu-label">{{ __('teacher.nav.performance') }}</span></a></li>@endif
            @if($hasAccountantPermission)
                <li class="menu-item"><a class="menu-link" href="{{ route('accountant.expenses.index') }}"><span class="menu-label">{{ __('teacher.nav.expenses') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('accountant.income.index') }}"><span class="menu-label">{{ __('teacher.nav.income') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('accountant.budget.index') }}"><span class="menu-label">{{ __('teacher.nav.budget') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('accountant.reports.index') }}"><span class="menu-label">{{ __('teacher.nav.finance') }}</span></a></li>
            @endif
            @if($hasGoalPermission)
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.goals.index') }}"><span class="menu-label">{{ __('teacher.nav.goals') }}</span></a></li>
                <li class="menu-item"><a class="menu-link" href="{{ route('admin.goals.reports') }}"><span class="menu-label">{{ __('teacher.nav.goal_reports') }}</span></a></li>
            @endif
            @if($hasDepartmentPermission)<li class="menu-item"><a class="menu-link" href="{{ route('sgpm.departments.index') }}"><span class="menu-label">{{ __('teacher.nav.departments') }}</span></a></li>@endif
        </ul>
    </li>
    @endif

    <li class="menu-heading"><span class="menu-label">{{ __('teacher.nav.account') }}</span></li>
    <li class="menu-item">
        <a class="menu-link {{ request()->routeIs('teacher.profile') ? 'active' : '' }}" href="{{ route('teacher.profile') }}">
            <i class="fi fi-rr-user"></i><span class="menu-label">{{ __('teacher.nav.profile') }}</span>
        </a>
    </li>
    <li class="menu-item">
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start">
                <i class="fi fi-sr-exit"></i><span class="menu-label">{{ __('teacher.nav.logout') }}</span>
            </button>
        </form>
    </li>
</ul>
