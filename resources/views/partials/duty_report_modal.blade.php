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
</style>
<div class="modal fade" id="dutyReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header d-block p-4">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title mb-1"><i class="fa fa-book mr-2"></i> DAILY DUTY REPORT</h5>
                <p class="text-muted small mb-0 font-weight-bold">Detailed observation and attendance tracking.</p>
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
                                        <h6 class="mb-0" style="color: #2f2f2f;"><strong>Teacher on Duty:</strong> <u id="display_teacher_name" style="color: #940000;">---</u></h6>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h6 class="mb-0" style="color: #2f2f2f;"><strong>Day:</strong> <span id="display_day" class="text-uppercase" style="color: #940000;"></span></h6>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <h6 class="mb-0" style="color: #2f2f2f;"><strong>Date:</strong> <span id="display_date" class="font-weight-bold" style="color: #940000;"></span></h6>
                                    </div>
                                </div>
                            </div>

                            <p class="small text-muted mb-2 font-weight-bold" id="duty_enrollment_scope_note">
                                <i class="fa fa-lock mr-1" style="color: #940000;"></i>
                                Counts from <strong>Active enrollments</strong> only (header academic year), not editable. Use Sync to refresh.
                            </p>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center" id="attendance_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th rowspan="2" class="align-middle">CLASS</th>
                                            <th colspan="3">REGISTERED</th>
                                            <th colspan="3">PRESENT</th>
                                            <th colspan="3">SHIFTED</th>
                                            <th colspan="3">NEW COMERS</th>
                                            <th colspan="3">ABSENT</th>
                                            <th colspan="3">PERMISSION</th>
                                            <th colspan="3">SICK</th>
                                        </tr>
                                        <tr style="font-size: 0.7rem;">
                                            @for($i=0; $i<7; $i++)
                                                <th>B</th><th>G</th><th>T</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $cls)
                                        <tr class="class-row" data-class-id="{{ $cls->classID }}">
                                            <td class="bg-light font-weight-bold">{{ $cls->class_name }}</td>
                                            <!-- REGISTERED -->
                                            <td><input type="number" class="form-control form-control-sm reg-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- PRESENT -->
                                            <td><input type="number" class="form-control form-control-sm pres-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- SHIFTED -->
                                            <td><input type="number" class="form-control form-control-sm shift-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- NEW COMERS -->
                                            <td><input type="number" class="form-control form-control-sm new-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm new-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm new-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- ABSENT -->
                                            <td><input type="number" class="form-control form-control-sm abs-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- PERMISSION -->
                                            <td><input type="number" class="form-control form-control-sm perm-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                            <!-- SICK -->
                                            <td><input type="number" class="form-control form-control-sm sick-b" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-g" value="0" readonly tabindex="-1" style="background-color: #f8f9fa;"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-t" readonly value="0" style="background-color: #f8f9fa;"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td>TOTAL</td>
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
                                        <label class="font-weight-bold"><i class="fa fa-calculator mr-1"></i> 1. Attendance report in percentage (%):</label>
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="number" step="0.01" name="attendance_percentage" id="attendance_percentage" class="form-control font-weight-bold" readonly tabindex="-1" style="background-color: #e8f5e9; color: #2e7d32;">
                                            <div class="input-group-append"><span class="input-group-text bg-success text-white">%</span></div>
                                        </div>
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> Auto-calculated from Active enrollments + system attendance (read-only).</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>2. School environment:</label>
                                        <input type="text" name="school_environment" class="form-control form-control-sm" placeholder="e.g. Well cleaned">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>3. <span class="student-pupil-label">{{ (isset($school_details) && $school_details->school_type == 'Secondary') ? "Student's" : "Pupil's" }}</span> cleanliness:</label>
                                        <input type="text" name="pupils_cleanliness" class="form-control form-control-sm" placeholder="e.g. They were smart">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>4. Meals (Chakula):</label>
                                        <textarea name="meals" class="form-control form-control-sm" rows="2" placeholder="e.g. Chakula kiliandaliwa vizuri na wote wamekul"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>5. Time table:</label>
                                        <input type="text" name="timetable_status" class="form-control form-control-sm" placeholder="e.g. Well followed">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>6. Outside activities:</label>
                                        <input type="text" name="outside_activities" class="form-control form-control-sm" placeholder="e.g. Well conducted">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>7. Special events:</label>
                                        <input type="text" name="special_events" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>8. English speaking:</label>
                                        <input type="text" name="english_speaking" class="form-control form-control-sm" placeholder="e.g. well speakin">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>9. List of names of sick <span class="student-pupil-label-plural">{{ (isset($school_details) && $school_details->school_type == 'Secondary') ? "students" : "pupils" }}</span>:</label>
                                        <textarea name="sick_students_list" class="form-control form-control-sm" rows="2" placeholder="e.g. hassani, juma"></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>10. Teachers' on duty comments:</label>
                                        <textarea name="teacher_comments" class="form-control form-control-sm" rows="2" placeholder="Sum up the day..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Feedback Section (Simplified for Auto-Sign) -->
                    <div class="card shadow-sm border-0 border-top border-warning overflow-hidden" id="adminFeedbackSection" style="display: none; border-top-width: 5px !important;">
                        <div class="card-header bg-white py-3">
                            <h6 class="text-warning mb-0 font-weight-bold">
                                <i class="fa fa-gavel mr-2"></i> HEADMASTER / ADMINISTRATOR REVIEW
                            </h6>
                        </div>
                        <div class="card-body bg-light">
                            <div id="approvalPrompt" class="text-center py-3">
                                <p class="mb-0 text-muted">Click the button below to officially approve and sign this report.</p>
                                <small class="text-info"><i class="fa fa-info-circle"></i> Digital stamp and signature will be applied automatically.</small>
                            </div>
                            
                            <div id="signedDisplayArea" style="display: none;">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-muted small uppercase">Admin Comments:</label>
                                            <div id="admin_comments_display" class="p-2 bg-white rounded border border-light" style="min-height: 50px; font-style: italic;">---</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div id="view-only-signature" style="display: inline-block; text-align: center;">
                                            <img id="signature-image-preview" src="" style="max-height: 80px; max-width: 200px;">
                                            <div class="mt-1 border-top pt-1">
                                                <span id="signed_by_display" class="font-weight-bold" style="color: #000080;"></span><br>
                                                <small id="signedAtDisplay" class="text-success font-weight-bold">
                                                    <i class="fa fa-check-circle"></i> SIGNED ON <span id="signedAtDate"></span>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="syncFromAttendance" style="display: none;">
                        <i class="fa fa-refresh"></i> Sync From Attendance
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-danger" id="downloadReportPdf" style="display: none;">
                        <i class="fa fa-file-pdf-o"></i> Download PDF
                    </button>
                    <!-- Teacher Buttons -->
                    <button type="button" class="btn btn-info" id="saveDraft">Save as Draft</button>
                    <button type="button" class="btn btn-primary" id="saveAndSend">Send to Admin</button>
                    
                    <!-- Admin Buttons -->
                    <button type="button" class="btn btn-success" id="btnApproveReport" style="display: none;">
                        <i class="fa fa-check-square-o"></i> Approve & Sign Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
