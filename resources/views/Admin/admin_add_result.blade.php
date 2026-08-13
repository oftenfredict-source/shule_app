@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Premium Red Theme for Admin Add Results */
    body, .card, .btn, .form-control, .form-select, table {
        font-family: 'Century Gothic', Muli, sans-serif !important;
    }
    .bg-light-warning { background-color: #fff9f0 !important; }

    .subject-row:hover { background-color: rgba(243, 156, 18, 0.05); }

    .already-assigned-wrapper {
        opacity: 0.6;
        background: #f8f9fa;
        cursor: not-allowed;
        border-radius: 4px;
        padding: 2px 5px;
    }

    .bg-primary-custom { background-color: #940000 !important; color: #fff !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom { background-color: #940000 !important; border-color: #940000 !important; color: #fff !important; }
    .btn-primary-custom:hover { background-color: #b30000 !important; color: #fff !important; }
    .glass-widget {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
    }

    /* Progress Bar */
    .progress-container { width: 100%; background-color: #f3f3f3; border-radius: 8px; overflow: hidden; margin: 20px 0; }
    .progress-bar-premium { width: 0%; height: 12px; background: linear-gradient(90deg, #940000 0%, #d40000 50%, #940000 100%); background-size: 200% 100%; animation: gradient-pulse 2s infinite linear; border-radius: 8px; transition: width 0.4s ease-out; }
    @keyframes gradient-pulse { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
    .loading-text { font-weight: 600; color: #940000; font-size: 1.1rem; margin-bottom: 5px; }
    .percentage-text { font-weight: bold; font-size: 1.2rem; color: #333; }
    .enrollment-inactive-row {
        background-color: #eceff1 !important;
        opacity: 0.75;
        color: #607d8b;
    }
    .enrollment-inactive-row input,
    .enrollment-inactive-row button {
        pointer-events: none;
        background-color: #cfd8dc !important;
        cursor: not-allowed !important;
    }
    .enrollment-status-badge { font-size: 0.7rem; vertical-align: middle; }

    .duo-marks-status { display: inline-flex; align-items: center; gap: 6px; min-height: 28px; }
    .duo-marks-bullet {
        width: 12px; height: 12px; border-radius: 50%;
        background: #ced4da; display: inline-block;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
    }
    .duo-marks-bullet.is-loading { animation: duoPulse 0.9s ease-in-out infinite; background: #ffc107; }
    .duo-marks-bullet.duo-state-pass,
    .duo-marks-bullet.is-pass { background: #28a745 !important; }
    .duo-marks-bullet.duo-state-fail,
    .duo-marks-bullet.is-fail { background: #dc3545 !important; }
    .duo-marks-bullet.duo-state-invalid,
    .duo-marks-bullet.is-invalid { background: #ffc107 !important; }
    .duo-marks-label.duo-state-pass,
    .duo-marks-label.is-pass { color: #28a745 !important; }
    .duo-marks-label.duo-state-fail,
    .duo-marks-label.is-fail { color: #dc3545 !important; }
    @keyframes duoPulse { 0%,100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.25); opacity: 0.65; } }
    .duo-marks-label { font-size: 0.75rem; font-weight: 600; }
    .marks-input.duo-marks-ok {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.1rem rgba(40,167,69,0.15);
    }
    .marks-input.duo-marks-bad,
    .marks-input.marks-over-max {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.1rem rgba(220,53,69,0.15);
    }
    .marks-input.duo-marks-warn {
        border-color: #ffc107 !important;
    }
    .marks-max-error {
        display: none;
        color: #dc3545;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 2px;
        line-height: 1.2;
    }
    .marks-max-error.is-visible { display: block; }
    .marks-max-hint {
        display: block;
        color: #6c757d;
        font-size: 0.72rem;
        margin-top: 2px;
    }
    .results-tools-bar {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px 14px;
        margin-top: 12px;
        margin-bottom: 8px;
    }
    .results-tools-bar label {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: #495057;
    }
    .results-excel-actions .btn { margin-left: 4px; margin-top: 4px; }
    tr.result-student-row.is-search-hidden,
    tr.question-details-row.is-search-hidden {
        display: none !important;
    }

    /* View / Add Results modals — visible branded scrollbar */
    #viewResultsModal .modal-body,
    #viewResultsModal #resultsModalBody,
    #addEditResultsModal .modal-body,
    #addEditResultsModal #addEditResultsModalBody {
        max-height: 75vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin;
        scrollbar-color: #940000 #f1f1f1;
    }
    #viewResultsModal .modal-body::-webkit-scrollbar,
    #viewResultsModal #resultsModalBody::-webkit-scrollbar,
    #addEditResultsModal .modal-body::-webkit-scrollbar,
    #addEditResultsModal #addEditResultsModalBody::-webkit-scrollbar {
        width: 8px !important;
        display: block !important;
    }
    #viewResultsModal .modal-body::-webkit-scrollbar-track,
    #addEditResultsModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #viewResultsModal .modal-body::-webkit-scrollbar-thumb,
    #addEditResultsModal .modal-body::-webkit-scrollbar-thumb {
        background: #940000;
        border-radius: 4px;
    }
    body.modal-open { overflow: hidden !important; }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.31/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary-custom rounded">
            <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Add Results (Admin)</h4>
        </div>
    </div>

    <!-- Global Result Entry Toggle -->
    @if($user_type == 'Admin')
    <div class="card border-0 shadow-sm mb-4 glass-widget" style="border-left: 5px solid #940000 !important;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="form-check form-switch me-4" style="padding-left: 3.5em;">
                        <input class="form-check-input" type="checkbox" id="globalTeacherResultToggle" {{ $resultButtonStatus ? 'checked' : '' }}
                               style="width: 3em; height: 1.5em; cursor: pointer; margin-left: -3.5em;">
                    </div>
                    <div>
                        <h5 class="mb-0 text-primary-custom fw-bold" style="font-size: 1.1rem;">Global Teacher Result Entry</h5>
                        <p class="text-muted small mb-0 d-none d-md-block">Enable or Disable the "Add Result" button for all teachers.</p>
                    </div>
                </div>
                <div class="ms-auto">
                    <button type="button" class="btn btn-primary-custom" id="assignTeacherBtn" data-toggle="modal" data-target="#assignTeacherModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Assign Teacher
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Teachers List -->
    <div class="card border-0 shadow-sm mb-4 glass-widget" id="assignmentsSection">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
            <h6 class="mb-0 text-primary-custom fw-bold"><i class="bi bi-list-task me-2"></i> Active Teacher Assignments</h6>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#assignmentsCollapse">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="assignmentsCollapse">
            <div class="card-body border-bottom bg-light py-2">
                <form id="assignmentFilterForm" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold">Teacher</label>
                        <select class="form-select form-select-sm" name="teacherID" id="filter_teacherID">
                            <option value="">All Teachers</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Year</label>
                        <select class="form-select form-select-sm" name="year" id="filter_year">
                            <option value="">All Years</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Term</label>
                        <select class="form-select form-select-sm" name="term" id="filter_term">
                            <option value="">All Terms</option>
                            <option value="first_term">First Term</option>
                            <option value="second_term">Second Term</option>
                            <option value="all_terms">All Terms (Tests)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Exam</label>
                        <select class="form-select form-select-sm" name="examID" id="filter_examID">
                            <option value="">All Exams</option>
                            @foreach($availableExams as $exam)
                                <option value="{{ $exam->examID }}" data-year="{{ $exam->year }}" data-term="{{ $exam->term }}">{{ $exam->exam_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-primary-custom w-100" id="filterAssignmentsBtn">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table small">
                        <thead class="table-light">
                            <tr>
                                <th>Teacher</th>
                                <th>Class & Subclass</th>
                                <th>Subjects</th>
                                <th>Examination</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentsTableBody">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Form -->
    <div class="card glass-widget mb-4">
        <div class="card-body">
            <form id="adminResultFilterForm">
                <div class="row align-items-end">
                    <!-- Class Filter -->
                    <div class="col-md-3 mb-3">
                        <label class="small fw-bold text-muted"><i class="bi bi-columns"></i> Main Class</label>
                        <select class="form-control" id="classID" name="classID" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subclass Filter -->
                    <div class="col-md-3 mb-3">
                        <label class="small fw-bold text-muted"><i class="bi bi-list-ul"></i> Subclass (Optional)</label>
                        <select class="form-control" id="subclassID" name="subclassID">
                            <option value="">All Subclasses</option>
                            @foreach($subclasses as $subclass)
                                <option value="{{ $subclass->subclassID }}" data-class="{{ $subclass->classID }}">{{ $subclass->display_name ?? ($subclass->class_name . ' ' . $subclass->subclass_name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subject Filter -->
                    <div class="col-md-3 mb-3">
                        <label class="small fw-bold text-muted"><i class="bi bi-book"></i> Subject</label>
                        <select class="form-control" id="subjectID" name="subjectID" required>
                            <option value="">Select Subject</option>
                            @foreach($schoolSubjects as $subject)
                                @php
                                    $isDuoOpt = !empty($subject->is_duo) && $subject->components && $subject->components->count() > 0;
                                @endphp
                                <option value="{{ $subject->subjectID }}"
                                        data-is-duo="{{ $isDuoOpt ? '1' : '0' }}"
                                        data-subject-name="{{ $subject->subject_name }}">
                                    {{ $subject->subject_name }}{{ $isDuoOpt ? ' [DUO]' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="duoSubjectHint" style="display:none;">
                            <i class="bi bi-layers"></i> This is a <strong>DUO</strong> subject — you will select a paper before entering marks.
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-3 mb-3">
                        <div class="d-flex">
                            <button type="button" class="btn btn-primary-custom flex-fill" id="btnStartAddResult" title="Start Add Result">
                                <i class="bi bi-pencil-square me-1"></i> Add
                            </button>
                            <button type="button" class="btn btn-success flex-fill ml-2" id="btnViewResult" title="View Result">
                                <i class="bi bi-eye me-1"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict";

        // Auto-filter subclasses based on class
        $('#classID').on('change', function() {
            var classID = $(this).val();
            var $subclassSelect = $('#subclassID');

            // Keep the "All" option
            $subclassSelect.html('<option value="">All Subclasses</option>');

            if (classID) {
                // Get all original subclasses from a template or hidden data
                @foreach($subclasses as $sub)
                    if ("{{ $sub->classID }}" == classID) {
                        $subclassSelect.append('<option value="{{ $sub->subclassID }}">{{ $sub->display_name ?? ($sub->class_name . " " . $sub->subclass_name) }}</option>');
                    }
                @endforeach
            } else {
                // If no class selected, show all subclasses or keep it empty
                @foreach($subclasses as $sub)
                    $subclassSelect.append('<option value="{{ $sub->subclassID }}">{{ $sub->display_name ?? ($sub->class_name . " " . $sub->subclass_name) }}</option>');
                @endforeach
            }
        });

        $('#subjectID').on('change', function() {
            const isDuo = String($(this).find('option:selected').data('is-duo')) === '1';
            $('#duoSubjectHint').toggle(isDuo);
        });

        // Helper to get class_subject_id from server
        window.fetchClassSubjectAndExecute = function(action) {
            const classID = $('#classID').val();
            const subclassID = $('#subclassID').val();
            const subjectID = $('#subjectID').val();

            if (!classID || !subjectID) {
                Swal.fire('Error', 'Please select a Class and Subject.', 'error');
                return;
            }

            // Lock Form 5/6 grade scale from selected class (non-duo subjects)
            const className = $('#classID option:selected').text().trim();
            window.currentResultClassName = className;
            window.currentViewMainClass = className;
            window.currentResultIsALevel = isALevelClassName(className);

            Swal.fire({
                title: 'Mapping Subject...',
                html: 'Finding the correct assignment for selected class and subject.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("admin.get_class_subject_id") }}',
                method: 'GET',
                data: { classID, subclassID, subjectID },
                success: function(res) {
                    Swal.close();
                    if (res.success && res.class_subjectID) {
                        // Store all_subclasses state globally so addResults/viewResults can use it
                        window._allSubclassesMode = !!res.all_subclasses;
                        window._classSubjectMap = res.class_subject_map || {};
                        if (action === 'add') {
                            if (typeof addResults === 'function') addResults(res.class_subjectID);
                        } else if (action === 'view') {
                            if (typeof viewResults === 'function') viewResults(res.class_subjectID);
                        }
                    } else {
                        Swal.fire('Error', 'Could not map this subject. Please try again.', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Could not fetch subject mapping.', 'error');
                }
            });
        };

        $('#btnStartAddResult').on('click', function() {
            fetchClassSubjectAndExecute('add');
        });

        $('#btnViewResult').on('click', function() {
            fetchClassSubjectAndExecute('view');
        });

    })(jQuery);
</script>

<!-- BELOW WE WILL APPEND THE MODALS AND JS FROM teacher_subjects.blade.php -->


<!-- View Results Modal -->
<div class="modal" id="viewResultsModal" role="dialog" aria-labelledby="viewResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewResultsModalLabel">
                    <i class="bi bi-clipboard-check"></i> Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="resultsModalBody" style="max-height: 75vh; overflow-y: auto; overflow-x: hidden;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Results Modal -->
<div class="modal" id="addEditResultsModal" role="dialog" aria-labelledby="addEditResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addEditResultsModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="addEditResultsModalBody" style="max-height: 75vh; overflow-y: auto; overflow-x: hidden;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Excel Modal -->
<div class="modal" id="uploadExcelModal" role="dialog" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="uploadExcelModalLabel">
                    <i class="bi bi-upload"></i> Upload Excel Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="uploadExcelForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="upload_class_subject_id" name="class_subject_id">
                    <input type="hidden" id="upload_exam_id" name="exam_id">
                    <div class="form-group">
                        <label>Select Microsoft Excel Worksheet File <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
                            <label class="custom-file-label" for="excel_file" id="excel_file_label">
                                <i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Only Microsoft Excel Worksheet files (.xlsx or .xls) are allowed.
                        </small>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> Upload fills Marks into the form and <strong>auto-calculates grade/remark</strong>.
                        You can still edit below, then click <strong>Save Results</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary-custom" id="btnLoadExcelIntoForm">
                        <i class="bi bi-upload"></i> Load into Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assignTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i> Assign Teacher to Add Results</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form id="assignTeacherForm">
                    <input type="hidden" id="assignment_id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">1. Select Teacher</label>
                            <select class="form-select select2-modal" id="assign_teacherID" name="teacherID" required style="width: 100%;">
                                <option value="">Loading teachers...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">2. Select Class</label>
                            <select class="form-select" id="assign_classID" name="classID" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">3. Select Subclass</label>
                            <select class="form-select" id="assign_subclassID" name="subclassID">
                                <option value="all">All Subclasses</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">4. Select Term</label>
                            <select class="form-select" id="assign_term" name="term">
                                <option value="">Select Term</option>
                                <option value="first_term">First Term</option>
                                <option value="second_term">Second Term</option>
                                <option value="all_terms">All Terms (For Weekly/Monthly Tests)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">5. Select Examination</label>
                            <select class="form-select" id="assign_examID" name="examID" required>
                                <option value="">Select Exam</option>
                                @foreach($availableExams as $exam)
                                    @if($exam->enter_result == 1)
                                        <option value="{{ $exam->examID }}" data-term="{{ $exam->term }}">{{ $exam->exam_name }} ({{ $exam->year }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold">6. Select Allowed Subjects</label>
                            <div class="p-3 border rounded bg-light" id="assign_subjects_container" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-muted small">Select class to see subjects.</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="saveAssignmentBtn">
                    <i class="bi bi-check-circle me-1"></i> Save Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Ensure jQuery is available
if (typeof $ === 'undefined') {
    var $ = jQuery;
}

const isSecondarySchool = @json(strtolower($schoolType ?? 'Secondary')) === 'secondary';
const questionColspan = isSecondarySchool ? 7 : 6;
// Helper for Premium Progress Bar Loading
// LocalStorage Cache Logic for Offline Persistence
const RESULTS_CACHE_PREFIX = 'shulelink_marks_cache_';

function getResultsCacheKey() {
    const classSubjectID = jQuery('#class_subject_id').val();
    const examID = jQuery('#exam_id').val();
    const testWeek = jQuery('#test_week').val() || 'none';
    // Duo papers must be isolated — without componentID, PHY1/PHY2 share one cache and restore wrong totals
    const componentID = jQuery('#component_id').val() || window.selectedComponentID || '0';
    if (!classSubjectID || !examID) return null;
    return `${RESULTS_CACHE_PREFIX}${classSubjectID}_${examID}_${testWeek}_c${componentID}`;
}

function normalizeCacheMark(marks) {
    if (marks === null || marks === undefined || marks === '') return '';
    const n = parseFloat(marks);
    if (isNaN(n)) return '';
    return Number.isInteger(n) ? String(n) : String(n);
}

function saveMarkToCache(studentID, fieldKey, marks) {
    const key = getResultsCacheKey();
    if (!key || !studentID) return;

    let cache = JSON.parse(localStorage.getItem(key) || '{}');
    if (!cache[studentID]) cache[studentID] = {};

    const normalized = normalizeCacheMark(marks);
    if (normalized === '') {
        delete cache[studentID][fieldKey];
        if (Object.keys(cache[studentID]).length === 0) {
            delete cache[studentID];
        }
    } else {
        cache[studentID][fieldKey] = normalized;
    }
    localStorage.setItem(key, JSON.stringify(cache));
}

function restoreMarksFromCache() {
    const key = getResultsCacheKey();
    if (!key) return;

    const cache = JSON.parse(localStorage.getItem(key) || '{}');
    if (Object.keys(cache).length === 0) return;

    let restoredCount = 0;
    const studentsWithCachedMain = {};

    Object.keys(cache).forEach(studentID => {
        const studentMarks = cache[studentID] || {};

        // 1) Restore question-level marks into memory first
        Object.keys(studentMarks).forEach(qID => {
            if (qID === 'main') return;
            if (!examQuestionData.marksByStudent[studentID]) {
                examQuestionData.marksByStudent[studentID] = {};
            }
            const qVal = normalizeCacheMark(studentMarks[qID]);
            if (qVal === '') return;
            examQuestionData.marksByStudent[studentID][qID] = qVal;

            const $qInput = jQuery(`.question-mark-input[data-student="${studentID}"][data-question-id="${qID}"]`);
            if ($qInput.length) {
                $qInput.val(qVal);
                const $optionalCheck = jQuery(`.optional-select[data-student="${studentID}"][data-question-id="${qID}"]`);
                if ($optionalCheck.length && parseFloat(qVal) > 0) {
                    $optionalCheck.prop('checked', true);
                    $qInput.prop('disabled', false);
                }
                restoredCount++;
            }
        });

        // 2) Prefer explicit main total from cache (do NOT let question recalc overwrite it)
        if (studentMarks['main'] !== undefined && studentMarks['main'] !== null && studentMarks['main'] !== '') {
            const mainVal = normalizeCacheMark(studentMarks['main']);
            const $mainInput = jQuery(`#marks_${studentID}`);
            if ($mainInput.length && mainVal !== '') {
                $mainInput.val(mainVal);
                autoCalculateGrade(studentID);
                studentsWithCachedMain[studentID] = true;
                restoredCount++;
            }
        } else {
            // No main cached — derive from question marks only
            updateStudentTotal(studentID);
        }
    });

    // Recalc totals only for students without an explicit cached main
    if (typeof updateTotalsFromCache === 'function') {
        if (!examQuestionData.marksByStudent) return;
        Object.keys(examQuestionData.marksByStudent).forEach(function(studentID) {
            if (studentsWithCachedMain[studentID]) return;
            const questionMarks = examQuestionData.marksByStudent[studentID] || {};
            let total = 0;
            let hasMarks = false;
            Object.keys(questionMarks).forEach(function(questionId) {
                const value = parseFloat(questionMarks[questionId]);
                if (!isNaN(value)) {
                    total += value;
                    hasMarks = true;
                }
            });
            if (hasMarks) {
                const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
                jQuery(`#marks_${studentID}`).val(displayTotal);
                autoCalculateGrade(studentID);
                saveMarkToCache(studentID, 'main', displayTotal);
                restoredCount++;
            }
        });
    }

    if (restoredCount > 0) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Unsaved marks restored from local cache.',
            showConfirmButton: false,
            timer: 4000
        });
    }
}

function clearResultsCache() {
    const key = getResultsCacheKey();
    if (key) localStorage.removeItem(key);
}

// Attach Event Listeners for Caching + realtime max validation
jQuery(document).on('input', '.marks-input', function() {
    const studentID = jQuery(this).data('student');
    const marks = jQuery(this).val();
    saveMarkToCache(studentID, 'main', marks);
    validateDirectMarksInput(jQuery(this));
});

jQuery(document).on('blur', '.marks-input', function() {
    validateDirectMarksInput(jQuery(this));
});

// Enter on marks: next row; last row saves
jQuery(document).on('keydown', '#resultsForm .marks-input', function(e) {
    if (e.key !== 'Enter' && e.which !== 13) return;
    e.preventDefault();
    e.stopPropagation();
    const $current = jQuery(this);
    if ($current.prop('disabled')) return;
    validateDirectMarksInput($current);
    const $editable = jQuery('#resultsForm .marks-input').filter(function() {
        const $el = jQuery(this);
        if ($el.prop('disabled')) return false;
        if ($el.closest('tr.result-student-row').hasClass('is-search-hidden')) return false;
        if ($el.prop('readonly') && !isDuoResultMode() && !isDirectMarksEntryMode()) return false;
        return true;
    });
    const idx = $editable.index($current);
    if (idx < 0) return;
    if (idx < $editable.length - 1) {
        const $next = $editable.eq(idx + 1);
        $next.focus().select();
        const $body = jQuery('#addEditResultsModalBody');
        if ($body.length && $next.length) {
            const nextTop = $next.closest('tr')[0] ? ($next.closest('tr')[0].offsetTop - 80) : 0;
            $body.stop(true).animate({ scrollTop: Math.max(nextTop, 0) }, 120);
        }
        return;
    }
    jQuery('#resultsForm').trigger('submit');
});

function showPremiumLoading(targetID, title = "Preparing Data...", detail = "Fetching information from server...") {
    jQuery(`#${targetID}`).html(`
        <div class="p-5 text-center">
            <div class="loading-text"><i class="bi bi-hourglass-split"></i> <span>${title}</span></div>
            <div class="percentage-text progress-percentage" id="perc-${targetID}">0%</div>
            <div class="progress-container">
                <div class="progress-bar-premium" id="prog-${targetID}" style="width: 0%"></div>
            </div>
            <small class="text-muted">${detail}</small>
        </div>
    `);

    let progress = 0;
    const interval = setInterval(() => {
        if (progress >= 40) { // Default starting point before response
            clearInterval(interval);
        } else {
            progress += 5;
            jQuery(`#prog-${targetID}`).css('width', progress + '%');
            jQuery(`#perc-${targetID}`).text(progress + '%');
        }
    }, 40);

    return {
        update: (target, text) => {
            const step = 5;
            const subInterval = setInterval(() => {
                if (progress >= target) {
                    clearInterval(subInterval);
                } else {
                    progress += step;
                    if (progress > target) progress = target;
                    jQuery(`#prog-${targetID}`).css('width', progress + '%');
                    jQuery(`#perc-${targetID}`).text(progress + '%');
                    if (text) jQuery(`#${targetID} .loading-text span`).text(text);
                }
            }, 30);
        }
    };
}

let examQuestionData = {
    questions: [],
    marksByStudent: {},
    maxTotal: 0,
    optionalTotal: 0,
    optionalRanges: []
};

jQuery(document).ready(function($) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize search functionality
    initializeSubjectSearch();

    // Global Teacher Result Entry Toggle
    $('#globalTeacherResultToggle').on('change', function() {
        const isEnabled = this.checked;
        const label = $('#globalToggleLabel');

        Swal.fire({
            title: isEnabled ? 'Enable Teacher Result Entry?' : 'Disable Teacher Result Entry?',
            text: isEnabled ? 'Teachers will be able to add and edit results.' : 'The "Add Result" button will be hidden for all teachers.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch("{{ route('toggle_teacher_result_permission') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.checked = data.status;
                        Swal.fire({ icon: 'success', title: 'Updated!', text: data.message, confirmButtonColor: '#940000' });
                    } else {
                        this.checked = !isEnabled;
                        Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'Failed to update setting.', confirmButtonColor: '#940000' });
                    }
                })
                .catch(err => {
                    this.checked = !isEnabled;
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.', confirmButtonColor: '#940000' });
                });
            } else {
                this.checked = !isEnabled;
            }
        });
    });

    // Load existing assignments if Admin
    if ("{{ $user_type }}" === 'Admin') {
        loadTeacherAssignmentsTable();
        // Also populate the filter teacher select
        fetch("{{ route('admin.get_teachers_for_assignment') }}")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    data.teachers.forEach(t => {
                        jQuery('#filter_teacherID').append(`<option value="${t.id}">${t.first_name} ${t.last_name}</option>`);
                    });
                }
            });
    }

    // Assignment Filtering
    jQuery(document).on('click', '#filterAssignmentsBtn', function(e) {
        e.preventDefault();
        const $btn = jQuery(this);
        const originalHtml = $btn.html();

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        const teacherID = jQuery('#filter_teacherID').val();
        const year = jQuery('#filter_year').val();
        const term = jQuery('#filter_term').val();
        const examID = jQuery('#filter_examID').val();

        loadTeacherAssignmentsTable({ teacherID, year, term, examID });

        // Restore button after a short delay (the table will show its own loading)
        setTimeout(() => {
            $btn.prop('disabled', false).html(originalHtml);
        }, 1000);
    });

    jQuery('#assignmentFilterForm').on('submit', function(e) {
        e.preventDefault();
        jQuery('#filterAssignmentsBtn').click();
    });

    // View Details Listener
    jQuery(document).on('click', '.view-assignment-details', function() {
        const d = jQuery(this).data();
        Swal.fire({
            title: '<span class="text-primary-custom">Assignment Details</span>',
            html: `
                <div class="text-start mt-3">
                    <p><strong>Teacher:</strong> ${d.teacher}</p>
                    <p><strong>Class:</strong> ${d.class} (${d.subclass})</p>
                    <p><strong>Exam:</strong> ${d.exam} (${d.year})</p>
                    <p><strong>Term:</strong> ${d.term}</p>
                    <hr>
                    <p class="fw-bold mb-1 text-muted">Assigned Subjects:</p>
                    <div class="p-2 border rounded bg-light small">
                        ${d.subjects.split(', ').map(s => `<span class="badge bg-primary-custom m-1">${s}</span>`).join('')}
                    </div>
                </div>
            `,
            confirmButtonText: 'Close',
            confirmButtonColor: '#940000',
            width: '500px'
        });
    });

    // Modal Flow
    jQuery(document).on('click', '#assignTeacherBtn', function(e) {
        e.preventDefault();
        openAssignTeacherModal();
    });

    function openAssignTeacherModal(id = null) {
        const $modal = jQuery('#assignTeacherModal');
        const $form = jQuery('#assignTeacherForm');
        const $title = $modal.find('.modal-title');
        const $btn = jQuery('#saveAssignmentBtn');

        $form[0].reset();
        jQuery('#assignment_id').val('');
        jQuery('#assign_subjects_container').html('<div class="text-muted small">Select class to see subjects.</div>');

        if (id) {
            $title.html('<i class="bi bi-pencil-square me-2"></i> Edit Teacher Assignment');
            $btn.html('<i class="bi bi-check-circle me-1"></i> Update Assignment');

            Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch(`{{ url('admin/get-assignment-details') }}/${id}`)
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        const a = data.assignment;
                        jQuery('#assignment_id').val(a.id);

                        // Load teachers first then select
                        loadTeachersAssignment(() => {
                            jQuery('#assign_teacherID').val(a.teacherID).trigger('change');
                        });

                        jQuery('#assign_classID').val(a.classID);

                        // Load subclasses
                        const $subSelect = jQuery('#assign_subclassID');
                        $subSelect.html('<option value="all">All Subclasses</option>');
                        @foreach($subclasses as $sub)
                            if ("{{ $sub->classID }}" == a.classID) {
                                $subSelect.append('<option value="{{ $sub->subclassID }}">{{ $sub->display_name ?? ($sub->class_name . " " . $sub->subclass_name) }}</option>');
                            }
                        @endforeach
                        $subSelect.val(a.subclassID || 'all');

                        // Set Term and Exam
                        if (a.examID) {
                            // Find the exam to get its term
                            const $examOption = jQuery(`#assign_examID option[value="${a.examID}"]`);
                            if ($examOption.length) {
                                const term = $examOption.data('term');
                                jQuery('#assign_term').val(term || '').trigger('change');
                                jQuery('#assign_examID').val(a.examID);
                            }
                        }

                        // Load subjects and check the ones assigned
                        loadSubjectsForAssignment(a.subject_ids);

                        $modal.modal('show');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        } else {
            $title.html('<i class="bi bi-person-plus-fill me-2"></i> Assign Teacher to Add Results');
            $btn.html('<i class="bi bi-check-circle me-1"></i> Save Assignment');
            loadTeachersAssignment();
            $modal.modal('show');
        }
    }

    jQuery(document).on('click', '.edit-assignment', function() {
        const id = jQuery(this).data('id');
        openAssignTeacherModal(id);
    });

    jQuery('#assign_classID').on('change', function() {
        const classID = jQuery(this).val();
        // Load subclasses
        const $subSelect = jQuery('#assign_subclassID');
        $subSelect.html('<option value="all">All Subclasses</option>');
        @foreach($subclasses as $sub)
            if ("{{ $sub->classID }}" == classID) {
                $subSelect.append('<option value="{{ $sub->subclassID }}">{{ $sub->display_name ?? ($sub->class_name . " " . $sub->subclass_name) }}</option>');
            }
        @endforeach
        loadSubjectsForAssignment();
    });

    jQuery('#assign_subclassID').on('change', function() {
        loadSubjectsForAssignment();
    });

    jQuery('#assign_examID').on('change', function() {
        loadSubjectsForAssignment();
    });

    // Filter exams in filter form based on year and term
    jQuery('#filter_year, #filter_term').on('change', function() {
        const year = jQuery('#filter_year').val();
        const term = jQuery('#filter_term').val();
        const $examSelect = jQuery('#filter_examID');

        $examSelect.val(''); // Reset selected exam

        $examSelect.find('option').each(function() {
            const $opt = jQuery(this);
            if ($opt.val() === '') {
                $opt.show();
                return;
            }

            const optYear = $opt.data('year');
            const optTerm = $opt.data('term');

            let show = true;
            if (year && optYear != year) show = false;
            if (term && optTerm != term) show = false;

            if (show) $opt.show();
            else $opt.hide();
        });
    });

    // Filter exams in MODAL based on selected term
    jQuery('#assign_term').on('change', function() {
        const term = jQuery(this).val();
        const $examSelect = jQuery('#assign_examID');

        $examSelect.val(''); // Reset exam selection

        if (!term) {
            $examSelect.find('option').show();
        } else {
            $examSelect.find('option').each(function() {
                const examTerm = jQuery(this).data('term');
                if (jQuery(this).val() === '') {
                    jQuery(this).show();
                } else if (examTerm === term || examTerm === 'all_terms' || term === 'all_terms') {
                    jQuery(this).show();
                } else {
                    jQuery(this).hide();
                }
            });
        }
    });

    function loadTeachersAssignment(callback = null) {
        const $select = jQuery('#assign_teacherID');
        $select.html('<option value="">Loading...</option>');

        fetch("{{ route('admin.get_teachers_for_assignment') }}")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $select.html('<option value="">Select Teacher</option>');
                    data.teachers.forEach(t => {
                        $select.append(`<option value="${t.id}">${t.first_name} ${t.middle_name || ''} ${t.last_name}</option>`);
                    });
                    if (callback) callback();
                }
            });
    }

    function loadSubjectsForAssignment(selectedIds = []) {
        const classID = jQuery('#assign_classID').val();
        const subclassID = jQuery('#assign_subclassID').val();
        const examID = jQuery('#assign_examID').val();
        const $container = jQuery('#assign_subjects_container');

        if (!classID) {
            $container.html('<div class="text-muted small">Select class to see subjects.</div>');
            return;
        }

        // Reset subclass to 'all' label if it has no selection yet (first load on class change)
        const $subSelectCurrent = jQuery('#assign_subclassID');
        if (!$subSelectCurrent.val()) {
            $subSelectCurrent.val('all');
        }

        $container.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Loading subjects...</div>');

        fetch(`{{ route('admin.get_subjects_for_assignment') }}?classID=${classID}&subclassID=${subclassID}&examID=${examID || ''}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.subjects.length > 0) {
                    let html = '<div class="row">';
                    data.subjects.forEach(s => {
                        const sID = String(s.subjectID);
                        const isChecked = selectedIds.some(id => String(id) === sID);
                        const isAlreadyAssigned = data.alreadyAssignedIds.some(id => String(id) === sID) && !isChecked;

                        html += `
                            <div class="col-md-6 mb-2">
                                <div class="form-check d-flex align-items-center ${isAlreadyAssigned ? 'already-assigned-wrapper' : ''}">
                                    <input class="form-check-input subject-checkbox" type="checkbox" value="${s.subjectID}" id="subject_${s.subjectID}" ${isChecked ? 'checked' : ''} ${isAlreadyAssigned ? 'disabled' : ''}>
                                    <label class="form-check-label ms-2 ${isAlreadyAssigned ? 'text-muted' : ''}" for="subject_${s.subjectID}">
                                        ${s.subject_name}
                                        ${isAlreadyAssigned ? '<span class="badge bg-secondary text-white ms-2" style="font-size: 0.6rem;">Already Assigned</span>' : ''}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $container.html(html);
                } else {
                    $container.html('<div class="alert alert-warning p-2 small">No subjects found for this selection.</div>');
                }
            });
    }

    jQuery('#saveAssignmentBtn').on('click', function() {
        const id = jQuery('#assignment_id').val();
        const teacherID = jQuery('#assign_teacherID').val();
        const classID = jQuery('#assign_classID').val();
        const subclassID = jQuery('#assign_subclassID').val();
        const examID = jQuery('#assign_examID').val();
        const subject_ids = jQuery('.subject-checkbox:checked').map(function() { return jQuery(this).val(); }).get();

        if (!teacherID || !classID || !examID || subject_ids.length === 0) {
            Swal.fire('Error', 'Please fill all required fields and select at least one subject.', 'error');
            return;
        }

        Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch("{{ route('admin.assign_teacher_to_result') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id, teacherID, classID, subclassID, examID, subject_ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', data.message, 'success');
                jQuery('#assignTeacherModal').modal('hide');
                jQuery('#assignTeacherForm')[0].reset();
                loadTeacherAssignmentsTable();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });

    function loadTeacherAssignmentsTable(filters = {}) {
        const $section = jQuery('#assignmentsSection');
        const $tbody = jQuery('#assignmentsTableBody');

        // Show loading state
        $tbody.html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading assignments...</td></tr>');

        let query = jQuery.param(filters);
        let url = "{{ route('admin.get_teacher_assignments') }}";
        if (query) url += "?" + query;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.assignments.length > 0) {
                    let html = '';
                    data.assignments.forEach(a => {
                        html += `
                            <tr>
                                <td><strong>${a.teacher.first_name} ${a.teacher.last_name}</strong></td>
                                <td>${a.class.class_name} ${a.subclass ? '('+a.subclass.subclass_name+')' : '(All)'}</td>
                                <td><span class="text-truncate d-inline-block" style="max-width: 200px;" title="${a.subject_names}">${a.subject_names}</span></td>
                                <td>${a.exam.exam_name} (${a.exam.year})</td>
                                <td>
                                    <span class="badge ${a.status ? 'bg-success' : 'bg-danger'}">${a.status ? 'Enabled' : 'Disabled'}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info view-assignment-details"
                                            data-teacher="${a.teacher.first_name} ${a.teacher.last_name}"
                                            data-class="${a.class.class_name}"
                                            data-subclass="${a.subclass ? a.subclass.subclass_name : 'All'}"
                                            data-exam="${a.exam.exam_name}"
                                            data-year="${a.exam.year}"
                                            data-term="${(a.exam.term === 'first_term' ? 'First Term' : (a.exam.term === 'second_term' ? 'Second Term' : (a.exam.term === 'all_terms' ? 'All Terms' : (a.exam.term || 'N/A'))))}"
                                            data-subjects="${a.subject_names}"
                                            title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning edit-assignment" data-id="${a.id}" title="Edit Assignment">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary toggle-assignment" data-id="${a.id}" title="Toggle Status">
                                            <i class="bi bi-toggle-${a.status ? 'on' : 'off'}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-assignment" data-id="${a.id}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $tbody.html(html);

                    // Initialize DataTable
                    if (jQuery.fn.DataTable && !jQuery('#assignmentsTable').hasClass('dataTable')) {
                        jQuery('#assignmentsTable').DataTable({
                            "pageLength": 25,
                            "order": [[0, "asc"]],
                            "language": {
                                "search": "Search:",
                                "lengthMenu": "Show _MENU_ entries",
                                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                                "paginate": {
                                    "first": "First",
                                    "last": "Last",
                                    "next": "Next",
                                    "previous": "Previous"
                                }
                            }
                        });
                    }
                } else {
                    $tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted">No teacher assignments found.</td></tr>');
                }
            })
            .catch(err => {
                $tbody.html('<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle"></i> Failed to load assignments.</td></tr>');
            });
    }

    jQuery(document).on('click', '.toggle-assignment', function() {
        const id = jQuery(this).data('id');
        fetch("{{ route('admin.toggle_assignment_status') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        }).then(res => res.json()).then(data => {
            if (data.success) loadTeacherAssignmentsTable();
        });
    });

    jQuery(document).on('click', '.delete-assignment', function() {
        const id = jQuery(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This assignment will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('admin.delete_assignment') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                }).then(res => res.json()).then(data => {
                    if (data.success) loadTeacherAssignmentsTable();
                });
            }
        });
    });
});

// Initialize Subject Search
function initializeSubjectSearch() {
    jQuery('#subjectSearchInput').on('keyup', function() {
        var searchTerm = jQuery(this).val().toLowerCase().trim();

        if (searchTerm.length > 0) {
            jQuery('#clearSearchBtn').show();
        } else {
            jQuery('#clearSearchBtn').hide();
        }

        // Filter subject cards
        jQuery('.subject-item').each(function() {
            var subjectName = jQuery(this).data('subject-name') || '';
            var subjectCode = jQuery(this).data('subject-code') || '';
            var className = jQuery(this).data('class-name') || '';

            var matches = subjectName.includes(searchTerm) ||
                         subjectCode.includes(searchTerm) ||
                         className.includes(searchTerm);

            if (matches) {
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        });

        // Show message if no results
        var visibleCount = jQuery('.subject-item:visible').length;

        if (searchTerm.length > 0 && visibleCount === 0) {
            if (jQuery('#noResultsMessage').length === 0) {
                jQuery('#subjectsGrid').append(
                    '<div class="col-12" id="noResultsMessage">' +
                    '<div class="alert alert-info text-center">' +
                    '<i class="bi bi-info-circle"></i> No subjects or classes found matching "' + searchTerm + '".' +
                    '</div>' +
                    '</div>'
                );
            }
        } else {
            jQuery('#noResultsMessage').remove();
        }
    });

    // Clear search button
    jQuery('#clearSearchBtn').on('click', function() {
        jQuery('#subjectSearchInput').val('');
        jQuery('#clearSearchBtn').hide();
        jQuery('.subject-item').show();
        jQuery('#noResultsMessage').remove();
    });
}

// Show student photo in larger view
function showStudentPhoto(photoUrl, studentName) {
    Swal.fire({
        title: studentName,
        imageUrl: photoUrl,
        imageWidth: 300,
        imageHeight: 300,
        imageAlt: 'Student Photo',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            popup: 'swal2-popup-custom'
        }
    });
}

// Calculate grade and remark from marks
function isALevelClassName(className) {
    if (!className) {
        className = window.currentResultClassName
            || window.currentViewMainClass
            || jQuery('#classID option:selected').text()
            || '';
    }
    if (window.currentResultIsALevel === true && !className) return true;
    const n = String(className).toLowerCase().replace(/[\s\-]+/g, '_');
    return n === 'form_five' || n === 'form_six' || n === 'form_5' || n === 'form_6'
        || n.indexOf('form_five') !== -1 || n.indexOf('form_six') !== -1;
}

function calculateGradeAndRemark(marks) {
    if (!marks || marks === '' || isNaN(marks)) {
        return { grade: '', remark: '' };
    }

    const marksNum = parseFloat(marks);
    // Form Five / Form Six (non-duo subjects): A-Level NECTA scale realtime
    if (isALevelClassName(window.currentResultClassName || window.currentViewMainClass)) {
        if (marksNum >= 80) return { grade: 'A', remark: 'Excellent' };
        if (marksNum >= 70) return { grade: 'B', remark: 'Very Good' };
        if (marksNum >= 60) return { grade: 'C', remark: 'Good' };
        if (marksNum >= 50) return { grade: 'D', remark: 'Average' };
        if (marksNum >= 40) return { grade: 'E', remark: 'Satisfactory' };
        if (marksNum >= 35) return { grade: 'S', remark: 'Subsidiary' };
        return { grade: 'F', remark: 'Fail' };
    }

    if (marksNum >= 75) {
        return { grade: 'A', remark: 'Excellent' };
    } else if (marksNum >= 65) {
        return { grade: 'B', remark: 'Very Good' };
    } else if (marksNum >= 45) {
        return { grade: 'C', remark: 'Good' };
    } else if (marksNum >= 30) {
        return { grade: 'D', remark: 'Pass' };
    } else {
        return { grade: 'F', remark: 'Fail' };
    }
}

window.duoPaperContext = null;
function isDuoResultMode() {
    return !!(window.duoPaperContext && window.duoPaperContext.isDuo);
}
function getResultMarksMax() {
    if (isDuoResultMode() && Number(window.duoPaperContext.maxMarks) > 0) {
        return Number(window.duoPaperContext.maxMarks);
    }
    if (Number(examQuestionData.maxTotal) > 0) {
        return Number(examQuestionData.maxTotal);
    }
    return 100;
}

function isDirectMarksEntryMode() {
    const examID = jQuery('#exam_id').val();
    if (!examID) return true;
    const exam = window.examDataCache && window.examDataCache[examID] ? window.examDataCache[examID] : null;
    if (exam && (exam.allow_no_format == 1 || exam.allow_no_format === true)) return true;
    if (typeof isSecondarySchool !== 'undefined' && !isSecondarySchool) return true;
    const $sample = jQuery('#resultsForm .marks-input:not([disabled]):first');
    if ($sample.length && !$sample.prop('readonly')) return true;
    return false;
}

function syncMarksInputsMaxAttr() {
    const max = getResultMarksMax();
    jQuery('#resultsForm .marks-input').attr('max', max);
    jQuery('#resultsForm .marks-max-hint').text('Max ' + max);
    jQuery('#resultsForm .marks-input').each(function() {
        validateDirectMarksInput(jQuery(this));
    });
}

function validateDirectMarksInput($input) {
    if (!$input || !$input.length) return true;
    if ($input.prop('disabled')) return true;
    const studentID = $input.data('student');
    const $row = $input.closest('tr');
    let $err = $row.find(`.marks-max-error[data-student="${studentID}"]`);
    if (!$err.length) {
        $err = jQuery(`<small class="marks-max-error" data-student="${studentID}"></small>`);
        $input.after($err);
    }
    const max = getResultMarksMax();
    $input.attr('max', max);
    if ($input.prop('readonly') && !isDuoResultMode() && !isDirectMarksEntryMode()) {
        $input.removeClass('marks-over-max');
        $err.removeClass('is-visible').text('');
        return true;
    }
    const raw = $input.val();
    if (raw === '' || raw === null || raw === undefined) {
        $input.removeClass('marks-over-max');
        $err.removeClass('is-visible').text('');
        return true;
    }
    const marks = parseFloat(raw);
    if (isNaN(marks)) {
        $input.addClass('marks-over-max');
        $err.addClass('is-visible').text('Enter a valid number');
        return false;
    }
    if (marks < 0) {
        $input.addClass('marks-over-max');
        $err.addClass('is-visible').text('Marks cannot be negative');
        return false;
    }
    if (max > 0 && marks > max) {
        $input.addClass('marks-over-max');
        $err.addClass('is-visible').text('Total is ' + max);
        return false;
    }
    $input.removeClass('marks-over-max');
    $err.removeClass('is-visible').text('');
    return true;
}

function getDuoPassRatio() { return 0.4; }
function evaluateDuoPaperScore(marks) {
    const max = getResultMarksMax();
    const n = parseFloat(marks);
    if (marks === '' || marks === null || isNaN(n)) {
        return { status: 'empty', label: '', css: '' };
    }
    if (n > max) return { status: 'over', label: 'Total is ' + max, css: 'text-warning' };
    if (n >= max * getDuoPassRatio()) return { status: 'pass', label: 'Pass', css: 'text-success' };
    return { status: 'fail', label: 'Fail', css: 'text-danger' };
}
function updateDuoMarksIndicator(studentID, marks, $row = null) {
    const $target = $row || jQuery(`#marks_${studentID}`).closest('tr');
    const $wrap = $target.find(`.duo-marks-status[data-student="${studentID}"]`);
    const $bullet = $wrap.find('.duo-marks-bullet');
    const $label = $wrap.find('.duo-marks-label');
    if (!$bullet.length) return;
    const max = Number(getResultMarksMax()) || 0;
    const n = parseFloat(marks);

    const prevTimer = $wrap.data('duoBulletTimer');
    if (prevTimer) {
        clearTimeout(prevTimer);
        $wrap.removeData('duoBulletTimer');
    }

    $bullet
        .removeClass('duo-state-pass duo-state-fail duo-state-invalid is-pass is-fail is-invalid is-loading')
        .css({ backgroundColor: '#ced4da', boxShadow: '0 0 0 2px rgba(0,0,0,0.04)' });
    $label
        .removeClass('duo-state-pass duo-state-fail duo-state-invalid is-pass is-fail is-invalid')
        .css('color', '#6c757d')
        .text('');
    if (marks === '' || marks === null || isNaN(n) || max <= 0) return;

    if (n > max) {
        $bullet.addClass('duo-state-invalid').css({ backgroundColor: '#ffc107', boxShadow: '0 0 0 3px rgba(255,193,7,0.28)' });
        $label.addClass('duo-state-invalid').css('color', '#b8860b').text('Total is ' + max);
    } else if (n >= max * getDuoPassRatio()) {
        $bullet.addClass('duo-state-pass').css({ backgroundColor: '#28a745', boxShadow: '0 0 0 3px rgba(40,167,69,0.25)' });
        $label.addClass('duo-state-pass').css('color', '#28a745').text('Pass');
    } else {
        $bullet.addClass('duo-state-fail').css({ backgroundColor: '#dc3545', boxShadow: '0 0 0 3px rgba(220,53,69,0.25)' });
        $label.addClass('duo-state-fail').css('color', '#dc3545').text('Fail');
    }
}

// Get grade cell class for styling
function getGradeCellClass(grade) {
    if (!grade || grade === '' || grade.toLowerCase() === 'incomplete') {
        return 'bg-warning text-dark';
    }
    const gradeUpper = grade.toUpperCase();
    if (gradeUpper === 'A') {
        return 'bg-success text-white';
    } else if (gradeUpper === 'S') {
        return 'bg-secondary text-white';
    } else if (gradeUpper === 'E' || gradeUpper === 'F' || gradeUpper === 'S/F') {
        return 'bg-danger text-white';
    }
    return 'bg-info text-white';
}

function resetQuestionData() {
    examQuestionData = {
        questions: [],
        marksByStudent: {},
        maxTotal: 0,
        optionalTotal: 0,
        optionalRanges: []
    };
    if (isSecondarySchool) {
        jQuery('.question-details-row').addClass('d-none').removeClass('loaded');
        jQuery('.question-detail-container').html('<div class="text-muted small">Select examination to load question formats.</div>');
    }
    jQuery('#test_week_display_group').hide();
    jQuery('#selected_week_label').text('');
}

function loadExamQuestionData(classSubjectID, examID) {
    resetQuestionData();

    if (!examID) {
        return;
    }

    const componentID = jQuery('#component_id').val() || window.selectedComponentID || '';
    const qs = componentID ? `?componentID=${encodeURIComponent(componentID)}` : '';

    jQuery.ajax({
        url: `/get_exam_paper_question_data/${classSubjectID}/${examID}${qs}`,
        method: 'GET',
        success: function(response) {
            if (!response.success) {
                return;
            }

            examQuestionData.questions = response.questions || [];
            examQuestionData.marksByStudent = response.marks_by_student || {};
            examQuestionData.maxTotal = response.max_total || 0;
            examQuestionData.optionalTotal = response.optional_total || 0;
            examQuestionData.optionalRanges = response.optional_ranges || [];

            // Duo: keep paper max as the authoritative ceiling
            if (isDuoResultMode()) {
                if (response.paper_max_marks && Number(response.paper_max_marks) > 0) {
                    window.duoPaperContext.maxMarks = Number(response.paper_max_marks);
                }
            }
            syncMarksInputsMaxAttr();
            syncResultsExcelAvailability();
            if (response.test_week) {
                // Set the week value and hide the selection as it should be default from the paper
                jQuery('#test_week_group').hide();
                jQuery('#test_week_display_group').show();
                jQuery('#selected_week_label').text(response.test_week);

                const $tw = jQuery('#test_week');
                if ($tw.find(`option[value="${response.test_week}"]`).length === 0) {
                    $tw.append(`<option value="${response.test_week}">${response.test_week}</option>`);
                }
                $tw.val(response.test_week);
                // Reload existing results for this specific week
                loadExistingResults(classSubjectID, examID, response.test_week);
            } else {
                jQuery('#test_week_display_group').hide();
            }

            if (examQuestionData.questions.length === 0 && isSecondarySchool) {
                const exam = window.examDataCache[examID];
                if (exam && exam.allow_no_format == 1) {
                    jQuery('.question-detail-container').empty();
                    setTimeout(restoreMarksFromCache, 600);
                    return;
                }

                jQuery('.question-detail-container').html(
                    '<div class="alert alert-warning mb-0">' +
                    '<i class="bi bi-exclamation-triangle"></i> ' + (response.message || 'No approved exam paper question formats found for this subject.') +
                    '</div>'
                );
                setTimeout(restoreMarksFromCache, 600);
                return;
            }

            // Apply DB question totals first, then local cache overrides (per paper / component)
            updateTotalsFromCache();

            // Re-render any question detail rows that are already visible/loaded
            // so they show the fresh marks_by_student data
            jQuery('.question-details-row.loaded').each(function() {
                const studentID = jQuery(this).data('student');
                jQuery(this).find('.question-detail-container').html(buildQuestionDetails(studentID));
                // Restore optional-select checkbox state and enable inputs
                jQuery(this).find('.optional-select').each(function() {
                    const questionId = jQuery(this).data('question-id');
                    const hasMark = examQuestionData.marksByStudent[studentID] &&
                        examQuestionData.marksByStudent[studentID][questionId] !== undefined &&
                        examQuestionData.marksByStudent[studentID][questionId] !== null &&
                        examQuestionData.marksByStudent[studentID][questionId] !== '';
                    if (hasMark) {
                        jQuery(this).prop('checked', true);
                        const $input = jQuery(this).closest('.question-details-row')
                            .find(`.question-mark-input[data-question-id="${questionId}"]`);
                        $input.prop('disabled', false);
                    }
                });
                updateStudentTotal(studentID);
            });
            // Restore AFTER totals so cached main (e.g. 10) wins over wrong recalculation
            setTimeout(restoreMarksFromCache, 600);
        },
        error: function() {
            jQuery('.question-detail-container').html(
                '<div class="alert alert-danger mb-0">' +
                '<i class="bi bi-exclamation-triangle"></i> Failed to load question formats.' +
                '</div>'
            );
        }
    });
}

function buildQuestionDetails(studentID) {
    if (!isSecondarySchool) {
        return '';
    }

    if (!examQuestionData.questions || examQuestionData.questions.length === 0) {
        return `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle"></i> No question formats available.
            </div>
        `;
    }

    let html = '<div class="question-list">';
    examQuestionData.questions.forEach(function(question) {
        const existingMarks = examQuestionData.marksByStudent[studentID] &&
            examQuestionData.marksByStudent[studentID][question.exam_paper_questionID] !== undefined
            ? examQuestionData.marksByStudent[studentID][question.exam_paper_questionID]
            : '';
        let optionalTag = '';
        let isChecked = false;
        if (question.is_optional) {
            const rangeLabel = question.optional_range_number ? `Opt ${question.optional_range_number}` : 'Optional';
            optionalTag = `<span class="badge badge-warning ml-2">${rangeLabel}</span>`;
            // Only mark as selected if marks value is strictly > 0
            // (marks = 0 could be an orphan/stale entry from old duplicate saves)
            const savedMark = examQuestionData.marksByStudent[studentID] &&
                              examQuestionData.marksByStudent[studentID][question.exam_paper_questionID];
            const markValue = (savedMark !== undefined && savedMark !== null && savedMark !== '') ? parseFloat(savedMark) : NaN;
            isChecked = !isNaN(markValue) && markValue > 0;
        }
        html += `
            <div class="form-row align-items-end mb-2">
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Qn ${question.question_number}</label>
                </div>
                <div class="col-md-6">
                    <div class="small font-weight-bold">${question.question_description} ${optionalTag}</div>
                    <div class="small text-muted">Max: ${question.marks}</div>
                </div>
                <div class="col-md-4">
                    ${question.is_optional ? `
                        <div class="form-check mb-1">
                            <input class="form-check-input optional-select" type="checkbox"
                                   data-student="${studentID}"
                                   data-question-id="${question.exam_paper_questionID}"
                                   data-optional-range="${question.optional_range_number || 0}"
                                   ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label small">Selected</label>
                        </div>
                    ` : ''}
                    <input type="number"
                           class="form-control form-control-sm question-mark-input"
                           data-student="${studentID}"
                           data-question-id="${question.exam_paper_questionID}"
                           data-optional="${question.is_optional ? 1 : 0}"
                           data-optional-range="${question.optional_range_number || 0}"
                           data-max="${question.marks}"
                           min="0"
                           max="${question.marks}"
                           step="0.01"
                           value="${existingMarks}"
                           ${(question.is_optional && !isChecked) ? 'disabled' : ''}>
                    <div class="text-danger small question-max-warning d-none"></div>
                </div>
            </div>
        `;
    });
    html += `
        </div>
        <div class="text-muted small mt-2">
            Total: <span class="student-question-total" data-student="${studentID}">0</span> / ${examQuestionData.maxTotal || 100}
        </div>
        ${examQuestionData.optionalTotal > 0 ? `
            <div class="text-muted small">
                Optional Total: ${examQuestionData.optionalTotal}
            </div>
        ` : ''}
        <div class="text-danger small optional-total-warning d-none" data-student="${studentID}"></div>
    `;

    return html;
}

function updateTotalsFromCache() {
    if (!isSecondarySchool || !examQuestionData.marksByStudent) {
        return;
    }

    Object.keys(examQuestionData.marksByStudent).forEach(function(studentID) {
        const questionMarks = examQuestionData.marksByStudent[studentID] || {};
        let total = 0;
        let hasMarks = false;
        Object.keys(questionMarks).forEach(function(questionId) {
            const value = parseFloat(questionMarks[questionId]);
            if (!isNaN(value)) {
                total += value;
                hasMarks = true;
            }
        });

        if (hasMarks) {
            const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
            jQuery(`#marks_${studentID}`).val(displayTotal);
            autoCalculateGrade(studentID);
        }
    });
}

function updateStudentTotal(studentID) {
    if (!studentID) return;

    let total = 0;
    let optionalTotal = 0;
    let optionalTotalsByRange = {};
    let hasMarks = false;

    // Find inputs strictly for this student
    const $studentRow = jQuery(`input[id="marks_${studentID}"], .marks-input[data-student="${studentID}"]`).closest('tr');

    jQuery(`.question-mark-input[data-student="${studentID}"]`).each(function() {
        const value = parseFloat(jQuery(this).val());
        const isOptional = jQuery(this).data('optional') == 1;
        const rangeNumber = parseInt(jQuery(this).data('optional-range'), 10);
        if (!isNaN(value)) {
            total += value;
            if (isOptional) {
                optionalTotal += value;
                if (rangeNumber > 0) {
                    optionalTotalsByRange[rangeNumber] = (optionalTotalsByRange[rangeNumber] || 0) + value;
                }
            }
            hasMarks = true;
        }
    });

    const $optionalWarning = jQuery(`.optional-total-warning[data-student="${studentID}"]`);
    const rangeExceeded = Object.keys(optionalTotalsByRange).some(function(range) {
        const sum = optionalTotalsByRange[range];
        const rangeTotal = (examQuestionData.optionalRanges || []).find(r => r.range_number == range);
        return rangeTotal && sum > parseFloat(rangeTotal.total_marks);
    });

    if (rangeExceeded) {
        $optionalWarning.text('Optional marks exceed allowed range total.').removeClass('d-none');
    } else if (examQuestionData.optionalTotal > 0 && optionalTotal > examQuestionData.optionalTotal) {
        $optionalWarning.text('Optional marks exceed allowed total.').removeClass('d-none');
    } else {
        $optionalWarning.addClass('d-none').text('');
    }

    const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);

    // Update UI elements only for this student
    jQuery(`.student-question-total[data-student="${studentID}"]`).text(hasMarks ? displayTotal : 0);
    const $mainMarksInput = jQuery(`#marks_${studentID}`);

    if (hasMarks) {
        // Question-level marks entered — update the total
        if (isDuoResultMode() && parseFloat(displayTotal) > getResultMarksMax()) {
            $optionalWarning.text('Paper total exceeds max marks (' + getResultMarksMax() + ').').removeClass('d-none');
        }
        $mainMarksInput.val(displayTotal);
        $mainMarksInput.attr('max', getResultMarksMax());
        // Keep local cache main total in sync (input event does not fire on .val())
        saveMarkToCache(studentID, 'main', displayTotal);
        // Pass the row to autoCalculateGrade for precision
        autoCalculateGrade(studentID, $studentRow);
    }
    // If no question marks are entered (hasMarks = false), preserve the existing
    // marks input value (could be from loadExistingResults or manual entry).
    // Do NOT clear it — that would delete marks from students who have results
    // saved in the results table but do not have question-level breakdown yet.
}

// Active enrollment helpers (header academic year)
function canEnterResultForStudent(student) {
    if (student.can_enter_result === false) return false;
    if (student.can_enter_result === true) return true;
    return (student.enrollment_status || 'Active') === 'Active';
}

function buildResultStudentRowsHtml(students, isSecondarySchool, questionColspan) {
    let html = '';
    const marksMax = getResultMarksMax();
    const duoMode = isDuoResultMode();
    (students || []).forEach(function(student, index) {
        const canEnter = canEnterResultForStudent(student);
        const rowClass = canEnter ? '' : 'enrollment-inactive-row';
        const statusBadge = canEnter
            ? '<span class="badge badge-success enrollment-status-badge ml-1">Active</span>'
            : `<span class="badge badge-secondary enrollment-status-badge ml-1">${student.enrollment_status || 'Inactive'}</span>`;
        const disabledAttr = canEnter ? '' : 'disabled readonly';
        const marksReadonly = (!canEnter || isSecondarySchool) ? 'readonly' : '';
        const btnDisabled = canEnter ? '' : 'disabled';
        const fullName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.replace(/\s+/g, ' ').trim();
        const indexNo = student.examination_number || student.admission_number || 'N/A';
        const gender = student.gender || '';
        const sortName = fullName.toLowerCase();
        const sortIndex = String(indexNo).toLowerCase();
        const sortGender = String(gender).toLowerCase();
        const searchText = `${fullName} ${indexNo} ${gender} ${student.admission_number || ''}`.toLowerCase();
        const gradeCell = duoMode
            ? `<div class="duo-marks-status" data-student="${student.studentID}">
                    <span class="duo-marks-bullet" title="Paper score vs max"></span>
                    <span class="duo-marks-label"></span>
               </div>
               <input type="hidden" class="grade-input" name="grade[${student.studentID}]" id="grade_${student.studentID}" data-student="${student.studentID}" value="">
               <input type="hidden" class="remark-input" name="remark[${student.studentID}]" id="remark_${student.studentID}" data-student="${student.studentID}" value="">`
            : `<input type="text" class="form-control form-control-sm grade-input"
                           name="grade[${student.studentID}]"
                           id="grade_${student.studentID}"
                           data-student="${student.studentID}"
                           placeholder="A, B, C..." readonly ${disabledAttr}>`;
        const remarkCell = duoMode
            ? `<small class="text-muted">No A/B yet<br><span style="font-size:0.7rem;">(paper total later)</span></small>`
            : `<input type="text" class="form-control form-control-sm remark-input"
                           name="remark[${student.studentID}]"
                           id="remark_${student.studentID}"
                           data-student="${student.studentID}"
                           placeholder="Remark" readonly ${disabledAttr}>`;

        html += `
            <tr class="result-student-row ${rowClass}"
                data-can-enter="${canEnter ? 1 : 0}"
                data-enrollment-id="${student.enrollmentID || ''}"
                data-student-id="${student.studentID}"
                data-sort-name="${sortName.replace(/"/g, '&quot;')}"
                data-sort-index="${sortIndex.replace(/"/g, '&quot;')}"
                data-sort-gender="${sortGender.replace(/"/g, '&quot;')}"
                data-search-text="${searchText.replace(/"/g, '&quot;')}">
                <td class="result-row-num">${index + 1}</td>
                <td>${fullName}${statusBadge}</td>
                <td>${indexNo}</td>
                <td class="text-center questions-column-cell" style="${isSecondarySchool ? '' : 'display:none;'}">
                    <button type="button" class="btn btn-sm btn-outline-primary toggle-question-btn" data-student="${student.studentID}" title="${canEnter ? 'Add result questions' : 'Not editable: enrollment not Active'}" ${btnDisabled}>
                        <i class="bi bi-list-check"></i>
                    </button>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm marks-input"
                           name="marks[${student.studentID}]"
                           id="marks_${student.studentID}"
                           data-student="${student.studentID}"
                           data-can-enter="${canEnter ? 1 : 0}"
                           step="0.01" min="0" max="${marksMax}" placeholder="${canEnter ? '0.00' : '-'}"
                           oninput="autoCalculateGrade(${student.studentID})"
                           ${marksReadonly} ${disabledAttr}>
                    <small class="marks-max-hint">Max ${marksMax}</small>
                    <small class="marks-max-error" data-student="${student.studentID}"></small>
                </td>
                <td>
                    ${gradeCell}
                </td>
                <td>
                    ${remarkCell}
                </td>
            </tr>
            <tr class="question-details-row questions-detail-row d-none ${rowClass}" id="question_details_${student.studentID}" data-student="${student.studentID}">
                <td colspan="${questionColspan}">
                    <div class="question-detail-container" data-student="${student.studentID}">
                        <div class="text-muted small">${canEnter ? 'Select examination to load question formats.' : 'Marks locked: enrollment is not Active for this academic year.'}</div>
                    </div>
                </td>
            </tr>
        `;
    });
    return html;
}

function getResultsExcelAndToolsHtml(classSubjectID) {
    return `
        <div id="results-alert-container">
            ${isSecondarySchool ? `
                <div class="alert alert-warning mt-3 secondary-only-alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    Question-based results are enabled. Excel import/export is disabled unless this exam allows no-format entry.
                </div>
            ` : ''}
            <div class="alert alert-info mt-3 results-excel-alert" style="${isSecondarySchool ? 'display:none;' : ''}">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-1">
                        <i class="bi bi-file-earmark-excel"></i>
                        <strong>Excel Import/Export:</strong> Choose Name / Index columns, fill Marks only. Upload auto-calculates grade/remark, then Save.
                    </div>
                    <div class="results-excel-actions">
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="fillRandomPrimaryResults()" id="fillRandomBtn" aria-disabled="true">
                            <i class="bi bi-dice-5"></i> Fill Random
                        </button>
                        <button type="button" class="btn btn-sm btn-success js-export-results-excel" data-class-subject-id="${classSubjectID}" id="downloadExcelBtn" aria-disabled="true">
                            <i class="bi bi-download"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-primary ml-1 js-import-results-excel" data-class-subject-id="${classSubjectID}" id="uploadExcelBtn" aria-disabled="true">
                            <i class="bi bi-upload"></i> Import Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="results-tools-bar">
            <div class="row">
                <div class="col-md-5 col-sm-12 mb-2">
                    <label for="resultsStudentSearch"><i class="bi bi-search"></i> Search student</label>
                    <input type="text" class="form-control form-control-sm" id="resultsStudentSearch"
                           placeholder="Name, index no, sex..." autocomplete="off">
                </div>
                <div class="col-md-4 col-sm-8 mb-2">
                    <label for="resultsStudentSort"><i class="bi bi-sort-down"></i> Sort by</label>
                    <select class="form-control form-control-sm" id="resultsStudentSort">
                        <option value="index" selected>Index No. (default)</option>
                        <option value="sex">Sex</option>
                        <option value="alphabet">Alphabet (Name)</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-4 mb-2 d-flex align-items-end">
                    <small class="text-muted" id="resultsVisibleCount"></small>
                </div>
            </div>
        </div>
    `;
}

function handleNoActiveEnrollments(xhr) {
    const payload = xhr && xhr.responseJSON ? xhr.responseJSON : {};
    const msg = payload.error || 'Selected academic year has no Active enrollments. Result entry cannot continue.';
    jQuery('#addEditResultsModal').modal('hide');
    Swal.fire({
        title: 'No Active Enrollment',
        text: msg,
        icon: 'warning',
        confirmButtonColor: '#940000'
    });
}

function reapplyEnrollmentLocks() {
    jQuery('#resultsForm .marks-input[data-can-enter="0"]').each(function() {
        jQuery(this).prop('disabled', true).prop('readonly', true)
            .css({ 'background-color': '#cfd8dc', 'cursor': 'not-allowed' });
        const sid = jQuery(this).data('student');
        jQuery(`.grade-input[data-student="${sid}"], .remark-input[data-student="${sid}"]`)
            .prop('disabled', true).prop('readonly', true);
        jQuery(`.toggle-question-btn[data-student="${sid}"]`).prop('disabled', true);
    });
}

// View Students
function viewStudents(classSubjectID) {
    jQuery('#viewStudentsModal').modal('show');
    const loader = showPremiumLoading('studentsModalBody', "Loading Student List...", "Fetching all registered students for this subject...");

    jQuery.ajax({
        url: '/get_subject_students/' + classSubjectID + (window._allSubclassesMode ? '?all_subclasses=1' : ''),
        method: 'GET',
        success: function(response) {
            loader.update(100, "Formatting student list...");
            setTimeout(() => {
            if (response.success && response.students) {
                let html = `
                    <div class="mb-3">
                        <h6 class="text-primary-custom">
                            <i class="bi bi-book"></i> ${response.class_subject.subject ? response.class_subject.subject.subject_name : 'Subject'}
                            <span class="badge badge-primary ml-2">${response.active_enrollment_count != null ? response.active_enrollment_count : response.students.length} Active</span>
                            <span class="badge badge-light border ml-1">${response.students.length} total (year)</span>
                        </h6>
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Only Active enrollments for the selected academic year can receive marks.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Index No.</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Enrollment</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                response.students.forEach(function(student, index) {
                    // Get student photo or default based on gender
                    const baseUrl = '{{ asset("") }}';
                    let photoUrl = '';
                    if (student.photo) {
                        photoUrl = baseUrl + 'userImages/' + student.photo;
                    } else {
                        photoUrl = student.gender === 'Female'
                            ? baseUrl + 'images/female.png'
                            : baseUrl + 'images/male.png';
                    }

                    const dob = student.date_of_birth ? new Date(student.date_of_birth).toLocaleDateString() : 'N/A';
                    const studentName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                    const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';
                    const canEnter = canEnterResultForStudent(student);
                    const rowClass = canEnter ? '' : 'enrollment-inactive-row';
                    const statusBadge = canEnter
                        ? '<span class="badge badge-success enrollment-status-badge">Active</span>'
                        : `<span class="badge badge-secondary enrollment-status-badge">${student.enrollment_status || 'Inactive'}</span>`;

                    // Add red alarm icon if student has health conditions
                    let healthAlarmIcon = '';
                    if ((student.is_disabled && student.is_disabled == 1) ||
                        (student.has_epilepsy && student.has_epilepsy == 1) ||
                        (student.has_allergies && student.has_allergies == 1)) {
                        healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                    }

                    html += `
                        <tr class="${rowClass}">
                            <td>${index + 1}</td>
                            <td>
                                <img src="${photoUrl}"
                                     alt="Student Photo"
                                     class="rounded-circle"
                                     style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                                     onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                                     onerror="this.src='${fallbackPhoto}'">
                            </td>
                            <td><strong>${student.examination_number || student.admission_number || 'N/A'}</strong></td>
                            <td>${studentName}${healthAlarmIcon}</td>
                            <td>${student.subclass ? (student.subclass.subclass_name || 'N/A') : 'N/A'}</td>
                            <td>${statusBadge}</td>
                            <td>${student.gender || 'N/A'}</td>
                            <td>${dob}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                jQuery('#studentsModalBody').html(html);
            } else {
                jQuery('#studentsModalBody').html('<div class="alert alert-info">No students found.</div>');
            }
            }, 400); // end of setTimeout
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error_code === 'no_active_enrollments') {
                jQuery('#studentsModalBody').html(
                    '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle"></i> ' +
                    (xhr.responseJSON.error || 'No Active enrollments for the selected academic year.') +
                    '</div>'
                );
                return;
            }
            Swal.fire({
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load students',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            jQuery('#viewStudentsModal').modal('hide');
        }
    });
}

// View Results — same as teacher: header academic year (O/A), duo papers, Index No.
function viewResults(classSubjectID) {
    let subjectName, className, mainClassName;
    const card = jQuery(`i.view-results[onclick="viewResults(${classSubjectID})"]`).closest('.card');

    if (card.length > 0) {
        subjectName = card.find('.card-title').text().trim().replace(/\s*DUO\s*$/i, '').trim();
        className = card.find('strong').first().text().trim();
        mainClassName = className;
    } else {
        subjectName = jQuery('#subjectID option:selected').text().replace(/\s*\[DUO\]\s*$/i, '').trim();
        className = jQuery('#classID option:selected').text().trim();
        mainClassName = className;
        const subclass = jQuery('#subclassID option:selected').text();
        if (subclass && subclass !== 'All Subclasses' && subclass !== '') {
            className = className + ' - ' + subclass;
        }
    }

    const isDuoSubject = String(jQuery('#subjectID option:selected').data('is-duo') || '0') === '1';
    const teacherName = "{{ Auth::user()->name ?? 'N/A' }}";

    window.currentViewSubject = subjectName;
    window.currentViewClass = className;
    window.currentViewMainClass = mainClassName;
    window.currentViewTeacher = teacherName;
    window.currentViewIsDuo = isDuoSubject;
    window.currentResultClassName = mainClassName;
    window.currentResultIsALevel = isALevelClassName(mainClassName);

    jQuery('#viewResultsModal').modal('show');
    jQuery('#viewResultsModalLabel').html(`<i class="bi bi-clipboard-check"></i> Results: ${subjectName} (${className})`);
    const loader = showPremiumLoading('resultsModalBody', "Preparing Results View...", "Fetching examination structure for this subject...");

    jQuery.ajax({
        url: '/get_examinations_for_subject/' + classSubjectID + '?for_view=1',
        method: 'GET',
        success: function(examResponse) {
            loader.update(100, "Finalizing interface...");
            setTimeout(() => {
            if (examResponse.success && examResponse.examinations && examResponse.examinations.length > 0) {
                const headerYearIds = (examResponse.header_academic_year_ids || []).map(Number).filter(Boolean);
                const headerYearId = examResponse.header_academic_yearID || (headerYearIds[0] || null);
                const headerYearLabel = examResponse.header_academic_year_label || '—';
                window.currentViewIsALevel = !!(examResponse.is_a_level);
                window.currentResultClassName = examResponse.class_name || mainClassName;
                window.currentResultIsALevel = !!(examResponse.is_a_level) || isALevelClassName(window.currentResultClassName);
                window.currentViewHeaderYearId = headerYearId;
                window.currentViewHeaderYearIds = headerYearIds;

                // Prefer server list as-is for view. Soft-filter only when year meta exists.
                let exams = examResponse.examinations;
                if (headerYearIds.length) {
                    const filtered = exams.filter(function(e) {
                        const ids = (e.academic_year_ids || []).map(Number).filter(Boolean);
                        if (ids.length) {
                            return ids.some(function(id) { return headerYearIds.indexOf(id) !== -1; });
                        }
                        if (e.academic_yearID) {
                            return headerYearIds.indexOf(Number(e.academic_yearID)) !== -1;
                        }
                        return true;
                    });
                    exams = filtered.length ? filtered : exams;
                }

                window.viewModalExams = exams;
                window.viewModalAllExams = examResponse.examinations;
                window.examDataCache = window.examDataCache || {};
                exams.forEach(function(exam) {
                    if (exam && exam.examID) {
                        window.examDataCache[exam.examID] = exam;
                    }
                });

                let html = `
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label><i class="bi bi-calendar-event"></i> Academic Year:</label>
                            <input type="text" class="form-control" id="view_year_display" value="${headerYearLabel}" readonly
                                   style="background:#f8f9fa; font-weight:600; cursor:default;">
                        </div>
                        <div class="col-md-3">
                            <label><i class="bi bi-clock-history"></i> Term:</label>
                            <select class="form-control" id="view_term_filter" onchange="filterViewResultsOptions(${classSubjectID})">
                                <option value="">All Terms</option>
                                <option value="first_term">First Term</option>
                                <option value="second_term">Second Term</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label><i class="bi bi-file-earmark-text"></i> Examination:</label>
                            <select class="form-control" id="examSelector" onchange="handleViewResultsExamSelection(${classSubjectID}, this.value)">
                                <option value="">Select Examination</option>
                                ${generateExamOptions(exams, true)}
                            </select>
                        </div>
                         <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-block" onclick="exportResultsToPDF(${classSubjectID})">
                                <i class="bi bi-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>
                    <div class="mb-3" id="view_test_week_group" style="display: none;">
                        <label>Select Week:</label>
                        <select class="form-control" id="view_test_week" onchange="loadResultsForExam(${classSubjectID}, jQuery('#examSelector').val(), this.value)">
                            <option value="">All Weeks</option>
                        </select>
                    </div>
                    <div id="resultsContent">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Select an examination to view results.
                        </div>
                    </div>
                `;

                jQuery('#resultsModalBody').html(html);
            } else {
                jQuery('#resultsModalBody').html('<div class="alert alert-info">No examinations found for this subject.</div>');
            }
            }, 400);
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load examinations',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}
function resolveViewExamData(examID) {
    if (!examID) return null;
    if (window.examDataCache && window.examDataCache[examID]) {
        return window.examDataCache[examID];
    }
    const fromList = (window.viewModalExams || []).find(function(e) {
        return String(e.examID) === String(examID);
    });
    if (fromList) {
        window.examDataCache = window.examDataCache || {};
        window.examDataCache[examID] = fromList;
        return fromList;
    }
    return null;
}

function isWeeklyOrMonthlyTestExam(examData) {
    if (!examData || examData.exam_category !== 'test') return false;
    const t = (examData.test_type || examData.exam_type || '').toLowerCase();
    const name = (examData.exam_name || '').toLowerCase();
    if (t === 'weekly_test' || t === 'monthly_test' || t === 'weekly' || t === 'monthly') return true;
    return name.indexOf('weekly') !== -1 || name.indexOf('monthly') !== -1;
}

function isMonthlyTestExam(examData) {
    if (!examData) return false;
    const t = (examData.test_type || examData.exam_type || '').toLowerCase();
    const name = (examData.exam_name || '').toLowerCase();
    if (t === 'monthly_test' || t === 'monthly') return true;
    return name.indexOf('monthly') !== -1;
}

function handleViewResultsExamSelection(classSubjectID, examID) {
    if (!examID) {
        jQuery('#view_test_week_group').hide();
        jQuery('#resultsContent').html(
            '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Select an examination to view results.</div>'
        );
        return;
    }

    const examData = resolveViewExamData(examID);
    if (isWeeklyOrMonthlyTestExam(examData)) {
        jQuery('#view_test_week_group').show();
        const $weekSelect = jQuery('#view_test_week');
        $weekSelect.empty().append('<option value="">All Periods</option>');

        const today = new Date();
        const currentYear = today.getFullYear();

        if (isMonthlyTestExam(examData)) {
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            for (let i = 0; i < 12; i++) {
                const periodVal = `Month of ${months[i]} ${currentYear}`;
                $weekSelect.append(`<option value="${periodVal}">${periodVal}</option>`);
            }
        } else {
            const startDate = new Date(currentYear, 0, 1);
            while (startDate.getDay() !== 1) startDate.setDate(startDate.getDate() + 1);

            const monthNamesShort = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            function fmtRange(start, end) {
                const sM = monthNamesShort[start.getMonth()];
                const eM = monthNamesShort[end.getMonth()];
                const sD = start.getDate();
                const eD = end.getDate();
                const y = end.getFullYear();
                return `${sM} ${sD} to ${eM} ${eD}, ${y}`;
            }

            for (let i = 0; i < 52; i++) {
                const weekStart = new Date(startDate);
                weekStart.setDate(startDate.getDate() + (i * 7));
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                const startStr = weekStart.toISOString().split('T')[0];
                const endStr = weekEnd.toISOString().split('T')[0];
                const weekVal = `Week of ${startStr} to ${endStr}`;
                const weekLabel = fmtRange(weekStart, weekEnd);
                if (weekStart <= new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)) {
                    $weekSelect.append(`<option value="${weekVal}">${weekLabel}</option>`);
                }
            }
        }
    } else {
        jQuery('#view_test_week_group').hide();
        jQuery('#view_test_week').val('');
    }

    loadResultsForExam(classSubjectID, examID);
}

function buildDuoResultsViewTableAdmin(response) {
    const baseUrl = '{{ asset("") }}';
    const papers = response.duo_papers || [];
    const paperMaxTotal = response.paper_max_total || papers.reduce((s, p) => s + (Number(p.max_marks) || 0), 0);
    let paperHeaders = papers.map(p =>
        `<th>${p.component_name}${p.max_marks ? `<br><small>(/${p.max_marks})</small>` : ''}</th>`
    ).join('');

    let html = `
        <div class="mb-2">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> Duo total =
                student paper marks ÷ ${paperMaxTotal} (all papers max) × 100 → grade
            </small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="bg-primary-custom text-white">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Student Name</th>
                        <th>Index No.</th>
                        ${paperHeaders}
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (!response.results.length) {
        html += `<tr><td colspan="${6 + papers.length}" class="text-center text-muted">No results found for this examination.</td></tr>`;
    }

    response.results.forEach(function(result, index) {
        const student = result.student || {};
        const studentName = ((student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '')).trim();
        let photoUrl = student.photo
            ? baseUrl + 'userImages/' + student.photo
            : (student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png');
        const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';
        const examNo = result.examination_number || student.examination_number || 'N/A';
        const paperMarks = result.paper_marks || {};
        let paperCells = papers.map(function(p) {
            const m = paperMarks[p.componentID];
            return `<td><strong>${m !== null && m !== undefined ? m : '<span class="text-muted">-</span>'}</strong></td>`;
        }).join('');
        const totalPct = result.percentage !== null && result.percentage !== undefined ? result.percentage : null;
        const rawTotal = result.student_raw_total;
        const grade = result.grade || 'Incomplete';
        const remark = result.remark || 'Incomplete';
        const gradeClass = getGradeCellClass(grade);

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <img src="${photoUrl}" alt="Student Photo" class="rounded-circle"
                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                         onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                         onerror="this.src='${fallbackPhoto}'">
                </td>
                <td>${studentName || 'N/A'}</td>
                <td>${examNo}</td>
                ${paperCells}
                <td>
                    <strong>${totalPct !== null ? totalPct : '<span class="text-muted">-</span>'}</strong>
                    ${rawTotal !== null && rawTotal !== undefined ? `<br><small class="text-muted">${rawTotal}/${paperMaxTotal}</small>` : ''}
                </td>
                <td><span class="badge ${gradeClass}" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">${grade}</span></td>
                <td>${remark}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    return html;
}

function loadResultsForExam(classSubjectID, examID, testWeek = null) {
    if (!examID) {
        jQuery('#resultsContent').html(
            '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Select an examination to view results.</div>'
        );
        return;
    }

    let url = `/get_subject_results/${classSubjectID}/${examID}`;
    const data = {};
    if (testWeek) data.test_week = testWeek;
    if (window._allSubclassesMode) data.all_subclasses = 1;

    const loader = showPremiumLoading(
        'resultsContent',
        "Loading Student Results...",
        "Fetching marks and grades for this examination..."
    );

    jQuery.ajax({
        url: url,
        method: 'GET',
        data: data,
        success: function(response) {
            loader.update(100, "Formatting student data...");
            setTimeout(() => {
            if (response.success && response.results) {
                if (response.class_name) {
                    window.currentResultClassName = response.class_name;
                    window.currentViewMainClass = response.class_name;
                }
                if (response.is_a_level !== undefined) {
                    window.currentResultIsALevel = !!response.is_a_level;
                }

                if (response.available_test_weeks && response.available_test_weeks.length && jQuery('#view_test_week_group').is(':visible')) {
                    const $weekSelect = jQuery('#view_test_week');
                    const current = $weekSelect.val() || '';
                    (response.available_test_weeks || []).forEach(function(w) {
                        if (!w) return;
                        if ($weekSelect.find('option').filter(function() { return jQuery(this).val() === w; }).length === 0) {
                            $weekSelect.append(jQuery('<option></option>').attr('value', w).text(w));
                        }
                    });
                    if (current) $weekSelect.val(current);
                }

                if (response.is_duo && response.duo_papers && response.duo_papers.length) {
                    jQuery('#resultsContent').html(buildDuoResultsViewTableAdmin(response));
                    return;
                }

                const baseUrl = '{{ asset("") }}';
                let html = `
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Student Name</th>
                                    <th>Index No.</th>
                                    <th>Examination</th>
                                    ${response.has_ca ? '<th>Exam Marks</th><th>CA</th><th>Total</th><th>Avg</th>' : '<th>Marks</th>'}
                                    <th>Grade</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody id="viewResultsTable">
                `;

                response.results.forEach(function(result, index) {
                    const student = result.student || {};
                    const studentName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');

                    // Add red alarm icon if student has health conditions
                    let healthAlarmIcon = '';
                    if ((student.is_disabled && student.is_disabled == 1) ||
                        (student.has_epilepsy && student.has_epilepsy == 1) ||
                        (student.has_allergies && student.has_allergies == 1)) {
                        healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                    }

                    // Get student photo or default based on gender
                    let photoUrl = '';
                    if (student.photo) {
                        photoUrl = baseUrl + 'userImages/' + student.photo;
                    } else {
                        photoUrl = student.gender === 'Female'
                            ? baseUrl + 'images/female.png'
                            : baseUrl + 'images/male.png';
                    }
                    const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';

                    // Handle marks, grade, and remark
                    const examMarksValue = result.exam_marks !== undefined ? result.exam_marks : result.marks;
                    const marks = examMarksValue !== null && examMarksValue !== '' ? examMarksValue : null;
                    const caValue = response.has_ca ? (result.ca_marks !== undefined ? result.ca_marks : 0) : null;
                    const totalValue = response.has_ca ? (result.total_marks !== undefined ? result.total_marks : marks) : null;

                    let grade = result.grade || '';
                    let remark = result.remark || '';

                    // If marks not filled, show Incomplete
                    if (marks === null) {
                        grade = 'Incomplete';
                        remark = 'Incomplete';
                    }

                    // Get grade cell class for styling
                    const gradeClass = getGradeCellClass(grade);

                    let marksColumnHtml = '';
                    if (response.has_ca) {
                        const avgValue = result.avg_marks !== undefined ? result.avg_marks : (totalValue / 2);
                        marksColumnHtml = `
                            <td><strong>${marks !== null ? marks : '<span class="text-muted">-</span>'}</strong></td>
                            <td><span class="text-info font-weight-bold">${caValue !== null ? caValue : '-'}</span></td>
                            <td><span class="text-success font-weight-bold">${totalValue !== null ? totalValue : '-'}</span></td>
                            <td><span class="text-warning font-weight-bold" style="font-size: 1.1em">${avgValue !== null ? avgValue : '-'}</span></td>
                        `;
                    } else {
                        marksColumnHtml = `<td><strong>${marks !== null ? marks : '<span class="text-muted">-</span>'}</strong></td>`;
                    }

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <img src="${photoUrl}"
                                     alt="Student Photo"
                                     class="rounded-circle"
                                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                                     onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                                     onerror="this.src='${fallbackPhoto}'">
                            </td>
                            <td>${studentName || 'N/A'}${healthAlarmIcon}</td>
                            <td>${student.examination_number || result.examination_number || 'N/A'}</td>
                            <td>
                                ${result.examination ? result.examination.exam_name : 'N/A'}
                                ${result.test_week ? `<br><small class="badge badge-info">${result.test_week}</small>` : ''}
                            </td>
                            ${marksColumnHtml}
                            <td><span class="badge ${gradeClass}" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">${grade || '<span class="text-muted">-</span>'}</span></td>
                            <td>${remark || '<span class="text-muted">-</span>'}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                jQuery('#resultsContent').html(html);
            } else {
                jQuery('#resultsContent').html('<div class="alert alert-info">No results found.</div>');
            }
            }, 400);
        },
        error: function(xhr) {
            jQuery('#resultsContent').html('<div class="alert alert-danger">Error loading results.</div>');
        }
    });
}

// Helper: Get Unique Years from exams
function getUniqueYears(exams) {
    let years = [...new Set(exams.map(e => e.year_label || e.year))];
    years.sort();
    return years.map(y => `<option value="${y}">${y}</option>`).join('');
}

// Helper: Generate Exam Options
// forView=true → show all exams (including entry closed); Add Results keeps enter_result only
function generateExamOptions(exams, forView = false) {
    return (exams || [])
        .filter(function(e) {
            if (forView) return true;
            return e.enter_result === true || e.enter_result === 1;
        })
        .map(function(e) {
            const open = e.enter_result === true || e.enter_result === 1;
            const closedTag = forView && !open ? ' — entry closed' : '';
            return `<option value="${e.examID}" data-year="${e.year}" data-term="${e.term}">${e.exam_name} (${e.year_label || e.year})${closedTag}</option>`;
        })
        .join('');
}

// Helper: Filter by Term (year locked to header)
// Keep all_terms exams visible under any term filter.
function examMatchesViewTerm(exam, term) {
    if (!term) return true;
    const t = (exam && exam.term) ? String(exam.term).toLowerCase() : '';
    if (!t || t === 'all_terms' || t === 'all' || t === 'both') return true;
    return t === String(term).toLowerCase();
}

function filterViewResultsOptions(classSubjectID) {
    const term = jQuery('#view_term_filter').val();
    const selector = jQuery('#examSelector');
    const exams = window.viewModalExams || [];
    let filtered = exams;
    if (term) {
        filtered = filtered.filter(function(e) { return examMatchesViewTerm(e, term); });
    }
    selector.html('<option value="">Select Examination</option>' + generateExamOptions(filtered, true));
    jQuery('#view_test_week_group').hide();
    jQuery('#resultsContent').html(
        '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Select an examination to view results.</div>'
    );
}

// PDF Export Logic (jsPDF)
function exportResultsToPDF(classSubjectID) {
    const examID = jQuery('#examSelector').val() || '';
    if (!examID) {
        Swal.fire({
            title: 'Select Examination',
            text: 'Please select an examination before downloading the PDF.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }
    const testWeek = jQuery('#view_test_week').val() || '';

    let url = `/export_subject_results_pdf/${classSubjectID}`;
    url += `/${examID}`;

    const params = new URLSearchParams();
    if (testWeek) params.append('test_week', testWeek);
    if (window._allSubclassesMode) params.append('all_subclasses', '1');
    const queryString = params.toString();
    if (queryString) url += `?${queryString}`;

    // Show loading state
    Swal.fire({
        title: 'Generating PDF...',
        html: 'Please wait while your report is being prepared.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.blob();
        })
        .then(blob => {
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = downloadUrl;

            // Generate dynamic filename
            const subjectName = window.currentViewSubject ? window.currentViewSubject.replace(/\s+/g, '_') : 'Subject';
            const className = window.currentViewClass ? window.currentViewClass.replace(/\s+/g, '_') : 'Class';
            a.download = `${className}_${subjectName}_Results.pdf`;

            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);

            Swal.fire({
                title: 'Success!',
                text: 'Your PDF has been downloaded.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Download error:', error);
            Swal.fire('Error', 'Failed to generate PDF. Please try again.', 'error');
        });
}

// Removed checkExamStatusForAddEdit function - now only checking enter_result

// Edit Results
function editResults(classSubjectID) {
    jQuery('#addEditResultsModalLabel').html('<i class="bi bi-pencil-square"></i> Edit Results');
    jQuery('#addEditResultsModal').modal('show');
    const loader = showPremiumLoading('addEditResultsModalBody', "Checking Access...", "Verifying result-entry permissions for examinations...");

    // Clear exam data cache when opening modal
    window.examDataCache = {};
    resetQuestionData();

    // First check if there are any examinations with enter_result = true
    jQuery.ajax({
        url: '/get_examinations_for_subject/' + classSubjectID,
        method: 'GET',
        success: function(examsResponse) {
            loader.update(40, "Verifying status...");
            // Check if there are any examinations with enter_result = true
            let hasEnterResultEnabled = false;
            if (examsResponse.success && examsResponse.examinations && examsResponse.examinations.length > 0) {
                hasEnterResultEnabled = examsResponse.examinations.some(function(exam) {
                    return exam.enter_result === true || exam.enter_result === 1;
                });
            }

            if (!hasEnterResultEnabled) {
                jQuery('#addEditResultsModal').modal('hide');
                Swal.fire({
                    title: 'Access Denied!',
                    text: 'You are not allowed to edit results. Result entry has been disabled for all examinations.',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            // Get students, examinations, and existing results
            loader.update(60, "Fetching students...");
            jQuery.ajax({
                url: '/get_subject_students/' + classSubjectID + (window._allSubclassesMode ? '?all_subclasses=1' : ''),
                method: 'GET',
                success: function(studentsResponse) {
                    loader.update(80, "Fetching existing marks...");
                    // Get all results for this subject
                    jQuery.ajax({
                        url: '/get_subject_results/' + classSubjectID,
                        method: 'GET',
                        success: function(resultsResponse) {
                            loader.update(100, "Formatting interface...");
                            setTimeout(() => {
                            let html = `
                                <form id="resultsForm">
                                    <input type="hidden" id="class_subject_id" value="${classSubjectID}">
                                    <div class="form-group">
                                        <label>Select Examination <span class="text-danger">*</span></label>
                                        <select class="form-control" id="exam_id" name="exam_id" required onchange="handleExamSelection(${classSubjectID}, this.value)">
                                            <option value="">Select Examination</option>
                            `;

                            // Store exam data globally for use in handleExamSelection
                            window.examDataCache = window.examDataCache || {};

                            if (examsResponse.success && examsResponse.examinations) {
                                examsResponse.examinations.forEach(function(exam) {
                                    // Only show examinations where enter_result is true
                                    if (exam.enter_result === true || exam.enter_result === 1) {
                                        // Store exam data in cache
                                        window.examDataCache[exam.examID] = exam;

                                        const statusText = exam.status === 'awaiting_results' ? ' (Awaiting Results)' :
                                                          exam.status === 'ongoing' ? ' (Ongoing)' : ' (Results Available)';
                                        const termClosedText = exam.is_term_closed ? ' (Term Closed - Not Editable)' : '';
                                        const disabledAttr = exam.is_term_closed ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '';
                                        html += `<option value="${exam.examID}" data-status="${exam.status}" data-term-closed="${exam.is_term_closed}" data-enter-result="${exam.enter_result}" ${disabledAttr}>${exam.exam_name} (${exam.year_label || exam.year})${statusText}${termClosedText}</option>`;
                                    }
                                });
                            }

                            html += `
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle"></i> Only examinations with "Enter Result" enabled can be edited.
                                        </small>
                                    </div>
                                    ${getResultsExcelAndToolsHtml(classSubjectID)}
                                    <div class="table-responsive mt-2">
                                        <table class="table table-hover" id="studentsResultsTable">
                                            <thead class="bg-primary-custom text-white">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student Name</th>
                                                    <th>Index No.</th>
                                                    <th class="text-center questions-column-header" style="${isSecondarySchool ? '' : 'display:none;'}"><i class="bi bi-list-check"></i></th>
                                                    <th>Marks</th>
                                                    <th>Grade</th>
                                                    <th>Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody id="resultsTableBody">
                            `;

                            if (studentsResponse.success && studentsResponse.students) {
                                window.resultStudentsCache = studentsResponse.students;
                                html += buildResultStudentRowsHtml(studentsResponse.students, isSecondarySchool, questionColspan);
                            } else {
                                window.resultStudentsCache = [];
                                html += `<tr><td colspan="${questionColspan}" class="text-center text-muted">No enrolled students for the selected academic year.</td></tr>`;
                            }

                            html += `
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary-custom">
                                            <i class="bi bi-check-circle"></i> Update Results
                                        </button>
                                    </div>
                                </form>
                            `;

                                jQuery('#addEditResultsModalBody').html(html);
                                initResultsTableTools();
                                applyResultsSearchSort();
                                }, 400); // end of setTimeout
                            },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load results',
                                icon: 'error',
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error_code === 'no_active_enrollments') {
                        handleNoActiveEnrollments(xhr);
                        return;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load students',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load examinations',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function loadExistingResults(classSubjectID, examID, testWeek = null) {
    if (!examID) {
        // Clear all inputs
        jQuery('.marks-input, .grade-input, .remark-input').val('');
        return;
    }

    const data = {};
    if (testWeek) data.test_week = testWeek;
    const componentID = jQuery('#component_id').val() || window.selectedComponentID || '';
    if (componentID) data.componentID = componentID;

    jQuery.ajax({
        url: `/get_subject_results/${classSubjectID}/${examID}`,
        method: 'GET',
        data: data,
        success: function(response) {
            if (response.success && response.results) {
                // Clear all inputs first, scoped to the results table
                jQuery('#resultsTableBody .marks-input, #resultsTableBody .grade-input, #resultsTableBody .remark-input').val('');

                // Populate with existing results
                // Only show marks that are genuinely non-zero (> 0) — zero/null means 'not entered'
                response.results.forEach(function(result) {
                    if (result.studentID) {
                        const rawMarks = parseFloat(result.marks);
                        const hasRealMarks = !isNaN(rawMarks) && rawMarks > 0;
                        if (hasRealMarks) {
                            jQuery(`#marks_${result.studentID}`).val(rawMarks);
                            autoCalculateGrade(result.studentID);
                        }
                        // If marks are 0 or null, leave the input empty (placeholder shows)
                    }
                });
            }
            // Always restore cache — even if no DB results exist yet (first-time entry)
            setTimeout(restoreMarksFromCache, 600);
        },
        error: function(xhr) {
            console.error('Error loading existing results:', xhr);
        }
    });
}

// Auto-calculate grade and remark when marks are entered
// Fill random results for primary/non-secondary schools for testing/balancing
function fillRandomPrimaryResults() {
    jQuery('.marks-input:not([readonly])').each(function() {
        const studentID = jQuery(this).data('student');

        // Distribution targets:
        // A: 15%, B: 25%, C: 30%, D: 20%, F: 10%
        const rand = Math.random();
        let marks = 0;

        if (rand < 0.15) { // A: 75-100
            marks = 75 + Math.random() * 25;
        } else if (rand < 0.40) { // B: 65-74
            marks = 65 + Math.random() * 10;
        } else if (rand < 0.70) { // C: 45-64
            marks = 45 + Math.random() * 20;
        } else if (rand < 0.90) { // D: 30-44
            marks = 30 + Math.random() * 15;
        } else { // F: 5-29
            marks = 5 + Math.random() * 25;
        }

        jQuery(this).val(marks.toFixed(2));
        autoCalculateGrade(studentID);
    });
}

function autoCalculateGrade(studentID, $row = null) {
    if (!studentID) return;

    const $targetRow = $row || jQuery(`input[id="marks_${studentID}"], .marks-input[data-student="${studentID}"]`).closest('tr');
    const marksInput = $targetRow.find(`.marks-input[data-student="${studentID}"]`);
    const gradeInput = $targetRow.find(`.grade-input[data-student="${studentID}"]`);
    const remarkInput = $targetRow.find(`.remark-input[data-student="${studentID}"]`);

    const marks = marksInput.val();
    const marksMax = getResultMarksMax();
    marksInput.attr('max', marksMax);
    const marksOk = validateDirectMarksInput(marksInput);

    if (isDuoResultMode()) {
        gradeInput.val('');
        remarkInput.val('');
        if (!marksOk && marks !== '' && marks !== null && !isNaN(marks) && parseFloat(marks) > marksMax) {
            updateDuoMarksIndicator(studentID, marks, $targetRow);
            return;
        }
        updateDuoMarksIndicator(studentID, marks, $targetRow);
        return;
    }

    if (!marksOk) {
        gradeInput.val('');
        remarkInput.val('');
        return;
    }

    if (marks && marks !== '' && !isNaN(marks)) {
        const result = calculateGradeAndRemark(marks);
        gradeInput.val(result.grade);
        remarkInput.val(result.remark);
    } else {
        gradeInput.val('');
        remarkInput.val('');
    }
}

// Add Results — duo subjects: pick paper first (same as teacher)
function addResults(classSubjectID, isEdit = false) {
    const modalTitle = isEdit ? 'Edit Results' : 'Add Results';
    jQuery('#addEditResultsModalLabel').html(`<i class="bi bi-${isEdit ? 'pencil' : 'plus-circle'}"></i> ${modalTitle}`);
    jQuery('#addEditResultsModal').modal('show');
    const loader = showPremiumLoading('addEditResultsModalBody', "Preparing Details...", "Checking subject papers...");
    window.examDataCache = {};
    resetQuestionData();
    window.selectedComponentID = null;
    window.duoPaperContext = null;

    jQuery.ajax({
        url: '/get_class_subject_papers/' + classSubjectID,
        method: 'GET',
        success: function(paperResponse) {
            loader.update(40, "Loading papers...");
            const isDuo = !!(paperResponse && paperResponse.success && paperResponse.is_duo);
            const myPapers = (paperResponse && paperResponse.my_papers) ? paperResponse.my_papers : [];

            if (isDuo && myPapers.length > 0) {
                let paperOptions = '<option value="">Select paper...</option>';
                myPapers.forEach(function(p) {
                    if (!p.componentID) return;
                    paperOptions += `<option value="${p.componentID}" data-max-marks="${p.max_marks || 0}">${p.component_name}${p.max_marks ? ' (' + p.max_marks + ' marks)' : ''}</option>`;
                });
                const paperHtml = `
                    <div class="alert alert-info">
                        <i class="bi bi-layers"></i>
                        <strong>${paperResponse.subject_name || 'Subject'}</strong> is a <strong>DUO</strong> subject.
                        Select the paper before choosing an examination.
                        <br><small>Marks max = that paper's marks. No A/B here — Pass/Fail colour only.</small>
                    </div>
                    <div class="form-group">
                        <label>Select Paper <span class="text-danger">*</span></label>
                        <select class="form-control" id="component_id_picker">${paperOptions}</select>
                    </div>
                    <div id="duoResultsContinueArea"></div>`;
                jQuery('#addEditResultsModalBody').html(paperHtml);
                jQuery('#component_id_picker').off('change').on('change', function() {
                    const componentID = jQuery(this).val();
                    window.selectedComponentID = componentID || null;
                    if (!componentID) {
                        window.duoPaperContext = null;
                        jQuery('#duoResultsContinueArea').html('');
                        return;
                    }
                    const $opt = jQuery(this).find('option:selected');
                    window.duoPaperContext = {
                        isDuo: true,
                        componentID: componentID,
                        maxMarks: parseInt($opt.data('max-marks'), 10) || 100,
                        paperName: $opt.text()
                    };
                    addResultsContinue(classSubjectID, isEdit, componentID, $opt.text(), '#duoResultsContinueArea');
                });
                return;
            }
            window.duoPaperContext = null;
            addResultsContinue(classSubjectID, isEdit, null, null, '#addEditResultsModalBody');
        },
        error: function() {
            window.duoPaperContext = null;
            addResultsContinue(classSubjectID, isEdit, null, null, '#addEditResultsModalBody');
        }
    });
}

function addResultsContinue(classSubjectID, isEdit = false, componentID = null, paperName = null, targetSelector = '#addEditResultsModalBody') {
    window.currentResultsClassSubjectID = classSubjectID;
    const loader = showPremiumLoading(
        targetSelector === '#duoResultsContinueArea' ? 'duoResultsContinueArea' : 'addEditResultsModalBody',
        "Preparing Details...",
        "Fetching student lists and examinations..."
    );
    window.examDataCache = window.examDataCache || {};
    resetQuestionData();

    // Get students and examinations for this subject
    // When all_subclasses mode is active, append query param so backend fetches students from ALL subclasses
    var studentFetchUrl = '/get_subject_students/' + classSubjectID;
    if (window._allSubclassesMode) {
        studentFetchUrl += '?all_subclasses=1';
    }
    jQuery.ajax({
        url: studentFetchUrl,
        method: 'GET',
        success: function(studentsResponse) {
            console.log('Students response:', studentsResponse);
            loader.update(70, "Fetching examinations...");
            jQuery.ajax({
                url: '/get_examinations_for_subject/' + classSubjectID,
                method: 'GET',
                success: function(examsResponse) {
                    console.log('Exams response:', examsResponse);
                    loader.update(100, "Finalizing interface...");
                    if (examsResponse.class_name) {
                        window.currentResultClassName = examsResponse.class_name;
                    }
                    if (examsResponse.is_a_level !== undefined) {
                        window.currentResultIsALevel = !!examsResponse.is_a_level;
                    } else {
                        window.currentResultIsALevel = isALevelClassName(
                            window.currentResultClassName || jQuery('#classID option:selected').text()
                        );
                    }

                    // Small delay to let user see 100%
                    setTimeout(() => {
                    const paperBanner = componentID ? `
                        <div class="alert alert-success py-2">
                            <i class="bi bi-check-circle"></i> Entering results for paper:
                            <strong>${paperName || ('#' + componentID)}</strong>
                            ${isDuoResultMode() ? ` &nbsp;|&nbsp; <strong>Max marks: ${getResultMarksMax()}</strong> <small class="text-muted">(no A/B — Pass/Fail colour only)</small>` : ''}
                        </div>` : '';

                    let html = `
                        <form id="resultsForm">
                            <input type="hidden" id="class_subject_id" value="${classSubjectID}">
                            <input type="hidden" id="component_id" name="component_id" value="${componentID || ''}">
                            ${paperBanner}
                            <div class="form-group">
                                <label>Select Examination <span class="text-danger">*</span></label>
                                <select class="form-control" id="exam_id" name="exam_id" required onchange="handleExamSelection(${classSubjectID}, this.value)">
                                    <option value="">Select Examination</option>
                    `;

                    // Store exam data globally for use in handleExamSelection
                    window.examDataCache = window.examDataCache || {};

                    if (examsResponse.success && examsResponse.examinations) {
                        examsResponse.examinations.forEach(function(exam) {
                            // Only show examinations where enter_result is true
                            if (exam.enter_result === true || exam.enter_result === 1) {
                                // Store exam data in cache
                                window.examDataCache[exam.examID] = exam;

                                const statusText = exam.status === 'awaiting_results' ? ' (Awaiting Results)' :
                                                  exam.status === 'ongoing' ? ' (Ongoing)' : ' (Results Available)';
                                const termClosedText = exam.is_term_closed ? ' (Term Closed - Not Editable)' : '';
                                const disabledAttr = exam.is_term_closed ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '';
                                html += `<option value="${exam.examID}" data-status="${exam.status}" data-term-closed="${exam.is_term_closed}" data-enter-result="${exam.enter_result}" ${disabledAttr}>${exam.exam_name} (${exam.year_label || exam.year})${statusText}${termClosedText}</option>`;
                            }
                        });
                    }

                    html += `
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle"></i> Only examinations with "Enter Result" enabled can be used.
                                </small>
                            </div>
                            <!-- Auto-selected Week Display -->
                            <div id="test_week_display_group" style="display: none;" class="alert alert-info py-2 mb-3">
                                <strong><i class="bi bi-calendar-event"></i> Recording Results for:</strong>
                                <span id="selected_week_label" class="ml-1"></span>
                            </div>

                            <!-- Week selection for Weekly Tests -->
                            <div class="form-group" id="test_week_group" style="display: none;">
                                <label for="test_week">Select Week <span class="text-danger">*</span></label>
                                <select class="form-control" id="test_week" name="test_week">
                                    <option value="">Select Week</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-calendar-event"></i> Results will be recorded for the selected week.
                                </small>
                            </div>
                            ${getResultsExcelAndToolsHtml(classSubjectID)}
                            <div class="table-responsive mt-2">
                                <table class="table table-hover" id="studentsResultsTableEdit">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Index No.</th>
                                             <th class="text-center questions-column-header" style="${isSecondarySchool ? '' : 'display:none;'}"><i class="bi bi-list-check"></i></th>
                                            <th>Marks</th>
                                            <th>Grade</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resultsTableBody">
                    `;

                    if (studentsResponse.success && studentsResponse.students) {
                        window.resultStudentsCache = studentsResponse.students;
                        html += buildResultStudentRowsHtml(studentsResponse.students, isSecondarySchool, questionColspan);
                    } else {
                        window.resultStudentsCache = [];
                        html += `<tr><td colspan="${questionColspan}" class="text-center text-muted">No enrolled students for the selected academic year.</td></tr>`;
                    }

                    html += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-check-circle"></i> ${isEdit ? 'Update' : 'Save'} Results
                                </button>
                            </div>
                        </form>
                    `;

                    jQuery(targetSelector || '#addEditResultsModalBody').html(html);
                    initResultsTableTools();
                    applyResultsSearchSort();
                    }, 400); // end of setTimeout
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load examinations',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error_code === 'no_active_enrollments') {
                handleNoActiveEnrollments(xhr);
                return;
            }
            Swal.fire({
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load students',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

// Form submission for results
jQuery(document).on('submit', '#resultsForm', function(e) {
    e.preventDefault();

    const classSubjectID = jQuery('#class_subject_id').val();
    const examID = jQuery('#exam_id').val();

    // Check if selected exam is in a closed term
    const selectedExam = jQuery('#exam_id option:selected');
    const isTermClosed = selectedExam.data('term-closed');
    if (isTermClosed) {
        Swal.fire({
            title: 'Access Denied!',
            text: 'You cannot edit results for this examination. The term has been closed.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return false;
    }

    // Check only if form is disabled (which happens if enter_result is false)
    const formDisabled = jQuery('#resultsForm button[type="submit"]').prop('disabled');
    if (formDisabled) {
        Swal.fire({
            title: 'Access Denied!',
            text: 'You are not allowed to enter results for this examination. Result entry has been disabled.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return false;
    }

    const results = [];

    const examIDForResults = jQuery('#exam_id').val();
    const selectedExamData = window.examDataCache[examIDForResults];
    const allowNoFormat = selectedExamData && (selectedExamData.allow_no_format == 1);

    if (isSecondarySchool && !allowNoFormat) {
        if (!examQuestionData.questions || examQuestionData.questions.length === 0) {
            Swal.fire({
                title: 'Warning!',
                text: 'No question formats found for this examination.',
                icon: 'warning',
                confirmButtonColor: '#940000'
            });
            return;
        }

        let hasValidationError = false;
        let hasResults = false;
        // Check if any marks have been entered in the current form
        jQuery('#resultsForm .marks-input').each(function() {
            const marks = jQuery(this).val();
            if (marks && marks !== '') {
                hasResults = true;
                return false; // Break loop
            }
        });

        // Scope each loop to the resultsForm to avoid picking up stale DOM elements
        jQuery('#resultsForm .marks-input').each(function() {
            const studentID = jQuery(this).data('student');
            if (jQuery(this).data('can-enter') === 0 || jQuery(this).prop('disabled')) {
                return; // Skip non-Active enrollments
            }
            const questionMarks = examQuestionData.marksByStudent[studentID] || {};
            const optionalRanges = examQuestionData.optionalRanges || [];
            const hasAnyMarks = Object.keys(questionMarks).length > 0;
            const selectedOptionalCounts = {};

            jQuery(`.optional-select[data-student="${studentID}"]:checked`).each(function() {
                const rangeNumber = parseInt(jQuery(this).data('optional-range'), 10);
                if (rangeNumber > 0) {
                    selectedOptionalCounts[rangeNumber] = (selectedOptionalCounts[rangeNumber] || 0) + 1;
                }
            });

            if (!hasAnyMarks) {
                // If no question marks are cached, check if the main marks input has a value
                const manualMarks = jQuery(`#marks_${studentID}`).val();
                if (manualMarks === '' || manualMarks === null || isNaN(manualMarks)) {
                    return; // Skip student if no marks at all
                }
            }

            let total = 0;
            const questionPayload = [];
            let hasQuestionSpecificMarks = false;
            const selectedOptionalIds = {};

            // IMPORTANT: Don't just look at DOM checkboxes (they don't exist if panel is closed)
            // Look at our marks cache to see which optional questions are active
            if (examQuestionData.marksByStudent[studentID]) {
                Object.keys(examQuestionData.marksByStudent[studentID]).forEach(function(qId) {
                    selectedOptionalIds[qId] = true;
                });
            }
            // Also include anything currently checked in the DOM (for newly selected ones)
            jQuery(`.optional-select[data-student="${studentID}"]:checked`).each(function() {
                selectedOptionalIds[jQuery(this).data('question-id')] = true;
            });

            for (let i = 0; i < examQuestionData.questions.length; i++) {
                const question = examQuestionData.questions[i];
                const value = questionMarks[question.exam_paper_questionID];
                const isOptional = question.is_optional;
                const isSelectedOptional = selectedOptionalIds[question.exam_paper_questionID] === true;

                if (isOptional && !isSelectedOptional) {
                    continue;
                }

                // If Question has no marks entered, skip it (don't force it to 0 yet)
                if (value === undefined || value === null || value === '') {
                    continue;
                }

                const numericValue = parseFloat(value);
                if (isNaN(numericValue) || numericValue < 0 || numericValue > parseFloat(question.marks)) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: 'Question marks must not exceed the allowed maximum (Max: ' + question.marks + ') for student ' + studentID,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }

                total += numericValue;
                hasQuestionSpecificMarks = true;
                questionPayload.push({
                    question_id: question.exam_paper_questionID,
                    marks: numericValue
                });
            }

            // Logic decision: If we found no question marks for this student,
            // fall back to the manual main marks input (which might have been edited)
            if (!hasQuestionSpecificMarks) {
                const manualMarks = jQuery(`#marks_${studentID}`).val();
                if (manualMarks !== '' && manualMarks !== null && !isNaN(manualMarks)) {
                    total = parseFloat(manualMarks);
                } else {
                    return; // Skip student if no marks at all
                }
            } else {
                // We have question marks — recalculate and update total for UI consistency
                const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
                jQuery(`#marks_${studentID}`).val(displayTotal);
                autoCalculateGrade(studentID);
            }

            // Duo / direct marks: total must not exceed max
            if (isDuoResultMode()) {
                const paperMax = getResultMarksMax();
                if (parseFloat(total) > paperMax) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: `Total is ${paperMax}. Marks cannot exceed ${paperMax} for student ${studentID}.`,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }
            } else if (isDirectMarksEntryMode()) {
                const maxMarks = getResultMarksMax();
                if (parseFloat(total) > maxMarks) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: `Total is ${maxMarks}. Marks cannot exceed ${maxMarks} for student ${studentID}.`,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }
            }

            results.push({
                studentID: studentID,
                marks: total,
                grade: isDuoResultMode() ? null : (jQuery(`.grade-input[data-student="${studentID}"]`).val() || null),
                remark: isDuoResultMode() ? null : (jQuery(`.remark-input[data-student="${studentID}"]`).val() || null),
                question_marks: questionPayload.length > 0 ? questionPayload : null
            });
        });

        if (hasValidationError) {
            return;
        }
    } else {
        // Scope each loop to the resultsForm to avoid picking up stale DOM elements
        let duoMarksInvalid = false;
        jQuery('#resultsForm .marks-input').each(function() {
            if (jQuery(this).data('can-enter') === 0 || jQuery(this).prop('disabled')) {
                return; // Skip non-Active enrollments
            }
            const studentID = jQuery(this).data('student');
            const marks = jQuery(this).val();
            const grade = jQuery(`.grade-input[data-student="${studentID}"]`).val();
            const remark = jQuery(`.remark-input[data-student="${studentID}"]`).val();

            if (marks || grade || remark) {
                if (marks !== '' && marks !== null && !isNaN(marks)) {
                    const maxMarks = getResultMarksMax();
                    if (parseFloat(marks) > maxMarks) {
                        duoMarksInvalid = true;
                        validateDirectMarksInput(jQuery(this));
                        Swal.fire({
                            title: 'Error!',
                            text: `Total is ${maxMarks}. Marks cannot exceed ${maxMarks}.`,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                        return false;
                    }
                }
                results.push({
                    studentID: studentID,
                    marks: marks || null,
                    grade: isDuoResultMode() ? null : (grade || null),
                    remark: isDuoResultMode() ? null : (remark || null)
                });
            }
        });
        if (duoMarksInvalid) {
            return;
        }
    }

    if (results.length === 0) {
        Swal.fire({
            title: 'Warning!',
            text: 'Please enter at least one result',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save the results.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    jQuery.ajax({
        url: '/save_subject_results',
        method: 'POST',
        data: {
            class_subjectID: classSubjectID,
            examID: examID,
            componentID: jQuery('#component_id').val() || window.selectedComponentID || null,
            test_week: jQuery('#test_week').val(),
            results: JSON.stringify(results) // Stringify to bypass PHP max_input_vars limit
        },
        success: function(response) {
            clearResultsCache(); // Clear local storage on successful save
            Swal.fire({
                title: 'Success!',
                text: response.success || 'Results saved successfully!',
                icon: 'success',
                confirmButtonColor: '#940000'
            }).then(() => {
                jQuery('#addEditResultsModal').modal('hide');
            });
        },
        error: function(xhr) {
            if (xhr.status === 409) {
                Swal.fire({
                    title: 'Existing Results!',
                    text: (xhr.responseJSON && xhr.responseJSON.error) || 'Results already exist for this week. You cannot enter results twice.',
                    icon: 'warning',
                    confirmButtonColor: '#940000'
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to save results',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        }
    });
});

// Upload Excel: AJAX parse only (does not save). Document-level so it always works.
jQuery(document).off('submit.uploadExcel', '#uploadExcelForm').on('submit.uploadExcel', '#uploadExcelForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    loadExcelResultsIntoForm(this);
    return false;
});

jQuery(document).off('click.uploadExcel', '#btnLoadExcelIntoForm').on('click.uploadExcel', '#btnLoadExcelIntoForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    loadExcelResultsIntoForm(jQuery('#uploadExcelForm')[0]);
    return false;
});

jQuery(document).on('hidden.bs.modal', '#uploadExcelModal', function() {
    if (jQuery('#addEditResultsModal').is(':visible') || jQuery('#addEditResultsModal').hasClass('show')) {
        jQuery('body').addClass('modal-open');
    }
});

function loadExcelResultsIntoForm(formEl) {
    const classSubjectID = jQuery('#upload_class_subject_id').val() || jQuery('#class_subject_id').val();
    const examID = jQuery('#upload_exam_id').val() || jQuery('#exam_id').val();
    if (!examID || !classSubjectID) {
        Swal.fire({ title: 'Error!', text: 'Please select an examination first.', icon: 'error', confirmButtonColor: '#940000' });
        return;
    }
    const fileInput = document.getElementById('excel_file');
    const file = fileInput && fileInput.files ? fileInput.files[0] : null;
    if (!file) {
        Swal.fire({ title: 'Error!', text: 'Please choose an Excel file.', icon: 'error', confirmButtonColor: '#940000' });
        return;
    }
    const $btn = jQuery('#btnLoadExcelIntoForm');
    const originalBtnHtml = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

    const formData = new FormData();
    formData.append('class_subject_id', classSubjectID);
    formData.append('exam_id', examID);
    formData.append('excel_file', file);
    formData.append('_token', jQuery('meta[name="csrf-token"]').attr('content'));

    Swal.fire({
        title: 'Loading Excel...',
        text: 'Please wait while we parse your file.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => { Swal.showLoading(); }
    });

    jQuery.ajax({
        url: '/parse_excel_results',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $btn.prop('disabled', false).html(originalBtnHtml);
            const rows = (response && response.rows) ? response.rows : [];
            const result = applyExcelRowsToResultsForm(rows);
            const finish = function() {
                jQuery('body').addClass('modal-open');
                showExcelImportFeedback(result);
            };
            if (jQuery('#uploadExcelModal').is(':visible') || jQuery('#uploadExcelModal').hasClass('show')) {
                jQuery('#uploadExcelModal').one('hidden.bs.modal', finish);
                jQuery('#uploadExcelModal').modal('hide');
            } else {
                finish();
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalBtnHtml);
            let errorMsg = 'Failed to parse Excel file.';
            if (xhr.responseJSON && xhr.responseJSON.error) errorMsg = xhr.responseJSON.error;
            else if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
            else if (xhr.status === 419) errorMsg = 'Session expired. Please refresh the page and try again.';
            Swal.fire({ title: 'Error!', text: errorMsg, icon: 'error', confirmButtonColor: '#940000' });
        }
    });
}

function showExcelImportFeedback(result) {
    jQuery('.excel-import-feedback').remove();
    if (!result || result.filled === 0) {
        jQuery('#resultsForm .results-tools-bar').before(`
            <div class="alert alert-warning excel-import-feedback mt-2" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Import:</strong> No student rows matched. Check Name / Index No. columns match the list below.
            </div>`);
        Swal.fire({
            title: 'No matches',
            text: 'No student rows matched the form. Check Name / Index No. in Excel.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }
    let detail = `Loaded <strong>${result.filled}</strong> mark(s). Grade &amp; remark calculated automatically. <strong>Not saved yet</strong>. Review below, then click Save Results.`;
    if (result.skipped) detail += ` ${result.skipped} row(s) skipped.`;
    if (result.overMax) detail += ` <span class="text-danger">${result.overMax} over Max. Fix highlighted rows.</span>`;
    jQuery('#resultsForm .results-tools-bar').before(`
        <div class="alert alert-success excel-import-feedback mt-2" role="alert">
            <i class="bi bi-check-circle"></i> ${detail}
        </div>`);
    Swal.fire({ title: 'Loaded into form', html: detail, icon: 'success', confirmButtonColor: '#940000' });
}

// Handle exam selection - enable/disable Excel buttons
function handleExamSelection(classSubjectID, examID) {
    setResultsExcelButtonsEnabled(false);
    jQuery('#test_week_group, #test_week_display_group').hide();
    jQuery('#test_week').prop('required', false).val('');
    if (!examID) {
        disableResultsForm();
        resetQuestionData();
        return;
    }

    const exam = window.examDataCache[examID];
    if (exam) {
        const enterResult = exam.enter_result === true || exam.enter_result === 1;
        if (!enterResult) {
            disableResultsForm();
            setResultsExcelButtonsEnabled(false);
            showResultsStatusError('You are not allowed to enter results for this examination. Result entry has been disabled.');
            resetQuestionData();
            return;
        }

        enableResultsForm(exam);
        setResultsExcelButtonsEnabled(true);
        jQuery('.results-status-error').remove();

        if (exam.allow_no_format == 1) {
            jQuery('.secondary-only-alert').hide();
            jQuery('.questions-column-header, .questions-column-cell').hide();
            jQuery('.results-excel-alert').show();
        } else if (isSecondarySchool) {
            jQuery('.secondary-only-alert').show();
            jQuery('.questions-column-header, .questions-column-cell').show();
            jQuery('.results-excel-alert').hide();
        } else {
            jQuery('.secondary-only-alert').hide();
            jQuery('.results-excel-alert').show();
        }
        syncResultsExcelAvailability();

        loadExistingResults(classSubjectID, examID);
        loadExamQuestionData(classSubjectID, examID);
    } else {
        const selectedOption = jQuery(`#exam_id option[value="${examID}"]`);
        const enterResult = selectedOption.data('enter-result') === true || selectedOption.data('enter-result') === 1;
        if (!enterResult) {
            disableResultsForm();
            setResultsExcelButtonsEnabled(false);
            showResultsStatusError('You are not allowed to enter results for this examination. Result entry has been disabled.');
            resetQuestionData();
            return;
        }
        enableResultsForm();
        setResultsExcelButtonsEnabled(true);
        jQuery('.results-status-error').remove();
        syncResultsExcelAvailability();
        loadExistingResults(classSubjectID, examID);
        loadExamQuestionData(classSubjectID, examID);
    }
}

// Helper function to disable form inputs
function disableResultsForm() {
    jQuery('.marks-input, .grade-input, .remark-input').prop('disabled', true).css({
        'background-color': '#e9ecef',
        'cursor': 'not-allowed',
        'color': '#dc3545'
    });
    jQuery('.question-mark-input, .toggle-question-btn').prop('disabled', true);
    jQuery('#resultsForm button[type="submit"]').prop('disabled', true);
}

// Helper function to enable form inputs
function enableResultsForm(exam = null) {
    jQuery('.marks-input, .grade-input, .remark-input').prop('disabled', false).css({
        'background-color': '',
        'cursor': '',
        'color': ''
    });
    jQuery('.grade-input, .remark-input').prop('readonly', true); // Keep readonly for grade and remark

    // Check allow_no_format
    const allowNoFormat = exam && (exam.allow_no_format == 1);

    if (isSecondarySchool && !allowNoFormat) {
        jQuery('.marks-input').prop('readonly', true);
    } else {
        jQuery('.marks-input').prop('readonly', false);
    }
    jQuery('.question-mark-input, .toggle-question-btn').prop('disabled', false);
    jQuery('#resultsForm button[type="submit"]').prop('disabled', false);
    // Keep non-Active enrollment rows locked
    reapplyEnrollmentLocks();
    syncMarksInputsMaxAttr();
    syncResultsExcelAvailability();
}

// Helper function to show error message
function showResultsStatusError(message) {
    // Remove existing error messages
    jQuery('.results-status-error').remove();

    // Add error message above the table
    const errorHtml = `
        <div class="alert alert-danger results-status-error mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Access Denied:</strong> ${message}
        </div>
    `;
    jQuery('#resultsTableBody').closest('.table-responsive').before(errorHtml);
}

// Toggle question details per student
jQuery(document).on('click', '.toggle-question-btn', function() {
    if (examQuestionData.questions.length === 0) {
        Swal.fire({
            title: 'No Question Formats',
            text: 'Please select an examination with approved question formats first.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }
    const studentID = jQuery(this).data('student');
    const $detailRow = jQuery(`#question_details_${studentID}`);
    if ($detailRow.length === 0) {
        return;
    }

    // Always re-render to ensure fresh data (marks, optional selections)
    $detailRow.find('.question-detail-container').html(buildQuestionDetails(studentID));
    $detailRow.addClass('loaded');
    // Question fields were already rendered with correct checkbox state and disabled state in buildQuestionDetails


    // Sync rendered input values back into examQuestionData.marksByStudent
    // ONLY for inputs that are ENABLED (i.e. selected optional questions or mandatory)
    // Disabled optional inputs must NOT be synced - they carry stale values
    if (!examQuestionData.marksByStudent[studentID]) {
        examQuestionData.marksByStudent[studentID] = {};
    }
    $detailRow.find('.question-mark-input').each(function() {
        const questionId = jQuery(this).data('question-id');
        const isDisabled = jQuery(this).prop('disabled');
        const val = jQuery(this).val();

        if (isDisabled) {
            // This optional question was NOT selected — remove it from cache
            delete examQuestionData.marksByStudent[studentID][questionId];
        } else if (val !== '' && val !== null && val !== undefined) {
            examQuestionData.marksByStudent[studentID][questionId] = val;
        }
    });

    updateStudentTotal(studentID);

    $detailRow.toggleClass('d-none');
});

jQuery(document).on('change', '.optional-select', function() {
    const $checkbox = jQuery(this);
    const studentID = $checkbox.data('student');
    const rangeNumber = parseInt($checkbox.data('optional-range'), 10);
    const questionId = $checkbox.data('question-id');
    const $input = jQuery(`.question-mark-input[data-student="${studentID}"][data-question-id="${questionId}"]`);
    const rangeMeta = (examQuestionData.optionalRanges || []).find(r => r.range_number == rangeNumber);
    const requiredCount = rangeMeta ? parseInt(rangeMeta.required_questions || 0, 10) : 0;
    const selectedCount = jQuery(`.optional-select[data-student="${studentID}"][data-optional-range="${rangeNumber}"]:checked`).length;

    if (requiredCount > 0 && selectedCount > requiredCount) {
        $checkbox.prop('checked', false);
        Swal.fire({
            title: 'Optional Limit',
            text: `Question needed to opt is ${requiredCount}. You exceed the limit.`,
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if ($checkbox.is(':checked')) {
        $input.prop('disabled', false);
    } else {
        $input.val('');
        $input.prop('disabled', true);
        if (examQuestionData.marksByStudent[studentID]) {
            delete examQuestionData.marksByStudent[studentID][questionId];
        }
    }
    updateStudentTotal(studentID);
});

// Question marks input handler
jQuery(document).on('input', '.question-mark-input', function() {
    const $input = jQuery(this);
    const max = parseFloat($input.data('max'));
    let value = parseFloat($input.val());
    const $warning = $input.siblings('.question-max-warning');
    const isOptional = $input.data('optional') == 1;
    const rangeNumber = parseInt($input.data('optional-range'), 10);

    if (!isNaN(value) && value > max) {
        value = max;
        $input.val(max);
        $warning.text(`Max ${max}`).removeClass('d-none');
    } else {
        $warning.addClass('d-none').text('');
    }

    const studentID = $input.data('student');
    const questionId = $input.data('question-id');

    if (!studentID || !questionId) return;

    if (!examQuestionData.marksByStudent[studentID]) {
        examQuestionData.marksByStudent[studentID] = {};
    }

    if (isOptional && rangeNumber > 0) {
        let optionalSum = 0;
        jQuery(`.question-mark-input[data-student="${studentID}"]`).each(function() {
            if (parseInt(jQuery(this).data('optional-range'), 10) === rangeNumber) {
                const val = parseFloat(jQuery(this).val());
                if (!isNaN(val)) {
                    optionalSum += val;
                }
            }
        });

        const rangeTotal = (examQuestionData.optionalRanges || []).find(r => r.range_number == rangeNumber);
        const rangeLimit = rangeTotal ? parseFloat(rangeTotal.total_marks) : 0;

        if (rangeLimit > 0 && optionalSum > rangeLimit) {
            const otherSum = optionalSum - (isNaN(value) ? 0 : value);
            const remaining = Math.max(rangeLimit - otherSum, 0);
            $input.val(remaining);
            value = remaining;
        }
    }

    if ($input.val() === '') {
        delete examQuestionData.marksByStudent[studentID][questionId];
        saveMarkToCache(studentID, questionId, '');
    } else {
        examQuestionData.marksByStudent[studentID][questionId] = $input.val();
        saveMarkToCache(studentID, questionId, $input.val());
    }

    updateStudentTotal(studentID);
});

// Helper function to check if in edit mode
function checkIfEditMode(examID, classSubjectID) {
    // Check if any results exist for this exam and class subject
    let hasResults = false;
    jQuery('#resultsForm .marks-input').each(function() {
        const marks = jQuery(this).val();
        if (marks && marks !== '') {
            hasResults = true;
            return false; // Break loop
        }
    });
    return hasResults;
}

// --- Results table search / sort / Excel (direct marks / no-qn format) ---

function initResultsTableTools() {
    jQuery('#resultsStudentSearch').off('input.resultsTools').on('input.resultsTools', function() {
        applyResultsSearchSort();
    });
    jQuery('#resultsStudentSort').off('change.resultsTools').on('change.resultsTools', function() {
        applyResultsSearchSort();
    });
    syncResultsExcelAvailability();
}

function syncResultsExcelAvailability() {
    const canExcel = isDirectMarksEntryMode() && !!jQuery('#exam_id').val();
    if (canExcel) {
        jQuery('.results-excel-alert').show();
        if (isSecondarySchool) jQuery('.secondary-only-alert').hide();
    } else if (isSecondarySchool && jQuery('#exam_id').val()) {
        const exam = window.examDataCache && window.examDataCache[jQuery('#exam_id').val()];
        if (!(exam && exam.allow_no_format == 1)) {
            jQuery('.results-excel-alert').hide();
            jQuery('.secondary-only-alert').show();
        }
    }
    setResultsExcelButtonsEnabled(canExcel);
}

/** Keep Excel buttons clickable so Safari/Mac get feedback instead of a dead click. */
function setResultsExcelButtonsEnabled(enabled) {
    jQuery('#downloadExcelBtn, #uploadExcelBtn, #fillRandomBtn').each(function() {
        const $b = jQuery(this);
        $b.prop('disabled', false);
        $b.removeClass('disabled');
        $b.attr('aria-disabled', enabled ? 'false' : 'true');
        $b.toggleClass('btn-excel-locked', !enabled);
        $b.css({
            opacity: enabled ? '' : '0.65',
            cursor: enabled ? '' : 'not-allowed',
            pointerEvents: 'auto'
        });
    });
}

function getResultsSortLabel(sortBy) {
    if (sortBy === 'sex') return 'Sex';
    if (sortBy === 'alphabet') return 'Alphabet (Name)';
    return 'Index No.';
}

function genderSortRank(gender) {
    const g = String(gender || '').toLowerCase();
    if (g.indexOf('f') === 0 || g.indexOf('k') === 0) return 1;
    if (g.indexOf('m') === 0) return 2;
    return 3;
}

function naturalIndexCompare(a, b) {
    return String(a || '').localeCompare(String(b || ''), undefined, { numeric: true, sensitivity: 'base' });
}

function applyResultsSearchSort() {
    const $tbody = jQuery('#resultsTableBody');
    if (!$tbody.length) return;
    const q = String(jQuery('#resultsStudentSearch').val() || '').trim().toLowerCase();
    const sortBy = jQuery('#resultsStudentSort').val() || 'index';
    const pairs = [];
    $tbody.children('tr.result-student-row').each(function() {
        const $main = jQuery(this);
        const sid = $main.data('student-id');
        pairs.push({
            $main: $main,
            $detail: $tbody.find('#question_details_' + sid),
            name: String($main.data('sort-name') || ''),
            index: String($main.data('sort-index') || ''),
            gender: String($main.data('sort-gender') || ''),
            search: String($main.data('search-text') || '')
        });
    });
    pairs.sort(function(a, b) {
        if (sortBy === 'alphabet') return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
        if (sortBy === 'sex') {
            const gr = genderSortRank(a.gender) - genderSortRank(b.gender);
            if (gr !== 0) return gr;
            return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
        }
        const idx = naturalIndexCompare(a.index, b.index);
        if (idx !== 0) return idx;
        return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
    });
    let visible = 0;
    pairs.forEach(function(pair) {
        const match = !q || pair.search.indexOf(q) !== -1;
        pair.$main.toggleClass('is-search-hidden', !match);
        if (pair.$detail.length) pair.$detail.toggleClass('is-search-hidden', !match);
        $tbody.append(pair.$main);
        if (pair.$detail.length) $tbody.append(pair.$detail);
        if (match) {
            visible++;
            pair.$main.find('.result-row-num').text(visible);
        }
    });
    const total = pairs.length;
    const sortLabel = getResultsSortLabel(sortBy);
    jQuery('#resultsVisibleCount').text(
        q ? `${visible} of ${total} · sorted by ${sortLabel}` : `${total} student(s) · sorted by ${sortLabel}`
    );
}

function excelRowGet(row, keys) {
    if (!row) return '';
    const map = {};
    Object.keys(row).forEach(function(k) {
        map[String(k).trim().toLowerCase()] = row[k];
    });
    for (let i = 0; i < keys.length; i++) {
        const key = keys[i].toLowerCase();
        if (map[key] !== undefined && map[key] !== null && map[key] !== '') return map[key];
    }
    return '';
}

function normalizeExcelMatchText(value) {
    return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
}

function buildResultsStudentLookup() {
    const byId = {}, byIndex = {}, byName = {};
    jQuery('#resultsForm .result-student-row').each(function() {
        const $row = jQuery(this);
        const sid = String($row.data('student-id') || '');
        if (!sid) return;
        const name = normalizeExcelMatchText($row.data('sort-name') || $row.children('td').eq(1).clone().children().remove().end().text());
        const indexNo = normalizeExcelMatchText($row.data('sort-index') || $row.children('td').eq(2).text());
        byId[sid] = sid;
        if (indexNo && indexNo !== 'n/a') {
            if (!byIndex[indexNo]) byIndex[indexNo] = [];
            byIndex[indexNo].push(sid);
        }
        if (name) {
            if (!byName[name]) byName[name] = [];
            byName[name].push(sid);
        }
    });
    return { byId: byId, byIndex: byIndex, byName: byName };
}

function resolveStudentIdFromExcelRow(row, lookup) {
    const studentID = excelRowGet(row, ['student_id', 'Student ID', 'studentID', 'ID']);
    if (studentID !== '' && studentID !== null && studentID !== undefined) {
        const sid = String(studentID).trim();
        if (lookup.byId[sid]) return sid;
    }
    const indexNo = normalizeExcelMatchText(excelRowGet(row, [
        'index_no', 'Index No.', 'Index No', 'Index Number', 'Index', 'examination_number', 'Admission Number', 'Admission No.'
    ]));
    const name = normalizeExcelMatchText(excelRowGet(row, [
        'student_name', 'Student Name', 'Name', 'Full Name'
    ]));
    if (name && indexNo) {
        const nameHits = lookup.byName[name] || [];
        const indexHits = lookup.byIndex[indexNo] || [];
        const both = nameHits.filter(function(id) { return indexHits.indexOf(id) !== -1; });
        if (both.length === 1) return both[0];
        if (indexHits.length === 1) return indexHits[0];
        if (nameHits.length === 1) return nameHits[0];
        return null;
    }
    if (indexNo) {
        const hits = lookup.byIndex[indexNo] || [];
        return hits.length === 1 ? hits[0] : null;
    }
    if (name) {
        const hits = lookup.byName[name] || [];
        return hits.length === 1 ? hits[0] : null;
    }
    return null;
}

function applyExcelRowsToResultsForm(rows) {
    const maxMarks = getResultMarksMax();
    const lookup = buildResultsStudentLookup();
    let filled = 0, skipped = 0, overMax = 0;
    (rows || []).forEach(function(row) {
        const sid = resolveStudentIdFromExcelRow(row, lookup);
        if (!sid) { skipped++; return; }
        const $marks = jQuery(`#resultsForm .marks-input[data-student="${sid}"]`);
        if (!$marks.length || $marks.prop('disabled')) { skipped++; return; }
        let marks = excelRowGet(row, ['marks', 'Marks', 'Score', 'Total']);
        if (marks === '' || marks === null || marks === undefined) { skipped++; return; }
        marks = String(marks).trim();
        if (marks === '' || isNaN(parseFloat(marks))) { skipped++; return; }
        const marksNum = parseFloat(marks);
        $marks.val(marksNum);
        autoCalculateGrade(sid);
        if (maxMarks > 0 && marksNum > maxMarks) {
            overMax++;
            validateDirectMarksInput($marks);
        }
        filled++;
    });
    return { filled: filled, skipped: skipped, overMax: overMax };
}

function getExportColumnsChoiceLabel(mode) {
    if (mode === 'name') return 'Name only';
    if (mode === 'index') return 'Index No. only';
    return 'Name and Index No.';
}

function downloadWorkbookAsXlsx(wb, filename) {
    if (typeof XLSX === 'undefined') {
        throw new Error('Excel library failed to load');
    }
    const safeName = String(filename || 'Results.xlsx').replace(/[\\/:*?"<>|]+/g, '_');
    const out = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
    const blob = new Blob([out], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = safeName;
    a.style.display = 'none';
    a.rel = 'noopener';
    document.body.appendChild(a);
    a.click();
    setTimeout(function() {
        if (a.parentNode) a.parentNode.removeChild(a);
        URL.revokeObjectURL(url);
    }, 2000);
    return true;
}

function writeResultsExcelFile(classSubjectID, exportMode) {
    applyResultsSearchSort();
    const maxMarks = getResultMarksMax();
    const sortBy = jQuery('#resultsStudentSort').val() || 'index';
    const sortLabel = getResultsSortLabel(sortBy);
    const examName = jQuery('#exam_id option:selected').text() || 'Exam';
    const modeLabel = getExportColumnsChoiceLabel(exportMode);
    const header = [];
    if (exportMode === 'name' || exportMode === 'both') header.push('Student Name');
    if (exportMode === 'index' || exportMode === 'both') header.push('Index No.');
    header.push('Marks');
    const aoa = [header];
    let exported = 0;
    jQuery('#resultsTableBody tr.result-student-row').each(function() {
        const $row = jQuery(this);
        if ($row.hasClass('is-search-hidden')) return;
        const name = $row.children('td').eq(1).clone().children().remove().end().text().trim();
        const indexNo = $row.children('td').eq(2).text().trim();
        const marks = $row.find('.marks-input').val() || '';
        const rowData = [];
        if (exportMode === 'name' || exportMode === 'both') rowData.push(name);
        if (exportMode === 'index' || exportMode === 'both') rowData.push(indexNo);
        rowData.push(marks);
        aoa.push(rowData);
        exported++;
    });
    if (exported === 0) {
        throw new Error('No students to export (check search filter).');
    }
    const ws = XLSX.utils.aoa_to_sheet(aoa);
    ws['!cols'] = header.map(function(h) {
        if (h === 'Student Name') return { wch: 28 };
        if (h === 'Index No.') return { wch: 16 };
        return { wch: 10 };
    });
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Results');
    XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet([
        ['Export columns', modeLabel + ' + Marks'],
        ['Exported sort', sortLabel],
        ['Max marks (for validation)', maxMarks],
        ['Examination', examName],
        ['Class subject ID', classSubjectID],
        ['Rows', exported],
        ['Note', 'Fill Marks only. On import, grade and remark are calculated automatically.']
    ]), 'Export Info');
    const safeMode = String(exportMode).replace(/[^a-z0-9_-]/gi, '');
    const safeSort = String(sortBy).replace(/[^a-z0-9_-]/gi, '');
    downloadWorkbookAsXlsx(wb, 'Results_' + safeMode + '_sort-' + safeSort + '.xlsx');
    return { exported: exported, modeLabel: modeLabel, sortLabel: sortLabel };
}

function downloadExcelTemplate(classSubjectID) {
    classSubjectID = classSubjectID || window.currentResultsClassSubjectID;
    const examID = jQuery('#exam_id').val();
    if (!examID) {
        Swal.fire({ title: 'Error!', text: 'Please select an examination first.', icon: 'error', confirmButtonColor: '#940000' });
        return;
    }
    if (!isDirectMarksEntryMode()) {
        Swal.fire({ title: 'Not available', text: 'Excel export is only for exams without question format.', icon: 'info', confirmButtonColor: '#940000' });
        return;
    }
    if (typeof XLSX === 'undefined') {
        Swal.fire({ title: 'Error!', text: 'Excel library failed to load. Please refresh the page.', icon: 'error', confirmButtonColor: '#940000' });
        return;
    }
    Swal.fire({
        title: 'Export with',
        html: `
            <p class="mb-2 text-left" style="font-size:0.9rem;">Choose student columns. <strong>Marks</strong> is always included.</p>
            <div class="text-left" style="line-height:1.9;">
                <label class="d-block"><input type="radio" name="export_cols_mode" value="name"> Name only + Marks</label>
                <label class="d-block"><input type="radio" name="export_cols_mode" value="index"> Index No. only + Marks</label>
                <label class="d-block"><input type="radio" name="export_cols_mode" value="both" checked> Name + Index No. + Marks</label>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Export',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#940000',
        preConfirm: function() {
            const selected = document.querySelector('.swal2-container input[name="export_cols_mode"]:checked')
                || document.querySelector('input[name="export_cols_mode"]:checked');
            const mode = selected ? selected.value : 'both';
            try {
                return writeResultsExcelFile(classSubjectID, mode);
            } catch (err) {
                Swal.showValidationMessage((err && err.message) ? err.message : 'Export failed');
                return false;
            }
        }
    }).then(function(result) {
        if (!result.isConfirmed || !result.value) return;
        const info = result.value;
        Swal.fire({
            title: 'Exported',
            text: info.exported + ' student(s) · ' + info.modeLabel + ' + Marks · sorted by ' + info.sortLabel,
            icon: 'success',
            timer: 2400,
            showConfirmButton: false
        });
    });
}

window.downloadExcelTemplate = downloadExcelTemplate;
window.writeResultsExcelFile = writeResultsExcelFile;
window.downloadWorkbookAsXlsx = downloadWorkbookAsXlsx;

jQuery(document).off('click.resultsExcelExport').on('click.resultsExcelExport', '#downloadExcelBtn, .js-export-results-excel', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = jQuery(this);
    const locked = $btn.attr('aria-disabled') === 'true' || $btn.hasClass('btn-excel-locked');
    if (locked) {
        if (!jQuery('#exam_id').val()) {
            Swal.fire({ title: 'Select exam', text: 'Please select an examination first.', icon: 'info', confirmButtonColor: '#940000' });
        } else if (!isDirectMarksEntryMode()) {
            Swal.fire({ title: 'Not available', text: 'Excel export is only for exams without question format.', icon: 'info', confirmButtonColor: '#940000' });
        } else {
            Swal.fire({ title: 'Not available', text: 'Excel export is not available for this examination yet.', icon: 'info', confirmButtonColor: '#940000' });
        }
        return;
    }
    downloadExcelTemplate($btn.data('class-subject-id') || window.currentResultsClassSubjectID);
});

jQuery(document).off('click.resultsExcelImport').on('click.resultsExcelImport', '#uploadExcelBtn, .js-import-results-excel', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = jQuery(this);
    const locked = $btn.attr('aria-disabled') === 'true' || $btn.hasClass('btn-excel-locked');
    if (locked) {
        if (!jQuery('#exam_id').val()) {
            Swal.fire({ title: 'Select exam', text: 'Please select an examination first.', icon: 'info', confirmButtonColor: '#940000' });
        } else if (!isDirectMarksEntryMode()) {
            Swal.fire({ title: 'Not available', text: 'Excel import is only for exams without question format.', icon: 'info', confirmButtonColor: '#940000' });
        } else {
            Swal.fire({ title: 'Not available', text: 'Excel import is not available for this examination yet.', icon: 'info', confirmButtonColor: '#940000' });
        }
        return;
    }
    uploadExcelResults($btn.data('class-subject-id') || window.currentResultsClassSubjectID);
});

function uploadExcelResults(classSubjectID) {
    showUploadExcelModal(classSubjectID || window.currentResultsClassSubjectID);
}

function showUploadExcelModal(classSubjectID) {
    const examID = jQuery('#exam_id').val();
    if (!examID) {
        Swal.fire({ title: 'Error!', text: 'Please select an examination first.', icon: 'error', confirmButtonColor: '#940000' });
        return;
    }
    if (!isDirectMarksEntryMode()) {
        Swal.fire({ title: 'Not available', text: 'Excel import is only for exams without question format.', icon: 'info', confirmButtonColor: '#940000' });
        return;
    }
    jQuery('#upload_class_subject_id').val(classSubjectID);
    jQuery('#upload_exam_id').val(examID);
    jQuery('#excel_file').val('');
    jQuery('#excel_file_label').html('<i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)');
    jQuery('#uploadExcelModal').modal('show');
    jQuery('#excel_file').off('change.excelLabel').on('change.excelLabel', function() {
        var fileName = jQuery(this).val().split('\\').pop();
        if (fileName) {
            jQuery('#excel_file_label').html('<i class="bi bi-file-earmark-excel-fill"></i> ' + fileName);
        } else {
            jQuery('#excel_file_label').html('<i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)');
        }
    });
}

// Compatibility aliases for old search inputs (if any leftover)
function filterStudentsTable() { applyResultsSearchSort(); }
function filterStudentsTableEdit() { applyResultsSearchSort(); }

// View Session Attendance
function viewSessionAttendance(classSubjectID) {
    jQuery('#sessionAttendanceModal').modal('show');
    const loader = showPremiumLoading('sessionAttendanceModalBody', "Loading Session Attendance...", "Fetching attendance records for this subject...");

    // Load session attendance view
    jQuery.get(`/teacher/session-attendance/${classSubjectID}`)
    .done(function(response) {
        loader.update(100, "Displaying records...");
        setTimeout(() => {
            jQuery('#sessionAttendanceModalBody').html(response);
        }, 400);
    })
    .fail(function(xhr) {
        jQuery('#sessionAttendanceModalBody').html('<div class="alert alert-danger">Failed to load session attendance. Please try again.</div>');
    });
}

// View Exam Attendance
function viewExamAttendance(classSubjectID, subjectID) {
    jQuery('#examAttendanceModal').modal('show');
    const loader = showPremiumLoading('examAttendanceModalBody', "Loading Exam Attendance...", "Identifying student participation in examinations...");

    // Load exam attendance view
    jQuery.get(`/teacher/exam-attendance/${classSubjectID}`, {
        subjectID: subjectID
    })
    .done(function(response) {
        loader.update(100, "Compiling attendance list...");
        setTimeout(() => {
            jQuery('#examAttendanceModalBody').html(response);
        }, 400);
    })
    .fail(function(xhr) {
        jQuery('#examAttendanceModalBody').html('<div class="alert alert-danger">Failed to load exam attendance. Please try again.</div>');
    });
}
// Helper function to generate weeks for the current year
// Helper function to generate periods (weeks/months) for the current year
function generatePeriods(examID, classSubjectID, testType) {
    const $weekSelect = jQuery('#test_week');
    $weekSelect.empty().append('<option value="">Select Period</option>');

    const today = new Date();
    const currentYear = today.getFullYear();
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    function formatDateRange(start, end) {
        const startMonth = monthNames[start.getMonth()];
        const endMonth = monthNames[end.getMonth()];
        const startDay = start.getDate();
        const endDay = end.getDate();
        const year = end.getFullYear();

        return `${startMonth} ${startDay} to ${endMonth} ${endDay}, ${year}`;
    }

    if (testType === 'monthly_test') {
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        for (let i = 0; i < 12; i++) {
            const periodVal = `Month of ${months[i]} ${currentYear}`;
            $weekSelect.append(`<option value="${periodVal}">${periodVal}</option>`);
            // Auto-select current month
            if (i === today.getMonth()) {
                $weekSelect.val(periodVal);
            }
        }
    } else {
        // Find current week Monday
        const curr = new Date(today);
        const day = curr.getDay(); // 0 is Sunday, 1 is Monday...
        const diff = curr.getDate() - day + (day == 0 ? -6 : 1);
        const monday = new Date(curr.setDate(diff));
        monday.setHours(0,0,0,0);

        // Previous Week (Optional, but user said currently and one ahead)
        // Let's add current and next week as requested
        for (let i = 0; i < 2; i++) {
            const weekStart = new Date(monday);
            weekStart.setDate(monday.getDate() + (i * 7));
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);

            const startStr = weekStart.toISOString().split('T')[0];
            const endStr = weekEnd.toISOString().split('T')[0];
            const weekVal = `Week of ${startStr} to ${endStr}`;
            const weekLabel = formatDateRange(weekStart, weekEnd) + (i === 0 ? " (Current Week)" : " (Next Week)");

            $weekSelect.append(`<option value="${weekVal}">${weekLabel}</option>`);
            if (i === 0) {
                $weekSelect.val(weekVal);
            }
        }
    }

    // Add listener for period change
    $weekSelect.off('change').on('change', function() {
        if (jQuery(this).val()) {
            loadExistingResults(classSubjectID, examID, jQuery(this).val());
        } else {
            jQuery('.marks-input, .grade-input, .remark-input').val('');
        }
    });

    if ($weekSelect.val()) {
        $weekSelect.trigger('change');
    }
}

// Function to open Election Modal
function openElectionModal(classSubjectID, subjectName, subclassID) {
    if (!subclassID || subclassID === 0) {
        Swal.fire('Error', 'This subject is assigned to the entire class. Election is only supported for specific subclasses.', 'warning');
        return;
    }

    jQuery('#electionSubjectTitle').text(subjectName + ' Election');
    jQuery('#electionStudentsContainer').html(
        '<div class="text-center py-4">' +
        '<div class="spinner-border text-primary-custom" role="status">' +
        '<span class="visually-hidden">Loading...</span>' +
        '</div>' +
        '<p class="mt-2">Loading students...</p>' +
        '</div>'
    );

    jQuery('#saveElectionBtn').data('class-subject-id', classSubjectID);
    jQuery('#saveElectionBtn').data('subclass-id', subclassID);

    jQuery('#subjectElectionModal').modal('show');

    // Load students
    loadStudentsForElection(classSubjectID, subclassID);
}

function loadStudentsForElection(classSubjectID, subclassID) {
    jQuery.ajax({
        url: "{{ url('get_subclass_students') }}/" + subclassID,
        type: "GET",
        dataType: 'json',
        success: function(response) {
            if (response.success && response.students && response.students.length > 0) {
                // Fetch already elected
                jQuery.ajax({
                    url: "{{ url('get_subject_electors') }}/" + classSubjectID,
                    type: "GET",
                    dataType: 'json',
                    success: function(electorsResponse) {
                        var electedStudentIDs = [];
                        if (electorsResponse.success && electorsResponse.electors) {
                            electedStudentIDs = electorsResponse.electors.map(function(e) {
                                return e.studentID;
                            });
                        }

                        var html = '<div class="table-responsive">';
                        html += '<table class="table table-hover table-bordered" id="electionStudentsTable">';
                        html += '<thead class="bg-primary-custom text-white">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>Student Name</th>';
                        html += '<th>Index No</th>';
                        html += '<th style="text-align: center;">Election</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';

                        response.students.forEach(function(student, index) {
                            var sid = String(student.studentID);
                            var isElected = electedStudentIDs.some(function(id) {
                                return String(id) === sid;
                            });
                            html += '<tr data-student-id="' + sid + '">';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td>' + student.first_name + ' ' + (student.middle_name ? student.middle_name + ' ' : '') + student.last_name + '</td>';
                            html += '<td>' + (student.examination_number || 'N/A') + '</td>';
                            html += '<td style="text-align: center;">';
                            html += '<input type="checkbox" class="form-check-input election-checkbox" ';
                            html += 'data-student-id="' + sid + '" ';
                            html += 'value="' + sid + '"';
                            if (isElected) {
                                html += ' checked';
                            }
                            html += '>';
                            html += '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        if (jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
                            jQuery('#electionStudentsTable').DataTable().destroy();
                        }
                        jQuery('#electionStudentsContainer').html(html);

                        if (jQuery.fn.DataTable) {
                            jQuery('#electionStudentsTable').DataTable({
                                "pageLength": 25,
                                "order": [[1, "asc"]],
                                "deferRender": false
                            });
                        }
                    },
                    error: function() {
                        jQuery('#electionStudentsContainer').html('<div class="alert alert-danger">Error fetching electors data.</div>');
                    }
                });
            } else {
                jQuery('#electionStudentsContainer').html('<div class="alert alert-info">No students found in this subclass.</div>');
            }
        },
        error: function() {
            jQuery('#electionStudentsContainer').html('<div class="alert alert-danger">Failed to load students.</div>');
        }
    });
}

// Select All Handler
jQuery(document).on('click', '#electionSelectAllBtn', function() {
    if (typeof jQuery.fn.DataTable !== 'undefined' && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
        var table = jQuery('#electionStudentsTable').DataTable();
        table.rows({ page: 'all' }).every(function() {
            jQuery(this.node()).find('.election-checkbox').prop('checked', true);
        });
    } else {
        jQuery('#electionStudentsContainer .election-checkbox').prop('checked', true);
    }

    Swal.fire({
        icon: 'success',
        title: 'Selected All',
        timer: 1000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
    });
});

// Deselect All Handler
jQuery(document).on('click', '#electionDeselectAllBtn', function() {
    if (typeof jQuery.fn.DataTable !== 'undefined' && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
        var table = jQuery('#electionStudentsTable').DataTable();
        table.rows({ page: 'all' }).every(function() {
            jQuery(this.node()).find('.election-checkbox').prop('checked', false);
        });
    } else {
        jQuery('#electionStudentsContainer .election-checkbox').prop('checked', false);
    }

    Swal.fire({
        icon: 'info',
        title: 'Deselected All',
        timer: 1000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
    });
});

// Save Election Handler
jQuery(document).on('click', '#saveElectionBtn', function() {
    var classSubjectID = jQuery(this).data('class-subject-id');
    var subclassID = jQuery(this).data('subclass-id');
    var selectedStudents = [];
    var seen = {};
    function pushStudentId(rawId) {
        if (rawId === undefined || rawId === null || rawId === '') return;
        var id = String(rawId);
        if (seen[id]) return;
        seen[id] = true;
        selectedStudents.push(id);
    }

    if (typeof jQuery.fn.DataTable !== 'undefined' && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
        var table = jQuery('#electionStudentsTable').DataTable();
        table.rows({ page: 'all' }).every(function() {
            var $row = jQuery(this.node());
            var $checkbox = $row.find('.election-checkbox');
            if ($checkbox.length && $checkbox.is(':checked')) {
                pushStudentId($checkbox.val() || $checkbox.data('student-id') || $row.data('student-id'));
            }
        });
    } else {
        jQuery('#electionStudentsContainer .election-checkbox:checked').each(function() {
            pushStudentId(jQuery(this).val() || jQuery(this).data('student-id'));
        });
    }

    var $btn = jQuery(this);
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

    jQuery.ajax({
        url: "{{ route('save_subject_election') }}",
        type: "POST",
        data: {
            classSubjectID: classSubjectID,
            subclassID: subclassID,
            selectedStudents: selectedStudents,
            _token: jQuery('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $btn.prop('disabled', false).html(originalText);
            if (response.success) {
                jQuery('#subjectElectionModal').modal('hide');
                Swal.fire('Success', response.success, 'success').then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalText);
            Swal.fire('Error', 'Failed to save election.', 'error');
        }
    });
});

// Deselect Individual Handler
jQuery(document).on('click', '.deselect-student-btn', function() {
    var $btn = jQuery(this);
    var studentID = $btn.data('student-id');
    var classSubjectID = $btn.data('class-subject-id');
    var studentName = $btn.data('student-name');

    Swal.fire({
        title: 'Deselect Student?',
        text: 'Remove ' + studentName + ' from this subject?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, remove!'
    }).then((result) => {
        if (result.isConfirmed) {
            jQuery.ajax({
                url: "{{ route('deselect_student') }}",
                type: "POST",
                data: {
                    classSubjectID: classSubjectID,
                    studentID: studentID,
                    _token: jQuery('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Removed', response.success, 'success').then(() => {
                            loadStudentsForElection(classSubjectID, jQuery('#saveElectionBtn').data('subclass-id'));
                        });
                    }
                }
            });
        }
    });
});

// Filter students table function
function filterStudentsTable() {
    const searchTerm = jQuery('#studentSearchInput').val().toLowerCase();
    jQuery('#studentsResultsTable tbody tr').each(function() {
        const row = jQuery(this);
        const studentName = row.find('td:nth-child(2)').text().toLowerCase();
        const indexNo = row.find('td:nth-child(3)').text().toLowerCase();

        if (studentName.includes(searchTerm) || indexNo.includes(searchTerm)) {
            row.show();
        } else {
            row.hide();
        }
    });
}

// Filter students table function for edit mode
function filterStudentsTableEdit() {
    const searchTerm = jQuery('#studentSearchInputEdit').val().toLowerCase();
    jQuery('#studentsResultsTableEdit tbody tr').each(function() {
        const row = jQuery(this);
        const studentName = row.find('td:nth-child(2)').text().toLowerCase();
        const indexNo = row.find('td:nth-child(3)').text().toLowerCase();

        if (studentName.includes(searchTerm) || indexNo.includes(searchTerm)) {
            row.show();
        } else {
            row.hide();
        }
    });
}
</script>

@include('includes.footer')
