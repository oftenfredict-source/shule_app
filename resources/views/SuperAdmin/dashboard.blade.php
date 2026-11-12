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
    .sx-sa-dash,
    .sx-sa-dash p,
    .sx-sa-dash span,
    .sx-sa-dash small,
    .sx-sa-dash strong,
    .sx-sa-dash h1,
    .sx-sa-dash h2,
    .sx-sa-dash h3,
    .sx-sa-dash h4,
    .sx-sa-dash h5,
    .sx-sa-dash h6,
    .sx-sa-dash .card-title,
    .sx-sa-dash .stat-label,
    .sx-sa-dash .stat-value,
    .sx-sa-dash .sx-sa-copy,
    .sx-sa-dash .text-muted,
    .sx-sa-dash .text-body,
    .sx-sa-dash .text-dark,
    .sx-sa-dash a:not(.btn),
    .sx-sa-dash .quick-tool-link,
    .sx-sa-dash .list-row {
        color: #000 !important;
    }
    .sx-sa-dash .btn-primary,
    .sx-sa-dash a.btn-primary,
    .sx-sa-dash .text-white,
    .sx-sa-dash .badge,
    .sx-sa-dash .avatar,
    .sx-sa-dash .stat-ico {
        color: #fff !important;
    }
    .sx-sa-dash .card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        color: #000;
    }
    .sx-sa-dash a.stat-card {
        display: block;
        text-decoration: none;
        color: #000 !important;
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
        color: #000 !important;
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
        color: #000 !important;
        line-height: 1.2;
    }
    .sx-sa-dash .stat-value {
        display: block;
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.15;
        color: #000 !important;
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
    html[data-sx-theme="dark"] .sx-sa-dash .card,
    html[data-bs-theme="dark"] .sx-sa-dash .card {
        background: #24181c !important;
        border: 1px solid rgba(255, 180, 180, 0.16) !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        color: #fff;
    }
    html[data-sx-theme="dark"] .sx-sa-dash a.stat-card,
    html[data-bs-theme="dark"] .sx-sa-dash a.stat-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.18);
        border-top-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-sa-dash,
    html[data-bs-theme="dark"] .sx-sa-dash,
    html[data-sx-theme="dark"] .sx-sa-dash p,
    html[data-bs-theme="dark"] .sx-sa-dash p,
    html[data-sx-theme="dark"] .sx-sa-dash span,
    html[data-bs-theme="dark"] .sx-sa-dash span,
    html[data-sx-theme="dark"] .sx-sa-dash small,
    html[data-bs-theme="dark"] .sx-sa-dash small,
    html[data-sx-theme="dark"] .sx-sa-dash .card-title,
    html[data-bs-theme="dark"] .sx-sa-dash .card-title,
    html[data-sx-theme="dark"] .sx-sa-dash .stat-label,
    html[data-bs-theme="dark"] .sx-sa-dash .stat-label,
    html[data-sx-theme="dark"] .sx-sa-dash .stat-value,
    html[data-bs-theme="dark"] .sx-sa-dash .stat-value,
    html[data-sx-theme="dark"] .sx-sa-dash .text-muted,
    html[data-bs-theme="dark"] .sx-sa-dash .text-muted,
    html[data-sx-theme="dark"] .sx-sa-dash a:not(.btn),
    html[data-bs-theme="dark"] .sx-sa-dash a:not(.btn) {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa-dash .quick-tool-link .list-row:hover,
    html[data-bs-theme="dark"] .sx-sa-dash .quick-tool-link .list-row:hover {
        background: rgba(255, 180, 180, 0.08);
    }

</style>

<div class="sx-sa-dash">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- Metric cards (clickable, maroon top bar) --}}
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.schools.index') }}?environment=Demo">
                <div class="card-body">
                    <span class="stat-label">Demo Environments</span>
                    <span class="stat-value">{{ $demoCount }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.schools.index') }}?environment=Live">
                <div class="card-body">
                    <span class="stat-label">Live Environments</span>
                    <span class="stat-value">{{ $liveCount }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.schools.index') }}">
                <div class="card-body">
                    <span class="stat-label">Total Institutions</span>
                    <span class="stat-value">{{ $totalInstitutions }}</span>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a class="card stat-card h-100" href="{{ route('superadmin.customer_inquiries') }}">
                <div class="card-body">
                    <span class="stat-label">Unread Inquiries</span>
                    <span class="stat-value">{{ $unreadInquiries }}</span>
                </div>
            </a>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card overflow-hidden z-1 h-100">
                <div class="card-body">
                    <div class="w-75 pe-2">
                        <h5 class="card-title mb-2">Welcome, Super Admin</h5>
                        <p class="mb-0 sx-sa-copy">Oversee licenses, schools, and system-wide alerts from one place.</p>
                    </div>
                    <img src="{{ asset('gxon/images/media/svg/media1.svg') }}" alt="" class="position-absolute bottom-0 end-0 z-n1" style="max-width: 44%; opacity: .92;">
                </div>
                <div class="card-footer border-0 pt-0">
                    <a href="{{ route('superadmin.system_alerts') }}" class="btn btn-primary waves-effect waves-light">
                        <i class="fi fi-rr-bell me-1"></i> Broadcast Alert
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">Institution Mix</h6>
                    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-sm btn-outline-light btn-shadow waves-effect">View Schools</a>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <div class="text-muted small">Live vs Demo</div>
                            <h3 class="mb-0 fw-bold">{{ $totalInstitutions }}</h3>
                        </div>
                        <div class="text-end">
                            <div class="small"><span class="text-success fw-semibold">{{ $liveCount }}</span> Live ({{ $liveShare }}%)</div>
                            <div class="small"><span class="text-secondary fw-semibold">{{ $demoCount }}</span> Demo ({{ $demoShare }}%)</div>
                        </div>
                    </div>
                    <div class="env-bar mb-3" title="Live share">
                        <span style="width: {{ max(0, min(100, $liveShare)) }}%;"></span>
                    </div>
                    <a href="{{ route('superadmin.schools.registration_requests') }}" class="btn btn-primary waves-effect waves-light w-100">
                        <i class="fi fi-rr-file me-1"></i> School Requests
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">Account Settings</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar rounded-circle bg-brand text-white fw-bold me-2">
                            SA
                        </div>
                        <div>
                            <div class="fw-bold">Super Administrator</div>
                            <small class="text-muted d-block">ShuleXpert control plane</small>
                        </div>
                    </div>
                    <a href="{{ route('superadmin.change_password') }}" class="btn btn-outline-light btn-shadow waves-effect w-100 mb-2">
                        <i class="fi fi-rr-lock me-1"></i> Update Security
                    </a>
                    <a href="{{ route('superadmin.parent_fingerprint_portal') }}" class="btn btn-primary waves-effect waves-light w-100">
                        <i class="fi fi-rr-fingerprint me-1"></i> Parent Fingerprint Portal
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">System Quick Tools</h6>
                    <a href="{{ route('superadmin.customer_inquiries') }}" class="btn-link">Inquiries</a>
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
                                            <div class="fw-semibold">Customer Inquiries</div>
                                            <small class="text-muted">Public contact messages</small>
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
                                            <div class="fw-semibold">Reset User Password</div>
                                            <small class="text-muted">Send credentials by SMS</small>
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
                                            <div class="fw-semibold">Attendance Setup</div>
                                            <small class="text-muted">Fingerprint devices</small>
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
                                            <div class="fw-semibold">Bank Registration</div>
                                            <small class="text-muted">Fees &amp; collections</small>
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
                                            <div class="fw-semibold">Account Verification</div>
                                            <small class="text-muted">Approve school accounts</small>
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
                                            <div class="fw-semibold">System Logs</div>
                                            <small class="text-muted">Security &amp; activity</small>
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
