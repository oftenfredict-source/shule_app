<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\Examination;
use App\Models\ExamAttendanceAssignment;
use App\Models\ExamAttendanceRecord;
use App\Models\School;
use App\Models\SchoolSubject;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Services\SmsService;
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

        $classes = [];
        if ($exam->exam_category === 'special_exams') {
            $includeIds = $exam->include_class_ids ?? [];
            $classes = ClassModel::whereIn('classID', $includeIds)->get();
        } else {
            $exceptIds = $exam->except_class_ids ?? [];
            $classes = ClassModel::where('schoolID', $schoolID)
                ->whereNotIn('classID', $exceptIds)
                ->get();
        }

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
            'assignment' => $assignment,
            'details' => $details
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
                'status' => $records[$student->studentID]->status ?? 'Present'
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $studentData
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

        $students = $this->getStudentsWithExamNumbers($request->examID, $request->classID, $subclassRaw);

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

        $pdf      = PDF::loadView('Admin.pdf.exam_attendance_template', $data);
        $filename = $request->with_data ? 'Attendance_Report' : 'Attendance_Template';
        return $pdf->download("{$filename}_{$subject->subject_name}_{$class->class_name}.pdf");
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
            if ($exam->exam_category === 'special_exams') {
                $classIds = $exam->include_class_ids ?? [];
            } else {
                $exceptIds = $exam->except_class_ids ?? [];
                $classIds = ClassModel::where('schoolID', $schoolID)
                    ->whereNotIn('classID', $exceptIds)
                    ->pluck('classID')
                    ->toArray();
            }
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

                $studentCount = Student::where('subclassID', $subclass->subclassID)->where('status', 'Active')->count();

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
                    'student_count' => Student::join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                        ->where('subclasses.classID', $class->classID)
                        ->where('students.status', 'Active')
                        ->count(),
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

    private function getStudentsWithExamNumbers($examID, $classID, $subclassID = null)
    {
        $exam = Examination::find($examID);
        $school = School::find(Session::get('schoolID'));
        
        // Always get all students for the main class to ensure consistent sequence numbers
        $allStudentsInClass = Student::join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
            ->where('subclasses.classID', $classID)
            ->where('students.status', 'Active')
            ->select('students.*')
            ->orderByRaw("CASE 
                WHEN students.gender LIKE 'F%' OR students.gender LIKE 'K%' THEN 1 
                ELSE 2 
            END ASC")
            ->orderBy('students.first_name', 'asc')
            ->get();
            
        $examCenterNo = !empty($school->exam_center_number) ? $school->exam_center_number : 'SET-CENTER-NO';
        
        foreach ($allStudentsInClass as $index => $student) {
            $sequence = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $student->examination_number = "{$examCenterNo}/{$sequence}";
        }
        
        // Now filter by subclass if specified
        if ($subclassID && $subclassID !== 'all') {
            return $allStudentsInClass->where('subclassID', $subclassID);
        }
        
        return $allStudentsInClass;
    }
}
