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
        'operatingSystem' => 'Web, Android',
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'TZS',
            'description' => 'Contact EmCa Techonology for school packages',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
        ],
        'url' => url('/'),
    ];

    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
        ],
    ];

    if (!empty($seo['canonical']) && $seo['canonical'] !== url('/')) {
        $breadcrumb['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => $seo['title'] ?? 'Page',
            'item' => $seo['canonical'],
        ];
    }
@endphp

<script type="application/ld+json">{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($webSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($softwareSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumb, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>

@stack('schema')
