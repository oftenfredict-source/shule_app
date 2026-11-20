<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<style>
    #dutyReportModal {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
    }
    #signature-pad {
        background-color: #fff;
        border: 1px solid #eee;
    }
    @media (min-width: 1200px) {
        #dutyReportModal .modal-xl {
            max-width: 95% !important;
        }
    }
    .signature-box input::placeholder {
        color: #ddd;
        font-family: Arial, sans-serif;
        font-weight: normal;
        font-size: 0.9rem;
    }
    #dutyReportModal .modal-content {
        border-radius: 0;
    }
    #dutyReportModal .modal-header {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
        border-bottom: 1px solid rgba(148,0,0,0.1);
    }
    #dutyReportModal .modal-title {
        color: #940000;
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    #dutyReportModal .card {
        border-radius: 0;
    }
    #dutyReportModal #attendance_table thead th {
        background-color: #f8f9fa;
        color: #940000;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
    }
    #dutyReportModal .btn {
        border-radius: 0;
        font-weight: 700;
    }
    #dutyReportModal .btn-primary {
        background: #940000;
        border: none;
    }
    #dutyReportModal .btn-primary:hover {
        background: #7b0000;
    }
    /* Bootstrap 4 utility compatibility (modal markup) inside Bootstrap 5 layouts */
    #dutyReportModal .mr-1 { margin-right: .25rem !important; }
    #dutyReportModal .mr-2 { margin-right: .5rem !important; }
    #dutyReportModal .text-right { text-align: right !important; }
    #dutyReportModal .font-weight-bold { font-weight: 700 !important; }
    #dutyReportModal .border-right { border-right: 1px solid #dee2e6 !important; }
    #dutyReportModal .form-group { margin-bottom: 1rem; }
    #dutyReportModal .input-group-append { display: flex; }
    #dutyReportModal .modal-header .btn-close { opacity: 1; }
</style>
<div class="modal fade" id="dutyReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header d-block p-4">
                <button type="button" class="btn-close float-end" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
                <h5 class="modal-title mb-1"><i class="fa fa-book mr-2"></i> {{ __('teacher.duty.modal_title') }}</h5>
                <p class="text-muted small mb-0 font-weight-bold">{{ __('teacher.duty.modal_subtitle') }}</p>
            </div>
            <div class="modal-body p-4" style="background-color: #f4f7f6;">
                <form id="dailyDutyForm">
                    @csrf
                    <input type="hidden" name="report_date" id="report_date">
                    <input type="hidden" name="attendance_data" id="attendance_data">
                    <input type="hidden" name="reportID" id="reportID">

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="py-2 px-3 border-0 mb-3" style="background-color: #FFF5F5; border-left: 4px solid #940000 !important;">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-0" style="color: #2f2f2f;"><strong>{{ __('teacher.duty.teacher_on_duty') }}</strong> <u id="display_teacher_name" style="color: #940000;">---</u></h6>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h6 class="mb-0" style="color: #2f2f2f;"><strong>{{ __('teacher.duty.day_label') }}</strong> <span id="display_day" class="text-uppercase" style="color: #940000;"></span></h6>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <h6 class="mb-0" style="color: #2f2f2f;"><strong>{{ __('teacher.duty.date_label') }}</strong> <span id="display_date" class="font-weight-bold" style="color: #940000;"></span></h6>
                                    </div>
                                </div>
                            </div>

                            <p class="small text-muted mb-2 font-weight-bold" id="duty_enrollment_scope_note">
                                <i class="fa fa-info-circle mr-1" style="color: #940000;"></i>
                                {{ __('teacher.duty.counts_note_default') }}
                            </p>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center" id="attendance_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th rowspan="2" class="align-middle">{{ __('teacher.duty.class') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.registered') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.present') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.shifted') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.new_comers') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.absent') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.permission') }}</th>
                                            <th colspan="3">{{ __('teacher.duty.sick') }}</th>
                                        </tr>
                                        <tr style="font-size: 0.7rem;">
                                            @for($i=0; $i<7; $i++)
                                                <th>{{ __('teacher.duty.boy') }}</th><th>{{ __('teacher.duty.girl') }}</th><th>{{ __('teacher.duty.total_short') }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $cls)
                                        <tr class="class-row" data-class-id="{{ $cls->classID }}">
                                            <td class="bg-light font-weight-bold">{{ $cls->class_name }}</td>
                                            <!-- REGISTERED -->
                                            <td><input type="number" min="0" class="form-control form-control-sm reg-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm reg-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- PRESENT -->
                                            <td><input type="number" min="0" class="form-control form-control-sm pres-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm pres-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- SHIFTED -->
                                            <td><input type="number" min="0" class="form-control form-control-sm shift-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm shift-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- NEW COMERS -->
                                            <td><input type="number" min="0" class="form-control form-control-sm new-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm new-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm new-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- ABSENT -->
                                            <td><input type="number" min="0" class="form-control form-control-sm abs-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm abs-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- PERMISSION -->
                                            <td><input type="number" min="0" class="form-control form-control-sm perm-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm perm-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- SICK -->
                                            <td><input type="number" min="0" class="form-control form-control-sm sick-b" value="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm sick-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-t" readonly tabindex="-1" value="0" style="background-color: #f8f9fa;"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td>{{ __('teacher.duty.total') }}</td>
                                            @for($i=0; $i<21; $i++)
                                                <td id="total-{{ $i }}">0</td>
                                            @endfor
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold"><i class="fa fa-calculator mr-1"></i> {{ __('teacher.duty.att_pct') }}</label>
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="number" step="0.01" name="attendance_percentage" id="attendance_percentage" class="form-control font-weight-bold" readonly tabindex="-1" style="background-color: #e8f5e9; color: #2e7d32;">
                                            <div class="input-group-append"><span class="input-group-text bg-success text-white">%</span></div>
                                        </div>
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> {{ __('teacher.duty.att_pct_hint') }}</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.school_env') }}</label>
                                        <input type="text" name="school_environment" class="form-control form-control-sm" placeholder="{{ __('teacher.duty.school_env_ph') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.cleanliness', ['whose' => (isset($school_details) && $school_details->school_type == 'Secondary') ? __('teacher.duty.student_poss') : __('teacher.duty.pupil_poss')]) }}</label>
                                        <input type="text" name="pupils_cleanliness" class="form-control form-control-sm" placeholder="{{ __('teacher.duty.cleanliness_ph') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.meals') }}</label>
                                        <textarea name="meals" class="form-control form-control-sm" rows="2" placeholder="{{ __('teacher.duty.meals_ph') }}"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.timetable') }}</label>
                                        <input type="text" name="timetable_status" class="form-control form-control-sm" placeholder="{{ __('teacher.duty.timetable_ph') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.outside') }}</label>
                                        <input type="text" name="outside_activities" class="form-control form-control-sm" placeholder="{{ __('teacher.duty.outside_ph') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.special') }}</label>
                                        <input type="text" name="special_events" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.english') }}</label>
                                        <input type="text" name="english_speaking" class="form-control form-control-sm" placeholder="{{ __('teacher.duty.english_ph') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.sick_list', ['who' => (isset($school_details) && $school_details->school_type == 'Secondary') ? __('teacher.duty.students') : __('teacher.duty.pupils')]) }}</label>
                                        <textarea name="sick_students_list" class="form-control form-control-sm" rows="2" placeholder="{{ __('teacher.duty.sick_list_ph') }}"></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>{{ __('teacher.duty.comments') }}</label>
                                        <textarea name="teacher_comments" class="form-control form-control-sm" rows="2" placeholder="{{ __('teacher.duty.comments_ph') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4" id="dutyParentPermissionsCard">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 font-weight-bold" style="color: #940000;">
                                <i class="fa fa-file-text-o mr-2"></i> {{ __('teacher.duty.parent_perms_title') }}
                            </h6>
                            <small class="text-muted">{{ __('teacher.duty.parent_perms_hint') }}</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ __('teacher.duty.perm_col_student') }}</th>
                                            <th>{{ __('teacher.duty.perm_col_parent') }}</th>
                                            <th>{{ __('teacher.duty.perm_col_class') }}</th>
                                            <th>{{ __('teacher.duty.perm_col_permission') }}</th>
                                            <th>{{ __('teacher.duty.perm_col_status') }}</th>
                                            <th class="text-right">{{ __('teacher.duty.perm_col_action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dutyParentPermissionsBody">
                                        <tr><td colspan="6" class="text-center text-muted py-3">{{ __('teacher.duty.perm_open_day') }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Feedback Section (Simplified for Auto-Sign) -->
                    <div class="card shadow-sm border-0 border-top border-warning overflow-hidden" id="adminFeedbackSection" style="display: none; border-top-width: 5px !important;">
                        <div class="card-header bg-white py-3">
                            <h6 class="text-warning mb-0 font-weight-bold">
                                <i class="fa fa-gavel mr-2"></i> {{ __('teacher.duty.admin_review') }}
                            </h6>
                        </div>
                        <div class="card-body bg-light">
                            <div id="approvalPrompt" class="text-center py-3">
                                <p class="mb-0 text-muted">{{ __('teacher.duty.approve_prompt') }}</p>
                                <small class="text-info"><i class="fa fa-info-circle"></i> {{ __('teacher.duty.stamp_hint') }}</small>
                            </div>
                            
                            <div id="signedDisplayArea" style="display: none;">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-muted small uppercase">{{ __('teacher.duty.admin_comments') }}</label>
                                            <div id="admin_comments_display" class="p-2 bg-white rounded border border-light" style="min-height: 50px; font-style: italic;">---</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div id="view-only-signature" style="display: inline-block; text-align: center;">
                                            <img id="signature-image-preview" src="" style="max-height: 80px; max-width: 200px;">
                                            <div class="mt-1 border-top pt-1">
                                                <span id="signed_by_display" class="font-weight-bold" style="color: #000080;"></span><br>
                                                <small id="signedAtDisplay" class="text-success font-weight-bold">
                                                    <i class="fa fa-check-circle"></i> {{ __('teacher.duty.signed_on') }} <span id="signedAtDate"></span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('teacher.close') }}</button>
                    <button type="button" class="btn btn-warning" id="syncFromAttendance" style="display: none;">
                        <i class="fa fa-refresh"></i> {{ __('teacher.duty.sync_attendance') }}
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-danger" id="downloadReportPdf" style="display: none;">
                        <i class="fa fa-file-pdf-o"></i> {{ __('teacher.duty.download_pdf') }}
                    </button>
                    <!-- Teacher Buttons -->
                    <button type="button" class="btn btn-info" id="saveDraft">{{ __('teacher.duty.save_draft') }}</button>
                    <button type="button" class="btn btn-primary" id="saveAndSend">{{ __('teacher.duty.send_to_admin') }}</button>
                    
                    <!-- Admin Buttons -->
                    <button type="button" class="btn btn-success" id="btnApproveReport" style="display: none;">
                        <i class="fa fa-check-square-o"></i> {{ __('teacher.duty.approve_sign') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function ($) {
    var i18n = window.dutyI18n || @json(__('teacher.duty'));
    i18n.approved = i18n.approved || @json(__('teacher.approved'));
    i18n.pending = i18n.pending || @json(__('teacher.pending'));
    i18n.rejected = i18n.rejected || @json(__('teacher.rejected'));
    i18n.success = i18n.success || @json(__('teacher.duty.success'));
    i18n.error = i18n.error || @json(__('teacher.duty.error'));
    window.dutyI18n = i18n;

    function dutyPermEscape(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    window.loadDutyParentPermissions = function (date) {
        if (!date) {
            return;
        }
        $('#dutyParentPermissionsBody').html('<tr><td colspan="6" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i></td></tr>');
        $.get('{{ route("duty_book.parent_permissions") }}', { date: date }, function (res) {
            var rows = (res && res.permissions) ? res.permissions : [];
            if (!rows.length) {
                $('#dutyParentPermissionsBody').html('<tr><td colspan="6" class="text-center text-muted py-3">' + i18n.perm_none_date + '</td></tr>');
                return;
            }
            var html = '';
            rows.forEach(function (item) {
                var badge = item.status === 'approved'
                    ? '<span class="badge badge-success">' + i18n.approved + '</span>'
                    : (item.status === 'rejected'
                        ? '<span class="badge badge-danger">' + i18n.rejected + '</span>'
                        : '<span class="badge badge-warning">' + i18n.pending + '</span>');
                var action = '<span class="text-muted small">' + i18n.view_only + '</span>';
                if (item.can_act) {
                    action = '<button type="button" class="btn btn-sm btn-success duty-perm-approve" data-id="' + item.id + '">' + i18n.approve + '</button> '
                        + '<button type="button" class="btn btn-sm btn-outline-danger duty-perm-reject" data-id="' + item.id + '">' + i18n.reject + '</button>';
                }
                html += '<tr>'
                    + '<td>' + dutyPermEscape(item.student_name) + '</td>'
                    + '<td>' + dutyPermEscape(item.parent_name) + '</td>'
                    + '<td>' + dutyPermEscape(item.class_name) + '</td>'
                    + '<td>' + dutyPermEscape(item.reason_type) + '<br><small class="text-muted">' + dutyPermEscape(item.start_date) + ' - ' + dutyPermEscape(item.end_date) + '</small></td>'
                    + '<td>' + badge + '</td>'
                    + '<td class="text-right">' + action + '</td>'
                    + '</tr>';
            });
            $('#dutyParentPermissionsBody').html(html);
        }).fail(function () {
            $('#dutyParentPermissionsBody').html('<tr><td colspan="6" class="text-center text-danger py-3">' + i18n.perm_fail_load + '</td></tr>');
        });
    };

    var dutyPermLastLoad = 0;
    function dutyPermOnShown() {
        var now = Date.now();
        if (now - dutyPermLastLoad < 400) {
            return; // jQuery (BS4) and native (BS5) events may both fire
        }
        dutyPermLastLoad = now;
        window.loadDutyParentPermissions($('#report_date').val());
    }
    $('#dutyReportModal').on('shown.bs.modal', dutyPermOnShown);
    var dutyModalEl = document.getElementById('dutyReportModal');
    if (dutyModalEl) {
        dutyModalEl.addEventListener('shown.bs.modal', dutyPermOnShown);
    }

    function sendDutyPermission(url, id, title) {
        Swal.fire({
            title: title,
            input: 'textarea',
            inputPlaceholder: i18n.comments_ph_swal,
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: i18n.send,
            inputValidator: function (value) {
                if (!value || value.trim().length < 2) {
                    return i18n.need_comments;
                }
            }
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            $.post(url, {
                _token: $('#dailyDutyForm input[name="_token"]').val(),
                permissionID: id,
                admin_response: result.value,
                is_student_permission: 1
            }).done(function (res) {
                Swal.fire(i18n.success, (res && res.message) ? res.message : i18n.done_ok, 'success');
                window.loadDutyParentPermissions($('#report_date').val());
            }).fail(function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : i18n.fail_generic;
                Swal.fire(i18n.error, message, 'error');
            });
        });
    }

    $(document).on('click', '.duty-perm-approve', function () {
        sendDutyPermission('{{ route("admin.permissions.approve") }}', $(this).data('id'), i18n.approve_perm);
    });
    $(document).on('click', '.duty-perm-reject', function () {
        sendDutyPermission('{{ route("admin.permissions.reject") }}', $(this).data('id'), i18n.reject_perm);
    });
})(jQuery);
</script>
