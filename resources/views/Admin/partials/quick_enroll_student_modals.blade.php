{{-- Quick Register + Enroll Student modals (Admin manage_student + Class teacher) --}}
@php
    $qeMode = $qeMode ?? 'admin'; // admin | teacher
    $qeLockedSubclassID = $qeLockedSubclassID ?? null;
    $qeLockedSubclassLabel = $qeLockedSubclassLabel ?? '';
    $qeCanQuickRegister = $qeCanQuickRegister ?? true;
    $qeTeacherLocked = $qeMode === 'teacher' && !empty($qeLockedSubclassID);
@endphp

@once
{{-- Self-contained styles: works on Bootstrap 4 (teacher) and Bootstrap 5 (admin) --}}
<style>
    #quickRegisterStudentModal .modal-content,
    #quickTemplateDownloadModal .modal-content,
    #quickImportStudentsModal .modal-content,
    #enrollStudentModal .modal-content {
        border-radius: 0 !important;
        border: 1px solid #dee2e6;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.18);
        overflow: hidden;
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
    }
    #quickRegisterStudentModal .modal-header,
    #quickTemplateDownloadModal .modal-header,
    #quickImportStudentsModal .modal-header,
    #enrollStudentModal .modal-header {
        background: #f5f5f5 !important;
        border-bottom: 2px solid #940000 !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1rem;
    }
    #quickRegisterStudentModal .modal-title,
    #quickTemplateDownloadModal .modal-title,
    #quickImportStudentsModal .modal-title,
    #enrollStudentModal .modal-title {
        color: #940000 !important;
        font-weight: 700;
        margin: 0;
        font-size: 1.1rem;
    }
    #quickRegisterStudentModal .modal-body,
    #quickTemplateDownloadModal .modal-body,
    #quickImportStudentsModal .modal-body,
    #enrollStudentModal .modal-body {
        background: whitesmoke !important;
        padding: 1rem 1.15rem;
    }
    #quickRegisterStudentModal .modal-footer,
    #quickTemplateDownloadModal .modal-footer,
    #quickImportStudentsModal .modal-footer,
    #enrollStudentModal .modal-footer {
        background: #f5f5f5 !important;
        border-top: 1px solid #e5e5e5;
        padding: 0.75rem 1rem;
    }

    /* Close button, Bootstrap 4 .close + BS5 .btn-close fallback */
    #quickRegisterStudentModal .qe-modal-close,
    #quickTemplateDownloadModal .qe-modal-close,
    #quickImportStudentsModal .qe-modal-close,
    #enrollStudentModal .qe-modal-close {
        background: transparent;
        border: 0;
        font-size: 1.6rem;
        line-height: 1;
        color: #940000;
        opacity: 0.75;
        padding: 0 0.25rem;
        margin: 0;
        cursor: pointer;
        font-weight: 700;
    }
    #quickRegisterStudentModal .qe-modal-close:hover,
    #quickTemplateDownloadModal .qe-modal-close:hover,
    #quickImportStudentsModal .qe-modal-close:hover,
    #enrollStudentModal .qe-modal-close:hover {
        opacity: 1;
        color: #7a0000;
    }

    #quickRegisterStudentModal .form-control,
    #quickRegisterStudentModal .form-select,
    #quickTemplateDownloadModal .form-control,
    #quickImportStudentsModal .form-control,
    #enrollStudentModal .form-control,
    #enrollStudentModal .form-select {
        border-radius: 0 !important;
        border: 1px solid #ced4da;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        background-color: #fff;
        color: #212529;
        width: 100%;
        display: block;
        font-size: 0.95rem;
        box-shadow: none;
        appearance: auto;
        -webkit-appearance: menulist;
    }
    #quickRegisterStudentModal textarea.form-control,
    #enrollStudentModal textarea.form-control {
        height: auto;
        min-height: 80px;
    }
    #quickRegisterStudentModal .form-control:focus,
    #quickRegisterStudentModal .form-select:focus,
    #quickImportStudentsModal .form-control:focus,
    #enrollStudentModal .form-control:focus,
    #enrollStudentModal .form-select:focus {
        border-color: #940000;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.2);
    }
    #quickRegisterStudentModal .form-label,
    #quickImportStudentsModal .form-label,
    #enrollStudentModal .form-label {
        font-weight: 600;
        margin-bottom: 0.35rem;
        color: #212529;
        display: inline-block;
    }
    /* Bootstrap 4 does not have fw-bold / form-select / btn-close */
    #quickRegisterStudentModal .fw-bold,
    #quickTemplateDownloadModal .fw-bold,
    #quickImportStudentsModal .fw-bold,
    #enrollStudentModal .fw-bold {
        font-weight: 700 !important;
    }
    #quickRegisterStudentModal .d-flex,
    #quickImportStudentsModal .d-flex,
    #enrollStudentModal .d-flex {
        display: flex !important;
    }
    #quickRegisterStudentModal .flex-wrap,
    #quickImportStudentsModal .flex-wrap {
        flex-wrap: wrap !important;
    }
    #quickRegisterStudentModal .align-items-center {
        align-items: center !important;
    }
    #quickRegisterStudentModal .justify-content-between {
        justify-content: space-between !important;
    }
    #quickRegisterStudentModal .form-control[type="file"],
    #quickImportStudentsModal .form-control[type="file"] {
        height: auto;
        padding: 0.4rem 0.5rem;
        line-height: 1.4;
    }
    #quickRegisterStudentModal .input-group-text {
        border-radius: 0 !important;
        background: #f5f5f5;
        border: 1px solid #ced4da;
        font-weight: 600;
    }
    #quickRegisterStudentModal .input-group > .form-control {
        width: 1%;
        flex: 1 1 auto;
    }
    #quickRegisterStudentModal .row {
        margin-left: -8px;
        margin-right: -8px;
    }
    #quickRegisterStudentModal .row > [class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
        margin-bottom: 0.85rem;
    }
    #quickRegisterStudentModal .qe-step-badge,
    .qe-step-badge {
        border-radius: 0 !important;
        padding: 0.4rem 0.75rem;
        font-weight: 600;
        font-size: 0.85rem;
    }
    #quickRegisterStudentModal .btn,
    #quickTemplateDownloadModal .btn,
    #quickImportStudentsModal .btn,
    #enrollStudentModal .btn {
        border-radius: 0 !important;
    }
    #quickRegisterStudentModal .btn-secondary,
    #quickTemplateDownloadModal .btn-secondary,
    #quickImportStudentsModal .btn-secondary,
    #enrollStudentModal .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }

    /* Ensure modal sits above page widgets */
    #quickRegisterStudentModal,
    #quickTemplateDownloadModal,
    #quickImportStudentsModal,
    #enrollStudentModal {
        z-index: 1055;
    }
    .modal-backdrop {
        z-index: 1050;
    }
</style>
@endonce

@if($qeCanQuickRegister)
{{-- ========== Quick Student Registration ========== --}}
<div class="modal fade" id="quickRegisterStudentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-lightning-charge-fill"></i> Quick Student Registration
                </h5>
                <button type="button" class="close qe-modal-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickRegisterStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="background:whitesmoke;">
                    <div class="d-flex mb-3" style="gap:8px;">
                        <span class="badge qe-step-badge active" data-qstep="1" style="background:#940000;">1. Student</span>
                        <span class="badge qe-step-badge" data-qstep="2" style="background:#adb5bd;">2. Parent</span>
                    </div>

                    <div class="qe-reg-step" id="qeRegStep1">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" id="qe_first_name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" id="qe_middle_name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" id="qe_last_name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Sex <span class="text-danger">*</span></label>
                                <select class="form-control" name="gender" id="qe_gender" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Student Photo</label>
                                <div class="d-flex flex-wrap" style="gap:8px;">
                                    <input type="file" class="form-control" name="student_photo" id="qe_student_photo" accept="image/*" style="max-width:280px;">
                                    <button type="button" class="btn btn-outline-secondary" id="qeStudentCameraBtn">
                                        <i class="bi bi-camera"></i> Camera
                                    </button>
                                </div>
                                <div class="mt-2"><img id="qeStudentPhotoPreview" src="" alt="" style="max-height:80px;display:none;border:1px solid #dee2e6;"></div>
                            </div>
                        </div>
                        <p class="small text-muted mt-3 mb-0">
                            @if($qeTeacherLocked)
                                Admission number will be auto-generated. Student will be <strong>registered and enrolled</strong> into <strong style="color:#940000;">{{ $qeLockedSubclassLabel }}</strong>.
                            @else
                                Admission number will be auto-generated. Student is saved without class, enroll later.
                            @endif
                        </p>
                    </div>

                    <div class="qe-reg-step d-none" id="qeRegStep2">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parent Phone <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius:0;">
                                <span class="input-group-text" style="border-radius:0;background:#f5f5f5;font-weight:600;">255</span>
                                <input type="text" class="form-control" id="qe_parent_phone" inputmode="numeric" maxlength="9" placeholder="7XXXXXXXX" autocomplete="off" style="border-radius:0;">
                            </div>
                            <input type="hidden" name="parent_phone" id="qe_parent_phone_full" value="">
                            <small class="text-muted">Enter 9 digits only (e.g. 712345678). Starts with 255 automatically.</small>
                        </div>

                        <div id="qeParentFoundBox" class="alert d-none mb-3" style="background:#fff;border:1px solid #940000;border-radius:0;">
                            <input type="hidden" name="parent_id" id="qe_parent_id" value="">
                            <div class="fw-bold mb-1" style="color:#940000;">Parent found</div>
                            <strong id="qeParentFoundName">-</strong>
                            <div class="small text-muted" id="qeParentFoundMeta"></div>
                            <div class="d-flex flex-wrap mt-2" style="gap:8px;">
                                <button type="button" class="btn btn-sm" id="qeUseParentBtn" style="background:#940000;color:#fff;border-radius:0;">
                                    <i class="bi bi-check2"></i> Use
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="qeClearParentBtn" style="border-radius:0;">
                                    Use different
                                </button>
                            </div>
                        </div>

                        <div id="qeParentNewFields">
                            <p class="small text-muted mb-2">Enter parent details below (new parent).</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="parent_first_name" id="qe_parent_first_name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Middle Name</label>
                                    <input type="text" class="form-control" name="parent_middle_name" id="qe_parent_middle_name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="parent_last_name" id="qe_parent_last_name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sex <span class="text-danger">*</span></label>
                                    <select class="form-control" name="parent_gender" id="qe_parent_gender">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Parent Photo</label>
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        <input type="file" class="form-control" name="parent_photo" id="qe_parent_photo" accept="image/*" style="max-width:280px;">
                                        <button type="button" class="btn btn-outline-secondary" id="qeParentCameraBtn">
                                            <i class="bi bi-camera"></i> Camera
                                        </button>
                                    </div>
                                    <div class="mt-2"><img id="qeParentPhotoPreview" src="" alt="" style="max-height:80px;display:none;border:1px solid #dee2e6;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inline camera (avoids nested Bootstrap modal issues) --}}
                    <div id="qeInlineCameraPanel" class="d-none mt-3 p-3" style="background:#111;border:1px solid #940000;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-white" id="qeInlineCameraTitle"><i class="bi bi-camera"></i> Camera</strong>
                            <button type="button" class="btn btn-sm btn-light" id="qeInlineCameraClose" style="border-radius:0;">Close</button>
                        </div>
                        <video id="qeCameraVideo" autoplay playsinline muted style="width:100%;max-height:320px;background:#000;"></video>
                        <canvas id="qeCameraCanvas" style="display:none;"></canvas>
                        <p class="small text-white-50 mt-2 mb-2" id="qeCameraHint">Align face, then tap Capture</p>
                        <p class="small text-warning mt-2 mb-2 d-none" id="qeCameraFallbackHint">
                            Live preview haiwezi kwenye HTTP. Bonyeza button hapa chini kuchukua/kuchagua picha (simu itafungua kamera).
                        </p>
                        <p class="small text-danger mt-2 mb-2 d-none" id="qeCameraError"></p>
                        <button type="button" class="btn w-100 mb-2" id="qeCameraCaptureBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                            <i class="bi bi-camera-fill"></i> Capture Photo
                        </button>
                        <button type="button" class="btn btn-light w-100 d-none" id="qeCameraPickBtn" style="border-radius:0;">
                            <i class="bi bi-camera"></i> Take / Choose Photo
                        </button>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f5f5f5;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" style="border-radius:0;">Cancel</button>
                    <button type="button" class="btn btn-outline-secondary d-none" id="qeRegBackBtn" style="border-radius:0;">Back</button>
                    <button type="button" class="btn" id="qeRegNextBtn" style="background:#940000;color:#fff;border-radius:0;">Next</button>
                    <button type="submit" class="btn d-none" id="qeRegSubmitBtn" style="background:#940000;color:#fff;border-radius:0;">
                        <i class="bi bi-check-circle"></i> Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Quick Template download (optional demo data + optional enrollment) --}}
@if($qeCanQuickRegister)
<div class="modal fade" id="quickTemplateDownloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header" style="background:#f5f5f5;border-bottom:2px solid #940000;">
                <h5 class="modal-title" style="color:#940000;">
                    <i class="bi bi-download"></i> Quick Template
                </h5>
                <button type="button" class="close qe-modal-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background:whitesmoke;">
                @if($qeTeacherLocked)
                <div class="alert mb-3" style="border-radius:0;border-left:4px solid #940000;background:#fff;">
                    <strong>Class teacher template</strong>
                    <p class="mb-0 mt-2 small">Wanafunzi wote watasajiliwa na <strong>ku-enroll moja kwa moja</strong> kwenye <strong style="color:#940000;">{{ $qeLockedSubclassLabel }}</strong>. Hakuna haja ya Subclass ID kwenye Excel.</p>
                </div>
                @else
                <div class="mb-3 p-3" style="background:#fff;border:1px solid #dee2e6;border-radius:0;">
                    <div class="fw-bold mb-2" style="color:#212529;">Export type</div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="qeTemplateExportType" id="qeTemplateWithoutEnrollment" value="0" checked>
                        <label class="form-check-label" for="qeTemplateWithoutEnrollment">
                            <strong>Without enrollment</strong>
                            <span class="d-block small text-muted">Register students only, enroll later.</span>
                        </label>
                    </div>
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="radio" name="qeTemplateExportType" id="qeTemplateWithEnrollment" value="1">
                        <label class="form-check-label" for="qeTemplateWithEnrollment">
                            <strong>With enrollment</strong>
                            <span class="d-block small text-muted">Includes <em>Stream / Subclass ID</em> column + <strong>Valid Subclasses</strong> sheet (IDs za stream), jaza ID ili kusajili na ku-enroll moja kwa moja.</span>
                        </label>
                    </div>
                </div>
                @endif
                @include('Admin.partials.quick_demo_data_toggle', ['toggleId' => 'qeTemplateWithDemoData'])
                <p class="small text-muted mb-0">OFF = template tupu (mfano mmoja tu). ON = wanafunzi 2,000 wa demo (majina ya Kitanzania).</p>
            </div>
            <div class="modal-footer" style="background:#f5f5f5;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" style="border-radius:0;">Cancel</button>
                <button type="button" class="btn" id="qeTemplateDownloadConfirmBtn" style="background:#940000;color:#fff;border-radius:0;">
                    <i class="bi bi-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Quick Import (minimal columns; optional enrollment via Stream/Subclass ID) --}}
@if($qeCanQuickRegister)
<div class="modal fade" id="quickImportStudentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header" style="background:#f5f5f5;border-bottom:2px solid #940000;">
                <h5 class="modal-title" style="color:#940000;">
                    <i class="bi bi-file-earmark-excel"></i> Quick Import Students
                </h5>
                <button type="button" class="close qe-modal-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickImportStudentsForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="background:whitesmoke;">
                    @if($qeTeacherLocked)
                    <div class="alert mb-3" style="border-radius:0;border-left:4px solid #940000;background:#fff;">
                        <strong>Quick Import, {{ $qeLockedSubclassLabel }}</strong>
                        <p class="mb-2 mt-2 small">Upload taarifa chache (mwanafunzi + mzazi). Kila mwanafunzi <strong>atasajiliwa na ku-enroll moja kwa moja</strong> kwenye stream yako, <strong>hakuna Subclass ID</strong> kwenye Excel.</p>
                        <ol class="mb-0 ps-3 small">
                            <li>Download <strong>Quick Template</strong> (safu chache tu).</li>
                            <li>Jaza jina, jinsia, na simu ya mzazi (255 + tarakimu 9).</li>
                            <li>Ikiwa mzazi tayari yupo, atatumika tena.</li>
                            <li>Upload, wanafunzi wataonekana kwenye darasa lako mara moja.</li>
                        </ol>
                    </div>
                    @else
                    <div class="alert mb-3" style="border-radius:0;border-left:4px solid #940000;background:#fff;">
                        <strong>Quick Upload, maana yake nini?</strong>
                        <p class="mb-2 mt-2 small">Una-upload taarifa <strong>chache</strong> (mwanafunzi + mzazi). Unaweza pia <strong>ku-enroll moja kwa moja</strong> ukitumia template ya <em>With enrollment</em>.</p>
                        <ol class="mb-0 ps-3 small">
                            <li>Download <strong>Quick Template</strong>: chagua <em>Without enrollment</em> au <em>With enrollment</em>.</li>
                            <li>Jaza jina la mwanafunzi, jinsia, na namba ya simu ya mzazi (255 + tarakimu 9).</li>
                            <li>Ikiwa mzazi tayari yupo kwenye shule, atatumika tena (FK tu).</li>
                            <li><strong>With enrollment:</strong> jaza <em>Stream / Subclass ID</em> kutoka sheet ya <strong>Valid Subclasses</strong> (kama Import yenye details nyingi).</li>
                            <li>Without enrollment → baada ya upload chagua filter <strong>Not Enrolled</strong>, kisha <strong>Enroll Student</strong>.</li>
                        </ol>
                    </div>
                    <div class="mb-3 p-3" style="background:#fff;border:1px solid #dee2e6;border-radius:0;">
                        <div class="fw-bold mb-2">Template / import mode</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="qeImportExportType" id="qeImportWithoutEnrollment" value="0" checked>
                            <label class="form-check-label" for="qeImportWithoutEnrollment">Without enrollment</label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="radio" name="qeImportExportType" id="qeImportWithEnrollment" value="1">
                            <label class="form-check-label" for="qeImportWithEnrollment">With enrollment (Stream IDs + Valid Subclasses)</label>
                        </div>
                    </div>
                    @endif
                    <div class="mb-3">
                        @include('Admin.partials.quick_demo_data_toggle', ['toggleId' => 'qeImportWithDemoData'])
                    </div>
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-outline-secondary fw-bold" id="qeImportModalDownloadTemplateBtn" style="border-radius:0;border-color:#940000;color:#940000;">
                            <i class="bi bi-download"></i> Download Quick Template
                        </button>
                    </div>
                    <div class="mb-0" id="qeImportFileWrap">
                        <label for="qeExcelFile" class="form-label fw-bold">Excel file <span class="text-danger qe-import-file-required">*</span></label>
                        <input class="form-control" type="file" id="qeExcelFile" name="excel_file" accept=".xlsx,.xls,.csv" style="border-radius:0;">
                        <p class="small text-muted mt-1 mb-0 qe-import-file-hint">Required unless <strong>With demo data</strong> is ON, then import runs without a file.</p>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f5f5f5;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" style="border-radius:0;">Cancel</button>
                    <button type="submit" class="btn" id="qeImportSubmitBtn" style="background:#940000;color:#fff;border-radius:0;">
                        <i class="bi bi-upload"></i> Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ========== Enroll Student (tabs) ========== --}}
<div class="modal fade" id="enrollStudentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header" style="background:#f5f5f5;border-bottom:2px solid #940000;">
                <h5 class="modal-title" style="color:#940000;">
                    <i class="bi bi-person-check-fill"></i> Enroll Student
                </h5>
                <button type="button" class="close qe-modal-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background:whitesmoke;">
                <ul class="nav nav-tabs mb-3" id="enrollStudentTabs" role="tablist" style="border-bottom:1px solid #dee2e6;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="qe-enroll-tab" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#qeEnrollPane" href="#qeEnrollPane" type="button" role="tab" style="border-radius:0;color:#940000;">
                            Enroll Student
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="qe-enrolled-tab" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#qeEnrolledPane" href="#qeEnrolledPane" type="button" role="tab" style="border-radius:0;color:#940000;">
                            Enrolled
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Enroll tab --}}
                    <div class="tab-pane fade show active" id="qeEnrollPane" role="tabpanel">
                        <div id="qeEnrollStepClass">
                            @if($qeMode === 'teacher' && $qeLockedSubclassID)
                                <div class="alert mb-3" style="background:#fff;border:1px solid #940000;border-radius:0;">
                                    Enrolling into: <strong id="qeEnrollTargetLabel">{{ $qeLockedSubclassLabel }}</strong>
                                </div>
                                <input type="hidden" id="qeEnrollSubclassID" value="{{ $qeLockedSubclassID }}">
                                <button type="button" class="btn" id="qeEnrollGoStudentsBtn" style="background:#940000;color:#fff;border-radius:0;">
                                    Next, Select Students
                                </button>
                            @else
                                <label class="form-label fw-bold">Select Class</label>
                                <select class="form-control" id="qeEnrollSubclassSelect">
                                    <option value="">-- Search / Select Class (e.g. Form One B) --</option>
                                </select>
                                <button type="button" class="btn mt-3" id="qeEnrollGoStudentsBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                                    Next, Select Students
                                </button>
                                <input type="hidden" id="qeEnrollSubclassID" value="">
                            @endif
                        </div>

                        <div id="qeEnrollStepStudents" class="d-none">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:8px;">
                                <h6 class="mb-0" style="color:#940000;" id="qeEnrollInClassTitle">Enroll student in class -</h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="qeEnrollBackClassBtn" style="border-radius:0;">Change Class</button>
                            </div>
                            <div class="alert small mb-2" style="background:#fff;border:1px solid #940000;border-radius:0;">
                                Students already enrolled in another class can be selected, their previous Active enrollment will be <strong>closed</strong> (status updated, history kept), then they join this class.
                            </div>
                            <div class="d-flex flex-wrap align-items-center mb-2" style="gap:12px;">
                                <label class="mb-0 small">
                                    <input type="checkbox" id="qeEnrollUnenrolledOnly" checked> Not enrolled only
                                </label>
                                <span class="small text-muted" id="qeEnrollListMeta"></span>
                            </div>
                            <input type="search" class="form-control mb-2" id="qeEnrollStudentSearch" placeholder="Search by admission number or name..." style="border-radius:0;" autocomplete="off">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0"><input type="checkbox" id="qeEnrollSelectAll"> Select all visible</label>
                                <span class="small text-muted" id="qeEnrollSelectedCount">0 selected</span>
                            </div>
                            <div class="table-responsive" style="max-height:380px;overflow:auto;background:#fff;border:1px solid #dee2e6;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="bg-light" style="position:sticky;top:0;z-index:1;">
                                        <tr>
                                            <th width="40"></th>
                                            <th>Admission</th>
                                            <th>Name</th>
                                            <th>Sex</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qeEnrollStudentsBody">
                                        <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn mt-3" id="qeEnrollAssignBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                                <i class="bi bi-person-plus"></i> Assign to Class
                            </button>
                        </div>
                    </div>

                    {{-- Enrolled tab --}}
                    <div class="tab-pane fade" id="qeEnrolledPane" role="tabpanel">
                        @if($qeMode === 'teacher' && $qeLockedSubclassID)
                            <div class="alert mb-3" style="background:#fff;border:1px solid #940000;border-radius:0;">
                                Showing enrolled students in: <strong>{{ $qeLockedSubclassLabel }}</strong>
                            </div>
                            <input type="hidden" id="qeEnrolledSubclassID" value="{{ $qeLockedSubclassID }}">
                        @else
                            <label class="form-label fw-bold">Select Class</label>
                            <select class="form-control mb-3" id="qeEnrolledSubclassSelect">
                                <option value="">-- Search / Select Class --</option>
                            </select>
                            <input type="hidden" id="qeEnrolledSubclassID" value="">
                        @endif
                        <input type="search" class="form-control mb-2" id="qeEnrolledStudentSearch" placeholder="Search enrolled students..." style="border-radius:0;" autocomplete="off">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0"><input type="checkbox" id="qeEnrolledSelectAll"> Select all visible</label>
                            <span class="small text-muted" id="qeEnrolledSelectedCount">0 selected</span>
                        </div>
                        <div class="table-responsive" style="max-height:380px;overflow:auto;background:#fff;border:1px solid #dee2e6;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light" style="position:sticky;top:0;z-index:1;">
                                    <tr>
                                        <th width="40"></th>
                                        <th>Admission</th>
                                        <th>Name</th>
                                        <th>Sex</th>
                                        <th>Joined</th>
                                        <th>Enrolled by</th>
                                    </tr>
                                </thead>
                                <tbody id="qeEnrolledStudentsBody">
                                    <tr><td colspan="6" class="text-center text-muted">Select a class to load enrolled students</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-danger mt-3" id="qeRemoveEnrollmentBtn" style="border-radius:0;" disabled>
                            <i class="bi bi-person-dash"></i> Remove from Enrollment
                        </button>
                        <p class="small text-muted mt-2 mb-0">Removing updates the Active enrollment status to Cancelled (history kept). Student becomes free to enroll again.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#f5f5f5;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" style="border-radius:0;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    if (typeof $ !== 'function') return;

    const QE = {
        mode: @json($qeMode),
        lockedSubclassID: @json($qeLockedSubclassID ? (int)$qeLockedSubclassID : null),
        lockedLabel: @json($qeLockedSubclassLabel),
        canQuickRegister: @json((bool)$qeCanQuickRegister),
        teacherLocked: @json((bool)$qeTeacherLocked),
        routes: {
            quickRegister: @json(route('quick_register_student')),
            quickTemplate: @json(route('quick_register_template')),
            quickImport: @json(route('quick_register_import')),
            searchParent: @json(route('student.registration.search-parent')),
            listForEnroll: @json(route('quick_enroll_students_list')),
            enroll: @json(route('quick_enroll_students')),
            listEnrolled: @json(route('quick_enrolled_students_list')),
            remove: @json(route('quick_remove_enrollment')),
            subclasses: @json(route('get_subclasses_for_school')),
        },
        csrf: @json(csrf_token()),
        enrollStudentsCache: [],
        enrolledCache: [],
        cameraStream: null,
        cameraTarget: null,
        pendingParent: null,
        parentSearchTimer: null,
        lastSearchedPhone: '',
    };

    function qeSwal() {
        return (typeof StudentSwal !== 'undefined') ? StudentSwal : (typeof Swal !== 'undefined' ? Swal : null);
    }

    function qeEscape(t) {
        if (t == null) return '';
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(String(t)));
        return d.innerHTML;
    }

    function qeShowModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery(el).modal({ backdrop: 'static', keyboard: true, show: true });
            return;
        }
        if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
            bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' }).show();
            return;
        }
        el.style.display = 'block';
        el.classList.add('show');
        document.body.classList.add('modal-open');
    }

    function qeHideModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery(el).modal('hide');
            return;
        }
        if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
            const inst = bootstrap.Modal.getInstance(el);
            if (inst) inst.hide();
            return;
        }
        el.style.display = 'none';
        el.classList.remove('show');
    }

    function qeDigitsOnly(val) {
        return String(val || '').replace(/\D+/g, '');
    }

    function qeNormalizeLocalPhone(raw) {
        let d = qeDigitsOnly(raw);
        if (d.indexOf('255') === 0) d = d.slice(3);
        if (d.indexOf('0') === 0) d = d.slice(1);
        return d.slice(0, 9);
    }

    function qeFullPhone() {
        const local = qeNormalizeLocalPhone($('#qe_parent_phone').val());
        return local.length === 9 ? ('255' + local) : '';
    }

    function qeSyncPhoneHidden() {
        const full = qeFullPhone();
        $('#qe_parent_phone_full').val(full);
        return full;
    }

    function qeStopCamera() {
        if (QE.cameraStream) {
            try { QE.cameraStream.getTracks().forEach(function (t) { t.stop(); }); } catch (e) {}
            QE.cameraStream = null;
        }
        const video = document.getElementById('qeCameraVideo');
        if (video) {
            try { video.pause(); } catch (e) {}
            video.srcObject = null;
        }
        $('#qeCameraCaptureBtn').prop('disabled', true).removeClass('d-none');
        $('#qeCameraPickBtn').addClass('d-none');
        $('#qeCameraHint').removeClass('d-none');
        $('#qeCameraFallbackHint').addClass('d-none');
        $('#qeCameraError').addClass('d-none').text('');
        $('#qeInlineCameraPanel').addClass('d-none');
        $('#qeCameraVideo').removeClass('d-none');
    }

    function qeCanUseLiveCamera() {
        // Browsers block getUserMedia on http://192.168.x.x (only HTTPS or localhost).
        if (typeof window.isSecureContext === 'boolean' && !window.isSecureContext) {
            return false;
        }
        if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            const legacy = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
            return !!legacy;
        }
        return true;
    }

    function qePickFromDevice() {
        if (!QE.cameraTarget || !QE.cameraTarget.fileInput) return;
        const input = QE.cameraTarget.fileInput;
        const prevCapture = input.getAttribute('capture');
        // On phones this opens the native camera; on desktop opens file picker.
        input.setAttribute('capture', 'user');
        input.accept = 'image/*';
        const onChange = function () {
            input.removeEventListener('change', onChange);
            if (prevCapture === null) input.removeAttribute('capture');
            else input.setAttribute('capture', prevCapture);
            const file = input.files && input.files[0];
            if (file) {
                $(QE.cameraTarget.preview).attr('src', URL.createObjectURL(file)).show();
            }
            qeStopCamera();
        };
        input.addEventListener('change', onChange, { once: true });
        input.click();
    }

    function qeShowCameraFallback(message) {
        $('#qeCameraVideo').addClass('d-none');
        $('#qeCameraCaptureBtn').addClass('d-none').prop('disabled', true);
        $('#qeCameraHint').addClass('d-none');
        $('#qeCameraFallbackHint').removeClass('d-none');
        $('#qeCameraPickBtn').removeClass('d-none');
        if (message) {
            $('#qeCameraError').removeClass('d-none').text(message);
        } else {
            $('#qeCameraError').addClass('d-none').text('');
        }
    }

    function qeOpenCamera(fileInputSel, previewSel, title) {
        QE.cameraTarget = {
            fileInput: document.querySelector(fileInputSel),
            preview: previewSel
        };
        if (!QE.cameraTarget.fileInput) return;

        // On HTTP LAN (e.g. http://192.168.1.100) live preview is blocked,
        // open native camera / file picker immediately (no scary error panel).
        if (!qeCanUseLiveCamera()) {
            qePickFromDevice();
            return;
        }

        $('#qeInlineCameraTitle').html('<i class="bi bi-camera"></i> ' + qeEscape(title || 'Take Photo'));
        $('#qeCameraError').addClass('d-none').text('');
        $('#qeCameraHint').removeClass('d-none');
        $('#qeCameraFallbackHint').addClass('d-none');
        $('#qeCameraCaptureBtn').removeClass('d-none').prop('disabled', true);
        $('#qeCameraPickBtn').addClass('d-none');
        $('#qeCameraVideo').removeClass('d-none');
        $('#qeInlineCameraPanel').removeClass('d-none');

        const panel = document.getElementById('qeInlineCameraPanel');
        if (panel && panel.scrollIntoView) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        const tryGetUserMedia = function (constraints) {
            if (navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function') {
                return navigator.mediaDevices.getUserMedia(constraints);
            }
            const legacy = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
            if (!legacy) return Promise.reject(new Error('Camera API not supported'));
            return new Promise(function (resolve, reject) {
                legacy.call(navigator, constraints, resolve, reject);
            });
        };

        tryGetUserMedia({ video: { facingMode: 'user' }, audio: false })
            .catch(function () {
                return tryGetUserMedia({ video: true, audio: false });
            })
            .then(function (stream) {
                QE.cameraStream = stream;
                const video = document.getElementById('qeCameraVideo');
                video.srcObject = stream;
                const playPromise = video.play();
                if (playPromise && playPromise.catch) playPromise.catch(function () {});
                $('#qeCameraCaptureBtn').prop('disabled', false);
                $('#qeCameraPickBtn').removeClass('d-none');
            })
            .catch(function (err) {
                const msg = (err && err.name === 'NotAllowedError')
                    ? 'Camera permission denied. Use Take / Choose Photo instead.'
                    : 'Live camera unavailable. Use Take / Choose Photo.';
                qeShowCameraFallback(msg);
            });
    }

    function qeCapturePhoto() {
        const video = document.getElementById('qeCameraVideo');
        const canvas = document.getElementById('qeCameraCanvas');
        if (!video || !canvas || !QE.cameraTarget || !QE.cameraTarget.fileInput) return;
        if (!video.videoWidth) {
            const S = qeSwal();
            if (S) S.fire({ icon: 'info', title: 'Wait', text: 'Camera is still starting…', confirmButtonColor: '#940000' });
            return;
        }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function (blob) {
            if (!blob) return;
            const file = new File([blob], 'camera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                QE.cameraTarget.fileInput.files = dt.files;
            } catch (e) {}
            $(QE.cameraTarget.preview).attr('src', URL.createObjectURL(blob)).show();
            qeStopCamera();
        }, 'image/jpeg', 0.92);
    }

    $('#qeCameraCaptureBtn').on('click', qeCapturePhoto);
    $('#qeInlineCameraClose').on('click', qeStopCamera);
    $('#qeCameraPickBtn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        qePickFromDevice();
    });
    $('#quickRegisterStudentModal').on('hidden.bs.modal', function () {
        qeStopCamera();
    });

    function qeBindPhoto(fileInput, cameraBtn, preview) {
        $(fileInput).off('change.qePhoto').on('change.qePhoto', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            $(preview).attr('src', URL.createObjectURL(file)).show();
        });
        $(cameraBtn).off('click.qeCam').on('click.qeCam', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const title = String(cameraBtn).indexOf('Parent') >= 0 ? 'Parent Photo' : 'Student Photo';
            qeOpenCamera(fileInput, preview, title);
        });
    }

    // ----- Quick register -----
    let qeRegStep = 1;

    function qeSetRegStep(step) {
        qeRegStep = step;
        qeStopCamera();
        $('.qe-step-badge').each(function () {
            const s = parseInt($(this).data('qstep'), 10);
            $(this).css('background', s === step ? '#940000' : '#adb5bd');
        });
        $('#qeRegStep1').toggleClass('d-none', step !== 1);
        $('#qeRegStep2').toggleClass('d-none', step !== 2);
        $('#qeRegBackBtn').toggleClass('d-none', step === 1);
        $('#qeRegNextBtn').toggleClass('d-none', step !== 1);
        $('#qeRegSubmitBtn').toggleClass('d-none', step !== 2);
        if (step === 2) {
            setTimeout(function () { $('#qe_parent_phone').trigger('focus'); }, 150);
        }
    }

    function qeResetParentUi() {
        QE.pendingParent = null;
        QE.lastSearchedPhone = '';
        $('#qe_parent_id').val('');
        $('#qeParentFoundBox').addClass('d-none');
        $('#qeParentNewFields').removeClass('d-none');
        $('#qeUseParentBtn').html('<i class="bi bi-check2"></i> Use').prop('disabled', false);
        $('#qe_parent_first_name, #qe_parent_middle_name, #qe_parent_last_name').val('');
        $('#qe_parent_gender').val('');
        $('#qeParentPhotoPreview').hide().attr('src', '');
        $('#qe_parent_photo').val('');
    }

    function qeShowFoundParent(p, phone) {
        QE.pendingParent = p;
        $('#qe_parent_id').val(''); // not linked until Use
        $('#qeUseParentBtn').html('<i class="bi bi-check2"></i> Use').prop('disabled', false);
        $('#qeParentFoundName').text([p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' '));
        $('#qeParentFoundMeta').text((p.phone || phone) + (p.gender ? ' · ' + p.gender : ''));
        $('#qeParentFoundBox').removeClass('d-none');
        $('#qeParentNewFields').addClass('d-none');
    }

    function qeResetQuickForm() {
        const form = document.getElementById('quickRegisterStudentForm');
        if (form) form.reset();
        $('#qe_parent_phone').val('');
        $('#qe_parent_phone_full').val('');
        qeResetParentUi();
        $('#qeStudentPhotoPreview, #qeParentPhotoPreview').hide().attr('src', '');
        qeStopCamera();
        qeSetRegStep(1);
    }

    window.openQuickRegisterStudentModal = function () {
        qeResetQuickForm();
        qeShowModal('quickRegisterStudentModal');
    };

    $('#qeRegNextBtn').on('click', function () {
        const fn = ($('#qe_first_name').val() || '').trim();
        const ln = ($('#qe_last_name').val() || '').trim();
        const g = $('#qe_gender').val();
        if (!fn || !ln || !g) {
            const S = qeSwal();
            if (S) S.fire({ icon: 'warning', title: 'Required', text: 'First name, last name and sex are required.', confirmButtonColor: '#940000' });
            return;
        }
        qeSetRegStep(2);
    });
    $('#qeRegBackBtn').on('click', function () { qeSetRegStep(1); });

    qeBindPhoto('#qe_student_photo', '#qeStudentCameraBtn', '#qeStudentPhotoPreview');
    qeBindPhoto('#qe_parent_photo', '#qeParentCameraBtn', '#qeParentPhotoPreview');

    function qeSearchParentSilent() {
        const local = qeNormalizeLocalPhone($('#qe_parent_phone').val());
        $('#qe_parent_phone').val(local);
        const full = qeSyncPhoneHidden();
        if (local.length !== 9) return;
        if (full === QE.lastSearchedPhone) return;
        QE.lastSearchedPhone = full;

        $.ajax({
            url: QE.routes.searchParent,
            type: 'POST',
            data: { phone: full, _token: QE.csrf },
            dataType: 'json',
        }).done(function (res) {
            if (res.success && res.parent) {
                qeShowFoundParent(res.parent, full);
                return;
            }
            // Not found / other school: no popup, continue as new parent
            QE.pendingParent = null;
            $('#qe_parent_id').val('');
            $('#qeParentFoundBox').addClass('d-none');
            $('#qeParentNewFields').removeClass('d-none');
            if (res && res.in_other_school) {
                const S = qeSwal();
                if (S) S.fire({
                    icon: 'warning',
                    title: 'Phone in use',
                    text: res.message || 'This phone exists in another school. Use a different number.',
                    confirmButtonColor: '#940000'
                });
            }
        }).fail(function () {
            // Silent fail, user can still register new parent
            QE.pendingParent = null;
            $('#qe_parent_id').val('');
            $('#qeParentFoundBox').addClass('d-none');
            $('#qeParentNewFields').removeClass('d-none');
        });
    }

    $('#qe_parent_phone').on('input', function () {
        const local = qeNormalizeLocalPhone($(this).val());
        $(this).val(local);
        qeSyncPhoneHidden();
        // typing again clears previous selection
        if ($('#qe_parent_id').val() || QE.pendingParent) {
            $('#qe_parent_id').val('');
            QE.pendingParent = null;
            $('#qeParentFoundBox').addClass('d-none');
            $('#qeParentNewFields').removeClass('d-none');
            $('#qeUseParentBtn').html('<i class="bi bi-check2"></i> Use').prop('disabled', false);
        }
        clearTimeout(QE.parentSearchTimer);
        if (local.length === 9) {
            QE.parentSearchTimer = setTimeout(qeSearchParentSilent, 350);
        } else {
            QE.lastSearchedPhone = '';
            $('#qeParentFoundBox').addClass('d-none');
            $('#qeParentNewFields').removeClass('d-none');
        }
    });

    $('#qeUseParentBtn').on('click', function () {
        if (!QE.pendingParent) return;
        $('#qe_parent_id').val(QE.pendingParent.parentID);
        $('#qeParentNewFields').addClass('d-none');
        $('#qeParentFoundBox').removeClass('d-none');
        $('#qeUseParentBtn').html('<i class="bi bi-check2"></i> Using this parent').prop('disabled', true);
        if (QE.pendingParent.phone) {
            $('#qe_parent_phone').val(qeNormalizeLocalPhone(QE.pendingParent.phone));
            qeSyncPhoneHidden();
        }
    });

    $('#qeClearParentBtn').on('click', function () {
        QE.pendingParent = null;
        QE.lastSearchedPhone = '';
        $('#qe_parent_id').val('');
        $('#qeParentFoundBox').addClass('d-none');
        $('#qeParentNewFields').removeClass('d-none');
        $('#qeUseParentBtn').html('<i class="bi bi-check2"></i> Use').prop('disabled', false);
        $('#qe_parent_first_name, #qe_parent_middle_name, #qe_parent_last_name').val('');
        $('#qe_parent_gender').val('');
        $('#qe_parent_photo').val('');
        $('#qeParentPhotoPreview').hide().attr('src', '');
        $('#qe_parent_phone').val('').trigger('focus');
        $('#qe_parent_phone_full').val('');
    });

    $('#quickRegisterStudentForm').on('submit', function (e) {
        e.preventDefault();
        const fullPhone = qeSyncPhoneHidden();
        if (!fullPhone || fullPhone.length !== 12) {
            const S = qeSwal();
            if (S) S.fire({ icon: 'warning', title: 'Phone', text: 'Enter a valid parent phone: 9 digits after 255.', confirmButtonColor: '#940000' });
            qeSetRegStep(2);
            return;
        }

        const parentId = $('#qe_parent_id').val();
        if (!parentId) {
            const pf = ($('#qe_parent_first_name').val() || '').trim();
            const pl = ($('#qe_parent_last_name').val() || '').trim();
            const pg = $('#qe_parent_gender').val();
            if (!pf || !pl || !pg) {
                const S = qeSwal();
                if (S) S.fire({ icon: 'warning', title: 'Parent details', text: 'Click Use on an existing parent, or fill first name, last name and sex.', confirmButtonColor: '#940000' });
                return;
            }
        }

        const fd = new FormData(this);
        fd.set('parent_phone', fullPhone);
        if (!parentId) fd.delete('parent_id');
        if (QE.teacherLocked && QE.lockedSubclassID) {
            fd.set('subclassID', String(QE.lockedSubclassID));
        }
        const $btn = $('#qeRegSubmitBtn').prop('disabled', true);
        $.ajax({
            url: QE.routes.quickRegister,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': QE.csrf },
        }).done(function (res) {
            if (res.success) {
                qeHideModal('quickRegisterStudentModal');
                const S = qeSwal();
                if (S) {
                    const tip = (res.enrolled || QE.teacherLocked)
                        ? '<p class="mb-0 small">Student enrolled into <strong>' + qeEscape(res.class_label || QE.lockedLabel || 'your class') + '</strong>.</p>'
                        : '<p class="mb-0 small">Student is not enrolled yet, use Enroll Student.</p>';
                    S.fire({
                        icon: 'success',
                        title: 'Registered',
                        html: '<p>' + qeEscape(res.message) + '</p><p>Admission: <strong style="color:#940000;">' + qeEscape(res.admission_number) + '</strong></p>' + tip,
                        confirmButtonColor: '#940000'
                    }).then(function () {
                        if (typeof reloadData === 'function') reloadData();
                        else if (typeof loadStudents === 'function') loadStudents();
                        else if (typeof loadSubclassStudents === 'function') loadSubclassStudents();
                    });
                }
            } else {
                const S = qeSwal();
                if (S) S.fire({ icon: 'error', title: 'Failed', text: res.message || 'Registration failed', confirmButtonColor: '#940000' });
            }
        }).fail(function (xhr) {
            const S = qeSwal();
            let msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Registration failed';
            if (S) S.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#940000' });
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    // ----- Enroll -----
    function qeInitSubclassSelect($el, onChange) {
        if (!$el.length) return;
        $.getJSON(QE.routes.subclasses).done(function (res) {
            if (!res.success) return;
            let html = '<option value="">-- Search / Select Class --</option>';
            (res.subclasses || []).forEach(function (s) {
                const label = s.display_name || ((s.class_name || '') + ' ' + (s.subclass_name || '')).trim();
                html += '<option value="' + s.subclassID + '">' + qeEscape(label) + '</option>';
            });
            $el.html(html);
            if (typeof $.fn.select2 === 'function') {
                if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                $el.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Search class...',
                    allowClear: true,
                    dropdownParent: $('#enrollStudentModal')
                });
            }
            $el.off('change.qe').on('change.qe', function () {
                if (onChange) onChange($(this).val(), $(this).find('option:selected').text());
            });
        });
    }

    window.openEnrollStudentModal = function (opts) {
        opts = opts || {};
        QE.preselectStudentID = opts.studentID ? parseInt(opts.studentID, 10) : null;
        QE.preselectStudentName = opts.studentName || '';

        $('#qeEnrollStepClass').removeClass('d-none');
        $('#qeEnrollStepStudents').addClass('d-none');
        if (QE.preselectStudentName) {
            $('#qeEnrollStudentSearch').val(QE.preselectStudentName);
        } else {
            $('#qeEnrollStudentSearch').val('');
        }
        $('#qeEnrollUnenrolledOnly').prop('checked', true);
        if (QE.mode === 'teacher' && QE.lockedSubclassID) {
            $('#qeEnrollSubclassID').val(QE.lockedSubclassID);
            $('#qeEnrolledSubclassID').val(QE.lockedSubclassID);
            $('#qeEnrollBackClassBtn').addClass('d-none');
            $('#qeEnrollGoStudentsBtn').prop('disabled', false);
            // Jump straight to student picker for class teacher
            $('#qeEnrollInClassTitle').text('Enroll student in class ' + (QE.lockedLabel || ''));
            $('#qeEnrollStepClass').addClass('d-none');
            $('#qeEnrollStepStudents').removeClass('d-none');
            qeLoadForEnroll();
            qeLoadEnrolled();
        } else {
            $('#qeEnrollBackClassBtn').removeClass('d-none');
            qeInitSubclassSelect($('#qeEnrollSubclassSelect'), function (id, label) {
                $('#qeEnrollSubclassID').val(id || '');
                $('#qeEnrollGoStudentsBtn').prop('disabled', !id);
                $('#qeEnrollTargetLabel').text(label || '');
            });
            qeInitSubclassSelect($('#qeEnrolledSubclassSelect'), function (id) {
                $('#qeEnrolledSubclassID').val(id || '');
                if (id) qeLoadEnrolled();
            });
        }
        qeShowModal('enrollStudentModal');
    };

    $('#qeEnrollSubclassSelect').on('change', function () {
        $('#qeEnrollSubclassID').val($(this).val() || '');
        $('#qeEnrollGoStudentsBtn').prop('disabled', !$(this).val());
    });

    $('#qeEnrollGoStudentsBtn').on('click', function () {
        const id = $('#qeEnrollSubclassID').val();
        if (!id) return;
        let label = QE.lockedLabel;
        if (!label) {
            label = $('#qeEnrollSubclassSelect option:selected').text() || 'Class';
        }
        $('#qeEnrollInClassTitle').text('Enroll student in class ' + label);
        $('#qeEnrollStepClass').addClass('d-none');
        $('#qeEnrollStepStudents').removeClass('d-none');
        qeLoadForEnroll();
    });

    $('#qeEnrollBackClassBtn').on('click', function () {
        if (QE.mode === 'teacher' && QE.lockedSubclassID) return;
        $('#qeEnrollStepStudents').addClass('d-none');
        $('#qeEnrollStepClass').removeClass('d-none');
    });

    function qeApplyPreselectedEnrollStudent() {
        if (!QE.preselectStudentID) {
            return;
        }
        const id = QE.preselectStudentID;
        const $check = $('.qe-enroll-check[value="' + id + '"]');
        if ($check.length) {
            $check.prop('checked', true);
            qeUpdateEnrollSelected();
            const row = $check.closest('tr');
            if (row.length && row[0].scrollIntoView) {
                row[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }
        QE.preselectStudentID = null;
        QE.preselectStudentName = '';
    }

    function qeLoadForEnroll() {
        $('#qeEnrollStudentsBody').html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');
        $.getJSON(QE.routes.listForEnroll, {
            search: $('#qeEnrollStudentSearch').val() || '',
            unenrolled_only: $('#qeEnrollUnenrolledOnly').is(':checked') ? 1 : 0,
            limit: 3000
        })
            .done(function (res) {
                QE.enrollStudentsCache = (res.success && res.students) ? res.students : [];
                const meta = [];
                if (typeof res.not_enrolled_count === 'number') {
                    meta.push(res.not_enrolled_count + ' not enrolled');
                }
                if (typeof res.total === 'number') {
                    meta.push(res.total + ' shown');
                }
                $('#qeEnrollListMeta').text(meta.join(' · '));
                qeRenderEnrollList();
                qeApplyPreselectedEnrollStudent();
            })
            .fail(function () {
                $('#qeEnrollStudentsBody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>');
            });
    }

    function qeRenderEnrollList() {
        const q = ($('#qeEnrollStudentSearch').val() || '').toLowerCase().trim();
        const targetId = String($('#qeEnrollSubclassID').val() || '');
        let rows = QE.enrollStudentsCache.filter(function (s) {
            if (s.is_enrolled && String(s.current_subclassID) === targetId) return false; // already in this class
            if (!q) return true;
            const hay = ((s.admission_number || '') + ' ' + (s.full_name || '') + ' ' + (s.current_class || '')).toLowerCase();
            return hay.indexOf(q) !== -1;
        });
        if (!rows.length) {
            $('#qeEnrollStudentsBody').html('<tr><td colspan="5" class="text-center text-muted">No students match, try search or turn off &quot;Not enrolled only&quot; to include enrolled students.</td></tr>');
            qeUpdateEnrollSelected();
            return;
        }
        let html = '';
        rows.forEach(function (s) {
            const status = s.is_enrolled
                ? '<span class="badge" style="background:#fff3cd;color:#856404;border:1px solid #856404;border-radius:0;">Enrolled: ' + qeEscape(s.current_class || '') + '</span>'
                : '<span class="badge" style="background:#d4edda;color:#155724;border:1px solid #155724;border-radius:0;">Not enrolled</span>';
            html += '<tr>' +
                '<td><input type="checkbox" class="qe-enroll-check" value="' + s.studentID + '" data-enrolled="' + (s.is_enrolled ? '1' : '0') + '" data-from="' + qeEscape(s.current_class || '') + '"></td>' +
                '<td>' + qeEscape(s.admission_number || '-') + '</td>' +
                '<td>' + qeEscape(s.full_name || '-') + '</td>' +
                '<td>' + qeEscape(s.gender || '-') + '</td>' +
                '<td>' + status + '</td></tr>';
        });
        $('#qeEnrollStudentsBody').html(html);
        qeUpdateEnrollSelected();
    }

    let qeEnrollSearchTimer;
    $('#qeEnrollStudentSearch').on('input', function () {
        clearTimeout(qeEnrollSearchTimer);
        qeEnrollSearchTimer = setTimeout(qeLoadForEnroll, 300);
    });
    $('#qeEnrollUnenrolledOnly').on('change', function () {
        qeLoadForEnroll();
    });

    $(document).on('change', '.qe-enroll-check, #qeEnrollSelectAll', function () {
        if (this.id === 'qeEnrollSelectAll') {
            $('.qe-enroll-check').prop('checked', $(this).prop('checked'));
        }
        qeUpdateEnrollSelected();
    });

    function qeUpdateEnrollSelected() {
        const n = $('.qe-enroll-check:checked').length;
        $('#qeEnrollSelectedCount').text(n + ' selected');
        $('#qeEnrollAssignBtn').prop('disabled', n === 0);
    }

    $('#qeEnrollAssignBtn').on('click', function () {
        const subclassID = $('#qeEnrollSubclassID').val();
        const ids = $('.qe-enroll-check:checked').map(function () { return parseInt($(this).val(), 10); }).get();
        const reassigned = $('.qe-enroll-check:checked[data-enrolled="1"]').map(function () {
            return $(this).closest('tr').find('td:eq(2)').text() + ' (from ' + ($(this).data('from') || '?') + ')';
        }).get();

        let confirmHtml = 'Enroll <strong>' + ids.length + '</strong> student(s) into this class?<br><small>Uses the <strong>current academic year</strong> only.</small>';
        if (reassigned.length) {
            confirmHtml += '<br><br><div style="text-align:left;background:#fff;border:1px solid #940000;padding:8px;"><strong>Notice:</strong> These students already have an Active enrollment. The previous enrollment will be <em>closed</em> (status updated, history kept), then they join this class:<ul style="margin:6px 0 0;padding-left:18px;">' +
                reassigned.slice(0, 8).map(function (t) { return '<li>' + qeEscape(t) + '</li>'; }).join('') +
                (reassigned.length > 8 ? '<li>…and more</li>' : '') +
                '</ul></div>';
        }

        const S = qeSwal();
        if (!S) return;
        S.fire({
            icon: 'question',
            title: 'Confirm enrollment',
            html: confirmHtml,
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Assign'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const $btn = $('#qeEnrollAssignBtn').prop('disabled', true);
            $.ajax({
                url: QE.routes.enroll,
                type: 'POST',
                data: {
                    _token: QE.csrf,
                    subclassID: subclassID,
                    studentIDs: ids
                },
                dataType: 'json'
            }).done(function (res) {
                if (res.success) {
                    let html = '<p>' + qeEscape(res.message) + '</p>';
                    if (res.reassigned && res.reassigned.length) {
                        html += '<p class="small mb-0" style="color:#940000;">Previous Active enrollments were closed (history kept) for reassigned students.</p>';
                    }
                    S.fire({ icon: 'success', title: 'Done', html: html, confirmButtonColor: '#940000' }).then(function () {
                        qeLoadForEnroll();
                        if (typeof reloadData === 'function') reloadData();
                        else if (typeof loadStudents === 'function') loadStudents();
                    });
                } else {
                    S.fire({ icon: 'error', title: 'Failed', text: res.message || 'Enroll failed', confirmButtonColor: '#940000' });
                }
            }).fail(function (xhr) {
                S.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Enroll failed', confirmButtonColor: '#940000' });
            }).always(function () {
                $btn.prop('disabled', false);
                qeUpdateEnrollSelected();
            });
        });
    });

    // Enrolled tab
    function qeLoadEnrolled() {
        const id = $('#qeEnrolledSubclassID').val() || QE.lockedSubclassID;
        if (!id) {
            $('#qeEnrolledStudentsBody').html('<tr><td colspan="6" class="text-center text-muted">Select a class</td></tr>');
            return;
        }
        $('#qeEnrolledSubclassID').val(id);
        $('#qeEnrolledStudentsBody').html('<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>');
        $.getJSON(QE.routes.listEnrolled, {
            subclassID: id,
            search: $('#qeEnrolledStudentSearch').val() || ''
        }).done(function (res) {
            QE.enrolledCache = (res.success && res.students) ? res.students : [];
            qeRenderEnrolledList();
        }).fail(function () {
            $('#qeEnrolledStudentsBody').html('<tr><td colspan="6" class="text-center text-danger">Failed to load</td></tr>');
        });
    }

    function qeRenderEnrolledList() {
        const q = ($('#qeEnrolledStudentSearch').val() || '').toLowerCase().trim();
        let rows = QE.enrolledCache.filter(function (s) {
            if (!q) return true;
            return ((s.admission_number || '') + ' ' + (s.full_name || '') + ' ' + (s.enrolled_by || '')).toLowerCase().indexOf(q) !== -1;
        });
        if (!rows.length) {
            $('#qeEnrolledStudentsBody').html('<tr><td colspan="6" class="text-center text-muted">No enrolled students</td></tr>');
            qeUpdateEnrolledSelected();
            return;
        }
        let html = '';
        rows.forEach(function (s) {
            html += '<tr>' +
                '<td><input type="checkbox" class="qe-enrolled-check" value="' + s.enrollmentID + '"></td>' +
                '<td>' + qeEscape(s.admission_number || '-') + '</td>' +
                '<td>' + qeEscape(s.full_name || '-') + '</td>' +
                '<td>' + qeEscape(s.gender || '-') + '</td>' +
                '<td>' + qeEscape(s.joined_date || '-') + '</td>' +
                '<td class="small">' + qeEscape(s.enrolled_by || '-') + '</td></tr>';
        });
        $('#qeEnrolledStudentsBody').html(html);
        qeUpdateEnrolledSelected();
    }

    let qeEnrolledSearchTimer;
    $('#qeEnrolledStudentSearch').on('input', function () {
        clearTimeout(qeEnrolledSearchTimer);
        qeEnrolledSearchTimer = setTimeout(qeRenderEnrolledList, 200);
    });

    $(document).on('change', '.qe-enrolled-check, #qeEnrolledSelectAll', function () {
        if (this.id === 'qeEnrolledSelectAll') {
            $('.qe-enrolled-check').prop('checked', $(this).prop('checked'));
        }
        qeUpdateEnrolledSelected();
    });

    function qeUpdateEnrolledSelected() {
        const n = $('.qe-enrolled-check:checked').length;
        $('#qeEnrolledSelectedCount').text(n + ' selected');
        $('#qeRemoveEnrollmentBtn').prop('disabled', n === 0);
    }

    $('#qe-enrolled-tab, a[href="#qeEnrolledPane"]').on('shown.bs.tab click', function () {
        if (QE.mode === 'teacher' && QE.lockedSubclassID) qeLoadEnrolled();
        else if ($('#qeEnrolledSubclassID').val()) qeLoadEnrolled();
    });

    $('#qeRemoveEnrollmentBtn').on('click', function () {
        const ids = $('.qe-enrolled-check:checked').map(function () { return parseInt($(this).val(), 10); }).get();
        if (!ids.length) return;
        const S = qeSwal();
        if (!S) return;
        S.fire({
            icon: 'warning',
            title: 'Remove from enrollment?',
            html: 'This will <strong>close</strong> the Active enrollment for ' + ids.length + ' student(s) (status → Cancelled).<br>Enrollment history is kept. They become free to enroll again.',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Remove'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: QE.routes.remove,
                type: 'POST',
                data: { _token: QE.csrf, enrollmentIDs: ids },
                dataType: 'json'
            }).done(function (res) {
                if (res.success) {
                    S.fire({ icon: 'success', title: 'Removed', text: res.message, confirmButtonColor: '#940000' }).then(function () {
                        qeLoadEnrolled();
                        if (typeof reloadData === 'function') reloadData();
                    });
                } else {
                    S.fire({ icon: 'error', title: 'Failed', text: res.message || 'Remove failed', confirmButtonColor: '#940000' });
                }
            }).fail(function (xhr) {
                S.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Remove failed', confirmButtonColor: '#940000' });
            });
        });
    });

    function qeQuickTemplateUrl(withDemo, withEnrollment) {
        const base = QE.routes.quickTemplate || '';
        const params = [];
        if (withDemo) params.push('with_demo=1');
        if (QE.teacherLocked && QE.lockedSubclassID) {
            params.push('subclass_id=' + encodeURIComponent(String(QE.lockedSubclassID)));
        } else if (withEnrollment) {
            params.push('with_enrollment=1');
        }
        if (!params.length) return base;
        return base + (base.indexOf('?') >= 0 ? '&' : '?') + params.join('&');
    }

    function qeIsDemoDataOn($toggle) {
        return $toggle && $toggle.length && $toggle.prop('checked');
    }

    function qeIsEnrollmentExportOn(scope) {
        if (QE.teacherLocked) return false;
        if (scope === 'import') {
            return $('#qeImportWithEnrollment').is(':checked');
        }
        return $('#qeTemplateWithEnrollment').is(':checked');
    }

    function qeSyncImportDemoUi() {
        const on = qeIsDemoDataOn($('#qeImportWithDemoData'));
        const $file = $('#qeExcelFile');
        const $wrap = $('#qeImportFileWrap');
        if (on) {
            $file.prop('required', false).val('');
            $wrap.find('.qe-import-file-required').addClass('d-none');
            $wrap.find('.qe-import-file-hint').html('<span class="text-success">Demo ON, bonyeza Upload &amp; Import kuongeza wanafunzi 2,000 bila faili.</span>');
        } else {
            $file.prop('required', true);
            $wrap.find('.qe-import-file-required').removeClass('d-none');
            $wrap.find('.qe-import-file-hint').text('Required unless With demo data is ON, then import runs without a file.');
        }
    }

    $(document).on('change', '#qeImportWithDemoData', qeSyncImportDemoUi);

    $(document).on('click', '#qeImportModalDownloadTemplateBtn', function () {
        const withDemo = qeIsDemoDataOn($('#qeImportWithDemoData'));
        const withEnrollment = qeIsEnrollmentExportOn('import');
        window.open(qeQuickTemplateUrl(withDemo, withEnrollment), '_blank');
    });

    $(document).on('click', '#quickTemplateDownloadBtn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#studentPageActionsDropdown').removeClass('show');
        $('#qeTemplateWithDemoData').prop('checked', false);
        if ($('#qeTemplateWithoutEnrollment').length) {
            $('#qeTemplateWithoutEnrollment').prop('checked', true);
        }
        qeShowModal('quickTemplateDownloadModal');
    });

    $('#qeTemplateDownloadConfirmBtn').on('click', function () {
        const withDemo = qeIsDemoDataOn($('#qeTemplateWithDemoData'));
        const withEnrollment = qeIsEnrollmentExportOn('template');
        window.open(qeQuickTemplateUrl(withDemo, withEnrollment), '_blank');
        qeHideModal('quickTemplateDownloadModal');
    });

    // Action bar buttons (if present)
    $(document).on('click', '#quickRegisterStudentBtn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openQuickRegisterStudentModal();
        $('#studentPageActionsDropdown').removeClass('show');
    });
    $(document).on('click', '#quickImportStudentBtn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#studentPageActionsDropdown').removeClass('show');
        const form = document.getElementById('quickImportStudentsForm');
        if (form) form.reset();
        $('#qeImportWithDemoData').prop('checked', false);
        if ($('#qeImportWithoutEnrollment').length) {
            $('#qeImportWithoutEnrollment').prop('checked', true);
        }
        qeSyncImportDemoUi();
        qeShowModal('quickImportStudentsModal');
    });
    $('#quickImportStudentsForm').on('submit', function (e) {
        e.preventDefault();
        const withDemo = qeIsDemoDataOn($('#qeImportWithDemoData'));
        const withEnrollment = qeIsEnrollmentExportOn('import');
        const fileInput = document.getElementById('qeExcelFile');
        const hasFile = fileInput && fileInput.files && fileInput.files.length;

        if (!withDemo && !hasFile) {
            const S = qeSwal();
            if (S) S.fire({ icon: 'warning', title: 'File required', text: 'Please select an Excel file or turn on With demo data.', confirmButtonColor: '#940000' });
            return;
        }

        function qeRunQuickImport() {
            const fd = new FormData();
            fd.append('_token', QE.csrf);
            if (withDemo) {
                fd.append('with_demo', '1');
            }
            if (QE.teacherLocked && QE.lockedSubclassID) {
                fd.append('subclassID', String(QE.lockedSubclassID));
            } else if (withEnrollment) {
                fd.append('with_enrollment', '1');
            }
            if (hasFile) {
                fd.append('excel_file', fileInput.files[0]);
            }
            const $btn = $('#qeImportSubmitBtn').prop('disabled', true);
            const original = $btn.html();
            const isDemoBulk = withDemo && !hasFile;
            $btn.html(isDemoBulk
                ? '<i class="bi bi-hourglass-split"></i> Importing 2,000 demo…'
                : '<i class="bi bi-hourglass-split"></i> Importing...');
            $.ajax({
                url: QE.routes.quickImport,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: isDemoBulk ? 600000 : 180000,
                headers: { 'X-CSRF-TOKEN': QE.csrf },
            }).done(function (res) {
                const S = qeSwal();
                if (res.success && (res.imported || 0) > 0) {
                    qeHideModal('quickImportStudentsModal');
                    const enrolled = res.enrolled || 0;
                    if ($('#statusFilter').length) {
                        $('#statusFilter').val(enrolled > 0 || QE.teacherLocked ? 'Active' : 'NotEnrolled');
                    }
                    if (S) {
                        let tip;
                        if (QE.teacherLocked || enrolled > 0) {
                            tip = '<p class="mb-0 small">Wanafunzi wame-enroll kwenye <strong>' + qeEscape(res.class_label || QE.lockedLabel || 'class') + '</strong>. Fungua View Students kuwaona.</p>';
                        } else {
                            tip = '<p class="mb-0 small">Orodha imebadilishwa kuonyesha <strong>Not Enrolled</strong>. Tumia Enroll Student au Enroll Now kuweka darasani.</p>';
                        }
                        S.fire({
                            icon: 'success',
                            title: 'Imported',
                            html: '<p>' + qeEscape(res.message) + '</p>' + tip,
                            confirmButtonColor: '#940000'
                        }).then(function () {
                            if (typeof reloadData === 'function') reloadData();
                            else if (typeof loadStudents === 'function') loadStudents();
                            else if (typeof loadSubclassStudents === 'function') loadSubclassStudents();
                            else if (typeof handleFilterChange === 'function') handleFilterChange();
                        });
                    }
                } else if (S) {
                    const errHtml = (res.errors && res.errors.length)
                        ? '<ul class="text-start small mb-0">' + res.errors.slice(0, 8).map(function (e) { return '<li>' + qeEscape(e) + '</li>'; }).join('') + '</ul>'
                        : '';
                    S.fire({
                        icon: 'error',
                        title: 'Import failed',
                        html: '<p>' + qeEscape(res.message || 'No students were imported.') + '</p>' + errHtml,
                        confirmButtonColor: '#940000'
                    });
                }
            }).fail(function (xhr, textStatus) {
                const S = qeSwal();
                let errText = (xhr.responseJSON && xhr.responseJSON.message) || 'Import failed';
                if (textStatus === 'timeout') {
                    errText = 'Import took too long and timed out. Demo import may still be running on the server, refresh the student list.';
                } else if (xhr.status === 0) {
                    errText = 'Connection lost while importing. Refresh the page and check if students were added.';
                }
                if (S) S.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errText,
                    confirmButtonColor: '#940000'
                });
            }).always(function () {
                $btn.prop('disabled', false).html(original);
            });
        }

        if (withDemo && !hasFile) {
            const S = qeSwal();
            if (!S) {
                qeRunQuickImport();
                return;
            }
            const enrollNote = QE.teacherLocked
                ? 'na <strong>enrollment</strong> kwenye <strong>' + qeEscape(QE.lockedLabel || 'stream yako') + '</strong>'
                : (withEnrollment
                    ? 'na <strong>enrollment</strong> kwenye streams za shule'
                    : 'bila enrollment');
            S.fire({
                icon: 'question',
                title: 'Import demo data?',
                html: 'Hii itasajili <strong>wanafunzi 2,000 wa demo</strong> (majina ya Kitanzania, ' + enrollNote + ').<br><small class="text-muted">Inaweza kuchukua sekunde 30-90' + ((QE.teacherLocked || withEnrollment) ? ' au zaidi' : '') + '. Hakuna SMS kwa wazazi wa demo.</small>',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Import 2,000 demo'
            }).then(function (result) {
                if (result.isConfirmed) qeRunQuickImport();
            });
            return;
        }

        qeRunQuickImport();
    });
    $(document).on('click', '#enrollStudentBtn, #teacherEnrollStudentBtn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openEnrollStudentModal();
        $('#studentPageActionsDropdown').removeClass('show');
    });
})(window.jQuery || window.$);
</script>
