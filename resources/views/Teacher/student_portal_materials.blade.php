@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.learning_portal').' - ShuleXpert')
@section('page_title', __('teacher.pages.learning_portal'))
@section('page_breadcrumb', __('teacher.pages.learning_portal_bc'))

@section('page_actions')
    @if(!empty($enabled))
        <a href="{{ route('student_portal.login') }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm waves-effect">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('teacher.portal.open_student_view') }}
        </a>
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/learning-portal-hub.css') }}?v=6">
<style>
    /* Match GXON teacher cards (duty book / attendance / black book) */
    .sx-teacher-lph.lph {
        --lph-display: inherit;
        --lph-ui: inherit;
        --lph-surface: #fff;
        --lph-elevated: #fcf7f7;
        --lph-chip: #fcf7f7;
        --lph-line: #e6e6e6;
        font-family: inherit;
        padding-bottom: 1.25rem;
    }

    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph span:not(.lph-badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-item-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-item-meta,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-stat .n,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-stat .l,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-table td {
        color: #000 !important;
    }
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-table th,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-item-meta a,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-stat .n {
        color: #940000 !important;
    }
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-lph .lph-tabs button:not(.active) {
        color: #333 !important;
    }

    .sx-teacher-lph .lph-intro,
    .sx-teacher-lph .lph-panel,
    .sx-teacher-lph .lph-stat,
    .sx-teacher-lph .lph-tabs,
    .sx-teacher-lph .lph-item,
    .sx-teacher-lph .lph-q,
    .sx-teacher-lph .lph-section,
    .sx-teacher-lph .lph-empty,
    .sx-teacher-lph .lph-quiz-add-row,
    .sx-teacher-lph .lph-result-card {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    }

    .sx-teacher-lph .lph-intro,
    .sx-teacher-lph .lph-panel,
    .sx-teacher-lph .lph-stat {
        border-top: 4px solid #940000 !important;
        border-left: 1px solid #e6e6e6 !important;
    }

    .sx-teacher-lph .lph-stat::before { display: none !important; }

    .sx-teacher-lph .lph-intro {
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .sx-teacher-lph .lph-stats { gap: 0.85rem; margin-bottom: 1rem; }
    .sx-teacher-lph .lph-stat {
        padding: 1rem 1.1rem;
        overflow: visible;
    }
    .sx-teacher-lph .lph-stat .n {
        font-size: 1.45rem !important;
        font-weight: 700 !important;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .sx-teacher-lph .lph-stat .l {
        margin-top: 0.4rem;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
    }

    .sx-teacher-lph .lph-tabs {
        gap: 0.35rem;
        padding: 0.55rem;
        margin-bottom: 1rem;
        background: #fff !important;
        border-top: 1px solid #e6e6e6 !important;
    }
    .sx-teacher-lph .lph-tabs button {
        border-radius: 0 !important;
        border: 1px solid #e6e6e6;
        background: #fff;
        font-weight: 800 !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.03em;
        padding: 0.55rem 0.85rem;
    }
    .sx-teacher-lph .lph-tabs button.active {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }

    .sx-teacher-lph .lph-panel {
        padding: 1.15rem 1.25rem !important;
    }
    .sx-teacher-lph .lph-panel-head {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .sx-teacher-lph .lph-panel-head h2 {
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #940000 !important;
        margin: 0 0 0.25rem !important;
    }
    .sx-teacher-lph .lph-panel-head p {
        font-size: 0.82rem !important;
        font-weight: 600;
        color: #666 !important;
    }

    .sx-teacher-lph .lph-item,
    .sx-teacher-lph .lph-q,
    .sx-teacher-lph .lph-section,
    .sx-teacher-lph .lph-quiz-add-row {
        border-top: 3px solid #940000 !important;
        padding: 0.9rem 1rem !important;
    }

    .sx-teacher-lph .lph-btn,
    .sx-teacher-lph .lph-btn-brand,
    .sx-teacher-lph .lph-btn-soft,
    .sx-teacher-lph .lph-btn-danger,
    .sx-teacher-lph .lph-btn-light {
        border-radius: 0 !important;
        font-weight: 800 !important;
    }
    .sx-teacher-lph .lph-btn-brand {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-teacher-lph .lph-btn-brand:hover { background: #7b0000 !important; color: #fff !important; }
    /* White action buttons (Export / Review) — high contrast */
    .sx-teacher-lph .lph-btn-light {
        background: #fff !important;
        color: #940000 !important;
        border: 1px solid #940000 !important;
        width: auto;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    .sx-teacher-lph .lph-btn-light:hover {
        background: #fcf7f7 !important;
        color: #6e0000 !important;
        border-color: #6e0000 !important;
    }
    .sx-teacher-lph .lph-btn-light i {
        color: #940000 !important;
    }
    .sx-teacher-lph .form-control,
    .sx-teacher-lph select.form-control,
    .sx-teacher-lph textarea.form-control,
    .sx-teacher-lph .form-select {
        border-radius: 0 !important;
        min-height: 42px;
        font-weight: 600;
        box-shadow: none !important;
    }
    .sx-teacher-lph .form-control:focus,
    .sx-teacher-lph .form-select:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }

    .sx-teacher-lph .lph-table th {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #940000 !important;
        background: #fcf7f7 !important;
        border-bottom: 1px solid rgba(148, 0, 0, 0.12) !important;
        white-space: nowrap;
    }

    /* ========== Dark mode ========== */
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-stat,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-stat,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-tabs,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-tabs,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-item,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-item,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-q,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-q,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-section,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-section,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-empty,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-empty,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-quiz-add-row,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-quiz-add-row,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-result-card,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-result-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-intro,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-panel,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-stat,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-stat,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-item,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-item,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-q,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-q,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-section,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-section,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-quiz-add-row,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-quiz-add-row {
        border-top-color: #940000 !important;
    }

    /* Tabs: Notes / Files / Quizzes / Results */
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-tabs button,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-tabs button {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-tabs button i,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-tabs button i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-tabs button.active,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-tabs button.active {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-tabs button.active i,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-tabs button.active i {
        color: #fff !important;
    }

    /* Question type + form controls */
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-label,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-label,
    html[data-sx-theme="dark"] .sx-teacher-lph label,
    html[data-bs-theme="dark"] .sx-teacher-lph label,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-panel-head p,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-panel-head p,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-stat .l,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-stat .l {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-panel-head h2,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-panel-head h2,
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-stat .n,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-stat .n {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .form-control,
    html[data-bs-theme="dark"] .sx-teacher-lph .form-control,
    html[data-sx-theme="dark"] .sx-teacher-lph select.form-control,
    html[data-bs-theme="dark"] .sx-teacher-lph select.form-control,
    html[data-sx-theme="dark"] .sx-teacher-lph .form-select,
    html[data-bs-theme="dark"] .sx-teacher-lph .form-select,
    html[data-sx-theme="dark"] .sx-teacher-lph #sectionTypeSelect,
    html[data-bs-theme="dark"] .sx-teacher-lph #sectionTypeSelect {
        background: #1c1417 !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph #sectionTypeSelect option,
    html[data-bs-theme="dark"] .sx-teacher-lph #sectionTypeSelect option,
    html[data-sx-theme="dark"] .sx-teacher-lph select.form-control option,
    html[data-bs-theme="dark"] .sx-teacher-lph select.form-control option {
        background: #1c1417;
        color: #f3e9eb;
    }

    /* Soft / light action buttons (Add question, Export, Review) */
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-soft,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-soft {
        background: #1c1417 !important;
        border: 1px solid rgba(255, 180, 180, 0.35) !important;
        color: #ffe0e0 !important;
        -webkit-text-fill-color: #ffe0e0 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-soft i,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-soft i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-light,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-light,
    html[data-sx-theme="dark"] .sx-teacher-lph a.lph-btn-light,
    html[data-bs-theme="dark"] .sx-teacher-lph a.lph-btn-light {
        background: #fff8f8 !important;
        color: #7a0000 !important;
        border: 1px solid #ffb4b4 !important;
        -webkit-text-fill-color: #7a0000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-light:hover,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-light:hover,
    html[data-sx-theme="dark"] .sx-teacher-lph a.lph-btn-light:hover,
    html[data-bs-theme="dark"] .sx-teacher-lph a.lph-btn-light:hover {
        background: #ffe0e0 !important;
        color: #5c0000 !important;
        -webkit-text-fill-color: #5c0000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-light i,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-light i,
    html[data-sx-theme="dark"] .sx-teacher-lph a.lph-btn-light i,
    html[data-bs-theme="dark"] .sx-teacher-lph a.lph-btn-light i {
        color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-brand,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-brand {
        background: #940000 !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-btn-brand i,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-btn-brand i {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-table th,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-table th {
        background: #1c1417 !important;
        color: #ffb4b4 !important;
        border-bottom-color: rgba(255, 180, 180, 0.16) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-lph .lph-table td,
    html[data-bs-theme="dark"] .sx-teacher-lph .lph-table td {
        color: #f3e9eb !important;
        border-color: rgba(255, 180, 180, 0.12) !important;
    }
</style>
@endpush

@section('content')
@php $activeTab = session('portal_tab', request('tab', !empty($editingQuiz) ? 'quizzes' : 'notes')); @endphp

<div class="sx-teacher-lph lph">
    <div class="lph-intro">
        <p class="mb-0">{{ __('teacher.portal.intro') }}</p>
    </div>

    @if(session('success'))<div class="lph-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="lph-flash err">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="lph-flash err">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if(empty($enabled))
        <div class="lph-flash warn">
            {!! __('teacher.portal.disabled_alert', ['strong' => '<strong>'.e(__('teacher.portal.not_enabled')).'</strong>']) !!}
        </div>
    @else

    <div class="lph-stats">
        <div class="lph-stat"><div class="n">{{ $stats['notes'] }}</div><div class="l">{{ __('teacher.portal.stat_notes') }}</div></div>
        <div class="lph-stat"><div class="n">{{ $stats['books'] }}</div><div class="l">{{ __('teacher.portal.stat_files') }}</div></div>
        <div class="lph-stat"><div class="n">{{ $stats['quizzes'] }}</div><div class="l">{{ __('teacher.portal.stat_quizzes') }}</div></div>
        <div class="lph-stat"><div class="n">{{ $stats['attempts'] }}</div><div class="l">{{ __('teacher.portal.stat_attempts') }}</div></div>
    </div>

    <div class="lph-tabs" role="tablist">
        <button type="button" class="{{ $activeTab === 'notes' ? 'active' : '' }}" data-lph-tab="notes"><i class="fa-regular fa-file-lines"></i> {{ __('teacher.portal.tab_notes') }}</button>
        <button type="button" class="{{ $activeTab === 'files' ? 'active' : '' }}" data-lph-tab="files"><i class="fa-solid fa-cloud-arrow-up"></i> {{ __('teacher.portal.tab_files') }}</button>
        <button type="button" class="{{ $activeTab === 'quizzes' ? 'active' : '' }}" data-lph-tab="quizzes"><i class="fa-regular fa-pen-to-square"></i> {{ __('teacher.portal.tab_quizzes') }}</button>
        <button type="button" class="{{ $activeTab === 'results' ? 'active' : '' }}" data-lph-tab="results"><i class="fa-solid fa-chart-column"></i> {{ __('teacher.portal.tab_results') }}</button>
    </div>

    <div class="lph-tab-content">
        {{-- NOTES --}}
        <div class="lph-pane {{ $activeTab === 'notes' ? 'active' : '' }}" id="tab-notes" data-lph-pane="notes">
            <div class="lph-grid">
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>{{ __('teacher.portal.new_note') }}</h2>
                        <p>{{ __('teacher.portal.new_note_sub') }}</p>
                    </div>
                    <form method="POST" action="{{ route('student_portal.materials.notes.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('Teacher.partials.portal_class_fields', ['subjectClassMap' => $subjectClassMap ?? []])
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.title_required') }}</label>
                            <input class="form-control" name="title" placeholder="{{ __('teacher.portal.note_title_ph') }}" required maxlength="180">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.body') }}</label>
                            <textarea class="form-control" name="body" rows="5" placeholder="{{ __('teacher.portal.body_ph') }}"></textarea>
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.link') }}</label>
                            <input class="form-control" name="external_url" type="url" placeholder="{{ __('teacher.portal.link_ph') }}" value="{{ old('external_url') }}">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.attachment') }}</label>
                            <input class="form-control" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.mp4,.mp3">
                        </div>
                        <p class="file-hint">{{ __('teacher.portal.note_hint', ['limit' => $uploadLimit ?? '256M']) }}</p>
                        <button class="lph-btn lph-btn-brand" type="submit"><i class="fa-solid fa-floppy-disk"></i> {{ __('teacher.portal.save_note') }}</button>
                    </form>
                </div>
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>{{ __('teacher.portal.published_notes') }}</h2>
                        <p>{{ $notes->count() === 1 ? __('teacher.portal.item_in_library', ['count' => $notes->count()]) : __('teacher.portal.items_in_library', ['count' => $notes->count()]) }}</p>
                    </div>
                    <div class="lph-list">
                    @forelse($notes as $note)
                        <div class="lph-item">
                            <div>
                                <div class="lph-item-title">{{ $note->title }}</div>
                                <div class="lph-item-meta">
                                    {{ optional($classes->firstWhere('classID', $note->classID))->class_name ?? '-' }}
                                    @if($note->subclassID) · {{ optional($subclasses->firstWhere('subclassID', $note->subclassID))->subclass_name }}@endif
                                    @if($note->subjectID) · {{ optional($subjects->firstWhere('subjectID', $note->subjectID))->subject_name }}@endif
                                    @if($note->original_name)<br><i class="fa-solid fa-paperclip"></i> {{ $note->original_name }}@endif
                                    @if(!empty($note->external_url))
                                        <br><a href="{{ $note->external_url }}" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('teacher.portal.open_link') }}</a>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $note->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $note->is_published ? __('teacher.portal.published') : __('teacher.portal.hidden') }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <form method="POST" action="{{ route('student_portal.materials.notes.toggle', $note->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $note->is_published ? __('teacher.portal.hide') : __('teacher.portal.publish') }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.notes.delete', $note->id) }}" onsubmit="return confirm(@json(__('teacher.portal.confirm_delete_note')));">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">{{ __('teacher.portal.delete') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">{{ __('teacher.portal.empty_notes') }}</div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- FILES --}}
        <div class="lph-pane {{ $activeTab === 'files' ? 'active' : '' }}" id="tab-files" data-lph-pane="files">
            <div class="lph-grid">
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>{{ __('teacher.portal.upload_material') }}</h2>
                        <p>{{ __('teacher.portal.upload_material_sub') }}</p>
                    </div>
                    <form method="POST" action="{{ route('student_portal.materials.books.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('Teacher.partials.portal_class_fields', ['subjectClassMap' => $subjectClassMap ?? []])
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.title_required') }}</label>
                            <input class="form-control" name="title" placeholder="{{ __('teacher.portal.material_title_ph') }}" required maxlength="180">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.author') }}</label>
                            <input class="form-control" name="author" placeholder="{{ __('teacher.portal.optional') }}">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.description') }}</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="{{ __('teacher.portal.description_ph') }}"></textarea>
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.link') }}</label>
                            <input class="form-control" name="external_url" type="url" placeholder="{{ __('teacher.portal.link_ph') }}" value="{{ old('external_url') }}">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">{{ __('teacher.portal.file') }}</label>
                            <input class="form-control" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.mp4,.mp3,.epub">
                        </div>
                        <p class="file-hint">{{ __('teacher.portal.material_hint', ['limit' => $uploadLimit ?? '256M']) }}</p>
                        <button class="lph-btn lph-btn-brand" type="submit"><i class="fa-solid fa-upload"></i> {{ __('teacher.portal.save_material') }}</button>
                    </form>
                </div>
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>{{ __('teacher.portal.library') }}</h2>
                        <p>{{ $books->count() === 1 ? __('teacher.portal.uploaded_item', ['count' => $books->count()]) : __('teacher.portal.uploaded_items', ['count' => $books->count()]) }}</p>
                    </div>
                    <div class="lph-list">
                    @forelse($books as $book)
                        <div class="lph-item">
                            <div>
                                <div class="lph-item-title">{{ $book->title }}</div>
                                <div class="lph-item-meta">
                                    {{ optional($classes->firstWhere('classID', $book->classID))->class_name ?? '-' }}
                                    @if($book->author) · {{ $book->author }}@endif
                                    @if($book->file_path)
                                        <br><a href="{{ asset($book->file_path) }}" target="_blank"><i class="fa-solid fa-download"></i> {{ $book->original_name ?? __('teacher.download') }}</a>
                                    @endif
                                    @if(!empty($book->external_url))
                                        <br><a href="{{ $book->external_url }}" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i> {{ __('teacher.portal.open_link') }}</a>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $book->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $book->is_published ? __('teacher.portal.published') : __('teacher.portal.hidden') }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <form method="POST" action="{{ route('student_portal.materials.books.toggle', $book->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $book->is_published ? __('teacher.portal.hide') : __('teacher.portal.publish') }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.books.delete', $book->id) }}" onsubmit="return confirm(@json(__('teacher.portal.confirm_delete_material')));">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">{{ __('teacher.portal.delete') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">{{ __('teacher.portal.empty_files') }}</div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- QUIZZES --}}
        <div class="lph-pane {{ $activeTab === 'quizzes' ? 'active' : '' }}" id="tab-quizzes" data-lph-pane="quizzes">
            <div class="lph-grid quiz">
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2 id="quizFormHeading">{{ !empty($editingQuiz) ? __('teacher.portal.edit_quiz') : __('teacher.portal.create_quiz') }}</h2>
                        <p id="quizFormSub">{{ !empty($editingQuiz) ? __('teacher.portal.edit_quiz_sub') : __('teacher.portal.create_quiz_sub') }}</p>
                    </div>
                    @if(($user_type ?? '') === 'Teacher' && $classes->isEmpty())
                        <div class="lph-hint-warn">
                            {{ __('teacher.portal.no_classes_warn') }}
                        </div>
                    @endif
                    @if(!empty($editingQuiz) && ($editingQuiz['attempts_count'] ?? 0) > 0)
                        <div class="lph-hint-warn">
                            {!! __('teacher.portal.quiz_has_attempts', ['count' => '<strong>'.e($editingQuiz['attempts_count']).'</strong>']) !!}
                        </div>
                    @endif
                    <form method="POST"
                          action="{{ !empty($editingQuiz) ? route('student_portal.materials.quizzes.update', $editingQuiz['id']) : route('student_portal.materials.quizzes.store') }}"
                          id="quizForm"
                          data-store-url="{{ route('student_portal.materials.quizzes.store') }}"
                          data-update-url-template="{{ url('student-portal-materials/quizzes') }}/__ID__">
                        @csrf
                        @if(!empty($editingQuiz))
                            @method('PUT')
                        @endif
                        <div class="lph-quiz-meta">
                            @include('Teacher.partials.portal_class_fields', [
                                'requireSubject' => ($user_type ?? '') === 'Teacher',
                                'subjectClassMap' => $subjectClassMap ?? [],
                            ])
                            <div class="lph-field">
                                <label class="lph-label">{{ __('teacher.portal.quiz_title') }}</label>
                                <input class="form-control" name="title" placeholder="{{ __('teacher.portal.quiz_title_ph') }}" required maxlength="180" value="{{ $editingQuiz['title'] ?? '' }}">
                            </div>
                            <div class="lph-field">
                                <label class="lph-label">{{ __('teacher.portal.instructions') }}</label>
                                <textarea class="form-control" name="instructions" rows="3" placeholder="{{ __('teacher.portal.instructions_ph') }}">{{ $editingQuiz['instructions'] ?? '' }}</textarea>
                            </div>
                            <div class="lph-field">
                                <label class="lph-label">{{ __('teacher.portal.opens_at') }}</label>
                                <input class="form-control" type="datetime-local" name="opens_at" value="{{ $editingQuiz['opens_at'] ?? '' }}">
                            </div>
                            <div class="lph-field">
                                <label class="lph-label">{{ __('teacher.portal.closes_at') }}</label>
                                <input class="form-control" type="datetime-local" name="closes_at" value="{{ $editingQuiz['closes_at'] ?? '' }}">
                            </div>
                        </div>

                        <div class="lph-panel-head" style="margin-top:1.25rem;">
                            <h2 style="font-size:1.05rem;margin:0;">{{ __('teacher.portal.questions') }}</h2>
                            <p style="margin:0.35rem 0 0;">{!! __('teacher.portal.questions_hint', ['strong' => '<strong>'.e(__('teacher.portal.add_question')).'</strong>']) !!}</p>
                        </div>

                        <div class="lph-quiz-add-row">
                            <div class="lph-field">
                                <label class="lph-label" for="sectionTypeSelect">{{ __('teacher.portal.question_type') }}</label>
                                <select class="form-control" id="sectionTypeSelect">
                                    <option value="multiple_choice">{{ __('teacher.portal.type_mc') }}</option>
                                    <option value="true_false">{{ __('teacher.portal.type_tf') }}</option>
                                    <option value="matching">{{ __('teacher.portal.type_match') }}</option>
                                    <option value="fill_blank">{{ __('teacher.portal.type_fill') }}</option>
                                    <option value="short_answer">{{ __('teacher.portal.type_short') }}</option>
                                    <option value="essay">{{ __('teacher.portal.type_essay') }}</option>
                                </select>
                            </div>
                            <button type="button" class="lph-btn lph-btn-soft" id="addSectionBtn">
                                <i class="fa-solid fa-plus"></i> {{ __('teacher.portal.add_question') }}
                            </button>
                        </div>
                        <div id="quizSections"></div>
                        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;align-items:center;">
                            <button class="lph-btn lph-btn-brand" type="submit" id="quizSubmitBtn">
                                <i class="fa-solid fa-floppy-disk"></i> {{ !empty($editingQuiz) ? __('teacher.portal.update_exam') : __('teacher.portal.save_exam') }}
                            </button>
                            <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials') }}" id="quizCancelEditBtn"
                               style="{{ empty($editingQuiz) ? 'display:none;' : '' }}">{{ __('teacher.portal.cancel_edit') }}</a>
                        </div>
                    </form>
                </div>
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>{{ __('teacher.portal.your_quizzes') }}</h2>
                        <p>{{ $quizzes->count() }} {{ $quizzes->count() === 1 ? __('teacher.portal.quiz_one') : __('teacher.portal.quiz_many') }}</p>
                    </div>
                    <div class="lph-list">
                    @forelse($quizzes as $quiz)
                        <div class="lph-item">
                            <div>
                                <div class="lph-item-title">{{ $quiz->title }}</div>
                                <div class="lph-item-meta">
                                    {{ optional($classes->firstWhere('classID', $quiz->classID))->class_name ?? '—' }}
                                    · {{ $quiz->questions->groupBy('section_number')->count() }} {{ __('teacher.portal.sections') }}
                                    · {{ $quiz->questions->count() }} {{ __('teacher.portal.items') }}
                                    · {{ $quiz->attempts->count() }} {{ __('teacher.portal.attempts') }}
                                    @php
                                        $types = $quiz->questions->pluck('question_type')->unique()->filter()->values();
                                    @endphp
                                    @if($types->isNotEmpty())
                                        <br><span class="lph-type-line">{{ $types->map(fn ($t) => \App\Support\StudentPortalQuizScoring::typeLabel($t))->implode(' · ') }}</span>
                                    @endif
                                    @if(method_exists($quiz, 'isScheduled') && $quiz->isScheduled())
                                        <br><span class="lph-badge lph-badge-sched">{{ $quiz->opens_at->format('d M Y H:i') }} → {{ $quiz->closes_at->format('d M Y H:i') }}</span>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $quiz->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $quiz->is_published ? __('teacher.portal.published') : __('teacher.portal.hidden') }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials', ['edit' => $quiz->id, 'tab' => 'quizzes']) }}">{{ __('teacher.portal.edit') }}</a>
                                <form method="POST" action="{{ route('student_portal.materials.quizzes.toggle', $quiz->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $quiz->is_published ? __('teacher.portal.hide') : __('teacher.portal.publish') }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.quizzes.delete', $quiz->id) }}" onsubmit="return confirm(@json(__('teacher.portal.confirm_delete_quiz')));">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">{{ __('teacher.portal.delete') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">{{ __('teacher.portal.empty_quizzes') }}</div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- RESULTS --}}
        <div class="lph-pane {{ $activeTab === 'results' ? 'active' : '' }}" id="tab-results" data-lph-pane="results">
            <div class="lph-panel">
                <div class="lph-panel-head" style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;align-items:flex-end;">
                    <div>
                        <h2>{{ __('teacher.portal.quiz_results') }}</h2>
                        <p>{!! __('teacher.portal.quiz_results_sub', ['strong' => '<strong>'.e(__('teacher.portal.review_mark')).'</strong>']) !!}</p>
                    </div>
                    @php $hasAttempts = $quizzes->contains(fn ($q) => $q->attempts->count() > 0); @endphp
                    @if($hasAttempts)
                    <div class="lph-hero-actions">
                        <a class="lph-btn lph-btn-light" href="{{ route('student_portal.materials.results.export', ['format' => 'xlsx']) }}">
                            <i class="fa-regular fa-file-excel"></i> {{ __('teacher.portal.export_xlsx') }}
                        </a>
                        <a class="lph-btn lph-btn-light" href="{{ route('student_portal.materials.results.export', ['format' => 'csv']) }}">
                            <i class="fa-solid fa-download"></i> {{ __('teacher.portal.export_csv') }}
                        </a>
                    </div>
                    @endif
                </div>
                @if(!$hasAttempts)
                    <div class="lph-empty">{{ __('teacher.portal.empty_results') }}</div>
                @else
                    @foreach($quizzes as $quiz)
                        @if($quiz->attempts->count())
                        <div class="lph-result-block">
                            <div class="lph-result-head">
                                <strong>{{ $quiz->title }}</strong>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                    <span class="lph-type-line" style="font-size:0.82rem;">{{ __('teacher.portal.attempts_questions', ['attempts' => $quiz->attempts->count(), 'questions' => $quiz->questions->count()]) }}</span>
                                    <a class="lph-btn lph-btn-light" href="{{ route('student_portal.materials.results.export.quiz', ['quizId' => $quiz->id, 'format' => 'xlsx']) }}">
                                        <i class="fa-regular fa-file-excel"></i> {{ __('teacher.portal.excel') }}
                                    </a>
                                    <a class="lph-btn lph-btn-light" href="{{ route('student_portal.materials.results.export.quiz', ['quizId' => $quiz->id, 'format' => 'csv']) }}">
                                        {{ __('teacher.portal.csv') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="lph-table">
                                    <thead>
                                        <tr><th>{{ __('teacher.portal.col_student') }}</th><th>{{ __('teacher.portal.col_admission') }}</th><th>{{ __('teacher.portal.col_score') }}</th><th>{{ __('teacher.portal.col_pct') }}</th><th>{{ __('teacher.portal.col_status') }}</th><th>{{ __('teacher.portal.col_submitted') }}</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                    @foreach($quiz->attempts as $attempt)
                                        @php
                                            $st = $studentsById->get($attempt->studentID);
                                            $name = $st ? trim(($st->first_name ?? '').' '.($st->middle_name ?? '').' '.($st->last_name ?? '')) : __('teacher.student').' #'.$attempt->studentID;
                                            $pct = $attempt->total > 0 ? round(($attempt->score / $attempt->total) * 100) : 0;
                                            $hasManualQs = $quiz->questions->contains(fn ($q) => in_array($q->question_type, ['short_answer', 'essay'], true));
                                            $needsMark = $hasManualQs && empty($attempt->graded_at);
                                        @endphp
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>{{ $st->admission_number ?? '-' }}</td>
                                            <td><strong>{{ $attempt->score }}</strong> / {{ $attempt->total }}</td>
                                            <td><span class="lph-pct {{ $pct >= 50 ? 'pass' : 'fail' }}">{{ $pct }}%</span></td>
                                            <td>
                                                @if($needsMark)
                                                    <span class="lph-badge lph-badge-sched">{{ __('teacher.portal.needs_marking') }}</span>
                                                @elseif($hasManualQs && $attempt->graded_at)
                                                    <span class="lph-badge lph-badge-pub">{{ __('teacher.portal.graded') }}</span>
                                                @else
                                                    <span class="lph-badge lph-badge-hid">{{ __('teacher.portal.auto') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y H:i') : '—' }}</td>
                                            <td>
                                                <a class="lph-btn lph-btn-light" style="padding:4px 10px;font-size:0.8rem;"
                                                   href="{{ route('student_portal.materials.attempts.review', ['quizId' => $quiz->id, 'attemptId' => $attempt->id]) }}">
                                                    <i class="fa-solid fa-eye"></i> {{ __('teacher.portal.review_mark') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if(!empty($enabled))
<script>
(function () {
    var i18nPortal = @json(__('teacher.portal'));
    function t(key, fallback) {
        return (i18nPortal && i18nPortal[key]) ? i18nPortal[key] : (fallback || key);
    }
    function tReplace(key, map, fallback) {
        var s = t(key, fallback || '');
        Object.keys(map || {}).forEach(function (k) {
            s = s.replace(new RegExp(':' + k, 'g'), map[k]);
        });
        return s;
    }

    function showTab(name) {
        document.querySelectorAll('.lph-tabs [data-lph-tab]').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-lph-tab') === name);
        });
        document.querySelectorAll('.lph-pane').forEach(function (pane) {
            pane.classList.toggle('active', pane.getAttribute('data-lph-pane') === name);
        });
    }

    document.querySelectorAll('.lph-tabs [data-lph-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showTab(btn.getAttribute('data-lph-tab'));
        });
    });

    var sectionsBox = document.getElementById('quizSections');
    if (sectionsBox) {
        var sectionSeq = 0;
        var typeLabels = {
            multiple_choice: t('type_mc_js', 'Multiple choice'),
            true_false: t('type_tf_js', 'True / False'),
            matching: t('type_match_js', 'Matching (set correct pairs)'),
            fill_blank: t('type_fill_js', 'Fill in the blanks'),
            short_answer: t('type_short_js', 'Short answers (teacher marks)'),
            essay: t('type_essay_js', 'Essay / Assignment (teacher marks)')
        };

        function renumberSections() {
            sectionsBox.querySelectorAll('.lph-section').forEach(function (el, idx) {
                var lab = el.querySelector('.lph-section-num');
                if (lab) lab.textContent = tReplace('question_n', { n: idx + 1 }, 'Question ' + (idx + 1));
            });
        }

        function marksField(name, defaultVal) {
            return '<div class="row align-items-center mt-2">'
                + '<div class="col-auto"><label class="lph-label mb-0" style="letter-spacing:0.06em;">' + t('marks', 'Marks') + '</label></div>'
                + '<div class="col-4 col-md-2">'
                + '<input class="form-control" type="number" min="0.5" step="0.5" name="'+name+'" value="'+defaultVal+'" required title="' + t('marks_title', 'Marks for this question') + '">'
                + '</div></div>';
        }

        function setNamedValue(root, nameEnds, value) {
            if (value == null) return;
            var els = root.querySelectorAll('input, textarea, select');
            for (var i = 0; i < els.length; i++) {
                var n = els[i].getAttribute('name') || '';
                if (n.slice(-nameEnds.length) === nameEnds) {
                    els[i].value = value;
                    return;
                }
            }
        }

        function addItemRow(sectionEl, type, si, data) {
            var items = sectionEl.querySelector('.lph-section-items');
            if (!items) return;
            var ii = items.querySelectorAll('.lph-item-row').length;
            var ptsName = 'sections['+si+'][items]['+ii+'][points]';
            var defPts = (type === 'essay') ? '5' : (type === 'short_answer' ? '2' : '1');
            if (data && data.points != null) defPts = String(data.points);
            var html = '';
            if (type === 'multiple_choice') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>'+tReplace('item_n', { n: ii+1 }, 'Item '+(ii+1))+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" placeholder="'+t('q_text_ph','Question text *')+'" required>'
                    + '<div class="row">'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_a]" placeholder="'+t('option_a','A *')+'" required></div>'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_b]" placeholder="'+t('option_b','B *')+'" required></div>'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_c]" placeholder="'+t('option_c','C')+'"></div>'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_d]" placeholder="'+t('option_d','D')+'"></div>'
                    + '</div>'
                    + '<select class="form-control" name="sections['+si+'][items]['+ii+'][correct_option]" required>'
                    + '<option value="A">'+t('correct_a','Correct: A')+'</option><option value="B">'+t('correct_b','Correct: B')+'</option>'
                    + '<option value="C">'+t('correct_c','Correct: C')+'</option><option value="D">'+t('correct_d','Correct: D')+'</option></select>'
                    + marksField(ptsName, defPts)
                    + '</div>';
            } else if (type === 'true_false') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>'+tReplace('item_roman', { n: ii+1 }, 'Item (roman) '+(ii+1))+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" placeholder="'+t('statement_ph','Statement *')+'" required>'
                    + '<select class="form-control" name="sections['+si+'][items]['+ii+'][correct_option]" required>'
                    + '<option value="true">'+t('true','True')+'</option><option value="false">'+t('false','False')+'</option></select>'
                    + marksField(ptsName, defPts)
                    + '</div>';
            } else if (type === 'fill_blank') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>'+tReplace('blank_n', { n: ii+1 }, 'Blank '+(ii+1))+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" placeholder="'+t('blank_sentence_ph','Sentence with blank e.g. The capital is ____ *')+'" required>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][correct_answer]" placeholder="'+t('correct_answer_ph','Correct answer *')+'" required>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][alt_answers]" placeholder="'+t('alt_answers_ph','Alt answers (optional, separate with |)')+'">'
                    + marksField(ptsName, defPts)
                    + '</div>';
            } else if (type === 'short_answer' || type === 'essay') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>'+(type === 'essay' ? tReplace('essay_n', { n: ii+1 }, 'Essay '+(ii+1)) : tReplace('short_n', { n: ii+1 }, 'Short '+(ii+1)))+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<textarea class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" rows="2" placeholder="'+t('prompt_ph','Prompt *')+'" required></textarea>'
                    + marksField(ptsName, defPts)
                    + '</div>';
            }
            if (!html) return;
            items.insertAdjacentHTML('beforeend', html);
            if (!data) return;
            var row = items.lastElementChild;
            setNamedValue(row, '[question_text]', data.question_text || '');
            setNamedValue(row, '[option_a]', data.option_a || '');
            setNamedValue(row, '[option_b]', data.option_b || '');
            setNamedValue(row, '[option_c]', data.option_c || '');
            setNamedValue(row, '[option_d]', data.option_d || '');
            setNamedValue(row, '[correct_option]', data.correct_option || '');
            setNamedValue(row, '[correct_answer]', data.correct_answer || '');
            setNamedValue(row, '[alt_answers]', data.alt_answers || '');
            setNamedValue(row, '[points]', data.points != null ? data.points : defPts);
        }

        function addMatchPair(sectionEl, si, leftText, rightText) {
            var pairs = sectionEl.querySelector('.lph-match-pairs');
            if (!pairs) return;
            var pi = pairs.querySelectorAll('.lph-match-row').length;
            if (pi >= 10) { alert(t('max_pairs', 'Maximum 10 matching pairs.')); return; }
            pairs.insertAdjacentHTML('beforeend',
                '<div class="lph-match-row row" style="margin-bottom:6px;">'
                + '<div class="col-5"><input class="form-control" name="sections['+si+'][left]['+pi+']" placeholder="'+tReplace('match_left_ph', { n: pi+1 }, 'A'+(pi+1)+' — prompt *')+'" required></div>'
                + '<div class="col-5"><input class="form-control" name="sections['+si+'][right]['+pi+']" placeholder="'+tReplace('match_right_ph', { n: pi+1 }, 'Correct B match for A'+(pi+1)+' *')+'" required></div>'
                + '<div class="col-2"><input type="hidden" name="sections['+si+'][match]['+pi+']" value="'+pi+'">'
                + '<button type="button" class="lph-btn lph-btn-danger remove-match" style="width:100%;padding:6px;">×</button></div></div>'
            );
            var row = pairs.lastElementChild;
            if (leftText != null) setNamedValue(row, '[left]['+pi+']', leftText);
            if (rightText != null) setNamedValue(row, '[right]['+pi+']', rightText);
        }

        function addSection(type, seed) {
            var si = sectionSeq++;
            var wrap = document.createElement('div');
            wrap.className = 'lph-section lph-q';
            wrap.dataset.si = String(si);
            wrap.dataset.type = type;
            var matchPts = (seed && seed.points != null) ? String(seed.points) : '5';
            var body = '<div class="lph-q-top">'
                + '<small><span class="lph-section-num">'+t('question','Question')+'</span> · '+ (typeLabels[type] || type) +'</small>'
                + '<button type="button" class="lph-btn lph-btn-danger remove-section" style="padding:2px 8px;">×</button></div>'
                + '<input type="hidden" name="sections['+si+'][question_type]" value="'+type+'">'
                + '<input class="form-control mb-2" name="sections['+si+'][section_title]" placeholder="'+t('section_title_ph','Section title (optional) e.g. Matching items')+'">'
                + '<textarea class="form-control mb-2" name="sections['+si+'][instructions]" rows="1" placeholder="'+t('section_instructions_ph','Section instructions (optional)')+'"></textarea>';

            if (type === 'matching') {
                body += '<div class="lph-hint">'
                    + '<strong>'+t('matching_how','How matching works:')+'</strong><br>'
                    + t('matching_help', '1) Enter a prompt in column A and its correct answer in column B.<br>2) The system marks automatically.<br>3) Students drag B onto A.<br>Max 10 pairs.')
                    + '</div>'
                    + marksField('sections['+si+'][points]', matchPts)
                    + '<div class="row mb-1 mt-2 lph-match-cols"><div class="col-5">'+t('col_a','Column A (prompt)')+'</div><div class="col-5">'+t('col_b','Column B (correct answer)')+'</div></div>'
                    + '<div class="lph-match-pairs"></div>'
                    + '<button type="button" class="lph-btn lph-btn-soft mb-2 add-match-pair"><i class="fa-solid fa-plus"></i> '+t('add_pair','Add correct pair')+'</button>';
            } else {
                body += '<div class="lph-section-items"></div>'
                    + '<button type="button" class="lph-btn lph-btn-soft mb-2 add-section-item"><i class="fa-solid fa-plus"></i> '+t('add_item','Add item')+'</button>';
            }
            wrap.innerHTML = body;
            sectionsBox.appendChild(wrap);

            if (seed) {
                setNamedValue(wrap, '[section_title]', seed.section_title || '');
                setNamedValue(wrap, '[instructions]', seed.instructions || '');
            }

            if (type === 'matching') {
                if (seed && seed.left && seed.left.length) {
                    for (var mi = 0; mi < seed.left.length; mi++) {
                        addMatchPair(wrap, si, seed.left[mi] || '', (seed.right && seed.right[mi]) ? seed.right[mi] : '');
                    }
                } else {
                    addMatchPair(wrap, si);
                    addMatchPair(wrap, si);
                }
            } else if (seed && seed.items && seed.items.length) {
                seed.items.forEach(function (item) { addItemRow(wrap, type, si, item); });
            } else {
                addItemRow(wrap, type, si);
                if (type === 'true_false') addItemRow(wrap, type, si);
            }
            renumberSections();
        }

        var addSectionBtn = document.getElementById('addSectionBtn');
        var typeSelect = document.getElementById('sectionTypeSelect');
        if (addSectionBtn && typeSelect) {
            addSectionBtn.addEventListener('click', function () {
                addSection(typeSelect.value || 'multiple_choice');
            });
        }

        sectionsBox.addEventListener('click', function (e) {
            var sectionEl = e.target.closest('.lph-section');
            if (!sectionEl) return;
            var si = sectionEl.dataset.si;
            var type = sectionEl.dataset.type;
            if (e.target.closest('.remove-section')) {
                if (sectionsBox.querySelectorAll('.lph-section').length <= 1) return;
                sectionEl.remove();
                renumberSections();
                return;
            }
            if (e.target.closest('.add-section-item')) {
                addItemRow(sectionEl, type, si);
                return;
            }
            if (e.target.closest('.add-match-pair')) {
                addMatchPair(sectionEl, si);
                return;
            }
            if (e.target.closest('.remove-item')) {
                var items = sectionEl.querySelectorAll('.lph-item-row');
                if (items.length > 1) e.target.closest('.lph-item-row').remove();
                return;
            }
            if (e.target.closest('.remove-match')) {
                var rows = sectionEl.querySelectorAll('.lph-match-row');
                if (rows.length > 2) e.target.closest('.lph-match-row').remove();
            }
        });

        var editingQuiz = @json($editingQuiz ?? null);
        if (editingQuiz && editingQuiz.sections && editingQuiz.sections.length) {
            editingQuiz.sections.forEach(function (sec) {
                addSection(sec.question_type || 'multiple_choice', sec);
            });
        } else {
            addSection('multiple_choice');
        }
    }

    function filterPortalPlacement(form, keepSubject) {
        var classSel = form.querySelector('.portal-class');
        var sub = form.querySelector('.portal-subclass');
        var subjectSel = form.querySelector('.portal-subject');
        if (!classSel) return;
        var classId = classSel.value;

        if (sub) {
            Array.prototype.forEach.call(sub.options, function (opt) {
                if (!opt.value) { opt.hidden = false; return; }
                opt.hidden = !classId || opt.getAttribute('data-class') !== classId;
            });
            if (!classSel.dataset.keepSubclass) sub.value = '';
            delete classSel.dataset.keepSubclass;
        }

        if (subjectSel) {
            var current = subjectSel.value;
            var visibleCount = 0;
            Array.prototype.forEach.call(subjectSel.options, function (opt) {
                if (!opt.value) { opt.hidden = false; return; }
                var classes = (opt.getAttribute('data-classes') || '').split(',').filter(Boolean);
                var show = !classId || classes.indexOf(classId) !== -1;
                opt.hidden = !show;
                if (show) visibleCount++;
            });
            if (!keepSubject || !current) {
                subjectSel.value = '';
            } else {
                var selected = subjectSel.querySelector('option[value="'+current+'"]');
                if (!selected || selected.hidden) subjectSel.value = '';
            }
            // Auto-select when only one taught subject for this class
            if (!subjectSel.value && visibleCount === 1) {
                Array.prototype.some.call(subjectSel.options, function (opt) {
                    if (opt.value && !opt.hidden) {
                        subjectSel.value = opt.value;
                        return true;
                    }
                    return false;
                });
            }
        }
    }

    document.querySelectorAll('.portal-class').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var form = sel.closest('form');
            if (!form) return;
            filterPortalPlacement(form, false);
        });
    });

    var editingQuizMeta = @json($editingQuiz ?? null);
    if (editingQuizMeta) {
        var quizForm = document.getElementById('quizForm');
        if (quizForm) {
            var classSel = quizForm.querySelector('.portal-class');
            var subSel = quizForm.querySelector('.portal-subclass');
            var subjectSel = quizForm.querySelector('select[name="subjectID"]');
            if (classSel && editingQuizMeta.classID) {
                classSel.dataset.keepSubclass = '1';
                classSel.value = String(editingQuizMeta.classID);
                filterPortalPlacement(quizForm, true);
            }
            if (subSel && editingQuizMeta.subclassID) {
                subSel.value = String(editingQuizMeta.subclassID);
            }
            if (subjectSel && editingQuizMeta.subjectID) {
                subjectSel.value = String(editingQuizMeta.subjectID);
            }
        }
    } else {
        document.querySelectorAll('.portal-class').forEach(function (sel) {
            var form = sel.closest('form');
            if (form) filterPortalPlacement(form, false);
        });
    }
})();
</script>
@endif
@endpush
