<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoalManagementController;
use App\Http\Controllers\SgpmPerformanceController;
use App\Http\Controllers\SgpmTaskController;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hubs for HR & Goals sidebar:
 * Team Goals (HOD), Direct Goals, Assigned Tasks, SGPM My Tasks, Scorecard.
 * Write/AJAX actions stay as plain bridges in routes/mobile_teacher.php.
 */
class TeacherGoalsMobileApiController extends Controller
{
    /** GET /goals — module index (submenu flags). */
    public function index(Request $request)
    {
        $caps = app(TeacherMobileApiController::class)->capabilities($request)->getData(true)['data'] ?? [];
        $isHod = (bool) ($caps['is_hod'] ?? false);

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Goals',
                'is_hod' => $isHod,
                'submenus' => [
                    [
                        'key' => 'team_goals',
                        'title' => 'Team Goals',
                        'path' => '/goals/hod/assigned',
                        'path_alias' => '/goals/team',
                        'web_path' => '/goals/hod/assigned',
                        'visible' => $isHod,
                    ],
                    [
                        'key' => 'direct_goals',
                        'title' => 'Direct Goals',
                        'path' => '/goals/direct',
                        'visible' => true,
                    ],
                    [
                        'key' => 'assigned_tasks',
                        'title' => 'Assigned Tasks',
                        'path' => '/goals/assigned',
                        'visible' => true,
                    ],
                    [
                        'key' => 'sgpm_tasks',
                        'title' => 'My Tasks',
                        'path' => '/sgpm/tasks',
                        'visible' => true,
                    ],
                    [
                        'key' => 'scorecard',
                        'title' => 'Scorecard',
                        'path' => '/performance/staff',
                        'visible' => true,
                    ],
                ],
            ],
        ]);
    }

    /** GET /goals/team — HOD Team Goals (web: /goals/hod/assigned). */
    public function team()
    {
        $result = $this->callWeb(GoalManagementController::class, 'hodAssignedTasks');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'You are not assigned as HOD of any department.');
        }

        $d = $result->getData();
        $department = $d['department'] ?? null;
        $tasks = collect($d['tasks'] ?? []);

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Team Goals',
                'web_path' => '/goals/hod/assigned',
                'api_paths' => ['/goals/hod/assigned', '/goals/team'],
                'is_hod' => true,
                'department' => $department ? [
                    'departmentID' => (int) ($department->departmentID ?? 0),
                    'department_name' => $department->department_name ?? null,
                ] : null,
                'tasks' => $tasks->map(fn ($t) => $this->shapeHodTask($t))->values(),
                'actions' => [
                    'delegate' => 'POST /goals/hod/assign-member',
                    'department_members' => 'GET /goals/hod/department-members',
                    'member_tasks' => 'GET /goals/hod/member-tasks/{parent_task_id}',
                    'progress' => 'GET /goals/hod/tasks/{id}',
                    'progress_alias' => 'GET /goals/team/{id}',
                ],
            ],
        ]);
    }

    /** GET /goals/team/{id} — HOD task progress / review (web: /goals/hod/view-task/{id}). */
    public function teamTask($id)
    {
        $result = $this->callWeb(GoalManagementController::class, 'showHODTaskDetails', ['id' => $id]);
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Task not found.');
        }

        $task = $result->getData()['task'] ?? null;
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        $memberTasks = collect($task->memberTasks ?? [])->map(function ($m) {
            $subtasks = collect($m->subtasks ?? []);
            $pendingReview = $subtasks->whereIn('status', ['Submitted', 'Approved'])->count();

            return [
                'member_task_id' => (int) $m->id,
                'task_name' => $m->task_name ?? null,
                'description' => $m->description ?? null,
                'member_id' => (int) ($m->member_id ?? 0),
                'member_type' => $m->member_type ?? null,
                'member_name' => trim((string) ($m->member_name ?? '')),
                'weight' => (float) ($m->weight ?? 0),
                'progress' => (float) ($m->progress ?? 0),
                'status' => $m->status ?? null,
                'subtask_count' => $subtasks->count(),
                'pending_review_count' => $pendingReview,
                'can_review' => $pendingReview > 0,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Task Progress',
                'task' => [
                    'id' => (int) $task->id,
                    'task_name' => $task->task_name ?? null,
                    'description' => $task->description ?? null,
                    'weight' => (float) ($task->weight ?? 0),
                    'goal_name' => $task->goal->goal_name ?? null,
                    'deadline' => $this->fmtDate($task->goal->deadline ?? null),
                    'overall_progress_percent' => (float) ($task->overall_progress_percent ?? $task->progress ?? 0),
                    'status' => $task->status ?? null,
                ],
                'member_tasks' => $memberTasks,
                'actions' => [
                    'subtasks' => 'GET /goals/subtasks/{member_task_id}?role=hod',
                    'approve_subtask' => 'POST /goals/review/approve-subtask',
                    'reset_marks' => 'POST /goals/review/reset-marks/{id}',
                ],
            ],
        ]);
    }

    /** GET /goals/direct — Direct Goals (web: /goals/my-tasks?type=direct). */
    public function direct(Request $request)
    {
        $request->query->set('type', 'direct');

        return $this->myTasks($request);
    }

    /** GET /goals/assigned — Assigned Tasks (delegated + direct). */
    public function assigned(Request $request)
    {
        $request->query->remove('type');

        return $this->myTasks($request);
    }

    /**
     * GET /goals/my-tasks — same as web; use ?type=direct for Direct Goals.
     * Prefer /goals/direct and /goals/assigned hubs.
     */
    public function myTasks(Request $request)
    {
        $result = $this->callWeb(GoalManagementController::class, 'memberTasks');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Goals are not available.');
        }

        $d = $result->getData();
        $filterType = $d['filterType'] ?? $request->query('type');
        $isDirectOnly = $filterType === 'direct';

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $isDirectOnly ? 'Direct Goals' : 'Assigned Tasks',
                'filter_type' => $isDirectOnly ? 'direct' : 'all',
                'tasks' => collect($d['myTasks'] ?? [])->map(fn ($t) => $this->shapeMemberTask($t))->values(),
            ],
        ]);
    }

    /** GET /sgpm/tasks — SGPM My Tasks (+ HOD assign/review helpers). */
    public function sgpmTasks()
    {
        $result = $this->callWeb(SgpmTaskController::class, 'index');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'SGPM tasks are not available.');
        }

        $d = $result->getData();
        $isHod = (bool) ($d['isHod'] ?? false);
        $userId = (int) (Session::get('userID') ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'My Tasks',
                'is_hod' => $isHod,
                'user_type' => $d['userType'] ?? Session::get('user_type'),
                'tasks' => collect($d['tasks'] ?? [])->map(fn ($t) => $this->shapeSgpmTask($t, $userId, $isHod))->values(),
                'action_plans' => $isHod
                    ? collect($d['actionPlans'] ?? [])->map(fn ($p) => [
                        'action_planID' => (int) ($p->action_planID ?? $p->id ?? 0),
                        'title' => $p->title ?? $p->action_plan_name ?? $p->name ?? null,
                        'description' => $p->description ?? null,
                    ])->values()
                    : [],
                'assignees' => $isHod
                    ? collect($d['users'] ?? [])->map(function ($u) {
                        $name = trim((string) ($u->name ?? ''));
                        if ($name === '') {
                            $name = trim(($u->teacher->first_name ?? '').' '.($u->teacher->last_name ?? ''));
                        }
                        if ($name === '') {
                            $name = trim(($u->staff->first_name ?? '').' '.($u->staff->last_name ?? ''));
                        }

                        return [
                            'userID' => (int) $u->id,
                            'name' => $name !== '' ? $name : ('User #'.$u->id),
                        ];
                    })->values()
                    : [],
                'actions' => [
                    'assign_task' => $isHod ? 'POST /sgpm/tasks' : null,
                    'create_subtask' => 'POST /sgpm/subtasks',
                    'submit_subtask' => 'POST /sgpm/subtasks/{id}/submit',
                    'submit_progress' => 'POST /sgpm/tasks/{id}/submit',
                    'approve_subtask' => $isHod ? 'POST /sgpm/subtasks/{id}/approve' : null,
                    'reject_subtask' => $isHod ? 'POST /sgpm/subtasks/{id}/reject' : null,
                    'evaluate_task' => $isHod ? 'POST /sgpm/tasks/{id}/evaluate' : null,
                ],
            ],
        ]);
    }

    /** GET /performance/staff — Scorecard. */
    public function scorecard()
    {
        $result = $this->callWeb(SgpmPerformanceController::class, 'staffDashboard');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Scorecard is not available.');
        }

        $d = $result->getData();
        $avg = (float) ($d['avgScore'] ?? 0);
        $standing = $avg >= 80
            ? 'Excellent'
            : ($avg >= 60 ? 'Good' : ($avg > 0 ? 'Needs Attention' : 'No scores yet'));

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Scorecard',
                'avg_score' => round($avg, 1),
                'standing' => $standing,
                'tasks' => collect($d['tasks'] ?? [])->take(10)->map(fn ($t) => [
                    'taskID' => (int) ($t->taskID ?? 0),
                    'kpi' => $t->kpi ?? null,
                    'status' => $t->status ?? null,
                    'weight' => (float) ($t->weight ?? 0),
                    'total_score' => $t->total_score !== null ? (float) $t->total_score : null,
                    'due_date' => $this->fmtDate($t->due_date ?? null),
                ])->values(),
                'open_tasks_path' => '/sgpm/tasks',
            ],
        ]);
    }

    private function shapeHodTask($t): array
    {
        return [
            'id' => (int) $t->id,
            'task_name' => $t->task_name ?? null,
            'goal_name' => $t->goal->goal_name ?? null,
            'weight' => (float) ($t->weight ?? 0),
            'deadline' => $this->fmtDate($t->goal->deadline ?? null),
            'description' => $t->description ?? null,
            'status' => $t->status ?? null,
            'can_delegate' => true,
            'can_view_progress' => true,
        ];
    }

    private function shapeMemberTask($t): array
    {
        $isDirect = (bool) ($t->is_direct ?? false);
        $id = (int) $t->id;
        $subtasks = collect($t->subtasks ?? []);
        $weightSum = (float) ($t->weight_sum ?? $subtasks->sum('weight'));
        $progress = (float) ($t->progress_val ?? $subtasks->where('is_approved', true)->sum('marks'));
        $remaining = max(0, 100 - $weightSum);

        return [
            'id' => $id,
            'is_direct' => $isDirect,
            'member_task_id' => $isDirect ? null : $id,
            'direct_task_id' => $isDirect ? $id : null,
            'task_name' => $t->display_name ?? $t->task_name ?? null,
            'goal_name' => $t->parent_goal_name ?? ($t->goal->goal_name ?? ($t->parentTask->goal->goal_name ?? null)),
            'description' => $t->description ?? null,
            'weight' => (float) ($t->assigned_weight ?? $t->weight ?? 0),
            'progress' => $progress,
            'subtask_weight_sum' => $weightSum,
            'remaining_subtask_weight' => $remaining,
            'subtask_count' => $subtasks->count(),
            'status' => $t->status ?? null,
            'source' => $isDirect ? 'direct' : 'department',
            'can_add_subtasks' => $remaining > 0,
            'can_view_subtasks' => $subtasks->isNotEmpty() || true,
        ];
    }

    private function shapeSgpmTask($t, int $userId, bool $isHod): array
    {
        $subtasks = collect($t->subtasks ?? []);
        $assignedTo = (int) ($t->assigned_to ?? 0);
        $isMine = $assignedTo === $userId;
        $status = (string) ($t->status ?? '');
        $canManageSubtasks = $isMine && in_array($status, ['Pending', 'In Progress', ''], true);
        $submittedCount = $subtasks->where('status', 'Submitted')->count();

        return [
            'taskID' => (int) ($t->taskID ?? 0),
            'kpi' => $t->kpi ?? null,
            'weight' => (float) ($t->weight ?? 0),
            'due_date' => $this->fmtDate($t->due_date ?? null),
            'status' => $status !== '' ? $status : 'Pending',
            'progress' => (float) ($t->progress ?? 0),
            'total_score' => $t->total_score !== null ? (float) $t->total_score : null,
            'assigned_to' => $assignedTo,
            'assignee_name' => $t->assignee->name ?? null,
            'is_mine' => $isMine,
            'can_manage_subtasks' => $canManageSubtasks,
            'can_submit_progress' => $isMine && in_array($status, ['Pending', 'In Progress'], true),
            'can_evaluate' => $isHod && $status === 'Completed',
            'submitted_subtask_count' => $submittedCount,
            'can_review_subtasks' => $isHod && $submittedCount > 0,
            'subtasks' => $subtasks->map(fn ($s) => [
                'subtaskID' => (int) ($s->subtaskID ?? 0),
                'title' => $s->title ?? null,
                'description' => $s->description ?? null,
                'weight_percentage' => (float) ($s->weight_percentage ?? 0),
                'achieved_score' => $s->achieved_score !== null ? (float) $s->achieved_score : null,
                'status' => $s->status ?? null,
                'due_date' => $this->fmtDate($s->due_date ?? null),
                'hod_comments' => $s->hod_comments ?? null,
                'can_submit' => $isMine && ! in_array(($s->status ?? ''), ['Submitted', 'Approved'], true),
                'can_approve' => $isHod && ($s->status ?? '') === 'Submitted',
                'can_reject' => $isHod && ($s->status ?? '') === 'Submitted',
            ])->values(),
        ];
    }

    private function fmtDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->timezone(config('app.timezone', 'Africa/Dar_es_Salaam'))
                ->format('Y-m-d');
        } catch (\Throwable $e) {
            return is_string($value) ? substr($value, 0, 10) : null;
        }
    }

    private function callWeb(string $class, string $method, array $params = [])
    {
        try {
            return app()->call([app($class), $method], $params);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    private function forbiddenOrPassthrough($result, string $fallbackMessage)
    {
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            $error = Session::get('error') ?: $fallbackMessage;
            $path = (string) parse_url($result->getTargetUrl(), PHP_URL_PATH);
            $status = str_ends_with($path, '/login') ? 403 : 422;

            return response()->json(['success' => false, 'message' => $error], $status);
        }

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json(['success' => false, 'message' => $fallbackMessage], 403);
    }
}
