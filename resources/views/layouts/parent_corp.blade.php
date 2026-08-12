<!DOCTYPE html>
<html
  lang="en"
  class="light-style"
  dir="ltr"
  data-theme="theme-default"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>@yield('title', 'ShuleXpert - Parent Portal')</title>

    <meta name="description" content="ShuleXpert - Parent Portal" />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @include('includes.site_favicon')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons - Boxicons CDN -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables (CDN) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom ShuleXpert Parent Styles (Self-Contained) -->
    <style>
      @font-face {
        font-family: 'Century Gothic';
        src: local('Century Gothic'), url('https://fonts.cdnfonts.com/s/16107/CenturyGothic.woff') format('woff');
      }

      * {
        font-family: 'Century Gothic', 'Public Sans', sans-serif !important;
      }

      body {
        background-color: #fcfcfc;
        overflow-x: hidden;
      }

      :root {
        --bs-primary: #940000 !important;
        --bs-primary-rgb: 148, 0, 0 !important;
        --bs-primary-hover: #a80000 !important;
      }
      
      /* Primary Color Overrides */
      .btn-primary, .bg-primary { 
        background-color: #940000 !important; 
        border-color: #940000 !important; 
        color: #fff !important;
      }
      .btn-primary:hover, .bg-primary:hover { 
        background-color: #a80000 !important; 
        border-color: #a80000 !important; 
      }
      .text-primary { color: #940000 !important; }
      .badge-primary { background-color: #940000 !important; }
      
      /* Absolute No Radius Override */
      .card, .card-header, .card-footer, .btn, .form-control, .form-select, 
      .badge, .alert, .nav-link, .nav-tabs, .nav-pills, .border, .avatar, 
      .rounded, .rounded-circle, .input-group-text, .avatar-initial,
      .dropdown-menu, .dropdown-item, .table, .list-group-item, .modal-content {
          border-radius: 0 !important;
      }

      /* === Sidebar Layout === */
      #corp-sidebar {
          position: fixed;
          top: 70px;
          left: 0;
          width: 240px;
          height: calc(100vh - 70px);
          background: #FFFFFF;
          border-right: 2px solid #940000;
          z-index: 998;
          overflow-y: auto;
          overflow-x: hidden;
          transition: width 0.25s ease;
          box-shadow: 2px 0 12px rgba(148,0,0,0.07);
      }
      #corp-sidebar::-webkit-scrollbar { width: 5px; }
      #corp-sidebar::-webkit-scrollbar-track { background: transparent; }
      #corp-sidebar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); }
      
      #corp-main-content { 
          margin-left: 240px; 
          transition: margin-left 0.25s ease; 
          min-height: calc(100vh - 70px); 
          padding: 24px;
          background: #fcfcfc;
      }

      .sidebar-group-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #940000; padding: 20px 16px 8px; opacity: 0.8; }
      .sidebar-link {
          display: flex; align-items: center; gap: 12px; padding: 12px 18px;
          color: #444; font-size: 13px; font-weight: 600; text-decoration: none;
          letter-spacing: 0.02em; border-left: 4px solid transparent;
          transition: all 0.2s;
      }
      .sidebar-link:hover { background: #FFF5F5; border-left-color: #940000; color: #940000; }
      .sidebar-link.active { background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%); border-left-color: #940000; color: #940000; font-weight: 700; }
      .sidebar-link i { font-size: 19px; }

      #corp-header {
          width: 100%;
          max-width: 100%;
          height: 70px;
          background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
          border-bottom: 3px solid #940000;
          display: flex;
          align-items: center;
          padding: 0 20px 0 16px;
          position: sticky;
          top: 0;
          z-index: 1000;
          box-shadow: 0 2px 12px rgba(148,0,0,0.10);
          gap: 8px;
          overflow: visible;
          box-sizing: border-box;
      }
      #corp-header-actions {
          display: flex;
          align-items: center;
          gap: 10px;
          margin-left: auto;
          flex-shrink: 0;
          min-width: 0;
          overflow: visible;
          position: relative;
          z-index: 1002;
          padding-right: 2px;
          max-width: 100%;
      }
      #corp-header-actions .dropdown {
          position: relative;
          flex-shrink: 0;
      }
      #corp-header-actions .dropdown-menu {
          z-index: 1100;
          width: min(360px, calc(100vw - 24px)) !important;
          max-width: calc(100vw - 24px) !important;
          right: 0 !important;
          left: auto !important;
          margin-top: 8px;
          box-sizing: border-box;
      }
      #parentHpNotifBtn {
          width: 36px;
          height: 36px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          overflow: visible;
          flex-shrink: 0;
      }
      #parentHpNotifBadge {
          top: 2px !important;
          right: 0 !important;
          left: auto !important;
          transform: translate(35%, -35%) !important;
          min-width: 16px;
          padding: 2px 4px;
      }
      #mobile-toggle {
          display: none;
          flex-shrink: 0;
          background: none;
          border: none;
          color: #940000;
          font-size: 28px;
          line-height: 1;
          padding: 4px;
          margin: 0;
          cursor: pointer;
      }
      .parent-profile-btn {
          display: flex;
          align-items: center;
          flex-shrink: 0;
          text-decoration: none;
          margin-right: 0;
      }
      .parent-profile-avatar {
          width: 36px;
          height: 36px;
          border: 2px solid #940000;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
          color: #940000;
          overflow: hidden;
          background: #fff;
          flex-shrink: 0;
          box-sizing: border-box;
      }
      .parent-profile-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          display: block;
      }

      /* Social strip under header (all parent blades) */
      .parent-school-social-bar {
          width: 100%;
          background: #fff;
          border-bottom: 1px solid #f0d0d0;
          padding: 8px 16px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-wrap: wrap;
          gap: 10px;
          position: sticky;
          top: 70px;
          z-index: 999;
          box-sizing: border-box;
      }
      .parent-sm-icon {
          width: 34px;
          height: 34px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          color: #fff !important;
          border: none;
          text-decoration: none;
          font-size: 18px;
          transition: transform 0.15s ease, opacity 0.15s ease;
          background: #940000;
          flex-shrink: 0;
      }
      .parent-sm-icon i {
          color: #fff !important;
      }
      .parent-sm-icon:hover {
          opacity: 0.9;
          transform: translateY(-1px);
          color: #fff !important;
      }
      .parent-sm-icon:hover i {
          color: #fff !important;
      }
      body.has-school-social #corp-sidebar { top: 114px; height: calc(100vh - 114px); }
      body.has-school-social #corp-main-content { min-height: calc(100vh - 114px); }

      #corp-header-logo { display:flex; align-items:center; gap:10px; text-decoration:none; min-width:0; flex-shrink:1; }
      #corp-header-logo img { height: 40px; width: auto; flex-shrink: 0; }
      #corp-header-logo .corp-logo-text { border-left:2px solid #940000; padding-left:10px; min-width:0; }
      #corp-header-logo .corp-logo-title { font-size:18px; font-weight:bold; color:#940000; line-height:1; white-space:nowrap; }
      #corp-header-logo .corp-logo-sub { font-size:10px; color:#940000; letter-spacing:0.05em; opacity:0.8; white-space:nowrap; }
      
      #corp-header-center { flex:1; display:flex; justify-content:center; min-width:0; }
      .corp-login-info { text-align:center; }
      .corp-login-label { font-size:10px; color:#940000; opacity:0.7; text-transform:uppercase; font-weight:700; letter-spacing:1px; }
      .corp-login-name { font-size:15px; font-weight:800; color:#940000; }
      .parent-ay-wrap { display:flex; gap:6px; align-items:center; flex-shrink:1; min-width:0; }
      .parent-ay-select { max-width: 120px; }
 
      @media (max-width: 991px) {
          #corp-sidebar { left: -240px; transition: left 0.3s; top: 64px; height: calc(100vh - 64px); }
          body.has-school-social #corp-sidebar { top: 108px; height: calc(100vh - 108px); }
          #corp-sidebar.mobile-open { left: 0; }
          #corp-main-content { margin-left: 0; padding: 16px; }
          #corp-header { height: 64px; padding: 0 12px 0 8px; gap: 4px; }
          #corp-header-center { display: none; }
          #mobile-toggle { display: block !important; }
          #corp-header-logo .corp-logo-text { display: none; }
          #corp-header-logo img { height: 34px; }
          .parent-school-social-bar { top: 64px; padding: 6px 10px; gap: 8px; }
          .parent-sm-icon { width: 30px; height: 30px; font-size: 16px; }
          .parent-ay-wrap { gap: 4px; }
          .parent-ay-select { min-width: 0 !important; max-width: 78px; font-size: 10px; padding: 2px 2px; }
          .parent-profile-avatar { width: 32px; height: 32px; }
          #parentHpNotifBtn { width: 32px; height: 32px; font-size: 22px !important; }
          #corp-header-actions { gap: 4px; padding-right: 0; }
          #corp-header-actions .dropdown-menu {
              position: fixed !important;
              top: 64px !important;
              left: 8px !important;
              right: 8px !important;
              width: auto !important;
              max-width: none !important;
              margin: 0 !important;
              transform: none !important;
          }
      }

      @media (max-width: 420px) {
          .parent-ay-wrap { display: none; }
      }

      .footer-corp {
          background: #fff;
          border-top: 1px solid #eee;
          padding: 15px 0;
          font-size: 12px;
          color: #777;
          text-align: center;
          margin-top: 40px;
      }

      /* Sneat-like Utility classes if needed */
      .shadow-sm { box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important; }
      .parent-hp-notif-item {
          display: flex;
          gap: 10px;
          align-items: flex-start;
          padding: 10px 14px;
          border-bottom: 1px solid #eee;
      }
      .parent-hp-notif-item.unread {
          background: #fff8f8;
      }
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
          border-radius: 2px;
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
      .parent-hp-notif-title {
          font-size: 12px;
          font-weight: 800;
          color: #940000;
          margin-bottom: 2px;
      }
      .parent-hp-notif-msg {
          font-size: 12px;
          color: #333;
          line-height: 1.35;
      }
      .parent-hp-notif-meta {
          font-size: 11px;
          color: #888;
          margin-top: 3px;
      }
    </style>
    
    @stack('styles')
  </head>

  @php
      $sessParentID = Session::get('parentID');
      $sessSchoolID = Session::get('schoolID');
      $navParent = $sessParentID ? \App\Models\ParentModel::where('parentID', $sessParentID)->where('schoolID', $sessSchoolID)->first() : null;
      $navParentName = $navParent ? ($navParent->first_name . ' ' . $navParent->last_name) : 'Parent User';
      $navParentPhoto = $navParent && $navParent->photo ? asset('userImages/' . $navParent->photo) : null;
      $navSchool = $sessSchoolID ? \App\Models\School::find($sessSchoolID) : null;
      $navSchoolSocial = $navSchool ? $navSchool->socialMediaLinks() : [];
  @endphp
  <body class="{{ !empty($navSchoolSocial) ? 'has-school-social' : '' }}">

    <!-- Corporate Header -->
    <header id="corp-header">
        <button type="button" id="mobile-toggle" aria-label="Toggle menu" onclick="document.getElementById('corp-sidebar').classList.toggle('mobile-open')">
            <i class="bx bx-menu"></i>
        </button>

        <a id="corp-header-logo" href="{{ route('parentDashboard') }}">
            <img src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            <div class="corp-logo-text">
                <div class="corp-logo-title">ShuleXpert</div>
                <div class="corp-logo-sub">PARENT PORTAL</div>
            </div>
        </a>

        <div id="corp-header-center">
            <div class="corp-login-info">
                <div class="corp-login-label">ACCESSING PORTAL AS</div>
                <div class="corp-login-name">{{ strtoupper($navParentName) }}</div>
            </div>
        </div>

        <div id="corp-header-actions">
            {{-- Academic year (header) --}}
            @php
                $pay = $headerAcademicYear ?? ['mode' => 'none'];
                $payMode = $pay['mode'] ?? 'none';
            @endphp
            @if($payMode !== 'none')
                <div class="parent-ay-wrap" id="parentAySelector" data-update-url="{{ route('selected_academic_year.update') }}">
                    @if(($payMode === 'primary') || ($payMode === 'secondary_o_only'))
                        <select class="form-select form-select-sm parent-ay-select" data-ay-role="primary" style="border:1px solid #940000;color:#940000;font-weight:700;">
                            @foreach(($pay['years'] ?? $pay['o_level_years'] ?? []) as $y)
                                <option value="{{ $y->academic_yearID }}" @selected((int)($pay['selected_academic_yearID'] ?? $pay['selected_o_level_academic_yearID']) === (int)$y->academic_yearID)>
                                    {{ $y->year_name ?: $y->year }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <select class="form-select form-select-sm parent-ay-select" data-ay-role="o" title="O Level" style="border:1px solid #940000;color:#940000;font-weight:700;">
                            @foreach(($pay['o_level_years'] ?? []) as $y)
                                <option value="{{ $y->academic_yearID }}" @selected((int)($pay['selected_o_level_academic_yearID'] ?? 0) === (int)$y->academic_yearID)>
                                    O: {{ $y->year_name ?: $y->year }}
                                </option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm parent-ay-select" data-ay-role="a" title="A Level" style="border:1px solid #940000;color:#940000;font-weight:700;">
                            @foreach(($pay['a_level_years'] ?? []) as $y)
                                <option value="{{ $y->academic_yearID }}" @selected((int)($pay['selected_a_level_academic_yearID'] ?? 0) === (int)$y->academic_yearID)>
                                    A: {{ $y->year_name ?: $y->year }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            @endif

            {{-- Parent notifications (results + home package) --}}
            <div class="dropdown">
                <button class="btn btn-link position-relative p-0" type="button" id="parentHpNotifBtn"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-expanded="false" aria-label="Notifications"
                        style="color:#940000;font-size:24px;text-decoration:none;line-height:1;">
                    <i class="bx bx-bell"></i>
                    <span id="parentHpNotifBadge" class="position-absolute badge rounded-pill bg-danger" style="font-size:10px;display:none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 shadow" aria-labelledby="parentHpNotifBtn">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="background:#fff5f5;">
                        <strong style="color:#940000;font-size:13px;">
                            <i class="bx bxs-bell me-1"></i> Arifa
                        </strong>
                        <button type="button" class="btn btn-sm btn-link p-0" id="parentHpMarkAllRead" style="color:#940000;font-size:12px;">Soma zote</button>
                    </div>
                    <div id="parentHpNotifList" style="max-height:360px;overflow-y:auto;">
                        <div class="p-3 text-muted small text-center">Inapakia...</div>
                    </div>
                    <div class="border-top px-3 py-2 d-flex justify-content-between gap-2 flex-wrap">
                        <a href="{{ route('parentResults') }}" class="small" style="color:#940000;font-weight:700;">
                            <i class="bx bx-trophy"></i> Matokeo →
                        </a>
                        <a href="{{ route('parentHomePackages') }}" class="small" style="color:#940000;font-weight:700;">
                            <i class="bx bx-home-smile"></i> Home Package →
                        </a>
                    </div>
                </div>
            </div>

            <!-- User -->
            <div class="dropdown">
                <a href="#" class="parent-profile-btn" data-bs-toggle="dropdown" aria-label="Profile menu">
                    <div class="parent-profile-avatar">
                        @if($navParentPhoto)
                            <img src="{{ $navParentPhoto }}" alt="">
                        @else
                            {{ substr($navParentName, 0, 1) }}
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bx bx-user me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('parent.permissions') }}"><i class="bx bx-check-shield me-2"></i>Ruksa</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bx bx-power-off me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    @if(!empty($navSchoolSocial))
        <div class="parent-school-social-bar" title="School social media">
            @foreach($navSchoolSocial as $sm)
                <a href="{{ $sm['href'] }}" target="_blank" rel="noopener noreferrer" class="parent-sm-icon" title="{{ $sm['label'] }}"
                   style="background: {{ $sm['color'] ?? '#940000' }}; color:#fff;">
                    <i class="bx {{ $sm['icon'] }}" style="color:#fff !important;"></i>
                </a>
            @endforeach
        </div>
    @endif

    <!-- Sidebar -->
    <nav id="corp-sidebar">
        <div class="sidebar-group-title">TAALUMA NA SHUGHULI</div>
        <a href="{{ route('parentDashboard') }}" class="sidebar-link {{ request()->routeIs('parentDashboard') ? 'active' : '' }}">
            <i class="bx bx-grid-alt"></i> Dashibodi
        </a>

        <!-- Matokeo Dropdown / Collapse -->
        <a class="sidebar-link {{ request()->routeIs('parentResults') || request()->routeIs('parentReport') ? 'active' : '' }}" data-bs-toggle="collapse" href="#matokeoCollapse" role="button" aria-expanded="{{ request()->routeIs('parentResults') || request()->routeIs('parentReport') ? 'true' : 'false' }}" aria-controls="matokeoCollapse">
            <i class="bx bx-trophy"></i> Matokeo
            <span id="parentResultsSidebarBadge" class="badge bg-danger ms-1" style="font-size:10px;display:none;">0</span>
            <i class="bx bx-chevron-down ms-auto" style="font-size: 14px;"></i>
        </a>
        <div class="collapse {{ request()->routeIs('parentResults') || request()->routeIs('parentReport') ? 'show' : '' }}" id="matokeoCollapse">
            <a href="{{ route('parentResults') }}" class="sidebar-link" style="padding-left: 45px; font-size: 12px; {{ request()->routeIs('parentResults') ? 'color: #940000; font-weight: bold;' : '' }}">
                <i class="bx bx-file-blank" style="font-size: 14px;"></i> Matokeo ya Mtihani
            </a>
            <a href="{{ route('parentReport') }}" class="sidebar-link" style="padding-left: 45px; font-size: 12px; {{ request()->routeIs('parentReport') ? 'color: #940000; font-weight: bold;' : '' }}">
                <i class="bx bx-book-open" style="font-size: 14px;"></i> Ripoti ya Mwanafunzi
            </a>
        </div>

        <a class="sidebar-link {{ request()->routeIs('parentAttendance') || request()->routeIs('parentStudentAttendance') || request()->routeIs('parentFingerprintAttendance') || request()->routeIs('parentStudentFingerprintAttendance') ? 'active' : '' }}" data-bs-toggle="collapse" href="#mahudhurioCollapse" role="button" aria-expanded="{{ request()->routeIs('parentAttendance') || request()->routeIs('parentStudentAttendance') || request()->routeIs('parentFingerprintAttendance') || request()->routeIs('parentStudentFingerprintAttendance') ? 'true' : 'false' }}" aria-controls="mahudhurioCollapse">
            <i class="bx bx-calendar-check"></i> Mahudhurio
            <i class="bx bx-chevron-down ms-auto" style="font-size: 14px;"></i>
        </a>
        <div class="collapse {{ request()->routeIs('parentAttendance') || request()->routeIs('parentStudentAttendance') || request()->routeIs('parentFingerprintAttendance') || request()->routeIs('parentStudentFingerprintAttendance') ? 'show' : '' }}" id="mahudhurioCollapse">
            <a href="{{ route('parentAttendance') }}" class="sidebar-link" style="padding-left: 45px; font-size: 12px; {{ request()->routeIs('parentAttendance') || request()->routeIs('parentStudentAttendance') ? 'color: #940000; font-weight: bold;' : '' }}">
                <i class="bx bx-clipboard" style="font-size: 14px;"></i> Mahudhurio ya Mfumo
            </a>
            <a href="{{ route('parentFingerprintAttendance') }}" class="sidebar-link" style="padding-left: 45px; font-size: 12px; {{ request()->routeIs('parentFingerprintAttendance') || request()->routeIs('parentStudentFingerprintAttendance') ? 'color: #940000; font-weight: bold;' : '' }}">
                <i class="bx bx-fingerprint" style="font-size: 14px;"></i> Alama za Vidole
            </a>
        </div>
        <a href="{{ route('parentSubjects') }}" class="sidebar-link {{ request()->routeIs('parentSubjects') ? 'active' : '' }}">
            <i class="bx bx-book"></i> Masomo
        </a>
        <a href="{{ route('parentHomePackages') }}" class="sidebar-link {{ request()->routeIs('parentHomePackages') ? 'active' : '' }}">
            <i class="bx bx-home-smile"></i> Home Package
            <span id="parentHpSidebarBadge" class="badge bg-danger ms-auto" style="font-size:10px;display:none;">0</span>
        </a>

        <div class="sidebar-group-title">FEDHA</div>
        <a href="{{ route('parentPayments') }}" class="sidebar-link {{ request()->routeIs('parentPayments') ? 'active' : '' }}">
            <i class="bx bx-credit-card"></i> Fanya Malipo
        </a>
        <a href="{{ route('parentFeesSummary') }}" class="sidebar-link {{ request()->routeIs('parentFeesSummary') ? 'active' : '' }}">
            <i class="bx bx-list-ul"></i> Muhtasari wa Ada
        </a>

        <div class="sidebar-group-title">AKAUNTI</div>
        <a href="{{ route('parent.permissions') }}" class="sidebar-link {{ request()->routeIs('parent.permissions') ? 'active' : '' }}">
            <i class="bx bx-check-shield"></i> Ruhusa
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-link w-100 border-0 bg-transparent text-start">
                <i class="bx bx-log-out"></i> Ondoka (Logout)
            </button>
        </form>
    </nav>

    <!-- Main Content -->
    <main id="corp-main-content">
        @yield('content')

        <footer class="footer-corp">
            &copy; {{ date('Y') }} ShuleXpert | Powered by EmCa Technologies LTD
        </footer>
    </main>

    {{-- Global Home Package viewer (works from any parent blade / notifications) --}}
    <div class="modal fade" id="globalParentHpViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="globalParentHpViewTitle">Home Package</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height:75vh;">
                    <iframe id="globalParentHpViewFrame" src="" style="width:100%;height:100%;border:0;"></iframe>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-outline-primary" id="globalParentHpDownloadBtn">Download</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Funga</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS CDNs -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    
    <script>
        $(document).ready(function() {
            $(window).resize(function() {
                if($(window).width() > 991) {
                    $('#corp-sidebar').removeClass('mobile-open');
                }
            });

            window.openParentHomePackageView = function(url, downloadUrl, title) {
                $('#globalParentHpViewTitle').text(title || 'Home Package');
                $('#globalParentHpViewFrame').attr('src', url);
                $('#globalParentHpDownloadBtn').attr('href', downloadUrl || '#');
                var el = document.getElementById('globalParentHpViewModal');
                bootstrap.Modal.getOrCreateInstance(el).show();
            };

            $('#globalParentHpViewModal').on('hidden.bs.modal', function() {
                $('#globalParentHpViewFrame').attr('src', '');
            });

            var notifUrl = @json(route('parent.header_notifications'));
            var markUrl = @json(route('parent.header_notifications.mark_read'));
            var csrf = $('meta[name="csrf-token"]').attr('content');

            function escapeHtml(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function renderHpNotifications(data) {
                var unread = data.unread || 0;
                var unreadResults = data.unread_results || 0;
                var unreadHp = data.unread_home_packages || 0;

                if (unread > 0) {
                    $('#parentHpNotifBadge').text(unread).show();
                } else {
                    $('#parentHpNotifBadge').hide();
                }

                if (unreadHp > 0) {
                    $('#parentHpSidebarBadge').text(unreadHp).show();
                } else {
                    $('#parentHpSidebarBadge').hide();
                }

                if (unreadResults > 0) {
                    $('#parentResultsSidebarBadge').text(unreadResults).show();
                } else {
                    $('#parentResultsSidebarBadge').hide();
                }

                var list = data.notifications || [];
                if (!list.length) {
                    $('#parentHpNotifList').html('<div class="p-3 text-muted small text-center">Hakuna arifa</div>');
                    return;
                }

                var html = '';
                list.forEach(function(n) {
                    if (n.source === 'result') {
                        html += '<a href="' + escapeHtml(n.url || '#') + '" class="parent-hp-notif-item text-decoration-none parent-result-notif-link' + (n.is_viewed ? '' : ' unread') + '" data-id="' + n.id + '" data-source="result" style="color:inherit;">'
                            + '<div class="parent-hp-notif-icon" title="Matokeo" style="background:#fff5f5;color:#940000;">'
                            +   '<i class="bx ' + escapeHtml(n.icon || 'bx-trophy') + '"></i>'
                            + '</div>'
                            + '<div class="parent-hp-notif-body">'
                            +   '<div class="parent-hp-notif-title"><i class="bx ' + escapeHtml(n.icon || 'bx-trophy') + '"></i> ' + escapeHtml(n.title || 'Matokeo') + '</div>'
                            +   '<div class="parent-hp-notif-msg">' + escapeHtml(n.message || '') + '</div>'
                            +   '<div class="parent-hp-notif-meta"><i class="bx bx-time-five"></i> ' + escapeHtml(n.created_at || '') + '</div>'
                            + '</div>'
                            + '</a>';
                        return;
                    }

                    var kind = n.file_kind || 'file';
                    var fileIcon = kind === 'pdf' ? 'bxs-file-pdf' : (kind === 'doc' ? 'bxs-file-doc' : 'bx-file');
                    var fileLabel = kind === 'pdf' ? 'PDF' : (kind === 'doc' ? 'DOC' : 'FILE');
                    html += '<div class="parent-hp-notif-item' + (n.is_viewed ? '' : ' unread') + '">'
                        + '<div class="parent-hp-notif-icon" title="' + fileLabel + '">'
                        +   '<i class="bx bxs-envelope"></i>'
                        +   '<span class="hp-file-badge ' + kind + '"><i class="bx ' + fileIcon + '"></i></span>'
                        + '</div>'
                        + '<div class="parent-hp-notif-body">'
                        +   '<div class="parent-hp-notif-title"><i class="bx bx-home-smile"></i> Home Package</div>'
                        +   '<div class="parent-hp-notif-msg">' + escapeHtml(n.message || '') + '</div>'
                        +   '<div class="parent-hp-notif-meta">'
                        +     '<i class="bx bx-time-five"></i> ' + escapeHtml(n.created_at || '')
                        +     ' · ' + escapeHtml(n.level_label || '')
                        +     (n.original_filename ? (' · <i class="bx ' + fileIcon + '"></i> ' + escapeHtml(n.original_filename)) : '')
                        +   '</div>'
                        +   '<div class="mt-2 d-flex gap-2 flex-wrap">'
                        +     (n.view_url ? ('<button type="button" class="btn btn-sm btn-outline-primary parent-hp-notif-view" data-id="' + n.id + '" data-url="' + escapeHtml(n.view_url) + '" data-dl="' + escapeHtml(n.download_url || '') + '" data-title="' + escapeHtml(n.holiday_name || 'Home Package') + '"><i class="bx bx-show"></i> View</button>') : '')
                        +     (n.download_url ? ('<a class="btn btn-sm btn-primary parent-hp-notif-dl" data-id="' + n.id + '" href="' + escapeHtml(n.download_url) + '"><i class="bx bx-download"></i> Download</a>') : '')
                        +   '</div>'
                        + '</div>'
                        + '</div>';
                });
                $('#parentHpNotifList').html(html);
            }

            function loadHpNotifications() {
                $.get(notifUrl).done(renderHpNotifications).fail(function() {
                    $('#parentHpNotifList').html('<div class="p-3 text-muted small text-center">Imeshindikana kupakia</div>');
                });
            }

            function markHeaderRead(opts) {
                opts = opts || {};
                return $.ajax({
                    url: markUrl,
                    method: 'POST',
                    data: {
                        _token: csrf,
                        source: opts.source || 'all',
                        id: opts.id || 'all',
                        all: opts.all ? 1 : 0
                    }
                });
            }

            function markHpRead(id) {
                return markHeaderRead({ source: 'home_package', id: id || 'all', all: id === 'all' });
            }

            loadHpNotifications();

            $(document).on('click', '.parent-result-notif-link', function() {
                var id = $(this).data('id');
                markHeaderRead({ source: 'result', id: id });
            });

            $(document).on('click', '.parent-hp-notif-view', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var id = $(this).data('id');
                var url = $(this).data('url');
                var dl = $(this).data('dl');
                var title = $(this).data('title');
                window.openParentHomePackageView(url, dl, title);
                markHpRead(id).always(loadHpNotifications);
            });

            $(document).on('click', '.parent-hp-notif-dl', function() {
                var id = $(this).data('id');
                markHpRead(id).always(loadHpNotifications);
            });

            $('#parentHpMarkAllRead').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                markHeaderRead({ source: 'all', id: 'all', all: true }).done(function() {
                    loadHpNotifications();
                });
            });

            // Academic year change
            $('.parent-ay-select').on('change', function() {
                var $wrap = $('#parentAySelector');
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
                $.post(url, payload).done(function() {
                    location.reload();
                }).fail(function() {
                    alert('Imeshindikana kubadilisha academic year');
                });
            });
        });
    </script>
  </body>
</html>
