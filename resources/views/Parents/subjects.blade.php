@extends('layouts.parent_corp')

@section('title', __('parent.curriculum_subjects') . ' - ShuleXpert')
@section('page_title', __('parent.curriculum_subjects'))
@section('page_breadcrumb', __('parent.nav_subjects'))

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects span:not(.badge):not(.status-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .text-muted {
        color: #000 !important;
    }
    .sx-parent-subjects .btn-primary,
    .sx-parent-subjects a.btn-primary,
    .sx-parent-subjects .badge,
    .sx-parent-subjects .selection-card.selected,
    .sx-parent-subjects .selection-card.selected *,
    .sx-parent-subjects .selection-card.selected small,
    .sx-parent-subjects .selection-card.selected strong,
    .sx-parent-subjects .selection-card.selected i,
    .sx-parent-subjects .selection-card.selected .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .selection-card.selected,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .selection-card.selected *,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .selection-card.selected small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .selection-card.selected strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .selection-card.selected i,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-subjects .selection-card.selected .text-muted {
        color: #fff !important;
    }
    .sx-parent-subjects .card,
    .sx-parent-subjects .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-subjects .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-subjects .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-subjects .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-subjects .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }
    .sx-parent-subjects .selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }
    .sx-parent-subjects .selection-card {
        border: 1px solid #e6e6e6;
        border-top: 3px solid transparent;
        border-radius: 0;
        padding: 1rem 0.85rem;
        text-align: center;
        cursor: pointer;
        transition: box-shadow 0.15s ease, transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .sx-parent-subjects .selection-card:hover {
        border-color: #e0b0b0;
        border-top-color: #940000;
        background: #fff8f8;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.1);
    }
    .sx-parent-subjects .selection-card.selected {
        border-color: #940000;
        border-top-color: #940000;
        background: #940000;
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.18);
    }
    .sx-parent-subjects .selection-card.selected .text-muted {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .sx-parent-subjects .selection-card i {
        font-size: 1.35rem;
        margin-bottom: 0.45rem;
        display: block;
        color: #940000;
    }
    .sx-parent-subjects .selection-card.selected i {
        color: #fff !important;
    }
    .sx-parent-subjects .empty-state {
        text-align: center;
        padding: 2.75rem 1.25rem;
        background: #fff;
        border: 1px solid #e6e6e6;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-subjects .empty-state i {
        font-size: 2.5rem;
        color: #940000;
        opacity: 0.55;
        display: block;
        margin-bottom: 0.75rem;
    }
    .sx-parent-subjects .subjects-panel {
        overflow: hidden;
    }
    .sx-parent-subjects .subjects-panel .card-header {
        background: #fff;
        border-bottom: 1px solid #eee;
        padding: 1rem 1.25rem;
    }
    .sx-parent-subjects .subjects-panel .card-header h6 {
        margin: 0;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        font-size: 0.85rem;
    }
    .sx-parent-subjects .subjects-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #f0e6e6;
        background: #fffaf9;
    }
    .sx-parent-subjects .subjects-count {
        font-size: 0.78rem;
        font-weight: 700;
        color: #666 !important;
    }
    .sx-parent-subjects .subjects-count strong {
        color: #940000 !important;
    }
    .sx-parent-subjects .subjects-search {
        position: relative;
        min-width: 220px;
        max-width: 280px;
        width: 100%;
    }
    .sx-parent-subjects .subjects-search i {
        position: absolute;
        left: 0.7rem;
        top: 50%;
        transform: translateY(-50%);
        color: #940000;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .sx-parent-subjects .subjects-search input {
        width: 100%;
        border: 1px solid #e6d0d0;
        border-radius: 0.25rem;
        padding: 0.45rem 0.7rem 0.45rem 2rem;
        font-size: 0.85rem;
        background: #fff;
        outline: none;
    }
    .sx-parent-subjects .subjects-search input:focus {
        border-color: #940000;
        box-shadow: 0 0 0 3px rgba(148, 0, 0, 0.08);
    }
    .sx-parent-subjects .subjects-table-wrap {
        overflow-x: auto;
    }
    .sx-parent-subjects .subjects-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        background: #fff;
    }
    .sx-parent-subjects .subjects-table thead th {
        background: #fcf7f7;
        color: #940000 !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        font-weight: 800;
        border-bottom: 2px solid rgba(148, 0, 0, 0.18);
        border-right: 1px solid #f0e8e8;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.9rem 1rem;
    }
    .sx-parent-subjects .subjects-table thead th:last-child {
        border-right: none;
    }
    .sx-parent-subjects .subjects-table tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f0ecec;
        border-right: 1px solid #f6f1f1;
        font-size: 0.9rem;
        padding: 0.95rem 1rem;
        background: #fff;
    }
    .sx-parent-subjects .subjects-table tbody td:last-child {
        border-right: none;
    }
    .sx-parent-subjects .subjects-table tbody tr:nth-child(even) td {
        background: #fffcfc;
    }
    .sx-parent-subjects .subjects-table tbody tr:hover td {
        background: #fff5f5 !important;
    }
    .sx-parent-subjects .subjects-table tbody tr:last-child td {
        border-bottom: none;
    }
    .sx-parent-subjects .col-num {
        width: 56px;
        text-align: center;
    }
    .sx-parent-subjects .col-req {
        width: 120px;
    }
    .sx-parent-subjects .col-act {
        width: 140px;
        text-align: center;
    }
    .sx-parent-subjects .row-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fff0f0;
        border: 1px solid #f0d0d0;
        color: #940000 !important;
        font-weight: 800;
        font-size: 0.75rem;
    }
    .sx-parent-subjects .subj-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 180px;
    }
    .sx-parent-subjects .subj-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        color: #940000 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.78rem;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .sx-parent-subjects .subj-name {
        font-weight: 800;
        color: #1a1a1a !important;
        line-height: 1.25;
        margin-bottom: 0.15rem;
    }
    .sx-parent-subjects .subj-code {
        font-size: 0.72rem;
        color: #888 !important;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .sx-parent-subjects .teacher-cell {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 140px;
    }
    .sx-parent-subjects .teacher-cell i {
        color: #940000;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .sx-parent-subjects .teacher-name {
        font-weight: 600;
        color: #333 !important;
        line-height: 1.3;
    }
    .sx-parent-subjects .teacher-none {
        color: #999 !important;
        font-style: italic;
        font-size: 0.82rem;
    }
    .sx-parent-subjects .status-badge {
        font-size: 0.65rem;
        padding: 0.35rem 0.65rem;
        text-transform: uppercase;
        font-weight: 800;
        border-radius: 0 !important;
        letter-spacing: 0.04em;
        display: inline-block;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .sx-parent-subjects .status-badge.req {
        background: #fff3cd;
        color: #664d03 !important;
        border-color: #ffe69c;
    }
    .sx-parent-subjects .status-badge.opt {
        background: #e8f7fb;
        color: #055160 !important;
        border-color: #b6effb;
    }
    .sx-parent-subjects .status-badge.elected {
        background: #d1e7dd;
        color: #0f5132 !important;
        border-color: #a3cfbb;
        margin-left: 0.35rem;
    }
    .sx-parent-subjects .btn-elect,
    .sx-parent-subjects .btn-deselect {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-radius: 0;
        padding: 0.4rem 0.75rem;
        min-width: 100px;
    }
    .sx-parent-subjects .btn-deselect {
        background: #940000;
        border-color: #940000;
        color: #fff !important;
    }
    .sx-parent-subjects .btn-deselect:hover {
        background: #7a0000;
        border-color: #7a0000;
        color: #fff !important;
    }
    .sx-parent-subjects .act-dash {
        color: #bbb !important;
        font-weight: 700;
    }
    .sx-parent-subjects .subjects-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.75rem 1.15rem;
        border-top: 1px solid #f0e6e6;
        background: #fff;
        font-size: 0.8rem;
        color: #666 !important;
    }
    .sx-parent-subjects .subjects-empty-filter {
        display: none;
        text-align: center;
        padding: 1.5rem 1rem;
        color: #888 !important;
        font-size: 0.9rem;
        border-top: 1px solid #f0e6e6;
    }

    @media (max-width: 768px) {
        .sx-parent-subjects .selection-grid {
            grid-template-columns: 1fr;
        }
        .sx-parent-subjects .subjects-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .sx-parent-subjects .subjects-search {
            max-width: none;
        }
        .sx-parent-subjects .subjects-table thead th,
        .sx-parent-subjects .subjects-table tbody td {
            padding: 0.75rem 0.65rem;
            font-size: 0.82rem;
        }
        .sx-parent-subjects .subj-icon {
            width: 34px;
            height: 34px;
            font-size: 0.7rem;
        }
        .sx-parent-subjects .col-act {
            width: 110px;
        }
        .sx-parent-subjects .btn-elect,
        .sx-parent-subjects .btn-deselect {
            min-width: 0;
            padding: 0.35rem 0.5rem;
        }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-subjects .card,
    html[data-bs-theme="dark"] .sx-parent-subjects .card,
    html[data-sx-theme="dark"] .sx-parent-subjects .panel-card,
    html[data-bs-theme="dark"] .sx-parent-subjects .panel-card,
    html[data-sx-theme="dark"] .sx-parent-subjects .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-subjects .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-subjects .selection-card,
    html[data-bs-theme="dark"] .sx-parent-subjects .selection-card,
    html[data-sx-theme="dark"] .sx-parent-subjects .empty-state,
    html[data-bs-theme="dark"] .sx-parent-subjects .empty-state,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-panel .card-header,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-panel .card-header,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-toolbar,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-toolbar,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-footer,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-footer,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-table,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-table {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects,
    html[data-bs-theme="dark"] .sx-parent-subjects,
    html[data-sx-theme="dark"] .sx-parent-subjects p,
    html[data-bs-theme="dark"] .sx-parent-subjects p,
    html[data-sx-theme="dark"] .sx-parent-subjects span:not(.badge):not(.status-badge),
    html[data-bs-theme="dark"] .sx-parent-subjects span:not(.badge):not(.status-badge),
    html[data-sx-theme="dark"] .sx-parent-subjects small,
    html[data-bs-theme="dark"] .sx-parent-subjects small,
    html[data-sx-theme="dark"] .sx-parent-subjects strong,
    html[data-bs-theme="dark"] .sx-parent-subjects strong,
    html[data-sx-theme="dark"] .sx-parent-subjects div,
    html[data-bs-theme="dark"] .sx-parent-subjects div,
    html[data-sx-theme="dark"] .sx-parent-subjects .text-muted,
    html[data-bs-theme="dark"] .sx-parent-subjects .text-muted,
    html[data-sx-theme="dark"] .sx-parent-subjects .subj-name,
    html[data-bs-theme="dark"] .sx-parent-subjects .subj-name,
    html[data-sx-theme="dark"] .sx-parent-subjects .subj-code,
    html[data-bs-theme="dark"] .sx-parent-subjects .subj-code,
    html[data-sx-theme="dark"] .sx-parent-subjects .teacher-name,
    html[data-bs-theme="dark"] .sx-parent-subjects .teacher-name,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-count,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-count {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .section-label,
    html[data-bs-theme="dark"] .sx-parent-subjects .section-label,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-panel .card-header h6,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-panel .card-header h6,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-table thead th,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-table thead th,
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-count strong,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-count strong,
    html[data-sx-theme="dark"] .sx-parent-subjects .row-num,
    html[data-bs-theme="dark"] .sx-parent-subjects .row-num {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-table thead th,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-table thead th {
        background: rgba(148, 0, 0, 0.22) !important;
        border-bottom-color: rgba(255, 180, 180, 0.2) !important;
        border-right-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-table tbody td,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-table tbody td {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-table tbody tr:nth-child(even) td,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-table tbody tr:nth-child(even) td {
        background: #2a1c21 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-table tbody tr:hover td,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-table tbody tr:hover td {
        background: rgba(255, 180, 180, 0.08) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .subj-icon,
    html[data-bs-theme="dark"] .sx-parent-subjects .subj-icon,
    html[data-sx-theme="dark"] .sx-parent-subjects .row-num,
    html[data-bs-theme="dark"] .sx-parent-subjects .row-num {
        background: rgba(148, 0, 0, 0.25) !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .subjects-search input,
    html[data-bs-theme="dark"] .sx-parent-subjects .subjects-search input {
        background: #1c1216 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .selection-card:hover,
    html[data-bs-theme="dark"] .sx-parent-subjects .selection-card:hover {
        background: rgba(255, 180, 180, 0.08) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .selection-card.selected,
    html[data-bs-theme="dark"] .sx-parent-subjects .selection-card.selected {
        background: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-parent-subjects .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-subjects .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
</style>

<div class="sx-parent-subjects">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi fi-rr-books me-1" style="color:#940000;"></i>
                                {{ __('parent.curriculum_subjects') }}
                            </h5>
                            <p class="text-muted small mb-0">{{ __('parent.select_student_hint') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="hint-strip">
                <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                {{ __('parent.select_student_to_view') }}
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="section-label">
                        <i class="fi fi-rr-users me-1"></i>
                        {{ __('parent.select_student') }}
                    </div>

                    @if($students->isEmpty())
                        <div class="alert alert-warning mb-0">{{ __('parent.no_students') }}</div>
                    @else
                        <div class="selection-grid" id="studentGrid">
                            @foreach($students as $student)
                                @php
                                    $classLabel = trim(($student->subclass->class->class_name ?? '') . ' ' . ($student->subclass->subclass_name ?? ''));
                                @endphp
                                <div class="selection-card" data-value="{{ $student->studentID }}" role="button" tabindex="0">
                                    <i class="fi fi-rr-user"></i>
                                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                    @if(!empty($student->admission_number))
                                        <br><small class="text-muted">{{ $student->admission_number }}</small>
                                    @endif
                                    @if($classLabel !== '')
                                        <br><small style="color:#940000;font-weight:700;">{{ $classLabel }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12" id="subjectsContainer">
            <div class="empty-state">
                <i class="fi fi-rr-book-open-reader"></i>
                <h5 class="mb-1">{{ __('parent.select_student') }}</h5>
                <p class="text-muted small mb-0">{{ __('parent.select_student_to_view') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const P = @json(trans('parent'));
        let selectedStudentID = null;

        function esc(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function subjectInitials(name) {
            var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return 'S';
            if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }

        function updateSubjectsFilter() {
            var q = ($('#subjectsSearchInput').val() || '').toLowerCase().trim();
            var visible = 0;
            var total = $('#subjectsTable tbody tr.subject-row').length;

            $('#subjectsTable tbody tr.subject-row').each(function() {
                var hay = ($(this).attr('data-search') || '').toLowerCase();
                var show = !q || hay.indexOf(q) !== -1;
                $(this).toggle(show);
                if (show) visible++;
            });

            $('#subjectsVisibleCount').text(visible);
            $('#subjectsEmptyFilter').toggle(total > 0 && visible === 0);
        }

        function loadSubjects(studentID) {
            if (!studentID) return;

            selectedStudentID = studentID;
            $('#subjectsContainer').html(
                '<div class="card panel-card"><div class="card-body text-center py-5">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '</div></div>'
            );

            $.ajax({
                url: "{{ route('get_student_subjects', ':id') }}".replace(':id', studentID),
                type: "GET",
                success: function(response) {
                    if (response.success && response.subjects) {
                        var total = response.subjects.length;
                        var html = '<div class="card panel-card subjects-panel">';
                        html += '<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">';
                        html += '<h6><i class="fi fi-rr-list me-1"></i> ' + esc(P.subject_list_for.replace(':name', response.student.first_name)) + '</h6>';
                        if (response.student.subclass_name) {
                            html += '<span class="badge bg-primary">' + esc(response.student.subclass_name) + '</span>';
                        }
                        html += '</div>';

                        html += '<div class="subjects-toolbar">';
                        html += '<div class="subjects-count"><strong id="subjectsVisibleCount">' + total + '</strong> / ' + total + ' ' + esc(P.subjects || P.subject) + '</div>';
                        html += '<div class="subjects-search">';
                        html += '<i class="fi fi-rr-search"></i>';
                        html += '<input type="search" id="subjectsSearchInput" placeholder="' + esc(P.search || 'Search...') + '" autocomplete="off">';
                        html += '</div></div>';

                        html += '<div class="subjects-table-wrap">';
                        html += '<table class="subjects-table" id="subjectsTable">';
                        html += '<thead><tr>';
                        html += '<th class="col-num">#</th>';
                        html += '<th>' + esc(P.subject) + '</th>';
                        html += '<th>' + esc(P.teacher) + '</th>';
                        html += '<th class="col-req">' + esc(P.requirement) + '</th>';
                        html += '<th class="col-act">' + esc(P.actions) + '</th>';
                        html += '</tr></thead><tbody>';

                        if (!total) {
                            html += '<tr><td colspan="5" class="text-center text-muted py-5">' + esc(P.no_subjects || '—') + '</td></tr>';
                        }

                        response.subjects.forEach(function(s, idx) {
                            var isRequired = s.student_status === 'Required';
                            var reqClass = isRequired ? 'req' : 'opt';
                            var reqLabel = isRequired ? P.required : P.optional;
                            var hasTeacher = s.teacher_name && s.teacher_name !== 'Not Assigned';
                            var code = s.subject_code || '---';
                            var teacherLabel = hasTeacher ? s.teacher_name : P.no_teacher;
                            var searchBlob = [s.subject_name, code, teacherLabel, reqLabel].join(' ');

                            html += '<tr class="subject-row" data-search="' + esc(searchBlob) + '">';
                            html += '<td class="col-num"><span class="row-num">' + (idx + 1) + '</span></td>';
                            html += '<td><div class="subj-cell">';
                            html += '<span class="subj-icon">' + esc(subjectInitials(s.subject_name)) + '</span>';
                            html += '<div><div class="subj-name">' + esc(s.subject_name) + '</div>';
                            html += '<div class="subj-code">' + esc(code) + '</div></div></div></td>';
                            html += '<td><div class="teacher-cell">';
                            if (hasTeacher) {
                                html += '<i class="fi fi-rr-chalkboard-user"></i><span class="teacher-name">' + esc(s.teacher_name) + '</span>';
                            } else {
                                html += '<i class="fi fi-rr-user"></i><span class="teacher-none">' + esc(P.no_teacher) + '</span>';
                            }
                            html += '</div></td>';
                            html += '<td class="col-req"><span class="status-badge ' + reqClass + '">' + esc(reqLabel) + '</span>';
                            if (!isRequired && s.is_elected) {
                                html += '<span class="status-badge elected">' + esc(P.elected || 'Elected') + '</span>';
                            }
                            html += '</td>';
                            html += '<td class="col-act">';
                            if (s.student_status === 'Optional' && s.status === 'Active') {
                                if (s.is_elected) {
                                    html += '<button type="button" class="btn btn-sm btn-deselect deselect-subject-btn" data-sid="' + esc(response.student.studentID) + '" data-csid="' + esc(s.class_subjectID) + '" data-name="' + esc(s.subject_name) + '">' + esc(P.deselect) + '</button>';
                                } else {
                                    html += '<button type="button" class="btn btn-sm btn-primary btn-elect elect-subject-btn" data-sid="' + esc(response.student.studentID) + '" data-csid="' + esc(s.class_subjectID) + '" data-name="' + esc(s.subject_name) + '">' + esc(P.elect_subject) + '</button>';
                                }
                            } else {
                                html += '<span class="act-dash">—</span>';
                            }
                            html += '</td></tr>';
                        });

                        html += '</tbody></table></div>';
                        html += '<div class="subjects-empty-filter" id="subjectsEmptyFilter">' + esc(P.no_search_child || P.no_subjects || '—') + '</div>';
                        if (total) {
                            html += '<div class="subjects-footer">' + esc(P.subject_list_for.replace(':name', response.student.first_name)) + '</div>';
                        }
                        html += '</div>';
                        $('#subjectsContainer').html(html);
                    } else {
                        $('#subjectsContainer').html(
                            '<div class="alert alert-warning mb-0">' + esc(response.error || P.error) + '</div>'
                        );
                    }
                },
                error: function() {
                    $('#subjectsContainer').html(
                        '<div class="alert alert-danger mb-0">' + esc(P.error) + '</div>'
                    );
                }
            });
        }

        $(document).on('keyup search input', '#subjectsSearchInput', updateSubjectsFilter);

        $(document).on('click', '#studentGrid .selection-card', function() {
            var studentID = $(this).data('value');
            $('#studentGrid .selection-card').removeClass('selected');
            $(this).addClass('selected');
            loadSubjects(studentID);
        });

        $(document).on('keydown', '#studentGrid .selection-card', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        $(document).on('click', '.elect-subject-btn, .deselect-subject-btn', function() {
            var isElect = $(this).hasClass('elect-subject-btn');
            var url = isElect ? "{{ route('elect_subject') }}" : "{{ route('deselect_subject') }}";
            var sid = $(this).data('sid');
            var csid = $(this).data('csid');
            var name = $(this).data('name');

            Swal.fire({
                title: isElect ? P.elect_q : P.deselect_q,
                text: P.confirm_for.replace(':name', name),
                icon: isElect ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: P.confirm
            }).then(function(res) {
                if (res.isConfirmed) {
                    $.post(url, {
                        studentID: sid,
                        classSubjectID: csid,
                        _token: '{{ csrf_token() }}'
                    }, function(r) {
                        if (r.success) {
                            Swal.fire(P.success, r.success, 'success').then(function() {
                                loadSubjects(selectedStudentID || sid);
                            });
                        } else {
                            Swal.fire(P.error, r.error || P.error, 'error');
                        }
                    }).fail(function() {
                        Swal.fire(P.error, P.error, 'error');
                    });
                }
            });
        });
    });
</script>
@endpush
