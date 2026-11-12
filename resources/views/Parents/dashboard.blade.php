@extends('layouts.parent_corp')

@section('title', __('parent.dashboard_title') . ' - ShuleXpert')
@section('page_title', __('parent.dashboard_title'))
@section('page_breadcrumb', __('parent.nav_dashboard'))

@section('content')
@php
    $parentFullName = trim(($parent->first_name ?? '') . ' ' . ($parent->middle_name ?? '') . ' ' . ($parent->last_name ?? ''));
    $parentInitial = strtoupper(substr($parentFullName !== '' ? $parentFullName : 'P', 0, 1));
    $parentPhoto = !empty($parent->photo) ? asset('userImages/' . $parent->photo) : null;
    $activeShare = $totalStudents > 0 ? (int) round(($activeStudents / $totalStudents) * 100) : 0;
    $maleShare = $totalStudents > 0 ? (int) round(($maleStudents / $totalStudents) * 100) : 0;
    $femaleShare = $totalStudents > 0 ? (int) round(($femaleStudents / $totalStudents) * 100) : 0;
@endphp

<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash span:not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .stat-value,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .fw-semibold,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .fw-bold,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .text-dark,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .quick-tool-link,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .list-row,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .btn-link {
        color: #000 !important;
    }
    .sx-parent-dash .btn-primary,
    .sx-parent-dash a.btn-primary,
    .sx-parent-dash .text-white,
    .sx-parent-dash .badge,
    .sx-parent-dash .avatar,
    .sx-parent-dash .avatar span,
    .sx-parent-dash .avatar i,
    .sx-parent-dash .avatar i:before,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .avatar,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .avatar span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .avatar i,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-dash .avatar i:before {
        color: #fff !important;
    }
    .sx-parent-dash .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .sx-parent-dash a.stat-card {
        display: block;
        text-decoration: none;
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        transition: box-shadow 0.15s ease, transform 0.15s ease;
        cursor: pointer;
    }
    .sx-parent-dash a.stat-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
    }
    .sx-parent-dash a.stat-card .card-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .sx-parent-dash .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .sx-parent-dash .stat-value {
        display: block;
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .sx-parent-dash .avatar {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        overflow: hidden;
    }
    .sx-parent-dash .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sx-parent-dash .bg-brand {
        background: #940000 !important;
    }
    .sx-parent-dash .quick-tool-link {
        text-decoration: none;
        color: inherit;
    }
    .sx-parent-dash .quick-tool-link:hover {
        color: inherit;
    }
    .sx-parent-dash .quick-tool-link .list-row:hover {
        background: rgba(148, 0, 0, 0.05);
        border-radius: 0.75rem;
    }
    .sx-parent-dash .list-row {
        padding: 0.7rem 0.55rem;
        transition: background 0.15s ease;
    }
    .sx-parent-dash .env-bar {
        height: 12px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(148, 0, 0, 0.1);
    }
    .sx-parent-dash .env-bar > span {
        display: block;
        height: 100%;
        background: #940000;
    }
    .sx-parent-dash .gender-split-bar {
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        display: flex;
        background: rgba(148, 0, 0, 0.08);
    }
    .sx-parent-dash .gender-split-bar > span {
        display: block;
        height: 100%;
    }
    .sx-parent-dash .gender-split-bar > .is-male {
        background: #940000;
    }
    .sx-parent-dash .gender-split-bar > .is-female {
        background: #e8b4b4;
    }
    .sx-parent-dash .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-dash .btn-link {
        font-weight: 600;
        text-decoration: none;
        color: #940000 !important;
    }
    .sx-parent-dash .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-dash .child-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0.75rem 0.35rem;
        border-bottom: 1px solid rgba(148, 0, 0, 0.08);
    }
    .sx-parent-dash .child-row:last-child {
        border-bottom: 0;
    }
    .sx-parent-dash .child-avatar {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #940000;
        font-weight: 800;
    }
    .sx-parent-dash .child-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sx-parent-dash .table thead th {
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 800;
        color: #940000 !important;
        background: #fcf7f7;
        border-bottom-color: rgba(148, 0, 0, 0.12);
        white-space: nowrap;
    }
    .sx-parent-dash .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .sx-parent-dash .notif-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    html[data-sx-theme="dark"] .sx-parent-dash .card,
    html[data-bs-theme="dark"] .sx-parent-dash .card,
    html[data-sx-theme="dark"] .sx-parent-dash .panel-card,
    html[data-bs-theme="dark"] .sx-parent-dash .panel-card,
    html[data-sx-theme="dark"] .sx-parent-dash .notif-strip,
    html[data-bs-theme="dark"] .sx-parent-dash .notif-strip {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash a.stat-card,
    html[data-bs-theme="dark"] .sx-parent-dash a.stat-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash,
    html[data-bs-theme="dark"] .sx-parent-dash,
    html[data-sx-theme="dark"] .sx-parent-dash p,
    html[data-bs-theme="dark"] .sx-parent-dash p,
    html[data-sx-theme="dark"] .sx-parent-dash span:not(.badge):not(.avatar),
    html[data-bs-theme="dark"] .sx-parent-dash span:not(.badge):not(.avatar),
    html[data-sx-theme="dark"] .sx-parent-dash small,
    html[data-bs-theme="dark"] .sx-parent-dash small,
    html[data-sx-theme="dark"] .sx-parent-dash strong,
    html[data-bs-theme="dark"] .sx-parent-dash strong,
    html[data-sx-theme="dark"] .sx-parent-dash div:not(.avatar):not(.badge),
    html[data-bs-theme="dark"] .sx-parent-dash div:not(.avatar):not(.badge),
    html[data-sx-theme="dark"] .sx-parent-dash h1,
    html[data-sx-theme="dark"] .sx-parent-dash h2,
    html[data-sx-theme="dark"] .sx-parent-dash h3,
    html[data-sx-theme="dark"] .sx-parent-dash h4,
    html[data-sx-theme="dark"] .sx-parent-dash h5,
    html[data-sx-theme="dark"] .sx-parent-dash h6,
    html[data-bs-theme="dark"] .sx-parent-dash h1,
    html[data-bs-theme="dark"] .sx-parent-dash h2,
    html[data-bs-theme="dark"] .sx-parent-dash h3,
    html[data-bs-theme="dark"] .sx-parent-dash h4,
    html[data-bs-theme="dark"] .sx-parent-dash h5,
    html[data-bs-theme="dark"] .sx-parent-dash h6,
    html[data-sx-theme="dark"] .sx-parent-dash .card-title,
    html[data-bs-theme="dark"] .sx-parent-dash .card-title,
    html[data-sx-theme="dark"] .sx-parent-dash .stat-label,
    html[data-bs-theme="dark"] .sx-parent-dash .stat-label,
    html[data-sx-theme="dark"] .sx-parent-dash .stat-value,
    html[data-bs-theme="dark"] .sx-parent-dash .stat-value,
    html[data-sx-theme="dark"] .sx-parent-dash .fw-semibold,
    html[data-bs-theme="dark"] .sx-parent-dash .fw-semibold,
    html[data-sx-theme="dark"] .sx-parent-dash .fw-bold,
    html[data-bs-theme="dark"] .sx-parent-dash .fw-bold,
    html[data-sx-theme="dark"] .sx-parent-dash .text-muted,
    html[data-bs-theme="dark"] .sx-parent-dash .text-muted,
    html[data-sx-theme="dark"] .sx-parent-dash .text-body,
    html[data-bs-theme="dark"] .sx-parent-dash .text-body,
    html[data-sx-theme="dark"] .sx-parent-dash .text-dark,
    html[data-bs-theme="dark"] .sx-parent-dash .text-dark,
    html[data-sx-theme="dark"] .sx-parent-dash a:not(.btn),
    html[data-bs-theme="dark"] .sx-parent-dash a:not(.btn),
    html[data-sx-theme="dark"] .sx-parent-dash .quick-tool-link,
    html[data-bs-theme="dark"] .sx-parent-dash .quick-tool-link,
    html[data-sx-theme="dark"] .sx-parent-dash .list-row,
    html[data-bs-theme="dark"] .sx-parent-dash .list-row {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .text-muted,
    html[data-bs-theme="dark"] .sx-parent-dash .text-muted,
    html[data-sx-theme="dark"] .sx-parent-dash small.text-muted,
    html[data-bs-theme="dark"] .sx-parent-dash small.text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .btn-link,
    html[data-bs-theme="dark"] .sx-parent-dash .btn-link {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-dash .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .quick-tool-link .list-row:hover,
    html[data-bs-theme="dark"] .sx-parent-dash .quick-tool-link .list-row:hover {
        background: rgba(255, 180, 180, 0.08);
    }
    html[data-sx-theme="dark"] .sx-parent-dash .badge,
    html[data-bs-theme="dark"] .sx-parent-dash .badge,
    html[data-sx-theme="dark"] .sx-parent-dash .avatar,
    html[data-bs-theme="dark"] .sx-parent-dash .avatar,
    html[data-sx-theme="dark"] .sx-parent-dash .btn-primary,
    html[data-bs-theme="dark"] .sx-parent-dash .btn-primary {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .table thead th,
    html[data-bs-theme="dark"] .sx-parent-dash .table thead th {
        background: rgba(148, 0, 0, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .env-bar,
    html[data-bs-theme="dark"] .sx-parent-dash .env-bar,
    html[data-sx-theme="dark"] .sx-parent-dash .gender-split-bar,
    html[data-bs-theme="dark"] .sx-parent-dash .gender-split-bar {
        background: rgba(255, 180, 180, 0.12);
    }
    html[data-sx-theme="dark"] .sx-parent-dash .gender-split-bar > .is-female,
    html[data-bs-theme="dark"] .sx-parent-dash .gender-split-bar > .is-female {
        background: #8a555c;
    }
    html[data-sx-theme="dark"] .sx-parent-dash .child-row,
    html[data-bs-theme="dark"] .sx-parent-dash .child-row {
        border-bottom-color: rgba(255, 180, 180, 0.12);
    }
</style>

<div class="sx-parent-dash">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('parent.close') }}"></button>
        </div>
    @endif

    @if(isset($resultNotifications) && $resultNotifications->count() > 0)
        @foreach($resultNotifications as $rn)
            <div class="alert notif-strip alert-dismissible fade show d-flex align-items-start gap-3 mb-3" role="alert">
                <div class="avatar rounded-circle bg-brand flex-shrink-0">
                    <i class="fi fi-rr-megaphone"></i>
                </div>
                <div class="flex-grow-1">
                    @if($rn->type == 'exam')
                        {!! __('parent.exam_results_out', ['exam' => '<strong>'.e($rn->examination->exam_name ?? __('parent.exam_generic')).'</strong>', 'name' => '<strong>'.e(($rn->student->first_name ?? '').' '.($rn->student->last_name ?? '')).'</strong>']) !!}
                    @else
                        {!! __('parent.term_report_out', ['term' => $rn->term == 'first_term' ? __('parent.term_1') : __('parent.term_2'), 'name' => '<strong>'.e(($rn->student->first_name ?? '').' '.($rn->student->last_name ?? '')).'</strong>']) !!}
                    @endif
                    <a href="{{ route('parentResults') }}" class="btn-link ms-1">{{ __('parent.click_to_view') }}</a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('parent.close') }}"></button>
            </div>
        @endforeach
    @endif

    <div class="row g-3">
        {{-- Metric cards --}}
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="#parentChildrenPanel">
                <div class="card-body">
                    <span class="stat-label">{{ __('parent.total_children') }}</span>
                    <span class="stat-value">{{ number_format($totalStudents) }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="#parentChildrenPanel">
                <div class="card-body">
                    <span class="stat-label">{{ __('parent.active_students') }}</span>
                    <span class="stat-value">{{ number_format($activeStudents) }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('parentResults') }}">
                <div class="card-body">
                    <span class="stat-label">{{ __('parent.recent_results') }}</span>
                    <span class="stat-value">{{ $recentResults->count() }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('parentSubjects') }}">
                <div class="card-body">
                    <span class="stat-label">{{ __('parent.upcoming_exams') }}</span>
                    <span class="stat-value">{{ $upcomingExams->count() }}</span>
                </div>
            </a>
        </div>

        {{-- Welcome / profile --}}
        <div class="col-xl-4">
            <div class="card panel-card overflow-hidden z-1 h-100 position-relative">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar rounded-circle bg-brand fw-bold flex-shrink-0" style="width:56px;height:56px;font-size:1.25rem;color:#fff !important;">
                            @if($parentPhoto)
                                <img src="{{ $parentPhoto }}" alt="">
                            @else
                                <span style="color:#fff !important;">{{ $parentInitial }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 pe-2">
                            <h5 class="card-title mb-0">{{ __('parent.dashboard_welcome') }}</h5>
                        </div>
                    </div>
                    <div class="small">
                        <div class="fw-bold text-uppercase mb-1">{{ strtoupper($parentFullName) }}</div>
                        <div class="text-muted mb-1"><i class="fi fi-rr-phone-call me-1"></i>{{ $parent->phone }}</div>
                        @if($parent->email)
                            <div class="text-muted mb-1"><i class="fi fi-rr-envelope me-1"></i>{{ $parent->email }}</div>
                        @endif
                        <div class="text-muted mb-1"><i class="fi fi-rr-id-badge me-1"></i>{{ __('parent.parent_id') }}: {{ $parent->parentID }}</div>
                        @if($school)
                            <div class="fw-semibold mt-2" style="color:#940000 !important;"><i class="fi fi-rr-building me-1"></i>{{ $school->school_name }}</div>
                        @endif
                    </div>
                    <img src="{{ asset('gxon/images/media/svg/media1.svg') }}" alt="" class="position-absolute bottom-0 end-0 z-n1" style="max-width: 42%; opacity: .88;">
                </div>
                <div class="card-footer border-0 pt-0">
                    <a href="{{ route('parent.permissions') }}" class="btn btn-primary waves-effect waves-light">
                        <i class="fi fi-rr-shield-check me-1"></i> {{ __('parent.permissions') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Children mix --}}
        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('parent.dashboard_children_mix') }}</h6>
                    <a href="{{ route('parentAttendance') }}" class="btn btn-sm btn-outline-light btn-shadow waves-effect">{{ __('parent.dashboard_open_attendance') }}</a>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <div class="text-muted small">{{ __('parent.dashboard_active_share') }}</div>
                            <h3 class="mb-0 fw-bold">{{ $totalStudents }}</h3>
                        </div>
                        <div class="text-end">
                            <div class="small"><span class="text-success fw-semibold">{{ $activeStudents }}</span> {{ __('parent.active') }} · {{ $activeShare }}%</div>
                            <div class="small"><span class="fw-semibold">{{ $maleStudents }}</span> {{ __('parent.male') }} · {{ $maleShare }}%</div>
                            <div class="small"><span class="fw-semibold">{{ $femaleStudents }}</span> {{ __('parent.female') }} · {{ $femaleShare }}%</div>
                        </div>
                    </div>
                    <div class="env-bar mb-2" title="{{ __('parent.dashboard_active_share') }}">
                        <span style="width: {{ max(0, min(100, $activeShare)) }}%;"></span>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ __('parent.gender_distribution') }}</span>
                            <span class="text-muted">{{ $maleStudents }} / {{ $femaleStudents }}</span>
                        </div>
                        <div class="gender-split-bar" title="{{ __('parent.gender_distribution') }}">
                            <span class="is-male" style="width: {{ max(0, min(100, $maleShare)) }}%;"></span>
                            <span class="is-female" style="width: {{ max(0, min(100, $femaleShare)) }}%;"></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span>{{ __('parent.male') }} {{ $maleShare }}%</span>
                            <span>{{ __('parent.female') }} {{ $femaleShare }}%</span>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-7">
                            <select class="form-select form-select-sm" id="dashboardAttendanceMode" aria-label="{{ __('parent.choose_type') }}">
                                <option value="{{ route('parentAttendance') }}">{{ __('parent.system_attendance') }}</option>
                                <option value="{{ route('parentFingerprintAttendance') }}">{{ __('parent.fingerprint_attendance') }}</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <a href="{{ route('parentAttendance') }}" id="dashboardAttendanceGo" class="btn btn-primary waves-effect waves-light w-100 btn-sm">
                                <i class="fi fi-rr-eye me-1"></i> {{ __('parent.view') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- My children --}}
        <div class="col-xl-4" id="parentChildrenPanel">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('parent.my_children') }}</h6>
                    <span class="badge bg-primary rounded-pill">{{ $totalStudents }}</span>
                </div>
                <div class="card-body pt-2" style="max-height: 360px; overflow:auto;">
                    @forelse($students as $student)
                        @php
                            $childName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                            $childInitial = strtoupper(substr($childName !== '' ? $childName : 'S', 0, 1));
                            $classLabel = trim(($student->subclass->class->class_name ?? '') . ' ' . ($student->subclass->subclass_name ?? ''));
                            $attPct = $attendanceStats[$student->studentID]['percentage'] ?? null;
                        @endphp
                        <div class="child-row">
                            <div class="child-avatar">
                                @if($student->photo)
                                    <img src="{{ asset('userImages/' . $student->photo) }}" alt="">
                                @else
                                    {{ $childInitial }}
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-truncate">{{ $childName }}</div>
                                <small class="text-muted d-block text-truncate">{{ $classLabel ?: '—' }}</small>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="badge {{ $student->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ strtoupper($student->status == 'Active' ? __('parent.active') : __('parent.inactive')) }}
                                </span>
                                @if($attPct !== null)
                                    <div class="small text-muted mt-1">{{ __('parent.rate') }}: {{ $attPct }}%</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small py-4 text-center">{{ __('parent.no_students') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick tools --}}
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('parent.dashboard_quick_tools') }}</h6>
                    <a href="{{ route('parentResults') }}" class="btn-link">{{ __('parent.dashboard_open_results') }}</a>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('parentResults') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-brand text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-trophy"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('parent.nav_exam_results') }}</div>
                                            <small class="text-muted">{{ __('parent.latest_exam_results') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('parentAttendance') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-secondary text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-calendar"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('parent.nav_attendance') }}</div>
                                            <small class="text-muted">{{ __('parent.children_attendance') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('parentPayments') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-success text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-credit-card"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('parent.dashboard_make_payment') }}</div>
                                            <small class="text-muted">{{ __('parent.nav_fees_summary') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('parentHomePackages') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-info text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-home"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('parent.nav_home_package') }}</div>
                                            <small class="text-muted">{{ __('parent.home_package') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('parentNotes') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-warning text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-notebook"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('parent.nav_notes') }}</div>
                                            <small class="text-muted">{{ __('parent.notes') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('parentFeesSummary') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-dark text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-list"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('parent.nav_fees_summary') }}</div>
                                            <small class="text-muted">{{ __('parent.payments_title') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Latest results --}}
        <div class="col-xl-7">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('parent.latest_exam_results') }}</h6>
                    <a href="{{ route('parentResults') }}" class="btn-link">{{ __('parent.dashboard_view_all') }}</a>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('parent.student') }}</th>
                                    <th>{{ __('parent.subject') }}</th>
                                    <th>{{ __('parent.exam') }}</th>
                                    <th class="text-center">{{ __('parent.marks') }}</th>
                                    <th class="text-center">{{ __('parent.grade') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentResults as $result)
                                    <tr>
                                        <td class="fw-bold">{{ $result->student->first_name ?? '—' }}</td>
                                        <td>{{ $result->classSubject->subject->subject_name ?? __('parent.na') }}</td>
                                        <td class="small">{{ $result->examination->exam_name ?? __('parent.na') }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $result->marks }}</span></td>
                                        <td class="text-center"><span class="badge bg-success">{{ $result->grade }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">{{ __('parent.no_recent_results') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent attendance --}}
        <div class="col-xl-5">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('parent.recent_attendance') }}</h6>
                    <a href="{{ route('parentAttendance') }}" class="btn-link">{{ __('parent.dashboard_view_all') }}</a>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('parent.student') }}</th>
                                    <th>{{ __('parent.date') }}</th>
                                    <th>{{ __('parent.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $attendance)
                                    @php
                                        $color = match($attendance->status) {
                                            'Present' => 'success',
                                            'Wait' => 'warning',
                                            'Absent' => 'danger',
                                            default => 'secondary'
                                        };
                                        $attStatus = match(strtolower((string)$attendance->status)) {
                                            'present' => __('parent.present'),
                                            'wait' => __('parent.wait'),
                                            'absent' => __('parent.absent'),
                                            'sick' => __('parent.sick'),
                                            default => $attendance->status
                                        };
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $attendance->student->first_name ?? '—' }}</td>
                                        <td class="small">{{ optional($attendance->attendance_date)->format('d M, Y') }}</td>
                                        <td><span class="badge bg-{{ $color }}">{{ strtoupper($attStatus) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">{{ __('parent.no_recent_attendance') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mode = document.getElementById('dashboardAttendanceMode');
        var go = document.getElementById('dashboardAttendanceGo');
        if (mode && go) {
            mode.addEventListener('change', function () {
                go.setAttribute('href', this.value);
            });
            go.setAttribute('href', mode.value);
        }
    });
</script>
@endpush
