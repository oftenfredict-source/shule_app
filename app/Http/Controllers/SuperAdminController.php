<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolRegistrationRequest;
use App\Models\OtpPhoneBlock;
use App\Models\SchoolAttendanceSmsSchedule;
use App\Models\SchoolFingerprintAttendanceSmsSchedule;
use App\Models\ClassModel;
use App\Services\ParentFingerprintPortalService;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\Watchman;
use App\Models\ParentModel;
use App\Models\Role;
use App\Models\StaffProfession;
use App\Models\SystemAlert;
use App\Models\User;
use App\Models\UserLog;
use App\Models\SystemSetting;
use App\Models\WebVisitor;
use App\Models\UserActivity;
use App\Models\BlockedIp;
use App\Models\Bank;
use App\Models\SchoolBankAccount;
use App\Models\CustomerRequest;
use App\Services\SmsService;
use App\Support\CenturyGothicPdfFont;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SuperAdminController extends Controller
{
    private function ensureSuperAdmin()
    {
        if (Session::get('user_type') !== 'SuperAdmin') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access. Please login as Super Admin.'
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Unauthorized access. Please login as Super Admin.');
        }

        return null;
    }

    private function schoolPlaceholderUrl(): string
    {
        return asset('images/school-placeholder.svg');
    }

    private function resolveSchoolAssetUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $relative = ltrim($path, '/');
        $candidates = [
            public_path($relative),
        ];

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relative;
        }

        foreach ($candidates as $fullPath) {
            if (file_exists($fullPath) && is_file($fullPath)) {
                return asset($relative);
            }
        }

        return null;
    }

    private function schoolUploadPath(string $folder): string
    {
        // Always store under Laravel public/ so online-application cards and asset() URLs resolve.
        $uploadPath = public_path($folder);

        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        return $uploadPath;
    }

    private function deleteSchoolAsset(?string $storedPath, string $folder): void
    {
        if (!$storedPath) {
            return;
        }

        $basename = basename($storedPath);
        $possiblePaths = [
            $this->schoolUploadPath($folder) . '/' . $basename,
            public_path(ltrim($storedPath, '/')),
        ];

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $possiblePaths[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($storedPath, '/');
        }

        foreach ($possiblePaths as $oldPath) {
            if ($oldPath && file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    public function dashboard()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $demoSchoolsCount = School::whereNull('environment')
            ->orWhere('environment', 'Demo')
            ->count();
        $liveSchoolsCount = School::where('environment', 'Live')->count();

        $customerInquiryUnread = Schema::hasTable('customer_requests')
            ? CustomerRequest::where('is_read', false)->count()
            : 0;

        return view('SuperAdmin.dashboard', compact('demoSchoolsCount', 'liveSchoolsCount', 'customerInquiryUnread'));
    }

    public function schools(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $query = School::query()->orderBy('schoolID', 'desc');
        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', '%' . $search . '%')
                    ->orWhere('registration_number', 'like', '%' . $search . '%');
            });
        }

        $schools = $query->paginate(10)->withQueryString();
        $schools->getCollection()->transform(function ($school) {
            $school->logo_url = $this->resolveSchoolAssetUrl($school->school_logo) ?? $this->schoolPlaceholderUrl();
            $school->stamp_url = $this->resolveSchoolAssetUrl($school->school_stamp);
            $school->sign_url = $this->resolveSchoolAssetUrl($school->school_signature);
            $school->org_logo_url = $this->resolveSchoolAssetUrl($school->organization_logo);

            return $school;
        });

        $studentsBySchool = DB::table('students')
            ->select('schoolID', DB::raw('COUNT(*) as total'))
            ->groupBy('schoolID')
            ->pluck('total', 'schoolID');

        $teachersBySchool = DB::table('teachers')
            ->select('schoolID', DB::raw('COUNT(*) as total'))
            ->groupBy('schoolID')
            ->pluck('total', 'schoolID');

        $staffBySchool = DB::table('other_staff')
            ->select('schoolID', DB::raw('COUNT(*) as total'))
            ->groupBy('schoolID')
            ->pluck('total', 'schoolID');

        $parentsBySchool = DB::table('parents')
            ->select('schoolID', DB::raw('COUNT(*) as total'))
            ->groupBy('schoolID')
            ->pluck('total', 'schoolID');

        $classesBySchool = DB::table('classes')
            ->select('schoolID', 'class_name')
            ->get()
            ->groupBy('schoolID');

        $subclassesBySchool = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->select('classes.schoolID', 'subclasses.subclass_name', 'classes.class_name')
            ->get()
            ->map(function($item) {
                $subName = trim($item->subclass_name);
                $item->full_name = $item->class_name . ($subName ? ' ' . $subName : '');
                return $item;
            })
            ->groupBy('schoolID');

        return view('SuperAdmin.schools', compact(
            'schools', 'studentsBySchool', 'teachersBySchool', 'staffBySchool', 
            'parentsBySchool', 'classesBySchool', 'subclassesBySchool'
        ));
    }

    /**
     * Public school registration requests (landing form) - pending / ready / rejected.
     * Verify → ready only; does NOT create login credentials.
     */
    public function schoolRegistrationRequests(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $status = $request->get('status', 'pending');
        $query = SchoolRegistrationRequest::query()->orderByDesc('id');
        if (in_array($status, ['pending', 'ready', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(30)->withQueryString();
        $counts = [
            'pending' => SchoolRegistrationRequest::where('status', 'pending')->count(),
            'ready' => SchoolRegistrationRequest::where('status', 'ready')->count(),
            'rejected' => SchoolRegistrationRequest::where('status', 'rejected')->count(),
            'all' => SchoolRegistrationRequest::count(),
        ];
        $publicRegisterUrl = 'https://test.shulexpert.com/register-school';
        $blockedOtpPhones = OtpPhoneBlock::query()
            ->forContext(OtpPhoneBlock::CONTEXT_SCHOOL_REGISTRATION)
            ->blocked()
            ->orderByDesc('blocked_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('SuperAdmin.school_registration_requests', compact(
            'requests',
            'counts',
            'status',
            'publicRegisterUrl',
            'blockedOtpPhones'
        ));
    }

    public function releaseBlockedRegistrationOtpPhone(Request $request, $id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $row = OtpPhoneBlock::query()
            ->forContext(OtpPhoneBlock::CONTEXT_SCHOOL_REGISTRATION)
            ->findOrFail($id);

        $row->is_blocked = false;
        $row->otp_send_count = 0;
        $row->blocked_at = null;
        $row->block_reason = null;
        $row->released_at = now();
        $row->released_by = Session::get('userID') ?: Session::get('id');
        $row->save();

        return back()->with(
            'success',
            'Phone ' . $row->phone . ' has been released and can receive registration OTPs again.'
        );
    }

    public function verifySchoolRegistrationRequest(Request $request, $id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $row = SchoolRegistrationRequest::findOrFail($id);
        if ($row->status === 'ready') {
            return back()->with('success', 'Request is already marked Ready.');
        }

        $row->status = 'ready';
        $row->verified_at = now();
        $row->verified_by = Session::get('userID') ?: Session::get('id');
        if ($request->filled('admin_notes')) {
            $row->admin_notes = $request->admin_notes;
        }
        $row->save();

        return back()->with(
            'success',
            'School request marked Ready. No login credentials were created - register the school manually when ready to onboard.'
        );
    }

    public function rejectSchoolRegistrationRequest(Request $request, $id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $row = SchoolRegistrationRequest::findOrFail($id);
        $row->status = 'rejected';
        $row->verified_at = now();
        $row->verified_by = Session::get('userID') ?: Session::get('id');
        $row->admin_notes = $request->input('admin_notes', $row->admin_notes);
        $row->save();

        return back()->with('success', 'School registration request rejected.');
    }

    public function changePasswordForm()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        return view('SuperAdmin.change_password');
    }

    public function changePassword(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }
            return redirect()->back()->withErrors($validator);
        }

        $user = User::find(Session::get('userID'));
        if (!$user || $user->user_type !== 'SuperAdmin') {
            Session::flush();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired. Please login again.'], 401);
            }
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
            }
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
        }
        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function updateSchoolSettings(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'environment' => 'nullable|in:Demo,Live',
            'status' => 'required|in:Active,Inactive',
            'two_factor_enabled' => 'nullable|boolean',
            'must_change_default_password' => 'nullable|boolean',
            'senderID' => 'nullable|string|max:20',
            'sms_enabled' => 'nullable|boolean',
            'student_portal_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $environment = $request->input('environment');
        if (!$environment) {
            $environment = 'Demo';
        }

        $school->environment = $environment;
        $school->status = $request->status;
        $school->two_factor_enabled = (bool)($request->input('two_factor_enabled', false));
        $school->must_change_default_password = (bool)($request->input('must_change_default_password', false));
        $school->senderID = $request->senderID;
        $school->sms_enabled = $request->boolean('sms_enabled', true);
        if ($request->exists('student_portal_enabled')) {
            $school->student_portal_enabled = $request->boolean('student_portal_enabled');
        }
        $school->save();

        return response()->json([
            'success' => true,
            'message' => 'School settings updated successfully.',
            'school' => [
                'schoolID' => $school->schoolID,
                'environment' => $school->environment ?: 'Demo',
                'status' => $school->status,
                'two_factor_enabled' => (bool)$school->two_factor_enabled,
                'must_change_default_password' => (bool)$school->must_change_default_password,
                'senderID' => $school->senderID,
                'sms_enabled' => (bool)$school->sms_enabled,
                'student_portal_enabled' => (bool)$school->student_portal_enabled,
            ]
        ]);
    }

    public function updateSchoolLogo(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        if (!$request->hasFile('school_logo')) {
            $errCode = isset($_FILES['school_logo']['error']) ? $_FILES['school_logo']['error'] : 'MISSING_ENTIRELY';
            $errMsg = "No valid file received. PHP Error Code: " . $errCode . " | Web Limit: " . ini_get('upload_max_filesize') . " | Post: " . ini_get('post_max_size');
            return response()->json(['success' => false, 'errors' => ['school_logo' => [$errMsg]]], 422);
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'school_logo' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        // Determine upload path - Robust strategy for Main Domain and Subdomains
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/logos';
        
        // If scriptDir/logos doesn't exist or isn't writable, fallback to other common paths
        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/logos' : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path('logos');
            }
        }

        if ($uploadPath && !file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        if ($school->school_logo) {
            $possibleOldPaths = [
                $uploadPath . '/' . basename($school->school_logo),
                public_path($school->school_logo),
                (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $school->school_logo : null),
            ];
            foreach ($possibleOldPaths as $oldPath) {
                if ($oldPath && file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        $logo = $request->file('school_logo');
        $filename = time() . '_' . $logo->getClientOriginalName();
        $logo->move($uploadPath, $filename);
        $school->school_logo = 'logos/' . $filename;
        $school->save();

        return response()->json([
            'success' => true,
            'message' => 'School logo updated successfully.',
            'logo_url' => asset($school->school_logo),
        ]);
    }

    public function updateSchoolStamp(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        if (!$request->hasFile('school_stamp')) {
            $errCode = isset($_FILES['school_stamp']['error']) ? $_FILES['school_stamp']['error'] : 'MISSING_ENTIRELY';
            $errMsg = "No valid file received. PHP Error Code: " . $errCode . " | Web Limit: " . ini_get('upload_max_filesize') . " | Post: " . ini_get('post_max_size');
            return response()->json(['success' => false, 'errors' => ['school_stamp' => [$errMsg]]], 422);
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'school_stamp' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        // Determine upload path - Robust strategy for Main Domain and Subdomains
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/stamps';
        
        // If scriptDir/stamps doesn't exist or isn't writable, fallback to other common paths
        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/stamps' : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path('stamps');
            }
        }

        if ($uploadPath && !file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        if ($school->school_stamp) {
            $possibleOldPaths = [
                $uploadPath . '/' . basename($school->school_stamp),
                public_path($school->school_stamp),
                (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $school->school_stamp : null),
            ];
            foreach ($possibleOldPaths as $oldPath) {
                if ($oldPath && file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        $stamp = $request->file('school_stamp');
        $filename = time() . '_' . $stamp->getClientOriginalName();
        $stamp->move($uploadPath, $filename);
        $school->school_stamp = 'stamps/' . $filename;
        $school->save();

        return response()->json([
            'success' => true,
            'message' => 'School stamp updated successfully.',
            'stamp_url' => asset($school->school_stamp),
        ]);
    }

    public function updateSchoolSignature(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        if (!$request->hasFile('school_signature')) {
            $errCode = isset($_FILES['school_signature']['error']) ? $_FILES['school_signature']['error'] : 'MISSING_ENTIRELY';
            $errMsg = "No valid file received. PHP Error Code: " . $errCode . " | Web Limit: " . ini_get('upload_max_filesize') . " | Post: " . ini_get('post_max_size');
            return response()->json(['success' => false, 'errors' => ['school_signature' => [$errMsg]]], 422);
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'school_signature' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        // Determine upload path - Robust strategy for Main Domain and Subdomains
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/signatures';
        
        // If scriptDir/signatures doesn't exist or isn't writable, fallback to other common paths
        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/signatures' : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path('signatures');
            }
        }

        if ($uploadPath && !file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        if ($school->school_signature) {
            $possibleOldPaths = [
                $uploadPath . '/' . basename($school->school_signature),
                public_path($school->school_signature),
                (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $school->school_signature : null),
            ];
            foreach ($possibleOldPaths as $oldPath) {
                if ($oldPath && file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        $signature = $request->file('school_signature');
        $filename = time() . '_' . $signature->getClientOriginalName();
        $signature->move($uploadPath, $filename);
        $school->school_signature = 'signatures/' . $filename;
        $school->save();

        return response()->json([
            'success' => true,
            'message' => 'School signature updated successfully.',
            'signature_url' => asset($school->school_signature),
        ]);
    }

    public function userPassword()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('school_name', 'asc')->get(['schoolID', 'school_name', 'school_logo', 'registration_number']);
        $userTypes = ['All', 'Admin', 'Teacher', 'Staff', 'Watchman', 'parent'];

        return view('SuperAdmin.user_password', compact('schools', 'userTypes'));
    }

    public function customerCare()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('school_name', 'asc')->get(['schoolID', 'school_name', 'school_logo', 'registration_number']);
        $userTypes = ['All', 'Admin', 'Teacher', 'Staff', 'Watchman', 'parent'];

        return view('SuperAdmin.customer_care', compact('schools', 'userTypes'));
    }

    public function systemAlerts()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('school_name', 'asc')->get(['schoolID', 'school_name', 'school_logo', 'registration_number']);
        $userTypes = ['Admin', 'Teacher', 'Staff', 'parent'];

        return view('SuperAdmin.system_alerts', compact('schools', 'userTypes'));
    }

    public function getSystemAlertOptions(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        try {
            $validator = Validator::make($request->all(), [
                'schoolID' => 'required|integer|exists:schools,schoolID',
                'user_type' => 'required|string|in:Admin,Teacher,Staff,parent',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $schoolID = (int) $request->schoolID;
            $userType = $request->user_type;

            if ($userType === 'Teacher') {
                $roles = Role::where('schoolID', $schoolID)
                    ->orderBy('role_name')
                    ->get(['id', 'role_name', 'name']);

                $roles = $roles->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'name' => $r->role_name,
                    ];
                });

                return response()->json(['success' => true, 'roles' => $roles]);
            }

            if ($userType === 'Staff') {
                $professions = StaffProfession::where('schoolID', $schoolID)
                    ->orderBy('name')
                    ->get(['id', 'name']);

                $professions = $professions->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                    ];
                });

                return response()->json(['success' => true, 'professions' => $professions]);
            }

            // Admin & parent: no roles/positions
            return response()->json(['success' => true, 'options' => []]);
        } catch (\Throwable $e) {
            Log::error('SuperAdmin getSystemAlertOptions failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function listSystemAlerts(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        try {
            $validator = Validator::make($request->all(), [
                'schoolID' => 'required|integer|exists:schools,schoolID',
                'user_type' => 'required|string|in:Admin,Teacher,Staff,parent',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $schoolID = (int) $request->schoolID;
            $userType = $request->user_type;

            $alerts = SystemAlert::where('schoolID', $schoolID)
                ->where('target_user_type', $userType)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json(['success' => true, 'alerts' => $alerts]);
        } catch (\Throwable $e) {
            Log::error('SuperAdmin listSystemAlerts failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeSystemAlert(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        try {

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'target_user_type' => 'required|string|in:Admin,Teacher,Staff,parent',
            'applies_to_all' => 'nullable|boolean',
            'target_role_id' => 'nullable|integer',
            'target_profession_id' => 'nullable|integer',
            'alert_type' => 'required|string|in:info,warning,success,danger',
            'message' => 'required|string|max:1500',
            'is_marquee' => 'nullable|boolean',
            'width' => 'nullable|string|max:20',
            'bg_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
            'is_bold' => 'nullable|boolean',
            'font_size' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['applies_to_all'] = (bool) ($request->input('applies_to_all', false));
        $data['is_marquee'] = (bool) ($request->input('is_marquee', false));
        $data['is_bold'] = (bool) ($request->input('is_bold', false));
        $data['font_size'] = $request->input('font_size');
        $data['is_active'] = (bool) ($request->input('is_active', true));
        $data['created_by'] = Session::get('userID');

        if (in_array($data['target_user_type'], ['Admin', 'parent'], true)) {
            $data['applies_to_all'] = true;
            $data['target_role_id'] = null;
            $data['target_profession_id'] = null;
        } elseif ($data['target_user_type'] === 'Teacher') {
            $data['target_profession_id'] = null;
            if ($data['applies_to_all']) {
                $data['target_role_id'] = null;
            }
        } else {
            $data['target_role_id'] = null;
            if ($data['applies_to_all']) {
                $data['target_profession_id'] = null;
            }
        }

        $alert = SystemAlert::create($data);

        return response()->json(['success' => true, 'alert' => $alert]);
        } catch (\Throwable $e) {
            Log::error('SuperAdmin storeSystemAlert failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSystemAlert(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        try {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:system_alerts,id',
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'target_user_type' => 'required|string|in:Admin,Teacher,Staff,parent',
            'applies_to_all' => 'nullable|boolean',
            'target_role_id' => 'nullable|integer',
            'target_profession_id' => 'nullable|integer',
            'alert_type' => 'required|string|in:info,warning,success,danger',
            'message' => 'required|string|max:1500',
            'is_marquee' => 'nullable|boolean',
            'width' => 'nullable|string|max:20',
            'bg_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
            'is_bold' => 'nullable|boolean',
            'font_size' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['applies_to_all'] = (bool) ($request->input('applies_to_all', false));
        $data['is_marquee'] = (bool) ($request->input('is_marquee', false));
        $data['is_bold'] = (bool) ($request->input('is_bold', false));
        $data['font_size'] = $request->input('font_size');
        $data['is_active'] = (bool) ($request->input('is_active', true));

        if (in_array($data['target_user_type'], ['Admin', 'parent'], true)) {
            $data['applies_to_all'] = true;
            $data['target_role_id'] = null;
            $data['target_profession_id'] = null;
        } elseif ($data['target_user_type'] === 'Teacher') {
            $data['target_profession_id'] = null;
            if ($data['applies_to_all']) {
                $data['target_role_id'] = null;
            }
        } else {
            $data['target_role_id'] = null;
            if ($data['applies_to_all']) {
                $data['target_profession_id'] = null;
            }
        }

        $alert = SystemAlert::find($data['id']);
        $alert->update($data);

        return response()->json(['success' => true, 'alert' => $alert]);
        } catch (\Throwable $e) {
            Log::error('SuperAdmin updateSystemAlert failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSystemAlert($id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        try {

        $alert = SystemAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
        }

        $alert->delete();

        return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('SuperAdmin deleteSystemAlert failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSchoolUsers(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'user_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolID = (int) $request->schoolID;
        $filterType = $request->input('user_type', 'All');

        $school = School::find($schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $logoUrl = $school->school_logo ? asset($school->school_logo) : null;
        $schoolName = $school->school_name;

        $rows = [];

        // Admin
        if ($filterType === 'All' || $filterType === 'Admin') {
            $adminUsername = $school->registration_number;
            if ($adminUsername) {
                $rows[] = [
                    'user_type' => 'Admin',
                    'username' => $adminUsername,
                    'phone' => $school->phone,
                    'schoolID' => $schoolID,
                    'school_name' => $schoolName,
                    'school_logo' => $logoUrl,
                ];
            }
        }

        // Teacher
        if ($filterType === 'All' || $filterType === 'Teacher') {
            $teachers = Teacher::where('schoolID', $schoolID)
                ->select('employee_number', 'phone_number')
                ->orderBy('employee_number')
                ->get();
            foreach ($teachers as $t) {
                $rows[] = [
                    'user_type' => 'Teacher',
                    'username' => $t->employee_number,
                    'phone' => $t->phone_number,
                    'schoolID' => $schoolID,
                    'school_name' => $schoolName,
                    'school_logo' => $logoUrl,
                ];
            }
        }

        // Staff
        if ($filterType === 'All' || $filterType === 'Staff') {
            $staff = OtherStaff::where('schoolID', $schoolID)
                ->select('employee_number', 'phone_number')
                ->orderBy('employee_number')
                ->get();
            foreach ($staff as $s) {
                $rows[] = [
                    'user_type' => 'Staff',
                    'username' => $s->employee_number,
                    'phone' => $s->phone_number,
                    'schoolID' => $schoolID,
                    'school_name' => $schoolName,
                    'school_logo' => $logoUrl,
                ];
            }
        }

        // Watchman
        if ($filterType === 'All' || $filterType === 'Watchman') {
            $watchmen = Watchman::where('schoolID', $schoolID)
                ->select('phone_number')
                ->orderBy('id', 'desc')
                ->get();
            foreach ($watchmen as $w) {
                $rows[] = [
                    'user_type' => 'Watchman',
                    'username' => $w->phone_number,
                    'phone' => $w->phone_number,
                    'schoolID' => $schoolID,
                    'school_name' => $schoolName,
                    'school_logo' => $logoUrl,
                ];
            }
        }

        // Parent
        if ($filterType === 'All' || $filterType === 'parent') {
            $parents = ParentModel::where('schoolID', $schoolID)
                ->select('phone')
                ->orderBy('parentID', 'desc')
                ->get();
            foreach ($parents as $p) {
                $rows[] = [
                    'user_type' => 'parent',
                    'username' => $p->phone,
                    'phone' => $p->phone,
                    'schoolID' => $schoolID,
                    'school_name' => $schoolName,
                    'school_logo' => $logoUrl,
                ];
            }
        }

        // cleanup
        $rows = array_values(array_filter($rows, function ($r) {
            $u = trim((string)($r['username'] ?? ''));
            return $u !== '' && strtolower($u) !== 'null' && strtolower($u) !== 'undefined';
        }));

        return response()->json([
            'success' => true,
            'school' => [
                'schoolID' => $schoolID,
                'school_name' => $schoolName,
                'senderID' => $school->senderID
            ],
            'users' => $rows
        ]);
    }

    public function sendUserCredentialsSms(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'user_type' => 'required|string',
            'username' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:500',
            'from' => 'nullable|string|max:11',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $first = $errors->first();
            return response()->json([
                'success' => false,
                'error' => $first ?: 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        try {
            $school = School::find((int) $request->schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'error' => 'School not found'], 404);
            }

            $userType = $request->user_type;
            $username = $request->username;
            $phone = $request->phone;

            $plainPassword = null;

            if ($userType === 'Admin') {
                $plainPassword = 'Admin@3345';
            } elseif ($userType === 'Teacher') {
                $t = Teacher::where('schoolID', $school->schoolID)->where('employee_number', $username)->first();
                $plainPassword = $t ? $t->last_name : null;
            } elseif ($userType === 'Staff') {
                $s = OtherStaff::where('schoolID', $school->schoolID)->where('employee_number', $username)->first();
                $plainPassword = $s ? $s->last_name : null;
            } elseif ($userType === 'Watchman') {
                $w = Watchman::where('schoolID', $school->schoolID)->where('phone_number', $username)->first();
                $plainPassword = $w ? $w->last_name : null;
            } elseif ($userType === 'parent') {
                $p = ParentModel::where('schoolID', $school->schoolID)->where('phone', $username)->first();
                $plainPassword = $p ? $p->last_name : null;
            }

            if (!$plainPassword) {
                Log::warning('SuperAdmin sendUserCredentialsSms: default password not found', [
                    'schoolID' => $school->schoolID,
                    'user_type' => $userType,
                    'username' => $username,
                ]);
                return response()->json(['success' => false, 'error' => 'Could not determine default password for this user.']);
            }

            $user = User::where('name', $username)->where('user_type', $userType)->first();
            if (!$user) {
                // Auto-create missing login account (common for old data)
                $email = null;
                $fingerprintId = null;

                if ($userType === 'Admin') {
                    $email = $school->email ?: ($school->registration_number . '@admin.local');
                } elseif ($userType === 'Teacher') {
                    $t = Teacher::where('schoolID', $school->schoolID)->where('employee_number', $username)->first();
                    if (!$t) {
                        return response()->json(['success' => false, 'error' => 'Teacher record not found for this username.']);
                    }
                    $email = $t->email ?: ($username . '@teacher.local');
                    $fingerprintId = $t->fingerprint_id ?? $t->id;
                } elseif ($userType === 'Staff') {
                    $s = OtherStaff::where('schoolID', $school->schoolID)->where('employee_number', $username)->first();
                    if (!$s) {
                        return response()->json(['success' => false, 'error' => 'Staff record not found for this username.']);
                    }
                    $email = $s->email ?: ($username . '@staff.local');
                    $fingerprintId = $s->fingerprint_id ?? $s->id;
                } elseif ($userType === 'Watchman') {
                    $w = Watchman::where('schoolID', $school->schoolID)->where('phone_number', $username)->first();
                    if (!$w) {
                        return response()->json(['success' => false, 'error' => 'Watchman record not found for this username.']);
                    }
                    $email = $w->email ?: ($username . '@watchman.local');
                } elseif ($userType === 'parent') {
                    $p = ParentModel::where('schoolID', $school->schoolID)->where('phone', $username)->first();
                    if (!$p) {
                        return response()->json(['success' => false, 'error' => 'Parent record not found for this username.']);
                    }
                    $email = $p->email ?: ($username . '@parent.local');
                }

                Log::warning('SuperAdmin sendUserCredentialsSms: login account missing; creating', [
                    'schoolID' => $school->schoolID,
                    'user_type' => $userType,
                    'username' => $username,
                    'email' => $email,
                ]);

                // If email is already used by another account, do not reuse it (users.email is unique)
                if ($email && User::where('email', $email)->exists()) {
                    Log::warning('SuperAdmin sendUserCredentialsSms: email already taken; setting email to null', [
                        'email' => $email,
                        'username' => $username,
                        'user_type' => $userType,
                    ]);
                    $email = null;
                }

                try {
                    $user = User::create([
                        'name' => $username,
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'user_type' => $userType,
                        'fingerprint_id' => $fingerprintId,
                    ]);
                } catch (QueryException $qe) {
                    // Retry without email on duplicate key edge-cases
                    Log::error('SuperAdmin sendUserCredentialsSms: user create query exception; retrying without email', [
                        'message' => $qe->getMessage(),
                        'username' => $username,
                        'user_type' => $userType,
                        'email' => $email,
                    ]);
                    $user = User::create([
                        'name' => $username,
                        'email' => null,
                        'password' => Hash::make($plainPassword),
                        'user_type' => $userType,
                        'fingerprint_id' => $fingerprintId,
                    ]);
                }
            }

            $user->password = Hash::make($plainPassword);
            $user->save();

            $message = $request->message;
            $message = str_replace(
                ['{username}', '{password}', '{school}'],
                [$username, $plainPassword, ($school->school_name ?? 'ShuleXpert')],
                $message
            );

            $smsService = new SmsService();
            $senderID = $request->input('from');
            $smsResult = $smsService->sendSms($phone, $message, $senderID);

            if (!($smsResult['success'] ?? false)) {
                Log::error('SuperAdmin sendUserCredentialsSms: gateway rejected/failed', [
                    'schoolID' => $school->schoolID,
                    'user_type' => $userType,
                    'username' => $username,
                    'phone' => $phone,
                    'sms_message' => $smsResult['message'] ?? null,
                    'http_code' => $smsResult['http_code'] ?? null,
                    'response' => $smsResult['response'] ?? null,
                ]);
                return response()->json([
                    'success' => false,
                    'error' => $smsResult['message'] ?? 'Failed to send SMS',
                    'http_code' => $smsResult['http_code'] ?? null,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('SuperAdmin sendUserCredentialsSms: exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function sendCustomerCareSms(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'user_type' => 'required|string',
            'username' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:500',
            'from' => 'nullable|string|max:11',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $first = $errors->first();
            return response()->json([
                'success' => false,
                'error' => $first ?: 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        try {
            $school = School::find((int) $request->schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'error' => 'School not found'], 404);
            }

            $phone = $request->phone;
            $message = $request->message;

            $message = str_replace(
                ['{username}', '{school}'],
                [$request->username, ($school->school_name ?? 'ShuleXpert')],
                $message
            );

            $smsService = new SmsService();
            $senderID = $request->input('from');
            $smsResult = $smsService->sendSms($phone, $message, $senderID);

            if (!($smsResult['success'] ?? false)) {
                Log::error('SuperAdmin sendCustomerCareSms: gateway rejected/failed', [
                    'schoolID' => $school->schoolID,
                    'user_type' => $request->user_type,
                    'username' => $request->username,
                    'phone' => $phone,
                    'sms_message' => $smsResult['message'] ?? null,
                    'http_code' => $smsResult['http_code'] ?? null,
                    'response' => $smsResult['response'] ?? null,
                ]);
                return response()->json([
                    'success' => false,
                    'error' => $smsResult['message'] ?? 'Failed to send SMS',
                    'http_code' => $smsResult['http_code'] ?? null,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('SuperAdmin sendCustomerCareSms: exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateSchoolComprehensive(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'school_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'exam_center_number' => 'nullable|string|max:100',
            'school_type' => 'nullable|in:Primary,Secondary,Advanced',
            'ownership' => 'nullable|in:Public,Private',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'environment' => 'nullable|in:Demo,Live',
            'status' => 'required|in:Active,Inactive',
            'senderID' => 'nullable|string|max:20',
            'sms_enabled' => 'nullable|boolean',
            'student_portal_enabled' => 'nullable|boolean',
            'login_status' => 'nullable|in:0,1',
            'suspension_reason' => 'nullable|string|max:1000',
            'school_logo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'school_stamp' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'school_signature' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'is_under_organization' => 'nullable|boolean',
            'organization_name' => 'nullable|string|max:255',
            'organization_logo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'enable_social_media' => 'nullable|boolean',
            'social_media' => 'nullable|array',
            'social_media.*.platform' => 'nullable|string|max:40',
            'social_media.*.value' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $loginStatus = (int) $request->input('login_status', 1);

        if ($loginStatus === 0 && !$request->filled('suspension_reason')) {
            return response()->json([
                'success' => false,
                'message' => 'Suspension reason is required when login access is denied.',
            ], 422);
        }

        try {
            $school->school_name = $request->school_name;
            if ($request->exists('registration_number')) {
                $school->registration_number = $request->input('registration_number');
            }
            if ($request->exists('exam_center_number')) {
                $school->exam_center_number = $request->input('exam_center_number');
            }
            if ($request->filled('school_type')) {
                $school->school_type = $request->input('school_type');
            }
            if ($request->filled('ownership')) {
                $school->ownership = $request->input('ownership');
            }
            if ($request->exists('established_year')) {
                $school->established_year = $request->input('established_year') ?: null;
            }
            if ($request->exists('region')) {
                $school->region = $request->input('region');
            }
            if ($request->exists('district')) {
                $school->district = $request->input('district');
            }
            if ($request->exists('ward')) {
                $school->ward = $request->input('ward');
            }
            if ($request->exists('email')) {
                $school->email = $request->input('email');
            }
            if ($request->exists('phone')) {
                $school->phone = $request->input('phone');
            }
            if ($request->exists('address')) {
                $school->address = $request->input('address');
            }
            $school->environment = $request->input('environment', 'Demo');
            $school->status = $request->status;
            $school->two_factor_enabled = $request->boolean('two_factor_enabled');
            $school->must_change_default_password = $request->boolean('must_change_default_password');
            $school->is_under_organization = $request->boolean('is_under_organization');
            $school->organization_name = $school->is_under_organization
                ? $request->input('organization_name')
                : null;
            $school->enable_social_media = $request->boolean('enable_social_media');
            $school->social_media = $school->enable_social_media
                ? \App\Support\SchoolSocialMedia::normalize($request->input('social_media', []))
                : null;
            $school->senderID = $request->senderID;
            $school->sms_enabled = $request->boolean('sms_enabled', true);
            if ($request->exists('student_portal_enabled')) {
                $school->student_portal_enabled = $request->boolean('student_portal_enabled');
            }
            $school->login_status = $loginStatus;
            $school->suspension_reason = $loginStatus === 0 ? $request->suspension_reason : null;

            if ($request->hasFile('school_logo')) {
                $uploadPath = $this->schoolUploadPath('logos');
                $this->deleteSchoolAsset($school->school_logo, 'logos');
                $file = $request->file('school_logo');
                $filename = time() . '_logo_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move($uploadPath, $filename);
                $school->school_logo = 'logos/' . $filename;
            }

            if ($request->hasFile('organization_logo') && $school->is_under_organization) {
                $uploadPath = $this->schoolUploadPath('logos');
                $this->deleteSchoolAsset($school->organization_logo, 'logos');
                $file = $request->file('organization_logo');
                $filename = time() . '_org_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move($uploadPath, $filename);
                $school->organization_logo = 'logos/' . $filename;
            }

            if ($request->hasFile('school_stamp')) {
                $uploadPath = $this->schoolUploadPath('stamps');
                $this->deleteSchoolAsset($school->school_stamp, 'stamps');
                $file = $request->file('school_stamp');
                $filename = time() . '_stamp_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move($uploadPath, $filename);
                $school->school_stamp = 'stamps/' . $filename;
            }

            if ($request->hasFile('school_signature')) {
                $uploadPath = $this->schoolUploadPath('signatures');
                $this->deleteSchoolAsset($school->school_signature, 'signatures');
                $file = $request->file('school_signature');
                $filename = time() . '_sign_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $file->move($uploadPath, $filename);
                $school->school_signature = 'signatures/' . $filename;
            }

            $school->save();

            return response()->json(['success' => true, 'message' => 'School information updated successfully']);
        } catch (\Exception $e) {
            Log::error('School comprehensive update failed', [
                'schoolID' => $request->schoolID,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk enable/disable outbound SMS for all schools (implementation / ops control).
     */
    public function bulkUpdateSmsEnabled(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'sms_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $enabled = $request->boolean('sms_enabled');
        $updated = School::query()->update(['sms_enabled' => $enabled]);

        return response()->json([
            'success' => true,
            'message' => $enabled
                ? "SMS enabled for {$updated} school(s)."
                : "SMS disabled for {$updated} school(s). No client SMS will be sent.",
            'updated' => $updated,
            'sms_enabled' => $enabled,
        ]);
    }

    public function blockSchool(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'suspension_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $school->login_status = 0;
        $school->suspension_reason = $request->suspension_reason;
        $school->save();

        return response()->json([
            'success' => true,
            'message' => $school->school_name . ' has been blocked.',
        ]);
    }

    public function unblockSchool(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $school->login_status = 1;
        $school->suspension_reason = null;
        $school->save();

        return response()->json([
            'success' => true,
            'message' => $school->school_name . ' has been unblocked.',
        ]);
    }

    public function deleteSchool(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $school = School::find($request->schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found'], 404);
        }

        $name = $school->school_name;
        $school->delete();

        return response()->json([
            'success' => true,
            'message' => $name . ' has been deleted.',
        ]);
    }

    public function users()
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;
        $schools = School::all();
        return view('SuperAdmin.users', compact('schools'));
    }

    public function getUsersData(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $activityWindowSeconds = 120;
        $activityCutoff = now()->subSeconds($activityWindowSeconds);

        // Hard cleanup: close stale "active" login logs with no recent activity
        UserLog::whereNull('logout_at')
            ->where('login_at', '<', $activityCutoff)
            ->where(function ($q) use ($activityCutoff) {
                $q->whereDoesntHave('activities')
                    ->orWhereDoesntHave('activities', function ($aq) use ($activityCutoff) {
                        $aq->where('created_at', '>=', $activityCutoff);
                    });
            })
            ->update(['logout_at' => now()]);

        $usersTableHasSchoolId = Schema::hasColumn((new User)->getTable(), 'schoolID');
        $schoolFilterId = $request->filled('school_id') ? (int) $request->school_id : null;

        $query = $usersTableHasSchoolId ? User::with('school') : User::query();

        if ($schoolFilterId) {
            if ($usersTableHasSchoolId) {
                $query->where('schoolID', $schoolFilterId);
            } else {
                // users.schoolID does not exist locally — resolve school members via related tables + login logs.
                $school = School::find($schoolFilterId);
                $usernames = collect();
                if ($school && filled($school->registration_number)) {
                    $usernames->push($school->registration_number);
                }
                $usernames = $usernames
                    ->merge(Teacher::where('schoolID', $schoolFilterId)->pluck('employee_number'))
                    ->merge(OtherStaff::where('schoolID', $schoolFilterId)->pluck('employee_number'))
                    ->merge(Watchman::where('schoolID', $schoolFilterId)->pluck('phone_number'))
                    ->merge(ParentModel::where('schoolID', $schoolFilterId)->pluck('phone'))
                    ->map(fn ($v) => trim((string) $v))
                    ->filter(fn ($v) => $v !== '' && strtolower($v) !== 'null')
                    ->unique()
                    ->values();

                $userIdsFromLogs = UserLog::where('schoolID', $schoolFilterId)
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->filter()
                    ->values();

                $query->where(function ($q) use ($usernames, $userIdsFromLogs) {
                    $hasUsernames = $usernames->isNotEmpty();
                    $hasLogUsers = $userIdsFromLogs->isNotEmpty();

                    if ($hasUsernames) {
                        $q->whereIn('name', $usernames->all());
                    }
                    if ($hasLogUsers) {
                        $hasUsernames
                            ? $q->orWhereIn('id', $userIdsFromLogs->all())
                            : $q->whereIn('id', $userIdsFromLogs->all());
                    }
                    if (!$hasUsernames && !$hasLogUsers) {
                        $q->whereRaw('1 = 0');
                    }
                });
            }
        }

        if ($request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        $users = $query->get();
        $userIds = $users->pluck('id')->filter()->values();

        // Pull latest login log per user in bulk (avoid per-user queries)
        $latestLogs = collect();
        if ($userIds->isNotEmpty()) {
            $latestLogs = UserLog::whereIn('user_id', $userIds)
                ->with('school')
                ->orderByDesc('login_at')
                ->get()
                ->unique('user_id')
                ->keyBy('user_id');
        }

        // Strict realtime online users:
        // - login record still active (logout_at is null)
        // - has activity within the configured window
        $onlineUserIds = UserLog::whereIn('user_id', $userIds)
            ->whereNull('logout_at')
            ->whereHas('activities', function ($q) use ($activityCutoff) {
                $q->where('created_at', '>=', $activityCutoff);
            })
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
        $onlineUserMap = array_flip($onlineUserIds);

        // Ensure current logged-in session user is counted as online
        // even if activity row hasn't been flushed yet.
        $currentSessionUserId = Session::get('userID');
        $currentSessionLogId = Session::get('user_log_id');
        if ($currentSessionUserId && $currentSessionLogId) {
            $currentLogActive = UserLog::where('id', $currentSessionLogId)
                ->where('user_id', $currentSessionUserId)
                ->whereNull('logout_at')
                ->exists();
            if ($currentLogActive) {
                $onlineUserMap[$currentSessionUserId] = true;
            }
        }
        // Final fallback: if current request has an authenticated session user,
        // treat that specific user as online for this realtime response.
        if ($currentSessionUserId) {
            $onlineUserMap[$currentSessionUserId] = true;
        }

        // Pull latest activity timestamp per latest user_log_id in bulk
        $latestActivitiesByLogId = collect();
        $latestLogIds = $latestLogs->pluck('id')->filter()->values();
        if ($latestLogIds->isNotEmpty()) {
            $latestActivitiesByLogId = UserActivity::whereIn('user_log_id', $latestLogIds)
                ->select('user_log_id', DB::raw('MAX(created_at) as last_activity_at'))
                ->groupBy('user_log_id')
                ->pluck('last_activity_at', 'user_log_id');
        }

        // Resolve school fallback by username for legacy users missing schoolID in users/user_logs
        $adminUsernames = $users->where('user_type', 'Admin')->pluck('name')->filter()->unique()->values();
        $teacherUsernames = $users->where('user_type', 'Teacher')->pluck('name')->filter()->unique()->values();
        $staffUsernames = $users->where('user_type', 'Staff')->pluck('name')->filter()->unique()->values();

        $adminSchoolIdByUsername = $adminUsernames->isNotEmpty()
            ? School::whereIn('registration_number', $adminUsernames)->pluck('schoolID', 'registration_number')
            : collect();
        $teacherSchoolIdByUsername = $teacherUsernames->isNotEmpty()
            ? Teacher::whereIn('employee_number', $teacherUsernames)->pluck('schoolID', 'employee_number')
            : collect();
        $staffSchoolIdByUsername = $staffUsernames->isNotEmpty()
            ? OtherStaff::whereIn('employee_number', $staffUsernames)->pluck('schoolID', 'employee_number')
            : collect();

        $fallbackSchoolIds = collect()
            ->merge($adminSchoolIdByUsername->values())
            ->merge($teacherSchoolIdByUsername->values())
            ->merge($staffSchoolIdByUsername->values())
            ->filter()
            ->unique()
            ->values();
        $fallbackSchoolsById = $fallbackSchoolIds->isNotEmpty()
            ? School::whereIn('schoolID', $fallbackSchoolIds)->get()->keyBy('schoolID')
            : collect();

        $users = $users->map(function($user) use (
            $latestLogs,
            $latestActivitiesByLogId,
            $onlineUserMap,
            $currentSessionUserId,
            $adminSchoolIdByUsername,
            $teacherSchoolIdByUsername,
            $staffSchoolIdByUsername,
            $fallbackSchoolsById
        ) {
            $latestLog = $latestLogs->get($user->id);
            if (!$user->school && $latestLog && $latestLog->school) {
                $user->setRelation('school', $latestLog->school);
            }

            if (!$user->school) {
                $resolvedSchoolId = $user->schoolID ?? null;
                if (!$resolvedSchoolId && $latestLog) {
                    $resolvedSchoolId = $latestLog->schoolID;
                }
                if (!$resolvedSchoolId) {
                    if ($user->user_type === 'Admin') {
                        $resolvedSchoolId = $adminSchoolIdByUsername->get($user->name);
                    } elseif ($user->user_type === 'Teacher') {
                        $resolvedSchoolId = $teacherSchoolIdByUsername->get($user->name);
                    } elseif ($user->user_type === 'Staff') {
                        $resolvedSchoolId = $staffSchoolIdByUsername->get($user->name);
                    }
                }
                if ($resolvedSchoolId && $fallbackSchoolsById->has($resolvedSchoolId)) {
                    $user->setRelation('school', $fallbackSchoolsById->get($resolvedSchoolId));
                }
            }

            $isCurrentSessionUser = $currentSessionUserId && ((int) $user->id === (int) $currentSessionUserId);
            $user->is_online = $isCurrentSessionUser || isset($onlineUserMap[$user->id]);
            $user->photo_path = $latestLog ? $latestLog->photo_path : null;
            $user->last_ip = $latestLog ? ($latestLog->ip_address ?: 'N/A') : 'N/A';

            return $user;
        });

        $onlineCount = $users->where('is_online', true)->count();
        $offlineCount = $users->where('is_online', false)->count();

        // Filter by status if requested (this is done in collection for simplicity, or could be in query)
        if ($request->status) {
            $status = $request->status; // 'online' or 'offline'
            $users = $users->filter(function($u) use ($status) {
                return $status === 'online' ? $u->is_online : !$u->is_online;
            })->values();
        }

        return response()->json([
            'success' => true, 
            'data' => $users,
            'summary' => [
                'online' => $onlineCount,
                'offline' => $offlineCount,
            ]
        ]);
    }

    public function systemLogs()
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $loginPhotoCaptureEnabled = SystemSetting::isLoginPhotoCaptureEnabled();
        $loginLogsToday = UserLog::whereDate('login_at', today())->count();
        $webVisitorsToday = WebVisitor::uniqueIpCountForDate(today());

        return view('SuperAdmin.system_logs', compact(
            'loginPhotoCaptureEnabled',
            'loginLogsToday',
            'webVisitorsToday'
        ));
    }

    public function updateLoginPhotoCaptureSetting(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $enabled = $request->boolean('enabled');
        SystemSetting::setValue('login_photo_capture_enabled', $enabled ? '1' : '0');

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled
                ? 'Login photo capture enabled.'
                : 'Login photo capture disabled.',
        ]);
    }

    public function getSystemLogsData(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $query = UserLog::with('school');
        $recordsTotal = UserLog::count();
        $todayCount = UserLog::whereDate('login_at', today())->count();

        if ($request->start_date) {
            $query->whereDate('login_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('login_at', '<=', $request->end_date);
        }
        if ($request->username) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        // DataTables global search
        $searchValue = trim((string) $request->input('search.value', ''));
        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('username', 'like', "%{$searchValue}%")
                    ->orWhere('user_type', 'like', "%{$searchValue}%")
                    ->orWhere('ip_address', 'like', "%{$searchValue}%")
                    ->orWhere('device_brand', 'like', "%{$searchValue}%")
                    ->orWhere('os', 'like', "%{$searchValue}%")
                    ->orWhere('browser', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        // DataTables ordering
        $orderColumnIndex = (int) $request->input('order.0.column', 5);
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $columnMap = [
            0 => 'username',
            1 => 'username',
            2 => 'device_brand',
            3 => 'ip_address',
            4 => 'latitude',
            5 => 'login_at',
        ];
        $orderColumn = $columnMap[$orderColumnIndex] ?? 'login_at';

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        if ($length <= 0) {
            $length = 10;
        }
        $length = min($length, 100);

        $logs = $query
            ->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $blockedIps = BlockedIp::whereIn('ip_address', $logs->pluck('ip_address')->filter()->unique()->values())
            ->pluck('ip_address')
            ->flip();

        $data = $logs->map(function ($log) use ($blockedIps) {
            return [
                'id' => $log->id,
                'username' => $log->username,
                'user_type' => $log->user_type,
                'photo_path' => $log->photo_path,
                'device_type' => $log->device_type,
                'device_brand' => $log->device_brand,
                'os' => $log->os,
                'browser' => $log->browser,
                'ip_address' => $log->ip_address,
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
                'login_at' => $log->login_at,
                'is_blocked' => isset($blockedIps[$log->ip_address]),
                'school' => $log->school ? ['school_name' => $log->school->school_name] : null,
            ];
        })->values();

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'todayCount' => $todayCount,
            'data' => $data,
        ]);
    }

    public function getWebVisitorsData(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        // Today = unique IPs that visited (not page-hit count)
        $todayCount = WebVisitor::uniqueIpCountForDate(today());
        $recordsTotal = (int) WebVisitor::query()
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->distinct()
            ->count('ip_address');

        $base = WebVisitor::query();

        if ($request->start_date) {
            $base->whereDate('visited_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $base->whereDate('visited_at', '<=', $request->end_date);
        }

        $search = trim((string) ($request->username ?: $request->input('search.value', '')));
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('page_name', 'like', "%{$search}%")
                    ->orWhere('page_url', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('browser', 'like', "%{$search}%")
                    ->orWhere('os', 'like', "%{$search}%")
                    ->orWhere('device_type', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%");
            });
        }

        $grouped = (clone $base)
            ->select('ip_address')
            ->selectRaw('COUNT(*) as pages_count')
            ->selectRaw('MAX(visited_at) as last_visited')
            ->selectRaw('MAX(id) as latest_id')
            ->selectRaw('MAX(CASE WHEN email IS NOT NULL AND email != "" THEN email END) as email')
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->groupBy('ip_address');

        $recordsFiltered = (clone $grouped)->get()->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 6);
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderMap = [
            0 => 'ip_address',
            1 => 'email',
            2 => 'last_visited',
            3 => 'pages_count',
            4 => 'last_visited',
            5 => 'last_visited',
            6 => 'last_visited',
        ];
        $orderColumn = $orderMap[$orderColumnIndex] ?? 'last_visited';

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        if ($length <= 0) {
            $length = 10;
        }
        $length = min($length, 100);

        $rows = $grouped
            ->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $latestIds = $rows->pluck('latest_id')->filter()->values();
        $latestById = WebVisitor::whereIn('id', $latestIds)->get()->keyBy('id');

        $data = $rows->map(function ($row) use ($latestById) {
            $latest = $latestById->get($row->latest_id);
            $location = $latest ? $latest->locationLabel() : 'Unknown location';

            return [
                'id' => $row->latest_id,
                'ip_address' => $row->ip_address,
                'email' => $row->email ?: ($latest->email ?? null),
                'page_name' => $latest->page_name ?? 'Unknown page',
                'page_url' => $latest->page_url ?? '',
                'pages_count' => (int) $row->pages_count,
                'device_type' => $latest->device_type ?? 'Unknown',
                'browser' => $latest->browser ?? '-',
                'os' => $latest->os ?? '-',
                'city' => $latest->city ?? null,
                'region' => $latest->region ?? null,
                'country' => $latest->country ?? null,
                'location' => $location,
                'visited_at' => $row->last_visited,
            ];
        })->values();

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'todayCount' => $todayCount,
            'data' => $data,
        ]);
    }

    public function getWebVisitorDetails(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $ip = trim((string) $request->input('ip_address', ''));
        if ($ip === '') {
            return response()->json(['success' => false, 'message' => 'IP address is required'], 422);
        }

        $pages = WebVisitor::where('ip_address', $ip)
            ->orderByDesc('visited_at')
            ->limit(200)
            ->get();

        if ($pages->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
        }

        $latest = $pages->first();

        // Backfill location if missing
        if (!$latest->latitude && !$latest->country) {
            $geo = WebVisitor::resolveIpLocation($ip);
            if (!empty($geo['country']) || !empty($geo['latitude'])) {
                WebVisitor::where('ip_address', $ip)
                    ->whereNull('country')
                    ->update([
                        'country' => $geo['country'],
                        'region' => $geo['region'],
                        'city' => $geo['city'],
                        'latitude' => $geo['latitude'],
                        'longitude' => $geo['longitude'],
                    ]);

                $latest->country = $geo['country'];
                $latest->region = $geo['region'];
                $latest->city = $geo['city'];
                $latest->latitude = $geo['latitude'];
                $latest->longitude = $geo['longitude'];
            }
        }

        $emails = $pages->pluck('email')->filter()->unique()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'ip_address' => $ip,
                'email' => $emails->first(),
                'emails' => $emails,
                'device_type' => $latest->device_type,
                'browser' => $latest->browser,
                'os' => $latest->os,
                'country' => $latest->country,
                'region' => $latest->region,
                'city' => $latest->city,
                'latitude' => $latest->latitude,
                'longitude' => $latest->longitude,
                'location' => $latest->locationLabel(),
                'pages_count' => $pages->count(),
                'first_visit' => optional($pages->last())->visited_at,
                'last_visit' => optional($latest)->visited_at,
                'pages' => $pages->map(function ($page) {
                    return [
                        'id' => $page->id,
                        'page_name' => $page->page_name,
                        'page_url' => $page->page_url,
                        'email' => $page->email,
                        'referer' => $page->referer,
                        'visited_at' => $page->visited_at,
                    ];
                })->values(),
            ],
        ]);
    }

    public function getLogDetails($id)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $log = UserLog::with('activities')->find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Log not found'], 404);

        $log->is_blocked = BlockedIp::where('ip_address', $log->ip_address)->exists();

        return response()->json(['success' => true, 'data' => $log]);
    }

    public function blockIp(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        BlockedIp::updateOrCreate(
            ['ip_address' => $request->ip_address],
            ['reason' => $request->reason]
        );

        return response()->json(['success' => true, 'message' => 'IP Address blocked successfully']);
    }

    public function unblockIp(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        BlockedIp::where('ip_address', $request->ip_address)->delete();

        return response()->json(['success' => true, 'message' => 'IP Address unblocked successfully']);
    }

    public function attendanceSmsSchedule()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('school_name', 'asc')
            ->get(['schoolID', 'school_name', 'school_logo', 'registration_number', 'environment']);

        $schoolIds = $schools->pluck('schoolID');

        $scheduleRows = SchoolAttendanceSmsSchedule::whereIn('schoolID', $schoolIds)
            ->get()
            ->keyBy('schoolID');

        $fpScheduleRows = SchoolFingerprintAttendanceSmsSchedule::whereIn('schoolID', $schoolIds)
            ->get()
            ->keyBy('schoolID');

        return view('SuperAdmin.attendance_sms_schedule', compact('schools', 'scheduleRows', 'fpScheduleRows'));
    }

    public function getAttendanceSmsSchedule($schoolID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schedule = SchoolAttendanceSmsSchedule::where('schoolID', $schoolID)->first();

        return response()->json([
            'success' => true,
            'schoolID' => (int) $schoolID,
            'schedule' => $schedule?->schedule ?? SchoolAttendanceSmsSchedule::SCHEDULE_NONE,
            'send_method' => $schedule?->send_method ?? SchoolAttendanceSmsSchedule::METHOD_MANUAL,
            'send_time' => $schedule?->send_time ? Carbon::parse($schedule->send_time)->format('H:i') : null,
            'send_day_of_week' => $schedule?->send_day_of_week,
            'send_day_of_month' => $schedule?->send_day_of_month,
            'send_month' => $schedule?->send_month,
        ]);
    }

    public function saveAttendanceSmsSchedule(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schedule = $request->input('schedule');
        $sendMethod = $request->input('send_method', SchoolAttendanceSmsSchedule::METHOD_MANUAL);

        $rules = [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'schedule' => 'required|in:none,daily,weekly,monthly,yearly',
            'send_method' => 'required|in:manual,auto',
            'send_time' => 'nullable|date_format:H:i',
            'send_day_of_week' => 'nullable|integer|min:1|max:7',
            'send_day_of_month' => 'nullable|integer|min:1|max:31',
            'send_month' => 'nullable|integer|min:1|max:12',
        ];

        if ($schedule !== SchoolAttendanceSmsSchedule::SCHEDULE_NONE && $sendMethod === SchoolAttendanceSmsSchedule::METHOD_AUTO) {
            $rules['send_time'] = 'required|date_format:H:i';
            if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY) {
                $rules['send_day_of_week'] = 'required|integer|min:1|max:7';
            }
            if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY) {
                $rules['send_day_of_month'] = 'required|integer|min:1|max:31';
            }
            if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY) {
                $rules['send_month'] = 'required|integer|min:1|max:12';
                $rules['send_day_of_month'] = 'required|integer|min:1|max:31';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $userID = Session::get('userID');

        $payload = [
            'schedule' => $schedule,
            'send_method' => $schedule === SchoolAttendanceSmsSchedule::SCHEDULE_NONE
                ? SchoolAttendanceSmsSchedule::METHOD_MANUAL
                : $sendMethod,
            'send_time' => null,
            'send_day_of_week' => null,
            'send_day_of_month' => null,
            'send_month' => null,
            'updated_by' => $userID ? (int) $userID : null,
        ];

        if ($schedule !== SchoolAttendanceSmsSchedule::SCHEDULE_NONE && $sendMethod === SchoolAttendanceSmsSchedule::METHOD_AUTO) {
            $payload['send_time'] = $request->input('send_time') . ':00';
            if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY) {
                $payload['send_day_of_week'] = (int) $request->input('send_day_of_week');
            }
            if (in_array($schedule, [SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY, SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY], true)) {
                $payload['send_day_of_month'] = (int) $request->input('send_day_of_month');
            }
            if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY) {
                $payload['send_month'] = (int) $request->input('send_month');
            }
        }

        $existing = SchoolAttendanceSmsSchedule::where('schoolID', (int) $request->input('schoolID'))->first();
        if ($existing && (
            $existing->schedule !== $schedule
            || $existing->send_method !== $payload['send_method']
            || $existing->send_time !== $payload['send_time']
        )) {
            $payload['last_auto_period_key'] = null;
            $payload['last_auto_run_at'] = null;
        }

        SchoolAttendanceSmsSchedule::updateOrCreate(
            ['schoolID' => (int) $request->input('schoolID')],
            $payload
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance SMS schedule saved successfully.',
        ]);
    }

    public function getFingerprintAttendanceSmsSchedule($schoolID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schedule = SchoolFingerprintAttendanceSmsSchedule::where('schoolID', $schoolID)->first();

        return response()->json([
            'success' => true,
            'schoolID' => (int) $schoolID,
            'schedule' => $schedule?->schedule ?? SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE,
            'send_method' => $schedule?->send_method ?? SchoolFingerprintAttendanceSmsSchedule::METHOD_MANUAL,
            'send_time' => $schedule?->send_time ? Carbon::parse($schedule->send_time)->format('H:i') : null,
            'send_day_of_week' => $schedule?->send_day_of_week,
            'send_day_of_month' => $schedule?->send_day_of_month,
            'send_month' => $schedule?->send_month,
        ]);
    }

    public function saveFingerprintAttendanceSmsSchedule(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schedule = $request->input('schedule');
        $sendMethod = $request->input('send_method', SchoolFingerprintAttendanceSmsSchedule::METHOD_MANUAL);

        $rules = [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'schedule' => 'required|in:none,daily,weekly,monthly,yearly',
            'send_method' => 'required|in:manual,auto',
            'send_time' => 'nullable|date_format:H:i',
            'send_day_of_week' => 'nullable|integer|min:1|max:7',
            'send_day_of_month' => 'nullable|integer|min:1|max:31',
            'send_month' => 'nullable|integer|min:1|max:12',
        ];

        if ($schedule !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE && $sendMethod === SchoolFingerprintAttendanceSmsSchedule::METHOD_AUTO) {
            $rules['send_time'] = 'required|date_format:H:i';
            if ($schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY) {
                $rules['send_day_of_week'] = 'required|integer|min:1|max:7';
            }
            if ($schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY) {
                $rules['send_day_of_month'] = 'required|integer|min:1|max:31';
            }
            if ($schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY) {
                $rules['send_month'] = 'required|integer|min:1|max:12';
                $rules['send_day_of_month'] = 'required|integer|min:1|max:31';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $payload = [
            'schedule' => $schedule,
            'send_method' => $schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE
                ? SchoolFingerprintAttendanceSmsSchedule::METHOD_MANUAL
                : $sendMethod,
            'send_time' => null,
            'send_day_of_week' => null,
            'send_day_of_month' => null,
            'send_month' => null,
            'updated_by' => Session::get('userID'),
        ];

        if ($schedule !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE && $sendMethod === SchoolFingerprintAttendanceSmsSchedule::METHOD_AUTO) {
            $payload['send_time'] = $request->input('send_time').':00';
            if ($schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY) {
                $payload['send_day_of_week'] = (int) $request->input('send_day_of_week');
            }
            if (in_array($schedule, [SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY, SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY], true)) {
                $payload['send_day_of_month'] = (int) $request->input('send_day_of_month');
            }
            if ($schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY) {
                $payload['send_month'] = (int) $request->input('send_month');
            }
        }

        $existing = SchoolFingerprintAttendanceSmsSchedule::where('schoolID', (int) $request->input('schoolID'))->first();
        if ($existing && (
            $existing->schedule !== $schedule
            || $existing->send_method !== $payload['send_method']
            || $existing->send_time !== $payload['send_time']
        )) {
            $payload['last_auto_period_key'] = null;
            $payload['last_auto_run_at'] = null;
        }

        SchoolFingerprintAttendanceSmsSchedule::updateOrCreate(
            ['schoolID' => (int) $request->input('schoolID')],
            $payload
        );

        return response()->json([
            'success' => true,
            'message' => 'Fingerprint attendance SMS schedule saved successfully.',
        ]);
    }

    public function parentFingerprintPortalSettings()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('school_name', 'asc')
            ->get(['schoolID', 'school_name', 'school_logo', 'registration_number', 'environment']);

        return view('SuperAdmin.parent_fingerprint_portal', compact('schools'));
    }

    public function getParentFingerprintPortalSettings($schoolID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $school = School::where('schoolID', $schoolID)->first();
        if (! $school) {
            return response()->json(['success' => false, 'error' => 'School not found.'], 404);
        }

        $portalService = app(ParentFingerprintPortalService::class);
        $enabledMap = $portalService->getClassEnabledMap((int) $schoolID);

        $classes = ClassModel::where('schoolID', $schoolID)
            ->orderBy('class_name')
            ->get(['classID', 'class_name'])
            ->map(fn ($class) => [
                'classID' => (int) $class->classID,
                'class_name' => $class->class_name,
                'enabled' => $enabledMap[(int) $class->classID] ?? false,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'schoolID' => (int) $schoolID,
            'school_name' => $school->school_name,
            'classes' => $classes,
        ]);
    }

    public function saveParentFingerprintPortalSettings(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'schoolID' => 'required|integer|exists:schools,schoolID',
            'enabled_class_ids' => 'nullable|array',
            'enabled_class_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $schoolID = (int) $request->input('schoolID');
        $enabledClassIds = array_map('intval', (array) $request->input('enabled_class_ids', []));

        app(ParentFingerprintPortalService::class)->syncSchoolClasses($schoolID, $enabledClassIds);

        return response()->json([
            'success' => true,
            'message' => 'Parent fingerprint attendance settings saved successfully.',
        ]);
    }

    // ─── Bank Registration (integration partners) ───────────────────────────

    public function banks()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $banks = Bank::orderBy('bank_name')->get();
        return view('SuperAdmin.banks', compact('banks'));
    }

    public function storeBank(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:150|unique:banks,bank_name',
            'bank_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $bank = Bank::create([
            'bank_name' => trim($request->bank_name),
            'bank_code' => $request->bank_code ? strtoupper(trim($request->bank_code)) : null,
            'status' => $request->status ?: 'Active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank registered successfully.',
            'bank' => $bank,
        ]);
    }

    public function updateBank(Request $request, $bankID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $bank = Bank::find($bankID);
        if (!$bank) {
            return response()->json(['success' => false, 'error' => 'Bank not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:150|unique:banks,bank_name,' . $bankID . ',bankID',
            'bank_code' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $bank->update([
            'bank_name' => trim($request->bank_name),
            'bank_code' => $request->bank_code ? strtoupper(trim($request->bank_code)) : null,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank updated successfully.',
            'bank' => $bank->fresh(),
        ]);
    }

    public function deleteBank($bankID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $bank = Bank::find($bankID);
        if (!$bank) {
            return response()->json(['success' => false, 'error' => 'Bank not found.'], 404);
        }

        if ($bank->schoolBankAccounts()->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot delete bank with registered school accounts. Deactivate it instead.',
            ], 422);
        }

        $bank->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank deleted successfully.',
        ]);
    }

    // ─── School bank account verification ───────────────────────────────────

    public function bankAccountVerification()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('school_name')->get(['schoolID', 'school_name', 'phone']);
        return view('SuperAdmin.bank_account_verification', compact('schools'));
    }

    public function getSchoolBankAccounts(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schoolID = (int) $request->input('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'error' => 'School is required.'], 422);
        }

        $accounts = SchoolBankAccount::with('bank')
            ->where('schoolID', $schoolID)
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('accountID')
            ->get()
            ->map(function ($acc) {
                return [
                    'accountID' => $acc->accountID,
                    'account_number' => $acc->account_number,
                    'account_name' => $acc->account_name,
                    'status' => $acc->status,
                    'rejection_reason' => $acc->rejection_reason,
                    'otp_verified_at' => optional($acc->otp_verified_at)->format('Y-m-d H:i'),
                    'reviewed_at' => optional($acc->reviewed_at)->format('Y-m-d H:i'),
                    'bank_name' => $acc->bank->bank_name ?? '-',
                    'bank_code' => $acc->bank->bank_code ?? '',
                    'display_label' => $acc->displayLabel(),
                ];
            });

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    public function approveSchoolBankAccount(Request $request, $accountID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $account = SchoolBankAccount::with(['bank', 'school'])->find($accountID);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found.'], 404);
        }

        $account->update([
            'status' => SchoolBankAccount::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_by' => Session::get('user_id') ?? Session::get('id'),
            'reviewed_at' => now(),
        ]);

        $this->notifySchoolAboutBankAccount($account, true);

        return response()->json([
            'success' => true,
            'message' => 'Bank account approved. SMS notification sent to school.',
        ]);
    }

    public function rejectSchoolBankAccount(Request $request, $accountID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $account = SchoolBankAccount::with(['bank', 'school'])->find($accountID);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found.'], 404);
        }

        $account->update([
            'status' => SchoolBankAccount::STATUS_REJECTED,
            'rejection_reason' => trim($request->rejection_reason),
            'reviewed_by' => Session::get('user_id') ?? Session::get('id'),
            'reviewed_at' => now(),
        ]);

        $this->notifySchoolAboutBankAccount($account, false);

        return response()->json([
            'success' => true,
            'message' => 'Bank account rejected. SMS notification sent to school.',
        ]);
    }

    private function notifySchoolAboutBankAccount(SchoolBankAccount $account, bool $approved): void
    {
        $school = $account->school;
        if (!$school || empty($school->phone)) {
            return;
        }

        $label = $account->account_number;
        $bank = $account->bank->bank_code ?: ($account->bank->bank_name ?? 'Bank');

        if ($approved) {
            $msg = "ShuleXpert: Bank account {$label} ({$bank}) has been APPROVED. You can now use it for Multi Account Fees.";
        } else {
            $reason = $account->rejection_reason ?: 'No reason provided';
            $msg = "ShuleXpert: Bank account {$label} ({$bank}) was REJECTED. Reason: {$reason}";
        }

        try {
            (new SmsService())->sendSms($school->phone, $msg, $school->senderID, $school->schoolID);
        } catch (\Throwable $e) {
            Log::warning('Failed to SMS school about bank account decision', [
                'accountID' => $account->accountID,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Super Admin page: download NBC payment integration documentation (EN / SW).
     */
    public function nbcIntegrationDocs()
    {
        if ($deny = $this->ensureSuperAdmin()) {
            return $deny;
        }

        return view('SuperAdmin.nbc_integration_docs');
    }

    /**
     * Download NBC × ShuleXpert payment integration modality PDF.
     * lang=en|sw
     */
    public function downloadNbcIntegrationDocs(Request $request, string $lang = 'en')
    {
        if ($deny = $this->ensureSuperAdmin()) {
            return $deny;
        }

        $lang = strtolower($lang) === 'sw' ? 'sw' : 'en';

        $titles = [
            'en' => 'Payment Integration Modality Proposal - National Bank of Commerce (NBC)',
            'sw' => 'Pendekezo la Modality ya Uunganishaji wa Malipo - National Bank of Commerce (NBC)',
        ];

        $views = [
            'en' => 'SuperAdmin.pdf.nbc_payment_integration_en',
            'sw' => 'SuperAdmin.pdf.nbc_payment_integration_sw',
        ];

        $filenames = [
            'en' => 'ShuleXpert_NBC_Payment_Integration_Modality_EN.pdf',
            'sw' => 'ShuleXpert_NBC_Payment_Integration_Modality_SW.pdf',
        ];

        $data = [
            'title' => $titles[$lang],
            'version' => '1.0',
            'document_ref' => 'SX-NBC-PAY-' . date('Y'),
            'generated_at' => Carbon::now()->format('d M Y'),
            'lang' => $lang,
        ];

        try {
            $pdf = PDF::loadView($views[$lang], $data);
            // NOTE: do not call setOptions() here - it resets DomPDF font metrics and
            // breaks the registered Century Gothic font (logo is embedded as base64).
            CenturyGothicPdfFont::ensureRegistered($pdf->getDomPDF());
            $pdf->setPaper('A4', 'portrait');

            return $pdf->download($filenames[$lang]);
        } catch (\Throwable $e) {
            Log::error('NBC integration docs PDF failed', [
                'lang' => $lang,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('superadmin.nbc_integration_docs')
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function customerInquiries()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        return view('SuperAdmin.customer_inquiries', [
            'customerInquiryUnread' => CustomerRequest::where('is_read', false)->count(),
            'customerInquiryTotal' => CustomerRequest::count(),
        ]);
    }

    private function customerInquiryBaseQuery(Request $request)
    {
        $query = CustomerRequest::query();
        $period = $request->input('period', 'all');
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', $today);
                break;
            case 'month':
                $query->whereYear('created_at', $today->year)
                    ->whereMonth('created_at', $today->month);
                break;
            case 'year':
                $query->whereYear('created_at', $today->year);
                break;
            case 'range':
                if ($request->filled('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                break;
        }

        $status = $request->input('status', 'all');
        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        $search = trim((string) $request->input('filter_search', $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function getCustomerInquiriesData(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $recordsTotal = CustomerRequest::count();
        $query = $this->customerInquiryBaseQuery($request);
        $recordsFiltered = (clone $query)->count();

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 12);
        if ($length <= 0) {
            $length = 12;
        }
        $length = min($length, 100);

        $items = $query
            ->orderByDesc('created_at')
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function (CustomerRequest $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'subject' => $item->subject,
                    'message' => $item->message,
                    'message_preview' => \Illuminate\Support\Str::limit($item->message, 70),
                    'is_read' => $item->is_read,
                    'read_at' => $item->read_at?->format('d M Y H:i'),
                    'created_at' => $item->created_at?->format('d M Y H:i'),
                    'created_human' => $item->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'success' => true,
            'data' => $items,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'summary' => [
                'unread' => CustomerRequest::where('is_read', false)->count(),
                'total' => $recordsTotal,
            ],
        ]);
    }

    public function showCustomerInquiry($id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $item = CustomerRequest::findOrFail($id);
        $item->markAsRead();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'phone' => $item->phone,
                'subject' => $item->subject,
                'message' => $item->message,
                'is_read' => $item->is_read,
                'read_at' => $item->read_at?->format('d M Y H:i'),
                'created_at' => $item->created_at?->format('d M Y H:i'),
            ],
        ]);
    }

    public function markCustomerInquiryRead($id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $item = CustomerRequest::findOrFail($id);
        $item->markAsRead();

        return response()->json([
            'success' => true,
            'summary' => [
                'unread' => CustomerRequest::where('is_read', false)->count(),
            ],
        ]);
    }

    public function getCustomerInquiryNotifications()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $unread = CustomerRequest::where('is_read', false)->count();
        $recent = CustomerRequest::where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function (CustomerRequest $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'subject' => $item->subject,
                    'message_preview' => \Illuminate\Support\Str::limit($item->message, 80),
                    'created_at' => $item->created_at?->format('d M Y H:i'),
                    'created_human' => $item->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'unread' => $unread,
            'items' => $recent,
        ]);
    }
}
