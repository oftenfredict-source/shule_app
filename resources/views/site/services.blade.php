@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Services</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.services') }}">Services</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="feature-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="single-feature">
                    <div class="title"><h4>School Digitization</h4></div>
                    <div class="desc-wrap">
                        <p>Full platform deployment for academics, fees, attendance, exams, library, store and parent communication.</p>
                        <a href="{{ route('site.modules') }}">View Modules</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-feature">
                    <div class="title"><h4>Training &amp; Onboarding</h4></div>
                    <div class="desc-wrap">
                        <p>Role-based training for Admin, Teachers, Staff and Accountants included in the installation package.</p>
                        <a href="{{ route('pricing') }}">See Pricing</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-feature">
                    <div class="title"><h4>SMS &amp; Support</h4></div>
                    <div class="desc-wrap">
                        <p>Ongoing SMS notification services, helpdesk support, updates and secure cloud hosting for your school.</p>
                        <a href="{{ route('site.contact') }}">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-50 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">What EmCa Techonology delivers</h1>
                    <p>Practical services that help schools worldwide go digital and stay supported.</p>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach([
                ['icon'=>'fa-cogs','title'=>'Implementation','text'=>'School setup, branding, academic years, fee structures, roles and go-live checklist.'],
                ['icon'=>'fa-chalkboard-teacher','title'=>'Capacity Building','text'=>'Hands-on training so every user role can operate ShuleXpert confidently.'],
                ['icon'=>'fa-fingerprint','title'=>'Biometric Support','text'=>'Guidance for ZKTeco fingerprint devices, School Agent sync and attendance SMS.'],
                ['icon'=>'fa-sms','title'=>'Communication Layer','text'=>'Bulk and automated SMS for results, fees, attendance and announcements.'],
                ['icon'=>'fa-headset','title'=>'Continuous Support','text'=>'User support channels for day-to-day questions and operational issues.'],
                ['icon'=>'fa-shield-alt','title'=>'Hosting & Security','text'=>'Cloud storage, backups, updates and monitoring under EmCa Techonology.'],
            ] as $svc)
                <div class="col-lg-4 col-md-6 mb-30">
                    <div class="sx-module-card" style="padding:24px;">
                        <div class="icon-badge"><i class="fa {{ $svc['icon'] }}"></i></div>
                        <h4 style="color:#940000;font-weight:800;">{{ $svc['title'] }}</h4>
                        <p>{{ $svc['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-20">
            <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">Request Service Quotation</a>
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => 'ShuleXpert School Digitization Services',
        'provider' => ['@type' => 'Organization', 'name' => 'EmCa Techonology'],
        'areaServed' => 'TZ',
        'description' => 'Implementation, training, biometric support, SMS communication, hosting and continuous support for ShuleXpert.',
        'url' => url('/services'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
