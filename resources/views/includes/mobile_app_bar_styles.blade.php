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
    body.open .mobile-app-bar {
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    /* Header: restore normal mobile floats; full horizontal line under header only */
    #header .header-menu {
        display: block !important;
        width: 100% !important;
        overflow: hidden !important;
    }
    #header .header-menu > .col-sm-7 {
        float: left !important;
        width: auto !important;
        max-width: 70% !important;
        display: block !important;
        padding-right: 8px !important;
    }
    #header .header-menu > .col-sm-5 {
        float: right !important;
        width: auto !important;
        max-width: 30% !important;
        display: block !important;
        text-align: right !important;
        padding-left: 8px !important;
        border-left: none !important;
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
        display: inline-block !important;
    }
    #header .menutoggle,
    a#menuToggle.menutoggle {
        display: none !important;
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
