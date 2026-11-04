<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js" data-sx-theme="light" data-sx-site="public">
<head>
    @include('includes.theme_init')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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

    @php
        $sxCssVer = static function (string $rel): string {
            $path = public_path($rel);
            return is_file($path) ? (string) filemtime($path) : (string) time();
        };
    @endphp
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
    <link rel="stylesheet" href="{{ asset('lib/animate/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-base.css') }}?v={{ $sxCssVer('css/site-base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shulexpert-site.css') }}?v={{ $sxCssVer('css/shulexpert-site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shulexpert-theme.css') }}?v={{ $sxCssVer('css/shulexpert-theme.css') }}">

    <style>
        html[data-sx-theme="light"],
        html[data-sx-theme="light"] body { background:#fff!important }
        html[data-sx-theme="light"] #main-content { background:#fff!important }
        html[data-sx-theme="dark"][data-sx-site="public"],
        html[data-sx-theme="dark"][data-sx-site="public"] body { background:#160d10!important }
        html[data-sx-theme="dark"][data-sx-site="public"] #main-content { background:#160d10!important }
        /* Critical header prefs - keeps EN/SW styled even if CSS file is stale/cached */
        .sx-header-top-tools{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
        .sx-header-top-link{color:#fff!important;font-size:13px}
        .sx-site-prefs{display:inline-flex;align-items:center;gap:8px}
        .sx-lang-switch{display:inline-flex;border:1px solid rgba(255,255,255,.35);overflow:hidden;background:rgba(0,0,0,.15)}
        .sx-lang-btn{border:0;background:transparent;color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em;padding:6px 10px;cursor:pointer;line-height:1;min-width:36px}
        .sx-lang-btn.is-active{background:#fff;color:#940000}
        .sx-site-header .header-top .sx-theme-toggle{width:32px;height:32px;border-color:rgba(255,255,255,.4);background:rgba(255,255,255,.12);color:#fff}
    </style>
    @unless(!empty($skipSplash))
    <style>
        #sx-splash{position:fixed;inset:0;z-index:99999;background:#fff!important;display:flex;align-items:center;justify-content:center;transition:opacity .45s ease,visibility .45s ease}
        html[data-sx-theme="dark"] #sx-splash{background:#000!important}
        #sx-splash.hide{opacity:0;visibility:hidden;pointer-events:none}
        #sx-splash .sx-splash-inner{text-align:center;width:min(280px,82vw);background:transparent!important;border:none!important;box-shadow:none!important;padding:0}
        #sx-splash .sx-splash-logo{display:block!important;width:auto!important;height:auto!important;max-width:140px;max-height:140px;margin:0 auto 28px;padding:0!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;filter:none!important;-webkit-filter:none!important;animation:none!important;transition:none!important;transform:none!important;object-fit:contain!important}
        #sx-splash .sx-splash-bar{width:100%;height:4px;margin:0 auto 16px;background:rgba(148,0,0,.12);border-radius:999px;overflow:hidden}
        #sx-splash .sx-splash-bar-fill{display:block;height:100%;width:0;background:#940000;border-radius:999px;animation:sxSplashLoad 1.35s ease-out forwards}
        #sx-splash .sx-splash-powered{margin:0;font-size:12px;font-weight:600;color:#666}
        html[data-sx-theme="dark"] #sx-splash .sx-splash-powered{color:#ddd}
        @keyframes sxSplashLoad{from{width:0}to{width:100%}}
        body.sx-splash-lock{overflow:hidden}
        body.sx-splash-lock #main-content,
        body.sx-splash-lock #header,
        body.sx-splash-lock .footer-area,
        body.sx-splash-lock #sx-mobile-nav,
        body.sx-splash-lock #sx-mobile-nav-backdrop{visibility:hidden!important}
    </style>
    @endunless

    @stack('styles')

    @include('site.partials.schema')
</head>
<body class="sx-public-site{{ empty($skipSplash) ? ' sx-splash-lock' : '' }}">
    @unless(!empty($skipSplash))
        @include('site.partials.splash')
    @endunless
    @include('site.partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('site.partials.footer')
    @include('site.partials.ask-float')

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
                    if (data && data.success) {
                        window.location.reload();
                        return;
                    }
                    alert((data && data.message) ? data.message : 'Failed to change language.');
                })
                .catch(function () {
                    alert('Failed to change language. Please try again.');
                });
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
