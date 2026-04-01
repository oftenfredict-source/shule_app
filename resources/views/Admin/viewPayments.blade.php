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

    /* Statistics Backgrounds */
    .stat-card-accent {
        background-color: #FFF5F5 !important;
        border-left: 4px solid #940000 !important;
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
        background-color: #FFF5F5;
        color: #940000;
        font-weight: bold;
        border-bottom: 2px solid #940000;
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
                                <i class="bi bi-calendar"></i> Academic Year
                            </label>
                            <select class="form-select" id="filterYear">
                                @php
                                    $defaultYear = $defaultYear ?? date('Y');
                                    $availableYears = $availableYears ?? [];
                                @endphp
                                @if(count($availableYears) > 0)
                                    @foreach($availableYears as $academicYear)
                                        @php
                                            $yearValue = is_array($academicYear) ? $academicYear['year'] : $academicYear;
                                            $yearName = is_array($academicYear) ? ($academicYear['year_name'] ?? $academicYear['year']) : $yearValue;
                                            $status = is_array($academicYear) ? ($academicYear['status'] ?? '') : '';
                                            $isSelected = $yearValue == $defaultYear;
                                        @endphp
                                        <option value="{{ $yearValue }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $yearName }} @if($status) ({{ $status }}) @endif
                                        </option>
                                    @endforeach
                                @else
                                    <option value="{{ $defaultYear }}" selected>{{ $defaultYear }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-book"></i> Class
                            </label>
                            <select class="form-select" id="filterClass">
                                <option value="">All Classes</option>
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
                                <i class="bi bi-person-check"></i> Status
                            </label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Graduated">Graduated</option>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Control Numbers & Payments List
                    </h5>
                </div>
                <div class="card-body p-0">
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
            </div>
        </div>
    </div>
</div>

<!-- View More Modal -->
<div class="modal fade" id="viewMoreModal" tabindex="-1" aria-labelledby="viewMoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 1200px;">
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
                <div class="row mb-4 align-items-center">
                    <div class="col-md-2 text-center">
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
                    <div class="col-md-6">
                        <h4 class="text-primary-custom mb-1" id="view_student_name">-</h4>
                        <p class="mb-0 text-muted"> <i class="bi bi-book"></i> <span id="view_student_class">-</span> | <i class="bi bi-person-badge"></i> <span id="view_student_admission">-</span></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="p-2 stat-card-accent rounded border">
                            <small class="text-muted d-block font-weight-bold">Control Number</small>
                            <span class="h5 mb-0 text-primary-custom fw-bold" id="view_control_number">-</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary Dashboard -->
                <div class="row mb-4 g-2">
                    <div class="col">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Fee Required</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_total_fee_required">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Total Paid</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_total_paid">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Bill Balance</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_bill_balance">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 stat-card-accent h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1 font-weight-bold" style="font-size: 0.75rem;">Prev. Year Debt</small>
                                <h6 class="mb-0 text-dark fw-bold" id="view_old_debt">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 bg-primary-custom text-white h-100 shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #7a0000 100%) !important;">
                            <div class="card-body p-2 text-center">
                                <small class="d-block mb-1 text-white-50 font-weight-bold" style="font-size: 0.75rem;">Total Outstanding</small>
                                <h6 class="mb-0 fw-bold text-white" id="view_total_outstanding">0.00</h6>
                            </div>
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

                <!-- Transaction History -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-bottom-0 p-0">
                        <button class="btn btn-link w-100 text-start text-decoration-none p-3 d-flex justify-content-between align-items-center" 
                                type="button" data-toggle="collapse" data-target="#allTransactionsCollapse" aria-expanded="false">
                            <span class="fw-bold text-dark"><i class="bi bi-clock-history me-2"></i> All Transactions of Control Number</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse" id="allTransactionsCollapse">
                        <div class="card-body pt-0">
                            <div id="allTransactionsContent">
                                <!-- Transaction history will be dynamically inserted here -->
                            </div>
                        </div>
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
                        <textarea name="notes" id="record_notes" class="form-control" rows="3" placeholder="Optional notes about this payment"></textarea>
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
                    <p class="mt-3 text-muted small">Tafadhali subiri kidogo wakati tunaandaa ripoti yako rasmi.</p>
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

        // Variable to store the current AJAX request to allow aborting previous ones
        var currentPaymentsAjax = null;

        // Function to load payments data via AJAX
        function loadPaymentsData() {
            // Abort previous request if it exists
            if (currentPaymentsAjax) {
                currentPaymentsAjax.abort();
            }

            var year = $('#filterYear').val();
            var classID = $('#filterClass').val();
            var subclassID = $('#filterSubclass').val();
            var studentStatus = $('#filterStatus').val();
            var paymentStatus = $('#filterPaymentStatus').val();
            var searchStudentName = $('#searchStudentName').val();

            // Clear previous table instance
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#paymentsTable')) {
                $('#paymentsTable').DataTable().destroy();
            }

            // Show loading
            $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payments...</p></td></tr>');

            currentPaymentsAjax = $.ajax({
                url: '{{ route("get_payments_ajax") }}',
                type: 'GET',
                data: {
                    year: year,
                    class_id: classID,
                    subclass_id: subclassID,
                    student_status: studentStatus,
                    payment_status: paymentStatus,
                    search_student_name: searchStudentName,
                    sponsorship_filter: $('#filterSponsorship').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var isClosedYear = response.is_closed_year || false;
                        var html = '';
                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                var student = item.student;
                                var paymentsData = item.payment || {}; // Renamed to avoid conflict
                                var totals = item.totals || {};
                                
                                // Generate placeholder
                                var firstName = student.first_name || '';
                                var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';
                                var fullName = (student.first_name || '') + ' ' + (student.last_name || '');
                                var placeholderColor = getPlaceholderColor(fullName);

                                // Photo HTML
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

                                var studentFullName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                                var studentClass = 'N/A';
                                if (student.subclass) {
                                    var mainClass = student.subclass.class_name || '';
                                    var subclass = student.subclass.subclass_name || '';
                                    studentClass = (mainClass && subclass) ? mainClass + ' ' + subclass : (subclass || mainClass || 'N/A');
                                }

                                // Column 1: Student Details
                                var col1 = '<div class="d-flex align-items-center">' +
                                    photoHtml +
                                    '<div>' +
                                    '<div class="fw-bold text-dark">' + studentFullName.trim() + '</div>' +
                                    '<div class="small text-muted">' + (student.admission_number || 'No Adm') + ' | ' + studentClass + '</div>' +
                                    '<div class="small badge bg-light text-dark border">' + (item.academic_year || '') + '</div>' +
                                    '</div>' +
                                    '</div>';

                                // Column 2: Payment Type
                                var colType = '';
                                if (student.sponsor) {
                                    colType = '<div class="text-center"><span class="badge bg-primary-custom text-white mb-1" style="font-size: 0.7rem; padding: 4px 8px; display: inline-block; width: 100px;">' +
                                        '<i class="bi bi-shield-check me-1"></i>Sponsored' +
                                        '</span><br><small class="text-muted">' + student.sponsor.percentage + '% Cover</small> <br> <small class="text-muted" style="font-size: 0.65rem;">' + student.sponsor.sponsor_name + '</small></div>';
                                } else {
                                    colType = '<div class="text-center"><span class="badge bg-secondary text-white" style="font-size: 0.7rem; padding: 4px 8px; display: inline-block; width: 100px;">' +
                                        '<i class="bi bi-person me-1"></i>Own Payment' +
                                        '</span></div>';
                                }

                                // Column 3: Bill Summary
                                var col2 = '<div class="bill-container">' +
                                    '<div class="bill-item text-muted small">Required: <span class="fw-bold text-dark">' + parseFloat(totals.total_required || 0).toLocaleString() + '</span></div>' +
                                    '<div class="bill-item text-muted small">Paid: <span class="fw-bold text-success">' + parseFloat(totals.total_paid || 0).toLocaleString() + '</span></div>' +
                                    '</div>';

                                // Column 4: Balance & Status
                                var overallStatus = totals.overall_status || 'Pending';
                                var statusClass = 'bg-secondary';
                                if (overallStatus === 'Paid') statusClass = 'bg-success';
                                else if (overallStatus === 'Overpaid') statusClass = 'bg-info';
                                else if (overallStatus.includes('Incomplete') || overallStatus === 'Partial') statusClass = 'bg-warning text-dark';
                                
                                var col3 = '<div class="text-end">' +
                                    '<div class="fw-bold text-danger mb-1">' + parseFloat(totals.total_balance || 0).toLocaleString() + '/=</div>' +
                                    '<span class="badge ' + statusClass + ' mb-1">' + overallStatus + '</span>';
                                
                                // Can Start Icon
                                if (totals.can_start_school) {
                                    col3 += ' <i class="bi bi-check-circle-fill text-success" title="Can Start School"></i>';
                                }
                                col3 += '</div>';

                                // Column 5: Actions
                                var paymentsJson = JSON.stringify(paymentsData).replace(/'/g, "&#39;");
                                var sponsorJson = student.sponsor ? JSON.stringify(student.sponsor).replace(/'/g, "&#39;") : '';
                                var col4 = '<button class="btn btn-primary-custom btn-sm view-more-btn" ' +
                                    'data-student-id="' + student.studentID + '" ' +
                                    'data-student-name="' + studentFullName.trim() + '" ' +
                                    'data-student-admission="' + (student.admission_number || 'N/A') + '" ' +
                                    'data-student-class="' + studentClass + '" ' +
                                    'data-student-photo="' + (student.photo || '') + '" ' +
                                    'data-student-first-letter="' + firstLetter + '" ' +
                                    'data-student-placeholder-color="' + placeholderColor + '" ' +
                                    'data-student-sponsor=\'' + sponsorJson + '\' ' +
                                    'data-is-closed-year="' + (isClosedYear ? 'true' : 'false') + '" ' +
                                    'data-payment=\'' + paymentsJson + '\' ' +
                                    'data-totals=\'' + JSON.stringify(totals).replace(/'/g, "&#39;") + '\' ' +
                                    'title="View More Details"><i class="bi bi-eye"></i> Details</button>';

                                html += '<tr>' +
                                    '<td data-label="Student">' + col1 + '</td>' +
                                    '<td data-label="Payment Type" class="text-end">' + colType + '</td>' +
                                    '<td data-label="Bill Summary" class="text-end">' + col2 + '</td>' +
                                    '<td data-label="Status" class="text-end">' + col3 + '</td>' +
                                    '<td data-label="Actions" class="text-center">' + col4 + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="5" class="text-center py-4 text-muted"><p class="mt-2">No payments found</p></td></tr>';
                        }
                        
                        $('#paymentsTableBody').html(html);
                        
                        // Update statistics
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
                            
                            // Log additional statistics for debugging
                            if (stats.total_students !== undefined) {
                                console.log('Filter Statistics:', {
                                    total_students: stats.total_students,
                                    active_students: stats.active_students,
                                    graduated_students: stats.graduated_students,
                                    students_with_debt: stats.students_with_debt,
                                    students_paid: stats.students_paid
                                });
                            }
                        }
                        
                        // Initialize DataTable
                        if ($.fn.DataTable) {
                            // Check again if it's already a DataTable (in case another request finished just before this)
                            if ($.fn.DataTable.isDataTable('#paymentsTable')) {
                                $('#paymentsTable').DataTable().destroy();
                            }
                            
                            var table = $('#paymentsTable').DataTable({
                                destroy: true, // Allow re-initialization by destroying previous instance
                                dom: 'Bfrtip',
                                buttons: [
                                    'copy', 'csv', 'excel',
                                    {
                                        text: 'PDF',
                                        action: function ( e, dt, node, config ) {
                                            $('#exportFilteredPdfBtn').trigger('click');
                                        }
                                    },
                                    'print'
                                ],
                                pageLength: 10,
                                lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
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

                            // Column filters
                            $('.column-filter').off('keyup change').on('keyup change', function() {
                                var columnIndex = $(this).data('column');
                                table.column(columnIndex).search($(this).val()).draw();
                            });
                        }
                    } else {
                        $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted"><p class="mt-2">No payments found</p></td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    if (status === 'abort') return; // Ignore aborted requests
                    console.error('Error loading payments:', xhr);
                    $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger">Error loading payments. Please try again.</td></tr>');
                }
            });
        }

        // Real-time filtering
        $('#filterYear, #filterClass, #filterSubclass, #filterStatus, #filterPaymentStatus, #filterSponsorship').on('change', function() {
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
            $('#filterYear').val('{{ $currentYear }}');
            $('#filterClass').val('');
            $('#filterSubclass').html('<option value="">All Subclasses</option>');
            $('#filterStatus').val('');
            $('#filterPaymentStatus').val('');
            $('#filterSponsorship').val('');
            $('#searchStudentName').val('');
            loadPaymentsData();
        });
        
        // Export filtered PDF (Standardized AJAX Method)
        $('#exportFilteredPdfBtn').on('click', function() {
            const year = $('#filterYear').val();
            const class_id = $('#filterClass').val();
            const subclass_id = $('#filterSubclass').val();
            const student_status = $('#filterStatus').val();
            const payment_status = $('#filterPaymentStatus').val();
            const search_student_name = $('#searchStudentName').val();

            let url = '{{ route("admin.export_filtered_payments_pdf") }}';
            url += `?year=${year}&class_id=${class_id}&subclass_id=${subclass_id}&student_status=${student_status}&payment_status=${payment_status}&search_student_name=${search_student_name}`;

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
        // FEE SMS MODAL — shared for "Send Control Numbers" + "Debt Reminders"
        // Same UX as result_management.blade.php smsProgressModal
        // ====================================================================
        let feeIsSendingSms    = false;
        let feeStopSms         = false;
        let feeSmsType         = 'control_number'; // 'control_number' | 'debt_reminder'
        let feeRecipientType   = 'parent';        // 'parent' | 'sponsor'
        let feeSmsYear         = null;

        function feeUpdateSelectedCount() {
            const count = $('.fee-parent-checkbox:checked').length;
            $('#feeSelectedCount').text('Recipients: ' + count);
            $('#feeStartSendingSms').prop('disabled', count === 0);
        }

        function openFeeSmsModal(type, year, sponsorshipFilter) {
            feeSmsType = type;
            feeSmsYear = year;
            feeRecipientType = 'parent'; // Reset to parent by default

            // Set modal title & description
            if (type === 'debt_reminder') {
                $('#feeSmsModalTitle').text('Send Debt Reminder');
                $('#feeSmsModalDesc').text('List of parents/sponsors with outstanding balances. Select all or individual ones.');
                $('#feeSmsTabs').show(); // Show tabs for debt reminders
            } else {
                $('#feeSmsModalTitle').text('Send Control Numbers SMS');
                $('#feeSmsModalDesc').text('List of parents to receive Control Number SMS.');
                $('#feeSmsTabs').hide(); // Hide tabs for CN (mostly for parents)
            }

            // Reset tabs UI
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
            $('#feeStartSendingSms').prop('disabled', true).html('<i class="bi bi-send"></i> Start Sending SMS');
            $('#feeBtnCancelSms').prop('disabled', false).text('Cancel');
            $('.fee-parent-checkbox, #feeSelectAllParents').prop('disabled', false);
            feeIsSendingSms = false;
            feeStopSms      = false;

            // Update table header based on recipient type
            const headerRow = $('#feeSmsTableHeader');
            if (feeRecipientType === 'sponsor') {
                headerRow.html(`
                    <th style="width:40px;"></th>
                    <th>Sponsor Name</th>
                    <th>Students</th>
                    <th>Phone</th>
                    <th>Debt (TZS)</th>
                    <th class="text-center" style="width:110px;">Status</th>
                `);
            } else {
                headerRow.html(`
                    <th style="width:40px;"></th>
                    <th>Recipient (Parent)</th>
                    <th>Phone</th>
                    <th>Debt (TZS)</th>
                    <th class="text-center" style="width:110px;">Status</th>
                `);
            }

            $.ajax({
                url: '{{ route("get_fee_sms_recipients") }}',
                method: 'GET',
                data: {
                    type: feeSmsType,
                    year: feeSmsYear,
                    recipient_type: feeRecipientType
                },
                success: function(response) {
                    $('#feeSmsLoadingState').addClass('d-none');
                    const list = $('#feeSmsRecipientsList');
                    list.empty();

                    if (!response.success || !response.recipients || response.recipients.length === 0) {
                        const colspan = feeRecipientType === 'sponsor' ? 5 : 5;
                        list.html(`<tr><td colspan="${colspan}" class="text-center text-muted py-3">No recipients found in this tab.</td></tr>`);
                        $('#feeSmsRecipientsSection').removeClass('d-none');
                        return;
                    }

                    response.recipients.forEach(function(r, idx) {
                        const hasPhone   = r.phone && r.phone.length > 0;
                        const disabledAt = hasPhone ? '' : 'disabled="disabled"';
                        const checkId    = 'fee_chk_' + (r.paymentID || r.sponsorID) + '_' + idx;
                        const statusHtml = hasPhone
                            ? '<span class="fee-status-marker text-muted small">Pending</span>'
                            : '<span class="text-danger small">No Phone</span>';

                        let rowHtml = `<tr data-payment-id="${r.paymentID || ''}" data-sponsor-id="${r.sponsorID || ''}" data-phone="${r.phone || ''}">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input fee-parent-checkbox" id="${checkId}" ${disabledAt}>
                                    <label class="custom-control-label" for="${checkId}">&nbsp;&nbsp;&nbsp;</label>
                                </div>
                            </td>
                            <td>${r.recipientLabel}</td>`;
                        
                        if (feeRecipientType === 'sponsor') {
                            rowHtml += `<td>${r.studentCount || 0}</td>`;
                            rowHtml += `<td>${r.phone || '<span class="text-muted">-</span>'}</td>`;
                        } else {
                            rowHtml += `<td>${r.phone || '<span class="text-muted">-</span>'}</td>`;
                        }

                        rowHtml += `<td>${parseFloat(r.balance || 0).toLocaleString()}</td>
                            <td class="text-center fee-status-col">${statusHtml}</td>
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

        // Start Sending — async loop per-row (same as result_management)
        $('#feeStartSendingSms').on('click', async function() {
            const selectedRows = $('.fee-parent-checkbox:checked').closest('tr');
            if (selectedRows.length === 0) return;

            const confirmed = await Swal.fire({
                title: 'Send SMS?',
                text: `Do you want to send SMS to ${selectedRows.length} recipients.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, Send Now',
                cancelButtonText: 'Cancel'
            });
            if (!confirmed.isConfirmed) return;

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

                // Show spinner on this row
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
                            year: feeSmsYear,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    if (response.success) {
                        statusCol.html('<i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;" title="Delivered"></i>');
                        delivered++;
                    } else {
                        statusCol.html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:1.2rem;" title="' + (response.message || 'Failed') + '"></i>');
                        failed++;
                    }
                } catch (err) {
                    statusCol.html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:1.2rem;" title="Network Error"></i>');
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
            });
        });

        // ---------- Button: Send Control Numbers ----------
        $('#sendSMSBtn').off('click').on('click', function(e) {
            e.preventDefault();
            openFeeSmsModal('control_number', $('#filterYear').val(), '');
        });

        // ---------- Button: Send Debt Reminders ----------
        $('#sendDebtRemindersBtn').off('click').on('click', function(e) {
            e.preventDefault();
            openFeeSmsModal('debt_reminder', $('#filterYear').val(), '');
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
                text: 'This will generate control numbers for all active students. Existing control numbers will not be overwritten.',
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
                            year: $('#filterYear').val()
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

        // Send SMS to All Parents - Send control numbers directly
        $('#sendSMSBtn').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Send Control Numbers via SMS',
                text: 'This will send control numbers (School Fee and Other Contribution) to all parents who have not received SMS yet.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send SMS',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');
                    
                    $.ajax({
                        url: '/send_control_numbers_sms',
                        method: 'POST',
                        data: {
                            year: $('#filterYear').val()
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    html: `<p>${response.message}</p><p><strong>Sent:</strong> ${response.sent || 0}</p><p><strong>Failed:</strong> ${response.failed || 0}</p>`,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    loadPaymentsData(); // Reload data via AJAX
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to send SMS',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to send SMS';
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
            
            // Extract fee payments from the unified payment record
            const feePayments = payment.fee_payments || [];
            const tuitionPayments = feePayments.filter(fp => fp.is_required);
            const otherFeePayments = feePayments.filter(fp => !fp.is_required);
            
            // Debt handling
            const oldDebt = parseFloat(payment.debt || 0);
            const totalPaid = parseFloat(payment.amount_paid || 0);
            const totalRequired = parseFloat(payment.amount_required || 0);
            const totalOutstanding = parseFloat(payment.balance || 0);
            const currentBillBalance = Math.max(0, totalOutstanding - oldDebt);

            // Populate Summary Dashboard
            $('#view_control_number').text(payment.control_number || 'N/A');
            $('#view_total_paid').text(totalPaid.toLocaleString() + '/=');
            $('#view_bill_balance').text(currentBillBalance.toLocaleString() + '/=');
            $('#view_total_fee_required').text(totalRequired.toLocaleString() + '/=');
            $('#view_old_debt').text(oldDebt.toLocaleString() + '/=');
            $('#view_total_outstanding').text(totalOutstanding.toLocaleString() + '/=');

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
            
            // Build Record Payment button for the whole control number
            let recordPaymentHtml = '';
            if (!isClosedYear) {
                recordPaymentHtml += '<div class="btn-group">';
                recordPaymentHtml += '<button class="btn btn-success record-payment-btn" ' +
                    'data-payment-id="' + payment.paymentID + '" ' +
                    'data-control-number="' + (payment.control_number || '') + '" ' +
                    'data-amount-required="' + (payment.amount_required || 0) + '" ' +
                    'data-amount-paid="' + (payment.amount_paid || 0) + '" ' +
                    'data-balance="' + (payment.balance || 0) + '" ' +
                    'data-is-sponsored="' + (sponsorData ? 'yes' : 'no') + '" ' +
                    'data-payment=\'' + JSON.stringify(payment).replace(/'/g, "&#39;") + '\' ' +
                    'data-fee-type="Unified Payment" ' +
                    'title="Record Payment">' +
                    '<i class="bi bi-cash-coin me-2"></i> Record New Payment' +
                    '</button>';
                
                // Add Send SMS button to modal
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
                tuitionHtml += '<table class="table table-sm table-bordered table-hover">';
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
                    tuitionHtml += '<td><strong>Previous Year Debt</strong></td>';
                    tuitionHtml += '<td class="text-end">' + oldDebt.toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end">0.00/=</td>';
                    tuitionHtml += '<td class="text-end"><strong>' + oldDebt.toLocaleString() + '/=</strong></td>';
                    tuitionHtml += '<td class="text-center">-</td>';
                    tuitionHtml += '</tr>';
                }

                tuitionPayments.forEach(function(fp) {
                    tuitionHtml += '<tr>';
                    tuitionHtml += '<td>' + fp.fee_name + '</td>';
                    tuitionHtml += '<td class="text-end">' + parseFloat(fp.fee_total_amount || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end text-success">' + parseFloat(fp.amount_paid || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end text-danger">' + parseFloat(fp.balance || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-center">';
                    tuitionHtml += '<button class="btn btn-sm btn-outline-info view-payment-records-btn" ';
                    tuitionHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    tuitionHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
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
                otherFeesHtml += '<table class="table table-sm table-bordered table-hover">';
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
                    otherFeesHtml += '<tr>';
                    otherFeesHtml += '<td>' + fp.fee_name + '</td>';
                    otherFeesHtml += '<td class="text-end">' + parseFloat(fp.fee_total_amount || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-success">' + parseFloat(fp.amount_paid || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-danger">' + parseFloat(fp.balance || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-center">';
                    otherFeesHtml += '<button class="btn btn-sm btn-outline-info view-payment-records-btn" ';
                    otherFeesHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    otherFeesHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
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
            
            // Build All Transactions section
            let transactionsHtml = '';
            const paymentRecords = payment.payment_records || [];
            
            // Update collapse button text with control number
            const controlNo = payment.control_number || 'N/A';
            $('#allTransactionsCollapse').parent().find('button span').html('<i class="bi bi-clock-history me-2"></i> All Transactions of Control Number: <span class="text-primary-custom">' + controlNo + '</span>');

            if (paymentRecords.length > 0) {
                transactionsHtml += '<div class="table-responsive">';
                transactionsHtml += '<table class="table table-sm table-bordered table-hover">';
                transactionsHtml += '<thead class="bg-light">';
                transactionsHtml += '<tr>';
                transactionsHtml += '<th>Date</th>';
                transactionsHtml += '<th class="text-end">Amount (TZS)</th>';
                transactionsHtml += '<th>Method</th>';
                transactionsHtml += '<th>Reference</th>';
                transactionsHtml += '<th class="text-center">Status</th>';
                transactionsHtml += '<th class="text-center">Actions</th>';
                transactionsHtml += '</tr>';
                transactionsHtml += '</thead>';
                transactionsHtml += '<tbody>';
                
                paymentRecords.sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date)).forEach(function(record) {
                    const isVerified = record.is_verified || false;
                    
                    // Format Date
                    let displayDate = 'N/A';
                    if (record.payment_date) {
                        const pDate = new Date(record.payment_date);
                        displayDate = pDate.toLocaleString('en-GB', { 
                            day: '2-digit', 
                            month: 'short', 
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }

                    transactionsHtml += '<tr>';
                    transactionsHtml += '<td>' + displayDate + '</td>';
                    transactionsHtml += '<td class="text-end fw-bold text-success">' + parseFloat(record.paid_amount || 0).toLocaleString() + '/=</td>';
                    transactionsHtml += '<td><span class="badge ' + (record.payment_source === 'Bank' ? 'bg-primary' : 'bg-success') + '">' + (record.payment_source || 'N/A') + '</span></td>';
                    transactionsHtml += '<td>' + (record.reference_number || 'N/A') + '</td>';
                    transactionsHtml += '<td class="text-center">';
                    if (isVerified) {
                        transactionsHtml += '<span class="badge bg-success" title="Verified"><i class="bi bi-patch-check-fill"></i></span>';
                    } else {
                        transactionsHtml += '<span class="badge bg-secondary" title="Unverified"><i class="bi bi-patch-exclamation"></i></span>';
                    }
                    transactionsHtml += '</td>';
                    transactionsHtml += '<td class="text-center">';
                    transactionsHtml += '<div class="btn-group btn-group-sm">';
                    
                    if (!isClosedYear) {
                        // Verify/Unverify Toggle
                        if (isVerified) {
                            transactionsHtml += '<button class="btn btn-outline-secondary unverify-payment-record-btn" ';
                            transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                            transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                            transactionsHtml += 'title="Unverify"><i class="bi bi-patch-minus"></i></button>';
                        } else {
                            transactionsHtml += '<button class="btn btn-outline-success verify-payment-record-btn" ';
                            transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                            transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                            transactionsHtml += 'title="Verify"><i class="bi bi-patch-check"></i></button>';
                        }

                        // Edit button
                        transactionsHtml += '<button class="btn btn-outline-warning edit-payment-record-btn" ';
                        transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                        transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                        transactionsHtml += 'data-paid-amount="' + record.paid_amount + '" ';
                        transactionsHtml += 'data-payment-date="' + (record.payment_date || '') + '" ';
                        transactionsHtml += 'data-payment-source="' + (record.payment_source || '') + '" ';
                        transactionsHtml += 'data-reference-number="' + (record.reference_number || '') + '" ';
                        transactionsHtml += 'data-bank-name="' + (record.bank_name || '') + '" ';
                        transactionsHtml += 'data-notes="' + (record.notes || '') + '" ';
                        transactionsHtml += 'title="Edit"><i class="bi bi-pencil"></i></button>';
                        
                        // Delete button
                        transactionsHtml += '<button class="btn btn-outline-danger delete-payment-record-btn" ';
                        transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                        transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                        transactionsHtml += 'data-paid-amount="' + record.paid_amount + '" ';
                        transactionsHtml += 'title="Delete"><i class="bi bi-trash"></i></button>';
                    } else {
                        transactionsHtml += '-';
                    }
                    
                    transactionsHtml += '</div>';
                    transactionsHtml += '</td>';
                    transactionsHtml += '</tr>';
                });
                
                transactionsHtml += '</tbody>';
                transactionsHtml += '</table>';
                transactionsHtml += '</div>';
            } else {
                transactionsHtml = '<div class="text-center py-3 text-muted">No transactions found for this control number.</div>';
            }
            $('#allTransactionsContent').html(transactionsHtml);
            
            $('#viewMoreModal').modal('show');
        });


        // Record Payment Button Click Handler
        $(document).on('click', '.record-payment-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Get payment data
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const amountRequired = btn.data('amount-required');
            const balance = btn.data('balance');
            const isSponsored = btn.data('is-sponsored');
            
            // Get raw payment data to access fee_payments
            let payment = {};
            try {
                const pData = btn.data('payment');
                payment = typeof pData === 'string' ? JSON.parse(pData) : pData;
            } catch(e) { console.error(e); }

            // Set modal fields
            $('#record_payment_id').val(paymentID);
            $('#record_control_number').val(controlNumber);
            
            // Populate Fee Dropdown
            const feeSelect = $('#record_fee_id');
            feeSelect.find('option:not([value="any"])').remove();
            
            if (payment && payment.fee_payments) {
                payment.fee_payments.forEach(fp => {
                    const optionText = `${fp.fee_name} (Bal: ${parseFloat(fp.balance).toLocaleString()}/=)`;
                    const option = $('<option></option>')
                        .attr('value', fp.feeID)
                        .attr('data-required', fp.fee_total_amount)
                        .attr('data-balance', fp.balance)
                        .text(optionText);
                    feeSelect.append(option);
                });
            }
            feeSelect.val('any').trigger('change');

            $('#record_amount_required').val(parseFloat(amountRequired || 0).toLocaleString() + '/=');
            $('#record_balance').val(parseFloat(balance || 0).toLocaleString() + '/=');
            
            $('#record_paid_amount').val('').removeClass('is-invalid');
            $('#record_reference_number').val('');
            $('#record_payment_date').val(new Date().toISOString().split('T')[0]);
            $('#record_payment_source').val('Cash');
            $('#record_payment_by').val('Parent');
            $('#record_bank_name').val('');
            $('#record_notes').val('');

            // Store split balances in modal for real-time validation
            $('#recordPaymentModal').data('sponsor-balance', parseFloat(payment.sponsor_balance || 0));
            $('#recordPaymentModal').data('parent-balance', parseFloat(payment.parent_balance || 0));

            // Hide/Show Payment By field
            if (isSponsored === 'yes') {
                $('#record_payment_by_container').show();
                $('#record_payment_by').val('Parent').trigger('change');
            } else {
                $('#record_payment_by_container').hide();
                $('#record_payment_by').val('Parent'); // Still set for backend
                $('#payer_balance_detail_section').hide();
            }
            
            // Reset form state
            togglePaymentMethodFields('Cash');
            $('#recordPaymentModal').modal('show');
        });

        // Fee Selection Change Handler
        $(document).on('change', '#record_fee_id', function() {
            const selectedOption = $(this).find('option:selected');
            const feeID = $(this).val();
            
            $('#record_paid_amount').val('').removeClass('is-invalid');

            if (feeID === 'any') {
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
                
                if (confirm('Je, unathibitisha kupokea malipo haya?')) {
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
                
                if (confirm('Je, una uhakika wa kufuta uthibitisho huu?')) {
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

        // Send Control Number Button Click Handler
        $(document).on('click', '.send-control-number-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const feeType = btn.data('fee-type');
            
            Swal.fire({
                title: 'Send Control Number?',
                text: `Are you sure you want to send control number ${controlNumber} to parent?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: `/resend_control_number/${paymentID}`,
                        method: 'POST',
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
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to send control number',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to send control number';
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

        // Resend SMS
        $(document).on('click', '.resend-sms-btn', function(e) {
            e.preventDefault();
            const paymentID = $(this).data('payment-id');
            const studentName = $(this).data('student-name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `Resend control number SMS to parent of ${studentName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Resend',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: `/resend_control_number/${paymentID}`,
                        method: 'POST',
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
                            let errorMessage = 'Failed to send SMS';
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

        // Resend Individual Control SMS from Modal
        $(document).on('click', '.resend-control-sms-btn', function() {
            const btn = $(this);
            const paymentID = btn.data('payment-id');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');

            $.ajax({
                url: '/resend_control_number/' + paymentID,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.success ? 'Sent!' : 'Failed!',
                        text: response.message,
                        confirmButtonColor: '#940000'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to send SMS.',
                        confirmButtonColor: '#940000'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
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
    <div class="modal-dialog modal-lg" role="document">
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
                    <i class="bi bi-send"></i> Start Sending SMS
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
