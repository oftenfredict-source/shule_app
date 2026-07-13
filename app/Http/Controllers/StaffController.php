<?php

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StaffPermission;
use App\Models\StaffProfession;
use App\Models\PermissionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class StaffController extends Controller
{
    private function getStaffContext()
    {
        $userType = Session::get('user_type');
        if (!$userType || $userType !== 'Staff') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $staffID = Session::get('staffID');
        $staff = $staffID ? OtherStaff::find($staffID) : null;
        $userID = Session::get('userID');
        $user = $userID ? \App\Models\User::find($userID) : null;

        if (!$staff) {
            Session::flush();
            return redirect()->route('login')->with('error', 'Staff account not found.');
        }

        $staffProfession = $staff->profession_id
            ? StaffProfession::find($staff->profession_id)
            : null;

        $staffPermissionsByCategory = collect();
        if ($staff->profession_id) {
            $staffPermissionsByCategory = StaffPermission::where('profession_id', $staff->profession_id)
                ->get()
                ->groupBy('permission_category');
        }

        return [
            'staff' => $staff,
            'user' => $user,
            'staffProfession' => $staffProfession,
            'staffPermissionsByCategory' => $staffPermissionsByCategory,
        ];
    }

    public function staffDashboard()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.dashboard', $context);
    }

    public function suggestions()
    {
        return $this->manageStaffFeedback(request());
    }

    public function incidents()
    {
        return $this->manageStaffFeedback(request());
    }

    public function permissions()
    {
        return $this->manageStaffPermissions(request());
    }

    public function leave()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.leave', $context);
    }

    public function payroll()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.payroll', $context);
    }

    public function manageStaffPermissions(Request $request)
    {
        $user = Session::get('user_type');
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Staff' || !$staffID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $activeTab = $request->input('tab', 'request');

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $filterType = $request->input('filter_type', 'all');
        $filterMonth = $request->input('filter_month');
        $filterMonthYear = $request->input('filter_month_year', date('Y'));
        $filterYear = $request->input('filter_year', date('Y'));

        $query = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID);

        if ($filterType === 'range') {
            if ($dateFrom) $query->whereDate('start_date', '>=', $dateFrom);
            if ($dateTo) $query->whereDate('start_date', '<=', $dateTo);
        } elseif ($filterType === 'month') {
            if ($filterMonth) {
                $query->whereMonth('start_date', $filterMonth)
                      ->whereYear('start_date', $filterMonthYear);
            }
        } elseif ($filterType === 'year') {
            if ($filterYear) $query->whereYear('start_date', $filterYear);
        }

        $permissions = $query->orderBy('created_at', 'desc')->get();

        $pendingPermissions = $permissions->where('status', 'pending');
        $approvedPermissions = $permissions->where('status', 'approved');
        $rejectedPermissions = $permissions->where('status', 'rejected');

        if (in_array($activeTab, ['pending', 'approved', 'rejected'], true)) {
            \App\Models\PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'staff')
                ->where('staffID', $staffID)
                ->where('status', $activeTab)
                ->update(['is_read_by_requester' => true]);
        }

        $unreadPendingCount = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->where('status', 'pending')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadApprovedCount = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->where('status', 'approved')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadRejectedCount = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->where('status', 'rejected')
            ->where('is_read_by_requester', false)
            ->count();

        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        $editPermission = null;
        $editId = $request->input('edit_id');
        if ($editId && $activeTab === 'request') {
            $editPermission = \App\Models\PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'staff')
                ->where('staffID', $staffID)
                ->where('permissionID', $editId)
                ->where('status', 'rejected')
                ->first();
        }

        return view('Teacher.manage_permissions', array_merge($context, [
            'activeTab' => $activeTab,
            'pendingPermissions' => $pendingPermissions,
            'approvedPermissions' => $approvedPermissions,
            'rejectedPermissions' => $rejectedPermissions,
            'unreadPendingCount' => $unreadPendingCount,
            'unreadApprovedCount' => $unreadApprovedCount,
            'unreadRejectedCount' => $unreadRejectedCount,
            'permissionContext' => 'staff',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterType' => $filterType,
            'filterMonth' => $filterMonth,
            'filterMonthYear' => $filterMonthYear,
            'filterYear' => $filterYear,
            'editPermission' => $editPermission,
        ]));
    }

    public function storeStaffPermission(Request $request)
    {
        $user = Session::get('user_type');
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Staff' || !$staffID || !$schoolID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'time_mode' => 'required|in:days,hours',
            'days_count' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'reason_type' => 'required|in:medical,official,professional,emergency,other',
            'reason_description' => 'required|string|min:5',
        ]);

        if ($validated['time_mode'] === 'days') {
            if (empty($validated['days_count']) || empty($validated['start_date']) || empty($validated['end_date'])) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please provide days count and date range.'], 422);
                }
                return redirect()->back()->with('error', 'Please provide days count and date range.')->withInput();
            }
            $daysCount = $this->calculateWorkingDays($validated['start_date'], $validated['end_date']);
            if ($daysCount > 7) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Days exceed limit. Use 7 days or less.'], 422);
                }
                return redirect()->back()->with('error', 'Days exceed limit. Use 7 days or less.')->withInput();
            }
        }

        if ($validated['time_mode'] === 'hours') {
            if (empty($validated['start_time']) || empty($validated['end_time'])) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please provide start and end time.'], 422);
                }
                return redirect()->back()->with('error', 'Please provide start and end time.')->withInput();
            }
            $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
            $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
            if ($end->lessThanOrEqualTo($start)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'End time must be after start time.'], 422);
                }
                return redirect()->back()->with('error', 'End time must be after start time.')->withInput();
            }
        }

        $computedDays = null;
        if ($validated['time_mode'] === 'days') {
            $computedDays = $this->calculateWorkingDays($validated['start_date'], $validated['end_date']);
        }

        // Check for approval chain
        $hasChain = \Illuminate\Support\Facades\DB::table('permission_approval_chains')
            ->where('schoolID', $schoolID)
            ->where('permission_type', 'staff')
            ->exists();

        $permissionId = $request->input('permission_id');
        $permissionData = [
            'schoolID' => $schoolID,
            'requester_type' => 'staff',
            'staffID' => $staffID,
            'time_mode' => $validated['time_mode'],
            'days_count' => $validated['time_mode'] === 'days' ? $computedDays : null,
            'start_date' => $validated['time_mode'] === 'days' ? $validated['start_date'] : null,
            'end_date' => $validated['time_mode'] === 'days' ? $validated['end_date'] : null,
            'start_time' => $validated['time_mode'] === 'hours' ? $validated['start_time'] : null,
            'end_time' => $validated['time_mode'] === 'hours' ? $validated['end_time'] : null,
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_requester' => true,
            'current_approval_order' => $hasChain ? 1 : 0
        ];

        $isResubmission = false;
        if ($permissionId) {
            $permission = \App\Models\PermissionRequest::where('permissionID', $permissionId)
                ->where('staffID', $staffID)
                ->where('status', 'rejected')
                ->first();
            if ($permission) {
                // Determine if we should keep the current stage or reset
                $resubmissionOrder = $permission->current_approval_order;

                $permissionData['current_approval_order'] = $resubmissionOrder;
                $permissionData['status'] = 'pending';

                // Capture the rejector's info BEFORE clearing the rejected log
                $rejectionLog = \Illuminate\Support\Facades\DB::table('permission_approval_logs')
                    ->where('permissionID', $permissionId)
                    ->where('status', 'rejected')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $rejectorPhone = null;
                if ($rejectionLog) {
                    $rejectorTeacher = \App\Models\Teacher::where('id', $rejectionLog->approver_id)->first();
                    if ($rejectorTeacher) {
                        $rejectorPhone = $rejectorTeacher->phone_number;
                    } else {
                        $rejectorStaff = \App\Models\OtherStaff::where('id', $rejectionLog->approver_id)->first();
                        if ($rejectorStaff) {
                            $rejectorPhone = $rejectorStaff->phone_number;
                        }
                    }
                }

                $permission->update($permissionData);

                // Remove ONLY the rejected log
                \Illuminate\Support\Facades\DB::table('permission_approval_logs')
                    ->where('permissionID', $permissionId)
                    ->where('status', 'rejected')
                    ->delete();

                $isResubmission = true;

                // Notify the rejector via SMS
                if ($rejectorPhone) {
                    $staffMember = \App\Models\OtherStaff::where('id', $staffID)->first();
                    $requesterName = $staffMember ? trim(($staffMember->first_name ?? '') . ' ' . ($staffMember->last_name ?? '')) : 'Staff';
                    $smsMsg = "Ruksa uliyoikataa ya {$requesterName}, amerekebisha na kutuma upya ingia kwenye mfumo kukagua.";
                    (new \App\Services\SmsService())->sendSms($rejectorPhone, $smsMsg);
                }
            } else {
                $permission = \App\Models\PermissionRequest::create($permissionData);
            }
        } else {
            $permission = \App\Models\PermissionRequest::create($permissionData);
        }

        if ($hasChain) {
            // Notify first approver
            app(\App\Http\Controllers\AdminController::class)->notifyNextApprover($permission);
        }

        $staff = \App\Models\OtherStaff::where('id', $staffID)->first();
        $school = \App\Models\School::where('schoolID', $schoolID)->first();
        $smsService = new \App\Services\SmsService();

        if ($staff && $staff->phone_number) {
            $smsService->sendSms($staff->phone_number, 'Your permission request has been submitted to Admin. Please wait for approval.');
        }

        if ($school && $school->phone) {
            $staffName = $staff ? trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) : 'Staff';
            $reasonLabel = $validated['reason_description'];
            $schoolName = $school->school_name ?? 'School';
            $periodLabel = 'N/A';
            if ($validated['time_mode'] === 'days') {
                $periodLabel = ($validated['start_date'] ?? '') . ' to ' . ($validated['end_date'] ?? '');
            } else {
                $periodLabel = ($validated['start_time'] ?? '') . ' to ' . ($validated['end_time'] ?? '');
            }

            $prefix = ($school && $school->senderID) ? "" : "{$schoolName}: ";
            $message = "{$prefix}New staff permission request by {$staffName}. Period: {$periodLabel}. Reason: {$reasonLabel}. Please review.";
            $smsService->sendSms($school->phone, $message);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Permission request submitted successfully.']);
        }
        return redirect()->route('staff.permissions')->with('success', 'Permission request submitted successfully.');
    }

    public function manageStaffFeedback(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Staff') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        $tab = $request->query('tab');
        $section = $request->query('section', 'send');
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab = $routeName === 'staff.incidents' ? 'incidents' : 'suggestions';
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $feedbackQuery = \App\Models\StaffFeedback::where('staffID', $staffID)
            ->where('schoolID', $schoolID);

        if ($dateFrom) {
            $feedbackQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate('created_at', '<=', $dateTo);
        }

        $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();

        $suggestions = $feedback->where('type', 'suggestion')->values();
        $incidents = $feedback->where('type', 'incident')->values();

        $suggestionStats = [
            'total' => $suggestions->count(),
            'pending' => $suggestions->where('status', 'pending')->count(),
            'approved' => $suggestions->where('status', 'approved')->count(),
            'rejected' => $suggestions->where('status', 'rejected')->count(),
        ];

        $incidentStats = [
            'total' => $incidents->count(),
            'pending' => $incidents->where('status', 'pending')->count(),
            'approved' => $incidents->where('status', 'approved')->count(),
            'rejected' => $incidents->where('status', 'rejected')->count(),
        ];

        $readType = $tab === 'incidents' ? 'incident' : 'suggestion';
        \App\Models\StaffFeedback::where('staffID', $staffID)
            ->where('schoolID', $schoolID)
            ->where('type', $readType)
            ->update(['is_read_by_staff' => true]);

        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Teacher.manage_feedback', array_merge($context, [
            'activeTab' => $tab,
            'activeSection' => $section,
            'suggestions' => $suggestions,
            'incidents' => $incidents,
            'suggestionStats' => $suggestionStats,
            'incidentStats' => $incidentStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'feedbackContext' => 'staff',
        ]));
    }

    public function storeStaffFeedback(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Staff') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired'], 403);
            }
            return redirect()->route('login')->with('error', 'Session expired');
        }

        $validated = $request->validate([
            'type' => 'required|in:suggestion,incident',
            'message' => 'required|string',
        ]);

        \App\Models\StaffFeedback::create([
            'schoolID' => $schoolID,
            'staffID' => $staffID,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_staff' => true,
        ]);

        $staff = \App\Models\OtherStaff::where('id', $staffID)->first();
        $school = \App\Models\School::where('schoolID', $schoolID)->first();
        $adminPhone = $school->phone ?? null;

        if ($adminPhone) {
            $typeLabel = $validated['type'] === 'incident' ? 'Incident' : 'Suggestion';
            $staffName = $staff ? trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) : 'Staff';
            $message = "New {$typeLabel} from {$staffName}. Please review in the system.";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($adminPhone, $message);
        }

        $routeName = $validated['type'] === 'incident' ? 'staff.incidents' : 'staff.suggestions';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Message sent successfully.']);
        }

        return redirect()->route($routeName)->with('success', 'Message sent successfully.');
    }

    public function profile()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.profile', $context);
    }

    public function updateProfile(Request $request)
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return $context;
        }

        /** @var \App\Models\OtherStaff $staff */
        $staff = $context['staff'];
        /** @var \App\Models\User|null $user */
        $user = $context['user'];

        $request->validate([
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'current_password' => 'nullable|string|min:4',
            'new_password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
        ]);

        if ($request->filled('new_password')) {
            if (!$user || !$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
                }
                return redirect()->back()->with('error', 'Current password is incorrect.');
            }
            $user->password = Hash::make($request->new_password);
            $user->save();

            Session::forget('force_password_change');
        }

        if ($request->hasFile('profile_image')) {
            // Determine upload path - Prioritize the directory where index.php is located
            $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
            $uploadPath = $scriptDir . '/userImages';

            // If scriptDir doesn't exist or isn't writable, fallback to other common paths
            if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
                $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/userImages' : null;
                if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                    $uploadPath = $docRootPath;
                } else {
                    $uploadPath = public_path('userImages');
                }
            }

            if (!file_exists($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            if ($staff->image && file_exists($uploadPath . '/' . $staff->image)) {
                @unlink($uploadPath . '/' . $staff->image);
            }

            $imageName = time() . '_' . $request->file('profile_image')->getClientOriginalName();
            $request->file('profile_image')->move($uploadPath, $imageName);
            $staff->image = $imageName;
            $staff->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
        }
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    // Student Permission Request Methods
    public function showStudentPermissionForm()
    {
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Staff.student_permission');
    }

    public function getStaffPermissionRequests()
    {
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        \Log::info('getStaffPermissionRequests called', ['staffID' => $staffID, 'schoolID' => $schoolID]);

        if (!$staffID || !$schoolID) {
            \Log::error('Unauthorized access - missing session data', ['staffID' => $staffID, 'schoolID' => $schoolID]);
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }

        try {
            $requests = DB::table('student_permissions as sp')
                ->where('sp.schoolID', $schoolID)
                ->where('sp.requester_id', $staffID)
                ->where('sp.requester_type', 'staff')
                ->leftJoin('students', 'sp.student_id', '=', 'students.studentID')
                ->leftJoin('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                ->leftJoin('classes', 'subclasses.classID', '=', 'classes.classID')
                ->select(
                    'sp.id',
                    'sp.student_id',
                    'sp.time_mode',
                    'sp.days_count',
                    'sp.start_date',
                    'sp.end_date',
                    'sp.start_time',
                    'sp.end_time',
                    'sp.reason_type',
                    'sp.reason_description',
                    'sp.status',
                    'sp.hod_status',
                    'sp.principal_status',
                    'sp.final_status',
                    'sp.created_at',
                    'students.first_name as student_first_name',
                    'students.last_name as student_last_name',
                    'students.middle_name as student_middle_name',
                    'classes.class_name',
                    'subclasses.subclass_name'
                )
                ->orderBy('sp.created_at', 'desc')
                ->get()
                ->map(function($item) {
                    $studentName = trim(($item->student_first_name ?? '') . ' ' . ($item->student_middle_name ?? '') . ' ' . ($item->student_last_name ?? ''));
                    $item->student_name = $studentName ?: 'Student ID: ' . $item->student_id;
                    $item->duration = $item->time_mode === 'days' ? $item->days_count . ' days' : 'Hours';
                    $item->date_range = $item->time_mode === 'days'
                        ? ($item->start_date . ' to ' . $item->end_date)
                        : ($item->start_time . ' to ' . $item->end_time);
                    $item->reason_details = $item->reason_description;
                    $item->class_name = ($item->class_name ?? '') . ' ' . ($item->subclass_name ?? '');
                    return $item;
                });

            \Log::info('Staff permission requests loaded', ['count' => $requests->count()]);

            $pending = $requests->where('status', 'pending')->values();
            $approved = $requests->where('status', 'approved')->values();
            $rejected = $requests->where('status', 'rejected')->values();

            return response()->json([
                'success' => true,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading staff permission requests', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error loading requests: ' . $e->getMessage()]);
        }
    }

    public function searchStudentsForPermission(Request $request)
    {
        $query = $request->input('query');
        $schoolID = Session::get('schoolID');

        \Log::info('searchStudentsForPermission called', ['query' => $query, 'schoolID' => $schoolID]);

        try {
            $students = DB::table('students')
                ->where('students.schoolID', $schoolID)
                ->where(function($q) use ($query) {
                    $q->where('students.first_name', 'like', '%' . $query . '%')
                      ->orWhere('students.last_name', 'like', '%' . $query . '%');
                })
                ->leftJoin('subclasses', 'students.subclassID', '=', 'subclasses.subclassID')
                ->leftJoin('classes', 'subclasses.classID', '=', 'classes.classID')
                ->select(
                    'students.studentID',
                    'students.first_name',
                    'students.last_name',
                    'students.gender',
                    'students.photo',
                    DB::raw('CONCAT(classes.class_name, " ", subclasses.subclass_name) as class_name')
                )
                ->limit(10)
                ->get();

            \Log::info('Students found', ['count' => $students->count()]);

            return response()->json(['success' => true, 'students' => $students]);
        } catch (\Exception $e) {
            \Log::error('Error searching students', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error searching students: ' . $e->getMessage()]);
        }
    }

    public function storeStudentPermission(Request $request)
    {
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,studentID',
            'time_mode' => 'required|in:days,hours',
            'reason_type' => 'required|string',
            'reason_description' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        // Check if staff is authorized to request student permissions
        $isAuthorized = DB::table('student_permission_requesters')
            ->where('schoolID', $schoolID)
            ->where('user_id', $staffID)
            ->where('user_type', 'staff')
            ->where('is_active', true)
            ->exists();

        if (!$isAuthorized) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to request student permissions']);
        }

        $permissionData = [
            'schoolID' => $schoolID,
            'student_id' => $validated['student_id'],
            'enrollmentID' => app(\App\Services\StudentEnrollmentService::class)
                ->bestEnrollmentID($validated['student_id'], (int) $schoolID),
            'requester_id' => $staffID,
            'requester_type' => 'staff',
            'time_mode' => $validated['time_mode'],
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'status' => 'pending',
            'hod_status' => 'pending',
            'principal_status' => 'pending',
            'final_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($validated['time_mode'] === 'days') {
            $request->validate([
                'days_count' => 'required|integer|min:1',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);
            $permissionData['days_count'] = $request->days_count;
            $permissionData['start_date'] = $request->start_date;
            $permissionData['end_date'] = $request->end_date;
        } else {
            $request->validate([
                'start_date_hours' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time'
            ]);
            $permissionData['start_date_hours'] = $request->start_date_hours;
            $permissionData['start_time'] = $request->start_time;
            $permissionData['end_time'] = $request->end_time;
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/permissions'), $fileName);
            $permissionData['attachment'] = $fileName;
        }

        DB::table('student_permissions')->insert($permissionData);

        // Check for approval chain for student permissions
        $hasChain = DB::table('permission_approval_chains')
            ->where('schoolID', $schoolID)
            ->where('permission_type', 'student')
            ->exists();

        if ($hasChain) {
            // Get the inserted permission ID using insertGetId
            $permissionId = DB::table('student_permissions')
                ->where('schoolID', $schoolID)
                ->where('student_id', $validated['student_id'])
                ->where('requester_id', $staffID)
                ->where('requester_type', 'staff')
                ->orderBy('id', 'desc')
                ->value('id');

            \Log::info('Student permission inserted (Staff)', ['permissionId' => $permissionId, 'schoolID' => $schoolID]);

            if (!$permissionId) {
                \Log::error('Failed to get permission ID after insert (Staff)');
                return response()->json(['success' => true, 'message' => 'Student permission request submitted successfully']);
            }

            // Update with approval chain info
            DB::table('student_permissions')
                ->where('id', $permissionId)
                ->update([
                    'current_approval_order' => 1,
                    'is_chain_completed' => false
                ]);

            // Get the permission object
            $permission = DB::table('student_permissions')->where('id', $permissionId)->first();

            \Log::info('Permission object retrieved (Staff)', ['permission' => $permission, 'permissionId' => $permissionId]);

            if ($permission) {
                // Notify first approver using unified function
                app(\App\Http\Controllers\AdminController::class)->notifyNextApprover($permission, true);
            } else {
                \Log::error('Failed to retrieve permission object (Staff)', ['permissionId' => $permissionId]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Student permission request submitted successfully']);
    }

    public function getStudentPermissionDetails($id)
    {
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }

        $permission = DB::table('student_permissions as sp')
            ->where('sp.id', $id)
            ->where('sp.schoolID', $schoolID)
            ->where('sp.requester_id', $staffID)
            ->where('sp.requester_type', 'staff')
            ->leftJoin('students', 'sp.student_id', '=', 'students.studentID')
            ->select(
                'sp.*',
                'students.first_name as student_first_name',
                'students.last_name as student_last_name',
                'students.middle_name as student_middle_name'
            )
            ->first();

        if (!$permission) {
            return response()->json(['success' => false, 'message' => 'Permission not found']);
        }

        $studentName = trim(($permission->student_first_name ?? '') . ' ' . ($permission->student_middle_name ?? '') . ' ' . ($permission->student_last_name ?? ''));

        $returnDate = null;
        if ($permission->time_mode === 'days' && $permission->end_date) {
            $returnDate = \Carbon\Carbon::parse($permission->end_date)->addDay()->format('d M Y');
        }

        return response()->json([
            'success' => true,
            'permission' => [
                'id' => $permission->id,
                'status' => $permission->status,
                'requested_at' => $permission->created_at ? \Carbon\Carbon::parse($permission->created_at)->format('d M Y H:i') : 'N/A',
                'time_mode' => $permission->time_mode,
                'days_count' => $permission->days_count,
                'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('d M Y') : 'N/A',
                'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('d M Y') : 'N/A',
                'return_date' => $returnDate,
                'start_time' => $permission->start_time,
                'end_time' => $permission->end_time,
                'reason_type' => $permission->reason_type,
                'reason_description' => $permission->reason_description,
                'student_name' => $studentName
            ]
        ]);
    }

    public function getStudentPermissionProgress($id)
    {
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access']);
        }

        $permission = DB::table('student_permissions as sp')
            ->where('sp.id', $id)
            ->where('sp.schoolID', $schoolID)
            ->where('sp.requester_id', $staffID)
            ->where('sp.requester_type', 'staff')
            ->first();

        if (!$permission) {
            return response()->json(['success' => false, 'message' => 'Permission not found']);
        }

        $chain = DB::table('permission_approval_chains')
            ->where('schoolID', $schoolID)
            ->where('permission_type', 'student')
            ->orderBy('approval_order')
            ->get();

        return response()->json([
            'success' => true,
            'permission' => $permission,
            'chain' => $chain
        ]);
    }

    private function notifyNextStudentApprover($permissionId, $schoolID)
    {
        $permission = DB::table('student_permissions')->where('id', $permissionId)->first();
        if (!$permission) return;

        $chain = DB::table('permission_approval_chains')
            ->where('schoolID', $schoolID)
            ->where('permission_type', 'student')
            ->where('approval_order', $permission->current_approval_order)
            ->first();

        if (!$chain) {
            // No more steps in chain, notify Admin
            $school = DB::table('schools')->where('schoolID', $schoolID)->first();
            if ($school && $school->phone) {
                $smsService = new \App\Services\SmsService();
                $smsService->sendSms($school->phone, "Final approval required for a student permission request. Please login to ShuleXpert to review.");
            }
            return;
        }

        $phones = [];
        if ($chain->role_id) {
            $teacherPhones = DB::table('role_user')
                ->join('teachers', 'role_user.teacher_id', '=', 'teachers.id')
                ->where('role_user.role_id', $chain->role_id)
                ->pluck('teachers.phone_number')
                ->toArray();
            $phones = array_merge($phones, $teacherPhones);
        }

        if ($chain->profession_id) {
            $staffPhones = DB::table('other_staff')
                ->where('profession_id', $chain->profession_id)
                ->where('schoolID', $schoolID)
                ->pluck('phone_number')
                ->toArray();
            $phones = array_merge($phones, $staffPhones);
        }

        if ($chain->is_admin) {
            $school = DB::table('schools')->where('schoolID', $schoolID)->first();
            if ($school && $school->phone) {
                $phones[] = $school->phone;
            }
        }

        $smsService = new \App\Services\SmsService();
        foreach ($phones as $phone) {
            if ($phone) {
                $smsService->sendSms($phone, "You have a student permission request to approve. Please login to ShuleXpert to review.");
            }
        }
    }
}
