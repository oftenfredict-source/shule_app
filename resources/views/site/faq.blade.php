@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">FAQ</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.faq') }}">FAQ</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-50 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">Frequently asked questions</h1>
                    <p>Quick answers about ShuleXpert pricing, modules, SMS and onboarding.</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <dl class="accordion">
                    @foreach([
                        ['q'=>'How much does ShuleXpert installation cost?','a'=>'Installation is a one-time fee of TZS 1,000,000. It covers professional training, expert facilitator fees, secure cloud storage setup, system configuration, go-live support and data migration assistance.'],
                        ['q'=>'What is the monthly subscription?','a'=>'TZS 500 per active student per month. This covers SMS notification services, continuous user support, platform updates and security, parent portal access, uptime/backups and ongoing feature enhancements.'],
                        ['q'=>'Which school levels are supported?','a'=>'Preschool, primary, secondary and advanced (O-level / A-level) schools can run on ShuleXpert.'],
                        ['q'=>'Does ShuleXpert support fee control numbers?','a'=>'Yes. Schools define fee structures, generate control numbers, track payments and send SMS reminders to parents.'],
                        ['q'=>'Is biometric attendance available?','a'=>'Yes. ShuleXpert supports ZKTeco fingerprint devices, School Agent sync and parent attendance SMS alerts.'],
                        ['q'=>'Who built ShuleXpert?','a'=>'ShuleXpert is powered by EmCa Techonology (Moshi, Kilimanjaro). Contact emca@emca.tech or '.config('shulexpert.phone_display').'.'],
                    ] as $i => $item)
                        <dt><a href="#faq{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}">{{ $item['q'] }}</a></dt>
                        <dd style="{{ $i === 0 ? '' : 'display:none;' }}">{{ $item['a'] }}</dd>
                    @endforeach
                </dl>
                <div class="text-center mt-40">
                    <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">Ask another question</a>
                    <a href="{{ route('pricing') }}" class="primary-btn wh text-uppercase ml-10">View Pricing</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $faq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'How much does ShuleXpert installation cost?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Installation is a one-time fee of TZS 1,000,000 covering training, expert facilitators, cloud storage setup, configuration, go-live support and data migration assistance.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the monthly subscription?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'TZS 500 per active student per month covering SMS notifications, user support, updates, parent portal, uptime and backups.'],
            ],
            [
                '@type' => 'Question',
                'name' => 'Who built ShuleXpert?',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'ShuleXpert is powered by EmCa Techonology in Moshi, Kilimanjaro, Tanzania.'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('scripts')
<script>
$(function () {
    $('.accordion > dt > a').on('click', function (e) {
        e.preventDefault();
        var $a = $(this);
        var $dd = $a.parent().next('dd');
        $('.accordion > dt > a').removeClass('active');
        $('.accordion > dd').slideUp(180);
        if (!$dd.is(':visible')) {
            $a.addClass('active');
            $dd.slideDown(180);
        }
    });
});
</script>
@endpush
