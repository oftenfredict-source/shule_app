<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'School Board Portal' }} — {{ $schoolName ?? 'ShuleXpert' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root {
            --bp-red: #940000;
            --bp-red-mid: #b30000;
            --bp-red-dark: #5c0000;
            --bp-ink: #161616;
            --bp-muted: #6a6a6a;
            --bp-line: #ebe7e2;
            --bp-paper: #f4f1ed;
            --bp-card: #ffffff;
            --bp-sidebar: #1a0a0a;
            --bp-sidebar-hover: rgba(255,255,255,0.06);
            --bp-sidebar-w: 268px;
            --bp-top-h: 64px;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: "Century Gothic", CenturyGothic, AppleGothic, Arial, sans-serif;
            background: var(--bp-paper);
            color: var(--bp-ink);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* —— App shell —— */
        .bp-app {
            display: flex;
            min-height: 100vh;
        }

        /* —— Sidebar —— */
        .bp-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--bp-sidebar-w);
            background:
                linear-gradient(165deg, rgba(148,0,0,0.35) 0%, transparent 42%),
                linear-gradient(180deg, #2a0c0c 0%, var(--bp-sidebar) 55%, #0d0606 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 8px 0 32px rgba(0,0,0,0.18);
            transition: transform 0.28s ease;
        }
        .bp-sidebar-brand {
            padding: 1.35rem 1.25rem 1.1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .bp-sidebar-brand .eyebrow {
            margin: 0 0 0.35rem;
            font-size: 0.65rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.55);
            font-weight: 700;
        }
        .bp-sidebar-brand h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .bp-sidebar-brand h1 span {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255,255,255,0.65);
            margin-top: 0.2rem;
            letter-spacing: 0;
        }
        .bp-member-chip {
            margin: 1rem 1rem 0.5rem;
            padding: 0.85rem 0.95rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        .bp-avatar {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--bp-red) 0%, var(--bp-red-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
        }
        .bp-member-chip .meta { min-width: 0; }
        .bp-member-chip .meta strong {
            display: block;
            font-size: 0.88rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bp-member-chip .meta small {
            display: block;
            margin-top: 2px;
            font-size: 0.72rem;
            color: rgba(255,200,200,0.85);
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .bp-nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.65rem 0.75rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .bp-nav-label {
            margin: 0.65rem 0.65rem 0.35rem;
            font-size: 0.62rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            font-weight: 700;
        }
        .bp-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.72rem 0.9rem;
            color: rgba(255,255,255,0.78);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
            border-left: 3px solid transparent;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
        .bp-nav a i {
            width: 18px;
            text-align: center;
            font-size: 1rem;
            opacity: 0.85;
        }
        .bp-nav a:hover {
            background: var(--bp-sidebar-hover);
            color: #fff;
        }
        .bp-nav a.active {
            background: linear-gradient(90deg, rgba(148,0,0,0.45) 0%, rgba(148,0,0,0.12) 100%);
            color: #fff;
            border-left-color: #ff6b6b;
        }
        .bp-nav a.active i { opacity: 1; }
        .bp-nav-badge {
            margin-left: auto;
            min-width: 1.35rem;
            height: 1.35rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            background: #ff6b6b;
            color: #fff;
            font-size: 0.68rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .bp-btn .bp-nav-badge {
            margin-left: 0.35rem;
            vertical-align: middle;
        }

        .bp-sidebar-foot {
            padding: 0.85rem 1rem 1.15rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .bp-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 0.7rem 1rem;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.18);
            color: rgba(255,255,255,0.85);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .bp-logout:hover {
            background: rgba(148,0,0,0.5);
            border-color: rgba(255,255,255,0.35);
            color: #fff;
        }

        /* —— Main —— */
        .bp-main {
            flex: 1;
            margin-left: var(--bp-sidebar-w);
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .bp-topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            height: var(--bp-top-h);
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--bp-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0 1.5rem;
        }
        .bp-topbar-left {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
        }
        .bp-menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid var(--bp-line);
            background: #fff;
            color: var(--bp-red);
            font-size: 1.1rem;
            cursor: pointer;
            flex-shrink: 0;
        }
        .bp-page-heading h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--bp-ink);
            letter-spacing: -0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bp-page-heading p {
            margin: 0.15rem 0 0;
            font-size: 0.8rem;
            color: var(--bp-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 48vw;
        }
        .bp-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-shrink: 0;
        }
        .bp-school-pill {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--bp-red);
            background: #fff5f5;
            border: 1px solid rgba(148,0,0,0.15);
            padding: 0.4rem 0.75rem;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bp-content {
            flex: 1;
            padding: 1.35rem 1.5rem 2.5rem;
            max-width: 1180px;
            width: 100%;
        }

        /* —— Shared UI —— */
        .bp-flash {
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid;
        }
        .bp-flash.ok { background: #f0faf3; border-color: #b7e0c0; color: #1a5c2e; }
        .bp-flash.err { background: #fff5f5; border-color: #f0c2c2; color: #8a1a1a; }

        .bp-card {
            background: var(--bp-card);
            border: 1px solid var(--bp-line);
            padding: 1.15rem 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 0 rgba(0,0,0,0.02);
        }
        .bp-card h2,
        .bp-card .bp-card-title {
            margin: 0 0 0.9rem;
            font-size: 1rem;
            color: var(--bp-red);
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .bp-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 1.15rem;
        }
        .bp-stat {
            background: var(--bp-card);
            border: 1px solid var(--bp-line);
            padding: 1rem 1.1rem;
            position: relative;
            overflow: hidden;
        }
        .bp-stat::before {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--bp-red);
        }
        .bp-stat .label {
            font-size: 0.68rem;
            color: var(--bp-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.06em;
        }
        .bp-stat .value {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--bp-red);
            margin-top: 4px;
            letter-spacing: -0.03em;
        }
        .bp-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            background: #fff5f5;
            color: var(--bp-red);
            border: 1px solid rgba(148,0,0,0.2);
        }
        .bp-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .bp-table th, .bp-table td {
            border-bottom: 1px solid var(--bp-line);
            padding: 0.7rem 0.45rem;
            text-align: left;
            vertical-align: top;
        }
        .bp-table th {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bp-muted);
            font-weight: 700;
        }
        .bp-table tbody tr:hover td { background: #faf9f7; }
        .bp-muted { color: var(--bp-muted); }
        .bp-empty { text-align: center; padding: 1.75rem 1rem; color: var(--bp-muted); font-size: 0.9rem; }
        .bp-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 0;
            background: var(--bp-red);
            color: #fff;
            padding: 0.55rem 1rem;
            font-size: 0.82rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }
        .bp-btn:hover { background: var(--bp-red-mid); color: #fff; }
        .bp-btn-outline {
            background: #fff;
            color: var(--bp-red);
            border: 1px solid rgba(148,0,0,0.35);
        }
        .bp-btn-outline:hover { background: #fff5f5; color: var(--bp-red); }
        .bp-select {
            font-family: inherit;
            font-size: 0.85rem;
            padding: 5px 8px;
            border: 1px solid var(--bp-line);
            background: #fff;
        }
        .bp-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .bp-list-row {
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--bp-line);
        }
        .bp-list-row:last-child { border-bottom: 0; }
        .bp-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 90;
        }
        .bp-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1rem;
        }
        .bp-form-grid .full { grid-column: 1 / -1; }
        .bp-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--bp-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .bp-input, .bp-textarea, .bp-select {
            width: 100%;
            font-family: inherit;
            font-size: 0.9rem;
            padding: 0.55rem 0.7rem;
            border: 1px solid var(--bp-line);
            background: #fff;
            color: var(--bp-ink);
        }
        .bp-textarea { min-height: 88px; resize: vertical; }
        .bp-check-list {
            max-height: 140px;
            overflow-y: auto;
            border: 1px solid var(--bp-line);
            padding: 0.5rem 0.65rem;
            background: #faf9f7;
        }
        .bp-check-list label {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 0.85rem;
            padding: 3px 0;
        }
        .bp-card-head {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }
        .bp-card-head h2 { margin: 0; }
        .bp-create-panel {
            border-top: 1px solid var(--bp-line);
            margin-top: 1rem;
            padding-top: 1rem;
            display: none;
        }
        .bp-create-panel.is-open { display: block; }
        @media (max-width: 700px) {
            .bp-form-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 1100px) {
            .bp-stats { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 900px) {
            .bp-grid-2 { grid-template-columns: 1fr; }
            .bp-menu-toggle { display: inline-flex; align-items: center; justify-content: center; }
            .bp-sidebar { transform: translateX(-105%); }
            body.bp-drawer-open .bp-sidebar { transform: translateX(0); }
            body.bp-drawer-open .bp-backdrop { display: block; }
            .bp-main { margin-left: 0; }
            .bp-content { padding: 1rem 1rem 2rem; }
            .bp-school-pill { display: none; }
            .bp-page-heading p { max-width: 55vw; }
        }
        @media (max-width: 520px) {
            .bp-stats { grid-template-columns: 1fr 1fr; gap: 8px; }
            .bp-stat .value { font-size: 1.35rem; }
            .bp-topbar { padding: 0 0.85rem; }
        }
    </style>
    @yield('head')
</head>
<body>
@php
    $memberName = $member->full_name ?? Session::get('board_member_name', 'Board Member');
    $positionName = $member->position->position_name ?? Session::get('board_position_name', 'Member');
    $initials = collect(preg_split('/\s+/', trim($memberName)))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    if ($initials === '') {
        $initials = 'BM';
    }
@endphp

<div class="bp-backdrop" id="bpBackdrop" aria-hidden="true"></div>

<div class="bp-app">
    <aside class="bp-sidebar" id="bpSidebar" aria-label="Board portal navigation">
        <div class="bp-sidebar-brand">
            <p class="eyebrow">ShuleXpert</p>
            <h1>
                Board Portal
                <span>{{ $schoolName ?? 'School Board' }}</span>
            </h1>
        </div>

        <div class="bp-member-chip">
            <div class="bp-avatar" aria-hidden="true">{{ $initials }}</div>
            <div class="meta">
                <strong title="{{ $memberName }}">{{ $memberName }}</strong>
                <small>{{ $positionName }}</small>
            </div>
        </div>

        <nav class="bp-nav">
            <div class="bp-nav-label">Menu</div>
            @foreach(($menu ?? []) as $item)
                <a href="{{ route($item['route']) }}" class="{{ ($section ?? '') === $item['key'] ? 'active' : '' }}">
                    <i class="fa {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    @if(!empty($item['badge']))
                        <span class="bp-nav-badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="bp-sidebar-foot">
            <a class="bp-logout" href="{{ route('logout') }}">
                <i class="fa fa-sign-out"></i> Sign out
            </a>
        </div>
    </aside>

    <div class="bp-main">
        <header class="bp-topbar">
            <div class="bp-topbar-left">
                <button type="button" class="bp-menu-toggle" id="bpMenuToggle" aria-label="Open menu">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="bp-page-heading">
                    <h2>{{ $title ?? 'Dashboard' }}</h2>
                    @if(!empty($subtitle))
                        <p>{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="bp-topbar-right">
                <span class="bp-school-pill" title="{{ $schoolName ?? '' }}">
                    <i class="fa fa-university"></i> {{ $schoolName ?? 'School' }}
                </span>
            </div>
        </header>

        <main class="bp-content">
            @if(session('success'))
                <div class="bp-flash ok">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bp-flash err">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
(function () {
    var toggle = document.getElementById('bpMenuToggle');
    var backdrop = document.getElementById('bpBackdrop');
    function closeDrawer() {
        document.body.classList.remove('bp-drawer-open');
    }
    function openDrawer() {
        document.body.classList.add('bp-drawer-open');
    }
    if (toggle) {
        toggle.addEventListener('click', function () {
            if (document.body.classList.contains('bp-drawer-open')) closeDrawer();
            else openDrawer();
        });
    }
    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    document.querySelectorAll('.bp-nav a').forEach(function (a) {
        a.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 900px)').matches) closeDrawer();
        });
    });
})();
</script>
@yield('scripts')
</body>
</html>
