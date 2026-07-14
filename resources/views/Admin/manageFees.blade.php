@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

@php
    // Get school data for export
    use Illuminate\Support\Facades\Session;
    $schoolID = Session::get('schoolID');
    $school = \App\Models\School::where('schoolID', $schoolID)->first();
    $schoolName = $school ? $school->school_name : 'School Name';
    $schoolLogo = $school && $school->school_logo ? asset($school->school_logo) : '';
@endphp

<style>
    /* Font Family Global */
    body, div, h1, h2, h3, h4, h5, h6, p, span, table, button, input, select, textarea {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }

    /* Corporate Color Scheme */
    :root {
        --primary-custom: #940000;
        --secondary-custom: #FFF5F5;
        --accent-soft: #FFF5F5;
        --header-gradient: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
    }

    .bg-primary-custom {
        background: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
        border-radius: 0;
    }
    .btn-primary-custom:hover {
        background-color: #7a0000;
        border-color: #7a0000;
        color: #ffffff;
    }
    .header-gradient {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
    }
    .accent-soft-red {
        background-color: #FFF5F5 !important;
    }

    .card {
        border-radius: 0 !important;
    }
    
    .setting-item {
        transition: all 0.3s ease;
        border: 1px solid transparent;
        border-radius: 0;
    }
    .setting-item:hover {
        background: #FFF5F5;
        transform: translateY(-3px);
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: whitesmoke;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cfcfcf;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #940000;
    }

    .modal-content {
        border-radius: 0 !important;
        border: none !important;
    }
    
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #940000 #f1f1f1;
    }
    
    .agreement-students-list {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(3px);
    }
    .loading-overlay.show {
        display: flex;
    }
    .loading-spinner {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 2px solid #940000;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SheetJS for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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

            <!-- Page Header (Expandable Pattern) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">MANAGE FEES & FINANCIALS</h4>
                            <p class="text-muted mb-0 small" style="font-weight: 600;">Configure institutional fee structures, installments, and payment reporting.</p>
                        </div>
                        <button class="btn shadow-sm text-white" id="toggleFeeSettingsBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 20px;">
                            <i class="bi bi-gear-fill"></i> SETTINGS
                        </button>
                    </div>

                    <!-- Expanded Settings Section -->
                    <div id="expandedFeeSettings" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(148, 0, 0, 0.1); position: relative;">
                        <button type="button" class="close" onclick="$('#expandedFeeSettings').slideUp(300);" style="position: absolute; right: 10px; top: 10px; color: #000000; opacity: 1;">&times;</button>
                        <div class="row text-center">
                            <div class="col-md-3 border-right">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="openAddFeeModalBtn_Exp">
                                    <i class="fa fa-plus-circle fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">CREATE FEE</h6>
                                </a>
                            </div>
                            <div class="col-md-3 border-right">
                                <a href="{{ route('view_payments') }}" class="text-decoration-none d-block py-3 setting-item">
                                    <i class="fa fa-credit-card fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">PAYMENTS</h6>
                                </a>
                            </div>
                            <div class="col-md-3 border-right">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="openConditionModalBtn_Exp">
                                    <i class="fa fa-tags fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">DISCOUNTS</h6>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="openAgreementModalBtn_Exp">
                                    <i class="fa fa-handshake-o fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">AGREEMENTS</h6>
                                </a>
                            </div>
                        </div>
                        <div class="row text-center mt-3 pt-3 border-top">
                            <div class="col-md-4 border-right">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="openSponsorModalBtn_Exp">
                                    <i class="fa fa-user-circle fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">ASSIGN SPONSORS</h6>
                                </a>
                            </div>
                            <div class="col-md-4 border-right">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="exportPdfBtn_Exp">
                                    <i class="fa fa-file-pdf-o fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">EXPORT PDF</h6>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="exportExcelBtn_Exp">
                                    <i class="fa fa-file-excel-o fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">EXPORT EXCEL</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Structure Table -->
            @if(count($classes) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="feesStructureTable" class="table table-bordered table-hover mb-0" style="border-collapse: separate; border-spacing: 0;">
                                <thead style="background-color: #940000 !important;">
                                    <tr style="background-color: #940000 !important;">
                                        <th style="width: 12%; background-color: #940000 !important;" class="text-center border-end text-white">Class</th>
                                        <th style="width: 25%; background-color: #940000 !important;" class="border-end text-white">Fee Name</th>
                                        <th style="width: 15%; background-color: #940000 !important;" class="text-end border-end text-white">Amount (TZS)</th>
                                        <th style="width: 15%; background-color: #940000 !important;" class="border-end text-white">Deadline</th>
                                        <th style="width: 13%; background-color: #940000 !important;" class="text-center border-end text-white">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                @foreach($classes as $class)
                    @php
                        $classFees = $feesByClass->get($class->classID, collect())->where('status', 'Active');
                        $feeCount = $classFees->count();
                        $rowSpan = $feeCount > 0 ? $feeCount : 1;
                    @endphp
                    
                    @if($feeCount > 0)
                        @foreach($classFees as $index => $fee)
                            <tr class="align-middle">
                                @if($index === 0)
                                    <!-- Class Column (Rowspan) -->
                                    <td class="text-center border-end align-middle bg-light" rowspan="{{ $rowSpan }}">
                                        <strong class="text-dark" style="font-size: 1.1rem;">
                                            <i class="bi bi-mortarboard text-primary-custom"></i> {{ $class->class_name }}
                                        </strong>
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-muted">Total: </small><br>
                                            <strong class="text-primary-custom">{{ number_format($classFees->sum('amount'), 0) }}/=</strong>
                                        </div>
                                    </td>
                                @endif

                                <!-- Fee Name -->
                                <td class="border-end">
                                    <span class="fw-bold">{{ $fee->fee_name }}</span>
                                    @if($fee->description)
                                        <br><small class="text-muted">{{ $fee->description }}</small>
                                    @endif
                                </td>

                                <!-- Amount -->
                                <td class="text-end border-end">
                                    <strong class="text-dark">{{ number_format($fee->amount, 0) }}/=</strong>
                                </td>



                                <!-- Deadline -->
                                <td class="border-end">
                                    @if($fee->payment_deadline_amount || $fee->payment_deadline_date)
                                        @if($fee->payment_deadline_amount)
                                            <small class="text-danger fw-bold">Amount: {{ number_format($fee->payment_deadline_amount, 0) }}/=</small><br>
                                        @endif
                                        @if($fee->payment_deadline_date)
                                            <small class="text-muted">Hadi: {{ $fee->payment_deadline_date->format('d/m/Y') }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="text-center border-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-sm btn-outline-info view-installment-btn" 
                                                data-fee-id="{{ $fee->feeID }}"
                                                title="View Installments">
                                            <i class="bi bi-calendar-range"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary edit-fee-btn" 
                                                data-fee-id="{{ $fee->feeID }}"
                                                title="Edit Fee">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-fee-btn" 
                                                data-fee-id="{{ $fee->feeID }}"
                                                data-fee-name="{{ $fee->fee_name }}"
                                                title="Delete Fee">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <!-- Class with no fees -->
                        <tr class="align-middle">
                            <td class="text-center border-end align-middle bg-light">
                                <strong class="text-dark" style="font-size: 1.1rem;">
                                    <i class="bi bi-mortarboard text-primary-custom"></i> {{ $class->class_name }}
                                </strong>
                            </td>
                            <td colspan="4" class="text-center text-muted py-3">
                                <em>No fees assigned to this class</em>
                            </td>
                        </tr>
                    @endif
                @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td class="text-center fw-bold border-end" colspan="2">
                                            <strong>TOTAL FEES</strong>
                                        </td>
                                        <td class="text-end border-end" colspan="3">
                                            <strong class="text-primary-custom" style="font-size: 1.5rem;">
                                                {{ number_format($classes->sum(function($class) use ($feesByClass) {
                                                    return $feesByClass->get($class->classID, collect())->where('status', 'Active')->sum('amount');
                                                }), 0) }}/=
                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                </div>
            @endif

            <!-- SMS Templates Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 small"><i class="bi bi-chat-left-text"></i> SMS Templates</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h6 class="small fw-bold">Kumbusho la Deni (Debt Reminder)</h6>
                            <div class="p-2 bg-light rounded border">
                                <small class="text-muted">Ndugu [Mzazi], mwanafunzi [Jina] ana deni la ada kiasi cha TZS [Kiasi]. Tafadhali kamilisha malipo kabla ya tarehe [Tarehe] ili kuepuka usumbufu.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Add Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1" aria-labelledby="addFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addFeeModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Fee to Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addFeeForm">
                @csrf
                <div class="modal-body">
                    <!-- Select Class -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="classID" id="fee_class_select" class="form-select" required>
                            <option value="">Select class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <hr>
                    
                    <!-- Fees List Container -->
                    <div id="fees_list_container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="bi bi-list-ul"></i> Fees</h6>
                            <button type="button" class="btn btn-sm btn-primary-custom" id="add_fee_row_btn">
                                <i class="bi bi-plus-circle"></i> Add Fee
                            </button>
                        </div>
                        
                        <!-- Fees will be added here dynamically -->
                        <div id="fees_rows">
                            <!-- Initial fee row will be added by JavaScript -->
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Total Display -->
                    <div class="card border-primary-custom mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total Fees:</strong>
                                <strong class="text-primary-custom" style="font-size: 1.3rem;" id="total_fees_display">TZS 0.00</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Duration Field -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration <span class="text-danger">*</span></label>
                        <select name="duration" id="fee_duration_select" class="form-select" required>
                            <option value="">Choose duration...</option>
                            <option value="Year">Year</option>
                            <option value="Month">Month</option>
                            <option value="Term">Term</option>
                            <option value="Semester">Semester</option>
                            <option value="One-time">One-time</option>
                        </select>
                        <small class="text-muted">Default duration for this fee (usually Year for annual fees)</small>
                    </div>

                    <!-- Installment Options -->
                    <div class="card border-primary-custom mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-range"></i> Installment Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_partial_payment" id="allow_partial_payment" value="1" checked>
                                    <label class="form-check-label fw-bold" for="allow_partial_payment">
                                        Allow Partial Payment
                                    </label>
                                </div>
                                <small class="text-muted">Parents can pay in partial amounts</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_installments" id="allow_installments" value="1">
                                    <label class="form-check-label fw-bold" for="allow_installments">
                                        Allow Installments
                                    </label>
                                </div>
                                <small class="text-muted">Parents can pay in installments (monthly, termly, etc.)</small>
                            </div>
                            
                            <div id="installment_options" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Type <span class="text-danger">*</span></label>
                                    <select name="default_installment_type" id="default_installment_type" class="form-select">
                                        <option value="">Select type...</option>
                                        <option value="Semester">Semester</option>
                                        <option value="Month">Month</option>
                                        <option value="Two Months">2 Months</option>
                                        <option value="Term">Term</option>
                                        <option value="Quarter">Quarter</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" id="number_of_installments" class="form-control" min="1" max="12" placeholder="e.g.: 2, 12">
                                    <small class="text-muted">Total installments (e.g. 2 for 2 semesters, 12 for 12 months)</small>
                                </div>
                                <div id="installment_amounts_container" class="mt-3"></div>
                                <div id="installment_total_validation" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Fee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1" aria-labelledby="editFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editFeeModalLabel">
                    <i class="bi bi-pencil"></i> Edit Fee
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFeeForm">
                @csrf
                <input type="hidden" name="feeID" id="edit_fee_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="classID" id="edit_fee_class_select" class="form-select" required>
                            <option value="">Choose a class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Name <span class="text-danger">*</span></label>
                        <input type="text" name="fee_name" id="edit_fee_name" class="form-control" placeholder="e.g. Tuition Fee, Bus Fee" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="must_start_pay" id="edit_must_start_pay" value="1">
                                <label class="form-check-label fw-bold" for="edit_must_start_pay">
                                    Must Pay to Start?
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="edit_fee_amount" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="card border-danger mb-3">
                        <div class="card-header bg-danger text-white py-2">
                           <h6 class="mb-0 small"><i class="bi bi-alarm"></i> Deadline Info (Optional)</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Deadline Amount</label>
                                    <input type="number" name="payment_deadline_amount" id="edit_deadline_amount" class="form-control form-control-sm" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Deadline Date</label>
                                    <input type="date" name="payment_deadline_date" id="edit_deadline_date" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration <span class="text-danger">*</span></label>
                        <select name="duration" id="edit_fee_duration_select" class="form-select" required>
                            <option value="">Choose duration...</option>
                            <option value="Year">Year</option>
                            <option value="Month">Month</option>
                            <option value="Term">Term</option>
                            <option value="Semester">Semester</option>
                            <option value="One-time">One-time</option>
                        </select>
                        <small class="text-muted">Default duration for this fee (usually Year for annual fees)</small>
                    </div>

                    <!-- Fee Condition Fields -->
                    <div class="mb-3">
                        <hr>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="edit_has_conditions" value="1">
                            <label class="form-check-label fw-bold" for="edit_has_conditions">
                                <i class="bi bi-tags-fill text-primary-custom"></i> Define Fee Conditions (Discounts)
                            </label>
                        </div>
                        <small class="text-muted text-primary-custom">Manage specific discount conditions for this fee</small>
                    </div>
                    
                    <div id="edit_conditions_details" style="display: none;" class="mb-3">
                        <div class="card border-primary-custom bg-light-info">
                            <div class="card-body">
                                <div id="edit_conditions_container">
                                    <!-- Conditions rows will be added here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary-custom mt-2" id="add_edit_condition_btn">
                                    <i class="bi bi-plus-circle-fill"></i> Add New Condition
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Installment Options -->
                    <div class="card border-primary-custom mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-range"></i> Payment Installment Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_partial_payment" id="edit_allow_partial_payment" value="1">
                                    <label class="form-check-label fw-bold" for="edit_allow_partial_payment">
                                        Allow Partial Payment
                                    </label>
                                </div>
                                <small class="text-muted">If unchecked, parents must pay the full installment amount, not partial amounts</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_installments" id="edit_allow_installments" value="1">
                                    <label class="form-check-label fw-bold" for="edit_allow_installments">
                                        Allow Payment by Installments
                                    </label>
                                </div>
                                <small class="text-muted">Parents can pay this fee in installments (semester, monthly, etc.)</small>
                            </div>
                            
                            <div id="edit_installment_options" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Type <span class="text-danger">*</span></label>
                                    <select name="default_installment_type" id="edit_default_installment_type" class="form-select">
                                        <option value="">Select installment type...</option>
                                        <option value="Semester">Semester</option>
                                        <option value="Month">Monthly</option>
                                        <option value="Two Months">Bi-Monthly (Every 2 Months)</option>
                                        <option value="Term">Term</option>
                                        <option value="Quarter">Quarterly</option>
                                        <option value="One-time">One-time</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" id="edit_number_of_installments" class="form-control" min="1" max="12" placeholder="e.g., 2 for 2 semesters, 12 for 12 months">
                                    <small class="text-muted">Total number of installments (e.g., 2 for 2 semesters, 12 for 12 months)</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Amounts</label>
                                    <div id="edit_installment_amounts_container" class="border rounded p-3 bg-light">
                                        <small class="text-muted">Installment amounts will be calculated automatically based on total fee amount and number of installments.</small>
                                    </div>
                                    <div id="edit_installment_total_validation" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_fee_description" class="form-control" rows="3" placeholder="Optional description about this fee"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Fee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Fee Details Modal -->
<div class="modal fade" id="viewFeeModal" tabindex="-1" aria-labelledby="viewFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewFeeModalLabel">
                    <i class="bi bi-eye"></i> Fee Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewFeeContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Installments Modal -->
<div class="modal fade" id="viewInstallmentModal" tabindex="-1" aria-labelledby="viewInstallmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewInstallmentModalLabel">
                    <i class="bi bi-calendar-range"></i> Installments - <span id="installmentClassName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewInstallmentContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-3 mb-0 fw-bold text-primary-custom loading-text">Saving...</p>
    </div>
</div>

<!-- Fee Agreement Modal -->
<div class="modal fade" id="agreementModal" tabindex="-1" aria-labelledby="agreementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary-custom text-white border-0">
                <h5 class="modal-title fw-bold" id="agreementModalLabel">
                    <i class="bi bi-handshake me-2"></i> Student Fee Agreements (Special Arrangements)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-4">
                    <!-- Left Panel: Configuration -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 1;">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-primary-custom mb-3 border-bottom pb-2">
                                    <i class="bi bi-sliders me-2"></i> 1. Setup Class
                                </h6>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Select Class</label>
                                    <select class="form-select border-primary-custom" id="agreement_class_select">
                                        <option value="">Choose Class...</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4 d-none" id="agreement_search_block">
                                    <label class="form-label small fw-bold text-muted">2. Find Student</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-primary-custom"><i class="bi bi-search"></i></span>
                                        <input type="text" id="agreement_student_search" class="form-control border-start-0 border-primary-custom" placeholder="Name or Adm No...">
                                    </div>
                                </div>

                                <div class="mt-5 border-top pt-3 text-center d-none" id="agreement_save_actions">
                                    <p class="text-muted small mb-3">Any changes to agreements will automatically update student balances.</p>
                                    <button type="button" class="btn btn-primary-custom w-100 fw-bold py-2 shadow-sm" id="save_agreements_btn">
                                        <i class="bi bi-save2 me-2"></i> Save All Agreements
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Student List & Fee Options -->
                    <div class="col-md-8">
                        <div id="agreement_placeholder" class="text-center py-5 bg-white border border-dashed rounded">
                            <i class="bi bi-mortarboard text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Select a class to view students and manage agreements.</p>
                            <p class="small text-muted mb-0">Uses Active enrollments for the academic year selected in the header.</p>
                        </div>

                        <div id="agreement_results_container" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-primary-custom"><i class="bi bi-people me-2"></i> Students in <span id="agreement_target_class_name">Class</span></h6>
                                <span class="badge bg-primary-custom rounded-0" id="agreement_student_count">0 Students</span>
                            </div>
                            
                            <div class="agreement-students-list" id="agreement_students_list">
                                <!-- Student rows will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Sponsor Modal -->
<div class="modal fade" id="assignSponsorModal" tabindex="-1" aria-labelledby="assignSponsorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="assignSponsorModalLabel">
                    <i class="bi bi-person-heart"></i> Assign Student Sponsors
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignSponsorForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Panel: Select Sponsor & Class -->
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label fw-bold">1. Select Sponsor</label>
                                <div class="input-group">
                                    <select name="sponsorID" id="sponsor_select" class="form-select" required>
                                        <option value="">Loading sponsors...</option>
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" id="addNewSponsorBtn" title="Register New Sponsor">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">2. Select Class</label>
                                <select name="classID" id="sponsor_class_select" class="form-select" required disabled>
                                    <option value="">Choose class...</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Right Panel: Student Selection -->
                        <div class="col-md-8">
                            <div id="sponsor_students_container" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold mb-0">3. Select Students & Set %</label>
                                    <div class="input-group input-group-sm w-50">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="sponsor_student_search" class="form-control" placeholder="Search student...">
                                    </div>
                                </div>
                                
                                <div class="table-responsive agreement-students-list">
                                    <table class="table table-sm table-hover border">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th width="40" class="text-center align-middle">
                                                    <div class="d-flex justify-content-center align-items-center m-0 p-0">
                                                        <input class="form-check-input m-0 shadow-none" type="checkbox" id="selectAllSponsorStudents" style="position: static; transform: scale(1.1);">
                                                    </div>
                                                </th>
                                                <th>Student Name</th>
                                                <th>ADM No.</th>
                                                <th style="min-width: 100px;">Coverage %</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sponsor_students_list">
                                            <!-- Dynamic Students -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3 p-3 bg-light rounded border">
                                    <h6 class="small fw-bold mb-2">Selection Status:</h6>
                                    <div id="selected_sponsor_count" class="badge bg-primary-custom mb-2">
                                        0 students assigned
                                    </div>
                                    <p class="text-muted small mb-0">Unchecking a student will remove them from this sponsor.</p>
                                </div>
                            </div>
                            
                            <div id="no_sponsor_class_placeholder" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-circle display-4"></i>
                                <p class="mt-2 text-center w-100">Please select a sponsor and a class to view students.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom" id="saveSponsorAssignmentBtn" disabled>
                        <i class="bi bi-save"></i> Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Sponsor Modal (Inline) -->
<div class="modal fade" id="addSponsorInlineModal" tabindex="-1" aria-labelledby="addSponsorInlineModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addSponsorInlineModalLabel">
                    <i class="bi bi-plus-circle"></i> Register New Sponsor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSponsorInlineForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inline_sponsor_name" class="form-label">Sponsor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inline_sponsor_name" name="sponsor_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="inline_description" class="form-label">Description</label>
                        <textarea class="form-control" id="inline_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inline_contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="inline_contact_person" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="inline_phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text">255</span>
                                <input type="text" class="form-control" id="inline_phone" name="phone" placeholder="7XXXXXXXX" pattern="[0-9]{9}" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inline_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="inline_email" name="email">
                        </div>
                        <input type="hidden" name="status" value="Active">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitInlineSponsorBtn">
                        <i class="bi bi-check-circle"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="assignConditionModal" tabindex="-1" aria-labelledby="assignConditionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="assignConditionModalLabel">
                    <i class="bi bi-tags"></i> Assign Fee Condition & Discount
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignConditionForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Panel: Select Class & Conditions -->
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <label class="form-label fw-bold">1. Select Class</label>
                                <select name="classID" id="condition_class_select" class="form-select" required>
                                    <option value="">Choose class...</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div id="conditions_container" style="display: none;">
                                <label class="form-label fw-bold mt-3">2. Select Conditions (Discounts)</label>
                                <div id="class_conditions_list" class="list-group">
                                    <!-- Dynamic Conditions -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Panel: Student Selection -->
                        <div class="col-md-8">
                            <div id="students_selection_container" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold mb-0">3. Select Students for this Discount</label>
                                    <div class="input-group input-group-sm w-50">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="student_search_input" class="form-control" placeholder="Search student name...">
                                    </div>
                                </div>
                                
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-hover border">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th width="40">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAllStudents">
                                                    </div>
                                                </th>
                                                <th>Student Name</th>
                                                <th>ADM No.</th>
                                            </tr>
                                        </thead>
                                        <tbody id="students_list_body">
                                            <!-- Dynamic Students -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3 p-3 bg-light rounded border">
                                    <h6 class="small fw-bold mb-2">Selected Students:</h6>
                                    <div id="selected_students_sum" class="mb-2">
                                        <span id="selected_students_count" class="badge bg-primary-custom">0 students selected</span>
                                    </div>
                                    <div id="selected_students_names" class="small text-muted" style="max-height: 60px; overflow-y: auto;">
                                        None selected
                                    </div>
                                </div>
                            </div>
                            
                            <div id="no_class_placeholder" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-circle display-4"></i>
                                <p class="mt-2 text-center w-100">Please select a class first to see conditions and students.</p>
                                <p class="small text-muted mb-0">Uses Active enrollments for the academic year selected in the header.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom" id="saveConditionBtn" disabled>
                        <i class="bi bi-save"></i> Save Condition Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')

    <!-- ADDING DOWNLOAD PROGRESS MODAL -->
    <div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="bi bi-file-pdf text-danger animate-pulse" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-4" style="color: #940000; font-weight: bold; font-family: 'Century Gothic', sans-serif;">Generating Fees Structure Report</h5>
                    <div class="progress mb-2" style="height: 12px; border-radius: 0 !important;">
                        <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                            role="progressbar" style="width: 0%; border-radius: 0 !important;"></div>
                    </div>
                    <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                    <p class="mt-3 text-muted small">Sawa, tafadhali subiri kidogo wakati tunaandaa ripoti yako ya ada.</p>
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
// Wait for jQuery and Bootstrap to be loaded
(function() {
    function initFeesManagement() {
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            setTimeout(initFeesManagement, 100);
            return;
        }
        
        // Use jQuery
        var $ = jQuery;
        
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle logic for expanded settings
        $(document).on('click', '#toggleFeeSettingsBtn', function() {
            $('#expandedFeeSettings').slideToggle(300);
        });



        // Open Add Fee Modal Button Click Handler
        $(document).on('click', '#openAddFeeModalBtn, #openAddFeeModalBtn_Exp', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Open modal button clicked');
            
            // Check if modal exists
            if ($('#addFeeModal').length === 0) {
                console.error('Modal #addFeeModal not found!');
                alert('Modal not found. Please check console for more details.');
                return;
            }
            
            // Try to show modal
            try {
                $('#addFeeModal').modal('show');
                console.log('Modal show command executed');
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Error showing modal: ' + error.message);
            }
        });

        // Also handle data-toggle for backward compatibility
        $(document).on('click', '[data-toggle="modal"][data-target="#addFeeModal"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Modal opened via data-toggle');
            $('#addFeeModal').modal('show');
        });
        
        // Test if button exists
        console.log('Button exists:', $('#openAddFeeModalBtn').length > 0);
        console.log('Modal exists:', $('#addFeeModal').length > 0);
        console.log('jQuery version:', $.fn.jquery);

        // ==================== NEW FEE STRUCTURE LOGIC ====================
        
        let feeRowCounter = 0;
        
        // Condition Row Template
        function createConditionRow(feeIndex) {
            const condIndex = Date.now() + Math.floor(Math.random() * 1000);
            return `
                <div class="condition-row bg-white p-3 border rounded-0 mb-3" data-cond-id="${condIndex}">
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-5">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Condition Name *</label>
                            <input type="text" class="form-control form-control-sm fee-condition-name" name="fees[${feeIndex}][conditions][${condIndex}][name]" placeholder="e.g. Merit Award" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Amount *</label>
                            <input type="number" class="form-control form-control-sm fee-discount-amount" name="fees[${feeIndex}][conditions][${condIndex}][amount]" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Type</label>
                            <select class="form-select form-select-sm fee-discount-type" name="fees[${feeIndex}][conditions][${condIndex}][type]">
                                <option value="Fixed">Fixed (TZS)</option>
                                <option value="Percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-condition-row" title="Delete Condition">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Description (Optional)</label>
                            <textarea class="form-control form-control-sm" name="fees[${feeIndex}][conditions][${condIndex}][description]" rows="1" placeholder="Short description why this discount is given..."></textarea>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Fee Row Template
        function createFeeRow() {
            feeRowCounter++;
            return `
                <div class="fee-row card border-primary-custom mb-3" data-fee-index="${feeRowCounter}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-receipt"></i> Fee #${feeRowCounter}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-fee-row" ${feeRowCounter === 1 ? 'style="display:none;"' : ''}>
                            <i class="bi bi-trash"></i> Remove Fee
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Fee Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Fee Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control fee-name" name="fees[${feeRowCounter}][name]" placeholder="e.g. Tuition, Transport" required>
                                <small class="text-muted">Short name for this fee</small>
                            </div>
                            
                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control fee-amount" name="fees[${feeRowCounter}][amount]" placeholder="0.00" step="0.01" min="0" required>
                                <small class="text-muted">Fee amount</small>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-bold small">Description</label>
                                <textarea class="form-control fee-description" name="fees[${feeRowCounter}][description]" rows="2" placeholder="Fee description (optional)"></textarea>
                            </div>
                            
                            <!-- Must Start Pay Toggle -->
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input must-start-pay-toggle" type="checkbox" name="fees[${feeRowCounter}][must_start_pay]" id="must_pay_${feeRowCounter}" value="1">
                                    <label class="form-check-label fw-bold" for="must_pay_${feeRowCounter}">
                                        <i class="bi bi-exclamation-triangle text-warning"></i> Must Pay to Start
                                    </label>
                                </div>
                                <small class="text-muted">Student cannot start school until this fee is paid</small>
                            </div>
                            
                            <!-- Payment Deadline (Optional) -->
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input deadline-toggle" type="checkbox" id="deadline_toggle_${feeRowCounter}">
                                    <label class="form-check-label fw-bold" for="deadline_toggle_${feeRowCounter}">
                                        Set Deadline
                                    </label>
                                </div>
                                <small class="text-muted">Parents must pay a certain amount by a date</small>
                            </div>
                            
                            <!-- Deadline Details (Hidden by default) -->
                            <div class="col-12 deadline-details" style="display: none;">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small">Required Amount (TZS)</label>
                                                <input type="number" class="form-control fee-deadline-amount" name="fees[${feeRowCounter}][deadline_amount]" placeholder="0.00" step="0.01" min="0">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small">Deadline Date</label>
                                                <input type="date" class="form-control fee-deadline-date" name="fees[${feeRowCounter}][deadline_date]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Multi-Condition Section -->
                            <div class="col-md-12">
                                <hr>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input condition-toggle" type="checkbox" data-fee-index="${feeRowCounter}" id="condition_toggle_${feeRowCounter}">
                                    <label class="form-check-label fw-bold" for="condition_toggle_${feeRowCounter}">
                                        <i class="bi bi-tags-fill text-primary-custom"></i> Define Fee Conditions (Discounts)
                                    </label>
                                </div>
                                <small class="text-muted text-primary-custom">Define one or more specific conditions for this fee</small>
                            </div>
                            
                            <div class="col-12 condition-details" style="display: none;">
                                <div class="card border-primary-custom bg-light-info">
                                    <div class="card-body">
                                        <div class="conditions-container" id="conditions_container_${feeRowCounter}">
                                            <!-- Additional conditions will land here -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary-custom mt-2 add-condition-row-btn" data-fee-index="${feeRowCounter}">
                                            <i class="bi bi-plus-circle-fill"></i> Add New Condition
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Initialize with one fee row when modal opens
        $('#addFeeModal').on('shown.bs.modal', function() {
            if ($('#fees_rows').children().length === 0) {
                $('#fees_rows').html(createFeeRow());
                calculateTotalFees();
            }
        });
        
        // Reset form when modal closes
        $('#addFeeModal').on('hidden.bs.modal', function() {
            feeRowCounter = 0;
            $('#fees_rows').empty();
            $('#addFeeForm')[0].reset();
            $('#total_fees_display').text('TZS 0.00');
        });
        
        // Add Fee Row
        $(document).on('click', '#add_fee_row_btn', function() {
            $('#fees_rows').append(createFeeRow());
            updateDeleteButtons();
            calculateTotalFees();
        });
        
        // Remove Fee Row
        $(document).on('click', '.remove-fee-row', function() {
            $(this).closest('.fee-row').remove();
            updateDeleteButtons();
            calculateTotalFees();
        });
        
        // Update delete buttons visibility (hide if only one row)
        function updateDeleteButtons() {
            const rowCount = $('.fee-row').length;
            if (rowCount <= 1) {
                $('.remove-fee-row').hide();
            } else {
                $('.remove-fee-row').show();
            }
        }
        
        // Toggle Deadline Details
        $(document).on('change', '.deadline-toggle', function() {
            const row = $(this).closest('.fee-row');
            const deadlineDetails = row.find('.deadline-details');
            
            if ($(this).is(':checked')) {
                deadlineDetails.slideDown();
            } else {
                deadlineDetails.slideUp();
                // Clear deadline fields
                row.find('.fee-deadline-amount').val('');
                row.find('.fee-deadline-date').val('');
            }
        });

        // Overlay Helpers
        function showOverlay(text = "Loading...") {
            $('#loadingOverlay .loading-text').text(text);
            $('#loadingOverlay').addClass('show');
        }
        function hideOverlay() {
            $('#loadingOverlay').removeClass('show');
        }

        // Toggle Condition Details
        $(document).on('change', '.condition-toggle', function() {
            const feeRow = $(this).closest('.fee-row');
            const feeIndex = $(this).attr('data-fee-index');
            const conditionDetails = feeRow.find('.condition-details');
            const container = $(`#conditions_container_${feeIndex}`);
            
            if ($(this).is(':checked')) {
                conditionDetails.slideDown();
                if (container.children().length === 0) {
                    container.append(createConditionRow(feeIndex));
                }
            } else {
                conditionDetails.slideUp();
                // Optional: Clear conditions if unchecked
                // container.empty();
            }
        });

        // Add Condition Row
        $(document).on('click', '.add-condition-row-btn', function() {
            const feeIndex = $(this).attr('data-fee-index');
            const container = $(`#conditions_container_${feeIndex}`);
            container.append(createConditionRow(feeIndex));
        });

        // Remove Condition Row
        $(document).on('click', '.remove-condition-row', function() {
            $(this).closest('.condition-row').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Calculate Total Fees
        function calculateTotalFees() {
            let total = 0;
            $('.fee-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            $('#total_fees_display').text('TZS ' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            
            // Recalculate installments if enabled
            if ($('#allow_installments').is(':checked')) {
                calculateInstallments();
            }
        }
        
        // Update total when fee amounts change
        $(document).on('input', '.fee-amount', function() {
            calculateTotalFees();
        });
        
        // Update calculateInstallments to use total from all fees
        const originalCalculateInstallments = calculateInstallments;
        function calculateInstallments() {
            // Override amount to use total from all fees
            let totalAmount = 0;
            $('.fee-amount').each(function() {
                totalAmount += parseFloat($(this).val()) || 0;
            });
            
            const numberOfInstallments = parseInt($('#number_of_installments').val()) || 0;
            const installmentType = $('#default_installment_type').val();
            
            if (totalAmount > 0 && numberOfInstallments > 0 && installmentType) {
                const container = $('#installment_amounts_container');
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                html += '<thead><tr><th>Installment</th><th>Amount (TZS)</th></tr></thead><tbody>';
                
                let totalCalculated = 0;
                const baseAmount = totalAmount / numberOfInstallments;
                
                for (let i = 1; i <= numberOfInstallments; i++) {
                    let installmentName = '';
                    switch(installmentType) {
                        case 'Semester': installmentName = 'Semester ' + i; break;
                        case 'Month': installmentName = 'Month ' + i; break;
                        case 'Two Months': installmentName = '2 Months (' + i + ')'; break;
                        case 'Term': installmentName = 'Term ' + i; break;
                        case 'Quarter': installmentName = 'Quarter ' + i; break;
                        default: installmentName = 'Installment ' + i;
                    }
                    
                    let installmentAmount = baseAmount;
                    if (i === numberOfInstallments) {
                        installmentAmount = totalAmount - totalCalculated;
                    } else {
                        installmentAmount = Math.floor(baseAmount * 100) / 100;
                    }
                    
                    totalCalculated += installmentAmount;
                    html += `<tr><td><strong>${installmentName}</strong></td><td>TZS ${installmentAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td></tr>`;
                }
                
                html += '</tbody></table></div>';
                html += `<div class="mt-2"><strong>Total: TZS ${totalCalculated.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>`;
                
                container.html(html);
                
                const difference = Math.abs(totalCalculated - totalAmount);
                const validationDiv = $('#installment_total_validation');
                
                if (difference < 0.01) {
                    validationDiv.html('<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle"></i> Installment total matches fee total</div>');
                } else {
                    validationDiv.html('<div class="alert alert-danger mb-0 py-2"><i class="bi bi-exclamation-triangle"></i> Warning: Installment total does not match fee total</div>');
                }
            } else {
                $('#installment_amounts_container').html('<small class="text-muted">Installments will be calculated automatically based on fee total and number of installments.</small>');
                $('#installment_total_validation').html('');
            }
        }
        
        // ==================== END NEW FEE STRUCTURE LOGIC ====================


        // Show/hide installment options based on checkbox - only if partial payment is allowed
        function toggleInstallmentOptions() {
            const allowPartial = $('#allow_partial_payment').is(':checked');
            const allowInstallments = $('#allow_installments').is(':checked');
            
            if (!allowPartial) {
                // If partial payment is not allowed, disable and hide installments
                $('#allow_installments').prop('checked', false);
                $('#installment_options').slideUp();
            } else if (allowInstallments) {
                $('#installment_options').slideDown();
                calculateInstallments();
            } else {
                $('#installment_options').slideUp();
            }
        }

        function toggleEditInstallmentOptions() {
            const allowPartial = $('#edit_allow_partial_payment').is(':checked');
            const allowInstallments = $('#edit_allow_installments').is(':checked');
            
            if (!allowPartial) {
                // If partial payment is not allowed, disable and hide installments
                $('#edit_allow_installments').prop('checked', false);
                $('#edit_installment_options').slideUp();
            } else if (allowInstallments) {
                $('#edit_installment_options').slideDown();
                calculateEditInstallments();
            } else {
                $('#edit_installment_options').slideUp();
            }
        }

        $('#allow_partial_payment').on('change', toggleInstallmentOptions);
        $('#allow_installments').on('change', toggleInstallmentOptions);
        
        $('#edit_allow_partial_payment').on('change', toggleEditInstallmentOptions);
        $('#edit_allow_installments').on('change', toggleEditInstallmentOptions);


        function calculateEditInstallments() {
            const totalAmount = parseFloat($('#edit_fee_amount').val()) || 0;
            const numberOfInstallments = parseInt($('#edit_number_of_installments').val()) || 0;
            const installmentType = $('#edit_default_installment_type').val();
            
            if (totalAmount > 0 && numberOfInstallments > 0 && installmentType) {
                const container = $('#edit_installment_amounts_container');
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                html += '<thead><tr><th>Installment</th><th>Amount (TZS)</th></tr></thead><tbody>';
                
                let totalCalculated = 0;
                const baseAmount = totalAmount / numberOfInstallments;
                
                for (let i = 1; i <= numberOfInstallments; i++) {
                    let installmentName = '';
                    switch(installmentType) {
                        case 'Semester': installmentName = 'Semester ' + i; break;
                        case 'Month': installmentName = 'Month ' + i; break;
                        case 'Two Months': installmentName = 'Two Months ' + i; break;
                        case 'Term': installmentName = 'Term ' + i; break;
                        case 'Quarter': installmentName = 'Quarter ' + i; break;
                        case 'One-time': installmentName = 'One-time Payment'; break;
                        default: installmentName = 'Installment ' + i;
                    }
                    
                    // For the last installment, add any remainder to ensure total matches
                    let installmentAmount = baseAmount;
                    if (i === numberOfInstallments) {
                        installmentAmount = totalAmount - totalCalculated;
                    } else {
                        installmentAmount = Math.floor(baseAmount * 100) / 100; // Round to 2 decimals
                    }
                    
                    totalCalculated += installmentAmount;
                    html += `<tr><td><strong>${installmentName}</strong></td><td>TZS ${installmentAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td></tr>`;
                }
                
                html += '</tbody></table></div>';
                html += `<div class="mt-2"><strong>Total: TZS ${totalCalculated.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>`;
                
                container.html(html);
                
                // Validate total
                const difference = Math.abs(totalCalculated - totalAmount);
                const validationDiv = $('#edit_installment_total_validation');
                
                if (difference < 0.01) { // Allow small floating point differences
                    validationDiv.html('<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle"></i> Installment total matches fee amount</div>');
                } else {
                    validationDiv.html('<div class="alert alert-danger mb-0 py-2"><i class="bi bi-exclamation-triangle"></i> Warning: Installment total does not match fee amount</div>');
                }
            } else {
                $('#edit_installment_amounts_container').html('<small class="text-muted">Enter fee amount, installment type, and number of installments to calculate amounts.</small>');
                $('#edit_installment_total_validation').html('');
            }
        }

        // Show/hide other fees details section based on fee type
        $('#fee_type_select').on('change', function() {
            if ($(this).val() === 'Other Fees') {
                $('#other_fees_details_section').slideDown();
                $('#amount_help_text').text('Total will be calculated from Other Fees Details below');
            } else {
                $('#other_fees_details_section').slideUp();
                $('#amount_help_text').text('Total fee amount');
                // Clear other fees details
                $('#other_fees_details_list').empty();
                updateOtherFeesTotal();
            }
        });

        // Add Other Fee Detail
        let otherFeeDetailCounter = 0;
        $(document).on('click', '#add_other_fee_detail_btn', function() {
            otherFeeDetailCounter++;
            const detailHtml = `
                <div class="other-fee-detail-item border rounded p-3 mb-3" data-detail-index="${otherFeeDetailCounter}">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm other-fee-detail-name" placeholder="e.g., Food, Study Tour, Library" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm other-fee-detail-amount" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger w-100 remove-other-fee-detail" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description (Optional)</label>
                            <textarea class="form-control form-control-sm other-fee-detail-description" rows="2" placeholder="Optional description"></textarea>
                        </div>
                    </div>
                </div>
            `;
            $('#other_fees_details_list').append(detailHtml);
            updateOtherFeesTotal();
        });

        // Remove Other Fee Detail
        $(document).on('click', '.remove-other-fee-detail', function() {
            $(this).closest('.other-fee-detail-item').remove();
            updateOtherFeesTotal();
        });

        // Add Edit Other Fee Detail
        let editOtherFeeDetailCounter = 0;
        $(document).on('click', '#add_edit_other_fee_detail_btn', function() {
            editOtherFeeDetailCounter++;
            const detailHtml = `
                <div class="edit-other-fee-detail-item border rounded p-3 mb-3" data-detail-index="${editOtherFeeDetailCounter}">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm edit-other-fee-detail-name" placeholder="e.g., Food, Study Tour, Library" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm edit-other-fee-detail-amount" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger w-100 remove-edit-other-fee-detail" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description (Optional)</label>
                            <textarea class="form-control form-control-sm edit-other-fee-detail-description" rows="2" placeholder="Optional description"></textarea>
                        </div>
                    </div>
                </div>
            `;
            $('#edit_other_fees_details_list').append(detailHtml);
            updateEditOtherFeesTotal();
        });

        // Remove Edit Other Fee Detail
        $(document).on('click', '.remove-edit-other-fee-detail', function() {
            $(this).closest('.edit-other-fee-detail-item').remove();
            updateEditOtherFeesTotal();
        });

        // Update total when other fee detail amounts change
        $(document).on('input', '.other-fee-detail-amount', function() {
            updateOtherFeesTotal();
        });

        $(document).on('input', '.edit-other-fee-detail-amount', function() {
            updateEditOtherFeesTotal();
        });

        // Function to calculate and update edit other fees total
        function updateEditOtherFeesTotal() {
            let total = 0;
            $('.edit-other-fee-detail-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            $('#edit_other_fees_total').text('TZS ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Update the main amount field if fee type is Other Fees
            if ($('#edit_fee_type_select').val() === 'Other Fees') {
                $('#edit_fee_amount').val(total.toFixed(2));
                calculateEditInstallments();
            }
        }

        // Function to calculate and update other fees total
        function updateOtherFeesTotal() {
            let total = 0;
            $('.other-fee-detail-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            $('#other_fees_total').text('TZS ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Update the main amount field if fee type is Other Fees
            if ($('#fee_type_select').val() === 'Other Fees') {
                $('#fee_amount_input').val(total.toFixed(2));
                calculateInstallments();
            }
        }

        // Real-time calculation on input change
        $('input[name="amount"]').on('input', calculateInstallments);
        $('#number_of_installments').on('input', calculateInstallments);
        $('#default_installment_type').on('change', calculateInstallments);
        
        $('#edit_fee_amount').on('input', calculateEditInstallments);
        $('#edit_number_of_installments').on('input', calculateEditInstallments);
        $('#edit_default_installment_type').on('change', calculateEditInstallments);

    // Add Fee Form Submission
    $('#addFeeForm').on('submit', function(e) {
        e.preventDefault();

        // Ensure checkbox values are properly set
        $('#allow_installments').val($('#allow_installments').is(':checked') ? 1 : 0);
        $('#allow_partial_payment').val($('#allow_partial_payment').is(':checked') ? 1 : 0);
        
        // Collect checkboxes status as numeric values
        const allowInstallmentsCheckbox = $('#allow_installments').is(':checked') ? 1 : 0;
        const allowPartialCheckbox = $('#allow_partial_payment').is(':checked') ? 1 : 0;
        
        let formData = $(this).serializeArray();
        
        // Ensure checkboxes are present in form data even if unchecked
        formData.push({name: 'allow_installments', value: allowInstallmentsCheckbox});
        formData.push({name: 'allow_partial_payment', value: allowPartialCheckbox});
        
        console.log('Form Data to be sent:', formData);
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const modal = $('#addFeeModal');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');
        
        // Show loading overlay
        showOverlay("Saving fee...");
        
        // Disable all form inputs during submission
        modal.find('input, select, textarea').prop('disabled', true);
        
        console.log('Sending request to server...');
        
        $.ajax({
            url: '{{ route("store_fee") }}',
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                console.log('Request sent, waiting for response...');
            },
            success: function(response) {
                console.log('Server response received:', response);
                hideOverlay();
                
                if (response && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Fee assigned successfully',
                        timer: 2500,
                        showConfirmButton: true,
                        confirmButtonColor: '#940000'
                    }).then((result) => {
                        modal.modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message || 'Saved but uncertain response',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr, status, error);
                
                let errorMessage = 'Failed to assign fee. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Handle validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'No connection to server. Please check your connection.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please contact administrator.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                    confirmButtonColor: '#940000'
                });
            },
            complete: function() {
                console.log('Request completed');
                // Hide loading overlay
                hideOverlay();
                // Re-enable form inputs
                modal.find('input, select, textarea').prop('disabled', false);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // View Fee Button Click
    $(document).on('click', '.view-fee-btn', function() {
        const feeID = $(this).data('fee-id');
        const feeType = $(this).data('fee-type');
        
        $.ajax({
            url: `/get_fee/${feeID}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const fee = response.fee;
                    let html = '';
                    
                    // Fee Basic Information
                    html += '<div class="card mb-3">';
                    html += '<div class="card-header bg-light">';
                    html += '<h6 class="mb-0"><i class="bi bi-info-circle"></i> Fee Information</h6>';
                    html += '</div>';
                    html += '<div class="card-body">';
                    html += '<div class="row">';
                    html += '<div class="col-md-6 mb-2"><strong>Fee Name:</strong> ' + (fee.fee_name || 'N/A') + '</div>';
                    html += '<div class="col-md-6 mb-2"><strong>Fee Type:</strong> <span class="badge bg-' + (fee.fee_type === 'Tuition Fees' ? 'success' : 'warning text-dark') + '">' + (fee.fee_type || 'N/A') + '</span></div>';
                    html += '<div class="col-md-6 mb-2"><strong>Total Amount:</strong> <span class="text-primary fw-bold">TZS ' + parseFloat(fee.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span></div>';
                    html += '<div class="col-md-6 mb-2"><strong>Duration:</strong> ' + (fee.duration || 'N/A') + '</div>';
                    if (fee.description) {
                        html += '<div class="col-12 mb-2"><strong>Description:</strong> ' + fee.description + '</div>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Installments Section (for both Tuition and Other Fees)
                    if (fee.allow_installments && fee.installments && fee.installments.length > 0) {
                        html += '<div class="card mb-3">';
                        html += '<div class="card-header bg-success text-white">';
                        html += '<h6 class="mb-0"><i class="bi bi-calendar-range"></i> Payment Installments</h6>';
                        html += '</div>';
                        html += '<div class="card-body">';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-hover">';
                        html += '<thead class="table-light">';
                        html += '<tr><th>Installment</th><th>Type</th><th>Amount (TZS)</th></tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        let installmentTotal = 0;
                        fee.installments.forEach(function(installment) {
                            installmentTotal += parseFloat(installment.amount || 0);
                            html += '<tr>';
                            html += '<td><strong>' + (installment.installment_name || 'N/A') + '</strong></td>';
                            html += '<td><span class="badge bg-info">' + (installment.installment_type || 'N/A') + '</span></td>';
                            html += '<td class="text-success fw-bold">TZS ' + parseFloat(installment.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody>';
                        html += '<tfoot class="table-light">';
                        html += '<tr><th colspan="2">Total</th><th class="text-primary">TZS ' + installmentTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</th></tr>';
                        html += '</tfoot>';
                        html += '</table>';
                        html += '</div>';
                        html += '<div class="mt-2">';
                        html += '<small class="text-muted">';
                        if (fee.allow_partial_payment) {
                            html += '<i class="bi bi-check-circle text-success"></i> Partial payments are allowed';
                        } else {
                            html += '<i class="bi bi-x-circle text-danger"></i> Full installment amount must be paid (no partial payments)';
                        }
                        html += '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    } else {
                        // No installments - show message
                        html += '<div class="card mb-3 border-warning">';
                        html += '<div class="card-body">';
                        html += '<div class="alert alert-warning mb-0">';
                        html += '<i class="bi bi-exclamation-triangle"></i> <strong>Full Payment Required</strong><br>';
                        html += 'This fee must be paid in full. No installment plan is available.';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    // Other Fees Details Section (only for Other Fees)
                    if (feeType === 'Other Fees' && fee.other_fee_details && fee.other_fee_details.length > 0) {
                        html += '<div class="card mb-3">';
                        html += '<div class="card-header bg-warning text-dark">';
                        html += '<h6 class="mb-0"><i class="bi bi-list-ul"></i> Other Fees Breakdown</h6>';
                        html += '</div>';
                        html += '<div class="card-body">';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-hover">';
                        html += '<thead class="table-light">';
                        html += '<tr><th>Item Name</th><th>Description</th><th>Amount (TZS)</th></tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        let otherFeesTotal = 0;
                        fee.other_fee_details.forEach(function(detail) {
                            otherFeesTotal += parseFloat(detail.amount || 0);
                            html += '<tr>';
                            html += '<td><strong>' + (detail.fee_detail_name || 'N/A') + '</strong></td>';
                            html += '<td>' + (detail.description || '-') + '</td>';
                            html += '<td class="text-warning fw-bold">TZS ' + parseFloat(detail.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody>';
                        html += '<tfoot class="table-light">';
                        html += '<tr><th colspan="2">Total Other Fees</th><th class="text-primary">TZS ' + otherFeesTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</th></tr>';
                        html += '</tfoot>';
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    $('#viewFeeContent').html(html);
                    $('#viewFeeModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load fee details'
                });
            }
        });
    });

        // Condition row for edit modal
        function createEditConditionRow(condition = null) {
            const condId = condition ? condition.conditionID : (Date.now() + Math.floor(Math.random() * 1000));
            return `
                <div class="condition-row bg-white p-3 border rounded-0 mb-3" data-cond-id="${condId}">
                    <input type="hidden" name="conditions[${condId}][id]" value="${condition ? condition.conditionID : ''}">
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-5">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Condition Name *</label>
                            <input type="text" class="form-control form-control-sm" name="conditions[${condId}][name]" value="${condition ? condition.condition_name : ''}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Amount *</label>
                            <input type="number" class="form-control form-control-sm" name="conditions[${condId}][amount]" value="${condition ? condition.discount_amount : ''}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Type</label>
                            <select class="form-select form-select-sm" name="conditions[${condId}][type]">
                                <option value="Fixed" ${condition && condition.discount_type === 'Fixed' ? 'selected' : ''}>Fixed</option>
                                <option value="Percentage" ${condition && condition.discount_type === 'Percentage' ? 'selected' : ''}>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-condition-row" title="Delete Condition">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label mb-1 fw-bold small text-primary-custom">Description (Optional)</label>
                            <textarea class="form-control form-control-sm" name="conditions[${condId}][description]" rows="1" placeholder="Why this discount?">${condition ? (condition.description || '') : ''}</textarea>
                        </div>
                    </div>
                </div>
            `;
        }

    // Edit Fee Button Click
    $(document).on('click', '.edit-fee-btn', function() {
        const feeID = $(this).data('fee-id');
        showOverlay("Loading details...");
        
        $.get("{{ url('get_fee') }}/" + feeID, function(response) {
            hideOverlay();
            if (response.success) {
                const fee = response.fee;
                $('#edit_fee_id').val(fee.feeID);
                $('#edit_fee_class_select').val(fee.classID);
                $('#edit_fee_name').val(fee.fee_name);
                $('#edit_must_start_pay').prop('checked', fee.must_start_pay == 1);
                $('#edit_fee_amount').val(fee.amount);
                $('#edit_deadline_amount').val(fee.payment_deadline_amount || '');
                $('#edit_deadline_date').val(fee.payment_deadline_date);
                $('#edit_fee_duration_select').val(fee.duration);
                
                // Populate Conditions
                const editConditionsContainer = $('#edit_conditions_container');
                editConditionsContainer.empty();
                const activeConditions = fee.fee_conditions && fee.fee_conditions.filter(c => c.status === 'Active');
                
                if (activeConditions && activeConditions.length > 0) {
                    $('#edit_has_conditions').prop('checked', true);
                    $('#edit_conditions_details').show();
                    activeConditions.forEach(cond => {
                        editConditionsContainer.append(createEditConditionRow(cond));
                    });
                } else {
                    $('#edit_has_conditions').prop('checked', false);
                    $('#edit_conditions_details').hide();
                }
                
                // Handle installments checkbox
                $('#edit_fee_description').val(fee.description || '');
                
                if (fee.allow_installments) {
                    $('#edit_allow_installments').prop('checked', true);
                    $('#edit_installment_options').show();
                    $('#edit_default_installment_type').val(fee.default_installment_type);
                    $('#edit_number_of_installments').val(fee.number_of_installments);
                } else {
                    $('#edit_allow_installments').prop('checked', false);
                    $('#edit_installment_options').hide();
                }
                
                $('#editFeeModal').modal('show');
            }
        }).fail(function() {
            hideOverlay();
            Swal.fire('Error', 'Failed to load fee details', 'error');
        });
    });

    // Toggle condition in edit modal
    $('#edit_has_conditions').on('change', function() {
        const container = $('#edit_conditions_container');
        if ($(this).is(':checked')) {
            $('#edit_conditions_details').slideDown();
            if (container.children().length === 0) {
                container.append(createEditConditionRow());
            }
        } else {
            $('#edit_conditions_details').slideUp();
        }
    });

    $(document).on('click', '#add_edit_condition_btn', function() {
        $('#edit_conditions_container').append(createEditConditionRow());
    });

    // Open Assign Fee Modal
    window.openAssignFeeModal = function() {
        $('#addFeeForm')[0].reset();
        $('#fee_class_select').val(''); // Reset class selection
        $('#addFeeModal').modal('show');
    };

    // Edit Fee Form Submission
    $('#editFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate installment amounts match total fee
        const totalAmount = parseFloat($('#edit_fee_amount').val()) || 0;
        const allowInstallments = $('#edit_allow_installments').is(':checked');
        const numberOfInstallments = parseInt($('#edit_number_of_installments').val()) || 0;
        
        if (allowInstallments && numberOfInstallments > 0) {
            const calculatedTotal = (totalAmount / numberOfInstallments) * numberOfInstallments;
            const difference = Math.abs(calculatedTotal - totalAmount);
            
            if (difference > 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Installment amounts do not match the total fee amount. Please check your calculations.',
                    confirmButtonColor: '#940000'
                });
                return;
            }
        }
        
        // Collect checkboxes status as numeric values
        const allowInstallmentsCheckbox = $('#edit_allow_installments').is(':checked') ? 1 : 0;
        const allowPartialCheckbox = $('#edit_allow_partial_payment').is(':checked') ? 1 : 0;
        const mustStartPayCheckbox = $('#edit_must_start_pay').is(':checked') ? 1 : 0;
        
        let formData = $(this).serializeArray();
        
        // Ensure checkboxes are present in form data even if unchecked
        formData.push({name: 'allow_installments', value: allowInstallmentsCheckbox});
        formData.push({name: 'allow_partial_payment', value: allowPartialCheckbox});
        formData.push({name: 'must_start_pay', value: mustStartPayCheckbox});

        const feeID = $('#edit_fee_id').val();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const modal = $('#editFeeModal');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...');
        
        // Show loading overlay
        showOverlay("Saving changes...");
        
        // Disable all form inputs during submission
        modal.find('input, select, textarea').prop('disabled', true);
        
        console.log('Sending update request to server...');
        
        $.ajax({
            url: `/update_fee/${feeID}`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                console.log('Update request sent, waiting for response...');
            },
            success: function(response) {
                console.log('Server response received:', response);
                
                if (response && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Fee updated successfully',
                        timer: 2500,
                        showConfirmButton: true,
                        confirmButtonColor: '#940000'
                    }).then((result) => {
                        modal.modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message || 'Updated but received no definitive response',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr, status, error);
                
                let errorMessage = 'Failed to update fee. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'No server connection. Please check your internet.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please contact administrator.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                    confirmButtonColor: '#940000'
                });
            },
            complete: function() {
                console.log('Update request completed');
                // Hide loading overlay
                hideOverlay();
                // Re-enable form inputs
                modal.find('input, select, textarea').prop('disabled', false);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Delete Fee Button Click
    $(document).on('click', '.delete-fee-btn', function() {
        const feeID = $(this).data('fee-id');
        const feeName = $(this).data('fee-name');
        const deleteBtn = $(this);
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to delete "${feeName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading on button
                const originalHtml = deleteBtn.html();
                deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                
                console.log('Sending delete request to server...');
                
                $.ajax({
                    url: `/delete_fee/${feeID}`,
                    method: 'DELETE',
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('Delete request sent, waiting for response...');
                    },
                    success: function(response) {
                        console.log('Server response received:', response);
                        
                        if (response && response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Fee deleted successfully',
                                timer: 2000,
                                showConfirmButton: true,
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message || 'Attempted to delete but received no definitive response',
                                confirmButtonColor: '#940000'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr, status, error);
                        
                        let errorMessage = 'Failed to delete fee. Please try again.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 0) {
                            errorMessage = 'No server connection. Please check your internet.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error. Please contact administrator.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage,
                            confirmButtonColor: '#940000'
                        });
                    },
                    complete: function() {
                        console.log('Delete request completed');
                        deleteBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            }
        });
    });

        // View Installment Button Click - For individual fee
        $(document).on('click', '.view-installment-btn', function() {
            const feeID = $(this).data('fee-id');
            const feeType = $(this).data('fee-type');
            
            $('#viewInstallmentContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-3">Loading...</p></div>');
            $('#viewInstallmentModal').modal('show');
            
            // Get fee details with installments
            $.ajax({
                url: `/get_fee/${feeID}`,
                method: 'GET',
                success: function(feeResponse) {
                    if (feeResponse.success) {
                        const fee = feeResponse.fee;
                        $('#installmentClassName').text(fee.fee_name);
                        
                        // Use installments from fee response if available
                        let installments = [];
                        if (feeResponse.fee && feeResponse.fee.installments) {
                            installments = feeResponse.fee.installments;
                        } else if (feeResponse.installments) {
                            installments = feeResponse.installments;
                        }
                        
                        // Process installments
                        const response = { success: true, installments: installments };
                        if (response.success) {
                            let html = '';
                            
                            if (response.installments && response.installments.length > 0) {
                                // Sort installments by installment_number
                                response.installments.sort(function(a, b) {
                                    return (a.installment_number || 0) - (b.installment_number || 0);
                                });
                                
                                html += '<div class="table-responsive">';
                                html += '<table class="table table-bordered table-hover mb-0">';
                                html += '<thead style="background-color: #940000; color: white;">';
                                html += '<tr>';
                                html += '<th style="width: 60%; color: white;">Installment</th>';
                                html += '<th style="width: 40%; color: white;" class="text-end">Amount (TZS)</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';
                                
                                let total = 0;
                                response.installments.forEach(function(inst) {
                                    const amount = parseFloat(inst.amount || 0);
                                    total += amount;
                                    
                                    html += '<tr>';
                                    html += '<td><strong>' + (inst.installment_name || 'N/A') + '</strong></td>';
                                    html += '<td class="text-end"><strong>' + amount.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong></td>';
                                    html += '</tr>';
                                });
                                
                                html += '</tbody>';
                                html += '<tfoot class="table-light">';
                                html += '<tr>';
                                html += '<th class="text-end">Total:</th>';
                                html += '<th class="text-end text-primary">' + total.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</th>';
                                html += '</tr>';
                                html += '</tfoot>';
                                html += '</table>';
                                html += '</div>';
                            } else {
                                html = '<div class="alert alert-warning mb-0">';
                                html += '<i class="bi bi-exclamation-triangle"></i> No installments allowed';
                                html += '</div>';
                            }
                            
                            $('#viewInstallmentContent').html(html);
                        } else {
                            $('#viewInstallmentContent').html('<div class="alert alert-danger">Failed to retrieve installments. ' + (response.message || '') + '</div>');
                        }
                    } else {
                        $('#viewInstallmentContent').html('<div class="alert alert-danger">Failed to retrieve fee details.</div>');
                    }
                },
                error: function(xhr) {
                    $('#viewInstallmentContent').html('<div class="alert alert-danger">Failed to retrieve fee details.</div>');
                }
            });
        });

        // Edit Class Fees Button Click - Opens Add Fee Modal with class pre-selected (for empty classes)
        $(document).on('click', '.edit-class-fees-btn', function() {
            const classID = $(this).data('class-id');
            $('#fee_class_select').val(classID);
            $('#addFeeModal').modal('show');
    });

        // Reset forms when modals are closed
        $('#addFeeModal').on('hidden.bs.modal', function() {
            $('#addFeeForm')[0].reset();
        });
        
        $('#editFeeModal').on('hidden.bs.modal', function() {
            $('#editFeeForm')[0].reset();
        });

        // Export PDF Function
        // Export PDF Function (Standardized AJAX Server-Side Method)
        $(document).on('click', '#exportPdfBtn, #exportPdfBtn_Exp', function(e) {
            if (e) e.preventDefault();
            const classID = $('#classFilter').val() || '';
            const className = classID ? $('#classFilter option:selected').text().trim() : 'All_Classes';
            
            let url = '{{ route("admin.export_fees_structure_pdf") }}';
            if (classID) {
                url += '?classID=' + classID;
            }
            
            // Show progress modal
            $('#pdfProgressTitle').text('Generating Fees Structure');
            $('#downloadProgressModal').modal('show');
            $('#pdfProgressBar').css('width', '0%');
            $('#pdfProgressPercent').text('0%');

            let progress = 0;
            const interval = setInterval(function() {
                if (progress < 95) {
                    let inc = 2 + Math.random() * 5;
                    progress += inc;
                    if (progress > 95) progress = 95;
                    
                    $('#pdfProgressBar').css('width', progress + '%');
                    $('#pdfProgressPercent').text(Math.round(progress) + '%');
                }
            }, 300);

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Server error: ' + response.statusText);
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
                        
                        const timestamp = new Date().toISOString().slice(0, 10);
                        a.download = `Fees_Structure_${className}_${timestamp}.pdf`;
                        
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                        
                        setTimeout(() => {
                            $('#downloadProgressModal').modal('hide');
                        }, 500);
                    }, 400);
                })
                .catch(error => {
                    clearInterval(interval);
                    $('#downloadProgressModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: 'Failed to generate PDF. ' + error.message,
                        confirmButtonColor: '#940000'
                    });
                    console.error('PDF Export Error:', error);
                });
        });

        // Export Excel Function
        $(document).on('click', '#exportExcelBtn, #exportExcelBtn_Exp', function(e) {
            if (e) e.preventDefault();
            // Get school name from blade variables
            const schoolName = '{{ $schoolName }}';
            
            // Collect fee data directly from table
            const feesData = [];
            
            let currentClassName = '';
            
            $('table tbody tr').each(function() {
                // Check for "No fees"
                if ($(this).text().includes('No fees assigned')) return;
                
                let row = {
                    className: '',
                    feeName: '',
                    amount: '',
                    deadline: ''
                };
                
                let hasClassColumn = $(this).find('td[rowspan]').length > 0;
                
                if (hasClassColumn) {
                    let classCell = $(this).find('td[rowspan]').clone();
                    classCell.find('div.mt-2').remove(); // Remove total block
                    currentClassName = classCell.text().trim();
                }
                
                row.className = currentClassName;
                
                // Fee Name
                let feeNameCell;
                if (hasClassColumn) {
                    feeNameCell = $(this).find('td').eq(1);
                } else {
                    feeNameCell = $(this).find('td').eq(0);
                }
                let feeName = feeNameCell.find('span.fw-bold').text().trim();
                let feeDesc = feeNameCell.find('small.text-muted').text().trim();
                row.feeName = feeName + (feeDesc ? ' (' + feeDesc + ')' : '');
                
                // Amount
                let amountCell;
                if (hasClassColumn) {
                    amountCell = $(this).find('td').eq(2);
                } else {
                    amountCell = $(this).find('td').eq(1);
                }
                row.amount = amountCell.text().trim();
                
                // Deadline
                let deadlineCell;
                if (hasClassColumn) {
                    deadlineCell = $(this).find('td').eq(3);
                } else {
                    deadlineCell = $(this).find('td').eq(2);
                }
                row.deadline = deadlineCell.text().trim();
                
                feesData.push(row);
            });
            
            // Prepare data with header
            const data = [];
            
            // Add school name (centered)
            data.push([schoolName]);
            data.push(['FEES STRUCTURE FOR ALL CLASSES']);
            data.push(['']); // Empty row
            
            // Create headers
            const tableHeaders = ['Class', 'Fee Name', 'Amount', 'Deadline'];
            data.push(tableHeaders);
            
            // Add data rows
            feesData.forEach(function(row) {
                data.push([row.className, row.feeName, row.amount, row.deadline]);
            });
            
            // Add footer row
            const totalText = $('table tfoot strong.text-primary-custom').text().trim();
            data.push(['', '', 'TOTAL', totalText]);
            
            // Create workbook
            const wb = XLSX.utils.book_new();
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 15 }, // Class
                { wch: 40 }, // Fee Name
                { wch: 20 }, // Amount
                { wch: 20 }  // Deadline
            ];
            
            // Merge cells for header
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: tableHeaders.length - 1 } }); // School name
            ws['!merges'].push({ s: { r: 1, c: 0 }, e: { r: 1, c: tableHeaders.length - 1 } }); // Title
            
            // Style header cells
            if (ws['A1']) {
                ws['A1'].s = { font: { bold: true, sz: 16 }, alignment: { horizontal: 'center' } };
            }
            if (ws['A2']) {
                ws['A2'].s = { font: { bold: true, sz: 14 }, alignment: { horizontal: 'center' } };
            }
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Fees Structure');
            
            // Save file
            XLSX.writeFile(wb, 'Fees_Structure_' + new Date().toISOString().split('T')[0] + '.xlsx');
        });

        // ==================== FEE CONDITION MANAGEMENT ====================
        
        let allStudentsForClass = [];
        let selectedStudentIDs = new Set();
        let allConditionsData = []; // Store full condition objects including assigned students

        // Open Condition Modal
        $(document).on('click', '#openConditionModalBtn, #openConditionModalBtn_Exp', function(e) {
            if (e) e.preventDefault();
            $('#assignConditionModal').modal('show');
        });

        // On Class selection in Condition Modal
        $('#condition_class_select').on('change', function() {
            const classID = $(this).val();
            if (!classID) {
                $('#conditions_container, #students_selection_container').hide();
                $('#no_class_placeholder').show();
                return;
            }

            $('#no_class_placeholder').hide();
            showOverlay("Loading...");

            // Load Conditions and Students for this class (Active enrollments / header year)
            Promise.all([
                $.get("{{ url('get_class_conditions') }}/" + classID),
                $.get("{{ url('get_class_students') }}/" + classID)
            ]).then(([conditionsRes, studentsRes]) => {
                hideOverlay();

                if (studentsRes.success === false || studentsRes.error_code === 'no_academic_year') {
                    Swal.fire('Academic year required', studentsRes.message || 'Select an academic year in the header.', 'warning');
                    $('#conditions_container, #students_selection_container').hide();
                    $('#no_class_placeholder').show();
                    return;
                }
                
                // 1. Store and Render Conditions
                allConditionsData = conditionsRes.conditions || [];
                renderConditions(allConditionsData);
                
                // 2. Setup Students
                allStudentsForClass = studentsRes.students || [];
                selectedStudentIDs.clear();
                $('#selectAllStudents').prop('checked', false);
                renderStudents(allStudentsForClass, studentsRes.error_code);
                
                $('#conditions_container, #students_selection_container').fadeIn();
                updateAssignmentUI();
            }).catch(err => {
                hideOverlay();
                const msg = (err && err.responseJSON && err.responseJSON.message)
                    ? err.responseJSON.message
                    : 'Failed to load data for this class';
                Swal.fire('Error', msg, 'error');
                $('#conditions_container, #students_selection_container').hide();
                $('#no_class_placeholder').show();
            });
        });

        function renderConditions(conditions) {
            const container = $('#class_conditions_list');
            container.empty();

            if (!conditions || conditions.length === 0) {
                container.append('<div class="alert alert-warning small">No conditional fees defined for this class yet. Define them in the "Assign Fee to Class" modal.</div>');
                return;
            }

            conditions.forEach(cond => {
                const discountText = cond.discount_type === 'Percentage' ? cond.discount_amount + '%' : 'TZS ' + parseFloat(cond.discount_amount).toLocaleString();
                const assignedCount = cond.assigned_student_ids ? cond.assigned_student_ids.length : 0;
                
                container.append(`
                    <div class="list-group-item list-group-item-action flex-column align-items-start border-info bg-light-info mb-2">
                        <div class="form-check">
                            <input class="form-check-input condition-checkbox" type="checkbox" value="${cond.conditionID}" id="cond_${cond.conditionID}">
                            <label class="form-check-label w-100" for="cond_${cond.conditionID}">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h6 class="mb-1 fw-bold text-primary-custom small">${cond.condition_name}</h6>
                                    <span class="badge badge-primary-custom small">${assignedCount} Assigned</span>
                                </div>
                                <p class="mb-1 small">
                                    <strong>Fee:</strong> ${cond.fee.fee_name}<br>
                                    <strong>Discount:</strong> <span class="badge bg-success">${discountText} OFF</span>
                                </p>
                            </label>
                        </div>
                    </div>
                `);
            });
        }

        function renderStudents(students, errorCode) {
            const body = $('#students_list_body');
            body.empty();

            if (!students || students.length === 0) {
                const emptyMsg = errorCode === 'no_active_enrollments'
                    ? 'No Active enrollments for this class in the selected academic year.'
                    : 'No Active enrollments found for this class.';
                body.append(`<tr><td colspan="3" class="text-center py-3 text-muted">${emptyMsg}</td></tr>`);
                return;
            }

            students.forEach(student => {
                const isSelected = selectedStudentIDs.has(parseInt(student.studentID));
                const canAssign = student.can_assign !== false;
                body.append(`
                    <tr class="${canAssign ? '' : 'table-secondary text-muted'}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input student-assignment-checkbox" type="checkbox" value="${student.studentID}" ${isSelected ? 'checked' : ''} ${canAssign ? '' : 'disabled'} data-name="${student.first_name} ${student.last_name}">
                            </div>
                        </td>
                        <td class="small fw-bold">${student.first_name} ${student.middle_name || ''} ${student.last_name}</td>
                        <td class="small text-muted">${student.admission_number || '-'}</td>
                    </tr>
                `);
            });
        }

        // Handle Condition selection change
        $(document).on('change', '.condition-checkbox', function() {
            const checkedConditions = $('.condition-checkbox:checked').map(function() { return parseInt($(this).val()); }).get();
            
            // Clear current set and rebuild it based on all checked conditions
            selectedStudentIDs.clear();
            
            checkedConditions.forEach(condID => {
                const cond = allConditionsData.find(c => c.conditionID === condID);
                if (cond && cond.assigned_student_ids) {
                    cond.assigned_student_ids.forEach(sid => selectedStudentIDs.add(parseInt(sid)));
                }
            });
            
            // Re-render student list to show checked checkboxes
            renderStudents(allStudentsForClass);
            updateAssignmentUI();
        });

        // Student Search
        $('#student_search_input').on('keyup', function() {
            const term = $(this).val().toLowerCase();
            const filtered = allStudentsForClass.filter(s => 
                (s.first_name + ' ' + (s.middle_name || '') + ' ' + s.last_name).toLowerCase().includes(term) ||
                (s.admission_number || '').toLowerCase().includes(term)
            );
            renderStudents(filtered);
        });

        // Select All Students
        $('#selectAllStudents').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.student-assignment-checkbox:visible').each(function() {
                const id = parseInt($(this).val());
                if (isChecked) selectedStudentIDs.add(id);
                else selectedStudentIDs.delete(id);
                $(this).prop('checked', isChecked);
            });
            updateAssignmentUI();
        });

        // Individual Student Toggle
        $(document).on('change', '.student-assignment-checkbox', function() {
            const id = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedStudentIDs.add(id);
            } else {
                selectedStudentIDs.delete(id);
                $('#selectAllStudents').prop('checked', false);
            }
            updateAssignmentUI();
        });

        function updateAssignmentUI() {
            const selectedCount = selectedStudentIDs.size;
            const conditionsCount = $('.condition-checkbox:checked').length;
            
            $('#selected_students_count').text(`${selectedCount} students selected`);
            
            // Render names
            const names = [];
            selectedStudentIDs.forEach(id => {
                const student = allStudentsForClass.find(s => s.studentID === id);
                if (student) names.push(student.first_name + ' ' + student.last_name);
            });
            $('#selected_students_names').text(names.length > 0 ? names.join(', ') : 'None selected');

            // Enable/Disable Save button
            $('#saveConditionBtn').prop('disabled', selectedCount === 0 || conditionsCount === 0);
        }

        // Toggle condition in edit modal
        $('#edit_has_condition').on('change', function() {
            if ($(this).is(':checked')) {
                $('#edit_condition_details').slideDown();
                $('#edit_condition_name, #edit_discount_amount').prop('required', true);
            } else {
                $('#edit_condition_details').slideUp();
                $('#edit_condition_name, #edit_discount_amount').prop('required', false);
            }
        });

        // Handle Condition Form Submission
        $('#assignConditionForm').on('submit', function(e) {
            e.preventDefault();
            
            const studentIDsArray = Array.from(selectedStudentIDs);
            const conditionIDs = $('.condition-checkbox:checked').map(function() { return $(this).val(); }).get();
            const classID = $('#condition_class_select').val();

            $('#loadingOverlay').addClass('show');

            $.ajax({
                url: "{{ route('assign_students_to_conditions') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    classID: classID,
                    studentIDs: studentIDsArray,
                    conditionIDs: conditionIDs
                },
                success: function(response) {
                    $('#loadingOverlay').removeClass('show');
                    if (response.success) {
                        $('#assignConditionModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to save assignments: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
                },
                complete: function() {
                    $('#loadingOverlay').removeClass('show');
                }
            });
        });
        // Agreement Modal Logic
        let classStudentsForAgreement = [];
        let classFeesForAgreement = [];

        $(document).on('click', '#openAgreementModalBtn, #openAgreementModalBtn_Exp', function(e) {
            if (e) e.preventDefault();
            $('#agreementModal').modal('show');
        });

        $('#agreement_class_select').on('change', function() {
            const classID = $(this).val();
            if (!classID) {
                $('#agreement_placeholder').removeClass('d-none');
                $('#agreement_results_container').addClass('d-none');
                $('#agreement_search_block, #agreement_save_actions').addClass('d-none');
                return;
            }

            $('#agreement_target_class_name').text($(this).find('option:selected').text());
            showOverlay("Loading data...");

            // Fetch students, fees and existing agreements (Active enrollments / header year)
            Promise.all([
                $.get("{{ url('get_class_students') }}/" + classID),
                $.get("{{ url('get_class_fees') }}/" + classID),
                $.get("{{ url('get_existing_agreements') }}/" + classID)
            ]).then(([studentsRes, feesRes, agreementsRes]) => {
                hideOverlay();

                if (studentsRes.success === false || studentsRes.error_code === 'no_academic_year') {
                    Swal.fire('Academic year required', studentsRes.message || 'Select an academic year in the header.', 'warning');
                    $('#agreement_placeholder').removeClass('d-none');
                    $('#agreement_results_container, #agreement_search_block, #agreement_save_actions').addClass('d-none');
                    return;
                }

                classStudentsForAgreement = studentsRes.students || [];
                classFeesForAgreement = feesRes.fees || [];
                const existingAgreements = agreementsRes.agreements || [];
                const emptyCode = studentsRes.error_code || agreementsRes.error_code || null;
                
                $('#agreement_student_count').text(classStudentsForAgreement.length + ' Students');
                $('#agreement_placeholder').addClass('d-none');
                $('#agreement_results_container').removeClass('d-none');
                $('#agreement_search_block, #agreement_save_actions').removeClass('d-none');
                
                renderAgreementStudents(classStudentsForAgreement, existingAgreements, emptyCode);
            }).catch(err => {
                hideOverlay();
                const msg = (err && err.responseJSON && err.responseJSON.message)
                    ? err.responseJSON.message
                    : 'Failed to load class data';
                Swal.fire('Error', msg, 'error');
                $('#agreement_placeholder').removeClass('d-none');
                $('#agreement_results_container, #agreement_search_block, #agreement_save_actions').addClass('d-none');
            });
        });

        function renderAgreementStudents(students, existingAgreements = [], emptyCode = null) {
            const container = $('#agreement_students_list');
            container.empty();

            if (students.length === 0) {
                const emptyMsg = emptyCode === 'no_active_enrollments'
                    ? 'No Active enrollments for this class in the selected academic year.'
                    : 'No students found matching your search.';
                container.html(`<div class="alert alert-info border-0 rounded-0">${emptyMsg}</div>`);
                return;
            }

            students.forEach(student => {
                // Find all agreements for this student
                const studentAgreements = existingAgreements.filter(a => String(a.studentID) === String(student.studentID));
                const hasAgreements = studentAgreements.length > 0;
                
                const name = `${student.first_name} ${student.middle_name || ''} ${student.last_name}`;
                const card = `
                    <div class="student-agreement-card border bg-white mb-2 p-0 overflow-hidden shadow-sm" data-id="${student.studentID}">
                        <div class="p-3 d-flex align-items-center justify-content-between cursor-pointer student-header" style="background: ${hasAgreements ? 'linear-gradient(135deg, #FFF0F0 0%, #FFFFFF 100%)' : 'linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%)'};">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input student-check" type="checkbox" data-id="${student.studentID}" ${hasAgreements ? 'checked' : ''}>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">${name}</h6>
                                    <small class="text-muted">Adm No: ${student.admission_number || 'N/A'}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge ${hasAgreements ? 'bg-primary-custom text-white' : 'bg-white text-primary-custom border border-primary-custom'} small ${hasAgreements ? '' : 'd-none'}" id="agreement_count_${student.studentID}">${studentAgreements.length} Agreement${studentAgreements.length !== 1 ? 's' : ''}</span>
                                <i class="bi bi-chevron-down text-muted transition-icon"></i>
                            </div>
                        </div>
                        
                        <div class="student-details p-3 border-top" style="display: ${hasAgreements ? 'block' : 'none'}; background: #fafafa;">
                            <p class="small fw-bold text-primary-custom mb-3 border-bottom pb-1">
                                <i class="bi bi-list-check me-1"></i> Class Fees - Select to Override
                            </p>
                            <div class="row g-2">
                                ${classFeesForAgreement.map(fee => {
                                    const ag = studentAgreements.find(a => String(a.feeID) === String(fee.feeID));
                                    const amount = ag ? parseFloat(ag.agreed_amount).toFixed(0) : '';
                                    
                                    return `
                                        <div class="col-12 fee-agreement-line mb-1 border bg-white transition-all ${ag ? 'border-primary-custom shadow-sm' : ''}">
                                            <div class="d-flex justify-content-between align-items-center p-2 cursor-pointer fee-toggle-header">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi ${ag ? 'bi-caret-down-fill text-dark' : 'bi-caret-right-fill text-primary-custom'} me-2 fee-caret"></i>
                                                    <span class="small fw-bold text-dark">${fee.fee_name}</span>
                                                </div>
                                                <span class="badge bg-light text-dark border extra-small">Std: TZS ${parseFloat(fee.amount).toLocaleString()}</span>
                                            </div>
                                            
                                            <div class="fee-input-section p-3 border-top bg-light" style="display: ${ag ? 'block' : 'none'};">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-primary-custom border-primary-custom fw-bold">Agreed Amount (TZS)</span>
                                                    <input type="number" class="form-control border-primary-custom agreed-amount-input" 
                                                           data-student-id="${student.studentID}" 
                                                           data-fee-id="${fee.feeID}" 
                                                           placeholder="Enter negotiated amount..." 
                                                           value="${amount}"
                                                           min="0" step="1000">
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <small class="text-muted extra-small"><i class="bi bi-info-circle me-1"></i> Custom price for this student only.</small>
                                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 extra-small clear-agreement-btn">Clear</button>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        // Toggle student cards
        $(document).on('click', '.student-header', function(e) {
            if ($(e.target).hasClass('form-check-input')) return;
            
            const card = $(this).closest('.student-agreement-card');
            const details = card.find('.student-details');
            const icon = $(this).find('.transition-icon');
            const checkbox = card.find('.student-check');

            details.slideToggle(200);
            icon.toggleClass('bi-chevron-down bi-chevron-up');
            
            // Auto check if opening
            if (details.css('display') === 'none') {
                checkbox.prop('checked', true);
            }
        });

        // Toggle Fee sections
        $(document).on('click', '.fee-toggle-header', function() {
            const section = $(this).closest('.fee-agreement-line').find('.fee-input-section');
            const caret = $(this).find('.fee-caret');
            
            section.slideToggle(150);
            caret.toggleClass('bi-caret-right-fill bi-caret-down-fill text-primary-custom text-dark');
            $(this).closest('.fee-agreement-line').toggleClass('border-primary-custom shadow-sm');
        });

        $(document).on('click', '.clear-agreement-btn', function() {
            $(this).closest('.fee-input-section').find('.agreed-amount-input').val('');
            updateAgreementBadgeInternal($(this).closest('.student-agreement-card'));
        });

        $(document).on('input', '.agreed-amount-input', function() {
            updateAgreementBadgeInternal($(this).closest('.student-agreement-card'));
        });

        function updateAgreementBadgeInternal(card) {
            const studentID = card.data('id');
            const count = card.find('.agreed-amount-input').filter(function() { return $(this).val() !== ''; }).length;
            const badge = $(`#agreement_count_${studentID}`);
            
            if (count > 0) {
                badge.removeClass('d-none').text(`${count} Agreement${count > 1 ? 's' : ''}`);
            } else {
                badge.addClass('d-none');
            }
        }

        $(document).on('change', '.student-check', function() {
            const details = $(this).closest('.student-agreement-card').find('.student-details');
            if ($(this).is(':checked')) {
                details.slideDown(200);
            } else {
                details.slideUp(200);
            }
        });

        $('#agreement_student_search').on('input', function() {
            const term = $(this).val().toLowerCase();
            const filtered = classStudentsForAgreement.filter(s => 
                (s.first_name + ' ' + (s.middle_name || '') + ' ' + s.last_name).toLowerCase().includes(term) ||
                (s.admission_number || '').toLowerCase().includes(term)
            );
            renderAgreementStudents(filtered);
        });

        $('#save_agreements_btn').on('click', function() {
            const agreements = [];
            $('.agreed-amount-input').each(function() {
                const val = $(this).val();
                if (val !== '' && val !== null) {
                    agreements.push({
                        studentID: $(this).data('student-id'),
                        feeID: $(this).data('fee-id'),
                        amount: val
                    });
                }
            });

            if (agreements.length === 0) {
                Swal.fire('Info', 'No agreed amounts entered to save.', 'info');
                return;
            }

            const saveBtn = $(this);
            const originalText = saveBtn.html();

            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');
            showOverlay("Saving...");

            $.ajax({
                url: "{{ route('store_fee_agreements') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    classID: $('#agreement_class_select').val(),
                    agreements: agreements,
                    schoolID: "{{ session('schoolID') }}"
                },
                success: function(response) {
                    hideOverlay();
                    saveBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire({
                            title: 'Agreements Saved!',
                            text: response.message || 'Balances updated.',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    hideOverlay();
                    saveBtn.prop('disabled', false).html(originalText);
                    Swal.fire('Error', 'Failed to save. ' + (xhr.responseJSON?.message || ''), 'error');
                },
                complete: function() {
                    hideOverlay();
                    saveBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // ==================== SPONSOR ASSIGNMENT LOGIC ====================
        let classStudentsForSponsor = [];

        function loadSponsors(selectedId = null) {
            $.get("{{ route('get_sponsors') }}", function(res) {
                if (res.success) {
                    let html = '<option value="">Choose a sponsor...</option>';
                    res.sponsors.forEach(function(sp) {
                        html += `<option value="${sp.sponsorID}" ${selectedId == sp.sponsorID ? 'selected' : ''}>${sp.sponsor_name} (${sp.status})</option>`;
                    });
                    $('#sponsor_select').html(html);
                }
            });
        }

        $('#openSponsorModalBtn, #openSponsorModalBtn_Exp').on('click', function(e) {
            if (e) e.preventDefault();
            e.preventDefault();
            loadSponsors();
            // Reset modal
            $('#sponsor_class_select').val('').prop('disabled', true);
            $('#sponsor_students_container').hide();
            $('#no_sponsor_class_placeholder').show();
            // Show modal
            $('#assignSponsorModal').modal('show');
        });

        $('#addNewSponsorBtn').on('click', function() {
            // Option to add directly without leaving
            $('#addSponsorInlineForm')[0].reset();
            $('#addSponsorInlineModal').modal('show');
        });

        $('#addSponsorInlineForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#submitInlineSponsorBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Saving...');
            
            $.ajax({
                url: '{{ route("sponsors.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html(originalHtml);
                    if (res.success) {
                        $('#addSponsorInlineModal').modal('hide');
                        Swal.fire({
                            icon: 'success', title: 'Sponsor Created',
                            text: res.message, timer: 1500, showConfirmButton: false
                        });
                        // reload sponsors and select the new one
                        loadSponsors(res.sponsor.sponsorID);
                        $('#sponsor_class_select').prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save sponsor', 'error');
                }
            });
        });

        $('#sponsor_select').on('change', function() {
            if ($(this).val()) {
                $('#sponsor_class_select').prop('disabled', false);
                if ($('#sponsor_class_select').val()) {
                    $('#sponsor_class_select').trigger('change');
                }
            } else {
                $('#sponsor_class_select').val('').prop('disabled', true);
                $('#sponsor_students_container').hide();
                $('#no_sponsor_class_placeholder').show();
            }
        });

        $('#sponsor_class_select').on('change', function() {
            const classID = $(this).val();
            const sponsorID = $('#sponsor_select').val();
            
            if (!classID || !sponsorID) {
                $('#sponsor_students_container').hide();
                $('#no_sponsor_class_placeholder').show();
                return;
            }

            showOverlay("Loading students...");
            $('#saveSponsorAssignmentBtn').prop('disabled', true);
            
            $.get("{{ url('get_class_students') }}/" + classID, function(res) {
                hideOverlay();
                if (res.success === false || res.error_code === 'no_academic_year') {
                    Swal.fire('Academic year required', res.message || 'Select an academic year in the header.', 'warning');
                    return;
                }
                if (res.success) {
                    classStudentsForSponsor = res.students || [];
                    renderSponsorStudents(classStudentsForSponsor, sponsorID, res.error_code);
                    $('#no_sponsor_class_placeholder').hide();
                    $('#sponsor_students_container').show();
                    $('#saveSponsorAssignmentBtn').prop('disabled', classStudentsForSponsor.length === 0);
                } else {
                    Swal.fire('Error', 'Failed to load students', 'error');
                }
            }).fail(function(xhr) {
                hideOverlay();
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load students', 'error');
            });
        });

        function renderSponsorStudents(students, sponsorID, errorCode) {
            const container = $('#sponsor_students_list');
            container.empty();
            let assignedCount = 0;

            if (students.length === 0) {
                const emptyMsg = errorCode === 'no_active_enrollments'
                    ? 'No Active enrollments for this class in the selected academic year.'
                    : 'No students found.';
                container.html(`<tr><td colspan="4" class="text-center text-muted py-3">${emptyMsg}</td></tr>`);
            } else {
                students.forEach(function(student) {
                    // Pre-check if assigned to THIS sponsor
                    const isAssignedToThisSponsor = (String(student.sponsor_id) === String(sponsorID));
                    const isAssignedToOtherSponsor = student.sponsor_id && !isAssignedToThisSponsor;
                    
                    if (isAssignedToThisSponsor) assignedCount++;
                    
                    const pct = isAssignedToThisSponsor ? student.sponsorship_percentage : 100;

                    const row = `
                        <tr class="${isAssignedToOtherSponsor ? 'table-light' : ''}">
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center align-items-center m-0 p-0">
                                    <input class="form-check-input m-0 shadow-none sponsor-student-check" 
                                        type="checkbox" 
                                        value="${student.studentID}" 
                                        ${isAssignedToThisSponsor ? 'checked' : ''}
                                        style="position: static; transform: scale(1.1);">
                                </div>
                            </td>
                            <td>
                                ${student.first_name} ${student.middle_name || ''} ${student.last_name}
                                ${isAssignedToOtherSponsor ? '<br><small class="badge bg-warning-light text-dark extra-small">Sponsored by another</small>' : ''}
                            </td>
                            <td>${student.admission_number || 'N/A'}</td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap" style="min-width: 80px;">
                                    <input type="number" 
                                        class="form-control sponsor-student-pct" 
                                        min="1" max="100" 
                                        value="${pct}"
                                        ${isAssignedToThisSponsor ? '' : 'disabled'}>
                                    <span class="input-group-text px-1">%</span>
                                </div>
                            </td>
                        </tr>
                    `;
                    container.append(row);
                });
            }

            updateSponsorSelectionCount();
        }

        $(document).on('change', '.sponsor-student-check', function() {
            const tr = $(this).closest('tr');
            const pctInput = tr.find('.sponsor-student-pct');
            // Toggle PCT input
            if ($(this).is(':checked')) {
                pctInput.prop('disabled', false);
            } else {
                pctInput.prop('disabled', true);
            }
            updateSponsorSelectionCount();
            
            // Uncheck SelectAll if all not selected
            const total = $('.sponsor-student-check').length;
            const checked = $('.sponsor-student-check:checked').length;
            $('#selectAllSponsorStudents').prop('checked', total === checked && total > 0);
        });

        $('#selectAllSponsorStudents').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.sponsor-student-check').prop('checked', isChecked).trigger('change');
        });

        function updateSponsorSelectionCount() {
            const count = $('.sponsor-student-check:checked').length;
            $('#selected_sponsor_count').text(`${count} students assigned`);
        }

        $('#sponsor_student_search').on('input', function() {
            const term = $(this).val().toLowerCase();
            $('#sponsor_students_list tr').each(function() {
                if ($(this).text().toLowerCase().indexOf(term) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#assignSponsorForm').on('submit', function(e) {
            e.preventDefault();
            
            const sponsorID = $('#sponsor_select').val();
            const classID = $('#sponsor_class_select').val();
            const assignments = [];
            
            $('.sponsor-student-check:checked').each(function() {
                const tr = $(this).closest('tr');
                const studentID = $(this).val();
                const pct = tr.find('.sponsor-student-pct').val() || 100;
                
                assignments.push({
                    studentID: studentID,
                    percentage: pct
                });
            });

            const btn = $('#saveSponsorAssignmentBtn');
            const origHtml = btn.html();
            btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Saving...');
            showOverlay('Saving Assignments...');
            
            $.ajax({
                url: "{{ route('assign_sponsor_students') }}",
                method: "POST",
                data: {
                    sponsorID: sponsorID,
                    classID: classID,
                    assignments: assignments
                },
                success: function(res) {
                    hideOverlay();
                    btn.prop('disabled', false).html(origHtml);
                    if (res.success) {
                        $('#assignSponsorModal').modal('hide');
                        Swal.fire('Success', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message || 'Error occurred', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred', 'error');
                },
                complete: function() {
                    hideOverlay();
                    btn.prop('disabled', false).html(origHtml);
                }
            });
        });

    }



    // Init
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            initFeesManagement();
        });
    } else {
        window.addEventListener('load', () => { if (typeof jQuery !== 'undefined') initFeesManagement(); });
    }
})();
</script>
