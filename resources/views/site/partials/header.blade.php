@php
    $headerModules = array_values(\App\Http\Controllers\SiteController::modules());
    $megaFeaturedSlugs = [
        'lesson-plans',
        'scheme-of-work',
        'fees-payments',
        'attendance-biometric',
        'exams-results',
        'parent-portal',
    ];
    $megaFeatured = collect($headerModules)
        ->filter(fn ($module) => in_array($module['slug'], $megaFeaturedSlugs, true))
        ->values();
@endphp
<header id="header" class="sx-site-header">
    <div class="header-top">
        <div class="container sx-header-inner">
            <div class="row align-items-center">
                <div class="col-lg-5 col-sm-5 col-6 header-top-left no-padding">
                    <ul>
                        <li><a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa fa-youtube-play"></i></a></li>
                    </ul>
                </div>
                <div class="col-lg-7 col-sm-7 col-6 header-top-right no-padding">
                    <div class="sx-header-top-tools">
                        @include('site.partials.prefs')
                        <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-header-top-link d-none d-md-inline-flex"><span class="lnr lnr-phone-handset"></span> <span class="text">{{ config('shulexpert.phone_display') }}</span></a>
                        <a href="mailto:emca@emca.tech" class="sx-header-top-link d-none d-lg-inline-flex"><span class="lnr lnr-envelope"></span> <span class="text">emca@emca.tech</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main-menu">
        <div class="container sx-header-inner">
            <div class="sx-nav-bar">
                <div id="logo">
                <a class="sx-brand" href="{{ route('site.home') }}">
                    <img class="sx-logo" src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert">
                    <span>
                        <span class="sx-brand-text">ShuleXpert</span>
                        <span class="sx-brand-sub">EmCa Techonology</span>
                    </span>
                </a>
                </div>
                <button type="button" class="sx-mobile-nav-toggle" id="sx-mobile-nav-toggle" aria-label="{{ __('site.open_menu') }}" aria-expanded="false" aria-controls="sx-mobile-nav">
                    <span></span><span></span><span></span>
                </button>
                <nav id="nav-menu-container">
                <ul class="nav-menu">
                    <li class="{{ request()->routeIs('site.home') ? 'active' : '' }}">
                        <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('site.about') ? 'active' : '' }}">
                        <a href="{{ route('site.about') }}">{{ __('site.about') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('site.team') ? 'active' : '' }}">
                        <a href="{{ route('site.team') }}">{{ __('site.team') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('site.services') ? 'active' : '' }}">
                        <a href="{{ route('site.services') }}">{{ __('site.services') }}</a>
                    </li>
                    <li class="menu-has-children sx-modules-mega {{ request()->routeIs('site.modules*') ? 'active' : '' }}">
                        <a href="{{ route('site.modules') }}">{{ __('site.modules') }} <i class="fa fa-angle-down sx-mega-caret" aria-hidden="true"></i></a>
                        <div class="sx-mega-panel" aria-label="ShuleXpert modules">
                            <div class="sx-mega-panel-inner">
                                <div class="sx-mega-head">
                                    <div class="sx-mega-head__text">
                                        <h4>{{ __('site.popular_modules') }}</h4>
                                        <p>{{ __('site.popular_modules_desc') }}</p>
                                    </div>
                                    <a href="{{ route('site.modules') }}" class="sx-mega-head__link">{{ __('site.view_all_modules') }} <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                                </div>
                                <div class="sx-mega-grid">
                                    @foreach($megaFeatured as $module)
                                        <a href="{{ route('site.module', $module['slug']) }}" class="sx-mega-item">
                                            <span class="sx-mega-icon"><i class="fa {{ $module['icon'] }}" aria-hidden="true"></i></span>
                                            <span class="sx-mega-copy">
                                                <strong>{{ $module['title'] }}</strong>
                                                <small>{{ \Illuminate\Support\Str::limit($module['short'] ?? '', 56) }}</small>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="sx-mega-foot">
                                    <a href="{{ route('site.modules') }}" class="sx-mega-more">
                                        <span class="sx-mega-icon"><i class="fa fa-th-large" aria-hidden="true"></i></span>
                                        <span class="sx-mega-copy">
                                            <strong>{{ __('site.more') }}</strong>
                                            <small>{{ __('site.view_all_modules') }}</small>
                                        </span>
                                        <i class="fa fa-angle-right sx-mega-more__arrow" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="{{ request()->routeIs('pricing') ? 'active' : '' }}">
                        <a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('site.faq') ? 'active' : '' }}">
                        <a href="{{ route('site.faq') }}">{{ __('site.faq') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('online_application*') ? 'active' : '' }}">
                        <a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('site.register_school') ? 'active' : '' }}">
                        <a href="{{ route('site.register_school') }}">{{ __('site.register_school') }}</a>
                    </li>
                    <li class="{{ request()->routeIs('site.contact') ? 'active' : '' }}">
                        <a href="{{ route('site.contact') }}">{{ __('site.contact') }}</a>
                    </li>
                    <li class="sx-login-link">
                        <a href="{{ route('login') }}">{{ __('site.login') }}</a>
                    </li>
                </ul>
                </nav>
            </div>
        </div>
    </div>
    <nav id="sx-mobile-nav" class="sx-mobile-nav" aria-hidden="true" aria-label="Mobile navigation">
        <div class="sx-mobile-prefs">
            @include('site.partials.prefs')
        </div>
        <ul>
            <li class="{{ request()->routeIs('site.home') ? 'active' : '' }}"><a href="{{ route('site.home') }}">{{ __('site.home') }}</a></li>
            <li class="{{ request()->routeIs('site.about') ? 'active' : '' }}"><a href="{{ route('site.about') }}">{{ __('site.about') }}</a></li>
            <li class="{{ request()->routeIs('site.team') ? 'active' : '' }}"><a href="{{ route('site.team') }}">{{ __('site.team') }}</a></li>
            <li class="{{ request()->routeIs('site.services') ? 'active' : '' }}"><a href="{{ route('site.services') }}">{{ __('site.services') }}</a></li>
            <li class="sx-mobile-has-children {{ request()->routeIs('site.modules*') ? 'active' : '' }}">
                <button type="button" class="sx-mobile-submenu-toggle" aria-expanded="false">{{ __('site.modules') }} <i class="fa fa-chevron-down" aria-hidden="true"></i></button>
                <ul class="sx-mobile-submenu">
                    @foreach($megaFeatured as $module)
                        <li><a href="{{ route('site.module', $module['slug']) }}">{{ $module['title'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('site.modules') }}">{{ __('site.view_all_modules') }}</a></li>
                </ul>
            </li>
            <li class="{{ request()->routeIs('pricing') ? 'active' : '' }}"><a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a></li>
            <li class="{{ request()->routeIs('site.faq') ? 'active' : '' }}"><a href="{{ route('site.faq') }}">{{ __('site.faq') }}</a></li>
            <li class="{{ request()->routeIs('online_application*') ? 'active' : '' }}"><a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a></li>
            <li class="{{ request()->routeIs('site.register_school') ? 'active' : '' }}"><a href="{{ route('site.register_school') }}">{{ __('site.register_school') }}</a></li>
            <li class="{{ request()->routeIs('site.contact') ? 'active' : '' }}"><a href="{{ route('site.contact') }}">{{ __('site.contact') }}</a></li>
            <li><a href="{{ route('login') }}">{{ __('site.login') }}</a></li>
        </ul>
    </nav>
    <div class="sx-mobile-nav-backdrop" id="sx-mobile-nav-backdrop" hidden></div>
</header>
