@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Font Family Global */
    body, div, h1, h2, h3, h4, h5, h6, p, span, table, button, input, select, textarea {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }

    /* Corporate Color Scheme */
    .bg-primary-custom {
        background: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
        border-radius: 0;
    }
    .btn-primary-custom:hover {
        background-color: #7a0000;
        border-color: #7a0000;
        color: #ffffff;
    }
    .header-gradient {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%) !important;
    }
    .accent-soft-red {
        background-color: #FFF5F5 !important;
    }

    .subject-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(148, 0, 0, 0.1) !important;
    }
    
    .setting-item {
        transition: all 0.3s ease;
        border: 1px solid transparent;
        border-radius: 0;
    }
    .setting-item:hover {
        background: #FFF5F5;
        transform: translateY(-3px);
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: whitesmoke;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cfcfcf;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #940000;
    }

    /* View Class / Subclass subjects modals, scrollable body + visible scrollbar */
    #viewClassSubjectsModal .modal-body,
    #viewSubclassSubjectsModal .modal-body {
        max-height: 70vh;
        overflow-y: scroll !important;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #940000 #f1f1f1;
    }
    #viewClassSubjectsModal .modal-body::-webkit-scrollbar,
    #viewSubclassSubjectsModal .modal-body::-webkit-scrollbar {
        width: 10px;
        display: block !important;
    }
    #viewClassSubjectsModal .modal-body::-webkit-scrollbar-track,
    #viewSubclassSubjectsModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #viewClassSubjectsModal .modal-body::-webkit-scrollbar-thumb,
    #viewSubclassSubjectsModal .modal-body::-webkit-scrollbar-thumb {
        background: #940000;
        border-radius: 4px;
    }
    #viewClassSubjectsModal .modal-body::-webkit-scrollbar-thumb:hover,
    #viewSubclassSubjectsModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #7a0000;
    }

    .modal-content {
        border-radius: 0 !important;
        border: none !important;
    }
    .modal-header {
        border-radius: 0 !important;
    }

    /* Duo / multi-paper subjects (A-Level Secondary) */
    .duo-toggle-wrap {
        background: #FFF5F5;
        border: 1px solid rgba(148, 0, 0, 0.15);
        padding: 12px 14px;
        border-radius: 0;
    }
    .duo-components-box {
        background: #fafafa;
        border: 1px dashed rgba(148, 0, 0, 0.25);
        padding: 12px;
        margin-top: 10px;
    }
    .duo-component-row {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        margin-bottom: 8px;
    }
    .duo-component-row .duo-name-wrap { flex: 1; }
    .duo-component-row .duo-marks-wrap { width: 130px; }
    .duo-component-row .remove-duo-component-btn {
        margin-top: 22px;
    }
    .duo-component-row .duo-marks-wrap .duo-marks-error {
        display: none;
        color: #dc3545;
        font-size: 0.7rem;
        font-weight: 700;
        margin-top: 2px;
        line-height: 1.2;
    }
    .duo-component-row .duo-component-marks.is-invalid {
        border-color: #dc3545 !important;
        background-color: #fff5f5;
    }
    .duo-component-row .duo-component-marks.is-invalid + .duo-marks-error,
    .duo-component-row .duo-marks-wrap .duo-component-marks.is-invalid ~ .duo-marks-error {
        display: block;
    }
    /* Custom searchable select (Select2 was broken inside this Bootstrap modal) */
    .sx-ss {
        position: relative;
        width: 100%;
    }
    .sx-ss-btn {
        border-radius: 0 !important;
        text-align: left !important;
        background: #fff;
        cursor: pointer;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .sx-ss-btn .sx-ss-label {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #212529;
    }
    .sx-ss-btn .sx-ss-caret {
        color: #6c757d;
        font-size: 0.75rem;
    }
    .sx-ss-btn.is-placeholder .sx-ss-label {
        color: #6c757d;
    }
    .sx-ss-panel {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 2px);
        z-index: 2050;
        background: #fff;
        border: 1px solid #ced4da;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        display: none;
    }
    .sx-ss-panel.open {
        display: block;
    }
    .sx-ss-search {
        border: 0 !important;
        border-bottom: 1px solid #ced4da !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }
    .sx-ss-list {
        max-height: 220px;
        overflow-y: auto;
    }
    .sx-ss-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: #fff;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 0.9rem;
        color: #212529;
    }
    .sx-ss-item:hover,
    .sx-ss-item.active {
        background: #FFF5F5;
        color: #940000;
    }
    .sx-ss-item:disabled,
    .sx-ss-item.is-disabled {
        color: #adb5bd;
        cursor: not-allowed;
        background: #f8f9fa;
    }
    .sx-ss-empty {
        padding: 10px 12px;
        color: #6c757d;
        font-size: 0.85rem;
    }
    .class-subject-locked-label {
        background-color: #e9ecef;
        font-weight: 600;
        cursor: not-allowed;
    }
    .duo-papers-box {
        background: #fafafa;
        border: 1px dashed rgba(148, 0, 0, 0.3);
        padding: 12px;
        margin-top: 4px;
    }
    .duo-papers-box .duo-paper-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-end;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .duo-papers-box .duo-paper-row:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: 0;
    }
    .duo-papers-box .duo-paper-name {
        flex: 1;
        min-width: 140px;
    }
    .duo-papers-box .duo-paper-teacher {
        flex: 1.4;
        min-width: 180px;
    }

    .duo-badge {
        background: #940000 !important;
        color: #fff !important;
        font-size: 0.7rem;
        letter-spacing: 0.03em;
    }
    .duo-component-chip {
        display: inline-block;
        background: #FFF5F5;
        border: 1px solid rgba(148, 0, 0, 0.2);
        color: #940000;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 8px;
        margin: 2px;
    }
    .subject-row .field-error,
    #editSchoolSubjectForm .field-error {
        display: none;
        color: #dc3545;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 4px;
    }
    .subject-row .is-invalid,
    #editSchoolSubjectForm .is-invalid {
        border-color: #dc3545 !important;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 inside manageSubjects modals, searchable teacher picker */
    .select2-container--default .select2-selection--single {
        border-radius: 0 !important;
        border: 1px solid #ced4da !important;
        min-height: 38px;
        padding: 4px 8px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        padding-left: 0 !important;
        color: #212529;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.15rem rgba(148, 0, 0, 0.15);
    }
    .select2-dropdown {
        border-radius: 0 !important;
        border-color: #ced4da !important;
        z-index: 3000 !important;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #940000 !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 0 !important;
        border-color: #ced4da !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #940000 !important;
        outline: none;
    }
    .modal .select2-container {
        width: 100% !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Page Header (Expandable Pattern) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">MANAGE SUBJECTS</h4>
                            <p class="text-muted mb-0 small" style="font-weight: 600;">Control school-wide curricula and teacher assignments.</p>
                        </div>
                        <button class="btn shadow-sm text-white" id="toggleSubjectSettingsBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 20px;">
                            <i class="bi bi-gear-fill"></i> SETTINGS
                        </button>
                    </div>

                    @php
                        $perms = collect($teacherPermissions ?? []);
                        $isAdmin = ($user_type ?? '') == 'Admin';
                        // Match JS/controller hierarchy: any subject modify/read permission grants view
                        $canViewClassSubjects = $isAdmin || $perms->intersect([
                            'view_class_subjects', 'view_subject', 'subject_read_only',
                            'subject_create', 'subject_update', 'subject_delete',
                            'create_subject', 'edit_subject', 'update_subject', 'delete_subject',
                            'create_class_subject', 'update_class_subject', 'delete_class_subject',
                            'activate_class_subject', 'manage_class_subject',
                        ])->isNotEmpty();
                        $canCreateClassSubject = $isAdmin || $perms->intersect([
                            'create_class_subject', 'subject_create', 'subject_update', 'subject_delete',
                            'create_subject',
                        ])->isNotEmpty();
                        $canCreateSubject = $isAdmin || $perms->intersect([
                            'create_subject', 'subject_create', 'subject_update', 'subject_delete',
                        ])->isNotEmpty();
                        $canEditSubject = $isAdmin || $perms->intersect([
                            'edit_subject', 'update_subject', 'subject_update', 'subject_delete',
                            'update_class_subject',
                        ])->isNotEmpty();
                        $canDeleteSubject = $isAdmin || $perms->intersect([
                            'delete_subject', 'subject_delete', 'delete_class_subject',
                        ])->isNotEmpty();
                        $canActivateSubject = $isAdmin || $perms->intersect([
                            'activate_subject', 'activate_class_subject', 'subject_update', 'subject_delete',
                            'update_subject', 'edit_subject',
                        ])->isNotEmpty();
                        $isSecondarySchool = isset($school_details) && strcasecmp((string) ($school_details->school_type ?? ''), 'Secondary') === 0;
                    @endphp

                    <!-- Hidden triggers for AJAX refreshes -->
                    <button id="viewClassSubjectsBtn" style="display: none;"></button>

                    <!-- Expanded Settings Section -->
                    <div id="expandedSubjectSettings" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(148, 0, 0, 0.1); position: relative;">
                        <button type="button" class="close" onclick="$('#expandedSubjectSettings').slideUp(300);" style="position: absolute; right: 10px; top: 10px; color: #000000; opacity: 1;">&times;</button>
                        <div class="row text-center">
                            @if($canViewClassSubjects)
                            <div class="col-md-4 border-right">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" id="viewClassSubjectsBtn_Exp">
                                    <i class="bi bi-eye fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">VIEW CLASS SUBJECTS</h6>
                                </a>
                            </div>
                            @endif
                            @if($canCreateClassSubject)
                            <div class="col-md-4 border-right">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" data-toggle="modal" data-target="#addClassSubjectModal">
                                    <i class="bi bi-plus-circle fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">ADD CLASS SUBJECT</h6>
                                </a>
                            </div>
                            @endif
                            @if($canCreateSubject)
                            <div class="col-md-4">
                                <a href="#" class="text-decoration-none d-block py-3 setting-item" data-toggle="modal" data-target="#addSchoolSubjectModal">
                                    <i class="bi bi-plus-square fa-2x mb-2" style="color: #940000;"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">ADD SCHOOL SUBJECT</h6>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- School Subjects Grid -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
                <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                    <h5 class="mb-0 font-weight-bold" style="color: #940000;">
                        <i class="bi bi-book-fill"></i> SCHOOL SUBJECTS
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @if(count($schoolSubjects) > 0)
                            @foreach ($schoolSubjects as $subject)
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="card border-0 shadow-sm h-100 subject-card" style="transition: transform 0.2s;">
                                        <div class="card-header bg-primary-custom text-white text-center py-3">
                                            <div class="d-flex justify-content-center align-items-center mb-2">
                                                <i class="bi bi-book-fill" style="font-size: 2.5rem; color: #ffffff;"></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold text-white">
                                                {{ $subject->subject_name }}
                                                @if($subject->is_duo)
                                                    <span class="badge duo-badge ml-1">DUO</span>
                                                @endif
                                            </h5>
                                        </div>
                                        <div class="card-body p-3">
                                            @if($subject->subject_code)
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                                    <span class="text-muted small fw-bold">
                                                        <i class="bi bi-code-square text-primary-custom me-1"></i> Code:
                                                    </span>
                                                    <span class="badge bg-info text-white fw-bold">{{ $subject->subject_code }}</span>
                                                </div>
                                            </div>
                                            @endif
                                            @if($subject->is_duo && $subject->components && $subject->components->count())
                                            <div class="mb-3">
                                                <div class="p-2 bg-light rounded">
                                                    <span class="text-muted small fw-bold d-block mb-1">
                                                        <i class="bi bi-layers text-primary-custom me-1"></i> Papers:
                                                    </span>
                                                    @foreach($subject->components as $comp)
                                                        <span class="duo-component-chip">{{ $comp->component_name }} ({{ $comp->max_marks }})</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                            <div class="mb-2">
                                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                                    <span class="text-muted small fw-bold">
                                                        <i class="bi bi-circle-fill text-primary-custom me-1"></i> Status:
                                                    </span>
                                                    <span class="badge {{ $subject->status == 'Active' ? 'bg-success' : 'bg-secondary' }} fw-bold">
                                                        {{ $subject->status }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            @if($canEditSubject || $canDeleteSubject)
                                            <div class="mt-3 d-flex flex-column flex-sm-row gap-2">
                                                @if($canEditSubject)
                                                <button class="btn btn-sm btn-warning text-dark edit-school-subject-btn flex-fill w-100"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subject-name="{{ $subject->subject_name }}"
                                                        data-subject-code="{{ $subject->subject_code }}"
                                                        data-is-duo="{{ $subject->is_duo ? '1' : '0' }}"
                                                        data-components='@json($subject->components->map(function ($c) { return ["component_name" => $c->component_name, "max_marks" => $c->max_marks]; })->values())'
                                                        title="Edit Subject">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @endif
                                                @if($canDeleteSubject)
                                                <button class="btn btn-sm btn-danger delete-school-subject-btn flex-fill w-100"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subject-name="{{ $subject->subject_name }}"
                                                        title="Delete Subject">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                                @endif
                                            </div>
                                            @endif

                                            @if($canActivateSubject)
                                            <div class="mt-2 text-center">
                                                <button class="btn btn-sm {{ $subject->status == 'Active' ? 'btn-secondary' : 'btn-success' }} text-white activate-subject-btn w-100"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subject-name="{{ $subject->subject_name }}"
                                                        data-current-status="{{ $subject->status }}"
                                                        title="{{ $subject->status == 'Active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-{{ $subject->status == 'Active' ? 'x-circle' : 'check-circle' }}-fill"></i>
                                                    {{ $subject->status == 'Active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 64px; color: #940000;"></i>
                                    <p class="mt-3 mb-0 text-muted">No school subjects found. Click "Add School Subject" to create a new subject.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add School Subject Modal -->
<div class="modal fade" id="addSchoolSubjectModal" tabindex="-1" aria-labelledby="addSchoolSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addSchoolSubjectModalLabel">
                    <i class="bi bi-plus-square"></i> Add New School Subjects
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSchoolSubjectForm">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Dynamic Subjects Container -->
                    <div id="schoolSubjectsContainer">
                        <!-- First Subject Row -->
                        <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-primary-custom">
                                    <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" name="subjects[0][subject_name]" class="form-control subject-name" placeholder="e.g., Mathematics, English, Physics" required maxlength="100">
                                <div class="field-error subject-name-error">Subject name is required</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                                <input type="text" name="subjects[0][subject_code]" class="form-control subject-code" placeholder="e.g., MAT, ENG, PHY" maxlength="20">
                                <div class="field-error subject-code-error">Subject code already exists for this school</div>
                        <small class="text-muted">Must be unique within this school (same code in another school is allowed)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select name="subjects[0][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                            </div>
                            @if($isSecondarySchool)
                            <div class="mb-3 duo-toggle-wrap">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label fw-bold mb-0" for="duo_toggle_0">Duo Subject</label>
                                        <small class="d-block text-muted">ON = subject has papers (e.g. PHY1, PHY2, PHY3)</small>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input duo-toggle" id="duo_toggle_0" data-row="0">
                                        <label class="custom-control-label" for="duo_toggle_0"></label>
                                    </div>
                                </div>
                                <div class="duo-components-box" id="duo_components_0" style="display: none;">
                                    <div class="duo-components-list"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-duo-component-btn mt-1">
                                        <i class="bi bi-plus-circle"></i> Add Paper
                                    </button>
                                    <small class="d-block text-muted mt-2">Each paper: name + max marks (1-100). Need at least 2 papers.</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Add Another Subject Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-primary-custom" id="addAnotherSchoolSubjectBtn">
                            <i class="bi bi-plus-circle"></i> Add Another Subject
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save All Subjects
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit School Subject Modal -->
<div class="modal fade" id="editSchoolSubjectModal" tabindex="-1" aria-labelledby="editSchoolSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editSchoolSubjectModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit School Subject
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSchoolSubjectForm">
                @csrf
                <input type="hidden" name="subjectID" id="edit_subjectID">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="form-control" placeholder="e.g., Mathematics, English, Physics" required maxlength="100">
                        <div class="field-error" id="edit_subject_name_error">Subject name is required</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input type="text" name="subject_code" id="edit_subject_code" class="form-control" placeholder="e.g., MAT, ENG, PHY" maxlength="20">
                        <div class="field-error" id="edit_subject_code_error">Subject code already exists for this school</div>
                        <small class="text-muted">Must be unique within this school (same code in another school is allowed)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    @if($isSecondarySchool)
                    <div class="mb-3 duo-toggle-wrap">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-label fw-bold mb-0" for="edit_duo_toggle">Duo Subject</label>
                                <small class="d-block text-muted">ON = subject has papers (e.g. PHY1, PHY2, PHY3)</small>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="edit_duo_toggle">
                                <label class="custom-control-label" for="edit_duo_toggle"></label>
                            </div>
                        </div>
                        <div class="duo-components-box" id="edit_duo_components" style="display: none;">
                            <div class="duo-components-list" id="edit_duo_components_list"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_duo_component_btn">
                                <i class="bi bi-plus-circle"></i> Add Paper
                            </button>
                            <small class="d-block text-muted mt-2">Each paper: name + max marks (1-100). Need at least 2 papers.</small>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Update Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Class Subject Modal -->
<div class="modal fade" id="addClassSubjectModal" tabindex="-1" aria-labelledby="addClassSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addClassSubjectModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Class Subject
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClassSubjectForm">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Subclass <span class="text-danger">*</span></label>
                        <select name="subclassID" id="class_subject_subclass_select" class="form-select" required>
                            <option value="">Choose a subclass...</option>
                            @foreach($subclasses as $subclass)
                                <option value="{{ $subclass->subclassID }}"
                                    data-combie-id="{{ $subclass->combieID ?? '' }}"
                                    data-combie-name="{{ $subclass->combie_name ?? '' }}">
                                    {{ $subclass->display_name ?? ($subclass->stream_code ?? ($subclass->class_name . ' ' . $subclass->subclass_name)) }}
                                    @if(isset($school_details) && $school_details->school_type == 'Secondary' && $subclass->combie_name)
                                        - {{ $subclass->combie_name }}
                                        @if($subclass->combie_code)
                                            ({{ $subclass->combie_code }})
                                        @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            @if(isset($school_details) && $school_details->school_type == 'Secondary')
                                <i class="bi bi-info-circle"></i> Search and select subclass (shows combination when available)
                            @else
                                <i class="bi bi-info-circle"></i> Search and select subclass
                            @endif
                        </small>
                    </div>
                    <div class="alert alert-info mb-3" id="subclassSelectedAlert" style="display: none;">
                        <i class="bi bi-info-circle"></i> <span id="subclassSelectedAlertText">Subjects already added to this subclass stay as they are. Missing combie subjects load below, assign a teacher for each.</span>
                    </div>

                    <!-- Dynamic Subjects Container -->
                    <div id="classSubjectsContainer">
                        <!-- First Subject Row -->
                        <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-primary-custom">
                                    <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="subjects[0][subjectID]" class="form-select subject-select" required>
                                        <option value="">Choose a subject...</option>
                                        @foreach($schoolSubjects as $subject)
                                            @if($subject->status == 'Active')
                                                <option value="{{ $subject->subjectID }}">
                                                    {{ $subject->subject_name }} @if($subject->subject_code)({{ $subject->subject_code }})@endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-muted">Only active subjects are shown</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Teacher</label>
                                <select name="subjects[0][teacherID]" class="form-select teacher-select" data-placeholder="Search / select teacher...">
                                    <option value="">Select Teacher (Optional)</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted"><i class="bi bi-search"></i> Type to search by name or employee number</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select name="subjects[0][status]" class="form-select status-select" required>
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Student Status <span class="text-danger">*</span></label>
                                <select name="subjects[0][student_status]" class="form-select student-status-select" required>
                                    <option value="Required" selected>Required - All students must take this subject</option>
                                    <option value="Optional">Optional - Students can elect (election) this subject</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> <strong>Optional</strong> = election subject. Students who do not elect it will not see it on individual result reports.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Subject Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-primary-custom" id="addAnotherSubjectBtn">
                            <i class="bi bi-plus-circle"></i> Add Another Subject
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save All Subjects
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Subject Modal -->
<div class="modal fade" id="editClassSubjectModal" tabindex="-1" aria-labelledby="editClassSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editClassSubjectModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Subject Teacher
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editClassSubjectForm">
                @csrf
                <input type="hidden" name="class_subjectID" id="edit_class_subject_id">
                <input type="hidden" name="subclassID" id="edit_subclass_id">
                <input type="hidden" name="is_duo" id="edit_is_duo" value="0">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="form-control" readonly style="background-color: #e9ecef; font-weight: 600; cursor: not-allowed;" tabindex="-1">
                        <small class="text-muted" id="edit_subject_hint">Subject name cannot be changed</small>
                    </div>

                    <!-- Non-duo: single teacher -->
                    <div class="mb-3" id="editMainTeacherWrap">
                        <label class="form-label fw-bold">Select Teacher</label>
                        <select name="teacherID" id="edit_teacher_select" class="form-select teacher-select" data-placeholder="Search / select teacher...">
                            <option value="">Select Teacher (Optional)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted"><i class="bi bi-search"></i> Type to search by name or employee number, or leave empty to unassign</small>
                    </div>

                    <!-- Duo: per-paper teachers -->
                    <div class="mb-3" id="editDuoPapersWrap" style="display: none;">
                        <label class="form-label fw-bold text-primary-custom">
                            <i class="bi bi-layers"></i> Duo papers, assign a teacher per paper
                        </label>
                        <div class="duo-papers-box" id="editDuoPapersBox"></div>
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle"></i> Main subject has no single teacher. Same teacher can be selected on every paper if they teach all.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Class Subjects Modal -->
<div class="modal fade" id="viewClassSubjectsModal" tabindex="-1" aria-labelledby="viewClassSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewClassSubjectsModalLabel">
                    <i class="bi bi-book-fill"></i> Class Subjects
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body custom-scrollbar" id="viewClassSubjectsModalBody">
                <!-- Search Box -->
                <div class="mb-3" id="classSubjectsSearchContainer" style="display: none;">
                    <div class="input-group">
                        <span class="input-group-text bg-primary-custom text-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="classSubjectsSearchInput" placeholder="Search by subclass name...">
                        <button class="btn btn-outline-secondary" type="button" id="clearClassSubjectsSearch" title="Clear Search">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
                <div id="classSubjectsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading class subjects...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeViewClassSubjectsModal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Subclass Subjects Modal -->
<div class="modal fade" id="viewSubclassSubjectsModal" tabindex="-1" aria-labelledby="viewSubclassSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewSubclassSubjectsModalLabel">
                    <i class="bi bi-mortarboard-fill"></i> <span id="subclassModalTitle">Subclass Subjects</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body custom-scrollbar" id="subclassSubjectsModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subjects...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Subject Election Modal -->
<div class="modal fade" id="subjectElectionModal" tabindex="-1" aria-labelledby="subjectElectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="subjectElectionModalLabel">
                    <i class="bi bi-person-check"></i> <span id="electionSubjectTitle">Subject Election</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle"></i> Tick students who take this optional subject, then Save. Untick to remove. Use Select All / Deselect All for bulk changes.
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-success" id="adminElectionSelectAllBtn">
                            <i class="bi bi-check-all"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="adminElectionDeselectAllBtn">
                            <i class="bi bi-dash-circle"></i> Deselect All
                        </button>
                    </div>
                </div>
                <div id="electionStudentsContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading students...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary-custom" id="saveElectionBtn">
                    <i class="bi bi-save"></i> Save Election
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- Select2 (searchable teacher dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Wait for jQuery and SweetAlert to be loaded
    (function($) {
        'use strict';

        // Check if jQuery is available
        if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }

        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded!');
        }

        // Store user permissions for JavaScript checks
        var userPermissions = @json($teacherPermissions ?? collect());
        var userType = @json($user_type ?? '');
        var isSecondarySchool = @json(isset($school_details) && strcasecmp((string) ($school_details->school_type ?? ''), 'Secondary') === 0);
        var activeSchoolSubjects = @json($activeSchoolSubjectsJson ?? collect());
        var teacherOptionsData = @json($teacherOptionsJson ?? collect());
        var subjectRowCount = 0;
        var addedSubjectIDsCache = [];

        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function buildSubjectOptionsHtml(selectedId, assignedIds, locked) {
            selectedId = selectedId ? String(selectedId) : '';
            assignedIds = (assignedIds || []).map(String);
            let html = '<option value="">Choose a subject...</option>';
            activeSchoolSubjects.forEach(function(subject) {
                const id = String(subject.subjectID);
                const already = assignedIds.includes(id) && id !== selectedId;
                const selected = id === selectedId ? ' selected' : '';
                const disabled = already || (locked && id !== selectedId) ? ' disabled' : '';
                const duo = subject.is_duo ? ' [DUO]' : '';
                const code = subject.subject_code ? ' (' + subject.subject_code + ')' : '';
                const alreadyText = already ? ' (Already Added)' : '';
                html += `<option value="${id}"${selected}${disabled}>${escapeHtml(subject.subject_name)}${escapeHtml(code)}${duo}${alreadyText}</option>`;
            });
            return html;
        }

        function buildTeacherOptionsHtml(selectedId) {
            selectedId = selectedId ? String(selectedId) : '';
            let html = '<option value="">Select Teacher (Optional)</option>';
            teacherOptionsData.forEach(function(teacher) {
                const id = String(teacher.id);
                const selected = id === selectedId ? ' selected' : '';
                html += `<option value="${id}"${selected}>${escapeHtml(teacher.label)}</option>`;
            });
            return html;
        }

        function destroySelect2In($root) {
            destroyTeacherSelect2In($root);
            destroySearchSelectsIn($root);
        }

        function destroyTeacherSelect2In($root) {
            if (!$root || !$root.length) return;
            $root.find('select.teacher-select, select.edit-duo-component-teacher, select.duo-component-teacher').each(function() {
                const $select = $(this);
                if ($select.hasClass('select2-hidden-accessible') && typeof $select.select2 === 'function') {
                    try { $select.select2('destroy'); } catch (e) {}
                }
            });
        }

        /**
         * Searchable teacher dropdown (Select2), works inside Bootstrap modals.
         */
        function initTeacherSearchSelect($el) {
            if (!$el || !$el.length) return;
            if (typeof $.fn.select2 !== 'function') {
                // Fallback to custom searchable select if Select2 failed to load
                initSearchableSelect($el, 'Search teacher...');
                return;
            }

            $el.each(function() {
                const $select = $(this);
                // Remove custom sx wrapper if previously enhanced that way
                if ($select.hasClass('sx-ss-enhanced')) {
                    const $wrap = $select.closest('.sx-ss');
                    if ($wrap.length) {
                        if (typeof $select.data('sx-close-panel') === 'function') {
                            $select.data('sx-close-panel')();
                        }
                        $wrap.before($select);
                        $wrap.remove();
                    }
                    $select.removeClass('sx-ss-enhanced').removeData('sx-close-panel').removeData('sx-panel').removeData('sx-placeholder').show();
                }

                if ($select.hasClass('select2-hidden-accessible')) {
                    try { $select.select2('destroy'); } catch (e) {}
                }

                const $modal = $select.closest('.modal');
                const placeholder = $select.data('placeholder') || 'Search / select teacher...';

                $select.select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    dropdownParent: $modal.length ? $modal : $(document.body),
                    minimumResultsForSearch: 0,
                    language: {
                        noResults: function() { return 'No teacher found'; },
                        searching: function() { return 'Searching...'; }
                    }
                });
            });
        }

        function destroySearchSelectsIn($root) {
            $root.find('select.sx-ss-enhanced').each(function() {
                const $select = $(this);
                if (typeof $select.data('sx-close-panel') === 'function') {
                    $select.data('sx-close-panel')();
                }
                const $wrap = $select.closest('.sx-ss');
                if ($wrap.length) {
                    // Also remove any panel that was moved to body
                    $('body > .sx-ss-panel').each(function() {
                        const $p = $(this);
                        if ($p.data('sx-owner-wrap') && $p.data('sx-owner-wrap')[0] === $wrap[0]) {
                            $p.remove();
                        }
                    });
                    $wrap.before($select);
                    $wrap.remove();
                }
                $select.removeClass('sx-ss-enhanced').removeData('sx-close-panel').removeData('sx-panel').removeData('sx-placeholder').show();
            });
        }

        function syncSearchSelectLabel($select) {
            const $wrap = $select.closest('.sx-ss');
            if (!$wrap.length) return;
            const $btn = $wrap.find('.sx-ss-btn');
            const $opt = $select.find('option:selected');
            const val = $select.val();
            const placeholder = $select.data('sx-placeholder') || 'Select...';
            if (!val) {
                $btn.addClass('is-placeholder');
                $btn.find('.sx-ss-label').text(placeholder);
            } else {
                $btn.removeClass('is-placeholder');
                $btn.find('.sx-ss-label').text($opt.text() || placeholder);
            }
        }

        function rebuildSearchSelectList($select, filterText) {
            const $panel = $select.data('sx-panel');
            if (!$panel || !$panel.length) return;
            const $list = $panel.find('.sx-ss-list').empty();
            const q = String(filterText || '').toLowerCase().trim();
            let shown = 0;

            $select.find('option').each(function() {
                const $opt = $(this);
                const val = $opt.attr('value');
                const text = $.trim($opt.text());
                if (val === undefined || val === null || val === '') {
                    return;
                }
                if (q && text.toLowerCase().indexOf(q) === -1) {
                    return;
                }
                const disabled = !!$opt.prop('disabled');
                const isSelected = String($select.val()) === String(val);
                const $item = $('<button type="button" class="sx-ss-item"></button>')
                    .attr('data-value', val)
                    .text(text);
                if (disabled) {
                    $item.addClass('is-disabled').prop('disabled', true);
                }
                if (isSelected) {
                    $item.addClass('active');
                }
                $list.append($item);
                shown++;
            });

            if (!shown) {
                $list.append('<div class="sx-ss-empty">No results found</div>');
            }
        }

        function initSearchableSelect($el, placeholder) {
            if (!$el || !$el.length) return;
            $el.each(function() {
                const $select = $(this);
                if ($select.hasClass('sx-ss-enhanced')) {
                    syncSearchSelectLabel($select);
                    return;
                }

                const ph = placeholder || ($select.find('option[value=""]').text() || 'Select...');
                $select.data('sx-placeholder', ph);

                const $wrap = $('<div class="sx-ss"></div>');
                const $btn = $(
                    '<button type="button" class="form-control sx-ss-btn is-placeholder">' +
                        '<span class="sx-ss-label"></span>' +
                        '<span class="sx-ss-caret"><i class="bi bi-chevron-down"></i></span>' +
                    '</button>'
                );
                const $panel = $(
                    '<div class="sx-ss-panel">' +
                        '<input type="text" class="form-control sx-ss-search" placeholder="Type to search..." autocomplete="off">' +
                        '<div class="sx-ss-list"></div>' +
                    '</div>'
                );

                $select.addClass('sx-ss-enhanced').hide();
                $select.data('sx-panel', $panel);
                $select.before($wrap);
                $wrap.append($btn).append($panel).append($select);
                $btn.find('.sx-ss-label').text(ph);
                syncSearchSelectLabel($select);

                function closePanel() {
                    $panel.removeClass('open').css({
                        position: '',
                        left: '',
                        top: '',
                        width: '',
                        zIndex: ''
                    });
                    // Keep panel node with wrap for cleanup
                    if ($panel.parent()[0] !== $wrap[0]) {
                        $wrap.append($panel);
                    }
                }

                $btn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const willOpen = !$panel.hasClass('open');
                    $('.sx-ss-panel.open').each(function() {
                        const $p = $(this);
                        const $owner = $p.data('sx-owner-wrap');
                        $p.removeClass('open').css({
                            position: '',
                            left: '',
                            top: '',
                            width: '',
                            zIndex: ''
                        });
                        if ($owner && $owner.length) {
                            $owner.append($p);
                        }
                    });
                    if (willOpen) {
                        const rect = this.getBoundingClientRect();
                        // Append to body so Bootstrap modal transform/overflow cannot clip it
                        $panel.data('sx-owner-wrap', $wrap);
                        $('body').append($panel);
                        $panel.css({
                            position: 'fixed',
                            left: rect.left + 'px',
                            top: (rect.bottom + 2) + 'px',
                            width: Math.max(rect.width, 240) + 'px',
                            zIndex: 3000
                        }).addClass('open');
                        $panel.find('.sx-ss-search').val('');
                        rebuildSearchSelectList($select, '');
                        setTimeout(function() {
                            $panel.find('.sx-ss-search').trigger('focus');
                        }, 10);
                    } else {
                        closePanel();
                    }
                });

                $panel.on('input', '.sx-ss-search', function() {
                    rebuildSearchSelectList($select, $(this).val());
                });

                $panel.on('click', '.sx-ss-item', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) {
                        return;
                    }
                    const val = String($(this).attr('data-value'));
                    $select.val(val).trigger('change');
                    syncSearchSelectLabel($select);
                    closePanel();
                });

                $panel.on('click', function(e) {
                    e.stopPropagation();
                });

                $select.data('sx-close-panel', closePanel);
            });
        }

        function initClassSubjectFormSelect2() {
            initClassSubjectSearchSelects();
        }

        function initClassSubjectSearchSelects() {
            initSearchableSelect($('#class_subject_subclass_select'), 'Search subclass...');
            $('#addClassSubjectModal .subject-select').each(function() {
                initSearchableSelect($(this), 'Search subject...');
            });
            $('#addClassSubjectModal .teacher-select').each(function() {
                initTeacherSearchSelect($(this));
            });
        }

        // When subject changes (manual row), show/hide duo paper teachers
        $(document).on('change', '#addClassSubjectModal .subject-select', function() {
            const $row = $(this).closest('.subject-row');
            applyDuoUiForRow($row, $(this).val());
            $row.attr('data-is-duo', $row.find('.duo-papers-wrap').length ? '1' : '0');
        });

        // Close searchable panels when clicking outside / scrolling
        function closeAllSxPanels() {
            $('.sx-ss-panel.open').each(function() {
                const $p = $(this);
                const $owner = $p.data('sx-owner-wrap');
                $p.removeClass('open').css({
                    position: '',
                    left: '',
                    top: '',
                    width: '',
                    zIndex: ''
                });
                if ($owner && $owner.length) {
                    $owner.append($p);
                }
            });
        }

        $(document).on('click.sxss', function() {
            closeAllSxPanels();
        });

        $(window).on('resize.sxss', function() {
            closeAllSxPanels();
        });

        // Modal body scroll should close floating panels
        $(document).on('scroll.sxss', '#addClassSubjectModal .modal-body, #editClassSubjectModal .modal-body', function() {
            closeAllSxPanels();
        });

        function getSubjectMeta(subjectID) {
            const id = String(subjectID || '');
            return (activeSchoolSubjects || []).find(function(s) {
                return String(s.subjectID) === id;
            }) || null;
        }

        function buildDuoPapersHtml(rowIndex, components, teacherMap) {
            components = components || [];
            teacherMap = teacherMap || {};
            if (!components.length) {
                return '';
            }
            let rows = '';
            components.forEach(function(comp, i) {
                const cid = comp.componentID;
                const teacherVal = teacherMap[cid] || teacherMap[String(cid)] || '';
                rows += `
                    <div class="duo-paper-row" data-component-id="${escapeHtml(cid)}">
                        <div class="duo-paper-name">
                            <label class="form-label fw-bold mb-1">Paper ${i + 1}</label>
                            <input type="text" class="form-control class-subject-locked-label" value="${escapeHtml(comp.component_name)}${comp.max_marks ? ' (' + escapeHtml(comp.max_marks) + ' marks)' : ''}" readonly tabindex="-1">
                            <input type="hidden" name="subjects[${rowIndex}][components][${i}][componentID]" value="${escapeHtml(cid)}" class="duo-component-id">
                        </div>
                        <div class="duo-paper-teacher">
                            <label class="form-label fw-bold mb-1">Teacher <span class="text-muted">(paper)</span></label>
                            <select name="subjects[${rowIndex}][components][${i}][teacherID]" class="form-select teacher-select duo-component-teacher" data-placeholder="Search / select teacher...">
                                ${buildTeacherOptionsHtml(teacherVal)}
                            </select>
                            <small class="text-muted"><i class="bi bi-search"></i> Type to search</small>
                        </div>
                    </div>`;
            });
            return `
                <div class="mb-3 duo-papers-wrap">
                    <label class="form-label fw-bold text-primary-custom">
                        <i class="bi bi-layers"></i> Duo papers, assign a teacher per paper
                    </label>
                    <div class="duo-papers-box">
                        ${rows}
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle"></i> Main subject has no single teacher. Same teacher can be selected on every paper if they teach all.
                        </small>
                    </div>
                </div>`;
        }

        function buildMainTeacherHtml(rowIndex, teacherID, visible) {
            const display = visible ? '' : 'display: none;';
            return `
                <div class="mb-3 main-teacher-wrap" style="${display}">
                    <label class="form-label fw-bold">Teacher</label>
                    <select name="subjects[${rowIndex}][teacherID]" class="form-select teacher-select main-teacher-select" data-placeholder="Search / select teacher...">
                        ${buildTeacherOptionsHtml(teacherID)}
                    </select>
                    <small class="text-muted"><i class="bi bi-search"></i> Type to search by name or employee number</small>
                </div>`;
        }

        function applyDuoUiForRow($row, subjectID) {
            const rowIndex = $row.attr('data-row-index') || 0;
            const meta = getSubjectMeta(subjectID);
            const isDuo = !!(meta && meta.is_duo && meta.components && meta.components.length);
            const $mainTeacher = $row.find('.main-teacher-wrap');
            $row.find('.duo-papers-wrap').remove();

            if (isDuo) {
                $mainTeacher.hide();
                $mainTeacher.find('select').val('');
                const papersHtml = buildDuoPapersHtml(rowIndex, meta.components, {});
                if ($mainTeacher.length) {
                    $mainTeacher.after(papersHtml);
                } else {
                    $row.find('.subject-select, .subject-select-value').closest('.mb-3').after(papersHtml);
                }
                $row.find('.duo-component-teacher').each(function() {
                    initTeacherSearchSelect($(this));
                });
            } else {
                $mainTeacher.show();
                initTeacherSearchSelect($mainTeacher.find('.teacher-select'));
            }
        }

        function buildClassSubjectRowHtml(rowIndex, options) {
            options = options || {};
            const locked = !!options.locked;
            const subjectID = options.subjectID || '';
            const subjectLabel = options.subjectLabel || '';
            const teacherID = options.teacherID || '';
            const studentStatus = options.studentStatus || (locked ? 'Required' : '');
            const canRemove = options.canRemove !== false;
            const subjectNumber = options.subjectNumber || (rowIndex + 1);
            const components = options.components || [];
            const isDuo = !!(options.isDuo && components.length);

            let subjectBlock;
            if (locked) {
                subjectBlock = `
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control class-subject-locked-label" value="${escapeHtml(subjectLabel)}" readonly tabindex="-1">
                        <input type="hidden" name="subjects[${rowIndex}][subjectID]" value="${escapeHtml(subjectID)}" class="subject-select-value" data-locked="1">
                        <small class="text-muted"><i class="bi bi-lock-fill"></i> Pre-filled from combie${isDuo ? ' (DUO, assign teachers per paper below)' : ', assign a teacher'}</small>
                    </div>`;
            } else {
                subjectBlock = `
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                        <select name="subjects[${rowIndex}][subjectID]" class="form-select subject-select" required>
                            ${buildSubjectOptionsHtml(subjectID, addedSubjectIDsCache, false)}
                        </select>
                        <small class="text-muted">Only active subjects are shown</small>
                    </div>`;
            }

            const teacherBlock = isDuo
                ? buildDuoPapersHtml(rowIndex, components, options.componentTeachers || {})
                : buildMainTeacherHtml(rowIndex, teacherID, true);

            // Keep a hidden main-teacher slot for duo rows so toggling works if subject changes
            const hiddenMain = isDuo ? buildMainTeacherHtml(rowIndex, '', false) : '';

            return `
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="${rowIndex}" data-locked="${locked ? '1' : '0'}" data-is-duo="${isDuo ? '1' : '0'}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">${subjectNumber}</span>
                            ${isDuo ? '<span class="badge badge-danger ms-1" style="border-radius:0;background:#940000;">DUO</span>' : ''}
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="${canRemove ? '' : 'display: none;'}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    ${subjectBlock}
                    ${hiddenMain}
                    ${teacherBlock}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[${rowIndex}][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Status <span class="text-danger">*</span></label>
                        <select name="subjects[${rowIndex}][student_status]" class="form-select student-status-select" required>
                            <option value="Required"${!studentStatus || studentStatus === 'Required' ? ' selected' : ''}>Required - All students must take this subject</option>
                            <option value="Optional"${studentStatus === 'Optional' ? ' selected' : ''}>Optional - Students can elect (election) this subject</option>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> <strong>Optional</strong> = election subject. Unelected students will not see it on individual reports.
                        </small>
                    </div>
                </div>`;
        }

        function resetClassSubjectsContainer(manual) {
            destroySelect2In($('#classSubjectsContainer'));
            subjectRowCount = 0;
            $('#classSubjectsContainer').html(buildClassSubjectRowHtml(0, {
                locked: false,
                canRemove: false,
                subjectNumber: 1
            }));
            if (manual !== false) {
                initClassSubjectFormSelect2();
            }
        }

        function renderMissingCombieSubjects(missingSubjects, alertMeta) {
            destroySelect2In($('#classSubjectsContainer'));
            if (!missingSubjects || missingSubjects.length === 0) {
                $('#classSubjectsContainer').html(`
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> All combie main subjects are already assigned to this subclass.
                        Use <strong>Add Another Subject</strong> only if you need extra (non-combie) subjects.
                    </div>`);
                subjectRowCount = -1;
                updateClassSubjectNumbers();
                initSearchableSelect($('#class_subject_subclass_select'), 'Search subclass...');
                return;
            }

            let html = '';
            missingSubjects.forEach(function(subject, index) {
                const code = subject.subject_code ? ' (' + subject.subject_code + ')' : '';
                const isDuo = !!(subject.is_duo && subject.components && subject.components.length);
                const duo = isDuo ? ' [DUO]' : '';
                html += buildClassSubjectRowHtml(index, {
                    locked: true,
                    subjectID: subject.subjectID,
                    subjectLabel: subject.subject_name + code + duo,
                    studentStatus: 'Required',
                    canRemove: false,
                    subjectNumber: index + 1,
                    isDuo: isDuo,
                    components: subject.components || []
                });
            });
            subjectRowCount = missingSubjects.length - 1;
            $('#classSubjectsContainer').html(html);
            updateClassSubjectNumbers();
            initClassSubjectFormSelect2();

            if (alertMeta) {
                $('#subclassSelectedAlertText').html(alertMeta);
                $('#subclassSelectedAlert').show();
            }
        }

        // Helper function to check permission with hierarchy support
        function hasPermission(permissionName) {
            if (userType === 'Admin') {
                return true;
            }
            
            // Exact match check
            if (userPermissions.includes(permissionName)) {
                return true;
            }
            
            // --- SUBJECT MANAGEMENT HIERARCHY ---
            
            // Normalize to array (Laravel Collection JSON is usually a list)
            if (!Array.isArray(userPermissions)) {
                userPermissions = Object.values(userPermissions || {});
            }

            // 1. Hierarchy for View Class Subjects: any modify or read_only permission grants view
            if (permissionName === 'view_class_subjects' || permissionName === 'view_subject' || permissionName === 'subject_read_only') {
                const viewPerms = [
                    'subject_create', 'subject_update', 'subject_delete', 'subject_read_only',
                    'create_subject', 'edit_subject', 'update_subject', 'delete_subject',
                    'create_class_subject', 'update_class_subject', 'delete_class_subject',
                    'activate_class_subject', 'manage_class_subject'
                ];
                return userPermissions.some(p => viewPerms.includes(p));
            }
            
            // 2. Hierarchy for Create: subject_create or any broad modify permission
            if (permissionName === 'create_class_subject' || permissionName === 'create_subject' || permissionName === 'subject_create') {
                const createPerms = ['subject_create', 'subject_update', 'subject_delete', 'create_class_subject', 'create_subject'];
                return userPermissions.some(p => createPerms.includes(p));
            }
            
            // 3. Hierarchy for Update: subject_update or higher delete permission
            if (permissionName === 'update_class_subject' || permissionName === 'edit_subject' || permissionName === 'update_subject' || permissionName === 'subject_update' || permissionName === 'activate_class_subject' || permissionName === 'activate_subject') {
                const updatePerms = ['subject_update', 'subject_delete', 'update_class_subject', 'edit_subject', 'update_subject', 'activate_class_subject', 'activate_subject'];
                return userPermissions.some(p => updatePerms.includes(p));
            }
            
            // 4. Hierarchy for Delete: subject_delete
            if (permissionName === 'delete_class_subject' || permissionName === 'delete_subject' || permissionName === 'subject_delete') {
                const deletePerms = ['subject_delete', 'delete_class_subject', 'delete_subject'];
                return userPermissions.some(p => deletePerms.includes(p));
            }
            
            return false;
        }

        // Initialize Class Subjects Search Functionality
        function initializeClassSubjectsSearch() {
            var $searchInput = $('#classSubjectsSearchInput');
            var $clearBtn = $('#clearClassSubjectsSearch');
            var $container = $('#classSubjectsContent');
            
            if ($container.length === 0) return;
            
            // Search functionality
            $searchInput.on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                var $cards = $container.find('.card');
                
                if (searchTerm === '') {
                    $cards.closest('.col-12, .col-sm-6, .col-md-4, .col-lg-3').show();
                    $clearBtn.prop('disabled', true);
                } else {
                    $clearBtn.prop('disabled', false);
                    var visibleCount = 0;
                    
                    $cards.each(function() {
                        var $card = $(this);
                        var $col = $card.closest('.col-12, .col-sm-6, .col-md-4, .col-lg-3');
                        // Search only in subclass name (from card header)
                        var subclassName = $card.find('.subclass-name').data('subclass-name') || $card.find('.subclass-name').text().toLowerCase();
                        
                        if (subclassName && subclassName.indexOf(searchTerm) > -1) {
                            $col.show();
                            visibleCount++;
                        } else {
                            $col.hide();
                        }
                    });
                    
                    // Show message if no results
                    if (visibleCount === 0) {
                        if ($container.find('.no-results-message').length === 0) {
                            $container.append('<div class="no-results-message alert alert-info mt-3"><i class="bi bi-info-circle"></i> No results found matching "' + searchTerm + '"</div>');
                        }
                    } else {
                        $container.find('.no-results-message').remove();
                    }
                }
            });
            
            // Clear search
            $clearBtn.on('click', function() {
                $searchInput.val('');
                $searchInput.trigger('keyup');
                $searchInput.focus();
            });
            
            // Enable clear button on input
            $searchInput.on('input', function() {
                if ($(this).val().length > 0) {
                    $clearBtn.prop('disabled', false);
                } else {
                    $clearBtn.prop('disabled', true);
                }
            });
        }

        $(document).ready(function() {
            console.log('Add Class Subject Form exists:', $('#addClassSubjectForm').length > 0);

            // Toggle logic for expanded settings
            $('#toggleSubjectSettingsBtn').on('click', function() {
                $('#expandedSubjectSettings').slideToggle(300);
            });

            // Re-link the expanded button to the original view subjects trigger
            $('#viewClassSubjectsBtn_Exp').on('click', function(e) {
                e.preventDefault();
                $('#viewClassSubjectsBtn').click();
            });



        // ---- Duo / multi-paper subject helpers (Secondary A-Level) ----
        const isSecondarySchool = @json($isSecondarySchool ?? false);

        function buildDuoComponentRowHtml(name, marks) {
            const safeName = name || '';
            let safeMarks = parseInt(marks, 10);
            if (isNaN(safeMarks)) safeMarks = 100;
            return `
                <div class="duo-component-row">
                    <div class="duo-name-wrap">
                        <label class="small fw-bold mb-0">Paper name</label>
                        <input type="text" class="form-control form-control-sm duo-component-name" placeholder="e.g. PHY1" value="${safeName}" maxlength="100">
                        <div class="field-error duo-name-error">Paper name is required</div>
                    </div>
                    <div class="duo-marks-wrap">
                        <label class="small fw-bold mb-0">Max marks</label>
                        <input type="number" class="form-control form-control-sm duo-component-marks" min="1" max="100" value="${safeMarks}" inputmode="numeric">
                        <div class="duo-marks-error">Marks cannot exceed 100</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-duo-component-btn" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        }

        function validateDuoMarksInput($input) {
            const raw = ($input.val() || '').toString().trim();
            const $err = $input.siblings('.duo-marks-error');
            if (raw === '') {
                $input.removeClass('is-invalid');
                $err.hide().text('Marks cannot exceed 100');
                return true;
            }
            const marks = parseFloat(raw);
            if (isNaN(marks) || marks < 1) {
                $input.addClass('is-invalid');
                $err.text('Marks must be at least 1').show();
                return false;
            }
            if (marks > 100) {
                $input.addClass('is-invalid');
                $err.text('Marks cannot exceed 100').show();
                return false;
            }
            $input.removeClass('is-invalid');
            $err.hide().text('Marks cannot exceed 100');
            return true;
        }

        function validateDuoNameInput($input, requireFilled) {
            const name = ($input.val() || '').trim();
            const $err = $input.siblings('.duo-name-error');
            if (!requireFilled && name === '') {
                $input.removeClass('is-invalid');
                $err.hide();
                return true;
            }
            if (name === '') {
                $input.addClass('is-invalid');
                $err.text('Paper name is required').show();
                return false;
            }
            $input.removeClass('is-invalid');
            $err.hide();
            return true;
        }

        function validateSubjectNameInput($input) {
            const name = ($input.val() || '').trim();
            const $err = $input.siblings('.subject-name-error, .field-error').first();
            if (name === '') {
                $input.addClass('is-invalid');
                $err.text('Subject name is required').show();
                return false;
            }
            $input.removeClass('is-invalid');
            $err.hide();
            return true;
        }

        let subjectCodeCheckTimer = null;
        function validateSubjectCodeInput($input, options) {
            options = options || {};
            const code = ($input.val() || '').trim();
            const $err = $input.siblings('.subject-code-error, #edit_subject_code_error').first();
            const excludeId = options.excludeSubjectID || $input.data('exclude-subject-id') || '';

            if (code === '') {
                $input.removeClass('is-invalid is-valid');
                $input.removeData('code-invalid');
                if ($err.length) $err.hide();
                return true;
            }

            // Duplicate within current add form rows
            if ($input.hasClass('subject-code') && $input.closest('#schoolSubjectsContainer').length) {
                let dupInForm = false;
                $('#schoolSubjectsContainer .subject-code').each(function() {
                    if (this === $input[0]) return;
                    if (($(this).val() || '').trim().toLowerCase() === code.toLowerCase()) {
                        dupInForm = true;
                    }
                });
                if (dupInForm) {
                    $input.addClass('is-invalid').removeClass('is-valid').data('code-invalid', 1);
                    $err.text('Subject code is duplicated in this form').show();
                    return false;
                }
            }

            clearTimeout(subjectCodeCheckTimer);
            subjectCodeCheckTimer = setTimeout(function() {
                $.get(@json(route('check_subject_code')), {
                    subject_code: code,
                    exclude_subjectID: excludeId || ''
                }).done(function(res) {
                    if (($input.val() || '').trim() !== code) return; // stale
                    if (res.available) {
                        $input.removeClass('is-invalid').addClass('is-valid').data('code-invalid', 0);
                        $err.hide();
                    } else {
                        $input.addClass('is-invalid').removeClass('is-valid').data('code-invalid', 1);
                        $err.text(res.message || 'Subject code already exists for this school').show();
                    }
                }).fail(function() {
                    // don't block typing on network fail; server will still validate on save
                });
            }, 350);

            return $input.data('code-invalid') != 1;
        }

        function validateDuoRowRealtime($row) {
            const isDuo = isSecondarySchool && $row.find('.duo-toggle').is(':checked');
            let ok = validateSubjectNameInput($row.find('.subject-name'));
            validateSubjectCodeInput($row.find('.subject-code'));
            if ($row.find('.subject-code').data('code-invalid') == 1) ok = false;
            if (!isDuo) {
                $row.find('.duo-component-name, .duo-component-marks').removeClass('is-invalid');
                $row.find('.duo-name-error, .duo-marks-error').hide();
                return ok;
            }
            const $names = $row.find('.duo-component-name');
            let filled = 0;
            $names.each(function() {
                const filledOk = validateDuoNameInput($(this), true);
                if (($(this).val() || '').trim() !== '') filled++;
                if (!filledOk) ok = false;
            });
            $row.find('.duo-component-marks').each(function() {
                if (!validateDuoMarksInput($(this))) ok = false;
            });
            if (filled < 2) {
                ok = false;
            }
            return ok;
        }

        $(document).on('input change blur', '#schoolSubjectsContainer .subject-name', function() {
            validateSubjectNameInput($(this));
        });
        $(document).on('input change blur', '#edit_subject_name', function() {
            validateSubjectNameInput($(this));
        });
        $(document).on('input change blur', '#schoolSubjectsContainer .subject-code', function() {
            validateSubjectCodeInput($(this));
        });
        $(document).on('input change blur', '#edit_subject_code', function() {
            validateSubjectCodeInput($(this), {
                excludeSubjectID: $('#edit_subjectID').val() || ''
            });
        });
        $(document).on('input change blur', '.duo-component-marks', function() {
            validateDuoMarksInput($(this));
        });
        $(document).on('input change blur', '.duo-component-name', function() {
            const $row = $(this).closest('.subject-row, #editSchoolSubjectForm');
            const isDuo = $row.find('.duo-toggle, #edit_duo_toggle').is(':checked');
            validateDuoNameInput($(this), !!isDuo);
        });

        function duoMarksAreValid($scope) {
            let ok = true;
            $scope.find('.duo-component-marks').each(function() {
                if (!validateDuoMarksInput($(this))) {
                    ok = false;
                }
            });
            return ok;
        }
        function ensureDuoComponents($box, minRows) {
            const $list = $box.find('.duo-components-list');
            while ($list.find('.duo-component-row').length < minRows) {
                $list.append(buildDuoComponentRowHtml('', 100));
            }
        }

        function collectDuoComponentsFrom($box) {
            const components = [];
            let hasInvalidMarks = false;
            $box.find('.duo-component-row').each(function() {
                const $marksInput = $(this).find('.duo-component-marks');
                if (!validateDuoMarksInput($marksInput)) {
                    hasInvalidMarks = true;
                    return;
                }
                const name = ($(this).find('.duo-component-name').val() || '').trim();
                let marks = parseInt($marksInput.val(), 10);
                if (isNaN(marks)) marks = 100;
                if (name) {
                    components.push({ component_name: name, max_marks: marks });
                }
            });
            if (hasInvalidMarks) {
                return null; // signal invalid
            }
            return components;
        }

        function duoSectionHtml(rowIndex) {
            if (!isSecondarySchool) return '';
            return `
                <div class="mb-3 duo-toggle-wrap">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-label fw-bold mb-0" for="duo_toggle_${rowIndex}">Duo Subject</label>
                            <small class="d-block text-muted">ON = subject has papers (e.g. PHY1, PHY2, PHY3)</small>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input duo-toggle" id="duo_toggle_${rowIndex}" data-row="${rowIndex}">
                            <label class="custom-control-label" for="duo_toggle_${rowIndex}"></label>
                        </div>
                    </div>
                    <div class="duo-components-box" id="duo_components_${rowIndex}" style="display: none;">
                        <div class="duo-components-list"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary add-duo-component-btn mt-1">
                            <i class="bi bi-plus-circle"></i> Add Paper
                        </button>
                        <small class="d-block text-muted mt-2">Each paper: name + max marks (1-100). Need at least 2 papers.</small>
                    </div>
                </div>
            `;
        }

        $(document).on('change', '.duo-toggle', function() {
            const $row = $(this).closest('.subject-row');
            const $box = $row.find('.duo-components-box');
            if ($(this).is(':checked')) {
                $box.slideDown(150);
                ensureDuoComponents($box, 2);
                setTimeout(function() { validateDuoRowRealtime($row); }, 200);
            } else {
                $box.slideUp(150);
                $row.find('.duo-component-name, .duo-component-marks').removeClass('is-invalid');
                $row.find('.duo-name-error, .duo-marks-error').hide();
            }
        });

        $(document).on('click', '.add-duo-component-btn', function() {
            const $box = $(this).closest('.duo-components-box');
            $box.find('.duo-components-list').append(buildDuoComponentRowHtml('', 100));
        });

        $(document).on('click', '.remove-duo-component-btn', function() {
            const $list = $(this).closest('.duo-components-list');
            if ($list.find('.duo-component-row').length <= 2) {
                Swal.fire({
                    icon: 'info',
                    title: 'Duo Subject',
                    text: 'Duo subject needs at least 2 papers.',
                    confirmButtonColor: '#940000'
                });
                return;
            }
            $(this).closest('.duo-component-row').remove();
        });

        $('#edit_duo_toggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#edit_duo_components').slideDown(150);
                ensureDuoComponents($('#edit_duo_components'), 2);
            } else {
                $('#edit_duo_components').slideUp(150);
                $('#edit_duo_components').find('.duo-component-name, .duo-component-marks').removeClass('is-invalid');
                $('#edit_duo_components').find('.duo-name-error, .duo-marks-error').hide();
            }
        });

        $('#edit_add_duo_component_btn').on('click', function() {
            $('#edit_duo_components_list').append(buildDuoComponentRowHtml('', 100));
        });

        // Add Another School Subject Row
        let schoolSubjectRowCount = 0;
        $(document).on('click', '#addAnotherSchoolSubjectBtn', function() {
            schoolSubjectRowCount++;
            const rowIndex = schoolSubjectRowCount;
            const subjectNumber = schoolSubjectRowCount + 1;
            
            const newRow = `
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="${rowIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">${subjectNumber}</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subjects[${rowIndex}][subject_name]" class="form-control subject-name" placeholder="e.g., Mathematics, English, Physics" required maxlength="100">
                        <div class="field-error subject-name-error">Subject name is required</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input type="text" name="subjects[${rowIndex}][subject_code]" class="form-control subject-code" placeholder="e.g., MAT, ENG, PHY" maxlength="20">
                        <div class="field-error subject-code-error">Subject code already exists for this school</div>
                        <small class="text-muted">Must be unique within this school</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[${rowIndex}][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    ${duoSectionHtml(rowIndex)}
                </div>
            `;
            
            $('#schoolSubjectsContainer').append(newRow);
            
            // Update remove button visibility
            updateRemoveButtonsVisibility();
        });

        // Remove Subject Row (school subjects modal)
        $(document).on('click', '#schoolSubjectsContainer .remove-subject-row, #addSchoolSubjectModal .remove-subject-row', function() {
            $(this).closest('.subject-row').remove();
            updateSchoolSubjectNumbers();
            updateRemoveButtonsVisibility();
        });

        // Function to update school subject numbers
        function updateSchoolSubjectNumbers() {
            $('#schoolSubjectsContainer .subject-row').each(function(index) {
                $(this).find('.subject-number').text(index + 1);
                $(this).attr('data-row-index', index);
            });
        }

        // Legacy alias used by school-subject reset flows
        function updateSubjectNumbers() {
            updateSchoolSubjectNumbers();
        }

        // Function to update remove button visibility
        function updateRemoveButtonsVisibility() {
            const $rows = $('#schoolSubjectsContainer .subject-row');
            const rowCount = $rows.length;
            $rows.find('.remove-subject-row').each(function() {
                if (rowCount > 1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Handle Add School Subject Form Submission
        $(document).on('submit', '#addSchoolSubjectForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('create_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create subjects.'
                });
                return false;
            }

            // Collect all subjects data - scope to school subjects container only
            const subjects = [];
            let hasError = false;
            let errorMessage = '';

            $('#schoolSubjectsContainer .subject-row').each(function(index) {
                const $row = $(this);
                const subjectName = ($row.find('.subject-name').val() || '').trim();
                const subjectCode = ($row.find('.subject-code').val() || '').trim();
                const status = $row.find('.status-select').val() || 'Active';
                const isDuo = isSecondarySchool && $row.find('.duo-toggle').is(':checked');
                let components = [];

                // Validation
                if (!subjectName) {
                    hasError = true;
                    errorMessage = `Please enter subject name for Subject ${index + 1}.`;
                    $row.find('.subject-name').addClass('is-invalid');
                    return false;
                }

                if ($row.find('.subject-code').data('code-invalid') == 1) {
                    hasError = true;
                    errorMessage = `Subject ${index + 1}: Subject code already exists for this school.`;
                    $row.find('.subject-code').addClass('is-invalid');
                    return false;
                }

                // Client-side duplicate codes in the same form
                if (subjectCode) {
                    const lower = subjectCode.toLowerCase();
                    const seen = subjects.some(s => (s.subject_code || '').toLowerCase() === lower);
                    if (seen) {
                        hasError = true;
                        errorMessage = `Subject ${index + 1}: Subject code "${subjectCode}" is duplicated in this form.`;
                        $row.find('.subject-code').addClass('is-invalid');
                        return false;
                    }
                }

                if (isDuo) {
                    components = collectDuoComponentsFrom($row.find('.duo-components-box'));
                    if (components === null) {
                        hasError = true;
                        errorMessage = `Subject ${index + 1}: Marks cannot exceed 100 (and must be at least 1).`;
                        return false;
                    }
                    if (components.length < 2) {
                        hasError = true;
                        errorMessage = `Subject ${index + 1}: Duo subject needs at least 2 papers (name + max marks).`;
                        return false;
                    }
                }

                subjects.push({
                    subject_name: subjectName,
                    subject_code: subjectCode || null,
                    status: status,
                    is_duo: isDuo ? 1 : 0,
                    components: components
                });
            });

            console.log('Total school subjects collected:', subjects.length, subjects);

            if (hasError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessage,
                    confirmButtonColor: '#940000'
                });
                return false;
            }

            if (subjects.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please add at least one subject.'
                });
                return false;
            }

            console.log('Add School Subject Form submitted');

            var formData = {
                subjects: subjects,
                _token: $('input[name="_token"]', '#addSchoolSubjectForm').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_school_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || formData._token
                },
                success: function(response) {
                    console.log('Success Response:', response);

                    if (response && response.success) {
                        $('#addSchoolSubjectModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(function() {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        xhr: xhr,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMsg += '- ' + errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || xhr.status === 500) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle Edit School Subject Button Click
        $(document).on('click', '.edit-school-subject-btn', function() {
            const subjectID = $(this).data('subject-id');
            const subjectName = $(this).data('subject-name');
            const subjectCode = $(this).data('subject-code') || '';
            const isDuo = String($(this).data('is-duo')) === '1';
            let components = $(this).data('components') || [];
            if (typeof components === 'string') {
                try { components = JSON.parse(components); } catch (e) { components = []; }
            }

            $('#edit_subjectID').val(subjectID);
            $('#edit_subject_name').val(subjectName);
            $('#edit_subject_code').val(subjectCode).removeClass('is-invalid is-valid').removeData('code-invalid');
            $('#edit_subject_code_error').hide();
            
            // Get current status from the activate button
            const currentStatus = $(this).closest('.card').find('.activate-subject-btn').data('current-status');
            $('#edit_status').val(currentStatus);

            if (isSecondarySchool) {
                $('#edit_duo_components_list').empty();
                $('#edit_duo_toggle').prop('checked', isDuo);
                if (isDuo) {
                    $('#edit_duo_components').show();
                    if (components.length) {
                        components.forEach(function(c) {
                            $('#edit_duo_components_list').append(
                                buildDuoComponentRowHtml(c.component_name || '', c.max_marks || 100)
                            );
                        });
                    }
                    ensureDuoComponents($('#edit_duo_components'), 2);
                } else {
                    $('#edit_duo_components').hide();
                }
            }

            $('#editSchoolSubjectModal').modal('show');
        });

        // Handle Edit School Subject Form Submission
        $(document).on('submit', '#editSchoolSubjectForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('edit_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to edit subjects.'
                });
                return false;
            }

            const isDuo = isSecondarySchool && $('#edit_duo_toggle').is(':checked');
            let components = [];
            if (isDuo) {
                components = collectDuoComponentsFrom($('#edit_duo_components'));
                if (components === null) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Marks cannot exceed 100 (and must be at least 1).',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }
                if (components.length < 2) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Duo subject needs at least 2 papers (name + max marks).',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }
            }

            const formData = {
                subjectID: $('#edit_subjectID').val(),
                subject_name: $('#edit_subject_name').val(),
                subject_code: $('#edit_subject_code').val(),
                status: $('#edit_status').val(),
                is_duo: isDuo ? 1 : 0,
                components: components,
                _token: $('input[name="_token"]', '#editSchoolSubjectForm').val()
            };

            if ($('#edit_subject_code').data('code-invalid') == 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Subject code already exists for this school.',
                    confirmButtonColor: '#940000'
                });
                return false;
            }

            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_school_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || formData._token
                },
                success: function(response) {
                    if (response && response.success) {
                        $('#editSchoolSubjectModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(function() {
                            setTimeout(function() {
                                location.reload();
                            }, 100);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : (xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Something went wrong. Please try again.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Handle Delete School Subject Button Click
        $(document).on('click', '.delete-school-subject-btn', function() {
            const subjectID = $(this).data('subject-id');
            const subjectName = $(this).data('subject-name');

            Swal.fire({
                title: 'Delete Subject?',
                html: 'Are you sure you want to delete <strong>' + subjectName + '</strong>?<br><br>This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        html: 'Please wait while we delete the subject.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Get CSRF token
                    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
                    
                    if (!csrfToken) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'CSRF token not found. Please refresh the page and try again.',
                            confirmButtonColor: '#940000'
                        });
                        return;
                    }
                    
                    $.ajax({
                        url: "{{ route('delete_school_subject', ':id') }}".replace(':id', subjectID),
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        data: {
                            _token: csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.success,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.error || 'Failed to delete subject',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('Delete error:', xhr);
                            let errorMsg = 'Failed to delete subject';
                            
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                } else if (xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                            } else if (xhr.responseText) {
                                try {
                                    const parsed = JSON.parse(xhr.responseText);
                                    errorMsg = parsed.error || parsed.message || errorMsg;
                                } catch (e) {
                                    errorMsg = xhr.responseText || errorMsg;
                                }
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg,
                                confirmButtonColor: '#940000',
                                width: '600px'
                            });
                        }
                    });
                }
            });
        });

        // Handle Subclass Selection Change, load combie defaults / skip already assigned
        $(document).on('change', '#class_subject_subclass_select', function() {
            const subclassID = $(this).val();

            if (!subclassID) {
                $('#subclassSelectedAlert').hide();
                $('#subclassSelectedAlertText').text('Subjects already added to this subclass stay as they are. Missing combie subjects load below, assign a teacher for each.');
                addedSubjectIDsCache = [];
                resetClassSubjectsContainer(true);
                return;
            }

            $('#subclassSelectedAlert').show();
            $('#subclassSelectedAlertText').html('<i class="bi bi-hourglass-split"></i> Loading subclass subjects...');

            $.ajax({
                url: "{{ route('get_subclass_subject_defaults', ':id') }}".replace(':id', subclassID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        addedSubjectIDsCache = [];
                        resetClassSubjectsContainer(true);
                        $('#subclassSelectedAlertText').text(response.error || 'Could not load subclass defaults.');
                        return;
                    }

                    addedSubjectIDsCache = (response.assigned_subject_ids || []).map(String);

                    const missing = response.missing_subjects || [];
                    const alreadyCombie = response.already_assigned_combie_subjects || [];
                    const hasCombieSubjects = !!response.has_combie_subjects;
                    const combieName = response.combie
                        ? (response.combie.combie_name + (response.combie.combie_code ? ' (' + response.combie.combie_code + ')' : ''))
                        : '';

                    if (hasCombieSubjects) {
                        let noteParts = [];
                        if (combieName) {
                            noteParts.push('<strong>Combie:</strong> ' + escapeHtml(combieName));
                        }
                        if (alreadyCombie.length) {
                            noteParts.push(
                                '<strong>Already assigned:</strong> ' +
                                alreadyCombie.map(s => escapeHtml(s.subject_name)).join(', ') +
                                ' (left as-is).'
                            );
                        }
                        if (missing.length) {
                            noteParts.push(
                                '<strong>To assign now:</strong> ' +
                                missing.map(s => escapeHtml(s.subject_name)).join(', ') +
                                ', select a teacher for each.'
                            );
                        } else {
                            noteParts.push('All combie main subjects are already on this subclass.');
                        }
                        renderMissingCombieSubjects(missing, noteParts.join('<br>'));
                    } else {
                        resetClassSubjectsContainer(true);
                        $('#classSubjectsContainer .subject-select').each(function() {
                            disableAlreadyAddedSubjects($(this), addedSubjectIDsCache);
                        });
                        initClassSubjectFormSelect2();
                        $('#subclassSelectedAlertText').html(
                            addedSubjectIDsCache.length
                                ? 'No combie main subjects on this subclass. Already-added subjects are disabled in the subject list.'
                                : 'No combie main subjects assigned. Pick subjects and teachers manually.'
                        );
                    }
                },
                error: function() {
                    console.error('Failed to fetch subclass subject defaults');
                    addedSubjectIDsCache = [];
                    resetClassSubjectsContainer(true);
                    $('#subclassSelectedAlertText').text('Failed to load subclass defaults. You can still add subjects manually.');
                }
            });
        });

        // Function to disable already added subjects in a select element
        function disableAlreadyAddedSubjects($selectElement, addedSubjectIDs) {
            $selectElement.find('option').each(function() {
                const optionValue = $(this).val();
                if (optionValue && optionValue !== '') {
                    if (addedSubjectIDs.includes(optionValue)) {
                        $(this).prop('disabled', true)
                            .css({
                                'background-color': '#e9ecef',
                                'color': '#6c757d'
                            });

                        let optionText = $(this).text();
                        if (optionText.indexOf('(Already Added)') === -1) {
                            $(this).text(optionText + ' (Already Added)');
                        }
                    } else {
                        $(this).prop('disabled', false)
                            .css({
                                'background-color': '',
                                'color': ''
                            });

                        let optionText = $(this).text();
                        if (optionText.indexOf('(Already Added)') !== -1) {
                            $(this).text(optionText.replace(' (Already Added)', ''));
                        }
                    }
                }
            });
        }

        // Add Another Subject Row
        $(document).on('click', '#addAnotherSubjectBtn', function() {
            // If container only has the "all assigned" alert, clear it first
            if ($('#classSubjectsContainer .subject-row').length === 0) {
                subjectRowCount = -1;
            }
            subjectRowCount++;
            const rowIndex = subjectRowCount;

            const newRow = buildClassSubjectRowHtml(rowIndex, {
                locked: false,
                canRemove: true,
                subjectNumber: $('#classSubjectsContainer .subject-row').length + 1
            });

            // Remove empty-state alert if present
            $('#classSubjectsContainer > .alert').remove();
            $('#classSubjectsContainer').append(newRow);
            updateClassSubjectNumbers();
            const $newSelects = $('#classSubjectsContainer .subject-row').last();
            initSearchableSelect($newSelects.find('.subject-select'), 'Search subject...');
            initTeacherSearchSelect($newSelects.find('.teacher-select'));
            disableAlreadyAddedSubjects($newSelects.find('.subject-select'), addedSubjectIDsCache);
        });

        // Remove Subject Row (class subjects modal only)
        $(document).on('click', '#addClassSubjectModal .remove-subject-row', function() {
            const $row = $(this).closest('.subject-row');
            destroySelect2In($row);
            $row.remove();
            updateClassSubjectNumbers();
        });

        // Update Subject Numbers (class subjects)
        function updateClassSubjectNumbers() {
            const $rows = $('#classSubjectsContainer .subject-row');
            $rows.each(function(index) {
                $(this).find('.subject-number').text(index + 1);
                if ($rows.length > 1 && $(this).attr('data-locked') !== '1') {
                    $(this).find('.remove-subject-row').show();
                } else if ($(this).attr('data-locked') === '1') {
                    $(this).find('.remove-subject-row').hide();
                } else {
                    $(this).find('.remove-subject-row').hide();
                }
            });
        }

        // Init searchable selects when Add Class Subject modal opens
        $('#addClassSubjectModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                initClassSubjectSearchSelects();
            }, 50);
        });

        // Edit modal teacher search (main + duo papers)
        $('#editClassSubjectModal').on('shown.bs.modal', function() {
            setTimeout(function() {
                if ($('#editMainTeacherWrap').is(':visible')) {
                    initTeacherSearchSelect($('#edit_teacher_select'));
                }
                $('#editDuoPapersBox .edit-duo-component-teacher').each(function() {
                    initTeacherSearchSelect($(this));
                });
            }, 50);
        });

        $('#editClassSubjectModal').on('hidden.bs.modal', function() {
            destroySearchSelectsIn($(this));
            $('#editDuoPapersWrap').hide();
            $('#editDuoPapersBox').empty();
            $('#editMainTeacherWrap').show();
            $('#edit_is_duo').val('0');
            $('#editClassSubjectForm')[0].reset();
        });

        // Reset form when modal is closed
        $('#addClassSubjectModal').on('hidden.bs.modal', function() {
            destroySearchSelectsIn($(this));
            $('#addClassSubjectForm')[0].reset();
            $('#subclassSelectedAlert').hide();
            $('#subclassSelectedAlertText').text('Subjects already added to this subclass stay as they are. Missing combie subjects load below, assign a teacher for each.');
            addedSubjectIDsCache = [];
            resetClassSubjectsContainer(false);
            subjectRowCount = 0;
        });

        // Handle Add Class Subject Form Submission
        $(document).on('submit', '#addClassSubjectForm', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Add Class Subject Form submitted');

            if (!hasPermission('create_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to create class subjects.'
                });
                return false;
            }

            const subclassID = $('#class_subject_subclass_select').val();
            console.log('Subclass ID:', subclassID);
            
            if (!subclassID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a subclass first.'
                });
                return false;
            }

            // Collect all subjects data - scope to class subjects container only
            const subjects = [];
            let hasError = false;
            let errorMessage = '';

            $('#classSubjectsContainer .subject-row').each(function(index) {
                const $row = $(this);
                const subjectID = $row.find('.subject-select').val()
                    || $row.find('input[name*="[subjectID]"]').val()
                    || $row.find('.subject-select-value').val()
                    || '';
                const status = $row.find('.status-select').val() || 'Active';
                const studentStatus = $row.find('.student-status-select').val() || 'Required';
                const isDuo = $row.find('.duo-papers-wrap').length > 0
                    || $row.attr('data-is-duo') === '1';

                if (!subjectID) {
                    hasError = true;
                    errorMessage = `Please select a subject for Subject ${index + 1}.`;
                    return false;
                }

                const payload = {
                    subjectID: subjectID,
                    teacherID: null,
                    status: status,
                    student_status: studentStatus
                };

                if (isDuo) {
                    const components = [];
                    $row.find('.duo-paper-row').each(function() {
                        const componentID = $(this).find('.duo-component-id').val()
                            || $(this).attr('data-component-id');
                        const teacherID = $(this).find('.duo-component-teacher').val() || null;
                        if (componentID) {
                            components.push({
                                componentID: componentID,
                                teacherID: teacherID || null
                            });
                        }
                    });
                    if (!components.length) {
                        hasError = true;
                        errorMessage = `Duo subject ${index + 1} has no papers. Re-select the subject.`;
                        return false;
                    }
                    payload.components = components;
                    payload.teacherID = null;
                } else {
                    payload.teacherID = $row.find('.main-teacher-select').val()
                        || $row.find('.teacher-select').not('.duo-component-teacher').val()
                        || null;
                }

                subjects.push(payload);
            });

            console.log('Total subjects collected:', subjects.length, subjects);

            if (hasError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessage
                });
                return false;
            }

            if (subjects.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please add at least one subject.'
                });
                return false;
            }

            console.log('Add Class Subject Form submitted');

            var formData = {
                subclassID: subclassID,
                subjects: subjects,
                _token: $('input[name="_token"]', '#addClassSubjectForm').val()
            };

            console.log('Form Data:', formData);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_class_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || formData._token
                },
                success: function(response) {
                    console.log('Success Response:', response);

                    if (response && response.success) {
                        $('#addClassSubjectModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        xhr: xhr,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMsg += '- ' + errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else if (xhr.status === 0 || xhr.status === 500) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection or try again.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle View Class Subjects Button Click
        $(document).on('click', '#viewClassSubjectsBtn', function(e) {
            e.preventDefault();

            if (!hasPermission('view_class_subjects')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to view class subjects.'
                });
                return false;
            }

            console.log('View Class Subjects clicked');

            $('#viewClassSubjectsModal').modal('show');
            $('#classSubjectsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading class subjects...</p></div>');

            $.ajax({
                url: "{{ route('get_class_subjects') }}",
                type: "GET",
                success: function(response) {
                    console.log('Class subjects data received:', response);

                    if (response.success && response.subclasses && response.subclasses.length > 0) {
                        var html = '<div class="row">';

                        response.subclasses.forEach(function(subclass) {
                            html += '<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">';
                            html += '<div class="card shadow-sm h-100">';

                            // Card Header
                            var subclassDisplayName = subclass.display_name || subclass.stream_code || (subclass.class_name + ' ' + subclass.subclass_name);
                            html += '<div class="card-header bg-primary-custom text-white text-center">';
                            html += '<div class="d-flex justify-content-center align-items-center mb-2">';
                            html += '<i class="bi bi-mortarboard-fill" style="font-size: 2rem;"></i>';
                            html += '</div>';
                            html += '<h6 class="mb-0 fw-bold text-white subclass-name" data-subclass-name="' + subclassDisplayName.toLowerCase() + '">' + subclassDisplayName + '</h6>';
                            html += '</div>';

                            // Card Body
                            html += '<div class="card-body">';

                            // Subject Count - Using Bootstrap utilities
                            html += '<div class="row mb-3">';
                            html += '<div class="col-12">';
                            html += '<div class="text-center p-3 bg-light rounded border border-primary-custom">';
                            html += '<i class="bi bi-book-fill text-primary-custom d-block mb-2" style="font-size: 1.5rem;"></i>';
                            html += '<div class="h4 mb-1 fw-bold">' + subclass.subject_count + '</div>';
                            html += '<small class="text-muted fw-bold">Subjects</small>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';

                            // Hidden Details Section (View More)
                            html += '<div class="subclass-subjects-details-' + (subclass.subclassID || subclass.id) + '" style="display: none;">';

                            // Subjects List - Using Bootstrap utilities
                            if (subclass.subjects && subclass.subjects.length > 0) {
                                html += '<div class="mt-3">';
                                html += '<div class="d-flex align-items-center mb-2">';
                                html += '<i class="bi bi-list-ul text-primary-custom me-2"></i>';
                                html += '<strong class="small">Subject List:</strong>';
                                html += '</div>';
                                html += '<div class="list-group">';
                                subclass.subjects.forEach(function(subject) {
                                    html += '<div class="list-group-item">';
                                    html += '<div class="row g-2 align-items-center">';
                                    
                                    // Subject Info Column
                                    html += '<div class="col-12 col-md-8">';
                                    html += '<div class="d-flex align-items-start">';
                                    html += '<i class="bi bi-book-fill text-primary-custom me-2 mt-1"></i>';
                                    html += '<div class="flex-grow-1">';
                                    html += '<div class="d-flex flex-wrap align-items-center mb-1">';
                                    html += '<div class="fw-bold">' + subject.subject_name + '</div>';
                                    // Student Status Badge
                                    if (subject.student_status) {
                                        var statusBadgeClass = subject.student_status === 'Required' ? 'bg-warning text-dark' : 'bg-info text-white';
                                        html += '<span class="badge ' + statusBadgeClass + ' ms-2">' + subject.student_status + '</span>';
                                    }
                                    html += '</div>';
                                    // Election Stats for Optional subjects
                                    if (subject.student_status === 'Optional') {
                                        var electedCount = subject.elected_count || 0;
                                        var nonElectedCount = subject.non_elected_count || 0;
                                        var totalStudents = subject.total_students || 0;
                                        html += '<div class="mt-1 mb-2">';
                                        html += '<small class="text-muted">';
                                        html += '<span class="badge bg-success me-1">Elected: ' + electedCount + '</span>';
                                        html += '<span class="badge bg-secondary">Not Elected: ' + nonElectedCount + '</span>';
                                        html += ' <span class="text-muted">(Total: ' + totalStudents + ')</span>';
                                        html += '</small>';
                                        html += '</div>';
                                    }
                                    if (subject.subject_code) {
                                        html += '<div class="mb-1">';
                                        html += '<small class="text-muted d-block">';
                                        html += '<i class="bi bi-code-square text-primary-custom me-1"></i>Code: ' + subject.subject_code;
                                        html += '</small>';
                                        html += '</div>';
                                    }
                                    if (subject.is_duo && subject.papers && subject.papers.length) {
                                        html += '<div class="mt-1">';
                                        html += '<span class="badge badge-danger mr-1 mb-1" style="border-radius:0;background:#940000;">DUO</span>';
                                        subject.papers.forEach(function(paper) {
                                            html += '<div class="small text-muted">';
                                            html += '<i class="bi bi-layers text-primary-custom me-1"></i>';
                                            html += '<strong>' + paper.component_name + '</strong>: ';
                                            html += (paper.teacher_name || 'Not Assigned');
                                            html += '</div>';
                                        });
                                        html += '</div>';
                                    } else if (subject.teacher_name && subject.teacher_name !== 'Not Assigned') {
                                        html += '<div>';
                                        html += '<small class="text-muted d-block">';
                                        html += '<i class="bi bi-person-badge text-info me-1"></i>Teacher: ' + subject.teacher_name;
                                        html += '</small>';
                                        html += '</div>';
                                    }
                                    html += '</div>'; // End flex-grow-1
                                    html += '</div>'; // End d-flex
                                    html += '</div>'; // End col-md-8

                                    // Action Buttons Column
                                    html += '<div class="col-12 col-md-4">';
                                    html += '<div class="d-flex flex-row flex-wrap gap-2 justify-content-md-end w-100">';
                                    // Election button for optional subjects
                                    if (subject.student_status === 'Optional') {
                                        html += '<button class="btn btn-sm btn-primary text-white election-subject-btn flex-fill" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'data-subclass-id="' + (subclass.subclassID || subclass.id) + '" ';
                                        html += 'title="Manage Subject Election">';
                                        html += '<i class="bi bi-person-check"></i> Election';
                                        html += '</button>';
                                    }
                                    html += '<div class="d-flex flex-wrap gap-1 flex-fill">';
                                    var canEdit = hasPermission('update_class_subject');
                                    var canDelete = hasPermission('delete_class_subject');
                                    var canActivate = hasPermission('activate_class_subject');
                                    var subjectStatus = subject.status || 'Inactive';
                                    if (canActivate) {
                                        var activateBtnClass = subjectStatus === 'Active' ? 'btn-secondary' : 'btn-success';
                                        var activateIcon = subjectStatus === 'Active' ? 'x-circle' : 'check-circle';
                                        var activateTitle = subjectStatus === 'Active' ? 'Deactivate' : 'Activate';
                                        html += '<button class="btn btn-sm ' + activateBtnClass + ' text-white activate-class-subject-btn flex-fill" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'data-current-status="' + subjectStatus + '" ';
                                        html += 'title="' + activateTitle + ' Subject">';
                                        html += '<i class="bi bi-' + activateIcon + '-fill"></i>';
                                        html += '</button>';
                                    }
                                    if (canEdit) {
                                        window.classSubjectEditCache = window.classSubjectEditCache || {};
                                        window.classSubjectEditCache[subject.class_subjectID] = {
                                            is_duo: !!subject.is_duo,
                                            papers: subject.papers || [],
                                            teacher_id: subject.teacher_id || null,
                                            subject_name: subject.subject_name
                                        };
                                        html += '<button class="btn btn-sm btn-warning text-dark edit-class-subject-btn flex-fill" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'data-teacher-id="' + (subject.teacher_id || '') + '" ';
                                        html += 'data-subclass-id="' + (subclass.subclassID || subclass.id) + '" ';
                                        html += 'data-is-duo="' + (subject.is_duo ? '1' : '0') + '" ';
                                        html += 'title="Edit Teacher">';
                                        html += '<i class="bi bi-pencil-square"></i>';
                                        html += '</button>';
                                    }
                                    if (canDelete) {
                                        html += '<button class="btn btn-sm btn-danger text-white delete-class-subject-btn flex-fill" ';
                                        html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                        html += 'data-subject-name="' + subject.subject_name + '" ';
                                        html += 'title="Delete Subject">';
                                        html += '<i class="bi bi-trash-fill"></i>';
                                        html += '</button>';
                                    }
                                    html += '</div>'; // End flex-wrap gap-1
                                    html += '</div>'; // End action d-flex
                                    html += '</div>'; // End action buttons column
                                    html += '</div>'; // End row
                                    html += '</div>'; // End list-group-item
                                });
                                html += '</div>';
                                html += '</div>';
                            } else {
                                html += '<div class="text-center py-3">';
                                html += '<i class="bi bi-inbox text-muted" style="font-size: 1.5rem;"></i>';
                                html += '<p class="mt-2 mb-0 small text-muted">No subjects assigned</p>';
                                html += '</div>';
                            }

                            html += '</div>'; // End hidden details
                            html += '</div>'; // End card-body

                            // Card Footer with View More Button
                            html += '<div class="card-footer bg-white">';
                            html += '<button class="btn btn-sm btn-outline-primary view-more-subject-btn w-100" data-subclass-id="' + (subclass.subclassID || subclass.id) + '" data-expanded="false">';
                            html += '<i class="bi bi-chevron-down"></i> View More';
                            html += '</button>';
                            html += '</div>'; // End card-footer

                            html += '</div>'; // End card
                            html += '</div>'; // End col
                        });

                        html += '</div>'; // End row
                        $('#classSubjectsContent').html(html);
                        
                        // Show search box
                        $('#classSubjectsSearchContainer').show();
                        
                        // Initialize search functionality
                        initializeClassSubjectsSearch();
                    } else {
                        $('#classSubjectsContent').html(
                            '<div class="text-center py-5">' +
                            '<i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="mt-3 mb-0 text-muted">No class subjects found. Click "Add Class Subject" to assign subjects to subclasses.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching class subjects:', xhr);
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Failed to load class subjects. Please try again.';

                    $('#classSubjectsContent').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> ' + errorMsg +
                        '</div>'
                    );
                }
            });
        });


        
        // Clear search when modal is closed
        $('#viewClassSubjectsModal').on('hidden.bs.modal', function() {
            $('#classSubjectsSearchInput').val('');
            $('#classSubjectsSearchContainer').hide();
            $('#classSubjectsContent').find('.no-results-message').remove();
            // Clear content to free memory
            $('#classSubjectsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading class subjects...</p></div>');
        });

        // Handle close button click
        $(document).on('click', '#closeViewClassSubjectsModal', function() {
            $('#viewClassSubjectsModal').modal('hide');
        });

        // Handle View More Button Click (Class Subjects) - Open Modal
        $(document).on('click', '.view-more-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var subclassID = $(this).data('subclass-id');
            var $card = $(this).closest('.card');
            var subclassName = $card.find('.subclass-name').text() || 'Subclass';

            console.log('View More clicked for subclassID:', subclassID, 'subclassName:', subclassName);

            // Set modal title
            $('#subclassModalTitle').html('<i class="bi bi-mortarboard-fill"></i> ' + subclassName);

            // Show loading
            $('#subclassSubjectsModalBody').html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border text-primary-custom" role="status">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<p class="mt-2">Loading subjects...</p>' +
                '</div>'
            );

            // Close the Class Subjects modal first
            $('#viewClassSubjectsModal').modal('hide');

            // Open modal
            $('#viewSubclassSubjectsModal').modal('show');

            // Find the details section to get subjects data
            var $details = $('.subclass-subjects-details-' + subclassID);
            if ($details.length === 0) {
                $details = $card.find('[class*="subclass-subjects-details"]');
            }

            if ($details.length > 0) {
                // Get the list-group content from the hidden section
                var $listGroup = $details.find('.list-group');
                if ($listGroup.length > 0) {
                    // Clone the list-group items
                    var subjectsHtml = $listGroup.html();
                    $('#subclassSubjectsModalBody').html(
                        '<div class="mt-3">' +
                        '<div class="d-flex align-items-center mb-3">' +
                        '<i class="bi bi-list-ul text-primary-custom me-2"></i>' +
                        '<strong>Subject List:</strong>' +
                        '</div>' +
                        '<div class="list-group">' +
                        subjectsHtml +
                        '</div>' +
                        '</div>'
                    );
                } else {
                    // If no subjects, show message from hidden section
                    var noSubjectsMsg = $details.find('.text-center').html() || 
                        '<div class="text-center py-3">' +
                        '<i class="bi bi-inbox text-muted" style="font-size: 1.5rem;"></i>' +
                        '<p class="mt-2 mb-0 small text-muted">No subjects assigned</p>' +
                        '</div>';
                    $('#subclassSubjectsModalBody').html(noSubjectsMsg);
                }
            } else {
                // If not found, show message
                $('#subclassSubjectsModalBody').html(
                    '<div class="alert alert-info text-center">' +
                    '<i class="bi bi-info-circle"></i> No subjects found for this subclass.' +
                    '</div>'
                );
            }
        });

        // Refresh view class subjects modal after actions in subclass modal
        $('#viewSubclassSubjectsModal').on('hidden.bs.modal', function() {
            // Refresh the main view class subjects modal if it's open
            if ($('#viewClassSubjectsModal').hasClass('show')) {
                $('#viewClassSubjectsBtn').click();
            }
        });

        // Handle Election Button Click
        $(document).on('click', '.election-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var subclassID = $(this).data('subclass-id');

            console.log('Election clicked for classSubjectID:', classSubjectID, 'subjectName:', subjectName);

            // Set modal title
            $('#electionSubjectTitle').text(subjectName + ' Election');

            // Show loading
            $('#electionStudentsContainer').html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border text-primary-custom" role="status">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<p class="mt-2">Loading students...</p>' +
                '</div>'
            );

            // Store classSubjectID for save button
            $('#saveElectionBtn').data('class-subject-id', classSubjectID);
            $('#saveElectionBtn').data('subclass-id', subclassID);

            // Close Subclass Subjects modal
            $('#viewSubclassSubjectsModal').modal('hide');

            // Open modal
            $('#subjectElectionModal').modal('show');

            // Fetch students for this subclass
            $.ajax({
                url: "{{ route('get_subclass_students', ':id') }}".replace(':id', subclassID),
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    console.log('Students data received:', response);

                    if (response.success && response.students && response.students.length > 0) {
                        // Fetch already elected students
                        var electorsUrl = "{{ url('get_subject_electors') }}/" + classSubjectID;
                        console.log('Fetching electors from:', electorsUrl);
                        
                        $.ajax({
                            url: electorsUrl,
                            type: "GET",
                            dataType: 'json',
                            success: function(electorsResponse) {
                                console.log('Electors data received:', electorsResponse);
                                
                                var electedStudentIDs = [];
                                if (electorsResponse.success && electorsResponse.electors) {
                                    electedStudentIDs = electorsResponse.electors.map(function(e) {
                                        return e.studentID;
                                    });
                                }

                                // Build table
                                var html = '<div class="table-responsive">';
                                html += '<table class="table table-hover table-bordered" id="electionStudentsTable">';
                                html += '<thead class="bg-primary-custom text-white">';
                                html += '<tr>';
                                html += '<th style="width: 50px;">#</th>';
                                html += '<th>Student Name</th>';
                                html += '<th>Admission Number</th>';
                                html += '<th style="width: 100px; text-align: center;">Election</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                response.students.forEach(function(student, index) {
                                    var sid = String(student.studentID);
                                    var isElected = electedStudentIDs.some(function(id) {
                                        return String(id) === sid;
                                    });
                                    html += '<tr data-student-id="' + sid + '">';
                                    html += '<td>' + (index + 1) + '</td>';
                                    html += '<td>' + student.first_name + ' ' + student.last_name + '</td>';
                                    html += '<td>' + (student.admission_number || 'N/A') + '</td>';
                                    html += '<td style="text-align: center;">';
                                    html += '<input type="checkbox" class="form-check-input election-checkbox" ';
                                    html += 'data-student-id="' + sid + '" ';
                                    html += 'value="' + sid + '"';
                                    if (isElected) {
                                        html += ' checked';
                                    }
                                    html += '>';
                                    html += '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';

                                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#electionStudentsTable')) {
                                    $('#electionStudentsTable').DataTable().destroy();
                                }
                                $('#electionStudentsContainer').html(html);

                                // Initialize DataTable
                                if ($.fn.DataTable) {
                                    $('#electionStudentsTable').DataTable({
                                        "pageLength": 25,
                                        "order": [[1, "asc"]],
                                        "deferRender": false,
                                        "language": {
                                            "search": "Search students:"
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.error('Error fetching electors:', xhr);
                                console.error('Response:', xhr.responseJSON);
                                console.error('Status:', xhr.status);
                                
                                // If error, just continue with empty electors list (no electors yet)
                                var electedStudentIDs = [];
                                
                                // Build table
                                var html = '<div class="table-responsive">';
                                html += '<table class="table table-hover table-bordered" id="electionStudentsTable">';
                                html += '<thead class="bg-primary-custom text-white">';
                                html += '<tr>';
                                html += '<th style="width: 50px;">#</th>';
                                html += '<th>Student Name</th>';
                                html += '<th>Admission Number</th>';
                                html += '<th style="width: 100px; text-align: center;">Election</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                response.students.forEach(function(student, index) {
                                    var sid = String(student.studentID);
                                    html += '<tr data-student-id="' + sid + '">';
                                    html += '<td>' + (index + 1) + '</td>';
                                    html += '<td>' + student.first_name + ' ' + student.last_name + '</td>';
                                    html += '<td>' + (student.admission_number || 'N/A') + '</td>';
                                    html += '<td style="text-align: center;">';
                                    html += '<input type="checkbox" class="form-check-input election-checkbox" ';
                                    html += 'data-student-id="' + sid + '" ';
                                    html += 'value="' + sid + '">';
                                    html += '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';

                                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#electionStudentsTable')) {
                                    $('#electionStudentsTable').DataTable().destroy();
                                }
                                $('#electionStudentsContainer').html(html);

                                // Initialize DataTable
                                if ($.fn.DataTable) {
                                    $('#electionStudentsTable').DataTable({
                                        "pageLength": 25,
                                        "order": [[1, "asc"]],
                                        "deferRender": false,
                                        "language": {
                                            "search": "Search students:"
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        $('#electionStudentsContainer').html(
                            '<div class="alert alert-info text-center">' +
                            '<i class="bi bi-info-circle"></i> No students found in this subclass.' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching students:', xhr);
                    $('#electionStudentsContainer').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> Failed to load students. Please try again.' +
                        '</div>'
                    );
                }
            });
        });

        // Handle Save Election Button Click
        $(document).on('click', '#saveElectionBtn', function(e) {
            e.preventDefault();

            var classSubjectID = $(this).data('class-subject-id');
            var subclassID = $(this).data('subclass-id');

            if (!classSubjectID || !subclassID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Missing required data. Please try again.',
                    confirmButtonColor: '#940000'
                });
                return false;
            }

            // Collect selected students across ALL DataTable pages
            var selectedStudents = [];
            var seen = {};
            function pushStudentId(rawId) {
                if (rawId === undefined || rawId === null || rawId === '') return;
                var id = String(rawId);
                if (seen[id]) return;
                seen[id] = true;
                selectedStudents.push(id);
            }
            if ($.fn.DataTable.isDataTable('#electionStudentsTable')) {
                var table = $('#electionStudentsTable').DataTable();
                table.rows({ page: 'all' }).every(function() {
                    var $row = $(this.node());
                    var $checkbox = $row.find('.election-checkbox');
                    if ($checkbox.length && $checkbox.is(':checked')) {
                        pushStudentId($checkbox.val() || $checkbox.data('student-id') || $row.data('student-id'));
                    }
                });
            } else {
                $('#electionStudentsContainer .election-checkbox:checked').each(function() {
                    pushStudentId($(this).val() || $(this).data('student-id'));
                });
            }

            console.log('Saving election:', {
                classSubjectID: classSubjectID,
                subclassID: subclassID,
                selectedStudents: selectedStudents
            });

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_subject_election') }}",
                type: "POST",
                data: {
                    classSubjectID: classSubjectID,
                    subclassID: subclassID,
                    selectedStudents: selectedStudents,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);

                    if (response && response.success) {
                        $('#subjectElectionModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            // Refresh view class subjects modal if open
                            if ($('#viewClassSubjectsModal').hasClass('show')) {
                                $('#viewClassSubjectsBtn').click();
                            }
                        });
            } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            if (Array.isArray(errors[field])) {
                                errorMsg += '- ' + errors[field][0] + '\n';
                            } else {
                                errorMsg += '- ' + errors[field] + '\n';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle Deselect Student Button Click
        $(document).on('click', '.deselect-student-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var studentID = $btn.data('student-id');
            var classSubjectID = $btn.data('class-subject-id');
            var studentName = $btn.data('student-name');

            Swal.fire({
                title: 'Deselect Student?',
                html: 'Are you sure you want to deselect <strong>' + studentName + '</strong> from this subject election?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, deselect!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Removing...');

                    $.ajax({
                        url: "{{ route('deselect_student') }}",
                        type: "POST",
                        data: {
                            classSubjectID: classSubjectID,
                            studentID: studentID,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response && response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.success,
                                    confirmButtonColor: '#940000',
                                    timer: 1500
                                }).then(function() {
                                    // Replace deselect button with checkbox
                                    var $row = $btn.closest('tr');
                                    var $td = $row.find('td:last');
                                    $td.html(
                                        '<input type="checkbox" class="form-check-input election-checkbox" ' +
                                        'data-student-id="' + studentID + '" ' +
                                        'value="' + studentID + '">'
                                    );
                                    
                                    // Remove from DataTable if initialized
                                    if ($.fn.DataTable && $('#electionStudentsTable').hasClass('dataTable')) {
                                        var table = $('#electionStudentsTable').DataTable();
                                        table.draw(false); // Redraw without resetting paging
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Unexpected response format.',
                                    confirmButtonColor: '#940000'
                                });
                                $btn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html(originalText);

                            let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                ? xhr.responseJSON.error
                                : 'Something went wrong. Please try again.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg,
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                }
            });
        });

        // Handle Edit Class Subject Button Click
        $(document).on('click', '.edit-class-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('update_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to update class subjects.'
                });
                return false;
            }

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var teacherID = $(this).data('teacher-id');
            var subclassID = $(this).data('subclass-id');
            var cached = (window.classSubjectEditCache || {})[classSubjectID] || {};
            var isDuo = ($(this).data('is-duo') == 1 || $(this).data('is-duo') === true || !!cached.is_duo);
            var papers = cached.papers || [];

            destroySearchSelectsIn($('#editClassSubjectModal'));

            $('#edit_class_subject_id').val(classSubjectID);
            $('#edit_subclass_id').val(subclassID);
            $('#edit_subject_name').val(subjectName);
            $('#edit_is_duo').val(isDuo ? '1' : '0');

            if (isDuo && papers.length) {
                $('#editMainTeacherWrap').hide();
                $('#edit_teacher_select').val('');
                $('#edit_subject_hint').text('DUO subject, assign a teacher for each paper below');
                var papersHtml = '';
                papers.forEach(function(paper, i) {
                    papersHtml += `
                        <div class="duo-paper-row" data-component-id="${escapeHtml(paper.componentID)}">
                            <div class="duo-paper-name">
                                <label class="form-label fw-bold mb-1">Paper ${i + 1}</label>
                                <input type="text" class="form-control class-subject-locked-label" value="${escapeHtml(paper.component_name)}${paper.max_marks ? ' (' + escapeHtml(paper.max_marks) + ' marks)' : ''}" readonly tabindex="-1">
                                <input type="hidden" class="edit-duo-component-id" value="${escapeHtml(paper.componentID)}">
                            </div>
                            <div class="duo-paper-teacher">
                                <label class="form-label fw-bold mb-1">Teacher <span class="text-muted">(paper)</span></label>
                                <select class="form-select teacher-select edit-duo-component-teacher" data-placeholder="Search / select teacher...">
                                    ${buildTeacherOptionsHtml(paper.teacherID || '')}
                                </select>
                                <small class="text-muted"><i class="bi bi-search"></i> Type to search</small>
                            </div>
                        </div>`;
                });
                $('#editDuoPapersBox').html(papersHtml);
                $('#editDuoPapersWrap').show();
            } else {
                $('#editDuoPapersWrap').hide();
                $('#editDuoPapersBox').empty();
                $('#editMainTeacherWrap').show();
                $('#edit_subject_hint').text('Subject name cannot be changed');
                $('#edit_teacher_select').val(teacherID || cached.teacher_id || '');
            }

            // Close Subclass Subjects modal
            $('#viewSubclassSubjectsModal').modal('hide');

            // Show modal
            $('#editClassSubjectModal').modal('show');
        });

        // Handle Edit Class Subject Form Submission
        $(document).on('submit', '#editClassSubjectForm', function(e) {
            e.preventDefault();

            if (!hasPermission('update_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to update class subjects.'
                });
                return false;
            }

            var isDuo = $('#edit_is_duo').val() === '1';
            var formData = {
                class_subjectID: $('#edit_class_subject_id').val(),
                subclassID: $('#edit_subclass_id').val(),
                _token: $('input[name="_token"]', '#editClassSubjectForm').val()
            };

            if (isDuo) {
                var components = [];
                $('#editDuoPapersBox .duo-paper-row').each(function() {
                    var componentID = $(this).find('.edit-duo-component-id').val()
                        || $(this).attr('data-component-id');
                    var paperTeacherID = $(this).find('.edit-duo-component-teacher').val() || null;
                    if (componentID) {
                        components.push({
                            componentID: componentID,
                            teacherID: paperTeacherID || null
                        });
                    }
                });
                formData.components = components;
                formData.teacherID = null;
            } else {
                formData.teacherID = $('#edit_teacher_select').val() || null;
            }

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_class_subject') }}",
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Always re-enable button on success
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (response && response.success) {
                        $('#editClassSubjectModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#940000',
                            timer: 2000
                        }).then(function() {
                            // Refresh the view class subjects modal if it's open
                            if ($('#viewClassSubjectsModal').hasClass('show')) {
                            $('#viewClassSubjectsBtn').click();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unexpected response format.',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    // Always re-enable button on error
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                        let errorMsg = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMsg += '- ' + errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#940000'
                        });
                    }
                }
            });
        });

        // Handle Delete Class Subject Button Click
        $(document).on('click', '.delete-class-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('delete_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to delete class subjects.'
                });
                return false;
            }

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');

            // Close Subclass Modal if open
            $('#viewSubclassSubjectsModal').modal('hide');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove \"" + subjectName + "\" from this subclass? This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delete_class_subject', ':id') }}".replace(':id', classSubjectID),
                        type: "DELETE",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success || 'Subject removed successfully!',
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                // Refresh the view class subjects modal if it's open
                                if ($('#viewClassSubjectsModal').hasClass('show')) {
                                $('#viewClassSubjectsBtn').click();
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to remove subject. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Handle Activate Subject Button Click
        $(document).on('click', '.activate-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('activate_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to activate subjects.'
                });
                return false;
            }

            var subjectID = $(this).data('subject-id');
            var subjectName = $(this).data('subject-name');
            var currentStatus = $(this).data('current-status');
            var action = currentStatus === 'Active' ? 'deactivate' : 'activate';

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to ' + action + ' "' + subjectName + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + action + ' it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activate_subject', ':id') }}".replace(':id', subjectID),
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success,
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to update subject status. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Handle Activate Class Subject Button Click
        $(document).on('click', '.activate-class-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!hasPermission('activate_class_subject')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to activate class subjects.'
                });
                return false;
            }

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var currentStatus = $(this).data('current-status');
            var action = currentStatus === 'Active' ? 'deactivate' : 'activate';

            // Close Subclass Modal if open
            $('#viewSubclassSubjectsModal').modal('hide');

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to ' + action + ' "' + subjectName + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + action + ' it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activate_class_subject', ':id') }}".replace(':id', classSubjectID),
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success,
                                confirmButtonColor: '#940000',
                                timer: 2000
                            }).then(function() {
                                // Refresh the view class subjects modal if it's open
                                if ($('#viewClassSubjectsModal').hasClass('show')) {
                                $('#viewClassSubjectsBtn').click();
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to update class subject status. Please try again.'
                            });
                        }
                    });
                }
            });
        });

        // Reset forms when modals are closed
        // Function to reset school subject form
        function resetSchoolSubjectForm() {
            $('#addSchoolSubjectForm')[0].reset();
            $('#schoolSubjectsContainer').html(`
                <div class="subject-row mb-4 p-3 border rounded" data-row-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary-custom">
                            <i class="bi bi-book"></i> Subject <span class="subject-number">1</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger remove-subject-row" style="display: none;">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subjects[0][subject_name]" class="form-control subject-name" placeholder="e.g., Mathematics, English, Kiswahili" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input type="text" name="subjects[0][subject_code]" class="form-control subject-code" placeholder="e.g., MAT, ENG, KIS" maxlength="20">
                        <div class="field-error subject-code-error">Subject code already exists for this school</div>
                        <small class="text-muted">Must be unique within this school</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="subjects[0][status]" class="form-select status-select" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            `);
            schoolSubjectRowCount = 0;
            $('.is-invalid').removeClass('is-invalid');
        }

        $('#addSchoolSubjectModal').on('hidden.bs.modal', function() {
            resetSchoolSubjectForm();
        });

        $('#addClassSubjectModal').on('hidden.bs.modal', function() {
            $('#addClassSubjectForm')[0].reset();
        });

        $('#editClassSubjectModal').on('hidden.bs.modal', function() {
            $('#editClassSubjectForm')[0].reset();
        });

        // Alternative Bootstrap 4 event handlers
        $('#addSchoolSubjectModal').on('hidden', function() {
            resetSchoolSubjectForm();
        });

        $('#addClassSubjectModal').on('hidden', function() {
            $('#addClassSubjectForm')[0].reset();
        });

        $('#editClassSubjectModal').on('hidden', function() {
            $('#editClassSubjectForm')[0].reset();
        });

        // Select All Button for Admin Election
        $(document).on('click', '#adminElectionSelectAllBtn', function() {
            if ($.fn.DataTable.isDataTable('#electionStudentsTable')) {
                var table = $('#electionStudentsTable').DataTable();
                table.rows({ page: 'all' }).every(function() {
                    $(this.node()).find('.election-checkbox').prop('checked', true);
                });
            } else {
                $('#electionStudentsContainer .election-checkbox').prop('checked', true);
            }

            Swal.fire({
                icon: 'success',
                title: 'Selected All',
                text: 'All students have been selected. You can uncheck individuals if needed.',
                timer: 1500,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        });

        // Deselect All Button for Admin Election
        $(document).on('click', '#adminElectionDeselectAllBtn', function() {
            if ($.fn.DataTable.isDataTable('#electionStudentsTable')) {
                var table = $('#electionStudentsTable').DataTable();
                table.rows({ page: 'all' }).every(function() {
                    $(this.node()).find('.election-checkbox').prop('checked', false);
                });
            } else {
                $('#electionStudentsContainer .election-checkbox').prop('checked', false);
            }

            Swal.fire({
                icon: 'info',
                title: 'Deselected All',
                text: 'All checkboxes have been cleared.',
                timer: 1500,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        });
        });
    })(jQuery);
</script>
