@php
    $sp = asset('studypresshtml-62/studypresshtml-62/studypress-client-files-v6.2/studypress-bootstrap5-new-version');
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
{{-- Header: StudyPress multipage layout2 --}}
<header id="header" class="header header-layout-type-header-2rows">
  <div class="header-top">
    <div class="container">
      <div class="row sx-header-top-row align-items-center">
        <div class="col-auto header-top-left align-self-center text-start">
          <ul class="element contact-info sx-topbar-contact">
            <li class="contact-phone">
              <a href="tel:{{ config('shulexpert.phone_e164') }}" class="sx-topbar-contact-link">
                <i class="fa fa-phone font-icon" aria-hidden="true"></i>
                <span class="sx-topbar-phone-label">Tel:</span>
                <span class="sx-topbar-phone-num">{{ config('shulexpert.phone_display') }}</span>
              </a>
            </li>
            <li class="contact-email d-none d-md-inline-flex">
              <a href="mailto:{{ config('shulexpert.email_company', config('shulexpert.email', 'emca@emcatech.co.tz')) }}" class="sx-topbar-contact-link">
                <i class="fa fa-envelope font-icon" aria-hidden="true"></i>
                <span>{{ config('shulexpert.email_company', config('shulexpert.email', 'emca@emcatech.co.tz')) }}</span>
              </a>
            </li>
            <li class="contact-address d-none d-lg-inline-flex">
              <span class="sx-topbar-contact-link">
                <i class="fa fa-map font-icon" aria-hidden="true"></i>
                <span>Moshi, Kilimanjaro</span>
              </span>
            </li>
          </ul>
        </div>
        <div class="col-auto ms-auto header-top-right align-self-center text-end">
          <div class="element pt-0 pb-0 d-inline-flex align-items-center sx-topbar-actions">
            <span class="sx-header-prefs d-inline-flex">@include('site.partials.prefs')</span>
            <a href="{{ route('site.contact') }}" class="btn btn-theme-colored1 btn-sm text-white sx-topbar-contact-btn">{{ __('site.contact_us') }}</a>
            <a href="{{ route('site.faq') }}" class="sx-topbar-faq">FAQs</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="header-nav">
    <div class="header-nav-wrapper navbar-scrolltofixed">
      <div class="menuzord-container header-nav-container">
        <div class="container position-relative">
          <div class="row header-nav-col-row">
            <div class="col-sm-auto align-self-center">
              <a class="menuzord-brand site-brand" href="{{ route('site.home') }}">
                <img class="logo-default logo-1x" src="{{ asset('shule.png') }}" alt="ShuleXpert">
                <img class="logo-default logo-2x retina" src="{{ asset('shule.png') }}" alt="ShuleXpert">
              </a>
            </div>
            <div class="col-sm-auto ms-auto pr-0 align-self-center">
              <nav id="top-primary-nav" class="menuzord theme-color1" data-effect="slide" data-animation="none" data-align="right">
                <ul id="main-nav" class="menuzord-menu">
                  <li class="menu-item {{ request()->routeIs('site.home') ? 'active' : '' }}">
                    <a href="{{ route('site.home') }}">{{ __('site.home') }}</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('site.about') ? 'active' : '' }}">
                    <a href="{{ route('site.about') }}">{{ __('site.about') }}</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('site.services') ? 'active' : '' }}">
                    <a href="{{ route('site.services') }}">{{ __('site.services') }}</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('site.modules*') ? 'active' : '' }}">
                    <a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a>
                    <div class="megamenu megamenu-fullwidth megamenu-position-left">
                      <div class="megamenu-row">
                        <div class="col3">
                          <ul class="list-unstyled list-dashed">
                            <li class="menu-item">
                              <a class="tm-submenu-title" href="{{ route('site.modules') }}"><span>{{ __('site.popular_modules') }}</span></a>
                              <ul class="list-unstyled">
                                @foreach($megaFeatured->take(3) as $module)
                                  <li><a href="{{ route('site.module', $module['slug']) }}">{{ $module['title'] }}</a></li>
                                @endforeach
                              </ul>
                            </li>
                          </ul>
                        </div>
                        <div class="col3">
                          <ul class="list-unstyled list-dashed">
                            <li class="menu-item">
                              <a class="tm-submenu-title" href="{{ route('site.modules') }}"><span>{{ __('site.more') }}</span></a>
                              <ul class="list-unstyled">
                                @foreach($megaFeatured->slice(3) as $module)
                                  <li><a href="{{ route('site.module', $module['slug']) }}">{{ $module['title'] }}</a></li>
                                @endforeach
                                <li><a href="{{ route('site.modules') }}">{{ __('site.view_all_modules') }}</a></li>
                              </ul>
                            </li>
                          </ul>
                        </div>
                        <div class="col3">
                          <ul class="list-unstyled list-dashed">
                            <li class="menu-item">
                              <a class="tm-submenu-title" href="#"><span>{{ __('site.resources') }}</span></a>
                              <ul class="list-unstyled">
                                <li><a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a></li>
                                <li><a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a></li>
                                <li><a href="{{ route('site.register_school') }}">{{ __('site.register_school') }}</a></li>
                              </ul>
                            </li>
                          </ul>
                        </div>
                        <div class="col3">
                          <ul class="list-unstyled list-dashed">
                            <li class="menu-item">
                              <a class="tm-submenu-title" href="{{ route('login') }}"><span>{{ __('site.login') }}</span></a>
                              <ul class="list-unstyled">
                                <li><a href="{{ route('login') }}">{{ __('site.school_login') }}</a></li>
                                <li><a href="{{ route('site.contact') }}">{{ __('site.contact') }}</a></li>
                                <li><a href="{{ route('site.download') }}">{{ __('site.download_app') }}</a></li>
                              </ul>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </li>
                  <li class="menu-item {{ request()->routeIs('pricing') ? 'active' : '' }}">
                    <a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('online_application') || request()->is('online-application*') ? 'active' : '' }}">
                    <a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('site.contact') ? 'active' : '' }}">
                    <a href="{{ route('site.contact') }}">{{ __('site.contact') }}</a>
                  </li>
                  <li class="menu-item">
                    <a href="{{ route('login') }}">{{ __('site.login') }}</a>
                  </li>
                </ul>
              </nav>
            </div>
            <div class="col-sm-auto align-self-center nav-side-icon-parent">
              <ul class="list-inline nav-side-icon-list">
                <li class="hidden-mobile-mode"><a href="#" id="top-nav-search-btn"><i class="search-icon fa fa-search"></i></a></li>
                <li class="hidden-mobile-mode">
                  <div id="side-panel-trigger" class="side-panel-trigger">
                    <a href="#">
                      <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                      </div>
                    </a>
                  </div>
                </li>
              </ul>
              <div id="top-nav-search-form" class="clearfix">
                <form action="{{ route('site.modules') }}" method="GET">
                  <input type="text" name="q" value="" placeholder="Type and Press Enter..." autocomplete="off" />
                </form>
                <a href="#" id="close-search-btn"><i class="fa fa-times"></i></a>
              </div>
            </div>
          </div>
          <div class="row d-block d-xl-none">
            <div class="col-12">
              <nav id="top-primary-nav-clone" class="menuzord d-block d-xl-none default menuzord-color-default menuzord-border-boxed menuzord-responsive" data-effect="slide" data-animation="none" data-align="right">
                <ul id="main-nav-clone" class="menuzord-menu menuzord-right menuzord-indented scrollable"></ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
