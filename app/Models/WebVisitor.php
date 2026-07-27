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

        return Cache::remember("web_visitor_geo_{$ip}", now()->addHours(24), function () use ($ip, $empty) {
            try {
                $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");
                if (!$response->successful()) {
                    return $empty;
                }

                $data = $response->json();
                if (!is_array($data) || !empty($data['error'])) {
                    return $empty;
                }

                return [
                    'country' => $data['country_name'] ?? null,
                    'region' => $data['region'] ?? null,
                    'city' => $data['city'] ?? null,
                    'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                    'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
                ];
            } catch (\Throwable $e) {
                return $empty;
            }
        });
    }
}
