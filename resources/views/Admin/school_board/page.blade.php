@php
    $user_type = $user_type ?? Session::get('user_type');
@endphp
@if($user_type == 'Admin')
    @include('includes.Admin_nav')
@elseif($user_type == 'Staff')
    @include('includes.staff_nav')
@else
    @include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .sx-board-wrap { padding: 1rem 1.25rem 2rem; }
    .sx-board-hero {
        background: linear-gradient(135deg, #940000 0%, #6b0000 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }
    .sx-board-hero h1 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0 0 0.35rem;
    }
    .sx-board-hero p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.92rem;
    }
    .sx-board-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1rem;
    }
    .sx-board-nav a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border: 1px solid #e8e8e8;
        background: #fff;
        color: #333;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .sx-board-nav a:hover { border-color: #940000; color: #940000; }
    .sx-board-nav a.active {
        background: #940000;
        border-color: #940000;
        color: #fff;
    }
    .sx-board-card {
        background: #fff;
        border: 1px solid #eee;
        padding: 1.5rem;
        min-height: 220px;
    }
    .sx-board-card h2 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #940000;
        margin: 0 0 0.5rem;
    }
    .sx-board-empty {
        margin-top: 1rem;
        padding: 1.25rem;
        border: 1px dashed rgba(148, 0, 0, 0.35);
        background: #fff8f8;
        color: #555;
    }
    .sx-board-empty i { color: #940000; margin-right: 6px; }
</style>

<div class="sx-board-wrap">
    <div class="sx-board-hero">
        <h1><i class="fa fa-university"></i> School Board</h1>
        <p>{{ $subtitle ?: 'School board governance workspace' }}</p>
    </div>

    <div class="sx-board-nav">
        @foreach($menu as $item)
            <a href="{{ route($item['route']) }}" class="{{ $section === $item['key'] ? 'active' : '' }}">
                <i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    <div class="sx-board-card">
        <h2>{{ $title }}</h2>
        <p class="text-muted mb-0">{{ $subtitle }}</p>
        <div class="sx-board-empty">
            <i class="fa fa-info-circle"></i>
            This section is ready. Records and management tools for <strong>{{ $title }}</strong> will appear here.
        </div>
    </div>
</div>

@include('includes.footer')
