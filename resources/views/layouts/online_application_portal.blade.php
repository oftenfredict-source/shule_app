<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="{{ $seo['robots'] ?? 'noindex, follow' }}">
    <meta name="theme-color" content="#940000">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seo = $seo ?? [];
        $pageTitle = $seo['title'] ?? 'ShuleXpert Online Application Portal';
        $pageDesc = $seo['description'] ?? 'ShuleXpert online school application portal.';
        $canonical = $seo['canonical'] ?? url()->current();
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <link rel="canonical" href="{{ $canonical }}">

    @include('includes.site_favicon')

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/site-base.css') }}?v={{ filemtime(public_path('css/site-base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/shulexpert-site.css') }}?v={{ filemtime(public_path('css/shulexpert-site.css')) }}">
    @stack('styles')
</head>
<body class="sx-oa-portal-body">
    @include('online_application.partials.portal-header')

    <main id="main-content" class="sx-oa-portal-main">
        @yield('content')
    </main>

    <footer class="sx-oa-portal-footer">
        <div class="container">
            <p class="mb-0">
                &copy; {{ date('Y') }} <strong>ShuleXpert</strong> Online Application Portal
                · Powered by <strong>EmCa Techonology</strong>
            </p>
            <a href="{{ route('online_application.portal.apply') }}">Browse schools</a>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        var toggle = document.getElementById('sxOaMenuToggle');
        var header = document.querySelector('.sx-oa-header');
        var nav = document.getElementById('sxOaHeaderNav');
        if (!toggle || !header || !nav) return;

        function setOpen(open) {
            header.classList.toggle('is-nav-open', open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            var icon = toggle.querySelector('i');
            if (icon) icon.className = open ? 'fa fa-times' : 'fa fa-bars';
        }

        toggle.addEventListener('click', function () {
            setOpen(!header.classList.contains('is-nav-open'));
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
