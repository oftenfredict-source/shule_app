@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.system_logs'))
@section('page_breadcrumb', __('superadmin.pages.system_logs'))

@section('page_actions')
    <button type="button" class="btn btn-primary waves-effect waves-light" onclick="reloadActiveTab()">
        <i class="fi fi-rr-refresh me-1"></i> {{ __('superadmin.system_logs.refresh') }}
    </button>
@endsection

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl span:not(.sx-badge):not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl a:not(.btn) {
        color: #000 !important;
    }
    .sx-sl .btn-primary,
    .sx-sl a.btn-primary,
    .sx-sl .btn-primary i,
    .sx-sl .btn-primary i:before,
    .sx-sl .text-white,
    .sx-sl .sx-badge.is-blocked,
    .sx-sl .sx-avatar,
    .sx-sl .nav-tabs .badge {
        color: #fff !important;
    }
    .sx-sl .sx-maroon,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-sl a.sx-maroon,
    #logDetailsModal .sx-maroon,
    #visitorDetailsModal .sx-maroon {
        color: #940000 !important;
    }
    .sx-sl .sx-sl-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-sl .sx-sl-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-sl .sx-sl-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-sl .sx-sl-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-sl .sx-sl-stat .stat-value.is-sm { font-size: 1.15rem; line-height: 1.25; }
    .sx-sl .sx-sl-stat .stat-ico {
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
    .sx-sl .sx-sl-stat .stat-ico i,
    .sx-sl .sx-sl-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-sl .sx-sl-stat.is-login .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-sl .sx-sl-stat.is-visitors .stat-ico { background: #eef2ff; color: #3b4cc0 !important; }
    .sx-sl .sx-sl-stat.is-photo .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-sl .sx-sl-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-sl .sx-sl-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-sl .sx-sl-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-sl .sx-sl-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-sl .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-sl .form-select,
    .sx-sl .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-sl .form-select:focus,
    .sx-sl .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-sl .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-sl .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-sl .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-sl .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-sl .btn-outline-secondary,
    .sx-sl .btn-outline-danger,
    .sx-sl .btn-danger,
    .sx-sl .btn-success,
    .sx-sl .btn-secondary {
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-sl .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    .sx-sl .sx-photo-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        height: 100%;
    }
    .sx-sl .nav-tabs {
        border-bottom: 1px solid #eee;
        gap: 0.25rem;
    }
    .sx-sl .nav-tabs .nav-link {
        border: 0;
        border-radius: 0.45rem 0.45rem 0 0;
        font-weight: 700;
        font-size: 0.82rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #666 !important;
        padding: 0.85rem 1rem;
        background: transparent;
    }
    .sx-sl .nav-tabs .nav-link.active {
        color: #940000 !important;
        border-bottom: 3px solid #940000;
        background: #fffafa;
    }
    .sx-sl .nav-tabs .badge {
        background: #940000 !important;
        font-weight: 700;
        font-size: 0.68rem;
        vertical-align: middle;
    }
    .sx-sl .nav-tabs .nav-link:not(.active) .badge {
        background: #888 !important;
    }
    .sx-sl .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-sl .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-sl .table thead th {
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
    .sx-sl .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-sl .table tbody tr:hover { background: #fffafa; }
    .sx-sl .sx-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: #940000;
        color: #fff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .sx-sl .sx-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.2rem 0.45rem;
        border-radius: 0.25rem;
    }
    .sx-sl .sx-badge.is-blocked { background: #940000; color: #fff !important; }
    .sx-sl .sx-badge.is-pages { background: #f5f5f5; color: #444 !important; border: 1px solid #e6e6e6; }
    .sx-sl .dataTables_wrapper .dataTables_length select,
    .sx-sl .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
    }
    .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }
    .activity-item {
        border-left: 2px solid #eee;
        padding-left: 15px;
        margin-bottom: 15px;
        position: relative;
    }
    .activity-item::before {
        content: "";
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #ddd;
    }
    .activity-item.active::before { background: #940000; }
    #logDetailsModal .modal-content,
    #visitorDetailsModal .modal-content {
        border: 0;
        border-radius: 0.65rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(20, 0, 0, 0.18);
    }
    #logDetailsModal .modal-header,
    #visitorDetailsModal .modal-header {
        background: linear-gradient(135deg, #940000 0%, #6b0000 100%);
        color: #fff;
        border-bottom: 0;
    }
    #logDetailsModal .modal-title,
    #visitorDetailsModal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
    }
    #logDetailsModal .nav-tabs .nav-link.active,
    #visitorDetailsModal .nav-tabs .nav-link.active {
        color: #940000 !important;
        border-bottom: 3px solid #940000;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-sl,
    html[data-bs-theme="dark"] .sx-sl,
    html[data-sx-theme="dark"] .sx-sl p,
    html[data-bs-theme="dark"] .sx-sl p,
    html[data-sx-theme="dark"] .sx-sl span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-sl span:not(.sx-badge):not(.badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-sl small,
    html[data-bs-theme="dark"] .sx-sl small,
    html[data-sx-theme="dark"] .sx-sl strong,
    html[data-bs-theme="dark"] .sx-sl strong,
    html[data-sx-theme="dark"] .sx-sl div,
    html[data-bs-theme="dark"] .sx-sl div,
    html[data-sx-theme="dark"] .sx-sl label,
    html[data-bs-theme="dark"] .sx-sl label,
    html[data-sx-theme="dark"] .sx-sl h1,
    html[data-bs-theme="dark"] .sx-sl h1,
    html[data-sx-theme="dark"] .sx-sl h2,
    html[data-bs-theme="dark"] .sx-sl h2,
    html[data-sx-theme="dark"] .sx-sl h3,
    html[data-bs-theme="dark"] .sx-sl h3,
    html[data-sx-theme="dark"] .sx-sl h4,
    html[data-bs-theme="dark"] .sx-sl h4,
    html[data-sx-theme="dark"] .sx-sl h5,
    html[data-bs-theme="dark"] .sx-sl h5,
    html[data-sx-theme="dark"] .sx-sl h6,
    html[data-bs-theme="dark"] .sx-sl h6,
    html[data-sx-theme="dark"] .sx-sl .sx-sl-card-title,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-card-title,
    html[data-sx-theme="dark"] .sx-sl .stat-label,
    html[data-bs-theme="dark"] .sx-sl .stat-label,
    html[data-sx-theme="dark"] .sx-sl .stat-value,
    html[data-bs-theme="dark"] .sx-sl .stat-value,
    html[data-sx-theme="dark"] .sx-sl .table,
    html[data-bs-theme="dark"] .sx-sl .table,
    html[data-sx-theme="dark"] .sx-sl .table tbody td,
    html[data-bs-theme="dark"] .sx-sl .table tbody td,
    html[data-sx-theme="dark"] .sx-sl .text-body,
    html[data-bs-theme="dark"] .sx-sl .text-body,
    html[data-sx-theme="dark"] .sx-sl a:not(.btn),
    html[data-bs-theme="dark"] .sx-sl a:not(.btn),
    html[data-sx-theme="dark"] .sx-sl .form-check-label,
    html[data-bs-theme="dark"] .sx-sl .form-check-label,
    html[data-sx-theme="dark"] .sx-sl .nav-tabs .nav-link,
    html[data-bs-theme="dark"] .sx-sl .nav-tabs .nav-link,
    html[data-sx-theme="dark"] .sx-sl .font-monospace,
    html[data-bs-theme="dark"] .sx-sl .font-monospace {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sl .text-muted,
    html[data-bs-theme="dark"] .sx-sl .text-muted,
    html[data-sx-theme="dark"] .sx-sl .form-label,
    html[data-bs-theme="dark"] .sx-sl .form-label,
    html[data-sx-theme="dark"] .sx-sl .table thead th,
    html[data-bs-theme="dark"] .sx-sl .table thead th,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_filter,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_processing,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_processing {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-sl-stat,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-stat,
    html[data-sx-theme="dark"] .sx-sl .sx-sl-card,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-card,
    html[data-sx-theme="dark"] .sx-sl .card,
    html[data-bs-theme="dark"] .sx-sl .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-sl .sx-sl-card-head,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-sl .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .table,
    html[data-bs-theme="dark"] .sx-sl .table {
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-hover-bg: rgba(255, 180, 180, 0.06);
        --bs-table-hover-color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-sl .table thead th,
    html[data-bs-theme="dark"] .sx-sl .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .table tbody td,
    html[data-bs-theme="dark"] .sx-sl .table tbody td,
    html[data-sx-theme="dark"] .sx-sl .table tbody tr,
    html[data-bs-theme="dark"] .sx-sl .table tbody tr {
        background-color: transparent !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-sl .table tbody tr:hover,
    html[data-sx-theme="dark"] .sx-sl .table tbody tr:hover td,
    html[data-bs-theme="dark"] .sx-sl .table tbody tr:hover td {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .form-control,
    html[data-bs-theme="dark"] .sx-sl .form-control,
    html[data-sx-theme="dark"] .sx-sl .form-select,
    html[data-bs-theme="dark"] .sx-sl .form-select,
    html[data-sx-theme="dark"] .sx-sl .kbd-textarea,
    html[data-bs-theme="dark"] .sx-sl .kbd-textarea,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sl .form-control::placeholder,
    html[data-bs-theme="dark"] .sx-sl .form-control::placeholder,
    html[data-sx-theme="dark"] .sx-sl .kbd-textarea::placeholder,
    html[data-bs-theme="dark"] .sx-sl .kbd-textarea::placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] .sx-sl .form-control:focus,
    html[data-bs-theme="dark"] .sx-sl .form-control:focus,
    html[data-sx-theme="dark"] .sx-sl .form-select:focus,
    html[data-bs-theme="dark"] .sx-sl .form-select:focus,
    html[data-sx-theme="dark"] .sx-sl .kbd-textarea:focus,
    html[data-bs-theme="dark"] .sx-sl .kbd-textarea:focus {
        border-color: #c43a3a !important;
        box-shadow: 0 0 0 0.2rem rgba(196, 58, 58, 0.25) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .form-select,
    html[data-bs-theme="dark"] .sx-sl .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23d7c4c8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }
    html[data-sx-theme="dark"] .sx-sl .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-sl .select2-container--default .select2-selection--single,
    html[data-sx-theme="dark"] .sx-sl .select2-container--default .select2-selection--multiple,
    html[data-bs-theme="dark"] .sx-sl .select2-container--default .select2-selection--multiple {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-sl .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sl .select2-container--default .select2-selection--single .select2-selection__placeholder,
    html[data-bs-theme="dark"] .sx-sl .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #a8949a !important;
    }
    html[data-sx-theme="dark"] .sx-sl .select2-container--default .select2-selection--single .select2-selection__arrow b,
    html[data-bs-theme="dark"] .sx-sl .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #d7c4c8 transparent transparent transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sl .select2-container--default .select2-selection__clear,
    html[data-bs-theme="dark"] .sx-sl .select2-container--default .select2-selection__clear {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-sl .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-sl .btn-outline-primary:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-sl .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-sl .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.12) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-outline-danger,
    html[data-bs-theme="dark"] .sx-sl .btn-outline-danger {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-outline-danger:hover,
    html[data-bs-theme="dark"] .sx-sl .btn-outline-danger:hover {
        background: rgba(148, 0, 0, 0.45) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .btn-primary,
    html[data-bs-theme="dark"] .sx-sl .btn-primary,
    html[data-sx-theme="dark"] .sx-sl .btn-success,
    html[data-bs-theme="dark"] .sx-sl .btn-success,
    html[data-sx-theme="dark"] .sx-sl .btn-danger,
    html[data-bs-theme="dark"] .sx-sl .btn-danger,
    html[data-sx-theme="dark"] .sx-sl .btn-primary i,
    html[data-bs-theme="dark"] .sx-sl .btn-primary i,
    html[data-sx-theme="dark"] .sx-sl .btn-primary i:before,
    html[data-bs-theme="dark"] .sx-sl .btn-primary i:before {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.3) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-sl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .form-check-input,
    html[data-bs-theme="dark"] .sx-sl .form-check-input {
        background-color: #1c1417;
        border-color: rgba(255, 180, 180, 0.35);
    }
    html[data-sx-theme="dark"] .sx-sl .form-check-input:checked,
    html[data-bs-theme="dark"] .sx-sl .form-check-input:checked {
        background-color: #940000;
        border-color: #940000;
    }
    html[data-sx-theme="dark"] .sx-sl .spinner-border,
    html[data-bs-theme="dark"] .sx-sl .spinner-border {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-sl-stat.is-login .stat-ico,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-stat.is-login .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-sl-stat.is-visitors .stat-ico,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-stat.is-visitors .stat-ico {
        background: rgba(59, 76, 192, 0.3) !important;
        color: #c5cdff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-sl-stat.is-photo .stat-ico,
    html[data-bs-theme="dark"] .sx-sl .sx-sl-stat.is-photo .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .nav-tabs,
    html[data-bs-theme="dark"] .sx-sl .nav-tabs {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-sl .nav-tabs .nav-link,
    html[data-bs-theme="dark"] .sx-sl .nav-tabs .nav-link {
        color: #d7c4c8 !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-sl .nav-tabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-sl .nav-tabs .nav-link.active {
        color: #ffb4b4 !important;
        background: rgba(148, 0, 0, 0.14) !important;
        border-bottom: 3px solid #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .nav-tabs .badge,
    html[data-bs-theme="dark"] .sx-sl .nav-tabs .badge {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .nav-tabs .nav-link:not(.active) .badge,
    html[data-bs-theme="dark"] .sx-sl .nav-tabs .nav-link:not(.active) .badge {
        background: #5a4a4e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-avatar,
    html[data-bs-theme="dark"] .sx-sl .sx-avatar {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-badge.is-blocked,
    html[data-bs-theme="dark"] .sx-sl .sx-badge.is-blocked {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-badge.is-pages,
    html[data-bs-theme="dark"] .sx-sl .sx-badge.is-pages {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-sl .sx-maroon,
    html[data-bs-theme="dark"] .sx-sl .sx-maroon,
    html[data-sx-theme="dark"] .sx-sl a.sx-maroon,
    html[data-bs-theme="dark"] .sx-sl a.sx-maroon,
    html[data-sx-theme="dark"] #logDetailsModal .sx-maroon,
    html[data-bs-theme="dark"] #logDetailsModal .sx-maroon,
    html[data-sx-theme="dark"] #visitorDetailsModal .sx-maroon,
    html[data-bs-theme="dark"] #visitorDetailsModal .sx-maroon {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .modal-content,
    html[data-bs-theme="dark"] #logDetailsModal .modal-content,
    html[data-sx-theme="dark"] #visitorDetailsModal .modal-content,
    html[data-bs-theme="dark"] #visitorDetailsModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-top: 4px solid #940000 !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
    }
    html[data-sx-theme="dark"] #logDetailsModal .modal-title,
    html[data-bs-theme="dark"] #logDetailsModal .modal-title,
    html[data-sx-theme="dark"] #visitorDetailsModal .modal-title,
    html[data-bs-theme="dark"] #visitorDetailsModal .modal-title,
    html[data-sx-theme="dark"] #logDetailsModal .modal-header .btn-close,
    html[data-bs-theme="dark"] #logDetailsModal .modal-header .btn-close,
    html[data-sx-theme="dark"] #visitorDetailsModal .modal-header .btn-close,
    html[data-bs-theme="dark"] #visitorDetailsModal .modal-header .btn-close {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .bg-light,
    html[data-bs-theme="dark"] #logDetailsModal .bg-light,
    html[data-sx-theme="dark"] #visitorDetailsModal .bg-light,
    html[data-bs-theme="dark"] #visitorDetailsModal .bg-light,
    html[data-sx-theme="dark"] #logDetailsModal .modal-footer,
    html[data-bs-theme="dark"] #logDetailsModal .modal-footer,
    html[data-sx-theme="dark"] #visitorDetailsModal .modal-footer,
    html[data-bs-theme="dark"] #visitorDetailsModal .modal-footer {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .border-end,
    html[data-bs-theme="dark"] #logDetailsModal .border-end,
    html[data-sx-theme="dark"] #visitorDetailsModal .border-end,
    html[data-bs-theme="dark"] #visitorDetailsModal .border-end,
    html[data-sx-theme="dark"] #logDetailsModal .border,
    html[data-bs-theme="dark"] #logDetailsModal .border,
    html[data-sx-theme="dark"] #visitorDetailsModal .border,
    html[data-bs-theme="dark"] #visitorDetailsModal .border {
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .modal-body,
    html[data-bs-theme="dark"] #logDetailsModal .modal-body,
    html[data-sx-theme="dark"] #visitorDetailsModal .modal-body,
    html[data-bs-theme="dark"] #visitorDetailsModal .modal-body,
    html[data-sx-theme="dark"] #logDetailsModal h6,
    html[data-bs-theme="dark"] #logDetailsModal h6,
    html[data-sx-theme="dark"] #visitorDetailsModal h6,
    html[data-bs-theme="dark"] #visitorDetailsModal h6,
    html[data-sx-theme="dark"] #logDetailsModal p,
    html[data-bs-theme="dark"] #logDetailsModal p,
    html[data-sx-theme="dark"] #visitorDetailsModal p,
    html[data-bs-theme="dark"] #visitorDetailsModal p,
    html[data-sx-theme="dark"] #logDetailsModal .fw-bold,
    html[data-bs-theme="dark"] #logDetailsModal .fw-bold,
    html[data-sx-theme="dark"] #visitorDetailsModal .fw-bold,
    html[data-bs-theme="dark"] #visitorDetailsModal .fw-bold,
    html[data-sx-theme="dark"] #logDetailsModal small,
    html[data-bs-theme="dark"] #logDetailsModal small,
    html[data-sx-theme="dark"] #visitorDetailsModal small,
    html[data-bs-theme="dark"] #visitorDetailsModal small {
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] #logDetailsModal .text-muted,
    html[data-bs-theme="dark"] #logDetailsModal .text-muted,
    html[data-sx-theme="dark"] #visitorDetailsModal .text-muted,
    html[data-bs-theme="dark"] #visitorDetailsModal .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .nav-tabs,
    html[data-bs-theme="dark"] #logDetailsModal .nav-tabs,
    html[data-sx-theme="dark"] #visitorDetailsModal .nav-tabs,
    html[data-bs-theme="dark"] #visitorDetailsModal .nav-tabs {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .nav-tabs .nav-link,
    html[data-bs-theme="dark"] #logDetailsModal .nav-tabs .nav-link,
    html[data-sx-theme="dark"] #visitorDetailsModal .nav-tabs .nav-link,
    html[data-bs-theme="dark"] #visitorDetailsModal .nav-tabs .nav-link {
        color: #d7c4c8 !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .nav-tabs .nav-link.active,
    html[data-bs-theme="dark"] #logDetailsModal .nav-tabs .nav-link.active,
    html[data-sx-theme="dark"] #visitorDetailsModal .nav-tabs .nav-link.active,
    html[data-bs-theme="dark"] #visitorDetailsModal .nav-tabs .nav-link.active {
        color: #ffb4b4 !important;
        background: rgba(148, 0, 0, 0.14) !important;
        border-bottom: 3px solid #940000 !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .activity-item,
    html[data-bs-theme="dark"] #logDetailsModal .activity-item,
    html[data-sx-theme="dark"] #visitorDetailsModal .activity-item,
    html[data-bs-theme="dark"] #visitorDetailsModal .activity-item {
        border-left-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .activity-item::before,
    html[data-bs-theme="dark"] #logDetailsModal .activity-item::before,
    html[data-sx-theme="dark"] #visitorDetailsModal .activity-item::before,
    html[data-bs-theme="dark"] #visitorDetailsModal .activity-item::before {
        background: #5a4a4e;
    }
    html[data-sx-theme="dark"] #logDetailsModal .activity-item.active::before,
    html[data-bs-theme="dark"] #logDetailsModal .activity-item.active::before,
    html[data-sx-theme="dark"] #visitorDetailsModal .activity-item.active::before,
    html[data-bs-theme="dark"] #visitorDetailsModal .activity-item.active::before {
        background: #940000;
    }
    html[data-sx-theme="dark"] #logDetailsModal .activity-item .fw-bold,
    html[data-bs-theme="dark"] #logDetailsModal .activity-item .fw-bold,
    html[data-sx-theme="dark"] #visitorDetailsModal .activity-item .fw-bold,
    html[data-bs-theme="dark"] #visitorDetailsModal .activity-item .fw-bold {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .btn-outline-primary,
    html[data-bs-theme="dark"] #logDetailsModal .btn-outline-primary,
    html[data-sx-theme="dark"] #visitorDetailsModal .btn-outline-primary,
    html[data-bs-theme="dark"] #visitorDetailsModal .btn-outline-primary {
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .btn-outline-primary:hover,
    html[data-bs-theme="dark"] #logDetailsModal .btn-outline-primary:hover,
    html[data-sx-theme="dark"] #visitorDetailsModal .btn-outline-primary:hover,
    html[data-bs-theme="dark"] #visitorDetailsModal .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .btn-outline-secondary,
    html[data-bs-theme="dark"] #logDetailsModal .btn-outline-secondary,
    html[data-sx-theme="dark"] #visitorDetailsModal .btn-outline-secondary,
    html[data-bs-theme="dark"] #visitorDetailsModal .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .btn-success,
    html[data-bs-theme="dark"] #logDetailsModal .btn-success,
    html[data-sx-theme="dark"] #logDetailsModal .btn-danger,
    html[data-bs-theme="dark"] #logDetailsModal .btn-danger {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #logDetailsModal .bg-white,
    html[data-bs-theme="dark"] #logDetailsModal .bg-white,
    html[data-sx-theme="dark"] #visitorDetailsModal .bg-white,
    html[data-bs-theme="dark"] #visitorDetailsModal .bg-white {
        background: #1c1417 !important;
    }
</style>

<div class="sx-sl">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-sl-stat is-login h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.system_logs.logins_today') }}</span>
                        <span class="stat-value" id="statLoginToday">{{ (int) ($loginLogsToday ?? 0) }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-sign-in-alt"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-sl-stat is-visitors h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.system_logs.visitor_ips_today') }}</span>
                        <span class="stat-value" id="statVisitorsToday">{{ (int) ($webVisitorsToday ?? 0) }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-6">
            <div class="card sx-sl-stat is-photo h-100">
                <div class="card-body">
                    <div class="sx-photo-toggle">
                        <div class="d-flex align-items-start gap-3">
                            <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-camera"></i></span>
                            <div>
                                <span class="stat-label">{{ __('superadmin.system_logs.photo_capture') }}</span>
                                <span class="stat-value is-sm" id="loginPhotoStatusLabel">
                                    {{ !empty($loginPhotoCaptureEnabled) ? __('superadmin.system_logs.photo_enabled') : __('superadmin.system_logs.photo_disabled') }}
                                </span>
                            </div>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="loginPhotoCaptureToggle"
                                   {{ !empty($loginPhotoCaptureEnabled) ? 'checked' : '' }}
                                   style="width:2.5em; height:1.3em; cursor:pointer;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-sl-card mb-3">
        <div class="card-body">
            <div class="sx-sl-card-head">
                <h6 class="sx-sl-card-title"><i class="fi fi-rr-filters me-1"></i> {{ __('superadmin.system_logs.filters') }}</h6>
                <span class="small text-muted">{{ __('superadmin.system_logs.filters_desc') }}</span>
            </div>
            <form id="filterForm" class="row g-3 align-items-end" onsubmit="return false;">
                <div class="col-md-3">
                    <label class="form-label" for="start_date">{{ __('superadmin.system_logs.from_date') }}</label>
                    <input type="date" id="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="end_date">{{ __('superadmin.system_logs.to_date') }}</label>
                    <input type="date" id="end_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="filter_username" id="filterSearchLabel">{{ __('superadmin.system_logs.user_username') }}</label>
                    <input type="text" id="filter_username" class="form-control" placeholder="{{ __('superadmin.system_logs.search_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="reloadActiveTab()">
                        <i class="fi fi-rr-filters me-1"></i> {{ __('superadmin.system_logs.sync') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card sx-sl-card">
        <div class="card-body">
            <div class="sx-sl-card-head mb-0 border-0 pb-0">
                <h6 class="sx-sl-card-title"><i class="fi fi-rr-shield-check me-1"></i> {{ __('superadmin.system_logs.security_logs') }}</h6>
                <span class="small text-muted">{{ __('superadmin.system_logs.security_logs_desc') }}</span>
            </div>
            <ul class="nav nav-tabs mt-3" id="mainLogsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-logs-tab" data-bs-toggle="tab" data-bs-target="#login-logs-pane" type="button" role="tab">
                        <i class="fi fi-rr-sign-in-alt me-1"></i> {{ __('superadmin.system_logs.tab_login_logs') }}
                        <span class="badge ms-2" id="loginTodayBadge">{{ __('superadmin.system_logs.today_count', ['count' => (int) ($loginLogsToday ?? 0)]) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="web-visitors-tab" data-bs-toggle="tab" data-bs-target="#web-visitors-pane" type="button" role="tab">
                        <i class="fi fi-rr-globe me-1"></i> {{ __('superadmin.system_logs.tab_web_visitors') }}
                        <span class="badge ms-2" id="visitorsTodayBadge">{{ __('superadmin.system_logs.today_ips', ['count' => (int) ($webVisitorsToday ?? 0)]) }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="login-logs-pane" role="tabpanel">
                    <div class="sx-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100" id="logsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('superadmin.system_logs.th_user') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_school') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_device_os') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_ip') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_location') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_login_time') }}</th>
                                        <th class="text-end">{{ __('superadmin.system_logs.th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="logsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="web-visitors-pane" role="tabpanel">
                    <div class="sx-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100" id="visitorsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('superadmin.system_logs.th_ip') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_email') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_location') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_pages') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_last_page') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_device') }}</th>
                                        <th>{{ __('superadmin.system_logs.th_last_visit') }}</th>
                                        <th class="text-end">{{ __('superadmin.system_logs.th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="visitorsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fi fi-rr-info me-2"></i>{{ __('superadmin.system_logs.log_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-4 bg-light p-4 text-center border-end">
                        <div id="logUserPhoto" class="mb-3"></div>
                        <h6 id="logUsername" class="fw-bold mb-1"></h6>
                        <p id="logUserType" class="text-muted small mb-4"></p>
                        <div class="text-start small">
                            <p class="mb-1 text-muted">{{ __('superadmin.system_logs.th_ip') }}</p>
                            <p id="logIp" class="fw-bold mb-3"></p>
                            <p class="mb-1 text-muted">{{ __('superadmin.system_logs.th_device') }}</p>
                            <p id="logDevice" class="fw-bold mb-3"></p>
                            <p class="mb-1 text-muted">{{ __('superadmin.system_logs.browser_os') }}</p>
                            <p id="logBrowserOs" class="fw-bold mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-8 p-0">
                        <ul class="nav nav-tabs nav-fill bg-light" id="logTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-pane" type="button"><i class="fi fi-rr-marker me-1"></i>{{ __('superadmin.system_logs.map') }}</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane" type="button"><i class="fi fi-rr-list me-1"></i>{{ __('superadmin.system_logs.activities') }}</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" id="photo-tab" data-bs-toggle="tab" data-bs-target="#photo-pane" type="button"><i class="fi fi-rr-camera me-1"></i>{{ __('superadmin.system_logs.photo') }}</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="logTabContent">
                            <div class="tab-pane fade show active p-3" id="map-pane">
                                <div id="map" style="height: 350px; width: 100%; border-radius: 8px;"></div>
                                <div id="locationDetails" class="mt-2 small text-muted"></div>
                            </div>
                            <div class="tab-pane fade p-3" id="activities-pane">
                                <div id="activitiesList" style="max-height: 350px; overflow-y: auto;"></div>
                            </div>
                            <div class="tab-pane fade p-3 text-center" id="photo-pane">
                                <div id="largePhotoContainer"></div>
                                <div class="mt-3">
                                    <a id="downloadPhotoBtn" href="#" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fi fi-rr-download me-1"></i>{{ __('superadmin.system_logs.download_image') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <div id="blockActionBtn"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.system_logs.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="visitorDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fi fi-rr-globe me-2"></i>{{ __('superadmin.system_logs.visitor_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-4 bg-light p-4 border-end">
                        <div class="text-center mb-3">
                            <div class="sx-avatar mx-auto" style="width:70px;height:70px;font-size:1.5rem;">
                                <i class="fi fi-rr-marker"></i>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-1 text-center" id="visitorIpLabel">-</h6>
                        <p class="text-muted small mb-2 text-center" id="visitorEmailLabel">{{ __('superadmin.system_logs.no_email') }}</p>
                        <p class="text-muted small mb-4 text-center" id="visitorLocationLabel">{{ __('superadmin.system_logs.unknown_location') }}</p>
                        <div class="small">
                            <p class="mb-1 text-muted">{{ __('superadmin.system_logs.th_device') }}</p>
                            <p id="visitorDevice" class="fw-bold mb-3">-</p>
                            <p class="mb-1 text-muted">{{ __('superadmin.system_logs.browser_os_slash') }}</p>
                            <p id="visitorBrowserOs" class="fw-bold mb-3">-</p>
                            <p class="mb-1 text-muted">{{ __('superadmin.system_logs.pages_visited') }}</p>
                            <p id="visitorPagesCount" class="fw-bold mb-0">0</p>
                        </div>
                    </div>
                    <div class="col-md-8 p-0">
                        <ul class="nav nav-tabs nav-fill bg-light" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3" data-bs-toggle="tab" data-bs-target="#visitor-map-pane" type="button">
                                    <i class="fi fi-rr-marker me-1"></i>{{ __('superadmin.system_logs.th_location') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#visitor-pages-pane" type="button">
                                    <i class="fi fi-rr-list me-1"></i>{{ __('superadmin.system_logs.pages_visited') }}
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active p-3" id="visitor-map-pane">
                                <div id="visitorMap" style="height: 320px; width: 100%; border-radius: 8px;"></div>
                                <div id="visitorLocationDetails" class="mt-2 small text-muted"></div>
                            </div>
                            <div class="tab-pane fade p-3" id="visitor-pages-pane">
                                <div id="visitorPagesList" style="max-height: 360px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.system_logs.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChAk-LYD4BqrIktIngfd1r2EWC1hL-PTI&callback=initMap&loading=async"></script>
<script>
    const SX = @json(__('superadmin.system_logs'));
    const SXC = @json(__('superadmin.common'));
    let map;
    let marker;
    let visitorMap;
    let visitorMarker;
    let dataTable;
    let visitorsTable;
    let activeMainTab = 'login';

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: -6.7924, lng: 39.2083 },
            zoom: 12,
        });
        marker = new google.maps.Marker({
            map: map,
            draggable: false,
        });

        visitorMap = new google.maps.Map(document.getElementById("visitorMap"), {
            center: { lat: -6.7924, lng: 39.2083 },
            zoom: 5,
        });
        visitorMarker = new google.maps.Marker({
            map: visitorMap,
            draggable: false,
        });
    }

    function updateTodayBadge(type, count) {
        if (type === 'login') {
            $('#loginTodayBadge').text(SX.today_count.replace(':count', count ?? 0));
            $('#statLoginToday').text(count ?? 0);
        } else {
            $('#visitorsTodayBadge').text(SX.today_ips.replace(':count', count ?? 0));
            $('#statVisitorsToday').text(count ?? 0);
        }
    }

    function deviceIcon(type) {
        if (type === 'Mobile') return 'fi fi-rr-smartphone';
        if (type === 'Tablet') return 'fi fi-rr-tablet';
        return 'fi fi-rr-laptop';
    }

    function initLogsTable() {
        if (!$.fn || !$.fn.DataTable) {
            console.error(SX.js.plugin_missing);
            $('#logsBody').html('<tr><td colspan="7" class="text-center text-danger py-4">' + SX.js.datatable_failed + '</td></tr>');
            return;
        }

        if ($.fn.DataTable.isDataTable('#logsTable')) {
            $('#logsTable').DataTable().destroy();
        }

        dataTable = $('#logsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']],
            searchDelay: 500,
            autoWidth: false,
            ajax: {
                url: @json(route('superadmin.system_logs.data')),
                type: 'GET',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.username = $('#filter_username').val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    render: function(log) {
                        const initial = (log.username || 'U').charAt(0).toUpperCase();
                        return `<div class="d-flex align-items-center">
                                    <div class="sx-avatar me-2">${initial}</div>
                                    <div>
                                        <div class="fw-bold">${log.username || ''}</div>
                                        <div class="text-muted small">${log.user_type || ''}</div>
                                    </div>
                                </div>`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `<div class="fw-bold small text-uppercase sx-maroon">${log.school ? log.school.school_name : SX.js.super_admin}</div>`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `<i class="${deviceIcon(log.device_type)} me-1"></i> ${log.device_brand || SX.js.unknown}
                                <div class="text-muted small">${log.os || '-'} / ${log.browser || '-'}</div>`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `${log.ip_address || '-'} ${log.is_blocked ? `<span class="sx-badge is-blocked ms-1">${SX.js.blocked}</span>` : ''}`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `<span class="small text-muted"><i class="fi fi-rr-marker me-1"></i> ${log.latitude ? (parseFloat(log.latitude).toFixed(4) + ', ' + parseFloat(log.longitude).toFixed(4)) : SX.js.no_gps}</span>`;
                    }
                },
                {
                    data: 'login_at',
                    render: function(value) {
                        if (!value) return '-';
                        const dt = new Date(value);
                        return `<div class="fw-bold small">${dt.toLocaleDateString()}</div><div class="text-muted small">${dt.toLocaleTimeString()}</div>`;
                    }
                },
                {
                    data: 'id',
                    searchable: false,
                    orderable: false,
                    className: 'text-end',
                    render: function(id) {
                        return `<button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${id})"><i class="fi fi-rr-eye me-1"></i>${SX.js.view}</button>`;
                    }
                }
            ],
            language: {
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_logs,
                search: SXC.search + ':',
                searchPlaceholder: SX.js.quick_search,
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });

        $('#logsTable').on('xhr.dt', function (e, settings, json) {
            if (json && typeof json.todayCount !== 'undefined') {
                updateTodayBadge('login', json.todayCount);
            }
        });
    }

    function initVisitorsTable() {
        if (!$.fn || !$.fn.DataTable) return;
        if (visitorsTable) return;

        visitorsTable = $('#visitorsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            responsive: true,
            pageLength: 10,
            order: [[6, 'desc']],
            searchDelay: 500,
            autoWidth: false,
            ajax: {
                url: @json(route('superadmin.system_logs.visitors_data')),
                type: 'GET',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.username = $('#filter_username').val();
                }
            },
            columns: [
                {
                    data: 'ip_address',
                    render: function(ip) {
                        return `<span class="fw-bold">${ip || '-'}</span>`;
                    }
                },
                {
                    data: 'email',
                    orderable: true,
                    render: function(email) {
                        if (!email) {
                            return `<span class="text-muted small">-</span>`;
                        }
                        return `<a href="mailto:${email}" class="small fw-semibold sx-maroon">${email}</a>`;
                    }
                },
                {
                    data: 'location',
                    orderable: false,
                    render: function(location) {
                        return `<span class="small text-muted"><i class="fi fi-rr-marker me-1"></i>${location || SX.js.unknown_location}</span>`;
                    }
                },
                {
                    data: 'pages_count',
                    render: function(count) {
                        return `<span class="sx-badge is-pages">${SX.js.pages_count.replace(':count', count || 1)}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(row) {
                        const page = row.page_name || SX.js.unknown_page;
                        const url = row.page_url || '';
                        return `<div class="fw-bold small">${page}</div>
                                <div class="text-muted small text-truncate" style="max-width:260px;" title="${url}">${url}</div>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(row) {
                        return `<i class="${deviceIcon(row.device_type)} me-1"></i> ${row.device_type || SX.js.unknown}
                                <div class="text-muted small">${row.browser || '-'} / ${row.os || '-'}</div>`;
                    }
                },
                {
                    data: 'visited_at',
                    render: function(value) {
                        if (!value) return '-';
                        const dt = new Date(value);
                        return `<div class="fw-bold small">${dt.toLocaleDateString()}</div><div class="text-muted small">${dt.toLocaleTimeString()}</div>`;
                    }
                },
                {
                    data: 'ip_address',
                    searchable: false,
                    orderable: false,
                    className: 'text-end',
                    render: function(ip) {
                        return `<button class="btn btn-sm btn-outline-primary" onclick='viewVisitorDetails(${JSON.stringify(ip || "")})'>
                                    <i class="fi fi-rr-eye me-1"></i>${SXC.view_more}
                                </button>`;
                    }
                }
            ],
            language: {
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_visitors,
                search: SXC.search + ':',
                searchPlaceholder: SX.js.search_visitors,
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });

        $('#visitorsTable').on('xhr.dt', function (e, settings, json) {
            if (json && typeof json.todayCount !== 'undefined') {
                updateTodayBadge('visitors', json.todayCount);
            }
        });
    }

    function viewVisitorDetails(ip) {
        if (!ip) return;

        $.get(@json(route('superadmin.system_logs.visitors_details')), { ip_address: ip }, function(res) {
            if (!res.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire(SX.js.error, res.message || SX.js.visitor_failed, 'error');
                }
                return;
            }

            const data = res.data;
            $('#visitorIpLabel').text(data.ip_address || '-');
            if (data.email) {
                $('#visitorEmailLabel').html('<i class="fi fi-rr-envelope me-1"></i><a href="mailto:' + data.email + '" class="fw-semibold sx-maroon">' + data.email + '</a>');
            } else {
                $('#visitorEmailLabel').text(SX.js.no_email);
            }
            $('#visitorLocationLabel').text(data.location || SX.js.unknown_location);
            $('#visitorDevice').text(data.device_type || SX.js.unknown);
            $('#visitorBrowserOs').text((data.browser || '-') + ' / ' + (data.os || '-'));
            $('#visitorPagesCount').text(SX.js.pages_count.replace(':count', data.pages_count || 0));

            if (data.latitude && data.longitude && visitorMap && visitorMarker) {
                const pos = { lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) };
                setTimeout(function() {
                    google.maps.event.trigger(visitorMap, 'resize');
                    visitorMap.setCenter(pos);
                    visitorMap.setZoom(10);
                    visitorMarker.setPosition(pos);
                }, 300);
                $('#visitorLocationDetails').text(`${data.location} • ${data.latitude}, ${data.longitude}`);
            } else {
                $('#visitorLocationDetails').text(SX.js.no_coords_visitor);
            }

            let pagesHtml = '';
            if (data.pages && data.pages.length) {
                data.pages.forEach(function(page) {
                    const visited = page.visited_at ? new Date(page.visited_at) : null;
                    pagesHtml += `
                        <div class="activity-item">
                            <div class="fw-bold small sx-maroon">${page.page_name || SX.js.unknown_page}</div>
                            <div class="text-muted" style="font-size: 10px;">
                                <span class="font-monospace">${page.page_url || ''}</span>
                            </div>
                            <div class="text-muted small" style="font-size: 10px;">
                                ${visited ? visited.toLocaleString() : '-'}
                            </div>
                        </div>
                    `;
                });
            } else {
                pagesHtml = '<div class="text-center py-5 text-muted"><i class="fi fi-rr-time-past mb-2 d-block" style="font-size:30px;"></i>' + SX.js.no_pages + '</div>';
            }
            $('#visitorPagesList').html(pagesHtml);

            const modal = new bootstrap.Modal(document.getElementById('visitorDetailsModal'));
            modal.show();
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.message || SX.js.visitor_failed;
            if (typeof Swal !== 'undefined') {
                Swal.fire(SX.js.error, msg, 'error');
            }
        });
    }

    function loadLogs() {
        if (!dataTable) {
            initLogsTable();
            return;
        }
        dataTable.ajax.reload(null, true);
    }

    function loadVisitors() {
        if (!visitorsTable) {
            initVisitorsTable();
            return;
        }
        visitorsTable.ajax.reload(null, true);
    }

    function reloadActiveTab() {
        if (activeMainTab === 'visitors') {
            loadVisitors();
        } else {
            loadLogs();
        }
    }

    function syncFilterLabels() {
        if (activeMainTab === 'visitors') {
            $('#filterSearchLabel').text(SX.js.ip_email_page);
            $('#filter_username').attr('placeholder', SX.js.search_visitors);
        } else {
            $('#filterSearchLabel').text(SX.js.user_username);
            $('#filter_username').attr('placeholder', SX.js.search_by_name);
        }
    }

    function viewDetails(id) {
        $.get(@json(url('super-admin/system-logs/details')) + '/' + id, function(res) {
            if (res.success) {
                const log = res.data;
                $('#logUsername').text(log.username);
                $('#logUserType').text(log.user_type);
                $('#logIp').text(log.ip_address);
                $('#logDevice').text(log.device_type + ' (' + (log.device_brand || SX.js.unknown) + ')');
                $('#logBrowserOs').text(log.os + ' / ' + log.browser);

                $('#logUserPhoto').html(`<div class="bg-white border rounded d-flex align-items-center justify-content-center mx-auto" style="width:150px; height:150px;"><i class="fi fi-rr-user" style="font-size:64px;color:#940000;"></i></div>`);

                if (log.latitude && log.longitude) {
                    const pos = { lat: parseFloat(log.latitude), lng: parseFloat(log.longitude) };
                    setTimeout(() => {
                        google.maps.event.trigger(map, 'resize');
                        map.setCenter(pos);
                        marker.setPosition(pos);
                    }, 300);
                    $('#locationDetails').text(SX.js.coordinates.replace(':lat', log.latitude).replace(':lng', log.longitude));
                } else {
                    $('#locationDetails').text(SX.js.gps_unavailable);
                }

                let actHtml = '';
                if (log.activities && log.activities.length > 0) {
                    log.activities.forEach(act => {
                        actHtml += `
                            <div class="activity-item">
                                <div class="fw-bold small sx-maroon">${act.friendly_description}</div>
                                <div class="text-muted" style="font-size: 10px;">
                                    ${act.method} <span class="font-monospace">${act.url}</span>
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">${new Date(act.created_at).toLocaleTimeString()}</div>
                            </div>
                        `;
                    });
                } else {
                    actHtml = '<div class="text-center py-5 text-muted"><i class="fi fi-rr-time-past mb-2 d-block" style="font-size:30px;"></i>' + SX.js.no_activity + '</div>';
                }
                $('#activitiesList').html(actHtml);

                if (log.photo_path) {
                    $('#largePhotoContainer').html(`<img src="/${log.photo_path}" class="img-fluid rounded shadow border" style="max-height:300px; border: 5px solid #fff !important;">`);
                    $('#downloadPhotoBtn').attr('href', `/${log.photo_path}`).show();
                } else {
                    $('#largePhotoContainer').html('<div class="text-center py-5 text-muted"><i class="fi fi-rr-camera mb-2 d-block" style="font-size:30px;"></i>' + SX.js.no_photo + '</div>');
                    $('#downloadPhotoBtn').hide();
                }

                if (log.is_blocked) {
                    $('#blockActionBtn').html(`<button class="btn btn-success" onclick="unblockIp('${log.ip_address}')"><i class="fi fi-rr-unlock me-1"></i>${SX.js.unblock_ip}</button>`);
                } else {
                    $('#blockActionBtn').html(`<button class="btn btn-danger" onclick="blockIp('${log.ip_address}')"><i class="fi fi-rr-shield me-1"></i>${SX.js.block_ip}</button>`);
                }

                const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
                modal.show();
            }
        });
    }

    function blockIp(ip) {
        const reason = prompt(SX.js.block_reason_prompt);
        if (reason === null) return;

        $.post(@json(route('superadmin.system_logs.block_ip')), {
            _token: @json(csrf_token()),
            ip_address: ip,
            reason: reason
        }, function(res) {
            if (res.success) {
                Swal.fire(SX.js.success, res.message, 'success');
                loadLogs();
                bootstrap.Modal.getInstance(document.getElementById('logDetailsModal')).hide();
            }
        });
    }

    function unblockIp(ip) {
        if (!confirm(SX.js.unblock_confirm)) return;

        $.post(@json(route('superadmin.system_logs.unblock_ip')), {
            _token: @json(csrf_token()),
            ip_address: ip
        }, function(res) {
            if (res.success) {
                Swal.fire(SX.js.success, res.message, 'success');
                loadLogs();
                bootstrap.Modal.getInstance(document.getElementById('logDetailsModal')).hide();
            }
        });
    }

    function updateLoginPhotoCaptureSetting(enabled) {
        const toggle = $('#loginPhotoCaptureToggle');
        toggle.prop('disabled', true);

        $.post(@json(route('superadmin.system_logs.login_photo_capture')), {
            _token: @json(csrf_token()),
            enabled: enabled ? 1 : 0
        }, function(res) {
            if (res.success) {
                $('#loginPhotoStatusLabel').text(enabled ? SX.js.enabled : SX.js.disabled);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: SX.js.updated,
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
            } else {
                toggle.prop('checked', !enabled);
                if (typeof Swal !== 'undefined') {
                    Swal.fire(SX.js.error, res.message || SX.js.update_failed, 'error');
                }
            }
        }).fail(function(xhr) {
            toggle.prop('checked', !enabled);
            const msg = xhr.responseJSON?.message || SX.js.update_failed;
            if (typeof Swal !== 'undefined') {
                Swal.fire(SX.js.error, msg, 'error');
            }
        }).always(function() {
            toggle.prop('disabled', false);
        });
    }

    $(document).ready(function() {
        loadLogs();

        $('#loginPhotoCaptureToggle').on('change', function() {
            updateLoginPhotoCaptureSetting($(this).is(':checked'));
        });

        $('#login-logs-tab').on('shown.bs.tab', function() {
            activeMainTab = 'login';
            syncFilterLabels();
            loadLogs();
        });

        $('#web-visitors-tab').on('shown.bs.tab', function() {
            activeMainTab = 'visitors';
            syncFilterLabels();
            loadVisitors();
            setTimeout(function() {
                if (visitorsTable) visitorsTable.columns.adjust();
            }, 100);
        });

        $('#start_date, #end_date').on('change', function() {
            reloadActiveTab();
        });

        let searchTimer;
        $('#filter_username').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(reloadActiveTab, 500);
        });
    });
</script>
@endpush
