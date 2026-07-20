@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">{{ $module['title'] }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.modules') }}">Modules</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.module', $module['slug']) }}">{{ $module['title'] }}</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="course-details-area section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 left-contents">
                <div class="main-image mb-30 sx-module-hero-icon">
                    <i class="fa {{ $module['icon'] }}" aria-hidden="true"></i>
                </div>
                <h2 style="color:#940000;font-weight:800;margin-bottom:16px;">{{ $module['title'] }}</h2>
                <p style="font-size:15px;line-height:1.8;">{{ $module['body'] }}</p>
                <h4 class="mt-40 mb-20" style="color:#222;">Key capabilities</h4>
                <ul class="sx-point-list">
                    @foreach($module['points'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
                <div class="mt-40">
                    <a href="{{ route('site.contact') }}" class="primary-btn text-uppercase">Request Demo</a>
                    <a href="{{ route('site.modules') }}" class="primary-btn wh text-uppercase ml-10">All Modules</a>
                </div>
            </div>
            <div class="col-lg-4 right-contents">
                <ul>
                    <li>
                        <a class="justify-content-between d-flex" href="#">
                            <p>Module</p>
                            <span class="or">{{ $module['title'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="justify-content-between d-flex" href="{{ route('pricing') }}">
                            <p>Pricing</p>
                            <span>View plans</span>
                        </a>
                    </li>
                    <li>
                        <a class="justify-content-between d-flex" href="{{ route('login') }}">
                            <p>Access</p>
                            <span>School Login</span>
                        </a>
                    </li>
                    <li>
                        <a class="justify-content-between d-flex" href="{{ route('site.contact') }}">
                            <p>Support</p>
                            <span>Contact</span>
                        </a>
                    </li>
                </ul>
                <a href="{{ route('online_application') }}" class="primary-btn text-uppercase mt-20" style="display:block;">Online Application</a>

                <h4 class="mt-40 mb-20" style="color:#940000;">Other modules</h4>
                @foreach(array_slice($modules, 0, 6) as $other)
                    @if($other['slug'] !== $module['slug'])
                        <div style="border-bottom:1px solid #f0dada;padding:10px 0;">
                            <a href="{{ route('site.module', $other['slug']) }}" style="color:#222;font-weight:600;">
                                <i class="fa {{ $other['icon'] }}" style="color:#940000;margin-right:8px;"></i>{{ $other['title'] }}
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $svc = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $module['title'],
        'description' => $module['body'],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'ShuleXpert',
            'parentOrganization' => ['@type' => 'Organization', 'name' => 'EmCa Techonology'],
        ],
        'url' => url('/modules/' . $module['slug']),
        'image' => url('/images/shuleXpert.jpg'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($svc, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
