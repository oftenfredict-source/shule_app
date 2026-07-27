<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="google-site-verification" content="hhr5zKuMzQymlmmZu5nBjh5tHwlJY_-qEk_7i9Uzo0Q" />
    <meta name="author" content="EmCa Techonology">
    <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
    <meta name="theme-color" content="#940000">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seo = $seo ?? [];
        $pageTitle = $seo['title'] ?? 'ShuleXpert — School Management System';
        $pageDesc = $seo['description'] ?? 'ShuleXpert school management platform by EmCa Techonology.';
        $pageKeywords = $seo['keywords'] ?? 'ShuleXpert, school management, EmCa Techonology';
        $canonical = $seo['canonical'] ?? url()->current();
        $ogType = $seo['type'] ?? 'website';
        $ogImage = $seo['image'] ?? url('/images/shuleXpert.jpg');
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:locale" content="en_TZ">
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
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    @include('includes.site_favicon')

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
    <link rel="stylesheet" href="{{ asset('lib/animate/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-base.css') }}?v={{ filemtime(public_path('css/site-base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/shulexpert-site.css') }}?v={{ filemtime(public_path('css/shulexpert-site.css')) }}">

    @stack('styles')

    @include('site.partials.schema')
</head>
<body class="{{ empty($skipSplash) ? 'sx-splash-lock' : '' }}">
    @unless(!empty($skipSplash))
        @include('site.partials.splash')
    @endunless
    @include('site.partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('site.partials.footer')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function ($) {
            if (!$ || !$('#header').length) return;
            $(window).on('scroll', function () {
                if ($(this).scrollTop() > 100) {
                    $('#header').addClass('header-scrolled');
                } else {
                    $('#header').removeClass('header-scrolled');
                }
            }).trigger('scroll');
        })(window.jQuery);
    </script>
    <script>
        (function () {
            var splash = document.getElementById('sx-splash');
            function hideSplash() {
                if (!splash) return;
                splash.classList.add('hide');
                document.body.classList.remove('sx-splash-lock');
                setTimeout(function () {
                    if (splash && splash.parentNode) splash.parentNode.removeChild(splash);
                }, 750);
            }
            window.addEventListener('load', function () {
                setTimeout(hideSplash, 1600);
            });
            setTimeout(hideSplash, 4200);
        })();
    </script>
    <script>
        (function () {
            var mega = document.querySelector('.sx-modules-mega');
            if (!mega) return;
            var panel = mega.querySelector('.sx-mega-panel');
            var timer = null;
            function openMega() {
                if (timer) clearTimeout(timer);
                mega.classList.add('is-open');
            }
            function closeMega() {
                timer = setTimeout(function () {
                    mega.classList.remove('is-open');
                }, 120);
            }
            mega.addEventListener('mouseenter', openMega);
            mega.addEventListener('mouseleave', closeMega);
            if (panel) {
                panel.addEventListener('mouseenter', openMega);
                panel.addEventListener('mouseleave', closeMega);
            }
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') mega.classList.remove('is-open');
            });
        })();
    </script>
    <script>
        (function () {
            var toggle = document.getElementById('sx-mobile-nav-toggle');
            var panel = document.getElementById('sx-mobile-nav');
            var backdrop = document.getElementById('sx-mobile-nav-backdrop');
            if (!toggle || !panel) return;

            function setOpen(open) {
                toggle.classList.toggle('is-open', open);
                panel.classList.toggle('is-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.classList.toggle('sx-mobile-nav-open', open);
                if (backdrop) backdrop.hidden = !open;
            }

            toggle.addEventListener('click', function () {
                setOpen(!panel.classList.contains('is-open'));
            });

            if (backdrop) {
                backdrop.addEventListener('click', function () {
                    setOpen(false);
                });
            }

            panel.addEventListener('click', function (e) {
                var submenuBtn = e.target.closest('.sx-mobile-submenu-toggle');
                if (submenuBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var parent = submenuBtn.closest('.sx-mobile-has-children');
                    if (!parent) return;
                    var expanded = parent.classList.toggle('is-expanded');
                    submenuBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    return;
                }

                var link = e.target.closest('a');
                if (link && panel.contains(link)) {
                    setOpen(false);
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setOpen(false);
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 991) setOpen(false);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
