@php
    $publicBase = rtrim((string) config('shulexpert.public_url', 'https://shulexpert.com'), '/');
    $homeUrl = $publicBase . '/';
    $orgSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'ShuleXpert',
        'alternateName' => [
            'Shule Xpert',
            'ShuleXpert School Management System',
            'Mfumo wa Shule ShuleXpert',
            'EmCa ShuleXpert',
        ],
        'url' => $homeUrl,
        'logo' => $publicBase . '/images/shuleXpert.jpg',
        'description' => 'Mfumo bora wa shule Tanzania na duniani, better school management system and school software by EmCa Techonology.',
        'email' => 'emca@emcatech.co.tz',
        'telephone' => config('shulexpert.phone_e164'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Moshi',
            'addressRegion' => 'Kilimanjaro',
            'addressCountry' => 'TZ',
        ],
        'areaServed' => [
            ['@type' => 'Country', 'name' => 'Tanzania'],
            ['@type' => 'Place', 'name' => 'Africa'],
            ['@type' => 'Place', 'name' => 'Worldwide'],
        ],
        'knowsAbout' => [
            'Mfumo bora wa shule Tanzania',
            'School management system',
            'School software',
            'Mfumo wa taaluma',
            'Mifumo ya shule',
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
        'alternateName' => ['Mfumo bora wa shule Tanzania', 'School management system ShuleXpert'],
        'url' => $homeUrl,
        'inLanguage' => ['sw', 'en'],
        'description' => 'ShuleXpert, mfumo bora wa shule Tanzania, better school management system, school software na mfumo wa taaluma.',
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $publicBase . '/modules?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $softwareSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'ShuleXpert',
        'alternateName' => [
            'Mfumo bora wa shule Tanzania',
            'Better school management system',
            'Mfumo wa shule',
            'School system',
            'School software',
            'Mfumo wa taaluma',
            'Mfumo namba moja wa shule Tanzania',
        ],
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'School Management System',
        'operatingSystem' => 'Web, Android, iOS',
        'inLanguage' => ['sw', 'en'],
        'keywords' => 'Mfumo bora wa shule Tanzania, Better school management system, ShuleXpert, shulesoft, Mifumo ya shule, Mfumo wa shule, School system, school software, Mfumo wa taaluma, Mifumo bora ya shule Duniani, school management system Tanzania',
        'description' => 'ShuleXpert ni mfumo bora wa shule Tanzania na mifumo bora ya shule duniani, complete school management software for fees, attendance, exams, lesson plans, scheme of work and parent communication.',
        'featureList' => [
            'Fees & control numbers',
            'Biometric attendance',
            'Exams & results',
            'Topic analysis',
            'Parent portal',
            'Lesson plans & scheme of work',
            'Timetable',
            'SMS alerts',
            'Library & store',
            'Online school application',
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $publicBase . '/pricing',
            'priceCurrency' => 'TZS',
            'description' => 'Installation TZS 1,000,000 + TZS 500 per student / month',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => 'https://emca.tech',
        ],
        'url' => $homeUrl,
        'downloadUrl' => $publicBase . '/download',
        'screenshot' => $publicBase . '/images/shuleXpert.jpg',
        'countriesSupported' => 'TZ',
        'audience' => [
            '@type' => 'EducationalAudience',
            'educationalRole' => 'administrator',
        ],
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
            'item' => $homeUrl,
        ];
        if (!empty($seo['canonical']) && $seo['canonical'] !== $homeUrl && rtrim($seo['canonical'], '/') !== rtrim($homeUrl, '/')) {
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
