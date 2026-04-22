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
          height: 70px;
          background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
          border-bottom: 3px solid #940000;
          display: flex;
          align-items: center;
          padding: 0 24px;
          position: sticky;
          top: 0;
          z-index: 1000;
          box-shadow: 0 2px 12px rgba(148,0,0,0.10);
      }
      #corp-header-logo { display:flex; align-items:center; gap:12px; text-decoration:none; }
      #corp-header-logo .corp-logo-text { border-left:2px solid #940000; padding-left:12px; }
      #corp-header-logo .corp-logo-title { font-size:18px; font-weight:bold; color:#940000; line-height:1; }
      #corp-header-logo .corp-logo-sub { font-size:10px; color:#940000; letter-spacing:0.05em; opacity:0.8; }
      
      #corp-header-center { flex:1; display:flex; justify-content:center; }
      .corp-login-info { text-align:center; }
      .corp-login-label { font-size:10px; color:#940000; opacity:0.7; text-transform:uppercase; font-weight:700; letter-spacing:1px; }
      .corp-login-name { font-size:15px; font-weight:800; color:#940000; }
 
      @media (max-width: 991px) {
          #corp-sidebar { left: -240px; transition: left 0.3s; }
          #corp-sidebar.mobile-open { left: 0; }
          #corp-main-content { margin-left: 0; }
          #corp-header-center { display: none; }
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
      .dropdown-menu { border: 1px solid #eee; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    </style>
    
    @stack('styles')
  </head>

  <body>
    @php
        $sessParentID = Session::get('parentID');
        $sessSchoolID = Session::get('schoolID');
        $navParent = $sessParentID ? \App\Models\ParentModel::where('parentID', $sessParentID)->where('schoolID', $sessSchoolID)->first() : null;
        $navParentName = $navParent ? ($navParent->first_name . ' ' . $navParent->last_name) : 'Parent User';
        $navParentPhoto = $navParent && $navParent->photo ? asset('userImages/' . $navParent->photo) : null;
    @endphp

    <!-- Corporate Header -->
    <header id="corp-header">
        <a id="corp-header-logo" href="{{ route('parentDashboard') }}">
            <img src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert" style="height: 40px; width: auto;" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
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

        <div style="display:flex; align-items:center; gap:15px;">
            <!-- User -->
            <div class="dropdown">
                <a href="#" data-bs-toggle="dropdown" style="display:flex; align-items:center; gap:8px; text-decoration:none;">
                    <div style="width:36px; height:36px; border:2px solid #940000; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#940000; overflow:hidden; background:#fff;">
                        @if($navParentPhoto)
                            <img src="{{ $navParentPhoto }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ substr($navParentName, 0, 1) }}
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bx bx-user me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('parent.permissions') }}"><i class="bx bx-check-shield me-2"></i>Permissions</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bx bx-power-off me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>

            <button id="mobile-toggle" onclick="document.getElementById('corp-sidebar').classList.toggle('mobile-open')" style="display:none; background:none; border:none; color:#940000; font-size:28px;">
                <i class="bx bx-menu"></i>
            </button>
        </div>
    </header>

    <!-- Sidebar -->
    <nav id="corp-sidebar">
        <div class="sidebar-group-title">ACADEMIC & ACTIVITIES</div>
        <a href="{{ route('parentDashboard') }}" class="sidebar-link {{ request()->routeIs('parentDashboard') ? 'active' : '' }}">
            <i class="bx bx-grid-alt"></i> Dashboard
        </a>
        <a href="{{ route('parentResults') }}" class="sidebar-link {{ request()->routeIs('parentResults') ? 'active' : '' }}">
            <i class="bx bx-trophy"></i> Exam Results
        </a>
        <a href="{{ route('parentAttendance') }}" class="sidebar-link {{ request()->routeIs('parentAttendance') ? 'active' : '' }}">
            <i class="bx bx-calendar-check"></i> Attendance
        </a>
        <a href="{{ route('parentSubjects') }}" class="sidebar-link {{ request()->routeIs('parentSubjects') ? 'active' : '' }}">
            <i class="bx bx-book"></i> Subjects
        </a>

        <div class="sidebar-group-title">FINANCIAL</div>
        <a href="{{ route('parentPayments') }}" class="sidebar-link {{ request()->routeIs('parentPayments') ? 'active' : '' }}">
            <i class="bx bx-credit-card"></i> Make Payments
        </a>
        <a href="{{ route('parentFeesSummary') }}" class="sidebar-link {{ request()->routeIs('parentFeesSummary') ? 'active' : '' }}">
            <i class="bx bx-list-ul"></i> Fees Summary
        </a>

        <div class="sidebar-group-title">ACCOUNT</div>
        <a href="{{ route('parent.permissions') }}" class="sidebar-link {{ request()->routeIs('parent.permissions') ? 'active' : '' }}">
            <i class="bx bx-check-shield"></i> Permissions
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-link w-100 border-0 bg-transparent text-start">
                <i class="bx bx-log-out"></i> Logout
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

    <!-- Core JS CDNs -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    
    <script>
        $(document).ready(function() {
            // Close sidebar on mobile when window is resized
            $(window).resize(function() {
                if($(window).width() > 991) {
                    $('#corp-sidebar').removeClass('mobile-open');
                }
            });
        });
    </script>

    <style>
        @media (max-width: 991px) {
            #mobile-toggle { display: block !important; }
        }
    </style>
  </body>
</html>
