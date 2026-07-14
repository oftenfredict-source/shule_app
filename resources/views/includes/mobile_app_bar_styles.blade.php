{{-- Shared mobile top bar styles (Admin / Staff / Teacher) --}}
<style>
.mobile-app-bar {
    display: none;
}
.header-mobile-divider {
    display: none;
}
@media (max-width: 991px) {
    .mobile-app-bar {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 1060;
        min-height: 52px;
        padding: 10px 14px;
        background: #940000;
        border-bottom: 1px solid rgba(255,255,255,0.35);
        box-sizing: border-box;
    }
    .mobile-app-bar .mobile-brand {
        display: flex !important;
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
    .mobile-app-bar .mobile-brand i {
        color: #ffffff !important;
        font-size: 1.3rem;
    }
    .mobile-app-bar .navbar-toggler,
    #mobileSidebarToggle {
        border: none !important;
        padding: 6px 4px !important;
        border-radius: 4px !important;
        background: transparent !important;
        color: #ffffff !important;
        cursor: pointer !important;
        margin-left: auto !important;
        box-shadow: none !important;
        outline: none !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
    }
    .mobile-app-bar .nav-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background-color: #ffffff;
        display: block;
    }
    body.mobile-drawer-open .mobile-app-bar {
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    /* Header: flex row — years/icons scroll; avatar never overlaps */
    #header .header-menu {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 6px !important;
        width: 100% !important;
        overflow: visible !important;
    }
    #header .header-menu > .col-sm-7 {
        float: none !important;
        flex: 1 1 auto !important;
        width: auto !important;
        min-width: 0 !important;
        max-width: calc(100% - 72px) !important;
        display: block !important;
        padding-right: 4px !important;
    }
    #header .header-menu > .col-sm-5 {
        float: none !important;
        flex: 0 0 auto !important;
        width: auto !important;
        max-width: none !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        text-align: right !important;
        padding-left: 4px !important;
        border-left: none !important;
        margin-left: auto !important;
    }
    #header .header-mobile-divider {
        display: none !important;
    }
    #header.header,
    .right-panel header.header,
    header#header.header {
        border-bottom: 1px solid #d0d0d0 !important;
        box-shadow: none !important;
    }
    #header .header-left {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 4px !important;
        width: 100% !important;
        min-width: 0 !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch;
        white-space: nowrap !important;
        scrollbar-width: none;
    }
    #header .header-left::-webkit-scrollbar {
        display: none;
    }
    #header .header-left > .dropdown {
        flex: 0 0 auto !important;
    }
    #header .dropdown.for-message {
        display: none !important;
    }
    #header .menutoggle,
    a#menuToggle.menutoggle {
        display: none !important;
    }
    /* Profile + flag horizontal */
    #header .language-select,
    #header #language-select {
        clear: none !important;
        float: none !important;
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border-top: none !important;
        order: 0 !important;
    }
    #header .user-area.dropdown {
        float: none !important;
        display: inline-flex !important;
        align-items: center !important;
        order: 0 !important;
        margin: 0 !important;
    }
    #header .user-area .user-avatar {
        width: 36px !important;
        height: 36px !important;
    }
    #header .header-academic-year {
        flex: 0 0 auto !important;
        margin-left: 4px !important;
        gap: 4px !important;
    }
    #header .header-academic-year select {
        min-width: 0 !important;
        max-width: 102px !important;
        width: auto !important;
        flex-shrink: 1 !important;
    }

    /* Off-canvas drawer (works even if footer styles are missing on some teacher pages) */
    .right-panel,
    #right-panel {
        margin-left: 0 !important;
    }
    aside.left-panel,
    aside#left-panel {
        display: block !important;
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: min(300px, 86vw) !important;
        max-width: min(300px, 86vw) !important;
        height: 100vh !important;
        max-height: 100vh !important;
        z-index: 1050 !important;
        transform: translateX(-105%) !important;
        -webkit-transform: translateX(-105%) !important;
        transition: transform 0.25s ease !important;
        pointer-events: none !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        background: #ffffff !important;
    }
    body.mobile-drawer-open aside.left-panel,
    body.mobile-drawer-open aside#left-panel {
        transform: translateX(0) !important;
        -webkit-transform: translateX(0) !important;
        pointer-events: auto !important;
        box-shadow: 8px 0 24px rgba(0, 0, 0, 0.2) !important;
        padding-top: 52px !important;
    }
    body.mobile-drawer-open #left-panel #main-menu.collapse,
    body.mobile-drawer-open #left-panel #main-menu {
        display: block !important;
        height: auto !important;
        visibility: visible !important;
        overflow: visible !important;
    }
    #left-panel .navbar-toggler {
        display: none !important;
    }
    .sidebar-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        top: 52px;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1040;
    }
    body.mobile-drawer-open .sidebar-backdrop.is-visible {
        display: block;
    }

    /* Offset page under fixed mobile bar — keep footer at bottom */
    .right-panel,
    #right-panel {
        padding-top: 52px !important;
        min-height: 100vh !important;
        min-height: 100dvh !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .right-panel > .content,
    .right-panel > .animated,
    .right-panel > .container-fluid,
    .right-panel > main,
    #right-panel > .content {
        flex: 1 0 auto !important;
    }
    .site-footer {
        position: relative !important;
        margin-top: auto !important;
        flex-shrink: 0 !important;
        bottom: auto !important;
        left: auto !important;
        right: auto !important;
        width: 100% !important;
    }
}
</style>
