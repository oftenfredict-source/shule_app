<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TeachersController;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Mobile hubs for result approvals (web: /approve_result/{examID}).
 * Opened from dashboard pending cards; Academic / class teacher / coordinator.
 */
class TeacherResultApprovalMobileApiController extends Controller
{
    /**
     * GET /result-approvals
     * Pending / waiting / special lists (same source as dashboard approvals).
     */
    public function index(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'teachersDashboard');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Result approvals are not available.');
        }

        $d = $result->getData();
        $approvalRow = function ($a) {
            return [
                'examID' => isset($a->examID) ? (int) $a->examID : null,
                'exam_name' => $a->examination->exam_name ?? 'N/A',
                'role' => $a->role->name ?? $a->role->role_name ?? 'N/A',
                'approval_order' => $a->approval_order ?? null,
                'status' => $a->status ?? null,
                'rejection_reason' => $a->rejection_reason ?? null,
                'api_path' => isset($a->examID) ? '/result-approvals/' . (int) $a->examID : null,
            ];
        };

        $pending = collect($d['pendingApprovals'] ?? [])->map($approvalRow)->values();
        $special = collect($d['specialRoleApprovals'] ?? [])->map(function ($s) use ($approvalRow) {
            $row = $approvalRow($s['approval'] ?? null);
            $row['type'] = $s['type'] ?? null;
            $row['exam_name'] = $s['exam']->exam_name
                ?? ($s['approval']->examination->exam_name ?? $row['exam_name']);

            return $row;
        })->values();
        $waiting = collect($d['waitingApprovals'] ?? [])->map(function ($w) use ($approvalRow) {
            $row = $approvalRow($w['approval'] ?? null);
            $row['pending_count'] = (int) ($w['pending_count'] ?? 0);
            $row['current_approver'] = isset($w['current_approver']) && $w['current_approver'] ? [
                'role' => $w['current_approver']->role->name
                    ?? $w['current_approver']->role->role_name
                    ?? null,
                'approval_order' => $w['current_approver']->approval_order ?? null,
                'status' => $w['current_approver']->status ?? null,
            ] : null;

            return $row;
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Result Approvals',
                'pending_count' => $pending->count() + $special->count(),
                'pending' => $pending,
                'special' => $special,
                'waiting' => $waiting,
                'actions' => [
                    'open' => 'GET /result-approvals/{examID}',
                    'submit' => 'POST /result-approvals/{examID}/submit',
                ],
            ],
        ]);
    }

    /**
     * GET /result-approvals/{examID}
     * Hub for website /approve_result/{examID}.
     */
    public function show(Request $request, $examID)
    {
        $result = $this->callWeb(TeachersController::class, 'approveResult', [
            'examID' => $examID,
        ]);

        if ($result instanceof RedirectResponse) {
            $error = Session::get('error') ?: 'No pending approval found for this examination.';
            $path = (string) parse_url($result->getTargetUrl(), PHP_URL_PATH);
            $status = str_ends_with($path, '/login') ? 403 : 422;

            return response()->json(['success' => false, 'message' => $error], $status);
        }

        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Failed to open result approval.');
        }

        $d = $result->getData();
        $exam = $d['examination'] ?? null;
        $approval = $d['resultApproval'] ?? null;

        $chain = collect($d['allApprovals'] ?? [])->map(function ($a) {
            return [
                'result_approvalID' => (int) ($a->result_approvalID ?? 0),
                'approval_order' => (int) ($a->approval_order ?? 0),
                'role_id' => $a->role_id ? (int) $a->role_id : null,
                'role_name' => $a->role->name ?? $a->role->role_name ?? null,
                'special_role_type' => $a->special_role_type ?? null,
                'status' => $a->status ?? null,
                'approved_by' => $a->approved_by ? (int) $a->approved_by : null,
                'approved_at' => $this->fmtDateTime($a->approved_at ?? null),
                'rejection_reason' => $a->rejection_reason ?? null,
            ];
        })->values();

        $classes = collect($d['participatingClasses'] ?? [])->map(fn ($c) => [
            'classID' => (int) $c->classID,
            'class_name' => $c->class_name ?? null,
        ])->values();

        $subclasses = collect($d['participatingSubclasses'] ?? [])->map(fn ($s) => [
            'subclassID' => (int) $s->subclassID,
            'classID' => (int) ($s->classID ?? 0),
            'subclass_name' => $s->subclass_name ?? null,
            'class_name' => $s->class->class_name ?? null,
        ])->values();

        $showOnlyRoadmap = (bool) ($d['showOnlyRoadmap'] ?? false);
        $isClassTeacher = (bool) ($d['isClassTeacherApproval'] ?? false);
        $isCoordinator = (bool) ($d['isCoordinatorApproval'] ?? false);

        // Website page loads student marks via AJAX (/get_filtered_results_for_approval).
        // Embed that same payload here so one hub call matches what the browser shows after load.
        $resultsPayload = [
            'results' => [],
            'total_count' => 0,
            'statistics' => null,
            'school_type' => $d['schoolType'] ?? null,
        ];
        if (!$showOnlyRoadmap) {
            $mainClassId = $request->input('main_class_id');
            $subclassId = $request->input('subclass_id');

            if ($isClassTeacher) {
                $available = collect($d['availableSubclasses'] ?? []);
                if (!$subclassId || $subclassId === 'all') {
                    $subclassId = $available->count() === 1
                        ? ($available->first()['subclassID'] ?? 'all')
                        : 'all';
                }
                $mainClassId = 'all';
            } elseif ($isCoordinator) {
                $available = collect($d['availableMainClasses'] ?? []);
                if (!$mainClassId || $mainClassId === 'all') {
                    $mainClassId = $available->count() === 1
                        ? ($available->first()['classID'] ?? 'all')
                        : 'all';
                }
            } else {
                // Academic / regular role — same as website default filters.
                $mainClassId = $mainClassId ?: 'all';
                $subclassId = $subclassId ?: 'all';
            }

            $request->merge([
                'main_class_id' => $mainClassId,
                'subclass_id' => $subclassId,
            ]);

            $filtered = $this->callWeb(TeachersController::class, 'getFilteredResultsForApproval', [
                'request' => $request,
                'examID' => $examID,
            ]);
            if ($filtered instanceof JsonResponse) {
                $fp = $filtered->getData(true);
                if ($fp['success'] ?? false) {
                    $resultsPayload = [
                        'results' => $fp['results'] ?? [],
                        'total_count' => (int) ($fp['total_count'] ?? count($fp['results'] ?? [])),
                        'statistics' => $fp['statistics'] ?? null,
                        'school_type' => $fp['school_type'] ?? ($d['schoolType'] ?? null),
                        'filters_applied' => [
                            'main_class_id' => $mainClassId,
                            'subclass_id' => $subclassId,
                        ],
                    ];
                } elseif (!empty($fp['error_code']) || !empty($fp['message']) || !empty($fp['error'])) {
                    $resultsPayload['error'] = $fp['error'] ?? $fp['message'] ?? 'Failed to load results.';
                    $resultsPayload['error_code'] = $fp['error_code'] ?? null;
                }
            }
        }

        $examinationPayload = $exam ? [
            'examID' => (int) $exam->examID,
            'exam_name' => $exam->exam_name ?? null,
            'exam_category' => $exam->exam_category ?? null,
            'term' => $exam->term ?? null,
            'year' => $exam->year ?? null,
            'approval_status' => $exam->approval_status ?? null,
            'start_date' => $this->fmtDate($exam->start_date ?? null),
            'end_date' => $this->fmtDate($exam->end_date ?? null),
        ] : null;

        $currentApprovalPayload = $approval ? [
            'result_approvalID' => (int) $approval->result_approvalID,
            'approval_order' => (int) ($approval->approval_order ?? 0),
            'role_id' => $approval->role_id ? (int) $approval->role_id : null,
            'role_name' => $approval->role->name ?? $approval->role->role_name ?? null,
            'special_role_type' => $approval->special_role_type ?? null,
            'status' => $approval->status ?? null,
            'rejection_reason' => $approval->rejection_reason ?? null,
        ] : null;

        $examHasEnded = (bool) ($d['examHasEnded'] ?? false);
        $attendanceStats = $d['attendanceStats'] ?? null;

        // Same top-to-bottom blocks as resources/views/Teacher/approve_result.blade.php
        $pageSections = [
            [
                'key' => 'approval_chain',
                'title' => 'Approval Chain',
                'visible' => true,
                'path' => 'chain',
            ],
            [
                'key' => 'examination_info',
                'title' => 'Examination',
                'visible' => true,
                'path' => 'examination',
            ],
            [
                'key' => 'examination_attendance',
                'title' => 'Examination Statistics (attendance)',
                'visible' => $examHasEnded && !empty($attendanceStats),
                'path' => 'attendance_stats',
                'note' => 'Website card “Walio Takwa / Hudhuria / Hawaja Hudhuria” + Attendance by Subject table. Not the same as marks tables.',
            ],
            [
                'key' => 'filters',
                'title' => 'Filter Results',
                'visible' => !$showOnlyRoadmap,
                'path' => 'filters',
            ],
            [
                'key' => 'result_statistics',
                'title' => 'Statistics za Matokeo',
                'visible' => !$showOnlyRoadmap && !empty($resultsPayload['statistics']),
                'path' => 'results_statistics',
                'note' => 'Use subject_stats / grade_gender_stats / class_pass_rates — same as website charts after AJAX load.',
            ],
            [
                'key' => 'results_tables',
                'title' => 'Results',
                'visible' => !$showOnlyRoadmap,
                'path' => 'results',
                'note' => 'Grouped by class/subclass → students → subjects/marks. For exam 45 only subjects with entered marks appear (e.g. Physics).',
            ],
            [
                'key' => 'approval_action',
                'title' => 'Approval Action',
                'visible' => !$showOnlyRoadmap,
                'path' => 'approval_action',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Approve Results',
                'screen' => 'approve_result',
                'web_path' => '/approve_result/' . (int) $examID,
                'show_only_roadmap' => $showOnlyRoadmap,
                'can_approve' => !$showOnlyRoadmap,
                'page_sections' => $pageSections,
                'examination' => $examinationPayload,
                'current_approval' => $currentApprovalPayload,
                'is_class_teacher_approval' => $isClassTeacher,
                'is_coordinator_approval' => $isCoordinator,
                'available_subclasses' => collect($d['availableSubclasses'] ?? [])->values(),
                'available_main_classes' => collect($d['availableMainClasses'] ?? [])->values(),
                'chain' => $chain,
                'classes' => $classes,
                'subclasses' => $subclasses,
                'filters' => [
                    'main_class_id' => $resultsPayload['filters_applied']['main_class_id'] ?? 'all',
                    'subclass_id' => $resultsPayload['filters_applied']['subclass_id'] ?? 'all',
                    'classes' => $classes,
                    'subclasses' => $subclasses,
                    'available_subclasses' => collect($d['availableSubclasses'] ?? [])->values(),
                    'available_main_classes' => collect($d['availableMainClasses'] ?? [])->values(),
                ],
                'class_subclass_map' => $d['classSubclassMap'] ?? (object) [],
                'overall_statistics' => $d['overallStatistics'] ?? null,
                'class_statistics' => $d['classStatistics'] ?? null,
                'attendance_stats' => $attendanceStats,
                'exam_has_ended' => $examHasEnded,
                'school_type' => $d['schoolType'] ?? null,
                // Marks tables + AJAX statistics (website #resultsContainer + #statisticsSection)
                'results' => $resultsPayload['results'],
                'results_total_count' => $resultsPayload['total_count'],
                'results_statistics' => $resultsPayload['statistics'],
                'results_filters' => $resultsPayload['filters_applied'] ?? null,
                'results_error' => $resultsPayload['error'] ?? null,
                'approval_action' => [
                    'can_approve' => !$showOnlyRoadmap,
                    'current_approval' => $currentApprovalPayload,
                    'submit' => 'POST /result-approvals/' . (int) $examID . '/submit',
                    'approve_body' => [
                        'action' => 'approve',
                        'approval_comment' => 'optional',
                    ],
                    'reject_body' => [
                        'action' => 'reject',
                        'rejection_reason' => 'required',
                    ],
                ],
                'actions' => [
                    'filtered_results' => 'GET /result-approvals/' . (int) $examID . '/filtered?main_class_id=all&subclass_id=all',
                    'class_teacher' => 'GET /result-approvals/' . (int) $examID . '/class-teacher',
                    'coordinator' => 'GET /result-approvals/' . (int) $examID . '/coordinator',
                    'chain' => 'GET /result-approvals/' . (int) $examID . '/chain',
                    'submit' => 'POST /result-approvals/' . (int) $examID . '/submit',
                ],
            ],
        ]);
    }

    private function fmtDate($value): ?string
    {
        if ($value === null || $value === '' || in_array($value, ['every_week', 'every_month'], true)) {
            return is_string($value) ? $value : null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return is_string($value) ? substr($value, 0, 10) : null;
        }
    }

    private function fmtDateTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d H:i');
        } catch (\Throwable $e) {
            return is_string($value) ? $value : null;
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
        if ($result instanceof RedirectResponse) {
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
