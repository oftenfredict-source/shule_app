@include('includes.teacher_nav')

<div class="content-wrapper" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif; background-color: #FFFFFF;">
    <div class="content mt-3">
        <div class="animated fadeIn">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">TEACHER LOGBOOK</h4>
                                    <p class="text-muted mb-0 small" style="font-weight: 600;">Track your teaching progress and content coverage</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn shadow-sm text-white" id="addEntryBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 20px; display: none;" onclick="openAddEntryModal()">
                                        <i class="fa fa-plus"></i> ADD ENTRY
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Selection -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; background-color: #FFFFFF;">
                        <div class="card-header border-0 p-4" style="background: #fafafa;">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Select Subject</label>
                                    <select id="subjectSelect" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;">
                                        <option value="">-- Choose Subject --</option>
                                        @foreach($teachingSubjects as $subject)
                                            <option value="{{ $subject->class_subjectID }}">
                                                {{ $subject->subject->subject_name }} - {{ $subject->subclass->class->class_name }} {{ $subject->subclass->subclass_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-block text-white" id="loadLogbookBtn" style="background:#2f2f2f; border-radius:0; height:calc(1.5em + .75rem + 2px);">
                                        <i class="fa fa-search"></i> VIEW LOGBOOK
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logbook Content -->
            <div id="logbookTableContainer" style="display: none;">
                <!-- Header Info -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="p-3 shadow-sm border" style="background-color: #FFF5F5; border-radius: 0;">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">SUBJECT</small>
                                    <span id="headerSubject" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">CLASS</small>
                                    <span id="headerClass" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">YEAR</small>
                                    <span id="headerYear" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block font-weight-bold">TEACHER</small>
                                    <span id="headerTeacher" class="font-weight-bold" style="color: #940000;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 0;">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0" id="logbookTable" style="border-color: #eee;">
                                        <thead style="background-color: #940000; color: white;">
                                            <tr class="text-center small">
                                                <th style="width: 50px;">SN</th>
                                                <th>MAIN TOPIC</th>
                                                <th>SUB TOPIC</th>
                                                <th>CONTENT COVERED</th>
                                                <th>DATE STARTED</th>
                                                <th>DATE ENDED</th>
                                                <th>TEACHER'S COMMENT</th>
                                                <th>H.O.D COMMENT</th>
                                                <th>H/M COMMENT</th>
                                            </tr>
                                        </thead>
                                        <tbody id="logbookBody">
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Placeholder -->
            <div class="py-5 text-center" id="welcomePlaceholder">
                <div class="p-4 mb-4 rounded-circle mx-auto" style="background-color: #FFF5F5; width: fit-content;">
                    <i class="fa fa-book fa-4x text-muted" style="color: #940000 !important;"></i>
                </div>
                <h3 class="font-weight-bold mb-3" style="color: #2f2f2f;">Your Teaching Logbook</h3>
                <p class="text-muted mb-4 px-3" style="max-width: 600px; margin: 0 auto;">
                    Select a subject to view and update your teaching records. 
                    Keep track of topics covered and dates of instruction.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Add Entry Modal -->
<div class="modal fade" id="entryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">ADD LOGBOOK ENTRY</h5>
                <p class="text-muted small mb-0 font-weight-bold">Record content covered for the selected subject.</p>
            </div>
            <form id="entryForm">
                @csrf
                <input type="hidden" name="class_subjectID" id="formClassSubjectID">
                <div class="modal-body p-4 custom-scrollbar" style="max-height: 450px; overflow-y: auto;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">Main Topic</label>
                            <input type="text" name="main_topic" class="form-control" required style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">Sub Topic</label>
                            <input type="text" name="sub_topic" class="form-control" required style="border-radius: 0;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">Content Covered</label>
                            <textarea name="content_covered" class="form-control" rows="3" required style="border-radius: 0;"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">Date Started</label>
                            <input type="date" name="date_started" class="form-control" style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">Date Ended</label>
                            <input type="date" name="date_ended" class="form-control" style="border-radius: 0;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="small font-weight-bold text-uppercase pb-1" style="color:#666;">Teacher's Comment</label>
                            <textarea name="teacher_comment" class="form-control" rows="2" style="border-radius: 0;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 0; background: #f0f0f0; color: #2f2f2f;">CANCEL</button>
                    <button type="submit" class="btn px-4 font-weight-bold text-white shadow-sm" style="border-radius: 0; background: #940000; border: none;">SAVE ENTRY</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#loadLogbookBtn').on('click', function() {
            const classSubjectID = $('#subjectSelect').val();
            if (!classSubjectID) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select a subject first.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            loadLogbook(classSubjectID);
        });

        $('#entryForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: "{{ route('teacher.save_logbook_entry') }}",
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        $('#entryModal').modal('hide');
                        $('#entryForm')[0].reset();
                        loadLogbook($('#subjectSelect').val());
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });

    function loadLogbook(classSubjectID) {
        $.ajax({
            url: "{{ url('teacher/get-logbook-data') }}/" + classSubjectID,
            type: 'GET',
            beforeSend: function() {
                $('#welcomePlaceholder').hide();
                $('#logbookTableContainer').hide();
                // Optionally show spinner
            },
            success: function(response) {
                if (response.success) {
                    $('#headerSubject').text(response.subjectName);
                    $('#headerClass').text(response.className + ' ' + response.subclassName);
                    $('#headerYear').text(response.year);
                    $('#headerTeacher').text(response.teacherName);
                    $('#formClassSubjectID').val(classSubjectID);

                    let rows = '';
                    if (response.logbookEntries.length > 0) {
                        response.logbookEntries.forEach((entry, index) => {
                            rows += `
                                <tr class="text-center small">
                                    <td class="font-weight-bold">${index + 1}</td>
                                    <td>${entry.main_topic || ''}</td>
                                    <td>${entry.sub_topic || ''}</td>
                                    <td>${entry.content_covered || ''}</td>
                                    <td>${entry.date_started || ''}</td>
                                    <td>${entry.date_ended || ''}</td>
                                    <td>${entry.teacher_comment || ''}</td>
                                    <td class="text-muted italic">${entry.hod_comment || 'Pending...'}</td>
                                    <td class="text-muted italic">${entry.hm_comment || 'Pending...'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="9" class="text-center py-5 text-muted font-weight-bold">No entries found for this subject. Click "Add Entry" to begin recording.</td></tr>';
                    }

                    $('#logbookBody').html(rows);
                    $('#logbookTableContainer').fadeIn();
                    $('#addEntryBtn').show();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    function openAddEntryModal() {
        $('#entryModal').modal('show');
    }
</script>
