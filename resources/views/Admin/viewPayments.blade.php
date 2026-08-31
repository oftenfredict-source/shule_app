@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');

    body {
        font-family: 'Century Gothic', sans-serif !important;
        background-color: #FFFFFF !important;
    }

    * {
        border-radius: 0 !important;
    }

    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
        font-weight: 600;
    }
    .btn-primary-custom:hover {
        background-color: #7a0000;
        border-color: #7a0000;
        color: #ffffff;
        box-shadow: 0 4px 8px rgba(148, 0, 0, 0.2);
    }
    
    .card {
        border: 1px solid #FFF5F5 !important;
        background-color: #FFFFFF !important;
        box-shadow: 0 4px 6px -1px rgba(148, 0, 0, 0.05) !important;
    }

    .card-header-gradient {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
        border-bottom: 2px solid #940000 !important;
        color: #940000 !important;
        font-weight: bold;
    }

    /* Modal Styling */
    .modal-header {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
        border-bottom: 3px solid #940000 !important;
        color: #940000 !important;
    }
    .modal-title {
        font-weight: bold !important;
        color: #940000 !important;
        font-family: 'Century Gothic', sans-serif !important;
    }
    .modal-content {
        border-radius: 0 !important;
        border: none !important;
        overflow: hidden;
    }

    /* Tabs Styling */
    .nav-pills .nav-link {
        color: #940000 !important;
        font-weight: bold;
        border: 1px solid #FFF5F5;
        margin-right: 5px;
        background-color: #FFFFFF;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
        border: 1px solid #940000 !important;
        color: #940000 !important;
        box-shadow: 0 2px 4px rgba(148, 0, 0, 0.1);
    }
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #FFF5F5 !important;
    }

    /* Statistics Backgrounds, whitesmoke + #940000 like manage_student */
    .stat-card-accent {
        background-color: whitesmoke !important;
        border: 1px solid #ececec !important;
        border-left: 4px solid #940000 !important;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .stat-card-accent:hover {
        border-color: #d8d8d8 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
    }
    .stat-card-accent h4, .stat-card-accent h3 {
        color: #940000 !important;
        font-weight: 700;
    }
    .stat-card-accent h6 {
        color: #666 !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .payment-section-title {
        font-weight: 600;
        margin-bottom: 0;
        color: #940000;
        font-size: 1.05rem;
        font-family: "Century Gothic", Arial, sans-serif;
    }

    .student-photo {
        width: 40px;
        height: 40px;
        border-radius: 0;
        object-fit: cover;
        border: 2px solid #940000;
    }
    .student-photo-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #940000;
        color: white;
        font-weight: bold;
        font-size: 16px;
        border: 2px solid #940000;
    }
    .control-number {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        color: #940000;
    }
    #paymentsTable th {
        background-color: whitesmoke;
        color: #940000;
        font-weight: bold;
        border-bottom: 2px solid #940000;
    }
    #paymentsTable tbody tr:hover {
        background-color: whitesmoke;
    }

    /* Payments list / grid toggle */
    .payment-view-toggle .btn.active {
        background-color: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 700;
    }
    .payment-data-wrapper .payment-grid-view { display: none; overflow: visible; }
    .payment-data-wrapper[data-active-view="list"] .payment-list-view { display: block !important; }
    .payment-data-wrapper[data-active-view="list"] .payment-grid-view { display: none !important; }
    .payment-data-wrapper[data-active-view="grid"] .payment-list-view { display: none !important; }
    .payment-data-wrapper[data-active-view="grid"] .payment-grid-view { display: block !important; overflow: visible; }

    .payment-grid-search-wrap {
        width: 100%;
        margin-bottom: 14px;
    }
    .payment-grid-search-wrap .form-control {
        width: 100%;
        border-radius: 0 !important;
        padding: 10px 14px;
        border: 1px solid #dee2e6;
    }
    .payment-grid-search-wrap .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }

    .payments-widget-grid {
        display: flex;
        flex-wrap: wrap;
        margin-left: -8px;
        margin-right: -8px;
        overflow: visible;
    }
    .payments-widget-grid > [class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
        margin-bottom: 16px;
        overflow: visible;
    }

    .payment-student-widget {
        width: 100%;
        background: #ffffff;
        border: 1px solid #dee2e6;
        overflow: visible;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 1;
    }
    .payment-student-widget:has(.payment-action-dropdown.show) {
        z-index: 20;
    }
    .payment-student-widget-header {
        background: #940000;
        padding: 16px;
        text-align: center;
        position: relative;
        overflow: visible;
    }
    .payment-grid-actions-top {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 5;
    }
    .payment-grid-actions-top .payment-action-toggle {
        background: rgba(255,255,255,0.95);
        border-color: rgba(255,255,255,0.6);
        color: #940000;
        padding: 4px 10px;
    }
    .payment-grid-actions-top .payment-action-dropdown .dropdown-menu {
        right: 0;
        left: auto;
        top: 100%;
    }
    .payment-view-toggle-bar {
        position: sticky;
        top: 0;
        z-index: 40;
        background: #fff;
        border: 1px solid #ececec;
        border-left: 4px solid #940000;
        padding: 10px 14px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .payment-view-toggle-bar .payment-view-label {
        font-weight: 700;
        color: #940000;
        margin: 0;
        font-size: 0.95rem;
    }
    .payment-student-widget-header img,
    .payment-student-widget-header .payment-grid-photo-placeholder {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.35);
        background: #fff;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        color: #fff;
    }
    #smsTemplateModal {
        z-index: 1065 !important;
    }
    body.sms-template-open .modal-backdrop {
        z-index: 1060 !important;
    }
    #smsTemplateEditorList {
        max-height: 55vh;
        overflow-y: auto;
    }
    #smsTemplateEditorList textarea {
        background: #fff;
        resize: vertical;
        min-height: 110px;
    }

    /* View More modal, desktop */
    .view-more-modal-dialog {
        max-width: 95%;
        width: 1200px;
    }
    #viewMoreModal .view-more-stats-row .col {
        min-width: 0;
    }
    #viewMoreModal .enrollment-years-header small {
        text-align: right;
    }

    @media (max-width: 767.98px) {
        .view-more-modal-dialog {
            max-width: calc(100% - 0.5rem) !important;
            width: 100% !important;
            margin: 0.25rem auto;
            max-height: calc(100% - 0.5rem);
        }
        #viewMoreModal .modal-content {
            max-height: calc(100vh - 0.5rem);
            display: flex;
            flex-direction: column;
            border-radius: 0;
        }
        #viewMoreModal .modal-body {
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1 1 auto;
            padding: 0.75rem;
            -webkit-overflow-scrolling: touch;
        }
        #viewMoreModal .modal-header,
        #viewMoreModal .modal-footer {
            padding: 0.75rem;
            flex-shrink: 0;
        }
        #viewMoreModal .modal-header .modal-title {
            font-size: 1rem;
            line-height: 1.3;
            padding-right: 1.5rem;
        }
        #viewMoreModal .view-more-student-row > [class*="col-"] {
            text-align: center !important;
            margin-bottom: 0.65rem;
        }
        #viewMoreModal #view_student_name {
            font-size: 1.05rem;
            word-break: break-word;
        }
        #viewMoreModal #view_student_enrollment_badges .badge {
            margin-bottom: 0.25rem;
        }
        #viewMoreModal #singleAccountCnWrap .stat-card-accent {
            text-align: center;
        }
        #viewMoreModal .view-more-stats-row > .col {
            flex: 0 0 50%;
            max-width: 50%;
            margin-bottom: 0.35rem;
        }
        #viewMoreModal .view-more-stats-row > .col:nth-child(5) {
            flex: 0 0 100%;
            max-width: 100%;
        }
        #viewMoreModal .view-more-stats-row h6 {
            font-size: 0.9rem;
            word-break: break-word;
        }
        #viewMoreModal .enrollment-years-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.35rem;
        }
        #viewMoreModal .enrollment-years-header small {
            text-align: left;
            font-size: 0.72rem;
            line-height: 1.35;
            word-break: break-word;
        }
        #viewMoreModal .table-responsive {
            overflow-x: visible;
        }
        #viewMoreModal .view-more-responsive-table {
            border: 0;
        }
        #viewMoreModal .view-more-responsive-table thead {
            display: none;
        }
        #viewMoreModal .view-more-responsive-table tbody tr {
            display: block;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            padding: 0.35rem 0;
            background: #fff;
        }
        #viewMoreModal .view-more-responsive-table tbody tr.table-danger {
            background: #fff5f5;
        }
        #viewMoreModal .view-more-responsive-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
            border: none !important;
            padding: 0.45rem 0.75rem !important;
            text-align: right !important;
            word-break: break-word;
        }
        #viewMoreModal .view-more-responsive-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #666;
            font-size: 0.78rem;
            text-align: left;
            flex: 0 0 40%;
            max-width: 40%;
        }
        #viewMoreModal .view-more-responsive-table td.text-nowrap,
        #viewMoreModal .view-more-responsive-table td.text-center {
            justify-content: space-between;
        }
        #viewMoreModal .student-photo-modal-container img,
        #viewMoreModal .student-photo-modal-container .student-photo-placeholder {
            width: 72px !important;
            height: 72px !important;
            font-size: 28px !important;
        }
        #viewMoreModal .modal-footer {
            flex-direction: column;
            gap: 0.5rem;
        }
        #viewMoreModal .modal-footer .btn,
        #viewMoreModal #modalRecordPaymentContainer .btn {
            width: 100%;
        }
        #viewMoreModal #modalRecordPaymentContainer .btn-group {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 0.5rem;
        }
        #viewMoreModal #modalRecordPaymentContainer .btn-group .btn {
            width: 100%;
            border-radius: 0 !important;
        }
        #viewMoreModal #sponsorInfoSection .border-start,
        #viewMoreModal #sponsorInfoSection .border-end {
            border: none !important;
        }
        #viewMoreModal #sponsorInfoSection .col-md-4.text-end {
            text-align: center !important;
        }
        #viewMoreModal .view-more-responsive-table td[data-label="Action"],
        #viewMoreModal .view-more-responsive-table td[data-label="Actions"] {
            flex-wrap: wrap;
        }
    }

    tr.payment-deadline-overdue td {
        background-color: #fff5f5 !important;
    }
    tr.payment-deadline-overdue td:first-child {
        box-shadow: inset 4px 0 0 #dc3545;
    }
    .payment-student-widget.payment-deadline-overdue {
        border-color: #dc3545;
        box-shadow: 0 0 0 1px rgba(220, 53, 69, 0.25);
    }
    .payment-student-widget.payment-deadline-overdue .payment-student-widget-header {
        background: #b30000;
    }
    .payment-student-widget-body {
        padding: 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .payment-student-widget-body h3 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 4px;
        color: #222;
        line-height: 1.3;
    }
    .payment-student-widget-body .pay-class {
        color: #940000;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 8px;
    }
    .payment-student-widget-body .pay-meta {
        font-size: 0.84rem;
        color: #555;
        margin-bottom: 4px;
    }
    .payment-student-widget-body .pay-amounts {
        margin-top: 8px;
        padding-top: 10px;
        border-top: 1px solid #f0f0f0;
        font-size: 0.84rem;
    }
    .payment-student-widget-actions {
        margin-top: auto;
        padding-top: 12px;
    }
    .payment-grid-empty {
        border: 1px dashed #dee2e6;
        padding: 2rem 1rem;
        text-align: center;
        color: #6c757d;
        width: 100%;
    }

    /* Actions toggle dropdown (three dots), same pattern as manage_student */
    .payment-action-dropdown {
        display: inline-block;
        position: relative;
    }
    .payment-action-toggle {
        padding: 4px 12px;
        color: #555;
        background: #fff;
        border: 1px solid #dee2e6;
        line-height: 1.2;
        border-radius: 0;
        cursor: pointer;
    }
    .payment-action-toggle:hover,
    .payment-action-dropdown.show .payment-action-toggle {
        background: whitesmoke;
        color: #940000;
        border-color: #ccc;
    }
    .payment-action-dropdown .dropdown-menu {
        min-width: 200px;
        padding: 4px 0;
        font-size: 0.875rem;
        border-radius: 0;
        border: 1px solid #dee2e6;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        display: none;
        position: absolute;
        right: 0;
        left: auto;
        top: 100%;
        z-index: 1050;
        background: #fff;
        margin-top: 2px;
    }
    .payment-action-dropdown.show > .dropdown-menu {
        display: block;
    }
    .payment-action-dropdown .dropdown-item {
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        text-decoration: none;
        border: 0;
        background: transparent;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    .payment-action-dropdown .dropdown-item:hover {
        background: whitesmoke;
        color: #940000;
    }
    .payment-action-dropdown .dropdown-item i {
        width: 18px;
        text-align: center;
    }
    .payment-actions-cell {
        width: 70px;
        text-align: center;
        white-space: nowrap;
    }
    .payment-student-widget .payment-action-dropdown .dropdown-menu {
        right: 0;
        left: auto;
    }
    .badge-primary-custom {
        background-color: #FFF5F5;
        color: #940000;
        border: 1px solid #940000;
    }
    .border-primary-custom {
        border: 1px solid #940000 !important;
    }
    
    .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    
    /* Mobile Responsive DataTables Cards */
    @media screen and (max-width: 767px) {
        #paymentsTable thead, #paymentsTable tfoot {
            display: none;
        }
        #paymentsTable, #paymentsTable tbody, #paymentsTable tr, #paymentsTable td {
            display: block;
            width: 100%;
        }
        #paymentsTable tr {
            margin-bottom: 20px;
            border: 1px solid #FFF5F5 !important;
            border-left: 5px solid #940000 !important;
            box-shadow: 0 4px 6px -1px rgba(148, 0, 0, 0.1) !important;
            background-color: #ffffff;
            border-radius: 4px;
        }
        #paymentsTable td {
            text-align: right !important;
            padding-left: 50% !important;
            position: relative;
            border: none;
            border-bottom: 1px solid #f9f9f9;
            min-height: 40px;
        }
        #paymentsTable td:last-child {
            border-bottom: 0;
            text-align: center !important;
            padding-left: 15px !important;
            padding-bottom: 15px;
            padding-top: 15px;
        }
        #paymentsTable td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            width: 45%;
            padding-right: 10px;
            text-align: left;
            font-weight: bold;
            color: #940000;
            top: 50%;
            transform: translateY(-50%);
            white-space: nowrap;
        }
        #paymentsTable td:last-child::before {
            display: none;
        }
        .dataTables_wrapper .row {
            margin: 0 !important;
        }
        .dataTables_wrapper .col-sm-12 {
            padding: 0 !important;
        }
        #paymentsTable .d-flex.align-items-center {
            justify-content: flex-end;
            text-align: right;
        }
        #paymentsTable .bill-container {
            text-align: right;
        }
        body, html {
            overflow-x: hidden;
        }
        .stat-card-accent h4, .stat-card-accent h3 {
            font-size: 1.15rem !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .stat-card-accent h6 {
            font-size: 0.75rem !important;
        }
        #statisticsCards > div, #amountSummary > div {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
        #statisticsCards.row, #amountSummary.row {
            margin-left: -8px;
            margin-right: -8px;
        }
    }

    /* ── Theme overrides: remove Bootstrap blues, use whitesmoke/white/#940000 ── */
    .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        background: #fff;
        border-radius: 0 !important;
    }
    .btn-outline-primary:hover,
    .btn-outline-primary:focus,
    .btn-outline-primary:active {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        box-shadow: none !important;
    }
    .card-header.bg-info {
        background-color: #940000 !important;
        color: #fff !important;
    }
    .badge.bg-info {
        background-color: whitesmoke !important;
        color: #940000 !important;
        border: 1px solid #940000;
    }
    .alert-info {
        background-color: whitesmoke !important;
        color: #333 !important;
        border: 1px solid #e0e0e0 !important;
        border-left: 4px solid #940000 !important;
    }
    .spinner-border.text-primary {
        color: #940000 !important;
    }
    #quickRecordStudentResults .quick-student-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        border-bottom: 1px solid #f0e0e0;
        background: #fff;
        padding: 10px 12px;
        cursor: pointer;
    }
    #quickRecordStudentResults .quick-student-item:hover {
        background: #fff5f5;
    }
    #quickRecordStudentResults .quick-student-item .qs-name {
        font-weight: 700;
        color: #940000;
    }
    #quickRecordStudentResults .quick-student-item .qs-meta {
        font-size: 12px;
        color: #777;
    }

    .form-control:focus, .form-select:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15) !important;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white rounded shadow" style="background: linear-gradient(135deg, #940000 0%, #7a0000 100%) !important;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <h4 class="mb-0 fw-bold text-center text-md-start mb-3 mb-md-0">
                            <i class="bi bi-credit-card me-2"></i> Payments Management & Control Numbers
                        </h4>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <button class="btn btn-warning text-dark fw-bold shadow-sm" type="button" id="quickRecordPaymentBtn">
                                <i class="bi bi-cash-coin"></i> Record Payment
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold shadow-sm" type="button" id="generateControlNumbersBtn">
                                <i class="bi bi-key"></i> Generate CN
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold shadow-sm" type="button" id="sendSMSBtn">
                                <i class="bi bi-send-check"></i> Send CN
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold shadow-sm" type="button" id="sendDebtRemindersBtn">
                                <i class="bi bi-bell-fill"></i> Send Reminders
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold shadow-sm" type="button" id="exportFilteredPdfBtn">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statisticsCards">
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-2 small fw-bold">Pending</h6>
                            <h4 class="mb-0 text-dark fw-bold" id="statPendingPayments">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-2 small fw-bold">Incomplete</h6>
                            <h4 class="mb-0 text-dark fw-bold" id="statIncompletePayments">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-2 small fw-bold">Fully Paid</h6>
                            <h4 class="mb-0 text-dark fw-bold" id="statPaidPayments">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-lg mb-3">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-2 small fw-bold">Overpaid</h6>
                            <h4 class="mb-0 text-dark fw-bold" id="statOverpaidPayments">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg mb-3">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-2 small fw-bold">Total Balance</h6>
                            <h4 class="mb-0 text-primary-custom fw-bold" id="statTotalBalance">0</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Summary -->
            <div class="row mb-4" id="amountSummary">
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2 fw-bold">Total Amount Required</h6>
                            <h3 class="mb-0 text-dark fw-bold" id="statTotalRequired">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm stat-card-accent h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2 fw-bold">Total Amount Paid</h6>
                            <h3 class="mb-0 text-dark fw-bold" id="statTotalPaid">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #7a0000 100%) !important; color: white !important;">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2 fw-bold">Outstanding Balance</h6>
                            <h3 class="mb-0 text-white fw-bold" id="statOutstandingBalance">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-book"></i> Class
                            </label>
                            <select class="form-select" id="filterClass">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="col-md-2" id="filterFeeWrap" style="display:none;">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-cash-stack"></i> Fee Account
                            </label>
                            <select class="form-select" id="filterFee">
                                <option value="all">All Fees</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-layers"></i> Subclass
                            </label>
                            <select class="form-select" id="filterSubclass">
                                <option value="">All Subclasses</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-person-check"></i> Enrollment Status
                            </label>
                            <select class="form-select" id="filterStatus">
                                <option value="all">All statuses</option>
                                <option value="Active" selected>Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Dropped">Dropped</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-arrow-up-right-circle"></i> Promotion Status
                            </label>
                            <select class="form-select" id="filterPromotion">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Promoted">Promoted</option>
                                <option value="Repeated">Repeated</option>
                                <option value="Graduated">Graduated</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Truancy">Truancy</option>
                                <option value="Dropout">Dropout</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-none">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-filter"></i> Fee Type
                            </label>
                            <select class="form-select" id="filterFeeType">
                                <option value="" selected>All Types</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-credit-card"></i> Payment Status
                            </label>
                            <select class="form-select" id="filterPaymentStatus">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Incomplete">Incomplete</option>
                                <option value="Partial">Partial</option>
                                <option value="Paid">Paid</option>
                                <option value="Overpaid">Overpaid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-alarm"></i> Deadline
                            </label>
                            <select class="form-select" id="filterDeadline">
                                <option value="">All</option>
                                <option value="reached_unpaid">Reached, not paid</option>
                                <option value="reached_paid">Reached, paid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-shield-check"></i> Sponsorship
                            </label>
                            <select class="form-select" id="filterSponsorship">
                                <option value="">All</option>
                                <option value="sponsored">Sponsored</option>
                                <option value="self">Self-paying</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-search"></i> Search Student Name
                            </label>
                            <input type="text" class="form-control" id="searchStudentName" placeholder="Enter student name...">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-arrow-clockwise"></i> Actions
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary flex-fill" id="clearFiltersBtn" title="Clear Filters">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                                <button type="button" class="btn btn-primary-custom flex-fill" id="openReportBtn" title="Open Payments Report">
                                    <i class="bi bi-graph-up"></i> Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="payment-view-toggle-bar">
                <h5 class="payment-view-label mb-0">
                    <i class="bi bi-credit-card-2-front"></i> Control Numbers &amp; Payments
                </h5>
                <div class="btn-group btn-group-sm payment-view-toggle" role="group" aria-label="View mode">
                    <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view">
                        <i class="bi bi-list-ul"></i> <span class="d-none d-sm-inline">List</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view">
                        <i class="bi bi-grid-3x3-gap"></i> <span class="d-none d-sm-inline">Grid</span>
                    </button>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="payment-data-wrapper" id="paymentsDataWrapper" data-active-view="list">
                        <div class="payment-list-view">
                            <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                                <table class="table table-hover table-sm mb-0" id="paymentsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student Details</th>
                                            <th>Payment Type</th>
                                            <th>Bill Summary (TZS)</th>
                                            <th>Balance & Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filter Student" data-column="0"></th>
                                            <th><select class="form-control form-control-sm column-filter" data-column="1">
                                                <option value="">All Types</option>
                                                <option value="Sponsored">Sponsored</option>
                                                <option value="Own Payment">Own Payment</option>
                                            </select></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filter Bill" data-column="2"></th>
                                            <th><select class="form-control form-control-sm column-filter" data-column="3">
                                                <option value="">All Status</option>
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Incomplete">Incomplete</option>
                                            </select></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    <tbody id="paymentsTableBody">
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="payment-grid-view p-3">
                            <div class="payment-grid-search-wrap">
                                <input type="search" class="form-control" id="paymentGridSearch" placeholder="Search by name, admission, class..." autocomplete="off">
                            </div>
                            <div id="paymentsWidgetGrid" class="payments-widget-grid"></div>
                            <div id="paymentsGridLoadMore" class="text-center py-3 d-none" aria-live="polite">
                                <div class="spinner-border spinner-border-sm text-primary-custom me-2" role="status"></div>
                                <span>Loading more...</span>
                            </div>
                            <div id="paymentsGridCount" class="text-center text-muted small py-2 d-none"></div>
                            <div id="paymentsGridSentinel" style="height:1px;" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View More Modal -->
<div class="modal fade" id="viewMoreModal" tabindex="-1" aria-labelledby="viewMoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable view-more-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMoreModalLabel">
                    <i class="bi bi-eye me-2"></i> Student Payment Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Student Information -->
                <div class="row mb-4 align-items-center view-more-student-row">
                    <div class="col-12 col-md-2 text-center">
                        <div class="student-photo-modal-container">
                            <img id="view_student_photo" src="" alt="Student Photo" 
                                class="img-thumbnail" 
                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 0; display: none;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div id="view_student_photo_placeholder" 
                                class="student-photo-placeholder d-none" 
                                style="width: 100px; height: 100px; border-radius: 0; display: none; align-items: center; justify-content: center; font-size: 36px; font-weight: bold; color: white; margin: 0 auto;">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-center text-md-start">
                        <h4 class="text-primary-custom mb-1" id="view_student_name">-</h4>
                        <p class="mb-1 text-muted"> <i class="bi bi-book"></i> <span id="view_student_class">-</span> | <i class="bi bi-person-badge"></i> <span id="view_student_admission">-</span></p>
                        <div id="view_student_enrollment_badges" class="mb-0"></div>
                    </div>
                    <div class="col-12 col-md-4 text-center text-md-end" id="singleAccountCnWrap">
                        <div class="p-2 stat-card-accent rounded border">
                            <small class="text-muted d-block font-weight-bold">Control Number</small>
                            <span class="h5 mb-0 text-primary-custom fw-bold" id="view_control_number">-</span>
                        </div>
                    </div>
                </div>

                <div id="multiAccountsSection" class="mb-4" style="display:none;">
                    <h6 class="fw-bold text-primary-custom mb-2"><i class="bi bi-bank"></i> Multi Account Control Numbers</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 view-more-responsive-table">
                            <thead style="background:whitesmoke;color:#940000;">
                                <tr>
                                    <th>Fee</th>
                                    <th>Account</th>
                                    <th>Control Number</th>
                                    <th class="text-end">Required</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="multiAccountsBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Summary Dashboard -->
                <div class="row mb-4 g-2 view-more-stats-row">
                    <div class="col-6 col-md">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Fee Required</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_total_fee_required">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Total Paid</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_total_paid">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Bill Balance</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_bill_balance">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Prev. Year Debt</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_old_debt">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md">
                        <div class="card border-0 bg-primary-custom text-white h-100 shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #7a0000 100%) !important;">
                            <div class="card-body p-2 text-center">
                                <small class="d-block mb-1 text-white-50 font-weight-bold" style="font-size: 0.75rem;">Total Outstanding</small>
                                <h6 class="mb-0 fw-bold text-white" id="view_total_outstanding">0.00</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Year / Enrollment Payment History -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center enrollment-years-header">
                        <span class="fw-bold text-dark">
                            <i class="bi bi-calendar3 me-2"></i> Academic Years &amp; Enrollment Payments
                        </span>
                        <small class="text-muted" id="enrollmentYearsSummary">Loading…</small>
                    </div>
                    <div class="card-body p-0">
                        <div id="enrollmentYearsLoading" class="text-center py-3 text-muted small">
                            <i class="bi bi-hourglass-split"></i> Loading enrollment payment history…
                        </div>
                        <div class="table-responsive" id="enrollmentYearsTableWrap" style="display:none;">
                            <table class="table table-sm table-bordered table-hover mb-0 view-more-responsive-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Academic Year</th>
                                        <th>Main Class</th>
                                        <th>Enrollment</th>
                                        <th>Promotion</th>
                                        <th class="text-end">Required</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Owed</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="enrollmentYearsBody">
                                </tbody>
                            </table>
                        </div>
                        <div id="enrollmentYearsEmpty" class="text-center py-3 text-muted small" style="display:none;">
                            No enrollment / academic year payment history found for this student.
                        </div>
                    </div>
                </div>

                <!-- Sponsorship Information (Hidden by default) -->
                <div id="sponsorInfoSection" class="mb-4" style="display: none;">
                    <div class="card border shadow-sm mb-3">
                        <div class="card-header card-header-gradient py-1 px-3">
                            <small class="fw-bold"><i class="bi bi-shield-check me-2"></i> Sponsorship Summary</small>
                        </div>
                        <div class="card-body py-2 px-3">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <h6 class="mb-1 text-primary-custom fw-bold" id="view_sponsor_name">-</h6>
                                    <div class="small mb-1"><i class="bi bi-person"></i> <span id="view_sponsor_contact_person">-</span></div>
                                    <div class="small text-muted mb-1"><i class="bi bi-telephone"></i> <span id="view_sponsor_phone">-</span></div>
                                    <div class="small text-muted"><i class="bi bi-envelope"></i> <span id="view_sponsor_email">-</span></div>
                                </div>
                                <div class="col-md-3 text-center border-start border-end">
                                    <div class="display-6 fw-bold text-primary-custom" id="view_sponsor_percentage">0%</div>
                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Coverage</small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted d-block">Sponsor Component</small>
                                    <h5 class="mb-0 text-success fw-bold" id="view_sponsor_amount">0.00/=</h5>
                                    <small class="text-muted" style="font-size: 0.7rem;">(Fee amount assigned to sponsor)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Split Portions Tracking -->
                    <div class="row g-2" id="splitBillPortionsSection">
                        <div class="col-md-6">
                            <div class="card border-info h-100 bg-light-info">
                                <div class="card-header py-1 bg-info text-white">
                                    <small class="fw-bold">Sponsor Portion</small>
                                </div>
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Required:</span>
                                        <span class="fw-bold" id="view_sponsor_req_val">0/=</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Paid:</span>
                                        <span class="fw-bold text-success" id="view_sponsor_paid_val">0/=</span>
                                    </div>
                                    <div class="border-top pt-1 mt-1 d-flex justify-content-between small">
                                        <span>Balance:</span>
                                        <span class="fw-bold text-danger" id="view_sponsor_bal_val">0/=</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-secondary h-100 bg-light">
                                <div class="card-header py-1 bg-secondary text-white">
                                    <small class="fw-bold">Parent Portion</small>
                                </div>
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Required:</span>
                                        <span class="fw-bold" id="view_parent_req_val">0/=</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Paid:</span>
                                        <span class="fw-bold text-success" id="view_parent_paid_val">0/=</span>
                                    </div>
                                    <div class="border-top pt-1 mt-1 d-flex justify-content-between small">
                                        <span>Balance:</span>
                                        <span class="fw-bold text-danger" id="view_parent_bal_val">0/=</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Record Payment Button Section -->
                <div id="modalRecordPaymentContainer" class="mb-4 text-center">
                    <!-- Button will be dynamically inserted here -->
                </div>

                <!-- Expandable Detailed Breakdown -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 p-0">
                        <button class="btn btn-link w-100 text-start text-decoration-none p-3 d-flex justify-content-between align-items-center" 
                                type="button" data-toggle="collapse" data-target="#detailedBreakdownCollapse" aria-expanded="false">
                            <span class="fw-bold text-dark"><i class="bi bi-list-task me-2"></i> Detailed Fee Breakdown</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse" id="detailedBreakdownCollapse">
                        <div class="card-body pt-0">
                            <!-- Tuition Fees Details -->
                            <div id="tuitionFeesSection" style="display: none;">
                                <div id="tuitionFeesContent" class="mb-4">
                                    <!-- Tuition fees will be dynamically inserted here -->
                                </div>
                            </div>

                            <!-- Other Fees Details -->
                            <div id="otherFeesSection" style="display: none;">
                                <div id="otherFeesContent">
                                    <!-- Other fees will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History (per control number) -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-bottom p-3">
                        <span class="fw-bold text-dark"><i class="bi bi-clock-history me-2"></i> All Transactions by Control Number</span>
                        <small class="text-muted d-block">Toggle each control number to view its payment transactions.</small>
                    </div>
                    <div class="card-body pt-2" id="allTransactionsContent">
                        <!-- Per-CN accordion inserted here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-custom" id="modalExportPdfBtn">
                    <i class="bi bi-file-earmark-pdf"></i> Export Details
                </button>
                <button type="button" class="btn btn-light text-primary-custom border" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1" aria-labelledby="updatePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePaymentModalLabel">
                    <i class="bi bi-arrow-repeat me-2"></i> Update Balances
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updatePaymentForm">
                @csrf
                <input type="hidden" name="paymentID" id="update_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Required</label>
                        <input type="text" id="update_amount_required" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Paid (Current)</label>
                        <input type="text" id="update_amount_paid_current" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount_paid" id="update_amount_paid" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="text-muted">Enter the new payment amount received</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Reference</label>
                        <input type="text" name="payment_reference" id="update_payment_reference" class="form-control" placeholder="e.g., Bank reference number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="update_notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light text-primary-custom border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Update Balances</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Record Payment (student + fee picker) -->
<div class="modal fade" id="quickRecordPaymentModal" tabindex="-1" aria-labelledby="quickRecordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickRecordPaymentModalLabel">
                    <i class="bi bi-lightning-charge me-2"></i> Quick Record Payment
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3" style="border-left:4px solid #940000 !important;">
                    <small class="text-muted">
                        Select a student, then choose the fee type. Next you will enter the amount and payment method.
                        The academic year follows the header selection.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">1. Search / Select Student <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-primary-custom"></i></span>
                        <input type="text" class="form-control" id="quickRecordStudentSearch"
                               placeholder="Type student name or admission number..." autocomplete="off">
                    </div>
                    <div id="quickRecordStudentResults" class="border mt-1 bg-white" style="max-height:220px;overflow-y:auto;display:none;"></div>
                    <div id="quickRecordSelectedStudent" class="mt-2 p-2 border bg-light" style="display:none;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-bold text-primary-custom" id="quickRecordStudentName">-</div>
                                <small class="text-muted" id="quickRecordStudentMeta">-</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="quickRecordClearStudent">Change</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="quickRecordFeeSection" style="display:none;">
                    <label class="form-label fw-bold">2. Select Fee Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="quickRecordFeeSelect">
                        <option value="">- Select fee -</option>
                    </select>
                    <small class="text-muted d-block mt-1" id="quickRecordFeeHint"></small>
                    <div id="quickRecordFeeEmpty" class="alert alert-warning mt-2 mb-0 py-2" style="display:none;">
                        No bill can be recorded for this student (no control number, or the academic year is closed).
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light text-primary-custom border" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="quickRecordContinueBtn" disabled>
                    Continue to record <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel">
                    <i class="bi bi-cash-coin me-2"></i> Record Payment
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="recordPaymentForm">
                @csrf
                <input type="hidden" name="paymentID" id="record_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Control Number</label>
                        <input type="text" class="form-control" id="record_control_number" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Academic Year</label>
                        <input type="text" class="form-control bg-light" id="record_academic_year_label" readonly>
                        <input type="hidden" name="academic_year_id" id="record_academic_year_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Fee to Pay <span class="text-danger">*</span></label>
                        <select name="fee_id" id="record_fee_id" class="form-select" required>
                            <option value="any">Pay to Any Fee (Automatic Priority)</option>
                        </select>
                        <small class="text-muted">Choose a specific fee or pay generally</small>
                    </div>
                    <div id="selected_fee_details" style="display: none;">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Fee Required</label>
                                <input type="text" class="form-control form-control-sm bg-light" id="selected_fee_required" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Fee Balance</label>
                                <input type="text" class="form-control form-control-sm bg-light text-danger fw-bold" id="selected_fee_balance" readonly>
                                <input type="hidden" id="selected_fee_balance_raw">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="overall_payment_details">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Total Required</label>
                                <input type="text" class="form-control form-control-sm bg-light" id="record_amount_required" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Total Balance</label>
                                <input type="text" class="form-control form-control-sm bg-light text-danger fw-bold" id="record_balance" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_source" id="record_payment_source" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                        </select>
                        <small class="text-muted">Select payment method</small>
                    </div>
                    <div class="mb-3" id="record_payment_by_container">
                        <label class="form-label fw-bold">Paid By <span class="text-danger">*</span></label>
                        <select name="payment_by" id="record_payment_by" class="form-select" required>
                            <option value="Parent">Parent / Student</option>
                            <option value="Sponsor">Sponsor</option>
                        </select>
                        <div class="mt-2 p-2 bg-light border rounded" id="payer_balance_detail_section">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><span id="payer_label_name">Parent</span>'s Remaining Debt:</small>
                                <span class="fw-bold text-primary-custom" id="current_payer_balance_val">0.00/=</span>
                                <input type="hidden" id="current_payer_balance_raw">
                            </div>
                        </div>
                        <small class="text-muted">Specify who made this payment</small>
                    </div>
                    <div class="mb-3" id="record_bank_name_container" style="display: none;">
                        <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="record_bank_name" class="form-control" placeholder="e.g., CRDB Bank, NMB Bank">
                        <small class="text-muted">Enter bank name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Paid Amount (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="paid_amount" id="record_paid_amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
                        <small class="text-muted">Enter the amount being paid</small>
                    </div>
                    <div class="mb-3" id="record_reference_number_container">
                        <label class="form-label fw-bold">Reference Number <span class="text-danger" id="record_reference_required">*</span></label>
                        <input type="text" name="reference_number" id="record_reference_number" class="form-control" placeholder="e.g., BANK123456">
                        <small class="text-muted">Unique reference number (required for bank payments)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="record_payment_date" class="form-control" required>
                        <small class="text-muted">Date when payment was made</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="record_notes" class="form-control" rows="3" placeholder="Optional notes (academic year is saved automatically)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light text-primary-custom border" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Records Modal -->
<div class="modal fade" id="viewPaymentRecordsModal" tabindex="-1" aria-labelledby="viewPaymentRecordsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPaymentRecordsModalLabel">
                    <i class="bi bi-eye me-2"></i> Payment Records
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Control Number</label>
                    <input type="text" class="form-control" id="view_records_control_number" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Fee Type</label>
                    <input type="text" class="form-control" id="view_records_fee_type" readonly>
                </div>
                <div id="paymentRecordsContent">
                    <!-- Payment records will be loaded dynamically -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status"></div>
                        <p class="mt-2">Loading payment records...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-custom" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Payment Record Modal -->
<div class="modal fade" id="editPaymentRecordModal" tabindex="-1" aria-labelledby="editPaymentRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentRecordModalLabel">
                    <i class="bi bi-pencil-square me-2"></i> Edit Record
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPaymentRecordForm">
                @csrf
                <input type="hidden" name="recordID" id="edit_record_id">
                <input type="hidden" name="paymentID" id="edit_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Paid (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="paid_amount" id="edit_paid_amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="edit_payment_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_source" id="edit_payment_source" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_reference_number_group" style="display: none;">
                        <label class="form-label fw-bold">Reference Number <span class="text-danger">*</span></label>
                        <input type="text" name="reference_number" id="edit_reference_number" class="form-control">
                    </div>
                    <div class="mb-3" id="edit_bank_name_group" style="display: none;">
                        <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="edit_bank_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Core dependencies wait and load -->
    <!-- ADDING DOWNLOAD PROGRESS MODAL -->
    <div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="bi bi-file-pdf text-danger animate-pulse" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-4" id="pdfProgressTitle" style="color: #940000; font-weight: bold; font-family: 'Century Gothic', sans-serif;">Generatig Payment Report</h5>
                    <div class="progress mb-2" style="height: 12px; border-radius: 0 !important;">
                        <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                            role="progressbar" style="width: 0%; border-radius: 0 !important;"></div>
                    </div>
                    <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                    <p class="mt-3 text-muted small">Please wait while we prepare your official report.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
<script>
(function() {
    var scripts = [
        'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
        'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js'
    ];

    function loadScript(url, callback) {
        var script = document.createElement('script');
        script.src = url;
        script.onload = callback;
        script.onerror = function() {
            console.error('Failed to load script: ' + url);
            // Continue with next script even if one fails
            if (callback) callback();
        };
        document.head.appendChild(script);
    }

    function loadScriptsSequentially(urls, callback) {
        if (urls.length === 0) {
            if (callback) callback();
            return;
        }
        var nextUrl = urls.shift();
        loadScript(nextUrl, function() {
            loadScriptsSequentially(urls, callback);
        });
    }

    function waitForJQuery(callback) {
        if (window.jQuery) {
            callback();
        } else {
            setTimeout(function() { waitForJQuery(callback); }, 100);
        }
    }

    // Show loading state in table immediately
    waitForJQuery(function() {
        var $ = window.jQuery;
        $('#paymentsTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Initializing assets...</p></td></tr>');
        
        loadScriptsSequentially(scripts, function() {
            console.log('All scripts loaded, initializing payments management...');
            
            // Ensure jsPDF mapping
            if (typeof window.jspdf !== 'undefined' && !window.jsPDF) {
                window.jsPDF = window.jspdf.jsPDF;
            }
            
            initPaymentsApp($);
        });
    });

    function initPaymentsApp($) {
        // Function to generate placeholder color
        function getPlaceholderColor(name) {
            if (!name) return '#940000';
            var colors = ['#940000', '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6610f2', '#6c757d', '#1abc9c', '#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#16a085', '#2980b9', '#8e44ad'];
            var hash = 0;
            for (var i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        // Load classes and subclasses on page load
        var classesData = @json($classes ?? []);
        var subclassesData = @json($subclasses ?? []);
        
        // Populate classes dropdown
        if (classesData && classesData.length > 0) {
            classesData.forEach(function(cls) {
                $('#filterClass').append('<option value="' + cls.classID + '">' + cls.class_name + '</option>');
            });
        }
        
        // Update subclasses when class changes
        $('#filterClass').on('change', function() {
            var selectedClassID = $(this).val();
            $('#filterSubclass').html('<option value="">All Subclasses</option>');
            resetFeeFilter();

            if (selectedClassID && subclassesData && subclassesData.length > 0) {
                var filteredSubclasses = subclassesData.filter(function(sub) {
                    return sub.classID == selectedClassID;
                });

                filteredSubclasses.forEach(function(sub) {
                    $('#filterSubclass').append('<option value="' + sub.subclassID + '">' + sub.subclass_name + '</option>');
                });
            }

            loadPaymentsData();
        });

        var currentFeeStructure = { mode: 'none', fees: [] };

        function resetFeeFilter() {
            currentFeeStructure = { mode: 'none', fees: [] };
            $('#filterFee').html('<option value="all">All Fees</option>');
            $('#filterFeeWrap').hide();
        }

        function applyFeeStructure(structure, preferredFeeId) {
            currentFeeStructure = structure || { mode: 'none', fees: [] };
            var mode = currentFeeStructure.mode || 'none';
            var fees = currentFeeStructure.fees || [];
            var $fee = $('#filterFee');
            var previous = preferredFeeId || $fee.val() || 'all';

            if (mode === 'multi' || mode === 'mixed') {
                var html = '<option value="all">All Fees</option>';
                fees.filter(function(f) {
                    return (f.account_mode || 'single') === 'multi' || mode === 'mixed';
                }).forEach(function(f) {
                    html += '<option value="' + f.feeID + '">' + f.fee_name +
                        (f.account_display ? (', ' + f.account_display) : '') + '</option>';
                });
                $fee.html(html);
                if (previous && $fee.find('option[value="' + previous + '"]').length) {
                    $fee.val(previous);
                } else {
                    $fee.val('all');
                }
                $('#filterFeeWrap').show();
            } else if (mode === 'single') {
                $fee.html('<option value="all">Single Account (combined)</option>').val('all');
                $('#filterFeeWrap').hide();
            } else {
                resetFeeFilter();
            }
        }

        // Variable to store the current AJAX request to allow aborting previous ones
        var currentPaymentsAjax = null;
        var currentPaymentsData = [];
        var currentPaymentsClosedYear = false;
        var currentPaymentsEmptyMsg = '';
        var paymentGridFilteredData = [];
        var paymentGridRenderedCount = 0;
        var paymentGridBatchSize = 10;
        var paymentGridObserver = null;
        var paymentGridLoadingBatch = false;
        var paymentGridScrollArmed = false;

        // Header academic year (page reloads when header year changes)
        @php
            $__vpDefaultAyId = $defaultAcademicYearID ?? null;
            $__vpDefaultAyLabel = $defaultYear ?? date('Y');
            foreach (($availableYears ?? []) as $__ay) {
                $__ayArr = is_array($__ay) ? $__ay : (array) $__ay;
                if ((int)($__ayArr['academic_yearID'] ?? 0) === (int)$__vpDefaultAyId) {
                    $__vpDefaultAyLabel = $__ayArr['year_name'] ?? $__ayArr['year'] ?? $__vpDefaultAyLabel;
                    break;
                }
            }
        @endphp
        var headerAcademicYearID = {!! json_encode($__vpDefaultAyId) !!};
        var headerAcademicYearLabel = {!! json_encode($__vpDefaultAyLabel) !!};

        function paymentIsMobile() {
            return window.matchMedia('(max-width: 767.98px)').matches;
        }
        function paymentDefaultView() {
            return paymentIsMobile() ? 'grid' : 'list';
        }
        function paymentPrefKey() {
            return 'payment_view_' + (paymentIsMobile() ? 'mobile' : 'desktop');
        }
        function paymentUserPrefKey() {
            return 'payment_view_user_' + (paymentIsMobile() ? 'mobile' : 'desktop');
        }
        function paymentGetView() {
            try {
                if (localStorage.getItem(paymentUserPrefKey()) === '1') {
                    var saved = localStorage.getItem(paymentPrefKey());
                    if (saved === 'list' || saved === 'grid') return saved;
                }
            } catch (e) {}
            return paymentDefaultView();
        }
        function paymentSetUserViewPref(view) {
            try {
                localStorage.setItem(paymentPrefKey(), view);
                localStorage.setItem(paymentUserPrefKey(), '1');
            } catch (e) {}
        }
        function setPaymentView(view, userChoice) {
            view = (view === 'grid') ? 'grid' : 'list';
            $('#paymentsDataWrapper').attr('data-active-view', view);
            $('.payment-view-toggle [data-view]').removeClass('active');
            $('.payment-view-toggle [data-view="' + view + '"]').addClass('active');
            if (userChoice) paymentSetUserViewPref(view);
            if (view === 'list' && currentPaymentsData.length) {
                initPaymentsDataTable();
            } else if (view === 'grid' && currentPaymentsData.length) {
                paymentGridObserveSentinel();
            }
        }

        function buildPaymentObjectFromMultiAccount(ma) {
            if (!ma) {
                return {
                    paymentID: null,
                    feeID: null,
                    control_number: '',
                    amount_required: 0,
                    amount_paid: 0,
                    balance: 0,
                    fee_payments: [],
                    sponsor_balance: 0,
                    parent_balance: 0,
                    sponsor_amount_required: 0,
                    parent_amount_required: 0
                };
            }
            var feePayments = ma.fee_payments || [];
            if (typeof feePayments === 'string') {
                try { feePayments = JSON.parse(feePayments); } catch (e) { feePayments = []; }
            }
            if (!feePayments.length && ma.feeID) {
                feePayments = [{
                    feeID: ma.feeID,
                    fee_name: ma.fee_name || 'Fee',
                    fee_total_amount: ma.amount_required,
                    amount_paid: ma.amount_paid,
                    balance: ma.balance,
                    is_required: 1
                }];
            }
            return {
                paymentID: ma.paymentID,
                feeID: ma.feeID,
                control_number: ma.control_number,
                amount_required: ma.amount_required,
                amount_paid: ma.amount_paid,
                balance: ma.balance,
                fee_payments: feePayments,
                sponsor_balance: ma.sponsor_balance || 0,
                parent_balance: ma.parent_balance || 0,
                sponsor_amount_required: ma.sponsor_amount_required || 0,
                parent_amount_required: ma.parent_amount_required || 0
            };
        }

        function findMultiAccountByPaymentId(multiAccounts, paymentId) {
            if (!multiAccounts || !multiAccounts.length || paymentId == null || paymentId === '') {
                return null;
            }
            return multiAccounts.find(function(ma) {
                return ma && String(ma.paymentID) === String(paymentId);
            }) || null;
        }

        function getViewMoreMultiAccounts() {
            return $('#viewMoreModal').data('multiAccounts')
                || $('#viewMoreModal').data('multi-accounts')
                || [];
        }

        function isRecordPaymentMultiMode(btn) {
            return String(btn.attr('data-multi-mode') || btn.data('multiMode') || '') === '1';
        }

        function parseRecordPaymentData(btn) {
            var payment = {};
            try {
                var pData = btn.data('payment');
                if (typeof pData === 'string' && pData.trim() !== '') {
                    payment = JSON.parse(pData);
                } else if (pData && typeof pData === 'object') {
                    payment = pData;
                }
            } catch (e) {
                console.error('parseRecordPaymentData error:', e);
            }
            if (!payment || typeof payment !== 'object') {
                payment = {};
            }
            return payment;
        }

        function normalizeFeePaymentsFromMulti(ma) {
            if (!ma) return [];
            var fps = ma.fee_payments || [];
            if (typeof fps === 'string') {
                try { fps = JSON.parse(fps); } catch (e) { fps = []; }
            }
            if (!fps.length && ma.feeID) {
                fps = [{
                    feeID: ma.feeID,
                    fee_name: ma.fee_name || 'Fee',
                    fee_total_amount: ma.amount_required,
                    amount_paid: ma.amount_paid,
                    balance: ma.balance,
                    is_required: 1
                }];
            }
            return fps;
        }

        function populateRecordPaymentFeeSelect(payment, multiAccounts, options) {
            options = options || {};
            var feeSelect = $('#record_fee_id');
            var isMulti = multiAccounts && multiAccounts.length > 0;

            feeSelect.find('option').remove();

            if (isMulti) {
                feeSelect.append('<option value="" disabled selected>Select fee...</option>');
                multiAccounts.forEach(function(ma) {
                    if (!ma) return;
                    normalizeFeePaymentsFromMulti(ma).forEach(function(fp) {
                        var feeId = fp.feeID || ma.feeID;
                        var feeName = fp.fee_name || ma.fee_name || 'Fee';
                        var bal = parseFloat(fp.balance != null ? fp.balance : (ma.balance || 0));
                        var req = parseFloat(fp.fee_total_amount != null ? fp.fee_total_amount : (ma.amount_required || 0));
                        $('<option></option>')
                            .attr('value', feeId)
                            .attr('data-payment-id', ma.paymentID)
                            .attr('data-control-number', ma.control_number || '')
                            .attr('data-required', req)
                            .attr('data-balance', bal)
                            .attr('data-sponsor-balance', ma.sponsor_balance || 0)
                            .attr('data-parent-balance', ma.parent_balance || 0)
                            .text(feeName + ' · Control Number ' + (ma.control_number || '-') + ' (Bal: ' + bal.toLocaleString() + '/=)')
                            .appendTo(feeSelect);
                    });
                });
                feeSelect.prop('required', true);
                if (options.preselectFeeId) {
                    feeSelect.val(String(options.preselectFeeId));
                    feeSelect.find('option[value=""]').remove();
                }
                feeSelect.trigger('change');
            } else {
                feeSelect.append('<option value="any">Pay to any fee (Automatic Priority)</option>');
                if (payment && payment.fee_payments) {
                    var fps = payment.fee_payments;
                    if (typeof fps === 'string') {
                        try { fps = JSON.parse(fps); } catch (e) { fps = []; }
                    }
                    fps.forEach(function(fp) {
                        var optionText = fp.fee_name + ' (Bal: ' + parseFloat(fp.balance || 0).toLocaleString() + '/=)';
                        $('<option></option>')
                            .attr('value', fp.feeID)
                            .attr('data-required', fp.fee_total_amount)
                            .attr('data-balance', fp.balance)
                            .text(optionText)
                            .appendTo(feeSelect);
                    });
                }
                feeSelect.prop('required', true);
                feeSelect.val(options.preselectFeeId || 'any').trigger('change');
            }
        }

        function applyRecordPaymentFeeSelection(selectedOption) {
            if (!selectedOption.length) return;
            var paymentId = selectedOption.data('payment-id');
            if (paymentId) {
                $('#record_payment_id').val(paymentId);
                $('#record_control_number').val(selectedOption.data('control-number') || '');
                $('#record_amount_required').val(parseFloat(selectedOption.data('required') || 0).toLocaleString() + '/=');
                $('#record_balance').val(parseFloat(selectedOption.data('balance') || 0).toLocaleString() + '/=');
                $('#recordPaymentModal').data('sponsor-balance', parseFloat(selectedOption.data('sponsor-balance') || 0));
                $('#recordPaymentModal').data('parent-balance', parseFloat(selectedOption.data('parent-balance') || 0));
            }
        }

        function buildViewMoreBtnAttrs(item, isClosedYear, cssClass) {
            var student = item.student || {};
            var paymentsData = item.payment || {};
            var totals = item.totals || {};
            var firstName = student.first_name || '';
            var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';
            var fullName = ((student.first_name || '') + ' ' + (student.last_name || '')).trim();
            var placeholderColor = getPlaceholderColor(fullName);
            var studentFullName = ((student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '')).replace(/\s+/g, ' ').trim();
            var studentClass = 'N/A';
            if (student.subclass) {
                var mainClass = student.subclass.class_name || '';
                var subclass = student.subclass.subclass_name || '';
                studentClass = (mainClass && subclass) ? mainClass + ' ' + subclass : (subclass || mainClass || 'N/A');
            }
            var enrStatus = student.enrollment_status || '';
            var promoStatus = student.promotion_status || '';
            var promoLabel = student.promotion_status_label || promoStatus;
            var priorOwed = parseFloat(item.prior_years_owed || totals.prior_years_owed || 0);
            var paymentsJson = JSON.stringify(paymentsData).replace(/'/g, "&#39;");
            var sponsorJson = student.sponsor ? JSON.stringify(student.sponsor).replace(/'/g, "&#39;") : '';
            var totalsJson = JSON.stringify(totals).replace(/'/g, "&#39;");
            var multiJson = JSON.stringify(item.multi_accounts || []).replace(/'/g, "&#39;");

            return 'class="' + (cssClass || 'btn btn-primary-custom btn-sm view-more-btn') + '" ' +
                'data-student-id="' + (student.studentID || '') + '" ' +
                'data-student-name="' + studentFullName + '" ' +
                'data-student-admission="' + (student.admission_number || 'N/A') + '" ' +
                'data-student-class="' + studentClass + '" ' +
                'data-student-photo="' + (student.photo || '') + '" ' +
                'data-student-first-letter="' + firstLetter + '" ' +
                'data-student-placeholder-color="' + placeholderColor + '" ' +
                'data-student-sponsor=\'' + sponsorJson + '\' ' +
                'data-is-closed-year="' + (isClosedYear ? 'true' : 'false') + '" ' +
                'data-academic-year="' + (item.academic_year || '') + '" ' +
                'data-academic-year-id="' + (item.academic_year_id || '') + '" ' +
                'data-enrollment-status="' + (enrStatus || '') + '" ' +
                'data-promotion-status="' + (promoLabel || promoStatus || '') + '" ' +
                'data-prior-years-owed="' + priorOwed + '" ' +
                'data-payment=\'' + paymentsJson + '\' ' +
                'data-totals=\'' + totalsJson + '\' ' +
                'data-multi-accounts=\'' + multiJson + '\' ' +
                'title="View More Details"';
        }

        function buildPaymentActionsDropdown(item, isClosedYear) {
            var paymentsData = item.payment || {};
            var paymentID = paymentsData.paymentID || '';
            if (item.focus_fee && item.focus_fee.paymentID) {
                paymentID = item.focus_fee.paymentID;
            } else if (!paymentID && item.multi_accounts && item.multi_accounts.length === 1) {
                paymentID = item.multi_accounts[0].paymentID || '';
            }

            var items = '<button type="button" ' + buildViewMoreBtnAttrs(item, isClosedYear, 'dropdown-item view-more-btn') + '>' +
                '<i class="bi bi-eye"></i> View Details</button>';

            if (paymentID) {
                items += '<button type="button" class="dropdown-item resend-control-sms-btn" data-payment-id="' + paymentID + '">' +
                    '<i class="bi bi-send"></i> Send CN SMS</button>';
            }

            return '<div class="dropdown payment-action-dropdown">' +
                    '<button class="btn btn-sm payment-action-toggle" type="button" title="Actions" aria-haspopup="true" aria-expanded="false">' +
                        '<i class="bi bi-three-dots-vertical"></i>' +
                    '</button>' +
                    '<div class="dropdown-menu dropdown-menu-right shadow-sm">' + items + '</div>' +
                '</div>';
        }

        function buildPaymentsListRow(item, isClosedYear) {
            var student = item.student || {};
            var totals = item.totals || {};
            var firstName = student.first_name || '';
            var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';
            var fullName = ((student.first_name || '') + ' ' + (student.last_name || '')).trim();
            var placeholderColor = getPlaceholderColor(fullName);
            var photoHtml = '';
            if (student.photo) {
                photoHtml = '<div class="photo-container me-2">' +
                    '<img src="' + student.photo + '" alt="Photo" class="student-photo" ' +
                    'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                    '<div class="student-photo-placeholder d-none" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                    '</div>';
            } else {
                photoHtml = '<div class="photo-container me-2">' +
                    '<div class="student-photo-placeholder" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                    '</div>';
            }

            var studentFullName = ((student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '')).replace(/\s+/g, ' ').trim();
            var studentClass = 'N/A';
            if (student.subclass) {
                var mainClass = student.subclass.class_name || '';
                var subclass = student.subclass.subclass_name || '';
                studentClass = (mainClass && subclass) ? mainClass + ' ' + subclass : (subclass || mainClass || 'N/A');
            }
            var enrStatus = student.enrollment_status || '';
            var enrBadgeClass = 'bg-secondary';
            if (enrStatus === 'Active') enrBadgeClass = 'bg-success';
            else if (enrStatus === 'Completed') enrBadgeClass = 'bg-primary';
            else if (enrStatus === 'Transferred') enrBadgeClass = 'bg-info text-dark';
            else if (enrStatus === 'Dropped' || enrStatus === 'Cancelled') enrBadgeClass = 'bg-warning text-dark';
            var enrBadge = enrStatus
                ? '<span class="badge ' + enrBadgeClass + ' ms-1" style="font-size:0.65rem;">' + enrStatus + '</span>'
                : '';

            var promoStatus = student.promotion_status || '';
            var promoLabel = student.promotion_status_label || promoStatus;
            var promoBadgeClass = 'bg-light text-dark border';
            if (promoStatus === 'Promoted' || promoStatus === 'Graduated') promoBadgeClass = 'bg-success';
            else if (promoStatus === 'Repeated') promoBadgeClass = 'bg-warning text-dark';
            else if (promoStatus === 'Transferred') promoBadgeClass = 'bg-info text-dark';
            else if (promoStatus === 'Truancy' || promoStatus === 'Dropout') promoBadgeClass = 'bg-danger';
            else if (promoStatus === 'Pending') promoBadgeClass = 'bg-secondary';
            var promoBadge = promoStatus
                ? '<span class="badge ' + promoBadgeClass + ' ms-1" style="font-size:0.65rem;" title="Promotion status">' + promoLabel + '</span>'
                : '';

            var col1 = '<div class="d-flex align-items-center">' +
                photoHtml +
                '<div>' +
                '<div class="fw-bold text-dark">' + studentFullName + enrBadge + promoBadge + '</div>' +
                '<div class="small text-muted">' + (student.admission_number || 'No Adm') + ' | ' + studentClass + '</div>' +
                '<div class="small badge bg-light text-dark border">' + (item.academic_year || headerAcademicYearLabel || '') + '</div>' +
                '</div></div>';

            var colType = student.sponsor
                ? '<div class="text-center"><span class="badge bg-primary-custom text-white mb-1" style="font-size: 0.7rem; padding: 4px 8px; display: inline-block; width: 100px;"><i class="bi bi-shield-check me-1"></i>Sponsored</span><br><small class="text-muted">' + student.sponsor.percentage + '% Cover</small><br><small class="text-muted" style="font-size: 0.65rem;">' + student.sponsor.sponsor_name + '</small></div>'
                : '<div class="text-center"><span class="badge bg-secondary text-white" style="font-size: 0.7rem; padding: 4px 8px; display: inline-block; width: 100px;"><i class="bi bi-person me-1"></i>Own Payment</span></div>';

            var col2 = '<div class="bill-container">';
            if (item.focus_fee) {
                col2 += '<div class="bill-item text-muted small mb-1"><span class="badge" style="background:#fff5f5;color:#940000;border:1px solid #940000;">' +
                    (item.focus_fee.fee_name || 'Fee') + '</span></div>';
                if (item.focus_fee.control_number) {
                    col2 += '<div class="bill-item text-muted small">CN: <span class="fw-bold text-dark">' + item.focus_fee.control_number + '</span></div>';
                }
            } else if (item.multi_accounts && item.multi_accounts.length) {
                col2 += '<div class="bill-item text-muted small mb-1">' + item.multi_accounts.length + ' fee account(s)</div>';
            }
            col2 += '<div class="bill-item text-muted small">Required: <span class="fw-bold text-dark">' + parseFloat(totals.total_required || 0).toLocaleString() + '</span></div>' +
                '<div class="bill-item text-muted small">Paid: <span class="fw-bold text-success">' + parseFloat(totals.total_paid || 0).toLocaleString() + '</span></div>' +
                '</div>';

            var overallStatus = totals.overall_status || 'Pending';
            var statusClass = 'bg-secondary';
            if (overallStatus === 'Paid') statusClass = 'bg-success';
            else if (overallStatus === 'Overpaid') statusClass = 'bg-info';
            else if ((overallStatus + '').includes('Incomplete') || overallStatus === 'Partial') statusClass = 'bg-warning text-dark';

            var col3 = '<div class="text-end">' +
                '<div class="fw-bold text-danger mb-1">' + parseFloat(totals.total_balance || 0).toLocaleString() + '/=</div>' +
                '<span class="badge ' + statusClass + ' mb-1">' + overallStatus + '</span>';
            var priorOwed = parseFloat(item.prior_years_owed || totals.prior_years_owed || 0);
            if (priorOwed > 0) {
                col3 += '<div class="small text-warning mt-1" title="Outstanding from previous enrollments/years"><i class="bi bi-exclamation-triangle"></i> Prev years: ' + priorOwed.toLocaleString() + '/=</div>';
            }
            var deadlineRequired = parseFloat(totals.required_amount || 0);
            var deadlinePaid = parseFloat(totals.required_paid || 0);
            var deadlineMet = !!totals.deadline_met;
            if (item.focus_fee) {
                deadlineRequired = parseFloat(item.focus_fee.deadline_required || 0);
                deadlinePaid = parseFloat(item.focus_fee.deadline_paid || 0);
                deadlineMet = !!item.focus_fee.deadline_met;
            }
            if (deadlineRequired > 0) {
                deadlineMet = deadlinePaid + 0.00001 >= deadlineRequired;
            } else {
                deadlineMet = true;
            }
            if (deadlineRequired > 0 && !deadlineMet) {
                col3 += '<div class="small mt-1 text-danger" title="Deadline reached, not yet paid">' +
                    '<i class="bi bi-clock-history"></i> Deadline: ' +
                    deadlinePaid.toLocaleString() + ' / ' + deadlineRequired.toLocaleString() + '</div>';
            }
            if (totals.can_start_school) {
                col3 += ' <i class="bi bi-check-circle-fill text-success" title="Can Start School"></i>';
            }
            col3 += '</div>';

            var col4 = buildPaymentActionsDropdown(item, isClosedYear);

            var deadlineOverdue = item.focus_fee
                ? !!item.focus_fee.deadline_overdue
                : !!totals.deadline_overdue;
            if (!deadlineOverdue && deadlineRequired > 0 && !deadlineMet) {
                deadlineOverdue = true;
            }
            var rowClass = deadlineOverdue ? ' class="payment-deadline-overdue"' : '';

            return '<tr' + rowClass + '>' +
                '<td data-label="Student">' + col1 + '</td>' +
                '<td data-label="Payment Type" class="text-end">' + colType + '</td>' +
                '<td data-label="Bill Summary" class="text-end">' + col2 + '</td>' +
                '<td data-label="Status" class="text-end">' + col3 + '</td>' +
                '<td data-label="Actions" class="text-center payment-actions-cell">' + col4 + '</td>' +
                '</tr>';
        }

        function buildPaymentsGridCard(item, isClosedYear) {
            var student = item.student || {};
            var totals = item.totals || {};
            var firstName = student.first_name || '';
            var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';
            var fullName = ((student.first_name || '') + ' ' + (student.last_name || '')).trim();
            var placeholderColor = getPlaceholderColor(fullName);
            var studentFullName = ((student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '')).replace(/\s+/g, ' ').trim();
            var studentClass = 'N/A';
            if (student.subclass) {
                var mainClass = student.subclass.class_name || '';
                var subclass = student.subclass.subclass_name || '';
                studentClass = (mainClass && subclass) ? mainClass + ' ' + subclass : (subclass || mainClass || 'N/A');
            }
            var enrStatus = student.enrollment_status || '';
            var promoLabel = student.promotion_status_label || student.promotion_status || '';
            var overallStatus = totals.overall_status || 'Pending';
            var priorOwed = parseFloat(item.prior_years_owed || totals.prior_years_owed || 0);

            var photoBlock = student.photo
                ? '<img src="' + student.photo + '" alt="" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                  '<div class="payment-grid-photo-placeholder d-none" style="background-color:' + placeholderColor + ';display:none;">' + firstLetter + '</div>'
                : '<div class="payment-grid-photo-placeholder" style="background-color:' + placeholderColor + ';">' + firstLetter + '</div>';

            var badges = '';
            if (enrStatus) badges += '<span class="badge bg-light text-dark border me-1" style="font-size:0.65rem;">' + enrStatus + '</span>';
            if (promoLabel) badges += '<span class="badge bg-secondary me-1" style="font-size:0.65rem;">' + promoLabel + '</span>';
            if (student.sponsor) badges += '<span class="badge bg-primary-custom text-white" style="font-size:0.65rem;">Sponsored</span>';

            var deadlineOverdue = item.focus_fee
                ? !!item.focus_fee.deadline_overdue
                : !!totals.deadline_overdue;
            var drCheck = item.focus_fee ? parseFloat(item.focus_fee.deadline_required || 0) : parseFloat(totals.required_amount || 0);
            var dpCheck = item.focus_fee ? parseFloat(item.focus_fee.deadline_paid || 0) : parseFloat(totals.required_paid || 0);
            var metCheck = drCheck > 0 ? (dpCheck + 0.00001 >= drCheck) : true;
            if (!deadlineOverdue && drCheck > 0 && !metCheck) {
                deadlineOverdue = true;
            }
            var widgetOverdueClass = deadlineOverdue ? ' payment-deadline-overdue' : '';

            return '<div class="col-12 col-sm-6 col-xl-4">' +
                '<div class="payment-student-widget' + widgetOverdueClass + '">' +
                    '<div class="payment-student-widget-header">' +
                        '<div class="payment-grid-actions-top">' + buildPaymentActionsDropdown(item, isClosedYear) + '</div>' +
                        photoBlock +
                    '</div>' +
                    '<div class="payment-student-widget-body">' +
                        '<h3>' + studentFullName + '</h3>' +
                        '<div class="pay-class">' + studentClass + '</div>' +
                        '<div class="mb-2">' + badges + '</div>' +
                        '<div class="pay-meta"><strong>Adm:</strong> ' + (student.admission_number || 'N/A') + '</div>' +
                        '<div class="pay-meta"><strong>Year:</strong> ' + (item.academic_year || headerAcademicYearLabel || '-') + '</div>' +
                        '<div class="pay-amounts">' +
                            (item.focus_fee ? ('<div class="small mb-1"><span class="badge" style="background:#fff5f5;color:#940000;border:1px solid #940000;">' + (item.focus_fee.fee_name || 'Fee') + '</span></div>') : '') +
                            (item.focus_fee && item.focus_fee.control_number ? ('<div class="pay-meta mb-1"><strong>CN:</strong> ' + item.focus_fee.control_number + '</div>') : '') +
                            '<div class="d-flex justify-content-between"><span>Required</span><strong>' + parseFloat(totals.total_required || 0).toLocaleString() + '/=</strong></div>' +
                            '<div class="d-flex justify-content-between"><span>Paid</span><strong class="text-success">' + parseFloat(totals.total_paid || 0).toLocaleString() + '/=</strong></div>' +
                            '<div class="d-flex justify-content-between"><span>Balance</span><strong class="text-danger">' + parseFloat(totals.total_balance || 0).toLocaleString() + '/=</strong></div>' +
                            (function() {
                                if (drCheck <= 0 || metCheck) return '';
                                return '<div class="small mt-1 text-danger"><i class="bi bi-clock-history"></i> Deadline: ' +
                                    dpCheck.toLocaleString() + ' / ' + drCheck.toLocaleString() + '</div>';
                            })() +
                            (priorOwed > 0 ? '<div class="small text-warning mt-1"><i class="bi bi-exclamation-triangle"></i> Prev years: ' + priorOwed.toLocaleString() + '/=</div>' : '') +
                            '<div class="mt-1"><span class="badge bg-secondary">' + overallStatus + '</span></div>' +
                        '</div>' +
                        '<div class="payment-student-widget-actions">' +
                            '<button ' + buildViewMoreBtnAttrs(item, isClosedYear) + ' style="width:100%;"><i class="bi bi-eye"></i> Details</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function initPaymentsDataTable() {
            if (!$.fn.DataTable) return;
            if ($.fn.DataTable.isDataTable('#paymentsTable')) {
                $('#paymentsTable').DataTable().destroy();
            }
            var table = $('#paymentsTable').DataTable({
                destroy: true,
                dom: 'Bfrtip',
                deferRender: true,
                processing: true,
                buttons: [
                    'copy', 'csv', 'excel',
                    {
                        text: 'PDF',
                        action: function () {
                            $('#exportFilteredPdfBtn').trigger('click');
                        }
                    },
                    'print'
                ],
                pageLength: 10,
                displayStart: 0,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[0, 'asc']],
                responsive: true,
                scrollX: true,
                autoWidth: false,
                columnDefs: [
                    { width: "30%", targets: 0 },
                    { width: "15%", targets: 1 },
                    { width: "20%", targets: 2 },
                    { width: "20%", targets: 3 },
                    { width: "15%", targets: 4, orderable: false }
                ]
            });
            $('.column-filter').off('keyup change').on('keyup change', function() {
                var columnIndex = $(this).data('column');
                table.column(columnIndex).search($(this).val()).draw();
            });
        }

        function paymentGridUpdateCount() {
            var total = paymentGridFilteredData.length;
            var shown = Math.min(paymentGridRenderedCount, total);
            if (!total) {
                $('#paymentsGridCount').addClass('d-none').text('');
                return;
            }
            $('#paymentsGridCount')
                .removeClass('d-none')
                .text('Showing ' + shown + ' of ' + total);
        }

        function paymentGridAppendNextBatch() {
            if (paymentGridLoadingBatch || paymentGridRenderedCount >= paymentGridFilteredData.length) {
                paymentGridUpdateCount();
                return;
            }

            paymentGridLoadingBatch = true;
            $('#paymentsGridLoadMore').removeClass('d-none');

            window.requestAnimationFrame(function() {
                var end = Math.min(
                    paymentGridRenderedCount + paymentGridBatchSize,
                    paymentGridFilteredData.length
                );
                var html = '';
                for (var i = paymentGridRenderedCount; i < end; i++) {
                    html += buildPaymentsGridCard(
                        paymentGridFilteredData[i],
                        currentPaymentsClosedYear
                    );
                }
                $('#paymentsWidgetGrid').append(html);
                paymentGridRenderedCount = end;
                paymentGridLoadingBatch = false;
                $('#paymentsGridLoadMore').addClass('d-none');
                paymentGridUpdateCount();
            });
        }

        function paymentGridObserveSentinel() {
            if (paymentGridObserver) {
                paymentGridObserver.disconnect();
            }
            if (!('IntersectionObserver' in window)) return;

            paymentGridObserver = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting &&
                    paymentGridScrollArmed &&
                    ($('#paymentsDataWrapper').attr('data-active-view') || 'list') === 'grid') {
                    paymentGridScrollArmed = false;
                    paymentGridAppendNextBatch();
                }
            }, {
                root: null,
                rootMargin: '250px 0px',
                threshold: 0
            });
            var sentinel = document.getElementById('paymentsGridSentinel');
            if (sentinel) paymentGridObserver.observe(sentinel);
        }

        function renderPaymentsGrid(items) {
            var $grid = $('#paymentsWidgetGrid');
            paymentGridRenderedCount = 0;
            paymentGridFilteredData = [];
            paymentGridLoadingBatch = false;
            paymentGridScrollArmed = false;
            $('#paymentsGridLoadMore, #paymentsGridCount').addClass('d-none');

            if (!items || !items.length) {
                $grid.html('<div class="col-12"><div class="payment-grid-empty">' + (currentPaymentsEmptyMsg || 'No payments found') + '</div></div>');
                return;
            }
            var q = (($('#paymentGridSearch').val() || '') + '').toLowerCase().trim();
            var filtered = items;
            if (q) {
                filtered = items.filter(function(item) {
                    var s = item.student || {};
                    var hay = [
                        s.first_name, s.middle_name, s.last_name, s.admission_number,
                        (s.subclass && s.subclass.class_name) || '',
                        (s.subclass && s.subclass.subclass_name) || '',
                        item.academic_year || ''
                    ].join(' ').toLowerCase();
                    return hay.indexOf(q) !== -1;
                });
            }
            if (!filtered.length) {
                $grid.html('<div class="col-12"><div class="payment-grid-empty">No students match your search.</div></div>');
                return;
            }

            paymentGridFilteredData = filtered;
            $grid.empty();
            paymentGridAppendNextBatch();
            paymentGridObserveSentinel();
        }

        function renderPaymentsUI(data, isClosedYear, emptyMsg) {
            currentPaymentsData = data || [];
            currentPaymentsClosedYear = !!isClosedYear;
            currentPaymentsEmptyMsg = emptyMsg || 'No payments found';

            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#paymentsTable')) {
                $('#paymentsTable').DataTable().destroy();
            }

            if (!currentPaymentsData.length) {
                $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted"><p class="mt-2">' + currentPaymentsEmptyMsg + '</p></td></tr>');
                $('#paymentsWidgetGrid').html('<div class="col-12"><div class="payment-grid-empty">' + currentPaymentsEmptyMsg + '</div></div>');
                return;
            }

            var listHtml = '';
            currentPaymentsData.forEach(function(item) {
                listHtml += buildPaymentsListRow(item, currentPaymentsClosedYear);
            });
            $('#paymentsTableBody').html(listHtml);
            renderPaymentsGrid(currentPaymentsData);

            if (($('#paymentsDataWrapper').attr('data-active-view') || 'list') === 'list') {
                initPaymentsDataTable();
            }
        }

        // Function to load payments data via AJAX
        function loadPaymentsData() {
            if (currentPaymentsAjax) {
                currentPaymentsAjax.abort();
            }

            var classID = $('#filterClass').val();
            var subclassID = $('#filterSubclass').val();
            var enrollmentStatus = $('#filterStatus').val() || 'Active';
            var promotionStatus = $('#filterPromotion').val() || '';
            var paymentStatus = $('#filterPaymentStatus').val();
            var deadlineFilter = $('#filterDeadline').val() || '';
            var searchStudentName = $('#searchStudentName').val();
            var feeID = $('#filterFee').val() || 'all';

            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#paymentsTable')) {
                $('#paymentsTable').DataTable().destroy();
            }

            $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payments...</p></td></tr>');
            $('#paymentsWidgetGrid').html('<div class="col-12"><div class="payment-grid-empty"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2 mb-0">Loading...</p></div></div>');

            currentPaymentsAjax = $.ajax({
                url: '{{ route("get_payments_ajax") }}',
                type: 'GET',
                data: {
                    class_id: classID,
                    subclass_id: subclassID,
                    enrollment_status: enrollmentStatus,
                    student_status: enrollmentStatus,
                    promotion_status: promotionStatus,
                    payment_status: paymentStatus,
                    deadline_filter: deadlineFilter,
                    search_student_name: searchStudentName,
                    sponsorship_filter: $('#filterSponsorship').val(),
                    fee_id: feeID
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        if (response.academic_year) headerAcademicYearLabel = response.academic_year;
                        if (response.academic_year_id) headerAcademicYearID = response.academic_year_id;
                        if (response.fee_structure) {
                            applyFeeStructure(response.fee_structure, feeID);
                        } else if (!classID) {
                            resetFeeFilter();
                        }

                        var emptyMsg = response.error_code === 'no_enrollments'
                            ? (response.message || 'No enrollments found for the header academic year / selected filters.')
                            : 'No payments found';

                        renderPaymentsUI(response.data, response.is_closed_year || false, emptyMsg);

                        if (response.statistics) {
                            var stats = response.statistics;
                            $('#statPendingPayments').text(stats.pending_payments || 0);
                            $('#statIncompletePayments').text(stats.incomplete_payments || 0);
                            $('#statPaidPayments').text(stats.paid_payments || 0);
                            $('#statOverpaidPayments').text(stats.overpaid_payments || 0);
                            $('#statTotalBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            $('#statTotalRequired').text('TZS ' + parseFloat(stats.total_required || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            $('#statTotalPaid').text('TZS ' + parseFloat(stats.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            $('#statOutstandingBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                        }
                    } else {
                        renderPaymentsUI([], false, 'No payments found');
                    }
                },
                error: function(xhr, status) {
                    if (status === 'abort') return;
                    console.error('Error loading payments:', xhr);
                    $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger">Error loading payments. Please try again.</td></tr>');
                    $('#paymentsWidgetGrid').html('<div class="col-12"><div class="payment-grid-empty text-danger">Error loading payments.</div></div>');
                }
            });
        }

        // View toggle: list / grid (mobile default = grid, desktop default = list)
        $(document).on('click', '.payment-view-toggle [data-view]', function() {
            setPaymentView($(this).data('view'), true);
        });

        // Actions dropdown toggle (three dots), same behavior as manage_student
        $(document).on('click', '.payment-action-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $dropdown = $(this).closest('.payment-action-dropdown');
            const wasOpen = $dropdown.hasClass('show');
            $('.payment-action-dropdown.show').removeClass('show')
                .find('.payment-action-toggle').attr('aria-expanded', 'false');
            if (!wasOpen) {
                $dropdown.addClass('show');
                $(this).attr('aria-expanded', 'true');
            }
        });
        $(document).on('click', function() {
            $('.payment-action-dropdown.show').removeClass('show')
                .find('.payment-action-toggle').attr('aria-expanded', 'false');
        });
        $(document).on('click', '.payment-action-dropdown .dropdown-menu', function(e) {
            e.stopPropagation();
        });
        $(document).on('click', '.payment-action-dropdown .dropdown-item', function() {
            $(this).closest('.payment-action-dropdown').removeClass('show')
                .find('.payment-action-toggle').attr('aria-expanded', 'false');
        });
        var paymentGridSearchTimeout;
        $('#paymentGridSearch').on('keyup search', function() {
            clearTimeout(paymentGridSearchTimeout);
            paymentGridSearchTimeout = setTimeout(function() {
                renderPaymentsGrid(currentPaymentsData);
            }, 250);
        });
        $(window).on('wheel touchmove keydown', function(e) {
            if (($('#paymentsDataWrapper').attr('data-active-view') || 'list') !== 'grid') return;
            if (e.type === 'keydown' && !['ArrowDown', 'PageDown', 'End', ' '].includes(e.key)) return;
            paymentGridScrollArmed = true;
        });
        $(window).on('scroll', function() {
            if (!paymentGridScrollArmed ||
                ($('#paymentsDataWrapper').attr('data-active-view') || 'list') !== 'grid') return;
            var sentinel = document.getElementById('paymentsGridSentinel');
            if (sentinel && sentinel.getBoundingClientRect().top <= window.innerHeight + 250) {
                paymentGridScrollArmed = false;
                paymentGridAppendNextBatch();
            }
        });
        $(window).on('resize', function() {
            try {
                if (localStorage.getItem(paymentUserPrefKey()) !== '1') {
                    setPaymentView(paymentDefaultView(), false);
                }
            } catch (e) {}
        });
        setPaymentView(paymentGetView(), false);

        // Real-time filtering
        $('#filterClass, #filterSubclass, #filterStatus, #filterPromotion, #filterPaymentStatus, #filterDeadline, #filterSponsorship, #filterFee').on('change', function() {
            if ($(this).attr('id') === 'filterClass') return; // handled above (includes load)
            loadPaymentsData();
        });

        // Search student name with debounce
        var searchTimeout;
        $('#searchStudentName').on('keyup', function() {
            clearTimeout(searchTimeout);
            var searchValue = $(this).val();
            searchTimeout = setTimeout(function() {
                loadPaymentsData();
            }, 500); // Wait 500ms after user stops typing
        });

        // Clear all filters
        $('#clearFiltersBtn').on('click', function() {
            $('#filterClass').val('');
            $('#filterSubclass').html('<option value="">All Subclasses</option>');
            resetFeeFilter();
            $('#filterStatus').val('Active');
            $('#filterPromotion').val('');
            $('#filterPaymentStatus').val('');
            $('#filterDeadline').val('');
            $('#filterSponsorship').val('');
            $('#searchStudentName').val('');
            loadPaymentsData();
        });
        
        // Export filtered PDF (Standardized AJAX Method)
        $('#exportFilteredPdfBtn').on('click', function() {
            const class_id = $('#filterClass').val();
            const subclass_id = $('#filterSubclass').val();
            const enrollment_status = $('#filterStatus').val() || 'Active';
            const promotion_status = $('#filterPromotion').val() || '';
            const payment_status = $('#filterPaymentStatus').val();
            const deadline_filter = $('#filterDeadline').val() || '';
            const search_student_name = $('#searchStudentName').val();
            const sponsorship_filter = $('#filterSponsorship').val() || '';
            const fee_id = $('#filterFee').val() || 'all';

            let url = '{{ route("admin.export_filtered_payments_pdf") }}';
            url += `?class_id=${encodeURIComponent(class_id || '')}` +
                `&subclass_id=${encodeURIComponent(subclass_id || '')}` +
                `&enrollment_status=${encodeURIComponent(enrollment_status)}` +
                `&student_status=${encodeURIComponent(enrollment_status)}` +
                `&promotion_status=${encodeURIComponent(promotion_status)}` +
                `&payment_status=${encodeURIComponent(payment_status || '')}` +
                `&deadline_filter=${encodeURIComponent(deadline_filter)}` +
                `&search_student_name=${encodeURIComponent(search_student_name || '')}` +
                `&sponsorship_filter=${encodeURIComponent(sponsorship_filter)}` +
                `&fee_id=${encodeURIComponent(fee_id)}` +
                `&academic_year_id=${encodeURIComponent(headerAcademicYearID || '')}` +
                `&use_request_year=${headerAcademicYearID ? 1 : 0}`;

            $('#pdfProgressTitle').text('Generating Payments Report');
            $('#downloadProgressModal').modal('show');
            $('#pdfProgressBar').css('width', '0%');
            $('#pdfProgressPercent').text('0%');

            let progress = 0;
            const interval = setInterval(() => {
                if (progress < 95) {
                    let inc = 2 + Math.random() * 5;
                    progress += inc;
                    if (progress > 95) progress = 95;
                    $('#pdfProgressBar').css('width', progress + '%');
                    $('#pdfProgressPercent').text(Math.round(progress) + '%');
                }
            }, 400);

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Download failed');
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(interval);
                    $('#pdfProgressBar').css('width', '100%');
                    $('#pdfProgressPercent').text('100%');
                    
                    setTimeout(() => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        a.download = `Payments_Report_${new Date().toISOString().split('T')[0]}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                        
                        setTimeout(() => {
                            $('#downloadProgressModal').modal('hide');
                        }, 500);
                    }, 400);
                })
                .catch(err => {
                    clearInterval(interval);
                    $('#downloadProgressModal').modal('hide');
                    Swal.fire('Error', 'PDF generation failed. Please try again.', 'error');
                });
        });

        // Load initial data
        loadPaymentsData();

        // ====================================================================
        // FEE SMS MODAL, shared for "Send Control Numbers" + "Debt Reminders"
        // Same UX as result_management.blade.php smsProgressModal
        // ====================================================================
        let feeIsSendingSms    = false;
        let feeStopSms         = false;
        let feeSmsType         = 'control_number'; // 'control_number' | 'debt_reminder'
        let feeRecipientType   = 'parent';        // 'parent' | 'sponsor'
        let feeSmsYear         = null;
        let feeSmsAudience     = 'all';

        function feeEscapeHtml(str) {
            return $('<div>').text(str || '').html();
        }

        function feeHideModalForSmsEditor($modal) {
            return closeAllModalsForSms();
        }

        function closeAllModalsForSms() {
            return new Promise(function(resolve) {
                var $open = $('.modal.show').not('#smsTemplateModal');
                if (!$open.length) {
                    resolve([]);
                    return;
                }
                var ids = [];
                $open.each(function() {
                    var id = $(this).attr('id');
                    if (id) ids.push(id);
                });
                var remaining = $open.length;
                var finished = false;
                var done = function() {
                    if (finished) return;
                    finished = true;
                    resolve(ids);
                };
                $open.each(function() {
                    $(this).one('hidden.bs.modal', function() {
                        remaining--;
                        if (remaining <= 0) done();
                    });
                    $(this).modal('hide');
                });
                setTimeout(done, 500);
            });
        }

        function restoreModalsAfterSms(modalIds) {
            (modalIds || []).forEach(function(id) {
                if (!id || id === 'smsTemplateModal') return;
                var $m = $('#' + id);
                if ($m.length) $m.modal('show');
            });
        }

        function openSmsTemplateEditor(config) {
            return new Promise(async function(resolve) {
                var restoredIds = await closeAllModalsForSms();
                var resolved = false;

                function finish(result) {
                    if (resolved) return;
                    resolved = true;
                    $('body').removeClass('sms-template-open');
                    $('#smsTemplateConfirmBtn').off('click.smsTemplate');
                    $('#smsTemplateCancelBtn, #smsTemplateModal .close').off('click.smsTemplate');
                    $('#smsTemplateModal').off('hidden.bs.modal.smsTemplate shown.bs.modal.smsTemplate');
                    $('#smsTemplateModal').modal('hide');
                    setTimeout(function() {
                        if (!result.confirmed || config.reopenOnConfirm) {
                            restoreModalsAfterSms(restoredIds);
                        }
                        resolve(result);
                    }, 300);
                }

                var items = config.items || [];
                $('#smsTemplateModalTitle').text(config.title || 'Confirm SMS');
                $('#smsTemplateModalDesc').html(config.description || '');

                var html = '';
                items.forEach(function(item, idx) {
                    var key = item.key || ('item_' + idx);
                    html += '<div class="mb-3 p-3 border rounded sms-template-item" style="background:#fff5f5;border-color:#f0d0d0 !important;">';
                    if (item.label) {
                        html += '<div class="small fw-bold mb-1">' + feeEscapeHtml(item.label) + '</div>';
                    }
                    if (item.meta) {
                        html += '<div class="small text-muted mb-2">' + feeEscapeHtml(item.meta) + '</div>';
                    }
                    html += '<textarea class="form-control sms-template-message" rows="' + (items.length === 1 ? 5 : 3) + '" data-send-key="' + feeEscapeHtml(key) + '"></textarea>';
                    html += '</div>';
                });
                $('#smsTemplateEditorList').html(html);
                $('.sms-template-message').each(function(i) {
                    $(this).val((items[i] || {}).message || '');
                });

                $('#smsTemplateConfirmBtn').on('click.smsTemplate', function() {
                    var messages = {};
                    $('.sms-template-message').each(function() {
                        messages[String($(this).data('send-key'))] = $(this).val();
                    });
                    finish({ confirmed: true, messages: messages });
                });

                $('#smsTemplateCancelBtn, #smsTemplateModal .close').on('click.smsTemplate', function() {
                    finish({ confirmed: false, messages: {} });
                });

                $('#smsTemplateModal').on('hidden.bs.modal.smsTemplate', function() {
                    if (!resolved) finish({ confirmed: false, messages: {} });
                }).on('shown.bs.modal.smsTemplate', function() {
                    $('body').addClass('sms-template-open');
                    var $first = $('.sms-template-message').first();
                    if ($first.length) $first.trigger('focus');
                });

                $('#smsTemplateModal').modal('show');
            });
        }

        async function sendControlNumberSmsWithPreview(paymentID, options) {
            options = options || {};
            var btn = options.button || null;
            var originalHtml = btn ? btn.html() : '';

            if (!paymentID) return;

            if (btn) btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            try {
                var previewRes = await $.ajax({
                    url: '/resend_control_number/' + paymentID,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', preview: 1 }
                });

                if (btn) btn.prop('disabled', false).html(originalHtml);

                if (!previewRes.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to preview SMS',
                        text: previewRes.message || 'Failed to prepare the SMS message.',
                        confirmButtonColor: '#940000'
                    });
                    return;
                }

                var metaParts = [];
                if (previewRes.student_name) metaParts.push('Student: ' + previewRes.student_name);
                if (previewRes.control_number) metaParts.push('Control Number: ' + previewRes.control_number);
                if (previewRes.fee_name) metaParts.push('Fee: ' + previewRes.fee_name);
                if (previewRes.to) metaParts.push('Phone: ' + previewRes.to);

                var editResult = await openSmsTemplateEditor({
                    title: 'Send Control Number SMS',
                    description: '<div class="mb-2"><strong>To:</strong> ' + feeEscapeHtml(previewRes.parent_name || 'Parent') + '</div>',
                    items: [{
                        key: String(paymentID),
                        label: previewRes.parent_name || 'Parent',
                        meta: metaParts.join(' · '),
                        message: previewRes.sms_sample || ''
                    }]
                });

                if (!editResult.confirmed) return;

                var editedMessage = editResult.messages[String(paymentID)] || previewRes.sms_sample || '';
                if (btn) btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');

                var response = await $.ajax({
                    url: '/resend_control_number/' + paymentID,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        message: editedMessage
                    }
                });

                Swal.fire({
                    icon: response.success ? 'success' : 'error',
                    title: response.success ? 'Sent!' : 'Failed!',
                    text: response.message,
                    confirmButtonColor: '#940000'
                });
            } catch (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to send SMS.',
                    confirmButtonColor: '#940000'
                });
            } finally {
                if (btn) btn.prop('disabled', false).html(originalHtml);
            }
        }

        function getFeeSmsFilterParams() {
            return {
                class_id: $('#filterClass').val() || '',
                subclass_id: $('#filterSubclass').val() || '',
                enrollment_status: $('#filterStatus').val() || '',
                promotion_status: $('#filterPromotion').val() || '',
                sponsorship_filter: $('#filterSponsorship').val() || '',
                fee_id: $('#filterFee').val() || 'all',
                academic_year_id: headerAcademicYearID || '',
                use_request_year: headerAcademicYearID ? 1 : 0
            };
        }

        function feeBuildAudienceChooserHtml() {
            return '<div class="d-grid gap-2 w-100">' +
                '<button type="button" class="btn btn-outline-primary btn-lg w-100 fee-audience-choice" data-audience="all">' +
                    '<i class="bi bi-people-fill me-2"></i> All with outstanding balance' +
                '</button>' +
                '<button type="button" class="btn btn-outline-danger btn-lg w-100 fee-audience-choice" data-audience="deadline_reached">' +
                    '<i class="bi bi-alarm-fill me-2"></i> Deadline reached only' +
                '</button>' +
            '</div>';
        }

        function feeUpdateSelectedCount() {
            const count = $('.fee-parent-checkbox:checked').length;
            $('#feeSelectedCount').text('Recipients: ' + count);
            $('#feeStartSendingSms').prop('disabled', count === 0);
        }

        function openFeeSmsModal(type, year, sponsorshipFilter, audience) {
            feeSmsType = type;
            feeSmsYear = year;
            feeSmsAudience = audience || 'all';
            feeRecipientType = 'parent';

            if (type === 'debt_reminder') {
                $('#feeSmsModalTitle').text('Send Debt Reminder');
                var audienceLabel = feeSmsAudience === 'deadline_reached'
                    ? 'Deadline reached'
                    : 'All with outstanding balance';
                $('#feeSmsModalDesc').text('Parent list based on your filters. Recipients: ' + audienceLabel + '.');
                $('#feeSmsTabs').show();
            } else {
                $('#feeSmsModalTitle').text('Send Control Numbers (CN)');
                $('#feeSmsModalDesc').text('Parent list based on your filters. Select recipients, then review the SMS before sending.');
                $('#feeSmsTabs').hide();
            }

            $('#parent-sms-tab').addClass('active').attr('aria-selected', 'true');
            $('#sponsor-sms-tab').removeClass('active').attr('aria-selected', 'false');

            loadFeeRecipients();
            $('#feeSmsProgressModal').modal('show');
        }

        function loadFeeRecipients() {
            // Reset modal state
            $('#feeSmsLoadingState').removeClass('d-none');
            $('#feeSmsRecipientsSection').addClass('d-none');
            $('#feeSmsProgressArea').addClass('d-none');
            $('#feeSmsProgressBar').css('width', '0%').text('0%').removeClass('bg-info').addClass('bg-success');
            $('#feeSmsDeliverySummary').empty();
            $('#feeSmsProgressText').text('0 / 0');
            $('#feeSelectAllParents').prop('checked', false);
            $('#feeStartSendingSms').prop('disabled', true).html('<i class="bi bi-eye"></i> Review SMS & Send');
            $('#feeBtnCancelSms').prop('disabled', false).text('Cancel');
            $('.fee-parent-checkbox, #feeSelectAllParents').prop('disabled', false);
            feeIsSendingSms = false;
            feeStopSms      = false;

            // Update table header based on recipient type
            const headerRow = $('#feeSmsTableHeader');
            if (feeRecipientType === 'sponsor') {
                headerRow.html(`
                    <th style="width:40px;"></th>
                    <th>Sponsor</th>
                    <th>Students</th>
                    <th>Phone</th>
                    <th>Debt (TZS)</th>
                    <th class="text-center" style="width:110px;">Status</th>
                `);
            } else if (feeSmsType === 'control_number') {
                headerRow.html(`
                    <th style="width:40px;"></th>
                    <th>Parent</th>
                    <th>Student</th>
                    <th>Fee</th>
                    <th>CN</th>
                    <th>Phone</th>
                    <th class="text-center" style="width:110px;">Status</th>
                `);
            } else {
                headerRow.html(`
                    <th style="width:40px;"></th>
                    <th>Parent</th>
                    <th>Student</th>
                    <th>Fee</th>
                    <th>CN</th>
                    <th>Debt (TZS)</th>
                    <th class="text-center" style="width:110px;">Status</th>
                `);
            }

            const filterParams = getFeeSmsFilterParams();
            $.ajax({
                url: '{{ route("get_fee_sms_recipients") }}',
                method: 'GET',
                data: Object.assign({
                    type: feeSmsType,
                    year: feeSmsYear,
                    recipient_type: feeRecipientType,
                    audience: feeSmsAudience
                }, filterParams),
                success: function(response) {
                    $('#feeSmsLoadingState').addClass('d-none');
                    const list = $('#feeSmsRecipientsList');
                    list.empty();

                    if (!response.success || !response.recipients || response.recipients.length === 0) {
                        const colspan = feeRecipientType === 'sponsor' ? 6 : (feeSmsType === 'control_number' ? 7 : 7);
                        list.html(`<tr><td colspan="${colspan}" class="text-center text-muted py-3">No recipients match your filters.</td></tr>`);
                        $('#feeSmsRecipientsSection').removeClass('d-none');
                        return;
                    }

                    response.recipients.forEach(function(r, idx) {
                        const hasPhone   = r.phone && r.phone.length > 0;
                        const disabledAt = hasPhone ? '' : 'disabled="disabled"';
                        const checkId    = 'fee_chk_' + (r.paymentID || r.sponsorID) + '_' + idx;
                        const overdueRow = r.deadline_overdue ? ' table-danger' : '';
                        const statusHtml = hasPhone
                            ? '<span class="fee-status-marker text-muted small">Waiting</span>'
                            : '<span class="text-danger small">No Phone</span>';
                        const smsEncoded = encodeURIComponent(r.sms_message || '');

                        let rowHtml = `<tr class="${overdueRow.trim()}" data-payment-id="${r.paymentID || ''}" data-sponsor-id="${r.sponsorID || ''}" data-phone="${r.phone || ''}" data-sms-message="${smsEncoded}">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input fee-parent-checkbox" id="${checkId}" ${disabledAt}>
                                    <label class="custom-control-label" for="${checkId}">&nbsp;&nbsp;&nbsp;</label>
                                </div>
                            </td>
                            <td>${feeEscapeHtml(r.recipientLabel)}</td>`;

                        if (feeRecipientType === 'sponsor') {
                            rowHtml += `<td>${r.studentCount || 0}</td>`;
                            rowHtml += `<td>${r.phone || '<span class="text-muted">-</span>'}</td>`;
                            rowHtml += `<td>${parseFloat(r.balance || 0).toLocaleString()}</td>`;
                        } else {
                            rowHtml += `<td>${feeEscapeHtml(r.studentName || '-')}</td>`;
                            rowHtml += `<td><span class="badge" style="background:#fff5f5;color:#940000;border:1px solid #940000;">${feeEscapeHtml(r.fee_name || 'Ada')}</span></td>`;
                            rowHtml += `<td class="fw-bold">${feeEscapeHtml(r.control_number || '-')}</td>`;
                            if (feeSmsType === 'control_number') {
                                rowHtml += `<td>${r.phone || '<span class="text-muted">-</span>'}</td>`;
                            } else {
                                rowHtml += `<td>${parseFloat(r.balance || 0).toLocaleString()}</td>`;
                            }
                        }

                        rowHtml += `<td class="text-center fee-status-col">${statusHtml}</td>
                        </tr>`;

                        list.append(rowHtml);
                    });

                    feeUpdateSelectedCount();
                    $('#feeSmsRecipientsSection').removeClass('d-none');
                },
                error: function(xhr) {
                    $('#feeSmsLoadingState').addClass('d-none');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to download list.';
                    $('#feeSmsRecipientsList').html(`<tr><td colspan="5" class="text-center text-danger py-3">${msg}</td></tr>`);
                    $('#feeSmsRecipientsSection').removeClass('d-none');
                }
            });
        }

        // Tab Change Handler
        $(document).on('click', '#feeSmsTabs .nav-link', function(e) {
            e.preventDefault();
            if (feeIsSendingSms) return;
            
            feeRecipientType = $(this).data('type');
            loadFeeRecipients();
        });

        // Select All toggle
        $('#feeSelectAllParents').on('change', function() {
            $('.fee-parent-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
            feeUpdateSelectedCount();
        });
        $(document).on('change', '.fee-parent-checkbox', function() {
            feeUpdateSelectedCount();
        });

        // Preview SMS templates in dedicated modal, confirm, then send per-row
        $('#feeStartSendingSms').on('click', async function() {
            const selectedRows = $('.fee-parent-checkbox:checked').closest('tr');
            if (selectedRows.length === 0) return;

            const templateItems = [];
            selectedRows.each(function(i) {
                const row = $(this);
                const label = row.find('td').eq(1).text().trim();
                const studentCol = feeRecipientType === 'sponsor' ? '' : row.find('td').eq(2).text().trim();
                const feeCol = feeRecipientType === 'sponsor' ? '' : row.find('td').eq(3).text().trim();
                const cnCol = feeRecipientType === 'sponsor' ? '' : row.find('td').eq(4).text().trim();
                const msg = decodeURIComponent(row.attr('data-sms-message') || '');
                const pid = row.data('payment-id') || '';
                const sid = row.data('sponsor-id') || '';
                const key = String(pid || sid || ('row_' + i));

                const metaParts = [];
                if (studentCol) metaParts.push(studentCol);
                if (feeCol) metaParts.push(feeCol);
                if (cnCol && cnCol !== '-') metaParts.push('Control Number: ' + cnCol);

                templateItems.push({
                    key: key,
                    label: label,
                    meta: metaParts.join(' · '),
                    message: msg
                });
            });

            const editResult = await openSmsTemplateEditor({
                title: 'Confirm sending SMS',
                description: 'You are about to send SMS to <strong>' + selectedRows.length + '</strong> recipients. Edit the message below:',
                reopenOnConfirm: true,
                items: templateItems
            });
            if (!editResult.confirmed) return;

            const messageMap = editResult.messages || {};
            feeIsSendingSms = true;
            feeStopSms      = false;

            $('#feeStartSendingSms').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Sending...');
            $('#feeBtnCancelSms').prop('disabled', true);
            $('.fee-parent-checkbox, #feeSelectAllParents').prop('disabled', true);
            $('#feeSmsProgressArea').removeClass('d-none');

            const total = selectedRows.length;
            let delivered = 0;
            let failed    = 0;

            $('#feeSmsProgressText').text(`0 / ${total}`);
            $('#feeSmsDeliverySummary').empty();

            for (let i = 0; i < total; i++) {
                if (feeStopSms) break;

                const row       = $(selectedRows[i]);
                const statusCol = row.find('.fee-status-col');
                const pID       = row.data('payment-id');
                const sID       = row.data('sponsor-id');
                const sendKey   = pID || sID || ('row_' + i);
                const customMsg = messageMap[sendKey] || decodeURIComponent(row.attr('data-sms-message') || '');

                statusCol.html('<div class="spinner-border spinner-border-sm text-primary" role="status"></div>');

                try {
                    const response = await $.ajax({
                        url: '{{ route("send_single_fee_sms") }}',
                        type: 'POST',
                        data: {
                            paymentID: pID,
                            sponsorID: sID,
                            type: feeSmsType,
                            recipient_type: feeRecipientType,
                            audience: feeSmsAudience,
                            year: feeSmsYear,
                            message: customMsg,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    if (response.success) {
                        statusCol.html('<i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;" title="Sent"></i>');
                        delivered++;
                    } else {
                        statusCol.html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:1.2rem;" title="' + feeEscapeHtml(response.message || 'Failed') + '"></i>');
                        failed++;
                    }
                } catch (err) {
                    statusCol.html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:1.2rem;" title="Network error"></i>');
                    failed++;
                }

                const current = delivered + failed;
                const percent = Math.round((current / total) * 100);
                $('#feeSmsProgressBar').css('width', percent + '%').text(percent + '%');
                $('#feeSmsProgressText').text(`${current} / ${total}`);
                $('#feeSmsDeliverySummary').html(
                    `<span class="text-success font-weight-bold">${delivered} Sent</span> | <span class="text-danger font-weight-bold">${failed} Failed</span>`
                );
            }

            feeIsSendingSms = false;
            $('#feeStartSendingSms').html('<i class="bi bi-check-all"></i> Completed').prop('disabled', true);
            $('#feeBtnCancelSms').prop('disabled', false).text('Close');

            Swal.fire({
                title: 'SMS Completed!',
                text: `Sent: ${delivered}, Failed: ${failed}`,
                icon: delivered > 0 ? 'success' : 'info',
                confirmButtonColor: '#940000'
            }).then(function() {
                if (delivered > 0) loadPaymentsData();
            });
        });

        // ---------- Button: Send Control Numbers ----------
        $('#sendSMSBtn').off('click').on('click', function(e) {
            e.preventDefault();
            openFeeSmsModal('control_number', headerAcademicYearID, '', 'all');
        });

        // ---------- Button: Send Debt Reminders, choose audience first ----------
        $('#sendDebtRemindersBtn').off('click').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Select Reminder Recipients',
                html: feeBuildAudienceChooserHtml(),
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#6c757d',
                width: 520,
                didOpen: function() {
                    $('.fee-audience-choice').on('click', function() {
                        const audience = $(this).data('audience');
                        Swal.close();
                        openFeeSmsModal('debt_reminder', headerAcademicYearID, '', audience);
                    });
                }
            });
        });

        // New AJAX Handler for Individual Student Export (Standardized Server-Side)
        $(document).on('click', '.btn-export-pdf, #modalExportPdfBtn', function(e) {
            e.preventDefault();
            
            const btn = $('#viewMoreModal').data('view-more-btn');
            if (!btn) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Could not identify student for export. Please close and re-open details.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            const studentID = btn.data('student-id');
            const studentName = btn.data('student-name') || 'Student';
            
            if (!studentID) {
                Swal.fire('Error', 'Missing student ID.', 'error');
                return;
            }

            let url = '{{ url("export_payment_invoice_pdf") }}/' + studentID;
            const qs = new URLSearchParams();
            if (headerAcademicYearID) {
                qs.set('academic_year_id', headerAcademicYearID);
                qs.set('use_request_year', '1');
            }
            const filterClass = $('#filterClass').val() || btn.data('class-id') || '';
            const filterSubclass = $('#filterSubclass').val() || '';
            const filterFee = $('#filterFee').val() || 'all';
            const filterPaymentStatus = $('#filterPaymentStatus').val() || '';
            const filterDeadline = $('#filterDeadline').val() || '';
            const filterSponsorship = $('#filterSponsorship').val() || '';
            const filterEnrollment = $('#filterStatus').val() || '';
            if (filterClass) qs.set('class_id', filterClass);
            if (filterSubclass) qs.set('subclass_id', filterSubclass);
            if (filterFee && filterFee !== 'all') qs.set('fee_id', filterFee);
            if (filterPaymentStatus) qs.set('payment_status', filterPaymentStatus);
            if (filterDeadline) qs.set('deadline_filter', filterDeadline);
            if (filterSponsorship) qs.set('sponsorship_filter', filterSponsorship);
            if (filterEnrollment) qs.set('enrollment_status', filterEnrollment);
            const q = qs.toString();
            if (q) url += '?' + q;

            $('#pdfProgressTitle').text('Generating Student Invoice');
            $('#downloadProgressModal').modal('show');
            $('#pdfProgressBar').css('width', '0%');
            $('#pdfProgressPercent').text('0%');

            let progress = 0;
            const interval = setInterval(() => {
                if (progress < 95) {
                    progress += (2 + Math.random() * 5);
                    if (progress > 95) progress = 95;
                    $('#pdfProgressBar').css('width', progress + '%');
                    $('#pdfProgressPercent').text(Math.round(progress) + '%');
                }
            }, 300);

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Download failed');
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(interval);
                    $('#pdfProgressBar').css('width', '100%');
                    $('#pdfProgressPercent').text('100%');
                    
                    setTimeout(() => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        a.download = `Invoice_${studentName.replace(/\s+/g, '_')}_${new Date().getFullYear()}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                        
                        setTimeout(() => {
                            $('#downloadProgressModal').modal('hide');
                        }, 500);
                    }, 400);
                })
                .catch(err => {
                    clearInterval(interval);
                    $('#downloadProgressModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: 'Failed to generate PDF invoice. Please try again.',
                        confirmButtonColor: '#940000'
                    });
                });
        });

        // Add export button to view more modal

        
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Generate Control Numbers
        $('#generateControlNumbersBtn').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: $('#filterClass').val()
                    ? 'This will generate control numbers for active students in the selected class. Multi-account fees each get their own CN.'
                    : 'This will generate control numbers for all active students. Multi-account fees each get their own CN. Existing CNs will not be overwritten.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Generate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalText = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');
                    
                    $.ajax({
                        url: '{{ route("generate_control_numbers") }}',
                        method: 'POST',
                        data: {
                            academic_year_id: headerAcademicYearID,
                            class_id: $('#filterClass').val() || ''
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    loadPaymentsData(); // Reload data via AJAX
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to generate control numbers';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        });

        // Copy Control Number
        $(document).on('click', '.copy-control-btn', function(e) {
            e.preventDefault();
            const controlNumber = $(this).data('control-number');
            
            navigator.clipboard.writeText(controlNumber).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Control number copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });

        // View More
        $(document).on('click', '.view-more-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Store button reference in modal for PDF export
            $('#viewMoreModal').data('view-more-btn', btn);
            
            // Get isClosedYear from button data or from global response
            const isClosedYear = btn.data('is-closed-year') || false;
            
            // Student Photo
            const studentPhoto = btn.data('student-photo') || '';
            const firstLetter = btn.data('student-first-letter') || 'N';
            const placeholderColor = btn.data('student-placeholder-color') || '#940000';
            
            const photoImg = $('#view_student_photo');
            const photoPlaceholder = $('#view_student_photo_placeholder');
            
            // Reset both elements
            photoImg.hide();
            photoPlaceholder.hide();
            
            if (studentPhoto) {
                photoImg.attr('src', studentPhoto);
                photoImg.off('error').on('error', function() {
                    $(this).hide();
                    photoPlaceholder.css({
                        'background-color': placeholderColor,
                        'display': 'flex'
                    }).text(firstLetter);
                });
                photoImg.show();
            } else {
                photoPlaceholder.css({
                    'background-color': placeholderColor,
                    'display': 'flex'
                }).text(firstLetter);
            }
            
            // Student Information
            $('#view_student_name').text(btn.data('student-name') || '-');
            $('#view_student_class').text(btn.data('student-class') || '-');
            $('#view_student_admission').text(btn.data('student-admission') || '-');
            var enrSt = btn.data('enrollment-status') || '';
            var promoSt = btn.data('promotion-status') || '';
            var badgeHtml = '';
            if (enrSt) badgeHtml += '<span class="badge bg-success me-1">' + enrSt + '</span>';
            if (promoSt) badgeHtml += '<span class="badge bg-secondary me-1">' + promoSt + '</span>';
            var ayLbl = btn.data('academic-year') || '';
            if (ayLbl) badgeHtml += '<span class="badge bg-light text-dark border">' + ayLbl + '</span>';
            $('#view_student_enrollment_badges').html(badgeHtml);
            
            // Get payments and totals data
            let payment = {};
            let totals = {};
            try {
                const paymentData = btn.data('payment');
                const totalsData = btn.data('totals');
                
                if (paymentData) {
                    payment = typeof paymentData === 'string' ? JSON.parse(paymentData) : paymentData;
                }
                if (totalsData) {
                    totals = typeof totalsData === 'string' ? JSON.parse(totalsData) : totalsData;
                }
            } catch(e) {
                console.error('Error parsing payments data:', e);
            }

            let multiAccounts = [];
            try {
                const multiData = btn.data('multi-accounts');
                if (multiData) multiAccounts = typeof multiData === 'string' ? JSON.parse(multiData) : multiData;
            } catch (e) { multiAccounts = []; }
            const isMultiAccountStudent = multiAccounts && multiAccounts.length > 0;
            $('#viewMoreModal').data('multiAccounts', multiAccounts);
            $('#viewMoreModal').data('isMultiAccount', isMultiAccountStudent);
            
            // Extract fee payments, for multi-account aggregate all fee rows
            let feePayments = payment.fee_payments || [];
            if (typeof feePayments === 'string') {
                try { feePayments = JSON.parse(feePayments); } catch (e) { feePayments = []; }
            }
            if (multiAccounts && multiAccounts.length) {
                feePayments = [];
                multiAccounts.forEach(function(ma) {
                    normalizeFeePaymentsFromMulti(ma).forEach(function(fp) {
                        feePayments.push(Object.assign({}, fp, {
                            _paymentID: ma.paymentID,
                            _control_number: ma.control_number,
                            _feeID: fp.feeID || ma.feeID
                        }));
                    });
                });
            }
            const tuitionPayments = feePayments.filter(fp => fp.is_required);
            const otherFeePayments = feePayments.filter(fp => !fp.is_required);
            
            // Debt handling, prefer live prior-years calc from list, then bill debt field
            const priorFromList = parseFloat(btn.data('prior-years-owed') || totals.prior_years_owed || 0);
            const billDebt = parseFloat(payment.debt || 0);
            const oldDebt = Math.max(priorFromList, billDebt);
            // Prefer aggregated totals (single + multi) when present
            const totalPaid = parseFloat(totals.total_paid != null ? totals.total_paid : (payment.amount_paid || 0));
            const totalRequired = parseFloat(totals.total_required != null ? totals.total_required : (payment.amount_required || 0));
            const totalOutstanding = parseFloat(totals.total_balance != null ? totals.total_balance : (payment.balance || 0));
            const currentBillBalance = Math.max(0, totalOutstanding - oldDebt);

            // Populate Summary Dashboard
            $('#view_control_number').text(payment.control_number || 'N/A');
            $('#view_total_paid').text(totalPaid.toLocaleString() + '/=');
            $('#view_bill_balance').text(currentBillBalance.toLocaleString() + '/=');
            $('#view_total_fee_required').text(totalRequired.toLocaleString() + '/=');
            $('#view_old_debt').text(oldDebt.toLocaleString() + '/=');
            $('#view_total_outstanding').text(totalOutstanding.toLocaleString() + '/=');

            // Multi account control numbers table
            if (multiAccounts && multiAccounts.length) {
                let mah = '';
                multiAccounts.forEach(function(ma) {
                    var dReq = parseFloat(ma.deadline_required || 0);
                    var dPaid = parseFloat(ma.deadline_paid || 0);
                    var dMet = dReq > 0 ? (dPaid + 0.00001 >= dReq) : true;
                    var deadlineHtml = (dReq > 0 && !dMet)
                        ? ('<span class="text-danger">' +
                            dPaid.toLocaleString() + ' / ' + dReq.toLocaleString() + '</span>')
                        : '<span class="text-muted">-</span>';
                    mah += '<tr>' +
                        '<td class="fw-bold" data-label="Fee">' + (ma.fee_name || '-') + '</td>' +
                        '<td data-label="Account"><span class="badge bg-light text-dark border">' + (ma.account_label || '-') + '</span></td>' +
                        '<td class="control-number" data-label="Control Number">' + (ma.control_number || '-') + '</td>' +
                        '<td class="text-end" data-label="Required">' + parseFloat(ma.amount_required || 0).toLocaleString() + '</td>' +
                        '<td class="text-end text-success" data-label="Paid">' + parseFloat(ma.amount_paid || 0).toLocaleString() + '</td>' +
                        '<td class="text-end text-danger" data-label="Balance">' + parseFloat(ma.balance || 0).toLocaleString() + '</td>' +
                        '<td class="small" data-label="Deadline">' + deadlineHtml + '</td>' +
                        '<td data-label="Status"><span class="badge bg-secondary">' + (ma.payment_status || 'Pending') + '</span></td>' +
                        '<td class="text-nowrap" data-label="Action">' +
                            (!isClosedYear ? (
                                '<button class="btn btn-sm btn-success record-payment-btn" ' +
                                    'data-multi-mode="1" ' +
                                    'data-payment-id="' + ma.paymentID + '" ' +
                                    'data-fee-id="' + (ma.feeID || '') + '" ' +
                                    'data-control-number="' + (ma.control_number || '') + '" ' +
                                    'data-amount-required="' + (ma.amount_required || 0) + '" ' +
                                    'data-amount-paid="' + (ma.amount_paid || 0) + '" ' +
                                    'data-balance="' + (ma.balance || 0) + '" ' +
                                    'data-is-sponsored="' + (btn.data('student-sponsor') ? 'yes' : 'no') + '" ' +
                                    'data-academic-year="' + (btn.data('academic-year') || '') + '" ' +
                                    'data-academic-year-id="' + (btn.data('academic-year-id') || '') + '" ' +
                                    'title="Record payment for ' + (ma.fee_name || 'fee') + '"><i class="bi bi-cash"></i></button> '
                            ) : '') +
                            (ma.paymentID ? ('<button class="btn btn-sm btn-outline-primary resend-control-sms-btn" data-payment-id="' + ma.paymentID + '"><i class="bi bi-send"></i></button>') : '') +
                        '</td></tr>';
                });
                $('#multiAccountsBody').html(mah);
                $('#multiAccountsSection').show();
                // Multi-account: each fee has its own CN, hide single-account CN box
                $('#singleAccountCnWrap').hide();
            } else {
                $('#multiAccountsBody').empty();
                $('#multiAccountsSection').hide();
                $('#singleAccountCnWrap').show();
            }

            // Sponsorship Info
            const sponsorData = btn.data('student-sponsor');
            const sponsorInfoSection = $('#sponsorInfoSection');
            if (sponsorData) {
                let sponsor = typeof sponsorData === 'string' ? JSON.parse(sponsorData) : sponsorData;
                $('#view_sponsor_name').text(sponsor.sponsor_name || '-');
                $('#view_sponsor_contact_person').text(sponsor.contact_person || 'N/A');
                $('#view_sponsor_phone').text(sponsor.phone || '-');
                $('#view_sponsor_email').text(sponsor.email || 'N/A');
                $('#view_sponsor_percentage').text((sponsor.percentage || 0) + '%');
                
                // Calculate display-only sponsor coverage amount
                const sponsorDisplayAmount = (totalRequired * (sponsor.percentage || 0)) / 100;
                $('#view_sponsor_amount').text(sponsorDisplayAmount.toLocaleString() + '/=');
                
                // Real split data from database
                const sponsorReq = parseFloat(payment.sponsor_amount_required || 0);
                const sponsorPaid = parseFloat(payment.sponsor_amount_paid || 0);
                const sponsorBal = parseFloat(payment.sponsor_balance || 0);
                const parentReq = parseFloat(payment.parent_amount_required || 0);
                const parentPaid = parseFloat(payment.parent_amount_paid || 0);
                const parentBal = parseFloat(payment.parent_balance || 0);

                $('#view_sponsor_req_val').text(sponsorReq.toLocaleString() + '/=');
                $('#view_sponsor_paid_val').text(sponsorPaid.toLocaleString() + '/=');
                $('#view_sponsor_bal_val').text(sponsorBal.toLocaleString() + '/=');

                $('#view_parent_req_val').text(parentReq.toLocaleString() + '/=');
                $('#view_parent_paid_val').text(parentPaid.toLocaleString() + '/=');
                $('#view_parent_bal_val').text(parentBal.toLocaleString() + '/=');

                // If sponsorship is 100% or 0%, we can hide the breakdown or just show it accurately.
                // The user said "100% acha kama ilivyosasa", so maybe hide breakdown if 100%?
                // Actually showing it accurately is better.
                $('#splitBillPortionsSection').show();
                sponsorInfoSection.show();
            } else {
                sponsorInfoSection.hide();
                $('#splitBillPortionsSection').hide();
                // Reset Total Fee Required display if previously modified
                $('#view_total_fee_required').text(totalRequired.toLocaleString() + '/=');
            }
            
            // Build Record Payment / Send SMS
            let recordPaymentHtml = '';
            const rowYearLabel = btn.data('academic-year') || headerAcademicYearLabel || '';
            const rowYearId = btn.data('academic-year-id') || headerAcademicYearID || '';
            if (!isClosedYear && isMultiAccountStudent) {
                recordPaymentHtml += '<button class="btn btn-success record-payment-btn" ' +
                    'data-multi-mode="1" ' +
                    'data-payment-id="' + (multiAccounts[0].paymentID || '') + '" ' +
                    'data-control-number="' + (multiAccounts[0].control_number || '') + '" ' +
                    'data-amount-required="' + (totals.total_required || 0) + '" ' +
                    'data-amount-paid="' + (totals.total_paid || 0) + '" ' +
                    'data-balance="' + (totals.total_balance || 0) + '" ' +
                    'data-is-sponsored="' + (btn.data('student-sponsor') ? 'yes' : 'no') + '" ' +
                    'data-academic-year="' + rowYearLabel + '" ' +
                    'data-academic-year-id="' + rowYearId + '" ' +
                    'title="Record payment, select fee">' +
                    '<i class="bi bi-cash-coin me-2"></i> Record New Payment' +
                    '</button>';
            } else if (!isClosedYear && payment && payment.paymentID) {
                recordPaymentHtml += '<div class="btn-group">';
                recordPaymentHtml += '<button class="btn btn-success record-payment-btn" ' +
                    'data-payment-id="' + payment.paymentID + '" ' +
                    'data-control-number="' + (payment.control_number || '') + '" ' +
                    'data-amount-required="' + (payment.amount_required || 0) + '" ' +
                    'data-amount-paid="' + (payment.amount_paid || 0) + '" ' +
                    'data-balance="' + (payment.balance || 0) + '" ' +
                    'data-is-sponsored="' + (sponsorData ? 'yes' : 'no') + '" ' +
                    'data-academic-year="' + rowYearLabel + '" ' +
                    'data-academic-year-id="' + rowYearId + '" ' +
                    'data-payment=\'' + JSON.stringify(payment).replace(/'/g, "&#39;") + '\' ' +
                    'data-fee-type="Unified Payment" ' +
                    'title="Record Payment">' +
                    '<i class="bi bi-cash-coin me-2"></i> Record New Payment' +
                    '</button>';
                recordPaymentHtml += '<button class="btn btn-outline-primary resend-control-sms-btn" ' +
                    'data-payment-id="' + payment.paymentID + '" ' +
                    'title="Send Control Number SMS">' +
                    '<i class="bi bi-send me-1"></i> Send SMS' +
                    '</button>';
                recordPaymentHtml += '</div>';
            }
            $('#modalRecordPaymentContainer').html(recordPaymentHtml);
            
            // Build Tuition Fees section
            let tuitionHtml = '';
            if (tuitionPayments.length > 0 || oldDebt > 0) {
                tuitionHtml += '<div class="table-responsive">';
                tuitionHtml += '<table class="table table-sm table-bordered table-hover view-more-responsive-table">';
                tuitionHtml += '<thead class="bg-light">';
                tuitionHtml += '<tr>';
                tuitionHtml += '<th>Fee Name</th>';
                tuitionHtml += '<th class="text-end">Required (TZS)</th>';
                tuitionHtml += '<th class="text-end">Paid (TZS)</th>';
                tuitionHtml += '<th class="text-end">Balance (TZS)</th>';
                tuitionHtml += '<th class="text-center">Actions</th>';
                tuitionHtml += '</tr>';
                tuitionHtml += '</thead>';
                tuitionHtml += '<tbody>';
                
                // Add Previous Year Debt if applicable
                if (oldDebt > 0) {
                    tuitionHtml += '<tr class="table-warning">';
                    tuitionHtml += '<td data-label="Fee Name"><strong>Previous Year Debt</strong></td>';
                    tuitionHtml += '<td class="text-end" data-label="Required (TZS)">' + oldDebt.toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end" data-label="Paid (TZS)">0.00/=</td>';
                    tuitionHtml += '<td class="text-end" data-label="Balance (TZS)"><strong>' + oldDebt.toLocaleString() + '/=</strong></td>';
                    tuitionHtml += '<td class="text-center" data-label="Actions">-</td>';
                    tuitionHtml += '</tr>';
                }

                tuitionPayments.forEach(function(fp) {
                    var rowPaymentId = fp._paymentID || payment.paymentID;
                    var rowCn = fp._control_number || payment.control_number || '';
                    tuitionHtml += '<tr>';
                    tuitionHtml += '<td data-label="Fee Name">' + fp.fee_name + '</td>';
                    tuitionHtml += '<td class="text-end" data-label="Required (TZS)">' + parseFloat(fp.fee_total_amount || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end text-success" data-label="Paid (TZS)">' + parseFloat(fp.amount_paid || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end text-danger" data-label="Balance (TZS)">' + parseFloat(fp.balance || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-center" data-label="Actions">';
                    if (!isClosedYear && isMultiAccountStudent && rowPaymentId) {
                        tuitionHtml += '<button class="btn btn-sm btn-success record-payment-btn me-1" ' +
                            'data-multi-mode="1" data-payment-id="' + rowPaymentId + '" ' +
                            'data-fee-id="' + (fp._feeID || fp.feeID || '') + '" ' +
                            'data-control-number="' + rowCn + '" ' +
                            'data-amount-required="' + (fp.fee_total_amount || 0) + '" ' +
                            'data-balance="' + (fp.balance || 0) + '" ' +
                            'data-is-sponsored="' + (btn.data('student-sponsor') ? 'yes' : 'no') + '" ' +
                            'data-academic-year="' + rowYearLabel + '" data-academic-year-id="' + rowYearId + '" ' +
                            'title="Record payment"><i class="bi bi-cash-coin"></i></button>';
                    }
                    tuitionHtml += '<button class="btn btn-sm btn-outline-info view-payment-records-btn" ';
                    tuitionHtml += 'data-payment-id="' + rowPaymentId + '" ';
                    tuitionHtml += 'data-control-number="' + rowCn + '" ';
                    tuitionHtml += 'data-fee-name="' + fp.fee_name + '" ';
                    tuitionHtml += 'title="View History"><i class="bi bi-clock-history"></i></button>';
                    tuitionHtml += '</td>';
                    tuitionHtml += '</tr>';
                });
                
                tuitionHtml += '</tbody>';
                tuitionHtml += '</table>';
                tuitionHtml += '</div>';
                
                $('#tuitionFeesContent').html(tuitionHtml);
                $('#tuitionFeesSection').show();
            } else {
                $('#tuitionFeesSection').hide();
            }
            
            // Build Other Fees section
            let otherFeesHtml = '';
            if (otherFeePayments.length > 0) {
                otherFeesHtml += '<div class="table-responsive">';
                otherFeesHtml += '<table class="table table-sm table-bordered table-hover view-more-responsive-table">';
                otherFeesHtml += '<thead class="bg-light">';
                otherFeesHtml += '<tr>';
                otherFeesHtml += '<th>Fee Name</th>';
                otherFeesHtml += '<th class="text-end">Required (TZS)</th>';
                otherFeesHtml += '<th class="text-end">Paid (TZS)</th>';
                otherFeesHtml += '<th class="text-end">Balance (TZS)</th>';
                otherFeesHtml += '<th class="text-center">Actions</th>';
                otherFeesHtml += '</tr>';
                otherFeesHtml += '</thead>';
                otherFeesHtml += '<tbody>';
                
                otherFeePayments.forEach(function(fp) {
                    var rowPaymentId = fp._paymentID || payment.paymentID;
                    var rowCn = fp._control_number || payment.control_number || '';
                    otherFeesHtml += '<tr>';
                    otherFeesHtml += '<td data-label="Fee Name">' + fp.fee_name + '</td>';
                    otherFeesHtml += '<td class="text-end" data-label="Required (TZS)">' + parseFloat(fp.fee_total_amount || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-success" data-label="Paid (TZS)">' + parseFloat(fp.amount_paid || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-danger" data-label="Balance (TZS)">' + parseFloat(fp.balance || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-center" data-label="Actions">';
                    if (!isClosedYear && isMultiAccountStudent && rowPaymentId) {
                        otherFeesHtml += '<button class="btn btn-sm btn-success record-payment-btn me-1" ' +
                            'data-multi-mode="1" data-payment-id="' + rowPaymentId + '" ' +
                            'data-fee-id="' + (fp._feeID || fp.feeID || '') + '" ' +
                            'data-control-number="' + rowCn + '" ' +
                            'data-amount-required="' + (fp.fee_total_amount || 0) + '" ' +
                            'data-balance="' + (fp.balance || 0) + '" ' +
                            'data-is-sponsored="' + (btn.data('student-sponsor') ? 'yes' : 'no') + '" ' +
                            'data-academic-year="' + rowYearLabel + '" data-academic-year-id="' + rowYearId + '" ' +
                            'title="Record payment"><i class="bi bi-cash-coin"></i></button>';
                    }
                    otherFeesHtml += '<button class="btn btn-sm btn-outline-info view-payment-records-btn" ';
                    otherFeesHtml += 'data-payment-id="' + rowPaymentId + '" ';
                    otherFeesHtml += 'data-control-number="' + rowCn + '" ';
                    otherFeesHtml += 'data-fee-name="' + fp.fee_name + '" ';
                    otherFeesHtml += 'title="View History"><i class="bi bi-clock-history"></i></button>';
                    otherFeesHtml += '</td>';
                    otherFeesHtml += '</tr>';
                });
                
                otherFeesHtml += '</tbody>';
                otherFeesHtml += '</table>';
                otherFeesHtml += '</div>';
                    
                $('#otherFeesContent').html(otherFeesHtml);
                $('#otherFeesSection').show();
            } else {
                $('#otherFeesSection').hide();
            }
            
            // Build All Transactions section, one toggle per control number
            function buildCnTransactionTable(records, paymentID, isClosedYear) {
                if (!records || !records.length) {
                    return '<div class="text-center py-3 text-muted small">No transactions for this control number.</div>';
                }
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover mb-0">';
                html += '<thead class="bg-light"><tr>';
                html += '<th>Date</th><th class="text-end">Amount (TZS)</th><th>Method</th><th>Reference</th>';
                html += '<th class="text-center">Status</th><th class="text-center">Actions</th>';
                html += '</tr></thead><tbody>';

                records.slice().sort(function(a, b) {
                    return new Date(b.payment_date) - new Date(a.payment_date);
                }).forEach(function(record) {
                    const isVerified = record.is_verified || false;
                    let displayDate = 'N/A';
                    if (record.payment_date) {
                        const pDate = new Date(record.payment_date);
                        displayDate = pDate.toLocaleString('en-GB', {
                            day: '2-digit', month: 'short', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                    }
                    html += '<tr>';
                    html += '<td>' + displayDate + '</td>';
                    html += '<td class="text-end fw-bold text-success">' + parseFloat(record.paid_amount || 0).toLocaleString() + '/=</td>';
                    html += '<td><span class="badge ' + (record.payment_source === 'Bank' ? 'bg-primary' : 'bg-success') + '">' + (record.payment_source || 'N/A') + '</span></td>';
                    html += '<td>' + (record.reference_number || 'N/A') + '</td>';
                    html += '<td class="text-center">';
                    html += isVerified
                        ? '<span class="badge bg-success" title="Verified"><i class="bi bi-patch-check-fill"></i></span>'
                        : '<span class="badge bg-secondary" title="Unverified"><i class="bi bi-patch-exclamation"></i></span>';
                    html += '</td><td class="text-center"><div class="btn-group btn-group-sm">';
                    if (!isClosedYear) {
                        if (isVerified) {
                            html += '<button class="btn btn-outline-secondary unverify-payment-record-btn" data-record-id="' + record.recordID + '" data-payment-id="' + paymentID + '" title="Unverify"><i class="bi bi-patch-minus"></i></button>';
                        } else {
                            html += '<button class="btn btn-outline-success verify-payment-record-btn" data-record-id="' + record.recordID + '" data-payment-id="' + paymentID + '" title="Verify"><i class="bi bi-patch-check"></i></button>';
                        }
                        html += '<button class="btn btn-outline-warning edit-payment-record-btn" ' +
                            'data-record-id="' + record.recordID + '" data-payment-id="' + paymentID + '" ' +
                            'data-paid-amount="' + record.paid_amount + '" data-payment-date="' + (record.payment_date || '') + '" ' +
                            'data-payment-source="' + (record.payment_source || '') + '" data-reference-number="' + (record.reference_number || '') + '" ' +
                            'data-bank-name="' + (record.bank_name || '') + '" data-notes="' + (record.notes || '') + '" title="Edit"><i class="bi bi-pencil"></i></button>';
                        html += '<button class="btn btn-outline-danger delete-payment-record-btn" data-record-id="' + record.recordID + '" data-payment-id="' + paymentID + '" data-paid-amount="' + record.paid_amount + '" title="Delete"><i class="bi bi-trash"></i></button>';
                    } else {
                        html += '-';
                    }
                    html += '</div></td></tr>';
                });
                html += '</tbody></table></div>';
                return html;
            }

            // Collect all control-number groups (single + multi)
            const cnGroups = [];
            const seenPaymentIds = {};
            if (multiAccounts && multiAccounts.length) {
                multiAccounts.forEach(function(ma) {
                    if (!ma.paymentID || seenPaymentIds[ma.paymentID]) return;
                    seenPaymentIds[ma.paymentID] = true;
                    cnGroups.push({
                        paymentID: ma.paymentID,
                        control_number: ma.control_number || 'N/A',
                        label: ma.fee_name || 'Multi Fee',
                        account_label: ma.account_label || '',
                        records: ma.payment_records || []
                    });
                });
            }
            // Single-account / unified bill (feeID null)
            if (payment && payment.paymentID && !seenPaymentIds[payment.paymentID] && !payment.feeID) {
                seenPaymentIds[payment.paymentID] = true;
                cnGroups.unshift({
                    paymentID: payment.paymentID,
                    control_number: payment.control_number || 'N/A',
                    label: 'Single Account',
                    account_label: '',
                    records: payment.payment_records || payment.paymentRecords || []
                });
            } else if (payment && payment.paymentID && !seenPaymentIds[payment.paymentID] && (!multiAccounts || !multiAccounts.length)) {
                // Fallback: only one payment object available
                seenPaymentIds[payment.paymentID] = true;
                cnGroups.push({
                    paymentID: payment.paymentID,
                    control_number: payment.control_number || 'N/A',
                    label: 'Control Number',
                    account_label: '',
                    records: payment.payment_records || payment.paymentRecords || []
                });
            }

            let transactionsHtml = '';
            if (!cnGroups.length) {
                transactionsHtml = '<div class="text-center py-3 text-muted">No control numbers / transactions found.</div>';
            } else {
                cnGroups.forEach(function(group, idx) {
                    const collapseId = 'cnTxCollapse_' + group.paymentID + '_' + idx;
                    const recordCount = (group.records || []).length;
                    transactionsHtml += '<div class="border mb-2">';
                    transactionsHtml += '<button class="btn btn-link w-100 text-start text-decoration-none p-3 d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#' + collapseId + '" aria-expanded="false">';
                    transactionsHtml += '<span><i class="bi bi-upc me-2 text-primary-custom"></i>' +
                        '<strong class="text-primary-custom">' + group.control_number + '</strong>' +
                        ' <span class="text-muted">- ' + group.label + '</span>' +
                        (group.account_label ? (' <span class="badge bg-light text-dark border ms-1">' + group.account_label + '</span>') : '') +
                        ' <span class="badge bg-secondary ms-2">' + recordCount + ' txn</span></span>';
                    transactionsHtml += '<i class="bi bi-chevron-down"></i></button>';
                    transactionsHtml += '<div class="collapse" id="' + collapseId + '"><div class="px-3 pb-3">';
                    transactionsHtml += buildCnTransactionTable(group.records, group.paymentID, isClosedYear);
                    transactionsHtml += '</div></div></div>';
                });
            }
            $('#allTransactionsContent').html(transactionsHtml);

            // Load all academic-year / enrollment payment rows for this student
            const studentIdForHistory = btn.data('student-id') || (payment.studentID || null);
            loadEnrollmentPaymentHistory(studentIdForHistory);
            
            $('#viewMoreModal').modal('show');
        });

        function loadEnrollmentPaymentHistory(studentID) {
            const $loading = $('#enrollmentYearsLoading');
            const $wrap = $('#enrollmentYearsTableWrap');
            const $empty = $('#enrollmentYearsEmpty');
            const $body = $('#enrollmentYearsBody');
            const $summary = $('#enrollmentYearsSummary');

            $loading.show();
            $wrap.hide();
            $empty.hide();
            $body.empty();
            $summary.text('Loading…');

            if (!studentID) {
                $loading.hide();
                $empty.show().text('Student ID missing, cannot load enrollment history.');
                $summary.text('');
                return;
            }

            $.ajax({
                url: '{{ url("student_enrollment_payments") }}/' + studentID,
                method: 'GET',
                success: function(res) {
                    $loading.hide();
                    if (!res.success || !res.years || !res.years.length) {
                        $empty.show();
                        $summary.text('0 years');
                        return;
                    }

                    const fmt = (n) => parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + '/=';
                    let rows = '';
                    res.years.forEach(function(y) {
                        const owed = parseFloat(y.balance || 0);
                        const statusBadge = y.enrollment_status
                            ? '<span class="badge bg-secondary">' + y.enrollment_status + '</span>'
                            : '<span class="text-muted">-</span>';
                        const promoLabel = y.promotion_status_label || y.promotion_status || '';
                        const promoBadge = promoLabel
                            ? '<span class="badge bg-light text-dark border">' + promoLabel + '</span>'
                            : '<span class="text-muted">-</span>';
                        const payBadge = owed > 0
                            ? '<span class="badge bg-danger">Owes</span>'
                            : (y.source === 'none'
                                ? '<span class="badge bg-light text-dark border">No Bill</span>'
                                : '<span class="badge bg-success">Cleared</span>');
                        const yearBadge = (y.year_status === 'Closed')
                            ? ' <span class="badge bg-warning text-dark">Closed</span>'
                            : ((y.enrollment_status === 'Active') ? ' <span class="badge bg-info text-white">Current</span>' : '');

                        rows += '<tr' + (owed > 0 ? ' class="table-danger"' : '') + '>';
                        rows += '<td data-label="Academic Year"><strong>' + (y.year_name || 'N/A') + '</strong>' + yearBadge;
                        if (y.enrollmentID) {
                            rows += '<div class="small text-muted">Enr #' + y.enrollmentID + '</div>';
                        }
                        rows += '</td>';
                        rows += '<td data-label="Main Class">' + (y.class_name || 'N/A') + '</td>';
                        rows += '<td data-label="Enrollment">' + statusBadge + '</td>';
                        rows += '<td data-label="Promotion">' + promoBadge + '</td>';
                        rows += '<td class="text-end" data-label="Required">' + fmt(y.amount_required) + '</td>';
                        rows += '<td class="text-end text-success" data-label="Paid">' + fmt(y.amount_paid) + '</td>';
                        rows += '<td class="text-end fw-bold' + (owed > 0 ? ' text-danger' : '') + '" data-label="Owed">' + fmt(owed) + '</td>';
                        rows += '<td data-label="Status">' + payBadge + ' <small class="text-muted d-block">' + (y.payment_status || '') + '</small></td>';
                        rows += '<td class="text-center" data-label="Action">';
                        if (y.can_record && y.paymentID) {
                            const payObj = y.payment || {
                                paymentID: y.paymentID,
                                control_number: y.control_number,
                                amount_required: y.amount_required,
                                amount_paid: y.amount_paid,
                                balance: y.balance,
                                debt: y.debt,
                                fee_payments: y.fee_payments || [],
                                sponsor_balance: (y.payment && y.payment.sponsor_balance) || 0,
                                parent_balance: (y.payment && y.payment.parent_balance) || 0
                            };
                            rows += '<button type="button" class="btn btn-sm btn-success record-payment-btn" ' +
                                'data-payment-id="' + y.paymentID + '" ' +
                                'data-control-number="' + (y.control_number || '') + '" ' +
                                'data-amount-required="' + (y.amount_required || 0) + '" ' +
                                'data-amount-paid="' + (y.amount_paid || 0) + '" ' +
                                'data-balance="' + (y.balance || 0) + '" ' +
                                'data-is-sponsored="no" ' +
                                'data-academic-year="' + (y.year_name || '') + '" ' +
                                'data-academic-year-id="' + (y.academic_yearID || '') + '" ' +
                                'data-payment=\'' + JSON.stringify(payObj).replace(/'/g, "&#39;") + '\' ' +
                                'title="Record payment for ' + (y.year_name || 'this year') + '">' +
                                '<i class="bi bi-cash-coin"></i></button>';
                        } else {
                            rows += '<span class="text-muted">-</span>';
                        }
                        rows += '</td>';
                        rows += '</tr>';
                    });
                    $body.html(rows);
                    $wrap.show();

                    const s = res.summary || {};
                    $summary.text((s.years_count || res.years.length) + ' year(s) · Owes ' + fmt(s.total_owed || 0) + ' across ' + (s.years_owing || 0) + ' year(s)');
                },
                error: function() {
                    $loading.hide();
                    $empty.show().text('Failed to load enrollment payment history.');
                    $summary.text('Error');
                }
            });
        }


        // Record Payment Button Click Handler
        $(document).on('click', '.record-payment-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const amountRequired = btn.data('amount-required');
            const balance = btn.data('balance');
            const isSponsored = btn.data('is-sponsored');
            const isMultiMode = isRecordPaymentMultiMode(btn);
            const preselectFeeId = btn.data('fee-id') || btn.data('feeId') || '';
            
            let payment = parseRecordPaymentData(btn);

            let multiAccounts = [];
            if (isMultiMode) {
                multiAccounts = getViewMoreMultiAccounts();
                const matched = findMultiAccountByPaymentId(multiAccounts, paymentID);
                if (matched) {
                    payment = buildPaymentObjectFromMultiAccount(matched);
                }
            }

            $('#record_payment_id').val(paymentID);
            $('#record_control_number').val(controlNumber);

            const academicYearLabel = btn.data('academic-year')
                || (payment.academic_year && (payment.academic_year.year_name || payment.academic_year.year))
                || headerAcademicYearLabel
                || '';
            const academicYearId = btn.data('academic-year-id')
                || payment.academic_yearID
                || headerAcademicYearID
                || '';
            $('#record_academic_year_label').val(academicYearLabel || '-');
            $('#record_academic_year_id').val(academicYearId || '');

            populateRecordPaymentFeeSelect(payment, multiAccounts, {
                preselectFeeId: preselectFeeId
            });

            if (!multiAccounts.length) {
                $('#record_amount_required').val(parseFloat(amountRequired || payment.amount_required || 0).toLocaleString() + '/=');
                $('#record_balance').val(parseFloat(balance || payment.balance || 0).toLocaleString() + '/=');
                $('#recordPaymentModal').data('sponsor-balance', parseFloat(payment.sponsor_balance || 0));
                $('#recordPaymentModal').data('parent-balance', parseFloat(payment.parent_balance || 0));
            }
            
            $('#record_paid_amount').val('').removeClass('is-invalid');
            $('#record_reference_number').val('');
            $('#record_payment_date').val(new Date().toISOString().split('T')[0]);
            $('#record_payment_source').val('Cash');
            $('#record_payment_by').val('Parent');
            $('#record_bank_name').val('');
            $('#record_notes').val(academicYearLabel ? ('[Academic Year: ' + academicYearLabel + '] ') : '');

            if (isSponsored === 'yes') {
                $('#record_payment_by_container').show();
                $('#record_payment_by').val('Parent').trigger('change');
            } else {
                $('#record_payment_by_container').hide();
                $('#record_payment_by').val('Parent');
                $('#payer_balance_detail_section').hide();
            }
            
            togglePaymentMethodFields('Cash');
            $('#recordPaymentModal').modal('show');
        });

        // ===== Quick Record Payment (header button) =====
        var quickRecordState = {
            student: null,
            fees: [],
            multiAccounts: [],
            isMulti: false,
            searchTimer: null,
            searchResults: []
        };

        function resetQuickRecordModal() {
            quickRecordState.student = null;
            quickRecordState.fees = [];
            quickRecordState.multiAccounts = [];
            quickRecordState.isMulti = false;
            $('#quickRecordStudentSearch').val('').prop('disabled', false);
            $('#quickRecordStudentResults').hide().empty();
            $('#quickRecordSelectedStudent').hide();
            $('#quickRecordFeeSection').hide();
            $('#quickRecordFeeSelect').html('<option value="">- Select fee -</option>');
            $('#quickRecordFeeEmpty').hide();
            $('#quickRecordFeeHint').text('');
            $('#quickRecordContinueBtn').prop('disabled', true);
        }

        function openRecordPaymentFromQuick(feeOption) {
            if (!feeOption || !feeOption.paymentID) return;

            var payment = feeOption.payment || {};
            var multiAccounts = quickRecordState.isMulti ? (quickRecordState.multiAccounts || []) : [];
            var preselectFeeId = (feeOption.feeID && feeOption.feeID !== 'any') ? feeOption.feeID : '';
            var academicYearLabel = feeOption.academic_year || headerAcademicYearLabel || '';
            var academicYearId = feeOption.academic_year_id || headerAcademicYearID || '';
            var isSponsored = quickRecordState.student && quickRecordState.student.has_sponsor ? 'yes' : 'no';

            $('#record_payment_id').val(feeOption.paymentID);
            $('#record_control_number').val(feeOption.control_number || payment.control_number || '');
            $('#record_academic_year_label').val(academicYearLabel || '-');
            $('#record_academic_year_id').val(academicYearId || '');

            populateRecordPaymentFeeSelect(payment, multiAccounts, {
                preselectFeeId: preselectFeeId
            });

            if (!multiAccounts.length) {
                $('#record_amount_required').val(parseFloat(feeOption.amount_required || payment.amount_required || 0).toLocaleString() + '/=');
                $('#record_balance').val(parseFloat(feeOption.balance || payment.balance || 0).toLocaleString() + '/=');
                $('#recordPaymentModal').data('sponsor-balance', parseFloat(feeOption.sponsor_balance || payment.sponsor_balance || 0));
                $('#recordPaymentModal').data('parent-balance', parseFloat(feeOption.parent_balance || payment.parent_balance || 0));
            } else {
                // Ensure selected fee option drives paymentID / balances
                $('#record_fee_id').trigger('change');
            }

            $('#record_paid_amount').val('').removeClass('is-invalid');
            $('#record_reference_number').val('');
            $('#record_payment_date').val(new Date().toISOString().split('T')[0]);
            $('#record_payment_source').val('Cash');
            $('#record_payment_by').val('Parent');
            $('#record_bank_name').val('');
            $('#record_notes').val(academicYearLabel ? ('[Academic Year: ' + academicYearLabel + '] ') : '');

            if (isSponsored === 'yes') {
                $('#record_payment_by_container').show();
                $('#record_payment_by').val('Parent').trigger('change');
            } else {
                $('#record_payment_by_container').hide();
                $('#record_payment_by').val('Parent');
                $('#payer_balance_detail_section').hide();
            }

            togglePaymentMethodFields('Cash');
            $('#quickRecordPaymentModal').modal('hide');
            setTimeout(function() {
                $('#recordPaymentModal').modal('show');
            }, 350);
        }

        $('#quickRecordPaymentBtn').on('click', function() {
            resetQuickRecordModal();
            $('#quickRecordPaymentModal').modal('show');
            setTimeout(function() { $('#quickRecordStudentSearch').focus(); }, 300);
        });

        $('#quickRecordClearStudent').on('click', function() {
            resetQuickRecordModal();
            $('#quickRecordStudentSearch').focus();
        });

        $('#quickRecordStudentSearch').on('input', function() {
            var q = ($(this).val() || '').trim();
            $('#quickRecordContinueBtn').prop('disabled', true);
            if (quickRecordState.searchTimer) clearTimeout(quickRecordState.searchTimer);
            if (q.length < 2) {
                $('#quickRecordStudentResults').hide().empty();
                return;
            }
            quickRecordState.searchTimer = setTimeout(function() {
                $('#quickRecordStudentResults').show().html('<div class="p-2 text-muted small">Searching...</div>');
                $.get('{{ route("quick_record_search_students") }}', { q: q })
                    .done(function(res) {
                        var list = (res && res.students) ? res.students : [];
                        quickRecordState.searchResults = list;
                        if (!list.length) {
                            $('#quickRecordStudentResults').html('<div class="p-2 text-muted small">No students found</div>');
                            return;
                        }
                        var html = '';
                        list.forEach(function(s, idx) {
                            html += '<button type="button" class="quick-student-item" data-idx="' + idx + '">'
                                + '<div class="qs-name">' + $('<div>').text(s.name || '').html() + '</div>'
                                + '<div class="qs-meta">' + $('<div>').text((s.admission_number || '') + ' · ' + (s.class_label || '') + (s.status ? ' · ' + s.status : '')).html() + '</div>'
                                + '</button>';
                        });
                        $('#quickRecordStudentResults').html(html);
                    })
                    .fail(function() {
                        $('#quickRecordStudentResults').html('<div class="p-2 text-danger small">Search failed</div>');
                    });
            }, 280);
        });

        $(document).on('click', '#quickRecordStudentResults .quick-student-item', function() {
            var idx = parseInt($(this).data('idx'), 10);
            var s = quickRecordState.searchResults[idx];
            if (!s || !s.studentID) return;
            quickRecordState.student = s;
            $('#quickRecordStudentSearch').val(s.name || '').prop('disabled', true);
            $('#quickRecordStudentResults').hide().empty();
            $('#quickRecordStudentName').text(s.name || '-');
            $('#quickRecordStudentMeta').text((s.admission_number || '') + ' · ' + (s.class_label || ''));
            $('#quickRecordSelectedStudent').show();
            $('#quickRecordFeeSection').show();
            $('#quickRecordFeeSelect').html('<option value="">Loading fees...</option>').prop('disabled', true);
            $('#quickRecordFeeEmpty').hide();
            $('#quickRecordContinueBtn').prop('disabled', true);

            $.get('{{ url("quick_record_student_fees") }}/' + s.studentID, {
                academic_year_id: headerAcademicYearID || '',
                use_request_year: headerAcademicYearID ? 1 : 0
            }).done(function(res) {
                quickRecordState.fees = (res && res.fees) ? res.fees : [];
                quickRecordState.multiAccounts = (res && res.multi_accounts) ? res.multi_accounts : [];
                quickRecordState.isMulti = !!(res && res.is_multi_account);
                if (res && res.student) {
                    quickRecordState.student = Object.assign({}, quickRecordState.student, res.student);
                }

                var $sel = $('#quickRecordFeeSelect').empty().prop('disabled', false);
                if (!quickRecordState.fees.length) {
                    $sel.append('<option value="">- No fees -</option>');
                    $('#quickRecordFeeEmpty').show();
                    $('#quickRecordFeeHint').text(res.message || '');
                    return;
                }
                $('#quickRecordFeeEmpty').hide();
                $sel.append('<option value="">- Select fee -</option>');
                quickRecordState.fees.forEach(function(f, idx) {
                    $sel.append($('<option></option>').attr('value', String(idx)).text(f.label || f.fee_name));
                });
                $('#quickRecordFeeHint').text('Academic Year: ' + ((res && res.academic_year) || headerAcademicYearLabel || '-'));
            }).fail(function() {
                $('#quickRecordFeeSelect').html('<option value="">Error</option>').prop('disabled', true);
                $('#quickRecordFeeEmpty').show().text('Failed to load fees.');
            });
        });

        $('#quickRecordFeeSelect').on('change', function() {
            var idx = $(this).val();
            $('#quickRecordContinueBtn').prop('disabled', idx === '' || idx === null);
        });

        $('#quickRecordContinueBtn').on('click', function() {
            var idx = $('#quickRecordFeeSelect').val();
            if (idx === '' || idx === null) return;
            var feeOption = quickRecordState.fees[parseInt(idx, 10)];
            if (!feeOption) return;
            openRecordPaymentFromQuick(feeOption);
        });

        // Fee Selection Change Handler
        $(document).on('change', '#record_fee_id', function() {
            const selectedOption = $(this).find('option:selected');
            const feeID = $(this).val();
            
            $('#record_paid_amount').val('').removeClass('is-invalid');

            if (selectedOption.data('payment-id')) {
                applyRecordPaymentFeeSelection(selectedOption);
            }

            if (!feeID || feeID === 'any') {
                $('#selected_fee_details').hide();
                $('#overall_payment_details').show();
            } else {
                const req = parseFloat(selectedOption.data('required') || 0);
                const bal = parseFloat(selectedOption.data('balance') || 0);
                
                $('#selected_fee_required').val(req.toLocaleString() + '/=');
                $('#selected_fee_balance').val(bal.toLocaleString() + '/=');
                $('#selected_fee_balance_raw').val(bal);
                
                $('#selected_fee_details').show();
                $('#overall_payment_details').hide();
            }
        });

        // Payer Selection Change Handler
        $(document).on('change', '#record_payment_by', function() {
            const payer = $(this).val();
            const sponsorBal = parseFloat($('#recordPaymentModal').data('sponsor-balance') || 0);
            const parentBal = parseFloat($('#recordPaymentModal').data('parent-balance') || 0);
            
            $('#record_paid_amount').val('').removeClass('is-invalid');

            if (payer === 'Sponsor') {
                $('#payer_label_name').text('Sponsor');
                $('#current_payer_balance_val').text(sponsorBal.toLocaleString() + '/=');
                $('#current_payer_balance_raw').val(sponsorBal);
            } else {
                $('#payer_label_name').text('Parent');
                $('#current_payer_balance_val').text(parentBal.toLocaleString() + '/=');
                $('#current_payer_balance_raw').val(parentBal);
            }
            
            if ($('#record_payment_by_container').is(':visible')) {
                $('#payer_balance_detail_section').show();
            }
        });

        // Real-time Validation for Paid Amount
        $(document).on('input', '#record_paid_amount', function() {
            const amount = parseFloat($(this).val() || 0);
            const feeID = $('#record_fee_id').val();
            const isSponsoredView = $('#record_payment_by_container').is(':visible');
            
            let limit = 0;
            let limitLabel = "";

            if (feeID === 'any') {
                const balanceText = $('#record_balance').val() || "0";
                limit = parseFloat(balanceText.replace(/[^0-9.]/g, ''));
                limitLabel = "Total Balance";
            } else {
                limit = parseFloat($('#selected_fee_balance_raw').val() || 0);
                limitLabel = "Fee Balance";
            }

            // Check against Payer Limit if sponsorship applies
            if (isSponsoredView) {
                const payerLimit = parseFloat($('#current_payer_balance_raw').val() || 0);
                if (payerLimit < limit) {
                    limit = payerLimit;
                    limitLabel = $('#record_payment_by').val() + "'s Remaining Obligation";
                }
            }

            if (amount > limit) {
                $(this).addClass('is-invalid');
                const feedback = `Amount cannot exceed ${limitLabel} (${limit.toLocaleString()}/=)`;
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after(`<div class="invalid-feedback">${feedback}</div>`);
                } else {
                    $(this).next('.invalid-feedback').text(feedback);
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Toggle payment method fields
        function togglePaymentMethodFields(paymentMethod) {
            const referenceField = $('#record_reference_number');
            const referenceContainer = $('#record_reference_number_container');
            const referenceRequired = $('#record_reference_required');
            const bankNameContainer = $('#record_bank_name_container');
            const bankNameField = $('#record_bank_name');
            
            if (paymentMethod === 'Bank') {
                // Bank: Reference number required, show bank name
                referenceContainer.show();
                referenceField.prop('required', true);
                referenceRequired.show();
                bankNameContainer.show();
                bankNameField.prop('required', true);
            } else {
                // Cash: Reference number hidden completely, hide bank name
                referenceContainer.hide();
                referenceField.prop('required', false);
                referenceField.val(''); // Clear value
                referenceRequired.hide();
                bankNameContainer.hide();
                bankNameField.prop('required', false);
                bankNameField.val('');
            }
        }
        
        // Payment method change handler
        $(document).on('change', '#record_payment_source', function() {
            const paymentMethod = $(this).val();
            togglePaymentMethodFields(paymentMethod);
        });

        // Record Payment Form Submission
        $(document).on('submit', '#recordPaymentForm', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Recording...');
            
            $.ajax({
                url: '/record_payment',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#recordPaymentModal').modal('hide');
                            $('#viewMoreModal').modal('hide');
                            loadPaymentsData(); // Reload payments table
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to record payment'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to record payment';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // View Payment Records Button Click Handler
        $(document).on('click', '.view-payment-records-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Get payment data
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const feeType = btn.data('fee-type');
            
            // Set modal fields
            $('#view_records_control_number').val(controlNumber);
            $('#view_records_fee_type').val(feeType);
            
            // Show loading
            $('#paymentRecordsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payment records...</p></div>');
            
            // Close viewMoreModal first if it's open, then open payment records modal
            if ($('#viewMoreModal').hasClass('show')) {
                $('#viewMoreModal').modal('hide');
                // Wait for viewMoreModal to close, then open payment records modal
                $('#viewMoreModal').one('hidden.bs.modal', function() {
                    $('#viewPaymentRecordsModal').modal('show');
                });
            } else {
                // If viewMoreModal is not open, open payment records modal immediately
                $('#viewPaymentRecordsModal').modal('show');
            }
            
            // Load payment records
            $.ajax({
                url: '/get_payment_records',
                method: 'GET',
                data: {
                    paymentID: paymentID
                },
                success: function(response) {
                    if (response.success && response.records) {
                        let html = '';
                        
                        if (response.records.length > 0) {
                            html += '<div class="table-responsive">';
                            html += '<table class="table table-bordered table-hover">';
                            html += '<thead style="background-color: #940000; color: white;">';
                            html += '<tr>';
                            html += '<th>#</th>';
                            html += '<th>Payment Date</th>';
                            html += '<th>Paid Amount (TZS)</th>';
                            html += '<th>Payment Method</th>';
                            html += '<th>Paid By</th>';
                            html += '<th>Reference Number</th>';
                            html += '<th>Status</th>';
                            html += '<th>Notes</th>';
                            html += '<th class="text-center">Actions</th>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody>';
                            
                            let totalPaid = 0;
                            response.records.forEach(function(record, index) {
                                totalPaid += parseFloat(record.paid_amount || 0);
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
                                html += '<td>' + (record.payment_date || 'N/A') + '</td>';
                                html += '<td class="text-end text-success fw-bold">' + parseFloat(record.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                                html += '<td><span class="badge ' + (record.payment_source === 'Bank' ? 'bg-primary' : 'bg-success') + '">' + (record.payment_source || 'N/A') + '</span></td>';
                                html += '<td><span class="badge ' + (record.payment_by === 'Sponsor' ? 'bg-info' : 'bg-light text-dark border') + '">' + (record.payment_by || 'Parent') + '</span></td>';
                                html += '<td>' + (record.reference_number || 'N/A') + '</td>';
                                html += '<td class="text-center">';
                                if (record.is_verified) {
                                    html += '<span class="badge bg-success" title="Verified"><i class="bi bi-patch-check-fill"></i> Verified</span>';
                                } else {
                                    html += '<span class="badge bg-secondary" title="Pending Verification"><i class="bi bi-patch-exclamation"></i> Unverified</span>';
                                }
                                html += '</td>';
                                html += '<td>' + (record.notes || '-') + '</td>';
                                html += '<td class="text-center">';
                                html += '<div class="btn-group btn-group-sm" role="group">';
                                
                                if (!record.is_verified) {
                                    html += '<button class="btn btn-sm btn-success verify-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'title="Verify Payment">';
                                    html += '<i class="bi bi-shield-check"></i>';
                                    html += '</button>';
                                    
                                    html += '<button class="btn btn-sm btn-warning edit-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'data-paid-amount="' + record.paid_amount + '" ';
                                    html += 'data-payment-date="' + (record.payment_date || '') + '" ';
                                    html += 'data-payment-source="' + (record.payment_source || '') + '" ';
                                    html += 'data-reference-number="' + (record.reference_number || '') + '" ';
                                    html += 'data-bank-name="' + (record.bank_name || '') + '" ';
                                    html += 'data-notes="' + (record.notes || '') + '" ';
                                    html += 'title="Edit Payment Record">';
                                    html += '<i class="bi bi-pencil"></i>';
                                    html += '</button>';
                                    
                                    html += '<button class="btn btn-sm btn-danger delete-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'data-paid-amount="' + record.paid_amount + '" ';
                                    html += 'title="Delete Payment Record">';
                                    html += '<i class="bi bi-trash"></i>';
                                    html += '</button>';
                                } else {
                                    html += '<button class="btn btn-sm btn-info unverify-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'title="Cancel Verification">';
                                    html += '<i class="bi bi-shield-x"></i>';
                                    html += '</button>';
                                    
                                    html += '<span class="btn btn-sm btn-light disabled" title="Verified records cannot be edited"><i class="bi bi-lock-fill"></i></span>';
                                }
                                
                                html += '</div>';
                                html += '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody>';
                            html += '<tfoot style="background-color: #f8f9fa;">';
                            html += '<tr>';
                            html += '<th colspan="2" class="text-end">Total Paid:</th>';
                            html += '<th class="text-end text-success fw-bold">' + totalPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</th>';
                            html += '<th colspan="6"></th>';
                            html += '</tr>';
                            html += '</tfoot>';
                            html += '</table>';
                            html += '</div>';
                        } else {
                            html += '<div class="alert alert-info text-center">';
                            html += '<i class="bi bi-info-circle"></i> No payment records found for this payment.';
                            html += '</div>';
                        }
                        
                        $('#paymentRecordsContent').html(html);
                    } else {
                        $('#paymentRecordsContent').html('<div class="alert alert-warning text-center"><i class="bi bi-exclamation-triangle"></i> ' + (response.message || 'Failed to load payment records') + '</div>');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to load payment records';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#paymentRecordsContent').html('<div class="alert alert-danger text-center"><i class="bi bi-x-circle"></i> ' + errorMessage + '</div>');
                }
            });
        });

            // Verify Payment Record
            $(document).on('click', '.verify-payment-record-btn', function() {
                const recordID = $(this).data('record-id');
                const paymentID = $(this).data('payment-id');
                
                if (confirm('Confirm that you have received this payment?')) {
                    const btn = $(this);
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    
                    $.ajax({
                        url: '/verify_payment',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            recordID: recordID
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                // Refresh payment records list
                                $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').trigger('click');
                                // Refresh main table
                                loadPaymentsData();
                            } else {
                                toastr.error(response.message || 'Kosa limetokea');
                                btn.prop('disabled', false).html('<i class="bi bi-shield-check"></i>');
                            }
                        },
                        error: function() {
                            toastr.error('Kosa la mawasiliano');
                            btn.prop('disabled', false).html('<i class="bi bi-shield-check"></i>');
                        }
                    });
                }
            });
            
            // Unverify Payment Record
            $(document).on('click', '.unverify-payment-record-btn', function() {
                const recordID = $(this).data('record-id');
                const paymentID = $(this).data('payment-id');
                
                if (confirm('Are you sure you want to remove this verification?')) {
                    const btn = $(this);
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    
                    $.ajax({
                        url: '/unverify_payment',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            recordID: recordID
                        },
                        success: function(response) {
                            if (response.success) {
                                // Refresh payment records list
                                $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').trigger('click');
                                // Refresh main table
                                loadPaymentsData();
                            } else {
                                toastr.error(response.message || 'Kosa limetokea');
                                btn.prop('disabled', false).html('<i class="bi bi-shield-x"></i>');
                            }
                        },
                        error: function() {
                            toastr.error('Kosa la mawasiliano');
                            btn.prop('disabled', false).html('<i class="bi bi-shield-x"></i>');
                        }
                    });
                }
            });
            
            // Handle verification UI (ensure toastr is available for these calls)
            function showNotification(message, type) {
                if (typeof toastr !== 'undefined') {
                    toastr[type](message);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type === 'success' ? 'success' : 'error',
                        title: type === 'success' ? 'Success' : 'Error',
                        text: message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(message);
                }
            }

        // Edit Payment Record Button Click Handler
        $(document).on('click', '.edit-payment-record-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Get record data
            const recordID = btn.data('record-id');
            const paymentID = btn.data('payment-id');
            const paidAmount = btn.data('paid-amount');
            const paymentDate = btn.data('payment-date');
            const paymentSource = btn.data('payment-source');
            const referenceNumber = btn.data('reference-number');
            const bankName = btn.data('bank-name');
            const notes = btn.data('notes');
            
            // Set modal fields
            $('#edit_record_id').val(recordID);
            $('#edit_payment_id').val(paymentID);
            $('#edit_paid_amount').val(paidAmount);
            $('#edit_payment_date').val(paymentDate);
            $('#edit_payment_source').val(paymentSource);
            $('#edit_reference_number').val(referenceNumber || '');
            $('#edit_bank_name').val(bankName || '');
            $('#edit_notes').val(notes || '');
            
            // Show/hide reference number and bank name based on payment source
            if (paymentSource === 'Bank') {
                $('#edit_reference_number_group').show();
                $('#edit_bank_name_group').show();
                $('#edit_reference_number').prop('required', true);
                $('#edit_bank_name').prop('required', true);
            } else {
                $('#edit_reference_number_group').hide();
                $('#edit_bank_name_group').hide();
                $('#edit_reference_number').prop('required', false);
                $('#edit_bank_name').prop('required', false);
            }
            
            // Open modal
            $('#editPaymentRecordModal').modal('show');
        });
        
        // Payment Source Change Handler for Edit Modal
        $('#edit_payment_source').on('change', function() {
            const paymentSource = $(this).val();
            if (paymentSource === 'Bank') {
                $('#edit_reference_number_group').show();
                $('#edit_bank_name_group').show();
                $('#edit_reference_number').prop('required', true);
                $('#edit_bank_name').prop('required', true);
            } else {
                $('#edit_reference_number_group').hide();
                $('#edit_bank_name_group').hide();
                $('#edit_reference_number').prop('required', false);
                $('#edit_bank_name').prop('required', false);
                $('#edit_reference_number').val('');
                $('#edit_bank_name').val('');
            }
        });
        
        // Edit Payment Record Form Submission
        $(document).on('submit', '#editPaymentRecordForm', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
            
            $.ajax({
                url: '/update_payment_record',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#editPaymentRecordModal').modal('hide');
                            // Reload payment records
                            const paymentID = $('#edit_payment_id').val();
                            $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').first().click();
                            loadPaymentsData(); // Reload payments table
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to update payment record'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update payment record';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Delete Payment Record Button Click Handler
        $(document).on('click', '.delete-payment-record-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const recordID = btn.data('record-id');
            const paymentID = btn.data('payment-id');
            const paidAmount = btn.data('paid-amount');
            
            Swal.fire({
                title: 'Delete Payment Record?',
                html: `Are you sure you want to delete this payment record?<br><strong>Amount: TZS ${parseFloat(paidAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}/=</strong><br><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: '/delete_payment_record',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            recordID: recordID,
                            paymentID: paymentID
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    // Reload payment records
                                    $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').first().click();
                                    loadPaymentsData(); // Reload payments table
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to delete payment record',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to delete payment record';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Send Control Number, preview + editable template modal
        $(document).on('click', '.send-control-number-btn', function(e) {
            e.preventDefault();
            sendControlNumberSmsWithPreview($(this).data('payment-id'), { button: $(this) });
        });

        // Resend SMS (legacy button)
        $(document).on('click', '.resend-sms-btn', function(e) {
            e.preventDefault();
            sendControlNumberSmsWithPreview($(this).data('payment-id'), { button: $(this) });
        });

        // Update Payment
        $(document).on('click', '.update-payment-btn', function(e) {
            e.preventDefault();
            const paymentID = $(this).data('payment-id');
            const amountRequired = $(this).data('amount-required');
            const amountPaid = $(this).data('amount-paid');
            
            $('#update_payment_id').val(paymentID);
            $('#update_amount_required').val('TZS ' + parseFloat(amountRequired).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#update_amount_paid_current').val('TZS ' + parseFloat(amountPaid).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#update_amount_paid').val('');
            $('#update_payment_reference').val('');
            $('#update_notes').val('');
            
            $('#updatePaymentModal').modal('show');
        });

        // Update Payment Form Submission
        $('#updatePaymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const paymentID = $('#update_payment_id').val();
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
            
            $.ajax({
                url: `/update_payment_status/${paymentID}`,
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#updatePaymentModal').modal('hide');
                            loadPaymentsData(); // Reload data via AJAX
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update payment';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Resend Individual Control SMS, shared editable template modal
        $(document).on('click', '.resend-control-sms-btn', function(e) {
            e.preventDefault();
            sendControlNumberSmsWithPreview($(this).data('payment-id'), { button: $(this) });
        });



        // Load initial data on startup
        console.log('Starting Payments Management App...');
        loadPaymentsData();
    }
    
    // App is started via waitForJQuery at the top
})();
</script>

<!-- ===== FEE SMS PROGRESS MODAL ===== -->
<div class="modal fade" id="feeSmsProgressModal" tabindex="-1" role="dialog" aria-labelledby="feeSmsProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="feeSmsProgressModalLabel">
                    <i class="bi bi-send-check me-2"></i> <span id="feeSmsModalTitle">Send SMS</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 shadow-sm mb-3" id="feeSmsInfoAlert">
                    <i class="bi bi-info-circle-fill"></i> <span id="feeSmsModalDesc">Select parents/sponsors to receive SMS.</span>
                </div>

                <!-- Recipient Tabs -->
                <ul class="nav nav-pills nav-fill mb-3" id="feeSmsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="parent-sms-tab" data-toggle="tab" data-target="#parent-sms" type="button" role="tab" data-type="parent">
                            <i class="bi bi-people-fill"></i> Parents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="sponsor-sms-tab" data-toggle="tab" data-target="#sponsor-sms" type="button" role="tab" data-type="sponsor">
                            <i class="bi bi-award-fill"></i> Sponsors
                        </button>
                    </li>
                </ul>

                <!-- Loading state -->
                <div id="feeSmsLoadingState" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Downloading recipient list...</p>
                </div>

                <!-- Recipients table (hidden until loaded) -->
                <div id="feeSmsRecipientsSection" class="d-none">
                    <div class="tab-content" id="feeSmsTabContent">
                        <div class="tab-pane fade show active" id="parent-sms" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="feeSelectAllParents">
                                    <label class="custom-control-label font-weight-bold" for="feeSelectAllParents">Select All</label>
                                </div>
                                <span class="badge badge-primary-custom" id="feeSelectedCount" style="font-size: 0.95rem;">Recipients: 0</span>
                            </div>

                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover border" id="feeSmsTable">
                                    <thead class="bg-light sticky-top">
                                        <tr id="feeSmsTableHeader">
                                            <th style="width:40px;"></th>
                                            <th>Recipient</th>
                                            <th>Phone</th>
                                            <th class="text-center" style="width:110px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="feeSmsRecipientsList">
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Progress section (hidden until sending) -->
                    <div id="feeSmsProgressArea" class="mt-3 d-none">
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-primary-custom">Sending Progress</span>
                            <span id="feeSmsProgressText" class="small">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 24px; border-radius: 0; border: 1px solid #ddd;">
                            <div id="feeSmsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width: 0%; border-radius: 0;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div id="feeSmsDeliverySummary" class="text-center small mt-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light text-primary-custom border" data-dismiss="modal" id="feeBtnCancelSms">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="feeStartSendingSms" disabled>
                    <i class="bi bi-eye"></i> Review SMS & Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== SMS TEMPLATE EDITOR MODAL (single modal for all SMS actions) ===== -->
<div class="modal fade" id="smsTemplateModal" tabindex="-1" role="dialog" aria-labelledby="smsTemplateModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="smsTemplateModalLabel">
                    <i class="bi bi-chat-left-text me-2"></i> <span id="smsTemplateModalTitle">Confirm SMS</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 shadow-sm mb-3" id="smsTemplateModalDesc"></div>
                <div id="smsTemplateEditorList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light text-primary-custom border" id="smsTemplateCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="smsTemplateConfirmBtn">
                    <i class="bi bi-send"></i> Yes, Send SMS
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
