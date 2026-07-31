<?php

namespace App\Http\Middleware;

use App\Models\WebVisitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class LogWebVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Capture request data now; log AFTER the response is sent so the
        // gateway never waits on geo-IP HTTP (ipapi.co was causing live 504s).
        try {
            if (!$this->shouldLog($request, $response)) {
                return $response;
            }

            $routeName = $request->route() ? $request->route()->getName() : null;
            $path = '/' . ltrim($request->path(), '/');
            if ($path === '//') {
                $path = '/';
            }

            $ip = $request->ip() ?: 'unknown';
            $throttleKey = 'web_visitor:' . md5($ip . '|' . $path);
            if (!Cache::add($throttleKey, 1, now()->addSeconds(45))) {
                return $response;
            }

            $deviceType = 'Desktop';
            $browser = 'Unknown';
            $os = 'Unknown';

            if (class_exists(Agent::class)) {
                $agent = new Agent();
                if ($agent->isMobile()) {
                    $deviceType = 'Mobile';
                } elseif ($agent->isTablet()) {
                    $deviceType = 'Tablet';
                }
                $browser = $agent->browser() ?: 'Unknown';
                $os = $agent->platform() ?: 'Unknown';
            }

            $payload = [
                'ip_address' => $ip,
                'email' => WebVisitor::resolveVisitorEmail($request),
                'page_url' => mb_substr($request->fullUrl(), 0, 500),
                'page_name' => WebVisitor::friendlyPageName($routeName, $path),
                'route_name' => $routeName,
                'referer' => mb_substr((string) $request->headers->get('referer'), 0, 500) ?: null,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            ];

            // afterResponse flushes to the client (fastcgi_finish_request) before geo-IP HTTP,
            // so reverse proxies never wait on ipapi.co — that was causing live 504s.
            dispatch(function () use ($payload) {
                try {
                    $geo = WebVisitor::resolveIpLocation($payload['ip_address']);
                    WebVisitor::create(array_merge($payload, [
                        'country' => $geo['country'] ?? null,
                        'region' => $geo['region'] ?? null,
                        'city' => $geo['city'] ?? null,
                        'latitude' => $geo['latitude'] ?? null,
                        'longitude' => $geo['longitude'] ?? null,
                        'visited_at' => now(),
                    ]));
                } catch (\Throwable $e) {
                    // Never break the site for visitor logging
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            // Never break the site for visitor logging
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (!$request->isMethod('GET')) {
            return false;
        }

        if (Session::has('user_type') || Session::has('userID') || Session::has('user_log_id')) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return false;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
            return false;
        }

        $path = ltrim($request->path(), '/');
        $skipPrefixes = [
            'super-admin',
            'admin',
            'api',
            'iclock',
            'livewire',
            'vendor',
            'storage',
            'userImages',
            'exam_papers',
            'online-application/portal',
            'online-application/account/verify-otp',
        ];
        foreach ($skipPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return false;
            }
        }

        $skipExact = [
            'favicon.ico',
            'sitemap.xml',
            'up',
            'robots.txt',
        ];
        if (in_array($path, $skipExact, true)) {
            return false;
        }

        // Track public marketing / landing style pages (and login without auth)
        $routeName = $request->route() ? (string) $request->route()->getName() : '';
        if ($routeName !== '') {
            if (
                str_starts_with($routeName, 'site.')
                || str_starts_with($routeName, 'online_application')
                || in_array($routeName, ['pricing', 'login', 'forgot_password'], true)
            ) {
                return true;
            }

            // Named authenticated app routes — skip
            return false;
        }

        // Unnamed public top-level paths only
        return $path === '' || !str_contains($path, '/');
    }
}
