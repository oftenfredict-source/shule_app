@extends('layouts.parent_corp')

@section('title', 'Matokeo ya Mitihani - ShuleXpert')

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
                        <i class="bx bx-arrow-back mr-2"></i> Rudi Nyuma
                    </button>
                </div>
                <h4 class="mb-4 text-center font-weight-bold" style="color: var(--primary-red);">
                    <i class="bx bx-trophy"></i> Matokeo ya Mwanafunzi
                </h4>

                <!-- Step Progress Chain -->
                <div class="step-chain">
                    <div class="step-chain-progress" id="stepProgress"></div>
                    <div class="step-item active" id="step1-tab">
                        <div class="step-circle">1</div>
                        <div class="step-label">Mwaka</div>
                    </div>
                    <div class="step-item" id="step2-tab">
                        <div class="step-circle">2</div>
                        <div class="step-label">Muhula</div>
                    </div>
                    <div class="step-item" id="step3-tab">
                        <div class="step-circle">3</div>
                        <div class="step-label">Mtihani</div>
                    </div>
                    <div class="step-item" id="step4-tab">
                        <div class="step-circle">4</div>
                        <div class="step-label">Mwanafunzi</div>
                    </div>
                </div>

                <!-- Wizard Content -->
                <div class="wizard-content">
                    <!-- Step 1: Year Selection -->
                    <div class="wizard-step" id="step1">
                        <h5 class="font-weight-bold mb-3">Hatua ya 1: Chagua Mwaka wa Masomo</h5>
                        <div class="selection-grid" id="yearGrid">
                            @foreach($years as $year)
                                <div class="selection-card" data-value="{{ $year }}">
                                    <i class="bx bx-calendar"></i>
                                    <strong>{{ $year }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Step 2: Term Selection -->
                    <div class="wizard-step d-none" id="step2">
                        <h5 class="font-weight-bold mb-3">Hatua ya 2: Chagua Muhula</h5>
                        <div class="selection-grid" id="termGrid">
                            <div class="selection-card" data-value="first_term">
                                <i class="bx bx-time-five"></i>
                                <strong>Muhula wa Kwanza</strong>
                            </div>
                            <div class="selection-card" data-value="second_term">
                                <i class="bx bx-time"></i>
                                <strong>Muhula wa Pili</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Exam Selection -->
                    <div class="wizard-step d-none" id="step3">
                        <h5 class="font-weight-bold mb-3">Hatua ya 3: Chagua Mtihani</h5>
                        <div class="selection-grid" id="examGrid">
                            <!-- Dynamically loaded -->
                        </div>
                        <div id="noExamsMessage" class="alert alert-warning d-none mt-3">
                            Hakuna mitihani iliyochapishwa kwa chaguzi hizi.
                        </div>
                    </div>

                    <!-- Step 4: Student Selection -->
                    <div class="wizard-step d-none" id="step4">
                        <h5 class="font-weight-bold mb-3">Hatua ya 4: Chagua Mwanafunzi</h5>
                        <div class="selection-grid" id="studentGrid">
                            @foreach($students as $student)
                                <div class="selection-card" data-value="{{ $student->studentID }}">
                                    <i class="bx bx-user"></i>
                                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                    <br><small class="text-muted">{{ $student->admission_number }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="nav-buttons mt-5">
                        <button class="btn btn-outline-secondary px-4 d-none" id="prevBtn">
                            <i class="bx bx-chevron-left"></i> Rudia
                        </button>
                        <div class="ms-auto">
                            <button class="btn btn-primary px-4 d-none" id="nextBtn">
                                Endelea <i class="bx bx-chevron-right"></i>
                            </button>
                            <button class="btn btn-success px-4 d-none" id="viewBtn">
                                <i class="bx bx-show"></i> Angalia Matokeo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="resultDisplay" class="result-section" style="margin-top: -10px;">
                <!-- Color Definition -->
                <div class="color-def-container">
                    <div class="color-def-title">Tafsiri ya Rangi za Matokeo</div>
                    <div class="color-blocks">
                        <div class="color-block bg-pass">Ufaulu</div>
                        <div class="color-block bg-fail">Kufeli</div>
                        <div class="color-block bg-inc">Haijakamilika</div>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="univ-summary-table">
                    <div class="univ-summary-header">Results Summary</div>
                    <table style="width: 100%;">
                        <tbody id="overviewBody">
                            <!-- Loaded via JS -->
                        </tbody>
                    </table>
                </div>
                
                <a href="#" id="univDownloadBtn" class="univ-dl-link btn-premium-download" style="text-align: center; display: block; padding: 12px; border-radius: 10px; text-decoration: none; margin: 20px 0;">
                    <i class="bx bx-download mr-2"></i> Bofya hapa ku sevu matokeo (PDF)
                </a>

                <div class="univ-section-title" id="semesterTitle">Matokeo ya Mtihani</div>

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
    let currentStep = 1;
    let selections = {
        year: '',
        term: '',
        exam: '',
        student: ''
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
            $('#overviewBody').html('<tr><td colspan="5" class="text-center py-5"><i class="bx bx-loader bx-spin text-danger" style="font-size: 30px;"></i><br>Inaleta Matokeo...</td></tr>');
            
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
                        Swal.fire('Error', response.message || 'Haikuweza kupata matokeo.', 'error');
                    }
                },
                error: function() {
                    sessionStorage.removeItem('parent_results_selections');
                    $('.step-chain, .wizard-content, h4.text-center').show();
                    $('#backBtnWrapper').hide();
                    $('#resultDisplay').hide();
                    Swal.fire('Error', 'Tatizo la mtandao limetokea.', 'error');
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
        const key = ['year', 'term', 'exam', 'student'][currentStep - 1];
        if(!selections[key]) $('#nextBtn, #viewBtn').prop('disabled', true);
        else $('#nextBtn, #viewBtn').prop('disabled', false);
    };

    // Card Selection
    $(document).on('click', '.selection-card', function() {
        const step = $(this).closest('.wizard-step').attr('id');
        const key = step.replace('step', '');
        const map = { '1': 'year', '2': 'term', '3': 'exam', '4': 'student' };
        const dataKey = map[key];

        $(`#${step} .selection-card`).removeClass('selected');
        $(this).addClass('selected');
        selections[dataKey] = $(this).data('value');

        // Auto move to next step if possible
        if(currentStep < 3) {
            currentStep++;
            if(currentStep === 3) loadExams();
            updateWizard();
        } else if(currentStep === 3) {
            currentStep++;
            updateWizard();
        } else {
            updateWizard();
        }
    });

    $('#nextBtn').click(function() {
        if(currentStep < 4) {
            currentStep++;
            if(currentStep === 3) loadExams();
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
            data: { year: selections.year, term: selections.term },
            success: function(response) {
                if(response.success && response.exams.length > 0) {
                    let html = '';
                    response.exams.forEach(exam => {
                        html += `
                            <div class="selection-card" data-value="${exam.examID}">
                                <i class="bx bx-file"></i>
                                <strong>${exam.exam_name}</strong>
                                <br><small class="text-muted">Ilichapishwa hivi karibuni</small>
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
        $(this).html('<i class="bx bx-loader bx-spin"></i> Inatafuta...');
        
        $.ajax({
            url: '{{ route("fetch_student_results_ajax") }}',
            data: selections,
            success: function(response) {
                $('#viewBtn').html('<i class="bx bx-show"></i> Angalia Matokeo');
                if(response.success) {
                    sessionStorage.setItem('parent_results_selections', JSON.stringify(selections));
                    // Hide wizard internals, show back button at top
                    $('.step-chain, .wizard-content, h4.text-center').hide();
                    $('#backBtnWrapper').show();
                    renderResults(response.data);
                    $('#resultDisplay').fadeIn();
                    window.scrollTo(0, 0);
                } else {
                    Swal.fire('Error', response.message || 'Haikuweza kupata matokeo.', 'error');
                }
            },
            error: function() {
                $('#viewBtn').html('<i class="bx bx-show"></i> Angalia Matokeo');
                Swal.fire('Error', 'Tatizo la mtandao limetokea.', 'error');
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

    const renderResults = (data) => {
        // Handle "N/A" -> "Incomplete"
        const position = (data.position && data.position !== 'N/A') ? data.position : 'Incomplete';
        const grade = (data.overall_grade || data.overall_division || 'Incomplete');
        const totalMarks = (data.total_marks !== null && data.total_marks !== undefined) ? parseFloat(data.total_marks).toFixed(0) : 'Incomplete';
        const average = (data.average !== null && data.average !== undefined && data.average !== 0) ? parseFloat(data.average).toFixed(0) : 'Incomplete';

        // Overview
        $('#overviewBody').html(`
            <tr><td class="label">Jina la Mwanafunzi:</td><td>${data.student.first_name} ${data.student.last_name}</td></tr>
            <tr><td class="label">Jumla ya Alama:</td><td>${totalMarks}</td></tr>
            <tr><td class="label">Wastani:</td><td>${average}</td></tr>
            <tr><td class="label">Divisheni ya Mtihani:</td><td>${grade}</td></tr>
            <tr><td class="label">Nafasi:</td><td>${position}</td></tr>
        `);

        // Marks Table — conditional on whether exam has CA
        const hasCa = data.has_ca;

        let headHtml = '';
        if (hasCa) {
            headHtml = `
                <tr>
                    <th class="col-somo">Somo</th>
                    <th class="col-mtihani">Mitihani</th>
                    <th class="col-small text-center">Wastani</th>
                    <th class="col-small text-center">Daraja</th>
                    <th class="col-small text-center">Remark</th>
                </tr>`;
        } else {
            headHtml = `
                <tr>
                    <th class="col-somo">Somo</th>
                    <th class="col-small text-center">Alama</th>
                    <th class="col-small text-center">Daraja</th>
                    <th class="col-small text-center">Remark</th>
                </tr>`;
        }
        $('#marksHead').html(headHtml);

        // Marks Table Body
        let bodyHtml = '';
        data.results.forEach(res => {
            // Determine if marks are null/empty → Incomplete
            const rawMarks = res.average ?? res.marks;
            const hasMarks = rawMarks !== null && rawMarks !== undefined && rawMarks !== '' && rawMarks !== '-';
            const marksVal = hasMarks ? parseFloat(rawMarks).toFixed(0) : 'Incomplete';
            const gradeText = hasMarks ? (res.grade || 'F') : 'Incomplete';

            // Determine remark class / text
            let remarkClass = 'bg-fail'; 
            let remarkText = 'Failed';
            if (!hasMarks || gradeText === 'Incomplete') {
                remarkClass = 'bg-inc';
                remarkText = 'Incomplete';
            } else if (['A', 'B', 'C', 'D'].includes(gradeText)) {
                remarkClass = 'bg-pass';
                remarkText = 'Pass';
            }

            if (hasCa) {
                bodyHtml += `
                    <tr>
                        <td class="align-middle"><strong>${res.subject_name || 'N/A'}</strong></td>
                        <td class="col-mtihani p-0">
                            <ul class="exam-sub-list">
                                ${(res.exams || []).map(ex => `<li class="exam-sub-item px-3">${ex.exam_name}: <strong>${ex.marks ? parseFloat(ex.marks).toFixed(0) : '-'}</strong></li>`).join('')}
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
                        <td class="align-middle"><strong>${res.subject_name || 'N/A'}</strong></td>
                        <td class="col-small align-middle"><strong>${marksVal}</strong></td>
                        <td class="col-small align-middle">${gradeText}</td>
                        <td class="col-small colored-remark ${remarkClass} align-middle">${remarkText}</td>
                    </tr>
                `;
            }
        });
        $('#marksBody').html(bodyHtml);

        // Update Exam Title
        $('#semesterTitle').text($('#examSelect option:selected').text() || 'Matokeo ya Mtihani');
    };
    
    $('#univDownloadBtn').click(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Inatayarisha PDF...',
            html: 'Tafadhali subiri kidogo wakati matokeo yako yanatayarishwa.<br><br><b></b>%',
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
