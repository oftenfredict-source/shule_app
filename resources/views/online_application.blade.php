@php
    $skipSplash = true;
    $seo = [
        'title' => 'Online School Application Tanzania | Apply to Schools | ShuleXpert',
        'description' => 'Apply to primary and secondary schools online with ShuleXpert. Search schools in Tanzania by region or district, check class availability, view school details, and submit your student admission application in minutes. Free for parents, powered by EmCa Techonologies.',
        'keywords' => 'online school application Tanzania, apply to school online, school admission Tanzania, ShuleXpert online application, primary school application, secondary school application, student admission online, school enrollment Tanzania, apply school Moshi, EmCa Techonologies, ShuleXpert admission portal',
        'canonical' => app(\App\Services\OnlineApplicationApplyTokenService::class)->publicAbsoluteUrl('/online-application'),
        'type' => 'website',
        'image' => app(\App\Services\OnlineApplicationApplyTokenService::class)->publicAbsoluteUrl('/images/apply-hero.jpg'),
        'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
    ];
@endphp
@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-apply-page-banner" id="home" style="background-image:url('{{ asset('images/apply-hero.jpg') }}')">
    <div class="sx-apply-page-banner__overlay" aria-hidden="true"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-10 text-center">
                <h1 class="text-white">{{ __('site.online_application') }}</h1>
                <p class="text-white link-nav mb-0">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    <span class="fa fa-angle-right mx-1"></span>
                    <a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="sx-apply-page-wrap">
    <div class="container sx-apply-block">
        <div class="sx-apply-intro-lead text-center">
            <p class="mb-0">{!! __('site.apply_intro') !!}</p>
        </div>
    </div>

    <div class="container sx-apply-block">
        <div class="row">
            <div class="col-md-4 mb-30">
                <div class="sx-apply-quick-card">
                    <span class="sx-apply-quick-card__icon"><i class="fa fa-user-plus"></i></span>
                    <strong>{{ __('site.apply_step1_title') }}</strong>
                    <span>{{ __('site.apply_step1_text') }}</span>
                </div>
            </div>
            <div class="col-md-4 mb-30">
                <div class="sx-apply-quick-card">
                    <span class="sx-apply-quick-card__icon"><i class="fa fa-search"></i></span>
                    <strong>{{ __('site.apply_step2_title') }}</strong>
                    <span>{{ __('site.apply_step2_text') }}</span>
                </div>
            </div>
            <div class="col-md-4 mb-30">
                <div class="sx-apply-quick-card">
                    <span class="sx-apply-quick-card__icon"><i class="fa fa-paper-plane"></i></span>
                    <strong>{{ __('site.apply_step3_title') }}</strong>
                    <span>{{ __('site.apply_step3_text') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container sx-apply-block sx-apply-block--last">
        @if (session('success'))
            <div class="sx-apply-alert sx-apply-alert--success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="sx-apply-alert sx-apply-alert--info">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="sx-apply-alert sx-apply-alert--error">{{ session('error') }}</div>
        @endif

        <div class="sx-apply-browse">
            <div class="sx-apply-browse__top">
                <div class="sx-apply-browse__titles">
                    <p class="sx-apply-kicker">{{ __('site.apply_schools_kicker') }}</p>
                    <h2 class="sx-apply-heading">{{ __('site.apply_heading') }}</h2>
                    <p class="sx-apply-browse__sub">{{ __('site.apply_browse_sub') }}</p>
                </div>
                <div class="sx-apply-browse__account">
                    @auth('online_application')
                        <span class="sx-apply-browse__hello">{{ __('site.apply_logged_in') }} <strong>{{ auth('online_application')->user()->first_name }}</strong></span>
                        <a href="{{ route('online_application.portal.dashboard') }}" class="sx-apply-pill sx-apply-pill--solid">
                            <i class="fa fa-tachometer"></i> {{ __('site.apply_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('online_application.auth.register') }}" class="sx-apply-pill sx-apply-pill--solid">
                            <i class="fa fa-user-plus"></i> {{ __('site.apply_create_account') }}
                        </a>
                        <a href="{{ route('online_application.auth.login') }}" class="sx-apply-pill sx-apply-pill--ghost">
                            <i class="fa fa-sign-in"></i> {{ __('site.apply_login') }}
                        </a>
                    @endauth
                </div>
            </div>

            <form method="GET" action="{{ route('online_application') }}" class="sx-apply-toolbar" role="search">
                <label class="sx-apply-toolbar__field" for="oaSearchInput">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input id="oaSearchInput" type="search" name="search"
                        placeholder="{{ __('site.apply_search_placeholder') }}"
                        value="{{ $search }}"
                        autocomplete="off">
                </label>
                <button type="submit" class="sx-apply-toolbar__submit">{{ __('site.apply_search') }}</button>
                @if($search)
                    <a href="{{ route('online_application') }}" class="sx-apply-toolbar__clear">{{ __('site.apply_clear') }}</a>
                @endif
                <div class="sx-apply-toolbar__views sx-apply-view-toggle" role="group" aria-label="{{ __('site.apply_layout') }}">
                    <button type="button" class="sx-apply-view-toggle__btn" data-oa-view="list" title="{{ __('site.apply_list') }}" aria-pressed="false">
                        <i class="fa fa-list" aria-hidden="true"></i><span>{{ __('site.apply_list') }}</span>
                    </button>
                    <button type="button" class="sx-apply-view-toggle__btn is-active" data-oa-view="grid" title="{{ __('site.apply_grid') }}" aria-pressed="true">
                        <i class="fa fa-th-large" aria-hidden="true"></i><span>{{ __('site.apply_grid') }}</span>
                    </button>
                </div>
            </form>

            <div class="row sx-apply-schools is-grid" id="oaSchoolsGrid"
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
                            <h5>{{ __('site.apply_empty_title') }}</h5>
                            <p>{{ __('site.apply_empty_desc') }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div id="oaLoadMore" class="text-center py-3" style="{{ !empty($hasMore) ? '' : 'display:none;' }}">
                <div class="spinner-border text-danger" role="status" id="oaLoadSpinner" style="display:none;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-muted mb-0 mt-2" id="oaLoadHint">{{ __('site.apply_load_more') }}</p>
            </div>
        </div>
    </div>
</section>

@include('site.partials.online-application-modal')
@endsection

@push('styles')
<style>
.sx-public-site .banner-area.sx-apply-page-banner.about-banner,
.sx-apply-page-banner.about-banner {
    min-height: 300px !important;
    height: 300px !important;
    padding: 0 !important;
    position: relative;
    overflow: hidden;
    background-color: #5c0000 !important;
    background-size: cover !important;
    background-position: center 35% !important;
    background-repeat: no-repeat !important;
    display: flex;
    align-items: center;
}
.sx-apply-page-banner__overlay {
    position: absolute;
    inset: 0;
    background: rgba(92, 0, 0, 0.55);
    z-index: 1;
}
.sx-apply-page-banner .container { position: relative; z-index: 2; width: 100%; }
.sx-apply-page-banner .about-content h1 {
    font-size: 30px;
    font-weight: 800;
    margin-bottom: 8px;
}
.sx-apply-page-banner .link-nav a { color: #fff !important; }

.sx-apply-page-wrap > .container.sx-apply-block {
    padding-top: 48px !important;
    padding-bottom: 0 !important;
}
.sx-apply-page-wrap > .container.sx-apply-block.sx-apply-block--last {
    padding-bottom: 90px !important;
}
.sx-apply-page-wrap { padding-bottom: 20px; background: #fff; }

.sx-apply-intro-lead {
    max-width: 760px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 14px;
    padding: 22px 28px;
    box-shadow: 0 8px 22px rgba(148,0,0,.06);
    color: #444;
    font-size: 16px;
    line-height: 1.7;
}

.sx-apply-quick-card {
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 12px;
    padding: 20px 18px;
    height: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease;
}
.sx-apply-quick-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(148,0,0,.1);
}
.sx-apply-quick-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #940000 !important;
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    flex-shrink: 0;
    box-shadow: 0 6px 14px rgba(148, 0, 0, 0.22);
}
.sx-apply-quick-card__icon i,
.sx-apply-quick-card__icon .fa {
    color: #fff !important;
    font-size: 18px;
    line-height: 1;
    font-family: "Font Awesome 5 Free", FontAwesome !important;
    font-weight: 900 !important;
    font-style: normal !important;
    -webkit-font-smoothing: antialiased;
    display: inline-block !important;
}
.sx-apply-quick-card strong { color: #1a1a1a; font-size: 15px; font-weight: 800; }
.sx-apply-quick-card > span:not(.sx-apply-quick-card__icon) {
    color: #666;
    font-size: 13px;
    line-height: 1.45;
}

.sx-apply-alert {
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-weight: 600;
}
.sx-apply-alert--success { background: #e8f7ee; border: 1px solid #b7e4c7; color: #1a7f37; }
.sx-apply-alert--info { background: #eef5ff; border: 1px solid #c5d8f5; color: #1d4f91; }
.sx-apply-alert--error { background: #fff0f0; border: 1px solid #f0c2c2; color: #940000; }

.sx-apply-browse {
    background: #fff;
    border: 1px solid #efe4e4;
    border-radius: 20px;
    padding: 28px 26px 22px;
    box-shadow: 0 18px 40px rgba(80, 0, 0, .06);
}
.sx-apply-browse__top {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3e8e8;
}
.sx-apply-kicker {
    color: #940000;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.sx-apply-heading {
    color: #161616;
    font-weight: 800;
    font-size: 28px;
    line-height: 1.2;
    margin: 0 0 8px;
}
.sx-apply-browse__sub {
    margin: 0;
    color: #6b6b6b;
    font-size: 14.5px;
    line-height: 1.55;
    max-width: 520px;
}
.sx-apply-browse__account {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: flex-end;
    flex-shrink: 0;
}
.sx-apply-browse__hello {
    color: #555;
    font-size: 13.5px;
    margin-right: 4px;
}
.sx-apply-pill {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 11px 18px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    line-height: 1;
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
}
.sx-apply-pill--solid {
    background: #940000 !important;
    color: #fff !important;
    box-shadow: 0 10px 22px rgba(148,0,0,.28);
}
.sx-apply-pill--solid:hover {
    transform: translateY(-1px);
    color: #fff !important;
    box-shadow: 0 14px 28px rgba(148,0,0,.34);
}
.sx-apply-pill--ghost {
    background: #fff !important;
    color: #940000 !important;
    border: 1.5px solid #e4bcbc;
}
.sx-apply-pill--ghost:hover {
    background: #fff5f5 !important;
    color: #7a0000 !important;
    border-color: #940000;
}

.sx-apply-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 10px;
    background: #fff;
    border: 1px solid #eddada;
    border-radius: 16px;
    padding: 10px;
    margin-bottom: 24px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
}
.sx-apply-toolbar__field {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1 1 260px;
    min-width: 0;
    background: #fff;
    border: 1px solid #ecdada;
    border-radius: 12px;
    padding: 0 14px;
    margin: 0;
}
.sx-apply-toolbar__field i {
    color: #940000;
    font-family: FontAwesome !important;
    flex-shrink: 0;
}
.sx-apply-toolbar__field input {
    width: 100%;
    border: 0;
    outline: none;
    background: transparent;
    min-height: 46px;
    font-size: 14.5px;
    color: #222;
}
.sx-apply-toolbar__field input::placeholder { color: #9a9a9a; }
.sx-apply-toolbar__submit {
    border: 0;
    border-radius: 12px;
    background: #940000;
    color: #fff;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 0 22px;
    min-height: 46px;
    white-space: nowrap;
    cursor: pointer;
    flex: 0 0 auto;
    transition: background .2s ease, transform .2s ease;
}
.sx-apply-toolbar__submit:hover { background: #7a0000; transform: translateY(-1px); }
.sx-apply-toolbar__clear {
    align-self: center;
    color: #940000;
    font-weight: 700;
    font-size: 13px;
    text-decoration: underline;
    white-space: nowrap;
    flex: 0 0 auto;
}
.sx-apply-toolbar__views {
    display: inline-flex;
    align-items: stretch;
    border: 1px solid #e8d4d4;
    border-radius: 12px;
    overflow: hidden;
    flex: 0 0 auto;
    margin-left: auto;
}
.sx-apply-toolbar__views .sx-apply-view-toggle__btn {
    appearance: none;
    border: none;
    background: #fff;
    color: #666;
    font-size: 13px;
    font-weight: 700;
    padding: 0 14px;
    min-height: 46px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1;
    transition: background 0.2s ease, color 0.2s ease;
}
.sx-apply-toolbar__views .sx-apply-view-toggle__btn + .sx-apply-view-toggle__btn {
    border-left: 1px solid #e8d4d4;
}
.sx-apply-toolbar__views .sx-apply-view-toggle__btn.is-active,
.sx-apply-toolbar__views .sx-apply-view-toggle__btn[aria-pressed="true"] {
    background: #940000;
    color: #fff;
}
.sx-apply-toolbar__views .sx-apply-view-toggle__btn:hover:not(.is-active):not([aria-pressed="true"]) {
    background: #fff5f5;
    color: #940000;
}
.sx-apply-toolbar__views .sx-apply-view-toggle__btn .fa {
    font-family: FontAwesome !important;
}
.sx-apply-browse .sx-apply-schools {
    margin-left: -10px;
    margin-right: -10px;
}
.sx-apply-browse .sx-apply-schools > [class*="col-"] {
    padding-left: 10px;
    padding-right: 10px;
}
.sx-apply-browse .sx-apply-schools.is-list .sx-apply-school-col {
    flex: 0 0 100%;
    max-width: 100%;
}

@media (max-width: 767.98px) {
    .sx-apply-toolbar__views,
    .sx-apply-view-toggle {
        display: none !important;
    }
}
@media (max-width: 991.98px) {
    .sx-apply-page-banner.about-banner {
        min-height: 240px !important;
        height: 240px !important;
        background-position: center 30% !important;
    }
    .sx-apply-page-banner .about-content h1 { font-size: 24px; }
    .sx-apply-browse__top { flex-direction: column; align-items: flex-start; }
    .sx-apply-browse__account { justify-content: flex-start; }
    .sx-apply-toolbar__submit { flex: 1 1 auto; }
    .sx-apply-toolbar__views { margin-left: 0; width: 100%; }
    .sx-apply-toolbar__views .sx-apply-view-toggle__btn { flex: 1; justify-content: center; }
    .sx-apply-page-wrap > .container.sx-apply-block.sx-apply-block--last {
        padding-bottom: 70px !important;
    }
}
@media (max-width: 575.98px) {
    .sx-apply-page-banner.about-banner {
        min-height: 200px !important;
        height: 200px !important;
    }
    .sx-apply-page-banner .about-content h1 { font-size: 22px; }
    .sx-apply-intro-lead { padding: 18px 16px; }
    .sx-apply-browse { padding: 20px 14px 16px; border-radius: 16px; }
    .sx-apply-heading { font-size: 22px; }
    .sx-apply-pill { width: 100%; justify-content: center; }
}
</style>
@endpush

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
        'name' => 'Online School Application | ShuleXpert',
        'description' => 'Search participating schools in Tanzania and submit a student admission application online through ShuleXpert.',
        'inLanguage' => 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => asset('images/apply-hero.jpg'),
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
            'name' => 'EmCa Techonologies',
            'url' => url('/'),
            'email' => 'emca@emcatech.co.tz',
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
            'name' => 'EmCa Techonologies',
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($applyPage, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyService, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyBreadcrumbs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyFaq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($applyAction, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
