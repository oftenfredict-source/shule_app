<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolAiSupportUser
{
    public const ALLOWED_TYPES = ['Admin', 'Teacher', 'Staff'];

    public function handle(Request $request, Closure $next): Response
    {
        $userType = Session::get('user_type');
        $userId = Session::get('userID');
        $schoolId = Session::get('schoolID');

        if (
            !in_array($userType, self::ALLOWED_TYPES, true)
            || empty($userId)
            || empty($schoolId)
        ) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please sign in as Admin, Teacher, or Staff.',
                ], 401);
            }

            return redirect()->route('login');
        }

        if (!(bool) config('services.openai.enabled', true)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI Customer Support is currently disabled.',
                ], 503);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
