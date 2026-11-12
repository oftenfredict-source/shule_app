@extends('layouts.parent_corp')

@section('title', __('parent.hp_page_title') . ' - ShuleXpert')
@section('page_title', __('parent.hp_page_title'))
@section('page_breadcrumb', __('parent.nav_home_package'))

@section('content')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp span:not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-hp .text-muted {
        color: #000 !important;
    }
    .sx-parent-hp .btn-primary,
    .sx-parent-hp a.btn-primary,
    .sx-parent-hp .badge {
        color: #fff !important;
    }
    .sx-parent-hp .card,
    .sx-parent-hp .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-hp .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-hp .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-hp .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-hp .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }
    .sx-parent-hp .holiday-card {
        display: block;
        width: 100%;
        text-align: left;
        text-decoration: none;
        color: inherit;
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        padding: 1.05rem 1.15rem;
        transition: box-shadow 0.15s ease, transform 0.15s ease, background 0.15s ease;
        cursor: pointer;
    }
    .sx-parent-hp .holiday-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
        background: #fff8f8;
        color: inherit;
        text-decoration: none;
    }
    .sx-parent-hp .holiday-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        border: 1px solid #f0d0d0;
        background: #fff5f5;
        color: #940000 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .sx-parent-hp .holiday-name {
        font-weight: 800;
        font-size: 1rem;
        color: #222 !important;
        line-height: 1.3;
        margin-bottom: 0.2rem;
    }
    .sx-parent-hp .holiday-dates {
        font-size: 0.78rem;
        color: #777 !important;
        font-weight: 600;
    }
    .sx-parent-hp .holiday-chevron {
        color: #940000 !important;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .sx-parent-hp .empty-state {
        text-align: center;
        padding: 2.75rem 1.25rem;
        background: #fff;
        border: 1px solid #e6e6e6;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-hp .empty-state i {
        font-size: 2.5rem;
        color: #940000;
        opacity: 0.55;
        display: block;
        margin-bottom: 0.75rem;
    }

    /* Modal */
    .sx-hp-modal .modal-content {
        border-radius: 0.35rem;
        border: 1px solid #e6e6e6;
        overflow: hidden;
    }
    .sx-hp-modal .modal-header {
        background: #940000;
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.25rem;
    }
    .sx-hp-modal .modal-title {
        font-weight: 800;
        font-size: 1rem;
        color: #fff !important;
    }
    .sx-hp-modal .modal-header small {
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 600;
    }
    .sx-hp-modal .modal-body {
        padding: 0;
        background: #fff;
    }
    .sx-hp-modal .modal-footer {
        border-top: 1px solid #eee;
        background: #fff;
        padding: 0.85rem 1.15rem;
    }
    .sx-hp-modal .hp-modal-loading,
    .sx-hp-modal .hp-modal-empty {
        padding: 2.5rem 1.25rem;
        text-align: center;
    }
    .sx-hp-modal .hp-pkg-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #f0e6e6;
        background: #fffaf9;
    }
    .sx-hp-modal .hp-pkg-count {
        font-size: 0.78rem;
        font-weight: 700;
        color: #666 !important;
    }
    .sx-hp-modal .hp-pkg-count strong {
        color: #940000 !important;
    }
    .sx-hp-modal .hp-pkg-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        background: #fff;
    }
    .sx-hp-modal .hp-pkg-table thead th {
        background: #fcf7f7;
        color: #940000 !important;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.05em;
        font-weight: 800;
        border-bottom: 2px solid rgba(148, 0, 0, 0.18);
        border-right: 1px solid #f0e8e8;
        white-space: nowrap;
        padding: 0.85rem 1rem;
        vertical-align: middle;
    }
    .sx-hp-modal .hp-pkg-table thead th:last-child {
        border-right: none;
    }
    .sx-hp-modal .hp-pkg-table tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f0ecec;
        border-right: 1px solid #f6f1f1;
        padding: 0.95rem 1rem;
        font-size: 0.88rem;
        background: #fff;
    }
    .sx-hp-modal .hp-pkg-table tbody td:last-child {
        border-right: none;
    }
    .sx-hp-modal .hp-pkg-table tbody tr:nth-child(even) td {
        background: #fffcfc;
    }
    .sx-hp-modal .hp-pkg-table tbody tr:hover td {
        background: #fff5f5 !important;
    }
    .sx-hp-modal .hp-pkg-table tbody tr:last-child td {
        border-bottom: none;
    }
    .sx-hp-modal .subj-cell {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        min-width: 160px;
    }
    .sx-hp-modal .subj-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #fff5f5;
        border: 1px solid #f0d0d0;
        color: #940000 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.72rem;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .sx-hp-modal .subj-name {
        font-weight: 800;
        color: #1a1a1a !important;
        line-height: 1.25;
    }
    .sx-hp-modal .subj-meta {
        font-size: 0.72rem;
        color: #888 !important;
        font-weight: 600;
        margin-top: 0.1rem;
    }
    .sx-hp-modal .file-title {
        font-weight: 700;
        color: #222 !important;
        line-height: 1.3;
    }
    .sx-hp-modal .file-name {
        font-size: 0.72rem;
        color: #888 !important;
        margin-top: 0.15rem;
        word-break: break-all;
    }
    .sx-hp-modal .teacher-name {
        font-weight: 600;
        color: #333 !important;
    }
    .sx-hp-modal .col-act {
        width: 1%;
        white-space: nowrap;
        text-align: right;
    }
    .sx-hp-modal .btn-view {
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
    .sx-hp-modal .btn-view:hover {
        border-color: #940000;
        color: #940000 !important;
        background: #fff8f8;
    }
    .sx-hp-modal .btn-dl {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-radius: 0;
        padding: 0.35rem 0.65rem;
    }

    @media (max-width: 576px) {
        .sx-hp-modal .col-act {
            white-space: normal;
        }
        .sx-hp-modal .btn-view,
        .sx-hp-modal .btn-dl {
            display: block;
            width: 100%;
            margin-bottom: 0.35rem;
        }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-parent-hp .card,
    html[data-bs-theme="dark"] .sx-parent-hp .card,
    html[data-sx-theme="dark"] .sx-parent-hp .panel-card,
    html[data-bs-theme="dark"] .sx-parent-hp .panel-card,
    html[data-sx-theme="dark"] .sx-parent-hp .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-hp .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-card,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-card,
    html[data-sx-theme="dark"] .sx-parent-hp .empty-state,
    html[data-bs-theme="dark"] .sx-parent-hp .empty-state {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-card,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-card {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-hp,
    html[data-bs-theme="dark"] .sx-parent-hp,
    html[data-sx-theme="dark"] .sx-parent-hp p,
    html[data-bs-theme="dark"] .sx-parent-hp p,
    html[data-sx-theme="dark"] .sx-parent-hp span:not(.badge),
    html[data-bs-theme="dark"] .sx-parent-hp span:not(.badge),
    html[data-sx-theme="dark"] .sx-parent-hp small,
    html[data-bs-theme="dark"] .sx-parent-hp small,
    html[data-sx-theme="dark"] .sx-parent-hp strong,
    html[data-bs-theme="dark"] .sx-parent-hp strong,
    html[data-sx-theme="dark"] .sx-parent-hp div,
    html[data-bs-theme="dark"] .sx-parent-hp div,
    html[data-sx-theme="dark"] .sx-parent-hp .text-muted,
    html[data-bs-theme="dark"] .sx-parent-hp .text-muted,
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-name,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-name,
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-dates,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-dates {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-hp .section-label,
    html[data-bs-theme="dark"] .sx-parent-hp .section-label,
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-chevron,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-chevron {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-icon,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-icon {
        background: rgba(148, 0, 0, 0.25) !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-hp .holiday-card:hover,
    html[data-bs-theme="dark"] .sx-parent-hp .holiday-card:hover {
        background: rgba(255, 180, 180, 0.08) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-hp .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-hp .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .modal-content,
    html[data-bs-theme="dark"] .sx-hp-modal .modal-content,
    html[data-sx-theme="dark"] .sx-hp-modal .modal-body,
    html[data-bs-theme="dark"] .sx-hp-modal .modal-body,
    html[data-sx-theme="dark"] .sx-hp-modal .modal-footer,
    html[data-bs-theme="dark"] .sx-hp-modal .modal-footer,
    html[data-sx-theme="dark"] .sx-hp-modal .hp-pkg-toolbar,
    html[data-bs-theme="dark"] .sx-hp-modal .hp-pkg-toolbar,
    html[data-sx-theme="dark"] .sx-hp-modal .hp-pkg-table,
    html[data-bs-theme="dark"] .sx-hp-modal .hp-pkg-table {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .hp-pkg-table thead th,
    html[data-bs-theme="dark"] .sx-hp-modal .hp-pkg-table thead th {
        background: rgba(148, 0, 0, 0.22) !important;
        color: #ffb4b4 !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .hp-pkg-table tbody td,
    html[data-bs-theme="dark"] .sx-hp-modal .hp-pkg-table tbody td {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.1) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .hp-pkg-table tbody tr:nth-child(even) td,
    html[data-bs-theme="dark"] .sx-hp-modal .hp-pkg-table tbody tr:nth-child(even) td {
        background: #2a1c21 !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .subj-name,
    html[data-bs-theme="dark"] .sx-hp-modal .subj-name,
    html[data-sx-theme="dark"] .sx-hp-modal .file-title,
    html[data-bs-theme="dark"] .sx-hp-modal .file-title,
    html[data-sx-theme="dark"] .sx-hp-modal .teacher-name,
    html[data-bs-theme="dark"] .sx-hp-modal .teacher-name,
    html[data-sx-theme="dark"] .sx-hp-modal .subj-meta,
    html[data-bs-theme="dark"] .sx-hp-modal .subj-meta,
    html[data-sx-theme="dark"] .sx-hp-modal .file-name,
    html[data-bs-theme="dark"] .sx-hp-modal .file-name,
    html[data-sx-theme="dark"] .sx-hp-modal .hp-pkg-count,
    html[data-bs-theme="dark"] .sx-hp-modal .hp-pkg-count {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .subj-icon,
    html[data-bs-theme="dark"] .sx-hp-modal .subj-icon {
        background: rgba(148, 0, 0, 0.25) !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-hp-modal .btn-view,
    html[data-bs-theme="dark"] .sx-hp-modal .btn-view {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
</style>

<div class="sx-parent-hp">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi fi-rr-box-open me-1" style="color:#940000;"></i>
                                {{ __('parent.hp_page_title') }}
                            </h5>
                            <p class="text-muted small mb-0">{{ __('parent.hp_page_hint') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="hint-strip">
                <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                {{ __('parent.hp_page_hint') }}
            </div>
        </div>

        <div class="col-12">
            <div class="section-label">
                <i class="fi fi-rr-calendar me-1"></i>
                {{ __('parent.holiday') }}
                @if(isset($holidays) && $holidays->count())
                    ({{ $holidays->count() }})
                @endif
            </div>
        </div>

        @forelse($holidays as $holiday)
            <div class="col-12 col-md-6">
                <button type="button"
                        class="holiday-card parent-hp-holiday w-100"
                        data-holiday-id="{{ $holiday->holidayID }}"
                        data-holiday-name="{{ $holiday->holiday_name }}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="holiday-icon"><i class="fi fi-rr-calendar-days"></i></span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="holiday-name text-truncate">{{ $holiday->holiday_name }}</div>
                            <div class="holiday-dates">
                                <i class="fi fi-rr-clock me-1"></i>
                                {{ optional($holiday->start_date)->format('d/m/Y') }}
                                —
                                {{ optional($holiday->end_date)->format('d/m/Y') }}
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right holiday-chevron"></i>
                    </div>
                </button>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fi fi-rr-box-open"></i>
                    <h5 class="mb-1">{{ __('parent.hp_page_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('parent.hp_empty') }}</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Holiday packages modal --}}
<div class="modal fade sx-hp-modal" id="parentHpHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="parentHpHolidayTitle">{{ __('parent.home_package') }}</h5>
                    <small id="parentHpHolidayDates"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('parent.close') }}"></button>
            </div>
            <div class="modal-body" id="parentHpHolidayBody">
                <div class="hp-modal-loading text-muted">{{ __('parent.loading') }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light btn-shadow waves-effect" data-bs-dismiss="modal">{{ __('parent.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function($) {
    const P = @json(trans('parent'));
    const holidayPackagesUrl = @json(url('parentHomePackages'));

    function esc(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initials(name) {
        var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return 'HP';
        if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    $(document).on('click', '.parent-hp-holiday', function() {
        var id = $(this).data('holiday-id');
        var name = $(this).data('holiday-name');
        $('#parentHpHolidayTitle').text(name || P.home_package);
        $('#parentHpHolidayDates').text('');
        $('#parentHpHolidayBody').html(
            '<div class="hp-modal-loading"><div class="spinner-border text-primary mb-2" role="status"></div><div class="text-muted">' + esc(P.loading) + '</div></div>'
        );
        bootstrap.Modal.getOrCreateInstance(document.getElementById('parentHpHolidayModal')).show();

        $.get(holidayPackagesUrl + '/' + id + '/packages')
            .done(function(res) {
                if (res.holiday && res.holiday.dates) {
                    $('#parentHpHolidayDates').text(res.holiday.dates);
                }
                var rows = res.packages || [];
                if (!rows.length) {
                    $('#parentHpHolidayBody').html(
                        '<div class="hp-modal-empty"><i class="fi fi-rr-box-open d-block mb-2" style="font-size:2rem;color:#940000;opacity:.5;"></i><div class="text-muted">' + esc(P.hp_none_holiday) + '</div></div>'
                    );
                    return;
                }

                var html = '<div class="hp-pkg-toolbar"><div class="hp-pkg-count"><strong>' + rows.length + '</strong> ' + esc(P.home_package) + '</div></div>';
                html += '<div class="table-responsive"><table class="hp-pkg-table"><thead><tr>';
                html += '<th>' + esc(P.subject) + '</th>';
                html += '<th>' + esc(P.topic_file) + '</th>';
                html += '<th>' + esc(P.teacher) + '</th>';
                html += '<th class="col-act">' + esc(P.actions) + '</th>';
                html += '</tr></thead><tbody>';

                rows.forEach(function(p) {
                    var title = p.title || p.original_filename || '';
                    var filename = p.original_filename || '';
                    html += '<tr>';
                    html += '<td><div class="subj-cell">';
                    html += '<span class="subj-icon">' + esc(initials(p.subject_name)) + '</span>';
                    html += '<div><div class="subj-name">' + esc(p.subject_name || '') + '</div>';
                    if (p.subject_code) {
                        html += '<div class="subj-meta">' + esc(p.subject_code) + '</div>';
                    }
                    if (p.class_label) {
                        html += '<div class="subj-meta">' + esc(p.class_label) + '</div>';
                    }
                    html += '</div></div></td>';
                    html += '<td><div class="file-title">' + esc(title) + '</div>';
                    if (filename && filename !== title) {
                        html += '<div class="file-name"><i class="fi fi-rr-file me-1"></i>' + esc(filename) + '</div>';
                    }
                    html += '</td>';
                    html += '<td><span class="teacher-name">' + esc(p.teacher_name || '—') + '</span></td>';
                    html += '<td class="col-act">';
                    html += '<button type="button" class="btn btn-sm btn-view parent-hp-view me-1" data-url="' + esc(p.view_url) + '" data-dl="' + esc(p.download_url) + '" data-title="' + esc(p.subject_name || P.home_package) + '"><i class="fi fi-rr-eye me-1"></i>' + esc(P.view) + '</button>';
                    html += '<a class="btn btn-sm btn-primary btn-dl" href="' + esc(p.download_url) + '"><i class="fi fi-rr-download me-1"></i>' + esc(P.download) + '</a>';
                    html += '</td></tr>';
                });

                html += '</tbody></table></div>';
                $('#parentHpHolidayBody').html(html);
            })
            .fail(function() {
                $('#parentHpHolidayBody').html(
                    '<div class="hp-modal-empty"><div class="alert alert-danger mb-0">' + esc(P.load_failed) + '</div></div>'
                );
            });
    });

    $(document).on('click', '.parent-hp-view', function() {
        var url = $(this).data('url');
        var dl = $(this).data('dl');
        var title = $(this).data('title') || P.home_package;
        if (window.openParentHomePackageView) {
            window.openParentHomePackageView(url, dl, title);
        }
    });
})(jQuery);
</script>
@endpush
