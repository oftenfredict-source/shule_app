<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\TimeTableController;
use App\Models\ClassSubject;
use App\Models\LessonPlan;
use App\Models\Logbook;
use App\Models\SchemeOfWork;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Mobile-shaped screens for the Teaching module (and HOD department logbook / calendar).
 * Detail and write actions stay on the existing bridge to TeachersController / CalendarController.
 */
class TeacherTeachingMobileApiController extends Controller
{
    /**
     * GET /timetable — Class → Timetable (read-only weekly grid).
     * Web: /teacher_time_table
     */
    public function timetable()
    {
        $result = $this->callWeb(TimeTableController::class, 'teacher_time_table');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Timetable is not available.');
        }

        $d = $result->getData();
        $days = $d['days'] ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $byDay = $d['sessionsByDay'] ?? [];

        $sessionsByDay = [];
        foreach ($days as $day) {
            $list = collect($byDay[$day] ?? []);
            $sessionsByDay[$day] = $list->map(function ($session) {
                $subjectName = $session->classSubject->subject->subject_name
                    ?? $session->subject->subject_name
                    ?? 'N/A';
                if ($session->is_prepo) {
                    $subjectName .= ' (Prepo)';
                }
                $className = $session->subclass?->class?->class_name ?? '';
                $subclassName = $session->subclass?->subclass_name ?? '';
                $classLabel = trim($className . ($className && $subclassName ? ' ' : '') . $subclassName);

                $start = (string) ($session->start_time ?? '');
                $end = (string) ($session->end_time ?? '');
                if (strlen($start) === 5) {
                    $start .= ':00';
                }
                if (strlen($end) === 5) {
                    $end .= ':00';
                }

                return [
                    'session_timetableID' => (int) $session->session_timetableID,
                    'day' => $session->day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'start_time_formatted' => $start !== ''
                        ? Carbon::parse($start)->format('g:i A')
                        : null,
                    'end_time_formatted' => $end !== ''
                        ? Carbon::parse($end)->format('g:i A')
                        : null,
                    'subject_name' => $subjectName,
                    'class_name' => $className ?: null,
                    'subclass_name' => $subclassName ?: null,
                    'class_label' => $classLabel !== '' ? $classLabel : null,
                    'is_prepo' => (bool) ($session->is_prepo ?? false),
                    'subjectID' => $session->subjectID ? (int) $session->subjectID : null,
                    'class_subjectID' => $session->class_subjectID ? (int) $session->class_subjectID : null,
                ];
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'My Timetable',
                'days' => array_values($days),
                'time_slots' => collect($d['timeSlots'] ?? [])->values(),
                'sessions_by_day' => $sessionsByDay,
                'message' => $d['message'] ?? null,
            ],
        ]);
    }

    /**
     * GET /sessions?week=0 — Teaching → Sessions (week list + action flags).
     * Web: /teacher/my-sessions
     */
    public function sessions(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'getTeacherMySessionsAPI');
        if (!$result instanceof JsonResponse) {
            return $this->forbiddenOrPassthrough($result, 'Sessions are not available.');
        }

        $payload = $result->getData(true);
        if (!($payload['success'] ?? false)) {
            $status = $result->getStatusCode();
            if ($status < 400) {
                $status = 400;
            }

            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? $payload['message'] ?? 'Sessions are not available.',
            ], $status);
        }

        $data = $payload['data'] ?? [];
        $days = collect($data['days'] ?? [])->map(function ($day) {
            $sessions = collect($day['sessions'] ?? [])->map(function ($s) use ($day) {
                $canInteract = (bool) ($s['can_interact'] ?? false);
                $isPast = (bool) ($s['is_past'] ?? false);
                $isSessionTime = (bool) ($s['is_session_time'] ?? false);
                $status = $isSessionTime ? 'Active' : ($isPast ? 'Done' : 'Upcoming');
                $isBlockedDay = (bool) (($day['is_holiday'] ?? false) || ($day['is_weekend'] ?? false));

                return array_merge($s, [
                    'status' => $status,
                    'can_collect_attendance' => $canInteract && !$isBlockedDay,
                    'can_assign_task' => $canInteract && !$isBlockedDay && empty($s['task']),
                    'can_open_lesson_plan' => !$isBlockedDay,
                    'can_view_task' => !empty($s['task']),
                ]);
            })->values();

            return array_merge($day, [
                'session_count' => $sessions->count(),
                'sessions' => $sessions,
            ]);
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'My Sessions',
                'week_offset' => (int) ($data['week_offset'] ?? 0),
                'week_start' => $data['week_start'] ?? null,
                'week_end' => $data['week_end'] ?? null,
                'holiday_dates' => $data['holiday_dates'] ?? [],
                'days' => $days,
            ],
        ]);
    }

    public function tasks(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'myTasks');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Tasks are not available.');
        }

        $tasks = collect($result->getData()['tasks'] ?? []);

        return response()->json([
            'success' => true,
            'data' => [
                'filters' => [
                    'status' => $request->query('status'),
                    'date' => $request->query('date'),
                ],
                'tasks' => $tasks->map(function ($task) {
                    $session = $task->sessionTimetable;
                    $subject = $session->subject ?? null;
                    $subclass = $session->subclass ?? null;
                    $class = $subclass->class ?? null;
                    $approver = $task->approver;

                    return [
                        'session_taskID' => (int) $task->session_taskID,
                        'session_timetableID' => (int) $task->session_timetableID,
                        'task_date' => Carbon::parse($task->task_date)->format('Y-m-d'),
                        'status' => $task->status,
                        'topic' => $task->topic,
                        'subtopic' => $task->subtopic,
                        'task_description' => $task->task_description,
                        'admin_comment' => $task->admin_comment,
                        'can_edit' => strtolower((string) $task->status) === 'rejected',
                        'subject_name' => $subject->subject_name ?? null,
                        'class_name' => trim(($class->class_name ?? '') . ' ' . ($subclass->subclass_name ?? '')) ?: null,
                        'session_start' => $session->start_time ?? null,
                        'session_end' => $session->end_time ?? null,
                        'approver_name' => $approver
                            ? trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? ''))
                            : null,
                    ];
                })->values(),
            ],
        ]);
    }

    public function schemeOfWork(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'schemeOfWork');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Scheme of work is not available.');
        }

        $subjects = collect($result->getData()['teacherSubjects'] ?? []);
        $year = (int) ($request->query('year') ?: date('Y'));
        $ids = $subjects->pluck('class_subjectID')->filter()->map(fn ($id) => (int) $id)->all();

        $schemes = empty($ids)
            ? collect()
            : SchemeOfWork::whereIn('class_subjectID', $ids)
                ->where('year', $year)
                ->get()
                ->keyBy(fn ($s) => (int) $s->class_subjectID);

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'subjects' => $subjects->map(function ($cs) use ($schemes, $year) {
                    $scheme = $schemes->get((int) $cs->class_subjectID);
                    $className = 'N/A';
                    if ($cs->subclass) {
                        $className = trim(($cs->subclass->class->class_name ?? '') . ' ' . ($cs->subclass->subclass_name ?? ''));
                    } elseif ($cs->class) {
                        $className = ($cs->class->class_name ?? '') . ' - All streams';
                    }

                    return [
                        'class_subjectID' => (int) $cs->class_subjectID,
                        'subjectID' => (int) $cs->subjectID,
                        'subject_name' => $cs->subject->subject_name ?? null,
                        'class_name' => $className,
                        'year' => $year,
                        'has_scheme' => (bool) $scheme,
                        'scheme' => $scheme ? [
                            'scheme_of_workID' => (int) $scheme->scheme_of_workID,
                            'status' => $scheme->status,
                            'created_by' => $scheme->created_by ? (int) $scheme->created_by : null,
                        ] : null,
                        'actions' => [
                            'can_create' => !$scheme,
                            'can_view' => (bool) $scheme,
                            'can_manage' => (bool) $scheme,
                            'can_use_existing' => !$scheme,
                        ],
                    ];
                })->values(),
            ],
        ]);
    }

    public function lessonPlans()
    {
        $result = $this->callWeb(TeachersController::class, 'lessonPlans');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Lesson plans are not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Lesson Plans',
                'subjects' => collect($d['subjects'] ?? [])->map(fn ($s) => [
                    'subjectID' => (int) ($s['subjectID'] ?? $s->subjectID ?? 0),
                    'subject_name' => $s['subject_name'] ?? $s->subject_name ?? null,
                ])->values(),
                'school_type' => $d['schoolType'] ?? null,
                'current_year' => (int) ($d['currentYear'] ?? date('Y')),
                'message' => $d['message'] ?? null,
            ],
        ]);
    }

    /**
     * GET /lesson-plans/get?session_timetableID=&date= (or lesson_date=)
     */
    public function lessonPlanGet(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $sessionTimetableID = $request->input('session_timetableID');
        $date = $request->input('date') ?: $request->input('lesson_date');

        if (!$teacherID || !$sessionTimetableID || !$date) {
            return response()->json([
                'success' => false,
                'message' => 'session_timetableID and date (or lesson_date) are required.',
            ], 422);
        }

        $plan = LessonPlan::where('session_timetableID', $sessionTimetableID)
            ->whereDate('lesson_date', $date)
            ->where('teacherID', $teacherID)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson plan not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->shapeLessonPlan($plan),
        ]);
    }

    /**
     * GET /lesson-plans/by-id?lesson_planID=
     */
    public function lessonPlanById(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $lessonPlanID = $request->input('lesson_planID');

        if (!$teacherID || !$lessonPlanID) {
            return response()->json([
                'success' => false,
                'message' => 'lesson_planID is required.',
            ], 422);
        }

        $plan = LessonPlan::where('lesson_planID', $lessonPlanID)
            ->where('teacherID', $teacherID)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson plan not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->shapeLessonPlan($plan),
        ]);
    }

    /**
     * GET /lesson-plans/previous?session_timetableID=&before_date=
     * Prefills form fields from the latest earlier plan for the same subject.
     */
    public function lessonPlanPrevious(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'getPreviousLessonPlan');
        if (!$result instanceof JsonResponse) {
            return $this->forbiddenOrPassthrough($result, 'Unable to load previous lesson plan.');
        }

        $payload = $result->getData(true);
        if (!($payload['success'] ?? false)) {
            $status = $result->getStatusCode();
            if ($status < 400) {
                $status = 400;
            }

            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? $payload['message'] ?? 'Unable to load previous lesson plan.',
            ], $status);
        }

        if (!($payload['found'] ?? false) || empty($payload['data'])) {
            return response()->json([
                'success' => true,
                'found' => false,
                'data' => null,
            ]);
        }

        $raw = $payload['data'];
        $stages = $raw['stages'] ?? $raw['lesson_stages'] ?? [];
        if (!is_array($stages)) {
            $stages = [];
        }

        $lessonDate = $this->formatPlanDate($raw['lesson_date'] ?? null);

        return response()->json([
            'success' => true,
            'found' => true,
            'data' => [
                'lesson_planID' => (int) ($raw['lesson_planID'] ?? 0),
                'lesson_date' => $lessonDate,
                'main_competence' => $raw['main_competence'] ?? null,
                'specific_competence' => $raw['specific_competence'] ?? null,
                'main_activity' => $raw['main_activity'] ?? null,
                'specific_activity' => $raw['specific_activity'] ?? null,
                'teaching_learning_resources' => $raw['teaching_learning_resources'] ?? null,
                'references' => $raw['references'] ?? null,
                'lesson_stages' => $stages,
                'remarks' => $raw['remarks'] ?? null,
                'reflection' => $raw['reflection'] ?? null,
                'evaluation' => $raw['evaluation'] ?? null,
            ],
        ]);
    }

    /**
     * GET /lesson-plans/by-filter
     * filter_type=year&year=2026  OR  filter_type=date_range&from_date=&to_date=
     * Requires session_timetableID.
     */
    public function lessonPlansByFilter(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $sessionTimetableID = $request->input('session_timetableID');
        $filterType = $request->input('filter_type', 'year');

        if (!$teacherID || !$sessionTimetableID) {
            return response()->json([
                'success' => false,
                'message' => 'session_timetableID is required.',
            ], 422);
        }

        $query = LessonPlan::where('session_timetableID', $sessionTimetableID)
            ->where('teacherID', $teacherID);

        if ($filterType === 'date_range') {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            if (!$fromDate || !$toDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide both from_date and to_date.',
                ], 422);
            }
            $query->whereBetween('lesson_date', [$fromDate, $toDate]);
        } else {
            $year = $request->input('year') ?: date('Y');
            $query->where(DB::raw('YEAR(lesson_date)'), $year);
        }

        $plans = $query->orderBy('lesson_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'session_timetableID' => (int) $sessionTimetableID,
                'filter_type' => $filterType,
                'lesson_plans' => $plans->map(fn ($p) => $this->shapeLessonPlanSummary($p))->values(),
            ],
        ]);
    }

    private function formatPlanDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)
            ->timezone(config('app.timezone', 'Africa/Dar_es_Salaam'))
            ->format('Y-m-d');
    }

    private function shapeLessonPlanSummary(LessonPlan $plan): array
    {
        return [
            'lesson_planID' => (int) $plan->lesson_planID,
            'session_timetableID' => (int) $plan->session_timetableID,
            'lesson_date' => $this->formatPlanDate($plan->lesson_date),
            'lesson_time_start' => $plan->lesson_time_start,
            'lesson_time_end' => $plan->lesson_time_end,
            'subject' => $plan->subject,
            'class_name' => $plan->class_name,
            'main_competence' => $plan->main_competence,
            'status' => $plan->status,
            'sent_to_admin' => (bool) $plan->sent_to_admin,
            'sent_at' => $plan->sent_at
                ? Carbon::parse($plan->sent_at)->format('Y-m-d H:i:s')
                : null,
            'can_send' => !(bool) $plan->sent_to_admin,
        ];
    }

    private function shapeLessonPlan(LessonPlan $plan): array
    {
        $teacher = $plan->relationLoaded('teacher') ? $plan->teacher : null;
        if (!$teacher && $plan->teacherID) {
            $teacher = Teacher::find($plan->teacherID);
        }
        $teacherName = $teacher
            ? trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''))
            : null;

        $stages = $plan->lesson_stages ?? [];
        if (!is_array($stages)) {
            $stages = [];
        }

        $supervisorSignature = $plan->supervisor_signature;
        if (
            is_string($supervisorSignature) &&
            $supervisorSignature !== '' &&
            !str_starts_with($supervisorSignature, 'data:image') &&
            !str_starts_with($supervisorSignature, 'http')
        ) {
            $supervisorSignature = asset(ltrim($supervisorSignature, '/'));
        }

        return [
            'lesson_planID' => (int) $plan->lesson_planID,
            'session_timetableID' => (int) $plan->session_timetableID,
            'lesson_date' => $this->formatPlanDate($plan->lesson_date),
            'lesson_time_start' => $plan->lesson_time_start,
            'lesson_time_end' => $plan->lesson_time_end,
            'subject' => $plan->subject,
            'class_name' => $plan->class_name,
            'year' => (int) $plan->year,
            'teacher_name' => $teacherName,
            'status' => $plan->status,
            'sent_to_admin' => (bool) $plan->sent_to_admin,
            'sent_at' => $plan->sent_at
                ? Carbon::parse($plan->sent_at)->format('Y-m-d H:i:s')
                : null,
            'attendance' => [
                'registered_girls' => (int) ($plan->registered_girls ?? 0),
                'registered_boys' => (int) ($plan->registered_boys ?? 0),
                'registered_total' => (int) ($plan->registered_total ?? 0),
                'present_girls' => (int) ($plan->present_girls ?? 0),
                'present_boys' => (int) ($plan->present_boys ?? 0),
                'present_total' => (int) ($plan->present_total ?? 0),
            ],
            'main_competence' => $plan->main_competence,
            'specific_competence' => $plan->specific_competence,
            'main_activity' => $plan->main_activity,
            'specific_activity' => $plan->specific_activity,
            'teaching_learning_resources' => $plan->teaching_learning_resources,
            'references' => $plan->references,
            'lesson_stages' => $stages,
            'remarks' => $plan->remarks,
            'reflection' => $plan->reflection,
            'evaluation' => $plan->evaluation,
            'teacher_signature' => $plan->teacher_signature,
            'supervisor_signature' => $supervisorSignature,
            'can_send' => !(bool) $plan->sent_to_admin,
        ];
    }

    public function logbook()
    {
        $result = $this->callWeb(TeachersController::class, 'logbook');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Logbook is not available.');
        }

        $subjects = collect($result->getData()['teachingSubjects'] ?? []);
        $year = (int) date('Y');

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Logbook',
                'current_year' => $year,
                'subjects' => $subjects->map(function ($cs) {
                    $className = $cs->subclass?->class?->class_name
                        ?? $cs->class?->class_name
                        ?? null;
                    $subclassName = $cs->subclass?->subclass_name ?? null;

                    return [
                        'class_subjectID' => (int) $cs->class_subjectID,
                        'subjectID' => (int) $cs->subjectID,
                        'subject_name' => $cs->subject->subject_name ?? null,
                        'class_name' => $className,
                        'subclass_name' => $subclassName,
                        'label' => trim(
                            ($cs->subject->subject_name ?? 'N/A')
                            . ($className ? ' - ' . $className : '')
                            . ($subclassName ? ' ' . $subclassName : '')
                        ),
                        'can_add_entry' => true,
                        'can_download_pdf' => true,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * GET /logbook/{classSubjectID}?year=2026
     * Entries for a subject the teacher teaches (default = current calendar year).
     */
    public function logbookEntries(Request $request, $classSubjectID)
    {
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');

        if (!$teacherID) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $classSubject = ClassSubject::with(['subject', 'subclass.class', 'class'])
            ->where('class_subjectID', $classSubjectID)
            ->where('teacherID', $teacherID)
            ->first();

        if (!$classSubject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
            ], 404);
        }

        $year = (int) ($request->input('year') ?: date('Y'));

        $entries = Logbook::where('class_subjectID', $classSubjectID)
            ->where('schoolID', $schoolID)
            ->where('year', $year)
            ->orderBy('date_started', 'asc')
            ->orderBy('logbookID', 'asc')
            ->get();

        $teacher = Teacher::find($teacherID);
        $teacherName = $teacher
            ? trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''))
            : null;

        $className = $classSubject->subclass?->class?->class_name
            ?? $classSubject->class?->class_name
            ?? null;
        $subclassName = $classSubject->subclass?->subclass_name ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'class_subjectID' => (int) $classSubject->class_subjectID,
                'subject_name' => $classSubject->subject->subject_name ?? null,
                'class_name' => $className,
                'subclass_name' => $subclassName,
                'year' => $year,
                'teacher_name' => $teacherName,
                'can_add_entry' => true,
                'can_download_pdf' => true,
                'entries' => $entries->map(fn ($e) => $this->shapeLogbookEntry($e))->values(),
            ],
        ]);
    }

    private function shapeLogbookEntry(Logbook $entry): array
    {
        return [
            'logbookID' => (int) $entry->logbookID,
            'class_subjectID' => (int) $entry->class_subjectID,
            'main_topic' => $entry->main_topic,
            'sub_topic' => $entry->sub_topic,
            'content_covered' => $entry->content_covered,
            'date_started' => $this->formatPlanDate($entry->date_started),
            'date_ended' => $this->formatPlanDate($entry->date_ended),
            'teacher_comment' => $entry->teacher_comment,
            'hod_comment' => $entry->hod_comment,
            'hm_comment' => $entry->hm_comment,
            'year' => (int) ($entry->year ?? 0),
            'status' => $entry->status,
            'hod_signed' => filled($entry->hod_comment),
            'hm_signed' => filled($entry->hm_comment),
        ];
    }

    public function departmentLogbook()
    {
        $result = $this->callWeb(TeachersController::class, 'departmentLogbook');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'You are not assigned as HOD of any department.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Department Logbooks',
                'web_path' => '/teacher/department-logbook',
                'api_paths' => ['/department-logbook', '/teacher/department-logbook'],
                'current_year' => (int) date('Y'),
                'is_hod' => true,
                'departments' => collect($d['departments'] ?? [])->map(function ($dept) {
                    return [
                        'departmentID' => (int) $dept->departmentID,
                        'department_name' => $dept->department_name ?? $dept->name ?? null,
                        'subject_ids' => collect($dept->subjects ?? [])->pluck('subjectID')->map(fn ($id) => (int) $id)->values(),
                    ];
                })->values(),
                'subjects' => collect($d['subjects'] ?? [])->map(fn ($s) => [
                    'subjectID' => (int) $s->subjectID,
                    'subject_name' => $s->subject_name ?? null,
                ])->values(),
                'classes' => collect($d['classes'] ?? [])->map(fn ($c) => [
                    'classID' => (int) $c->classID,
                    'class_name' => $c->class_name ?? null,
                ])->values(),
                'actions' => [
                    'subclasses' => 'GET /department-logbook/subclasses/{classID}',
                    'entries' => 'GET /department-logbook/data?subjectID=&subclassID=',
                    'entries_alias' => 'GET /teacher/get-department-logbook-data?subjectID=&subclassID=',
                    'sign' => 'POST /logbook/sign',
                    'pdf' => 'GET /logbook/{classSubjectID}/pdf',
                ],
                'flow' => 'Pick subject → class → GET subclasses → pick stream → GET entries → optional HOD sign / PDF',
            ],
        ]);
    }

    /**
     * GET /department-logbook/data?subjectID=&subclassID=&year=
     * HOD view of a subject's stream logbook for the year.
     */
    public function departmentLogbookEntries(Request $request)
    {
        $result = $this->callWeb(TeachersController::class, 'getDepartmentLogbookData');
        if (!$result instanceof JsonResponse) {
            return $this->forbiddenOrPassthrough($result, 'Unable to load department logbook.');
        }

        $payload = $result->getData(true);
        if (!($payload['success'] ?? false)) {
            $status = $result->getStatusCode();
            if ($status < 400) {
                $status = 400;
            }

            return response()->json([
                'success' => false,
                'message' => $payload['message'] ?? $payload['error'] ?? 'Unable to load department logbook.',
            ], $status);
        }

        $entries = collect($payload['logbookEntries'] ?? [])->map(function ($raw) {
            $entry = is_array($raw) ? (object) $raw : $raw;

            return [
                'logbookID' => (int) ($entry->logbookID ?? 0),
                'class_subjectID' => (int) ($entry->class_subjectID ?? 0),
                'main_topic' => $entry->main_topic ?? null,
                'sub_topic' => $entry->sub_topic ?? null,
                'content_covered' => $entry->content_covered ?? null,
                'date_started' => $this->formatPlanDate($entry->date_started ?? null),
                'date_ended' => $this->formatPlanDate($entry->date_ended ?? null),
                'teacher_comment' => $entry->teacher_comment ?? null,
                'hod_comment' => $entry->hod_comment ?? null,
                'hm_comment' => $entry->hm_comment ?? null,
                'year' => (int) ($entry->year ?? 0),
                'status' => $entry->status ?? null,
                'hod_signed' => filled($entry->hod_comment ?? null),
                'hm_signed' => filled($entry->hm_comment ?? null),
                'can_sign' => !filled($entry->hod_comment ?? null),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'class_subjectID' => (int) ($payload['classSubjectID'] ?? 0),
                'subject_name' => $payload['subjectName'] ?? null,
                'class_name' => $payload['className'] ?? null,
                'subclass_name' => $payload['subclassName'] ?? null,
                'year' => (int) ($payload['year'] ?? date('Y')),
                'teacher_name' => $payload['teacherName'] ?? null,
                'can_sign' => true,
                'can_download_pdf' => (int) ($payload['classSubjectID'] ?? 0) > 0,
                'entries' => $entries,
            ],
        ]);
    }

    public function calendar(Request $request)
    {
        // Keep query year in the request so teacherCalendar() sees it.
        $result = $this->callWeb(CalendarController::class, 'teacherCalendar');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Calendar is not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'year' => (int) ($d['currentYear'] ?? date('Y')),
                'year_ids' => $d['yearIds'] ?? [],
                'stats' => $d['stats'] ?? null,
                'monthly_stats' => $d['monthlyStats'] ?? null,
                'holidays' => collect($d['holidays'] ?? [])->map(fn ($h) => [
                    'holidayID' => (int) ($h->holidayID ?? 0),
                    'holiday_name' => $h->holiday_name ?? null,
                    'start_date' => optional($h->start_date)->format('Y-m-d') ?? (string) ($h->start_date ?? ''),
                    'end_date' => optional($h->end_date)->format('Y-m-d') ?? (string) ($h->end_date ?? ''),
                    'description' => $h->description ?? null,
                    'type' => $h->type ?? null,
                    'has_working_days' => (bool) ($h->has_working_days ?? false),
                    'working_days_count' => (int) ($h->working_days_count ?? 0),
                ])->values(),
                'events' => collect($d['events'] ?? [])->map(fn ($e) => [
                    'eventID' => (int) ($e->eventID ?? 0),
                    'event_name' => $e->event_name ?? null,
                    'event_date' => optional($e->event_date)->format('Y-m-d') ?? (string) ($e->event_date ?? ''),
                    'start_time' => $e->start_time ?? null,
                    'end_time' => $e->end_time ?? null,
                    'description' => $e->description ?? null,
                    'type' => $e->type ?? null,
                    'is_non_working_day' => (bool) ($e->is_non_working_day ?? false),
                ])->values(),
                'auto_holidays' => $d['autoHolidays'] ?? [],
            ],
        ]);
    }

    /**
     * GET /scheme-of-work/{schemeOfWorkID}
     * Same content as /teacher/scheme-of-work/view/{id} (read-only table).
     */
    public function viewScheme(int $schemeOfWorkID)
    {
        $result = $this->callWeb(
            TeachersController::class,
            'viewSchemeOfWork',
            ['schemeOfWorkID' => $schemeOfWorkID]
        );
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Scheme of work not found or not accessible.');
        }

        $d = $result->getData();
        $scheme = $d['scheme'] ?? null;
        $school = $d['school'] ?? null;
        if (!$scheme) {
            return response()->json(['success' => false, 'message' => 'Scheme of work not found.'], 404);
        }

        $header = $this->schemeHeader($scheme, $school);

        return response()->json([
            'success' => true,
            'data' => [
                'scheme_of_workID' => (int) $scheme->scheme_of_workID,
                'class_subjectID' => (int) $scheme->class_subjectID,
                'school_name' => $header['school_name'],
                'teacher_name' => $header['teacher_name'],
                'subject' => $header['subject'],
                'year' => $header['year'],
                'class' => $header['class'],
                'learning_objectives_title' => $header['learning_objectives_title'],
                'learning_objectives' => $this->shapeObjectives($scheme),
                'table' => $this->shapeViewTable($scheme),
            ],
        ]);
    }

    /**
     * GET /scheme-of-work/{schemeOfWorkID}/manage
     * Same content as /teacher/scheme-of-work/manage/{id} (editable table + holidays).
     */
    public function manageScheme(int $schemeOfWorkID)
    {
        $result = $this->callWeb(
            TeachersController::class,
            'manageSchemeOfWork',
            ['schemeOfWorkID' => $schemeOfWorkID]
        );
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Scheme of work not found or not accessible.');
        }

        $d = $result->getData();
        $scheme = $d['scheme'] ?? null;
        if (!$scheme) {
            return response()->json(['success' => false, 'message' => 'Scheme of work not found.'], 404);
        }

        $header = $this->schemeHeader($scheme, null);

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Manage Scheme of Work',
                'scheme_of_workID' => (int) $scheme->scheme_of_workID,
                'class_subjectID' => (int) $scheme->class_subjectID,
                'subject' => $header['subject_display'],
                'class' => $header['class'],
                'year' => $header['year'],
                'learning_objectives_title' => 'Define Learning Objectives',
                'learning_objectives' => $this->shapeObjectives($scheme, true),
                'table' => $this->shapeManageTable($scheme, collect($d['allHolidays'] ?? [])),
            ],
        ]);
    }

    private function schemeHeader($scheme, $school = null): array
    {
        $cs = $scheme->classSubject;
        $className = 'N/A';
        if ($cs?->subclass?->class) {
            $className = strtoupper(trim(($cs->subclass->class->class_name ?? '') . ' ' . ($cs->subclass->subclass_name ?? '')));
        } elseif ($cs?->class) {
            $className = strtoupper((string) $cs->class->class_name);
        }

        $createdBy = $scheme->createdBy;
        $teacherName = $createdBy
            ? trim(($createdBy->first_name ?? '') . ' ' . ($createdBy->last_name ?? ''))
            : '';
        $subject = strtoupper((string) ($cs->subject->subject_name ?? 'N/A'));
        $schoolType = $school->school_type ?? null;
        $objectivesTitle = ($schoolType && stripos((string) $schoolType, 'primary') !== false)
            ? 'Objectives of Primary Education'
            : 'Learning Objectives';

        return [
            'school_name' => $school->school_name ?? null,
            'teacher_name' => $teacherName !== '' ? $teacherName : null,
            'subject' => $subject,
            'subject_display' => $cs->subject->subject_name ?? null,
            'year' => (int) $scheme->year,
            'class' => $className,
            'learning_objectives_title' => $objectivesTitle,
        ];
    }

    private function shapeObjectives($scheme, bool $withIds = false)
    {
        return collect($scheme->learningObjectives ?? [])
            ->sortBy(fn ($o) => (int) ($o->order ?? 0))
            ->values()
            ->map(function ($o) use ($withIds) {
                if ($withIds) {
                    return [
                        'objectiveID' => (int) ($o->objectiveID ?? $o->objective_id ?? $o->id ?? 0),
                        'text' => (string) ($o->objective_text ?? ''),
                    ];
                }

                return (string) ($o->objective_text ?? '');
            })
            ->values();
    }

    /**
     * View table: only months that have items (same as the website view page).
     */
    private function shapeViewTable($scheme)
    {
        $monthOrder = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        $itemsByMonth = collect($scheme->items ?? [])->groupBy('month');
        $months = collect($monthOrder)
            ->filter(fn ($m) => $itemsByMonth->has($m))
            ->merge($itemsByMonth->keys()->diff($monthOrder))
            ->values();

        $rows = [];
        $rowNumber = 1;
        foreach ($months as $month) {
            foreach ($itemsByMonth->get($month, collect()) as $item) {
                $rows[] = $this->tableItemRow($rowNumber++, $item, $month);
            }
        }

        return $rows;
    }

    /**
     * Manage table: all 12 months, empty month placeholders, holidays after each month (website manage page).
     */
    private function shapeManageTable($scheme, $holidays)
    {
        $monthOrder = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        $itemsByMonth = collect($scheme->items ?? [])->groupBy('month');
        $rows = [];
        $rowNumber = 1;

        foreach ($monthOrder as $month) {
            $monthItems = $itemsByMonth->get($month, collect());
            if ($monthItems->count() > 0) {
                foreach ($monthItems as $item) {
                    $rows[] = $this->tableItemRow($rowNumber++, $item, $month, true);
                }
            } else {
                $rows[] = [
                    'row_number' => $rowNumber++,
                    'type' => 'empty_month',
                    'itemID' => null,
                    'main_competence' => '',
                    'specific_competences' => '',
                    'learning_activities' => '',
                    'specific_activities' => '',
                    'month' => $month,
                    'week' => '',
                    'number_of_periods' => '',
                    'teaching_methods' => '',
                    'teaching_resources' => '',
                    'assessment_tools' => '',
                    'references' => '',
                    'remarks' => '',
                    'is_done' => false,
                ];
            }

            $monthHolidays = $holidays->filter(function ($holiday) use ($month) {
                $holiday = (array) $holiday;

                return ($holiday['start_month'] ?? null) === $month
                    || ($holiday['end_month'] ?? null) === $month;
            });

            foreach ($monthHolidays as $holiday) {
                $holiday = (array) $holiday;
                $start = $holiday['start_date'] ?? null;
                $labelDate = $start ? Carbon::parse($start)->format('j.n.Y') : '';
                $name = $holiday['name'] ?? 'Holiday';
                $rows[] = [
                    'type' => 'holiday',
                    'month' => $month,
                    'label' => trim($name . ($labelDate !== '' ? ' (' . $labelDate . ')' : '')),
                    'name' => $name,
                    'start_date' => $holiday['start_date'] ?? null,
                    'end_date' => $holiday['end_date'] ?? null,
                ];
            }
        }

        return $rows;
    }

    private function tableItemRow(int $rowNumber, $item, string $month, bool $forManage = false): array
    {
        $remarks = (string) ($item->remarks ?? '');

        return [
            'row_number' => $rowNumber,
            'type' => 'item',
            'itemID' => (int) $item->itemID,
            'main_competence' => (string) ($item->main_competence ?? ''),
            'specific_competences' => (string) ($item->specific_competences ?? ''),
            'learning_activities' => (string) ($item->learning_activities ?? ''),
            'specific_activities' => (string) ($item->specific_activities ?? ''),
            'month' => $month,
            'week' => $item->week !== null && $item->week !== '' ? (string) $item->week : '',
            'number_of_periods' => $item->number_of_periods !== null && $item->number_of_periods !== ''
                ? (int) $item->number_of_periods
                : ($forManage ? '' : ''),
            'teaching_methods' => (string) ($item->teaching_methods ?? ''),
            'teaching_resources' => (string) ($item->teaching_resources ?? ''),
            'assessment_tools' => (string) ($item->assessment_tools ?? ''),
            'references' => (string) ($item->references ?? ''),
            'remarks' => $remarks,
            'is_done' => strtolower(trim($remarks)) === 'done',
        ];
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

        return $result;
    }
}
