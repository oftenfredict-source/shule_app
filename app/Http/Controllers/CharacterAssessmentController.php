<?php

namespace App\Http\Controllers;

use App\Models\CharacterAssessmentCategory;
use App\Models\CharacterAssessmentChoice;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\CharacterAssessmentTimetable;
use App\Models\CharacterAssessmentAssignment;
use App\Models\StudentCharacterAssessment;
use App\Models\School;
use App\Services\SelectedAcademicYearService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CharacterAssessmentController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login');
        }

        $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $globalChoices = CharacterAssessmentChoice::where('schoolID', $schoolID)
            ->orderBy('weight', 'asc')
            ->get();

        $academicYears = AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get();
        $terms = Term::where('schoolID', $schoolID)->orderBy('termID', 'desc')->get();
        $classes = ClassModel::where('schoolID', $schoolID)->where('status', 'active')->get();
        $subclasses = Subclass::whereIn('classID', $classes->pluck('classID'))->get();
        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'active')->get();

        $yearService = app(SelectedAcademicYearService::class);
        $selectedYearIds = $yearService->selectedYearIds((int) $schoolID);
        $defaultAcademicYearID = $selectedYearIds[0] ?? null;
        $defaultYear = date('Y');
        if ($defaultAcademicYearID) {
            $headerYear = $academicYears->firstWhere('academic_yearID', $defaultAcademicYearID);
            if ($headerYear) {
                $defaultYear = $headerYear->year;
            }
        } elseif ($academicYears->isNotEmpty()) {
            $defaultAcademicYearID = $academicYears->first()->academic_yearID;
            $defaultYear = $academicYears->first()->year;
        }

        $school = School::find($schoolID);
        $categoriesLimit = (int) ($school->character_categories_limit ?? 5);
        if ($categoriesLimit < 1) {
            $categoriesLimit = 5;
        }

        return view('Admin.character_assessment', compact(
            'categories', 
            'globalChoices', 
            'academicYears', 
            'terms', 
            'classes', 
            'subclasses', 
            'teachers',
            'defaultAcademicYearID',
            'defaultYear',
            'categoriesLimit'
        ));
    }

    /**
     * Save max assessment categories selectable per timetable assignment.
     */
    public function update_categories_limit(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'character_categories_limit' => 'required|integer|min:1|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $school = School::find($schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $limit = (int) $request->character_categories_limit;
        $school->character_categories_limit = $limit;
        $school->save();

        return response()->json([
            'success' => true,
            'message' => 'Categories limit saved.',
            'character_categories_limit' => $limit,
        ]);
    }

    /**
     * Enrollments for character assessment (header selected academic year).
     *
     * @return array{enrollments: Collection, student_ids: array, active_count: int, meta: array}
     */
    private function characterEnrollmentCohort(
        int $schoolID,
        int $classID,
        $subclassID = null,
        string $enrollmentStatus = 'Active'
    ): array {
        $service = app(StudentEnrollmentService::class);

        if ($subclassID && $subclassID !== 'all' && $subclassID !== '') {
            $enrollments = $service->enrollmentsInSubclass((int) $subclassID, $schoolID, $enrollmentStatus);
        } else {
            $enrollments = $service->enrollmentsInClass($classID, $schoolID, $enrollmentStatus);
        }

        $meta = [];
        foreach ($enrollments as $enr) {
            $meta[(int) $enr->studentID] = [
                'enrollmentID' => (int) $enr->enrollmentID,
                'enrollment_status' => $enr->status,
                'class_name' => $enr->classModel->class_name ?? null,
                'subclass_name' => $enr->subclass->subclass_name ?? null,
            ];
        }

        $studentIds = $enrollments->pluck('studentID')->map(fn ($id) => (int) $id)->values()->all();
        $activeCount = $enrollments->where('status', 'Active')->count();
        if ($enrollmentStatus === 'Active') {
            $activeCount = count($studentIds);
        }

        return [
            'enrollments' => $enrollments,
            'student_ids' => $studentIds,
            'active_count' => $activeCount,
            'meta' => $meta,
        ];
    }

    public function store_category(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $request->validate([
                'categories' => 'required|array',
                'categories.*.name' => 'required|string|max:255',
                'categories.*.type' => 'required|in:text,choice'
            ]);

            foreach ($request->categories as $cat) {
                CharacterAssessmentCategory::create([
                    'schoolID' => $schoolID,
                    'name' => $cat['name'],
                    'type' => $cat['type']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Categories created successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update_category(Request $request, $id)
    {
        try {
            $category = CharacterAssessmentCategory::findOrFail($id);
            $category->update([
                'name' => $request->name,
                'type' => $request->type
            ]);
            return response()->json(['success' => true, 'message' => 'Category updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_category($id)
    {
        try {
            CharacterAssessmentCategory::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store_choice(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $request->validate([
                'choices' => 'required|array',
                'choices.*.name' => 'required|string|max:255',
                'choices.*.weight' => 'required|integer'
            ]);

            // Clear existing global choices for this school to perform a sync
            CharacterAssessmentChoice::where('schoolID', $schoolID)->delete();

            foreach ($request->choices as $choice) {
                CharacterAssessmentChoice::create([
                    'schoolID' => $schoolID,
                    'choice_name' => $choice['name'],
                    'weight' => $choice['weight']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Global choices saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update_choice(Request $request, $id)
    {
        try {
            $choice = CharacterAssessmentChoice::findOrFail($id);
            $choice->update([
                'choice_name' => $request->choice_name,
                'weight' => $request->weight
            ]);
            return response()->json(['success' => true, 'message' => 'Choice updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_choice($id)
    {
        try {
            CharacterAssessmentChoice::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Choice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get_timetables(Request $request)
    {
        try {
            $schoolID = auth()->user()->schoolID ?? Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: School identity not found.'], 401);
            }

            $year = $request->year;
            $term = $request->term;
            $id = $request->id;

            $query = CharacterAssessmentTimetable::where('schoolID', $schoolID)
            ->with(['termDetails', 'assignments.class', 'assignments.subclass', 'assignments.teacher']);
    
            if ($id) {
                $query->where('timetableID', $id);
            } else {
                if ($year) $query->where('year', $year);
                if ($term) $query->where('term', $term);
            }

            $timetables = $query->orderBy('start_date', 'desc')->get();

            return response()->json(['success' => true, 'timetables' => $timetables]);
        } catch (\Exception $e) {
            \Log::error('CharacterAssessment get_timetables error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function store_timetable(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $id = $request->timetableID;

            $school = School::find($schoolID);
            $categoriesLimit = (int) ($school->character_categories_limit ?? 5);
            if ($categoriesLimit < 1) {
                $categoriesLimit = 5;
            }

            if ($request->assignments && is_array($request->assignments)) {
                foreach ($request->assignments as $asgn) {
                    $selected = $asgn['selected_categories'] ?? [];
                    if (is_array($selected) && count($selected) > $categoriesLimit) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot select more than {$categoriesLimit} assessment categories per assignment.",
                        ], 422);
                    }
                }
            }
            
            // 2. Create or Update Timetable
            if ($id) {
                $timetable = CharacterAssessmentTimetable::findOrFail($id);
                $timetable->update([
                    'year' => $request->year,
                    'term' => $request->term,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'target_classes' => $request->target_classes
                ]);
                // Clear old assignments for update
                CharacterAssessmentAssignment::where('timetableID', $id)->delete();
            } else {
                $timetable = CharacterAssessmentTimetable::create([
                    'schoolID' => $schoolID,
                    'year' => $request->year,
                    'term' => $request->term,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'target_classes' => $request->target_classes
                ]);
            }

            // 3. Create Assignments
            if ($request->assignments && is_array($request->assignments)) {
                foreach ($request->assignments as $asgn) {
                    CharacterAssessmentAssignment::create([
                        'timetableID' => $timetable->timetableID,
                        'classID' => $asgn['classID'],
                        'subclassID' => $asgn['subclassID'] == 'all' ? null : $asgn['subclassID'],
                        'teacherID' => $asgn['teacherID'],
                        'selected_categories' => $asgn['selected_categories'] ?? []
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => $id ? 'Timetable updated successfully' : 'Timetable defined successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_timetable($id)
    {
        try {
            CharacterAssessmentTimetable::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Timetable deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- TEACHER ACTIONS ---
    public function teacher_index()
    {
        $teacherID = (int) Session::get('teacherID');
        $schoolID = (int) Session::get('schoolID');
        if (!$teacherID) {
            return redirect()->route('login');
        }

        // Prefer open periods (active + upcoming). If none, still show any assignment
        // so a teacher who was assigned can open the task from the menu.
        $assignments = CharacterAssessmentAssignment::visibleToTeacher($teacherID, $schoolID)
            ->with(['class', 'subclass', 'timetable'])
            ->get();

        if ($assignments->isEmpty()) {
            $assignments = CharacterAssessmentAssignment::where('teacherID', $teacherID)
                ->whereHas('timetable', function ($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })
                ->with(['class', 'subclass', 'timetable'])
                ->orderByDesc('assignmentID')
                ->get();
        }

        $today = \Carbon\Carbon::today();
        $periodStatus = 'none';
        if ($assignments->isNotEmpty()) {
            $hasActivePeriod = $assignments->contains(function ($assignment) use ($today) {
                $tt = $assignment->timetable;
                if (!$tt || !$tt->start_date || !$tt->end_date) {
                    return true;
                }

                return $tt->start_date->lte($today) && $tt->end_date->gte($today);
            });
            $periodStatus = $hasActivePeriod ? 'active' : 'upcoming';

            $allEnded = $assignments->every(function ($assignment) use ($today) {
                $tt = $assignment->timetable;
                return $tt && $tt->end_date && $tt->end_date->lt($today);
            });
            if ($allEnded) {
                $periodStatus = 'ended';
            }
        }

        // Global choices for reference
        $globalChoices = CharacterAssessmentChoice::where('schoolID', $schoolID)->orderBy('weight', 'asc')->get();
        $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)->get();
        $subclasses = \App\Models\Subclass::whereHas('class', function ($q) use ($schoolID) {
            $q->where('schoolID', $schoolID);
        })->get();
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->get();

        return view('Teacher.student_character_assessment', compact(
            'assignments',
            'globalChoices',
            'categories',
            'subclasses',
            'classes',
            'periodStatus'
        ));
    }

    public function get_assessment_students(Request $request)
    {
        try {
            $subclassID = $request->subclassID;
            $classID = (int) $request->classID;
            $timetableID = $request->timetableID;
            $schoolID = (int) Session::get('schoolID');

            if (!$schoolID || !$classID) {
                return response()->json(['success' => false, 'message' => 'Missing class or school.'], 400);
            }

            $selectedYearIds = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID);
            if (empty($selectedYearIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select an academic year in the header before assessing students.',
                    'error_code' => 'no_academic_year',
                    'students' => [],
                    'active_enrollment_count' => 0,
                ], 422);
            }

            // All statuses: Active editable; others returned greyed-out
            $cohort = $this->characterEnrollmentCohort(
                $schoolID,
                $classID,
                $subclassID,
                'all'
            );

            if ($cohort['active_count'] === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected academic year has no Active enrollments for this class. Assessment cannot continue.',
                    'error_code' => 'no_active_enrollments',
                    'students' => [],
                    'active_enrollment_count' => 0,
                ], 422);
            }

            $students = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $cohort['student_ids'])
                ->with(['characterAssessments' => function ($q) use ($timetableID) {
                    if ($timetableID) {
                        $q->where('timetableID', $timetableID);
                    }
                }])
                ->orderBy('first_name')
                ->get()
                ->map(function ($student) use ($cohort) {
                    $meta = $cohort['meta'][(int) $student->studentID] ?? null;
                    $status = $meta['enrollment_status'] ?? 'Active';
                    $student->enrollment_status = $status;
                    $student->enrollmentID = $meta['enrollmentID'] ?? null;
                    $student->can_assess = $status === 'Active';
                    $student->subclass_name = $meta['subclass_name'] ?? null;
                    return $student;
                });

            return response()->json([
                'success' => true,
                'students' => $students,
                'active_enrollment_count' => $cohort['active_count'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store_student_assessment(Request $request)
    {
        try {
            $schoolID = (int) Session::get('schoolID');
            $teacherID = Session::get('teacherID');
            
            $data = $request->assessments; // Array of {studentID, timetableID, categories: [{categoryID, choiceID, description}]}

            if (!is_array($data) || empty($data)) {
                return response()->json(['success' => false, 'message' => 'No assessment data provided.'], 400);
            }

            $enrollmentService = app(StudentEnrollmentService::class);
            $selectedYearIds = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID);

            if (empty($selectedYearIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select an academic year in the header before saving assessments.',
                    'error_code' => 'no_academic_year',
                ], 422);
            }

            foreach ($data as $studentEntry) {
                $studentID = (int) ($studentEntry['studentID'] ?? 0);
                $enrollment = $enrollmentService->currentEnrollment($studentID, $schoolID);
                if (!$enrollment || $enrollment->status !== 'Active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Active enrollments for the selected academic year can be assessed.',
                        'error_code' => 'enrollment_not_active',
                    ], 422);
                }

                $timetable = CharacterAssessmentTimetable::find($studentEntry['timetableID']);
                $year = $timetable ? $timetable->year : date('Y');

                foreach ($studentEntry['categories'] as $cat) {
                    StudentCharacterAssessment::updateOrCreate(
                        [
                            'schoolID' => $schoolID,
                            'studentID' => $studentID,
                            'timetableID' => $studentEntry['timetableID'],
                            'categoryID' => $cat['categoryID'],
                        ],
                        [
                            'year' => $year,
                            'choiceID' => isset($cat['choiceID']) && $cat['choiceID'] ? $cat['choiceID'] : null,
                            'description' => isset($cat['description']) ? $cat['description'] : null,
                            'assessed_by' => $teacherID
                        ]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Assessments saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get_student_assessment_history(Request $request)
    {
        try {
            $studentID = $request->studentID;
            $timetableID = $request->timetableID;
            
            $history = StudentCharacterAssessment::where('studentID', $studentID)
                ->where('timetableID', $timetableID)
                ->with(['category', 'choice'])
                ->get();

            return response()->json(['success' => true, 'history' => $history]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get_timetables_by_term(Request $request)
    {
        $termID = $request->termID;
        $year = $request->input('year');
        $schoolID = Session::get('schoolID');

        $query = CharacterAssessmentTimetable::where('term', $termID)
            ->where('schoolID', $schoolID);

        if ($year !== null && $year !== '') {
            $query->where('year', $year);
        } else {
            // Default to header academic year's calendar year when filter year omitted
            $selectedIds = app(SelectedAcademicYearService::class)->selectedYearIds((int) $schoolID);
            if (!empty($selectedIds)) {
                $ay = AcademicYear::where('schoolID', $schoolID)
                    ->where('academic_yearID', $selectedIds[0])
                    ->first();
                if ($ay) {
                    $query->where('year', $ay->year);
                }
            }
        }

        $timetables = $query->orderBy('start_date', 'desc')->get();
        return response()->json(['success' => true, 'timetables' => $timetables]);
    }

    public function get_classes_by_timetable(Request $request)
    {
        $timetableID = $request->timetableID;
        $assignments = CharacterAssessmentAssignment::where('timetableID', $timetableID)
            ->with('class')
            ->get();
        
        $classes = $assignments->pluck('class')->filter()->unique('classID')->values();
        return response()->json(['success' => true, 'classes' => $classes]);
    }

    public function get_report_data(Request $request)
    {
        try {
            $timetableID = $request->timetableID;
            $classID = (int) $request->classID;
            $schoolID = (int) Session::get('schoolID');

            $selectedYearIds = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID);
            if (empty($selectedYearIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select an academic year in the header before viewing reports.',
                    'error_code' => 'no_academic_year',
                ], 422);
            }

            // Report stats/list use Active enrollments for selected header year
            $cohort = $this->characterEnrollmentCohort($schoolID, $classID, null, 'Active');

            if (empty($cohort['student_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected academic year has no Active enrollments for this class.',
                    'error_code' => 'no_active_enrollments',
                    'summary' => [
                        'total' => 0,
                        'males' => 0,
                        'females' => 0,
                        'assessed' => 0,
                        'pending' => 0,
                    ],
                    'studentList' => [],
                ], 422);
            }

            $allStudents = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $cohort['student_ids'])
                ->orderBy('first_name')
                ->get();

            $totalStudents = $allStudents->count();
            $males = $allStudents->where('gender', 'Male')->count();
            $females = $allStudents->where('gender', 'Female')->count();

            $assessments = StudentCharacterAssessment::where('timetableID', $timetableID)
                ->whereIn('studentID', $allStudents->pluck('studentID'))
                ->with(['category', 'choice'])
                ->get();

            $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)->where('type', 'choice')->get();
            $choices = CharacterAssessmentChoice::where('schoolID', $schoolID)->orderBy('weight', 'asc')->get();
            
            $chartData = [];
            $statsTable = [];

            foreach ($categories as $cat) {
                $catData = [
                    'category_name' => $cat->name,
                    'choices' => []
                ];
                
                $catAssessments = $assessments->where('categoryID', $cat->categoryID);
                $catTotal = $catAssessments->count();

                $tableRow = [
                    'category' => $cat->name,
                    'choices' => []
                ];

                foreach ($choices as $ch) {
                    $count = $catAssessments->where('choiceID', $ch->choiceID)->count();
                    $percentage = $catTotal > 0 ? round(($count / $catTotal) * 100, 1) : 0;
                    
                    $catData['choices'][] = [
                        'name' => $ch->choice_name,
                        'count' => $count
                    ];

                    $tableRow['choices'][] = [
                        'name' => $ch->choice_name,
                        'percentage' => $percentage,
                        'count' => $count
                    ];
                }
                $chartData[] = $catData;
                $statsTable[] = $tableRow;
            }

            $studentList = $allStudents->map(function ($student) use ($assessments, $cohort) {
                $studentAssessments = $assessments->where('studentID', $student->studentID);
                $isReady = $studentAssessments->count() > 0;
                $meta = $cohort['meta'][(int) $student->studentID] ?? null;
                
                $summary = $studentAssessments->map(function ($a) {
                    return $a->choice ? $a->choice->choice_name : null;
                })->filter()->unique()->values()->toArray();

                return [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'gender' => $student->gender,
                    'photo' => $student->image,
                    'is_ready' => $isReady,
                    'summary' => $summary,
                    'enrollment_status' => $meta['enrollment_status'] ?? 'Active',
                    'enrollmentID' => $meta['enrollmentID'] ?? null,
                    'subclass_name' => $meta['subclass_name'] ?? null,
                ];
            });

            $assessedCount = $studentList->where('is_ready', true)->count();
            $pendingCount = $totalStudents - $assessedCount;

            return response()->json([
                'success' => true,
                'summary' => [
                    'total' => $totalStudents,
                    'males' => $males,
                    'females' => $females,
                    'assessed' => $assessedCount,
                    'pending' => $pendingCount
                ],
                'chartData' => $chartData,
                'choices' => $choices->pluck('choice_name'),
                'statsTable' => $statsTable,
                'studentList' => $studentList,
                'allChoices' => $choices,
                'active_enrollment_count' => $cohort['active_count'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function download_pdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        try {
            $schoolID = (int) Session::get('schoolID');
            if (!$schoolID) return "Session expired";

            $scope = $request->scope; // 'class' or 'student'
            $timetableID = $request->timetableID;
            
            $school = \App\Models\School::find($schoolID);
            $timetable = CharacterAssessmentTimetable::find($timetableID);

            if (!$timetable) return "Timetable period not found";

            if ($scope === 'class') {
                $classID = (int) $request->classID;
                $class = ClassModel::find($classID);
                if (!$class) return "Class not found";

                $cohort = $this->characterEnrollmentCohort($schoolID, $classID, null, 'Active');
                if (empty($cohort['student_ids'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected academic year has no Active enrollments for this class.',
                        'error_code' => 'no_active_enrollments',
                    ], 422);
                }

                $students = Student::where('schoolID', $schoolID)
                    ->whereIn('studentID', $cohort['student_ids'])
                    ->with(['characterAssessments' => function ($q) use ($timetableID) {
                        $q->where('timetableID', $timetableID)->with(['category', 'choice']);
                    }])
                    ->orderBy('first_name')
                    ->get();

                $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)->get();
                $choices = CharacterAssessmentChoice::where('schoolID', $schoolID)->orderBy('weight')->get();
                
                $stats = [];
                foreach ($categories as $cat) {
                    $catStats = ['name' => $cat->name, 'choices' => []];
                    $catTotal = StudentCharacterAssessment::where('timetableID', $timetableID)
                        ->where('categoryID', $cat->categoryID)
                        ->whereIn('studentID', $students->pluck('studentID'))
                        ->count();
                    
                    foreach ($choices as $ch) {
                        $count = StudentCharacterAssessment::where('timetableID', $timetableID)
                            ->where('categoryID', $cat->categoryID)
                            ->where('choiceID', $ch->choiceID)
                            ->whereIn('studentID', $students->pluck('studentID'))
                            ->count();
                        $percent = $catTotal > 0 ? round(($count / $catTotal) * 100, 1) : 0;
                        $catStats['choices'][] = ['name' => $ch->choice_name, 'percent' => $percent, 'count' => $count];
                    }
                    $stats[] = $catStats;
                }

                $pdf = Pdf::loadView('Reports.character_assessment_class', compact('school', 'timetable', 'class', 'students', 'categories', 'choices', 'stats'));
                $pdf->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => base_path(),
                ]);
                $pdf->setPaper('A4', 'portrait');

                $filename = "Character_Assessment_Summary_" . str_replace(' ', '_', $class->class_name) . "_" . date('YmdHis') . ".pdf";
                return $pdf->download($filename);
            } else {
                $studentID = $request->studentID;
                $student = Student::with(['subclass.class'])->find($studentID);
                if (!$student) return "Student not found";

                // Prefer enrollment class label for selected year
                $enrollment = app(StudentEnrollmentService::class)->currentEnrollment((int) $studentID, $schoolID);
                if ($enrollment) {
                    $enrollment->loadMissing(['subclass.class']);
                    if ($enrollment->subclass) {
                        $student->setRelation('subclass', $enrollment->subclass);
                    }
                }
                
                $assessments = StudentCharacterAssessment::where('studentID', $studentID)
                    ->where('timetableID', $timetableID)
                    ->with(['category', 'choice'])
                    ->get();

                $pdf = Pdf::loadView('Reports.character_assessment_single', compact('school', 'timetable', 'student', 'assessments'));
                $pdf->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => base_path(),
                ]);
                $pdf->setPaper('A4', 'portrait');

                $filename = "Assessment_" . str_replace(' ', '_', $student->first_name . '_' . $student->last_name) . "_" . date('YmdHis') . ".pdf";
                return $pdf->download($filename);
            }

        } catch (\Exception $e) {
            return "PDF Generation Error: " . $e->getMessage();
        }
    }

    public function download_excel(Request $request)
    {
        try {
            $schoolID = (int) Session::get('schoolID');
            $timetableID = $request->timetableID;
            $classID = (int) $request->classID;

            $school = \App\Models\School::find($schoolID);
            $timetable = CharacterAssessmentTimetable::find($timetableID);
            $class = ClassModel::find($classID);

            $cohort = $this->characterEnrollmentCohort($schoolID, $classID, null, 'Active');
            if (empty($cohort['student_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected academic year has no Active enrollments for this class.',
                    'error_code' => 'no_active_enrollments',
                ], 422);
            }

            $students = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $cohort['student_ids'])
                ->with(['characterAssessments' => function ($q) use ($timetableID) {
                    $q->where('timetableID', $timetableID)->with(['category', 'choice']);
                }])
                ->orderBy('first_name')
                ->get();

            $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Character Assessment');

            // Header Section
            $sheet->setCellValue('A1', strtoupper($school->school_name));
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A2', 'CHARACTER ASSESSMENT REPORT - ' . $class->class_name);
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A3', 'Assessment Period: ' . $timetable->start_date . ' to ' . $timetable->end_date);
            $sheet->mergeCells('A3:F3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table Headers
            $headers = ['Admission #', 'Student Name', 'Gender', 'Category', 'Rating', 'Remarks'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '5', $header);
                $sheet->getStyle($col . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('940000');
                $sheet->getStyle($col . '5')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE))->setBold(true);
                $col++;
            }

            // Data
            $row = 6;
            foreach ($students as $student) {
                $name = $student->first_name . ' ' . $student->last_name;
                $assessCount = $student->characterAssessments->count();
                
                if ($assessCount == 0) {
                    $sheet->setCellValue('A' . $row, $student->admission_number);
                    $sheet->setCellValue('B' . $row, $name);
                    $sheet->setCellValue('C' . $row, $student->gender);
                    $sheet->setCellValue('D' . $row, 'NO ASSESSMENT DATA');
                    $sheet->mergeCells("D{$row}:F{$row}");
                    $row++;
                } else {
                    foreach ($student->characterAssessments as $index => $ass) {
                        if ($index == 0) {
                            $sheet->setCellValue('A' . $row, $student->admission_number);
                            $sheet->setCellValue('B' . $row, $name);
                            $sheet->setCellValue('C' . $row, $student->gender);
                        }
                        $sheet->setCellValue('D' . $row, $ass->category->name);
                        $sheet->setCellValue('E' . $row, $ass->choice ? $ass->choice->choice_name : 'N/A');
                        $sheet->setCellValue('F' . $row, $ass->description ?? '');
                        $row++;
                    }
                }
            }

            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = "Character_Assessment_" . str_replace(' ', '_', $class->class_name) . ".xlsx";
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return "Excel Export Error: " . $e->getMessage();
        }
    }
}
