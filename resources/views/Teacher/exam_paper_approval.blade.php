@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.exam_approval').' - ShuleXpert')
@section('page_title', __('teacher.pages.exam_approval'))
@section('page_breadcrumb', __('teacher.pages.exam_approval_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval th,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-paper-approval strong {
        color: #111 !important;
    }

    .sx-paper-approval {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
        --muted: #666;
    }

    .sx-paper-approval .sx-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .sx-paper-approval .sx-body { padding: 1.05rem 1.15rem 1.2rem; }

    .sx-paper-approval .form-label {
        font-size: .7rem;
        font-weight: 800 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: .3rem;
    }
    .sx-paper-approval .form-control,
    .sx-paper-approval .form-select,
    .sx-paper-approval .btn,
    .modal .form-control,
    .modal .btn,
    .modal .modal-content {
        border-radius: 0 !important;
    }
    .sx-paper-approval .form-control,
    .sx-paper-approval .form-select {
        min-height: 42px;
        border: 1px solid #d4caca;
        font-size: .92rem;
        font-weight: 600 !important;
        color: #111 !important;
        background: #fff;
    }
    .sx-paper-approval .form-control:focus,
    .sx-paper-approval .form-select:focus {
        border-color: var(--maroon);
        box-shadow: 0 0 0 .18rem rgba(148,0,0,.12);
    }

    .sx-paper-approval #approvalTabs {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .7rem;
        margin: 0;
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(135deg, rgba(148,0,0,.035), transparent 42%), linear-gradient(180deg, #fff, var(--soft));
    }
    .sx-paper-approval #approvalTabs .nav-item { width: 100%; margin: 0; }
    .sx-paper-approval #approvalTabs .nav-link {
        width: 100%;
        height: 72px;
        min-height: 72px;
        max-height: 72px;
        border: 1px solid #eadfdf !important;
        border-top: 3px solid #d2c4c4 !important;
        border-radius: 0 !important;
        color: #444 !important;
        font-weight: 700 !important;
        font-size: .9rem;
        padding: .75rem .55rem;
        background: linear-gradient(180deg, #fff 0%, #fcfbfb 100%) !important;
        box-shadow: 0 2px 8px rgba(80,0,0,.05);
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .25rem;
        position: relative;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .sx-paper-approval #approvalTabs .nav-link i {
        font-size: 1.1rem;
        color: #940000 !important;
    }
    .sx-paper-approval #approvalTabs .nav-link:hover {
        background: linear-gradient(180deg, #fff, #fff5f5) !important;
        border-color: #d8b4b4 !important;
        border-top-color: #940000 !important;
        color: var(--maroon) !important;
        transform: translateY(-1px);
    }
    .sx-paper-approval #approvalTabs .nav-link.active {
        color: var(--maroon) !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        background: linear-gradient(180deg, #fff8f8, #ffeaea) !important;
        box-shadow: 0 0 0 1px rgba(148,0,0,.08), 0 8px 18px rgba(148,0,0,.12);
    }
    .sx-paper-approval #approvalTabs .badge {
        position: absolute !important;
        top: 6px;
        right: 6px;
        margin: 0 !important;
        border-radius: 0 !important;
        font-weight: 700 !important;
        font-size: .65rem;
        padding: .18rem .35rem;
        line-height: 1;
    }

    .sx-paper-approval .sx-filter {
        border: 1px solid var(--line);
        background: linear-gradient(180deg, #fff, var(--soft));
        padding: .95rem 1.05rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 4px rgba(80,0,0,.04);
    }
    .sx-paper-approval .sx-week-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .35rem;
        min-height: 42px;
        border: 1px solid #d4caca;
        background: #fff;
        padding: .2rem .35rem;
    }
    .sx-paper-approval .sx-week-nav .btn {
        min-height: 32px;
        padding: .2rem .55rem;
        border-radius: 0 !important;
        color: #940000 !important;
        font-weight: 800 !important;
    }

    .sx-ep-table-wrap {
        border: 1px solid var(--line);
        overflow: auto;
        background: #fff;
        box-shadow: 0 2px 10px rgba(80,0,0,.04);
    }
    table.sx-ep-table { width: 100%; margin: 0; border-collapse: collapse; }
    table.sx-ep-table th {
        background: linear-gradient(180deg, #f7f2f2, #f1eaea);
        color: #333 !important;
        font-size: .7rem;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .72rem .8rem;
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }
    table.sx-ep-table td {
        font-size: .9rem;
        font-weight: 600 !important;
        color: #111 !important;
        padding: .8rem;
        border-bottom: 1px solid #f0e8e8;
        vertical-align: middle;
        line-height: 1.4;
        background: #fff;
    }
    table.sx-ep-table tbody tr:hover td { background: #fffafa; }
    table.sx-ep-table tr:last-child td { border-bottom: 0; }
    .sx-ep-title { color: var(--maroon) !important; font-weight: 800 !important; font-size: .94rem; }
    .sx-ep-meta { display: block; margin-top: .14rem; font-size: .8rem; color: var(--muted) !important; font-weight: 600 !important; }
    .sx-ep-actions { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; }
    .sx-ep-empty {
        padding: 2.4rem 1rem;
        text-align: center;
        color: var(--muted) !important;
        font-weight: 600 !important;
        background: radial-gradient(circle at top, rgba(148,0,0,.05), transparent 55%);
    }
    .sx-ep-empty i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto .65rem;
        border: 1px solid #eadfdf;
        background: #fff;
        color: var(--maroon);
        font-size: 1.25rem;
        box-shadow: 0 2px 8px rgba(80,0,0,.06);
    }

    .btn-primary-custom,
    .btn-primary-custom:hover,
    .btn-primary-custom:focus {
        background: linear-gradient(180deg, #a80000 0%, #940000 55%, #7f0000 100%) !important;
        border-color: #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
        min-height: 42px;
        box-shadow: 0 4px 12px rgba(148,0,0,.22);
    }
    .btn-primary-custom *,
    .btn-primary-custom:hover * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    .btn-exam-action {
        background: #fff !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: .35rem .7rem;
        font-size: .78rem;
        font-weight: 700 !important;
        border-radius: 0 !important;
    }
    .btn-exam-action:hover {
        background: #fff5f5 !important;
        box-shadow: 0 2px 8px rgba(148,0,0,.12);
    }
    .text-primary-custom { color: #940000 !important; }
    .bg-primary-custom { background: #fff5f5 !important; color: #940000 !important; }

    .tag {
        display: inline-block;
        padding: .2rem .45rem;
        font-size: .7rem;
        font-weight: 700 !important;
        border-radius: 0;
        line-height: 1.3;
        letter-spacing: .02em;
    }
    .tag-approved { background: #e8f6ec !important; color: #146c2e !important; border: 1px solid #c6e6cf; }
    .tag-rejected { background: #fdecec !important; color: #a11919 !important; border: 1px solid #f0c7c7; }
    .tag-pending { background: #fff3cd !important; color: #7a5a00 !important; border: 1px solid #edd89a; }
    .tag-step { background: #f8eaea; color: #940000 !important; border: 1px solid #ecd0d0; }

    .preview-panel {
        border: 1px solid #eadfdf;
        background: #fff;
        padding: 1rem;
        margin: .35rem 0;
        box-shadow: 0 2px 8px rgba(80,0,0,.04);
    }

    .timeline { position: relative; padding: 12px 0; list-style: none; margin: 0; }
    .timeline:before { content: ''; position: absolute; top: 0; bottom: 0; left: 40px; width: 2px; background: #e5d6d6; }
    .timeline-item { position: relative; margin-bottom: 16px; }
    .timeline-badge { position: absolute; top: 0; left: 30px; width: 22px; height: 22px; border-radius: 50%; border: 2px solid #dee2e6; z-index: 2; background: #fff; }
    .timeline-badge.approved { border-color: #28a745; background: #28a745; }
    .timeline-badge.pending { border-color: #ffc107; background: #ffc107; }
    .timeline-badge.rejected { border-color: #dc3545; background: #dc3545; }
    .timeline-badge.current { border-color: #940000; background: #fff; box-shadow: 0 0 0 3px rgba(148,0,0,.12); }
    .timeline-panel { margin-left: 70px; padding: 12px; border: 1px solid #eadfdf; background: #fff; box-shadow: 0 2px 8px rgba(80,0,0,.04); }

    .modal .modal-content {
        border: 1px solid #eadfdf !important;
        box-shadow: 0 18px 40px rgba(80,0,0,.14) !important;
    }
    .modal .modal-header {
        border-radius: 0 !important;
        background: linear-gradient(180deg, #fff8f8, #fff);
        border-bottom: 1px solid #eadfdf !important;
    }
    .modal .modal-title { font-weight: 800 !important; color: #940000 !important; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-paper-approval,
    html[data-bs-theme="dark"] .sx-paper-approval {
        --line: #3a2a2e;
        --soft: #2a1c20;
        --muted: #d7c4c8;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .sx-panel,
    html[data-bs-theme="dark"] .sx-paper-approval .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .form-label,
    html[data-bs-theme="dark"] .sx-paper-approval .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .form-control,
    html[data-bs-theme="dark"] .sx-paper-approval .form-control,
    html[data-sx-theme="dark"] .sx-paper-approval .form-select,
    html[data-bs-theme="dark"] .sx-paper-approval .form-select,
    html[data-sx-theme="dark"] .modal .form-control,
    html[data-bs-theme="dark"] .modal .form-control,
    html[data-sx-theme="dark"] .modal .form-select,
    html[data-bs-theme="dark"] .modal .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .form-select option,
    html[data-bs-theme="dark"] .sx-paper-approval .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-paper-approval #approvalTabs,
    html[data-bs-theme="dark"] .sx-paper-approval #approvalTabs {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval #approvalTabs .nav-link,
    html[data-bs-theme="dark"] .sx-paper-approval #approvalTabs .nav-link {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        border-top-color: #4a383c !important;
        color: #e8c9cd !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval #approvalTabs .nav-link i,
    html[data-bs-theme="dark"] .sx-paper-approval #approvalTabs .nav-link i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval #approvalTabs .nav-link:hover,
    html[data-bs-theme="dark"] .sx-paper-approval #approvalTabs .nav-link:hover,
    html[data-sx-theme="dark"] .sx-paper-approval #approvalTabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-paper-approval #approvalTabs .nav-link.active {
        background: #2a1c20 !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .sx-filter,
    html[data-bs-theme="dark"] .sx-paper-approval .sx-filter {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .sx-week-nav,
    html[data-bs-theme="dark"] .sx-paper-approval .sx-week-nav {
        background: #24181c !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-paper-approval .sx-week-nav .btn,
    html[data-bs-theme="dark"] .sx-paper-approval .sx-week-nav .btn {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-table-wrap,
    html[data-bs-theme="dark"] .sx-ep-table-wrap {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table th,
    html[data-bs-theme="dark"] table.sx-ep-table th {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-bottom-color: #6e0000 !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table td,
    html[data-bs-theme="dark"] table.sx-ep-table td {
        background: #24181c !important;
        color: #f3e9eb !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] table.sx-ep-table tbody tr:hover td,
    html[data-bs-theme="dark"] table.sx-ep-table tbody tr:hover td {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-title,
    html[data-bs-theme="dark"] .sx-ep-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-meta,
    html[data-bs-theme="dark"] .sx-ep-meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-empty,
    html[data-bs-theme="dark"] .sx-ep-empty {
        color: #d7c4c8 !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] .sx-ep-empty i,
    html[data-bs-theme="dark"] .sx-ep-empty i {
        background: #2a1c20 !important;
        border-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .btn-exam-action,
    html[data-bs-theme="dark"] .btn-exam-action {
        background: transparent !important;
        color: #ffb4b4 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .btn-exam-action:hover,
    html[data-bs-theme="dark"] .btn-exam-action:hover {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .preview-panel,
    html[data-bs-theme="dark"] .preview-panel {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .timeline-panel,
    html[data-bs-theme="dark"] .timeline-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .timeline:before,
    html[data-bs-theme="dark"] .timeline:before {
        background: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .modal .modal-content,
    html[data-bs-theme="dark"] .modal .modal-content {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .modal .modal-header,
    html[data-bs-theme="dark"] .modal .modal-header,
    html[data-sx-theme="dark"] .modal .modal-header.bg-primary-custom,
    html[data-bs-theme="dark"] .modal .modal-header.bg-primary-custom {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .modal .modal-title,
    html[data-bs-theme="dark"] .modal .modal-title,
    html[data-sx-theme="dark"] .text-primary-custom,
    html[data-bs-theme="dark"] .text-primary-custom {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .modal .modal-body,
    html[data-bs-theme="dark"] .modal .modal-body,
    html[data-sx-theme="dark"] .modal .modal-footer,
    html[data-bs-theme="dark"] .modal .modal-footer {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
</style>
@endpush

@section('content')
<div class="sx-paper-approval">
    <div class="sx-panel">
        <ul class="nav nav-tabs" id="approvalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" role="tab" aria-controls="pending" aria-selected="true">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('teacher.pending') }}</span>
                    <span class="badge bg-danger" id="pending-count-badge">{{ count($pendingLogs) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" role="tab" aria-controls="history" aria-selected="false">
                    <i class="bi bi-clock"></i>
                    <span>{{ __('teacher.exam_appr.history') }}</span>
                    <span class="badge bg-secondary" id="history-count-badge">{{ count($historyLogs) }}</span>
                </button>
            </li>
        </ul>

        <div class="sx-body">
            <div class="sx-filter">
                <form action="{{ route('admin.exam_paper_approval') }}" method="GET" class="row g-2 align-items-end" id="filterForm">
                    <div class="col-md-2 col-6">
                        <label class="form-label">{{ __('teacher.exam_appr.year') }}</label>
                        <select name="year" class="form-control">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $yearFilter == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">{{ __('teacher.exam_appr.term') }}</label>
                        <select name="term" class="form-control">
                            <option value="">{{ __('teacher.exam_appr.all_terms') }}</option>
                            <option value="first_term" {{ $termFilter == 'first_term' ? 'selected' : '' }}>{{ __('teacher.exam_appr.first_term') }}</option>
                            <option value="second_term" {{ $termFilter == 'second_term' ? 'selected' : '' }}>{{ __('teacher.exam_appr.second_term') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">{{ __('teacher.exam_appr.exam') }}</label>
                        <select name="examID" class="form-control">
                            <option value="">{{ __('teacher.exam_appr.all_examinations') }}</option>
                            @foreach($filter_examinations as $exam)
                                <option value="{{ $exam->examID }}" {{ $examID == $exam->examID ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">{{ __('teacher.class') }}</label>
                        <select name="classID" class="form-control">
                            <option value="">{{ __('teacher.exam_appr.all_classes') }}</option>
                            @foreach($filter_classes as $class)
                                <option value="{{ $class->classID }}" {{ $classID == $class->classID ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">{{ __('teacher.exam_appr.subclass') }}</label>
                        <select name="subclassID" class="form-control">
                            <option value="">{{ __('teacher.exam_appr.all_subclasses') }}</option>
                            @foreach($filter_subclasses as $sub)
                                <option value="{{ $sub->subclassID }}" {{ $subclassID == $sub->subclassID ? 'selected' : '' }}>
                                    {{ $sub->subclass_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6" id="week_filter_container" style="{{ (isset($selected_exam) && in_array(strtolower(trim($selected_exam->exam_name)), ['weekly test', 'monthly test'])) ? '' : 'display: none;' }}">
                        <label class="form-label">{{ __('teacher.exam_appr.week') }}</label>
                        <input type="hidden" name="week" id="filter_week_value" value="{{ $weekFilter ?? '' }}">
                        <div class="sx-week-nav">
                            <button type="button" class="btn btn-sm btn-light" id="btn_prev_week"><i class="bi bi-chevron-left"></i></button>
                            <span class="small fw-bold text-center flex-grow-1 text-truncate px-1" id="display_week_label">{{ $weekFilter ?: __('teacher.exam_appr.select_week') }}</span>
                            <button type="button" class="btn btn-sm btn-light" id="btn_next_week"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">{{ __('teacher.subject') }}</label>
                        <select name="subjectID" class="form-control">
                            <option value="">{{ __('teacher.exam_appr.all_subjects') }}</option>
                            @foreach($filter_subjects as $sub)
                                <option value="{{ $sub->subjectID }}" {{ $subjectID == $sub->subjectID ? 'selected' : '' }}>
                                    {{ $sub->subject_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1" id="btnFilterApply">
                            <i class="bi bi-funnel"></i> {{ __('teacher.exam_appr.apply') }}
                        </button>
                        <a href="{{ route('admin.exam_paper_approval') }}" class="btn btn-outline-secondary flex-grow-1" style="min-height:42px;border-radius:0;font-weight:700;">
                            <i class="bi bi-arrow-counterclockwise"></i> {{ __('teacher.exam_appr.reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="tab-content" id="approvalTabsContent">
                <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    <div class="sx-ep-table-wrap">
                        <table class="sx-ep-table" id="pendingApprovalsTable">
                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th>{{ __('teacher.exam_appr.exam') }}</th>
                                    <th>{{ __('teacher.exam_appr.subject_class') }}</th>
                                    <th>{{ __('teacher.exam_appr.teacher') }}</th>
                                    <th>{{ __('teacher.exam_appr.step') }}</th>
                                    <th>{{ __('teacher.exam_appr.uploaded') }}</th>
                                    <th class="text-end">{{ __('teacher.exam_appr.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingLogs as $index => $log)
                                    <tr id="row-{{ $log->exam_paperID }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="sx-ep-title">{{ $log->examPaper->examination->exam_name ?? __('teacher.exam_appr.na') }}</div>
                                            <span class="sx-ep-meta">{{ $log->examPaper->displayYearLabel() }} · {{ $log->examPaper->examination->term }}</span>
                                        </td>
                                        <td>
                                            <div class="sx-ep-title">
                                                {{ $log->examPaper->classSubject->subject->subject_name ?? __('teacher.exam_appr.na') }}
                                                @if(!empty($log->examPaper->componentID) && $log->examPaper->component)
                                                    <span class="tag tag-step">{{ $log->examPaper->component->component_name }}</span>
                                                @endif
                                            </div>
                                            <span class="sx-ep-meta">{{ ($log->examPaper->classSubject->class->class_name ?? '') }} {{ ($log->examPaper->classSubject->subclass->subclass_name ?? __('teacher.exam_appr.na')) }}</span>
                                        </td>
                                        <td>{{ $log->examPaper->teacher->first_name ?? '' }} {{ $log->examPaper->teacher->last_name ?? '' }}</td>
                                        <td>
                                            <span class="tag tag-step">
                                                {{ __('teacher.exam_appr.step_n', ['n' => $log->approval_order]) }}
                                                @if($log->special_role_type)
                                                    {{ ucwords(str_replace('_', ' ', $log->special_role_type)) }}
                                                @else
                                                    {{ $log->role->name ?? __('teacher.exam_appr.unknown_role') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $log->examPaper->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <div class="sx-ep-actions">
                                                <button type="button" class="btn-exam-action btn-preview-paper" data-paper-id="{{ $log->exam_paperID }}" title="{{ __('teacher.exam_appr.preview') }}">
                                                    <i class="bi bi-eye"></i> {{ __('teacher.exam_appr.view') }}
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn-exam-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('teacher.exam_appr.more') }}</button>
                                                    <ul class="dropdown-menu dropdown-menu-end rounded-0">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('download_exam_paper', $log->exam_paperID) }}">
                                                                <i class="bi bi-download"></i> {{ __('teacher.download') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item btn-view-chain" href="javascript:void(0)"
                                                               data-paper-id="{{ $log->exam_paperID }}"
                                                               data-exam-id="{{ $log->examPaper->examID }}">
                                                                <i class="bi bi-list-ol"></i> {{ __('teacher.exam_appr.view_chain') }}
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-success btn-approve-reject" href="javascript:void(0)"
                                                               data-action="approve"
                                                               data-paper-id="{{ $log->exam_paperID }}"
                                                               data-log-id="{{ $log->paper_approval_logID }}">
                                                                <i class="bi bi-check2"></i> {{ __('teacher.duty.approve') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger btn-approve-reject" href="javascript:void(0)"
                                                               data-action="reject"
                                                               data-paper-id="{{ $log->exam_paperID }}"
                                                               data-log-id="{{ $log->paper_approval_logID }}">
                                                                <i class="bi bi-x"></i> {{ __('teacher.duty.reject') }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="preview-row-{{ $log->exam_paperID }}" class="preview-row" style="display: none;">
                                        <td colspan="7">
                                            <div class="preview-panel">
                                                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                                    <h6 class="text-primary-custom mb-0 fw-bold"><i class="bi bi-file-earmark-text"></i> {{ __('teacher.exam_appr.exam_paper_preview') }}</h6>
                                                    <button type="button" class="btn-close btn-close-preview" data-id="{{ $log->exam_paperID }}" aria-label="{{ __('teacher.close') }}"></button>
                                                </div>
                                                <div id="preview-content-{{ $log->exam_paperID }}">
                                                    <div class="text-center py-4">
                                                        <i class="bi bi-arrow-repeat fa-spin text-primary-custom"></i>
                                                        <p class="mb-0 mt-2">{{ __('teacher.exam_appr.loading_paper') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="sx-ep-empty">
                                                <i class="bi bi-info-circle"></i>
                                                {{ __('teacher.exam_appr.no_pending') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                    <div class="sx-ep-table-wrap">
                        <table class="sx-ep-table" id="historyApprovalsTable">
                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th>{{ __('teacher.exam_appr.exam') }}</th>
                                    <th>{{ __('teacher.exam_appr.subject_class') }}</th>
                                    <th>{{ __('teacher.exam_appr.teacher') }}</th>
                                    <th>{{ __('teacher.status') }}</th>
                                    <th>{{ __('teacher.exam_appr.comment') }}</th>
                                    <th>{{ __('teacher.date') }}</th>
                                    <th class="text-end">{{ __('teacher.exam_appr.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historyLogs as $index => $log)
                                    @php
                                        $statusLabel = $log->status == 'approved' ? __('teacher.approved') : ($log->status == 'rejected' ? __('teacher.rejected') : __('teacher.pending'));
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="sx-ep-title">{{ $log->examPaper->examination->exam_name ?? __('teacher.exam_appr.na') }}</div>
                                            <span class="sx-ep-meta">{{ $log->examPaper->displayYearLabel() }} · {{ $log->examPaper->examination->term }}</span>
                                        </td>
                                        <td>
                                            <div class="sx-ep-title">
                                                {{ $log->examPaper->classSubject->subject->subject_name ?? __('teacher.exam_appr.na') }}
                                                @if($log->examPaper->component)
                                                    <span class="tag tag-step">{{ $log->examPaper->component->component_name }}</span>
                                                @endif
                                            </div>
                                            <span class="sx-ep-meta">{{ ($log->examPaper->classSubject->class->class_name ?? '') }} {{ ($log->examPaper->classSubject->subclass->subclass_name ?? __('teacher.exam_appr.na')) }}</span>
                                        </td>
                                        <td>{{ $log->examPaper->teacher->first_name ?? '' }} {{ $log->examPaper->teacher->last_name ?? '' }}</td>
                                        <td>
                                            <span class="tag {{ $log->status == 'approved' ? 'tag-approved' : 'tag-rejected' }}">
                                                {{ __('teacher.exam_appr.status_step', ['status' => $statusLabel, 'n' => $log->approval_order]) }}
                                            </span>
                                            <span class="sx-ep-meta">
                                                @if($log->special_role_type)
                                                    {{ ucwords(str_replace('_', ' ', $log->special_role_type)) }}
                                                @else
                                                    {{ $log->role->name ?? __('teacher.exam_appr.unknown_role') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->comment)
                                                <span class="small">"{{ $log->comment }}"</span>
                                            @else
                                                <span class="sx-ep-meta">{{ __('teacher.exam_appr.no_comment') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->updated_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <div class="sx-ep-actions">
                                                <button type="button" class="btn-exam-action btn-preview-paper"
                                                        data-paper-id="{{ $log->exam_paperID }}"
                                                        data-is-history="true">
                                                    <i class="bi bi-eye"></i> {{ __('teacher.exam_appr.view') }}
                                                </button>
                                                <button type="button" class="btn-exam-action btn-view-chain"
                                                        data-paper-id="{{ $log->exam_paperID }}"
                                                        data-exam-id="{{ $log->examPaper->examID }}"
                                                        title="{{ __('teacher.exam_appr.view_chain') }}">
                                                    <i class="bi bi-list-ol"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="history-preview-row-{{ $log->exam_paperID }}" class="preview-row" style="display: none;">
                                        <td colspan="8">
                                            <div class="preview-panel">
                                                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                                    <h6 class="text-primary-custom mb-0 fw-bold"><i class="bi bi-file-earmark-text"></i> {{ __('teacher.exam_appr.exam_paper_preview') }}</h6>
                                                    <button type="button" class="btn-close btn-close-preview" data-id="history-{{ $log->exam_paperID }}" aria-label="{{ __('teacher.close') }}"></button>
                                                </div>
                                                <div id="history-preview-content-{{ $log->exam_paperID }}">
                                                    <div class="text-center py-4">
                                                        <i class="bi bi-arrow-repeat text-primary-custom"></i>
                                                        <p class="mb-0 mt-2">{{ __('teacher.exam_appr.loading_paper') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="sx-ep-empty">
                                                <i class="bi bi-clock-history"></i>
                                                {{ __('teacher.exam_appr.no_history') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve / Reject Modal -->
<div class="modal fade" id="approvalActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">{{ __('teacher.exam_appr.approve_paper') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <form id="approvalActionForm">
                @csrf
                <input type="hidden" id="action_paper_id" name="exam_paper_id">
                <input type="hidden" id="action_log_id" name="paper_approval_log_id">
                <input type="hidden" id="action_type" name="action">
                <div class="modal-body">
                    <div id="rejection_reason_group" style="display: none;">
                        <label class="form-label">{{ __('teacher.exam_appr.rejection_reason') }}</label>
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="4" placeholder="{{ __('teacher.exam_appr.rejection_ph') }}"></textarea>
                    </div>
                    <div id="approval_comment_group">
                        <label class="form-label">{{ __('teacher.exam_appr.comments_optional') }}</label>
                        <textarea class="form-control" name="approval_comment" id="approval_comment" rows="4" placeholder="{{ __('teacher.exam_appr.comments_ph') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:0;font-weight:700;">{{ __('teacher.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitApprovalBtn">{{ __('teacher.exam_appr.confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Chain Modal -->
<div class="modal fade" id="viewChainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('teacher.exam_appr.approval_chain') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <div id="chainTimelineContent">
                    <div class="text-center py-4">
                        <i class="bi bi-arrow-repeat text-primary-custom"></i>
                        <p class="mb-0 mt-2">{{ __('teacher.exam_appr.loading_chain') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-paper-approval .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-paper-approval .form-label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th,
html[data-bs-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-paper-approval .sx-ep-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-paper-approval .sx-ep-title {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-paper-approval .sx-ep-meta,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-paper-approval .sx-ep-meta {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-paper-approval #approvalTabs .nav-link,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-paper-approval #approvalTabs .nav-link {
    color: #e8c9cd !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-paper-approval #approvalTabs .nav-link.active,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-paper-approval #approvalTabs .nav-link.active {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-approved,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-approved {
    background: #e8f6ec !important; color: #146c2e !important; -webkit-text-fill-color: #146c2e !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-rejected,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-rejected {
    background: #fdecec !important; color: #a11919 !important; -webkit-text-fill-color: #a11919 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-pending,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-pending {
    background: #fff3cd !important; color: #7a5a00 !important; -webkit-text-fill-color: #7a5a00 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-step,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-step {
    background: #f8eaea !important; color: #940000 !important; -webkit-text-fill-color: #940000 !important;
}
</style>
@endsection

@push('scripts')
<script>
var i18nAppr = @json(__('teacher.exam_appr'));
i18nAppr.pending = @json(__('teacher.pending'));
i18nAppr.approved = @json(__('teacher.approved'));
i18nAppr.rejected = @json(__('teacher.rejected'));
i18nAppr.cancel = @json(__('teacher.cancel'));
i18nAppr.close = @json(__('teacher.close'));
i18nAppr.loading = @json(__('teacher.loading'));
i18nAppr.status = @json(__('teacher.status'));
i18nAppr.subject = @json(__('teacher.subject'));
i18nAppr.class = @json(__('teacher.class'));
i18nAppr.approve = @json(__('teacher.duty.approve'));
i18nAppr.reject = @json(__('teacher.duty.reject'));

window.sxShowModal = function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        const inst = bootstrap.Modal.getInstance(node);
        if (inst) inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};

$(document).ready(function() {
    $(document).on('click', '.btn-approve-reject', function() {
        const paperId = $(this).data('paper-id');
        const logId = $(this).data('log-id');
        const action = $(this).data('action');

        $('#action_paper_id').val(paperId);
        $('#action_log_id').val(logId);
        $('#action_type').val(action);

        if (action === 'approve') {
            $('#approvalModalTitle').text(i18nAppr.approve_paper);
            $('#rejection_reason_group').hide();
            $('#approval_comment_group').show();
            $('#submitApprovalBtn').text(i18nAppr.confirm_approval).removeClass('btn-danger').addClass('btn-primary-custom');
        } else {
            $('#approvalModalTitle').text(i18nAppr.reject_paper);
            $('#rejection_reason_group').show();
            $('#approval_comment_group').hide();
            $('#submitApprovalBtn').text(i18nAppr.confirm_rejection).removeClass('btn-primary-custom').addClass('btn-danger');
        }

        sxShowModal('#approvalActionModal');
    });

    $('#approvalActionForm').on('submit', function(e) {
        e.preventDefault();
        const paperId = $('#action_paper_id').val();

        $('#submitApprovalBtn').prop('disabled', true).html('<i class="bi bi-arrow-repeat"></i> ' + i18nAppr.processing);

        $.ajax({
            url: "{{ url('approve_reject_exam_paper') }}/" + paperId,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                sxHideModal('#approvalActionModal');
                Swal.fire({
                    icon: 'success',
                    title: i18nAppr.success,
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() { location.reload(); });
            },
            error: function(xhr) {
                $('#submitApprovalBtn').prop('disabled', false).text(i18nAppr.confirm);
                let errorMsg = i18nAppr.err_process;
                if (xhr.status === 422) errorMsg = i18nAppr.err_required;
                else if (xhr.responseJSON && xhr.responseJSON.error) errorMsg = xhr.responseJSON.error;
                Swal.fire({ icon: 'error', title: i18nAppr.oops, text: errorMsg });
            }
        });
    });

    $(document).on('click', '.btn-preview-paper', function() {
        const paperId = $(this).data('paper-id');
        const isHistory = $(this).data('is-history');
        const prefix = isHistory ? 'history-' : '';
        const previewRow = $('#' + prefix + 'preview-row-' + paperId);
        const contentDiv = $('#' + prefix + 'preview-content-' + paperId);

        if (previewRow.is(':visible')) {
            previewRow.hide();
            return;
        }

        $('.preview-row').hide();
        previewRow.show();
        contentDiv.html('<div class="text-center py-4"><i class="bi bi-arrow-repeat text-primary-custom"></i><p class="mb-0 mt-2">' + i18nAppr.loading_paper + '</p></div>');

        $.ajax({
            url: "{{ url('get_admin_exam_paper_review') }}/" + paperId,
            method: 'GET',
            success: function(response) {
                if (!response.success) {
                    contentDiv.html('<div class="alert alert-danger rounded-0">' + i18nAppr.failed_paper + '</div>');
                    return;
                }
                const paper = response.paper;
                let html = `
                    <div class="row g-3">
                        <div class="col-md-4">
                            <h6 class="fw-bold text-primary-custom">${i18nAppr.general_info}</h6>
                            <table class="table table-sm table-borderless mb-3">
                                <tr><td width="40%" class="text-muted">${i18nAppr.teacher_label}</td><td>${paper.teacher_name}</td></tr>
                                <tr><td class="text-muted">${i18nAppr.subject_label}</td><td>${paper.subject}</td></tr>
                                <tr><td class="text-muted">${i18nAppr.class_label}</td><td>${paper.class}</td></tr>
                            </table>
                            <h6 class="fw-bold text-primary-custom">${i18nAppr.description}</h6>
                            <p class="small">${paper.description || i18nAppr.no_description}</p>
                            ${paper.file_path ? `<a href="${paper.file_path}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-0 w-100"><i class="bi bi-file-earmark-pdf"></i> ${i18nAppr.view_uploaded_file}</a>` : '<div class="alert alert-secondary py-2 small rounded-0 mb-0">' + i18nAppr.no_file + '</div>'}
                        </div>
                        <div class="col-md-8">
                            <h6 class="fw-bold text-primary-custom">${i18nAppr.questions_list}</h6>
                            ${paper.questions.length > 0 ? `
                                <div class="sx-ep-table-wrap" style="max-height:400px;overflow:auto;">
                                    <table class="sx-ep-table">
                                        <thead><tr><th width="10%">${i18nAppr.no_col}</th><th>${i18nAppr.description}</th><th width="15%">${i18nAppr.marks}</th></tr></thead>
                                        <tbody>
                                            ${paper.questions.map(q => `<tr><td>${q.number}</td><td class="small">${q.description}</td><td>${q.marks}</td></tr>`).join('')}
                                        </tbody>
                                    </table>
                                </div>` : '<div class="alert alert-warning rounded-0">' + i18nAppr.no_questions + '</div>'}
                        </div>
                    </div>
                    ${!isHistory ? `
                    <div class="mt-3 d-flex justify-content-end gap-2 border-top pt-2">
                        <button type="button" class="btn btn-sm btn-success rounded-0 btn-approve-direct" data-paper-id="${paper.exam_paperID}"><i class="bi bi-check2"></i> ${i18nAppr.approve}</button>
                        <button type="button" class="btn btn-sm btn-danger rounded-0 btn-reject-direct" data-paper-id="${paper.exam_paperID}"><i class="bi bi-x"></i> ${i18nAppr.reject}</button>
                    </div>` : ''}
                `;
                contentDiv.html(html);
            },
            error: function() {
                contentDiv.html('<div class="alert alert-danger rounded-0">' + i18nAppr.err_fetch_paper + '</div>');
            }
        });
    });

    $(document).on('click', '.btn-approve-direct', function() {
        const pId = $(this).data('paper-id');
        $(`#row-${pId} .btn-approve-reject[data-action="approve"]`).trigger('click');
    });
    $(document).on('click', '.btn-reject-direct', function() {
        const pId = $(this).data('paper-id');
        $(`#row-${pId} .btn-approve-reject[data-action="reject"]`).trigger('click');
    });

    $(document).on('click', '.btn-close-preview', function() {
        const id = $(this).data('id');
        if (typeof id === 'string' && id.startsWith('history-')) {
            $('#history-preview-row-' + id.replace('history-', '')).hide();
        } else {
            $('#preview-row-' + id).hide();
        }
    });

    let availableWeeks = @json($available_weeks ?? []);
    let currentWeekValue = "{{ $weekFilter ?? '' }}";

    $('select[name="examID"]').on('change', function() {
        const examID = $(this).val();
        if (!examID) {
            $('#week_filter_container').hide();
            $('#filter_week_value').val('');
            return;
        }
        $.ajax({
            url: "{{ url('get_exam_available_weeks') }}/" + examID,
            method: 'GET',
            success: function(response) {
                if (!response.success) return;
                availableWeeks = response.available_weeks;
                const category = (response.category || '').toLowerCase().trim();
                if (category === 'weekly test' || category === 'monthly test') {
                    $('#week_filter_container').show();
                    if (!$('#filter_week_value').val()) {
                        currentWeekValue = 'Week ' + getISOWeek(new Date());
                        updateWeekDisplay(currentWeekValue);
                    }
                } else {
                    $('#week_filter_container').hide();
                    $('#filter_week_value').val('');
                    availableWeeks = [];
                }
            }
        });
    });

    function getISOWeek(date) {
        const tempDate = new Date(date.getTime());
        tempDate.setHours(0, 0, 0, 0);
        tempDate.setDate(tempDate.getDate() + 3 - (tempDate.getDay() + 6) % 7);
        const week1 = new Date(tempDate.getFullYear(), 0, 4);
        return 1 + Math.round(((tempDate.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
    }
    function updateWeekDisplay(val) {
        $('#filter_week_value').val(val);
        $('#display_week_label').text(val || i18nAppr.select_week);
    }

    $('#btn_prev_week').on('click', function() {
        if (availableWeeks.length === 0) return;
        let currentIndex = availableWeeks.indexOf(currentWeekValue);
        if (currentIndex > 0) {
            currentWeekValue = availableWeeks[currentIndex - 1];
            updateWeekDisplay(currentWeekValue);
        }
    });
    $('#btn_next_week').on('click', function() {
        if (availableWeeks.length === 0) return;
        let currentIndex = availableWeeks.indexOf(currentWeekValue);
        if (currentIndex < availableWeeks.length - 1) {
            currentWeekValue = availableWeeks[currentIndex + 1];
            updateWeekDisplay(currentWeekValue);
        } else if (currentIndex === -1) {
            currentWeekValue = availableWeeks[0];
            updateWeekDisplay(currentWeekValue);
        }
    });

    $('select[name="year"], select[name="term"]').on('change', function() {
        $.ajax({
            url: "{{ route('get_examinations_for_filter') }}",
            method: 'GET',
            data: { year: $('select[name="year"]').val(), term: $('select[name="term"]').val() },
            success: function(response) {
                if (!response.success) return;
                let html = '<option value="">' + i18nAppr.all_examinations + '</option>';
                response.examinations.forEach(function(exam) {
                    html += `<option value="${exam.examID}">${exam.exam_name}</option>`;
                });
                $('select[name="examID"]').html(html).trigger('change');
            }
        });
    });

    $('select[name="classID"]').on('change', function() {
        $.ajax({
            url: "{{ route('get_subclasses_for_exam') }}",
            method: 'GET',
            data: { classID: $(this).val() },
            success: function(response) {
                if (!response.success) return;
                let html = '<option value="">' + i18nAppr.all_subclasses + '</option>';
                response.subclasses.forEach(function(sub) {
                    html += `<option value="${sub.subclassID}">${sub.subclass_name}</option>`;
                });
                $('select[name="subclassID"]').html(html);
                $('select[name="subjectID"]').html('<option value="">' + i18nAppr.all_subjects + '</option>');
            }
        });
    });

    $('select[name="subclassID"]').on('change', function() {
        const subclassID = $(this).val();
        if (!subclassID) return;
        $.ajax({
            url: "{{ route('get_class_subjects_by_subclass_post') }}",
            method: 'POST',
            data: { subclass_ids: [subclassID], _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (!response.success) return;
                let html = '<option value="">' + i18nAppr.all_subjects + '</option>';
                const addedSubjects = new Set();
                response.class_subjects.forEach(function(cs) {
                    if (cs.subjectID && !addedSubjects.has(cs.subjectID)) {
                        html += `<option value="${cs.subjectID}">${cs.subject_name}</option>`;
                        addedSubjects.add(cs.subjectID);
                    }
                });
                $('select[name="subjectID"]').html(html);
            }
        });
    });

    $(document).on('click', '.btn-view-chain', function() {
        const paperId = $(this).data('paper-id');
        const examId = $(this).data('exam-id');
        sxShowModal('#viewChainModal');
        $('#chainTimelineContent').html('<div class="text-center py-4"><i class="bi bi-arrow-repeat text-primary-custom"></i><p class="mb-0 mt-2">' + i18nAppr.loading_chain + '</p></div>');

        $.ajax({
            url: "{{ url('view_paper_approval_chain') }}/" + examId,
            method: 'GET',
            data: { paper_id: paperId },
            success: function(response) {
                let html = '<ul class="timeline">';
                const chain = response.chain || [];
                const logs = response.logs || [];

                chain.forEach(function(step) {
                    let statusClass = 'pending';
                    let statusText = i18nAppr.pending;
                    let comment = '';
                    let approver = '';
                    let date = '';
                    const log = logs.find(l => l.approval_order === step.approval_order);

                    if (log) {
                        if (log.status === 'approved') {
                            statusClass = 'approved';
                            statusText = i18nAppr.approved;
                            approver = log.approver ? log.approver.first_name + ' ' + log.approver.last_name : i18nAppr.na;
                            date = new Date(log.updated_at).toLocaleString();
                            comment = log.comment ? '<div class="small mt-2 p-2" style="background:#f8f8f8;border-left:3px solid #28a745;">" ' + log.comment + ' "</div>' : '';
                        } else if (log.status === 'rejected') {
                            statusClass = 'rejected';
                            statusText = i18nAppr.rejected;
                            approver = log.approver ? log.approver.first_name + ' ' + log.approver.last_name : i18nAppr.na;
                            date = new Date(log.updated_at).toLocaleString();
                            comment = log.comment ? '<div class="small mt-2 p-2" style="background:#f8f8f8;border-left:3px solid #dc3545;">" ' + log.comment + ' "</div>' : '';
                        } else if (log.status === 'pending') {
                            statusClass = 'current';
                            statusText = i18nAppr.awaiting_approval;
                        }
                    }

                    const roleName = step.special_role_type
                        ? step.special_role_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
                        : (step.role ? step.role.name : i18nAppr.unknown_role);

                    const contentHtml = (approver || date || comment)
                        ? `<div class="small text-muted">${approver ? (i18nAppr.by_approver.replace(':name', approver) + (date ? ' | ' + date : '')) : i18nAppr.progressing}</div>${comment}`
                        : `<div class="small text-muted">${i18nAppr.progressing}</div>`;

                    const badgeClass = statusClass === 'approved' ? 'tag-approved' : (statusClass === 'rejected' ? 'tag-rejected' : 'tag-pending');

                    html += `
                        <li class="timeline-item">
                            <div class="timeline-badge ${statusClass}"></div>
                            <div class="timeline-panel">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <h6 class="mb-1 text-primary-custom fw-bold">${i18nAppr.step_role.replace(':n', step.approval_order).replace(':role', roleName)}</h6>
                                    <span class="tag ${badgeClass}">${statusText}</span>
                                </div>
                                ${contentHtml}
                            </div>
                        </li>`;
                });

                html += '</ul>';
                $('#chainTimelineContent').html(html);
            },
            error: function() {
                $('#chainTimelineContent').html('<div class="alert alert-danger rounded-0">' + i18nAppr.failed_chain + '</div>');
            }
        });
    });

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btnFilterApply');
        const origHtml = btn.html();
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat"></i> ' + i18nAppr.filtering);

        const tbodyPending = $('#pendingApprovalsTable tbody');
        const tbodyHistory = $('#historyApprovalsTable tbody');
        tbodyPending.html('<tr><td colspan="7" class="text-center py-5"><i class="bi bi-arrow-repeat text-primary-custom"></i></td></tr>');
        tbodyHistory.html('<tr><td colspan="8" class="text-center py-5"><i class="bi bi-arrow-repeat text-primary-custom"></i></td></tr>');

        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            success: function(response) {
                const parsedResponse = $(response);
                tbodyPending.html(parsedResponse.find('#pendingApprovalsTable tbody').html());
                tbodyHistory.html(parsedResponse.find('#historyApprovalsTable tbody').html());
                $('#pending-count-badge').text(parsedResponse.find('#pending-count-badge').text() || parsedResponse.find('#pending-tab .badge').text());
                $('#history-count-badge').text(parsedResponse.find('#history-count-badge').text() || parsedResponse.find('#history-tab .badge').text());
                btn.prop('disabled', false).html(origHtml);
            },
            error: function() {
                btn.prop('disabled', false).html(origHtml);
                tbodyPending.html('<tr><td colspan="7" class="text-center text-danger py-4">' + i18nAppr.err_filter_data + '</td></tr>');
                tbodyHistory.html('<tr><td colspan="8" class="text-center text-danger py-4">' + i18nAppr.err_filter_data + '</td></tr>');
                Swal.fire({ icon: 'error', title: i18nAppr.oops, text: i18nAppr.err_filter });
            }
        });
    });
});
</script>
@endpush
