<?php

namespace App\Http\Middleware;

use App\Models\ParentModel;
use App\Models\School;
use App\Models\SchoolBoardMember;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Watchman;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\MobileApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobileToken
{
    public function __construct(private MobileApiTokenService $tokens)
    {
    }

    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        // Mobile API always answers in JSON, even when the app omits the Accept header.
        $request->headers->set('Accept', 'application/json');
        // Many web controllers branch on ajax(); treat mobile the same as XHR.
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $plain = $this->tokens->extractBearer($request);
        if (!$plain) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token is required. Send Authorization: Bearer {token}.',
            ], 401);
        }

        $token = $this->tokens->findValid($plain);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $user = $token->user;
        $request->attributes->set('mobile_api', true);
        $request->attributes->set('mobile_token', $token);
        $request->attributes->set('mobile_user', $user);
        $request->headers->set('Accept', 'application/json');

        $lang = strtolower(substr((string) $request->header('Accept-Language', 'sw'), 0, 2));
        $locale = $lang === 'en' ? 'en' : 'sw';
        app()->setLocale($locale);

        if ($role === 'parent' && strtolower((string) $user->user_type) !== 'parent') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not a parent account.',
            ], 403);
        }

        if ($role === 'board' && strtolower((string) $user->user_type) !== 'boardmember') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not a board member account.',
            ], 403);
        }

        if ($role === 'teacher' && strtolower((string) $user->user_type) !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not a teacher account.',
            ], 403);
        }

        if ($role === 'watchman' && strtolower((string) $user->user_type) !== 'watchman') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not a watchman account.',
            ], 403);
        }

        if (strtolower((string) $user->user_type) === 'teacher') {
            $teacher = null;
            if ($token->teacherID) {
                $teacher = Teacher::where('id', $token->teacherID)
                    ->when($token->schoolID, fn ($q) => $q->where('schoolID', $token->schoolID))
                    ->first();
            }
            if (!$teacher) {
                $teacher = Teacher::where('employee_number', $user->name)->first();
            }

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found.',
                ], 401);
            }

            $teacherSchool = School::find($teacher->schoolID);
            if ($teacherSchool && ($teacherSchool->login_status === 0 || $teacherSchool->login_status === false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huduma imesitishwa sababu: ' . ($teacherSchool->suspension_reason ?: 'No reason provided.'),
                ], 403);
            }

            // Same rule as the website's ForcePasswordChange middleware: until the default password is
            // changed, only the profile/password/logout/me endpoints are allowed.
            if ($this->teacherMustChangePassword($teacher, $user) && !$this->isPasswordChangeExempt($request)) {
                return response()->json([
                    'success' => false,
                    'code' => 'password_change_required',
                    'message' => 'Please change your password to continue.',
                ], 403);
            }

            $request->attributes->set('mobile_teacher', $teacher);
            $request->attributes->set('mobile_teacher_id', (int) $teacher->id);
            $request->attributes->set('mobile_school_id', (int) $teacher->schoolID);

            // Identity comes from the token only. The existing teacher API methods read these
            // values from headers first, then input, so set both to stop clients overriding them.
            $identity = [
                'teacherID' => (int) $teacher->id,
                'schoolID' => (int) $teacher->schoolID,
                'user_id' => (int) $user->id,
                'user_type' => 'Teacher',
            ];
            foreach ($identity as $key => $value) {
                $request->headers->set($key, (string) $value);
            }
            $request->merge($identity);

            $roleIds = DB::table('role_user')->where('teacher_id', $teacher->id)->pluck('role_id')->toArray();
            $permissions = !empty($roleIds)
                ? DB::table('permissions')->whereIn('role_id', $roleIds)->pluck('name')->unique()->values()->toArray()
                : [];

            config(['session.driver' => 'array']);
            Session::put([
                'user_type' => 'Teacher',
                'userID' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'schoolID' => (int) $teacher->schoolID,
                'school_name' => $teacherSchool->school_name ?? null,
                'teacherID' => (int) $teacher->id,
                'teacher_name' => trim($teacher->first_name . ' ' . $teacher->last_name),
                'teacher_permissions' => $permissions,
                'locale' => $locale,
            ]);
        }

        if (strtolower((string) $user->user_type) === 'boardmember') {
            $member = $this->resolveBoardMember($user, $token->schoolID ? (int) $token->schoolID : null);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Board member record not found.',
                ], 401);
            }

            if ($member->status !== 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This board member account is not active.',
                ], 403);
            }

            $school = School::find($member->schoolID);
            if ($school && ($school->login_status === 0 || $school->login_status === false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huduma imesitishwa sababu: ' . ($school->suspension_reason ?: 'No reason provided.'),
                ], 403);
            }

            $member->loadMissing(['position', 'boardRoles.permissions']);

            $request->attributes->set('mobile_board_member', $member);
            $request->attributes->set('mobile_school_id', (int) $member->schoolID);

            config(['session.driver' => 'array']);
            Session::put([
                'user_type' => 'BoardMember',
                'userID' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'schoolID' => (int) $member->schoolID,
                'school_name' => $school->school_name ?? null,
                'boardMemberID' => (int) $member->memberID,
                'board_member_name' => $member->full_name,
                'board_position_code' => $member->position->position_code ?? 'MEMBER',
                'board_position_name' => $member->position->position_name ?? 'Member',
                'board_permissions' => $member->boardPermissionNames(),
                'locale' => $locale,
            ]);
        }

        if (strtolower((string) $user->user_type) === 'parent') {
            $parent = ParentModel::with('school')
                ->where('parentID', $token->parentID)
                ->where('schoolID', $token->schoolID)
                ->first();

            if (!$parent) {
                $parent = ParentModel::with('school')->where('phone', $user->name)->first();
            }

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent record not found.',
                ], 401);
            }

            $request->attributes->set('mobile_parent', $parent);
            $request->attributes->set('mobile_parent_id', (int) $parent->parentID);
            $request->attributes->set('mobile_school_id', (int) $parent->schoolID);
            $request->merge([
                'parentID' => (int) $parent->parentID,
                'schoolID' => (int) $parent->schoolID,
            ]);

            config(['session.driver' => 'array']);
            Session::put([
                'user_type' => 'parent',
                'parentID' => (int) $parent->parentID,
                'schoolID' => (int) $parent->schoolID,
                'school_name' => $parent->school->school_name ?? null,
                'locale' => $locale,
            ]);
        }

        if (strtolower((string) $user->user_type) === 'watchman') {
            $watchman = Watchman::query()
                ->when($token->schoolID, fn ($q) => $q->where('schoolID', $token->schoolID))
                ->where('phone_number', $user->name)
                ->first();

            if (!$watchman) {
                $watchman = Watchman::where('phone_number', $user->name)->first();
            }

            if (!$watchman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Watchman record not found.',
                ], 401);
            }

            if (strcasecmp((string) $watchman->status, 'Active') !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This watchman account is not active.',
                ], 403);
            }

            $watchmanSchool = School::find($watchman->schoolID);
            if ($watchmanSchool && ($watchmanSchool->login_status === 0 || $watchmanSchool->login_status === false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huduma imesitishwa sababu: ' . ($watchmanSchool->suspension_reason ?: 'No reason provided.'),
                ], 403);
            }

            $request->attributes->set('mobile_watchman', $watchman);
            $request->attributes->set('mobile_watchman_id', (int) $watchman->id);
            $request->attributes->set('mobile_school_id', (int) $watchman->schoolID);

            config(['session.driver' => 'array']);
            Session::put([
                'user_type' => 'Watchman',
                'userID' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'schoolID' => (int) $watchman->schoolID,
                'school_name' => $watchmanSchool->school_name ?? null,
                'watchmanID' => (int) $watchman->id,
                'watchman_name' => trim(($watchman->first_name ?? '') . ' ' . ($watchman->last_name ?? '')),
                'locale' => $locale,
            ]);
        }

        $response = $next($request);

        // The token is valid at this point, so a 401 from inside the website code means "not allowed".
        // The mobile app treats 401 as "token expired" and logs the user out, so send 403 instead.
        if ($response->getStatusCode() === 401) {
            $response->setStatusCode(403);
        }

        return $response;
    }

    /**
     * Mirrors Auth::shouldForcePasswordChange() for teachers: the school requires a change and the
     * teacher still uses the default password (last name + "staff").
     */
    private function teacherMustChangePassword(Teacher $teacher, User $user): bool
    {
        $mustChange = (bool) School::where('schoolID', $teacher->schoolID)->value('must_change_default_password');
        if (!$mustChange) {
            return false;
        }

        $default = (string) ($teacher->last_name ?? '') . 'staff';

        return $default !== 'staff' && Hash::check($default, $user->password);
    }

    private function isPasswordChangeExempt(Request $request): bool
    {
        $uri = $request->route()?->uri();

        return in_array($uri, [
            'api/v1/auth/me',
            'api/v1/auth/password',
            'api/v1/auth/logout',
            'api/v1/teacher/profile',
            'api/v1/teacher/password/change',
            'api/v1/teacher/profile/update',
        ], true);
    }

    /**
     * Same lookup as the web login (Auth@login, BoardMember case).
     */
    private function resolveBoardMember(User $user, ?int $schoolID): ?SchoolBoardMember
    {
        $member = SchoolBoardMember::with('position')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('username', $user->name);
            })
            ->when($schoolID, fn ($q) => $q->where('schoolID', $schoolID))
            ->first();

        if (!$member && $user->schoolID) {
            $member = SchoolBoardMember::with('position')
                ->where('schoolID', $user->schoolID)
                ->where(function ($q) use ($user) {
                    $q->where('username', $user->name)
                        ->orWhere('user_id', $user->id);
                })
                ->first();
        }

        return $member;
    }
}
