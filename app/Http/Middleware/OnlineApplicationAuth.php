<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlineApplicationAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('online_application')->check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login or create an account to continue.',
                    'redirect' => route('online_application.auth.login', [
                        'redirect' => $request->getRequestUri(),
                    ]),
                ], 401);
            }

            return redirect()
                ->route('online_application.auth.login', [
                    'redirect' => $request->getRequestUri(),
                ])
                ->with('info', 'Please login or create an online application account to continue.');
        }

        return $next($request);
    }
}
