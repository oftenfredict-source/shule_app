<?php

namespace App\Http\Controllers;

use App\Models\CharacterAssessmentCategory;
use App\Models\CharacterAssessmentChoice;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Models\CharacterAssessmentTimetable;
use App\Models\CharacterAssessmentAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
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

        $academicYears = AcademicYear::where('schoolID', $schoolID)->orderBy('academic_yearID', 'desc')->get();
        $terms = Term::where('schoolID', $schoolID)->orderBy('termID', 'desc')->get();
        $classes = ClassModel::where('schoolID', $schoolID)->where('status', 'active')->get();
        $subclasses = Subclass::whereIn('classID', $classes->pluck('classID'))->get();
        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'active')->get();

        return view('Admin.character_assessment', compact(
            'categories', 
            'globalChoices', 
            'academicYears', 
            'terms', 
            'classes', 
            'subclasses', 
            'teachers'
        ));
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
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');
        if (!$teacherID) return redirect()->route('login');

        // Fetch active assignments for this teacher
        $today = \Carbon\Carbon::now();
        $assignments = CharacterAssessmentAssignment::where('teacherID', $teacherID)
            ->whereHas('timetable', function($q) use ($today, $schoolID) {
                $q->where('schoolID', $schoolID)
                  ->where('start_date', '<=', $today)
                  ->where('end_date', '>=', $today);
            })
            ->with(['class', 'subclass', 'timetable'])
            ->get();

        if ($assignments->isEmpty()) {
            return redirect()->route('teachersDashboard')->with('error', 'No active character assessment period found.');
        }

        // Global choices for reference
        $globalChoices = CharacterAssessmentChoice::where('schoolID', $schoolID)->orderBy('weight', 'asc')->get();
        $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)->get();
        $subclasses = \App\Models\Subclass::whereHas('class', function($q) use ($schoolID) {
            $q->where('schoolID', $schoolID);
        })->get();
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->get();

        return view('Teacher.student_character_assessment', compact('assignments', 'globalChoices', 'categories', 'subclasses', 'classes'));
    }

    public function get_assessment_students(Request $request)
    {
        try {
            $subclassID = $request->subclassID;
            $classID = $request->classID;
            $timetableID = $request->timetableID;
            $schoolID = Session::get('schoolID');
            
            $query = \App\Models\Student::where('schoolID', $schoolID)->where('status', 'Active');
            
            if ($subclassID && $subclassID != 'all') {
                $query->where('subclassID', $subclassID);
            } else {
                // Since students table doesn't have classID, we filter via subclasses
                $query->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            }

            $students = $query->with(['characterAssessments' => function($q) use ($timetableID) {
                if ($timetableID) {
                    $q->where('timetableID', $timetableID);
                }
            }])->orderBy('first_name')->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store_student_assessment(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $teacherID = Session::get('teacherID');
            
            $data = $request->assessments; // Array of {studentID, timetableID, categories: [{categoryID, choiceID, description}]}

            foreach ($data as $studentEntry) {
                $timetable = CharacterAssessmentTimetable::find($studentEntry['timetableID']);
                $year = $timetable ? $timetable->year : date('Y');

                foreach ($studentEntry['categories'] as $cat) {
                    \App\Models\StudentCharacterAssessment::updateOrCreate(
                        [
                            'schoolID' => $schoolID,
                            'studentID' => $studentEntry['studentID'],
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
            
            $history = \App\Models\StudentCharacterAssessment::where('studentID', $studentID)
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
        $schoolID = Session::get('schoolID');
        $timetables = CharacterAssessmentTimetable::where('term', $termID)
            ->where('schoolID', $schoolID)
            ->orderBy('start_date', 'desc')
            ->get();
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
            $classID = $request->classID;
            $schoolID = Session::get('schoolID');

            // 1. Get All Students in this class
            $studentsQuery = \App\Models\Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            
            $allStudents = $studentsQuery->get();
            $totalStudents = $allStudents->count();
            $males = $allStudents->where('gender', 'Male')->count();
            $females = $allStudents->where('gender', 'Female')->count();

            // 2. Get Assessments for this timetable and class
            $assessments = \App\Models\StudentCharacterAssessment::where('timetableID', $timetableID)
                ->whereIn('studentID', $allStudents->pluck('studentID'))
                ->with(['category', 'choice'])
                ->get();

            // 3. Choice distribution by Category
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

            // 4. Student List with their assessments
            $studentList = $allStudents->map(function($student) use ($assessments) {
                $studentAssessments = $assessments->where('studentID', $student->studentID);
                $isReady = $studentAssessments->count() > 0;
                
                // Get summary of assessment (distinct choice names)
                $summary = $studentAssessments->map(function($a) {
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
                    'summary' => $summary
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
                'allChoices' => $choices
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
            $schoolID = Session::get('schoolID');
            if (!$schoolID) return "Session expired";

            $scope = $request->scope; // 'class' or 'student'
            $timetableID = $request->timetableID;
            
            $school = \App\Models\School::find($schoolID);
            $timetable = CharacterAssessmentTimetable::find($timetableID);

            if (!$timetable) return "Timetable period not found";

            if ($scope === 'class') {
                $classID = $request->classID;
                $class = ClassModel::find($classID);
                if (!$class) return "Class not found";
                
                $students = \App\Models\Student::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->whereHas('subclass', function($q) use ($classID) {
                        $q->where('classID', $classID);
                    })
                    ->with(['characterAssessments' => function($q) use ($timetableID) {
                        $q->where('timetableID', $timetableID)->with(['category', 'choice']);
                    }])
                    ->get();

                // Prepare analytical data for PDF
                $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)->get();
                $choices = CharacterAssessmentChoice::where('schoolID', $schoolID)->orderBy('weight')->get();
                
                // Calculate stats for graphs/bars
                $stats = [];
                foreach ($categories as $cat) {
                    $catStats = ['name' => $cat->name, 'choices' => []];
                    $catTotal = \App\Models\StudentCharacterAssessment::where('timetableID', $timetableID)
                        ->where('categoryID', $cat->categoryID)
                        ->whereIn('studentID', $students->pluck('studentID'))
                        ->count();
                    
                    foreach ($choices as $ch) {
                        $count = \App\Models\StudentCharacterAssessment::where('timetableID', $timetableID)
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
                $student = \App\Models\Student::with(['subclass.class'])->find($studentID);
                if (!$student) return "Student not found";
                
                $assessments = \App\Models\StudentCharacterAssessment::where('studentID', $studentID)
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
            $schoolID = Session::get('schoolID');
            $timetableID = $request->timetableID;
            $classID = $request->classID;

            $school = \App\Models\School::find($schoolID);
            $timetable = CharacterAssessmentTimetable::find($timetableID);
            $class = ClassModel::find($classID);

            $students = \App\Models\Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                })
                ->with(['characterAssessments' => function($q) use ($timetableID) {
                    $q->where('timetableID', $timetableID)->with(['category', 'choice']);
                }])
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
