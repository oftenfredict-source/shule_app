@php
    $canView = ($user_type ?? '') == 'Admin' ||
        ($teacherPermissions ?? collect())->contains('examination_read_only') ||
        ($teacherPermissions ?? collect())->contains('examination_create') ||
        ($teacherPermissions ?? collect())->contains('examination_update') ||
        ($teacherPermissions ?? collect())->contains('examination_delete');

    $canViewExamPapers = ($user_type ?? '') == 'Admin' ||
        ($teacherPermissions ?? collect())->intersect([
            'view_exam_papers', 'examination_read_only', 'examination_create',
            'examination_update', 'examination_delete', 'manage_exam', 'view_exams'
        ])->isNotEmpty();

    $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_update');
    $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_delete');
@endphp

<div class="dropdown store-action-dropdown exam-action-dropdown">
    <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right shadow-sm">
        @if($canView)
        <button type="button" class="dropdown-item view-exam-more-btn" data-exam-id="{{ $exam->examID }}">
            <i class="bi bi-eye text-muted"></i> View More
        </button>
        @endif
        @if($canViewExamPapers)
        <button type="button" class="dropdown-item view-exam-papers-btn"
            data-exam-id="{{ $exam->examID }}"
            data-exam-name="{{ $exam->exam_name }}">
            <i class="bi bi-file-earmark-text text-primary"></i> Exam Papers
            <span class="badge badge-danger ml-1 d-none exam-paper-count" data-exam-id="{{ $exam->examID }}"></span>
        </button>
        @endif
        <button type="button" class="dropdown-item view-exam-halls-btn"
            data-exam-id="{{ $exam->examID }}"
            data-exam-name="{{ $exam->exam_name }}">
            <i class="bi bi-building text-secondary"></i> Exam Halls
        </button>
        <button type="button" class="dropdown-item view-exam-attendance-btn"
            data-exam-id="{{ $exam->examID }}"
            data-exam-name="{{ $exam->exam_name }}">
            <i class="bi bi-person-check text-info"></i> Attendance
        </button>
        @if($canUpdate)
        <button type="button" class="dropdown-item edit-exam-btn" data-exam-id="{{ $exam->examID }}">
            <i class="bi bi-pencil text-primary"></i> Edit
        </button>
        <button type="button" class="dropdown-item toggle-enter-result-btn"
            data-exam-id="{{ $exam->examID }}"
            data-exam-name="{{ $exam->exam_name }}"
            data-current-value="{{ $exam->enter_result ? 'true' : 'false' }}">
            <i class="bi {{ $exam->enter_result ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
            {{ $exam->enter_result ? 'Disallow Enter Result' : 'Allow Enter Result' }}
        </button>
        <button type="button" class="dropdown-item toggle-publish-result-btn"
            data-exam-id="{{ $exam->examID }}"
            data-current-value="{{ $exam->publish_result ? 'true' : 'false' }}">
            <i class="bi {{ $exam->publish_result ? 'bi-eye' : 'bi-eye-slash' }}"></i>
            {{ $exam->publish_result ? 'Unpublish Result' : 'Publish Result' }}
        </button>
        @if(($exam->student_shifting_status ?? 'none') !== 'none')
        <button type="button" class="dropdown-item auto-shift-students-btn"
            data-exam-id="{{ $exam->examID }}"
            data-exam-name="{{ $exam->exam_name }}"
            data-shifting-status="{{ $exam->student_shifting_status }}">
            <i class="bi bi-arrow-right-circle"></i> Shift Students
        </button>
        <button type="button" class="dropdown-item unshift-students-btn" data-exam-id="{{ $exam->examID }}">
            <i class="bi bi-arrow-counterclockwise"></i> Undo Shift
        </button>
        @endif
        <button type="button" class="dropdown-item toggle-upload-paper-btn"
            data-exam-id="{{ $exam->examID }}"
            data-current-value="{{ ($exam->upload_paper ?? true) ? 'true' : 'false' }}">
            <i class="bi {{ ($exam->upload_paper ?? true) ? 'bi-file-earmark-arrow-up' : 'bi-file-earmark-x' }}"></i>
            {{ ($exam->upload_paper ?? true) ? 'Disallow Upload Paper' : 'Allow Upload Paper' }}
        </button>
        @endif
        @if(($exam->approval_status ?? 'Pending') == 'Pending' && $canUpdate)
        <div class="dropdown-divider"></div>
        <button type="button" class="dropdown-item text-success approve-exam-btn"
            data-exam-id="{{ $exam->examID }}" data-exam-name="{{ $exam->exam_name }}">
            <i class="bi bi-check-circle"></i> Approve
        </button>
        <button type="button" class="dropdown-item text-danger reject-exam-btn"
            data-exam-id="{{ $exam->examID }}" data-exam-name="{{ $exam->exam_name }}">
            <i class="bi bi-x-circle"></i> Reject
        </button>
        @endif
        @if($canDelete)
        <div class="dropdown-divider"></div>
        <button type="button" class="dropdown-item text-danger delete-exam-btn"
            data-exam-id="{{ $exam->examID }}"
            data-exam-name="{{ $exam->exam_name }}">
            <i class="bi bi-trash"></i> Delete
        </button>
        @endif
    </div>
</div>
