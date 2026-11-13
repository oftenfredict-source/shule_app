@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.customer_care'))
@section('page_breadcrumb', __('superadmin.pages.customer_care'))

@section('page_actions')
    <a href="{{ route('superadmin.customer_inquiries') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-envelope me-1"></i> {{ __('superadmin.customer_care.inquiries') }}
    </a>
    <a href="{{ route('superadmin.user_password') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-user-key me-1"></i> {{ __('superadmin.customer_care.user_password') }}
    </a>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc span:not(.sx-badge):not(.status-marker),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-cc a:not(.btn) {
        color: #000 !important;
    }
    .sx-cc .btn-primary,
    .sx-cc a.btn-primary,
    .sx-cc .btn-primary i,
    .sx-cc .btn-primary i:before,
    .sx-cc .text-white {
        color: #fff !important;
    }
    .sx-cc .sx-cc-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-cc .sx-cc-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-cc .sx-cc-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-cc .sx-cc-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-cc .sx-cc-stat .stat-ico {
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
    .sx-cc .sx-cc-stat .stat-ico i,
    .sx-cc .sx-cc-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-cc .sx-cc-stat.is-loaded .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-cc .sx-cc-stat.is-ready .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-cc .sx-cc-stat.is-missing .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-cc .sx-cc-stat.is-selected .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-cc .sx-cc-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-cc .sx-cc-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-cc .sx-cc-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-cc .sx-cc-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-cc .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-cc .form-select,
    .sx-cc .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-cc .form-select:focus,
    .sx-cc .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-cc .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-cc .btn-primary:hover,
    .sx-cc .btn-primary:focus {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-cc .btn-primary:disabled { opacity: 0.55; }
    .sx-cc .sx-selected-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.75rem;
        background: #f7f7f7;
        border: 1px solid #e6e6e6;
        border-radius: 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-cc .sx-selected-badge strong { color: #940000 !important; }
    .sx-cc .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-cc .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-cc .table thead th {
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
    .sx-cc .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-cc .table tbody tr:hover { background: #fffafa; }
    .sx-cc .school-logo {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e0e0e0;
        background: #fff;
    }
    .sx-cc .school-logo-fallback {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid #e0e0e0;
        background: #f7f7f7;
        color: #940000 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .sx-cc .sx-school-name { font-weight: 600; color: #000 !important; }
    .sx-cc .sx-username { font-weight: 700; color: #940000 !important; }
    .sx-cc .sx-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        border-radius: 0.3rem;
        line-height: 1;
    }
    .sx-cc .sx-badge-type {
        background: #f5f5f5;
        color: #333 !important;
        border: 1px solid #e6e6e6;
    }
    .sx-cc .sx-badge-ready { background: #e8f7ec; color: #145c2a !important; }
    .sx-cc .sx-badge-missing { background: #fde8e8; color: #940000 !important; }
    .sx-cc .sx-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #666 !important;
    }
    .sx-cc .sx-empty i {
        display: block;
        font-size: 2rem;
        color: #940000 !important;
        margin-bottom: 0.65rem;
    }
    .sx-cc .sx-empty i:before { color: inherit !important; }
    .sx-cc .sx-progress-wrap {
        border-top: 1px solid #eee;
        background: #fafafa;
        padding: 1rem 1.25rem;
    }
    .sx-cc .progress {
        height: 10px;
        border-radius: 0.35rem;
        background: #ececec;
    }
    .sx-cc .progress-bar { background: #940000 !important; }
    .sx-cc .select2-container--default .select2-selection--single {
        height: 40px;
        border-radius: 0.45rem !important;
        border: 1px solid #d9d9d9;
        display: flex;
        align-items: center;
    }
    .sx-cc .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
        color: #000;
    }
    .sx-cc .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .sx-cc .select2-container--default.select2-container--focus .select2-selection--single,
    .sx-cc .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-cc .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 36px;
        padding: 0.25rem 0.65rem;
    }
    .sx-cc .dataTables_wrapper .dataTables_length select {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
        margin: 0 0.35rem;
    }
    .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }
    .sx-cc .text-success { color: #145c2a !important; }
    .sx-cc .text-danger { color: #940000 !important; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-cc,
    html[data-bs-theme="dark"] .sx-cc,
    html[data-sx-theme="dark"] .sx-cc p,
    html[data-bs-theme="dark"] .sx-cc p,
    html[data-sx-theme="dark"] .sx-cc span:not(.sx-badge):not(.badge):not(.stat-ico):not(.status-marker),
    html[data-bs-theme="dark"] .sx-cc span:not(.sx-badge):not(.badge):not(.stat-ico):not(.status-marker),
    html[data-sx-theme="dark"] .sx-cc small,
    html[data-bs-theme="dark"] .sx-cc small,
    html[data-sx-theme="dark"] .sx-cc strong,
    html[data-bs-theme="dark"] .sx-cc strong,
    html[data-sx-theme="dark"] .sx-cc div,
    html[data-bs-theme="dark"] .sx-cc div,
    html[data-sx-theme="dark"] .sx-cc label,
    html[data-bs-theme="dark"] .sx-cc label,
    html[data-sx-theme="dark"] .sx-cc h1,
    html[data-bs-theme="dark"] .sx-cc h1,
    html[data-sx-theme="dark"] .sx-cc h2,
    html[data-bs-theme="dark"] .sx-cc h2,
    html[data-sx-theme="dark"] .sx-cc h3,
    html[data-bs-theme="dark"] .sx-cc h3,
    html[data-sx-theme="dark"] .sx-cc h4,
    html[data-bs-theme="dark"] .sx-cc h4,
    html[data-sx-theme="dark"] .sx-cc h5,
    html[data-bs-theme="dark"] .sx-cc h5,
    html[data-sx-theme="dark"] .sx-cc h6,
    html[data-bs-theme="dark"] .sx-cc h6,
    html[data-sx-theme="dark"] .sx-cc .sx-cc-card-title,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-card-title,
    html[data-sx-theme="dark"] .sx-cc .stat-label,
    html[data-bs-theme="dark"] .sx-cc .stat-label,
    html[data-sx-theme="dark"] .sx-cc .stat-value,
    html[data-bs-theme="dark"] .sx-cc .stat-value,
    html[data-sx-theme="dark"] .sx-cc .table,
    html[data-bs-theme="dark"] .sx-cc .table,
    html[data-sx-theme="dark"] .sx-cc .table tbody td,
    html[data-bs-theme="dark"] .sx-cc .table tbody td,
    html[data-sx-theme="dark"] .sx-cc .sx-school-name,
    html[data-bs-theme="dark"] .sx-cc .sx-school-name,
    html[data-sx-theme="dark"] .sx-cc a:not(.btn),
    html[data-bs-theme="dark"] .sx-cc a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-cc .text-muted,
    html[data-bs-theme="dark"] .sx-cc .text-muted,
    html[data-sx-theme="dark"] .sx-cc .form-label,
    html[data-bs-theme="dark"] .sx-cc .form-label,
    html[data-sx-theme="dark"] .sx-cc .table thead th,
    html[data-bs-theme="dark"] .sx-cc .table thead th,
    html[data-sx-theme="dark"] .sx-cc .sx-empty,
    html[data-bs-theme="dark"] .sx-cc .sx-empty,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-cc-stat,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-stat,
    html[data-sx-theme="dark"] .sx-cc .sx-cc-card,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-card,
    html[data-sx-theme="dark"] .sx-cc .card,
    html[data-bs-theme="dark"] .sx-cc .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-cc .sx-cc-card-head,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-cc .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .table thead th,
    html[data-bs-theme="dark"] .sx-cc .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .table tbody td,
    html[data-bs-theme="dark"] .sx-cc .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-cc .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .form-control,
    html[data-bs-theme="dark"] .sx-cc .form-control,
    html[data-sx-theme="dark"] .sx-cc .form-select,
    html[data-bs-theme="dark"] .sx-cc .form-select,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-cc .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-cc .select2-container--default .select2-selection--single {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-cc .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-username,
    html[data-bs-theme="dark"] .sx-cc .sx-username,
    html[data-sx-theme="dark"] .sx-cc .sx-empty i,
    html[data-bs-theme="dark"] .sx-cc .sx-empty i,
    html[data-sx-theme="dark"] .sx-cc .school-logo-fallback,
    html[data-bs-theme="dark"] .sx-cc .school-logo-fallback {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .school-logo-fallback,
    html[data-bs-theme="dark"] .sx-cc .school-logo-fallback {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .school-logo,
    html[data-bs-theme="dark"] .sx-cc .school-logo {
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-selected-badge,
    html[data-bs-theme="dark"] .sx-cc .sx-selected-badge {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-selected-badge strong,
    html[data-bs-theme="dark"] .sx-cc .sx-selected-badge strong {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-progress-wrap,
    html[data-bs-theme="dark"] .sx-cc .sx-progress-wrap {
        background: #1c1417 !important;
        border-top-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .progress,
    html[data-bs-theme="dark"] .sx-cc .progress {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-cc .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-badge-type,
    html[data-bs-theme="dark"] .sx-cc .sx-badge-type {
        background: #2c2125 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-badge-ready,
    html[data-bs-theme="dark"] .sx-cc .sx-badge-ready {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-badge-missing,
    html[data-bs-theme="dark"] .sx-cc .sx-badge-missing {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-cc-stat.is-loaded .stat-ico,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-stat.is-loaded .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-cc-stat.is-ready .stat-ico,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-stat.is-ready .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cc .sx-cc-stat.is-missing .stat-ico,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-stat.is-missing .stat-ico,
    html[data-sx-theme="dark"] .sx-cc .sx-cc-stat.is-selected .stat-ico,
    html[data-bs-theme="dark"] .sx-cc .sx-cc-stat.is-selected .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .text-success,
    html[data-bs-theme="dark"] .sx-cc .text-success {
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cc .text-danger,
    html[data-bs-theme="dark"] .sx-cc .text-danger {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .alert-success,
    html[data-bs-theme="dark"] .sx-cc .alert-success {
        background: rgba(20, 92, 42, 0.28) !important;
        border-color: rgba(140, 233, 154, 0.3) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-cc .alert-danger,
    html[data-bs-theme="dark"] .sx-cc .alert-danger {
        background: rgba(148, 0, 0, 0.28) !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-cc .btn-primary,
    html[data-bs-theme="dark"] .sx-cc .btn-primary,
    html[data-sx-theme="dark"] .sx-cc .btn-primary i,
    html[data-bs-theme="dark"] .sx-cc .btn-primary i,
    html[data-sx-theme="dark"] .sx-cc .btn-primary i:before,
    html[data-bs-theme="dark"] .sx-cc .btn-primary i:before {
        color: #fff !important;
    }
</style>

<div class="sx-cc">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-cc-stat is-loaded h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.customer_care.loaded') }}</span>
                        <span class="stat-value" id="statLoaded">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-users"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-cc-stat is-ready h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.customer_care.ready_phone') }}</span>
                        <span class="stat-value" id="statReady">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-check-circle"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-cc-stat is-missing h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.customer_care.missing_phone') }}</span>
                        <span class="stat-value" id="statMissing">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-exclamation"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-cc-stat is-selected h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.customer_care.selected') }}</span>
                        <span class="stat-value" id="statSelected">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-checkbox"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-cc-card mb-3">
        <div class="card-body">
            <div class="sx-cc-card-head">
                <h6 class="sx-cc-card-title">{{ __('superadmin.customer_care.broadcast_message') }}</h6>
                <span class="small text-muted">{{ __('superadmin.customer_care.broadcast_desc') }}</span>
            </div>
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label" for="schoolID">{{ __('superadmin.customer_care.target_institution') }}</label>
                    <select id="schoolID" class="form-control">
                        <option value="">{{ __('superadmin.customer_care.search_select_school') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="userType">{{ __('superadmin.customer_care.filter_role') }}</label>
                    <select id="userType" class="form-select">
                        @foreach($userTypes as $ut)
                            <option value="{{ $ut }}">{{ $ut }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="senderID">{{ __('superadmin.customer_care.sender_id') }}</label>
                    <select id="senderID" class="form-select">
                        <option value="ShuleXpert">ShuleXpert</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="form-label" for="smsMessage">{{ __('superadmin.customer_care.sms_message') }}</label>
                    <textarea id="smsMessage" class="form-control" rows="2" placeholder="{{ __('superadmin.customer_care.message_placeholder') }}"></textarea>
                    <small class="text-muted d-block mt-1">{{ __('superadmin.customer_care.placeholders_hint') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-cc-card">
        <div class="card-body">
            <div class="sx-cc-card-head">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <h6 class="sx-cc-card-title mb-0">{{ __('superadmin.customer_care.recipients') }}</h6>
                    <div class="form-check m-0">
                        <input type="checkbox" class="form-check-input" id="selectAllUsers">
                        <label class="form-check-label small fw-semibold" for="selectAllUsers">{{ __('superadmin.customer_care.select_all') }}</label>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="sx-selected-badge" id="selectedCount">{{ __('superadmin.customer_care.recipients_count') }} <strong>0</strong></span>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnSendSms" disabled>
                        <i class="fi fi-rr-paper-plane me-1"></i> {{ __('superadmin.customer_care.broadcast_sms') }}
                    </button>
                </div>
            </div>

            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="usersTable">
                        <thead>
                            <tr>
                                <th width="40"></th>
                                <th width="60">{{ __('superadmin.customer_care.th_logo') }}</th>
                                <th>{{ __('superadmin.customer_care.th_school') }}</th>
                                <th>{{ __('superadmin.customer_care.th_username') }}</th>
                                <th>{{ __('superadmin.customer_care.th_role') }}</th>
                                <th>{{ __('superadmin.customer_care.th_phone') }}</th>
                                <th class="text-center" width="150">{{ __('superadmin.customer_care.th_status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="usersTbody">
                            <tr>
                                <td colspan="7">
                                    <div class="sx-empty">
                                        <i class="fi fi-rr-info"></i>
                                        {{ __('superadmin.customer_care.select_school_to_load') }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="smsProgressArea" class="sx-progress-wrap d-none">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold" style="color:#940000 !important;">{{ __('superadmin.customer_care.progress') }}</span>
                        <span id="smsProgressText" class="small fw-bold">0 / 0</span>
                    </div>
                    <div class="progress">
                        <div id="smsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="smsDeliverySummary" class="text-center small mt-2 fw-bold"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const SX = @json(__('superadmin.customer_care'));
const SXC = @json(__('superadmin.common'));
function sxFmt(str, map) {
    return Object.keys(map || {}).reduce(function (s, k) { return s.split(':' + k).join(map[k]); }, String(str));
}
$(document).ready(function() {
    let loadedUsers = [];
    let usersDataTable = null;

    $('#schoolID').select2({
        width: '100%',
        placeholder: SX.js.search_select_school,
        allowClear: true,
        dropdownParent: $('.sx-cc')
    });

    function safeText(v) {
        return $('<div>').text(v == null ? '' : String(v)).html();
    }

    function updateSelectedCount() {
        const count = $('.user-checkbox:checked').length;
        $('#selectedCount').html(sxFmt(SX.js.recipients, { count: '<strong>' + count + '</strong>' }));
        $('#statSelected').text(count);
        $('#btnSendSms').prop('disabled', count === 0);
    }

    function updateLoadStats(users) {
        const list = users || [];
        let ready = 0;
        let missing = 0;
        list.forEach(function (u) {
            const phone = (u.phone || '').toString().trim();
            const hasPhone = phone && !['null', 'undefined', 'n/a'].includes(phone.toLowerCase());
            if (hasPhone) ready++;
            else missing++;
        });
        $('#statLoaded').text(list.length);
        $('#statReady').text(ready);
        $('#statMissing').text(missing);
        $('#statSelected').text(0);
    }

    function destroyUsersDataTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#usersTable')) {
            $('#usersTable').DataTable().destroy();
        }
        usersDataTable = null;
    }

    function setUsersTableBody(html) {
        destroyUsersDataTable();
        const $tbody = $('#usersTable tbody');
        if ($tbody.length) {
            $tbody.attr('id', 'usersTbody').html(html);
        }
    }

    function initUsersDataTable() {
        destroyUsersDataTable();
        if (!$.fn.DataTable) return;

        usersDataTable = $('#usersTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, SXC.all]],
            ordering: true,
            info: true,
            searching: true,
            autoWidth: false,
            columnDefs: [{ orderable: false, targets: [0, 1, 6] }],
            language: {
                search: '_INPUT_',
                searchPlaceholder: SX.js.filter_recipients,
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_matching,
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    function emptyRow(message, icon) {
        return `
            <tr>
                <td colspan="7">
                    <div class="sx-empty">
                        <i class="fi ${icon || 'fi-rr-info'}"></i>
                        ${safeText(message)}
                    </div>
                </td>
            </tr>
        `;
    }

    function resetProgress() {
        $('#smsProgressArea').addClass('d-none');
        $('#smsProgressText').text('0 / 0');
        $('#smsProgressBar').css('width', '0%');
        $('#smsDeliverySummary').empty();
    }

    async function loadUsers() {
        const schoolID = $('#schoolID').val();
        const userType = $('#userType').val();

        loadedUsers = [];
        resetProgress();
        $('#selectAllUsers').prop('checked', false);
        updateLoadStats([]);
        setUsersTableBody(emptyRow(SX.js.loading_users, 'fi-rr-spinner'));

        if (!schoolID) {
            setUsersTableBody(emptyRow(SX.js.select_school_to_load, 'fi-rr-info'));
            updateSelectedCount();
            return;
        }

        try {
            const res = await $.ajax({
                url: '{{ route("superadmin.customer_care.users") }}',
                type: 'GET',
                data: { schoolID: schoolID, user_type: userType }
            });

            if (!res.success) {
                updateLoadStats([]);
                setUsersTableBody(emptyRow(SX.js.load_failed, 'fi-rr-exclamation'));
                return;
            }

            loadedUsers = res.users || [];
            updateLoadStats(loadedUsers);

            $('#senderID').html('<option value="ShuleXpert">ShuleXpert</option>');
            if (res.school && res.school.senderID && res.school.senderID.trim() !== '') {
                const sID = res.school.senderID.trim();
                if (sID.toUpperCase() !== 'SHULEXPERT') {
                    $('#senderID').append(`<option value="${safeText(sID)}">${safeText(sID)}</option>`);
                }
            }

            if (loadedUsers.length === 0) {
                setUsersTableBody(emptyRow(SX.js.no_users, 'fi-rr-users'));
                updateSelectedCount();
                return;
            }

            const rowsHtml = loadedUsers.map((u, idx) => {
                const checkboxId = 'u_chk_' + idx + '_' + Math.random().toString(36).substr(2, 5);
                const phone = (u.phone || '').toString().trim();
                const hasPhone = phone && !['null', 'undefined', 'n/a'].includes(phone.toLowerCase());
                const logo = u.school_logo
                    ? `<img src="${safeText(u.school_logo)}" class="school-logo" alt="${safeText(SXC.logo)}">`
                    : `<span class="school-logo-fallback"><i class="fi fi-rr-building"></i></span>`;

                return `
                    <tr data-school-id="${safeText(u.schoolID)}" data-user-type="${safeText(u.user_type)}" data-username="${safeText(u.username)}" data-phone="${safeText(phone)}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input user-checkbox" id="${checkboxId}" ${hasPhone ? '' : 'disabled'}>
                        </td>
                        <td class="text-center">${logo}</td>
                        <td><div class="sx-school-name">${safeText(u.school_name)}</div></td>
                        <td><div class="sx-username">${safeText(u.username)}</div></td>
                        <td><span class="sx-badge sx-badge-type">${safeText(u.user_type)}</span></td>
                        <td>${hasPhone ? '<i class="fi fi-rr-phone-call me-1"></i>' + safeText(phone) : '—'}</td>
                        <td class="text-center status-col">
                            ${hasPhone
                                ? '<span class="sx-badge sx-badge-ready status-marker">' + safeText(SX.js.ready) + '</span>'
                                : '<span class="sx-badge sx-badge-missing">' + safeText(SX.js.no_phone) + '</span>'}
                        </td>
                    </tr>
                `;
            }).join('');

            setUsersTableBody(rowsHtml);
            initUsersDataTable();
            updateSelectedCount();
        } catch (e) {
            updateLoadStats([]);
            setUsersTableBody(emptyRow(SX.js.error_loading, 'fi-rr-exclamation'));
        }
    }

    $('#schoolID').on('change', loadUsers);
    $('#userType').on('change', loadUsers);

    $('#selectAllUsers').on('change', function () {
        $('.user-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });

    $(document).on('change', '.user-checkbox', updateSelectedCount);

    $('#btnSendSms').on('click', async function () {
        const selectedRows = $('.user-checkbox:checked').closest('tr');
        const msgText = ($('#smsMessage').val() || '').trim();

        if (!msgText) {
            Swal.fire({ icon: 'warning', title: SX.js.message_required_title, text: SX.js.message_required_text });
            return;
        }

        const confirmed = await Swal.fire({
            title: SX.js.confirm_title,
            text: sxFmt(SX.js.confirm_text, { count: selectedRows.length }),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: SX.js.confirm_button,
            cancelButtonText: SX.js.cancel
        });
        if (!confirmed.isConfirmed) return;

        $('#btnSendSms').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> ' + SX.js.sending);
        $('#schoolID, #userType, #smsMessage, #selectAllUsers, #senderID').prop('disabled', true);
        $('.user-checkbox').prop('disabled', true);
        $('#smsProgressArea').removeClass('d-none');

        const total = selectedRows.length;
        let delivered = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const row = $(selectedRows[i]);
            const statusCol = row.find('.status-col');
            statusCol.html('<div class="spinner-border spinner-border-sm text-primary"></div>');

            try {
                const res = await $.ajax({
                    url: '{{ route("superadmin.customer_care.send_sms") }}',
                    type: 'POST',
                    data: {
                        schoolID: row.data('school-id'),
                        user_type: row.data('user-type'),
                        username: row.data('username'),
                        phone: row.data('phone'),
                        message: msgText,
                        from: $('#senderID').val(),
                        _token: '{{ csrf_token() }}'
                    }
                });

                if (res.success) {
                    statusCol.html('<span class="sx-badge sx-badge-ready"><i class="fi fi-rr-check me-1"></i>' + safeText(SX.js.sent) + '</span>');
                    delivered++;
                } else {
                    statusCol.html('<span class="sx-badge sx-badge-missing"><i class="fi fi-rr-exclamation me-1"></i>' + safeText(SX.js.failed) + '</span>');
                    failed++;
                }
            } catch (e) {
                statusCol.html('<span class="sx-badge sx-badge-missing">' + safeText(SX.js.failed) + '</span>');
                failed++;
            }

            const current = delivered + failed;
            const percent = Math.round((current / total) * 100);
            $('#smsProgressBar').css('width', percent + '%');
            $('#smsProgressText').text(`${current} / ${total}`);
            $('#smsDeliverySummary').html(`<span class="text-success">${safeText(sxFmt(SX.js.success_count, { delivered: delivered }))}</span> | <span class="text-danger">${safeText(sxFmt(SX.js.failed_count, { failed: failed }))}</span>`);
        }

        $('#schoolID, #userType, #smsMessage, #selectAllUsers, #senderID').prop('disabled', false);
        $('#btnSendSms').html('<i class="fi fi-rr-paper-plane me-1"></i> ' + SX.js.broadcast_sms).prop('disabled', true);

        Swal.fire({
            title: SX.js.completed_title,
            text: sxFmt(SX.js.completed_text, { delivered: delivered, failed: failed }),
            icon: delivered > 0 ? 'success' : 'info'
        });
    });
});
</script>
@endpush
