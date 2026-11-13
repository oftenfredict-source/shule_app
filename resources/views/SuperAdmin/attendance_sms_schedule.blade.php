@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.attendance_sms_schedule'))
@section('page_breadcrumb', __('superadmin.pages.attendance_sms_schedule'))

@section('page_actions')
    <a href="{{ route('superAdminDashboard') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-apps me-1"></i> {{ __('superadmin.attendance_sms.dashboard') }}
    </a>
@endsection

@section('content')
@php
    $attendanceService = app(\App\Services\AttendanceSmsService::class);
    $fpService = app(\App\Services\StudentFingerprintAttendanceSmsService::class);
    $totalSchools = $schools->count();
    $enabledCount = $scheduleRows->filter(fn ($r) => ($r->schedule ?? 'none') !== 'none')->count();
    $autoCount = $scheduleRows->filter(fn ($r) => ($r->schedule ?? 'none') !== 'none' && ($r->send_method ?? '') === 'auto')->count();
    $fpEnabledCount = ($fpScheduleRows ?? collect())->filter(fn ($r) => ($r->schedule ?? 'none') !== 'none')->count();
@endphp

<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm span:not(.sx-badge):not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-asm a:not(.btn) {
        color: #000 !important;
    }
    .sx-asm .btn-primary,
    .sx-asm a.btn-primary,
    .sx-asm .btn-primary i,
    .sx-asm .btn-primary i:before,
    .sx-asm .text-white,
    .sx-asm .sx-badge.is-on,
    .sx-asm .sx-badge.is-auto {
        color: #fff !important;
    }
    .sx-asm .sx-asm-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-asm .sx-asm-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-asm .sx-asm-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-asm .sx-asm-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-asm .sx-asm-stat .stat-ico {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.2rem;
        line-height: 1;
    }
    .sx-asm .sx-asm-stat .stat-ico i,
    .sx-asm .sx-asm-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-asm .sx-asm-stat.is-schools .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-asm .sx-asm-stat.is-enabled .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-asm .sx-asm-stat.is-auto .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-asm .sx-asm-stat.is-fp .stat-ico { background: #eef2ff; color: #3b4cc0 !important; }
    .sx-asm .sx-asm-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-asm .sx-asm-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-asm .sx-asm-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-asm .sx-asm-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-asm .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-asm .form-select,
    .sx-asm .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-asm .form-select:focus,
    .sx-asm .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-asm .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-asm .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-asm .btn-primary:disabled {
        opacity: 0.55;
    }
    .sx-asm .sx-auto-panel {
        border: 1px solid #f0dada;
        border-left: 4px solid #940000;
        background: #fffafa;
        border-radius: 0.45rem;
        padding: 1rem 1.1rem;
        margin-top: 1rem;
    }
    .sx-asm .sx-auto-panel-title {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #940000 !important;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .sx-asm .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: hidden;
    }
    .sx-asm .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-asm .table thead th {
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
    .sx-asm .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-asm .table tbody tr:hover { background: #fffafa; }
    .sx-asm .sx-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    .sx-asm .sx-badge.is-off {
        background: #f0f0f0;
        color: #666 !important;
    }
    .sx-asm .sx-badge.is-on {
        background: #145c2a;
        color: #fff !important;
    }
    .sx-asm .sx-badge.is-auto {
        background: #940000;
        color: #fff !important;
    }
    .sx-asm .sx-badge.is-manual {
        background: #fff0e8;
        color: #a34a00 !important;
    }
    .sx-asm .dataTables_wrapper .dataTables_length select,
    .sx-asm .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
    }
    .sx-asm .dataTables_wrapper .dataTables_filter input { padding: 0.25rem 0.6rem; }
    .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-asm,
    html[data-bs-theme="dark"] .sx-asm,
    html[data-sx-theme="dark"] .sx-asm p,
    html[data-bs-theme="dark"] .sx-asm p,
    html[data-sx-theme="dark"] .sx-asm span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-asm span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-asm small,
    html[data-bs-theme="dark"] .sx-asm small,
    html[data-sx-theme="dark"] .sx-asm strong,
    html[data-bs-theme="dark"] .sx-asm strong,
    html[data-sx-theme="dark"] .sx-asm h1,
    html[data-bs-theme="dark"] .sx-asm h1,
    html[data-sx-theme="dark"] .sx-asm h2,
    html[data-bs-theme="dark"] .sx-asm h2,
    html[data-sx-theme="dark"] .sx-asm h3,
    html[data-bs-theme="dark"] .sx-asm h3,
    html[data-sx-theme="dark"] .sx-asm h4,
    html[data-bs-theme="dark"] .sx-asm h4,
    html[data-sx-theme="dark"] .sx-asm h5,
    html[data-bs-theme="dark"] .sx-asm h5,
    html[data-sx-theme="dark"] .sx-asm h6,
    html[data-bs-theme="dark"] .sx-asm h6,
    html[data-sx-theme="dark"] .sx-asm label,
    html[data-bs-theme="dark"] .sx-asm label,
    html[data-sx-theme="dark"] .sx-asm a:not(.btn),
    html[data-bs-theme="dark"] .sx-asm a:not(.btn),
    html[data-sx-theme="dark"] .sx-asm .sx-asm-card-title,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-card-title,
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat .stat-label,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat .stat-label,
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat .stat-value,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat .stat-value,
    html[data-sx-theme="dark"] .sx-asm .table,
    html[data-bs-theme="dark"] .sx-asm .table,
    html[data-sx-theme="dark"] .sx-asm .table tbody td,
    html[data-bs-theme="dark"] .sx-asm .table tbody td {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-asm .text-muted,
    html[data-bs-theme="dark"] .sx-asm .text-muted,
    html[data-sx-theme="dark"] .sx-asm .text-body,
    html[data-bs-theme="dark"] .sx-asm .text-body,
    html[data-sx-theme="dark"] .sx-asm span.text-muted:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-asm span.text-muted:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-asm .form-label,
    html[data-bs-theme="dark"] .sx-asm .form-label,
    html[data-sx-theme="dark"] .sx-asm label.form-label,
    html[data-bs-theme="dark"] .sx-asm label.form-label,
    html[data-sx-theme="dark"] .sx-asm .table thead th,
    html[data-bs-theme="dark"] .sx-asm .table thead th,
    html[data-sx-theme="dark"] .sx-asm .table tbody td.text-muted,
    html[data-bs-theme="dark"] .sx-asm .table tbody td.text-muted,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat,
    html[data-sx-theme="dark"] .sx-asm .sx-asm-card,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-asm .sx-asm-card-head,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    /* Auto timing panel */
    html[data-sx-theme="dark"] .sx-asm .sx-auto-panel,
    html[data-bs-theme="dark"] .sx-asm .sx-auto-panel {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-left-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-auto-panel-title,
    html[data-bs-theme="dark"] .sx-asm .sx-auto-panel-title {
        color: #ffb4b4 !important;
    }
    /* Stat icons */
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat.is-schools .stat-ico,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat.is-schools .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat.is-enabled .stat-ico,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat.is-enabled .stat-ico {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat.is-auto .stat-ico,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat.is-auto .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-asm-stat.is-fp .stat-ico,
    html[data-bs-theme="dark"] .sx-asm .sx-asm-stat.is-fp .stat-ico {
        background: rgba(59, 76, 192, 0.28) !important;
        color: #b8c4ff !important;
    }
    /* Tables */
    html[data-sx-theme="dark"] .sx-asm .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-asm .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-asm .table thead th,
    html[data-bs-theme="dark"] .sx-asm .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-asm .table tbody td,
    html[data-bs-theme="dark"] .sx-asm .table tbody td {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-asm .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-asm .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    /* Forms */
    html[data-sx-theme="dark"] .sx-asm .form-control,
    html[data-bs-theme="dark"] .sx-asm .form-control,
    html[data-sx-theme="dark"] .sx-asm .form-select,
    html[data-bs-theme="dark"] .sx-asm .form-select,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-asm .form-select:disabled,
    html[data-bs-theme="dark"] .sx-asm .form-select:disabled,
    html[data-sx-theme="dark"] .sx-asm .form-control:disabled,
    html[data-bs-theme="dark"] .sx-asm .form-control:disabled {
        background-color: #1a1215 !important;
        color: #8a7579 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .form-select option,
    html[data-bs-theme="dark"] .sx-asm .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    /* Badges */
    html[data-sx-theme="dark"] .sx-asm .sx-badge.is-off,
    html[data-bs-theme="dark"] .sx-asm .sx-badge.is-off {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-badge.is-on,
    html[data-bs-theme="dark"] .sx-asm .sx-badge.is-on {
        background: rgba(20, 92, 42, 0.45) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-badge.is-auto,
    html[data-bs-theme="dark"] .sx-asm .sx-badge.is-auto {
        background: rgba(148, 0, 0, 0.5) !important;
        color: #ffd0d0 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .sx-badge.is-manual,
    html[data-bs-theme="dark"] .sx-asm .sx-badge.is-manual {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    /* DataTables paginate */
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.3) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-asm .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    /* Buttons */
    html[data-sx-theme="dark"] .sx-asm .btn-primary,
    html[data-bs-theme="dark"] .sx-asm .btn-primary,
    html[data-sx-theme="dark"] .sx-asm .btn-primary i,
    html[data-bs-theme="dark"] .sx-asm .btn-primary i {
        color: #fff !important;
    }
</style>

<div class="sx-asm">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-asm-stat is-schools h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.attendance_sms.schools') }}</span>
                        <span class="stat-value">{{ $totalSchools }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-school"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-asm-stat is-enabled h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.attendance_sms.attendance_enabled') }}</span>
                        <span class="stat-value">{{ $enabledCount }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-comment-sms"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-asm-stat is-auto h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.attendance_sms.auto_send') }}</span>
                        <span class="stat-value">{{ $autoCount }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-time-forward"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-asm-stat is-fp h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.attendance_sms.fingerprint_enabled') }}</span>
                        <span class="stat-value">{{ $fpEnabledCount }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-fingerprint"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-asm-card mb-3">
        <div class="card-body">
            <div class="sx-asm-card-head">
                <h6 class="sx-asm-card-title"><i class="fi fi-rr-calendar me-1"></i> {{ __('superadmin.attendance_sms.school_attendance_sms') }}</h6>
                <span class="small text-muted">{{ __('superadmin.attendance_sms.school_attendance_sms_desc') }}</span>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label" for="schoolID">{{ __('superadmin.attendance_sms.select_school') }}</label>
                    <select id="schoolID" class="form-select">
                        <option value="">{{ __('superadmin.attendance_sms.choose_school') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">
                                {{ $school->school_name }}
                                @if($school->environment) ({{ $school->environment }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="schedule">{{ __('superadmin.attendance_sms.frequency') }}</label>
                    <select id="schedule" class="form-select" disabled>
                        <option value="none">{{ __('superadmin.attendance_sms.freq_none') }}</option>
                        <option value="daily">{{ __('superadmin.attendance_sms.freq_daily') }}</option>
                        <option value="weekly">{{ __('superadmin.attendance_sms.freq_weekly') }}</option>
                        <option value="monthly">{{ __('superadmin.attendance_sms.freq_monthly') }}</option>
                        <option value="yearly">{{ __('superadmin.attendance_sms.freq_yearly') }}</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="send_method">{{ __('superadmin.attendance_sms.send_method') }}</label>
                    <select id="send_method" class="form-select" disabled>
                        <option value="manual">{{ __('superadmin.attendance_sms.method_manual') }}</option>
                        <option value="auto">{{ __('superadmin.attendance_sms.method_auto') }}</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-100" id="btnSaveSchedule" disabled>
                        <i class="fi fi-rr-disk me-1"></i> {{ __('superadmin.attendance_sms.save') }}
                    </button>
                </div>
            </div>

            <div id="autoTimingPanel" class="sx-auto-panel d-none">
                <div class="sx-auto-panel-title"><i class="fi fi-rr-clock"></i> {{ __('superadmin.attendance_sms.auto_timing') }}</div>
                <div class="row g-3">
                    <div class="col-md-3" id="field_send_time">
                        <label class="form-label" for="send_time">{{ __('superadmin.attendance_sms.time_24h') }}</label>
                        <input type="time" id="send_time" class="form-control" value="18:00">
                        <small class="text-muted">{{ __('superadmin.attendance_sms.time_hint') }}</small>
                    </div>
                    <div class="col-md-3 d-none" id="field_day_of_week">
                        <label class="form-label" for="send_day_of_week">{{ __('superadmin.attendance_sms.day_of_week') }}</label>
                        <select id="send_day_of_week" class="form-select">
                            <option value="1">{{ __('superadmin.attendance_sms.monday') }}</option>
                            <option value="2">{{ __('superadmin.attendance_sms.tuesday') }}</option>
                            <option value="3">{{ __('superadmin.attendance_sms.wednesday') }}</option>
                            <option value="4">{{ __('superadmin.attendance_sms.thursday') }}</option>
                            <option value="5">{{ __('superadmin.attendance_sms.friday') }}</option>
                            <option value="6">{{ __('superadmin.attendance_sms.saturday') }}</option>
                            <option value="7">{{ __('superadmin.attendance_sms.sunday') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="field_day_of_month">
                        <label class="form-label" for="send_day_of_month">{{ __('superadmin.attendance_sms.day_of_month') }}</label>
                        <select id="send_day_of_month" class="form-select">
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ $d === 28 ? 'selected' : '' }}>{{ $d }}</option>
                            @endfor
                        </select>
                        <small class="text-muted">{{ __('superadmin.attendance_sms.day_of_month_hint') }}</small>
                    </div>
                    <div class="col-md-3 d-none" id="field_send_month">
                        <label class="form-label" for="send_month">{{ __('superadmin.attendance_sms.month') }}</label>
                        <select id="send_month" class="form-select">
                            @foreach(range(1, 12) as $i => $m)
                                <option value="{{ $i + 1 }}">{{ \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('M') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="small text-muted mb-0 mt-3">
                    <strong>{{ __('superadmin.attendance_sms.manual_note') }}</strong> {{ __('superadmin.attendance_sms.manual_note_text') }}
                    <strong>{{ __('superadmin.attendance_sms.auto_note') }}</strong> {!! __('superadmin.attendance_sms.auto_note_text', ['command' => '<code>attendance:send-auto-sms</code>']) !!}
                </p>
            </div>
        </div>
    </div>

    <div class="card sx-asm-card mb-3">
        <div class="card-body">
            <div class="sx-asm-card-head">
                <h6 class="sx-asm-card-title"><i class="fi fi-rr-list me-1"></i> {{ __('superadmin.attendance_sms.overview') }}</h6>
                <span class="small text-muted">{{ __('superadmin.attendance_sms.overview_desc') }}</span>
            </div>
            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="scheduleOverviewTable">
                        <thead>
                            <tr>
                                <th>{{ __('superadmin.attendance_sms.th_school') }}</th>
                                <th>{{ __('superadmin.attendance_sms.th_environment') }}</th>
                                <th>{{ __('superadmin.attendance_sms.th_attendance') }}</th>
                                <th>{{ __('superadmin.attendance_sms.th_method') }}</th>
                                <th>{{ __('superadmin.attendance_sms.th_auto_timing') }}</th>
                                <th>{{ __('superadmin.attendance_sms.th_fingerprint') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                                @php
                                    $row = $scheduleRows[$school->schoolID] ?? null;
                                    $sch = $row?->schedule ?? 'none';
                                    $method = $row?->send_method ?? 'manual';
                                    $summary = $attendanceService->formatAutoScheduleSummary($row);
                                    $fpRow = ($fpScheduleRows ?? collect())[$school->schoolID] ?? null;
                                    $fpSch = $fpRow?->schedule ?? 'none';
                                    $fpSummary = $fpService->formatAutoScheduleSummary($fpRow);
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $school->school_name }}</td>
                                    <td>{{ $school->environment ?? '-' }}</td>
                                    <td>
                                        <span class="sx-badge {{ $sch === 'none' ? 'is-off' : 'is-on' }}">{{ $sch === 'none' ? __('superadmin.common.none') : __('superadmin.attendance_sms.freq_' . $sch) }}</span>
                                    </td>
                                    <td>
                                        @if($sch === 'none')
                                            <span class="text-muted">—</span>
                                        @elseif($method === 'auto')
                                            <span class="sx-badge is-auto">{{ __('superadmin.attendance_sms.method_auto_short') }}</span>
                                        @else
                                            <span class="sx-badge is-manual">{{ __('superadmin.attendance_sms.method_manual_short') }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $summary }}</td>
                                    <td>
                                        <span class="sx-badge {{ $fpSch === 'none' ? 'is-off' : 'is-on' }}">{{ $fpSch === 'none' ? __('superadmin.common.none') : __('superadmin.attendance_sms.freq_' . $fpSch) }}</span>
                                        @if($fpSch !== 'none')
                                            <div class="small text-muted mt-1">{{ $fpSummary }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-asm-card mb-3">
        <div class="card-body">
            <div class="sx-asm-card-head">
                <h6 class="sx-asm-card-title"><i class="fi fi-rr-fingerprint me-1"></i> {{ __('superadmin.attendance_sms.fp_title') }}</h6>
                <span class="small text-muted">{{ __('superadmin.attendance_sms.fp_desc') }}</span>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label" for="fpSchoolID">{{ __('superadmin.attendance_sms.select_school') }}</label>
                    <select id="fpSchoolID" class="form-select">
                        <option value="">{{ __('superadmin.attendance_sms.fp_same_school') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="fp_schedule">{{ __('superadmin.attendance_sms.frequency') }}</label>
                    <select id="fp_schedule" class="form-select" disabled>
                        <option value="none">{{ __('superadmin.attendance_sms.freq_none') }}</option>
                        <option value="daily">{{ __('superadmin.attendance_sms.freq_daily') }}</option>
                        <option value="weekly">{{ __('superadmin.attendance_sms.freq_weekly') }}</option>
                        <option value="monthly">{{ __('superadmin.attendance_sms.freq_monthly') }}</option>
                        <option value="yearly">{{ __('superadmin.attendance_sms.freq_yearly') }}</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="fp_send_method">{{ __('superadmin.attendance_sms.send_method') }}</label>
                    <select id="fp_send_method" class="form-select" disabled>
                        <option value="manual">{{ __('superadmin.attendance_sms.method_manual_short') }}</option>
                        <option value="auto">{{ __('superadmin.attendance_sms.fp_method_auto') }}</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-100" id="btnSaveFpSchedule" disabled>
                        <i class="fi fi-rr-disk me-1"></i> {{ __('superadmin.attendance_sms.save') }}
                    </button>
                </div>
            </div>
            <div id="fpAutoTimingPanel" class="sx-auto-panel d-none">
                <div class="sx-auto-panel-title"><i class="fi fi-rr-clock"></i> {{ __('superadmin.attendance_sms.auto_timing') }}</div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="fp_send_time">{{ __('superadmin.attendance_sms.time_24h') }}</label>
                        <input type="time" id="fp_send_time" class="form-control" value="18:30">
                    </div>
                    <div class="col-md-3 d-none" id="fp_field_day_of_week">
                        <label class="form-label" for="fp_send_day_of_week">{{ __('superadmin.attendance_sms.day_of_week') }}</label>
                        <select id="fp_send_day_of_week" class="form-select">
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $i => $d)
                                <option value="{{ $i + 1 }}">{{ __('superadmin.attendance_sms.' . $d) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="fp_field_day_of_month">
                        <label class="form-label" for="fp_send_day_of_month">{{ __('superadmin.attendance_sms.day_of_month') }}</label>
                        <select id="fp_send_day_of_month" class="form-select">
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ $d === 28 ? 'selected' : '' }}>{{ $d }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="fp_field_send_month">
                        <label class="form-label" for="fp_send_month">{{ __('superadmin.attendance_sms.month') }}</label>
                        <select id="fp_send_month" class="form-select">
                            @foreach(range(1, 12) as $i => $m)
                                <option value="{{ $i + 1 }}">{{ \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('M') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SX = @json(__('superadmin.attendance_sms'));
const SXC = @json(__('superadmin.common'));
(function () {
    const schoolSelect = document.getElementById('schoolID');
    const scheduleSelect = document.getElementById('schedule');
    const sendMethodSelect = document.getElementById('send_method');
    const autoPanel = document.getElementById('autoTimingPanel');
    const saveBtn = document.getElementById('btnSaveSchedule');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function toggleAutoFields() {
        const schedule = scheduleSelect.value;
        const method = sendMethodSelect.value;
        const showAuto = schedule !== 'none' && method === 'auto';

        autoPanel.classList.toggle('d-none', !showAuto);
        document.getElementById('field_day_of_week').classList.toggle('d-none', !(showAuto && schedule === 'weekly'));
        document.getElementById('field_day_of_month').classList.toggle('d-none', !(showAuto && (schedule === 'monthly' || schedule === 'yearly')));
        document.getElementById('field_send_month').classList.toggle('d-none', !(showAuto && schedule === 'yearly'));
    }

    function setFormEnabled(enabled) {
        scheduleSelect.disabled = !enabled;
        sendMethodSelect.disabled = !enabled;
        saveBtn.disabled = !enabled;
        toggleAutoFields();
    }

    scheduleSelect.addEventListener('change', function () {
        if (this.value === 'none') {
            sendMethodSelect.value = 'manual';
        }
        toggleAutoFields();
    });
    sendMethodSelect.addEventListener('change', toggleAutoFields);

    schoolSelect.addEventListener('change', async function () {
        if (!this.value) {
            setFormEnabled(false);
            return;
        }

        setFormEnabled(true);
        scheduleSelect.value = 'none';
        sendMethodSelect.value = 'manual';

        try {
            const res = await fetch(@json(url('super-admin/attendance-sms-schedule')) + '/' + this.value, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                scheduleSelect.value = data.schedule || 'none';
                sendMethodSelect.value = data.send_method || 'manual';
                if (data.send_time) document.getElementById('send_time').value = data.send_time;
                if (data.send_day_of_week) document.getElementById('send_day_of_week').value = data.send_day_of_week;
                if (data.send_day_of_month) document.getElementById('send_day_of_month').value = data.send_day_of_month;
                if (data.send_month) document.getElementById('send_month').value = data.send_month;
            }
        } catch (e) { /* keep defaults */ }

        toggleAutoFields();
    });

    saveBtn.addEventListener('click', async function () {
        const schoolID = schoolSelect.value;
        if (!schoolID) return;

        const payload = {
            schoolID: schoolID,
            schedule: scheduleSelect.value,
            send_method: sendMethodSelect.value,
            send_time: document.getElementById('send_time').value || null,
            send_day_of_week: document.getElementById('send_day_of_week').value || null,
            send_day_of_month: document.getElementById('send_day_of_month').value || null,
            send_month: document.getElementById('send_month').value || null,
        };

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + SX.js.saving;

        try {
            const res = await fetch(@json(route('superadmin.attendance_sms_schedule.save')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: SX.js.saved, text: data.message, timer: 2000, showConfirmButton: false });
                } else {
                    alert(data.message);
                }
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(data.error || SX.js.failed_save);
            }
        } catch (e) {
            alert(SX.js.network_error);
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fi fi-rr-disk me-1"></i> ' + SX.js.save;
        }
    });

    const fpSchoolSelect = document.getElementById('fpSchoolID');
    const fpScheduleSelect = document.getElementById('fp_schedule');
    const fpSendMethodSelect = document.getElementById('fp_send_method');
    const fpAutoPanel = document.getElementById('fpAutoTimingPanel');
    const fpSaveBtn = document.getElementById('btnSaveFpSchedule');

    function fpEffectiveSchoolId() {
        return fpSchoolSelect.value || schoolSelect.value;
    }

    function toggleFpAutoFields() {
        const schedule = fpScheduleSelect.value;
        const method = fpSendMethodSelect.value;
        const showAuto = schedule !== 'none' && method === 'auto';
        fpAutoPanel.classList.toggle('d-none', !showAuto);
        document.getElementById('fp_field_day_of_week').classList.toggle('d-none', !(showAuto && schedule === 'weekly'));
        document.getElementById('fp_field_day_of_month').classList.toggle('d-none', !(showAuto && (schedule === 'monthly' || schedule === 'yearly')));
        document.getElementById('fp_field_send_month').classList.toggle('d-none', !(showAuto && schedule === 'yearly'));
    }

    function setFpFormEnabled(enabled) {
        fpScheduleSelect.disabled = !enabled;
        fpSendMethodSelect.disabled = !enabled;
        fpSaveBtn.disabled = !enabled;
        toggleFpAutoFields();
    }

    fpScheduleSelect.addEventListener('change', toggleFpAutoFields);
    fpSendMethodSelect.addEventListener('change', toggleFpAutoFields);

    schoolSelect.addEventListener('change', function () {
        if (!fpSchoolSelect.value && this.value) {
            loadFpSchedule(this.value);
        }
    });

    fpSchoolSelect.addEventListener('change', function () {
        const id = fpEffectiveSchoolId();
        if (id) loadFpSchedule(id);
        else setFpFormEnabled(false);
    });

    async function loadFpSchedule(schoolId) {
        setFpFormEnabled(true);
        try {
            const res = await fetch(@json(url('super-admin/fingerprint-attendance-sms-schedule')) + '/' + schoolId, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                fpScheduleSelect.value = data.schedule || 'none';
                fpSendMethodSelect.value = data.send_method || 'manual';
                if (data.send_time) document.getElementById('fp_send_time').value = data.send_time;
                if (data.send_day_of_week) document.getElementById('fp_send_day_of_week').value = data.send_day_of_week;
                if (data.send_day_of_month) document.getElementById('fp_send_day_of_month').value = data.send_day_of_month;
                if (data.send_month) document.getElementById('fp_send_month').value = data.send_month;
            }
        } catch (e) {}
        toggleFpAutoFields();
    }

    fpSaveBtn.addEventListener('click', async function () {
        const schoolID = fpEffectiveSchoolId();
        if (!schoolID) return alert(SX.js.select_school_first);

        const payload = {
            schoolID: schoolID,
            schedule: fpScheduleSelect.value,
            send_method: fpSendMethodSelect.value,
            send_time: document.getElementById('fp_send_time').value || null,
            send_day_of_week: document.getElementById('fp_send_day_of_week').value || null,
            send_day_of_month: document.getElementById('fp_send_day_of_month').value || null,
            send_month: document.getElementById('fp_send_month').value || null,
        };

        fpSaveBtn.disabled = true;
        fpSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + SX.js.saving;
        try {
            const res = await fetch(@json(route('superadmin.fingerprint_attendance_sms_schedule.save')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: SX.js.saved, text: data.message || SX.js.saved, timer: 2000, showConfirmButton: false });
                } else {
                    alert(data.message || SX.js.saved);
                }
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || data.error || SX.js.failed);
            }
        } catch (e) {
            alert(SX.js.network_error);
        } finally {
            fpSaveBtn.disabled = false;
            fpSaveBtn.innerHTML = '<i class="fi fi-rr-disk me-1"></i> ' + SX.js.save;
        }
    });

    if ($.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#scheduleOverviewTable')) {
            $('#scheduleOverviewTable').DataTable().destroy();
        }
        $('#scheduleOverviewTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            autoWidth: false,
            language: {
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_schools,
                search: SXC.search + ':',
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }
})();
</script>
@endpush
