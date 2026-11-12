<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js" data-sx-theme="light" data-sx-site="public">
<head>
    @include('includes.theme_init')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="google-site-verification" content="hhr5zKuMzQymlmmZu5nBjh5tHwlJY_-qEk_7i9Uzo0Q" />
    <meta name="author" content="EmCa Techonology">
    <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
    <meta name="theme-color" content="#940000">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seo = $seo ?? [];
        $pageTitle = $seo['title'] ?? 'ShuleXpert | School Management System';
        $pageDesc = $seo['description'] ?? 'ShuleXpert school management platform by EmCa Techonology.';
        $pageKeywords = $seo['keywords'] ?? 'ShuleXpert, school management, EmCa Techonology';
        $canonical = $seo['canonical'] ?? url()->current();
        $ogType = $seo['type'] ?? 'website';
        $ogImage = $seo['image'] ?? url('/images/shuleXpert.jpg');
        $sp = asset('studypresshtml-62/studypresshtml-62/studypress-client-files-v6.2/studypress-bootstrap5-new-version');
        $sxCssVer = static function (string $rel): string {
            $path = public_path($rel);
            return is_file($path) ? (string) filemtime($path) : (string) time();
        };
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:locale" content="en_TZ">
    <meta property="og:locale:alternate" content="sw_TZ">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="ShuleXpert">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $seo['image_alt'] ?? $pageTitle }}">
    @if(!empty($seo['modified_time']))
    <meta property="og:updated_time" content="{{ $seo['modified_time'] }}">
    <meta property="article:modified_time" content="{{ $seo['modified_time'] }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $seo['image_alt'] ?? $pageTitle }}">
    <link rel="alternate" hreflang="en" href="{{ $canonical }}">
    <link rel="alternate" hreflang="sw" href="{{ $canonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    @include('includes.site_favicon')

    {{-- StudyPress multipage layout2 assets (exact) --}}
    <link href="{{ $sp }}/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $sp }}/css/animate.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $sp }}/css/javascript-plugins-bundle.css" rel="stylesheet">
    <link href="{{ $sp }}/js/menuzord/css/menuzord.css" rel="stylesheet">
    <link href="{{ $sp }}/css/style-main.css" rel="stylesheet" type="text/css">
    <link id="menuzord-menu-skins" href="{{ $sp }}/css/menuzord-skins/menuzord-rounded-boxed.css" rel="stylesheet">
    <link href="{{ $sp }}/css/responsive.css" rel="stylesheet" type="text/css">
    <link href="{{ $sp }}/css/colors/theme-skin-color-set1.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/shulexpert-studypress.css') }}?v={{ $sxCssVer('css/shulexpert-studypress.css') }}">

    <script src="{{ $sp }}/js/jquery.js"></script>
    <script src="{{ $sp }}/js/popper.min.js"></script>
    <script src="{{ $sp }}/js/bootstrap.min.js"></script>
    <script src="{{ $sp }}/js/javascript-plugins-bundle.js"></script>
    <script src="{{ $sp }}/js/menuzord/js/menuzord.js"></script>

    {{-- Revolution Slider (layout2) --}}
    <link rel="stylesheet" type="text/css" href="{{ $sp }}/js/revolution-slider/css/rs6.css">
    <link rel="stylesheet" type="text/css" href="{{ $sp }}/js/revolution-slider/extra-rev-slider1.css">
    <script src="{{ $sp }}/js/revolution-slider/js/revolution.tools.min.js"></script>
    <script src="{{ $sp }}/js/revolution-slider/js/rs6.min.js"></script>
    <script src="{{ $sp }}/js/revolution-slider/extra-rev-slider1.js"></script>

    @stack('styles')
    {{-- Dark mode last so it overrides page-local light colors --}}
    <link rel="stylesheet" href="{{ asset('css/shulexpert-studypress-dark.css') }}?v={{ $sxCssVer('css/shulexpert-studypress-dark.css') }}">
    @include('site.partials.schema')
</head>
<body class="tm-container-1340px has-side-panel side-panel-right sx-public-site">
@php $sp = asset('studypresshtml-62/studypresshtml-62/studypress-client-files-v6.2/studypress-bootstrap5-new-version'); @endphp

{{-- preloader (layout2) --}}
<div id="preloader">
  <div id="spinner">
    <div class="preloader-dot-loading">
      <div class="cssload-loading"><i></i><i></i><i></i><i></i></div>
    </div>
  </div>
  <div id="disable-preloader" class="btn btn-default btn-sm bg-white-f5">Disable Preloader</div>
</div>

<div class="side-panel-body-overlay"></div>
<div id="side-panel-container" class="sx-side-panel dark">
  <div class="side-panel-wrap sx-side-panel__wrap">
    <div id="side-panel-trigger-close" class="side-panel-trigger sx-side-panel__close">
      <a href="#" aria-label="Close"><i class="fa fa-times side-panel-trigger-icon" aria-hidden="true"></i></a>
    </div>

    <div class="sx-side-panel__brand">
      <div class="sx-side-panel__logo-wrap">
        <img class="logo sx-site-logo-side" src="{{ asset('shule.png') }}" alt="ShuleXpert" width="180" height="72">
      </div>
      <p class="sx-side-panel__tagline">{{ __('site.footer_tagline') }}</p>
    </div>

    <div class="sx-side-panel__section">
      <h5 class="sx-side-panel__heading">{{ __('site.contact') }}</h5>
      <ul class="sx-side-panel__contacts">
        <li>
          <a href="tel:{{ config('shulexpert.phone_e164') }}">
            <span class="sx-side-panel__icon" aria-hidden="true"><i class="fa fa-phone"></i></span>
            <span>
              <small>{{ __('site.footer_call_us') }}</small>
              <strong>{{ config('shulexpert.phone_display') }}</strong>
            </span>
          </a>
        </li>
        <li>
          <a href="mailto:{{ config('shulexpert.email', 'emca@emcatech.co.tz') }}">
            <span class="sx-side-panel__icon" aria-hidden="true"><i class="fa fa-envelope"></i></span>
            <span>
              <small>{{ __('site.footer_email_us') }}</small>
              <strong>{{ config('shulexpert.email', 'emca@emcatech.co.tz') }}</strong>
            </span>
          </a>
        </li>
        <li>
          <span class="sx-side-panel__static">
            <span class="sx-side-panel__icon" aria-hidden="true"><i class="fa fa-map-marker"></i></span>
            <span>
              <small>{{ __('site.office') }}</small>
              <strong>Moshi, Kilimanjaro</strong>
            </span>
          </span>
        </li>
      </ul>
    </div>

    <div class="sx-side-panel__section sx-side-panel__section--links">
      <h5 class="sx-side-panel__heading">{{ __('site.quick_links') }}</h5>
      <div class="sx-side-panel__links">
        <a href="{{ route('site.download') }}"><i class="fab fa-android" aria-hidden="true"></i>{{ __('site.download_app') }}</a>
        <a href="{{ route('site.contact') }}"><i class="fa fa-headset" aria-hidden="true"></i>{{ __('site.contact_us') }}</a>
        <a href="{{ route('online_application') }}"><i class="fa fa-graduation-cap" aria-hidden="true"></i>{{ __('site.online_application') }}</a>
      </div>
    </div>

    <div class="sx-side-panel__prefs">
      <span class="sx-side-panel__prefs-label">{{ __('site.language') }} / {{ __('site.dark_mode') }}</span>
      @include('site.partials.prefs')
    </div>
  </div>
</div>

<div id="wrapper" class="clearfix">
    @include('site.partials.header')

    <div class="main-content-area" id="main-content">
        @yield('content')
    </div>

    @include('site.partials.footer')
    <a class="scrollToTop" href="#"><i class="fa fa-angle-up"></i></a>
</div>

{{-- Tawk.to live chat (public site) --}}
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6af194d1427f7a3c40c3eefd/1k6vqqllf';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>

<script src="{{ $sp }}/js/custom.js"></script>
@stack('scripts')
<script>
    (function () {
        if (window.__sxSiteLangBound) return;
        window.__sxSiteLangBound = true;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var token = csrf ? csrf.getAttribute('content') : '';
        function setLocale(locale) {
            fetch('{{ route('change_language') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ locale: locale })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) { window.location.reload(); return; }
                alert((data && data.message) ? data.message : 'Failed to change language.');
            })
            .catch(function () { alert('Failed to change language. Please try again.'); });
        }
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.sx-lang-btn');
            if (!btn) return;
            e.preventDefault();
            var locale = btn.getAttribute('data-locale');
            if (!locale || btn.classList.contains('is-active')) return;
            setLocale(locale);
        });
    })();
</script>
</body>
</html>
