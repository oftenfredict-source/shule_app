@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.exam_papers').' - ShuleXpert')
@section('page_title', __('teacher.pages.exam_papers'))
@section('page_breadcrumb', __('teacher.pages.exam_papers_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers span,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers td,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers th,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-papers strong {
        color: #111 !important;
    }

    .sx-teacher-papers {
        --maroon: #940000;
        --maroon-deep: #6e0000;
        --line: #e8e0e0;
        --soft: #faf7f7;
        --muted: #666;
        --ink: #111;
    }

    .sx-teacher-papers .sx-panel {
        background:
            linear-gradient(180deg, #fff 0%, #fff 72%, #fbf8f8 100%);
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow:
            0 1px 2px rgba(80, 0, 0, .04),
            0 10px 28px rgba(80, 0, 0, .06);
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .sx-teacher-papers #examPapersTabs {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .7rem;
        margin: 0;
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--line);
        background:
            linear-gradient(135deg, rgba(148,0,0,.035), transparent 42%),
            linear-gradient(180deg, #fff, var(--soft));
        flex-wrap: nowrap;
    }
    .sx-teacher-papers #examPapersTabs .nav-item {
        width: 100%;
        margin: 0;
    }
    .sx-teacher-papers #examPapersTabs .nav-link {
        width: 100%;
        height: 78px;
        min-height: 78px;
        max-height: 78px;
        border: 1px solid #eadfdf !important;
        border-top: 3px solid #d2c4c4 !important;
        border-radius: 0 !important;
        color: #444 !important;
        font-weight: 600 !important;
        font-size: .86rem;
        letter-spacing: .01em;
        padding: .7rem .55rem .65rem;
        background:
            linear-gradient(180deg, #fff 0%, #fcfbfb 100%) !important;
        box-shadow: 0 2px 8px rgba(80,0,0,.05);
        text-align: center;
        white-space: nowrap;
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .2rem;
        overflow: visible;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease;
        position: relative;
    }
    .sx-teacher-papers #examPapersTabs .nav-link i {
        font-size: 1.1rem;
        color: #940000 !important;
        line-height: 1;
        opacity: .9;
        flex: 0 0 auto;
    }
    .sx-teacher-papers #examPapersTabs .nav-link span:not(.badge) {
        font-weight: 700 !important;
        color: inherit !important;
        line-height: 1.15;
        flex: 0 0 auto;
    }
    .sx-teacher-papers #examPapersTabs .nav-link:hover {
        background: linear-gradient(180deg, #fff 0%, #fff5f5 100%) !important;
        border-color: #d8b4b4 !important;
        border-top-color: #940000 !important;
        color: var(--maroon) !important;
        box-shadow: 0 6px 16px rgba(148,0,0,.1);
        transform: translateY(-1px);
    }
    .sx-teacher-papers #examPapersTabs .nav-link.active {
        color: var(--maroon) !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        background:
            linear-gradient(180deg, #fff8f8 0%, #ffeaea 100%) !important;
        font-weight: 700 !important;
        box-shadow:
            0 0 0 1px rgba(148,0,0,.08),
            0 8px 18px rgba(148,0,0,.12);
    }
    .sx-teacher-papers #examPapersTabs .nav-link.active::after {
        content: '';
        position: absolute;
        left: 18%;
        right: 18%;
        bottom: 0;
        height: 2px;
        background: var(--maroon);
    }
    .sx-teacher-papers #examPapersTabs .badge {
        position: absolute !important;
        top: 6px;
        right: 6px;
        margin: 0 !important;
        border-radius: 0 !important;
        font-weight: 700 !important;
        font-size: .65rem;
        letter-spacing: .02em;
        padding: .18rem .35rem;
        line-height: 1;
        min-width: 1.25rem;
        text-align: center;
    }
    @media (max-width: 991.98px) {
        .sx-teacher-papers #examPapersTabs {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 575.98px) {
        .sx-teacher-papers #examPapersTabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .sx-teacher-papers #examPapersTabs .nav-link {
            height: 72px;
            min-height: 72px;
            max-height: 72px;
        }
    }

    .sx-teacher-papers .sx-body { padding: 1.15rem 1.2rem 1.35rem; }

    .sx-teacher-papers .sx-upload-surface {
        border: 1px solid var(--line);
        background:
            linear-gradient(180deg, #fff 0%, #fcfafa 100%);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
        padding: 1rem 1.05rem 1.1rem;
        margin-bottom: 1rem;
    }

    .sx-teacher-papers .form-label {
        font-size: .72rem;
        font-weight: 800 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: .35rem;
    }
    .sx-teacher-papers .form-control,
    .sx-teacher-papers .form-select,
    .sx-teacher-papers .btn,
    .modal .form-control,
    .modal .form-select,
    .modal .btn,
    .modal .modal-content {
        border-radius: 0 !important;
    }
    .sx-teacher-papers .form-control,
    .sx-teacher-papers .form-select {
        min-height: 44px;
        border: 1px solid #d4caca;
        font-size: .93rem;
        font-weight: 600 !important;
        color: var(--ink) !important;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .sx-teacher-papers .form-control:hover,
    .sx-teacher-papers .form-select:hover {
        border-color: #c4a8a8;
    }
    .sx-teacher-papers .form-control:focus,
    .sx-teacher-papers .form-select:focus,
    .modal .form-control:focus,
    .modal .form-select:focus {
        border-color: var(--maroon);
        box-shadow: 0 0 0 .18rem rgba(148,0,0,.12);
    }
    .sx-teacher-papers .form-text,
    .sx-teacher-papers .text-muted { color: var(--muted) !important; font-weight: 500 !important; font-size: .8rem; }

    .sx-teacher-papers .sx-qbox {
        border: 1px solid var(--line);
        border-left: 3px solid var(--maroon);
        background: linear-gradient(180deg, #fff, #faf7f7);
        padding: 0;
        margin-top: .15rem;
        box-shadow: 0 2px 10px rgba(80,0,0,.04);
        overflow: visible;
    }
    .sx-teacher-papers .sx-qbox-body,
    .sx-teacher-papers #question-rows-main,
    .sx-teacher-papers #question-rows-modal,
    .sx-teacher-papers #question-rows-edit,
    .sx-teacher-papers #question-rows-edit-unified {
        overflow: visible;
        min-height: 0;
    }
    .sx-teacher-papers .sx-qbox-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .65rem 1rem;
        padding: .85rem 1rem;
        border-bottom: 1px solid #eadfdf;
        background: linear-gradient(180deg, #fff8f8, #fff);
    }
    .sx-teacher-papers .sx-qbox-head h6 {
        margin: 0;
        font-size: .92rem;
        font-weight: 800 !important;
        color: var(--maroon) !important;
        line-height: 1.3;
    }
    .sx-teacher-papers .sx-qbox-head .sx-q-total {
        display: inline-block;
        margin-left: .35rem;
        padding: .12rem .4rem;
        font-size: .72rem;
        font-weight: 800 !important;
        color: #940000 !important;
        background: #ffeaea;
        border: 1px solid #f0cfcf;
        vertical-align: middle;
    }
    .sx-teacher-papers .sx-qbox-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        align-items: center;
    }
    .sx-teacher-papers .sx-qbox-actions .btn {
        min-height: 34px;
        font-weight: 700 !important;
        border-radius: 0 !important;
    }
    .sx-teacher-papers .sx-qbox-body {
        padding: .85rem 1rem 1rem;
    }
    .sx-teacher-papers .sx-qbox-foot {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .4rem 1rem;
        margin-top: .75rem;
        padding-top: .7rem;
        border-top: 1px solid #eadfdf;
        font-size: .82rem;
        font-weight: 700 !important;
        color: #444 !important;
    }
    .sx-teacher-papers .sx-q-row,
    .sx-teacher-papers .optional-range-item,
    .modal .sx-q-row,
    .modal .optional-range-item {
        display: grid !important;
        gap: .55rem;
        align-items: end;
        padding: .7rem .75rem;
        margin: 0 0 .5rem;
        border: 1px solid #eadfdf;
        background: #fff;
        box-shadow: 0 1px 3px rgba(80,0,0,.03);
    }
    .sx-teacher-papers .sx-q-row,
    .modal .sx-q-row {
        grid-template-columns: 64px minmax(0, 1fr) 110px 100px 44px;
    }
    .sx-teacher-papers .optional-range-item,
    .modal .optional-range-item {
        grid-template-columns: minmax(0, 1.2fr) minmax(0, .9fr) 44px;
        margin-bottom: .55rem;
    }
    .sx-teacher-papers .sx-q-row > [class*="col-"],
    .sx-teacher-papers .optional-range-item > [class*="col-"],
    .modal .sx-q-row > [class*="col-"],
    .modal .optional-range-item > [class*="col-"] {
        width: auto !important;
        max-width: none !important;
        flex: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .sx-teacher-papers .sx-q-row .form-label,
    .sx-teacher-papers .optional-range-item .form-label,
    .modal .sx-q-row .form-label,
    .modal .optional-range-item .form-label {
        margin-bottom: .25rem;
        font-size: .7rem;
        font-weight: 800 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #666 !important;
    }
    .sx-teacher-papers .sx-q-row .form-control,
    .sx-teacher-papers .optional-range-item .form-control,
    .modal .sx-q-row .form-control,
    .modal .optional-range-item .form-control {
        min-height: 40px;
        font-size: .88rem;
        border-radius: 0 !important;
    }
    .sx-teacher-papers .sx-q-row .btn,
    .sx-teacher-papers .optional-range-item .btn,
    .modal .sx-q-row .btn,
    .modal .optional-range-item .btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0 !important;
    }
    .sx-teacher-papers .optional-ranges-wrapper:empty,
    .modal .optional-ranges-wrapper:empty { display: none; }
    .sx-teacher-papers .optional-ranges-wrapper:not(:empty),
    .modal .optional-ranges-wrapper:not(:empty) { margin-bottom: .65rem; }
    .modal .sx-qbox {
        border: 1px solid #eadfdf;
        border-left: 3px solid #940000;
        background: #fff;
        padding: 0;
        overflow: hidden;
    }
    .modal .sx-qbox-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .65rem 1rem;
        padding: .85rem 1rem;
        border-bottom: 1px solid #eadfdf;
        background: #fff8f8;
    }
    .modal .sx-qbox-head h6 {
        margin: 0;
        font-size: .92rem;
        font-weight: 800 !important;
        color: #940000 !important;
    }
    .modal .sx-qbox-head .sx-q-total {
        display: inline-block;
        margin-left: .35rem;
        padding: .12rem .4rem;
        font-size: .72rem;
        font-weight: 800 !important;
        color: #940000 !important;
        background: #ffeaea;
        border: 1px solid #f0cfcf;
    }
    .modal .sx-qbox-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }
    .modal .sx-qbox-body { padding: .85rem 1rem 1rem; }
    .modal .sx-qbox-foot {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .4rem 1rem;
        margin-top: .75rem;
        padding-top: .7rem;
        border-top: 1px solid #eadfdf;
        font-size: .82rem;
        font-weight: 700 !important;
    }
    @media (max-width: 767.98px) {
        .sx-teacher-papers .sx-q-row,
        .modal .sx-q-row {
            grid-template-columns: 64px 1fr 44px;
        }
        .sx-teacher-papers .sx-q-row .sx-q-desc,
        .modal .sx-q-row .sx-q-desc { grid-column: 1 / -1; order: 2; }
        .sx-teacher-papers .sx-q-row .sx-q-marks,
        .modal .sx-q-row .sx-q-marks { order: 3; }
        .sx-teacher-papers .sx-q-row .sx-q-opt,
        .modal .sx-q-row .sx-q-opt { order: 4; }
        .sx-teacher-papers .sx-q-row .sx-q-remove,
        .modal .sx-q-row .sx-q-remove { order: 1; justify-self: end; }
        .sx-teacher-papers .optional-range-item,
        .modal .optional-range-item {
            grid-template-columns: 1fr 44px;
        }
    }

    .sx-teacher-papers .sx-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: .35rem;
        padding-top: .85rem;
        border-top: 1px dashed #e5d6d6;
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
        padding: .55rem 1.1rem;
        box-shadow: 0 4px 12px rgba(148,0,0,.22);
    }
    .btn-primary-custom *,
    .btn-primary-custom:hover * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        font-weight: 700 !important;
    }
    .btn-primary-custom:hover {
        background: linear-gradient(180deg, #940000 0%, #7b0000 100%) !important;
        border-color: #6e0000 !important;
        box-shadow: 0 6px 16px rgba(148,0,0,.28);
    }
    .sx-teacher-papers .btn-secondary {
        min-height: 42px;
        font-weight: 700 !important;
        border: 1px solid #d0c4c4 !important;
        background: #fff !important;
        color: #444 !important;
    }
    .sx-teacher-papers .btn-secondary:hover {
        border-color: #940000 !important;
        color: #940000 !important;
        background: #fff8f8 !important;
    }

    .btn-exam-action {
        background: #fff !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: .35rem .7rem;
        font-size: .78rem;
        font-weight: 700 !important;
        border-radius: 0 !important;
        transition: background .15s ease, box-shadow .15s ease;
    }
    .btn-exam-action:hover {
        background: #fff5f5 !important;
        box-shadow: 0 2px 8px rgba(148,0,0,.12);
    }

    .bg-primary-custom { background: #fff5f5 !important; color: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .border-primary-custom { border-color: #940000 !important; }

    #papersFilterBar {
        border: 1px solid var(--line) !important;
        background: linear-gradient(180deg, #fff, var(--soft));
        margin-bottom: 1rem;
        padding: .95rem 1.05rem;
        box-shadow: 0 1px 4px rgba(80,0,0,.04);
    }
    #papersFilterBar .card-body { padding: 0 !important; }

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
        color: var(--ink) !important;
        padding: .8rem;
        border-bottom: 1px solid #f0e8e8;
        vertical-align: middle;
        line-height: 1.4;
        background: #fff;
    }
    table.sx-ep-table tbody tr:hover td {
        background: #fffafa;
    }
    table.sx-ep-table tr:last-child td { border-bottom: 0; }
    .sx-ep-title { color: var(--maroon) !important; font-weight: 800 !important; font-size: .94rem; }
    .sx-ep-meta { display: block; margin-top: .14rem; font-size: .8rem; color: var(--muted) !important; font-weight: 600 !important; }
    .sx-ep-actions { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; }
    .sx-ep-empty {
        padding: 2.4rem 1rem;
        text-align: center;
        color: var(--muted) !important;
        font-weight: 600 !important;
        background:
            radial-gradient(circle at top, rgba(148,0,0,.05), transparent 55%);
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

    .tag {
        display: inline-block;
        padding: .2rem .45rem;
        font-size: .7rem;
        font-weight: 700 !important;
        border-radius: 0;
        line-height: 1.3;
        letter-spacing: .02em;
    }
    .tag-approved, .badge-status-approved { background: #e8f6ec !important; color: #146c2e !important; border: 1px solid #c6e6cf; }
    .tag-rejected, .badge-status-rejected { background: #fdecec !important; color: #a11919 !important; border: 1px solid #f0c7c7; }
    .tag-pending, .badge-status-wait-approval, .badge-status-pending, .badge-status-wait_approval { background: #fff3cd !important; color: #7a5a00 !important; border: 1px solid #edd89a; }
    .tag-type { background: #f8eaea; color: #940000 !important; border: 1px solid #ecd0d0; }

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
    html[data-sx-theme="dark"] .sx-teacher-papers,
    html[data-bs-theme="dark"] .sx-teacher-papers {
        --line: #3a2a2e;
        --soft: #2a1c20;
        --muted: #d7c4c8;
        --ink: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-panel,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers #examPapersTabs,
    html[data-bs-theme="dark"] .sx-teacher-papers #examPapersTabs {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link,
    html[data-bs-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        border-top-color: #4a383c !important;
        color: #e8c9cd !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link i,
    html[data-bs-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link:hover,
    html[data-bs-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link:hover,
    html[data-sx-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-teacher-papers #examPapersTabs .nav-link.active {
        background: #2a1c20 !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .form-label,
    html[data-bs-theme="dark"] .sx-teacher-papers .form-label,
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-q-row .form-label,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-q-row .form-label,
    html[data-sx-theme="dark"] .modal .sx-q-row .form-label,
    html[data-bs-theme="dark"] .modal .sx-q-row .form-label,
    html[data-sx-theme="dark"] .modal .optional-range-item .form-label,
    html[data-bs-theme="dark"] .modal .optional-range-item .form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .form-control,
    html[data-bs-theme="dark"] .sx-teacher-papers .form-control,
    html[data-sx-theme="dark"] .sx-teacher-papers .form-select,
    html[data-bs-theme="dark"] .sx-teacher-papers .form-select,
    html[data-sx-theme="dark"] .modal .form-control,
    html[data-bs-theme="dark"] .modal .form-control,
    html[data-sx-theme="dark"] .modal .form-select,
    html[data-bs-theme="dark"] .modal .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-papers .form-select option,
    html[data-sx-theme="dark"] .modal .form-select option,
    html[data-bs-theme="dark"] .modal .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .form-text,
    html[data-bs-theme="dark"] .sx-teacher-papers .form-text,
    html[data-sx-theme="dark"] .sx-teacher-papers .text-muted,
    html[data-bs-theme="dark"] .sx-teacher-papers .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-upload-surface,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-upload-surface,
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-qbox,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-qbox,
    html[data-sx-theme="dark"] .modal .sx-qbox,
    html[data-bs-theme="dark"] .modal .sx-qbox {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-qbox-head,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-qbox-head,
    html[data-sx-theme="dark"] .modal .sx-qbox-head,
    html[data-bs-theme="dark"] .modal .sx-qbox-head {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-qbox-head h6,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-qbox-head h6 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-q-total,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-q-total {
        background: #3a1a1a !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-qbox-foot,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-qbox-foot {
        border-top-color: #3a2a2e !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .sx-q-row,
    html[data-bs-theme="dark"] .sx-teacher-papers .sx-q-row,
    html[data-sx-theme="dark"] .sx-teacher-papers .optional-range-item,
    html[data-bs-theme="dark"] .sx-teacher-papers .optional-range-item,
    html[data-sx-theme="dark"] .modal .sx-q-row,
    html[data-bs-theme="dark"] .modal .sx-q-row,
    html[data-sx-theme="dark"] .modal .optional-range-item,
    html[data-bs-theme="dark"] .modal .optional-range-item {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .btn-secondary,
    html[data-bs-theme="dark"] .sx-teacher-papers .btn-secondary {
        background: #24181c !important;
        border-color: #4a383c !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-papers .btn-secondary:hover,
    html[data-bs-theme="dark"] .sx-teacher-papers .btn-secondary:hover {
        background: #2a1c20 !important;
        border-color: #940000 !important;
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
    html[data-sx-theme="dark"] #papersFilterBar,
    html[data-bs-theme="dark"] #papersFilterBar {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
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
    html[data-sx-theme="dark"] .modal .text-primary-custom,
    html[data-bs-theme="dark"] .modal .text-primary-custom {
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
</style>
@endpush
@section('content')
<div class="sx-teacher-papers">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
                </div>
            @endif

            @if(isset($rejectionNotifications) && count($rejectionNotifications) > 0)
                @foreach($rejectionNotifications as $notification)
                    <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                        <i class="bi bi-x-circle"></i>
                        <strong>{{ __('teacher.exam_papers.exam_rejected') }}</strong> {{ $notification['message'] }}
                        @if(isset($notification['reason']))
                            <br><small><strong>{{ __('teacher.exam_papers.reason') }}</strong> {{ $notification['reason'] }}</small>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}" onclick="dismissNotification('{{ $notification['exam_name'] ?? '' }}')"></button>
                    </div>
                @endforeach
            @endif

            <div class="sx-panel">
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="examPapersTabs" role="tablist">
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button type="button" class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" role="tab" aria-controls="upload" aria-selected="true">
                        <i class="bi bi-cloud-upload"></i> <span>{{ __('teacher.exam_papers.upload') }}</span>
                    </button>
                </li>
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button type="button" class="nav-link" id="pending-uploads-tab" data-bs-toggle="tab" data-bs-target="#pending-uploads" role="tab" aria-controls="pending-uploads" aria-selected="false">
                        <i class="bi bi-clock-history"></i> <span>{{ __('teacher.pending') }}</span>
                        @if(isset($pendingSlots) && $pendingSlots->count() > 0)
                            <span class="badge bg-danger ms-1">{{ $pendingSlots->count() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button type="button" class="nav-link" id="approved-papers-tab" data-bs-toggle="tab" data-bs-target="#approved-papers" role="tab" aria-controls="approved-papers" aria-selected="false">
                        <i class="bi bi-check-circle"></i> <span>{{ __('teacher.approved') }}</span>
                        @php
                            $approvedCount = $myExamPapers ? $myExamPapers->filter(fn($p) => strtolower($p->status) == 'approved')->count() : 0;
                        @endphp
                        @if($approvedCount > 0)
                            <span class="badge bg-success ms-1">{{ $approvedCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button type="button" class="nav-link" id="rejected-papers-tab" data-bs-toggle="tab" data-bs-target="#rejected-papers" role="tab" aria-controls="rejected-papers" aria-selected="false">
                        <i class="bi bi-x-circle"></i> <span>{{ __('teacher.rejected') }}</span>
                        @php
                            $rejectedCount = $myExamPapers ? $myExamPapers->filter(fn($p) => strtolower($p->status) == 'rejected')->count() : 0;
                        @endphp
                        @if($rejectedCount > 0)
                            <span class="badge bg-danger ms-1" id="rejected-count-badge">{{ $rejectedCount }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button type="button" class="nav-link" id="pending-papers-tab" data-bs-toggle="tab" data-bs-target="#pending-papers" role="tab" aria-controls="pending-papers" aria-selected="false">
                        <i class="bi bi-clock"></i> <span>{{ __('teacher.exam_papers.waiting') }}</span>
                        @php
                            $waitCount = $myExamPapers ? $myExamPapers->filter(fn($p) => in_array(strtolower($p->status), ['wait_approval', 'pending']))->count() : 0;
                        @endphp
                        @if($waitCount > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ $waitCount }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <div class="sx-body">
            <!-- Shared Filter Bar for Approved/Rejected/Waiting tabs -->
            <div id="papersFilterBar" style="display:none;">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="search_my_papers">{{ __('teacher.search') }}</label>
                            <input type="text" class="form-control form-control-sm" id="search_my_papers" placeholder="{{ __('teacher.exam_papers.search_papers_ph') }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="filter_my_papers_exam">{{ __('teacher.exam_papers.exam') }}</label>
                            <select class="form-control form-control-sm" id="filter_my_papers_exam">
                                <option value="">{{ __('teacher.exam_papers.all_exams') }}</option>
                                @if($examinations)
                                    @foreach($examinations as $exam)
                                        <option value="{{ $exam->examID }}">{{ $exam->exam_name }} ({{ $exam->displayYearLabelAll() }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label" for="filter_my_papers_term">{{ __('teacher.exam_papers.term') }}</label>
                            <select class="form-control form-control-sm" id="filter_my_papers_term">
                                <option value="">{{ __('teacher.exam_papers.all_terms') }}</option>
                                <option value="term_1">{{ __('teacher.exam_papers.term_1') }}</option>
                                <option value="term_2">{{ __('teacher.exam_papers.term_2') }}</option>
                                <option value="term_3">{{ __('teacher.exam_papers.term_3') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label" for="filter_my_papers_year">{{ __('teacher.exam_papers.year') }}</label>
                            <select class="form-control form-control-sm" id="filter_my_papers_year">
                                <option value="">{{ __('teacher.exam_papers.all_years') }}</option>
                                @php
                                    $years = $myExamPapers ? $myExamPapers->pluck('examination.year')->unique()->filter()->sort()->reverse() : collect();
                                @endphp
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearPapersFilter" style="min-height:38px;">
                                <i class="bi bi-x-circle"></i> {{ __('teacher.exam_papers.clear') }}
                            </button>
                        </div>
                    </div>
            </div>

            <div class="tab-content" id="examPapersTabContent">
                <!-- Upload/Create Tab -->
                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                            <form id="uploadExamPaperForm" novalidate>
                                <input type="hidden" name="placeholder_id" id="placeholder_id">

                                <div class="sx-upload-surface">
                                <div class="row g-2 align-items-end mb-0">
                                    <div class="col-md-4">
                                        <label for="selected_exam" class="form-label">
                                            {{ __('teacher.exam_papers.examination') }} <span class="text-danger">*</span>
                                        </label>
                                        @if($examinations && $examinations->count() > 0)
                                            <select class="form-control" id="selected_exam" name="examID" required>
                                                <option value="">{{ __('teacher.exam_papers.select_examination') }}</option>
                                                @foreach($examinations as $exam)
                                                    <option value="{{ $exam->examID }}" data-allow-no-format="{{ $exam->allow_no_format ? '1' : '0' }}">
                                                        {{ $exam->exam_name }}
                                                        @if($exam->term)
                                                            - {{ ucfirst(str_replace('_', ' ', $exam->term)) }}
                                                        @endif
                                                        ({{ $exam->displayYearLabelAll() }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <select class="form-control" id="selected_exam" name="examID" required disabled>
                                                <option value="">{{ __('teacher.exam_papers.no_examinations') }}</option>
                                            </select>
                                            <div class="alert alert-warning mt-2 mb-0 py-2">
                                                {{ __('teacher.exam_papers.no_examinations_upload') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-4" id="regular_subject_container">
                                        <label for="class_subject" class="form-label">
                                            {{ __('teacher.subject') }} <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="class_subject" name="class_subjectID" required>
                                            <option value="">{{ __('teacher.exam_papers.select_subject') }}</option>
                                            @foreach($teacherSubjects as $subject)
                                                @php
                                                    $subjectName = $subject->subject->subject_name ?? 'N/A';
                                                    $className = $subject->class?->class_name
                                                        ?? $subject->subclass?->class?->class_name
                                                        ?? '';
                                                    $subclassName = $subject->subclass?->subclass_name ?? '';
                                                    $classDisplay = trim($className . ' ' . $subclassName);
                                                    $isDuo = !empty($subject->is_duo_subject);
                                                    $duoPapers = $subject->my_duo_papers ?? collect();
                                                    $subjectClassId = $subject->subclass?->classID
                                                        ?? $subject->classID
                                                        ?? $subject->class?->classID
                                                        ?? null;
                                                    $subjectOptionLabel = $subjectName
                                                        . ($isDuo ? ' [DUO]' : '')
                                                        . ($classDisplay !== '' ? ' (' . $classDisplay . ')' : '');
                                                @endphp
                                                <option value="{{ $subject->class_subjectID }}"
                                                        data-class-id="{{ $subjectClassId }}"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subclass-id="{{ $subject->subclass ? $subject->subclass->subclassID : '' }}"
                                                        data-is-duo="{{ $isDuo ? '1' : '0' }}"
                                                        data-duo-papers="{{ json_encode($duoPapers) }}">
                                                    {{ $subjectOptionLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="exam_file" class="form-label">
                                            {{ __('teacher.exam_papers.exam_paper_file') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" id="exam_file" name="file" accept=".pdf,.doc,.docx" required>
                                    </div>
                                </div>

                                <!-- Weekly/Monthly Test Fields (Hidden by default) -->
                                <div id="test_fields_container" class="mt-3" style="display: none;">
                                    <div class="row g-2 mb-0">
                                        <div class="col-md-4">
                                            <label for="test_year" class="form-label">
                                                {{ __('teacher.exam_papers.year') }} <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="test_year" name="test_year">
                                                <option value="">{{ __('teacher.exam_papers.select_year') }}</option>
                                                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="test_period" class="form-label">
                                                {{ __('teacher.exam_papers.period') }} <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="test_period" name="test_week">
                                                <option value="">{{ __('teacher.exam_papers.select_period') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="test_subject" class="form-label">
                                                {{ __('teacher.exam_papers.scheduled_subject') }} <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="test_subject" name="class_subjectID">
                                                <option value="">{{ __('teacher.exam_papers.select_period_first') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mt-3 mb-0" id="duo_paper_container" style="display: none;">
                                    <div class="col-md-4">
                                        <label for="component_id" class="form-label">
                                            {{ __('teacher.exam_papers.duo_paper') }} <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="component_id" name="componentID">
                                            <option value="">{{ __('teacher.exam_papers.select_paper_ph') }}</option>
                                        </select>
                                    </div>
                                </div>

                                @if(strtolower($schoolType ?? 'Secondary') === 'secondary')
                                    <div class="row g-2 mt-3 mb-0">
                                        <div class="col-md-4">
                                            <label for="existing_upload_main" class="form-label">
                                                {{ __('teacher.exam_papers.use_existing') }}
                                            </label>
                                            <select class="form-control" id="existing_upload_main">
                                                <option value="">{{ __('teacher.exam_papers.do_not_use_existing') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="sx-qbox mt-3" id="question-format-main">
                                            <div class="sx-qbox-head">
                                                <h6>
                                                    <i class="bi bi-list-check"></i> {{ __('teacher.exam_papers.question_formats') }}
                                                    <span class="sx-q-total">{{ __('teacher.exam_papers.total') }} <span class="format-expected-total-label">100</span></span>
                                                </h6>
                                                <div class="sx-qbox-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary add-optional-range" data-target="#question-rows-main">
                                                        <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.optional_range') }}
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-main">
                                                        <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.add_question') }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="sx-qbox-body">
                                                <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-main"></div>
                                                <div id="question-rows-main" data-expected-total="100"></div>
                                                <div class="sx-qbox-foot">
                                                    <span>
                                                        {{ __('teacher.exam_papers.total_marks') }}
                                                        <span class="total-marks" data-total-for="#question-rows-main">0</span>/<span class="format-total-max" data-total-for="#question-rows-main">100</span>
                                                    </span>
                                                    <div class="text-danger small marks-warning d-none mb-0" data-warning-for="#question-rows-main"></div>
                                                </div>
                                            </div>
                                    </div>
                                @endif

                                <div class="sx-actions-row">
                                        <button type="submit" class="btn btn-primary-custom text-white">
                                            <i class="bi bi-check-circle"></i> <span class="text-white">{{ __('teacher.exam_papers.submit_exam_paper') }}</span>
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> {{ __('teacher.exam_papers.reset') }}
                                        </button>
                                </div>
                                </div>
                            </form>
                </div>

                <!-- Pending Uploads Tab -->
                <div class="tab-pane fade" id="pending-uploads" role="tabpanel">
                    @if($pendingSlots && $pendingSlots->count() > 0)
                        <div class="sx-ep-table-wrap">
                            <table class="sx-ep-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('teacher.exam_papers.exam_subject') }}</th>
                                        <th>{{ __('teacher.class') }}</th>
                                        <th>{{ __('teacher.exam_papers.week') }}</th>
                                        <th>{{ __('teacher.exam_papers.test_date') }}</th>
                                        <th class="text-end">{{ __('teacher.exam_papers.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingSlots as $slot)
                                        @php
                                            $subjectName = $slot->classSubject?->subject?->subject_name ?? 'N/A';
                                            $classNameRaw = trim(
                                                ($slot->classSubject?->subclass?->class?->class_name
                                                    ?? $slot->classSubject?->class?->class_name
                                                    ?? '')
                                                . ' '
                                                . ($slot->classSubject?->subclass?->subclass_name ?? '')
                                            );
                                            $fullSubjectDisplay = $subjectName . ' - ' . ($classNameRaw !== '' ? $classNameRaw : 'N/A');
                                            $slotClassName = $slot->classSubject?->class?->class_name
                                                ?? $slot->classSubject?->subclass?->class?->class_name;
                                            $slotYearLabel = $slot->examination
                                                ? $slot->examination->displayYearLabelForClass($slotClassName)
                                                : '';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="sx-ep-title">{{ $slot->examination->exam_name }}</div>
                                                <span class="sx-ep-meta">{{ $subjectName }}@if($slotYearLabel) · {{ $slotYearLabel }}@endif</span>
                                            </td>
                                            <td>{{ trim($classNameRaw) ?: 'N/A' }}</td>
                                            <td>
                                                <span class="tag tag-type">{{ $slot->test_week }}</span>
                                                <span class="sx-ep-meta">{{ $slot->test_week_range }}</span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($slot->test_date)->format('D, d M Y') }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-primary-custom btn-sm text-white upload-pending-btn"
                                                    data-exam-id="{{ $slot->examID }}"
                                                    data-class-subject-id="{{ $slot->class_subjectID }}"
                                                    data-test-week="{{ $slot->test_week }}"
                                                    data-test-week-range="{{ $slot->test_week_range }}"
                                                    data-test-date="{{ $slot->test_date }}"
                                                    data-full-subject-display="{{ $fullSubjectDisplay }}"
                                                    data-slot-id="{{ $slot->exam_paperID }}">
                                                    <i class="bi bi-cloud-upload"></i> <span class="text-white">{{ __('teacher.exam_papers.upload_now') }}</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="sx-ep-empty">
                            <i class="bi bi-check-circle" style="color:#198754;"></i>
                            {{ __('teacher.exam_papers.no_pending_uploads') }}
                        </div>
                    @endif
                </div>

                <!-- My Exam Papers Tab -->
                <!-- Component: Papers View Macro / Partials -->
                @php
                    $approvedPapers     = $myExamPapers ? $myExamPapers->where('status', 'approved') : collect();
                    $rejectedPapers     = $myExamPapers ? $myExamPapers->where('status', 'rejected') : collect();
                    $waitApprovalPapers = $myExamPapers ? $myExamPapers->whereIn('status', ['wait_approval', 'pending']) : collect();

                    $renderPapers = function($papersCollection, $emptyText, $tabId) {
                        if ($papersCollection->count() === 0) {
                            return '<div class="sx-ep-empty"><i class="bi bi-file-earmark-x"></i>' . e(__('teacher.exam_papers.empty_found', ['what' => $emptyText])) . '</div>';
                        }

                        $html = '<div class="sx-ep-table-wrap"><table class="sx-ep-table"><thead><tr>
                                    <th>' . e(__('teacher.exam_papers.exam_subject')) . '</th>
                                    <th>' . e(__('teacher.class')) . '</th>
                                    <th>' . e(__('teacher.exam_papers.type')) . '</th>
                                    <th>' . e(__('teacher.status')) . '</th>
                                    <th>' . e(__('teacher.exam_papers.submitted')) . '</th>
                                    <th class="text-end">' . e(__('teacher.actions')) . '</th>
                                </tr></thead><tbody>';

                        foreach ($papersCollection as $paper) {
                            $status      = $paper->status;
                            $examName    = e($paper->examination->exam_name ?? __('teacher.exam_papers.na'));
                            $subjectName = e($paper->classSubject->subject->subject_name ?? __('teacher.exam_papers.na'));
                            if ($paper->component) {
                                $subjectName .= ' · ' . e($paper->component->component_name);
                            }
                            $uploadType  = e(ucfirst($paper->upload_type));
                            $paperId     = $paper->exam_paperID;
                            $examId      = $paper->examID;
                            $term        = e($paper->examination->term ?? '');
                            $year        = e($paper->displayYearLabel());
                            $testWeek    = e($paper->test_week ?? '');
                            $testDate    = $paper->test_date ? \Carbon\Carbon::parse($paper->test_date)->format('d M Y') : '';
                            $submitDate  = $paper->created_at->format('d M Y · H:i');
                            $filePath    = $paper->file_path;
                            $searchBlob  = strtolower(($paper->examination->exam_name ?? '') . ' ' . ($paper->classSubject->subject->subject_name ?? '') . ' ' . $uploadType);

                            $paperClassName = $paper->classSubject?->subclass?->class?->class_name
                                ?? $paper->classSubject?->class?->class_name
                                ?? __('teacher.exam_papers.na');
                            $paperSubclassName = $paper->classSubject?->subclass?->subclass_name ?? '';
                            $classDisplay = e(trim($paperClassName . ' ' . $paperSubclassName));

                            if ($status === 'wait_approval' || $status === 'pending') {
                                $stepName = e($paper->current_step_name ?? '');
                                $badgeLabel = ($status === 'pending' && $paper->current_approval_order && $stepName)
                                    ? __('teacher.exam_papers.awaiting', ['step' => $stepName])
                                    : __('teacher.exam_papers.wait_approval');
                                $statusClass = 'tag-pending';
                            } elseif ($status === 'approved') {
                                $badgeLabel = __('teacher.approved');
                                $statusClass = 'tag-approved';
                            } else {
                                $badgeLabel = __('teacher.rejected');
                                $statusClass = 'tag-rejected';
                            }

                            $note = '';
                            if ($status === 'rejected' && $paper->rejection_reason) {
                                $note = '<span class="sx-ep-meta" style="color:#a11919 !important;">' . e(__('teacher.exam_papers.reason_label')) . ' ' . e($paper->rejection_reason) . '</span>';
                            } elseif ($status === 'approved' && $paper->approval_comment) {
                                $note = '<span class="sx-ep-meta">' . e(__('teacher.exam_papers.comment_label')) . ' ' . e($paper->approval_comment) . '</span>';
                            }
                            $extraMeta = '';
                            if ($testWeek || $testDate) {
                                $extraMeta = '<span class="sx-ep-meta">' . trim(($testWeek ? $testWeek : '') . ($testWeek && $testDate ? ' · ' : '') . ($testDate ?: '')) . '</span>';
                            }

                            $actions = '<div class="sx-ep-actions">';
                            if ($status === 'rejected') {
                                $actions .= '<button type="button" class="btn btn-sm btn-danger edit-paper-btn"
                                                data-paper-id="' . $paperId . '"
                                                data-week="' . $testWeek . '"
                                                data-date="' . e($paper->test_date) . '"
                                                data-is-rejected="1">
                                                <i class="bi bi-pencil-square"></i> ' . e(__('teacher.exam_papers.edit_resend')) . '
                                            </button>';
                            } elseif (in_array($status, ['wait_approval', 'pending'])) {
                                $actions .= '<button type="button" class="btn btn-sm btn-primary-custom text-white edit-paper-btn"
                                                data-paper-id="' . $paperId . '"
                                                data-week="' . $testWeek . '"
                                                data-date="' . e($paper->test_date) . '">
                                                <i class="bi bi-pencil"></i> ' . e(__('teacher.exam_papers.edit')) . '
                                            </button>';
                            }
                            if ($paper->upload_type === 'upload' && $filePath) {
                                $downloadUrl = route('download_exam_paper', $paperId);
                                $actions .= '<a href="' . $downloadUrl . '" class="btn btn-exam-action btn-sm">
                                                <i class="bi bi-download"></i> ' . e(__('teacher.download')) . '
                                            </a>';
                            }
                            if ($status === 'approved') {
                                $actions .= '<button type="button" class="btn btn-sm btn-exam-action edit-questions-btn"
                                                data-paper-id="' . $paperId . '"
                                                data-week="' . $testWeek . '"
                                                data-date="' . e($paper->test_date) . '">
                                                <i class="bi bi-list-check"></i> ' . e(__('teacher.exam_papers.questions')) . '
                                            </button>';
                            }
                            if (in_array($status, ['pending', 'approved', 'rejected'])) {
                                $actions .= '<button type="button" class="btn btn-exam-action btn-sm btn-view-chain"
                                                data-paper-id="' . $paperId . '"
                                                data-exam-id="' . $examId . '">
                                                <i class="bi bi-list-ol"></i> ' . e(__('teacher.exam_papers.chain')) . '
                                            </button>';
                            }
                            $actions .= '</div>';

                            $html .= '<tr class="exam-paper-row"
                                     data-paper-id="' . $paperId . '"
                                     data-term="' . $term . '"
                                     data-year="' . $year . '"
                                     data-exam-id="' . $examId . '"
                                     data-search="' . e($searchBlob) . '">
                                    <td>
                                        <div class="sx-ep-title">' . $examName . '</div>
                                        <span class="sx-ep-meta">' . $subjectName . '</span>
                                        ' . $extraMeta . $note . '
                                    </td>
                                    <td>' . $classDisplay . '</td>
                                    <td><span class="tag tag-type">' . $uploadType . '</span></td>
                                    <td><span class="tag ' . $statusClass . '">' . $badgeLabel . '</span></td>
                                    <td>' . $submitDate . '</td>
                                    <td class="text-end">' . $actions . '</td>
                                </tr>';
                        }
                        $html .= '</tbody></table></div>';
                        return $html;
                    };
                @endphp

                
                <!-- Approved Papers Tab -->
                <div class="tab-pane fade" id="approved-papers" role="tabpanel">
                    <div id="approved_papers_list">
                        {!! $renderPapers($approvedPapers, __('teacher.exam_papers.empty_approved'), 'approved') !!}
                    </div>
                </div>

                <!-- Rejected Papers Tab -->
                <div class="tab-pane fade" id="rejected-papers" role="tabpanel">
                    <div id="rejected_papers_list">
                         {!! $renderPapers($rejectedPapers, __('teacher.exam_papers.empty_rejected'), 'rejected') !!}
                    </div>
                </div>

                <!-- Wait Approval Tab -->
                <div class="tab-pane fade" id="pending-papers" role="tabpanel">
                    <div id="pending_papers_list">
                         {!! $renderPapers($waitApprovalPapers, __('teacher.exam_papers.empty_pending'), 'pending') !!}
                    </div>
                </div>
            </div>{{-- tab-content --}}
            </div>{{-- sx-body --}}
            </div>{{-- sx-panel --}}
</div>{{-- sx-teacher-papers --}}

<!-- Upload Exam Paper Modal -->
<div class="modal fade" id="uploadExamPaperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary-custom rounded-0">
                <h5 class="modal-title" style="color:#940000;font-weight:800;">
                    <i class="bi bi-cloud-upload"></i> {{ __('teacher.exam_papers.upload_modal_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="modalUploadForm" novalidate>
                    <div class="form-group">
                        <label for="modal_selected_exam">{{ __('teacher.exam_papers.select_examination') }} <span class="text-danger">*</span></label>
                        @if($examinations && $examinations->count() > 0)
                            <select class="form-control" id="modal_selected_exam" name="examID" required>
                                <option value="">{{ __('teacher.exam_papers.select_examination') }}</option>
                                @foreach($examinations as $exam)
                                    <option value="{{ $exam->examID }}">
                                        {{ $exam->exam_name }}
                                        @if($exam->term)
                                            - {{ ucfirst(str_replace('_', ' ', $exam->term)) }}
                                        @endif
                                        ({{ $exam->displayYearLabelAll() }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <select class="form-control" id="modal_selected_exam" name="examID" required disabled>
                                <option value="">{{ __('teacher.exam_papers.no_examinations') }}</option>
                            </select>
                            <div class="alert alert-warning mt-2 mb-0 py-2">
                                {{ __('teacher.exam_papers.no_examinations_upload') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="modal_class_subject">{{ __('teacher.exam_papers.select_subject') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="modal_class_subject" name="class_subjectID" required>
                            <option value="">{{ __('teacher.exam_papers.select_subject') }}</option>
                            @foreach($teacherSubjects as $subject)
                                @php
                                    $subjectName = $subject->subject->subject_name ?? 'N/A';
                                    $className = $subject->class?->class_name
                                        ?? $subject->subclass?->class?->class_name
                                        ?? '';
                                    $subclassName = $subject->subclass?->subclass_name ?? '';
                                    $classDisplay = trim($className . ' ' . $subclassName);
                                    $isDuo = !empty($subject->is_duo_subject);
                                    $duoPapers = $subject->my_duo_papers ?? collect();
                                    $subjectClassId = $subject->subclass?->classID
                                        ?? $subject->classID
                                        ?? $subject->class?->classID
                                        ?? null;
                                    $subjectOptionLabel = $subjectName
                                        . ($isDuo ? ' [DUO]' : '')
                                        . ($classDisplay !== '' ? ' (' . $classDisplay . ')' : '');
                                @endphp
                                <option value="{{ $subject->class_subjectID }}"
                                        data-class-id="{{ $subjectClassId }}"
                                        data-subject-id="{{ $subject->subjectID }}"
                                        data-subclass-id="{{ $subject->subclass ? $subject->subclass->subclassID : '' }}"
                                        data-is-duo="{{ $isDuo ? '1' : '0' }}"
                                        data-duo-papers="{{ json_encode($duoPapers) }}">
                                    {{ $subjectOptionLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="modal_duo_paper_container" style="display: none;">
                        <label for="modal_component_id">{{ __('teacher.exam_papers.select_duo_paper') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="modal_component_id" name="componentID">
                            <option value="">{{ __('teacher.exam_papers.select_paper_ph') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_exam_file">{{ __('teacher.exam_papers.exam_paper_file_title') }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="modal_exam_file" name="file" accept=".pdf,.doc,.docx" required>
                    </div>

                        @if(strtolower($schoolType ?? 'Secondary') === 'secondary')
                        <div class="form-group mb-3">
                            <label for="existing_upload_modal">{{ __('teacher.exam_papers.use_existing_title') }}</label>
                            <select class="form-control" id="existing_upload_modal">
                                <option value="">{{ __('teacher.exam_papers.do_not_use_existing') }}</option>
                            </select>
                        </div>
                        <div class="sx-qbox mt-3" id="question-format-modal">
                            <div class="sx-qbox-head">
                                <h6>
                                    <i class="bi bi-list-check"></i> {{ __('teacher.exam_papers.question_formats') }}
                                    <span class="sx-q-total">{{ __('teacher.exam_papers.total') }} <span class="format-expected-total-label">100</span></span>
                                </h6>
                                <div class="sx-qbox-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-optional-range" data-target="#question-rows-modal">
                                        <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.optional_range') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-modal">
                                        <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.add_question') }}
                                    </button>
                                </div>
                            </div>
                            <div class="sx-qbox-body">
                                <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-modal"></div>
                                <div id="question-rows-modal" data-expected-total="100"></div>
                                <div class="sx-qbox-foot">
                                    <span>
                                        {{ __('teacher.exam_papers.total_marks') }}
                                        <span class="total-marks" data-total-for="#question-rows-modal">0</span>/<span class="format-total-max" data-total-for="#question-rows-modal">100</span>
                                    </span>
                                    <div class="text-danger small marks-warning d-none mb-0" data-warning-for="#question-rows-modal"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
                <button type="button" class="btn btn-primary-custom text-white" id="submitModalForm">
                    <i class="bi bi-check-circle"></i> {{ __('teacher.exam_papers.submit') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Exam Paper Modal -->
<div class="modal fade" id="editExamPaperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary-custom rounded-0">
                <h5 class="modal-title" style="color:#940000;font-weight:800;">
                    <i class="bi bi-pencil-square"></i> {{ __('teacher.exam_papers.edit_modal_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <form id="editExamPaperForm" novalidate>
                    <input type="hidden" id="edit_paper_id" name="exam_paperID">
                    <input type="hidden" id="edit_upload_type" name="upload_type">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_selected_exam" class="form-label">
                                <i class="bi bi-clipboard-check"></i> {{ __('teacher.exam_papers.select_examination') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="edit_selected_exam" name="examID" required>
                                <option value="">{{ __('teacher.exam_papers.select_examination') }}</option>
                                @foreach($examinations as $exam)
                                    <option value="{{ $exam->examID }}" data-allow-no-format="{{ $exam->allow_no_format ? '1' : '0' }}">
                                        {{ $exam->exam_name }} ({{ $exam->displayYearLabelAll() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Test Fields (Same as upload form) -->
                    <div id="edit_test_fields_container" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_test_year" class="form-label">
                                    <i class="bi bi-calendar"></i> {{ __('teacher.exam_papers.select_year') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="edit_test_year" name="test_year">
                                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_test_period" class="form-label">
                                    <i class="bi bi-calendar-week"></i> {{ __('teacher.exam_papers.select_period') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="edit_test_period" name="test_week">
                                    <option value="">{{ __('teacher.exam_papers.select_period') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_test_subject" class="form-label">
                                    <i class="bi bi-book"></i> {{ __('teacher.exam_papers.select_scheduled_subject') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="edit_test_subject" name="class_subjectID_test">
                                    <option value="">{{ __('teacher.exam_papers.select_period_first') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Regular Subject Selection -->
                    <div class="row" id="edit_regular_subject_container">
                        <div class="col-md-12 mb-3">
                            <label for="edit_class_subject" class="form-label">
                                <i class="bi bi-book"></i> {{ __('teacher.exam_papers.select_subject') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="edit_class_subject" name="class_subjectID">
                                <option value="">{{ __('teacher.exam_papers.select_subject') }}</option>
                                @foreach($teacherSubjects as $subject)
                                    @php
                                        $editSubjectName = $subject->subject->subject_name ?? 'N/A';
                                        $editClassName = $subject->class?->class_name
                                            ?? $subject->subclass?->class?->class_name
                                            ?? '';
                                        $editSubclassName = $subject->subclass?->subclass_name ?? '';
                                        $editClassDisplay = trim($editClassName . ' ' . $editSubclassName);
                                    @endphp
                                    <option value="{{ $subject->class_subjectID }}">
                                        {{ $editSubjectName }}@if($editClassDisplay !== '') ({{ $editClassDisplay }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_exam_file" class="form-label">
                                <i class="bi bi-file-earmark"></i> {{ __('teacher.exam_papers.exam_paper_file_title') }} <span class="text-danger">*</span>
                            </label>
                            <div id="current_file_info" class="mb-2 small text-info" style="display:none;"></div>
                            <input type="file" class="form-control" id="edit_exam_file" name="file" accept=".pdf,.doc,.docx">
                        </div>
                    </div>

                    @if(strtolower($schoolType ?? 'Secondary') === 'secondary')
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_existing_upload" class="form-label">
                                    <i class="bi bi-files"></i> {{ __('teacher.exam_papers.use_existing_title') }}
                                </label>
                                <select class="form-control" id="edit_existing_upload">
                                    <option value="">{{ __('teacher.exam_papers.do_not_use_existing') }}</option>
                                </select>
                            </div>
                        </div>
                        <div id="edit_question-format-container" class="sx-qbox mt-3" style="display:none;">
                            <div class="sx-qbox-head">
                                <h6>
                                    <i class="bi bi-list-check"></i> {{ __('teacher.exam_papers.question_formats') }}
                                    <span class="sx-q-total">{{ __('teacher.exam_papers.total') }} <span class="format-expected-total-label">100</span></span>
                                </h6>
                                <div class="sx-qbox-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-optional-range" data-target="#question-rows-edit-unified">
                                        <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.optional_range') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-edit-unified">
                                        <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.add_question') }}
                                    </button>
                                </div>
                            </div>
                            <div class="sx-qbox-body">
                                <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-edit-unified"></div>
                                <div id="question-rows-edit-unified" data-expected-total="100"></div>
                                <div class="sx-qbox-foot">
                                    <span>
                                        {{ __('teacher.exam_papers.total_marks') }}
                                        <span class="total-marks" id="edit_total_marks_display" data-total-for="#question-rows-edit-unified">0</span>/<span class="format-total-max" data-total-for="#question-rows-edit-unified">100</span>
                                    </span>
                                    <div class="text-danger small marks-warning d-none mb-0" data-warning-for="#question-rows-edit-unified" id="edit_marks_warning"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
                <button type="button" class="btn btn-primary-custom text-white" id="submitEditForm">
                    <i class="bi bi-check-circle"></i> {{ __('teacher.exam_papers.update_resubmit') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Exam Paper Questions Modal -->
<div class="modal fade" id="editExamPaperQuestionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary-custom rounded-0">
                <h5 class="modal-title" style="color:#940000;font-weight:800;">
                    <i class="bi bi-list-check"></i> {{ __('teacher.exam_papers.edit_questions_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="editExamPaperQuestionsForm">
                    <input type="hidden" id="edit_questions_paper_id" name="exam_paperID">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="edit_questions_test_week">{{ __('teacher.exam_papers.test_week') }}</label>
                                <input type="text" class="form-control" id="edit_questions_test_week" name="test_week" placeholder="{{ __('teacher.exam_papers.test_week_ph') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="edit_questions_test_date">{{ __('teacher.exam_papers.test_date_label') }}</label>
                                <input type="date" class="form-control" id="edit_questions_test_date" name="test_date">
                            </div>
                        </div>
                    </div>
                    <div class="sx-qbox">
                        <div class="sx-qbox-head">
                            <h6>
                                <i class="bi bi-list-check"></i> {{ __('teacher.exam_papers.question_formats') }}
                                <span class="sx-q-total">{{ __('teacher.exam_papers.total') }} <span class="format-expected-total-label">100</span></span>
                            </h6>
                            <div class="sx-qbox-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary add-optional-range" data-target="#question-rows-edit">
                                    <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.optional_range') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-edit">
                                    <i class="bi bi-plus-circle"></i> {{ __('teacher.exam_papers.add_question') }}
                                </button>
                            </div>
                        </div>
                        <div class="sx-qbox-body">
                            <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-edit"></div>
                            <div id="question-rows-edit" data-expected-total="100"></div>
                            <div class="sx-qbox-foot">
                                <span>
                                    {{ __('teacher.exam_papers.total_marks') }}
                                    <span class="total-marks" data-total-for="#question-rows-edit">0</span>/<span class="format-total-max" data-total-for="#question-rows-edit">100</span>
                                </span>
                                <div class="text-danger small marks-warning d-none mb-0" data-warning-for="#question-rows-edit"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
                <button type="button" class="btn btn-primary-custom text-white" id="submitEditQuestionsForm">
                    <i class="bi bi-check-circle"></i> <span class="text-white">{{ __('teacher.exam_papers.update_questions') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for View Chain -->
<div class="modal fade" id="viewChainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-primary-custom rounded-0">
                <h5 class="modal-title" style="color:#940000;font-weight:800;">{{ __('teacher.exam_papers.approval_chain') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <div id="chainTimelineContent">
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                        <p>{{ __('teacher.exam_papers.loading_chain') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .form-label,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .sx-q-row .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .sx-q-row .form-label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th,
html[data-bs-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .sx-ep-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .sx-ep-title {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .sx-ep-meta,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers .sx-ep-meta {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-approved,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-approved,
html[data-sx-theme="dark"] .sx-sa-content-wrap .badge-status-approved,
html[data-bs-theme="dark"] .sx-sa-content-wrap .badge-status-approved {
    background: #e8f6ec !important; color: #146c2e !important; -webkit-text-fill-color: #146c2e !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-rejected,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-rejected,
html[data-sx-theme="dark"] .sx-sa-content-wrap .badge-status-rejected,
html[data-bs-theme="dark"] .sx-sa-content-wrap .badge-status-rejected {
    background: #fdecec !important; color: #a11919 !important; -webkit-text-fill-color: #a11919 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-pending,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-pending,
html[data-sx-theme="dark"] .sx-sa-content-wrap .badge-status-wait-approval,
html[data-bs-theme="dark"] .sx-sa-content-wrap .badge-status-wait-approval,
html[data-sx-theme="dark"] .sx-sa-content-wrap .badge-status-pending,
html[data-bs-theme="dark"] .sx-sa-content-wrap .badge-status-pending,
html[data-sx-theme="dark"] .sx-sa-content-wrap .badge-status-wait_approval,
html[data-bs-theme="dark"] .sx-sa-content-wrap .badge-status-wait_approval {
    background: #fff3cd !important; color: #7a5a00 !important; -webkit-text-fill-color: #7a5a00 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .tag.tag-type,
html[data-bs-theme="dark"] .sx-sa-content-wrap .tag.tag-type {
    background: #f8eaea !important; color: #940000 !important; -webkit-text-fill-color: #940000 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers #examPapersTabs .nav-link,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers #examPapersTabs .nav-link {
    color: #e8c9cd !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers #examPapersTabs .nav-link.active,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers #examPapersTabs .nav-link.active,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers #examPapersTabs .nav-link.active span:not(.badge),
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-teacher-papers #examPapersTabs .nav-link.active span:not(.badge) {
    color: #ffb4b4 !important;
}
</style>
@endsection

@push('scripts')
<script>
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
window.sxShowTab = function(selector) {
    var node = document.querySelector(selector);
    if (!node) return;
    if (window.bootstrap && bootstrap.Tab) {
        bootstrap.Tab.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.tab) jQuery(node).tab('show');
};
</script>
<script>
jQuery(document).ready(function($) {
    var i18nEp = @json(__('teacher.exam_papers'));
    i18nEp.pending = @json(__('teacher.pending'));
    i18nEp.approved = @json(__('teacher.approved'));
    i18nEp.rejected = @json(__('teacher.rejected'));
    i18nEp.save = @json(__('teacher.save'));
    i18nEp.cancel = @json(__('teacher.cancel'));
    i18nEp.close = @json(__('teacher.close'));
    i18nEp.loading = @json(__('teacher.loading'));
    i18nEp.status = @json(__('teacher.status'));
    i18nEp.actions = @json(__('teacher.actions'));
    i18nEp.filter = @json(__('teacher.filter'));
    i18nEp.search = @json(__('teacher.search'));
    i18nEp.subject = @json(__('teacher.subject'));
    i18nEp.class = @json(__('teacher.class'));
    i18nEp.date = @json(__('teacher.date'));
    // Handle View Chain
    $('.btn-view-chain').on('click', function() {
        const paperId = $(this).data('paper-id');
        const examId = $(this).data('exam-id');

        sxShowModal('#viewChainModal');
        $('#chainTimelineContent').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i><p>{{ __('teacher.exam_papers.loading_chain') }}</p></div>');

        $.ajax({
            url: "{{ url('view_paper_approval_chain') }}/" + examId,
            method: 'GET',
            data: { paper_id: paperId },
            success: function(response) {
                let html = '<ul class="timeline">';

                // Approval Chain Config from Examination
                const chain = response.chain;
                const logs = response.logs || [];
                const currentOrder = response.current_order;

                chain.forEach(function(step) {
                    let statusClass = 'pending';
                    let statusText = i18nEp.pending;
                    let comment = '';
                    let approver = '';
                    let date = '';

                    // Check if we have a log entry for this step
                    const log = logs.find(l => l.approval_order === step.approval_order);

                    if (log) {
                        if (log.status === 'approved') {
                            statusClass = 'approved';
                            statusText = i18nEp.approved;
                            approver = log.approver ? log.approver.first_name + ' ' + log.approver.last_name : i18nEp.na;
                            date = new Date(log.updated_at).toLocaleString();
                            comment = log.comment ? '<div class="small mt-2 p-2 bg-light border-left border-success">" ' + log.comment + ' "</div>' : '';
                        } else if (log.status === 'rejected') {
                            statusClass = 'rejected';
                            statusText = i18nEp.rejected;
                            approver = log.approver ? log.approver.first_name + ' ' + log.approver.last_name : i18nEp.na;
                            date = new Date(log.updated_at).toLocaleString();
                            comment = log.comment ? '<div class="small mt-2 p-2 bg-light border-left border-danger">" ' + log.comment + ' "</div>' : '';
                        } else if (log.status === 'pending') {
                            statusClass = 'current';
                            statusText = i18nEp.awaiting_approval;
                        }
                    }

                    let roleName = step.special_role_type ?
                        step.special_role_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) :
                        (step.role ? step.role.name : i18nEp.unknown_role);

                    let contentHtml = '';
                    if (approver || date || comment) {
                        contentHtml = `
                            <div class="small text-muted">
                                ${approver ? i18nEp.by_approver.replace(':name', approver) + (date ? ' | ' + date : '') : i18nEp.progressing}
                            </div>
                            ${comment}
                        `;
                    } else {
                        contentHtml = `<div class="small text-muted">${i18nEp.progressing}</div>`;
                    }

                    html += `
                        <li class="timeline-item">
                            <div class="timeline-badge ${statusClass}"></div>
                            <div class="timeline-panel">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1 text-primary-custom font-weight-bold">${i18nEp.step_role.replace(':n', step.approval_order).replace(':role', roleName)}</h6>
                                    <span class="badge ${statusClass === 'approved' ? 'badge-success' : (statusClass === 'rejected' ? 'badge-danger' : 'badge-warning')} text-capitalize">${statusText}</span>
                                </div>
                                ${contentHtml}
                            </div>
                        </li>
                    `;
                });

                html += '</ul>';
                $('#chainTimelineContent').html(html);
            },
            error: function() {
                $('#chainTimelineContent').html('<div class="alert alert-danger">' + i18nEp.failed_chain + '</div>');
            }
        });
    });
    const examinations = @json($examinations ?? []);
    let allowedClassIds = [];
    let existingExamPapers = [];
    const isSecondarySchool = @json(strtolower($schoolType ?? 'Secondary')) === 'secondary';

    function getExpectedFormatTotal(targetId) {
        const id = targetId && String(targetId).startsWith('#') ? targetId : `#${targetId || ''}`;
        const $container = $(id);
        const fromData = parseInt($container.data('expected-total'), 10);
        if (!isNaN(fromData) && fromData > 0) {
            return fromData;
        }
        return 100;
    }

    function setExpectedFormatTotal(targetId, total) {
        const id = targetId && String(targetId).startsWith('#') ? targetId : `#${targetId}`;
        const expected = Math.max(1, parseInt(total, 10) || 100);
        const $container = $(id);
        $container.attr('data-expected-total', expected).data('expected-total', expected);

        const $box = $container.closest('.sx-qbox, .card');
        $box.find('.format-expected-total-label').text(expected);
        $(`.format-total-max[data-total-for="${id}"]`).text(expected);
        $container.find('.question-marks').attr('max', expected);
        $(`.optional-ranges-wrapper[data-wrapper-for="${id}"] .optional-total-input`).attr('max', Math.max(1, expected - 1));
        if ($container.find('.question-row').length) {
            updateTotalMarks($container);
        }
    }

    function resolveUploadExpectedTotal(isModal) {
        const $paperSelect = isModal ? $('#modal_component_id') : $('#component_id');
        if ($paperSelect.length && $paperSelect.is(':visible') && $paperSelect.val()) {
            const max = parseInt($paperSelect.find('option:selected').data('max-marks'), 10);
            if (!isNaN(max) && max > 0) {
                return max;
            }
        }
        return 100;
    }

    function syncUploadFormatTotal(isModal) {
        const target = isModal ? '#question-rows-modal' : '#question-rows-main';
        setExpectedFormatTotal(target, resolveUploadExpectedTotal(isModal));
    }

    function buildQuestionRow(targetId) {
        const optionalOptions = buildOptionalRangeOptions(targetId);
        const expectedTotal = getExpectedFormatTotal(targetId);
        return `
            <div class="sx-q-row question-row">
                <div class="sx-q-num">
                    <label class="form-label">${i18nEp.qn}</label>
                    <input type="text" class="form-control question-number" readonly>
                </div>
                <div class="sx-q-desc">
                    <label class="form-label">${i18nEp.description}</label>
                    <input type="text" class="form-control question-description" name="question_descriptions[]" required>
                </div>
                <div class="sx-q-marks">
                    <label class="form-label">${i18nEp.marks}</label>
                    <input type="number" class="form-control question-marks" name="question_marks[]" min="1" max="${expectedTotal}" required>
                </div>
                <div class="sx-q-opt">
                    <label class="form-label">${i18nEp.opt}</label>
                    <select class="form-control question-optional" name="question_optional[]">
                        ${optionalOptions}
                    </select>
                </div>
                <div class="sx-q-remove">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-question-row" title="${i18nEp.remove}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    function appendQuestionRow(target) {
        const targetId = target && String(target).startsWith('#') ? String(target) : ('#' + String(target || '').replace(/^#/, ''));
        const $container = $(targetId);
        if (!$container.length) {
            console.warn('Add Question: container not found', targetId);
            return false;
        }
        $container.append(buildQuestionRow(targetId));
        refreshQuestionNumbers($container);
        updateTotalMarks($container);
        toggleRemoveButtons($container);
        const $box = $container.closest('.sx-qbox');
        if ($box.length && $box.is(':hidden')) {
            $box.show();
        }
        return true;
    }

    // Expose for early native click binder
    window.sxExamPaperAppendQuestion = appendQuestionRow;
    window.sxExamPaperBuildQuestionRow = buildQuestionRow;

    function buildOptionalRangeOptions(targetId) {
        const ranges = getOptionalRanges(targetId);
        let options = '<option value="0">' + i18nEp.opt_no + '</option>';
        ranges.forEach(function(range) {
            options += `<option value="${range.number}">${i18nEp.opt_n.replace(':n', range.number)}</option>`;
        });
        return options;
    }

    function getOptionalRanges(targetId) {
        const ranges = [];
        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            if (!isNaN(rangeNumber)) {
                ranges.push({ number: rangeNumber });
            }
        });
        return ranges;
    }

    function refreshOptionalRangeSelects(targetId) {
        const options = buildOptionalRangeOptions(targetId);
        $(`${targetId} .question-optional`).each(function() {
            const current = $(this).val();
            $(this).html(options);
            if (current && $(this).find(`option[value="${current}"]`).length > 0) {
                $(this).val(current);
            }
        });
    }

    function refreshQuestionNumbers($container) {
        $container.find('.question-row').each(function(index) {
            $(this).find('.question-number').val(index + 1);
        });
    }

    function updateTotalMarks($container) {
        const targetId = '#' + $container.attr('id');
        const expectedTotal = getExpectedFormatTotal(targetId);
        const $totalEl = $(`.total-marks[data-total-for="${targetId}"]`);
        const $warningEl = $(`.marks-warning[data-warning-for="${targetId}"]`);
        const optionalTotals = getOptionalTotals(targetId);
        let total = 0;
        let optionalSumByRange = {};

        $container.find('.question-row').each(function() {
            const value = parseInt($(this).find('.question-marks').val(), 10);
            const optionalRange = parseInt($(this).find('.question-optional').val(), 10);
            if (!isNaN(value)) {
                if (optionalRange > 0) {
                    optionalSumByRange[optionalRange] = (optionalSumByRange[optionalRange] || 0) + value;
                } else {
                    total += value;
                }
            }
        });

        const requiredTotal = total;
        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const requiredMax = expectedTotal - optionalTotalSum;
        const overallTotal = requiredTotal + optionalTotalSum;

        $totalEl.text(overallTotal);
        $(`.format-total-max[data-total-for="${targetId}"]`).text(expectedTotal);

        const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
            const rangeTotal = optionalTotals[range];
            const sum = optionalSumByRange[range] || 0;
            return sum < rangeTotal;
        });

        if (optionalTotalSum > expectedTotal) {
            $warningEl.text(`Optional range totals exceed ${expectedTotal}. Reduce optional totals.`).removeClass('d-none');
        } else if (optionalRangeMismatch) {
            $warningEl.text(i18nEp.optional_range_warn).removeClass('d-none');
        } else if (requiredTotal > requiredMax) {
            $warningEl.text(i18nEp.err_required_exceed).removeClass('d-none');
        } else if (overallTotal > expectedTotal) {
            $warningEl.text(`Marks exceed ${expectedTotal}. Please reduce marks.`).removeClass('d-none');
        } else if (overallTotal < expectedTotal) {
            $warningEl.text(`Marks are less than ${expectedTotal}. Add questions or update marks.`).removeClass('d-none');
        } else {
            $warningEl.addClass('d-none').text('');
        }
    }

    function ensureAtLeastOneRow($container) {
        if ($container.find('.question-row').length === 0) {
            $container.append(buildQuestionRow('#' + $container.attr('id')));
        }
        refreshQuestionNumbers($container);
        updateTotalMarks($container);
        toggleRemoveButtons($container);
    }

    function toggleRemoveButtons($container) {
        const rowCount = $container.find('.question-row').length;
        $container.find('.remove-question-row').prop('disabled', rowCount <= 1);
    }

    function getOptionalTotals(targetId) {
        const totals = {};
        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            const total = parseInt($(this).find('.optional-total-input').val(), 10);
            if (!isNaN(rangeNumber) && !isNaN(total)) {
                totals[rangeNumber] = total;
            }
        });
        return totals;
    }

    function getOptionalRequiredCounts(targetId) {
        const counts = {};
        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            const requiredCount = parseInt($(this).find('.optional-required-input').val(), 10);
            if (!isNaN(rangeNumber) && !isNaN(requiredCount)) {
                counts[rangeNumber] = requiredCount;
            }
        });
        return counts;
    }

    function addOptionalRange(targetId) {
        const $wrapper = $(`.optional-ranges-wrapper[data-wrapper-for="${targetId}"]`);
        const existing = $wrapper.find('.optional-range-item').length;
        const rangeNumber = existing + 1;
        const expectedTotal = getExpectedFormatTotal(targetId);
        const html = `
            <div class="optional-range-item" data-wrapper-for="${targetId}" data-range-number="${rangeNumber}">
                <div>
                    <label class="form-label">Optional Range ${rangeNumber} Total</label>
                    <input type="number" class="form-control optional-total-input" min="1" max="${Math.max(1, expectedTotal - 1)}" placeholder="${i18nEp.optional_total_ph}">
                    <div class="text-danger small optional-total-error d-none">Optional totals exceed ${expectedTotal}.</div>
                </div>
                <div>
                    <label class="form-label">${i18nEp.required_questions}</label>
                    <input type="number" class="form-control optional-required-input" min="1" placeholder="${i18nEp.required_count_ph}">
                    <div class="text-danger small optional-required-error d-none">${i18nEp.required_exceeds}</div>
                </div>
                <div>
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-optional-range" title="${i18nEp.remove}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $wrapper.append(html);
        refreshOptionalRangeSelects(targetId);
        updateTotalMarks($(targetId));
    }

    // Bind Add Question early so it still works even if later init throws
    $(document).off('click.sxAddQuestion', '.add-question-row').on('click.sxAddQuestion', '.add-question-row', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const target = $(this).attr('data-target') || $(this).data('target');
        appendQuestionRow(target);
    });

    if (isSecondarySchool) {
        ensureAtLeastOneRow($('#question-rows-main'));
        ensureAtLeastOneRow($('#question-rows-modal'));
    }

    // Dismiss notification
    window.dismissNotification = function(examName) {
        // Remove notification from session via AJAX
        $.ajax({
            url: '/dismiss_exam_rejection_notification',
            method: 'POST',
            data: {
                exam_name: examName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                // Remove alert from DOM
                $('.alert').filter(function() {
                    return $(this).text().includes(examName);
                }).fadeOut();
            }
        });
    };


    function filterSubjectsByAllowedClasses(selectId) {
        const $select = $(selectId);
        let visibleCount = 0;

        $select.find('option').each(function(index) {
            if (index === 0) {
                $(this).prop('disabled', false).prop('hidden', false);
                return;
            }

            const classId = $(this).data('class-id');
            const isAllowed = allowedClassIds.length === 0 || (classId && allowedClassIds.includes(classId));

            $(this).data('allowed', isAllowed);
            $(this).prop('disabled', !isAllowed).prop('hidden', !isAllowed);
            if (isAllowed) {
                visibleCount++;
            }
        });

        if (visibleCount === 0) {
            $select.val('');
        }
    }

    function parseDuoPapersFromOption($option) {
        let papers = $option.attr('data-duo-papers');
        try {
            papers = papers ? JSON.parse(papers) : [];
        } catch (e) {
            papers = [];
        }
        return Array.isArray(papers) ? papers : [];
    }

    function isPaperAlreadyUploaded(classSubjectID, componentID) {
        return existingExamPapers.some(function(paper) {
            if (String(paper.class_subjectID) !== String(classSubjectID) || paper.status === 'rejected') {
                return false;
            }
            if (componentID === null || componentID === undefined || componentID === '') {
                return !paper.componentID;
            }
            return String(paper.componentID) === String(componentID);
        });
    }

    function applyExistingUploadStatus(selectId) {
        const $select = $(selectId);
        $select.find('option').each(function(index) {
            if (index === 0) {
                return;
            }
            const $option = $(this);
            const originalText = $option.data('original-text') || $option.text();
            if (!$option.data('original-text')) {
                $option.data('original-text', originalText);
            }

            const classSubjectID = String($option.val());
            const isDuo = $option.data('is-duo') == 1 || $option.data('is-duo') === true;
            const duoPapers = parseDuoPapersFromOption($option);
            const allowed = $option.data('allowed') !== false;

            if (isDuo && duoPapers.length) {
                const uploadedCount = duoPapers.filter(function(p) {
                    return isPaperAlreadyUploaded(classSubjectID, p.componentID);
                }).length;
                const allDone = uploadedCount === duoPapers.length;

                if (allDone) {
                    $option.text(`${originalText} - All papers uploaded`);
                    $option.prop('disabled', true).addClass('text-muted');
                } else if (uploadedCount > 0) {
                    $option.text(`${originalText} - ${uploadedCount}/${duoPapers.length} papers uploaded`);
                    $option.removeClass('text-muted');
                    if (allowed && !$option.prop('hidden')) {
                        $option.prop('disabled', false);
                    }
                } else {
                    $option.text(originalText);
                    $option.removeClass('text-muted');
                    if (allowed && !$option.prop('hidden')) {
                        $option.prop('disabled', false);
                    }
                }
            } else {
                const already = isPaperAlreadyUploaded(classSubjectID, null)
                    || existingExamPapers.find(paper => String(paper.class_subjectID) === classSubjectID && paper.status !== 'rejected');

                if (already) {
                    $option.text(`${originalText} - Already uploaded`);
                    $option.prop('disabled', true).addClass('text-muted');
                } else {
                    $option.text(originalText);
                    $option.removeClass('text-muted');
                    if (allowed && !$option.prop('hidden')) {
                        $option.prop('disabled', false);
                    }
                }
            }
        });

        const selectedOption = $select.find('option:selected');
        if (selectedOption.length && selectedOption.prop('disabled')) {
            $select.val('');
        }

        // Refresh duo paper options if a subject is currently selected
        if ($select.is('#class_subject') || $select.is('#test_subject')) {
            updateDuoPaperSelect(false);
        } else if ($select.is('#modal_class_subject')) {
            updateDuoPaperSelect(true);
        }
    }

    function refreshExistingUploadOptions(isModal) {
        const $subjectSelect = isModal ? $('#modal_class_subject') : $('#class_subject');
        const $examSelect = isModal ? $('#modal_selected_exam') : $('#selected_exam');
        const $reuseSelect = isModal ? $('#existing_upload_modal') : $('#existing_upload_main');

        if (!isSecondarySchool || $reuseSelect.length === 0) {
            return;
        }

        const examID = $examSelect.val();
        const classSubjectID = $subjectSelect.val();
        const $selectedOption = $subjectSelect.find('option:selected');
        const subjectID = $selectedOption.data('subject-id');
        const classID = $selectedOption.data('class-id');

        $reuseSelect.empty().append('<option value="">{{ __('teacher.exam_papers.do_not_use_existing') }}</option>');

        if (!examID || !classSubjectID || !subjectID || !classID) {
            $reuseSelect.prop('disabled', true);
            return;
        }

        const matches = existingExamPapers.filter(paper => {
            return String(paper.subjectID) === String(subjectID)
                && String(paper.class_subjectID) !== String(classSubjectID)
                && paper.status !== 'rejected';
        });

        matches.forEach(paper => {
            let weekInfo = '';
            if (paper.test_week) {
                weekInfo = ` - ${paper.test_week}`;
                if (paper.test_date) weekInfo += ` (${paper.test_date})`;
            }
            const label = `${paper.class_display}${weekInfo} (${paper.status})`;
            $reuseSelect.append(`<option value="${paper.exam_paperID}">${label}</option>`);
        });

        $reuseSelect.prop('disabled', matches.length === 0);
    }

    function applyExistingQuestions(targetId, data) {
        const $container = $(targetId);
        const $wrapper = $(`.optional-ranges-wrapper[data-wrapper-for="${targetId}"]`);

        $wrapper.empty();
        $container.empty();

        if (Array.isArray(data.optional_ranges)) {
            const sortedRanges = data.optional_ranges.slice().sort(function(a, b) {
                return (a.range_number || 0) - (b.range_number || 0);
            });
            sortedRanges.forEach(function(range) {
                addOptionalRange(targetId);
                const $lastRange = $wrapper.find('.optional-range-item').last();
                $lastRange.find('.optional-total-input').val(range.total_marks);
                if (range.required_questions !== undefined && range.required_questions !== null) {
                    $lastRange.find('.optional-required-input').val(range.required_questions);
                }
            });
        }

        if (Array.isArray(data.questions)) {
            data.questions.forEach(function(question) {
                $container.append(buildQuestionRow(targetId));
                const $row = $container.find('.question-row').last();
                $row.find('.question-description').val(question.question_description);
                $row.find('.question-marks').val(question.marks);
                $row.find('.question-optional').val(question.optional_range_number ? String(question.optional_range_number) : '0');
            });
        }

        refreshQuestionNumbers($container);
        updateTotalMarks($container);
        toggleRemoveButtons($container);
    }

    function fetchExistingPapers(examID) {
        existingExamPapers = [];

        if (!examID) {
            applyExistingUploadStatus('#class_subject');
            applyExistingUploadStatus('#modal_class_subject');
            refreshExistingUploadOptions(false);
            refreshExistingUploadOptions(true);
            return;
        }

        $.ajax({
            url: `/teacher/get-exam-paper-summary/${examID}`,
            method: 'GET',
            success: function(response) {
                if (response.success && Array.isArray(response.exam_papers)) {
                    existingExamPapers = response.exam_papers;
                } else {
                    existingExamPapers = [];
                }
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
                refreshExistingUploadOptions(false);
                refreshExistingUploadOptions(true);
            },
            error: function() {
                existingExamPapers = [];
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
                refreshExistingUploadOptions(false);
                refreshExistingUploadOptions(true);
            }
        });
    }

    function fetchAllowedClasses(examID) {
        allowedClassIds = [];

        if (!examID) {
            filterSubjectsByAllowedClasses('#class_subject');
            filterSubjectsByAllowedClasses('#modal_class_subject');
            applyExistingUploadStatus('#class_subject');
            applyExistingUploadStatus('#modal_class_subject');
            return;
        }

        $.ajax({
            url: `/get_exam_allowed_classes/${examID}`,
            method: 'GET',
            success: function(response) {
                if (response.success && Array.isArray(response.allowed_class_ids)) {
                    allowedClassIds = response.allowed_class_ids.map(id => parseInt(id, 10));
                } else {
                    allowedClassIds = [];
                }

                filterSubjectsByAllowedClasses('#class_subject');
                filterSubjectsByAllowedClasses('#modal_class_subject');
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
            },
            error: function() {
                allowedClassIds = [];
                filterSubjectsByAllowedClasses('#class_subject');
                filterSubjectsByAllowedClasses('#modal_class_subject');
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
            }
        });
    }

    // Handle "Upload Now" from pending slots
    $(document).on('click', '.upload-pending-btn', function() {
        const examID = $(this).data('exam-id');
        const classSubjectID = $(this).data('class-subject-id');
        const testWeek = $(this).data('test-week');
        const testDate = $(this).data('test-date');
        const slotID = $(this).data('slot-id');

        // Reset form first
        $('#uploadExamPaperForm')[0].reset();
        $('#placeholder_id').val(slotID);

        // Set Exam - this will trigger the change event that shows test fields
        $('#selected_exam').val(examID);

        // Manually trigger the exam change logic
        const examName = $('#selected_exam').find('option:selected').text();
        const isWeeklyTest = examName.includes('Weekly Test');
        const isMonthlyTest = examName.includes('Monthly Test');
        const isTestType = isWeeklyTest || isMonthlyTest;

        if (isTestType) {
            // Show test fields, hide regular subject selection
            $('#test_fields_container').show();
            $('#regular_subject_container').hide();
            $('#class_subject').prop('required', false);
            $('#test_subject').prop('required', true);

            // Load available periods for the current year
            const currentYear = $('#test_year').val() || new Date().getFullYear();
            const fullSubjectDisplay = $(this).data('full-subject-display');
            const testWeekRange = $(this).data('test-week-range');
            const displayWeek = testWeekRange ? `${testWeek} (${testWeekRange})` : testWeek;

            // DIRECTLY SET VALUES FIRST (Instant Feedback)

            // 1. Set Period (Test Week)
            const $periodSelect = $('#test_period');
            // Ensure option exists
            if ($periodSelect.find(`option[value="${testWeek}"]`).length === 0) {
                 // Clear existing if it only has 'Select Period' to avoid clutter or duplicates if logic differs
                 if ($periodSelect.children().length <= 1) $periodSelect.html('<option value="">{{ __('teacher.exam_papers.select_period') }}</option>');
                 $periodSelect.append(`<option value="${testWeek}">${displayWeek}</option>`);
            }
            $periodSelect.val(testWeek);

            // 2. Set Subject
            const $subjectSelect = $('#test_subject');
            if ($subjectSelect.find(`option[value="${classSubjectID}"]`).length === 0) {
                 if ($subjectSelect.children().length <= 1) $subjectSelect.html('<option value="">{{ __('teacher.exam_papers.select_subject') }}</option>');
                 $subjectSelect.append(`<option value="${classSubjectID}">${fullSubjectDisplay}</option>`);
            }
            $subjectSelect.val(classSubjectID);

            // 3. Set Date
            $('#test_date').val(testDate);

            // BACKGROUND LOAD (To populate other options)
            // Load periods
            $.ajax({
                url: '/get_available_periods',
                method: 'GET',
                data: {
                    year: currentYear,
                    test_type: isWeeklyTest ? 'weekly_test' : 'monthly_test',
                    examID: examID
                },
                success: function(response) {
                    if (response.success && response.periods) {
                        // Remember current selection
                        const selectedPeriod = $periodSelect.val();
                        $periodSelect.html('<option value="">{{ __('teacher.exam_papers.select_period') }}</option>');

                        let periodExists = false;
                        response.periods.forEach(function(period) {
                            const displayText = period.range ? `${period.week} (${period.range})` : period.week;
                            $periodSelect.append(`<option value="${period.week}">${displayText}</option>`);
                            if (period.week === selectedPeriod) periodExists = true;
                        });

                        // Restore selection if valid, otherwise keep what we injected if not in list (edge case)
                        if (periodExists) {
                            $periodSelect.val(selectedPeriod);
                        } else if (selectedPeriod) {
                             $periodSelect.append(`<option value="${selectedPeriod}">${displayWeek}</option>`);
                             $periodSelect.val(selectedPeriod);
                        }

                        // Load subjects for this week (Background)
                        $.ajax({
                            url: '/get_scheduled_subjects',
                            method: 'GET',
                            data: {
                                examID: examID,
                                week: selectedPeriod
                            },
                            success: function(subjectResponse) {
                                if (subjectResponse.success && subjectResponse.subjects) {
                                    // Remember current subject
                                    const selectedSubject = $subjectSelect.val();
                                    $subjectSelect.html('<option value="">{{ __('teacher.exam_papers.select_subject') }}</option>');

                                    let subjectExists = false;
                                    subjectResponse.subjects.forEach(function(subject) {
                                        appendScheduledSubjectOption($subjectSelect, subject);
                                        if (subject.class_subjectID == selectedSubject) subjectExists = true;
                                    });

                                    // Restore selection
                                    if (subjectExists) {
                                        $subjectSelect.val(selectedSubject);
                                    } else if (selectedSubject) {
                                        $subjectSelect.append(`<option value="${selectedSubject}">${fullSubjectDisplay}</option>`);
                                        $subjectSelect.val(selectedSubject);
                                    }
                                    updateDuoPaperSelect(false);
                                }
                            }
                        });
                    }
                }
            });
        }

        // Fetch allowed classes
        fetchAllowedClasses(examID);
        fetchExistingPapers(examID);

        // Switch Tab
        sxShowTab('#upload-tab');
    });

    $('#selected_exam').on('change', function() {
        const examID = $(this).val();
        const examName = $(this).find('option:selected').text();

        // Check if this is a Weekly Test or Monthly Test
        const isWeeklyTest = examName.includes('Weekly Test');
        const isMonthlyTest = examName.includes('Monthly Test');
        const isTestType = isWeeklyTest || isMonthlyTest;

        if (isTestType) {
            // Show test fields, hide regular subject selection
            $('#test_fields_container').show();
            $('#regular_subject_container').hide();
            $('#class_subject').prop('required', false);
            $('#test_subject').prop('required', true);

            // Load available periods for the current year
            const currentYear = $('#test_year').val() || new Date().getFullYear();
            loadAvailablePeriods(currentYear, isWeeklyTest ? 'weekly_test' : 'monthly_test', examID);
        } else {
            // Hide test fields, show regular subject selection
            $('#test_fields_container').hide();
            $('#regular_subject_container').show();
            $('#class_subject').prop('required', true);
            $('#test_subject').prop('required', false);

            // Clear test fields
            $('#test_period').html('<option value="">{{ __('teacher.exam_papers.select_period') }}</option>');
            $('#test_subject').html('<option value="">{{ __('teacher.exam_papers.select_period_first') }}</option>');
        }

        updateDuoPaperSelect(false);

        const allowNoFormat = $(this).find('option:selected').data('allow-no-format') == '1';
        if (allowNoFormat) {
            $('#question-format-main').hide();
        } else {
            $('#question-format-main').show();
        }

        fetchAllowedClasses(examID);
        fetchExistingPapers(examID);
    });

    // Handle year change for tests
    $('#test_year').on('change', function() {
        const year = $(this).val();
        const examName = $('#selected_exam').find('option:selected').text();
        const isWeeklyTest = examName.includes('Weekly Test');
        const isMonthlyTest = examName.includes('Monthly Test');

        if (year && (isWeeklyTest || isMonthlyTest)) {
            loadAvailablePeriods(year, isWeeklyTest ? 'weekly_test' : 'monthly_test');
        }
    });

    // Handle period change for tests
    $('#test_period').on('change', function() {
        const period = $(this).val();
        const examID = $('#selected_exam').val();

        if (period && examID) {
            loadScheduledSubjects(examID, period);
        } else {
            $('#test_subject').html('<option value="">{{ __('teacher.exam_papers.select_period_first') }}</option>');
        }
    });

    // Function to load available periods (weeks/months) excluding holidays
    function loadAvailablePeriods(year, testType, examID) {
        $.ajax({
            url: '/get_available_periods',
            method: 'GET',
            data: {
                year: year,
                test_type: testType,
                examID: examID
            },
            success: function(response) {
                if (response.success && response.periods) {
                    const $periodSelect = $('#test_period');
                    $periodSelect.html('<option value="">{{ __('teacher.exam_papers.select_period') }}</option>');

                    response.periods.forEach(function(period) {
                        $periodSelect.append(`<option value="${period.id}">${period.text}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Failed to load periods:', xhr);
                Swal.fire(i18nEp.error, i18nEp.fail_load_periods, 'error');
            }
        });
    }

    // Function to load scheduled subjects for a specific test period
    function appendScheduledSubjectOption($subjectSelect, subject) {
        const duoLabel = subject.is_duo ? ' [DUO]' : '';
        const classLabel = subject.class_display || subject.class_name || '';
        const dayTime = (subject.day || subject.time)
            ? ` - ${subject.day || ''} ${subject.time || ''}`.trim()
            : '';
        const label = classLabel
            ? `${subject.subject_name}${duoLabel} (${classLabel})${dayTime}`
            : `${subject.subject_name}${duoLabel}${dayTime}`;

        const $opt = $('<option></option>')
            .val(subject.class_subjectID)
            .text(label)
            .attr('data-subject-id', subject.subjectID || '')
            .attr('data-is-duo', subject.is_duo ? '1' : '0')
            .attr('data-duo-papers', JSON.stringify(subject.duo_papers || []));
        $subjectSelect.append($opt);
    }

    function loadScheduledSubjects(examID, testWeek) {
        $.ajax({
            url: '/get_scheduled_subjects',
            method: 'GET',
            data: {
                examID: examID,
                test_week: testWeek
            },
            success: function(response) {
                if (response.success && response.subjects) {
                    const $subjectSelect = $('#test_subject');
                    $subjectSelect.html('<option value="">{{ __('teacher.exam_papers.select_subject') }}</option>');

                    if (response.subjects.length === 0) {
                        $subjectSelect.html('<option value="">' + i18nEp.no_subjects_period + '</option>');
                    } else {
                        response.subjects.forEach(function(subject) {
                            appendScheduledSubjectOption($subjectSelect, subject);
                        });
                    }
                    updateDuoPaperSelect(false);
                }
            },
            error: function(xhr) {
                console.error('Failed to load subjects:', xhr);
                $('#test_subject').html('<option value="">' + i18nEp.err_load_subjects + '</option>');
            }
        });
    }

    $('#modal_selected_exam').on('change', function() {
        const examID = $(this).val();
        const allowNoFormat = $(this).find('option:selected').data('allow-no-format') == '1';
        if (allowNoFormat) {
            $('#question-format-modal').hide();
        } else {
            $('#question-format-modal').show();
        }
        
        fetchAllowedClasses(examID);
        fetchExistingPapers(examID);
    });

    $('#class_subject').on('change', function() {
        refreshExistingUploadOptions(false);
        updateDuoPaperSelect(false);
    });

    $('#test_subject').on('change', function() {
        updateDuoPaperSelect(false);
    });

    $('#modal_class_subject').on('change', function() {
        refreshExistingUploadOptions(true);
        updateDuoPaperSelect(true);
    });

    function updateDuoPaperSelect(isModal) {
        const isTestMode = !isModal && $('#test_fields_container').is(':visible');
        const $subjectSelect = isModal
            ? $('#modal_class_subject')
            : (isTestMode ? $('#test_subject') : $('#class_subject'));
        const $paperWrap = isModal ? $('#modal_duo_paper_container') : $('#duo_paper_container');
        const $paperSelect = isModal ? $('#modal_component_id') : $('#component_id');
        const $opt = $subjectSelect.find('option:selected');
        const classSubjectID = $opt.val();
        const isDuo = $opt.data('is-duo') == 1 || $opt.data('is-duo') === true;
        const papers = parseDuoPapersFromOption($opt);

        $paperSelect.empty().append('<option value="">{{ __('teacher.exam_papers.select_paper_ph') }}</option>');
        if (isDuo && papers.length) {
            papers.forEach(function(p) {
                const marks = p.max_marks ? ' (' + p.max_marks + ' marks)' : '';
                const already = classSubjectID && isPaperAlreadyUploaded(classSubjectID, p.componentID);
                const label = already
                    ? `${p.component_name}${marks} - Already uploaded`
                    : `${p.component_name}${marks}`;
                const $paperOpt = $(`<option value="${p.componentID}" data-max-marks="${p.max_marks || 100}">${label}</option>`);
                if (already) {
                    $paperOpt.prop('disabled', true);
                }
                $paperSelect.append($paperOpt);
            });
            $paperWrap.show();
            $paperSelect.prop('required', true);
        } else {
            $paperWrap.hide();
            $paperSelect.prop('required', false).val('');
        }
        syncUploadFormatTotal(isModal);
    }

    $(document).on('change', '#component_id', function() {
        syncUploadFormatTotal(false);
    });

    $(document).on('change', '#modal_component_id', function() {
        syncUploadFormatTotal(true);
    });

    function toggleFileRequirement(isModal, useExisting) {
        const $fileInput = isModal ? $('#modal_exam_file') : $('#exam_file');
        if (useExisting) {
            $fileInput.prop('required', false);
        } else {
            $fileInput.prop('required', true);
        }
    }

    $('#existing_upload_main').on('change', function() {
        const paperId = $(this).val();
        toggleFileRequirement(false, !!paperId);
        if (!paperId) {
            return;
        }
        $.ajax({
            url: `/get_exam_paper_questions/${paperId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    applyExistingQuestions('#question-rows-main', response);
                }
            }
        });
    });

    $('#existing_upload_modal').on('change', function() {
        const paperId = $(this).val();
        toggleFileRequirement(true, !!paperId);
        if (!paperId) {
            return;
        }
        $.ajax({
            url: `/get_exam_paper_questions/${paperId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    applyExistingQuestions('#question-rows-modal', response);
                }
            }
        });
    });

    const initialExam = $('#selected_exam').val();
    if (initialExam) {
        fetchExistingPapers(initialExam);
    }

    // Submit form
    $('#uploadExamPaperForm').on('submit', function(e) {
        e.preventDefault();
        submitExamPaper($(this), false);
    });

    $('#submitModalForm').on('click', function() {
        const form = $('#modalUploadForm');
        submitExamPaper(form, true);
    });

    function submitExamPaper(form, isModal) {
        const examID = isModal ? $('#modal_selected_exam').val() : $('#selected_exam').val();
        const isTestMode = !isModal && $('#test_fields_container').is(':visible');
        const classSubjectID = isModal
            ? $('#modal_class_subject').val()
            : (isTestMode ? $('#test_subject').val() : $('#class_subject').val());
        const existingUploadId = isModal ? $('#existing_upload_modal').val() : $('#existing_upload_main').val();

        if (!examID) {
            Swal.fire(i18nEp.error, i18nEp.err_select_exam, 'error');
            return;
        }

        // Verify selected exam exists and has upload_paper enabled
        const selectedExam = examinations.find(exam => exam.examID == examID);
        if (!selectedExam) {
            Swal.fire(i18nEp.error, i18nEp.err_exam_disabled, 'error');
            return;
        }

        if (!classSubjectID) {
            Swal.fire(i18nEp.error, i18nEp.err_select_subject, 'error');
            return;
        }

        const $subjectSelect = isModal
            ? $('#modal_class_subject')
            : (isTestMode ? $('#test_subject') : $('#class_subject'));
        const $selectedOpt = $subjectSelect.find('option:selected');
        const isDuo = $selectedOpt.data('is-duo') == 1 || $selectedOpt.data('is-duo') === true;
        const componentID = isModal
            ? $('#modal_component_id').val()
            : $('#component_id').val();

        if (isDuo && !componentID) {
            Swal.fire(i18nEp.error, i18nEp.err_select_duo, 'error');
            return;
        }

        const fileInput = isModal ? $('#modal_exam_file')[0] : $('#exam_file')[0];
        if (!existingUploadId && fileInput.files.length === 0) {
            Swal.fire(i18nEp.error, i18nEp.err_select_file, 'error');
            return;
        }

        if (isSecondarySchool && selectedExam.allow_no_format != 1) {
            const $questionContainer = isModal ? $('#question-rows-modal') : $('#question-rows-main');
            const descriptions = $questionContainer.find('.question-description').map(function() {
                return $(this).val().trim();
            }).get();
            const marks = $questionContainer.find('.question-marks').map(function() {
                return $(this).val();
            }).get();
            const optionals = $questionContainer.find('.question-optional').map(function() {
                return parseInt($(this).val(), 10);
            }).get();
            const targetId = isModal ? '#question-rows-modal' : '#question-rows-main';
            const expectedTotal = getExpectedFormatTotal(targetId);
            const optionalTotals = getOptionalTotals(targetId);
            const optionalRequiredCounts = getOptionalRequiredCounts(targetId);

            if (descriptions.length === 0) {
                Swal.fire(i18nEp.error, i18nEp.err_add_question, 'error');
                return;
            }

            const hasEmptyDescription = descriptions.some(desc => desc === '');
            if (hasEmptyDescription) {
                Swal.fire(i18nEp.error, i18nEp.err_fill_descriptions, 'error');
                return;
            }

            let requiredTotal = 0;
            let optionalSumByRange = {};
            for (let i = 0; i < marks.length; i++) {
                const markValue = parseInt(marks[i], 10);
                if (isNaN(markValue) || markValue <= 0) {
                    Swal.fire(i18nEp.error, i18nEp.err_valid_marks, 'error');
                    return;
                }
                if (optionals[i] > 0) {
                    optionalSumByRange[optionals[i]] = (optionalSumByRange[optionals[i]] || 0) + markValue;
                } else {
                    requiredTotal += markValue;
                }
            }

            const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
            const optionalQuestionsCountByRange = {};
            optionals.forEach(function(rangeNumber) {
                if (rangeNumber > 0) {
                    optionalQuestionsCountByRange[rangeNumber] = (optionalQuestionsCountByRange[rangeNumber] || 0) + 1;
                }
            });

            const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
                const rangeTotal = optionalTotals[range];
                const sum = optionalSumByRange[range] || 0;
                return sum < rangeTotal;
            });

            const requiredCountInvalid = Object.keys(optionalRequiredCounts).some(function(range) {
                const requiredCount = optionalRequiredCounts[range];
                const available = optionalQuestionsCountByRange[range] || 0;
                return requiredCount > available;
            });

            if (requiredCountInvalid) {
                Swal.fire(i18nEp.error, i18nEp.err_required_optional, 'error');
                return;
            }

            if (optionalRangeMismatch) {
                Swal.fire(i18nEp.error, i18nEp.err_optional_total, 'error');
                return;
            }

            if (requiredTotal > (expectedTotal - optionalTotalSum)) {
                Swal.fire(i18nEp.error, i18nEp.err_required_exceed, 'error');
                return;
            }

            if ((requiredTotal + optionalTotalSum) !== expectedTotal) {
                Swal.fire(i18nEp.error, i18nEp.err_total_exact.replace(':total', expectedTotal), 'error');
                return;
            }
        }

        const formData = new FormData();
        formData.append('examID', examID);
        formData.append('class_subjectID', classSubjectID);
        if (componentID) {
            formData.append('componentID', componentID);
        }
        formData.append('upload_type', 'upload');
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        if (existingUploadId) {
            formData.append('existing_exam_paper_id', existingUploadId);
        }
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        const applyToAll = isModal ? $('#apply_all_modal').is(':checked') : $('#apply_all_main').is(':checked');
        if (applyToAll) {
            formData.append('apply_to_all_subjects', '1');
        }

        // Add test-specific fields if applicable
        const testWeek = isModal ? $('#modal_test_week').val() : $('#test_period').val();
        const testDate = isModal ? $('#modal_test_date').val() : $('#test_date').val();
        const placeholderId = $('#placeholder_id').val();

        if (testWeek) {
            formData.append('test_week', testWeek);
        }
        if (testDate) {
            formData.append('test_date', testDate);
        }
        if (placeholderId) {
            formData.append('placeholder_id', placeholderId);
        }

        if (isSecondarySchool) {
            const $questionContainer = isModal ? $('#question-rows-modal') : $('#question-rows-main');
            $questionContainer.find('.question-description').each(function() {
                formData.append('question_descriptions[]', $(this).val().trim());
            });
            $questionContainer.find('.question-marks').each(function() {
                formData.append('question_marks[]', $(this).val());
            });
            $questionContainer.find('.question-optional').each(function() {
                formData.append('question_optional[]', $(this).val());
            });
            const targetId = isModal ? '#question-rows-modal' : '#question-rows-main';
            const optionalTotals = getOptionalTotals(targetId);
            const optionalRequiredCounts = getOptionalRequiredCounts(targetId);
            Object.keys(optionalTotals).forEach(function(rangeNumber) {
                formData.append('optional_ranges[' + rangeNumber + ']', optionalTotals[rangeNumber]);
            });
            Object.keys(optionalRequiredCounts).forEach(function(rangeNumber) {
                formData.append('optional_required_counts[' + rangeNumber + ']', optionalRequiredCounts[rangeNumber]);
            });
        }

        // Show loading progress bar
        Swal.fire({
            title: i18nEp.uploading,
            html: i18nEp.uploading_wait + '<br><br><b id="upload-percentage">0%</b><br><div class="progress mt-2" style="height: 10px; border-radius: 5px; overflow: hidden;"><div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("store_exam_paper") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = parseInt(percentComplete * 100);
                        $('#upload-progress-bar').css('width', percentComplete + '%');
                        $('#upload-percentage').text(percentComplete + '%');
                        if (percentComplete === 100) {
                            Swal.update({
                                title: i18nEp.processing,
                                html: i18nEp.finalizing
                            });
                        }
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                Swal.fire(i18nEp.success, response.success || i18nEp.submitted_ok, 'success').then(() => {
                    if (isModal) {
                        sxHideModal('#uploadExamPaperModal');
                    }
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = i18nEp.fail_submit;
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.status === 0) {
                    errorMsg = i18nEp.network_error;
                } else if (xhr.status >= 500) {
                    errorMsg = i18nEp.server_error.replace(':status', xhr.status);
                }
                Swal.fire(i18nEp.error, errorMsg, 'error');
            }
        });
    }

    // --- NEW UNIFIED EDIT MODAL LOGIC (Aligned with Upload) ---

    // Toggle fields in Edit Modal based on exam type
    $('#edit_selected_exam').on('change', function() {
        const examID = $(this).val();
        const examName = $(this).find('option:selected').text();
        const isTestType = examName.includes('Weekly Test') || examName.includes('Monthly Test');

        if (isTestType) {
            $('#edit_test_fields_container').show();
            $('#edit_regular_subject_container').hide();
            $('#edit_class_subject').prop('required', false);
            $('#edit_test_subject').prop('required', true);
            
            const currentYear = $('#edit_test_year').val() || new Date().getFullYear();
            loadEditPeriods(currentYear, examName.includes('Weekly Test') ? 'weekly_test' : 'monthly_test', examID);
        } else {
            $('#edit_test_fields_container').hide();
            $('#edit_regular_subject_container').show();
            $('#edit_class_subject').prop('required', true);
            $('#edit_test_subject').prop('required', false);
        }

        const allowNoFormat = $(this).find('option:selected').data('allow-no-format') == '1';
        $('#edit_question-format-container').toggle(!allowNoFormat);
    });

    $('#edit_test_year').on('change', function() {
        const examName = $('#edit_selected_exam').find('option:selected').text();
        loadEditPeriods($(this).val(), examName.includes('Weekly Test') ? 'weekly_test' : 'monthly_test', $('#edit_selected_exam').val());
    });

    $('#edit_test_period').on('change', function() {
        loadEditScheduledSubjects($('#edit_selected_exam').val(), $(this).val());
    });

    function loadEditPeriods(year, testType, examID) {
        $.ajax({
            url: '/get_available_periods',
            method: 'GET',
            data: { year: year, test_type: testType, examID: examID },
            success: function(response) {
                if (response.success && response.periods) {
                    const $select = $('#edit_test_period');
                    $select.html('<option value="">{{ __('teacher.exam_papers.select_period') }}</option>');
                    response.periods.forEach(function(p) { $select.append('<option value="' + p.id + '">' + p.text + '</option>'); });
                    if (window._temp_edit_week) {
                        $select.val(window._temp_edit_week).trigger('change');
                        delete window._temp_edit_week;
                    }
                }
            }
        });
    }

    function loadEditScheduledSubjects(examID, testWeek) {
        $.ajax({
            url: '/get_scheduled_subjects',
            method: 'GET',
            data: { examID: examID, test_week: testWeek },
            success: function(response) {
                if (response.success && response.subjects) {
                    const $select = $('#edit_test_subject');
                    $select.html('<option value="">{{ __('teacher.exam_papers.select_subject') }}</option>');
                    response.subjects.forEach(function(s) { $select.append('<option value="' + s.class_subjectID + '">' + s.subject_name + ' (' + s.class_name + ')</option>'); });
                    if (window._temp_edit_subject) {
                        $select.val(window._temp_edit_subject);
                        delete window._temp_edit_subject;
                    }
                }
            }
        });
    }

    $(document).on('click', '.edit-paper-btn', function() {
        const paperID = $(this).data('paper-id');
        const isRejected = $(this).data('is-rejected') == '1';

        $.ajax({
            url: '/get_exam_paper_questions/' + paperID,
            method: 'GET',
            success: function(response) {
                if (response.success && response.exam_paper) {
                    const paper = response.exam_paper;
                    $('#edit_paper_id').val(paper.exam_paperID);
                    $('#edit_upload_type').val(paper.upload_type);
                    
                    if (isRejected) {
                        $('#editExamPaperModal .modal-title').html('<i class="bi bi-pencil-square"></i> ' + i18nEp.edit_exam_resend);
                        $('#submitEditForm').html('<i class="bi bi-send"></i> ' + i18nEp.update_resubmit).data('is-resubmit', 1);
                    } else {
                        $('#editExamPaperModal .modal-title').html('<i class="bi bi-pencil"></i> ' + i18nEp.edit_exam);
                        $('#submitEditForm').html('<i class="bi bi-check-circle"></i> ' + i18nEp.save_changes).data('is-resubmit', 0);
                    }

                    $('#edit_selected_exam').val(paper.examID).trigger('change');

                    const isTest = $('#edit_test_fields_container').is(':visible');
                    if (isTest) {
                        window._temp_edit_week = paper.test_week;
                        window._temp_edit_subject = paper.class_subjectID;
                    } else {
                        $('#edit_class_subject').val(paper.class_subjectID);
                    }

                    if (paper.file_path) {
                        const fileName = paper.file_path.split('/').pop();
                        $('#current_file_info').text((i18nEp.current_file || 'Current file:') + ' ' + fileName).show();
                    } else {
                        $('#current_file_info').hide();
                    }

                    // Pre-fill Question rows using UNIVERSAL logic
                    const $container = $('#question-rows-edit-unified');
                    const $rangeWrapper = $('.optional-ranges-wrapper[data-wrapper-for="#question-rows-edit-unified"]');
                    $container.empty();
                    $rangeWrapper.empty();
                    setExpectedFormatTotal('#question-rows-edit-unified', response.paper_max_marks || 100);

                    // 1. First add all optional ranges if any
                    if (response.optional_ranges && response.optional_ranges.length > 0) {
                        response.optional_ranges.forEach(function(range) {
                            addOptionalRange('#question-rows-edit-unified');
                            const $lastRange = $rangeWrapper.find('.optional-range-item').last();
                            $lastRange.find('.optional-total-input').val(range.total_marks);
                            $lastRange.find('.optional-required-input').val(range.required_questions);
                        });
                        refreshOptionalRangeSelects('#question-rows-edit-unified');
                    }

                    // 2. Add Questions
                    if (response.questions && response.questions.length > 0) {
                        response.questions.forEach(function(q) {
                            const html = buildQuestionRow('#question-rows-edit-unified');
                            $container.append(html);
                            const $lastRow = $container.find('.question-row').last();
                            $lastRow.find('.question-description').val(q.question_description);
                            $lastRow.find('.question-marks').val(q.marks);
                            $lastRow.find('.question-optional').val(q.is_optional ? q.optional_range_number : 0);
                        });
                    } else {
                        // Ensure at least one row
                        $container.append(buildQuestionRow('#question-rows-edit-unified'));
                    }

                    refreshQuestionNumbers($container);
                    updateTotalMarks($container);
                    sxShowModal('#editExamPaperModal');
                }
            }
        });
    });

    $('#submitEditForm').on('click', function() {
        const paperID = $('#edit_paper_id').val();
        const examID = $('#edit_selected_exam').val();
        const isTest = $('#edit_test_fields_container').is(':visible');
        const classSubjectID = isTest ? $('#edit_test_subject').val() : $('#edit_class_subject').val();
        const fileInput = $('#edit_exam_file')[0];
        
        if (!examID || !classSubjectID) {
            Swal.fire(i18nEp.error, i18nEp.err_exam_subject, 'error');
            return;
        }

        const formData = new FormData();
        formData.append('examID', examID);
        formData.append('class_subjectID', classSubjectID);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        if (isTest) {
            formData.append('test_week', $('#edit_test_period').val());
        }

        // VALIDATION & DATA COLLECTION (Aligned with main form)
        if ($('#edit_question-format-container').is(':visible')) {
            const $container = $('#question-rows-edit-unified');
            const targetId = '#question-rows-edit-unified';
            const expectedTotal = getExpectedFormatTotal(targetId);
            
            const descriptions = $container.find('.question-description').map(function() { return $(this).val().trim(); }).get();
            const marks = $container.find('.question-marks').map(function() { return $(this).val(); }).get();
            const optionals = $container.find('.question-optional').map(function() { return parseInt($(this).val(), 10); }).get();
            
            if (descriptions.some(d => d === '')) { Swal.fire(i18nEp.error, i18nEp.err_fill_all_desc, 'error'); return; }
            
            const optionalTotals = getOptionalTotals(targetId);
            const optionalRequiredCounts = getOptionalRequiredCounts(targetId);

            let requiredTotal = 0;
            let optionalSumByRange = {};
            for (let i = 0; i < marks.length; i++) {
                const markValue = parseInt(marks[i], 10);
                if (isNaN(markValue) || markValue <= 0) {
                    Swal.fire(i18nEp.error, i18nEp.err_valid_marks, 'error');
                    return;
                }
                if (optionals[i] > 0) {
                    optionalSumByRange[optionals[i]] = (optionalSumByRange[optionals[i]] || 0) + markValue;
                } else {
                    requiredTotal += markValue;
                }
            }
            const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
            const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
                return (optionalSumByRange[range] || 0) < optionalTotals[range];
            });
            if (optionalRangeMismatch) {
                Swal.fire(i18nEp.error, i18nEp.err_optional_total, 'error');
                return;
            }
            if (requiredTotal > (expectedTotal - optionalTotalSum) || (requiredTotal + optionalTotalSum) !== expectedTotal) {
                Swal.fire(i18nEp.error, i18nEp.err_total_exact.replace(':total', expectedTotal), 'error');
                return;
            }

            // Append Arrays to FormData
            descriptions.forEach(function(d) { formData.append('question_descriptions[]', d); });
            marks.forEach(function(m) { formData.append('question_marks[]', m); });
            optionals.forEach(function(o) { formData.append('question_optional[]', o); });
            
            Object.keys(optionalTotals).forEach(function(range) {
                formData.append('optional_ranges[' + range + ']', optionalTotals[range]);
            });
            Object.keys(optionalRequiredCounts).forEach(function(range) {
                formData.append('optional_required_counts[' + range + ']', optionalRequiredCounts[range]);
            });
        }

        const isResubmit = $('#submitEditForm').data('is-resubmit') == 1;
        Swal.fire({
            title: isResubmit ? i18nEp.updating_resubmitting : i18nEp.updating_paper,
            text: i18nEp.please_wait,
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        $.ajax({
            url: '/update_exam_paper/' + paperID,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (isResubmit) {
                    $.ajax({
                        url: '/resubmit_exam_paper/' + paperID,
                        method: 'POST',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function() {
                            Swal.fire(i18nEp.success, i18nEp.updated_resubmitted, 'success').then(function() { location.reload(); });
                        }
                    });
                } else {
                    Swal.fire(i18nEp.success, i18nEp.updated_ok, 'success').then(function() { location.reload(); });
                }
            },
            error: function(xhr) {
                let msg = i18nEp.fail_update;
                if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                Swal.fire(i18nEp.error, msg, 'error');
            }
        });
    });

    // Delete exam paper (for rejected papers)
    $(document).on('click', '.delete-paper-btn', function() {
        const paperID = $(this).data('paper-id');

        Swal.fire({
            title: i18nEp.delete_confirm_title,
            text: i18nEp.delete_confirm_text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: i18nEp.yes_delete
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: i18nEp.deleting,
                    text: i18nEp.deleting_wait,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/delete_exam_paper/' + paperID,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(i18nEp.deleted, response.success || i18nEp.deleted_ok, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = i18nEp.fail_delete;
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire(i18nEp.error, errorMsg, 'error');
                    }
                });
            }
        });
    });

    // Search my exam papers
    $('#search_my_papers').on('input', function() {
        applyMyPapersFilters();
    });

    function applyMyPapersFilters() {
        const searchTerm = ($('#search_my_papers').val() || '').toLowerCase();
        const term = $('#filter_my_papers_term').val();
        const year = $('#filter_my_papers_year').val();
        const examId = $('#filter_my_papers_exam').val();

        // Apply to ALL tab panes so switching tabs also shows filtered results
        $('.tab-pane tr.exam-paper-row').each(function() {
            const $row = $(this);
            const text = String($row.data('search') || $row.text() || '').toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesTerm = !term || $row.data('term') === term;
            const matchesYear = !year || String($row.data('year')) === String(year);
            const matchesExam = !examId || String($row.data('exam-id')) === String(examId);

            if (matchesSearch && matchesTerm && matchesYear && matchesExam) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }

    $('#search_my_papers, #filter_my_papers_term, #filter_my_papers_year, #filter_my_papers_exam').on('input change', function() {
        applyMyPapersFilters();
    });

    // Clear filter
    $('#clearPapersFilter').on('click', function() {
        $('#search_my_papers').val('');
        $('#filter_my_papers_exam').val('');
        $('#filter_my_papers_term').val('');
        $('#filter_my_papers_year').val('');
        applyMyPapersFilters();
    });

    // Show/hide filter bar based on active tab (hide on Upload and Pending Uploads tabs)
    function getActiveTabTarget() {
        const $active = $('#examPapersTabs .nav-link.active');
        return $active.data('bs-target') || $active.attr('href') || '#upload';
    }
    function toggleFilterBar() {
        const activeTab = getActiveTabTarget();
        if (activeTab === '#upload' || activeTab === '#pending-uploads') {
            $('#papersFilterBar').slideUp(200);
        } else {
            $('#papersFilterBar').slideDown(200);
        }
    }

    // Tab persistence: remember active tab in localStorage
    const TAB_STORAGE_KEY = 'examPapersActiveTab';

    $('#examPapersTabs [data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const tabId = $(e.target).data('bs-target') || $(e.target).attr('href');
        localStorage.setItem(TAB_STORAGE_KEY, tabId);
        toggleFilterBar();

        // Badge persistence: mark tab as "seen" and hide badge permanently
        const tabKey = 'examPapersSeen_' + String(tabId).replace('#', '');
        const currentCount = $(e.target).find('.badge').text().trim();
        if (currentCount) {
            localStorage.setItem(tabKey, currentCount);
        }
        $(e.target).find('.badge').fadeOut(300);
    });

    // Restore active tab on page load
    const savedTab = localStorage.getItem(TAB_STORAGE_KEY);
    if (savedTab && $(savedTab).length) {
        const $tabBtn = $(`#examPapersTabs [data-bs-target="${savedTab}"]`);
        if ($tabBtn.length) sxShowTab($tabBtn[0]);
    }
    toggleFilterBar();

    // Badge persistence on page load: hide badges for tabs already seen with same count
    $('#examPapersTabs .nav-link .badge').each(function() {
        const $badge = $(this);
        const $tab = $badge.closest('.nav-link');
        const tabId = String($tab.data('bs-target') || $tab.attr('href') || '').replace('#', '');
        const tabKey = 'examPapersSeen_' + tabId;
        const seenCount = localStorage.getItem(tabKey);
        const currentCount = $badge.text().trim();

        if (seenCount && seenCount === currentCount) {
            $badge.hide(); // Already seen - hide permanently
        } else if (seenCount && seenCount !== currentCount) {
            // Count changed - show new count (difference)
            localStorage.removeItem(tabKey); // Reset so it shows again
        }
    });

    // Edit exam paper questions
    $(document).on('click', '.edit-questions-btn', function() {
        const paperID = $(this).data('paper-id');
        const testWeek = $(this).data('week');
        const testDate = $(this).data('date');

        $('#edit_questions_paper_id').val(paperID);
        $('#edit_questions_test_week').val(testWeek);
        $('#edit_questions_test_date').val(testDate);
        $('#question-rows-edit').empty();
        $('.optional-ranges-wrapper[data-wrapper-for="#question-rows-edit"]').empty();
        
        let isRejected = false;
        
        $.ajax({
            url: '/get_my_exam_papers',
            method: 'GET',
            success: function(getResp) {
                if (getResp.success) {
                    const paper = getResp.exam_papers.find(p => p.exam_paperID == paperID);
                    if (paper) {
                        isRejected = paper.status === 'rejected';
                        
                        // Toggle test fields based on exam_category or name
                        if (paper.examination && (paper.examination.exam_category === 'test' || paper.examination.exam_name.toLowerCase().includes('test'))) {
                            $('#edit_questions_test_week').closest('.col-md-6').show();
                            $('#edit_questions_test_date').closest('.col-md-6').show();
                        } else {
                            $('#edit_questions_test_week').closest('.col-md-6').hide();
                            $('#edit_questions_test_date').closest('.col-md-6').hide();
                        }
                        
                        if (isRejected) {
                            $('#editExamPaperQuestionsModal .modal-title').html('<i class="bi bi-pencil-square"></i> Edit & Resend Questions');
                            $('#submitEditQuestionsForm').html('<i class="bi bi-send"></i> Submit & Resend');
                        } else {
                            $('#editExamPaperQuestionsModal .modal-title').html('<i class="bi bi-list-check"></i> {{ __('teacher.exam_papers.edit_questions_title') }}');
                            $('#submitEditQuestionsForm').html('<i class="bi bi-check-circle"></i> Save Changes');
                        }
                    }
                }
                
                sxShowModal('#editExamPaperQuestionsModal');

                $.ajax({
                    url: `/get_exam_paper_questions/${paperID}`,
                    method: 'GET',
                    success: function(response) {
                        if (!response.success) {
                            Swal.fire(i18nEp.error, response.error || i18nEp.fail_load_questions, 'error');
                            return;
                        }

                        setExpectedFormatTotal('#question-rows-edit', response.paper_max_marks || 100);

                        const ranges = response.optional_ranges || [];
                        ranges.forEach(function(range) {
                            addOptionalRange('#question-rows-edit');
                            const $wrapper = $('.optional-ranges-wrapper[data-wrapper-for="#question-rows-edit"] .optional-range-item').last();
                            $wrapper.find('.optional-total-input').val(range.total_marks);
                            $wrapper.find('.optional-required-input').val(range.required_questions);
                        });

                        (response.questions || []).forEach(function(question) {
                            $('#question-rows-edit').append(buildQuestionRow('#question-rows-edit'));
                            const $row = $('#question-rows-edit .question-row').last();
                            $row.find('.question-description').val(question.question_description);
                            $row.find('.question-marks').val(question.marks);
                            $row.find('.question-optional').val(question.optional_range_number || 0);
                        });

                        refreshQuestionNumbers($('#question-rows-edit'));
                        refreshOptionalRangeSelects($('#question-rows-edit'));
                        updateTotalMarks($('#question-rows-edit'));
                        toggleRemoveButtons($('#question-rows-edit'));
                    },
                    error: function() {
                        Swal.fire(i18nEp.error, i18nEp.fail_load_questions, 'error');
                    }
                });
            }
        });
    });

    $('#submitEditQuestionsForm').on('click', function() {
        const paperID = $('#edit_questions_paper_id').val();
        const $questionContainer = $('#question-rows-edit');
        const descriptions = $questionContainer.find('.question-description').map(function() {
            return $(this).val().trim();
        }).get();
        const marks = $questionContainer.find('.question-marks').map(function() {
            return $(this).val();
        }).get();
        const optionals = $questionContainer.find('.question-optional').map(function() {
            return parseInt($(this).val(), 10);
        }).get();
        const optionalTotals = getOptionalTotals('#question-rows-edit');
        const optionalRequiredCounts = getOptionalRequiredCounts('#question-rows-edit');
        const expectedTotal = getExpectedFormatTotal('#question-rows-edit');

        if (descriptions.length === 0) {
            Swal.fire(i18nEp.error, i18nEp.err_add_question, 'error');
            return;
        }

        const hasEmptyDescription = descriptions.some(desc => desc === '');
        if (hasEmptyDescription) {
            Swal.fire(i18nEp.error, i18nEp.err_fill_descriptions, 'error');
            return;
        }

        let requiredTotal = 0;
        let optionalSumByRange = {};
        for (let i = 0; i < marks.length; i++) {
            const markValue = parseInt(marks[i], 10);
            if (isNaN(markValue) || markValue <= 0) {
                Swal.fire(i18nEp.error, i18nEp.err_valid_marks, 'error');
                return;
            }
            if (optionals[i] > 0) {
                optionalSumByRange[optionals[i]] = (optionalSumByRange[optionals[i]] || 0) + markValue;
            } else {
                requiredTotal += markValue;
            }
        }

        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
            const rangeTotal = optionalTotals[range];
            const sum = optionalSumByRange[range] || 0;
            return sum < rangeTotal;
        });

        if (optionalRangeMismatch) {
            Swal.fire(i18nEp.error, i18nEp.err_optional_total, 'error');
            return;
        }

        if (requiredTotal > (expectedTotal - optionalTotalSum)) {
            Swal.fire(i18nEp.error, i18nEp.err_required_exceed, 'error');
            return;
        }

        if ((requiredTotal + optionalTotalSum) !== expectedTotal) {
            Swal.fire(i18nEp.error, i18nEp.err_total_exact.replace(':total', expectedTotal), 'error');
            return;
        }

        const formData = new FormData();
        descriptions.forEach(function(desc) {
            formData.append('question_descriptions[]', desc);
        });
        marks.forEach(function(mark) {
            formData.append('question_marks[]', mark);
        });
        optionals.forEach(function(opt) {
            formData.append('question_optional[]', opt);
        });
        Object.keys(optionalTotals).forEach(function(rangeNumber) {
            formData.append(`optional_ranges[${rangeNumber}]`, optionalTotals[rangeNumber]);
        });
        Object.keys(optionalRequiredCounts).forEach(function(rangeNumber) {
            formData.append(`optional_required_counts[${rangeNumber}]`, optionalRequiredCounts[rangeNumber]);
        });
        formData.append('test_week', $('#edit_questions_test_week').val());
        formData.append('test_date', $('#edit_questions_test_date').val());
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // Show loading
        Swal.fire({
            title: i18nEp.updating_questions,
            text: i18nEp.updating_questions_wait,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/update_exam_paper_questions/${paperID}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire(i18nEp.success, response.success || i18nEp.questions_updated, 'success').then(() => {
                    sxHideModal('#editExamPaperQuestionsModal');
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = i18nEp.fail_update_questions;
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire(i18nEp.error, errorMsg, 'error');
            }
        });
    });

    $(document).on('click', '.remove-question-row', function() {
        const $container = $(this).closest('.question-row').parent();
        $(this).closest('.question-row').remove();
        ensureAtLeastOneRow($container);
    });

    $(document).on('input', '.question-marks, .question-description', function() {
        const $container = $(this).closest('[id^="question-rows"]');
        updateTotalMarks($container);
    });

    $(document).on('change', '.question-optional', function() {
        const $container = $(this).closest('[id^="question-rows"]');
        updateTotalMarks($container);
    });

    $(document).on('input', '.optional-total-input', function() {
        const $wrapper = $(this).closest('.optional-range-item');
        const target = $wrapper.data('wrapper-for');
        const expectedTotal = getExpectedFormatTotal(target);
        const optionalTotals = getOptionalTotals(target);
        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const hasError = optionalTotalSum > expectedTotal;

        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-error`)
            .text(`Optional totals exceed ${expectedTotal}.`)
            .toggleClass('d-none', !hasError);
        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-input`).toggleClass('is-invalid', hasError);
        updateTotalMarks($(target));
    });

    $(document).on('input', '.optional-required-input, .question-optional', function() {
        const $wrapper = $(this).closest('.optional-range-item');
        const target = $wrapper.length ? $wrapper.data('wrapper-for') : $(this).closest('[id^="question-rows"]').attr('id');
        const targetId = target.startsWith('#') ? target : `#${target}`;
        const optionalRequiredCounts = getOptionalRequiredCounts(targetId);
        const optionalQuestionsCountByRange = {};
        $(`${targetId} .question-optional`).each(function() {
            const rangeNumber = parseInt($(this).val(), 10);
            if (rangeNumber > 0) {
                optionalQuestionsCountByRange[rangeNumber] = (optionalQuestionsCountByRange[rangeNumber] || 0) + 1;
            }
        });

        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            const requiredCount = optionalRequiredCounts[rangeNumber] || 0;
            const available = optionalQuestionsCountByRange[rangeNumber] || 0;
            const isInvalid = requiredCount > available;
            $(this).find('.optional-required-error').toggleClass('d-none', !isInvalid);
            $(this).find('.optional-required-input').toggleClass('is-invalid', isInvalid);
        });
        updateTotalMarks($(targetId));
    });

    $(document).on('click', '.add-optional-range', function() {
        const target = $(this).data('target');
        addOptionalRange(target);
    });

    $(document).on('click', '.remove-optional-range', function() {
        const $item = $(this).closest('.optional-range-item');
        const target = $item.data('wrapper-for');
        const rangeNumber = $item.data('range-number');
        $item.remove();
        $(`.question-optional option[value="${rangeNumber}"]`).each(function() {
            if ($(this).closest('select').val() == rangeNumber) {
                $(this).closest('select').val('0');
            }
        });
        refreshOptionalRangeSelects(target);
        const optionalTotals = getOptionalTotals(target);
        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const expectedTotal = getExpectedFormatTotal(target);
        const hasError = optionalTotalSum > expectedTotal;
        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-error`).toggleClass('d-none', !hasError);
        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-input`).toggleClass('is-invalid', hasError);
        updateTotalMarks($(target));
    });

    // Resubmit rejected exam paper
    $(document).on('click', '.resubmit-paper-btn', function() {
        const paperID = $(this).data('paper-id');
        const $btn = $(this);

        Swal.fire({
            title: i18nEp.resubmit_title,
            text: i18nEp.resubmit_text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> ' + i18nEp.yes_resubmit
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: i18nEp.resubmitting,
                    text: i18nEp.please_wait,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '/resubmit_exam_paper/' + paperID,
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire(i18nEp.success, response.success || i18nEp.resubmitted_ok, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = i18nEp.fail_resubmit;
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire(i18nEp.error, errorMsg, 'error');
                    }
                });
            }
        });
    });

});
</script>
@endpush
