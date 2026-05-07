@include('includes.teacher_nav')

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

    body {
        background-color: #fcfcfc;
    }

    .bg-primary-custom {
        background: var(--header-gradient) !important;
        color: var(--primary-color) !important;
        border-bottom: 2px solid var(--primary-color) !important;
    }

    .text-primary-custom {
        color: var(--primary-color) !important;
    }

    .card {
        border-radius: 15px !important;
        border: 1px solid #eee;
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.05);
        background-color: var(--secondary-bg);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .assignment-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border-left: 5px solid var(--primary-color);
    }

    .assignment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.12);
        background-color: var(--accent-pale);
    }

    .attendance-table th {
        background-color: var(--accent-pale);
        color: var(--primary-color);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none !important;
    }

    .btn-primary-custom {
        background-color: var(--primary-color) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 30px !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary-custom:hover {
        background-color: #7a0000 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
        border-radius: 30px !important;
    }

    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: #ffffff;
    }

    .status-present { color: #28a745; font-weight: 700; }
    .status-absent { color: #dc3545; font-weight: 700; }

    /* Custom Radio Buttons */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .search-box input {
        border-radius: 30px !important;
        border: 1px solid #eee;
        padding-left: 45px;
        height: 50px;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.05);
    }

    .sticky-actions {
        background: rgba(255,255,255,0.98);
        border-bottom: 1px solid #eee;
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 10px 0;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 768px) {
        .attendance-table thead { display: none; }
        .attendance-table tr { 
            display: block; 
            margin-bottom: 15px; 
            border: 1px solid #eee; 
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(148,0,0,0.03);
        }
        .attendance-table td { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            text-align: right;
            border: none !important;
            padding: 10px 5px !important;
        }
        .attendance-table td::before {
            content: attr(data-label);
            font-weight: bold;
            text-align: left;
            flex: 1;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .btn-group-mobile {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-group-mobile .btn {
            width: 100%;
        }
        .sticky-actions {
            position: relative;
            top: 0;
        }
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

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="card bg-primary-custom mb-4">
        <div class="card-body py-4">
            <div class="d-flex align-items-center">
                <div class="mr-3 bg-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-person-check text-primary-custom" style="font-size: 2rem;"></i>
                </div>
                <div>
                    <h3 class="mb-1 font-weight-bold">Exam Attendance</h3>
                    <p class="mb-0 opacity-8">Track student participation for examinations</p>
                </div>
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-4 font-weight-bold">No Assignments Found</h4>
                <p class="text-muted">You are not currently assigned to collect attendance for any examinations.</p>
            </div>
        </div>
    @else
        <!-- Assignment Selection (Mobile Friendly Cards) -->
        <div id="assignmentSelection">
            <h5 class="mb-3 font-weight-bold"><i class="bi bi-list-task mr-2"></i> Your Assignments</h5>
            <div class="row">
                @foreach($assignments as $a)
                    <div class="col-md-6 col-lg-4">
                        <div class="card assignment-card" onclick="loadAttendanceList({{ $a->id }})">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-pill badge-danger py-2 px-3">{{ $a->exam->exam_name }}</span>
                                    <i class="bi bi-arrow-right-circle text-primary-custom" style="font-size: 1.2rem;"></i>
                                </div>
                                <h5 class="font-weight-bold mb-1">{{ $a->class->class_name }}</h5>
                                @if($a->subclass)
                                    <p class="text-primary-custom mb-2"><strong>Subclass:</strong> {{ $a->subclass->subclass_name }}</p>
                                @endif
                                <div class="text-muted small">
                                    <i class="bi bi-book mr-1"></i> {{ count($a->subject_ids) }} Subjects Assigned
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
                <i class="bi bi-arrow-left mr-2"></i> Back to Assignments
            </button>

            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h4 class="font-weight-bold mb-0" id="current_exam_name">Exam Name</h4>
                            <p class="text-muted mb-0" id="current_class_info">Class Info</p>
                        </div>
                        <div class="btn-group-mobile mt-2 mt-md-0">
                            <button class="btn btn-outline-primary mr-md-2" onclick="downloadTeacherTemplate()">
                                <i class="bi bi-file-pdf"></i> Template
                            </button>
                            <button class="btn btn-outline-success" onclick="downloadTeacherAttendance()">
                                <i class="bi bi-download"></i> Attendance
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Select Subject</label>
                        <select class="form-control rounded-pill" id="teacher_subject_selector" onchange="switchSubject()">
                            <!-- Subjects will be loaded here -->
                        </select>
                    </div>

                    <div class="sticky-actions border-bottom mb-3">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <div class="search-box mb-0">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0" style="border-radius: 0 !important;"><i class="bi bi-search"></i></span>
                                        </div>
                                        <input type="text" id="studentSearch" class="form-control border-left-0" placeholder="Search student name..." onkeyup="filterStudents()" style="border-radius: 0 !important;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 text-md-right">
                                <div class="btn-group w-100 w-md-auto">
                                    <button class="btn btn-success" onclick="markAll('Present')" style="border-radius: 0 !important; background-color: #28a745; border-color: #28a745;">
                                        <i class="bi bi-check-all"></i> Present
                                    </button>
                                    <button class="btn btn-danger" onclick="markAll('Absent')" style="border-radius: 0 !important; background-color: #dc3545; border-color: #dc3545;">
                                        <i class="bi bi-x-circle"></i> Absent
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="attendance_students_list">
                        <!-- Student list will be rendered here -->
                    </div>

                    <div class="text-right mt-4 pt-3 border-top">
                        <button class="btn btn-primary-custom btn-lg px-5 shadow rounded-pill" onclick="saveAttendance()">
                            <i class="bi bi-cloud-arrow-up mr-2"></i> Save Attendance Records
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentAssignment = null;
    let currentSubjectID = null;
    let assignmentDetails = [];

    function loadAttendanceList(id) {
        Swal.fire({
            title: 'Loading students...',
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
            Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load details', 'error');
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
                            <th style="width: 50px;">#</th>
                            <th>Student Name</th>
                            <th class="text-center">Exam Number</th>
                            <th class="text-center" style="width: 200px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="student_tbody">
        `;

        detail.students.forEach((s, index) => {
            html += `
                <tr class="student-row" data-name="${s.name.toLowerCase()}">
                    <td data-label="#">${index + 1}</td>
                    <td data-label="Name" class="font-weight-bold">${s.name}</td>
                    <td data-label="Exam Number" class="text-center small text-muted font-monospace">${s.examination_number || '-'}</td>
                    <td data-label="Status">
                        <select class="form-control form-control-sm attendance-select" 
                                style="border-radius: 0 !important; border: 1px solid #ddd;"
                                onchange="updateLocalStatus(${s.studentID}, this.value)">
                            <option value="Present" ${s.status === 'Present' ? 'selected' : ''}>Present</option>
                            <option value="Absent" ${s.status === 'Absent' ? 'selected' : ''}>Absent</option>
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
                const row = $(`.student-row[data-name="${s.name.toLowerCase()}"]`);
                if (row.is(':visible')) {
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
            title: 'Saving Records',
            text: 'Please wait...',
            didOpen: () => { Swal.showLoading(); }
        });

        $.post("{{ route('admin.exam_attendance.save') }}", {
            _token: "{{ csrf_token() }}",
            examID: currentAssignment.examID,
            subjectID: currentSubjectID,
            attendance: detail.students
        }, function(response) {
            Swal.fire('Success', 'Attendance records saved successfully!', 'success');
        }).fail(function(xhr) {
            Swal.fire('Error', 'Failed to save attendance', 'error');
        });
    }

    function backToAssignments() {
        $('#attendanceCollectionView').fadeOut(300, function() {
            $('#assignmentSelection').fadeIn(300);
        });
    }

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

    function downloadTeacherTemplate() {
        const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${currentAssignment.examID}&subjectID=${currentSubjectID}&classID=${currentAssignment.classID}&subclassID=${currentAssignment.subclassID || ''}`;
        performDownloadWithProgress(url, `Attendance_Template_${currentAssignment.examID}.pdf`);
    }

    function downloadTeacherAttendance() {
        const url = `{{ route('admin.exam_attendance.download_template') }}?examID=${currentAssignment.examID}&subjectID=${currentSubjectID}&classID=${currentAssignment.classID}&subclassID=${currentAssignment.subclassID || ''}&with_data=true`;
        performDownloadWithProgress(url, `Attendance_Record_${currentAssignment.examID}.pdf`);
    }
</script>

@include('includes.footer')
