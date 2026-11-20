<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ExamAttendanceController;
use App\Http\Controllers\ManageExaminationController;
use App\Http\Controllers\ResultManagementController;
use App\Http\Controllers\TimeTableController;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hubs for Teaching → Exams sidebar:
 * Add Results, Supervise, Exam Papers, Approvals, Attendance.
 * Write/AJAX actions stay as plain bridges in routes/mobile_teacher.php.
 */
class TeacherExamMobileApiController extends Controller
{
    /** GET /exams — module index (submenu flags for the app). */
    public function index(Request $request)
    {
        $caps = app(TeacherMobileApiController::class)->capabilities($request)->getData(true)['data'] ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Exams',
                'submenus' => [
                    [
                        'key' => 'add_results',
                        'title' => 'Add Results',
                        'path' => '/add-results',
                        'visible' => (bool) ($caps['can_add_results'] ?? false),
                    ],
                    [
                        'key' => 'supervise',
                        'title' => 'Supervise',
                        'path' => '/exams/supervise',
                        'visible' => true,
                    ],
                    [
                        'key' => 'exam_papers',
                        'title' => 'Exam Papers',
                        'path' => '/exam-papers',
                        'visible' => true,
                    ],
                    [
                        'key' => 'approvals',
                        'title' => 'Approvals',
                        'path' => '/exam-paper-approvals',
                        'visible' => true,
                        'badge' => (int) ($caps['badges']['pending_paper_approvals'] ?? 0),
                    ],
                    [
                        'key' => 'attendance',
                        'title' => 'Attendance',
                        'path' => '/exam-attendance',
                        'visible' => (bool) ($caps['can_collect_exam_attendance'] ?? false),
                    ],
                ],
            ],
        ]);
    }

    /** GET /add-results */
    public function addResults(Request $request)
    {
        $result = $this->callWeb(ResultManagementController::class, 'addResultsView');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Add Results is not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Add Results',
                'result_button_status' => (bool) ($d['resultButtonStatus'] ?? true),
                'years' => collect($d['availableYears'] ?? [])->values(),
                'terms' => collect($d['availableTerms'] ?? [])->map(function ($t) {
                    if (is_object($t)) {
                        return [
                            'termID' => (int) ($t->termID ?? $t->id ?? 0),
                            'term_number' => $t->term_number ?? null,
                            'term_name' => $t->term_name ?? $t->name ?? null,
                        ];
                    }

                    return $t;
                })->values(),
                'classes' => collect($d['classes'] ?? [])->map(fn ($c) => [
                    'classID' => (int) $c->classID,
                    'class_name' => $c->class_name ?? null,
                ])->values(),
                'subclasses' => collect($d['subclasses'] ?? [])->map(fn ($s) => [
                    'subclassID' => (int) $s->subclassID,
                    'classID' => (int) ($s->classID ?? $s->class?->classID ?? 0),
                    'subclass_name' => $s->subclass_name ?? null,
                    'class_name' => $s->class?->class_name ?? null,
                ])->values(),
                'subjects' => collect($d['schoolSubjects'] ?? [])->map(function ($s) {
                    return [
                        'subjectID' => (int) $s->subjectID,
                        'subject_name' => $s->subject_name ?? null,
                        'is_duo' => (bool) ($s->is_duo ?? false),
                        'components' => collect($s->components ?? [])->map(fn ($c) => [
                            'componentID' => (int) $c->componentID,
                            'component_name' => $c->component_name ?? null,
                            'max_marks' => (int) ($c->max_marks ?? 0),
                        ])->values(),
                    ];
                })->values(),
                'exams' => collect($d['availableExams'] ?? [])->map(fn ($e) => $this->shapeExam($e))->values(),
            ],
        ]);
    }

    /** GET /exams/supervise?category=&year= */
    public function supervise(Request $request)
    {
        $result = $this->callWeb(ManageExaminationController::class, 'supervise_exams');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Supervise exams is not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Supervise',
                'selected_category' => $d['selectedCategory'] ?? 'all',
                'selected_year' => $d['selectedYear'] ?? null,
                'years' => collect($d['years'] ?? [])->values(),
                'categories' => [
                    ['value' => 'all', 'label' => 'All'],
                    ['value' => 'school_exams', 'label' => 'School exams'],
                    ['value' => 'test', 'label' => 'Tests'],
                ],
                'assignments' => collect($d['assignments'] ?? [])->map(function ($a) {
                    $a = (object) $a;
                    $examDate = $a->exam_date ?? $a->test_date ?? null;
                    if ($examDate) {
                        try {
                            $examDate = Carbon::parse($examDate)->timezone(config('app.timezone'))->format('Y-m-d');
                        } catch (\Throwable $e) {
                            $examDate = (string) $examDate;
                        }
                    }

                    return [
                        'exam_hall_supervisorID' => isset($a->exam_hall_supervisorID) ? (int) $a->exam_hall_supervisorID : null,
                        'examID' => isset($a->examID) ? (int) $a->examID : null,
                        'exam_name' => $a->exam_name ?? null,
                        'exam_category' => $a->exam_category ?? null,
                        'term' => $a->term ?? null,
                        'hall_id' => isset($a->exam_hallID) ? (int) $a->exam_hallID : (isset($a->hall_id) ? (int) $a->hall_id : null),
                        'hall_name' => $a->hall_name ?? null,
                        'classID' => isset($a->classID) ? (int) $a->classID : null,
                        'class_name' => $a->class_name ?? null,
                        'subjectID' => isset($a->subjectID) ? (int) $a->subjectID : null,
                        'subject_name' => $a->subject_name ?? null,
                        'exam_timetableID' => isset($a->exam_timetableID) ? (int) $a->exam_timetableID : null,
                        'exam_date' => $examDate,
                        'start_time' => $a->start_time ?? null,
                        'end_time' => $a->end_time ?? null,
                        'students_count' => (int) ($a->students_count ?? 0),
                        'is_active' => (bool) ($a->is_active ?? false),
                        'is_past' => (bool) ($a->is_past ?? false),
                        'scope' => $a->scope ?? null,
                        'can_view_students' => true,
                        'can_take_attendance' => true,
                        'can_view_timetable' => true,
                    ];
                })->values(),
            ],
        ]);
    }

    /** GET /exams/supervise/students?... */
    public function superviseStudents(Request $request)
    {
        $result = $this->callWeb(ManageExaminationController::class, 'viewHallStudentsPage');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Unable to open hall students.');
        }

        return response()->json([
            'success' => true,
            'data' => $this->shapeSuperviseContext($result->getData()),
        ]);
    }

    /** GET /exams/supervise/take-attendance?... */
    public function superviseTakeAttendance(Request $request)
    {
        $result = $this->callWeb(ManageExaminationController::class, 'takeAttendancePage');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Unable to open attendance.');
        }

        return response()->json([
            'success' => true,
            'data' => $this->shapeSuperviseContext($result->getData()),
        ]);
    }

    /** GET /exams/supervise/timetable */
    public function superviseTimetable(Request $request)
    {
        $result = $this->callWeb(TimeTableController::class, 'supervise_exam_time_table');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Exam timetable is not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Exam Timetable',
                'payload' => $this->viewPayload($d),
            ],
        ]);
    }

    /** GET /exam-papers */
    public function examPapers(Request $request)
    {
        $result = $this->callWeb(ManageExaminationController::class, 'exam_paper');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Exam papers are not available.');
        }

        $d = $result->getData();
        $myPapers = collect($d['myExamPapers'] ?? []);
        $pendingSlots = collect($d['pendingSlots'] ?? []);
        $shapedPapers = $myPapers->map(fn ($p) => $this->shapeExamPaper($p))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Exam Papers',
                'school_type' => $d['schoolType'] ?? null,
                'selected_year_ids' => $d['selectedYearIds'] ?? [],
                // Website tabs: Upload | Pending | Approved | Rejected | Waiting
                'tabs' => [
                    'upload' => ['key' => 'upload', 'label' => 'Upload'],
                    'pending' => [
                        'key' => 'pending',
                        'label' => 'Pending',
                        'count' => $pendingSlots->count(),
                        'source' => 'pending_slots',
                    ],
                    'approved' => [
                        'key' => 'approved',
                        'label' => 'Approved',
                        'count' => $myPapers->filter(fn ($p) => strtolower((string) ($p->status ?? '')) === 'approved')->count(),
                        'filter_status' => ['approved'],
                    ],
                    'rejected' => [
                        'key' => 'rejected',
                        'label' => 'Rejected',
                        'count' => $myPapers->filter(fn ($p) => strtolower((string) ($p->status ?? '')) === 'rejected')->count(),
                        'filter_status' => ['rejected'],
                    ],
                    'waiting' => [
                        'key' => 'waiting',
                        'label' => 'Waiting',
                        'count' => $myPapers->filter(fn ($p) => in_array(strtolower((string) ($p->status ?? '')), ['wait_approval', 'pending'], true))->count(),
                        'filter_status' => ['wait_approval', 'pending'],
                    ],
                ],
                'subjects' => collect($d['teacherSubjects'] ?? [])->map(function ($cs) {
                    $className = $cs->class?->class_name
                        ?? $cs->subclass?->class?->class_name
                        ?? null;
                    $subclassName = $cs->subclass?->subclass_name ?? null;

                    return [
                        'class_subjectID' => (int) $cs->class_subjectID,
                        'subjectID' => (int) $cs->subjectID,
                        'subject_name' => $cs->subject->subject_name ?? null,
                        'classID' => (int) ($cs->classID ?? $cs->class?->classID ?? $cs->subclass?->classID ?? 0),
                        'class_name' => $className,
                        'subclassID' => $cs->subclassID ? (int) $cs->subclassID : null,
                        'subclass_name' => $subclassName,
                        'is_duo_subject' => (bool) ($cs->is_duo_subject ?? false),
                        'duo_papers' => collect($cs->my_duo_papers ?? [])->values(),
                        'label' => trim(
                            ($cs->subject->subject_name ?? 'N/A')
                            . ($className ? ' - ' . $className : '')
                            . ($subclassName ? ' ' . $subclassName : '')
                        ),
                    ];
                })->values(),
                'examinations' => collect($d['examinations'] ?? [])->map(fn ($e) => $this->shapeExam($e))->values(),
                'my_papers' => $shapedPapers,
                'pending_slots' => $pendingSlots->map(fn ($p) => $this->shapeExamPaper($p, true))->values(),
                'rejection_notifications' => collect($d['rejectionNotifications'] ?? [])->values(),
                'question_format_rules' => [
                    'expected_total_marks' => 100,
                    'required_for_secondary' => stripos((string) ($d['schoolType'] ?? ''), 'secondary') !== false,
                    'skip_when_exam_allow_no_format' => true,
                    'fields' => [
                        'question_descriptions[]',
                        'question_marks[]',
                        'question_optional[]',
                        'optional_ranges[]',
                        'optional_required_counts[]',
                    ],
                ],
            ],
        ]);
    }

    /** GET /exam-paper-approvals */
    public function examPaperApprovals(Request $request)
    {
        // Default to all years so pending count matches capabilities badge (web defaults to one year).
        if (!$request->filled('year')) {
            $request->query->set('year', 'all');
            $request->merge(['year' => 'all']);
        }

        $result = $this->callWeb(ManageExaminationController::class, 'examPaperApproval');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Exam paper approvals are not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Exam Paper Approvals',
                'filters' => [
                    'year' => $d['yearFilter'] ?? null,
                    'term' => $d['termFilter'] ?? null,
                    'examID' => $d['examID'] ? (int) $d['examID'] : null,
                    'classID' => $d['classID'] ? (int) $d['classID'] : null,
                    'subclassID' => $d['subclassID'] ? (int) $d['subclassID'] : null,
                    'subjectID' => $d['subjectID'] ? (int) $d['subjectID'] : null,
                    'week' => $d['weekFilter'] ?? null,
                ],
                'available_years' => collect($d['availableYears'] ?? [])->values(),
                'available_weeks' => collect($d['available_weeks'] ?? [])->values(),
                'examinations' => collect($d['filter_examinations'] ?? [])->map(fn ($e) => $this->shapeExam($e))->values(),
                'classes' => collect($d['filter_classes'] ?? [])->map(fn ($c) => [
                    'classID' => (int) $c->classID,
                    'class_name' => $c->class_name ?? null,
                ])->values(),
                'subclasses' => collect($d['filter_subclasses'] ?? [])->map(fn ($s) => [
                    'subclassID' => (int) $s->subclassID,
                    'classID' => (int) ($s->classID ?? 0),
                    'subclass_name' => $s->subclass_name ?? null,
                ])->values(),
                'subjects' => collect($d['filter_subjects'] ?? [])->map(fn ($s) => [
                    'subjectID' => (int) ($s->subjectID ?? 0),
                    'subject_name' => $s->subject_name ?? null,
                ])->values(),
                'pending' => collect($d['pendingLogs'] ?? [])->map(fn ($log) => $this->shapeApprovalLog($log))->values(),
                'history' => collect($d['historyLogs'] ?? [])->map(fn ($log) => $this->shapeApprovalLog($log))->values(),
                'pending_count' => collect($d['pendingLogs'] ?? [])->count(),
            ],
        ]);
    }

    /** GET /exam-attendance */
    public function examAttendance(Request $request)
    {
        $result = $this->callWeb(ExamAttendanceController::class, 'teacherIndex');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Exam attendance is not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Exam Attendance',
                'assignments' => collect($d['assignments'] ?? [])->map(function ($a) {
                    return [
                        'id' => (int) $a->id,
                        'examID' => (int) $a->examID,
                        'exam_name' => $a->exam->exam_name ?? null,
                        'exam_category' => $a->exam->exam_category ?? null,
                        'term' => $a->exam->term ?? null,
                        'year' => $a->exam->year ?? null,
                        'classID' => (int) $a->classID,
                        'class_name' => $a->class->class_name ?? null,
                        'subclassID' => $a->subclassID ? (int) $a->subclassID : null,
                        'subclass_name' => $a->subclass->subclass_name ?? null,
                        'subject_ids' => collect($a->subject_ids ?? [])->map(fn ($id) => (int) $id)->values(),
                        'status' => (bool) ($a->status ?? false),
                        'can_open' => true,
                    ];
                })->values(),
            ],
        ]);
    }

    private function shapeSuperviseContext(array $d): array
    {
        $exam = $d['exam'] ?? null;
        $subject = $d['subject'] ?? null;
        $hall = $d['hall'] ?? null;

        return [
            'hall_id' => $d['hallID'] ? (int) $d['hallID'] : null,
            'hall_name' => $hall->hall_name ?? null,
            'subject_id' => $d['subjectID'] ? (int) $d['subjectID'] : null,
            'subject_name' => $subject->subject_name ?? null,
            'examID' => $d['examID'] ? (int) $d['examID'] : null,
            'exam_name' => $exam->exam_name ?? null,
            'exam_category' => $d['exam_category'] ?? ($exam->exam_category ?? null),
            'timetable_id' => $d['timetable_id'] ?? null,
            'classID' => isset($d['classID']) && $d['classID'] !== '' ? (int) $d['classID'] : null,
            'scope' => $d['scope'] ?? null,
            'date' => $d['date'] ?? null,
        ];
    }

    private function shapeExam($e): array
    {
        return [
            'examID' => (int) ($e->examID ?? 0),
            'exam_name' => $e->exam_name ?? null,
            'exam_category' => $e->exam_category ?? null,
            'term' => $e->term ?? null,
            'year' => $e->year ?? null,
            'status' => $e->status ?? null,
            'approval_status' => $e->approval_status ?? null,
            'start_date' => $this->fmtDate($e->start_date ?? null),
            'end_date' => $this->fmtDate($e->end_date ?? null),
            'upload_paper' => (bool) ($e->upload_paper ?? false),
            'allow_no_format' => (bool) ($e->allow_no_format ?? false),
            'allow_no_paper' => (bool) ($e->allow_no_paper ?? false),
        ];
    }

    private function shapeExamPaper($p, bool $isSlot = false): array
    {
        $cs = $p->classSubject ?? null;
        $className = $cs?->class?->class_name
            ?? $cs?->subclass?->class?->class_name
            ?? null;
        $subclassName = $cs?->subclass?->subclass_name ?? null;

        return [
            'exam_paperID' => (int) ($p->exam_paperID ?? 0),
            'examID' => (int) ($p->examID ?? 0),
            'exam_name' => $p->examination->exam_name ?? null,
            'class_subjectID' => (int) ($p->class_subjectID ?? 0),
            'subject_name' => $cs?->subject?->subject_name,
            'class_name' => $className,
            'subclass_name' => $subclassName,
            'componentID' => $p->componentID ? (int) $p->componentID : null,
            'component_name' => $p->component->component_name ?? null,
            'status' => $p->status ?? null,
            'upload_type' => $p->upload_type ?? null,
            'test_week' => $p->test_week ?? null,
            'test_date' => $this->fmtDate($p->test_date ?? null),
            'has_file' => filled($p->file_path ?? null),
            'current_step_name' => $p->current_step_name ?? null,
            'rejection_reason' => $p->rejection_reason ?? null,
            'created_at' => $p->created_at
                ? Carbon::parse($p->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i:s')
                : null,
            'is_pending_slot' => $isSlot,
            'can_download' => filled($p->file_path ?? null),
            'can_edit' => in_array(($p->status ?? ''), ['pending', 'rejected'], true),
            'can_resubmit' => ($p->status ?? '') === 'rejected',
            'can_delete' => in_array(($p->status ?? ''), ['pending', 'rejected'], true),
        ];
    }

    private function shapeApprovalLog($log): array
    {
        $paper = $log->examPaper ?? null;
        $cs = $paper?->classSubject;

        return [
            'paper_approval_logID' => (int) ($log->paper_approval_logID ?? 0),
            'exam_paperID' => (int) ($paper->exam_paperID ?? $log->exam_paperID ?? 0),
            'examID' => (int) ($paper->examID ?? 0),
            'exam_name' => $paper?->examination?->exam_name,
            'subject_name' => $cs?->subject?->subject_name,
            'class_name' => $cs?->class?->class_name ?? $cs?->subclass?->class?->class_name,
            'subclass_name' => $cs?->subclass?->subclass_name,
            'teacher_name' => $paper?->teacher
                ? trim(($paper->teacher->first_name ?? '') . ' ' . ($paper->teacher->last_name ?? ''))
                : null,
            'component_name' => $paper?->component?->component_name,
            'test_week' => $paper?->test_week,
            'status' => $log->status ?? null,
            'approval_order' => (int) ($log->approval_order ?? 0),
            'role_name' => $log->role->name ?? null,
            'special_role_type' => $log->special_role_type ?? null,
            'comment' => $log->comment ?? null,
            'has_file' => filled($paper?->file_path),
            'can_approve' => ($log->status ?? '') === 'pending',
            'can_review' => true,
            'can_download' => filled($paper?->file_path),
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

    private function viewPayload(array $d): array
    {
        $out = [];
        foreach ($d as $key => $value) {
            if (in_array($key, ['errors', '__env', 'app', 'obLevel', '__data', 'slot'], true)) {
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
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
