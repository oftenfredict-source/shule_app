@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.requisitions').' - ShuleXpert')
@section('page_title', __('teacher.pages.requisitions'))
@section('page_breadcrumb', __('teacher.pages.requisitions_bc'))

@section('page_actions')
    <button type="button" class="btn btn-primary btn-sm waves-effect" data-bs-toggle="modal" data-bs-target="#requestItemModal">
        <i class="fa-solid fa-plus me-1"></i> {{ __('teacher.req.request_item') }}
    </button>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    #requestItemModal .modal-dialog {
        max-width: 720px;
        margin: 1.25rem auto;
    }
    #requestItemModal .modal-content {
        border: none;
        border-radius: 0;
        overflow: visible;
        background: #fff;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
    }
    #requestItemModal .modal-header {
        border: none;
        padding: 1.15rem 1.4rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
    }
    #requestItemModal .modal-title {
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 800;
        color: #fff !important;
    }
    #requestItemModal .modal-body {
        padding: 1.15rem 1.35rem 0.45rem;
        background: #faf7f7;
        max-height: min(72vh, 640px);
        overflow-y: auto;
    }
    #requestItemModal .sx-req-lead {
        font-size: 0.86rem;
        font-weight: 600;
        color: #555 !important;
        margin: 0 0 0.95rem;
        line-height: 1.45;
    }
    #requestItemModal .sx-req-section {
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        padding: 1rem 1.05rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    #requestItemModal .sx-req-section-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0 0 0.85rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #940000 !important;
    }
    #requestItemModal .sx-req-step {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #940000;
        color: #fff !important;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #requestItemModal .sx-req-requester {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.1);
        padding: 0.85rem 1rem;
        margin: 0;
    }
    #requestItemModal .sx-req-requester-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #888 !important;
        margin-bottom: 0.15rem;
    }
    #requestItemModal .form-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #222 !important;
        margin-bottom: 0.4rem;
        text-transform: none;
        letter-spacing: 0;
    }
    #requestItemModal .form-hint {
        display: block;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        color: #666 !important;
        line-height: 1.4;
    }
    #requestItemModal .form-control,
    #requestItemModal .form-select:not(.select2-hidden-accessible) {
        border-radius: 0 !important;
        min-height: 46px !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        color: #111 !important;
        background: #fff !important;
        border: 1px solid #cfcfcf !important;
        -webkit-text-fill-color: #111 !important;
        box-shadow: none !important;
    }
    #requestItemModal textarea.form-control {
        min-height: 96px !important;
        line-height: 1.45;
        padding-top: 0.7rem;
    }
    #requestItemModal .form-control:focus,
    #requestItemModal .form-select:not(.select2-hidden-accessible):focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }
    #requestItemModal select.select2-hidden-accessible {
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        height: 1px !important;
        margin: -1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        width: 1px !important;
        white-space: nowrap !important;
        -webkit-text-fill-color: transparent !important;
    }
    #requestItemModal .select2-container { width: 100% !important; }
    #requestItemModal .select2-container--bootstrap-5 .select2-selection--single {
        border-radius: 0 !important;
        min-height: 46px !important;
        border: 1px solid #cfcfcf !important;
        background: #fff !important;
        display: flex !important;
        align-items: center;
        padding: 0.25rem 0.75rem;
    }
    #requestItemModal .select2-container--bootstrap-5.select2-container--focus .select2-selection--single,
    #requestItemModal .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }
    #requestItemModal .select2-container--bootstrap-5 .select2-selection__rendered {
        color: #111 !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        padding: 0 !important;
        line-height: 1.3 !important;
    }
    #requestItemModal .select2-container--bootstrap-5 .select2-selection__placeholder {
        color: #888 !important;
        font-weight: 600 !important;
    }
    #requestItemModal .select2-container--bootstrap-5 .select2-selection__arrow {
        height: 44px !important;
    }
    .select2-container--open { z-index: 2000 !important; }
    .select2-dropdown {
        border-radius: 0 !important;
        border-color: #cfcfcf !important;
        z-index: 2001 !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background: #940000 !important;
    }
    #requestItemModal .sx-req-stock-info {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        margin-top: 0.85rem;
        padding: 0.75rem 0.9rem;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.1);
        font-size: 0.86rem;
        font-weight: 600;
        color: #222 !important;
    }
    #requestItemModal .sx-req-meta-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #888 !important;
        margin-bottom: 0.1rem;
    }
    #requestItemModal .sx-req-check-list {
        max-height: 220px;
        overflow-y: auto;
        background: #fafafa;
        border: 1px solid #e6e6e6;
        padding: 0.85rem 1rem;
    }
    #requestItemModal .sx-req-preview {
        margin-top: 0.85rem;
        padding: 0.75rem 0.9rem;
        background: #f0f7ff;
        border: 1px solid #cfe2ff;
        font-size: 0.86rem;
        font-weight: 600;
        color: #084298 !important;
    }
    #requestItemModal .store-checkbox-toolbar .btn {
        border-radius: 0 !important;
        font-weight: 700;
    }
    #requestItemModal .modal-footer {
        border-top: 1px solid rgba(148, 0, 0, 0.1);
        background: #fff;
        padding: 0.9rem 1.35rem 1.1rem;
        gap: 0.55rem;
    }
    #requestItemModal .modal-footer .btn {
        border-radius: 0 !important;
        font-weight: 800;
        min-height: 42px;
        padding: 0.5rem 1.1rem;
    }
    #requestItemModal .modal-footer .btn-primary-custom,
    #requestItemModal .modal-footer .btn-primary-custom:disabled {
        background: #940000;
        border-color: #940000;
        color: #fff;
    }
    #requestItemModal .modal-footer .btn-primary-custom:not(:disabled):hover {
        background: #7b0000;
        border-color: #7b0000;
    }
    #requestItemModal .modal-footer .btn-outline-secondary {
        color: #555;
        border-color: #cfcfcf;
    }
    body.modal-open > .modal-backdrop {
        background: rgba(20, 0, 0, 0.55);
    }
    @media (max-width: 576px) {
        #requestItemModal .modal-dialog { margin: 0.5rem; }
        #requestItemModal .modal-footer .btn { width: 100%; }
    }

    /* Dark mode: modal lead + requester name must stay readable */
    html[data-sx-theme="dark"] #requestItemModal .modal-content,
    html[data-bs-theme="dark"] #requestItemModal .modal-content {
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .modal-body,
    html[data-bs-theme="dark"] #requestItemModal .modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-lead,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-lead {
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-section,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-section {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-section-title,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-section-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-requester,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-requester,
    html[data-sx-theme="dark"] #requestItemModal .portal-requester-box,
    html[data-bs-theme="dark"] #requestItemModal .portal-requester-box {
        background: #2a1c20 !important;
        border-color: rgba(255, 180, 180, 0.18) !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-requester-label,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-requester-label,
    html[data-sx-theme="dark"] #requestItemModal .sx-req-meta-label,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-meta-label {
        color: #c9b4b8 !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-requester strong,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-requester strong,
    html[data-sx-theme="dark"] #requestItemModal .portal-requester-box strong,
    html[data-bs-theme="dark"] #requestItemModal .portal-requester-box strong {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-requester .text-muted,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-requester .text-muted,
    html[data-sx-theme="dark"] #requestItemModal .portal-requester-box .text-muted,
    html[data-bs-theme="dark"] #requestItemModal .portal-requester-box .text-muted {
        color: #e8c9cd !important;
        -webkit-text-fill-color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .form-label,
    html[data-bs-theme="dark"] #requestItemModal .form-label {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .form-hint,
    html[data-bs-theme="dark"] #requestItemModal .form-hint {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .form-control,
    html[data-bs-theme="dark"] #requestItemModal .form-control,
    html[data-sx-theme="dark"] #requestItemModal .form-select:not(.select2-hidden-accessible),
    html[data-bs-theme="dark"] #requestItemModal .form-select:not(.select2-hidden-accessible) {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .select2-container--bootstrap-5 .select2-selection--single,
    html[data-bs-theme="dark"] #requestItemModal .select2-container--bootstrap-5 .select2-selection--single {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .select2-container--bootstrap-5 .select2-selection__rendered,
    html[data-bs-theme="dark"] #requestItemModal .select2-container--bootstrap-5 .select2-selection__rendered {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .select2-container--bootstrap-5 .select2-selection__placeholder,
    html[data-bs-theme="dark"] #requestItemModal .select2-container--bootstrap-5 .select2-selection__placeholder {
        color: #a99599 !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-stock-info,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-stock-info,
    html[data-sx-theme="dark"] #requestItemModal .sx-req-check-list,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-check-list {
        background: #2a1c20 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .sx-req-preview,
    html[data-bs-theme="dark"] #requestItemModal .sx-req-preview {
        background: rgba(13, 110, 253, 0.15) !important;
        border-color: rgba(116, 192, 252, 0.35) !important;
        color: #74c0fc !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .modal-footer,
    html[data-bs-theme="dark"] #requestItemModal .modal-footer {
        background: #1c1417 !important;
        border-top-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] #requestItemModal .modal-footer .btn-outline-secondary,
    html[data-bs-theme="dark"] #requestItemModal .modal-footer .btn-outline-secondary {
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        background: transparent !important;
    }
</style>
@endpush

@section('content')
@include('portal.partials.store_requisitions_body', ['useTeacherCorp' => true])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
