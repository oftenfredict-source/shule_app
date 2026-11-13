@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.customer_inquiries'))
@section('page_breadcrumb', __('superadmin.pages.customer_inquiries'))

@section('page_actions')
    <a href="{{ route('superadmin.customer_care') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-headset me-1"></i> {{ __('superadmin.customer_inquiries.customer_care') }}
    </a>
@endsection

@section('content')
<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci span:not(.sx-badge-unread):not(.sx-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-ci a:not(.btn) {
        color: #000 !important;
    }
    .sx-ci .btn-primary,
    .sx-ci a.btn-primary,
    .sx-ci .btn-primary i,
    .sx-ci .btn-primary i:before,
    .sx-ci .text-white,
    .sx-ci .sx-badge-unread {
        color: #fff !important;
    }
    .sx-ci .sx-ci-stat {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }
    .sx-ci .sx-ci-stat .card-body { padding: 1rem 1.15rem 1.1rem; }
    .sx-ci .sx-ci-stat .stat-label {
        display: block;
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000 !important;
    }
    .sx-ci .sx-ci-stat .stat-value {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #000 !important;
    }
    .sx-ci .sx-ci-stat .stat-ico {
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
    .sx-ci .sx-ci-stat .stat-ico i,
    .sx-ci .sx-ci-stat .stat-ico i:before {
        color: inherit !important;
        display: block;
        line-height: 1;
    }
    .sx-ci .sx-ci-stat.is-unread .stat-ico { background: #fde8e8; color: #940000 !important; }
    .sx-ci .sx-ci-stat.is-total .stat-ico { background: #fff0e8; color: #a34a00 !important; }
    .sx-ci .sx-ci-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-ci .sx-ci-card .card-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sx-ci .sx-ci-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-ci .sx-ci-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #000;
    }
    .sx-ci .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #555 !important;
        margin-bottom: 0.35rem;
    }
    .sx-ci .form-select,
    .sx-ci .form-control {
        border-radius: 0.45rem;
        border-color: #d9d9d9;
        min-height: 40px;
        color: #000;
    }
    .sx-ci .form-select:focus,
    .sx-ci .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.12);
    }
    .sx-ci .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-ci .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-ci .btn-outline-secondary,
    .sx-ci .btn-outline-dark,
    .sx-ci .btn-outline-primary {
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-ci .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        background: #fff;
    }
    .sx-ci .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-ci .sx-view-toggle {
        display: inline-flex;
        border: 1px solid #e0e0e0;
        border-radius: 0.45rem;
        overflow: hidden;
    }
    .sx-ci .sx-view-btn {
        border: 0;
        background: #fff;
        color: #444 !important;
        padding: 0.45rem 0.75rem;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
    }
    .sx-ci .sx-view-btn + .sx-view-btn { border-left: 1px solid #e0e0e0; }
    .sx-ci .sx-view-btn.is-active {
        background: #940000;
        color: #fff !important;
    }
    .sx-ci .sx-view-btn.is-active i:before { color: #fff !important; }
    .sx-ci .sx-table-wrap {
        border: 1px solid #ececec;
        border-radius: 0.45rem;
        overflow: visible;
        padding: 0.75rem;
    }
    .sx-ci .table { margin: 0; color: #000; vertical-align: middle; }
    .sx-ci .table thead th {
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
    .sx-ci .table tbody td {
        border-color: #f0f0f0;
        padding: 0.85rem 0.9rem;
        font-size: 0.9rem;
        color: #000;
    }
    .sx-ci .table tbody tr:hover { background: #fffafa; }
    .sx-ci #inquiriesTable tbody tr.unread-row { background: #fffafa; font-weight: 600; }
    .sx-ci #inquiriesTable tbody tr.unread-row td:first-child { border-left: 4px solid #940000; }
    .sx-ci .sx-badge-unread {
        display: inline-flex;
        align-items: center;
        background: #940000;
        color: #fff !important;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.2rem 0.45rem;
        border-radius: 0.25rem;
        margin-left: 0.35rem;
    }
    .sx-ci .ci-grid-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: box-shadow 0.15s ease, transform 0.15s ease;
        padding: 1rem 1.1rem;
    }
    .sx-ci .ci-grid-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
    }
    .sx-ci .ci-grid-card.unread {
        background: #fffafa;
        border-left: 4px solid #940000;
    }
    .sx-ci .ci-subject {
        font-weight: 700;
        color: #000 !important;
        line-height: 1.3;
    }
    .sx-ci .ci-meta {
        font-size: 0.82rem;
        color: #666 !important;
    }
    #inquiryModal .modal-content {
        border: 0;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(20, 0, 0, 0.22);
    }
    #inquiryModal .ci-modal-hero {
        background: linear-gradient(135deg, #940000 0%, #6b0000 55%, #4a0000 100%);
        color: #fff;
        padding: 1.35rem 1.5rem 1.25rem;
        position: relative;
    }
    #inquiryModal .ci-modal-hero::after {
        content: '';
        position: absolute;
        right: -40px;
        top: -40px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }
    #inquiryModal .ci-modal-kicker {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 0.35rem;
    }
    #inquiryModal .ci-modal-subject {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.35;
        color: #fff;
        max-width: 90%;
    }
    #inquiryModal .ci-modal-close {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        z-index: 2;
        filter: invert(1) grayscale(1) brightness(2);
        opacity: 0.85;
    }
    #inquiryModal .ci-modal-close:hover { opacity: 1; }
    #inquiryModal .modal-body {
        padding: 1.35rem 1.5rem 0.5rem;
        background: #faf8f7;
    }
    #inquiryModal .ci-profile {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 0.65rem;
        padding: 0.95rem 1.05rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    #inquiryModal .ci-avatar {
        width: 3.1rem;
        height: 3.1rem;
        border-radius: 50%;
        background: #fde8e8;
        color: #940000;
        font-weight: 800;
        font-size: 1.15rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #f5c2c2;
    }
    #inquiryModal .ci-profile-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111;
        line-height: 1.2;
        margin-bottom: 0.15rem;
    }
    #inquiryModal .ci-profile-meta {
        font-size: 0.82rem;
        color: #666;
    }
    #inquiryModal .ci-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        margin-bottom: 1.1rem;
    }
    @media (max-width: 575.98px) {
        #inquiryModal .ci-meta-grid { grid-template-columns: 1fr; }
    }
    #inquiryModal .ci-meta-item {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 0.55rem;
        padding: 0.7rem 0.85rem;
    }
    #inquiryModal .ci-meta-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 0.25rem;
    }
    #inquiryModal .ci-meta-value {
        display: block;
        font-size: 0.92rem;
        font-weight: 600;
        color: #111;
        word-break: break-word;
    }
    #inquiryModal .ci-meta-value a {
        color: #940000;
        text-decoration: none;
        font-weight: 600;
    }
    #inquiryModal .ci-meta-value a:hover { text-decoration: underline; }
    #inquiryModal .ci-message-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 0.65rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 0.75rem;
    }
    #inquiryModal .ci-message-head {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.7rem 1.05rem;
        background: #fff5f5;
        border-bottom: 1px solid #f0dada;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #940000;
    }
    #inquiryModal .sx-message-box {
        padding: 1.2rem 1.25rem 1.35rem;
        white-space: pre-wrap;
        word-break: break-word;
        color: #1a1a1a;
        font-size: 1.02rem;
        line-height: 1.75;
        max-height: 340px;
        overflow-y: auto;
        background: #fff;
    }
    #inquiryModal .modal-footer {
        background: #fff;
        border-top: 1px solid #eee;
        padding: 0.9rem 1.5rem 1.1rem;
        gap: 0.5rem;
    }
    #inquiryModal .modal-footer .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        font-weight: 600;
        border-radius: 0.45rem;
        padding: 0.45rem 1rem;
    }
    #inquiryModal .modal-footer .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    #inquiryModal .modal-footer .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    #inquiryModal .modal-footer .btn-outline-secondary {
        border-radius: 0.45rem;
        font-weight: 600;
    }
    .sx-ci .ci-msg-cell,
    .sx-ci .ci-subject-cell {
        max-width: 220px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        line-height: 1.35;
        word-break: break-word;
    }
    .sx-ci .ci-subject-cell {
        max-width: 180px;
        font-weight: 600;
    }
    .sx-ci .ci-grid-card .ci-msg-preview {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
    }
    .sx-ci .dataTables_wrapper .dataTables_length select,
    .sx-ci .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.45rem;
        border: 1px solid #d9d9d9;
        min-height: 34px;
    }
    .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.35rem !important;
        border: 1px solid #e0e0e0 !important;
        margin: 0 2px;
        padding: 0.25rem 0.6rem !important;
        color: #940000 !important;
        background: #fff !important;
    }
    .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #999 !important;
        border-color: #ececec !important;
        background: #fafafa !important;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-ci,
    html[data-bs-theme="dark"] .sx-ci,
    html[data-sx-theme="dark"] .sx-ci p,
    html[data-bs-theme="dark"] .sx-ci p,
    html[data-sx-theme="dark"] .sx-ci span:not(.sx-badge-unread):not(.sx-badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-ci span:not(.sx-badge-unread):not(.sx-badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-ci small,
    html[data-bs-theme="dark"] .sx-ci small,
    html[data-sx-theme="dark"] .sx-ci strong,
    html[data-bs-theme="dark"] .sx-ci strong,
    html[data-sx-theme="dark"] .sx-ci h1,
    html[data-bs-theme="dark"] .sx-ci h1,
    html[data-sx-theme="dark"] .sx-ci h2,
    html[data-bs-theme="dark"] .sx-ci h2,
    html[data-sx-theme="dark"] .sx-ci h3,
    html[data-bs-theme="dark"] .sx-ci h3,
    html[data-sx-theme="dark"] .sx-ci h4,
    html[data-bs-theme="dark"] .sx-ci h4,
    html[data-sx-theme="dark"] .sx-ci h5,
    html[data-bs-theme="dark"] .sx-ci h5,
    html[data-sx-theme="dark"] .sx-ci h6,
    html[data-bs-theme="dark"] .sx-ci h6,
    html[data-sx-theme="dark"] .sx-ci label,
    html[data-bs-theme="dark"] .sx-ci label,
    html[data-sx-theme="dark"] .sx-ci a:not(.btn),
    html[data-bs-theme="dark"] .sx-ci a:not(.btn),
    html[data-sx-theme="dark"] .sx-ci .sx-ci-card-title,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-card-title,
    html[data-sx-theme="dark"] .sx-ci .sx-ci-stat .stat-label,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-stat .stat-label,
    html[data-sx-theme="dark"] .sx-ci .sx-ci-stat .stat-value,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-stat .stat-value,
    html[data-sx-theme="dark"] .sx-ci .table,
    html[data-bs-theme="dark"] .sx-ci .table,
    html[data-sx-theme="dark"] .sx-ci .table tbody td,
    html[data-bs-theme="dark"] .sx-ci .table tbody td,
    html[data-sx-theme="dark"] .sx-ci .ci-subject,
    html[data-bs-theme="dark"] .sx-ci .ci-subject {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-ci .text-muted,
    html[data-bs-theme="dark"] .sx-ci .text-muted,
    html[data-sx-theme="dark"] .sx-ci .text-body,
    html[data-bs-theme="dark"] .sx-ci .text-body,
    html[data-sx-theme="dark"] .sx-ci span.text-muted:not(.sx-badge-unread):not(.sx-badge):not(.stat-ico),
    html[data-bs-theme="dark"] .sx-ci span.text-muted:not(.sx-badge-unread):not(.sx-badge):not(.stat-ico),
    html[data-sx-theme="dark"] .sx-ci .form-label,
    html[data-bs-theme="dark"] .sx-ci .form-label,
    html[data-sx-theme="dark"] .sx-ci label.form-label,
    html[data-bs-theme="dark"] .sx-ci label.form-label,
    html[data-sx-theme="dark"] .sx-ci .table thead th,
    html[data-bs-theme="dark"] .sx-ci .table thead th,
    html[data-sx-theme="dark"] .sx-ci .ci-meta,
    html[data-bs-theme="dark"] .sx-ci .ci-meta,
    html[data-sx-theme="dark"] .sx-ci .ci-meta strong,
    html[data-bs-theme="dark"] .sx-ci .ci-meta strong,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_info,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_length,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_filter,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_filter {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ci .sx-ci-stat,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-stat,
    html[data-sx-theme="dark"] .sx-ci .sx-ci-card,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-card,
    html[data-sx-theme="dark"] .sx-ci .ci-grid-card,
    html[data-bs-theme="dark"] .sx-ci .ci-grid-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-ci .ci-grid-card.unread,
    html[data-bs-theme="dark"] .sx-ci .ci-grid-card.unread {
        background: #2c1c21 !important;
        border-left-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-ci .ci-grid-card:hover,
    html[data-bs-theme="dark"] .sx-ci .ci-grid-card:hover {
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
    }
    html[data-sx-theme="dark"] .sx-ci .sx-ci-card-head,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    /* Stat icons */
    html[data-sx-theme="dark"] .sx-ci .sx-ci-stat.is-unread .stat-ico,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-stat.is-unread .stat-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ci .sx-ci-stat.is-total .stat-ico,
    html[data-bs-theme="dark"] .sx-ci .sx-ci-stat.is-total .stat-ico {
        background: rgba(163, 74, 0, 0.28) !important;
        color: #ffd8a8 !important;
    }
    /* Tables */
    html[data-sx-theme="dark"] .sx-ci .sx-table-wrap,
    html[data-bs-theme="dark"] .sx-ci .sx-table-wrap {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-ci .table thead th,
    html[data-bs-theme="dark"] .sx-ci .table thead th {
        background: #1c1417 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-ci .table tbody td,
    html[data-bs-theme="dark"] .sx-ci .table tbody td {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-ci .table tbody td .text-muted,
    html[data-bs-theme="dark"] .sx-ci .table tbody td .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ci .table tbody tr:hover,
    html[data-bs-theme="dark"] .sx-ci .table tbody tr:hover,
    html[data-sx-theme="dark"] .sx-ci #inquiriesTable tbody tr.unread-row,
    html[data-bs-theme="dark"] .sx-ci #inquiriesTable tbody tr.unread-row {
        background: rgba(255, 180, 180, 0.06) !important;
    }
    /* Forms */
    html[data-sx-theme="dark"] .sx-ci .form-control,
    html[data-bs-theme="dark"] .sx-ci .form-control,
    html[data-sx-theme="dark"] .sx-ci .form-select,
    html[data-bs-theme="dark"] .sx-ci .form-select,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_filter input,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_filter input,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_length select,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_length select {
        background-color: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.2) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-ci .form-select option,
    html[data-bs-theme="dark"] .sx-ci .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    /* View toggle */
    html[data-sx-theme="dark"] .sx-ci .sx-view-toggle,
    html[data-bs-theme="dark"] .sx-ci .sx-view-toggle {
        border-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-ci .sx-view-btn,
    html[data-bs-theme="dark"] .sx-ci .sx-view-btn {
        background: #1c1417 !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ci .sx-view-btn + .sx-view-btn,
    html[data-bs-theme="dark"] .sx-ci .sx-view-btn + .sx-view-btn {
        border-left-color: rgba(255, 180, 180, 0.2) !important;
    }
    html[data-sx-theme="dark"] .sx-ci .sx-view-btn.is-active,
    html[data-bs-theme="dark"] .sx-ci .sx-view-btn.is-active {
        background: #940000 !important;
        color: #fff !important;
    }
    /* Buttons */
    html[data-sx-theme="dark"] .sx-ci .btn-primary,
    html[data-bs-theme="dark"] .sx-ci .btn-primary,
    html[data-sx-theme="dark"] .sx-ci .btn-primary i,
    html[data-bs-theme="dark"] .sx-ci .btn-primary i,
    html[data-sx-theme="dark"] .sx-ci .sx-badge-unread,
    html[data-bs-theme="dark"] .sx-ci .sx-badge-unread {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ci .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-ci .btn-outline-primary {
        background: transparent !important;
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
    }
    html[data-sx-theme="dark"] .sx-ci .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-ci .btn-outline-primary:hover {
        background: rgba(148, 0, 0, 0.35) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ci .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-ci .btn-outline-secondary,
    html[data-sx-theme="dark"] .sx-ci .btn-outline-dark,
    html[data-bs-theme="dark"] .sx-ci .btn-outline-dark {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
    }
    html[data-sx-theme="dark"] .sx-ci .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] .sx-ci .btn-outline-secondary:hover,
    html[data-sx-theme="dark"] .sx-ci .btn-outline-dark:hover,
    html[data-bs-theme="dark"] .sx-ci .btn-outline-dark:hover {
        background: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
    }
    /* Empty state (grid) */
    html[data-sx-theme="dark"] .sx-ci .alert-light,
    html[data-bs-theme="dark"] .sx-ci .alert-light {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #d7c4c8 !important;
    }
    /* DataTables paginate */
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(148, 0, 0, 0.3) !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html[data-sx-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    html[data-bs-theme="dark"] .sx-ci .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #8a7579 !important;
        background: #1a1215 !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }

    /* Inquiry modal (rendered outside .sx-ci) */
    html[data-sx-theme="dark"] #inquiryModal .modal-content,
    html[data-bs-theme="dark"] #inquiryModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-modal-hero,
    html[data-bs-theme="dark"] #inquiryModal .ci-modal-hero {
        background: linear-gradient(135deg, #7a0000 0%, #520000 55%, #330000 100%) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-modal-kicker,
    html[data-bs-theme="dark"] #inquiryModal .ci-modal-kicker,
    html[data-sx-theme="dark"] #inquiryModal .ci-modal-subject,
    html[data-bs-theme="dark"] #inquiryModal .ci-modal-subject {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-body,
    html[data-bs-theme="dark"] #inquiryModal .modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-body .text-muted,
    html[data-bs-theme="dark"] #inquiryModal .modal-body .text-muted,
    html[data-sx-theme="dark"] #inquiryModal .ci-profile-meta,
    html[data-bs-theme="dark"] #inquiryModal .ci-profile-meta,
    html[data-sx-theme="dark"] #inquiryModal .ci-meta-label,
    html[data-bs-theme="dark"] #inquiryModal .ci-meta-label {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-profile,
    html[data-bs-theme="dark"] #inquiryModal .ci-profile,
    html[data-sx-theme="dark"] #inquiryModal .ci-meta-item,
    html[data-bs-theme="dark"] #inquiryModal .ci-meta-item,
    html[data-sx-theme="dark"] #inquiryModal .ci-message-card,
    html[data-bs-theme="dark"] #inquiryModal .ci-message-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        box-shadow: none;
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-avatar,
    html[data-bs-theme="dark"] #inquiryModal .ci-avatar {
        background: rgba(148, 0, 0, 0.28) !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-profile-name,
    html[data-bs-theme="dark"] #inquiryModal .ci-profile-name,
    html[data-sx-theme="dark"] #inquiryModal .ci-meta-value,
    html[data-bs-theme="dark"] #inquiryModal .ci-meta-value {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-meta-value a,
    html[data-bs-theme="dark"] #inquiryModal .ci-meta-value a {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .ci-message-head,
    html[data-bs-theme="dark"] #inquiryModal .ci-message-head {
        background: rgba(148, 0, 0, 0.22) !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .sx-message-box,
    html[data-bs-theme="dark"] #inquiryModal .sx-message-box {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-footer,
    html[data-bs-theme="dark"] #inquiryModal .modal-footer {
        background: #24181c !important;
        border-top-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-footer .btn-primary,
    html[data-bs-theme="dark"] #inquiryModal .modal-footer .btn-primary {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-footer .btn-outline-primary,
    html[data-bs-theme="dark"] #inquiryModal .modal-footer .btn-outline-primary {
        background: transparent !important;
        color: #ffb4b4 !important;
        border-color: #c43a3a !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-footer .btn-outline-primary:hover,
    html[data-bs-theme="dark"] #inquiryModal .modal-footer .btn-outline-primary:hover {
        background: rgba(148, 0, 0, 0.35) !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-footer .btn-outline-secondary,
    html[data-bs-theme="dark"] #inquiryModal .modal-footer .btn-outline-secondary {
        color: #d7c4c8 !important;
        border-color: rgba(255, 180, 180, 0.3) !important;
    }
    html[data-sx-theme="dark"] #inquiryModal .modal-footer .btn-outline-secondary:hover,
    html[data-bs-theme="dark"] #inquiryModal .modal-footer .btn-outline-secondary:hover {
        background: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
    }
</style>

<div class="sx-ci">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-ci-stat is-unread h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.customer_inquiries.unread') }}</span>
                        <span class="stat-value" id="summaryUnread">{{ $customerInquiryUnread ?? 0 }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card sx-ci-stat is-total h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="stat-label">{{ __('superadmin.customer_inquiries.total') }}</span>
                        <span class="stat-value" id="summaryTotal">{{ $customerInquiryTotal ?? 0 }}</span>
                    </div>
                    <span class="stat-ico" aria-hidden="true"><i class="fi fi-rr-inbox"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card sx-ci-card mb-3">
        <div class="card-body">
            <div class="sx-ci-card-head">
                <h6 class="sx-ci-card-title">{{ __('superadmin.customer_inquiries.filters') }}</h6>
                <span class="small text-muted">{{ __('superadmin.customer_inquiries.filters_desc') }}</span>
            </div>
            <form id="filterForm" class="row g-3 align-items-end" onsubmit="return false;">
                <div class="col-md-2">
                    <label class="form-label" for="filterPeriod">{{ __('superadmin.customer_inquiries.period') }}</label>
                    <select id="filterPeriod" class="form-select">
                        <option value="all">{{ __('superadmin.customer_inquiries.period_all') }}</option>
                        <option value="today">{{ __('superadmin.customer_inquiries.period_today') }}</option>
                        <option value="month">{{ __('superadmin.customer_inquiries.period_month') }}</option>
                        <option value="year">{{ __('superadmin.customer_inquiries.period_year') }}</option>
                        <option value="range">{{ __('superadmin.customer_inquiries.period_range') }}</option>
                    </select>
                </div>
                <div class="col-md-2 range-field d-none">
                    <label class="form-label" for="filterStart">{{ __('superadmin.customer_inquiries.from') }}</label>
                    <input type="date" id="filterStart" class="form-control">
                </div>
                <div class="col-md-2 range-field d-none">
                    <label class="form-label" for="filterEnd">{{ __('superadmin.customer_inquiries.to') }}</label>
                    <input type="date" id="filterEnd" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="filterStatus">{{ __('superadmin.customer_inquiries.status') }}</label>
                    <select id="filterStatus" class="form-select">
                        <option value="all">{{ __('superadmin.customer_inquiries.status_all') }}</option>
                        <option value="unread">{{ __('superadmin.customer_inquiries.status_unread') }}</option>
                        <option value="read">{{ __('superadmin.customer_inquiries.status_read') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filterSearch">{{ __('superadmin.customer_inquiries.search') }}</label>
                    <input type="text" id="filterSearch" class="form-control" placeholder="{{ __('superadmin.customer_inquiries.search_placeholder') }}">
                </div>
                <div class="col-md-3 d-flex flex-wrap gap-2 align-items-end">
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="applyFilters">
                        <i class="fi fi-rr-filters me-1"></i> {{ __('superadmin.customer_inquiries.apply') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">{{ __('superadmin.customer_inquiries.reset') }}</button>
                    <div class="sx-view-toggle ms-auto" role="group" aria-label="{{ __('superadmin.customer_inquiries.view_mode') }}">
                        <button type="button" class="sx-view-btn is-active" data-view="list" title="{{ __('superadmin.customer_inquiries.list_view') }}">
                            <i class="fi fi-rr-list"></i>
                        </button>
                        <button type="button" class="sx-view-btn" data-view="grid" title="{{ __('superadmin.customer_inquiries.grid_view') }}">
                            <i class="fi fi-rr-apps"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="listView" class="card sx-ci-card">
        <div class="card-body">
            <div class="sx-ci-card-head">
                <h6 class="sx-ci-card-title">{{ __('superadmin.customer_inquiries.inquiries') }}</h6>
            </div>
            <div class="sx-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="inquiriesTable">
                        <thead>
                            <tr>
                                <th>{{ __('superadmin.customer_inquiries.th_customer') }}</th>
                                <th>{{ __('superadmin.customer_inquiries.th_contact') }}</th>
                                <th>{{ __('superadmin.customer_inquiries.th_subject') }}</th>
                                <th>{{ __('superadmin.customer_inquiries.th_message') }}</th>
                                <th>{{ __('superadmin.customer_inquiries.th_received') }}</th>
                                <th class="text-end">{{ __('superadmin.customer_inquiries.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="gridView" class="d-none">
        <div class="row g-3" id="gridContainer"></div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="gridPrev" disabled>{{ __('superadmin.customer_inquiries.previous') }}</button>
            <span class="small text-muted" id="gridPageInfo"></span>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="gridNext">{{ __('superadmin.customer_inquiries.next') }}</button>
        </div>
    </div>

</div>

<div class="modal fade" id="inquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="ci-modal-hero" id="inquiryModalHero">
                <button type="button" class="btn-close ci-modal-close" data-bs-dismiss="modal" aria-label="{{ __('superadmin.customer_inquiries.close') }}"></button>
                <div class="ci-modal-kicker"><i class="fi fi-rr-envelope me-1"></i> {{ __('superadmin.customer_inquiries.modal_title') }}</div>
                <h5 class="ci-modal-subject" id="inquiryModalSubject">{{ __('superadmin.customer_inquiries.loading') }}</h5>
            </div>
            <div class="modal-body" id="inquiryModalBody">
                <div class="text-center py-5 text-muted">{{ __('superadmin.customer_inquiries.loading') }}</div>
            </div>
            <div class="modal-footer">
                <a href="#" id="inquiryReplyEmail" class="btn btn-primary waves-effect waves-light">
                    <i class="fi fi-rr-paper-plane me-1"></i> {{ __('superadmin.customer_inquiries.reply_email') }}
                </a>
                <a href="#" id="inquiryCallPhone" class="btn btn-outline-primary d-none">
                    <i class="fi fi-rr-phone-call me-1"></i> {{ __('superadmin.customer_inquiries.call') }}
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('superadmin.customer_inquiries.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SX = @json(__('superadmin.customer_inquiries'));
const SXC = @json(__('superadmin.common'));
function sxFmt(str, map) {
    return Object.keys(map || {}).sort(function (a, b) { return b.length - a.length; }).reduce(function (s, k) { return s.split(':' + k).join(map[k]); }, String(str));
}
(function () {
    const routes = {
        data: @json(route('superadmin.customer_inquiries.data')),
        show: @json(url('super-admin/customer-inquiries')),
    };

    let currentView = 'list';
    let gridPage = 0;
    const gridPageSize = 12;
    let gridLastFiltered = 0;
    let table = null;

    function filterParams() {
        return {
            period: $('#filterPeriod').val(),
            start_date: $('#filterStart').val(),
            end_date: $('#filterEnd').val(),
            status: $('#filterStatus').val(),
            search: $('#filterSearch').val(),
        };
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function updateSummary(summary) {
        if (!summary) return;
        $('#summaryUnread').text(summary.unread ?? 0);
        $('#summaryTotal').text(summary.total ?? 0);
        if (window.refreshCustomerInquiryNotifications) {
            window.refreshCustomerInquiryNotifications();
        }
    }

    function initialsFromName(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '?';
        if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function renderModal(data) {
        $('#inquiryModalSubject').text(data.subject || SX.js.default_subject);
        $('#inquiryModalBody').html(
            '<div class="ci-profile">' +
                '<div class="ci-avatar" aria-hidden="true">' + escapeHtml(initialsFromName(data.name)) + '</div>' +
                '<div>' +
                    '<div class="ci-profile-name">' + escapeHtml(data.name) + '</div>' +
                    '<div class="ci-profile-meta">' + escapeHtml(SX.js.received) + ' ' + escapeHtml(data.created_at || '-') +
                        (data.created_human ? ' · ' + escapeHtml(data.created_human) : '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="ci-meta-grid">' +
                '<div class="ci-meta-item">' +
                    '<span class="ci-meta-label">' + escapeHtml(SX.js.email) + '</span>' +
                    '<span class="ci-meta-value"><a href="mailto:' + escapeHtml(data.email) + '">' + escapeHtml(data.email) + '</a></span>' +
                '</div>' +
                '<div class="ci-meta-item">' +
                    '<span class="ci-meta-label">' + escapeHtml(SX.js.phone) + '</span>' +
                    '<span class="ci-meta-value">' +
                        (data.phone
                            ? '<a href="tel:' + escapeHtml(data.phone) + '">' + escapeHtml(data.phone) + '</a>'
                            : '-') +
                    '</span>' +
                '</div>' +
            '</div>' +
            '<div class="ci-message-card">' +
                '<div class="ci-message-head"><i class="fi fi-rr-comment-alt"></i> ' + escapeHtml(SX.js.message) + '</div>' +
                '<div class="sx-message-box">' + escapeHtml(data.message) + '</div>' +
            '</div>'
        );
        $('#inquiryReplyEmail').attr('href', 'mailto:' + encodeURIComponent(data.email) + '?subject=' + encodeURIComponent('Re: ' + data.subject));
        if (data.phone) {
            $('#inquiryCallPhone').removeClass('d-none').attr('href', 'tel:' + data.phone);
        } else {
            $('#inquiryCallPhone').addClass('d-none');
        }
    }

    window.openCustomerInquiry = function (id) {
        const modal = new bootstrap.Modal(document.getElementById('inquiryModal'));
        $('#inquiryModalSubject').text(SX.loading);
        $('#inquiryModalBody').html('<div class="text-center py-5 text-muted">' + escapeHtml(SX.js.loading) + '</div>');
        modal.show();

        $.get(routes.show + '/' + id, function (res) {
            if (!res.success) {
                $('#inquiryModalBody').html('<div class="text-center py-5 text-muted">' + escapeHtml(SX.js.load_failed) + '</div>');
                return;
            }
            renderModal(res.data);
            if (currentView === 'list' && table) table.ajax.reload(null, false);
            else if (currentView === 'grid') loadGrid();
            updateSummary({
                unread: Math.max(0, parseInt($('#summaryUnread').text(), 10) - (res.data.is_read ? 0 : 1)),
                total: $('#summaryTotal').text()
            });
        }).fail(function () {
            $('#inquiryModalBody').html('<div class="text-center py-5 text-muted">' + escapeHtml(SX.js.load_failed) + '</div>');
        });
    };

    function initTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#inquiriesTable')) {
            $('#inquiriesTable').DataTable().destroy();
        }

        table = $('#inquiriesTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: true,
            pageLength: 10,
            order: [],
            autoWidth: false,
            ajax: {
                url: routes.data,
                data: function (d) {
                    const f = filterParams();
                    d.period = f.period;
                    d.start_date = f.start_date;
                    d.end_date = f.end_date;
                    d.status = f.status;
                    d.filter_search = f.search;
                },
                dataSrc: function (json) {
                    updateSummary(json.summary);
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'name',
                    render: function (data, type, row) {
                        const badge = row.is_read ? '' : '<span class="sx-badge-unread">' + escapeHtml(SX.js.unread) + '</span>';
                        return '<div>' + escapeHtml(data) + badge + '</div>';
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return '<div class="small">' + escapeHtml(row.email) + '</div><div class="small text-muted">' + escapeHtml(row.phone || '-') + '</div>';
                    }
                },
                {
                    data: 'subject',
                    render: function (d) {
                        return '<div class="ci-subject-cell" title="' + escapeHtml(d) + '">' + escapeHtml(d) + '</div>';
                    }
                },
                {
                    data: 'message_preview',
                    render: function (d) {
                        return '<div class="ci-msg-cell text-muted small" title="' + escapeHtml(d) + '">' + escapeHtml(d) + '</div>';
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return '<div class="small">' + escapeHtml(row.created_at) + '</div><div class="text-muted small">' + escapeHtml(row.created_human) + '</div>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return '<button type="button" class="btn btn-sm btn-outline-primary" onclick="openCustomerInquiry(' + row.id + ')"><i class="fi fi-rr-eye me-1"></i>' + escapeHtml(SX.js.view) + '</button>';
                    }
                }
            ],
            createdRow: function (row, data) {
                if (!data.is_read) $(row).addClass('unread-row');
            },
            language: {
                lengthMenu: SXC.datatables_show,
                info: SXC.datatables_info,
                zeroRecords: SX.js.no_inquiries,
                paginate: { previous: SXC.previous, next: SXC.next }
            },
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"l>t<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ip>'
        });
    }

    function loadGrid() {
        const start = gridPage * gridPageSize;
        const f = filterParams();
        $.get(routes.data, {
            start: start,
            length: gridPageSize,
            period: f.period,
            start_date: f.start_date,
            end_date: f.end_date,
            status: f.status,
            filter_search: f.search,
        }, function (res) {
            if (!res.success) return;
            updateSummary(res.summary);
            gridLastFiltered = res.recordsFiltered;
            const $container = $('#gridContainer').empty();

            if (!res.data.length) {
                $container.html('<div class="col-12"><div class="alert alert-light border text-center mb-0">' + escapeHtml(SX.js.no_inquiries_filters) + '</div></div>');
            } else {
                res.data.forEach(function (row) {
                    const unreadClass = row.is_read ? '' : ' unread';
                    const badge = row.is_read ? '' : '<span class="sx-badge-unread">' + escapeHtml(SX.js.unread) + '</span>';
                    $container.append(
                        '<div class="col-md-6 col-xl-4">' +
                            '<div class="ci-grid-card' + unreadClass + '">' +
                                '<div class="d-flex justify-content-between align-items-start gap-2 mb-2">' +
                                    '<div class="ci-subject">' + escapeHtml(row.subject) + '</div>' + badge +
                                '</div>' +
                                '<div class="ci-meta mb-2"><strong>' + escapeHtml(row.name) + '</strong></div>' +
                                '<div class="ci-meta mb-1"><i class="fi fi-rr-envelope"></i> ' + escapeHtml(row.email) + '</div>' +
                                '<div class="ci-meta mb-2"><i class="fi fi-rr-phone-call"></i> ' + escapeHtml(row.phone || '-') + '</div>' +
                                '<p class="small text-muted mb-3 ci-msg-preview">' + escapeHtml(row.message_preview) + '</p>' +
                                '<div class="d-flex justify-content-between align-items-center">' +
                                    '<span class="small text-muted">' + escapeHtml(row.created_at) + '</span>' +
                                    '<button type="button" class="btn btn-sm btn-outline-primary" onclick="openCustomerInquiry(' + row.id + ')"><i class="fi fi-rr-eye me-1"></i>' + escapeHtml(SX.js.view_more) + '</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    );
                });
            }

            const totalPages = Math.max(1, Math.ceil(gridLastFiltered / gridPageSize));
            $('#gridPageInfo').text(sxFmt(SX.js.page_of, { page: gridPage + 1, pages: totalPages }));
            $('#gridPrev').prop('disabled', gridPage <= 0);
            $('#gridNext').prop('disabled', (gridPage + 1) >= totalPages);
        });
    }

    function switchView(view) {
        currentView = view;
        $('.sx-view-btn').removeClass('is-active');
        $('.sx-view-btn[data-view="' + view + '"]').addClass('is-active');
        if (view === 'grid') {
            $('#listView').addClass('d-none');
            $('#gridView').removeClass('d-none');
            loadGrid();
        } else {
            $('#gridView').addClass('d-none');
            $('#listView').removeClass('d-none');
            if (!table) initTable();
            else table.ajax.reload();
        }
    }

    $('#filterPeriod').on('change', function () {
        $('.range-field').toggleClass('d-none', $(this).val() !== 'range');
    });

    $('#applyFilters').on('click', function () {
        gridPage = 0;
        if (currentView === 'grid') loadGrid();
        else if (table) table.ajax.reload();
    });

    $('#resetFilters').on('click', function () {
        $('#filterPeriod').val('all');
        $('#filterStart, #filterEnd, #filterSearch').val('');
        $('#filterStatus').val('all');
        $('.range-field').addClass('d-none');
        gridPage = 0;
        if (currentView === 'grid') loadGrid();
        else if (table) table.ajax.reload();
    });

    $('.sx-view-btn').on('click', function () {
        switchView($(this).data('view'));
    });

    $('#gridPrev').on('click', function () {
        if (gridPage > 0) { gridPage--; loadGrid(); }
    });
    $('#gridNext').on('click', function () {
        gridPage++;
        loadGrid();
    });

    initTable();

    const openId = new URLSearchParams(window.location.search).get('open');
    if (openId) {
        window.openCustomerInquiry(openId);
    }
})();
</script>
@endpush
