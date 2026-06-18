<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\Watchman;
use App\Models\ParentModel;
use App\Models\Role;
use App\Models\StaffProfession;
use App\Models\SystemAlert;
use App\Models\User;
use App\Models\UserLog;
use App\Models\UserActivity;
use App\Models\BlockedIp;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

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
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/' . $folder;

        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $folder : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path($folder);
            }
        }

        if ($uploadPath && !file_exists($uploadPath)) {
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

        return view('SuperAdmin.dashboard', compact('demoSchoolsCount', 'liveSchoolsCount'));
    }

    public function schools()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $schools = School::orderBy('schoolID', 'desc')->get()->map(function ($school) {
            $school->logo_url = $this->resolveSchoolAssetUrl($school->school_logo) ?? $this->schoolPlaceholderUrl();
            $school->stamp_url = $this->resolveSchoolAssetUrl($school->school_stamp);
            $school->sign_url = $this->resolveSchoolAssetUrl($school->school_signature);

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
            'environment' => 'nullable|in:Demo,Live',
            'status' => 'required|in:Active,Inactive',
            'senderID' => 'nullable|string|max:20',
            'login_status' => 'nullable|in:0,1',
            'suspension_reason' => 'nullable|string|max:1000',
            'school_logo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'school_stamp' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'school_signature' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
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
            $school->environment = $request->input('environment', 'Demo');
            $school->status = $request->status;
            $school->two_factor_enabled = $request->boolean('two_factor_enabled');
            $school->must_change_default_password = $request->boolean('must_change_default_password');
            $school->senderID = $request->senderID;
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

        $query = User::with('school');

        if ($request->school_id) {
            $query->where('schoolID', $request->school_id);
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
        return view('SuperAdmin.system_logs');
    }

    public function getSystemLogsData(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) return $resp;

        $query = UserLog::with('school');
        $recordsTotal = UserLog::count();

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
            'data' => $data,
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
}
