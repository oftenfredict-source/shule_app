@extends('layouts.parent_corp')

@section('title', (($kind ?? 'note') === 'past_paper' ? __('parent.past_papers') : __('parent.notes')) . ' - ShuleXpert')
@section('page_title', (($kind ?? 'note') === 'past_paper' ? __('parent.past_papers') : __('parent.notes')))
@section('page_breadcrumb', (($kind ?? 'note') === 'past_paper' ? __('parent.nav_past_papers') : __('parent.nav_notes')))

@section('content')
@php
    $kind = $kind ?? 'note';
    $isPaper = $kind === 'past_paper';
    $t = [
        'page' => $isPaper ? __('parent.past_papers') : __('parent.notes'),
        'pick_child' => __('parent.pick_child'),
        'no_child' => __('parent.no_child'),
        'notes' => $isPaper ? __('parent.past_papers_count_label') : __('parent.notes_count_label'),
        'back' => __('parent.back'),
        'subjects' => __('parent.subjects'),
        'loading' => __('parent.loading'),
        'fail' => __('parent.load_failed'),
        'no_notes' => $isPaper ? __('parent.no_past_papers') : __('parent.no_notes'),
        'view' => __('parent.view'),
        'title' => __('parent.title'),
        'file' => __('parent.file'),
        'teacher' => __('parent.teacher'),
        'close' => __('parent.close'),
        'download' => __('parent.download'),
        'page_icon' => $isPaper ? 'fi-rr-document' : 'fi-rr-notebook',
        'item_icon' => $isPaper ? 'fi-rr-file' : 'fi-rr-notes',
    ];
@endphp
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes span:not(.badge):not(.pn-unread),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-notes .text-muted {
        color: #000 !important;
    }
    .sx-parent-notes .btn-primary,
    .sx-parent-notes a.btn-primary,
    .sx-parent-notes .badge,
    .sx-parent-notes .pn-unread {
        color: #fff !important;
    }
    .sx-parent-notes .card,
    .sx-parent-notes .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-notes .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-notes .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-notes .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-notes .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }
    .sx-parent-notes .pn-nav-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        width: 100%;
        text-align: left;
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        padding: 1.05rem 1.15rem;
        cursor: pointer;
        transition: box-shadow 0.15s ease, transform 0.15s ease, background 0.15s ease;
        color: inherit;
    }
    .sx-parent-notes .pn-nav-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
        background: #fff8f8;
        color: inherit;
    }
    .sx-parent-notes .pn-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        border: 1px solid #f0d0d0;
        background: #fff5f5;
        color: #940000 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        overflow: hidden;
    }
    .sx-parent-notes .pn-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sx-parent-notes .pn-name {
        font-weight: 800;
        font-size: 0.98rem;
        color: #222 !important;
        line-height: 1.3;
        margin-bottom: 0.15rem;
    }
    .sx-parent-notes .pn-meta {
        font-size: 0.75rem;
        color: #777 !important;
        font-weight: 600;
        line-height: 1.4;
    }
    .sx-parent-notes .pn-unread {
        background: #c40000;
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        font-size: 0.72rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
        border-radius: 0;
    }
    .sx-parent-notes .pn-chevron {
        color: #940000 !important;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .sx-parent-notes .pn-stat {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #940000 !important;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        padding: 0.2rem 0.45rem;
        display: inline-block;
        margin-top: 0.35rem;
    }
    .sx-parent-notes .stat-pill {
        position: relative;
        text-align: center;
        padding: 0.95rem 0.55rem;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        background: #fff;
        height: 100%;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        cursor: pointer;
        transition: box-shadow 0.15s ease, transform 0.15s ease, background 0.15s ease;
        width: 100%;
        color: inherit;
    }
    .sx-parent-notes .stat-pill:hover {
        background: #fff8f8;
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.1);
        transform: translateY(-1px);
        color: inherit;
    }
    .sx-parent-notes .stat-pill.selected {
        background: #fff0f0;
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.14);
    }
    .sx-parent-notes .stat-pill .num {
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1;
        color: #940000 !important;
    }
    .sx-parent-notes .stat-pill .txt {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #777 !important;
        font-weight: 700;
        margin-top: 0.4rem;
        line-height: 1.35;
        word-break: break-word;
    }
    .sx-parent-notes .stat-pill .sub {
        font-size: 0.58rem;
        color: #999 !important;
        font-weight: 600;
        margin-top: 0.2rem;
        line-height: 1.3;
        text-transform: none;
        letter-spacing: 0;
    }
    .sx-parent-notes .stat-pill .pn-unread {
        position: absolute;
        top: 6px;
        right: 6px;
        min-width: 18px;
        height: 18px;
        font-size: 0.62rem;
        padding: 0 4px;
    }
    .sx-parent-notes .empty-state {
        text-align: center;
        padding: 2.5rem 1.25rem;
        background: #fff;
        border: 1px solid #e6e6e6;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-notes .empty-state i {
        font-size: 2.4rem;
        color: #940000;
        opacity: 0.5;
        display: block;
        margin-bottom: 0.75rem;
    }
    .sx-parent-notes .loading-state {
        text-align: center;
        padding: 2rem 1rem;
        background: #fff;
        border: 1px solid #e6e6e6;
    }

    /* Modal */
    .sx-notes-modal .modal-content {
        border-radius: 0.35rem;
        border: 1px solid #e6e6e6;
        overflow: hidden;
    }
    .sx-notes-modal .modal-header {
        background: #940000;
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.25rem;
    }
    .sx-notes-modal .modal-title {
        font-weight: 800;
        font-size: 1rem;
        color: #fff !important;
    }
    .sx-notes-modal .modal-body {
        padding: 0;
        background: #fff;
    }
    .sx-notes-modal .modal-footer {
        border-top: 1px solid #eee;
        background: #fff;
        padding: 0.85rem 1.15rem;
    }
    .sx-notes-modal .pn-modal-loading,
    .sx-notes-modal .pn-modal-empty {
        padding: 2.5rem 1.25rem;
        text-align: center;
    }
    .sx-notes-modal .pn-pkg-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #f0e6e6;
        background: #fffaf9;
    }
    .sx-notes-modal .pn-pkg-count {
        font-size: 0.78rem;
        font-weight: 700;
        color: #666 !important;
    }
    .sx-notes-modal .pn-pkg-count strong {
        color: #940000 !important;
    }
    .sx-notes-modal .pn-note-row {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.95rem 1.15rem;
        border-bottom: 1px solid #f0ecec;
        background: #fff;
    }
    .sx-notes-modal .pn-note-row:nth-child(even) {
        background: #fffcfc;
    }
    .sx-notes-modal .pn-note-row:last-child {
        border-bottom: none;
    }
    .sx-notes-modal .pn-note-row:hover {
        background: #fff5f5;
    }
    .sx-notes-modal .pn-note-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid #f0d0d0;
        background: #fff5f5;
        color: #940000 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .sx-notes-modal .pn-note-name {
        font-weight: 800;
        color: #1a1a1a !important;
        line-height: 1.3;
        margin-bottom: 0.15rem;
    }
    .sx-notes-modal .pn-note-meta {
        font-size: 0.72rem;
        color: #888 !important;
        font-weight: 600;
        word-break: break-word;
    }
    .sx-notes-modal .btn-view {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-radius: 0;
        padding: 0.35rem 0.65rem;
    }
    .sx-notes-modal .btn-view:hover {
        border-color: #940000;
        color: #940000 !important;
        background: #fff8f8;
    }
    .sx-notes-modal .btn-dl {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-radius: 0;
        padding: 0.35rem 0.65rem;
    }
    .sx-notes-modal .pn-note-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        flex-shrink: 0;
        margin-left: auto;
    }

    @media (max-width: 576px) {
        .sx-parent-notes .stat-pill .num {
            font-size: 1rem;
        }
        .sx-notes-modal .pn-note-row {
            flex-wrap: wrap;
        }
        .sx-notes-modal .pn-note-actions {
            width: 100%;
            margin-left: 0;
            margin-top: 0.35rem;
        }
        .sx-notes-modal .btn-view,
        .sx-notes-modal .btn-dl {
            flex: 1;
        }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-notes .card,
    html[data-bs-theme="dark"] .sx-parent-notes .card,
    html[data-sx-theme="dark"] .sx-parent-notes .panel-card,
    html[data-bs-theme="dark"] .sx-parent-notes .panel-card,
    html[data-sx-theme="dark"] .sx-parent-notes .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-notes .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-nav-card,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-nav-card,
    html[data-sx-theme="dark"] .sx-parent-notes .empty-state,
    html[data-bs-theme="dark"] .sx-parent-notes .empty-state,
    html[data-sx-theme="dark"] .sx-parent-notes .loading-state,
    html[data-bs-theme="dark"] .sx-parent-notes .loading-state {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .pn-nav-card,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-nav-card {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes,
    html[data-bs-theme="dark"] .sx-parent-notes,
    html[data-sx-theme="dark"] .sx-parent-notes p,
    html[data-bs-theme="dark"] .sx-parent-notes p,
    html[data-sx-theme="dark"] .sx-parent-notes span:not(.badge):not(.pn-unread),
    html[data-bs-theme="dark"] .sx-parent-notes span:not(.badge):not(.pn-unread),
    html[data-sx-theme="dark"] .sx-parent-notes small,
    html[data-bs-theme="dark"] .sx-parent-notes small,
    html[data-sx-theme="dark"] .sx-parent-notes strong,
    html[data-bs-theme="dark"] .sx-parent-notes strong,
    html[data-sx-theme="dark"] .sx-parent-notes div,
    html[data-bs-theme="dark"] .sx-parent-notes div,
    html[data-sx-theme="dark"] .sx-parent-notes .text-muted,
    html[data-bs-theme="dark"] .sx-parent-notes .text-muted,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-name,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-name,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-meta,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-meta {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .section-label,
    html[data-bs-theme="dark"] .sx-parent-notes .section-label,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-chevron,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-chevron,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-stat,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-stat {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .pn-icon,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-icon,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-stat,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-stat {
        background: rgba(148, 0, 0, 0.25) !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .stat-pill,
    html[data-bs-theme="dark"] .sx-parent-notes .stat-pill {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .stat-pill:hover,
    html[data-bs-theme="dark"] .sx-parent-notes .stat-pill:hover,
    html[data-sx-theme="dark"] .sx-parent-notes .stat-pill.selected,
    html[data-bs-theme="dark"] .sx-parent-notes .stat-pill.selected,
    html[data-sx-theme="dark"] .sx-parent-notes .pn-nav-card:hover,
    html[data-bs-theme="dark"] .sx-parent-notes .pn-nav-card:hover {
        background: rgba(255, 180, 180, 0.08) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .stat-pill .num,
    html[data-bs-theme="dark"] .sx-parent-notes .stat-pill .num {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .stat-pill .txt,
    html[data-bs-theme="dark"] .sx-parent-notes .stat-pill .txt,
    html[data-sx-theme="dark"] .sx-parent-notes .stat-pill .sub,
    html[data-bs-theme="dark"] .sx-parent-notes .stat-pill .sub {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-notes .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-notes .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-notes-modal .modal-content,
    html[data-bs-theme="dark"] .sx-notes-modal .modal-content,
    html[data-sx-theme="dark"] .sx-notes-modal .modal-body,
    html[data-bs-theme="dark"] .sx-notes-modal .modal-body,
    html[data-sx-theme="dark"] .sx-notes-modal .modal-footer,
    html[data-bs-theme="dark"] .sx-notes-modal .modal-footer,
    html[data-sx-theme="dark"] .sx-notes-modal .pn-pkg-toolbar,
    html[data-bs-theme="dark"] .sx-notes-modal .pn-pkg-toolbar,
    html[data-sx-theme="dark"] .sx-notes-modal .pn-note-row,
    html[data-bs-theme="dark"] .sx-notes-modal .pn-note-row {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-notes-modal .pn-note-row:nth-child(even),
    html[data-bs-theme="dark"] .sx-notes-modal .pn-note-row:nth-child(even) {
        background: #2a1c21 !important;
    }
    html[data-sx-theme="dark"] .sx-notes-modal .pn-note-name,
    html[data-bs-theme="dark"] .sx-notes-modal .pn-note-name,
    html[data-sx-theme="dark"] .sx-notes-modal .pn-note-meta,
    html[data-bs-theme="dark"] .sx-notes-modal .pn-note-meta,
    html[data-sx-theme="dark"] .sx-notes-modal .pn-pkg-count,
    html[data-bs-theme="dark"] .sx-notes-modal .pn-pkg-count {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-notes-modal .pn-note-icon,
    html[data-bs-theme="dark"] .sx-notes-modal .pn-note-icon {
        background: rgba(148, 0, 0, 0.25) !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-notes-modal .btn-view,
    html[data-bs-theme="dark"] .sx-notes-modal .btn-view {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
</style>

<div class="sx-parent-notes">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi {{ $t['page_icon'] }} me-1" style="color:#940000;"></i>
                                {{ $t['page'] }}
                            </h5>
                            <p class="text-muted small mb-0" id="pnStepHint">{{ $t['pick_child'] }}</p>
                        </div>
                        <div id="pnBackWrap" style="display:none;">
                            <button type="button" class="btn btn-outline-light btn-shadow waves-effect" id="pnBackBtn">
                                <i class="fi fi-rr-angle-left me-1"></i> {{ $t['back'] }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="hint-strip" id="pnHintStrip">
                <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                <span id="pnHintText">{{ $t['pick_child'] }}</span>
            </div>
        </div>

        <div class="col-12">
            <div class="section-label" id="pnSectionLabel">
                <i class="fi fi-rr-users me-1"></i>
                <span id="pnSectionText">{{ $t['pick_child'] }}</span>
            </div>
            <div class="row g-3" id="pnList"></div>
        </div>
    </div>
</div>

<div class="modal fade sx-notes-modal" id="parentNotesListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parentNotesListTitle">{{ $t['page'] }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ $t['close'] }}"></button>
            </div>
            <div class="modal-body" id="parentNotesListBody">
                <div class="pn-modal-loading text-muted">{{ $t['loading'] }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light btn-shadow waves-effect" data-bs-dismiss="modal">{{ $t['close'] }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function($) {
    const T = @json($t);
    const children = @json($children);
    const subjectsUrl = @json($isPaper ? url('parentPastPapers') : url('parentNotes'));
    let step = 'children';
    let currentStudent = null;

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function unreadBadge(n) {
        const v = parseInt(n, 10) || 0;
        if (v < 1) return '';
        return '<span class="pn-unread" title="Unread">' + v + '</span>';
    }

    function notesMeta(count) {
        const v = parseInt(count, 10) || 0;
        return '<span class="pn-stat">' + v + ' ' + escapeHtml(T.notes) + '</span>';
    }

    function setChrome(hint, section, showBack) {
        $('#pnStepHint').text(hint);
        $('#pnHintText').text(hint);
        $('#pnSectionText').text(section);
        if (showBack) {
            $('#pnBackWrap').show();
            $('#pnSectionLabel i').attr('class', 'fi fi-rr-book me-1');
        } else {
            $('#pnBackWrap').hide();
            $('#pnSectionLabel i').attr('class', 'fi fi-rr-users me-1');
        }
    }

    function emptyHtml(msg) {
        return '<div class="col-12"><div class="empty-state">'
            + '<i class="fi ' + escapeHtml(T.page_icon) + '"></i>'
            + '<p class="text-muted small mb-0">' + escapeHtml(msg) + '</p>'
            + '</div></div>';
    }

    function loadingHtml() {
        return '<div class="col-12"><div class="loading-state">'
            + '<div class="spinner-border text-primary mb-2" role="status"></div>'
            + '<div class="text-muted small">' + escapeHtml(T.loading) + '</div>'
            + '</div></div>';
    }

    function renderChildren() {
        step = 'children';
        currentStudent = null;
        setChrome(T.pick_child, T.pick_child, false);

        if (!children.length) {
            $('#pnList').html(emptyHtml(T.no_child));
            return;
        }

        let html = '';
        children.forEach(function(c) {
            const photo = c.photo
                ? '<img src="' + escapeHtml(c.photo) + '" alt="">'
                : '<i class="fi fi-rr-user"></i>';
            html += '<div class="col-12 col-md-6">'
                + '<button type="button" class="pn-nav-card pn-child" data-id="' + escapeHtml(c.studentID) + '">'
                + '<span class="pn-icon">' + photo + '</span>'
                + '<div class="flex-grow-1 min-w-0">'
                + '<div class="pn-name text-truncate">' + escapeHtml(c.full_name) + '</div>'
                + '<div class="pn-meta text-truncate">'
                + escapeHtml(c.class_label || '')
                + (c.admission_number ? ' · ' + escapeHtml(c.admission_number) : '')
                + '</div>'
                + notesMeta(c.notes_count)
                + '</div>'
                + unreadBadge(c.unread_count)
                + '<i class="fi fi-rr-angle-right pn-chevron"></i>'
                + '</button></div>';
        });
        $('#pnList').html(html);
    }

    function renderSubjects(student, subjects) {
        step = 'subjects';
        currentStudent = student;
        currentStudent.subjects = subjects;
        const label = (student.full_name || '') + ' · ' + T.subjects;
        setChrome(label, T.subjects, true);

        if (!subjects.length) {
            $('#pnList').html(emptyHtml(T.no_notes));
            return;
        }

        let html = '<div class="col-12"><div class="row g-2" id="subjectGrid">';
        subjects.forEach(function(s) {
            const count = parseInt(s.notes_count, 10) || 0;
            html += '<div class="col-4 col-md-2">'
                + '<div class="stat-pill pn-subject"'
                + ' data-id="' + escapeHtml(s.class_subjectID) + '"'
                + ' data-name="' + escapeHtml(s.subject_name) + '"'
                + ' data-unread="' + (parseInt(s.unread_count, 10) || 0) + '"'
                + ' role="button" tabindex="0">'
                + unreadBadge(s.unread_count)
                + '<div class="num">' + count + '</div>'
                + '<div class="txt">' + escapeHtml(s.subject_name) + '</div>'
                + (s.teacher_name ? '<div class="sub">' + escapeHtml(s.teacher_name) + '</div>' : '')
                + '</div></div>';
        });
        html += '</div></div>';
        $('#pnList').html(html);
    }

    $(document).on('click', '.pn-child', function() {
        const id = $(this).data('id');
        $('#pnList').html(loadingHtml());
        $.get(subjectsUrl + '/' + id + '/subjects')
            .done(function(res) {
                renderSubjects(res.student || {}, res.subjects || []);
            })
            .fail(function() {
                $('#pnList').html(emptyHtml(T.fail));
            });
    });

    $(document).on('keydown', '.pn-subject', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    $('#pnBackBtn').on('click', function(e) {
        e.preventDefault();
        renderChildren();
    });

    $(document).on('click', '.pn-subject', function() {
        if (!currentStudent) return;
        $('#subjectGrid .stat-pill').removeClass('selected');
        $(this).addClass('selected');
        const csId = $(this).data('id');
        const name = $(this).data('name') || T.page;
        $('#parentNotesListTitle').text(name);
        $('#parentNotesListBody').html(
            '<div class="pn-modal-loading"><div class="spinner-border text-primary mb-2" role="status"></div><div class="text-muted">' + escapeHtml(T.loading) + '</div></div>'
        );
        bootstrap.Modal.getOrCreateInstance(document.getElementById('parentNotesListModal')).show();
        const unreadOpened = parseInt($(this).attr('data-unread'), 10) || 0;

        $.get(subjectsUrl + '/' + currentStudent.studentID + '/subjects/' + csId + '/notes')
            .done(function(res) {
                if (unreadOpened > 0) {
                    const $row = $('.pn-subject[data-id="' + csId + '"]');
                    $row.attr('data-unread', 0).find('.pn-unread').remove();
                    if (currentStudent.subjects) {
                        currentStudent.subjects.forEach(function(s) {
                            if (String(s.class_subjectID) === String(csId)) s.unread_count = 0;
                        });
                    }
                    const child = children.find(function(c) {
                        return String(c.studentID) === String(currentStudent.studentID);
                    });
                    if (child) {
                        child.unread_count = Math.max(0, (parseInt(child.unread_count, 10) || 0) - unreadOpened);
                    }
                }

                const rows = res.notes || [];
                if (!rows.length) {
                    $('#parentNotesListBody').html(
                        '<div class="pn-modal-empty"><i class="fi ' + escapeHtml(T.item_icon) + ' d-block mb-2" style="font-size:2rem;color:#940000;opacity:.5;"></i><div class="text-muted">' + escapeHtml(T.no_notes) + '</div></div>'
                    );
                    return;
                }

                let html = '<div class="pn-pkg-toolbar"><div class="pn-pkg-count"><strong>' + rows.length + '</strong> ' + escapeHtml(T.notes) + '</div></div>';
                rows.forEach(function(n) {
                    html += '<div class="pn-note-row">'
                        + '<span class="pn-note-icon"><i class="fi ' + escapeHtml(T.item_icon) + '"></i></span>'
                        + '<div class="flex-grow-1 min-w-0">'
                        + '<div class="pn-note-name">' + escapeHtml(n.title) + '</div>'
                        + '<div class="pn-note-meta">'
                        + escapeHtml(n.original_filename || '')
                        + (n.teacher_name ? ' · ' + escapeHtml(n.teacher_name) : '')
                        + '</div></div>'
                        + '<div class="pn-note-actions">'
                        + '<button type="button" class="btn btn-sm btn-view parent-note-view" data-url="' + escapeHtml(n.view_url) + '" data-dl="' + escapeHtml(n.download_url) + '" data-title="' + escapeHtml(n.title) + '"><i class="fi fi-rr-eye me-1"></i>' + escapeHtml(T.view) + '</button>'
                        + '<a class="btn btn-sm btn-primary btn-dl" href="' + escapeHtml(n.download_url) + '" title="' + escapeHtml(T.download) + '"><i class="fi fi-rr-download me-1"></i>' + escapeHtml(T.download) + '</a>'
                        + '</div></div>';
                });
                $('#parentNotesListBody').html(html);
            })
            .fail(function() {
                $('#parentNotesListBody').html(
                    '<div class="pn-modal-empty"><div class="alert alert-danger mb-0">' + escapeHtml(T.fail) + '</div></div>'
                );
            });
    });

    $(document).on('click', '.parent-note-view', function() {
        const url = $(this).data('url');
        const dl = $(this).data('dl');
        const title = $(this).data('title') || T.page;
        if (window.openParentHomePackageView) {
            window.openParentHomePackageView(url, dl, title);
        }
    });

    renderChildren();
})(jQuery);
</script>
@endpush
