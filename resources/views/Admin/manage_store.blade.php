@php
    $storeRole = $storeAccess['role'] ?? 'admin';
    $storePriv = $storeAccess['privileges'] ?? [];
    $canManageActors = !empty($storeAccess['can_manage_actors']);
@endphp
@if($storeRole === 'admin')
    @include('includes.Admin_nav')
@elseif(($storeAccess['actor_type'] ?? '') === 'teacher')
    @include('includes.teacher_nav')
@else
    @include('includes.staff_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select, .select2-container {
        border-radius: 0 !important;
    }
    .bg-primary-custom { background-color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }

    /* Unique store AJAX loader */
    .store-ajax-bar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        z-index: 20000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease;
        background: rgba(148, 0, 0, 0.08);
    }
    .store-ajax-bar.is-active {
        opacity: 1;
    }
    .store-ajax-bar-fill {
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, #940000, #ff4d4d, #940000);
        background-size: 200% 100%;
        box-shadow: 0 0 10px rgba(148, 0, 0, 0.55);
        transition: width 0.25s ease;
    }
    .store-ajax-bar.is-active .store-ajax-bar-fill {
        animation: storeBarRun 1.1s ease-in-out infinite, storeBarShine 1.2s linear infinite;
        width: 70%;
    }
    .store-ajax-bar.is-finishing .store-ajax-bar-fill {
        animation: none;
        width: 100% !important;
        transition: width 0.2s ease;
    }
    @keyframes storeBarRun {
        0% { margin-left: -40%; width: 30%; }
        50% { margin-left: 20%; width: 55%; }
        100% { margin-left: 100%; width: 30%; }
    }
    @keyframes storeBarShine {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .store-ajax-overlay {
        position: fixed;
        inset: 0;
        z-index: 19999;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(20, 0, 0, 0.28);
        backdrop-filter: blur(2px);
    }
    .store-ajax-overlay.is-active {
        display: flex;
    }
    .store-ajax-card {
        min-width: 220px;
        max-width: 320px;
        background: #fff;
        border: 2px solid #940000;
        box-shadow: 0 12px 40px rgba(148, 0, 0, 0.25);
        padding: 22px 24px 18px;
        text-align: center;
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .store-ajax-orbit {
        width: 52px;
        height: 52px;
        margin: 0 auto 12px;
        position: relative;
    }
    .store-ajax-orbit span {
        position: absolute;
        inset: 0;
        border: 2px solid transparent;
        border-top-color: #940000;
        border-radius: 50%;
        animation: storeOrbit 0.9s linear infinite;
    }
    .store-ajax-orbit span:nth-child(2) {
        inset: 8px;
        border-top-color: #c44;
        animation-duration: 0.7s;
        animation-direction: reverse;
    }
    .store-ajax-orbit span:nth-child(3) {
        inset: 16px;
        border-top-color: #f0a0a0;
        animation-duration: 0.5s;
    }
    @keyframes storeOrbit {
        to { transform: rotate(360deg); }
    }
    .store-ajax-text {
        font-weight: 700;
        color: #940000;
        font-size: 0.95rem;
    }
    .store-ajax-sub {
        font-size: 0.75rem;
        color: #888;
        margin-top: 4px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    body.store-ajax-busy {
        cursor: progress;
    }
    body.store-ajax-busy .modal .btn[type="submit"],
    body.store-ajax-busy .modal button[type="submit"] {
        pointer-events: none;
        opacity: 0.75;
    }
    .store-swal-popup {
        font-family: "Century Gothic", Arial, sans-serif !important;
        border-radius: 0 !important;
    }
    .store-swal-confirm {
        border-radius: 0 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .store-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .store-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
    }
    .section-title { font-weight: 600; margin-bottom: 12px; color: #940000; font-size: 1.05rem; }
    .unit-chip {
        display: inline-block;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 4px 10px;
        margin: 3px;
        font-size: 0.85rem;
    }
    .unit-chip .btn-link {
        padding: 0 4px;
        font-size: 0.75rem;
    }
    .unit-chip.unit-hidden {
        display: none !important;
    }
    .measurement-type-card.measurement-hidden {
        display: none !important;
    }
    .units-search-bar {
        width: 100%;
        margin-bottom: 1rem;
    }
    .units-search-bar .form-control {
        width: 100%;
        padding: 10px 14px 10px 40px;
        font-size: 0.95rem;
        border: 1px solid #ced4da;
    }
    .units-search-bar .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }
    .units-search-bar .search-icon-wrap {
        position: relative;
        width: 100%;
    }
    .stock-label-row {
        margin-bottom: 8px;
    }
    .stock-label-row .label-prefix {
        min-width: 90px;
        font-size: 0.85rem;
        color: #666;
    }
    .units-search-bar .search-icon-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }
    #unitMeasureSearchCount {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 6px;
    }
    /* Searchable selects in Add/Edit Item modals — same width as item name input */
    .item-select-field {
        width: 100%;
    }
    #addItemModal .item-form-select,
    #editItemModal .item-form-select {
        width: 100%;
    }
    #addItemModal .select2-container,
    #editItemModal .select2-container {
        width: 100% !important;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-selection,
    #editItemModal .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-selection--single,
    #editItemModal .select2-container--bootstrap-5 .select2-selection--single {
        height: 38px;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
    #editItemModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow,
    #editItemModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-dropdown,
    #editItemModal .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 10060 !important;
        box-sizing: border-box;
    }
    #addItemModal .pkg-select-wrap,
    #editItemModal .pkg-select-wrap {
        position: relative;
    }
    #addItemModal .pkg-select-wrap .select2-container,
    #editItemModal .pkg-select-wrap .select2-container {
        width: 100% !important;
    }
    #addItemModal .pkg-select-wrap .select2-container--open,
    #editItemModal .pkg-select-wrap .select2-container--open {
        z-index: 10070 !important;
    }
    #addItemModal .pkg-select-wrap .select2-dropdown,
    #editItemModal .pkg-select-wrap .select2-dropdown {
        z-index: 10070 !important;
    }
    .select2-results__option[aria-disabled="true"] {
        opacity: 0.55 !important;
        background: #f0f0f0 !important;
        color: #888 !important;
        font-style: italic;
        cursor: not-allowed !important;
    }
    .pkg-option-main {
        opacity: 0.7;
        color: #888;
        font-style: italic;
    }
    .pkg-option-main .pkg-main-badge {
        display: inline-block;
        margin-left: 6px;
        font-size: 0.7rem;
        font-style: normal;
        font-weight: 600;
        color: #940000;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        padding: 0 5px;
    }
    .pkg-live-dashboard {
        border: 1px solid #e8d4d4;
        background: #fffafa;
        padding: 12px;
    }
    .pkg-live-dashboard-title {
        font-weight: 700;
        color: #940000;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    .pkg-live-chain-block {
        margin-bottom: 12px;
    }
    .pkg-live-chain-block:last-child {
        margin-bottom: 0;
    }
    .pkg-live-chain-name {
        font-weight: 600;
        font-size: 0.82rem;
        color: #555;
        margin-bottom: 6px;
    }
    .pkg-live-widgets {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 8px;
    }
    .pkg-live-widget {
        background: #fff;
        border: 1px solid #f0d0d0;
        padding: 10px 8px;
        text-align: center;
    }
    .pkg-live-widget .pkg-live-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: #940000;
        line-height: 1.2;
    }
    .pkg-live-widget .pkg-live-label {
        font-size: 0.72rem;
        color: #666;
        margin-top: 3px;
        word-break: break-word;
    }
    .pkg-live-flow {
        font-size: 0.82rem;
        color: #333;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed #e8d4d4;
    }
    .pkg-live-flow span {
        display: inline-block;
        margin: 2px 0;
    }
    .pkg-chain-summary {
        border: 1px solid #e8d4d4;
        background: #fffafa;
        padding: 10px;
        margin-bottom: 10px;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-selection--multiple,
    #editItemModal .select2-container--bootstrap-5 .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    #addItemModal .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice,
    #editItemModal .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #fff5f5;
        border-color: #f0d0d0;
        color: #940000;
    }
    #addItemModal .select2-search--dropdown,
    #editItemModal .select2-search--dropdown {
        display: block !important;
        padding: 6px 8px !important;
    }
    #addItemModal .select2-search--dropdown .select2-search__field,
    #editItemModal .select2-search--dropdown .select2-search__field {
        width: 100% !important;
        padding: 6px 10px !important;
        font-size: 0.9rem;
        border: 1px solid #ced4da;
        box-sizing: border-box;
    }
    #addItemModal .select2-search--dropdown .select2-search__field:focus,
    #editItemModal .select2-search--dropdown .select2-search__field:focus {
        border-color: #940000;
        outline: none;
    }
    #inventoryActorsModal .actor-form-select {
        width: 100% !important;
    }
    #inventoryActorsModal .select2-container {
        width: 100% !important;
        z-index: 1060;
    }
    #inventoryActorsModal .select2-container--open {
        z-index: 1061;
    }
    #inventoryActorsModal .select2-dropdown {
        z-index: 1062;
    }
    #inventoryActorsModal .select2-container--bootstrap-5 .select2-selection--single {
        min-height: 38px;
        border-radius: 0 !important;
        scroll-margin: 0;
    }
    #inventoryActorsModal .select2-container .select2-selection {
        scroll-margin: 0;
    }
    #inventoryActorsModal .select2-search--dropdown .select2-search__field {
        border-radius: 0 !important;
        border: 1px solid #ced4da;
        padding: 8px 10px;
    }
    #inventoryActorsModal .select2-search--dropdown .select2-search__field:focus {
        border-color: #940000;
        outline: none;
    }

    /* Actors modal — scroll ndani ya tab content tu (hairuki juu) */
    #inventoryActorsModal .modal-dialog {
        display: flex;
        align-items: flex-start;
        margin-top: 0.75rem;
        margin-bottom: 0.75rem;
        max-height: calc(100vh - 1.5rem);
    }
    #inventoryActorsModal .modal-content {
        max-height: calc(100vh - 1.5rem);
    }
    #inventoryActorsModal .store-actors-modal-body {
        display: flex;
        flex-direction: column;
        overflow: hidden !important;
        min-height: 0;
        flex: 1 1 auto;
        padding-bottom: 0.75rem;
    }
    #inventoryActorsModal .store-actors-tab-scroll {
        flex: 1 1 auto;
        min-height: 0;
        max-height: calc(100vh - 14rem);
        /* Force always-visible scrollbar */
        overflow-y: scroll;
        overflow-x: hidden;
        overscroll-behavior: contain;
        overflow-anchor: none;
        -webkit-overflow-scrolling: touch;
        scrollbar-gutter: stable both-edges;
        scrollbar-width: auto;
        scrollbar-color: #b0b0b0 #f0f0f0;
        padding-right: 2px;
    }
    #inventoryActorsModal .store-actors-tab-scroll::-webkit-scrollbar {
        width: 12px;
    }
    #inventoryActorsModal .store-actors-tab-scroll::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 4px;
    }
    #inventoryActorsModal .store-actors-tab-scroll::-webkit-scrollbar-thumb {
        background: #b0b0b0;
        border-radius: 4px;
    }
    #inventoryActorsModal .store-actors-tab-scroll::-webkit-scrollbar-thumb:hover {
        background: #888;
    }
    #inventoryActorsModal .store-actors-person-strip {
        flex-shrink: 0;
        padding-bottom: 0.5rem;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
        background: #fff;
    }
    #inventoryActorsModal #inventoryActorForm {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        margin: 0;
    }
    #inventoryActorsModal #actorCategoryChecks {
        background: #fafafa;
    }
    #inventoryActorsModal #actorCategoryChecks .custom-control {
        margin-bottom: 0.35rem;
    }
    #inventoryActorsModal .store-actors-tab-scroll input,
    #inventoryActorsModal .store-actors-tab-scroll select,
    #inventoryActorsModal .store-actors-tab-scroll label {
        scroll-margin: 0;
    }
    #inventoryActorsModal .modal-footer {
        border-top: 1px solid #dee2e6;
        background: #fff;
        padding: 0.75rem 1rem;
    }

    /* Store modals — let the browser/viewport handle scrolling (no inner modal-body scrollbars) */
    .store-modal .modal-dialog {
        margin: 0.75rem auto;
    }
    .store-modal .modal-content {
        width: 100%;
    }
    .store-modal .modal-body {
        overflow: visible !important;
        max-height: none !important;
        -webkit-overflow-scrolling: auto;
        overflow-anchor: none;
    }
    /* Focused inputs must NOT pull scroll back while user is scrolling */
    .store-modal .modal-body input,
    .store-modal .modal-body select,
    .store-modal .modal-body textarea,
    .store-modal .modal-body .select2-selection {
        scroll-margin: 0 !important;
        scroll-margin-block: 0 !important;
    }
    /* Sticky action bar inside Stock modal body */
    #addItemStockModal .stock-sticky-actions {
        position: sticky;
        bottom: -1px;
        z-index: 5;
        display: flex;
        justify-content: flex-end;
        padding: 0.75rem 0;
        background: #fff;
        border-top: 1px solid #dee2e6;
    }

    /* Inline expanded stock form: let page/browser handle scrolling */
    #addItemStockModal .modal-body {
        overflow: visible !important;
        max-height: none !important;
    }
    #addItemStockModal .stock-modal-scroll {
        max-height: none;
        overflow: visible;
    }

    /* Override any global scrollbar-hiding styles on store scroll areas */
    #inventoryActorsModal .store-actors-tab-scroll::-webkit-scrollbar {
        display: block !important;
    }
    .store-modal .store-actors-scroll-box {
        scrollbar-width: thin;
        scrollbar-color: #b0b0b0 #f0f0f0;
    }
    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }
    .store-modal.modal {
        /* Fallback: allow outer modal to scroll if body still overflows on tiny screens */
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .store-checkbox-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }
    .store-checkbox-toolbar .btn {
        font-size: 0.75rem;
        padding: 3px 10px;
        line-height: 1.3;
    }

    .select2-results__option.select-add-option {
        color: #940000 !important;
        font-weight: 600;
        border-top: 1px dashed #dee2e6;
        margin-top: 4px;
        padding-top: 10px !important;
    }
    .store-section { display: none; }
    .store-section.active { display: block; }
    .store-action-dropdown { display: inline-block; }
    .store-action-dropdown .store-action-toggle {
        padding: 4px 12px;
        color: #555;
        background: #fff;
        border: 1px solid #dee2e6;
        line-height: 1.2;
    }
    .store-action-dropdown .store-action-toggle:hover,
    .store-action-dropdown.show .store-action-toggle {
        background: #f8f9fa;
        color: #940000;
        border-color: #ccc;
    }
    .store-action-dropdown .dropdown-menu {
        min-width: 150px;
        padding: 4px 0;
        font-size: 0.875rem;
    }
    .store-action-dropdown .dropdown-item {
        padding: 8px 14px;
    }
    .store-action-dropdown .dropdown-item i {
        width: 18px;
    }
    .store-actions-cell { width: 60px; text-align: center; }
    .store-tag {
        display: inline-block;
        background: #fff5f5;
        border: 1px solid #e0bcbc;
        color: #333;
        padding: 4px 10px;
        margin: 3px 4px 3px 0;
        font-size: 0.8rem;
        line-height: 1.3;
    }
    .store-badge-pill {
        display: inline-block;
        padding: 4px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .store-badge-package {
        background: #e7f3ff;
        color: #0c5460;
        border-color: #bee5eb;
    }
    .store-badge-unit {
        background: #f0f0f0;
        color: #495057;
        border-color: #dee2e6;
    }
    .store-badge-return-yes {
        background: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }
    .store-badge-return-no {
        background: #fff3cd;
        color: #856404;
        border-color: #ffeeba;
    }
    .store-badge-overdue {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
        font-weight: 600;
    }
    .store-badge-pending {
        background: #e2e3e5;
        color: #383d41;
        border-color: #d6d8db;
    }
    .store-badge-new-request {
        background: #ffc107;
        color: #212529;
    }
    .store-menu-badge {
        display: inline-block;
        min-width: 18px;
        padding: 1px 6px;
        margin-left: 4px;
        font-size: 0.68rem;
        line-height: 1.4;
        border-radius: 10px;
        background: #ffc107;
        color: #212529;
        font-weight: 700;
        vertical-align: middle;
    }
    .store-item-request-row.is-pending {
        cursor: pointer;
    }
    #requisitionsTable tbody tr.store-item-request-row.is-pending td {
        background: #fffbf0 !important;
        border-top: 2px solid #ffc107 !important;
        border-bottom: 2px solid #ffc107 !important;
    }
    #requisitionsTable tbody tr.store-item-request-row.is-pending td:first-child {
        border-left: 2px solid #ffc107 !important;
    }
    #requisitionsTable tbody tr.store-item-request-row.is-pending td:last-child {
        border-right: 2px solid #ffc107 !important;
    }
    #requisitionsTable tbody tr.store-item-request-row.is-pending.is-new td {
        background: #fff8e1 !important;
    }
    #requisitionsTable tbody tr.store-item-request-row.is-pending:hover td {
        background: #fff3cd !important;
    }
    .store-item-request-row.is-reviewed td {
        opacity: 0.92;
    }
    .text-request-highlight { color: #d39e00 !important; }
    .store-requisition-filter {
        margin-bottom: 1rem;
    }
    .store-requisition-filter label {
        font-weight: 600;
        font-size: 0.85rem;
    }
    .store-view-widgets .store-widget-section {
        margin-bottom: 1.25rem;
    }
    .store-view-widgets .store-widget-section-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #940000;
        border-bottom: 2px solid #f0d0d0;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .store-view-widget-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }
    .store-view-widget {
        border: 1px solid #e9ecef;
        background: #fafafa;
        padding: 10px 12px;
    }
    .store-view-widget .widget-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .store-view-widget .widget-value {
        font-size: 0.9rem;
        color: #333;
        word-break: break-word;
    }
    .store-view-widget.widget-danger {
        border-color: #f5c6cb;
        background: #fff5f5;
    }
    .store-view-widget.widget-danger .widget-value {
        color: #721c24;
        font-weight: 600;
    }
    .store-view-widget.widget-wide {
        grid-column: 1 / -1;
    }
    .req-return-overdue {
        color: #dc3545 !important;
        font-weight: 600;
    }
    .store-view-label {
        font-weight: 600;
        font-size: 0.8rem;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }
    .store-view-value {
        font-size: 0.95rem;
        color: #333;
    }
    .store-view-tags-wrap {
        max-height: 320px;
        overflow-y: auto;
        padding: 4px 0;
    }
    .store-table-compact td,
    .store-table-compact th {
        vertical-align: middle !important;
    }

    /* Responsive navigation — horizontal tabs (Bootstrap 4.1 local haija list-group-horizontal) */
    .store-menu-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }
    .store-menu.list-group,
    .list-group.store-menu {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap;
        min-width: min-content;
        width: 100%;
        padding-left: 0;
        margin-bottom: 0;
    }
    .store-menu {
        flex-wrap: nowrap;
        min-width: min-content;
    }
    .store-menu > .list-group-item {
        flex: 1 1 0;
        width: auto;
        margin-bottom: 0 !important;
        white-space: nowrap;
        min-width: 88px;
        font-size: 0.82rem;
        padding: 10px 8px;
        cursor: pointer;
        border-left: 4px solid transparent;
        color: #333;
        background: #fff;
        border-radius: 0 !important;
        text-align: center;
    }
    .store-menu > .list-group-item + .list-group-item {
        border-top-width: 1px;
        border-left-width: 0;
    }
    .store-menu > .list-group-item.active {
        margin-top: 0;
    }
    .store-menu > .list-group-item:hover {
        background: #fafafa;
        color: #940000;
    }
    .store-menu .list-group-item {
        white-space: nowrap;
        min-width: 88px;
        font-size: 0.82rem;
        padding: 10px 8px;
    }
    .store-menu .list-group-item.active {
        border-left-color: #940000 !important;
        background: #fff5f5 !important;
        color: #940000 !important;
        font-weight: 600;
        z-index: 1;
    }
    .store-section-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 12px;
    }
    .store-section-toolbar .section-title {
        margin-bottom: 0;
    }
    .store-view-toggle .btn {
        min-width: 72px;
    }
    .store-view-toggle .btn.active {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .store-data-wrapper .store-grid-view {
        display: none;
    }
    .store-data-wrapper[data-active-view="list"] .store-list-view {
        display: block !important;
    }
    .store-data-wrapper[data-active-view="list"] .store-grid-view {
        display: none !important;
    }
    .store-data-wrapper[data-active-view="grid"] .store-list-view {
        display: none !important;
    }
    .store-data-wrapper[data-active-view="grid"] .store-grid-view {
        display: flex !important;
        flex-wrap: wrap;
        margin-left: -8px;
        margin-right: -8px;
    }
    .store-data-wrapper[data-active-view="grid"] .store-grid-view > [class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
    }
    .store-grid-card {
        border: 1px solid #dee2e6;
        background: #fff;
        height: 100%;
        padding: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .store-grid-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        gap: 8px;
    }
    .store-grid-num {
        font-size: 0.75rem;
        color: #999;
        font-weight: 700;
    }
    .store-grid-card-title {
        font-weight: 700;
        font-size: 1rem;
        color: #222;
        margin-bottom: 10px;
        line-height: 1.35;
        word-break: break-word;
    }
    .store-grid-card-body {
        border-top: 1px solid #f0f0f0;
        padding-top: 8px;
    }
    .store-grid-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.84rem;
        padding: 6px 0;
        border-bottom: 1px dashed #f0f0f0;
    }
    .store-grid-meta-row:last-child {
        border-bottom: none;
    }
    .store-grid-meta-label {
        color: #888;
        flex-shrink: 0;
    }
    .store-grid-meta-value {
        text-align: right;
        color: #333;
        word-break: break-word;
    }
    .store-grid-empty {
        border: 1px dashed #dee2e6;
        padding: 2rem 1rem;
        text-align: center;
        color: #6c757d;
        width: 100%;
    }

    @media (max-width: 767.98px) {
        .content.mt-3 { margin-top: 0.5rem !important; }
        .card-body { padding: 12px; }
        .card-header {
            flex-wrap: wrap;
            gap: 8px;
        }
        .card-header .btn {
            width: 100%;
        }
        .store-menu .list-group-item {
            min-width: 76px;
            font-size: 0.7rem;
            padding: 8px 6px;
        }
        .store-menu .list-group-item i {
            display: block;
            font-size: 1rem;
            margin-bottom: 3px;
        }
        .store-section-toolbar .btn-primary-custom,
        .store-section-toolbar .btn-sm {
            width: 100%;
        }
        .store-view-toggle {
            width: 100%;
        }
        .store-view-toggle .btn {
            flex: 1;
        }
        .modal-dialog {
            margin: 0.5rem;
        }
        .store-table-compact {
            font-size: 0.85rem;
        }
        .breadcrumbs .page-title h1 {
            font-size: 1.25rem;
        }
    }

    @media (min-width: 768px) {
        .store-menu {
            flex-wrap: wrap;
        }
        .store-menu .list-group-item {
            min-width: 0;
        }
    }
    .store-report-filters .form-group label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #444;
    }
    .store-report-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 1.25rem;
    }
    /* Dashboard has 10 stats — keep rows balanced (5 + 5) instead of 7 + 3 */
    #storeDashboardSummary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    @media (min-width: 576px) {
        #storeDashboardSummary {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 768px) {
        #storeDashboardSummary {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
    .store-report-stat {
        border: 1px solid #e8d4d4;
        background: #fffafa;
        padding: 14px 12px;
        text-align: center;
        min-height: 88px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .store-report-stat .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #940000;
        line-height: 1.2;
    }
    .store-report-stat .stat-label {
        font-size: 0.78rem;
        color: #666;
        margin-top: 4px;
        line-height: 1.25;
        word-break: break-word;
    }
    .store-dashboard-quick {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1rem;
    }
    .store-dashboard-quick .btn {
        border-radius: 0;
    }
    .store-dashboard-panels {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 1rem;
    }
    .store-dashboard-panel {
        border: 1px solid #e8d4d4;
        background: #fff;
    }
    .store-dashboard-panel-head {
        background: #fffafa;
        border-bottom: 1px solid #e8d4d4;
        padding: 10px 14px;
        font-weight: 700;
        color: #940000;
        font-size: 0.9rem;
    }
    .store-dashboard-panel-body {
        padding: 12px 14px;
    }
    .store-dashboard-list-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px dashed #eee;
        font-size: 0.85rem;
    }
    .store-dashboard-list-item:last-child {
        border-bottom: none;
    }
    .store-dashboard-view-more-wrap {
        text-align: center;
        padding-top: 6px;
        border-top: 1px dashed #eee;
        margin-top: 4px;
    }
    .store-dashboard-view-more-wrap .btn-link {
        color: #940000;
        font-weight: 600;
        font-size: 0.82rem;
    }
    .store-dashboard-empty {
        color: #888;
        font-size: 0.85rem;
        text-align: center;
        padding: 16px 8px;
    }
    .store-coming-soon-panel {
        text-align: center;
        padding: 48px 24px;
        border: 2px dashed #e8d4d4;
        background: #fffafa;
        color: #666;
    }
    .store-coming-soon-panel i {
        color: #940000;
        margin-bottom: 12px;
    }
    .store-report-table thead th {
        background: #f8f9fa;
        font-size: 0.82rem;
        vertical-align: middle;
        text-align: center;
    }
    .store-report-table tbody td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .store-report-table .item-name-cell {
        font-weight: 600;
        color: #333;
        background: #fff;
    }
    .store-report-qty-head {
        text-align: center;
        font-weight: 600;
    }
    .store-report-subhead {
        font-size: 0.78rem;
        font-weight: 600;
        color: #555;
    }
    .store-report-placeholder {
        border: 1px dashed #dee2e6;
        padding: 2.5rem 1rem;
        text-align: center;
        color: #888;
    }
    .pkg-chain-card {
        border: 1px solid #e8d4d4;
        background: #fff;
        padding: 12px;
        margin-bottom: 12px;
    }
    .pkg-chain-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-weight: 600;
        color: #940000;
    }
    .pkg-level-block {
        border-left: 3px solid #f0d0d0;
        padding-left: 12px;
        margin-top: 10px;
    }
    .pkg-level-block.pkg-level-root {
        border-left-color: #940000;
        padding-left: 0;
        border-left: none;
        margin-top: 0;
    }
    .pkg-level-title {
        font-size: 0.78rem;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .stock-hierarchy-title {
        font-weight: 600;
        color: #940000;
        margin-bottom: 6px;
    }
    .stock-hierarchy-line {
        font-size: 0.88rem;
        margin-bottom: 3px;
    }
    .stock-batch-card {
        background: #fafafa;
        border: 1px solid #e8d4d4;
        border-radius: 0;
    }
    .stock-batch-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .stock-total-items-dashboard {
        background: #fffafa;
        border: 2px solid #940000;
    }
    .stock-total-items-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        padding: 4px 0;
        border-bottom: 1px dashed #eee;
    }
    .stock-total-items-row:last-child {
        border-bottom: none;
        font-weight: 700;
        color: #940000;
        font-size: 1rem;
        padding-top: 8px;
        margin-top: 4px;
        border-top: 2px solid #940000;
    }
    .issue-take-target-list {
        max-height: 280px;
        overflow-y: auto;
        background: #fffafa;
    }
    .issue-take-target-option .custom-control-label {
        width: 100%;
        cursor: pointer;
    }
    .issue-take-target-option .issue-take-chain-indent {
        color: #940000;
        font-weight: 600;
        margin-right: 4px;
    }
    .issue-take-target-option .issue-take-main-badge {
        font-size: 0.68rem;
        background: #940000;
        color: #fff;
        padding: 2px 6px;
        margin-left: 6px;
        vertical-align: middle;
    }
    .issue-take-avail-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        background: #e8f4fd;
        color: #0c5460;
        border: 1px solid #bee5eb;
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 6px;
        vertical-align: middle;
    }
    .issue-take-capacity-line {
        font-size: 12px;
        color: #6c757d;
    }
    .issue-hier-unit {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }
    .issue-hier-unit.is-checked {
        border-color: #940000;
        background: #fffafa;
    }
    .issue-hier-unit-head .custom-control-label {
        cursor: pointer;
        width: 100%;
    }
    .issue-hier-unit-body {
        margin-top: 10px;
        padding: 10px 12px;
        border-left: 3px solid #940000;
        background: #f8f9fa;
        border-radius: 0 6px 6px 0;
    }
    .issue-hier-mode-row {
        margin-bottom: 8px;
    }
    .issue-hier-child-info {
        font-size: 12px;
        color: #495057;
        margin-bottom: 8px;
    }
    .issue-hier-qty-error {
        font-size: 12px;
        margin-top: 4px;
    }
    .issue-hier-summary-bar {
        font-size: 12px;
        color: #495057;
        margin-bottom: 10px;
        padding: 8px 10px;
        background: #e8f4fd;
        border: 1px solid #bee5eb;
        border-radius: 4px;
    }
    #issueTakeLivePreview, #incidentTakeLivePreview {
        font-size: 13px;
    }
    .issue-take-row-qty {
        margin-left: 1.75rem;
        max-width: 220px;
    }
    .issue-take-row-qty .form-control-sm {
        max-width: 120px;
    }
    .stock-label-breakdown {
        font-size: 0.78rem;
        color: #666;
        margin-top: 2px;
    }
    .store-list-view .dataTables_wrapper {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .store-list-view .dataTables_wrapper .dataTables_filter input,
    .store-list-view .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 0 !important;
        padding: 4px 8px;
        min-height: 34px;
    }
    .store-list-view .dataTables_wrapper .dataTables_filter input:focus,
    .store-list-view .dataTables_wrapper .dataTables_length select:focus {
        border-color: #940000;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }
    .store-list-view .dataTables_wrapper .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
    }
    .store-list-view .dataTables_wrapper .page-link {
        color: #940000;
        border-radius: 0 !important;
    }
    .store-list-view .dataTables_wrapper .page-link:hover {
        color: #b30000;
    }
    .store-list-view .dataTables_info,
    .store-list-view .dataTables_length,
    .store-list-view .dataTables_filter,
    .store-list-view .dataTables_paginate {
        font-size: 0.85rem;
        margin-top: 8px;
        margin-bottom: 8px;
    }
    #inventoryReportResults .dataTables_wrapper .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
    }
    #inventoryReportResults .dataTables_wrapper .page-link {
        color: #940000;
        border-radius: 0 !important;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title"><h1>Manage Store</h1></div>
        </div>
    </div>
</div>

{{-- Store AJAX loading UI --}}
<div id="storeAjaxBar" class="store-ajax-bar" aria-hidden="true">
    <div class="store-ajax-bar-fill"></div>
</div>
<div id="storeAjaxOverlay" class="store-ajax-overlay" aria-hidden="true">
    <div class="store-ajax-card">
        <div class="store-ajax-orbit">
            <span></span><span></span><span></span>
        </div>
        <div class="store-ajax-text" id="storeAjaxText">Working...</div>
        <div class="store-ajax-sub">Please wait</div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
            <strong><i class="fa fa-archive me-1"></i> {{ $storeRole === 'admin' ? 'Store Management' : 'Inventory' }}</strong>
            <div class="d-flex flex-wrap" style="gap:8px;">
                @if(!empty($storePriv['register_items']))
                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#addItemModal">
                    <i class="fa fa-plus"></i> Add Item
                </button>
                @endif
                @if($canManageActors)
                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#inventoryActorsModal">
                    <i class="fa fa-users"></i> Actors
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div id="storeAlert" class="alert d-none"></div>

            <div class="store-menu-wrap">
                    <div class="list-group list-group-horizontal store-menu">
                        <a class="list-group-item active flex-fill text-center" data-section="section-dashboard">
                            <i class="fa fa-dashboard"></i> Dashboard
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-items" @if(empty($storePriv['register_items'])) style="display:none;" @endif>
                            <i class="fa fa-cubes"></i> Items
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-stock" @if(empty($storePriv['manage_stock'])) style="display:none;" @endif>
                            <i class="fa fa-warehouse"></i> Item Stock
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-requisitions" @if(empty($storePriv['issue_items']) && empty($storePriv['approve_requests'])) style="display:none;" @endif>
                            <i class="fa fa-hand-o-right"></i> Requisition
                            @if(($unreadRequestCount ?? 0) > 0)
                                <span class="store-menu-badge" id="requisitionMenuBadge">{{ $unreadRequestCount }}</span>
                            @else
                                <span class="store-menu-badge" id="requisitionMenuBadge" style="display:none;">0</span>
                            @endif
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-damaged" @if(empty($storePriv['manage_incidents'])) style="display:none;" @endif>
                            <i class="fa fa-warning"></i> Damaged Item
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-lost" @if(empty($storePriv['manage_incidents'])) style="display:none;" @endif>
                            <i class="fa fa-search"></i> Lost Item
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-categories" @if(empty($storePriv['manage_categories'])) style="display:none;" @endif>
                            <i class="fa fa-tags"></i> Categories
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-measurements" @if(empty($storePriv['manage_units'])) style="display:none;" @endif>
                            <i class="fa fa-balance-scale"></i> Units
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-packages" @if(empty($storePriv['manage_packages'])) style="display:none;" @endif>
                            <i class="fa fa-gift"></i> Packages
                        </a>
                        <a class="list-group-item flex-fill text-center" data-section="section-report" @if(empty($storePriv['view_reports'])) style="display:none;" @endif>
                            <i class="fa fa-bar-chart"></i> Report
                        </a>
                    </div>
            </div>

            @php
                $storeViewToggle = function ($tableId) {
                    return '<div class="btn-group btn-group-sm store-view-toggle" data-table="' . $tableId . '">
                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="fa fa-list"></i> <span class="d-none d-sm-inline">List</span></button>
                        <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="fa fa-th"></i> <span class="d-none d-sm-inline">Grid</span></button>
                    </div>';
                };
                $storePdfBtn = function ($section) {
                    return '<button type="button" class="btn btn-sm btn-danger-custom store-download-pdf-btn" data-section="' . $section . '" title="Download PDF"><i class="fa fa-file-pdf-o"></i> <span class="d-none d-sm-inline">PDF</span></button>';
                };
            @endphp

            {{-- Dashboard --}}
            <div id="section-dashboard" class="store-section active">
                <div class="form-group mb-3">
                    <label for="dashboardCategoryFilter" class="font-weight-bold">Item Category</label>
                    <select class="form-control w-100" id="dashboardCategoryFilter">
                        <option value="" selected>
                            @if(($storeAccess['category_scope'] ?? 'all') === 'specific')
                                All My Categories
                            @else
                                All Categories
                            @endif
                        </option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->categoryID }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Store Dashboard <span id="dashboardFilterBadge" class="text-muted small font-weight-normal"></span></div>
                        <p class="text-muted small mb-0">Overview of inventory, stock, requisitions, and returns. Updated: <span id="dashboardGeneratedAt">{{ $dashboardStats['generated_at'] ?? '—' }}</span></p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('dashboard') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" id="btnRefreshDashboard">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="store-dashboard-quick">
                    <button type="button" class="btn btn-outline-secondary btn-sm store-dash-goto" data-section="section-stock"><i class="fa fa-warehouse"></i> Add Stock</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm store-dash-goto" data-section="section-requisitions"><i class="fa fa-hand-o-right"></i> Issue Item</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm store-dash-goto" data-section="section-items"><i class="fa fa-cubes"></i> Register Item</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm store-dash-goto" data-section="section-report"><i class="fa fa-bar-chart"></i> Reports</button>
                </div>

                <div class="store-report-summary" id="storeDashboardSummary"></div>

                <div class="store-dashboard-panels">
                    <div class="store-dashboard-panel">
                        <div class="store-dashboard-panel-head"><i class="fa fa-clock-o"></i> Recent Requisitions</div>
                        <div class="store-dashboard-panel-body" id="dashboardRecentRequisitions"></div>
                    </div>
                    <div class="store-dashboard-panel">
                        <div class="store-dashboard-panel-head"><i class="fa fa-exclamation-circle"></i> Overdue Returns</div>
                        <div class="store-dashboard-panel-body" id="dashboardOverdueList"></div>
                    </div>
                    <div class="store-dashboard-panel">
                        <div class="store-dashboard-panel-head" id="dashboardCategoryPanelTitle"><i class="fa fa-tags"></i> Items by Category</div>
                        <div class="store-dashboard-panel-body" id="dashboardCategoryBreakdown"></div>
                    </div>
                    <div class="store-dashboard-panel">
                        <div class="store-dashboard-panel-head"><i class="fa fa-line-chart"></i> Top Issued This Month</div>
                        <div class="store-dashboard-panel-body" id="dashboardTopIssued"></div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div id="section-items" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Registered Items</div>
                        <p class="text-muted small mb-0">Items available in your inventory catalogue. Register items here before adding them to stock.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('items') !!}
                        {!! $storeViewToggle('itemsTable') !!}
                    </div>
                </div>
                <div class="store-data-wrapper" data-table-id="itemsTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped store-table-compact" id="itemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Measurement Type</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            @php
                                $itemUnits = $item->units->pluck('unit_name')->all();
                                $allSpecs = $item->packageSpecs;
                                $fmtQty = function ($n) {
                                    return rtrim(rtrim(number_format((float) $n, 4, '.', ''), '0'), '.');
                                };
                                $buildSpecPayload = null;
                                $buildSpecPayload = function ($spec) use (&$buildSpecPayload, $allSpecs) {
                                    $hasSub = (bool) $spec->has_sub_package;
                                    $node = [
                                        'packageID' => (int) $spec->packageID,
                                        'quantity' => (float) $spec->quantity,
                                        'content_quantity' => $spec->content_quantity !== null ? (float) $spec->content_quantity : null,
                                        'has_sub_package' => $hasSub,
                                        'unitID' => $spec->unitID ? (int) $spec->unitID : null,
                                    ];
                                    if ($hasSub) {
                                        $child = $allSpecs->first(fn ($c) => (int) $c->parent_specID === (int) $spec->specID);
                                        if ($child) {
                                            $node['child'] = $buildSpecPayload($child);
                                        }
                                    }
                                    return $node;
                                };
                                $buildSpecLines = null;
                                $buildSpecLines = function ($spec, $depth = 0) use (&$buildSpecLines, $allSpecs, $fmtQty) {
                                    $lines = [];
                                    $pad = str_repeat('— ', $depth);
                                    if ($spec->has_sub_package) {
                                        $child = $allSpecs->first(fn ($c) => (int) $c->parent_specID === (int) $spec->specID);
                                        $childName = $child?->package?->package_name ?? 'sub-package';
                                        $childQty = $child ? $fmtQty($child->quantity) : '0';
                                        $lines[] = $pad . ($spec->package->package_name ?? 'Package') . ' has ' . $childQty . ' ' . $childName;
                                        if ($child) {
                                            $lines = array_merge($lines, $buildSpecLines($child, $depth + 1));
                                        }
                                    } else {
                                        $items = $spec->content_quantity !== null ? $spec->content_quantity : $spec->quantity;
                                        $lines[] = $pad . ($spec->package->package_name ?? 'Package') . ' has ' . $fmtQty($items) . ' ' . ($spec->unit->unit_name ?? 'item');
                                    }
                                    return $lines;
                                };
                                $itemPackages = [];
                                if ($item->has_packages && $allSpecs->count()) {
                                    $roots = $allSpecs->filter(fn ($s) => $s->parent_specID === null);
                                    foreach ($roots as $root) {
                                        $itemPackages = array_merge($itemPackages, $buildSpecLines($root));
                                    }
                                }
                                $itemViewMore = [
                                    'title' => $item->item_name,
                                    'fields' => [
                                        ['label' => 'Category', 'value' => $item->category->category_name ?? '—'],
                                        ['label' => 'Measurement Type', 'value' => $item->measurementType->type_name ?? '—'],
                                        ['label' => 'Units of Measure', 'value' => $itemUnits, 'type' => 'tags'],
                                        ['label' => 'Has Packages', 'value' => $item->has_packages ? 'Yes' : 'No'],
                                        ['label' => 'Package Details', 'value' => $itemPackages, 'type' => 'tags'],
                                    ],
                                ];
                            @endphp
                            <tr data-item-id="{{ $item->itemID }}" data-view-more='@json($itemViewMore)'>
                                <td>{{ $index + 1 }}</td>
                                <td class="item-name font-weight-bold">{{ $item->item_name }}</td>
                                <td class="item-category">{{ $item->category->category_name ?? '-' }}</td>
                                <td class="item-type">{{ $item->measurementType->type_name ?? '-' }}</td>
                                <td class="store-actions-cell">
                                    <div class="dropdown store-action-dropdown">
                                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                                            <button type="button" class="dropdown-item btn-edit-item"
                                                data-id="{{ $item->itemID }}"
                                                data-name="{{ $item->item_name }}"
                                                data-category="{{ $item->categoryID }}"
                                                data-type="{{ $item->measurement_typeID }}"
                                                data-units="{{ $item->units->pluck('unitID')->implode(',') }}"
                                                data-has-packages="{{ $item->has_packages ? '1' : '0' }}"><i class="fa fa-edit text-primary"></i> Edit</button>
                                            <button type="button" class="dropdown-item text-danger btn-delete-item" data-id="{{ $item->itemID }}"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Item Stock --}}
            <div id="section-stock" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Item Stock</div>
                        <p class="text-muted small mb-0">Physical stock in the store. Each labelled unit or package is tracked individually for requisitions.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('stock') !!}
                        {!! $storeViewToggle('stockTable') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" data-toggle="collapse" data-target="#addItemStockModal" aria-expanded="false" aria-controls="addItemStockModal">
                            <i class="fa fa-plus"></i> Add Item in Stock
                        </button>
                    </div>
                </div>
                {{-- Inline (expand/collapse) Add Item in Stock form --}}
                <div class="collapse mt-3" id="addItemStockModal">
                    <div class="modal-dialog modal-xl" style="margin:0;max-width:none;">
                        <div class="modal-content">
                            <div class="modal-header bg-primary-custom text-white">
                                <h5 class="modal-title"><i class="fa fa-warehouse"></i> Add Item in Stock</h5>
                                <button type="button" class="close text-white" data-toggle="collapse" data-target="#addItemStockModal"><span>&times;</span></button>
                            </div>
                            <form id="addItemStockForm">
                                <div class="modal-body">
                                    <div class="stock-modal-scroll" id="addItemStockScroll">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Select Item <span class="text-danger">*</span></label>
                                            <select class="form-control stock-form-select" id="stockItemSelect" required>
                                                <option value="">Search or select item...</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->itemID }}">{{ $item->item_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="stockItemInfo" class="alert alert-light border small py-2" style="display:none;">
                                            <strong>Measurement Type:</strong> <span id="stockMeasurementType">—</span>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">Store Method <span class="text-danger">*</span></label>
                                            <select class="form-control" id="stockStoreMethod" required>
                                                <option value="">Select store method...</option>
                                                <option value="package">Package</option>
                                                <option value="unit">Unit Measure</option>
                                            </select>
                                        </div>

                                        <div id="stockPackageError" class="alert alert-danger py-2 small" style="display:none;"></div>

                                        <div id="stockPackageSection" style="display:none;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="font-weight-bold mb-0">Package Receipts <span class="text-danger">*</span></label>
                                            </div>
                                            <p class="text-muted small mb-2">Add each package type received (e.g. 50 Sets and 60 Pieces). Total items are calculated automatically from each package chain.</p>
                                            <div id="stockPackageBatches"></div>
                                            <div id="stockTotalItemsDashboard" class="stock-total-items-dashboard border p-3 mb-3" style="display:none;">
                                                <div class="stock-hierarchy-title"><i class="fa fa-calculator"></i> Total Items Summary</div>
                                                <div id="stockTotalItemsBody"></div>
                                            </div>
                                            <div class="stock-sticky-actions">
                                                <button type="button" class="btn btn-sm btn-primary-custom" id="btnAddStockPackageBatch">
                                                    <i class="fa fa-plus"></i> Add Another Package
                                                </button>
                                            </div>
                                        </div>

                                        <div id="stockUnitSection" style="display:none;">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Unit of Measure <span class="text-danger">*</span></label>
                                                <select class="form-control stock-form-select" id="stockUnitSelect">
                                                    <option value="">Select unit...</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div id="stockQuantitySection" style="display:none;">
                                            <div class="form-group">
                                                <label class="font-weight-bold" id="stockQuantityLabel">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="stockQuantity" min="1" max="500" placeholder="How many?">
                                                <small class="text-muted" id="stockQuantityHint"></small>
                                            </div>
                                        </div>

                                        <div id="stockLabelsSection" style="display:none;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="font-weight-bold mb-0">Labels <span class="text-danger">*</span></label>
                                                <label class="mb-0 small d-flex align-items-center" style="gap:8px;">
                                                    <input type="checkbox" id="stockAutoLabels" checked>
                                                    Auto label
                                                </label>
                                            </div>
                                            <p class="text-muted small mb-2">Labels zimejazwa automatically (kwa idadi). Uki-hitaji kubadilisha jina, edit tu input husika.</p>
                                            <div id="stockLabelsContainer"></div>
                                        </div>

                                        <div id="stockRequisitionSection" style="display:none;">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold">Requisition Status <span class="text-danger">*</span></label>
                                                <select class="form-control" id="stockRequisitionStatus">
                                                    <option value="">Select...</option>
                                                    <option value="can_return">Can Return — item can be returned after use (e.g. books, laptops)</option>
                                                    <option value="cant_return">Can't Return — item is consumed on use (e.g. chalk box, supplies)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-toggle="collapse" data-target="#addItemStockModal">Cancel</button>
                                    <button type="submit" class="btn btn-primary-custom" id="btnSaveStock" disabled>
                                        <i class="fa fa-save"></i> Save Stock
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="store-data-wrapper" data-table-id="stockTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped store-table-compact" id="stockTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Item</th>
                                <th>Package / Unit</th>
                                <th>Available</th>
                                <th>Requisition</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockEntries as $index => $entry)
                            @php
                                $availableUnits = $entry->stockUnits->filter(fn ($u) => (float) ($u->remaining_items ?? ($u->stock_status === 'in_stock' ? 1 : 0)) > 0);
                                $fmtStockQty = fn ($n) => rtrim(rtrim(number_format((float) $n, 4, '.', ''), '0'), '.');
                                $inStockLabels = $availableUnits->map(function ($u) use ($entry, $fmtStockQty) {
                                    $label = $u->label;
                                    if ($entry->store_method === 'package') {
                                        $label .= ' (' . $fmtStockQty($u->remaining_items ?? 0) . '/' . $fmtStockQty($u->initial_items ?? 0)
                                            . ' ' . ($entry->unit->unit_name ?? 'item') . ')';
                                    }
                                    return $label;
                                })->values()->all();
                                $issuedLabels = $entry->stockUnits
                                    ->filter(fn ($u) => (float) ($u->remaining_items ?? 0) < (float) ($u->initial_items ?? 1))
                                    ->map(fn ($u) => $u->label . ' (used ' . $fmtStockQty((float) ($u->initial_items ?? 0) - (float) ($u->remaining_items ?? 0)) . ')')
                                    ->values()->all();
                                $availableQty = $availableUnits->count();
                                $remainingItems = (float) $availableUnits->sum(fn ($u) => (float) ($u->remaining_items ?? 0));
                                $issuedQty = $entry->stockUnits->count() - $availableQty;
                                $pkgUnitLabel = '-';
                                if ($entry->store_method === 'package' && $entry->package) {
                                    $pkgUnitLabel = $entry->package->package_name;
                                    if ($entry->content_per_unit) {
                                        $pkgUnitLabel .= ' (' . $fmtStockQty($entry->content_per_unit)
                                            . ' ' . ($entry->unit->unit_name ?? '') . ')';
                                    }
                                } else {
                                    $pkgUnitLabel = $entry->unit->unit_name ?? '-';
                                }
                                $stockViewMore = [
                                    'title' => ($entry->item->item_name ?? 'Stock') . ' — Stock Details',
                                    'layout' => 'widgets',
                                    'sections' => [
                                        [
                                            'title' => 'Stock Summary',
                                            'widgets' => array_filter([
                                                ['label' => 'Item', 'value' => $entry->item->item_name ?? '—'],
                                                ['label' => 'Store Method', 'value' => $entry->store_method === 'package' ? 'Package' : 'Unit Measure'],
                                                ['label' => 'Package / Unit', 'value' => $pkgUnitLabel],
                                                ['label' => 'Available Labels', 'value' => (string) $availableQty, 'danger' => $availableQty === 0],
                                                $entry->store_method === 'package' ? ['label' => 'Items Remaining', 'value' => $fmtStockQty($remainingItems)] : null,
                                                ['label' => 'Labels Touched', 'value' => (string) $issuedQty],
                                                ['label' => 'Total Labels Received', 'value' => (string) $entry->quantity],
                                                ['label' => 'Requisition Status', 'value' => $entry->requisition_status === 'can_return' ? 'Can Return' : "Can't Return"],
                                                ['label' => 'Date Added', 'value' => $entry->created_at ? $entry->created_at->format('d M Y, H:i') : '—'],
                                            ]),
                                        ],
                                        [
                                            'title' => 'Labels',
                                            'widgets' => array_filter([
                                                count($inStockLabels)
                                                    ? ['label' => 'In Stock Labels (' . count($inStockLabels) . ')', 'value' => $inStockLabels, 'type' => 'tags', 'wide' => true]
                                                    : ['label' => 'In Stock Labels', 'value' => 'None', 'wide' => true],
                                                count($issuedLabels)
                                                    ? ['label' => 'Issued Labels (' . count($issuedLabels) . ')', 'value' => $issuedLabels, 'type' => 'tags', 'wide' => true]
                                                    : null,
                                            ]),
                                        ],
                                    ],
                                ];
                            @endphp
                            <tr data-stock-entry-id="{{ $entry->stock_entryID }}" data-view-more='@json($stockViewMore)'>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-weight-bold">{{ $entry->item->item_name ?? '-' }}</td>
                                <td class="small">{{ $pkgUnitLabel }}</td>
                                <td>
                                    <span class="font-weight-bold {{ $availableQty === 0 ? 'text-danger' : '' }}">{{ $availableQty }}</span>
                                    @if($entry->store_method === 'package')
                                        <small class="text-muted d-block">{{ $fmtStockQty($remainingItems) }} item(s) left</small>
                                    @endif
                                    @if($issuedQty > 0)
                                        <small class="text-muted d-block">{{ $issuedQty }} label(s) touched</small>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->requisition_status === 'can_return')
                                        <span class="store-badge-pill store-badge-return-yes">Can Return</span>
                                    @else
                                        <span class="store-badge-pill store-badge-return-no">Can't Return</span>
                                    @endif
                                </td>
                                <td class="store-actions-cell">
                                    <div class="dropdown store-action-dropdown">
                                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                                            <button type="button" class="dropdown-item text-danger btn-delete-stock" data-id="{{ $entry->stock_entryID }}"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Requisition --}}
            <div id="section-requisitions" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Requisition
                            @if(($unreadRequestCount ?? 0) > 0)
                                <span class="store-menu-badge" id="itemRequestsUnreadBadge">{{ $unreadRequestCount }} new</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0">Issue store items to teachers, staff, or students. Track returns and expected return dates.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('requisitions') !!}
                        {!! $storeViewToggle('requisitionsTable') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" data-toggle="modal" data-target="#issueItemModal" @if(empty($storePriv['issue_items'])) style="display:none;" @endif>
                            <i class="fa fa-hand-o-right"></i> Issue Item
                        </button>
                    </div>
                </div>

                <div class="store-requisition-filter">
                    <label for="requisitionReturnFilter">Filter by Item Type</label>
                    <select class="form-control w-100" id="requisitionReturnFilter">
                        <option value="all" selected>All</option>
                        <option value="can_return">Can Return</option>
                        <option value="cant_return">Can't Return</option>
                    </select>
                </div>
                <div class="store-data-wrapper" data-table-id="requisitionsTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped store-table-compact" id="requisitionsTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Recipient</th>
                                <th>Item</th>
                                <th>Package / Unit</th>
                                <th>Return Status</th>
                                <th>Expected Return</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $requisitionRowNum = 0; @endphp
                            @if(!empty($storePriv['approve_requests']))
                                @foreach($itemRequests ?? [] as $req)
                                    @php $requisitionRowNum++; @endphp
                                    @include('Admin.partials.store_requisition_request_row', ['req' => $req, 'rowNum' => $requisitionRowNum])
                                @endforeach
                                @foreach($recentReviewedRequests ?? [] as $req)
                                    @php $requisitionRowNum++; @endphp
                                    @include('Admin.partials.store_requisition_request_row', ['req' => $req, 'rowNum' => $requisitionRowNum])
                                @endforeach
                            @endif
                            @foreach($requisitions as $req)
                            @php $requisitionRowNum++; @endphp
                            @php
                                $recipientTypeLabel = match($req->recipient_type) {
                                    'teacher' => 'Teacher',
                                    'staff' => 'Staff',
                                    'student' => 'Student',
                                    default => ucfirst($req->recipient_type),
                                };
                                $recipientClassName = ($req->recipient_type === 'student')
                                    ? (($studentClassMap[$req->recipient_id] ?? null) ?: null)
                                    : null;
                                $isOverdue = false;
                                $returnStatusHtml = '<span class="text-muted">—</span>';
                                $expectedReturnHtml = '<span class="text-muted">—</span>';

                                if ($req->requisition_status === 'can_return') {
                                    if ($req->status === 'returned') {
                                        $returnCond = $req->return_condition === 'bad' ? 'damaged' : $req->return_condition;
                                        if ($returnCond === 'damaged') {
                                            $returnStatusHtml = '<span class="store-badge-pill store-badge-defective">Returned — Damaged</span>';
                                        } elseif ($returnCond === 'lost') {
                                            $returnStatusHtml = '<span class="store-badge-pill store-badge-overdue">Returned — Lost</span>';
                                        } else {
                                            $returnStatusHtml = '<span class="store-badge-pill store-badge-return-yes">Returned — Good</span>';
                                        }
                                    } else {
                                        $isOverdue = $req->expected_return_date && $req->expected_return_date->isPast();
                                        $returnStatusHtml = $isOverdue
                                            ? '<span class="store-badge-pill store-badge-overdue">Not Returned — Overdue</span>'
                                            : '<span class="store-badge-pill store-badge-pending">Not Returned — Pending</span>';
                                    }
                                    if ($req->expected_return_date) {
                                        $expectedReturnHtml = '<span class="' . ($isOverdue && $req->status === 'issued' ? 'req-return-overdue' : '') . '">'
                                            . $req->expected_return_date->format('d M Y') . '</span>';
                                    }
                                } else {
                                    $returnStatusHtml = '<span class="store-badge-pill store-badge-return-no">Can\'t Return</span>';
                                }

                                $returnStatusText = '—';
                                if ($req->requisition_status === 'can_return') {
                                    if ($req->status === 'returned') {
                                        $rc = $req->return_condition === 'bad' ? 'damaged' : $req->return_condition;
                                        $returnStatusText = match ($rc) {
                                            'damaged' => 'Returned — Damaged',
                                            'lost' => 'Returned — Lost',
                                            default => 'Returned — Good',
                                        };
                                    } else {
                                        $returnStatusText = $isOverdue ? 'Not Returned — Overdue' : 'Not Returned — Pending';
                                    }
                                } else {
                                    $returnStatusText = "Can't Return";
                                }

                                $reqViewMore = [
                                    'title' => ($req->item->item_name ?? 'Item') . ' — Requisition',
                                    'layout' => 'widgets',
                                    'sections' => [
                                        [
                                            'title' => 'Recipient & Issue',
                                            'widgets' => array_filter([
                                                ['label' => 'Recipient', 'value' => $req->recipient_name],
                                                ['label' => 'Type', 'value' => $recipientTypeLabel],
                                                $recipientClassName ? ['label' => 'Class', 'value' => $recipientClassName] : null,
                                                ['label' => 'Item', 'value' => $req->item->item_name ?? '—'],
                                                ['label' => 'Label', 'value' => $req->label],
                                                ['label' => 'Package / Unit', 'value' => $req->package_unit_display ?? '—'],
                                                ['label' => 'Taken', 'value' => $req->take_display ?: ($req->package_unit_display ?? '—')],
                                                ['label' => 'Quantity Taken', 'value' => (string) ($req->quantity_taken ?: 1)],
                                                ['label' => 'Return Policy', 'value' => $req->requisition_status === 'can_return' ? 'Can Return' : "Can't Return"],
                                                ['label' => 'Issued By', 'value' => $req->issued_by ?? '—'],
                                                ['label' => 'Issued At', 'value' => $req->issued_at ? $req->issued_at->format('d M Y, H:i') : '—'],
                                                ['label' => 'Notes', 'value' => $req->notes ?: '—'],
                                            ]),
                                        ],
                                        [
                                            'title' => 'Return Details',
                                            'widgets' => array_filter([
                                                $req->requisition_status === 'can_return' ? ['label' => 'Expected Return', 'value' => $req->expected_return_date ? $req->expected_return_date->format('d M Y') : '—', 'danger' => $isOverdue && $req->status === 'issued'] : null,
                                                ['label' => 'Return Status', 'value' => $returnStatusText],
                                                $req->status === 'returned' ? ['label' => 'Quantity Returned', 'value' => (string) ($req->quantity_returned ?: $req->quantity_taken ?: 1)] : null,
                                                $req->status === 'returned' ? ['label' => 'Returned On', 'value' => $req->returned_at ? $req->returned_at->format('d M Y') : '—'] : null,
                                                $req->status === 'returned' && $req->return_description ? ['label' => 'Return Description', 'value' => $req->return_description] : null,
                                                $req->status === 'returned' ? ['label' => 'Return Status', 'value' => match ($req->return_condition === 'bad' ? 'damaged' : $req->return_condition) {
                                                    'damaged' => 'Damaged',
                                                    'lost' => 'Lost',
                                                    default => 'Good',
                                                }] : null,
                                                in_array($req->return_condition, ['bad', 'damaged', 'lost'], true) ? ['label' => 'Reason', 'value' => $req->bad_description ?: '—', 'danger' => true] : null,
                                                in_array($req->return_condition, ['bad', 'damaged', 'lost'], true) ? ['label' => 'Responsible Person', 'value' => $req->recipient_name . ' (' . $recipientTypeLabel . ($recipientClassName ? ' · ' . $recipientClassName : '') . ')', 'danger' => true] : null,
                                            ]),
                                        ],
                                    ],
                                ];
                            @endphp
                            <tr data-requisition-id="{{ $req->requisitionID }}"
                                data-return-policy="{{ $req->requisition_status }}"
                                data-view-more='@json($reqViewMore)'>
                                <td>{{ $requisitionRowNum }}</td>
                                <td>
                                    <span class="font-weight-bold">{{ $req->recipient_name }}</span>
                                    <br><small class="text-muted">{{ $recipientTypeLabel }}</small>
                                    @if($recipientClassName)
                                        <br><small class="text-muted">Class: {{ $recipientClassName }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $req->item->item_name ?? '-' }}
                                    <br><small class="text-muted">Label: {{ $req->label }}</small>
                                    @if($req->take_display)
                                        <br><small class="text-primary-custom">{{ $req->take_display }}</small>
                                    @endif
                                </td>
                                <td class="small">{{ $req->package_unit_display ?? '-' }}</td>
                                <td>{!! $returnStatusHtml !!}</td>
                                <td>{!! $expectedReturnHtml !!}</td>
                                <td class="store-actions-cell">
                                    <div class="dropdown store-action-dropdown">
                                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                                            @if($req->status === 'issued' && $req->requisition_status === 'can_return')
                                            <button type="button" class="dropdown-item btn-return-requisition"
                                                data-id="{{ $req->requisitionID }}"
                                                data-recipient="{{ $req->recipient_name }}{{ $recipientClassName ? ' · ' . $recipientClassName : '' }}"
                                                data-item="{{ $req->item->item_name ?? '' }}"
                                                data-label="{{ $req->label }}"
                                                data-taken="{{ $req->take_display ?: ($req->quantity_taken ?: 1) }}">
                                                <i class="fa fa-undo text-success"></i> Return Item
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Damaged Items --}}
            <div id="section-damaged" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Damaged Items</div>
                        <p class="text-muted small mb-0">Record items damaged by teachers, students, staff, or others. Track payment status and resolve with cash or replacement.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('damaged') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" id="btnAddDamagedItem">
                            <i class="fa fa-plus"></i> Add Damaged Item
                        </button>
                    </div>
                </div>
                <div class="store-data-wrapper" data-table-id="damagedItemsTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped store-table-compact" id="damagedItemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Damaged By</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Lost Items --}}
            <div id="section-lost" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Lost Items</div>
                        <p class="text-muted small mb-0">Record items that were lost. Same workflow as damaged items — track responsible person, reason, and resolution.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('lost') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" id="btnAddLostItem">
                            <i class="fa fa-plus"></i> Add Lost Item
                        </button>
                    </div>
                </div>
                <div class="store-data-wrapper" data-table-id="lostItemsTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped store-table-compact" id="lostItemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Lost By</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Categories --}}
            <div id="section-categories" class="store-section">
                <div class="store-section-toolbar">
                    <div class="section-title">Item Categories</div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('categories') !!}
                        {!! $storeViewToggle('categoriesTable') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" data-toggle="modal" data-target="#addCategoryModal">
                            <i class="fa fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>
                <div class="store-data-wrapper" data-table-id="categoriesTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped store-table-compact" id="categoriesTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Category Name</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $index => $cat)
                            @php
                                $catViewMore = [
                                    'title' => $cat->category_name,
                                    'fields' => [
                                        ['label' => 'Category Name', 'value' => $cat->category_name],
                                        ['label' => 'Status', 'value' => $cat->status ?? 'Active'],
                                    ],
                                ];
                            @endphp
                            <tr data-category-id="{{ $cat->categoryID }}" data-view-more='@json($catViewMore)'>
                                <td>{{ $index + 1 }}</td>
                                <td class="cat-name font-weight-bold">{{ $cat->category_name }}</td>
                                <td class="store-actions-cell">
                                    <div class="dropdown store-action-dropdown">
                                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                                            <button type="button" class="dropdown-item btn-edit-category" data-id="{{ $cat->categoryID }}" data-name="{{ $cat->category_name }}"><i class="fa fa-edit text-primary"></i> Edit</button>
                                            <button type="button" class="dropdown-item text-danger btn-delete-category" data-id="{{ $cat->categoryID }}"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Measurement Types --}}
            <div id="section-measurements" class="store-section">
                <div class="store-section-toolbar">
                    <div class="section-title">Measurement Types & Units of Measure</div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('units') !!}
                        {!! $storeViewToggle('measurementTypesTable') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" data-toggle="modal" data-target="#addMeasurementTypeModal">
                            <i class="fa fa-plus"></i> Add Measurement Type
                        </button>
                    </div>
                </div>

                <div class="units-search-bar">
                    <label class="font-weight-bold small mb-2 d-block">Search Measurement Types</label>
                    <div class="search-icon-wrap">
                        <i class="fa fa-search"></i>
                        <input type="text" id="unitMeasureSearch" class="form-control" placeholder="Type to search measurement type...">
                    </div>
                    <div id="unitMeasureSearchCount"></div>
                </div>

                <div class="store-data-wrapper" data-table-id="measurementTypesTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped store-table-compact" id="measurementTypesTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Type Name</th>
                                <th>Units</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($measurementTypes as $index => $type)
                            @php
                                $typeUnitNames = $type->units->pluck('unit_name')->all();
                                $typeViewMore = [
                                    'title' => $type->type_name,
                                    'fields' => [
                                        ['label' => 'Type Name', 'value' => $type->type_name],
                                        ['label' => 'Type', 'value' => $type->is_system ? 'Default (System)' : 'Custom'],
                                        ['label' => 'Units of Measure (' . count($typeUnitNames) . ')', 'value' => $typeUnitNames, 'type' => 'tags'],
                                    ],
                                    'units' => $type->units->map(fn ($u) => [
                                        'unitID' => $u->unitID,
                                        'unit_name' => $u->unit_name,
                                        'abbreviation' => $u->abbreviation,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="measurement-type-row" data-type-id="{{ $type->measurement_typeID }}"
                                data-type-name="{{ strtolower($type->type_name) }}"
                                data-view-more='@json($typeViewMore)'>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="type-name font-weight-bold">{{ $type->type_name }}</span>
                                    @if($type->is_system)
                                        <span class="store-badge-pill store-badge-unit ml-1" style="font-size:10px;">Default</span>
                                    @else
                                        <span class="store-badge-pill store-badge-package ml-1" style="font-size:10px;">Custom</span>
                                    @endif
                                </td>
                                <td><span class="text-muted">{{ $type->units->count() }} unit(s)</span></td>
                                <td class="store-actions-cell">
                                    <div class="dropdown store-action-dropdown">
                                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                                            <button type="button" class="dropdown-item btn-add-unit" data-type-id="{{ $type->measurement_typeID }}" data-type-name="{{ $type->type_name }}"><i class="fa fa-plus text-success"></i> Add Unit</button>
                                            <button type="button" class="dropdown-item btn-edit-type" data-id="{{ $type->measurement_typeID }}" data-name="{{ $type->type_name }}"><i class="fa fa-edit text-primary"></i> Edit</button>
                                            <button type="button" class="dropdown-item text-danger btn-delete-type" data-id="{{ $type->measurement_typeID }}"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Packages --}}
            <div id="section-packages" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Packages</div>
                        <p class="text-muted small mb-0">Add package names directly (e.g. Sack, Container, Bag, Box, Gunia). These appear when registering store items.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('packages') !!}
                        {!! $storeViewToggle('packagesTable') !!}
                        <button type="button" class="btn btn-sm btn-primary-custom" data-toggle="modal" data-target="#addPackagesModal">
                            <i class="fa fa-plus"></i> Add Packages
                        </button>
                    </div>
                </div>
                <div class="store-data-wrapper" data-table-id="packagesTable">
                <div class="store-list-view">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped store-table-compact" id="packagesTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Package Name</th>
                                <th class="store-actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($packages as $index => $pkg)
                            @php
                                $pkgViewMore = [
                                    'title' => $pkg->package_name,
                                    'fields' => [
                                        ['label' => 'Package Name', 'value' => $pkg->package_name],
                                        ['label' => 'Status', 'value' => $pkg->status ?? 'Active'],
                                    ],
                                ];
                            @endphp
                            <tr data-package-id="{{ $pkg->packageID }}" data-view-more='@json($pkgViewMore)'>
                                <td>{{ $index + 1 }}</td>
                                <td class="pkg-name font-weight-bold">{{ $pkg->package_name }}</td>
                                <td class="store-actions-cell">
                                    <div class="dropdown store-action-dropdown">
                                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                                            <button type="button" class="dropdown-item btn-edit-package" data-id="{{ $pkg->packageID }}" data-name="{{ $pkg->package_name }}"><i class="fa fa-edit text-primary"></i> Edit</button>
                                            <button type="button" class="dropdown-item text-danger btn-delete-package" data-id="{{ $pkg->packageID }}"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="store-grid-view row"></div>
                </div>
            </div>

            {{-- Report --}}
            <div id="section-report" class="store-section">
                <div class="store-section-toolbar">
                    <div>
                        <div class="section-title">Store Reports</div>
                        <p class="text-muted small mb-0">Chagua category kwanza, kisha aina ya report na filters. Generate reports kwa inventory, requisitions, au item maalum.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap:8px;">
                        {!! $storePdfBtn('report') !!}
                    </div>
                </div>

                <div class="store-report-filters mb-4">
                    {{-- Step 1: Category (always first) --}}
                    <div class="border rounded p-3 mb-3 bg-light">
                        <label for="reportCategoryFilter" class="font-weight-bold mb-1">
                            <span class="badge badge-primary mr-1">1</span> Item Category
                        </label>
                        <select class="form-control w-100" id="reportCategoryFilter">
                            <option value="" selected>All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->categoryID }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Chagua category unayotaka kuona report yake — au All Categories kwa zote.</small>
                    </div>

                    {{-- Step 2: Report type (after category context) --}}
                    <div id="reportTypeSection" class="border rounded p-3 mb-3" style="display:none;">
                        <label for="reportTypeSelect" class="font-weight-bold mb-1">
                            <span class="badge badge-primary mr-1">2</span> Report Type
                        </label>
                        <select class="form-control w-100" id="reportTypeSelect">
                            <option value="all_inventory" selected>All Items Inventory Report</option>
                            <option value="requisition">Requisition Report — waliochukua items</option>
                            <option value="specific_item">Specific Item Report</option>
                        </select>
                    </div>

                    {{-- All Inventory filters --}}
                    <div id="allInventoryReportPanel" class="border rounded p-3 mb-3" style="display:none;">
                        <div class="font-weight-bold mb-2"><span class="badge badge-secondary mr-1">3</span> Inventory Filters</div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="reportReturnFilter">Return Policy</label>
                                <select class="form-control w-100" id="reportReturnFilter">
                                    <option value="all" selected>All</option>
                                    <option value="can_return">Can Return</option>
                                    <option value="cant_return">Can't Return</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="reportAsOfDate">Stock As Of Date</label>
                                <input type="date" class="form-control w-100" id="reportAsOfDate" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Requisition report filters --}}
                    <div id="requisitionReportPanel" class="border rounded p-3 mb-3" style="display:none;">
                        <div class="font-weight-bold mb-2"><span class="badge badge-secondary mr-1">3</span> Requisition Filters</div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="reqReportReturnFilter">Return Policy</label>
                                <select class="form-control w-100" id="reqReportReturnFilter">
                                    <option value="all" selected>All</option>
                                    <option value="can_return">Can Return</option>
                                    <option value="cant_return">Can't Return</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group" id="reqReportReturnStatusWrap">
                                <label for="reqReportReturnStatus">Return Status</label>
                                <select class="form-control w-100" id="reqReportReturnStatus">
                                    <option value="all" selected>All</option>
                                    <option value="not_returned">Not Returned Yet</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="returned">Returned (Any)</option>
                                    <option value="returned_good">Returned — Good</option>
                                    <option value="returned_damaged">Returned — Damaged</option>
                                    <option value="returned_lost">Returned — Lost</option>
                                </select>
                                <small class="text-muted">Inatumika kwa items zenye Can Return</small>
                            </div>
                        </div>
                    </div>

                    {{-- Specific item filters --}}
                    <div id="specificItemReportPanel" class="border rounded p-3 mb-3" style="display:none;">
                        <div class="font-weight-bold mb-2"><span class="badge badge-secondary mr-1">3</span> Item Selection</div>
                        <div class="form-group mb-0">
                            <label for="reportItemSelect">Select Item</label>
                            <select class="form-control w-100" id="reportItemSelect">
                                <option value="">Choose item...</option>
                            </select>
                        </div>
                    </div>

                    {{-- Shared period filters (requisition + specific item) --}}
                    <div id="reportPeriodPanel" class="border rounded p-3 mb-3" style="display:none;">
                        <div class="font-weight-bold mb-2"><span class="badge badge-secondary mr-1">4</span> Time Period</div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="reportPeriodType">Period</label>
                                <select class="form-control w-100" id="reportPeriodType">
                                    <option value="current_month" selected>Current Month</option>
                                    <option value="month">Specific Month</option>
                                    <option value="year">Full Year</option>
                                    <option value="range">Date Range</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group" id="reportMonthField" style="display:none;">
                                <label for="reportMonth">Month</label>
                                <select class="form-control w-100" id="reportMonth">
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                                        <option value="{{ $i + 1 }}" @if($i + 1 == (int) date('n')) selected @endif>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group" id="reportYearField" style="display:none;">
                                <label for="reportYear">Year</label>
                                <select class="form-control w-100" id="reportYear">
                                    @for($y = (int) date('Y'); $y >= (int) date('Y') - 10; $y--)
                                        <option value="{{ $y }}" @if($y == (int) date('Y')) selected @endif>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4 form-group" id="reportDateFromField" style="display:none;">
                                <label for="reportDateFrom">From Date</label>
                                <input type="date" class="form-control w-100" id="reportDateFrom" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-md-4 form-group" id="reportDateToField" style="display:none;">
                                <label for="reportDateTo">To Date</label>
                                <input type="date" class="form-control w-100" id="reportDateTo" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div id="reportGenerateRow" class="text-right" style="display:none;">
                        <button type="button" class="btn btn-primary-custom px-4" id="btnGenerateReport">
                            <i class="fa fa-bar-chart"></i> Generate Report
                        </button>
                    </div>
                </div>

                <div id="reportLoading" class="text-center py-5" style="display:none;">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Generating report...</p>
                </div>

                <div id="reportEmpty" class="store-report-placeholder" style="display:none;">
                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                    <span id="reportEmptyText">No data found for the selected filters.</span>
                </div>

                {{-- Inventory results --}}
                <div id="inventoryReportResults" class="report-results-panel" style="display:none;">
                    <div class="mb-2 small text-muted" id="inventoryReportMeta"></div>
                    <div class="store-report-summary" id="inventoryReportSummary"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped store-report-table mb-0" id="inventoryReportTable">
                            <thead>
                                <tr>
                                    <th width="50" rowspan="2">#</th>
                                    <th rowspan="2">Item Name</th>
                                    <th colspan="2" class="store-report-qty-head">Unit Measure Stock</th>
                                    <th colspan="2" class="store-report-qty-head">Package Stock</th>
                                </tr>
                                <tr>
                                    <th class="store-report-subhead">Unit Measure</th>
                                    <th class="store-report-subhead">Quantity</th>
                                    <th class="store-report-subhead">Package</th>
                                    <th class="store-report-subhead">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryReportTableBody"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Requisition results --}}
                <div id="requisitionReportResults" class="report-results-panel" style="display:none;">
                    <div class="mb-2 small text-muted" id="requisitionReportMeta"></div>
                    <div class="store-report-summary" id="requisitionReportSummary"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped store-report-table mb-0" id="requisitionReportTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Recipient</th>
                                    <th>Item</th>
                                    <th>Label / Taken</th>
                                    <th>Return Policy</th>
                                    <th>Return Status</th>
                                    <th>Expected Return</th>
                                    <th>Issued</th>
                                    <th>Returned</th>
                                </tr>
                            </thead>
                            <tbody id="requisitionReportTableBody"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Specific item results --}}
                <div id="specificItemReportResults" class="report-results-panel" style="display:none;">
                    <div class="mb-2" id="specificItemReportHeader"></div>
                    <div class="store-report-summary" id="specificItemReportSummary"></div>
                    <h6 class="font-weight-bold mt-3">Current Stock Breakdown</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm store-report-table mb-0" id="specificStockTable">
                            <thead class="bg-light">
                                <tr><th>Package / Unit</th><th>Labels In Stock</th><th>Items Remaining</th></tr>
                            </thead>
                            <tbody id="specificStockTableBody"></tbody>
                        </table>
                    </div>
                    <h6 class="font-weight-bold">Issues in Period</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped store-report-table mb-0" id="specificReqReportTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th><th>Recipient</th><th>Label / Taken</th>
                                    <th>Return Status</th><th>Issued</th><th>Returned</th>
                                </tr>
                            </thead>
                            <tbody id="specificReqReportTableBody"></tbody>
                        </table>
                    </div>
                    <h6 class="font-weight-bold">Damaged / Lost in Period</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped store-report-table mb-0" id="specificIncidentReportTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th><th>Type</th><th>Responsible</th><th>Label / Affected</th>
                                    <th>Reason</th><th>Status</th><th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="specificIncidentReportTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- View More Modal --}}
<div class="modal fade store-modal" id="storeViewMoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="storeViewMoreTitle">Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="storeViewMoreBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Item Request Review Modal --}}
<div class="modal fade store-modal" id="itemRequestReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="itemRequestReviewTitle">Item Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="itemRequestReviewBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-danger" id="itemRequestModalReject" style="display:none;"><i class="fa fa-times"></i> Reject</button>
                <button type="button" class="btn btn-primary-custom" id="itemRequestModalApprove" style="display:none;"><i class="fa fa-check"></i> Approve</button>
            </div>
        </div>
    </div>
</div>

{{-- Add Item Modal --}}
<div class="modal fade store-modal" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="fa fa-plus"></i> Add Store Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addItemForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="item_name" required placeholder="e.g. A4 Paper, Chalk">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                        <div class="item-select-field">
                        <select class="form-control item-form-select" name="categoryID" id="itemCategorySelect" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->categoryID }}">{{ $cat->category_name }}</option>
                            @endforeach
                            <option value="__add_category__" class="select-add-option">+ Add New Category</option>
                        </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Measurement Type <span class="text-danger">*</span></label>
                        <div class="item-select-field">
                        <select class="form-control item-form-select" name="measurement_typeID" id="itemMeasurementTypeSelect" required>
                            <option value="">Select Measurement Type</option>
                            @foreach($measurementTypes as $type)
                                <option value="{{ $type->measurement_typeID }}">{{ $type->type_name }}</option>
                            @endforeach
                            <option value="__add_type__" class="select-add-option">+ Add New Measurement Type</option>
                        </select>
                        </div>
                    </div>
                    <div class="form-group" id="unitSelectGroup" style="display:none;">
                        <label class="font-weight-bold">Units of Measure <span class="text-danger">*</span></label>
                        <small class="text-muted d-block mb-2">Select all units used for this item (e.g. Carton, Box, Dozen for Chalk).</small>
                        <div class="item-select-field">
                        <select class="form-control item-unit-select" name="unitIDs[]" id="itemUnitSelect" multiple>
                        </select>
                        </div>
                    </div>

                    <div class="form-group border-top pt-3 mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="hasPackagesCheck" name="has_packages" value="1">
                            <label class="custom-control-label font-weight-bold" for="hasPackagesCheck">There is a package inside?</label>
                        </div>
                        <small class="text-muted d-block mt-1">Enable to define how this item is packaged (e.g. Sack = 50kg, Container = 100 Tonnes).</small>
                    </div>

                    <div id="packageSpecsSection" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold mb-0">Package Chains</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnManagePackagesFromItem">
                                <i class="fa fa-cog"></i> Manage Packages
                            </button>
                        </div>
                        <p class="text-muted small mb-2">Add one or more package chains (e.g. <strong>Set</strong> → Carton → Box, and another chain <strong>Roboter</strong> → …). Each chain is separate. You can stock any level later (Set, Carton, Roboter, etc.).</p>
                        <div id="packageLiveDashboard" class="pkg-live-dashboard mb-3" style="display:none;"></div>
                        <div id="packageSpecsBody"></div>
                        <button type="button" class="btn btn-sm btn-primary-custom" id="btnAddPackageSpecRow">
                            <i class="fa fa-plus"></i> Add Another Package Chain
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Item Modal --}}
<div class="modal fade store-modal" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Store Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editItemForm">
                <input type="hidden" name="itemID" id="editItemId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="item_name" id="editItemName" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                        <div class="item-select-field">
                        <select class="form-control item-form-select" name="categoryID" id="editItemCategory" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->categoryID }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Measurement Type <span class="text-danger">*</span></label>
                        <div class="item-select-field">
                        <select class="form-control item-form-select" name="measurement_typeID" id="editItemType" required>
                            @foreach($measurementTypes as $type)
                                <option value="{{ $type->measurement_typeID }}">{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Units of Measure <span class="text-danger">*</span></label>
                        <small class="text-muted d-block mb-2">Select all units used for this item.</small>
                        <div class="item-select-field">
                        <select class="form-control item-unit-select" name="unitIDs[]" id="editItemUnit" multiple>
                        </select>
                        </div>
                    </div>

                    <div class="form-group border-top pt-3 mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="editHasPackagesCheck" name="has_packages" value="1">
                            <label class="custom-control-label font-weight-bold" for="editHasPackagesCheck">There is a package inside?</label>
                        </div>
                    </div>

                    <div id="editPackageSpecsSection" style="display:none;">
                        <label class="font-weight-bold d-block mb-2">Package Chains</label>
                        <p class="text-muted small mb-2">Add one or more package chains (e.g. <strong>Set</strong> → Carton → Box, and another chain <strong>Roboter</strong> → …). Each chain is separate. You can stock any level later (Set, Carton, Roboter, etc.).</p>
                        <div id="editPackageLiveDashboard" class="pkg-live-dashboard mb-3" style="display:none;"></div>
                        <div id="editPackageSpecsBody"></div>
                        <button type="button" class="btn btn-sm btn-primary-custom" id="btnEditAddPackageSpecRow">
                            <i class="fa fa-plus"></i> Add Another Package Chain
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade store-modal" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Add Item Category</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="category_name" required placeholder="e.g. Stationery, Lab Equipment">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
<div class="modal fade store-modal" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editCategoryForm">
                <input type="hidden" id="editCategoryId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Measurement Type Modal --}}
<div class="modal fade store-modal" id="addMeasurementTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Add Measurement Type</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addMeasurementTypeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="type_name" required placeholder="e.g. Custom Packaging">
                    </div>
                    <p class="small text-muted mb-0">After saving, add units of measure for this type.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Measurement Type Modal --}}
<div class="modal fade store-modal" id="editMeasurementTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Edit Measurement Type</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editMeasurementTypeForm">
                <input type="hidden" id="editTypeId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Type Name</label>
                        <input type="text" class="form-control" id="editTypeName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Unit Modal --}}
<div class="modal fade store-modal" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Add Unit of Measure</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addUnitForm">
                <input type="hidden" name="measurement_typeID" id="addUnitTypeId">
                <div class="modal-body">
                    <p class="small text-muted">For type: <strong id="addUnitTypeName"></strong></p>
                    <div class="form-group">
                        <label class="font-weight-bold">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="unit_name" required placeholder="e.g. Kilogram (kg)">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Abbreviation (optional)</label>
                        <input type="text" class="form-control" name="abbreviation" placeholder="e.g. kg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Unit Modal --}}
<div class="modal fade store-modal" id="editUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Edit Unit of Measure</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editUnitForm">
                <input type="hidden" id="editUnitId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Unit Name</label>
                        <input type="text" class="form-control" id="editUnitName" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Abbreviation</label>
                        <input type="text" class="form-control" id="editUnitAbbr">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Packages Modal (multiple) --}}
<div class="modal fade store-modal" id="addPackagesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Add Packages</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addPackagesForm">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Add one or more package names at once (e.g. Sack, Container, Bag, Box, Gunia).</p>
                    <div id="packageNamesContainer">
                        <div class="package-name-row input-group mb-2">
                            <input type="text" class="form-control package-name-input" placeholder="Package name (e.g. Sack)" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger btn-remove-package-name" style="display:none;" title="Remove">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPackageNameRow">
                        <i class="fa fa-plus"></i> Add Another Package
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Save Packages</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Package Modal --}}
<div class="modal fade store-modal" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Edit Package</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editPackageForm">
                <input type="hidden" id="editPackageId">
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Package Name</label>
                        <input type="text" class="form-control" id="editPackageName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Item in Stock Modal removed: now inline collapse inside section-stock --}}

{{-- Issue Item (Requisition) Modal --}}
<div class="modal fade store-modal" id="issueItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="fa fa-hand-o-right"></i> Issue Item to Requester</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="issueItemForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Recipient Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="issueRecipientType" required>
                                    <option value="">Select type...</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="staff">Staff</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Select Person <span class="text-danger">*</span></label>
                                <select class="form-control issue-form-select" id="issueRecipientSelect" required disabled>
                                    <option value="">Select recipient type first...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Item Category</label>
                        <select class="form-control" id="issueCategoryFilter">
                            <option value="" selected>All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->categoryID }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Chagua category kwanza kufupisha orodha ya items — au All Categories kwa zote.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Select Item <span class="text-danger">*</span></label>
                        <select class="form-control issue-form-select" id="issueItemSelect" required>
                            <option value="">Search or select item in stock...</option>
                        </select>
                        <small class="text-muted">Only items with available stock are listed.</small>
                    </div>

                    <div id="issueStockInfo" class="alert alert-light border small py-2" style="display:none;">
                        <strong>Package / Unit:</strong> <span id="issuePackageUnit">—</span>
                        &nbsp;|&nbsp;
                        <strong>Available:</strong> <span id="issueAvailableCount">0</span> unit(s)
                    </div>

                    <div id="issueLabelsSection" style="display:none;">
                        <label class="font-weight-bold">Select Label <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Chagua label moja au zaidi. Kuchagua label = kuchukua kitengo hicho. Labels nyingi → main package moja kwa kila label (quantity auto = 1). Sub-package inapatikana label moja tu.</p>
                        <div class="store-checkbox-toolbar">
                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#issueLabelsList">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#issueLabelsList">Deselect All</button>
                        </div>
                        <div id="issueLabelsList" class="border p-3" style="max-height:220px;overflow-y:auto;background:#fafafa;"></div>
                    </div>

                    <div id="issueTakeSection" style="display:none;">
                        <label class="font-weight-bold">What are they taking? <span class="text-danger">*</span></label>
                        <div id="issueTakeChainInfo" class="alert alert-light border small py-2 mb-2" style="display:none;"></div>
                        <div class="form-group mb-2" id="issueTakeMethodWrap">
                            <label class="font-weight-bold">Request Method <span class="text-danger">*</span></label>
                            <select class="form-control" id="issueTakeMethod">
                                <option value="">Choose request method...</option>
                            </select>
                        </div>
                        <div class="form-group mb-2" id="issueTakeTargetWrap" style="display:none;">
                            <label class="font-weight-bold" id="issueTakeTargetLabel">Package / Unit <span class="text-danger">*</span></label>
                            <select class="form-control" id="issueTakeTarget" style="display:none;">
                                <option value="">Choose...</option>
                            </select>
                            <div class="store-checkbox-toolbar" id="issueTakeTargetToolbar" style="display:none;">
                                <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#issueTakeTargetList">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#issueTakeTargetList">Deselect All</button>
                            </div>
                            <div id="issueTakeTargetList" class="issue-take-target-list border p-3" style="display:none;"></div>
                            <small class="text-muted d-block mt-1" id="issueTakeTargetDetail"></small>
                        </div>
                        <div class="form-group mb-0" id="issueTakeQtyWrap">
                            <label class="font-weight-bold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="issueTakeQty" min="1" step="1" value="1">
                            <small class="text-muted" id="issueTakeQtyHint"></small>
                            <div class="text-danger small store-qty-error mt-1" style="display:none;"></div>
                        </div>
                        <div id="issueTakeLivePreview" class="alert alert-info border small py-2 mt-3 mb-0" style="display:none;"></div>
                    </div>

                    <div id="issueExpectedReturnSection" class="form-group" style="display:none;">
                        <label class="font-weight-bold">Expected Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="issueExpectedReturnDate" min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Notes</label>
                        <textarea class="form-control" id="issueNotes" rows="2" placeholder="Optional notes about this requisition..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="btnIssueItem" disabled>
                        <i class="fa fa-hand-o-right"></i> Issue Item(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Return Item Modal --}}
<div class="modal fade store-modal" id="returnItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="fa fa-undo"></i> Return Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="returnItemForm">
                <input type="hidden" id="returnRequisitionId">
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3" id="returnItemSummary"></div>

                    <div class="form-group">
                        <label class="font-weight-bold">Date Returned <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="returnedAtDate" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Return Description</label>
                        <textarea class="form-control" id="returnDescription" rows="2" placeholder="Optional notes about this return..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Return Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="returnCondition" required>
                            <option value="">Select return status...</option>
                            <option value="good">Good — return to stock</option>
                            <option value="damaged">Damaged — record as damaged item</option>
                            <option value="lost">Lost — record as lost item</option>
                        </select>
                    </div>

                    <div id="returnIncidentReasonSection" style="display:none;">
                        <div class="alert alert-warning small py-2" id="returnIncidentAlert">
                            This will be recorded in Damaged / Lost Items. The person who received this item will be marked as responsible.
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold" id="returnIncidentReasonLabel">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="badDescription" rows="3" placeholder="Describe what happened to the item..."></textarea>
                        </div>
                        <div class="form-group mt-2 mb-0">
                            <label class="font-weight-bold text-danger">Responsible Person</label>
                            <input type="text" class="form-control" id="returnResponsiblePerson" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fa fa-check"></i> Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Record Damaged / Lost Item Modal --}}
<div class="modal fade store-modal" id="incidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="incidentModalTitle"><i class="fa fa-warning"></i> Record Item Incident</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="incidentForm">
                <input type="hidden" id="incidentType" value="damaged">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Responsible Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="incidentResponsibleType" required>
                                    <option value="">Select type...</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="staff">Staff</option>
                                    <option value="student">Student</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="incidentResponsibleSelectWrap">
                                <label class="font-weight-bold">Select Person <span class="text-danger">*</span></label>
                                <select class="form-control incident-form-select" id="incidentResponsibleSelect" required disabled>
                                    <option value="">Select responsible type first...</option>
                                </select>
                            </div>
                            <div class="form-group" id="incidentOtherNameWrap" style="display:none;">
                                <label class="font-weight-bold">Name (Other) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="incidentOtherName" placeholder="Enter name of responsible person...">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Select Item <span class="text-danger">*</span></label>
                        <select class="form-control incident-form-select" id="incidentItemSelect" required>
                            <option value="">Search or select item in stock...</option>
                        </select>
                        <small class="text-muted">Only items with available stock are listed.</small>
                    </div>

                    <div id="incidentStockInfo" class="alert alert-light border small py-2" style="display:none;">
                        <strong>Package / Unit:</strong> <span id="incidentPackageUnit">—</span>
                        &nbsp;|&nbsp;
                        <strong>Available:</strong> <span id="incidentAvailableCount">0</span> unit(s)
                    </div>

                    <div id="incidentLabelsSection" style="display:none;">
                        <label class="font-weight-bold">Select Label <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Chagua label moja au zaidi. Labels nyingi → main package moja kwa kila label (quantity auto = 1). Sub-package inapatikana label moja tu.</p>
                        <div class="store-checkbox-toolbar">
                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#incidentLabelsList">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#incidentLabelsList">Deselect All</button>
                        </div>
                        <div id="incidentLabelsList" class="border p-3" style="max-height:220px;overflow-y:auto;background:#fafafa;"></div>
                    </div>

                    <div id="incidentTakeSection" style="display:none;">
                        <label class="font-weight-bold">What was affected? <span class="text-danger">*</span></label>
                        <div id="incidentTakeChainInfo" class="alert alert-light border small py-2 mb-2" style="display:none;"></div>
                        <div class="form-group mb-2" id="incidentTakeMethodWrap">
                            <label class="font-weight-bold">Request Method <span class="text-danger">*</span></label>
                            <select class="form-control" id="incidentTakeMethod">
                                <option value="">Choose request method...</option>
                            </select>
                        </div>
                        <div class="form-group mb-2" id="incidentTakeTargetWrap" style="display:none;">
                            <label class="font-weight-bold" id="incidentTakeTargetLabel">Package / Unit <span class="text-danger">*</span></label>
                            <select class="form-control" id="incidentTakeTarget" style="display:none;">
                                <option value="">Choose...</option>
                            </select>
                            <div class="store-checkbox-toolbar" id="incidentTakeTargetToolbar" style="display:none;">
                                <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#incidentTakeTargetList">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#incidentTakeTargetList">Deselect All</button>
                            </div>
                            <div id="incidentTakeTargetList" class="issue-take-target-list border p-3" style="display:none;"></div>
                            <small class="text-muted d-block mt-1" id="incidentTakeTargetDetail"></small>
                        </div>
                        <div class="form-group mb-0" id="incidentTakeQtyWrap">
                            <label class="font-weight-bold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="incidentTakeQty" min="1" step="1" value="1">
                            <small class="text-muted" id="incidentTakeQtyHint"></small>
                            <div class="text-danger small store-qty-error mt-1" style="display:none;"></div>
                        </div>
                        <div id="incidentTakeLivePreview" class="alert alert-info border small py-2 mt-3 mb-0" style="display:none;"></div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="incidentReason" rows="2" required placeholder="Describe how the item was damaged or lost..."></textarea>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Notes</label>
                        <textarea class="form-control" id="incidentNotes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="btnSaveIncident" disabled>
                        <i class="fa fa-save"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Incident Modal --}}
<div class="modal fade store-modal" id="editIncidentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editIncidentModalTitle"><i class="fa fa-edit"></i> Edit Record</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editIncidentForm">
                <input type="hidden" id="editIncidentId">
                <input type="hidden" id="editIncidentTableId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Responsible Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="editIncidentResponsibleType" required>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Staff</option>
                            <option value="student">Student</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" id="editIncidentResponsibleSelectWrap">
                        <label class="font-weight-bold">Select Person <span class="text-danger">*</span></label>
                        <select class="form-control" id="editIncidentResponsibleSelect"></select>
                    </div>
                    <div class="form-group" id="editIncidentOtherNameWrap" style="display:none;">
                        <label class="font-weight-bold">Name (Other) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editIncidentOtherName">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editIncidentReason" rows="3" required></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Notes</label>
                        <textarea class="form-control" id="editIncidentNotes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Resolve Incident Modal --}}
<div class="modal fade store-modal" id="resolveIncidentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="resolveIncidentModalTitle"><i class="fa fa-check-circle"></i> Resolve Record</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="resolveIncidentForm">
                <input type="hidden" id="resolveIncidentId">
                <input type="hidden" id="resolveIncidentTableId">
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3" id="resolveIncidentSummary"></div>
                    <div class="form-group">
                        <label class="font-weight-bold">Resolve Method <span class="text-danger">*</span></label>
                        <select class="form-control" id="resolveIncidentMethod" required>
                            <option value="">Select method...</option>
                            <option value="cash">Cash — person pays compensation</option>
                            <option value="replace_new">Replace New — restore item to stock</option>
                        </select>
                    </div>
                    <div class="form-group mb-0" id="resolvePaymentAmountWrap" style="display:none;">
                        <label class="font-weight-bold">Amount Paid <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="resolvePaymentAmount" min="0" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-check"></i> Confirm Resolve</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Store PDF Download Progress --}}
<div class="modal fade store-modal" id="storeDownloadProgressModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="fa fa-file-pdf-o fa-3x text-danger store-pdf-pulse"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold;" id="storePdfProgressTitle">Generating Store Report</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 6px;">
                    <div id="storePdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="storePdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small">Please wait while your PDF is being prepared...</p>
            </div>
        </div>
    </div>
</div>

<style>
    .store-pdf-pulse { animation: storePdfPulse 1.5s infinite; }
    @keyframes storePdfPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

@include('Admin.partials.store_actors_modal')

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const routes = {
        unitsByType: (id) => `{{ url('store/measurement-types') }}/${id}/units`,
        categoriesStore: `{{ route('store.categories.store') }}`,
        categoriesUpdate: (id) => `{{ url('store/categories') }}/${id}/update`,
        categoriesDelete: (id) => `{{ url('store/categories') }}/${id}/delete`,
        typesStore: `{{ route('store.measurement_types.store') }}`,
        typesUpdate: (id) => `{{ url('store/measurement-types') }}/${id}/update`,
        typesDelete: (id) => `{{ url('store/measurement-types') }}/${id}/delete`,
        unitsStore: `{{ route('store.units.store') }}`,
        unitsUpdate: (id) => `{{ url('store/units') }}/${id}/update`,
        unitsDelete: (id) => `{{ url('store/units') }}/${id}/delete`,
        itemsStore: `{{ route('store.items.store') }}`,
        itemsUpdate: (id) => `{{ url('store/items') }}/${id}/update`,
        itemsDelete: (id) => `{{ url('store/items') }}/${id}/delete`,
        packagesBulk: `{{ route('store.packages.bulk') }}`,
        packagesUpdate: (id) => `{{ url('store/packages') }}/${id}/update`,
        packagesDelete: (id) => `{{ url('store/packages') }}/${id}/delete`,
        stockStore: `{{ route('store.stock.store') }}`,
        stockDelete: (id) => `{{ url('store/stock') }}/${id}/delete`,
        requisitionsStore: `{{ route('store.requisitions.store') }}`,
        requisitionsReturn: (id) => `{{ url('store/requisitions') }}/${id}/return`,
        incidentsStore: `{{ route('store.incidents.store') }}`,
        incidentsUpdate: (id) => `{{ url('store/incidents') }}/${id}/update`,
        incidentsDelete: (id) => `{{ url('store/incidents') }}/${id}/delete`,
        incidentsResolve: (id) => `{{ url('store/incidents') }}/${id}/resolve`,
        allInventoryReport: `{{ route('store.reports.all_inventory') }}`,
        requisitionReport: `{{ route('store.reports.requisitions') }}`,
        specificItemReport: `{{ route('store.reports.specific_item') }}`,
        storeDashboard: `{{ route('store.dashboard') }}`,
        exportPdf: `{{ route('store.export_pdf') }}`,
        itemRequestsMarkRead: `{{ route('store.item_requests.mark_read') }}`,
        itemRequestsApprove: (id) => `{{ url('store/item-requests') }}/${id}/approve`,
        itemRequestsReject: (id) => `{{ url('store/item-requests') }}/${id}/reject`,
        inventoryActorsStore: `{{ route('store.inventory_actors.store') }}`,
        inventoryActorsUpdate: (id) => `{{ url('store/inventory-actors') }}/${id}/update`,
        inventoryActorsDelete: (id) => `{{ url('store/inventory-actors') }}/${id}/delete`,
    };

    const storeAccess = @json($storeAccess ?? ['role' => 'admin', 'privileges' => []]);
    const actorPickerTeachers = @json($actorPickerTeachers ?? []);
    const actorPickerStaff = @json($actorPickerStaff ?? []);
    let inventoryActorsState = @json($inventoryActors ?? []);
    let storeDashboardData = @json($dashboardStats ?? []);

    let storePackagesList = @json($storePackagesList);
    let storeItemsList = @json($storeItemsList);
    const requestersList = @json($requestersList);
    let availableStockList = @json($availableStockList);
    let measurementTypesState = @json($measurementTypesState);
    let itemPackageSpecsMap = @json($itemPackageSpecsMap ?? new \stdClass());
    const damagedIncidentsUi = @json($damagedIncidentsUi ?? []);
    const lostIncidentsUi = @json($lostIncidentsUi ?? []);
    const reportItemsList = @json($reportItemsList ?? []);
    if (Array.isArray(itemPackageSpecsMap)) {
        itemPackageSpecsMap = Object.assign({}, itemPackageSpecsMap);
    }

    function showAlert(message, type) {
        const isSuccess = type === 'success';
        const isWarning = type === 'warning';
        Swal.fire({
            icon: isSuccess ? 'success' : (isWarning ? 'warning' : 'error'),
            title: isSuccess ? 'Success' : (isWarning ? 'Notice' : 'Error'),
            text: message || (isSuccess ? 'Done successfully.' : 'Something went wrong.'),
            confirmButtonColor: '#940000',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'store-swal-popup',
                confirmButton: 'store-swal-confirm',
            },
        });
    }

    async function storeConfirm(message, title) {
        const result = await Swal.fire({
            icon: 'warning',
            title: title || 'Are you sure?',
            text: message,
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'store-swal-popup',
                confirmButton: 'store-swal-confirm',
            },
        });
        return result.isConfirmed;
    }

    let storeAjaxActive = 0;
    let storeAjaxFinishTimer = null;

    function storeLoadingMessage(url, method) {
        const u = String(url || '').toLowerCase();
        if (u.includes('/delete') || u.includes('delete')) return 'Deleting...';
        if (u.includes('/update') || u.includes('update')) return 'Updating...';
        if (u.includes('/return')) return 'Processing return...';
        if (u.includes('requisition')) return 'Saving requisition...';
        if (u.includes('/stock')) return 'Saving stock...';
        if (u.includes('/items')) return method === 'GET' ? 'Loading items...' : 'Saving item...';
        if (u.includes('report')) return 'Generating report...';
        if (u.includes('package')) return 'Saving package...';
        if (u.includes('categor')) return 'Saving category...';
        if (u.includes('measurement') || u.includes('/units') || u.includes('/unit')) return 'Saving...';
        return method === 'GET' ? 'Loading...' : 'Saving...';
    }

    function storeLoadingStart(message) {
        storeAjaxActive += 1;
        clearTimeout(storeAjaxFinishTimer);
        const $bar = $('#storeAjaxBar');
        const $overlay = $('#storeAjaxOverlay');
        $bar.removeClass('is-finishing').addClass('is-active').attr('aria-hidden', 'false');
        $('#storeAjaxText').text(message || 'Working...');
        $overlay.addClass('is-active').attr('aria-hidden', 'false');
        $('body').addClass('store-ajax-busy');
    }

    function storeLoadingStop() {
        storeAjaxActive = Math.max(0, storeAjaxActive - 1);
        if (storeAjaxActive > 0) return;

        const $bar = $('#storeAjaxBar');
        const $overlay = $('#storeAjaxOverlay');
        $bar.addClass('is-finishing');
        $overlay.removeClass('is-active').attr('aria-hidden', 'true');
        $('body').removeClass('store-ajax-busy');

        storeAjaxFinishTimer = setTimeout(function () {
            $bar.removeClass('is-active is-finishing').attr('aria-hidden', 'true');
        }, 220);
    }

    async function postJson(url, data, loadingMessage) {
        storeLoadingStart(loadingMessage || storeLoadingMessage(url, 'POST'));
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        } finally {
            storeLoadingStop();
        }
    }

    async function getJson(url, loadingMessage) {
        storeLoadingStart(loadingMessage || storeLoadingMessage(url, 'GET'));
        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        } finally {
            storeLoadingStop();
        }
    }

    const ADD_CATEGORY_OPT = '__add_category__';
    const ADD_TYPE_OPT = '__add_type__';
    const ADD_UNIT_OPT = '__add_unit__';
    let returnToAddItemAfter = null;

    function isAddOption(value) {
        return value === ADD_CATEGORY_OPT || value === ADD_TYPE_OPT || value === ADD_UNIT_OPT;
    }

    function select2AddOptionTemplate(data) {
        if (!data.id) return data.text;
        if (isAddOption(data.id)) {
            return $('<span class="text-primary-custom font-weight-bold">' + data.text + '</span>');
        }
        return data.text;
    }

    function ensureAddOptionLast($select, value, label) {
        $select.find(`option[value="${value}"]`).remove();
        $select.append(new Option(label, value, false, false));
    }

    function initItemFormSelect2($el) {
        $el.each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            const $parent = $select.closest('.modal');
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownAutoWidth: false,
                dropdownParent: $parent.length ? $parent : $(document.body),
                templateResult: select2AddOptionTemplate,
            });
        });
    }

    function initUnitMultiSelect($unitSelect, selectedUnitIds) {
        if ($unitSelect.hasClass('select2-hidden-accessible')) {
            $unitSelect.select2('destroy');
        }
        const $parent = $unitSelect.closest('.modal');
        $unitSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            multiple: true,
            placeholder: 'Select one or more units...',
            allowClear: true,
            minimumResultsForSearch: 0,
            closeOnSelect: false,
            dropdownParent: $parent.length ? $parent : $(document.body),
            templateResult: select2AddOptionTemplate,
        });
        const ids = (selectedUnitIds || []).map(String).filter(id => !isAddOption(id));
        if (ids.length) {
            $unitSelect.val(ids).trigger('change');
        }
    }

    function openAddUnitFromItemForm() {
        const typeId = $('#itemMeasurementTypeSelect').val();
        const typeName = $('#itemMeasurementTypeSelect option:selected').text();
        if (!typeId || isAddOption(typeId)) return;
        returnToAddItemAfter = 'unit';
        $('#addItemModal').modal('hide');
        $('#addUnitTypeId').val(typeId);
        $('#addUnitTypeName').text(typeName);
        $('#addUnitModal').modal('show');
    }

    $(document).on('select2:open', '#addItemModal .item-form-select, #editItemModal .item-form-select, #addItemModal .item-unit-select, #editItemModal .item-unit-select', function () {
        setTimeout(function () {
            const $open = $('.select2-container--open');
            const searchField = $open.find('.select2-search__field')[0];
            if (searchField) {
                searchField.focus();
            }
        }, 0);
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function storeCheckboxToolbarHtml(targetSelector) {
        return `<div class="store-checkbox-toolbar">
            <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="${targetSelector}">Select All</button>
            <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="${targetSelector}">Deselect All</button>
        </div>`;
    }

    function setStoreCheckboxGroupChecked(targetSelector, checked) {
        $(targetSelector).find('input[type="checkbox"]:not(:disabled)').each(function () {
            $(this).prop('checked', checked).trigger('change');
        });
    }

    $(document).on('click', '.store-check-all-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const target = $(this).data('target');
        if (target) setStoreCheckboxGroupChecked(target, true);
    });

    $(document).on('click', '.store-check-none-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const target = $(this).data('target');
        if (target) setStoreCheckboxGroupChecked(target, false);
    });

    function renderTagsHtml(values) {
        if (!Array.isArray(values) || !values.length) {
            return '<span class="text-muted">—</span>';
        }
        return '<div class="store-view-tags-wrap">' + values.map(v => `<span class="store-tag">${escapeHtml(v)}</span>`).join('') + '</div>';
    }

    function openStoreViewMore(data) {
        const $body = $('#storeViewMoreBody').empty();
        $('#storeViewMoreTitle').text(data.title || 'Details');

        if (data.layout === 'widgets' && data.sections) {
            const $wrap = $('<div class="store-view-widgets"></div>');
            (data.sections || []).forEach(section => {
                const widgets = (section.widgets || []).filter(w => w);
                if (!widgets.length) return;
                const $section = $(`
                    <div class="store-widget-section">
                        <div class="store-widget-section-title">${escapeHtml(section.title || '')}</div>
                        <div class="store-view-widget-grid"></div>
                    </div>
                `);
                const $grid = $section.find('.store-view-widget-grid');
                widgets.forEach(w => {
                    const dangerClass = w.danger ? ' widget-danger' : '';
                    const wideClass = w.type === 'tags' || w.wide ? ' widget-wide' : '';
                    let valueHtml;
                    if (w.type === 'tags') {
                        valueHtml = renderTagsHtml(w.value);
                    } else {
                        valueHtml = escapeHtml(w.value != null && w.value !== '' ? w.value : '—');
                    }
                    $grid.append(`
                        <div class="store-view-widget${dangerClass}${wideClass}">
                            <div class="widget-label">${escapeHtml(w.label)}</div>
                            <div class="widget-value">${valueHtml}</div>
                        </div>
                    `);
                });
                $wrap.append($section);
            });
            $body.append($wrap);
            $('#storeViewMoreModal').modal('show');
            return;
        }

        (data.fields || []).forEach(field => {
            let content;
            if (field.type === 'tags') {
                content = renderTagsHtml(field.value);
            } else {
                content = escapeHtml(field.value || '—');
            }
            $body.append(`
                <div class="store-view-field mb-3">
                    <div class="store-view-label">${escapeHtml(field.label)}</div>
                    <div class="store-view-value">${content}</div>
                </div>
            `);
        });

        if (data.units && data.units.length) {
            const $unitsBlock = $(`
                <div class="store-view-field mb-0 mt-3">
                    <div class="store-view-label">Manage Units</div>
                    <div class="store-view-value">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light"><tr><th>Unit</th><th>Abbreviation</th><th width="90">Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            `);
            const $tbody = $unitsBlock.find('tbody');
            data.units.forEach(u => {
                const $tr = $('<tr></tr>');
                $tr.append(`<td>${escapeHtml(u.unit_name)}</td>`);
                $tr.append(`<td>${escapeHtml(u.abbreviation || '—')}</td>`);
                const $actions = $('<td></td>');
                const $editBtn = $('<button type="button" class="btn btn-sm btn-link p-0 btn-edit-unit"><i class="fa fa-edit text-primary"></i></button>');
                $editBtn.data({ id: u.unitID, name: u.unit_name, abbr: u.abbreviation || '' });
                const $delBtn = $('<button type="button" class="btn btn-sm btn-link p-0 btn-delete-unit ml-2"><i class="fa fa-trash text-danger"></i></button>');
                $delBtn.data('id', u.unitID);
                $actions.append($editBtn, $delBtn);
                $tr.append($actions);
                $tbody.append($tr);
            });
            $body.append($unitsBlock);
        }

        $('#storeViewMoreModal').modal('show');
    }

    $(document).on('click', '.store-view-more-trigger', function (e) {
        e.preventDefault();
        $(this).closest('.dropdown').removeClass('show');
        let data = $(this).closest('tr').data('viewMore');
        if (!data) data = $(this).closest('.store-grid-card').data('viewMore');
        if (data) openStoreViewMore(data);
    });

    $(document).on('click', '.store-action-dropdown .dropdown-item', function () {
        $(this).closest('.dropdown').removeClass('show');
    });

    function filterUnitMeasures() {
        const query = ($('#unitMeasureSearch').val() || '').toLowerCase().trim();
        let visibleTypes = 0;

        $('#measurementTypesTable tbody tr.measurement-type-row').each(function () {
            const typeName = ($(this).data('type-name') || '').toString();
            const show = !query || typeName.includes(query);
            $(this).toggle(show).attr('data-filter-hidden', show ? null : '1');
            if (show) visibleTypes++;
        });

        const $count = $('#unitMeasureSearchCount');
        if (!query) {
            $count.text('');
            return;
        }
        $count.text(visibleTypes + ' measurement type(s) found');
        refreshStoreGridIfActive('measurementTypesTable');
    }

    $('#unitMeasureSearch').on('input', filterUnitMeasures);

    function refreshCategorySelects(category) {
        ['#itemCategorySelect', '#editItemCategory'].forEach(sel => {
            const $s = $(sel);
            if (!$s.find(`option[value="${category.categoryID}"]`).length) {
                $s.find(`option[value="${ADD_CATEGORY_OPT}"]`).before(
                    new Option(category.category_name, category.categoryID, false, false)
                );
            }
            ensureAddOptionLast($s, ADD_CATEGORY_OPT, '+ Add New Category');
        });
        initItemFormSelect2($('#itemCategorySelect, #editItemCategory'));
    }

    function refreshTypeSelects(type) {
        ['#itemMeasurementTypeSelect', '#editItemType'].forEach(sel => {
            const $s = $(sel);
            if (!$s.find(`option[value="${type.measurement_typeID}"]`).length) {
                $s.find(`option[value="${ADD_TYPE_OPT}"]`).before(
                    new Option(type.type_name, type.measurement_typeID, false, false)
                );
            }
            ensureAddOptionLast($s, ADD_TYPE_OPT, '+ Add New Measurement Type');
        });
        initItemFormSelect2($('#itemMeasurementTypeSelect, #editItemType'));
    }

    async function loadUnits(typeId, $unitSelect, selectedUnitIds, includeAddOption) {
        if (!typeId || isAddOption(typeId)) {
            $('#unitSelectGroup').hide();
            $unitSelect.empty();
            if ($unitSelect.hasClass('select2-hidden-accessible')) {
                $unitSelect.select2('destroy');
            }
            return;
        }
        try {
            const res = await fetch(routes.unitsByType(typeId), { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            $unitSelect.empty();
            (json.units || []).forEach(u => {
                $unitSelect.append(new Option(u.unit_name, u.unitID, false, false));
            });
            if (includeAddOption !== false && $unitSelect.attr('id') === 'itemUnitSelect') {
                ensureAddOptionLast($unitSelect, ADD_UNIT_OPT, '+ Add New Unit');
            }
            $('#unitSelectGroup').show();
            initUnitMultiSelect($unitSelect, selectedUnitIds || []);
        } catch (e) {
            showAlert(e.message, 'danger');
        }
    }

    function collectItemFormData(form) {
        const formData = new FormData(form);
        const unitIDs = ($('#itemUnitSelect').val() || []).filter(id => !isAddOption(id));
        const hasPackages = $('#hasPackagesCheck').is(':checked');
        const pkgResult = hasPackages ? collectPackageSpecs('packageSpecsBody') : { specs: [] };
        return {
            item_name: formData.get('item_name'),
            categoryID: formData.get('categoryID'),
            measurement_typeID: formData.get('measurement_typeID'),
            unitIDs: unitIDs,
            has_packages: hasPackages,
            package_specs: pkgResult.specs || [],
            package_specs_error: pkgResult.error || null,
        };
    }

    function collectEditItemFormData() {
        const unitIDs = ($('#editItemUnit').val() || []).filter(id => !isAddOption(id));
        const hasPackages = $('#editHasPackagesCheck').is(':checked');
        const pkgResult = hasPackages ? collectPackageSpecs('editPackageSpecsBody') : { specs: [] };
        return {
            item_name: $('#editItemName').val(),
            categoryID: $('#editItemCategory').val(),
            measurement_typeID: $('#editItemType').val(),
            unitIDs: unitIDs,
            has_packages: hasPackages,
            package_specs: pkgResult.specs || [],
            package_specs_error: pkgResult.error || null,
        };
    }

    function getItemUnitsMap(sourceSelectId) {
        const $sel = $(sourceSelectId);
        const map = {};
        ($sel.val() || []).forEach(id => {
            if (!id || isAddOption(id)) return;
            const text = $sel.find(`option[value="${id}"]`).text();
            map[id] = text;
        });
        return map;
    }

    function buildPackageOptionsHtml(selectedId, blockedIds) {
        const blocked = (blockedIds || []).map(String);
        let html = '<option value="">Search or select package...</option>';
        storePackagesList.forEach(p => {
            const id = String(p.packageID);
            const isBlocked = blocked.includes(id);
            const sel = id === String(selectedId || '') && !isBlocked ? ' selected' : '';
            const dis = isBlocked ? ' disabled' : '';
            const label = isBlocked
                ? escapeHtml(p.package_name) + ' (main)'
                : escapeHtml(p.package_name);
            html += `<option value="${p.packageID}"${sel}${dis} data-is-main="${isBlocked ? '1' : '0'}">${label}</option>`;
        });
        return html;
    }

    /** Package IDs already chosen above this level in the same chain. */
    function getAncestorPackageIds($level) {
        const ids = [];
        let $parent = $level.parent().closest('.pkg-level-block');
        while ($parent.length) {
            const id = $parent.find('> .form-row .pkg-spec-package').val();
            if (id) ids.push(String(id));
            $parent = $parent.parent().closest('.pkg-level-block');
        }
        return ids;
    }

    /** Blocked packages for a new level being mounted into $container (.pkg-child-wrap or .pkg-chain-levels). */
    function getBlockedPackageIdsForContainer($container) {
        const ids = [];
        let $parent = $container.closest('.pkg-level-block');
        while ($parent.length) {
            const id = $parent.find('> .form-row .pkg-spec-package').val();
            if (id) ids.push(String(id));
            $parent = $parent.parent().closest('.pkg-level-block');
        }
        return ids;
    }

    function refreshPackageOptionsForLevel($level) {
        const $pkgSelect = $level.find('> .form-row .pkg-spec-package');
        if (!$pkgSelect.length) return;

        const blocked = getAncestorPackageIds($level);
        let current = $pkgSelect.val();
        if (current && blocked.includes(String(current))) {
            current = '';
        }

        if ($pkgSelect.hasClass('select2-hidden-accessible')) {
            $pkgSelect.select2('destroy');
        }
        $pkgSelect.html(buildPackageOptionsHtml(current, blocked));
        initPackageLevelSelects($level.find('> .form-row'));

        $level.find('> .pkg-child-wrap > .pkg-level-block').each(function () {
            refreshPackageOptionsForLevel($(this));
        });
    }

    function buildUnitOptionsHtml(unitsMap, selectedId) {
        let html = '<option value="">Select unit...</option>';
        Object.entries(unitsMap).forEach(([id, name]) => {
            const sel = String(id) === String(selectedId) ? ' selected' : '';
            html += `<option value="${id}"${sel}>${escapeHtml(name)}</option>`;
        });
        return html;
    }

    function packageSelectDropdownParent($s) {
        const $wrap = $s.closest('.pkg-select-wrap');
        if ($wrap.length) return $wrap;
        const $modal = $s.closest('.modal');
        if ($modal.length) return $modal.find('.modal-content').first();
        return $(document.body);
    }

    function initPackageLevelSelects($scope) {
        $scope.find('.pkg-spec-package, .pkg-spec-unit, .pkg-spec-has-sub').each(function () {
            const $s = $(this);
            if ($s.hasClass('select2-hidden-accessible')) {
                $s.select2('destroy');
            }
            const opts = {
                theme: 'bootstrap-5',
                width: '100%',
                minimumResultsForSearch: $s.hasClass('pkg-spec-has-sub') ? Infinity : 0,
                dropdownParent: packageSelectDropdownParent($s),
            };
            if ($s.hasClass('pkg-spec-package')) {
                opts.templateResult = function (data) {
                    if (!data.id) return data.text;
                    const isMain = data.element && $(data.element).data('is-main') == '1';
                    if (isMain || (data.element && data.element.disabled)) {
                        const name = (data.text || '').replace(/\s*\(main\)\s*$/i, '');
                        return $('<span class="pkg-option-main"></span>')
                            .append(document.createTextNode(name))
                            .append('<span class="pkg-main-badge">main</span>');
                    }
                    return data.text;
                };
                opts.templateSelection = function (data) {
                    if (!data.id) return data.text;
                    return (data.text || '').replace(/\s*\(main\)\s*$/i, '');
                };
            }
            $s.select2(opts);
        });
    }

    function buildPackageLevelHtml(unitSourceSelectId, prefill, depth, blockedIds) {
        const unitsMap = getItemUnitsMap(unitSourceSelectId);
        const hasSub = prefill?.has_sub_package ? true : false;
        const isRoot = depth === 0;
        // Root+sub: no quantity. Child: quantity = count in parent. Root leaf: quantity = items.
        // Child leaf: quantity = count in parent, content_quantity = items.
        let qty = '';
        let contentQty = '';
        if (prefill) {
            if (isRoot && !hasSub) {
                qty = prefill.quantity ?? '';
            } else if (!isRoot) {
                qty = prefill.quantity ?? '';
                if (!hasSub) contentQty = prefill.content_quantity ?? '';
            }
        }
        const levelLabel = isRoot ? 'Top package' : ('Sub-package level ' + depth);
        const showQty = !(isRoot && hasSub);
        const showUnit = !hasSub;
        const showContentQty = !isRoot && !hasSub;

        return `
            <div class="pkg-level-block ${isRoot ? 'pkg-level-root' : ''}" data-depth="${depth}">
                <div class="pkg-level-title">${escapeHtml(levelLabel)}</div>
                <div class="form-row">
                    <div class="form-group col-md-4 pkg-select-wrap">
                        <label class="small font-weight-bold mb-1">Package Name <span class="text-danger">*</span></label>
                        <select class="form-control pkg-spec-package">${buildPackageOptionsHtml(prefill?.packageID, blockedIds)}</select>
                    </div>
                    <div class="form-group col-md-3 pkg-qty-wrap" style="${showQty ? '' : 'display:none;'}">
                        <label class="small font-weight-bold mb-1 pkg-qty-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control pkg-spec-qty" min="0.0001" step="any" value="${qty}" placeholder="e.g. 10">
                    </div>
                    <div class="form-group col-md-5 pkg-select-wrap">
                        <label class="small font-weight-bold mb-1">Are there sub packages in this package? <span class="text-danger">*</span></label>
                        <select class="form-control pkg-spec-has-sub">
                            <option value="no"${!hasSub ? ' selected' : ''}>No</option>
                            <option value="yes"${hasSub ? ' selected' : ''}>Yes</option>
                        </select>
                    </div>
                </div>
                <div class="pkg-unit-wrap" style="${showUnit ? '' : 'display:none;'}">
                    <div class="form-row">
                        <div class="form-group col-md-6 pkg-select-wrap">
                            <label class="small font-weight-bold mb-1">Unit of Measure <span class="text-danger">*</span></label>
                            <select class="form-control pkg-spec-unit">${buildUnitOptionsHtml(unitsMap, prefill?.unitID)}</select>
                        </div>
                        <div class="form-group col-md-6 pkg-content-qty-wrap" style="${showContentQty ? '' : 'display:none;'}">
                            <label class="small font-weight-bold mb-1">Items inside this package <span class="text-danger">*</span></label>
                            <input type="number" class="form-control pkg-spec-content-qty" min="0.0001" step="any" value="${contentQty}" placeholder="e.g. 50">
                        </div>
                    </div>
                </div>
                <div class="pkg-child-wrap"></div>
            </div>
        `;
    }

    function updatePackageLevelFields($level) {
        const depth = parseInt($level.data('depth'), 10) || 0;
        const hasSub = $level.find('> .form-row .pkg-spec-has-sub').val() === 'yes';
        const isRoot = depth === 0;
        const $qtyWrap = $level.find('> .form-row .pkg-qty-wrap');
        const $unitWrap = $level.find('> .pkg-unit-wrap');
        const $contentWrap = $level.find('> .pkg-unit-wrap .pkg-content-qty-wrap');

        if (isRoot && hasSub) {
            $qtyWrap.hide();
            $qtyWrap.find('.pkg-spec-qty').val('');
        } else {
            $qtyWrap.show();
            $level.find('> .form-row .pkg-qty-label').text(
                isRoot ? 'Quantity of items *' : 'Quantity inside parent package *'
            );
        }

        if (hasSub) {
            $unitWrap.hide();
            $unitWrap.find('.pkg-spec-unit').val('').trigger('change');
            $unitWrap.find('.pkg-spec-content-qty').val('');
        } else {
            $unitWrap.show();
            if (!isRoot) {
                $contentWrap.show();
            } else {
                $contentWrap.hide();
                $contentWrap.find('.pkg-spec-content-qty').val('');
            }
            initPackageLevelSelects($unitWrap);
        }
    }

    function packageNameById(id) {
        const pkg = storePackagesList.find(p => String(p.packageID) === String(id));
        return pkg ? pkg.package_name : '';
    }

    function unitNameById(unitSourceSelectId, id) {
        return $(unitSourceSelectId).find(`option[value="${id}"]`).text() || 'item';
    }

    function formatChainQty(n) {
        const num = parseFloat(n);
        if (!num && num !== 0) return '';
        return Number.isInteger(num) ? String(num) : String(num);
    }

    function buildChainPreviewFromCard($card, unitSourceSelectId) {
        const $root = $card.find('> .pkg-chain-levels > .pkg-level-block').first();
        if (!$root.length) return null;

        const lines = [];
        const widgets = [];
        let $level = $root;
        let factor = 1;
        let topName = '';

        while ($level && $level.length) {
            const depth = parseInt($level.data('depth'), 10) || 0;
            const name = packageNameById($level.find('> .form-row .pkg-spec-package').val());
            const hasSub = $level.find('> .form-row .pkg-spec-has-sub').val() === 'yes';
            const qty = parseFloat($level.find('> .form-row .pkg-spec-qty').val());
            const contentQty = parseFloat($level.find('> .pkg-unit-wrap .pkg-spec-content-qty').val());
            const unitID = $level.find('> .pkg-unit-wrap .pkg-spec-unit').val();
            const unitName = unitNameById(unitSourceSelectId, unitID);

            if (!name) break;

            if (depth === 0) {
                topName = name;
                widgets.push({ label: name, value: 1 });
            }

            if (hasSub) {
                const $child = $level.find('> .pkg-child-wrap > .pkg-level-block').first();
                if (!$child.length) break;
                const childName = packageNameById($child.find('> .form-row .pkg-spec-package').val());
                const childQty = parseFloat($child.find('> .form-row .pkg-spec-qty').val());
                if (childName && childQty > 0) {
                    lines.push({ parent: name, qty: childQty, child: childName });
                    factor = factor * childQty;
                    widgets.push({ label: childName, value: factor });
                }
                $level = $child;
            } else {
                const items = depth === 0 ? qty : contentQty;
                if (items > 0 && unitID) {
                    lines.push({ parent: name, qty: items, child: unitName });
                    const itemTotal = depth === 0 ? items : factor * items;
                    widgets.push({ label: unitName, value: itemTotal });
                }
                break;
            }
        }

        if (!lines.length) return null;
        return { topName, lines, widgets };
    }

    function renderChainPreviewHtml(preview, compact) {
        if (!preview) return '';
        let html = '';
        if (!compact) {
            html += '<div class="pkg-live-widgets mb-2">';
            preview.widgets.forEach(w => {
                html += `<div class="pkg-live-widget">
                    <div class="pkg-live-value">${formatChainQty(w.value)}</div>
                    <div class="pkg-live-label">${escapeHtml(w.label)}</div>
                </div>`;
            });
            html += '</div>';
        }
        html += '<div class="pkg-live-flow">';
        preview.lines.forEach((line, i) => {
            html += `<span>${escapeHtml(line.parent)} ina <strong>${formatChainQty(line.qty)}</strong> ${escapeHtml(line.child)}</span>`;
            if (i < preview.lines.length - 1) html += ' <span class="text-muted">·</span> ';
        });
        html += '</div>';
        return html;
    }

    function updatePackageChainSummary($card, unitSourceSelectId) {
        const $summary = $card.find('.pkg-chain-summary');
        const preview = buildChainPreviewFromCard($card, unitSourceSelectId);
        if (!preview) {
            $summary.hide().empty();
        } else {
            $summary.html(
                '<div class="pkg-live-dashboard-title mb-2"><i class="fa fa-dashboard"></i> Live preview</div>' +
                renderChainPreviewHtml(preview, false)
            ).show();
        }

        const bodyId = $card.parent().attr('id');
        if (bodyId) updatePackageLiveDashboard(bodyId, unitSourceSelectId);
    }

    function packageDashboardId(bodyId) {
        if (bodyId === 'packageSpecsBody') return '#packageLiveDashboard';
        if (bodyId === 'editPackageSpecsBody') return '#editPackageLiveDashboard';
        return null;
    }

    function packageUnitSourceForBody(bodyId) {
        return bodyId === 'editPackageSpecsBody' ? '#editItemUnit' : '#itemUnitSelect';
    }

    function updatePackageLiveDashboard(bodyId, unitSourceSelectId) {
        const dashId = packageDashboardId(bodyId);
        if (!dashId) return;
        const $dash = $(dashId);
        const previews = [];

        $(`#${bodyId} .pkg-chain-card`).each(function (i) {
            const preview = buildChainPreviewFromCard($(this), unitSourceSelectId || packageUnitSourceForBody(bodyId));
            if (preview) {
                preview.chainNo = i + 1;
                previews.push(preview);
            }
        });

        if (!previews.length) {
            $dash.hide().empty();
            return;
        }

        let html = '<div class="pkg-live-dashboard-title"><i class="fa fa-dashboard"></i> Realtime package dashboard</div>';
        previews.forEach(preview => {
            html += `<div class="pkg-live-chain-block">
                <div class="pkg-live-chain-name">Chain #${preview.chainNo}: ${escapeHtml(preview.topName || 'Package')}</div>
                ${renderChainPreviewHtml(preview, false)}
            </div>`;
        });
        $dash.html(html).show();
    }

    function bindPackageChainSummary($card, unitSourceSelectId) {
        $card.off('change.pkgsummary input.pkgsummary select2:select.pkgsummary select2:clear.pkgsummary')
            .on('change.pkgsummary input.pkgsummary select2:select.pkgsummary select2:clear.pkgsummary',
                '.pkg-spec-package, .pkg-spec-has-sub, .pkg-spec-unit, .pkg-spec-qty, .pkg-spec-content-qty',
                function () {
                    updatePackageChainSummary($card, unitSourceSelectId);
                });
        updatePackageChainSummary($card, unitSourceSelectId);
    }

    function bindPackageLevelEvents($level, bodyId, unitSourceSelectId) {
        initPackageLevelSelects($level);
        updatePackageLevelFields($level);

        $level.find('> .form-row .pkg-spec-package').off('change.pkgblock').on('change.pkgblock', function () {
            // Parent package changed — refresh sub-package options (block ancestors as main).
            $level.find('> .pkg-child-wrap > .pkg-level-block').each(function () {
                refreshPackageOptionsForLevel($(this));
            });
            const $card = $level.closest('.pkg-chain-card');
            updatePackageChainSummary($card, unitSourceSelectId);
        });

        $level.find('> .form-row .pkg-spec-has-sub').off('change.pkgchain').on('change.pkgchain', function () {
            const hasSub = $(this).val() === 'yes';
            const $childWrap = $level.find('> .pkg-child-wrap');
            updatePackageLevelFields($level);

            if (hasSub) {
                if (!$childWrap.children('.pkg-level-block').length) {
                    const depth = parseInt($level.data('depth'), 10) + 1;
                    mountPackageLevel($childWrap, unitSourceSelectId, null, depth, bodyId);
                } else {
                    $childWrap.children('.pkg-level-block').each(function () {
                        refreshPackageOptionsForLevel($(this));
                    });
                }
            } else {
                $childWrap.empty();
            }

            const $card = $level.closest('.pkg-chain-card');
            updatePackageChainSummary($card, unitSourceSelectId);
        });
    }

    function mountPackageLevel($container, unitSourceSelectId, prefill, depth, bodyId) {
        const blockedIds = getBlockedPackageIdsForContainer($container);
        const $level = $(buildPackageLevelHtml(unitSourceSelectId, prefill, depth, blockedIds));
        $container.append($level);
        bindPackageLevelEvents($level, bodyId, unitSourceSelectId);
        if (prefill && prefill.has_sub_package && prefill.child) {
            mountPackageLevel($level.find('> .pkg-child-wrap'), unitSourceSelectId, prefill.child, depth + 1, bodyId);
        }
        return $level;
    }

    function addPackageSpecRow(bodyId, unitSourceSelectId, prefill) {
        const unitsMap = getItemUnitsMap(unitSourceSelectId);
        if (!Object.keys(unitsMap).length) {
            showAlert('Select item units of measure first.', 'danger');
            return;
        }

        const chainNo = $(`#${bodyId} .pkg-chain-card`).length + 1;
        const $card = $(`
            <div class="pkg-chain-card package-spec-row">
                <div class="pkg-chain-card-head">
                    <span>Package Chain #${chainNo}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pkg-row" title="Remove chain">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="pkg-chain-summary border p-2 mb-2" style="display:none;background:#fffafa;"></div>
                <div class="pkg-chain-levels"></div>
            </div>
        `);

        $(`#${bodyId}`).append($card);
        mountPackageLevel($card.find('.pkg-chain-levels'), unitSourceSelectId, prefill, 0, bodyId);
        bindPackageChainSummary($card, unitSourceSelectId);
        renumberPackageChains(bodyId);
    }

    function renumberPackageChains(bodyId) {
        $(`#${bodyId} .pkg-chain-card`).each(function (i) {
            $(this).find('.pkg-chain-card-head span').first().text('Package Chain #' + (i + 1));
        });
    }

    function refreshPackageSpecRowUnits(bodyId, unitSourceSelectId) {
        const unitsMap = getItemUnitsMap(unitSourceSelectId);
        $(`#${bodyId} .pkg-spec-unit`).each(function () {
            const $unitSel = $(this);
            const current = $unitSel.val();
            if ($unitSel.hasClass('select2-hidden-accessible')) {
                $unitSel.select2('destroy');
            }
            $unitSel.html(buildUnitOptionsHtml(unitsMap, current));
        });
        $(`#${bodyId} .pkg-chain-card`).each(function () {
            initPackageLevelSelects($(this));
            bindPackageChainSummary($(this), unitSourceSelectId);
        });
    }

    function collectLevelSpec($level) {
        const depth = parseInt($level.data('depth'), 10) || 0;
        const packageID = $level.find('> .form-row .pkg-spec-package').val();
        const quantity = $level.find('> .form-row .pkg-spec-qty').val();
        const contentQuantity = $level.find('> .pkg-unit-wrap .pkg-spec-content-qty').val();
        const hasSub = $level.find('> .form-row .pkg-spec-has-sub').val() === 'yes';
        if (!packageID) return null;

        const node = {
            packageID: parseInt(packageID, 10),
            has_sub_package: hasSub,
        };

        if (hasSub) {
            if (depth > 0) {
                if (!quantity) return null;
                node.quantity = parseFloat(quantity);
            }
            const $child = $level.find('> .pkg-child-wrap > .pkg-level-block').first();
            if (!$child.length) return null;
            const child = collectLevelSpec($child);
            if (!child) return null;
            node.child = child;
        } else {
            const unitID = $level.find('> .pkg-unit-wrap .pkg-spec-unit').val();
            if (!unitID || !quantity) return null;
            node.unitID = parseInt(unitID, 10);
            node.quantity = parseFloat(quantity);
            if (depth > 0) {
                if (!contentQuantity) return null;
                node.content_quantity = parseFloat(contentQuantity);
            }
        }
        return node;
    }

    function collectPackageSpecs(bodyId) {
        const cards = $(`#${bodyId} .pkg-chain-card`);
        if (!cards.length) {
            return { error: 'Add at least one package chain.', specs: [] };
        }

        const specs = [];
        let error = null;
        cards.each(function (i) {
            if (error) return;
            const $root = $(this).find('> .pkg-chain-levels > .pkg-level-block').first();
            const node = collectLevelSpec($root);
            if (!node) {
                error = 'Package chain #' + (i + 1) + ' is incomplete. Fill package name, quantities, and unit measure where required.';
                return;
            }
            specs.push(node);
        });

        if (error) return { error, specs: [] };
        return { specs };
    }

    function clearPackageSpecs(bodyId) {
        $(`#${bodyId}`).empty();
        const dashId = packageDashboardId(bodyId);
        if (dashId) $(dashId).hide().empty();
    }

    function normalizePackagePrefill(spec) {
        if (!spec) return null;
        return {
            packageID: spec.packageID != null ? parseInt(spec.packageID, 10) : null,
            quantity: spec.quantity != null && spec.quantity !== '' ? parseFloat(spec.quantity) : null,
            content_quantity: spec.content_quantity != null && spec.content_quantity !== ''
                ? parseFloat(spec.content_quantity)
                : null,
            has_sub_package: !!spec.has_sub_package,
            unitID: spec.unitID != null && spec.unitID !== '' ? parseInt(spec.unitID, 10) : null,
            child: spec.child ? normalizePackagePrefill(spec.child) : null,
        };
    }

    function loadPackageSpecs(bodyId, unitSourceSelectId, specs) {
        clearPackageSpecs(bodyId);
        const list = Array.isArray(specs) ? specs : [];
        list.forEach(spec => addPackageSpecRow(bodyId, unitSourceSelectId, normalizePackagePrefill(spec)));
    }

    function rememberItemPackageSpecs(item) {
        if (!item || !item.itemID) return;
        itemPackageSpecsMap[String(item.itemID)] = item.package_specs_payload || [];
    }

    // Tab navigation
    const storeSectionTableMap = {
        'section-items': 'itemsTable',
        'section-stock': 'stockTable',
        'section-requisitions': 'requisitionsTable',
        'section-damaged': 'damagedItemsTable',
        'section-lost': 'lostItemsTable',
        'section-categories': 'categoriesTable',
        'section-measurements': 'measurementTypesTable',
        'section-packages': 'packagesTable',
    };

    const DASHBOARD_WIDGET_LIMIT = 3;
    const dashboardPanelLists = {};

    function dashboardViewMoreFooter(totalCount, panelKey) {
        if (totalCount <= DASHBOARD_WIDGET_LIMIT) return '';
        const remaining = totalCount - DASHBOARD_WIDGET_LIMIT;
        return `<div class="store-dashboard-view-more-wrap">
            <button type="button" class="btn btn-link btn-sm store-dashboard-view-more p-0" data-panel="${panelKey}">View more (${remaining})</button>
            <button type="button" class="btn btn-link btn-sm store-dashboard-view-less p-0" data-panel="${panelKey}" style="display:none;">Show less</button>
        </div>`;
    }

    function renderDashboardWidgetPanel(containerSelector, panelKey, items, renderItemFn, emptyHtml) {
        const $el = $(containerSelector);
        dashboardPanelLists[panelKey] = { items: items || [], renderItemFn };
        $el.removeClass('is-expanded');
        if (!items || !items.length) {
            $el.html(emptyHtml);
            return;
        }
        const initial = items.slice(0, DASHBOARD_WIDGET_LIMIT).map(renderItemFn).join('');
        $el.html(initial + dashboardViewMoreFooter(items.length, panelKey));
    }

    const dashboardPanelMap = {
        recent: '#dashboardRecentRequisitions',
        overdue: '#dashboardOverdueList',
        categories: '#dashboardCategoryBreakdown',
        top: '#dashboardTopIssued',
    };

    $(document).on('click', '.store-dashboard-view-more', function () {
        const panel = $(this).data('panel');
        const cfg = dashboardPanelLists[panel];
        const $body = $(dashboardPanelMap[panel]);
        if (!$body.length || !cfg || !cfg.items.length) return;
        const extra = cfg.items.slice(DASHBOARD_WIDGET_LIMIT).map(cfg.renderItemFn).join('');
        $body.find('.store-dashboard-view-more-wrap').before(extra);
        $body.addClass('is-expanded');
        $body.find('.store-dashboard-view-more').hide();
        $body.find('.store-dashboard-view-less').show();
    });

    $(document).on('click', '.store-dashboard-view-less', function () {
        const panel = $(this).data('panel');
        const cfg = dashboardPanelLists[panel];
        const $body = $(dashboardPanelMap[panel]);
        if (!$body.length || !cfg) return;
        const initial = cfg.items.slice(0, DASHBOARD_WIDGET_LIMIT).map(cfg.renderItemFn).join('');
        $body.removeClass('is-expanded').html(initial + dashboardViewMoreFooter(cfg.items.length, panel));
    });

    function renderStoreDashboard(data) {
        if (!data) return;
        storeDashboardData = data;
        $('#dashboardGeneratedAt').text(data.generated_at || '—');
        const filterLabel = data.filter_label || 'All Categories';
        $('#dashboardFilterBadge').text(
            filterLabel !== 'All Categories' ? '— ' + filterLabel : ''
        );
        $('#dashboardCategoryPanelTitle').html(
            data.categoryID
                ? '<i class="fa fa-cubes"></i> Items in ' + escapeHtml(data.category_name || filterLabel) + ' (labels in stock)'
                : '<i class="fa fa-tags"></i> Items by Category'
        );
        const s = data.summary || {};

        $('#storeDashboardSummary').html(
            '<div class="store-report-stat"><div class="stat-value">' + (s.registered_items || 0) + '</div><div class="stat-label">Registered Items</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.items_with_stock || 0) + '</div><div class="stat-label">Items In Stock</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.labels_in_stock || 0) + '</div><div class="stat-label">Labels Available</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + escapeHtml(s.total_remaining_items || '0') + '</div><div class="stat-label">Total Items Remaining</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.issued_this_month || 0) + '</div><div class="stat-label">Issued This Month</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.pending_returns || 0) + '</div><div class="stat-label">Pending Returns</div></div>' +
            '<div class="store-report-stat"><div class="stat-value text-danger">' + (s.overdue_returns || 0) + '</div><div class="stat-label">Overdue Returns</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.labels_defective || 0) + '</div><div class="stat-label">Defective Labels</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.damaged_records || 0) + '</div><div class="stat-label">Damaged Records</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (s.lost_records || 0) + '</div><div class="stat-label">Lost Records</div></div>'
        );

        const recent = data.recent_requisitions || [];
        renderDashboardWidgetPanel(
            '#dashboardRecentRequisitions',
            'recent',
            recent,
            r => {
                const statusBadge = r.status === 'returned'
                    ? '<span class="store-badge-pill store-badge-return-yes">Returned</span>'
                    : (r.is_overdue
                        ? '<span class="store-badge-pill store-badge-overdue">Overdue</span>'
                        : '<span class="store-badge-pill store-badge-pending">Issued</span>');
                return `<div class="store-dashboard-list-item">
                    <div>
                        <strong>${escapeHtml(r.item_name)}</strong>
                        <div class="text-muted small">${escapeHtml(r.recipient_name)} · ${escapeHtml(r.label)}</div>
                        <div class="text-muted small">${escapeHtml(r.take_display || '')}</div>
                    </div>
                    <div class="text-right">${statusBadge}<div class="small text-muted">${escapeHtml(r.issued_at)}</div></div>
                </div>`;
            },
            '<div class="store-dashboard-empty">No requisitions yet.</div>'
        );

        const overdue = data.overdue_list || [];
        renderDashboardWidgetPanel(
            '#dashboardOverdueList',
            'overdue',
            overdue,
            r => `<div class="store-dashboard-list-item">
                <div>
                    <strong>${escapeHtml(r.item_name)}</strong>
                    <div class="text-muted small">${escapeHtml(r.recipient_name)} · ${escapeHtml(r.label)}</div>
                </div>
                <div class="text-right text-danger small">
                    <strong>${r.days_overdue}d late</strong><br>
                    Due ${escapeHtml(r.expected_return_date)}
                </div>
            </div>`,
            '<div class="store-dashboard-empty">No overdue returns.</div>'
        );

        const cats = data.category_breakdown || [];
        renderDashboardWidgetPanel(
            '#dashboardCategoryBreakdown',
            'categories',
            cats,
            c => `<div class="store-dashboard-list-item">
                <span>${escapeHtml(c.category_name)}</span>
                <strong>${c.item_count}</strong>
            </div>`,
            '<div class="store-dashboard-empty">No categories with items.</div>'
        );

        const top = data.top_issued_this_month || [];
        renderDashboardWidgetPanel(
            '#dashboardTopIssued',
            'top',
            top,
            t => `<div class="store-dashboard-list-item">
                <span>${escapeHtml(t.item_name)}</span>
                <strong>${t.issue_count}</strong>
            </div>`,
            '<div class="store-dashboard-empty">No issues this month.</div>'
        );
    }

    async function loadStoreDashboard(refresh) {
        const categoryID = $('#dashboardCategoryFilter').val();
        if (!refresh && storeDashboardData && storeDashboardData.summary
            && String(storeDashboardData.categoryID || '') === String(categoryID || '')) {
            renderStoreDashboard(storeDashboardData);
            return;
        }
        try {
            const params = categoryID ? '?categoryID=' + encodeURIComponent(categoryID) : '';
            const json = await getJson(routes.storeDashboard + params);
            if (json.dashboard) {
                renderStoreDashboard(json.dashboard);
            }
        } catch (err) {
            if (storeDashboardData && storeDashboardData.summary) {
                renderStoreDashboard(storeDashboardData);
            }
        }
    }

    document.querySelectorAll('.store-dash-goto').forEach(btn => {
        btn.addEventListener('click', function () {
            const sectionId = this.dataset.section;
            const tab = document.querySelector('.store-menu .list-group-item[data-section="' + sectionId + '"]');
            if (tab) tab.click();
        });
    });

    $('#btnRefreshDashboard').on('click', function () {
        loadStoreDashboard(true);
    });

    $('#dashboardCategoryFilter').on('change', function () {
        loadStoreDashboard(true);
    });

    document.querySelectorAll('.store-menu .list-group-item').forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.store-menu .list-group-item').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.store-section').forEach(s => s.classList.remove('active'));
            const sectionId = this.dataset.section;
            document.getElementById(sectionId).classList.add('active');
            if (sectionId === 'section-requisitions') {
                markItemRequestsRead();
            }
            if (sectionId === 'section-dashboard') {
                loadStoreDashboard(false);
            }
            if (sectionId === 'section-report') {
                initReportWizard();
            }
            const tableId = storeSectionTableMap[sectionId];
            if (tableId) {
                adjustStoreDataTable(tableId);
                if ($('.store-data-wrapper[data-table-id="' + tableId + '"]').attr('data-active-view') === 'grid') {
                    buildStoreGrid(tableId);
                }
            }
        });
    });

    // Add-item select: open modals from last dropdown options
    $('#itemCategorySelect').on('change', function () {
        if ($(this).val() !== ADD_CATEGORY_OPT) return;
        returnToAddItemAfter = 'category';
        $(this).val('').trigger('change.select2');
        $('#addItemModal').modal('hide');
        $('#addCategoryModal').modal('show');
    });

    $('#itemMeasurementTypeSelect').on('change', function () {
        const val = $(this).val();
        if (val === ADD_TYPE_OPT) {
            returnToAddItemAfter = 'type';
            $(this).val('').trigger('change.select2');
            $('#addItemModal').modal('hide');
            $('#addMeasurementTypeModal').modal('show');
            return;
        }
        loadUnits(val, $('#itemUnitSelect'), []);
    });

    $('#itemUnitSelect').on('select2:select', function (e) {
        if (e.params.data.id !== ADD_UNIT_OPT) return;
        const current = ($(this).val() || []).filter(id => !isAddOption(id));
        $(this).val(current).trigger('change');
        openAddUnitFromItemForm();
    });

    $('#itemUnitSelect').on('change', function () {
        if ($('#hasPackagesCheck').is(':checked')) {
            refreshPackageSpecRowUnits('packageSpecsBody', '#itemUnitSelect');
        }
    });

    $('#editItemUnit').on('change', function () {
        if ($('#editHasPackagesCheck').is(':checked')) {
            refreshPackageSpecRowUnits('editPackageSpecsBody', '#editItemUnit');
        }
    });

    $('#hasPackagesCheck').on('change', function () {
        if ($(this).is(':checked')) {
            $('#packageSpecsSection').show();
            if (!$('#packageSpecsBody .package-spec-row').length) {
                addPackageSpecRow('packageSpecsBody', '#itemUnitSelect', null);
            }
        } else {
            $('#packageSpecsSection').hide();
            clearPackageSpecs('packageSpecsBody');
        }
    });

    $('#editHasPackagesCheck').on('change', function () {
        if ($(this).is(':checked')) {
            $('#editPackageSpecsSection').show();
            if (!$('#editPackageSpecsBody .package-spec-row').length) {
                addPackageSpecRow('editPackageSpecsBody', '#editItemUnit', null);
            }
        } else {
            $('#editPackageSpecsSection').hide();
            clearPackageSpecs('editPackageSpecsBody');
        }
    });

    $('#btnAddPackageSpecRow').on('click', function () {
        addPackageSpecRow('packageSpecsBody', '#itemUnitSelect', null);
    });

    $('#btnEditAddPackageSpecRow').on('click', function () {
        addPackageSpecRow('editPackageSpecsBody', '#editItemUnit', null);
    });

    $(document).on('click', '.btn-remove-pkg-row', function () {
        const $card = $(this).closest('.pkg-chain-card');
        const bodyId = $card.parent().attr('id');
        $card.remove();
        if (bodyId) {
            renumberPackageChains(bodyId);
            updatePackageLiveDashboard(bodyId, packageUnitSourceForBody(bodyId));
        }
    });

    $('#btnManagePackagesFromItem').on('click', function () {
        $('#addItemModal').modal('hide');
        document.querySelectorAll('.store-menu .list-group-item').forEach(t => t.classList.remove('active'));
        document.querySelector('[data-section="section-packages"]').classList.add('active');
        document.querySelectorAll('.store-section').forEach(s => s.classList.remove('active'));
        document.getElementById('section-packages').classList.add('active');
        $('#addPackagesModal').modal('show');
    });

    $('#editItemType').on('change', function () {
        loadUnits($(this).val(), $('#editItemUnit'), [], false);
    });

    // Add item
    $('#addItemForm').on('submit', async function (e) {
        e.preventDefault();
        const data = collectItemFormData(this);
        if (isAddOption(data.categoryID) || isAddOption(data.measurement_typeID)) {
            showAlert('Please select a valid category and measurement type.', 'danger');
            return;
        }
        if (!data.unitIDs.length) {
            showAlert('Select at least one unit of measure.', 'danger');
            return;
        }
        if (data.has_packages && data.package_specs_error) {
            showAlert(data.package_specs_error, 'danger');
            return;
        }
        if (data.has_packages && !data.package_specs.length) {
            showAlert('Add at least one complete package chain.', 'danger');
            return;
        }
        try {
            const payload = { ...data };
            delete payload.package_specs_error;
            const json = await postJson(routes.itemsStore, payload);
            showAlert(json.message, 'success');
            $('#addItemModal').modal('hide');
            this.reset();
            $('#itemUnitSelect').empty();
            $('#unitSelectGroup').hide();
            $('#hasPackagesCheck').prop('checked', false);
            $('#packageSpecsSection').hide();
            clearPackageSpecs('packageSpecsBody');
            upsertStoreTableRow('itemsTable', `tr[data-item-id="${json.item.itemID}"]`, buildItemRow(json.item));
            syncStoreItemsList(json.item);
            rememberItemPackageSpecs(json.item);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Edit item
    $(document).on('click', '.btn-edit-item', async function () {
        const btn = $(this);
        const itemId = String(btn.data('id'));
        const unitIds = String(btn.data('units') || '').split(',').filter(Boolean);
        const packageSpecs = Array.isArray(itemPackageSpecsMap[itemId])
            ? JSON.parse(JSON.stringify(itemPackageSpecsMap[itemId]))
            : [];
        $('#editItemId').val(itemId);
        $('#editItemName').val(btn.data('name'));
        $('#editItemCategory').val(btn.data('category'));
        $('#editItemType').val(btn.data('type'));
        const hasPackages = String(btn.data('has-packages')) === '1';
        $('#editHasPackagesCheck').prop('checked', hasPackages);
        initItemFormSelect2($('#editItemCategory, #editItemType'));
        await loadUnits(btn.data('type'), $('#editItemUnit'), unitIds, false);
        if (hasPackages) {
            $('#editPackageSpecsSection').show();
            loadPackageSpecs('editPackageSpecsBody', '#editItemUnit', packageSpecs);
        } else {
            $('#editPackageSpecsSection').hide();
            clearPackageSpecs('editPackageSpecsBody');
        }
        $('#editItemModal').modal('show');
    });

    $('#editItemForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#editItemId').val();
        const data = collectEditItemFormData();
        if (!data.unitIDs.length) {
            showAlert('Select at least one unit of measure.', 'danger');
            return;
        }
        if (data.has_packages && data.package_specs_error) {
            showAlert(data.package_specs_error, 'danger');
            return;
        }
        if (data.has_packages && !data.package_specs.length) {
            showAlert('Add at least one complete package chain.', 'danger');
            return;
        }
        try {
            const payload = { ...data };
            delete payload.package_specs_error;
            const json = await postJson(routes.itemsUpdate(id), payload);
            showAlert(json.message, 'success');
            $('#editItemModal').modal('hide');
            upsertStoreTableRow('itemsTable', `tr[data-item-id="${json.item.itemID}"]`, buildItemRow(json.item));
            syncStoreItemsList(json.item);
            rememberItemPackageSpecs(json.item);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-item', async function () {
        if (!(await storeConfirm('Delete this store item?', 'Delete item'))) return;
        const id = $(this).data('id');
        try {
            const json = await postJson(routes.itemsDelete(id), {});
            showAlert(json.message, 'success');
            removeStoreTableRow('itemsTable', `tr[data-item-id="${id}"]`);
            removeFromStoreItemsList(id);
            delete itemPackageSpecsMap[String(id)];
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Categories
    $('#addCategoryForm').on('submit', async function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        try {
            const json = await postJson(routes.categoriesStore, data);
            showAlert(json.message, 'success');
            $('#addCategoryModal').modal('hide');
            this.reset();
            refreshCategorySelects(json.category);
            const row = document.createElement('tr');
            row.dataset.categoryId = json.category.categoryID;
            const catViewMore = {
                title: json.category.category_name,
                fields: [
                    { label: 'Category Name', value: json.category.category_name },
                    { label: 'Status', value: 'Active' },
                ],
            };
            row.dataset.viewMore = JSON.stringify(catViewMore);
            row.innerHTML = `<td></td><td class="cat-name font-weight-bold">${escapeHtml(json.category.category_name)}</td>
                <td class="store-actions-cell">
                    <div class="dropdown store-action-dropdown">
                        <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                            <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                            <button type="button" class="dropdown-item btn-edit-category" data-id="${json.category.categoryID}" data-name="${escapeHtml(json.category.category_name)}"><i class="fa fa-edit text-primary"></i> Edit</button>
                            <button type="button" class="dropdown-item text-danger btn-delete-category" data-id="${json.category.categoryID}"><i class="fa fa-trash"></i> Delete</button>
                        </div>
                    </div>
                </td>`;
            addStoreTableRow('categoriesTable', row);
            refreshStoreGridIfActive('categoriesTable');
            if (returnToAddItemAfter === 'category') {
                returnToAddItemAfter = null;
                $('#addItemModal').modal('show');
                $('#itemCategorySelect').val(json.category.categoryID).trigger('change');
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-edit-category', function () {
        $('#editCategoryId').val($(this).data('id'));
        $('#editCategoryName').val($(this).data('name'));
        $('#editCategoryModal').modal('show');
    });

    $('#editCategoryForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#editCategoryId').val();
        try {
            const json = await postJson(routes.categoriesUpdate(id), { category_name: $('#editCategoryName').val() });
            showAlert(json.message, 'success');
            const $tr = $(`tr[data-category-id="${id}"]`);
            $tr.find('.cat-name').text(json.category.category_name);
            $tr.find('.btn-edit-category').attr('data-name', json.category.category_name).data('name', json.category.category_name);
            $tr.attr('data-view-more', JSON.stringify({
                title: json.category.category_name,
                fields: [
                    { label: 'Category Name', value: json.category.category_name },
                    { label: 'Status', value: 'Active' },
                ],
            }));
            if ($.fn.DataTable.isDataTable('#categoriesTable')) {
                $('#categoriesTable').DataTable().row($tr).invalidate().draw(false);
            }
            refreshStoreGridIfActive('categoriesTable');
            refreshCategorySelects(json.category);
            $('#editCategoryModal').modal('hide');
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-category', async function () {
        if (!(await storeConfirm('Delete this category?', 'Delete category'))) return;
        const id = $(this).data('id');
        try {
            const json = await postJson(routes.categoriesDelete(id), {});
            showAlert(json.message, 'success');
            removeStoreTableRow('categoriesTable', `tr[data-category-id="${id}"]`);
            $(`#itemCategorySelect option[value="${id}"], #editItemCategory option[value="${id}"]`).remove();
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Measurement types
    $('#addMeasurementTypeForm').on('submit', async function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        try {
            const json = await postJson(routes.typesStore, data);
            showAlert(json.message, 'success');
            $('#addMeasurementTypeModal').modal('hide');
            this.reset();
            const type = {
                measurement_typeID: json.measurement_type.measurement_typeID,
                type_name: json.measurement_type.type_name,
                is_system: !!json.measurement_type.is_system,
                units: [],
            };
            measurementTypesState.push(type);
            upsertStoreTableRow('measurementTypesTable', `tr[data-type-id="${type.measurement_typeID}"]`, buildMeasurementTypeRow(type));
            refreshTypeSelects(json.measurement_type);
            if (returnToAddItemAfter === 'type') {
                returnToAddItemAfter = null;
                $('#addItemModal').modal('show');
                $('#itemMeasurementTypeSelect').val(json.measurement_type.measurement_typeID).trigger('change');
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-edit-type', function () {
        $('#editTypeId').val($(this).data('id'));
        $('#editTypeName').val($(this).data('name'));
        $('#editMeasurementTypeModal').modal('show');
    });

    $('#editMeasurementTypeForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#editTypeId').val();
        try {
            const json = await postJson(routes.typesUpdate(id), { type_name: $('#editTypeName').val() });
            showAlert(json.message, 'success');
            const type = measurementTypesState.find(t => String(t.measurement_typeID) === String(id));
            if (type) {
                type.type_name = json.measurement_type.type_name;
                refreshMeasurementTypeRow(id);
            }
            $('#editMeasurementTypeModal').modal('hide');
            refreshTypeSelects(json.measurement_type);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-type', async function () {
        if (!(await storeConfirm('Delete this measurement type and all its units?', 'Delete measurement type'))) return;
        const id = $(this).data('id');
        try {
            const json = await postJson(routes.typesDelete(id), {});
            showAlert(json.message, 'success');
            measurementTypesState = measurementTypesState.filter(t => String(t.measurement_typeID) !== String(id));
            removeStoreTableRow('measurementTypesTable', `tr[data-type-id="${id}"]`);
            $(`#itemMeasurementTypeSelect option[value="${id}"], #editItemType option[value="${id}"]`).remove();
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Units
    $(document).on('click', '.btn-add-unit', function () {
        $('#addUnitTypeId').val($(this).data('type-id'));
        $('#addUnitTypeName').text($(this).data('type-name'));
        $('#addUnitModal').modal('show');
    });

    $('#addUnitForm').on('submit', async function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        try {
            const json = await postJson(routes.unitsStore, data);
            showAlert(json.message, 'success');
            $('#addUnitModal').modal('hide');
            this.reset();
            const typeId = json.unit.measurement_typeID || $('#addUnitTypeId').val();
            const type = measurementTypesState.find(t => String(t.measurement_typeID) === String(typeId));
            if (type) {
                type.units.push({
                    unitID: json.unit.unitID,
                    unit_name: json.unit.unit_name,
                    abbreviation: json.unit.abbreviation || '',
                });
                refreshMeasurementTypeRow(typeId);
            }
            if (returnToAddItemAfter === 'unit') {
                const previous = ($('#itemUnitSelect').val() || []).filter(id => !isAddOption(id));
                const merged = [...new Set([...previous, String(json.unit.unitID)])];
                returnToAddItemAfter = null;
                $('#addItemModal').modal('show');
                await loadUnits(typeId, $('#itemUnitSelect'), merged);
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-edit-unit', function () {
        $('#storeViewMoreModal').modal('hide');
        $('#editUnitId').val($(this).data('id'));
        $('#editUnitName').val($(this).data('name'));
        $('#editUnitAbbr').val($(this).data('abbr') || '');
        $('#editUnitModal').modal('show');
    });

    $('#editUnitForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#editUnitId').val();
        try {
            const json = await postJson(routes.unitsUpdate(id), {
                unit_name: $('#editUnitName').val(),
                abbreviation: $('#editUnitAbbr').val(),
            });
            showAlert(json.message, 'success');
            $('#editUnitModal').modal('hide');
            const type = measurementTypesState.find(t => (t.units || []).some(u => String(u.unitID) === String(id)));
            if (type) {
                const unit = type.units.find(u => String(u.unitID) === String(id));
                if (unit) {
                    unit.unit_name = json.unit.unit_name;
                    unit.abbreviation = json.unit.abbreviation || '';
                }
                refreshMeasurementTypeRow(type.measurement_typeID);
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-unit', async function () {
        if (!(await storeConfirm('Delete this unit?', 'Delete unit'))) return;
        try {
            const unitId = $(this).data('id');
            const json = await postJson(routes.unitsDelete(unitId), {});
            showAlert(json.message, 'success');
            $('#storeViewMoreModal').modal('hide');
            const typeId = json.measurement_typeID;
            const type = measurementTypesState.find(t => String(t.measurement_typeID) === String(typeId));
            if (type) {
                type.units = (type.units || []).filter(u => String(u.unitID) !== String(unitId));
                refreshMeasurementTypeRow(typeId);
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Packages
    function refreshPackageNameRemoveButtons() {
        const rows = $('#packageNamesContainer .package-name-row');
        rows.find('.btn-remove-package-name').toggle(rows.length > 1);
    }

    function resetPackageNamesForm() {
        $('#packageNamesContainer').html(`
            <div class="package-name-row input-group mb-2">
                <input type="text" class="form-control package-name-input" placeholder="Package name (e.g. Sack)" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-remove-package-name" style="display:none;" title="Remove">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        `);
    }

    $('#btnAddPackageNameRow').on('click', function () {
        $('#packageNamesContainer').append(`
            <div class="package-name-row input-group mb-2">
                <input type="text" class="form-control package-name-input" placeholder="Package name (e.g. Container)" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-remove-package-name" title="Remove">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        `);
        refreshPackageNameRemoveButtons();
    });

    $(document).on('click', '.btn-remove-package-name', function () {
        $(this).closest('.package-name-row').remove();
        refreshPackageNameRemoveButtons();
    });

    $('#addPackagesModal').on('show.bs.modal', function () {
        resetPackageNamesForm();
    });

    $('#addPackagesForm').on('submit', async function (e) {
        e.preventDefault();
        const package_names = [];
        $('#packageNamesContainer .package-name-input').each(function () {
            const val = ($(this).val() || '').trim();
            if (val) package_names.push(val);
        });
        if (!package_names.length) {
            showAlert('Enter at least one package name.', 'danger');
            return;
        }
        try {
            const json = await postJson(routes.packagesBulk, { package_names });
            showAlert(json.message, 'success');
            $('#addPackagesModal').modal('hide');
            (json.packages || []).forEach(pkg => {
                storePackagesList.push({ packageID: pkg.packageID, package_name: pkg.package_name });
                upsertStoreTableRow('packagesTable', `tr[data-package-id="${pkg.packageID}"]`, buildPackageRow(pkg));
            });
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-edit-package', function () {
        $('#editPackageId').val($(this).data('id'));
        $('#editPackageName').val($(this).data('name'));
        $('#editPackageModal').modal('show');
    });

    $('#editPackageForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#editPackageId').val();
        try {
            const json = await postJson(routes.packagesUpdate(id), {
                package_name: $('#editPackageName').val(),
            });
            showAlert(json.message, 'success');
            $('#editPackageModal').modal('hide');
            const pkg = storePackagesList.find(p => String(p.packageID) === String(id));
            if (pkg) pkg.package_name = json.package.package_name;
            upsertStoreTableRow('packagesTable', `tr[data-package-id="${id}"]`, buildPackageRow(json.package));
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-package', async function () {
        if (!(await storeConfirm('Delete this package?', 'Delete package'))) return;
        try {
            const id = $(this).data('id');
            const json = await postJson(routes.packagesDelete(id), {});
            showAlert(json.message, 'success');
            storePackagesList = storePackagesList.filter(p => String(p.packageID) !== String(id));
            removeStoreTableRow('packagesTable', `tr[data-package-id="${id}"]`);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Item Stock
    let selectedStockItem = null;
    let stockBatchCounter = 0;

    function getStockItemById(id) {
        return storeItemsList.find(i => String(i.itemID) === String(id));
    }

    function initStockSelect2() {
        $('#addItemStockModal .stock-form-select').each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownParent: $('#addItemStockModal'),
            });
        });
    }

    function initStockBatchPackageSelect($select) {
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select package level to stock...',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $('#addItemStockModal'),
        });
    }

    function resetStockPackageBatches() {
        stockBatchCounter = 0;
        $('#stockPackageBatches').empty();
        $('#stockTotalItemsDashboard').hide();
        $('#stockTotalItemsBody').empty();
    }

    function resetStockForm() {
        selectedStockItem = null;
        $('#stockItemSelect').val('').trigger('change');
        $('#stockStoreMethod').val('');
        $('#stockUnitSelect').empty().append('<option value="">Select unit...</option>');
        $('#stockQuantity').val('');
        $('#stockRequisitionStatus').val('');
        $('#stockItemInfo, #stockPackageSection, #stockUnitSection, #stockQuantitySection, #stockLabelsSection, #stockRequisitionSection, #stockPackageError').hide();
        resetStockPackageBatches();
        $('#stockQuantityHint').text('');
        $('#stockLabelsContainer').empty();
        $('#btnSaveStock').prop('disabled', true);
    }

    function getStockBatchSpec($card) {
        const value = $card.find('.stock-batch-package-select').val();
        if (!value || !selectedStockItem) return null;
        return findStockableSpec(selectedStockItem, value);
    }

    function collectStockBatchRows() {
        return $('#stockPackageBatches .stock-batch-card').toArray().map(el => $(el));
    }

    function collectAllStockLabels() {
        const labels = [];
        $('.stock-batch-label-input, #stockLabelsContainer .stock-label-input').each(function () {
            const v = ($(this).val() || '').trim();
            if (v) labels.push(v);
        });
        return labels;
    }

    function updateStockSaveButton() {
        const method = $('#stockStoreMethod').val();
        const reqStatus = $('#stockRequisitionStatus').val();

        if (!method || !selectedStockItem || !reqStatus) {
            $('#btnSaveStock').prop('disabled', true);
            return;
        }

        if (method === 'package') {
            const batches = collectStockBatchRows();
            if (!batches.length) {
                $('#btnSaveStock').prop('disabled', true);
                return;
            }

            let batchesOk = true;
            batches.forEach($card => {
                const spec = getStockBatchSpec($card);
                const qty = parseInt($card.find('.stock-batch-qty').val(), 10);
                const labels = [];
                $card.find('.stock-batch-label-input').each(function () {
                    const v = ($(this).val() || '').trim();
                    if (v) labels.push(v);
                });
                if (!spec || !(qty > 0) || qty > 500 || labels.length !== qty) {
                    batchesOk = false;
                }
            });

            const allLabels = collectAllStockLabels();
            const uniqueLabels = new Set(allLabels);
            const labelsUnique = allLabels.length === uniqueLabels.size;
            const packageErrorVisible = $('#stockPackageError').is(':visible');

            $('#btnSaveStock').prop('disabled', !(batchesOk && labelsUnique && !packageErrorVisible));
            return;
        }

        const qty = parseInt($('#stockQuantity').val(), 10);
        let labelsOk = false;
        if (qty > 0) {
            const labels = [];
            $('#stockLabelsContainer .stock-label-input').each(function () {
                const v = ($(this).val() || '').trim();
                if (v) labels.push(v);
            });
            labelsOk = labels.length === qty;
        }
        const unitOk = !!$('#stockUnitSelect').val();
        const qtyOk = qty > 0 && qty <= 500;
        $('#btnSaveStock').prop('disabled', !(unitOk && qtyOk && labelsOk));
    }

    function getStockModalBodyEl() {
        return document.querySelector('#addItemStockModal .stock-modal-scroll');
    }

    /** Keep modal scroll position while DOM (labels/hierarchy/select2) rebuilds. */
    function withStockModalScrollPreserved(fn) {
        const el = getStockModalBodyEl();
        const savedTop = el ? el.scrollTop : 0;
        let result;
        try {
            result = fn();
        } finally {
            if (el) {
                el.scrollTop = savedTop;
                requestAnimationFrame(function () {
                    el.scrollTop = savedTop;
                });
            }
        }
        return result;
    }

    function blurStockModalActiveControl() {
        const el = document.activeElement;
        if (!el) return;
        if (!$(el).closest('#addItemStockModal .stock-modal-scroll').length) return;
        if (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA'
            || el.classList.contains('select2-search__field')) {
            el.blur();
        }
    }

    function stockAutoLabelsEnabled() {
        return $('#stockAutoLabels').is(':checked');
    }

    function makeAutoLabel(prefix, index) {
        return String(prefix || 'Label') + '-' + String(index).padStart(2, '0');
    }

    function readExistingLabelValues($container) {
        return $container.find('input[type="text"]').toArray().map(el => ($(el).val() || '').trim());
    }

    function renderStockLabels(count, $container, specOrPrefix, isPackage) {
        withStockModalScrollPreserved(function () {
            const existing = readExistingLabelValues($container);
            $container.empty();
            if (!count || count < 1) {
                updateStockSaveButton();
                return;
            }

            const prefix = isPackage
                ? (specOrPrefix?.package_name || 'Package')
                : (specOrPrefix || 'Unit');

            let onePackageBreakdown = '';
            if (isPackage && specOrPrefix) {
                const parts = getPackageBreakdownTotals(specOrPrefix, 1).slice(1).map(row =>
                    formatStockQty(row.qty) + ' ' + row.name
                );
                if (parts.length) {
                    onePackageBreakdown = parts.join(', ');
                }
            }

            for (let i = 1; i <= count; i++) {
                const existingValue = (existing[i - 1] || '').trim();
                const autoValue = makeAutoLabel(prefix, i);
                const value = existingValue || (stockAutoLabelsEnabled() ? autoValue : '');
                $container.append(`
                    <div class="mb-2">
                        <div class="stock-label-row input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text label-prefix">${escapeHtml(prefix)} ${i}</span>
                            </div>
                            <input type="text" class="form-control stock-label-input stock-batch-label-input" value="${escapeHtml(value)}" placeholder="Enter label (e.g. ${escapeHtml(autoValue)})" required>
                        </div>
                        ${onePackageBreakdown ? `<div class="stock-label-breakdown">Contains: ${escapeHtml(onePackageBreakdown)}</div>` : ''}
                    </div>
                `);
            }
            updateStockSaveButton();
        });
    }

    function renderUnitStockLabels(count) {
        withStockModalScrollPreserved(function () {
            const prefix = $('#stockUnitSelect option:selected').text() || 'Unit';
            const $container = $('#stockLabelsContainer');
            const existing = readExistingLabelValues($container);
            $container.empty();
            if (!count || count < 1) {
                $('#stockLabelsSection, #stockRequisitionSection').hide();
                updateStockSaveButton();
                return;
            }
            for (let i = 1; i <= count; i++) {
                const existingValue = (existing[i - 1] || '').trim();
                const autoValue = makeAutoLabel(prefix, i);
                const value = existingValue || (stockAutoLabelsEnabled() ? autoValue : '');
                $container.append(`
                    <div class="mb-2">
                        <div class="stock-label-row input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text label-prefix">${escapeHtml(prefix)} ${i}</span>
                            </div>
                            <input type="text" class="form-control stock-label-input" value="${escapeHtml(value)}" placeholder="Enter label (e.g. ${escapeHtml(autoValue)})" required>
                        </div>
                    </div>
                `);
            }
            $('#stockLabelsSection, #stockRequisitionSection').show();
            updateStockSaveButton();
        });
    }

    function onStockItemChange() {
        const itemId = $('#stockItemSelect').val();
        selectedStockItem = itemId ? getStockItemById(itemId) : null;
        $('#stockStoreMethod').val('');
        $('#stockPackageSection, #stockUnitSection, #stockQuantitySection, #stockLabelsSection, #stockRequisitionSection, #stockPackageError').hide();
        resetStockPackageBatches();
        $('#stockQuantity').val('');
        $('#stockRequisitionStatus').val('');
        $('#stockLabelsContainer').empty();

        if (!selectedStockItem) {
            $('#stockItemInfo').hide();
            updateStockSaveButton();
            return;
        }

        $('#stockMeasurementType').text(selectedStockItem.measurement_type || '—');
        $('#stockItemInfo').show();
        updateStockSaveButton();
    }

    function buildStockPackageSelectOptions() {
        const stockable = getStockablePackages(selectedStockItem);
        let html = '<option value="">Select package level to stock...</option>';
        stockable.forEach(spec => {
            const value = spec.specID || spec.packageID;
            html += `<option value="${value}">${escapeHtml(spec.package_name)} — ${escapeHtml(spec.chain_summary || '')}</option>`;
        });
        return html;
    }

    function renumberStockPackageBatches() {
        collectStockBatchRows().forEach(($card, index) => {
            $card.find('.stock-batch-card-head .stock-batch-title').text('Package Receipt #' + (index + 1));
            $card.find('.btn-remove-stock-batch').toggle(index > 0);
        });
    }

    function addStockPackageBatch() {
        if (!selectedStockItem) return;
        withStockModalScrollPreserved(function () {
            const batchId = ++stockBatchCounter;
            const batchNo = $('#stockPackageBatches .stock-batch-card').length + 1;
            const html = `
                <div class="stock-batch-card border p-3 mb-3" data-batch-id="${batchId}">
                    <div class="stock-batch-card-head">
                        <span class="font-weight-bold text-primary-custom stock-batch-title">Package Receipt #${batchNo}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-stock-batch" ${batchNo === 1 ? 'style="display:none;"' : ''}>
                            <i class="fa fa-times"></i> Remove
                        </button>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold">Package <span class="text-danger">*</span></label>
                        <select class="form-control stock-batch-package-select" data-batch-id="${batchId}">
                            ${buildStockPackageSelectOptions()}
                        </select>
                    </div>
                    <div class="stock-batch-hierarchy border p-2 mb-2" style="display:none;background:#fff;"></div>
                    <div class="form-group mb-2 stock-batch-qty-wrap" style="display:none;">
                        <label class="font-weight-bold">Number of Packages <span class="text-danger">*</span></label>
                        <input type="number" class="form-control stock-batch-qty" data-batch-id="${batchId}" min="1" max="500" placeholder="How many?">
                        <small class="text-muted">Enter how many of this package type you are receiving.</small>
                    </div>
                    <div class="stock-batch-labels-section" style="display:none;">
                        <label class="font-weight-bold d-block">Labels <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Enter a unique label for each package received.</p>
                        <div class="stock-batch-labels"></div>
                    </div>
                </div>
            `;
            $('#stockPackageBatches').append(html);
            initStockBatchPackageSelect($(`#stockPackageBatches .stock-batch-card[data-batch-id="${batchId}"] .stock-batch-package-select`));
            renumberStockPackageBatches();
            updateStockTotalItemsDashboard();
            updateStockSaveButton();
        });
    }

    function onStockMethodChange() {
        const method = $('#stockStoreMethod').val();
        $('#stockPackageError').hide().text('');
        $('#stockPackageSection, #stockUnitSection, #stockQuantitySection, #stockLabelsSection, #stockRequisitionSection').hide();
        resetStockPackageBatches();
        $('#stockQuantity').val('');
        $('#stockRequisitionStatus').val('');
        $('#stockLabelsContainer').empty();

        if (!selectedStockItem || !method) {
            updateStockSaveButton();
            return;
        }

        if (method === 'package') {
            const stockable = getStockablePackages(selectedStockItem);
            if (!selectedStockItem.has_packages || !stockable.length) {
                $('#stockPackageError').text('No package in this item. This item was not registered with packages — use Unit Measure instead.').show();
                updateStockSaveButton();
                return;
            }
            $('#stockPackageSection').show();
            $('#stockRequisitionSection').show();
            addStockPackageBatch();
        } else {
            const $unit = $('#stockUnitSelect');
            $unit.empty().append('<option value="">Select unit...</option>');
            (selectedStockItem.units || []).forEach(u => {
                $unit.append(new Option(u.unit_name, u.unitID, false, false));
            });
            $('#stockUnitSection').show();
            $('#stockQuantityLabel').text('Quantity');
            $('#stockQuantityHint').text('Enter how many individual units you are receiving (e.g. 5 hoes, 3 laptops).');
            if ($unit.hasClass('select2-hidden-accessible')) {
                $unit.select2('destroy');
            }
            $unit.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select unit...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownParent: $('#addItemStockModal'),
            });
        }
        updateStockSaveButton();
    }

    function formatStockQty(n) {
        const num = parseFloat(n);
        if (Number.isInteger(num)) return String(num);
        return String(num);
    }

    function getPackageBreakdownTotals(spec, topQty) {
        const result = [];
        let count = topQty;
        const levels = spec.levels || [];
        levels.forEach((level, index) => {
            result.push({ name: level.package_name, qty: count });
            if (index < levels.length - 1) {
                count = count * parseFloat(levels[index + 1].quantity || 0);
            } else {
                const items = level.content_quantity != null
                    ? parseFloat(level.content_quantity)
                    : parseFloat(level.quantity || 0);
                count = count * items;
                result.push({ name: spec.leaf_unit_name || 'item', qty: count });
            }
        });
        return result;
    }

    function renderStockBatchHierarchy($card, spec, topQty) {
        withStockModalScrollPreserved(function () {
            const $box = $card.find('.stock-batch-hierarchy').empty();
            if (!spec) {
                $box.hide();
                return;
            }

            let html = '<div class="stock-hierarchy-title"><i class="fa fa-sitemap"></i> Package structure</div>';
            (spec.levels || []).forEach((level, index) => {
                if (index < (spec.levels.length - 1)) {
                    const next = spec.levels[index + 1];
                    html += `<div class="stock-hierarchy-line">${escapeHtml(level.package_name)} has <strong>${formatStockQty(next.quantity)}</strong> ${escapeHtml(next.package_name)}</div>`;
                } else {
                    const items = level.content_quantity != null ? level.content_quantity : level.quantity;
                    html += `<div class="stock-hierarchy-line">${escapeHtml(level.package_name)} has <strong>${formatStockQty(items)}</strong> ${escapeHtml(level.unit_name || 'item')}</div>`;
                }
            });

            if (topQty > 0) {
                html += '<div class="stock-hierarchy-title mt-2">Totals for ' + formatStockQty(topQty) + ' ' + escapeHtml(spec.package_name) + '(s)</div>';
                const breakdown = getPackageBreakdownTotals(spec, topQty);
                breakdown.forEach(row => {
                    html += `<div class="stock-hierarchy-line"><strong>${formatStockQty(row.qty)}</strong> ${escapeHtml(row.name)}</div>`;
                });
            }

            $box.html(html).show();
        });
    }

    function getItemsPerPackage(spec) {
        if (!spec) return 0;
        if (spec.items_per_package) return parseFloat(spec.items_per_package) || 0;
        const breakdown = getPackageBreakdownTotals(spec, 1);
        const leaf = breakdown[breakdown.length - 1];
        return leaf ? parseFloat(leaf.qty) || 0 : 0;
    }

    function updateStockTotalItemsDashboard() {
        withStockModalScrollPreserved(function () {
            const $dashboard = $('#stockTotalItemsDashboard');
            const $body = $('#stockTotalItemsBody').empty();
            const batches = collectStockBatchRows();

            if (!batches.length || $('#stockStoreMethod').val() !== 'package') {
                $dashboard.hide();
                return;
            }

            let grandTotal = 0;
            let hasAny = false;
            let rowsHtml = '';

            batches.forEach($card => {
                const spec = getStockBatchSpec($card);
                const qty = parseInt($card.find('.stock-batch-qty').val(), 10);
                if (!spec || !(qty > 0)) return;

                hasAny = true;
                const itemsEach = getItemsPerPackage(spec);
                const batchTotal = itemsEach * qty;
                grandTotal += batchTotal;
                const leafName = spec.leaf_unit_name || 'item';

                rowsHtml += `
                    <div class="stock-total-items-row">
                        <span>${formatStockQty(qty)} × ${escapeHtml(spec.package_name)}</span>
                        <span><strong>${formatStockQty(batchTotal)}</strong> ${escapeHtml(leafName)}</span>
                    </div>
                `;
            });

            if (!hasAny) {
                $dashboard.hide();
                return;
            }

            const leafName = batches.map(getStockBatchSpec).find(Boolean)?.leaf_unit_name || 'item';
            rowsHtml += `
                <div class="stock-total-items-row">
                    <span>Total Items</span>
                    <span>${formatStockQty(grandTotal)} ${escapeHtml(leafName)}</span>
                </div>
            `;

            $body.html(rowsHtml);
            $dashboard.show();
        });
    }

    function onStockBatchPackageChange($card) {
        withStockModalScrollPreserved(function () {
            const spec = getStockBatchSpec($card);
            $card.find('.stock-batch-qty-wrap, .stock-batch-labels-section').hide();
            $card.find('.stock-batch-qty').val('');
            $card.find('.stock-batch-labels').empty();
            renderStockBatchHierarchy($card, spec, 0);

            if (spec) {
                $card.find('.stock-batch-qty-wrap').show();
            }
            updateStockTotalItemsDashboard();
            updateStockSaveButton();
        });
    }

    function onStockBatchQtyChange($card) {
        withStockModalScrollPreserved(function () {
            const spec = getStockBatchSpec($card);
            const qty = parseInt($card.find('.stock-batch-qty').val(), 10);
            const $labelsSection = $card.find('.stock-batch-labels-section');
            const $labels = $card.find('.stock-batch-labels');

            renderStockBatchHierarchy($card, spec, qty > 0 ? qty : 0);

            if (spec && qty > 0 && qty <= 500) {
                $labelsSection.show();
                renderStockLabels(qty, $labels, spec, true);
            } else {
                $labelsSection.hide();
                $labels.empty();
            }
            updateStockTotalItemsDashboard();
            updateStockSaveButton();
        });
    }

    function getStockablePackages(item) {
        if (!item) return [];
        return item.stockable_packages || item.package_specs || [];
    }

    function findStockableSpec(item, value) {
        const list = getStockablePackages(item);
        return list.find(s => String(s.specID || '') === String(value))
            || list.find(s => String(s.packageID) === String(value))
            || null;
    }

    function onStockUnitChange() {
        $('#stockQuantitySection, #stockLabelsSection, #stockRequisitionSection').hide();
        $('#stockQuantity').val('');
        $('#stockLabelsContainer').empty();

        if ($('#stockUnitSelect').val()) {
            $('#stockQuantitySection').show();
        }
        updateStockSaveButton();
    }

    $('#stockItemSelect').on('change', onStockItemChange);
    $('#stockStoreMethod').on('change', onStockMethodChange);
    $('#stockUnitSelect').on('change', onStockUnitChange);
    $('#btnAddStockPackageBatch').on('click', addStockPackageBatch);

    $(document).on('change', '.stock-batch-package-select', function () {
        onStockBatchPackageChange($(this).closest('.stock-batch-card'));
    });
    $(document).on('click', '.btn-remove-stock-batch', function () {
        $(this).closest('.stock-batch-card').remove();
        withStockModalScrollPreserved(function () {
            renumberStockPackageBatches();
            updateStockTotalItemsDashboard();
            updateStockSaveButton();
        });
    });

    var stockQtyLabelTimer = null;
    var lastRenderedStockQty = null;
    var stockBatchQtyTimers = {};

    $('#stockQuantity').on('input', function () {
        const qty = parseInt($(this).val(), 10);

        clearTimeout(stockQtyLabelTimer);
        stockQtyLabelTimer = setTimeout(function () {
            withStockModalScrollPreserved(function () {
                if (qty > 0 && qty <= 500) {
                    if (lastRenderedStockQty !== qty) {
                        renderUnitStockLabels(qty);
                        lastRenderedStockQty = qty;
                    }
                } else {
                    $('#stockLabelsSection, #stockRequisitionSection').hide();
                    $('#stockLabelsContainer').empty();
                    lastRenderedStockQty = null;
                }
                updateStockSaveButton();
            });
        }, 180);

        updateStockSaveButton();
    });

    // Debounce package qty → labels rebuild so typing does not jump scroll every keystroke
    $(document).on('input', '.stock-batch-qty', function () {
        const $card = $(this).closest('.stock-batch-card');
        const batchId = $card.data('batch-id') || '0';
        clearTimeout(stockBatchQtyTimers[batchId]);
        stockBatchQtyTimers[batchId] = setTimeout(function () {
            onStockBatchQtyChange($card);
        }, 180);
        updateStockSaveButton();
    });

    // When user starts scrolling the stock modal, blur focused number/select so
    // the browser does not force scroll back to keep the focused field in view.
    $(document).on('wheel touchstart pointerdown', '#addItemStockModal .stock-modal-scroll, #addItemModal .modal-body, #editItemModal .modal-body', function (e) {
        // Don't steal focus from Select2 search while picking an option
        if ($(e.target).closest('.select2-container, .select2-dropdown').length) return;
        blurStockModalActiveControl();
        var el = document.activeElement;
        if (!el) return;
        if ($(el).closest('#addItemModal .modal-body, #editItemModal .modal-body').length) {
            if (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
                el.blur();
            }
        }
    });

    // Guard: some browsers/plugins jump scrollTop back to 0 after DOM rebuild/focus changes.
    // If user was mid-scroll inside stock modal and it suddenly jumps to top, restore last position.
    let stockModalScrollMem = 0;
    let stockModalUserScrollTimer = null;
    let stockModalUserScrolling = false;

    $(document).on('wheel touchstart pointerdown', '#addItemStockModal .stock-modal-scroll', function () {
        const el = getStockModalBodyEl();
        if (!el) return;
        stockModalUserScrolling = true;
        stockModalScrollMem = el.scrollTop || 0;
        clearTimeout(stockModalUserScrollTimer);
        stockModalUserScrollTimer = setTimeout(function () {
            stockModalUserScrolling = false;
        }, 350);
    });

    $(document).on('scroll', '#addItemStockModal .stock-modal-scroll', function () {
        if (!stockModalUserScrolling) {
            stockModalScrollMem = this.scrollTop || 0;
            return;
        }
        const top = this.scrollTop || 0;
        if (top === 0 && stockModalScrollMem > 40) {
            const restore = stockModalScrollMem;
            this.scrollTop = restore;
            requestAnimationFrame(() => {
                this.scrollTop = restore;
            });
            return;
        }
        stockModalScrollMem = top;
    });

    // Focus can trigger auto "scroll into view" jumps (especially Select2 + Bootstrap modals).
    // Whenever a control inside the stock modal gains focus, restore previous scroll position.
    let stockModalFocusRestoreTimer = null;
    $(document).on('focusin', '#addItemStockModal .stock-modal-scroll input, #addItemStockModal .stock-modal-scroll select, #addItemStockModal .stock-modal-scroll textarea, #addItemStockModal .stock-modal-scroll .select2-search__field', function () {
        const el = getStockModalBodyEl();
        if (!el) return;
        const saved = stockModalScrollMem || el.scrollTop || 0;
        clearTimeout(stockModalFocusRestoreTimer);
        stockModalFocusRestoreTimer = setTimeout(function () {
            el.scrollTop = saved;
            requestAnimationFrame(function () {
                el.scrollTop = saved;
            });
        }, 0);
    });

    // Select2 open also tends to reposition scroll; pin it.
    $(document).on('select2:open select2:closing', '#addItemStockModal select', function () {
        const el = getStockModalBodyEl();
        if (!el) return;
        const saved = stockModalScrollMem || el.scrollTop || 0;
        setTimeout(function () {
            el.scrollTop = saved;
            requestAnimationFrame(function () {
                el.scrollTop = saved;
            });
        }, 0);
    });

    $(document).on('input', '.stock-label-input, .stock-batch-label-input', updateStockSaveButton);
    $('#stockRequisitionStatus').on('change', updateStockSaveButton);
    $('#stockAutoLabels').on('change', function () {
        // Only auto-fill missing values; never overwrite user's edits.
        withStockModalScrollPreserved(function () {
            const method = $('#stockStoreMethod').val();
            if (method === 'unit') {
                const qty = parseInt($('#stockQuantity').val(), 10);
                if (qty > 0 && qty <= 500) renderUnitStockLabels(qty);
            } else if (method === 'package') {
                collectStockBatchRows().forEach($card => {
                    const qty = parseInt($card.find('.stock-batch-qty').val(), 10);
                    const spec = getStockBatchSpec($card);
                    if (spec && qty > 0 && qty <= 500) {
                        renderStockLabels(qty, $card.find('.stock-batch-labels'), spec, true);
                    }
                });
            }
        });
    });

    $('#addItemStockModal').on('shown.bs.collapse', function () {
        lastRenderedStockQty = null;
        initStockSelect2();
        // Scroll to the expanded form so user continues
        const el = document.querySelector('#addItemStockModal');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    $('#addItemStockModal').on('hidden.bs.collapse', resetStockForm);

    $('#addItemStockForm').on('submit', async function (e) {
        e.preventDefault();
        const method = $('#stockStoreMethod').val();
        const requisitionStatus = $('#stockRequisitionStatus').val();

        if (!selectedStockItem || !method || !requisitionStatus) {
            showAlert('Chagua item, store method, na return policy.', 'danger');
            return;
        }

        let payload = {
            itemID: $('#stockItemSelect').val(),
            store_method: method,
            requisition_status: requisitionStatus,
        };

        if (method === 'package') {
            const batches = [];
            collectStockBatchRows().forEach($card => {
                const spec = getStockBatchSpec($card);
                const qty = parseInt($card.find('.stock-batch-qty').val(), 10);
                const labels = [];
                $card.find('.stock-batch-label-input').each(function () {
                    labels.push(($(this).val() || '').trim());
                });
                if (!spec || !(qty > 0)) return;
                batches.push({
                    packageID: spec.packageID || null,
                    package_specID: spec.specID || null,
                    unitID: spec.leaf_unitID || spec.unitID,
                    quantity: qty,
                    labels: labels,
                });
            });

            if (!batches.length) {
                showAlert('Add at least one package receipt.', 'danger');
                return;
            }
            for (const batch of batches) {
                if (!batch.packageID && !batch.package_specID) {
                    showAlert('Chagua package type kwa kila receipt.', 'danger');
                    return;
                }
                if (!(batch.quantity > 0) || batch.quantity > 500) {
                    showAlert('Idadi ya package lazima iwe kati ya 1 na 500.', 'danger');
                    return;
                }
                if ((batch.labels || []).length !== batch.quantity) {
                    showAlert('Idadi ya labels lazima ilingane na quantity kwa kila package receipt.', 'danger');
                    return;
                }
                if ((batch.labels || []).some(l => !l)) {
                    showAlert('Jaza label zote — hakuna label tupu.', 'danger');
                    return;
                }
            }
            const allLabels = collectAllStockLabels();
            if (allLabels.length !== new Set(allLabels).size) {
                showAlert('Labels zinazofanana haziruhusiwi — kila label iwe ya kipekee.', 'danger');
                return;
            }
            payload.batches = batches;
        } else {
            const qty = parseInt($('#stockQuantity').val(), 10);
            const labels = [];
            $('#stockLabelsContainer .stock-label-input').each(function () {
                labels.push(($(this).val() || '').trim());
            });
            if (!$('#stockUnitSelect').val()) {
                showAlert('Chagua unit of measure.', 'danger');
                return;
            }
            if (!(qty > 0) || qty > 500) {
                showAlert('Idadi lazima iwe kati ya 1 na 500.', 'danger');
                return;
            }
            if (labels.length !== qty) {
                showAlert('Idadi ya labels lazima ilingane na quantity.', 'danger');
                return;
            }
            if (labels.some(l => !l)) {
                showAlert('Jaza label zote — hakuna label tupu.', 'danger');
                return;
            }
            if (labels.length !== new Set(labels).size) {
                showAlert('Labels zinazofanana haziruhusiwi.', 'danger');
                return;
            }
            payload.unitID = $('#stockUnitSelect').val();
            payload.quantity = qty;
            payload.labels = labels;
        }

        try {
            const json = await postJson(routes.stockStore, payload);
            showAlert(json.message, 'success');
            $('#addItemStockModal').collapse('hide');
            const entries = json.entries || (json.entry ? [json.entry] : []);
            entries.forEach(entry => {
                upsertStoreTableRow('stockTable', `tr[data-stock-entry-id="${entry.stock_entryID}"]`, buildStockRow(entry));
                syncAvailableStockFromEntry(entry);
            });
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-stock', async function () {
        if (!(await storeConfirm('Remove this stock entry? All labelled units in this batch will be removed.', 'Remove stock'))) return;
        try {
            const id = $(this).data('id');
            const json = await postJson(routes.stockDelete(id), {});
            showAlert(json.message, 'success');
            removeStoreTableRow('stockTable', `tr[data-stock-entry-id="${id}"]`);
            const removedIds = (json.removed_stock_unitIDs || []).map(String);
            if (removedIds.length) {
                availableStockList = availableStockList.filter(u => !removedIds.includes(String(u.stock_unitID)));
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    // Requisitions
    function initIssueSelect2() {
        $('#issueItemModal .issue-form-select').each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownParent: $('#issueItemModal'),
            });
        });
    }

    function getItemCategoryId(itemID) {
        const fromStock = availableStockList.find(u => String(u.itemID) === String(itemID));
        if (fromStock && fromStock.categoryID) return fromStock.categoryID;
        const fromReport = reportItemsList.find(it => String(it.itemID) === String(itemID));
        return fromReport?.categoryID ?? null;
    }

    function resetIssueForm() {
        $('#issueRecipientType').val('');
        $('#issueRecipientSelect').prop('disabled', true).empty().append('<option value="">Select recipient type first...</option>');
        $('#issueCategoryFilter').val('');
        $('#issueItemSelect').empty().append('<option value="">Search or select item in stock...</option>');
        $('#issueStockInfo, #issueLabelsSection, #issueTakeSection, #issueExpectedReturnSection').hide();
        $('#issueLabelsList').empty();
        $('#issueTakeMethod').empty().append('<option value="">Choose request method...</option>');
        $('#issueTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#issueTakeTargetList').empty().hide();
        $('#issueTakeTargetToolbar').hide();
        $('#issueTakeChainInfo').hide().empty();
        $('#issueTakeMethodWrap, #issueTakeTargetWrap').hide();
        $('#issueTakeTargetDetail').text('');
        $('#issueTakeQtyWrap').show();
        $('#issueTakeQty').val(1).prop('readonly', false);
        $('#issueTakeQtyHint').text('');
        $('#issueNotes').val('');
        $('#issueExpectedReturnDate').val('');
        $('#btnIssueItem').prop('disabled', true);
        populateIssueItemSelect();
    }

    function populateIssueItemSelect() {
        const cat = $('#issueCategoryFilter').val();
        const itemMap = {};
        availableStockList.forEach(u => {
            if (cat) {
                const itemCat = u.categoryID ?? getItemCategoryId(u.itemID);
                if (String(itemCat) !== String(cat)) return;
            }
            if (!itemMap[u.itemID]) {
                itemMap[u.itemID] = u.item_name;
            }
        });
        const $sel = $('#issueItemSelect');
        const prev = $sel.val();
        $sel.find('option:not(:first)').remove();
        Object.keys(itemMap).sort((a, b) => itemMap[a].localeCompare(itemMap[b])).forEach(itemID => {
            $sel.append(new Option(itemMap[itemID], itemID, false, String(itemID) === String(prev)));
        });
        if (!itemMap[prev]) {
            $sel.val('');
        }
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.trigger('change.select2');
        }
    }

    function populateRecipientSelect() {
        const type = $('#issueRecipientType').val();
        const $sel = $('#issueRecipientSelect');
        $sel.empty();
        if (!type) {
            $sel.prop('disabled', true).append('<option value="">Select recipient type first...</option>');
            updateIssueButton();
            return;
        }
        $sel.prop('disabled', false).append('<option value="">Search or select person...</option>');
        requestersList.filter(r => r.type === type).forEach(r => {
            let label = r.name;
            if (r.employee_number) label += ` (${r.employee_number})`;
            if (type === 'student' && r.class_name) label += ` — ${r.class_name}`;
            $sel.append(new Option(label, `${r.type}:${r.id}`, false, false));
        });
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }
        $sel.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search or select person...',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $('#issueItemModal'),
        });
        updateIssueButton();
    }

    function renderIssueLabels(itemID) {
        const units = availableStockList.filter(u => String(u.itemID) === String(itemID));
        const $list = $('#issueLabelsList').empty();
        $('#issueTakeSection').hide();
        resetIssueTakeFields();

        if (!units.length) {
            $('#issueLabelsSection, #issueStockInfo').hide();
            updateIssueButton();
            return;
        }

        const pkgUnits = [...new Set(units.map(u => u.package_unit_display))];
        const remainingTotal = units.reduce((sum, u) => sum + (parseFloat(u.remaining_items) || 0), 0);
        $('#issuePackageUnit').text(pkgUnits.join(', '));
        $('#issueAvailableCount').text(units.length + ' label(s), ' + remainingTotal + ' item(s) left');
        $('#issueStockInfo, #issueLabelsSection').show();

        units.forEach(u => {
            const returnBadge = u.requisition_status === 'can_return'
                ? '<span class="store-badge-pill store-badge-return-yes ml-1">Can Return</span>'
                : '<span class="store-badge-pill store-badge-return-no ml-1">Can\'t Return</span>';
            const remainText = u.store_method === 'package'
                ? ` — ${escapeHtml(u.package_unit_display)} · left ${u.remaining_items}/${u.initial_items} ${escapeHtml(u.leaf_unit_name || 'item')}`
                : ` — ${escapeHtml(u.package_unit_display)}`;
            $list.append(`
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input issue-label-checkbox"
                        id="issueLabel${u.stock_unitID}" value="${u.stock_unitID}">
                    <label class="custom-control-label" for="issueLabel${u.stock_unitID}">
                        <strong>${escapeHtml(u.label)}</strong>
                        <span class="text-muted small">${remainText}</span>
                        ${returnBadge}
                    </label>
                </div>
            `);
        });
        updateIssueButton();
    }

    function getSelectedIssueUnits() {
        const ids = [];
        $('.issue-label-checkbox:checked').each(function () {
            ids.push($(this).val());
        });
        return ids
            .map(id => availableStockList.find(x => String(x.stock_unitID) === String(id)))
            .filter(Boolean);
    }

    function getSelectedIssueUnit() {
        const units = getSelectedIssueUnits();
        return units.length === 1 ? units[0] : null;
    }

    function resolveMainTakeForUnit(unit) {
        if (!unit) return null;
        const structure = getIssueTakeStructure(unit);
        if (!structure || !(structure.take_options || []).length) return null;

        if (unit.store_method !== 'package') {
            const opt = structure.take_options[0];
            return opt ? { take_key: opt.key, quantity: 1, opt, unit, max_qty: parseFloat(opt.max_qty) || 1 } : null;
        }

        const mainMethod = (structure.methods || []).find(m => m.key === 'main');
        if (mainMethod && mainMethod.options.length) {
            const opt = mainMethod.options[0];
            return { take_key: opt.key, quantity: 1, opt, unit, max_qty: parseFloat(opt.max_qty) || 1 };
        }

        const pkgOpt = structure.take_options.find(o => o.level === 'package');
        if (pkgOpt) {
            return { take_key: pkgOpt.key, quantity: 1, opt: pkgOpt, unit, max_qty: parseFloat(pkgOpt.max_qty) || 1 };
        }

        return null;
    }

    function buildMultiLabelMainSelections(units) {
        return (units || []).map(u => resolveMainTakeForUnit(u)).filter(Boolean);
    }

    function renderIssueTakeForLabels() {
        const units = getSelectedIssueUnits();
        resetIssueTakeFields();

        if (!units.length) {
            $('#issueTakeSection').hide();
            updateIssueButton();
            return;
        }

        if (units.length > 1) {
            $('#issueTakeSection').show();
            $('#issueTakeChainInfo')
                .html(
                    '<strong>Selected labels:</strong> ' + escapeHtml(units.map(u => u.label).join(', ')) +
                    '<br><span class="text-muted">By Main Package — quantity auto = 1 kwa kila label iliyochaguliwa.</span>'
                )
                .show();
            $('#issueTakeMethodWrap, #issueTakeTargetWrap, #issueTakeQtyWrap').hide();
            $('#issueTakeMethod').val('main');
            updateTakeLivePreview('issue', null);
            updateIssueButton();
            return;
        }

        renderIssueTakeOptions(units[0]);
        updateIssueButton();
    }

    function resetIssueTakeFields() {
        $('#issueTakeMethod').empty().append('<option value="">Choose request method...</option>');
        $('#issueTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#issueTakeTargetList').empty().hide();
        $('#issueTakeTargetToolbar').hide();
        $('#issueTakeChainInfo').hide().empty();
        $('#issueTakeMethodWrap, #issueTakeTargetWrap').hide();
        $('#issueTakeTargetDetail').text('');
        $('#issueTakeQtyWrap').show();
        $('#issueTakeQty').val(1).prop('readonly', false);
        $('#issueTakeQtyHint').text('');
        $('#issueTakeLivePreview').hide().empty();
        const activeUnit = getSelectedIssueUnit();
        if (activeUnit) activeUnit._subHierarchy = null;
    }

    function getIssueTakeStructure(unit) {
        if (!unit) return null;
        if (unit.take_structure) return unit.take_structure;
        if (unit.take_options && unit.take_options.length) {
            return {
                show_method_select: false,
                methods: [],
                take_options: unit.take_options,
                chain_summary: null,
                contains_line: null,
            };
        }
        return null;
    }

    function renderIssueTakeOptions(unit) {
        resetIssueTakeFields();
        if (!unit) {
            $('#issueTakeSection').hide();
            return;
        }

        const structure = getIssueTakeStructure(unit);
        if (!structure || !(structure.take_options || []).length) {
            $('#issueTakeSection').hide();
            return;
        }

        $('#issueTakeSection').show();

        if (structure.contains_line || structure.chain_summary) {
            $('#issueTakeChainInfo')
                .html('<strong>Package chain:</strong> ' + escapeHtml(structure.contains_line || structure.chain_summary))
                .show();
        }

        if (!structure.show_method_select) {
            const opt = structure.take_options[0];
            $('#issueTakeMethodWrap').hide();
            $('#issueTakeTargetWrap').hide();
            $('#issueTakeQtyWrap').hide();
            $('#issueTakeQty').val(1).prop('readonly', true);
            unit._activeTakeOptions = structure.take_options;
            unit._selectedTakeKey = opt.key;
            updateIssueTakeQtyLimits();
            return;
        }

        $('#issueTakeMethodWrap').show();
        const $method = $('#issueTakeMethod').empty().append('<option value="">Choose request method...</option>');
        (structure.methods || []).forEach(m => {
            $method.append(`<option value="${escapeHtml(m.key)}">${escapeHtml(m.label)}</option>`);
        });

        unit._takeStructure = structure;
        $('#issueTakeTargetWrap').hide();
    }

    function buildHierarchyUnitHtml(unit, hierarchy, prefix) {
        const unitCheckClass = prefix === 'issue' ? 'issue-hier-unit-check' : 'incident-hier-unit-check';
        const modeRadioClass = prefix === 'issue' ? 'issue-hier-mode-radio' : 'incident-hier-mode-radio';
        const partialQtyClass = prefix === 'issue' ? 'issue-hier-partial-qty' : 'incident-hier-partial-qty';
        const radioName = prefix + 'HierMode' + unit.index;
        const unitId = prefix + 'HierUnit' + unit.index;
        const isPartial = !!unit.is_partial;
        const child = unit.child;
        const childInfo = child
            ? `<span class="issue-hier-child-info">Ina <strong>${child.capacity_in_parent}</strong> ${escapeHtml(child.package_name)}(s)</span>`
            : '';

        let bodyHtml = '';
        if (isPartial) {
            if (child) {
                bodyHtml = `
                    <div class="issue-hier-child-info">${escapeHtml(unit.label)} — chagua ${escapeHtml(child.package_name)} ngapi:</div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Idadi ya ${escapeHtml(child.package_name)}(s)</label>
                        <input type="number" class="form-control form-control-sm ${partialQtyClass}"
                            min="1" max="${child.partial_take.max_qty}" step="1" value="1"
                            data-take-key="${escapeHtml(child.partial_take.key)}"
                            data-max-qty="${child.partial_take.max_qty}"
                            data-items-each="${child.partial_take.items_each}">
                        <small class="text-muted d-block mt-1">Max ${child.partial_take.max_qty} ${escapeHtml(child.package_name)}(s)</small>
                        <div class="text-danger issue-hier-qty-error" style="display:none;"></div>
                    </div>`;
            } else if (unit.loose_take) {
                bodyHtml = `
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Idadi ya ${escapeHtml(unit.loose_take.label)}(s)</label>
                        <input type="number" class="form-control form-control-sm ${partialQtyClass}"
                            min="1" max="${unit.loose_take.max_qty}" step="1" value="1"
                            data-take-key="${escapeHtml(unit.loose_take.key)}"
                            data-max-qty="${unit.loose_take.max_qty}"
                            data-items-each="1">
                        <small class="text-muted d-block mt-1">Max ${unit.loose_take.max_qty}</small>
                        <div class="text-danger issue-hier-qty-error" style="display:none;"></div>
                    </div>`;
            }
        } else {
            let modeHtml = '';
            if (unit.can_take_full && unit.full_take) {
                modeHtml += `
                    <div class="custom-control custom-radio issue-hier-mode-row">
                        <input type="radio" class="custom-control-input ${modeRadioClass}" name="${radioName}"
                            id="${unitId}_full" value="full" data-take-key="${escapeHtml(unit.full_take.key)}" checked>
                        <label class="custom-control-label" for="${unitId}_full">
                            <strong>Full ${escapeHtml(hierarchy.first_sub_name)}</strong>
                        </label>
                    </div>`;
            }
            if (child) {
                modeHtml += `
                    <div class="custom-control custom-radio issue-hier-mode-row">
                        <input type="radio" class="custom-control-input ${modeRadioClass}" name="${radioName}"
                            id="${unitId}_partial" value="partial">
                        <label class="custom-control-label" for="${unitId}_partial">
                            <strong>Chukua ${escapeHtml(child.package_name)}(s) tu</strong> — si ${escapeHtml(hierarchy.first_sub_name)} nzima
                        </label>
                    </div>
                    <div class="issue-hier-partial-wrap mt-2" style="display:none;">
                        ${childInfo}
                        <label class="small font-weight-bold">${escapeHtml(unit.label)} ina ${escapeHtml(child.package_name)} ngapi?</label>
                        <input type="number" class="form-control form-control-sm ${partialQtyClass}"
                            min="1" max="${child.partial_take.max_qty}" step="1" value="1"
                            data-take-key="${escapeHtml(child.partial_take.key)}"
                            data-max-qty="${child.partial_take.max_qty}"
                            data-items-each="${child.partial_take.items_each}"
                            disabled>
                        <small class="text-muted d-block mt-1">Max ${child.partial_take.max_qty} · kila ${escapeHtml(child.package_name)} = ${child.partial_take.items_each} item(s)</small>
                        <div class="text-danger issue-hier-qty-error" style="display:none;"></div>
                    </div>`;
            }
            bodyHtml = modeHtml;
        }

        return `
            <div class="issue-hier-unit" data-unit-index="${unit.index}" data-is-partial="${isPartial ? '1' : '0'}">
                <div class="issue-hier-unit-head custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input ${unitCheckClass}" id="${unitId}" value="${unit.index}">
                    <label class="custom-control-label" for="${unitId}">
                        <strong>${escapeHtml(unit.label)}</strong>
                        ${child ? `<span class="issue-take-avail-badge">Ina ${child.capacity_in_parent} ${escapeHtml(child.package_name)}(s)</span>` : ''}
                    </label>
                </div>
                <div class="issue-hier-unit-body" style="display:none;">${bodyHtml}</div>
            </div>`;
    }

    function renderSubPackageHierarchy(hierarchy, unit, prefix) {
        const $list = prefix === 'issue' ? $('#issueTakeTargetList') : $('#incidentTakeTargetList');
        const $target = prefix === 'issue' ? $('#issueTakeTarget') : $('#incidentTakeTarget');
        const $detail = prefix === 'issue' ? $('#issueTakeTargetDetail') : $('#incidentTakeTargetDetail');

        $list.empty().show();
        $('#' + (prefix === 'issue' ? 'issueTakeTargetToolbar' : 'incidentTakeTargetToolbar')).show();
        $target.hide();
        unit._subHierarchy = hierarchy;
        unit._activeTakeOptions = hierarchy.take_options || [];

        const summary = hierarchy.available_summary || '';
        const contains = hierarchy.contains_line ? `<div class="text-muted small mb-2">${escapeHtml(hierarchy.contains_line)}</div>` : '';
        $list.append(
            `<div class="issue-hier-summary-bar">
                <strong>1 ${escapeHtml(hierarchy.main_package_name)}</strong> ina hadi ${hierarchy.capacity_per_main} ${escapeHtml(hierarchy.first_sub_name)}(s).
                ${summary ? ' · <strong>' + escapeHtml(summary) + '</strong>' : ''}
            </div>${contains}`
        );

        (hierarchy.units || []).forEach(u => {
            $list.append(buildHierarchyUnitHtml(u, hierarchy, prefix));
        });

        $detail.text('Chagua ' + (hierarchy.first_sub_name || 'sub-package') + '(s), kisha Full au ' + (hierarchy.child_sub_name || 'vitengo') + ' ndani yake.');
        updateTakeLivePreview(prefix, unit);
    }

    function collectHierarchyTakeSelections(prefix, unit) {
        const unitCheckClass = prefix === 'issue' ? '.issue-hier-unit-check' : '.incident-hier-unit-check';
        const optionMap = Object.fromEntries((unit._activeTakeOptions || []).map(o => [o.key, o]));
        const selections = [];

        $(unitCheckClass + ':checked').each(function () {
            const $block = $(this).closest('.issue-hier-unit');
            const isPartial = String($block.data('is-partial')) === '1';

            if (isPartial) {
                const $qty = $block.find(prefix === 'issue' ? '.issue-hier-partial-qty' : '.incident-hier-partial-qty');
                const key = $qty.data('take-key');
                const qty = parseFloat($qty.val());
                const opt = optionMap[key];
                if (opt && qty > 0) {
                    selections.push({ take_key: key, quantity: qty, max_qty: parseFloat(opt.max_qty) || 0, opt });
                }
                return;
            }

            const mode = $block.find(prefix === 'issue' ? '.issue-hier-mode-radio:checked' : '.incident-hier-mode-radio:checked').val();
            if (mode === 'full') {
                const key = $block.find('[value="full"]').data('take-key');
                const opt = optionMap[key];
                if (opt) {
                    selections.push({ take_key: key, quantity: 1, max_qty: 1, opt });
                }
            } else if (mode === 'partial') {
                const $qty = $block.find(prefix === 'issue' ? '.issue-hier-partial-qty' : '.incident-hier-partial-qty');
                const key = $qty.data('take-key');
                const qty = parseFloat($qty.val());
                const opt = optionMap[key];
                if (opt && qty > 0) {
                    selections.push({ take_key: key, quantity: qty, max_qty: parseFloat(opt.max_qty) || 0, opt });
                }
            }
        });

        return selections;
    }

    function getQtyErrorEl($input) {
        let $err = $input.siblings('.store-qty-error, .issue-hier-qty-error').first();
        if (!$err.length) {
            $err = $input.parent().children('.store-qty-error, .issue-hier-qty-error').first();
        }
        if (!$err.length) {
            $err = $('<div class="text-danger small store-qty-error mt-1"></div>');
            $input.after($err);
        }
        return $err;
    }

    function markQtyInputInvalid($input, message) {
        getQtyErrorEl($input).text(message || 'Idadi si sahihi.').show();
        $input.addClass('is-invalid');
    }

    function clearQtyInputInvalid($input) {
        $input.removeClass('is-invalid');
        $input.siblings('.store-qty-error, .issue-hier-qty-error').hide();
        $input.parent().children('.store-qty-error, .issue-hier-qty-error').hide();
    }

    function validateTakeQuantityInputs(prefix, unit) {
        const qtyInputSel = prefix === 'issue' ? '#issueTakeQty' : '#incidentTakeQty';
        const rowQtyClass = prefix === 'issue' ? '.issue-take-row-qty-input' : '.incident-take-row-qty-input';
        const hierQtyClass = prefix === 'issue' ? '.issue-hier-partial-qty' : '.incident-hier-partial-qty';
        const unitCheckClass = prefix === 'issue' ? '.issue-hier-unit-check' : '.incident-hier-unit-check';
        const modeRadioClass = prefix === 'issue' ? '.issue-hier-mode-radio' : '.incident-hier-mode-radio';
        const checkboxClass = prefix === 'issue' ? '.issue-take-target-checkbox' : '.incident-take-target-checkbox';

        $(qtyInputSel + ',' + rowQtyClass + ',' + hierQtyClass).each(function () {
            clearQtyInputInvalid($(this));
        });

        let valid = true;

        $(checkboxClass + ':checked').each(function () {
            const $cb = $(this);
            const $input = $cb.closest('.issue-take-target-option').find(rowQtyClass);
            if (!$input.length || $input.prop('disabled')) return;
            const max = parseFloat($cb.data('max-qty')) || parseFloat($input.attr('max')) || 0;
            const val = parseFloat($input.val()) || 0;
            if (val > max) {
                valid = false;
                markQtyInputInvalid($input, 'Haiwezi zidi ' + max + '!');
            } else if (val <= 0) {
                valid = false;
                markQtyInputInvalid($input, 'Weka idadi sahihi (angalau 1).');
            }
        });

        $(unitCheckClass + ':checked').each(function () {
            const $block = $(this).closest('.issue-hier-unit');
            const isPartialUnit = String($block.data('is-partial')) === '1';
            const mode = isPartialUnit ? 'partial' : $block.find(modeRadioClass + ':checked').val();

            if (mode !== 'partial' && !isPartialUnit) return;

            $block.find(hierQtyClass).each(function () {
                const $input = $(this);
                if ($input.prop('disabled')) return;
                const max = parseFloat($input.data('max-qty')) || parseFloat($input.attr('max')) || 0;
                const val = parseFloat($input.val()) || 0;
                if (val > max) {
                    valid = false;
                    markQtyInputInvalid($input, 'Haiwezi zidi ' + max + '!');
                } else if (val <= 0) {
                    valid = false;
                    markQtyInputInvalid($input, 'Weka idadi sahihi (angalau 1).');
                }
            });
        });

        const $mainQty = $(qtyInputSel);
        if ($mainQty.is(':visible') && !$mainQty.prop('readonly')) {
            const opt = prefix === 'issue' ? getSelectedTakeOption() : getSelectedIncidentTakeOption();
            if (opt) {
                const max = parseFloat(opt.max_qty) || 1;
                const val = parseFloat($mainQty.val()) || 0;
                if (val > max) {
                    valid = false;
                    markQtyInputInvalid($mainQty, 'Haiwezi zidi ' + max + '!');
                } else if (val <= 0) {
                    valid = false;
                    markQtyInputInvalid($mainQty, 'Weka idadi sahihi (angalau 1).');
                }
            }
        }

        if (unit) {
            const selections = prefix === 'issue'
                ? collectIssueTakeSelections()
                : collectIncidentTakeSelections();
            const remaining = parseFloat(unit.remaining_items) || 0;
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt?.items_each) || 1)), 0);
            if (totalItems > remaining + 0.00001) {
                valid = false;
                const $preview = prefix === 'issue' ? $('#issueTakeLivePreview') : $('#incidentTakeLivePreview');
                $preview.addClass('alert-danger').removeClass('alert-info');
            } else {
                const $preview = prefix === 'issue' ? $('#issueTakeLivePreview') : $('#incidentTakeLivePreview');
                $preview.removeClass('alert-danger').addClass('alert-info');
            }
        }

        return valid;
    }

    function assertTakeOperationValid(prefix) {
        const units = prefix === 'issue' ? getSelectedIssueUnits() : getSelectedIncidentUnits();
        const unit = units.length === 1 ? units[0] : null;
        const selections = prefix === 'issue' ? collectIssueTakeSelections() : collectIncidentTakeSelections();

        if (!units.length) {
            return { valid: false, message: 'Chagua label moja au zaidi.' };
        }
        if (!selections.length) {
            return { valid: false, message: 'Kamilisha maelezo ya kuchukua (method, package, idadi).' };
        }
        if (!validateTakeQuantityInputs(prefix, unit)) {
            return { valid: false, message: 'Sahihisha idadi — haiwezi zidi kiwango cha juu (max) kilichoonyeshwa.' };
        }
        if (!selections.every(s => s.quantity > 0 && s.quantity <= s.max_qty)) {
            return { valid: false, message: 'Idadi ya kuchukua ni kubwa kuliko inavyoruhusiwa kwa kiwango hicho.' };
        }
        if (units.length > 1 && selections.length !== units.length) {
            return { valid: false, message: 'Kila label iliyochaguliwa lazima iwe na chaguo la kuchukua.' };
        }

        const withinStock = selections.every(s => {
            const u = s.unit || units.find(x => String(x.stock_unitID) === String(s.stock_unitID));
            if (!u) return false;
            const items = s.quantity * (parseFloat(s.opt?.items_each) || 1);
            return items <= (parseFloat(u.remaining_items) || 0) + 0.00001;
        });
        if (!withinStock) {
            return { valid: false, message: 'Jumla ya vitu unavyochukua inazidi stock iliyobaki kwenye label.' };
        }

        return { valid: true };
    }

    function validateHierarchyQuantities(prefix, unit) {
        return validateTakeQuantityInputs(prefix, unit)
            && (prefix === 'issue' ? collectIssueTakeSelections() : collectIncidentTakeSelections())
                .every(s => s.quantity > 0 && s.quantity <= s.max_qty);
    }

    function buildTakeCheckboxRow(opt, idx, prefix) {
        const leafName = opt.leaf_unit_name || 'item';
        const indent = opt.chain_position > 0
            ? '<span class="issue-take-chain-indent">' + '— '.repeat(Math.max(0, opt.chain_position - 1)) + '</span>'
            : '';
        const availBadge = opt.available_qty != null
            ? `<span class="issue-take-avail-badge">${opt.available_qty} ${escapeHtml(opt.label)}(s) available</span>`
            : '';
        const capacityLine = opt.capacity_per_main > 0
            ? `<div class="issue-take-capacity-line mt-1">1 ${escapeHtml(opt.main_package_name || 'Set')} has ${opt.capacity_per_main} ${escapeHtml(opt.label)}(s)</div>`
            : '';
        const checkboxClass = prefix === 'incident' ? 'incident-take-target-checkbox' : 'issue-take-target-checkbox';
        const qtyInputClass = prefix === 'incident' ? 'incident-take-row-qty-input' : 'issue-take-row-qty-input';
        const qtyWrapClass = prefix === 'incident' ? 'incident-take-row-qty' : 'issue-take-row-qty';
        const inputId = prefix + 'TakeTargetOpt' + idx;

        return `
            <div class="issue-take-target-option mb-3 pb-2 border-bottom">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input ${checkboxClass}"
                        id="${inputId}" value="${escapeHtml(opt.key)}"
                        data-max-qty="${opt.max_qty}" data-items-each="${opt.items_each}"
                        data-level="${escapeHtml(opt.level)}" data-label="${escapeHtml(opt.label)}">
                    <label class="custom-control-label" for="${inputId}">
                        ${indent}<strong>${escapeHtml(opt.label)}</strong>${availBadge}
                        ${capacityLine}
                        <div class="text-muted small mt-1">${escapeHtml(opt.detail || '')}</div>
                    </label>
                </div>
                <div class="${qtyWrapClass} mt-2" style="display:none;">
                    <label class="small font-weight-bold mb-1">How many ${escapeHtml(opt.label)}(s)?</label>
                    <input type="number" class="form-control form-control-sm ${qtyInputClass}"
                        min="1" max="${opt.max_qty}" step="1" value="1"
                        data-key="${escapeHtml(opt.key)}" data-max-qty="${opt.max_qty}" disabled>
                    <small class="text-muted d-block mt-1 issue-take-row-hint"
                        data-items-each="${opt.items_each}" data-label="${escapeHtml(opt.label)}">
                        Max ${opt.max_qty} · each = ${opt.items_each} ${escapeHtml(leafName)}
                    </small>
                    <div class="text-danger small store-qty-error mt-1" style="display:none;"></div>
                </div>
            </div>
        `;
    }

    function collectTakeSelectionsFromDom(prefix, unit) {
        const methodKey = prefix === 'incident' ? $('#incidentTakeMethod').val() : $('#issueTakeMethod').val();
        const options = unit._activeTakeOptions || unit.take_options || [];
        const optionMap = Object.fromEntries(options.map(o => [o.key, o]));
        const checkboxClass = prefix === 'incident' ? '.incident-take-target-checkbox' : '.issue-take-target-checkbox';
        const qtyClass = prefix === 'incident' ? '.incident-take-row-qty-input' : '.issue-take-row-qty-input';

        if (methodKey === 'sub') {
            if (unit._subHierarchy) {
                return collectHierarchyTakeSelections(prefix, unit);
            }
            const selections = [];
            $(checkboxClass + ':checked').each(function () {
                const key = $(this).val();
                const opt = optionMap[key];
                const qty = parseFloat($(this).closest('.issue-take-target-option').find(qtyClass).val());
                if (!opt || !(qty > 0)) return;
                selections.push({
                    take_key: key,
                    quantity: qty,
                    max_qty: parseFloat(opt.max_qty) || 0,
                    opt,
                });
            });
            return selections;
        }

        const opt = prefix === 'incident' ? getSelectedIncidentTakeOption() : getSelectedTakeOption();
        const qtyInput = prefix === 'incident' ? '#incidentTakeQty' : '#issueTakeQty';
        const qty = parseFloat($(qtyInput).val());
        if (!opt || !(qty > 0)) return [];
        return [{
            take_key: opt.key,
            quantity: qty,
            max_qty: parseFloat(opt.max_qty) || 0,
            opt,
        }];
    }

    function updateTakeLivePreview(prefix, unit) {
        const $preview = prefix === 'incident' ? $('#incidentTakeLivePreview') : $('#issueTakeLivePreview');
        const units = prefix === 'incident' ? getSelectedIncidentUnits() : getSelectedIssueUnits();

        if (!units.length) {
            $preview.hide().empty();
            return;
        }

        if (units.length > 1) {
            const selections = buildMultiLabelMainSelections(units);
            if (!selections.length) {
                $preview.hide().empty();
                return;
            }
            const lines = selections.map(s => {
                const items = s.quantity * (parseFloat(s.opt.items_each) || 1);
                const leafName = s.unit.leaf_unit_name || 'item';
                return `<li><strong>${escapeHtml(s.unit.label)}</strong> — 1 × ${escapeHtml(s.opt.label)} = ${items} ${escapeHtml(leafName)}</li>`;
            }).join('');
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt.items_each) || 1)), 0);
            $preview.html(
                `<strong>Main package from ${selections.length} label(s):</strong><ul class="mb-1 pl-3">${lines}</ul>` +
                `<span>Total deducting: <strong>${totalItems}</strong> item(s) across selected labels.</span>`
            ).show();
            return;
        }

        unit = unit || units[0];
        if (!unit) {
            $preview.hide().empty();
            return;
        }
        const methodKey = prefix === 'incident' ? $('#incidentTakeMethod').val() : $('#issueTakeMethod').val();
        const selections = prefix === 'incident' ? collectIncidentTakeSelections() : collectIssueTakeSelections();
        const leafName = unit.leaf_unit_name || 'item';

        if (!selections.length) {
            $preview.hide().empty();
            return;
        }

        const totalItems = selections.reduce((sum, s) => sum + (s.quantity * (parseFloat(s.opt.items_each) || 1)), 0);
        const remainingAfter = Math.max(0, (parseFloat(unit.remaining_items) || 0) - totalItems);
        const lines = selections.map(s => {
            const items = s.quantity * (parseFloat(s.opt.items_each) || 1);
            return `<li><strong>${s.quantity} × ${escapeHtml(s.opt.label)}</strong> = ${items} ${escapeHtml(leafName)}</li>`;
        }).join('');

        $preview.html(
            `<strong>From label "${escapeHtml(unit.label)}":</strong><ul class="mb-1 pl-3">${lines}</ul>` +
            `<span>Total deducting: <strong>${totalItems}</strong> ${escapeHtml(leafName)} · ` +
            `Remaining after issue: <strong>${remainingAfter}</strong> ${escapeHtml(leafName)}</span>`
        ).toggle(methodKey === 'sub' || methodKey === 'item' || selections.length > 0);
    }

    function renderIssueTakeTargetCheckboxes(method, unit) {
        $('#issueTakeTarget').hide();
        unit._selectedTakeKey = null;

        if (method.hierarchy && method.hierarchy.units && method.hierarchy.units.length) {
            renderSubPackageHierarchy(method.hierarchy, unit, 'issue');
            return;
        }

        const $list = $('#issueTakeTargetList');
        $list.empty().show();
        $('#issueTakeTargetToolbar').show();
        const leafName = unit?.leaf_unit_name || 'item';
        unit._subHierarchy = null;
        method.options.forEach((opt, idx) => {
            opt.leaf_unit_name = leafName;
            $list.append(buildTakeCheckboxRow(opt, idx, 'issue'));
        });
        $('#issueTakeTargetDetail').text('Check a sub-package level (Carton, Box, …) and enter how many you are taking from this label.');
        updateTakeLivePreview('issue', unit);
    }

    function collectIssueTakeSelections() {
        const units = getSelectedIssueUnits();
        if (!units.length) return [];

        if (units.length > 1) {
            return buildMultiLabelMainSelections(units).map(s => ({
                stock_unitID: s.unit.stock_unitID,
                take_key: s.take_key,
                quantity: 1,
                max_qty: s.max_qty,
                opt: s.opt,
                unit: s.unit,
            }));
        }

        const unit = units[0];
        const structure = getIssueTakeStructure(unit);
        const methodKey = $('#issueTakeMethod').val();

        if (!structure?.show_method_select) {
            const opt = structure.take_options[0];
            return opt ? [{
                stock_unitID: unit.stock_unitID,
                take_key: opt.key,
                quantity: 1,
                max_qty: parseFloat(opt.max_qty) || 1,
                opt,
                unit,
            }] : [];
        }

        if (methodKey === 'main') {
            const opt = getSelectedTakeOption()
                || (structure.methods || []).find(m => m.key === 'main')?.options?.[0];
            if (!opt) return [];
            return [{
                stock_unitID: unit.stock_unitID,
                take_key: opt.key,
                quantity: 1,
                max_qty: parseFloat(opt.max_qty) || 1,
                opt,
                unit,
            }];
        }

        return collectTakeSelectionsFromDom('issue', unit).map(s => ({
            ...s,
            stock_unitID: unit.stock_unitID,
            unit,
        }));
    }

    function renderIssueTakeTargets() {
        const unit = getSelectedIssueUnit();
        const methodKey = $('#issueTakeMethod').val();
        const $target = $('#issueTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        const $list = $('#issueTakeTargetList').empty().hide();
        $('#issueTakeTargetToolbar').hide();
        $('#issueTakeTargetDetail').text('');
        $('#issueTakeTargetWrap').hide();
        if (unit) unit._selectedTakeKey = null;

        if (!unit || !methodKey) {
            updateIssueTakeQtyLimits();
            updateTakeLivePreview('issue', unit);
            return;
        }

        const structure = unit._takeStructure || getIssueTakeStructure(unit);
        const method = (structure?.methods || []).find(m => m.key === methodKey);
        if (!method) {
            updateIssueTakeQtyLimits();
            return;
        }
        if (methodKey === 'sub') {
            if (method.hierarchy?.units?.length || (method.options || []).length) {
                renderIssueTakeTargetCheckboxes(method, unit);
                unit._subHierarchy = method.hierarchy || null;
                $('#issueTakeQtyWrap').hide();
                $('#issueTakeTargetWrap').show();
                updateIssueButton();
                return;
            }
            updateIssueTakeQtyLimits();
            return;
        }
        if (!(method.options || []).length) {
            updateIssueTakeQtyLimits();
            return;
        }

        unit._activeTakeOptions = method.options;

        const targetLabels = {
            main: 'Main Package',
            sub: 'Chagua Sub-Package',
            item: 'Unit Measure',
        };
        $('#issueTakeTargetLabel').html(
            (targetLabels[methodKey] || 'Package / Unit') + ' <span class="text-danger">*</span>'
        );

        if (methodKey === 'main') {
            $('#issueTakeQtyWrap').hide();
            $('#issueTakeQty').val(1).prop('readonly', true);
        } else {
            $('#issueTakeQtyWrap').show();
            $('#issueTakeQty').prop('readonly', false);
        }

        method.options.forEach(opt => {
            $target.append(`<option value="${escapeHtml(opt.key)}">${escapeHtml(opt.label)}</option>`);
        });

        if (method.options.length === 1) {
            $target.val(method.options[0].key);
            unit._selectedTakeKey = method.options[0].key;
            $('#issueTakeTargetDetail').text(method.options[0].detail || '');
            $('#issueTakeTargetWrap').show();
        } else {
            $target.show();
            $('#issueTakeTargetWrap').show();
        }

        updateIssueTakeQtyLimits();
    }

    function getSelectedTakeOption() {
        const unit = getSelectedIssueUnit();
        if (!unit) return null;

        const options = unit._activeTakeOptions || unit.take_options || [];
        const methodKey = $('#issueTakeMethod').val();
        let key = unit._selectedTakeKey;
        if (!key) {
            key = $('#issueTakeTarget').val();
        }
        if (!key && options.length === 1) {
            key = options[0].key;
        }
        return options.find(o => o.key === key) || null;
    }

    function updateIssueTakeQtyLimits() {
        const opt = getSelectedTakeOption();
        const unit = getSelectedIssueUnit();
        const methodKey = $('#issueTakeMethod').val();
        const $qty = $('#issueTakeQty');
        if (!opt) {
            $qty.val(1).prop('readonly', false);
            $('#issueTakeQtyHint').text('');
            return;
        }
        const maxQty = parseFloat(opt.max_qty) || 1;
        const itemsEach = parseFloat(opt.items_each) || 1;
        const leafName = unit?.leaf_unit_name || 'item';

        if (methodKey === 'main' || opt.level === 'package' || opt.level === 'full') {
            $('#issueTakeQtyWrap').hide();
            $qty.val(1).prop('readonly', true);
            $('#issueTakeQtyHint').text('Quantity auto = 1 (whole ' + opt.label + ' from selected label).');
            return;
        }

        $('#issueTakeQtyWrap').show();
        $qty.attr('min', 1);
        $qty.attr('max', maxQty);
        if ((parseFloat($qty.val()) || 0) <= 0) {
            $qty.val(1);
        }
        $qty.prop('readonly', false);
        if (opt.level === 'item') {
            $('#issueTakeQtyHint').text(
                'Max ' + maxQty + ' ' + leafName
                + (opt.detail ? ' · ' + opt.detail.replace(/^Request by unit measure\s*/i, '') : '')
            );
        } else {
            $('#issueTakeQtyHint').text(
                'Max ' + maxQty + ' · each = ' + itemsEach + ' ' + leafName
                + ' (total items = quantity × ' + itemsEach + ')'
            );
        }
    }

    function hasCanReturnSelected() {
        return getSelectedIssueUnits().some(u => u.requisition_status === 'can_return');
    }

    function toggleExpectedReturnField() {
        if (hasCanReturnSelected()) {
            $('#issueExpectedReturnSection').show();
        } else {
            $('#issueExpectedReturnSection').hide();
            $('#issueExpectedReturnDate').val('');
        }
    }

    function updateIssueButton() {
        const recipient = $('#issueRecipientSelect').val();
        const itemId = $('#issueItemSelect').val();
        const units = getSelectedIssueUnits();
        const unit = units.length === 1 ? units[0] : null;
        const structure = unit ? getIssueTakeStructure(unit) : null;
        const needsMethod = units.length === 1 && structure && structure.show_method_select;
        const methodOk = units.length > 1 || !needsMethod || !!$('#issueTakeMethod').val();
        const selections = collectIssueTakeSelections();
        const needsReturnDate = hasCanReturnSelected();
        const hasReturnDate = !!$('#issueExpectedReturnDate').val();
        const returnDateOk = !needsReturnDate || hasReturnDate;
        const methodKey = $('#issueTakeMethod').val();

        let withinStock = true;
        if (unit && selections.length) {
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt?.items_each) || 1)), 0);
            withinStock = totalItems <= (parseFloat(unit.remaining_items) || 0) + 0.00001;
        } else if (selections.length) {
            withinStock = selections.every(s => {
                const u = s.unit || units.find(x => String(x.stock_unitID) === String(s.stock_unitID));
                if (!u) return false;
                const items = s.quantity * (parseFloat(s.opt?.items_each) || 1);
                return items <= (parseFloat(u.remaining_items) || 0) + 0.00001;
            });
        }

        let takeOk = units.length > 0 && methodOk && selections.length > 0
            && selections.every(s => s.quantity > 0 && s.quantity <= s.max_qty)
            && withinStock;
        if (units.length > 1 && selections.length !== units.length) {
            takeOk = false;
        }
        if (units.length && $('#issueTakeSection').is(':visible')) {
            takeOk = takeOk && validateTakeQuantityInputs('issue', unit);
        }
        if (methodKey === 'sub' && unit && unit._subHierarchy) {
            takeOk = takeOk && validateHierarchyQuantities('issue', unit);
        }
        $('#btnIssueItem').prop('disabled', !(recipient && itemId && takeOk && returnDateOk));
        toggleExpectedReturnField();
        updateTakeLivePreview('issue', unit);
    }

    $('#issueRecipientType').on('change', populateRecipientSelect);
    $('#issueRecipientSelect').on('change', updateIssueButton);
    $('#issueCategoryFilter').on('change', function () {
        populateIssueItemSelect();
        $('#issueItemSelect').val('').trigger('change');
        $('#issueStockInfo, #issueLabelsSection, #issueTakeSection, #issueExpectedReturnSection').hide();
        $('#issueLabelsList').empty();
        resetIssueTakeFields();
        updateIssueButton();
    });
    $('#issueExpectedReturnDate').on('change', updateIssueButton);
    $('#issueItemSelect').on('change', function () {
        renderIssueLabels($(this).val());
    });
    $(document).on('change', '.issue-label-checkbox', function () {
        renderIssueTakeForLabels();
    });
    $('#issueTakeMethod').on('change', function () {
        renderIssueTakeTargets();
        updateIssueButton();
    });
    $('#issueTakeTarget').on('change', function () {
        const unit = getSelectedIssueUnit();
        if (unit) unit._selectedTakeKey = $(this).val();
        const opt = getSelectedTakeOption();
        $('#issueTakeTargetDetail').text(opt?.detail || '');
        updateIssueTakeQtyLimits();
        updateIssueButton();
    });
    $(document).on('change', '.issue-take-target-checkbox', function () {
        const $row = $(this).closest('.issue-take-target-option');
        const $qtyWrap = $row.find('.issue-take-row-qty');
        const $qtyInput = $row.find('.issue-take-row-qty-input');
        if ($(this).is(':checked')) {
            $qtyWrap.show();
            $qtyInput.prop('disabled', false);
        } else {
            $qtyWrap.hide();
            $qtyInput.prop('disabled', true).val(1);
        }
        updateIssueButton();
    });
    $(document).on('input change', '.issue-take-row-qty-input', updateIssueButton);
    $(document).on('input change', '#issueTakeQty', updateIssueButton);

    $(document).on('change', '.issue-hier-unit-check', function () {
        const $block = $(this).closest('.issue-hier-unit');
        const $body = $block.find('.issue-hier-unit-body');
        if ($(this).is(':checked')) {
            $block.addClass('is-checked');
            $body.slideDown(150);
            if ($block.find('.issue-hier-mode-radio[value="full"]').length) {
                $block.find('.issue-hier-mode-radio[value="full"]').prop('checked', true).trigger('change');
            }
        } else {
            $block.removeClass('is-checked');
            $body.slideUp(150);
        }
        updateIssueButton();
    });
    $(document).on('change', '.issue-hier-mode-radio', function () {
        const $block = $(this).closest('.issue-hier-unit');
        const isPartial = $(this).val() === 'partial';
        const $wrap = $block.find('.issue-hier-partial-wrap');
        const $qty = $wrap.find('.issue-hier-partial-qty');
        if (isPartial) {
            $wrap.show();
            $qty.prop('disabled', false);
        } else {
            $wrap.hide();
            $qty.prop('disabled', true).val(1);
        }
        updateIssueButton();
    });
    $(document).on('input change', '.issue-hier-partial-qty', updateIssueButton);

    $(document).on('change', '.incident-hier-unit-check', function () {
        const $block = $(this).closest('.issue-hier-unit');
        const $body = $block.find('.issue-hier-unit-body');
        if ($(this).is(':checked')) {
            $block.addClass('is-checked');
            $body.slideDown(150);
            if ($block.find('.incident-hier-mode-radio[value="full"]').length) {
                $block.find('.incident-hier-mode-radio[value="full"]').prop('checked', true).trigger('change');
            }
        } else {
            $block.removeClass('is-checked');
            $body.slideUp(150);
        }
        updateIncidentButton();
    });
    $(document).on('change', '.incident-hier-mode-radio', function () {
        const $block = $(this).closest('.issue-hier-unit');
        const isPartial = $(this).val() === 'partial';
        const $wrap = $block.find('.issue-hier-partial-wrap');
        const $qty = $wrap.find('.incident-hier-partial-qty');
        if (isPartial) {
            $wrap.show();
            $qty.prop('disabled', false);
        } else {
            $wrap.hide();
            $qty.prop('disabled', true).val(1);
        }
        updateIncidentButton();
    });
    $(document).on('input change', '.incident-hier-partial-qty', updateIncidentButton);

    $('#issueItemModal').on('shown.bs.modal', function () {
        resetIssueForm();
        initIssueSelect2();
    });

    $('#issueItemForm').on('submit', async function (e) {
        e.preventDefault();
        const recipientVal = $('#issueRecipientSelect').val();
        if (!recipientVal || !recipientVal.includes(':')) {
            showAlert('Select a recipient.', 'danger');
            return;
        }
        const [recipientType, recipientId] = recipientVal.split(':');
        const takeCheck = assertTakeOperationValid('issue');
        if (!takeCheck.valid) {
            showAlert(takeCheck.message, 'danger');
            return;
        }
        const selections = collectIssueTakeSelections();

        try {
            const payload = {
                recipient_type: recipientType,
                recipient_id: parseInt(recipientId, 10),
                issues: selections.map(s => ({
                    stock_unitID: s.stock_unitID,
                    take_key: s.take_key,
                    quantity: s.quantity,
                })),
                notes: $('#issueNotes').val() || null,
            };
            if (hasCanReturnSelected()) {
                payload.expected_return_date = $('#issueExpectedReturnDate').val();
            }
            const json = await postJson(routes.requisitionsStore, payload);
            showAlert(json.message, 'success');
            $('#issueItemModal').modal('hide');
            (json.requisitions || []).forEach(req => {
                addStoreTableRow('requisitionsTable', buildRequisitionRow(req));
            });
            refreshStoreGridIfActive('requisitionsTable');

            (json.depleted_stock_unitIDs || []).forEach(id => {
                availableStockList = availableStockList.filter(u => String(u.stock_unitID) !== String(id));
            });
            (json.available_stock_units || []).forEach(u => {
                const idx = availableStockList.findIndex(x => String(x.stock_unitID) === String(u.stock_unitID));
                if (idx >= 0) availableStockList[idx] = u;
                else availableStockList.push(u);
            });

            (json.stock_entries || []).forEach(entry => {
                upsertStoreTableRow('stockTable', `tr[data-stock-entry-id="${entry.stock_entryID}"]`, buildStockRow(entry));
            });
            if ($('#requisitionReturnFilter').val() !== 'all') {
                $('#requisitionReturnFilter').trigger('change');
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-return-requisition', function () {
        const $btn = $(this);
        $('#returnRequisitionId').val($btn.data('id'));
        $('#returnItemSummary').html(
            '<strong>Item:</strong> ' + escapeHtml($btn.data('item')) +
            ' &nbsp;|&nbsp; <strong>Label:</strong> ' + escapeHtml($btn.data('label')) +
            ($btn.data('taken') ? ' &nbsp;|&nbsp; <strong>Must return:</strong> ' + escapeHtml($btn.data('taken')) : '')
        );
        $('#returnResponsiblePerson').val($btn.data('recipient'));
        $('#returnedAtDate').val(new Date().toISOString().slice(0, 10));
        $('#returnDescription').val('');
        $('#returnCondition').val('');
        $('#badDescription').val('');
        $('#returnIncidentReasonSection').hide();
        $('#returnItemModal').modal('show');
    });

    function toggleReturnIncidentReason() {
        const condition = $('#returnCondition').val();
        if (condition === 'damaged' || condition === 'lost') {
            $('#returnIncidentReasonSection').show();
            const isLost = condition === 'lost';
            $('#returnIncidentAlert').html(
                isLost
                    ? 'This will be recorded in <strong>Lost Items</strong>. The person who received this item will be marked as responsible.'
                    : 'This will be recorded in <strong>Damaged Items</strong>. The person who received this item will be marked as responsible.'
            );
            $('#returnIncidentReasonLabel').html(
                (isLost ? 'Why was the item lost?' : 'Describe the damage') + ' <span class="text-danger">*</span>'
            );
        } else {
            $('#returnIncidentReasonSection').hide();
            $('#badDescription').val('');
        }
    }

    $('#returnCondition').on('change', toggleReturnIncidentReason);

    $('#returnItemForm').on('submit', async function (e) {
        e.preventDefault();
        const requisitionId = $('#returnRequisitionId').val();
        const condition = $('#returnCondition').val();
        if (!condition) {
            showAlert('Select return status.', 'danger');
            return;
        }
        if ((condition === 'damaged' || condition === 'lost') && !$('#badDescription').val().trim()) {
            showAlert('Enter a reason for this return status.', 'danger');
            return;
        }
        try {
            const json = await postJson(routes.requisitionsReturn(requisitionId), {
                returned_at: $('#returnedAtDate').val(),
                return_description: $('#returnDescription').val() || null,
                return_condition: condition,
                bad_description: (condition === 'damaged' || condition === 'lost') ? $('#badDescription').val().trim() : null,
            });
            showAlert(json.message, 'success');
            $('#returnItemModal').modal('hide');
            if (json.requisition) {
                upsertStoreTableRow('requisitionsTable', `tr[data-requisition-id="${json.requisition.requisitionID}"]`, buildRequisitionRow(json.requisition));
            }
            if (json.incident) {
                addStoreTableRow(incidentTableId(json.incident), buildIncidentRow(json.incident));
                refreshStoreGridIfActive(incidentTableId(json.incident));
            }
            if (json.stock_entry) {
                upsertStoreTableRow('stockTable', `tr[data-stock-entry-id="${json.stock_entry.stock_entryID}"]`, buildStockRow(json.stock_entry));
                syncAvailableStockFromEntry(json.stock_entry);
            }
            if (json.returned_stock_unit) {
                const exists = availableStockList.some(u => String(u.stock_unitID) === String(json.returned_stock_unit.stock_unitID));
                if (!exists) availableStockList.push(json.returned_stock_unit);
            }
            if ($('#requisitionReturnFilter').val() !== 'all') {
                $('#requisitionReturnFilter').trigger('change');
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $('#requisitionReturnFilter').on('change', function () {
        if ($.fn.DataTable.isDataTable('#requisitionsTable')) {
            $('#requisitionsTable').DataTable().draw();
        }
        refreshStoreGridIfActive('requisitionsTable');
    });

    // Damaged / Lost incidents
    function openIncidentModal(type) {
        $('#incidentType').val(type);
        const isLost = type === 'lost';
        $('#incidentModalTitle').html(
            '<i class="fa ' + (isLost ? 'fa-search' : 'fa-warning') + '"></i> Record ' + (isLost ? 'Lost' : 'Damaged') + ' Item'
        );
        $('#incidentModal').modal('show');
    }

    $('#btnAddDamagedItem').on('click', () => openIncidentModal('damaged'));
    $('#btnAddLostItem').on('click', () => openIncidentModal('lost'));

    function initIncidentSelect2() {
        $('#incidentModal .incident-form-select').each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownParent: $('#incidentModal'),
            });
        });
    }

    function resetIncidentForm() {
        $('#incidentResponsibleType').val('');
        $('#incidentResponsibleSelect').prop('disabled', true).empty().append('<option value="">Select responsible type first...</option>');
        $('#incidentOtherName').val('');
        $('#incidentOtherNameWrap').hide();
        $('#incidentResponsibleSelectWrap').show();
        $('#incidentItemSelect').empty().append('<option value="">Search or select item in stock...</option>');
        $('#incidentStockInfo, #incidentLabelsSection, #incidentTakeSection').hide();
        $('#incidentLabelsList').empty();
        $('#incidentTakeMethod').empty().append('<option value="">Choose request method...</option>');
        $('#incidentTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#incidentTakeTargetList').empty().hide();
        $('#incidentTakeTargetToolbar').hide();
        $('#incidentTakeChainInfo').hide().empty();
        $('#incidentTakeMethodWrap, #incidentTakeTargetWrap').hide();
        $('#incidentTakeTargetDetail, #incidentTakeQtyHint').text('');
        $('#incidentTakeQtyWrap').show();
        $('#incidentTakeQty').val(1).prop('readonly', false);
        $('#incidentReason, #incidentNotes').val('');
        $('#btnSaveIncident').prop('disabled', true);
        populateIncidentItemSelect();
    }

    function populateIncidentItemSelect() {
        const itemMap = {};
        availableStockList.forEach(u => {
            if (!itemMap[u.itemID]) itemMap[u.itemID] = u.item_name;
        });
        const $sel = $('#incidentItemSelect');
        $sel.find('option:not(:first)').remove();
        Object.keys(itemMap).sort((a, b) => itemMap[a].localeCompare(itemMap[b])).forEach(itemID => {
            $sel.append(new Option(itemMap[itemID], itemID, false, false));
        });
        if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger('change.select2');
    }

    function toggleIncidentResponsibleFields() {
        const type = $('#incidentResponsibleType').val();
        if (type === 'other') {
            $('#incidentResponsibleSelectWrap').hide();
            $('#incidentOtherNameWrap').show();
        } else {
            $('#incidentResponsibleSelectWrap').show();
            $('#incidentOtherNameWrap').hide();
            $('#incidentOtherName').val('');
        }
        updateIncidentButton();
    }

    function populateIncidentResponsibleSelect() {
        const type = $('#incidentResponsibleType').val();
        const $sel = $('#incidentResponsibleSelect');
        $sel.empty();
        if (!type || type === 'other') {
            $sel.prop('disabled', true).append('<option value="">—</option>');
            toggleIncidentResponsibleFields();
            return;
        }
        $sel.prop('disabled', false).append('<option value="">Search or select person...</option>');
        requestersList.filter(r => r.type === type).forEach(r => {
            let label = r.name;
            if (r.employee_number) label += ` (${r.employee_number})`;
            if (type === 'student' && r.class_name) label += ` — ${r.class_name}`;
            $sel.append(new Option(label, String(r.id), false, false));
        });
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search or select person...',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $('#incidentModal'),
        });
        toggleIncidentResponsibleFields();
    }

    function getSelectedIncidentUnits() {
        const ids = [];
        $('.incident-label-checkbox:checked').each(function () {
            ids.push($(this).val());
        });
        return ids
            .map(id => availableStockList.find(x => String(x.stock_unitID) === String(id)))
            .filter(Boolean);
    }

    function getSelectedIncidentUnit() {
        const units = getSelectedIncidentUnits();
        return units.length === 1 ? units[0] : null;
    }

    function renderIncidentTakeForLabels() {
        const units = getSelectedIncidentUnits();
        resetIncidentTakeFields();

        if (!units.length) {
            $('#incidentTakeSection').hide();
            updateIncidentButton();
            return;
        }

        if (units.length > 1) {
            $('#incidentTakeSection').show();
            $('#incidentTakeChainInfo')
                .html(
                    '<strong>Selected labels:</strong> ' + escapeHtml(units.map(u => u.label).join(', ')) +
                    '<br><span class="text-muted">By Main Package — quantity auto = 1 kwa kila label iliyochaguliwa.</span>'
                )
                .show();
            $('#incidentTakeMethodWrap, #incidentTakeTargetWrap, #incidentTakeQtyWrap').hide();
            $('#incidentTakeMethod').val('main');
            updateTakeLivePreview('incident', null);
            updateIncidentButton();
            return;
        }

        renderIncidentTakeOptions(units[0]);
        updateIncidentButton();
    }

    function resetIncidentTakeFields() {
        $('#incidentTakeMethod').empty().append('<option value="">Choose request method...</option>');
        $('#incidentTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#incidentTakeTargetList').empty().hide();
        $('#incidentTakeTargetToolbar').hide();
        $('#incidentTakeChainInfo').hide().empty();
        $('#incidentTakeMethodWrap, #incidentTakeTargetWrap').hide();
        $('#incidentTakeTargetDetail').text('');
        $('#incidentTakeQtyWrap').show();
        $('#incidentTakeQty').val(1).prop('readonly', false);
        $('#incidentTakeQtyHint').text('');
    }

    function renderIncidentLabels(itemID) {
        const units = availableStockList.filter(u => String(u.itemID) === String(itemID));
        const $list = $('#incidentLabelsList').empty();
        $('#incidentTakeSection').hide();
        resetIncidentTakeFields();
        if (!units.length) {
            $('#incidentLabelsSection, #incidentStockInfo').hide();
            updateIncidentButton();
            return;
        }
        const pkgUnits = [...new Set(units.map(u => u.package_unit_display))];
        const remainingTotal = units.reduce((sum, u) => sum + (parseFloat(u.remaining_items) || 0), 0);
        $('#incidentPackageUnit').text(pkgUnits.join(', '));
        $('#incidentAvailableCount').text(units.length + ' label(s), ' + remainingTotal + ' item(s) left');
        $('#incidentStockInfo, #incidentLabelsSection').show();
        units.forEach(u => {
            const remainText = u.store_method === 'package'
                ? ` — ${escapeHtml(u.package_unit_display)} · left ${u.remaining_items}/${u.initial_items} ${escapeHtml(u.leaf_unit_name || 'item')}`
                : ` — ${escapeHtml(u.package_unit_display)}`;
            $list.append(`
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input incident-label-checkbox"
                        id="incidentLabel${u.stock_unitID}" value="${u.stock_unitID}">
                    <label class="custom-control-label" for="incidentLabel${u.stock_unitID}">
                        <strong>${escapeHtml(u.label)}</strong>
                        <span class="text-muted small">${remainText}</span>
                    </label>
                </div>
            `);
        });
        updateIncidentButton();
    }

    function renderIncidentTakeOptions(unit) {
        resetIncidentTakeFields();
        if (!unit) {
            $('#incidentTakeSection').hide();
            return;
        }
        const structure = getIssueTakeStructure(unit);
        if (!structure || !(structure.take_options || []).length) {
            $('#incidentTakeSection').hide();
            return;
        }
        $('#incidentTakeSection').show();
        if (structure.contains_line || structure.chain_summary) {
            $('#incidentTakeChainInfo')
                .html('<strong>Package chain:</strong> ' + escapeHtml(structure.contains_line || structure.chain_summary))
                .show();
        }
        if (!structure.show_method_select) {
            const opt = structure.take_options[0];
            $('#incidentTakeMethodWrap').hide();
            $('#incidentTakeTargetWrap').hide();
            $('#incidentTakeQtyWrap').hide();
            $('#incidentTakeQty').val(1).prop('readonly', true);
            unit._activeTakeOptions = structure.take_options;
            unit._selectedTakeKey = opt.key;
            updateIncidentTakeQtyLimits();
            return;
        }
        $('#incidentTakeMethodWrap').show();
        const $method = $('#incidentTakeMethod').empty().append('<option value="">Choose request method...</option>');
        (structure.methods || []).forEach(m => {
            $method.append(`<option value="${escapeHtml(m.key)}">${escapeHtml(m.label)}</option>`);
        });
        unit._takeStructure = structure;
        $('#incidentTakeTargetWrap').hide();
    }

    function renderIncidentTakeTargetCheckboxes(method, unit) {
        $('#incidentTakeTarget').hide();
        unit._selectedTakeKey = null;

        if (method.hierarchy && method.hierarchy.units && method.hierarchy.units.length) {
            renderSubPackageHierarchy(method.hierarchy, unit, 'incident');
            return;
        }

        const $list = $('#incidentTakeTargetList');
        $list.empty().show();
        $('#incidentTakeTargetToolbar').show();
        const leafName = unit?.leaf_unit_name || 'item';
        unit._subHierarchy = null;
        method.options.forEach((opt, idx) => {
            opt.leaf_unit_name = leafName;
            $list.append(buildTakeCheckboxRow(opt, idx, 'incident'));
        });
        $('#incidentTakeTargetDetail').text('Check a sub-package level and enter how many you are taking from this label.');
        updateTakeLivePreview('incident', unit);
    }

    function getSelectedIncidentTakeOption() {
        const unit = getSelectedIncidentUnit();
        if (!unit) return null;
        const options = unit._activeTakeOptions || unit.take_options || [];
        let key = unit._selectedTakeKey;
        if (!key) key = $('#incidentTakeTarget').val();
        if (!key && options.length === 1) key = options[0].key;
        return options.find(o => o.key === key) || null;
    }

    function collectIncidentTakeSelections() {
        const units = getSelectedIncidentUnits();
        if (!units.length) return [];

        if (units.length > 1) {
            return buildMultiLabelMainSelections(units).map(s => ({
                stock_unitID: s.unit.stock_unitID,
                take_key: s.take_key,
                quantity: 1,
                max_qty: s.max_qty,
                opt: s.opt,
                unit: s.unit,
            }));
        }

        const unit = units[0];
        const structure = getIssueTakeStructure(unit);
        const methodKey = $('#incidentTakeMethod').val();

        if (!structure?.show_method_select) {
            const opt = structure.take_options[0];
            return opt ? [{
                stock_unitID: unit.stock_unitID,
                take_key: opt.key,
                quantity: 1,
                max_qty: parseFloat(opt.max_qty) || 1,
                opt,
                unit,
            }] : [];
        }

        if (methodKey === 'main') {
            const opt = getSelectedIncidentTakeOption()
                || (structure.methods || []).find(m => m.key === 'main')?.options?.[0];
            if (!opt) return [];
            return [{
                stock_unitID: unit.stock_unitID,
                take_key: opt.key,
                quantity: 1,
                max_qty: parseFloat(opt.max_qty) || 1,
                opt,
                unit,
            }];
        }

        return collectTakeSelectionsFromDom('incident', unit).map(s => ({
            ...s,
            stock_unitID: unit.stock_unitID,
            unit,
        }));
    }

    function renderIncidentTakeTargets() {
        const unit = getSelectedIncidentUnit();
        const methodKey = $('#incidentTakeMethod').val();
        const $target = $('#incidentTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#incidentTakeTargetList').empty().hide();
        $('#incidentTakeTargetToolbar').hide();
        $('#incidentTakeTargetDetail').text('');
        $('#incidentTakeTargetWrap').hide();
        if (unit) unit._selectedTakeKey = null;
        if (!unit || !methodKey) {
            updateIncidentTakeQtyLimits();
            return;
        }
        const structure = unit._takeStructure || getIssueTakeStructure(unit);
        const method = (structure?.methods || []).find(m => m.key === methodKey);
        if (!method || !(method.options || []).length) {
            updateIncidentTakeQtyLimits();
            return;
        }
        unit._activeTakeOptions = method.options;
        const targetLabels = { main: 'Main Package', sub: 'Chagua Sub-Package', item: 'Unit Measure' };
        $('#incidentTakeTargetLabel').html((targetLabels[methodKey] || 'Package / Unit') + ' <span class="text-danger">*</span>');
        if (methodKey === 'sub') {
            renderIncidentTakeTargetCheckboxes(method, unit);
            unit._subHierarchy = method.hierarchy || null;
            $('#incidentTakeQtyWrap').hide();
            $('#incidentTakeTargetWrap').show();
            updateIncidentButton();
            return;
        }
        if (methodKey === 'main') {
            $('#incidentTakeQtyWrap').hide();
            $('#incidentTakeQty').val(1).prop('readonly', true);
        } else {
            $('#incidentTakeQtyWrap').show();
            $('#incidentTakeQty').prop('readonly', false);
        }
        method.options.forEach(opt => {
            $target.append(`<option value="${escapeHtml(opt.key)}">${escapeHtml(opt.label)}</option>`);
        });
        if (method.options.length === 1) {
            $target.val(method.options[0].key);
            unit._selectedTakeKey = method.options[0].key;
            $('#incidentTakeTargetDetail').text(method.options[0].detail || '');
            $('#incidentTakeTargetWrap').show();
        } else {
            $target.show();
            $('#incidentTakeTargetWrap').show();
        }
        updateIncidentTakeQtyLimits();
    }

    function updateIncidentTakeQtyLimits() {
        const opt = getSelectedIncidentTakeOption();
        const unit = getSelectedIncidentUnit();
        const methodKey = $('#incidentTakeMethod').val();
        const $qty = $('#incidentTakeQty');
        if (!opt) {
            $qty.val(1).prop('readonly', false);
            $('#incidentTakeQtyHint').text('');
            return;
        }
        const maxQty = parseFloat(opt.max_qty) || 1;
        const itemsEach = parseFloat(opt.items_each) || 1;
        const leafName = unit?.leaf_unit_name || 'item';

        if (methodKey === 'main' || opt.level === 'package' || opt.level === 'full') {
            $('#incidentTakeQtyWrap').hide();
            $qty.val(1).prop('readonly', true);
            $('#incidentTakeQtyHint').text('Quantity auto = 1 (whole ' + opt.label + ' from selected label).');
            return;
        }

        $('#incidentTakeQtyWrap').show();
        $qty.attr('min', 1);
        $qty.attr('max', maxQty);
        if ((parseFloat($qty.val()) || 0) <= 0) {
            $qty.val(1);
        }
        $qty.prop('readonly', false);
        if (opt.level === 'item') {
            $('#incidentTakeQtyHint').text('Max ' + maxQty + ' ' + leafName);
        } else {
            $('#incidentTakeQtyHint').text('Max ' + maxQty + ' · each = ' + itemsEach + ' ' + leafName);
        }
    }

    function updateIncidentButton() {
        const type = $('#incidentResponsibleType').val();
        const personOk = type === 'other'
            ? !!$('#incidentOtherName').val().trim()
            : !!(type && $('#incidentResponsibleSelect').val());
        const itemId = $('#incidentItemSelect').val();
        const units = getSelectedIncidentUnits();
        const unit = units.length === 1 ? units[0] : null;
        const structure = unit ? getIssueTakeStructure(unit) : null;
        const needsMethod = units.length === 1 && structure && structure.show_method_select;
        const methodOk = units.length > 1 || !needsMethod || !!$('#incidentTakeMethod').val();
        const selections = collectIncidentTakeSelections();
        const reasonOk = !!$('#incidentReason').val().trim();
        const methodKey = $('#incidentTakeMethod').val();

        let withinStock = true;
        if (unit && selections.length) {
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt?.items_each) || 1)), 0);
            withinStock = totalItems <= (parseFloat(unit.remaining_items) || 0) + 0.00001;
        } else if (selections.length) {
            withinStock = selections.every(s => {
                const u = s.unit || units.find(x => String(x.stock_unitID) === String(s.stock_unitID));
                if (!u) return false;
                const items = s.quantity * (parseFloat(s.opt?.items_each) || 1);
                return items <= (parseFloat(u.remaining_items) || 0) + 0.00001;
            });
        }

        let takeOk = units.length > 0 && methodOk && selections.length > 0
            && selections.every(s => s.quantity > 0 && s.quantity <= s.max_qty)
            && withinStock;
        if (units.length > 1 && selections.length !== units.length) {
            takeOk = false;
        }
        if (units.length && $('#incidentTakeSection').is(':visible')) {
            takeOk = takeOk && validateTakeQuantityInputs('incident', unit);
        }
        if (methodKey === 'sub' && unit && unit._subHierarchy) {
            takeOk = takeOk && validateHierarchyQuantities('incident', unit);
        }
        $('#btnSaveIncident').prop('disabled', !(personOk && itemId && takeOk && reasonOk));
        updateTakeLivePreview('incident', unit);
    }

    $('#incidentResponsibleType').on('change', populateIncidentResponsibleSelect);
    $('#incidentResponsibleSelect, #incidentOtherName, #incidentReason').on('change input', updateIncidentButton);
    $('#incidentItemSelect').on('change', function () { renderIncidentLabels($(this).val()); });
    $(document).on('change', '.incident-label-checkbox', function () {
        renderIncidentTakeForLabels();
    });
    $('#incidentTakeMethod').on('change', renderIncidentTakeTargets);
    $('#incidentTakeTarget').on('change', function () {
        const unit = getSelectedIncidentUnit();
        if (unit) unit._selectedTakeKey = $(this).val();
        const opt = getSelectedIncidentTakeOption();
        $('#incidentTakeTargetDetail').text(opt?.detail || '');
        updateIncidentTakeQtyLimits();
        updateIncidentButton();
    });
    $(document).on('change', '.incident-take-target-checkbox', function () {
        const $row = $(this).closest('.issue-take-target-option');
        const $qtyWrap = $row.find('.incident-take-row-qty');
        const $qtyInput = $row.find('.incident-take-row-qty-input');
        if ($(this).is(':checked')) {
            $qtyWrap.show();
            $qtyInput.prop('disabled', false);
        } else {
            $qtyWrap.hide();
            $qtyInput.prop('disabled', true).val(1);
        }
        updateIncidentButton();
    });
    $(document).on('input change', '.incident-take-row-qty-input, #incidentTakeQty', updateIncidentButton);

    $('#incidentModal').on('shown.bs.modal', function () {
        resetIncidentForm();
        initIncidentSelect2();
    });

    $('#incidentForm').on('submit', async function (e) {
        e.preventDefault();
        const takeCheck = assertTakeOperationValid('incident');
        if (!takeCheck.valid) {
            showAlert(takeCheck.message, 'danger');
            return;
        }
        const selections = collectIncidentTakeSelections();
        const respType = $('#incidentResponsibleType').val();
        const payload = {
            incident_type: $('#incidentType').val(),
            responsible_type: respType,
            reason: $('#incidentReason').val().trim(),
            issues: selections.map(s => ({
                stock_unitID: s.stock_unitID,
                take_key: s.take_key,
                quantity: s.quantity,
            })),
            notes: $('#incidentNotes').val() || null,
        };
        if (respType === 'other') {
            payload.responsible_name = $('#incidentOtherName').val().trim();
        } else {
            payload.responsible_id = parseInt($('#incidentResponsibleSelect').val(), 10);
        }
        try {
            const json = await postJson(routes.incidentsStore, payload);
            showAlert(json.message, 'success');
            $('#incidentModal').modal('hide');
            (json.incidents || []).forEach(inc => {
                addStoreTableRow(incidentTableId(inc), buildIncidentRow(inc));
            });
            syncStockFromIncidentResponse(json);
            if (storeDashboardData) {
                const dash = await getJson(routes.storeDashboard);
                if (dash.dashboard) renderStoreDashboard(dash.dashboard);
            }
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    function populateEditIncidentResponsible(type, selectedId) {
        const $sel = $('#editIncidentResponsibleSelect');
        $sel.empty();
        if (type === 'other') {
            $('#editIncidentResponsibleSelectWrap').hide();
            $('#editIncidentOtherNameWrap').show();
            return;
        }
        $('#editIncidentResponsibleSelectWrap').show();
        $('#editIncidentOtherNameWrap').hide();
        $sel.append('<option value="">Select person...</option>');
        requestersList.filter(r => r.type === type).forEach(r => {
            let label = r.name;
            if (r.employee_number) label += ` (${r.employee_number})`;
            $sel.append(new Option(label, String(r.id), false, String(r.id) === String(selectedId)));
        });
    }

    $(document).on('click', '.btn-edit-incident', function () {
        const $btn = $(this);
        $('#editIncidentId').val($btn.data('id'));
        $('#editIncidentTableId').val($btn.data('table'));
        const type = $btn.data('type');
        $('#editIncidentResponsibleType').val(type);
        populateEditIncidentResponsible(type, $btn.data('rid'));
        if (type === 'other') $('#editIncidentOtherName').val($btn.data('name'));
        $('#editIncidentReason').val($btn.data('reason'));
        $('#editIncidentNotes').val($btn.data('notes') || '');
        $('#editIncidentModal').modal('show');
    });

    $('#editIncidentResponsibleType').on('change', function () {
        populateEditIncidentResponsible($(this).val(), '');
    });

    $('#editIncidentForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#editIncidentId').val();
        const type = $('#editIncidentResponsibleType').val();
        const payload = {
            responsible_type: type,
            reason: $('#editIncidentReason').val().trim(),
            notes: $('#editIncidentNotes').val() || null,
        };
        if (type === 'other') payload.responsible_name = $('#editIncidentOtherName').val().trim();
        else payload.responsible_id = parseInt($('#editIncidentResponsibleSelect').val(), 10);
        try {
            const json = await postJson(routes.incidentsUpdate(id), payload);
            showAlert(json.message, 'success');
            $('#editIncidentModal').modal('hide');
            const tableId = $('#editIncidentTableId').val();
            upsertStoreTableRow(tableId, `tr[data-incident-id="${json.incident.incidentID}"]`, buildIncidentRow(json.incident));
            refreshStoreGridIfActive(tableId);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-resolve-incident', function () {
        const $btn = $(this);
        $('#resolveIncidentId').val($btn.data('id'));
        $('#resolveIncidentTableId').val($btn.data('table'));
        $('#resolveIncidentSummary').html(
            '<strong>Item:</strong> ' + escapeHtml($btn.data('item')) +
            ' &nbsp;|&nbsp; <strong>Label:</strong> ' + escapeHtml($btn.data('label')) +
            ' &nbsp;|&nbsp; <strong>Affected:</strong> ' + escapeHtml($btn.data('taken')) +
            '<br><strong>Responsible:</strong> ' + escapeHtml($btn.data('person'))
        );
        $('#resolveIncidentMethod').val('');
        $('#resolvePaymentAmount').val('');
        $('#resolvePaymentAmountWrap').hide();
        $('#resolveIncidentModal').modal('show');
    });

    $('#resolveIncidentMethod').on('change', function () {
        if ($(this).val() === 'cash') $('#resolvePaymentAmountWrap').show();
        else $('#resolvePaymentAmountWrap').hide();
    });

    $('#resolveIncidentForm').on('submit', async function (e) {
        e.preventDefault();
        const id = $('#resolveIncidentId').val();
        const method = $('#resolveIncidentMethod').val();
        if (!method) {
            showAlert('Select resolve method.', 'danger');
            return;
        }
        const payload = { resolve_method: method };
        if (method === 'cash') {
            const amt = parseFloat($('#resolvePaymentAmount').val());
            if (!(amt >= 0)) {
                showAlert('Enter payment amount.', 'danger');
                return;
            }
            payload.payment_amount = amt;
        }
        try {
            const json = await postJson(routes.incidentsResolve(id), payload);
            showAlert(json.message, 'success');
            $('#resolveIncidentModal').modal('hide');
            const tableId = $('#resolveIncidentTableId').val();
            upsertStoreTableRow(tableId, `tr[data-incident-id="${json.incident.incidentID}"]`, buildIncidentRow(json.incident));
            syncStockFromIncidentResponse(json);
            refreshStoreGridIfActive(tableId);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-delete-incident', async function () {
        if (!(await storeConfirm('Delete this record? Stock will be restored.', 'Delete record'))) return;
        try {
            const id = $(this).data('id');
            const tableId = $(this).data('table');
            const json = await postJson(routes.incidentsDelete(id), {});
            showAlert(json.message, 'success');
            removeStoreTableRow(tableId, `tr[data-incident-id="${id}"]`);
            syncStockFromIncidentResponse(json);
            refreshStoreGridIfActive(tableId);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    function renumberTable(selector) {
        const tableId = selector.replace('#', '');
        if ($.fn.DataTable.isDataTable('#' + tableId)) {
            $('#' + tableId).DataTable().draw(false);
        } else {
            document.querySelectorAll(`${selector} tbody tr`).forEach((row, i) => {
                const cell = row.querySelector('td:first-child');
                if (cell && !(row.id && row.id.indexOf('no') === 0)) cell.textContent = i + 1;
            });
        }
        refreshStoreGridIfActive(tableId);
    }

    // DataTables
    const storeDataTableIds = [
        'itemsTable',
        'stockTable',
        'requisitionsTable',
        'damagedItemsTable',
        'lostItemsTable',
        'categoriesTable',
        'measurementTypesTable',
        'packagesTable',
    ];

    const storeDtEmptyMessages = {
        itemsTable: 'No store items yet. Click Add Item to register one.',
        stockTable: 'No stock recorded yet. Click Add Item in Stock to receive items into the store.',
        requisitionsTable: 'No items issued yet. Click Issue Item to give items to a requester.',
        damagedItemsTable: 'No damaged items recorded yet. Click Add Damaged Item to record one.',
        lostItemsTable: 'No lost items recorded yet. Click Add Lost Item to record one.',
        inventoryReportTable: 'No available stock found for the selected filters.',
        requisitionReportTable: 'No requisitions found for the selected filters.',
        specificReqReportTable: 'No issues in this period.',
        specificIncidentReportTable: 'No damaged or lost records in this period.',
        categoriesTable: 'No categories configured. Add your first category.',
        measurementTypesTable: 'No measurement types configured.',
        packagesTable: 'No packages yet. Click Add Packages to add one or more.',
    };

    function storeDataTableOptions(tableId) {
        const $table = $('#' + tableId);
        const actionCol = Math.max(0, $table.find('thead tr').last().find('th').length - 1);
        const isReport = tableId === 'inventoryReportTable';
        const isRequisitions = tableId === 'requisitionsTable';

        return {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            order: isRequisitions ? [] : (isReport ? [[1, 'asc']] : [[1, 'asc']]),
            ordering: !isRequisitions,
            autoWidth: false,
            deferRender: true,
            stateSave: false,
            columnDefs: [
                { targets: 0, orderable: false, searchable: false },
                { targets: actionCol, orderable: !isReport ? false : true, searchable: !isReport ? false : true },
            ],
            language: {
                emptyTable: storeDtEmptyMessages[tableId] || 'No data available',
                zeroRecords: 'No matching records found',
                search: 'Search:',
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_ to _END_ of _TOTAL_',
                infoEmpty: 'Showing 0 entries',
                infoFiltered: '(filtered from _MAX_ total)',
                paginate: { previous: 'Prev', next: 'Next' },
            },
            drawCallback: function () {
                const api = this.api();
                const start = api.page.info().start;
                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = start + i + 1;
                });
            },
        };
    }

    function initStoreDataTable(tableId) {
        const $table = $('#' + tableId);
        if (!$table.length || !$.fn.DataTable) return;
        if ($.fn.DataTable.isDataTable($table)) return;

        $table.find('tbody tr[id^="no"]').remove();
        $table.DataTable(storeDataTableOptions(tableId));
    }

    function initAllStoreDataTables() {
        storeDataTableIds.forEach(initStoreDataTable);
    }

    function adjustStoreDataTable(tableId) {
        if (!tableId || !$.fn.DataTable.isDataTable('#' + tableId)) return;
        setTimeout(function () {
            try {
                $('#' + tableId).DataTable().columns.adjust();
            } catch (e) {}
        }, 50);
    }

    function addStoreTableRow(tableId, rowEl) {
        const $table = $('#' + tableId);
        if (tableId === 'requisitionsTable') {
            const $tbody = $table.find('tbody');
            const $lastReq = $tbody.find('tr[data-requisition-id]').last();
            if ($lastReq.length) {
                $lastReq.after(rowEl);
            } else {
                const $lastRequest = $tbody.find('tr.store-item-request-row').last();
                if ($lastRequest.length) {
                    $lastRequest.after(rowEl);
                } else {
                    $tbody.append(rowEl);
                }
            }
            if ($.fn.DataTable.isDataTable($table)) {
                $table.DataTable().rows().invalidate();
                $table.DataTable().draw(false);
            }
            return;
        }
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().row.add(rowEl).draw(false);
        } else {
            $table.find('tbody').append(rowEl);
        }
    }

    function removeStoreTableRow(tableId, rowSelector) {
        const $table = $('#' + tableId);
        const $row = $(rowSelector);
        if (!$row.length) return;
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().row($row).remove().draw(false);
        } else {
            $row.remove();
        }
        refreshStoreGridIfActive(tableId);
    }

    function upsertStoreTableRow(tableId, rowSelector, rowEl) {
        removeStoreTableRow(tableId, rowSelector);
        addStoreTableRow(tableId, rowEl);
        refreshStoreGridIfActive(tableId);
    }

    function actionsDropdownHtml(itemsHtml) {
        return `<td class="store-actions-cell">
            <div class="dropdown store-action-dropdown">
                <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">${itemsHtml}</div>
            </div>
        </td>`;
    }

    function buildItemRow(item) {
        const viewMore = {
            title: item.item_name,
            fields: [
                { label: 'Category', value: item.category_name || '—' },
                { label: 'Measurement Type', value: item.measurement_type || '—' },
                { label: 'Units of Measure', value: item.unit_names || [], type: 'tags' },
                { label: 'Has Packages', value: item.has_packages ? 'Yes' : 'No' },
                { label: 'Package Details', value: item.package_lines || [], type: 'tags' },
            ],
        };
        const row = document.createElement('tr');
        row.dataset.itemId = item.itemID;
        row.dataset.viewMore = JSON.stringify(viewMore);
        row.innerHTML = `
            <td></td>
            <td class="item-name font-weight-bold">${escapeHtml(item.item_name)}</td>
            <td class="item-category">${escapeHtml(item.category_name || '-')}</td>
            <td class="item-type">${escapeHtml(item.measurement_type || '-')}</td>
            ${actionsDropdownHtml(`
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                <button type="button" class="dropdown-item btn-edit-item"
                    data-id="${item.itemID}"
                    data-name="${escapeHtml(item.item_name)}"
                    data-category="${item.categoryID}"
                    data-type="${item.measurement_typeID}"
                    data-units="${(item.unitIDs || []).join(',')}"
                    data-has-packages="${item.has_packages ? '1' : '0'}"><i class="fa fa-edit text-primary"></i> Edit</button>
                <button type="button" class="dropdown-item text-danger btn-delete-item" data-id="${item.itemID}"><i class="fa fa-trash"></i> Delete</button>
            `)}
        `;
        return row;
    }

    function syncStoreItemsList(item) {
        const listItem = item.list_item || item;
        const idx = storeItemsList.findIndex(i => String(i.itemID) === String(listItem.itemID));
        if (idx >= 0) storeItemsList[idx] = listItem;
        else storeItemsList.push(listItem);
        refreshStockItemSelectOptions();
    }

    function removeFromStoreItemsList(itemID) {
        storeItemsList = storeItemsList.filter(i => String(i.itemID) !== String(itemID));
        refreshStockItemSelectOptions();
    }

    function refreshStockItemSelectOptions() {
        const current = $('#stockItemSelect').val();
        const $sel = $('#stockItemSelect');
        $sel.find('option:not(:first)').remove();
        storeItemsList.slice().sort((a, b) => a.item_name.localeCompare(b.item_name)).forEach(item => {
            $sel.append(new Option(item.item_name, item.itemID, false, String(item.itemID) === String(current)));
        });
        if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger('change.select2');
    }

    function buildStockRow(entry) {
        const inStockLabels = entry.in_stock_labels || [];
        const issuedLabels = entry.issued_labels || [];
        const viewMore = {
            title: (entry.item_name || 'Stock') + ' — Stock Details',
            layout: 'widgets',
            sections: [
                {
                    title: 'Stock Summary',
                    widgets: [
                        { label: 'Item', value: entry.item_name || '—' },
                        { label: 'Store Method', value: entry.store_method === 'package' ? 'Package' : 'Unit Measure' },
                        { label: 'Package / Unit', value: entry.pkg_unit_label || '—' },
                        { label: 'Available Labels', value: String(entry.available_qty), danger: entry.available_qty === 0 },
                        entry.remaining_items != null ? { label: 'Items Remaining', value: String(entry.remaining_items) } : null,
                        { label: 'Labels Touched', value: String(entry.issued_qty) },
                        { label: 'Total Labels Received', value: String(entry.quantity) },
                        { label: 'Requisition Status', value: entry.requisition_status === 'can_return' ? 'Can Return' : "Can't Return" },
                        { label: 'Date Added', value: entry.created_at || '—' },
                    ].filter(Boolean),
                },
                {
                    title: 'Labels',
                    widgets: [
                        inStockLabels.length
                            ? { label: 'In Stock Labels (' + inStockLabels.length + ')', value: inStockLabels, type: 'tags', wide: true }
                            : { label: 'In Stock Labels', value: 'None', wide: true },
                        issuedLabels.length
                            ? { label: 'Issued Labels (' + issuedLabels.length + ')', value: issuedLabels, type: 'tags', wide: true }
                            : null,
                    ].filter(Boolean),
                },
            ],
        };
        const returnBadge = entry.requisition_status === 'can_return'
            ? '<span class="store-badge-pill store-badge-return-yes">Can Return</span>'
            : '<span class="store-badge-pill store-badge-return-no">Can\'t Return</span>';
        const row = document.createElement('tr');
        row.dataset.stockEntryId = entry.stock_entryID;
        row.dataset.viewMore = JSON.stringify(viewMore);
        row.innerHTML = `
            <td></td>
            <td class="font-weight-bold">${escapeHtml(entry.item_name || '-')}</td>
            <td class="small">${escapeHtml(entry.pkg_unit_label || '-')}</td>
            <td>
                <span class="font-weight-bold ${entry.available_qty === 0 ? 'text-danger' : ''}">${entry.available_qty}</span>
                ${entry.remaining_items != null ? `<small class="text-muted d-block">${entry.remaining_items} item(s) left</small>` : ''}
                ${entry.issued_qty > 0 ? `<small class="text-muted d-block">${entry.issued_qty} label(s) touched</small>` : ''}
            </td>
            <td>${returnBadge}</td>
            ${actionsDropdownHtml(`
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                <button type="button" class="dropdown-item text-danger btn-delete-stock" data-id="${entry.stock_entryID}"><i class="fa fa-trash"></i> Delete</button>
            `)}
        `;
        return row;
    }

    function syncAvailableStockFromEntry(entry) {
        const entryUnitIds = (entry.stock_units || []).map(u => String(u.stock_unitID));
        availableStockList = availableStockList.filter(u => !entryUnitIds.includes(String(u.stock_unitID)));
        (entry.stock_units || []).filter(u => u.stock_status === 'in_stock' && (parseFloat(u.remaining_items) || 0) > 0).forEach(u => {
            availableStockList.push({
                stock_unitID: u.stock_unitID,
                itemID: u.itemID,
                item_name: u.item_name,
                categoryID: u.categoryID ?? null,
                category_name: u.category_name || '',
                label: u.label,
                store_method: u.store_method,
                packageID: u.packageID,
                package_specID: u.package_specID,
                package_unit_display: u.package_unit_display,
                requisition_status: u.requisition_status,
                initial_items: u.initial_items,
                remaining_items: u.remaining_items,
                leaf_unit_name: u.leaf_unit_name || 'item',
                take_structure: u.take_structure || null,
                take_options: u.take_options || [],
            });
        });
    }

    function returnConditionLabel(condition) {
        const c = condition === 'bad' ? 'damaged' : condition;
        if (c === 'damaged') return 'Damaged';
        if (c === 'lost') return 'Lost';
        return 'Good';
    }

    function buildRequisitionRow(req) {
        const viewMore = {
            title: (req.item_name || 'Item') + ' — Requisition',
            layout: 'widgets',
            sections: [
                {
                    title: 'Recipient & Issue',
                    widgets: [
                        { label: 'Recipient', value: req.recipient_name },
                        { label: 'Type', value: req.recipient_type_label },
                        req.class_name ? { label: 'Class', value: req.class_name } : null,
                        { label: 'Item', value: req.item_name },
                        { label: 'Label', value: req.label },
                        { label: 'Package / Unit', value: req.package_unit_display },
                        { label: 'Taken', value: req.take_display || '—' },
                        { label: 'Quantity Taken', value: String(req.quantity_taken || 1) },
                        { label: 'Return Policy', value: req.requisition_status === 'can_return' ? 'Can Return' : "Can't Return" },
                        { label: 'Issued By', value: req.issued_by || '—' },
                        { label: 'Issued At', value: req.issued_at || '—' },
                        { label: 'Notes', value: req.notes || '—' },
                    ].filter(Boolean),
                },
                {
                    title: 'Return Details',
                    widgets: [
                        req.requisition_status === 'can_return' ? { label: 'Expected Return', value: req.expected_return_date, danger: req.is_overdue } : null,
                        { label: 'Return Status', value: req.return_status_text },
                        req.status === 'returned' ? { label: 'Quantity Returned', value: String(req.quantity_returned || req.quantity_taken || 1) } : null,
                        req.status === 'returned' ? { label: 'Returned On', value: req.returned_at } : null,
                        req.status === 'returned' && req.return_description ? { label: 'Return Description', value: req.return_description } : null,
                        req.status === 'returned' ? { label: 'Return Status', value: returnConditionLabel(req.return_condition) } : null,
                        ['damaged', 'lost', 'bad'].includes(req.return_condition) ? { label: 'Reason', value: req.bad_description || '—', danger: true } : null,
                        ['damaged', 'lost', 'bad'].includes(req.return_condition) ? { label: 'Responsible Person', value: req.recipient_name + ' (' + req.recipient_type_label + (req.class_name ? ' · ' + req.class_name : '') + ')', danger: true } : null,
                    ].filter(Boolean),
                },
            ],
        };
        const returnAction = req.can_return_action
            ? `<button type="button" class="dropdown-item btn-return-requisition"
                data-id="${req.requisitionID}"
                data-recipient="${escapeHtml(req.recipient_name)}${req.class_name ? ' · ' + escapeHtml(req.class_name) : ''}"
                data-item="${escapeHtml(req.item_name)}"
                data-label="${escapeHtml(req.label)}"
                data-taken="${escapeHtml(req.take_display || String(req.quantity_taken || 1))}">
                <i class="fa fa-undo text-success"></i> Return Item
            </button>`
            : '';
        const row = document.createElement('tr');
        row.dataset.requisitionId = req.requisitionID;
        row.dataset.returnPolicy = req.requisition_status;
        row.dataset.viewMore = JSON.stringify(viewMore);
        row.innerHTML = `
            <td></td>
            <td>
                <span class="font-weight-bold">${escapeHtml(req.recipient_name)}</span>
                <br><small class="text-muted">${escapeHtml(req.recipient_type_label)}</small>
                ${req.class_name ? `<br><small class="text-muted">Class: ${escapeHtml(req.class_name)}</small>` : ''}
            </td>
            <td>
                ${escapeHtml(req.item_name)}
                <br><small class="text-muted">Label: ${escapeHtml(req.label)}</small>
                ${req.take_display ? `<br><small class="text-primary-custom">${escapeHtml(req.take_display)}</small>` : ''}
            </td>
            <td class="small">${escapeHtml(req.package_unit_display || '-')}</td>
            <td>${req.return_status_html}</td>
            <td>${req.expected_return_html}</td>
            ${actionsDropdownHtml(`
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                ${returnAction}
            `)}
        `;
        return row;
    }

    function incidentTableId(inc) {
        return inc.incident_type === 'lost' ? 'lostItemsTable' : 'damagedItemsTable';
    }

    function incidentTypeLabel(inc) {
        return inc.incident_type === 'lost' ? 'Lost' : 'Damaged';
    }

    function buildIncidentRow(inc) {
        const typeLabel = incidentTypeLabel(inc);
        const byLabel = inc.incident_type === 'lost' ? 'Lost By' : 'Damaged By';
        const viewMore = {
            title: (inc.item_name || 'Item') + ' — ' + typeLabel,
            layout: 'widgets',
            sections: [
                {
                    title: typeLabel + ' Details',
                    widgets: [
                        { label: byLabel, value: inc.responsible_name },
                        { label: 'Type', value: inc.responsible_type_label },
                        inc.class_name ? { label: 'Class', value: inc.class_name } : null,
                        { label: 'Item', value: inc.item_name },
                        { label: 'Label', value: inc.label },
                        { label: 'Package / Unit', value: inc.package_unit_display },
                        { label: 'Affected', value: inc.take_display || '—' },
                        { label: 'Quantity', value: String(inc.quantity_affected || 1) },
                        { label: 'Reason', value: inc.reason || '—' },
                        { label: 'Payment', value: inc.payment_status === 'paid' ? 'Paid' : 'Pending' },
                        inc.payment_amount !== null ? { label: 'Amount', value: String(inc.payment_amount) } : null,
                        { label: 'Resolve Status', value: inc.resolve_status === 'resolved' ? 'Resolved' : 'Open' },
                        inc.resolve_method ? { label: 'Resolve Method', value: inc.resolve_method_label } : null,
                        { label: 'Recorded By', value: inc.recorded_by || '—' },
                        { label: 'Recorded At', value: inc.recorded_at || '—' },
                        inc.resolved_at && inc.resolved_at !== '—' ? { label: 'Resolved At', value: inc.resolved_at } : null,
                        inc.notes ? { label: 'Notes', value: inc.notes } : null,
                    ].filter(Boolean),
                },
            ],
        };

        const editBtn = inc.can_edit
            ? `<button type="button" class="dropdown-item btn-edit-incident"
                data-id="${inc.incidentID}"
                data-table="${incidentTableId(inc)}"
                data-type="${inc.responsible_type}"
                data-rid="${inc.responsible_id || ''}"
                data-name="${escapeHtml(inc.responsible_name)}"
                data-reason="${escapeHtml(inc.reason)}"
                data-notes="${escapeHtml(inc.notes || '')}">
                <i class="fa fa-edit text-primary"></i> Edit
            </button>`
            : '';
        const resolveBtn = inc.can_resolve
            ? `<button type="button" class="dropdown-item btn-resolve-incident"
                data-id="${inc.incidentID}"
                data-table="${incidentTableId(inc)}"
                data-item="${escapeHtml(inc.item_name)}"
                data-label="${escapeHtml(inc.label)}"
                data-taken="${escapeHtml(inc.take_display || String(inc.quantity_affected || 1))}"
                data-person="${escapeHtml(inc.responsible_name)}">
                <i class="fa fa-check-circle text-success"></i> Resolve
            </button>`
            : '';
        const deleteBtn = inc.can_delete
            ? `<button type="button" class="dropdown-item text-danger btn-delete-incident"
                data-id="${inc.incidentID}"
                data-table="${incidentTableId(inc)}">
                <i class="fa fa-trash"></i> Delete
            </button>`
            : '';

        const row = document.createElement('tr');
        row.dataset.incidentId = inc.incidentID;
        row.dataset.incidentType = inc.incident_type;
        row.dataset.viewMore = JSON.stringify(viewMore);
        row.innerHTML = `
            <td></td>
            <td>
                ${escapeHtml(inc.item_name)}
                <br><small class="text-muted">Label: ${escapeHtml(inc.label)}</small>
                ${inc.take_display ? `<br><small class="text-primary-custom">${escapeHtml(inc.take_display)}</small>` : ''}
            </td>
            <td>
                <span class="font-weight-bold">${escapeHtml(inc.responsible_name)}</span>
                <br><small class="text-muted">${escapeHtml(inc.responsible_type_label)}</small>
                ${inc.class_name ? `<br><small class="text-muted">Class: ${escapeHtml(inc.class_name)}</small>` : ''}
            </td>
            <td class="small">${escapeHtml(inc.reason || '—')}</td>
            <td>${inc.status_html}</td>
            ${actionsDropdownHtml(`
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                ${editBtn}
                ${resolveBtn}
                ${deleteBtn}
            `)}
        `;
        return row;
    }

    function syncStockFromIncidentResponse(json) {
        if (json.stock_entry) {
            upsertStoreTableRow('stockTable', `tr[data-stock-entry-id="${json.stock_entry.stock_entryID}"]`, buildStockRow(json.stock_entry));
        }
        (json.stock_entries || []).forEach(entry => {
            upsertStoreTableRow('stockTable', `tr[data-stock-entry-id="${entry.stock_entryID}"]`, buildStockRow(entry));
        });
        const units = [];
        if (json.available_stock_unit) units.push(json.available_stock_unit);
        (json.available_stock_units || []).forEach(u => units.push(u));
        (json.depleted_stock_unitIDs || []).forEach(id => {
            availableStockList = availableStockList.filter(u => String(u.stock_unitID) !== String(id));
        });
        units.forEach(u => {
            const idx = availableStockList.findIndex(x => String(x.stock_unitID) === String(u.stock_unitID));
            if (idx >= 0) availableStockList[idx] = u;
            else availableStockList.push(u);
        });
    }

    function buildPackageRow(pkg) {
        const viewMore = {
            title: pkg.package_name,
            fields: [
                { label: 'Package Name', value: pkg.package_name },
                { label: 'Status', value: pkg.status || 'Active' },
            ],
        };
        const row = document.createElement('tr');
        row.dataset.packageId = pkg.packageID;
        row.dataset.viewMore = JSON.stringify(viewMore);
        row.innerHTML = `
            <td></td>
            <td class="pkg-name font-weight-bold">${escapeHtml(pkg.package_name)}</td>
            ${actionsDropdownHtml(`
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                <button type="button" class="dropdown-item btn-edit-package" data-id="${pkg.packageID}" data-name="${escapeHtml(pkg.package_name)}"><i class="fa fa-edit text-primary"></i> Edit</button>
                <button type="button" class="dropdown-item text-danger btn-delete-package" data-id="${pkg.packageID}"><i class="fa fa-trash"></i> Delete</button>
            `)}
        `;
        return row;
    }

    function buildMeasurementTypeRow(type) {
        const units = type.units || [];
        const unitNames = units.map(u => u.unit_name);
        const viewMore = {
            title: type.type_name,
            fields: [
                { label: 'Type Name', value: type.type_name },
                { label: 'Type', value: type.is_system ? 'Default (System)' : 'Custom' },
                { label: 'Units of Measure (' + units.length + ')', value: unitNames, type: 'tags' },
            ],
            units: units,
        };
        const badge = type.is_system
            ? '<span class="store-badge-pill store-badge-unit ml-1" style="font-size:10px;">Default</span>'
            : '<span class="store-badge-pill store-badge-package ml-1" style="font-size:10px;">Custom</span>';
        const row = document.createElement('tr');
        row.className = 'measurement-type-row';
        row.dataset.typeId = type.measurement_typeID;
        row.dataset.typeName = String(type.type_name || '').toLowerCase();
        row.dataset.viewMore = JSON.stringify(viewMore);
        row.innerHTML = `
            <td></td>
            <td>
                <span class="type-name font-weight-bold">${escapeHtml(type.type_name)}</span>
                ${badge}
            </td>
            <td><span class="text-muted units-count">${units.length} unit(s)</span></td>
            ${actionsDropdownHtml(`
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                <button type="button" class="dropdown-item btn-add-unit" data-type-id="${type.measurement_typeID}" data-type-name="${escapeHtml(type.type_name)}"><i class="fa fa-plus text-success"></i> Add Unit</button>
                <button type="button" class="dropdown-item btn-edit-type" data-id="${type.measurement_typeID}" data-name="${escapeHtml(type.type_name)}"><i class="fa fa-edit text-primary"></i> Edit</button>
                <button type="button" class="dropdown-item text-danger btn-delete-type" data-id="${type.measurement_typeID}"><i class="fa fa-trash"></i> Delete</button>
            `)}
        `;
        return row;
    }

    function refreshMeasurementTypeRow(typeID) {
        const type = measurementTypesState.find(t => String(t.measurement_typeID) === String(typeID));
        if (!type) return;
        upsertStoreTableRow('measurementTypesTable', `tr[data-type-id="${typeID}"]`, buildMeasurementTypeRow(type));
    }

    function destroyReportDataTable(tableId) {
        const $table = $('#' + tableId);
        if ($table.length && $.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
        }
    }

    function destroyAllReportDataTables() {
        ['inventoryReportTable', 'requisitionReportTable', 'specificReqReportTable', 'specificIncidentReportTable'].forEach(destroyReportDataTable);
    }

    function initReportDataTable(tableId) {
        const $table = $('#' + tableId);
        if (!$table.length || !$.fn.DataTable) return;
        destroyReportDataTable(tableId);
        if (!$table.find('tbody tr').length) return;
        const opts = storeDataTableOptions(tableId);
        if (tableId === 'inventoryReportTable') {
            opts.columnDefs = [{ targets: 0, orderable: false, searchable: false }];
            opts.order = [[1, 'asc']];
        } else if (tableId === 'requisitionReportTable') {
            opts.order = [[7, 'desc']];
        } else {
            opts.order = [[0, 'asc']];
        }
        $table.DataTable(opts);
    }

    if ($.fn.dataTable) {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'requisitionsTable') return true;
            const filter = $('#requisitionReturnFilter').val() || 'all';
            const row = settings.aoData[dataIndex].nTr;
            if ($(row).attr('data-row-type') === 'item-request') {
                return filter === 'all';
            }
            if (filter === 'all') return true;
            return $(row).attr('data-return-policy') === filter;
        });
    }

    // List / Grid view (desktop=list default, mobile=grid default)
    const storeViewConfigs = {
        itemsTable: { titleCol: 1, fields: [{ col: 2, label: 'Category' }, { col: 3, label: 'Measurement Type' }] },
        stockTable: { titleCol: 1, fields: [{ col: 2, label: 'Package / Unit' }, { col: 3, label: 'Available', html: true }, { col: 4, label: 'Requisition', html: true }] },
        requisitionsTable: { titleCol: 1, titleHtml: true, fields: [{ col: 2, label: 'Item', html: true }, { col: 3, label: 'Package / Unit' }, { col: 4, label: 'Return Status', html: true }, { col: 5, label: 'Expected Return', html: true }] },
        damagedItemsTable: { titleCol: 2, titleHtml: true, fields: [{ col: 1, label: 'Responsible', html: true }, { col: 3, label: 'Reason' }, { col: 4, label: 'Status', html: true }] },
        lostItemsTable: { titleCol: 2, titleHtml: true, fields: [{ col: 1, label: 'Responsible', html: true }, { col: 3, label: 'Reason' }, { col: 4, label: 'Status', html: true }] },
        categoriesTable: { titleCol: 1, fields: [] },
        measurementTypesTable: { titleCol: 1, titleHtml: true, fields: [{ col: 2, label: 'Units' }] },
        packagesTable: { titleCol: 1, fields: [] },
    };

    function storeIsMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function storeDeviceKey() {
        return storeIsMobile() ? 'mobile' : 'desktop';
    }

    function storeDefaultView() {
        return storeIsMobile() ? 'grid' : 'list';
    }

    function storePrefKey(tableId) {
        return 'store_view_' + storeDeviceKey() + '_' + tableId;
    }

    function storeUserPrefKey(tableId) {
        return 'store_view_user_' + storeDeviceKey() + '_' + tableId;
    }

    function storeHasUserViewPref(tableId) {
        try { return localStorage.getItem(storeUserPrefKey(tableId)) === '1'; } catch (e) { return false; }
    }

    function storeGetView(tableId) {
        if (storeHasUserViewPref(tableId)) {
            const saved = localStorage.getItem(storePrefKey(tableId));
            if (saved === 'list' || saved === 'grid') return saved;
        }
        return storeDefaultView();
    }

    function storeSetUserViewPref(tableId, view) {
        try {
            localStorage.setItem(storePrefKey(tableId), view);
            localStorage.setItem(storeUserPrefKey(tableId), '1');
        } catch (e) {}
    }

    function getStoreTableRows($table) {
        const rows = [];
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().rows({ search: 'applied' }).every(function () {
                rows.push($(this.node()));
            });
        } else {
            $table.find('tbody tr').each(function () {
                rows.push($(this));
            });
        }
        return rows;
    }

    function buildStoreGrid(tableId) {
        const config = storeViewConfigs[tableId];
        if (!config) return;
        const $wrapper = $('.store-data-wrapper[data-table-id="' + tableId + '"]');
        const $grid = $wrapper.find('.store-grid-view').empty();
        const $table = $('#' + tableId);
        let cardCount = 0;

        getStoreTableRows($table).forEach(function ($tr) {
            const rowId = $tr.attr('id') || '';
            if (rowId.indexOf('no') === 0) return;

            const $cells = $tr.find('td');
            if (!$cells.length) return;

            const titleContent = config.titleHtml ? $cells.eq(config.titleCol).html() : escapeHtml($cells.eq(config.titleCol).text().trim());
            let metaHtml = '';
            (config.fields || []).forEach(f => {
                const val = f.html ? $cells.eq(f.col).html() : escapeHtml($cells.eq(f.col).text().trim());
                metaHtml += '<div class="store-grid-meta-row"><span class="store-grid-meta-label">' + escapeHtml(f.label) + '</span><span class="store-grid-meta-value">' + val + '</span></div>';
            });
            const actionsHtml = $tr.find('.store-actions-cell').html() || '';
            const viewMore = $tr.attr('data-view-more');
            const $card = $('<div class="store-grid-card"></div>');
            if (viewMore) {
                try { $card.data('viewMore', JSON.parse(viewMore)); } catch (e) {}
            }
            $card.append(
                '<div class="store-grid-card-head"><span class="store-grid-num">#' + escapeHtml($cells.eq(0).text().trim()) + '</span><div class="store-grid-actions">' + actionsHtml + '</div></div>' +
                '<div class="store-grid-card-title">' + titleContent + '</div>' +
                (metaHtml ? '<div class="store-grid-card-body">' + metaHtml + '</div>' : '')
            );
            $grid.append($('<div class="col-12 col-sm-6 col-xl-4 mb-3"></div>').append($card));
            cardCount++;
        });

        if (cardCount === 0) {
            const emptyMsg = (storeDtEmptyMessages[tableId] || 'No data available.');
            $grid.append('<div class="col-12"><div class="store-grid-empty">' + escapeHtml(emptyMsg) + '</div></div>');
        }
    }

    function setStoreView(tableId, view, userChoice) {
        const $wrapper = $('.store-data-wrapper[data-table-id="' + tableId + '"]');
        if (!$wrapper.length) return;

        if (view === 'grid') {
            buildStoreGrid(tableId);
        }

        $wrapper.attr('data-active-view', view);
        $('.store-view-toggle[data-table="' + tableId + '"] button').removeClass('active');
        $('.store-view-toggle[data-table="' + tableId + '"] button[data-view="' + view + '"]').addClass('active');
        if (userChoice) storeSetUserViewPref(tableId, view);
        if (view === 'list') {
            adjustStoreDataTable(tableId);
        }
    }

    function initStoreDataViews() {
        Object.keys(storeViewConfigs).forEach(tableId => {
            setStoreView(tableId, storeGetView(tableId), false);
        });
        const $activeWrapper = $('.store-section.active .store-data-wrapper').first();
        if ($activeWrapper.length && $activeWrapper.attr('data-active-view') === 'grid') {
            buildStoreGrid($activeWrapper.attr('data-table-id'));
        }
    }

    function refreshStoreGridIfActive(tableId) {
        if ($('.store-data-wrapper[data-table-id="' + tableId + '"]').attr('data-active-view') === 'grid') {
            buildStoreGrid(tableId);
        }
    }

    $(document).on('click', '.store-view-toggle button', function () {
        setStoreView($(this).closest('.store-view-toggle').data('table'), $(this).data('view'), true);
    });

    $(window).on('resize', function () {
        clearTimeout(window._storeResizeTimer);
        window._storeResizeTimer = setTimeout(function () {
            Object.keys(storeViewConfigs).forEach(tableId => {
                setStoreView(tableId, storeGetView(tableId), false);
            });
            const $activeWrapper = $('.store-section.active .store-data-wrapper').first();
            if ($activeWrapper.length && $activeWrapper.attr('data-active-view') === 'grid') {
                buildStoreGrid($activeWrapper.attr('data-table-id'));
            }
        }, 200);
    });

    // Store Reports
    function escapeHtmlReport(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    let reportWizardReady = false;

    function hideAllReportResults() {
        $('.report-results-panel').hide();
        $('#reportEmpty').hide();
    }

    function showReportLoading(show) {
        $('#reportLoading').toggle(!!show);
    }

    function getReportCategoryParams() {
        const params = new URLSearchParams();
        const cat = $('#reportCategoryFilter').val();
        if (cat) params.set('categoryID', cat);
        return params;
    }

    function appendReportPeriodParams(params) {
        params.set('period_type', $('#reportPeriodType').val() || 'current_month');
        const pt = params.get('period_type');
        if (pt === 'month' || pt === 'year') {
            params.set('year', $('#reportYear').val() || new Date().getFullYear());
        }
        if (pt === 'month') {
            params.set('month', $('#reportMonth').val() || (new Date().getMonth() + 1));
        }
        if (pt === 'range') {
            params.set('date_from', $('#reportDateFrom').val() || '');
            params.set('date_to', $('#reportDateTo').val() || '');
        }
        return params;
    }

    function updateReportPeriodFields() {
        const pt = $('#reportPeriodType').val();
        $('#reportMonthField').toggle(pt === 'month');
        $('#reportYearField').toggle(pt === 'month' || pt === 'year');
        $('#reportDateFromField, #reportDateToField').toggle(pt === 'range');
    }

    function populateReportItemSelect() {
        const cat = $('#reportCategoryFilter').val();
        const $sel = $('#reportItemSelect');
        const prev = $sel.val();
        $sel.find('option:not(:first)').remove();
        reportItemsList
            .filter(it => !cat || String(it.categoryID) === String(cat))
            .sort((a, b) => a.item_name.localeCompare(b.item_name))
            .forEach(it => {
                $sel.append(new Option(it.item_name, it.itemID, false, String(it.itemID) === String(prev)));
            });
    }

    function updateReportFilterPanels() {
        const type = $('#reportTypeSelect').val();
        $('#allInventoryReportPanel').toggle(type === 'all_inventory');
        $('#requisitionReportPanel').toggle(type === 'requisition');
        $('#specificItemReportPanel').toggle(type === 'specific_item');
        $('#reportPeriodPanel').toggle(type === 'requisition' || type === 'specific_item');
        const reqFilter = $('#reqReportReturnFilter').val();
        $('#reqReportReturnStatusWrap').toggle(type === 'requisition' && reqFilter !== 'cant_return');
    }

    function initReportWizard() {
        $('#reportTypeSection').show();
        $('#reportGenerateRow').show();
        updateReportPeriodFields();
        populateReportItemSelect();
        updateReportFilterPanels();
        reportWizardReady = true;
    }

    function renderInventoryReport(data) {
        const summary = data.summary || {};
        const items = data.items || [];

        destroyReportDataTable('inventoryReportTable');

        $('#inventoryReportMeta').text(
            'Category: ' + (summary.filter_label || 'All Categories') +
            ' · Stock snapshot as of ' + (summary.as_of_label || '—') +
            ' · Issued in ' + (summary.month_label || 'this month') + ': ' + (summary.issued_this_month || 0)
        );

        $('#inventoryReportSummary').html(
            '<div class="store-report-stat"><div class="stat-value">' + (summary.total_in_stock || 0) + '</div><div class="stat-label">Labels In Stock</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.issued_this_month || 0) + '</div><div class="stat-label">Issued This Month</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.available_in_stock || 0) + '</div><div class="stat-label">Available In Stock</div></div>'
        );

        const $tbody = $('#inventoryReportTableBody').empty();
        if (!items.length) {
            hideAllReportResults();
            $('#reportEmptyText').text('No available stock found for the selected filters.');
            $('#reportEmpty').show();
            return;
        }

        items.forEach(function (item) {
            (item.rows || []).forEach(function (row) {
                let html = '<tr><td class="text-center"></td>';
                html += '<td class="item-name-cell">' + escapeHtmlReport(item.item_name) + '</td>';
                if (row.row_type === 'unit') {
                    html += '<td class="text-center">' + escapeHtmlReport(row.unit_name) + '</td>';
                    html += '<td class="text-center font-weight-bold">' + escapeHtmlReport(row.quantity) + '</td>';
                    html += '<td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>';
                } else {
                    html += '<td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>';
                    html += '<td class="text-center">' + escapeHtmlReport(row.package_display) + '</td>';
                    html += '<td class="text-center font-weight-bold">' + escapeHtmlReport(row.package_quantity) + '</td>';
                }
                html += '</tr>';
                $tbody.append(html);
            });
        });

        hideAllReportResults();
        $('#inventoryReportResults').show();
        initReportDataTable('inventoryReportTable');
    }

    function renderRequisitionReport(data) {
        const summary = data.summary || {};
        const rows = data.rows || [];

        destroyReportDataTable('requisitionReportTable');

        $('#requisitionReportMeta').text(
            'Category: ' + (summary.filter_label || 'All Categories') +
            ' · Period: ' + (summary.period_label || '—') +
            ' · Unique recipients: ' + (summary.unique_recipients || 0)
        );

        $('#requisitionReportSummary').html(
            '<div class="store-report-stat"><div class="stat-value">' + (summary.total_issues || 0) + '</div><div class="stat-label">Total Issues</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.can_return_issues || 0) + '</div><div class="stat-label">Can Return</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.not_returned || 0) + '</div><div class="stat-label">Not Returned</div></div>' +
            '<div class="store-report-stat"><div class="stat-value text-danger">' + (summary.overdue || 0) + '</div><div class="stat-label">Overdue</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.returned_good || 0) + '</div><div class="stat-label">Returned Good</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.returned_damaged || 0) + '</div><div class="stat-label">Returned Damaged</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.returned_lost || 0) + '</div><div class="stat-label">Returned Lost</div></div>'
        );

        const $tbody = $('#requisitionReportTableBody').empty();
        if (!rows.length) {
            hideAllReportResults();
            $('#reportEmptyText').text('No requisitions found for the selected category and period.');
            $('#reportEmpty').show();
            return;
        }

        rows.forEach(function (row) {
            const policy = row.requisition_status === 'can_return'
                ? '<span class="store-badge-pill store-badge-return-yes">Can Return</span>'
                : '<span class="store-badge-pill store-badge-return-no">Can\'t Return</span>';
            $tbody.append(`
                <tr>
                    <td></td>
                    <td>
                        <span class="font-weight-bold">${escapeHtmlReport(row.recipient_name)}</span>
                        <br><small class="text-muted">${escapeHtmlReport(row.recipient_type_label)}</small>
                        ${row.class_name ? '<br><small class="text-muted">Class: ' + escapeHtmlReport(row.class_name) + '</small>' : ''}
                    </td>
                    <td>
                        ${escapeHtmlReport(row.item_name)}
                        <br><small class="text-muted">${escapeHtmlReport(row.category_name || '')}</small>
                    </td>
                    <td>
                        <small>Label: ${escapeHtmlReport(row.label)}</small>
                        ${row.take_display ? '<br><small class="text-primary-custom">' + escapeHtmlReport(row.take_display) + '</small>' : ''}
                    </td>
                    <td>${policy}</td>
                    <td>${row.return_status_html || '—'}</td>
                    <td>${row.expected_return_html || '—'}</td>
                    <td class="small">${escapeHtmlReport(row.issued_at || '—')}</td>
                    <td class="small">${escapeHtmlReport(row.returned_at || '—')}</td>
                </tr>
            `);
        });

        hideAllReportResults();
        $('#requisitionReportResults').show();
        initReportDataTable('requisitionReportTable');
    }

    function renderSpecificItemReport(data) {
        const item = data.item || {};
        const summary = data.summary || {};
        const reqs = data.requisitions || [];
        const incidents = data.incidents || [];
        const stock = data.stock_breakdown || [];

        destroyReportDataTable('specificReqReportTable');
        destroyReportDataTable('specificIncidentReportTable');

        $('#specificItemReportHeader').html(
            '<h5 class="font-weight-bold text-primary-custom mb-1">' + escapeHtmlReport(item.item_name) + '</h5>' +
            '<div class="small text-muted">Category: ' + escapeHtmlReport(item.category_name) +
            ' · Measurement: ' + escapeHtmlReport(item.measurement_type) +
            ' · Period: ' + escapeHtmlReport(summary.period_label) + '</div>'
        );

        $('#specificItemReportSummary').html(
            '<div class="store-report-stat"><div class="stat-value">' + (summary.labels_in_stock || 0) + '</div><div class="stat-label">Labels In Stock</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + escapeHtmlReport(summary.total_remaining_items || '0') + '</div><div class="stat-label">Items Remaining</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.issues_in_period || 0) + '</div><div class="stat-label">Issues in Period</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.active_issues || 0) + '</div><div class="stat-label">Still Out</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.damaged_incidents || 0) + '</div><div class="stat-label">Damaged</div></div>' +
            '<div class="store-report-stat"><div class="stat-value">' + (summary.lost_incidents || 0) + '</div><div class="stat-label">Lost</div></div>'
        );

        const $stockBody = $('#specificStockTableBody').empty();
        if (!stock.length) {
            $stockBody.append('<tr><td colspan="3" class="text-center text-muted">No stock currently available for this item.</td></tr>');
        } else {
            stock.forEach(s => {
                $stockBody.append('<tr><td>' + escapeHtmlReport(s.display) + '</td><td class="text-center font-weight-bold">' + s.labels + '</td><td class="text-center">' + escapeHtmlReport(s.remaining_items) + '</td></tr>');
            });
        }

        const $reqBody = $('#specificReqReportTableBody').empty();
        if (!reqs.length) {
            $reqBody.append('<tr><td colspan="6" class="text-center text-muted">No issues in this period.</td></tr>');
        } else {
            reqs.forEach(r => {
                $reqBody.append(`
                    <tr>
                        <td></td>
                        <td><strong>${escapeHtmlReport(r.recipient_name)}</strong><br><small class="text-muted">${escapeHtmlReport(r.recipient_type_label)}</small></td>
                        <td><small>${escapeHtmlReport(r.label)}</small>${r.take_display ? '<br><small class="text-primary-custom">' + escapeHtmlReport(r.take_display) + '</small>' : ''}</td>
                        <td>${r.return_status_html || '—'}</td>
                        <td class="small">${escapeHtmlReport(r.issued_at || '—')}</td>
                        <td class="small">${escapeHtmlReport(r.returned_at || '—')}</td>
                    </tr>
                `);
            });
        }

        const $incBody = $('#specificIncidentReportTableBody').empty();
        if (!incidents.length) {
            $incBody.append('<tr><td colspan="7" class="text-center text-muted">No damaged or lost records in this period.</td></tr>');
        } else {
            incidents.forEach(inc => {
                $incBody.append(`
                    <tr>
                        <td></td>
                        <td>${inc.incident_type === 'lost' ? 'Lost' : 'Damaged'}</td>
                        <td><strong>${escapeHtmlReport(inc.responsible_name)}</strong></td>
                        <td><small>${escapeHtmlReport(inc.label)}</small>${inc.take_display ? '<br><small>' + escapeHtmlReport(inc.take_display) + '</small>' : ''}</td>
                        <td class="small">${escapeHtmlReport(inc.reason || '—')}</td>
                        <td>${inc.status_html || '—'}</td>
                        <td class="small">${escapeHtmlReport(inc.recorded_at || '—')}</td>
                    </tr>
                `);
            });
        }

        hideAllReportResults();
        $('#specificItemReportResults').show();
        if (reqs.length) initReportDataTable('specificReqReportTable');
        if (incidents.length) initReportDataTable('specificIncidentReportTable');
    }

    async function loadAllInventoryReport() {
        destroyAllReportDataTables();
        hideAllReportResults();
        showReportLoading(true);

        const params = getReportCategoryParams();
        params.set('return_filter', $('#reportReturnFilter').val() || 'all');
        params.set('as_of_date', $('#reportAsOfDate').val() || '');

        try {
            const json = await getJson(routes.allInventoryReport + '?' + params.toString());
            renderInventoryReport(json);
        } catch (err) {
            showAlert(err.message || 'Failed to load report', 'danger');
            $('#reportEmptyText').text(err.message || 'Failed to load report');
            $('#reportEmpty').show();
        } finally {
            showReportLoading(false);
        }
    }

    async function loadRequisitionReport() {
        destroyAllReportDataTables();
        hideAllReportResults();
        showReportLoading(true);

        const params = getReportCategoryParams();
        appendReportPeriodParams(params);
        params.set('return_filter', $('#reqReportReturnFilter').val() || 'all');
        params.set('return_status_filter', $('#reqReportReturnStatus').val() || 'all');

        try {
            const json = await getJson(routes.requisitionReport + '?' + params.toString());
            renderRequisitionReport(json);
        } catch (err) {
            showAlert(err.message || 'Failed to load report', 'danger');
            $('#reportEmptyText').text(err.message || 'Failed to load report');
            $('#reportEmpty').show();
        } finally {
            showReportLoading(false);
        }
    }

    async function loadSpecificItemReport() {
        const itemId = $('#reportItemSelect').val();
        if (!itemId) {
            showAlert('Select an item for this report.', 'warning');
            return;
        }

        destroyAllReportDataTables();
        hideAllReportResults();
        showReportLoading(true);

        const params = getReportCategoryParams();
        appendReportPeriodParams(params);
        params.set('itemID', itemId);

        try {
            const json = await getJson(routes.specificItemReport + '?' + params.toString());
            renderSpecificItemReport(json);
        } catch (err) {
            showAlert(err.message || 'Failed to load report', 'danger');
            $('#reportEmptyText').text(err.message || 'Failed to load report');
            $('#reportEmpty').show();
        } finally {
            showReportLoading(false);
        }
    }

    function loadActiveReport() {
        if (!reportWizardReady) initReportWizard();
        const type = $('#reportTypeSelect').val();
        if (type === 'requisition') loadRequisitionReport();
        else if (type === 'specific_item') loadSpecificItemReport();
        else loadAllInventoryReport();
    }

    $('#reportCategoryFilter').on('change', function () {
        initReportWizard();
        populateReportItemSelect();
        hideAllReportResults();
    });

    $('#reportTypeSelect').on('change', function () {
        updateReportFilterPanels();
        hideAllReportResults();
    });

    $('#reportPeriodType').on('change', updateReportPeriodFields);
    $('#reqReportReturnFilter').on('change', updateReportFilterPanels);

    $('#btnGenerateReport').on('click', loadActiveReport);

    function buildStorePdfParams(section) {
        const params = new URLSearchParams();
        params.set('section', section);

        if (section === 'dashboard') {
            const categoryID = $('#dashboardCategoryFilter').val();
            if (categoryID) params.set('categoryID', categoryID);
        } else if (section === 'report') {
            const categoryID = $('#reportCategoryFilter').val();
            if (categoryID) params.set('categoryID', categoryID);
        }

        if (section === 'requisitions') {
            const rf = $('#requisitionReturnFilter').val();
            if (rf && rf !== 'all') {
                params.set('return_filter', rf);
            }
        }

        if (section === 'report') {
            const reportType = $('#reportTypeSelect').val() || 'all_inventory';
            params.set('report_type', reportType);

            if (reportType === 'all_inventory') {
                const retFilter = $('#reportReturnFilter').val();
                if (retFilter) params.set('return_filter', retFilter);
                const asOf = $('#reportAsOfDate').val();
                if (asOf) params.set('as_of_date', asOf);
            } else if (reportType === 'requisition') {
                const retFilter = $('#reqReportReturnFilter').val();
                if (retFilter) params.set('return_filter', retFilter);
                const retStatus = $('#reqReportReturnStatus').val();
                if (retStatus) params.set('return_status_filter', retStatus);
                params.set('period_type', $('#reportPeriodType').val() || 'current_month');
                params.set('month', $('#reportMonth').val() || '');
                params.set('year', $('#reportYear').val() || '');
                params.set('date_from', $('#reportDateFrom').val() || '');
                params.set('date_to', $('#reportDateTo').val() || '');
            } else if (reportType === 'specific_item') {
                const itemID = $('#reportItemSelect').val();
                if (!itemID) {
                    Swal.fire('Select Item', 'Chagua item kwanza kwa Specific Item Report PDF.', 'warning');
                    return null;
                }
                params.set('itemID', itemID);
                params.set('period_type', $('#reportPeriodType').val() || 'current_month');
                params.set('month', $('#reportMonth').val() || '');
                params.set('year', $('#reportYear').val() || '');
                params.set('date_from', $('#reportDateFrom').val() || '');
                params.set('date_to', $('#reportDateTo').val() || '');
            }
        }

        return params;
    }

    function downloadStorePdf(section) {
        const params = buildStorePdfParams(section);
        if (!params) return;

        const sectionTitles = {
            dashboard: 'Store Dashboard',
            items: 'Registered Items',
            stock: 'Item Stock',
            requisitions: 'Requisitions',
            damaged: 'Damaged Items',
            lost: 'Lost Items',
            categories: 'Item Categories',
            units: 'Measurement Types & Units',
            packages: 'Packages',
            report: 'Store Report'
        };

        $('#storePdfProgressTitle').text('Generating ' + (sectionTitles[section] || 'Store') + ' PDF');
        $('#storeDownloadProgressModal').modal('show');
        $('#storePdfProgressBar').css('width', '0%');
        $('#storePdfProgressPercent').text('0%');

        let progress = 0;
        const interval = setInterval(function () {
            if (progress < 95) {
                let inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                if (progress > 80) inc = 0.5 + Math.random();
                progress += inc;
                if (progress > 95) progress = 95;
                $('#storePdfProgressBar').css('width', progress + '%');
                $('#storePdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 400);

        const url = routes.exportPdf + '?' + params.toString();

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Server error: ' + response.statusText);
                return response.blob();
            })
            .then(blob => {
                clearInterval(interval);
                $('#storePdfProgressBar').css('width', '100%');
                $('#storePdfProgressPercent').text('100%');

                setTimeout(() => {
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
                    a.download = 'Store_' + section + '_' + timestamp + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);

                    setTimeout(() => {
                        $('#storeDownloadProgressModal').modal('hide');
                        setTimeout(() => {
                            $('#storePdfProgressBar').css('width', '0%');
                            $('#storePdfProgressPercent').text('0%');
                        }, 500);
                    }, 800);
                }, 400);
            })
            .catch(error => {
                clearInterval(interval);
                $('#storeDownloadProgressModal').modal('hide');
                Swal.fire('PDF Error', 'Failed to generate PDF: ' + error.message, 'error');
                console.error('Store PDF Export Error:', error);
            });
    }

    $(document).on('click', '.store-download-pdf-btn', function () {
        downloadStorePdf($(this).data('section'));
    });

    $('#reportReturnFilter, #reportAsOfDate').on('change', function () {
        if ($('#section-report').hasClass('active') && $('#reportTypeSelect').val() === 'all_inventory') {
            loadAllInventoryReport();
        }
    });

    function updateRequestBadges(count) {
        const n = parseInt(count, 10) || 0;
        const $menuBadge = $('#requisitionMenuBadge');
        const $panelBadge = $('#itemRequestsUnreadBadge');
        if (n > 0) {
            $menuBadge.text(n).show();
            if ($panelBadge.length) {
                $panelBadge.text(n + ' new').show();
            }
        } else {
            $menuBadge.hide();
            $panelBadge.remove();
        }
    }

    function renderItemRequestReviewBody(data) {
        const $body = $('#itemRequestReviewBody').empty();
        if (!data) return;
        if (data.layout === 'widgets' && data.sections) {
            data.sections.forEach(section => {
                $body.append(`<div class="store-view-widgets"><div class="store-widget-section-title">${escapeHtml(section.title || '')}</div><div class="store-view-widget-grid"></div></div>`);
                const $grid = $body.find('.store-view-widget-grid').last();
                (section.widgets || []).forEach(widget => {
                    const cls = widget.danger ? ' widget-danger' : (widget.wide ? ' widget-wide' : '');
                    $grid.append(`<div class="store-view-widget${cls}"><div class="widget-label">${escapeHtml(widget.label)}</div><div class="widget-value">${escapeHtml(widget.value || '—')}</div></div>`);
                });
            });
            return;
        }
        (data.fields || []).forEach(field => {
            $body.append(`<div class="store-view-field mb-3"><div class="store-view-label">${escapeHtml(field.label)}</div><div class="store-view-value">${escapeHtml(field.value || '—')}</div></div>`);
        });
    }

    function openItemRequestReviewModal(requestId) {
        const $row = $(`#requisitionsTable tr.store-item-request-row[data-request-id="${requestId}"]`);
        if (!$row.length) return;
        let data = $row.data('viewMore');
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch (e) { data = null; }
        }
        $('#itemRequestReviewTitle').text((data && data.title) ? data.title : 'Item Request');
        renderItemRequestReviewBody(data);
        const canApprove = String($row.attr('data-can-approve')) === '1';
        $('#itemRequestModalApprove, #itemRequestModalReject').toggle(canApprove);
        $('#itemRequestReviewModal').data('requestId', requestId).modal('show');
    }

    async function approveItemRequestById(id) {
        const result = await Swal.fire({
            icon: 'question',
            title: 'Approve this request?',
            text: 'Items will be issued to the requester from store stock.',
            input: 'textarea',
            inputLabel: 'Approval notes (optional)',
            inputPlaceholder: 'Enter any notes for the requester...',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve',
            reverseButtons: true,
            customClass: { popup: 'store-swal-popup', confirmButton: 'store-swal-confirm' },
        });
        if (!result.isConfirmed) return null;
        const json = await postJson(routes.itemRequestsApprove(id), { review_notes: result.value || null });
        showAlert(json.message, 'success');
        $(`#requisitionsTable tr[data-request-id="${id}"]`).fadeOut(300, function () {
            $(this).remove();
            if ($.fn.DataTable.isDataTable('#requisitionsTable')) {
                $('#requisitionsTable').DataTable().draw(false);
            }
        });
        (json.requisitions || []).forEach(req => {
            addStoreTableRow('requisitionsTable', buildRequisitionRow(req));
        });
        refreshStoreGridIfActive('requisitionsTable');
        $('#itemRequestReviewModal').modal('hide');
        return json;
    }

    async function rejectItemRequestById(id) {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Reject request?',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Enter reason for rejection...',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Reject',
            reverseButtons: true,
            customClass: { popup: 'store-swal-popup', confirmButton: 'store-swal-confirm' },
        });
        if (!result.isConfirmed) return null;
        const json = await postJson(routes.itemRequestsReject(id), { review_notes: result.value || null });
        showAlert(json.message, 'success');
        $(`#requisitionsTable tr[data-request-id="${id}"]`).fadeOut(300, function () {
            $(this).remove();
            if ($.fn.DataTable.isDataTable('#requisitionsTable')) {
                $('#requisitionsTable').DataTable().draw(false);
            }
        });
        refreshStoreGridIfActive('requisitionsTable');
        $('#itemRequestReviewModal').modal('hide');
        return json;
    }

    function promptItemRequestAction(requestId) {
        const $row = $(`#requisitionsTable tr.store-item-request-row[data-request-id="${requestId}"]`);
        if (!$row.length || String($row.attr('data-can-approve')) !== '1') return;

        const requester = $row.find('td').eq(1).clone().children().remove().end().text().trim().split('\n')[0].trim();
        const items = $row.find('td').eq(2).text().trim();

        Swal.fire({
            icon: 'question',
            title: 'Review item request?',
            html: '<div class="text-left"><strong>' + escapeHtml(requester) + '</strong><br><small class="text-muted">' + escapeHtml(items) + '</small></div>',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: '<i class="fa fa-check"></i> Approve',
            denyButtonText: '<i class="fa fa-times"></i> Reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#dc3545',
            reverseButtons: true,
            customClass: { popup: 'store-swal-popup', confirmButton: 'store-swal-confirm' },
        }).then(async function (result) {
            if (result.isConfirmed) {
                try {
                    await approveItemRequestById(requestId);
                } catch (err) {
                    showAlert(err.message, 'danger');
                }
            } else if (result.isDenied) {
                try {
                    await rejectItemRequestById(requestId);
                } catch (err) {
                    showAlert(err.message, 'danger');
                }
            }
        });
    }

    async function markItemRequestsRead() {
        if (!storeAccess.privileges || !storeAccess.privileges.approve_requests) return;
        try {
            const json = await postJson(routes.itemRequestsMarkRead, {});
            updateRequestBadges(json.unread_count || 0);
            $('#requisitionsTable tr.store-item-request-row.is-new').removeClass('is-new');
            $('#requisitionsTable tr.store-item-request-row .store-badge-new-request').each(function () {
                $(this).removeClass('store-badge-new-request').addClass('store-badge-pending').text('Requested');
            });
        } catch (err) {
            // silent
        }
    }

    $(document).on('click', 'tr.store-item-request-row.is-pending[data-can-approve="1"]', function (e) {
        if ($(e.target).closest('.store-action-dropdown, .dropdown-menu, .dropdown-item, button, a').length) return;
        e.preventDefault();
        promptItemRequestAction($(this).data('request-id'));
    });

    $(document).on('click', '.btn-approve-item-request', async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        try {
            await approveItemRequestById(id);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).on('click', '.btn-reject-item-request', async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        try {
            await rejectItemRequestById(id);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $('#itemRequestModalApprove').on('click', async function () {
        const id = $('#itemRequestReviewModal').data('requestId');
        if (!id) return;
        try {
            await approveItemRequestById(id);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $('#itemRequestModalReject').on('click', async function () {
        const id = $('#itemRequestReviewModal').data('requestId');
        if (!id) return;
        try {
            await rejectItemRequestById(id);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    (function () {
        const params = new URLSearchParams(window.location.search);
        if (params.get('open') === 'requisitions') {
            const tab = document.querySelector('.store-menu .list-group-item[data-section="section-requisitions"]');
            if (tab) tab.click();
        }
    })();

    function destroyActorFormSelect2() {
        $('#inventoryActorsModal .actor-form-select').each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        });
    }

    function initActorFormSelect2() {
        const $dropdownParent = $('#inventoryActorsModal');
        $('#inventoryActorsModal .actor-form-select').each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: !$select.prop('disabled'),
                minimumResultsForSearch: 0,
                dropdownParent: $dropdownParent,
            });
        });
    }

    function getActorModalScrollEl() {
        return document.querySelector('#inventoryActorsModal .store-actors-tab-scroll');
    }

    function restoreActorModalScroll(savedTop) {
        const el = getActorModalScrollEl();
        if (!el || savedTop == null) return;
        el.scrollTop = savedTop;
        requestAnimationFrame(function () {
            el.scrollTop = savedTop;
        });
    }

    function syncActorModalFooter() {
        const onAddTab = $('#actorModalTabs a[href="#actorTabAdd"]').hasClass('active');
        $('#actorModalFooter').toggle(onAddTab);
        $('#actorPersonFieldsStrip').toggle(onAddTab);
    }

    function resetInventoryActorForm() {
        destroyActorFormSelect2();
        $('#actorEditId').val('');
        $('#actorPersonType').prop('disabled', false).val('');
        $('#actorPersonSelect').prop('disabled', true).empty().append('<option value="">Select type first...</option>');
        $('#actorScopeAll').prop('checked', true);
        $('#actorCategoryChecks').hide();
        $('.actor-category-check').prop('checked', false);
        $('.actor-privilege-check').prop('checked', false);
        $('#btnSaveInventoryActor').html('<i class="fa fa-save"></i> Save Actor Access');
        $('#actorTabAddLink').text('Add Actor');
        initActorFormSelect2();
    }

    function populateActorPersonSelect() {
        const type = $('#actorPersonType').val();
        const $sel = $('#actorPersonSelect');
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }
        $sel.empty();
        if (!type) {
            $sel.prop('disabled', true).append('<option value="">Select type first...</option>');
            initActorFormSelect2();
            return;
        }
        const list = type === 'teacher' ? actorPickerTeachers : actorPickerStaff;
        $sel.prop('disabled', false).append('<option value="">Search or select person...</option>');
        list.forEach(p => {
            let label = p.name;
            if (p.employee_number) label += ' (' + p.employee_number + ')';
            $sel.append(new Option(label, p.id, false, false));
        });
        initActorFormSelect2();
    }

    let actorFormScrollMem = 0;

    $('input[name="actorCategoryScope"]').on('change', function () {
        const scrollEl = getActorModalScrollEl();
        const savedScroll = scrollEl ? scrollEl.scrollTop : 0;
        $('#actorCategoryChecks').toggle($('#actorScopeSpecific').is(':checked'));
        restoreActorModalScroll(savedScroll);
    });

    $('#actorPersonType').on('change', populateActorPersonSelect);

    $('#inventoryActorsModal').on('mousedown', '.actor-category-check, .actor-privilege-check, input[name="actorCategoryScope"], label.custom-control-label', function () {
        const scrollEl = getActorModalScrollEl();
        actorFormScrollMem = scrollEl ? scrollEl.scrollTop : 0;
    });

    $('#inventoryActorsModal').on('change', '.actor-category-check, .actor-privilege-check', function () {
        restoreActorModalScroll(actorFormScrollMem);
    });

    $('#inventoryActorsModal').on('shown.bs.modal', function () {
        initActorFormSelect2();
        syncActorModalFooter();
        // Zima Bootstrap focus trap — husababisha scroll kurudi juu na Select2 kutoweza kuandika
        $(document).off('focusin.bs.modal');
    });

    $('#actorModalTabs a[data-toggle="tab"]').on('shown.bs.tab', function () {
        syncActorModalFooter();
        const el = getActorModalScrollEl();
        if (el) el.scrollTop = 0;
    });

    $(document).on('select2:open', '#inventoryActorsModal .actor-form-select', function () {
        setTimeout(function () {
            const searchField = $('#inventoryActorsModal .select2-container--open .select2-search__field')[0];
            if (searchField) {
                searchField.focus();
            }
        }, 0);
    });

    $('#inventoryActorsModal').on('hidden.bs.modal', resetInventoryActorForm);

    $(document).on('click', '.btn-edit-actor', function () {
        const actorId = $(this).data('id') || $(this).data('actor')?.actorID;
        const actor = inventoryActorsState.find(a => String(a.actorID) === String(actorId)) || $(this).data('actor');
        if (!actor) return;
        resetInventoryActorForm();
        $('#actorEditId').val(actor.actorID);
        $('#actorPersonType').val(actor.actor_type).prop('disabled', true);
        populateActorPersonSelect();
        $('#actorPersonSelect').val(String(actor.actor_ref_id)).prop('disabled', true).trigger('change');
        destroyActorFormSelect2();
        initActorFormSelect2();
        if (actor.category_scope === 'specific') {
            $('#actorScopeSpecific').prop('checked', true);
            $('#actorCategoryChecks').show();
            (actor.category_ids || []).forEach(id => {
                $('#actorCat' + id).prop('checked', true);
            });
        }
        Object.keys(actor.privileges || {}).forEach(key => {
            if (actor.privileges[key]) {
                $('#actorPriv' + key).prop('checked', true);
            }
        });
        $('#btnSaveInventoryActor').html('<i class="fa fa-save"></i> Update Actor Access');
        $('#actorTabAddLink').text('Edit Actor');
        $('#actorModalTabs a[href="#actorTabAdd"]').tab('show');
        syncActorModalFooter();
    });

    $(document).on('click', '.btn-delete-actor', async function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        if (!(await storeConfirm('Remove inventory access from ' + name + '?', 'Remove actor'))) return;
        try {
            const json = await postJson(routes.inventoryActorsDelete(id), {});
            showAlert(json.message, 'success');
            $(`tr[data-actor-id="${id}"]`).remove();
            inventoryActorsState = inventoryActorsState.filter(a => String(a.actorID) !== String(id));
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $('#inventoryActorForm').on('submit', async function (e) {
        e.preventDefault();
        const editId = $('#actorEditId').val();
        const privileges = {};
        $('.actor-privilege-check').each(function () {
            privileges[$(this).val()] = $(this).is(':checked');
        });
        const payload = {
            category_scope: $('input[name="actorCategoryScope"]:checked').val(),
            category_ids: $('.actor-category-check:checked').map(function () { return parseInt($(this).val(), 10); }).get(),
            privileges: privileges,
        };
        if (!editId) {
            payload.actor_type = $('#actorPersonType').val();
            payload.actor_ref_id = parseInt($('#actorPersonSelect').val(), 10);
            if (!payload.actor_type || !payload.actor_ref_id) {
                showAlert('Select a person.', 'danger');
                return;
            }
        }
        try {
            const url = editId ? routes.inventoryActorsUpdate(editId) : routes.inventoryActorsStore;
            const json = await postJson(url, payload);
            showAlert(json.message, 'success');
            if (editId) {
                $(`tr[data-actor-id="${editId}"]`).remove();
                const idx = inventoryActorsState.findIndex(a => String(a.actorID) === String(editId));
                if (idx >= 0) inventoryActorsState[idx] = json.actor;
                else inventoryActorsState.push(json.actor);
            } else {
                inventoryActorsState.push(json.actor);
            }
            const actor = json.actor;
            const $row = $(`<tr data-actor-id="${actor.actorID}">
                <td></td>
                <td><strong>${escapeHtml(actor.actor_name)}</strong></td>
                <td>${escapeHtml(actor.actor_type_label)}</td>
                <td class="small">${escapeHtml(actor.category_summary)}</td>
                <td class="small">${escapeHtml(actor.privileges_summary)}</td>
                <td class="small">${escapeHtml(actor.created_at)}<br><span class="text-muted">${escapeHtml(actor.granted_by || '')}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-actor" data-id="${actor.actorID}"><i class="fa fa-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-actor" data-id="${actor.actorID}" data-name="${escapeHtml(actor.actor_name)}"><i class="fa fa-trash"></i></button>
                </td>
            </tr>`);
            if (editId) {
                $(`tr[data-actor-id="${editId}"]`).replaceWith($row);
            } else {
                $('#inventoryActorsTableBody').append($row);
            }
            $('#inventoryActorsTableBody tr').each(function (i) { $(this).find('td:first').text(i + 1); });
            $('#inventoryActorsModal').modal('hide');
            resetInventoryActorForm();
            $('#actorModalTabs a[href="#actorTabList"]').tab('show');
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(document).ready(function () {
        damagedIncidentsUi.forEach(inc => addStoreTableRow('damagedItemsTable', buildIncidentRow(inc)));
        lostIncidentsUi.forEach(inc => addStoreTableRow('lostItemsTable', buildIncidentRow(inc)));
        renderStoreDashboard(storeDashboardData);
        initAllStoreDataTables();
        initStoreDataViews();
        initItemFormSelect2($('.item-form-select'));
        $('#addItemModal, #editItemModal').on('shown.bs.modal', function () {
            initItemFormSelect2($(this).find('.item-form-select'));
        });
    });
})();
</script>
