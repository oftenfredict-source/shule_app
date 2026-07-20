@extends('layouts.site')

@section('content')
{{-- Banner --}}
<section class="banner-area relative home-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row fullscreen d-flex align-items-center justify-content-between">
            <div class="banner-content col-lg-9 col-md-12">
                <h1 class="text-uppercase">
                    Transform school management<br>for a better WORLD
                </h1>
                <p class="pt-10 pb-10" style="color:#eee;max-width:620px;">
                    ShuleXpert is a complete school management platform — fees &amp; control numbers, biometric attendance,
                    exams &amp; results, parent portal, library, store, SMS, and more. Built for preschool to advanced schools.
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
            <div class="col-lg-4">
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
            <div class="col-lg-4">
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
            <div class="col-lg-4">
                <div class="single-feature sx-feature-card">
                    <div class="title">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <h4>Parent Portal</h4>
                    </div>
                    <div class="desc-wrap">
                        <p>Results, attendance, fees, and permissions — always available on web and mobile.</p>
                        <a href="{{ route('site.module', 'parent-portal') }}">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modules grid (like popular courses) --}}
<section class="popular-course-area section-gap sx-modules-showcase" style="--sx-modules-bg: url('{{ asset('images/c1.webp') }}')">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">Modules that power your school</h1>
                    <p>Every major ShuleXpert capability — mapped from the live system your school already runs on.</p>
                </div>
            </div>
        </div>
        <div class="row sx-modules-grid">
            @foreach(array_slice($modules, 0, 8) as $module)
                <div class="col-lg-3 col-md-6 sx-module-col">
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
<section class="info-area pb-120 sx-soft-band">
    <div class="container section-gap">
        <div class="row align-items-center">
            <div class="col-lg-6 info-area-left">
                <img class="img-fluid" src="{{ asset('images/african-children-primary-school-classroom-johannesburg-south-africa-september-176110088.webp') }}" alt="ShuleXpert in schools">
            </div>
            <div class="col-lg-6 info-area-right">
                <h1>Built for schools worldwide</h1>
                <p>
                    From single campuses to multi-branch institutions, ShuleXpert unifies administration, academics, finance, and parent communication —
                    with school-level branding, academic-year awareness, and role-based portals for Admin, Teachers, Staff, Parents, and Watchmen.
                </p>
                <p class="mt-20">
                    No more scattered spreadsheets. One platform for enrollments, fees, attendance, exams, library, store, hostel, discipline, and strategic performance.
                </p>
                <a href="{{ route('site.about') }}" class="primary-btn text-uppercase mt-30">About ShuleXpert</a>
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
                <h1 class="text-white">Ready to digitize your school?</h1>
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
                    <h1 class="mb-10">The team behind ShuleXpert</h1>
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
<section class="review-area section-gap">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">Trusted by school leaders</h1>
                    <p>What administrators, teachers, and parents say about ShuleXpert.</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="single-review" style="background:#fff;border:1px solid #f0dada;padding:28px;margin-bottom:20px;">
                    <div class="star"><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span></div>
                    <p class="mt-20">Fee control numbers and SMS reminders transformed our collections. Parents pay with confidence.</p>
                    <h4 class="mt-20" style="color:#940000;">School Administrator</h4>
                    <p>Secondary School, Kilimanjaro</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-review" style="background:#fff;border:1px solid #f0dada;padding:28px;margin-bottom:20px;">
                    <div class="star"><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span></div>
                    <p class="mt-20">Entering results and publishing to parents is finally simple. The teacher portal saves hours every week.</p>
                    <h4 class="mt-20" style="color:#940000;">Subject Teacher</h4>
                    <p>High School, Moshi</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-review" style="background:#fff;border:1px solid #f0dada;padding:28px;margin-bottom:20px;">
                    <div class="star"><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span><span class="fa fa-star checked"></span></div>
                    <p class="mt-20">I get attendance and results on my phone. Fee balances are clear — I always know what I owe.</p>
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
                <h1>Start with ShuleXpert today</h1>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('online_application') }}">Online Application</a>
            </div>
        </div>
    </div>
</section>
@endsection
