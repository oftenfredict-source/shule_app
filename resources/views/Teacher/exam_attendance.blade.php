@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.exam_attendance').' - ShuleXpert')
@section('page_title', __('teacher.pages.exam_attendance'))
@section('page_breadcrumb', __('teacher.pages.exam_attendance_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #940000;
        --secondary-bg: #FFFFFF;
        --accent-pale: #FFF5F5;
        --header-gradient: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
        --text-dark: #2f2f2f;
        --text-light: #666666;
    }

    .sx-exam-att .bg-primary-custom {
        background: var(--header-gradient) !important;
        color: var(--primary-color) !important;
        border-bottom: 2px solid var(--primary-color) !important;
    }

    .sx-exam-att .text-primary-custom {
        color: var(--primary-color) !important;
    }

    .sx-exam-att .card {
        border-radius: 15px !important;
        border: 1px solid #eee;
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.05);
        background-color: var(--secondary-bg);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .sx-exam-att .assignment-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border-left: 5px solid var(--primary-color);
    }

    .sx-exam-att .assignment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.12);
        background-color: var(--accent-pale);
    }

    .sx-exam-att .attendance-table th {
        background-color: var(--accent-pale);
        color: var(--primary-color);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none !important;
    }

    .sx-exam-att .btn-primary-custom {
        background-color: var(--primary-color) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 30px !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .sx-exam-att .btn-primary-custom:hover {
        background-color: #7a0000 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
    }

    .sx-exam-att .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
        border-radius: 30px !important;
    }
    .sx-exam-att .enrollment-inactive-row {
        background-color: #eceff1 !important;
        opacity: 0.75;
        color: #607d8b;
    }
    .sx-exam-att .enrollment-inactive-row select {
        pointer-events: none;
        background-color: #cfd8dc !important;
        cursor: not-allowed !important;
    }

    .sx-exam-att .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: #ffffff;
    }

    .sx-exam-att .status-present { color: #28a745; font-weight: 700; }
    .sx-exam-att .status-absent { color: #dc3545; font-weight: 700; }

    /* Custom Radio Buttons */
    .sx-exam-att .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .sx-exam-att .search-box input {
        border-radius: 30px !important;
        border: 1px solid #eee;
        padding-left: 45px;
        height: 50px;
    }

    .sx-exam-att .search-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.05);
    }

    .sx-exam-att .sticky-actions {
        background: rgba(255,255,255,0.98);
        border-bottom: 1px solid #eee;
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 10px 0;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 768px) {
        .sx-exam-att .attendance-table thead { display: none; }
        .sx-exam-att .attendance-table tr {
            display: block;
            margin-bottom: 15px;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(148,0,0,0.03);
        }
        .sx-exam-att .attendance-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right;
            border: none !important;
            padding: 10px 5px !important;
        }
        .sx-exam-att .attendance-table td::before {
            content: attr(data-label);
            font-weight: bold;
            text-align: left;
            flex: 1;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .sx-exam-att .btn-group-mobile {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .sx-exam-att .btn-group-mobile .btn {
            width: 100%;
        }
        .sx-exam-att .sticky-actions {
            position: relative;
            top: 0;
        }
    }
    .sx-exam-att .animate-pulse { animation: sx-exam-att-pulse 1.5s infinite; }
    @keyframes sx-exam-att-pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="sx-exam-att">
<!-- DOWNLOAD PROGRESS MODAL -->
<div class="modal fade" id="downloadProgressModal" data-bs-backdrop="static" data-backdrop="static" tabindex="-1" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="bi bi-file-pdf fa-3x text-danger animate-pulse" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold;">{{ __('teacher.exam_att.gen_pdf') }}</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 0;">
                    <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%; background-color: #940000;"></div>
                </div>
                <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small">{{ __('teacher.exam_att.wait_pdf') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-2">
    <!-- Header -->
    <div class="card bg-primary-custom mb-4">
        <div class="card-body py-4">
            <div class="d-flex align-items-center">
                <div class="me-3 bg-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-person-check text-primary-custom" style="font-size: 2rem;"></i>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold">{{ __('teacher.exam_att.title') }}</h3>
                    <p class="mb-0 opacity-8">{{ __('teacher.exam_att.subtitle') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-4 fw-bold">{{ __('teacher.exam_att.no_assignments') }}</h4>
                <p class="text-muted">{{ __('teacher.exam_att.no_assignments_body') }}</p>
            </div>
        </div>
    @else
        <!-- Assignment Selection (Mobile Friendly Cards) -->
        <div id="assignmentSelection">
            <h5 class="mb-3 fw-bold"><i class="bi bi-list-task me-2"></i> {{ __('teacher.exam_att.your_assignments') }}</h5>
            <div class="row">
                @foreach($assignments as $a)
                    <div class="col-md-6 col-lg-4">
                        <div class="card assignment-card" onclick="loadAttendanceList({{ $a->id }})">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge rounded-pill text-bg-danger py-2 px-3">{{ $a->exam->exam_name }}</span>
                                    <i class="bi bi-arrow-right-circle text-primary-custom" style="font-size: 1.2rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-1">{{ $a->class->class_name }}</h5>
                                @if($a->subclass)
                                    <p class="text-primary-custom mb-2"><strong>{{ __('teacher.exam_att.subclass') }}</strong> {{ $a->subclass->subclass_name }}</p>
                                @endif
                                <div class="text-muted small">
                                    <i class="bi bi-book me-1"></i> {{ __('teacher.exam_att.subjects_assigned', ['count' => count($a->subject_ids)]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Attendance Collection View (Hidden by default) -->
        <div id="attendanceCollectionView" style="display: none;">
            <button class="btn btn-outline-secondary mb-4 rounded-pill px-4" onclick="backToAssignments()">
                <i class="bi bi-arrow-left me-2"></i> {{ __('teacher.exam_att.back_assignments') }}
            </button>

            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0" id="current_exam_name">{{ __('teacher.exam_att.exam_name') }}</h4>
                            <p class="text-muted mb-0" id="current_class_info">{{ __('teacher.exam_att.class_info') }}</p>
                        </div>
                        <div class="btn-group-mobile mt-2 mt-md-0">
                            <button class="btn btn-outline-primary me-md-2" onclick="downloadTeacherTemplate()">
                                <i class="bi bi-file-pdf"></i> {{ __('teacher.exam_att.template') }}
                            </button>
                            <button class="btn btn-outline-success" onclick="downloadTeacherAttendance()">
                                <i class="bi bi-download"></i> {{ __('teacher.exam_att.attendance') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <label class="fw-bold">{{ __('teacher.exam_att.select_subject') }}</label>
                        <select class="form-control rounded-pill" id="teacher_subject_selector" onchange="switchSubject()">
                            <!-- Subjects will be loaded here -->
                        </select>
                    </div>

                    <div class="sticky-actions border-bottom mb-3">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <div class="search-box mb-0">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0" style="border-radius: 0 !important;"><i class="bi bi-search"></i></span>
                                        <input type="text" id="studentSearch" class="form-control border-start-0" placeholder="{{ __('teacher.exam_att.search_ph') }}" onkeyup="filterStudents()" style="border-radius: 0 !important;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 text-md-end">
                                <div class="btn-group w-100 w-md-auto">
                                    <button class="btn btn-success" onclick="markAll('Present')" style="border-radius: 0 !important; background-color: #28a745; border-color: #28a745;">
                                        <i class="bi bi-check-all"></i> {{ __('teacher.exam_att.present') }}
                                    </button>
                                    <button class="btn btn-danger" onclick="markAll('Absent')" style="border-radius: 0 !important; background-color: #dc3545; border-color: #dc3545;">
                                        <i class="bi bi-x-circle"></i> {{ __('teacher.exam_att.absent') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="attendance_students_list">
                        <!-- Student list will be rendered here -->
                    </div>

                    <div class="text-end mt-4 pt-3 border-top">
                        <button class="btn btn-primary-custom btn-lg px-5 shadow rounded-pill" onclick="saveAttendance()">
                            <i class="bi bi-cloud-arrow-up me-2"></i> {{ __('teacher.exam_att.save_records') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
    let currentAssignment = null;
    let currentSubjectID = null;
    let assignmentDetails = [];
    const i18n = @json(__('teacher.exam_att'));
    i18n.active = @json(__('teacher.active'));
    i18n.inactive = @json(__('teacher.inactive'));
    i18n.status = @json(__('teacher.status'));
    i18n.student = @json(__('teacher.student'));

    function loadAttendanceList(id) {
        Swal.fire({
            title: i18n.loading_students,
            didOpen: () => { Swal.showLoading(); }
        });

        $.get("{{ url('teacher/exam-attendance/details') }}/" + id, function(response) {
            Swal.close();
            if (response.success) {
                currentAssignment = response.assignment;
                assignmentDetails = response.details;

                $('#current_exam_name').text(currentAssignment.exam.exam_name);
                $('#current_class_info').text(`${currentAssignment.class.class_name} ${currentAssignment.subclass ? ' - ' + currentAssignment.subclass.subclass_name : ''}`);

                // Populate subjects
                let subHtml = '';
                assignmentDetails.forEach(d => {
                    subHtml += `<option value="${d.subjectID}">${d.subject_name}</option>`;
                });
                $('#teacher_subject_selector').html(subHtml);

                if (assignmentDetails.length > 0) {
                    currentSubjectID = assignmentDetails[0].subjectID;
                    renderStudentList();
                }

                $('#assignmentSelection').fadeOut(300, function() {
                    $('#attendanceCollectionView').fadeIn(300);
                });
            }
        }).fail(function(xhr) {
            Swal.fire(i18n.error, xhr.responseJSON?.error || i18n.fail_load, 'error');
        });
    }

    function switchSubject() {
        currentSubjectID = $('#teacher_subject_selector').val();
        renderStudentList();
    }

    function renderStudentList() {
        const detail = assignmentDetails.find(d => d.subjectID == currentSubjectID);
        if (!detail) return;

        let html = `
            <div class="table-responsive">
                <table class="table attendance-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">${i18n.col_num}</th>
                            <th>${i18n.student_name}</th>
                            <th class="text-center">${i18n.exam_number}</th>
                            <th class="text-center" style="width: 200px;">${i18n.status}</th>
                        </tr>
                    </thead>
                    <tbody id="student_tbody">
        `;

        detail.students.forEach((s, index) => {
            const canEnter = s.can_enter_result !== false && (s.enrollment_status || 'Active') === 'Active';
            const rowClass = canEnter ? '' : 'enrollment-inactive-row';
            const enrBadge = canEnter
                ? `<span class="badge text-bg-success ms-1">${i18n.active}</span>`
                : `<span class="badge text-bg-secondary ms-1">${s.enrollment_status || i18n.inactive}</span>`;
            const disabledAttr = canEnter ? '' : 'disabled';
            html += `
                <tr class="student-row ${rowClass}" data-name="${s.name.toLowerCase()}" data-can-enter="${canEnter ? 1 : 0}">
                    <td data-label="${i18n.col_num}">${index + 1}</td>
                    <td data-label="${i18n.name}" class="fw-bold">${s.name}${enrBadge}</td>
                    <td data-label="${i18n.exam_number}" class="text-center small text-muted font-monospace">${s.examination_number || '-'}</td>
                    <td data-label="${i18n.status}">
                        <select class="form-control form-control-sm attendance-select"
                                style="border-radius: 0 !important; border: 1px solid #ddd;"
                                onchange="updateLocalStatus(${s.studentID}, this.value)" ${disabledAttr}>
                            <option value="Present" ${s.status === 'Present' ? 'selected' : ''}>${i18n.present}</option>
                            <option value="Absent" ${s.status === 'Absent' ? 'selected' : ''}>${i18n.absent}</option>
                        </select>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;
        $('#attendance_students_list').html(html);
        filterStudents(); // Apply current search if any
    }

    function updateLocalStatus(studentID, status) {
        const detail = assignmentDetails.find(d => d.subjectID == currentSubjectID);
        if (detail) {
            const student = detail.students.find(s => s.studentID == studentID);
            if (student) student.status = status;
        }
    }

    function filterStudents() {
        const query = $('#studentSearch').val().toLowerCase();
        $('.student-row').each(function() {
            const name = $(this).data('name');
            if (name.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function markAll(status) {
        const detail = assignmentDetails.find(d => d.subjectID == currentSubjectID);
        if (detail) {
            detail.students.forEach(s => {
                if (s.can_enter_result === false) return;
                const row = $(`.student-row[data-name="${s.name.toLowerCase()}"]`);
                if (row.is(':visible') && row.data('can-enter') != 0) {
                    s.status = status;
                    row.find('.attendance-select').val(status);
                }
            });
        }
    }

    function saveAttendance() {
        const detail = assignmentDetails.find(d => d.subjectID == currentSubjectID);
        if (!detail) return;

        Swal.fire({
            title: i18n.saving,
            text: i18n.please_wait,
            didOpen: () => { Swal.showLoading(); }
        });

        const payloadStudents = detail.students.filter(s => s.can_enter_result !== false);
        $.post("{{ route('admin.exam_attendance.save') }}", {
            _token: "{{ csrf_token() }}",
            examID: currentAssignment.examID,
            subjectID: currentSubjectID,
            attendance: payloadStudents
        }, function(response) {
            Swal.fire(i18n.success, i18n.saved, 'success');
        }).fail(function(xhr) {
            Swal.fire(i18n.error, i18n.fail_save, 'error');
        });
    }

    function backToAssignments() {
        $('#attendanceCollectionView').fadeOut(300, function() {
            $('#assignmentSelection').fadeIn(300);
        });
    }

    function performDownloadWithProgress(url, defaultFilename) {
        const $modal = $('#downloadProgressModal');
        if (typeof $modal.modal === 'function') {
            $modal.modal('show');
        } else if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance($modal[0]).show();
        }
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
                    if (typeof $modal.modal === 'function') {
                        $modal.modal('hide');
                    } else if (window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
                    }
                }, 500);
            })
            .catch(error => {
                clearInterval(interval);
                if (typeof $modal.modal === 'function') {
                    $modal.modal('hide');
                } else if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
                }
                Swal.fire(i18n.error, i18n.fail_pdf.replace(':msg', error.message), 'error');
            });
    }

    function downloadTeacherTemplate() {
        const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${currentAssignment.examID}&subjectID=${currentSubjectID}&classID=${currentAssignment.classID}&subclassID=${currentAssignment.subclassID || ''}`;
        performDownloadWithProgress(url, `Attendance_Template_${currentAssignment.examID}.pdf`);
    }

    function downloadTeacherAttendance() {
        const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${currentAssignment.examID}&subjectID=${currentSubjectID}&classID=${currentAssignment.classID}&subclassID=${currentAssignment.subclassID || ''}&with_data=true`;
        performDownloadWithProgress(url, `Attendance_Record_${currentAssignment.examID}.pdf`);
    }
</script>
@endpush
