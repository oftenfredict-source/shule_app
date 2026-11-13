@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.user_password'))
@section('page_breadcrumb', __('superadmin.pages.user_password'))

@section('page_actions')
    <a href="{{ route('superadmin.users') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-users me-1"></i> {{ __('superadmin.user_password.user_management') }}
    </a>
    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-building me-1"></i> {{ __('superadmin.user_password.schools') }}
    </a>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw span:not(.badge):not(.sx-badge):not(.status-marker),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-upw a:not(.btn) {
        color: #000 !important;
    }
    .sx-upw .btn-primary,
    .sx-upw a.btn-primary,
    .sx-upw .btn-primary i,
    .sx-upw .btn-primary i:before,
    .sx-upw .text-white {
        color: #fff !important;
    }
    .sx-upw .sx-upw-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-upw .sx-upw-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-upw .sx-upw-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-upw .sx-upw-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-upw .sx-upw-stat .stat-ico {
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
    .sx-upw .sx-upw-stat .stat-ico i,
    .sx-upw .sx-upw-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-upw .sx-upw-stat.is-loaded .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-upw .sx-upw-stat.is-ready .stat-ico { background: #e8f7ec; color: #145c2a !important; }
    .sx-upw .sx-upw-stat.is-missing .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-upw .sx-upw-stat.is-selected .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-upw .sx-upw-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-upw .sx-upw-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-upw .sx-upw-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-upw .sx-upw-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-upw .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-upw .form-select,
    .sx-upw .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-upw .form-select:focus,
    .sx-upw .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-upw .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-upw .btn-primary:hover,
    .sx-upw .btn-primary:focus {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-upw .btn-primary:disabled {
        opacity: 0.55;
    }
    .sx-upw .sx-selected-badge {
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
    .sx-upw .sx-selected-badge strong {
        color: #940000 !important;
    }
    .sx-upw .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-upw .sx-dt-top,
    .sx-upw .sx-dt-bottom {
        width: 100%;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }
    .sx-upw .dataTables_length select {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
        margin: 0 0.35rem;
    }
    .sx-upw .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-upw .table thead th {
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
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .sx-upw .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-upw .table tbody tr:hover { background: #fffafa; }
    .sx-upw .school-logo {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e0e0e0;
        background: #fff;
    }
    .sx-upw .school-logo-fallback {
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
    .sx-upw .sx-school-name {
        font-weight: 600;
        color: #000 !important;
        line-height: 1.25;
    }
    .sx-upw .sx-username {
        font-weight: 700;
        color: #940000 !important;
    }
    .sx-upw .sx-badge {
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
    .sx-upw .sx-badge-type {
        background: #f5f5f5;
        color: #333 !important;
        border: 1px solid #e6e6e6;
    }
    .sx-upw .sx-badge-ready {
        background: #e8f7ec;
        color: #145c2a !important;
    }
    .sx-upw .sx-badge-missing {
        background: #fde8e8;
        color: #940000 !important;
    }
    .sx-upw .sx-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #666 !important;
    }
    .sx-upw .sx-empty i {
        display: block;
        font-size: 2rem;
        color: #940000 !important;
        margin-bottom: 0.65rem;
    }
    .sx-upw .sx-empty i:before { color: inherit !important; }
    .sx-upw .sx-progress-wrap {
        border-top: 1px solid #eee;
        background: #fafafa;
        padding: 1rem 1.25rem;
    }
    .sx-upw .progress {
        height: 10px;
        border-radius: 0.35rem;
        background: #ececec;
    }
    .sx-upw .progress-bar {
        background: #940000 !important;
    }
    .sx-upw .select2-container--default .select2-selection--single {
        height: 40px;
        border-radius: 0.45rem !important;
        border: 1px solid #d9d9d9;
        display: flex;
        align-items: center;
    }
    .sx-upw .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
        color: #000;
    }
    .sx-upw .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    .sx-upw .select2-container--default.select2-container--focus .select2-selection--single,
    .sx-upw .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-upw .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 36px;
        padding: 0.25rem 0.65rem;
    }
    .sx-upw .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #940000;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-upw .dataTables_wrapper .dataTables_length select {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
        margin: 0 0.35rem;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }
    .sx-upw .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
    }
    .sx-upw .page-link { color: #940000; }
    .sx-upw .text-success { color: #145c2a !important; }
    .sx-upw .text-danger { color: #940000 !important; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-upw,
    html[data-bs-theme="dark"] .sx-upw,
    html[data-sx-theme="dark"] .sx-upw p,
    html[data-bs-theme="dark"] .sx-upw p,
    html[data-sx-theme="dark"] .sx-upw span:not(.sx-badge):not(.badge):not(.stat-ico):not(.status-marker),
    html[data-bs-theme="dark"] .sx-upw span:not(.sx-badge):not(.badge):not(.stat-ico):not(.status-marker),
    html[data-sx-theme="dark"] .sx-upw small,
    html[data-bs-theme="dark"] .sx-upw small,
    html[data-sx-theme="dark"] .sx-upw strong,
    html[data-bs-theme="dark"] .sx-upw strong,
    html[data-sx-theme="dark"] .sx-upw div,
    html[data-bs-theme="dark"] .sx-upw div,
    html[data-sx-theme="dark"] .sx-upw label,
    html[data-bs-theme="dark"] .sx-upw label,
    html[data-sx-theme="dark"] .sx-upw h1,
    html[data-bs-theme="dark"] .sx-upw h1,
    html[data-sx-theme="dark"] .sx-upw h2,
    html[data-bs-theme="dark"] .sx-upw h2,
    html[data-sx-theme="dark"] .sx-upw h3,
    html[data-bs-theme="dark"] .sx-upw h3,
    html[data-sx-theme="dark"] .sx-upw h4,
    html[data-bs-theme="dark"] .sx-upw h4,
    html[data-sx-theme="dark"] .sx-upw h5,
    html[data-bs-theme="dark"] .sx-upw h5,
    html[data-sx-theme="dark"] .sx-upw h6,
    html[data-bs-theme="dark"] .sx-upw h6,
    html[data-sx-theme="dark"] .sx-upw .sx-upw-card-title,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-card-title,
    html[data-sx-theme="dark"] .sx-upw .stat-label,
    html[data-bs-theme="dark"] .sx-upw .stat-label,
    html[data-sx-theme="dark"] .sx-upw .stat-value,
    html[data-bs-theme="dark"] .sx-upw .stat-value,
    html[data-sx-theme="dark"] .sx-upw .table,
    html[data-bs-theme="dark"] .sx-upw .table,
    html[data-sx-theme="dark"] .sx-upw .table tbody td,
    html[data-bs-theme="dark"] .sx-upw .table tbody td,
    html[data-sx-theme="dark"] .sx-upw .sx-school-name,
    html[data-bs-theme="dark"] .sx-upw .sx-school-name,
    html[data-sx-theme="dark"] .sx-upw a:not(.btn),
    html[data-bs-theme="dark"] .sx-upw a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-upw .text-muted,
    html[data-bs-theme="dark"] .sx-upw .text-muted,
    html[data-sx-theme="dark"] .sx-upw .form-label,
    html[data-bs-theme="dark"] .sx-upw .form-label,
    html[data-sx-theme="dark"] .sx-upw .table thead th,
    html[data-bs-theme="dark"] .sx-upw .table thead th,
    html[data-sx-theme="dark"] .sx-upw .sx-empty,
    html[data-bs-theme="dark"] .sx-upw .sx-empty,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-upw-stat,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-stat,
    html[data-sx-theme="dark"] .sx-upw .sx-upw-card,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-card,
    html[data-sx-theme="dark"] .sx-upw .card,
    html[data-bs-theme="dark"] .sx-upw .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-upw .sx-upw-card-head,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-upw .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .table thead th,
    html[data-bs-theme="dark"] .sx-upw .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .table tbody td,
    html[data-bs-theme="dark"] .sx-upw .table tbody td {
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-upw .table tbody tr:hover {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .form-control,
    html[data-bs-theme="dark"] .sx-upw .form-control,
    html[data-sx-theme="dark"] .sx-upw .form-select,
    html[data-bs-theme="dark"] .sx-upw .form-select,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-upw .select2-container--default .select2-selection--single,
    html[data-bs-theme="dark"] .sx-upw .select2-container--default .select2-selection--single {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .select2-container--default .select2-selection--single .select2-selection__rendered,
    html[data-bs-theme="dark"] .sx-upw .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-username,
    html[data-bs-theme="dark"] .sx-upw .sx-username,
    html[data-sx-theme="dark"] .sx-upw .sx-empty i,
    html[data-bs-theme="dark"] .sx-upw .sx-empty i,
    html[data-sx-theme="dark"] .sx-upw .school-logo-fallback,
    html[data-bs-theme="dark"] .sx-upw .school-logo-fallback {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .school-logo-fallback,
    html[data-bs-theme="dark"] .sx-upw .school-logo-fallback {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .school-logo,
    html[data-bs-theme="dark"] .sx-upw .school-logo {
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-selected-badge,
    html[data-bs-theme="dark"] .sx-upw .sx-selected-badge {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-selected-badge strong,
    html[data-bs-theme="dark"] .sx-upw .sx-selected-badge strong {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-progress-wrap,
    html[data-bs-theme="dark"] .sx-upw .sx-progress-wrap {
        background: #1c1417 !important;
        border-top-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .progress,
    html[data-bs-theme="dark"] .sx-upw .progress {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-upw .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-badge-type,
    html[data-bs-theme="dark"] .sx-upw .sx-badge-type {
        background: #2c2125 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-badge-ready,
    html[data-bs-theme="dark"] .sx-upw .sx-badge-ready {
        background: rgba(20, 92, 42, 0.35) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-badge-missing,
    html[data-bs-theme="dark"] .sx-upw .sx-badge-missing {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-upw-stat.is-loaded .stat-ico,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-stat.is-loaded .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-upw-stat.is-ready .stat-ico,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-stat.is-ready .stat-ico {
        background: rgba(20, 92, 42, 0.28) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-upw .sx-upw-stat.is-missing .stat-ico,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-stat.is-missing .stat-ico,
    html[data-sx-theme="dark"] .sx-upw .sx-upw-stat.is-selected .stat-ico,
    html[data-bs-theme="dark"] .sx-upw .sx-upw-stat.is-selected .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .text-success,
    html[data-bs-theme="dark"] .sx-upw .text-success {
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-upw .text-danger,
    html[data-bs-theme="dark"] .sx-upw .text-danger {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .alert-success,
    html[data-bs-theme="dark"] .sx-upw .alert-success {
        background: rgba(20, 92, 42, 0.28) !important;
        border-color: rgba(140, 233, 154, 0.3) !important;
        color: #8ce99a !important;
    }
    html[data-sx-theme="dark"] .sx-upw .alert-danger,
    html[data-bs-theme="dark"] .sx-upw .alert-danger {
        background: rgba(148, 0, 0, 0.28) !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-upw .btn-primary,
    html[data-bs-theme="dark"] .sx-upw .btn-primary,
    html[data-sx-theme="dark"] .sx-upw .btn-primary i,
    html[data-bs-theme="dark"] .sx-upw .btn-primary i,
    html[data-sx-theme="dark"] .sx-upw .btn-primary i:before,
    html[data-bs-theme="dark"] .sx-upw .btn-primary i:before {
        color: #fff !important;
    }
</style>

<div class="sx-upw">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('superadmin.common.close') }}"></button>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-upw-stat is-loaded h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.user_password.loaded') }}</span>
                        <span class="stat-value" id="statLoaded">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-users"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-upw-stat is-ready h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.user_password.ready_phone') }}</span>
                        <span class="stat-value" id="statReady">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-check-circle"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-upw-stat is-missing h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.user_password.missing_phone') }}</span>
                        <span class="stat-value" id="statMissing">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-exclamation"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-upw-stat is-selected h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.user_password.selected') }}</span>
                        <span class="stat-value" id="statSelected">0</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-checkbox"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-upw-card mb-3">
        <div class="card-body">
            <div class="sx-upw-card-head">
                <h6 class="sx-upw-card-title">{{ __('superadmin.user_password.send_credentials') }}</h6>
                <span class="small text-muted">{{ __('superadmin.user_password.send_credentials_desc') }}</span>
            </div>
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label" for="schoolID">{{ __('superadmin.user_password.target_institution') }}</label>
                    <select id="schoolID" class="form-control">
                        <option value="">{{ __('superadmin.user_password.search_select_school') }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="userType">{{ __('superadmin.user_password.user_role') }}</label>
                    <select id="userType" class="form-select">
                        @foreach($userTypes as $ut)
                            <option value="{{ $ut }}">{{ $ut }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="senderID">{{ __('superadmin.user_password.sender_id') }}</label>
                    <select id="senderID" class="form-select">
                        <option value="ShuleXpert">ShuleXpert</option>
                    </select>
                </div>
                <div class="d-none">
                    <textarea id="smsTemplate" class="form-control" rows="2" aria-hidden="true" tabindex="-1">HABARI, Account yako ipo tayari. LOGIN KWA username:{username} password:{password} shule:{school}. Login: https://shulexpert.com/login</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-upw-card">
        <div class="card-body">
            <div class="sx-upw-card-head">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <h6 class="sx-upw-card-title mb-0">{{ __('superadmin.user_password.users') }}</h6>
                    <div class="form-check m-0">
                        <input type="checkbox" class="form-check-input" id="selectAllUsers">
                        <label class="form-check-label small fw-semibold" for="selectAllUsers">{{ __('superadmin.user_password.select_all') }}</label>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="sx-selected-badge" id="selectedCount">{{ __('superadmin.user_password.selected_count') }} <strong>0</strong></span>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="btnSendSms" disabled>
                        <i class="fi fi-rr-paper-plane me-1"></i> {{ __('superadmin.user_password.send_sms') }}
                    </button>
                </div>
            </div>

            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="usersTable">
                        <thead>
                            <tr>
                                <th width="40"></th>
                                <th width="60">{{ __('superadmin.user_password.th_logo') }}</th>
                                <th>{{ __('superadmin.user_password.th_school') }}</th>
                                <th>{{ __('superadmin.user_password.th_username') }}</th>
                                <th>{{ __('superadmin.user_password.th_role') }}</th>
                                <th>{{ __('superadmin.user_password.th_phone') }}</th>
                                <th class="text-center" width="150">{{ __('superadmin.user_password.th_status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="usersTbody">
                            <tr>
                                <td colspan="7">
                                    <div class="sx-empty">
                                        <i class="fi fi-rr-info"></i>
                                        {{ __('superadmin.user_password.select_school_to_load') }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="smsProgressArea" class="sx-progress-wrap d-none">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold" style="color:#940000 !important;">{{ __('superadmin.user_password.batch_progress') }}</span>
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
const SX = @json(__('superadmin.user_password'));
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
        dropdownParent: $('.sx-upw')
    });

    function safeText(v) {
        return $('<div>').text(v == null ? '' : String(v)).html();
    }

    function updateSelectedCount() {
        const count = $('.user-checkbox:checked').length;
        $('#selectedCount').html(sxFmt(SX.js.selected, { count: '<strong>' + count + '</strong>' }));
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

        if (!$.fn.DataTable) {
            console.error(SX.js.plugin_missing);
            return;
        }

        usersDataTable = $('#usersTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, SXC.all]],
            ordering: true,
            info: true,
            searching: true,
            autoWidth: false,
            deferRender: true,
            columnDefs: [
                { orderable: false, targets: [0, 1, 6] }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: SX.js.filter_results,
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                infoEmpty: SX.js.no_users,
                infoFiltered: SXC.datatables_filtered,
                zeroRecords: SX.js.no_matching,
                paginate: {
                    next: SXC.next,
                    previous: SXC.previous
                }
            },
            dom: '<"sx-dt-top d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>t<"sx-dt-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    function resetProgress() {
        $('#smsProgressArea').addClass('d-none');
        $('#smsProgressText').text('0 / 0');
        $('#smsProgressBar').css('width', '0%');
        $('#smsDeliverySummary').empty();
    }

    function emptyRow(message, icon) {
        return `
            <tr>
                <td colspan="7">
                    <div class="sx-empty">
                        <i class="fi ${icon || 'fi-rr-info'}"></i>
                        ${message}
                    </div>
                </td>
            </tr>
        `;
    }

    async function loadUsers() {
        const schoolID = $('#schoolID').val();
        const userType = $('#userType').val();

        loadedUsers = [];
        resetProgress();
        $('#selectAllUsers').prop('checked', false);
        updateLoadStats([]);
        setUsersTableBody(emptyRow(SX.js.fetching, 'fi-rr-spinner'));

        if (!schoolID) {
            setUsersTableBody(emptyRow(SX.js.select_school_to_load, 'fi-rr-info'));
            updateSelectedCount();
            return;
        }

        try {
            const res = await $.ajax({
                url: '{{ route("superadmin.user_password.users") }}',
                type: 'GET',
                data: { schoolID: schoolID, user_type: userType }
            });

            if (!res.success) {
                updateLoadStats([]);
                setUsersTableBody(emptyRow(SX.js.load_failed, 'fi-rr-exclamation'));
                return;
            }

            loadedUsers = res.users || [];

            $('#senderID').html('<option value="ShuleXpert">ShuleXpert</option>');
            if (res.school && res.school.senderID && res.school.senderID.trim() !== '') {
                const sID = res.school.senderID.trim();
                if (sID.toUpperCase() !== 'SHULEXPERT') {
                    $('#senderID').append(`<option value="${safeText(sID)}">${safeText(sID)}</option>`);
                }
            }

            updateLoadStats(loadedUsers);

            if (loadedUsers.length === 0) {
                setUsersTableBody(emptyRow(SX.js.no_users_role, 'fi-rr-users'));
                updateSelectedCount();
                return;
            }

            const rowsHtml = loadedUsers.map((u, idx) => {
                const checkboxId = 'u_chk_' + idx + '_' + Math.random().toString(36).substr(2, 5);
                const phone = (u.phone || '').toString().trim();
                const hasPhone = phone && !['null', 'undefined', 'n/a'].includes(phone.toLowerCase());
                const disabledAttr = hasPhone ? '' : 'disabled="disabled"';
                const statusHtml = hasPhone
                    ? '<span class="sx-badge sx-badge-ready status-marker">' + safeText(SX.js.ready) + '</span>'
                    : '<span class="sx-badge sx-badge-missing">' + safeText(SX.js.missing_phone) + '</span>';

                const logo = u.school_logo
                    ? `<img src="${safeText(u.school_logo)}" class="school-logo" alt="${safeText(SXC.logo)}">`
                    : `<span class="school-logo-fallback"><i class="fi fi-rr-building"></i></span>`;

                return `
                    <tr data-school-id="${safeText(u.schoolID)}" data-user-type="${safeText(u.user_type)}" data-username="${safeText(u.username)}" data-phone="${safeText(phone)}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input user-checkbox" id="${checkboxId}" ${disabledAttr}>
                        </td>
                        <td class="text-center">${logo}</td>
                        <td><div class="sx-school-name">${safeText(u.school_name)}</div></td>
                        <td><div class="sx-username">${safeText(u.username)}</div></td>
                        <td><span class="sx-badge sx-badge-type">${safeText(u.user_type)}</span></td>
                        <td>${hasPhone ? '<i class="fi fi-rr-phone-call me-1"></i>' + safeText(phone) : '—'}</td>
                        <td class="text-center status-col">${statusHtml}</td>
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

    $('#selectAllUsers').on('change', function() {
        $('.user-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });

    $(document).on('change', '.user-checkbox', function() { updateSelectedCount(); });

    $('#btnSendSms').on('click', async function() {
        const selectedRows = $('.user-checkbox:checked').closest('tr');
        const template = $('#smsTemplate').val();

        if (!template || template.trim().length === 0) {
            Swal.fire({ icon: 'warning', title: SX.js.empty_template_title, text: SX.js.empty_template_text });
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

        $('#btnSendSms').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> ' + SX.js.transmitting);
        $('#schoolID, #userType, #smsTemplate, #selectAllUsers, #senderID').prop('disabled', true);
        $('.user-checkbox').prop('disabled', true);
        $('#smsProgressArea').removeClass('d-none');

        const total = selectedRows.length;
        let delivered = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const row = $(selectedRows[i]);
            const statusCol = row.find('.status-col');
            statusCol.html('<div class="spinner-border spinner-border-sm text-primary"></div>');

            const payload = {
                schoolID: row.data('school-id'),
                user_type: row.data('user-type'),
                username: row.data('username'),
                phone: row.data('phone'),
                message: template,
                from: $('#senderID').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            try {
                const response = await $.ajax({
                    url: '{{ route("superadmin.user_password.send_sms") }}',
                    type: 'POST',
                    data: payload
                });

                if (response && response.success) {
                    statusCol.html('<span class="sx-badge sx-badge-ready"><i class="fi fi-rr-check me-1"></i>' + safeText(SX.js.sent) + '</span>');
                    delivered++;
                } else {
                    const msg = (response && (response.error || response.message)) || SXC.error;
                    statusCol.html('<span class="sx-badge sx-badge-missing"><i class="fi fi-rr-exclamation me-1"></i>' + safeText(msg) + '</span>');
                    failed++;
                }
            } catch (err) {
                statusCol.html('<span class="sx-badge sx-badge-missing">' + safeText(SX.js.connection_failed) + '</span>');
                failed++;
            }

            const currentCount = delivered + failed;
            const percent = Math.round((currentCount / total) * 100);
            $('#smsProgressBar').css('width', percent + '%');
            $('#smsProgressText').text(`${currentCount} / ${total}`);
            $('#smsDeliverySummary').html(`<span class="text-success">${safeText(sxFmt(SX.js.transmitted_count, { delivered: delivered }))}</span> | <span class="text-danger">${safeText(sxFmt(SX.js.failed_count, { failed: failed }))}</span>`);
        }

        $('#schoolID, #userType, #smsTemplate, #selectAllUsers, #senderID').prop('disabled', false);
        $('#btnSendSms').html('<i class="fi fi-rr-paper-plane me-1"></i> ' + SX.js.send_sms).prop('disabled', true);

        Swal.fire({
            title: SX.js.completed_title,
            text: sxFmt(SX.js.completed_text, { delivered: delivered, failed: failed }),
            icon: delivered > 0 ? 'success' : 'info'
        });
    });
});
</script>
@endpush
