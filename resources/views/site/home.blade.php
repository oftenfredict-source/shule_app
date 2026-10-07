@extends('layouts.site')

@php
    $homeFeaturedSlugs = [
        'lesson-plans',
        'scheme-of-work',
        'fees-payments',
        'attendance-biometric',
        'exams-results',
        'topic-analysis',
        'parent-portal',
        'timetable-academics',
    ];
    $homeModules = collect($modules)
        ->keyBy('slug')
        ->only($homeFeaturedSlugs)
        ->filter()
        ->values()
        ->all();

    $pillars = [
        [
            'icon' => 'fa-money',
            'title' => __('site.fees_title'),
            'text' => __('site.fees_text'),
            'link' => route('site.module', 'fees-payments'),
        ],
        [
            'icon' => 'fa-hand-paper-o',
            'title' => __('site.biometric_title'),
            'text' => __('site.biometric_text'),
            'link' => route('site.module', 'attendance-biometric'),
        ],
        [
            'icon' => 'fa-users',
            'title' => __('site.parent_title'),
            'text' => __('site.parent_text'),
            'link' => route('site.module', 'parent-portal'),
        ],
    ];

    $reviews = [
        [
            'quote' => 'Fee control numbers and SMS reminders transformed our collections. Parents pay with confidence.',
            'role' => 'School Administrator',
            'place' => 'Secondary School, Kilimanjaro',
        ],
        [
            'quote' => 'Entering results and publishing to parents is finally simple. The teacher portal saves hours every week.',
            'role' => 'Subject Teacher',
            'place' => 'High School, Moshi',
        ],
        [
            'quote' => 'I get attendance and results on my phone. Fee balances are clear, I always know what I owe.',
            'role' => 'Parent',
            'place' => 'Primary School, Arusha',
        ],
    ];
@endphp

@section('content')
<section class="banner-area relative home-banner sx-home-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="sx-home-banner__glow sx-home-banner__glow--a" aria-hidden="true"></div>
    <div class="sx-home-banner__glow sx-home-banner__glow--b" aria-hidden="true"></div>
    <div class="container">
        <div class="row fullscreen d-flex align-items-center justify-content-between">
            <div class="banner-content col-12 col-lg-9 col-md-12">
                <h1 class="text-uppercase">
                    {{ __('site.hero_title_1') }}<br>{{ __('site.hero_title_2') }}
                </h1>
                <p class="sx-home-lead">
                    {{ __('site.hero_lead') }}
                </p>
                <div class="sx-home-hero-actions">
                    <a href="{{ route('site.modules') }}" class="primary-btn text-uppercase">{{ __('site.explore_modules') }}</a>
                    <a href="{{ route('login') }}" class="primary-btn wh text-uppercase">{{ __('site.school_login') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="sx-home-pillars">
    <div class="container">
        <div class="row">
            @foreach($pillars as $pillar)
                <div class="col-12 col-lg-4 mb-3 mb-lg-0">
                    <a href="{{ $pillar['link'] }}" class="sx-home-pillar">
                        <span class="sx-home-pillar__icon" aria-hidden="true"><i class="fa {{ $pillar['icon'] }}"></i></span>
                        <span class="sx-home-pillar__body">
                            <strong>{{ $pillar['title'] }}</strong>
                            <span>{{ $pillar['text'] }}</span>
                            <em>{{ __('site.learn_more') }} <i class="fa fa-angle-right"></i></em>
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="popular-course-area section-gap sx-modules-showcase" style="--sx-modules-bg: url('{{ asset('images/x.jpg') }}')">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <p class="sx-home-section-kicker">{{ __('site.module_suite') }}</p>
                    <h2 class="mb-10">{{ __('site.modules_heading') }}</h2>
                    <p>{{ __('site.modules_lead') }}</p>
                </div>
            </div>
        </div>
        <div class="row sx-modules-grid">
            @foreach($homeModules as $module)
                <div class="col-lg-3 col-md-6 col-12 sx-module-col">
                    <div class="sx-module-card">
                        <div class="thumb thumb-icon" aria-hidden="true">
                            <i class="fa {{ $module['icon'] }}"></i>
                        </div>
                        <div class="details">
                            <a href="{{ route('site.module', $module['slug']) }}"><h4>{{ $module['title'] }}</h4></a>
                            <p>{{ $module['short'] }}</p>
                            <a href="{{ route('site.module', $module['slug']) }}" class="primary-btn sx-module-btn">{{ __('site.view_module') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12 text-center mt-20">
                <a href="{{ route('site.modules') }}" class="primary-btn text-uppercase">{{ __('site.view_all_modules') }}</a>
            </div>
        </div>
    </div>
</section>

<section class="info-area pb-120 sx-soft-band sx-home-info">
    <div class="sx-home-info-inner">
        <div class="sx-home-info-media">
            <img src="{{ asset('images/african-children-primary-school-classroom-johannesburg-south-africa-september-176110088.webp') }}" alt="ShuleXpert mfumo bora wa shule Tanzania, school management system in classrooms" loading="lazy" decoding="async">
        </div>
        <div class="sx-home-info-body">
            <p class="sx-home-section-kicker">{{ __('site.why_choose') }}</p>
            <h2>{{ __('site.built_worldwide') }}</h2>
            <p>
                {{ __('site.built_worldwide_p1') }}
            </p>
            <p class="sx-home-info-gap">
                {{ __('site.built_worldwide_p2') }}
            </p>
            <div class="sx-home-info-actions">
                <a href="{{ route('site.about') }}" class="primary-btn text-uppercase">{{ __('site.about_shulexpert') }}</a>
                <a href="{{ route('site.services') }}" class="primary-btn wh text-uppercase">{{ __('site.our_services') }}</a>
            </div>
        </div>
    </div>
</section>

<section class="section-gap sx-seo-home" aria-labelledby="sx-seo-home-heading">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="title text-center mb-40">
                    <h2 id="sx-seo-home-heading" class="mb-10">{{ __('site.seo_heading') }}</h2>
                    <p class="sx-seo-home-lead">
                        {{ __('site.seo_lead') }}
                    </p>
                </div>
                <div class="row sx-seo-home-grid">
                    <div class="col-md-6 mb-30">
                        <div class="sx-home-seo-card">
                            <h3>{{ __('site.seo_why_title') }}</h3>
                            <p>{{ __('site.seo_why_p1') }}</p>
                            <p>{{ __('site.seo_why_p2') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-30">
                        <div class="sx-home-seo-card">
                            <h3>{{ __('site.seo_use_title') }}</h3>
                            <p>
                                {{ __('site.seo_use_intro') }}
                                <a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a>,
                                <a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a>,
                                {{ __('site.seo_use_or') }}
                                <a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a>.
                            </p>
                            <ul class="sx-seo-home-list">
                                <li>{{ __('site.seo_list_1') }}</li>
                                <li>{{ __('site.seo_list_2') }}</li>
                                <li>{{ __('site.seo_list_3') }}</li>
                                <li>{{ __('site.seo_list_4') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="sx-home-cta relative section-gap">
    <div class="sx-home-cta__bg" style="background-image: url('{{ asset('images/African_science_academy_banner_1600-x-650.webp') }}');" aria-hidden="true"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center sx-home-cta__content">
                <p class="sx-home-eyebrow">{{ __('site.next_step') }}</p>
                <h2 class="text-white">{{ __('site.ready_digitize') }}</h2>
                <p class="text-white">
                    {{ __('site.ready_digitize_desc') }}
                </p>
                <div class="sx-home-hero-actions sx-home-hero-actions--center">
                    <a class="primary-btn wh text-uppercase" href="{{ route('site.contact') }}">{{ __('site.contact_us') }}</a>
                    <a class="primary-btn text-uppercase" href="{{ route('pricing') }}">{{ __('site.see_pricing') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="sx-team-spotlight section-gap" id="team" style="--sx-team-bg: url('{{ asset('img/carousel-1.jpg') }}')">
    <div class="sx-team-spotlight__bg" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-50 col-lg-8">
                <div class="title text-center sx-team-spotlight__head">
                    <span class="sx-team-eyebrow">{{ __('site.crafted_by') }}</span>
                    <h2 class="mb-10">{{ __('site.team_behind') }}</h2>
                    <p>{{ __('site.team_behind_desc') }}</p>
                </div>
            </div>
        </div>
        <div class="row sx-team-grid justify-content-center align-items-end">
            @foreach($teamMembers as $member)
                @include('site.partials.team-card', ['member' => $member, 'variant' => 'home'])
            @endforeach
        </div>
        <div class="row">
            <div class="col-12 text-center mt-30">
                <a href="{{ route('site.team') }}" class="primary-btn text-uppercase sx-team-cta">{{ __('site.meet_full_team') }}</a>
            </div>
        </div>
    </div>
</section>

<section class="review-area section-gap sx-reviews-showcase" style="--sx-reviews-bg: url('{{ asset('images/SS.jpg') }}')">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <p class="sx-home-section-kicker">{{ __('site.social_proof') }}</p>
                    <h2 class="mb-10">{{ __('site.trusted_leaders') }}</h2>
                    <p>{{ __('site.trusted_leaders_desc') }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($reviews as $review)
                <div class="col-12 col-lg-4">
                    <article class="sx-home-review">
                        <div class="star" aria-label="5 star rating">
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                        </div>
                        <p>{{ $review['quote'] }}</p>
                        <h4>{{ $review['role'] }}</h4>
                        <span>{{ $review['place'] }}</span>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h2>{{ __('site.start_today') }}</h2>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('online_application') }}">{{ __('site.online_application') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.sx-home-banner {
    position: relative;
    overflow: hidden;
}
.sx-home-banner .fullscreen {
    padding-top: 130px;
    padding-bottom: 48px;
    box-sizing: border-box;
    align-items: center;
}
.sx-home-banner .banner-content {
    overflow: visible;
}
.sx-home-banner .banner-content h1 {
    line-height: 1.28 !important;
    overflow: visible;
    padding-top: 4px;
    margin-bottom: 14px;
}
.sx-home-banner__glow {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
}
.sx-home-banner__glow--a {
    width: 380px; height: 380px;
    top: -100px; right: -80px;
    background: radial-gradient(circle, rgba(255,255,255,0.16) 0%, transparent 70%);
}
.sx-home-banner__glow--b {
    width: 300px; height: 300px;
    bottom: -120px; left: -60px;
    background: radial-gradient(circle, rgba(196,58,58,0.35) 0%, transparent 70%);
}
.sx-home-banner .container { position: relative; z-index: 2; }
.sx-home-eyebrow {
    display: inline-block;
    color: #ffd6d6;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.sx-home-lead {
    color: #eee;
    max-width: 640px;
    padding-top: 10px;
    padding-bottom: 10px;
    font-size: 15px;
    line-height: 1.75;
}
.sx-home-hero-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
}
.sx-home-hero-actions--center { justify-content: center; }
.sx-home-hero-actions .primary-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    height: 44px !important;
    min-height: 44px !important;
    line-height: 1 !important;
    padding: 0 26px !important;
    margin: 0 !important;
    box-sizing: border-box;
}

.sx-home-pillars {
    margin-top: -42px;
    position: relative;
    z-index: 3;
    padding-bottom: 10px;
}
.sx-home-pillar {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: #fff;
    border: 1px solid #f0dada;
    padding: 20px 16px;
    height: 100%;
    text-decoration: none !important;
    box-shadow: 0 10px 28px rgba(148,0,0,0.06);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.sx-home-pillar:hover {
    transform: translateY(-3px);
    border-color: #e8b4b4;
    box-shadow: 0 14px 32px rgba(148,0,0,0.12);
}
.sx-home-pillar__icon {
    width: 46px; height: 46px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(145deg, #940000, #c43a3a);
    color: #fff;
    flex-shrink: 0;
}
.sx-home-pillar__body {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}
.sx-home-pillar__body strong {
    color: #222;
    font-size: 16px;
    font-weight: 800;
}
.sx-home-pillar__body span {
    color: #666;
    font-size: 13px;
    line-height: 1.55;
}
.sx-home-pillar__body em {
    font-style: normal;
    color: #940000;
    font-size: 13px;
    font-weight: 700;
    margin-top: 4px;
}

.sx-home-section-kicker {
    color: #940000 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px !important;
}
.sx-home-info-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 22px;
}
.sx-home-info-actions .primary-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    height: 42px !important;
    min-height: 42px !important;
    max-height: 42px !important;
    line-height: 1 !important;
    padding: 0 28px !important;
    margin: 0 !important;
    box-sizing: border-box;
    flex: 0 0 auto;
}
.sx-home-seo-card {
    border: 1px solid #f0dada;
    background: linear-gradient(180deg, #fff 0%, #FFF5F5 100%);
    padding: 24px 20px;
    height: 100%;
}

.sx-home-cta {
    position: relative;
    overflow: hidden;
    padding-top: 72px !important;
    padding-bottom: 72px !important;
}
.sx-home-cta__bg {
    position: absolute;
    inset: 0;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
}
.sx-home-cta__bg::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(148,0,0,.92), rgba(4,9,30,.88));
}
.sx-home-cta__content {
    position: relative;
    z-index: 2;
}
.sx-home-cta__content h2 {
    font-weight: 800;
    margin-bottom: 12px;
}
.sx-home-cta__content > p.text-white {
    max-width: 560px;
    margin: 0 auto 18px;
    opacity: .92;
}

.sx-home-review {
    background: #fff;
    border: 1px solid #f0dada;
    padding: 28px 22px;
    margin-bottom: 20px;
    height: calc(100% - 20px);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.sx-home-review:hover {
    border-color: #e8b4b4;
    box-shadow: 0 12px 28px rgba(148,0,0,0.08);
    transform: translateY(-2px);
}
.sx-home-review .star { margin-bottom: 12px; }
.sx-home-review .star .fa { color: #c43a3a; }
.sx-home-review p {
    color: #555;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 16px;
}
.sx-home-review h4 {
    color: #940000;
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 4px;
}
.sx-home-review span {
    color: #777;
    font-size: 13px;
}

@media (max-width: 991.98px) {
    .sx-home-pillars { margin-top: -28px; }
    .sx-home-banner .fullscreen { padding-top: 120px; }
}
@media (max-width: 575.98px) {
    .sx-home-hero-actions .primary-btn { width: 100%; text-align: center; }
    .sx-home-banner .banner-content h1 { font-size: 28px !important; line-height: 1.3 !important; }
    .sx-home-info-actions { width: 100%; }
    .sx-home-info-actions .primary-btn {
        width: 100%;
        flex: 1 1 100%;
    }
}
</style>
@endpush

@push('schema')
@php
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Je, ShuleXpert ni mfumo bora wa shule Tanzania?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Ndiyo. ShuleXpert ni mfumo bora wa shule Tanzania na school management system kamili: ada, mahudhurio ya biometric, mitihani, lesson plans, scheme of work, parent portal na SMS. Imejengwa na EmCa Techonology kwa shule duniani.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the best school management system / school software for Tanzania?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ShuleXpert is a better school management system and school software for Tanzania and worldwide: fees with control numbers, biometric attendance, exams and results, parent portal, timetable, and online admission, one school system for admin, teachers, staff and parents.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Mfumo wa taaluma na mifumo ya shule inajumuisha nini?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Mfumo wa taaluma wa ShuleXpert unajumuisha lesson plans, scheme of work, timetable, exams, topic analysis, attendance na parent communication, sehemu ya mifumo bora ya shule Tanzania na duniani.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Is ShuleXpert an alternative if I searched for ShuleSoft or other school systems?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'If you searched for shulesoft, school system, school software, or mfumo wa shule Tanzania, ShuleXpert is a modern school management platform with live modules, mobile access, and EmCa Techonology support.',
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
