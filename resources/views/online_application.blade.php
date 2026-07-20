@php
    $skipSplash = true;
    $seo = [
        'title' => 'Online School Application Tanzania — Apply to Schools | ShuleXpert',
        'description' => 'Apply to primary and secondary schools online with ShuleXpert. Search schools in Tanzania by region or district, check class availability, view school details, and submit your student admission application in minutes. Free for parents — powered by EmCa Techonology.',
        'keywords' => 'online school application Tanzania, apply to school online, school admission Tanzania, ShuleXpert online application, primary school application, secondary school application, student admission online, school enrollment Tanzania, apply school Moshi, EmCa Techonology, ShuleXpert admission portal',
        'canonical' => route('online_application'),
        'type' => 'website',
        'image' => url('/images/shuleXpert.jpg'),
        'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
    ];
@endphp
@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-apply-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Online Application</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('online_application') }}">Online Application</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-apply-page section-gap">
    <div class="container">
        <div class="sx-apply-hero text-center">
            <h2>Online school application in Tanzania</h2>
            <p class="sx-apply-intro">
                Search participating primary and secondary schools, check available class spaces for student admission,
                and submit your application online — free for parents and guardians. Powered by
                <strong>EmCa Techonology</strong>.
            </p>
        </div>

        <div class="sx-apply-auth-cta">
            @auth('online_application')
                <div class="sx-apply-auth-cta__logged">
                    <span>Logged in as <strong>{{ auth('online_application')->user()->first_name }}</strong></span>
                    <a href="{{ route('online_application.portal.dashboard') }}" class="primary-btn text-uppercase">My Dashboard</a>
                </div>
            @else
                <a href="{{ route('online_application.auth.register') }}" class="primary-btn text-uppercase">
                    <i class="fa fa-user-plus"></i> Create account to apply
                </a>
                <a href="{{ route('online_application.auth.login') }}" class="sx-apply-btn-outline">
                    <i class="fa fa-sign-in"></i> Login to apply online
                </a>
            @endauth
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="sx-apply-search">
            <form method="GET" action="{{ route('online_application') }}" class="sx-apply-search__form">
                <div class="sx-apply-search__field">
                    <i class="fa fa-search"></i>
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by school name, region, or district..."
                        value="{{ $search }}">
                </div>
                <button type="submit" class="primary-btn text-uppercase">Search</button>
                @if($search)
                    <a href="{{ route('online_application') }}" class="sx-apply-search__clear">Clear</a>
                @endif
            </form>
            <div class="sx-apply-view-toggle" role="group" aria-label="School layout">
                <button type="button" class="sx-apply-view-toggle__btn" data-oa-view="list" title="List view" aria-pressed="false">
                    <i class="fa fa-list" aria-hidden="true"></i><span>List</span>
                </button>
                <button type="button" class="sx-apply-view-toggle__btn" data-oa-view="grid" title="Grid view" aria-pressed="false">
                    <i class="fa fa-th-large" aria-hidden="true"></i><span>Grid</span>
                </button>
            </div>
        </div>

        <div class="row sx-apply-schools" id="oaSchoolsGrid"
            data-load-url="{{ route('online_application.schools') }}"
            data-page="{{ $nextPage ?? 2 }}"
            data-has-more="{{ !empty($hasMore) ? '1' : '0' }}"
            data-search="{{ $search }}">
            @include('site.partials.online-application-view-boot')
            @include('partials.online_application_school_cards', [
                'schools' => $schools,
                'requireAuth' => !auth('online_application')->check(),
            ])

            @if($schools->count() === 0)
                <div class="col-12">
                    <div class="sx-apply-empty">
                        <i class="fa fa-info-circle"></i>
                        <h5>No schools with available spaces found</h5>
                        <p>Please try a different search or check back later.</p>
                    </div>
                </div>
            @endif
        </div>

        <div id="oaLoadMore" class="text-center py-3" style="{{ !empty($hasMore) ? '' : 'display:none;' }}">
            <div class="spinner-border text-danger" role="status" id="oaLoadSpinner" style="display:none;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="text-muted mb-0 mt-2" id="oaLoadHint">Scroll down to load more schools</p>
        </div>
    </div>
</section>

@include('site.partials.online-application-modal')
@endsection

@push('scripts')
    @include('site.partials.online-application-scripts')
    @include('site.partials.online-application-lazy-load')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sx-apply-view-more-classes').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = document.getElementById(btn.getAttribute('data-target'));
                if (!target) return;
                target.style.display = 'block';
                btn.style.display = 'none';
            });
        });
    });
    </script>
@endpush

@push('schema')
@php
    $applyPage = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => route('online_application') . '#webpage',
        'url' => route('online_application'),
        'name' => 'Online School Application — ShuleXpert',
        'description' => 'Search participating schools in Tanzania and submit a student admission application online through ShuleXpert.',
        'inLanguage' => 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => asset('images/shuleXpert.jpg'),
        ],
    ];

    $applyService = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => route('online_application') . '#service',
        'name' => 'ShuleXpert Online School Application',
        'serviceType' => 'School admission and enrollment',
        'description' => 'Digital school admission service for parents and guardians to apply to ShuleXpert-participating schools across Tanzania.',
        'url' => route('online_application'),
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Tanzania',
        ],
        'audience' => [
            '@type' => 'PeopleAudience',
            'audienceType' => 'Parents and guardians applying for student admission',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
            'email' => 'emca@emca.tech',
            'telephone' => config('shulexpert.phone_e164'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Moshi',
                'addressRegion' => 'Kilimanjaro',
                'addressCountry' => 'TZ',
            ],
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'TZS',
            'description' => 'Free online school application for parents',
            'url' => route('online_application'),
            'availability' => 'https://schema.org/InStock',
        ],
    ];

    $applyBreadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Online Application', 'item' => route('online_application')],
        ],
    ];

    $applyFaq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'How do I apply to a school online with ShuleXpert?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Create a free online application account, verify your phone with OTP, login, search for a school, choose a class with available spaces, and complete the application form.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Is ShuleXpert online school application free?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Parents and guardians can search schools and submit applications online at no cost through the ShuleXpert admission portal.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Do I need a separate account to apply online?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Online application uses a dedicated account with phone verification. It is separate from school staff, teacher, or parent school accounts for security.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What happens after I submit an online school application?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'After submission you can track your application status in your online application dashboard. The school reviews your application and you are notified by SMS when a decision is made.',
                ],
            ],
        ],
    ];

    $applyAction = [
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'ShuleXpert Online Application Portal',
        'applicationCategory' => 'EducationalApplication',
        'operatingSystem' => 'Web',
        'url' => route('online_application'),
        'browserRequirements' => 'Requires JavaScript. Works on mobile and desktop browsers.',
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($applyPage, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyService, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyBreadcrumbs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyFaq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyAction, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
