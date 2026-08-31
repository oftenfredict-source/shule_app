<!DOCTYPE html>
<html
  lang="en"
  class="light-style"
  dir="ltr"
  data-theme="theme-default"
>
  <head>
    @include('includes.theme_init')
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>@yield('title', 'ShuleXpert - Super Admin')</title>

    <meta name="description" content="ShuleXpert - Super Admin Portal" />
    
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

    <!-- Icons - Boxicons & FontAwesome CDN -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Core JS (jQuery first) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables (CDN) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom ShuleXpert Layout Styles -->
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
          transition: all 0.3s ease;
          box-shadow: 2px 0 12px rgba(148,0,0,0.07);
      }
      #corp-sidebar.collapsed {
          width: 0;
          left: -240px;
      }
      
      #corp-main-content { 
          margin-left: 240px; 
          transition: all 0.3s ease; 
          min-height: calc(100vh - 70px); 
          padding: 24px;
          background: #fcfcfc;
      }
      #corp-main-content.expanded {
          margin-left: 0;
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
          #corp-sidebar { 
              left: -240px; 
              transition: left 0.3s; 
              width: 240px !important;
          }
          #corp-sidebar.mobile-open { 
              left: 0; 
              box-shadow: 10px 0 30px rgba(0,0,0,0.2);
          }
          #corp-main-content { margin-left: 0 !important; }
          #corp-header-center { display: none; }
          #sidebar-toggle { display: block !important; }
      }

      /* Mobile Overlay Backdrop */
      #sidebar-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100vw;
          height: 100vh;
          background: rgba(0,0,0,0.4);
          z-index: 997;
          display: none;
      }
      #sidebar-overlay.show { display: block; }

      .footer-corp {
          background: #fff;
          border-top: 1px solid #eee;
          padding: 15px 0;
          font-size: 12px;
          color: #777;
          text-align: center;
          margin-top: 40px;
      }
    </style>
    
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/shulexpert-theme.css') }}?v={{ filemtime(public_path('css/shulexpert-theme.css')) }}">
  </head>

  <body>
    <div id="sidebar-overlay"></div>
    <!-- Corporate Header -->
    <header id="corp-header">
        <a id="corp-header-logo" href="{{ route('superAdminDashboard') }}">
            <img src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert" style="height: 40px; width: auto;" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            <div class="corp-logo-text">
                <div class="corp-logo-title">ShuleXpert</div>
                <div class="corp-logo-sub">SUPER ADMIN PORTAL</div>
            </div>
        </a>

        <div id="corp-header-center">
            <div class="corp-login-info">
                <div class="corp-login-label">ACCESSING PORTAL AS</div>
                <div class="corp-login-name">SUPER ADMIN</div>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:15px;">
            @php
                $customerInquiryUnread = 0;
                if (session('user_type') === 'SuperAdmin' && \Illuminate\Support\Facades\Schema::hasTable('customer_requests')) {
                    $customerInquiryUnread = \App\Models\CustomerRequest::where('is_read', false)->count();
                }
            @endphp

            <div class="dropdown" id="ci-notifications-wrap">
                <a href="#" class="position-relative d-inline-flex align-items-center justify-content-center text-decoration-none"
                   data-bs-toggle="dropdown" aria-expanded="false"
                   style="width:40px;height:40px;border:2px solid #940000;color:#940000;">
                    <i class="bx bx-bell" style="font-size:22px;"></i>
                    <span id="ci-notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $customerInquiryUnread > 0 ? '' : 'd-none' }}"
                          style="font-size:10px;min-width:18px;">{{ $customerInquiryUnread > 99 ? '99+' : $customerInquiryUnread }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width:min(360px,92vw);">
                    <li class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase" style="color:#940000;">Customer Inquiries</span>
                        <span class="badge bg-danger" id="ci-notif-count-label">{{ $customerInquiryUnread }} unread</span>
                    </li>
                    <li><div id="ci-notif-list" class="list-group list-group-flush" style="max-height:320px;overflow:auto;">
                        <div class="list-group-item small text-muted text-center py-4">Loading...</div>
                    </div></li>
                    <li class="border-top">
                        <a class="dropdown-item text-center fw-bold py-2" href="{{ route('superadmin.customer_inquiries') }}" style="color:#940000;">
                            View all inquiries
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Unified Sidebar Toggle -->
            <button id="sidebar-toggle" style="background:none; border:none; color:#940000; font-size:28px; cursor:pointer;">
                <i class="bx bx-menu"></i>
            </button>

            <div class="dropdown">
                <a href="#" data-bs-toggle="dropdown" style="display:flex; align-items:center; gap:8px; text-decoration:none;">
                    <div style="width:36px; height:36px; border:2px solid #940000; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#940000; overflow:hidden; background:#fff;">
                        SA
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('superadmin.change_password') }}"><i class="bx bx-lock me-2"></i>Change Password</a></li>
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
        </div>
    </header>

    <!-- Sidebar -->
    <nav id="corp-sidebar">
        <div class="sidebar-group-title">MAIN NAVIGATION</div>
        <a href="{{ route('superAdminDashboard') }}" class="sidebar-link {{ request()->routeIs('superAdminDashboard') ? 'active' : '' }}">
            <i class="bx bx-grid-alt"></i> Dashboard
        </a>

        <div class="sidebar-group-title">SCHOOL MANAGEMENT</div>
        <a href="{{ route('superadmin.schools.register') }}" class="sidebar-link {{ request()->routeIs('superadmin.schools.register') ? 'active' : '' }}">
            <i class="bx bx-plus-circle"></i> Register School
        </a>
        <a href="{{ route('superadmin.schools.registration_requests') }}" class="sidebar-link {{ request()->routeIs('superadmin.schools.registration_requests*') ? 'active' : '' }}">
            <i class="bx bx-file"></i> School Requests
        </a>
        <a href="{{ route('superadmin.schools.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.schools.index') ? 'active' : '' }}">
            <i class="bx bx-building"></i> Registered Schools
        </a>
        <a href="{{ route('superadmin.users') }}" class="sidebar-link {{ request()->routeIs('superadmin.users') ? 'active' : '' }}">
            <i class="bx bx-group"></i> User Management
        </a>

        <div class="sidebar-group-title">BANKING / FEES</div>
        <a href="{{ route('superadmin.banks.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.banks.*') ? 'active' : '' }}">
            <i class="bx bx-buildings"></i> Bank Registration
        </a>
        <a href="{{ route('superadmin.bank_accounts.verification') }}" class="sidebar-link {{ request()->routeIs('superadmin.bank_accounts.*') ? 'active' : '' }}">
            <i class="bx bx-check-shield"></i> Account Verification
        </a>
        <a href="{{ route('superadmin.nbc_integration_docs') }}" class="sidebar-link {{ request()->routeIs('superadmin.nbc_integration_docs*') ? 'active' : '' }}">
            <i class="bx bx-file-blank"></i> NBC Integration Docs
        </a>

        <div class="sidebar-group-title">SYSTEM TOOLS</div>
        <a href="{{ route('superadmin.user_password') }}" class="sidebar-link {{ request()->routeIs('superadmin.user_password') ? 'active' : '' }}">
            <i class="bx bx-key"></i> User Password
        </a>
        <a href="{{ route('superadmin.customer_care') }}" class="sidebar-link {{ request()->routeIs('superadmin.customer_care') ? 'active' : '' }}">
            <i class="bx bx-support"></i> Customer Care
        </a>
        <a href="{{ route('superadmin.customer_inquiries') }}" class="sidebar-link {{ request()->routeIs('superadmin.customer_inquiries*') ? 'active' : '' }}">
            <i class="bx bx-envelope"></i> Customer Inquiries
            @if(($customerInquiryUnread ?? 0) > 0)
                <span class="badge bg-danger ms-auto">{{ $customerInquiryUnread > 99 ? '99+' : $customerInquiryUnread }}</span>
            @endif
        </a>
        <a href="{{ route('superadmin.attendance_sms_schedule') }}" class="sidebar-link {{ request()->routeIs('superadmin.attendance_sms_schedule') ? 'active' : '' }}">
            <i class="bx bx-calendar-check"></i> Attendance SMS Schedule
        </a>
        <a href="{{ route('superadmin.fingerprint_attendance_setup.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.fingerprint_attendance_setup.*') ? 'active' : '' }}">
            <i class="bx bx-desktop"></i> Attendance Setup
        </a>
        <a href="{{ route('superadmin.system_alerts') }}" class="sidebar-link {{ request()->routeIs('superadmin.system_alerts') ? 'active' : '' }}">
            <i class="bx bx-bell"></i> System Alerts
        </a>
        <a href="{{ route('superadmin.system_logs') }}" class="sidebar-link {{ request()->routeIs('superadmin.system_logs') ? 'active' : '' }}">
            <i class="bx bx-shield-quarter"></i> System Logs
        </a>

        <div class="sidebar-group-title">SECURITY</div>
        <a href="{{ route('superadmin.change_password') }}" class="sidebar-link {{ request()->routeIs('superadmin.change_password') ? 'active' : '' }}">
            <i class="bx bx-lock-alt"></i> Change Password
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
            &copy; {{ date('Y') }} ShuleXpert | Powered by EmCa Techonology
        </footer>
    </main>

    <!-- Core JS CDNs (Popper & Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    
    <script>
        $(document).ready(function() {
            // Sidebar Toggle
            $('#sidebar-toggle').on('click', function() {
                if ($(window).width() <= 991) {
                    $('#corp-sidebar').toggleClass('mobile-open');
                    $('#sidebar-overlay').toggleClass('show');
                } else {
                    $('#corp-sidebar').toggleClass('collapsed');
                    $('#corp-main-content').toggleClass('expanded');
                }
            });

            // Close sidebar when clicking overlay on mobile
            $('#sidebar-overlay').on('click', function() {
                $('#corp-sidebar').removeClass('mobile-open');
                $(this).removeClass('show');
            });

            // Close sidebar on mobile when window is resized
            $(window).resize(function() {
                if($(window).width() > 991) {
                    $('#corp-sidebar').removeClass('mobile-open collapsed');
                    $('#corp-main-content').removeClass('expanded');
                }
            });

            // Idle Timer Logic
            const IDLE_MS = 270 * 1000;
            const WARN_SECONDS = 30;
            const LOGOUT_URL = '{{ route('logout') }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';

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
                    '<div style="width:min(520px,92vw);background:#fff;border-radius:0;padding:18px 18px 14px;box-shadow:0 20px 60px rgba(0,0,0,.25);font-family:inherit;">'
                    + '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">'
                    + '<div style="font-weight:800;color:#940000;">Security Warning</div>'
                    + '<button type="button" id="idleStayBtn" style="border:1px solid rgba(148,0,0,.3);background:#fff;color:#940000;border-radius:0;padding:6px 10px;cursor:pointer;">Stay Logged In</button>'
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
                idleTimer = setTimeout(showWarning, IDLE_MS);
            }

            function resetAll() {
                hideWarning();
                scheduleIdle();
            }

            ['mousemove','mousedown','keydown','scroll','touchstart','click'].forEach(evt => {
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

    <style>
        @media (max-width: 991px) {
            #mobile-toggle { display: block !important; }
        }
    </style>
  </body>
</html>
