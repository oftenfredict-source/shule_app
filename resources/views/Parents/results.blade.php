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
        left: 50px;
        right: 50px;
        height: 3px;
        background: #f0f0f0;
        z-index: 1;
    }

    .step-chain-progress {
        position: absolute;
        top: 25px;
        left: 50px;
        height: 3px;
        background: var(--primary-red);
        z-index: 2;
        transition: width 0.4s ease;
        width: 0%;
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

    .overview-table {
        background: var(--light-red);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 25px;
        border: 1px solid rgba(148,0,0,0.1);
    }

    .overview-table th {
        background: var(--primary-red);
        color: white;
        padding: 12px 20px;
        font-size: 12px;
        text-transform: uppercase;
    }

    .overview-table td {
        padding: 15px 20px;
        font-weight: 700;
        color: var(--primary-red);
        font-size: 16px;
    }

    .marks-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .marks-table th {
        padding: 15px;
        font-size: 11px;
        text-transform: uppercase;
        color: #777;
        font-weight: 800;
        border-bottom: 2px solid #f0f0f0;
    }

    .marks-table tr {
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    .marks-table td {
        padding: 15px;
        border-top: 1px solid #f9f9f9;
        border-bottom: 1px solid #f9f9f9;
    }

    .marks-table td:first-child { border-left: 1px solid #f9f9f9; border-radius: 10px 0 0 10px; }
    .marks-table td:last-child { border-right: 1px solid #f9f9f9; border-radius: 0 10px 10px 0; }

    .grade-badge {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-weight: 800;
        font-size: 14px;
    }

    .grade-A { background: #e6fcf5; color: #0ca678; }
    .grade-B { background: #e7f5ff; color: #1c7ed6; }
    .grade-C { background: #fff9db; color: #f08c00; }
    .grade-D { background: #fff4e6; color: #fd7e14; }
    .grade-F { background: #fff5f5; color: #fa5252; }

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
        .selection-grid {
            grid-template-columns: 1fr 1fr;
        }
        .wizard-container {
            padding: 15px;
        }
        .overview-table td {
            font-size: 14px;
            padding: 10px;
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
            <div id="resultDisplay" class="result-section">
                <!-- Overview Table -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">Muhtasari wa Matokeo</h5>
                        <button class="btn btn-sm btn-danger px-3" id="downloadBtn">
                            <i class="bx bx-download"></i> Pakua (PDF)
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table overview-table mb-0">
                            <thead>
                                <tr>
                                    <th>Jina la Mwanafunzi</th>
                                    <th class="text-center">Jumla</th>
                                    <th class="text-center">Wastani</th>
                                    <th class="text-center">Daraja</th>
                                    <th class="text-center">Nafasi</th>
                                </tr>
                            </thead>
                            <tbody id="overviewBody">
                                <!-- Dynamically loaded -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Marks Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-weight-bold">Mchanganuo wa Masomo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table marks-table">
                                <thead id="marksHead">
                                    <!-- Dynamically loaded -->
                                </thead>
                                <tbody id="marksBody">
                                    <!-- Dynamically loaded -->
                                </tbody>
                            </table>
                        </div>
                    </div>
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
        $('#resultDisplay').hide();
        $(this).html('<i class="bx bx-loader bx-spin"></i> Inatafuta...');
        
        $.ajax({
            url: '{{ route("fetch_student_results_ajax") }}',
            data: selections,
            success: function(response) {
                $('#viewBtn').html('<i class="bx bx-show"></i> Angalia Matokeo');
                if(response.success) {
                    renderResults(response.data);
                    $('#resultDisplay').fadeIn();
                    $('html, body').animate({ scrollTop: $("#resultDisplay").offset().top - 50 }, 500);
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

    const renderResults = (data) => {
        // Overview
        $('#overviewBody').html(`
            <tr>
                <td>${data.student.first_name} ${data.student.last_name}</td>
                <td class="text-center">${data.total_marks}</td>
                <td class="text-center">${data.average}</td>
                <td class="text-center"><span class="badge bg-danger">${data.overall_grade || data.overall_division || 'N/A'}</span></td>
                <td class="text-center"><span class="badge bg-success">${data.position || 'N/A'}</span></td>
            </tr>
        `);

        // Marks Table Header
        let headHtml = `
            <tr>
                <th>Somo</th>
                ${data.has_ca ? '<th class="text-center">CA</th>' : ''}
                ${data.has_ca ? '<th class="text-center">Main</th>' : ''}
                <th class="text-center">Alama</th>
                <th class="text-center">Daraja</th>
                <th>Maoni</th>
            </tr>
        `;
        $('#marksHead').html(headHtml);

        // Marks Table Body
        let bodyHtml = '';
        data.results.forEach(res => {
            const gradeClass = `grade-${res.grade}`;
            bodyHtml += `
                <tr>
                    <td class="font-weight-bold">${res.subject_name}</td>
                    ${data.has_ca ? `<td class="text-center text-muted">${res.ca_marks || '-'}</td>` : ''}
                    ${data.has_ca ? `<td class="text-center text-muted">${res.main_exam_marks || '-'}</td>` : ''}
                    <td class="text-center font-weight-bold" style="color: var(--primary-red);">${res.marks}</td>
                    <td class="text-center">
                        <span class="grade-badge ${gradeClass}">${res.grade}</span>
                    </td>
                    <td class="text-muted small">${res.remark || 'Maendeleo Mazuri'}</td>
                </tr>
            `;
        });
        $('#marksBody').html(bodyHtml);
    };

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
