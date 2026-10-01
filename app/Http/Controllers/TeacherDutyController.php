<?php

namespace App\Http\Controllers;

use App\Models\TeacherDuty;
use App\Models\Teacher; // Assuming Teacher model exists or is 'App\Models\Teachers'
use App\Models\Term;
use App\Models\AcademicYear;
use App\Services\SelectedAcademicYearService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TeacherDutyController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new \App\Services\SmsService();
    }

    public function index(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login');
        }

        // --- FILTERING LOGIC ---
        // Default to current month if no dates provided
        $fromDate = $request->get('from_date') ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->get('to_date') ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        // Human readable month title
        $monthTitle = Carbon::parse($fromDate)->format('F Y');
        if ($fromDate && $toDate && Carbon::parse($fromDate)->format('M Y') != Carbon::parse($toDate)->format('M Y')) {
            $monthTitle = Carbon::parse($fromDate)->format('M Y') . ' - ' . Carbon::parse($toDate)->format('M Y');
        }

        // Fetch current active term and year (for assignments)
        $activeTerm = Term::where('schoolID', $schoolID)->where('status', 'Active')->first();
        $activeYear = AcademicYear::where('schoolID', $schoolID)->where('status', 'Active')->first();

        // Fetch duties for this school within date range
        $duties = TeacherDuty::where('schoolID', $schoolID)
            ->with(['teacher', 'term'])
            ->where(function($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                  ->orWhereBetween('end_date', [$fromDate, $toDate])
                  ->orWhere(function($sub) use ($fromDate, $toDate) {
                      $sub->where('start_date', '<=', $fromDate)
                          ->where('end_date', '>=', $toDate);
                  });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Fetch all teachers for the dropdown
        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        // Fetch reports for status indicators
        $reports = \App\Models\DailyDutyReport::where('schoolID', $schoolID)
            ->whereBetween('report_date', [$fromDate, $toDate])
            ->get()
            ->groupBy(function($item) {
                return $item->report_date->format('Y-m-d');
            });

        // Get the end date of the last duty assigned to help with continuity
        $lastDuty = TeacherDuty::where('schoolID', $schoolID)->orderBy('end_date', 'desc')->first();
        $lastDutyEndDate = $lastDuty ? $lastDuty->end_date : null;

        // If AJAX, we only want the table part and the title
        if ($request->ajax()) {
            return response()->json([
                'html' => view('Admin.teacher_duties.table_body', compact('duties', 'reports'))->render(),
                'title' => 'Teacher on Duties in ' . $monthTitle
            ]);
        }

        // Fetch classes for the duty report modal
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name', 'asc')->get();

        return view('Admin.teacher_duties.index', compact('duties', 'teachers', 'activeTerm', 'activeYear', 'lastDutyEndDate', 'fromDate', 'toDate', 'monthTitle', 'reports', 'classes'));
    }

    public function export_pdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) return redirect()->back();

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $teacherID = Session::get('teacherID');
    $isTeacherRoute = str_contains($request->route()->getName(), 'teacher.duty_book');

    $query = TeacherDuty::where('schoolID', $schoolID)
        ->with(['teacher', 'term'])
        ->whereBetween('start_date', [$fromDate, $toDate])
        ->orderBy('start_date', 'asc');

    // If teacher is exporting, only show their assignments
    if ($isTeacherRoute && $teacherID) {
        $query->where('teacherID', $teacherID);
    }

    $duties = $query->get();

        $school = \App\Models\School::find($schoolID);
        $monthTitle = Carbon::parse($fromDate)->format('F Y');

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('Admin.teacher_duties.pdf_roster', compact('duties', 'school', 'fromDate', 'toDate', 'monthTitle'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('Teacher_Duty_Roster_' . $monthTitle . '.pdf');
        } catch (\Exception $e) {
            Log::error("Roster PDF Generation failed for period $fromDate to $toDate: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function teacherIndex(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return redirect()->route('login');
        }

        $fromDate = $request->get('from_date') ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->get('to_date') ?: Carbon::now()->endOfMonth()->format('Y-m-d');
        $monthTitle = Carbon::parse($fromDate)->format('F Y');

        // Only show duties assigned to THIS teacher
        $duties = TeacherDuty::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->with(['teacher', 'term'])
            ->where(function($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                  ->orWhereBetween('end_date', [$fromDate, $toDate]);
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Check if current teacher is on duty
        $today = Carbon::today();
        $currentDuty = TeacherDuty::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        // Fetch classes for the attendance form
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name', 'asc')->get();

        // Fetch reports for status indicators
        $reports = \App\Models\DailyDutyReport::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->whereBetween('report_date', [$fromDate, $toDate])
            ->get()
            ->keyBy(function($item) {
                return $item->report_date->format('Y-m-d');
            });

        // Fetch school details for dynamic labels
        $school_details = \App\Models\School::find($schoolID);

        return view('Teacher.duty_book', compact('duties', 'monthTitle', 'fromDate', 'toDate', 'currentDuty', 'classes', 'reports', 'school_details'));
    }

    public function getDailyReport(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $date = $request->get('date');
        $reportID = $request->get('reportID');

        if (!$reportID && !$date) {
            return response()->json(['success' => false, 'message' => 'Date or Report ID is required.'], 400);
        }

        // Fetch saved report
        $query = \App\Models\DailyDutyReport::where('schoolID', $schoolID);

        if ($reportID) {
            $query->where('reportID', $reportID);
        } else {
            $query->whereDate('report_date', $date);
            // If it's a teacher request, optionally narrow by their ID
            if ($teacherID) {
                $query->where('teacherID', $teacherID);
            }
        }

        $report = $query->first();

        $permissionService = app(\App\Services\StudentPermissionAttendanceService::class);
        $enrollmentService = app(StudentEnrollmentService::class);
        $yearService = app(SelectedAcademicYearService::class);
        $permissionStatusMap = $permissionService->loadApprovedStatusMap($schoolID, $date, $date);
        $useFingerprint = $permissionService->schoolHasFingerprintAttendanceOnDate($schoolID, $date);

        // Duty book always uses Active enrollments for the header academic year (not editable).
        $enrollmentStatus = 'Active';

        // System / fingerprint attendance calculation (pre-fill data)
        $classes = \App\Models\ClassModel::where('schoolID', $schoolID)->orderBy('class_name', 'asc')->get();
        $systemAttendance = [];

        foreach ($classes as $class) {
            $subclassIDs = \App\Models\Subclass::where('classID', $class->classID)->pluck('subclassID')->toArray();
            $enrolledStudentIDs = $enrollmentService->studentIDsInClass(
                (int) $class->classID,
                (int) $schoolID,
                $enrollmentStatus
            );

            $genderCounts = $this->countStudentsByGender($schoolID, $enrolledStudentIDs);
            $registeredBoys = $genderCounts['b'];
            $registeredGirls = $genderCounts['g'];

            if ($useFingerprint) {
                $genderStats = $permissionService->aggregateSubclassStatsFromFingerprint(
                    $schoolID,
                    $subclassIDs,
                    $date,
                    $permissionStatusMap,
                    $enrolledStudentIDs
                );
            } else {
                $genderStats = $permissionService->aggregateSubclassStatsFromSystem(
                    $schoolID,
                    $subclassIDs,
                    $date,
                    $permissionStatusMap,
                    $enrolledStudentIDs
                );
            }

            // New comers: Active-enrolled students admitted on this date
            $newComers = ['b' => 0, 'g' => 0];
            if ($enrolledStudentIDs !== []) {
                $newComers = $this->countStudentsByGender(
                    $schoolID,
                    $enrolledStudentIDs,
                    $date
                );
            }

            $systemAttendance[$class->classID] = [
                'reg_b' => (int) $registeredBoys,
                'reg_g' => (int) $registeredGirls,
                'pres_b' => (int) ($genderStats['pres_b'] ?? 0),
                'pres_g' => (int) ($genderStats['pres_g'] ?? 0),
                'abs_b' => (int) ($genderStats['abs_b'] ?? 0),
                'abs_g' => (int) ($genderStats['abs_g'] ?? 0),
                'perm_b' => (int) ($genderStats['perm_b'] ?? 0),
                'perm_g' => (int) ($genderStats['perm_g'] ?? 0),
                'new_b' => (int) $newComers['b'],
                'new_g' => (int) $newComers['g'],
                'shifted_b' => 0,
                'shifted_g' => 0,
                'sick_b' => (int) ($genderStats['sick_b'] ?? 0),
                'sick_g' => (int) ($genderStats['sick_g'] ?? 0),
            ];
        }

        // Total Active enrollments in selected academic year(s)
        $totalActiveStudents = count(
            $enrollmentService->activeEnrollments((int) $schoolID)
                ->pluck('studentID')
                ->unique()
                ->all()
        );

        $totalRegistered = 0;
        $totalPresentAcrossSchool = 0;
        foreach ($systemAttendance as $classStat) {
            $totalRegistered += ((int) ($classStat['reg_b'] ?? 0) + (int) ($classStat['reg_g'] ?? 0));
            $totalPresentAcrossSchool += ((int) ($classStat['pres_b'] ?? 0) + (int) ($classStat['pres_g'] ?? 0));
        }

        $calculatedPercentage = $totalRegistered > 0
            ? round(($totalPresentAcrossSchool / $totalRegistered) * 100, 2)
            : 0;

        $academicYearLabel = null;
        try {
            $yearCtx = $yearService->headerContext((int) $schoolID);
            $academicYearLabel = collect($yearCtx['selected_year_ids'] ?? [])
                ->map(function ($id) use ($yearCtx) {
                    $y = ($yearCtx['years'] ?? collect())->firstWhere('academic_yearID', (int) $id);
                    return $y ? ($y->year_name ?? $y->year) : null;
                })
                ->filter()
                ->implode(' / ');
            if ($academicYearLabel === '') {
                $academicYearLabel = 'Current / Active enrollments';
            }
        } catch (\Throwable $e) {
            $academicYearLabel = 'Active enrollments';
        }

        return response()->json([
            'success' => true,
            'report' => $report ? array_merge($report->toArray(), [
                'teacher_name' => $report->teacher ? $report->teacher->first_name . ' ' . $report->teacher->last_name : 'N/A',
                'attendance_percentage' => $this->percentageFromAttendanceData($report->attendance_data ?? null)
                    ?? $report->attendance_percentage,
            ]) : null,
            'system_attendance' => $systemAttendance,
            'attendance_source' => $useFingerprint ? 'fingerprint' : 'system',
            'enrollment_status' => $enrollmentStatus,
            'academic_year_label' => $academicYearLabel,
            'total_active_students' => $totalActiveStudents,
            'calculated_attendance_percentage' => $calculatedPercentage
        ]);
    }

    /**
     * @param  array<int>  $studentIDs
     * @return array{b: int, g: int}
     */
    private function countStudentsByGender(int $schoolID, array $studentIDs, ?string $admissionDate = null): array
    {
        if ($studentIDs === []) {
            return ['b' => 0, 'g' => 0];
        }

        $query = DB::table('students')
            ->where('schoolID', $schoolID)
            ->whereIn('studentID', $studentIDs);

        if ($admissionDate) {
            $query->whereDate('admission_date', $admissionDate);
        }

        $row = $query
            ->selectRaw("SUM(CASE WHEN LOWER(gender) IN ('female', 'f') THEN 1 ELSE 0 END) as girls")
            ->selectRaw("SUM(CASE WHEN LOWER(gender) IN ('female', 'f') THEN 0 ELSE 1 END) as boys")
            ->first();

        return [
            'b' => (int) ($row->boys ?? 0),
            'g' => (int) ($row->girls ?? 0),
        ];
    }

    /**
     * Present / registered from the duty sheet rows.
     */
    private function percentageFromAttendanceData($attendanceData): ?float
    {
        if (is_string($attendanceData)) {
            $attendanceData = json_decode($attendanceData, true);
        }
        if (!is_array($attendanceData) || $attendanceData === []) {
            return null;
        }

        $registered = 0;
        $present = 0;
        foreach ($attendanceData as $row) {
            if (!is_array($row)) {
                continue;
            }
            $registered += (int) ($row['reg_b'] ?? 0) + (int) ($row['reg_g'] ?? 0);
            $present += (int) ($row['pres_b'] ?? 0) + (int) ($row['pres_g'] ?? 0);
        }

        if ($registered < 1) {
            return null;
        }

        return round(($present / $registered) * 100, 2);
    }

    public function saveDailyReport(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("Saving Daily Report", $request->all());

        try {
            $schoolID = Session::get('schoolID');
            $teacherID = Session::get('teacherID');

            if (!$schoolID || !$teacherID) {
                return response()->json(['success' => false, 'message' => 'Session expired. Please login again.'], 401);
            }

            $request->validate([
                'report_date' => 'required|date',
                'action' => 'required|in:send,draft',
                'attendance_percentage' => 'nullable|numeric'
            ]);

            $data = $request->all();
            $date = $data['report_date'];

            // Handle potential JSON string from JS
            $attendanceData = $request->get('attendance_data');
            if (is_string($attendanceData)) {
                $attendanceData = json_decode($attendanceData, true);
            }

            $report = \App\Models\DailyDutyReport::firstOrNew([
                'schoolID' => $schoolID,
                'teacherID' => $teacherID,
                'report_date' => $date,
            ]);

            $action = (string) ($data['action'] ?? 'draft');

            // Important:
            // When teacher clicks the quick "Send to Admin" button from the table, the request
            // may contain only (report_date, action). In that case we must NOT overwrite an
            // existing draft with 0/null defaults.
            $update = [
                'status' => $action === 'send' ? 'Sent' : 'Draft',
            ];

            // Update only if provided in request
            if ($request->exists('attendance_data')) {
                $update['attendance_data'] = $attendanceData ?? [];
            } elseif (! $report->exists) {
                $update['attendance_data'] = $attendanceData ?? [];
            }

            if ($request->exists('attendance_percentage')) {
                $update['attendance_percentage'] = $data['attendance_percentage'] ?? 0;
            } elseif (! $report->exists) {
                $update['attendance_percentage'] = $data['attendance_percentage'] ?? 0;
            }

            $optionalFields = [
                'school_environment',
                'pupils_cleanliness',
                'meals',
                'timetable_status',
                'outside_activities',
                'special_events',
                'english_speaking',
                'sick_students_list',
                'teacher_comments',
            ];

            foreach ($optionalFields as $field) {
                if ($request->exists($field) || ! $report->exists) {
                    $update[$field] = $data[$field] ?? null;
                }
            }

            $report->fill($update);
            $report->save();

            // Send SMS notification to Admin if Sent
            if ($report->status === 'Sent') {
                try {
                    $school = \App\Models\School::find($schoolID);
                    $teacher = \App\Models\Teacher::find($teacherID);
                    if ($school && $school->phone) {
                        $teacherName = $teacher?->first_name . ' ' . $teacher?->last_name ?? 'Mwalimu';
                        $smsMsg = "Habari Admin, " . $teacherName . " ametuma ripoti ya duty book ya tarehe " . date('d/m/Y', strtotime($date)) . ". Tafadhali ipitie ShuleXpert.";
                        $this->smsService->sendSms($school->phone, $smsMsg);
                    }
                } catch (\Exception $smsEx) {
                    \Illuminate\Support\Facades\Log::error("Duty Report SMS Error: " . $smsEx->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Report saved as ' . $report->status]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['success' => false, 'message' => 'Validation error: ' . $ve->getMessage()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("DailyDutyReport Save Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'start_date' => 'required|date',
            'weeks' => 'required|array|min:1',
            'weeks.*.teachers' => 'required|array|min:1',
            'weeks.*.teachers.*' => 'exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found in session. Please login again.'], 401);
        }

        $school = \App\Models\School::find($schoolID);
        $activeTerm = Term::where('schoolID', $schoolID)->where('status', 'Active')->first();
        $activeYear = AcademicYear::where('schoolID', $schoolID)->where('status', 'Active')->first();

        // Fetch holidays to skip them
        $holidays = \App\Models\Holiday::where('schoolID', $schoolID)->get();

        $initialDate = Carbon::parse($request->start_date);

        DB::beginTransaction();

        try {
            $currentStartDate = $initialDate->copy();

            foreach ($request->weeks as $weekIndex => $weekData) {
                if (!isset($weekData['teachers']) || empty($weekData['teachers'])) {
                    continue;
                }

                // Skip holidays logic
                $attempts = 0;
                while ($this->isHolidayWeek($currentStartDate, $holidays) && $attempts < 52) {
                    $currentStartDate->addWeek();
                    $attempts++;
                }

                $weekEndDate = $currentStartDate->copy()->addDays(6);

                // --- UPDATE/EDIT LOGIC ---
                // If records already exist for this exact timeframe, delete them before re-creating
                // This allows the "Edit" functionality to replace teachers for that week.
                TeacherDuty::where('schoolID', $schoolID)
                    ->whereDate('start_date', $currentStartDate->format('Y-m-d'))
                    ->whereDate('end_date', $weekEndDate->format('Y-m-d'))
                    ->delete();

                foreach ($weekData['teachers'] as $teacherID) {
                    if (!$teacherID) continue;

                    TeacherDuty::create([
                        'schoolID' => $schoolID,
                        'teacherID' => $teacherID,
                        'termID' => $activeTerm ? $activeTerm->termID : null,
                        'academic_yearID' => $activeYear ? $activeYear->academic_yearID : null,
                        'start_date' => $currentStartDate->format('Y-m-d'),
                        'end_date' => $weekEndDate->format('Y-m-d'),
                    ]);

                    // Send SMS (tolerant of failure)
                    try {
                        $teacher = Teacher::find($teacherID);
                        if ($teacher && $teacher->phone_number) {
                            $schoolName = $school ? $school->school_name : 'ShuleXpert';
                            $msg = "Habari {$teacher->first_name}, umechaguliwa kuwa mwalimu wa zamu kuanzia " .
                                   $currentStartDate->format('d/m/Y') . " hadi " . $weekEndDate->format('d/m/Y') . ". {$schoolName}";

                            $this->smsService->sendSms($teacher->phone_number, $msg);
                        }
                    } catch (\Exception $smsEx) {
                        \Illuminate\Support\Facades\Log::error("TeacherDuty SMS Error: " . $smsEx->getMessage());
                    }
                }

                $currentStartDate->addWeek();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Duty roster generated/updated successfully. Teachers have been notified via SMS.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("TeacherDuty Store Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Critical error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            TeacherDuty::where('schoolID', $schoolID)
                ->whereDate('start_date', $request->start_date)
                ->whereDate('end_date', $request->end_date)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Duties for this week deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to check if a week is a holiday week
     */
    private function isHolidayWeek($date, $holidays)
    {
        $weekStart = $date->copy()->startOfDay();
        $weekEnd = $date->copy()->addDays(6)->endOfDay();

        $holidayDaysInWeek = 0;
        
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $weekStart->copy()->addDays($i);
            
            foreach ($holidays as $holiday) {
                $hStart = Carbon::parse($holiday->start_date)->startOfDay();
                $hEnd = Carbon::parse($holiday->end_date)->endOfDay();
                
                if ($currentDay->between($hStart, $hEnd)) {
                    $holidayDaysInWeek++;
                    break; 
                }
            }
        }

        return $holidayDaysInWeek >= 4;
    }

    public function report()
    {
        $schoolID = Session::get('schoolID');

        // Fetch all duties
        $duties = TeacherDuty::where('schoolID', $schoolID)
            ->with(['teacher', 'term'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('Admin.teacher_duties.report', compact('duties'));
    }

    public function exportDailyReportPdf(Request $request)
    {
        $date = $request->get('date');
        $reportID = $request->get('reportID');
        $schoolID = Session::get('schoolID');

        $query = \App\Models\DailyDutyReport::query();
        if ($reportID) {
            $query->where('reportID', $reportID);
        } else {
            $query->where('schoolID', $schoolID)->whereDate('report_date', $date);
        }
        
        $report = $query->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Duty report not found.'], 404);
        }

        // Try to find the mwalimu by any means
        $targetTeacherId = 0;
        if (is_object($report)) {
            $targetTeacherId = $report->teacherID ?? 0;
        }

        $teacher = null;
        if ($targetTeacherId > 0) {
            $teacher = \App\Models\Teacher::where('id', $targetTeacherId)
                ->orWhere('fingerprint_id', $targetTeacherId)
                ->first();
        }
            
        if (!$teacher) {
            // Check User model
            $user = null;
            if ($targetTeacherId > 0) {
                $user = \App\Models\User::where('id', $targetTeacherId)
                    ->orWhere('fingerprint_id', $targetTeacherId)
                    ->first();
            }
                
            if ($user && is_object($user)) {
                $teacher = (object)[
                    'first_name' => $user->name ?? 'Staff',
                    'last_name' => ''
                ];
            } else {
                $teacher = (object)[
                    'first_name' => 'Duty Officer',
                    'last_name' => ''
                ];
            }
        }

        $school = \App\Models\School::find($report->schoolID);
        if (!$school) {
            $school = \App\Models\School::find($schoolID);
        }
        $classes = \App\Models\ClassModel::where('schoolID', $report->schoolID)->orderBy('class_name', 'asc')->get();
        $report->attendance_percentage = $this->percentageFromAttendanceData($report->attendance_data ?? null)
            ?? $report->attendance_percentage;

        Log::info("PDF Exporting for Report: " . $report->reportID, [
            'report_teacherID' => $report->teacherID,
            'resolved_teacher_first_name' => $teacher?->first_name ?? 'NOT FOUND'
        ]);

        try {
            // For debug per your request: exactly the same as teacher pattern
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('Teacher.duty_report_pdf', compact('report', 'school', 'teacher', 'classes'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('Daily_Duty_Report_' . $report->report_date->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error("PDF Generation failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'PDF Error: ' . $e->getMessage()], 500);
        }
    }

    public function approveReport(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'reportID' => 'required|exists:daily_duty_reports,reportID',
            'signed_by' => 'nullable|string|max:255',
            'admin_comments' => 'nullable|string',
            'signature_image' => 'nullable|string'
        ]);

        try {
            $report = \App\Models\DailyDutyReport::where('schoolID', $schoolID)
                ->where('reportID', $request->reportID)
                ->firstOrFail();

            $signedBy = $request->signed_by;
            if (!$signedBy) {
                // If no name provided, use session name or generic Administrator
                $signedBy = Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';
            }

            $report->update([
                'status' => 'Approved',
                'signed_by' => $signedBy,
                'signed_at' => now(),
                'admin_comments' => $request->admin_comments,
                'signature_image' => $request->signature_image,
                'approved_by_id' => Session::get('adminID')
            ]);

            return response()->json(['success' => true, 'message' => 'Report approved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve: ' . $e->getMessage()], 500);
        }
    }
}
