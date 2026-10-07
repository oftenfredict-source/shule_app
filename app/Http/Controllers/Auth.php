<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\OtherStaff;
use App\Models\User;
use App\Models\Watchman;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\UserLog;
use App\Models\BlockedIp;
use App\Models\SystemSetting;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Storage;

class Auth extends Controller
{
    private function checkIpBlocked(Request $request)
    {
        $ip = $request->ip();
        $blocked = BlockedIp::where('ip_address', $ip)->first();
        if ($blocked) {
            $msg = "Your IP Address blocked: " . ($blocked->reason ?: 'No reason provided');
            if ($this->isAjax($request)) {
                return $this->jsonFail($msg, 403);
            }
            return redirect()->back()->with('error', $msg)->withInput($request->only('username'));
        }
        return null;
    }

    private function logUserLogin(User $user, Request $request, $schoolID = null)
    {
        $deviceType = 'Desktop';
        $brand = 'Personal Computer';
        $browser = 'Unknown';
        $os = 'Unknown';

        if (class_exists('Jenssegers\Agent\Agent')) {
            $agent = new Agent();
            if ($agent->isMobile()) $deviceType = 'Mobile';
            elseif ($agent->isTablet()) $deviceType = 'Tablet';
            $brand = $agent->device() ?: ($deviceType === 'Desktop' ? 'Personal Computer' : 'Unknown');
            $browser = $agent->browser();
            $os = $agent->platform();
        }

        $photoPath = null;
        if (SystemSetting::isLoginPhotoCaptureEnabled() && $request->filled('photo_data')) {
            $data = $request->input('photo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]); 

                if (in_array($type, ['jpg', 'jpeg', 'png'])) {
                    $data = base64_decode($data);
                    
                    // Determine upload path dynamically (handles Localhost, Subdomains, and Main Domain on CPanel)
                    $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
                    $uploadPath = $scriptDir . '/userImages/user_logs';
                    
                    if (!file_exists($uploadPath)) {
                        @mkdir($uploadPath, 0775, true);
                    }

                    $fileName = 'log_' . time() . '_' . Str::random(10) . '.' . $type;
                    file_put_contents($uploadPath . '/' . $fileName, $data);
                    
                    // The path we store in DB should be relative to the domain root
                    $photoPath = 'userImages/user_logs/' . $fileName;
                }
            }
        }

        $userLog = UserLog::create([
            'user_id' => $user->id,
            'schoolID' => $schoolID,
            'username' => $user->name,
            'user_type' => $user->user_type,
            'ip_address' => $request->ip(),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'device_type' => $deviceType,
            'device_brand' => $brand,
            'browser' => $browser,
            'os' => $os,
            'user_agent' => $request->userAgent(),
            'photo_path' => $photoPath,
            'login_at' => now(),
        ]);

        if ($userLog) {
            Session::put('user_log_id', $userLog->id);
        }
        return $userLog;
    }

    private function checkSchoolLoginStatus($schoolID, Request $request)
    {
        if (!$schoolID) return null;
        $school = School::find($schoolID);
        if (!$school) return null;

        if ($school->login_status === 0 || $school->login_status === false) {
            $reason = $school->suspension_reason ?: 'No reason provided.';
            $message = "Huduma imesitishwa sababu: {$reason}";
            
            if ($this->isAjax($request)) {
                return $this->jsonFail($message, 403);
            }
            return redirect()->back()
                ->with('error', $message)
                ->withInput($request->only('username'));
        }
        return null;
    }

    private function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson() || $request->expectsJson();
    }

    private function jsonFail(string $message, int $code = 400)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    private function sendOtp(string $phone, string $otp, string $context = 'Login')
    {
        $sms = new SmsService();
        $msg = "{$context} OTP: {$otp}. ShuleXpert";
        return $sms->sendSms($phone, $msg);
    }

    private function startOtpSession(array $payload): void
    {
        // payload: pending_user_id, pending_user_type, pending_username, schoolID?, teacherID?, staffID?, phone
        $otp = (string) random_int(100000, 999999);
        $token = (string) Str::uuid();

        Session::put('otp_pending', true);
        Session::put('otp_token', $token);
        Session::put('otp_code_hash', Hash::make($otp));
        Session::put('otp_expires_at', time() + (10 * 60));
        Session::put('otp_payload', $payload);

        $this->sendOtp($payload['phone'], $otp);
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) <= 5) return $digits;
        $start = substr($digits, 0, 6);
        $end = substr($digits, -2);
        return $start . '****' . $end;
    }

    private function requiresOtpForSchool(?int $schoolID): bool
    {
        if (!$schoolID) return false;
        $school = School::where('schoolID', $schoolID)->first();
        return $school ? (bool) $school->two_factor_enabled : false;
    }

    private function isLiveEnvironment(?int $schoolID): bool
    {
        if (!$schoolID) return false;
        $env = School::where('schoolID', $schoolID)->value('environment');
        return strtolower((string) $env) === 'live';
    }

    private function shouldForcePasswordChange(string $userType, ?int $schoolID, ?string $username): bool
    {
        if (!in_array($userType, ['Admin', 'Teacher', 'Staff'], true)) return false;
        if (!$schoolID) return false;
        $mustChangeDefaultPassword = (bool) School::where('schoolID', $schoolID)->value('must_change_default_password');
        if (!$mustChangeDefaultPassword) return false;
        if (!$username) return false;

        $user = User::where('name', $username)->where('user_type', $userType)->first();
        if (!$user) return false;

        if ($userType === 'Admin') {
            return Hash::check('3345', $user->password) || Hash::check('Admin@3345', $user->password);
        }

        if ($userType === 'Teacher') {
            $teacher = Teacher::where('employee_number', $username)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$teacher) return false;
            $default = (string) ($teacher->last_name ?? '') . 'staff';
            return $default !== 'staff' && Hash::check($default, $user->password);
        }

        if ($userType === 'Staff') {
            $staff = OtherStaff::where('employee_number', $username)
                ->where('schoolID', $schoolID)
                ->first();
            if (!$staff) return false;
            $default = (string) ($staff->last_name ?? '');
            return $default !== '' && Hash::check($default, $user->password);
        }

        return false;
    }

    private function hasOtpSkipToken($userLogin): bool
    {
        $cookieToken = request()->cookie('otp_remember_token');
        if (!$cookieToken || !$userLogin->otp_remember_token) return false;

        return hash_equals($userLogin->otp_remember_token, hash('sha256', $cookieToken));
    }

    private function setOtpSkipToken($userLogin): void
    {
        $token = Str::random(60);
        $userLogin->otp_remember_token = hash('sha256', $token);
        $userLogin->save();

        // Set cookie for 30 days
        cookie()->queue('otp_remember_token', $token, 30 * 24 * 60);
    }

    private function clearAuthSessionContext(): void
    {
        Session::forget([
            'schoolID',
            'teacherID',
            'teacher_name',
            'teacher_permissions',
            'teacher_roles',
            'staffID',
            'staff_name',
            'profession_id',
            'staff_permissions',
            'parentID',
            'watchmanID',
            'watchman_name',
            'force_password_change',
            'user_log_id',
            'otp_pending',
            'otp_token',
            'otp_code_hash',
            'otp_expires_at',
            'otp_payload',
        ]);
    }

    private function resolveUserByCredentials(string $username, string $password): array
    {
        $candidateUsernames = [$username];
        if (preg_match('/^(?:0|255|)\d{9}$/', $username)) {
            $purePhone = substr($username, -9);
            $candidateUsernames = array_values(array_unique([
                $username,
                '255' . $purePhone,
                '0' . $purePhone,
                $purePhone,
            ]));
        }

        $candidates = User::whereIn('name', $candidateUsernames)->get();
        $matched = $candidates->filter(function ($user) use ($password) {
            return Hash::check($password, $user->password);
        })->values();

        if ($matched->count() === 0) {
            return ['user' => null, 'error' => null];
        }
        if ($matched->count() > 1) {
            return ['user' => null, 'error' => 'Multiple accounts matched these credentials. Please contact system administrator.'];
        }

        return ['user' => $matched->first(), 'error' => null];
    }

    private function resolveTeacherForLogin(string $username, User $userLogin): ?Teacher
    {
        $query = Teacher::where('employee_number', $username);
        if (!empty($userLogin->schoolID)) {
            $query->where('schoolID', $userLogin->schoolID);
        }
        $teachers = $query->get();

        if ($teachers->count() === 1) return $teachers->first();

        if ($teachers->count() > 1) {
            $distinctSchoolCount = $teachers->pluck('schoolID')->filter()->unique()->count();
            if ($distinctSchoolCount > 1) return null;
            return $teachers->first();
        }

        // last fallback for legacy data where user.schoolID may be empty
        return Teacher::where('employee_number', $username)->first();
    }

    private function resolveStaffForLogin(string $username, User $userLogin): ?OtherStaff
    {
        $query = OtherStaff::where('employee_number', $username);
        if (!empty($userLogin->schoolID)) {
            $query->where('schoolID', $userLogin->schoolID);
        }
        $staffRows = $query->get();

        if ($staffRows->count() === 1) return $staffRows->first();

        if ($staffRows->count() > 1) {
            $distinctSchoolCount = $staffRows->pluck('schoolID')->filter()->unique()->count();
            if ($distinctSchoolCount > 1) return null;
            return $staffRows->first();
        }

        // last fallback for legacy data where user.schoolID may be empty
        return OtherStaff::where('employee_number', $username)->first();
    }

    /**
     * Show login form
     */
    public function login()
    {
        // Redirect if already logged in
        if (Session::has('user_type')) {
            $userType = Session::get('user_type');
            if ($userType === 'SuperAdmin') {
                return redirect()->route('superAdminDashboard');
            }
            if ($userType === 'Admin') {
                return redirect()->route('AdminDashboard');
            } elseif ($userType === 'Teacher') {
                return redirect()->route('teachersDashboard');
            } elseif ($userType === 'Staff') {
                return redirect()->route('staffDashboard');
            } elseif ($userType === 'Watchman') {
                return redirect()->route('watchman.visitors');
            }
        }

        return response()
            ->view('login', [
                'loginPhotoCaptureEnabled' => SystemSetting::isLoginPhotoCaptureEnabled(),
            ])
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Authenticate user
     */
    public function auth(Request $request)
    {
        // Check if IP is blocked
        if ($resp = $this->checkIpBlocked($request)) return $resp;

        // Ensure default SuperAdmin exists
        $defaultSuperAdminUsername = 'Admin.ShuleXpert.com';
        $defaultSuperAdminPassword = 'Admin@3345';
        User::firstOrCreate(
            ['name' => $defaultSuperAdminUsername],
            [
                'email' => 'superadmin@shulexpert.local',
                'password' => Hash::make($defaultSuperAdminPassword),
                'user_type' => 'SuperAdmin',
            ]
        );

        // Validation
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:1',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            if ($this->isAjax($request)) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('username'));
        }

        $username = $request->username;
        $password = $request->password;

        // Rate limiting - prevent brute force attacks
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            if ($this->isAjax($request)) {
                return $this->jsonFail("Too many login attempts. Please try again in {$seconds} seconds.", 429);
            }
            return redirect()->back()
                ->with('error', "Too many login attempts. Please try again in {$seconds} seconds.")
                ->withInput($request->only('username'));
        }

        $resolvedUser = $this->resolveUserByCredentials($username, $password);
        $userLogin = $resolvedUser['user'];
        $resolveError = $resolvedUser['error'];

        if ($resolveError) {
            RateLimiter::hit($key, 60);
            if ($this->isAjax($request)) {
                return $this->jsonFail($resolveError, 401);
            }
            return redirect()->back()
                ->with('error', $resolveError)
                ->withInput($request->only('username'));
        }

        if (!$userLogin) {
            RateLimiter::hit($key, 60); // 60 seconds lockout
            if ($this->isAjax($request)) {
                return $this->jsonFail('Incorrect username or password!', 401);
            }
            return redirect()->back()
                ->with('error', 'Incorrect username or password!')
                ->withInput($request->only('username'));
        }

        // Always use the exact stored username for downstream lookups
        $username = (string) $userLogin->name;

        // Clear cross-role leftovers before building a new auth session
        $this->clearAuthSessionContext();

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // We will log the login AFTER we determine the schoolID for specific roles
        // or immediately for SuperAdmin.
        
        // Handle different user types
        // If school has 2FA enabled, Admin/Teacher/Staff must confirm OTP before session is created.
        if (in_array($userLogin->user_type, ['Admin', 'Teacher', 'Staff'], true)) {
            $pending = [
                'pending_user_id' => $userLogin->id,
                'pending_user_type' => $userLogin->user_type,
                'pending_username' => $userLogin->name,
                'pending_user_email' => $userLogin->email,
                'remember_me' => $request->has('remember_me'),
            ];

            if ($userLogin->user_type === 'Admin') {
                $school = School::where('registration_number', $username)->first();
                if (!$school) {
                    if ($this->isAjax($request)) return $this->jsonFail('School not found for this admin account.', 404);
                    return redirect()->back()->with('error', 'School not found for this admin account.')->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($school->schoolID, $request)) return $resp;

                $skipOtp = $this->hasOtpSkipToken($userLogin);

                if (!$skipOtp && $this->requiresOtpForSchool((int) $school->schoolID)) {
                    $pending['schoolID'] = (int) $school->schoolID;
                    if (empty($school->phone)) {
                        if ($this->isAjax($request)) return $this->jsonFail('School phone number not set. Please contact admin.', 422);
                        return redirect()->back()->with('error', 'School phone number not set. Please contact admin.')->withInput($request->only('username'));
                    }
                    $pending['phone'] = $school->phone;
                    $this->startOtpSession($pending);
                    if ($this->isAjax($request)) {
                        return response()->json([
                            'success' => true,
                            'requires_otp' => true,
                            'otp_token' => Session::get('otp_token'),
                            'masked_phone' => $this->maskPhone($pending['phone']),
                            'expires_in' => 600,
                        ]);
                    }
                    return redirect()->back()->with('success', 'OTP sent. Please login again using the OTP flow.')->withInput($request->only('username'));
                }
            }

            if ($userLogin->user_type === 'Teacher') {
                $teacher = $this->resolveTeacherForLogin($username, $userLogin);
                if (!$teacher) {
                    if ($this->isAjax($request)) return $this->jsonFail('Teacher record not found.', 404);
                    return redirect()->back()->with('error', 'Teacher record not found.')->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($teacher->schoolID, $request)) return $resp;

                $skipOtp = $this->hasOtpSkipToken($userLogin);

                if (!$skipOtp && $this->requiresOtpForSchool((int) $teacher->schoolID)) {
                    $pending['schoolID'] = (int) $teacher->schoolID;
                    $pending['teacherID'] = (int) $teacher->id;
                    $pending['teacher_name'] = $teacher->first_name . ' ' . $teacher->last_name;
                    if (empty($teacher->phone_number)) {
                        if ($this->isAjax($request)) return $this->jsonFail('Teacher phone number not set. Please contact admin.', 422);
                        return redirect()->back()->with('error', 'Teacher phone number not set. Please contact admin.')->withInput($request->only('username'));
                    }
                    $pending['phone'] = $teacher->phone_number;
                    $this->startOtpSession($pending);
                    if ($this->isAjax($request)) {
                        return response()->json([
                            'success' => true,
                            'requires_otp' => true,
                            'otp_token' => Session::get('otp_token'),
                            'masked_phone' => $this->maskPhone($pending['phone']),
                            'expires_in' => 600,
                        ]);
                    }
                    return redirect()->back()->with('success', 'OTP sent. Please login again using the OTP flow.')->withInput($request->only('username'));
                }
            }

            if ($userLogin->user_type === 'Staff') {
                $staff = $this->resolveStaffForLogin($username, $userLogin);
                if (!$staff) {
                    if ($this->isAjax($request)) return $this->jsonFail('Staff record not found.', 404);
                    return redirect()->back()->with('error', 'Staff record not found.')->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($staff->schoolID, $request)) return $resp;

                $skipOtp = $this->hasOtpSkipToken($userLogin);

                if (!$skipOtp && $this->requiresOtpForSchool((int) $staff->schoolID)) {
                    $pending['schoolID'] = (int) $staff->schoolID;
                    $pending['staffID'] = (int) $staff->id;
                    $pending['staff_name'] = $staff->first_name . ' ' . $staff->last_name;
                    $pending['profession_id'] = $staff->profession_id;
                    if (empty($staff->phone_number)) {
                        if ($this->isAjax($request)) return $this->jsonFail('Staff phone number not set. Please contact admin.', 422);
                        return redirect()->back()->with('error', 'Staff phone number not set. Please contact admin.')->withInput($request->only('username'));
                    }
                    $pending['phone'] = $staff->phone_number;
                    $this->startOtpSession($pending);
                    if ($this->isAjax($request)) {
                        return response()->json([
                            'success' => true,
                            'requires_otp' => true,
                            'otp_token' => Session::get('otp_token'),
                            'masked_phone' => $this->maskPhone($pending['phone']),
                            'expires_in' => 600,
                        ]);
                    }
                    return redirect()->back()->with('success', 'OTP sent. Please login again using the OTP flow.')->withInput($request->only('username'));
                }
            }
        }

        switch ($userLogin->user_type) {
            case 'SuperAdmin':
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);

                $this->logUserLogin($userLogin, $request); // SuperAdmin

                if ($request->has('remember_me')) {
                    $this->setOtpSkipToken($userLogin);
                }

                $request->session()->regenerate();

                if ($this->isAjax($request)) {
                    return response()->json(['success' => true, 'redirect' => route('superAdminDashboard')]);
                }

                return redirect()->route('superAdminDashboard')
                    ->with('success', 'You have logged in successfully as Super Admin!');
                break;

            case 'Admin':
                $school = School::where('registration_number', $username)->first();

                if (!$school) {
                    return redirect()->back()
                        ->with('error', 'School not found for this admin account.')
                        ->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($school->schoolID, $request)) return $resp;

                // Set session data
                Session::put('schoolID', $school->schoolID);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);

                $this->logUserLogin($userLogin, $request, $school->schoolID);

                if ($request->has('remember_me')) {
                    $this->setOtpSkipToken($userLogin);
                }

                // Regenerate session ID for security
                $request->session()->regenerate();

                if ($this->shouldForcePasswordChange('Admin', (int) $school->schoolID, $username)) {
                    Session::put('force_password_change', true);
                }

                if (Session::get('force_password_change')) {
                    if ($this->isAjax($request)) {
                        return response()->json(['success' => true, 'redirect' => route('admin.change_password')]);
                    }
                    return redirect()->route('admin.change_password')->with('error', 'Please change your password to continue.');
                }

                if ($this->isAjax($request)) {
                    return response()->json(['success' => true, 'redirect' => route('AdminDashboard')]);
                }

                return redirect()->route('AdminDashboard')
                    ->with('success', 'You have logged in successfully as Admin!');
                break;

            case 'Teacher':
                $teacher = $this->resolveTeacherForLogin($username, $userLogin);

                if (!$teacher) {
                    return redirect()->back()
                        ->with('error', 'Teacher record not found.')
                        ->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($teacher->schoolID, $request)) return $resp;

                // Set session data
                Session::put('schoolID', $teacher->schoolID);
                Session::put('teacherID', $teacher->id);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);
                Session::put('teacher_name', $teacher->first_name . ' ' . $teacher->last_name);

                $this->logUserLogin($userLogin, $request, $teacher->schoolID);

                if ($request->has('remember_me')) {
                    $this->setOtpSkipToken($userLogin);
                }

                // Load teacher permissions
                $roleIds = DB::table('teachers')
                    ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                    ->where('role_user.teacher_id', $teacher->id)
                    ->pluck('role_user.role_id')
                    ->toArray();

                if (!empty($roleIds)) {
                    $permissions = DB::table('permissions')
                        ->whereIn('role_id', $roleIds)
                        ->pluck('name')
                        ->unique()
                        ->toArray();
                    Session::put('teacher_permissions', $permissions);
                } else {
                    Session::put('teacher_permissions', []);
                }

                // Load teacher roles if Spatie is installed
                if (class_exists(\Spatie\Permission\Models\Permission::class) && method_exists($teacher, 'roles')) {
                    $roles = $teacher->roles()->pluck('name')->toArray();
                    Session::put('teacher_roles', $roles);
                }

                // Regenerate session ID for security
                $request->session()->regenerate();

                if ($this->shouldForcePasswordChange('Teacher', (int) $teacher->schoolID, $username)) {
                    Session::put('force_password_change', true);
                }

                if (Session::get('force_password_change')) {
                    if ($this->isAjax($request)) {
                        return response()->json(['success' => true, 'redirect' => route('teacher.profile') . '#change-password']);
                    }
                    return redirect()->route('teacher.profile')->with('error', 'Please change your password to continue.');
                }

                if ($this->isAjax($request)) {
                    return response()->json(['success' => true, 'redirect' => route('teachersDashboard')]);
                }

                return redirect()->route('teachersDashboard')
                    ->with('success', 'You have logged in successfully as Teacher!');
                break;

            case 'Staff':
                $staff = $this->resolveStaffForLogin($username, $userLogin);

                if (!$staff) {
                    return redirect()->back()
                        ->with('error', 'Staff record not found.')
                        ->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($staff->schoolID, $request)) return $resp;

                // Set session data
                Session::put('schoolID', $staff->schoolID);
                Session::put('staffID', $staff->id);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);
                Session::put('staff_name', $staff->first_name . ' ' . $staff->last_name);
                Session::put('profession_id', $staff->profession_id);

                $this->logUserLogin($userLogin, $request, $staff->schoolID);

                if ($request->has('remember_me')) {
                    $this->setOtpSkipToken($userLogin);
                }

                // Load staff permissions based on profession
                if ($staff->profession_id) {
                    $permissions = \App\Models\StaffPermission::where('profession_id', $staff->profession_id)
                        ->pluck('name')
                        ->toArray();
                    Session::put('staff_permissions', $permissions);
                }

                // Regenerate session ID for security
                $request->session()->regenerate();

                if ($this->shouldForcePasswordChange('Staff', (int) $staff->schoolID, $username)) {
                    Session::put('force_password_change', true);
                }

                if (Session::get('force_password_change')) {
                    if ($this->isAjax($request)) {
                        return response()->json(['success' => true, 'redirect' => route('staff.profile')]);
                    }
                    return redirect()->route('staff.profile')->with('error', 'Please change your password to continue.');
                }

                if ($this->isAjax($request)) {
                    return response()->json(['success' => true, 'redirect' => route('staffDashboard')]);
                }

                return redirect()->route('staffDashboard')
                    ->with('success', 'You have logged in successfully as Staff!');
                break;

            case 'Watchman':
                $watchman = Watchman::where('phone_number', $username)->first();

                if (!$watchman) {
                    return redirect()->back()
                        ->with('error', 'Watchman record not found.')
                        ->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($watchman->schoolID, $request)) return $resp;

                Session::put('schoolID', $watchman->schoolID);
                Session::put('watchmanID', $watchman->id);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);
                Session::put('watchman_name', $watchman->first_name . ' ' . $watchman->last_name);

                $request->session()->regenerate();

                return redirect()->route('watchman.visitors')
                    ->with('success', 'You have logged in successfully as Watchman!');
                break;

            case 'parent':
            case 'Parent':
                $parent = ParentModel::where('phone', $username)->first();

                if (!$parent) {
                    // Try by user name if phone doesn't match directly
                    $parent = ParentModel::where('phone', 'like', '%' . substr($username, -9))->first();
                }

                if (!$parent) {
                    if ($this->isAjax($request)) {
                        return $this->jsonFail('Parent profile record not found. Please contact admin.', 404);
                    }
                    return redirect()->back()
                        ->with('error', 'Parent profile record not found.')
                        ->withInput($request->only('username'));
                }

                if ($resp = $this->checkSchoolLoginStatus($parent->schoolID, $request)) return $resp;

                // Set session data
                Session::put('parentID', $parent->parentID);
                Session::put('schoolID', $parent->schoolID);
                Session::put('user_type', $userLogin->user_type);
                Session::put('userID', $userLogin->id);
                Session::put('user_name', $userLogin->name);
                Session::put('user_email', $userLogin->email);

                // Regenerate session ID for security
                $request->session()->regenerate();

                if ($this->isAjax($request)) {
                    return response()->json(['success' => true, 'redirect' => route('parentDashboard')]);
                }

                return redirect()->route('parentDashboard')
                    ->with('success', 'You have logged in successfully as Parent!');
                break;

            default:
                return redirect()->back()
                    ->with('error', 'Invalid user type.')
                    ->withInput($request->only('username'));
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Get user ID before flushing session to clear the persistent OTP token if it exists
        $userId = Session::get('userID');
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->otp_remember_token = null;
                $user->last_activity_at = null; // immediately mark offline on logout
                $user->save();
            }
        }

        // Record logout in system logs
        $logId = Session::get('user_log_id');
        if ($logId) {
            \App\Models\UserLog::where('id', $logId)->update(['logout_at' => now()]);
        }

        // Forget the persistent skip cookie
        cookie()->queue(cookie()->forget('otp_remember_token'));

        // Clear all sessions
        Session::flush();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully.')
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|min:4|max:10',
            'otp_token' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!Session::get('otp_pending') || !Session::get('otp_token') || !Session::get('otp_code_hash')) {
            return $this->jsonFail('OTP session expired. Please login again.', 401);
        }

        if ((string) Session::get('otp_token') !== (string) $request->otp_token) {
            return $this->jsonFail('Invalid OTP session. Please login again.', 401);
        }

        $expiresAt = (int) Session::get('otp_expires_at', 0);
        if (!$expiresAt || time() > $expiresAt) {
            Session::forget(['otp_pending', 'otp_token', 'otp_code_hash', 'otp_expires_at', 'otp_payload']);
            return $this->jsonFail('OTP expired. Please login again.', 401);
        }

        $otp = preg_replace('/\s+/', '', (string) $request->otp);
        if (!Hash::check($otp, (string) Session::get('otp_code_hash'))) {
            return $this->jsonFail('Incorrect OTP.', 401);
        }

        $payload = Session::get('otp_payload', []);
        $userType = $payload['pending_user_type'] ?? null;
        $userId = $payload['pending_user_id'] ?? null;

        if (!$userType || !$userId) {
            return $this->jsonFail('OTP payload missing. Please login again.', 401);
        }

        Session::forget(['otp_pending', 'otp_token', 'otp_code_hash', 'otp_expires_at', 'otp_payload']);

        Session::put('user_type', $userType);
        Session::put('userID', $userId);
        Session::put('user_name', $payload['pending_username'] ?? '');
        Session::put('user_email', $payload['pending_user_email'] ?? '');

        if (isset($payload['schoolID'])) Session::put('schoolID', $payload['schoolID']);
        if ($userType === 'Teacher') {
            if (isset($payload['teacherID'])) Session::put('teacherID', $payload['teacherID']);
            if (isset($payload['teacher_name'])) Session::put('teacher_name', $payload['teacher_name']);
            
            if (isset($payload['teacherID'])) {
                $roleIds = DB::table('teachers')
                    ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                    ->where('role_user.teacher_id', $payload['teacherID'])
                    ->pluck('role_user.role_id')
                    ->toArray();
                
                if (!empty($roleIds)) {
                    $permissions = DB::table('permissions')
                        ->whereIn('role_id', $roleIds)
                        ->pluck('name')
                        ->unique()
                        ->toArray();
                    Session::put('teacher_permissions', $permissions);
                } else {
                    Session::put('teacher_permissions', []);
                }
            }
        }
        if ($userType === 'Staff') {
            if (isset($payload['staffID'])) Session::put('staffID', $payload['staffID']);
            if (isset($payload['staff_name'])) Session::put('staff_name', $payload['staff_name']);
            if (isset($payload['profession_id'])) Session::put('profession_id', $payload['profession_id']);

            if (!empty($payload['profession_id'])) {
                $permissions = \App\Models\StaffPermission::where('profession_id', $payload['profession_id'])
                    ->pluck('name')
                    ->toArray();
                Session::put('staff_permissions', $permissions);
            }
        }

        $request->session()->regenerate();

        $pendingUsername = $payload['pending_username'] ?? null;
        $schoolID = $payload['schoolID'] ?? null;
        if ($pendingUsername && $schoolID && in_array($userType, ['Admin', 'Teacher', 'Staff'], true)) {
            if ($this->shouldForcePasswordChange($userType, (int) $schoolID, (string) $pendingUsername)) {
                Session::put('force_password_change', true);
            }
        }

        $redirect = route('login');
        if ($userType === 'Admin') $redirect = route('AdminDashboard');
        if ($userType === 'Teacher') $redirect = route('teachersDashboard');
        if ($userType === 'Staff') $redirect = route('staffDashboard');

        if (Session::get('force_password_change') && in_array($userType, ['Admin', 'Teacher', 'Staff'], true)) {
            if ($userType === 'Admin') $redirect = route('admin.change_password');
            if ($userType === 'Teacher') $redirect = route('teacher.profile') . '#change-password';
            if ($userType === 'Staff') $redirect = route('staff.profile');
        }

        if (!empty($payload['remember_me'])) {
            $user = User::find($userId);
            if ($user) {
                $this->logUserLogin($user, $request, $schoolID);
                $this->setOtpSkipToken($user);
            }
        } else {
            $user = User::find($userId);
            if ($user) {
                $this->logUserLogin($user, $request, $schoolID);
            }
        }

        return response()->json(['success' => true, 'redirect' => $redirect]);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp_token' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!Session::get('otp_pending') || !Session::get('otp_token') || !Session::get('otp_payload')) {
            return $this->jsonFail('OTP session expired. Please login again.', 401);
        }

        if ((string) Session::get('otp_token') !== (string) $request->otp_token) {
            return $this->jsonFail('Invalid OTP session. Please login again.', 401);
        }

        $payload = Session::get('otp_payload', []);
        $phone = $payload['phone'] ?? null;
        if (!$phone) {
            return $this->jsonFail('Phone not found for OTP.', 422);
        }

        $otp = (string) random_int(100000, 999999);
        Session::put('otp_code_hash', Hash::make($otp));
        Session::put('otp_expires_at', time() + (10 * 60));
        $this->sendOtp($phone, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP resent',
            'expires_in' => 600,
            'masked_phone' => $this->maskPhone($phone),
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Username inahitajika (required).'], 422);
        }

        $username = $request->username;
        $user = User::where('name', $username)->first();

        if (!$user && preg_match('/^(?:0|255|)\d{9}$/', $username)) {
            $purePhone = substr($username, -9);
            $formats = ['255' . $purePhone, '0' . $purePhone, $purePhone];
            foreach ($formats as $fmt) {
                if ($fmt === $username) continue;
                $user = User::where('name', $fmt)->first();
                if ($user) {
                    $username = $fmt;
                    break;
                }
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Hatujaona akaunti yenye username hii.'], 404);
        }

        $phone = null;
        if ($user->user_type === 'Admin') {
            $school = School::where('registration_number', $username)->first();
            if ($school) $phone = $school->phone;
        } elseif ($user->user_type === 'Teacher') {
            $teacher = Teacher::where('employee_number', $username)->first();
            if ($teacher) $phone = $teacher->phone_number;
        } elseif ($user->user_type === 'Staff') {
            $staff = OtherStaff::where('employee_number', $username)->first();
            if ($staff) $phone = $staff->phone_number;
        } elseif (strtolower($user->user_type) === 'parent' || $user->user_type === 'Watchman') {
            $phone = $username;
        }

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Namba ya simu haijapatikana kwa akaunti hii. Wasiliana na msimamizi (Admin).'], 422);
        }

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $newPassword = '';
        for ($i = 0; $i < 8; $i++) {
            $newPassword .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        $sms = new SmsService();
        $msg = "Habari, password yako mpya ya ShuleXpert ni: {$newPassword}. Tafadhali ingia na ubadilishe uweke mpya.";
        $sms->sendSms($phone, $msg);

        $digits = preg_replace('/\D+/', '', $phone);
        $maskedPhone = substr($digits, -4);

        return response()->json([
            'success' => true,
            'message' => "Password mpya imetumwa kwenye namba yako ya simu ********{$maskedPhone}"
        ]);
    }

    /**
     * School fingerprint sync agent — Admin login API (token-based, no session).
     */
    public function schoolAgentLoginApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:1',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($resp = $this->checkIpBlocked($request)) {
            return $resp;
        }

        $username = (string) $request->input('username');
        $password = (string) $request->input('password');
        $rateKey = 'school_agent_login.' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        $resolvedUser = $this->resolveUserByCredentials($username, $password);
        $userLogin = $resolvedUser['user'];
        $resolveError = $resolvedUser['error'];

        if ($resolveError) {
            RateLimiter::hit($rateKey, 120);
            return response()->json(['success' => false, 'message' => $resolveError], 401);
        }

        if (!$userLogin) {
            RateLimiter::hit($rateKey, 120);
            return response()->json([
                'success' => false,
                'message' => 'Incorrect username or password!',
            ], 401);
        }

        if ($userLogin->user_type !== 'Admin') {
            RateLimiter::hit($rateKey, 120);
            return response()->json([
                'success' => false,
                'message' => 'Only school Admin accounts can access the fingerprint attendance sync system.',
            ], 403);
        }

        $username = (string) $userLogin->name;
        $school = School::where('registration_number', $username)->first();

        if (!$school) {
            RateLimiter::hit($rateKey, 120);
            return response()->json([
                'success' => false,
                'message' => 'School not found for this admin account.',
            ], 404);
        }

        if ($resp = $this->checkSchoolLoginStatus($school->schoolID, $request)) {
            return $resp;
        }

        RateLimiter::clear($rateKey);

        $token = bin2hex(random_bytes(32));
        $ttlMinutes = 720; // 12 hours

        Cache::put('school_agent_token:' . $token, [
            'user_id' => (int) $userLogin->id,
            'schoolID' => (int) $school->schoolID,
            'user_name' => (string) $userLogin->name,
            'user_email' => (string) ($userLogin->email ?? ''),
            'school_name' => (string) ($school->school_name ?? ''),
            'registration_number' => (string) ($school->registration_number ?? ''),
            'issued_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes($ttlMinutes)->toIso8601String(),
        ], now()->addMinutes($ttlMinutes));

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttlMinutes * 60,
            'server' => $this->resolveSchoolAgentEnvironment($request),
            'user' => [
                'id' => (int) $userLogin->id,
                'name' => (string) $userLogin->name,
                'email' => (string) ($userLogin->email ?? ''),
                'user_type' => 'Admin',
            ],
            'school' => [
                'schoolID' => (int) $school->schoolID,
                'school_name' => (string) ($school->school_name ?? ''),
                'registration_number' => (string) ($school->registration_number ?? ''),
            ],
        ]);
    }

    /**
     * Verify school agent bearer token (for sync agent & future API calls).
     */
    public function schoolAgentVerifyToken(Request $request)
    {
        $token = $this->extractSchoolAgentBearerToken($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token is required.',
            ], 401);
        }

        $payload = Cache::get('school_agent_token:' . $token);

        if (!$payload || empty($payload['schoolID'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
            'server' => $this->resolveSchoolAgentEnvironment($request),
            'user' => [
                'id' => (int) ($payload['user_id'] ?? 0),
                'name' => (string) ($payload['user_name'] ?? ''),
                'email' => (string) ($payload['user_email'] ?? ''),
                'user_type' => 'Admin',
            ],
            'school' => [
                'schoolID' => (int) $payload['schoolID'],
                'school_name' => (string) ($payload['school_name'] ?? ''),
                'registration_number' => (string) ($payload['registration_number'] ?? ''),
            ],
            'expires_at' => (string) ($payload['expires_at'] ?? ''),
        ]);
    }

    private function extractSchoolAgentBearerToken(Request $request): ?string
    {
        $header = (string) $request->header('Authorization', '');
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        $token = $request->input('token');
        return is_string($token) && $token !== '' ? trim($token) : null;
    }

    /**
     * @return array{name:string,host:string,app_url:string,label:string}
     */
    private function resolveSchoolAgentEnvironment(Request $request): array
    {
        $host = strtolower((string) $request->getHost());
        $name = 'live';

        if (str_contains($host, 'test.')) {
            $name = 'test';
        } elseif (in_array($host, ['127.0.0.1', 'localhost'], true) || str_contains($host, '192.168.')) {
            $name = 'local';
        }

        $labels = [
            'test' => 'ShuleXpert Test',
            'live' => 'ShuleXpert Live',
            'local' => 'ShuleXpert Local',
        ];

        return [
            'name' => $name,
            'label' => $labels[$name] ?? 'ShuleXpert',
            'host' => (string) $request->getHost(),
            'app_url' => rtrim((string) config('app.url'), '/'),
        ];
    }

    /**
     * Mobile app (Flutter) — create a web session so WebView can load parent/teacher/admin blades.
     * Uses the same login rules as POST /auth but without CSRF (API route).
     */
    public function createMobileWebSession(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->auth($request);

        if (! $response instanceof \Illuminate\Http\JsonResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected login response.',
            ], 500);
        }

        $data = $response->getData(true);
        if (! ($data['success'] ?? false)) {
            return $response;
        }

        if (! empty($data['redirect'])) {
            $data['web_url'] = $data['redirect'];
        }

        $userType = strtolower((string) Session::get('user_type', ''));
        $data['user_type'] = Session::get('user_type');
        $data['schoolID'] = Session::get('schoolID');
        $data['session_active'] = Session::has('userID');

        if ($userType === 'parent') {
            $data['parentID'] = Session::get('parentID');
            $data['portal_urls'] = $this->parentPortalUrls();
        }

        return response()->json($data, $response->getStatusCode());
    }

    /**
     * @return array<string, string>
     */
    private function parentPortalUrls(): array
    {
        return [
            'dashboard' => route('parentDashboard'),
            'results' => route('parentResults'),
            'attendance' => route('parentAttendance'),
            'fingerprint_attendance' => route('parentFingerprintAttendance'),
            'payments' => route('parentPayments'),
            'fees_summary' => route('parentFeesSummary'),
            'subjects' => route('parentSubjects'),
            'permissions' => route('parent.permissions'),
        ];
    }
}
