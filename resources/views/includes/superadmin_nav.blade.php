<!DOCTYPE html>
@php
  $saLocale = app()->getLocale() === 'sw' ? 'sw' : 'en';
@endphp
<html
  lang="{{ $saLocale }}"
  class="light-style"
  dir="ltr"
  data-theme="theme-default"
  data-app-sidebar="full"
>
  <head>
    @include('includes.theme_init')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="theme-color" content="#940000" />
    <title>@yield('title', 'ShuleXpert - Super Admin')</title>
    <meta name="description" content="ShuleXpert - Super Admin Portal" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @include('includes.site_favicon')

    {{-- GXON required styles --}}
    <link rel="stylesheet" href="{{ asset('gxon/libs/flaticon/css/all/all.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/lucide/lucide.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/simplebar/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/node-waves/waves.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/css/styles.css') }}">

    {{-- Keep existing page tooling --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- ShuleXpert brand overrides on GXON --}}
    <style>
      :root {
        --bs-font-sans-serif: "Century Gothic", CenturyGothic, AppleGothic, "Segoe UI", sans-serif;
        --bs-body-font-family: "Century Gothic", CenturyGothic, AppleGothic, "Segoe UI", sans-serif;
        --bs-primary: #940000 !important;
        --bs-primary-rgb: 148, 0, 0 !important;
        --bs-primary-text-emphasis: #5c0000 !important;
        --bs-primary-bg-subtle: #fff5f5 !important;
        --bs-primary-border-subtle: #f0dada !important;
        --bs-link-color: #940000 !important;
        --bs-link-hover-color: #7a0000 !important;
      }
      html, body,
      .app-header,
      .app-menubar,
      .app-wrapper,
      .btn, .form-control, .dropdown-menu, .card, .menu-link {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, "Segoe UI", sans-serif !important;
      }
      html[data-bs-theme="dark"],
      html[data-sx-theme="dark"] {
        --bs-primary: #c43a3a !important;
        --bs-primary-rgb: 196, 58, 58 !important;
        --bs-primary-bg-subtle: #2a1216 !important;
        --bs-primary-border-subtle: #5c2028 !important;
      }

      .btn-primary {
        --bs-btn-color: #fff !important;
        --bs-btn-bg: #940000 !important;
        --bs-btn-border-color: #940000 !important;
        --bs-btn-hover-color: #fff !important;
        --bs-btn-hover-bg: #7a0000 !important;
        --bs-btn-hover-border-color: #7a0000 !important;
        --bs-btn-active-color: #fff !important;
        --bs-btn-active-bg: #7a0000 !important;
        --bs-btn-active-border-color: #7a0000 !important;
        --bs-btn-disabled-bg: #940000 !important;
        --bs-btn-disabled-border-color: #940000 !important;
      }
      .btn-primary,
      .bg-primary,
      .badge.bg-primary {
        background-color: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
      }
      .btn-primary:hover,
      .btn-primary:focus {
        background-color: #7a0000 !important;
        border-color: #7a0000 !important;
      }
      .text-primary { color: #940000 !important; }

      /* Sidebar: keep readable text on primary hover/active (do NOT force maroon on red bg) */
      .app-navbar .menubar > li.menu-item > .menu-link:hover,
      .app-navbar .menubar > li.menu-item > .menu-link.active,
      .app-navbar .menubar > li.menu-item > .menu-link.open,
      .app-navbar .menubar > li.menu-item > button.menu-link:hover {
        color: #fff !important;
        background-color: #940000 !important;
      }
      .app-navbar .menubar > li.menu-item > .menu-link:hover > i,
      .app-navbar .menubar > li.menu-item > .menu-link.active > i,
      .app-navbar .menubar > li.menu-item > .menu-link.open > i,
      .app-navbar .menubar > li.menu-item > .menu-link:hover .menu-label,
      .app-navbar .menubar > li.menu-item > .menu-link.active .menu-label,
      .app-navbar .menubar > li.menu-item > .menu-link.open .menu-label {
        color: #fff !important;
      }
      .menu-heading .menu-label { color: #940000 !important; opacity: .9; }

      .form-check-input:checked {
        background-color: #940000 !important;
        border-color: #940000 !important;
      }
      .page-item.active .page-link {
        background-color: #940000 !important;
        border-color: #940000 !important;
      }

      /* Logo section stays white + centered */
      .app-navbar-brand {
        background: #ffffff !important;
        border-bottom: 1px solid #eee !important;
        border-right-color: #eee !important;
        justify-content: center !important;
        align-items: center !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
        gap: 0 !important;
      }
      .sx-sa-brand-logo {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 100%;
        margin: 0 auto;
      }
      .sx-sa-brand-logo img {
        display: block;
        max-height: 46px;
        width: auto;
        max-width: 170px;
        object-fit: contain;
        margin: 0 auto;
      }
      [data-app-sidebar="mini"] .app-navbar-brand {
        padding-left: 8px !important;
        padding-right: 8px !important;
      }
      [data-app-sidebar="mini"] .sx-sa-brand-logo img {
        max-height: 32px;
        max-width: 48px;
      }

      /* Static sidebar identity (screenshot style) */
      .sx-sa-sidebar-identity {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px 16px;
        background: #ffffff;
        border-bottom: 1px solid #ececec;
        border-right: 1px solid #eee;
      }
      .sx-sa-id-avatar {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 4px;
        background: #940000;
        color: #fff !important;
        font-weight: 800;
        font-size: 1.05rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0;
      }
      .sx-sa-id-name {
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #111 !important;
        line-height: 1.2;
        margin: 0;
      }
      .sx-sa-id-role {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        line-height: 1.25;
        margin: 2px 0 0;
      }
      [data-app-sidebar="mini"] .sx-sa-sidebar-identity {
        justify-content: center;
        padding: 12px 8px;
      }
      [data-app-sidebar="mini"] .sx-sa-id-text {
        display: none;
      }
      /* Brand + identity take ~140px under header */
      .app-menubar .app-navbar {
        height: calc(100% - (var(--app-header-height) + 140px)) !important;
      }
      [data-app-sidebar="mini"] .app-menubar .app-navbar {
        height: calc(100% - (var(--app-header-height) + 120px)) !important;
      }

      /* Header = primary brand bar (Mauzo-style) */
      .app-header {
        --app-header-bg: #940000;
        background-color: #940000 !important;
        border-bottom: none !important;
        color: #fff;
      }
      .app-header .app-toggler span {
        background-color: #fff !important;
      }
      .app-header .sx-sa-header-date {
        display: inline-flex;
        align-items: center;
        color: #fff !important;
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.01em;
        white-space: nowrap;
      }
      .app-header .btn:not(.sx-lang-btn),
      .app-header .fi,
      .app-header .fw-bold,
      .app-header .text-dark,
      .app-header .text-body,
      .app-header a {
        color: #fff !important;
      }
      .app-header .vr {
        background-color: rgba(255, 255, 255, 0.35) !important;
        opacity: 1;
      }
      .app-header .btn-action-gray,
      .app-header .btn-icon {
        background: rgba(255, 255, 255, 0.12) !important;
        border-color: rgba(255, 255, 255, 0.22) !important;
        color: #fff !important;
      }
      .app-header .avatar.bg-primary {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
      }
      .app-header .dropdown-menu {
        color: var(--bs-body-color);
      }
      .app-header .dropdown-menu .fw-bold,
      .app-header .dropdown-menu .text-dark,
      .app-header .dropdown-menu .text-body,
      .app-header .dropdown-menu a,
      .app-header .dropdown-menu .fi {
        color: inherit !important;
      }
      .app-header .dropdown-menu .text-danger,
      .app-header .dropdown-menu .text-danger .fi {
        color: #dc3545 !important;
      }
      .app-header .dropdown-menu .avatar.bg-primary {
        background: #940000 !important;
      }

      .sx-sa-content-wrap {
        padding-bottom: 24px;
      }
      /* Content text: pure black in light mode */
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-wrapper,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-title,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head span,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap p,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap span,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap small,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .text-muted,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .text-body,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .card-title,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap h1,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap h2,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap h3,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap h4,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap h5,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap h6 {
        color: #000 !important;
      }
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap a:not(.btn) {
        color: #000 !important;
      }
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap a.btn-primary,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .text-white,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .badge,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .avatar {
        color: #fff !important;
      }
      .sx-sa-footer {
        margin-top: 24px;
        padding-top: 14px;
        border-top: 1px solid rgba(148, 0, 0, 0.12);
        font-size: 12px;
        color: #777;
        text-align: center;
      }
      html[data-sx-theme="dark"] .sx-sa-footer {
        border-top-color: rgba(255, 180, 180, 0.16);
        color: #c9b4b8;
      }

      /* Theme toggle on primary header — use Flaticon (visible) */
      .app-header .sx-theme-toggle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.45) !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        padding: 0;
        flex-shrink: 0;
      }
      .app-header .sx-theme-toggle:hover {
        background: rgba(255, 255, 255, 0.28) !important;
      }
      .app-header .sx-theme-toggle i {
        color: #fff !important;
        font-size: 1.15rem;
        line-height: 1;
      }
      .app-header .sx-theme-toggle .sx-theme-icon-sun { display: none !important; }
      .app-header .sx-theme-toggle.is-dark .sx-theme-icon-moon { display: none !important; }
      .app-header .sx-theme-toggle.is-dark .sx-theme-icon-sun { display: inline-flex !important; }
      .app-header .sx-theme-toggle.is-dark {
        background: rgba(0, 0, 0, 0.28) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        color: #fff !important;
      }

      /* EN / SW flag switch (landing-page style) */
      .app-header .sx-lang-switch {
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .app-header .sx-lang-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        background: rgba(255, 255, 255, 0.12);
        color: #fff !important;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
      }
      .app-header .sx-lang-btn:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #fff !important;
      }
      .app-header .sx-lang-btn.is-active {
        background: #fff !important;
        border-color: #fff !important;
        color: #940000 !important;
      }
      .app-header .sx-lang-flag {
        width: 18px;
        height: 12px;
        object-fit: cover;
        border-radius: 2px;
        display: block;
      }

      html[data-sx-theme="dark"] body,
      html[data-bs-theme="dark"] body {
        color-scheme: dark;
      }
    </style>

    @stack('styles')
    @include('includes.shulexpert_theme_css')
  </head>

  <body>
    @php
        $customerInquiryUnread = 0;
        if (session('user_type') === 'SuperAdmin' && \Illuminate\Support\Facades\Schema::hasTable('customer_requests')) {
            $customerInquiryUnread = \App\Models\CustomerRequest::where('is_read', false)->count();
        }
        $saLogo = asset('images/shuleXpert.jpg');
        $saLogoFallback = asset('shule.png');
    @endphp

    <div class="page-layout">
      {{-- Header --}}
      <header class="app-header">
        <div class="app-header-inner">
          <button class="app-toggler" type="button" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="app-header-start d-flex align-items-center">
            <div class="sx-sa-header-date">
              <span>{{ \Carbon\Carbon::now()->format('l, F j, Y · g:i A') }}</span>
            </div>
          </div>

          <div class="app-header-end">
            <div class="px-lg-3 px-2 ps-0 d-flex align-items-center gap-2">
              <div class="sx-lang-switch" role="group" aria-label="Language">
                <button type="button"
                        class="sx-lang-btn{{ $saLocale === 'en' ? ' is-active' : '' }}"
                        data-locale="en"
                        title="English"
                        aria-label="English"
                        aria-pressed="{{ $saLocale === 'en' ? 'true' : 'false' }}">
                  <img class="sx-lang-flag" src="https://flagcdn.com/w40/gb.png" width="18" height="12" alt="" loading="lazy" decoding="async">
                  <span>EN</span>
                </button>
                <button type="button"
                        class="sx-lang-btn{{ $saLocale === 'sw' ? ' is-active' : '' }}"
                        data-locale="sw"
                        title="Kiswahili"
                        aria-label="Kiswahili"
                        aria-pressed="{{ $saLocale === 'sw' ? 'true' : 'false' }}">
                  <img class="sx-lang-flag" src="https://flagcdn.com/w40/tz.png" width="18" height="12" alt="" loading="lazy" decoding="async">
                  <span>SW</span>
                </button>
              </div>

              <button type="button" class="sx-theme-toggle" aria-label="Switch to dark mode" title="Dark / Light mode">
                <i class="fi fi-rr-moon sx-theme-icon-moon" aria-hidden="true"></i>
                <i class="fi fi-rr-sun sx-theme-icon-sun" aria-hidden="true"></i>
              </button>
            </div>
            <div class="vr my-3"></div>

            <div class="d-flex align-items-center gap-sm-2 gap-0 px-lg-4 px-sm-2 px-1">
              <div class="dropdown text-end" id="ci-notifications-wrap">
                <button type="button" class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
                  <i class="fi fi-rr-bell"></i>
                  <span id="ci-notif-badge" class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger {{ $customerInquiryUnread > 0 ? '' : 'd-none' }}"
                        style="font-size:10px;min-width:18px;">{{ $customerInquiryUnread > 99 ? '99+' : $customerInquiryUnread }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 w-300px mt-2">
                  <div class="px-3 py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Customer Inquiries</h6>
                    <span class="badge badge-sm rounded-pill bg-primary" id="ci-notif-count-label">{{ $customerInquiryUnread }} unread</span>
                  </div>
                  <div class="p-0" style="max-height: 320px; overflow:auto;">
                    <div id="ci-notif-list" class="list-group list-group-flush">
                      <div class="list-group-item small text-muted text-center py-4">Loading...</div>
                    </div>
                  </div>
                  <div class="p-2">
                    <a href="{{ route('superadmin.customer_inquiries') }}" class="btn w-100 btn-primary waves-effect waves-light">View all inquiries</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="vr my-3"></div>

            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
              <a href="#" class="d-flex align-items-center py-2 text-decoration-none" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Account">
                <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">
                  <i class="fi fi-rr-user"></i>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                <li class="d-flex align-items-center p-2">
                  <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">E</div>
                  <div class="ms-2">
                    <div class="fw-bold text-dark">EMCA TECH LTD</div>
                    <small class="text-body d-block lh-sm">Super Administrator</small>
                  </div>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('superadmin.change_password') }}">
                    <i class="fi fi-rr-lock scale-1x"></i> Change Password
                  </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger w-100 border-0 bg-transparent">
                      <i class="fi fi-sr-exit scale-1x"></i> Log Out
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </header>

      {{-- Sidebar --}}
      <aside class="app-menubar" id="appMenubar">
        <div class="app-navbar-brand">
          <a class="navbar-brand-logo sx-sa-brand-logo" href="{{ route('superAdminDashboard') }}" title="ShuleXpert">
            <img src="{{ $saLogo }}" alt="ShuleXpert" onerror="this.onerror=null;this.src='{{ $saLogoFallback }}';">
          </a>
        </div>

        <div class="sx-sa-sidebar-identity" aria-label="Signed in as Super Administrator">
          <div class="sx-sa-id-avatar" aria-hidden="true">E</div>
          <div class="sx-sa-id-text">
            <p class="sx-sa-id-name">EMCA TECH LTD</p>
            <p class="sx-sa-id-role">SUPER ADMINISTRATOR</p>
          </div>
        </div>

        <nav class="app-navbar" data-simplebar>
          <ul class="menubar" id="saMenubar">
            <li class="menu-heading"><span class="menu-label">Main Navigation</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superAdminDashboard') ? 'active' : '' }}" href="{{ route('superAdminDashboard') }}">
                <i class="fi fi-rr-apps"></i>
                <span class="menu-label">Dashboard</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">School Management</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.schools.register') ? 'active' : '' }}" href="{{ route('superadmin.schools.register') }}">
                <i class="fi fi-rr-add"></i>
                <span class="menu-label">Register School</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.schools.registration_requests*') ? 'active' : '' }}" href="{{ route('superadmin.schools.registration_requests') }}">
                <i class="fi fi-rr-file"></i>
                <span class="menu-label">School Requests</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.schools.index') ? 'active' : '' }}" href="{{ route('superadmin.schools.index') }}">
                <i class="fi fi-rr-building"></i>
                <span class="menu-label">Registered Schools</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.users') ? 'active' : '' }}" href="{{ route('superadmin.users') }}">
                <i class="fi fi-rr-users"></i>
                <span class="menu-label">User Management</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">Banking / Fees</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.banks.*') ? 'active' : '' }}" href="{{ route('superadmin.banks.index') }}">
                <i class="fi fi-rr-bank"></i>
                <span class="menu-label">Bank Registration</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.bank_accounts.*') ? 'active' : '' }}" href="{{ route('superadmin.bank_accounts.verification') }}">
                <i class="fi fi-rr-shield-check"></i>
                <span class="menu-label">Account Verification</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.nbc_integration_docs*') ? 'active' : '' }}" href="{{ route('superadmin.nbc_integration_docs') }}">
                <i class="fi fi-rr-document"></i>
                <span class="menu-label">NBC Integration Docs</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">System Tools</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.user_password') ? 'active' : '' }}" href="{{ route('superadmin.user_password') }}">
                <i class="fi fi-rr-key"></i>
                <span class="menu-label">User Password</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.customer_care') ? 'active' : '' }}" href="{{ route('superadmin.customer_care') }}">
                <i class="fi fi-rr-headset"></i>
                <span class="menu-label">Customer Care</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.customer_inquiries*') ? 'active' : '' }}" href="{{ route('superadmin.customer_inquiries') }}">
                <i class="fi fi-rr-envelope"></i>
                <span class="menu-label">Customer Inquiries</span>
                @if(($customerInquiryUnread ?? 0) > 0)
                  <span class="badge bg-danger ms-auto">{{ $customerInquiryUnread > 99 ? '99+' : $customerInquiryUnread }}</span>
                @endif
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.ai_training.*') ? 'active' : '' }}" href="{{ route('superadmin.ai_training.index') }}">
                <i class="fi fi-rr-robot"></i>
                <span class="menu-label">AI Training</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.attendance_sms_schedule') ? 'active' : '' }}" href="{{ route('superadmin.attendance_sms_schedule') }}">
                <i class="fi fi-rr-calendar"></i>
                <span class="menu-label">Attendance SMS Schedule</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.fingerprint_attendance_setup.*') ? 'active' : '' }}" href="{{ route('superadmin.fingerprint_attendance_setup.index') }}">
                <i class="fi fi-rr-fingerprint"></i>
                <span class="menu-label">Attendance Setup</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.system_alerts') ? 'active' : '' }}" href="{{ route('superadmin.system_alerts') }}">
                <i class="fi fi-rr-bell"></i>
                <span class="menu-label">System Alerts</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.system_logs') ? 'active' : '' }}" href="{{ route('superadmin.system_logs') }}">
                <i class="fi fi-rr-shield"></i>
                <span class="menu-label">System Logs</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">Security</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.change_password') ? 'active' : '' }}" href="{{ route('superadmin.change_password') }}">
                <i class="fi fi-rr-lock"></i>
                <span class="menu-label">Change Password</span>
              </a>
            </li>
            <li class="menu-item">
              <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start">
                  <i class="fi fi-sr-exit"></i>
                  <span class="menu-label">Logout</span>
                </button>
              </form>
            </li>
          </ul>
        </nav>
      </aside>

      {{-- Main content --}}
      <main class="app-wrapper">
        <div class="container sx-sa-content-wrap">
          @hasSection('page_title')
            <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
              <div class="clearfix">
                <h1 class="app-page-title">@yield('page_title')</h1>
                @hasSection('page_subtitle')
                  <span>@yield('page_subtitle')</span>
                @endif
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2">
                @hasSection('page_actions')
                  @yield('page_actions')
                @else
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                      <li class="breadcrumb-item"><a href="{{ route('superAdminDashboard') }}">Home</a></li>
                      <li class="breadcrumb-item active" aria-current="page">@yield('page_breadcrumb', 'Dashboard')</li>
                    </ol>
                  </nav>
                @endif
              </div>
            </div>
          @endif

          @yield('content')

          <footer class="sx-sa-footer">
            &copy; {{ date('Y') }} ShuleXpert | Powered by EmCa Techonology
          </footer>
        </div>
      </main>
    </div>

    {{-- GXON shell scripts only (no demo dashboard/theme/search/DataTables auto-init) --}}
    <script src="{{ asset('gxon/libs/global/global.min.js') }}"></script>
    <script src="{{ asset('gxon/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('gxon/libs/node-waves/waves.min.js') }}"></script>
    <script>
      (function () {
        document.addEventListener('DOMContentLoaded', function () {
          if (typeof Waves !== 'undefined' && Waves.init) {
            Waves.init();
          }

          var togglers = document.querySelectorAll('.app-toggler');
          var menubar = document.getElementById('appMenubar');
          if (!menubar || !togglers.length) return;

          togglers.forEach(function (toggler) {
            toggler.addEventListener('click', function () {
              toggler.classList.toggle('active');
              if (window.innerWidth >= 1191) {
                var current = document.documentElement.getAttribute('data-app-sidebar');
                document.documentElement.setAttribute(
                  'data-app-sidebar',
                  current === 'full' ? 'mini' : 'full'
                );
              } else {
                menubar.classList.toggle('open');
              }
            });
          });

          menubar.addEventListener('mouseenter', function () {
            if (document.documentElement.getAttribute('data-app-sidebar') === 'mini') {
              document.documentElement.setAttribute('data-app-sidebar', 'mini-hover');
            }
          });
          menubar.addEventListener('mouseleave', function () {
            if (document.documentElement.getAttribute('data-app-sidebar') === 'mini-hover') {
              document.documentElement.setAttribute('data-app-sidebar', 'mini');
            }
          });
        });
      })();
    </script>

    {{-- Keep DataTables CDN for existing pages --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    @stack('scripts')

    <script>
      (function () {
        if (!window.__sxThemeToggleBound) {
          window.__sxThemeToggleBound = true;
          function applyToggleState(theme) {
            var isDark = theme === 'dark';
            document.querySelectorAll('.sx-theme-toggle').forEach(function (btn) {
              btn.classList.toggle('is-dark', isDark);
              btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
              btn.setAttribute('title', isDark ? 'Light mode' : 'Dark mode');
            });
          }
          function setTheme(theme) {
            theme = theme === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-sx-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);
            try { localStorage.setItem('sx-theme', theme); } catch (e) {}
            applyToggleState(theme);
          }
          function toggleTheme() {
            var current = document.documentElement.getAttribute('data-sx-theme') || 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
          }
          document.addEventListener('click', function (e) {
            var btn = e.target.closest('.sx-theme-toggle');
            if (!btn) return;
            e.preventDefault();
            toggleTheme();
          });
          var theme = 'light';
          try {
            var saved = localStorage.getItem('sx-theme');
            if (saved === 'dark' || saved === 'light') theme = saved;
          } catch (e) {}
          setTheme(theme);
        }

        if (!window.__sxSaLangBound) {
          window.__sxSaLangBound = true;
          var csrf = document.querySelector('meta[name="csrf-token"]');
          var token = csrf ? csrf.getAttribute('content') : '';
          function setLocale(locale) {
            fetch(@json(route('change_language')), {
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
        }
      })();
    </script>

    <script>
      $(document).ready(function () {
        // Idle Timer Logic (unchanged workflow)
        const IDLE_MS = 270 * 1000;
        const WARN_SECONDS = 30;
        const LOGOUT_URL = @json(route('logout'));
        const CSRF_TOKEN = @json(csrf_token());

        let idleTimer = null;
        let countdownTimer = null;
        let remaining = WARN_SECONDS;
        let overlay = null;

        function ensureOverlay() {
          if (overlay) return overlay;
          overlay = document.createElement('div');
          overlay.id = 'idle-logout-overlay';
          overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:99999;';
          overlay.innerHTML =
            '<div style="width:min(520px,92vw);background:#fff;border-radius:12px;padding:18px 18px 14px;box-shadow:0 20px 60px rgba(0,0,0,.25);font-family:inherit;">'
            + '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">'
            + '<div style="font-weight:800;color:#940000;">Security Warning</div>'
            + '<button type="button" id="idleStayBtn" style="border:1px solid rgba(148,0,0,.3);background:#fff;color:#940000;border-radius:8px;padding:6px 10px;cursor:pointer;">Stay Logged In</button>'
            + '</div>'
            + '<div style="margin-top:10px;color:#333;line-height:1.4;">System is idle. You will be logged out after <b id="idleCountdown">30</b> seconds.</div>'
            + '<div style="margin-top:10px;color:#666;font-size:.9rem;">Move the mouse, type, or click to continue.</div>'
            + '</div>';
          document.body.appendChild(overlay);
          const stayBtn = overlay.querySelector('#idleStayBtn');
          if (stayBtn) stayBtn.addEventListener('click', resetAll);
          return overlay;
        }

        function showWarning() {
          ensureOverlay();
          remaining = WARN_SECONDS;
          overlay.style.display = 'flex';
          const c = overlay.querySelector('#idleCountdown');
          if (c) c.textContent = String(remaining);
          if (countdownTimer) clearInterval(countdownTimer);
          countdownTimer = setInterval(function () {
            remaining -= 1;
            if (c) c.textContent = String(Math.max(0, remaining));
            if (remaining <= 0) logoutNow();
          }, 1000);
        }

        function hideWarning() {
          if (overlay) overlay.style.display = 'none';
          if (countdownTimer) clearInterval(countdownTimer);
          countdownTimer = null;
        }

        function logoutNow() {
          hideWarning();
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = LOGOUT_URL;
          form.style.display = 'none';
          const token = document.createElement('input');
          token.type = 'hidden';
          token.name = '_token';
          token.value = CSRF_TOKEN;
          form.appendChild(token);
          document.body.appendChild(form);
          form.submit();
        }

        function scheduleIdle() {
          if (idleTimer) clearTimeout(idleTimer);
          idleTimer = setTimeout(showWarning, IDLE_MS);
        }

        function resetAll() {
          hideWarning();
          scheduleIdle();
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
          window.addEventListener(evt, resetAll, { passive: true });
        });
        scheduleIdle();
      });

      window.refreshCustomerInquiryNotifications = function () {
        var $wrap = $('#ci-notifications-wrap');
        if (!$wrap.length) return;

        $.get(@json(route('superadmin.customer_inquiries.notifications')), function (res) {
          if (!res.success) return;

          var unread = res.unread || 0;
          var $badge = $('#ci-notif-badge');
          if (unread > 0) {
            $badge.removeClass('d-none').text(unread > 99 ? '99+' : unread);
          } else {
            $badge.addClass('d-none');
          }
          $('#ci-notif-count-label').text(unread + ' unread');

          var $list = $('#ci-notif-list').empty();
          if (!res.items || !res.items.length) {
            $list.html('<div class="list-group-item small text-muted text-center py-4">No unread inquiries</div>');
            return;
          }

          res.items.forEach(function (item) {
            var safeName = $('<div>').text(item.name || '').html();
            var safeSubject = $('<div>').text(item.subject || '').html();
            var safePreview = $('<div>').text(item.message_preview || '').html();
            $list.append(
              '<button type="button" class="list-group-item list-group-item-action text-start ci-notif-item" data-id="' + item.id + '">' +
                '<div class="d-flex justify-content-between gap-2"><strong class="small">' + safeName + '</strong>' +
                '<span class="text-muted smaller">' + (item.created_human || '') + '</span></div>' +
                '<div class="small fw-semibold mt-1" style="color:#940000;">' + safeSubject + '</div>' +
                '<div class="small text-muted text-truncate">' + safePreview + '</div>' +
              '</button>'
            );
          });
        });
      };

      $(document).on('click', '.ci-notif-item', function () {
        var id = $(this).data('id');
        if (!id) return;
        if (typeof window.openCustomerInquiry === 'function') {
          window.openCustomerInquiry(id);
        } else {
          window.location.href = @json(route('superadmin.customer_inquiries')) + '?open=' + id;
        }
      });

      $('#ci-notifications-wrap').on('show.bs.dropdown', function () {
        window.refreshCustomerInquiryNotifications();
      });

      if ($('#ci-notifications-wrap').length) {
        window.refreshCustomerInquiryNotifications();
        setInterval(window.refreshCustomerInquiryNotifications, 60000);
      }
    </script>
  </body>
</html>
