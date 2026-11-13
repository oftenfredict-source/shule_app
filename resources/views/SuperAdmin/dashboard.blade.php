@extends('includes.superadmin_nav')

@section('content')
@php
    $demoCount = (int) ($demoSchoolsCount ?? 0);
    $liveCount = (int) ($liveSchoolsCount ?? 0);
    $totalInstitutions = $demoCount + $liveCount;
    $unreadInquiries = (int) ($customerInquiryUnread ?? 0);
    $demoShare = $totalInstitutions > 0 ? (int) round(($demoCount / $totalInstitutions) * 100) : 0;
    $liveShare = $totalInstitutions > 0 ? (int) round(($liveCount / $totalInstitutions) * 100) : 0;
@endphp

<style>
    /* Light mode only — never force black in dark */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .stat-value,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .fw-semibold,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .fw-bold,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .text-dark,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .quick-tool-link,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .list-row,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa-dash .btn-link {
        color: #000 !important;
    }
    .sx-sa-dash .btn-primary,
    .sx-sa-dash a.btn-primary,
    .sx-sa-dash .text-white,
    .sx-sa-dash .badge,
    .sx-sa-dash .avatar,
    .sx-sa-dash .stat-ico,
    .sx-sa-dash .avatar i,
    .sx-sa-dash .avatar i:before {
        color: #fff !important;
    }
    .sx-sa-dash .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
    }
    .sx-sa-dash a.stat-card {
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
    .sx-sa-dash a.stat-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
    }
    .sx-sa-dash a.stat-card .card-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .sx-sa-dash .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .sx-sa-dash .stat-value {
        display: block;
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .sx-sa-dash .avatar {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .sx-sa-dash .bg-brand {
        background: #940000 !important;
    }
    .sx-sa-dash .shadow-brand {
        box-shadow: 0 0.35rem 0.75rem rgba(148, 0, 0, 0.25) !important;
    }
    .sx-sa-dash .quick-tool-link {
        text-decoration: none;
        color: inherit;
    }
    .sx-sa-dash .quick-tool-link:hover {
        color: inherit;
    }
    .sx-sa-dash .quick-tool-link .list-row:hover {
        background: rgba(148, 0, 0, 0.05);
        border-radius: 0.75rem;
    }
    .sx-sa-dash .list-row {
        padding: 0.7rem 0.55rem;
        transition: background 0.15s ease;
    }
    .sx-sa-dash .env-bar {
        height: 12px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(148, 0, 0, 0.1);
    }
    .sx-sa-dash .env-bar > span {
        display: block;
        height: 100%;
        background: #940000;
    }
    .sx-sa-dash .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-sa-dash .btn-link {
        font-weight: 600;
        text-decoration: none;
        color: #940000 !important;
    }
    .sx-sa-dash .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-sa-dash .card,
    html[data-bs-theme="dark"] .sx-sa-dash .card,
    html[data-sx-theme="dark"] .sx-sa-dash .panel-card,
    html[data-bs-theme="dark"] .sx-sa-dash .panel-card {
        background: #24181c !important;
        border: 1px solid rgba(255, 180, 180, 0.16) !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash a.stat-card,
    html[data-bs-theme="dark"] .sx-sa-dash a.stat-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash,
    html[data-bs-theme="dark"] .sx-sa-dash,
    html[data-sx-theme="dark"] .sx-sa-dash p,
    html[data-bs-theme="dark"] .sx-sa-dash p,
    html[data-sx-theme="dark"] .sx-sa-dash span:not(.badge):not(.avatar),
    html[data-bs-theme="dark"] .sx-sa-dash span:not(.badge):not(.avatar),
    html[data-sx-theme="dark"] .sx-sa-dash small,
    html[data-bs-theme="dark"] .sx-sa-dash small,
    html[data-sx-theme="dark"] .sx-sa-dash strong,
    html[data-bs-theme="dark"] .sx-sa-dash strong,
    html[data-sx-theme="dark"] .sx-sa-dash div:not(.avatar):not(.badge),
    html[data-bs-theme="dark"] .sx-sa-dash div:not(.avatar):not(.badge),
    html[data-sx-theme="dark"] .sx-sa-dash h1,
    html[data-sx-theme="dark"] .sx-sa-dash h2,
    html[data-sx-theme="dark"] .sx-sa-dash h3,
    html[data-sx-theme="dark"] .sx-sa-dash h4,
    html[data-sx-theme="dark"] .sx-sa-dash h5,
    html[data-sx-theme="dark"] .sx-sa-dash h6,
    html[data-bs-theme="dark"] .sx-sa-dash h1,
    html[data-bs-theme="dark"] .sx-sa-dash h2,
    html[data-bs-theme="dark"] .sx-sa-dash h3,
    html[data-bs-theme="dark"] .sx-sa-dash h4,
    html[data-bs-theme="dark"] .sx-sa-dash h5,
    html[data-bs-theme="dark"] .sx-sa-dash h6,
    html[data-sx-theme="dark"] .sx-sa-dash .card-title,
    html[data-bs-theme="dark"] .sx-sa-dash .card-title,
    html[data-sx-theme="dark"] .sx-sa-dash .card-header,
    html[data-bs-theme="dark"] .sx-sa-dash .card-header,
    html[data-sx-theme="dark"] .sx-sa-dash .stat-label,
    html[data-bs-theme="dark"] .sx-sa-dash .stat-label,
    html[data-sx-theme="dark"] .sx-sa-dash .stat-value,
    html[data-bs-theme="dark"] .sx-sa-dash .stat-value,
    html[data-sx-theme="dark"] .sx-sa-dash .fw-semibold,
    html[data-bs-theme="dark"] .sx-sa-dash .fw-semibold,
    html[data-sx-theme="dark"] .sx-sa-dash .fw-bold,
    html[data-bs-theme="dark"] .sx-sa-dash .fw-bold,
    html[data-sx-theme="dark"] .sx-sa-dash .text-muted,
    html[data-bs-theme="dark"] .sx-sa-dash .text-muted,
    html[data-sx-theme="dark"] .sx-sa-dash .text-body,
    html[data-bs-theme="dark"] .sx-sa-dash .text-body,
    html[data-sx-theme="dark"] .sx-sa-dash .text-dark,
    html[data-bs-theme="dark"] .sx-sa-dash .text-dark,
    html[data-sx-theme="dark"] .sx-sa-dash a:not(.btn),
    html[data-bs-theme="dark"] .sx-sa-dash a:not(.btn),
    html[data-sx-theme="dark"] .sx-sa-dash .quick-tool-link,
    html[data-bs-theme="dark"] .sx-sa-dash .quick-tool-link,
    html[data-sx-theme="dark"] .sx-sa-dash .list-row,
    html[data-bs-theme="dark"] .sx-sa-dash .list-row {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash .text-muted,
    html[data-bs-theme="dark"] .sx-sa-dash .text-muted,
    html[data-sx-theme="dark"] .sx-sa-dash small.text-muted,
    html[data-bs-theme="dark"] .sx-sa-dash small.text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash .btn-link,
    html[data-bs-theme="dark"] .sx-sa-dash .btn-link {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash .btn-outline-light,
    html[data-bs-theme="dark"] .sx-sa-dash .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash .quick-tool-link .list-row:hover,
    html[data-bs-theme="dark"] .sx-sa-dash .quick-tool-link .list-row:hover {
        background: rgba(255, 180, 180, 0.08);
    }
    html[data-sx-theme="dark"] .sx-sa-dash .badge,
    html[data-bs-theme="dark"] .sx-sa-dash .badge,
    html[data-sx-theme="dark"] .sx-sa-dash .avatar,
    html[data-bs-theme="dark"] .sx-sa-dash .avatar,
    html[data-sx-theme="dark"] .sx-sa-dash .avatar i,
    html[data-bs-theme="dark"] .sx-sa-dash .avatar i,
    html[data-sx-theme="dark"] .sx-sa-dash .avatar i:before,
    html[data-bs-theme="dark"] .sx-sa-dash .avatar i:before,
    html[data-sx-theme="dark"] .sx-sa-dash .btn-primary,
    html[data-bs-theme="dark"] .sx-sa-dash .btn-primary {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash .env-bar,
    html[data-bs-theme="dark"] .sx-sa-dash .env-bar {
        background: rgba(255, 180, 180, 0.12);
    }
</style>

<div class="sx-sa-dash">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('superadmin.common.close') }}"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- Metric cards (clickable, maroon top bar) --}}
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.schools.index') }}?environment=Demo">
                <div class="card-body">
                    <span class="stat-label">{{ __('superadmin.dashboard.demo_environments') }}</span>
                    <span class="stat-value">{{ $demoCount }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.schools.index') }}?environment=Live">
                <div class="card-body">
                    <span class="stat-label">{{ __('superadmin.dashboard.live_environments') }}</span>
                    <span class="stat-value">{{ $liveCount }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.schools.index') }}">
                <div class="card-body">
                    <span class="stat-label">{{ __('superadmin.dashboard.total_institutions') }}</span>
                    <span class="stat-value">{{ $totalInstitutions }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.customer_inquiries') }}">
                <div class="card-body">
                    <span class="stat-label">{{ __('superadmin.dashboard.unread_inquiries') }}</span>
                    <span class="stat-value">{{ $unreadInquiries }}</span>
                </div>
            </a>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card overflow-hidden z-1 h-100">
                <div class="card-body">
                    <div class="w-75 pe-2">
                        <h5 class="card-title mb-2">{{ __('superadmin.dashboard.welcome') }}</h5>
                        <p class="mb-0 sx-sa-copy">{{ __('superadmin.dashboard.welcome_desc') }}</p>
                    </div>
                    <img src="{{ asset('gxon/images/media/svg/media1.svg') }}" alt="" class="position-absolute bottom-0 end-0 z-n1" style="max-width: 44%; opacity: .92;">
                </div>
                <div class="card-footer border-0 pt-0">
                    <a href="{{ route('superadmin.system_alerts') }}" class="btn btn-primary waves-effect waves-light">
                        <i class="fi fi-rr-bell me-1"></i> {{ __('superadmin.dashboard.broadcast_alert') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('superadmin.dashboard.institution_mix') }}</h6>
                    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-sm btn-outline-light btn-shadow waves-effect">{{ __('superadmin.dashboard.view_schools') }}</a>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <div class="text-muted small">{{ __('superadmin.dashboard.live_vs_demo') }}</div>
                            <h3 class="mb-0 fw-bold">{{ $totalInstitutions }}</h3>
                        </div>
                        <div class="text-end">
                            <div class="small"><span class="text-success fw-semibold">{{ $liveCount }}</span> {{ __('superadmin.dashboard.live_pct', ['pct' => $liveShare]) }}</div>
                            <div class="small"><span class="text-secondary fw-semibold">{{ $demoCount }}</span> {{ __('superadmin.dashboard.demo_pct', ['pct' => $demoShare]) }}</div>
                        </div>
                    </div>
                    <div class="env-bar mb-3" title="{{ __('superadmin.dashboard.live_share') }}">
                        <span style="width: {{ max(0, min(100, $liveShare)) }}%;"></span>
                    </div>
                    <a href="{{ route('superadmin.schools.registration_requests') }}" class="btn btn-primary waves-effect waves-light w-100">
                        <i class="fi fi-rr-file me-1"></i> {{ __('superadmin.dashboard.school_requests') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('superadmin.dashboard.account_settings') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar rounded-circle bg-brand text-white fw-bold me-2">
                            {{ __('superadmin.dashboard.sa_initials') }}
                        </div>
                        <div>
                            <div class="fw-bold">{{ __('superadmin.dashboard.super_administrator') }}</div>
                            <small class="text-muted d-block">{{ __('superadmin.dashboard.control_plane') }}</small>
                        </div>
                    </div>
                    <a href="{{ route('superadmin.change_password') }}" class="btn btn-outline-light btn-shadow waves-effect w-100 mb-2">
                        <i class="fi fi-rr-lock me-1"></i> {{ __('superadmin.dashboard.update_security') }}
                    </a>
                    <a href="{{ route('superadmin.parent_fingerprint_portal') }}" class="btn btn-primary waves-effect waves-light w-100">
                        <i class="fi fi-rr-fingerprint me-1"></i> {{ __('superadmin.dashboard.parent_fingerprint_portal') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('superadmin.dashboard.quick_tools') }}</h6>
                    <a href="{{ route('superadmin.customer_inquiries') }}" class="btn-link">{{ __('superadmin.dashboard.inquiries') }}</a>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('superadmin.customer_inquiries') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-brand text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-envelope"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('superadmin.dashboard.customer_inquiries') }}</div>
                                            <small class="text-muted">{{ __('superadmin.dashboard.public_contact_messages') }}</small>
                                        </div>
                                    </div>
                                    @if($unreadInquiries > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $unreadInquiries > 99 ? '99+' : $unreadInquiries }}</span>
                                    @else
                                        <i class="fi fi-rr-angle-small-right text-muted"></i>
                                    @endif
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('superadmin.user_password') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-secondary text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-key"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('superadmin.dashboard.reset_user_password') }}</div>
                                            <small class="text-muted">{{ __('superadmin.dashboard.send_credentials_sms') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('superadmin.fingerprint_attendance_setup.index') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-info text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-fingerprint"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('superadmin.dashboard.attendance_setup') }}</div>
                                            <small class="text-muted">{{ __('superadmin.dashboard.fingerprint_devices') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('superadmin.banks.index') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-success text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-bank"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('superadmin.dashboard.bank_registration') }}</div>
                                            <small class="text-muted">{{ __('superadmin.dashboard.fees_collections') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('superadmin.bank_accounts.verification') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-warning text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-shield-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('superadmin.dashboard.account_verification') }}</div>
                                            <small class="text-muted">{{ __('superadmin.dashboard.approve_school_accounts') }}</small>
                                        </div>
                                    </div>
                                    <i class="fi fi-rr-angle-small-right text-muted"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('superadmin.system_logs') }}" class="quick-tool-link d-block">
                                <div class="list-row d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm rounded-circle bg-dark text-white" style="width:42px;height:42px;">
                                            <i class="fi fi-rr-shield"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ __('superadmin.dashboard.system_logs') }}</div>
                                            <small class="text-muted">{{ __('superadmin.dashboard.security_activity') }}</small>
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
    </div>
</div>
@endsection
