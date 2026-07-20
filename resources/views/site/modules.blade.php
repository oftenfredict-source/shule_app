@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Modules</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.modules') }}">Modules</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="popular-course-area section-gap courses-page">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="menu-content pb-70 col-lg-8">
                <div class="title text-center">
                    <h1 class="mb-10">Complete ShuleXpert module suite</h1>
                    <p>Each module below reflects a live capability in the ShuleXpert system.</p>
                </div>
            </div>
        </div>
        <div class="row sx-modules-grid">
            @foreach($modules as $module)
                <div class="col-lg-3 col-md-6 sx-module-col">
                    <div class="sx-module-card">
                        <div class="thumb thumb-icon" aria-hidden="true">
                            <i class="fa {{ $module['icon'] }}"></i>
                        </div>
                        <div class="details">
                            <a href="{{ route('site.module', $module['slug']) }}"><h4>{{ $module['title'] }}</h4></a>
                            <p>{{ $module['short'] }}</p>
                            <a href="{{ route('site.module', $module['slug']) }}" class="primary-btn sx-module-btn">Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $itemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'ShuleXpert Modules',
        'itemListElement' => collect($modules)->values()->map(function ($m, $i) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $m['title'],
                'url' => url('/modules/' . $m['slug']),
                'description' => $m['short'],
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($itemList, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
