<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\RequireAcademicYear;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Use custom CSRF middleware to exclude Push SDK routes
        $middleware->validateCsrfTokens(except: [
            'iclock/getrequest',
            'iclock/cdata',
        ]);

        $middleware->web(append: [
            ForcePasswordChange::class,
            RequireAcademicYear::class,
            // Temporarily disabled during live 504 incident (DB write after every request).
            // \App\Http\Middleware\LogUserActivity::class,
            // LogWebVisitor disabled: sync/geo IP was exhausting PHP workers → live 504s.
            // \App\Http\Middleware\LogWebVisitor::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SchoolAgentCors::class,
        ]);

        $middleware->alias([
            'school.agent' => \App\Http\Middleware\AuthenticateSchoolAgent::class,
            'online.application.auth' => \App\Http\Middleware\OnlineApplicationAuth::class,
            'school.ai.support' => \App\Http\Middleware\EnsureSchoolAiSupportUser::class,
            'mobile.auth' => \App\Http\Middleware\AuthenticateMobileToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSRF token mismatch.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            $target = url()->previous();
            if (!$target || $target === $request->fullUrl()) {
                $target = route('login');
            }

            return redirect()->to($target)
                ->with('error', 'Kikao kimekwisha. Jaribu kuingia tena.')
                ->header('Cache-Control', 'no-store, private, max-age=0');
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->ajax() || $request->wantsJson()) {
                $limit = ini_get('post_max_size') ?: 'server limit';

                return response()->json([
                    'success' => false,
                    'message' => 'File ni kubwa sana. Kikomo cha server ni '.$limit.'.',
                ], 413);
            }
        });
    })->create();
