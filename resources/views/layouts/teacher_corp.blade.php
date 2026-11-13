<!DOCTYPE html>
@php
  $teacherLocale = app()->getLocale() === 'sw' ? 'sw' : 'en';
  $sessTeacherID = Session::get('teacherID');
  $sessSchoolID = Session::get('schoolID');
  $navTeacher = null;
  if (isset($teacher) && $teacher) {
      $navTeacher = $teacher;
  } elseif ($sessTeacherID) {
      $navTeacher = \App\Models\Teacher::find($sessTeacherID);
  }
  $navTeacherName = $navTeacher
      ? trim(($navTeacher->first_name ?? '') . ' ' . ($navTeacher->last_name ?? ''))
      : 'Teacher';
  if ($navTeacherName === '') { $navTeacherName = 'Teacher'; }
  $navTeacherInitial = strtoupper(substr($navTeacherName, 0, 1));
  $defaultTeacherImg = (($navTeacher && ($navTeacher->gender ?? '') === 'Female'))
      ? asset('images/female.png')
      : asset('images/male.png');
  $navTeacherPhoto = $defaultTeacherImg;
  if ($navTeacher && !empty($navTeacher->image)) {
      $rawImg = ltrim((string) $navTeacher->image, '/');
      if (preg_match('#^https?://#i', $rawImg)) {
          $navTeacherPhoto = $rawImg;
      } elseif (str_starts_with($rawImg, 'userImages/')) {
          $navTeacherPhoto = asset($rawImg);
      } else {
          $navTeacherPhoto = asset('userImages/' . $rawImg);
      }
  }
  $teacherRoleName = 'Teacher';
  if (isset($role) && $role && $role->count() > 0) {
      $firstRole = $role->first();
      if ($firstRole && !empty($firstRole->role_name)) {
          $teacherRoleName = $firstRole->role_name;
      }
  }
  $navSchool = $sessSchoolID ? \App\Models\School::find($sessSchoolID) : null;
  $navSchoolName = $navSchool->school_name ?? 'School';
  $saLogo = asset('images/shuleXpert.jpg');
  $saLogoFallback = asset('shule.png');
  $teacherNotificationsList = $teacherNotifications ?? collect();
  $teacherNotificationCount = is_countable($teacherNotificationsList) ? count($teacherNotificationsList) : 0;
@endphp
<html
  lang="{{ $teacherLocale }}"
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
    <title>@yield('title', __('teacher.portal_title'))</title>
    <meta name="description" content="ShuleXpert - Teacher Portal" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @include('includes.site_favicon')

    {{-- GXON required styles (same shell as Super Admin) --}}
    <link rel="stylesheet" href="{{ asset('gxon/libs/flaticon/css/all/all.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/lucide/lucide.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/simplebar/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/node-waves/waves.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('gxon/css/styles.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />

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
        color: #000 !important;
        line-height: 1.2;
        margin: 0;
      }
      .sx-sa-id-role {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
        line-height: 1.25;
        margin: 2px 0 0;
      }
      /* Keep identity label black even when app is in dark mode */
      html[data-sx-theme="dark"] .sx-sa-sidebar-identity,
      html[data-bs-theme="dark"] .sx-sa-sidebar-identity {
        background: #ffffff !important;
        border-bottom-color: #ececec !important;
        border-right-color: #eee !important;
      }
      html[data-sx-theme="dark"] .sx-sa-sidebar-identity .sx-sa-id-name,
      html[data-bs-theme="dark"] .sx-sa-sidebar-identity .sx-sa-id-name,
      html[data-sx-theme="dark"] .sx-sa-sidebar-identity .sx-sa-id-role,
      html[data-bs-theme="dark"] .sx-sa-sidebar-identity .sx-sa-id-role,
      html[data-sx-theme="dark"] .sx-sa-sidebar-identity p,
      html[data-bs-theme="dark"] .sx-sa-sidebar-identity p,
      html[data-sx-theme="dark"] .sx-sa-sidebar-identity .sx-sa-id-text,
      html[data-bs-theme="dark"] .sx-sa-sidebar-identity .sx-sa-id-text {
        color: #000 !important;
      }
      html[data-sx-theme="dark"] .app-header .dropdown-menu .fw-bold,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .fw-bold,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .text-dark,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .text-dark,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .text-body,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .text-body,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .sx-parent-id-name,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .sx-parent-id-name,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .sx-parent-id-role,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .sx-parent-id-role {
        color: #000 !important;
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
        background: #fff;
      }
      .app-header .dropdown-menu .fw-bold,
      .app-header .dropdown-menu .text-dark,
      .app-header .dropdown-menu .text-body,
      .app-header .dropdown-menu a,
      .app-header .dropdown-menu .fi {
        color: inherit !important;
      }
      .app-header .dropdown-menu .sx-parent-id-name,
      .app-header .dropdown-menu .sx-parent-id-role,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .sx-parent-id-name,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .sx-parent-id-name,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .sx-parent-id-role,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .sx-parent-id-role,
      html[data-sx-theme="dark"] .app-header .dropdown-menu .fw-bold.text-dark,
      html[data-bs-theme="dark"] .app-header .dropdown-menu .fw-bold.text-dark,
      html[data-sx-theme="dark"] .app-header .dropdown-menu small.text-body,
      html[data-bs-theme="dark"] .app-header .dropdown-menu small.text-body {
        color: #000 !important;
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
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary *,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary span,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary i,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary-custom,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary-custom *,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary-custom span,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .btn-primary-custom i,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head .btn-primary,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head .btn-primary *,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head .btn-primary span,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head .btn-primary i,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .text-white,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .text-white *,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head .text-white,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .app-page-head .text-white *,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .badge,
      html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-content-wrap .avatar {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
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

      /* Parent header: academic year + notifications */
      .app-header .parent-ay-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 280px;
      }
      .app-header .parent-ay-select {
        min-width: 110px;
        max-width: 150px;
        height: 34px;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 700;
        color: #940000 !important;
        background: #fff !important;
        border: 1px solid rgba(255,255,255,.55) !important;
        border-radius: 6px;
      }
      .app-header .parent-ay-select:focus {
        box-shadow: 0 0 0 0.15rem rgba(255,255,255,.25);
      }
      .parent-hp-notif-item {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 10px 14px;
        border-bottom: 1px solid #eee;
        color: inherit;
      }
      .parent-hp-notif-item.unread { background: #fff8f8; }
      .parent-hp-notif-icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        color: #940000;
        font-size: 22px;
        position: relative;
      }
      .parent-hp-notif-icon .hp-file-badge {
        position: absolute;
        right: -4px;
        bottom: -4px;
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #fff !important;
        border: 1px solid #fff;
      }
      .parent-hp-notif-icon .hp-file-badge.pdf { background: #E53935; }
      .parent-hp-notif-icon .hp-file-badge.doc { background: #1E88E5; }
      .parent-hp-notif-icon .hp-file-badge.file { background: #757575; }
      .parent-hp-notif-body { min-width: 0; flex: 1; }
      .parent-hp-notif-title { font-size: 12px; font-weight: 800; color: #940000; margin-bottom: 2px; }
      .parent-hp-notif-msg { font-size: 12px; color: #333; line-height: 1.35; }
      .parent-hp-notif-meta { font-size: 11px; color: #888; margin-top: 3px; }
      .sx-sa-id-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
      }
      .app-header .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
      }
      @media (max-width: 991.98px) {
        .app-header .sx-sa-header-date { display: none; }
      }
      @media (max-width: 575.98px) {
        .app-header .parent-ay-wrap,
        .app-header .teacher-ay-wrap { display: none; }
      }

      /* ========== Premium Teacher Sidebar ========== */
      /* Do NOT set width on :root — that blocks mini collapse */
      html[data-app-sidebar="full"],
      html[data-app-sidebar="mini-hover"] {
        --app-sidebar-width: 280px;
      }
      html[data-app-sidebar="mini"] {
        --app-sidebar-width: 80px;
      }
      html[data-app-sidebar="mini"] .app-menubar .menu-label,
      html[data-app-sidebar="mini"] .app-menubar .badge,
      html[data-app-sidebar="mini"] .app-menubar .menu-heading .menu-label,
      html[data-app-sidebar="mini"] .sx-sa-id-text {
        display: none !important;
      }
      html[data-app-sidebar="mini"] .app-menubar .app-navbar .menubar > li.menu-item .menu-inner,
      html[data-app-sidebar="mini"] .app-menubar .app-navbar .menu-arrow > .menu-link::after {
        display: none !important;
      }
      @media (max-width: 1199.98px) {
        .app-menubar {
          left: -300px;
          width: min(280px, 86vw);
        }
        .app-menubar.open {
          left: 0;
        }
        .sx-sidebar-backdrop {
          display: none;
          position: fixed;
          inset: 0;
          z-index: 8;
          background: rgba(0, 0, 0, 0.35);
        }
        .sx-sidebar-backdrop.show {
          display: block;
        }
      }
      .app-navbar {
        padding: 10px 12px 18px !important;
      }
      .app-navbar .menubar > li {
        margin-bottom: 2px !important;
      }
      .app-navbar .menubar .menu-heading {
        padding: 14px 12px 8px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        letter-spacing: 0.06em !important;
      }
      .app-navbar .menu-item .menu-link {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        min-height: 42px !important;
        padding: 10px 14px !important;
        padding-right: 34px !important;
        border-radius: 10px !important;
        font-size: 13.5px !important;
        font-weight: 650 !important;
        line-height: 1.25 !important;
        color: #1f1f1f !important;
      }
      .app-navbar .menu-item .menu-link > i {
        flex: 0 0 20px !important;
        width: 20px !important;
        margin-right: 0 !important;
        font-size: 17px !important;
        color: #6b6b6b !important;
        text-align: center;
      }
      .app-navbar .menu-item .menu-link .menu-label {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: unset !important;
        word-break: normal !important;
        overflow-wrap: anywhere !important;
        line-height: 1.3 !important;
        color: inherit !important;
      }
      .app-navbar .menu-arrow > .menu-link::after {
        right: 12px !important;
        font-size: 12px !important;
        opacity: 0.75;
      }
      .app-navbar .menubar > li.menu-item > .menu-link:hover,
      .app-navbar .menubar > li.menu-item > .menu-link.active,
      .app-navbar .menubar > li.menu-item > .menu-link.open,
      .app-navbar .menubar > li.menu-item > button.menu-link:hover {
        color: #fff !important;
        background-color: #940000 !important;
        box-shadow: 0 6px 14px rgba(148, 0, 0, 0.18) !important;
      }
      .app-navbar .menubar > li.menu-item > .menu-link:hover > i,
      .app-navbar .menubar > li.menu-item > .menu-link.active > i,
      .app-navbar .menubar > li.menu-item > .menu-link.open > i,
      .app-navbar .menubar > li.menu-item > .menu-link:hover .menu-label,
      .app-navbar .menubar > li.menu-item > .menu-link.active .menu-label,
      .app-navbar .menubar > li.menu-item > .menu-link.open .menu-label {
        color: #fff !important;
      }
      .app-navbar .menubar > li.menu-item .menu-inner {
        margin-left: 18px !important;
        margin-top: 4px !important;
        margin-bottom: 6px !important;
        padding-left: 10px !important;
        border-left: 2px solid #f0dada !important;
      }
      .app-navbar .menu-inner .menu-item > .menu-link {
        min-height: 34px !important;
        padding: 7px 12px !important;
        padding-right: 12px !important;
        font-size: 12.75px !important;
        font-weight: 600 !important;
        color: #333 !important;
        background: transparent !important;
        box-shadow: none !important;
      }
      .app-navbar .menu-inner .menu-item > .menu-link:hover,
      .app-navbar .menu-inner .menu-item > .menu-link.active {
        color: #940000 !important;
        background: #fff5f5 !important;
      }
      .app-navbar .menu-inner .menu-item > .menu-link > i {
        display: none !important;
      }
      .sx-menu-badge {
        font-size: 10px !important;
        min-width: 18px;
        margin-left: auto;
        flex-shrink: 0;
      }
      .sx-duty-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 10px;
        background: #fff8e8;
        border: 1px solid #ffe08a;
        color: #856404;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
      }
      .sx-duty-chip .fi { color: #c48a00; }

      .app-header .teacher-ay-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .app-header .teacher-ay-select {
        min-width: 110px;
        height: 32px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid rgba(255,255,255,.45);
        border-radius: 6px;
        background: rgba(255,255,255,.95);
        color: #333;
        padding: 2px 8px;
      }
      .app-header .sx-teacher-notif-item {
        display: flex;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #eee;
        text-decoration: none;
        color: inherit;
      }
      .app-header .sx-teacher-notif-item:hover { background: #fff5f5; }
      .app-header .sx-teacher-notif-item .fi { color: #940000; margin-top: 2px; }
    </style>

    @stack('styles')
    @include('includes.shulexpert_theme_css')

    {{-- Dark mode overrides AFTER page styles so text/icons stay readable on all Teacher pages --}}
    <style id="sx-teacher-dark-fix">
      /* ========== Sidebar (dark) ========== */
      html[data-sx-theme="dark"] .app-menubar,
      html[data-bs-theme="dark"] .app-menubar {
        background: #1a1215 !important;
        border-right: 1px solid rgba(255, 180, 180, 0.12) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .app-menubar .app-navbar,
      html[data-bs-theme="dark"] .app-menubar .app-navbar {
        background: transparent !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menu-item .menu-link,
      html[data-bs-theme="dark"] .app-navbar .menu-item .menu-link {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menu-item .menu-link .menu-label,
      html[data-bs-theme="dark"] .app-navbar .menu-item .menu-link .menu-label {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menu-item .menu-link > i,
      html[data-bs-theme="dark"] .app-navbar .menu-item .menu-link > i,
      html[data-sx-theme="dark"] .app-navbar .menu-item .menu-link .fi,
      html[data-bs-theme="dark"] .app-navbar .menu-item .menu-link .fi,
      html[data-sx-theme="dark"] .app-navbar .menu-item .menu-link .bi,
      html[data-bs-theme="dark"] .app-navbar .menu-item .menu-link .bi,
      html[data-sx-theme="dark"] .app-navbar .menu-item .menu-link .fa,
      html[data-bs-theme="dark"] .app-navbar .menu-item .menu-link .fa {
        color: #e8c9cd !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menu-arrow > .menu-link::after,
      html[data-bs-theme="dark"] .app-navbar .menu-arrow > .menu-link::after {
        color: #e8c9cd !important;
        opacity: .9;
      }
      html[data-sx-theme="dark"] .menu-heading .menu-label,
      html[data-bs-theme="dark"] .menu-heading .menu-label {
        color: #ffb4b4 !important;
        opacity: 1;
      }
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item .menu-inner,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item .menu-inner {
        border-left-color: rgba(255, 180, 180, 0.22) !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menu-inner .menu-item > .menu-link,
      html[data-bs-theme="dark"] .app-navbar .menu-inner .menu-item > .menu-link {
        color: #e2d0d4 !important;
        background: transparent !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menu-inner .menu-item > .menu-link:hover,
      html[data-bs-theme="dark"] .app-navbar .menu-inner .menu-item > .menu-link:hover,
      html[data-sx-theme="dark"] .app-navbar .menu-inner .menu-item > .menu-link.active,
      html[data-bs-theme="dark"] .app-navbar .menu-inner .menu-item > .menu-link.active {
        color: #ffd0d0 !important;
        background: rgba(148, 0, 0, 0.28) !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link:hover,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link:hover,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.active,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.active,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.open,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.open {
        color: #fff !important;
        background-color: #940000 !important;
      }
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link:hover > i,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link:hover > i,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.active > i,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.active > i,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.open > i,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.open > i,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link:hover .menu-label,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link:hover .menu-label,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.active .menu-label,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.active .menu-label,
      html[data-sx-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.open .menu-label,
      html[data-bs-theme="dark"] .app-navbar .menubar > li.menu-item > .menu-link.open .menu-label {
        color: #fff !important;
      }
      html[data-sx-theme="dark"] .sx-duty-chip,
      html[data-bs-theme="dark"] .sx-duty-chip {
        background: rgba(255, 214, 102, 0.12) !important;
        border-color: rgba(255, 214, 102, 0.28) !important;
        color: #ffe8a3 !important;
      }
      html[data-sx-theme="dark"] .sx-duty-chip .fi,
      html[data-bs-theme="dark"] .sx-duty-chip .fi {
        color: #ffd666 !important;
      }
      html[data-sx-theme="dark"] .simplebar-scrollbar::before,
      html[data-bs-theme="dark"] .simplebar-scrollbar::before {
        background: rgba(255, 180, 180, 0.45) !important;
      }

      /* Keep brand + identity readable (light strip) */
      html[data-sx-theme="dark"] .app-navbar-brand,
      html[data-bs-theme="dark"] .app-navbar-brand {
        background: #ffffff !important;
        border-bottom-color: #eee !important;
      }

      /* ========== Page content: text + icons (dark) ========== */
      html[data-sx-theme="dark"] .app-wrapper,
      html[data-bs-theme="dark"] .app-wrapper,
      html[data-sx-theme="dark"] .sx-sa-content-wrap,
      html[data-bs-theme="dark"] .sx-sa-content-wrap {
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(
        p, span, small, strong, em, label, li, td, th, dt, dd,
        h1, h2, h3, h4, h5, h6,
        .text-dark, .text-body, .text-muted, .text-secondary, .text-black,
        .form-label, .form-text, .card-title, .card-text, .card-header,
        .breadcrumb, .breadcrumb-item, .breadcrumb-item a,
        .nav-link, .list-group-item, .table, .dropdown-item
      ):not(.badge):not(.status-badge):not([class*="status-"]):not([class*="sx-tag"]):not(.sx-chip),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(
        p, span, small, strong, em, label, li, td, th, dt, dd,
        h1, h2, h3, h4, h5, h6,
        .text-dark, .text-body, .text-muted, .text-secondary, .text-black,
        .form-label, .form-text, .card-title, .card-text, .card-header,
        .breadcrumb, .breadcrumb-item, .breadcrumb-item a,
        .nav-link, .list-group-item, .table, .dropdown-item
      ):not(.badge):not(.status-badge):not([class*="status-"]):not([class*="sx-tag"]):not(.sx-chip) {
        color: #f3e9eb !important;
        -webkit-text-fill-color: currentColor;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .text-muted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .text-muted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap small,
      html[data-bs-theme="dark"] .sx-sa-content-wrap small,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .form-text,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .form-text {
        color: #d7c4c8 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap a:not(.btn):not(.badge),
      html[data-bs-theme="dark"] .sx-sa-content-wrap a:not(.btn):not(.badge) {
        color: #ffc9c9 !important;
      }

      /* Icons across all Teacher pages */
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(
        i, .bi, .fa, .fas, .far, .fal, .fab, .fad, .bx, .lni,
        [class^="fi-"], [class*=" fi-"], [class^="fa-"], [class*=" fa-"],
        [class^="bi-"], [class*=" bi-"]
      ),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(
        i, .bi, .fa, .fas, .far, .fal, .fab, .fad, .bx, .lni,
        [class^="fi-"], [class*=" fi-"], [class^="fa-"], [class*=" fa-"],
        [class^="bi-"], [class*=" bi-"]
      ),
      html[data-sx-theme="dark"] .app-page-head :where(i, .bi, .fa, .fas, .far, .fi, [class^="fi-"], [class*=" fi-"]),
      html[data-bs-theme="dark"] .app-page-head :where(i, .bi, .fa, .fas, .far, .fi, [class^="fi-"], [class*=" fi-"]) {
        color: #ffb4b4 !important;
      }

      /* Darken common white shells so light text remains readable */
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(
        .card, .sx-panel, .mt-page, .sx-score-hero, .sx-score-ring-wrap,
        .sx-sessions-toolbar, .sx-id-band, .sx-tange-box, .sx-notes,
        .sx-req-card, .sx-info-item, .sx-dl, .sx-dl-row, .sx-ep-table-wrap,
        .list-group-item, .modal-content, .dropdown-menu, .alert-light, .bg-white, .bg-light
      ),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(
        .card, .sx-panel, .mt-page, .sx-score-hero, .sx-score-ring-wrap,
        .sx-sessions-toolbar, .sx-id-band, .sx-tange-box, .sx-notes,
        .sx-req-card, .sx-info-item, .sx-dl, .sx-dl-row, .sx-ep-table-wrap,
        .list-group-item, .modal-content, .dropdown-menu, .alert-light, .bg-white, .bg-light
      ) {
        background-color: #24181c !important;
        background-image: none !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.sx-panel-head, .card-header, .sx-dl-k, thead, thead th, .table > thead),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.sx-panel-head, .card-header, .sx-dl-k, thead, thead th, .table > thead) {
        background: #1c1417 !important;
        background-image: none !important;
        color: #ffb4b4 !important;
        border-color: rgba(255, 180, 180, 0.14) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap table :where(th, td),
      html[data-bs-theme="dark"] .sx-sa-content-wrap table :where(th, td) {
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.12) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .table-hover tbody tr:hover,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .table-hover tbody tr:hover,
      html[data-sx-theme="dark"] .sx-sa-content-wrap table tbody tr:hover,
      html[data-bs-theme="dark"] .sx-sa-content-wrap table tbody tr:hover {
        background: rgba(148, 0, 0, 0.18) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.form-control, .form-select, .form-control-plaintext, input, textarea, select),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.form-control, .form-select, .form-control-plaintext, input, textarea, select) {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
        color: #f3e9eb !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.form-control, .form-select, input, textarea)::placeholder,
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.form-control, .form-select, input, textarea)::placeholder {
        color: #a99599 !important;
      }

      /* Preserve contrast on solid action buttons / badges */
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(
        .btn-primary, .btn-primary *, .btn-primary-custom, .btn-primary-custom *,
        .sx-btn-maroon, .sx-btn-maroon *, .bg-primary, .badge, .avatar, .text-white, .text-white *
      ),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(
        .btn-primary, .btn-primary *, .btn-primary-custom, .btn-primary-custom *,
        .sx-btn-maroon, .sx-btn-maroon *, .bg-primary, .badge, .avatar, .text-white, .text-white *
      ),
      html[data-sx-theme="dark"] .app-page-head :where(.btn-primary, .btn-primary *, .text-white, .text-white *),
      html[data-bs-theme="dark"] .app-page-head :where(.btn-primary, .btn-primary *, .text-white, .text-white *) {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.sx-btn-outline, .btn-outline-primary),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.sx-btn-outline, .btn-outline-primary) {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.sx-btn-outline, .btn-outline-primary):hover,
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.sx-btn-outline, .btn-outline-primary):hover {
        color: #fff !important;
        background: #940000 !important;
      }

      /* Status / semantic colors stay distinct */
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.text-success, .text-success *),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.text-success, .text-success *) { color: #69db7c !important; }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.text-danger, .text-danger *, .text-primary, .text-primary *, .text-maroon),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.text-danger, .text-danger *, .text-primary, .text-primary *, .text-maroon) { color: #ffb4b4 !important; }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.text-warning, .text-warning *),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.text-warning, .text-warning *) { color: #ffd666 !important; }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.text-info, .text-info *),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.text-info, .text-info *) { color: #74c0fc !important; }

      /* Page-local hard black overrides (common Teacher shells) */
      html[data-sx-theme="dark"] .sx-sa-content-wrap [class*="sx-"] :where(p, span, small, strong, label, td, th, h1, h2, h3, h4, h5, h6, li):not(.badge):not(.status-badge):not([class*="status-"]):not([class*="sx-tag"]):not(.sx-chip),
      html[data-bs-theme="dark"] .sx-sa-content-wrap [class*="sx-"] :where(p, span, small, strong, label, td, th, h1, h2, h3, h4, h5, h6, li):not(.badge):not(.status-badge):not([class*="status-"]):not([class*="sx-tag"]):not(.sx-chip) {
        color: #f3e9eb !important;
      }

      /* Status pills: keep readable colored text on tinted backgrounds */
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-badge,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-badge {
        -webkit-text-fill-color: unset !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-present,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-present {
        background: #e8f5e9 !important;
        color: #146c2e !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-early,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-early {
        background: #fff3cd !important;
        color: #7a4d00 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-late,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-late,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-absent,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-absent {
        background: #ffe0e0 !important;
        color: #7a0000 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-permitted,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-permitted,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-weekend,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-weekend {
        background: #efe5ff !important;
        color: #5a2d9c !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-off-day,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-off-day,
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-present-off-day,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-present-off-day {
        background: #ececec !important;
        color: #333 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .status-not-a-working-day,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .status-not-a-working-day {
        background: #fff3cd !important;
        color: #6b5300 !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.sx-tag-active, .sx-tag-approved, .sx-tag-completed),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.sx-tag-active, .sx-tag-approved, .sx-tag-completed) {
        color: #69db7c !important;
        background: rgba(105, 219, 124, 0.14) !important;
        border-color: rgba(105, 219, 124, 0.28) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.sx-tag-rejected, .sx-tag-inactive),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.sx-tag-rejected, .sx-tag-inactive) {
        color: #ffb4b4 !important;
        background: rgba(196, 58, 58, 0.18) !important;
        border-color: rgba(196, 58, 58, 0.3) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap :where(.sx-tag-pending, .sx-tag-in-progress, .sx-tag-submitted),
      html[data-bs-theme="dark"] .sx-sa-content-wrap :where(.sx-tag-pending, .sx-tag-in-progress, .sx-tag-submitted) {
        color: #ffd666 !important;
        background: rgba(255, 214, 102, 0.12) !important;
        border-color: rgba(255, 214, 102, 0.28) !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-chip,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-chip {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #e8c9cd !important;
      }
      html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-chip i,
      html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-chip i {
        color: #ffb4b4 !important;
      }
    </style>
  </head>

  <body>
    <div class="page-layout">
      {{-- Header --}}
      <header class="app-header">
        <div class="app-header-inner">
          <button class="app-toggler" type="button" aria-label="{{ __('teacher.toggle_menu') }}">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="app-header-start d-flex align-items-center">
            <div class="sx-sa-header-date">
              <span>{{ \Carbon\Carbon::now()->translatedFormat($teacherLocale === 'sw' ? 'l, j F Y · H:i' : 'l, F j, Y · g:i A') }}</span>
            </div>
          </div>

          <div class="app-header-end">
            <div class="px-lg-3 px-2 ps-0 d-flex align-items-center gap-2 flex-wrap justify-content-end">
              @php
                $tay = $headerAcademicYear ?? ['mode' => 'none'];
                $tayMode = $tay['mode'] ?? 'none';
              @endphp
              @if($tayMode !== 'none')
                <div class="teacher-ay-wrap" id="teacherAySelector" data-update-url="{{ route('selected_academic_year.update') }}">
                  @if(($tayMode === 'primary') || ($tayMode === 'secondary_o_only'))
                    <select class="form-select form-select-sm teacher-ay-select" data-ay-role="primary" aria-label="{{ __('teacher.academic_year') }}">
                      @foreach(($tay['years'] ?? $tay['o_level_years'] ?? []) as $y)
                        <option value="{{ $y->academic_yearID }}" @selected((int)($tay['selected_academic_yearID'] ?? $tay['selected_o_level_academic_yearID']) === (int)$y->academic_yearID)>
                          {{ $y->year_name ?: $y->year }}
                        </option>
                      @endforeach
                    </select>
                  @else
                    <select class="form-select form-select-sm teacher-ay-select" data-ay-role="o" title="{{ __('teacher.o_level') }}" aria-label="{{ __('teacher.o_level') }}">
                      @foreach(($tay['o_level_years'] ?? []) as $y)
                        <option value="{{ $y->academic_yearID }}" @selected((int)($tay['selected_o_level_academic_yearID'] ?? 0) === (int)$y->academic_yearID)>
                          O: {{ $y->year_name ?: $y->year }}
                        </option>
                      @endforeach
                    </select>
                    <select class="form-select form-select-sm teacher-ay-select" data-ay-role="a" title="{{ __('teacher.a_level') }}" aria-label="{{ __('teacher.a_level') }}">
                      @foreach(($tay['a_level_years'] ?? []) as $y)
                        <option value="{{ $y->academic_yearID }}" @selected((int)($tay['selected_a_level_academic_yearID'] ?? 0) === (int)$y->academic_yearID)>
                          A: {{ $y->year_name ?: $y->year }}
                        </option>
                      @endforeach
                    </select>
                  @endif
                </div>
              @endif

              <div class="sx-lang-switch" role="group" aria-label="{{ __('teacher.language') }}">
                <button type="button"
                        class="sx-lang-btn{{ $teacherLocale === 'en' ? ' is-active' : '' }}"
                        data-locale="en"
                        title="{{ __('teacher.english') }}"
                        aria-pressed="{{ $teacherLocale === 'en' ? 'true' : 'false' }}">
                  <img class="sx-lang-flag" src="https://flagcdn.com/w40/gb.png" width="18" height="12" alt="" loading="lazy" decoding="async">
                  <span>EN</span>
                </button>
                <button type="button"
                        class="sx-lang-btn{{ $teacherLocale === 'sw' ? ' is-active' : '' }}"
                        data-locale="sw"
                        title="{{ __('teacher.swahili') }}"
                        aria-pressed="{{ $teacherLocale === 'sw' ? 'true' : 'false' }}">
                  <img class="sx-lang-flag" src="https://flagcdn.com/w40/tz.png" width="18" height="12" alt="" loading="lazy" decoding="async">
                  <span>SW</span>
                </button>
              </div>

              <button type="button" class="sx-theme-toggle" aria-label="{{ __('teacher.theme_dark') }}" title="{{ __('teacher.theme_toggle') }}">
                <i class="fi fi-rr-moon sx-theme-icon-moon" aria-hidden="true"></i>
                <i class="fi fi-rr-sun sx-theme-icon-sun" aria-hidden="true"></i>
              </button>
            </div>
            <div class="vr my-3"></div>

            <div class="d-flex align-items-center gap-sm-2 gap-0 px-lg-4 px-sm-2 px-1">
              <div class="dropdown text-end" id="teacherNotifWrap">
                <button type="button" id="teacherNotifBtn"
                        class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="{{ __('teacher.notifications') }}">
                  <i class="fi fi-rr-bell"></i>
                  <span id="teacherNotifBadge" class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger {{ $teacherNotificationCount > 0 ? '' : 'd-none' }}"
                        style="font-size:10px;min-width:18px;">{{ $teacherNotificationCount > 99 ? '99+' : $teacherNotificationCount }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 mt-2" style="width:min(380px,92vw);">
                  <div class="px-3 py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('teacher.notifications') }}</h6>
                    <span class="badge bg-primary rounded-pill">{{ $teacherNotificationCount }}</span>
                  </div>
                  <div class="p-0" style="max-height: 360px; overflow:auto;">
                    @if($teacherNotificationCount > 0)
                      @foreach($teacherNotificationsList as $notification)
                        <a class="sx-teacher-notif-item" href="{{ $notification['link'] ?? '#' }}">
                          <i class="fi {{ isset($notification['icon']) ? str_replace('fa-', 'fi-rr-', $notification['icon']) : 'fi-rr-bell' }}"></i>
                          <div>
                            <div class="fw-bold text-dark" style="font-size:0.9rem;">{{ $notification['title'] ?? __('teacher.notification') }}</div>
                            <div class="small text-muted">{{ $notification['message'] ?? '' }}</div>
                            @if(!empty($notification['date']))
                              <small class="text-muted"><i class="fi fi-rr-clock"></i> {{ \Carbon\Carbon::parse($notification['date'])->diffForHumans() }}</small>
                            @endif
                          </div>
                        </a>
                      @endforeach
                    @else
                      <div class="p-4 text-muted small text-center">
                        <i class="fi fi-rr-bell-slash"></i> {{ __('teacher.no_notifications') }}
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="vr my-3"></div>

            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
              <a href="#" class="d-flex align-items-center py-2 text-decoration-none" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="My profile">
                <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold overflow-hidden">
                  <img src="{{ $navTeacherPhoto }}" alt="" onerror="this.onerror=null;this.src='{{ $defaultTeacherImg }}';">
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                <li class="d-flex align-items-center p-2">
                  <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold overflow-hidden">
                    <img src="{{ $navTeacherPhoto }}" alt="" onerror="this.onerror=null;this.src='{{ $defaultTeacherImg }}';">
                  </div>
                  <div class="ms-2">
                    <div class="fw-bold text-dark sx-parent-id-name">{{ strtoupper($navTeacherName) }}</div>
                    <small class="text-body d-block lh-sm sx-parent-id-role">{{ $teacherRoleName }}</small>
                  </div>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('teacher.profile') }}">
                    <i class="fi fi-rr-user scale-1x"></i> {{ __('teacher.view_profile') }}
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('teacher.profile') }}#change-password">
                    <i class="fi fi-rr-lock scale-1x"></i> {{ __('teacher.change_password') }}
                  </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger w-100 border-0 bg-transparent">
                      <i class="fi fi-sr-exit scale-1x"></i> {{ __('teacher.logout') }}
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </header>

      {{-- Sidebar --}}
      <div class="sx-sidebar-backdrop" id="sxSidebarBackdrop" aria-hidden="true"></div>
      <aside class="app-menubar" id="appMenubar">
        <div class="app-navbar-brand">
          <a class="navbar-brand-logo sx-sa-brand-logo" href="{{ route('teachersDashboard') }}" title="ShuleXpert">
            <img src="{{ $saLogo }}" alt="ShuleXpert" onerror="this.onerror=null;this.src='{{ $saLogoFallback }}';">
          </a>
        </div>

        <div class="sx-sa-sidebar-identity" aria-label="{{ __('teacher.signed_in_as') }}">
          <div class="sx-sa-id-avatar overflow-hidden" aria-hidden="true">
            <img src="{{ $navTeacherPhoto }}" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';this.parentElement.textContent='{{ $navTeacherInitial }}';">
          </div>
          <div class="sx-sa-id-text">
            <p class="sx-sa-id-name">{{ strtoupper($navTeacherName) }}</p>
            <p class="sx-sa-id-role">{{ $teacherRoleName }}</p>
          </div>
        </div>

        <nav class="app-navbar" data-simplebar>
          @include('includes.teacher_gxon_sidebar')
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
                      <li class="breadcrumb-item"><a href="{{ route('teachersDashboard') }}">{{ __('teacher.dashboard') }}</a></li>
                      <li class="breadcrumb-item active" aria-current="page">@yield('page_breadcrumb', __('teacher.dashboard'))</li>
                    </ol>
                  </nav>
                @endif
              </div>
            </div>
          @endif

          @yield('content')

          <footer class="sx-sa-footer">
            &copy; {{ date('Y') }} {{ __('teacher.footer') }}
          </footer>
        </div>
      </main>
    </div>

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
          var backdrop = document.getElementById('sxSidebarBackdrop');

          // GXON nested menu: toggle .open on menu-arrow items
          document.querySelectorAll('#teacherMenubar > li.menu-arrow > .menu-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
              e.preventDefault();
              var item = link.closest('li.menu-arrow');
              if (!item) return;
              var willOpen = !item.classList.contains('open');
              // Accordion: close other top-level groups
              document.querySelectorAll('#teacherMenubar > li.menu-arrow.open').forEach(function (openItem) {
                if (openItem === item) return;
                openItem.classList.remove('open');
                var openLink = openItem.querySelector(':scope > .menu-link');
                if (openLink) openLink.classList.remove('open');
              });
              item.classList.toggle('open', willOpen);
              link.classList.toggle('open', willOpen);
            });
          });

          function isDesktopSidebar() {
            return window.innerWidth >= 1200;
          }

          function setMobileOpen(open) {
            if (!menubar) return;
            menubar.classList.toggle('open', open);
            togglers.forEach(function (t) { t.classList.toggle('active', open); });
            if (backdrop) backdrop.classList.toggle('show', open);
          }

          if (menubar && togglers.length) {
            togglers.forEach(function (toggler) {
              toggler.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (isDesktopSidebar()) {
                  var current = document.documentElement.getAttribute('data-app-sidebar') || 'full';
                  var collapsed = current === 'mini' || current === 'mini-hover';
                  var next = collapsed ? 'full' : 'mini';
                  document.documentElement.setAttribute('data-app-sidebar', next);
                  togglers.forEach(function (t) { t.classList.toggle('active', next === 'mini'); });
                } else {
                  setMobileOpen(!menubar.classList.contains('open'));
                }
              });
            });

            if (backdrop) {
              backdrop.addEventListener('click', function () {
                setMobileOpen(false);
              });
            }

            window.addEventListener('resize', function () {
              if (isDesktopSidebar()) {
                setMobileOpen(false);
              }
            });

            menubar.addEventListener('mouseenter', function () {
              if (!isDesktopSidebar()) return;
              if (document.documentElement.getAttribute('data-app-sidebar') === 'mini') {
                document.documentElement.setAttribute('data-app-sidebar', 'mini-hover');
              }
            });
            menubar.addEventListener('mouseleave', function () {
              if (!isDesktopSidebar()) return;
              if (document.documentElement.getAttribute('data-app-sidebar') === 'mini-hover') {
                document.documentElement.setAttribute('data-app-sidebar', 'mini');
              }
            });
          }
        });
      })();
    </script>

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
              alert((data && data.message) ? data.message : 'Language change failed');
            })
            .catch(function () {
              allBtns.forEach(function (b) { b.disabled = false; });
              if (btn) btn.classList.remove('is-loading');
              alert('Language change failed');
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
        var csrf = $('meta[name="csrf-token"]').attr('content');
        $('.teacher-ay-select').on('change', function () {
          var $wrap = $('#teacherAySelector');
          var url = $wrap.data('update-url');
          var payload = { _token: csrf };
          var role = $(this).data('ay-role');
          if (role === 'primary') {
            payload.academic_yearID = $(this).val();
            payload.o_level_academic_yearID = $(this).val();
          } else if (role === 'o') {
            payload.o_level_academic_yearID = $(this).val();
          } else if (role === 'a') {
            payload.a_level_academic_yearID = $(this).val();
          }
          $.post(url, payload).done(function () {
            location.reload();
          }).fail(function () {
            alert('Failed to update academic year');
          });
        });
      });
    </script>
  </body>
</html>
