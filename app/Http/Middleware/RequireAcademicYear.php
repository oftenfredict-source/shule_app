<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RequireAcademicYear
{
    /**
     * School portal roles that need at least one academic year.
     */
    private const GUARDED_ROLES = ['Admin', 'Teacher', 'Staff', 'Parent', 'Watchman'];

    public function handle(Request $request, Closure $next): Response
    {
        $userType = Session::get('user_type');
        $schoolID = (int) Session::get('schoolID');

        if (!$schoolID || !in_array($userType, self::GUARDED_ROLES, true)) {
            return $next($request);
        }

        if ($this->schoolHasAcademicYear($schoolID)) {
            return $next($request);
        }

        if ($this->isAllowedWithoutYear($request, (string) $userType)) {
            return $next($request);
        }

        $messageAdmin = 'Please define an Academic Year before using the system.';
        $messageOthers = 'No academic year defined. Contact Admin.';

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $userType === 'Admin' ? $messageAdmin : $messageOthers,
                'message' => $userType === 'Admin' ? $messageAdmin : $messageOthers,
                'redirect' => $userType === 'Admin' ? route('admin.academicYears') : null,
            ], 403);
        }

        if ($userType === 'Admin') {
            return redirect()
                ->route('admin.academicYears')
                ->with('error', $messageAdmin)
                ->with('require_academic_year', true);
        }

        return response()->view('errors.no_academic_year', [
            'userType' => $userType,
            'message' => $messageOthers,
        ], 403);
    }

    private function schoolHasAcademicYear(int $schoolID): bool
    {
        static $cache = [];

        if (array_key_exists($schoolID, $cache)) {
            return $cache[$schoolID];
        }

        return $cache[$schoolID] = AcademicYear::where('schoolID', $schoolID)->exists();
    }

    private function isAllowedWithoutYear(Request $request, string $userType): bool
    {
        $routeName = optional($request->route())->getName();
        $path = ltrim($request->path(), '/');

        $allowedRouteNames = [
            'login',
            'auth',
            'auth.otp.verify',
            'auth.otp.resend',
            'logout',
            'admin.change_password',
            'admin.change_password.store',
            'teacher.profile',
            'teacher.change_password',
            'staff.profile',
            'staff.profile.update',
        ];

        $allowedPaths = [
            'login',
            'auth',
            'auth/otp/verify',
            'auth/otp/resend',
            'logout',
            'admin/change-password',
            'teacher/profile',
            'teacher/change-password',
            'staff/profile',
            'staff/profile/update',
        ];

        // Only Admin may open / manage academic years while none exist yet.
        if ($userType === 'Admin') {
            $allowedRouteNames = array_merge($allowedRouteNames, [
                'admin.academicYears',
                'admin.academicYears.store',
                'admin.academicYears.show',
                'admin.academicYears.update',
                'admin.academicYears.delete',
            ]);
            $allowedPaths[] = 'admin/academic-years';
        }

        if ($routeName && in_array($routeName, $allowedRouteNames, true)) {
            return true;
        }

        if (in_array($path, $allowedPaths, true)) {
            return true;
        }

        // Admin academic-years nested paths (update/delete/show)
        if ($userType === 'Admin' && str_starts_with($path, 'admin/academic-years')) {
            return true;
        }

        return false;
    }
}
