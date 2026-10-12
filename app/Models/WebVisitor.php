<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WebVisitor extends Model
{
    protected $table = 'web_visitors';

    protected $fillable = [
        'ip_address',
        'email',
        'page_url',
        'page_name',
        'route_name',
        'referer',
        'device_type',
        'browser',
        'os',
        'user_agent',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function locationLabel(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);

        return $parts ? implode(', ', $parts) : 'Unknown location';
    }

    /**
     * Remember visitor email in cookie/session and backfill rows for this IP.
     */
    public static function rememberVisitorEmail(?string $email, ?string $ip = null): void
    {
        $email = strtolower(trim((string) $email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            session(['sx_visitor_email' => $email]);
            cookie()->queue(cookie('sx_visitor_email', $email, 60 * 24 * 90)); // 90 days
        } catch (\Throwable $e) {
            // ignore cookie/session failures
        }

        if ($ip && $ip !== 'unknown') {
            try {
                static::where('ip_address', $ip)
                    ->where(function ($q) {
                        $q->whereNull('email')->orWhere('email', '');
                    })
                    ->update(['email' => $email]);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * Resolve known visitor email from request cookie/session (if any).
     */
    public static function resolveVisitorEmail($request = null): ?string
    {
        $request = $request ?: request();

        $candidates = [
            $request->cookie('sx_visitor_email'),
            session('sx_visitor_email'),
        ];

        foreach ($candidates as $candidate) {
            $email = strtolower(trim((string) $candidate));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Unique IP count for a date (defaults to today).
     */
    public static function uniqueIpCountForDate($date = null): int
    {
        $query = static::query()->whereNotNull('ip_address')->where('ip_address', '!=', '');

        if ($date) {
            $query->whereDate('visited_at', $date);
        } else {
            $query->whereDate('visited_at', today());
        }

        return (int) $query->distinct()->count('ip_address');
    }

    public static function friendlyPageName(?string $routeName, string $path): string
    {
        $map = [
            'site.home' => 'Home / Landing Page',
            'site.about' => 'About',
            'site.team' => 'Team',
            'site.team.member' => 'Team Member',
            'site.services' => 'Services',
            'site.modules' => 'Modules',
            'site.module' => 'Module Details',
            'pricing' => 'Pricing',
            'site.faq' => 'FAQ',
            'site.download' => 'Download App',
            'site.contact' => 'Contact',
            'login' => 'School Login',
            'forgot_password' => 'Forgot Password',
            'online_application' => 'Online Application',
            'online_application.school' => 'Online Application School',
            'online_application.apply' => 'Online Application Form',
            'online_application.auth.login' => 'Online Application Login',
            'online_application.auth.register' => 'Online Application Register',
        ];

        if ($routeName && isset($map[$routeName])) {
            return $map[$routeName];
        }

        if ($routeName) {
            return ucwords(str_replace(['.', '_', '-'], ' ', $routeName));
        }

        $path = trim($path, '/') ?: '/';

        return $path === '/' ? 'Home / Landing Page' : ucwords(str_replace(['-', '_'], ' ', $path));
    }

    /**
     * Resolve approximate location from IP (cached).
     *
     * @return array{country:?string,region:?string,city:?string,latitude:?float,longitude:?float}
     */
    public static function resolveIpLocation(?string $ip): array
    {
        $empty = [
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        if (!$ip || in_array($ip, ['127.0.0.1', '::1', 'unknown', '0.0.0.0'], true)) {
            return $empty;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $empty;
        }

        // Hard-disable external geo lookups (were blocking PHP workers / causing live 504s).
        return $empty;

        $cacheKey = "web_visitor_geo_{$ip}";
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            // Fail fast - never block PHP workers on slow/rate-limited geo APIs.
            $response = Http::connectTimeout(1)
                ->timeout(2)
                ->get("https://ipapi.co/{$ip}/json/");

            if (!$response->successful()) {
                Cache::put($cacheKey, $empty, now()->addMinutes(30));

                return $empty;
            }

            $data = $response->json();
            if (!is_array($data) || !empty($data['error'])) {
                Cache::put($cacheKey, $empty, now()->addMinutes(30));

                return $empty;
            }

            $resolved = [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
            ];
            Cache::put($cacheKey, $resolved, now()->addHours(24));

            return $resolved;
        } catch (\Throwable $e) {
            Cache::put($cacheKey, $empty, now()->addMinutes(30));

            return $empty;
        }
    }
}
