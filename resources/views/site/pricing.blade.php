@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Pricing</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('pricing') }}">Pricing</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-gap sx-soft-band">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-50 col-lg-9">
                <div class="title text-center">
                    <h1 class="mb-10">Transparent ShuleXpert pricing</h1>
                    <p>
                        Simple, school-friendly pricing from EmCa Techonology:
                        a one-time installation investment, then a clear monthly fee per student.
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Installation --}}
            <div class="col-lg-6 mb-30">
                <div class="sx-price-card featured" style="height:100%;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                        <div>
                            <span style="display:inline-block;background:#940000;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;letter-spacing:.06em;text-transform:uppercase;">One-time</span>
                            <h3 style="color:#940000;font-weight:800;margin:14px 0 6px;">Installation Fee</h3>
                            <p style="margin:0;color:#666;">Paid once when your school goes live on ShuleXpert.</p>
                        </div>
                        <div style="text-align:right;">
                            <div class="price" style="font-size:34px;line-height:1;">TZS 1,000,000</div>
                            <small style="color:#888;">One-time setup</small>
                        </div>
                    </div>

                    <h5 style="color:#222;font-weight:700;margin:28px 0 12px;border-top:1px solid #f0dada;padding-top:18px;">What this covers</h5>
                    <ul class="sx-point-list">
                        <li><strong>Professional training</strong>: hands-on sessions for Admin, Teachers, Staff &amp; Accountant users</li>
                        <li><strong>Expert facilitator fees</strong>: specialists who deliver onboarding and role-based coaching</li>
                        <li><strong>Secure cloud storage setup</strong>: school data hosting, backups &amp; environment provisioning</li>
                        <li><strong>System configuration</strong>: academic years, classes, fee structures, roles &amp; branding</li>
                        <li><strong>Go-live support</strong>: supervised launch checklist and first-week hypercare</li>
                        <li><strong>Data migration assistance</strong>: import guidance for students, parents &amp; existing records</li>
                    </ul>
                </div>
            </div>

            {{-- Monthly --}}
            <div class="col-lg-6 mb-30">
                <div class="sx-price-card" style="height:100%;border-top:3px solid #940000;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                        <div>
                            <span style="display:inline-block;background:#FFF5F5;color:#940000;border:1px solid #f0dada;font-size:11px;font-weight:700;padding:4px 10px;letter-spacing:.06em;text-transform:uppercase;">Monthly</span>
                            <h3 style="color:#940000;font-weight:800;margin:14px 0 6px;">Per Student Subscription</h3>
                            <p style="margin:0;color:#666;">Billed monthly based on active enrolled students.</p>
                        </div>
                        <div style="text-align:right;">
                            <div class="price" style="font-size:34px;line-height:1;">TZS 500</div>
                            <small style="color:#888;">per student / month</small>
                        </div>
                    </div>

                    <h5 style="color:#222;font-weight:700;margin:28px 0 12px;border-top:1px solid #f0dada;padding-top:18px;">What this covers</h5>
                    <ul class="sx-point-list">
                        <li><strong>SMS notification services</strong>: results, attendance, fee reminders &amp; school announcements</li>
                        <li><strong>Continuous user support</strong>: helpdesk for Admin, Teachers, Parents &amp; Staff</li>
                        <li><strong>Platform updates &amp; security</strong>: ongoing improvements, patches &amp; monitoring</li>
                        <li><strong>Parent portal access</strong>: fees, results, attendance &amp; permissions online</li>
                        <li><strong>System uptime &amp; backups</strong>: reliable hosting with routine data protection</li>
                        <li><strong>Feature enhancements</strong>: new modules and UX improvements as ShuleXpert grows</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Example calculator --}}
        <div class="row mt-20">
            <div class="col-12">
                <div style="background:#fff;border:1px solid #f0dada;padding:28px;">
                    <h4 style="color:#940000;font-weight:800;margin-bottom:8px;">Example monthly cost</h4>
                    <p style="color:#666;margin-bottom:18px;">
                        Formula: <strong>Active students × TZS 500</strong>. Installation (TZS 1,000,000) is paid once only.
                    </p>
                    <div class="row text-center">
                        <div class="col-md-4 mb-20">
                            <div style="background:linear-gradient(135deg,#FFF5F5,#FFFFFF);border:1px solid #f0dada;padding:20px;">
                                <div style="font-size:13px;color:#888;text-transform:uppercase;font-weight:700;">200 students</div>
                                <div style="font-size:26px;font-weight:800;color:#940000;margin-top:8px;">TZS 100,000<span style="font-size:13px;font-weight:600;color:#666;"> / month</span></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-20">
                            <div style="background:linear-gradient(135deg,#FFF5F5,#FFFFFF);border:1px solid #f0dada;padding:20px;">
                                <div style="font-size:13px;color:#888;text-transform:uppercase;font-weight:700;">500 students</div>
                                <div style="font-size:26px;font-weight:800;color:#940000;margin-top:8px;">TZS 250,000<span style="font-size:13px;font-weight:600;color:#666;"> / month</span></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-20">
                            <div style="background:linear-gradient(135deg,#FFF5F5,#FFFFFF);border:1px solid #f0dada;padding:20px;">
                                <div style="font-size:13px;color:#888;text-transform:uppercase;font-weight:700;">1,000 students</div>
                                <div style="font-size:26px;font-weight:800;color:#940000;margin-top:8px;">TZS 500,000<span style="font-size:13px;font-weight:600;color:#666;"> / month</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-40">
            <div class="col-lg-8 mx-auto text-center">
                <p style="color:#555;margin-bottom:22px;">
                    Need a quotation for your school size, biometric devices, or multi-campus setup?
                    Talk to EmCa Techonology, we will tailor onboarding to your needs.
                </p>
                <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">Request Quotation</a>
                <a href="{{ route('online_application') }}" class="primary-btn wh text-uppercase ml-10">Online Application</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-two-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 cta-left">
                <h1>Start with TZS 1,000,000 installation</h1>
            </div>
            <div class="col-lg-4 cta-right">
                <a class="primary-btn wh" href="{{ route('site.contact') }}">Contact Sales</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $offerSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => 'ShuleXpert School Management System',
        'description' => 'Complete school management platform for schools worldwide with one-time installation and monthly per-student subscription.',
        'brand' => [
            '@type' => 'Brand',
            'name' => 'ShuleXpert',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
        ],
        'offers' => [
            [
                '@type' => 'Offer',
                'name' => 'Installation Fee',
                'price' => '1000000',
                'priceCurrency' => 'TZS',
                'category' => 'One-time setup',
                'description' => 'One-time installation covering training, expert facilitators, cloud storage setup, configuration, go-live support and data migration assistance.',
                'url' => url('/pricing'),
                'availability' => 'https://schema.org/InStock',
            ],
            [
                '@type' => 'Offer',
                'name' => 'Monthly Per Student Subscription',
                'price' => '500',
                'priceCurrency' => 'TZS',
                'unitText' => 'MONTH',
                'category' => 'Subscription',
                'description' => 'TZS 500 per active student per month covering SMS notifications, user support, updates, parent portal access, uptime and backups.',
                'url' => url('/pricing'),
                'availability' => 'https://schema.org/InStock',
            ],
        ],
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'How much is ShuleXpert installation?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ShuleXpert installation is a one-time fee of TZS 1,000,000 covering training, expert facilitators, cloud storage setup, system configuration, go-live support and data migration assistance.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the monthly ShuleXpert fee?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'The monthly subscription is TZS 500 per active student. It covers SMS notification services, continuous user support, platform updates, parent portal access, uptime and backups.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Who provides ShuleXpert?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ShuleXpert is powered by EmCa Techonology, based in Moshi, Kilimanjaro, Tanzania.',
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($offerSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
