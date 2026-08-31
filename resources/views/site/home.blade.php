@extends('layouts.site')

@section('content')
{{-- Banner --}}
<section class="banner-area relative home-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row fullscreen d-flex align-items-center justify-content-between">
            <div class="banner-content col-12 col-lg-9 col-md-12">
                <h1 class="text-uppercase">
                    ShuleXpert: Best School System<br>for Tanzania &amp; the World
                </h1>
                <p class="pt-10 pb-10" style="color:#eee;max-width:640px;">
                    A complete better school management system and school software: fees &amp; control numbers, biometric attendance,
                    exams &amp; results, lesson plans, scheme of work, parent portal, SMS and more. Academic and school administration
                    from preschool to advanced.
                </p>
                <a href="{{ route('site.modules') }}" class="primary-btn text-uppercase">Explore Modules</a>
                <a href="{{ route('login') }}" class="primary-btn wh text-uppercase ml-10">School Login</a>
            </div>
        </div>
    </div>
</section>

{{-- Feature strip --}}
<section class="feature-area">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-4">
                <div class="single-feature sx-feature-card">
                    <div class="title">
                        <i class="fa fa-money" aria-hidden="true"></i>
                        <h4>Fees &amp; Control Numbers</h4>
                    </div>
                    <div class="desc-wrap">
                        <p>Define fee structures, generate control numbers, track payments, and notify parents by SMS.</p>
                        <a href="{{ route('site.module', 'fees-payments') }}">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="single-feature sx-feature-card">
                    <div class="title">
                        <i class="fa fa-hand-paper-o" aria-hidden="true"></i>
                        <h4>Biometric Attendance</h4>
                    </div>
                    <div class="desc-wrap">
                        <p>ZKTeco fingerprint sync, School Agent bridge, and automatic parent attendance SMS.</p>
                        <a href="{{ route('site.module', 'attendance-biometric') }}">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="single-feature sx-feature-card">
                    <div class="title">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <h4>Parent Portal</h4>
                    </div>
                    <div class="desc-wrap">
                        <p>Results, attendance, fees, and permissions, always available on web and mobile.</p>
                        <a href="{{ route('site.module', 'parent-portal') }}">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modules grid (like popular courses) --}}
<section class="popular-course-area section-gap sx-modules-showcase" style="--sx-modules-bg: url('{{ asset('images/x.jpg') }}')">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <h2 class="mb-10">Modules that power your school</h2>
                    <p>Start with <strong>Lesson Plans</strong> and <strong>Scheme of Work</strong>: plus fees, exams, attendance and more from the live ShuleXpert system.</p>
                </div>
            </div>
        </div>
        <div class="row sx-modules-grid">
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
            @endphp
            @foreach($homeModules as $module)
                <div class="col-lg-3 col-md-6 col-12 sx-module-col">
                    <div class="sx-module-card">
                        <div class="thumb thumb-icon" aria-hidden="true">
                            <i class="fa {{ $module['icon'] }}"></i>
                        </div>
                        <div class="details">
                            <a href="{{ route('site.module', $module['slug']) }}"><h4>{{ $module['title'] }}</h4></a>
                            <p>{{ $module['short'] }}</p>
                            <a href="{{ route('site.module', $module['slug']) }}" class="primary-btn sx-module-btn">View Module</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12 text-center mt-20">
                <a href="{{ route('site.modules') }}" class="primary-btn text-uppercase">View All Modules</a>
            </div>
        </div>
    </div>
</section>

{{-- About / info --}}
<section class="info-area pb-120 sx-soft-band sx-home-info">
    <div class="sx-home-info-inner">
        <div class="sx-home-info-media">
            <img src="{{ asset('images/african-children-primary-school-classroom-johannesburg-south-africa-september-176110088.webp') }}" alt="ShuleXpert mfumo bora wa shule Tanzania, school management system in classrooms" loading="lazy" decoding="async">
        </div>
        <div class="sx-home-info-body">
            <h2>Built for schools worldwide</h2>
            <p>
                From single campuses to multi-branch institutions, <strong>ShuleXpert</strong> unifies administration, academics, finance, and parent communication,
                with school-level branding, academic-year awareness, and role-based portals for Admin, Teachers, Staff, Parents, and Watchmen.
            </p>
            <p class="sx-home-info-gap">
                No more scattered spreadsheets. One <strong>school system</strong> for enrollments, fees, attendance, exams, library, store, hostel, discipline, and strategic performance,
                <strong>mfumo wa shule</strong> schools trust across Tanzania and beyond.
            </p>
            <a href="{{ route('site.about') }}" class="primary-btn text-uppercase sx-home-info-btn">About ShuleXpert</a>
        </div>
    </div>
</section>

{{-- SEO: bilingual keyword-rich content (natural, indexable) --}}
<section class="section-gap sx-seo-home" aria-labelledby="sx-seo-home-heading">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="title text-center mb-40">
                    <h2 id="sx-seo-home-heading" class="mb-10">Mfumo bora wa shule Tanzania, better school management system</h2>
                    <p class="sx-seo-home-lead">
                        Unatafuta <strong>mfumo wa shule</strong>, <strong>mifumo ya shule</strong>, <strong>school software</strong>, au
                        <strong>school management system</strong>? <strong>ShuleXpert</strong> ni mfumo namba moja wa shule Tanzania,
                        school system kamili kwa usimamizi wa taaluma, fedha, mahudhurio na mawasiliano na wazazi.
                    </p>
                </div>
                <div class="row sx-seo-home-grid">
                    <div class="col-md-6 mb-30">
                        <h3>Kwa nini ShuleXpert?</h3>
                        <p>
                            <strong>Mifumo bora ya shule Tanzania</strong> inahitaji zaidi ya spreadsheet. ShuleXpert inatoa
                            <strong>mfumo wa taaluma</strong>, ada na control numbers, biometric attendance, mitihani na matokeo,
                            lesson plans, scheme of work, na parent portal, kwenye jukwaa moja. Ni <strong>better school management system</strong>
                            kwa preschool, primary, secondary na advanced.
                        </p>
                        <p>
                            Shule nyingi hutafuta pia majina kama <em>shulesoft</em> au “school software Tanzania”.
                            ShuleXpert ni jibu la kisasa: <strong>mifumo bora ya shule duniani</strong> yenye branding ya shule yako,
                            support ya EmCa Techonology, na moduli zinazofanya kazi kila siku shuleni.
                        </p>
                    </div>
                    <div class="col-md-6 mb-30">
                        <h3>School software schools actually use</h3>
                        <p>
                            Run fees, exams, attendance, timetable, SMS, library and online admission in one
                            <strong>school system</strong>. Explore our
                            <a href="{{ route('site.modules') }}">modules</a>,
                            <a href="{{ route('pricing') }}">pricing</a>,
                            or start an
                            <a href="{{ route('online_application') }}">online school application</a>.
                        </p>
                        <ul class="sx-seo-home-list">
                            <li>Mfumo wa shule Tanzania, web + mobile</li>
                            <li>School management software for Africa &amp; worldwide</li>
                            <li>Mfumo wa mitihani, ada, na mahudhurio</li>
                            <li>Parent portal &amp; SMS alerts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Search / CTA style band --}}
<section class="cta-one-area relative section-gap" style="background: linear-gradient(135deg, rgba(148,0,0,.9), rgba(4,9,30,.85)), url('{{ asset('images/African_science_academy_banner_1600-x-650.webp') }}') center/cover;">
    <div class="container">
        <div class="overlay overlay-bg" style="background:transparent;"></div>
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" style="position:relative;z-index:2;">
                <h2 class="text-white">Ready to digitize your school?</h2>
                <p class="text-white">
                    Talk to EmCa Techonology about onboarding, training, biometric devices, and payment integration.
                </p>
                <a class="primary-btn wh text-uppercase" href="{{ route('site.contact') }}">Contact Us</a>
                <a class="primary-btn text-uppercase ml-10" href="{{ route('pricing') }}">See Pricing</a>
            </div>
        </div>
    </div>
</section>

{{-- Team spotlight --}}
<section class="sx-team-spotlight section-gap" id="team" style="--sx-team-bg: url('{{ asset('img/carousel-1.jpg') }}')">
    <div class="sx-team-spotlight__bg" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-50 col-lg-8">
                <div class="title text-center sx-team-spotlight__head">
                    <span class="sx-team-eyebrow">Crafted by EmCa Techonology</span>
                    <h2 class="mb-10">The team behind ShuleXpert</h2>
                    <p>Engineers, analysts, and designers building school management that schools trust worldwide.</p>
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
                <a href="{{ route('site.team') }}" class="primary-btn text-uppercase sx-team-cta">Meet the Full Team</a>
            </div>
        </div>
    </div>
</section>

{{-- Why / testimonials lite --}}
<section class="review-area section-gap sx-reviews-showcase" style="--sx-reviews-bg: url('{{ asset('images/SS.jpg') }}')">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <h2 class="mb-10">Trusted by school leaders</h2>
                    <p>What administrators, teachers, and parents say about ShuleXpert.</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-4">
                <div class="single-review" style="background:#fff;border:1px solid #f0dada;padding:28px;margin-bottom:20px;">
                    <div class="star"><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span></div>
                    <p class="mt-20">Fee control numbers and SMS reminders transformed our collections. Parents pay with confidence.</p>
                    <h4 class="mt-20" style="color:#940000;">School Administrator</h4>
                    <p>Secondary School, Kilimanjaro</p>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="single-review" style="background:#fff;border:1px solid #f0dada;padding:28px;margin-bottom:20px;">
                    <div class="star"><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span></div>
                    <p class="mt-20">Entering results and publishing to parents is finally simple. The teacher portal saves hours every week.</p>
                    <h4 class="mt-20" style="color:#940000;">Subject Teacher</h4>
                    <p>High School, Moshi</p>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="single-review" style="background:#fff;border:1px solid #f0dada;padding:28px;margin-bottom:20px;">
                    <div class="star"><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span></div>
                    <p class="mt-20">I get attendance and results on my phone. Fee balances are clear, I always know what I owe.</p>
                    <h4 class="mt-20" style="color:#940000;">Parent</h4>
                    <p>Primary School, Arusha</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Bottom CTA --}}
<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h2>Start with ShuleXpert today</h2>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('online_application') }}">Online Application</a>
            </div>
        </div>
    </div>
</section>
@endsection

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
                    'text' => 'Ndiyo. ShuleXpert ni mfumo bora wa shule Tanzania na school management system kamili, ada, mahudhurio ya biometric, mitihani, lesson plans, scheme of work, parent portal na SMS. Imejengwa na EmCa Techonology kwa shule duniani.',
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
