<!-- ======= Custom Footer Start ======= -->
<style>
  /*
   * Layout: sticky sidebar + sticky header while scrolling.
   * Footer scrolls with page content (not fixed).
   */
  body {
    padding-bottom: 0 !important;
  }

  aside.left-panel,
  aside#left-panel {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    max-height: 100vh !important;
    overflow-x: hidden;
    overflow-y: auto;
    /* Below Bootstrap modal (1050) / backdrop (1040) so modals are usable */
    z-index: 1000 !important;
    width: 280px;
  }

  .right-panel,
  #right-panel {
    display: flex !important;
    flex-direction: column !important;
    margin-left: 280px !important;
    min-height: 100vh;
    position: relative;
    /* Do NOT set z-index here — it traps modals under the fixed sidebar */
    z-index: auto !important;
  }

  /* Desktop only: body.open = icon-collapsed rail */
  @media (min-width: 992px) {
  .open aside.left-panel,
  .open aside#left-panel,
  body.open aside.left-panel,
  body.open aside#left-panel {
    width: 76px !important;
    max-width: 76px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  body.open .right-panel,
  body.open #right-panel,
  .open .right-panel,
  .open #right-panel {
    margin-left: 76px !important;
  }

  /*
   * Collapsed sidebar: large centered icons + fixed hover flyouts
   * (flyouts use position:fixed via JS so overflow-y scroll cannot clip them)
   */
  body.open #left-panel .navbar,
  body.open #left-panel .main-menu,
  body.open #left-panel .navbar-nav,
  body.open #left-panel .sidebar-links-container,
  body.open #left-panel .sidebar-links-container > ul {
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }

  body.open #left-panel .navbar-header {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 10px 0 !important;
    width: 100% !important;
  }

  body.open #left-panel .admin-brand-logo,
  body.open #left-panel .navbar-header img {
    width: 40px !important;
    height: 40px !important;
    object-fit: cover !important;
    margin: 0 auto !important;
    display: block !important;
  }

  body.open #left-panel .sidebar-profile {
    padding: 8px 0 !important;
    justify-content: center !important;
  }

  body.open #left-panel .sidebar-profile .profile-meta,
  body.open #left-panel .admin-brand-text,
  body.open #left-panel .admin-brand-subtitle,
  body.open #left-panel .fa-chevron-down {
    display: none !important;
  }

  body.open #left-panel .sidebar-links-container > ul > li {
    position: relative;
    width: 100%;
  }

  body.open #left-panel .sidebar-links-container > ul > li > a.nav-link {
    font-size: 0 !important;
    line-height: 1 !important;
    text-align: center !important;
    padding: 16px 0 !important;
    margin: 2px 6px !important;
    width: auto !important;
    min-height: 48px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    white-space: nowrap;
    border-radius: 8px;
    box-sizing: border-box !important;
  }

  body.open #left-panel .sidebar-links-container > ul > li > a.nav-link > i.fa:first-child,
  body.open #left-panel .sidebar-links-container > ul > li > a.nav-link > i[class*="fa-"]:first-of-type,
  body.open #left-panel .sidebar-links-container > ul > li > a.nav-link > i:first-child {
    font-size: 22px !important;
    margin: 0 !important;
    width: 1.25em !important;
    height: auto !important;
    line-height: 1 !important;
    display: inline-block !important;
    text-align: center !important;
    color: #940000 !important;
    float: none !important;
  }

  body.open #left-panel .dropdown-nav-item {
    position: relative;
  }

  body.open #left-panel .dropdown-nav-item > .submenu {
    display: none !important;
    position: fixed !important;
    /* left/top set by JS — do not use !important here or flyout sticks to top */
    left: 84px;
    top: 0;
    min-width: 250px;
    max-width: 320px;
    max-height: 70vh;
    overflow-y: auto;
    background: #ffffff !important;
    border: 1px solid #e5e5e5 !important;
    border-left: 3px solid #940000 !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18) !important;
    z-index: 3000 !important;
    padding: 6px 0 !important;
    margin: 0 !important;
    list-style: none !important;
    border-radius: 0 6px 6px 0;
  }

  body.open #left-panel .dropdown-nav-item > .submenu.is-flyout-open,
  body.open #left-panel .dropdown-nav-item:hover > .submenu,
  body.open #left-panel .dropdown-nav-item:focus-within > .submenu {
    display: block !important;
    height: auto !important;
  }

  /* Invisible bridge so cursor can reach flyout without closing */
  body.open #left-panel .dropdown-nav-item > .submenu::after {
    content: "";
    position: absolute;
    left: -16px;
    top: 0;
    width: 16px;
    height: 100%;
  }

  body.open #left-panel .dropdown-nav-item > .submenu::before {
    content: "";
    position: absolute;
    left: -11px;
    top: 18px;
    border: 6px solid transparent;
    border-right-color: #940000;
  }

  body.open #left-panel .dropdown-nav-item > .submenu .nav-link {
    font-size: 13px !important;
    color: #333 !important;
    padding: 8px 14px !important;
    white-space: nowrap;
    display: block !important;
    text-align: left !important;
    line-height: 1.3 !important;
  }

  body.open #left-panel .dropdown-nav-item > .submenu .nav-link i {
    font-size: 13px !important;
    margin-right: 8px !important;
    color: #940000 !important;
    width: 16px;
    text-align: center;
    display: inline-block !important;
  }

  body.open #left-panel .dropdown-nav-item > .submenu .nav-link:hover {
    background: rgba(148, 0, 0, 0.08) !important;
    color: #940000 !important;
  }
  } /* end desktop collapsed sidebar */

  /* Sidebar toggle button (desktop header) — keep visible, original theme margins OK on desktop */
  #header .menutoggle,
  a#menuToggle.menutoggle,
  .header .menutoggle {
    background-color: whitesmoke !important;
    color: #940000 !important;
    border: 1px solid #e8e8e8 !important;
    border-radius: 4px !important;
    padding: 6px 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    text-decoration: none !important;
    cursor: pointer !important;
  }

  #header .menutoggle i,
  #header .menutoggle .header-icon-muted,
  a#menuToggle.menutoggle i,
  a#menuToggle.menutoggle .header-icon-muted,
  .header .menutoggle i {
    color: #940000 !important;
    opacity: 1 !important;
  }

  #header .menutoggle:hover,
  a#menuToggle.menutoggle:hover,
  .header .menutoggle:hover {
    background-color: #f0f0f0 !important;
    color: #940000 !important;
  }

  /* Mobile app bar (ShuleXpert + 3 dots) — matches teachers mobile header */
  .mobile-app-bar {
    display: none;
  }

  .header-mobile-divider {
    display: none;
  }

  /* Sticky top header inside content area */
  .right-panel header.header,
  .right-panel #header,
  header#header.header,
  #header {
    position: sticky !important;
    top: 0 !important;
    z-index: 1020 !important;
    background-color: #ffffff !important;
    flex-shrink: 0;
  }

  /* Footer: bottom of page, no red border */
  .site-footer {
    position: relative !important;
    left: auto !important;
    right: auto !important;
    bottom: auto !important;
    width: 100% !important;
    float: none !important;
    clear: both;
    background-color: #ffffff;
    color: #333;
    text-align: center;
    border-top: none !important;
    border: none !important;
    font-size: 13px;
    padding: 12px 0;
    margin: auto 0 0 0 !important;
    margin-top: auto !important;
    z-index: auto !important;
    box-shadow: none !important;
    pointer-events: auto;
    flex-shrink: 0;
  }

  /* Only OUTER sidebar scrollbar (remove inner .sidebar-links-container scroll) */
  .sidebar-links-container {
    overflow: visible !important;
    max-height: none !important;
    height: auto !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
  }

  .sidebar-links-container::-webkit-scrollbar {
    width: 0 !important;
    display: none !important;
  }

  .sidebar-links-container:hover {
    overflow: visible !important;
  }

  /* Modals ABOVE sidebar + header */
  .modal-backdrop {
    z-index: 2000 !important;
  }

  .modal {
    z-index: 2010 !important;
  }

  .modal-dialog {
    z-index: 2011 !important;
  }

  .modal-content {
    z-index: 2012 !important;
    pointer-events: auto !important;
  }

  .modal.show {
    display: block !important;
    pointer-events: auto !important;
  }

  .swal2-container {
    z-index: 30000 !important;
  }

  .swal2-popup {
    z-index: 30001 !important;
  }

  /* While any modal is open, don't let sidebar steal clicks */
  body.modal-open aside.left-panel,
  body.modal-open aside#left-panel {
    pointer-events: none !important;
  }

  canvas,
  .flot-container,
  .chart-container,
  [id*="chart"],
  [id*="Chart"],
  [id*="graph"],
  [id*="Graph"],
  .widget,
  .chart-wrapper {
    position: relative !important;
    z-index: 1 !important;
    margin-bottom: 1rem !important;
  }

  .right-panel,
  .content-wrapper,
  .main-content,
  .container-fluid {
    padding-bottom: 1rem !important;
  }

  /* Mobile / tablet: off-canvas full sidebar (NOT icon collapse) */
  @media (max-width: 991px) {
    .right-panel,
    #right-panel,
    .open .right-panel,
    .open #right-panel,
    body.open .right-panel,
    body.open #right-panel {
      margin-left: 0 !important;
    }

    .site-footer {
      width: 100% !important;
    }

    /* Hide desktop header menuToggle on mobile (use top app bar instead) */
    #header .menutoggle,
    #header a#menuToggle.menutoggle,
    a#menuToggle.menutoggle {
      display: none !important;
    }

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
      border-bottom: 2px solid rgba(255,255,255,0.15);
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

    /* Offset content below fixed app bar — do not break footer to bottom */
    .right-panel,
    #right-panel {
      padding-top: 52px !important;
      min-height: 100vh !important;
      min-height: 100dvh !important;
      box-sizing: border-box !important;
    }

    .right-panel header.header,
    #header {
      top: 52px !important;
    }

    .site-footer {
      position: relative !important;
      bottom: auto !important;
      left: auto !important;
      right: auto !important;
      margin-top: auto !important;
      width: 100% !important;
    }

    /* Header: years/icons scroll left; avatar+flag stay fixed right (no overlap) */
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
      border-left: none !important;
      padding-left: 4px !important;
      margin-left: auto !important;
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

    #header .dropdown.for-message {
      display: none !important;
    }

    #header .header-academic-year {
      flex: 0 0 auto !important;
      margin-left: 4px !important;
      gap: 4px !important;
    }

    #header .header-academic-year select {
      min-width: 0 !important;
      max-width: 102px !important;
    }

    #header .header-mobile-divider {
      display: none !important;
    }

    #header.header,
    .right-panel header.header {
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

    /*
     * Profile + flag side-by-side (horizontal).
     * Do not use body.open here — theme style.css .open = 70px icon rail + font-size:0.
     */
    #header .header-menu > .col-sm-5 {
      display: flex !important;
      flex-direction: row !important;
      flex-wrap: nowrap !important;
      align-items: center !important;
      justify-content: flex-end !important;
      gap: 8px !important;
      float: right !important;
      width: auto !important;
      max-width: none !important;
    }

    #header .language-select,
    #header #language-select {
      clear: none !important;
      float: none !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: flex-end !important;
      flex-wrap: nowrap !important;
      width: auto !important;
      order: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      border-top: none !important;
      text-align: right !important;
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

    /* Closed drawer (off-canvas). Open state = body.mobile-drawer-open only. */
    aside.left-panel,
    aside#left-panel {
      display: block !important;
      position: fixed !important;
      left: 0 !important;
      top: 0 !important;
      width: min(300px, 86vw) !important;
      max-width: min(300px, 86vw) !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      height: 100vh !important;
      max-height: 100vh !important;
      z-index: 1050 !important;
      transform: translateX(-105%) !important;
      -webkit-transform: translateX(-105%) !important;
      transition: transform 0.25s ease !important;
      box-shadow: none !important;
      overflow-x: hidden !important;
      overflow-y: auto !important;
      pointer-events: none !important;
      background: #ffffff !important;
    }

    body.mobile-drawer-open aside.left-panel,
    body.mobile-drawer-open aside#left-panel {
      transform: translateX(0) !important;
      -webkit-transform: translateX(0) !important;
      box-shadow: 8px 0 24px rgba(0, 0, 0, 0.2) !important;
      pointer-events: auto !important;
      padding-top: 52px !important;
      width: min(300px, 86vw) !important;
      max-width: min(300px, 86vw) !important;
    }

    /* Kill theme .open collapse if it ever lands on mobile */
    body.open aside.left-panel,
    body.open aside#left-panel,
    .open aside.left-panel,
    .open aside#left-panel {
      width: min(300px, 86vw) !important;
      max-width: min(300px, 86vw) !important;
    }

    /* Logo — original size (not tiny square rail) */
    #left-panel .navbar-header,
    body.mobile-drawer-open #left-panel .navbar-header {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 100% !important;
      padding: 16px 14px 12px !important;
      background: transparent !important;
      border-bottom: 1px solid #eeeeee !important;
      min-height: unset !important;
    }

    #left-panel .admin-sidebar-title,
    body.mobile-drawer-open #left-panel .admin-sidebar-title {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      text-align: center !important;
      width: 100% !important;
    }

    #left-panel .admin-brand-logo,
    #left-panel .navbar-header img,
    body.mobile-drawer-open #left-panel .admin-brand-logo,
    body.mobile-drawer-open #left-panel .navbar-header img {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      width: 100% !important;
      max-width: 180px !important;
      height: auto !important;
      max-height: 100px !important;
      object-fit: contain !important;
      margin: 0 auto !important;
    }

    #left-panel .navbar-header > .mobile-brand,
    body.mobile-drawer-open #left-panel .navbar-header > .mobile-brand {
      display: none !important;
    }

    #left-panel .navbar-toggler {
      display: none !important;
    }

    body.mobile-drawer-open #left-panel #main-menu.collapse,
    body.mobile-drawer-open #left-panel #main-menu,
    #left-panel #main-menu {
      display: block !important;
      height: auto !important;
      visibility: visible !important;
      padding: 8px 0 20px !important;
      background: #ffffff !important;
      width: 100% !important;
      max-width: 100% !important;
      overflow: visible !important;
    }

    body.mobile-drawer-open #left-panel .navbar,
    body.mobile-drawer-open #left-panel .navbar-nav,
    body.mobile-drawer-open #left-panel .sidebar-links-container,
    body.mobile-drawer-open #left-panel .sidebar-links-container > ul {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      display: block !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      margin: 0 !important;
      background: transparent !important;
      list-style: none !important;
      box-sizing: border-box !important;
    }

    /* Full-width readable links — beat theme .open font-size:0 / max-width:60px */
    body.mobile-drawer-open #left-panel .sidebar-links-container > ul > li,
    body.mobile-drawer-open #left-panel .navbar-nav li,
    #left-panel .sidebar-links-container > ul > li {
      position: relative !important;
      width: 100% !important;
      max-width: 100% !important;
      display: block !important;
      overflow: visible !important;
      box-sizing: border-box !important;
    }

    body.mobile-drawer-open #left-panel .sidebar-links-container > ul > li > a.nav-link,
    body.mobile-drawer-open #left-panel .navbar-nav li > a.nav-link,
    body.mobile-drawer-open #left-panel .navbar-nav li > a,
    body.mobile-drawer-open #left-panel .navbar .navbar-nav li a {
      font-size: 14px !important;
      font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
      font-weight: 400 !important;
      line-height: 1.4 !important;
      letter-spacing: normal !important;
      text-align: left !important;
      color: #000000 !important;
      padding: 12px 16px !important;
      margin: 2px 10px !important;
      width: calc(100% - 20px) !important;
      max-width: none !important;
      min-width: 0 !important;
      min-height: 44px !important;
      max-height: none !important;
      white-space: normal !important;
      overflow: visible !important;
      text-overflow: clip !important;
      display: flex !important;
      align-items: center !important;
      justify-content: flex-start !important;
      flex-wrap: nowrap !important;
      gap: 10px !important;
      border-radius: 4px !important;
      border-left: 3px solid transparent !important;
      background: transparent !important;
      box-sizing: border-box !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    body.mobile-drawer-open #left-panel .sidebar-links-container > ul > li > a.nav-link > i,
    body.mobile-drawer-open #left-panel .navbar-nav li > a .menu-icon,
    body.mobile-drawer-open #left-panel .sidebar-links-container > ul > li > a.nav-link > i.fa:first-child,
    body.mobile-drawer-open #left-panel .sidebar-links-container > ul > li > a.nav-link > i:first-child {
      font-size: 16px !important;
      width: 22px !important;
      min-width: 22px !important;
      max-width: 22px !important;
      height: auto !important;
      margin: 0 !important;
      display: inline-block !important;
      color: #2f2f2f !important;
      float: none !important;
      flex: 0 0 22px !important;
      text-align: center !important;
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 1 !important;
    }

    body.mobile-drawer-open #left-panel .navbar-nav a .menu-title-text,
    body.mobile-drawer-open #left-panel .navbar-nav .menu-title {
      font-size: 14px !important;
      font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
      font-weight: 400 !important;
      line-height: 1.35 !important;
      color: #000000 !important;
      opacity: 1 !important;
    }

    body.mobile-drawer-open #left-panel .fa-chevron-down {
      display: inline-block !important;
      font-size: 12px !important;
      margin-left: auto !important;
      color: #666666 !important;
      flex: 0 0 auto !important;
    }

    /* Active link (mobile drawer) */
    body.mobile-drawer-open #left-panel .nav-link.menu-active,
    body.mobile-drawer-open #left-panel a.nav-link.menu-active,
    body.mobile-drawer-open #left-panel .nav-link.active,
    body.mobile-drawer-open #left-panel a.nav-link.active,
    body.mobile-drawer-open #left-panel li.active > a,
    body.mobile-drawer-open #left-panel li.menu-active > a,
    body.mobile-drawer-open #left-panel .sidebar-links-container .nav-link.menu-active,
    body.mobile-drawer-open #left-panel .submenu .nav-link.menu-active,
    body.mobile-drawer-open #left-panel .dropdown-toggle.menu-active {
      color: #000000 !important;
      font-weight: 400 !important;
      font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
      background-color: rgba(148, 0, 0, 0.1) !important;
      border-left: 3px solid #940000 !important;
      border-radius: 4px !important;
    }

    body.mobile-drawer-open #left-panel .nav-link.menu-active > i,
    body.mobile-drawer-open #left-panel .nav-link.active > i,
    body.mobile-drawer-open #left-panel .sidebar-links-container .nav-link.menu-active > i,
    body.mobile-drawer-open #left-panel .submenu .nav-link.menu-active > i,
    body.mobile-drawer-open #left-panel .dropdown-toggle.menu-active > i {
      color: #940000 !important;
    }

    body.mobile-drawer-open #left-panel .sidebar-profile {
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      padding: 12px 16px !important;
      justify-content: flex-start !important;
      width: 100% !important;
      box-sizing: border-box !important;
    }

    body.mobile-drawer-open #left-panel .sidebar-profile .profile-meta,
    body.mobile-drawer-open #left-panel .admin-brand-text,
    body.mobile-drawer-open #left-panel .admin-brand-subtitle {
      display: block !important;
    }

    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu,
    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu.is-flyout-open {
      position: static !important;
      left: auto !important;
      top: auto !important;
      right: auto !important;
      max-width: 100% !important;
      width: 100% !important;
      min-width: 0 !important;
      max-height: none !important;
      box-shadow: none !important;
      border: none !important;
      border-left: none !important;
      background: #f7f7f7 !important;
      display: none !important;
      padding: 4px 8px 8px 20px !important;
      margin: 0 !important;
      overflow: visible !important;
      z-index: auto !important;
    }

    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu.show,
    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu.collapse.show {
      display: block !important;
    }

    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu .nav-link {
      font-size: 13px !important;
      font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
      font-weight: 400 !important;
      color: #000000 !important;
      padding: 8px 12px !important;
      width: 100% !important;
      max-width: 100% !important;
      display: flex !important;
      align-items: center !important;
      text-align: left !important;
      white-space: normal !important;
      box-sizing: border-box !important;
      border-left: 3px solid transparent !important;
      border-radius: 4px !important;
    }

    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu::before,
    body.mobile-drawer-open #left-panel .dropdown-nav-item > .submenu::after {
      display: none !important;
    }

    body.mobile-drawer-open .mobile-app-bar {
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .sidebar-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      z-index: 1040;
      top: 52px;
    }

    body.mobile-drawer-open .sidebar-backdrop.is-visible {
      display: block;
    }
  }

  .site-footer a {
    color: #940000;
    text-decoration: none;
    transition: color 0.3s;
    font-weight: 600;
  }

  .site-footer a:hover {
    text-decoration: underline;
    color: #b30000;
  }

  .site-footer strong {
    color: #940000;
  }

  @media (max-width: 576px) {
    .site-footer {
      font-size: 12px;
      padding: 6px 5px;
    }
  }
</style>

<script>
(function () {
    // Move Bootstrap modals to <body> so fixed sidebar never covers them / blocks typing
    function hoistModalToBody(modal) {
        if (!modal || !modal.parentElement) return;
        if (modal.parentElement === document.body) return;
        document.body.appendChild(modal);
    }

    document.addEventListener('show.bs.modal', function (e) {
        hoistModalToBody(e.target);
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.modal').forEach(hoistModalToBody);
        if (window.jQuery) {
            jQuery(document).on('show.bs.modal', '.modal', function () {
                hoistModalToBody(this);
            });

            // Desktop collapsed only: click does not accordion; hover shows flyout
            jQuery(document).on('click', 'body.open #left-panel .dropdown-toggle', function (e) {
                if (window.matchMedia('(max-width: 991px)').matches) {
                    return; // mobile: allow normal Bootstrap collapse accordion
                }
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            });

            function isMobileSidebar() {
                return window.matchMedia('(max-width: 991px)').matches;
            }

            function ensureSidebarBackdrop() {
                if (!document.getElementById('sidebar-backdrop')) {
                    var el = document.createElement('div');
                    el.id = 'sidebar-backdrop';
                    el.className = 'sidebar-backdrop';
                    el.setAttribute('aria-hidden', 'true');
                    document.body.appendChild(el);
                }
                return document.getElementById('sidebar-backdrop');
            }

            function syncMobileSidebar() {
                var backdrop = ensureSidebarBackdrop();
                var mobile = isMobileSidebar();

                if (mobile) {
                    /* Theme .open = 70px + font-size:0 — never keep it on mobile */
                    if (document.body.classList.contains('open')) {
                        document.body.classList.remove('open');
                        document.body.classList.add('mobile-drawer-open');
                    }
                    var open = document.body.classList.contains('mobile-drawer-open');
                    backdrop.classList.toggle('is-visible', open);
                    var mainMenu = document.getElementById('main-menu');
                    if (mainMenu && open) {
                        mainMenu.classList.add('show');
                        mainMenu.style.display = 'block';
                        mainMenu.style.height = 'auto';
                        mainMenu.style.visibility = 'visible';
                    }
                    jQuery('#left-panel .submenu.is-flyout-open').removeClass('is-flyout-open')
                        .each(function () {
                            this.style.removeProperty('left');
                            this.style.removeProperty('top');
                        });
                } else {
                    document.body.classList.remove('mobile-drawer-open');
                    backdrop.classList.remove('is-visible');
                }
            }

            // On mobile load: sidebar closed
            if (isMobileSidebar()) {
                document.body.classList.remove('open', 'mobile-drawer-open');
            }
            ensureSidebarBackdrop();
            syncMobileSidebar();

            // Desktop #menuToggle: main.js toggles body.open — sync backdrop only
            jQuery(document).on('click', '#menuToggle', function () {
                setTimeout(syncMobileSidebar, 0);
            });

            // Mobile top-bar 3-dot — use mobile-drawer-open (NOT body.open)
            // Skip if teacher/staff nav already bound a dedicated handler (avoids double-toggle)
            if (!window.__sxTeacherMobileToggleBound) {
                jQuery(document).on('click', '#mobileSidebarToggle', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (!isMobileSidebar()) return;
                    document.body.classList.remove('open');
                    document.body.classList.toggle('mobile-drawer-open');
                    syncMobileSidebar();
                });
            } else {
                // Keep backdrop / main-menu sync in sync when teacher handler toggles class
                jQuery(document).on('click', '#mobileSidebarToggle', function () {
                    setTimeout(syncMobileSidebar, 0);
                });
            }

            jQuery(document).on('click', '#sidebar-backdrop', function () {
                document.body.classList.remove('open', 'mobile-drawer-open');
                syncMobileSidebar();
            });

            jQuery(document).on('click', '#left-panel .sidebar-links-container a.nav-link:not(.dropdown-toggle)', function () {
                if (!isMobileSidebar()) return;
                document.body.classList.remove('open', 'mobile-drawer-open');
                syncMobileSidebar();
            });

            var resizeTimer = null;
            jQuery(window).on('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    if (isMobileSidebar()) {
                        document.body.classList.remove('open');
                    } else {
                        document.body.classList.remove('mobile-drawer-open');
                    }
                    syncMobileSidebar();
                }, 150);
            });

            // Position flyout outside sidebar (fixed) so overflow scroll cannot clip it
            function positionCollapsedFlyout(item) {
                if (!document.body.classList.contains('open')) return;
                if (isMobileSidebar()) return;
                var sub = jQuery(item).children('.submenu').get(0);
                if (!sub) return;
                var rect = item.getBoundingClientRect();
                var left = Math.round(rect.right + 8);
                var top = Math.round(rect.top);
                sub.style.setProperty('left', left + 'px', 'important');
                sub.style.setProperty('top', top + 'px', 'important');
                sub.classList.add('is-flyout-open');
                // Keep inside viewport vertically
                requestAnimationFrame(function () {
                    var h = sub.offsetHeight || 0;
                    var maxTop = Math.max(8, window.innerHeight - h - 8);
                    if (top > maxTop) {
                        sub.style.setProperty('top', maxTop + 'px', 'important');
                    }
                });
            }

            function hideCollapsedFlyout(item) {
                var sub = jQuery(item).children('.submenu').get(0);
                if (!sub) return;
                sub.classList.remove('is-flyout-open');
                sub.style.removeProperty('left');
                sub.style.removeProperty('top');
            }

            jQuery(document)
                .on('mouseenter', 'body.open #left-panel .dropdown-nav-item', function () {
                    if (isMobileSidebar()) return;
                    positionCollapsedFlyout(this);
                })
                .on('mouseleave', 'body.open #left-panel .dropdown-nav-item', function () {
                    if (isMobileSidebar()) return;
                    hideCollapsedFlyout(this);
                });

            // Reposition on scroll/resize while open
            jQuery('#left-panel').on('scroll.collapsedFlyout', function () {
                if (!document.body.classList.contains('open') || isMobileSidebar()) return;
                jQuery('#left-panel .dropdown-nav-item:hover').each(function () {
                    positionCollapsedFlyout(this);
                });
            });
            jQuery(window).on('resize.collapsedFlyout', function () {
                if (!document.body.classList.contains('open') || isMobileSidebar()) return;
                jQuery('#left-panel .dropdown-nav-item:hover').each(function () {
                    positionCollapsedFlyout(this);
                });
            });
        } else {
            // Fallback without jQuery (if $ broken / jQuery missing)
            document.addEventListener('click', function (e) {
                var t = e.target;
                var toggle = null;
                if (t && t.closest) {
                    toggle = t.closest('#mobileSidebarToggle') || t.closest('#menuToggle');
                }
                if (!toggle && t && (t.id === 'mobileSidebarToggle' || t.id === 'menuToggle')) {
                    toggle = t;
                }
                if (toggle && toggle.id === 'mobileSidebarToggle') {
                    e.preventDefault();
                    document.body.classList.remove('open');
                    document.body.classList.toggle('mobile-drawer-open');
                }
                var backdrop = document.getElementById('sidebar-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.id = 'sidebar-backdrop';
                    backdrop.className = 'sidebar-backdrop';
                    document.body.appendChild(backdrop);
                }
                var mobile = window.matchMedia('(max-width: 991px)').matches;
                var open = document.body.classList.contains('mobile-drawer-open');
                if (mobile) {
                    if (document.body.classList.contains('open')) {
                        document.body.classList.remove('open');
                        document.body.classList.add('mobile-drawer-open');
                        open = true;
                    }
                    backdrop.classList.toggle('is-visible', open);
                } else {
                    document.body.classList.remove('mobile-drawer-open');
                    backdrop.classList.remove('is-visible');
                }
                if (t && t.id === 'sidebar-backdrop') {
                    document.body.classList.remove('open', 'mobile-drawer-open');
                    t.classList.remove('is-visible');
                }
            });
        }
    });
})();
</script>

<!-- Universal Modal Close Handler - Fixes close button issues for all modals -->
<script>
(function() {
    'use strict';
    
    // Function to close modal - works with both Bootstrap 4 and 5
    function closeModal(modalElement) {
        if (!modalElement) return;

        var closed = false;

        // Prefer jQuery / Bootstrap 4 (Admin_nav loads BS4)
        if (typeof jQuery !== 'undefined' && jQuery.fn && typeof jQuery.fn.modal === 'function') {
            try {
                jQuery(modalElement).modal('hide');
                closed = true;
            } catch (e) {}
        }

        // Bootstrap 5 native (only if jQuery path unavailable)
        if (!closed && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                var modalInstance = (typeof bootstrap.Modal.getInstance === 'function')
                    ? bootstrap.Modal.getInstance(modalElement)
                    : null;
                if (modalInstance && typeof modalInstance.hide === 'function') {
                    modalInstance.hide();
                    closed = true;
                }
            } catch (e) {}
        }

        // Always hard-clean so X never appears "stuck"
        try {
            modalElement.classList.remove('show', 'in');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.removeAttribute('aria-modal');

            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                backdrop.remove();
            });
        } catch (e) {
            console.warn('Manual modal close failed:', e);
        }
    }
    
    // Handle all close button types
    function handleCloseClick(e) {
        var target = e.target;
        var closeBtn = null;
        
        // Check if clicked element is a close button or inside one
        var isCloseButton = function(el) {
            if (!el) return false;
            return el.matches && (
                el.matches('[data-bs-dismiss="modal"]') ||
                el.matches('[data-dismiss="modal"]') ||
                el.matches('.btn-close') ||
                el.matches('.close') ||
                el.matches('button.close') ||
                (el.tagName === 'SPAN' && (el.textContent === '×' || el.textContent === '✕') && el.closest('.close'))
            );
        };
        
        // Find the actual close button
        if (isCloseButton(target)) {
            closeBtn = target;
        } else if (target.closest) {
            closeBtn = target.closest('[data-bs-dismiss="modal"]') ||
                      target.closest('[data-dismiss="modal"]') ||
                      target.closest('.btn-close') ||
                      target.closest('.close') ||
                      target.closest('button.close');
        }
        
        if (closeBtn) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Find the modal
            var modal = closeBtn.closest('.modal');
            if (!modal) {
                // Try to find modal by ID if close button has data-target
                var targetId = closeBtn.getAttribute('data-target') || 
                              closeBtn.getAttribute('data-bs-target') ||
                              closeBtn.getAttribute('href');
                if (targetId) {
                    // Remove # if present
                    targetId = targetId.replace('#', '');
                    modal = document.getElementById(targetId) || document.querySelector('[id="' + targetId + '"]');
                }
            }
            
            if (modal) {
                closeModal(modal);
            }
            return false;
        }
    }
    
    // Initialize handlers
    function initModalCloseHandlers() {
        // Remove any existing handlers to avoid duplicates
        document.removeEventListener('click', handleCloseClick, true);
        
        // Add new handler with capture phase
        document.addEventListener('click', handleCloseClick, true);
        
        // Also handle Escape key
        function handleEscape(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                var openModals = document.querySelectorAll('.modal.show, .modal[aria-hidden="false"], .modal:not([style*="display: none"])');
                if (openModals.length > 0) {
                    var lastModal = openModals[openModals.length - 1];
                    closeModal(lastModal);
                }
            }
        }
        
        document.removeEventListener('keydown', handleEscape);
        document.addEventListener('keydown', handleEscape);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModalCloseHandlers);
    } else {
        initModalCloseHandlers();
    }
    
    // Re-initialize after AJAX loads (for dynamic modals)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxComplete(function() {
            setTimeout(initModalCloseHandlers, 100);
        });
        
        // Also re-initialize after modals are shown dynamically
        jQuery(document).on('shown.bs.modal', '.modal', function() {
            setTimeout(initModalCloseHandlers, 50);
        });
    }
    
    // Also handle when modals are dynamically added
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldReinit = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (node.classList.contains('modal') || node.querySelector('.modal'))) {
                            shouldReinit = true;
                        }
                    });
                }
            });
            if (shouldReinit) {
                setTimeout(initModalCloseHandlers, 50);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
</script>

<footer class="site-footer">
  <div class="container">
    <p class="mb-1">
      &copy; 2026 <strong>ShuleXpert</strong>. All Rights Reserved.
    </p>
    <p class="small mb-0">
      Powered by: <a href="#">EmCa Techonologies LTD
      </a> |
      <a href="mailto:support@yoursite.com">Contact Support</a>
    </p>
  </div>
</footer>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;

    // Universal AJAX Form Handler for SGPM and other modules
    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();

        // Show loading state
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', response.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        if (Array.isArray(xhr.responseJSON.errors)) {
                            errorMsg = xhr.responseJSON.errors.join('<br>');
                        } else {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg
                });
            },
            complete: function() {
                $submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    // Confirmation for Delete buttons
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $form = $btn.closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If it's an AJAX form, trigger submit, otherwise submit directly
                if ($form.hasClass('ajax-form')) {
                    $form.submit();
                } else {
                    // Manually submit non-ajax forms via DOM method
                    $form[0].submit();
                }
            }
        });
    });
});
</script>

 <script src="{{ asset('assets/js/main.js') }}"></script>

 <script src="{{ asset('assets/js/widgets.js') }}"></script>
<!-- ======= Custom Footer End ======= -->
