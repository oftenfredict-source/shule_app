@php
    $user_type = $user_type ?? Session::get('user_type');
@endphp
@if($user_type == 'Staff')
@include('includes.staff_nav')
@else
<!doctype html>
<html class="no-js" lang="en">
<head>
    @include('includes.theme_init')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin</title>
    <meta name="description" content="Sufee Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('includes.site_favicon')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/themify-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/selectFX/css/cs-skin-elastic.css') }}">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<!-- Bootstrap Bundle JS (includes Popper) -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script src="{{ asset('vendors/jquery/dist/jquery.js') }}" onerror="this.onerror=null;this.src='https://code.jquery.com/jquery-3.6.4.min.js';"></script>
    <script>
        (function bindJqueryAlias() {
            function lockDollarToJquery() {
                if (typeof window.jQuery !== 'function') {
                    return false;
                }
                try {
                    Object.defineProperty(window, '$', {
                        configurable: true,
                        enumerable: true,
                        get: function () { return window.jQuery; },
                        set: function (value) {
                            if (typeof value === 'function' && value.fn) {
                                window.jQuery = value;
                            }
                        }
                    });
                } catch (e) {
                    window.$ = window.jQuery;
                }
                return true;
            }

            if (lockDollarToJquery()) {
                return;
            }

            // Local asset may still be loading after onerror CDN swap
            var tries = 0;
            var timer = setInterval(function () {
                tries += 1;
                if (lockDollarToJquery()) {
                    clearInterval(timer);
                } else if (tries > 40) {
                    clearInterval(timer);
                    var s = document.createElement('script');
                    s.src = 'https://code.jquery.com/jquery-3.6.4.min.js';
                    s.onload = function () { lockDollarToJquery(); };
                    document.head.appendChild(s);
                }
            }, 50);
        })();
    </script>

    <style>
/* Badilisha rangi ya background ya sidebar */
#left-panel,
#left-panel .navbar,
#left-panel .navbar-default,
#left-panel .main-menu,
#left-panel .navbar-nav,
#left-panel ul {
    background-color: #ffffff !important; /* nyeupe */
    color: #2f2f2f !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Badilisha rangi ya maandishi (links) */
#left-panel .nav-link,
#left-panel a.nav-link,
#left-panel li a,
#left-panel .navbar-nav li a,
#left-panel .navbar-nav > li > a {
    color: #2f2f2f !important;
    font-weight: 600;
    background-color: transparent !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Rangi ya icon ndani ya links */
#left-panel .nav-link i,
#left-panel a.nav-link i,
#left-panel li a i,
#left-panel .navbar-nav li a i,
#left-panel .fa {
    color: #940000 !important;
}

/* Hover effect ya link */
#left-panel .nav-link:hover,
#left-panel a.nav-link:hover,
#left-panel li a:hover,
#left-panel .navbar-nav li a:hover,
#left-panel .navbar-nav > li > a:hover,
#left-panel li:hover {
    background-color: #f5f5f5 !important; /* kijivu chepesi */
    color: #2f2f2f !important;
}

/* Active link - higher specificity */
#left-panel .nav-link.active,
#left-panel a.nav-link.active,
#left-panel li.active > a,
#left-panel .navbar-nav > li.active > a,
#left-panel .nav-link.menu-active,
#left-panel a.nav-link.menu-active,
#left-panel li.menu-active > a,
#left-panel .sidebar-links-container .nav-link.menu-active,
#left-panel .sidebar-links-container a.nav-link.menu-active,
#left-panel .submenu .nav-link.menu-active,
#left-panel .submenu a.nav-link.menu-active {
    background-color: rgba(148, 0, 0, 0.08) !important;
    color: #2f2f2f !important;
    border-radius: 4px !important;
    padding: 8px 15px !important;
    margin: 2px 0 !important;
}

/* Active link icons - white when active */
#left-panel .nav-link.active i,
#left-panel a.nav-link.active i,
#left-panel li.active > a i,
#left-panel .nav-link.menu-active i,
#left-panel a.nav-link.menu-active i,
#left-panel .sidebar-links-container .nav-link.menu-active i,
#left-panel .submenu .nav-link.menu-active i {
    color: #940000 !important;
}

/* Active dropdown toggle */
#left-panel .dropdown-toggle.menu-active {
    background-color: rgba(148, 0, 0, 0.08) !important;
    color: #2f2f2f !important;
    border-radius: 4px !important;
}

#left-panel .dropdown-toggle.menu-active i {
    color: #940000 !important;
}

/* Header icon styling */
.header-icon-muted {
    color: rgba(148, 0, 0, 0.7) !important;
    font-size: 1.1rem;
}

        .admin-sidebar-title {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 6px 12px;
            border-bottom: 1px solid #f0f0f0;
            background: #fff;
            height: 55px;
            min-height: 55px;
            max-height: 55px;
            box-sizing: border-box;
            overflow: hidden;
        }

        .admin-brand-logo {
            width: 100%;
            max-width: 150px;
            height: 42px;
            object-fit: contain;
            object-position: center;
            display: block;
            background: #fff;
        }

        .admin-brand-block {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .admin-brand-text {
            color: #940000 !important;
            font-weight: 800;
        }

        .admin-brand-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 2px;
        }
#notification.dropdown-toggle {
    position: relative;
    overflow: visible !important;
    width: 42px;
    height: 42px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

#header .header-left .dropdown .dropdown-toggle#notification .notification-count {
    position: absolute;
    top: 2px;
    right: 0;
    min-width: 20px;
    width: auto;
    height: 20px;
    padding: 0 5px;
    margin: 0;
    border-radius: 999px;
    background: #940000 !important;
    color: #fff !important;
    font-size: 11px;
    font-weight: 700;
    line-height: 16px;
    text-align: center;
    letter-spacing: 0;
    border: 2px solid #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
    z-index: 3;
    box-sizing: border-box;
}

#visitorNotificationMenu.dropdown-menu {
    padding: 0 !important;
    min-width: 0;
    width: min(380px, calc(100vw - 20px));
    border: 1px solid #ececec !important;
    border-radius: 12px !important;
    overflow: hidden;
    box-shadow: 0 14px 36px rgba(0, 0, 0, 0.12);
}

.perm-bell-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.perm-bell-head .red {
    padding: 0 !important;
    margin: 0;
    flex: 1 1 auto;
    font-size: 14px;
    font-weight: 700;
    color: #1f1f1f;
    line-height: 1.3;
}

#visitorNotificationList {
    max-height: 62vh;
    overflow-y: auto;
    background: #fff;
}

.bell-note {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    border-bottom: 1px solid #f0f0f0;
    text-decoration: none !important;
    color: inherit;
}

.bell-note[data-href] {
    cursor: pointer;
}

.bell-feed-more {
    padding: 10px 14px 12px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    color: #940000;
    background: #fff;
}

.bell-note:last-child {
    border-bottom: 0;
}

.bell-note.is-unread {
    background: #fff4f4;
    box-shadow: inset 4px 0 0 #940000;
}

.bell-note.is-read {
    background: #f6f6f6;
    box-shadow: inset 4px 0 0 #d5d5d5;
}

#header #visitorNotificationMenu a.bell-note.is-unread,
#header #visitorNotificationMenu a.bell-note.is-unread:hover {
    background: #fff4f4 !important;
    color: inherit !important;
}

#header #visitorNotificationMenu a.bell-note.is-read,
#header #visitorNotificationMenu a.bell-note.is-read:hover {
    background: #f6f6f6 !important;
    color: inherit !important;
}

.bell-note-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    font-size: 14px;
}

.bell-note.is-unread .bell-note-icon {
    background: #940000;
    color: #fff;
}

.bell-note.is-read .bell-note-icon {
    background: #e6e6e6;
    color: #8a8a8a;
}

.bell-note-main {
    flex: 1 1 auto;
    min-width: 0;
}

.bell-note-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.bell-note-title {
    font-size: 13px;
    line-height: 1.3;
}

.bell-note.is-unread .bell-note-title {
    color: #1a1a1a;
    font-weight: 700;
}

.bell-note.is-read .bell-note-title {
    color: #8a8a8a;
    font-weight: 600;
}

.bell-note-time {
    flex: 0 0 auto;
    font-size: 11px;
    color: #8a8a8a;
    white-space: nowrap;
}

.bell-note-meta {
    margin-top: 3px;
    font-size: 12px;
    line-height: 1.35;
    color: #555;
}

.bell-note.is-read .bell-note-meta {
    color: #9a9a9a;
}

.bell-note-status {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 6px;
}

.bell-note-flag {
    display: inline-block;
    margin-top: 0;
    padding: 2px 7px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.bell-mark-read {
    border: 0;
    background: transparent;
    color: #940000;
    font-size: 12px;
    font-weight: 700;
    padding: 0;
    cursor: pointer;
}

.bell-note-title a {
    color: inherit;
    text-decoration: none;
}

.bell-note-title a:hover {
    text-decoration: underline;
}

.bell-note.is-unread .bell-note-flag {
    background: #940000;
    color: #fff;
}

.bell-note.is-read .bell-note-flag {
    background: #e4e4e4;
    color: #777;
}

.perm-bell-actions {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}

.perm-bell-actions .bell-act {
    flex: 1 1 0;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    cursor: pointer;
    position: relative;
    z-index: 2;
}

.bell-act-view {
    background: #fff;
    color: #222;
    border: 1px solid #cfcfcf;
}

.bell-act-accept {
    background: #157347;
    color: #fff;
    border: 1px solid #157347;
}

.bell-act-reject {
    background: #940000;
    color: #fff;
    border: 1px solid #940000;
}

#markAllNotificationsRead {
    flex: 0 0 auto;
    border: 0;
    background: transparent;
    color: #940000;
    font-size: 12px;
    font-weight: 700;
    padding: 0;
    white-space: nowrap;
    cursor: pointer;
}

#markAllNotificationsRead:hover {
    text-decoration: underline;
}

#bellOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 200000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

#bellOverlay.is-open {
    display: flex;
}

#bellOverlayCard {
    width: min(460px, 100%);
    background: #fff;
    border-radius: 12px;
    padding: 16px 16px 14px;
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.25);
    position: relative;
}

#bellOverlayLoading {
    display: none;
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    z-index: 5;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 8px;
    color: #940000;
    font-weight: 700;
}

#bellOverlayLoading.is-on {
    display: flex;
}

#bellOverlayHead {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}

#bellOverlayTitle {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
}

#bellOverlayClose {
    border: 0;
    background: transparent;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    color: #666;
}

#bellOverlayBody {
    font-size: 14px;
    color: #333;
    line-height: 1.5;
}

#bellOverlayBody textarea {
    width: 100%;
    min-height: 90px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 8px 10px;
    margin-top: 8px;
}

#bellOverlayError {
    color: #940000;
    font-size: 13px;
    margin-top: 6px;
}

/* Rangi ya jina la "Teacher" na maandishi ya ndani ya sidebar */
#left-panel p,
#left-panel .navbar-brand,
#left-panel .navbar-brand:hover {
    color: #2f2f2f !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Rangi ya navbar brand */
#left-panel .navbar-header .navbar-brand {
    color: #2f2f2f !important;
    font-weight: 700 !important;
}

/* Rangi ya toggle button */
#left-panel .navbar-toggler,
#left-panel .navbar-toggler i {
    color: #2f2f2f !important;
}

/* Background ya list items */
#left-panel .navbar-nav li {
    background-color: transparent !important;
}

/* Border na dividers */
#left-panel .navbar-nav li {
    border-bottom: 1px solid #f0f0f0 !important;
}

/* Ensure all text in sidebar is #940000 */
#left-panel * {
    color: #2f2f2f !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Ensure brand header keeps intended colors (override global sidebar rule) */
#left-panel .admin-brand-text {
    color: #940000 !important;
    font-weight: 800 !important;
}

#left-panel .admin-brand-subtitle {
    color: #6c757d !important;
}

/* Exception for icons - keep FontAwesome font-family and #940000 color */
#left-panel i,
#left-panel .fa,
#left-panel [class*="fa-"],
#left-panel [class^="fa-"] {
    color: #940000 !important;
    font-family: 'FontAwesome' !important;
}

/* Sidebar links: NO inner scrollbar, only outer #left-panel scrolls */
.sidebar-links-container {
    overflow: visible !important;
    overflow-x: visible !important;
    overflow-y: visible !important;
    max-height: none !important;
    height: auto !important;
    width: 100% !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.sidebar-links-container::-webkit-scrollbar,
.sidebar-links-container:hover::-webkit-scrollbar {
    width: 0 !important;
    display: none !important;
}

.sidebar-links-container:hover {
    overflow: visible !important;
}

.sidebar-links-container ul {
    width: 100% !important;
}

aside.left-panel,
aside#left-panel {
    display: flex !important;
    flex-direction: column !important;
    vertical-align: top;
    overflow: hidden !important;
    max-height: 100vh !important;
    height: 100vh !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

#left-panel > .navbar {
    display: flex !important;
    flex-direction: column !important;
    flex: 1 1 auto !important;
    height: 100% !important;
    min-height: 0 !important;
    margin-bottom: 0 !important;
    overflow: hidden !important;
}

/* Logo card - pinned, never scrolls away */
#left-panel .navbar-header {
    position: relative !important;
    z-index: 20 !important;
    flex: 0 0 55px !important;
    width: 100% !important;
    height: 55px !important;
    min-height: 55px !important;
    max-height: 55px !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    border-bottom: 1px solid #f0f0f0 !important;
    overflow: hidden !important;
}

#left-panel .admin-sidebar-title {
    background: #ffffff !important;
}

#left-panel .admin-brand-logo {
    background: #ffffff !important;
}

/* Keep menu visible (Bootstrap .collapse can hide it) and scrollable */
#left-panel #main-menu,
#left-panel #main-menu.collapse,
#left-panel #main-menu.navbar-collapse {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    flex: 1 1 auto !important;
    min-height: 0 !important;
    height: auto !important;
    max-height: none !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 8px 12px 24px !important;
    width: 100% !important;
}

#left-panel #main-menu .nav.navbar-nav,
#left-panel #main-menu .sidebar-links-container,
#left-panel #main-menu .sidebar-links-container > ul {
    display: block !important;
    visibility: visible !important;
    width: 100% !important;
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
}

#left-panel #main-menu .nav-link {
    display: block !important;
    visibility: visible !important;
}

#left-panel #main-menu::-webkit-scrollbar {
    width: 8px !important;
}

#left-panel #main-menu::-webkit-scrollbar-track {
    background: #f0f0f0 !important;
}

#left-panel #main-menu::-webkit-scrollbar-thumb {
    background: #cfcfcf !important;
    border-radius: 4px !important;
}

#left-panel #main-menu::-webkit-scrollbar-thumb:hover {
    background: #bdbdbd !important;
}

/* Dropdown menu items styling */
.dropdown-nav-item {
    position: relative;
}

.dropdown-nav-item .dropdown-toggle {
    cursor: pointer;
    position: relative;
}

.dropdown-nav-item .dropdown-toggle i.fa-chevron-down {
    transition: transform 0.3s ease;
    font-size: 0.75rem;
    margin-top: 3px;
}

.dropdown-nav-item .dropdown-toggle[aria-expanded="true"] i.fa-chevron-down {
    transform: rotate(180deg);
}

.dropdown-nav-item .submenu {
    background-color: #f8f9fa !important;
    border-left: 2px solid #e0e0e0;
    margin-left: 10px;
}

.dropdown-nav-item .submenu li {
    border-bottom: none !important;
}

.dropdown-nav-item .submenu li a {
    padding-left: 15px !important;
    font-size: 0.9rem;
    color: #2f2f2f !important;
}

.dropdown-nav-item .submenu li a:hover {
    background-color: #f2f2f2 !important;
    padding-left: 20px !important;
}

.dropdown-nav-item .submenu li a i {
    margin-right: 8px;
    font-size: 0.85rem;
}

/*
 * Collapsed sidebar (desktop) styles live in footer.blade.php.
 * Mobile uses off-canvas full menu, do not force 76px here.
 */
@media (min-width: 992px) {
    body.open aside#left-panel,
    body.open aside.left-panel {
        width: 76px !important;
        max-width: 76px !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.open .right-panel,
    body.open #right-panel {
        margin-left: 76px !important;
    }
}

/* Header styling - Super Admin style (brand red, flex layout) */
#header,
#header.header,
.right-panel header.header,
.right-panel #header {
    background: #940000 !important;
    border-bottom: none !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.18) !important;
    height: 55px !important;
    min-height: 55px !important;
    max-height: 55px !important;
    padding: 0 16px !important;
    display: flex !important;
    align-items: center !important;
    clear: both !important;
    width: 100% !important;
    box-sizing: border-box !important;
    overflow: visible !important;
}

#header .header-menu {
    background: transparent !important;
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    width: 100% !important;
    height: 55px !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

#header .header-menu > .col-sm-7,
#header .header-menu > .col-sm-5 {
    float: none !important;
    width: auto !important;
    max-width: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    position: static !important;
}

#header .header-menu > .col-sm-7 {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    gap: 8px !important;
}

#header .header-menu > .col-sm-5 {
    flex: 0 0 auto !important;
    margin-left: auto !important;
    justify-content: flex-end !important;
}

#header .header-left {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    gap: 6px !important;
    min-width: 0 !important;
    float: none !important;
}

#header .header-left > .dropdown,
#header .header-left > .header-academic-year {
    display: inline-flex !important;
    align-items: center !important;
    flex-shrink: 0 !important;
    margin: 0 !important;
}

#header .menutoggle,
#header .search-trigger,
#header .btn-secondary,
#header .header-icon-muted {
    color: #fff !important;
}

#header .menutoggle,
a#menuToggle.menutoggle,
.header .menutoggle {
    background: transparent !important;
    color: #fff !important;
    border: 1px solid rgba(255,255,255,0.35) !important;
    border-radius: 0 !important;
    padding: 0 !important;
    margin: 0 8px 0 0 !important;
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    text-decoration: none !important;
    float: none !important;
    position: static !important;
    cursor: pointer !important;
}

#header .menutoggle i,
#header .menutoggle .header-icon-muted,
a#menuToggle.menutoggle i,
a#menuToggle.menutoggle .header-icon-muted {
    color: #fff !important;
    opacity: 1 !important;
    font-size: 16px !important;
    line-height: 1 !important;
    margin: 0 !important;
}

#header .menutoggle:hover,
a#menuToggle.menutoggle:hover {
    background: rgba(255,255,255,0.12) !important;
    color: #fff !important;
}

#header .btn-secondary {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #fff !important;
    padding: 6px 8px !important;
    height: 36px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
#header .btn-secondary:hover,
#header .btn-secondary:focus,
#header .btn-secondary:active {
    background: rgba(255,255,255,0.12) !important;
    color: #fff !important;
    box-shadow: none !important;
}

#header .user-area.dropdown {
    display: inline-flex !important;
    align-items: center !important;
    margin: 0 !important;
}

#header .user-avatar {
    border: 2px solid rgba(255,255,255,0.7) !important;
    background: #fff !important;
    width: 34px !important;
    height: 34px !important;
}

#header .dropdown-toggle {
    color: #fff !important;
}

#header .dropdown-menu {
    border: 1px solid #e0e0e0 !important;
    background: #fff !important;
    color: #2f2f2f !important;
}

#header .dropdown-menu a,
#header .dropdown-menu .nav-link,
#header .dropdown-menu p,
#header .dropdown-menu span {
    color: #2f2f2f !important;
}

#header .dropdown-menu a:hover,
#header .dropdown-menu .nav-link:hover {
    background-color: #f5f5f5 !important;
    color: #2f2f2f !important;
}

#header .sx-theme-toggle {
    width: 36px !important;
    height: 36px !important;
    border: 1px solid rgba(255,255,255,0.35) !important;
    background: transparent !important;
    color: #fff !important;
    border-radius: 0 !important;
    flex-shrink: 0;
}
#header .sx-theme-toggle:hover {
    background: rgba(255,255,255,0.12) !important;
    color: #fff !important;
    border-color: rgba(255,255,255,0.55) !important;
}
#header .sx-theme-toggle i {
    color: #fff !important;
}
#header .sx-theme-toggle.is-dark {
    background: rgba(0,0,0,0.2) !important;
    border-color: rgba(255,255,255,0.45) !important;
}

#header .header-academic-year {
    margin-left: 8px !important;
}
#header .header-academic-year .ay-label {
    color: rgba(255,255,255,0.9) !important;
}
#header .header-academic-year select {
    height: 32px !important;
    border: 1px solid rgba(255,255,255,0.35) !important;
    background: rgba(255,255,255,0.95) !important;
    color: #333 !important;
}

#header .float-right {
    float: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
}

/* Logo in dashboard header when sidebar is collapsed */
#header .admin-header-brand {
    display: none !important;
    align-items: center;
    justify-content: center;
    height: 40px;
    max-width: 140px;
    padding: 2px 8px;
    background: #fff;
    text-decoration: none;
    flex-shrink: 0;
    overflow: hidden;
    box-sizing: border-box;
}
#header .admin-header-brand img {
    display: block;
    height: 36px;
    width: auto;
    max-width: 130px;
    object-fit: contain;
    background: #fff;
}
@media (min-width: 992px) {
    body.open #header .admin-header-brand {
        display: inline-flex !important;
    }
}

/* Soft dark - Admin chrome (beats hard-coded white !important rules) */
html[data-sx-theme="dark"] #left-panel,
html[data-sx-theme="dark"] #left-panel .navbar,
html[data-sx-theme="dark"] #left-panel .navbar-default,
html[data-sx-theme="dark"] #left-panel .main-menu,
html[data-sx-theme="dark"] #left-panel .navbar-nav,
html[data-sx-theme="dark"] #left-panel ul,
html[data-sx-theme="dark"] #left-panel li,
html[data-sx-theme="dark"] #left-panel .sidebar-links-container {
    background-color: #1a1d21 !important;
    background: #1a1d21 !important;
    color: #c9b4b8 !important;
    border-color: #2a2e34 !important;
}
html[data-sx-theme="dark"] #left-panel {
    border-right: 1px solid #2a2e34 !important;
    box-shadow: 2px 0 16px rgba(0, 0, 0, 0.3) !important;
}
/* Logo card stays fully white (dark + light) */
html[data-sx-theme="dark"] #left-panel .navbar-header,
html[data-sx-theme="dark"] #left-panel .navbar-header *,
html[data-sx-theme="dark"] #left-panel .admin-sidebar-title,
html[data-sx-theme="dark"] #left-panel .admin-brand-logo,
html[data-sx-theme="light"] #left-panel .navbar-header,
html[data-sx-theme="light"] #left-panel .admin-sidebar-title,
html[data-sx-theme="light"] #left-panel .admin-brand-logo,
html[data-sx-theme="dark"] #header .admin-header-brand,
html[data-sx-theme="dark"] #header .admin-header-brand img {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border-bottom-color: #e8e8e8 !important;
}
html[data-sx-theme="dark"] #left-panel #main-menu::-webkit-scrollbar-track {
    background: #15181c !important;
}
html[data-sx-theme="dark"] #left-panel #main-menu::-webkit-scrollbar-thumb {
    background: #3a3f46 !important;
}
html[data-sx-theme="dark"] #header,
html[data-sx-theme="dark"] #header.header,
html[data-sx-theme="dark"] .right-panel header.header {
    background: #940000 !important;
}
html[data-sx-theme="dark"] #left-panel .nav-link,
html[data-sx-theme="dark"] #left-panel a.nav-link,
html[data-sx-theme="dark"] #left-panel li a,
html[data-sx-theme="dark"] #left-panel .navbar-nav li a,
html[data-sx-theme="dark"] #left-panel .navbar-nav > li > a,
html[data-sx-theme="dark"] #left-panel p,
html[data-sx-theme="dark"] #left-panel .navbar-brand {
    color: #c9b4b8 !important;
    background-color: transparent !important;
}
html[data-sx-theme="dark"] #left-panel .nav-link i,
html[data-sx-theme="dark"] #left-panel a.nav-link i,
html[data-sx-theme="dark"] #left-panel li a i,
html[data-sx-theme="dark"] #left-panel .fa,
html[data-sx-theme="dark"] #left-panel [class*="fa-"] {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] #left-panel .nav-link:hover,
html[data-sx-theme="dark"] #left-panel a.nav-link:hover,
html[data-sx-theme="dark"] #left-panel li a:hover,
html[data-sx-theme="dark"] #left-panel .navbar-nav li a:hover,
html[data-sx-theme="dark"] #left-panel li:hover {
    background-color: rgba(148, 0, 0, 0.22) !important;
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] #left-panel .nav-link.active,
html[data-sx-theme="dark"] #left-panel a.nav-link.active,
html[data-sx-theme="dark"] #left-panel li.active > a,
html[data-sx-theme="dark"] #left-panel .nav-link.menu-active,
html[data-sx-theme="dark"] #left-panel a.nav-link.menu-active,
html[data-sx-theme="dark"] #left-panel .dropdown-toggle.menu-active {
    background-color: rgba(148, 0, 0, 0.28) !important;
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] #left-panel .dropdown-nav-item .submenu {
    background-color: #15181c !important;
    border-left-color: #2a2e34 !important;
}
html[data-sx-theme="dark"] #left-panel .navbar-nav li {
    border-bottom-color: #2a2e34 !important;
}
html[data-sx-theme="dark"] #left-panel::-webkit-scrollbar-track {
    background: #15181c !important;
}
html[data-sx-theme="dark"] #left-panel::-webkit-scrollbar-thumb {
    background: #3a3f46 !important;
}
html[data-sx-theme="dark"] #right-panel,
html[data-sx-theme="dark"] .right-panel,
html[data-sx-theme="dark"] body {
    background-color: #160d10 !important;
}
html[data-sx-theme="dark"] .site-footer,
html[data-sx-theme="dark"] .footer-area {
    background-color: #0c0709 !important;
    color: #c9b4b8 !important;
    border-top-color: rgba(255, 180, 180, 0.18) !important;
}

/* Brand styling */
.brand-title {
    color: #940000 !important;
    font-weight: 700 !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Brand row - aligns with red #header (55px), like Super Admin */
.navbar-header {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0;
    height: 55px;
    min-height: 55px;
    max-height: 55px;
    background: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    overflow: hidden;
    box-sizing: border-box;
}

.mobile-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.45rem;
    font-weight: 800;
    color: #ffffff !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    text-decoration: none !important;
    letter-spacing: 0.5px;
    line-height: 1;
}

.mobile-brand:hover {
    color: #ffffff !important;
    text-decoration: none !important;
}

.mobile-brand i {
    color: #ffffff !important;
    font-size: 1.3rem;
}

/* Force white color inside navbar-header (overrides #left-panel * rule) */
#left-panel .navbar-header .mobile-brand,
#left-panel .navbar .navbar-header .mobile-brand {
    color: #ffffff !important;
}

#left-panel .navbar-header .mobile-brand i,
#left-panel .navbar-header .mobile-brand .fa,
#left-panel .navbar .navbar-header .mobile-brand i {
    color: #ffffff !important;
    font-family: 'FontAwesome' !important;
}

#left-panel .navbar-header .nav-dot {
    background-color: #ffffff !important;
}

.navbar-toggler {
    border: none;
    padding: 6px 4px;
    border-radius: 4px;
    background: transparent;
    color: #ffffff;
    cursor: pointer;
    margin-left: auto;
    box-shadow: none;
    outline: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.navbar-toggler:focus {
    outline: none;
    box-shadow: none;
}

.nav-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background-color: #ffffff;
    display: block;
}

/* Hide mobile brand on desktop (when sidebar is always visible) */
@media (min-width: 768px) {
    .mobile-brand {
        display: none;
    }
    .navbar-header {
        justify-content: center;
        padding: 0 !important;
        background: #ffffff !important;
        border-bottom: 1px solid #f0f0f0 !important;
        height: 55px !important;
        min-height: 55px !important;
        max-height: 55px !important;
    }
    .navbar-toggler {
        margin-left: 0;
    }
    .nav-dot {
        background-color: #940000;
    }
}

/* Mobile: remove aside padding so navbar-header is truly full width */
@media (max-width: 767px) {
    aside.left-panel,
    aside#left-panel {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    #main-menu {
        padding-left: 25px;
        padding-right: 25px;
    }
}

/* Sidebar profile block */
.sidebar-profile {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(148, 0, 0, 0.08);
    border: 1px solid rgba(148, 0, 0, 0.35);
    border-radius: 8px;
}
.sidebar-profile img.profile-image {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    background: rgba(148, 0, 0, 0.08);
    border: 2px solid rgba(148, 0, 0, 0.35);
}
.sidebar-profile .profile-name {
    font-weight: 700;
    color: #2f2f2f !important;
}
.sidebar-profile .profile-role {
    font-size: 0.8rem;
    color: #666666 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

@include('includes.mobile_app_bar_styles')
@include('includes.shulexpert_theme_css')
</head>
<body>
    {{-- Fixed mobile top bar (visible when sidebar is off-canvas) --}}
    <div class="mobile-app-bar" id="mobileAppBar" aria-label="Mobile navigation">
        <a class="mobile-brand" href="{{ route('AdminDashboard') }}">
            <i class="fa fa-graduation-cap"></i>
            ShuleXpert
        </a>
        <button type="button" class="navbar-toggler" id="mobileSidebarToggle" aria-label="Toggle sidebar">
            <span class="nav-dot"></span>
            <span class="nav-dot"></span>
            <span class="nav-dot"></span>
        </button>
    </div>

    <!-- Left Panel -->

  <!-- Left Panel -->
<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">

        <div class="navbar-header">
            <a class="mobile-brand d-md-none" href="{{ route('AdminDashboard') }}" style="display:none;">
                <i class="fa fa-graduation-cap"></i>
                ShuleXpert
            </a>
            <a href="{{ route('AdminDashboard') }}" class="admin-sidebar-title" style="text-decoration:none;">
                <img src="{{ asset('images/shuleXpert-favicon.png') }}" alt="ShuleXpert" class="admin-brand-logo">
            </a>
            <button class="navbar-toggler d-none" type="button" aria-hidden="true" tabindex="-1">
                <span class="nav-dot"></span>
                <span class="nav-dot"></span>
                <span class="nav-dot"></span>
            </button>
        </div>

        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">

                <!-- Sidebar Links -->
                <li class="sidebar-links-container">
                    <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                        <li><a href="{{ route('AdminDashboard') }}" class="nav-link"><i class="fa fa-building"></i> Dashboard</a></li>

                        <!-- User Management -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#userManagement" aria-expanded="false">
                                <i class="fa fa-users"></i> User Management <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="userManagement" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('school') }}" class="nav-link"><i class="fa fa-building"></i> School</a></li>
                                <li><a href="{{ route('manageTeachers') }}" class="nav-link"><i class="fa fa-users"></i> Teachers And Staff</a></li>
                                <li><a href="{{ route('manage_watchman') }}" class="nav-link"><i class="fa fa-shield"></i> Watchman</a></li>
                                <li><a href="{{ route('manage_student') }}" class="nav-link"><i class="fa fa-user"></i> Students</a></li>
                                <li><a href="{{ route('admin.pending_students.index') }}" class="nav-link"><i class="fa fa-user-plus"></i> Pending Registrations</a></li>
                                <li><a href="{{ route('manage_parents') }}" class="nav-link"><i class="fa fa-user"></i> parents/guardians</a></li>
                                <li><a href="{{ route('manage_sponsors') }}" class="nav-link"><i class="fa fa-handshake-o"></i> Sponsors</a></li>
                                <li><a href="{{ route('admin.school_visitors') }}" class="nav-link"><i class="fa fa-id-badge"></i> School Visitors</a></li>
                            </ul>
                        </li>

                        <!-- Academic Management -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#academicManagement" aria-expanded="false">
                                <i class="fa fa-graduation-cap"></i> Academic Management <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="academicManagement" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manageClasses') }}" class="nav-link"><i class="fa fa-columns"></i> Classes</a></li>
                                <li><a href="{{ route('school.online_admission.index') }}" class="nav-link"><i class="fa fa-wpforms"></i> Online Admission</a></li>
                                <li><a href="{{ route('manageSubjects') }}" class="nav-link"><i class="fa fa-bookmark"></i> Subjects</a></li>
                                <li><a href="{{ route('manage_fees') }}" class="nav-link"><i class="fa fa-money"></i> Fees Structure</a></li>
                                <li><a href="{{ route('view_payments') }}" class="nav-link"><i class="fa fa-credit-card"></i> Payments</a></li>
                                <li><a href="{{ route('manage_fees') }}" class="nav-link" onclick="setTimeout(() => { $('#openAgreementModalBtn_Exp').click() }, 500);"><i class="fa fa-handshake-o"></i> Fee Agreements</a></li>
                                <li><a href="{{ route('manage_library') }}" class="nav-link"><i class="fa fa-book"></i> Library</a></li>
                                <li><a href="{{ route('manageResults') }}" class="nav-link"><i class="fa fa-trophy"></i> Results</a></li>
                                <li><a href="{{ route('admin.add_results') }}" class="nav-link"><i class="fa fa-plus-circle"></i> Add Results</a></li>
                                <li><a href="{{ route('admin.subject_analysis') }}" class="nav-link"><i class="fa fa-line-chart"></i> Topic Analysis</a></li>
                                <li><a href="{{ route('manageExamination') }}" class="nav-link"><i class="fa fa-pencil-square-o"></i> Examinations</a></li>
                                <li><a href="{{ route('character_assessment') }}" class="nav-link"><i class="fa fa-heartbeat"></i> Character Assessment</a></li>
                            </ul>
                        </li>

                        <!-- Black Book -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#blackBookMenu" aria-expanded="false">
                                <i class="fa fa-book"></i> Black Book <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="blackBookMenu" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('black_book.report_student') }}" class="nav-link"><i class="fa fa-exclamation-triangle"></i> Report Student</a></li>
                                <li><a href="{{ route('manage_black_book') }}" class="nav-link"><i class="fa fa-list-alt"></i> Black Book Details</a></li>
                            </ul>
                        </li>

                        <!-- Attendance -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#attendanceMenu" aria-expanded="false">
                                <i class="fa fa-check-square-o"></i> Attendance <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="attendanceMenu" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manageAttendance') }}" class="nav-link"><i class="fa fa-user"></i> Student Attendance</a></li>
                                <li><a href="{{ route('manageTeacherStaffAttendance') }}" class="nav-link"><i class="fa fa-users"></i> Teacher and Staff Attendance</a></li>
                            </ul>
                        </li>

                        <!-- Student Identity Cards -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#studentIDCards" aria-expanded="false">
                                <i class="fa fa-id-card-o"></i> Student Identity Card <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="studentIDCards" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                @php
                                    $navSchoolID = Session::get('schoolID');
                                    $navClasses = $navSchoolID ? \App\Models\ClassModel::where('schoolID', $navSchoolID)->where('status', 'Active')->orderBy('class_name', 'asc')->get() : collect();
                                @endphp
                                @if($navClasses->isEmpty())
                                    <li><a href="#" class="nav-link text-muted small">No Classes Available</a></li>
                                @else
                                    @foreach($navClasses as $nc)
                                        @php
                                            $encryptedNavClassId = strtr(
                                                \Illuminate\Support\Facades\Crypt::encryptString((string) $nc->classID),
                                                ['+' => '-', '/' => '_']
                                            );
                                        @endphp
                                        <li><a href="{{ route('admin.student_id_cards', $encryptedNavClassId) }}" class="nav-link"><i class="fa fa-chevron-right small"></i> {{ $nc->class_name }}</a></li>
                                    @endforeach
                                @endif
                            </ul>
                        </li>

                        <!-- Planning & Scheduling -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#planningScheduling" aria-expanded="false">
                                <i class="fa fa-calendar"></i> Planning & Scheduling <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="planningScheduling" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('timeTable') }}" class="nav-link"><i class="fa fa-clock-o"></i> Time Tables</a></li>
                                <li><a href="{{ route('admin.calendar') }}" class="nav-link"><i class="fa fa-calendar"></i> Calendar</a></li>
                                <li><a href="{{ route('admin.schemeOfWork') }}" class="nav-link"><i class="fa fa-book"></i> Scheme of Work</a></li>
                                <li><a href="{{ route('admin.lessonPlans') }}" class="nav-link"><i class="fa fa-file-text"></i> Lesson Plans</a></li>
                                <li><a href="{{ route('admin.logbook') }}" class="nav-link"><i class="fa fa-book"></i> Logbook</a></li>
                                <li><a href="{{ route('admin.academicYears') }}" class="nav-link"><i class="fa fa-calendar-check-o"></i> Academic Years</a></li>
                            </ul>
                        </li>

                        <!-- Services -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#services" aria-expanded="false">
                                <i class="fa fa-cogs"></i> Services <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="services" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('taskManagement') }}" class="nav-link"><i class="fa fa-tasks"></i> Tasks</a></li>
                                <li><a href="{{ route('fingerprint_device_settings') }}" class="nav-link"><i class="fa fa-id-card"></i> Fingerprint</a></li>
                                <li><a href="{{ route('manage_accomodation') }}" class="nav-link"><i class="fa fa-bed"></i> Hostel</a></li>
                                <li><a href="{{ route('sms_notification') }}" class="nav-link"><i class="fa fa-bell"></i> SMS Information</a></li>
                                @if(\App\Models\School::where('schoolID', Session::get('schoolID'))->value('student_portal_enabled'))
                                <li><a href="{{ route('student_portal.materials') }}" class="nav-link"><i class="fa fa-graduation-cap"></i> Learning Portal</a></li>
                                @endif
                                <li><a href="{{ route('admin.printing_unit') }}" class="nav-link"><i class="fa fa-print"></i> Printing Unit</a></li>
                            </ul>
                        </li>

                        <!-- Inventory -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#inventoryMenu" aria-expanded="false">
                                <i class="fa fa-shopping-cart"></i> Inventory <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="inventoryMenu" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manage_store') }}" class="nav-link"><i class="fa fa-archive"></i> Manage Store</a></li>
                                <li><a href="{{ route('manage_store', ['open' => 'requisitions']) }}" class="nav-link"><i class="fa fa-hand-o-right"></i> Requisition Requests</a></li>
                            </ul>
                        </li>

                        <!-- School Resources -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#schoolResources" aria-expanded="false">
                                <i class="fa fa-cubes"></i> School Resources <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="schoolResources" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manage_incoming_resources') }}" class="nav-link"><i class="fa fa-level-down"></i> Incoming Resources</a></li>
                                <li><a href="{{ route('manage_outgoing_resources') }}" class="nav-link"><i class="fa fa-level-up"></i> Outgoing Resources</a></li>
                            </ul>
                        </li>

                        <!-- HR Operations -->
                        @php
                            $navSchoolID = Session::get('schoolID');
                            $permCount = 0;
                            $parentPermissionNoticeCount = 0;
                            if ($navSchoolID && \Illuminate\Support\Facades\Schema::hasTable('permission_approval_chains') && \Illuminate\Support\Facades\Schema::hasTable('permission_requests')) {
                                $permCount = \App\Models\PermissionRequest::where('schoolID', $navSchoolID)
                                    ->where('status', 'pending')
                                    ->where(function($q) {
                                        $q->where('current_approval_order', 0)
                                          ->orWhere('current_approval_order', '>', function($sub) {
                                              $sub->selectRaw('COALESCE(MAX(approval_order), 0)')
                                                  ->from('permission_approval_chains')
                                                  ->whereColumn('permission_approval_chains.schoolID', 'permission_requests.schoolID')
                                                  ->whereColumn('permission_approval_chains.permission_type', 'permission_requests.requester_type');
                                          });
                                    })->count();
                            }
                            if ($navSchoolID && \Illuminate\Support\Facades\Schema::hasTable('student_permissions')) {
                                try {
                                    $parentPermissionNoticeCount = \Illuminate\Support\Facades\DB::table('student_permissions')
                                        ->where('schoolID', $navSchoolID)
                                        ->where('requester_type', 'parent')
                                        ->where('status', 'pending')
                                        ->count();
                                } catch (\Throwable $e) {
                                    $parentPermissionNoticeCount = 0;
                                }
                            }
                            $hrPermissionNoticeCount = $permCount + $parentPermissionNoticeCount;
                        @endphp
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#hrOperations" aria-expanded="false">
                                <i class="fa fa-briefcase"></i> HR Operations
                                @if($hrPermissionNoticeCount > 0)
                                    <span class="badge badge-danger ml-1" style="font-size: 10px; border-radius: 50%;">{{ $hrPermissionNoticeCount }}</span>
                                @endif
                                <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="hrOperations" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li>
                                    <!-- <a href="{{ \Illuminate\Support\Facades\Route::has('admin.hr.leave') ? route('admin.hr.leave') : '#' }}" class="nav-link">
                                        <i class="fa fa-calendar"></i> Leave
                                    </a> -->
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.hr.permission') ? route('admin.hr.permission') : '#' }}" class="nav-link">
                                        <i class="fa fa-check-square-o"></i> Permission
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.hr.permissions.pending_approvals') }}" class="nav-link">
                                        <i class="fa fa-check-circle"></i> Permission Approvals
                                        @if($permCount > 0)
                                            <span class="badge badge-danger ml-1" style="font-size: 10px; border-radius: 50%;">{{ $permCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <!-- <a href="{{ \Illuminate\Support\Facades\Route::has('admin.hr.payroll') ? route('admin.hr.payroll') : '#' }}" class="nav-link">
                                        <i class="fa fa-money"></i> Payroll
                                    </a> -->
                                </li>
                            </ul>
                        </li>

                        <!-- Duties Book -->
                        @php
                            $pendingDutyReports = \App\Models\DailyDutyReport::where('schoolID', Session::get('schoolID'))
                                ->where('status', 'Sent')
                                ->count();
                        @endphp
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#dutiesBook" aria-expanded="false">
                                <i class="fa fa-book"></i> Duties Book
                                @if($pendingDutyReports > 0)
                                    <span class="badge badge-danger ml-1" style="font-size: 10px; border-radius: 50%;">{{ $pendingDutyReports }}</span>
                                @endif
                                <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="dutiesBook" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('admin.teacher_duties') }}" class="nav-link"><i class="fa fa-calendar-check-o"></i> Teacher on Duties</a></li>
                                <li><a href="{{ route('admin.teacher_duties.report') }}" class="nav-link"><i class="fa fa-file-text-o"></i> Report</a></li>
                            </ul>
                        </li>

                        <!-- Reports & Analytics -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#reportsAnalytics" aria-expanded="false">
                                <i class="fa fa-bar-chart"></i> Reports & Analytics <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="reportsAnalytics" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                @php
                                    $schoolID = Session::get('schoolID');
                                    $unreadSuggestions = 0;
                                    $unreadIncidents = 0;
                                    $unreadStaffSuggestions = 0;
                                    $unreadStaffIncidents = 0;
                                    if ($schoolID) {
                                        $unreadSuggestions = \App\Models\TeacherFeedback::where('schoolID', $schoolID)
                                            ->where('type', 'suggestion')
                                            ->where('is_read_by_admin', false)
                                            ->count();
                                        $unreadIncidents = \App\Models\TeacherFeedback::where('schoolID', $schoolID)
                                            ->where('type', 'incident')
                                            ->where('is_read_by_admin', false)
                                            ->count();
                                        $unreadStaffSuggestions = \App\Models\StaffFeedback::where('schoolID', $schoolID)
                                            ->where('type', 'suggestion')
                                            ->where('is_read_by_admin', false)
                                            ->count();
                                        $unreadStaffIncidents = \App\Models\StaffFeedback::where('schoolID', $schoolID)
                                            ->where('type', 'incident')
                                            ->where('is_read_by_admin', false)
                                            ->count();
                                    }
                                @endphp
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.suggestions') ? route('admin.suggestions') : '#' }}" class="nav-link">
                                        <i class="fa fa-lightbulb-o"></i> Suggestions
                                        @if($unreadSuggestions > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadSuggestions }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.incidents') ? route('admin.incidents') : '#' }}" class="nav-link">
                                        <i class="fa fa-exclamation-triangle"></i> Incidents
                                        @if($unreadIncidents > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadIncidents }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.staff.suggestions') ? route('admin.staff.suggestions') : '#' }}" class="nav-link">
                                        <i class="fa fa-lightbulb-o"></i> Staff Suggestions
                                        @if($unreadStaffSuggestions > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadStaffSuggestions }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.staff.incidents') ? route('admin.staff.incidents') : '#' }}" class="nav-link">
                                        <i class="fa fa-exclamation-triangle"></i> Staff Incidents
                                        @if($unreadStaffIncidents > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadStaffIncidents }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.performance') ? route('admin.performance') : '#' }}" class="nav-link">
                                        <i class="fa fa-line-chart"></i> Performance
                                    </a>
                                </li>
                                <li><a href="{{ route('admin.printing_unit') }}" class="nav-link"><i class="fa fa-print"></i> Printing Unit</a></li>
                            </ul>
                        </li>

                        <!-- Accountant Module -->
                        @php
                            $navSchoolID = Session::get('schoolID');
                            $navPendingExpenses = $navSchoolID ? \App\Models\Expense::where('schoolID', $navSchoolID)->where('status', 'Pending')->count() : 0;
                        @endphp
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#accountantModule" aria-expanded="false">
                                <i class="fa fa-calculator"></i> Accountant Module <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="accountantModule" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li>
                                    <a href="{{ route('accountant.expenses.index') }}" class="nav-link">
                                        <i class="fa fa-money"></i> School Expenses
                                        @if($navPendingExpenses > 0)
                                            <span class="badge badge-warning ml-1" style="font-size: 10px; border-radius: 50%;">{{ $navPendingExpenses }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li><a href="{{ route('accountant.income.index') }}" class="nav-link"><i class="fa fa-usd"></i> School Income</a></li>
                                <li><a href="{{ route('accountant.budget.index') }}" class="nav-link"><i class="fa fa-pie-chart"></i> School Budget</a></li>
                                <li><a href="{{ route('accountant.expense_categories.index') }}" class="nav-link"><i class="fa fa-tags"></i> Expense Categories</a></li>
                                <li><a href="{{ route('accountant.income_categories.index') }}" class="nav-link"><i class="fa fa-list-alt"></i> Income Categories</a></li>
                                <li><a href="{{ route('accountant.reports.index') }}" class="nav-link"><i class="fa fa-line-chart"></i> Financial Reports</a></li>
                            </ul>
                        </li>

                        <!-- Revenue and Expenses -->
                        <!-- <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#revenueExpenses" aria-expanded="false"> -->
                                <!-- <i class="fa fa-money"></i> Revenue and Expenses <i class="fa fa-chevron-down float-right"></i> -->
                            <!-- </a>
                            <ul id="revenueExpenses" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                {{-- <li><a href="{{ route('manage_revenue') }}" class="nav-link"><i class="fa fa-arrow-up"></i> Manage Revenue</a></li>
                                <li><a href="{{ route('manage_expenses') }}" class="nav-link"><i class="fa fa-arrow-down"></i> Manage Expenses</a></li> --}}
                            </ul>
                        </li> -->

                        <!-- Goal Management Logic -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#goalManagement" aria-expanded="false">
                                <i class="fa fa-bullseye"></i> Goal Management <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="goalManagement" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('admin.goals.create') }}" class="nav-link"><i class="fa fa-plus-circle"></i> Create Goal</a></li>
                                <li><a href="{{ route('admin.goals.index') }}" class="nav-link"><i class="fa fa-list"></i> Goal List</a></li>
                                <li><a href="{{ route('admin.goals.reports') }}" class="nav-link"><i class="fa fa-bar-chart"></i> Goal Reports</a></li>
                            </ul>
                        </li>

                        <!-- School Board -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#schoolBoardMenu" aria-expanded="false">
                                <i class="fa fa-university"></i> School Board <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="schoolBoardMenu" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('admin.school_board.dashboard') }}" class="nav-link"><i class="fa fa-dashboard"></i> Board Dashboard</a></li>
                                <li><a href="{{ route('admin.school_board.members') }}" class="nav-link"><i class="fa fa-users"></i> Board Members</a></li>
                                <li><a href="{{ route('admin.school_board.committees') }}" class="nav-link"><i class="fa fa-sitemap"></i> Committees</a></li>
                                <li><a href="{{ route('admin.school_board.meetings') }}" class="nav-link"><i class="fa fa-calendar"></i> Meetings</a></li>
                                <li><a href="{{ route('admin.school_board.minutes') }}" class="nav-link"><i class="fa fa-file-text-o"></i> Minutes</a></li>
                                <li><a href="{{ route('admin.school_board.resolutions') }}" class="nav-link"><i class="fa fa-gavel"></i> Resolutions</a></li>
                                <li><a href="{{ route('admin.school_board.action_items') }}" class="nav-link"><i class="fa fa-check-square-o"></i> Action Items</a></li>
                                <li><a href="{{ route('admin.school_board.documents') }}" class="nav-link"><i class="fa fa-folder-open-o"></i> Documents</a></li>
                                <li><a href="{{ route('admin.school_board.reports') }}" class="nav-link"><i class="fa fa-bar-chart"></i> Reports</a></li>
                            </ul>
                        </li>

                        <!-- Strategic Management (SGPM) -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#strategicManagement" aria-expanded="false">
                                <i class="fa fa-bullseye"></i> School Depertments <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="strategicManagement" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('sgpm.departments.index') }}" class="nav-link"><i class="fa fa-sitemap"></i> Departments</a></li>
                                <!-- <li><a href="{{ route('sgpm.goals.index') }}" class="nav-link"><i class="fa fa-flag-checkered) }}"></i> Strategic Goals</a></li>
                                <li><a href="{{ route('sgpm.objectives.index') }}" class="nav-link"><i class="fa fa-crosshairs"></i> Objectives</a></li>
                                <li><a href="{{ route('sgpm.performance.index') }}" class="nav-link"><i class="fa fa-line-chart"></i> Performance</a></li> -->
                            </ul>
                        </li>

                    </ul>
                </li>

            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</aside>

    <!-- Left Panel -->

    <!-- Right Panel -->

    <div id="right-panel" class="right-panel">

        <!-- Header-->
        <header id="header" class="header">

            <div class="header-menu">

                <div class="col-sm-7">
                    <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa fa-tasks header-icon-muted"></i></a>
                    <a href="{{ route('AdminDashboard') }}" class="admin-header-brand" title="ShuleXpert">
                        <img src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                    </a>
                    <div class="header-left">
                        <div class="form-inline"></div>

                        <div class="dropdown for-notification">
                            @php
                                $isAdmin = Session::get('user_type') === 'Admin';
                                $schoolID = Session::get('schoolID');
                                $lastSeen = Session::get('visitors_last_seen');
                                $lastSeenAt = $lastSeen ? \Carbon\Carbon::parse($lastSeen) : \Carbon\Carbon::createFromTimestamp(0);
                                $oaLastSeen = Session::get('online_admissions_last_seen');
                                $oaLastSeenAt = $oaLastSeen ? \Carbon\Carbon::parse($oaLastSeen) : \Carbon\Carbon::createFromTimestamp(0);
                                $permSeen = Session::get('parent_permissions_bell_seen');
                                $permSeenAt = $permSeen ? \Carbon\Carbon::parse($permSeen) : \Carbon\Carbon::createFromTimestamp(0);
                                $readVisitorIds = array_values(array_unique(array_map('intval', (array) Session::get('bell_read_visitors', []))));
                                $readAdmissionIds = array_values(array_unique(array_map('intval', (array) Session::get('bell_read_admissions', []))));
                                $readPermissionIds = array_values(array_unique(array_map('intval', (array) Session::get('bell_read_permissions', []))));
                                $newVisitorCount = 0;
                                $newOnlineAdmissionCount = 0;
                                $pendingParentPermissionCount = 0;
                                $recentVisitors = collect();
                                $recentOnlineAdmissions = collect();
                                $pendingParentPermissions = collect();
                                $permBellClient = [];

                                if ($isAdmin && $schoolID) {
                                    $visitorCountQuery = \Illuminate\Support\Facades\DB::table('school_visitors')
                                        ->where('schoolID', $schoolID)
                                        ->where('created_at', '>', $lastSeenAt);
                                    if ($readVisitorIds !== []) {
                                        $visitorCountQuery->whereNotIn('visitorID', $readVisitorIds);
                                    }
                                    $newVisitorCount = $visitorCountQuery->count();
                                    $recentVisitors = \Illuminate\Support\Facades\DB::table('school_visitors')
                                        ->where('schoolID', $schoolID)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(6)
                                        ->get();

                                    try {
                                        $oaQuery = \Illuminate\Support\Facades\DB::table('online_admissions')
                                            ->where('schoolID', $schoolID)
                                            ->where('status', 'Pending')
                                            ->where('created_at', '>', $oaLastSeenAt);
                                        if ($readAdmissionIds !== []) {
                                            $oaQuery->whereNotIn('id', $readAdmissionIds);
                                        }
                                        $newOnlineAdmissionCount = $oaQuery->count();
                                        $recentOnlineAdmissions = \Illuminate\Support\Facades\DB::table('online_admissions')
                                            ->where('schoolID', $schoolID)
                                            ->where('status', 'Pending')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(6)
                                            ->get();
                                    } catch (\Throwable $e) {
                                        $newOnlineAdmissionCount = 0;
                                        $recentOnlineAdmissions = collect();
                                    }

                                    try {
                                        $permQuery = \Illuminate\Support\Facades\DB::table('student_permissions as sp')
                                            ->leftJoin('students as s', 'sp.student_id', '=', 's.studentID')
                                            ->leftJoin('parents as p', 's.parentID', '=', 'p.parentID')
                                            ->where('sp.schoolID', $schoolID)
                                            ->where('sp.requester_type', 'parent')
                                            ->where('sp.status', 'pending');
                                        $permCountQuery = (clone $permQuery)->where('sp.created_at', '>', $permSeenAt);
                                        if ($readPermissionIds !== []) {
                                            $permCountQuery->whereNotIn('sp.id', $readPermissionIds);
                                        }
                                        $pendingParentPermissionCount = $permCountQuery->count();
                                        $pendingParentPermissions = (clone $permQuery)
                                            ->orderByDesc('sp.created_at')
                                            ->limit(8)
                                            ->get([
                                                'sp.id',
                                                'sp.reason_type',
                                                'sp.reason_description',
                                                'sp.days_count',
                                                'sp.start_date',
                                                'sp.end_date',
                                                'sp.attachment',
                                                'sp.created_at',
                                                's.first_name as student_first_name',
                                                's.last_name as student_last_name',
                                                'p.first_name as parent_first_name',
                                                'p.last_name as parent_last_name',
                                            ]);
                                    } catch (\Throwable $e) {
                                        $pendingParentPermissionCount = 0;
                                        $pendingParentPermissions = collect();
                                    }
                                }
                                $headerNotificationCount = $newVisitorCount + $newOnlineAdmissionCount + $pendingParentPermissionCount;
                            @endphp
                            <button class="btn btn-secondary dropdown-toggle position-relative" type="button" id="notification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell-o header-icon-muted"></i>
                                @if($headerNotificationCount > 0)
                                    <span class="count bg-danger notification-count" id="visitorNotificationCount">{{ $headerNotificationCount }}</span>
                                @endif
                            </button>
                            <div class="dropdown-menu" id="visitorNotificationMenu" aria-labelledby="notification">
                                <div class="perm-bell-head">
                                    <p class="red" id="visitorNotificationHeader">
                                        Notifications: {{ $headerNotificationCount }}
                                        @if($pendingParentPermissionCount > 0)
                                            <small class="d-block text-muted" style="font-weight:500;">Permission requests: {{ $pendingParentPermissionCount }}</small>
                                        @endif
                                        @if($newOnlineAdmissionCount > 0)
                                            <small class="d-block text-muted" style="font-weight:500;">Online applications: {{ $newOnlineAdmissionCount }}</small>
                                        @endif
                                    </p>
                                    <button type="button" id="markAllNotificationsRead" @if($headerNotificationCount < 1) style="display:none;" @endif>Mark all as read</button>
                                </div>
                                <div id="visitorNotificationList">
                                    @php
                                        $hasBellItems = $pendingParentPermissions->isNotEmpty()
                                            || $recentOnlineAdmissions->isNotEmpty()
                                            || $recentVisitors->isNotEmpty();
                                    @endphp
                                    @if(! $hasBellItems)
                                        <div class="bell-note is-read">
                                            <div class="bell-note-main">
                                                <div class="bell-note-title">No notifications.</div>
                                            </div>
                                        </div>
                                    @else
                                        @foreach($pendingParentPermissions as $permNote)
                                            @php
                                                $permCreated = $permNote->created_at ? \Carbon\Carbon::parse($permNote->created_at) : null;
                                                $permUnread = $permCreated
                                                    && $permCreated->gt($permSeenAt)
                                                    && ! in_array((int) $permNote->id, $readPermissionIds, true);
                                                $permTimeLabel = $permCreated
                                                    ? ($permCreated->isToday() ? $permCreated->diffForHumans() : $permCreated->format('d M Y'))
                                                    : '';
                                                $permStudent = trim(($permNote->student_first_name ?? '') . ' ' . ($permNote->student_last_name ?? '')) ?: 'Student';
                                                $permParent = trim(($permNote->parent_first_name ?? '') . ' ' . ($permNote->parent_last_name ?? '')) ?: 'Parent';
                                                $permReason = strtolower((string) ($permNote->reason_type ?? ''));
                                                $permReasonLabel = in_array($permReason, ['sick', 'medical'], true) ? 'Sick' : 'Permission';
                                                $permStart = $permNote->start_date ? \Carbon\Carbon::parse($permNote->start_date)->format('j M Y') : '';
                                                $permEnd = $permNote->end_date ? \Carbon\Carbon::parse($permNote->end_date)->format('j M Y') : '';
                                                $permAttachment = '';
                                                if (!empty($permNote->attachment)) {
                                                    $permAttachment = str_contains((string) $permNote->attachment, '/')
                                                        ? asset($permNote->attachment)
                                                        : asset('uploads/permissions/' . $permNote->attachment);
                                                }
                                                $permPayload = [
                                                    'id' => (int) $permNote->id,
                                                    'student_name' => $permStudent,
                                                    'parent_name' => $permParent,
                                                    'reason_label' => $permReasonLabel,
                                                    'reason_description' => (string) ($permNote->reason_description ?? ''),
                                                    'days_count' => (int) ($permNote->days_count ?? 0),
                                                    'start_date' => $permStart,
                                                    'end_date' => $permEnd,
                                                    'time_label' => $permTimeLabel,
                                                    'attachment_url' => $permAttachment,
                                                    'is_unread' => $permUnread,
                                                ];
                                                $permBellClient[(int) $permNote->id] = $permPayload;
                                                $permOpenUrl = route('admin.hr.permission', ['tab' => 'student', 'open' => 'sp'.(int) $permNote->id]);
                                            @endphp
                                            <div class="bell-note {{ $permUnread ? 'is-unread' : 'is-read' }}" data-href="{{ $permOpenUrl }}" data-note-type="permission" data-note-id="{{ (int) $permNote->id }}">
                                                <div class="bell-note-icon"><i class="fa fa-calendar-check-o"></i></div>
                                                <div class="bell-note-main">
                                                    <div class="bell-note-top">
                                                        <span class="bell-note-title">Permission request</span>
                                                        <span class="bell-note-time">{{ $permTimeLabel }}</span>
                                                    </div>
                                                    <div class="bell-note-meta">{{ $permStudent }} | {{ $permReasonLabel }} | {{ $permStart }} - {{ $permEnd }}</div>
                                                    <div class="bell-note-status">
                                                        <span class="bell-note-flag">{{ $permUnread ? 'Unread' : 'Read' }}</span>
                                                        @if($permUnread)
                                                            <button type="button" class="bell-mark-read" data-note-type="permission" data-note-id="{{ (int) $permNote->id }}">Mark as read</button>
                                                        @endif
                                                    </div>
                                                    <div class="perm-bell-actions">
                                                        <button type="button" class="bell-act bell-act-view" data-perm-id="{{ (int) $permNote->id }}">View</button>
                                                        <button type="button" class="bell-act bell-act-accept bell-act-decide" data-perm-id="{{ (int) $permNote->id }}" data-action="approve">Accept</button>
                                                        <button type="button" class="bell-act bell-act-reject bell-act-decide" data-perm-id="{{ (int) $permNote->id }}" data-action="reject">Reject</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @foreach($recentOnlineAdmissions as $app)
                                            @php
                                                $created = \Carbon\Carbon::parse($app->created_at);
                                                $appUnread = $created->gt($oaLastSeenAt) && ! in_array((int) $app->id, $readAdmissionIds, true);
                                                $timeLabel = $created->isToday()
                                                    ? $created->diffForHumans()
                                                    : $created->format('d M Y');
                                                $studentLabel = trim(($app->student_first_name ?? '') . ' ' . ($app->student_last_name ?? ''));
                                            @endphp
                                            <div class="bell-note {{ $appUnread ? 'is-unread' : 'is-read' }}" data-href="{{ route('school.online_admission.index') }}" data-note-type="admission" data-note-id="{{ (int) $app->id }}">
                                                <div class="bell-note-icon"><i class="fa fa-wpforms"></i></div>
                                                <div class="bell-note-main">
                                                    <div class="bell-note-top">
                                                        <span class="bell-note-title"><a href="{{ route('school.online_admission.index') }}">Online application</a></span>
                                                        <span class="bell-note-time">{{ $timeLabel }}</span>
                                                    </div>
                                                    <div class="bell-note-meta">{{ $studentLabel }} | {{ $app->application_number ?? '' }}</div>
                                                    <div class="bell-note-status">
                                                        <span class="bell-note-flag">{{ $appUnread ? 'Unread' : 'Read' }}</span>
                                                        @if($appUnread)
                                                            <button type="button" class="bell-mark-read" data-note-type="admission" data-note-id="{{ (int) $app->id }}">Mark as read</button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @foreach($recentVisitors as $visitor)
                                            @php
                                                $created = \Carbon\Carbon::parse($visitor->created_at);
                                                $visitorUnread = $created->gt($lastSeenAt) && ! in_array((int) $visitor->visitorID, $readVisitorIds, true);
                                                $timeLabel = $created->isToday()
                                                    ? $created->diffForHumans()
                                                    : $created->format('d M Y');
                                            @endphp
                                            <div class="bell-note {{ $visitorUnread ? 'is-unread' : 'is-read' }}" data-href="{{ route('admin.school_visitors') }}" data-note-type="visitor" data-note-id="{{ (int) $visitor->visitorID }}">
                                                <div class="bell-note-icon"><i class="fa fa-user"></i></div>
                                                <div class="bell-note-main">
                                                    <div class="bell-note-top">
                                                        <span class="bell-note-title"><a href="{{ route('admin.school_visitors') }}">{{ $visitor->name }}</a></span>
                                                        <span class="bell-note-time">{{ $timeLabel }}</span>
                                                    </div>
                                                    <div class="bell-note-meta">{{ $visitor->reason ?? 'N/A' }} | {{ $visitor->contact ?? 'N/A' }}</div>
                                                    <div class="bell-note-status">
                                                        <span class="bell-note-flag">{{ $visitorUnread ? 'Unread' : 'Read' }}</span>
                                                        @if($visitorUnread)
                                                            <button type="button" class="bell-mark-read" data-note-type="visitor" data-note-id="{{ (int) $visitor->visitorID }}">Mark as read</button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <script>window.permBellById = @json($permBellClient ?? []);</script>

                        </div>

                        @include('includes.sgpm_notifications')
                        @include('includes.goal_notifications')

                        <div class="dropdown for-message">
                            @php
                                $isAdmin = Session::get('user_type') === 'Admin';
                                $schoolID = Session::get('schoolID');
                                $examPaperNotificationCount = 0;
                                $examPaperNotifications = collect();

                                if ($isAdmin && $schoolID) {
                                    $teacherHasImage = \Illuminate\Support\Facades\Schema::hasColumn('teachers', 'image');
                                    $teacherHasGender = \Illuminate\Support\Facades\Schema::hasColumn('teachers', 'gender');

                                    // Count only notifications for papers that HAVE content
                                    $examPaperNotificationCount = \Illuminate\Support\Facades\DB::table('exam_paper_notifications')
                                        ->join('exam_papers', 'exam_paper_notifications.exam_paperID', '=', 'exam_papers.exam_paperID')
                                        ->where('exam_paper_notifications.schoolID', $schoolID)
                                        ->where('exam_paper_notifications.is_read', 0)
                                        ->where(function($query) {
                                            $query->whereNotNull('exam_papers.file_path')
                                                  ->orWhereNotNull('exam_papers.question_content');
                                        })
                                        ->count();

                                    $examPaperNotificationsQuery = \Illuminate\Support\Facades\DB::table('exam_paper_notifications')
                                        ->join('exam_papers', 'exam_paper_notifications.exam_paperID', '=', 'exam_papers.exam_paperID')
                                        ->join('examinations', 'exam_papers.examID', '=', 'examinations.examID')
                                        ->join('class_subjects', 'exam_papers.class_subjectID', '=', 'class_subjects.class_subjectID')
                                        ->join('school_subjects', 'class_subjects.subjectID', '=', 'school_subjects.subjectID')
                                        ->leftJoin('subclasses', 'class_subjects.subclassID', '=', 'subclasses.subclassID')
                                        ->leftJoin('classes', 'class_subjects.classID', '=', 'classes.classID')
                                        ->join('teachers', 'exam_paper_notifications.teacherID', '=', 'teachers.id')
                                        ->where('exam_paper_notifications.schoolID', $schoolID)
                                        ->where(function($query) {
                                            $query->whereNotNull('exam_papers.file_path')
                                                  ->orWhereNotNull('exam_papers.question_content');
                                        })
                                        ->orderBy('exam_paper_notifications.created_at', 'desc')
                                        ->limit(5);

                                    $selectColumns = [
                                            'exam_paper_notifications.exam_paper_notificationID',
                                            'exam_paper_notifications.created_at',
                                            'exam_paper_notifications.is_read',
                                            'examinations.exam_name',
                                            'school_subjects.subject_name',
                                            'classes.class_name',
                                            'subclasses.subclass_name',
                                            'teachers.first_name',
                                            'teachers.last_name',
                                        ];
                                    if ($teacherHasImage) {
                                        $selectColumns[] = 'teachers.image';
                                    }
                                    if ($teacherHasGender) {
                                        $selectColumns[] = 'teachers.gender';
                                    }

                                    $examPaperNotifications = $examPaperNotificationsQuery->get($selectColumns);
                                }
                            @endphp
                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                id="message"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti-email header-icon-muted"></i>
                                @if($examPaperNotificationCount > 0)
                                    <span class="count bg-primary" id="examPaperNotificationCount">{{ $examPaperNotificationCount }}</span>
                                @endif
                            </button>
                            <div class="dropdown-menu" aria-labelledby="message" id="examPaperNotificationsMenu">
                                <p class="red">Exam Paper Notifications</p>
                                <div id="examPaperNotificationList">
                                    @if($examPaperNotifications->isEmpty())
                                        <span class="dropdown-item text-muted">No new exam paper notifications.</span>
                                    @else
                                        @foreach($examPaperNotifications as $note)
                                            @php
                                                $created = \Carbon\Carbon::parse($note->created_at);
                                                $timeLabel = $created->isToday() ? $created->diffForHumans() : $created->format('d M Y');
                                                $teacherName = trim(($note->first_name ?? '') . ' ' . ($note->last_name ?? ''));
                                                $classDisplay = trim(($note->class_name ?? '') . ' ' . ($note->subclass_name ?? ''));
                                                $teacherGender = strtolower($note->gender ?? '');
                                                $photoUrl = \App\Support\UserImageUrl::forGender(
                                                    $note->image ?? null,
                                                    ($teacherGender === 'female') ? 'Female' : 'Male'
                                                );
                                                $isUnread = isset($note->is_read) ? !((int) $note->is_read === 1) : false;
                                            @endphp
                                            <a class="dropdown-item media" href="{{ route('manageExamination') }}">
                                                <span class="photo media-left"><img alt="avatar" src="{{ $photoUrl }}" onerror="this.onerror=null;this.src='{{ asset('images/male.png') }}';"></span>
                                                <span class="message media-body">
                                                    <span class="name float-left">{{ $teacherName }}</span>
                                                    @if($isUnread)
                                                        <span class="badge badge-danger ml-2">New</span>
                                                    @endif
                                                    <span class="time float-right">{{ $timeLabel }}</span>
                                                    <p>New exam paper: {{ $note->exam_name }} | {{ $note->subject_name }} | {{ $classDisplay }}</p>
                                                </span>
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                                <a class="dropdown-item text-center" href="{{ route('manageExamination') }}">View all exam papers</a>
                            </div>

                        </div>

                        @include('includes.header_academic_year_selector')
                    </div>
                </div>

                <div class="header-mobile-divider d-lg-none" aria-hidden="true"></div>

                <div class="col-sm-5">
                    <div class="float-right d-flex align-items-center justify-content-end" style="gap:12px;">
                    @include('includes.theme_toggle')
                    <div class="user-area dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @php
                                $navSchoolID = Session::get('schoolID');
                                $navSchool = $navSchoolID ? \App\Models\School::where('schoolID', $navSchoolID)->first() : null;
                                $navSchoolName = $navSchool ? $navSchool->school_name : 'School';
                                $navLogo = null;
                                if ($navSchool && !empty($navSchool->school_logo)) {
                                    $logoRel = ltrim(str_replace('\\', '/', $navSchool->school_logo), '/');
                                    $logoCandidates = [
                                        public_path($logoRel),
                                        public_path('schoolLogos/' . basename($logoRel)),
                                        dirname(base_path()) . '/public_html/' . $logoRel,
                                    ];
                                    foreach ($logoCandidates as $logoPath) {
                                        if (@is_file($logoPath)) {
                                            $navLogo = asset($logoRel);
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            @if($navLogo)
                                <img class="user-avatar rounded-circle" src="{{ $navLogo }}" alt="{{ $navSchoolName }}" style="width: 40px; height: 40px; object-fit: contain; background: #fff;" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                                <span class="user-avatar rounded-circle d-none align-items-center justify-content-center" style="width: 40px; height: 40px; background: #ffffff; border: 2px solid #e0e0e0;">
                                    <i class="fa fa-graduation-cap" aria-hidden="true" style="color: #940000;"></i>
                                </span>
                            @else
                                <span class="user-avatar rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #ffffff; border: 2px solid #e0e0e0;">
                                    <i class="fa fa-graduation-cap" aria-hidden="true" style="color: #940000;"></i>
                                </span>
                            @endif
                        </a>

                        <div class="user-menu dropdown-menu">
                            @php
                                $navTeacherID = Session::get('teacherID');
                                $navTeacher = $navTeacherID ? \App\Models\Teacher::find($navTeacherID) : null;
                            @endphp
                            @if($navTeacher)
                                <a class="nav-link view-teacher-btn" href="#" data-teacher-id="{{ $navTeacher->id }}"><i class="fa fa-user"></i> View Profile</a>
                            @endif
                            <a class="nav-link" href="{{ route('admin.change_password') }}"><i class="fa fa-lock"></i> Change Password</a>

                            <!-- <a class="nav-link" href="#"><i class="fa fa-user"></i> Notifications <span class="count">13</span></a>

                            <a class="nav-link" href="#"><i class="fa fa-cog"></i> Settings</a> -->

                            <a class="nav-link" href="{{ route('logout') }}"><i class="fa fa-power-off"></i> Logout</a>
                        </div>
                    </div>
                    </div>

                </div>
            </div>

        </header><!-- /header -->
        <!-- Header-->

        @php
            $systemAlerts = collect();
            try {
                $navSchoolID = \Illuminate\Support\Facades\Session::get('schoolID');
                if ($navSchoolID) {
                    $systemAlerts = \App\Models\SystemAlert::where('schoolID', $navSchoolID)
                        ->where('target_user_type', 'Admin')
                        ->where('is_active', 1)
                        ->orderBy('id', 'desc')
                        ->get();
                }
            } catch (\Throwable $e) {
                $systemAlerts = collect();
            }

            $alertIcons = [
                'info' => 'fa-info-circle',
                'warning' => 'fa-exclamation-triangle',
                'success' => 'fa-check-circle',
                'danger' => 'fa-times-circle',
            ];
        @endphp

        @if($systemAlerts->count() > 0)
            <div class="px-3 pt-2">
                @foreach($systemAlerts as $a)
                    @php
                        $type = $a->alert_type ?: 'info';
                        $icon = $alertIcons[$type] ?? 'fa-info-circle';
                        $bg = $a->bg_color;
                        $tc = $a->text_color;
                        $w = $a->width;
                        $style = '';
                        if ($bg) $style .= 'background-color:' . $bg . ' !important;';
                        if ($tc) $style .= 'color:' . $tc . ' !important;';
                        if (!$bg && !$tc && in_array($type, ['danger', 'success', 'info'], true)) $style .= 'color:#ffffff !important;';
                        if ($a->is_bold) $style .= 'font-weight:700;';
                        if ($a->font_size) $style .= 'font-size:' . $a->font_size . ';';
                        if ($w) $style .= 'width:' . $w . ';';
                    @endphp
                    <div class="alert alert-{{ $type }}" role="alert" style="margin-bottom: 8px; {!! $style !!}">
                        @if($a->is_marquee)
                            <marquee behavior="scroll" direction="left" scrollamount="6" style="white-space:nowrap; width:100%;">{{ $a->message }}</marquee>
                        @else
                            <i class="fa {{ $icon }}" style="margin-right: 8px;"></i>
                            {{ $a->message }}
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

<div id="bellOverlay">
    <div id="bellOverlayCard">
        <div id="bellOverlayHead">
            <div id="bellOverlayTitle"></div>
            <button type="button" id="bellOverlayClose" aria-label="Close">&times;</button>
        </div>
        <div id="bellOverlayBody"></div>
        <div id="bellOverlayLoading" aria-live="polite">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <span>Inaendelea...</span>
        </div>
    </div>
</div>

<script>
// Ensure $ inside this block refers to jQuery (pass window.jQuery into IIFE)
(function($){
// Function to initialize menu and dropdowns
function initializeMenuDropdowns() {
    // Wait for jQuery to be available and for Bootstrap collapse plugin
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn === 'undefined' || typeof window.jQuery.fn.collapse === 'undefined') {
        setTimeout(initializeMenuDropdowns, 100);
        return;
    }

    // Use a local $ bound to window.jQuery to avoid conflicts
    var $ = window.jQuery;

    // Get all sidebar menu links
    const menuLinks = document.querySelectorAll('#left-panel .nav-link');

    // Remove active class from all links
    function removeActiveClass() {
        menuLinks.forEach(link => {
            link.classList.remove('menu-active');
        });
    }

    // Reset all dropdowns to closed state first
    function resetAllDropdowns() {
        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
            const $collapse = $(collapse);
            if ($collapse.hasClass('show') && typeof $collapse.collapse === 'function') {
                $collapse.collapse('hide');
            }
            const toggle = collapse.previousElementSibling;
            if (toggle && toggle.classList.contains('dropdown-toggle')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Initialize all collapse elements and close them by default
    document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
        // Initialize collapse if not already initialized and if collapse function is available
        const $collapse = $(collapse);
        if (!$collapse.data('bs.collapse') && typeof $collapse.collapse === 'function') {
            $collapse.collapse({
                toggle: false
            });
        }
        // Ensure all dropdowns are closed on page load
        if ($collapse.hasClass('show')) {
            if (typeof $collapse.collapse === 'function') {
                $collapse.collapse('hide');
            }
        }
        // Set aria-expanded to false
        const toggle = collapse.previousElementSibling;
        if (toggle && toggle.classList.contains('dropdown-toggle')) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Remove all existing event listeners by cloning and replacing
    document.querySelectorAll('#left-panel .dropdown-toggle').forEach(toggle => {
        const newToggle = toggle.cloneNode(true);
        toggle.parentNode.replaceChild(newToggle, toggle);
    });

    // Add click event listener to each link
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Handle dropdown toggle
            if (this.classList.contains('dropdown-toggle')) {
                e.preventDefault();
                e.stopPropagation();

                const targetId = this.getAttribute('data-target');
                const target = document.querySelector(targetId);

                if (!target) return;

                const $target = $(target);
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Toggle current dropdown
                if (isExpanded) {
                    // Close current dropdown
                    if (typeof $target.collapse === 'function') {
                        $target.collapse('hide');
                    }
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    // Close all other dropdowns first
                    document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
                        const $collapse = $(collapse);
                        if (collapse.id !== targetId.replace('#', '') && $collapse.hasClass('show') && typeof $collapse.collapse === 'function') {
                            $collapse.collapse('hide');
                            const otherToggle = collapse.previousElementSibling;
                            if (otherToggle && otherToggle.classList.contains('dropdown-toggle')) {
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    // Open current dropdown after a small delay to ensure others are closed
                    setTimeout(() => {
                        if (typeof $target.collapse === 'function') {
                            $target.collapse('show');
                        }
                        this.setAttribute('aria-expanded', 'true');
                    }, 50);
                }

                return false;
            }

            // Don't prevent default if it's not a hash link
            if (this.getAttribute('href') !== '#') {
                // Remove active class from all links
                removeActiveClass();
                // Add active class to clicked link
                this.classList.add('menu-active');
            }
        });
    });

    // Set active based on current URL on page load
    const currentUrl = window.location.href;
    const currentPath = window.location.pathname;

    // Function to check if URL matches
    function urlMatches(linkHref, currentUrl, currentPath) {
        if (!linkHref || linkHref === '#') return false;

        // Remove query strings and fragments for comparison
        let linkPath = linkHref.split('?')[0].split('#')[0].replace(/\/$/, ''); // Remove trailing slash
        let currentPathClean = currentPath.split('?')[0].split('#')[0].replace(/\/$/, '');
        let currentUrlClean = currentUrl.split('?')[0].split('#')[0].replace(/\/$/, '');

        // Normalize paths
        linkPath = linkPath.toLowerCase();
        currentPathClean = currentPathClean.toLowerCase();
        currentUrlClean = currentUrlClean.toLowerCase();

        // Check exact match
        if (currentPathClean === linkPath || currentUrlClean === linkPath) {
            return true;
        }

        // Check if current URL/path ends with link path (for nested routes)
        if (currentPathClean.endsWith(linkPath) || currentUrlClean.endsWith(linkPath)) {
            return true;
        }

        // Check if current URL/path contains link path (for routes with parameters)
        if (linkPath && (currentPathClean.includes(linkPath) || currentUrlClean.includes(linkPath))) {
            return true;
        }

        return false;
    }

    // First reset all dropdowns - close ALL dropdowns
    resetAllDropdowns();

    // Force close all dropdowns again after a delay
    setTimeout(() => {
        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
            const $collapse = $(collapse);
            if (typeof $collapse.collapse === 'function') {
                $collapse.removeClass('show').collapse('hide');
            }
            const toggle = collapse.previousElementSibling;
            if (toggle && toggle.classList.contains('dropdown-toggle')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }, 100);

    // Then set active link and expand ONLY the parent dropdown of active link
    setTimeout(() => {
        let activeLinkFound = false;

        menuLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (urlMatches(linkHref, currentUrl, currentPath)) {
                link.classList.add('menu-active');
                activeLinkFound = true;

                // If link is in a submenu, expand the parent dropdown and make it active
                const submenu = link.closest('.submenu');
                if (submenu) {
                    const $submenu = $(submenu);
                    // Find the parent dropdown toggle (it should be before the submenu)
                    const parentLi = submenu.closest('li.dropdown-nav-item');
                    if (parentLi) {
                        const dropdownToggle = parentLi.querySelector('.dropdown-toggle');
                        if (dropdownToggle) {
                            // Show the dropdown
                            if (typeof $submenu.collapse === 'function') {
                                $submenu.collapse('show');
                            }
                            dropdownToggle.setAttribute('aria-expanded', 'true');

                            // Add active class to parent toggle
                            dropdownToggle.classList.add('menu-active');
                        }
                    } else {
                        // Fallback to previous method
                        const dropdownToggle = submenu.previousElementSibling;
                        if (dropdownToggle && dropdownToggle.classList.contains('dropdown-toggle')) {
                            if (typeof $submenu.collapse === 'function') {
                                $submenu.collapse('show');
                            }
                            dropdownToggle.setAttribute('aria-expanded', 'true');
                            dropdownToggle.classList.add('menu-active');
                        }
                    }
                }
            }
        });

        // If no active link found, check if we need to highlight parent menu
        // BUT ONLY expand ONE dropdown - the one containing the active link
        if (!activeLinkFound) {
            // Check if any route path matches partially (for nested routes)
            menuLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                if (linkHref && linkHref !== '#') {
                    const linkPath = linkHref.split('?')[0].split('#')[0];
                    // Only match if path is significant (not just "/")
                    if (linkPath && linkPath !== '/' && linkPath.length > 1) {
                        if (currentPath.includes(linkPath) || currentUrl.includes(linkPath)) {
                            link.classList.add('menu-active');
                            activeLinkFound = true;

                            // Expand ONLY the parent dropdown of this active link
                            const submenu = link.closest('.submenu');
                            if (submenu) {
                                const $submenu = $(submenu);
                                const parentLi = submenu.closest('li.dropdown-nav-item');
                                if (parentLi) {
                                    const dropdownToggle = parentLi.querySelector('.dropdown-toggle');
                                    if (dropdownToggle) {
                                        // Close all other dropdowns first
                                        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(c => {
                                            if (c !== submenu) {
                                                const $c = $(c);
                                                if (typeof $c.collapse === 'function') {
                                                    $c.removeClass('show').collapse('hide');
                                                }
                                            }
                                        });
                                        // Then open only this one
                                        if (typeof $submenu.collapse === 'function') {
                                            $submenu.collapse('show');
                                        }
                                        dropdownToggle.setAttribute('aria-expanded', 'true');
                                        dropdownToggle.classList.add('menu-active');
                                    }
                                } else {
                                    const dropdownToggle = submenu.previousElementSibling;
                                    if (dropdownToggle && dropdownToggle.classList.contains('dropdown-toggle')) {
                                        // Close all other dropdowns first
                                        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(c => {
                                            if (c !== submenu) {
                                                const $c = $(c);
                                                if (typeof $c.collapse === 'function') {
                                                    $c.removeClass('show').collapse('hide');
                                                }
                                            }
                                        });
                                        // Then open only this one
                                        if (typeof $submenu.collapse === 'function') {
                                            $submenu.collapse('show');
                                        }
                                        dropdownToggle.setAttribute('aria-expanded', 'true');
                                        dropdownToggle.classList.add('menu-active');
                                    }
                                }
                            }
                            // Stop after finding first match
                            return false;
                        }
                    }
                }
            });
        }

        // Also check for parent dropdowns that might contain active children
        if (activeLinkFound) {
            // Ensure all parent dropdowns of active links are open and highlighted
            document.querySelectorAll('#left-panel .nav-link.menu-active').forEach(activeLink => {
                const submenu = activeLink.closest('.submenu');
                if (submenu) {
                    const parentLi = submenu.closest('li.dropdown-nav-item');
                    if (parentLi) {
                        const dropdownToggle = parentLi.querySelector('.dropdown-toggle');
                        if (dropdownToggle && !dropdownToggle.classList.contains('menu-active')) {
                            dropdownToggle.classList.add('menu-active');
                        }
                    }
                }
            });
        }
    }, 300);

    // Initialize Bootstrap collapse events for dropdowns
    $('.dropdown-nav-item .collapse').off('show.bs.collapse hide.bs.collapse').on('show.bs.collapse', function() {
        const toggle = $(this).prev('.dropdown-toggle');
        if (toggle.length) {
            toggle.attr('aria-expanded', 'true');
        }
    }).on('hide.bs.collapse', function() {
        const toggle = $(this).prev('.dropdown-toggle');
        if (toggle.length) {
            toggle.attr('aria-expanded', 'false');
        }
    });

    // Also listen for when links are clicked that navigate to new pages
    menuLinks.forEach(link => {
        if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
            link.addEventListener('click', function() {
                // Close all dropdowns when navigating
                setTimeout(() => {
                    resetAllDropdowns();
                }, 100);
            });
        }
    });
}

function initializeVisitorNotifications() {
    if (typeof window.jQuery === 'undefined') {
        setTimeout(initializeVisitorNotifications, 100);
        return;
    }
    var $ = window.jQuery;
    $('#visitorNotificationList').off('scroll.bellMore').on('scroll.bellMore', function () {
        if (!window.bellFeedHasMore || window.bellFeedLoading) {
            return;
        }
        if (this.scrollTop + this.clientHeight >= this.scrollHeight - 48) {
            fetchRecentVisitors((window.bellFeedPage || 1) + 1, true);
        }
    });

    $(document).off('click.markAllNotes', '#markAllNotificationsRead')
        .on('click.markAllNotes', '#markAllNotificationsRead', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            $btn.prop('disabled', true);
            $.ajax({
                url: '{{ route("admin.mark_visitor_notifications_read") }}',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                complete: function () {
                    $btn.prop('disabled', false);
                    updateVisitorNotificationCount();
                }
            });
        });

    $(document).off('click.bellOverlay').on('click.bellOverlay', '#bellOverlayClose', function () {
        closeBellOverlay();
    }).on('click.bellOverlay', '#bellOverlay', function (e) {
        if (e.target && e.target.id === 'bellOverlay') {
            closeBellOverlay();
        }
    });

    if (!window.permBellClickBound) {
        window.permBellClickBound = true;
        document.addEventListener('click', function (e) {
            var note = e.target.closest('.bell-note[data-href]');
            if (note && note.closest('#visitorNotificationMenu') && !e.target.closest('button, .bell-act, .bell-mark-read')) {
                var href = note.getAttribute('data-href');
                if (href) {
                    e.preventDefault();
                    e.stopPropagation();
                    var noteType = note.getAttribute('data-note-type');
                    var noteId = note.getAttribute('data-note-id');
                    var openNote = function () { window.location.href = href; };
                    if (!noteType || !noteId || note.classList.contains('is-read')) {
                        openNote();
                        return;
                    }
                    $.ajax({
                        url: '{{ route("admin.mark_notification_read") }}',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            type: noteType,
                            id: noteId
                        },
                        complete: openNote
                    });
                    return;
                }
            }
            var readButton = e.target.closest('.bell-mark-read');
            if (readButton && readButton.closest('#visitorNotificationMenu')) {
                e.preventDefault();
                e.stopPropagation();
                readButton.disabled = true;
                $.ajax({
                    url: '{{ route("admin.mark_notification_read") }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        type: readButton.getAttribute('data-note-type'),
                        id: readButton.getAttribute('data-note-id')
                    },
                    complete: function () {
                        updateVisitorNotificationCount();
                    }
                });
                return;
            }
            var button = e.target.closest('.bell-act-view, .bell-act-decide');
            if (!button || !button.closest('#visitorNotificationMenu')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            var item = (window.permBellById || {})[button.getAttribute('data-perm-id')];
            if (!item) {
                return;
            }
            if (button.classList.contains('bell-act-view')) {
                openPermBellView(item);
                return;
            }
            openPermBellDecision(item, button.getAttribute('data-action'));
        }, true);
    }
}

function bellMarkReadButton(type, id, unread) {
    if (!unread) {
        return '';
    }
    return '<button type="button" class="bell-mark-read" data-note-type="' + escapePermBell(type) + '" data-note-id="' + escapePermBell(id) + '">Mark as read</button>';
}

function escapePermBell(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

function closeBellMenu() {
    $('#visitorNotificationMenu').removeClass('show');
    $('#notification').attr('aria-expanded', 'false').parent().removeClass('show');
}

function openBellOverlay(title, bodyHtml) {
    var overlay = document.getElementById('bellOverlay');
    if (!overlay) {
        return;
    }
    if (overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }
    document.getElementById('bellOverlayTitle').textContent = title || '';
    document.getElementById('bellOverlayBody').innerHTML = bodyHtml || '';
    overlay.classList.add('is-open');
    closeBellMenu();
}

function closeBellOverlay() {
    var overlay = document.getElementById('bellOverlay');
    if (overlay) {
        overlay.classList.remove('is-open');
    }
}

function openPermBellView(item) {
    var attachment = item.attachment_url
        ? '<p style="margin-top:10px;"><a href="' + escapePermBell(item.attachment_url) + '" target="_blank" rel="noopener">Open attachment</a></p>'
        : '';
    openBellOverlay(item.student_name || 'Permission',
        '<p><strong>Parent:</strong> ' + escapePermBell(item.parent_name) + '</p>' +
        '<p><strong>Type:</strong> ' + escapePermBell(item.reason_label) + '</p>' +
        '<p><strong>Days:</strong> ' + escapePermBell(item.days_count) + '</p>' +
        '<p><strong>From:</strong> ' + escapePermBell(item.start_date) + ' <strong>To:</strong> ' + escapePermBell(item.end_date) + '</p>' +
        '<p><strong>Reason:</strong><br>' + escapePermBell(item.reason_description) + '</p>' +
        attachment
    );
}

function openPermBellDecision(item, action) {
    var approving = action === 'approve';
    openBellOverlay(approving ? 'Accept permission' : 'Reject permission',
        '<p>' + escapePermBell(item.student_name) + ' | ' + escapePermBell(item.reason_label) + '</p>' +
        '<label for="bellReasonInput"><strong>Reason</strong></label>' +
        '<textarea id="bellReasonInput" maxlength="500" placeholder="Andika sababu"></textarea>' +
        '<div id="bellOverlayError"></div>' +
        '<div style="margin-top:12px; display:flex; gap:8px;">' +
        '<button type="button" id="bellReasonSubmit" class="bell-act ' + (approving ? 'bell-act-accept' : 'bell-act-reject') + '" style="flex:1;">' + (approving ? 'Accept' : 'Reject') + '</button>' +
        '<button type="button" id="bellReasonCancel" class="bell-act bell-act-view" style="flex:1;">Cancel</button>' +
        '</div>'
    );
    $('#bellReasonCancel').on('click', function () {
        closeBellOverlay();
    });
    $('#bellReasonSubmit').on('click', function () {
        var reason = String($('#bellReasonInput').val() || '').trim();
        if (!reason) {
            $('#bellOverlayError').text('Andika sababu');
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true);
        $('#bellReasonCancel').prop('disabled', true);
        $('#bellReasonInput').prop('disabled', true);
        $('#bellOverlayLoading').addClass('is-on');
        $.ajax({
            url: approving
                ? '{{ route("admin.permissions.approve") }}'
                : '{{ route("admin.permissions.reject") }}',
            method: 'POST',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                permissionID: item.id,
                is_student_permission: 1,
                admin_response: reason
            },
            success: function (response) {
                $('#bellOverlayLoading').removeClass('is-on');
                openBellOverlay('Saved', '<p>' + escapePermBell((response && response.message) ? response.message : 'Saved') + '</p>');
                updateVisitorNotificationCount();
            },
            error: function (xhr) {
                var message = 'Imeshindikana. Jaribu tena.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    }
                }
                $('#bellOverlayLoading').removeClass('is-on');
                $btn.prop('disabled', false);
                $('#bellReasonCancel').prop('disabled', false);
                $('#bellReasonInput').prop('disabled', false);
                $('#bellOverlayError').text(message);
            }
        });
    });
}

function updateVisitorNotificationCount() {
    if (typeof window.jQuery === 'undefined') {
        return;
    }
    var $ = window.jQuery;
    $.get('{{ route("admin.visitor_notifications_count") }}', function(response) {
        if (!response || response.success !== true) {
            return;
        }
        const count = parseInt(response.count || 0, 10);
        const oaCount = parseInt(response.online_admissions || 0, 10);
        const permCount = parseInt(response.parent_permissions || 0, 10);
        const $button = $('#notification');
        const $badge = $('#visitorNotificationCount');

        if (count > 0) {
            if ($badge.length) {
                $badge.text(count);
            } else {
                $button.append(`<span class="count bg-danger notification-count" id="visitorNotificationCount">${count}</span>`);
            }
            let header = 'Notifications: ' + count;
            if (permCount > 0) {
                header += '<small class="d-block text-muted" style="font-weight:500;">Permission requests: ' + permCount + '</small>';
            }
            if (oaCount > 0) {
                header += '<small class="d-block text-muted" style="font-weight:500;">Online applications: ' + oaCount + '</small>';
            }
            $('#visitorNotificationHeader').html(header);
            $('#markAllNotificationsRead').show();
        } else {
            $badge.remove();
            $('#visitorNotificationHeader').text('Notifications: 0');
            $('#markAllNotificationsRead').hide();
        }
        fetchRecentVisitors(1, false);
    });
}

function bellNoteHtml(item) {
    var unread = !!item.is_unread;
    var state = unread ? 'is-unread' : 'is-read';
    var icon = item.type === 'permission' ? 'fa-calendar-check-o' : (item.type === 'admission' ? 'fa-wpforms' : 'fa-user');
    var actions = '';
    if (item.type === 'permission' && item.permission) {
        actions = '<div class="perm-bell-actions">' +
            '<button type="button" class="bell-act bell-act-view" data-perm-id="' + escapePermBell(item.id) + '">View</button>' +
            '<button type="button" class="bell-act bell-act-accept bell-act-decide" data-perm-id="' + escapePermBell(item.id) + '" data-action="approve">Accept</button>' +
            '<button type="button" class="bell-act bell-act-reject bell-act-decide" data-perm-id="' + escapePermBell(item.id) + '" data-action="reject">Reject</button>' +
            '</div>';
    }
    return '<div class="bell-note ' + state + '" data-href="' + escapePermBell(item.url || '') + '" data-note-type="' + escapePermBell(item.type || '') + '" data-note-id="' + escapePermBell(item.id) + '">' +
        '<div class="bell-note-icon"><i class="fa ' + icon + '"></i></div>' +
        '<div class="bell-note-main">' +
            '<div class="bell-note-top">' +
                '<span class="bell-note-title">' + escapePermBell(item.title || '') + '</span>' +
                '<span class="bell-note-time">' + escapePermBell(item.time_label || '') + '</span>' +
            '</div>' +
            '<div class="bell-note-meta">' + escapePermBell(item.meta || '') + '</div>' +
            '<div class="bell-note-status">' +
                '<span class="bell-note-flag">' + (unread ? 'Unread' : 'Read') + '</span>' +
                bellMarkReadButton(item.type, item.id, unread) +
            '</div>' +
            actions +
        '</div>' +
    '</div>';
}

function renderBellFeed(items, append, hasMore) {
    var $ = window.jQuery;
    var $list = $('#visitorNotificationList');
    if (!append) {
        window.permBellById = {};
    }
    window.permBellById = window.permBellById || {};
    items.forEach(function (item) {
        if (item.type === 'permission' && item.permission) {
            window.permBellById[String(item.id)] = item.permission;
        }
    });
    var html = '';
    if (!items.length && !append) {
        html = '<div class="bell-note is-read"><div class="bell-note-main"><div class="bell-note-title">No notifications.</div></div></div>';
    } else {
        items.forEach(function (item) {
            html += bellNoteHtml(item);
        });
    }
    if (hasMore) {
        html += '<div class="bell-feed-more" id="bellFeedMore">Scroll for more</div>';
    }
    if (append) {
        $list.find('#bellFeedMore').remove();
        $list.append(html);
    } else {
        $list.html(html);
    }
    window.bellFeedHasMore = !!hasMore;
}

function fetchRecentVisitors(page, append) {
    var $ = window.jQuery;
    page = page || 1;
    if (window.bellFeedLoading) {
        return;
    }
    window.bellFeedLoading = true;
    $.get('{{ route("admin.get_recent_visitors") }}', { page: page }, function(response) {
        window.bellFeedLoading = false;
        if (!response || response.success !== true) return;
        if (Array.isArray(response.items)) {
            if (append && response.items.length === 0) {
                window.bellFeedHasMore = false;
                $('#bellFeedMore').remove();
                return;
            }
            renderBellFeed(response.items, !!append, !!response.has_more);
            window.bellFeedPage = page;
            return;
        }

        const $list = $('#visitorNotificationList');
        const admissions = response.online_admissions || [];
        const visitors = response.visitors || [];
        const permissions = response.parent_permissions || [];
        window.permBellById = {};
        permissions.forEach(function (perm) {
            window.permBellById[String(perm.id)] = perm;
        });

        if (admissions.length === 0 && visitors.length === 0 && permissions.length === 0) {
            $list.html('<div class="bell-note is-read"><div class="bell-note-main"><div class="bell-note-title">No notifications.</div></div></div>');
            return;
        }

        let html = '';
        permissions.forEach(function (perm) {
            var unread = !!perm.is_unread;
            var state = unread ? 'is-unread' : 'is-read';
            html += `
                <div class="bell-note ${state}">
                    <div class="bell-note-icon"><i class="fa fa-calendar-check-o"></i></div>
                    <div class="bell-note-main">
                        <div class="bell-note-top">
                            <span class="bell-note-title">Permission request</span>
                            <span class="bell-note-time">${escapePermBell(perm.time_label)}</span>
                        </div>
                        <div class="bell-note-meta">${escapePermBell(perm.student_name)} | ${escapePermBell(perm.reason_label)} | ${escapePermBell(perm.start_date)} - ${escapePermBell(perm.end_date)}</div>
                        <div class="bell-note-status">
                            <span class="bell-note-flag">${unread ? 'Unread' : 'Read'}</span>
                            ${bellMarkReadButton('permission', perm.id, unread)}
                        </div>
                        <div class="perm-bell-actions">
                            <button type="button" class="bell-act bell-act-view" data-perm-id="${escapePermBell(perm.id)}">View</button>
                            <button type="button" class="bell-act bell-act-accept bell-act-decide" data-perm-id="${escapePermBell(perm.id)}" data-action="approve">Accept</button>
                            <button type="button" class="bell-act bell-act-reject bell-act-decide" data-perm-id="${escapePermBell(perm.id)}" data-action="reject">Reject</button>
                        </div>
                    </div>
                </div>
            `;
        });
        admissions.forEach(function (app) {
            var unread = !!app.is_new;
            var state = unread ? 'is-unread' : 'is-read';
            html += `
                <div class="bell-note ${state}">
                    <div class="bell-note-icon"><i class="fa fa-wpforms"></i></div>
                    <div class="bell-note-main">
                        <div class="bell-note-top">
                            <span class="bell-note-title"><a href="${app.url || '{{ route('school.online_admission.index') }}'}">Online application</a></span>
                            <span class="bell-note-time">${escapePermBell(app.time_label || '')}</span>
                        </div>
                        <div class="bell-note-meta">${escapePermBell(app.name || 'Applicant')} | ${escapePermBell(app.application_number || '')}</div>
                        <div class="bell-note-status">
                            <span class="bell-note-flag">${unread ? 'Unread' : 'Read'}</span>
                            ${bellMarkReadButton('admission', app.id, unread)}
                        </div>
                    </div>
                </div>
            `;
        });
        visitors.forEach(function (visitor) {
            var unread = !!visitor.is_new;
            var state = unread ? 'is-unread' : 'is-read';
            html += `
                <div class="bell-note ${state}">
                    <div class="bell-note-icon"><i class="fa fa-user"></i></div>
                    <div class="bell-note-main">
                        <div class="bell-note-top">
                            <span class="bell-note-title"><a href="{{ route('admin.school_visitors') }}">${escapePermBell(visitor.name)}</a></span>
                            <span class="bell-note-time">${escapePermBell(visitor.time_label)}</span>
                        </div>
                        <div class="bell-note-meta">${escapePermBell(visitor.reason || 'N/A')} | ${escapePermBell(visitor.contact || 'N/A')}</div>
                        <div class="bell-note-status">
                            <span class="bell-note-flag">${unread ? 'Unread' : 'Read'}</span>
                            ${bellMarkReadButton('visitor', visitor.visitorID, unread)}
                        </div>
                    </div>
                </div>
            `;
        });
        $list.html(html);
    }).fail(function () {
        window.bellFeedLoading = false;
    });
}


function initializeExamPaperNotifications() {

    if (typeof window.jQuery === 'undefined') {
        setTimeout(initializeExamPaperNotifications, 100);
        return;
    }
    var $ = window.jQuery;
    $('#message').off('show.bs.dropdown.examPaper').on('show.bs.dropdown.examPaper', function() {
        $.ajax({
            url: '{{ route("mark_exam_paper_notifications_read") }}',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                $('#examPaperNotificationCount').remove();
            }
        });
    });
}

function updateExamPaperNotificationCount() {
    if (typeof window.jQuery === 'undefined') {
        return;
    }
    var $ = window.jQuery;
    $.get('{{ route("admin.exam_paper_notifications_count") }}', function(response) {
        if (!response || response.success !== true) {
            return;
        }
        const count = parseInt(response.count || 0, 10);
        const $button = $('#message');
        const $badge = $('#examPaperNotificationCount');

        if (count > 0) {
            if ($badge.length) {
                $badge.text(count);
            } else {
                $button.append(`<span class="count bg-primary" id="examPaperNotificationCount">${count}</span>`);
            }

            // Also refresh the exam paper list if there are new ones
            fetchRecentExamPaperNotifications();
        } else {
            $badge.remove();
        }
    });
}

function fetchRecentExamPaperNotifications() {
    var $ = window.jQuery;
    $.get('{{ route("admin.get_recent_exam_paper_notifications") }}', function(response) {
        if (!response || response.success !== true) return;

        const $list = $('#examPaperNotificationList');
        if (response.notifications.length === 0) {
            $list.html('<span class="dropdown-item text-muted">No new exam paper notifications.</span>');
            return;
        }

        let html = '';
        response.notifications.forEach(note => {
            const isUnread = parseInt(note.is_read) === 0;
            html += `
                <a class="dropdown-item media" href="{{ route('manageExamination') }}">
                    <span class="photo media-left"><img alt="avatar" src="${note.photo_url}"></span>
                    <span class="message media-body">
                        <span class="name float-left">${note.teacher_name}</span>
                        ${isUnread ? '<span class="badge badge-danger ml-2">New</span>' : ''}
                        <span class="time float-right">${note.time_label}</span>
                        <p>New exam paper: ${note.exam_name} | ${note.subject_name} | ${note.class_display}</p>
                    </span>
                </a>
            `;
        });
        $list.html(html);
    });
}


function loadScriptOnce(src, onLoad) {
    if (document.querySelector('script[src="' + src + '"]')) {
        if (typeof onLoad === 'function') {
            onLoad();
        }
        return;
    }
    var script = document.createElement('script');
    script.src = src;
    script.onload = function() {
        if (typeof onLoad === 'function') {
            onLoad();
        }
    };
    document.head.appendChild(script);
}

function ensureJqueryAndBootstrap(callback) {
    function bindAliasAndContinue() {
        if (typeof window.jQuery === 'function') {
            window.$ = window.jQuery;
        }
        if (typeof callback === 'function') {
            callback();
        }
    }

    if (window.jQuery) {
        // If jQuery exists but Bootstrap collapse doesn't, just load Bootstrap
        if (typeof window.jQuery.fn.collapse !== 'function') {
            loadScriptOnce('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js', bindAliasAndContinue);
        } else {
            bindAliasAndContinue();
        }
        return;
    }

    // Fallback if no jQuery at all
    loadScriptOnce('https://code.jquery.com/jquery-3.6.0.min.js', function() {
        window.$ = window.jQuery;
        loadScriptOnce('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js', bindAliasAndContinue);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    ensureJqueryAndBootstrap(function() {
        initializeMenuDropdowns();
        initializeVisitorNotifications();
        initializeExamPaperNotifications();
        updateVisitorNotificationCount();
        updateExamPaperNotificationCount();

        // Collapsed sidebar: don't accordion-toggle; hover shows flyout actions
        if (window.jQuery) {
            jQuery(document).on('click', 'body.open #left-panel .dropdown-toggle', function (e) {
                if (window.matchMedia('(max-width: 991px)').matches) {
                    return;
                }
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            });
        }

        // Setup intervals for polling (every 30 seconds)
        setInterval(updateVisitorNotificationCount, 30000);
        setInterval(updateExamPaperNotificationCount, 30000);
    });
});

// Also re-initialize when page is shown (for back/forward navigation)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        ensureJqueryAndBootstrap(function() {
            initializeMenuDropdowns();
            initializeVisitorNotifications();
            initializeExamPaperNotifications();
            updateVisitorNotificationCount();
            updateExamPaperNotificationCount();
        });
    }
});

// Re-initialize after a short delay to ensure everything is loaded
setTimeout(function() {
    ensureJqueryAndBootstrap(function() {
        initializeMenuDropdowns();
        initializeVisitorNotifications();
        initializeExamPaperNotifications();
        updateVisitorNotificationCount();
        updateExamPaperNotificationCount();
    });
}, 500);

})(window.jQuery);
</script>

<script>
(function() {
    const IDLE_MS = 270 * 1000;
    const WARN_SECONDS = 30;
    const LOGOUT_URL = '{{ route('logout') }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    let idleTimer = null;
    let warnTimer = null;
    let countdownTimer = null;
    let remaining = WARN_SECONDS;
    let overlay = null;
    let lastActiveAt = Date.now();

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
        countdownTimer = setInterval(() => {
            remaining -= 1;
            if (c) c.textContent = String(Math.max(0, remaining));
            if (remaining <= 0) {
                logoutNow();
            }
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
        idleTimer = setTimeout(() => {
            showWarning();
        }, IDLE_MS);
    }

    function resetAll() {
        lastActiveAt = Date.now();
        hideWarning();
        if (warnTimer) clearTimeout(warnTimer);
        warnTimer = null;
        scheduleIdle();
    }

    function onActivity() {
        // Only reset if user actually interacted after warning shown or idle countdown running
        resetAll();
    }

    ['mousemove','mousedown','keydown','scroll','touchstart','click'].forEach(evt => {
        window.addEventListener(evt, onActivity, { passive: true });
    });

    // Start
    scheduleIdle();
})();
</script>
@endif
