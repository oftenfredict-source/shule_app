@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.system_alerts'))
@section('page_breadcrumb', __('superadmin.pages.system_alerts'))

@section('page_actions')
    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-building me-1"></i> {{ __('superadmin.system_alerts.schools') }}
    </a>
    <a href="{{ route('superAdminDashboard') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-apps me-1"></i> {{ __('superadmin.system_alerts.dashboard') }}
    </a>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa span:not(.sx-badge):not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sa a:not(.btn) {
        color: #000 !important;
    }
    .sx-sa .btn-primary,
    .sx-sa a.btn-primary,
    .sx-sa .btn-primary i,
    .sx-sa .btn-primary i:before,
    .sx-sa .text-white,
    .sx-sa .sx-badge.is-active,
    .sx-sa .badge.text-bg-danger,
    .sx-sa .badge.bg-danger,
    .sx-sa .badge.bg-success,
    .sx-sa .badge.bg-primary {
        color: #fff !important;
    }
    .sx-sa .sx-sa-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-sa .sx-sa-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-sa .sx-sa-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-sa .sx-sa-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-sa .sx-sa-stat .stat-ico {
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
    .sx-sa .sx-sa-stat .stat-ico i,
    .sx-sa .sx-sa-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-sa .sx-sa-stat.is-loaded .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-sa .sx-sa-stat.is-active .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-sa .sx-sa-stat.is-inactive .stat-ico { background: #f5f5f5; color: #555 !important; }
    .sx-sa .sx-sa-stat.is-schools .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-sa .sx-sa-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-sa .sx-sa-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-sa .sx-sa-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-sa .sx-sa-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-sa .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-sa .form-select,
    .sx-sa .form-control,
    .sx-sa .kbd-textarea {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-sa .form-select:focus,
    .sx-sa .form-control:focus,
    .sx-sa .kbd-textarea:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
        outline: none;
    }
    .sx-sa .kbd-textarea {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: #fcfcfc;
        padding: 0.9rem 1rem;
        font-size: 0.95rem;
        line-height: 1.55;
        min-height: 96px;
    }
    .sx-sa .sx-color-field {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 40px;
        padding: 0.3rem 0.45rem;
        border: 1px solid #d9d9d9;
        border-radius: 0.45rem;
        background: #fff;
    }
    .sx-sa .form-control-color {
        width: 2.15rem !important;
        height: 2.15rem !important;
        min-height: 2.15rem !important;
        padding: 0.15rem !important;
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        background: #f7f7f7 !important;
        cursor: pointer;
        flex-shrink: 0;
    }
    .sx-sa .form-control-color::-webkit-color-swatch-wrapper { padding: 0; }
    .sx-sa .form-control-color::-webkit-color-swatch {
        border: none;
        border-radius: 0.25rem;
    }
    .sx-sa .sx-color-hex {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        color: #555 !important;
        text-transform: uppercase;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sx-sa .form-check-input {
        width: 2.3rem;
        height: 1.2rem;
        cursor: pointer;
        background-color: #e9e9e9;
        border-color: #d0d0d0;
    }
    .sx-sa .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-sa .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-sa .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-sa .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-sa .btn-outline-secondary,
    .sx-sa .btn-outline-danger {
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-sa .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    .sx-sa .form-check-label {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #333 !important;
    }
    .sx-sa .sx-switch-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem 1rem;
        min-height: 40px;
        width: 100%;
        padding: 0.45rem 0.7rem;
        border: 1px solid #e6e6e6;
        border-radius: 0.45rem;
        background: #fafafa;
    }
    .sx-sa .sx-switch-row .form-check {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding-left: 0;
        margin: 0;
    }
    .sx-sa .sx-switch-row .form-check-input {
        margin: 0;
        float: none;
    }
    .sx-sa .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: hidden;
        padding: 0.75rem;
    }
    .sx-sa .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-sa .table thead th {
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
    .sx-sa .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-sa .table tbody tr:hover { background: #fffafa; }
    .sx-sa .sx-msg-cell {
        max-width: 360px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        word-break: break-word;
    }
    .sx-sa .sx-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    .sx-sa .sx-badge.is-active { background: #145c2a; color: #fff !important; }
    .sx-sa .sx-badge.is-inactive { background: #f0f0f0; color: #666 !important; }
    .sx-sa .sx-badge.is-all { background: #940000; color: #fff !important; }
    .sx-sa .sx-badge.is-role { background: #fff0e8; color: #a34a00 !important; }
    .sx-sa .sx-badge.is-info { background: #d1ecf1; color: #0c5460 !important; }
    .sx-sa .sx-badge.is-warning { background: #fff3cd; color: #856404 !important; }
    .sx-sa .sx-badge.is-success { background: #d4edda; color: #145c2a !important; }
    .sx-sa .sx-badge.is-danger { background: #f8d7da; color: #721c24 !important; }
    .sx-sa .select2-container--default .select2-selection--single {
        height: 40px;
        border-radius: 0.45rem !important;
        border: 1px solid #d9d9d9;
        display: flex;
        align-items: center;
    }
    .sx-sa .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        color: #000;
        padding-left: 0.75rem;
    }
    .sx-sa .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .sx-sa .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #940000;
    }
    .sx-sa .dataTables_wrapper .dataTables_length select,
    .sx-sa .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
    }
    .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-sa,
    html[data-bs-theme="dark"] .sx-sa,
    html[data-sx-theme="dark"] .sx-sa p,
    html[data-bs-theme="dark"] .sx-sa p,
    html[data-sx-theme="dark"] .sx-sa span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-sa span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-sa small,
    html[data-bs-theme="dark"] .sx-sa small,
    html[data-sx-theme="dark"] .sx-sa strong,
    html[data-bs-theme="dark"] .sx-sa strong,
    html[data-sx-theme="dark"] .sx-sa div,
    html[data-bs-theme="dark"] .sx-sa div,
    html[data-sx-theme="dark"] .sx-sa label,
    html[data-bs-theme="dark"] .sx-sa label,
    html[data-sx-theme="dark"] .sx-sa h1,
    html[data-bs-theme="dark"] .sx-sa h1,
    html[data-sx-theme="dark"] .sx-sa h2,
    html[data-bs-theme="dark"] .sx-sa h2,
    html[data-sx-theme="dark"] .sx-sa h3,
    html[data-bs-theme="dark"] .sx-sa h3,
    html[data-sx-theme="dark"] .sx-sa h4,
    html[data-bs-theme="dark"] .sx-sa h4,
    html[data-sx-theme="dark"] .sx-sa h5,
    html[data-bs-theme="dark"] .sx-sa h5,
    html[data-sx-theme="dark"] .sx-sa h6,
    html[data-bs-theme="dark"] .sx-sa h6,
    html[data-sx-theme="dark"] .sx-sa .sx-sa-card-title,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-card-title,
    html[data-sx-theme="dark"] .sx-sa .stat-label,
    html[data-bs-theme="dark"] .sx-sa .stat-label,
    html[data-sx-theme="dark"] .sx-sa .stat-value,
    html[data-bs-theme="dark"] .sx-sa .stat-value,
    html[data-sx-theme="dark"] .sx-sa .table,
    html[data-bs-theme="dark"] .sx-sa .table,
    html[data-sx-theme="dark"] .sx-sa .table tbody td,
    html[data-bs-theme="dark"] .sx-sa .table tbody td,
    html[data-sx-theme="dark"] .sx-sa .text-body,
    html[data-bs-theme="dark"] .sx-sa .text-body,
    html[data-sx-theme="dark"] .sx-sa a:not(.btn),
    html[data-bs-theme="dark"] .sx-sa a:not(.btn),
    html[data-sx-theme="dark"] .sx-sa .form-check-label,
    html[data-bs-theme="dark"] .sx-sa .form-check-label,
    html[data-sx-theme="dark"] .sx-sa .sx-msg-cell,
    html[data-bs-theme="dark"] .sx-sa .sx-msg-cell {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa .text-muted,
    html[data-bs-theme="dark"] .sx-sa .text-muted,
    html[data-sx-theme="dark"] .sx-sa .form-label,
    html[data-bs-theme="dark"] .sx-sa .form-label,
    html[data-sx-theme="dark"] .sx-sa .table thead th,
    html[data-bs-theme="dark"] .sx-sa .table thead th,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_filter,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_processing,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_processing {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-sa-stat,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-stat,
    html[data-sx-theme="dark"] .sx-sa .sx-sa-card,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-card,
    html[data-sx-theme="dark"] .sx-sa .card,
    html[data-bs-theme="dark"] .sx-sa .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-sa .sx-sa-card-head,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-sa .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .table,
    html[data-bs-theme="dark"] .sx-sa .table {
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-hover-bg: rgba(255, 180, 180, 0.06);
        --bs-table-hover-color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-sa .table thead th,
    html[data-bs-theme="dark"] .sx-sa .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .table tbody td,
    html[data-bs-theme="dark"] .sx-sa .table tbody td,
    html[data-sx-theme="dark"] .sx-sa .table tbody tr,
    html[data-bs-theme="dark"] .sx-sa .table tbody tr {
        background-color: transparent !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-sa .table tbody tr:hover,
    html[data-sx-theme="dark"] .sx-sa .table tbody tr:hover td,
    html[data-bs-theme="dark"] .sx-sa .table tbody tr:hover td {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-control,
    html[data-bs-theme="dark"] .sx-sa .form-control,
    html[data-sx-theme="dark"] .sx-sa .form-select,
    html[data-bs-theme="dark"] .sx-sa .form-select,
    html[data-sx-theme="dark"] .sx-sa .kbd-textarea,
    html[data-bs-theme="dark"] .sx-sa .kbd-textarea,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-control::placeholder,
    html[data-bs-theme="dark"] .sx-sa .form-control::placeholder,
    html[data-sx-theme="dark"] .sx-sa .kbd-textarea::placeholder,
    html[data-bs-theme="dark"] .sx-sa .kbd-textarea::placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-control:focus,
    html[data-bs-theme="dark"] .sx-sa .form-control:focus,
    html[data-sx-theme="dark"] .sx-sa .form-select:focus,
    html[data-bs-theme="dark"] .sx-sa .form-select:focus,
    html[data-sx-theme="dark"] .sx-sa .kbd-textarea:focus,
    html[data-bs-theme="dark"] .sx-sa .kbd-textarea:focus {
        border-color: #c43a3a !important;
        box-shadow: 0 0 0 0.2rem rgba(196, 58, 58, 0.25) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-select,
    html[data-bs-theme="dark"] .sx-sa .form-select {
        background-color: #1c1417 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23d7c4c8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
    }
    html[data-sx-theme="dark"] .sx-sa .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-sa .select2-container--default .select2-selection--single,
    html[data-sx-theme="dark"] .sx-sa .select2-container--default .select2-selection--multiple,
    html[data-bs-theme="dark"] .sx-sa .select2-container--default .select2-selection--multiple {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-sa .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa .select2-container--default .select2-selection--single .select2-selection__placeholder,
    html[data-bs-theme="dark"] .sx-sa .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] .sx-sa .select2-container--default .select2-selection--single .select2-selection__arrow b,
    html[data-bs-theme="dark"] .sx-sa .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #d7c4c8 transparent transparent transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sa .select2-container--default .select2-selection__clear,
    html[data-bs-theme="dark"] .sx-sa .select2-container--default .select2-selection__clear {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-primary:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.12) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-danger,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-danger {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-danger:hover,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-danger:hover {
        background: rgba(148, 0, 0, 0.45) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-primary,
    html[data-bs-theme="dark"] .sx-sa .btn-primary,
    html[data-sx-theme="dark"] .sx-sa .btn-success,
    html[data-bs-theme="dark"] .sx-sa .btn-success,
    html[data-sx-theme="dark"] .sx-sa .btn-danger,
    html[data-bs-theme="dark"] .sx-sa .btn-danger,
    html[data-sx-theme="dark"] .sx-sa .btn-primary i,
    html[data-bs-theme="dark"] .sx-sa .btn-primary i,
    html[data-sx-theme="dark"] .sx-sa .btn-primary i:before,
    html[data-bs-theme="dark"] .sx-sa .btn-primary i:before {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.3) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-sa .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-check-input,
    html[data-bs-theme="dark"] .sx-sa .form-check-input {
        background-color: #1c1417;
        border-color: rgba(255, 180, 180, 0.35);
    }
    html[data-sx-theme="dark"] .sx-sa .form-check-input:checked,
    html[data-bs-theme="dark"] .sx-sa .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-sa .spinner-border,
    html[data-bs-theme="dark"] .sx-sa .spinner-border {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-sa-stat.is-schools .stat-ico,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-stat.is-schools .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-sa-stat.is-loaded .stat-ico,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-stat.is-loaded .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-sa-stat.is-active .stat-ico,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-stat.is-active .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-sa-stat.is-inactive .stat-ico,
    html[data-bs-theme="dark"] .sx-sa .sx-sa-stat.is-inactive .stat-ico {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-active,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-active {
        background: rgba(20, 92, 42, 0.45) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-inactive,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-inactive {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-all,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-all {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-role,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-role {
        background: rgba(163, 74, 0, 0.3) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-info,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-info {
        background: rgba(12, 84, 96, 0.4) !important;
        color: #9eeaf9 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-warning,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-warning {
        background: rgba(154, 107, 0, 0.3) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-success,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-success {
        background: rgba(20, 92, 42, 0.4) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-badge.is-danger,
    html[data-bs-theme="dark"] .sx-sa .sx-badge.is-danger {
        background: rgba(148, 0, 0, 0.35) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-color-field,
    html[data-bs-theme="dark"] .sx-sa .sx-color-field {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-control-color,
    html[data-bs-theme="dark"] .sx-sa .form-control-color {
        background-color: #24181c !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-color-hex,
    html[data-bs-theme="dark"] .sx-sa .sx-color-hex {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .sx-switch-row,
    html[data-bs-theme="dark"] .sx-sa .sx-switch-row {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-check-input,
    html[data-bs-theme="dark"] .sx-sa .form-check-input {
        background-color: #3a2a2f !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-check-input:checked,
    html[data-bs-theme="dark"] .sx-sa .form-check-input:checked {
        background-color: #940000 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-sa .form-check-label,
    html[data-bs-theme="dark"] .sx-sa .form-check-label {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sa .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-sa .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.4) !important;
    }
    html[data-sx-theme="dark"] .select2-container--default .select2-dropdown,
    html[data-bs-theme="dark"] .select2-container--default .select2-dropdown {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field,
    html[data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .select2-container--default .select2-results__option,
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option {
        background: transparent !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected],
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected],
    html[data-sx-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable,
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable,
    html[data-sx-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true],
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true],
    html[data-sx-theme="dark"] .select2-container--default .select2-results__option--selected,
    html[data-bs-theme="dark"] .select2-container--default .select2-results__option--selected {
        background: #940000 !important;
        color: #fff !important;
    }
</style>

<div class="sx-sa">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-sa-stat is-schools h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.system_alerts.schools') }}</span>
                        <span class="stat-value">{{ $schools->count() }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-building"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-sa-stat is-loaded h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.system_alerts.loaded') }}</span>
                        <span class="stat-value" id="statLoaded">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-megaphone"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-sa-stat is-active h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.system_alerts.active') }}</span>
                        <span class="stat-value" id="statActive">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-bell"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-sa-stat is-inactive h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.system_alerts.inactive') }}</span>
                        <span class="stat-value" id="statInactive">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-exclamation"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-sa-card mb-3">
        <div class="card-body">
            <div class="sx-sa-card-head">
                <h6 class="sx-sa-card-title"><i class="fi fi-rr-users me-1"></i> {{ __('superadmin.system_alerts.step1_title') }}</h6>
                <span class="small text-muted">{{ __('superadmin.system_alerts.step1_desc') }}</span>
            </div>
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label" for="schoolID">{{ __('superadmin.system_alerts.institution') }}</label>
                    <select id="schoolID" class="form-control">
                        <option value="">{{ __('superadmin.system_alerts.search_school') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="targetUserType">{{ __('superadmin.system_alerts.user_category') }}</label>
                    <select id="targetUserType" class="form-select">
                        <option value="">{{ __('superadmin.system_alerts.select_category') }}</option>
                        @foreach($userTypes as $ut)
                            <option value="{{ $ut }}">{{ $ut }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5" id="targetFilterWrap" style="display:none;">
                    <label class="form-label" for="targetFilter" id="targetFilterLabel">{{ __('superadmin.system_alerts.specific_role') }}</label>
                    <select id="targetFilter" class="form-select"></select>
                    <small class="text-muted d-block mt-1" id="targetHelp"></small>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-sa-card mb-3">
        <div class="card-body">
            <div class="sx-sa-card-head">
                <h6 class="sx-sa-card-title"><i class="fi fi-rr-edit me-1"></i> {{ __('superadmin.system_alerts.step2_title') }}</h6>
                <span class="small text-muted">{{ __('superadmin.system_alerts.step2_desc') }}</span>
            </div>
            <form id="alertForm">
                <input type="hidden" id="alertId" value="">
                <div class="row g-3 mb-3">
                    <div class="col-lg-2">
                        <label class="form-label" for="alertType">{{ __('superadmin.system_alerts.preset_style') }}</label>
                        <select id="alertType" class="form-select">
                            <option value="info">{{ __('superadmin.system_alerts.style_info') }}</option>
                            <option value="warning">{{ __('superadmin.system_alerts.style_warning') }}</option>
                            <option value="success">{{ __('superadmin.system_alerts.style_success') }}</option>
                            <option value="danger">{{ __('superadmin.system_alerts.style_danger') }}</option>
                            <option value="custom">{{ __('superadmin.system_alerts.custom_styling') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label" for="fontSize">{{ __('superadmin.system_alerts.font_size') }}</label>
                        <select id="fontSize" class="form-select">
                            <option value="">{{ __('superadmin.system_alerts.font_default') }}</option>
                            <option value="12px">12px</option>
                            <option value="14px">14px</option>
                            <option value="16px">16px</option>
                            <option value="18px">18px</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label" for="alertWidth">{{ __('superadmin.system_alerts.width') }}</label>
                        <select id="alertWidth" class="form-select">
                            <option value="">{{ __('superadmin.system_alerts.width_auto') }}</option>
                            <option value="100%">{{ __('superadmin.system_alerts.width_full') }}</option>
                            <option value="80%">{{ __('superadmin.system_alerts.width_large') }}</option>
                            <option value="60%">{{ __('superadmin.system_alerts.width_medium') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label" for="bgColor">{{ __('superadmin.system_alerts.bg_color') }}</label>
                        <div class="sx-color-field">
                            <input type="color" id="bgColor" class="form-control form-control-color" value="#fff3cd" title="{{ __('superadmin.system_alerts.bg_color_label') }}">
                            <span class="sx-color-hex" id="bgColorHex">#fff3cd</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label" for="textColor">{{ __('superadmin.system_alerts.text_color') }}</label>
                        <div class="sx-color-field">
                            <input type="color" id="textColor" class="form-control form-control-color" value="#000000" title="{{ __('superadmin.system_alerts.text_color_label') }}">
                            <span class="sx-color-hex" id="textColorHex">#000000</span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 d-flex align-items-end">
                        <div class="sx-switch-row">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="isMarquee">
                                <label class="form-check-label" for="isMarquee">{{ __('superadmin.system_alerts.marquee') }}</label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="isBold">
                                <label class="form-check-label" for="isBold">{{ __('superadmin.system_alerts.bold') }}</label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="isActive" checked>
                                <label class="form-check-label" for="isActive">{{ __('superadmin.system_alerts.active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="alertMessage">{{ __('superadmin.system_alerts.message_body') }}</label>
                    <textarea id="alertMessage" class="kbd-textarea w-100" rows="3" placeholder="{{ __('superadmin.system_alerts.message_placeholder') }}"></textarea>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="btnReset">{{ __('superadmin.system_alerts.reset_form') }}</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnSave">
                        <i class="fi fi-rr-disk me-1"></i> {{ __('superadmin.system_alerts.save_alert') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card sx-sa-card">
        <div class="card-body">
            <div class="sx-sa-card-head">
                <h6 class="sx-sa-card-title"><i class="fi fi-rr-list me-1"></i> {{ __('superadmin.system_alerts.step3_title') }}</h6>
                <span class="small text-muted">{{ __('superadmin.system_alerts.step3_desc') }}</span>
            </div>
            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="alertsTable">
                        <thead>
                            <tr>
                                <th width="60">{{ __('superadmin.system_alerts.th_id') }}</th>
                                <th width="120">{{ __('superadmin.system_alerts.th_type') }}</th>
                                <th>{{ __('superadmin.system_alerts.th_target') }}</th>
                                <th>{{ __('superadmin.system_alerts.th_message') }}</th>
                                <th class="text-center" width="110">{{ __('superadmin.system_alerts.th_status') }}</th>
                                <th class="text-end" width="140">{{ __('superadmin.system_alerts.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="alertsTbody">
                            <tr><td colspan="6" class="text-center py-5 text-muted">{{ __('superadmin.system_alerts.select_to_view') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const SX = @json(__('superadmin.system_alerts'));
    const SXC = @json(__('superadmin.common'));
    let alertsTable = null;
    let currentOptions = [];
    let loadedAlerts = [];
    let lastNonCustomType = 'info';

    $('#schoolID').select2({ width: '100%', placeholder: SX.search_school, allowClear: true });

    function safeText(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }

    function syncColorHexes() {
        $('#bgColorHex').text(($('#bgColor').val() || '#fff3cd').toLowerCase());
        $('#textColorHex').text(($('#textColor').val() || '#000000').toLowerCase());
    }

    function updateStats(list) {
        const items = list || [];
        const active = items.filter(a => !!a.is_active).length;
        $('#statLoaded').text(items.length);
        $('#statActive').text(active);
        $('#statInactive').text(Math.max(0, items.length - active));
    }

    function resetForm() {
        $('#alertId').val(''); $('#alertType').val('info'); $('#alertWidth').val('');
        $('#bgColor').val('#fff3cd'); $('#textColor').val('#000000');
        $('#isMarquee').prop('checked', false); $('#isBold').prop('checked', false);
        $('#isActive').prop('checked', true); $('#fontSize').val(''); $('#alertMessage').val('');
        $('#btnSave').html('<i class="fi fi-rr-disk me-1"></i> ' + SX.js.save_alert);
        lastNonCustomType = 'info';
        syncColorHexes();
    }

    function getTargetSelectionPayload() {
        const userType = $('#targetUserType').val();
        if (userType === 'Admin' || userType === 'parent') return { applies_to_all: 1, target_role_id: null, target_profession_id: null };
        const targetVal = $('#targetFilter').val();
        const appliesToAll = (targetVal === '__all__');
        return { applies_to_all: appliesToAll ? 1 : 0, target_role_id: (userType === 'Teacher' && !appliesToAll) ? targetVal : null, target_profession_id: (userType === 'Staff' && !appliesToAll) ? targetVal : null };
    }

    async function loadOptions() {
        const schoolID = $('#schoolID').val(); const userType = $('#targetUserType').val();
        $('#targetFilterWrap').hide(); $('#targetFilter').empty(); currentOptions = [];
        if (!schoolID || !userType || userType === 'Admin' || userType === 'parent') return;
        try {
            const res = await $.ajax({ url: @json(route('superadmin.system_alerts.options')), type: 'GET', data: { schoolID, user_type: userType } });
            if (!res.success) return;
            currentOptions = (userType === 'Teacher') ? res.roles : res.professions;
            $('#targetFilterLabel').text((userType === 'Teacher') ? SX.js.specific_teacher_role : SX.js.specific_staff_position);
            $('#targetHelp').text((userType === 'Teacher') ? SX.js.broadcast_teacher : SX.js.broadcast_staff);
            const opts = ['<option value="__all__">' + safeText(SX.js.all_everyone) + '</option>'].concat(currentOptions.map(o => `<option value="${safeText(o.id)}">${safeText(o.name)}</option>`));
            $('#targetFilter').html(opts.join('')); $('#targetFilterWrap').show();
        } catch (e) { }
    }

    function renderTargetText(a) {
        if (a.applies_to_all || a.target_user_type === 'Admin' || a.target_user_type === 'parent') {
            return '<span class="sx-badge is-all">' + safeText(SX.js.badge_all_type).replace(':type', safeText(a.target_user_type).toUpperCase()) + '</span>';
        }
        if (a.target_user_type === 'Teacher') {
            const role = currentOptions.find(x => String(x.id) === String(a.target_role_id));
            return '<span class="sx-badge is-role">' + safeText(SX.js.badge_role) + ' ' + (role ? safeText(role.name) : safeText(a.target_role_id)) + '</span>';
        }
        if (a.target_user_type === 'Staff') {
            const p = currentOptions.find(x => String(x.id) === String(a.target_profession_id));
            return '<span class="sx-badge is-role">' + safeText(SX.js.badge_position) + ' ' + (p ? safeText(p.name) : safeText(a.target_profession_id)) + '</span>';
        }
        return '';
    }

    function typeBadge(type) {
        const t = String(type || 'info').toLowerCase();
        const cls = (t === 'danger' || t === 'success' || t === 'warning') ? t : 'info';
        return '<span class="sx-badge is-' + cls + '">' + safeText(t) + '</span>';
    }

    function initTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#alertsTable')) {
            $('#alertsTable').DataTable().destroy();
        }
        alertsTable = $('#alertsTable').DataTable({
            pageLength: 10,
            ordering: true,
            autoWidth: false,
            columnDefs: [{ orderable: false, targets: [5] }],
            language: {
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_alerts,
                search: SXC.search + ':',
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    async function loadAlerts() {
        const schoolID = $('#schoolID').val(); const userType = $('#targetUserType').val();
        resetForm();
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#alertsTable')) {
            $('#alertsTable').DataTable().clear().destroy();
            alertsTable = null;
        }
        if (!schoolID || !userType) {
            updateStats([]);
            $('#alertsTbody').html('<tr><td colspan="6" class="text-center py-5 text-muted">' + safeText(SX.js.select_to_view) + '</td></tr>');
            return;
        }
        $('#alertsTbody').html('<tr><td colspan="6" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-danger"></div> ' + safeText(SX.js.fetching) + '</td></tr>');
        try {
            const res = await $.ajax({ url: @json(route('superadmin.system_alerts.list')), type: 'GET', data: { schoolID, user_type: userType } });
            if (!res.success) {
                updateStats([]);
                $('#alertsTbody').html('<tr><td colspan="6" class="text-center text-danger py-5">' + safeText(SX.js.load_failed) + '</td></tr>');
                return;
            }
            loadedAlerts = res.alerts || [];
            updateStats(loadedAlerts);
            if (loadedAlerts.length === 0) {
                $('#alertsTbody').html('<tr><td colspan="6" class="text-center py-5 text-muted">' + safeText(SX.js.none_found) + '</td></tr>');
                return;
            }
            const rows = loadedAlerts.map(a => `
                <tr data-id="${safeText(a.id)}">
                    <td><small class="fw-bold text-muted">#${safeText(a.id)}</small></td>
                    <td>${typeBadge(a.alert_type)}</td>
                    <td>${renderTargetText(a)}</td>
                    <td><div class="sx-msg-cell small" title="${safeText(a.message)}">${safeText(a.message)}</div></td>
                    <td class="text-center">${a.is_active ? `<span class="sx-badge is-active">${safeText(SX.js.active)}</span>` : `<span class="sx-badge is-inactive">${safeText(SX.js.inactive)}</span>`}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit me-1" data-id="${safeText(a.id)}" title="${safeText(SX.js.edit)}"><i class="fi fi-rr-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="${safeText(a.id)}" title="${safeText(SX.js.delete)}"><i class="fi fi-rr-trash"></i></button>
                    </td>
                </tr>
            `).join('');
            $('#alertsTbody').html(rows); initTable();
        } catch (e) {
            updateStats([]);
            $('#alertsTbody').html('<tr><td colspan="6" class="text-center text-danger py-5">' + safeText(SX.js.network_error) + '</td></tr>');
        }
    }

    const defaultColors = { info: { bg: '#d1ecf1', text: '#0c5460' }, warning: { bg: '#fff3cd', text: '#856404' }, success: { bg: '#d4edda', text: '#000000' }, danger: { bg: '#f8d7da', text: '#721c24' } };

    $('#bgColor, #textColor').on('input change', syncColorHexes);

    $('#alertType').on('change', function() {
        const val = $(this).val(); if (val !== 'custom') {
            lastNonCustomType = val; const c = defaultColors[val] || defaultColors.info;
            $('#bgColor').val(c.bg); $('#textColor').val(c.text);
            syncColorHexes();
        }
    });

    $('#btnReset').on('click', resetForm);

    $('#btnSave').on('click', async function() {
        const schoolID = $('#schoolID').val(), target_user_type = $('#targetUserType').val(), message = $('#alertMessage').val();
        if (!schoolID || !target_user_type || !message) { Swal.fire({ icon: 'warning', title: SX.js.missing_info, text: SX.js.missing_info_text }); return; }
        const targetPayload = getTargetSelectionPayload();
        const mode = $('#alertType').val(), isCustom = mode === 'custom', alertTypeToSave = isCustom ? lastNonCustomType : mode;
        const pickedBg = $('#bgColor').val(), pickedText = $('#textColor').val(), defaultsForType = defaultColors[alertTypeToSave] || defaultColors.info;
        const shouldPersistCustomColors = isCustom || (pickedBg.toLowerCase() !== (defaultsForType.bg || '').toLowerCase()) || (pickedText.toLowerCase() !== (defaultsForType.text || '').toLowerCase());

        const payload = {
            _token: @json(csrf_token()), id: $('#alertId').val(), schoolID, target_user_type, alert_type: alertTypeToSave, message,
            is_marquee: $('#isMarquee').is(':checked') ? 1 : 0, is_bold: $('#isBold').is(':checked') ? 1 : 0,
            font_size: $('#fontSize').val(), width: $('#alertWidth').val(),
            bg_color: shouldPersistCustomColors ? pickedBg : '', text_color: shouldPersistCustomColors ? pickedText : '',
            is_active: $('#isActive').is(':checked') ? 1 : 0, applies_to_all: targetPayload.applies_to_all,
            target_role_id: targetPayload.target_role_id, target_profession_id: targetPayload.target_profession_id
        };

        try {
            const res = await $.ajax({ url: payload.id ? @json(route('superadmin.system_alerts.update')) : @json(route('superadmin.system_alerts.store')), type: 'POST', data: payload });
            if (res.success) { resetForm(); await loadAlerts(); Swal.fire({ icon: 'success', title: SX.js.saved }); }
        } catch (e) { Swal.fire({ icon: 'error', title: SX.js.save_error }); }
    });

    $(document).on('click', '.btn-edit', function() {
        const a = loadedAlerts.find(x => String(x.id) === String($(this).data('id'))); if (!a) return;
        $('#alertId').val(a.id); const hasCustom = !!(a.bg_color || a.text_color); lastNonCustomType = a.alert_type || 'info';
        $('#alertType').val(hasCustom ? 'custom' : lastNonCustomType); $('#alertWidth').val(a.width || '');
        $('#bgColor').val(hasCustom ? a.bg_color : defaultColors[lastNonCustomType].bg);
        $('#textColor').val(hasCustom ? a.text_color : defaultColors[lastNonCustomType].text);
        $('#isMarquee').prop('checked', !!a.is_marquee); $('#isBold').prop('checked', !!a.is_bold);
        $('#isActive').prop('checked', !!a.is_active); $('#fontSize').val(a.font_size || ''); $('#alertMessage').val(a.message || '');
        $('#targetFilter').val(a.applies_to_all ? '__all__' : (a.target_role_id || a.target_profession_id || ''));
        $('#btnSave').html('<i class="fi fi-rr-disk me-1"></i> ' + SX.js.update_alert);
        syncColorHexes();
        $('html, body').animate({ scrollTop: $('#alertForm').offset().top - 80 }, 300);
    });

    $(document).on('click', '.btn-delete', async function() {
        const confirm = await Swal.fire({ title: SX.js.delete_title, text: SX.js.delete_text, icon: 'warning', showCancelButton: true, confirmButtonColor: '#940000', confirmButtonText: SXC.yes_delete, cancelButtonText: SX.js.cancel });
        if (!confirm.isConfirmed) return;
        try {
            const res = await $.ajax({ url: @json(url('super-admin/system-alerts/delete')) + '/' + $(this).data('id'), type: 'DELETE', data: { _token: @json(csrf_token()) } });
            if (res.success) { await loadAlerts(); Swal.fire({ icon: 'success', title: SX.js.deleted }); }
        } catch (e) { }
    });

    $('#schoolID, #targetUserType').on('change', async function() { await loadOptions(); await loadAlerts(); });
    resetForm();
    updateStats([]);
});
</script>
@endpush
