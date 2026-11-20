<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Result;
use App\Models\Attendance;
use App\Models\BookBorrow;
use App\Models\Examination;
use App\Models\ExamTimetable;
use App\Models\School;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\ClassSubject;
use App\Models\SubjectElector;
use App\Models\PermissionRequest;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ParentResultNotification;
use App\Models\ParentHomePackageNotification;
use App\Models\ParentSubjectNoteNotification;
use App\Models\CaDefinition;
use App\Models\GradeDefinition;
use App\Models\ClassModel;
use App\Services\SchoolWorkingDaysService;
use App\Services\StudentPermissionAttendanceService;
use App\Services\ParentFingerprintPortalService;
use App\Services\SecondaryResultGradingService;
use App\Models\StudentFingerprintAttendance;
use App\Models\AcademicYear;
use App\Services\SelectedAcademicYearService;
use App\Support\MobileApi;

class ParentsContoller extends Controller
{
    protected $smsService;
    protected SecondaryResultGradingService $grading;

    public function __construct(SecondaryResultGradingService $grading)
    {
        $this->smsService = new SmsService();
        $this->grading = $grading;
    }

    /**
     * Get current academic year ID for a school
     * Returns the academic year ID for current year, or null if not found
     */






    public function parentDashboard()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class', 'results.examination', 'subclass.classTeacher'])
            ->get();

        // Statistics
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'Active')->count();
        $maleStudents = $students->where('gender', 'Male')->count();
        $femaleStudents = $students->where('gender', 'Female')->count();

        // Get recent results (last 5)
        $recentResults = Result::whereIn('studentID', $students->pluck('studentID'))
            ->where('parent_status', 'enabled')
            ->with(['student', 'examination', 'classSubject.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent attendance (last 7 days)
        $recentAttendance = Attendance::whereIn('studentID', $students->pluck('studentID'))
            ->where('attendance_date', '>=', Carbon::now()->subDays(7))
            ->with(['student', 'subclass.class'])
            ->orderBy('attendance_date', 'desc')
            ->limit(10)
            ->get();

        // Calculate attendance statistics
        $attendanceStats = [];
        foreach ($students as $student) {
            $totalDays = Attendance::where('studentID', $student->studentID)
                ->where('attendance_date', '>=', Carbon::now()->startOfMonth())
                ->count();

            $presentDays = Attendance::where('studentID', $student->studentID)
                ->where('attendance_date', '>=', Carbon::now()->startOfMonth())
                ->where('status', 'Present')
                ->count();

            $attendanceStats[$student->studentID] = [
                'total' => $totalDays,
                'present' => $presentDays,
                'percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 0) : 0
            ];
        }

        // Get book borrows (active)
        $activeBookBorrows = BookBorrow::whereIn('studentID', $students->pluck('studentID'))
            ->where('status', 'borrowed')
            ->with(['student', 'book'])
            ->orderBy('borrow_date', 'desc')
            ->get();

        // Get upcoming exams (next 30 days)
        $upcomingExams = ExamTimetable::whereIn('subclassID', $students->pluck('subclassID'))
            ->where('exam_date', '>=', Carbon::now())
            ->where('exam_date', '<=', Carbon::now()->addDays(30))
            ->with(['examination', 'subclass.class', 'subject'])
            ->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(10)
            ->get();

        // Get recent examinations
        $recentExaminations = Examination::where('schoolID', $schoolID)
            ->where('status', 'results_available')
            ->orderBy('end_date', 'desc')
            ->limit(5)
            ->get();

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        // Notifications (combine results, attendance, exams)
        $notifications = collect();

        // New results notifications
        foreach ($recentResults as $result) {
            $notifications->push([
                'type' => 'result',
                'icon' => 'bi-trophy',
                'color' => 'success',
                'title' => 'New Result Available',
                'message' => $result->student->first_name . ' ' . $result->student->last_name . ' - ' . ($result->examination->exam_name ?? 'Exam'),
                'date' => $result->created_at,
                'link' => '#'
            ]);
        }

        // Absent/Late attendance notifications (today)
        $todayAttendance = Attendance::whereIn('studentID', $students->pluck('studentID'))
            ->where('attendance_date', Carbon::today())
            ->whereIn('status', ['Absent', 'Late'])
            ->with('student')
            ->get();

        foreach ($todayAttendance as $attendance) {
            $notifications->push([
                'type' => 'attendance',
                'icon' => 'bi-exclamation-triangle',
                'color' => $attendance->status == 'Absent' ? 'danger' : 'warning',
                'title' => 'Attendance Alert',
                'message' => $attendance->student->first_name . ' ' . $attendance->student->last_name . ' was ' . $attendance->status,
                'date' => $attendance->attendance_date,
                'link' => '#'
            ]);
        }

        // Upcoming exams notifications
        foreach ($upcomingExams->take(5) as $exam) {
            $notifications->push([
                'type' => 'exam',
                'icon' => 'bi-calendar-event',
                'color' => 'info',
                'title' => 'Upcoming Exam',
                'message' => ($exam->examination->exam_name ?? 'Exam') . ' - ' . ($exam->subject->subject_name ?? 'Subject'),
                'date' => $exam->exam_date,
                'link' => '#'
            ]);
        }

        // Sort notifications by date
        $notifications = $notifications->sortByDesc('date')->take(10)->values();

        // Get unviewed result notifications
        $resultNotifications = ParentResultNotification::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('is_viewed', 0)
            ->with(['student', 'examination'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Parents.dashboard', compact(
            'parent',
            'students',
            'totalStudents',
            'activeStudents',
            'maleStudents',
            'femaleStudents',
            'recentResults',
            'recentAttendance',
            'attendanceStats',
            'activeBookBorrows',
            'upcomingExams',
            'recentExaminations',
            'school',
            'notifications',
            'resultNotifications'
        ));
    }

    public function manageParentPermissions(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        $normalizedUserType = strtolower($userType ?? '');
        if (!$userType || $normalizedUserType !== 'parent' || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Umeingia kinyume na sheria');
        }

        $activeTab = $request->get('tab', 'request');
        $students = Student::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();
        $studentIds = $students->pluck('studentID')->all();
        $studentNames = $students->mapWithKeys(function ($student) {
            $name = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            return [$student->studentID => $name ?: 'N/A'];
        });

        if ($studentIds === []) {
            return view('Parent.manage_permissions', [
                'activeTab' => $activeTab,
                'students' => $students,
                'studentNames' => $studentNames,
                'pendingPermissions' => collect(),
                'approvedPermissions' => collect(),
                'rejectedPermissions' => collect(),
                'unreadPendingCount' => 0,
                'unreadApprovedCount' => 0,
                'unreadRejectedCount' => 0,
                'dateFrom' => $request->input('date_from'),
                'dateTo' => $request->input('date_to'),
                'filterType' => $request->input('filter_type', 'all'),
                'filterMonth' => $request->input('filter_month'),
                'filterMonthYear' => $request->input('filter_month_year', date('Y')),
                'filterYear' => $request->input('filter_year', date('Y')),
                'editPermission' => null,
            ]);
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $filterType = $request->input('filter_type', 'all');
        $filterMonth = $request->input('filter_month');
        $filterMonthYear = $request->input('filter_month_year', date('Y'));
        $filterYear = $request->input('filter_year', date('Y'));

        $query = DB::table('student_permissions')
            ->where('schoolID', $schoolID)
            ->where('requester_type', 'parent')
            ->where('requester_id', $parentID)
            ->whereIn('student_id', $studentIds);

        if ($filterType === 'range') {
            if ($dateFrom) {
                $query->whereDate('start_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('start_date', '<=', $dateTo);
            }
        } elseif ($filterType === 'month') {
            if ($filterMonth) {
                $query->whereMonth('start_date', $filterMonth)
                    ->whereYear('start_date', $filterMonthYear);
            }
        } elseif ($filterType === 'year') {
            if ($filterYear) {
                $query->whereYear('start_date', $filterYear);
            }
        }

        $permissions = $query->orderByDesc('created_at')->get()->map(function ($row) {
            $row->permissionID = (int) $row->id;
            $row->studentID = (int) $row->student_id;
            $row->created_at = $row->created_at ? Carbon::parse($row->created_at) : null;

            return $row;
        });

        $pendingPermissions = $permissions->where('status', 'pending');
        $approvedPermissions = $permissions->where('status', 'approved');
        $rejectedPermissions = $permissions->where('status', 'rejected');

        $unreadPendingCount = 0;
        $unreadApprovedCount = 0;
        $unreadRejectedCount = 0;
        if (Schema::hasColumn('student_permissions', 'is_read_by_parent')) {
            $unreadBase = DB::table('student_permissions')
                ->where('schoolID', $schoolID)
                ->where('requester_type', 'parent')
                ->where('requester_id', $parentID)
                ->whereIn('student_id', $studentIds)
                ->where('is_read_by_parent', false);
            $unreadPendingCount = (clone $unreadBase)->where('status', 'pending')->count();
            $unreadApprovedCount = (clone $unreadBase)->where('status', 'approved')->count();
            $unreadRejectedCount = (clone $unreadBase)->where('status', 'rejected')->count();

            if (in_array($activeTab, ['pending', 'approved', 'rejected'], true)) {
                DB::table('student_permissions')
                    ->where('schoolID', $schoolID)
                    ->where('requester_type', 'parent')
                    ->where('requester_id', $parentID)
                    ->whereIn('student_id', $studentIds)
                    ->where('status', $activeTab)
                    ->update(['is_read_by_parent' => true]);
                if ($activeTab === 'pending') {
                    $unreadPendingCount = 0;
                } elseif ($activeTab === 'approved') {
                    $unreadApprovedCount = 0;
                } else {
                    $unreadRejectedCount = 0;
                }
            }
        }

        $editPermission = null;
        $editId = $request->input('edit_id');
        if ($editId && $activeTab === 'request') {
            $editRow = DB::table('student_permissions')
                ->where('schoolID', $schoolID)
                ->where('requester_type', 'parent')
                ->where('requester_id', $parentID)
                ->where('id', $editId)
                ->where('status', 'rejected')
                ->first();
            if ($editRow) {
                $editRow->permissionID = (int) $editRow->id;
                $editRow->studentID = (int) $editRow->student_id;
                $editPermission = $editRow;
            }
        }

        return view('Parent.manage_permissions', [
            'activeTab' => $activeTab,
            'students' => $students,
            'studentNames' => $studentNames,
            'pendingPermissions' => $pendingPermissions,
            'approvedPermissions' => $approvedPermissions,
            'rejectedPermissions' => $rejectedPermissions,
            'unreadPendingCount' => $unreadPendingCount,
            'unreadApprovedCount' => $unreadApprovedCount,
            'unreadRejectedCount' => $unreadRejectedCount,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterType' => $filterType,
            'filterMonth' => $filterMonth,
            'filterMonthYear' => $filterMonthYear,
            'filterYear' => $filterYear,
            'editPermission' => $editPermission,
        ]);
    }

    public function markParentPermissionTabRead(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');
        $status = $request->input('status');

        if (strtolower((string) $userType) !== 'parent' || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Umeingia kinyume na sheria'], 403);
        }

        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return response()->json(['success' => false, 'message' => 'Tab haijulikani'], 422);
        }

        if (Schema::hasColumn('student_permissions', 'is_read_by_parent')) {
            $studentIds = Student::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->pluck('studentID');

            DB::table('student_permissions')
                ->where('schoolID', $schoolID)
                ->where('requester_type', 'parent')
                ->where('requester_id', $parentID)
                ->whereIn('student_id', $studentIds)
                ->where('status', $status)
                ->update(['is_read_by_parent' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function storeParentPermission(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        $normalizedUserType = strtolower($userType ?? '');
        if (!$userType || $normalizedUserType !== 'parent' || !$schoolID || !$parentID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Umeingia kinyume na sheria'], 403);
            }
            return redirect()->route('login')->with('error', 'Umeingia kinyume na sheria');
        }

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,studentID',
            'days_count' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason_type' => 'required|in:permission,sick',
            'reason_description' => 'required|string|min:5',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $student = Student::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('studentID', $validated['student_id'])
            ->where('status', 'Active')
            ->firstOrFail();

        $daysCount = $this->calculateWorkingDays($validated['start_date'], $validated['end_date']);
        if ($daysCount > 7) {
            $message = 'Siku zimezidi kikomo. Ruksa isizidi siku 7 za kazi.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message)->withInput();
        }

        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/permissions'), $attachmentName);
        }

        $hasChain = DB::table('permission_approval_chains')
            ->where('schoolID', $schoolID)
            ->where('permission_type', 'student')
            ->exists();

        $permissionData = [
            'schoolID' => $schoolID,
            'student_id' => $student->studentID,
            'requester_id' => $parentID,
            'requester_type' => 'parent',
            'time_mode' => 'days',
            'days_count' => $daysCount,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'status' => 'pending',
            'hod_status' => 'pending',
            'principal_status' => 'pending',
            'final_status' => 'pending',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('student_permissions', 'current_approval_order')) {
            $permissionData['current_approval_order'] = $hasChain ? 1 : 0;
        }
        if (Schema::hasColumn('student_permissions', 'is_chain_completed')) {
            $permissionData['is_chain_completed'] = false;
        }
        if (Schema::hasColumn('student_permissions', 'is_read_by_parent')) {
            $permissionData['is_read_by_parent'] = true;
        }

        if ($attachmentName) {
            $permissionData['attachment'] = $attachmentName;
        }

        $permissionId = $request->input('permission_id');
        $permission = null;

        if ($permissionId) {
            $existing = DB::table('student_permissions')
                ->where('id', $permissionId)
                ->where('schoolID', $schoolID)
                ->where('requester_type', 'parent')
                ->where('requester_id', $parentID)
                ->where('status', 'rejected')
                ->first();

            if ($existing) {
                $permissionData['status'] = 'pending';
                if (Schema::hasColumn('student_permissions', 'current_approval_order')) {
                    $permissionData['current_approval_order'] = $hasChain
                        ? max(1, (int) ($existing->current_approval_order ?? 1))
                        : 0;
                }
                if (Schema::hasColumn('student_permissions', 'is_read_by_parent')) {
                    $permissionData['is_read_by_parent'] = true;
                }

                DB::table('student_permissions')
                    ->where('id', $existing->id)
                    ->update($permissionData);

                DB::table('permission_approval_logs')
                    ->where('permissionID', $existing->id)
                    ->where('permission_type', 'student')
                    ->where('status', 'rejected')
                    ->delete();

                $permission = DB::table('student_permissions')->where('id', $existing->id)->first();
            }
        }

        if (! $permission) {
            $permissionData['created_at'] = now();
            $newId = DB::table('student_permissions')->insertGetId($permissionData);
            $permission = DB::table('student_permissions')->where('id', $newId)->first();
        }

        if ($hasChain && $permission) {
            app(\App\Http\Controllers\AdminController::class)->notifyNextApprover($permission, true);
        }

        $parent = ParentModel::where('parentID', $parentID)->first();
        $school = School::where('schoolID', $schoolID)->first();
        $reasonLabel = $this->parentReasonTypeLabelSw($validated['reason_type']);

        if ($parent && $parent->phone) {
            $this->smsService->sendSms(
                $parent->phone,
                $this->smsService->formatMessage(
                    "Ombi lako la ruksa ({$reasonLabel}) limewasilishwa. Tafadhali subiri uidhinishwe."
                )
            );
        }

        $adminPhones = $school ? $school->phoneNumbers() : [];
        if ($adminPhones !== []) {
            $studentName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));
            $message = $this->smsService->formatMessage(
                "Mzazi ameomba ruksa ya {$studentName}. Aina: {$reasonLabel}. Tafadhali ikague kwenye ShuleXpert."
            );
            foreach ($adminPhones as $adminPhone) {
                $this->smsService->sendSms($adminPhone, $message);
            }
        }

        $successMessage = 'Ombi la ruksa limewasilishwa kikamilifu.';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $successMessage]);
        }

        return redirect()->route('parent.permissions', ['tab' => 'pending'])->with('success', $successMessage);
    }

    private function parentReasonTypeLabelSw(string $reasonType): string
    {
        return match (strtolower($reasonType)) {
            'sick', 'medical' => 'Mgonjwa',
            'permission', 'official', 'family', 'emergency', 'other' => 'Ruhusa',
            default => ucfirst($reasonType),
        };
    }

    public function parentResults(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Get filter parameters
        $studentFilter = $request->get('student', '');
        $yearFilter = $request->get('year', '');
        $termFilter = $request->get('term', '');
        $typeFilter = request()->routeIs('parentReport') ? 'report' : $request->get('type', 'exam'); // 'exam' or 'report'
        $examFilter = $request->get('exam', '');

        // Build query for results - try both allowed and approved statuses
        $query = Result::whereIn('studentID', $students->pluck('studentID'))
            ->whereIn('status', ['allowed', 'approved'])
            ->where('parent_status', 'enabled')
            ->with([
                'student.subclass.class',
                'student.subclass.combie.subjects',
                'examination',
                'classSubject.subject.components',
            ]);

        // Apply filters
        if (!empty($studentFilter)) {
            $query->where('studentID', $studentFilter);
        }

        if (!empty($yearFilter)) {
            $query->whereHas('examination', function($q) use ($yearFilter) {
                $q->where('year', $yearFilter);
            });
        }

        if (!empty($termFilter)) {
            $query->whereHas('examination', function($q) use ($termFilter) {
                $q->where('term', $termFilter);
            });
        }

        // For exam type, check for CA definitions and include associated exams
        $activeExamIDs = [];
        if ($typeFilter === 'exam' && !empty($examFilter)) {
            $activeExamIDs = [$examFilter];
            $caDef = \App\Models\CaDefinition::where('examID', $examFilter)->where('schoolID', $schoolID)->first();
            if ($caDef && !empty($caDef->test_ids)) {
                $activeExamIDs = array_merge([$examFilter], $caDef->test_ids);
            }
            $query->whereIn('examID', $activeExamIDs);
        } elseif ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
            $termExams = Examination::where('schoolID', $schoolID)
                ->where('year', $yearFilter)
                ->where('term', $termFilter)
                ->where('approval_status', 'Approved')
                ->pluck('examID')
                ->toArray();
            $query->whereIn('examID', $termExams);
            $activeExamIDs = $termExams;
        }

        // Get results ordered by exam date and student
        $results = $query->orderBy('created_at', 'desc')->get();

        // Get all examinations for filter dropdown (only approved results exams)
        $examinationsQuery = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function($q) use ($students) {
                $q->whereIn('studentID', $students->pluck('studentID'))
                  ->whereIn('status', ['allowed', 'approved'])
                  ->where('parent_status', 'enabled');
            });

        // Filter examinations by year if year filter is set
        if (!empty($yearFilter)) {
            $examinationsQuery->where('year', $yearFilter);
        }

        // Filter examinations by term if term filter is set
        if (!empty($termFilter)) {
            $examinationsQuery->where('term', $termFilter);
        }

        $examinations = $examinationsQuery->orderBy('year', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get unique years from examinations (all approved exams with results for parent's students)
        // Try multiple status values to ensure we get data
        $years = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function($q) use ($students) {
                $q->whereIn('studentID', $students->pluck('studentID'))
                  ->whereIn('status', ['allowed', 'approved'])
                  ->where('parent_status', 'enabled');
            })
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->unique()
            ->values()
            ->toArray();

        // If still no years, try without status restriction
        if (empty($years)) {
            $years = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'));
                })
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->unique()
                ->values()
                ->toArray();
        }

        // Academic years (O/A) - wizard filters by student level after student is chosen
        $ayContext = $this->parentAcademicYearContext((int) $schoolID);
        $academicYears = $ayContext['options'];
        $academicYearLabel = $ayContext['label'];
        $ayMode = $ayContext['mode'];
        if (!empty($ayContext['calendar_years'])) {
            $years = $ayContext['calendar_years'];
        }

        $yearService = app(SelectedAcademicYearService::class);
        $isSecondaryDual = $ayMode === 'secondary_dual';
        $students = $students->map(function ($student) use ($yearService, $isSecondaryDual, $ayMode) {
            $className = $student->subclass->class->class_name ?? '';
            $isA = $yearService->isALevelClassName($className);
            $student->setAttribute('class_name', $className);
            $student->setAttribute('is_a_level', $isA);
            if ($ayMode === 'primary') {
                $levelLabel = $className ?: 'Primary';
            } elseif ($isSecondaryDual || $ayMode === 'secondary_o_only') {
                $levelLabel = ($isA ? 'A Level' : 'O Level') . ($className ? ' · ' . $className : '');
            } else {
                $levelLabel = $className ?: '-';
            }
            $student->setAttribute('level_label', $levelLabel);
            return $student;
        });

        // Get unique terms from examinations (all approved exams with results for parent's students)
        $terms = Examination::where('schoolID', $schoolID)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function($q) use ($students) {
                $q->whereIn('studentID', $students->pluck('studentID'))
                  ->whereIn('status', ['allowed', 'approved'])
                  ->where('parent_status', 'enabled');
            })
            ->distinct()
            ->orderBy('term', 'asc')
            ->pluck('term')
            ->unique()
            ->values()
            ->toArray();

        // If still no terms, try without status restriction
        if (empty($terms)) {
            $terms = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'));
                })
                ->distinct()
                ->orderBy('term', 'asc')
                ->pluck('term')
                ->unique()
                ->values()
                ->toArray();
        }

        // Mark all result notifications as viewed
        ParentResultNotification::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('is_viewed', 0)
            ->update(['is_viewed' => 1]);

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        // Process results based on type (exam or report)
        $processedResults = [];

        if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
            // For report: Group by student and calculate term averages
            $studentResults = $results->groupBy('studentID');

            // Filter by published reports only
            $publishedReports = \App\Models\PublishedReport::where('schoolID', $schoolID)
                ->where('term', $termFilter)
                ->where('year', $yearFilter)
                ->where('status', 'enabled')
                ->pluck('classID')
                ->toArray();

            $schoolType = $school->school_type ?? 'Secondary';

            foreach ($studentResults as $studentID => $studentResultList) {
                $student = $studentResultList->first()->student ?? null;
                if (!$student) continue;

                $studentClassID = $student->subclass ? $student->subclass->classID : null;
                if (!in_array($studentClassID, $publishedReports)) {
                    continue;
                }

                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;

                $examResults = $studentResultList->groupBy('examID');
                $examsData = [];
                $subjectAccum = []; // subjectID => totals across exams

                foreach ($examResults as $examID => $examResultList) {
                    $exam = $examResultList->first()->examination ?? null;
                    if (!$exam) continue;

                    $collapsed = $this->grading->collapseResultsToSubjectMarks($examResultList);
                    $examTotalMarks = 0;
                    $examSubjectCount = 0;

                    foreach ($collapsed as $entry) {
                        if ($entry['marks'] === null || $entry['marks'] === '') {
                            continue;
                        }
                        $marks = (float) $entry['marks'];
                        $examTotalMarks += $marks;
                        $examSubjectCount++;

                        $sid = $entry['subjectID'];
                        if (!isset($subjectAccum[$sid])) {
                            $subjectAccum[$sid] = [
                                'subject_name' => $entry['subject_name'],
                                'subjectID' => $sid,
                                'is_duo' => $entry['is_duo'],
                                'exams' => [],
                                'mark_sum' => 0.0,
                                'mark_count' => 0,
                            ];
                        }
                        $gradeInfo = $schoolType === 'Secondary'
                            ? $this->grading->getSecondaryHardcodedGrade($marks, $className)
                            : $this->calculateGradeOrDivision($marks, $schoolType, $className, $studentClassID);

                        $subjectAccum[$sid]['exams'][$examID] = [
                            'marks' => $marks,
                            'grade' => $gradeInfo['grade'] ?? null,
                            'exam_name' => $exam->exam_name,
                            'papers' => $entry['papers'] ?? [],
                        ];
                        $subjectAccum[$sid]['mark_sum'] += $marks;
                        $subjectAccum[$sid]['mark_count']++;
                    }

                    $examAverage = $examSubjectCount > 0 ? round($examTotalMarks / $examSubjectCount, 0) : 0;
                    $examsData[] = [
                        'exam' => $exam,
                        'average' => $examAverage,
                        'grade' => null,
                        'division' => null,
                    ];
                }

                $termExamsCount = \App\Models\Examination::where('schoolID', $schoolID)
                    ->where('year', $yearFilter)
                    ->where('term', $termFilter)
                    ->count();
                $reportExamsCount = $termExamsCount > 0 ? $termExamsCount : 1;

                $subjects = [];
                $allSubjectPoints = [];
                $subjectAverages = [];

                foreach ($subjectAccum as $sid => $data) {
                    $subjectAverage = round($data['mark_sum'] / $reportExamsCount, 0);
                    $subjectAverages[] = $subjectAverage;

                    $gradePoints = $schoolType === 'Secondary'
                        ? $this->grading->getSecondaryHardcodedGrade($subjectAverage, $className)
                        : $this->calculateGradeOrDivision($subjectAverage, $schoolType, $className, $studentClassID);

                    if ($gradePoints['points'] !== null) {
                        $allSubjectPoints[] = [
                            'points' => (int) $gradePoints['points'],
                            'marks' => (float) $subjectAverage,
                            'grade' => $gradePoints['grade'] ?? null,
                            'subjectID' => (int) $sid,
                        ];
                    }

                    $subjects[] = [
                        'subject_name' => $data['subject_name'],
                        'subjectID' => $sid,
                        'is_duo' => $data['is_duo'],
                        'exams' => array_values($data['exams']),
                        'average' => $subjectAverage,
                        'grade' => $gradePoints['grade'] ?? null,
                        'points' => $gradePoints['points'] ?? null,
                        'remark' => $this->grading->subjectRemark($gradePoints['grade'] ?? null, $className),
                        'papers' => [],
                    ];
                }

                $overallAverage = !empty($subjectAverages)
                    ? round(array_sum($subjectAverages) / max(1, count($subjectAverages)), 0)
                    : 0;

                $totalPoints = 0;
                $overallDivision = null;
                $overallGrade = null;

                if ($schoolType === 'Secondary') {
                    $pick = $this->grading->resolveSecondaryBestSubjectsForDivision(
                        $allSubjectPoints,
                        (string) $className,
                        $schoolType,
                        $this->grading->getCombinationSubjectIdsForStudent($student),
                    );
                    $totalPoints = $pick['total_points'];
                    $div = $this->grading->calculateDivisionFromPoints(
                        $totalPoints,
                        $schoolType,
                        $className,
                    );
                    $overallDivision = $div['division'] ?? null;
                } else {
                    $overallGradeOrDiv = $this->calculateTotalDivision(
                        0,
                        $schoolType,
                        $className,
                        $overallAverage,
                        $studentClassID,
                    );
                    $overallGrade = $overallGradeOrDiv['grade'] ?? null;
                }

                $processedResults[] = [
                    'type' => 'report',
                    'student' => $student,
                    'term' => $termFilter,
                    'year' => $yearFilter,
                    'exams' => $examsData,
                    'subjects' => $subjects,
                    'overall_average' => $overallAverage,
                    'average' => $overallAverage,
                    'overall_grade' => $overallGrade ?? $overallDivision,
                    'overall_division' => $overallDivision,
                    'total_points' => $totalPoints,
                    'is_a_level' => $this->grading->isALevelClassName($className),
                ];
            }
        } else {
            // For exam: duo collapsed totals + papers for parent view
            $studentResults = $results->groupBy('studentID');
            $schoolType = $school->school_type ?? 'Secondary';

            foreach ($studentResults as $studentID => $studentResultList) {
                $student = $studentResultList->first()->student ?? null;
                if (!$student) continue;
                $mainExam = Examination::find($examFilter);

                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;

                $caDef = \App\Models\CaDefinition::where('examID', $examFilter)
                    ->where('schoolID', $schoolID)
                    ->first();
                $involvedCount = ($caDef && !empty($caDef->test_ids))
                    ? count($caDef->test_ids) + 1
                    : 1;

                $finalSubjects = [];
                $allSubjectPoints = [];
                $totalMarks = 0;
                $subjectCount = 0;

                if ($involvedCount > 1) {
                    // CA: collapse per exam, then average
                    $byExam = $studentResultList->groupBy('examID');
                    $accum = [];
                    foreach ($byExam as $examRows) {
                        foreach ($this->grading->collapseResultsToSubjectMarks($examRows) as $entry) {
                            $sid = $entry['subjectID'];
                            if (!isset($accum[$sid])) {
                                $accum[$sid] = [
                                    'subject_name' => $entry['subject_name'],
                                    'subjectID' => $sid,
                                    'is_duo' => $entry['is_duo'],
                                    'exams' => [],
                                    'sum' => 0.0,
                                    'count' => 0,
                                    'papers' => $entry['papers'] ?? [],
                                ];
                            }
                            if ($entry['marks'] !== null && $entry['marks'] !== '') {
                                $marks = (float) $entry['marks'];
                                $examModel = $examRows->first()->examination ?? null;
                                $gradeInfo = $schoolType === 'Secondary'
                                    ? $this->grading->getSecondaryHardcodedGrade($marks, $className)
                                    : $this->calculateGradeOrDivision($marks, $schoolType, $className, $classID);
                                $accum[$sid]['exams'][] = [
                                    'exam_name' => $examModel->exam_name ?? 'N/A',
                                    'marks' => $marks,
                                    'grade' => $gradeInfo['grade'] ?? null,
                                    'papers' => $entry['papers'] ?? [],
                                ];
                                $accum[$sid]['sum'] += $marks;
                                $accum[$sid]['count']++;
                                if (!empty($entry['papers'])) {
                                    $accum[$sid]['papers'] = $entry['papers'];
                                }
                            }
                        }
                    }

                    foreach ($accum as $sid => $data) {
                        $avg = $involvedCount > 0
                            ? round($data['sum'] / $involvedCount, 0)
                            : 0;
                        $totalMarks += $avg;
                        $subjectCount++;
                        $gradePoints = $schoolType === 'Secondary'
                            ? $this->grading->getSecondaryHardcodedGrade($avg, $className)
                            : $this->calculateGradeOrDivision($avg, $schoolType, $className, $classID);
                        if ($gradePoints['points'] !== null) {
                            $allSubjectPoints[] = [
                                'points' => (int) $gradePoints['points'],
                                'marks' => (float) $avg,
                                'grade' => $gradePoints['grade'] ?? null,
                                'subjectID' => (int) $sid,
                            ];
                        }
                        $finalSubjects[] = [
                            'subject_name' => $data['subject_name'],
                            'subjectID' => $sid,
                            'is_duo' => $data['is_duo'],
                            'exams' => $data['exams'],
                            'average' => $avg,
                            'grade' => $gradePoints['grade'] ?? 'F',
                            'points' => $gradePoints['points'] ?? null,
                            'remark' => $this->grading->subjectRemark($gradePoints['grade'] ?? null, $className),
                            'papers' => $data['papers'] ?? [],
                        ];
                    }
                } else {
                    foreach ($this->grading->collapseResultsToSubjectMarks($studentResultList) as $entry) {
                        if ($entry['marks'] === null || $entry['marks'] === '') {
                            continue;
                        }
                        $avg = round((float) $entry['marks'], 0);
                        $totalMarks += $avg;
                        $subjectCount++;
                        $gradePoints = $schoolType === 'Secondary'
                            ? $this->grading->getSecondaryHardcodedGrade($avg, $className)
                            : $this->calculateGradeOrDivision($avg, $schoolType, $className, $classID);
                        if ($gradePoints['points'] !== null) {
                            $allSubjectPoints[] = [
                                'points' => (int) $gradePoints['points'],
                                'marks' => (float) $avg,
                                'grade' => $gradePoints['grade'] ?? null,
                                'subjectID' => (int) $entry['subjectID'],
                            ];
                        }
                        $finalSubjects[] = [
                            'subject_name' => $entry['subject_name'],
                            'subjectID' => $entry['subjectID'],
                            'is_duo' => $entry['is_duo'],
                            'exams' => [[
                                'exam_name' => $mainExam->exam_name ?? 'N/A',
                                'marks' => $avg,
                                'papers' => $entry['papers'] ?? [],
                            ]],
                            'average' => $avg,
                            'grade' => $gradePoints['grade'] ?? 'F',
                            'points' => $gradePoints['points'] ?? null,
                            'remark' => $this->grading->subjectRemark($gradePoints['grade'] ?? null, $className),
                            'papers' => $entry['papers'] ?? [],
                        ];
                    }
                }

                $overallAverage = $subjectCount > 0 ? round($totalMarks / $subjectCount, 0) : 0;

                $totalPoints = 0;
                $overallDivision = null;
                $overallGrade = null;
                if ($schoolType === 'Secondary') {
                    $pick = $this->grading->resolveSecondaryBestSubjectsForDivision(
                        $allSubjectPoints,
                        (string) $className,
                        $schoolType,
                        $this->grading->getCombinationSubjectIdsForStudent($student),
                    );
                    $totalPoints = $pick['total_points'];
                    $div = $this->grading->calculateDivisionFromPoints(
                        $totalPoints,
                        $schoolType,
                        $className,
                    );
                    $overallDivision = $div['division'] ?? null;
                } else {
                    $overallGradeOrDiv = $this->calculateTotalDivision(
                        0,
                        $schoolType,
                        $className,
                        $overallAverage,
                        $classID,
                    );
                    $overallGrade = $overallGradeOrDiv['grade'] ?? null;
                }

                $processedResults[] = [
                    'type' => 'exam',
                    'student' => $student,
                    'exam' => $mainExam,
                    'results' => $finalSubjects,
                    'has_ca' => $involvedCount > 1,
                    'total_marks' => $totalMarks,
                    'average' => $overallAverage,
                    'overall_grade' => $overallGrade ?? $overallDivision,
                    'overall_division' => $overallDivision,
                    'total_points' => $totalPoints,
                    'is_a_level' => $this->grading->isALevelClassName($className),
                    'position' => $this->calculatePosition(
                        $student,
                        $termFilter,
                        $yearFilter,
                        $overallAverage,
                        $schoolType,
                    ),
                ];
            }
        }

        // No further processing needed for $processedResults as overhaul handles it
        $resultsWithDivisions = $processedResults;

        return view(request()->routeIs('parentReport') ? 'Parents.term_report' : 'Parents.results', compact(
            'parent',
            'students',
            'results',
            'resultsWithDivisions',
            'examinations',
            'years',
            'academicYears',
            'academicYearLabel',
            'ayMode',
            'terms',
            'studentFilter',
            'yearFilter',
            'termFilter',
            'typeFilter',
            'examFilter',
            'school'
        ));
    }

    private function getGradeFromDefinition($marks, $classID)
    {
        if ($marks === null || $marks === '' || !$classID) {
            return ['grade' => null, 'points' => null];
        }

        $marksNum = round((float) $marks, 2);

        // Get grade definition from database
        $gradeDefinition = GradeDefinition::where('classID', $classID)
            ->where('first', '<=', $marksNum)
            ->where('last', '>=', $marksNum)
            ->first();

        if (!$gradeDefinition) {
            return ['grade' => null, 'points' => null];
        }

        // Calculate points based on grade (for backward compatibility)
        $grade = $gradeDefinition->grade;
        $points = null;

        // Points calculation for O-Level and A-Level (maintain existing logic)
        $className = ClassModel::find($classID)->class_name ?? '';
        $classNameLower = strtolower(preg_replace('/[\s\-]+/', '_', $className));

        if (in_array($classNameLower, ['form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4']) || preg_match('/form\s*(one|two|three|four|1|2|3|4)/i', $className)) {
            // O-Level points
            $pointsMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'F' => 5];
            $points = $pointsMap[$grade] ?? 5;
        } elseif (in_array($classNameLower, ['form_five', 'form_six', 'form_5', 'form_6']) || preg_match('/form\s*(five|six|5|6)/i', $className)) {
            // A-Level points (NECTA): A=1 … S=6, F=7
            $pointsMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'S' => 6, 'F' => 7];
            $points = $pointsMap[$grade] ?? 7;
        }

        return ['grade' => $grade, 'points' => $points];
    }

    private function calculateGradeOrDivision($marks, $schoolType, $className = null, $classID = null)
    {
        if ($marks === null || $marks === '') {
            return ['grade' => null, 'division' => null, 'points' => null];
        }

        if ($schoolType === 'Secondary') {
            $g = $this->grading->getSecondaryHardcodedGrade($marks, $className);
            return [
                'grade' => $g['grade'],
                'division' => null,
                'points' => $g['points'],
            ];
        }

        // Primary: prefer grade_definitions
        if ($classID) {
            $def = $this->getGradeFromDefinition($marks, $classID);
            if ($def['grade']) {
                return ['grade' => $def['grade'], 'division' => null, 'points' => $def['points']];
            }
        }

        $marksNum = (float) $marks;
        if ($marksNum >= 75) {
            return ['grade' => 'A', 'division' => null, 'points' => null];
        }
        if ($marksNum >= 65) {
            return ['grade' => 'B', 'division' => null, 'points' => null];
        }
        if ($marksNum >= 45) {
            return ['grade' => 'C', 'division' => null, 'points' => null];
        }
        if ($marksNum >= 30) {
            return ['grade' => 'D', 'division' => null, 'points' => null];
        }
        if ($marksNum >= 20) {
            return ['grade' => 'E', 'division' => null, 'points' => null];
        }

        return ['grade' => 'F', 'division' => null, 'points' => null];
    }

    private function calculateTotalDivision($totalPoints, $schoolType, $className = null, $averageMarks = 0, $classID = null)
    {
        if ($schoolType === 'Secondary') {
            return $this->grading->calculateDivisionFromPoints(
                (int) $totalPoints,
                $schoolType,
                $className,
            );
        }

        // Primary: Calculate grade from grade_definitions if classID available
        if ($classID && $averageMarks !== null && $averageMarks !== '') {
            $gradeResult = $this->getGradeFromDefinition($averageMarks, $classID);
            if ($gradeResult['grade']) {
                return ['grade' => $gradeResult['grade'], 'division' => null];
            }
        }

        if ($averageMarks >= 75) {
            return ['grade' => 'A', 'division' => null];
        }
        if ($averageMarks >= 65) {
            return ['grade' => 'B', 'division' => null];
        }
        if ($averageMarks >= 45) {
            return ['grade' => 'C', 'division' => null];
        }
        if ($averageMarks >= 30) {
            return ['grade' => 'D', 'division' => null];
        }

        return ['grade' => 'F', 'division' => null];
    }

    public function parentAttendance(Request $request)
    {
        $context = $this->resolveParentPortalContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        ['parent' => $parent, 'students' => $students, 'school' => $school, 'schoolID' => $schoolID] = $context;

        $month = (int) $request->input('month', (int) date('m'));
        $year = (int) $request->input('year', (int) date('Y'));
        $search = trim((string) $request->input('q', ''));

        $childrenOverview = [];
        foreach ($students as $student) {
            if ($search !== '') {
                $haystack = strtolower(trim(
                    ($student->first_name ?? '') . ' ' .
                    ($student->middle_name ?? '') . ' ' .
                    ($student->last_name ?? '') . ' ' .
                    ($student->admission_number ?? '')
                ));
                if (! str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            $childrenOverview[] = [
                'student' => $student,
                'stats' => $this->getParentChildMonthStats($student->studentID, $schoolID, $year, $month),
            ];
        }

        return view('Parents.attendance', compact(
            'parent',
            'students',
            'childrenOverview',
            'school',
            'month',
            'year',
            'search'
        ));
    }

    public function parentStudentAttendance(Request $request, int $studentID)
    {
        $context = $this->resolveParentPortalContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        ['parent' => $parent, 'students' => $students, 'school' => $school, 'schoolID' => $schoolID] = $context;

        $student = $students->firstWhere('studentID', $studentID);
        if (! $student) {
            return redirect()->route('parentAttendance')->with('error', 'Mwanafunzi hajapatikana.');
        }

        $month = max(1, min(12, (int) $request->input('month', (int) date('m'))));
        $year = (int) $request->input('year', (int) date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $calendar = $this->buildParentAttendanceMonthCalendar($studentID, $schoolID, $year, $month);
        $stats = $calendar['stats'];

        $monthLabel = Carbon::create($year, $month, 1)->locale(app()->getLocale())->isoFormat('MMMM Y');
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        return view('Parents.student_attendance_calendar', compact(
            'parent',
            'student',
            'school',
            'month',
            'year',
            'monthLabel',
            'prevMonth',
            'nextMonth',
            'calendar',
            'stats'
        ))->with('attendanceMode', 'system');
    }

    /**
     * @return array{parent: ParentModel, students: \Illuminate\Support\Collection, school: ?School, schoolID: int}|\Illuminate\Http\RedirectResponse
     */
    private function resolveParentPortalContext()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (! $userType || ! $schoolID || ! $parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (! $parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', '!=', 'Transferred')
            ->with(['subclass.class'])
            ->get();

        $school = School::where('schoolID', $schoolID)->first();

        return [
            'parent' => $parent,
            'students' => $students,
            'school' => $school,
            'schoolID' => (int) $schoolID,
        ];
    }

    /**
     * @return array{
     *     present: int,
     *     absent: int,
     *     sick: int,
     *     permission: int,
     *     incomplete: int,
     *     working_days: int,
     *     attendance_rate: float
     * }
     */
    private function getParentChildMonthStats(int $studentID, int $schoolID, int $year, int $month): array
    {
        $calendar = $this->buildParentAttendanceMonthCalendar($studentID, $schoolID, $year, $month);

        return $calendar['stats'];
    }

    /**
     * @return array{
     *     stats: array<string, int|float>,
     *     rows: array<int, array<int, array<string, mixed>>>,
     *     legend: array<int, array<string, string>>
     * }
     */
    private function buildParentAttendanceMonthCalendar(int $studentID, int $schoolID, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $today = Carbon::today();

        $workingDaysService = app(SchoolWorkingDaysService::class);
        $permissionService = app(StudentPermissionAttendanceService::class);

        $workingDates = $workingDaysService->getWorkingDates($schoolID, $start, $end);
        $workingDateSet = array_flip($workingDates);

        $permissionMap = $permissionService->loadApprovedStatusMap(
            $schoolID,
            $start->toDateString(),
            $end->toDateString(),
            [$studentID]
        );

        $attendanceByDate = Attendance::where('studentID', $studentID)
            ->where('schoolID', $schoolID)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->attendance_date)->toDateString());

        $stats = [
            'present' => 0,
            'absent' => 0,
            'sick' => 0,
            'permission' => 0,
            'incomplete' => 0,
            'working_days' => count($workingDates),
            'attendance_rate' => 0.0,
        ];

        $cells = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->toDateString();
            $cell = $this->resolveParentAttendanceCell(
                $dateStr,
                $today,
                isset($workingDateSet[$dateStr]),
                $attendanceByDate[$dateStr] ?? null,
                $permissionService->statusForStudentOnDate($permissionMap, $studentID, $dateStr)
            );

            if ($cell['counts_toward_stats'] ?? false) {
                match ($cell['status']) {
                    'present' => $stats['present']++,
                    'absent' => $stats['absent']++,
                    'sick' => $stats['sick']++,
                    'permission' => $stats['permission']++,
                    'incomplete' => $stats['incomplete']++,
                    default => null,
                };
            }

            $cells[] = $cell;
            $cursor->addDay();
        }

        $eligibleDays = $stats['present'] + $stats['absent'] + $stats['sick'] + $stats['permission'] + $stats['incomplete'];
        if ($eligibleDays > 0) {
            $stats['attendance_rate'] = round(($stats['present'] / $eligibleDays) * 100, 1);
        }

        $rows = array_chunk($cells, 8);

        return [
            'stats' => $stats,
            'rows' => $rows,
            'legend' => [
                ['key' => 'present', 'label' => __('parent.present'), 'class' => 'att-cell-present', 'icon' => 'bx-check'],
                ['key' => 'absent', 'label' => __('parent.missed'), 'class' => 'att-cell-absent', 'icon' => 'bx-x'],
                ['key' => 'permission', 'label' => __('parent.permission'), 'class' => 'att-cell-permission', 'icon' => 'bx-time-five'],
                ['key' => 'sick', 'label' => __('parent.sick'), 'class' => 'att-cell-sick', 'icon' => 'bx-plus-medical'],
                ['key' => 'incomplete', 'label' => __('parent.incomplete'), 'class' => 'att-cell-incomplete', 'icon' => 'bx-minus'],
                ['key' => 'non_school', 'label' => __('parent.non_school'), 'class' => 'att-cell-non-school', 'icon' => 'bx-calendar-x'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveParentAttendanceCell(
        string $dateStr,
        Carbon $today,
        bool $isWorkingDay,
        ?Attendance $attendance,
        ?string $permissionStatus
    ): array {
        if (! $isWorkingDay) {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'non_school',
                'label' => __('parent.non_school'),
                'icon' => 'bx-calendar-x',
                'css_class' => 'att-cell-non-school',
                'counts_toward_stats' => false,
            ];
        }

        $status = null;
        if ($permissionStatus === 'Sick') {
            $status = 'sick';
        } elseif ($permissionStatus === 'Excused') {
            $status = 'permission';
        } elseif ($attendance) {
            $normalized = strtolower((string) $attendance->status);
            $status = match ($normalized) {
                'present' => 'present',
                'absent' => 'absent',
                'sick' => 'sick',
                'excused', 'permission' => 'permission',
                default => 'incomplete',
            };
        } elseif ($dateStr <= $today->toDateString()) {
            $status = 'incomplete';
        } else {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'future',
                'label' => __('parent.future'),
                'icon' => 'bx-minus',
                'css_class' => 'att-cell-future',
                'counts_toward_stats' => false,
            ];
        }

        $map = [
            'present' => ['label' => __('parent.present'), 'icon' => 'bx-check', 'css_class' => 'att-cell-present'],
            'absent' => ['label' => __('parent.missed'), 'icon' => 'bx-x', 'css_class' => 'att-cell-absent'],
            'permission' => ['label' => __('parent.permission'), 'icon' => 'bx-time-five', 'css_class' => 'att-cell-permission'],
            'sick' => ['label' => __('parent.sick'), 'icon' => 'bx-plus-medical', 'css_class' => 'att-cell-sick'],
            'incomplete' => ['label' => __('parent.incomplete'), 'icon' => 'bx-minus', 'css_class' => 'att-cell-incomplete'],
        ];

        $meta = $map[$status] ?? $map['incomplete'];

        return array_merge([
            'date' => $dateStr,
            'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
            'status' => $status,
            'counts_toward_stats' => true,
        ], $meta);
    }

    private function formatParentAttendanceDateLabel(string $dateStr): string
    {
        $locale = in_array(app()->getLocale(), ['en', 'sw'], true) ? app()->getLocale() : 'sw';

        return Carbon::parse($dateStr)->locale($locale)->isoFormat('dddd, D MMMM Y');
    }

    public function parentFingerprintAttendance(Request $request)
    {
        $context = $this->resolveParentPortalContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        ['parent' => $parent, 'students' => $students, 'school' => $school, 'schoolID' => $schoolID] = $context;

        $month = (int) $request->input('month', (int) date('m'));
        $year = (int) $request->input('year', (int) date('Y'));
        $search = trim((string) $request->input('q', ''));

        $portalService = app(ParentFingerprintPortalService::class);

        $childrenOverview = [];
        foreach ($students as $student) {
            if ($search !== '') {
                $haystack = strtolower(trim(
                    ($student->first_name ?? '') . ' ' .
                    ($student->middle_name ?? '') . ' ' .
                    ($student->last_name ?? '') . ' ' .
                    ($student->admission_number ?? '')
                ));
                if (! str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            $enabled = $portalService->isStudentEnabled($student);

            $childrenOverview[] = [
                'student' => $student,
                'enabled' => $enabled,
                'stats' => $enabled
                    ? $this->getParentChildFingerprintMonthStats($student->studentID, $schoolID, $year, $month)
                    : null,
            ];
        }

        return view('Parents.fingerprint_attendance', compact(
            'parent',
            'students',
            'childrenOverview',
            'school',
            'month',
            'year',
            'search'
        ));
    }

    public function parentStudentFingerprintAttendance(Request $request, int $studentID)
    {
        $context = $this->resolveParentPortalContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        ['parent' => $parent, 'students' => $students, 'school' => $school, 'schoolID' => $schoolID] = $context;

        $student = $students->firstWhere('studentID', $studentID);
        if (! $student) {
            return redirect()->route('parentFingerprintAttendance')->with('error', 'Mwanafunzi hajapatikana.');
        }

        $portalService = app(ParentFingerprintPortalService::class);
        if (! $portalService->isStudentEnabled($student)) {
            return redirect()->route('parentFingerprintAttendance')->with(
                'error',
                'Mahudhurio kwa alama za vidole hayajaruhusiwa kwa darasa la mwanafunzi huyu.'
            );
        }

        $month = max(1, min(12, (int) $request->input('month', (int) date('m'))));
        $year = (int) $request->input('year', (int) date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $calendar = $this->buildParentFingerprintMonthCalendar($studentID, $schoolID, $year, $month);
        $stats = $calendar['stats'];
        $attendanceMode = 'fingerprint';

        $monthLabel = Carbon::create($year, $month, 1)->locale(app()->getLocale())->isoFormat('MMMM Y');
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        return view('Parents.student_attendance_calendar', compact(
            'parent',
            'student',
            'school',
            'month',
            'year',
            'monthLabel',
            'prevMonth',
            'nextMonth',
            'calendar',
            'stats',
            'attendanceMode'
        ));
    }

    /**
     * @return array{
     *     present: int,
     *     absent: int,
     *     sick: int,
     *     permission: int,
     *     working_days: int,
     *     attendance_rate: float
     * }
     */
    private function getParentChildFingerprintMonthStats(int $studentID, int $schoolID, int $year, int $month): array
    {
        $calendar = $this->buildParentFingerprintMonthCalendar($studentID, $schoolID, $year, $month);

        return $calendar['stats'];
    }

    /**
     * @return array{
     *     stats: array<string, int|float>,
     *     rows: array<int, array<int, array<string, mixed>>>,
     *     legend: array<int, array<string, string>>
     * }
     */
    private function buildParentFingerprintMonthCalendar(int $studentID, int $schoolID, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $today = Carbon::today();

        $workingDaysService = app(SchoolWorkingDaysService::class);
        $permissionService = app(StudentPermissionAttendanceService::class);

        $workingDates = $workingDaysService->getWorkingDates($schoolID, $start, $end);
        $workingDateSet = array_flip($workingDates);

        $permissionMap = $permissionService->loadApprovedStatusMap(
            $schoolID,
            $start->toDateString(),
            $end->toDateString(),
            [$studentID]
        );

        $fingerprintByDate = StudentFingerprintAttendance::where('studentID', $studentID)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->attendance_date)->toDateString());

        $stats = [
            'present' => 0,
            'absent' => 0,
            'sick' => 0,
            'permission' => 0,
            'working_days' => count($workingDates),
            'attendance_rate' => 0.0,
        ];

        $cells = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->toDateString();
            $fpRow = $fingerprintByDate[$dateStr] ?? null;
            $checkIn = $fpRow && $fpRow->check_in_time
                ? Carbon::parse($fpRow->check_in_time)->toDateTimeString()
                : null;

            $cell = $this->resolveParentFingerprintCell(
                $dateStr,
                $today,
                isset($workingDateSet[$dateStr]),
                $checkIn,
                $permissionService->statusForStudentOnDate($permissionMap, $studentID, $dateStr)
            );

            if ($cell['counts_toward_stats'] ?? false) {
                match ($cell['status']) {
                    'present' => $stats['present']++,
                    'absent' => $stats['absent']++,
                    'sick' => $stats['sick']++,
                    'permission' => $stats['permission']++,
                    default => null,
                };
            }

            $cells[] = $cell;
            $cursor->addDay();
        }

        $eligibleDays = $stats['present'] + $stats['absent'] + $stats['sick'] + $stats['permission'];
        if ($eligibleDays > 0) {
            $stats['attendance_rate'] = round(($stats['present'] / $eligibleDays) * 100, 1);
        }

        $rows = array_chunk($cells, 8);

        return [
            'stats' => $stats,
            'rows' => $rows,
            'legend' => [
                ['key' => 'present', 'label' => __('parent.present'), 'class' => 'att-cell-present', 'icon' => 'bx-check'],
                ['key' => 'absent', 'label' => __('parent.absent_fp'), 'class' => 'att-cell-absent', 'icon' => 'bx-x'],
                ['key' => 'permission', 'label' => __('parent.permission'), 'class' => 'att-cell-permission', 'icon' => 'bx-time-five'],
                ['key' => 'sick', 'label' => __('parent.sick'), 'class' => 'att-cell-sick', 'icon' => 'bx-plus-medical'],
                ['key' => 'non_school', 'label' => __('parent.non_school'), 'class' => 'att-cell-non-school', 'icon' => 'bx-calendar-x'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveParentFingerprintCell(
        string $dateStr,
        Carbon $today,
        bool $isWorkingDay,
        ?string $checkInTime,
        ?string $permissionStatus
    ): array {
        if (! $isWorkingDay) {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'non_school',
                'label' => __('parent.non_school'),
                'icon' => 'bx-calendar-x',
                'css_class' => 'att-cell-non-school',
                'counts_toward_stats' => false,
            ];
        }

        if ($permissionStatus === 'Sick') {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'sick',
                'label' => __('parent.sick'),
                'icon' => 'bx-plus-medical',
                'css_class' => 'att-cell-sick',
                'counts_toward_stats' => true,
            ];
        }

        if ($permissionStatus === 'Excused') {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'permission',
                'label' => __('parent.permission'),
                'icon' => 'bx-time-five',
                'css_class' => 'att-cell-permission',
                'counts_toward_stats' => true,
            ];
        }

        if (! empty($checkInTime)) {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'present',
                'label' => __('parent.present'),
                'icon' => 'bx-check',
                'css_class' => 'att-cell-present',
                'counts_toward_stats' => true,
            ];
        }

        if ($dateStr > $today->toDateString()) {
            return [
                'date' => $dateStr,
                'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
                'status' => 'future',
                'label' => __('parent.future'),
                'icon' => 'bx-minus',
                'css_class' => 'att-cell-future',
                'counts_toward_stats' => false,
            ];
        }

        return [
            'date' => $dateStr,
            'date_label' => $this->formatParentAttendanceDateLabel($dateStr),
            'status' => 'absent',
            'label' => __('parent.absent_fp'),
            'icon' => 'bx-x',
            'css_class' => 'att-cell-absent',
            'counts_toward_stats' => true,
        ];
    }

    private function getParentChildAttendanceData(
        int $studentID,
        int $schoolID,
        string $searchType,
        ?string $yearFilter,
        ?string $monthFilter,
        ?string $dateFilter
    ): array {
        $query = Attendance::where('studentID', $studentID)
            ->where('schoolID', $schoolID);

        if ($searchType === 'date' && !empty($dateFilter)) {
            $query->whereDate('attendance_date', $dateFilter);
        } elseif ($searchType === 'month' && !empty($yearFilter) && !empty($monthFilter)) {
            $query->whereYear('attendance_date', $yearFilter)
                ->whereMonth('attendance_date', $monthFilter);
        } elseif ($searchType === 'year' && !empty($yearFilter)) {
            $query->whereYear('attendance_date', $yearFilter);
        }

        $records = $query->orderBy('attendance_date', 'desc')->get();
        $present = $records->where('status', 'Present')->count();
        $total = $records->count();

        return [
            'records' => $records,
            'stats' => [
                'total_days' => $total,
                'present' => $present,
                'absent' => $records->where('status', 'Absent')->count(),
                'sick' => $records->where('status', 'Sick')->count(),
                'excused' => $records->where('status', 'Excused')->count(),
                'late' => $records->where('status', 'Late')->count(),
                'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * API Endpoint: Get Parent Attendance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetParentAttendance(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $studentFilter = $request->input('student', '');
            $yearFilter = $request->input('year', date('Y'));
            $monthFilter = $request->input('month', date('m'));
            $dateFilter = $request->input('date', '');
            $searchType = $request->input('search_type', 'month'); // month, year, date

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class'])
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No students found for this parent',
                    'data' => [
                        'parent' => $parent,
                        'students' => [],
                        'years' => [],
                        'overview' => null,
                        'daily_records' => []
                    ]
                ]);
            }

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            // Get available years from attendance records
            $years = Attendance::whereIn('studentID', $students->pluck('studentID'))
                ->distinct()
                ->orderBy('attendance_date', 'desc')
                ->get()
                ->pluck('attendance_date')
                ->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('Y');
                })
                ->unique()
                ->values()
                ->sort()
                ->reverse()
                ->values();

            // Initialize attendance data
            $overviewData = null;
            $dailyRecords = collect();

            // If filters are applied, get attendance data
            if ($studentFilter || $searchType) {
                $query = Attendance::whereIn('studentID', $students->pluck('studentID'))
                    ->with(['student.subclass.class']);

                // Apply student filter
                if (!empty($studentFilter)) {
                    $query->where('studentID', $studentFilter);
                }

                // Apply date filters based on search type
                if ($searchType === 'date' && !empty($dateFilter)) {
                    $query->whereDate('attendance_date', $dateFilter);
                    $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
                } elseif ($searchType === 'month' && !empty($yearFilter) && !empty($monthFilter)) {
                    $query->whereYear('attendance_date', $yearFilter)
                          ->whereMonth('attendance_date', $monthFilter);
                    $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
                } elseif ($searchType === 'year' && !empty($yearFilter)) {
                    $query->whereYear('attendance_date', $yearFilter);
                    $dailyRecords = $query->orderBy('attendance_date', 'desc')->get();
                }

                // Calculate overview statistics
                if ($dailyRecords->count() > 0) {
                    $overviewData = [
                        'total_days' => $dailyRecords->count(),
                        'present' => $dailyRecords->where('status', 'Present')->count(),
                        'absent' => $dailyRecords->where('status', 'Absent')->count(),
                        'late' => $dailyRecords->where('status', 'Late')->count(),
                        'excused' => $dailyRecords->where('status', 'Excused')->count(),
                        'attendance_rate' => $dailyRecords->count() > 0
                            ? round(($dailyRecords->where('status', 'Present')->count() / $dailyRecords->count()) * 100, 2)
                            : 0
                    ];
                }
            }

            // Format daily records
            $formattedRecords = [];
            foreach ($dailyRecords as $record) {
                $formattedRecords[] = [
                    'attendanceID' => $record->attendanceID ?? null,
                    'student' => [
                        'studentID' => $record->student->studentID ?? null,
                        'first_name' => $record->student->first_name ?? '',
                        'middle_name' => $record->student->middle_name ?? '',
                        'last_name' => $record->student->last_name ?? '',
                        'admission_number' => $record->student->admission_number ?? '',
                        'photo' => $record->student->photo ? url('userImages/' . $record->student->photo) : null,
                        'class' => $record->student->subclass && $record->student->subclass->class
                            ? $record->student->subclass->class->class_name
                            : null,
                        'subclass' => $record->student->subclass
                            ? $record->student->subclass->subclass_name
                            : null,
                    ],
                    'attendance_date' => $record->attendance_date ? \Carbon\Carbon::parse($record->attendance_date)->format('Y-m-d') : null,
                    'status' => $record->status ?? null,
                    'remark' => $record->remark ?? null,
                    'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : null,
                ];
            }

            // Group daily records by date for easier consumption
            $groupedRecords = [];
            foreach ($dailyRecords->groupBy('attendance_date') as $date => $records) {
                $dateObj = \Carbon\Carbon::parse($date);
                $firstRecord = collect($records)->first();
                $groupedRecords[] = [
                    'date' => $dateObj->format('Y-m-d'),
                    'date_formatted' => $dateObj->format('l, F d, Y'),
                    'status' => $firstRecord->status ?? null,
                    'remark' => $firstRecord->remark ?? null,
                    'students' => collect($records)->map(function($rec) {
                        return [
                            'studentID' => $rec->student->studentID ?? null,
                            'first_name' => $rec->student->first_name ?? '',
                            'middle_name' => $rec->student->middle_name ?? '',
                            'last_name' => $rec->student->last_name ?? '',
                            'admission_number' => $rec->student->admission_number ?? '',
                            'photo' => $rec->student->photo ? url('userImages/' . $rec->student->photo) : null,
                            'status' => $rec->status ?? null,
                            'remark' => $rec->remark ?? null,
                        ];
                    })->toArray()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance records retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                        'school_type' => $school->school_type ?? 'Secondary',
                    ],
                    'students' => $students->map(function($student) {
                        return [
                            'studentID' => $student->studentID,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name ?? '',
                            'last_name' => $student->last_name,
                            'admission_number' => $student->admission_number ?? '',
                            'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                            'class' => $student->subclass && $student->subclass->class
                                ? $student->subclass->class->class_name
                                : null,
                            'subclass' => $student->subclass
                                ? $student->subclass->subclass_name
                                : null,
                        ];
                    }),
                    'years' => $years->toArray(),
                    'filters' => [
                        'student' => $studentFilter,
                        'year' => $yearFilter,
                        'month' => $monthFilter,
                        'date' => $dateFilter,
                        'search_type' => $searchType,
                    ],
                    'overview' => $overviewData,
                    'daily_records' => $formattedRecords,
                    'grouped_records' => $groupedRecords,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Endpoint: Get Parent Results
     *
     * This endpoint returns all results for a parent's children including:
     * - Parent details
     * - Students list
     * - Examinations list
     * - Available years and terms
     * - Results grouped by exam and student (for exam type)
     * - Term reports with all exams (for report type)
     * - Statistics (total marks, average, position, division/grade)
     * - Subject-wise results with grades/divisions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Request Parameters (Query or Body):
     * - parentID: integer (required) - Parent ID
     * - schoolID: integer (required) - School ID
     * - student: integer (required) - Filter by student ID
     * - year: string (required) - Filter by year
     * - term: string (required) - Filter by term (first_term, second_term, third_term)
     * - type: string (required) - Type of results: "exam" or "report"
     * - exam: integer (required if type="exam") - Filter by exam ID
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Results retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "students": [...],
     *     "examinations": [...],
     *     "years": [...],
     *     "terms": [...],
     *     "filters": {...},
     *     "results": [...]
     *   }
     * }
     *
     * Error Response (400/404/500):
     * {
     *   "success": false,
     *   "message": "Error message"
     * }
     */
    public function apiGetParentResults(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $studentFilter = $request->input('student', '');
            $yearFilter = $request->input('year', '');
            $termFilter = $request->input('term', '');
            $typeFilter = $request->input('type', 'exam'); // 'exam' or 'report'
            $examFilter = $request->input('exam', '');
            $academicYearID = (int) $request->input('academic_yearID', 0);

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->with(['subclass.class'])
                ->get();

            $yearService = app(SelectedAcademicYearService::class);
            $ayMode = $this->parentAcademicYearContext((int) $schoolID)['mode'] ?? 'none';
            $students = $students->map(function ($student) use ($yearService, $ayMode) {
                $className = $student->subclass->class->class_name ?? '';
                $isA = $yearService->isALevelClassName($className);
                $student->setAttribute('class_name', $className);
                $student->setAttribute('is_a_level', $isA);
                if ($ayMode === 'primary') {
                    $levelLabel = $className ?: 'Primary';
                } elseif ($ayMode === 'secondary_dual' || $ayMode === 'secondary_o_only') {
                    $levelLabel = ($isA ? 'A Level' : 'O Level').($className ? ' · '.$className : '');
                } else {
                    $levelLabel = $className ?: '-';
                }
                $student->setAttribute('level_label', $levelLabel);

                return $student;
            });

            // Same academic-year cards the website shows (filtered by child when provided).
            $ayContext = $this->parentAcademicYearContext(
                (int) $schoolID,
                !empty($studentFilter) ? (int) $studentFilter : null
            );
            $academicYears = $ayContext['options'];
            $years = collect($academicYears)->pluck('calendar_year')->unique()->values();

            if ($academicYearID > 0) {
                $ay = AcademicYear::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->first();
                if ($ay) {
                    $cals = $this->extractCalendarYearsFromAcademicYear($ay);
                    if (!empty($cals) && $yearFilter === '') {
                        $yearFilter = (string) $cals[0];
                    }
                }
            }

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No students found for this parent',
                    'data' => [
                        'parent' => [
                            'parentID' => $parent->parentID,
                            'first_name' => $parent->first_name ?? '',
                            'middle_name' => $parent->middle_name ?? '',
                            'last_name' => $parent->last_name ?? '',
                        ],
                        'school' => null,
                        'students' => [],
                        'examinations' => [],
                        'years' => [],
                        'academic_years' => [],
                        'filters' => [
                            'student' => $studentFilter,
                            'year' => $yearFilter,
                            'academic_yearID' => $academicYearID ?: null,
                            'exam' => $examFilter,
                        ],
                        'statistics' => [
                            'total_students' => 0,
                            'total_examinations' => 0,
                            'total_results' => 0,
                        ],
                        'results' => []
                    ]
                ]);
            }

            // Build query for results - only allowed/approved results released to parents
            $query = Result::whereIn('studentID', $students->pluck('studentID'))
                ->whereIn('status', ['allowed', 'approved'])
                ->where('parent_status', 'enabled')
                ->with(['student.subclass.class', 'examination', 'classSubject.subject']);

            // Apply filters
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            if ($academicYearID > 0) {
                $ayIds = [$academicYearID];
                $calendarYears = [];
                $ay = AcademicYear::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->first();
                if ($ay) {
                    $calendarYears = $this->extractCalendarYearsFromAcademicYear($ay);
                }
                $query->whereHas('examination', function ($q) use ($ayIds, $calendarYears) {
                    $this->applyParentExaminationAcademicYearFilter($q, $ayIds, $calendarYears);
                });
            } elseif (!empty($yearFilter)) {
                $query->whereHas('examination', function($q) use ($yearFilter) {
                    $q->where('year', $yearFilter);
                });
            }

            if (!empty($termFilter)) {
                $query->whereHas('examination', function($q) use ($termFilter) {
                    $q->where('term', $termFilter);
                });
            }

            // For exam type, filter by specific exam
            if ($typeFilter === 'exam' && !empty($examFilter)) {
                $query->where('examID', $examFilter);
            }

            // For report type, get all exams in the term
            if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
                $examIDs = Examination::where('schoolID', $schoolID)
                    ->where('year', $yearFilter)
                    ->where('term', $termFilter)
                    ->where('approval_status', 'Approved')
                    ->pluck('examID');
                $query->whereIn('examID', $examIDs);
            }

            // Get results ordered by exam date and student
            $results = $query->orderBy('created_at', 'desc')->get();

            // Examinations for the picker — same rules as the parent website
            $examinationsQuery = Examination::where('schoolID', $schoolID)
                ->where('approval_status', 'Approved')
                ->whereHas('results', function($q) use ($students) {
                    $q->whereIn('studentID', $students->pluck('studentID'))
                      ->whereIn('status', ['allowed', 'approved'])
                      ->where('parent_status', 'enabled');
                });

            if ($academicYearID > 0) {
                $ayIds = [$academicYearID];
                $calendarYears = [];
                $ay = AcademicYear::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->first();
                if ($ay) {
                    $calendarYears = $this->extractCalendarYearsFromAcademicYear($ay);
                }
                $this->applyParentExaminationAcademicYearFilter($examinationsQuery, $ayIds, $calendarYears);
            } elseif (!empty($yearFilter)) {
                $examinationsQuery->where('year', $yearFilter);
            }

            if (!empty($termFilter)) {
                $examinationsQuery->where('term', $termFilter);
            }

            $examinations = $examinationsQuery->orderBy('year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get();

            // Terms shown on the website results wizard
            $terms = collect(['first_term', 'second_term']);

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            // Process results based on type (exam or report) - same logic as parentResults
            $processedResults = [];

            if ($typeFilter === 'report' && !empty($termFilter) && !empty($yearFilter)) {
                // For report: Group by student and calculate term averages
                $studentResults = $results->groupBy('studentID');

                foreach ($studentResults as $studentID => $studentResultList) {
                    $student = $studentResultList->first()->student ?? null;
                    if (!$student) continue;
                    $className = $student->subclass && $student->subclass->class
                        ? $student->subclass->class->class_name
                        : null;

                    // Group results by exam
                    $examResults = $studentResultList->groupBy('examID');
                    $examsData = [];
                    $subjectData = [];

                    foreach ($examResults as $examID => $examResultList) {
                        $exam = $examResultList->first()->examination ?? null;
                        if (!$exam) continue;

                        $examTotalMarks = 0;
                        $examSubjectCount = 0;

                        foreach ($examResultList as $result) {
                            if ($result->marks !== null && $result->marks !== '') {
                                $examTotalMarks += (float)$result->marks;
                                $examSubjectCount++;
                            }

                            $subjectName = $result->classSubject->subject->subject_name ?? 'N/A';
                            if (!isset($subjectData[$subjectName])) {
                                $subjectData[$subjectName] = [
                                    'subject_name' => $subjectName,
                                    'exams' => []
                                ];
                            }

                            $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $school->school_type ?? 'Secondary', $className);
                            $subjectData[$subjectName]['exams'][$examID] = [
                                'marks' => $result->marks,
                                'grade' => $result->grade ?? $gradeOrDivision['grade'],
                                'exam_name' => $exam->exam_name
                            ];
                        }

                        $examAverage = $examSubjectCount > 0 ? $examTotalMarks / $examSubjectCount : 0;
                        $examGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $examAverage);

                        $examsData[] = [
                            'exam' => $exam,
                            'average' => $examAverage,
                            'grade' => $examGradeOrDiv['grade'] ?? null,
                            'division' => $examGradeOrDiv['division'] ?? null
                        ];
                    }

                    // Calculate overall average and grade
                    $overallTotalMarks = 0;
                    $overallSubjectCount = 0;
                    foreach ($subjectData as $subjectName => $data) {
                        $subjectMarks = [];
                        foreach ($data['exams'] as $examID => $examData) {
                            if ($examData['marks'] !== null && $examData['marks'] !== '') {
                                $subjectMarks[] = (float)$examData['marks'];
                            }
                        }
                        if (!empty($subjectMarks)) {
                            $overallTotalMarks += array_sum($subjectMarks);
                            $overallSubjectCount += count($subjectMarks);
                        }
                    }

                    $overallAverage = $overallSubjectCount > 0 ? $overallTotalMarks / $overallSubjectCount : 0;
                    $overallGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $overallAverage);

                    // Calculate subjects with averages
                    $subjects = [];
                    foreach ($subjectData as $subjectName => $data) {
                        $subjectMarks = [];
                        foreach ($data['exams'] as $examID => $examData) {
                            if ($examData['marks'] !== null && $examData['marks'] !== '') {
                                $subjectMarks[] = (float)$examData['marks'];
                            }
                        }
                        $subjectAverage = !empty($subjectMarks) ? array_sum($subjectMarks) / count($subjectMarks) : 0;
                        $subjectGradeOrDiv = $this->calculateTotalDivision(0, $school->school_type ?? 'Secondary', $className, $subjectAverage);

                        $subjects[] = [
                            'subject_name' => $subjectName,
                            'exams' => array_values($data['exams']),
                            'average' => $subjectAverage,
                            'grade' => $subjectGradeOrDiv['grade'] ?? null
                        ];
                    }

                    $processedResults[] = [
                        'type' => 'report',
                        'student' => $student,
                        'term' => $termFilter,
                        'year' => $yearFilter,
                        'exams' => $examsData,
                        'subjects' => $subjects,
                        'overall_average' => $overallAverage,
                        'overall_grade' => $overallGradeOrDiv['grade'] ?? null,
                        'overall_division' => $overallGradeOrDiv['division'] ?? null
                    ];
                }
            } else {
                // For exam: Group by exam and student (existing logic)
            foreach($results as $result) {
                $examKey = $result->examID . '_' . $result->studentID;
                    if(!isset($processedResults[$examKey])) {
                        $processedResults[$examKey] = [
                            'type' => 'exam',
                        'exam' => $result->examination,
                        'student' => $result->student,
                        'results' => []
                    ];
                }
                    $processedResults[$examKey]['results'][] = $result;
                }
            }

            // Calculate divisions for exam results (existing logic)
            $resultsWithDivisions = [];
            foreach($processedResults as $key => $group) {
                if ($group['type'] === 'exam') {
                    $examKey = $key;
                    if(!isset($resultsWithDivisions[$examKey])) {
                        $resultsWithDivisions[$examKey] = [
                            'exam' => $group['exam'],
                            'student' => $group['student'],
                            'results' => $group['results']
                        ];
                    }
                } else {
                    // For report, use different key
                    $reportKey = 'report_' . $group['student']->studentID . '_' . $group['term'] . '_' . $group['year'];
                    $resultsWithDivisions[$reportKey] = $group;
                }
            }

            // Calculate total division for each group (only for exam type)
            foreach($resultsWithDivisions as $key => $group) {
                // Skip calculation for report type (already calculated)
                if (isset($group['type']) && $group['type'] === 'report') {
                    continue;
                }

                $student = $group['student'];
                $examID = $group['exam']->examID ?? null;
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;

                $subjectsData = [];
                $totalMarks = 0;
                $subjectCount = 0;

                foreach($group['results'] as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float)$result->marks;
                        $subjectCount++;
                    }
                    $gradeOrDivision = $this->calculateGradeOrDivision($result->marks, $school->school_type ?? 'Secondary', $className);
                    $subjectsData[] = [
                        'marks' => $result->marks,
                        'points' => $gradeOrDivision['points'] ?? null,
                        'grade' => $gradeOrDivision['grade'] ?? null,
                        'division' => $gradeOrDivision['division'] ?? null
                    ];
                }

                // Calculate total points
                $subjectPoints = array_filter(array_column($subjectsData, 'points'), function($p) { return $p !== null; });
                $totalPoints = 0;

                if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                    if (count($subjectPoints) > 0) {
                        sort($subjectPoints);
                        $bestSeven = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
                        $totalPoints = array_sum($bestSeven);
                    }
                } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                    if (count($subjectPoints) > 0) {
                        rsort($subjectPoints);
                        $bestThree = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
                        $totalPoints = array_sum($bestThree);
                    }
                }

                $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
                $totalDivision = $this->calculateTotalDivision($totalPoints, $school->school_type ?? 'Secondary', $className, $averageMarks);

                // Calculate position - Get all students who took this exam in the same class/subclass
                $position = null;
                $totalStudentsInExam = 0;

                if ($examID && $student->subclassID) {
                    // Get all students in the same subclass who took this exam
                    $subclassStudents = Student::where('subclassID', $student->subclassID)
                        ->where('status', 'Active')
                        ->pluck('studentID');

                    // Get all results for this exam in this subclass
                    $allSubclassResults = Result::where('examID', $examID)
                        ->whereIn('studentID', $subclassStudents)
                        ->where('status', 'approved')
                        ->with('classSubject')
                        ->get();

                    // Group by student and calculate totals
                    $studentTotals = [];
                    foreach ($allSubclassResults as $subResult) {
                        $subStudentID = $subResult->studentID;
                        if (!isset($studentTotals[$subStudentID])) {
                            $studentTotals[$subStudentID] = [
                                'total_marks' => 0,
                                'total_points' => 0,
                                'subject_count' => 0
                            ];
                        }

                        if ($subResult->marks !== null && $subResult->marks !== '') {
                            $studentTotals[$subStudentID]['total_marks'] += (float)$subResult->marks;
                            $studentTotals[$subStudentID]['subject_count']++;
                        }

                        $subGradeOrDiv = $this->calculateGradeOrDivision($subResult->marks, $school->school_type ?? 'Secondary', $className);
                        if ($subGradeOrDiv['points'] !== null) {
                            $studentTotals[$subStudentID]['total_points'] += $subGradeOrDiv['points'];
                        }
                    }

                    // Calculate average marks and final points for each student
                    $finalScores = [];
                    foreach ($studentTotals as $subStudentID => $totals) {
                        $avgMarks = $totals['subject_count'] > 0 ? $totals['total_marks'] / $totals['subject_count'] : 0;

                        // Calculate final points (same logic as above)
                        $subFinalPoints = 0;
                        if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                            $subFinalPoints = $totals['total_points'];
                        } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                            $subFinalPoints = $totals['total_points'];
                        }

                        $finalScores[$subStudentID] = [
                            'total_marks' => $totals['total_marks'],
                            'average_marks' => $avgMarks,
                            'total_points' => $subFinalPoints
                        ];
                    }

                    // Sort by appropriate criteria
                    if ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four'])) {
                        // O-Level: Lower points is better
                        uasort($finalScores, function($a, $b) {
                            return ($a['total_points'] ?? 999) <=> ($b['total_points'] ?? 999);
                        });
                    } elseif ($school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_five', 'form_six'])) {
                        // A-Level: Higher points is better
                        uasort($finalScores, function($a, $b) {
                            return ($b['total_points'] ?? 0) <=> ($a['total_points'] ?? 0);
                        });
                    } else {
                        // Primary or other: Higher marks is better
                        uasort($finalScores, function($a, $b) {
                            return ($b['average_marks'] ?? 0) <=> ($a['average_marks'] ?? 0);
                        });
                    }

                    // Find position
                    $currentPos = 1;
                    $prevValue = null;
                    $totalStudentsInExam = count($finalScores);

                    foreach ($finalScores as $subStudentID => $score) {
                        $currentValue = $school->school_type === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])
                            ? $score['total_points']
                            : $score['average_marks'];

                        if ($prevValue !== null && abs($currentValue - $prevValue) > 0.01) {
                            $currentPos++;
                        }

                        if ($subStudentID == $student->studentID) {
                            $position = $currentPos;
                            break;
                        }

                        $prevValue = $currentValue;
                    }
                }

                $resultsWithDivisions[$key]['total_division'] = $totalDivision['division'] ?? null;
                $resultsWithDivisions[$key]['total_grade'] = $totalDivision['grade'] ?? null;
                $resultsWithDivisions[$key]['total_points'] = $totalPoints;
                $resultsWithDivisions[$key]['average_marks'] = $averageMarks;
                $resultsWithDivisions[$key]['subjects_data'] = $subjectsData;
                $resultsWithDivisions[$key]['position'] = $position;
                $resultsWithDivisions[$key]['total_students'] = $totalStudentsInExam;
            }

            // Format results for API response
            $formattedResults = [];
            foreach($resultsWithDivisions as $group) {
                $student = $group['student'];
                $exam = $group['exam'];
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : null;

                $isSecondaryWithDivision = ($school->school_type ?? 'Secondary') === 'Secondary' &&
                    in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six']);
                $displayLabel = ($school->school_type ?? 'Secondary') === 'Primary' ? 'Division' :
                    ($isSecondaryWithDivision ? 'Division' : 'Grade');

                $subjectResults = [];
                foreach($group['results'] as $index => $result) {
                    $subjectData = $group['subjects_data'][$index] ?? null;
                    $displayGradeOrDivision = '';

                    if($subjectData) {
                        if(($school->school_type ?? 'Secondary') === 'Primary') {
                            $displayGradeOrDivision = $subjectData['division'] ?? '-';
                        } else {
                            $displayGradeOrDivision = $subjectData['grade'] ?? '-';
                        }
                    } else {
                        $displayGradeOrDivision = $result->grade ?? '-';
                    }

                    $subjectResults[] = [
                        'resultID' => $result->resultID ?? null,
                        'subject' => [
                            'subjectID' => $result->classSubject && $result->classSubject->subject
                                ? $result->classSubject->subject->subjectID
                                : null,
                            'subject_name' => $result->classSubject && $result->classSubject->subject
                                ? $result->classSubject->subject->subject_name
                                : 'N/A',
                        ],
                        'marks' => $result->marks ?? null,
                        'grade' => $displayGradeOrDivision !== '-' ? $displayGradeOrDivision : null,
                        'division' => ($school->school_type ?? 'Secondary') === 'Primary' && $displayGradeOrDivision !== '-' ? $displayGradeOrDivision : null,
                        'points' => $subjectData['points'] ?? null,
                        'remark' => $result->remark ?? null,
                    ];
                }

                $formattedResults[] = [
                    'exam' => [
                        'examID' => $exam->examID ?? null,
                        'exam_name' => $exam->exam_name ?? 'N/A',
                        'year' => $exam->year ?? 'N/A',
                        'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                        'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                    ],
                    'student' => [
                        'studentID' => $student->studentID ?? null,
                        'first_name' => $student->first_name ?? '',
                        'middle_name' => $student->middle_name ?? '',
                        'last_name' => $student->last_name ?? '',
                        'admission_number' => $student->admission_number ?? '',
                        'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                        'class' => $student->subclass && $student->subclass->class
                            ? $student->subclass->class->class_name
                            : null,
                        'subclass' => $student->subclass
                            ? $student->subclass->subclass_name
                            : null,
                    ],
                    'summary' => [
                        'subject_count' => count($group['results']),
                        'total_marks' => array_sum(array_map(function($r) { return (float)($r->marks ?? 0); }, $group['results'])),
                        'average_marks' => round($group['average_marks'] ?? 0, 1),
                        'total_points' => $group['total_points'] ?? 0,
                        'total_division' => $group['total_division'] ?? null,
                        'total_grade' => $group['total_grade'] ?? null,
                        'display_label' => $displayLabel,
                        'position' => $group['position'] ?? null,
                        'total_students' => $group['total_students'] ?? 0,
                    ],
                    'subjects' => $subjectResults,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Results retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                        'school_type' => $school->school_type ?? 'Secondary',
                    ],
                    'students' => $students->map(function($student) {
                        return [
                            'studentID' => $student->studentID,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name ?? '',
                            'last_name' => $student->last_name,
                            'admission_number' => $student->admission_number ?? '',
                            'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                            'class' => $student->subclass && $student->subclass->class
                                ? $student->subclass->class->class_name
                                : null,
                            'subclass' => $student->subclass
                                ? $student->subclass->subclass_name
                                : null,
                            'is_a_level' => (bool) ($student->is_a_level ?? false),
                            'level_label' => $student->level_label ?? null,
                        ];
                    })->values(),
                    'examinations' => $examinations->map(function($exam) {
                        return [
                            'examID' => $exam->examID,
                            'exam_name' => $exam->exam_name ?? '',
                            'year' => $exam->year ?? '',
                            'academic_yearID' => $exam->academic_yearID ?? null,
                            'term' => $exam->term ?? null,
                            'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                            'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                        ];
                    })->values(),
                    // Same year cards as the website picker (not expanded slash calendars).
                    'years' => $years->values()->toArray(),
                    'academic_years' => $academicYears,
                    'academic_year_mode' => $ayContext['mode'] ?? $ayMode,
                    'terms' => $terms->values()->toArray(),
                    'filters' => [
                        'student' => $studentFilter,
                        'year' => $yearFilter,
                        'academic_yearID' => $academicYearID ?: null,
                        'term' => $termFilter,
                        'type' => $typeFilter,
                        'exam' => $examFilter,
                    ],
                    'statistics' => [
                        'total_students' => $students->count(),
                        'total_examinations' => $examinations->count(),
                        'total_results' => count($formattedResults),
                    ],
                    'results' => $formattedResults,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving results: ' . $e->getMessage()
            ], 500);
        }
    }

    public function parentPayments()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Get available years from payments
        $availableYears = Payment::whereIn('studentID', $students->pluck('studentID'))
            ->where('schoolID', $schoolID)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Get current year
        $currentYear = date('Y');

        // Add current year if not in list
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
        }

        // Add past years (last 10 years) to show historical data option
        for ($i = 1; $i <= 10; $i++) {
            $pastYear = $currentYear - $i;
            if (!in_array($pastYear, $availableYears)) {
                $availableYears[] = $pastYear;
            }
        }

        // Sort years in descending order (newest first)
        rsort($availableYears);

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        return view('Parents.payments', compact('parent', 'students', 'school', 'availableYears', 'currentYear'));
    }

    public function get_parent_payments_ajax(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Get filter parameters
            $search = $request->input('search', '');
            $year = $request->input('year', date('Y'));
            $studentFilter = $request->input('student', '');
            $feeType = $request->input('fee_type', '');

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json(['success' => false, 'message' => 'Parent not found'], 404);
            }

            // Get all students of this parent
            $query = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['subclass.class', 'payments' => function($q) use ($year) {
                    $q->whereYear('created_at', $year);
                }, 'payments.fee']);

            // Filter by student if provided
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            // Search by student name or admission number
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('admission_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('middle_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->orderBy('first_name')->get();

            // Filter payments based on criteria
            $filteredData = [];
            $index = 1;

            foreach ($students as $student) {
                foreach ($student->payments as $payment) {
                    // Filter by year (already filtered in query, but double-check)
                    if ($year && date('Y', strtotime($payment->created_at)) != $year) {
                        continue;
                    }

                    // Fee type filter is deprecated since unified fees, skipping check
                    // if (!empty($feeType) && $payment->fee_type !== $feeType) {
                    //     continue;
                    // }

                    // Format payment data for JSON
                    $paymentData = [
                        'paymentID' => $payment->paymentID,
                        'control_number' => $payment->control_number,
                        'amount_required' => (float) $payment->amount_required,
                        'amount_paid' => (float) $payment->amount_paid,
                        'balance' => (float) $payment->balance,
                        'payment_status' => $payment->payment_status,
                        'payment_date' => $payment->payment_date ? $payment->payment_date->toDateTimeString() : null,
                        'payment_reference' => $payment->payment_reference,
                        'notes' => $payment->notes,
                    ];

                    // Format student data for JSON
                    $studentImgPath = $student->photo
                        ? asset('userImages/' . $student->photo)
                        : ($student->gender == 'Female'
                            ? asset('images/female.png')
                            : asset('images/male.png'));

                    $studentData = [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'photo' => $studentImgPath,
                        'subclass' => $student->subclass ? [
                            'subclass_name' => $student->subclass->subclass_name,
                            'class' => $student->subclass->class ? [
                                'class_name' => $student->subclass->class->class_name,
                            ] : null,
                        ] : null,
                    ];

                    // Add to filtered data
                    $filteredData[] = [
                        'index' => $index++,
                        'student' => $studentData,
                        'payment' => $paymentData
                    ];
                }
            }

            // Calculate statistics
            $totalPayments = count($filteredData);
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $tuitionFeesTotal = 0;
            $otherFeesTotal = 0;

            foreach ($filteredData as $item) {
                if ($item['payment']) {
                    $payment = $item['payment'];
                    $totalRequired += $payment['amount_required'] ?? 0;
                    $totalPaid += $payment['amount_paid'] ?? 0;
                    $totalBalance += $payment['balance'] ?? 0;

                    // Fee type is deprecated, aggregating all as Tuition/Total
                    $tuitionFeesTotal += $payment['amount_required'] ?? 0;

                    if ($payment['payment_status'] === 'Pending') {
                        $pendingPayments++;
                    } elseif ($payment['payment_status'] === 'Incomplete Payment' || $payment['payment_status'] === 'Partial') {
                        $incompletePayments++;
                    } elseif ($payment['payment_status'] === 'Paid') {
                        $paidPayments++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'statistics' => [
                    'total_payments' => $totalPayments,
                    'pending_payments' => $pendingPayments,
                    'incomplete_payments' => $incompletePayments,
                    'paid_payments' => $paidPayments,
                    'total_required' => $totalRequired,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'tuition_fees_total' => $tuitionFeesTotal,
                    'other_fees_total' => $otherFeesTotal,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request control number for a specific student
     */
    public function request_control_number(Request $request, $studentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Verify student belongs to this parent (check without status first to give better error message)
            $student = Student::where('studentID', $studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found or does not belong to you'
                ], 404);
            }

            // Check if student is active
            if ($student->status !== 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not active (Status: ' . $student->status . '). Only active students can request control numbers.'
                ], 400);
            }

            DB::beginTransaction();

            $generated = 0;
            $messages = [];

            // Get classID from subclass (fees are assigned to class, not subclass)
            $classID = $student->subclass && $student->subclass->class
                ? $student->subclass->class->classID
                : null;

            $academicYearID = $this->getCurrentAcademicYearID($schoolID);
            if (!$academicYearID) {
                return response()->json(['success' => false, 'message' => 'Hakuna mwaka wa masomo uliowekwa.'], 404);
            }

            // Unified Control Number Logic
            $existingPayment = Payment::where('studentID', $student->studentID)
                ->where('academic_yearID', $academicYearID)
                ->whereNotNull('control_number')
                ->where('control_number', '!=', '')
                ->first();
            $alreadyGenerated = (bool) $existingPayment;

            $controlNumber = $this->generateSingleStudentBill($student, $academicYearID, $schoolID);

            DB::commit();

            if ($alreadyGenerated) {
                return response()->json([
                    'success' => true,
                    'already_generated' => true,
                    'generated' => 0,
                    'message' => 'Control number already generated.',
                    'message_sw' => 'Control number tayari imezalishwa.',
                    'control_number' => $controlNumber ?: $existingPayment->control_number,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'already_generated' => false,
                'message' => 'Control number imezalishwa kikamilifu.',
                'generated' => 1,
                'control_number' => $controlNumber,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating control numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique control number
     */
    private function generateControlNumber($schoolID, $studentID)
    {
        do {
            // Generate 5 random digits (0-9)
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Format: 3345 + 5 random digits = 9 digits total
            $controlNumber = '3345' . $randomDigits;

            // Check if control number already exists in active payments
            $existsActive = DB::table('payments')->where('control_number', $controlNumber)->exists();

            // Check if control number exists in history
            $existsHistory = DB::table('payments_history')->where('control_number', $controlNumber)->exists();

        } while ($existsActive || $existsHistory);

        return $controlNumber;
    }

    /**
     * Send SMS to parent with unified control number
     */
    private function sendControlNumberSMS($payment, $student, $schoolID)
    {
        try {
            $payment->load(['student.parent', 'school']);

            if (!$payment->student || !$payment->student->parent || !$payment->student->parent->phone) {
                return false;
            }

            $parent = $payment->student->parent;
            $school = $payment->school ?? School::find($schoolID);

            $studentName = trim($student->first_name . ' ' . $student->last_name);
            $controlNumber = $payment->control_number;
            $totalAmount = number_format($payment->amount_required, 0);
            $requiredAmount = number_format($payment->required_fees_amount, 0);

            // Kiswahili Message (Unified)
            $amount = number_format($payment->amount_required, 0);
            $school = School::find($schoolID);
            $schoolName = $school->school_name ?? 'School';
            $prefix = ($school && $school->senderID) ? "" : "{$schoolName}: ";
            $message = "{$prefix}Mzazi wa {$student->first_name}, Control Number ya Ada ni {$payment->control_number}. Jumla Sh {$amount}. Ahsante.";

            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Error sending control number SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unified student bill generator for ParentsController
     */
    private function generateSingleStudentBill($student, $academicYearID, $schoolID)
    {
        $fees = Fee::where('schoolID', $schoolID)
            ->where('classID', $student->subclass->classID)
            ->where('status', 'Active')
            ->get();

        $totalAmount = $fees->sum('amount');
        $mustPayAmount = $fees->where('must_start_pay', true)->sum('amount');

        $previousYearBalance = $this->getPreviousYearBalance($student->studentID, $schoolID);
        $debt = $previousYearBalance['school_fee_balance'] + $previousYearBalance['other_contribution_balance'];

        $totalRequired = $totalAmount + $debt;

        $payment = Payment::where('studentID', $student->studentID)
            ->where('academic_yearID', $academicYearID)
            ->first();

        $isNew = false;
        if (!$payment) {
            $school = School::find($schoolID);
            $controlNumber = $this->generateControlNumber($schoolID, $student->studentID);

            $payment = Payment::create([
                'schoolID' => $schoolID,
                'academic_yearID' => $academicYearID,
                'studentID' => $student->studentID,
                'control_number' => $controlNumber,
                'amount_required' => $totalRequired,
                'amount_paid' => 0,
                'balance' => $totalRequired,
                'debt' => $debt,
                'required_fees_amount' => $mustPayAmount,
                'required_fees_paid' => 0,
                'can_start_school' => $mustPayAmount <= 0,
                'payment_status' => 'Pending'
            ]);
            $isNew = true;
        } else {
            $payment->update([
                'amount_required' => $totalRequired,
                'balance' => $totalRequired - $payment->amount_paid,
                'debt' => $debt,
                'required_fees_amount' => $mustPayAmount,
            ]);
        }

        // SMS only when a new control number is created (not on repeat taps).
        if ($isNew) {
            $this->sendControlNumberSMS($payment, $student, $schoolID);
        }

        return $payment->control_number;
    }

    private function getCurrentAcademicYearID($schoolID)
    {
        $active = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();
        return $active ? $active->academic_yearID : null;
    }

    private function getPreviousYearBalance($studentID, $schoolID)
    {
        // Get the most recent closed academic year
        $closed = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Closed')
            ->orderBy('year', 'desc')
            ->first();

        if (!$closed) {
            return ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
        }

        // Get payments from history
        $previousYearPayments = DB::table('payments_history')
            ->where('studentID', $studentID)
            ->where('academic_yearID', $closed->academic_yearID)
            ->get();

        $schoolFeeBalance = 0;
        $otherContributionBalance = 0;

        foreach ($previousYearPayments as $payment) {
            $balance = (float) $payment->balance;

            // Only count positive balances as debt
            if ($balance > 0) {
                 $schoolFeeBalance += $balance;
            }
        }

        return [
            'school_fee_balance' => max(0, $schoolFeeBalance),
            'other_contribution_balance' => max(0, $otherContributionBalance)
        ];
    }

    /**
     * Change language
     */
    public function changeLanguage(Request $request)
    {
        $locale = $request->input('locale', $request->json('locale', 'en'));

        // Validate locale
        if (! in_array($locale, ['en', 'sw'], true)) {
            $locale = 'en';
        }

        // Persist for all portals (parent, school, super admin)
        Session::put('locale', $locale);
        Session::save();
        app()->setLocale($locale);

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'message' => __('superadmin.lang_changed'),
        ]);
    }

    public function parentFeesSummary()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all students of this parent
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->with(['subclass.class'])
            ->get();

        // Period choices = academic-year cards (same as results), not calendar years
        $period = $this->feesSummaryPeriod((int) $schoolID, request()->input('academic_yearID'), request()->input('year'));
        $academicYears = $period['options'];
        $selectedAcademicYearID = $period['selected']['academic_yearID'] ?? null;

        // Get school details
        $school = School::where('schoolID', $schoolID)->first();

        return view('Parents.feesSummary', compact('parent', 'students', 'school', 'academicYears', 'selectedAcademicYearID'));
    }

    /**
     * Get fees summary for all students by class
     */
    public function get_fees_summary_ajax(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $period = $this->feesSummaryPeriod((int) $schoolID, $request->input('academic_yearID'), $request->input('year'));

            // Get all students of this parent with subclass and class relationships
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with([
                    'subclass.class',
                    'payments' => function($q) use ($period) {
                        $this->applyFeesPeriodFilter($q, $period['selected']);
                    }
                ])
                ->get();

            $summary = [];

            foreach ($students as $student) {
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : 'N/A';

                // Get classID from subclass (fees are assigned to class, not subclass)
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;

                // Get fees for this student's class with relationships
                $classFees = Fee::where('classID', $classID)
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->with(['installments' => function($q) {
                        $q->where('status', 'Active');
                    }, 'otherFeeDetails' => function($q) {
                        $q->where('status', 'Active');
                    }])
                    ->get();

                $tuitionFees = $classFees->where('fee_type', 'Tuition Fees');
                $otherFees = $classFees->where('fee_type', 'Other Fees');

                // Get payments for this student
                $tuitionPayment = $student->payments->where('fee_type', 'Tuition Fees')->first();
                $otherPayment = $student->payments->where('fee_type', 'Other Fees')->first();

                // Calculate totals
                $tuitionRequired = $tuitionFees->sum('amount');
                $otherRequired = $otherFees->sum('amount');
                $totalRequired = $tuitionRequired + $otherRequired;

                $tuitionPaid = $tuitionPayment ? (float) $tuitionPayment->amount_paid : 0;
                $otherPaid = $otherPayment ? (float) $otherPayment->amount_paid : 0;
                $totalPaid = $tuitionPaid + $otherPaid;

                $tuitionBalance = $tuitionRequired - $tuitionPaid;
                $otherBalance = $otherRequired - $otherPaid;
                $totalBalance = $totalRequired - $totalPaid;

                // Get installments for tuition fees
                $tuitionInstallments = collect();
                $tuitionAllowPartial = false;
                foreach ($tuitionFees as $fee) {
                    // Reload fee with installments if not already loaded
                    if (method_exists($fee, 'relationLoaded') && !$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }

                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $tuitionInstallments = $tuitionInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $tuitionAllowPartial = true;
                        }
                    }
                }

                // Get installments and other fees details for other fees
                $otherInstallments = collect();
                $otherAllowPartial = false;
                $otherFeesDetails = collect();
                foreach ($otherFees as $fee) {
                    // Reload fee with installments and otherFeeDetails if not already loaded
                    if (method_exists($fee, 'relationLoaded') && !$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    if (method_exists($fee, 'relationLoaded') && !$fee->relationLoaded('otherFeeDetails')) {
                        $fee->load(['otherFeeDetails' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }

                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $otherInstallments = $otherInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $otherAllowPartial = true;
                        }
                    }
                    if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                        $otherFeesDetails = $otherFeesDetails->merge($fee->otherFeeDetails);
                    }
                }

                $summary[] = [
                    'studentID' => $student->studentID,
                    'student_name' => ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''),
                    'admission_number' => $student->admission_number ?? 'N/A',
                    'class' => $className,
                    'tuition_fees' => [
                        'required' => $tuitionRequired,
                        'paid' => $tuitionPaid,
                        'balance' => $tuitionBalance,
                        'control_number' => $tuitionPayment ? $tuitionPayment->control_number : null,
                        'status' => $tuitionPayment ? $tuitionPayment->payment_status : 'No Payment',
                        'installments' => $tuitionInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $tuitionAllowPartial
                    ],
                    'other_fees' => [
                        'required' => $otherRequired,
                        'paid' => $otherPaid,
                        'balance' => $otherBalance,
                        'control_number' => $otherPayment ? $otherPayment->control_number : null,
                        'status' => $otherPayment ? $otherPayment->payment_status : 'No Payment',
                        'installments' => $otherInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $otherAllowPartial,
                        'other_fees_details' => $otherFeesDetails->map(function($detail) {
                            return [
                                'detailID' => $detail->detailID,
                                'fee_detail_name' => $detail->fee_detail_name,
                                'amount' => (float) $detail->amount,
                                'description' => $detail->description,
                            ];
                        })->values()
                    ],
                    'total' => [
                        'required' => $totalRequired,
                        'paid' => $totalPaid,
                        'balance' => $totalBalance
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading fees summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Endpoint: Get Parent Payments
     *
     * This endpoint returns payment records for a parent's children including:
     * - Parent details
     * - Students list
     * - Payment records with details
     * - Payment statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Request Parameters (Query or Body):
     * - parentID: integer (required) - Parent ID obtained from login
     * - schoolID: integer (required) - School ID obtained from login
     * - student: integer (optional) - Filter by student ID
     * - year: string (optional) - Filter by year (default: current year)
     * - fee_type: string (optional) - Filter by fee type (Tuition Fees, Other Fees)
     * - search: string (optional) - Search by student name or admission number
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Payments retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "students": [...],
     *     "years": [...],
     *     "filters": {...},
     *     "statistics": {...},
     *     "payments": [...]
     *   }
     * }
     */
    public function apiGetParentPayments(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            $studentFilter = $request->input('student', '');
            $yearFilter = $request->input('year', date('Y'));
            $feeTypeFilter = $request->input('fee_type', '');
            $search = $request->input('search', '');

            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Get all students of this parent
            $query = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['subclass.class', 'payments' => function($q) use ($yearFilter) {
                    $q->whereYear('created_at', $yearFilter);
                }, 'payments.fee']);

            // Filter by student if provided
            if (!empty($studentFilter)) {
                $query->where('studentID', $studentFilter);
            }

            // Search by student name or admission number
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('admission_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('middle_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->orderBy('first_name')->get();

            // Get available years from payments
            $availableYears = Payment::whereIn('studentID', $students->pluck('studentID'))
                ->where('schoolID', $schoolID)
                ->selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            $currentYear = date('Y');
            if (!in_array($currentYear, $availableYears)) {
                $availableYears[] = $currentYear;
            }
            rsort($availableYears);

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            // Format payments data
            $formattedPayments = [];
            $index = 1;

            foreach ($students as $student) {
                foreach ($student->payments as $payment) {
                    // Filter by year (already filtered in query, but double-check)
                    if ($yearFilter && date('Y', strtotime($payment->created_at)) != $yearFilter) {
                        continue;
                    }

                    // Filter by fee type
                    if (!empty($feeTypeFilter) && $payment->fee_type !== $feeTypeFilter) {
                        continue;
                    }

                    // Format payment data
                    $paymentData = [
                        'paymentID' => $payment->paymentID,
                        'fee_type' => $payment->fee_type,
                        'control_number' => $payment->control_number,
                        'amount_required' => (float) $payment->amount_required,
                        'amount_paid' => (float) $payment->amount_paid,
                        'balance' => (float) $payment->balance,
                        'payment_status' => $payment->payment_status,
                        'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                        'payment_reference' => $payment->payment_reference,
                        'notes' => $payment->notes,
                        'created_at' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : null,
                    ];

                    // Format student data
                    $studentData = [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name ?? '',
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                        'class' => $student->subclass && $student->subclass->class
                            ? $student->subclass->class->class_name
                            : null,
                        'subclass' => $student->subclass
                            ? $student->subclass->subclass_name
                            : null,
                    ];

                    $formattedPayments[] = [
                        'index' => $index++,
                        'student' => $studentData,
                        'payment' => $paymentData
                    ];
                }
            }

            // Calculate statistics
            $totalPayments = count($formattedPayments);
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $tuitionFeesTotal = 0;
            $otherFeesTotal = 0;

            foreach ($formattedPayments as $item) {
                if ($item['payment']) {
                    $payment = $item['payment'];
                    $totalRequired += $payment['amount_required'] ?? 0;
                    $totalPaid += $payment['amount_paid'] ?? 0;
                    $totalBalance += $payment['balance'] ?? 0;

                    // Separate by fee type
                    if ($payment['fee_type'] === 'Tuition Fees') {
                        $tuitionFeesTotal += $payment['amount_required'] ?? 0;
                    } else {
                        $otherFeesTotal += $payment['amount_required'] ?? 0;
                    }

                    if ($payment['payment_status'] === 'Pending') {
                        $pendingPayments++;
                    } elseif ($payment['payment_status'] === 'Incomplete Payment' || $payment['payment_status'] === 'Partial') {
                        $incompletePayments++;
                    } elseif ($payment['payment_status'] === 'Paid') {
                        $paidPayments++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                    ],
                    'students' => $students->map(function($student) {
                        return [
                            'studentID' => $student->studentID,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name ?? '',
                            'last_name' => $student->last_name,
                            'admission_number' => $student->admission_number ?? '',
                            'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                            'class' => $student->subclass && $student->subclass->class
                                ? $student->subclass->class->class_name
                                : null,
                            'subclass' => $student->subclass
                                ? $student->subclass->subclass_name
                                : null,
                        ];
                    }),
                    'years' => $availableYears,
                    'filters' => [
                        'student' => $studentFilter,
                        'year' => $yearFilter,
                        'fee_type' => $feeTypeFilter,
                        'search' => $search,
                    ],
                    'statistics' => [
                        'total_payments' => $totalPayments,
                        'pending_payments' => $pendingPayments,
                        'incomplete_payments' => $incompletePayments,
                        'paid_payments' => $paidPayments,
                        'total_required' => round($totalRequired, 2),
                        'total_paid' => round($totalPaid, 2),
                        'total_balance' => round($totalBalance, 2),
                        'tuition_fees_total' => round($tuitionFeesTotal, 2),
                        'other_fees_total' => round($otherFeesTotal, 2),
                    ],
                    'payments' => $formattedPayments,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Endpoint: Get Parent Fees Summary
     *
     * This endpoint returns fees summary for a parent's children including:
     * - Fees required for each student
     * - Payment status
     * - Installments information
     * - Other fees details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Request Parameters (Query or Body):
     * - parentID: integer (required) - Parent ID obtained from login
     * - schoolID: integer (required) - School ID obtained from login
     * - year: string (optional) - Filter by year (default: current year)
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Fees summary retrieved successfully",
     *   "data": {
     *     "parent": {...},
     *     "school": {...},
     *     "years": [...],
     *     "filters": {...},
     *     "summary": [...]
     *   }
     * }
     */
    public function apiGetParentFeesSummary(Request $request)
    {
        try {
            // Get parameters
            $parentID = $request->input('parentID');
            $schoolID = $request->input('schoolID');
            // Validate required parameters
            if (!$parentID || !$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'parentID and schoolID are required'
                ], 400);
            }

            // Get parent details
            $parent = ParentModel::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found'
                ], 404);
            }

            // Academic-year cards (same list as the web/results), selected card = filter
            $period = $this->feesSummaryPeriod((int) $schoolID, $request->input('academic_yearID'), $request->input('year'));

            // Get all students of this parent with subclass and class relationships
            $students = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with([
                    'subclass.class',
                    'payments' => function($q) use ($period) {
                        $this->applyFeesPeriodFilter($q, $period['selected']);
                    }
                ])
                ->get();

            // Get school details
            $school = School::where('schoolID', $schoolID)->first();

            $summary = [];

            foreach ($students as $student) {
                $className = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->class_name
                    : 'N/A';

                // Get classID from subclass (fees are assigned to class, not subclass)
                $classID = $student->subclass && $student->subclass->class
                    ? $student->subclass->class->classID
                    : null;

                // Get fees for this student's class with relationships
                $classFees = Fee::where('classID', $classID)
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->with(['installments' => function($q) {
                        $q->where('status', 'Active');
                    }, 'otherFeeDetails' => function($q) {
                        $q->where('status', 'Active');
                    }])
                    ->get();

                $tuitionFees = $classFees->where('fee_type', 'Tuition Fees');
                $otherFees = $classFees->where('fee_type', 'Other Fees');

                // Get payments for this student
                $tuitionPayment = $student->payments->where('fee_type', 'Tuition Fees')->first();
                $otherPayment = $student->payments->where('fee_type', 'Other Fees')->first();

                // Calculate totals
                $tuitionRequired = $tuitionFees->sum('amount');
                $otherRequired = $otherFees->sum('amount');
                $totalRequired = $tuitionRequired + $otherRequired;

                $tuitionPaid = $tuitionPayment ? (float) $tuitionPayment->amount_paid : 0;
                $otherPaid = $otherPayment ? (float) $otherPayment->amount_paid : 0;
                $totalPaid = $tuitionPaid + $otherPaid;

                $tuitionBalance = $tuitionRequired - $tuitionPaid;
                $otherBalance = $otherRequired - $otherPaid;
                $totalBalance = $totalRequired - $totalPaid;

                // Get installments for tuition fees
                $tuitionInstallments = collect();
                $tuitionAllowPartial = false;
                foreach ($tuitionFees as $fee) {
                    if (method_exists($fee, 'relationLoaded') && !$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }

                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $tuitionInstallments = $tuitionInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $tuitionAllowPartial = true;
                        }
                    }
                }

                // Get installments and other fees details for other fees
                $otherInstallments = collect();
                $otherAllowPartial = false;
                $otherFeesDetails = collect();
                foreach ($otherFees as $fee) {
                    if (method_exists($fee, 'relationLoaded') && !$fee->relationLoaded('installments')) {
                        $fee->load(['installments' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }
                    if (method_exists($fee, 'relationLoaded') && !$fee->relationLoaded('otherFeeDetails')) {
                        $fee->load(['otherFeeDetails' => function($q) {
                            $q->where('status', 'Active');
                        }]);
                    }

                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $otherInstallments = $otherInstallments->merge($fee->installments);
                        if ($fee->allow_partial_payment) {
                            $otherAllowPartial = true;
                        }
                    }
                    if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                        $otherFeesDetails = $otherFeesDetails->merge($fee->otherFeeDetails);
                    }
                }

                $summary[] = [
                    'studentID' => $student->studentID,
                    'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'admission_number' => $student->admission_number ?? 'N/A',
                    'photo' => $student->photo ? url('userImages/' . $student->photo) : null,
                    'class' => $className,
                    'tuition_fees' => [
                        'required' => round($tuitionRequired, 2),
                        'paid' => round($tuitionPaid, 2),
                        'balance' => round($tuitionBalance, 2),
                        'control_number' => $tuitionPayment ? $tuitionPayment->control_number : null,
                        'status' => $tuitionPayment ? $tuitionPayment->payment_status : 'No Payment',
                        'installments' => $tuitionInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $tuitionAllowPartial
                    ],
                    'other_fees' => [
                        'required' => round($otherRequired, 2),
                        'paid' => round($otherPaid, 2),
                        'balance' => round($otherBalance, 2),
                        'control_number' => $otherPayment ? $otherPayment->control_number : null,
                        'status' => $otherPayment ? $otherPayment->payment_status : 'No Payment',
                        'installments' => $otherInstallments->map(function($installment) {
                            return [
                                'installmentID' => $installment->installmentID,
                                'installment_name' => $installment->installment_name,
                                'installment_type' => $installment->installment_type,
                                'amount' => (float) $installment->amount,
                            ];
                        })->values(),
                        'allow_partial_payment' => $otherAllowPartial,
                        'other_fees_details' => $otherFeesDetails->map(function($detail) {
                            return [
                                'detailID' => $detail->detailID,
                                'fee_detail_name' => $detail->fee_detail_name,
                                'amount' => (float) $detail->amount,
                                'description' => $detail->description,
                            ];
                        })->values()
                    ],
                    'total' => [
                        'required' => round($totalRequired, 2),
                        'paid' => round($totalPaid, 2),
                        'balance' => round($totalBalance, 2)
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Fees summary retrieved successfully',
                'data' => [
                    'parent' => [
                        'parentID' => $parent->parentID,
                        'first_name' => $parent->first_name ?? '',
                        'middle_name' => $parent->middle_name ?? '',
                        'last_name' => $parent->last_name ?? '',
                        'phone' => $parent->phone ?? '',
                        'email' => $parent->email ?? '',
                    ],
                    'school' => [
                        'schoolID' => $school->schoolID ?? null,
                        'school_name' => $school->school_name ?? '',
                    ],
                    'academic_years' => $period['options'],
                    'filters' => [
                        'academic_yearID' => $period['selected']['academic_yearID'] ?? null,
                        'year' => $period['selected']['calendar_year'] ?? null,
                        'label' => $period['selected']['label'] ?? null,
                    ],
                    'summary' => $summary,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving fees summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function parentSubjects()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');

        if (!$userType || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Set locale from session
        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        // Get parent details
        $parent = ParentModel::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent not found');
        }

        // Get all active students of this parent with their subclass and class
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['subclass.class'])
            ->get();

        return view('Parents.subjects', compact('parent', 'students'));
    }

    public function getStudentSubjects($studentID)
    {
        try {
            $parentID = Session::get('parentID');
            $schoolID = Session::get('schoolID');

            if (!$parentID || !$schoolID) {
                return response()->json([
                    'error' => 'Parent ID or School ID not found in session.'
                ], 400);
            }

            // Verify student belongs to this parent
            $student = Student::where('studentID', $studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('subclass.class')
                ->first();

            if (!$student) {
                return response()->json([
                    'error' => 'Student not found or unauthorized access.'
                ], 404);
            }

            // Get all subjects for this student's subclass
            $subjects = ClassSubject::where('subclassID', $student->subclassID)
                ->with(['subject', 'teacher'])
                ->get()
                ->map(function($classSubject) use ($student) {
                    // Get election counts
                    $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                    $totalStudents = Student::where('subclassID', $classSubject->subclassID)
                        ->where('status', 'Active')
                        ->count();
                    $nonElectors = $totalStudents - $totalElectors;

                    // Check if this student has elected this subject
                    $isElected = SubjectElector::where('studentID', $student->studentID)
                        ->where('classSubjectID', $classSubject->class_subjectID)
                        ->exists();

                    return [
                        'class_subjectID' => $classSubject->class_subjectID,
                        'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                        'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                        'teacher_name' => $classSubject->teacher
                            ? $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name
                            : 'Not Assigned',
                        'status' => $classSubject->status ?? 'Inactive',
                        'student_status' => $classSubject->student_status ?? null,
                        'elected_count' => $totalElectors,
                        'non_elected_count' => $nonElectors,
                        'total_students' => $totalStudents,
                        'is_elected' => $isElected,
                    ];
                });

            return response()->json([
                'success' => true,
                'student' => [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'subclass_name' => $student->subclass ? ($student->subclass->class->class_name . ' ' . trim($student->subclass->subclass_name)) : 'N/A',
                ],
                'subjects' => $subjects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function electSubject(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'studentID' => 'required|exists:students,studentID',
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $parentID = Session::get('parentID');
            $schoolID = Session::get('schoolID');

            if (!$parentID || !$schoolID) {
                return response()->json(['error' => 'Parent ID or School ID not found in session.'], 400);
            }

            // Verify student belongs to this parent
            $student = Student::where('studentID', $request->studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('parent')
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or unauthorized access.'], 404);
            }

            // Get class subject
            $classSubject = ClassSubject::with(['subject', 'subclass.class'])->find($request->classSubjectID);
            if (!$classSubject) {
                return response()->json(['error' => 'Class subject not found.'], 404);
            }

            // Verify it's an optional subject
            if ($classSubject->student_status !== 'Optional') {
                return response()->json(['error' => 'This subject is required, not optional.'], 400);
            }

            // Verify student belongs to this subclass
            if ($classSubject->subclassID != $student->subclassID) {
                return response()->json(['error' => 'Student does not belong to this subclass.'], 400);
            }

            // Check if already elected
            $existing = SubjectElector::where('studentID', $request->studentID)
                ->where('classSubjectID', $request->classSubjectID)
                ->first();

            if ($existing) {
                return response()->json(['error' => 'Student has already elected this subject.'], 400);
            }

            // Create election
            SubjectElector::create([
                'studentID' => $request->studentID,
                'classSubjectID' => $request->classSubjectID,
            ]);

            // Send SMS notification
            $this->sendElectionSMS($student, $classSubject, 'elected');

            return response()->json([
                'success' => 'Subject elected successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deselectSubject(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'studentID' => 'required|exists:students,studentID',
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $parentID = Session::get('parentID');
            $schoolID = Session::get('schoolID');

            if (!$parentID || !$schoolID) {
                return response()->json(['error' => 'Parent ID or School ID not found in session.'], 400);
            }

            // Verify student belongs to this parent
            $student = Student::where('studentID', $request->studentID)
                ->where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('parent')
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or unauthorized access.'], 404);
            }

            // Get class subject
            $classSubject = ClassSubject::with(['subject', 'subclass.class'])->find($request->classSubjectID);
            if (!$classSubject) {
                return response()->json(['error' => 'Class subject not found.'], 404);
            }

            // Remove election
            $elector = SubjectElector::where('studentID', $request->studentID)
                ->where('classSubjectID', $request->classSubjectID)
                ->first();

            if (!$elector) {
                return response()->json(['error' => 'Student has not elected this subject.'], 400);
            }

            $elector->delete();

            // Send SMS notification
            $this->sendElectionSMS($student, $classSubject, 'deselected');

            return response()->json([
                'success' => 'Subject deselected successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendElectionSMS(Student $student, ClassSubject $classSubject, $action)
    {
        try {
            if (!$student->parent || !$student->parent->phone) {
                \Log::warning("Parent or parent phone not found for student ID: {$student->studentID}. Cannot send SMS.");
                return;
            }

            $schoolName = Session::get('school_name') ?? 'ShuleXpert';
            $studentName = $student->first_name . ' ' . $student->last_name;
            $subjectName = $classSubject->subject->subject_name ?? 'N/A';
            $className = $classSubject->subclass->class->class_name ?? 'N/A';
            $subclassName = $classSubject->subclass->subclass_name ?? 'N/A';
            $fullClassName = trim($subclassName) === '' ? $className : $className . ' ' . $subclassName;

            $msgBody = '';
            if ($action === 'elected') {
                $msgBody = "Mwanafunzi {$studentName} amechagua somo la {$subjectName} kwenye darasa {$fullClassName}. Asante";
            } elseif ($action === 'deselected') {
                $msgBody = "Mwanafunzi {$studentName} ameondoa somo la {$subjectName} kwenye darasa {$fullClassName}. Asante";
            }

            if (!empty($msgBody)) {
                $school = School::find($student->schoolID);
                $schoolName = $school->school_name ?? 'School';
                $prefix = ($school && $school->senderID) ? "" : "{$schoolName}: ";
                $message = "{$prefix}{$msgBody}";

                $response = $this->smsService->sendSms($student->parent->phone, $message);
                if (!$response['success']) {
                    \Log::error("Failed to send election SMS to {$student->parent->phone}: {$response['message']}");
                } else {
                    \Log::info("Election SMS sent to {$student->parent->phone} for student {$student->studentID} ({$action}).");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error sending election SMS: " . $e->getMessage());
        }
    }

    public function getPublishedExams(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $year = $request->get('year');
        $term = $request->get('term');
        $academicYearID = (int) $request->get('academic_yearID', 0);

        $ayIds = [];
        $calendarYears = [];
        if ($academicYearID > 0) {
            $ay = AcademicYear::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->first();
            if ($ay) {
                $ayIds = [$academicYearID];
                $calendarYears = $this->extractCalendarYearsFromAcademicYear($ay);
            }
        } elseif ($year) {
            $calendarYears = [(int) $year];
        } else {
            $ctx = $this->parentAcademicYearContext($schoolID);
            $ayIds = $ctx['selected_ids'];
            $calendarYears = $ctx['calendar_years'];
        }

        $examsQuery = Examination::where('schoolID', $schoolID)
            ->where('term', $term)
            ->where('approval_status', 'Approved')
            ->whereHas('results', function ($q) {
                $q->where('parent_status', 'enabled');
            });

        $this->applyParentExaminationAcademicYearFilter($examsQuery, $ayIds, $calendarYears);

        $exams = $examsQuery->select('examID', 'exam_name', 'academic_yearID', 'year')->get();

        return response()->json([
            'success' => true,
            'exams' => $exams,
            'academic_year_ids' => $ayIds,
            'calendar_years' => $calendarYears,
        ]);
    }

    public function fetchStudentResultsAjax(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $parentID = Session::get('parentID');
        $studentID = $request->input('student');
        $examID = $request->input('exam');
        $type = $request->input('type', 'exam');
        $year = $request->input('year');
        $term = $request->input('term');
        $academicYearID = (int) $request->input('academic_yearID', 0);

        $student = Student::with(['subclass.class', 'subclass.combie.subjects'])
            ->where('studentID', $studentID)
            ->where('parentID', $parentID)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Mwanafunzi hakupatikana.']);
        }

        // Resolve calendar year(s) from academic year (manageResults style)
        if ($academicYearID > 0) {
            $ay = AcademicYear::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->first();
            if ($ay) {
                $cals = $this->extractCalendarYearsFromAcademicYear($ay);
                if (!empty($cals) && empty($year)) {
                    $year = $cals[0];
                }
            }
        }

        if ($type === 'report') {
            // 1. Check if report is defined for this year and term
            $isDefined = \App\Models\TermReportDefinition::where('schoolID', $schoolID)
                ->where('year', $year)
                ->where('term', $term)
                ->exists();

            if (!$isDefined) {
                return response()->json(['success' => false, 'message' => 'Hakuna ripoti iliyotoka kwenye huu muhula.']);
            }

            // 2. Check if reports are published for this student's class
            $studentClassID = $student->subclass ? $student->subclass->classID : null;
            $isPublished = \App\Models\PublishedReport::where('schoolID', $schoolID)
                ->where('term', $term)
                ->where('year', $year)
                ->where('classID', $studentClassID)
                ->where('status', 'enabled')
                ->exists();

            if (!$isPublished) {
                return response()->json(['success' => false, 'message' => 'Hakuna ripoti iliyotoka kwenye huu muhula.']);
            }
        }

        // Fetch using ResultManagementController engine
        $resultController = new ResultManagementController();
        $reqClone = $request->duplicate();
        $reqClone->merge([
            'getSubjectDetails' => true,
            'studentID' => $studentID,
            'examID' => $examID,
            'type' => $type,
            'year' => $year,
            'term' => $term,
            'academic_yearID' => $academicYearID ?: null,
        ]);

        $detailedData = [];
        $res = $resultController->index($reqClone);
        if ($res instanceof \Illuminate\Http\JsonResponse) {
            $detailedData = json_decode($res->content(), true) ?? [];
        }

        if (empty($detailedData) || (isset($detailedData['success']) && !$detailedData['success'])) {
            return response()->json(['success' => false, 'message' => 'Hakuna matokeo yaliyochapishwa kwa sasa.']);
        }

        $isALevel = !empty($detailedData['isALevel']);
        $className = $student->subclass && $student->subclass->class
            ? $student->subclass->class->class_name
            : null;

        // For report type, the structure is slightly different (contains averages across exams)
        if ($type === 'report') {
            $isSecondary = strcasecmp($detailedData['schoolType'] ?? '', 'Secondary') === 0;
            $overallDisplay = $isSecondary
                ? ($detailedData['division'] ?? '-')
                : ($detailedData['grade'] ?? '-');

            $reportResults = [];
            foreach ($detailedData['subjects'] ?? [] as $subject) {
                $grade = $subject['grade'] ?? null;
                $reportResults[] = array_merge($subject, [
                    'average' => $subject['average'] ?? $subject['marks'] ?? null,
                    'remark' => $this->grading->subjectRemark(
                        is_string($grade) ? $grade : null,
                        $className,
                    ),
                    'is_duo' => (bool) ($subject['is_duo'] ?? false),
                ]);
            }

            return response()->json([
                'success' => true,
                'is_report' => true,
                'data' => [
                    'student' => $student,
                    'total_marks' => isset($detailedData['totalMarks']) ? round($detailedData['totalMarks'], 1) : '-',
                    'average' => isset($detailedData['averageMarks']) ? round($detailedData['averageMarks'], 1) : '-',
                    'position' => $detailedData['position'] ?? 'N/A',
                    'overall_grade' => $overallDisplay,
                    'overall_division' => $detailedData['division'] ?? '-',
                    'school_type' => $detailedData['schoolType'] ?? 'Secondary',
                    'is_a_level' => $isALevel,
                    'results' => $reportResults,
                    'exams_included' => $detailedData['exams'] ?? [],
                ],
            ]);
        }

        // For exam type (updated for multi-assessment UI)
        $hasCa = CaDefinition::where('examID', $examID)->exists();
        $position = $detailedData['position'] ?? 'N/A';

        $processedResults = [];
        if (!empty($detailedData['subjects'])) {
            foreach ($detailedData['subjects'] as $subject) {
                $grade = $subject['grade'] ?? 'F';
                $processedResults[] = [
                    'subject_name' => $subject['subject_name'] ?? 'N/A',
                    'average' => array_key_exists('marks', $subject) && $subject['marks'] !== null && $subject['marks'] !== ''
                        ? round((float) $subject['marks'], 1)
                        : '-',
                    'grade' => $grade,
                    'remark' => $this->grading->subjectRemark(
                        is_string($grade) ? $grade : null,
                        $className,
                    ),
                    'exams' => $subject['exams'] ?? [],
                    'is_duo' => (bool) ($subject['is_duo'] ?? false),
                    'papers' => $subject['papers'] ?? [],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'is_report' => false,
            'data' => [
                'student' => $student,
                'total_marks' => isset($detailedData['totalMarks']) ? round($detailedData['totalMarks'], 1) : '-',
                'average' => isset($detailedData['averageMarks']) ? round($detailedData['averageMarks'], 1) : '-',
                'position' => $position,
                'overall_grade' => $detailedData['division'] ?? ($detailedData['grade'] ?? '-'),
                'has_ca' => $hasCa,
                'is_a_level' => $isALevel,
                'school_type' => $detailedData['schoolType'] ?? 'Secondary',
                'results' => $processedResults,
            ],
        ]);
    }

    public function downloadStudentResult(Request $request)
    {
        $examID = $request->input('examID');
        $studentID = $request->input('studentID');
        $year = $request->input('year');
        $term = $request->input('term');
        $type = $request->input('type', 'exam');

        return redirect()->route('admin.download_results_pdf', [
            'option' => 'single',
            'studentID' => $studentID,
            'examID' => $examID,
            'type' => $type,
            'year' => $year,
            'term' => $term
        ]);
    }

    private function calculatePosition($student, $term, $year, $studentAverage, $schoolType)
    {
        // Get main class (class, not subclass) - handle both current and old subclass
        $mainClassID = null;
        if ($student->subclass) {
            $mainClassID = $student->subclass->classID ?? null;
        } elseif ($student->oldSubclass) {
            $mainClassID = $student->oldSubclass->classID ?? null;
        }

        if (!$mainClassID) {
            return null;
        }

        // Get all students in the same main class
        $classStudents = Student::whereHas('subclass', function($query) use ($mainClassID) {
            $query->where('classID', $mainClassID);
        })
        ->where('schoolID', Session::get('schoolID'))
        ->where('status', 'Active')
        ->get();

        // Calculate averages for all students in the class
        $studentAverages = [];

        foreach ($classStudents as $classStudent) {
            $examinationsQuery = Examination::where('schoolID', Session::get('schoolID'))
                ->where('year', $year)
                ->where('approval_status', 'Approved');

            if ($term) {
                $examinationsQuery->where('term', $term);
            }

            $examinations = $examinationsQuery->get();
            $totalMarks = 0;
            $totalSubjects = 0;

            foreach ($examinations as $exam) {
                // Get results with marks
                $results = Result::where('studentID', $classStudent->studentID)
                    ->where('examID', $exam->examID)
                    ->whereIn('status', ['allowed', 'approved'])
                    ->whereNotNull('marks')
                    ->get();

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== '') {
                        $totalMarks += (float) $result->marks;
                        $totalSubjects++;
                    }
                }
            }

            if ($totalSubjects > 0) {
                $avg = $totalMarks / $totalSubjects;
                $studentAverages[] = [
                    'studentID' => $classStudent->studentID,
                    'average' => $avg,
                ];
            }
        }

        // Sort by average (descending)
        usort($studentAverages, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        // Find position
        $position = null;
        foreach ($studentAverages as $index => $data) {
            if ($data['studentID'] == $student->studentID) {
                $position = $index + 1;
                break;
            }
        }

        return $position;
    }

    /**
     * Academic years for parent results wizard - same source as header selector.
     */
    /**
     * Academic-year picker data — same source the parent web results wizard uses.
     * For secondary dual schools, pass $studentID to keep only that child's O/A years
     * (exactly what the website shows after the parent picks a child).
     */
    /**
     * Fee-summary periods are the same academic-year cards the parent sees elsewhere
     * (one entry per academic year), not calendar years derived from payment dates.
     *
     * @return array{options: array, selected: ?array}
     */
    private function feesSummaryPeriod(int $schoolID, $academicYearID = null, $calendarYear = null): array
    {
        $options = $this->parentAcademicYearContext($schoolID)['options'];

        if (!empty($academicYearID)) {
            $selected = collect($options)->firstWhere('academic_yearID', (int) $academicYearID);
        } elseif (!empty($calendarYear)) {
            $selected = collect($options)->firstWhere('calendar_year', (int) $calendarYear);
        } else {
            $selected = collect($options)->firstWhere('is_selected', true) ?? ($options[0] ?? null);
        }

        return ['options' => $options, 'selected' => $selected];
    }

    /**
     * Restrict payments to one fee-summary period.
     * Payments carry academic_yearID; legacy rows without it fall back to the card's calendar year.
     */
    private function applyFeesPeriodFilter($query, ?array $selected)
    {
        if (!$selected) {
            return $query->whereYear('created_at', (int) date('Y'));
        }

        $academicYearID = (int) $selected['academic_yearID'];
        $calendarYear = (int) $selected['calendar_year'];

        return $query->where(function ($q) use ($academicYearID, $calendarYear) {
            $q->where('academic_yearID', $academicYearID)
                ->orWhere(function ($q2) use ($calendarYear) {
                    $q2->whereNull('academic_yearID')->whereYear('created_at', $calendarYear);
                });
        });
    }

    private function parentAcademicYearContext(int $schoolID, ?int $studentID = null, ?bool $forceALevel = null): array
    {
        $yearService = app(SelectedAcademicYearService::class);
        $ctx = $yearService->headerContext($schoolID);
        $mode = $ctx['mode'] ?? 'none';

        if ($mode === 'primary') {
            $pool = collect($ctx['years'] ?? []);
        } elseif ($mode === 'secondary_o_only') {
            // O-only: slash year names are calendars, not A-Level tracks (same as header service).
            $pool = collect($ctx['o_level_years'] ?? $ctx['years'] ?? []);
        } elseif ($mode === 'secondary_dual') {
            $pool = collect($ctx['o_level_years'] ?? [])->concat($ctx['a_level_years'] ?? []);
        } else {
            $pool = collect($ctx['years'] ?? []);
        }

        $filterALevel = $forceALevel;
        if ($filterALevel === null && $studentID && $mode === 'secondary_dual') {
            $student = Student::with('subclass.class')->where('studentID', $studentID)->first();
            $className = $student->subclass->class->class_name ?? '';
            $filterALevel = $yearService->isALevelClassName($className);
        }

        $selectedIds = array_values(array_filter(array_map('intval', $ctx['selected_year_ids'] ?? [])));
        $options = [];
        $seenIds = [];

        foreach ($pool as $y) {
            $id = (int) $y->academic_yearID;
            if ($id < 1 || isset($seenIds[$id])) {
                continue;
            }
            $seenIds[$id] = true;

            // Only dual schools treat slash year names as A-Level tracks.
            $isA = $mode === 'secondary_dual' && $yearService->isALevelYear($y);
            if ($filterALevel !== null && $isA !== $filterALevel) {
                continue;
            }

            $cals = $this->extractCalendarYearsFromAcademicYear($y);
            $baseLabel = trim((string) ($y->year_name ?: $y->year));
            if ($baseLabel === '') {
                $baseLabel = (string) ($cals[0] ?? date('Y'));
            }
            // Web dual picker shows short_label after the student is chosen (O/A already implied).
            $label = $mode === 'secondary_dual' && $filterALevel === null
                ? (($isA ? 'A: ' : 'O: ').$baseLabel)
                : $baseLabel;

            $options[] = [
                'academic_yearID' => $id,
                'label' => $label,
                'short_label' => $baseLabel,
                'calendar_year' => $cals[0] ?? (int) ($y->year ?: date('Y')),
                'is_selected' => in_array($id, $selectedIds, true),
                'is_a_level' => $isA,
            ];
        }

        $labels = [];
        foreach ($selectedIds as $sid) {
            $opt = collect($options)->firstWhere('academic_yearID', $sid);
            if ($opt) {
                $labels[] = $opt['label'];
            }
        }

        // One calendar value per option — same cards the web shows (no slash expansion duplicates).
        $calendarYears = array_values(array_unique(array_filter(array_map(
            fn ($opt) => (int) ($opt['calendar_year'] ?? 0),
            $options
        ))));

        return [
            'mode' => $mode,
            'options' => array_values($options),
            'selected_ids' => $selectedIds,
            'calendar_years' => $calendarYears,
            'filtered_by_student' => $studentID ? true : false,
            'student_is_a_level' => $filterALevel,
            'label' => !empty($labels)
                ? implode(' / ', $labels)
                : (!empty($options) ? $options[0]['label'] : ('Year ' . date('Y'))),
        ];
    }

    private function extractCalendarYearsFromAcademicYear($year): array
    {
        $blob = trim((string) ($year->year ?? '') . ' ' . (string) ($year->year_name ?? ''));
        if ($blob === '') {
            return [];
        }

        preg_match_all('/(20\d{2})/', $blob, $matches);
        $found = array_map('intval', $matches[1] ?? []);
        if (empty($found) && is_numeric($year->year ?? null)) {
            $found[] = (int) $year->year;
        }

        return array_values(array_unique(array_filter($found, fn ($y) => $y >= 2000)));
    }

    private function applyParentExaminationAcademicYearFilter($query, array $yearIds, array $calendarYears = [])
    {
        $yearIds = array_values(array_filter(array_map('intval', $yearIds)));
        $calendarYears = array_values(array_filter(array_map('intval', $calendarYears)));

        if (!empty($yearIds)) {
            $query->where(function ($q) use ($yearIds, $calendarYears) {
                $q->whereIn('academic_yearID', $yearIds);
                foreach ($yearIds as $yearId) {
                    $q->orWhereJsonContains('academic_year_ids', $yearId);
                    $q->orWhereJsonContains('academic_year_ids', (string) $yearId);
                }
                if (!empty($calendarYears)) {
                    $q->orWhere(function ($q2) use ($calendarYears) {
                        $q2->whereNull('academic_yearID')->whereIn('year', $calendarYears);
                    });
                    $q->orWhereIn('year', $calendarYears);
                }
            });
        } elseif (!empty($calendarYears)) {
            $query->whereIn('year', $calendarYears);
        }

        return $query;
    }

    /**
     * Unified parent header notifications (results + home packages).
     */
    public function headerNotifications()
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notifLimit = max(50, min(200, (int) request()->input('limit', 100)));

        $resultItems = ParentResultNotification::with(['student', 'examination'])
            ->where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->orderByDesc('created_at')
            ->limit($notifLimit)
            ->get()
            ->map(function (ParentResultNotification $n) {
                $studentName = trim(
                    ($n->student->first_name ?? '') . ' ' . ($n->student->last_name ?? '')
                ) ?: __('parent.student');
                $isReport = ($n->type ?? '') === 'report';
                $examName = $n->examination->exam_name ?? __('parent.exam_generic');
                $term = $n->term ?: ($n->examination->term ?? '');
                $year = $n->year ?: ($n->examination->year ?? '');
                $termYear = trim(($term ? __('parent.term_word') . ' ' . $term : '') . ($year ? ' · ' . $year : ''));
                $extra = $termYear ? ' (' . $termYear . ')' : '';

                if ($isReport) {
                    $message = __('parent.notif_report_msg', ['name' => $studentName, 'extra' => $extra]);
                    $title = __('parent.notif_report_title');
                    $url = route('parentReport');
                    $icon = 'bx-book-open';
                } else {
                    $message = __('parent.notif_exam_msg', ['exam' => $examName, 'name' => $studentName, 'extra' => $extra]);
                    $title = __('parent.notif_exam_title');
                    $url = route('parentResults');
                    $icon = 'bx-trophy';
                }

                return [
                    'id' => $n->id,
                    'source' => 'result',
                    'type' => $isReport ? 'report' : 'exam',
                    'is_viewed' => (bool) $n->is_viewed,
                    'created_at' => optional($n->created_at)->diffForHumans(),
                    'created_at_raw' => optional($n->created_at)->timestamp ?? 0,
                    'title' => $title,
                    'message' => $message,
                    'icon' => $icon,
                    'url' => $url,
                    'screen' => $isReport ? 'reports' : 'results',
                    'student_name' => $studentName,
                ];
            });

        $hpItems = ParentHomePackageNotification::with([
            'homePackage.holiday',
            'homePackage.subject',
        ])
            ->where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->orderByDesc('created_at')
            ->limit($notifLimit)
            ->get()
            ->map(function (ParentHomePackageNotification $n) {
                $p = $n->homePackage;
                $subject = $p->subject->subject_name ?? __('parent.subject');
                $holiday = $p->holiday->holiday_name ?? '';
                $mime = strtolower((string) ($p->mime_type ?? ''));
                $filename = strtolower((string) ($p->original_filename ?? ''));
                $isPdf = str_contains($mime, 'pdf') || str_ends_with($filename, '.pdf');
                $isDoc = str_contains($mime, 'word')
                    || str_contains($mime, 'msword')
                    || str_contains($mime, 'officedocument')
                    || str_ends_with($filename, '.doc')
                    || str_ends_with($filename, '.docx');
                $fileKind = $isPdf ? 'pdf' : ($isDoc ? 'doc' : 'file');

                return [
                    'id' => $n->id,
                    'source' => 'home_package',
                    'type' => 'home_package',
                    'is_viewed' => (bool) $n->is_viewed,
                    'created_at' => optional($n->created_at)->diffForHumans(),
                    'created_at_raw' => optional($n->created_at)->timestamp ?? 0,
                    'title' => __('parent.home_package'),
                    'message' => __('parent.notif_hp_msg', [
                        'extra' => ($subject ? ' - ' . $subject : '') . ($holiday ? ' (' . $holiday . ')' : ''),
                    ]),
                    'icon' => 'bx-home-smile',
                    'url' => route('parentHomePackages'),
                    'holiday_name' => $holiday ?: '-',
                    'original_filename' => $p->original_filename ?? null,
                    'file_kind' => $fileKind,
                    'screen' => 'home_packages',
                    'view_url' => $p ? MobileApi::packageViewUrl((int) $p->home_packageID) : null,
                    'download_url' => $p ? MobileApi::packageDownloadUrl((int) $p->home_packageID) : null,
                    'level_label' => $p ? $p->levelLabel() : '-',
                ];
            });

        $noteItems = collect();
        if (Schema::hasTable('subject_notes') && Schema::hasTable('parent_subject_note_notifications')) {
            $noteItems = ParentSubjectNoteNotification::with([
                'note.subject',
                'student',
            ])
                ->where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->orderByDesc('created_at')
                ->limit($notifLimit)
                ->get()
                ->map(function (ParentSubjectNoteNotification $n) {
                    $note = $n->note;
                    $subject = $note->subject->subject_name ?? __('parent.subject');
                    $title = $note->title ?? '';
                    $studentName = trim(($n->student->first_name ?? '').' '.($n->student->last_name ?? ''));
                    $isPaper = strtolower((string) ($note->kind ?? 'note')) === 'past_paper';

                    return [
                        'id' => $n->id,
                        'source' => $isPaper ? 'past_paper' : 'note',
                        'type' => $isPaper ? 'past_paper' : 'note',
                        'is_viewed' => (bool) $n->is_viewed,
                        'created_at' => optional($n->created_at)->diffForHumans(),
                        'created_at_raw' => optional($n->created_at)->timestamp ?? 0,
                        'title' => $isPaper ? __('parent.past_papers') : __('parent.notes'),
                        'message' => __('parent.notes_subject').': '.$subject
                            . ($title ? "\n".__('parent.notes_title_label').': '.$title : '')
                            . ($studentName ? "\n".$studentName : ''),
                        'icon' => $isPaper ? 'bx-file' : 'bx-note',
                        'url' => $isPaper ? route('parentPastPapers') : route('parentNotes'),
                        'screen' => $isPaper ? 'past_papers' : 'notes',
                        'subject_name' => $subject,
                        'note_title' => $title,
                        'student_name' => $studentName,
                        'view_url' => $note ? MobileApi::noteViewUrl((int) $note->noteID) : null,
                        'download_url' => $note ? MobileApi::noteDownloadUrl((int) $note->noteID) : null,
                    ];
                });
        }

        $merged = $resultItems->concat($hpItems)->concat($noteItems)
            ->sortByDesc('created_at_raw')
            ->take($notifLimit)
            ->values()
            ->map(function ($row) {
                unset($row['created_at_raw']);
                return $row;
            });

        $unreadResults = ParentResultNotification::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('is_viewed', false)
            ->count();
        $unreadHp = ParentHomePackageNotification::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('is_viewed', false)
            ->count();
        $unreadNotes = 0;
        $unreadPastPapers = 0;
        if (Schema::hasTable('parent_subject_note_notifications')) {
            $unreadBase = ParentSubjectNoteNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->where('is_viewed', false);
            if (Schema::hasTable('subject_notes') && Schema::hasColumn('subject_notes', 'kind')) {
                $unreadNotes = (clone $unreadBase)->whereHas('note', function ($q) {
                    $q->where('kind', 'note');
                })->count();
                $unreadPastPapers = (clone $unreadBase)->whereHas('note', function ($q) {
                    $q->where('kind', 'past_paper');
                })->count();
            } else {
                $unreadNotes = $unreadBase->count();
            }
        }

        return response()->json([
            'success' => true,
            'unread' => $unreadResults + $unreadHp + $unreadNotes + $unreadPastPapers,
            'unread_results' => $unreadResults,
            'unread_home_packages' => $unreadHp,
            'unread_notes' => $unreadNotes,
            'unread_past_papers' => $unreadPastPapers,
            'notifications' => $merged,
        ]);
    }

    public function markHeaderNotificationsRead(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $source = $request->input('source', 'all');
        $id = $request->input('id');
        $markAll = $request->boolean('all') || $id === 'all' || $source === 'all';

        if ($markAll && ($source === 'all' || !$request->filled('source'))) {
            ParentResultNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->where('is_viewed', false)
                ->update(['is_viewed' => true]);
            ParentHomePackageNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->where('is_viewed', false)
                ->update(['is_viewed' => true]);
            if (Schema::hasTable('parent_subject_note_notifications')) {
                ParentSubjectNoteNotification::where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->where('is_viewed', false)
                    ->update(['is_viewed' => true]);
            }

            return response()->json(['success' => true, 'message' => 'Arifa zote zimesomwa']);
        }

        if ($source === 'result') {
            if ($request->boolean('all') || $id === 'all') {
                ParentResultNotification::where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->where('is_viewed', false)
                    ->update(['is_viewed' => true]);
            } else {
                ParentResultNotification::where('id', $id)
                    ->where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->update(['is_viewed' => true]);
            }
        } elseif ($source === 'home_package') {
            if ($request->boolean('all') || $id === 'all') {
                ParentHomePackageNotification::where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->where('is_viewed', false)
                    ->update(['is_viewed' => true]);
            } else {
                ParentHomePackageNotification::where('id', $id)
                    ->where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->update(['is_viewed' => true]);
            }
        } elseif (in_array($source, ['note', 'past_paper'], true) && Schema::hasTable('parent_subject_note_notifications')) {
            $markQ = ParentSubjectNoteNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID);
            if (Schema::hasTable('subject_notes') && Schema::hasColumn('subject_notes', 'kind')) {
                $kind = $source === 'past_paper' ? 'past_paper' : 'note';
                $markQ->whereHas('note', function ($q) use ($kind) {
                    $q->where('kind', $kind);
                });
            } elseif ($source === 'past_paper') {
                return response()->json(['success' => true]);
            }
            if ($request->boolean('all') || $id === 'all') {
                $markQ->where('is_viewed', false)->update(['is_viewed' => true]);
            } else {
                $markQ->where('id', $id)->update(['is_viewed' => true]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function mobileAcademicYearContext(int $schoolID, ?int $studentID = null, ?bool $forceALevel = null): array
    {
        return $this->parentAcademicYearContext($schoolID, $studentID, $forceALevel);
    }

    public function mobileAttendanceCalendar(int $studentID, int $schoolID, int $year, int $month): array
    {
        return $this->buildParentAttendanceMonthCalendar($studentID, $schoolID, $year, $month);
    }

    public function mobileFingerprintCalendar(int $studentID, int $schoolID, int $year, int $month): array
    {
        return $this->buildParentFingerprintMonthCalendar($studentID, $schoolID, $year, $month);
    }
}
