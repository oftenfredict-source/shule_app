@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
    }

    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }

    body, .content, .card, .btn, .form-control, .form-select, .table {
        font-family: "Century Gothic", Arial, sans-serif;
    }

    body {
        background: #ffffff;
        min-height: 100vh;
    }

    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }

    .sms-header {
        background: #ffffff;
        color: #212529;
        padding: 15px 0 5px;
        margin-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .sms-header h2 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .sms-header h2 i {
        font-size: 2rem;
    }

    .sms-card {
        background: white;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        transition: none;
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .card-title i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    .recipient-option {
        border: 1px solid #e9ecef;
        border-radius: 0;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .recipient-option:hover {
        border-color: var(--primary-color);
        background: #fff;
        transform: translateX(5px);
    }

    .recipient-option.active {
        border-color: var(--primary-color);
        background: #fff5f5;
    }

    .recipient-option input[type="radio"] {
        margin-right: 15px;
        transform: scale(1.3);
        accent-color: var(--primary-color);
    }

    .recipient-option-label {
        display: flex;
        align-items: center;
        gap: 15px;
        font-weight: 500;
        color: #495057;
        margin-bottom: 10px;
    }

    .recipient-option-label i {
        font-size: 1.5rem;
        color: var(--primary-color);
    }

    .recipient-option-desc {
        color: #6c757d;
        font-size: 0.9rem;
        margin-left: 45px;
        margin-top: 5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-control, .form-select {
        border: 1px solid #e9ecef;
        border-radius: 0;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .char-count {
        text-align: right;
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .btn-send {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 0;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s ease;
        box-shadow: none;
    }

    .btn-send:hover {
        background: var(--primary-hover);
    }

    .btn-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stats-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stats-card.failed {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    }

    .stats-card.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }

    .stats-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .recipient-preview {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border-left: 4px solid var(--primary-color);
    }

    .recipient-preview strong {
        color: var(--primary-color);
    }

    .student-search-results {
        max-height: 300px;
        overflow-y: auto;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-top: 10px;
        background: white;
        display: none;
    }

    .student-result-item {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .student-result-item:hover {
        background: #f8f9fa;
    }

    .student-result-item:last-child {
        border-bottom: none;
    }

    .student-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .student-name {
        font-weight: 500;
        color: #212529;
    }

    .student-details {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        max-width: 400px;
    }

    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .results-table {
        margin-top: 20px;
    }

    .badge-success-custom {
        background-color: var(--success-color);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .badge-danger-custom {
        background-color: var(--danger-color);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .hidden {
        display: none !important;
    }

    #composeMessageSection.hidden {
        display: none !important;
    }

    #composeMessageSection:not(.hidden) {
        display: block !important;
    }

    .search-filter-section {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }

    .sms-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .sms-menu .list-group-item.active {
        border-left-color: var(--primary-color);
        background: #fff5f5;
        color: var(--primary-color);
        font-weight: 600;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        font-weight: 500;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
        color: white;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage SMS Information</h1>
            </div>
        </div>
    </div>
</div>

<style>
    /* Expandable Settings Styling */
    .setting-item {
        transition: all 0.3s ease;
        border: 1px solid transparent;
        padding: 15px;
        border-radius: 8px;
    }
    .setting-item:hover {
        background: #FFF5F5;
        transform: translateY(-5px);
        border-color: rgba(148, 0, 0, 0.1);
    }
    
    /* DataTable Search Padding & Custom Search */
    .dataTables_filter {
        display: none !important; /* We use a custom search input */
    }
    .dataTables_wrapper .table-responsive {
        margin-top: 10px;
    }
</style>

<div class="content mt-3">
    <!-- Header Section (Always Visible Actions) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">SMS NOTIFICATION SYSTEM</h4>
                            <p class="text-muted mb-0 small" style="font-weight: 600;">Send notifications to parents, teachers, staff, the school board, and parents registering children.</p>
                            <p class="text-muted mb-0 small mt-1"><i class="fa fa-info-circle"></i> Parent recipients use <strong>Active enrollments</strong> for the academic year selected in the header. Joining parents come from <strong>Pending Students</strong> who are not yet confirmed.</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn shadow-sm text-white" id="toggleActionsBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 25px; transition: all 0.3s ease;">
                                <i class="fa fa-cogs"></i> ACTION <i class="fa fa-chevron-up ml-2" id="actionChevron"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Expanded Actions Section -->
                    <div id="expandedActions" style="display: block; margin-top: 25px; padding-top: 25px; border-top: 1px solid rgba(148, 0, 0, 0.1); position: relative;">
                        <button type="button" class="close" onclick="jQuery('#expandedActions').slideUp(300); jQuery('#actionChevron').removeClass('fa-chevron-up').addClass('fa-chevron-down');" style="position: absolute; right: 10px; top: 10px; color: #000; opacity: 0.5;">&times;</button>
                        <div class="row text-center">
                            <!-- Row 1 -->
                            <div class="col-md-4 border-right border-bottom pb-3 mb-3">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('all_parents')">
                                    <i class="fa fa-users fa-2x mb-3 text-primary"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO PARENTS</h6>
                                </a>
                            </div>
                            <div class="col-md-4 border-right border-bottom pb-3 mb-3">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('class_parents')">
                                    <i class="fa fa-graduation-cap fa-2x mb-3 text-success"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO CLASS PARENT</h6>
                                </a>
                            </div>
                            <div class="col-md-4 border-bottom pb-3 mb-3">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('all_teachers')">
                                    <i class="fa fa-user fa-2x mb-3 text-info"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO TEACHERS</h6>
                                </a>
                            </div>
                            
                            <!-- Row 2 -->
                            <div class="col-md-4 border-right">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('all_staff')">
                                    <i class="fa fa-briefcase fa-2x mb-3 text-warning"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO STAFF</h6>
                                </a>
                            </div>
                            <div class="col-md-4 border-right">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('all_users')">
                                    <i class="fa fa-globe fa-2x mb-3 text-danger"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO ALL USERS</h6>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openDeliveryReport()">
                                    <i class="fa fa-bar-chart fa-2x mb-3 text-secondary"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">DELIVERY REPORT</h6>
                                </a>
                            </div>

                            <!-- Row 3 -->
                            <div class="col-md-6 border-right border-top pt-3 mt-3">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('school_board')">
                                    <i class="fa fa-university fa-2x mb-3" style="color: #6f42c1;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO SCHOOL BOARD</h6>
                                </a>
                            </div>
                            <div class="col-md-6 border-top pt-3 mt-3">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="openSmsModalType('pending_parents')">
                                    <i class="fa fa-user-plus fa-2x mb-3" style="color: #17a2b8;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">SEND TO JOINING PARENTS</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generic SMS Modal -->
<div class="modal fade" id="smsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="smsModalLabel"><i class="fa fa-comments"></i> <span id="smsModalTitleText">Send SMS</span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" id="classSelectionRow" style="display: none;">
                    <div class="col-md-12 mb-4">
                        <label class="font-weight-bold text-primary-custom">Select Class <span class="text-danger">*</span></label>
                        <select class="form-control border-primary-custom shadow-sm" id="modalClassSelect" onchange="loadRecipientsForClass(this.value)" style="width: 100%;">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div id="smsContentArea">
                    <div class="row">
                        <!-- Message Composition -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold mb-2"><i class="fa fa-edit text-primary-custom"></i> Type Your Message</label>
                                <textarea class="form-control shadow-sm" id="modalMessageText" rows="12" placeholder="Type message here..."></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="small text-muted"><i class="fa fa-info-circle"></i> SMS is typically 160 chars</span>
                                    <span class="small font-weight-bold text-primary-custom"><span id="modalCharCount">0</span> chars</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recipients List -->
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="custom-control custom-checkbox d-inline-block mr-3">
                                        <input type="checkbox" class="custom-control-input" id="modalSelectAll">
                                        <label class="custom-control-label font-weight-bold" for="modalSelectAll">Select All</label>
                                    </div>
                                    <span class="badge badge-success px-2 py-1" id="modalSelectedCount">Selected: 0</span>
                                </div>
                            </div>
                            
                            <!-- Custom Full Width Search -->
                            <div class="input-group mb-3 shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" class="form-control border-left-0 pl-0" id="modalSearchInput" placeholder="Search by name, phone, or role...">
                            </div>
                            
                            <div class="table-responsive border shadow-sm mt-2" style="height: 350px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" id="modalRecipientsTable">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th style="width: 40px;"></th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th class="text-center" style="width: 100px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalRecipientsList">
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Please select an option to load recipients.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Area -->
                    <div id="modalProgressArea" class="mt-4 d-none">
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-primary-custom">Sending Progress</span>
                            <span id="modalProgressText" class="small font-weight-bold">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 25px; border-radius: 12px; border: 1px solid #ddd;">
                            <div id="modalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;">0%</div>
                        </div>
                        <div id="modalDeliverySummary" class="text-center small mt-2 font-weight-bold"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom px-4 shadow-sm" id="modalStartSendingBtn">
                    <i class="fa fa-paper-plane"></i> Send SMS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Report Modal -->
<div class="modal fade" id="deliveryReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #940000, #c0392b);">
                <h5 class="modal-title"><i class="fa fa-bar-chart"></i> SMS Delivery Report</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <!-- Filter Row -->
                <div class="row mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="font-weight-bold small">Filter by Type</label>
                        <select class="form-control form-control-sm shadow-sm" id="drTypeFilter">
                            <option value="all">All Types</option>
                            <option value="all_parents">Parent SMS</option>
                            <option value="class_parents">Class Parent SMS</option>
                            <option value="all_teachers">Teacher SMS</option>
                            <option value="all_staff">Staff SMS</option>
                            <option value="school_board">School Board SMS</option>
                            <option value="pending_parents">Joining Parents SMS</option>
                            <option value="all_parents_teachers">All Users SMS</option>
                            <option value="result">Result SMS</option>
                            <option value="fee">Fee SMS</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold small">Date From</label>
                        <input type="date" class="form-control form-control-sm shadow-sm" id="drDateFrom">
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold small">Date To</label>
                        <input type="date" class="form-control form-control-sm shadow-sm" id="drDateTo">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-sm btn-primary-custom w-100 shadow-sm" id="drLoadBtn">
                            <i class="fa fa-search"></i> Load Report
                        </button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-3" id="drStatsRow" style="display:none;">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3" style="border-left: 4px solid #940000 !important;">
                            <h4 class="mb-0 font-weight-bold" id="drStatTotal">0</h4>
                            <small class="text-muted">Total Sent</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3" style="border-left: 4px solid #28a745 !important;">
                            <h4 class="mb-0 font-weight-bold text-success" id="drStatDelivered">0</h4>
                            <small class="text-muted">Delivered</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3" style="border-left: 4px solid #ffc107 !important;">
                            <h4 class="mb-0 font-weight-bold text-warning" id="drStatPending">0</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3" style="border-left: 4px solid #dc3545 !important;">
                            <h4 class="mb-0 font-weight-bold text-danger" id="drStatFailed">0</h4>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>

                <!-- Search & Table -->
                <div class="input-group mb-3 shadow-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" class="form-control border-left-0" id="drSearchInput" placeholder="Search report by name, phone, or status...">
                </div>

                <div class="table-responsive border shadow-sm" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" id="drTable">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th>Recipient</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Message ID</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="drTableBody">
                            <tr><td colspan="7" class="text-center py-4">Click "Load Report" to view delivery history.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted font-weight-bold" id="drRecordCount">0 record(s) found</small>
                    <button class="btn btn-sm btn-outline-primary" id="drRefreshAllBtn" style="display:none;">
                        <i class="fa fa-refresh"></i> Refresh All Statuses
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    console.log("SMS Management Script Initializing...");

    (function($) {
        'use strict';

        window.openSmsModalType = function(type) {
            console.log("Opening SMS Modal:", type);
            window.currentSmsType = type;
            $('#smsModal').modal('show');
            $('#modalProgressArea').addClass('d-none');
            $('#modalStartSendingBtn').prop('disabled', false);
            $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4"><i class="fa fa-spinner fa-spin text-primary"></i> Loading recipients...</td></tr>');
            $('#modalSelectAll').prop('checked', false);
            $('#modalSelectedCount').text('Selected: 0');
            
            let title = "Send SMS";
            $('#classSelectionRow').hide();
            
            switch(type) {
                case 'all_parents': 
                    title = "Send to All Parents"; 
                    fetchRecipients('{{ route("get_all_parents_sms") }}');
                    break;
                case 'class_parents': 
                    title = "Send to Class Parents"; 
                    $('#classSelectionRow').show();
                    $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4">Please select a class above.</td></tr>');
                    break;
                case 'all_teachers': 
                    title = "Send to All Teachers"; 
                    fetchRecipients('{{ route("get_all_teachers_sms") }}');
                    break;
                case 'all_staff': 
                    title = "Send to All Staff"; 
                    fetchRecipients('{{ route("get_all_staff_sms") }}');
                    break;
                case 'school_board':
                    title = "Send to School Board";
                    fetchRecipients('{{ route("get_school_board_sms") }}');
                    break;
                case 'pending_parents':
                    title = "Send to Parents Registering Children";
                    fetchRecipients('{{ route("get_pending_parents_sms") }}');
                    break;
                case 'all_users': 
                    title = "Send to All Users (Parents & Teachers)"; 
                    fetchRecipients('{{ route("get_all_parents_sms") }}', true); 
                    break;
            }
            
            $('#smsModalTitleText').text(title);
        };

        window.openDeliveryReport = function() {
            console.log("Opening Delivery Report");
            $('#deliveryReportModal').modal('show');
            // Trigger load automatically
            $('#drLoadBtn').trigger('click');
        };

        window.fetchRecipients = function(url, appendTeachers = false) {
            $.get(url, function(res) {
                if (res.success === false || res.error_code === 'no_academic_year' || res.error_code === 'no_active_enrollments') {
                    const msg = res.message || 'No Active enrollments for the selected academic year.';
                    $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4 text-muted">' + msg + '</td></tr>');
                    updateSelectedCount();
                    return;
                }
                if (res.success) {
                    let html = '';
                    let data = res.parents || res.teachers || res.staff || res.recipients;
                    let role = res.role || (res.parents ? 'Parent' : (res.teachers ? 'Teacher' : 'Staff'));
                    let kind = res.recipient_kind || role.toLowerCase();
                    let badge = kind === 'board' ? 'badge-primary' : (kind === 'pending_parent' ? 'badge-info' : 'badge-info');
                    
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            let rowRole = item.role_label || role;
                            let rowName = item.display_name || (item.first_name + ' ' + (item.last_name || ''));
                            html += `
                                <tr class="recipient-row">
                                    <td><input type="checkbox" class="recipient-checkbox" value="${item.parentID || item.id}" data-type="${kind}" data-phone="${item.phone || item.phone_number}" data-name="${rowName}"></td>
                                    <td>${rowName}</td>
                                    <td>${item.phone || item.phone_number}</td>
                                    <td><span class="badge ${badge}">${rowRole}</span></td>
                                    <td class="text-center status-cell"><i class="fa fa-clock-o text-muted"></i></td>
                                </tr>
                            `;
                        });
                    } else if (!appendTeachers) {
                        html = '<tr><td colspan="5" class="text-center py-4 text-muted">' + (res.message || 'No recipients found for Active enrollments in the selected academic year.') + '</td></tr>';
                    }

                    if (appendTeachers) {
                        $('#modalRecipientsList').html(html);
                        $.get('{{ route("get_all_teachers_sms") }}', function(tRes) {
                            if (tRes.success && tRes.teachers) {
                                let tHtml = '';
                                tRes.teachers.forEach(item => {
                                    tHtml += `
                                        <tr class="recipient-row">
                                            <td><input type="checkbox" class="recipient-checkbox" value="${item.id}" data-type="teacher" data-phone="${item.phone_number}" data-name="${item.first_name + ' ' + (item.last_name || '')}"></td>
                                            <td>${item.first_name + ' ' + (item.last_name || '')}</td>
                                            <td>${item.phone_number}</td>
                                            <td><span class="badge badge-success">Teacher</span></td>
                                            <td class="text-center status-cell"><i class="fa fa-clock-o text-muted"></i></td>
                                        </tr>
                                    `;
                                });
                                $('#modalRecipientsList').append(tHtml);
                                updateSelectedCount();
                            }
                        });
                    } else {
                        $('#modalRecipientsList').html(html || '<tr><td colspan="5" class="text-center py-4">No recipients found.</td></tr>');
                        updateSelectedCount();
                    }
                }
            }).fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Failed to load recipients.';
                $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4 text-muted">' + msg + '</td></tr>');
                updateSelectedCount();
            });
        };

        window.loadRecipientsForClass = function(classId) {
            if (!classId) return;
            $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4"><i class="fa fa-spinner fa-spin text-primary"></i> Loading...</td></tr>');
            $.get('{{ route("get_parents_by_class_sms") }}', { classID: classId }, function(res) {
                if (res.success === false || res.error_code === 'no_academic_year' || res.error_code === 'no_active_enrollments') {
                    $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4 text-muted">' + (res.message || 'No Active enrollments for this class.') + '</td></tr>');
                    updateSelectedCount();
                    return;
                }
                if (res.success && res.parents) {
                    let html = '';
                    res.parents.forEach(item => {
                        html += `
                            <tr class="recipient-row">
                                <td><input type="checkbox" class="recipient-checkbox" value="${item.parentID}" data-type="parent" data-phone="${item.phone}" data-name="${item.display_name}"></td>
                                <td>${item.display_name}</td>
                                <td>${item.phone}</td>
                                <td><span class="badge badge-info">Parent</span></td>
                                <td class="text-center status-cell"><i class="fa fa-clock-o text-muted"></i></td>
                            </tr>
                        `;
                    });
                    $('#modalRecipientsList').html(html.length ? html : '<tr><td colspan="5" class="text-center py-4">No parents found for Active enrollments in this class.</td></tr>');
                    updateSelectedCount();
                }
            }).fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Failed to load parents for this class.';
                $('#modalRecipientsList').html('<tr><td colspan="5" class="text-center py-4 text-muted">' + msg + '</td></tr>');
                updateSelectedCount();
            });
        };

        window.updateSelectedCount = function() {
            let count = $('.recipient-checkbox:checked').length;
            $('#modalSelectedCount').text(`Selected: ${count}`);
        };

        window.drStatusBadge = function(status) {
            switch(status) {
                case 'DELIVERED': return '<span class="badge badge-success">DELIVERED</span>';
                case 'PENDING':   return '<span class="badge badge-warning text-white">PENDING</span>';
                case 'FAILED':    return '<span class="badge badge-danger">FAILED</span>';
                default:          return `<span class="badge badge-secondary">${status || 'UNKNOWN'}</span>`;
            }
        };

        window.drRenderTable = function(logs) {
            if (window.drDataTable) {
                window.drDataTable.destroy();
            }

            let html = '';
            if (!logs || logs.length === 0) {
                html = '<tr><td colspan="7" class="text-center py-4">No records found.</td></tr>';
                $('#drTableBody').html(html);
            } else {
                logs.forEach(log => {
                    html += `
                        <tr>
                            <td class="font-weight-bold">${log.recipient_name}</td>
                            <td>${log.recipient_phone}</td>
                            <td><small>${log.message_type}</small></td>
                            <td class="small text-muted font-italic">${log.message_id || 'N/A'}</td>
                            <td class="text-center" id="dr-status-${log.id}">${drStatusBadge(log.status)}</td>
                            <td class="text-center">
                                ${log.message_id ? 
                                    `<button class="btn btn-xs btn-outline-info dr-check-btn" data-id="${log.id}" data-msgid="${log.message_id}"><i class="fa fa-refresh"></i></button>` : 
                                    '-'}
                            </td>
                        </tr>
                    `;
                });
                $('#drTableBody').html(html);

                window.drDataTable = $('#drTable').DataTable({
                    "pageLength": 10,
                    "ordering": false,
                    "dom": 'rtp',
                    "language": { "paginate": { "previous": "<", "next": ">" } }
                });
            }

            const total     = logs ? logs.length : 0;
            const delivered = logs ? logs.filter(l => l.status === 'DELIVERED').length : 0;
            const pending   = logs ? logs.filter(l => l.status === 'PENDING').length : 0;
            const failed    = logs ? logs.filter(l => l.status !== 'DELIVERED' && l.status !== 'PENDING').length : 0;

            $('#drStatTotal').text(total);
            $('#drStatDelivered').text(delivered);
            $('#drStatPending').text(pending);
            $('#drStatFailed').text(failed);
            $('#drStatsRow').show();
            $('#drRecordCount').text(`${total} record(s) found`);
            $('#drRefreshAllBtn').toggle(pending > 0);
        };

        $(document).ready(function() {
            console.log("DOM Ready - Attaching listeners");

            $('#toggleActionsBtn').on('click', function() {
                $('#expandedActions').slideToggle();
                $('#actionChevron').toggleClass('fa-chevron-up fa-chevron-down');
            });

            $('#modalSelectAll').on('change', function() {
                $('.recipient-checkbox').prop('checked', this.checked);
                updateSelectedCount();
            });

            $(document).on('change', '.recipient-checkbox', function() {
                updateSelectedCount();
            });

            $('#modalSearchInput').on('keyup', function() {
                let val = this.value.toLowerCase();
                $('#modalRecipientsList tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });

            $('#modalMessageText').on('input', function() {
                $('#modalCharCount').text(this.value.length);
            });

            $('#modalStartSendingBtn').on('click', async function() {
                let message = $('#modalMessageText').val();
                let selected = $('.recipient-checkbox:checked');
                
                if (!message) {
                    Swal.fire('Error', 'Please type a message', 'error');
                    return;
                }
                if (selected.length === 0) {
                    Swal.fire('Error', 'Please select at least one recipient', 'error');
                    return;
                }

                const confirmResult = await Swal.fire({
                    title: 'Confirm Sending',
                    text: `Send this message to ${selected.length} recipient(s)?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#940000',
                    confirmButtonText: 'Yes, Send Now'
                });

                if (confirmResult.isConfirmed) {
                    $('#modalStartSendingBtn').prop('disabled', true);
                    $('#modalProgressArea').removeClass('d-none');
                    
                    let total = selected.length;
                    let successCount = 0;
                    let failCount = 0;

                    for (let i = 0; i < selected.length; i++) {
                        let checkbox = $(selected[i]);
                        let row = checkbox.closest('tr');
                        let statusCell = row.find('.status-cell');
                        let id = checkbox.val();
                        let type = checkbox.data('type');
                        
                        // Update progress bar
                        let percent = Math.round(((i) / total) * 100);
                        $('#modalProgressBar').css('width', percent + '%').text(percent + '%');
                        
                        // Show spinner in row
                        statusCell.html('<i class="fa fa-spinner fa-spin text-primary"></i>');
                        
                        // Determine recipient type for API
                        let recipientType = 'all_parents';
                        if (type === 'teacher') recipientType = 'all_teachers';
                        else if (type === 'staff') recipientType = 'all_staff';
                        else if (type === 'board') recipientType = 'school_board';
                        else if (type === 'pending_parent') recipientType = 'pending_parents';
                        else if (window.currentSmsType === 'class_parents') recipientType = 'class_parents';

                        const payload = {
                            _token: '{{ csrf_token() }}',
                            message: message,
                            recipient_type: recipientType,
                            selected_ids: [id],
                            is_sync: true
                        };
                        if (recipientType === 'class_parents') {
                            payload.classID = $('#modalClassSelect').val();
                        }

                        try {
                            const res = await $.ajax({
                                url: '{{ route("send_sms") }}',
                                type: 'POST',
                                data: payload
                            });

                            if (res.success && res.results && res.results.success > 0) {
                                statusCell.html('<i class="fa fa-check-circle text-success" title="Sent successfully"></i>');
                                successCount++;
                            } else {
                                statusCell.html('<i class="fa fa-times-circle text-danger" title="Failed to send"></i>');
                                failCount++;
                            }
                        } catch (err) {
                            statusCell.html('<i class="fa fa-exclamation-triangle text-warning" title="Error"></i>');
                            failCount++;
                        }
                        
                        // Small delay to prevent hitting rate limits too hard
                        await new Promise(resolve => setTimeout(resolve, 300));
                    }

                    $('#modalProgressBar').css('width', '100%').text('100% - Finished');
                    
                    Swal.fire({
                        title: 'Sending Finished',
                        text: `Process completed: ${successCount} Success, ${failCount} Failed.`,
                        icon: 'info'
                    }).then(() => {
                        $('#modalStartSendingBtn').prop('disabled', false);
                        $('#modalProgressArea').addClass('d-none');
                    });
                }
            });

            $('#drLoadBtn').on('click', function() {
                const type     = $('#drTypeFilter').val();
                const dateFrom = $('#drDateFrom').val();
                const dateTo   = $('#drDateTo').val();
                $('#drTableBody').html('<tr><td colspan="7" class="text-center py-4"><i class="fa fa-spinner fa-spin text-primary"></i> Loading...</td></tr>');
                $.get('{{ route("get_sms_logs") }}', { type, date_from: dateFrom, date_to: dateTo }, function(res) {
                    if (res.success) {
                        window.drAllLogs = res.logs;
                        drRenderTable(window.drAllLogs);
                    }
                });
            });

            $(document).on('click', '.dr-check-btn', function() {
                const btn = $(this);
                const id = btn.data('id');
                const msgId = btn.data('msgid');
                btn.html('<i class="fa fa-refresh fa-spin"></i>');
                $.get('{{ route("get_delivery_status") }}', { message_id: msgId }, function(res) {
                    if (res.success) {
                        $(`#dr-status-${id}`).html(drStatusBadge(res.status));
                        let log = window.drAllLogs.find(l => l.id == id);
                        if (log) log.status = res.status;
                    }
                    btn.html('<i class="fa fa-refresh"></i>');
                });
            });

            const today = new Date().toISOString().split('T')[0];
            $('#drDateTo').val(today);
            let lastMonth = new Date();
            lastMonth.setMonth(lastMonth.getMonth() - 1);
            $('#drDateFrom').val(lastMonth.toISOString().split('T')[0]);

            $('#drRefreshAllBtn').on('click', function () {
                const pending = window.drAllLogs.filter(l => l.status === 'PENDING' && l.message_id);
                if (pending.length === 0) return;

                $(this).html('<i class="fa fa-spinner fa-spin"></i> Refreshing...').prop('disabled', true);
                let done = 0;

                pending.forEach(log => {
                    $.get('{{ route("get_delivery_status") }}', { message_id: log.message_id }, function (res) {
                        if (res.success) {
                            $(`#dr-status-${log.id}`).html(drStatusBadge(res.status));
                            log.status = res.status;
                        }
                    }).always(function() {
                        done++;
                        if (done === pending.length) {
                            drRenderTable(window.drAllLogs);
                            $('#drRefreshAllBtn').html('<i class="fa fa-refresh"></i> Refresh All Statuses').prop('disabled', false);
                        }
                    });
                });
            });
        });
    })(jQuery);
</script>
