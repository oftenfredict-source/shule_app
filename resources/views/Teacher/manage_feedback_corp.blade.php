@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.suggestions').' - ShuleXpert')
@section('page_title', __('teacher.pages.suggestions'))
@section('page_breadcrumb', __('teacher.pages.suggestions_bc'))

@php
    $suggestionsRoute = 'teacher.suggestions';
    $incidentsRoute = 'teacher.incidents';
    $storeRoute = 'teacher.feedback.store';
@endphp

@push('styles')
<style>
    .sx-feedback {
        --maroon: #940000;
        --line: #e8e0e0;
        --soft: #faf7f7;
    }
    .sx-feedback .sx-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        overflow: hidden;
    }
    .sx-feedback .sx-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(180deg, #fff, var(--soft));
        border-bottom: 1px solid var(--line);
    }
    .sx-feedback .sx-panel-head h4 { margin: 0; color: var(--maroon); font-size: 1.05rem; font-weight: 800; }
    .sx-feedback .sx-panel-head p { margin: .2rem 0 0; color: #666; font-size: .8rem; }
    .sx-feedback .sx-panel-body { padding: 1.1rem; }
    .sx-feedback .btn, .sx-feedback .form-control, .sx-feedback .form-select,
    .sx-feedback .alert, .sx-feedback .badge, .sx-feedback .card,
    .sx-feedback .list-group-item, .sx-feedback .nav-link { border-radius: 0 !important; }
    .sx-btn-maroon {
        background: linear-gradient(180deg, #a80000, #940000 55%, #7f0000) !important;
        border: 1px solid #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
    }
    .sx-btn-maroon:hover { color: #fff !important; filter: brightness(.95); }
    .sx-feedback .form-control:focus, .sx-feedback .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 .2rem rgba(148, 0, 0, .15);
    }
    .sx-feedback .feedback-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .65rem;
        margin-bottom: 1rem;
        border: 0;
    }
    .sx-feedback .feedback-tabs .nav-item { margin: 0; }
    .sx-feedback .feedback-tabs .nav-link {
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        border: 1px solid #eadfdf !important;
        border-top: 3px solid #d2c4c4 !important;
        color: #555 !important;
        font-weight: 700;
        background: linear-gradient(180deg, #fff, #fcfbfb) !important;
        cursor: pointer;
    }
    .sx-feedback .feedback-tabs .nav-link:hover {
        border-top-color: #940000 !important;
        color: #940000 !important;
        background: #fff8f8 !important;
    }
    .sx-feedback .feedback-tabs .nav-link.active {
        color: #940000 !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        background: linear-gradient(180deg, #fff8f8, #ffeaea) !important;
        box-shadow: 0 4px 14px rgba(148,0,0,.1);
    }
    .sx-feedback .feedback-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
        color: #444;
        font-weight: 600;
        padding: .8rem 1rem;
        border-color: var(--line);
    }
    .sx-feedback .feedback-menu .list-group-item:hover { background: #fffafa; }
    .sx-feedback .feedback-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
    }
    .sx-feedback .sx-notes {
        border: 1px solid var(--line);
        border-top: 3px solid #940000;
        background: linear-gradient(180deg, #fff, var(--soft));
        padding: 1rem;
        margin-top: 1rem;
    }
    .sx-feedback .sx-notes h6 { margin: 0 0 .4rem; color: #940000; font-weight: 800; font-size: .85rem; }
    .sx-feedback .sx-notes p { margin: 0; color: #777; font-size: .8rem; line-height: 1.5; }
    .sx-feedback .section-title {
        font-weight: 800;
        color: #333;
        margin-bottom: .85rem;
        font-size: .95rem;
    }
    .sx-feedback .muted-help { color: #6c757d; font-size: .85rem; }
    .sx-feedback .feedback-body {
        max-height: 72vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .sx-feedback .feedback-loading,
    .sx-feedback .filter-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, .25);
        background: rgba(148, 0, 0, .05);
        margin-bottom: 12px;
    }
    .sx-feedback .feedback-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        overflow: hidden;
    }
    .sx-feedback .feedback-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: sxFbProgress 1.1s linear infinite;
    }
    @keyframes sxFbProgress {
        0% { left: -40%; }
        100% { left: 100%; }
    }
    .sx-ep-table-wrap { border: 1px solid var(--line); overflow-x: auto; background: #fff; }
    table.sx-ep-table { width: 100%; margin: 0; border-collapse: collapse; }
    table.sx-ep-table th {
        background: linear-gradient(180deg, #f7f2f2, #f1eaea);
        color: #333;
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .72rem .8rem;
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }
    table.sx-ep-table td {
        padding: .75rem .8rem;
        border-bottom: 1px solid #f0eaea;
        vertical-align: top;
        font-size: .88rem;
        color: #333;
    }
    table.sx-ep-table tbody tr:hover { background: #fffafa; }
    .sx-tag {
        display: inline-block;
        padding: .22rem .55rem;
        font-size: .72rem;
        font-weight: 700;
        border: 1px solid transparent;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .sx-tag-approved { background: #eef8f0; color: #1b6b2e; border-color: #c8e6cf; }
    .sx-tag-rejected { background: #fdeeee; color: #940000; border-color: #f0c8c8; }
    .sx-tag-pending { background: #fff8e8; color: #9a6b00; border-color: #f0e0b0; }
    .sx-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
        margin-bottom: 1rem;
    }
    .sx-stats .sx-stat {
        border: 1px solid var(--line);
        background: var(--soft);
        padding: .7rem .8rem;
    }
    .sx-stats .sx-stat strong { display: block; color: #940000; font-size: 1.1rem; font-weight: 800; }
    .sx-stats .sx-stat span { color: #777; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
    @media (max-width: 767.98px) {
        .sx-stats { grid-template-columns: 1fr 1fr; }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-feedback,
    html[data-bs-theme="dark"] .sx-feedback {
        --line: #3a2a2e;
        --soft: #2a1c20;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-panel,
    html[data-bs-theme="dark"] .sx-feedback .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-panel-head,
    html[data-bs-theme="dark"] .sx-feedback .sx-panel-head {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-panel-head h4,
    html[data-bs-theme="dark"] .sx-feedback .sx-panel-head h4 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-panel-head p,
    html[data-bs-theme="dark"] .sx-feedback .sx-panel-head p {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-tabs .nav-link,
    html[data-bs-theme="dark"] .sx-feedback .feedback-tabs .nav-link {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        border-top-color: #4a383c !important;
        color: #e8c9cd !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-tabs .nav-link:hover,
    html[data-bs-theme="dark"] .sx-feedback .feedback-tabs .nav-link:hover,
    html[data-sx-theme="dark"] .sx-feedback .feedback-tabs .nav-link.active,
    html[data-bs-theme="dark"] .sx-feedback .feedback-tabs .nav-link.active {
        background: #2a1c20 !important;
        border-color: #940000 !important;
        border-top-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-menu .list-group-item,
    html[data-bs-theme="dark"] .sx-feedback .feedback-menu .list-group-item {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-menu .list-group-item:hover,
    html[data-bs-theme="dark"] .sx-feedback .feedback-menu .list-group-item:hover {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-menu .list-group-item.active,
    html[data-bs-theme="dark"] .sx-feedback .feedback-menu .list-group-item.active {
        background: #2a1c20 !important;
        border-left-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .form-label,
    html[data-bs-theme="dark"] .sx-feedback .form-label,
    html[data-sx-theme="dark"] .sx-feedback label,
    html[data-bs-theme="dark"] .sx-feedback label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .form-control,
    html[data-bs-theme="dark"] .sx-feedback .form-control,
    html[data-sx-theme="dark"] .sx-feedback .form-select,
    html[data-bs-theme="dark"] .sx-feedback .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .form-select option,
    html[data-bs-theme="dark"] .sx-feedback .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-notes,
    html[data-bs-theme="dark"] .sx-feedback .sx-notes {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-notes h6,
    html[data-bs-theme="dark"] .sx-feedback .sx-notes h6 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .sx-notes p,
    html[data-bs-theme="dark"] .sx-feedback .sx-notes p {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .section-title,
    html[data-bs-theme="dark"] .sx-feedback .section-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .muted-help,
    html[data-bs-theme="dark"] .sx-feedback .muted-help,
    html[data-sx-theme="dark"] .sx-feedback .text-muted,
    html[data-bs-theme="dark"] .sx-feedback .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-loading,
    html[data-bs-theme="dark"] .sx-feedback .feedback-loading,
    html[data-sx-theme="dark"] .sx-feedback .filter-loading,
    html[data-bs-theme="dark"] .sx-feedback .filter-loading {
        background: rgba(148, 0, 0, 0.18) !important;
        border-color: #4a383c !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .feedback-progress,
    html[data-bs-theme="dark"] .sx-feedback .feedback-progress {
        background: #2a2226 !important;
    }
    html[data-sx-theme="dark"] .sx-ep-table-wrap,
    html[data-bs-theme="dark"] .sx-ep-table-wrap {
        background: #24181c !important;
        border-color: #3a2a2e !important;
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
    html[data-sx-theme="dark"] table.sx-ep-table tbody tr:hover,
    html[data-bs-theme="dark"] table.sx-ep-table tbody tr:hover,
    html[data-sx-theme="dark"] table.sx-ep-table tbody tr:hover td,
    html[data-bs-theme="dark"] table.sx-ep-table tbody tr:hover td {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-stats .sx-stat,
    html[data-bs-theme="dark"] .sx-stats .sx-stat {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-stats .sx-stat strong,
    html[data-bs-theme="dark"] .sx-stats .sx-stat strong {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-stats .sx-stat span,
    html[data-bs-theme="dark"] .sx-stats .sx-stat span {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-feedback .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-feedback .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
        background: transparent !important;
    }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="sx-feedback">
    <div class="sx-panel">
        <div class="sx-panel-head">
            <div>
                <h4><i class="fa fa-comments me-1"></i> {{ __('teacher.feedback_head') }}</h4>
                <p>{{ __('teacher.feedback_sub') }}</p>
            </div>
        </div>
        <div class="sx-panel-body feedback-body">
            @if(session('success'))
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="feedback-loading" id="suggestionLoading">
                <span><i class="fa fa-spinner fa-spin" style="color:#940000"></i> {{ __('teacher.loading') }}</span>
                <div class="feedback-progress"></div>
            </div>
            <div class="feedback-loading" id="incidentLoading">
                <span><i class="fa fa-spinner fa-spin" style="color:#940000"></i> {{ __('teacher.loading') }}</span>
                <div class="feedback-progress"></div>
            </div>
            <div class="filter-loading" id="teacherFilterLoading">
                <span><i class="fa fa-spinner fa-spin" style="color:#940000"></i> {{ __('teacher.loading') }}</span>
                <div class="feedback-progress"></div>
            </div>

            <ul class="nav feedback-tabs" id="feedbackTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-tab="suggestions"><i class="fa fa-lightbulb-o"></i> {{ __('teacher.suggestions_tab') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-tab="incidents"><i class="fa fa-exclamation-circle"></i> {{ __('teacher.incidents_tab') }}</a>
                </li>
            </ul>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="list-group feedback-menu">
                        <a class="list-group-item active" data-section="send">
                            <i class="fa fa-paper-plane me-1"></i> {{ __('teacher.fb.send') }}
                        </a>
                        <a class="list-group-item" data-section="view">
                            <i class="fa fa-list me-1"></i> {{ __('teacher.fb.view') }}
                        </a>
                        <a class="list-group-item" data-section="report">
                            <i class="fa fa-bar-chart me-1"></i> {{ __('teacher.fb.report') }}
                        </a>
                    </div>
                    <div class="sx-notes">
                        <h6>{{ __('teacher.fb.quick_notes') }}</h6>
                        <p>{{ __('teacher.fb.messages_admin') }}<br>{{ __('teacher.fb.status_updates') }}</p>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="feedback-section" data-section="send" data-tab="suggestions">
                        <div class="section-title">{{ __('teacher.send_suggestion') }}</div>
                        <form method="POST" action="{{ route($storeRoute) }}" id="suggestionForm">
                            @csrf
                            <input type="hidden" name="type" value="suggestion">
                            <div class="mb-3">
                                <label for="suggestion_message" class="form-label">{{ __('teacher.message') }}</label>
                                <textarea class="form-control" id="suggestion_message" name="message" rows="4" placeholder="{{ __('teacher.fb.suggestion_ph') }}" required></textarea>
                            </div>
                            <button class="btn sx-btn-maroon" type="submit">
                                <i class="fa fa-paper-plane"></i> {{ __('teacher.send_suggestion') }}
                            </button>
                        </form>
                    </div>

                    <div class="feedback-section d-none" data-section="send" data-tab="incidents">
                        <div class="section-title">{{ __('teacher.send_incident') }}</div>
                        <form method="POST" action="{{ route($storeRoute) }}" id="incidentForm">
                            @csrf
                            <input type="hidden" name="type" value="incident">
                            <div class="mb-3">
                                <label for="incident_message" class="form-label">{{ __('teacher.message') }}</label>
                                <textarea class="form-control" id="incident_message" name="message" rows="4" placeholder="{{ __('teacher.fb.incident_ph') }}" required></textarea>
                            </div>
                            <button class="btn sx-btn-maroon" type="submit">
                                <i class="fa fa-paper-plane"></i> {{ __('teacher.send_incident') }}
                            </button>
                        </form>
                    </div>

                    <div class="feedback-section d-none" data-section="view" data-tab="suggestions">
                        <div class="section-title">{{ __('teacher.my_suggestions') }}</div>
                        <form method="GET" action="{{ route($suggestionsRoute) }}" class="mb-3 js-filter-form" data-tab="suggestions" data-section="view">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label for="suggestion_from" class="form-label">{{ __('teacher.from') }}</label>
                                    <input type="date" class="form-control" id="suggestion_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4">
                                    <label for="suggestion_to" class="form-label">{{ __('teacher.to') }}</label>
                                    <input type="date" class="form-control" id="suggestion_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 d-flex align-items-end">
                                    <button class="btn sx-btn-maroon w-100" type="submit">
                                        <i class="fa fa-filter"></i> {{ __('teacher.filter') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if(isset($suggestions) && $suggestions->count() > 0)
                            <div class="sx-ep-table-wrap">
                                <table class="sx-ep-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('teacher.date') }}</th>
                                            <th>{{ __('teacher.message') }}</th>
                                            <th>{{ __('teacher.status') }}</th>
                                            <th>{{ __('teacher.response') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suggestions as $suggestion)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($suggestion->created_at)->format('Y-m-d') }}</td>
                                                <td>{{ $suggestion->message }}</td>
                                                <td>
                                                    @if($suggestion->status === 'approved')
                                                        <span class="sx-tag sx-tag-approved">{{ __('teacher.approved') }}</span>
                                                    @elseif($suggestion->status === 'rejected')
                                                        <span class="sx-tag sx-tag-rejected">{{ __('teacher.rejected') }}</span>
                                                    @else
                                                        <span class="sx-tag sx-tag-pending">{{ __('teacher.pending') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($suggestion->admin_response)
                                                        {{ $suggestion->admin_response }}
                                                        @if($suggestion->response_due_date)
                                                            <br><small class="muted-help">{{ __('teacher.fb.planned') }}: {{ $suggestion->response_due_date }}</small>
                                                        @endif
                                                    @else
                                                        <span class="muted-help">{{ __('teacher.pending') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">{{ __('teacher.fb.no_suggestions') }}</div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="view" data-tab="incidents">
                        <div class="section-title">{{ __('teacher.my_incidents') }}</div>
                        <form method="GET" action="{{ route($incidentsRoute) }}" class="mb-3 js-filter-form" data-tab="incidents" data-section="view">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label for="incident_from" class="form-label">{{ __('teacher.from') }}</label>
                                    <input type="date" class="form-control" id="incident_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4">
                                    <label for="incident_to" class="form-label">{{ __('teacher.to') }}</label>
                                    <input type="date" class="form-control" id="incident_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 d-flex align-items-end">
                                    <button class="btn sx-btn-maroon w-100" type="submit">
                                        <i class="fa fa-filter"></i> {{ __('teacher.filter') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if(isset($incidents) && $incidents->count() > 0)
                            <div class="sx-ep-table-wrap">
                                <table class="sx-ep-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('teacher.date') }}</th>
                                            <th>{{ __('teacher.message') }}</th>
                                            <th>{{ __('teacher.status') }}</th>
                                            <th>{{ __('teacher.response') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incidents as $incident)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($incident->created_at)->format('Y-m-d') }}</td>
                                                <td>{{ $incident->message }}</td>
                                                <td>
                                                    @if($incident->status === 'approved')
                                                        <span class="sx-tag sx-tag-approved">{{ __('teacher.approved') }}</span>
                                                    @elseif($incident->status === 'rejected')
                                                        <span class="sx-tag sx-tag-rejected">{{ __('teacher.rejected') }}</span>
                                                    @else
                                                        <span class="sx-tag sx-tag-pending">{{ __('teacher.pending') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($incident->admin_response)
                                                        {{ $incident->admin_response }}
                                                        @if($incident->response_due_date)
                                                            <br><small class="muted-help">{{ __('teacher.fb.planned') }}: {{ $incident->response_due_date }}</small>
                                                        @endif
                                                    @else
                                                        <span class="muted-help">{{ __('teacher.pending') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">{{ __('teacher.fb.no_incidents') }}</div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="report" data-tab="suggestions">
                        <div class="section-title">{{ __('teacher.suggestion_reports') }}</div>
                        <form method="GET" action="{{ route($suggestionsRoute) }}" class="mb-3 js-filter-form" data-tab="suggestions" data-section="report">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label for="report_suggestion_from" class="form-label">{{ __('teacher.from') }}</label>
                                    <input type="date" class="form-control" id="report_suggestion_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4">
                                    <label for="report_suggestion_to" class="form-label">{{ __('teacher.to') }}</label>
                                    <input type="date" class="form-control" id="report_suggestion_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 d-flex align-items-end">
                                    <button class="btn sx-btn-maroon w-100" type="submit">
                                        <i class="fa fa-filter"></i> {{ __('teacher.filter') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="sx-stats">
                            <div class="sx-stat"><strong>{{ $suggestionStats['total'] ?? 0 }}</strong><span>{{ __('teacher.total') }}</span></div>
                            <div class="sx-stat"><strong>{{ $suggestionStats['pending'] ?? 0 }}</strong><span>{{ __('teacher.pending') }}</span></div>
                            <div class="sx-stat"><strong>{{ $suggestionStats['approved'] ?? 0 }}</strong><span>{{ __('teacher.approved') }}</span></div>
                            <div class="sx-stat"><strong>{{ $suggestionStats['rejected'] ?? 0 }}</strong><span>{{ __('teacher.rejected') }}</span></div>
                        </div>
                        @if(isset($suggestions) && $suggestions->count() > 0)
                            <div class="sx-ep-table-wrap">
                                <table class="sx-ep-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('teacher.date') }}</th>
                                            <th>{{ __('teacher.status') }}</th>
                                            <th>{{ __('teacher.message') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suggestions as $suggestion)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($suggestion->created_at)->format('Y-m-d') }}</td>
                                                <td>
                                                    @if($suggestion->status === 'approved')
                                                        <span class="sx-tag sx-tag-approved">{{ __('teacher.approved') }}</span>
                                                    @elseif($suggestion->status === 'rejected')
                                                        <span class="sx-tag sx-tag-rejected">{{ __('teacher.rejected') }}</span>
                                                    @else
                                                        <span class="sx-tag sx-tag-pending">{{ __('teacher.pending') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $suggestion->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="report" data-tab="incidents">
                        <div class="section-title">{{ __('teacher.incident_reports') }}</div>
                        <form method="GET" action="{{ route($incidentsRoute) }}" class="mb-3 js-filter-form" data-tab="incidents" data-section="report">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label for="report_incident_from" class="form-label">{{ __('teacher.from') }}</label>
                                    <input type="date" class="form-control" id="report_incident_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4">
                                    <label for="report_incident_to" class="form-label">{{ __('teacher.to') }}</label>
                                    <input type="date" class="form-control" id="report_incident_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 d-flex align-items-end">
                                    <button class="btn sx-btn-maroon w-100" type="submit">
                                        <i class="fa fa-filter"></i> {{ __('teacher.filter') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="sx-stats">
                            <div class="sx-stat"><strong>{{ $incidentStats['total'] ?? 0 }}</strong><span>{{ __('teacher.total') }}</span></div>
                            <div class="sx-stat"><strong>{{ $incidentStats['pending'] ?? 0 }}</strong><span>{{ __('teacher.pending') }}</span></div>
                            <div class="sx-stat"><strong>{{ $incidentStats['approved'] ?? 0 }}</strong><span>{{ __('teacher.approved') }}</span></div>
                            <div class="sx-stat"><strong>{{ $incidentStats['rejected'] ?? 0 }}</strong><span>{{ __('teacher.rejected') }}</span></div>
                        </div>
                        @if(isset($incidents) && $incidents->count() > 0)
                            <div class="sx-ep-table-wrap">
                                <table class="sx-ep-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('teacher.date') }}</th>
                                            <th>{{ __('teacher.status') }}</th>
                                            <th>{{ __('teacher.message') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incidents as $incident)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($incident->created_at)->format('Y-m-d') }}</td>
                                                <td>
                                                    @if($incident->status === 'approved')
                                                        <span class="sx-tag sx-tag-approved">{{ __('teacher.approved') }}</span>
                                                    @elseif($incident->status === 'rejected')
                                                        <span class="sx-tag sx-tag-rejected">{{ __('teacher.rejected') }}</span>
                                                    @else
                                                        <span class="sx-tag sx-tag-pending">{{ __('teacher.pending') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $incident->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- After #sx-teacher-dark-fix: keep status tags readable; force maroon thead --}}
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback .form-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback .form-label,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback .section-title,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback .section-title,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback .sx-panel-head h4,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback .sx-panel-head h4 {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback .sx-panel-head p,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback .sx-panel-head p,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback .muted-help,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback .muted-help,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-feedback .sx-notes p,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-feedback .sx-notes p {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th,
html[data-bs-theme="dark"] .sx-sa-content-wrap table.sx-ep-table th {
    background: #940000 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap table.sx-ep-table td,
html[data-bs-theme="dark"] .sx-sa-content-wrap table.sx-ep-table td {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-tag.sx-tag-approved,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-tag.sx-tag-approved {
    background: #eef8f0 !important;
    color: #1b6b2e !important;
    -webkit-text-fill-color: #1b6b2e !important;
    border-color: #c8e6cf !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-tag.sx-tag-rejected,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-tag.sx-tag-rejected {
    background: #fdeeee !important;
    color: #940000 !important;
    -webkit-text-fill-color: #940000 !important;
    border-color: #f0c8c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-tag.sx-tag-pending,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-tag.sx-tag-pending {
    background: #fff8e8 !important;
    color: #9a6b00 !important;
    -webkit-text-fill-color: #9a6b00 !important;
    border-color: #f0e0b0 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-stats .sx-stat span,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-stats .sx-stat span {
    color: #d7c4c8 !important;
}
</style>
@endsection

@push('scripts')
<script>
    (function() {
        const i18nFb = @json(__('teacher.fb'));

        const suggestionLoading = document.getElementById('suggestionLoading');
        const incidentLoading = document.getElementById('incidentLoading');

        function showAlert(status, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: status,
                    title: status === 'success' ? i18nFb.sent : i18nFb.failed,
                    text: message
                });
            } else {
                alert(message);
            }
        }

        const feedbackFormConfig = {
            suggestionForm: {
                loading: suggestionLoading,
                successMessage: i18nFb.sent_ok
            },
            incidentForm: {
                loading: incidentLoading,
                successMessage: i18nFb.sent_ok
            }
        };

        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !feedbackFormConfig[form.id]) {
                return;
            }
            e.preventDefault();
            const config = feedbackFormConfig[form.id];
            if (config.loading) {
                config.loading.style.display = 'flex';
            }
            const formData = new FormData(form);
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : ''
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    form.reset();
                    showAlert('success', data.message || config.successMessage);
                } else {
                    showAlert('error', (data && data.message) ? data.message : i18nFb.fail_send);
                }
            })
            .catch(() => {
                showAlert('error', i18nFb.fail_send);
            })
            .finally(() => {
                if (config.loading) {
                    config.loading.style.display = 'none';
                }
            });
        }, true);

        const teacherFilterLoading = document.getElementById('teacherFilterLoading');
        const filterForms = document.querySelectorAll('.js-filter-form');
        if (filterForms.length) {
            filterForms.forEach(form => {
                form.addEventListener('submit', () => {
                    const tabValue = form.getAttribute('data-tab') || activeTab;
                    const sectionValue = form.getAttribute('data-section') || activeSection;
                    if (tabValue) {
                        let tabInput = form.querySelector('input[name="tab"]');
                        if (!tabInput) {
                            tabInput = document.createElement('input');
                            tabInput.type = 'hidden';
                            tabInput.name = 'tab';
                            form.appendChild(tabInput);
                        }
                        tabInput.value = tabValue;
                    }
                    if (sectionValue) {
                        let sectionInput = form.querySelector('input[name="section"]');
                        if (!sectionInput) {
                            sectionInput = document.createElement('input');
                            sectionInput.type = 'hidden';
                            sectionInput.name = 'section';
                            form.appendChild(sectionInput);
                        }
                        sectionInput.value = sectionValue;
                    }
                    if (teacherFilterLoading) {
                        teacherFilterLoading.style.display = 'flex';
                    }
                });
            });
        }

        const tabs = document.querySelectorAll('.feedback-tabs .nav-link');
        const menuItems = document.querySelectorAll('.feedback-menu .list-group-item');
        const sections = document.querySelectorAll('.feedback-section');

        let activeTab = "{{ $activeTab ?? 'suggestions' }}";
        let activeSection = "{{ $activeSection ?? request('section', 'send') }}";

        function showSections() {
            sections.forEach(section => {
                const sectionName = section.getAttribute('data-section');
                const tabName = section.getAttribute('data-tab');
                if (sectionName === activeSection && tabName === activeTab) {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
        }

        function setActiveTab(tabName) {
            activeTab = tabName;
            tabs.forEach(tab => tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName));
            showSections();
        }

        function setActiveSection(sectionName) {
            activeSection = sectionName;
            menuItems.forEach(item => item.classList.toggle('active', item.getAttribute('data-section') === sectionName));
            showSections();
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                setActiveTab(this.getAttribute('data-tab'));
            });
        });

        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                setActiveSection(this.getAttribute('data-section'));
            });
        });

        setActiveTab(activeTab);
        setActiveSection(activeSection);
    })();
</script>
@endpush
