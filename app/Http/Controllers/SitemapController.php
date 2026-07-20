<?php

namespace App\Http\Controllers;

use App\Services\OnlineApplicationApplyTokenService;

class SitemapController extends Controller
{
    public function index(OnlineApplicationApplyTokenService $applyTokens)
    {
        $static = [
            ['/', 'daily', '1.0'],
            ['/about', 'monthly', '0.9'],
            ['/team', 'monthly', '0.9'],
            ['/services', 'monthly', '0.9'],
            ['/modules', 'weekly', '0.9'],
            ['/pricing', 'weekly', '0.95'],
            ['/faq', 'monthly', '0.85'],
            ['/download', 'weekly', '0.9'],
            ['/contact', 'monthly', '0.85'],
            ['/online-application', 'daily', '0.95'],
            ['/online-application/account/register', 'monthly', '0.85'],
            ['/online-application/account/login', 'monthly', '0.85'],
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
            $urls[] = [
                'loc' => $applyTokens->publicAbsoluteUrl('/modules/' . $slug),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
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
