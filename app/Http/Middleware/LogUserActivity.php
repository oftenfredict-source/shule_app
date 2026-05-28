<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Session;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log if user is logged in and we have a user_log_id
        if (Session::has('user_log_id')) {
            // Avoid logging ajax calls if too noisy, but user wants "activity zotee"
            // Also avoid logging logout itself or common static files if they go through laravel
            
            $url = $request->fullUrl();
            if (str_contains($url, '/login') || str_contains($url, '/logout') || $request->isMethod('OPTIONS')) {
                return $response;
            }

            // Prevent DB errors when query string is very long (e.g. DataTables server-side params)
            // Most schemas use varchar for url; keep a safe cap.
            $safeUrl = mb_substr($url, 0, 250);

            $payload = null;
            if ($request->method() === 'POST') {
                $payload = json_encode($request->except(['password', '_token', 'photo_data']));
                if ($payload !== null) {
                    $payload = mb_substr($payload, 0, 5000);
                }
            }

            UserActivity::create([
                'user_log_id' => Session::get('user_log_id'),
                'url' => $safeUrl,
                'method' => $request->method(),
                'route_name' => $request->route() ? $request->route()->getName() : null,
                'payload' => $payload,
            ]);

            // Update user last activity
            if (Session::has('userID')) {
                \App\Models\User::where('id', Session::get('userID'))->update(['last_activity_at' => now()]);
            }
        }

        return $response;
    }
}
