<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AccomodationController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ManageClassessController;
use App\Http\Controllers\ManageExaminationController;
use App\Http\Controllers\ManageStudentController;
use App\Http\Controllers\ManageSubjectController;
use App\Http\Controllers\ResultManagementController;
use App\Http\Controllers\TimeTableController;
use App\Support\ManagementScreenUi;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hubs for teacher Management sidebar pages.
 * Each hub mirrors the website screen: toolbar (SETTINGS/ACTION) + cards/rows + card actions.
 */
class TeacherManagementMobileApiController extends Controller
{
    private const MODULES = [
        ['key' => 'examination', 'category' => 'examination', 'title' => 'Examinations', 'web_path' => '/manageExamination', 'api_path' => '/manageExamination'],
        ['key' => 'result', 'category' => 'result', 'title' => 'Results', 'web_path' => '/manageResults', 'api_path' => '/manageResults'],
        ['key' => 'subject', 'category' => 'subject', 'title' => 'Subjects', 'web_path' => '/manageSubjects', 'api_path' => '/manageSubjects'],
        ['key' => 'classes', 'category' => 'classes', 'title' => 'Classes', 'web_path' => '/manageClasses', 'api_path' => '/manageClasses'],
        ['key' => 'student', 'category' => 'student', 'title' => 'Students', 'web_path' => '/manage_student', 'api_path' => '/manage_student'],
        ['key' => 'timetable', 'category' => 'timetable', 'title' => 'Timetable', 'web_path' => '/timeTable', 'api_path' => '/timeTable'],
        ['key' => 'fees', 'category' => 'fees', 'title' => 'Fees', 'web_path' => '/manage_fees', 'api_path' => '/manage_fees'],
        ['key' => 'library', 'category' => 'library', 'title' => 'Library', 'web_path' => '/manage_library', 'api_path' => '/manage_library'],
        ['key' => 'accommodation', 'category' => 'accommodation', 'title' => 'Hostel / Accommodation', 'web_path' => '/manage_accomodation', 'api_path' => '/manage_accomodation'],
    ];

    public function index(Request $request)
    {
        $caps = app(TeacherMobileApiController::class)->capabilities($request)->getData(true)['data'] ?? [];
        $allowed = collect($caps['management_permission_categories'] ?? [])->map(fn ($c) => (string) $c)->all();

        $modules = collect(self::MODULES)->map(function ($m) use ($allowed) {
            $ok = in_array($m['category'], $allowed, true);

            return [
                'key' => $m['key'],
                'category' => $m['category'],
                'title' => $m['title'],
                'web_path' => $m['web_path'],
                'api_path' => $m['api_path'],
                'visible' => $ok,
                'locked' => !$ok,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Management',
                'modules' => $modules,
                'visible_modules' => $modules->where('visible', true)->values(),
                'note' => 'Hide locked modules. Each hub returns toolbar + cards like the website.',
            ],
        ]);
    }

    /** GET /manageClasses — website cards + SETTINGS toolbar */
    public function classes(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'classes')) {
            return $deny;
        }

        $result = $this->callWeb(ManageClassessController::class, 'manageClasses');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Classes management is not available.');
        }

        $d = $result->getData();
        [$names, $isAdmin] = $this->permContext($request);
        $schoolType = $d['school_details']->school_type ?? 'Secondary';
        $isSecondary = $schoolType === 'Secondary';

        $cards = collect($d['classes'] ?? [])->map(function ($c) use ($names, $isAdmin) {
            $status = (string) ($c['status'] ?? 'Inactive');

            return [
                'classID' => (int) ($c['classID'] ?? 0),
                'title' => $c['class_name'] ?? null,
                'class_name' => $c['class_name'] ?? null,
                'status' => $status,
                'subclass_count' => (int) ($c['subclass_count'] ?? 0),
                'student_count' => (int) ($c['student_count'] ?? 0),
                'subject_count' => (int) ($c['subject_count'] ?? 0),
                'coordinator' => $c['coordinator'] ?? null,
                'coordinator_label' => $c['coordinator'] ? ('Coordinator: ' . $c['coordinator']) : 'Coordinator: Not Assigned',
                'description' => $c['description'] ?? null,
                'teacherID' => isset($c['teacherID']) ? (int) $c['teacherID'] : null,
                'stats' => [
                    ['key' => 'subclasses', 'label' => 'Subclasses', 'value' => (int) ($c['subclass_count'] ?? 0)],
                    ['key' => 'active_enrolled', 'label' => 'Active enrolled', 'value' => (int) ($c['student_count'] ?? 0)],
                ],
                'actions' => ManagementScreenUi::classCardActions($names, $isAdmin, $status),
                'subclasses' => collect($c['subclasses'] ?? [])->map(fn ($s) => [
                    'subclassID' => (int) ($s['subclassID'] ?? 0),
                    'subclass_name' => $s['subclass_name'] ?? null,
                    'stream_code' => $s['stream_code'] ?? null,
                    'status' => $s['status'] ?? null,
                    'teacher_name' => $s['teacher_name'] ?? null,
                    'student_count' => (int) ($s['student_count'] ?? 0),
                    'combie_name' => $s['combie_name'] ?? null,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE CLASSES & SUBCLASSES',
                'subtitle' => 'Define institutional structure, streams, and academic grading models.',
                'web_path' => '/manageClasses',
                'category' => 'classes',
                'settings_label' => 'SETTINGS',
                'academic_year_label' => $d['academicYearLabel'] ?? null,
                'school_type' => $schoolType,
                'toolbar' => ManagementScreenUi::classesToolbar($names, $isAdmin, $isSecondary),
                'cards' => $cards,
                'combies' => collect($d['combies'] ?? [])->map(fn ($c) => [
                    'combieID' => (int) ($c->combieID ?? 0),
                    'combie_name' => $c->combie_name ?? null,
                    'combie_code' => $c->combie_code ?? null,
                ])->values(),
                'teachers' => $this->mapTeachers($d['teachers'] ?? []),
                'ui' => [
                    'layout' => 'class_cards',
                    'card_fields' => ['title', 'status', 'stats.subclasses', 'stats.active_enrolled', 'coordinator'],
                    'card_actions_note' => 'Edit / Deactivate|Activate / Delete appear on each card when permitted.',
                ],
            ],
        ]);
    }

    /** GET /manageSubjects */
    public function subjects(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'subject')) {
            return $deny;
        }

        $result = $this->callWeb(ManageSubjectController::class, 'manageSubjects');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Subjects management is not available.');
        }

        $d = $result->getData();
        [$names, $isAdmin] = $this->permContext($request);

        $cards = collect($d['schoolSubjects'] ?? [])->map(function ($s) use ($names, $isAdmin) {
            $status = (string) ($s->status ?? 'Inactive');
            $components = collect($s->components ?? [])->map(fn ($c) => [
                'component_name' => $c->component_name ?? null,
                'max_marks' => $c->max_marks ?? null,
            ])->values();

            return [
                'subjectID' => (int) ($s->subjectID ?? 0),
                'title' => $s->subject_name ?? null,
                'subject_name' => $s->subject_name ?? null,
                'subject_code' => $s->subject_code ?? null,
                'status' => $status,
                'is_duo' => (bool) ($s->is_duo ?? false),
                'papers' => $components,
                'actions' => ManagementScreenUi::subjectCardActions($names, $isAdmin, $status),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE SUBJECTS',
                'web_path' => '/manageSubjects',
                'category' => 'subject',
                'settings_label' => 'SETTINGS',
                'toolbar' => ManagementScreenUi::subjectsToolbar($names, $isAdmin),
                'cards' => $cards,
                'subclasses' => $this->mapSubclasses($d['subclasses'] ?? []),
                'teachers' => $this->mapTeachers($d['teachers'] ?? []),
                'ui' => [
                    'layout' => 'subject_cards',
                    'card_fields' => ['title', 'subject_code', 'papers', 'status'],
                ],
            ],
        ]);
    }

    /** GET /manage_library */
    public function library(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'library')) {
            return $deny;
        }

        $result = $this->callWeb(LibraryController::class, 'manage_library');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Library management is not available.');
        }

        $d = $result->getData();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE LIBRARY',
                'web_path' => '/manage_library',
                'category' => 'library',
                'settings_label' => 'ACTION',
                'toolbar' => ManagementScreenUi::libraryToolbar(true),
                'summary' => [
                    ['key' => 'total', 'label' => 'TOTAL', 'value' => (int) ($d['totalBooks'] ?? 0)],
                    ['key' => 'available', 'label' => 'AVAILABLE', 'value' => (int) ($d['availableBooks'] ?? 0)],
                    ['key' => 'borrowed', 'label' => 'BORROWED', 'value' => (int) ($d['borrowedBooks'] ?? 0)],
                    ['key' => 'overdue', 'label' => 'OVERDUE', 'value' => (int) ($d['overdueBooks'] ?? 0)],
                    ['key' => 'lost', 'label' => 'LOST', 'value' => (int) ($d['lostBooks'] ?? 0)],
                    ['key' => 'damaged', 'label' => 'DAMAGED', 'value' => (int) ($d['damagedBooks'] ?? 0)],
                ],
                'card_actions_template' => ManagementScreenUi::libraryCardActions(true),
                'classes' => $this->mapClasses($d['classes'] ?? []),
                'subjects' => collect($d['subjects'] ?? [])->map(fn ($s) => [
                    'subjectID' => (int) ($s->subjectID ?? 0),
                    'subject_name' => $s->subject_name ?? null,
                ])->values(),
                'ui' => [
                    'layout' => 'library_catalog',
                    'load_cards' => 'GET /get_books',
                    'note' => 'BOOKS CATALOG cards load via GET /get_books. Attach card_actions_template to each book.',
                ],
            ],
        ]);
    }

    /** GET /manage_fees */
    public function fees(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'fees')) {
            return $deny;
        }

        $result = $this->callWeb(FeesController::class, 'manage_fees');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Fees management is not available.');
        }

        $d = $result->getData();
        [, $isAdmin] = $this->permContext($request);
        $rowActions = ManagementScreenUi::feeRowActions(true);

        $mapFee = function ($f) use ($rowActions) {
            return [
                'feeID' => (int) ($f->feeID ?? 0),
                'title' => $f->fee_name ?? null,
                'fee_name' => $f->fee_name ?? null,
                'amount' => $f->amount ?? null,
                'classID' => isset($f->classID) ? (int) $f->classID : null,
                'class_name' => $f->class->class_name ?? null,
                'duration' => $f->duration ?? null,
                'status' => $f->status ?? null,
                'account_mode' => $f->account_mode ?? 'single',
                'description' => $f->description ?? null,
                'actions' => $rowActions,
            ];
        };

        $single = collect($d['singleFeesByClass'] ?? [])->map(function ($fees, $classID) use ($mapFee) {
            $list = collect($fees)->map($mapFee)->values();
            $first = $list->first();

            return [
                'classID' => (int) $classID,
                'class_name' => $first['class_name'] ?? null,
                'total_amount' => $list->sum(fn ($f) => (float) ($f['amount'] ?? 0)),
                'fees' => $list,
            ];
        })->values();

        $multi = collect($d['multiFeesByClass'] ?? [])->map(function ($fees, $classID) use ($mapFee) {
            $list = collect($fees)->map($mapFee)->values();
            $first = $list->first();

            return [
                'classID' => (int) $classID,
                'class_name' => $first['class_name'] ?? null,
                'fees' => $list,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE FEES',
                'web_path' => '/manage_fees',
                'category' => 'fees',
                'settings_label' => 'SETTINGS',
                'toolbar' => ManagementScreenUi::feesToolbar(true, $isAdmin),
                'tabs' => [
                    ['key' => 'single', 'label' => 'Single Account Fee', 'groups' => $single],
                    ['key' => 'multi', 'label' => 'Multi Account Fee', 'groups' => $multi],
                ],
                'classes' => $this->mapClasses($d['classes'] ?? []),
                'ui' => [
                    'layout' => 'fees_by_class_table',
                    'row_fields' => ['class_name', 'fee_name', 'amount', 'deadline'],
                ],
            ],
        ]);
    }

    /** GET /manageExamination */
    public function examinations(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'examination')) {
            return $deny;
        }

        $result = $this->callWeb(ManageExaminationController::class, 'manageExamination');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Examinations management is not available.');
        }

        $d = $result->getData();
        [$names, $isAdmin] = $this->permContext($request);

        $mapExam = function ($e) use ($names, $isAdmin) {
            $approval = $e->approval_status ?? null;

            return [
                'examID' => (int) ($e->examID ?? 0),
                'title' => $e->exam_name ?? null,
                'exam_name' => $e->exam_name ?? null,
                'exam_category' => $e->exam_category ?? null,
                'term' => $e->term ?? null,
                'year' => $e->year ?? null,
                'status' => $e->status ?? null,
                'approval_status' => $approval,
                'enter_result' => (bool) ($e->enter_result ?? false),
                'publish_result' => (bool) ($e->publish_result ?? false),
                'start_date' => $e->start_date ?? null,
                'end_date' => $e->end_date ?? null,
                'expected_students' => (int) ($e->expected_students ?? 0),
                'students_with_marks' => (int) ($e->students_with_marks ?? 0),
                'days_remaining' => $e->days_remaining ?? null,
                'subtitle' => trim(($e->term ?? '') . ' • ' . ($e->year ?? '')),
                'actions' => ManagementScreenUi::examinationCardActions($names, $isAdmin, $approval),
            ];
        };

        $grouped = $d['examinationsGrouped'] ?? null;
        if (is_array($grouped) || $grouped instanceof \Illuminate\Support\Collection) {
            $tabs = collect($grouped)->map(function ($items, $key) use ($mapExam) {
                $list = $items instanceof \Illuminate\Contracts\Pagination\Paginator
                    ? collect($items->items())
                    : collect($items);

                return [
                    'key' => (string) $key,
                    'label' => match ((string) $key) {
                        'school_exams' => 'School Exams',
                        'test' => 'Test',
                        'special_exams' => 'Special Exams',
                        default => ucwords(str_replace('_', ' ', (string) $key)),
                    },
                    'cards' => $list->map($mapExam)->values(),
                ];
            })->values();
        } else {
            $examsRaw = $d['examinations'] ?? [];
            $items = $examsRaw instanceof \Illuminate\Contracts\Pagination\Paginator
                ? collect($examsRaw->items())
                : collect($examsRaw);
            $tabs = [[
                'key' => 'all',
                'label' => 'Examinations',
                'cards' => $items->map($mapExam)->values(),
            ]];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE EXAMINATIONS',
                'web_path' => '/manageExamination',
                'category' => 'examination',
                'toolbar' => ManagementScreenUi::examinationToolbar($names, $isAdmin),
                'filters' => [
                    'search' => $d['search'] ?? null,
                    'year' => $d['yearFilter'] ?? null,
                    'status' => $d['statusFilter'] ?? null,
                    'term' => $d['termFilter'] ?? null,
                    'exam_category' => $d['examCategoryFilter'] ?? null,
                    'academic_year_label' => $d['headerAcademicYearLabel'] ?? null,
                ],
                'total_exams' => (int) ($d['totalExams'] ?? 0),
                'tabs' => $tabs,
                'ui' => [
                    'layout' => 'exam_list_or_grid',
                    'card_fields' => ['title', 'subtitle', 'expected_students', 'status', 'approval_status'],
                ],
            ],
        ]);
    }

    /** GET /manageResults */
    public function results(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'result')) {
            return $deny;
        }

        $result = $this->callWeb(ResultManagementController::class, 'index');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Results management is not available.');
        }

        $d = $result->getData();
        $isTeacherView = (bool) ($d['isTeacherView'] ?? false);
        $rowActions = ManagementScreenUi::resultsRowActions(true);

        $students = collect($d['students'] ?? [])->take(100)->map(function ($s) use ($rowActions) {
            if (is_array($s)) {
                return [
                    'studentID' => (int) ($s['studentID'] ?? 0),
                    'title' => $s['full_name'] ?? trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                    'admission_number' => $s['admission_number'] ?? null,
                    'class_name' => $s['class_name'] ?? null,
                    'enrollment_status' => $s['enrollment_status'] ?? null,
                    'actions' => $rowActions,
                ];
            }

            return [
                'studentID' => (int) ($s->studentID ?? 0),
                'title' => $s->full_name ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')),
                'admission_number' => $s->admission_number ?? null,
                'class_name' => $s->class_name ?? ($s->class->class_name ?? null),
                'enrollment_status' => $s->enrollment_status ?? null,
                'actions' => $rowActions,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE RESULTS',
                'web_path' => '/manageResults',
                'category' => 'result',
                'toolbar' => ManagementScreenUi::resultsToolbar(true, $isTeacherView),
                'school_type' => $d['schoolType'] ?? null,
                'academic_year_label' => $d['academicYearLabel'] ?? null,
                'filters' => $d['filters'] ?? null,
                'available_exams' => collect($d['availableExams'] ?? [])->map(function ($e) {
                    if (is_array($e)) {
                        return $e;
                    }

                    return [
                        'examID' => (int) ($e->examID ?? 0),
                        'exam_name' => $e->exam_name ?? null,
                        'term' => $e->term ?? null,
                        'year' => $e->year ?? null,
                    ];
                })->values(),
                'classes' => $this->mapClasses($d['classes'] ?? []),
                'subclasses' => $this->mapSubclasses($d['subclasses'] ?? []),
                'exam_status_message' => $d['examStatusMessage'] ?? null,
                'has_results_data' => !empty($d['resultsData']),
                'students_preview' => $students,
                'students_count' => collect($d['students'] ?? [])->count(),
                'ui' => [
                    'layout' => 'results_filter_then_student_rows',
                    'note' => 'Apply filters then show student rows. Use Export / SMS / Publish from toolbar.',
                ],
            ],
        ]);
    }

    /** GET /manage_student */
    public function students(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'student')) {
            return $deny;
        }

        $result = $this->callWeb(ManageStudentController::class, 'manage_student');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Students management is not available.');
        }

        $d = $result->getData();
        [$names, $isAdmin] = $this->permContext($request);

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE STUDENTS',
                'web_path' => '/manage_student',
                'category' => 'student',
                'settings_label' => 'Actions',
                'toolbar' => ManagementScreenUi::studentToolbar($names, $isAdmin),
                'row_actions_template' => ManagementScreenUi::studentRowActions($names, $isAdmin),
                'classes' => $this->mapClasses($d['classes'] ?? []),
                'academic_years' => collect($d['academicYears'] ?? [])->map(fn ($y) => [
                    'academic_yearID' => (int) ($y->academic_yearID ?? 0),
                    'year' => $y->year ?? null,
                    'year_name' => $y->year_name ?? null,
                    'is_current' => (bool) ($y->is_current ?? false),
                ])->values(),
                'has_a_level' => (bool) ($d['hasALevel'] ?? false),
                'ui' => [
                    'layout' => 'student_list_or_grid',
                    'load_cards' => 'GET /get_students_list',
                    'stats' => 'GET /get_student_statistics',
                    'note' => 'Landing shell + Actions menu. Load list/grid via GET /get_students_list; attach row_actions_template.',
                ],
            ],
        ]);
    }

    /** GET /timeTable */
    public function timetable(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'timetable')) {
            return $deny;
        }

        $result = $this->callWeb(TimeTableController::class, 'timeTable');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Timetable management is not available.');
        }

        $d = $result->getData();
        [$names, $isAdmin] = $this->permContext($request);

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'MANAGE TIMETABLE',
                'web_path' => '/timeTable',
                'category' => 'timetable',
                'toolbar' => ManagementScreenUi::timetableToolbar($names, $isAdmin),
                'tabs' => [
                    ['key' => 'class', 'label' => 'Class Timetable'],
                    ['key' => 'school', 'label' => 'School Timetable'],
                    ['key' => 'exam', 'label' => 'Exam Timetable'],
                    ['key' => 'test', 'label' => 'Test Schedules'],
                ],
                'examinations' => collect($d['examinations'] ?? [])->map(fn ($e) => [
                    'examID' => (int) ($e->examID ?? 0),
                    'exam_name' => $e->exam_name ?? null,
                    'term' => $e->term ?? null,
                    'year' => $e->year ?? null,
                ])->values(),
                'classes' => $this->mapClasses($d['classes'] ?? []),
                'subclasses' => $this->mapSubclasses($d['subclasses'] ?? []),
                'teachers' => $this->mapTeachers($d['teachers'] ?? []),
                'subjects' => collect($d['schoolSubjects'] ?? [])->map(fn ($s) => [
                    'subjectID' => (int) ($s->subjectID ?? 0),
                    'subject_name' => $s->subject_name ?? null,
                ])->values(),
                'ui' => [
                    'layout' => 'timetable_tabs',
                    'empty_state' => 'Select a class to view session timetable',
                    'load_session' => 'GET /admin/get-session-timetable?subclassID={subclassID}',
                    'load_session_note' => 'Same as website: user must pick a subclass from subclasses[] before Load. Do not send schoolID — it comes from the Bearer token session.',
                ],
            ],
        ]);
    }

    /** GET /manage_accomodation */
    public function accommodation(Request $request)
    {
        if ($deny = $this->denyUnless($request, 'accommodation')) {
            return $deny;
        }

        $result = $this->callWeb(AccomodationController::class, 'manage_accomodation');
        if ($result instanceof RedirectResponse) {
            return $this->forbiddenOrPassthrough($result, 'Accommodation management is not available.');
        }

        $blocks = $this->callWeb(AccomodationController::class, 'apiBlocksIndex');
        $blockList = collect();
        if ($blocks instanceof JsonResponse) {
            $raw = $blocks->getData(true);
            if (($raw['success'] ?? false) && isset($raw['data'])) {
                $blockList = collect($raw['data']);
            } elseif (is_array($raw) && array_is_list($raw)) {
                $blockList = collect($raw);
            }
        }

        $cardActions = ManagementScreenUi::accommodationBlockActions(true);
        $cards = $blockList->map(function ($b) use ($cardActions) {
            $b = (array) $b;

            return [
                'blockID' => (int) ($b['blockID'] ?? 0),
                'title' => $b['blockName'] ?? $b['block_name'] ?? null,
                'location' => $b['location'] ?? null,
                'rooms_count' => (int) ($b['rooms'] ?? $b['rooms_count'] ?? 0),
                'beds_count' => (int) ($b['beds'] ?? $b['beds_count'] ?? 0),
                'block_type' => $b['blockType'] ?? $b['block_type'] ?? null,
                'block_sex' => $b['blockSex'] ?? $b['block_sex'] ?? null,
                'status' => $b['status'] ?? null,
                'actions' => $cardActions,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'HOSTEL / ACCOMMODATION',
                'web_path' => '/manage_accomodation',
                'category' => 'accommodation',
                'toolbar' => ManagementScreenUi::accommodationToolbar(true),
                'cards' => $cards,
                'ui' => [
                    'layout' => 'accommodation_sidebar',
                    'sidebar' => ['Register Block', 'View Blocks', 'Register Room', 'Register Beds', 'View Beds'],
                ],
            ],
        ]);
    }

    /** @return array{0: array<int,string>, 1: bool} */
    private function permContext(Request $request): array
    {
        $teacher = $request->attributes->get('mobile_teacher');
        $isAdmin = Session::get('user_type') === 'Admin';
        if (!$teacher) {
            return [[], $isAdmin];
        }
        $roleIds = DB::table('role_user')->where('teacher_id', $teacher->id)->pluck('role_id');
        if ($roleIds->isEmpty()) {
            return [[], $isAdmin];
        }
        $names = DB::table('permissions')
            ->whereIn('role_id', $roleIds)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        return [$names, $isAdmin];
    }

    private function denyUnless(Request $request, string $category): ?JsonResponse
    {
        $ok = app(TeacherMobileApiController::class)->hasPermissionCategory($request, $category);
        if ($ok) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have access to this module.',
            'error_code' => 'permission_category_required',
            'permission_category' => $category,
        ], 403);
    }

    private function mapClasses($items)
    {
        return collect($items)->map(function ($c) {
            if (is_array($c)) {
                return [
                    'classID' => (int) ($c['classID'] ?? 0),
                    'class_name' => $c['class_name'] ?? null,
                    'status' => $c['status'] ?? null,
                ];
            }

            return [
                'classID' => (int) ($c->classID ?? 0),
                'class_name' => $c->class_name ?? null,
                'status' => $c->status ?? null,
            ];
        })->values();
    }

    private function mapSubclasses($items)
    {
        return collect($items)->map(function ($s) {
            if (is_array($s)) {
                return [
                    'subclassID' => (int) ($s['subclassID'] ?? 0),
                    'classID' => (int) ($s['classID'] ?? 0),
                    'subclass_name' => $s['subclass_name'] ?? null,
                    'class_name' => $s['class_name'] ?? null,
                    'display_name' => $s['display_name'] ?? null,
                ];
            }

            return [
                'subclassID' => (int) ($s->subclassID ?? 0),
                'classID' => (int) ($s->classID ?? ($s->class->classID ?? 0)),
                'subclass_name' => $s->subclass_name ?? null,
                'class_name' => $s->class_name ?? ($s->class->class_name ?? null),
                'display_name' => $s->display_name ?? null,
            ];
        })->values();
    }

    private function mapTeachers($items)
    {
        return collect($items)->map(function ($t) {
            if (is_array($t)) {
                return [
                    'id' => (int) ($t['id'] ?? 0),
                    'full_name' => $t['full_name'] ?? trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')),
                    'employee_number' => $t['employee_number'] ?? null,
                ];
            }

            return [
                'id' => (int) ($t->id ?? 0),
                'full_name' => trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? '')),
                'employee_number' => $t->employee_number ?? null,
            ];
        })->values();
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
