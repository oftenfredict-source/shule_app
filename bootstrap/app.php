<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ForcePasswordChange;

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
            \App\Http\Middleware\LogUserActivity::class,
            \App\Http\Middleware\LogWebVisitor::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SchoolAgentCors::class,
        ]);

        $middleware->alias([
            'school.agent' => \App\Http\Middleware\AuthenticateSchoolAgent::class,
            'online.application.auth' => \App\Http\Middleware\OnlineApplicationAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
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
