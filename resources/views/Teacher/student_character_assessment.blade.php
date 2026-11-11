@include('includes.teacher_nav')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper"
    style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif; background-color: #FFFFFF;">
    <div class="content mt-3">
        <div class="animated fadeIn">

            <!-- Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm"
                        style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                        <div class="card-body py-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">
                                        STUDENT CHARACTER ASSESSMENT</h4>
                                    <p class="text-muted mb-0 small" style="font-weight: 600;">Record behavior and
                                        personality development for your assigned classes</p>
                                </div>
                                <div class="text-right">
                                    @php $periodStatus = $periodStatus ?? 'active'; @endphp
                                    @if($periodStatus === 'active')
                                        <span class="badge badge-danger p-2 px-3"
                                            style="border-radius: 0; font-weight: 700; background: #940000;">
                                            <i class="fa fa-calendar-check-o"></i> ACTIVE PERIOD
                                        </span>
                                    @elseif($periodStatus === 'upcoming')
                                        <span class="badge p-2 px-3"
                                            style="border-radius: 0; font-weight: 700; background: #856404; color: #fff;">
                                            <i class="fa fa-clock-o"></i> UPCOMING PERIOD
                                        </span>
                                    @elseif($periodStatus === 'ended')
                                        <span class="badge p-2 px-3"
                                            style="border-radius: 0; font-weight: 700; background: #6c757d; color: #fff;">
                                            <i class="fa fa-calendar-times-o"></i> PERIOD ENDED
                                        </span>
                                    @else
                                        <span class="badge p-2 px-3"
                                            style="border-radius: 0; font-weight: 700; background: #6c757d; color: #fff;">
                                            <i class="fa fa-info-circle"></i> NOT ASSIGNED
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($assignments->isEmpty())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert mb-0" style="border-radius:0;border:1px solid #ffe0a3;background:#fff8f0;color:#856404;">
                        <strong><i class="fa fa-info-circle"></i> No assignment yet.</strong>
                        You are not assigned on any character assessment timetable.
                        Ask the admin to open <em>Character Assessment → Timetable</em> and assign your name to a class.
                    </div>
                </div>
            </div>
            @endif

            <!-- Class Selection & Search -->
            <div class="row mb-4 sticky-top pt-2 pb-3"
                style="background: rgba(255,255,255,0.98); z-index: 1020; top: -1px; border-bottom: 2px solid #f0f0f0; {{ $assignments->isEmpty() ? 'opacity:0.55;pointer-events:none;' : '' }}">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted" style="letter-spacing: 1px;">SELECT MAIN
                        CLASS</label>
                    <select id="mainClassSelector" class="form-control form-control-lg border-0 bg-light"
                        style="border-radius: 0; font-weight: 700; color: #940000; height: 50px;">
                        @php
                            $uniqueClasses = $assignments->unique('classID');
                        @endphp
                        @forelse($uniqueClasses as $asgn)
                            <option value="{{ $asgn->classID }}">{{ optional($asgn->class)->class_name ?? ('Class #'.$asgn->classID) }}</option>
                        @empty
                            <option value="">No assigned class</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted" style="letter-spacing: 1px;">SELECT
                        SUBCLASS/GROUP</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-0 bg-white"><i class="fa fa-university text-danger"
                                    style="color:#940000 !important;"></i></span>
                        </div>
                        <select id="subclassSelector" class="form-control form-control-lg border-0 bg-light"
                            style="border-radius: 0; font-weight: 700; color: #940000; height: 50px;">
                            <!-- Populated via JS -->
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-muted" style="letter-spacing: 1px;">FIND STUDENT</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-0 bg-white"><i
                                    class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="studentSearch" class="form-control form-control-lg border-0 bg-light"
                            placeholder="Search by name..." style="border-radius: 0; height: 50px;">
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button class="btn btn-lg btn-block text-white" id="saveAllBtn"
                        style="border-radius: 0; background: #940000; font-weight: 700; border: none; height: 50px; display: none;">
                        <i class="fa fa-save"></i> SAVE
                    </button>
                    <div id="filterLoading" class="ml-2" style="display: none;">
                        <i class="fa fa-circle-o-notch fa-spin fa-2x" style="color: #940000;"></i>
                    </div>
                </div>
            </div>

            <!-- Mobile Floating Save Button -->
            <div id="mobileSaveFab" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: none;">
                <button class="btn btn-danger btn-lg rounded-circle shadow-lg"
                    style="width: 70px; height: 70px; background: #940000; border: none;" onclick="saveAssessments()">
                    <i class="fa fa-save fa-lg text-white"></i>
                </button>
            </div>

            <!-- Student List Section -->
            <div class="row">
                <div class="col-md-12">
                    <div id="studentContainer" class="row">
                        <!-- Students load here -->
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- History View Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0;">
            <div class="modal-header border-0 text-white p-4" style="background: #940000; border-radius: 0;">
                <h5 class="modal-title font-weight-bold" id="historyTitle">Student Record</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-4">
                    <img id="historyStudentImg" src="" class="rounded-circle mr-3"
                        style="width: 70px; height: 70px; object-fit: cover; border: 3px solid #FFF5F5;">
                    <div>
                        <h4 id="historyStudentName" class="font-weight-bold mb-0" style="color: #2f2f2f;"></h4>
                        <span id="historyStudentAdm" class="badge badge-light p-1 px-2 font-weight-bold"
                            style="border-radius:0;"></span>
                    </div>
                </div>
                <div id="historyContent" class="mt-4">
                    <!-- History rows -->
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light btn-block font-weight-bold p-3" data-dismiss="modal"
                    style="border-radius: 0;">CLOSE</button>
            </div>
        </div>
    </div>
</div>

<style>
    .student-card {
        transition: all 0.2s ease;
        border-radius: 0;
        border: 1px solid #eee;
        border-left: 5px solid #940000 !important;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
    }

    .assessment-dropdown {
        background-color: #ffffff;
        border-top: 2px solid #fecaca;
        display: none;
        padding-top: 30px !important;
    }

    .form-control:focus {
        border-color: #940000;
        box-shadow: none !important;
        background-color: #fff !important;
    }

    .badge-success {
        background-color: #28a745;
        border-radius: 0;
    }

    .btn-outline-danger {
        color: #940000;
        border-color: #940000;
        border-radius: 0;
    }

    .btn-outline-danger:hover {
        background-color: #940000;
        color: #fff;
    }

    .cat-label {
        font-size: 0.9rem;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .student-cat-input {
        background-color: #fcfcfc !important;
        border: 1px solid #ddd !important;
        height: 50px;
        padding: 10px 15px;
    }

    textarea.student-cat-input {
        height: auto !important;
        min-height: 100px;
    }

    .history-item {
        padding: 20px;
        border-left: 3px solid #940000;
        border-bottom: 1px solid #f5f5f5;
        background: #fff;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 5px;
        }

        .student-item-col {
            padding: 0;
        }

        .card-body {
            padding: 20px !important;
        }

        .btn-sm {
            height: 45px !important;
            font-size: 0.85rem !important;
        }

        .student-card h6 {
            font-size: 1.1rem;
        }
    }
</style>

<script>
    let currentStudents = [];
    let pendingChanges = {};
    const GLOBAL_CHOICES = @json($globalChoices);
    const GLOBAL_CATEGORIES = @json($categories);
    const ALL_ASSIGNMENTS = @json($assignments);
    const ALL_SUBCLASSES = @json($subclasses);

    $(document).ready(function () {
        if (!ALL_ASSIGNMENTS || !ALL_ASSIGNMENTS.length) {
            $('#studentList').html('<div class="col-12 text-center py-5 text-muted">No assigned classes to assess yet.</div>');
            return;
        }

        // Initial subclass population and loading
        updateSubclassOptions();
        loadStudents();

        // Class change
        $('#mainClassSelector').on('change', function () {
            updateSubclassOptions();
            loadStudents(); // Auto load first option
            pendingChanges = {};
            $('#saveAllBtn').fadeOut();
            $('#mobileSaveFab').fadeOut();
        });

        // Subclass change
        $('#subclassSelector').on('change', function () {
            loadStudents();
            pendingChanges = {};
            $('#saveAllBtn').fadeOut();
            $('#mobileSaveFab').fadeOut();
        });

        // Search
        $('#studentSearch').on('keyup', function () {
            const query = $(this).val().toLowerCase();
            $('.student-item-col').each(function () {
                const name = $(this).data('name').toLowerCase();
                const adm = $(this).data('adm').toString();
                if (name.includes(query) || adm.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Save All
        $('#saveAllBtn').on('click', function () {
            saveAssessments(); // Saves all pending
        });
    });

    function updateSubclassOptions() {
        const classID = $('#mainClassSelector').val();
        if (!classID) {
            $('#subclassSelector').html('<option value="">No Groups Assigned</option>');
            return;
        }
        const assignmentsForClass = ALL_ASSIGNMENTS.filter(a => a.classID == classID);

        if (assignmentsForClass.length === 0) {
            $('#subclassSelector').html('<option value="">No Groups Assigned</option>');
            return;
        }

        // The first assignment will serve as the base for the "All Groups" option
        const baseAssignment = assignmentsForClass[0];
        const hasAllAssignment = assignmentsForClass.find(a => a.subclassID === null);

        let html = `<option value="all" 
                            data-timetable-id="${baseAssignment.timetableID}" 
                            data-categories='${JSON.stringify(baseAssignment.selected_categories || [])}'>
                        All Groups, View All
                    </option>`;

        if (hasAllAssignment) {
            const relatedSubclasses = ALL_SUBCLASSES.filter(s => s.classID == classID);
            relatedSubclasses.forEach(s => {
                html += `<option value="${s.subclassID}" data-timetable-id="${hasAllAssignment.timetableID}" data-categories='${JSON.stringify(hasAllAssignment.selected_categories || [])}'>${s.subclass_name}</option>`;
            });
        } else {
            assignmentsForClass.forEach(a => {
                const subName = a.subclass ? a.subclass.subclass_name : 'Group-X';
                html += `<option value="${a.subclassID}" 
                                 data-timetable-id="${a.timetableID}" 
                                 data-categories='${JSON.stringify(a.selected_categories || [])}'>
                            ${subName}
                         </option>`;
            });
        }
        $('#subclassSelector').html(html);
    }

    function loadStudents() {
        const classID = $('#mainClassSelector').val();
        const subOption = $('#subclassSelector option:selected');
        const subclassID = subOption.val();
        const timetableID = subOption.attr('data-timetable-id');

        if (!classID) return;

        $('#filterLoading').show();
        $('#studentContainer').css('opacity', '0.5');

        $.ajax({
            url: "{{ route('teacher.get_assessment_students') }}",
            type: 'GET',
            data: { subclassID: subclassID, classID: classID, timetableID: timetableID },
            success: function (response) {
                $('#filterLoading').hide();
                $('#studentContainer').css('opacity', '1');
                if (response.success) {
                    currentStudents = response.students;
                    renderStudents(response.students);
                }
            },
            error: function (xhr) {
                $('#filterLoading').hide();
                $('#studentContainer').css('opacity', '1');
                currentStudents = [];
                renderStudents([]);
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Failed to load students';
                Swal.fire(xhr.status === 422 ? 'No Active Enrollment' : 'Error', msg, xhr.status === 422 ? 'info' : 'error');
            }
        });
    }

    function renderStudents(students) {
        let html = '';
        if (students.length === 0) {
            html = '<div class="col-md-12 text-center py-5"><p class="text-muted font-weight-bold">No students found for this selection.</p><p class="text-muted small">Roster uses Active enrollments for the academic year selected in the header.</p></div>';
        } else {
            students.forEach(student => {
                const fullName = `${student.first_name} ${student.middle_name ? student.middle_name + ' ' : ''}${student.last_name}`;
                const photo = student.photo ? `{{ asset('userImages/') }}/${student.photo}` : (student.gender === 'Female' ? `{{ asset('images/female.png') }}` : `{{ asset('images/male.png') }}`);
                
                // Laravel snake_cases relationship names in JSON
                const assessments = student.character_assessments || student.characterAssessments || [];
                const hasAssessments = assessments.length > 0;
                const canAssess = student.can_assess !== false && (student.enrollment_status || 'Active') === 'Active';
                const enrStatus = student.enrollment_status || 'Active';
                const enrBadge = canAssess
                    ? '<span class="badge badge-success px-2 py-1 ml-2" style="font-size: 10px; border-radius:0;">Active</span>'
                    : `<span class="badge badge-secondary px-2 py-1 ml-2" style="font-size: 10px; border-radius:0;">${enrStatus}</span>`;
                
                const btnLabel = hasAssessments ? 'EDIT' : 'ASSESS';
                const statusBadge = hasAssessments ? '<span class="badge badge-success px-2 py-1 ml-2" style="font-size: 10px; background: #28a745; border-radius:0; letter-spacing:1px;">READY</span>' : '';
                const cardStyle = canAssess ? '' : 'opacity:0.55; background:#f5f5f5;';
                const assessBtnAttrs = canAssess
                    ? `onclick="toggleAssessDiv(${student.studentID})"`
                    : 'disabled title="Not editable, enrollment is not Active for the selected academic year"';

                html += `
                    <div class="col-12 student-item-col" data-name="${fullName}" data-adm="${student.admission_number}">
                        <div class="card student-card border-0 shadow-none" style="${cardStyle}">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8 col-12 d-flex align-items-center mb-3 mb-md-0">
                                        <div class="mr-3">
                                            <img src="${photo}" class="rounded-circle" style="width: 65px; height: 65px; object-fit: cover; border: 2px solid #940000;">
                                        </div>
                                        <div class="overflow-hidden">
                                            <h5 class="font-weight-bold mb-1" style="color: #2f2f2f;">${fullName} ${enrBadge} ${statusBadge}</h5>
                                            <span class="badge badge-light text-muted small px-2 font-weight-bold" style="border-radius:0;">ADMISSION: ${student.admission_number}</span>
                                            ${canAssess ? '' : '<div class="small text-muted mt-1">Assessment locked, enrollment not Active</div>'}
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="row no-gutters">
                                            <div class="col-6 pr-1">
                                                <button class="btn btn-sm btn-block btn-outline-danger font-weight-bold py-3" style="border-radius:0;" ${assessBtnAttrs}>
                                                    <i class="fa fa-${hasAssessments ? 'edit' : 'pencil'}"></i> ${btnLabel}
                                                </button>
                                            </div>
                                            <div class="col-6 pl-1">
                                                <button class="btn btn-sm btn-block btn-light font-weight-bold py-3 text-muted" style="border-radius:0;" onclick="viewHistory(${student.studentID}, '${fullName}', '${student.admission_number}', '${photo}')">
                                                    <i class="fa fa-history"></i> HISTORY
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Assessment Div (Expanded) -->
                            <div id="assess_div_${student.studentID}" class="assessment-dropdown p-4 p-md-5">
                                ${canAssess ? renderAssessmentForm(student.studentID) : '<p class="text-muted mb-0">This student cannot be assessed because their enrollment is not Active for the selected academic year.</p>'}
                                ${canAssess ? `<div class="text-right mt-4 pt-3 border-top">
                                    <button class="btn btn-danger px-5 py-2 font-weight-bold" style="border-radius:0; background:#940000;" onclick="saveSingleStudent(${student.studentID})">
                                        <i class="fa fa-save"></i> SAVE THIS STUDENT
                                    </button>
                                </div>` : ''}

                            </div>
                        </div>
                    </div>
                `;
            });
        }
        $('#studentContainer').html(html);
    }

    function renderAssessmentForm(studentID) {
        const student = currentStudents.find(s => s.studentID == studentID);
        const existing = student.character_assessments || student.characterAssessments || [];
        
        const selectedOption = $('#subclassSelector option:selected');
        const selectedCategoriesStr = selectedOption.data('categories');
        let selectedCategories = [];
        try {
            selectedCategories = typeof selectedCategoriesStr === 'string' ? JSON.parse(selectedCategoriesStr) : selectedCategoriesStr;
        } catch (e) { selectedCategories = []; }

        if (selectedCategories.length === 0) {
            return '<p class="small text-danger font-weight-bold mb-0">No categories assigned to this class configuration.</p>';
        }

        selectedCategories.sort((a, b) => (a.number || 0) - (b.number || 0));

        let html = '<div class="assessment-form">';
        selectedCategories.forEach(config => {
            const targetID = typeof config === 'object' ? config.categoryID : config;
            const seqNum = typeof config === 'object' ? config.number : '';
            const cat = GLOBAL_CATEGORIES.find(c => c.categoryID == targetID);
            if (!cat) return;

            // Find existing record for this category
            const record = existing.find(r => r.categoryID == targetID);
            const oldChoice = record ? record.choiceID : '';
            const oldText = record ? record.description : '';

            html += `<div class="form-group mb-4">
                        <label class="cat-label d-block mb-2">
                           ${seqNum ? `<span class="badge badge-danger mr-2" style="background:#940000; border-radius:50%; width:20px; height:20px; display:inline-flex; align-items:center; justify-content:center; font-size:10px;">${seqNum}</span>` : ''}
                           ${cat.name}
                        </label>`;
            
            if (cat.type === 'choice') {
                html += `<select class="form-control border-0 shadow-sm student-cat-input" 
                                 data-student="${studentID}" data-cat="${cat.categoryID}" data-type="choice"
                                 onchange="trackChange(${studentID}, ${cat.categoryID}, 'choiceID', this.value)"
                                 style="border-radius: 0; font-weight: 600; font-size: 0.9rem; background-color: #fff;">
                            <option value="">-- Rate Student --</option>
                            ${GLOBAL_CHOICES.map(c => `<option value="${c.choiceID}" ${c.choiceID == oldChoice ? 'selected' : ''}>${c.choice_name} (${c.weight})</option>`).join('')}
                         </select>`;
            } else {
                html += `<textarea class="form-control border-0 shadow-sm student-cat-input" 
                                   data-student="${studentID}" data-cat="${cat.categoryID}" data-type="text"
                                   placeholder="Type description here..."
                                   onkeyup="trackChange(${studentID}, ${cat.categoryID}, 'description', this.value)"
                                   style="border-radius: 0; font-size: 0.9rem; min-height: 80px; background-color: #fff;">${oldText || ''}</textarea>`;
            }
            html += `</div>`;
        });
        html += '</div>';
        return html;
    }


    function toggleAssessDiv(studentID) {
        $(`#assess_div_${studentID}`).slideToggle(300);
    }

    function trackChange(studentID, categoryID, field, value) {
        if (!pendingChanges[studentID]) pendingChanges[studentID] = {};
        if (!pendingChanges[studentID][categoryID]) pendingChanges[studentID][categoryID] = {};

        pendingChanges[studentID][categoryID][field] = value;

        // Show save buttons based on device
        $('#saveAllBtn').fadeIn();
        if ($(window).width() <= 768) {
            $('#mobileSaveFab').fadeIn();
        }
    }

    function saveSingleStudent(studentID) {
        saveAssessments(studentID);
    }

    function saveAssessments(targetStudentID = null) {
        const selectedOption = $('#subclassSelector option:selected');
        const timetableID = selectedOption.data('timetable-id');
        const assessmentData = [];

        if (targetStudentID) {
            // Save only this student
            const targetStudent = currentStudents.find(s => s.studentID == targetStudentID);
            if (targetStudent && targetStudent.can_assess === false) {
                Swal.fire('Locked', 'Only Active enrollments for the selected academic year can be assessed.', 'info');
                return;
            }
            if (!pendingChanges[targetStudentID]) {
                Swal.fire('No Changes', 'Please make some ratings before saving.', 'info');
                return;
            }
            const categories = [];
            for (const categoryID in pendingChanges[targetStudentID]) {
                categories.push({ categoryID: categoryID, ...pendingChanges[targetStudentID][categoryID] });
            }
            assessmentData.push({ studentID: targetStudentID, timetableID: timetableID, categories: categories });
        } else {
            // Save ALL pending changes
            if (Object.keys(pendingChanges).length === 0) return;
            for (const studentID in pendingChanges) {
                const categories = [];
                for (const categoryID in pendingChanges[studentID]) {
                    categories.push({ categoryID: categoryID, ...pendingChanges[studentID][categoryID] });
                }
                assessmentData.push({ studentID: studentID, timetableID: timetableID, categories: categories });
            }
        }

        const saveBtn = targetStudentID ? $(`#assess_div_${targetStudentID} button`) : $('#saveAllBtn');
        const originalHtml = saveBtn.html();

        saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> SAVING...');
        $('#mobileSaveFab button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('teacher.store_student_assessment') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", assessments: assessmentData },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: 'Recorded!', text: 'Assessment saved successfully.',
                        confirmButtonColor: '#940000', timer: 1500
                    });

                    if (targetStudentID) {
                        delete pendingChanges[targetStudentID];
                    } else {
                        pendingChanges = {};
                    }

                    if (Object.keys(pendingChanges).length === 0) {
                        $('#saveAllBtn').hide();
                        $('#mobileSaveFab').hide();
                    }

                    saveBtn.prop('disabled', false).html(originalHtml);
                    $('#mobileSaveFab button').prop('disabled', false).html('<i class="fa fa-save fa-lg text-white"></i>');

                    // Reload list to show updated status
                    loadStudents();

                    if (!targetStudentID && $(window).width() <= 768) {
                        $('html, body').animate({ scrollTop: 0 }, 500);
                    }
                }
            },
            error: function (xhr) {
                saveBtn.prop('disabled', false).html(originalHtml);
                $('#mobileSaveFab button').prop('disabled', false).html('<i class="fa fa-save fa-lg text-white"></i>');
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to save assessments.';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    function viewHistory(studentID, name, adm, photo) {
        const selectedOption = $('#subclassSelector option:selected');
        const timetableID = selectedOption.data('timetable-id');

        $('#historyStudentImg').attr('src', photo);
        $('#historyStudentName').text(name);
        $('#historyStudentAdm').text(`ADM: ${adm}`);
        $('#historyContent').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x" style="color:#940000;"></i></div>');
        $('#historyModal').modal('show');

        $.ajax({
            url: "{{ route('teacher.get_student_assessment_history') }}",
            type: 'GET',
            data: { studentID: studentID, timetableID: timetableID },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    if (response.history.length === 0) {
                        html = `
                            <div class="text-center py-5" style="background: #fafafa; border: 2px dashed #eee;">
                                <i class="fa fa-folder-open-o fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted font-weight-bold">No character assessment recorded for this student.</h5>
                                <p class="small text-muted mb-0">Please use the Assess button to record behavior scores.</p>
                            </div>
                        `;
                    } else {
                        response.history.forEach(item => {
                            let resultStr = item.choice ? `<span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">${item.choice.choice_name}</span>` : '';
                            if (item.description) {
                                resultStr += `<p class="mt-2 mb-0 text-muted" style="line-height: 1.5;">${item.description}</p>`;
                            }

                            html += `
                                <div class="history-item">
                                    <h6 class="font-weight-bold mb-2 text-uppercase" style="color:#940000; font-size: 0.8rem; letter-spacing: 0.5px;">${item.category.name}</h6>
                                    <div>${resultStr}</div>
                                </div>
                            `;
                        });
                    }
                    $('#historyContent').html(html);
                }
            },
            error: function () {
                $('#historyContent').html('<p class="text-danger text-center">Failed to load history.</p>');
            }
        });
    }
</script>