<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\Examination;
use App\Models\ExamAttendanceAssignment;
use App\Models\ExamAttendanceRecord;
use App\Models\Result;
use App\Models\School;
use App\Models\SchoolSubject;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Services\SmsService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ExamAttendanceController extends Controller
{
    public function getExamClasses($examID)
    {
        $schoolID = Session::get('schoolID');
        $exam = Examination::find($examID);
        if (!$exam) return response()->json(['error' => 'Exam not found'], 404);

        $classIds = $this->getParticipatingClassIds($exam, $schoolID);
        $classes = ClassModel::where('schoolID', $schoolID)
            ->whereIn('classID', $classIds)
            ->orderBy('class_name')
            ->get();

        return response()->json(['success' => true, 'classes' => $classes]);
    }

    public function teacherIndex()
    {
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');
        
        $assignments = ExamAttendanceAssignment::where('teacherID', $teacherID)
            ->where('schoolID', $schoolID)
            ->where('status', true)
            ->with(['exam', 'class', 'subclass'])
            ->get();
            
        return view('Teacher.exam_attendance', compact('assignments'));
    }

    public function getAssignmentDetailsForTeacher($id)
    {
        $teacherID = Session::get('teacherID');
        $assignment = ExamAttendanceAssignment::where('id', $id)
            ->where('teacherID', $teacherID)
            ->where('status', true)
            ->with(['class', 'subclass', 'exam'])
            ->first();
            
        if (!$assignment) return response()->json(['error' => 'Assignment not found or disabled'], 404);

        $subjects = SchoolSubject::whereIn('subjectID', $assignment->subject_ids)->get();

        $students = $this->getStudentsWithExamNumbers(
            $assignment->examID,
            $assignment->classID,
            $assignment->subclassID,
        );
        $activeCount = $students->where('can_enter_result', true)->count();
        if ($activeCount === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Selected academic year has no Active enrollments for this class. Attendance collection cannot continue.',
                'error_code' => 'no_active_enrollments',
            ], 422);
        }
        
        $details = [];
        foreach ($subjects as $subject) {
            $records = ExamAttendanceRecord::where('examID', $assignment->examID)
                ->where('subjectID', $subject->subjectID)
                ->whereIn('studentID', $students->pluck('studentID'))
                ->get()
                ->keyBy('studentID');

            $studentData = $students->map(function($student) use ($records) {
                return [
                    'studentID' => $student->studentID,
                    'name' => $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name,
                    'examination_number' => $student->examination_number,
                    'status' => $records[$student->studentID]->status ?? 'Present',
                    'enrollmentID' => $student->enrollmentID ?? null,
                    'enrollment_status' => $student->enrollment_status ?? null,
                    'can_enter_result' => (bool) ($student->can_enter_result ?? true),
                ];
            });

            $details[] = [
                'subjectID' => $subject->subjectID,
                'subject_name' => $subject->subject_name,
                'students' => $studentData,
                'active_enrollment_count' => $activeCount,
            ];
        }

        return response()->json([
            'success' => true, 
            'assignment' => $assignment,
            'details' => $details,
            'active_enrollment_count' => $activeCount,
            'enrollment_scope' => 'header_year_active_editable',
        ]);
    }

    public function getStudentsForAdminCollection(Request $request)
    {
        $examID = $request->examID;
        $classID = $request->classID;
        $subclassID = $request->subclassID === 'all' ? null : $request->subclassID;
        $subjectID = $request->subjectID;

        // Use helper to get students with exam numbers and correct sorting
        $students = $this->getStudentsWithExamNumbers($examID, $classID, $subclassID);

        $activeCount = $students->where('can_enter_result', true)->count();
        if ($activeCount === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Selected academic year has no Active enrollments for this class. Attendance collection cannot continue.',
                'error_code' => 'no_active_enrollments',
                'students' => [],
            ], 422);
        }

        $records = ExamAttendanceRecord::where('examID', $examID)
            ->where('subjectID', $subjectID)
            ->whereIn('studentID', $students->pluck('studentID'))
            ->get()
            ->keyBy('studentID');

        $studentData = $students->map(function($student) use ($records) {
            return [
                'studentID' => $student->studentID,
                'name' => $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name,
                'examination_number' => $student->examination_number,
                'status' => $records[$student->studentID]->status ?? 'Present',
                'enrollmentID' => $student->enrollmentID ?? null,
                'enrollment_status' => $student->enrollment_status ?? null,
                'can_enter_result' => (bool) ($student->can_enter_result ?? true),
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $studentData,
            'active_enrollment_count' => $activeCount,
            'enrollment_scope' => 'header_year_active_editable',
        ]);
    }

    public function getSubclasses($classID)
    {
        $subclasses = Subclass::where('classID', $classID)->get();
        return response()->json(['success' => true, 'subclasses' => $subclasses]);
    }

    public function getSubjectsForAttendance(Request $request)
    {
        $classID = $request->classID;
        $subclassID = $request->subclassID;
        $examID = $request->examID;
        $assignmentID = $request->assignmentID;

        // Get all subjects for this class/subclass
        // Join with subclasses to handle 'all' subclasses case
        $subjects = ClassSubject::join('subclasses', 'class_subjects.subclassID', '=', 'subclasses.subclassID')
            ->where('subclasses.classID', $classID)
            ->when($subclassID && $subclassID !== 'all', function($q) use ($subclassID) {
                return $q->where('class_subjects.subclassID', $subclassID);
            })
            ->with('subject')
            ->select('class_subjects.*')
            ->get()
            ->unique('subjectID')
            ->values(); // Reset keys for JSON array

        // Get already assigned subjects for this exam/class/subclass
        $assignedSubjects = ExamAttendanceAssignment::where('examID', $examID)
            ->where('classID', $classID)
            ->when($subclassID && $subclassID !== 'all', function($q) use ($subclassID) {
                return $q->where('subclassID', $subclassID);
            })
            ->when($assignmentID, function($q) use ($assignmentID) {
                return $q->where('id', '!=', $assignmentID);
            })
            ->get()
            ->pluck('subject_ids')
            ->flatten()
            ->toArray();

        return response()->json([
            'success' => true, 
            'subjects' => $subjects,
            'assigned_subject_ids' => $assignedSubjects
        ]);
    }

    public function assignTeacher(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $createdBy = Session::get('teacherID') ?? Session::get('staffID') ?? 0;

        if ($request->assignmentID) {
            $assignment = ExamAttendanceAssignment::find($request->assignmentID);
            if ($assignment) {
                $assignment->update([
                    'teacherID' => $request->teacherID,
                    'classID' => $request->classID,
                    'subclassID' => $request->subclassID === 'all' ? null : $request->subclassID,
                    'subject_ids' => $request->subject_ids,
                    'status' => true
                ]);
            }
        } else {
            $assignment = ExamAttendanceAssignment::updateOrCreate(
                [
                    'schoolID' => $schoolID,
                    'examID' => $request->examID,
                    'teacherID' => $request->teacherID,
                    'classID' => $request->classID,
                    'subclassID' => $request->subclassID === 'all' ? null : $request->subclassID,
                ],
                [
                    'subject_ids' => $request->subject_ids,
                    'status' => true,
                    'created_by' => $createdBy
                ]
            );
        }

        // Send SMS
        try {
            $teacher = Teacher::find($request->teacherID);
            $exam = Examination::find($request->examID);
            $school = School::find($schoolID);
            $class = ClassModel::find($request->classID);
            $subclass = $request->subclassID && $request->subclassID !== 'all' ? Subclass::find($request->subclassID) : null;
            
            if ($teacher && $teacher->phone_number && $exam && $school) {
                $className = $class->class_name . ($subclass ? ' ' . $subclass->subclass_name : '');
                $subjectNames = SchoolSubject::whereIn('subjectID', $request->subject_ids)->pluck('subject_name')->toArray();
                $subjectList = implode(', ', $subjectNames);
                $termName = ($exam->term === 'first_term') ? 'First Term' : (($exam->term === 'second_term') ? 'Second Term' : 'All Terms');

                $message = "{$school->school_name}. Habari teacher {$teacher->first_name}, umechaguliwa kukusanya mahudhurio ya mtihani {$exam->exam_name}, darasa la {$className}, muhula wa {$termName}, masomo: {$subjectList}.";
                
                $smsService = new SmsService();
                $smsService->sendSms($teacher->phone_number, $message);
            }
        } catch (\Exception $e) {
            Log::error("Exam Attendance SMS Error: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Attendance assignment saved and SMS sent.']);
    }

    public function getAssignedTeachers($examID)
    {
        $assignments = ExamAttendanceAssignment::where('examID', $examID)
            ->with(['teacher', 'class', 'subclass'])
            ->get();

        return response()->json(['success' => true, 'assignments' => $assignments]);
    }

    public function deleteAssignment($id)
    {
        $assignment = ExamAttendanceAssignment::find($id);
        if (!$assignment) return response()->json(['error' => 'Assignment not found'], 404);
        
        $assignment->delete();
        return response()->json(['success' => true, 'message' => 'Assignment deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        $assignment = ExamAttendanceAssignment::find($id);
        if (!$assignment) return response()->json(['error' => 'Assignment not found'], 404);
        
        $assignment->status = !$assignment->status;
        $assignment->save();
        
        return response()->json(['success' => true, 'status' => $assignment->status, 'message' => 'Status updated successfully.']);
    }

    public function getAssignmentDetails($id)
    {
        $assignment = ExamAttendanceAssignment::with(['teacher', 'class', 'subclass'])->find($id);
        if (!$assignment) return response()->json(['error' => 'Assignment not found'], 404);

        $subjects = SchoolSubject::whereIn('subjectID', $assignment->subject_ids)->get();
        
        // For each subject, get students and their attendance status
        $details = [];
        foreach ($subjects as $subject) {
            // Use helper for consistent sorting and exam numbers
            $students = $this->getStudentsWithExamNumbers($assignment->examID, $assignment->classID, $assignment->subclassID);

            $records = ExamAttendanceRecord::where('examID', $assignment->examID)
                ->where('subjectID', $subject->subjectID)
                ->whereIn('studentID', $students->pluck('studentID'))
                ->get()
                ->keyBy('studentID');

            $studentData = $students->map(function($student) use ($records) {
                return [
                    'studentID' => $student->studentID,
                    'name' => $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name,
                    'examination_number' => $student->examination_number,
                    'status' => $records[$student->studentID]->status ?? 'Present'
                ];
            });

            $details[] = [
                'subjectID' => $subject->subjectID,
                'subject_name' => $subject->subject_name,
                'students' => $studentData
            ];
        }

        return response()->json([
            'success' => true, 
            'details' => $details,
            'assignment' => $assignment
        ]);
    }

    public function saveAttendance(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $markedBy = Session::get('teacherID') ?? Session::get('staffID') ?? 0;

        foreach ($request->attendance as $item) {
            ExamAttendanceRecord::updateOrCreate(
                [
                    'schoolID' => $schoolID,
                    'examID' => $request->examID,
                    'studentID' => $item['studentID'],
                    'subjectID' => $request->subjectID,
                ],
                [
                    'status' => $item['status'],
                    'marked_by' => $markedBy
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Attendance saved successfully.']);
    }

    public function downloadTemplate(Request $request)
    {
        $exam    = Examination::find($request->examID);
        $subject = SchoolSubject::find($request->subjectID);
        $class   = ClassModel::find($request->classID);

        // Guard: return a clear error if any required model is missing
        if (!$exam || !$subject || !$class) {
            return response()->json([
                'error' => 'Invalid exam, subject, or class ID. Please select valid values and try again.'
            ], 422);
        }

        // Handle subclassID: treat empty string and "all" as null
        $subclassRaw = $request->subclassID;
        $subclass = ($subclassRaw && $subclassRaw !== '' && $subclassRaw !== 'all')
            ? Subclass::find($subclassRaw)
            : null;

        $school   = School::find(Session::get('schoolID'));

        $subclassForRoster = ($subclassRaw && $subclassRaw !== '' && $subclassRaw !== 'all')
            ? $subclassRaw
            : null;
        $students = $this->getStudentsWithExamNumbers($request->examID, $request->classID, $subclassForRoster);
        $students = $students->map(function ($student) {
            return (object) [
                'studentID' => $student->studentID,
                'examination_number' => (string) ($student->examination_number ?? ''),
                'first_name' => (string) ($student->first_name ?? ''),
                'middle_name' => (string) ($student->middle_name ?? ''),
                'last_name' => (string) ($student->last_name ?? ''),
                'gender' => (string) ($student->gender ?? ''),
            ];
        });

        $records = [];
        if ($request->with_data) {
            $records = ExamAttendanceRecord::where('examID', $request->examID)
                ->where('subjectID', $request->subjectID)
                ->get()
                ->keyBy('studentID');
        }

        $data = [
            'school'     => $school,
            'exam'       => $exam,
            'subject'    => $subject,
            'class'      => $class,
            'subclass'   => $subclass,
            'students'   => $students,
            'records'    => $records,
            'with_data'  => $request->with_data ?? false,
            'year'       => $exam->year,
            'term'       => ($exam->term === 'first_term') ? 'First Term'
                         : (($exam->term === 'second_term') ? 'Second Term' : 'All Terms'),
        ];

        try {
            @ini_set('memory_limit', '512M');
            $pdf = PDF::loadView('Admin.pdf.exam_attendance_template', $data);
            $filename = $request->with_data ? 'Attendance_Report' : 'Attendance_Template';
            $safeName = preg_replace('/[^\w.\- ]+/u', '_', "{$filename}_{$subject->subject_name}_{$class->class_name}") ?: 'Attendance';

            return $pdf->download($safeName . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Exam attendance template failed', [
                'examID' => $request->examID,
                'subjectID' => $request->subjectID,
                'classID' => $request->classID,
                'subclassID' => $subclassRaw,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Could not build the attendance PDF. ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getAttendanceSummary(Request $request, $examID)
    {
        $schoolID = Session::get('schoolID');
        $classFilter = $request->classID;
        $subclassFilter = $request->subclassID;
        $subjectFilter = $request->subjectID;
        
        $exam = Examination::find($examID);
        if (!$exam) return response()->json(['error' => 'Exam not found'], 404);

        $classIds = [];
        if ($classFilter && $classFilter !== 'all') {
            $classIds = [$classFilter];
        } else {
            $classIds = $this->getParticipatingClassIds($exam, $schoolID);
        }

        // Fetch all attendance counts for this exam grouped by subject and subclass
        $attendanceData = ExamAttendanceRecord::where('exam_attendance_records.examID', $examID)
            ->where('exam_attendance_records.status', 'Present')
            ->join('students', 'exam_attendance_records.studentID', '=', 'students.studentID')
            ->select('exam_attendance_records.subjectID', 'students.subclassID', DB::raw('count(*) as count'))
            ->groupBy('exam_attendance_records.subjectID', 'students.subclassID')
            ->get();
            
        // Create a lookup map: [subjectID][subclassID] => count
        $countsMap = [];
        foreach ($attendanceData as $row) {
            $countsMap[$row->subjectID][$row->subclassID] = $row->count;
        }

        $summary = [];
        $classes = ClassModel::whereIn('classID', $classIds)->get();

        foreach ($classes as $class) {
            $subclasses = Subclass::where('classID', $class->classID)
                ->when($subclassFilter && $subclassFilter !== 'all', function($q) use ($subclassFilter) {
                    return $q->where('subclassID', $subclassFilter);
                })
                ->get();
            
            $classSubclassesData = [];

            foreach ($subclasses as $subclass) {
                $subjects = ClassSubject::where('subclassID', $subclass->subclassID)
                    ->when($subjectFilter && $subjectFilter !== 'all', function($q) use ($subjectFilter) {
                        return $q->where('subjectID', $subjectFilter);
                    })
                    ->with(['subject', 'teacher'])
                    ->get();

                if ($subjects->isEmpty()) continue;

                $studentCount = count(
                    app(StudentEnrollmentService::class)->studentIDsInSubclass(
                        (int) $subclass->subclassID,
                        (int) $schoolID,
                        'Active',
                    ),
                );

                $subclassSubjectsData = $subjects->map(function($cs) use ($countsMap, $subclass, $studentCount) {
                    $present = $countsMap[$cs->subjectID][$subclass->subclassID] ?? 0;

                    return [
                        'subjectID' => $cs->subjectID,
                        'subject_name' => $cs->subject->subject_name,
                        'teacher_name' => $cs->teacher ? $cs->teacher->first_name . ' ' . $cs->teacher->last_name : 'N/A',
                        'expected' => $studentCount,
                        'present' => $present
                    ];
                });

                $classSubclassesData[] = [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'student_count' => $studentCount,
                    'subjects' => $subclassSubjectsData
                ];
            }

            if (!empty($classSubclassesData)) {
                $summary[] = [
                    'classID' => $class->classID,
                    'class_name' => $class->class_name,
                    'student_count' => count(
                        app(StudentEnrollmentService::class)->studentIDsInClass(
                            (int) $class->classID,
                            (int) $schoolID,
                            'Active',
                        ),
                    ),
                    'subclasses' => $classSubclassesData
                ];
            }
        }

        return response()->json(['success' => true, 'summary' => $summary]);
    }

    public function getAttendanceStats(Request $request, $examID)
    {
        $schoolID = Session::get('schoolID');
        $classFilter = $request->classID;
        $subclassFilter = $request->subclassID;
        $subjectFilter = $request->subjectID;

        $query = DB::table('exam_attendance_records')
            ->join('students', 'exam_attendance_records.studentID', '=', 'students.studentID')
            ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('exam_attendance_records.examID', $examID);

        if ($classFilter && $classFilter !== 'all') {
            $query->where('subclasses.classID', $classFilter);
        }
        if ($subclassFilter && $subclassFilter !== 'all') {
            $query->where('students.subclassID', $subclassFilter);
        }
        if ($subjectFilter && $subjectFilter !== 'all') {
            $query->where('exam_attendance_records.subjectID', $subjectFilter);
        }

        $stats = (clone $query)
            ->select('classes.class_name', 
                     DB::raw('SUM(CASE WHEN exam_attendance_records.status = "Present" THEN 1 ELSE 0 END) as present_count'),
                     DB::raw('SUM(CASE WHEN exam_attendance_records.status = "Absent" THEN 1 ELSE 0 END) as absent_count'))
            ->groupBy('classes.class_name')
            ->get();

        $totals = $query->select(
            DB::raw('SUM(CASE WHEN exam_attendance_records.status = "Present" THEN 1 ELSE 0 END) as total_present'),
            DB::raw('SUM(CASE WHEN exam_attendance_records.status = "Absent" THEN 1 ELSE 0 END) as total_absent')
        )->first();

        return response()->json([
            'success' => true, 
            'stats' => $stats,
            'totals' => $totals
        ]);
    }

    /**
     * Resolve which main classes participate in an exam.
     * Special exams store included classes via results/halls, not on the examination row.
     */
    private function getParticipatingClassIds(Examination $exam, $schoolID): array
    {
        if ($exam->exam_category === 'special_exams') {
            $classIds = Result::where('results.examID', $exam->examID)
                ->join('subclasses', 'results.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->distinct()
                ->pluck('classes.classID')
                ->toArray();

            if (empty($classIds)) {
                $classIds = DB::table('exam_halls')
                    ->where('examID', $exam->examID)
                    ->where('schoolID', $schoolID)
                    ->distinct()
                    ->pluck('classID')
                    ->toArray();
            }

            return $classIds;
        }

        $exceptIds = $exam->except_class_ids ?? [];

        return ClassModel::where('schoolID', $schoolID)
            ->whereNotIn('classID', $exceptIds)
            ->pluck('classID')
            ->toArray();
    }

    private function getStudentsWithExamNumbers($examID, $classID, $subclassID = null)
    {
        $schoolID = (int) Session::get('schoolID');
        $school = School::find($schoolID);
        $enrollmentService = app(StudentEnrollmentService::class);

        // Exam numbers sequence from Active enrollments in the whole class (header year)
        $activeInClassIds = $enrollmentService->studentIDsInClass(
            (int) $classID,
            $schoolID,
            'Active',
        );

        $examCenterNo = ! empty($school->exam_center_number)
            ? $school->exam_center_number
            : 'SET-CENTER-NO';

        $examNumberByStudent = [];
        if (! empty($activeInClassIds)) {
            $ordered = Student::whereIn('studentID', $activeInClassIds)
                ->orderByRaw("CASE 
                    WHEN gender LIKE 'F%' OR gender LIKE 'K%' THEN 1 
                    ELSE 2 
                END ASC")
                ->orderBy('first_name', 'asc')
                ->get(['studentID']);
            foreach ($ordered as $index => $row) {
                $sequence = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $examNumberByStudent[(int) $row->studentID] = "{$examCenterNo}/{$sequence}";
            }
        }

        // Roster: year enrollments (Active editable; others greyed)
        if ($subclassID && $subclassID !== 'all') {
            $enrollments = $enrollmentService->enrollmentsInSubclass(
                (int) $subclassID,
                $schoolID,
                'all',
            );
        } else {
            $enrollments = $enrollmentService->enrollmentsInClass(
                (int) $classID,
                $schoolID,
                'all',
            );
        }

        $enrollmentByStudent = $enrollments->keyBy('studentID');
        $studentIds = $enrollments->pluck('studentID')->map(fn ($id) => (int) $id)->all();

        if ($studentIds === []) {
            return collect();
        }

        return Student::whereIn('studentID', $studentIds)
            ->orderByRaw("CASE 
                WHEN gender LIKE 'F%' OR gender LIKE 'K%' THEN 1 
                ELSE 2 
            END ASC")
            ->orderBy('first_name', 'asc')
            ->get()
            ->map(function ($student) use ($enrollmentByStudent, $examNumberByStudent) {
                $enr = $enrollmentByStudent->get($student->studentID);
                $status = $enr ? (string) $enr->status : null;
                $student->examination_number =
                    $examNumberByStudent[(int) $student->studentID] ?? null;
                $student->enrollmentID = $enr ? (int) $enr->enrollmentID : null;
                $student->enrollment_status = $status;
                $student->can_enter_result = $status === 'Active';
                if ($enr && $enr->subclass) {
                    $student->setRelation('subclass', $enr->subclass);
                    $student->subclassID = $enr->subclassID;
                }
                return $student;
            });
    }
}
