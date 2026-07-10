<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\StudentFingerprintAttendance;
use App\Models\TeacherFingerprintAttendance;
use App\Models\StaffFingerprintAttendance;
use App\Models\TeacherStaffWorkTimeSetting;
use App\Models\StudentWorkTimeSetting;
use App\Services\FingerprintAttendanceSetupService;
use App\Services\SchoolWorkingDaysService;
use App\Services\TeacherStaffPermissionAttendanceService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Services\SmsService;

class ManageAttendanceController extends Controller
{
    public function manageAttendance(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');

        if (!$userType || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Check if coordinator view
        $isCoordinatorView = $request->input('coordinator') === 'true';
        $classIDParam = $request->input('classID');
        $isCoordinatorView = $isCoordinatorView && $classIDParam;
        
        $selectedClass = null;
        $coordinatorSubclasses = collect();
        
        if ($isCoordinatorView && $classIDParam) {
            // Decrypt classID if encrypted
            try {
                $decryptedClassID = Crypt::decrypt($classIDParam);
            } catch (\Exception $e) {
                $decryptedClassID = $classIDParam;
            }
            
            // Verify teacher is coordinator for this class
            $selectedClass = ClassModel::where('classID', $decryptedClassID)
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();
            
            if (!$selectedClass) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }
            
            // Get subclasses for this main class
            $coordinatorSubclasses = Subclass::where('classID', $decryptedClassID)
                ->where('status', 'Active')
                ->with('class')
                ->get()
                ->map(function($subclass) {
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = empty($subclassName) 
                        ? $subclass->class->class_name 
                        : $subclass->class->class_name . ' - ' . $subclassName;
                    
                    return (object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $displayName,
                        'class_name' => $subclass->class->class_name,
                    ];
                });
        }

        // Get all subclasses for dropdown with display names (for non-coordinator view)
        $allSubclasses = Subclass::with('class')
            ->whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->get()
            ->groupBy('classID');
        
        // Filter and format subclasses
        $subclasses = collect();
        foreach ($allSubclasses as $classID => $classSubclasses) {
            // If class has only one subclass and it's default (empty name), include it but show only class name
            if ($classSubclasses->count() === 1) {
                $subclass = $classSubclasses->first();
                if (trim($subclass->subclass_name) === '') {
                    // Default subclass - show only class name
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name,
                        'class_name' => $subclass->class->class_name,
                    ]);
                } else {
                    // Single subclass with name
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name . ' ' . $subclass->subclass_name,
                        'class_name' => $subclass->class->class_name,
                    ]);
                }
            } else {
                // Multiple subclasses - show all with class_name + subclass_name
                foreach ($classSubclasses as $subclass) {
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = empty($subclassName) 
                        ? $subclass->class->class_name 
                        : $subclass->class->class_name . ' ' . $subclassName;
                    
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $displayName,
                        'class_name' => $subclass->class->class_name,
                    ]);
                }
            }
        }
        
        // Sort by class_name then subclass_name
        $subclasses = $subclasses->sortBy(function($item) {
            return $item->class_name . ' ' . $item->subclass_name;
        })->values();

        // Get school details for exports
        $school = School::where('schoolID', $schoolID)->first();
        $school_details = (object)[
            'school_name' => $school->school_name ?? 'School',
            'school_logo' => $school->school_logo ?? null
        ];

        return view('Admin.manage_attendance', compact('subclasses', 'school_details', 'isCoordinatorView', 'selectedClass', 'coordinatorSubclasses', 'classIDParam'));
    }

    public function manageTeacherStaffAttendance(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        if (!in_array($userType, ['Admin', 'Staff'], true)) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $school = School::where('schoolID', $schoolID)->first();
        $school_details = (object) [
            'school_name' => $school->school_name ?? 'School',
            'school_logo' => $school->school_logo ?? null,
        ];

        $teacherCount = Teacher::where('schoolID', $schoolID)->where('status', 'Active')->count();
        $staffCount = OtherStaff::where('schoolID', $schoolID)->where('status', 'Active')->count();
        $workTime = TeacherStaffWorkTimeSetting::forSchool((int) $schoolID);
        $setupData = $this->fingerprintSetupViewData();

        return view('Admin.manage_teacher_staff_attendance', compact(
            'school_details',
            'teacherCount',
            'staffCount',
            'workTime'
        ) + $setupData);
    }

    public function manageStudentFingerprintAttendance(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (! $userType || ! $schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        if (! in_array($userType, ['Admin', 'Staff'], true)) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $school = School::where('schoolID', $schoolID)->first();
        $school_details = (object) [
            'school_name' => $school->school_name ?? 'School',
            'school_logo' => $school->school_logo ?? null,
        ];

        $studentCount = Student::where('schoolID', $schoolID)->where('status', '!=', 'Transferred')->count();
        $workTime = StudentWorkTimeSetting::forSchool((int) $schoolID);

        $subclasses = Subclass::with('class')
            ->whereHas('class', function ($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })
            ->get()
            ->map(function ($subclass) {
                $subName = trim($subclass->subclass_name ?? '');
                $className = $subclass->class->class_name ?? '';
                $display = trim($className.($subName !== '' ? ' '.$subName : ''));

                return (object) [
                    'subclassID' => $subclass->subclassID,
                    'classID' => $subclass->classID,
                    'subclass_name' => $subName,
                    'main_class_name' => $className,
                    'display_name' => $display !== '' ? $display : 'Class',
                ];
            })
            ->sortBy('display_name')
            ->values();

        $mainClasses = ClassModel::where('schoolID', $schoolID)
            ->orderBy('class_name')
            ->get(['classID', 'class_name']);

        return view('Admin.manage_student_fingerprint_attendance', compact(
            'school_details',
            'studentCount',
            'workTime',
            'subclasses',
            'mainClasses'
        ) + $this->fingerprintSetupViewData());
    }

    public function getStudentWorkTime()
    {
        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $settings = StudentWorkTimeSetting::forSchool((int) $schoolID);

        return response()->json([
            'success' => true,
            'work_start_time' => substr((string) $settings->work_start_time, 0, 5),
            'work_end_time' => substr((string) $settings->work_end_time, 0, 5),
        ]);
    }

    public function saveStudentWorkTime(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i',
        ]);

        $settings = StudentWorkTimeSetting::forSchool((int) $schoolID);
        $settings->update([
            'work_start_time' => $validated['work_start_time'].':00',
            'work_end_time' => $validated['work_end_time'].':00',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student working time saved.',
            'work_start_time' => substr((string) $settings->work_start_time, 0, 5),
            'work_end_time' => substr((string) $settings->work_end_time, 0, 5),
        ]);
    }

    public function getStudentFingerprintSavedRecords(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $dateFilter = $request->input('date');
        $classID = $request->input('classID') ? (int) $request->input('classID') : null;
        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;

        if (Session::get('user_type') === 'Teacher') {
            try {
                [$classID, $subclassID] = $this->resolveStudentFingerprintReportScope($request, (int) $schoolID);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
        }

        $query = StudentFingerprintAttendance::with(['student.subclass.class'])
            ->whereHas('student', function ($q) use ($schoolID, $subclassID, $classID) {
                $q->where('schoolID', $schoolID)->where('status', '!=', 'Transferred');
                if ($subclassID) {
                    $q->where('subclassID', $subclassID);
                } elseif ($classID) {
                    $q->whereHas('subclass', fn ($sq) => $sq->where('classID', $classID));
                }
            });

        if ($dateFilter) {
            $query->whereDate('attendance_date', $dateFilter);
        }

        $total = $query->count();
        $records = $query->orderByDesc('attendance_date')
            ->orderByDesc('check_in_time')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $data = $records->map(function ($record) {
            $student = $record->student;
            $classLabel = 'N/A';
            if ($student && $student->subclass) {
                $main = $student->subclass->class->class_name ?? '';
                $sub = trim($student->subclass->subclass_name ?? '');
                $classLabel = trim($main.($sub !== '' ? ' '.$sub : ''));
            }

            return [
                'studentID' => $record->studentID,
                'user_name' => $record->user_name,
                'class_name' => $classLabel,
                'gender' => $student->gender ?? null,
                'fingerprint_id' => $student->fingerprint_id ?? $record->studentID,
                'attendance_date' => $record->attendance_date?->format('Y-m-d'),
                'check_in_time' => $record->check_in_time?->format('Y-m-d H:i:s'),
                'check_out_time' => $record->check_out_time?->format('Y-m-d H:i:s'),
                'status' => $record->status,
                'device_ip' => $record->device_ip,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'total' => $total,
            ],
        ]);
    }

    public function studentFingerprintAttendanceReport(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'classID' => 'nullable|integer',
            'subclassID' => 'nullable|integer',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            [$classID, $subclassID] = $this->resolveStudentFingerprintReportScope($request, (int) $schoolID);
            $request->merge([
                'classID' => $classID,
                'subclassID' => $subclassID,
            ]);
            $payload = $this->collectStudentFingerprintReportPayload($request, $workingDaysService, (int) $schoolID);

            return response()->json([
                'success' => true,
                'title' => $payload['title'],
                'filter_type' => $payload['filter_type'],
                'period_label' => $payload['range']['label'],
                'period_incomplete' => $payload['range']['incomplete'],
                'total_working_days' => $payload['workingDayCounts']['display_working_days'],
                'complete_working_days' => $payload['workingDayCounts']['elapsed_working_days'],
                'elapsed_working_days' => $payload['workingDayCounts']['elapsed_working_days'],
                'work_start_time' => substr((string) $payload['workTime']->work_start_time, 0, 5),
                'work_end_time' => substr((string) $payload['workTime']->work_end_time, 0, 5),
                'gender_totals' => $payload['gender_totals'],
                'attendance_overview' => $payload['attendance_overview'],
                'data' => $payload['rows'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function exportStudentFingerprintAttendanceReportPdf(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'classID' => 'nullable|integer',
            'subclassID' => 'nullable|integer',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            [$classID, $subclassID] = $this->resolveStudentFingerprintReportScope($request, (int) $schoolID);
            $request->merge([
                'classID' => $classID,
                'subclassID' => $subclassID,
            ]);
            $payload = $this->collectStudentFingerprintReportPayload($request, $workingDaysService, (int) $schoolID);
            $school = School::find((int) $schoolID);

            $data = [
                'school' => $school,
                'title' => $payload['title'],
                'filter_type' => $payload['filter_type'],
                'period_label' => $payload['range']['label'],
                'period_incomplete' => $payload['range']['incomplete'],
                'total_working_days' => $payload['workingDayCounts']['display_working_days'],
                'complete_working_days' => $payload['workingDayCounts']['elapsed_working_days'],
                'elapsed_working_days' => $payload['workingDayCounts']['elapsed_working_days'],
                'work_start_time' => substr((string) $payload['workTime']->work_start_time, 0, 5),
                'work_end_time' => substr((string) $payload['workTime']->work_end_time, 0, 5),
                'gender_totals' => $payload['gender_totals'],
                'attendance_overview' => $payload['attendance_overview'],
                'rows' => $payload['rows'],
                'generated_at' => now()->format('d M Y H:i'),
            ];

            $pdf = PDF::loadView('Admin.pdf.student_fingerprint_attendance_report_pdf', $data);
            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
            ]);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'Student_Fingerprint_Attendance_Report_'.date('YmdHis').'.pdf';

            return $pdf->download($filename);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array{
     *     title: string,
     *     filter_type: string,
     *     range: array<string, mixed>,
     *     workingDayCounts: array<string, mixed>,
     *     workTime: StudentWorkTimeSetting,
     *     gender_totals: array<string, int>,
     *     rows: array<int, array<string, mixed>>
     * }
     */
    private function collectStudentFingerprintReportPayload(
        Request $request,
        SchoolWorkingDaysService $workingDaysService,
        int $schoolID
    ): array {
        $filterType = $request->input('filter_type');
        $classID = $request->input('classID') ? (int) $request->input('classID') : null;
        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;

        $range = $this->resolveTeacherStaffReportDateRange($request);
        $workingDayCounts = $this->resolveReportWorkingDayCounts(
            $workingDaysService,
            $schoolID,
            $filterType,
            $range
        );
        $workingDates = $workingDayCounts['working_dates'];
        $workTime = StudentWorkTimeSetting::forSchool($schoolID);
        $students = $this->getStudentReportPeople($schoolID, $classID, $subclassID);
        $studentIds = array_column($students, 'student_id');
        $attendanceMap = $this->loadStudentFingerprintAttendanceMap(
            $studentIds,
            $range['start']->toDateString(),
            $range['end']->toDateString()
        );
        $permissionStatusMap = app(\App\Services\StudentPermissionAttendanceService::class)
            ->loadApprovedStatusMap(
                $schoolID,
                $range['start']->toDateString(),
                $range['end']->toDateString(),
                $studentIds
            );

        $rows = [];
        foreach ($students as $student) {
            $studentKey = 'student_'.$student['student_id'];
            $studentAttendance = $attendanceMap[$studentKey] ?? [];
            $permissionByDate = $permissionStatusMap[$studentKey] ?? [];
            $stats = $this->calculateStudentFingerprintAttendanceStats(
                $studentAttendance,
                $workingDates,
                (string) $workTime->work_start_time,
                (string) $workTime->work_end_time,
                $permissionByDate
            );

            $rows[] = array_merge($student, [
                'present_days' => $stats['present_days'],
                'absent_days' => $stats['absent_days'],
                'permission_days' => $stats['permission_days'],
                'sick_days' => $stats['sick_days'],
                'total_working_days' => $workingDayCounts['display_working_days'],
                'complete_working_days' => $workingDayCounts['elapsed_working_days'],
                'elapsed_working_days' => $workingDayCounts['elapsed_working_days'],
                'late_days' => $stats['late_days'],
                'early_days' => $stats['early_arrival_days'],
                'early_arrival_days' => $stats['early_arrival_days'],
                'early_departure_days' => $stats['early_departure_days'],
                'no_checkout_days' => $stats['no_checkout_days'],
            ]);
        }

        usort($rows, function ($a, $b) {
            $classCmp = strcasecmp($a['class_name'], $b['class_name']);
            if ($classCmp !== 0) {
                return $classCmp;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        $genderTotals = [
            'male_present' => 0,
            'female_present' => 0,
            'male_absent' => 0,
            'female_absent' => 0,
        ];
        if ($filterType === 'day') {
            $reportDate = $range['start']->toDateString();
            $permService = app(\App\Services\StudentPermissionAttendanceService::class);
            foreach ($students as $student) {
                $studentKey = 'student_'.$student['student_id'];
                $dayRecord = $attendanceMap[$studentKey][$reportDate] ?? null;
                $permStatus = $permService->statusForStudentOnDate(
                    $permissionStatusMap,
                    (int) $student['student_id'],
                    $reportDate
                );
                $dayStatus = $permService->resolveFingerprintDayStatus(
                    $dayRecord['check_in_time'] ?? null,
                    $permStatus
                );
                $gender = strtolower((string) ($student['gender'] ?? ''));
                if ($dayStatus === 'Present') {
                    if ($gender === 'male') {
                        $genderTotals['male_present']++;
                    } elseif ($gender === 'female') {
                        $genderTotals['female_present']++;
                    }
                } elseif ($dayStatus === 'Absent') {
                    if ($gender === 'male') {
                        $genderTotals['male_absent']++;
                    } elseif ($gender === 'female') {
                        $genderTotals['female_absent']++;
                    }
                }
            }
        }

        $attendanceOverview = $this->buildStudentFingerprintAttendanceOverview(
            $filterType,
            $rows,
            $students,
            $attendanceMap,
            $range,
            (string) $workTime->work_start_time,
            (string) $workTime->work_end_time
        );

        return [
            'title' => $this->buildStudentReportTitle($filterType, $range, $classID, $subclassID),
            'filter_type' => $filterType,
            'range' => $range,
            'workingDayCounts' => $workingDayCounts,
            'workTime' => $workTime,
            'gender_totals' => $genderTotals,
            'attendance_overview' => $attendanceOverview,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getStudentReportPeople(int $schoolID, ?int $classID, ?int $subclassID): array
    {
        $query = Student::with(['subclass.class'])
            ->where('schoolID', $schoolID)
            ->where('status', '!=', 'Transferred');

        if ($subclassID) {
            $query->where('subclassID', $subclassID);
        } elseif ($classID) {
            $query->whereHas('subclass', fn ($q) => $q->where('classID', $classID));
        }

        return $query->get()->map(fn ($student) => $this->formatStudentReportPersonRow($student))->all();
    }

    private function formatStudentReportPersonRow(Student $student): array
    {
        $name = trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''));
        $mainClass = $student->subclass->class->class_name ?? '';
        $subName = trim($student->subclass->subclass_name ?? '');
        $className = trim($mainClass.($subName !== '' ? ' '.$subName : ''));

        if ($student->photo) {
            $imageUrl = asset('userImages/'.$student->photo);
        } else {
            $imageUrl = strtolower((string) ($student->gender ?? '')) === 'female'
                ? asset('images/female.png')
                : asset('images/male.png');
        }

        return [
            'student_id' => (int) $student->studentID,
            'name' => $name !== '' ? $name : 'N/A',
            'class_name' => $className !== '' ? $className : 'N/A',
            'main_class_name' => $mainClass !== '' ? $mainClass : 'N/A',
            'subclass_name' => $subName !== '' ? $subName : '—',
            'subclassID' => (int) ($student->subclassID ?? 0),
            'classID' => (int) ($student->subclass->classID ?? 0),
            'gender' => $this->formatGenderLabel($student->gender ?? null),
            'admission_number' => $student->admission_number ?? null,
            'fingerprint_id' => $student->fingerprint_id ?? $student->studentID,
            'image_url' => $imageUrl,
        ];
    }

    /**
     * @param array<int, int|string> $studentIds
     * @return array<string, array<string, array{check_in_time: ?string, check_out_time: ?string}>>
     */
    private function loadStudentFingerprintAttendanceMap(array $studentIds, string $fromDate, string $toDate): array
    {
        $map = [];
        if (empty($studentIds)) {
            return $map;
        }

        DB::table('student_fingerprint_attendance')
            ->select(['studentID', 'attendance_date', 'check_in_time', 'check_out_time'])
            ->whereIn('studentID', $studentIds)
            ->where('attendance_date', '>=', $fromDate)
            ->where('attendance_date', '<=', $toDate)
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$map) {
                foreach ($rows as $row) {
                    $key = 'student_'.$row->studentID;
                    $date = substr((string) $row->attendance_date, 0, 10);
                    $map[$key][$date] = [
                        'check_in_time' => $this->normalizeAttendanceDateTime($row->check_in_time),
                        'check_out_time' => $this->normalizeAttendanceDateTime($row->check_out_time),
                    ];
                }
            });

        return $map;
    }

    private function formatGenderLabel(?string $gender): string
    {
        $value = strtolower(trim((string) $gender));
        if ($value === 'male' || $value === 'm') {
            return 'Male';
        }
        if ($value === 'female' || $value === 'f') {
            return 'Female';
        }

        return $gender !== null && trim((string) $gender) !== '' ? ucfirst(trim((string) $gender)) : '-';
    }

    private function buildStudentReportTitle(
        string $filterType,
        array $range,
        ?int $classID,
        ?int $subclassID
    ): string {
        $scope = 'STUDENTS FINGERPRINT ATTENDANCE REPORT';

        if ($subclassID) {
            $sub = Subclass::with('class')->find($subclassID);
            if ($sub) {
                $main = $sub->class->class_name ?? '';
                $subName = trim($sub->subclass_name ?? '');
                $scope .= ' — '.strtoupper(trim($main.($subName !== '' ? ' '.$subName : '')));
            }
        } elseif ($classID) {
            $class = ClassModel::find($classID);
            if ($class) {
                $scope .= ' — '.strtoupper($class->class_name);
            }
        }

        if ($filterType === 'day') {
            return $scope.' ON '.strtoupper($range['start']->format('j F Y'));
        }

        if ($filterType === 'date_range') {
            return $scope.' ON '.strtoupper($range['start']->format('j F Y'))
                .' UP TO '.strtoupper($range['end']->format('j F Y'));
        }

        if ($filterType === 'month') {
            return $scope.' ON '.strtoupper($range['start']->format('F Y'));
        }

        return $scope.' FOR YEAR '.$range['start']->year;
    }

    public function searchAttendance(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $searchType = $request->input('search_type'); // date, year, month, week, day, subclass, all_school
            $subclassID = $request->input('subclassID');
            $classID = $request->input('classID');
            $isCoordinatorView = $request->input('coordinator') === 'true';
            $date = $request->input('date');
            $year = $request->input('year');
            $month = $request->input('month');
            $week = $request->input('week');
            $day = $request->input('day');
            $studentID = $request->input('studentID');

            $query = Attendance::join('students', 'attendances.studentID', '=', 'students.studentID')
                ->join('subclasses', 'attendances.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('attendances.schoolID', $schoolID)
                ->select(
                    'attendances.*',
                    'students.first_name',
                    'students.middle_name',
                    'students.last_name',
                    'students.admission_number',
                    'students.photo',
                    'students.gender',
                    'subclasses.subclass_name',
                    'classes.class_name'
                );
            
            // Coordinator view: filter by main class
            if ($isCoordinatorView && $classID) {
                try {
                    $decryptedClassID = Crypt::decrypt($classID);
                } catch (\Exception $e) {
                    $decryptedClassID = $classID;
                }
                $query->where('subclasses.classID', $decryptedClassID);
                
                // If subclass is selected, filter by that subclass
                if ($subclassID) {
                    $query->where('attendances.subclassID', $subclassID);
                }
            } elseif ($subclassID) {
                // Regular subclass filter
                $query->where('attendances.subclassID', $subclassID);
            }

            // Apply search filters
            switch ($searchType) {
                case 'date':
                    if ($date) {
                        $query->whereDate('attendances.attendance_date', $date);
                    }
                    break;
                case 'year':
                    if ($year) {
                        $query->whereYear('attendances.attendance_date', $year);
                    }
                    break;
                case 'month':
                    if ($year && $month) {
                        $query->whereYear('attendances.attendance_date', $year)
                              ->whereMonth('attendances.attendance_date', $month);
                    }
                    break;
                case 'week':
                    if ($week && $year) {
                        try {
                            $startDate = Carbon::create($year, 1, 1)->setISODate($year, $week, 1)->startOfWeek();
                            $endDate = $startDate->copy()->endOfWeek();
                            $query->whereBetween('attendances.attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                        } catch (\Exception $e) {
                            // Invalid week number
                        }
                    }
                    break;
                case 'day':
                    if ($day) {
                        $query->whereDay('attendances.attendance_date', $day);
                    }
                    break;
                case 'subclass':
                    if ($subclassID) {
                        $query->where('attendances.subclassID', $subclassID);
                    }
                    break;
                case 'all_school':
                    // No additional filter, already filtered by schoolID
                    break;
            }

            // Filter by student if provided
            if ($studentID) {
                $query->where('attendances.studentID', $studentID);
            }

            $attendances = $query->orderBy('attendances.attendance_date', 'desc')
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->orderBy('students.first_name')
                ->get();
            // Calculate statistics
            $stats = $this->calculateAttendanceStats($attendances, $searchType, $date, $year, $month, $week, $subclassID, $schoolID);

            // Get attendance by class with student details
            $attendanceByClass = $this->getAttendanceByClass($searchType, $date, $year, $month, $week, $subclassID, $schoolID, $classID, $isCoordinatorView);

            return response()->json([
                'success' => true,
                'attendances' => $attendances,
                'stats' => $stats,
                'attendance_by_class' => $attendanceByClass
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentAttendanceDetails($studentID, Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $period = $request->input('period', 'month'); // month, year
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));
            $week = $request->input('week');
            $date = $request->input('date');
            $searchType = $request->input('search_type', 'month'); // month, year, week, date

            $query = Attendance::where('studentID', $studentID)
                ->where('schoolID', $schoolID);

            // Apply filters based on search type
            if ($searchType == 'year' && $year) {
                $query->whereYear('attendance_date', $year);
            } elseif ($searchType == 'month' && $year && $month) {
                $query->whereYear('attendance_date', $year)
                      ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'date' && $date) {
                $query->whereDate('attendance_date', $date);
            } else {
                // Default to month if period is specified
                if ($period == 'year') {
                    $query->whereYear('attendance_date', $year);
                } else {
                    $query->whereYear('attendance_date', $year)
                          ->whereMonth('attendance_date', $month);
                }
            }

            $attendances = $query->orderBy('attendance_date', 'asc')->get();

            $student = Student::find($studentID);

            // Calculate statistics
            $totalDays = $attendances->count();
            $present = $attendances->where('status', 'Present')->count();
            $absent = $attendances->where('status', 'Absent')->count();
            $sick = $attendances->where('status', 'Sick')->count();
            $excused = $attendances->where('status', 'Excused')->count();

            // Prepare data for line chart
            $chartData = $this->prepareChartData($attendances, $searchType == 'year' ? 'year' : 'month');

            return response()->json([
                'success' => true,
                'student' => $student,
                'attendances' => $attendances,
                'stats' => [
                    'total_days' => $totalDays,
                    'present' => $present,
                    'absent' => $absent,
                    'sick' => $sick,
                    'excused' => $excused
                ],
                'chart_data' => $chartData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateAttendanceStats($attendances, $searchType, $date = null, $year = null, $month = null, $week = null, $subclassID = null, $schoolID = null)
    {
        // If searching by date, month, week, or year for all school, get stats by subclass
        if (in_array($searchType, ['date', 'month', 'week', 'year']) && !$subclassID && $schoolID) {
            $query = DB::table('attendances')
                ->join('subclasses', 'attendances.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('attendances.schoolID', $schoolID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $query->whereDate('attendances.attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $query->whereYear('attendances.attendance_date', $year)
                      ->whereMonth('attendances.attendance_date', $month);
            } elseif ($searchType == 'week' && $week && $year) {
                try {
                    $startDate = Carbon::create($year, 1, 1)->setISODate($year, $week, 1)->startOfWeek();
                    $endDate = $startDate->copy()->endOfWeek();
                    $query->whereBetween('attendances.attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } catch (\Exception $e) {
                    // Invalid week
                }
            } elseif ($searchType == 'year' && $year) {
                $query->whereYear('attendances.attendance_date', $year);
            }

            $statsBySubclass = $query->select(
                    'subclasses.subclassID',
                    'subclasses.subclass_name',
                    'classes.class_name',
                    DB::raw('COUNT(DISTINCT attendances.studentID) as total_students'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Present" THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Absent" THEN 1 ELSE 0 END) as absent'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Sick" THEN 1 ELSE 0 END) as sick'),
                    DB::raw('SUM(CASE WHEN attendances.status = "Excused" THEN 1 ELSE 0 END) as excused')
                )
                ->groupBy('subclasses.subclassID', 'subclasses.subclass_name', 'classes.class_name')
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->get()
                ->map(function($item) {
                    $subclassName = trim($item->subclass_name);
                    $item->display_name = empty($subclassName) 
                        ? $item->class_name 
                        : $item->class_name . ' ' . $subclassName;
                    return $item;
                });

            // Gender statistics
            $genderQuery = DB::table('attendances')
                ->join('students', 'attendances.studentID', '=', 'students.studentID')
                ->where('attendances.schoolID', $schoolID)
                ->where('attendances.status', 'Present');

            if ($searchType == 'date' && $date) {
                $genderQuery->whereDate('attendances.attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $genderQuery->whereYear('attendances.attendance_date', $year)
                           ->whereMonth('attendances.attendance_date', $month);
            } elseif ($searchType == 'week' && $week && $year) {
                try {
                    $startDate = Carbon::create($year, 1, 1)->setISODate($year, $week, 1)->startOfWeek();
                    $endDate = $startDate->copy()->endOfWeek();
                    $genderQuery->whereBetween('attendances.attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } catch (\Exception $e) {
                    // Invalid week
                }
            } elseif ($searchType == 'year' && $year) {
                $genderQuery->whereYear('attendances.attendance_date', $year);
            }

            $genderStats = $genderQuery->select(
                    DB::raw('COUNT(DISTINCT CASE WHEN students.gender = "Male" THEN attendances.studentID END) as male_present'),
                    DB::raw('COUNT(DISTINCT CASE WHEN students.gender = "Female" THEN attendances.studentID END) as female_present')
                )
                ->first();

            return [
                'by_subclass' => $statsBySubclass,
                'gender_stats' => $genderStats,
                'total_present' => $attendances->where('status', 'Present')->count(),
                'total_absent' => $attendances->where('status', 'Absent')->count(),
                'total_sick' => $attendances->where('status', 'Sick')->count(),
                'total_excused' => $attendances->where('status', 'Excused')->count(),
                'total_students' => $attendances->unique('studentID')->count()
            ];
        }

        // General statistics
        return [
            'total_present' => $attendances->where('status', 'Present')->count(),
            'total_absent' => $attendances->where('status', 'Absent')->count(),
            'total_sick' => $attendances->where('status', 'Sick')->count(),
            'total_excused' => $attendances->where('status', 'Excused')->count(),
            'total_students' => $attendances->unique('studentID')->count()
        ];
    }

    private function getAttendanceByClass($searchType, $date = null, $year = null, $month = null, $week = null, $subclassID = null, $schoolID = null, $classID = null, $isCoordinatorView = false)
    {
        if (!in_array($searchType, ['date', 'month', 'year']) || !$schoolID) {
            return [];
        }

        // Get subclasses query
        $subclassesQuery = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID);

        // Coordinator view: filter by main class
        if ($isCoordinatorView && $classID) {
            try {
                $decryptedClassID = Crypt::decrypt($classID);
            } catch (\Exception $e) {
                $decryptedClassID = $classID;
            }
            $subclassesQuery->where('subclasses.classID', $decryptedClassID);
            
            // If subclass is selected, filter by that subclass
            if ($subclassID) {
                $subclassesQuery->where('subclasses.subclassID', $subclassID);
            }
        } elseif ($subclassID) {
            // Regular subclass filter
            $subclassesQuery->where('subclasses.subclassID', $subclassID);
        }

        $subclasses = $subclassesQuery->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'classes.class_name',
                'classes.classID'
            )
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get();

        $result = [];
        foreach ($subclasses as $subclass) {
            // Get total students in this subclass
            $totalStudents = DB::table('students')
                ->where('subclassID', $subclass->subclassID)
                ->where('schoolID', $schoolID)
                ->where('status', '!=', 'Transferred')
                ->count();

            // Check if attendance was collected for this subclass in the period
            $attendanceCheckQuery = DB::table('attendances')
                ->where('schoolID', $schoolID)
                ->where('subclassID', $subclass->subclassID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $attendanceCheckQuery->whereDate('attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $attendanceCheckQuery->whereYear('attendance_date', $year)
                                  ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $attendanceCheckQuery->whereYear('attendance_date', $year);
            }

            $hasAttendance = $attendanceCheckQuery->exists();

            if (!$hasAttendance) {
                // No attendance collected
                // Calculate display name
                $subclassName = trim($subclass->subclass_name);
                $displayName = empty($subclassName) 
                    ? $subclass->class_name 
                    : $subclass->class_name . ' ' . $subclassName;
                
                $result[] = [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'class_name' => $subclass->class_name,
                    'display_name' => $displayName,
                    'total_students' => $totalStudents,
                    'has_attendance' => false,
                    'present' => 0,
                    'absent' => 0,
                    'sick' => 0,
                    'excused' => 0,
                    'present_percentage' => 0,
                    'absent_percentage' => 0,
                    'sick_percentage' => 0,
                    'excused_percentage' => 0,
                    'students' => []
                ];
                continue;
            }

            // Get attendance stats for this subclass
            $attendanceStatsQuery = DB::table('attendances')
                ->where('schoolID', $schoolID)
                ->where('subclassID', $subclass->subclassID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $attendanceStatsQuery->whereDate('attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $attendanceStatsQuery->whereYear('attendance_date', $year)
                                  ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $attendanceStatsQuery->whereYear('attendance_date', $year);
            }

            $attendanceStats = $attendanceStatsQuery
                ->select(
                    DB::raw('COUNT(DISTINCT studentID) as total_students_with_attendance'),
                    DB::raw('SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent'),
                    DB::raw('SUM(CASE WHEN status = "Sick" THEN 1 ELSE 0 END) as sick'),
                    DB::raw('SUM(CASE WHEN status = "Excused" THEN 1 ELSE 0 END) as excused')
                )
                ->first();

            $present = $attendanceStats->present ?? 0;
            $absent = $attendanceStats->absent ?? 0;
            $sick = $attendanceStats->sick ?? 0;
            $excused = $attendanceStats->excused ?? 0;
            $totalRecords = $present + $absent + $sick + $excused;

            // Calculate percentages for month and year
            $presentPercentage = 0;
            $absentPercentage = 0;
            $sickPercentage = 0;
            $excusedPercentage = 0;

            if (in_array($searchType, ['month', 'year']) && $totalStudents > 0) {
                // For month/year, calculate percentage of students
                $presentPercentage = $totalRecords > 0 ? round(($present / $totalRecords) * 100, 2) : 0;
                $absentPercentage = $totalRecords > 0 ? round(($absent / $totalRecords) * 100, 2) : 0;
                $sickPercentage = $totalRecords > 0 ? round(($sick / $totalRecords) * 100, 2) : 0;
                $excusedPercentage = $totalRecords > 0 ? round(($excused / $totalRecords) * 100, 2) : 0;
            }

            // Get students with their attendance stats
            $studentsQuery = DB::table('students')
                ->leftJoin('attendances', function($join) use ($schoolID, $subclass, $searchType, $date, $year, $month, $week) {
                    $join->on('students.studentID', '=', 'attendances.studentID')
                         ->where('attendances.schoolID', $schoolID)
                         ->where('attendances.subclassID', $subclass->subclassID);

                    if ($searchType == 'date' && $date) {
                        $join->whereDate('attendances.attendance_date', $date);
                    } elseif ($searchType == 'month' && $year && $month) {
                        $join->whereYear('attendances.attendance_date', $year)
                            ->whereMonth('attendances.attendance_date', $month);
                    } elseif ($searchType == 'year' && $year) {
                        $join->whereYear('attendances.attendance_date', $year);
                    }
                })
                ->where('students.subclassID', $subclass->subclassID)
                ->where('students.schoolID', $schoolID)
                ->where('students.status', '!=', 'Transferred')
                ->select(
                    'students.studentID',
                    'students.first_name',
                    'students.middle_name',
                    'students.last_name',
                    'students.admission_number',
                    'students.gender',
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Present" THEN 1 ELSE 0 END), 0) as days_present'),
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Absent" THEN 1 ELSE 0 END), 0) as days_absent'),
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Sick" THEN 1 ELSE 0 END), 0) as days_sick'),
                    DB::raw('COALESCE(SUM(CASE WHEN attendances.status = "Excused" THEN 1 ELSE 0 END), 0) as days_excused')
                )
                ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'students.gender')
                ->orderBy('students.first_name')
                ->get();

            // Calculate display name
            $subclassName = trim($subclass->subclass_name);
            $displayName = empty($subclassName) 
                ? $subclass->class_name 
                : $subclass->class_name . ' ' . $subclassName;
            
            $result[] = [
                'subclassID' => $subclass->subclassID,
                'subclass_name' => $subclass->subclass_name,
                'class_name' => $subclass->class_name,
                'display_name' => $displayName,
                'total_students' => $totalStudents,
                'has_attendance' => true,
                'present' => $present,
                'absent' => $absent,
                'sick' => $sick,
                'excused' => $excused,
                'present_percentage' => $presentPercentage,
                'absent_percentage' => $absentPercentage,
                'sick_percentage' => $sickPercentage,
                'excused_percentage' => $excusedPercentage,
                'students' => $studentsQuery
            ];
        }

        return $result;
    }

    private function prepareChartData($attendances, $period)
    {
        if ($period == 'year') {
            // Group by month
            return $attendances->groupBy(function($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('M');
            })->map(function($monthAttendances) {
                return [
                    'present' => $monthAttendances->where('status', 'Present')->count(),
                    'absent' => $monthAttendances->where('status', 'Absent')->count(),
                    'sick' => $monthAttendances->where('status', 'Sick')->count(),
                    'excused' => $monthAttendances->where('status', 'Excused')->count()
                ];
            });
        } else {
            // Group by day
            return $attendances->groupBy(function($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            })->map(function($dayAttendances) {
                return [
                    'present' => $dayAttendances->where('status', 'Present')->count(),
                    'absent' => $dayAttendances->where('status', 'Absent')->count(),
                    'sick' => $dayAttendances->where('status', 'Sick')->count(),
                    'excused' => $dayAttendances->where('status', 'Excused')->count()
                ];
            });
        }
    }

    public function searchFingerprintAttendance(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $searchType = $request->input('search_type'); // date, year, month
            $date = $request->input('date');
            $year = $request->input('year');
            $month = $request->input('month');
            $classID = $request->input('classID');
            $subclassID = $request->input('subclassID');
            $isCoordinatorView = $request->input('coordinator') === 'true';

            // Get attendance by class with student details
            $attendanceByClass = $this->getFingerprintAttendanceByClass($searchType, $date, $year, $month, $schoolID, $classID, $subclassID, $isCoordinatorView);

            return response()->json([
                'success' => true,
                'attendance_by_class' => $attendanceByClass
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getFingerprintAttendanceByClass($searchType, $date = null, $year = null, $month = null, $schoolID = null, $classID = null, $subclassID = null, $isCoordinatorView = false)
    {
        if (!in_array($searchType, ['date', 'month', 'year']) || !$schoolID) {
            return [];
        }

        // Get subclasses query
        $subclassesQuery = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->where('classes.schoolID', $schoolID);
        
        // Coordinator view: filter by main class
        if ($isCoordinatorView && $classID) {
            try {
                $decryptedClassID = Crypt::decrypt($classID);
            } catch (\Exception $e) {
                $decryptedClassID = $classID;
            }
            $subclassesQuery->where('subclasses.classID', $decryptedClassID);
            
            // If subclass is selected, filter by that subclass
            if ($subclassID) {
                $subclassesQuery->where('subclasses.subclassID', $subclassID);
            }
        }
        
        $subclasses = $subclassesQuery->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'classes.class_name',
                'classes.classID'
            )
            ->orderBy('classes.class_name')
            ->orderBy('subclasses.subclass_name')
            ->get();

        $result = [];
        foreach ($subclasses as $subclass) {
            // Get total students in this subclass
            $totalStudents = DB::table('students')
                ->where('subclassID', $subclass->subclassID)
                ->where('schoolID', $schoolID)
                ->where('status', '!=', 'Transferred')
                ->count();

            // Check if fingerprint attendance was collected for this subclass in the period
            $attendanceCheckQuery = DB::table('student_fingerprint_attendance')
                ->join('students', 'student_fingerprint_attendance.studentID', '=', 'students.studentID')
                ->where('students.schoolID', $schoolID)
                ->where('students.subclassID', $subclass->subclassID)
                ->where('students.status', '!=', 'Transferred');

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $attendanceCheckQuery->whereDate('student_fingerprint_attendance.attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $attendanceCheckQuery->whereYear('student_fingerprint_attendance.attendance_date', $year)
                                  ->whereMonth('student_fingerprint_attendance.attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $attendanceCheckQuery->whereYear('student_fingerprint_attendance.attendance_date', $year);
            }

            $hasAttendance = $attendanceCheckQuery->exists();

            if (!$hasAttendance) {
                // No attendance collected
                // Calculate display name
                $subclassName = trim($subclass->subclass_name);
                $displayName = empty($subclassName) 
                    ? $subclass->class_name 
                    : $subclass->class_name . ' ' . $subclassName;
                
                $result[] = [
                    'subclassID' => $subclass->subclassID,
                    'subclass_name' => $subclass->subclass_name,
                    'class_name' => $subclass->class_name,
                    'display_name' => $displayName,
                    'total_students' => $totalStudents,
                    'has_attendance' => false,
                    'present' => 0,
                    'absent' => 0,
                    'present_percentage' => 0,
                    'absent_percentage' => 0,
                    'students' => []
                ];
                continue;
            }

            // Get students with their fingerprint attendance stats
            if ($searchType == 'date' && $date) {
                // For date view, get actual check in/out times
                $studentsQuery = DB::table('students')
                    ->leftJoin('student_fingerprint_attendance', function($join) use ($date) {
                        $join->on('students.studentID', '=', 'student_fingerprint_attendance.studentID')
                             ->whereDate('student_fingerprint_attendance.attendance_date', $date);
                    })
                    ->leftJoin('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                    ->where('students.subclassID', $subclass->subclassID)
                    ->where('students.schoolID', $schoolID)
                    ->where('students.status', '!=', 'Transferred')
                    ->select(
                        'students.studentID',
                        'students.first_name',
                        'students.middle_name',
                        'students.last_name',
                        'students.admission_number',
                        'students.gender',
                        'subclasses.subclass_name',
                        DB::raw('MAX(student_fingerprint_attendance.check_in_time) as check_in_time'),
                        DB::raw('MAX(student_fingerprint_attendance.check_out_time) as check_out_time'),
                        DB::raw('COUNT(DISTINCT student_fingerprint_attendance.attendance_date) as days_present'),
                        DB::raw('0 as days_absent'),
                        DB::raw('0 as days_late'),
                        DB::raw('0 as days_excused')
                    )
                    ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'students.gender', 'subclasses.subclass_name')
                    ->orderBy('students.first_name')
                    ->get();
            } else {
                // For month/year view, get aggregated stats
                $studentsQuery = DB::table('students')
                    ->leftJoin('student_fingerprint_attendance', function($join) use ($searchType, $date, $year, $month) {
                        $join->on('students.studentID', '=', 'student_fingerprint_attendance.studentID');

                        if ($searchType == 'month' && $year && $month) {
                            $join->whereYear('student_fingerprint_attendance.attendance_date', $year)
                                ->whereMonth('student_fingerprint_attendance.attendance_date', $month);
                        } elseif ($searchType == 'year' && $year) {
                            $join->whereYear('student_fingerprint_attendance.attendance_date', $year);
                        }
                    })
                    ->leftJoin('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                    ->where('students.subclassID', $subclass->subclassID)
                    ->where('students.schoolID', $schoolID)
                    ->where('students.status', '!=', 'Transferred')
                    ->select(
                        'students.studentID',
                        'students.first_name',
                        'students.middle_name',
                        'students.last_name',
                        'students.admission_number',
                        'students.gender',
                        'subclasses.subclass_name',
                        DB::raw('COUNT(DISTINCT student_fingerprint_attendance.attendance_date) as days_present'),
                        DB::raw('0 as days_absent'),
                        DB::raw('0 as days_late'),
                        DB::raw('0 as days_excused'),
                        DB::raw('NULL as check_in_time'),
                        DB::raw('NULL as check_out_time')
                    )
                    ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'students.gender', 'subclasses.subclass_name')
                    ->orderBy('students.first_name')
                    ->get();
            }

            // Calculate stats
            $totalPresentDays = $studentsQuery->sum('days_present');
            $totalStudentsWithAttendance = $studentsQuery->where('days_present', '>', 0)->count();
            
            // For month/year, calculate working days
            $workingDays = 0;
            if ($searchType == 'month' && $year && $month) {
                $startDate = Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                if ($startDate->isCurrentMonth()) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } elseif ($searchType == 'year' && $year) {
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 12, 31);
                if ($startDate->year == Carbon::now()->year) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } else {
                $workingDays = 1; // For date search
            }

            // Calculate absent days for each student
            $studentsWithStats = $studentsQuery->map(function($student) use ($workingDays, $searchType, $subclass) {
                $daysAbsent = max(0, $workingDays - $student->days_present);
                $result = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'gender' => $student->gender,
                    'subclass_name' => $student->subclass_name ?? $subclass->subclass_name,
                    'days_present' => $student->days_present,
                    'days_absent' => $daysAbsent,
                    'days_late' => 0,
                    'days_excused' => 0
                ];
                
                // Add check in/out times for date view
                if ($searchType == 'date') {
                    $result['check_in_time'] = $student->check_in_time ? Carbon::parse($student->check_in_time)->format('H:i:s') : null;
                    $result['check_out_time'] = $student->check_out_time ? Carbon::parse($student->check_out_time)->format('H:i:s') : null;
                }
                
                return $result;
            });

            $totalAbsentDays = $studentsWithStats->sum('days_absent');
            $totalRecords = $totalPresentDays + $totalAbsentDays;

            // Calculate percentages for month and year
            $presentPercentage = 0;
            $absentPercentage = 0;

            if (in_array($searchType, ['month', 'year']) && $totalRecords > 0) {
                $presentPercentage = round(($totalPresentDays / $totalRecords) * 100, 2);
                $absentPercentage = round(($totalAbsentDays / $totalRecords) * 100, 2);
            }

            // Calculate display name
            $subclassName = trim($subclass->subclass_name);
            $displayName = empty($subclassName) 
                ? $subclass->class_name 
                : $subclass->class_name . ' ' . $subclassName;
            
            $result[] = [
                'subclassID' => $subclass->subclassID,
                'subclass_name' => $subclass->subclass_name,
                'class_name' => $subclass->class_name,
                'display_name' => $displayName,
                'total_students' => $totalStudents,
                'has_attendance' => true,
                'present' => $totalPresentDays,
                'absent' => $totalAbsentDays,
                'late' => 0,
                'excused' => 0,
                'present_percentage' => $presentPercentage,
                'absent_percentage' => $absentPercentage,
                'late_percentage' => 0,
                'excused_percentage' => 0,
                'students' => $studentsWithStats->values()
            ];
        }

        return $result;
    }

    protected function calculateWorkingDays($startDate, $endDate)
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($currentDate->dayOfWeek != Carbon::SATURDAY && $currentDate->dayOfWeek != Carbon::SUNDAY) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        
        return $workingDays;
    }

    public function getStudentFingerprintAttendanceDetails($studentID, Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $searchType = $request->input('search_type', 'month'); // month, year, date
            $date = $request->input('date');
            $year = $request->input('year');
            $month = $request->input('month');

            // Get student info
            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            // Build query for fingerprint attendance
            $query = StudentFingerprintAttendance::where('studentID', $studentID);

            // Apply date filters
            if ($searchType == 'date' && $date) {
                $query->whereDate('attendance_date', $date);
            } elseif ($searchType == 'month' && $year && $month) {
                $query->whereYear('attendance_date', $year)
                      ->whereMonth('attendance_date', $month);
            } elseif ($searchType == 'year' && $year) {
                $query->whereYear('attendance_date', $year);
            }

            // Get all attendance records
            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->get();

            // Calculate statistics
            $totalRecords = $attendanceRecords->count();
            $daysPresent = $attendanceRecords->groupBy(function($record) {
                return $record->attendance_date->format('Y-m-d');
            })->count();

            // Calculate working days
            $workingDays = 0;
            if ($searchType == 'month' && $year && $month) {
                $startDate = Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                if ($startDate->isCurrentMonth()) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } elseif ($searchType == 'year' && $year) {
                $startDate = Carbon::create($year, 1, 1);
                $endDate = Carbon::create($year, 12, 31);
                if ($startDate->year == Carbon::now()->year) {
                    $endDate = Carbon::today();
                }
                $workingDays = $this->calculateWorkingDays($startDate, $endDate);
            } else {
                $workingDays = 1; // For date search
            }

            $daysAbsent = max(0, $workingDays - $daysPresent);

            // Format attendance records
            $formattedRecords = $attendanceRecords->map(function($record) {
                return [
                    'id' => $record->id,
                    'attendance_date' => $record->attendance_date->format('Y-m-d'),
                    'attendance_date_formatted' => $record->attendance_date->format('l, F j, Y'),
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('H:i:s') : null,
                    'status' => $record->status,
                    'verify_mode' => $record->verify_mode,
                    'device_ip' => $record->device_ip,
                ];
            });

            return response()->json([
                'success' => true,
                'student' => [
                    'studentID' => $student->studentID,
                    'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'admission_number' => $student->admission_number,
                ],
                'statistics' => [
                    'days_present' => $daysPresent,
                    'days_absent' => $daysAbsent,
                    'total_days' => $workingDays,
                    'total_records' => $totalRecords,
                ],
                'attendance_records' => $formattedRecords,
                'search_type' => $searchType,
                'search_period' => $searchType == 'month' && $year && $month 
                    ? Carbon::create($year, $month, 1)->format('F Y')
                    : ($searchType == 'year' && $year ? $year : ($searchType == 'date' && $date ? Carbon::parse($date)->format('F j, Y') : '')),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get terms for year (Admin)
    public function getTermsForYear(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $year = $request->input('year');

            if (!$year || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Year and school ID required']);
            }

            // Get distinct terms from examinations table for this year
            $terms = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->whereNotNull('term')
                ->distinct()
                ->pluck('term')
                ->toArray();

            return response()->json(['success' => true, 'terms' => $terms]);
        } catch (\Exception $e) {
            \Log::error('Error getting terms for year: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get exams for year and term (Admin)
    public function getExamsForYearTerm(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $year = $request->input('year');
            $term = $request->input('term');

            if (!$year || !$term || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Year, term and school ID required']);
            }

            // Get exams for year and term
            $examIDs = DB::table('examinations')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->where('term', $term)
                ->pluck('examID')
                ->toArray();

            if (empty($examIDs)) {
                return response()->json(['success' => true, 'exams' => []]);
            }

            $exams = DB::table('examinations')
                ->whereIn('examID', $examIDs)
                ->orderBy('start_date', 'desc')
                ->select('examID', 'exam_name', 'start_date', 'end_date')
                ->get();

            return response()->json(['success' => true, 'exams' => $exams]);
        } catch (\Exception $e) {
            \Log::error('Error getting exams for year and term: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get subclasses for class (Admin)
    public function getSubclassesForClass(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $classID = $request->input('classID');

            if (!$classID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Class ID and school ID required']);
            }

            $subclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('subclasses.classID', $classID)
                ->where('classes.schoolID', $schoolID)
                ->select('subclasses.subclassID', 'subclasses.subclass_name')
                ->orderBy('subclasses.subclass_name')
                ->get();

            return response()->json(['success' => true, 'subclasses' => $subclasses]);
        } catch (\Exception $e) {
            \Log::error('Error getting subclasses for class: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get exam attendance data (Admin)
    public function getExamAttendanceData(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $examID = $request->input('examID');
            $mainClassID = $request->input('mainClassID');
            $subclassID = $request->input('subclassID');
            $subjectID = $request->input('subjectID');

            if (!$examID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Exam ID and school ID required']);
            }

            // Get subjects based on class selection (from class_subjects table)
            $classSubjects = collect();
            
            if ($subclassID) {
                // Get subjects for specific subclass
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.subclassID', $subclassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->orderBy('school_subjects.subject_name')
                    ->get();
            } elseif ($mainClassID) {
                // Get subjects for all subclasses in main class
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.classID', $mainClassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->orderBy('school_subjects.subject_name')
                    ->get();
            }

            // Get exam subject IDs from exam timetable
            $examSubjectIDs1 = DB::table('exam_timetable')
                ->where('examID', $examID)
                ->pluck('subjectID')
                ->toArray();

            $examSubjectIDs2 = DB::table('exam_timetables')
                ->where('examID', $examID)
                ->pluck('subjectID')
                ->toArray();

            $examSubjectIDs = array_unique(array_merge($examSubjectIDs1, $examSubjectIDs2));

            // Determine all exam subjects based on class selection
            if ($subclassID || $mainClassID) {
                // Filter class subjects to only include those in exam
                if (empty($examSubjectIDs) || $classSubjects->isEmpty()) {
                    $allExamSubjects = collect();
                } else {
                    $allExamSubjects = $classSubjects->filter(function($subject) use ($examSubjectIDs) {
                        return in_array($subject->subjectID, $examSubjectIDs);
                    })->values();
                }
            } else {
                // If no class selected, get all subjects from exam timetable
                $examSubjects1 = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->get();

                $examSubjects2 = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->get();

                $allExamSubjects = $examSubjects1->merge($examSubjects2)->unique('subjectID')->values();
            }

            $totalSubjects = $allExamSubjects->count();

            // Get all subclasses to filter
            $subclassQuery = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID);

            if ($subclassID) {
                $subclassQuery->where('subclasses.subclassID', $subclassID);
            } elseif ($mainClassID) {
                $subclassQuery->where('subclasses.classID', $mainClassID);
            }

            $subclassIDs = $subclassQuery->pluck('subclasses.subclassID')->toArray();

            if (empty($subclassIDs)) {
                return response()->json(['success' => true, 'data' => ['subclasses' => [], 'students' => []]]);
            }

            // Get all students in these subclasses
            $students = DB::table('students')
                ->join('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->whereIn('students.subclassID', $subclassIDs)
                ->where('students.status', 'Active')
                ->select(
                    'students.studentID',
                    'students.first_name',
                    'students.last_name',
                    'students.middle_name',
                    'subclasses.subclass_name',
                    'classes.class_name',
                    'subclasses.subclassID'
                )
                ->get();

            // Get exam attendance records grouped by student and subject
            $attendanceRecords = DB::table('exam_attendance')
                ->where('examID', $examID)
                ->whereIn('studentID', $students->pluck('studentID')->toArray())
                ->select('studentID', 'subjectID', 'status')
                ->get()
                ->groupBy('studentID')
                ->map(function($records) {
                    return $records->keyBy('subjectID');
                })
                ->toArray();

            // Filter subjects if subjectID is provided
            $filteredSubjects = $allExamSubjects;
            if ($subjectID) {
                $filteredSubjects = $allExamSubjects->where('subjectID', $subjectID);
            }

            // Process students with attendance status and subjects information
            $allStudents = [];
            foreach ($students as $student) {
                $fullName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                
                // Format class display: Main Class - Subclass (or just Main Class if subclass is empty)
                $subclassName = trim($student->subclass_name ?? '');
                $classDisplay = $subclassName 
                    ? $student->class_name . ' - ' . $subclassName 
                    : $student->class_name;
                
                // Count subjects taken and get missed subjects
                $studentAttendance = $attendanceRecords[$student->studentID] ?? [];
                $subjectsTaken = 0;
                $missedSubjects = [];
                
                foreach ($filteredSubjects as $subject) {
                    $attendance = $studentAttendance[$subject->subjectID] ?? null;
                    if ($attendance && ($attendance->status ?? '') === 'Present') {
                        $subjectsTaken++;
                    } else {
                        $missedSubjects[] = [
                            'subjectID' => $subject->subjectID,
                            'subject_name' => $subject->subject_name,
                            'subject_code' => $subject->subject_code
                        ];
                    }
                }
                
                // Overall status: Present if taken at least one subject, Absent if none
                $overallStatus = $subjectsTaken > 0 ? 'Present' : 'Absent';
                
                $allStudents[] = [
                    'studentID' => $student->studentID,
                    'name' => $fullName,
                    'subclassID' => $student->subclassID,
                    'subclass_name' => $subclassName,
                    'class_name' => $student->class_name,
                    'class_display' => $classDisplay,
                    'status' => $overallStatus,
                    'subjects_taken' => $subjectsTaken,
                    'total_subjects' => $filteredSubjects->count(),
                    'missed_subjects' => $missedSubjects
                ];
            }

            // Group by subclass for statistics
            $attendanceData = [];
            $subclassGroups = collect($allStudents)->groupBy('subclassID');
            
            foreach ($subclassGroups as $subID => $subStudents) {
                $firstStudent = $subStudents->first();
                $present = $subStudents->where('status', 'Present')->count();
                $absent = $subStudents->where('status', '!=', 'Present')->count();
                $total = $subStudents->count();

                // Format class display: Main Class - Subclass (or just Main Class if subclass is empty)
                $subclassName = trim($firstStudent['subclass_name'] ?? '');
                $classDisplay = $subclassName 
                    ? $firstStudent['class_name'] . ' - ' . $subclassName 
                    : $firstStudent['class_name'];

                $attendanceData[] = [
                    'subclassID' => $subID,
                    'subclass_name' => $subclassName,
                    'class_name' => $firstStudent['class_name'],
                    'class_display' => $classDisplay,
                    'present' => $present,
                    'absent' => $absent,
                    'total' => $total
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'subclasses' => $attendanceData,
                    'students' => $allStudents
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting exam attendance data: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get subjects for class (Admin) - based on class_subjects table and exam timetable
    public function getSubjectsForClass(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $examID = $request->input('examID');
            $mainClassID = $request->input('mainClassID');
            $subclassID = $request->input('subclassID');

            if (!$examID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Exam ID and school ID required']);
            }

            // Get subjects based on class selection
            $subjects = collect();

            if ($subclassID) {
                // Get subjects for specific subclass from class_subjects
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.subclassID', $subclassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->pluck('subjectID')
                    ->toArray();

                // Get subjects from exam_timetables for this subclass
                $examTimetableSubjects = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->where('exam_timetables.subclassID', $subclassID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Get subjects from exam_timetable (school-wide)
                $schoolWideSubjects = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Merge and filter: only subjects that are in class_subjects AND in exam timetable
                $allExamSubjects = $examTimetableSubjects->merge($schoolWideSubjects)->unique('subjectID');
                $subjects = $allExamSubjects->filter(function($subject) use ($classSubjects) {
                    return in_array($subject->subjectID, $classSubjects);
                })->values();

            } elseif ($mainClassID) {
                // Get subjects for all subclasses in main class from class_subjects
                $classSubjects = DB::table('class_subjects')
                    ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                    ->join('classes', 'class_subjects.classID', '=', 'classes.classID')
                    ->where('class_subjects.classID', $mainClassID)
                    ->where('class_subjects.status', 'Active')
                    ->where('school_subjects.status', 'Active')
                    ->where('classes.schoolID', $schoolID)
                    ->select(
                        'school_subjects.subjectID',
                        'school_subjects.subject_name',
                        'school_subjects.subject_code'
                    )
                    ->distinct()
                    ->pluck('subjectID')
                    ->toArray();

                // Get all subclasses for this main class
                $subclassIDs = DB::table('subclasses')
                    ->where('classID', $mainClassID)
                    ->pluck('subclassID')
                    ->toArray();

                // Get subjects from exam_timetables for these subclasses
                $examTimetableSubjects = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->whereIn('exam_timetables.subclassID', $subclassIDs)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Get subjects from exam_timetable (school-wide)
                $schoolWideSubjects = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                // Merge and filter: only subjects that are in class_subjects AND in exam timetable
                $allExamSubjects = $examTimetableSubjects->merge($schoolWideSubjects)->unique('subjectID');
                $subjects = $allExamSubjects->filter(function($subject) use ($classSubjects) {
                    return in_array($subject->subjectID, $classSubjects);
                })->values();

            } else {
                // If no class selected, get all subjects from exam timetable
                $subjects1 = DB::table('exam_timetable')
                    ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetable.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                $subjects2 = DB::table('exam_timetables')
                    ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                    ->where('exam_timetables.examID', $examID)
                    ->where('school_subjects.status', 'Active')
                    ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                    ->distinct()
                    ->get();

                $subjects = $subjects1->merge($subjects2)->unique('subjectID')->values();
            }

            // Sort by subject name
            $subjects = $subjects->sortBy('subject_name')->values();

            return response()->json(['success' => true, 'subjects' => $subjects]);
        } catch (\Exception $e) {
            \Log::error('Error getting subjects for class: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get student missed subjects (Admin)
    public function getStudentMissedSubjects(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $studentID = $request->input('studentID');
            $examID = $request->input('examID');

            if (!$studentID || !$examID || !$schoolID) {
                return response()->json(['success' => false, 'error' => 'Student ID, Exam ID and school ID required']);
            }

            // Get all subjects for this exam
            $examSubjects = DB::table('exam_timetable')
                ->join('school_subjects', 'exam_timetable.subjectID', '=', 'school_subjects.subjectID')
                ->where('exam_timetable.examID', $examID)
                ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                ->get();

            $examSubjects2 = DB::table('exam_timetables')
                ->join('school_subjects', 'exam_timetables.subjectID', '=', 'school_subjects.subjectID')
                ->where('exam_timetables.examID', $examID)
                ->select('school_subjects.subjectID', 'school_subjects.subject_name', 'school_subjects.subject_code')
                ->get();

            $allExamSubjects = $examSubjects->merge($examSubjects2)->unique('subjectID')->values();

            // Get attendance records for this student
            $attendanceRecords = DB::table('exam_attendance')
                ->where('examID', $examID)
                ->where('studentID', $studentID)
                ->where('status', 'Present')
                ->pluck('subjectID')
                ->toArray();

            // Get missed subjects (subjects not in attendance records)
            $missedSubjects = $allExamSubjects->filter(function($subject) use ($attendanceRecords) {
                return !in_array($subject->subjectID, $attendanceRecords);
            })->values();

            return response()->json([
                'success' => true,
                'missed_subjects' => $missedSubjects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting student missed subjects: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getTeacherStaffWorkTime()
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $settings = TeacherStaffWorkTimeSetting::forSchool((int) $schoolID);

        return response()->json([
            'success' => true,
            'work_start_time' => substr((string) $settings->work_start_time, 0, 5),
            'work_end_time' => substr((string) $settings->work_end_time, 0, 5),
        ]);
    }

    public function saveTeacherStaffWorkTime(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i|after:work_start_time',
        ]);

        $settings = TeacherStaffWorkTimeSetting::forSchool((int) $schoolID);
        $settings->update([
            'work_start_time' => $request->input('work_start_time') . ':00',
            'work_end_time' => $request->input('work_end_time') . ':00',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Working time saved successfully.',
            'work_start_time' => substr((string) $settings->work_start_time, 0, 5),
            'work_end_time' => substr((string) $settings->work_end_time, 0, 5),
        ]);
    }

    public function teacherStaffAttendanceReport(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'person_type' => 'required|in:all,teacher,staff',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $range = $this->resolveTeacherStaffReportDateRange($request);
            $schoolID = (int) $schoolID;
            $filterType = $request->input('filter_type');
            $workingDayCounts = $this->resolveReportWorkingDayCounts(
                $workingDaysService,
                $schoolID,
                $filterType,
                $range
            );
            $workingDates = $workingDayCounts['working_dates'];
            $workTime = TeacherStaffWorkTimeSetting::forSchool($schoolID);
            $people = $this->getTeacherStaffReportPeople($schoolID, $request->input('person_type'));
            $attendanceMap = $this->loadTeacherStaffAttendanceMap(
                $schoolID,
                $request->input('person_type'),
                $range['start']->toDateString(),
                $range['end']->toDateString()
            );
            $permissionDatesMap = app(TeacherStaffPermissionAttendanceService::class)
                ->loadApprovedPermissionDatesMap(
                    $schoolID,
                    $range['start']->toDateString(),
                    $range['end']->toDateString(),
                    $request->input('person_type')
                );

            $rows = [];
            foreach ($people as $person) {
                $personKey = $person['person_type'] . '_' . $person['person_id'];
                $personAttendance = $attendanceMap[$personKey] ?? [];
                $permittedDates = $permissionDatesMap[$personKey] ?? [];
                $stats = $this->calculatePersonAttendanceStats(
                    $personAttendance,
                    $workingDates,
                    $workTime->work_start_time,
                    $permittedDates
                );

                $rows[] = [
                    'person_type' => $person['person_type'],
                    'person_id' => $person['person_id'],
                    'name' => $person['name'],
                    'role_label' => $person['role_label'],
                    'employee_number' => $person['employee_number'],
                    'phone_number' => $person['phone_number'],
                    'image_url' => $person['image_url'],
                    'present_days' => $stats['present_days'],
                    'absent_days' => $stats['absent_days'],
                    'permitted_days' => $stats['permitted_days'],
                    'total_working_days' => $workingDayCounts['display_working_days'],
                    'complete_working_days' => $workingDayCounts['elapsed_working_days'],
                    'elapsed_working_days' => $workingDayCounts['elapsed_working_days'],
                    'late_days' => $stats['late_days'],
                    'early_days' => $stats['early_days'],
                ];
            }

            usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

            return response()->json([
                'success' => true,
                'title' => $this->buildTeacherStaffReportTitle(
                    $request->input('person_type'),
                    $request->input('filter_type'),
                    $range
                ),
                'filter_type' => $request->input('filter_type'),
                'person_type' => $request->input('person_type'),
                'period_label' => $range['label'],
                'period_incomplete' => $range['incomplete'],
                'period_incomplete' => $range['incomplete'],
                'total_working_days' => $workingDayCounts['display_working_days'],
                'complete_working_days' => $workingDayCounts['elapsed_working_days'],
                'elapsed_working_days' => $workingDayCounts['elapsed_working_days'],
                'work_start_time' => substr((string) $workTime->work_start_time, 0, 5),
                'work_end_time' => substr((string) $workTime->work_end_time, 0, 5),
                'data' => $rows,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function teacherStaffAttendanceReportDetail(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'person_type' => 'required|in:teacher,staff',
            'person_id' => 'required|integer',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $schoolID = (int) $schoolID;
            $range = $this->resolveTeacherStaffReportDateRange($request);
            $filterType = $request->input('filter_type');
            $workingDayCounts = $this->resolveReportWorkingDayCounts(
                $workingDaysService,
                $schoolID,
                $filterType,
                $range
            );
            $workingDates = $workingDayCounts['working_dates'];
            $workTime = TeacherStaffWorkTimeSetting::forSchool($schoolID);
            $person = $this->getSingleTeacherStaffPerson(
                $schoolID,
                $request->input('person_type'),
                (int) $request->input('person_id')
            );

            if (!$person) {
                return response()->json(['success' => false, 'message' => 'Person not found.'], 404);
            }

            $personKey = $person['person_type'] . '_' . $person['person_id'];
            $attendanceMap = $this->loadTeacherStaffAttendanceMap(
                $schoolID,
                $person['person_type'],
                $range['start']->toDateString(),
                $range['end']->toDateString()
            );
            $personAttendance = $attendanceMap[$personKey] ?? [];
            $permissionDatesMap = app(TeacherStaffPermissionAttendanceService::class)
                ->loadApprovedPermissionDatesMap(
                    $schoolID,
                    $range['start']->toDateString(),
                    $range['end']->toDateString(),
                    $person['person_type']
                );
            $permittedDates = $permissionDatesMap[$personKey] ?? [];
            $stats = $this->calculatePersonAttendanceStats(
                $personAttendance,
                $workingDates,
                $workTime->work_start_time,
                $permittedDates
            );
            $daily = $this->buildPersonDailyAttendanceRecords(
                $personAttendance,
                $workingDates,
                $range['start'],
                $range['end'],
                $workTime->work_start_time,
                $permittedDates
            );

            return response()->json([
                'success' => true,
                'person' => $person,
                'title' => $this->buildTeacherStaffReportTitle(
                    $person['person_type'],
                    $request->input('filter_type'),
                    $range
                ),
                'statistics' => [
                    'present_days' => $stats['present_days'],
                    'absent_days' => $stats['absent_days'],
                    'permitted_days' => $stats['permitted_days'],
                    'total_working_days' => $workingDayCounts['display_working_days'],
                    'complete_working_days' => $workingDayCounts['elapsed_working_days'],
                    'elapsed_working_days' => $workingDayCounts['elapsed_working_days'],
                    'late_days' => $stats['late_days'],
                    'early_days' => $stats['early_days'],
                ],
                'work_start_time' => substr((string) $workTime->work_start_time, 0, 5),
                'work_end_time' => substr((string) $workTime->work_end_time, 0, 5),
                'period_incomplete' => $range['incomplete'],
                'period_label' => $range['label'],
                'daily_records' => $daily,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function resolveTeacherStaffReportDateRange(Request $request): array
    {
        $filterType = $request->input('filter_type');
        $today = Carbon::today();

        if ($filterType === 'day') {
            $date = $request->input('date');
            if (!$date) {
                throw new \InvalidArgumentException('Please select a date.');
            }
            $start = Carbon::parse($date)->startOfDay();
            $end = $start->copy();
            return [
                'start' => $start,
                'end' => $end,
                'incomplete' => false,
                'label' => $start->format('j F Y'),
                'date' => $date,
            ];
        }

        if ($filterType === 'date_range') {
            $from = $request->input('from_date');
            $to = $request->input('to_date');
            if (!$from || !$to) {
                throw new \InvalidArgumentException('Please select from date and to date.');
            }
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->startOfDay();
            if ($end->lt($start)) {
                throw new \InvalidArgumentException('To date must be on or after from date.');
            }
            return [
                'start' => $start,
                'end' => $end,
                'incomplete' => $end->gt($today),
                'label' => $start->format('j F Y') . ' — ' . $end->format('j F Y'),
                'from_date' => $from,
                'to_date' => $to,
            ];
        }

        if ($filterType === 'month') {
            $year = (int) ($request->input('year') ?: $today->year);
            $month = (int) $request->input('month');
            if (!$month) {
                throw new \InvalidArgumentException('Please select a month.');
            }
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
            $incomplete = $start->isSameMonth($today) && $start->isSameYear($today);
            if ($incomplete) {
                $end = $today->copy();
            }
            return [
                'start' => $start,
                'end' => $end,
                'incomplete' => $incomplete,
                'label' => $start->format('F Y'),
                'month' => $month,
                'year' => $year,
            ];
        }

        $year = (int) ($request->input('year') ?: $today->year);
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->startOfDay();
        $incomplete = $year === (int) $today->year;
        if ($incomplete) {
            $end = $today->copy();
        }

        return [
            'start' => $start,
            'end' => $end,
            'incomplete' => $incomplete,
            'label' => (string) $year,
            'year' => $year,
        ];
    }

    private function buildTeacherStaffReportTitle(string $personType, string $filterType, array $range): string
    {
        $scope = match ($personType) {
            'teacher' => 'TEACHERS',
            'staff' => 'STAFF',
            default => 'TEACHERS AND STAFF',
        };

        if ($filterType === 'day') {
            return $scope . ' ATTENDANCE REPORT ON ' . strtoupper($range['start']->format('j F Y'));
        }

        if ($filterType === 'date_range') {
            return $scope . ' ATTENDANCE ON ' . strtoupper($range['start']->format('j F Y'))
                . ' UP TO ' . strtoupper($range['end']->format('j F Y'));
        }

        if ($filterType === 'month') {
            return $scope . ' ATTENDANCE REPORT ON ' . strtoupper($range['start']->format('F Y'));
        }

        return $scope . ' ATTENDANCE REPORT FOR YEAR ' . $range['start']->year;
    }

    /**
     * display_working_days = calendar month/year total (matches /admin/calendar).
     * elapsed_working_days / complete_working_days = working days elapsed in period so far (for absent & in-progress column).
     *
     * @return array{working_dates: array<int, string>, display_working_days: int, elapsed_working_days: int}
     */
    private function resolveReportWorkingDayCounts(
        SchoolWorkingDaysService $workingDaysService,
        int $schoolID,
        string $filterType,
        array $range
    ): array {
        $workingDates = $workingDaysService->getWorkingDates($schoolID, $range['start'], $range['end']);
        $elapsedWorkingDays = count($workingDates);
        $displayWorkingDays = $elapsedWorkingDays;

        if ($filterType === 'month') {
            $displayWorkingDays = $workingDaysService->getMonthWorkingDaysCount(
                $schoolID,
                (int) $range['start']->year,
                (int) $range['start']->month
            );
        } elseif ($filterType === 'year') {
            $displayWorkingDays = $workingDaysService->getYearWorkingDaysCount(
                $schoolID,
                (int) $range['start']->year
            );
        } elseif ($filterType === 'day') {
            $displayWorkingDays = $elapsedWorkingDays > 0 ? 1 : 0;
        }

        return [
            'working_dates' => $workingDates,
            'display_working_days' => $displayWorkingDays,
            'elapsed_working_days' => $elapsedWorkingDays,
        ];
    }

    private function getTeacherStaffReportPeople(int $schoolID, string $personType): array
    {
        $people = [];

        if ($personType !== 'staff') {
            $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'Active')->get();
            foreach ($teachers as $teacher) {
                $people[] = $this->formatTeacherStaffPersonRow($teacher, 'teacher');
            }
        }

        if ($personType !== 'teacher') {
            $staffMembers = OtherStaff::where('schoolID', $schoolID)->where('status', 'Active')->get();
            foreach ($staffMembers as $staff) {
                $people[] = $this->formatTeacherStaffPersonRow($staff, 'staff');
            }
        }

        return $people;
    }

    private function getSingleTeacherStaffPerson(int $schoolID, string $personType, int $personId): ?array
    {
        if ($personType === 'teacher') {
            $teacher = Teacher::where('schoolID', $schoolID)->where('id', $personId)->where('status', 'Active')->first();
            return $teacher ? $this->formatTeacherStaffPersonRow($teacher, 'teacher') : null;
        }

        $staff = OtherStaff::where('schoolID', $schoolID)->where('id', $personId)->where('status', 'Active')->first();
        return $staff ? $this->formatTeacherStaffPersonRow($staff, 'staff') : null;
    }

    private function formatTeacherStaffPersonRow($person, string $personType): array
    {
        $name = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));
        if ($person->image) {
            $imageUrl = asset('userImages/' . $person->image);
        } else {
            $imageUrl = ($person->gender ?? '') === 'Female'
                ? asset('images/female.png')
                : asset('images/male.png');
        }

        return [
            'person_type' => $personType,
            'person_id' => (int) $person->id,
            'name' => $name ?: 'N/A',
            'role_label' => $person->position ?? ($personType === 'teacher' ? 'Teacher' : 'Staff'),
            'employee_number' => $person->employee_number ?? null,
            'phone_number' => $person->phone_number ?? null,
            'image_file' => $person->image ?? null,
            'image_url' => $imageUrl,
        ];
    }

    /**
     * @return array<string, array<string, array{check_in_time: ?string, check_out_time: ?string}>>
     */
    private function loadTeacherStaffAttendanceMap(int $schoolID, string $personType, string $fromDate, string $toDate): array
    {
        $map = [];

        if ($personType !== 'staff') {
            $teacherIds = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->pluck('id')
                ->all();

            if (! empty($teacherIds)) {
                DB::table('teacher_fingerprint_attendance')
                    ->select(['teacherID', 'attendance_date', 'check_in_time', 'check_out_time'])
                    ->whereIn('teacherID', $teacherIds)
                    ->where('attendance_date', '>=', $fromDate)
                    ->where('attendance_date', '<=', $toDate)
                    ->orderBy('id')
                    ->chunk(2000, function ($rows) use (&$map) {
                        foreach ($rows as $row) {
                            $key = 'teacher_' . $row->teacherID;
                            $date = substr((string) $row->attendance_date, 0, 10);
                            $map[$key][$date] = [
                                'check_in_time' => $this->normalizeAttendanceDateTime($row->check_in_time),
                                'check_out_time' => $this->normalizeAttendanceDateTime($row->check_out_time),
                            ];
                        }
                    });
            }
        }

        if ($personType !== 'teacher') {
            $staffIds = OtherStaff::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->pluck('id')
                ->all();

            if (! empty($staffIds)) {
                DB::table('staff_fingerprint_attendance')
                    ->select(['staffID', 'attendance_date', 'check_in_time', 'check_out_time'])
                    ->whereIn('staffID', $staffIds)
                    ->where('attendance_date', '>=', $fromDate)
                    ->where('attendance_date', '<=', $toDate)
                    ->orderBy('id')
                    ->chunk(2000, function ($rows) use (&$map) {
                        foreach ($rows as $row) {
                            $key = 'staff_' . $row->staffID;
                            $date = substr((string) $row->attendance_date, 0, 10);
                            $map[$key][$date] = [
                                'check_in_time' => $this->normalizeAttendanceDateTime($row->check_in_time),
                                'check_out_time' => $this->normalizeAttendanceDateTime($row->check_out_time),
                            ];
                        }
                    });
            }
        }

        return $map;
    }

    private function normalizeAttendanceDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = (string) $value;

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value)) {
            return substr($value, 0, 19);
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveStudentFingerprintReportScope(Request $request, int $schoolID): array
    {
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $classID = $request->input('classID') ? (int) $request->input('classID') : null;
        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;

        if ($userType === 'Teacher') {
            if ($subclassID) {
                if (! $this->teacherCanAccessSubclass($schoolID, $subclassID, $teacherID)) {
                    throw new \InvalidArgumentException('Unauthorized access to this class.');
                }

                return [null, $subclassID];
            }

            if ($classID) {
                if (! $this->teacherCanAccessMainClass($schoolID, $classID, $teacherID)) {
                    throw new \InvalidArgumentException('Unauthorized access to this class.');
                }

                return [$classID, null];
            }

            throw new \InvalidArgumentException('Class or subclass is required.');
        }

        return [$classID, $subclassID];
    }

    private function fingerprintSetupViewData(): array
    {
        $service = app(FingerprintAttendanceSetupService::class);

        return [
            'fingerprintSetup' => $service->currentSetup(),
        ];
    }

    private function teacherCanAccessMainClass(int $schoolID, int $classID, $teacherID): bool
    {
        if (! $teacherID) {
            return false;
        }

        return ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->exists();
    }

    private function teacherCanAccessSubclass(int $schoolID, int $subclassID, $teacherID): bool
    {
        $subclass = Subclass::with('class')->find($subclassID);
        if (! $subclass || ! $subclass->class || (int) $subclass->class->schoolID !== $schoolID) {
            return false;
        }

        if (! $teacherID) {
            return false;
        }

        if ((int) $subclass->teacherID === (int) $teacherID) {
            return true;
        }

        return ClassModel::where('classID', $subclass->classID)
            ->where('teacherID', $teacherID)
            ->where('schoolID', $schoolID)
            ->exists();
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, array<string, mixed>> $students
     * @param array<string, array<string, array{check_in_time: ?string, check_out_time: ?string}>> $attendanceMap
     * @return array{late: int, early_arrival: int, early_departure: int, no_checkout: int}
     */
    private function buildStudentFingerprintAttendanceOverview(
        string $filterType,
        array $rows,
        array $students,
        array $attendanceMap,
        array $range,
        string $workStartTime,
        string $workEndTime
    ): array {
        if ($filterType === 'day') {
            return $this->buildStudentDayFingerprintOverview(
                $students,
                $attendanceMap,
                $range['start']->toDateString(),
                $workStartTime,
                $workEndTime
            );
        }

        return [
            'late' => (int) array_sum(array_column($rows, 'late_days')),
            'early_arrival' => (int) array_sum(array_column($rows, 'early_arrival_days')),
            'early_departure' => (int) array_sum(array_column($rows, 'early_departure_days')),
            'no_checkout' => (int) array_sum(array_column($rows, 'no_checkout_days')),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $students
     * @param array<string, array<string, array{check_in_time: ?string, check_out_time: ?string}>> $attendanceMap
     * @return array{late: int, early_arrival: int, early_departure: int, no_checkout: int}
     */
    private function buildStudentDayFingerprintOverview(
        array $students,
        array $attendanceMap,
        string $reportDate,
        string $workStartTime,
        string $workEndTime
    ): array {
        $overview = [
            'late' => 0,
            'early_arrival' => 0,
            'early_departure' => 0,
            'no_checkout' => 0,
        ];
        $now = Carbon::now();

        foreach ($students as $student) {
            $studentKey = 'student_'.$student['student_id'];
            $record = $attendanceMap[$studentKey][$reportDate] ?? null;
            if (! $record || empty($record['check_in_time'])) {
                continue;
            }

            $punctuality = $this->classifyCheckInPunctuality($record['check_in_time'], $reportDate, $workStartTime);
            if ($punctuality === 'late') {
                $overview['late']++;
            } elseif ($punctuality === 'early') {
                $overview['early_arrival']++;
            }

            if (! empty($record['check_out_time'])) {
                if ($this->isEarlyDeparture($record['check_out_time'], $reportDate, $workEndTime)) {
                    $overview['early_departure']++;
                }
            } elseif ($this->isDayCompletedForCheckout($reportDate, $workEndTime, $now)) {
                $overview['no_checkout']++;
            }
        }

        return $overview;
    }

    /**
     * @param array<string, array{check_in_time: ?string, check_out_time: ?string}> $personAttendance
     * @param array<int, string> $workingDates
     * @return array{
     *     present_days: int,
     *     absent_days: int,
     *     late_days: int,
     *     early_arrival_days: int,
     *     early_departure_days: int,
     *     no_checkout_days: int
     * }
     */
    private function calculateStudentFingerprintAttendanceStats(
        array $personAttendance,
        array $workingDates,
        string $workStartTime,
        string $workEndTime,
        array $permissionStatusByDate = []
    ): array {
        $workingDateSet = array_flip($workingDates);
        $now = Carbon::now();
        $presentDays = 0;
        $lateDays = 0;
        $earlyArrivalDays = 0;
        $earlyDepartureDays = 0;
        $noCheckoutDays = 0;
        $permissionDays = 0;
        $sickDays = 0;

        foreach ($personAttendance as $date => $record) {
            if (isset($permissionStatusByDate[$date])) {
                continue;
            }

            if (empty($record['check_in_time'])) {
                continue;
            }

            $presentDays++;

            if (! isset($workingDateSet[$date])) {
                continue;
            }

            $punctuality = $this->classifyCheckInPunctuality($record['check_in_time'], $date, $workStartTime);
            if ($punctuality === 'late') {
                $lateDays++;
            } elseif ($punctuality === 'early') {
                $earlyArrivalDays++;
            }

            if (! empty($record['check_out_time'])) {
                if ($this->isEarlyDeparture($record['check_out_time'], $date, $workEndTime)) {
                    $earlyDepartureDays++;
                }
            } elseif ($this->isDayCompletedForCheckout($date, $workEndTime, $now)) {
                $noCheckoutDays++;
            }
        }

        $absentDays = 0;
        foreach ($workingDates as $workDate) {
            $permStatus = $permissionStatusByDate[$workDate] ?? null;
            if ($permStatus === 'Excused') {
                $permissionDays++;
                continue;
            }
            if ($permStatus === 'Sick') {
                $sickDays++;
                continue;
            }

            $record = $personAttendance[$workDate] ?? null;
            if (! $record || empty($record['check_in_time'])) {
                $absentDays++;
            }
        }

        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'permission_days' => $permissionDays,
            'sick_days' => $sickDays,
            'late_days' => $lateDays,
            'early_arrival_days' => $earlyArrivalDays,
            'early_departure_days' => $earlyDepartureDays,
            'no_checkout_days' => $noCheckoutDays,
        ];
    }

    private function isEarlyDeparture(string $checkOutDateTime, string $workDate, string $workEndTime): bool
    {
        $checkOut = Carbon::parse($checkOutDateTime);
        $officialEnd = Carbon::parse($workDate.' '.substr((string) $workEndTime, 0, 8));

        return $checkOut->lt($officialEnd);
    }

    private function isDayCompletedForCheckout(string $date, string $workEndTime, Carbon $now): bool
    {
        if ($date < $now->toDateString()) {
            return true;
        }

        if ($date === $now->toDateString()) {
            $officialEnd = Carbon::parse($date.' '.substr((string) $workEndTime, 0, 8));

            return $now->gt($officialEnd);
        }

        return false;
    }

    /**
     * Present = any fingerprint day in period with check-in in DB (matches Saved Records).
     * Absent = elapsed working days with no fingerprint record in DB (no scan = absent).
     * Late/early = only on working days with official hours.
     *
     * @param array<string, array{check_in_time: ?string, check_out_time: ?string}> $personAttendance
     * @param array<int, string> $workingDates elapsed working days in the report period
     */
    private function calculatePersonAttendanceStats(
        array $personAttendance,
        array $workingDates,
        string $workStartTime,
        array $permittedDates = []
    ): array {
        $workingDateSet = array_flip($workingDates);
        $permittedDateSet = array_flip($permittedDates);
        $presentDays = 0;
        $lateDays = 0;
        $earlyDays = 0;
        $permittedDays = 0;

        foreach ($personAttendance as $date => $record) {
            if (empty($record['check_in_time'])) {
                continue;
            }

            if (isset($permittedDateSet[$date])) {
                continue;
            }

            $presentDays++;

            if (!isset($workingDateSet[$date])) {
                continue;
            }

            $punctuality = $this->classifyCheckInPunctuality($record['check_in_time'], $date, $workStartTime);
            if ($punctuality === 'late') {
                $lateDays++;
            } elseif ($punctuality === 'early') {
                $earlyDays++;
            }
        }

        $absentDays = 0;
        foreach ($workingDates as $workDate) {
            if (isset($permittedDateSet[$workDate])) {
                $permittedDays++;
                continue;
            }

            $record = $personAttendance[$workDate] ?? null;
            if (!$record || empty($record['check_in_time'])) {
                $absentDays++;
            }
        }

        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'permitted_days' => $permittedDays,
            'late_days' => $lateDays,
            'early_days' => $earlyDays,
        ];
    }

    /**
     * @param array<string, array{check_in_time: ?string, check_out_time: ?string}> $personAttendance
     * @param array<int, string> $workingDates
     */
    private function buildPersonDailyAttendanceRecords(
        array $personAttendance,
        array $workingDates,
        Carbon $start,
        Carbon $end,
        string $workStartTime,
        array $permittedDates = []
    ): array {
        $workingDateSet = array_flip($workingDates);
        $permittedDateSet = array_flip($permittedDates);
        $daily = [];
        $current = $start->copy()->startOfDay();

        while ($current->lte($end)) {
            $date = $current->format('Y-m-d');
            $isWorkingDay = isset($workingDateSet[$date]);
            $isPermitted = $isWorkingDay && isset($permittedDateSet[$date]);
            $record = $personAttendance[$date] ?? null;
            $checkIn = $record['check_in_time'] ?? null;
            $checkOut = $record['check_out_time'] ?? null;

            if (!$isWorkingDay && !$checkIn && !$isPermitted) {
                $current->addDay();
                continue;
            }

            $status = 'Absent';
            $punctuality = null;

            if ($isPermitted) {
                $status = 'Permitted';
            } elseif ($checkIn) {
                $punctuality = $this->classifyCheckInPunctuality($checkIn, $date, $workStartTime);
                if (!$isWorkingDay) {
                    $status = 'Present (Off Day)';
                } elseif ($punctuality === 'late') {
                    $status = 'Late';
                } elseif ($punctuality === 'early') {
                    $status = 'Early';
                } else {
                    $status = 'Present';
                }
            }

            $daily[] = [
                'date' => $date,
                'date_label' => $current->format('l, j M Y'),
                'check_in_time' => $isPermitted ? null : ($checkIn ? Carbon::parse($checkIn)->format('H:i:s') : null),
                'check_out_time' => $isPermitted ? null : ($checkOut ? Carbon::parse($checkOut)->format('H:i:s') : null),
                'status' => $status,
                'punctuality' => $punctuality,
                'is_late' => $punctuality === 'late',
                'is_working_day' => $isWorkingDay,
                'is_permitted' => $isPermitted,
            ];

            $current->addDay();
        }

        return $daily;
    }

    private function classifyCheckInPunctuality(string $checkInDateTime, string $workDate, string $workStartTime): ?string
    {
        $checkIn = Carbon::parse($checkInDateTime);
        $officialStart = Carbon::parse($workDate . ' ' . substr((string) $workStartTime, 0, 8));

        if ($checkIn->gt($officialStart)) {
            return 'late';
        }
        if ($checkIn->lt($officialStart)) {
            return 'early';
        }

        return 'on_time';
    }

    public function exportTeacherStaffAttendanceReportPdf(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'person_type' => 'required|in:all,teacher,staff',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $payload = $this->collectTeacherStaffReportPayload($request, $workingDaysService);
            $school = School::find((int) $schoolID);

            $data = [
                'school' => $school,
                'title' => $payload['title'],
                'period_label' => $payload['range']['label'],
                'period_incomplete' => $payload['range']['incomplete'],
                'total_working_days' => $payload['workingDayCounts']['display_working_days'],
                'complete_working_days' => $payload['workingDayCounts']['elapsed_working_days'],
                'elapsed_working_days' => $payload['workingDayCounts']['elapsed_working_days'],
                'work_start_time' => substr((string) $payload['workTime']->work_start_time, 0, 5),
                'work_end_time' => substr((string) $payload['workTime']->work_end_time, 0, 5),
                'rows' => $payload['rows'],
                'generated_at' => now()->format('d M Y H:i'),
            ];

            $pdf = PDF::loadView('Admin.pdf.teacher_staff_attendance_report_pdf', $data);
            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
            ]);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'Teacher_Staff_Attendance_Report_' . date('YmdHis') . '.pdf';

            return $pdf->download($filename);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function exportTeacherStaffAttendancePersonPdf(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'person_type' => 'required|in:teacher,staff',
            'person_id' => 'required|integer',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $payload = $this->collectTeacherStaffReportPayload(
                $request,
                $workingDaysService,
                (int) $request->input('person_id'),
                $request->input('person_type')
            );

            if (empty($payload['detailed'])) {
                return response()->json(['success' => false, 'message' => 'Person not found.'], 404);
            }

            $detail = $payload['detailed'][0];
            $school = School::find((int) $schoolID);

            $data = [
                'school' => $school,
                'title' => $payload['title'],
                'person' => $detail['person'],
                'statistics' => $detail['statistics'],
                'daily_records' => $detail['daily_records'],
                'period_label' => $payload['range']['label'],
                'period_incomplete' => $payload['range']['incomplete'],
                'work_start_time' => substr((string) $payload['workTime']->work_start_time, 0, 5),
                'work_end_time' => substr((string) $payload['workTime']->work_end_time, 0, 5),
                'generated_at' => now()->format('d M Y H:i'),
            ];

            $pdf = PDF::loadView('Admin.pdf.teacher_staff_attendance_person_pdf', $data);
            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
            ]);
            $pdf->setPaper('A4', 'portrait');

            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $detail['person']['name'] ?? 'Person');
            $filename = 'Attendance_' . $safeName . '_' . date('YmdHis') . '.pdf';

            return $pdf->download($filename);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendTeacherStaffAttendanceSms(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'filter_type' => 'required|in:day,date_range,month,year',
            'person_type' => 'required|in:teacher,staff',
            'person_id' => 'required|integer',
            'message' => 'nullable|string|max:1000',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $payload = $this->collectTeacherStaffReportPayload(
                $request,
                $workingDaysService,
                (int) $request->input('person_id'),
                $request->input('person_type')
            );

            if (empty($payload['detailed'])) {
                return response()->json(['success' => false, 'message' => 'Person not found.'], 404);
            }

            $detail = $payload['detailed'][0];
            $person = $detail['person'];
            $stats = $detail['statistics'];
            $phone = $person['phone_number'] ?? null;

            if (!$phone) {
                return response()->json(['success' => false, 'message' => 'No phone number on file for this person.'], 422);
            }

            $school = School::find((int) $schoolID);
            $schoolName = $school->school_name ?? 'School';

            $message = trim((string) $request->input('message', ''));
            if ($message === '') {
                $message = sprintf(
                    'Habari %s, ripoti ya mahudhurio (%s): Present %d, Absent %d, Late %d, Early %d. Siku za kazi: %d. Muda rasmi: %s - %s. - %s',
                    $person['name'],
                    $payload['range']['label'],
                    $stats['present_days'],
                    $stats['absent_days'],
                    $stats['late_days'],
                    $stats['early_days'],
                    $stats['total_working_days'],
                    substr((string) $payload['workTime']->work_start_time, 0, 5),
                    substr((string) $payload['workTime']->work_end_time, 0, 5),
                    $schoolName
                );
            }

            $smsService = new SmsService();
            $result = $smsService->sendSms($phone, $message);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent to ' . $person['name'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gateway error: ' . ($result['message'] ?? 'Failed to send SMS'),
            ], 500);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array{
     *     title: string,
     *     range: array,
     *     workingDayCounts: array,
     *     workTime: TeacherStaffWorkTimeSetting,
     *     rows: array<int, array>,
     *     detailed: array<int, array{person: array, statistics: array, daily_records: array}>
     * }
     */
    private function collectTeacherStaffReportPayload(
        Request $request,
        SchoolWorkingDaysService $workingDaysService,
        ?int $onlyPersonId = null,
        ?string $onlyPersonType = null
    ): array {
        $schoolID = (int) Session::get('schoolID');
        $range = $this->resolveTeacherStaffReportDateRange($request);
        $filterType = $request->input('filter_type');
        $listPersonType = $onlyPersonType ? ($onlyPersonType === 'teacher' ? 'teacher' : 'staff') : $request->input('person_type');

        $workingDayCounts = $this->resolveReportWorkingDayCounts(
            $workingDaysService,
            $schoolID,
            $filterType,
            $range
        );
        $workingDates = $workingDayCounts['working_dates'];
        $workTime = TeacherStaffWorkTimeSetting::forSchool($schoolID);
        $people = $this->getTeacherStaffReportPeople($schoolID, $listPersonType);

        if ($onlyPersonId && $onlyPersonType) {
            $people = array_values(array_filter(
                $people,
                fn ($p) => $p['person_id'] === $onlyPersonId && $p['person_type'] === $onlyPersonType
            ));
        }

        $attendanceMap = $this->loadTeacherStaffAttendanceMap(
            $schoolID,
            $listPersonType,
            $range['start']->toDateString(),
            $range['end']->toDateString()
        );
        $permissionDatesMap = app(TeacherStaffPermissionAttendanceService::class)
            ->loadApprovedPermissionDatesMap(
                $schoolID,
                $range['start']->toDateString(),
                $range['end']->toDateString(),
                $listPersonType
            );

        $rows = [];
        $detailed = [];

        foreach ($people as $person) {
            $personKey = $person['person_type'] . '_' . $person['person_id'];
            $personAttendance = $attendanceMap[$personKey] ?? [];
            $permittedDates = $permissionDatesMap[$personKey] ?? [];
            $stats = $this->calculatePersonAttendanceStats(
                $personAttendance,
                $workingDates,
                $workTime->work_start_time,
                $permittedDates
            );
            $daily = $this->buildPersonDailyAttendanceRecords(
                $personAttendance,
                $workingDates,
                $range['start'],
                $range['end'],
                $workTime->work_start_time,
                $permittedDates
            );

            $rowStats = [
                'present_days' => $stats['present_days'],
                'absent_days' => $stats['absent_days'],
                'permitted_days' => $stats['permitted_days'],
                'total_working_days' => $workingDayCounts['display_working_days'],
                'complete_working_days' => $workingDayCounts['elapsed_working_days'],
                'elapsed_working_days' => $workingDayCounts['elapsed_working_days'],
                'late_days' => $stats['late_days'],
                'early_days' => $stats['early_days'],
            ];

            $rows[] = array_merge($person, $rowStats);
            $detailed[] = [
                'person' => $person,
                'statistics' => $rowStats,
                'daily_records' => $daily,
            ];
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        usort($detailed, fn ($a, $b) => strcasecmp($a['person']['name'], $b['person']['name']));

        $titlePersonType = $onlyPersonType ?? $request->input('person_type');

        return [
            'title' => $this->buildTeacherStaffReportTitle($titlePersonType, $filterType, $range),
            'range' => $range,
            'workingDayCounts' => $workingDayCounts,
            'workTime' => $workTime,
            'rows' => $rows,
            'detailed' => $detailed,
        ];
    }

    public function exportAttendanceReportPdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $searchType = $request->input('search_type');
        if (!in_array($searchType, ['date', 'month', 'year'], true)) {
            return redirect()->back()->with('error', 'Invalid search type for PDF export.');
        }

        $date = $request->input('date');
        $year = $request->input('year');
        $month = $request->input('month');
        $subclassID = $request->input('subclassID');
        $classID = $request->input('classID');
        $isCoordinatorView = $request->input('coordinator') === 'true';

        $attendanceByClass = $this->getAttendanceByClass(
            $searchType,
            $date,
            $year,
            $month,
            null,
            $subclassID,
            $schoolID,
            $classID,
            $isCoordinatorView
        );

        $school = School::find($schoolID);
        $periodLabel = $this->buildStudentAttendancePeriodLabel($searchType, $date, $year, $month);
        $scopeLabel = 'All Classes';

        if ($subclassID) {
            $sub = Subclass::with('class')->find($subclassID);
            if ($sub && $sub->class) {
                $sn = trim($sub->subclass_name ?? '');
                $scopeLabel = $sn ? $sub->class->class_name . ' ' . $sn : $sub->class->class_name;
            }
        } elseif ($isCoordinatorView && $classID) {
            try {
                $decryptedClassID = Crypt::decrypt($classID);
            } catch (\Exception $e) {
                $decryptedClassID = $classID;
            }
            $mainClass = ClassModel::find($decryptedClassID);
            if ($mainClass) {
                $scopeLabel = $mainClass->class_name . ' (All Subclasses)';
            }
        }

        $normalized = collect($attendanceByClass)->map(function ($item) {
            $row = is_array($item) ? $item : (array) $item;
            $row['students'] = collect($row['students'] ?? [])->map(fn ($s) => (array) $s)->values()->all();

            return $row;
        })->values()->all();

        $data = [
            'school' => $school,
            'title' => 'Student Attendance Report - ' . $periodLabel,
            'period_label' => $periodLabel,
            'scope_label' => $scopeLabel,
            'search_type' => $searchType,
            'attendanceByClass' => $normalized,
            'generated_at' => now()->format('d M Y H:i'),
        ];

        $pdf = PDF::loadView('Admin.pdf.student_attendance_report_pdf', $data);
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Student_Attendance_Report_' . date('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportStudentAttendancePdf($studentID, Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $student = Student::where('studentID', $studentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $date = $request->input('date');
        $searchType = $request->input('search_type', 'month');

        $query = Attendance::where('studentID', $studentID)
            ->where('schoolID', $schoolID);

        if ($searchType === 'year' && $year) {
            $query->whereYear('attendance_date', $year);
        } elseif ($searchType === 'month' && $year && $month) {
            $query->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month);
        } elseif ($searchType === 'date' && $date) {
            $query->whereDate('attendance_date', $date);
        } else {
            $query->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month);
            $searchType = 'month';
        }

        $attendances = $query->orderBy('attendance_date', 'asc')->get();

        $stats = [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'Present')->count(),
            'absent' => $attendances->where('status', 'Absent')->count(),
            'sick' => $attendances->where('status', 'Sick')->count(),
            'excused' => $attendances->where('status', 'Excused')->count(),
        ];

        $school = School::find($schoolID);
        $periodLabel = $this->buildStudentAttendancePeriodLabel($searchType, $date, $year, $month);
        $studentName = trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name);
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $studentName);

        $data = [
            'school' => $school,
            'student' => $student,
            'attendances' => $attendances,
            'stats' => $stats,
            'period_label' => $periodLabel,
            'title' => 'Attendance - ' . $studentName,
            'generated_at' => now()->format('d M Y H:i'),
        ];

        $pdf = PDF::loadView('Admin.pdf.student_attendance_individual_pdf', $data);
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Attendance_' . $safeName . '_' . date('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    private function buildStudentAttendancePeriodLabel(
        string $searchType,
        ?string $date = null,
        ?string $year = null,
        ?string $month = null
    ): string {
        if ($searchType === 'date' && $date) {
            return Carbon::parse($date)->format('d M Y');
        }

        if ($searchType === 'month' && $year && $month) {
            return Carbon::create((int) $year, (int) $month, 1)->format('F Y');
        }

        if ($searchType === 'year' && $year) {
            return 'Year ' . $year;
        }

        return 'Selected Period';
    }
}
