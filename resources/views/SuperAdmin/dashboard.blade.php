@extends('includes.superadmin_nav')

@section('content')
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border: 1px solid #f0dada;
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .dashboard-hero .hero-title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #940000;
        margin-bottom: 4px;
    }
    .dashboard-hero .hero-subtitle {
        color: #666;
        margin-bottom: 0;
        font-size: 0.95rem;
    }
    .dashboard-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 4px 12px rgba(148,0,0,0.05);
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid #940000;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(148,0,0,0.1);
    }
    .dashboard-card .card-title {
        font-weight: 700;
        color: #444;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    .dashboard-card .card-icon {
        font-size: 2.2rem;
        color: #940000;
        opacity: 0.9;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: #222;
        line-height: 1;
    }
    .stat-desc {
        font-size: 0.8rem;
        color: #888;
        margin-top: 8px;
    }
    /* Ensure no radius */
    .card, .btn, .alert { border-radius: 0 !important; }
</style>

<div class="container-fluid mt-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dashboard-hero">
        <div>
            <div class="text-muted small fw-bold uppercase" style="letter-spacing: 1px;">{{ \Carbon\Carbon::now()->format('l, d M Y') }}</div>
            <div class="hero-title">Welcome, Super Admin</div>
            <p class="hero-subtitle">System Overview and Institutional Management Dashboard</p>
        </div>
        <div class="hero-actions d-none d-md-flex gap-2">
            <a href="{{ route('superadmin.schools.register') }}" class="btn btn-primary-custom btn-sm py-2 px-3">
                <i class="bx bx-plus-circle me-1"></i> New School
            </a>
            <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-primary-custom btn-sm py-2 px-3">
                <i class="bx bx-list-ul me-1"></i> All Schools
            </a>
            <a href="{{ route('superadmin.fingerprint_attendance_setup.index') }}" class="btn btn-outline-primary-custom btn-sm py-2 px-3">
                <i class="bx bx-desktop me-1"></i> Attendance Setup
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Demo Schools -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="card-title">Demo Environments</div>
                            <div class="stat-value">{{ $demoSchoolsCount ?? 0 }}</div>
                        </div>
                        <div class="card-icon"><i class="bx bx-test-tube"></i></div>
                    </div>
                    <p class="stat-desc mb-0">Institutions currently in trial or demo mode.</p>
                </div>
            </div>
        </div>

        <!-- Live Schools -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="card-title">Live Environments</div>
                            <div class="stat-value">{{ $liveSchoolsCount ?? 0 }}</div>
                        </div>
                        <div class="card-icon"><i class="bx bx-rocket"></i></div>
                    </div>
                    <p class="stat-desc mb-0">Active production environments for schools.</p>
                </div>
            </div>
        </div>

        <!-- Registered Schools -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card" style="border-left-color: #444;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="card-title">Total Institutions</div>
                            <div class="stat-value">{{ ($demoSchoolsCount ?? 0) + ($liveSchoolsCount ?? 0) }}</div>
                        </div>
                        <div class="card-icon" style="color: #444;"><i class="bx bx-building-house"></i></div>
                    </div>
                    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-sm btn-outline-dark w-100 mt-2">Manage All</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card" style="border-left-color: #222; background: #fafafa;">
                <div class="card-body">
                    <div class="card-title">System Quick Tools</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('superadmin.system_alerts') }}" class="btn btn-sm btn-dark text-start">
                            <i class="bx bx-bullhorn me-2"></i> Broadcast Alert
                        </a>
                        <a href="{{ route('superadmin.customer_inquiries') }}" class="btn btn-sm btn-outline-dark text-start position-relative">
                            <i class="bx bx-envelope me-2"></i> Customer Inquiries
                            @if(($customerInquiryUnread ?? 0) > 0)
                                <span class="badge bg-danger ms-1">{{ $customerInquiryUnread }}</span>
                            @endif
                        </a>
                        <a href="{{ route('superadmin.user_password') }}" class="btn btn-sm btn-outline-dark text-start">
                            <i class="bx bx-key me-2"></i> Reset User Password
                        </a>
                        <a href="{{ route('superadmin.fingerprint_attendance_setup.index') }}" class="btn btn-sm btn-outline-dark text-start">
                            <i class="bx bx-desktop me-2"></i> Fingerprint Attendance Setup
                        </a>
                        <a href="{{ route('superadmin.parent_fingerprint_portal') }}" class="btn btn-sm btn-outline-dark text-start">
                            <i class="bx bx-fingerprint me-2"></i> Parent Fingerprint Portal
                        </a>
                        <a href="{{ route('superadmin.banks.index') }}" class="btn btn-sm btn-outline-dark text-start">
                            <i class="bx bx-buildings me-2"></i> Bank Registration
                        </a>
                        <a href="{{ route('superadmin.bank_accounts.verification') }}" class="btn btn-sm btn-outline-dark text-start">
                            <i class="bx bx-check-shield me-2"></i> Account Verification
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Row -->
    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card" style="border-left-color:#c41e3a;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="card-title">Customer Inquiries</div>
                            <div class="stat-value">{{ $customerInquiryUnread ?? 0 }}</div>
                        </div>
                        <div class="card-icon"><i class="bx bx-envelope"></i></div>
                    </div>
                    <p class="stat-desc mb-3">Unread messages from the public contact page.</p>
                    <a href="{{ route('superadmin.customer_inquiries') }}" class="btn btn-sm btn-primary-custom w-100">
                        <i class="bx bx-show me-1"></i> View Inquiries
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Row -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-info-circle me-2 text-primary"></i> Super Admin Information</h6>
                </div>
                <div class="card-body">
                    <p>Welcome to the ShuleXpert Super Admin panel. This area allows you to oversee the entire platform, manage school licenses, and monitor system-wide alerts.</p>
                    <div class="row text-center mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border">
                                <i class="bx bx-shield-quarter d-block mb-2 text-primary" style="font-size: 1.5rem;"></i>
                                <div class="small fw-bold">Secure Access</div>
                                <div class="text-muted smaller">Multi-level Auth</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border">
                                <i class="bx bx-data d-block mb-2 text-primary" style="font-size: 1.5rem;"></i>
                                <div class="small fw-bold">Data Control</div>
                                <div class="text-muted smaller">Full CRUD Access</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border">
                                <i class="bx bx-support d-block mb-2 text-primary" style="font-size: 1.5rem;"></i>
                                <div class="small fw-bold">Help Desk</div>
                                <div class="text-muted smaller">24/7 Monitoring</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-cog me-2 text-primary"></i> Account Settings</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sa me-3" style="width: 50px; height: 50px; background: #940000; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                            SA
                        </div>
                        <div>
                            <div class="fw-bold">Super Administrator</div>
                            <div class="text-muted small">system@shulelink.com</div>
                        </div>
                    </div>
                    <hr>
                    <a href="{{ route('superadmin.change_password') }}" class="btn btn-outline-primary-custom w-100 mb-2">
                        <i class="bx bx-lock-open-alt me-1"></i> Update Security
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
