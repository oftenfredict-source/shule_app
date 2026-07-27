<?php

namespace App\Http\Controllers;

use App\Services\OnlineApplicationApplyTokenService;

class SitemapController extends Controller
{
    /**
     * Higher priority for modules that drive search intent / conversions.
     */
    private const MODULE_PRIORITIES = [
        'lesson-plans' => '1.0',
        'scheme-of-work' => '0.99',
        'student-management' => '0.85',
        'online-admission' => '0.85',
        'fees-payments' => '0.9',
        'attendance-biometric' => '0.9',
        'exams-results' => '0.9',
        'topic-analysis' => '0.88',
        'parent-portal' => '0.88',
        'timetable-academics' => '0.85',
        'sms-communication' => '0.82',
        'student-id-cards' => '0.8',
        'character-assessment' => '0.8',
        'library' => '0.8',
        'accountant' => '0.8',
        'teacher-portal' => '0.8',
    ];

    public function index(OnlineApplicationApplyTokenService $applyTokens)
    {
        $static = [
            ['/', 'daily', '0.95'],
            ['/about', 'monthly', '0.85'],
            ['/team', 'weekly', '0.85'],
            ['/services', 'monthly', '0.85'],
            ['/modules', 'daily', '0.96'],
            ['/pricing', 'weekly', '0.9'],
            ['/faq', 'monthly', '0.8'],
            ['/download', 'weekly', '0.85'],
            ['/contact', 'monthly', '0.8'],
            ['/online-application', 'daily', '0.9'],
            ['/online-application/account/register', 'monthly', '0.8'],
            ['/online-application/account/login', 'monthly', '0.8'],
        ];

        $urls = [];
        foreach ($static as [$path, $freq, $priority]) {
            $urls[] = [
                'loc' => $applyTokens->publicAbsoluteUrl($path),
                'lastmod' => now()->toAtomString(),
                'changefreq' => $freq,
                'priority' => $priority,
            ];
        }

        foreach (array_keys(SiteController::modules()) as $slug) {
            $priority = self::MODULE_PRIORITIES[$slug] ?? '0.72';
            $urls[] = [
                'loc' => $applyTokens->publicAbsoluteUrl('/modules/' . $slug),
                'lastmod' => now()->toAtomString(),
                'changefreq' => in_array($slug, ['lesson-plans', 'scheme-of-work'], true) ? 'daily' : 'weekly',
                'priority' => $priority,
            ];
        }

        foreach (SiteController::teamMembers() as $member) {
            $slug = $member['slug'] ?? null;
            if (!$slug) {
                continue;
            }
            $urls[] = [
                'loc' => $applyTokens->publicAbsoluteUrl('/team/' . $slug),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => !empty($member['featured']) ? '0.9' : '0.8',
            ];
        }

        try {
            $schools = \App\Models\School::query()
                ->where('status', 'Active')
                ->where('environment', 'Live')
                ->orderBy('school_name')
                ->get(['schoolID', 'updated_at']);

            foreach ($schools as $school) {
                $urls[] = [
                    'loc' => $applyTokens->schoolUrl((int) $school->schoolID),
                    'lastmod' => $school->updated_at
                        ? $school->updated_at->toAtomString()
                        : now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.85',
                ];
            }
        } catch (\Throwable $e) {
            // ignore — sitemap should still return static URLs
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>' . e($url['loc']) . "</loc>\n";
            $xml .= '        <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            $xml .= '        <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '        <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "    </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
