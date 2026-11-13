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
    <title>@yield('title', __('superadmin.portal_title'))</title>
    <meta name="description" content="{{ __('superadmin.portal_desc') }}" />
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

      /* =========================================================
         SuperAdmin redesigned pages — shared dark mode
         Covers sx-* shells that hardcode light-mode blacks.
         ========================================================= */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sch,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sch,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-req,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-req,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-reg,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-reg,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa-dash,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa-dash {
        color: #f3e9eb !important;
      }

      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci span:not(.sx-badge):not(.badge):not(.sx-badge-unread),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci span:not(.sx-badge):not(.badge):not(.sx-badge-unread),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci .text-body,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci .text-body,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-ci a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-ci a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc span:not(.sx-badge):not(.badge):not(.status-marker),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc span:not(.sx-badge):not(.badge):not(.status-marker),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cc a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cc a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp span:not(.sx-badge):not(.is-ok):not(.is-bad):not(.sx-strength-text),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp span:not(.sx-badge):not(.is-ok):not(.is-bad):not(.sx-strength-text),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-cp a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-cp a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sl a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sl a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-fp a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-fp a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-asm a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-asm a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-sa a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-sa a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-upw a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-upw a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-banks a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-banks a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-bav a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-bav a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc span:not(.sx-badge):not(.badge):not(.sx-chip),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc span:not(.sx-badge):not(.badge):not(.sx-chip),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-nbc a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-nbc a:not(.btn),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users p,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users p,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users span:not(.sx-badge):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users span:not(.sx-badge):not(.badge),
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users strong,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users strong,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users label,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users h1,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users h2,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users h3,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users h4,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users h5,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users h6,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users h1,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users h2,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users h3,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users h4,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users h5,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users h6,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-users a:not(.btn),
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-users a:not(.btn) {
        color: #f3e9eb !important;
      }

      /* Cards / panels */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .card,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .card,
      html[data-sx-theme="dark"] .sx-sa-content-wrap [class*="-card"]:not(.stat-card),
      html[data-bs-theme="dark"] .sx-sa-content-wrap [class*="-card"]:not(.stat-card),
      html[data-sx-theme="dark"] .sx-sa-content-wrap [class$="-stat"],
      html[data-bs-theme="dark"] .sx-sa-content-wrap [class$="-stat"],
      html[data-sx-theme="dark"] .sx-sa-content-wrap .ci-grid-card,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .ci-grid-card,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-auto-panel,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-auto-panel,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-req-box,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-req-box,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-table-wrap,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap [class*="-card"],
      html[data-bs-theme="dark"] .sx-sa-content-wrap [class*="-card"],
      html[data-sx-theme="dark"] .sx-sa-content-wrap [class$="-stat"],
      html[data-bs-theme="dark"] .sx-sa-content-wrap [class$="-stat"],
      html[data-sx-theme="dark"] .sx-sa-content-wrap .ci-grid-card,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .ci-grid-card {
        border-top-color: #940000 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap [class*="-card-head"],
      html[data-bs-theme="dark"] .sx-sa-content-wrap [class*="-card-head"] {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
      }

      /* Forms */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .form-control,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .form-control,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .kbd-textarea,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .kbd-textarea,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .input-group-text,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .input-group-text {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      /* Keep Bootstrap select chevron (do not use background shorthand) */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .form-select,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .form-select {
        background-color: #1c1417 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23d7c4c8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .form-control:focus,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .form-control:focus,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .form-select:focus,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .form-select:focus,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .kbd-textarea:focus,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .kbd-textarea:focus {
        border-color: #c43a3a !important;
        box-shadow: 0 0 0 0.2rem rgba(196, 58, 58, 0.22) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .form-label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .form-label {
        color: #d7c4c8 !important;
      }

      /* Tables */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .table,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .table {
        color: #f3e9eb !important;
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-border-color: rgba(255, 180, 180, 0.12);
        --bs-table-hover-bg: rgba(255, 180, 180, 0.06);
        --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .table thead th,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .table thead th {
        background: #1c1417 !important;
        color: #d7c4c8 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .table tbody td,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .table tbody tr:hover,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .table tbody tr:hover,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .unread-row,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .unread-row {
        background: rgba(255, 180, 180, 0.06) !important;
      }

      /* Tabs / toggles / soft panels */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .nav-tabs,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .nav-tabs {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .nav-tabs .nav-link,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .nav-tabs .nav-link {
        color: #c9b4b8 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .nav-tabs .nav-link.active,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .nav-tabs .nav-link.active {
        color: #ffb4b4 !important;
        background: rgba(148, 0, 0, 0.18) !important;
        border-bottom-color: #940000 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-view-btn,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-view-btn,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-view-toggle,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-view-toggle {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-view-btn.is-active,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-view-btn.is-active {
        background: #940000 !important;
        color: #fff !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .bg-light,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .bg-light {
        background: #1c1417 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-strength-bar,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-strength-bar {
        background: #3a2a2e !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-strength-label,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-strength-label {
        color: #c9b4b8 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-setup-icon,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-setup-icon,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-empty-ico,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-empty-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
      }

      /* Select2 + DataTables */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .select2-container--default .select2-selection--single,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .select2-container--default .select2-selection--single {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .select2-container--default .select2-selection--single .select2-selection__rendered,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .select2-dropdown,
      html[data-bs-theme="dark"] .select2-dropdown {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .select2-container--default .select2-results__option,
      html[data-bs-theme="dark"] .select2-container--default .select2-results__option {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable,
      html[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background: #940000 !important;
        color: #fff !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_length select,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_length select,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_filter input,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_filter input {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_info,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_info,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_length,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_length,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_filter,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_paginate .paginate_button,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_paginate .paginate_button.current,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
      }

      /* Keep white on primary buttons / white badges */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-primary,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-primary,
      html[data-sx-theme="dark"] .sx-sa-content-wrap a.btn-primary,
      html[data-bs-theme="dark"] .sx-sa-content-wrap a.btn-primary,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .text-white,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .text-white,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-active,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-active,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-on,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-on,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-auto,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-auto,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-blocked,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-blocked,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-all,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-all,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge-unread,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge-unread,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-avatar,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-avatar {
        color: #fff !important;
      }

      /* Page title area */
      html[data-sx-theme="dark"] .app-page-title,
      html[data-bs-theme="dark"] .app-page-title,
      html[data-sx-theme="dark"] .app-page-head,
      html[data-bs-theme="dark"] .app-page-head,
      html[data-sx-theme="dark"] .app-page-head span,
      html[data-bs-theme="dark"] .app-page-head span {
        color: #f3e9eb !important;
      }

      /* Modals (often portaled outside page root) */
      html[data-sx-theme="dark"] .modal-content,
      html[data-bs-theme="dark"] .modal-content {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .modal-header,
      html[data-bs-theme="dark"] .modal-header,
      html[data-sx-theme="dark"] .modal-footer,
      html[data-bs-theme="dark"] .modal-footer {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.14) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .modal-title,
      html[data-bs-theme="dark"] .modal-title {
        color: #ffb4b4 !important;
      }
      html[data-sx-theme="dark"] .modal-body,
      html[data-bs-theme="dark"] .modal-body {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .modal .form-control,
      html[data-bs-theme="dark"] .modal .form-control {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .modal .form-select,
      html[data-bs-theme="dark"] .modal .form-select {
        background-color: #1c1417 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23d7c4c8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .modal .form-label,
      html[data-bs-theme="dark"] .modal .form-label,
      html[data-sx-theme="dark"] .modal .text-muted,
      html[data-bs-theme="dark"] .modal .text-muted,
      html[data-sx-theme="dark"] .modal .bg-light,
      html[data-bs-theme="dark"] .modal .bg-light {
        color: #d7c4c8 !important;
      }
      html[data-sx-theme="dark"] .modal .bg-light,
      html[data-bs-theme="dark"] .modal .bg-light {
        background: #1c1417 !important;
      }
      html[data-sx-theme="dark"] .modal .btn-close,
      html[data-bs-theme="dark"] .modal .btn-close {
        filter: invert(1) grayscale(1);
      }
      html[data-sx-theme="dark"] #inquiryModal .ci-modal-hero .ci-modal-subject,
      html[data-bs-theme="dark"] #inquiryModal .ci-modal-hero .ci-modal-subject,
      html[data-sx-theme="dark"] #inquiryModal .ci-modal-hero .ci-modal-kicker,
      html[data-bs-theme="dark"] #inquiryModal .ci-modal-hero .ci-modal-kicker,
      html[data-sx-theme="dark"] #logDetailsModal .modal-header .modal-title,
      html[data-bs-theme="dark"] #logDetailsModal .modal-header .modal-title,
      html[data-sx-theme="dark"] #visitorDetailsModal .modal-header .modal-title,
      html[data-bs-theme="dark"] #visitorDetailsModal .modal-header .modal-title {
        color: #fff !important;
      }
      html[data-sx-theme="dark"] #inquiryModal .modal-body,
      html[data-bs-theme="dark"] #inquiryModal .modal-body {
        background: #1a1215 !important;
      }
      html[data-sx-theme="dark"] #inquiryModal .ci-profile,
      html[data-bs-theme="dark"] #inquiryModal .ci-profile,
      html[data-sx-theme="dark"] #inquiryModal .ci-meta-item,
      html[data-bs-theme="dark"] #inquiryModal .ci-meta-item,
      html[data-sx-theme="dark"] #inquiryModal .ci-message-card,
      html[data-bs-theme="dark"] #inquiryModal .ci-message-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
      }
      html[data-sx-theme="dark"] #inquiryModal .ci-message-head,
      html[data-bs-theme="dark"] #inquiryModal .ci-message-head {
        background: rgba(148, 0, 0, 0.22) !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
        color: #ffb4b4 !important;
      }
      html[data-sx-theme="dark"] #inquiryModal .sx-message-box,
      html[data-bs-theme="dark"] #inquiryModal .sx-message-box {
        background: #24181c !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] #inquiryModal .ci-profile-name,
      html[data-bs-theme="dark"] #inquiryModal .ci-profile-name,
      html[data-sx-theme="dark"] #inquiryModal .ci-meta-value,
      html[data-bs-theme="dark"] #inquiryModal .ci-meta-value {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-outline-secondary,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-outline-secondary,
      html[data-sx-theme="dark"] .modal .btn-outline-secondary,
      html[data-bs-theme="dark"] .modal .btn-outline-secondary {
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        background: transparent !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-outline-secondary:hover,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-outline-secondary:hover,
      html[data-sx-theme="dark"] .modal .btn-outline-secondary:hover,
      html[data-bs-theme="dark"] .modal .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.1) !important;
        color: #fff !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-outline-primary,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-outline-primary,
      html[data-sx-theme="dark"] .modal .btn-outline-primary,
      html[data-bs-theme="dark"] .modal .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-outline-primary:hover,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-outline-primary:hover,
      html[data-sx-theme="dark"] .modal .btn-outline-primary:hover,
      html[data-bs-theme="dark"] .modal .btn-outline-primary:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .requirement-item.is-ok,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .requirement-item.is-ok,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-confirm-ok,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-confirm-ok {
        color: #69db7c !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .requirement-item.is-bad,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .requirement-item.is-bad,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-confirm-bad,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-confirm-bad {
        color: #ffa8a8 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-off,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-off,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-inactive,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-inactive,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-pages,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-pages {
        background: #3a2a2e !important;
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-manual,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-manual,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-badge.is-role,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-badge.is-role {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
      }

      /* Catch Bootstrap utility text that pages force to black */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .fw-semibold,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .fw-semibold,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .fw-bold,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .fw-bold,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .fw-medium,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .fw-medium,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-link,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-link,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .list-row,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .list-row,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .quick-tool-link,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .quick-tool-link,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .card-title,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .card-title,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .card-header,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .card-header {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap small.text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap small.text-muted {
        color: #d7c4c8 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .btn-link,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .btn-link {
        color: #ffb4b4 !important;
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
          <button class="app-toggler" type="button" aria-label="{{ __('superadmin.toggle_sidebar') }}">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="app-header-start d-flex align-items-center">
            <div class="sx-sa-header-date">
              <span>{{ \Carbon\Carbon::now()->translatedFormat($saLocale === 'sw' ? 'l, j F Y · H:i' : 'l, F j, Y · g:i A') }}</span>
            </div>
          </div>

          <div class="app-header-end">
            <div class="px-lg-3 px-2 ps-0 d-flex align-items-center gap-2">
              <div class="sx-lang-switch" role="group" aria-label="{{ __('superadmin.language') }}">
                <button type="button"
                        class="sx-lang-btn{{ $saLocale === 'en' ? ' is-active' : '' }}"
                        data-locale="en"
                        title="{{ __('superadmin.english') }}"
                        aria-label="{{ __('superadmin.english') }}"
                        aria-pressed="{{ $saLocale === 'en' ? 'true' : 'false' }}">
                  <img class="sx-lang-flag" src="https://flagcdn.com/w40/gb.png" width="18" height="12" alt="" loading="lazy" decoding="async">
                  <span>EN</span>
                </button>
                <button type="button"
                        class="sx-lang-btn{{ $saLocale === 'sw' ? ' is-active' : '' }}"
                        data-locale="sw"
                        title="{{ __('superadmin.kiswahili') }}"
                        aria-label="{{ __('superadmin.kiswahili') }}"
                        aria-pressed="{{ $saLocale === 'sw' ? 'true' : 'false' }}">
                  <img class="sx-lang-flag" src="https://flagcdn.com/w40/tz.png" width="18" height="12" alt="" loading="lazy" decoding="async">
                  <span>SW</span>
                </button>
              </div>

              <button type="button" class="sx-theme-toggle" aria-label="{{ __('superadmin.theme_dark') }}" title="{{ __('superadmin.theme_toggle') }}">
                <i class="fi fi-rr-moon sx-theme-icon-moon" aria-hidden="true"></i>
                <i class="fi fi-rr-sun sx-theme-icon-sun" aria-hidden="true"></i>
              </button>
            </div>
            <div class="vr my-3"></div>

            <div class="d-flex align-items-center gap-sm-2 gap-0 px-lg-4 px-sm-2 px-1">
              <div class="dropdown text-end" id="ci-notifications-wrap">
                <button type="button" class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="{{ __('superadmin.notifications') }}">
                  <i class="fi fi-rr-bell"></i>
                  <span id="ci-notif-badge" class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger {{ $customerInquiryUnread > 0 ? '' : 'd-none' }}"
                        style="font-size:10px;min-width:18px;">{{ $customerInquiryUnread > 99 ? '99+' : $customerInquiryUnread }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 w-300px mt-2">
                  <div class="px-3 py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('superadmin.nav.customer_inquiries') }}</h6>
                    <span class="badge badge-sm rounded-pill bg-primary" id="ci-notif-count-label">{{ $customerInquiryUnread }} {{ __('superadmin.unread') }}</span>
                  </div>
                  <div class="p-0" style="max-height: 320px; overflow:auto;">
                    <div id="ci-notif-list" class="list-group list-group-flush">
                      <div class="list-group-item small text-muted text-center py-4">{{ __('superadmin.loading') }}</div>
                    </div>
                  </div>
                  <div class="p-2">
                    <a href="{{ route('superadmin.customer_inquiries') }}" class="btn w-100 btn-primary waves-effect waves-light">{{ __('superadmin.view_all_inquiries') }}</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="vr my-3"></div>

            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
              <a href="#" class="d-flex align-items-center py-2 text-decoration-none" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="{{ __('superadmin.account') }}">
                <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">
                  <i class="fi fi-rr-user"></i>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                <li class="d-flex align-items-center p-2">
                  <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold">E</div>
                  <div class="ms-2">
                    <div class="fw-bold text-dark">{{ __('superadmin.company') }}</div>
                    <small class="text-body d-block lh-sm">{{ __('superadmin.role') }}</small>
                  </div>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('superadmin.change_password') }}">
                    <i class="fi fi-rr-lock scale-1x"></i> {{ __('superadmin.nav.change_password') }}
                  </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger w-100 border-0 bg-transparent">
                      <i class="fi fi-sr-exit scale-1x"></i> {{ __('superadmin.log_out') }}
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

        <div class="sx-sa-sidebar-identity" aria-label="{{ __('superadmin.signed_in_as') }}">
          <div class="sx-sa-id-avatar" aria-hidden="true">E</div>
          <div class="sx-sa-id-text">
            <p class="sx-sa-id-name">{{ __('superadmin.company') }}</p>
            <p class="sx-sa-id-role">{{ __('superadmin.role') }}</p>
          </div>
        </div>

        <nav class="app-navbar" data-simplebar>
          <ul class="menubar" id="saMenubar">
            <li class="menu-heading"><span class="menu-label">{{ __('superadmin.nav.main') }}</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superAdminDashboard') ? 'active' : '' }}" href="{{ route('superAdminDashboard') }}">
                <i class="fi fi-rr-apps"></i>
                <span class="menu-label">{{ __('superadmin.nav.dashboard') }}</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">{{ __('superadmin.nav.school_mgmt') }}</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.schools.register') ? 'active' : '' }}" href="{{ route('superadmin.schools.register') }}">
                <i class="fi fi-rr-add"></i>
                <span class="menu-label">{{ __('superadmin.nav.register_school') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.schools.registration_requests*') ? 'active' : '' }}" href="{{ route('superadmin.schools.registration_requests') }}">
                <i class="fi fi-rr-file"></i>
                <span class="menu-label">{{ __('superadmin.nav.school_requests') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.schools.index') ? 'active' : '' }}" href="{{ route('superadmin.schools.index') }}">
                <i class="fi fi-rr-building"></i>
                <span class="menu-label">{{ __('superadmin.nav.registered_schools') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.users') ? 'active' : '' }}" href="{{ route('superadmin.users') }}">
                <i class="fi fi-rr-users"></i>
                <span class="menu-label">{{ __('superadmin.nav.user_management') }}</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">{{ __('superadmin.nav.banking') }}</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.banks.*') ? 'active' : '' }}" href="{{ route('superadmin.banks.index') }}">
                <i class="fi fi-rr-bank"></i>
                <span class="menu-label">{{ __('superadmin.nav.bank_registration') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.bank_accounts.*') ? 'active' : '' }}" href="{{ route('superadmin.bank_accounts.verification') }}">
                <i class="fi fi-rr-shield-check"></i>
                <span class="menu-label">{{ __('superadmin.nav.account_verification') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.nbc_integration_docs*') ? 'active' : '' }}" href="{{ route('superadmin.nbc_integration_docs') }}">
                <i class="fi fi-rr-document"></i>
                <span class="menu-label">{{ __('superadmin.nav.nbc_docs') }}</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">{{ __('superadmin.nav.system_tools') }}</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.user_password') ? 'active' : '' }}" href="{{ route('superadmin.user_password') }}">
                <i class="fi fi-rr-key"></i>
                <span class="menu-label">{{ __('superadmin.nav.user_password') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.customer_care') ? 'active' : '' }}" href="{{ route('superadmin.customer_care') }}">
                <i class="fi fi-rr-headset"></i>
                <span class="menu-label">{{ __('superadmin.nav.customer_care') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.customer_inquiries*') ? 'active' : '' }}" href="{{ route('superadmin.customer_inquiries') }}">
                <i class="fi fi-rr-envelope"></i>
                <span class="menu-label">{{ __('superadmin.nav.customer_inquiries') }}</span>
                @if(($customerInquiryUnread ?? 0) > 0)
                  <span class="badge bg-danger ms-auto">{{ $customerInquiryUnread > 99 ? '99+' : $customerInquiryUnread }}</span>
                @endif
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.ai_training.*') ? 'active' : '' }}" href="{{ route('superadmin.ai_training.index') }}">
                <i class="fi fi-rr-robot"></i>
                <span class="menu-label">{{ __('superadmin.nav.ai_training') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.attendance_sms_schedule') ? 'active' : '' }}" href="{{ route('superadmin.attendance_sms_schedule') }}">
                <i class="fi fi-rr-calendar"></i>
                <span class="menu-label">{{ __('superadmin.nav.attendance_sms') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.fingerprint_attendance_setup.*') ? 'active' : '' }}" href="{{ route('superadmin.fingerprint_attendance_setup.index') }}">
                <i class="fi fi-rr-fingerprint"></i>
                <span class="menu-label">{{ __('superadmin.nav.attendance_setup') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.system_alerts') ? 'active' : '' }}" href="{{ route('superadmin.system_alerts') }}">
                <i class="fi fi-rr-bell"></i>
                <span class="menu-label">{{ __('superadmin.nav.system_alerts') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.system_logs') ? 'active' : '' }}" href="{{ route('superadmin.system_logs') }}">
                <i class="fi fi-rr-shield"></i>
                <span class="menu-label">{{ __('superadmin.nav.system_logs') }}</span>
              </a>
            </li>

            <li class="menu-heading"><span class="menu-label">{{ __('superadmin.nav.security') }}</span></li>
            <li class="menu-item">
              <a class="menu-link {{ request()->routeIs('superadmin.change_password') ? 'active' : '' }}" href="{{ route('superadmin.change_password') }}">
                <i class="fi fi-rr-lock"></i>
                <span class="menu-label">{{ __('superadmin.nav.change_password') }}</span>
              </a>
            </li>
            <li class="menu-item">
              <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start">
                  <i class="fi fi-sr-exit"></i>
                  <span class="menu-label">{{ __('superadmin.nav.logout') }}</span>
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
                      <li class="breadcrumb-item"><a href="{{ route('superAdminDashboard') }}">{{ __('superadmin.home') }}</a></li>
                      <li class="breadcrumb-item active" aria-current="page">@yield('page_breadcrumb', __('superadmin.nav.dashboard'))</li>
                    </ol>
                  </nav>
                @endif
              </div>
            </div>
          @endif

          @yield('content')

          <footer class="sx-sa-footer">
            &copy; {{ date('Y') }} {{ __('superadmin.footer') }}
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
          var langFailed = @json(__('superadmin.lang_failed'));
          var langRetry = @json(__('superadmin.lang_retry'));
          function setLocale(locale, btn) {
            var allBtns = document.querySelectorAll('.sx-lang-btn');
            allBtns.forEach(function (b) { b.disabled = true; });
            if (btn) btn.classList.add('is-loading');
            fetch(@json(route('change_language')), {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({ locale: locale })
            })
            .then(function (r) {
              if (!r.ok) throw new Error('HTTP ' + r.status);
              return r.json();
            })
            .then(function (data) {
              if (data && data.success) {
                try { localStorage.setItem('sx-locale', locale); } catch (e) {}
                window.location.reload();
                return;
              }
              allBtns.forEach(function (b) { b.disabled = false; });
              if (btn) btn.classList.remove('is-loading');
              alert((data && data.message) ? data.message : langFailed);
            })
            .catch(function () {
              allBtns.forEach(function (b) { b.disabled = false; });
              if (btn) btn.classList.remove('is-loading');
              alert(langRetry);
            });
          }
          document.addEventListener('click', function (e) {
            var btn = e.target.closest('.sx-lang-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var locale = btn.getAttribute('data-locale');
            if (!locale || btn.classList.contains('is-active') || btn.disabled) return;
            setLocale(locale, btn);
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
            + '<div style="font-weight:800;color:#940000;">' + @json(__('superadmin.security_warning')) + '</div>'
            + '<button type="button" id="idleStayBtn" style="border:1px solid rgba(148,0,0,.3);background:#fff;color:#940000;border-radius:8px;padding:6px 10px;cursor:pointer;">' + @json(__('superadmin.stay_logged_in')) + '</button>'
            + '</div>'
            + '<div style="margin-top:10px;color:#333;line-height:1.4;">' + @json(__('superadmin.idle_logout_before')) + ' <b id="idleCountdown">30</b> ' + @json(__('superadmin.idle_logout_after')) + '</div>'
            + '<div style="margin-top:10px;color:#666;font-size:.9rem;">' + @json(__('superadmin.idle_logout_hint')) + '</div>'
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
            $list.html('<div class="list-group-item small text-muted text-center py-4">' + @json(__('superadmin.no_unread_inquiries')) + '</div>');
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
