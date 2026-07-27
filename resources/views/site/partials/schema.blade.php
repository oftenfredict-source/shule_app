@php
    $orgSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'ShuleXpert',
        'url' => url('/'),
        'logo' => asset('images/shuleXpert.jpg'),
        'description' => 'School management platform for schools worldwide.',
        'email' => 'emca@emca.tech',
        'telephone' => config('shulexpert.phone_e164'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Moshi',
            'addressRegion' => 'Kilimanjaro',
            'addressCountry' => 'TZ',
        ],
        'parentOrganization' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => 'https://emca.tech',
        ],
        'sameAs' => [],
    ];

    $webSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'ShuleXpert',
        'url' => url('/'),
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/modules') . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $softwareSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'ShuleXpert',
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'School Management System',
        'operatingSystem' => 'Web, Android, iOS',
        'featureList' => [
            'Fees & control numbers',
            'Biometric attendance',
            'Exams & results',
            'Topic analysis',
            'Parent portal',
            'Timetable & schemes of work',
            'SMS alerts',
            'Library & store',
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => url('/pricing'),
            'priceCurrency' => 'TZS',
            'description' => 'Installation TZS 1,000,000 + TZS 500 per student / month',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => 'https://emca.tech',
        ],
        'url' => url('/'),
        'downloadUrl' => url('/download'),
        'screenshot' => asset('images/shuleXpert.jpg'),
    ];

    $breadcrumbItems = [];
    if (!empty($seo['breadcrumbs']) && is_array($seo['breadcrumbs'])) {
        foreach (array_values($seo['breadcrumbs']) as $i => $crumb) {
            if (empty($crumb['name']) || empty($crumb['url'])) {
                continue;
            }
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }
    }

    if ($breadcrumbItems === []) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => url('/'),
        ];
        if (!empty($seo['canonical']) && $seo['canonical'] !== url('/')) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $seo['breadcrumb_name'] ?? ($seo['title'] ?? 'Page'),
                'item' => $seo['canonical'],
            ];
        }
    }

    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];
@endphp

<script type="application/ld+json">{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($webSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($softwareSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumb, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>

@stack('schema')
