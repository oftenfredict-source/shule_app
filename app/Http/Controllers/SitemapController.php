<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Models\School; // Assuming there's a School model based on routes

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            [
                'loc' => url('/'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => url('/pricing'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/online-application'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/login'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
        ];

        // Add dynamic school application pages if they exist
        try {
            $schools = \App\Models\School::where('status', 'active')->get();
            foreach ($schools as $school) {
                $urls[] = [
                    'loc' => route('online_application.school_details', $school->schoolID),
                    'lastmod' => $school->updated_at ? $school->updated_at->toAtomString() : now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        } catch (\Exception $e) {
            // Handle cases where model or table might not exist
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . e($url['loc']) . '</loc>' . "\n";
            if (isset($url['lastmod'])) {
                $xml .= '        <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            if (isset($url['changefreq'])) {
                $xml .= '        <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            }
            if (isset($url['priority'])) {
                $xml .= '        <priority>' . $url['priority'] . '</priority>' . "\n";
            }
            $xml .= '    </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'text/xml');
    }
}
