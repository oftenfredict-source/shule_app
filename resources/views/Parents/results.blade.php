@extends('layouts.parent_corp')

@section('title', __('parent.exam_results_title') . ' - ShuleXpert')

@section('content')
<style>
    :root {
        --primary-red: #940000;
        --light-red: #fff5f5;
        --border-color: #eee;
    }

    .wizard-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        padding: 30px;
        margin-bottom: 30px;
    }

    /* Chain/Step Progress CSS */
    .step-chain {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        position: relative;
        padding: 0 20px;
    }

    .step-chain::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 70px;
        right: 70px;
        height: 3px;
        background: #f0f0f0;
        z-index: 1;
    }

    .step-chain-progress {
        position: absolute;
        top: 25px;
        left: 70px;
        height: 3px;
        background: var(--primary-red);
        z-index: 2;
        transition: width 0.4s ease;
        width: 0%;
        max-width: calc(100% - 140px); /* Prevents going beyond the last circle */
    }

    .step-item {
        position: relative;
        z-index: 3;
        text-align: center;
        width: 100px;
    }

    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: white;
        border: 3px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: 800;
        color: #999;
        transition: all 0.3s ease;
        font-size: 18px;
    }

    .step-item.active .step-circle {
        border-color: var(--primary-red);
        color: var(--primary-red);
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(148,0,0,0.2);
    }

    .step-item.completed .step-circle {
        background: var(--primary-red);
        border-color: var(--primary-red);
        color: white;
    }

    .step-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #999;
        letter-spacing: 0.5px;
    }

    .step-item.active .step-label {
        color: var(--primary-red);
    }

    /* Selection Cards */
    .selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .selection-card {
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .selection-card:hover {
        border-color: var(--primary-red);
        background: var(--light-red);
        transform: translateY(-3px);
    }

    .selection-card.selected {
        border-color: var(--primary-red);
        background: var(--primary-red);
        color: white;
    }

    .selection-card i {
        font-size: 24px;
        margin-bottom: 10px;
        display: block;
    }

    /* Result Table Styles */
    .result-section {
        display: none;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Collegiate UI Styles */
    .color-def-container {
        text-align: center;
        margin-bottom: 20px;
    }
    .color-def-title {
        font-weight: bold;
        color: #6c757d;
        margin-bottom: 10px;
        font-size: 15px;
    }
    .color-blocks {
        display: inline-flex;
    }
    .color-block {
        padding: 10px 40px;
        font-size: 15px;
        color: #6c757d;
    }
    .bg-pass { background-color: #d4edda; }
    .bg-fail { background-color: #f8d7da; }
    .bg-inc { background-color: #d1ecf1; }
    
    .univ-summary-table {
        width: 100%;
        border: 1px solid #ddd;
        background: #fff;
    }
    .univ-summary-header {
        background-color: #f8f9fa;
        font-weight: bold;
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        color: #6c757d;
    }
    .univ-summary-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        color: #555;
    }
    .univ-summary-table td.label {
        font-weight: bold;
        color: #6c757d;
        width: 250px;
        text-align: right;
        border-right: 1px solid #eee;
    }
    .univ-summary-table tr:last-child td {
        border-bottom: none;
    }
    .univ-dl-link {
        display: block;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-top: none;
        background-color: #fff;
        color: #337ab7;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 30px;
    }
    .univ-dl-link:hover {
        background-color: #f8f9fa;
        text-decoration: none;
    }
    
    .univ-section-title {
        font-weight: bold;
        font-size: 18px;
        margin: 25px 0 15px 0;
        color: #333;
    }
    
    .univ-marks-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #ddd;
        background: #fff;
        table-layout: auto;
    }
    .univ-marks-table th.col-somo { width: 22%; }
    .univ-marks-table th.col-mtihani { width: auto; }
    .univ-marks-table th.col-small {
        white-space: nowrap;
        width: 1%;
    }
    .univ-marks-table td.col-small {
        white-space: nowrap;
        text-align: center;
    }
    .univ-marks-table td.col-mtihani {
        word-break: break-word;
    }
    .univ-marks-table th {
        background-color: #fff;
        color: #555;
        font-weight: bold;
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        border-right: 1px solid #eee;
    }
    .univ-marks-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        border-right: 1px solid #eee;
        color: #6c757d;
    }
    .univ-marks-table td.colored-remark {
        text-align: center;
        font-weight: 500;
        padding: 8px 4px !important;
    }
    .bg-pass { background-color: #d4edda !important; color: #155724 !important; }
    .bg-fail { background-color: #f8d7da !important; color: #721c24 !important; }
    .bg-inc { background-color: #d1ecf1 !important; color: #0c5460 !important; }

    .exam-sub-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .exam-sub-item {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }
    .exam-sub-item:last-child {
        border-bottom: none;
    }
    
    .btn-premium-download {
        background-color: #f8f9fa;
        color: #940000;
        border: 1px solid #940000;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .btn-premium-download:hover {
        background-color: #940000;
        color: #white;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .step-chain {
            padding: 0;
        }
        .step-item {
            width: 70px;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
        .step-label {
            font-size: 9px;
        }
        .btn-back-responsive {
            width: 100%;
            margin-bottom: 5px;
        }
        .step-chain::before {
            left: 35px;
            right: 35px;
            top: 20px;
        }
        .step-chain-progress {
            left: 35px;
            max-width: calc(100% - 70px);
            top: 20px;
        }
        .selection-grid {
            grid-template-columns: 1fr; /* Make cards full width on mobile */
        }
        
        .container-fluid {
            padding-left: 2px !important;
            padding-right: 2px !important;
        }
        .univ-marks-table th, .univ-marks-table td {
            font-size: 10px !important;
            padding: 6px 4px !important;
        }
        .exam-sub-item {
            padding: 2px 0;
            word-break: break-word;
        }
        .univ-marks-table {
            width: 100% !important;
            table-layout: auto !important;
        }
        .colored-remark {
            padding: 5px 2px !important;
        }
    }
        
        .col-lg-10 {
            padding-left: 2px !important;
            padding-right: 2px !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .wizard-container {
            padding: 10px 5px !important;
            border-radius: 8px !important;
            margin: 0 auto !important;
            width: 98% !important;
        }

        .step-chain {
            padding: 0 5px;
            margin-bottom: 25px;
        }

        /* Color Definition Responsive */
        .color-blocks {
            display: flex;
            flex-direction: row;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .color-block {
            flex: 1;
            padding: 8px 5px;
            text-align: center;
            border-right: 1px solid #fff;
            font-size: 11px;
        }
        .color-block:last-child {
            border-right: none;
        }

        /* Summary Table Responsive (Keep Tabular) */
        .univ-summary-table td {
            padding: 8px 10px;
        }
        .univ-summary-table td.label {
            font-size: 12px;
            width: 45%;
        }
        .univ-summary-table td:not(.label) {
            font-size: 13px;
        }

        /* Marks Table Responsive (Keep Tabular) */
        .univ-marks-table th {
            padding: 8px 5px;
            font-size: 10px;
        }
        .univ-marks-table td {
            padding: 8px 5px;
            font-size: 12px;
        }
        .univ-marks-table td.colored-remark {
            padding: 8px 5px;
            font-size: 11px;
        }
    }

    .nav-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="wizard-container">
                <!-- Back Button - shown when results are displayed -->
                <div id="backBtnWrapper" style="display:none; margin-bottom: 10px;">
                    <button class="btn btn-outline-danger btn-back-responsive" id="backToWizardBtn" style="border-radius: 20px; font-weight: bold;">
                        <i class="bx bx-arrow-back mr-2"></i> {{ __('parent.back_wizard') }}
                    </button>
                </div>
                <h4 class="mb-4 text-center font-weight-bold" style="color: var(--primary-red);">
                    <i class="bx bx-trophy"></i> {{ __('parent.student_results') }}
                </h4>

                <div class="alert alert-light border mb-3" style="border-left:4px solid #940000 !important;border-radius:0;">
                    <i class="bx bx-info-circle"></i>
                    {{ __('parent.start_choose_student') }}
                </div>

                <!-- Step Progress Chain -->
                <div class="step-chain">
                    <div class="step-chain-progress" id="stepProgress"></div>
                    <div class="step-item active" id="step1-tab">
                        <div class="step-circle">1</div>
                        <div class="step-label">{{ __('parent.step_student') }}</div>
                    </div>
                    <div class="step-item" id="step2-tab">
                        <div class="step-circle">2</div>
                        <div class="step-label">{{ __('parent.step_year') }}</div>
                    </div>
                    <div class="step-item" id="step3-tab">
                        <div class="step-circle">3</div>
                        <div class="step-label">{{ __('parent.step_term') }}</div>
                    </div>
                    <div class="step-item" id="step4-tab">
                        <div class="step-circle">4</div>
                        <div class="step-label">{{ __('parent.step_exam') }}</div>
                    </div>
                </div>

                <!-- Wizard Content -->
                <div class="wizard-content">
                    <!-- Step 1: Student -->
                    <div class="wizard-step" id="step1">
                        <h5 class="font-weight-bold mb-3">{{ __('parent.step1_student') }}</h5>
                        <div class="selection-grid" id="studentGrid">
                            @foreach($students as $student)
                                <div class="selection-card"
                                     data-value="{{ $student->studentID }}"
                                     data-is-a-level="{{ !empty($student->is_a_level) ? '1' : '0' }}"
                                     data-level-label="{{ $student->level_label ?? '' }}">
                                    <i class="bx bx-user"></i>
                                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                    <br><small class="text-muted">{{ $student->admission_number }}</small>
                                    @if(!empty($student->level_label))
                                        <br><small style="color:#940000;font-weight:600;">{{ $student->level_label }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Step 2: Academic Year (filtered by student level) -->
                    <div class="wizard-step d-none" id="step2">
                        <h5 class="font-weight-bold mb-3">{{ __('parent.step2_year') }}</h5>
                        <div class="small text-muted mb-2" id="yearLevelHint"></div>
                        <div class="selection-grid" id="yearGrid">
                            <div class="col-12 text-muted small text-center py-3">{{ __('parent.choose_student_first') }}</div>
                        </div>
                        <div id="noYearsMessage" class="alert alert-warning d-none mt-3">
                            {{ __('parent.no_year_for_level') }}
                        </div>
                    </div>

                    <!-- Step 3: Term Selection -->
                    <div class="wizard-step d-none" id="step3">
                        <h5 class="font-weight-bold mb-3">{{ __('parent.step3_term') }}</h5>
                        <div class="selection-grid" id="termGrid">
                            <div class="selection-card" data-value="first_term">
                                <i class="bx bx-time-five"></i>
                                <strong>{{ __('parent.first_term') }}</strong>
                            </div>
                            <div class="selection-card" data-value="second_term">
                                <i class="bx bx-time"></i>
                                <strong>{{ __('parent.second_term') }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Exam Selection -->
                    <div class="wizard-step d-none" id="step4">
                        <h5 class="font-weight-bold mb-3">{{ __('parent.step4_exam') }}</h5>
                        <div class="selection-grid" id="examGrid">
                            <!-- Dynamically loaded -->
                        </div>
                        <div id="noExamsMessage" class="alert alert-warning d-none mt-3">
                            {{ __('parent.no_exams_published') }}
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="nav-buttons mt-5">
                        <button class="btn btn-outline-secondary px-4 d-none" id="prevBtn">
                            <i class="bx bx-chevron-left"></i> {{ __('parent.previous') }}
                        </button>
                        <div class="ms-auto">
                            <button class="btn btn-primary px-4 d-none" id="nextBtn">
                                {{ __('parent.continue') }} <i class="bx bx-chevron-right"></i>
                            </button>
                            <button class="btn btn-success px-4 d-none" id="viewBtn">
                                <i class="bx bx-show"></i> {{ __('parent.view_results') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="resultDisplay" class="result-section" style="margin-top: -10px;">
                <!-- Color Definition -->
                <div class="color-def-container">
                    <div class="color-def-title">{{ __('parent.color_meaning') }}</div>
                    <div class="color-blocks">
                        <div class="color-block bg-pass">{{ __('parent.pass') }}</div>
                        <div class="color-block bg-fail">{{ __('parent.fail') }}</div>
                        <div class="color-block bg-inc">{{ __('parent.incomplete') }}</div>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="univ-summary-table">
                    <div class="univ-summary-header">{{ __('parent.results_summary') }}</div>
                    <table style="width: 100%;">
                        <tbody id="overviewBody">
                            <!-- Loaded via JS -->
                        </tbody>
                    </table>
                </div>
                
                <a href="#" id="univDownloadBtn" class="univ-dl-link btn-premium-download" style="text-align: center; display: block; padding: 12px; border-radius: 10px; text-decoration: none; margin: 20px 0;">
                    <i class="bx bx-download mr-2"></i> {{ __('parent.download_results_pdf') }}
                </a>

                <div class="univ-section-title" id="semesterTitle">{{ __('parent.exam_results_heading') }}</div>

                <!-- Detail Table -->
                <div style="overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch;">
                    <table class="univ-marks-table">
                        <thead id="marksHead">
                            <!-- Loaded via JS -->
                        </thead>
                        <tbody id="marksBody">
                            <!-- Loaded via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Form for PDF Download -->
<form id="downloadForm" action="{{ route('download_student_result') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="studentID" id="dl_studentID">
    <input type="hidden" name="examID" id="dl_examID">
    <input type="hidden" name="year" id="dl_year">
    <input type="hidden" name="term" id="dl_term">
</form>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const P = @json(trans('parent'));
    let currentStep = 1;
    let selections = {
        student: '',
        year: '',
        academic_yearID: '',
        term: '',
        exam: ''
    };
    const ayMode = @json($ayMode ?? 'none');
    const allAcademicYears = @json($academicYears ?? []);
    const fallbackYears = @json($years ?? []);

    const renderYearGrid = (isALevel, levelLabel) => {
        $('#noYearsMessage').addClass('d-none');
        let years = allAcademicYears.slice();
        if (ayMode === 'secondary_dual') {
            years = years.filter(function(y) { return !!y.is_a_level === !!isALevel; });
            $('#yearLevelHint').text(isALevel
                ? 'Mwanafunzi ni A Level, chagua academic year ya A Level.'
                : 'Mwanafunzi ni O Level, chagua academic year ya O Level.');
        } else {
            $('#yearLevelHint').text(levelLabel ? ('Kiwango: ' + levelLabel) : '');
        }

        if (!years.length && fallbackYears.length && ayMode !== 'secondary_dual') {
            years = fallbackYears.map(function(y) {
                return { academic_yearID: '', label: String(y), short_label: String(y), calendar_year: y, is_a_level: false };
            });
        }

        if (!years.length) {
            $('#yearGrid').html('');
            $('#noYearsMessage').removeClass('d-none');
            return;
        }

        let html = '';
        years.forEach(function(ay) {
            const label = (ayMode === 'secondary_dual' ? (ay.short_label || ay.label) : (ay.label || ay.short_label));
            html += '<div class="selection-card" data-value="' + ay.calendar_year + '" data-academic-year-id="' + (ay.academic_yearID || '') + '" data-label="' + String(label).replace(/"/g, '&quot;') + '">'
                + '<i class="bx bx-calendar"></i>'
                + '<strong>' + label + '</strong>'
                + '</div>';
        });
        $('#yearGrid').html(html);
    };

    // Check if session exists to auto-load results on refresh
    const savedSession = sessionStorage.getItem('parent_results_selections');
    if(savedSession) {
        try {
            selections = JSON.parse(savedSession);
            // Hide wizard internals, show back button
            $('.step-chain, .wizard-content, h4.text-center').hide();
            $('#backBtnWrapper').show();
            $('#resultDisplay').show();
            $('#overviewBody').html('<tr><td colspan="5" class="text-center py-5"><i class="bx bx-loader bx-spin text-danger" style="font-size: 30px;"></i><br>' + P.loading_results + '</td></tr>');
            
            $.ajax({
                url: '{{ route("fetch_student_results_ajax") }}',
                data: selections,
                success: function(response) {
                    if(response.success) {
                        renderResults(response.data);
                    } else {
                        sessionStorage.removeItem('parent_results_selections');
                        $('.step-chain, .wizard-content, h4.text-center').show();
                        $('#backBtnWrapper').hide();
                        $('#resultDisplay').hide();
                        Swal.fire(P.error, response.message || P.could_not_get_results, 'error');
                    }
                },
                error: function() {
                    sessionStorage.removeItem('parent_results_selections');
                    $('.step-chain, .wizard-content, h4.text-center').show();
                    $('#backBtnWrapper').hide();
                    $('#resultDisplay').hide();
                    Swal.fire(P.error, P.network_error, 'error');
                }
            });
        } catch(e) {}
    }

    const updateWizard = () => {
        // Update Steps UI
        $('.step-item').removeClass('active completed');
        for(let i=1; i<=4; i++) {
            if(i < currentStep) $(`#step${i}-tab`).addClass('completed');
            if(i === currentStep) $(`#step${i}-tab`).addClass('active');
        }

        // Update Progress Bar
        const progress = ((currentStep - 1) / 3) * 100;
        $('#stepProgress').css('width', `${progress}%`);

        // Show/Hide Steps
        $('.wizard-step').addClass('d-none');
        $(`#step${currentStep}`).removeClass('d-none');

        // Update Buttons
        if(currentStep === 1) $('#prevBtn').addClass('d-none');
        else $('#prevBtn').removeClass('d-none');

        if(currentStep === 4) {
            $('#nextBtn').addClass('d-none');
            $('#viewBtn').removeClass('d-none');
        } else {
            $('#nextBtn').removeClass('d-none');
            $('#viewBtn').addClass('d-none');
        }

        // Disable next if no selection
        const key = ['student', 'year', 'term', 'exam'][currentStep - 1];
        if(!selections[key]) $('#nextBtn, #viewBtn').prop('disabled', true);
        else $('#nextBtn, #viewBtn').prop('disabled', false);
    };

    // Card Selection
    $(document).on('click', '.selection-card', function() {
        const step = $(this).closest('.wizard-step').attr('id');
        const key = step.replace('step', '');
        const map = { '1': 'student', '2': 'year', '3': 'term', '4': 'exam' };
        const dataKey = map[key];

        $(`#${step} .selection-card`).removeClass('selected');
        $(this).addClass('selected');
        selections[dataKey] = $(this).data('value');

        if (dataKey === 'student') {
            selections.year = '';
            selections.academic_yearID = '';
            selections.term = '';
            selections.exam = '';
            $('#termGrid .selection-card').removeClass('selected');
            $('#examGrid').html('');
            renderYearGrid($(this).data('is-a-level') == 1 || $(this).data('is-a-level') === true, $(this).data('level-label') || '');
        }

        if (dataKey === 'year') {
            selections.academic_yearID = $(this).data('academic-year-id') || '';
            selections.term = '';
            selections.exam = '';
            $('#termGrid .selection-card').removeClass('selected');
            $('#examGrid').html('');
        }

        if (dataKey === 'term') {
            selections.exam = '';
        }

        // Auto move to next step if possible
        if(currentStep < 3) {
            currentStep++;
            updateWizard();
        } else if(currentStep === 3) {
            currentStep++;
            loadExams();
            updateWizard();
        } else {
            updateWizard();
        }
    });

    $('#nextBtn').click(function() {
        if(currentStep < 4) {
            currentStep++;
            if(currentStep === 4) loadExams();
            updateWizard();
        }
    });

    $('#prevBtn').click(function() {
        if(currentStep > 1) {
            currentStep--;
            updateWizard();
        }
    });

    const loadExams = () => {
        $('#examGrid').html('<div class="col-12 text-center py-4"><div class="spinner-border text-danger"></div></div>');
        $('#noExamsMessage').addClass('d-none');
        selections.exam = '';

        $.ajax({
            url: '{{ route("get_published_exams") }}',
            data: {
                year: selections.year,
                term: selections.term,
                academic_yearID: selections.academic_yearID || ''
            },
            success: function(response) {
                if(response.success && response.exams.length > 0) {
                    let html = '';
                    response.exams.forEach(exam => {
                        html += `
                            <div class="selection-card" data-value="${exam.examID}">
                                <i class="bx bx-file"></i>
                                <strong>${exam.exam_name}</strong>
                                <br><small class="text-muted">${P.published_recently}</small>
                            </div>`;
                    });
                    $('#examGrid').html(html);
                } else {
                    $('#examGrid').html('');
                    $('#noExamsMessage').removeClass('d-none');
                }
                updateWizard();
            }
        });
    };

    $('#viewBtn').click(function() {
        $(this).html('<i class="bx bx-loader bx-spin"></i> ' + P.searching);
        
        $.ajax({
            url: '{{ route("fetch_student_results_ajax") }}',
            data: selections,
            success: function(response) {
                $('#viewBtn').html('<i class="bx bx-show"></i> ' + P.view_results);
                if(response.success) {
                    sessionStorage.setItem('parent_results_selections', JSON.stringify(selections));
                    // Hide wizard internals, show back button at top
                    $('.step-chain, .wizard-content, h4.text-center').hide();
                    $('#backBtnWrapper').show();
                    renderResults(response.data);
                    $('#resultDisplay').fadeIn();
                    window.scrollTo(0, 0);
                } else {
                    Swal.fire(P.error, response.message || P.could_not_get_results, 'error');
                }
            },
            error: function() {
                $('#viewBtn').html('<i class="bx bx-show"></i> ' + P.view_results);
                Swal.fire(P.error, P.network_error, 'error');
            }
        });
    });

    $('#backToWizardBtn').click(function(e) {
        e.preventDefault();
        sessionStorage.removeItem('parent_results_selections');
        $('#resultDisplay').hide();
        // Restore wizard internals
        $('.step-chain, .wizard-content, h4.text-center').show();
        $('#backBtnWrapper').hide();
        updateWizard();
    });

    const getParentSubjectRemark = (grade, isSecondary, isALevel) => {
        const g = String(grade || '').toUpperCase().trim();
        if (!grade || g === 'N/A' || g === 'INCOMPLETE' || g === '-') {
            return { text: P.incomplete, cssClass: 'bg-inc' };
        }
        if (isSecondary) {
            if (isALevel) {
                const map = {
                    A: P.excellent, B: P.very_good, C: P.good,
                    D: P.average_grade, E: P.satisfactory, S: P.subsidiary, F: P.fail
                };
                if (map[g]) {
                    return { text: map[g], cssClass: g === 'F' ? 'bg-fail' : 'bg-pass' };
                }
            } else {
                const map = {
                    A: P.excellent, B: P.very_good, C: P.good, D: P.pass, F: P.fail
                };
                if (map[g]) {
                    return { text: map[g], cssClass: g === 'F' ? 'bg-fail' : 'bg-pass' };
                }
            }
            if (/^(I|II|III|IV)\./.test(g)) {
                return { text: P.pass, cssClass: 'bg-pass' };
            }
            return { text: P.fail, cssClass: 'bg-fail' };
        }
        if (['A', 'B', 'C', 'D'].includes(g)) {
            return { text: P.pass, cssClass: 'bg-pass' };
        }
        return { text: P.fail, cssClass: 'bg-fail' };
    };

    const formatPaperLines = (papers) => {
        if (!papers || !papers.length) return '';
        return papers.map(p => {
            const pm = (p.marks !== null && p.marks !== undefined && p.marks !== '')
                ? `${Math.round(parseFloat(p.marks))}/${p.max_marks || ''}`
                : '-';
            return `<li class="exam-sub-item px-3" style="opacity:.9;">${p.component_name || P.paper}: <strong>${pm}</strong></li>`;
        }).join('');
    };

    const renderResults = (data) => {
        // Handle "N/A" -> "Incomplete"
        const position = (data.position && data.position !== 'N/A') ? data.position : P.incomplete;
        const grade = (data.overall_grade || data.overall_division || P.incomplete);
        const totalMarks = (data.total_marks !== null && data.total_marks !== undefined && data.total_marks !== '-') ? parseFloat(data.total_marks).toFixed(0) : P.incomplete;
        const average = (data.average !== null && data.average !== undefined && data.average !== 0 && data.average !== '-') ? parseFloat(data.average).toFixed(0) : P.incomplete;
        const isSecondary = String(data.school_type || 'Secondary').toLowerCase() === 'secondary';
        const isALevel = !!data.is_a_level;

        // Overview
        $('#overviewBody').html(`
            <tr><td class="label">${P.student_name}:</td><td>${data.student.first_name} ${data.student.last_name}</td></tr>
            <tr><td class="label">${P.total_marks}:</td><td>${totalMarks}</td></tr>
            <tr><td class="label">${P.average}:</td><td>${average}</td></tr>
            <tr><td class="label">${P.exam_division}:</td><td>${grade}</td></tr>
            <tr><td class="label">${P.position}:</td><td>${position}</td></tr>
        `);

        // Marks Table, conditional on whether exam has CA
        const hasCa = data.has_ca;

        let headHtml = '';
        if (hasCa) {
            headHtml = `
                <tr>
                    <th class="col-somo">${P.subject}</th>
                    <th class="col-mtihani">${P.exams}</th>
                    <th class="col-small text-center">${P.average}</th>
                    <th class="col-small text-center">${P.grade}</th>
                    <th class="col-small text-center">${P.remark}</th>
                </tr>`;
        } else {
            headHtml = `
                <tr>
                    <th class="col-somo">${P.subject}</th>
                    <th class="col-small text-center">${P.marks}</th>
                    <th class="col-small text-center">${P.grade}</th>
                    <th class="col-small text-center">${P.remark}</th>
                </tr>`;
        }
        $('#marksHead').html(headHtml);

        // Marks Table Body
        let bodyHtml = '';
        data.results.forEach(res => {
            // Determine if marks are null/empty → Incomplete
            const rawMarks = res.average ?? res.marks;
            const hasMarks = rawMarks !== null && rawMarks !== undefined && rawMarks !== '' && rawMarks !== '-';
            const marksVal = hasMarks ? parseFloat(rawMarks).toFixed(0) : P.incomplete;
            const gradeText = hasMarks ? (res.grade || 'F') : P.incomplete;
            const remarkInfo = getParentSubjectRemark(gradeText, isSecondary, isALevel);
            const remarkClass = remarkInfo.cssClass;
            const remarkText = res.remark || remarkInfo.text;

            let subjectCell = `<strong>${res.subject_name || P.na}</strong>`;
            if (res.is_duo && res.papers && res.papers.length) {
                subjectCell += `<ul class="exam-sub-list mt-1">${formatPaperLines(res.papers)}</ul>`;
            }

            if (hasCa) {
                bodyHtml += `
                    <tr>
                        <td class="align-middle">${subjectCell}</td>
                        <td class="col-mtihani p-0">
                            <ul class="exam-sub-list">
                                ${(res.exams || []).map(ex => {
                                    const exMarks = (ex.marks !== null && ex.marks !== undefined && ex.marks !== '')
                                        ? parseFloat(ex.marks).toFixed(0) : '-';
                                    return `<li class="exam-sub-item px-3">${ex.exam_name}: <strong>${exMarks}</strong></li>${formatPaperLines(ex.papers || [])}`;
                                }).join('')}
                            </ul>
                        </td>
                        <td class="col-small align-middle"><strong>${marksVal}</strong></td>
                        <td class="col-small align-middle">${gradeText}</td>
                        <td class="col-small colored-remark ${remarkClass} align-middle">${remarkText}</td>
                    </tr>
                `;
            } else {
                bodyHtml += `
                    <tr>
                        <td class="align-middle">${subjectCell}</td>
                        <td class="col-small align-middle"><strong>${marksVal}</strong></td>
                        <td class="col-small align-middle">${gradeText}</td>
                        <td class="col-small colored-remark ${remarkClass} align-middle">${remarkText}</td>
                    </tr>
                `;
            }
        });
        $('#marksBody').html(bodyHtml);

        // Update Exam Title
        $('#semesterTitle').text($('#examSelect option:selected').text() || P.exam_results_heading);
    };
    
    $('#univDownloadBtn').click(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: P.preparing_pdf,
            html: P.preparing_results_wait + '<br><br><b></b>%',
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                let progress = 0;
                const timerInterval = setInterval(() => {
                    progress += 5;
                    if(progress > 100) progress = 100;
                    b.textContent = progress;
                }, 150);
            },
            willClose: () => {
                $('#dl_studentID').val(selections.student);
                $('#dl_examID').val(selections.exam);
                $('#dl_year').val(selections.year);
                $('#dl_term').val(selections.term);
                $('#downloadForm').submit();
            }
        });
    });

    $('#downloadBtn').click(function() {
        $('#dl_studentID').val(selections.student);
        $('#dl_examID').val(selections.exam);
        $('#dl_year').val(selections.year);
        $('#dl_term').val(selections.term);
        $('#downloadForm').submit();
    });

    // Initialize
    updateWizard();
});
</script>
@endpush
