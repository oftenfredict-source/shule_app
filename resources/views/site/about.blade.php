@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner sx-about-banner" id="home">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">About Us</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.about') }}">About Us</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="feature-area sx-about-features">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="single-feature">
                    <div class="title"><h4>End-to-End Platform</h4></div>
                    <div class="desc-wrap">
                        <p>People, academics, fees, attendance, exams, library, store, hostel, SMS, and performance — one system.</p>
                        <a href="{{ route('site.modules') }}">See Modules</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-feature">
                    <div class="title"><h4>Role-Based Portals</h4></div>
                    <div class="desc-wrap">
                        <p>Admin, Teacher, Staff, Parent, and Watchman experiences tailored to real school workflows.</p>
                        <a href="{{ route('login') }}">Login</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-feature">
                    <div class="title"><h4>Locally Built</h4></div>
                    <div class="desc-wrap">
                        <p>Designed and supported by EmCa Techonology in Moshi, Kilimanjaro for schools worldwide.</p>
                        <a href="{{ route('site.contact') }}">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="info-area sx-about-info section-gap">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 no-padding info-area-left">
                <img class="img-fluid" src="{{ asset('images/school.png') }}" alt="About ShuleXpert">
            </div>
            <div class="col-lg-6 info-area-right">
                <h1>Who we are</h1>
                <p>
                    ShuleXpert is a multi-tenant school management SaaS that helps schools run operations digitally —
                    from student enrollment and fee collection with control numbers, to biometric attendance, examinations,
                    parent communication, and strategic performance management.
                </p>
                <p class="mt-20">
                    The platform is engineered and maintained by <strong>EmCa Techonology</strong>, with a focus on reliability,
                    school branding, academic-year awareness, and practical tools that administrators, teachers, and parents use daily.
                </p>
                <ul class="sx-point-list mt-30">
                    <li>Preschool, primary, secondary &amp; advanced support</li>
                    <li>SMS-ready parent communication</li>
                    <li>Fingerprint attendance (ZKTeco) ready</li>
                    <li>Fees, exams, library, store &amp; hostel modules</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h1>Partner with EmCa Techonology</h1>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('site.contact') }}">Get in Touch</a>
            </div>
        </div>
    </div>
</section>
@endsection
