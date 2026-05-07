@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Color scheme for #940000 */
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .exam-card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(148, 0, 0, 0.08) !important;
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .exam-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.3s ease;
    }
    .exam-widget-card {
        border-radius: 12px;
        border: 1px solid #f1d7d7;
        box-shadow: 0 6px 16px rgba(148, 0, 0, 0.08);
        font-family: "Century Gothic", "Segoe UI", Tahoma, sans-serif;
        background-color: #ffffff;
    }
    .exam-widget-title {
        color: #940000;
        font-weight: 600;
        font-size: 1.05rem;
    }
    .exam-widget-meta {
        color: #6c757d;
        font-size: 0.85rem;
    }
    .exam-widget-action {
        border: 1px solid #940000;
        color: #940000;
        background: #ffffff;
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        min-width: 120px;
    }
    .exam-widget-status {
        border: 1px solid #f1d7d7;
        background-color: #fff7f7;
        color: #940000;
        font-weight: 600;
        border-radius: 10px;
    }
    .manage-exam-wrapper,
    .manage-exam-wrapper * {
        font-family: "Century Gothic", "Segoe UI", Tahoma, sans-serif;
    }
    .nav-tabs .nav-link.active {
        background-color: #940000;
        color: white;
        border-color: #940000;
    }
    .nav-tabs .nav-link {
        color: #940000;
    }
    .nav-tabs .nav-link:hover {
        border-color: #940000;
        color: #940000;
    }
    .badge-status-wait-approval {
        background-color: #dc3545;
        color: white;
    }
    .badge-status-scheduled {
        background-color: #6c757d;
        color: white;
    }
    .badge-status-ongoing {
        background-color: #28a745;
        color: white;
    }
    .badge-status-awaiting-results {
        background-color: #ffc107;
        color: #000;
    }
    .badge-status-results-available {
        background-color: #17a2b8;
        color: white;
    }
    .select-item {
        position: relative;
        margin-bottom: 10px;
    }
    .remove-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        background-color: #dc3545;
        color: white !important;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.2s;
    }
    .remove-btn:hover {
        background-color: #c82333;
        color: white !important;
        transform: translateY(-50%) scale(1.1);
    }
    .remove-btn i {
        color: white !important;
        font-size: 14px;
    }
    /* Dropdown menu styling */
    .dropdown-menu {
        z-index: 1050;
        min-width: 180px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(148, 0, 0, 0.2);
    }
    .dropdown-item {
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }
    .dropdown-item:hover {
        background-color: #940000;
        color: white !important;
    }
    .dropdown-item i {
        margin-right: 8px;
    }
    .dropdown-toggle::after {
        display: none;
    }
    /* Modal z-index fix */
    .modal {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1054 !important;
    }
    .modal-dialog {
        z-index: 1056 !important;
    }
    .modal-content {
        z-index: 1057 !important;
        pointer-events: auto !important;
    }
    .modal-body,
    .modal-footer,
    .modal-header {
        pointer-events: auto !important;
    }
    /* Ensure form elements are clickable */
    #approveRejectExamPaperModal {
        z-index: 1065 !important;
    }
    #approveRejectExamPaperModal .modal-dialog {
        z-index: 1066 !important;
        pointer-events: auto !important;
    }
    #approveRejectExamPaperModal .modal-content {
        z-index: 1067 !important;
        pointer-events: auto !important;
    }
    #approveRejectExamPaperModal .form-control,
    #approveRejectExamPaperModal button,
    #approveRejectExamPaperModal textarea,
    #approveRejectExamPaperModal input,
    #approveRejectExamPaperModal label {
        pointer-events: auto !important;
        z-index: 1068 !important;
        position: relative;
    }
    /* Ensure parent modal doesn't block interactions */
    #viewExamPapersModal.show ~ #approveRejectExamPaperModal,
    body.modal-open #viewExamPapersModal:not(.show) {
        pointer-events: none !important;
    }
    body.modal-open #viewExamPapersModal:not(.show) .modal-content {
        pointer-events: none !important;
    }
    /* SweetAlert2 custom styles for textarea */
    .swal2-popup-custom .swal2-html-container textarea,
    .swal2-popup-custom textarea.form-control {
        pointer-events: auto !important;
        z-index: 9999 !important;
        position: relative !important;
    }

    /* Mobile responsiveness improvements */
    @media (max-width: 768px) {
        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

        .container-fluid {
            padding-left: 15px !important;
            padding-right: 15px !important;
            width: 100%;
            overflow-x: hidden;
        }

        .row {
            margin-left: -10px !important;
            margin-right: -10px !important;
        }

        .col-12, .col-md-12, .col-lg-12 {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .card-body {
            padding: 0.75rem;
        }

        .nav-tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-bottom: 2px solid #ddd;
            margin-bottom: 1rem !important;
            width: 100%;
        }

        .nav-tabs .nav-item {
            white-space: nowrap;
        }

        .nav-tabs .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }

        .search-filters .col-md-3, 
        .search-filters .col-md-4, 
        .search-filters .col-md-2 {
            margin-bottom: 15px !important;
        }

        .manage-exam-wrapper .card-body h4 {
            font-size: 1.15rem;
            word-wrap: break-word;
        }

        .btn-primary-custom, .btn-light, .exam-widget-action {
            width: 100% !important;
            margin-top: 5px;
            margin-bottom: 5px;
            min-width: 0 !important;
            justify-content: center;
        }

        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem) !important;
        }
        
        .modal-header {
            padding: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .modal-footer {
            flex-direction: column;
            gap: 10px;
        }
        
        .modal-footer .btn {
            width: 100%;
            margin: 0 !important;
        }
        
        /* Table fixes */
        .table-responsive table {
            font-size: 0.85rem;
        }

        /* Prevent long text overflow */
        * {
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Week navigator mobile fixes */
        #week_filter_container {
            flex-direction: column !important;
            gap: 10px !important;
        }

        #week_filter_container button {
            width: 100% !important;
        }

        #current_week_display {
            width: 100% !important;
            text-align: center;
            font-size: 0.9rem;
            padding: 8px !important;
        }
    }

    
    /* Touch target improvements */
    .btn, .form-control, .nav-link, .dropdown-item {
        min-height: 44px;
        display: flex;
        align-items: center;
    }
    
    .btn, .nav-link, .dropdown-item {
        justify-content: center;
    }

    .transition-transform {
        transition: transform 0.3s ease;
    }
    
    [aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }

</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@php
    $perms = $teacherPermissions ?? collect();
    $isAdmin = ($user_type ?? '') == 'Admin';
    
    // Hierarchy: If you have modify permissions, you inherently have view permissions
    $canViewExams = $isAdmin || $perms->intersect([
        'examination_read_only', 'examination_create', 'examination_update', 'examination_delete', 
        'manage_exam', 'view_exams'
    ])->isNotEmpty();
    
    $canCreateExam = $isAdmin || $perms->intersect(['examination_create', 'create_exam'])->isNotEmpty();
    $canEditExam = $isAdmin || $perms->intersect(['examination_update', 'edit_exam', 'update_exam'])->isNotEmpty();
    $canDeleteExam = $isAdmin || $perms->intersect(['examination_delete', 'delete_exam'])->isNotEmpty();
    
    // For individual exam actions, the hierarchy already covers them through the global variables
@endphp

<div class="container-fluid mt-4 manage-exam-wrapper">
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
                <div class="card-body bg-primary-custom text-white rounded p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> Manage Examinations
                            @if($totalExams ?? 0 > 0)
                            <small class="ms-md-2" style="font-size: 0.85rem; opacity: 0.9; display: block; display: md-inline;">
                                ({{ $totalExams ?? 0 }} {{ $totalExams == 1 ? 'Exam' : 'Exams' }})
                            </small>
                            @endif
                        </h4>
                        @if($canCreateExam)
                        <button class="btn btn-light text-primary-custom fw-bold mt-2 mt-md-0 d-flex align-items-center justify-content-center" type="button" data-toggle="modal" data-target="#createExamModal">
                            <i class="bi bi-plus-circle me-2"></i> Create New Examination
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form id="searchExamForm" method="GET" action="{{ route('manageExamination') }}">
                        <div class="row search-filters">
                            <div class="col-md-4 mb-3">
                                <label for="filter_exam_category" class="form-label">
                                    <i class="bi bi-grid"></i> Filter by Category
                                </label>
                                <select class="form-control" id="filter_exam_category" name="exam_category">
                                    <option value="">All Categories</option>
                                    <option value="school_exams" {{ ($examCategoryFilter ?? '') == 'school_exams' ? 'selected' : '' }}>School Exams</option>
                                    <option value="test" {{ ($examCategoryFilter ?? '') == 'test' ? 'selected' : '' }}>Test</option>
                                    <option value="special_exams" {{ ($examCategoryFilter ?? '') == 'special_exams' ? 'selected' : '' }}>Special Exams</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_term" class="form-label">
                                    <i class="bi bi-calendar-week"></i> Filter by Term
                                </label>
                                <select class="form-control" id="filter_term" name="term">
                                    <option value="">All Terms</option>
                                    <option value="first_term" {{ ($termFilter ?? '') == 'first_term' ? 'selected' : '' }}>First Term</option>
                                    <option value="second_term" {{ ($termFilter ?? '') == 'second_term' ? 'selected' : '' }}>Second Term</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_year" class="form-label">
                                    <i class="bi bi-calendar"></i> Filter by Year
                                </label>
                                <select class="form-control" id="filter_year" name="year">
                                    <option value="">All Years</option>
                                    @foreach($availableYears ?? [] as $year)
                                        <option value="{{ $year }}" {{ ($yearFilter ?? '') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary-custom w-100" title="Search Examinations">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                        @if(!empty($examCategoryFilter) || !empty($yearFilter) || !empty($termFilter))
                        <div class="row">
                            <div class="col-12">
                                <a href="{{ route('manageExamination') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x-circle"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Examinations Tabs -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="examTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="school-exams-tab" data-toggle="tab" href="#school-exams" role="tab">
                                <i class="bi bi-building"></i> School Exams
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="test-tab" data-toggle="tab" href="#test" role="tab">
                                <i class="bi bi-clipboard-check"></i> Test
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="special-exams-tab" data-toggle="tab" href="#special-exams" role="tab">
                                <i class="bi bi-star"></i> Special Exams
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="examTabsContent">
                        <!-- School Exams -->
                        <div class="tab-pane fade show active" id="school-exams" role="tabpanel">
                            <div class="row" id="schoolExamsContainer">
                                @if(isset($examinationsGrouped['school_exams']) && $examinationsGrouped['school_exams']->count() > 0)
                                    @foreach($examinationsGrouped['school_exams'] as $exam)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            @include('Admin.partials.exam_widget', ['exam' => $exam])
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle"></i> No school examinations found.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Test -->
                        <div class="tab-pane fade" id="test" role="tabpanel">
                            <div class="row" id="testExamsContainer">
                                @if(isset($examinationsGrouped['test']) && $examinationsGrouped['test']->count() > 0)
                                    @foreach($examinationsGrouped['test'] as $exam)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            @include('Admin.partials.exam_widget', ['exam' => $exam])
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle"></i> No test examinations found.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Special Exams -->
                        <div class="tab-pane fade" id="special-exams" role="tabpanel">
                            <div class="row" id="specialExamsContainer">
                                @if(isset($examinationsGrouped['special_exams']) && $examinationsGrouped['special_exams']->count() > 0)
                                    @foreach($examinationsGrouped['special_exams'] as $exam)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            @include('Admin.partials.exam_widget', ['exam' => $exam])
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle"></i> No special examinations found.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Create Examination Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1" role="dialog" aria-labelledby="createExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="createExamModalLabel">
                    <i class="bi bi-plus-circle"></i> Create New Examination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createExamForm">
                <div class="modal-body">
                    <div id="examFormErrors" class="alert alert-danger" style="display: none;"></div>

                    <!-- Basic Information -->
                    <div class="form-group">
                        <label for="exam_category">Exam Category <span class="text-danger">*</span></label>
                        <select class="form-control" id="exam_category" name="exam_category" required>
                            <option value="">Select Exam Category</option>
                            <option value="school_exams">School Exams</option>
                            <option value="test">Test</option>
                            <option value="special_exams">Special Exams</option>
                        </select>
                    </div>

                    <!-- Test Type Selection (shown when Test category is selected) -->
                    <div class="form-group" id="test_type_group" style="display: none;">
                        <label for="test_type">Test Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="test_type" name="test_type">
                            <option value="">Select Test Type</option>
                            <option value="weekly_test">Weekly Test</option>
                            <option value="monthly_test">Monthly Test</option>
                            <option value="other_test">Other Test</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select the type of test you want to create.
                        </small>
                    </div>

                    <div class="form-group" id="exam_name_text_group" style="display: none;">
                        <label for="exam_name">Examination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exam_name" name="exam_name" placeholder="Enter examination name">
                    </div>

                    <div class="form-group" id="exam_name_select_group" style="display: none;">
                        <label for="exam_name_type">Examination Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="exam_name_type" name="exam_name_type">
                            <option value="">Select Examination Type</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Terminal">Terminal</option>
                            <option value="Annual Exam">Annual Exam</option>
                        </select>
                    </div>

                    <div class="form-group" id="term_group" style="display: none;">
                        <label for="term">Term (Muhula) <span class="text-danger">*</span></label>
                        <select class="form-control" id="term" name="term">
                            <option value="">Select Term</option>
                            <option value="first_term" {{ in_array(1, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                First Term @if(in_array(1, $closedTerms ?? [])) (Closed) @endif
                            </option>
                            <option value="second_term" {{ in_array(2, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                Second Term @if(in_array(2, $closedTerms ?? [])) (Closed) @endif
                            </option>
                            <option value="all_terms">All Terms (For Weekly/Monthly Tests)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select which term this examination is for. Closed terms are disabled.
                        </small>
                    </div>

                    <div class="form-group border-top pt-3 mt-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="allow_no_format" name="allow_no_format" value="1">
                            <label class="custom-control-label" for="allow_no_format">
                                <strong>Allow Create Exam Without Question Format</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mb-3 ml-4">
                            <i class="bi bi-info-circle"></i> Kama ikiwashwa, mwalimu ataingiza marks moja kwa moja bila kuhitaji kujaza question format kwanza.
                        </small>

                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="allow_no_paper" name="allow_no_paper" value="1">
                            <label class="custom-control-label" for="allow_no_paper">
                                <strong>Create Exam Without Upload Paper</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mb-3 ml-4">
                            <i class="bi bi-info-circle"></i> Kama ikiwashwa, mwalimu ataweza kujaza matokeo hata kama hajawahi kupandisha (upload) karatasi ya mtihani.
                        </small>
                    </div>
                    
                    <div class="row" id="date_fields_group">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                                <small class="form-text text-muted" id="start_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="start_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                                <small class="form-text text-muted" id="end_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="end_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="year" name="year" value="{{ $currentYear }}" min="2000" max="2100" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="details">Details (Optional)</label>
                        <textarea class="form-control" id="details" name="details" rows="3"></textarea>
                    </div>

                    <!-- Exam Halls -->
                    <div class="form-group" id="exam_halls_group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0"><strong>Exam Halls</strong> <small class="text-muted">(Define halls, class, capacity & gender)</small></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add_exam_hall_btn">
                                <i class="bi bi-plus-circle"></i> Add Hall
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" id="exam_halls_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hall Name</th>
                                        <th>Main Class</th>
                                        <th>Subclass (Optional)</th> <!-- Added Subclass -->
                                        <th>Class Students</th>
                                        <th>Capacity</th>
                                        <th>Gender</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="exam_halls_body">
                                    <tr class="text-center text-muted" id="exam_halls_empty_row">
                                        <td colspan="7">No hall added yet.</td> <!-- Updated colspan -->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block">Total capacity per class must not be less than total students (and per gender if gender-specific).</small>
                        <div id="exam_halls_errors" class="alert alert-danger mt-2" style="display:none;"></div>
                    </div>

                    <!-- Except Classes (For School Exams) -->
                    <div class="form-group" id="except_classes_group" style="display: none;">
                        <label>Except Classes (Madarasa yasiyofanya mtihani) <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="use_except" name="use_except" value="1">
                            <label class="form-check-label" for="use_except">
                                Exclude certain classes from this examination
                            </label>
                        </div>
                        <div id="except_classes_selection" style="display: none;">
                            <small class="form-text text-muted mb-2 d-block">
                                <i class="bi bi-info-circle"></i> Select main classes that will NOT participate in this examination (e.g., Form Four for Annual Exam as they do NECTA instead).
                            </small>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($classes ?? [] as $class)
                                <div class="form-check">
                                    <input class="form-check-input except-class-checkbox" type="checkbox" name="except_class_ids[]" value="{{ $class->classID }}" id="except_class_{{ $class->classID }}">
                                    <label class="form-check-label" for="except_class_{{ $class->classID }}">
                                        {{ $class->class_name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Include Classes (For Special Exams) -->
                    <div class="form-group" id="include_classes_group" style="display: none;">
                        <label>Include Classes (Madarasa yatakayofanya mtihani) <span class="text-danger">*</span></label>
                        <small class="form-text text-muted mb-2 d-block">
                            <i class="bi bi-info-circle"></i> Select main classes that will participate in this special examination.
                        </small>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($classes ?? [] as $class)
                            <div class="form-check">
                                <input class="form-check-input include-class-checkbox" type="checkbox" name="include_class_ids[]" value="{{ $class->classID }}" id="include_class_{{ $class->classID }}">
                                <label class="form-check-label" for="include_class_{{ $class->classID }}">
                                    {{ $class->class_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Student Shifting Status (Only for School Exams) -->
                    <div class="form-group" id="student_shifting_status_group" style="display: none;">
                        <label for="student_shifting_status">Student Shifting Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="student_shifting_status" name="student_shifting_status">
                            <option value="none">None - No shifting allowed</option>
                            <option value="internal">Internal - Allow shifting within same class level (e.g., Form Four A to Form Four B)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> This status determines the type of student shifting allowed for this examination. Only available for School Exams.
                        </small>
                    </div>

                    <!-- Result Approval Chain -->
                    <div class="form-group" id="result_approval_group">
                        <label>
                            <input type="checkbox" id="use_result_approval" name="use_result_approval" value="1">
                            <strong>Enable Result Approval Chain</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, results must be approved by selected roles in order. First approver must approve before second can see, and so on.
                        </small>
                        <div id="result_approval_fields" style="display: none;">
                            <div class="form-group">
                                <label for="number_of_approvals">Number of Approvals <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="number_of_approvals" name="number_of_approvals" min="1" max="{{ (count($roles ?? []) + 2) }}" value="1">
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Maximum: {{ count($roles ?? []) }} regular roles + 2 special roles (Class Teacher, Coordinator) = {{ (count($roles ?? []) + 2) }} total. Each role can only be selected once.
                                </small>
                            </div>
                            <div id="approval_role_selections">
                                <!-- Dynamic approval role selects will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Exam Paper Approval Chain -->
                    <div class="form-group" id="paper_approval_group">
                        <label>
                            <input type="checkbox" id="use_paper_approval" name="use_paper_approval" value="1">
                            <strong>Enable Exam Paper Approval Chain</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> Kama ikiwashwa, kila mwalimu anapopandisha (upload) karatasi ya mtihani, itabidi ipite kwenye hatua za approval zilizopangwa hapon chini.
                        </small>
                        <div id="paper_approval_fields" style="display: none;">
                            <div class="form-group">
                                <label for="number_of_paper_approvals">Number of Approvals <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="number_of_paper_approvals" name="number_of_paper_approvals" min="1" max="{{ (count($roles ?? []) + 2) }}" value="1">
                            </div>
                            <div id="paper_approval_role_selections">
                                <!-- Dynamic paper approval role selects will be added here -->
                            </div>
                        </div>

                        <div class="mt-2" id="no_approval_required_group">
                            <label>
                                <input type="checkbox" id="no_approval_required" name="no_approval_required" value="1">
                                <strong>Exam upload without approval</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                <i class="bi bi-info-circle"></i> Ikishachaguliwa hii, mwalimu aki-upload mtihani unakuwa approved moja kwa moja.
                            </small>
                        </div>
                    </div>

                    <!-- Exam Attendance Tracking -->
                    <div class="form-group" id="exam_attendance_group">
                        <label>
                            <input type="checkbox" id="enable_exam_attendance" name="enable_exam_attendance" value="1">
                            <strong>Enable Exam Attendance Tracking</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, all students whose classes are participating in this examination will be automatically added to the exam attendance list for tracking purposes.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Create Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Examination Modal -->
<div class="modal fade" id="rejectExamModal" tabindex="-1" role="dialog" aria-labelledby="rejectExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectExamModalLabel">
                    <i class="bi bi-x-circle"></i> Reject Examination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectExamForm">
                @csrf
                <input type="hidden" name="exam_id" id="reject_exam_id">
                <input type="hidden" name="approval_status" value="Rejected">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Examination Name</label>
                        <input type="text" class="form-control" id="reject_exam_name" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" placeholder="Please provide a reason for rejecting this examination..." required maxlength="500"></textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Reject Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Exam Details Modal -->
<div class="modal fade" id="viewExamDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewExamDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" role="document" style="max-width: 95%; width: 95%; margin: 1.75rem auto;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #e9ecef; padding: 20px 25px;">
                <h5 class="modal-title" id="viewExamDetailsModalLabel" style="font-weight: 600; font-size: 1.25rem;">
                    <i class="bi bi-clipboard-check me-2"></i>Examination Details
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="examDetailsContent" style="padding: 25px; max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Loading examination details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Examination Modal -->
<div class="modal fade" id="editExamModal" tabindex="-1" role="dialog" aria-labelledby="editExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editExamModalLabel">
                    <i class="bi bi-pencil"></i> Edit Examination
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editExamForm">
                <input type="hidden" id="edit_examID" name="examID">
                <div class="modal-body">
                    <div id="editExamFormErrors" class="alert alert-danger" style="display: none;"></div>

                    <!-- Exam Category -->
                    <div class="form-group">
                        <label for="edit_exam_category">Exam Category <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_exam_category" name="exam_category" required>
                            <option value="">Select Exam Category</option>
                            <option value="school_exams">School Exams</option>
                            <option value="test">Test</option>
                            <option value="special_exams">Special Exams</option>
                        </select>
                    </div>

                    <!-- Test Type Selection -->
                    <div class="form-group" id="edit_test_type_group" style="display: none;">
                        <label for="edit_test_type">Test Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_test_type" name="test_type">
                            <option value="">Select Test Type</option>
                            <option value="weekly_test">Weekly Test</option>
                            <option value="monthly_test">Monthly Test</option>
                            <option value="other_test">Other Test</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select the type of test you want to create.
                        </small>
                    </div>

                    <!-- Exam Name -->
                    <div class="form-group" id="edit_exam_name_text_group" style="display: none;">
                        <label for="edit_exam_name">Examination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_exam_name" name="exam_name" placeholder="Enter examination name">
                    </div>

                    <div class="form-group" id="edit_exam_name_select_group" style="display: none;">
                        <label for="edit_exam_name_type">Examination Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_exam_name_type" name="exam_name_type">
                            <option value="">Select Examination Type</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Terminal">Terminal</option>
                            <option value="Annual Exam">Annual Exam</option>
                        </select>
                    </div>

                    <div class="form-group" id="edit_term_group" style="display: none;">
                        <label for="edit_term">Term (Muhula) <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_term" name="term">
                            <option value="">Select Term</option>
                            <option value="first_term" {{ in_array(1, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                First Term @if(in_array(1, $closedTerms ?? [])) (Closed) @endif
                            </option>
                            <option value="second_term" {{ in_array(2, $closedTerms ?? []) ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '' }}>
                                Second Term @if(in_array(2, $closedTerms ?? [])) (Closed) @endif
                            </option>
                            <option value="all_terms">All Terms (For Weekly/Monthly Tests)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Select which term this examination is for. Closed terms are disabled.
                        </small>
                    </div>

                    <div class="form-group border-top pt-3 mt-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="edit_allow_no_format" name="allow_no_format" value="1">
                            <label class="custom-control-label" for="edit_allow_no_format">
                                <strong>Allow Create Exam Without Question Format</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mb-3 ml-4">
                            <i class="bi bi-info-circle"></i> Kama ikiwashwa, mwalimu ataingiza marks moja kwa moja bila kuhitaji kujaza question format kwanza.
                        </small>

                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="edit_allow_no_paper" name="allow_no_paper" value="1">
                            <label class="custom-control-label" for="edit_allow_no_paper">
                                <strong>Create Exam Without Upload Paper</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mb-3 ml-4">
                            <i class="bi bi-info-circle"></i> Kama ikiwashwa, mwalimu ataweza kujaza matokeo hata kama hajawahi kupandisha (upload) karatasi ya mtihani.
                        </small>
                    </div>
                    
                    <div class="row" id="edit_date_fields_group">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                <small class="form-text text-muted" id="edit_start_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="edit_start_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                                <small class="form-text text-muted" id="edit_end_date_help" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="edit_end_date_help_text"></span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_year">Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_year" name="year" min="2000" max="2100" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_details">Details (Optional)</label>
                        <textarea class="form-control" id="edit_details" name="details" rows="3"></textarea>
                    </div>

                    <!-- Edit Exam Halls -->
                    <div class="form-group" id="edit_exam_halls_group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0"><strong>Exam Halls</strong> <small class="text-muted">(Define halls, class, capacity & gender)</small></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_exam_hall_btn">
                                <i class="bi bi-plus-circle"></i> Add Hall
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" id="edit_exam_halls_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hall Name</th>
                                        <th>Main Class</th>
                                        <th>Subclass (Optional)</th>
                                        <th>Class Students</th>
                                        <th>Capacity</th>
                                        <th>Gender</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="edit_exam_halls_body">
                                    <tr class="text-center text-muted" id="edit_exam_halls_empty_row">
                                        <td colspan="7">No hall added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block">Total capacity per class must not be less than total students (and per gender if gender-specific).</small>
                        <div id="edit_exam_halls_errors" class="alert alert-danger mt-2" style="display:none;"></div>
                    </div>

                    <!-- Except Classes (For School Exams/Test) -->
                    <div class="form-group" id="edit_except_classes_group" style="display: none;">
                        <label>Except Classes (Madarasa yasiyofanya mtihani) <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="edit_use_except" name="use_except" value="1">
                            <label class="form-check-label" for="edit_use_except">
                                Exclude certain classes from this examination
                            </label>
                        </div>
                        <div id="edit_except_classes_selection" style="display: none;">
                            <small class="form-text text-muted mb-2 d-block">
                                <i class="bi bi-info-circle"></i> Select main classes that will NOT participate in this examination.
                            </small>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($classes ?? [] as $class)
                                <div class="form-check">
                                    <input class="form-check-input edit-except-class-checkbox" type="checkbox" name="except_class_ids[]" value="{{ $class->classID }}" id="edit_except_class_{{ $class->classID }}">
                                    <label class="form-check-label" for="edit_except_class_{{ $class->classID }}">
                                        {{ $class->class_name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Include Classes (For Special Exams) -->
                    <div class="form-group" id="edit_include_classes_group" style="display: none;">
                        <label>Include Classes (Madarasa yatakayofanya mtihani) <span class="text-danger">*</span></label>
                        <small class="form-text text-muted mb-2 d-block">
                            <i class="bi bi-info-circle"></i> Select main classes that will participate in this special examination.
                        </small>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($classes ?? [] as $class)
                            <div class="form-check">
                                <input class="form-check-input edit-include-class-checkbox" type="checkbox" name="include_class_ids[]" value="{{ $class->classID }}" id="edit_include_class_{{ $class->classID }}">
                                <label class="form-check-label" for="edit_include_class_{{ $class->classID }}">
                                    {{ $class->class_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Student Shifting Status -->
                    <div class="form-group" id="edit_student_shifting_status_group" style="display: none;">
                        <label for="edit_student_shifting_status">Student Shifting Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_student_shifting_status" name="student_shifting_status">
                            <option value="none">None - No shifting allowed</option>
                            <option value="internal">Internal - Allow shifting within same class level (e.g., Form Four A to Form Four B)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Only available for School Exams and Tests.
                        </small>
                    </div>

                    <!-- Result Approval Chain -->
                    <div class="form-group" id="edit_result_approval_group">
                        <label>
                            <input type="checkbox" id="edit_use_result_approval" name="use_result_approval" value="1">
                            <strong>Enable Result Approval Chain</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, results must be approved by selected roles in order.
                        </small>
                        <div id="edit_result_approval_fields" style="display: none;">
                            <div class="form-group">
                                <label for="edit_number_of_approvals">Number of Approvals <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_number_of_approvals" name="number_of_approvals" min="1" value="1">
                            </div>
                            <div id="edit_approval_role_selections"></div>
                        </div>
                    </div>

                    <!-- Exam Paper Approval Chain -->
                    <div class="form-group" id="edit_paper_approval_group">
                        <label>
                            <input type="checkbox" id="edit_use_paper_approval" name="use_paper_approval" value="1">
                            <strong>Enable Exam Paper Approval Chain</strong>
                        </label>
                        <div id="edit_paper_approval_fields" style="display: none;">
                            <div class="form-group">
                                <label for="edit_number_of_paper_approvals">Number of Approvals <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_number_of_paper_approvals" name="number_of_paper_approvals" min="1" max="{{ (count($roles ?? []) + 2) }}" value="1">
                            </div>
                            <div id="edit_paper_approval_role_selections"></div>
                        </div>
                    </div>

                    <!-- Exam Attendance Tracking -->
                    <div class="form-group" id="edit_exam_attendance_group">
                        <label>
                            <input type="checkbox" id="edit_enable_exam_attendance" name="enable_exam_attendance" value="1">
                            <strong>Enable Exam Attendance Tracking</strong>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                            <i class="bi bi-info-circle"></i> If enabled, all participating students will be added to the attendance list.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Update Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
jQuery(document).ready(function($) {
    // Initialize tooltips
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip === 'function') {
        jQuery('[data-toggle="tooltip"], .btn[title], .exam-widget-action[title]').tooltip();
    }

    // Re-initialize tooltips after dynamic content is loaded
    $(document).on('shown.bs.dropdown', function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip === 'function') {
            jQuery('[data-toggle="tooltip"], .btn[title], .exam-widget-action[title]').tooltip();
        }
    });

    // Initialize Bootstrap dropdowns manually to ensure they work
    // Only handle dropdowns in the main content area, not sidebar navigation
    $(document).on('click', '[data-toggle="dropdown"]', function(e) {
        // Skip if this is a sidebar navigation dropdown (they use collapse, not dropdown)
        if ($(this).closest('#left-panel, .sidebar-links-container, .dropdown-nav-item').length > 0) {
            return; // Let sidebar dropdowns be handled by Admin_nav.blade.php
        }
        
        e.preventDefault();
        e.stopPropagation();

        const $dropdown = $(this).closest('.dropdown');
        const $menu = $dropdown.find('.dropdown-menu');
        const isOpen = $menu.hasClass('show');

        // Close all other dropdowns (only in content area, not sidebar)
        $('.dropdown-menu').not($menu).not('#left-panel .dropdown-menu, .sidebar-links-container .dropdown-menu').removeClass('show');
        $('[data-toggle="dropdown"]').not(this).not('#left-panel [data-toggle="dropdown"], .sidebar-links-container [data-toggle="dropdown"]').attr('aria-expanded', 'false');

        // Toggle current dropdown
        if (!isOpen) {
            $menu.addClass('show');
            $(this).attr('aria-expanded', 'true');
        } else {
            $menu.removeClass('show');
            $(this).attr('aria-expanded', 'false');
        }
    });

    // Close dropdown when clicking on dropdown items
    $(document).on('click', '.dropdown-item', function(e) {
        const $dropdown = $(this).closest('.dropdown');
        const $menu = $dropdown.find('.dropdown-menu');
        const $toggle = $dropdown.find('[data-toggle="dropdown"]');
        
        // Close dropdown after a short delay to allow onclick to execute
        setTimeout(function() {
            $menu.removeClass('show');
            $toggle.attr('aria-expanded', 'false');
        }, 100);
    });

    // Close dropdown when clicking outside (only for content area dropdowns, not sidebar)
    $(document).on('click', function(e) {
        // Don't close sidebar dropdowns
        if ($(e.target).closest('#left-panel, .sidebar-links-container, .dropdown-nav-item').length > 0) {
            return;
        }
        
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').not('#left-panel .dropdown-menu, .sidebar-links-container .dropdown-menu').removeClass('show');
            $('[data-toggle="dropdown"]').not('#left-panel [data-toggle="dropdown"], .sidebar-links-container [data-toggle="dropdown"]').attr('aria-expanded', 'false');
        }
    });

    // Prevent dropdown from closing when clicking inside menu items (only for content area dropdowns)
    $('.dropdown-menu').not('#left-panel .dropdown-menu, .sidebar-links-container .dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    let subclassCount = 0;
    let subjectCount = 0;
    let hallRowId = 0;
    const classStudentCache = {};

    function resetHalls(scope = 'create') {
        const tbody = scope === 'create' ? $('#exam_halls_body') : $('#edit_exam_halls_body');
        const emptyRow = scope === 'create' ? $('#exam_halls_empty_row') : $('#edit_exam_halls_empty_row');
        const errorBox = scope === 'create' ? $('#exam_halls_errors') : $('#edit_exam_halls_errors');
        tbody.find('tr').not(emptyRow).remove();
        emptyRow.show();
        errorBox.hide().html('');
    }

    function fetchSubclasses(classID, selectElement, selectedSubclassID = null) {
        if (!classID) {
            selectElement.html('<option value="">All Subclasses</option>');
            return;
        }
        
        // Assuming we have a route get_subclasses/{classID}
        // If not, we might need a different route. Let's try /get_subclasses_by_class_id which is common, or just /get_subclasses/
        // I'll search for exact route pattern first to be safe, but assuming /get_subclasses/ is standard.
        // Based on previous search, 'get_subclasses' exists.
        
        $.get('/get_subclasses/' + classID, function(data) {
            let html = '<option value="">All Subclasses</option>';
            if (data && data.length > 0) {
                // Determine if data structure is array of objects or key-value
                // Usually it returns [{subclassID: 1, subclass_name: 'A'}, ...]
                data.forEach(sub => {
                   html += `<option value="${sub.subclassID}">${sub.subclass_name}</option>`; 
                });
            }
            selectElement.html(html);
            if (selectedSubclassID) {
                selectElement.val(selectedSubclassID);
            }
        }).fail(function() {
            console.error('Failed to fetch subclasses');
            selectElement.html('<option value="">All Subclasses</option>');
        });
    }

    function renderHallRow(scope = 'create', data = {}) {
        console.log('Rendering hall row for scope:', scope, 'with data:', data); // Debugging
        hallRowId++;
        const tbody = scope === 'create' ? $('#exam_halls_body') : $('#edit_exam_halls_body');
        const emptyRow = scope === 'create' ? $('#exam_halls_empty_row') : $('#edit_exam_halls_empty_row');
        emptyRow.hide();
        const prefix = scope === 'create' ? '' : 'edit_';
        const rowId = `${prefix}hall_row_${hallRowId}`;
        const row = $(`
            <tr id="${rowId}">
                <td><input type="text" class="form-control hall-name-input" name="${prefix}hall_name[]" value="${data.hall_name || ''}" placeholder="e.g., Hall A" required></td>
                <td>
                    <select class="form-control hall-class-select" name="${prefix}hall_class_id[]" required>
                        <option value="">Select Class</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-control hall-subclass-select" name="${prefix}hall_subclass_id[]">
                        <option value="">All Subclasses</option>
                    </select>
                </td>
                <td class="text-center hall-class-count text-muted" style="min-width: 130px;">--</td>
                <td><input type="number" class="form-control hall-capacity-input" name="${prefix}hall_capacity[]" min="1" value="${data.capacity || ''}" placeholder="Capacity" required></td>
                <td>
                    <select class="form-control hall-gender-select" name="${prefix}hall_gender[]" required>
                        <option value="both" ${data.gender_allowed === 'both' ? 'selected' : ''}>Both</option>
                        <option value="male" ${data.gender_allowed === 'male' ? 'selected' : ''}>Male</option>
                        <option value="female" ${data.gender_allowed === 'female' ? 'selected' : ''}>Female</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-hall-btn"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `);

        tbody.append(row);

        if (data.classID) {
            row.find('.hall-class-select').val(data.classID);
            // Fetch subclasses and set value
            fetchSubclasses(data.classID, row.find('.hall-subclass-select'), data.subclassID);
            
            // Trigger update counts (existing logic will handle this via change event if we trigger it, OR we call explicitly)
            // Existing logic uses 'change' on .hall-class-select. So triggering change is easiest, BUT it might clear subclass select if we are not careful.
            // My fetchSubclasses is async.
            // Better to just call fetchClassCounts directly.
             fetchClassCounts(data.classID, counts => {
                if (counts) {
                   row.find('.hall-class-count').html(`<div>Total: ${counts.total}<br><small>♂ ${counts.male} / ♀ ${counts.female}</small></div>`);
                }
            });
        }
        
        if (data.capacity) {
            row.find('.hall-capacity-input').val(data.capacity);
        }
        if (data.gender_allowed) {
            row.find('.hall-gender-select').val(data.gender_allowed);
        }
    }

    function fetchClassCounts(classID, cb) {
        if (!classID) {
            cb(null);
            return;
        }
        if (classStudentCache[classID]) {
            cb(classStudentCache[classID]);
            return;
        }
        $.get('/get_class_student_counts/' + classID, function(res) {
            if (res && res.success) {
                classStudentCache[classID] = res.counts;
                cb(res.counts);
            } else {
                cb(null);
            }
        }).fail(function() {
            cb(null);
        });
    }

    function validateHallCapacities(scope = 'create') {
        const errorBox = scope === 'create' ? $('#exam_halls_errors') : $('#edit_exam_halls_errors');
        errorBox.hide().html('');

        const rows = scope === 'create' ? $('#exam_halls_body tr') : $('#edit_exam_halls_body tr');
        const totalsByClass = {};

        let hasRow = false;
        rows.each(function() {
            const classId = $(this).find('.hall-class-select').val();
            const capacity = parseInt($(this).find('.hall-capacity-input').val(), 10) || 0;
            const gender = $(this).find('.hall-gender-select').val() || 'both';
            if (!classId || capacity <= 0) return;
            hasRow = true;
            if (!totalsByClass[classId]) {
                totalsByClass[classId] = { both: 0, male: 0, female: 0 };
            }
            totalsByClass[classId].both += capacity;
            if (gender === 'male') totalsByClass[classId].male += capacity;
            else if (gender === 'female') totalsByClass[classId].female += capacity;
            else {
                totalsByClass[classId].male += capacity;
                totalsByClass[classId].female += capacity;
            }
        });

        const checks = [];
        const promises = Object.keys(totalsByClass).map(classId => new Promise(resolve => {
            fetchClassCounts(classId, counts => {
                if (!counts) {
                    checks.push(`Failed to fetch counts for selected class.`);
                    return resolve();
                }
                const needTotal = totalsByClass[classId].both;
                if (needTotal < counts.total) {
                    checks.push(`Class ${classId}: capacity (${needTotal}) is less than total students (${counts.total}).`);
                }
                if (totalsByClass[classId].male < counts.male) {
                    checks.push(`Class ${classId}: male capacity (${totalsByClass[classId].male}) is less than male students (${counts.male}).`);
                }
                if (totalsByClass[classId].female < counts.female) {
                    checks.push(`Class ${classId}: female capacity (${totalsByClass[classId].female}) is less than female students (${counts.female}).`);
                }
                resolve();
            });
        }));

        return Promise.all(promises).then(() => {
            if (checks.length > 0) {
                errorBox.html('<ul class="mb-0"><li>' + checks.join('</li><li>') + '</li></ul>').show();
                return false;
            }
            errorBox.hide().html('');
            return hasRow ? true : true; // allow zero halls for now
        });
    }

    // Add hall buttons
    // Add hall buttons - using delegated events for robustness
    $(document).on('click', '#add_exam_hall_btn', function() {
        console.log('Add hall button clicked'); // Debugging
        renderHallRow('create');
    });
    $(document).on('click', '#edit_add_exam_hall_btn', function() {
        renderHallRow('edit');
    });

    // Remove hall row
    $(document).on('click', '.remove-hall-btn', function() {
        const row = $(this).closest('tr');
        const tbody = row.closest('tbody');
        row.remove();
        const emptyRow = tbody.find('tr[id$="_empty_row"]');
        if (tbody.find('tr').length === 1) {
            emptyRow.show();
        }
        validateHallCapacities(tbody.attr('id') === 'exam_halls_body' ? 'create' : 'edit');
    });

    // On class change, fetch counts and update cell
    $(document).on('change', '.hall-class-select', function() {
        const row = $(this).closest('tr');
        const classId = $(this).val();
        
        // Fetch Subclasses
        fetchSubclasses(classId, row.find('.hall-subclass-select'));
        
        const cell = row.find('.hall-class-count');
        cell.text('...');
        fetchClassCounts(classId, counts => {
            if (counts) {
                cell.html(`<div>Total: ${counts.total}<br><small>♂ ${counts.male} / ♀ ${counts.female}</small></div>`);
            } else {
                cell.text('--');
            }
            validateHallCapacities(row.closest('tbody').attr('id') === 'exam_halls_body' ? 'create' : 'edit');
        });
    });

    // On capacity or gender change, revalidate
    $(document).on('input change', '.hall-capacity-input, .hall-gender-select', function() {
        validateHallCapacities($(this).closest('tbody').attr('id') === 'exam_halls_body' ? 'create' : 'edit');
    });

    // Handle exam category change
    // Handle exam category change - using delegated event for robustness
    $(document).on('change', '#exam_category', function() {
        const examCategory = $(this).val();
        console.log('Exam category changed to:', examCategory); // Debugging
        
        // Reset all fields
        $('#exam_name').val('');
        $('#exam_name_type').val('');
        $('#test_type').val('');
        $('#term').val('');
        $('#use_except').prop('checked', false);
        $('.except-class-checkbox').prop('checked', false);
        $('.include-class-checkbox').prop('checked', false);
        $('#except_classes_selection').hide();
        $('#start_date_help').hide();
        $('#end_date_help').hide();
        $('#test_type_group').hide();
        $('#date_fields_group').show();
        $('#start_date').prop('required', true);
        $('#end_date').prop('required', true);
        
        if (examCategory === 'school_exams') {
            // Show select dropdown for exam name, hide text input
            $('#exam_name_text_group').hide();
            $('#exam_name').prop('required', false);
            $('#exam_name_select_group').show();
            $('#exam_name_type').prop('required', true);
            $('#exam_name_type').val(''); // Reset selection
            
            // Show term selection
            $('#term_group').show();
            $('#term').prop('required', true);
            
            // Show except classes option, hide include classes
            $('#except_classes_group').show();
            $('#include_classes_group').hide();
            $('.include-class-checkbox').prop('required', false);
            
            // Show student shifting status
            $('#student_shifting_status_group').show();
            $('#student_shifting_status').prop('required', true);
        } else if (examCategory === 'test') {
            // Show test type selection dropdown
            console.log('Showing test type group'); // Debugging
            $('#test_type_group').stop(true, true).show();
            $('#test_type').prop('required', true).val('all_terms'); // Set default or reset
            
            // Hide exam name fields initially (will be shown based on test type)
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').prop('required', false);
            $('#exam_name_type').prop('required', false);
            
            // Show term selection
            $('#term_group').show();
            $('#term').prop('required', true).val('all_terms');
            
            // Show except classes option, hide include classes
            $('#except_classes_group').show();
            $('#include_classes_group').hide();
            $('.include-class-checkbox').prop('required', false);
            
            // Show student shifting status
            $('#student_shifting_status_group').show();
            $('#student_shifting_status').prop('required', true);
        } else if (examCategory === 'special_exams') {
            // Show text input for exam name, hide select dropdown
            $('#exam_name_select_group').hide();
            $('#exam_name_type').prop('required', false);
            $('#exam_name_text_group').show();
            $('#exam_name').prop('required', true);
            $('#exam_name').attr('placeholder', 'Enter examination name');
            
            // Show term selection for special exams
            $('#term_group').show();
            $('#term').prop('required', true);
            
            // Show include classes option, hide except classes
            $('#include_classes_group').show();
            $('#except_classes_group').hide();
            $('.except-class-checkbox').prop('required', false);
            
            // Hide student shifting status
            $('#student_shifting_status_group').hide();
            $('#student_shifting_status').prop('required', false);
        } else {
            // Hide all
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#test_type_group').hide();
            $('#term_group').hide();
            $('#except_classes_group').hide();
            $('#include_classes_group').hide();
            $('#student_shifting_status_group').hide();
            $('#exam_name').prop('required', false);
            $('#exam_name_type').prop('required', false);
            $('#term').prop('required', false);
        }
    });

    // Handle test type change (when Test category is selected)
    $('#test_type').on('change', function() {
        const testType = $(this).val();
        
        // Reset exam name fields
        $('#exam_name').val('');
        $('#exam_name_type').val('');
        
        // Handle Term Selection Visibility
        if (testType === 'weekly_test' || testType === 'monthly_test') {
             $('#term option[value="all_terms"]').hide();
             if ($('#term').val() === 'all_terms') {
                 $('#term').val('');
             }
        } else {
             $('#term option[value="all_terms"]').show();
        }
        
        if (testType === 'weekly_test') {
            // For weekly test, set exam name automatically
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').val('Weekly Test');
            $('#exam_name').prop('required', false);
            
            // Hide date fields (auto-handled)
            $('#date_fields_group').hide();
            $('#start_date').prop('required', false);
            $('#end_date').prop('required', false);
        } else if (testType === 'monthly_test') {
            // For monthly test, set exam name automatically
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').val('Monthly Test');
            $('#exam_name').prop('required', false);
            
            // Hide date fields (auto-handled)
            $('#date_fields_group').hide();
            $('#start_date').prop('required', false);
            $('#end_date').prop('required', false);
        } else if (testType === 'other_test') {
            // For other test, show text input for custom exam name and show date fields
            $('#exam_name_text_group').show();
            $('#exam_name_select_group').hide();
            $('#exam_name').prop('required', true);
            $('#exam_name').val(''); // Clear value for user input
            $('#exam_name').attr('placeholder', 'Enter test name (e.g., Quiz, Assessment, etc.)');
            
            // Show date fields
            $('#date_fields_group').show();
            $('#start_date').prop('required', true);
            $('#end_date').prop('required', true);
            $('#start_date_help').hide();
            $('#end_date_help').hide();
        } else {
            // Reset to default
            $('#exam_name_text_group').hide();
            $('#exam_name_select_group').hide();
            $('#exam_name').prop('required', false);
            $('#date_fields_group').show();
            $('#start_date').prop('required', true);
            $('#end_date').prop('required', true);

            // If category is test, set all terms ONLY if allowed (but here we are in 'else' meaning no test type selected or unknown)
            // We should respect the visibility logic above.
            // If we are here, testType is empty. So allow all terms.
            if ($('#exam_category').val() === 'test') {
               // Logic was: $('#term').val('all_terms');
               // But now we hide it for weekly/monthly.
               // If testType is empty, leave it as is.
            }
        }
    });

    // Handle use_except checkbox
    $('#use_except').on('change', function() {
        if ($(this).is(':checked')) {
            $('#except_classes_selection').show();
        } else {
            $('#except_classes_selection').hide();
            $('.except-class-checkbox').prop('checked', false);
        }
    });

    // Store roles data for JavaScript
    const roles = @json($roles ?? []);
    // Hard-coded special roles: class_teacher and coordinator
    const specialRoles = [
        { id: 'class_teacher', name: 'Class Teacher', role_name: 'Class Teacher' },
        { id: 'coordinator', name: 'Coordinator', role_name: 'Coordinator' },
        { id: 'head_of_department', name: 'Head Of Department', role_name: 'Head Of Department' }
    ];
    // Max approvals = regular roles + special roles
    const maxApprovals = roles.length + specialRoles.length;

    // Handle result approval checkbox
    $('#use_result_approval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#result_approval_fields').show();
            $('#number_of_approvals').prop('required', true);
            generateApprovalRoleSelects();
        } else {
            $('#result_approval_fields').hide();
            $('#number_of_approvals').prop('required', false);
            $('#approval_role_selections').empty();
        }
    });

    // Handle number of approvals change
    $('#number_of_approvals').on('change', function() {
        const numApprovals = parseInt($(this).val()) || 0;
        if (numApprovals > maxApprovals) {
            Swal.fire({ icon: 'warning', title: 'Invalid Number', text: 'Number of approvals cannot exceed ' + maxApprovals });
            $(this).val(maxApprovals);
            generateApprovalRoleSelects();
        } else if (numApprovals > 0) {
            generateApprovalRoleSelects();
        }
    });

    // Handle paper approval checkbox
    $('#use_paper_approval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#paper_approval_fields').show();
            $('#number_of_paper_approvals').prop('required', true);
            generatePaperApprovalRoleSelects();
            // Uncheck no_approval_required if paper approval chain is enabled
            $('#no_approval_required').prop('checked', false);
        } else {
            $('#paper_approval_fields').hide();
            $('#number_of_paper_approvals').prop('required', false);
            $('#paper_approval_role_selections').empty();
        }
    });

    $('#no_approval_required').on('change', function() {
        if ($(this).is(':checked')) {
            // Uncheck and hide paper approval chain if no approval required is checked
            $('#use_paper_approval').prop('checked', false).trigger('change');
        }
    });

    // Handle number of paper approvals change
    $('#number_of_paper_approvals').on('change', function() {
        const numApprovals = parseInt($(this).val()) || 0;
        const maxPaperApprovals = roles.length + specialRoles.length;
        if (numApprovals > maxPaperApprovals) {
            Swal.fire({ icon: 'warning', title: 'Invalid Number', text: 'Number of approvals cannot exceed ' + maxPaperApprovals });
            $(this).val(maxPaperApprovals);
            generatePaperApprovalRoleSelects();
        } else if (numApprovals > 0) {
            generatePaperApprovalRoleSelects();
        }
    });

    // Generate approval role selects
    function generateApprovalRoleSelects() {
        const numApprovals = parseInt($('#number_of_approvals').val()) || 0;
        const container = $('#approval_role_selections');
        container.empty();
        if (numApprovals <= 0 || (roles.length === 0 && specialRoles.length === 0)) return;
        for (let i = 1; i <= numApprovals; i++) {
            const selectId = 'approval_role_' + i;
            const selectHtml = `
                <div class="form-group">
                    <label for="${selectId}">Approval ${i} - Select Role <span class="text-danger">*</span></label>
                    <select class="form-control approval-role-select" id="${selectId}" name="approval_role_ids[]" required>
                        <option value="">Select Role</option>
                    </select>
                </div>`;
            container.append(selectHtml);
            const select = $('#' + selectId);
            specialRoles.forEach(role => select.append($('<option>', { value: role.id, text: role.name || role.role_name })));
            roles.forEach(role => select.append($('<option>', { value: role.id, text: role.name || role.role_name })));
        }
        updateRoleSelectOptions('.approval-role-select');
    }

    // Generate paper approval role selects
    function generatePaperApprovalRoleSelects() {
        const numApprovals = parseInt($('#number_of_paper_approvals').val()) || 0;
        const container = $('#paper_approval_role_selections');
        container.empty();
        // Allow generating if special roles exist even if roles.length is 0
        if (numApprovals <= 0) return;
        for (let i = 1; i <= numApprovals; i++) {
            const selectId = 'paper_approval_role_' + i;
            const selectHtml = `
                <div class="form-group">
                    <label for="${selectId}">Paper Approval ${i} - Select Role <span class="text-danger">*</span></label>
                    <select class="form-control paper-approval-role-select" id="${selectId}" name="paper_approval_role_ids[]" required>
                        <option value="">Select Role</option>
                    </select>
                </div>`;
            container.append(selectHtml);
            const select = $('#' + selectId);
            
            // Add Special Roles first
            select.append('<option value="class_teacher">Class Teacher</option>');
            select.append('<option value="coordinator">Coordinator</option>');
            select.append('<option value="head_of_department">Head Of Department</option>');
            
            // Add Regular Roles
            roles.forEach(role => select.append($('<option>', { 
                value: role.id, text: role.name || role.role_name 
            })));
        }
        updateRoleSelectOptions('.paper-approval-role-select');
    }

    function updateRoleSelectOptions(selector) {
        $(selector).on('change', function() {
            const allSelects = $(selector);
            const selectedValues = [];
            allSelects.each(function() {
                if ($(this).val()) selectedValues.push($(this).val());
            });

            allSelects.each(function() {
                const currentSelect = $(this);
                const currentValue = currentSelect.val();
                currentSelect.find('option').each(function() {
                    const opt = $(this);
                    if (opt.val() && opt.val() !== currentValue) {
                        if (selectedValues.indexOf(opt.val()) !== -1) {
                            opt.prop('disabled', true).css('background-color', '#eee');
                        } else {
                            opt.prop('disabled', false).css('background-color', '');
                        }
                    } else {
                        opt.prop('disabled', false).css('background-color', '');
                    }
                });
            });
        });
    }

        // Add change handlers to prevent duplicates
        $('.approval-role-select').on('change', function() {
            const currentSelect = $(this);
            const currentValue = currentSelect.val();
            const currentId = currentSelect.attr('id');

            // Remove this value from all other selects
            $('.approval-role-select').not(currentSelect).each(function() {
                const otherSelect = $(this);
                // Remove previously selected option
                otherSelect.find('option[data-selected="true"]').prop('selected', false).removeAttr('data-selected');
                
                // If this select had the same value, reset it
                if (otherSelect.val() === currentValue) {
                    otherSelect.val('');
                }

                // Re-enable all options
                otherSelect.find('option').prop('disabled', false);

                // Disable the selected value in other selects
                if (currentValue) {
                    otherSelect.find('option[value="' + currentValue + '"]').prop('disabled', true);
                }
            });

            // Update disabled states based on all selected values
            const selectedValues = [];
            $('.approval-role-select').each(function() {
                const val = $(this).val();
                if (val) {
                    selectedValues.push(val);
                }
            });

            $('.approval-role-select').each(function() {
                const thisSelect = $(this);
                const thisValue = thisSelect.val();
                
                // Re-enable all options first
                thisSelect.find('option').prop('disabled', false);
                
                // Disable selected values from other selects
                selectedValues.forEach(function(selectedVal) {
                    if (selectedVal !== thisValue) {
                        thisSelect.find('option[value="' + selectedVal + '"]').prop('disabled', true);
                    }
                });
            });
        });


    function addSubclassField() {
        subclassCount++;
        const fieldHtml = `
            <div class="form-group select-item" id="subclass-group-${subclassCount}">
                <label>Select Class <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                    <select class="form-control subclass-select" name="subclass_ids[]" required>
                        <option value="">Select Class</option>
                        @foreach($subclasses as $subclass)
                            <option value="{{ $subclass->subclassID }}">
                                {{ $subclass->subclass_name }}
                                @if($subclass->stream_code)
                                    ({{ $subclass->stream_code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-btn" onclick="removeSubclassField(${subclassCount})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        `;
        $('#dynamicFields').append(fieldHtml);
    }

    // Make addSubclassField globally accessible for onclick handlers
    window.addSubclassField = function() {
        addSubclassField();
    };

    function addSubjectField(subclassIds = []) {
        subjectCount++;
        const fieldId = `subject-group-${subjectCount}`;
        const fieldHtml = `
            <div class="form-group select-item" id="${fieldId}">
                <label>Select Subject <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                    <select class="form-control subject-select" name="class_subject_ids[]" id="subject-select-${subjectCount}" required>
                        <option value="">Loading subjects...</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-btn" onclick="removeSubjectField(${subjectCount})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        `;
        $('#dynamicFields').append(fieldHtml);

        // Load subjects based on selected subclasses
        loadSubjectsForField(subjectCount, subclassIds);
    }

    function loadSubjectsForField(fieldId, subclassIds = []) {
        const selectElement = $(`#subject-select-${fieldId}`);
        selectElement.html('<option value="">Loading subjects...</option>');

        const examType = $('#exam_type').val();

        // For school_wide_specific_subjects, load all subjects for the school (no subclass filter needed)
        if (examType === 'school_wide_specific_subjects') {
            // Pass empty array to get all subjects for the school
            subclassIds = [];
        } else {
            // For specific_classes_specific_subjects, get selected subclass IDs if not provided
        if (subclassIds.length === 0) {
            subclassIds = [];
            $('.subclass-select').each(function() {
                if ($(this).val()) {
                    subclassIds.push($(this).val());
                }
            });
        }

            // For specific_classes_specific_subjects, require at least one class to be selected
        if (subclassIds.length === 0) {
            selectElement.html('<option value="">Please select a class first</option>');
            return;
            }
        }

        $.ajax({
            url: '/get_class_subjects_by_subclass',
            method: 'POST',
            data: { subclass_ids: subclassIds },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.class_subjects) {
                    let options = '<option value="">Select Subject</option>';
                    response.class_subjects.forEach(function(subject) {
                        const subjectName = subject.subject_name || 'N/A';
                        const className = subject.class_name || 'N/A';
                        const subclassName = subject.subclass_name || 'N/A';
                        options += `<option value="${subject.class_subjectID}" data-subclass="${subject.subclassID || ''}">
                            ${subjectName} (${subclassName || className})
                        </option>`;
                    });
                    selectElement.html(options);
                } else {
                    selectElement.html('<option value="">No subjects found</option>');
                }
            },
            error: function(xhr) {
                selectElement.html('<option value="">Error loading subjects</option>');
                console.error('Error loading subjects:', xhr);
            }
        });
    }

    // Consolidated fetchSubclasses function
    function fetchSubclasses(classID, selectElement, selectedSubclassID = null) {
        if (!classID) {
            selectElement.html('<option value="">Select Subclass (Optional)</option>').prop('disabled', true);
            return;
        }
        
        selectElement.html('<option value="">Loading...</option>').prop('disabled', true);
        
        $.ajax({
            url: '/get_class_subclasses/' + classID,
            method: 'GET',
            success: function(response) {
                selectElement.html('<option value="">Select Subclass (Optional)</option>');
                if (response.subclasses && response.subclasses.length > 0) {
                    response.subclasses.forEach(function(subclass) {
                        let isSelected = (selectedSubclassID && subclass.subclassID == selectedSubclassID) ? 'selected' : '';
                        selectElement.append(`<option value="${subclass.subclassID}" ${isSelected}>${subclass.subclass_name}</option>`);
                    });
                    selectElement.prop('disabled', false);
                } else {
                    selectElement.append('<option value="">No subclasses found</option>');
                }
            },
            error: function(xhr) {
                console.error('Error fetching subclasses:', xhr);
                selectElement.html('<option value="">Error loading</option>');
            }
        });
    }

    window.removeSubclassField = function(id) {
        $('#subclass-group-' + id).remove();
    };

    window.removeSubjectField = function(id) {
        $('#subject-group-' + id).remove();
    };


    // Store user permissions for JavaScript checks - Make globally accessible
    window.userPermissions = @json($teacherPermissions ?? collect());
    window.userType = @json($user_type ?? '');

    // Helper function to check permission - Make globally accessible
    // Note: Admin has ALL permissions by default, other users need explicit permissions
    window.hasPermission = function(permissionName) {
        // Admin has all permissions automatically
        if (window.userType === 'Admin') {
            return true;
        }
        // For non-admin users, check if they have the specific permission
        if (!window.userPermissions || !Array.isArray(window.userPermissions)) {
            return false;
        }
        return window.userPermissions.includes(permissionName);
    };

    // Form submission
    $('#createExamForm').on('submit', function(e) {
        e.preventDefault();

        // Check create permission - New format: examination_create
        if (!hasPermission('examination_create')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to create examinations. You need examination_create permission.'
            });
            return false;
        }

        const formData = new FormData(this);
        const examCategory = $('#exam_category').val();
        const testType = $('#test_type').val();

        // Remove exam_name_type if not needed (to avoid validation errors)
        formData.delete('exam_name_type');

        // Handle exam name based on exam category
        if (examCategory === 'school_exams') {
            // For school exams, use exam_name_type value as exam_name
            const examNameType = $('#exam_name_type').val();
            if (examNameType) {
                formData.set('exam_name', examNameType);
                formData.set('exam_name_type', examNameType); // Required for validation
            }
            // Note: exam_type is set automatically by backend controller
        } else if (examCategory === 'test') {
            // Remove exam_name_type for test category (not needed and causes validation error)
            formData.delete('exam_name_type');
            
            // For test category, check the test type
            // Make sure test_type is included in form data
            if (testType) {
                formData.set('test_type', testType);
            }
            
            if (testType === 'weekly_test') {
                // For weekly test, exam name is Weekly Test
                formData.set('exam_name', 'Weekly Test');
                // Set valid dates that pass validation (backend will convert to "every_week")
                // Use the year from the form or current year
                const year = $('#year').val() || new Date().getFullYear();
                const validDate = year + '-01-01'; // Use a valid date format (Jan 1st of the year)
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                // Also set a flag for backend to know it's weekly
                formData.set('is_weekly_test', '1');
                // Note: exam_type is set automatically by backend controller
            } else if (testType === 'monthly_test') {
                // For monthly test, exam name is Monthly Test
                formData.set('exam_name', 'Monthly Test');
                // Set valid dates that pass validation (backend will convert to "every_month")
                // Use the year from the form or current year
                const year = $('#year').val() || new Date().getFullYear();
                const validDate = year + '-01-01'; // Use a valid date format (Jan 1st of the year)
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                // Also set a flag for backend to know it's monthly
                formData.set('is_monthly_test', '1');
                // Note: exam_type is set automatically by backend controller
            } else if (testType === 'other_test') {
                // For other test, use the text input value
                const otherTestName = $('#exam_name').val();
                if (!otherTestName || otherTestName.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a test name for Other Test.'
                    });
                    return false;
                }
                formData.set('exam_name', otherTestName.trim());
                // Keep start_date and end_date for other test (they are already in formData from the date inputs)
                // Validate that dates are provided
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select start date and end date for Other Test.'
                    });
                    return false;
                }
                // Note: exam_type is set automatically by backend controller
            } else {
                // No test type selected
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a test type.'
                });
                return false;
            }
        } else if (examCategory === 'special_exams') {
            formData.delete('exam_name_type');
            // Ensure exam_type is set for special exams
            formData.set('exam_type', 'specific_classes_all_subjects');
        }

        // Collect except class IDs (for School Exams and Test)
        const exceptClassIds = [];
        if ((examCategory === 'school_exams' || examCategory === 'test') && $('#use_except').is(':checked')) {
            $('.except-class-checkbox:checked').each(function() {
                exceptClassIds.push($(this).val());
            });
        }
        formData.delete('except_class_ids[]');
        exceptClassIds.forEach((id) => {
            formData.append('except_class_ids[]', id);
        });

        // Collect include class IDs (for Special Exams)
        const includeClassIds = [];
        if (examCategory === 'special_exams') {
            $('.include-class-checkbox:checked').each(function() {
                includeClassIds.push($(this).val());
            });
            // Validate at least one class is selected
            if (includeClassIds.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one class for this special examination.'
                });
                return false;
            }
        }
        formData.delete('include_class_ids[]');
        includeClassIds.forEach((id) => {
            formData.append('include_class_ids[]', id);
        });

        // Handle result approval chain
        if ($('#use_result_approval').is(':checked')) {
            formData.append('use_result_approval', '1');
            const numApprovals = parseInt($('#number_of_approvals').val()) || 0;
            formData.append('number_of_approvals', numApprovals);
            
            // Collect approval role IDs
            const approvalRoleIds = [];
            $('.approval-role-select').each(function() {
                const roleId = $(this).val();
                if (roleId) {
                    approvalRoleIds.push(roleId);
                }
            });
            
            // Validate that all roles are selected
            if (approvalRoleIds.length !== numApprovals) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a role for all approval steps.'
                });
                return false;
            }
            
            formData.delete('approval_role_ids[]');
            approvalRoleIds.forEach((id) => {
                formData.append('approval_role_ids[]', id);
            });
        } else {
            formData.delete('use_result_approval');
            formData.delete('number_of_approvals');
            formData.delete('approval_role_ids[]');
        }

        if ($('#no_approval_required').is(':checked')) {
            formData.append('no_approval_required', '1');
        } else {
            formData.delete('no_approval_required');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        validateHallCapacities('create').then(valid => {
            if (!valid) return;
            
            // Show loading progress bar
            Swal.fire({
                title: 'Creating Examination...',
                html: 'Please wait while we process your request.<br><br><div class="progress mt-2" style="height: 10px; border-radius: 5px; overflow: hidden;"><div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 100%"></div></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const submitBtn = $('#createExamForm button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("store_examination") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    $('#createExamModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.success,
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    let errorMessage = '';

                    // Check for validation errors
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml = '<ul class="mb-0">';
                        for (let field in errors) {
                            if (Array.isArray(errors[field])) {
                                errors[field].forEach(function(msg) {
                                    errorHtml += '<li>' + msg + '</li>';
                                });
                            } else {
                                errorHtml += '<li>' + errors[field] + '</li>';
                            }
                        }
                        errorHtml += '</ul>';
                        errorMessage = errorHtml;
                    }
                    // Check for general error message
                    else if (xhr.responseJSON && xhr.responseJSON.error) {
                        let errorText = xhr.responseJSON.error;
                        if (xhr.responseJSON.details && xhr.responseJSON.details.message) {
                            errorText += '<br><small class="text-muted">' + xhr.responseJSON.details.message + '</small>';
                        }
                        errorMessage = '<p class="mb-0"><strong>' + errorText + '</strong></p>';
                    }
                    else if (xhr.status === 403) {
                        errorMessage = '<p class="mb-0"><strong>Access Denied:</strong> You do not have permission to perform this action.</p>';
                    }
                    else if (xhr.status === 422) {
                        errorMessage = '<p class="mb-0"><strong>Validation Error:</strong> Please check your input and try again.</p>';
                    }
                    else {
                        errorMessage = '<p class="mb-0"><strong>Error:</strong> Failed to create examination. Please try again.</p>';
                    }

                    if (errorMessage) {
                        $('#examFormErrors').html(errorMessage).show();
                    } else {
                        $('#examFormErrors').html('<p class="mb-0"><strong>Error:</strong> An unexpected error occurred. Please try again.</p>').show();
                    }
                }
            });
        });
    });

    // Reset form when modal is closed
    $('#createExamModal').on('hidden.bs.modal', function() {
        $('#createExamForm')[0].reset();
        $('#dynamicFields').empty();
        $('#examFormErrors').hide();
        subclassCount = 0;
        subjectCount = 0;
        // Hide all conditional fields
        $('#test_type_group').hide();
        $('#exam_name_text_group').hide();
        $('#exam_name_select_group').hide();
        $('#term_group').hide();
        $('#except_classes_group').hide();
        $('#include_classes_group').hide();
        $('#student_shifting_status_group').hide();
        $('#date_fields_group').show();
        $('#start_date').prop('required', true);
        $('#end_date').prop('required', true);
        
        // Reset result approval fields
        $('#use_result_approval').prop('checked', false);
        $('#result_approval_fields').hide();
        $('#number_of_approvals').val(1).prop('required', false);
        $('#approval_role_selections').empty();
        
        // Reset exam attendance checkbox
        $('#enable_exam_attendance').prop('checked', false);

        // Reset halls
        resetHalls('create');
    });

    // Change exam status
    // Approve Exam
    // Event Delegation for Widget Buttons
    $(document).on('click', '.view-exam-more-btn', function() {
        const examID = $(this).data('exam-id');
        viewExamMore(examID);
    });

    $(document).on('click', '.edit-exam-btn', function() {
        const examID = $(this).data('exam-id');
        editExam(examID);
    });

    $(document).on('click', '.approve-exam-btn', function(e) {
        e.preventDefault();
        
        // Check permission
        if (!hasPermission('approve_exam')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to approve examinations.'
            });
            return;
        }
        
        var examID = $(this).data('exam-id');
        var examName = $(this).data('exam-name');

        Swal.fire({
            title: 'Approve Examination?',
            text: 'Do you want to approve "' + examName + '"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/approve_exam/' + examID,
                    method: 'POST',
                    data: {
                        approval_status: 'Approved',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Approved!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to approve exam.';
                        if (xhr.status === 403) {
                            errorMsg = xhr.responseJSON?.error || 'You do not have permission to approve examinations.';
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // View Report
    function viewReport(examID) {
        window.open('/view_exam_results/' + examID, '_blank');
    }

    // View Exam More - Detailed view like manage_attendance
    window.viewExamMore = function(examID) {
        $('#viewExamDetailsModal').modal('show');
        $('#examDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3">Loading examination details...</p>
            </div>
        `);

        $.ajax({
            url: '/get_exam_details/' + examID,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    displayExamMoreDetails(response);
                } else {
                    $('#examDetailsContent').html('<div class="alert alert-danger">Error loading examination details</div>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error loading examination details';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#examDetailsContent').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    }

    function displayExamMoreDetails(data) {
        const exam = data.exam;
        const classes = data.classes;
        const attendance = data.attendance || null;
        const examHasEnded = exam.has_ended || false;
        
        // Check if this is a Weekly Test or Monthly Test
        const isWeeklyTest = exam.exam_name === 'Weekly Test' || exam.start_date === 'every_week' || exam.end_date === 'every_week';
        const isMonthlyTest = exam.exam_name === 'Monthly Test' || exam.start_date === 'every_month' || exam.end_date === 'every_month';
        
        // Format dates or use text for weekly/monthly tests
        let dateDisplay = '';
        if (isWeeklyTest) {
            dateDisplay = '<small class="d-block">Every week</small>';
        } else if (isMonthlyTest) {
            dateDisplay = '<small class="d-block">Every month in a term</small>';
        } else {
            try {
        const startDate = new Date(exam.start_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const endDate = new Date(exam.end_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                dateDisplay = `<small class="d-block">${startDate}</small><small class="d-block">to ${endDate}</small>`;
            } catch (e) {
                dateDisplay = '<small class="d-block">Date not available</small>';
            }
        }
        
        let html = `
            <div class="mb-4">
                <h4 class="mb-3" style="color: #940000; font-weight: 700; font-size: 1.5rem;">
                    <i class="bi bi-clipboard-check"></i> ${exam.exam_name}
                </h4>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Students</h6>
                                        <h3 class="mb-0 fw-bold">${data.total_students}</h3>
                                    </div>
                                    <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Classes</h6>
                                        <h3 class="mb-0 fw-bold">${data.total_classes}</h3>
                                    </div>
                                    <i class="bi bi-building" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Total Subjects</h6>
                                        <h3 class="mb-0 fw-bold">${data.total_subjects}</h3>
                                    </div>
                                    <i class="bi bi-book" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.85rem; opacity: 0.9;">Exam Period</h6>
                                        ${dateDisplay}
                                    </div>
                                    <i class="bi bi-calendar-range" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${examHasEnded && attendance ? `
                <!-- Exam Attendance Statistics (Only shown after exam ends) -->
                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #940000 !important;">
                    <div class="card-header bg-primary-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> Exam Attendance Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body text-center">
                                        <h4 class="mb-1 fw-bold">${attendance.expected || 0}</h4>
                                        <p class="mb-0">Walio Takwa Kufanya</p>
                                        <small>(Expected Students)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                        <h4 class="mb-1 fw-bold">${attendance.present || 0}</h4>
                                        <p class="mb-0">Walio Fanya</p>
                                        <small>(Present)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-danger text-white h-100">
                                    <div class="card-body text-center">
                                        <h4 class="mb-1 fw-bold">${attendance.absent || 0}</h4>
                                        <p class="mb-0">Wasio Fanya</p>
                                        <small>(Absent)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${attendance.by_subclass && attendance.by_subclass.length > 0 ? `
                        <hr>
                        <h6 class="mb-3"><i class="bi bi-list-ul"></i> Attendance by Class</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Class</th>
                                        <th class="text-center">Expected</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ` : ''}
                        ${attendance.by_subclass ? attendance.by_subclass.map(subclass => `
                                    <tr>
                                        <td><strong>${subclass.class_name} - ${subclass.subclass_name}</strong></td>
                                        <td class="text-center">${subclass.expected || 0}</td>
                                        <td class="text-center text-success"><strong>${subclass.present || 0}</strong></td>
                                        <td class="text-center text-danger"><strong>${subclass.absent || 0}</strong></td>
                                    </tr>
                        `).join('') : ''}
                        ${attendance.by_subclass && attendance.by_subclass.length > 0 ? `
                                </tbody>
                            </table>
                        </div>
                        ` : ''}
                    </div>
                </div>
                ` : ''}

                <!-- Classes Details -->
                <div class="row">
        `;

        classes.forEach((classData, index) => {
            const isCompleted = classData.students_with_marks !== undefined;
            html += `
                <div class="col-md-6 mb-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-building"></i> ${classData.class_name} - ${classData.subclass_name}
                                ${classData.stream_code ? '<span class="badge bg-secondary ms-2">' + classData.stream_code + '</span>' : ''}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Students</small>
                                    <strong>${classData.students_count}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Subjects</small>
                                    <strong>${classData.subjects_count}</strong>
                                </div>
                            </div>
                            ${isCompleted ? `
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-success d-block">Completed</small>
                                        <strong class="text-success">${classData.students_with_marks || 0}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-warning d-block">Not Completed</small>
                                        <strong class="text-warning">${classData.students_without_marks || 0}</strong>
                                    </div>
                                </div>
                            ` : ''}
                            <div class="mb-2">
                                <small class="text-muted d-block mb-2"><strong>Subjects:</strong></small>
                                <div class="d-flex flex-wrap gap-1">
            `;
            
            classData.subjects.forEach(subject => {
                html += `<span class="badge bg-primary">${subject.subject_name}</span>`;
            });
            
            html += `
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;

        $('#examDetailsContent').html(html);
    }

    // Toggle Enter Result
    $(document).on('click', '.toggle-enter-result-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        // Use attr() to get the raw string value, then convert to boolean
        const currentValueStr = $(this).attr('data-current-value');
        const currentValue = currentValueStr === 'true' || currentValueStr === true;
        const newValue = !currentValue;
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: newValue ? 'Allow Enter Result?' : 'Disallow Enter Result?',
            text: newValue 
                ? `Allow teachers to enter results for "${examName}"?`
                : `Disallow result entry for "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading message
                const loadingTitle = newValue ? 'Enabling Enter Result...' : 'Disabling Enter Result...';
                const loadingText = newValue 
                    ? 'Please wait while we enable result entry and notify teachers via SMS. This may take a few minutes...'
                    : 'Please wait while we disable result entry and notify teachers via SMS. This may take a few minutes...';
                
                Swal.fire({
                    title: loadingTitle,
                    html: loadingText,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/toggle_enter_result/' + examID,
                    method: 'POST',
                    data: {
                        enter_result: newValue ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let successMessage = response.success || 'Result entry status updated successfully';
                        if (response.sms_sent_count !== undefined && response.sms_sent_count > 0) {
                            successMessage += ' SMS zimetumwa kwa walimu ' + response.sms_sent_count + '.';
                        }
                        
                        Swal.fire({
                            title: 'Success!',
                            text: successMessage,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to update';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else if (xhr.status === 403) {
                            errorMsg = 'You do not have permission to perform this action';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Examination not found';
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Toggle Publish Result
    $(document).on('click', '.toggle-publish-result-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        // Use attr() to get the raw string value, then convert to boolean
        const currentValueStr = $(this).attr('data-current-value');
        const currentValue = currentValueStr === 'true' || currentValueStr === true;
        const newValue = !currentValue;
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: newValue ? 'Publish Result?' : 'Unpublish Result?',
            text: newValue 
                ? `Publish results for "${examName}"? This will make results visible to all.`
                : `Unpublish results for "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading message, especially for publishing (SMS sending)
                if (newValue) {
                    Swal.fire({
                        title: 'Publishing Results...',
                        html: 'Tunachapisha matokeo na kutuma SMS kwa wazazi. Tafadhali subiri...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/toggle_publish_result/' + examID,
                    method: 'POST',
                    data: {
                        publish_result: newValue ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let successMessage = response.success;
                        if (newValue && response.sms_sent_count !== undefined && response.sms_sent_count > 0) {
                            successMessage += ' SMS zimetumwa kwa wazazi ' + response.sms_sent_count + ' wanaofunzi.';
                        }
                        
                        Swal.fire({
                            title: 'Success!',
                            text: successMessage,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to update',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Toggle Upload Paper
    $(document).on('click', '.toggle-upload-paper-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        // Use attr() to get the raw string value, then convert to boolean
        const currentValueStr = $(this).attr('data-current-value');
        const currentValue = currentValueStr === 'true' || currentValueStr === true;
        const newValue = !currentValue;
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: newValue ? 'Allow Upload Paper?' : 'Disallow Upload Paper?',
            text: newValue 
                ? `Allow teachers to upload exam papers for "${examName}"?`
                : `Disallow exam paper upload for "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/toggle_upload_paper/' + examID,
                    method: 'POST',
                    data: {
                        upload_paper: newValue ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.success || 'Upload paper status updated successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to update';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else if (xhr.status === 403) {
                            errorMsg = 'You do not have permission to perform this action';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Examination not found';
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Auto Shift Students
    $(document).on('click', '.auto-shift-students-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        const shiftingStatus = $(this).data('shifting-status');
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';
        const statusText = shiftingStatus === 'internal' ? 'darasa moja kwenda jengine (mfano: Form One A kwenda Form One B)' : 'darasa moja kwenda jengine (mfano: Form Three kwenda Form Four)';

        Swal.fire({
            title: 'Auto Shift Students?',
            text: `Je, unataka kuhamisha wanafunzi kwa automatik kutoka ${statusText} kwa ajili ya "${examName}"? Hii itaangalia matokeo na kuhamisha wanafunzi kulingana na alama zao.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndiyo, Hamisha',
            cancelButtonText: 'Ghairi'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Inahamisha Wanafunzi...',
                    html: 'Tunahamisha wanafunzi na kutuma SMS kwa wazazi. Tafadhali subiri...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/auto_shift_students/' + examID,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Imekamilika!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Kosa!',
                            text: xhr.responseJSON?.error || 'Imeshindwa kuhamisha wanafunzi',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Unshift Students
    $(document).on('click', '.unshift-students-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const examID = $(this).data('exam-id');
        const examName = $(this).closest('.card').find('h6').text() || 'this examination';

        Swal.fire({
            title: 'Rudisha Wanafunzi?',
            text: `Je, unataka kurudisha wanafunzi waliohamishwa kurudi darasa la zamani kwa ajili ya "${examName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndiyo, Rudisha',
            cancelButtonText: 'Ghairi'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Inarudisha Wanafunzi...',
                    html: 'Tunarudisha wanafunzi darasa la zamani. Tafadhali subiri...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/unshift_students/' + examID,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Imekamilika!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Kosa!',
                            text: xhr.responseJSON?.error || 'Imeshindwa kurudisha wanafunzi',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });

    // Reject Exam
    $(document).on('click', '.reject-exam-btn', function(e) {
        e.preventDefault();
        
        // Check permission
        if (!hasPermission('reject_exam')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to reject examinations.'
            });
            return;
        }
        
        var examID = $(this).data('exam-id');
        var examName = $(this).data('exam-name');

        // Populate reject modal
        $('#reject_exam_id').val(examID);
        $('#reject_exam_name').val(examName);
        $('#rejection_reason').val('');

        // Show reject modal
        $('#rejectExamModal').modal('show');
    });

    // Handle Reject Exam Form Submission
    $(document).on('submit', '#rejectExamForm', function(e) {
        e.preventDefault();

        // Check permission
        if (!hasPermission('reject_exam')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to reject examinations.'
            });
            $('#rejectExamModal').modal('hide');
            return false;
        }

        var examID = $('#reject_exam_id').val();
        var rejectionReason = $('#rejection_reason').val();

        if (!rejectionReason || rejectionReason.trim() === '') {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please provide a reason for rejecting this examination.'
            });
            return false;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/approve_exam/' + examID,
            method: 'POST',
            data: {
                approval_status: 'Rejected',
                rejection_reason: rejectionReason,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    title: 'Rejected!',
                    text: response.success,
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    $('#rejectExamModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                var errorMsg = 'Failed to reject exam.';
                if (xhr.status === 403) {
                    errorMsg = xhr.responseJSON?.error || 'You do not have permission to reject examinations.';
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire({
                    title: 'Error!',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    });



    // Delete exam (from button click)
    $(document).on('click', '.delete-exam-btn', function(e) {
        e.preventDefault();
        const examID = $(this).data('exam-id');
        const examName = $(this).data('exam-name');
        deleteExam(examID, examName);
    });

    // Delete exam
    window.deleteExam = function(examID, examName) {
        // Check delete permission - New format: examination_delete
        if (!hasPermission('examination_delete')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to delete examinations. You need examination_delete permission.'
            });
            return false;
        }

        Swal.fire({
            title: 'Delete Examination?',
            html: 'Are you sure you want to delete <strong>"' + examName + '"</strong>?<br><br>This will also delete all related results where examID = ' + examID + '.<br><span style="color: #dc3545; font-weight: bold;">This action cannot be undone!</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the examination and all related results.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/delete_examination/' + examID,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.success || 'Examination and all related results deleted successfully!',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to delete exam',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    };

    // See More button handler
    $(document).on('click', '.see-more-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const examType = btn.data('exam-type');
        const currentPage = btn.data('page');
        const nextPage = currentPage + 1;

        // Disable button and show loading
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Loading...');

        // Get search filters
        const search = $('#search_exam_name').val() || '';
        const year = $('#filter_year').val() || '';
        const status = $('#filter_status').val() || '';
        const approval = $('#filter_approval').val() || '';
        const examTypeFilter = $('#filter_exam_type').val() || '';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '{{ route("search_examinations") }}',
            method: 'POST',
            data: {
                page: nextPage,
                per_page: 12,
                search: search,
                year: year,
                status: status,
                approval_status: approval,
                exam_type: examTypeFilter
            },
            success: function(response) {
                if (response.html && response.html[examType]) {
                    // Append new exams
                    const containerMap = {
                        'school_wide_all_subjects': 'schoolWideAllExams',
                        'specific_classes_all_subjects': 'specificClassesAllExams',
                        'school_wide_specific_subjects': 'schoolWideSpecificExams',
                        'specific_classes_specific_subjects': 'specificClassesSpecificExams'
                    };
                    const container = $('#' + containerMap[examType]);
                    container.append(response.html[examType]);

                    // Update button
                    if (response.has_more) {
                        btn.prop('disabled', false).data('page', nextPage).html('<i class="bi bi-arrow-down-circle"></i> See More');
                    } else {
                        btn.remove();
                    }
                } else {
                    btn.remove();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to load more exams',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                btn.prop('disabled', false).html('<i class="bi bi-arrow-down-circle"></i> See More');
            }
        });
    });

    // -------------------------
    // Edit examination helpers
    // -------------------------
    function resetEditFormState() {
        $('#editExamForm')[0].reset();
        $('#editExamFormErrors').hide().html('');
        $('#edit_exam_name_text_group').hide();
        $('#edit_exam_name_select_group').hide();
        $('#edit_test_type_group').hide();
        $('#edit_term_group').hide();
        $('#edit_except_classes_group').hide();
        $('#edit_except_classes_selection').hide();
        $('#edit_include_classes_group').hide();
        $('#edit_student_shifting_status_group').hide();
        $('#edit_result_approval_fields').hide();
        $('#edit_approval_role_selections').empty();
        $('#edit_date_fields_group').show();
        $('#edit_start_date').prop('required', true);
        $('#edit_end_date').prop('required', true);
        $('#edit_number_of_approvals').val(1).prop('required', false);
        $('#edit_use_except').prop('checked', false);
        $('#edit_use_result_approval').prop('checked', false);
        $('#edit_enable_exam_attendance').prop('checked', false);
        $('.edit-except-class-checkbox, .edit-include-class-checkbox').prop('checked', false);
        resetHalls('edit');
    }

    function applyEditCategorySettings(examCategory) {
        // Reset category-specific fields
        $('#edit_exam_name').val('');
        $('#edit_exam_name_type').val('');
        $('#edit_test_type').val('');
        $('#edit_term').val('');
        $('#edit_use_except').prop('checked', false);
        $('.edit-except-class-checkbox, .edit-include-class-checkbox').prop('checked', false);
        $('#edit_except_classes_selection').hide();
        $('#edit_start_date_help, #edit_end_date_help').hide();
        $('#edit_test_type_group').hide();
        $('#edit_date_fields_group').show();
        $('#edit_start_date').prop('required', true);
        $('#edit_end_date').prop('required', true);

        if (examCategory === 'school_exams') {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_exam_name_select_group').show();
            $('#edit_exam_name_type').prop('required', true);

            $('#edit_term_group').show();
            $('#edit_term').prop('required', true);

            $('#edit_except_classes_group').show();
            $('#edit_include_classes_group').hide();
            $('.edit-include-class-checkbox').prop('required', false);

            $('#edit_student_shifting_status_group').show();
            $('#edit_student_shifting_status').prop('required', true);
        } else if (examCategory === 'test') {
            $('#edit_test_type_group').show();
            $('#edit_test_type').prop('required', true);

            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_exam_name_type').prop('required', false);

            $('#edit_term_group').show();
            $('#edit_term').prop('required', true);

            $('#edit_except_classes_group').show();
            $('#edit_include_classes_group').hide();
            $('.edit-include-class-checkbox').prop('required', false);

            $('#edit_student_shifting_status_group').show();
            $('#edit_student_shifting_status').prop('required', true);
        } else if (examCategory === 'special_exams') {
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name_type').prop('required', false);
            $('#edit_exam_name_text_group').show();
            $('#edit_exam_name').prop('required', true);
            $('#edit_exam_name').attr('placeholder', 'Enter examination name');

            $('#edit_term_group').show();
            $('#edit_term').prop('required', true);

            $('#edit_include_classes_group').show();
            $('#edit_except_classes_group').hide();
            $('.edit-except-class-checkbox').prop('required', false);

            $('#edit_student_shifting_status_group').hide();
            $('#edit_student_shifting_status').prop('required', false);
        } else {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_test_type_group').hide();
            $('#edit_term_group').hide();
            $('#edit_except_classes_group').hide();
            $('#edit_include_classes_group').hide();
            $('#edit_student_shifting_status_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_exam_name_type').prop('required', false);
            $('#edit_term').prop('required', false);
        }
    }

    function applyEditTestTypeSettings(testType) {
        $('#edit_exam_name').val('');
        $('#edit_exam_name_type').val('');

        if (testType === 'weekly_test') {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').val('Weekly Test');
            $('#edit_exam_name').prop('required', false);

            $('#edit_date_fields_group').hide();
            $('#edit_start_date').prop('required', false);
            $('#edit_end_date').prop('required', false);
            $('#edit_start_date_help, #edit_end_date_help').hide();
        } else if (testType === 'monthly_test') {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').val('Monthly Test');
            $('#edit_exam_name').prop('required', false);

            $('#edit_date_fields_group').hide();
            $('#edit_start_date').prop('required', false);
            $('#edit_end_date').prop('required', false);
            $('#edit_start_date_help, #edit_end_date_help').hide();
        } else if (testType === 'other_test') {
            $('#edit_exam_name_text_group').show();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').prop('required', true);
            $('#edit_exam_name').attr('placeholder', 'Enter test name (e.g., Quiz, Assessment, etc.)');

            $('#edit_date_fields_group').show();
            $('#edit_start_date').prop('required', true);
            $('#edit_end_date').prop('required', true);
            $('#edit_start_date_help, #edit_end_date_help').hide();
        } else {
            $('#edit_exam_name_text_group').hide();
            $('#edit_exam_name_select_group').hide();
            $('#edit_exam_name').prop('required', false);
            $('#edit_date_fields_group').show();
            $('#edit_start_date').prop('required', true);
            $('#edit_end_date').prop('required', true);
        }
    }

    function generateEditApprovalRoleSelects(selectedRoles = []) {
        const numApprovals = parseInt($('#edit_number_of_approvals').val()) || 0;
        const container = $('#edit_approval_role_selections');
        container.empty();
        if (numApprovals <= 0) return;
        for (let i = 1; i <= numApprovals; i++) {
            const selectId = 'edit_approval_role_' + i;
            container.append(`<div class="form-group"><label>Approval ${i}</label><select class="form-control edit-approval-role-select" id="${selectId}" name="approval_role_ids[]" required><option value="">Select Role</option></select></div>`);
            const select = $('#' + selectId);
            specialRoles.forEach(r => select.append($('<option>', { value: r.id, text: r.name || r.role_name })));
            roles.forEach(r => select.append($('<option>', { value: r.id, text: r.name || r.role_name })));
            if (selectedRoles[i - 1]) select.val(selectedRoles[i - 1]);
        }
        updateRoleSelectOptions('.edit-approval-role-select');
        $('.edit-approval-role-select').trigger('change');
    }

    function generateEditPaperApprovalRoleSelects(selectedRoles = []) {
        const numApprovals = parseInt($('#edit_number_of_paper_approvals').val()) || 0;
        const container = $('#edit_paper_approval_role_selections');
        container.empty();
        if (numApprovals <= 0) return;
        for (let i = 1; i <= numApprovals; i++) {
            const selectId = 'edit_paper_approval_role_' + i;
            container.append(`<div class="form-group"><label>Paper Approval ${i}</label><select class="form-control edit-paper-approval-role-select" id="${selectId}" name="paper_approval_role_ids[]" required><option value="">Select Role</option></select></div>`);
            const select = $('#' + selectId);
            
            // Add Special Roles
            select.append('<option value="class_teacher">Class Teacher</option>');
            select.append('<option value="coordinator">Coordinator</option>');
            select.append('<option value="head_of_department">Head Of Department</option>');
            
            // Add Regular Roles
            roles.forEach(r => select.append($('<option>', { value: r.id, text: r.name || r.role_name })));
            
            if (selectedRoles[i - 1]) select.val(selectedRoles[i - 1]);
        }
        updateRoleSelectOptions('.edit-paper-approval-role-select');
        $('.edit-paper-approval-role-select').trigger('change');
    }

    // Edit exam dynamic handlers
    $('#edit_exam_category').on('change', function() {
        applyEditCategorySettings($(this).val());
    });

    $('#edit_test_type').on('change', function() {
        applyEditTestTypeSettings($(this).val());
    });

    $('#edit_use_except').on('change', function() {
        if ($(this).is(':checked')) {
            $('#edit_except_classes_selection').show();
        } else {
            $('#edit_except_classes_selection').hide();
            $('.edit-except-class-checkbox').prop('checked', false);
        }
    });

    // Handle use_result_approval change in edit modal
    $('#edit_use_result_approval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#edit_result_approval_fields').show();
            $('#edit_number_of_approvals').prop('required', true);
            generateEditApprovalRoleSelects();
        } else {
            $('#edit_result_approval_fields').hide();
            $('#edit_number_of_approvals').prop('required', false);
            $('#edit_approval_role_selections').empty();
        }
    });

    // Handle use_paper_approval change in edit modal
    $('#edit_use_paper_approval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#edit_paper_approval_fields').show();
            $('#edit_number_of_paper_approvals').prop('required', true);
            generateEditPaperApprovalRoleSelects();
        } else {
            $('#edit_paper_approval_fields').hide();
            $('#edit_number_of_paper_approvals').prop('required', false);
            $('#edit_paper_approval_role_selections').empty();
        }
    });

    // Handle number of approvals change in edit
    $('#edit_number_of_approvals').on('change', function() {
        if (parseInt($(this).val()) > 0) generateEditApprovalRoleSelects();
    });

    $('#edit_number_of_paper_approvals').on('change', function() {
        if (parseInt($(this).val()) > 0) generateEditPaperApprovalRoleSelects();
    });

    $('#edit_number_of_approvals').on('change', function() {
        const numApprovals = parseInt($(this).val()) || 0;
        if (numApprovals > maxApprovals) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Number',
                text: 'Number of approvals cannot exceed ' + maxApprovals + ' (number of available roles + 2 special roles).'
            });
            $(this).val(maxApprovals);
        }

        const currentSelections = [];
        $('.edit-approval-role-select').each(function() {
            const val = $(this).val();
            if (val) {
                currentSelections.push(val);
            }
        });
        generateEditApprovalRoleSelects(currentSelections);
    });

    // Edit exam
    window.editExam = function(examID) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/get_exam/' + examID,
            method: 'GET',
            success: function(response) {
                if (response.success && response.exam) {
                    const exam = response.exam;

                    resetEditFormState();
                    hallRowId = 0;

                    $('#edit_examID').val(exam.examID);
                    $('#edit_allow_no_format').prop('checked', exam.allow_no_format == 1);
                    $('#edit_allow_no_paper').prop('checked', exam.allow_no_paper == 1);
                    $('#edit_exam_category').val(exam.exam_category || '');
                    applyEditCategorySettings(exam.exam_category || '');

                    $('#edit_term').val(exam.term || '');
                    $('#edit_start_date').val(exam.start_date || '');
                    $('#edit_end_date').val(exam.end_date || '');
                    $('#edit_year').val(exam.year || new Date().getFullYear());
                    $('#edit_details').val(exam.details || '');

                    if (exam.exam_category === 'school_exams') {
                        $('#edit_exam_name_type').val(exam.exam_name_type || exam.exam_name || '');
                    } else if (exam.exam_category === 'test') {
                        $('#edit_test_type').val(exam.test_type || '');
                        applyEditTestTypeSettings(exam.test_type || '');

                        if (exam.test_type === 'other_test') {
                            $('#edit_exam_name').val(exam.exam_name || '');
                        }
                    } else if (exam.exam_category === 'special_exams') {
                        $('#edit_exam_name').val(exam.exam_name || '');
                    }

                    // Load halls
                    if (Array.isArray(exam.exam_halls) && exam.exam_halls.length > 0) {
                        exam.exam_halls.forEach(h => {
                            renderHallRow('edit', {
                                hall_name: h.hall_name,
                                classID: h.classID,
                                capacity: h.capacity,
                                gender_allowed: h.gender_allowed,
                            });
                        });
                    } else {
                        resetHalls('edit');
                    }

                    if (exam.exam_category === 'school_exams' || exam.exam_category === 'test') {
                        $('#edit_student_shifting_status_group').show();
                        $('#edit_student_shifting_status').prop('required', true).val(exam.student_shifting_status || 'none');
                    } else {
                        $('#edit_student_shifting_status_group').hide();
                        $('#edit_student_shifting_status').prop('required', false).val('none');
                    }

                    $('.edit-except-class-checkbox').prop('checked', false);
                    if ((exam.exam_category === 'school_exams' || exam.exam_category === 'test') && Array.isArray(exam.except_class_ids) && exam.except_class_ids.length > 0) {
                        $('#edit_use_except').prop('checked', true);
                        $('#edit_except_classes_selection').show();
                        exam.except_class_ids.forEach(function(id) {
                            $('#edit_except_class_' + id).prop('checked', true);
                        });
                    }

                    $('.edit-include-class-checkbox').prop('checked', false);
                    if (exam.exam_category === 'special_exams' && Array.isArray(exam.include_class_ids) && exam.include_class_ids.length > 0) {
                        exam.include_class_ids.forEach(function(id) {
                            $('#edit_include_class_' + id).prop('checked', true);
                        });
                    }

                    $('#edit_enable_exam_attendance').prop('checked', !!exam.has_exam_attendance);

                    const approvalRoles = exam.approval_role_ids || [];
                    const approvalsCount = exam.number_of_approvals || approvalRoles.length || 1;
                    $('#edit_use_result_approval').prop('checked', approvalRoles.length > 0);
                    if (approvalRoles.length > 0) {
                        $('#edit_result_approval_fields').show();
                        $('#edit_number_of_approvals').val(approvalsCount);
                        generateEditApprovalRoleSelects(approvalRoles);
                    } else {
                        $('#edit_result_approval_fields').hide();
                        $('#edit_approval_role_selections').empty();
                    }

                    $('#editExamFormErrors').hide().html('');
                    $('#editExamModal').modal('show');
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load exam data',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to load exam',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        });
    };

    // View results
    window.viewResults = function(examID) {
        Swal.fire({
            title: 'View Results',
            text: 'This feature will be available soon!',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
        // Uncomment when route is ready:
        // window.location.href = '/view_exam_results/' + examID;
    };

    // Edit exam form submission
    $('#editExamForm').on('submit', function(e) {
        e.preventDefault();

        // Check update permission - New format: examination_update
        if (!hasPermission('examination_update')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to update examinations. You need examination_update permission.'
            });
            return false;
        }

        const examID = $('#edit_examID').val();
        const formData = new FormData(this);
        const examCategory = $('#edit_exam_category').val();
        const testType = $('#edit_test_type').val();

        formData.delete('exam_name_type');

        if (examCategory === 'school_exams') {
            const examNameType = $('#edit_exam_name_type').val();
            if (examNameType) {
                formData.set('exam_name', examNameType);
                formData.set('exam_name_type', examNameType);
            }
        } else if (examCategory === 'test') {
            formData.delete('exam_name_type');
            if (testType) {
                formData.set('test_type', testType);
            }

            if (testType === 'weekly_test') {
                formData.set('exam_name', 'Weekly Test');
                const year = $('#edit_year').val() || new Date().getFullYear();
                const validDate = year + '-01-01';
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                formData.set('is_weekly_test', '1');
            } else if (testType === 'monthly_test') {
                formData.set('exam_name', 'Monthly Test');
                const year = $('#edit_year').val() || new Date().getFullYear();
                const validDate = year + '-01-01';
                formData.set('start_date', validDate);
                formData.set('end_date', validDate);
                formData.set('is_monthly_test', '1');
            } else if (testType === 'other_test') {
                const otherTestName = $('#edit_exam_name').val();
                if (!otherTestName || otherTestName.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a test name for Other Test.'
                    });
                    return false;
                }
                formData.set('exam_name', otherTestName.trim());

                const startDate = $('#edit_start_date').val();
                const endDate = $('#edit_end_date').val();
                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select start date and end date for Other Test.'
                    });
                    return false;
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a test type.'
                });
                return false;
            }
        } else if (examCategory === 'special_exams') {
            formData.delete('exam_name_type');
        }

        const exceptClassIds = [];
        if ((examCategory === 'school_exams' || examCategory === 'test') && $('#edit_use_except').is(':checked')) {
            $('.edit-except-class-checkbox:checked').each(function() {
                exceptClassIds.push($(this).val());
            });
        }
        formData.delete('except_class_ids[]');
        exceptClassIds.forEach((id) => formData.append('except_class_ids[]', id));

        const includeClassIds = [];
        if (examCategory === 'special_exams') {
            $('.edit-include-class-checkbox:checked').each(function() {
                includeClassIds.push($(this).val());
            });
            if (includeClassIds.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one class for this special examination.'
                });
                return false;
            }
        }
        formData.delete('include_class_ids[]');
        includeClassIds.forEach((id) => formData.append('include_class_ids[]', id));

        if ($('#edit_use_result_approval').is(':checked')) {
            formData.set('use_result_approval', '1');
            const numApprovals = parseInt($('#edit_number_of_approvals').val()) || 0;
            formData.set('number_of_approvals', numApprovals);

            const approvalRoleIds = [];
            $('.edit-approval-role-select').each(function() {
                const roleId = $(this).val();
                if (roleId) {
                    approvalRoleIds.push(roleId);
                }
            });

            if (approvalRoleIds.length !== numApprovals) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a role for all approval steps.'
                });
                return false;
            }

            formData.delete('approval_role_ids[]');
            approvalRoleIds.forEach((id) => formData.append('approval_role_ids[]', id));
        } else {
            formData.delete('use_result_approval');
            formData.delete('number_of_approvals');
            formData.delete('approval_role_ids[]');
        }

        if ($('#edit_enable_exam_attendance').is(':checked')) {
            formData.set('enable_exam_attendance', '1');
        } else {
            formData.delete('enable_exam_attendance');
        }

        validateHallCapacities('edit').then(valid => {
            if (!valid) return;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the examination.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/update_examination/' + examID,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editExamModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.success || 'Examination updated successfully!',
                        icon: 'success',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    if (Object.keys(errors).length > 0) {
                        let errorHtml = '<ul>';
                        for (let field in errors) {
                            errorHtml += '<li>' + errors[field] + '</li>';
                        }
                        errorHtml += '</ul>';
                        $('#editExamFormErrors').html(errorHtml).show();
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to update exam',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });
    });

    loadExamPaperNotificationCounts();

    // Reset edit form when modal is closed
    $('#editExamModal').on('hidden.bs.modal', function() {
        resetEditFormState();
    });
    // Initialize tooltips
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"], .btn[title], .exam-widget-action[title]').tooltip();
    }

    // Re-initialize tooltips after AJAX content updates
    $(document).ajaxComplete(function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip === 'function') {
            $('[data-toggle="tooltip"], .btn[title], .exam-widget-action[title]').tooltip();
        }
    });

    // View Exam Papers
    // View Exam Halls
    $(document).on('click', '.view-exam-halls-btn', function(e) {
        e.preventDefault();
        const examID = $(this).data('exam-id');
        const examName = $(this).data('exam-name');
        
        if (!examID) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Exam ID not found.'
            });
            return;
        }
        
        // Show modal
        $('#viewExamHallsModalLabel').html('<i class="bi bi-building"></i> Exam Halls - ' + examName);
        $('#viewExamHallsModal').modal('show');
        $('#examHallsContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        // Load exam halls
        $.ajax({
            url: '/admin/get-exam-halls/' + examID,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.halls) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-hover"><thead class="thead-light"><tr><th>Hall Name</th><th>Class</th><th>Capacity</th><th>Gender Allowed</th><th>Supervisors</th></tr></thead><tbody>';
                    
                    if (response.halls.length === 0) {
                        html += '<tr><td colspan="5" class="text-center text-muted py-4">No exam halls found for this examination.</td></tr>';
                    } else {
                        response.halls.forEach(function(hall) {
                            const supervisors = hall.supervisors && hall.supervisors.length > 0 
                                ? hall.supervisors.map(s => s.teacher_name).join(', ') 
                                : '<span class="text-muted">No supervisors assigned</span>';
                            
                            html += `<tr>
                                <td><strong>${hall.hall_name || 'N/A'}</strong></td>
                                <td>${hall.class_name || 'N/A'}</td>
                                <td>${hall.capacity || 0}</td>
                                <td><span class="badge badge-info">${hall.gender_allowed ? hall.gender_allowed.charAt(0).toUpperCase() + hall.gender_allowed.slice(1) : 'N/A'}</span></td>
                                <td>${supervisors}</td>
                            </tr>`;
                        });
                    }
                    
                    html += '</tbody></table></div>';
                    $('#examHallsContent').html(html);
                } else {
                    $('#examHallsContent').html('<div class="alert alert-info text-center">No exam halls found for this examination.</div>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load exam halls.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#examHallsContent').html(`<div class="alert alert-danger text-center">${errorMsg}</div>`);
            }
        });
    });
    
    $(document).on('click', '.view-exam-papers-btn', function(e) {
        e.preventDefault();

        // Check permission
        if (!hasPermission('view_exam_papers')) {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to view exam papers.'
            });
            return;
        }

        const examID = $(this).data('exam-id');
        const examName = $(this).data('exam-name');
        viewExamPapers(examID, examName);
    });

    // View Exam Details
    $(document).on('click', '.view-exam-details-btn', function(e) {
        e.preventDefault();
        var examID = $(this).data('exam-id');
        var examName = $(this).data('exam-name');

        // Show modal
        $('#viewExamDetailsModal').modal('show');

        // Reset content
        $('#examDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3">Loading examination details...</p>
            </div>
        `);

        // Fetch exam details
        $.ajax({
            url: '/get_exam_details/' + examID,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Check if this is a Weekly Test or Monthly Test
                    const isWeeklyTest = response.exam.exam_name === 'Weekly Test' || response.exam.start_date === 'every_week' || response.exam.end_date === 'every_week';
                    const isMonthlyTest = response.exam.exam_name === 'Monthly Test' || response.exam.start_date === 'every_month' || response.exam.end_date === 'every_month';
                    
                    // Format dates or use text for weekly/monthly tests
                    let startDateDisplay = '';
                    let endDateDisplay = '';
                    if (isWeeklyTest) {
                        startDateDisplay = 'Every week';
                        endDateDisplay = 'Every week';
                    } else if (isMonthlyTest) {
                        startDateDisplay = 'Every month in a term';
                        endDateDisplay = 'Every month in a term';
                    } else {
                        try {
                            startDateDisplay = new Date(response.exam.start_date).toLocaleDateString();
                            endDateDisplay = new Date(response.exam.end_date).toLocaleDateString();
                        } catch (e) {
                            startDateDisplay = 'Not available';
                            endDateDisplay = 'Not available';
                        }
                    }
                    
                    var html = `
                        <div class="mb-3">
                            <h4 class="mb-3" style="color: #940000; font-weight: 700; font-size: 1.5rem;">
                                <i class="bi bi-file-earmark-text"></i> ${response.exam.exam_name}
                            </h4>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Exam Type</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${response.exam.exam_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Start Date</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${startDateDisplay}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">End Date</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${endDateDisplay}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded">
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Total Classes</small>
                                        <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">${response.total_classes}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="card shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c50000 100%); border: none;">
                                        <div class="card-body text-center p-3 text-white">
                                            <i class="bi bi-building" style="font-size: 1.5rem; opacity: 0.9; margin-bottom: 0.3rem;"></i>
                                            <h4 class="mb-1" style="font-weight: 700; font-size: 1.5rem;">${response.total_classes}</h4>
                                            <small style="font-weight: 500; font-size: 0.85rem;">Classes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c50000 100%); border: none;">
                                        <div class="card-body text-center p-3 text-white">
                                            <i class="bi bi-people" style="font-size: 1.5rem; opacity: 0.9; margin-bottom: 0.3rem;"></i>
                                            <h4 class="mb-1" style="font-weight: 700; font-size: 1.5rem;">${response.total_students}</h4>
                                            <small style="font-weight: 500; font-size: 0.85rem;">Total Students</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm" style="background: linear-gradient(135deg, #940000 0%, #c50000 100%); border: none;">
                                        <div class="card-body text-center p-3 text-white">
                                            <i class="bi bi-book" style="font-size: 1.5rem; opacity: 0.9; margin-bottom: 0.3rem;"></i>
                                            <h4 class="mb-1" style="font-weight: 700; font-size: 1.5rem;">${response.total_subjects}</h4>
                                            <small style="font-weight: 500; font-size: 0.85rem;">Total Subjects</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <h5 class="mb-3" style="color: #940000; font-size: 1.25rem;"><i class="bi bi-list-ul"></i> Classes and Subjects</h5>
                        <div class="accordion" id="examClassesAccordion">
                    `;

                    response.classes.forEach(function(classItem, index) {
                        var subjectsList = '';
                        classItem.subjects.forEach(function(subject) {
                            var teacherName = '';
                            if (subject.teacher_first_name && subject.teacher_last_name) {
                                var middleName = subject.teacher_middle_name ? ' ' + subject.teacher_middle_name : '';
                                teacherName = subject.teacher_first_name + middleName + ' ' + subject.teacher_last_name;
                            } else {
                                teacherName = 'Not Assigned';
                            }

                            subjectsList += `
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left: 4px solid #940000; border-top: 1px solid #dee2e6; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="mr-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #940000 0%, #c50000 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-book text-white" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1" style="color: #940000; font-weight: 600;">
                                                        <strong>${subject.subject_name}</strong>
                                                    </h6>
                                                    ${subject.subject_code ? '<p class="mb-0"><small class="text-muted"><i class="bi bi-tag"></i> ' + subject.subject_code + '</small></p>' : ''}
                                                </div>
                                            </div>
                                            <hr class="my-2" style="border-color: #e9ecef;">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle text-primary mr-2" style="font-size: 1.2rem;"></i>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Teacher</small>
                                                    <strong style="color: #940000; font-size: 0.9rem;">${teacherName}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light" id="heading${index}">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed p-3 toggle-subjects-btn" type="button" data-toggle="collapse" data-target="#collapse${index}" aria-expanded="false" aria-controls="collapse${index}" style="text-decoration: none; font-size: 1.1rem;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="bi bi-building" style="color: #940000; font-size: 1.3rem; margin-right: 0.5rem;"></i>
                                                    <strong style="color: #940000;">${classItem.class_name}</strong> -
                                                    <span class="text-dark">${classItem.subclass_name}</span>
                                                    ${classItem.stream_code ? '<span class="badge ml-2" style="background-color: #940000; color: white; padding: 0.25rem 0.5rem;">' + classItem.stream_code + '</span>' : ''}
                                                    <i class="bi bi-chevron-down ml-2 toggle-icon" style="color: #940000; font-size: 1rem; transition: transform 0.3s;"></i>
                                                </span>
                                                <div>
                                                    <span class="badge mr-2" style="background-color: #940000; color: white; padding: 0.4rem 0.8rem; font-size: 0.9rem;"><i class="bi bi-book"></i> ${classItem.subjects_count} Subjects</span>
                                                    <span class="badge" style="background-color: #940000; color: white; padding: 0.4rem 0.8rem; font-size: 0.9rem;"><i class="bi bi-people"></i> ${classItem.students_count} Students</span>
                                                </div>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapse${index}" class="collapse" aria-labelledby="heading${index}" data-parent="#examClassesAccordion">
                                    <div class="card-body p-4">
                                        <div class="mb-3 p-3 rounded" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #940000;">
                                            <h6 class="mb-0">
                                                <i class="bi bi-person-circle" style="color: #940000; font-size: 1.2rem;"></i>
                                                <span style="color: #6c757d;">Class Teacher:</span>
                                                <strong style="color: #940000;">${classItem.teacher_name}</strong>
                                            </h6>
                                        </div>
                                        <hr style="border-color: #dee2e6;">
                                        <h5 class="mb-3" style="color: #940000;">
                                            <i class="bi bi-book"></i> Subjects (${classItem.subjects_count})
                                        </h5>
                                        <div class="row">
                                            ${subjectsList || '<div class="col-12"><p class="text-muted">No subjects found</p></div>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;
                    $('#examDetailsContent').html(html);

                    // Handle toggle icon rotation
                    $('#examClassesAccordion').on('show.bs.collapse', function (e) {
                        $(e.target).prev().find('.toggle-icon').removeClass('bi-chevron-down').addClass('bi-chevron-up');
                    });

                    $('#examClassesAccordion').on('hide.bs.collapse', function (e) {
                        $(e.target).prev().find('.toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
                    });
                } else {
                    $('#examDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Failed to load examination details.
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Failed to load examination details.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#examDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${errorMsg}
                    </div>
                `);
            }
    });
    });
});



// Global functions for status management (outside document.ready for onclick handlers)
window.handleStatusChange = function(examID, status, permission) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    // Close dropdown
    $('.dropdown-menu').removeClass('show');
        $('.dropdown-toggle').attr('aria-expanded', 'false');

    // Map status to display names with better terminology
    var statusNames = {
        'not_allowed': 'Not Allow Results Entry',
        'allowed': 'Allow Results Entry',
        'under_review': 'Set Results Under Review',
        'approved': 'Approve for Public View'
    };

    // Map status to descriptive messages
    var statusMessages = {
        'not_allowed': 'This will not allow teachers to enter results',
        'allowed': 'This will allow teachers to add or edit results',
        'under_review': 'This will make the results under review',
        'approved': 'This will allow the results to be seen by public, parents and students'
    };

    var statusName = statusNames[status] || status;
    var statusMessage = statusMessages[status] || '';
    window.updateResultsStatus(examID, permission, status, statusName, statusMessage);
};

window.updateResultsStatus = function(examID, permission, status, statusName, statusMessage) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 is not loaded. Please refresh the page.');
        console.error('SweetAlert2 is not available');
        return;
    }

    // Build confirmation message
    var confirmText = 'Are you sure you want to ' + statusName.toLowerCase() + ' for this examination?';
    if (statusMessage) {
        confirmText += '\n\n' + statusMessage;
    }

    Swal.fire({
        title: 'Confirm Status Change',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading with special message for approved status (SMS sending)
            var loadingTitle = 'Updating...';
            var loadingText = 'Please wait while we update the status.';
            if (status === 'approved') {
                loadingTitle = 'Sending Results to Parents';
                loadingText = 'Sending results to parents via SMS. This will take a few minutes to complete. Please wait.';
            }

            Swal.fire({
                title: loadingTitle,
                text: loadingText,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ url("update_results_status") }}/' + examID,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    permission: permission,
                    status: status
                },
                dataType: 'json',
                       success: function(response) {
                           if (response.success) {
                               var message = response.message || 'Results status updated to "' + statusName + '" successfully.';
                               if (response.transferred_count && response.transferred_count > 0) {
                                   message += '\n\n' + response.transferred_count + ' student(s) transferred automatically based on their results.';
                               }
                               Swal.fire({
                                   title: 'Success!',
                                   text: message,
                                   icon: 'success',
                                   confirmButtonColor: '#940000'
                               }).then(function() {
                                   location.reload();
                               });
                           } else {
                               Swal.fire({
                                   title: 'Error!',
                                   text: response.message || 'Failed to update results status.',
                                   icon: 'error',
                                   confirmButtonColor: '#940000'
                               });
                           }
                       },
                error: function(xhr) {
                    var errorMsg = 'Failed to update results status.';
                    if (xhr.status === 403) {
                        errorMsg = 'You are not allowed to perform this action.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMsg = 'Examination not found.';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Server error occurred. Please try again.';
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }
    });
};
</script>

<!-- View Exam Papers Modal -->
<div class="modal fade" id="viewExamPapersModal" tabindex="-1" role="dialog" aria-labelledby="viewExamPapersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%; width: 95%; margin: 1.75rem auto;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h4 class="modal-title" id="viewExamPapersModalLabel">
                    <i class="bi bi-file-earmark-text"></i> Exam Papers
                    <span class="badge badge-light ml-2 d-none" id="examPaperModalCount"></span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto; padding: 2rem;">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="search_exam_papers" placeholder="Search by subject, class, or teacher...">
                    </div>
                    <div class="col-md-4" id="week_filter_container" style="display: none;">
                        <input type="hidden" id="filter_exam_paper_week" value="">
                        <div class="d-flex justify-content-between align-items-center bg-white rounded border p-1" style="height: 38px;">
                            <button class="btn btn-sm btn-light text-primary-custom font-weight-bold" id="prev_week_btn" title="Previous Week">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="font-weight-bold small text-center flex-grow-1 text-truncate px-2" id="current_week_display" style="font-size: 0.85rem;">
                                Loading...
                            </span>
                            <button class="btn btn-sm btn-light text-primary-custom font-weight-bold" id="next_week_btn" title="Next Week">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filter_exam_paper_status">
                            <option value="">All Status</option>
                            <option value="wait_approval">Waiting Approval</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div id="examPapersContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3">Loading exam papers...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Exam Paper Modal -->
<div class="modal fade" id="approveRejectExamPaperModal" tabindex="-1" role="dialog" aria-labelledby="approveRejectExamPaperModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="approveRejectExamPaperModalLabel">
                    <i class="bi bi-check-circle"></i> Approve/Reject Exam Paper
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="approveRejectExamPaperForm">
                <input type="hidden" id="approve_reject_paper_id">
                <input type="hidden" id="approve_reject_action" name="action">
                <div class="modal-body">
                    <div class="form-group" id="approval_comment_group">
                        <label for="approval_comment" id="approval_comment_label">Approval Comment (Optional)</label>
                        <textarea class="form-control" id="approval_comment" name="approval_comment" rows="4" placeholder="Enter approval comment (optional)..."></textarea>
                    </div>
                    <div class="form-group" id="rejection_reason_group" style="display: none;">
                        <label for="rejection_reason_text">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason_text" name="rejection_reason" rows="4" placeholder="Enter rejection reason..." required></textarea>
                        <small class="text-muted">Please provide a reason for rejecting this exam paper.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitApproveReject">
                        <i class="bi bi-check-circle"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Exam Halls Modal -->
<div class="modal fade" id="viewExamHallsModal" tabindex="-1" role="dialog" aria-labelledby="viewExamHallsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h4 class="modal-title" id="viewExamHallsModalLabel">
                    <i class="bi bi-building"></i> Exam Halls
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="examHallsContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Exam Paper Modal -->
<div class="modal fade" id="viewExamPaperModal" tabindex="-1" role="dialog" aria-labelledby="viewExamPaperModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
<h5 class="modal-title" id="viewExamPaperModalLabel">
                    <i class="bi bi-file-earmark-text"></i> View Exam Paper
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewExamPaperContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View Exam Papers - Make it globally accessible
window.viewExamPapers = function(examID, examName) {
    if (typeof jQuery === 'undefined') {
        alert('jQuery is not loaded. Please refresh the page.');
        return;
    }
    var $ = jQuery;

            // Check permission
            if (typeof window.hasPermission === 'function') {
                if (!window.hasPermission('view_exam_papers')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Access Denied',
                            text: 'You do not have permission to view exam papers.'
                        });
                    } else {
                        alert('You do not have permission to view exam papers.');
                    }
                    return;
                }
            } else {
                // Fallback: check userType directly
                if (typeof window.userType === 'undefined' || window.userType !== 'Admin') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Access Denied',
                            text: 'You do not have permission to view exam papers.'
                        });
                    } else {
                        alert('You do not have permission to view exam papers.');
                    }
                    return;
                }
            }

            // Check if modal exists
            if ($('#viewExamPapersModal').length === 0) {
                console.error('View Exam Papers Modal not found');
                alert('Error: Modal not found. Please refresh the page.');
                return;
            }

            $('#viewExamPapersModalLabel').html('<i class="bi bi-file-earmark-text"></i> Exam Papers - ' + examName + ' <span class="badge badge-light ml-2 d-none" id="examPaperModalCount"></span>');
            $('#viewExamPapersModal').modal('show');

            updateExamPaperModalCount();
    markExamPaperNotificationsReadForExam(examID);

            // Reset content
            $('#examPapersContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Loading exam papers...</p>
                </div>
            `);

            // Store exam ID for filtering
            $('#viewExamPapersModal').data('exam-id', examID);

            // Load exam papers
            if (typeof loadExamPapers === 'function') {
                loadExamPapers(examID);
            } else {
                console.error('loadExamPapers function is not defined');
                $('#examPapersContent').html('<div class="alert alert-danger">Error: Failed to load exam papers. Please refresh the page.</div>');
            }

};

window.updateExamPaperModalCount = function() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    $.get('{{ route("admin.exam_paper_notifications_count") }}', function(response) {
        if (!response || response.success !== true) {
            return;
        }
        const count = parseInt(response.count || 0, 10);
        const $badge = $('#examPaperModalCount');
        if (count > 0) {
            $badge.text(count + ' new').removeClass('d-none');
        } else {
            $badge.addClass('d-none').text('');
        }
    });
}

window.applyExamPaperNotificationCounts = function(counts) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    $('.exam-paper-count').each(function() {
        const $this = $(this);
        const examId = String($this.data('exam-id'));
        const count = parseInt(counts[examId] || 0, 10);
        if (count > 0) {
            $this.text(count).removeClass('d-none');
        } else {
            $this.addClass('d-none').text('');
        }
    });
}

window.loadExamPaperNotificationCounts = function() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    $.get('{{ route("admin.exam_paper_notifications_by_exam") }}', function(response) {
        if (!response || response.success !== true) {
            return;
        }
        applyExamPaperNotificationCounts(response.counts || {});
    });
}

window.markExamPaperNotificationsReadForExam = function(examID) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    $.ajax({
        url: `/admin/mark-exam-paper-notifications-read/${examID}`,
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function() {
            loadExamPaperNotificationCounts();
        }
    });
}

window.loadExamPapers = function(examID, search = '', status = '', week = '') {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    $.ajax({
        url: '/get_exam_papers/' + examID,
        method: 'GET',
        data: {
            search: search,
            status: status,
            week: week
        },
        success: function(response) {
            if (response.success && response.exam_papers) {
                // Handle week filter visibility and population
                // Handle week filter visibility and population
                if (response.is_weekly_test || response.is_monthly_test) {
                    $('#week_filter_container').show();
                    
                    // Initialize or update global weeks data
                    window.examPaperWeeks = response.available_weeks || [];
                    
                    // Logic to set default week if not set (Initial Load)
                    if (!week && window.examPaperWeeks.length > 0) {
                        // Find current week index
                        let idx = window.examPaperWeeks.findIndex(w => w.is_current);
                        if (idx === -1) {
                            // If not found, default to first week available? Or the last uploaded one?
                            // Let's default to the first one available in the list 
                            idx = 0;
                        }
                        
                        window.currentWeekIndex = idx;
                        const defaultWeek = window.examPaperWeeks[idx].week;
                        
                        // Reload with specific week filter
                        loadExamPapers(examID, search, status, defaultWeek);
                        return; // Stop processing this response as we are reloading
                    } 
                    else if (week) {
                        // Sync index with currently loaded week
                        window.currentWeekIndex = window.examPaperWeeks.findIndex(w => w.week === week);
                    }
                    
                    updateWeekNavigatorUI();

                } else {
                    $('#week_filter_container').hide();
                }

                let html = '';

                if (response.exam_papers.length === 0) {
                    html = '<div class="alert alert-info text-center"><i class="bi bi-info-circle"></i> No exam papers found for this examination.</div>';
                } else {
                    // Group exam papers by subclass
                    const groupedBySubclass = {};

                    response.exam_papers.forEach(function(paper) {
                        const subclassID = paper.class_subject?.subclass?.subclassID || 'no_subclass';
                        const subclassName = paper.class_subject?.subclass?.subclass_name || 'No Subclass';
                        const className = paper.class_subject?.class?.class_name || 'N/A';
                        const displayName = subclassName !== 'No Subclass' ? subclassName : className;

                        if (!groupedBySubclass[subclassID]) {
                            groupedBySubclass[subclassID] = {
                                subclassID: subclassID,
                                subclassName: subclassName,
                                className: className,
                                displayName: displayName,
                                papers: []
                            };
                        }
                        groupedBySubclass[subclassID].papers.push(paper);
                    });

                    // Display grouped by subclass
                    Object.keys(groupedBySubclass).forEach(function(subclassID) {
                        const group = groupedBySubclass[subclassID];

                        // Calculate statistics
                        const totalPapers = group.papers.length;
                        const totalSubjects = new Set(group.papers.map(p => p.class_subjectID)).size;
                        const approvedCount = group.papers.filter(p => p.status === 'approved').length;
                        const pendingCount = group.papers.filter(p => p.status === 'wait_approval').length;
                        const rejectedCount = group.papers.filter(p => p.status === 'rejected').length;

                        // Subclass header with statistics
                        html += `
                            <div class="card mb-4 border-primary-custom">
                                <div class="card-header bg-primary-custom text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-folder"></i> ${group.className} ${group.subclassName}
                                        </h5>
                                        <button class="btn btn-sm btn-light toggle-subclass-btn" data-subclass-id="${subclassID}">
                                            <i class="bi bi-chevron-down"></i> View Papers
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="card bg-info h-100" style="background-color: #17a2b8 !important;">
                                                <div class="card-body text-center p-2 p-md-3" style="color: #ffffff !important;">
                                                    <h4 style="color: #ffffff !important; font-weight: bold; font-size: 1.2rem;">${totalPapers}</h4>
                                                    <p class="mb-0 small" style="color: #ffffff !important; font-weight: 500;">Total Papers</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="card bg-primary h-100" style="background-color: #007bff !important;">
                                                <div class="card-body text-center p-2 p-md-3" style="color: #ffffff !important;">
                                                    <h4 style="color: #ffffff !important; font-weight: bold; font-size: 1.2rem;">${totalSubjects}</h4>
                                                    <p class="mb-0 small" style="color: #ffffff !important; font-weight: 500;">Subjects</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="card bg-success h-100" style="background-color: #28a745 !important;">
                                                <div class="card-body text-center p-2 p-md-3" style="color: #ffffff !important;">
                                                    <h4 style="color: #ffffff !important; font-weight: bold; font-size: 1.2rem;">${approvedCount}</h4>
                                                    <p class="mb-0 small" style="color: #ffffff !important; font-weight: 500;">Approved</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="card bg-warning h-100" style="background-color: #ffc107 !important;">
                                                <div class="card-body text-center p-2 p-md-3" style="color: #000000 !important;">
                                                    <h4 style="color: #000000 !important; font-weight: bold; font-size: 1.2rem;">${pendingCount}</h4>
                                                    <p class="mb-0 small" style="color: #000000 !important; font-weight: 500;">Pending</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="subclass-papers" id="subclass-papers-${subclassID}" style="display: none;">
                        `;

                        // Display papers for this subclass
                        function renderQuestionFormats(paper) {
                            if (!paper.questions || paper.questions.length === 0) {
                                return '<div class="text-muted small">No question formats provided.</div>';
                            }

                            const optionalRanges = paper.optional_ranges || [];
                            let optionalSummary = '';
                            if (optionalRanges.length > 0) {
                                const rangeLabels = optionalRanges.map(r => `Opt ${r.range_number}: ${r.total_marks}`).join(' | ');
                                optionalSummary = `<div class="small text-muted mb-2"><strong>Optional Totals:</strong> ${rangeLabels}</div>`;
                            }

                            const questionsHtml = paper.questions
                                .sort((a, b) => (a.question_number || 0) - (b.question_number || 0))
                                .map(q => {
                                    const optLabel = q.is_optional ? ` <span class="badge badge-warning">Opt ${q.optional_range_number || ''}</span>` : '';
                                    return `
                                        <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                                            <div>
                                                <strong>Qn ${q.question_number}:</strong> ${q.question_description}${optLabel}
                                            </div>
                                            <div class="text-primary"><strong>${q.marks}</strong></div>
                                        </div>
                                    `;
                                }).join('');

                            return `
                                <div class="mt-3 p-3 border rounded bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="text-primary-custom"><i class="bi bi-list-check"></i> Question Formats</strong>
                                        <span class="badge badge-info">Total 100</span>
                                    </div>
                                    ${optionalSummary}
                                    ${questionsHtml}
                                </div>
                            `;
                        }

                        function renderApprovalChainMap(paper) {
                            const chain = paper.full_chain || [];
                            if (chain.length === 0) return '';
                            
                            let html = '<div class="approval-chain-wrapper mt-4 mb-4 p-3 border rounded" style="background-color: #fcfcfc;">';
                            html += '<h6 class="text-muted small font-weight-bold mb-3"><i class="bi bi-diagram-3"></i> Approval Chain Tracking</h6>';
                            html += '<div class="d-flex justify-content-between align-items-start position-relative px-2 overflow-auto">';
                            
                            // Background connection line
                            html += '<div class="position-absolute" style="top: 15px; left: 35px; right: 35px; height: 3px; background-color: #dee2e6; z-index: 1;"></div>';
                            
                            chain.forEach((step, index) => {
                                let icon = 'bi-circle';
                                let color = '#adb5bd'; // Grey
                                let statusText = 'Waiting';
                                let bg = '#ffffff';
                                let border = '#dee2e6';
                                
                                if (step.status === 'approved') {
                                    icon = 'bi-check-circle-fill';
                                    color = '#28a745'; // Green
                                    statusText = 'Complete';
                                    bg = '#f1f8f3';
                                    border = '#28a745';
                                } else if (step.status === 'pending') {
                                    icon = 'bi-clock-history';
                                    color = '#ffc107'; // Yellow/Orange
                                    statusText = 'Pending';
                                    bg = '#fffdf5';
                                    border = '#ffc107';
                                } else if (step.status === 'rejected') {
                                    icon = 'bi-x-circle-fill';
                                    color = '#dc3545'; // Red
                                    statusText = 'Rejected';
                                    bg = '#fff5f6';
                                    border = '#dc3545';
                                }
                                
                                html += `
                                    <div class="text-center" style="z-index: 2; min-width: 80px; flex: 1;">
                                        <div class="d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 32px; height: 32px; border-radius: 50%; background-color: ${bg}; border: 2px solid ${border};">
                                            <i class="bi ${icon}" style="color: ${color}; font-size: 1.1rem;"></i>
                                        </div>
                                        <div class="small font-weight-bold mb-1" style="font-size: 0.7rem; color: #495057; line-height: 1.2; min-height: 2.4em;">${step.name}</div>
                                        <span class="badge" style="font-size: 0.6rem; padding: 0.2rem 0.4rem; color: ${color}; background-color: ${bg}; border: 1px solid ${border};">${statusText}</span>
                                    </div>
                                `;
                            });
                            
                            html += '</div></div>';
                            return html;
                        }

                        group.papers.forEach(function(paper) {
                            const subjectName = paper.class_subject?.subject?.subject_name || 'N/A';
                            const teacherName = paper.teacher ? (paper.teacher.first_name + ' ' + paper.teacher.last_name) : 'N/A';
                            const className = paper.class_subject?.class?.class_name || 'N/A';
                            const subclassName = paper.class_subject?.subclass?.subclass_name || 'N/A';
                            const paperId = paper.exam_paperID;
                            const statusBadge = paper.status === 'approved'
                                ? '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>'
                                : paper.status === 'rejected'
                                    ? '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>'
                                    : '<span class="badge badge-warning text-dark"><i class="bi bi-clock-history"></i> Pending</span>';

                        html += `
                            <div class="card mb-2 exam-paper-item" data-paper-id="${paperId}" style="border: 1.5px solid #940000;">
                                <!-- Collapsed Header (always visible) -->
                                <div class="card-body py-2 px-3" style="background: #fff;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2" style="flex: 1; min-width: 0;">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                                 style="width: 36px; height: 36px; background: #f8e8e8; border: 1.5px solid #940000;">
                                                <i class="bi bi-file-earmark-text" style="color: #940000; font-size: 1rem;"></i>
                                            </div>
                                            <div style="min-width: 0;">
                                                <div class="fw-bold text-truncate" style="font-size: 0.95rem; color: #222; max-width: 220px;">
                                                    ${subjectName}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.78rem;">
                                                    <i class="bi bi-person"></i> ${teacherName}
                                                    &nbsp;|&nbsp; ${className} ${subclassName}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            ${statusBadge}
                                            <button class="btn btn-sm toggle-paper-detail-btn"
                                                    data-paper-id="${paperId}"
                                                    style="background: #940000; color: #fff; border-radius: 20px; padding: 0.25rem 0.9rem; font-size: 0.82rem; font-weight: 600; border: none;">
                                                <i class="bi bi-eye" id="toggle-icon-${paperId}"></i>
                                                <span id="toggle-label-${paperId}">View</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expandable Detail Section (hidden by default) -->
                                <div class="paper-detail-section" id="paper-detail-${paperId}" style="display: none; border-top: 1px solid #f1d7d7; background: #fafafa;">
                                    <div class="card-body pt-3">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <p class="mb-2" style="font-size: 0.88rem;">
                                                    <strong>Subject & Class:</strong> ${subjectName} - ${className} ${subclassName}<br>
                                                    <strong>Teacher:</strong> ${teacherName}<br>
                                                    ${paper.test_week ? `<strong>Week:</strong> ${paper.test_week}${paper.test_week_range ? ` (${paper.test_week_range})` : ''}<br>` : ''}
                                                    ${paper.test_date ? `<strong>Test Date:</strong> ${new Date(paper.test_date).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})}<br>` : ''}
                                                    <strong>Type:</strong> <span class="badge badge-info">${paper.upload_type === 'upload' ? 'File Upload' : 'Created'}</span><br>
                                                    <strong>Approval Stage:</strong> <span class="badge badge-dark">${paper.chain_progress || 'N/A'}</span><br>
                                                    <strong>Status:</strong> <span class="badge ${paper.status === 'approved' ? 'badge-success' : (paper.status === 'rejected' ? 'badge-danger' : 'badge-warning')}">${paper.detailed_status || paper.status}</span>
                                                </p>
                                                ${paper.rejection_reason ? `
                                                    <div class="alert alert-danger mt-2 py-2">
                                                        <strong><i class="bi bi-exclamation-triangle"></i> Rejection Reason / Note:</strong>
                                                        <p class="mb-0">${paper.rejection_reason}</p>
                                                    </div>
                                                ` : ''}
                                                ${paper.approval_comment ? `
                                                    <div class="alert alert-success mt-2 py-2">
                                                        <strong><i class="bi bi-check-circle"></i> Approval Comment:</strong>
                                                        <p class="mb-0">${paper.approval_comment}</p>
                                                    </div>
                                                ` : ''}
                                                <p class="mb-2 text-muted">
                                                    <small><i class="bi bi-calendar"></i> Uploaded: ${new Date(paper.created_at).toLocaleString()}</small>
                                                </p>
                                                ${renderApprovalChainMap(paper)}
                                                ${paper.can_view_content ? `
                                                    ${renderQuestionFormats(paper)}
                                                ` : `
                                                    <div class="mt-3 p-3 border rounded bg-light text-center">
                                                        <i class="bi bi-shield-lock" style="font-size: 1.5rem; color: #6c757d;"></i>
                                                        <p class="mb-0 mt-2 text-muted small">
                                                            Paper content is hidden. It will be visible to you once it reaches the <strong>Admin Approval</strong> stage.
                                                        </p>
                                                    </div>
                                                `}
                                            </div>
                                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                                ${hasPermission('view_exam_papers') && paper.can_view_content ? `
                                                    <button class="btn btn-sm btn-info view-paper-btn mb-2 w-100 w-md-auto" data-paper-id="${paperId}" data-exam-id="${examID}" title="View Exam Paper">
                                                        <i class="bi bi-eye"></i> View Paper
                                                    </button><br class="d-none d-md-block">
                                                ` : ''}
                                                ${paper.upload_type === 'upload' && paper.file_path && paper.can_view_content ? `
                                                    <a href="/download_exam_paper/${paperId}" class="btn btn-sm btn-primary-custom mb-2 w-100 w-md-auto" title="Download">
                                                        <i class="bi bi-download"></i> Download
                                                    </a><br class="d-none d-md-block">
                                                    <button class="btn btn-sm btn-success print-exam-paper-btn mb-2 w-100 w-md-auto" data-paper-id="${paperId}" data-class-name="${className}" data-subclass-name="${subclassName}" title="Print Exam Paper">
                                                        <i class="bi bi-printer"></i> Print
                                                    </button><br class="d-none d-md-block">
                                                ` : ''}
                                                ${paper.status === 'wait_approval' || (paper.status === 'pending' && paper.can_approve) ? `
                                                    <div class="approve-reject-actions" data-paper-id="${paperId}">
                                                        ${paper.can_approve ? `
                                                            <button class="btn btn-sm btn-success show-approve-form-btn mb-2 w-100 w-md-auto" data-paper-id="${paperId}" data-log-id="${paper.pending_log_id}" title="Approve">
                                                                <i class="bi bi-check-circle"></i> Approve
                                                            </button><br class="d-none d-md-block">
                                                            <button class="btn btn-sm btn-danger show-reject-form-btn mb-2 w-100 w-md-auto" data-paper-id="${paperId}" data-log-id="${paper.pending_log_id}" title="Reject">
                                                                <i class="bi bi-x-circle"></i> Reject
                                                            </button>
                                                        ` : `
                                                            <div class="small text-muted font-italic mb-2">
                                                                <i class="bi bi-clock"></i> Currently Pending at: <br><strong>${paper.current_approver_role}</strong>
                                                            </div>
                                                        `}
                                                        <div class="approve-form mt-2" id="approve-form-${paperId}" style="display: none;">
                                                            <textarea class="form-control mb-2" rows="3" placeholder="Approval comment (optional)..." id="approval-comment-${paperId}"></textarea>
                                                            <button class="btn btn-sm btn-success submit-approve-btn w-100" data-paper-id="${paperId}" data-log-id="${paper.pending_log_id}">
                                                                <i class="bi bi-check-circle"></i> Submit Approval
                                                            </button>
                                                            <button class="btn btn-sm btn-secondary cancel-approve-btn w-100 mt-1" data-paper-id="${paperId}">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                        <div class="reject-form mt-2" id="reject-form-${paperId}" style="display: none;">
                                                            <textarea class="form-control mb-2" rows="3" placeholder="Rejection reason (required)..." id="rejection-reason-${paperId}" required></textarea>
                                                            <button class="btn btn-sm btn-danger submit-reject-btn w-100" data-paper-id="${paperId}" data-log-id="${paper.pending_log_id}">
                                                                <i class="bi bi-x-circle"></i> Submit Rejection
                                                            </button>
                                                            <button class="btn btn-sm btn-secondary cancel-reject-btn w-100 mt-1" data-paper-id="${paperId}">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        });

                        html += `
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }

                $('#examPapersContent').html(html);
            } else {
                $('#examPapersContent').html('<div class="alert alert-danger">Failed to load exam papers.</div>');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error loading exam papers.';
            if (xhr.status === 403) {
                // Permission denied
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action. You need the view_exam_papers permission to view exam papers.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#examPapersContent').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMsg + '</div>');
        }
    });
}

jQuery(function($) {

// Toggle exam paper detail expand/collapse
$(document).on('click', '.toggle-paper-detail-btn', function() {
    const paperId = $(this).data('paper-id');
    const $detail = $('#paper-detail-' + paperId);
    const $icon = $('#toggle-icon-' + paperId);
    const $label = $('#toggle-label-' + paperId);
    const isVisible = $detail.is(':visible');

    if (isVisible) {
        $detail.slideUp(200);
        $icon.removeClass('bi-eye-slash').addClass('bi-eye');
        $label.text('View');
        $(this).css('background', '#940000');
    } else {
        $detail.slideDown(250);
        $icon.removeClass('bi-eye').addClass('bi-eye-slash');
        $label.text('Hide');
        $(this).css('background', '#6c757d');
    }
});



// Search exam papers
$('#search_exam_papers').on('input', function() {
    const examID = $('#viewExamPapersModal').data('exam-id');
    const search = $(this).val();
    const status = $('#filter_exam_paper_status').val();
    const week = $('#filter_exam_paper_week').val();
    if (examID) {
        loadExamPapers(examID, search, status, week);
    }
});

// Filter by status
$('#filter_exam_paper_status').on('change', function() {
    const examID = $('#viewExamPapersModal').data('exam-id');
    const search = $('#search_exam_papers').val();
    const status = $(this).val();
    const week = $('#filter_exam_paper_week').val();
    if (examID) {
        loadExamPapers(examID, search, status, week);
    }
});

// Filter by week
$('#filter_exam_paper_week').on('change', function() {
    const examID = $('#viewExamPapersModal').data('exam-id');
    const search = $('#search_exam_papers').val();
    const status = $('#filter_exam_paper_status').val();
    const week = $(this).val();
    if (examID) {
        loadExamPapers(examID, search, status, week);
    }
});

// Show approve form
$(document).on('click', '.show-approve-form-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#approve-form-' + paperID).slideDown();
    $('#reject-form-' + paperID).slideUp();
    $(this).closest('.approve-reject-actions').find('.show-reject-form-btn').hide();
    $(this).hide();
    $('#approval-comment-' + paperID).focus();
});

// Cancel approve form
$(document).on('click', '.cancel-approve-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#approve-form-' + paperID).slideUp();
    $('#approval-comment-' + paperID).val('');
    $(this).closest('.approve-reject-actions').find('.show-approve-form-btn').show();
    $(this).closest('.approve-reject-actions').find('.show-reject-form-btn').show();
});

// Submit approve
$(document).on('click', '.submit-approve-btn', function() {
    const paperID = $(this).data('paper-id');
    const approvalComment = $('#approval-comment-' + paperID).val().trim();

    // Check permission
    if (!hasPermission('approve_exam_paper')) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to approve exam papers.'
        });
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we approve the exam paper.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const logID = $(this).data('log-id');

    // Submit approval
    $.ajax({
        url: '/approve_reject_exam_paper/' + paperID,
        method: 'POST',
        data: {
            action: 'approve',
            approval_comment: approvalComment,
            paper_approval_log_id: logID,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Exam paper approved successfully!',
                confirmButtonColor: '#940000'
            }).then(() => {
                // Reload exam papers
                const examID = $('#viewExamPapersModal').data('exam-id');
                const search = $('#search_exam_papers').val();
                const status = $('#filter_exam_paper_status').val();
                const week = $('#filter_exam_paper_week').val();
                if (examID) {
                    loadExamPapers(examID, search, status, week);
                }
            });
        },
        error: function(xhr) {
            let errorMsg = 'Failed to approve exam paper.';
            if (xhr.status === 403) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg,
                confirmButtonColor: '#940000'
            });
        }
    });
});

// Show reject form
$(document).on('click', '.show-reject-form-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#reject-form-' + paperID).slideDown();
    $('#approve-form-' + paperID).slideUp();
    $(this).closest('.approve-reject-actions').find('.show-approve-form-btn').hide();
    $(this).hide();
    $('#rejection-reason-' + paperID).focus();
});

// Cancel reject form
$(document).on('click', '.cancel-reject-btn', function() {
    const paperID = $(this).data('paper-id');
    $('#reject-form-' + paperID).slideUp();
    $('#rejection-reason-' + paperID).val('');
    $(this).closest('.approve-reject-actions').find('.show-approve-form-btn').show();
    $(this).closest('.approve-reject-actions').find('.show-reject-form-btn').show();
});

// Submit reject
$(document).on('click', '.submit-reject-btn', function() {
    const paperID = $(this).data('paper-id');
    const rejectionReason = $('#rejection-reason-' + paperID).val().trim();

    // Check permission
    if (!hasPermission('reject_exam_paper')) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to reject exam papers.'
        });
        return;
    }

    // Validate rejection reason
    if (!rejectionReason) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please provide a rejection reason.'
        });
        $('#rejection-reason-' + paperID).focus();
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we reject the exam paper.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const logID = $(this).data('log-id');

    // Submit rejection
    $.ajax({
        url: '/approve_reject_exam_paper/' + paperID,
        method: 'POST',
        data: {
            action: 'reject',
            rejection_reason: rejectionReason,
            paper_approval_log_id: logID,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Exam paper rejected successfully!',
                confirmButtonColor: '#940000'
            }).then(() => {
                // Reload exam papers
                const examID = $('#viewExamPapersModal').data('exam-id');
                const search = $('#search_exam_papers').val();
                const status = $('#filter_exam_paper_status').val();
                const week = $('#filter_exam_paper_week').val();
                if (examID) {
                    loadExamPapers(examID, search, status, week);
                }
            });
        },
        error: function(xhr) {
            let errorMsg = 'Failed to reject exam paper.';
            if (xhr.status === 403) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg,
                confirmButtonColor: '#940000'
            });
        }
    });
});

// Toggle subclass papers view
// Toggle for Exam Widget Test Breakdown
$(document).on('click', '.test-breakdown-toggle', function(e) {
    e.preventDefault();
    const target = $(this).data('target');
    const $target = $(target);
    const $this = $(this);
    
    // Toggle the visible state
    if ($target.is(':visible')) {
        $target.slideUp(300);
        $this.attr('aria-expanded', 'false').addClass('collapsed');
    } else {
        $target.slideDown(300);
        $this.attr('aria-expanded', 'true').removeClass('collapsed');
    }
});

$(document).on('click', '.toggle-subclass-btn', function() {
    const subclassID = $(this).data('subclass-id');
    const papersDiv = $('#subclass-papers-' + subclassID);
    const icon = $(this).find('i');

    if (papersDiv.is(':visible')) {
        papersDiv.slideUp();
        icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        $(this).html('<i class="bi bi-chevron-down"></i> View Papers');
    } else {
        papersDiv.slideDown();
        icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        $(this).html('<i class="bi bi-chevron-up"></i> Hide Papers');
    }
});

// Print exam paper with class name and subclass name
$(document).on('click', '.print-exam-paper-btn', function() {
    const paperID = $(this).data('paper-id');
    const className = $(this).data('class-name') || 'N/A';
    const subclassName = $(this).data('subclass-name') || 'N/A';
    
    const downloadUrl = '/download_exam_paper/' + paperID;
    const printWindow = window.open(downloadUrl, '_blank');
    
    printWindow.onload = function() {
        // Add class name and subclass name to print
        const printContent = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; background: #fff; padding: 10px; border-bottom: 2px solid #940000; text-align: center; z-index: 9999;">
                <strong>Class:</strong> ${className} | <strong>Subclass:</strong> ${subclassName}
            </div>
        `;
        
        // Try to inject header into the print window
        setTimeout(function() {
            try {
                printWindow.print();
            } catch (e) {
                console.error('Print error:', e);
                // Fallback: just open print dialog
                printWindow.print();
            }
        }, 1000);
    };
});

// View exam paper
$(document).on('click', '.view-paper-btn', function() {
    // Check permission
    if (!hasPermission('view_exam_papers')) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to view exam papers.'
        });
        return;
    }

    const paperID = $(this).data('paper-id');
    // Try to get examID from button first, then from modal
    let examID = $(this).data('exam-id') || $('#viewExamPapersModal').data('exam-id');

    if (!examID) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Exam ID not found. Please close and reopen the exam papers modal.'
        });
        return;
    }

    $('#viewExamPaperModal').modal('show');
    $('#viewExamPaperContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

    $.ajax({
        url: '/get_exam_papers/' + examID,
        method: 'GET',
        success: function(response) {
            if (response.success && response.exam_papers) {
                const paper = response.exam_papers.find(p => p.exam_paperID == paperID);

                if (paper) {
                    const subjectName = paper.class_subject?.subject?.subject_name || 'N/A';
                    const className = paper.class_subject?.class?.class_name || 'N/A';
                    const subclassName = paper.class_subject?.subclass?.subclass_name || '';
                    const teacherName = paper.teacher ? (paper.teacher.first_name + ' ' + paper.teacher.last_name) : 'N/A';

                    let statusBadge = '';
                    if (paper.status === 'wait_approval') {
                        statusBadge = '<span class="badge badge-warning"><i class="bi bi-clock-history"></i> Waiting Approval</span>';
                    } else if (paper.status === 'approved') {
                        statusBadge = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>';
                    } else {
                        statusBadge = '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>';
                    }

                    let html = `
                        <div class="card mb-3">
                            <div class="card-header bg-primary-custom text-white">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> ${subjectName}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>Class:</strong> ${subclassName || className}</p>
                                        <p><strong>Teacher:</strong> ${teacherName}</p>
                                        <p><strong>Type:</strong> <span class="badge badge-info">${paper.upload_type === 'upload' ? 'File Upload' : 'Created'}</span></p>
                                        <p><strong>Status:</strong> ${statusBadge}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Uploaded:</strong> ${new Date(paper.created_at).toLocaleString()}</p>
                                        ${paper.upload_type === 'upload' && paper.file_path ? `
                                            <a href="/download_exam_paper/${paper.exam_paperID}" class="btn btn-primary-custom" target="_blank">
                                                <i class="bi bi-download"></i> Download Exam Paper
                                            </a>
                                        ` : ''}
                                    </div>
                                </div>
                    `;

                    if (paper.upload_type === 'upload' && paper.file_path) {
                        // Display PDF/DOC viewer if possible
                        const fileExtension = paper.file_path.split('.').pop().toLowerCase();
                        if (fileExtension === 'pdf') {
                            html += `
                                <div class="mt-3">
                                    <h6><i class="bi bi-file-pdf"></i> Exam Paper Preview</h6>
                                    <div class="border rounded p-2" style="background-color: #f8f9fa;">
                                        <iframe src="/download_exam_paper/${paper.exam_paperID}?inline=1"
                                                type="application/pdf"
                                                style="width: 100%; height: 700px; border: none;"
                                                id="pdf-viewer-${paper.exam_paperID}"
                                                frameborder="0">
                                        </iframe>
                                        <p class="text-muted text-center mt-2">
                                            <small>If PDF doesn't display, <a href="/download_exam_paper/${paper.exam_paperID}" target="_blank">click here to download</a></small>
                                        </p>
                                    </div>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> This file format (${fileExtension.toUpperCase()}) cannot be previewed. Please download to view.
                                </div>
                            `;
                        }
                    } else if (paper.question_content) {
                        html += `
                            <div class="mt-3">
                                <h6><i class="bi bi-pencil"></i> Exam Questions Content</h6>
                                <div class="border p-3" style="background-color: #f8f9fa; white-space: pre-wrap; font-family: monospace;">${paper.question_content}</div>
                            </div>
                        `;
                    }

                    if (paper.rejection_reason) {
                        html += `
                            <div class="alert alert-danger mt-3">
                                <strong><i class="bi bi-exclamation-triangle"></i> Rejection Reason:</strong>
                                <p class="mb-0">${paper.rejection_reason}</p>
                            </div>
                        `;
                    }

                    if (paper.approval_comment) {
                        html += `
                            <div class="alert alert-success mt-3">
                                <strong><i class="bi bi-check-circle"></i> Approval Comment:</strong>
                                <p class="mb-0">${paper.approval_comment}</p>
                            </div>
                        `;
                    }

                    html += `
                            </div>
                        </div>
                    `;

                    $('#viewExamPaperContent').html(html);
                } else {
                    $('#viewExamPaperContent').html('<div class="alert alert-danger">Exam paper not found</div>');
                }
            } else {
                $('#viewExamPaperContent').html('<div class="alert alert-danger">Failed to load exam paper details</div>');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error loading exam paper';
            if (xhr.status === 403) {
                // Permission denied
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else {
                    errorMsg = 'You are not allowed to perform this action. You need the view_exam_papers permission to view exam papers.';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#viewExamPaperContent').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMsg + '</div>');
        }
    });
});

// --- Week Navigator Logic ---
window.updateWeekNavigatorUI = function() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    if (!window.examPaperWeeks || window.examPaperWeeks.length === 0) {
        $('#current_week_display').text('No weeks available');
        $('#prev_week_btn').prop('disabled', true);
        $('#next_week_btn').prop('disabled', true);
        return;
    }

    const idx = window.currentWeekIndex;

    if (idx < 0 || idx >= window.examPaperWeeks.length) {
        $('#current_week_display').text('Unknown Week');
        return;
    }

    const currentData = window.examPaperWeeks[idx];
    
    // Display Format: "Week X (Date - Date)"
    // Add "This Week" prefix if it's the current week
    let displayText = `${currentData.week} (${currentData.range})`;
    if (currentData.is_current) {
        displayText = `This Week (${displayText})`;
    }

    $('#current_week_display').text(displayText);
    
    // Disable buttons at boundaries
    $('#prev_week_btn').prop('disabled', idx <= 0);
    $('#next_week_btn').prop('disabled', idx >= window.examPaperWeeks.length - 1);
    
    // Update hidden input used for filtering logic if referenced elsewhere
    $('#filter_exam_paper_week').val(currentData.week);
}

// Navigator Event Handlers
$(document).on('click', '#prev_week_btn', function(e) {
    e.preventDefault();
    if (window.examPaperWeeks && window.currentWeekIndex > 0) {
        window.currentWeekIndex--;
        const week = window.examPaperWeeks[window.currentWeekIndex].week;
        
        // Fetch current filter states
        const examID = $('#viewExamPapersModal').data('exam-id');
        const search = $('#search_exam_papers').val();
        const status = $('#filter_exam_paper_status').val();
        
        updateWeekNavigatorUI(); // Optimistic Update
        loadExamPapers(examID, search, status, week);
    }
});

$(document).on('click', '#next_week_btn', function(e) {
    e.preventDefault();
    if (window.examPaperWeeks && window.currentWeekIndex < window.examPaperWeeks.length - 1) {
        window.currentWeekIndex++;
        const week = window.examPaperWeeks[window.currentWeekIndex].week;
        
        // Fetch current filter states
        const examID = $('#viewExamPapersModal').data('exam-id');
        const search = $('#search_exam_papers').val();
        const status = $('#filter_exam_paper_status').val();
        
        updateWeekNavigatorUI(); // Optimistic Update
        loadExamPapers(examID, search, status, week);
    }
});
});

/* --- Exam Attendance Management Logic --- */
</script>

<style>
    #examAttendanceModal .modal-content,
    #examAttendanceModal .modal-header,
    #examAttendanceModal .nav-link,
    #examAttendanceModal .btn,
    #examAttendanceModal .card,
    #examAttendanceModal .form-control,
    #examAttendanceModal .badge,
    #examAttendanceModal .alert {
        border-radius: 0 !important;
    }
    #examAttendanceModal .modal-content {
        overflow: hidden;
        border: none;
        background-color: #FFFFFF;
    }
    #examAttendanceModal .modal-header {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
        color: #940000 !important;
        border-bottom: 2px solid #940000 !important;
    }
    #examAttendanceModal .modal-header .modal-title, 
    #examAttendanceModal .modal-header .close {
        color: #940000 !important;
        opacity: 1;
    }
    #examAttendanceModal .nav-tabs {
        border-bottom: 1px solid #eee;
        background-color: #FFFFFF;
    }
    #examAttendanceModal .nav-link {
        color: #666;
        border: none;
        padding: 15px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    #examAttendanceModal .nav-link.active {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
        color: #940000 !important;
        border-bottom: 3px solid #940000 !important;
    }
    #examAttendanceModal .nav-link:hover:not(.active) {
        background-color: #FFF5F5;
        color: #940000;
    }
    #examAttendanceModal .btn-primary-custom {
        background-color: #940000 !important;
        border-color: #940000 !important;
        color: #FFFFFF !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    #examAttendanceModal .btn-primary-custom:hover {
        background-color: #7a0000 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
    }
    #examAttendanceModal .card {
        border: 1px solid #eee;
        transition: all 0.3s ease;
    }
    #examAttendanceModal .assignment-widget:hover {
        background-color: #FFF5F5 !important;
    }
    #examAttendanceModal .badge-primary { background-color: #940000; }
    #examAttendanceModal .text-primary-custom { color: #940000 !important; }
    
    .subject-checkbox-wrapper {
        transition: all 0.2s ease;
    }
    .subject-checkbox-wrapper:hover:not(.disabled) {
        background-color: #FFF5F5 !important;
        border-color: #940000 !important;
    }
    
    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #940000 !important;
        border-color: #940000 !important;
    }
    .animate-pulse { animation: pulse 1.5s infinite; }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<!-- DOWNLOAD PROGRESS MODAL -->
<div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="bi bi-file-pdf fa-3x text-danger animate-pulse" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold;">Generating Attendance PDF</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 0;">
                    <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                        role="progressbar" style="width: 0%; background-color: #940000;"></div>
                </div>
                <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small">Please wait while we prepare your document. This might take a moment.</p>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

// Modal Open Handler
$(document).on('click', '.view-exam-attendance-btn', function() {
    const examID = $(this).data('exam-id');
    const examName = $(this).data('exam-name');
    
    $('#attendance_exam_id').val(examID);
    $('#attendance_exam_name').text(examName);
    $('#examAttendanceModal').modal('show');
    
    // Load classes for this exam
    loadAttendanceClasses(examID);
    
    // Switch to first tab by default
    $('#attendanceTabs a[href="#assign-teacher"]').tab('show');
});

function loadAttendanceClasses(examID) {
    $.get("{{ url('admin/exam-attendance/classes') }}/" + examID, function(response) {
        if (response.success) {
            let options = '<option value="">Select Class</option>';
            response.classes.forEach(c => {
                options += `<option value="${c.classID}">${c.class_name}</option>`;
            });
            $('#assign_attendance_class, #collect_attendance_class, #view_att_class_filter, #stats_class_filter').html(options);
            // Default "All Classes" for filters
            $('#view_att_class_filter, #stats_class_filter').prepend('<option value="all" selected>All Classes</option>');
        }
    });
}

// Tab change listener
$('#attendanceTabs a').on('shown.bs.tab', function (e) {
    const examID = $('#attendance_exam_id').val();
    const target = $(e.target).attr("href");
    
    if (target === '#view-attendance') {
        loadAttendanceSummary(examID);
    } else if (target === '#attendance-stats') {
        loadAttendanceStats(examID);
    } else if (target === '#view-assigned') {
        loadAssignedTeachers(examID);
    }
});

// Class change handler for Assign Teacher tab
$('#assign_attendance_class').on('change', function() {
    const classID = $(this).val();
    if (!classID) return;
    
    // Load subclasses
    $.get("{{ url('admin/exam-attendance/subclasses') }}/" + classID, function(response) {
        if (response.success) {
            let options = '<option value="all">All Subclasses</option>';
            response.subclasses.forEach(sc => {
                options += `<option value="${sc.subclassID}">${sc.subclass_name}</option>`;
            });
            $('#assign_attendance_subclass').html(options);
            loadAttendanceSubjects(); // Initial subjects load
        }
    });
});

$('#assign_attendance_subclass').on('change', loadAttendanceSubjects);

function loadAttendanceSubjects() {
    const examID = $('#attendance_exam_id').val();
    const classID = $('#assign_attendance_class').val();
    const subclassID = $('#assign_attendance_subclass').val();
    const assignmentID = $('#attendance_assignment_id').val();
    
    if (!classID) return;
    
    $.post("{{ route('admin.exam_attendance.subjects') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        classID: classID,
        subclassID: subclassID,
        assignmentID: assignmentID
    }, function(response) {
        if (response.success) {
            let html = '';
            response.subjects.forEach(s => {
                const isAssigned = response.assigned_subject_ids.includes(String(s.subjectID)) || response.assigned_subject_ids.includes(Number(s.subjectID));
                
                html += `
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-checkbox p-2 border rounded ${isAssigned ? 'bg-light text-muted' : 'bg-white'}" 
                             style="${isAssigned ? 'opacity: 0.7; cursor: not-allowed;' : ''}">
                            <input type="checkbox" class="custom-control-input subject-checkbox" 
                                   id="att_subject_${s.subjectID}" 
                                   name="subject_ids[]" 
                                   value="${s.subjectID}"
                                   ${isAssigned ? 'disabled onclick="return false;"' : ''}>
                            <label class="custom-control-label w-100 cursor-pointer" for="att_subject_${s.subjectID}">
                                ${s.subject.subject_name}
                                ${isAssigned ? '<br><span class="badge badge-warning">Already Assigned</span>' : ''}
                            </label>
                        </div>
                    </div>
                `;
            });
            $('#attendance_subjects_container').html(html || '<div class="col-12 text-center text-muted">No subjects found for this class</div>');
        }
    });
}

// Form Submit: Assign Teacher
$('#assignAttendanceTeacherForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    
    $.post("{{ route('admin.exam_attendance.assign') }}", formData, function(response) {
        if (response.success) {
            Swal.fire('Success', response.message, 'success');
            loadAttendanceSubjects(); // Refresh subjects
        } else {
            Swal.fire('Error', response.error || 'Failed to save assignment', 'error');
        }
    });
});



function loadAssignedTeachers(examID) {
    $.get("{{ url('admin/exam-attendance/assigned') }}/" + examID, function(response) {
        if (response.success) {
            let html = '';
            response.assignments.forEach(a => {
                html += `
                    <div class="card mb-2 border-0 shadow-sm rounded-0">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center cursor-pointer assignment-widget" data-id="${a.id}">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold">${a.teacher.first_name} ${a.teacher.last_name}</h6>
                                <small class="text-muted">${a.class.class_name} ${a.subclass ? a.subclass.subclass_name : 'All Subclasses'}</small>
                            </div>
                            <div class="btn-group mr-3">
                                <button class="btn btn-sm btn-outline-info edit-assignment-btn" 
                                        data-id="${a.id}" 
                                        data-teacher-id="${a.teacherID}"
                                        data-class-id="${a.classID}"
                                        data-subclass-id="${a.subclassID || 'all'}"
                                        data-subjects='${JSON.stringify(a.subject_ids)}'
                                        title="Edit Assignment">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-assignment-btn" 
                                        data-id="${a.id}" 
                                        title="Delete Assignment">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="custom-control custom-switch mr-3">
                                <input type="checkbox" class="custom-control-input toggle-assignment-status" 
                                       id="toggle_att_${a.id}" data-id="${a.id}" ${a.status ? 'checked' : ''}>
                                <label class="custom-control-label cursor-pointer" for="toggle_att_${a.id}"></label>
                            </div>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="card-body p-3 border-top assignment-details" id="assignment_details_${a.id}" style="display: none;">
                            <div class="text-center py-3"><i class="spinner-border spinner-border-sm"></i> Loading details...</div>
                        </div>
                    </div>
                `;
            });
            $('#assigned_teachers_list').html(html || '<div class="alert alert-info text-center py-4">No teachers assigned yet</div>');
        }
    })
    .fail(function() {
        $('#assigned_teachers_list').html('<div class="alert alert-danger text-center py-4"><i class="bi bi-exclamation-triangle"></i> Failed to load assignments. Please check your connection.</div>');
    });
}

// Edit Assignment Handler
$(document).on('click', '.edit-assignment-btn', function(e) {
    e.stopPropagation();
    const id = $(this).data('id');
    const teacherID = $(this).data('teacher-id');
    const classID = $(this).data('class-id');
    const subclassID = $(this).data('subclass-id');
    const subjects = $(this).data('subjects');
    
    // Set form to edit mode
    $('#attendance_assignment_id').val(id);
    $('#assign_attendance_class').val(classID).trigger('change');
    $('#assign_attendance_teacher').val(teacherID);
    $('#cancel_edit_assignment').show();
    $('#save_assignment_btn').html('<i class="bi bi-save"></i> Update Assignment');
    
    // Subclass and subjects need to wait for AJAX
    setTimeout(() => {
        $('#assign_attendance_subclass').val(subclassID).trigger('change');
        
        setTimeout(() => {
            subjects.forEach(sid => {
                $(`#att_subject_${sid}`).prop('checked', true).prop('disabled', false).parent().removeClass('bg-light text-muted').addClass('bg-white').css('opacity', '1');
            });
        }, 1000);
    }, 500);
    
    $('#attendanceTabs a[href="#assign-teacher"]').tab('show');
});

$('#cancel_edit_assignment').on('click', function() {
    $('#attendance_assignment_id').val('');
    $('#assignAttendanceTeacherForm')[0].reset();
    $('#cancel_edit_assignment').hide();
    $('#save_assignment_btn').html('<i class="bi bi-save"></i> Save Assignment & Notify Teacher');
    $('#attendance_subjects_container').html('<div class="col-12 text-center text-muted">Please select a class first</div>');
});

// Delete Assignment Handler
$(document).on('click', '.delete-assignment-btn', function(e) {
    e.stopPropagation();
    const id = $(this).data('id');
    const examID = $('#attendance_exam_id').val();
    
    Swal.fire({
        title: 'Are you sure?',
        text: "This will remove the teacher assignment. Attendance records already collected will remain but the teacher will no longer be assigned.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/exam-attendance/delete-assignment') }}/" + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        loadAssignedTeachers(examID);
                        loadAttendanceSubjects();
                    }
                }
            });
        }
    });
});

// Toggle Assignment Status Handler
$(document).on('change', '.toggle-assignment-status', function() {
    const id = $(this).data('id');
    const isChecked = $(this).is(':checked');
    
    $.post("{{ url('admin/exam-attendance/toggle-status') }}/" + id, {
        _token: "{{ csrf_token() }}"
    }, function(response) {
        if (response.success) {
            Swal.fire({
                title: 'Status Updated',
                text: response.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
});

$(document).on('click', '.assignment-widget', function() {
    const id = $(this).data('id');
    const detailsContainer = $(`#assignment_details_${id}`);
    const icon = $(this).find('i');
    
    if (detailsContainer.is(':visible')) {
        detailsContainer.slideUp();
        icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
    } else {
        $('.assignment-details').slideUp(); // Close others
        $('.bi-chevron-up').removeClass('bi-chevron-up').addClass('bi-chevron-down');
        
        detailsContainer.slideDown();
        icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        
        // Load details
        $.get("{{ url('admin/exam-attendance/assignment-details') }}/" + id)
        .done(function(response) {
            if (response.success) {
                if (!response.details || response.details.length === 0) {
                    detailsContainer.html('<div class="alert alert-info border-0 rounded-0 m-0"><i class="bi bi-info-circle"></i> No subjects found for this assignment.</div>');
                    return;
                }

                let html = '';
                response.details.forEach(d => {
                    const studentCount = d.students ? d.students.length : 0;
                    html += `
                        <div class="mb-3 border rounded-0 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold text-dark">${d.subject_name}</h6>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary download-template-btn" 
                                            data-subject-id="${d.subjectID}" 
                                            data-exam-id="${$('#attendance_exam_id').val()}"
                                            data-class-id="${response.assignment.classID}"
                                            data-subclass-id="${response.assignment.subclassID || ''}"
                                            style="border-radius: 0 !important;">
                                        <i class="bi bi-file-pdf"></i> Template
                                    </button>
                                    <button class="btn btn-sm btn-primary view-students-att-btn" 
                                            data-target="#subject_students_${id}_${d.subjectID}"
                                            style="background-color: #940000; border-color: #940000; border-radius: 0 !important;">
                                        <i class="bi bi-people"></i> Students (${studentCount})
                                    </button>
                                </div>
                            </div>
                            <div id="subject_students_${id}_${d.subjectID}" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mt-2 mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0">Student Name</th>
                                                <th class="text-center border-0">Exam Number</th>
                                                <th class="text-center border-0">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${studentCount > 0 ? d.students.map(s => `
                                                <tr>
                                                    <td class="align-middle">${s.name}</td>
                                                    <td class="text-center align-middle small text-muted font-monospace">${s.examination_number || '-'}</td>
                                                    <td class="text-center align-middle">
                                                        <select class="form-control form-control-sm attendance-select-view" 
                                                                data-student-id="${s.studentID}" 
                                                                data-assignment-id="${id}" 
                                                                data-subject-id="${d.subjectID}"
                                                                style="border-radius: 0 !important; border: 1px solid #eee; width: 120px; display: inline-block;">
                                                            <option value="Present" ${s.status === 'Present' ? 'selected' : ''}>Present</option>
                                                            <option value="Absent" ${s.status === 'Absent' ? 'selected' : ''}>Absent</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            `).join('') : '<tr><td colspan="2" class="text-center py-3 text-muted">No students found</td></tr>'}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                });
                detailsContainer.html(html);
            } else {
                detailsContainer.html(`<div class="alert alert-danger border-0 rounded-0 m-0"><i class="bi bi-exclamation-triangle"></i> ${response.error || 'Failed to load details'}</div>`);
            }
        })
        .fail(function() {
            detailsContainer.html('<div class="alert alert-danger border-0 rounded-0 m-0"><i class="bi bi-exclamation-triangle"></i> Network error. Please try again.</div>');
        });
    }
});

// Add stopPropagation to buttons inside the widget to prevent expansion
$(document).on('click', '.assignment-widget .btn, .assignment-widget .form-check-input', function(e) {
    e.stopPropagation();
});

$(document).on('click', '.view-students-att-btn', function() {
    const target = $(this).data('target');
    $(target).slideToggle();
});

$(document).on('click', '.download-template-btn', function() {
    const examID = $(this).data('exam-id');
    const subjectID = $(this).data('subject-id');
    const classID = $(this).data('class-id');
    const subclassID = $(this).data('subclass-id');
    
    window.location.href = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}`;
});

function loadAttendanceSummary(examID) {
    const classID = $('#view_att_class_filter').val();
    const subclassID = $('#view_att_subclass_filter').val();
    const subjectID = $('#view_att_subject_filter').val();

    $('#attendance_collected_summary').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom"></div><p class="mt-2">Updating summary...</p></div>');

    $.get("{{ url('admin/exam-attendance/summary') }}/" + examID, {
        classID: classID,
        subclassID: subclassID,
        subjectID: subjectID
    }, function(response) {
        if (response.success) {
            let html = '';
            if (!response.summary || response.summary.length === 0) {
                html = '<div class="alert alert-info text-center py-5" style="border-radius: 0 !important;">No attendance data matching your filters</div>';
            } else {
                response.summary.forEach(c => {
                    html += `
                        <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: 0 !important;">
                            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center" style="cursor: pointer; border-left: 5px solid #940000 !important;" onclick="$(this).next('.subclass-container').slideToggle()">
                                <div>
                                    <h4 class="mb-1 fw-bold text-dark text-uppercase letter-spacing-1">${c.class_name}</h4>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-light border text-muted mr-2" style="font-size: 11px;">${c.student_count} Total Students</span>
                                        <span class="text-primary-custom small fw-bold"><i class="bi bi-stack mr-1"></i> ${c.subclasses.length} Subclasses</span>
                                    </div>
                                </div>
                                <div class="text-right d-flex align-items-center">
                                    <div class="mr-4 d-none d-md-block">
                                        <div class="text-muted small text-uppercase mb-1" style="font-size: 10px;">Overall Class Participation</div>
                                        <div class="progress" style="height: 6px; width: 120px; border-radius: 0 !important; background-color: #eee;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%; border-radius: 0 !important;"></div>
                                        </div>
                                    </div>
                                    <i class="bi bi-chevron-down text-muted fs-4"></i>
                                </div>
                            </div>
                            <div class="card-body p-0 subclass-container" style="display: none; background-color: #fdfdfd;">
                                ${(c.subclasses || []).map(sc => `
                                    <div class="p-4 border-top bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary-custom text-white p-2 mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                    ${sc.subclass_name.charAt(0)}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">${sc.subclass_name}</h6>
                                                    <small class="text-muted">${sc.student_count} Registered Students</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mx-n2">
                                            ${(sc.subjects || []).map(s => `
                                                <div class="col-xl-4 col-lg-6 px-2 mb-3">
                                                    <div class="border p-4 bg-white subject-detail-card transition-all" 
                                                         style="border-left: 4px solid #eee !important; cursor: pointer; border-radius: 0 !important;" 
                                                         data-exam-id="${examID}"
                                                         data-subject-id="${s.subjectID}"
                                                         data-class-id="${c.classID}"
                                                         data-subclass-id="${sc.subclassID || ''}"
                                                         onmouseover="this.style.borderColor='#940000'; this.style.borderLeftColor='#940000'"
                                                         onmouseout="this.style.borderColor='#eee'; this.style.borderLeftColor='#eee'"
                                                         onclick="loadSubjectStudentDrilldown(event, this)">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h6 class="mb-1 fw-bold text-dark" style="font-size: 15px;">${s.subject_name}</h6>
                                                                <small class="text-muted d-block"><i class="bi bi-person-badge mr-1"></i> ${s.teacher_name}</small>
                                                            </div>
                                                            <div class="text-right">
                                                                <div class="h5 mb-0 fw-bold text-primary-custom">${s.present}/${s.expected}</div>
                                                                <small class="text-uppercase fw-bold" style="font-size: 9px; color: ${ (s.present/s.expected) > 0.8 ? '#28a745' : '#f39c12' }">
                                                                    ${s.expected > 0 ? Math.round((s.present/s.expected)*100) : 0}% Recorded
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="progress mb-3" style="height: 4px; border-radius: 0 !important; background-color: #f0f0f0;">
                                                            <div class="progress-bar ${ (s.present/s.expected) > 0.8 ? 'bg-success' : 'bg-warning' }" 
                                                                 role="progressbar" 
                                                                 style="width: ${s.expected > 0 ? (s.present/s.expected)*100 : 0}%; border-radius: 0 !important;"></div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-outline-dark flex-fill" 
                                                                    onclick="event.stopPropagation(); downloadAdminTemplateSpecific(this)"
                                                                    style="font-size: 11px; border-radius: 0 !important; border-color: #ddd;">
                                                                <i class="bi bi-file-earmark-pdf"></i> Template
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-dark flex-fill" 
                                                                    onclick="event.stopPropagation(); downloadAdminAttendanceSpecific(this)"
                                                                    style="font-size: 11px; border-radius: 0 !important; border-color: #ddd;">
                                                                <i class="bi bi-cloud-download"></i> Record
                                                            </button>
                                                            <button class="btn btn-sm btn-primary-custom" 
                                                                    onclick="event.stopPropagation(); editSubjectAttendanceDirectFromCard(this)"
                                                                    style="font-size: 11px; border-radius: 0 !important; background-color: #940000;">
                                                                <i class="bi bi-pencil-square"></i> Edit
                                                            </button>
                                                        </div>
                                                        <div class="subject-student-drilldown mt-4 border-top pt-3" style="display: none;">
                                                            <div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary-custom"></div></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                });
            }
            $('#attendance_collected_summary').html(html);
        }
    }).fail(function() {
        $('#attendance_collected_summary').html('<div class="alert alert-danger text-center py-5">Error loading summary. Please try again.</div>');
    });
}

function editSubjectAttendanceDirectFromCard(btn) {
    const card = $(btn).closest('.subject-detail-card');
    const examID = card.data('exam-id');
    const subjectID = card.data('subject-id');
    const classID = card.data('class-id');
    const subclassID = card.data('subclass-id');

    $.post("{{ route('admin.exam_attendance.admin_students') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        classID: classID,
        subclassID: subclassID,
        subjectID: subjectID
    }, function(response) {
        if (response.success) {
            showAttendanceEditModal(examID, subjectID, 'Attendance Details', response.students);
        }
    });
}

window.downloadAdminTemplateSpecific = function(btn) {
    const card = $(btn).closest('.subject-detail-card');
    const examID = card.data('exam-id');
    const subjectID = card.data('subject-id');
    const classID = card.data('class-id');
    const subclassID = card.data('subclass-id');
    const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}`;
    performDownloadWithProgress(url, `Template_${subjectID}.pdf`);
}

window.downloadAdminAttendanceSpecific = function(btn) {
    const card = $(btn).closest('.subject-detail-card');
    const examID = card.data('exam-id');
    const subjectID = card.data('subject-id');
    const classID = card.data('class-id');
    const subclassID = card.data('subclass-id');
    const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}&with_data=true`;
    performDownloadWithProgress(url, `Attendance_${subjectID}.pdf`);
}

window.downloadAdminTemplate = function() {
    const examID = $('#attendance_exam_id').val();
    const classID = $('#collect_attendance_class').val();
    const subclassID = $('#collect_attendance_subclass').val();
    const subjectID = $('#collect_attendance_subject').val();
    
    if (!subjectID) {
        Swal.fire('Info', 'Please select a subject first.', 'info');
        return;
    }
    
    const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}`;
    performDownloadWithProgress(url, `Template_${subjectID}.pdf`);
};

window.downloadAdminAttendance = function() {
    const examID = $('#attendance_exam_id').val();
    const classID = $('#collect_attendance_class').val();
    const subclassID = $('#collect_attendance_subclass').val();
    const subjectID = $('#collect_attendance_subject').val();
    
    if (!subjectID) {
        Swal.fire('Info', 'Please select a subject first.', 'info');
        return;
    }
    
    const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}&with_data=true`;
    performDownloadWithProgress(url, `Attendance_${subjectID}.pdf`);
};

// Student drilldown loader
window.loadSubjectStudentDrilldown = function(event, element) {
    event.stopPropagation();
    const card = $(element);
    const drilldown = card.find('.subject-student-drilldown');
    
    if (drilldown.is(':visible')) {
        drilldown.slideUp();
        return;
    }

    const examID = card.data('exam-id');
    const subjectID = card.data('subject-id');
    const classID = card.data('class-id');
    const subclassID = card.data('subclass-id');

    drilldown.html('<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary-custom"></div></div>').slideDown();

    $.post("{{ route('admin.exam_attendance.admin_students') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        classID: classID,
        subclassID: subclassID,
        subjectID: subjectID
    }, function(response) {
        if (response.success) {
            let html = `
                <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                    <thead class="bg-white">
                        <tr>
                            <th>Student</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            response.students.forEach(s => {
                html += `
                    <tr>
                        <td class="font-weight-bold">${s.name}</td>
                        <td class="text-center">
                            <span class="badge ${s.status === 'Present' ? 'badge-success' : 'badge-danger'}" style="font-size: 9px;">${s.status}</span>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            drilldown.html(html);
        }
    });
}

if (typeof attendanceChart === 'undefined') {
    var attendanceChart = null;
}
function loadAttendanceStats(examID) {
    const classID = $('#stats_class_filter').val();
    const subclassID = $('#stats_subclass_filter').val();
    const subjectID = $('#stats_subject_filter').val();

    $.get("{{ url('admin/exam-attendance/stats') }}/" + examID, {
        classID: classID,
        subclassID: subclassID,
        subjectID: subjectID
    }, function(response) {
        if (response.success) {
            // Update Totals
            $('#stats_total_present').text(response.totals ? (response.totals.total_present || 0) : 0);
            $('#stats_total_absent').text(response.totals ? (response.totals.total_absent || 0) : 0);

            const chartContainer = $('#attendanceStatsChart').parent();
            
            if (response.stats && response.stats.length > 0) {
                const labels = response.stats.map(s => s.class_name);
                const presentData = response.stats.map(s => s.present_count);
                const absentData = response.stats.map(s => s.absent_count);
                
                if (attendanceChart) attendanceChart.destroy();
                
                const ctx = document.getElementById('attendanceStatsChart').getContext('2d');
                attendanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Present',
                                data: presentData,
                                backgroundColor: '#28a745',
                                borderRadius: 0
                            },
                            {
                                label: 'Absent',
                                data: absentData,
                                backgroundColor: '#dc3545',
                                borderRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, font: { family: 'Century Gothic' } } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { display: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
                $('#no-stats-msg').remove();
                $('#attendanceStatsChart').show();
            } else {
                if (attendanceChart) attendanceChart.destroy();
                $('#attendanceStatsChart').hide();
                if ($('#no-stats-msg').length === 0) {
                    chartContainer.append('<div id="no-stats-msg" class="alert alert-info text-center py-5">No attendance data available for these filters</div>');
                }
            }
        }
    }).fail(function() {
         Swal.fire('Error', 'Failed to load statistics.', 'error');
    });
}

// Filter Event Listeners
$('#view_att_class_filter, #stats_class_filter').on('change', function() {
    const classID = $(this).val();
    const isStats = $(this).attr('id') === 'stats_class_filter';
    const subclassSelect = isStats ? $('#stats_subclass_filter') : $('#view_att_subclass_filter');
    const examID = $('#attendance_exam_id').val();

    if (classID === 'all') {
        subclassSelect.html('<option value="all">All Subclasses</option>');
        if (isStats) loadAttendanceStats(examID); else loadAttendanceSummary(examID);
        return;
    }

    $.get("{{ url('admin/exam-attendance/subclasses') }}/" + classID, function(response) {
        if (response.success) {
            let options = '<option value="all">All Subclasses</option>';
            response.subclasses.forEach(sc => {
                options += `<option value="${sc.subclassID}">${sc.subclass_name}</option>`;
            });
            subclassSelect.html(options);
            
            // Trigger subject loading for the whole class if subclass is 'all'
            const subjectSelect = isStats ? $('#stats_subject_filter') : $('#view_att_subject_filter');
            $.post("{{ route('admin.exam_attendance.subjects') }}", {
                _token: "{{ csrf_token() }}",
                examID: examID,
                classID: classID,
                subclassID: 'all',
                isAdmin: true
            }, function(subResponse) {
                if (subResponse.success) {
                    let subOptions = '<option value="all">All Subjects</option>';
                    subResponse.subjects.forEach(s => {
                        subOptions += `<option value="${s.subjectID}">${s.subject.subject_name}</option>`;
                    });
                    subjectSelect.html(subOptions);
                    if (isStats) loadAttendanceStats(examID); else loadAttendanceSummary(examID);
                }
            });
        }
    });
});

$('#view_att_subclass_filter, #stats_subclass_filter').on('change', function() {
    const subclassID = $(this).val();
    const classID = $(this).closest('.row').find('select:first').val();
    const isStats = $(this).attr('id') === 'stats_subclass_filter';
    const subjectSelect = isStats ? $('#stats_subject_filter') : $('#view_att_subject_filter');
    const examID = $('#attendance_exam_id').val();

    if (subclassID === 'all' && classID === 'all') {
        subjectSelect.html('<option value="all">All Subjects</option>');
        if (isStats) loadAttendanceStats(examID); else loadAttendanceSummary(examID);
        return;
    }

    // Load subjects for filters
    $.post("{{ route('admin.exam_attendance.subjects') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        classID: classID,
        subclassID: subclassID,
        isAdmin: true
    }, function(response) {
        if (response.success) {
            let options = '<option value="all">All Subjects</option>';
            response.subjects.forEach(s => {
                options += `<option value="${s.subjectID}">${s.subject.subject_name}</option>`;
            });
            subjectSelect.html(options);
            if (isStats) loadAttendanceStats(examID); else loadAttendanceSummary(examID);
        }
    });
});

$('#view_att_subject_filter, #stats_subject_filter').on('change', function() {
    const examID = $('#attendance_exam_id').val();
    if ($(this).attr('id') === 'stats_subject_filter') loadAttendanceStats(examID); else loadAttendanceSummary(examID);
});

$(document).on('click', '.edit-attendance-btn', function() {
    const examID = $(this).data('exam-id');
    const subjectID = $(this).data('subject-id');
    const classID = $(this).data('class-id');
    const subclassID = $(this).data('subclass-id');
    
    // Create a temporary ID to check if assignment exists to get students
    // Actually we can just get students for class/subclass directly
    // Using assignment details logic to get student list with current status
    
    // We'll reuse getAssignmentDetails but with specific subject
    // But since it needs an assignment ID, we'll find the assignment first
    $.get("{{ url('admin/exam-attendance/assigned') }}/" + examID, function(response) {
        if (response.success) {
            const assignment = response.assignments.find(a => 
                a.classID == classID && 
                (a.subclassID == subclassID || (!a.subclassID && !subclassID))
            );
            
            if (assignment) {
                $.get("{{ url('admin/exam-attendance/assignment-details') }}/" + assignment.id, function(detailsResp) {
                    if (detailsResp.success) {
                        const subjectDetail = detailsResp.details.find(d => d.subjectID == subjectID);
                        if (subjectDetail) {
                            showAttendanceEditModal(examID, subjectID, subjectDetail.subject_name, subjectDetail.students);
                        }
                    }
                });
            }
        }
    });
});

function showAttendanceEditModal(examID, subjectID, subjectName, students) {
    let html = `
        <form id="editAttendanceForm">
            <input type="hidden" name="examID" value="${examID}">
            <input type="hidden" name="subjectID" value="${subjectID}">
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th class="text-center">Exam Number</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${students.map(s => `
                            <tr>
                                <td>${s.name}</td>
                                <td class="text-center small text-muted font-monospace">${s.examination_number || '-'}</td>
                                <td class="text-center">
                                    <input type="radio" name="attendance[${s.studentID}]" value="Present" ${s.status === 'Present' ? 'checked' : ''}>
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="attendance[${s.studentID}]" value="Absent" ${s.status === 'Absent' ? 'checked' : ''}>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </form>
    `;
    
    Swal.fire({
        title: `Edit Attendance: ${subjectName}`,
        html: html,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        confirmButtonColor: '#940000',
        width: '600px',
        preConfirm: () => {
            const formData = [];
            $('#editAttendanceForm table tbody tr').each(function() {
                const studentID = $(this).find('input[type="radio"]').attr('name').match(/\d+/)[0];
                const status = $(this).find('input[type="radio"]:checked').val();
                formData.push({ studentID, status });
            });
            
            return $.post("{{ route('admin.exam_attendance.save') }}", {
                _token: "{{ csrf_token() }}",
                examID: examID,
                subjectID: subjectID,
                attendance: formData
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Saved!', 'Attendance has been updated.', 'success');
            loadAttendanceSummary(examID);
        }
    });
}



// --- Admin Direct Collect Attendance Logic ---

$('#collect_attendance_class').on('change', function() {
    const classID = $(this).val();
    if (!classID) {
        $('#admin_collect_content').hide();
        $('#admin_collect_placeholder').show();
        return;
    }
    
    // Load subclasses
    $.get("{{ url('admin/exam-attendance/subclasses') }}/" + classID, function(response) {
        if (response.success) {
            let options = '<option value="all">All Subclasses</option>';
            response.subclasses.forEach(sc => {
                options += `<option value="${sc.subclassID}">${sc.subclass_name}</option>`;
            });
            $('#collect_attendance_subclass').html(options);
            loadAdminCollectSubjects();
        }
    });
});

$('#collect_attendance_subclass').on('change', loadAdminCollectSubjects);
$('#collect_attendance_subject').on('change', loadAdminCollectStudents);

function loadAdminCollectSubjects() {
    const classID = $('#collect_attendance_class').val();
    if (!classID) return;
    
    // For admin collection, we just need all subjects assigned to this class
    // We can use a simpler approach or the existing subjects endpoint
    $.post("{{ route('admin.exam_attendance.subjects') }}", {
        _token: "{{ csrf_token() }}",
        examID: $('#attendance_exam_id').val(),
        classID: classID,
        subclassID: $('#collect_attendance_subclass').val(),
        isAdmin: true // Flag to show all subjects regardless of assignment
    }, function(response) {
        if (response.success) {
            let options = '<option value="">Select Subject</option>';
            response.subjects.forEach(s => {
                options += `<option value="${s.subjectID}">${s.subject.subject_name}</option>`;
            });
            $('#collect_attendance_subject').html(options);
            $('#admin_collect_content').hide();
            $('#admin_collect_placeholder').show();
        }
    });
}

function loadAdminCollectStudents() {
    const examID = $('#attendance_exam_id').val();
    const classID = $('#collect_attendance_class').val();
    const subclassID = $('#collect_attendance_subclass').val();
    const subjectID = $('#collect_attendance_subject').val();
    
    if (!subjectID) {
        $('#admin_collect_content').hide();
        $('#admin_collect_placeholder').show();
        return;
    }
    
    $('#admin_collect_placeholder').hide();
    $('#admin_collect_content').show();
    $('#admin_students_list_container').html('<div class="text-center py-5"><i class="spinner-border"></i> Loading students...</div>');
    
    $.post("{{ route('admin.exam_attendance.admin_students') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        classID: classID,
        subclassID: subclassID,
        subjectID: subjectID
    }, function(response) {
        if (response.success) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover border" id="adminAttendanceTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Student Name</th>
                                <th class="text-center">Exam Number</th>
                                <th class="text-center" width="180">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            response.students.forEach((s, index) => {
                html += `
                    <tr class="student-row" data-name="${s.name.toLowerCase()}">
                        <td>${index + 1}</td>
                        <td class="fw-bold">${s.name}</td>
                        <td class="text-center small text-muted font-monospace">${s.examination_number || '-'}</td>
                        <td class="text-center">
                            <select class="form-control form-control-sm attendance-select" name="attendance[${s.studentID}]" style="border-radius: 0 !important; border: 1px solid #ddd;">
                                <option value="Present" ${s.status === 'Present' ? 'selected' : ''}>Present</option>
                                <option value="Absent" ${s.status === 'Absent' ? 'selected' : ''}>Absent</option>
                            </select>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            $('#admin_students_list_container').html(html);
        }
    });
}

window.filterAdminStudents = function() {
    const search = $('#adminStudentSearch').val().toLowerCase();
    $('.student-row').each(function() {
        const name = $(this).data('name');
        $(this).toggle(name.includes(search));
    });
}

window.markAllAdmin = function(status) {
    $(`#adminAttendanceTable .attendance-select`).each(function() {
        $(this).val(status);
    });
}

window.saveAdminAttendance = function() {
    const examID = $('#attendance_exam_id').val();
    const subjectID = $('#collect_attendance_subject').val();
    
    const records = [];
    $(`#adminAttendanceTable .attendance-select`).each(function() {
        const nameAttr = $(this).attr('name');
        const studentID = nameAttr.match(/\[(\d+)\]/)[1];
        records.push({
            studentID: studentID,
            status: $(this).val()
        });
    });
    
    if (records.length === 0) {
        Swal.fire('Info', 'No students found to save.', 'info');
        return;
    }
    
    Swal.fire({
        title: 'Saving...',
        didOpen: () => { Swal.showLoading(); }
    });
    
    $.post("{{ route('admin.exam_attendance.save') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        subjectID: subjectID,
        attendance: records
    }, function(response) {
        if (response.success) {
            Swal.fire('Success', 'Attendance saved successfully!', 'success');
        } else {
            Swal.fire('Error', response.error || 'Failed to save attendance', 'error');
        }
    });
}

// --- Reusable Download with Progress Logic ---
function performDownloadWithProgress(url, defaultFilename) {
    $('#downloadProgressModal').modal('show');
    $('#pdfProgressBar').css('width', '0%');
    $('#pdfProgressPercent').text('0%');

    let progress = 0;
    const interval = setInterval(function() {
        if (progress < 95) {
            let inc = 2 + Math.random() * 5;
            if (progress > 50) inc = 1 + Math.random() * 2;
            if (progress > 80) inc = 0.5 + Math.random();
            progress += inc;
            if (progress > 95) progress = 95;
            $('#pdfProgressBar').css('width', progress + '%');
            $('#pdfProgressPercent').text(Math.round(progress) + '%');
        }
    }, 400);

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
                a.download = defaultFilename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(downloadUrl);
                document.body.removeChild(a);
                $('#downloadProgressModal').modal('hide');
            }, 500);
        })
        .catch(error => {
            clearInterval(interval);
            $('#downloadProgressModal').modal('hide');
            Swal.fire('Error', 'Failed to generate PDF: ' + error.message, 'error');
        });
}

$(document).on('click', '.download-template-btn', function(e) {
    e.stopPropagation();
    const examID = $(this).data('exam-id');
    const subjectID = $(this).data('subject-id');
    const classID = $(this).data('class-id');
    const subclassID = $(this).data('subclass-id');
    
    const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}`;
    const filename = `Attendance_Template_${examID}_${subjectID}.pdf`;
    performDownloadWithProgress(url, filename);
});

window.downloadAdminTemplate = function() {
    const examID = $('#attendance_exam_id').val();
    const classID = $('#collect_attendance_class').val();
    const subclassID = $('#collect_attendance_subclass').val();
    const subjectID = $('#collect_attendance_subject').val();
    
    if (!classID || !subjectID) {
        Swal.fire('Info', 'Please select Class and Subject first', 'info');
        return;
    }
    
    const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${examID}&subjectID=${subjectID}&classID=${classID}&subclassID=${subclassID}`;
    const filename = `Attendance_Template_${classID}_${subjectID}.pdf`;
    performDownloadWithProgress(url, filename);
};

// Handle in-place attendance status changes in "Assigned Teachers" expansion
$(document).on('change', '.attendance-select-view', function() {
    const studentID = $(this).data('student-id');
    const subjectID = $(this).data('subject-id');
    const status = $(this).val();
    const examID = $('#attendance_exam_id').val();

    $.post("{{ route('admin.exam_attendance.save') }}", {
        _token: "{{ csrf_token() }}",
        examID: examID,
        subjectID: subjectID,
        attendance: [{ studentID: studentID, status: status }]
    }).done(function() {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Attendance updated',
            showConfirmButton: false,
            timer: 1500
        });
    });
});

// --- End Exam Attendance Logic ---

});
</script>

<!-- Exam Attendance Modal -->
<div class="modal fade" id="examAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="examAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down" role="document" style="max-width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="examAttendanceModalLabel">
                    <i class="bi bi-person-check"></i> Exam Attendance Management - <span id="attendance_exam_name"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs nav-justified" id="attendanceTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-3" id="assign-teacher-tab" data-toggle="tab" href="#assign-teacher" role="tab">
                            <i class="bi bi-person-plus"></i> Assign Teacher
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3" id="admin-collect-tab" data-toggle="tab" href="#admin-collect" role="tab">
                            <i class="bi bi-pencil-square"></i> Collect Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3" id="view-assigned-tab" data-toggle="tab" href="#view-assigned" role="tab">
                            <i class="bi bi-people"></i> Assigned Teachers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3" id="view-attendance-tab" data-toggle="tab" href="#view-attendance" role="tab">
                            <i class="bi bi-card-checklist"></i> View Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3" id="attendance-stats-tab" data-toggle="tab" href="#attendance-stats" role="tab">
                            <i class="bi bi-graph-up"></i> Statistics
                        </a>
                    </li>
                </ul>
                <div class="tab-content p-4" id="attendanceTabsContent">
                    <!-- Tab 1: Assign Teacher -->
                    <div class="tab-pane fade show active" id="assign-teacher" role="tabpanel">
                        <form id="assignAttendanceTeacherForm">
                            <input type="hidden" name="examID" id="attendance_exam_id">
                            <input type="hidden" name="assignmentID" id="attendance_assignment_id">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Select Class</label>
                                    <select class="form-control" name="classID" id="assign_attendance_class" required>
                                        <option value="">Select Class</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Select Subclass</label>
                                    <select class="form-control" name="subclassID" id="assign_attendance_subclass">
                                        <option value="all">All Subclasses</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Select Teacher</label>
                                    <select class="form-control" name="teacherID" id="assign_attendance_teacher" required>
                                        <option value="">Select Teacher</option>
                                        @foreach($teachers ?? [] as $teacher)
                                            <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Subjects</label>
                                <div id="attendance_subjects_container" class="row p-3 border rounded bg-light mx-0 shadow-sm" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa !important;">
                                    <div class="col-12 text-muted text-center py-5">
                                        <i class="bi bi-info-circle" style="font-size: 2rem;"></i><br>
                                        Please select a class first to see available subjects
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-4">
                                <button type="button" id="cancel_edit_assignment" class="btn btn-secondary btn-lg px-4 mr-2 shadow-sm" style="display: none;">
                                    Cancel Edit
                                </button>
                                <button type="submit" id="save_assignment_btn" class="btn btn-primary-custom btn-lg px-5 shadow-sm">
                                    <i class="bi bi-save"></i> Save Assignment & Notify Teacher
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Collect Attendance (Admin Direct) -->
                    <div class="tab-pane fade" id="admin-collect" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Class</label>
                                <select class="form-control" id="collect_attendance_class">
                                    <option value="">Select Class</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Subclass</label>
                                <select class="form-control" id="collect_attendance_subclass">
                                    <option value="all">All Subclasses</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Subject</label>
                                <select class="form-control" id="collect_attendance_subject">
                                    <option value="">Select Subject</option>
                                </select>
                            </div>
                        </div>

                        <div id="admin_collect_content" style="display: none;">
                            <div class="row mb-3 border-bottom pb-3">
                                <div class="col-12 col-md-5 mb-3 mb-md-0">
                                    <div class="search-box">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0" style="border-radius: 0 !important;"><i class="bi bi-search"></i></span>
                                            </div>
                                            <input type="text" id="adminStudentSearch" class="form-control border-left-0" placeholder="Search student name..." onkeyup="filterAdminStudents()" style="border-radius: 0 !important;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-7 text-md-right">
                                    <div class="btn-group w-100 w-md-auto">
                                        <button class="btn btn-outline-primary" onclick="downloadAdminTemplate()" style="border-radius: 0 !important;">
                                            <i class="bi bi-file-pdf"></i> Template
                                        </button>
                                        <button class="btn btn-outline-success" onclick="downloadAdminAttendance()" style="border-radius: 0 !important;">
                                            <i class="bi bi-download"></i> Record
                                        </button>
                                        <button class="btn btn-success" onclick="markAllAdmin('Present')" style="border-radius: 0 !important; background-color: #28a745; border-color: #28a745;">
                                            <i class="bi bi-check-all"></i> Present
                                        </button>
                                        <button class="btn btn-danger" onclick="markAllAdmin('Absent')" style="border-radius: 0 !important; background-color: #dc3545; border-color: #dc3545;">
                                            <i class="bi bi-x-circle"></i> Absent
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="admin_students_list_container">
                                <!-- Student list loaded via AJAX -->
                            </div>

                            <div class="text-right mt-4 pt-3 border-top">
                                <button class="btn btn-primary-custom btn-lg px-5 shadow-sm" onclick="saveAdminAttendance()">
                                    <i class="bi bi-cloud-arrow-up"></i> Save Attendance
                                </button>
                            </div>
                        </div>

                        <div id="admin_collect_placeholder" class="text-center py-5 text-muted">
                            <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                            <p class="mt-3">Select class, subclass, and subject to start collecting attendance.</p>
                        </div>
                    </div>

                    <!-- Tab 2: Assigned Teachers -->
                    <div class="tab-pane fade" id="view-assigned" role="tabpanel">
                        <div id="assigned_teachers_list" class="mt-2">
                            <!-- List of assigned teachers will be loaded here -->
                        </div>
                    </div>

                    <!-- Tab 3: View Attendance -->
                    <div class="tab-pane fade" id="view-attendance" role="tabpanel">
                        <div class="row mb-4 bg-light p-3 rounded mx-0">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="form-label fw-bold small text-uppercase">Filter Class</label>
                                <select class="form-control" id="view_att_class_filter">
                                    <option value="all">All Classes</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="form-label fw-bold small text-uppercase">Filter Subclass</label>
                                <select class="form-control" id="view_att_subclass_filter">
                                    <option value="all">All Subclasses</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Filter Subject</label>
                                <select class="form-control" id="view_att_subject_filter">
                                    <option value="all">All Subjects</option>
                                </select>
                            </div>
                        </div>

                        <div id="attendance_collected_summary">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                                <p class="mt-3">Loading attendance summary...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Statistics -->
                    <div class="tab-pane fade" id="attendance-stats" role="tabpanel">
                        <div class="row mb-4 bg-light p-3 rounded mx-0">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="form-label fw-bold small text-uppercase">Class</label>
                                <select class="form-control" id="stats_class_filter">
                                    <option value="all">All Classes</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="form-label fw-bold small text-uppercase">Subclass</label>
                                <select class="form-control" id="stats_subclass_filter">
                                    <option value="all">All Subclasses</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Subject</label>
                                <select class="form-control" id="stats_subject_filter">
                                    <option value="all">All Subjects</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-success text-white border-0 shadow-sm" style="border-radius: 0 !important;">
                                    <div class="card-body p-4 text-center">
                                        <h6 class="text-uppercase small mb-1 opacity-8">Total Present</h6>
                                        <h2 class="mb-0 fw-bold" id="stats_total_present">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-danger text-white border-0 shadow-sm" style="border-radius: 0 !important;">
                                    <div class="card-body p-4 text-center">
                                        <h6 class="text-uppercase small mb-1 opacity-8">Total Absent</h6>
                                        <h2 class="mb-0 fw-bold" id="stats_total_absent">0</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm" style="border-radius: 0 !important;">
                            <div class="card-body">
                                <h5 class="card-title text-primary-custom mb-4 font-weight-bold"><i class="bi bi-bar-chart"></i> Participation Analytics</h5>
                                <div style="height: 400px;">
                                    <canvas id="attendanceStatsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@include('includes.footer')
