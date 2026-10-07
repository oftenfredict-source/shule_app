<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Student Portal') | {{ $school->school_name ?? 'ShuleXpert' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}?v=6">
</head>
<body class="sp-body">
<div class="sp-shell">
@isset($school)
@php
    $routeName = request()->route()?->getName() ?? '';
    $initials = strtoupper(mb_substr($student->first_name ?? 'S', 0, 1) . mb_substr($student->last_name ?? 'P', 0, 1));
    $schoolLogoUrl = null;
    if (!empty($school->school_logo)) {
        $logoRel = ltrim(str_replace('\\', '/', $school->school_logo), '/');
        foreach ([$logoRel, 'schoolLogos/' . basename($logoRel)] as $rel) {
            if (is_file(public_path($rel))) {
                $schoolLogoUrl = asset($rel);
                break;
            }
        }
    }
@endphp
<header class="sp-header">
    <div class="sp-header-bar">
        <a href="{{ route('student_portal.home') }}" class="sp-brand">
            @if($schoolLogoUrl)
                <img class="sp-brand-logo" src="{{ $schoolLogoUrl }}" alt="">
            @else
                <span class="sp-brand-mark" aria-hidden="true">S</span>
            @endif
            <span class="sp-brand-copy">
                <span class="sp-brand-title">{{ $school->school_name }}</span>
                <span class="sp-brand-tag">Student Portal</span>
            </span>
        </a>

        <nav class="sp-menu" aria-label="Portal">
            <a href="{{ route('student_portal.home') }}" class="{{ $routeName === 'student_portal.home' ? 'is-active' : '' }}">Home</a>
            <a href="{{ route('student_portal.notes') }}" class="{{ str_starts_with($routeName, 'student_portal.note') ? 'is-active' : '' }}">Notes</a>
            <a href="{{ route('student_portal.books') }}" class="{{ str_starts_with($routeName, 'student_portal.book') ? 'is-active' : '' }}">Books</a>
            <a href="{{ route('student_portal.quizzes') }}" class="{{ str_starts_with($routeName, 'student_portal.quiz') ? 'is-active' : '' }}">Quizzes</a>
        </nav>

        <div class="sp-header-end">
            <div class="sp-user" title="{{ trim(($student->first_name ?? '').' '.($student->last_name ?? '')) }} · {{ $classLabel ?? '' }}">
                <span class="sp-user-avatar">{{ $initials }}</span>
                <span class="sp-user-meta">
                    <span class="sp-user-name">{{ $student->first_name }}</span>
                    <span class="sp-user-class">{{ $classLabel ?? '' }}</span>
                </span>
            </div>
            <a href="{{ route('student_portal.logout') }}" class="sp-logout-btn">Logout</a>
        </div>
    </div>
</header>
@endisset

<main class="@yield('main_class', 'sp-main')">
    @if(session('success'))<div class="sp-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="sp-flash err">{{ session('error') }}</div>@endif
    @yield('content')
</main>
</div>
@yield('scripts')
</body>
</html>
