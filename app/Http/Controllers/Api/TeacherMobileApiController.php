<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TeachersController;
use App\Models\ClassModel;
use App\Models\Department;
use App\Models\ExamAttendanceAssignment;
use App\Models\PaperApprovalLog;
use App\Models\PermissionRequest;
use App\Models\School;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Models\TeacherDuty;
use App\Models\TeacherFeedback;
use App\Models\TeacherResultAssignment;
use App\Models\TeacherResultPermission;
use App\Services\BlackBookAccessService;
use App\Services\StoreAccessService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token-authenticated Teacher API (/api/v1/teacher/*).
 *
 * Every route is bound to the existing web/teacher controller method through route defaults
 * (see routes/api.php). The mobile.auth:teacher middleware has already verified the bearer token
 * and filled the session + teacherID/schoolID/user_id/user_type from the token, so the same
 * business rules, validation and permission checks as the website apply.
 *
 * - Methods that already return JSON are passed through unchanged.
 * - Methods that render a Blade page return the data handed to that page (no HTML) under `data`.
 * - Methods that redirect with a flash message are turned into a JSON success/error response.
 */
class TeacherMobileApiController extends Controller
{
    /** Keys that are never part of a screen's data. */
    private const HIDDEN_VIEW_KEYS = ['errors', '__env', 'app', 'obLevel', '__data', 'slot'];

    public function capabilities(Request $request): JsonResponse
    {
        $teacher = $request->attributes->get('mobile_teacher');

        return response()->json([
            'success' => true,
            'data' => $this->capabilitiesFor($teacher),
        ]);
    }

    /**
     * Generic bridge: runs the web controller method named in the route defaults.
     */
    public function bridge(Request $request)
    {
        $route = $request->route();
        $target = $route->defaults['web'] ?? null;
        if (!is_array($target) || count($target) < 2) {
            abort(404);
        }

        [$class, $method] = $target;
        $only = $route->defaults['only'] ?? null;

        // Management modules: same rule as the sidebar (teacher must hold a permission in that category).
        $category = $route->defaults['category'] ?? null;
        if ($category !== null && !$this->hasPermissionCategory($request, (string) $category)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this module.',
            ], 403);
        }

        return $this->normalize($this->callWeb($class, $method, $route->parameters()), $only);
    }

    /**
     * True when one of the teacher's roles grants a permission in the given category
     * (the same test the website sidebar uses for its Management menu).
     */
    public function hasPermissionCategory(Request $request, string $category): bool
    {
        $teacher = $request->attributes->get('mobile_teacher');
        if (!$teacher) {
            return false;
        }

        $roleIds = DB::table('role_user')->where('teacher_id', $teacher->id)->pluck('role_id');
        if ($roleIds->isEmpty()) {
            return false;
        }

        return DB::table('permissions')
            ->whereIn('role_id', $roleIds)
            ->where('permission_category', $category)
            ->exists();
    }

    /**
     * Runs a web controller method and returns its raw result (View, JSON, redirect, ...).
     */
    private function callWeb(string $class, string $method, array $params = [])
    {
        try {
            return app()->call([app($class), $method], $params);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            // Web code sometimes does redirect()->...->throwResponse().
            return $e->getResponse();
        }
    }

    /**
     * GET /teacher/dashboard
     *
     * Runs the website's teachersDashboard() and returns the same data the dashboard page shows:
     * stat cards, today's attendance, upcoming session, TANGE completeness, approvals, notifications.
     */
    public function dashboard(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'teachersDashboard');
        if (!$result instanceof View) {
            return $this->normalize($result);
        }

        $d = $result->getData();
        $teacher = $request->attributes->get('mobile_teacher');
        $stats = $d['dashboardStats'] ?? [];
        $hasAssignedClass = (bool) ($d['hasAssignedClass'] ?? false);
        $att = $d['teacherTodayAttendance'] ?? [];
        $attStats = $att['statistics'] ?? [];
        $tangeTeacher = $d['tangeTeacher'] ?? null;

        $notifications = collect($d['teacherNotifications'] ?? [])->values();
        $sessionAlert = $notifications->first(fn ($n) => ($n['type'] ?? null) === 'session_time');

        $statCards = [
            ['key' => 'subjects', 'label' => __('teacher.nav.subjects'), 'value' => (int) ($stats['subjects_count'] ?? 0), 'api_path' => '/teacher/subjects', 'note' => __('teacher.dash.subjects_teaching')],
            ['key' => 'classes', 'label' => __('teacher.nav.classes'), 'value' => (int) ($stats['classes_count'] ?? 0), 'api_path' => $hasAssignedClass ? '/teacher/my-class' : null, 'note' => $hasAssignedClass ? __('teacher.dash.subclasses_sessions') : __('teacher.dash.no_class_assigned')],
            ['key' => 'sessions_week', 'label' => __('teacher.dash.sessions_week'), 'value' => (int) ($stats['sessions_per_week'] ?? 0), 'api_path' => '/teacher/sessions', 'note' => __('teacher.dash.mon_fri')],
            ['key' => 'lesson_plans', 'label' => __('teacher.nav.lesson_plans'), 'value' => (int) ($stats['lesson_plans_count'] ?? 0), 'api_path' => '/teacher/lesson-plans', 'note' => __('teacher.dash.sent_to_admin', ['count' => (int) ($stats['lesson_plans_sent_count'] ?? 0)])],
        ];
        if ((int) ($stats['coordinator_classes_count'] ?? 0) > 0) {
            $statCards[] = ['key' => 'coordinator', 'label' => __('teacher.dash.coordinator'), 'value' => (int) $stats['coordinator_classes_count'], 'api_path' => '/teacher/my-class', 'note' => __('teacher.dash.main_classes')];
        }
        $statCards[] = ['key' => 'sessions_year', 'label' => __('teacher.dash.sessions_year'), 'value' => (int) ($stats['sessions_per_year'] ?? 0), 'api_path' => null, 'note' => __('teacher.dash.excluding_holidays')];
        $statCards[] = ['key' => 'sessions_entered', 'label' => __('teacher.dash.sessions_entered'), 'value' => (int) ($stats['approved_sessions_count'] ?? 0), 'api_path' => '/teacher/sessions', 'note' => __('teacher.dash.with_approved_tasks')];
        $statCards[] = ['key' => 'scheme_of_work', 'label' => __('teacher.nav.scheme_of_work'), 'value' => (int) ($stats['scheme_of_work_count'] ?? 0), 'api_path' => '/teacher/scheme-of-work', 'note' => __('teacher.dash.current_year')];

        $status = $attStats['day_status'] ?? 'Not Available';
        $attendanceToday = [
            'status' => $status,
            'label' => $status,
            'check_in_time' => $attStats['check_in_time'] ?? null,
            'check_out_time' => $attStats['check_out_time'] ?? null,
            'official_start' => $att['work_start_time'] ?? null,
            'official_end' => $att['work_end_time'] ?? null,
        ];

        $tange = $tangeTeacher ? [
            'completion_percent' => (int) ($tangeTeacher->tange_completion_percent ?? 0),
            'is_complete' => (int) ($tangeTeacher->tange_completion_percent ?? 0) >= 100,
            'missing_fields' => $this->decodeJsonField($tangeTeacher->tange_missing_fields ?? null),
            'retirement_alert' => $this->decodeJsonField($tangeTeacher->retirement_alert ?? null),
            'api_path' => '/teacher/profile',
            'pdf_path' => '/teacher/profile/pdf',
        ] : null;

        $approvalRow = fn ($a) => [
            'examID' => $a->examID ?? null,
            'exam_name' => $a->examination->exam_name ?? 'N/A',
            'role' => $a->role->name ?? $a->role->role_name ?? 'N/A',
            'approval_order' => $a->approval_order ?? null,
            'status' => $a->status ?? null,
            'rejection_reason' => $a->rejection_reason ?? null,
        ];

        $approvals = [
            'pending' => collect($d['pendingApprovals'] ?? [])->map($approvalRow)->values(),
            'special' => collect($d['specialRoleApprovals'] ?? [])->map(fn ($s) => $approvalRow($s['approval']) + [
                'type' => $s['type'] ?? null,
                'exam_name' => $s['exam']->exam_name ?? ($s['approval']->examination->exam_name ?? 'N/A'),
            ])->values(),
            'waiting' => collect($d['waitingApprovals'] ?? [])->map(fn ($w) => $approvalRow($w['approval']) + [
                'pending_count' => (int) ($w['pending_count'] ?? 0),
                'current_approver' => isset($w['current_approver']) && $w['current_approver'] ? [
                    'role' => $w['current_approver']->role->name ?? $w['current_approver']->role->role_name ?? null,
                    'approval_order' => $w['current_approver']->approval_order ?? null,
                    'status' => $w['current_approver']->status ?? null,
                ] : null,
            ])->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'greeting' => [
                    'name' => trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')),
                    'date' => now()->translatedFormat('l, d M Y'),
                ],
                'is_on_duty' => (bool) ($d['isOnDuty'] ?? false),
                'session_alert' => $sessionAlert,
                'upcoming_session' => $d['upcomingSession'] ?? null,
                'stat_cards' => $statCards,
                'attendance_today' => $attendanceToday,
                'tange' => $tange,
                'approvals' => $approvals,
                'rejection_notifications' => collect($d['rejectionNotifications'] ?? [])->values(),
                'notifications' => $notifications,
                'teaching_subjects' => collect($stats['teaching_subjects'] ?? [])->values(),
                'has_assigned_class' => $hasAssignedClass,
            ],
        ]);
    }

    private function decodeJsonField($value)
    {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value ?: null;
    }

    private function normalize($result, ?array $only = null)
    {
        if ($result instanceof View) {
            return response()->json([
                'success' => true,
                'data' => $this->viewData($result, $only),
            ]);
        }

        if ($result instanceof RedirectResponse) {
            $error = Session::get('error');
            $errors = Session::get('errors');
            $message = Session::get('success') ?? Session::get('message') ?? Session::get('status');

            if ($error || ($errors && method_exists($errors, 'any') && $errors->any())) {
                // Web code sends "not allowed" users to the login route with an error; that is a 403, not a bad request.
                $path = (string) parse_url($result->getTargetUrl(), PHP_URL_PATH);
                $status = str_ends_with($path, '/login') ? 403 : 422;

                return response()->json([
                    'success' => false,
                    'message' => $error ?: ($errors ? $errors->first() : 'Request failed.'),
                    'errors' => $errors && method_exists($errors, 'toArray') ? $errors->toArray() : null,
                ], $status);
            }

            return response()->json([
                'success' => true,
                'message' => $message ?: 'Done.',
            ]);
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result) || $result instanceof \Illuminate\Contracts\Support\Arrayable) {
            return response()->json(['success' => true, 'data' => $result]);
        }

        return $result;
    }

    private function viewData(View $view, ?array $only): array
    {
        $data = $view->getData();

        foreach (self::HIDDEN_VIEW_KEYS as $key) {
            unset($data[$key]);
        }

        if ($only) {
            $data = array_intersect_key($data, array_flip($only));
        }

        return json_decode(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE), true) ?? [];
    }

    public function capabilitiesPayload(Teacher $teacher): array
    {
        return $this->capabilitiesFor($teacher);
    }

    private function capabilitiesFor(Teacher $teacher): array
    {
        $teacherID = (int) $teacher->id;
        $schoolID = (int) $teacher->schoolID;
        $userID = (int) Session::get('userID');
        $today = Carbon::today();

        $roles = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.teacher_id', $teacherID)
            ->select('roles.id', 'roles.role_name')
            ->get();
        $roleIds = $roles->pluck('id')->toArray();

        $permissionRows = !empty($roleIds)
            ? DB::table('permissions')->whereIn('role_id', $roleIds)->select('name', 'permission_category')->get()
            : collect();
        $permissionsByCategory = $permissionRows->groupBy('permission_category')
            ->map(fn ($rows) => $rows->pluck('name')->unique()->values());

        $departments = Department::where('head_teacherID', $teacherID)->get();
        $classTeacherSubclassIds = Subclass::where('teacherID', $teacherID)->pluck('subclassID')->toArray();
        $coordinatorClassIds = ClassModel::where('teacherID', $teacherID)->pluck('classID')->toArray();
        $hodSubjectIds = $departments->isNotEmpty()
            ? DB::table('department_subjects')->whereIn('departmentID', $departments->pluck('departmentID'))->pluck('subjectID')->toArray()
            : [];

        $globalResultPermission = TeacherResultPermission::where('schoolID', $schoolID)->first();
        $globalEnabled = $globalResultPermission ? (bool) $globalResultPermission->result_button_status : true;
        $canAddResults = $globalEnabled && TeacherResultAssignment::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)->where('status', true)->exists();

        $pendingPaperApprovals = 0;
        try {
            $schoolPaperScope = function ($q) use ($schoolID) {
                $q->whereHas('examPaper.examination', fn ($eq) => $eq->where('schoolID', $schoolID));
            };
            $roleCount = !empty($roleIds)
                ? PaperApprovalLog::whereIn('role_id', $roleIds)->where('status', 'pending')
                    ->where($schoolPaperScope)->count()
                : 0;
            $specialCount = PaperApprovalLog::where('status', 'pending')
                ->whereNotNull('special_role_type')
                ->where($schoolPaperScope)
                ->with(['examPaper.classSubject'])
                ->get()
                ->filter(function ($log) use ($classTeacherSubclassIds, $coordinatorClassIds, $hodSubjectIds) {
                    if ($log->special_role_type === 'class_teacher') {
                        return $log->examPaper && in_array($log->examPaper->classSubject->subclassID ?? 0, $classTeacherSubclassIds);
                    }
                    if ($log->special_role_type === 'coordinator') {
                        return $log->examPaper && in_array($log->examPaper->classSubject->classID ?? 0, $coordinatorClassIds);
                    }
                    if ($log->special_role_type === 'head_of_department') {
                        return $log->examPaper && $log->examPaper->classSubject
                            && in_array($log->examPaper->classSubject->subjectID ?? 0, $hodSubjectIds);
                    }

                    return false;
                })->count();
            $pendingPaperApprovals = $roleCount + $specialCount;
        } catch (\Throwable $e) {
            $pendingPaperApprovals = 0;
        }

        // Result approvals waiting for this teacher (Academic / roles / special) — dashboard cards.
        $pendingResultApprovals = 0;
        try {
            $schoolExamIDs = \App\Models\Examination::where('schoolID', $schoolID)->pluck('examID');
            if (!empty($roleIds)) {
                $pendingResultApprovals += \App\Models\ResultApproval::whereIn('examID', $schoolExamIDs)
                    ->whereIn('role_id', $roleIds)
                    ->whereIn('status', ['pending', 'rejected'])
                    ->count();
            }
            if (!empty($classTeacherSubclassIds)) {
                $pendingResultApprovals += \App\Models\ResultApproval::whereIn('examID', $schoolExamIDs)
                    ->where('special_role_type', 'class_teacher')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->whereHas('classTeacherApprovals', function ($q) use ($classTeacherSubclassIds) {
                        $q->whereIn('subclassID', $classTeacherSubclassIds)
                            ->whereIn('status', ['pending', 'rejected']);
                    })->count();
            }
            if (!empty($coordinatorClassIds)) {
                $pendingResultApprovals += \App\Models\ResultApproval::whereIn('examID', $schoolExamIDs)
                    ->where('special_role_type', 'coordinator')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->whereHas('coordinatorApprovals', function ($q) use ($coordinatorClassIds) {
                        $q->whereIn('mainclassID', $coordinatorClassIds)
                            ->whereIn('status', ['pending', 'rejected']);
                    })->count();
            }
        } catch (\Throwable $e) {
            $pendingResultApprovals = 0;
        }

        $pendingPermissionApprovals = 0;
        if (Schema::hasTable('permission_approval_chains') && Schema::hasTable('permission_requests')) {
            $userRoleIds = $userID ? DB::table('role_user')->where('user_id', $userID)->pluck('role_id')->toArray() : [];
            $pendingPermissionApprovals = PermissionRequest::where('schoolID', $schoolID)
                ->where('status', 'pending')
                ->where('current_approval_order', '>', 0)
                ->whereExists(function ($q) use ($userRoleIds) {
                    $q->select(DB::raw(1))
                        ->from('permission_approval_chains')
                        ->whereColumn('permission_approval_chains.schoolID', 'permission_requests.schoolID')
                        ->whereColumn('permission_approval_chains.permission_type', 'permission_requests.requester_type')
                        ->whereColumn('permission_approval_chains.approval_order', 'permission_requests.current_approval_order')
                        ->whereIn('role_id', $userRoleIds);
                })->count();
        }

        $unread = fn (string $type) => TeacherFeedback::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)->where('type', $type)->where('is_read_by_teacher', false)->count();

        $managementCategories = $permissionsByCategory->keys()->filter()->values();

        return [
            'teacher' => [
                'id' => $teacherID,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'full_name' => trim($teacher->first_name . ' ' . $teacher->last_name),
                'employee_number' => $teacher->employee_number,
                'schoolID' => $schoolID,
                'photo' => $teacher->image ? url('userImages/' . $teacher->image) : null,
            ],
            'school' => [
                'schoolID' => $schoolID,
                'name' => School::where('schoolID', $schoolID)->value('school_name'),
            ],
            'roles' => $roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->role_name])->values(),
            'is_hod' => $departments->isNotEmpty(),
            'hod_departments' => $departments->map(fn ($d) => ['departmentID' => $d->departmentID, 'name' => $d->department_name])->values(),
            'is_class_teacher' => !empty($classTeacherSubclassIds),
            'is_coordinator' => !empty($coordinatorClassIds),
            'has_assigned_class' => !empty($classTeacherSubclassIds) || !empty($coordinatorClassIds),
            'is_on_duty' => TeacherDuty::where('schoolID', $schoolID)->where('teacherID', $teacherID)
                ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->exists(),
            'is_student_requester' => DB::table('student_permission_requesters')
                ->where('schoolID', $schoolID)->where('user_id', $teacherID)
                ->where('user_type', 'teacher')->where('is_active', true)->exists(),
            'can_add_results' => $canAddResults,
            'can_collect_exam_attendance' => ExamAttendanceAssignment::where('teacherID', $teacherID)->where('status', true)->exists(),
            'has_black_book_access' => (bool) app(BlackBookAccessService::class)->currentUserHasManageAccess($schoolID),
            'has_inventory_access' => (bool) app(StoreAccessService::class)->currentUserHasStoreAccess($schoolID),
            'student_portal_enabled' => (bool) School::where('schoolID', $schoolID)->value('student_portal_enabled'),
            'badges' => [
                'pending_paper_approvals' => $pendingPaperApprovals,
                'pending_result_approvals' => $pendingResultApprovals,
                'pending_permission_approvals' => $pendingPermissionApprovals,
                'unread_suggestions' => $unread('suggestion'),
                'unread_incidents' => $unread('incident'),
            ],
            // Delegated management modules this teacher has been given through roles (web "Management" menu).
            'management_permission_categories' => $managementCategories,
            'permissions_by_category' => $permissionsByCategory,
        ];
    }
}
