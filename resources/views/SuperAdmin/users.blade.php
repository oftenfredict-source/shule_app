@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.user_management'))
@section('page_breadcrumb', __('superadmin.pages.user_management'))

@section('page_actions')
    <a href="{{ route('superadmin.user_password') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-user-key me-1"></i> {{ __('superadmin.users.password_btn') }}
    </a>
    <button type="button" class="btn btn-primary waves-effect waves-light" onclick="loadUsers()">
        <i class="fi fi-rr-refresh me-1"></i> {{ __('superadmin.users.refresh') }}
    </button>
@endsection

@section('content')
<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users span:not(.sx-badge):not(.badge):not(.status-dot),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users .stat-label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users .stat-value,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-users .sx-user-name {
        color: #000 !important;
    }
    .sx-users .btn-primary,
    .sx-users a.btn-primary,
    .sx-users .text-white,
    .sx-users .badge.text-white {
        color: #fff !important;
    }
    .sx-users .sx-users-stat {
        display: block;
        text-decoration: none;
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        transition: box-shadow 0.15s ease, transform 0.15s ease;
    }
    .sx-users .sx-users-stat:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
    }
    .sx-users .sx-users-stat .card-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .sx-users .sx-users-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .sx-users .sx-users-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }
    .sx-users .sx-users-stat .stat-ico {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #fde8e8;
        color: #940000 !important;
        font-size: 1.2rem;
        line-height: 1;
    }
    .sx-users .sx-users-stat .stat-ico i,
    .sx-users .sx-users-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
        margin: 0;
        width: auto;
        height: auto;
    }
    .sx-users .sx-users-stat.is-online .stat-ico {
        background: #e8f7ec;
        color: #145c2a !important;
    }
    .sx-users .sx-users-stat.is-offline .stat-ico {
        background: #f1f1f1;
        color: #5c5c5c !important;
    }
    .sx-users .sx-users-stat.is-total .stat-ico {
        background: #fff0e8;
        color: #a34a00 !important;
    }
    .sx-users .sx-users-stat.is-schools .stat-ico {
        background: #fde8e8;
        color: #940000 !important;
    }
    .sx-users .btn-primary i,
    .sx-users .btn-primary i:before {
        color: #fff !important;
    }
    .sx-users .sx-users-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-users .sx-users-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-users .sx-users-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-users .sx-users-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-users .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-users .form-select,
    .sx-users .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
    }
    .sx-users .form-select:focus,
    .sx-users .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-users .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-users .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-users .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-users .table { margin: 0; vertical-align: middle; }
    .sx-users .table thead th {
        background: #f7f7f7;
        border-bottom: 1px solid #e6e6e6;
        border-top: 0;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        white-space: nowrap;
        padding: 0.75rem 0.9rem;
    }
    .sx-users .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
    }
    .sx-users .table tbody tr:hover { background: #fffafa; }
    .sx-users .user-avatar {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 1px solid #e0e0e0;
        border-radius: 50%;
        background: #fff;
        flex-shrink: 0;
    }
    .sx-users .user-avatar-fallback {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid #e0e0e0;
        background: #f7f7f7;
        color: #940000 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .sx-users .sx-user-name {
        font-weight: 600;
        line-height: 1.25;
    }
    .sx-users .sx-user-meta {
        font-size: 0.78rem;
        color: #666 !important;
        margin-top: 0.1rem;
    }
    .sx-users .sx-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        border-radius: 0.3rem;
        line-height: 1;
    }
    .sx-users .sx-badge-type {
        background: #f5f5f5;
        color: #333 !important;
        border: 1px solid #e6e6e6;
    }
    .sx-users .sx-badge-on {
        background: #e8f7ec;
        color: #145c2a !important;
    }
    .sx-users .sx-badge-off {
        background: #f1f1f1;
        color: #555 !important;
    }
    .sx-users .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .sx-users .status-dot.on {
        background: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.18);
    }
    .sx-users .status-dot.off { background: #9aa0a6; }
    .sx-users .sx-school-name {
        font-size: 0.86rem;
        font-weight: 600;
        color: #940000 !important;
    }
    .sx-users .sx-dt-top,
    .sx-users .sx-dt-bottom {
        width: 100%;
    }
    .sx-users .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 36px;
        padding: 0.25rem 0.65rem;
    }
    .sx-users .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #940000;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-users .dataTables_wrapper .dataTables_length select {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
        margin: 0 0.35rem;
    }
    .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }
    .sx-users .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
    }
    .sx-users .page-link { color: #940000; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-users,
    html[data-bs-theme="dark"] .sx-users,
    html[data-sx-theme="dark"] .sx-users p,
    html[data-bs-theme="dark"] .sx-users p,
    html[data-sx-theme="dark"] .sx-users span:not(.sx-badge):not(.badge):not(.status-dot):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-users span:not(.sx-badge):not(.badge):not(.status-dot):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-users small,
    html[data-bs-theme="dark"] .sx-users small,
    html[data-sx-theme="dark"] .sx-users strong,
    html[data-bs-theme="dark"] .sx-users strong,
    html[data-sx-theme="dark"] .sx-users div:not(.status-dot),
    html[data-bs-theme="dark"] .sx-users div:not(.status-dot),
    html[data-sx-theme="dark"] .sx-users label,
    html[data-bs-theme="dark"] .sx-users label,
    html[data-sx-theme="dark"] .sx-users h1,
    html[data-sx-theme="dark"] .sx-users h2,
    html[data-sx-theme="dark"] .sx-users h3,
    html[data-sx-theme="dark"] .sx-users h4,
    html[data-sx-theme="dark"] .sx-users h5,
    html[data-sx-theme="dark"] .sx-users h6,
    html[data-bs-theme="dark"] .sx-users h1,
    html[data-bs-theme="dark"] .sx-users h2,
    html[data-bs-theme="dark"] .sx-users h3,
    html[data-bs-theme="dark"] .sx-users h4,
    html[data-bs-theme="dark"] .sx-users h5,
    html[data-bs-theme="dark"] .sx-users h6,
    html[data-sx-theme="dark"] .sx-users .card-title,
    html[data-bs-theme="dark"] .sx-users .card-title,
    html[data-sx-theme="dark"] .sx-users .sx-users-card-title,
    html[data-bs-theme="dark"] .sx-users .sx-users-card-title,
    html[data-sx-theme="dark"] .sx-users .stat-label,
    html[data-bs-theme="dark"] .sx-users .stat-label,
    html[data-sx-theme="dark"] .sx-users .stat-value,
    html[data-bs-theme="dark"] .sx-users .stat-value,
    html[data-sx-theme="dark"] .sx-users .sx-user-name,
    html[data-bs-theme="dark"] .sx-users .sx-user-name,
    html[data-sx-theme="dark"] .sx-users .fw-semibold,
    html[data-bs-theme="dark"] .sx-users .fw-semibold,
    html[data-sx-theme="dark"] .sx-users .fw-bold,
    html[data-bs-theme="dark"] .sx-users .fw-bold,
    html[data-sx-theme="dark"] .sx-users .table,
    html[data-bs-theme="dark"] .sx-users .table,
    html[data-sx-theme="dark"] .sx-users .table tbody td,
    html[data-bs-theme="dark"] .sx-users .table tbody td,
    html[data-sx-theme="dark"] .sx-users a:not(.btn),
    html[data-bs-theme="dark"] .sx-users a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-users .text-muted,
    html[data-bs-theme="dark"] .sx-users .text-muted,
    html[data-sx-theme="dark"] .sx-users .sx-user-meta,
    html[data-bs-theme="dark"] .sx-users .sx-user-meta,
    html[data-sx-theme="dark"] .sx-users .form-label,
    html[data-bs-theme="dark"] .sx-users .form-label,
    html[data-sx-theme="dark"] .sx-users .table thead th,
    html[data-bs-theme="dark"] .sx-users .table thead th,
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-users .sx-users-stat,
    html[data-bs-theme="dark"] .sx-users .sx-users-stat,
    html[data-sx-theme="dark"] .sx-users .sx-users-card,
    html[data-bs-theme="dark"] .sx-users .sx-users-card,
    html[data-sx-theme="dark"] .sx-users .card,
    html[data-bs-theme="dark"] .sx-users .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-users .sx-users-card-head,
    html[data-bs-theme="dark"] .sx-users .sx-users-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-users .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-users .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-users .table thead th,
    html[data-bs-theme="dark"] .sx-users .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-users .table tbody td,
    html[data-bs-theme="dark"] .sx-users .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-users .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-users .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-users .form-control,
    html[data-bs-theme="dark"] .sx-users .form-control,
    html[data-sx-theme="dark"] .sx-users .form-select,
    html[data-bs-theme="dark"] .sx-users .form-select,
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-users .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-users .select2-container--default .select2-selection--single {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-users .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-users .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-users .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-users .user-avatar,
    html[data-bs-theme="dark"] .sx-users .user-avatar,
    html[data-sx-theme="dark"] .sx-users .user-avatar-fallback,
    html[data-bs-theme="dark"] .sx-users .user-avatar-fallback {
        border-color: rgba(255, 180, 180, 0.25) !important;
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-users .user-avatar-fallback,
    html[data-bs-theme="dark"] .sx-users .user-avatar-fallback {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-users .sx-school-name,
    html[data-bs-theme="dark"] .sx-users .sx-school-name {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-users .sx-badge-type,
    html[data-bs-theme="dark"] .sx-users .sx-badge-type {
        background: #3a2a2e !important;
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-users .sx-badge-on,
    html[data-bs-theme="dark"] .sx-users .sx-badge-on {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-users .sx-badge-off,
    html[data-bs-theme="dark"] .sx-users .sx-badge-off {
        background: #3a2a2e !important;
        color: #c9b4b8 !important;
    }
    html[data-sx-theme="dark"] .sx-users .stat-ico,
    html[data-bs-theme="dark"] .sx-users .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-users .btn-primary,
    html[data-bs-theme="dark"] .sx-users .btn-primary {
        color: #fff !important;
    }
</style>

<div class="sx-users">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-users-stat is-online h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.users.online_users') }}</span>
                        <span class="stat-value" id="onlineCount">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-user-check"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-users-stat is-offline h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.users.offline_users') }}</span>
                        <span class="stat-value" id="offlineCount">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-user-slash"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-users-stat is-total h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.users.total_listed') }}</span>
                        <span class="stat-value" id="totalCount">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-users"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-users-stat is-schools h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.users.schools') }}</span>
                        <span class="stat-value">{{ $schools->count() }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-building"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-users-card mb-3">
        <div class="card-body">
            <div class="sx-users-card-head">
                <h6 class="sx-users-card-title">{{ __('superadmin.users.filters') }}</h6>
                <span class="small text-muted">{{ __('superadmin.users.filters_desc') }}</span>
            </div>
            <form id="filterForm" class="row g-3" onsubmit="return false;">
                <div class="col-md-3">
                    <label class="form-label" for="filter_school">{{ __('superadmin.users.school') }}</label>
                    <select id="filter_school" class="form-select">
                        <option value="">{{ __('superadmin.users.all_schools') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filter_type">{{ __('superadmin.users.user_type') }}</label>
                    <select id="filter_type" class="form-select">
                        <option value="">{{ __('superadmin.users.all_types') }}</option>
                        <option value="Admin">{{ __('superadmin.users.type_admin') }}</option>
                        <option value="Teacher">{{ __('superadmin.users.type_teacher') }}</option>
                        <option value="Parent">{{ __('superadmin.users.type_parent') }}</option>
                        <option value="Staff">{{ __('superadmin.users.type_staff') }}</option>
                        <option value="Watchman">{{ __('superadmin.users.type_watchman') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filter_status">{{ __('superadmin.users.status') }}</label>
                    <select id="filter_status" class="form-select">
                        <option value="">{{ __('superadmin.users.all_status') }}</option>
                        <option value="online">{{ __('superadmin.users.online') }}</option>
                        <option value="offline">{{ __('superadmin.users.offline') }}</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-100" onclick="loadUsers()">
                        <i class="fi fi-rr-filters me-1"></i> {{ __('superadmin.users.apply_filters') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card sx-users-card">
        <div class="card-body">
            <div class="sx-users-card-head">
                <h6 class="sx-users-card-title">{{ __('superadmin.users.users') }}</h6>
            </div>
            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="usersTable">
                        <thead>
                            <tr>
                                <th>{{ __('superadmin.users.th_user') }}</th>
                                <th>{{ __('superadmin.users.th_username') }}</th>
                                <th>{{ __('superadmin.users.user_type') }}</th>
                                <th>{{ __('superadmin.users.th_school') }}</th>
                                <th>{{ __('superadmin.users.th_status') }}</th>
                                <th class="text-end">{{ __('superadmin.users.th_last_ip') }}</th>
                            </tr>
                        </thead>
                        <tbody id="usersBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('superadmin.users.loading_users') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const SX = @json(__('superadmin.users'));
    const SXC = @json(__('superadmin.common'));
    let dataTable = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function destroyUsersDataTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#usersTable')) {
            $('#usersTable').DataTable().destroy();
        }
        dataTable = null;
    }

    function setUsersTableBody(html) {
        destroyUsersDataTable();
        const $tbody = $('#usersTable tbody');
        if ($tbody.length) {
            $tbody.attr('id', 'usersBody').html(html);
        }
    }

    function initUsersDataTable() {
        destroyUsersDataTable();

        if (!$.fn || !$.fn.DataTable) {
            console.error(SX.js.plugin_missing);
            return;
        }

        dataTable = $('#usersTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, SXC.all]],
            ordering: true,
            info: true,
            searching: true,
            autoWidth: false,
            deferRender: true,
            order: [[4, 'asc']],
            language: {
                search: '_INPUT_',
                searchPlaceholder: SX.js.quick_search,
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                infoEmpty: SX.js.no_users,
                zeroRecords: SX.js.no_matching,
                paginate: {
                    next: SXC.next,
                    previous: SXC.previous
                }
            },
            dom: '<"sx-dt-top d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"sx-dt-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    function loadUsers() {
        if (!$.fn || !$.fn.DataTable) {
            console.error(SX.js.plugin_missing);
            return;
        }

        const params = {
            school_id: $('#filter_school').val(),
            user_type: $('#filter_type').val(),
            status: $('#filter_status').val()
        };

        setUsersTableBody('<tr><td colspan="6" class="text-center text-muted py-4">' + escapeHtml(SX.js.loading_users) + '</td></tr>');

        $.get("{{ route('superadmin.users.data') }}", params)
            .done(function (res) {
                if (!res.success) {
                    setUsersTableBody('<tr><td colspan="6" class="text-center text-muted py-4">' + escapeHtml(SX.js.load_failed) + '</td></tr>');
                    return;
                }

                const rows = res.data || [];
                $('#onlineCount').text(res.summary.online);
                $('#offlineCount').text(res.summary.offline);
                $('#totalCount').text(rows.length);

                if (!rows.length) {
                    setUsersTableBody('<tr><td colspan="6" class="text-center text-muted py-4">' + escapeHtml(SX.js.no_match_filters) + '</td></tr>');
                    return;
                }

                let html = '';
                rows.forEach(function (user) {
                    const name = escapeHtml(user.name || '');
                    const email = escapeHtml(user.email || SXC.no_email);
                    const username = escapeHtml(user.username || user.name || '');
                    const userType = escapeHtml(user.user_type || '');
                    const schoolName = user.school ? escapeHtml(user.school.school_name) : escapeHtml(SX.js.na);
                    const lastIp = escapeHtml(user.last_ip || SX.js.na);
                    const initial = name ? name.charAt(0).toUpperCase() : '?';

                    const photoHtml = user.photo_path
                        ? `<img src="/${escapeHtml(user.photo_path)}" class="user-avatar me-2" alt="">`
                        : `<div class="user-avatar-fallback me-2">${initial}</div>`;

                    const statusHtml = user.is_online
                        ? `<span class="sx-badge sx-badge-on"><span class="status-dot on"></span>${escapeHtml(SX.js.online)}</span>`
                        : `<span class="sx-badge sx-badge-off"><span class="status-dot off"></span>${escapeHtml(SX.js.offline)}</span>`;

                    html += `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    ${photoHtml}
                                    <div>
                                        <div class="sx-user-name">${name}</div>
                                        <div class="sx-user-meta">${email}</div>
                                    </div>
                                </div>
                            </td>
                            <td>${username}</td>
                            <td><span class="sx-badge sx-badge-type">${userType}</span></td>
                            <td><div class="sx-school-name">${schoolName}</div></td>
                            <td>${statusHtml}</td>
                            <td class="text-end font-monospace small">${lastIp}</td>
                        </tr>
                    `;
                });

                setUsersTableBody(html);
                initUsersDataTable();
            })
            .fail(function () {
                setUsersTableBody('<tr><td colspan="6" class="text-center text-muted py-4">' + escapeHtml(SX.js.error_loading) + '</td></tr>');
            });
    }

    $(document).ready(function () {
        loadUsers();
        $('#filter_school, #filter_type, #filter_status').on('change', function () {
            loadUsers();
        });
    });
</script>
@endsection
