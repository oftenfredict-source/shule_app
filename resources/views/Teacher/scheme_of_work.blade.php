@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.scheme_of_work').' - ShuleXpert')
@section('page_title', __('teacher.pages.scheme_of_work'))
@section('page_breadcrumb', __('teacher.pages.scheme_of_work_bc'))

@push('styles')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-scheme,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-scheme p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-scheme small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-scheme strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-scheme label {
        color: #000 !important;
    }
    .sx-teacher-scheme .sx-scheme-toolbar,
    .sx-teacher-scheme .sx-scheme-actions {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
    }
    .sx-teacher-scheme .sx-scheme-toolbar {
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-scheme .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-scheme .form-hint {
        display: block;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        color: #666 !important;
    }
    .sx-teacher-scheme .form-select,
    .sx-teacher-scheme .form-control,
    .sx-teacher-scheme .btn {
        border-radius: 0 !important;
    }
    .sx-teacher-scheme .form-select {
        min-height: 46px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    .sx-teacher-scheme .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12);
    }
    .sx-teacher-scheme .sx-scheme-actions {
        display: none;
        padding: 1rem 1.1rem;
    }
    .sx-teacher-scheme .sx-scheme-actions.is-open {
        display: block;
    }
    .sx-teacher-scheme .sx-scheme-actions-title {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        margin: 0 0 0.85rem;
    }
    .sx-teacher-scheme .sx-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.55rem;
    }
    .sx-teacher-scheme .sx-action-btn {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.35rem;
        min-height: 96px;
        padding: 0.85rem 0.95rem;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        background: #fff;
        color: #111;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .sx-teacher-scheme .sx-action-btn:hover {
        background: #fcf7f7;
        border-color: #940000;
    }
    .sx-teacher-scheme .sx-action-btn i {
        color: #940000;
        font-size: 1.15rem;
    }
    .sx-teacher-scheme .sx-action-btn .t {
        font-size: 0.88rem;
        font-weight: 800;
        color: #940000 !important;
    }
    .sx-teacher-scheme .sx-action-btn .d {
        font-size: 0.78rem;
        color: #666 !important;
        line-height: 1.35;
        font-weight: 600;
    }
    .sx-teacher-scheme .btn-primary-custom,
    .btn-primary-custom {
        background: #940000;
        border-color: #940000;
        color: #fff;
        font-weight: 800;
    }
    .sx-teacher-scheme .btn-primary-custom:hover,
    .btn-primary-custom:hover {
        background: #7b0000;
        border-color: #7b0000;
        color: #fff;
    }

    #createOptionsModal .modal-dialog { max-width: 560px; }
    #createOptionsModal .modal-content {
        border: none;
        border-radius: 0;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
    }
    #createOptionsModal .modal-header {
        border: none;
        padding: 1.1rem 1.35rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
    }
    #createOptionsModal .modal-title {
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 800;
        color: #fff !important;
    }
    #createOptionsModal .modal-body {
        background: #faf7f7;
        padding: 1.15rem 1.35rem 0.5rem;
    }
    #createOptionsModal .sx-create-lead {
        font-size: 0.86rem;
        font-weight: 600;
        color: #555 !important;
        margin: 0 0 0.9rem;
    }
    #createOptionsModal .sx-create-option {
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        padding: 1rem 1.05rem;
        height: 100%;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    #createOptionsModal .sx-create-option:hover {
        background: #fcf7f7;
        border-color: #940000;
    }
    #createOptionsModal .sx-create-option i {
        color: #940000;
        font-size: 1.4rem;
        margin-bottom: 0.45rem;
        display: block;
    }
    #createOptionsModal .sx-create-option .t {
        font-size: 0.88rem;
        font-weight: 800;
        color: #940000 !important;
        margin-bottom: 0.25rem;
    }
    #createOptionsModal .sx-create-option .d {
        font-size: 0.78rem;
        color: #666 !important;
        font-weight: 600;
        margin: 0;
    }
    #createOptionsModal .modal-footer {
        border-top: 1px solid rgba(148, 0, 0, 0.1);
        background: #fff;
        padding: 0.85rem 1.35rem 1.05rem;
    }
    #createOptionsModal .modal-footer .btn {
        border-radius: 0 !important;
        font-weight: 800;
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-scheme-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-scheme-toolbar,
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-scheme-actions,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-scheme-actions {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .form-label,
    html[data-bs-theme="dark"] .sx-teacher-scheme .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .form-hint,
    html[data-bs-theme="dark"] .sx-teacher-scheme .form-hint {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .form-select,
    html[data-bs-theme="dark"] .sx-teacher-scheme .form-select,
    html[data-sx-theme="dark"] .sx-teacher-scheme .form-control,
    html[data-bs-theme="dark"] .sx-teacher-scheme .form-control {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-scheme .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-scheme-actions-title,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-scheme-actions-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-action-btn,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-action-btn {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-action-btn:hover,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-action-btn:hover {
        background: #2a1c20 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-action-btn i,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-action-btn i,
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-action-btn .t,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-action-btn .t {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-scheme .sx-action-btn .d,
    html[data-bs-theme="dark"] .sx-teacher-scheme .sx-action-btn .d {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .modal-content,
    html[data-bs-theme="dark"] #createOptionsModal .modal-content {
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .modal-body,
    html[data-bs-theme="dark"] #createOptionsModal .modal-body {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .sx-create-lead,
    html[data-bs-theme="dark"] #createOptionsModal .sx-create-lead {
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .sx-create-option,
    html[data-bs-theme="dark"] #createOptionsModal .sx-create-option {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .sx-create-option:hover,
    html[data-bs-theme="dark"] #createOptionsModal .sx-create-option:hover {
        background: #2a1c20 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .sx-create-option i,
    html[data-bs-theme="dark"] #createOptionsModal .sx-create-option i,
    html[data-sx-theme="dark"] #createOptionsModal .sx-create-option .t,
    html[data-bs-theme="dark"] #createOptionsModal .sx-create-option .t {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .sx-create-option .d,
    html[data-bs-theme="dark"] #createOptionsModal .sx-create-option .d {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #createOptionsModal .modal-footer,
    html[data-bs-theme="dark"] #createOptionsModal .modal-footer {
        background: #24181c !important;
        border-top-color: #3a2a2e !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-scheme">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
        </div>
    @endif

    <div class="sx-scheme-toolbar">
        <label class="form-label" for="subjectSelect">{{ __('teacher.scheme.select_subject') }}</label>
        <select class="form-select" id="subjectSelect" name="subject_id">
            <option value="">{{ __('teacher.scheme.select_subject_opt') }}</option>
            @if(isset($teacherSubjects) && $teacherSubjects->count() > 0)
                @foreach($teacherSubjects as $classSubject)
                    @if($classSubject->subject && $classSubject->subject->status === 'Active')
                        <option value="{{ $classSubject->class_subjectID }}"
                                data-subject-name="{{ $classSubject->subject->subject_name }}"
                                data-subject-code="{{ $classSubject->subject->subject_code ?? '' }}">
                            {{ $classSubject->subject->subject_name }}
                            @if($classSubject->subject->subject_code)
                                ({{ $classSubject->subject->subject_code }})
                            @endif
                            @if($classSubject->subclass && $classSubject->subclass->class)
                                - {{ $classSubject->subclass->class->class_name }} {{ $classSubject->subclass->subclass_name }}
                            @elseif($classSubject->class)
                                - {{ $classSubject->class->class_name }}
                            @endif
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
        <span class="form-hint">{{ __('teacher.scheme.hint') }}</span>
    </div>

    <div class="sx-scheme-actions" id="widgetsContainer">
        <div class="sx-scheme-actions-title">{{ __('teacher.scheme.actions_title') }}</div>
        <div class="sx-action-grid">
            <button type="button" class="sx-action-btn" id="createWidget" onclick="handleCreate()">
                <i class="fa-solid fa-plus"></i>
                <span class="t">{{ __('teacher.scheme.create') }}</span>
                <span class="d">{{ __('teacher.scheme.create_d') }}</span>
            </button>
            <button type="button" class="sx-action-btn" id="viewWidget" onclick="handleView()">
                <i class="fa-solid fa-eye"></i>
                <span class="t">{{ __('teacher.scheme.view') }}</span>
                <span class="d">{{ __('teacher.scheme.view_d') }}</span>
            </button>
            <button type="button" class="sx-action-btn" id="manageWidget" onclick="handleManage()">
                <i class="fa-solid fa-gear"></i>
                <span class="t">{{ __('teacher.scheme.manage') }}</span>
                <span class="d">{{ __('teacher.scheme.manage_d') }}</span>
            </button>
        </div>
    </div>
</div>

{{-- Create Options Modal --}}
<div class="modal fade" id="createOptionsModal" tabindex="-1" aria-labelledby="createOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOptionsModalLabel">
                    <i class="fa-solid fa-plus me-1"></i> {{ __('teacher.scheme.create_title') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="sx-create-lead">{{ __('teacher.scheme.choose_how') }}</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="sx-create-option" onclick="createNewScheme()" role="button" tabindex="0">
                            <i class="fa-regular fa-file"></i>
                            <div class="t">{{ __('teacher.scheme.create_new') }}</div>
                            <p class="d">{{ __('teacher.scheme.create_new_d') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sx-create-option" onclick="useExistingScheme()" role="button" tabindex="0">
                            <i class="fa-regular fa-copy"></i>
                            <div class="t">{{ __('teacher.scheme.use_existing') }}</div>
                            <p class="d">{{ __('teacher.scheme.use_existing_d') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var i18nScheme = @json(__('teacher.scheme'));
window.sxShowModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = function(el) {
    var node = typeof el === 'string' ? document.querySelector(el) : (el && el.jquery ? el[0] : el);
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        var inst = bootstrap.Modal.getInstance(node) || bootstrap.Modal.getOrCreateInstance(node);
        inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};

(function () {
    let selectedSubjectId = null;
    let selectedSubjectName = null;

    const subjectSelect = document.getElementById('subjectSelect');
    const widgetsContainer = document.getElementById('widgetsContainer');

    subjectSelect.addEventListener('change', function () {
        selectedSubjectId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        selectedSubjectName = selectedOption.getAttribute('data-subject-name');
        widgetsContainer.classList.toggle('is-open', !!selectedSubjectId);
    });

    window.handleCreate = function () {
        if (!selectedSubjectId) {
            alert(i18nScheme.select_first);
            return;
        }
        sxShowModal('#createOptionsModal');
    };

    window.handleView = function () {
        if (!selectedSubjectId) {
            alert(i18nScheme.select_first);
            return;
        }
        const year = new Date().getFullYear();
        $.ajax({
            url: '{{ route("teacher.checkExistingScheme") }}',
            method: 'POST',
            data: {
                class_subjectID: selectedSubjectId,
                year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.exists && response.scheme) {
                    window.location.href = '/teacher/scheme-of-work/view/' + response.scheme.id;
                } else {
                    alert(i18nScheme.none_found);
                }
            },
            error: function () {
                alert(i18nScheme.check_error);
            }
        });
    };

    window.handleManage = function () {
        if (!selectedSubjectId) {
            alert(i18nScheme.select_first);
            return;
        }
        const year = new Date().getFullYear();
        $.ajax({
            url: '{{ route("teacher.checkExistingScheme") }}',
            method: 'POST',
            data: {
                class_subjectID: selectedSubjectId,
                year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.exists && response.scheme) {
                    window.location.href = '/teacher/scheme-of-work/manage/' + response.scheme.id;
                } else {
                    alert(i18nScheme.none_found);
                }
            },
            error: function () {
                alert(i18nScheme.check_error);
            }
        });
    };

    window.createNewScheme = function () {
        sxHideModal('#createOptionsModal');
        if (!selectedSubjectId) {
            alert(i18nScheme.select_first);
            return;
        }
        $.ajax({
            url: '{{ route("teacher.checkExistingScheme") }}',
            method: 'POST',
            data: {
                class_subjectID: selectedSubjectId,
                year: new Date().getFullYear(),
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.exists) {
                    if (confirm(i18nScheme.already_exists)) {
                        handleView();
                    }
                } else {
                    window.location.href = '/teacher/scheme-of-work/create/' + selectedSubjectId;
                }
            },
            error: function () {
                window.location.href = '/teacher/scheme-of-work/create/' + selectedSubjectId;
            }
        });
    };

    window.useExistingScheme = function () {
        if (!selectedSubjectId) {
            alert(i18nScheme.select_first);
            return;
        }
        sxHideModal('#createOptionsModal');
        window.location.href = '{{ route("teacher.useExistingSchemes", ":subjectId") }}'.replace(':subjectId', selectedSubjectId);
    };
})();
</script>
@endpush
