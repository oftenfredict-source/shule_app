@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.my_subjects').' - ShuleXpert')
@section('page_title', __('teacher.pages.my_subjects'))
@section('page_breadcrumb', __('teacher.pages.my_subjects_bc'))

@section('page_actions')
    @if(!empty($resultButtonStatus))
    <button type="button" class="btn btn-primary btn-sm waves-effect" id="btnOpenBulkResults">
        <i class="fa-solid fa-plus me-1"></i> {{ __('teacher.subjects.add_result') }}
    </button>
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    /* Color scheme for #940000 */
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    /*gyguggjgjggjfgjffffjgngjvfgjghg*/
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .subject-card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(148, 0, 0, 0.08) !important;
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .subject-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }
    .enrollment-inactive-row {
        background-color: #eceff1 !important;
        opacity: 0.75;
        color: #607d8b;
    }

    /* View Results + Add Results: visible branded scrollbar (like election modal) */
    #viewResultsModal .modal-body,
    #viewResultsModal #resultsModalBody,
    #addEditResultsModal .modal-body,
    #addEditResultsModal #addEditResultsModalBody {
        max-height: 75vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin;
        scrollbar-color: #940000 #f1f1f1;
    }
    #viewResultsModal .modal-body::-webkit-scrollbar,
    #viewResultsModal #resultsModalBody::-webkit-scrollbar,
    #addEditResultsModal .modal-body::-webkit-scrollbar,
    #addEditResultsModal #addEditResultsModalBody::-webkit-scrollbar {
        width: 8px !important;
        display: block !important;
    }
    #viewResultsModal .modal-body::-webkit-scrollbar-track,
    #viewResultsModal #resultsModalBody::-webkit-scrollbar-track,
    #addEditResultsModal .modal-body::-webkit-scrollbar-track,
    #addEditResultsModal #addEditResultsModalBody::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #viewResultsModal .modal-body::-webkit-scrollbar-thumb,
    #viewResultsModal #resultsModalBody::-webkit-scrollbar-thumb,
    #addEditResultsModal .modal-body::-webkit-scrollbar-thumb,
    #addEditResultsModal #addEditResultsModalBody::-webkit-scrollbar-thumb {
        background: #940000;
        border-radius: 4px;
    }
    body.modal-open {
        overflow: hidden !important;
    }
    .enrollment-inactive-row input,
    .enrollment-inactive-row button {
        pointer-events: none;
        background-color: #cfd8dc !important;
        cursor: not-allowed !important;
    }
    .enrollment-status-badge {
        font-size: 0.7rem;
        vertical-align: middle;
    }
    .action-icon {
        font-size: 1.3rem;
        cursor: pointer;
        transition: all 0.2s;
        padding: 8px;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .action-icon:hover {
        background-color: rgba(148, 0, 0, 0.1);
        transform: scale(1.1);
    }
    .action-icon.view-students {
        color: #17a2b8;
    }
    .action-icon.view-results {
        color: #28a745;
    }
    .action-icon.edit-results {
        color: #ffc107;
    }
    .action-icon.add-results {
        color: #940000;
    }
    .stat-badge {
        background-color: rgba(148, 0, 0, 0.1);
        color: #940000;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
    
    /* Scrollbar for Election Modal */
    #subjectElectionModal .modal-body {
        overflow-y: auto !important;
        max-height: 80vh;
        scrollbar-width: thin;
    }
    #subjectElectionModal .modal-body::-webkit-scrollbar {
        width: 8px;
        display: block !important;
    }
    #subjectElectionModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #subjectElectionModal .modal-body::-webkit-scrollbar-thumb {
        background: #940000;
        border-radius: 4px;
    }
    /* Premium Progress Bar Styling */
    .progress-container {
        width: 100%;
        background-color: #f3f3f3;
        border-radius: 8px;
        overflow: hidden;
        margin: 20px 0;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .progress-bar-premium {
        width: 0%;
        height: 12px;
        background: linear-gradient(90deg, #940000 0%, #d40000 50%, #940000 100%);
        background-size: 200% 100%;
        animation: gradient-pulse 2s infinite linear;
        border-radius: 8px;
        transition: width 0.4s ease-out;
    }
    
    @keyframes gradient-pulse {
        0% { background-position: 100% 0; }
        100% { background-position: -100% 0; }
    }
    
    .loading-text {
        font-weight: 600;
        color: #940000;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    
    .percentage-text {
        font-weight: bold;
        font-size: 1.2rem;
        color: #333;
    }

    /* Duo paper realtime pass/fail bullet (no A/B letter grades) */
    .duo-marks-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 28px;
    }
    .duo-marks-bullet {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #ced4da;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.04);
        flex-shrink: 0;
        display: inline-block;
    }
    .duo-marks-bullet.duo-state-pass {
        background-color: #28a745 !important;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25) !important;
    }
    .duo-marks-bullet.duo-state-fail {
        background-color: #dc3545 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25) !important;
    }
    .duo-marks-bullet.duo-state-invalid {
        background-color: #ffc107 !important;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.28) !important;
    }
    .duo-marks-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6c757d;
    }
    .duo-marks-label.duo-state-pass { color: #28a745 !important; }
    .duo-marks-label.duo-state-fail { color: #dc3545 !important; }
    .duo-marks-label.duo-state-invalid { color: #b8860b !important; }
    .marks-input.duo-marks-ok {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.1rem rgba(40,167,69,0.15);
    }
    .marks-input.duo-marks-bad,
    .marks-input.marks-over-max {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.1rem rgba(220,53,69,0.15);
    }
    .marks-input.duo-marks-warn {
        border-color: #ffc107 !important;
    }
    .marks-max-error {
        display: none;
        color: #dc3545;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 2px;
        line-height: 1.2;
    }
    .marks-max-error.is-visible {
        display: block;
    }
    .marks-max-hint {
        display: block;
        color: #6c757d;
        font-size: 0.72rem;
        margin-top: 2px;
    }
    .results-tools-bar {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px 14px;
        margin-top: 12px;
        margin-bottom: 8px;
    }
    .results-tools-bar label {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: #495057;
    }
    .results-excel-actions .btn {
        margin-left: 4px;
        margin-top: 4px;
    }
    tr.result-student-row.is-search-hidden,
    tr.question-details-row.is-search-hidden {
        display: none !important;
    }

    .sx-teacher-subjects .sx-subjects-toolbar,
    .sx-teacher-subjects .sx-subject-panel {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
        border-top: 4px solid #940000 !important;
    }
    .sx-teacher-subjects .sx-subjects-toolbar {
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .sx-teacher-subjects .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #666 !important;
        margin-bottom: 0.3rem;
    }
    .sx-teacher-subjects .form-control,
    .sx-teacher-subjects .form-select,
    .sx-teacher-subjects .btn {
        border-radius: 0 !important;
    }
    .sx-teacher-subjects .form-select,
    .sx-teacher-subjects .form-control {
        min-height: 42px;
        font-weight: 600;
        border: 1px solid #cfcfcf;
    }
    .sx-teacher-subjects .form-select:focus,
    .sx-teacher-subjects .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.18rem rgba(148,0,0,0.12);
    }
    .sx-teacher-subjects .subject-item { display: none; }
    .sx-teacher-subjects .subject-item.is-active { display: block; }
    .sx-teacher-subjects .sx-subject-panel { padding: 1rem 1.1rem; }
    .sx-teacher-subjects .sx-subject-head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-start;
        margin-bottom: 0.85rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(148,0,0,0.1);
    }
    .sx-teacher-subjects .sx-subject-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #940000 !important;
        margin: 0;
    }
    .sx-teacher-subjects .sx-subject-meta {
        font-size: 0.84rem;
        color: #666 !important;
        margin-top: 0.2rem;
    }
    .sx-teacher-subjects .sx-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.45rem;
    }
    .sx-teacher-subjects .sx-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        min-height: 72px;
        padding: 0.55rem 0.4rem;
        border: 1px solid #e6e6e6;
        background: #fff;
        color: #940000;
        font-size: 0.72rem;
        font-weight: 800;
        text-align: center;
        cursor: pointer;
    }
    .sx-teacher-subjects .sx-action-btn:hover {
        background: #fcf7f7;
        border-color: #940000;
    }
    .sx-teacher-subjects .sx-action-btn i { font-size: 1.15rem; line-height: 1; }
    .sx-teacher-subjects .stat-badge { border-radius: 0 !important; }
    .sx-teacher-subjects .action-icon {
        border-radius: 0 !important;
        width: auto;
        height: auto;
        padding: 0;
        background: transparent !important;
    }
    .sx-teacher-subjects .action-icon:hover {
        transform: none;
        background: transparent !important;
    }
    .sx-teacher-subjects .subject-card {
        box-shadow: none !important;
        border: none !important;
        transform: none !important;
    }
    .sx-teacher-subjects .subject-card:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-subjects,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-subjects p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-subjects small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-subjects strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-subjects label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-teacher-subjects td {
        color: #000 !important;
    }
    .modal-header.bg-primary-custom {
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%) !important;
        border: none;
    }
    .modal-content { border-radius: 0 !important; }

    /* Add Result modal */
    #bulkResultsFilterModal .modal-dialog {
        max-width: 640px;
        margin: 1.25rem auto;
    }
    #bulkResultsFilterModal .sx-bulk-modal {
        border: none;
        border-radius: 0;
        overflow: visible;
        background: #fff;
        box-shadow: 0 22px 56px rgba(15, 23, 42, 0.22);
    }
    #bulkResultsFilterModal .modal-header {
        border: none;
        padding: 1.15rem 1.4rem;
        background: linear-gradient(135deg, #940000 0%, #6e0000 100%);
        color: #fff !important;
    }
    #bulkResultsFilterModal .modal-title {
        margin: 0;
        font-size: 0.9rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 800;
        color: #fff !important;
    }
    #bulkResultsFilterModal .modal-body {
        padding: 1.15rem 1.35rem 0.5rem;
        background: #faf7f7;
    }
    #bulkResultsFilterModal .sx-bulk-lead {
        font-size: 0.86rem;
        font-weight: 600;
        color: #555 !important;
        margin: 0 0 0.95rem;
        line-height: 1.45;
    }
    #bulkResultsFilterModal .sx-bulk-section {
        background: #fff;
        border: 1px solid rgba(148, 0, 0, 0.1);
        border-top: 3px solid #940000;
        padding: 1rem 1.05rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    #bulkResultsFilterModal .sx-bulk-section-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0 0 0.85rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #940000 !important;
    }
    #bulkResultsFilterModal .sx-bulk-step {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #940000;
        color: #fff !important;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #bulkResultsFilterModal .form-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #222 !important;
        margin-bottom: 0.4rem;
        text-transform: none;
        letter-spacing: 0;
    }
    #bulkResultsFilterModal .form-hint {
        display: block;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        color: #666 !important;
        line-height: 1.4;
    }
    #bulkResultsFilterModal .form-control {
        border-radius: 0 !important;
        min-height: 46px !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        color: #111 !important;
        background: #fff !important;
        border: 1px solid #cfcfcf !important;
        box-shadow: none !important;
    }
    #bulkResultsFilterModal .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.18rem rgba(148, 0, 0, 0.12) !important;
    }
    #bulkResultsFilterModal .form-control:disabled {
        background: #f3f3f3 !important;
        color: #888 !important;
    }
    #bulkResultsFilterModal .sx-bulk-duo-hint {
        margin-top: 0.65rem;
        padding: 0.65rem 0.8rem;
        background: #fcf7f7;
        border: 1px solid rgba(148, 0, 0, 0.12);
        font-size: 0.82rem;
        font-weight: 700;
        color: #940000 !important;
    }
    #bulkResultsFilterModal .modal-footer {
        border-top: 1px solid rgba(148, 0, 0, 0.1);
        background: #fff;
        padding: 0.9rem 1.35rem 1.1rem;
        gap: 0.5rem;
    }
    #bulkResultsFilterModal .modal-footer .btn {
        border-radius: 0 !important;
        font-weight: 800;
        min-height: 42px;
        padding: 0.5rem 1rem;
    }
    #bulkResultsFilterModal .modal-footer .btn-primary-custom {
        background: #940000;
        border-color: #940000;
        color: #fff;
    }
    #bulkResultsFilterModal .modal-footer .btn-primary-custom:disabled {
        opacity: 0.55;
    }
    #bulkResultsFilterModal .modal-footer .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000;
    }
    #bulkResultsFilterModal .modal-footer .btn-outline-primary:hover:not(:disabled) {
        background: #940000;
        color: #fff !important;
    }
    #bulkResultsFilterModal .modal-footer .btn-outline-secondary {
        color: #555;
        border-color: #cfcfcf;
    }
    @media (max-width: 576px) {
        #bulkResultsFilterModal .modal-footer .btn { width: 100%; }
    }

    /* Dark mode — toolbar, panels, labels, actions, modals */
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-subjects-toolbar,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-subjects-toolbar,
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-subject-panel,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-subject-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .form-label,
    html[data-bs-theme="dark"] .sx-teacher-subjects .form-label,
    html[data-sx-theme="dark"] .sx-teacher-subjects label.form-label,
    html[data-bs-theme="dark"] .sx-teacher-subjects label.form-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .form-control,
    html[data-bs-theme="dark"] .sx-teacher-subjects .form-control,
    html[data-sx-theme="dark"] .sx-teacher-subjects .form-select,
    html[data-bs-theme="dark"] .sx-teacher-subjects .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .form-select option,
    html[data-bs-theme="dark"] .sx-teacher-subjects .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-subject-title,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-subject-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-subject-meta,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-subject-meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-subject-head,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-subject-head {
        border-bottom-color: rgba(255, 180, 180, 0.18) !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .stat-badge,
    html[data-bs-theme="dark"] .sx-teacher-subjects .stat-badge {
        background-color: rgba(148, 0, 0, 0.35) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-action-btn,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-action-btn {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .sx-action-btn:hover,
    html[data-bs-theme="dark"] .sx-teacher-subjects .sx-action-btn:hover {
        background: #2a1c20 !important;
        border-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .badge.bg-light,
    html[data-bs-theme="dark"] .sx-teacher-subjects .badge.bg-light {
        background: #3a2a2e !important;
        color: #f3e9eb !important;
        border-color: #4a383c !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .alert-info,
    html[data-bs-theme="dark"] .sx-teacher-subjects .alert-info {
        background: #2a1c20 !important;
        border-color: #3a2a2e !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-teacher-subjects .btn-outline-secondary,
    html[data-bs-theme="dark"] .sx-teacher-subjects .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
    }
    html[data-sx-theme="dark"] .results-tools-bar,
    html[data-bs-theme="dark"] .results-tools-bar {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .results-tools-bar label,
    html[data-bs-theme="dark"] .results-tools-bar label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .percentage-text,
    html[data-bs-theme="dark"] .percentage-text {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .marks-max-hint,
    html[data-bs-theme="dark"] .marks-max-hint,
    html[data-sx-theme="dark"] .duo-marks-label:not(.duo-state-pass):not(.duo-state-fail):not(.duo-state-invalid),
    html[data-bs-theme="dark"] .duo-marks-label:not(.duo-state-pass):not(.duo-state-fail):not(.duo-state-invalid) {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .enrollment-inactive-row,
    html[data-bs-theme="dark"] .enrollment-inactive-row {
        background-color: #2a2226 !important;
        color: #b0a0a4 !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .sx-bulk-modal,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .sx-bulk-modal {
        background: #24181c !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .modal-body,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .modal-body {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .sx-bulk-lead,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .sx-bulk-lead,
    html[data-sx-theme="dark"] #bulkResultsFilterModal .sx-bulk-lead strong,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .sx-bulk-lead strong {
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .sx-bulk-section,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .sx-bulk-section {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .sx-bulk-section-title,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .sx-bulk-section-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .form-label,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .form-label {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .form-hint,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .form-hint {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .form-control,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .form-control {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .form-control:disabled,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .form-control:disabled {
        background: #2a2226 !important;
        color: #a8989c !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .sx-bulk-duo-hint,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .sx-bulk-duo-hint {
        background: #2a1c20 !important;
        border-color: #4a383c !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .modal-footer,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .modal-footer {
        background: #24181c !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #bulkResultsFilterModal .modal-footer .btn-outline-secondary,
    html[data-bs-theme="dark"] #bulkResultsFilterModal .modal-footer .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
    }
    html[data-sx-theme="dark"] #viewStudentsModal .modal-content,
    html[data-bs-theme="dark"] #viewStudentsModal .modal-content,
    html[data-sx-theme="dark"] #sessionAttendanceModal .modal-content,
    html[data-bs-theme="dark"] #sessionAttendanceModal .modal-content,
    html[data-sx-theme="dark"] #examAttendanceModal .modal-content,
    html[data-bs-theme="dark"] #examAttendanceModal .modal-content,
    html[data-sx-theme="dark"] #viewResultsModal .modal-content,
    html[data-bs-theme="dark"] #viewResultsModal .modal-content,
    html[data-sx-theme="dark"] #addEditResultsModal .modal-content,
    html[data-bs-theme="dark"] #addEditResultsModal .modal-content,
    html[data-sx-theme="dark"] #uploadExcelModal .modal-content,
    html[data-bs-theme="dark"] #uploadExcelModal .modal-content,
    html[data-sx-theme="dark"] #subjectElectionModal .modal-content,
    html[data-bs-theme="dark"] #subjectElectionModal .modal-content {
        background: #24181c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #viewStudentsModal .modal-body,
    html[data-bs-theme="dark"] #viewStudentsModal .modal-body,
    html[data-sx-theme="dark"] #sessionAttendanceModal .modal-body,
    html[data-bs-theme="dark"] #sessionAttendanceModal .modal-body,
    html[data-sx-theme="dark"] #examAttendanceModal .modal-body,
    html[data-bs-theme="dark"] #examAttendanceModal .modal-body,
    html[data-sx-theme="dark"] #viewResultsModal .modal-body,
    html[data-bs-theme="dark"] #viewResultsModal .modal-body,
    html[data-sx-theme="dark"] #addEditResultsModal .modal-body,
    html[data-bs-theme="dark"] #addEditResultsModal .modal-body,
    html[data-sx-theme="dark"] #uploadExcelModal .modal-body,
    html[data-bs-theme="dark"] #uploadExcelModal .modal-body,
    html[data-sx-theme="dark"] #subjectElectionModal .modal-body,
    html[data-bs-theme="dark"] #subjectElectionModal .modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #viewStudentsModal .modal-body .text-muted,
    html[data-bs-theme="dark"] #viewStudentsModal .modal-body .text-muted,
    html[data-sx-theme="dark"] #subjectElectionModal .text-muted,
    html[data-bs-theme="dark"] #subjectElectionModal .text-muted,
    html[data-sx-theme="dark"] #uploadExcelModal .text-muted,
    html[data-bs-theme="dark"] #uploadExcelModal .text-muted,
    html[data-sx-theme="dark"] #viewResultsModal .text-muted,
    html[data-bs-theme="dark"] #viewResultsModal .text-muted,
    html[data-sx-theme="dark"] #addEditResultsModal .text-muted,
    html[data-bs-theme="dark"] #addEditResultsModal .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #viewStudentsModal .table,
    html[data-bs-theme="dark"] #viewStudentsModal .table,
    html[data-sx-theme="dark"] #viewResultsModal .table,
    html[data-bs-theme="dark"] #viewResultsModal .table,
    html[data-sx-theme="dark"] #addEditResultsModal .table,
    html[data-bs-theme="dark"] #addEditResultsModal .table,
    html[data-sx-theme="dark"] #subjectElectionModal .table,
    html[data-bs-theme="dark"] #subjectElectionModal .table {
        color: #f3e9eb !important;
        --bs-table-bg: transparent;
        --bs-table-color: #f3e9eb;
        --bs-table-striped-bg: #24181c;
        --bs-table-hover-bg: #2a1c20;
        --bs-table-border-color: #3a2a2e;
    }
    html[data-sx-theme="dark"] #viewStudentsModal .table thead th,
    html[data-bs-theme="dark"] #viewStudentsModal .table thead th,
    html[data-sx-theme="dark"] #viewResultsModal .table thead th,
    html[data-bs-theme="dark"] #viewResultsModal .table thead th,
    html[data-sx-theme="dark"] #addEditResultsModal .table thead th,
    html[data-bs-theme="dark"] #addEditResultsModal .table thead th,
    html[data-sx-theme="dark"] #subjectElectionModal .table thead th,
    html[data-bs-theme="dark"] #subjectElectionModal .table thead th {
        background: #24181c !important;
        color: #ffb4b4 !important;
        border-color: #3a2a2e !important;
    }
</style>
@endpush

@section('content')
<div class="sx-teacher-subjects">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('teacher.close') }}"></button>
                </div>
            @endif

            <!-- Bulk Class Results Modal -->
            <div class="modal fade" id="bulkResultsFilterModal" tabindex="-1" aria-labelledby="bulkResultsFilterModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content sx-bulk-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bulkResultsFilterModalLabel">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('teacher.subjects.add_result') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <p class="sx-bulk-lead">
                                {!! __('teacher.subjects.bulk_lead') !!}
                            </p>

                            <div class="sx-bulk-section">
                                <h6 class="sx-bulk-section-title"><span class="sx-bulk-step">1</span> {{ __('teacher.class') }}</h6>
                                <label class="form-label" for="bulkClassID">{{ __('teacher.subjects.select_class') }}</label>
                                <select class="form-control" id="bulkClassID">
                                    <option value="">{{ __('teacher.subjects.select_class_opt') }}</option>
                                </select>
                            </div>

                            <div class="sx-bulk-section">
                                <h6 class="sx-bulk-section-title"><span class="sx-bulk-step">2</span> {{ __('teacher.subjects.stream') }}</h6>
                                <label class="form-label" for="bulkSubclassID">{{ __('teacher.subjects.subclass_stream') }}</label>
                                <select class="form-control" id="bulkSubclassID" disabled>
                                    <option value="">{{ __('teacher.subjects.all_subclasses') }}</option>
                                </select>
                                <span class="form-hint">{{ __('teacher.subjects.all_subclasses_hint') }}</span>
                            </div>

                            <div class="sx-bulk-section">
                                <h6 class="sx-bulk-section-title"><span class="sx-bulk-step">3</span> {{ __('teacher.subject') }}</h6>
                                <label class="form-label" for="bulkSubjectID">{{ __('teacher.subjects.select_subject') }}</label>
                                <select class="form-control" id="bulkSubjectID" disabled>
                                    <option value="">{{ __('teacher.subjects.select_subject_opt') }}</option>
                                </select>
                                <div class="sx-bulk-duo-hint" id="bulkDuoHint" style="display:none;">
                                    <i class="bi bi-layers"></i> {{ __('teacher.subjects.duo_hint') }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
                            <button type="button" class="btn btn-outline-primary" id="bulkBtnViewResult" disabled>
                                <i class="bi bi-eye"></i> {{ __('teacher.subjects.view') }}
                            </button>
                            <button type="button" class="btn btn-primary-custom" id="bulkBtnAddResult" disabled>
                                <i class="bi bi-pencil-square"></i> {{ __('teacher.subjects.add_result') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $activeSubjects = ($classSubjects ?? collect())->filter(function ($cs) {
                    return $cs->subject && $cs->subject->status === 'Active' && $cs->status === 'Active';
                })->values();
                $firstSubjectId = optional($activeSubjects->first())->class_subjectID;
            @endphp

            <div class="sx-subjects-toolbar">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-8">
                        <label class="form-label" for="subjectSelect">{{ __('teacher.subjects.select_subject') }}</label>
                        <select id="subjectSelect" class="form-select">
                            @if($activeSubjects->isEmpty())
                                <option value="">{{ __('teacher.subjects.no_subjects_assigned') }}</option>
                            @else
                                @foreach($activeSubjects as $classSubject)
                                    @php
                                        $allSubLabel = __('teacher.subjects.all_subclasses');
                                        $labelClass = $classSubject->subclass
                                            ? trim((optional(optional($classSubject->subclass)->class)->class_name ?? '') . ' - ' . ($classSubject->subclass->subclass_name ?? ''), ' -')
                                            : (optional($classSubject->class)->class_name ? (optional($classSubject->class)->class_name . ' - ' . $allSubLabel) : $allSubLabel);
                                        $optLabel = ($classSubject->subject->subject_name ?? 'N/A')
                                            . ($classSubject->subject->subject_code ? ' (' . $classSubject->subject->subject_code . ')' : '')
                                            . ' · ' . $labelClass
                                            . (!empty($classSubject->is_duo_subject) ? ' · DUO' : '');
                                    @endphp
                                    <option value="{{ $classSubject->class_subjectID }}" @selected($classSubject->class_subjectID == $firstSubjectId)>
                                        {{ $optLabel }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label" for="subjectSearchInput">{{ __('teacher.subjects.quick_filter') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="subjectSearchInput" placeholder="{{ __('teacher.subjects.filter_placeholder') }}">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display:none;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="subjectsGrid">
                @if($activeSubjects->isEmpty())
                    <div class="alert alert-info text-center mb-0">
                        <i class="bi bi-info-circle"></i> {{ __('teacher.subjects.no_subjects_yet') }}
                    </div>
                @else
                    @foreach($activeSubjects as $classSubject)
                        @php
                            $allSubLabel = __('teacher.subjects.all_subclasses');
                            $subjectName = strtolower($classSubject->subject->subject_name ?? '');
                            $subjectCode = strtolower($classSubject->subject->subject_code ?? '');
                            $className = $classSubject->subclass ? strtolower($classSubject->subclass->subclass_name ?? '') : 'all subclasses';
                            $displayClass = $classSubject->subclass
                                ? (optional(optional($classSubject->subclass)->class)->class_name
                                    ? optional($classSubject->subclass->class)->class_name . ' - ' . $classSubject->subclass->subclass_name
                                    : ($classSubject->subclass->subclass_name ?? 'N/A'))
                                : (optional($classSubject->class)->class_name
                                    ? optional($classSubject->class)->class_name . ' - ' . $allSubLabel
                                    : $allSubLabel);
                            $studentsCount = (int) ($classSubject->total_students ?? 0);
                            $studentsLabel = $studentsCount === 1
                                ? __('teacher.subjects.student_count', ['count' => $studentsCount])
                                : __('teacher.subjects.students_count', ['count' => $studentsCount]);
                        @endphp
                        <div class="subject-item {{ $classSubject->class_subjectID == $firstSubjectId ? 'is-active' : '' }}"
                             data-subject-id="{{ $classSubject->class_subjectID }}"
                             data-subject-name="{{ $subjectName }}"
                             data-subject-code="{{ $subjectCode }}"
                             data-class-name="{{ $className }}"
                             data-main-class="{{ optional(optional($classSubject->subclass)->class)->class_name ?? optional($classSubject->class)->class_name }}"
                             data-is-duo="{{ !empty($classSubject->is_duo_subject) ? '1' : '0' }}">
                            <div class="sx-subject-panel subject-card card border-0">
                                <div class="sx-subject-head">
                                    <div>
                                        <h5 class="sx-subject-title">
                                            {{ $classSubject->subject->subject_name ?? 'N/A' }}
                                            @if(!empty($classSubject->is_duo_subject))
                                                <span class="badge bg-danger" style="background:#940000 !important;font-size:0.65rem;vertical-align:middle;">DUO</span>
                                            @endif
                                        </h5>
                                        <div class="sx-subject-meta">
                                            @if($classSubject->subject->subject_code)
                                                {{ __('teacher.subjects.code') }}: {{ $classSubject->subject->subject_code }} ·
                                            @endif
                                            {{ $displayClass }} ·
                                            <span class="stat-badge">{{ $studentsLabel }}</span>
                                        </div>
                                        @if(!empty($classSubject->is_duo_subject) && !empty($classSubject->my_assigned_papers) && $classSubject->my_assigned_papers->count())
                                            <div class="mt-1">
                                                @foreach($classSubject->my_assigned_papers as $paper)
                                                    <span class="badge bg-light text-dark border me-1">{{ $paper['component_name'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="sx-action-grid">
                                    <div class="sx-action-btn" role="button" tabindex="0"
                                        data-class-subject-id="{{ $classSubject->class_subjectID }}"
                                        data-subject-name="{{ e($classSubject->subject->subject_name ?? __('teacher.subject')) }}"
                                        onclick="openHomePackageModal(this.getAttribute('data-class-subject-id'), this.getAttribute('data-subject-name'))">
                                        <i class="bi bi-house-door-fill action-icon" style="color:#940000;"></i>
                                        {{ __('teacher.subjects.home_package') }}
                                    </div>
                                    <div class="sx-action-btn" role="button" tabindex="0"
                                        data-class-subject-id="{{ $classSubject->class_subjectID }}"
                                        data-subject-name="{{ e($classSubject->subject->subject_name ?? __('teacher.subject')) }}"
                                        onclick="openSubjectNotesModal(this.getAttribute('data-class-subject-id'), this.getAttribute('data-subject-name'), 'note')">
                                        <i class="bi bi-journal-text action-icon" style="color:#940000;"></i>
                                        {{ __('teacher.subjects.notes') }}
                                    </div>
                                    <div class="sx-action-btn" role="button" tabindex="0"
                                        data-class-subject-id="{{ $classSubject->class_subjectID }}"
                                        data-subject-name="{{ e($classSubject->subject->subject_name ?? __('teacher.subject')) }}"
                                        onclick="openSubjectNotesModal(this.getAttribute('data-class-subject-id'), this.getAttribute('data-subject-name'), 'past_paper')">
                                        <i class="bi bi-file-earmark-text action-icon" style="color:#940000;"></i>
                                        {{ __('teacher.subjects.past_papers') }}
                                    </div>
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-people-fill action-icon view-students" onclick="viewStudents({{ $classSubject->class_subjectID }})"></i>
                                        {{ __('teacher.subjects.students') }}
                                    </div>
                                    @if(!empty($classSubject->is_duo_subject))
                                    <div class="sx-action-btn" role="button" tabindex="0"
                                        onclick='viewDuoPapers(@json([
                                            "subject" => $classSubject->subject->subject_name ?? __("teacher.subject"),
                                            "papers" => $classSubject->all_paper_teachers ?? []
                                        ]))'>
                                        <i class="bi bi-layers action-icon" style="color:#940000;"></i>
                                        {{ __('teacher.subjects.papers') }}
                                    </div>
                                    @endif
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-clock-history action-icon" style="color:#17a2b8;" onclick="viewSessionAttendance({{ $classSubject->class_subjectID }})"></i>
                                        {{ __('teacher.subjects.session_att') }}
                                    </div>
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-calendar-check action-icon" style="color:#940000;" onclick="viewExamAttendance({{ $classSubject->class_subjectID }}, {{ $classSubject->subject->subjectID ?? 0 }})"></i>
                                        {{ __('teacher.subjects.exam_att') }}
                                    </div>
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-clipboard-check action-icon view-results" onclick="viewResults({{ $classSubject->class_subjectID }})"></i>
                                        {{ __('teacher.subjects.view_results') }}
                                    </div>
                                    @if($resultButtonStatus)
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-pencil-square action-icon edit-results" onclick="editResults({{ $classSubject->class_subjectID }})"></i>
                                        {{ __('teacher.subjects.edit_result') }}
                                    </div>
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-plus-circle-fill action-icon add-results" onclick="addResults({{ $classSubject->class_subjectID }})"></i>
                                        {{ __('teacher.subjects.add_result') }}
                                    </div>
                                    @endif
                                    @if($classSubject->student_status === 'Optional')
                                    <div class="sx-action-btn" role="button" tabindex="0">
                                        <i class="bi bi-person-check action-icon" style="color:#007bff;"
                                           onclick="openElectionModal({{ $classSubject->class_subjectID }}, '{{ addslashes($classSubject->subject->subject_name) }}', {{ $classSubject->subclassID ?? 0 }})"></i>
                                        {{ __('teacher.subjects.election') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
</div>

<!-- Subject Election Modal -->
<div class="modal fade" id="subjectElectionModal" tabindex="-1" aria-labelledby="subjectElectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="subjectElectionModalLabel">
                    <i class="bi bi-person-check"></i> <span id="electionSubjectTitle">{{ __('teacher.subjects.subject_election') }}</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle"></i> {{ __('teacher.subjects.election_hint') }}
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-success" id="electionSelectAllBtn">
                            <i class="bi bi-check-all"></i> {{ __('teacher.subjects.select_all') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="electionDeselectAllBtn">
                            <i class="bi bi-dash-circle"></i> {{ __('teacher.subjects.deselect_all') }}
                        </button>
                    </div>
                </div>
                <div id="electionStudentsContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">{{ __('teacher.loading') }}</span>
                        </div>
                        <p class="mt-2">{{ __('teacher.subjects.loading_students') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> {{ __('teacher.cancel') }}
                </button>
                <button type="button" class="btn btn-primary-custom" id="saveElectionBtn">
                    <i class="bi bi-save"></i> {{ __('teacher.subjects.save_election') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Students Modal -->
<div class="modal" id="viewStudentsModal" role="dialog" aria-labelledby="viewStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStudentsModalLabel">
                    <i class="bi bi-people"></i> {{ __('teacher.subjects.students') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="studentsModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #viewStudentsModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #viewStudentsModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #viewStudentsModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">{{ __('teacher.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Attendance Modal -->
<div class="modal" id="sessionAttendanceModal" role="dialog" aria-labelledby="sessionAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="sessionAttendanceModalLabel">
                    <i class="bi bi-clock-history"></i> {{ __('teacher.subjects.session_attendance') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="sessionAttendanceModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #sessionAttendanceModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #sessionAttendanceModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #sessionAttendanceModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">{{ __('teacher.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Exam Attendance Modal -->
<div class="modal" id="examAttendanceModal" role="dialog" aria-labelledby="examAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="examAttendanceModalLabel">
                    <i class="bi bi-calendar-check"></i> {{ __('teacher.subjects.exam_attendance') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="examAttendanceModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #examAttendanceModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #examAttendanceModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #examAttendanceModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">{{ __('teacher.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Results Modal -->
<div class="modal" id="viewResultsModal" role="dialog" aria-labelledby="viewResultsModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewResultsModalLabel">
                    <i class="bi bi-clipboard-check"></i> {{ __('teacher.subjects.results') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="resultsModalBody" style="max-height: 75vh; overflow-y: auto; overflow-x: hidden;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">{{ __('teacher.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Results Modal -->
<div class="modal" id="addEditResultsModal" role="dialog" aria-labelledby="addEditResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addEditResultsModalLabel">
                    <i class="bi bi-plus-circle"></i> {{ __('teacher.subjects.add_results') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <div class="modal-body" id="addEditResultsModalBody" style="max-height: 75vh; overflow-y: auto; overflow-x: hidden;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">{{ __('teacher.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Excel Modal -->
<div class="modal" id="uploadExcelModal" role="dialog" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="uploadExcelModalLabel">
                    <i class="bi bi-upload"></i> {{ __('teacher.subjects.upload_excel_results') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <form id="uploadExcelForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="upload_class_subject_id" name="class_subject_id">
                    <input type="hidden" id="upload_exam_id" name="exam_id">
                    <div class="form-group">
                        <label>{{ __('teacher.subjects.select_excel_file') }} <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
                            <label class="custom-file-label" for="excel_file" id="excel_file_label">
                                <i class="bi bi-file-earmark-excel"></i> {{ __('teacher.subjects.choose_excel_file') }}
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> {{ __('teacher.subjects.excel_file_hint') }}
                        </small>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>{{ __('teacher.subjects.note') }}:</strong> {!! __('teacher.subjects.excel_note') !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('teacher.cancel') }}</button>
                    <button type="button" class="btn btn-primary-custom" id="btnLoadExcelIntoForm">
                        <i class="bi bi-upload"></i> {{ __('teacher.subjects.load_into_form') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Teacher.partials.home_package_modal')
@include('Teacher.partials.subject_notes_modal')
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.31/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
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
</script>
<script>
// Ensure jQuery is available
if (typeof $ === 'undefined') {
    var $ = jQuery;
}

var i18nSub = @json(__('teacher.subjects'));
i18nSub.cancel = @json(__('teacher.cancel'));
i18nSub.save = @json(__('teacher.save'));
i18nSub.loading = @json(__('teacher.loading'));
i18nSub.close = @json(__('teacher.close'));
i18nSub.subject = @json(__('teacher.subject'));
i18nSub.class = @json(__('teacher.class'));
i18nSub.search = @json(__('teacher.search'));
i18nSub.active = @json(__('teacher.active'));
i18nSub.inactive = @json(__('teacher.inactive'));

const isSecondarySchool = @json(strtolower($schoolType ?? 'Secondary')) === 'secondary';
const questionColspan = isSecondarySchool ? 7 : 6;
window.teacherBulkResultScope = @json($bulkResultScope ?? []);
window._allSubclassesMode = false;
window._classSubjectMap = {};

function withAllSubclassesParam(url) {
    if (!window._allSubclassesMode) return url;
    return url + (url.indexOf('?') >= 0 ? '&' : '?') + 'all_subclasses=1';
}

function resetTeacherBulkMode() {
    window._allSubclassesMode = false;
    window._classSubjectMap = {};
}
// Helper for Premium Progress Bar Loading
// LocalStorage Cache Logic for Offline Persistence
const RESULTS_CACHE_PREFIX = 'shulelink_marks_cache_';

function getResultsCacheKey() {
    const classSubjectID = jQuery('#class_subject_id').val();
    const examID = jQuery('#exam_id').val();
    const testWeek = jQuery('#test_week').val() || 'none';
    // Duo papers must be isolated without componentID, PHY1/PHY2 share one cache and restore wrong totals
    const componentID = jQuery('#component_id').val() || window.selectedComponentID || '0';
    if (!classSubjectID || !examID) return null;
    return `${RESULTS_CACHE_PREFIX}${classSubjectID}_${examID}_${testWeek}_c${componentID}`;
}

function normalizeCacheMark(marks) {
    if (marks === null || marks === undefined || marks === '') return '';
    const n = parseFloat(marks);
    if (isNaN(n)) return '';
    return Number.isInteger(n) ? String(n) : String(n);
}

function saveMarkToCache(studentID, fieldKey, marks) {
    const key = getResultsCacheKey();
    if (!key || !studentID) return;

    let cache = JSON.parse(localStorage.getItem(key) || '{}');
    if (!cache[studentID]) cache[studentID] = {};

    const normalized = normalizeCacheMark(marks);
    if (normalized === '') {
        delete cache[studentID][fieldKey];
        if (Object.keys(cache[studentID]).length === 0) {
            delete cache[studentID];
        }
    } else {
        cache[studentID][fieldKey] = normalized;
    }
    localStorage.setItem(key, JSON.stringify(cache));
}

function restoreMarksFromCache() {
    const key = getResultsCacheKey();
    if (!key) return;

    const cache = JSON.parse(localStorage.getItem(key) || '{}');
    if (Object.keys(cache).length === 0) return;

    let restoredCount = 0;
    const studentsWithCachedMain = {};

    Object.keys(cache).forEach(studentID => {
        const studentMarks = cache[studentID] || {};

        // 1) Restore question-level marks into memory first
        Object.keys(studentMarks).forEach(qID => {
            if (qID === 'main') return;
            if (!examQuestionData.marksByStudent[studentID]) {
                examQuestionData.marksByStudent[studentID] = {};
            }
            const qVal = normalizeCacheMark(studentMarks[qID]);
            if (qVal === '') return;
            examQuestionData.marksByStudent[studentID][qID] = qVal;

            const $qInput = jQuery(`.question-mark-input[data-student="${studentID}"][data-question-id="${qID}"]`);
            if ($qInput.length) {
                $qInput.val(qVal);
                const $optionalCheck = jQuery(`.optional-select[data-student="${studentID}"][data-question-id="${qID}"]`);
                if ($optionalCheck.length && parseFloat(qVal) > 0) {
                    $optionalCheck.prop('checked', true);
                    $qInput.prop('disabled', false);
                }
                restoredCount++;
            }
        });

        // 2) Prefer explicit main total from cache (do NOT let question recalc overwrite it)
        if (studentMarks['main'] !== undefined && studentMarks['main'] !== null && studentMarks['main'] !== '') {
            const mainVal = normalizeCacheMark(studentMarks['main']);
            const $mainInput = jQuery(`#marks_${studentID}`);
            if ($mainInput.length && mainVal !== '') {
                $mainInput.val(mainVal);
                autoCalculateGrade(studentID);
                studentsWithCachedMain[studentID] = true;
                restoredCount++;
            }
        } else {
            // No main cached; derive from question marks only
            updateStudentTotal(studentID);
        }
    });

    // Recalc totals only for students without an explicit cached main
    if (typeof updateTotalsFromCache === 'function') {
        if (!examQuestionData.marksByStudent) return;
        Object.keys(examQuestionData.marksByStudent).forEach(function(studentID) {
            if (studentsWithCachedMain[studentID]) return;
            const questionMarks = examQuestionData.marksByStudent[studentID] || {};
            let total = 0;
            let hasMarks = false;
            Object.keys(questionMarks).forEach(function(questionId) {
                const value = parseFloat(questionMarks[questionId]);
                if (!isNaN(value)) {
                    total += value;
                    hasMarks = true;
                }
            });
            if (hasMarks) {
                const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
                jQuery(`#marks_${studentID}`).val(displayTotal);
                autoCalculateGrade(studentID);
                saveMarkToCache(studentID, 'main', displayTotal);
                restoredCount++;
            }
        });
    }

    if (restoredCount > 0) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: i18nSub.unsaved_marks_restored,
            showConfirmButton: false,
            timer: 4000
        });
    }
}

function clearResultsCache() {
    const key = getResultsCacheKey();
    if (key) localStorage.removeItem(key);
}

// Attach Event Listeners for Caching + realtime max validation (non-qn / direct marks)
jQuery(document).on('input', '.marks-input', function() {
    const studentID = jQuery(this).data('student');
    const marks = jQuery(this).val();
    saveMarkToCache(studentID, 'main', marks);
    validateDirectMarksInput(jQuery(this));
});

jQuery(document).on('blur', '.marks-input', function() {
    validateDirectMarksInput(jQuery(this));
});

// Enter on marks: move to next editable row; on last row submit Save Results
jQuery(document).on('keydown', '#resultsForm .marks-input', function(e) {
    if (e.key !== 'Enter' && e.which !== 13) return;

    e.preventDefault();
    e.stopPropagation();

    const $current = jQuery(this);
    if ($current.prop('disabled')) return;

    validateDirectMarksInput($current);

    const $editable = jQuery('#resultsForm .marks-input').filter(function() {
        const $el = jQuery(this);
        if ($el.prop('disabled')) return false;
        if ($el.closest('tr.result-student-row').hasClass('is-search-hidden')) return false;
        // Skip readonly question-format totals (not typed marks)
        if ($el.prop('readonly') && !isDuoResultMode() && !isDirectMarksEntryMode()) return false;
        return true;
    });

    const idx = $editable.index($current);
    if (idx < 0) return;

    if (idx < $editable.length - 1) {
        const $next = $editable.eq(idx + 1);
        $next.focus().select();
        // Keep next row visible inside the scrollable modal body
        const $body = jQuery('#addEditResultsModalBody');
        if ($body.length && $next.length) {
            const nextTop = $next.closest('tr')[0]
                ? ($next.closest('tr')[0].offsetTop - 80)
                : 0;
            $body.stop(true).animate({ scrollTop: Math.max(nextTop, 0) }, 120);
        }
        return;
    }

    // Last editable row -> save
    const $form = jQuery('#resultsForm');
    if ($form.length) {
        $form.trigger('submit');
    }
});

function showPremiumLoading(targetID, title, detail) {
    title = title || i18nSub.preparing_data;
    detail = detail || i18nSub.fetching_info;
    jQuery(`#${targetID}`).html(`
        <div class="p-5 text-center">
            <div class="loading-text"><i class="bi bi-hourglass-split"></i> <span>${title}</span></div>
            <div class="percentage-text progress-percentage" id="perc-${targetID}">0%</div>
            <div class="progress-container">
                <div class="progress-bar-premium" id="prog-${targetID}" style="width: 0%"></div>
            </div>
            <small class="text-muted">${detail}</small>
        </div>
    `);

    let progress = 0;
    const interval = setInterval(() => {
        if (progress >= 40) { // Default starting point before response
            clearInterval(interval);
        } else {
            progress += 5;
            jQuery(`#prog-${targetID}`).css('width', progress + '%');
            jQuery(`#perc-${targetID}`).text(progress + '%');
        }
    }, 40);

    return {
        update: (target, text) => {
            const step = 5;
            const subInterval = setInterval(() => {
                if (progress >= target) {
                    clearInterval(subInterval);
                } else {
                    progress += step;
                    if (progress > target) progress = target;
                    jQuery(`#prog-${targetID}`).css('width', progress + '%');
                    jQuery(`#perc-${targetID}`).text(progress + '%');
                    if (text) jQuery(`#${targetID} .loading-text span`).text(text);
                }
            }, 30);
        }
    };
}

let examQuestionData = {
    questions: [],
    marksByStudent: {},
    maxTotal: 0,
    optionalTotal: 0,
    optionalRanges: []
};

jQuery(document).ready(function($) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize search functionality
    initializeSubjectSearch();
});

function showSelectedSubjectPanel(subjectId) {
    jQuery('.subject-item').removeClass('is-active');
    if (!subjectId) return;
    jQuery('.subject-item[data-subject-id="' + subjectId + '"]').addClass('is-active');
}

function initializeSubjectSearch() {
    var $select = jQuery('#subjectSelect');

    function filterSelectOptions(term) {
        term = (term || '').toLowerCase().trim();
        var firstVisible = null;
        $select.find('option').each(function() {
            var text = (jQuery(this).text() || '').toLowerCase();
            var match = !term || text.indexOf(term) !== -1;
            jQuery(this).prop('disabled', !match).toggle(match);
            if (match && firstVisible === null && jQuery(this).val()) {
                firstVisible = jQuery(this).val();
            }
        });
        if (term) {
            jQuery('#clearSearchBtn').show();
            if (firstVisible) {
                $select.val(firstVisible);
                showSelectedSubjectPanel(firstVisible);
            } else {
                showSelectedSubjectPanel(null);
                jQuery('#noResultsMessage').remove();
                jQuery('#subjectsGrid').append(
                    '<div id="noResultsMessage" class="alert alert-info text-center mt-2">' +
                    '<i class="bi bi-info-circle"></i> No subjects match "' + term + '".</div>'
                );
            }
        } else {
            jQuery('#clearSearchBtn').hide();
            jQuery('#noResultsMessage').remove();
            $select.find('option').prop('disabled', false).show();
            showSelectedSubjectPanel($select.val());
        }
    }

    $select.on('change', function() {
        showSelectedSubjectPanel(jQuery(this).val());
    });

    jQuery('#subjectSearchInput').on('keyup', function() {
        filterSelectOptions(jQuery(this).val());
    });

    jQuery('#clearSearchBtn').on('click', function() {
        jQuery('#subjectSearchInput').val('');
        filterSelectOptions('');
    });

    showSelectedSubjectPanel($select.val());
}

// Show student photo in larger view
function showStudentPhoto(photoUrl, studentName) {
    Swal.fire({
        title: studentName,
        imageUrl: photoUrl,
        imageWidth: 300,
        imageHeight: 300,
        imageAlt: 'Student Photo',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            popup: 'swal2-popup-custom'
        }
    });
}

// Calculate grade and remark from marks (NON-duo subjects only)
// Form Five / Form Six use A-Level NECTA scale; others keep O-Level scale
function isALevelClassName(className) {
    if (!className) return !!(window.currentResultIsALevel);
    const n = String(className).toLowerCase().replace(/[\s\-]+/g, '_');
    return n === 'form_five' || n === 'form_six' || n === 'form_5' || n === 'form_6'
        || n.indexOf('form_five') !== -1 || n.indexOf('form_six') !== -1;
}

function calculateGradeAndRemark(marks) {
    if (!marks || marks === '' || isNaN(marks)) {
        return { grade: '', remark: '' };
    }

    const marksNum = parseFloat(marks);
    const className = window.currentResultClassName || window.currentViewMainClass || '';

    if (isALevelClassName(className)) {
        if (marksNum >= 80) return { grade: 'A', remark: 'Excellent' };
        if (marksNum >= 70) return { grade: 'B', remark: 'Very Good' };
        if (marksNum >= 60) return { grade: 'C', remark: 'Good' };
        if (marksNum >= 50) return { grade: 'D', remark: 'Average' };
        if (marksNum >= 40) return { grade: 'E', remark: 'Satisfactory' };
        if (marksNum >= 35) return { grade: 'S', remark: 'Subsidiary' };
        return { grade: 'F', remark: 'Fail' };
    }

    if (marksNum >= 75) {
        return { grade: 'A', remark: 'Excellent' };
    } else if (marksNum >= 65) {
        return { grade: 'B', remark: 'Very Good' };
    } else if (marksNum >= 45) {
        return { grade: 'C', remark: 'Good' };
    } else if (marksNum >= 30) {
        return { grade: 'D', remark: 'Pass' };
    } else {
        return { grade: 'F', remark: 'Fail' };
    }
}

// Duo paper context: per-paper max marks, no A/B until combined formula later
window.duoPaperContext = null;

function isDuoResultMode() {
    return !!(window.duoPaperContext && window.duoPaperContext.isDuo);
}

function getResultMarksMax() {
    if (isDuoResultMode() && Number(window.duoPaperContext.maxMarks) > 0) {
        return Number(window.duoPaperContext.maxMarks);
    }
    // Configured paper / question-format total (e.g. 50) when available
    if (Number(examQuestionData.maxTotal) > 0) {
        return Number(examQuestionData.maxTotal);
    }
    // Default total when exam is not question-format / not configured
    return 100;
}

/**
 * Direct marks entry (not built from question-by-question inputs).
 * Includes primary schools and secondary allow_no_format exams.
 */
function isDirectMarksEntryMode() {
    const examID = jQuery('#exam_id').val();
    if (!examID) {
        // Before exam pick: still validate against default/configured max
        return true;
    }
    const exam = window.examDataCache && window.examDataCache[examID] ? window.examDataCache[examID] : null;
    if (exam && (exam.allow_no_format == 1 || exam.allow_no_format === true)) {
        return true;
    }
    if (typeof isSecondarySchool !== 'undefined' && !isSecondarySchool) {
        return true;
    }
    // Secondary with question format: main marks are usually readonly (sum of Qs)
    const $sample = jQuery('#resultsForm .marks-input:not([disabled]):first');
    if ($sample.length && !$sample.prop('readonly')) {
        return true;
    }
    return false;
}

function syncMarksInputsMaxAttr() {
    const max = getResultMarksMax();
    jQuery('#resultsForm .marks-input').attr('max', max);
    jQuery('#resultsForm .marks-max-hint').text('Max ' + max);
    jQuery('#resultsForm .marks-input').each(function() {
        validateDirectMarksInput(jQuery(this));
    });
}

/**
 * Realtime validation: marks must not exceed configured/default total.
 * Shows "Total is X" under the input when exceeded.
 */
function validateDirectMarksInput($input) {
    if (!$input || !$input.length) return true;
    if ($input.prop('disabled')) return true;

    const studentID = $input.data('student');
    const $row = $input.closest('tr');
    let $err = $row.find(`.marks-max-error[data-student="${studentID}"]`);
    if (!$err.length) {
        $err = jQuery(`<small class="marks-max-error" data-student="${studentID}"></small>`);
        $input.after($err);
    }

    const max = getResultMarksMax();
    $input.attr('max', max);

    // Question-format readonly totals: skip (validated via question marks)
    if ($input.prop('readonly') && !isDuoResultMode() && !isDirectMarksEntryMode()) {
        $input.removeClass('marks-over-max');
        $err.removeClass('is-visible').text('');
        return true;
    }

    const raw = $input.val();
    if (raw === '' || raw === null || raw === undefined) {
        $input.removeClass('marks-over-max');
        $err.removeClass('is-visible').text('');
        return true;
    }

    const marks = parseFloat(raw);
    if (isNaN(marks)) {
        $input.addClass('marks-over-max');
        $err.addClass('is-visible').text('Enter a valid number');
        return false;
    }

    if (marks < 0) {
        $input.addClass('marks-over-max');
        $err.addClass('is-visible').text('Marks cannot be negative');
        return false;
    }

    if (max > 0 && marks > max) {
        $input.addClass('marks-over-max');
        $err.addClass('is-visible').text('Total is ' + max);
        return false;
    }

    $input.removeClass('marks-over-max');
    $err.removeClass('is-visible').text('');
    return true;
}

function getDuoDisplayMax() {
    const paperMax = getResultMarksMax();
    if (isDuoResultMode() && Number(examQuestionData.maxTotal) > 0) {
        // Prefer paper max as the teacher-facing ceiling for duo
        return paperMax;
    }
    return Number(examQuestionData.maxTotal) > 0 ? Number(examQuestionData.maxTotal) : paperMax;
}

/** Pass threshold for duo paper live indicator (ratio of paper max). Letter grades come later from totals. */
function getDuoPassRatio() {
    return 0.40;
}

function evaluateDuoPaperScore(marks) {
    const max = Number(getResultMarksMax()) || 0;
    if (marks === null || marks === undefined || marks === '' || isNaN(Number(marks))) {
        return { state: 'empty', label: '', pct: 0, max: max };
    }
    const m = parseFloat(marks);
    const pct = max > 0 ? (m / max) * 100 : 0;
    if (m < 0 || (max > 0 && m > max)) {
        return { state: 'invalid', label: 'Total is ' + max, pct: pct, max: max };
    }
    // No paper max yet -> do not guess fail/pass
    if (max <= 0) {
        return { state: 'empty', label: '', pct: 0, max: max };
    }
    if ((m / max) >= getDuoPassRatio()) {
        return { state: 'pass', label: 'Pass', pct: pct, max: max };
    }
    return { state: 'fail', label: 'Fail', pct: pct, max: max };
}

function updateDuoMarksIndicator(studentID, marks, $row = null) {
    const $targetRow = $row || jQuery(`input[id="marks_${studentID}"], .marks-input[data-student="${studentID}"]`).closest('tr');
    const $input = $targetRow.find(`.marks-input[data-student="${studentID}"]`);
    const $wrap = $targetRow.find(`.duo-marks-status[data-student="${studentID}"]`);
    if (!$wrap.length) return;

    const $bullet = $wrap.find('.duo-marks-bullet');
    const $label = $wrap.find('.duo-marks-label');
    const evalResult = evaluateDuoPaperScore(marks);

    // Cancel any pending debounce
    const prevTimer = $wrap.data('duoBulletTimer');
    if (prevTimer) {
        clearTimeout(prevTimer);
        $wrap.removeData('duoBulletTimer');
    }

    const resetIndicator = function() {
        $input.removeClass('duo-marks-ok duo-marks-bad duo-marks-warn');
        $bullet
            .removeClass('duo-state-pass duo-state-fail duo-state-invalid is-pass is-fail is-invalid is-loading')
            .css({ backgroundColor: '#ced4da', boxShadow: '0 0 0 2px rgba(0,0,0,0.04)' });
        $label
            .removeClass('duo-state-pass duo-state-fail duo-state-invalid is-pass is-fail is-invalid')
            .css('color', '#6c757d')
            .text('');
    };

    resetIndicator();

    if (evalResult.state === 'empty') {
        return;
    }

    // Apply immediately (no delayed race that mixes Pass text with Fail colour)
    if (evalResult.state === 'pass') {
        $bullet
            .addClass('duo-state-pass')
            .css({ backgroundColor: '#28a745', boxShadow: '0 0 0 3px rgba(40,167,69,0.25)' });
        $label
            .addClass('duo-state-pass')
            .css('color', '#28a745')
            .text('Pass');
        $input.addClass('duo-marks-ok');
    } else if (evalResult.state === 'fail') {
        $bullet
            .addClass('duo-state-fail')
            .css({ backgroundColor: '#dc3545', boxShadow: '0 0 0 3px rgba(220,53,69,0.25)' });
        $label
            .addClass('duo-state-fail')
            .css('color', '#dc3545')
            .text('Fail');
        $input.addClass('duo-marks-bad');
    } else {
        $bullet
            .addClass('duo-state-invalid')
            .css({ backgroundColor: '#ffc107', boxShadow: '0 0 0 3px rgba(255,193,7,0.28)' });
        $label
            .addClass('duo-state-invalid')
            .css('color', '#b8860b')
            .text('Total is ' + evalResult.max);
        $input.addClass('duo-marks-warn');
    }
}

// Get grade cell class for styling
function getGradeCellClass(grade) {
    if (!grade || grade === '' || grade.toLowerCase() === 'incomplete') {
        return 'bg-warning text-dark';
    }
    const gradeUpper = grade.toUpperCase();
    if (gradeUpper === 'A') {
        return 'bg-success text-white';
    } else if (gradeUpper === 'S') {
        return 'bg-secondary text-white';
    } else if (gradeUpper === 'E' || gradeUpper === 'F' || gradeUpper === 'S/F') {
        return 'bg-danger text-white';
    }
    return 'bg-info text-white';
}

function resetQuestionData() {
    examQuestionData = {
        questions: [],
        marksByStudent: {},
        maxTotal: 0,
        optionalTotal: 0,
        optionalRanges: []
    };
    if (isSecondarySchool) {
        jQuery('.question-details-row').addClass('d-none').removeClass('loaded');
        jQuery('.question-detail-container').html('<div class="text-muted small">' + i18nSub.select_exam_load_qn + '</div>');
    }
    jQuery('#test_week_display_group').hide();
    jQuery('#selected_week_label').text('');
}

function loadExamQuestionData(classSubjectID, examID) {
    resetQuestionData();

    if (!examID) {
        return;
    }

    const componentID = jQuery('#component_id').val() || window.selectedComponentID || '';
    let url = `/get_exam_paper_question_data/${classSubjectID}/${examID}`;
    if (componentID) {
        url += (url.indexOf('?') >= 0 ? '&' : '?') + 'componentID=' + encodeURIComponent(componentID);
    }
    url = withAllSubclassesParam(url);

    jQuery.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            if (!response.success) {
                return;
            }

            examQuestionData.questions = response.questions || [];
            examQuestionData.marksByStudent = response.marks_by_student || {};
            examQuestionData.maxTotal = response.max_total || 0;
            examQuestionData.optionalTotal = response.optional_total || 0;
            examQuestionData.optionalRanges = response.optional_ranges || [];

            // Duo: keep paper max as the authoritative ceiling
            if (isDuoResultMode()) {
                if (response.paper_max_marks && Number(response.paper_max_marks) > 0) {
                    window.duoPaperContext.maxMarks = Number(response.paper_max_marks);
                }
            }
            // Apply configured/default max to all marks inputs + realtime validation
            syncMarksInputsMaxAttr();
            syncResultsExcelAvailability();
            if (response.test_week) {
                // Set the week value and hide the selection as it should be default from the paper
                jQuery('#test_week_group').hide();
                jQuery('#test_week_display_group').show();
                jQuery('#selected_week_label').text(response.test_week);

                const $tw = jQuery('#test_week');
                if ($tw.find(`option[value="${response.test_week}"]`).length === 0) {
                    $tw.append(`<option value="${response.test_week}">${response.test_week}</option>`);
                }
                $tw.val(response.test_week);
                // Reload existing results for this specific week
                loadExistingResults(classSubjectID, examID, response.test_week);
            } else {
                jQuery('#test_week_display_group').hide();
            }

            if (examQuestionData.questions.length === 0 && isSecondarySchool) {
                const exam = window.examDataCache[examID];
                if (exam && exam.allow_no_format == 1) {
                    jQuery('.question-detail-container').empty();
                    setTimeout(restoreMarksFromCache, 600);
                    return;
                }
                
                jQuery('.question-detail-container').html(
                    '<div class="alert alert-warning mb-0">' +
                    '<i class="bi bi-exclamation-triangle"></i> ' + (response.message || 'No approved exam paper question formats found for this subject.') +
                    '</div>'
                );
                setTimeout(restoreMarksFromCache, 600);
                return;
            }

            // Apply DB question totals first, then local cache overrides (per paper / component)
            updateTotalsFromCache();

            // Re-render any question detail rows that are already visible/loaded
            // so they show the fresh marks_by_student data
            jQuery('.question-details-row.loaded').each(function() {
                const studentID = jQuery(this).data('student');
                jQuery(this).find('.question-detail-container').html(buildQuestionDetails(studentID));
                // Restore optional-select checkbox state and enable inputs
                jQuery(this).find('.optional-select').each(function() {
                    const questionId = jQuery(this).data('question-id');
                    const hasMark = examQuestionData.marksByStudent[studentID] &&
                        examQuestionData.marksByStudent[studentID][questionId] !== undefined &&
                        examQuestionData.marksByStudent[studentID][questionId] !== null &&
                        examQuestionData.marksByStudent[studentID][questionId] !== '';
                    if (hasMark) {
                        jQuery(this).prop('checked', true);
                        const $input = jQuery(this).closest('.question-details-row')
                            .find(`.question-mark-input[data-question-id="${questionId}"]`);
                        $input.prop('disabled', false);
                    }
                });
                updateStudentTotal(studentID);
            });
            // Restore AFTER totals so cached main (e.g. 10) wins over wrong recalculation
            setTimeout(restoreMarksFromCache, 600);
        },
        error: function() {
            jQuery('.question-detail-container').html(
                '<div class="alert alert-danger mb-0">' +
                '<i class="bi bi-exclamation-triangle"></i> Failed to load question formats.' +
                '</div>'
            );
        }
    });
}

function buildQuestionDetails(studentID) {
    if (!isSecondarySchool) {
        return '';
    }

    if (!examQuestionData.questions || examQuestionData.questions.length === 0) {
        return `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle"></i> No question formats available.
            </div>
        `;
    }

    let html = '<div class="question-list">';
    examQuestionData.questions.forEach(function(question) {
        const existingMarks = examQuestionData.marksByStudent[studentID] &&
            examQuestionData.marksByStudent[studentID][question.exam_paper_questionID] !== undefined
            ? examQuestionData.marksByStudent[studentID][question.exam_paper_questionID]
            : '';
        let optionalTag = '';
        let isChecked = false;
        if (question.is_optional) {
            const rangeLabel = question.optional_range_number ? `Opt ${question.optional_range_number}` : 'Optional';
            optionalTag = `<span class="badge badge-warning ml-2">${rangeLabel}</span>`;
            // Only mark as selected if marks value is strictly > 0
            // (marks = 0 could be an orphan/stale entry from old duplicate saves)
            const savedMark = examQuestionData.marksByStudent[studentID] &&
                              examQuestionData.marksByStudent[studentID][question.exam_paper_questionID];
            const markValue = (savedMark !== undefined && savedMark !== null && savedMark !== '') ? parseFloat(savedMark) : NaN;
            isChecked = !isNaN(markValue) && markValue > 0;
        }
        html += `
            <div class="form-row align-items-end mb-2">
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Qn ${question.question_number}</label>
                </div>
                <div class="col-md-6">
                    <div class="small font-weight-bold">${question.question_description} ${optionalTag}</div>
                    <div class="small text-muted">Max: ${question.marks}</div>
                </div>
                <div class="col-md-4">
                    ${question.is_optional ? `
                        <div class="form-check mb-1">
                            <input class="form-check-input optional-select" type="checkbox"
                                   data-student="${studentID}"
                                   data-question-id="${question.exam_paper_questionID}"
                                   data-optional-range="${question.optional_range_number || 0}"
                                   ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label small">Selected</label>
                        </div>
                    ` : ''}
                    <input type="number"
                           class="form-control form-control-sm question-mark-input"
                           data-student="${studentID}"
                           data-question-id="${question.exam_paper_questionID}"
                           data-optional="${question.is_optional ? 1 : 0}"
                           data-optional-range="${question.optional_range_number || 0}"
                           data-max="${question.marks}"
                           min="0"
                           max="${question.marks}"
                           step="0.01"
                           value="${existingMarks}"
                           ${(question.is_optional && !isChecked) ? 'disabled' : ''}>
                    <div class="text-danger small question-max-warning d-none"></div>
                </div>
            </div>
        `;
    });
    html += `
        </div>
        <div class="text-muted small mt-2">
            Total: <span class="student-question-total" data-student="${studentID}">0</span> / ${getDuoDisplayMax()}
            ${isDuoResultMode() ? `<span class="badge badge-light border ml-1">Paper max ${getResultMarksMax()}</span>` : ''}
        </div>
        ${examQuestionData.optionalTotal > 0 ? `
            <div class="text-muted small">
                Optional Total: ${examQuestionData.optionalTotal}
            </div>
        ` : ''}
        <div class="text-danger small optional-total-warning d-none" data-student="${studentID}"></div>
    `;

    return html;
}

function updateTotalsFromCache() {
    if (!isSecondarySchool || !examQuestionData.marksByStudent) {
        return;
    }

    Object.keys(examQuestionData.marksByStudent).forEach(function(studentID) {
        const questionMarks = examQuestionData.marksByStudent[studentID] || {};
        let total = 0;
        let hasMarks = false;
        Object.keys(questionMarks).forEach(function(questionId) {
            const value = parseFloat(questionMarks[questionId]);
            if (!isNaN(value)) {
                total += value;
                hasMarks = true;
            }
        });

        if (hasMarks) {
            const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
            jQuery(`#marks_${studentID}`).val(displayTotal);
            autoCalculateGrade(studentID);
        }
    });
}

function updateStudentTotal(studentID) {
    if (!studentID) return;
    
    let total = 0;
    let optionalTotal = 0;
    let optionalTotalsByRange = {};
    let hasMarks = false;
    
    // Find inputs strictly for this student
    const $studentRow = jQuery(`input[id="marks_${studentID}"], .marks-input[data-student="${studentID}"]`).closest('tr');
    
    jQuery(`.question-mark-input[data-student="${studentID}"]`).each(function() {
        const value = parseFloat(jQuery(this).val());
        const isOptional = jQuery(this).data('optional') == 1;
        const rangeNumber = parseInt(jQuery(this).data('optional-range'), 10);
        if (!isNaN(value)) {
            total += value;
            if (isOptional) {
                optionalTotal += value;
                if (rangeNumber > 0) {
                    optionalTotalsByRange[rangeNumber] = (optionalTotalsByRange[rangeNumber] || 0) + value;
                }
            }
            hasMarks = true;
        }
    });

    const $optionalWarning = jQuery(`.optional-total-warning[data-student="${studentID}"]`);
    const rangeExceeded = Object.keys(optionalTotalsByRange).some(function(range) {
        const sum = optionalTotalsByRange[range];
        const rangeTotal = (examQuestionData.optionalRanges || []).find(r => r.range_number == range);
        return rangeTotal && sum > parseFloat(rangeTotal.total_marks);
    });

    if (rangeExceeded) {
        $optionalWarning.text('Optional marks exceed allowed range total.').removeClass('d-none');
    } else if (examQuestionData.optionalTotal > 0 && optionalTotal > examQuestionData.optionalTotal) {
        $optionalWarning.text('Optional marks exceed allowed total.').removeClass('d-none');
    } else {
        $optionalWarning.addClass('d-none').text('');
    }

    const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
    
    // Update UI elements only for this student
    jQuery(`.student-question-total[data-student="${studentID}"]`).text(hasMarks ? displayTotal : 0);
    const $mainMarksInput = jQuery(`#marks_${studentID}`);

    if (hasMarks) {
        // Teacher has entered question-level marks; update the total
        if (isDuoResultMode() && parseFloat(displayTotal) > getResultMarksMax()) {
            $optionalWarning.text('Paper total exceeds max marks (' + getResultMarksMax() + ').').removeClass('d-none');
        }
        $mainMarksInput.val(displayTotal);
        $mainMarksInput.attr('max', getResultMarksMax());
        // Keep local cache main total in sync (input event does not fire on .val())
        saveMarkToCache(studentID, 'main', displayTotal);
        // Pass the row to autoCalculateGrade for precision
        autoCalculateGrade(studentID, $studentRow);
    }
    // If no question marks are entered (hasMarks = false), preserve the existing
    // marks input value (could be from loadExistingResults or manual entry).
    // Do NOT clear it; that would delete marks from students who have results
    // saved in the results table but do not have question-level breakdown yet.
}

// Build one marks row: Active enrollment editable; others greyed out
function canEnterResultForStudent(student) {
    if (student.can_enter_result === false) return false;
    if (student.can_enter_result === true) return true;
    return (student.enrollment_status || 'Active') === 'Active';
}

function buildResultStudentRowsHtml(students, isSecondarySchool, questionColspan) {
    let html = '';
    const marksMax = getResultMarksMax();
    const duoMode = isDuoResultMode();
    (students || []).forEach(function(student, index) {
        const canEnter = canEnterResultForStudent(student);
        const rowClass = canEnter ? '' : 'enrollment-inactive-row';
        const statusBadge = canEnter
            ? '<span class="badge badge-success enrollment-status-badge ml-1">' + i18nSub.active + '</span>'
            : `<span class="badge badge-secondary enrollment-status-badge ml-1">${student.enrollment_status || i18nSub.inactive}</span>`;
        const disabledAttr = canEnter ? '' : 'disabled readonly';
        const marksReadonly = (!canEnter || isSecondarySchool) ? 'readonly' : '';
        const btnDisabled = canEnter ? '' : 'disabled';
        const fullName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.replace(/\s+/g, ' ').trim();
        const indexNo = student.examination_number || student.admission_number || 'N/A';
        const gender = student.gender || '';
        const sortName = fullName.toLowerCase();
        const sortIndex = String(indexNo).toLowerCase();
        const sortGender = String(gender).toLowerCase();
        const searchText = `${fullName} ${indexNo} ${gender} ${student.admission_number || ''}`.toLowerCase();
        const gradeCell = duoMode
            ? `<div class="duo-marks-status" data-student="${student.studentID}">
                    <span class="duo-marks-bullet" title="Paper score vs max"></span>
                    <span class="duo-marks-label"></span>
               </div>
               <input type="hidden" class="grade-input" name="grade[${student.studentID}]" id="grade_${student.studentID}" data-student="${student.studentID}" value="">
               <input type="hidden" class="remark-input" name="remark[${student.studentID}]" id="remark_${student.studentID}" data-student="${student.studentID}" value="">`
            : `<input type="text" class="form-control form-control-sm grade-input"
                           name="grade[${student.studentID}]"
                           id="grade_${student.studentID}"
                           data-student="${student.studentID}"
                           placeholder="A, B, C..." readonly ${disabledAttr}>`;
        const remarkCell = duoMode
            ? `<small class="text-muted">${i18nSub.no_ab_yet}<br><span style="font-size:0.7rem;">${i18nSub.paper_total_later}</span></small>`
            : `<input type="text" class="form-control form-control-sm remark-input"
                           name="remark[${student.studentID}]"
                           id="remark_${student.studentID}"
                           data-student="${student.studentID}"
                           placeholder="${i18nSub.remark}" readonly ${disabledAttr}>`;

        html += `
            <tr class="result-student-row ${rowClass}"
                data-can-enter="${canEnter ? 1 : 0}"
                data-enrollment-id="${student.enrollmentID || ''}"
                data-student-id="${student.studentID}"
                data-sort-name="${sortName.replace(/"/g, '&quot;')}"
                data-sort-index="${sortIndex.replace(/"/g, '&quot;')}"
                data-sort-gender="${sortGender.replace(/"/g, '&quot;')}"
                data-search-text="${searchText.replace(/"/g, '&quot;')}">
                <td class="result-row-num">${index + 1}</td>
                <td>${fullName}${statusBadge}</td>
                <td>${indexNo}</td>
                <td class="text-center questions-column-cell" style="${isSecondarySchool ? '' : 'display:none;'}">
                    <button type="button" class="btn btn-sm btn-outline-primary toggle-question-btn" data-student="${student.studentID}" title="${canEnter ? i18nSub.add_result_questions : i18nSub.not_editable_enrollment}" ${btnDisabled}>
                        <i class="bi bi-list-check"></i>
                    </button>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm marks-input"
                           name="marks[${student.studentID}]"
                           id="marks_${student.studentID}"
                           data-student="${student.studentID}"
                           data-can-enter="${canEnter ? 1 : 0}"
                           step="0.01" min="0" max="${marksMax}" placeholder="${canEnter ? '0.00' : '-'}"
                           oninput="autoCalculateGrade(${student.studentID})"
                           ${marksReadonly} ${disabledAttr}>
                    <small class="marks-max-hint">${String(i18nSub.max_marks).replace(':max', marksMax)}</small>
                    <small class="marks-max-error" data-student="${student.studentID}"></small>
                </td>
                <td>
                    ${gradeCell}
                </td>
                <td>
                    ${remarkCell}
                </td>
            </tr>
            <tr class="question-details-row questions-detail-row d-none ${rowClass}" id="question_details_${student.studentID}" data-student="${student.studentID}">
                <td colspan="${questionColspan}">
                    <div class="question-detail-container" data-student="${student.studentID}">
                        <div class="text-muted small">${canEnter ? i18nSub.select_exam_load_qn : i18nSub.marks_locked}</div>
                    </div>
                </td>
            </tr>
        `;
    });
    return html;
}

function handleNoActiveEnrollments(xhr) {
    const payload = xhr && xhr.responseJSON ? xhr.responseJSON : {};
    const msg = payload.error || 'Selected academic year has no Active enrollments. Result entry cannot continue.';
    sxHideModal('#addEditResultsModal');
    Swal.fire({
        title: 'No Active Enrollment',
        text: msg,
        icon: 'warning',
        confirmButtonColor: '#940000'
    });
}

// View Students
function viewStudents(classSubjectID) {
    sxShowModal('#viewStudentsModal');
    const loader = showPremiumLoading('studentsModalBody', i18nSub.loading_student_list, i18nSub.fetching_registered);

    jQuery.ajax({
        url: withAllSubclassesParam('/get_subject_students/' + classSubjectID),
        method: 'GET',
        success: function(response) {
            loader.update(100, i18nSub.formatting_student_list);
            setTimeout(() => {
            if (response.success && response.students) {
                const activeCnt = response.active_enrollment_count != null ? response.active_enrollment_count : response.students.length;
                let html = `
                    <div class="mb-3">
                        <h6 class="text-primary-custom">
                            <i class="bi bi-book"></i> ${response.class_subject.subject ? response.class_subject.subject.subject_name : i18nSub.subject}
                            <span class="badge badge-primary ml-2">${String(i18nSub.active_count).replace(':count', activeCnt)}</span>
                            <span class="badge badge-light border ml-1">${String(i18nSub.total_year).replace(':count', response.students.length)}</span>
                        </h6>
                        <small class="text-muted"><i class="bi bi-info-circle"></i> ${i18nSub.only_active_can_marks}</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>#</th>
                                    <th>${i18nSub.photo}</th>
                                    <th>${i18nSub.index_no}</th>
                                    <th>${i18nSub.name}</th>
                                    <th>${i18nSub.class}</th>
                                    <th>${i18nSub.enrollment}</th>
                                    <th>${i18nSub.gender}</th>
                                    <th>${i18nSub.date_of_birth}</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                response.students.forEach(function(student, index) {
                    // Get student photo or default based on gender
                    const baseUrl = '{{ asset("") }}';
                    let photoUrl = '';
                    if (student.photo) {
                        photoUrl = baseUrl + 'userImages/' + student.photo;
                    } else {
                        photoUrl = student.gender === 'Female'
                            ? baseUrl + 'images/female.png'
                            : baseUrl + 'images/male.png';
                    }

                    const dob = student.date_of_birth ? new Date(student.date_of_birth).toLocaleDateString() : 'N/A';
                    const studentName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                    const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';
                    const canEnter = canEnterResultForStudent(student);
                    const rowClass = canEnter ? '' : 'enrollment-inactive-row';
                    const statusBadge = canEnter
                        ? '<span class="badge badge-success enrollment-status-badge">' + i18nSub.active + '</span>'
                        : `<span class="badge badge-secondary enrollment-status-badge">${student.enrollment_status || i18nSub.inactive}</span>`;

                    // Add red alarm icon if student has health conditions
                    let healthAlarmIcon = '';
                    if ((student.is_disabled && student.is_disabled == 1) ||
                        (student.has_epilepsy && student.has_epilepsy == 1) ||
                        (student.has_allergies && student.has_allergies == 1)) {
                        healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                    }

                    html += `
                        <tr class="${rowClass}">
                            <td>${index + 1}</td>
                            <td>
                                <img src="${photoUrl}"
                                     alt="Student Photo"
                                     class="rounded-circle"
                                     style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                                     onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                                     onerror="this.src='${fallbackPhoto}'">
                            </td>
                            <td><strong>${student.examination_number || student.admission_number || 'N/A'}</strong></td>
                            <td>${studentName}${healthAlarmIcon}</td>
                            <td>${student.subclass ? (student.subclass.subclass_name || 'N/A') : 'N/A'}</td>
                            <td>${statusBadge}</td>
                            <td>${student.gender || 'N/A'}</td>
                            <td>${dob}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                jQuery('#studentsModalBody').html(html);
            } else {
                jQuery('#studentsModalBody').html('<div class="alert alert-info">' + i18nSub.no_students_found + '</div>');
            }
            }, 400); // end of setTimeout
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error_code === 'no_active_enrollments') {
                jQuery('#studentsModalBody').html(
                    '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle"></i> ' +
                    (xhr.responseJSON.error || i18nSub.only_active_can_marks) +
                    '</div>'
                );
                return;
            }
            Swal.fire({
                title: i18nSub.error,
                text: (xhr.responseJSON && xhr.responseJSON.error) || i18nSub.fail_load_students,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            sxHideModal('#viewStudentsModal');
        }
    });
}

// View Results
function viewResults(classSubjectID) {
    if (!window._bulkResultsLaunch) {
        resetTeacherBulkMode();
    }
    window._bulkResultsLaunch = false;
    // Get subject name and class from the card
    const card = jQuery(`i.view-results[onclick="viewResults(${classSubjectID})"]`).closest('.card');
    const item = card.closest('.subject-item');
    let subjectName = card.length ? card.find('.card-title').text().trim().replace(/\s*DUO\s*$/i, '').trim() : (window.currentViewSubject || 'Subject');
    let className = card.length ? card.find('strong').first().text().trim() : (window.currentViewClass || '');
    const mainClassName = (item.length && item.data('main-class')) || window.currentViewMainClass || className;
    const isDuoSubject = item.length ? String(item.data('is-duo')) === '1' : !!window.currentViewIsDuo;
    
    const teacherName = "{{ Auth::user()->name ?? 'N/A' }}";
    
    window.currentViewSubject = subjectName;
    window.currentViewClass = className;
    window.currentViewMainClass = mainClassName;
    window.currentViewTeacher = teacherName;
    window.currentViewIsDuo = isDuoSubject;
    window.currentResultClassName = mainClassName;

    sxShowModal('#viewResultsModal');
    jQuery('#viewResultsModalLabel').html(`<i class="bi bi-clipboard-check"></i> Results: ${subjectName} (${className})`);
    const loader = showPremiumLoading('resultsModalBody', "Preparing Results View...", "Fetching examination structure for this subject...");

    // First get examinations for this subject (for_view = include closed-entry exams with saved marks)
    jQuery.ajax({
        url: withAllSubclassesParam('/get_examinations_for_subject/' + classSubjectID + '?for_view=1'),
        method: 'GET',
        success: function(examResponse) {
            loader.update(100, "Finalizing interface...");
            setTimeout(() => {
            if (examResponse.success && examResponse.examinations && examResponse.examinations.length > 0) {
                const headerYearIds = (examResponse.header_academic_year_ids || []).map(Number).filter(Boolean);
                const headerYearId = examResponse.header_academic_yearID || (headerYearIds[0] || null);
                const headerYearLabel = examResponse.header_academic_year_label || '-';
                window.currentViewIsALevel = !!(examResponse.is_a_level);
                window.currentResultClassName = examResponse.class_name || mainClassName;
                window.currentViewHeaderYearId = headerYearId;
                window.currentViewHeaderYearIds = headerYearIds;

                // Prefer server list as-is for view (already scoped). Only soft-filter if year ids present on exam.
                let exams = examResponse.examinations;
                if (headerYearIds.length) {
                    const filtered = exams.filter(function(e) {
                        const ids = (e.academic_year_ids || []).map(Number).filter(Boolean);
                        if (ids.length) {
                            return ids.some(function(id) { return headerYearIds.indexOf(id) !== -1; });
                        }
                        if (e.academic_yearID) {
                            return headerYearIds.indexOf(Number(e.academic_yearID)) !== -1;
                        }
                        // Keep exams that may only be linked via saved results / missing year meta
                        return true;
                    });
                    exams = filtered.length ? filtered : exams;
                }

                window.viewModalExams = exams;
                window.viewModalAllExams = examResponse.examinations;
                // Needed for weekly/monthly test week UI when viewing (Add Results also uses this cache)
                window.examDataCache = window.examDataCache || {};
                exams.forEach(function(exam) {
                    if (exam && exam.examID) {
                        window.examDataCache[exam.examID] = exam;
                    }
                });

                let html = `
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label><i class="bi bi-calendar-event"></i> Academic Year:</label>
                            <input type="text" class="form-control" id="view_year_display" value="${headerYearLabel}" readonly
                                   style="background:#f8f9fa; font-weight:600; cursor:default;">
                        </div>
                        <div class="col-md-3">
                            <label><i class="bi bi-clock-history"></i> Term:</label>
                            <select class="form-control" id="view_term_filter" onchange="filterViewResultsOptions(${classSubjectID})">
                                <option value="">All Terms</option>
                                <option value="first_term">First Term</option>
                                <option value="second_term">Second Term</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label><i class="bi bi-file-earmark-text"></i> Examination:</label>
                            <select class="form-control" id="examSelector" onchange="handleViewResultsExamSelection(${classSubjectID}, this.value)">
                                <option value="">Select Examination</option>
                                ${generateExamOptions(exams, true)}
                            </select>
                        </div>
                         <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-block" onclick="exportResultsToPDF(${classSubjectID})">
                                <i class="bi bi-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>

                    <!-- Week selection for Weekly Tests in View Modal -->
                    <div class="mb-3" id="view_test_week_group" style="display: none;">
                        <label>Select Week:</label>
                        <select class="form-control" id="view_test_week" onchange="loadResultsForExam(${classSubjectID}, jQuery('#examSelector').val(), this.value)">
                            <option value="">All Weeks</option>
                        </select>
                    </div>
                    <div id="resultsContent">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Select an examination to view results.
                        </div>
                    </div>
                `;

                jQuery('#resultsModalBody').html(html);
            } else {
                jQuery('#resultsModalBody').html('<div class="alert alert-info">No examinations found for this subject.</div>');
            }
            }, 400); // end of setTimeout
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load examinations',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function resolveViewExamData(examID) {
    if (!examID) return null;
    if (window.examDataCache && window.examDataCache[examID]) {
        return window.examDataCache[examID];
    }
    const fromList = (window.viewModalExams || []).find(function(e) {
        return String(e.examID) === String(examID);
    });
    if (fromList) {
        window.examDataCache = window.examDataCache || {};
        window.examDataCache[examID] = fromList;
        return fromList;
    }
    return null;
}

function isWeeklyOrMonthlyTestExam(examData) {
    if (!examData || examData.exam_category !== 'test') return false;
    const t = (examData.test_type || examData.exam_type || '').toLowerCase();
    const name = (examData.exam_name || '').toLowerCase();
    if (t === 'weekly_test' || t === 'monthly_test' || t === 'weekly' || t === 'monthly') return true;
    return name.indexOf('weekly') !== -1 || name.indexOf('monthly') !== -1;
}

function isMonthlyTestExam(examData) {
    if (!examData) return false;
    const t = (examData.test_type || examData.exam_type || '').toLowerCase();
    const name = (examData.exam_name || '').toLowerCase();
    if (t === 'monthly_test' || t === 'monthly') return true;
    return name.indexOf('monthly') !== -1;
}

function handleViewResultsExamSelection(classSubjectID, examID) {
    if (!examID) {
        jQuery('#view_test_week_group').hide();
        jQuery('#resultsContent').html(
            '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Select an examination to view results.</div>'
        );
        return;
    }

    const examData = resolveViewExamData(examID);
    if (isWeeklyOrMonthlyTestExam(examData)) {
        jQuery('#view_test_week_group').show();
        const $weekSelect = jQuery('#view_test_week');
        $weekSelect.empty().append('<option value="">All Periods</option>');

        const today = new Date();
        const currentYear = today.getFullYear();

        if (isMonthlyTestExam(examData)) {
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            for (let i = 0; i < 12; i++) {
                const periodVal = `Month of ${months[i]} ${currentYear}`;
                $weekSelect.append(`<option value="${periodVal}">${periodVal}</option>`);
            }
        } else {
            const startDate = new Date(currentYear, 0, 1);
            while (startDate.getDay() !== 1) startDate.setDate(startDate.getDate() + 1);

            const monthNamesShort = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            function fmtRange(start, end) {
                const sM = monthNamesShort[start.getMonth()];
                const eM = monthNamesShort[end.getMonth()];
                const sD = start.getDate();
                const eD = end.getDate();
                const y = end.getFullYear();
                return `${sM} ${sD} to ${eM} ${eD}, ${y}`;
            }

            for (let i = 0; i < 52; i++) {
                const weekStart = new Date(startDate);
                weekStart.setDate(startDate.getDate() + (i * 7));
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                const startStr = weekStart.toISOString().split('T')[0];
                const endStr = weekEnd.toISOString().split('T')[0];
                const weekVal = `Week of ${startStr} to ${endStr}`;
                const weekLabel = fmtRange(weekStart, weekEnd);
                if (weekStart <= new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)) {
                    $weekSelect.append(`<option value="${weekVal}">${weekLabel}</option>`);
                }
            }
        }
    } else {
        jQuery('#view_test_week_group').hide();
        jQuery('#view_test_week').val('');
    }

    loadResultsForExam(classSubjectID, examID);
}

function loadResultsForExam(classSubjectID, examID, testWeek = null) {
    if (!examID) {
        jQuery('#resultsContent').html(
            '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Select an examination to view results.</div>'
        );
        return;
    }

    let url = withAllSubclassesParam(`/get_subject_results/${classSubjectID}/${examID}`);
    const data = {};
    if (testWeek) data.test_week = testWeek;

    const loader = showPremiumLoading(
        'resultsContent',
        "Loading Student Results...",
        "Fetching marks and grades for this examination..."
    );

    jQuery.ajax({
        url: url,
        method: 'GET',
        data: data,
        success: function(response) {
            loader.update(100, "Formatting student data...");
            setTimeout(() => {
            if (response.success && response.results) {
                if (response.class_name) {
                    window.currentResultClassName = response.class_name;
                    window.currentViewMainClass = response.class_name;
                }
                if (response.is_a_level !== undefined) {
                    window.currentResultIsALevel = !!response.is_a_level;
                }

                if (response.available_test_weeks && response.available_test_weeks.length && jQuery('#view_test_week_group').is(':visible')) {
                    const $weekSelect = jQuery('#view_test_week');
                    const current = $weekSelect.val() || '';
                    (response.available_test_weeks || []).forEach(function(w) {
                        if (!w) return;
                        if ($weekSelect.find('option').filter(function() { return jQuery(this).val() === w; }).length === 0) {
                            $weekSelect.append(jQuery('<option></option>').attr('value', w).text(w));
                        }
                    });
                    if (current) $weekSelect.val(current);
                }

                if (response.is_duo && response.duo_papers && response.duo_papers.length) {
                    jQuery('#resultsContent').html(buildDuoResultsViewTable(response));
                    return;
                }

                const baseUrl = '{{ asset("") }}';
                let html = `
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Student Name</th>
                                    <th>Index No.</th>
                                    <th>Examination</th>
                                    ${response.has_ca ? '<th>Exam Marks</th><th>CA</th><th>Total</th><th>Avg</th>' : '<th>Marks</th>'}
                                    <th>Grade</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody id="viewResultsTable">
                `;

                response.results.forEach(function(result, index) {
                    const student = result.student || {};
                    const studentName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');

                    let healthAlarmIcon = '';
                    if ((student.is_disabled && student.is_disabled == 1) ||
                        (student.has_epilepsy && student.has_epilepsy == 1) ||
                        (student.has_allergies && student.has_allergies == 1)) {
                        healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                    }

                    let photoUrl = '';
                    if (student.photo) {
                        photoUrl = baseUrl + 'userImages/' + student.photo;
                    } else {
                        photoUrl = student.gender === 'Female'
                            ? baseUrl + 'images/female.png'
                            : baseUrl + 'images/male.png';
                    }
                    const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';

                    const examMarksValue = result.exam_marks !== undefined ? result.exam_marks : result.marks;
                    const marks = examMarksValue !== null && examMarksValue !== '' ? examMarksValue : null;
                    const caValue = response.has_ca ? (result.ca_marks !== undefined ? result.ca_marks : 0) : null;
                    const totalValue = response.has_ca ? (result.total_marks !== undefined ? result.total_marks : marks) : null;

                    let grade = result.grade || '';
                    let remark = result.remark || '';

                    if (marks === null) {
                        grade = 'Incomplete';
                        remark = 'Incomplete';
                    }

                    const gradeClass = getGradeCellClass(grade);

                    let marksColumnHtml = '';
                    if (response.has_ca) {
                        const avgValue = result.avg_marks !== undefined ? result.avg_marks : (totalValue / 2);
                        marksColumnHtml = `
                            <td><strong>${marks !== null ? marks : '<span class="text-muted">-</span>'}</strong></td>
                            <td><span class="text-info font-weight-bold">${caValue !== null ? caValue : '-'}</span></td>
                            <td><span class="text-success font-weight-bold">${totalValue !== null ? totalValue : '-'}</span></td>
                            <td><span class="text-warning font-weight-bold" style="font-size: 1.1em">${avgValue !== null ? avgValue : '-'}</span></td>
                        `;
                    } else {
                        marksColumnHtml = `<td><strong>${marks !== null ? marks : '<span class="text-muted">-</span>'}</strong></td>`;
                    }

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <img src="${photoUrl}"
                                     alt="Student Photo"
                                     class="rounded-circle"
                                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                                     onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                                     onerror="this.src='${fallbackPhoto}'">
                            </td>
                            <td>${studentName || 'N/A'}${healthAlarmIcon}</td>
                            <td>${student.examination_number || result.examination_number || student.admission_number || 'N/A'}</td>
                            <td>
                                ${result.examination ? result.examination.exam_name : 'N/A'}
                                ${result.test_week ? `<br><small class="badge badge-info">${result.test_week}</small>` : ''}
                            </td>
                            ${marksColumnHtml}
                            <td><span class="badge ${gradeClass}" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">${grade || '<span class="text-muted">-</span>'}</span></td>
                            <td>${remark || '<span class="text-muted">-</span>'}</td>
                        </tr>
                    `;
                });

                if (!response.results.length) {
                    const colSpan = response.has_ca ? 11 : 8;
                    html += `<tr><td colspan="${colSpan}" class="text-center text-muted">No results found for this examination.</td></tr>`;
                }

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                jQuery('#resultsContent').html(html);
            } else {
                jQuery('#resultsContent').html('<div class="alert alert-info">No results found.</div>');
            }
            }, 400);
        },
        error: function(xhr) {
            jQuery('#resultsContent').html('<div class="alert alert-danger">Error loading results.</div>');
        }
    });
}

function buildDuoResultsViewTable(response) {
    const baseUrl = '{{ asset("") }}';
    const papers = response.duo_papers || [];
    const paperMaxTotal = response.paper_max_total || papers.reduce((s, p) => s + (Number(p.max_marks) || 0), 0);

    let paperHeaders = papers.map(p =>
        `<th>${p.component_name}${p.max_marks ? `<br><small>(/${p.max_marks})</small>` : ''}</th>`
    ).join('');

    let html = `
        <div class="mb-2">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> Duo total =
                student paper marks / ${paperMaxTotal} (all papers max) x 100 -> grade
            </small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="bg-primary-custom text-white">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Student Name</th>
                        <th>Index No.</th>
                        ${paperHeaders}
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (!response.results.length) {
        html += `<tr><td colspan="${6 + papers.length}" class="text-center text-muted">No results found for this examination.</td></tr>`;
    }

    response.results.forEach(function(result, index) {
        const student = result.student || {};
        const studentName = ((student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '')).trim();
        let photoUrl = student.photo
            ? baseUrl + 'userImages/' + student.photo
            : (student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png');
        const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';

        const paperMarks = result.paper_marks || {};
        let paperCells = papers.map(function(p) {
            const cid = p.componentID;
            const m = paperMarks[cid] !== undefined ? paperMarks[cid]
                : (paperMarks[String(cid)] !== undefined ? paperMarks[String(cid)] : null);
            return `<td><strong>${m !== null && m !== undefined ? m : '<span class="text-muted">-</span>'}</strong></td>`;
        }).join('');

        const totalPct = result.percentage !== null && result.percentage !== undefined
            ? result.percentage
            : null;
        const rawTotal = result.student_raw_total;
        const grade = result.grade || 'Incomplete';
        const remark = result.remark || 'Incomplete';
        const gradeClass = getGradeCellClass(grade);

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <img src="${photoUrl}" alt="Student Photo" class="rounded-circle"
                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                         onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                         onerror="this.src='${fallbackPhoto}'">
                </td>
                <td>${studentName || 'N/A'}</td>
                <td>${student.examination_number || result.examination_number || student.admission_number || 'N/A'}</td>
                ${paperCells}
                <td>
                    <strong>${totalPct !== null ? totalPct : '<span class="text-muted">-</span>'}</strong>
                    ${rawTotal !== null && rawTotal !== undefined ? `<br><small class="text-muted">${rawTotal}/${paperMaxTotal}</small>` : ''}
                </td>
                <td><span class="badge ${gradeClass}" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">${grade}</span></td>
                <td>${remark}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    return html;
}

// Helper: Get Unique Years from exams
function getUniqueYears(exams) {
    let years = [...new Set(exams.map(e => e.year_label || e.year))];
    years.sort();
    return years.map(y => `<option value="${y}">${y}</option>`).join('');
}

// Helper: Generate Exam Options
// forView=true -> show all exams (including entry closed); Add Results keeps enter_result only
function generateExamOptions(exams, forView = false) {
    return (exams || [])
        .filter(function(e) {
            if (forView) return true;
            return e.enter_result === true || e.enter_result === 1;
        })
        .map(function(e) {
            const open = e.enter_result === true || e.enter_result === 1;
            const closedTag = forView && !open ? ' (entry closed)' : '';
            return `<option value="${e.examID}" data-year="${e.year}" data-term="${e.term}">${e.exam_name} (${e.year_label || e.year})${closedTag}</option>`;
        })
        .join('');
}

// Helper: Filter Exam Selector based on Term (year locked to header)
// Exams with term all_terms / empty always remain visible for any term filter.
function examMatchesViewTerm(exam, term) {
    if (!term) return true;
    const t = (exam && exam.term) ? String(exam.term).toLowerCase() : '';
    if (!t || t === 'all_terms' || t === 'all' || t === 'both') return true;
    return t === String(term).toLowerCase();
}

function filterViewResultsOptions(classSubjectID) {
    const term = jQuery('#view_term_filter').val();
    const selector = jQuery('#examSelector');
    const exams = window.viewModalExams || [];

    let filtered = exams;
    if (term) {
        filtered = filtered.filter(function(e) { return examMatchesViewTerm(e, term); });
    }

    selector.html('<option value="">Select Examination</option>' + generateExamOptions(filtered, true));
    jQuery('#view_test_week_group').hide();
    jQuery('#resultsContent').html(
        '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Select an examination to view results.</div>'
    );
}

// PDF Export Logic (jsPDF)
function exportResultsToPDF(classSubjectID) {
    const examID = jQuery('#examSelector').val() || '';
    const testWeek = jQuery('#view_test_week').val() || '';
    
    let url = `/export_subject_results_pdf/${classSubjectID}`;
    if (examID) {
        url += `/${examID}`;
    }
    
    const params = new URLSearchParams();
    if (testWeek) params.append('test_week', testWeek);
    const queryString = params.toString();
    if (queryString) url += `?${queryString}`;

    // Show loading state
    Swal.fire({
        title: 'Generating PDF...',
        html: 'Please wait while your report is being prepared.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.blob();
        })
        .then(blob => {
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = downloadUrl;
            
            // Generate dynamic filename
            const subjectName = window.currentViewSubject ? window.currentViewSubject.replace(/\s+/g, '_') : 'Subject';
            const className = window.currentViewClass ? window.currentViewClass.replace(/\s+/g, '_') : 'Class';
            a.download = `${className}_${subjectName}_Results.pdf`;
            
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            
            Swal.fire({
                title: 'Success!',
                text: 'Your PDF has been downloaded.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Download error:', error);
            Swal.fire('Error', 'Failed to generate PDF. Please try again.', 'error');
        });
}

// Removed checkExamStatusForAddEdit function - now only checking enter_result

// Edit Results: reuse duo-aware Add Results flow
function editResults(classSubjectID) {
    const item = jQuery(`.subject-item`).has(`[onclick*="editResults(${classSubjectID})"]`).first();
    if (item.length) {
        window.currentResultClassName = item.data('main-class') || '';
        window.currentResultIsALevel = isALevelClassName(window.currentResultClassName);
    }
    addResults(classSubjectID, true);
}

function loadExistingResults(classSubjectID, examID, testWeek = null) {
    if (!examID) {
        // Clear all inputs
        jQuery('.marks-input, .grade-input, .remark-input').val('');
        return;
    }

    const data = {};
    if (testWeek) data.test_week = testWeek;
    const componentID = jQuery('#component_id').val() || window.selectedComponentID || '';
    if (componentID) data.componentID = componentID;

    jQuery.ajax({
        url: withAllSubclassesParam(`/get_subject_results/${classSubjectID}/${examID}`),
        method: 'GET',
        data: data,
        success: function(response) {
            if (response.success && response.results) {
                if (response.class_name) {
                    window.currentResultClassName = response.class_name;
                }
                if (response.is_a_level !== undefined) {
                    window.currentResultIsALevel = !!response.is_a_level;
                }

                // Clear all inputs first, scoped to the results table
                jQuery('#resultsTableBody .marks-input, #resultsTableBody .grade-input, #resultsTableBody .remark-input').val('');

                // Populate with existing results
                // Only show marks that are genuinely non-zero (> 0); zero/null means 'not entered'
                response.results.forEach(function(result) {
                    if (result.studentID) {
                        const rawMarks = parseFloat(result.marks);
                        const hasRealMarks = !isNaN(rawMarks) && rawMarks > 0;
                        if (hasRealMarks) {
                            jQuery(`#marks_${result.studentID}`).val(rawMarks);
                            autoCalculateGrade(result.studentID);
                        }
                        // If marks are 0 or null, leave the input empty (placeholder shows)
                    }
                });
            }
            // Always restore cache even if no DB results exist yet (first-time entry)
            setTimeout(restoreMarksFromCache, 600);
        },
        error: function(xhr) {
            console.error('Error loading existing results:', xhr);
        }
    });
}

// Fill random marks (direct entry + question-format secondary)
function randomMarksForMax(maxMarks) {
    maxMarks = Number(maxMarks);
    if (!(maxMarks > 0)) maxMarks = 100;
    const rand = Math.random();
    let pct;
    if (rand < 0.15) {
        pct = 0.75 + Math.random() * 0.25;
    } else if (rand < 0.40) {
        pct = 0.65 + Math.random() * 0.10;
    } else if (rand < 0.70) {
        pct = 0.45 + Math.random() * 0.20;
    } else if (rand < 0.90) {
        pct = 0.30 + Math.random() * 0.15;
    } else {
        pct = 0.05 + Math.random() * 0.25;
    }
    return Math.min(maxMarks, Math.max(0, +(maxMarks * pct).toFixed(2)));
}

function shuffleArrayCopy(arr) {
    const a = (arr || []).slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        const t = a[i];
        a[i] = a[j];
        a[j] = t;
    }
    return a;
}

function isQuestionFormatEntryMode() {
    if (typeof isSecondarySchool === 'undefined' || !isSecondarySchool) return false;
    if (isDirectMarksEntryMode()) return false;
    return !!(examQuestionData && examQuestionData.questions && examQuestionData.questions.length > 0);
}

function fillRandomQuestionFormatResults() {
    const questions = examQuestionData.questions || [];
    const ranges = examQuestionData.optionalRanges || [];
    if (!questions.length) return 0;

    let filled = 0;
    jQuery('#resultsForm .marks-input').each(function() {
        const $main = jQuery(this);
        if ($main.data('can-enter') === 0 || $main.prop('disabled')) return;
        const studentID = $main.data('student');
        if (!studentID) return;

        examQuestionData.marksByStudent[studentID] = {};

        const mandatory = [];
        const optionalByRange = {};
        questions.forEach(function(q) {
            if (q.is_optional) {
                const r = parseInt(q.optional_range_number || 0, 10) || 0;
                if (!optionalByRange[r]) optionalByRange[r] = [];
                optionalByRange[r].push(q);
            } else {
                mandatory.push(q);
            }
        });

        mandatory.forEach(function(q) {
            const max = parseFloat(q.marks) || 0;
            examQuestionData.marksByStudent[studentID][q.exam_paper_questionID] = randomMarksForMax(max);
        });

        Object.keys(optionalByRange).forEach(function(rangeNum) {
            const qs = optionalByRange[rangeNum];
            const meta = ranges.find(function(r) { return String(r.range_number) === String(rangeNum); });
            let pickCount = meta ? parseInt(meta.required_questions || 0, 10) : 0;
            if (!(pickCount > 0)) pickCount = Math.min(1, qs.length);
            pickCount = Math.min(pickCount, qs.length);

            const selected = shuffleArrayCopy(qs).slice(0, pickCount);
            selected.forEach(function(q) {
                const max = parseFloat(q.marks) || 0;
                examQuestionData.marksByStudent[studentID][q.exam_paper_questionID] = randomMarksForMax(max);
            });

            const rangeLimit = meta ? parseFloat(meta.total_marks) : 0;
            if (rangeLimit > 0 && selected.length) {
                let sum = 0;
                selected.forEach(function(q) {
                    sum += parseFloat(examQuestionData.marksByStudent[studentID][q.exam_paper_questionID]) || 0;
                });
                if (sum > rangeLimit && sum > 0) {
                    const factor = rangeLimit / sum;
                    selected.forEach(function(q) {
                        const v = (parseFloat(examQuestionData.marksByStudent[studentID][q.exam_paper_questionID]) || 0) * factor;
                        examQuestionData.marksByStudent[studentID][q.exam_paper_questionID] = +v.toFixed(2);
                    });
                }
            }
        });

        const $detail = jQuery(`.question-details-row[data-student="${studentID}"]`);
        if ($detail.hasClass('loaded')) {
            $detail.find('.question-detail-container').html(buildQuestionDetails(studentID));
            $detail.find('.optional-select').each(function() {
                const questionId = jQuery(this).data('question-id');
                const hasMark = examQuestionData.marksByStudent[studentID] &&
                    examQuestionData.marksByStudent[studentID][questionId] !== undefined &&
                    examQuestionData.marksByStudent[studentID][questionId] !== null &&
                    examQuestionData.marksByStudent[studentID][questionId] !== '';
                if (hasMark) {
                    jQuery(this).prop('checked', true);
                    $detail.find(`.question-mark-input[data-question-id="${questionId}"]`).prop('disabled', false);
                } else {
                    jQuery(this).prop('checked', false);
                    $detail.find(`.question-mark-input[data-question-id="${questionId}"]`).prop('disabled', true).val('');
                }
            });
            updateStudentTotal(studentID);
        }

        filled++;
    });

    updateTotalsFromCache();
    return filled;
}

function fillRandomDirectMarksResults() {
    const maxMarks = getResultMarksMax();
    let filled = 0;
    jQuery('#resultsForm .marks-input:not([readonly]):not([disabled])').each(function() {
        const $input = jQuery(this);
        if ($input.data('can-enter') === 0) return;
        const studentID = $input.data('student');
        const marks = randomMarksForMax(maxMarks);
        $input.val(marks.toFixed(2));
        if (typeof saveMarkToCache === 'function') {
            saveMarkToCache(studentID, 'main', marks.toFixed(2));
        }
        autoCalculateGrade(studentID);
        filled++;
    });
    if (filled === 0) {
        jQuery('#resultsForm .marks-input:not([disabled])').each(function() {
            const $input = jQuery(this);
            if ($input.data('can-enter') === 0) return;
            if ($input.prop('readonly') && !isDirectMarksEntryMode() && !isDuoResultMode()) return;
            const studentID = $input.data('student');
            const marks = randomMarksForMax(maxMarks);
            $input.val(marks.toFixed(2));
            if (typeof saveMarkToCache === 'function') {
                saveMarkToCache(studentID, 'main', marks.toFixed(2));
            }
            autoCalculateGrade(studentID);
            filled++;
        });
    }
    return filled;
}

function fillRandomPrimaryResults() {
    return fillRandomResults();
}

function fillRandomResults() {
    if (!jQuery('#exam_id').val()) {
        Swal.fire({
            title: 'Select Examination',
            text: 'Please select an examination before filling random marks.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if (!jQuery('#resultsForm .marks-input').length) {
        Swal.fire({
            title: 'No students',
            text: 'Load students first, then try Fill Random.',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
        return;
    }

    let filled = 0;
    if (isQuestionFormatEntryMode()) {
        filled = fillRandomQuestionFormatResults();
    } else if (isSecondarySchool && !isDirectMarksEntryMode() && !(examQuestionData.questions || []).length) {
        Swal.fire({
            title: 'Question format missing',
            text: 'No question formats loaded for this examination yet.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    } else {
        filled = fillRandomDirectMarksResults();
    }

    if (filled > 0) {
        Swal.fire({
            title: 'Filled',
            text: `Random marks applied for ${filled} student(s). Review then Save.`,
            icon: 'success',
            timer: 1800,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            title: 'Nothing filled',
            text: 'No editable student rows found.',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
    }
}

function autoCalculateGrade(studentID, $row = null) {
    if (!studentID) return;
    
    const $targetRow = $row || jQuery(`input[id="marks_${studentID}"], .marks-input[data-student="${studentID}"]`).closest('tr');
    const marksInput = $targetRow.find(`.marks-input[data-student="${studentID}"]`);
    const gradeInput = $targetRow.find(`.grade-input[data-student="${studentID}"]`);
    const remarkInput = $targetRow.find(`.remark-input[data-student="${studentID}"]`);

    const marks = marksInput.val();
    const marksMax = getResultMarksMax();
    marksInput.attr('max', marksMax);

    // Realtime max check for direct marks entry (non-qn format / primary / allow_no_format)
    const marksOk = validateDirectMarksInput(marksInput);

    // Duo subjects: no A/B; live pass/fail bullet vs paper max only
    if (isDuoResultMode()) {
        gradeInput.val('');
        remarkInput.val('');
        if (!marksOk && marks !== '' && marks !== null && !isNaN(marks) && parseFloat(marks) > marksMax) {
            // Keep typed value so teacher sees the error "Total is X"
            updateDuoMarksIndicator(studentID, marks, $targetRow);
            return;
        }
        updateDuoMarksIndicator(studentID, marks, $targetRow);
        return;
    }

    if (!marksOk) {
        // Over max / invalid; clear auto grade until corrected
        gradeInput.val('');
        remarkInput.val('');
        return;
    }

    if (marks && marks !== '' && !isNaN(marks)) {
        const result = calculateGradeAndRemark(marks);
        gradeInput.val(result.grade);
        remarkInput.val(result.remark);
    } else {
        gradeInput.val('');
        remarkInput.val('');
    }
}

// View duo papers + teachers on a subject card
function viewDuoPapers(payload) {
    const subject = (payload && payload.subject) ? payload.subject : 'Subject';
    const papers = (payload && payload.papers) ? payload.papers : [];
    let rows = '';
    if (!papers.length) {
        rows = '<tr><td colspan="3" class="text-center text-muted">No papers found.</td></tr>';
    } else {
        papers.forEach(function(p, i) {
            const me = p.is_me ? ' <span class="badge badge-success" style="border-radius:0;">You</span>' : '';
            rows += `<tr>
                <td>${i + 1}</td>
                <td><strong>${p.component_name || 'Paper'}</strong>${p.max_marks ? ' <small class="text-muted">(' + p.max_marks + ' marks)</small>' : ''}</td>
                <td>${p.teacher_name || 'Not assigned'}${me}</td>
            </tr>`;
        });
    }
    Swal.fire({
        title: subject + ' Papers',
        html: `<div class="table-responsive text-left">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr><th>#</th><th>Paper</th><th>Teacher</th></tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`,
        width: 560,
        confirmButtonColor: '#940000',
        confirmButtonText: 'Close'
    });
}

// Add Results: for duo subjects, pick paper BEFORE exam
function addResults(classSubjectID, isEdit = false) {
    // Card entry is always single-stream unless opened via Class Results bulk filter
    if (!window._bulkResultsLaunch) {
        resetTeacherBulkMode();
    }
    window._bulkResultsLaunch = false;
    const modalTitle = isEdit ? i18nSub.edit_result : i18nSub.add_results;
    const item = jQuery(`.subject-item`).has(`[onclick*="addResults(${classSubjectID})"], [onclick*="editResults(${classSubjectID})"]`).first();
    if (item.length) {
        window.currentResultClassName = item.data('main-class') || '';
        window.currentResultIsALevel = isALevelClassName(window.currentResultClassName);
    }

    jQuery('#addEditResultsModalLabel').html(`<i class="bi bi-${isEdit ? 'pencil' : 'plus-circle'}"></i> ${modalTitle}`);
    sxShowModal('#addEditResultsModal');

    const loader = showPremiumLoading('addEditResultsModalBody', i18nSub.preparing_data, i18nSub.fetching_info);
    window.examDataCache = {};
    resetQuestionData();
    window.selectedComponentID = null;
    window.duoPaperContext = null;

    jQuery.ajax({
        url: '/get_class_subject_papers/' + classSubjectID,
        method: 'GET',
        success: function(paperResponse) {
            loader.update(40, "Loading papers...");
            const isDuo = !!(paperResponse && paperResponse.success && paperResponse.is_duo);
            const myPapers = (paperResponse && paperResponse.my_papers) ? paperResponse.my_papers : [];

            if (isDuo && myPapers.length > 0) {
                let paperOptions = '<option value="">Select paper...</option>';
                myPapers.forEach(function(p) {
                    if (!p.componentID) return;
                    paperOptions += `<option value="${p.componentID}" data-max-marks="${p.max_marks || 0}">${p.component_name}${p.max_marks ? ' (' + p.max_marks + ' marks)' : ''}</option>`;
                });

                const paperHtml = `
                    <div class="alert alert-info">
                        <i class="bi bi-layers"></i>
                        <strong>${paperResponse.subject_name || 'Subject'}</strong> is a DUO subject.
                        Select your assigned paper before choosing an examination.
                        <br><small>Marks max = that paper's marks. No A/B here, only Pass/Fail colour vs paper total.</small>
                    </div>
                    <div class="form-group">
                        <label>Select Paper <span class="text-danger">*</span></label>
                        <select class="form-control" id="component_id_picker">
                            ${paperOptions}
                        </select>
                        <small class="text-muted d-block mt-2">Only papers assigned to you are listed.</small>
                    </div>
                    <div id="duoResultsContinueArea"></div>
                `;
                jQuery('#addEditResultsModalBody').html(paperHtml);

                jQuery('#component_id_picker').off('change').on('change', function() {
                    const componentID = jQuery(this).val();
                    window.selectedComponentID = componentID || null;
                    if (!componentID) {
                        window.duoPaperContext = null;
                        jQuery('#duoResultsContinueArea').html('');
                        return;
                    }
                    const $opt = jQuery(this).find('option:selected');
                    const paperName = $opt.text();
                    const maxMarks = parseInt($opt.data('max-marks'), 10) || 0;
                    window.duoPaperContext = {
                        isDuo: true,
                        componentID: componentID,
                        maxMarks: maxMarks > 0 ? maxMarks : 100,
                        paperName: paperName
                    };
                    loadAddResultsExamForm(classSubjectID, isEdit, componentID, paperName, '#duoResultsContinueArea');
                });
                return;
            }

            // Non-duo or no paper list; existing flow
            window.duoPaperContext = null;
            loadAddResultsExamForm(classSubjectID, isEdit, null, null, '#addEditResultsModalBody');
        },
        error: function() {
            // Fallback if papers endpoint fails
            window.duoPaperContext = null;
            loadAddResultsExamForm(classSubjectID, isEdit, null, null, '#addEditResultsModalBody');
        }
    });
}

function loadAddResultsExamForm(classSubjectID, isEdit, componentID, paperName, targetSelector) {
    const $target = jQuery(targetSelector || '#addEditResultsModalBody');
    const loader = showPremiumLoading(
        targetSelector === '#duoResultsContinueArea' ? 'duoResultsContinueArea' : 'addEditResultsModalBody',
        "Preparing Details...",
        "Fetching student lists and examinations..."
    );

    jQuery.ajax({
        url: withAllSubclassesParam('/get_subject_students/' + classSubjectID),
        method: 'GET',
        success: function(studentsResponse) {
            loader.update(70, "Fetching examinations...");
            jQuery.ajax({
                url: withAllSubclassesParam('/get_examinations_for_subject/' + classSubjectID),
                method: 'GET',
                success: function(examsResponse) {
                    loader.update(100, "Finalizing interface...");
                    if (examsResponse.class_name) {
                        window.currentResultClassName = examsResponse.class_name;
                    }
                    if (examsResponse.is_a_level !== undefined) {
                        window.currentResultIsALevel = !!examsResponse.is_a_level;
                    }

                    setTimeout(() => {
                    window.currentResultsClassSubjectID = classSubjectID;
                    const paperBanner = componentID ? `
                        <div class="alert alert-success py-2">
                            <i class="bi bi-check-circle"></i> Entering results for paper:
                            <strong>${paperName || ('#' + componentID)}</strong>
                            ${isDuoResultMode() ? ` &nbsp;|&nbsp; <strong>Max marks: ${getResultMarksMax()}</strong> <small class="text-muted">(no A/B, Pass/Fail colour only)</small>` : ''}
                        </div>` : '';

                    let html = `
                        <form id="resultsForm">
                            <input type="hidden" id="class_subject_id" value="${classSubjectID}">
                            <input type="hidden" id="component_id" name="component_id" value="${componentID || ''}">
                            ${paperBanner}
                            <div class="form-group">
                                <label>Select Examination <span class="text-danger">*</span></label>
                                <select class="form-control" id="exam_id" name="exam_id" required onchange="handleExamSelection(${classSubjectID}, this.value)">
                                    <option value="">Select Examination</option>
                    `;

                    window.examDataCache = window.examDataCache || {};

                    if (examsResponse.success && examsResponse.examinations) {
                        examsResponse.examinations.forEach(function(exam) {
                            if (exam.enter_result === true || exam.enter_result === 1) {
                                window.examDataCache[exam.examID] = exam;

                                const statusText = exam.status === 'awaiting_results' ? ' (Awaiting Results)' :
                                                  exam.status === 'ongoing' ? ' (Ongoing)' : ' (Results Available)';
                                const termClosedText = exam.is_term_closed ? ' (Term Closed - Not Editable)' : '';
                                const disabledAttr = exam.is_term_closed ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '';
                                html += `<option value="${exam.examID}" data-status="${exam.status}" data-term-closed="${exam.is_term_closed}" data-enter-result="${exam.enter_result}" ${disabledAttr}>${exam.exam_name} (${exam.year_label || exam.year})${statusText}${termClosedText}</option>`;
                            }
                        });
                    }

                    html += `
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle"></i> Only examinations with "Enter Result" enabled can be used.
                                </small>
                            </div>
                            <div id="test_week_display_group" style="display: none;" class="alert alert-info py-2 mb-3">
                                <strong><i class="bi bi-calendar-event"></i> Recording Results for:</strong>
                                <span id="selected_week_label" class="ml-1"></span>
                            </div>
                            <div class="form-group" id="test_week_group" style="display: none;">
                                <label for="test_week">Select Week <span class="text-danger">*</span></label>
                                <select class="form-control" id="test_week" name="test_week">
                                    <option value="">Select Week</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-calendar-event"></i> Results will be recorded for the selected week.
                                </small>
                            </div>
                            <div id="results-alert-container">
                                ${isSecondarySchool ? `
                                    <div class="alert alert-warning mt-3 secondary-only-alert">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                                            <div class="mb-1">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                Question-based results are enabled. Excel import/export is disabled unless this exam allows no-format entry.
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="fillRandomResults()" id="fillRandomBtnQf" aria-disabled="true">
                                                    <i class="bi bi-dice-5"></i> Fill Random
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                                <div class="alert alert-info mt-3 results-excel-alert" style="${isSecondarySchool ? 'display:none;' : ''}">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                                        <div class="mb-1">
                                            <i class="bi bi-file-earmark-excel"></i>
                                            <strong>Excel Import/Export:</strong> Choose Name / Index columns, fill Marks only. Upload auto-calculates grade/remark, then Save.
                                        </div>
                                        <div class="results-excel-actions">
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="fillRandomResults()" id="fillRandomBtn" aria-disabled="true">
                                                <i class="bi bi-dice-5"></i> Fill Random
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success js-export-results-excel" data-class-subject-id="${classSubjectID}" id="downloadExcelBtn" aria-disabled="true">
                                                <i class="bi bi-download"></i> Export Excel
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary ml-1 js-import-results-excel" data-class-subject-id="${classSubjectID}" id="uploadExcelBtn" aria-disabled="true">
                                                <i class="bi bi-upload"></i> Import Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="results-tools-bar">
                                <div class="row">
                                    <div class="col-md-5 col-sm-12 mb-2">
                                        <label for="resultsStudentSearch"><i class="bi bi-search"></i> Search student</label>
                                        <input type="text" class="form-control form-control-sm" id="resultsStudentSearch"
                                               placeholder="Name, index no, sex..." autocomplete="off">
                                    </div>
                                    <div class="col-md-4 col-sm-8 mb-2">
                                        <label for="resultsStudentSort"><i class="bi bi-sort-down"></i> Sort by</label>
                                        <select class="form-control form-control-sm" id="resultsStudentSort">
                                            <option value="index" selected>Index No. (default)</option>
                                            <option value="sex">Sex</option>
                                            <option value="alphabet">Alphabet (Name)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-4 mb-2 d-flex align-items-end">
                                        <small class="text-muted" id="resultsVisibleCount"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive mt-2">
                                <table class="table table-hover">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>${i18nSub.student_name}</th>
                                            <th>${i18nSub.index_no}</th>
                                             <th class="text-center questions-column-header" style="${isSecondarySchool ? '' : 'display:none;'}"><i class="bi bi-list-check"></i></th>
                                            <th>${i18nSub.marks}</th>
                                            <th>${i18nSub.grade}</th>
                                            <th>${i18nSub.remark}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resultsTableBody">
                    `;

                    if (studentsResponse.success && studentsResponse.students) {
                        window.resultStudentsCache = studentsResponse.students;
                        html += buildResultStudentRowsHtml(studentsResponse.students, isSecondarySchool, questionColspan);
                    } else {
                        window.resultStudentsCache = [];
                        html += `<tr><td colspan="${questionColspan}" class="text-center text-muted">No enrolled students for the selected academic year.</td></tr>`;
                    }

                    html += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${i18nSub.cancel}</button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-check-circle"></i> ${isEdit ? i18nSub.update_results : i18nSub.save_results}
                                </button>
                            </div>
                        </form>
                    `;

                    $target.html(html);
                    initResultsTableTools();
                    applyResultsSearchSort();
                    }, 400);
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load examinations',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error_code === 'no_active_enrollments') {
                handleNoActiveEnrollments(xhr);
                return;
            }
            Swal.fire({
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load students',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}


// Form submission for results
jQuery(document).on('submit', '#resultsForm', function(e) {
    e.preventDefault();

    const classSubjectID = jQuery('#class_subject_id').val();
    const examID = jQuery('#exam_id').val();

    // Check if selected exam is in a closed term
    const selectedExam = jQuery('#exam_id option:selected');
    const isTermClosed = selectedExam.data('term-closed');
    if (isTermClosed) {
        Swal.fire({
            title: 'Access Denied!',
            text: 'You cannot edit results for this examination. The term has been closed.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return false;
    }

    // Check only if form is disabled (which happens if enter_result is false)
    const formDisabled = jQuery('#resultsForm button[type="submit"]').prop('disabled');
    if (formDisabled) {
        Swal.fire({
            title: 'Access Denied!',
            text: 'You are not allowed to enter results for this examination. Result entry has been disabled.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return false;
    }

    const results = [];

    const examIDForResults = jQuery('#exam_id').val();
    const selectedExamData = window.examDataCache[examIDForResults];
    const allowNoFormat = selectedExamData && (selectedExamData.allow_no_format == 1);

    if (isSecondarySchool && !allowNoFormat) {
        if (!examQuestionData.questions || examQuestionData.questions.length === 0) {
            Swal.fire({
                title: 'Warning!',
                text: 'No question formats found for this examination.',
                icon: 'warning',
                confirmButtonColor: '#940000'
            });
            return;
        }

        let hasValidationError = false;
        let hasResults = false;
        // Check if any marks have been entered in the current form
        jQuery('#resultsForm .marks-input').each(function() {
            const marks = jQuery(this).val();
            if (marks && marks !== '') {
                hasResults = true;
                return false; // Break loop
            }
        });

        // Scope each loop to the resultsForm to avoid picking up stale DOM elements
        jQuery('#resultsForm .marks-input').each(function() {
            const studentID = jQuery(this).data('student');
            if (jQuery(this).data('can-enter') === 0 || jQuery(this).prop('disabled')) {
                return; // Skip non-Active enrollments
            }
            const questionMarks = examQuestionData.marksByStudent[studentID] || {};
            const optionalRanges = examQuestionData.optionalRanges || [];
            const hasAnyMarks = Object.keys(questionMarks).length > 0;
            const selectedOptionalCounts = {};

            jQuery(`.optional-select[data-student="${studentID}"]:checked`).each(function() {
                const rangeNumber = parseInt(jQuery(this).data('optional-range'), 10);
                if (rangeNumber > 0) {
                    selectedOptionalCounts[rangeNumber] = (selectedOptionalCounts[rangeNumber] || 0) + 1;
                }
            });

            if (!hasAnyMarks) {
                // If no question marks are cached, check if the main marks input has a value
                const manualMarks = jQuery(`#marks_${studentID}`).val();
                if (manualMarks === '' || manualMarks === null || isNaN(manualMarks)) {
                    return; // Skip student if no marks at all
                }
            }

            let total = 0;
            const questionPayload = [];
            let hasQuestionSpecificMarks = false;
            const selectedOptionalIds = {};

            // IMPORTANT: Don't just look at DOM checkboxes (they don't exist if panel is closed)
            // Look at our marks cache to see which optional questions are active
            if (examQuestionData.marksByStudent[studentID]) {
                Object.keys(examQuestionData.marksByStudent[studentID]).forEach(function(qId) {
                    selectedOptionalIds[qId] = true;
                });
            }
            // Also include anything currently checked in the DOM (for newly selected ones)
            jQuery(`.optional-select[data-student="${studentID}"]:checked`).each(function() {
                selectedOptionalIds[jQuery(this).data('question-id')] = true;
            });

            for (let i = 0; i < examQuestionData.questions.length; i++) {
                const question = examQuestionData.questions[i];
                const value = questionMarks[question.exam_paper_questionID];
                const isOptional = question.is_optional;
                const isSelectedOptional = selectedOptionalIds[question.exam_paper_questionID] === true;

                if (isOptional && !isSelectedOptional) {
                    continue;
                }

                // If Question has no marks entered, skip it (don't force it to 0 yet)
                if (value === undefined || value === null || value === '') {
                    continue;
                }

                const numericValue = parseFloat(value);
                if (isNaN(numericValue) || numericValue < 0 || numericValue > parseFloat(question.marks)) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: 'Question marks must not exceed the allowed maximum (Max: ' + question.marks + ') for student ' + studentID,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }

                total += numericValue;
                hasQuestionSpecificMarks = true;
                questionPayload.push({
                    question_id: question.exam_paper_questionID,
                    marks: numericValue
                });
            }

            // Logic decision: If we found no question marks for this student,
            // fall back to the manual main marks input (which might have been edited)
            if (!hasQuestionSpecificMarks) {
                const manualMarks = jQuery(`#marks_${studentID}`).val();
                if (manualMarks !== '' && manualMarks !== null && !isNaN(manualMarks)) {
                    total = parseFloat(manualMarks);
                } else {
                    return; // Skip student if no marks at all
                }
            } else {
                // We have question marks; recalculate and update total for UI consistency
                const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
                jQuery(`#marks_${studentID}`).val(displayTotal);
                autoCalculateGrade(studentID);
            }

            // Duo only: paper total must not exceed that paper's max marks
            if (isDuoResultMode()) {
                const paperMax = getResultMarksMax();
                if (parseFloat(total) > paperMax) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: `Total is ${paperMax}. Marks cannot exceed ${paperMax} for student ${studentID}.`,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }
            } else if (isDirectMarksEntryMode()) {
                const maxMarks = getResultMarksMax();
                if (parseFloat(total) > maxMarks) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: `Total is ${maxMarks}. Marks cannot exceed ${maxMarks} for student ${studentID}.`,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }
            }

            results.push({
                studentID: studentID,
                marks: total,
                grade: isDuoResultMode() ? null : (jQuery(`.grade-input[data-student="${studentID}"]`).val() || null),
                remark: isDuoResultMode() ? null : (jQuery(`.remark-input[data-student="${studentID}"]`).val() || null),
                question_marks: questionPayload.length > 0 ? questionPayload : null
            });
        });

        if (hasValidationError) {
            return;
        }
    } else {
        // Scope each loop to the resultsForm to avoid picking up stale DOM elements
        let duoMarksInvalid = false;
        jQuery('#resultsForm .marks-input').each(function() {
            if (jQuery(this).data('can-enter') === 0 || jQuery(this).prop('disabled')) {
                return; // Skip non-Active enrollments
            }
            const studentID = jQuery(this).data('student');
            const marks = jQuery(this).val();
            const grade = jQuery(`.grade-input[data-student="${studentID}"]`).val();
            const remark = jQuery(`.remark-input[data-student="${studentID}"]`).val();

            if (marks || grade || remark) {
                if (marks !== '' && marks !== null && !isNaN(marks)) {
                    const maxMarks = getResultMarksMax();
                    if (parseFloat(marks) > maxMarks) {
                        duoMarksInvalid = true;
                        validateDirectMarksInput(jQuery(this));
                        Swal.fire({
                            title: 'Error!',
                            text: `Total is ${maxMarks}. Marks cannot exceed ${maxMarks}.`,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                        return false;
                    }
                }
                results.push({
                    studentID: studentID,
                    marks: marks || null,
                    grade: isDuoResultMode() ? null : (grade || null),
                    remark: isDuoResultMode() ? null : (remark || null)
                });
            }
        });
        if (duoMarksInvalid) {
            return;
        }
    }

    if (results.length === 0) {
        Swal.fire({
            title: 'Warning!',
            text: 'Please enter at least one result',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save the results.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    jQuery.ajax({
        url: '/save_subject_results',
        method: 'POST',
        data: {
            class_subjectID: classSubjectID,
            examID: examID,
            componentID: jQuery('#component_id').val() || window.selectedComponentID || null,
            test_week: jQuery('#test_week').val(),
            all_subclasses: window._allSubclassesMode ? 1 : 0,
            results: JSON.stringify(results) // Stringify to bypass PHP max_input_vars limit
        },
        success: function(response) {
            clearResultsCache(); // Clear local storage on successful save
            Swal.fire({
                title: 'Success!',
                text: response.success || 'Results saved successfully!',
                icon: 'success',
                confirmButtonColor: '#940000'
            }).then(() => {
                sxHideModal('#addEditResultsModal');
            });
        },
        error: function(xhr) {
            if (xhr.status === 409) {
                Swal.fire({
                    title: 'Existing Results!',
                    text: (xhr.responseJSON && xhr.responseJSON.error) || 'Results already exist for this week. You cannot enter results twice.',
                    icon: 'warning',
                    confirmButtonColor: '#940000'
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to save results',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        }
    });
});

// Upload Excel: AJAX parse only (does not save). Bound at document level so it always works.
jQuery(document).off('submit.uploadExcel', '#uploadExcelForm').on('submit.uploadExcel', '#uploadExcelForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    loadExcelResultsIntoForm(this);
    return false;
});

jQuery(document).off('click.uploadExcel', '#btnLoadExcelIntoForm').on('click.uploadExcel', '#btnLoadExcelIntoForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    loadExcelResultsIntoForm(jQuery('#uploadExcelForm')[0]);
    return false;
});

jQuery(document).on('hidden.bs.modal', '#uploadExcelModal', function() {
    if (jQuery('#addEditResultsModal').is(':visible') || jQuery('#addEditResultsModal').hasClass('show')) {
        jQuery('body').addClass('modal-open');
    }
});

function loadExcelResultsIntoForm(formEl) {
    const classSubjectID = jQuery('#upload_class_subject_id').val() || jQuery('#class_subject_id').val();
    const examID = jQuery('#upload_exam_id').val() || jQuery('#exam_id').val();
    if (!examID || !classSubjectID) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an examination first.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    const fileInput = document.getElementById('excel_file');
    const file = fileInput && fileInput.files ? fileInput.files[0] : null;
    if (!file) {
        Swal.fire({
            title: 'Error!',
            text: 'Please choose an Excel file.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    const $btn = jQuery('#btnLoadExcelIntoForm');
    const originalBtnHtml = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

    const formData = new FormData();
    formData.append('class_subject_id', classSubjectID);
    formData.append('exam_id', examID);
    formData.append('excel_file', file);
    formData.append('_token', jQuery('meta[name="csrf-token"]').attr('content'));

    Swal.fire({
        title: 'Loading Excel...',
        text: 'Please wait while we parse your file.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    jQuery.ajax({
        url: '/parse_excel_results',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $btn.prop('disabled', false).html(originalBtnHtml);
            const rows = (response && response.rows) ? response.rows : [];
            const result = applyExcelRowsToResultsForm(rows);

            const finish = function() {
                jQuery('body').addClass('modal-open');
                showExcelImportFeedback(result);
            };

            if (jQuery('#uploadExcelModal').is(':visible') || jQuery('#uploadExcelModal').hasClass('show')) {
                jQuery('#uploadExcelModal').one('hidden.bs.modal', finish);
                sxHideModal('#uploadExcelModal');
            } else {
                finish();
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalBtnHtml);
            let errorMsg = 'Failed to parse Excel file.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.status === 419) {
                errorMsg = 'Session expired. Please refresh the page and try again.';
            } else if (xhr.status === 404) {
                errorMsg = 'Parse endpoint not found. Please refresh the page.';
            }
            Swal.fire({
                title: 'Error!',
                text: errorMsg,
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function showExcelImportFeedback(result) {
    jQuery('.excel-import-feedback').remove();
    if (!result || result.filled === 0) {
        const warn = `
            <div class="alert alert-warning excel-import-feedback mt-2" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Import:</strong> No student rows matched. Check Name / Index No. columns match the list below.
            </div>`;
        jQuery('#resultsForm .results-tools-bar').before(warn);
        Swal.fire({
            title: 'No matches',
            text: 'No student rows matched the form. Check Name / Index No. in Excel.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    let detail = `Loaded <strong>${result.filled}</strong> mark(s). Grade &amp; remark calculated automatically. <strong>Not saved yet</strong>. Review below, then click Save Results.`;
    if (result.skipped) detail += ` ${result.skipped} row(s) skipped.`;
    if (result.overMax) detail += ` <span class="text-danger">${result.overMax} over Max. Fix highlighted rows.</span>`;

    const ok = `
        <div class="alert alert-success excel-import-feedback mt-2" role="alert">
            <i class="bi bi-check-circle"></i> ${detail}
        </div>`;
    jQuery('#resultsForm .results-tools-bar').before(ok);

    const $body = jQuery('#addEditResultsModalBody');
    if ($body.length && jQuery('#resultsForm .results-tools-bar').length) {
        const top = jQuery('#resultsForm .results-tools-bar').position();
        if (top && typeof top.top === 'number') {
            $body.animate({ scrollTop: Math.max(top.top, 0) }, 300);
        }
    }

    Swal.fire({
        title: 'Loaded into form',
        html: detail,
        icon: 'success',
        confirmButtonColor: '#940000'
    });
}
// Handle exam selection - enable/disable Excel buttons
function handleExamSelection(classSubjectID, examID) {
    setResultsExcelButtonsEnabled(false);
    setFillRandomButtonsEnabled(false);
    jQuery('#test_week_group, #test_week_display_group').hide();
    jQuery('#test_week').prop('required', false).val('');
    if (!examID) {
        disableResultsForm();
        resetQuestionData();
        return;
    }

    const exam = window.examDataCache[examID];
    if (exam) {
        // Check if teacher is allowed to enter results
        const enterResult = exam.enter_result === true || exam.enter_result === 1;

        if (!enterResult) {
            disableResultsForm();
            setResultsExcelButtonsEnabled(false);
            setFillRandomButtonsEnabled(false);
            showResultsStatusError('You are not allowed to enter results for this examination. Result entry has been disabled.');
            resetQuestionData();
            return;
        }

        // If enter_result is true, enable form - no other checks
        enableResultsForm(exam);
        jQuery('.results-status-error').remove();


        // Check allow_no_format to toggle alerts and excel buttons
        if (exam.allow_no_format == 1) {
            jQuery('.secondary-only-alert').hide();
            jQuery('.questions-column-header, .questions-column-cell').hide();
            jQuery('.results-excel-alert').show();
        } else {
            if (isSecondarySchool) {
                jQuery('.secondary-only-alert').show();
                jQuery('.questions-column-header, .questions-column-cell').show();
                jQuery('.results-excel-alert').hide();
            } else {
                jQuery('.secondary-only-alert').hide();
                jQuery('.results-excel-alert').show();
            }
        }
        syncResultsExcelAvailability();

        // Load existing results after enabling form
        loadExistingResults(classSubjectID, examID);
        loadExamQuestionData(classSubjectID, examID);
    } else {
        // Fallback: try to get exam data from option attributes
        const selectedOption = jQuery(`#exam_id option[value="${examID}"]`);
        const enterResult = selectedOption.data('enter-result') === true || selectedOption.data('enter-result') === 1;

        if (!enterResult) {
            disableResultsForm();
            setResultsExcelButtonsEnabled(false);
            setFillRandomButtonsEnabled(false);
            showResultsStatusError('You are not allowed to enter results for this examination. Result entry has been disabled.');
            resetQuestionData();
            return;
        }

        enableResultsForm();
        jQuery('.results-status-error').remove();
        syncResultsExcelAvailability();
        loadExistingResults(classSubjectID, examID);
        loadExamQuestionData(classSubjectID, examID);
    }
}

// Helper function to disable form inputs
function disableResultsForm() {
    jQuery('.marks-input, .grade-input, .remark-input').prop('disabled', true).css({
        'background-color': '#e9ecef',
        'cursor': 'not-allowed',
        'color': '#dc3545'
    });
    jQuery('.question-mark-input, .toggle-question-btn').prop('disabled', true);
    jQuery('#resultsForm button[type="submit"]').prop('disabled', true);
}

// Helper function to enable form inputs
function enableResultsForm(exam = null) {
    jQuery('.marks-input, .grade-input, .remark-input').prop('disabled', false).css({
        'background-color': '',
        'cursor': '',
        'color': ''
    });
    jQuery('.grade-input, .remark-input').prop('readonly', true); // Keep readonly for grade and remark
    
    // Check allow_no_format
    const allowNoFormat = exam && (exam.allow_no_format == 1);
    
    if (isSecondarySchool && !allowNoFormat) {
        jQuery('.marks-input').prop('readonly', true);
    } else {
        jQuery('.marks-input').prop('readonly', false);
    }
    jQuery('.question-mark-input, .toggle-question-btn').prop('disabled', false);
    jQuery('#resultsForm button[type="submit"]').prop('disabled', false);
    syncMarksInputsMaxAttr();
    syncResultsExcelAvailability();
}

// Helper function to show error message
function showResultsStatusError(message) {
    // Remove existing error messages
    jQuery('.results-status-error').remove();

    // Add error message above the table
    const errorHtml = `
        <div class="alert alert-danger results-status-error mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Access Denied:</strong> ${message}
        </div>
    `;
    jQuery('#resultsTableBody').closest('.table-responsive').before(errorHtml);
}

// Toggle question details per student
jQuery(document).on('click', '.toggle-question-btn', function() {
    if (examQuestionData.questions.length === 0) {
        Swal.fire({
            title: 'No Question Formats',
            text: 'Please select an examination with approved question formats first.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }
    const studentID = jQuery(this).data('student');
    const $detailRow = jQuery(`#question_details_${studentID}`);
    if ($detailRow.length === 0) {
        return;
    }

    // Always re-render to ensure fresh data (marks, optional selections)
    $detailRow.find('.question-detail-container').html(buildQuestionDetails(studentID));
    $detailRow.addClass('loaded');
    // Question fields were already rendered with correct checkbox state and disabled state in buildQuestionDetails


    // Sync rendered input values back into examQuestionData.marksByStudent
    // ONLY for inputs that are ENABLED (i.e. selected optional questions or mandatory)
    // Disabled optional inputs must NOT be synced - they carry stale values
    if (!examQuestionData.marksByStudent[studentID]) {
        examQuestionData.marksByStudent[studentID] = {};
    }
    $detailRow.find('.question-mark-input').each(function() {
        const questionId = jQuery(this).data('question-id');
        const isDisabled = jQuery(this).prop('disabled');
        const val = jQuery(this).val();

        if (isDisabled) {
            // This optional question was NOT selected; remove it from cache
            delete examQuestionData.marksByStudent[studentID][questionId];
        } else if (val !== '' && val !== null && val !== undefined) {
            examQuestionData.marksByStudent[studentID][questionId] = val;
        }
    });

    updateStudentTotal(studentID);

    $detailRow.toggleClass('d-none');
});

jQuery(document).on('change', '.optional-select', function() {
    const $checkbox = jQuery(this);
    const studentID = $checkbox.data('student');
    const rangeNumber = parseInt($checkbox.data('optional-range'), 10);
    const questionId = $checkbox.data('question-id');
    const $input = jQuery(`.question-mark-input[data-student="${studentID}"][data-question-id="${questionId}"]`);
    const rangeMeta = (examQuestionData.optionalRanges || []).find(r => r.range_number == rangeNumber);
    const requiredCount = rangeMeta ? parseInt(rangeMeta.required_questions || 0, 10) : 0;
    const selectedCount = jQuery(`.optional-select[data-student="${studentID}"][data-optional-range="${rangeNumber}"]:checked`).length;

    if (requiredCount > 0 && selectedCount > requiredCount) {
        $checkbox.prop('checked', false);
        Swal.fire({
            title: 'Optional Limit',
            text: `Question needed to opt is ${requiredCount}. You exceed the limit.`,
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if ($checkbox.is(':checked')) {
        $input.prop('disabled', false);
    } else {
        $input.val('');
        $input.prop('disabled', true);
        if (examQuestionData.marksByStudent[studentID]) {
            delete examQuestionData.marksByStudent[studentID][questionId];
        }
    }
    updateStudentTotal(studentID);
});

// Question marks input handler
jQuery(document).on('input', '.question-mark-input', function() {
    const $input = jQuery(this);
    const max = parseFloat($input.data('max'));
    let value = parseFloat($input.val());
    const $warning = $input.siblings('.question-max-warning');
    const isOptional = $input.data('optional') == 1;
    const rangeNumber = parseInt($input.data('optional-range'), 10);

    if (!isNaN(value) && value > max) {
        value = max;
        $input.val(max);
        $warning.text(`Max ${max}`).removeClass('d-none');
    } else {
        $warning.addClass('d-none').text('');
    }

    const studentID = $input.data('student');
    const questionId = $input.data('question-id');

    if (!studentID || !questionId) return;

    if (!examQuestionData.marksByStudent[studentID]) {
        examQuestionData.marksByStudent[studentID] = {};
    }

    if (isOptional && rangeNumber > 0) {
        let optionalSum = 0;
        jQuery(`.question-mark-input[data-student="${studentID}"]`).each(function() {
            if (parseInt(jQuery(this).data('optional-range'), 10) === rangeNumber) {
                const val = parseFloat(jQuery(this).val());
                if (!isNaN(val)) {
                    optionalSum += val;
                }
            }
        });

        const rangeTotal = (examQuestionData.optionalRanges || []).find(r => r.range_number == rangeNumber);
        const rangeLimit = rangeTotal ? parseFloat(rangeTotal.total_marks) : 0;

        if (rangeLimit > 0 && optionalSum > rangeLimit) {
            const otherSum = optionalSum - (isNaN(value) ? 0 : value);
            const remaining = Math.max(rangeLimit - otherSum, 0);
            $input.val(remaining);
            value = remaining;
        }
    }

    if ($input.val() === '') {
        delete examQuestionData.marksByStudent[studentID][questionId];
        saveMarkToCache(studentID, questionId, '');
    } else {
        examQuestionData.marksByStudent[studentID][questionId] = $input.val();
        saveMarkToCache(studentID, questionId, $input.val());
    }

    updateStudentTotal(studentID);
});

// Helper function to check if in edit mode
function checkIfEditMode(examID, classSubjectID) {
    // Check if any results exist for this exam and class subject
    let hasResults = false;
    jQuery('#resultsForm .marks-input').each(function() {
        const marks = jQuery(this).val();
        if (marks && marks !== '') {
            hasResults = true;
            return false; // Break loop
        }
    });
    return hasResults;
}

// --- Results table search / sort / Excel (direct marks / no-qn format) ---

function initResultsTableTools() {
    jQuery('#resultsStudentSearch').off('input.resultsTools').on('input.resultsTools', function() {
        applyResultsSearchSort();
    });
    jQuery('#resultsStudentSort').off('change.resultsTools').on('change.resultsTools', function() {
        applyResultsSearchSort();
    });
    syncResultsExcelAvailability();
}

function syncResultsExcelAvailability() {
    const canExcel = isDirectMarksEntryMode() && !!jQuery('#exam_id').val();
    if (canExcel) {
        jQuery('.results-excel-alert').show();
        if (isSecondarySchool) {
            jQuery('.secondary-only-alert').hide();
        }
    } else if (isSecondarySchool && jQuery('#exam_id').val()) {
        const exam = window.examDataCache && window.examDataCache[jQuery('#exam_id').val()];
        if (!(exam && exam.allow_no_format == 1)) {
            jQuery('.results-excel-alert').hide();
            jQuery('.secondary-only-alert').show();
        }
    }
    setResultsExcelButtonsEnabled(canExcel);
    setFillRandomButtonsEnabled(!!jQuery('#exam_id').val() && jQuery('#resultsForm .marks-input').length > 0);
}

/** Keep Excel buttons clickable so Safari/Mac get feedback instead of a dead click. */
function setResultsExcelButtonsEnabled(enabled) {
    jQuery('#downloadExcelBtn, #uploadExcelBtn').each(function() {
        const $b = jQuery(this);
        // Never use native disabled / Bootstrap .disabled, both swallow clicks on Safari/macOS.
        $b.prop('disabled', false);
        $b.removeClass('disabled');
        $b.attr('aria-disabled', enabled ? 'false' : 'true');
        $b.toggleClass('btn-excel-locked', !enabled);
        $b.css({
            opacity: enabled ? '' : '0.65',
            cursor: enabled ? '' : 'not-allowed',
            pointerEvents: 'auto'
        });
    });
}

function setFillRandomButtonsEnabled(enabled) {
    jQuery('#fillRandomBtn, #fillRandomBtnQf').each(function() {
        const $b = jQuery(this);
        $b.prop('disabled', false);
        $b.removeClass('disabled');
        $b.attr('aria-disabled', enabled ? 'false' : 'true');
        $b.toggleClass('btn-excel-locked', !enabled);
        $b.css({
            opacity: enabled ? '' : '0.65',
            cursor: enabled ? '' : 'not-allowed',
            pointerEvents: 'auto'
        });
    });
}

function getResultsSortLabel(sortBy) {
    if (sortBy === 'sex') return 'Sex';
    if (sortBy === 'alphabet') return 'Alphabet (Name)';
    return 'Index No.';
}

function genderSortRank(gender) {
    const g = String(gender || '').toLowerCase();
    if (g.indexOf('f') === 0 || g.indexOf('k') === 0) return 1; // Female / Kike first
    if (g.indexOf('m') === 0) return 2;
    return 3;
}

function naturalIndexCompare(a, b) {
    const sa = String(a || '');
    const sb = String(b || '');
    return sa.localeCompare(sb, undefined, { numeric: true, sensitivity: 'base' });
}

function applyResultsSearchSort() {
    const $tbody = jQuery('#resultsTableBody');
    if (!$tbody.length) return;

    const q = String(jQuery('#resultsStudentSearch').val() || '').trim().toLowerCase();
    const sortBy = jQuery('#resultsStudentSort').val() || 'index';

    const pairs = [];
    $tbody.children('tr.result-student-row').each(function() {
        const $main = jQuery(this);
        const sid = $main.data('student-id');
        const $detail = $tbody.find(`#question_details_${sid}`);
        pairs.push({
            $main: $main,
            $detail: $detail,
            name: String($main.data('sort-name') || ''),
            index: String($main.data('sort-index') || ''),
            gender: String($main.data('sort-gender') || ''),
            search: String($main.data('search-text') || '')
        });
    });

    pairs.sort(function(a, b) {
        if (sortBy === 'alphabet') {
            return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
        }
        if (sortBy === 'sex') {
            const gr = genderSortRank(a.gender) - genderSortRank(b.gender);
            if (gr !== 0) return gr;
            return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
        }
        // default: index no
        const idx = naturalIndexCompare(a.index, b.index);
        if (idx !== 0) return idx;
        return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
    });

    let visible = 0;
    pairs.forEach(function(pair, i) {
        const match = !q || pair.search.indexOf(q) !== -1;
        pair.$main.toggleClass('is-search-hidden', !match);
        if (pair.$detail.length) {
            pair.$detail.toggleClass('is-search-hidden', !match);
        }
        $tbody.append(pair.$main);
        if (pair.$detail.length) {
            $tbody.append(pair.$detail);
        }
        if (match) {
            visible++;
            pair.$main.find('.result-row-num').text(visible);
        }
    });

    const total = pairs.length;
    const sortLabel = getResultsSortLabel(sortBy);
    jQuery('#resultsVisibleCount').text(
        q ? `${visible} of ${total} Â· sorted by ${sortLabel}` : `${total} student(s) Â· sorted by ${sortLabel}`
    );
}

function excelRowGet(row, keys) {
    if (!row) return '';
    const map = {};
    Object.keys(row).forEach(function(k) {
        map[String(k).trim().toLowerCase()] = row[k];
    });
    for (let i = 0; i < keys.length; i++) {
        const key = keys[i].toLowerCase();
        if (map[key] !== undefined && map[key] !== null && map[key] !== '') {
            return map[key];
        }
    }
    return '';
}

function normalizeExcelMatchText(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();
}

function buildResultsStudentLookup() {
    const byId = {};
    const byIndex = {};
    const byName = {};
    jQuery('#resultsForm .result-student-row').each(function() {
        const $row = jQuery(this);
        const sid = String($row.data('student-id') || '');
        if (!sid) return;
        const name = normalizeExcelMatchText($row.data('sort-name') || $row.children('td').eq(1).clone().children().remove().end().text());
        const indexNo = normalizeExcelMatchText($row.data('sort-index') || $row.children('td').eq(2).text());
        byId[sid] = sid;
        if (indexNo && indexNo !== 'n/a') {
            if (!byIndex[indexNo]) byIndex[indexNo] = [];
            byIndex[indexNo].push(sid);
        }
        if (name) {
            if (!byName[name]) byName[name] = [];
            byName[name].push(sid);
        }
    });
    return { byId: byId, byIndex: byIndex, byName: byName };
}

function resolveStudentIdFromExcelRow(row, lookup) {
    // Support AJAX-normalized rows + raw Excel header maps
    const studentID = excelRowGet(row, ['student_id', 'Student ID', 'studentID', 'ID']);
    if (studentID !== '' && studentID !== null && studentID !== undefined) {
        const sid = String(studentID).trim();
        if (lookup.byId[sid]) return sid;
    }

    const indexNo = normalizeExcelMatchText(excelRowGet(row, [
        'index_no', 'Index No.', 'Index No', 'Index Number', 'Index',
        'examination_number', 'Admission Number', 'Admission No.'
    ]));
    const name = normalizeExcelMatchText(excelRowGet(row, [
        'student_name', 'Student Name', 'Name', 'Full Name'
    ]));

    // Prefer exact name + index when both present
    if (name && indexNo) {
        const nameHits = lookup.byName[name] || [];
        const indexHits = lookup.byIndex[indexNo] || [];
        const both = nameHits.filter(function(id) { return indexHits.indexOf(id) !== -1; });
        if (both.length === 1) return both[0];
        if (indexHits.length === 1) return indexHits[0];
        if (nameHits.length === 1) return nameHits[0];
        return null;
    }

    if (indexNo) {
        const hits = lookup.byIndex[indexNo] || [];
        return hits.length === 1 ? hits[0] : null;
    }

    if (name) {
        const hits = lookup.byName[name] || [];
        return hits.length === 1 ? hits[0] : null;
    }

    return null;
}

function applyExcelRowsToResultsForm(rows) {
    const maxMarks = getResultMarksMax();
    const lookup = buildResultsStudentLookup();
    let filled = 0;
    let skipped = 0;
    let overMax = 0;

    (rows || []).forEach(function(row) {
        const sid = resolveStudentIdFromExcelRow(row, lookup);
        if (!sid) {
            skipped++;
            return;
        }

        const $marks = jQuery(`#resultsForm .marks-input[data-student="${sid}"]`);
        if (!$marks.length || $marks.prop('disabled')) {
            skipped++;
            return;
        }

        let marks = excelRowGet(row, ['marks', 'Marks', 'Score', 'Total']);
        if (marks === '' || marks === null || marks === undefined) {
            skipped++;
            return;
        }
        marks = String(marks).trim();
        if (marks === '' || isNaN(parseFloat(marks))) {
            skipped++;
            return;
        }

        const marksNum = parseFloat(marks);
        $marks.val(marksNum);
        // Always auto-calculate grade & remark from marks (same as typing by hand)
        autoCalculateGrade(sid);

        if (maxMarks > 0 && marksNum > maxMarks) {
            overMax++;
            validateDirectMarksInput($marks);
        }
        filled++;
    });

    return { filled: filled, skipped: skipped, overMax: overMax };
}

function getExportColumnsChoiceLabel(mode) {
    if (mode === 'name') return 'Name only';
    if (mode === 'index') return 'Index No. only';
    return 'Name and Index No.';
}

/**
 * Safari / macOS often block XLSX.writeFile after async dialogs.
 * Blob + temporary <a download> keeps the download reliable.
 */
function downloadWorkbookAsXlsx(wb, filename) {
    if (typeof XLSX === 'undefined') {
        throw new Error('Excel library failed to load');
    }
    const safeName = String(filename || 'Results.xlsx').replace(/[\\/:*?"<>|]+/g, '_');
    const out = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
    const blob = new Blob([out], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = safeName;
    a.style.display = 'none';
    a.rel = 'noopener';
    document.body.appendChild(a);
    a.click();
    setTimeout(function() {
        if (a.parentNode) a.parentNode.removeChild(a);
        URL.revokeObjectURL(url);
    }, 2000);
    return true;
}

function writeResultsExcelFile(classSubjectID, exportMode) {
    applyResultsSearchSort();

    const maxMarks = getResultMarksMax();
    const sortBy = jQuery('#resultsStudentSort').val() || 'index';
    const sortLabel = getResultsSortLabel(sortBy);
    const examName = jQuery('#exam_id option:selected').text() || 'Exam';
    const modeLabel = getExportColumnsChoiceLabel(exportMode);

    const header = [];
    if (exportMode === 'name' || exportMode === 'both') header.push('Student Name');
    if (exportMode === 'index' || exportMode === 'both') header.push('Index No.');
    header.push('Marks');

    const aoa = [header];
    let exported = 0;

    jQuery('#resultsTableBody tr.result-student-row').each(function() {
        const $row = jQuery(this);
        if ($row.hasClass('is-search-hidden')) return;
        const name = $row.children('td').eq(1).clone().children().remove().end().text().trim();
        const indexNo = $row.children('td').eq(2).text().trim();
        const marks = $row.find('.marks-input').val() || '';
        const rowData = [];
        if (exportMode === 'name' || exportMode === 'both') rowData.push(name);
        if (exportMode === 'index' || exportMode === 'both') rowData.push(indexNo);
        rowData.push(marks);
        aoa.push(rowData);
        exported++;
    });

    if (exported === 0) {
        throw new Error('No students to export (check search filter).');
    }

    const ws = XLSX.utils.aoa_to_sheet(aoa);
    ws['!cols'] = header.map(function(h) {
        if (h === 'Student Name') return { wch: 28 };
        if (h === 'Index No.') return { wch: 16 };
        return { wch: 10 };
    });
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Results');

    const meta = XLSX.utils.aoa_to_sheet([
        ['Export columns', modeLabel + ' + Marks'],
        ['Exported sort', sortLabel],
        ['Max marks (for validation)', maxMarks],
        ['Examination', examName],
        ['Class subject ID', classSubjectID],
        ['Rows', exported],
        ['Note', 'Fill Marks only. On import, grade and remark are calculated automatically.']
    ]);
    XLSX.utils.book_append_sheet(wb, meta, 'Export Info');

    const safeMode = String(exportMode).replace(/[^a-z0-9_-]/gi, '');
    const safeSort = String(sortBy).replace(/[^a-z0-9_-]/gi, '');
    downloadWorkbookAsXlsx(wb, 'Results_' + safeMode + '_sort-' + safeSort + '.xlsx');

    return { exported: exported, modeLabel: modeLabel, sortLabel: sortLabel };
}

// Download Excel Template: ask columns first (name / index / both) + Marks only
function downloadExcelTemplate(classSubjectID) {
    classSubjectID = classSubjectID || window.currentResultsClassSubjectID;
    const examID = jQuery('#exam_id').val();

    if (!examID) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an examination first.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if (!isDirectMarksEntryMode()) {
        Swal.fire({
            title: 'Not available',
            text: 'Excel export is only for exams without question format.',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if (typeof XLSX === 'undefined') {
        Swal.fire({
            title: 'Error!',
            text: 'Excel library failed to load. Please refresh the page.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    Swal.fire({
        title: 'Export with',
        html: `
            <p class="mb-2 text-left" style="font-size:0.9rem;">Choose student columns. <strong>Marks</strong> is always included.</p>
            <div class="text-left" style="line-height:1.9;">
                <label class="d-block"><input type="radio" name="export_cols_mode" value="name"> Name only + Marks</label>
                <label class="d-block"><input type="radio" name="export_cols_mode" value="index"> Index No. only + Marks</label>
                <label class="d-block"><input type="radio" name="export_cols_mode" value="both" checked> Name + Index No. + Marks</label>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Export',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#940000',
        // Run download here (still in confirm-click gesture), required for Safari/macOS
        preConfirm: function() {
            const selected = document.querySelector('.swal2-container input[name="export_cols_mode"]:checked')
                || document.querySelector('input[name="export_cols_mode"]:checked');
            const mode = selected ? selected.value : 'both';
            try {
                return writeResultsExcelFile(classSubjectID, mode);
            } catch (err) {
                Swal.showValidationMessage((err && err.message) ? err.message : 'Export failed');
                return false;
            }
        }
    }).then(function(result) {
        if (!result.isConfirmed || !result.value) return;
        const info = result.value;
        Swal.fire({
            title: 'Exported',
            text: info.exported + ' student(s) · ' + info.modeLabel + ' + Marks · sorted by ' + info.sortLabel,
            icon: 'success',
            timer: 2400,
            showConfirmButton: false
        });
    });
}

window.downloadExcelTemplate = downloadExcelTemplate;
window.writeResultsExcelFile = writeResultsExcelFile;
window.downloadWorkbookAsXlsx = downloadWorkbookAsXlsx;

// Event delegation (more reliable than inline onclick on Mac/Safari)
jQuery(document).off('click.resultsExcelExport').on('click.resultsExcelExport', '#downloadExcelBtn, .js-export-results-excel', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = jQuery(this);
    const locked = $btn.attr('aria-disabled') === 'true' || $btn.hasClass('btn-excel-locked');
    if (locked) {
        if (!jQuery('#exam_id').val()) {
            Swal.fire({ title: 'Select exam', text: 'Please select an examination first.', icon: 'info', confirmButtonColor: '#940000' });
        } else if (!isDirectMarksEntryMode()) {
            Swal.fire({ title: 'Not available', text: 'Excel export is only for exams without question format.', icon: 'info', confirmButtonColor: '#940000' });
        } else {
            Swal.fire({ title: 'Not available', text: 'Excel export is not available for this examination yet.', icon: 'info', confirmButtonColor: '#940000' });
        }
        return;
    }
    const classSubjectID = $btn.data('class-subject-id') || window.currentResultsClassSubjectID;
    downloadExcelTemplate(classSubjectID);
});

jQuery(document).off('click.resultsExcelImport').on('click.resultsExcelImport', '#uploadExcelBtn, .js-import-results-excel', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = jQuery(this);
    const locked = $btn.attr('aria-disabled') === 'true' || $btn.hasClass('btn-excel-locked');
    if (locked) {
        if (!jQuery('#exam_id').val()) {
            Swal.fire({ title: 'Select exam', text: 'Please select an examination first.', icon: 'info', confirmButtonColor: '#940000' });
        } else if (!isDirectMarksEntryMode()) {
            Swal.fire({ title: 'Not available', text: 'Excel import is only for exams without question format.', icon: 'info', confirmButtonColor: '#940000' });
        } else {
            Swal.fire({ title: 'Not available', text: 'Excel import is not available for this examination yet.', icon: 'info', confirmButtonColor: '#940000' });
        }
        return;
    }
    const classSubjectID = $btn.data('class-subject-id') || window.currentResultsClassSubjectID;
    uploadExcelResults(classSubjectID);
});

// Show Upload Excel Modal
function uploadExcelResults(classSubjectID) {
    showUploadExcelModal(classSubjectID || window.currentResultsClassSubjectID);
}

function showUploadExcelModal(classSubjectID) {
    const examID = jQuery('#exam_id').val();

    if (!examID) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an examination first.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if (!isDirectMarksEntryMode()) {
        Swal.fire({
            title: 'Not available',
            text: 'Excel import is only for exams without question format.',
            icon: 'info',
            confirmButtonColor: '#940000'
        });
        return;
    }

    jQuery('#upload_class_subject_id').val(classSubjectID);
    jQuery('#upload_exam_id').val(examID);
    jQuery('#excel_file').val('');
    jQuery('#excel_file_label').html('<i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)');
    sxShowModal('#uploadExcelModal');

    // Update file label when file is selected
    jQuery('#excel_file').off('change.excelLabel').on('change.excelLabel', function() {
        var fileName = jQuery(this).val().split('\\').pop();
        if (fileName) {
            jQuery('#excel_file_label').html('<i class="bi bi-file-earmark-excel-fill"></i> ' + fileName);
        } else {
            jQuery('#excel_file_label').html('<i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)');
        }
    });
}

// View Session Attendance
function viewSessionAttendance(classSubjectID) {
    sxShowModal('#sessionAttendanceModal');
    const loader = showPremiumLoading('sessionAttendanceModalBody', "Loading Session Attendance...", "Fetching attendance records for this subject...");

    // Load session attendance view
    jQuery.get(`/teacher/session-attendance/${classSubjectID}`)
    .done(function(response) {
        loader.update(100, "Displaying records...");
        setTimeout(() => {
            jQuery('#sessionAttendanceModalBody').html(response);
        }, 400);
    })
    .fail(function(xhr) {
        jQuery('#sessionAttendanceModalBody').html('<div class="alert alert-danger">Failed to load session attendance. Please try again.</div>');
    });
}

// View Exam Attendance
function viewExamAttendance(classSubjectID, subjectID) {
    sxShowModal('#examAttendanceModal');
    const loader = showPremiumLoading('examAttendanceModalBody', "Loading Exam Attendance...", "Identifying student participation in examinations...");

    // Load exam attendance view
    jQuery.get(`/teacher/exam-attendance/${classSubjectID}`, {
        subjectID: subjectID
    })
    .done(function(response) {
        loader.update(100, "Compiling attendance list...");
        setTimeout(() => {
            jQuery('#examAttendanceModalBody').html(response);
        }, 400);
    })
    .fail(function(xhr) {
        jQuery('#examAttendanceModalBody').html('<div class="alert alert-danger">Failed to load exam attendance. Please try again.</div>');
    });
}
// Helper function to generate weeks for the current year
// Helper function to generate periods (weeks/months) for the current year
function generatePeriods(examID, classSubjectID, testType) {
    const $weekSelect = jQuery('#test_week');
    $weekSelect.empty().append('<option value="">Select Period</option>');

    const today = new Date();
    const currentYear = today.getFullYear();
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    function formatDateRange(start, end) {
        const startMonth = monthNames[start.getMonth()];
        const endMonth = monthNames[end.getMonth()];
        const startDay = start.getDate();
        const endDay = end.getDate();
        const year = end.getFullYear();

        return `${startMonth} ${startDay} to ${endMonth} ${endDay}, ${year}`;
    }

    if (testType === 'monthly_test') {
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        for (let i = 0; i < 12; i++) {
            const periodVal = `Month of ${months[i]} ${currentYear}`;
            $weekSelect.append(`<option value="${periodVal}">${periodVal}</option>`);
            // Auto-select current month
            if (i === today.getMonth()) {
                $weekSelect.val(periodVal);
            }
        }
    } else {
        // Find current week Monday
        const curr = new Date(today);
        const day = curr.getDay(); // 0 is Sunday, 1 is Monday...
        const diff = curr.getDate() - day + (day == 0 ? -6 : 1);
        const monday = new Date(curr.setDate(diff));
        monday.setHours(0,0,0,0);

        // Previous Week (Optional, but user said currently and one ahead)
        // Let's add current and next week as requested
        for (let i = 0; i < 2; i++) {
            const weekStart = new Date(monday);
            weekStart.setDate(monday.getDate() + (i * 7));
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);

            const startStr = weekStart.toISOString().split('T')[0];
            const endStr = weekEnd.toISOString().split('T')[0];
            const weekVal = `Week of ${startStr} to ${endStr}`;
            const weekLabel = formatDateRange(weekStart, weekEnd) + (i === 0 ? " (Current Week)" : " (Next Week)");

            $weekSelect.append(`<option value="${weekVal}">${weekLabel}</option>`);
            if (i === 0) {
                $weekSelect.val(weekVal);
            }
        }
    }

    // Add listener for period change
    $weekSelect.off('change').on('change', function() {
        if (jQuery(this).val()) {
            loadExistingResults(classSubjectID, examID, jQuery(this).val());
        } else {
            jQuery('.marks-input, .grade-input, .remark-input').val('');
        }
    });

    if ($weekSelect.val()) {
        $weekSelect.trigger('change');
    }
}

// Function to open Election Modal
function openElectionModal(classSubjectID, subjectName, subclassID) {
    if (!subclassID || subclassID === 0) {
        Swal.fire('Error', 'This subject is assigned to the entire class. Election is only supported for specific subclasses.', 'warning');
        return;
    }

    jQuery('#electionSubjectTitle').text(String(i18nSub.election_title).replace(':name', subjectName));
    jQuery('#electionStudentsContainer').html(
        '<div class="text-center py-4">' +
        '<div class="spinner-border text-primary-custom" role="status">' +
        '<span class="visually-hidden">' + i18nSub.loading + '</span>' +
        '</div>' +
        '<p class="mt-2">' + i18nSub.loading_students + '</p>' +
        '</div>'
    );

    jQuery('#saveElectionBtn').data('class-subject-id', classSubjectID);
    jQuery('#saveElectionBtn').data('subclass-id', subclassID);

    sxShowModal('#subjectElectionModal');

    // Load students
    loadStudentsForElection(classSubjectID, subclassID);
}

function loadStudentsForElection(classSubjectID, subclassID) {
    jQuery.ajax({
        url: "{{ url('get_subclass_students') }}/" + subclassID,
        type: "GET",
        dataType: 'json',
        success: function(response) {
            if (response.success && response.students && response.students.length > 0) {
                // Fetch already elected
                jQuery.ajax({
                    url: "{{ url('get_subject_electors') }}/" + classSubjectID,
                    type: "GET",
                    dataType: 'json',
                    success: function(electorsResponse) {
                        var electedStudentIDs = [];
                        if (electorsResponse.success && electorsResponse.electors) {
                            electedStudentIDs = electorsResponse.electors.map(function(e) {
                                return e.studentID;
                            });
                        }

                        var html = '<div class="table-responsive">';
                        html += '<table class="table table-hover table-bordered" id="electionStudentsTable">';
                        html += '<thead class="bg-primary-custom text-white">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>' + i18nSub.student_name + '</th>';
                        html += '<th>' + i18nSub.index_no + '</th>';
                        html += '<th style="text-align: center;">' + i18nSub.election + '</th>';
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
                            html += '<td>' + student.first_name + ' ' + (student.middle_name ? student.middle_name + ' ' : '') + student.last_name + '</td>';
                            html += '<td>' + (student.examination_number || student.admission_number || 'N/A') + '</td>';
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

                        html += '</tbody></table></div>';
                        if (jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
                            jQuery('#electionStudentsTable').DataTable().destroy();
                        }
                        jQuery('#electionStudentsContainer').html(html);

                        if (jQuery.fn.DataTable) {
                            jQuery('#electionStudentsTable').DataTable({
                                "pageLength": 25,
                                "order": [[1, "asc"]],
                                "deferRender": false
                            });
                        }
                    },
                    error: function() {
                        jQuery('#electionStudentsContainer').html('<div class="alert alert-danger">Error fetching electors data.</div>');
                    }
                });
            } else {
                jQuery('#electionStudentsContainer').html('<div class="alert alert-info">' + i18nSub.no_students_subclass + '</div>');
            }
        },
        error: function() {
            jQuery('#electionStudentsContainer').html('<div class="alert alert-danger">Failed to load students.</div>');
        }
    });
}

// Select All Handler
jQuery(document).on('click', '#electionSelectAllBtn', function() {
    if (typeof jQuery.fn.DataTable !== 'undefined' && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
        var table = jQuery('#electionStudentsTable').DataTable();
        table.rows({ page: 'all' }).every(function() {
            jQuery(this.node()).find('.election-checkbox').prop('checked', true);
        });
    } else {
        jQuery('#electionStudentsTable .election-checkbox').prop('checked', true);
    }

    Swal.fire({
        icon: 'success',
        title: 'Selected All',
        timer: 1000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
    });
});

// Deselect All Handler
jQuery(document).on('click', '#electionDeselectAllBtn', function() {
    if (typeof jQuery.fn.DataTable !== 'undefined' && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
        var table = jQuery('#electionStudentsTable').DataTable();
        table.rows({ page: 'all' }).every(function() {
            jQuery(this.node()).find('.election-checkbox').prop('checked', false);
        });
    } else {
        jQuery('#electionStudentsTable .election-checkbox').prop('checked', false);
    }

    Swal.fire({
        icon: 'info',
        title: 'Deselected All',
        timer: 1000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
    });
});

// Save Election Handler
jQuery(document).on('click', '#saveElectionBtn', function() {
    var classSubjectID = jQuery(this).data('class-subject-id');
    var subclassID = jQuery(this).data('subclass-id');
    var selectedStudents = [];
    var seen = {};

    function pushStudentId(rawId) {
        if (rawId === undefined || rawId === null || rawId === '') return;
        var id = String(rawId);
        if (seen[id]) return;
        seen[id] = true;
        selectedStudents.push(id);
    }

    // Collect checked boxes across ALL DataTable pages (not only the visible page)
    if (typeof jQuery.fn.DataTable !== 'undefined' && jQuery.fn.DataTable.isDataTable('#electionStudentsTable')) {
        var table = jQuery('#electionStudentsTable').DataTable();
        table.rows({ page: 'all' }).every(function() {
            var $row = jQuery(this.node());
            var $checkbox = $row.find('.election-checkbox');
            if ($checkbox.length && $checkbox.is(':checked')) {
                pushStudentId($checkbox.val() || $checkbox.data('student-id') || $row.data('student-id'));
            }
        });
    } else {
        jQuery('#electionStudentsTable .election-checkbox:checked').each(function() {
            pushStudentId(jQuery(this).val() || jQuery(this).data('student-id'));
        });
    }

    var $btn = jQuery(this);
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

    jQuery.ajax({
        url: "{{ route('save_subject_election') }}",
        type: "POST",
        data: {
            classSubjectID: classSubjectID,
            subclassID: subclassID,
            selectedStudents: selectedStudents,
            _token: jQuery('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $btn.prop('disabled', false).html(originalText);
            if (response.success) {
                sxHideModal('#subjectElectionModal');
                Swal.fire('Success', response.success, 'success').then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalText);
            var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message))
                ? (xhr.responseJSON.error || xhr.responseJSON.message)
                : 'Failed to save election.';
            Swal.fire('Error', msg, 'error');
        }
    });
});

// Legacy individual deselect button (if any remain in DOM)
jQuery(document).on('click', '.deselect-student-btn', function() {
    var $btn = jQuery(this);
    var studentID = $btn.data('student-id');
    var classSubjectID = $btn.data('class-subject-id');
    var studentName = $btn.data('student-name');

    Swal.fire({
        title: 'Deselect Student?',
        text: 'Remove ' + studentName + ' from this subject?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, remove!'
    }).then((result) => {
        if (result.isConfirmed) {
            jQuery.ajax({
                url: "{{ route('deselect_student') }}",
                type: "POST",
                data: {
                    classSubjectID: classSubjectID,
                    studentID: studentID,
                    _token: jQuery('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Removed', response.success, 'success').then(() => {
                            loadStudentsForElection(classSubjectID, jQuery('#saveElectionBtn').data('subclass-id'));
                        });
                    }
                }
            });
        }
    });
});

// ---- Class Results (bulk across streams) ----
(function() {
    const scope = window.teacherBulkResultScope || [];

    function uniqueClasses() {
        const map = {};
        scope.forEach(function(r) {
            if (!r.classID) return;
            map[r.classID] = r.class_name || ('Class #' + r.classID);
        });
        return Object.keys(map).map(function(id) {
            return { classID: id, class_name: map[id] };
        }).sort(function(a, b) {
            return String(a.class_name).localeCompare(String(b.class_name));
        });
    }

    function subclassesForClass(classID) {
        const map = {};
        scope.forEach(function(r) {
            if (String(r.classID) !== String(classID) || !r.subclassID) return;
            map[r.subclassID] = r.subclass_display || r.subclass_name || ('Stream #' + r.subclassID);
        });
        return Object.keys(map).map(function(id) {
            return { subclassID: id, label: map[id] };
        }).sort(function(a, b) {
            return String(a.label).localeCompare(String(b.label));
        });
    }

    function subjectsFor(classID, subclassID) {
        const map = {};
        scope.forEach(function(r) {
            if (String(r.classID) !== String(classID)) return;
            if (subclassID && String(r.subclassID) !== String(subclassID)) return;
            // All subclasses: subject appears if taught in any stream of class
            const key = String(r.subjectID);
            if (!map[key]) {
                map[key] = {
                    subjectID: r.subjectID,
                    subject_name: r.subject_name,
                    is_duo: !!r.is_duo
                };
            } else if (r.is_duo) {
                map[key].is_duo = true;
            }
        });
        return Object.keys(map).map(function(k) { return map[k]; })
            .sort(function(a, b) {
                return String(a.subject_name).localeCompare(String(b.subject_name));
            });
    }

    function fillClasses() {
        const $c = jQuery('#bulkClassID');
        $c.html('<option value="">' + i18nSub.select_class_opt + '</option>');
        uniqueClasses().forEach(function(c) {
            $c.append('<option value="' + c.classID + '">' + jQuery('<div>').text(c.class_name).html() + '</option>');
        });
    }

    function fillSubclasses(classID) {
        const $s = jQuery('#bulkSubclassID');
        $s.html('<option value="">' + i18nSub.all_subclasses + '</option>');
        if (!classID) {
            $s.prop('disabled', true);
            return;
        }
        subclassesForClass(classID).forEach(function(s) {
            $s.append('<option value="' + s.subclassID + '">' + jQuery('<div>').text(s.label).html() + '</option>');
        });
        $s.prop('disabled', false);
    }

    function fillSubjects(classID, subclassID) {
        const $sub = jQuery('#bulkSubjectID');
        $sub.html('<option value="">' + i18nSub.select_subject_opt + '</option>');
        if (!classID) {
            $sub.prop('disabled', true);
            syncBulkActionButtons();
            return;
        }
        subjectsFor(classID, subclassID || '').forEach(function(s) {
            $sub.append(
                '<option value="' + s.subjectID + '" data-is-duo="' + (s.is_duo ? '1' : '0') + '">' +
                jQuery('<div>').text(s.subject_name).html() + (s.is_duo ? ' [DUO]' : '') +
                '</option>'
            );
        });
        $sub.prop('disabled', false);
        syncBulkActionButtons();
    }

    function syncBulkActionButtons() {
        const ok = !!jQuery('#bulkClassID').val() && !!jQuery('#bulkSubjectID').val();
        jQuery('#bulkBtnAddResult, #bulkBtnViewResult').prop('disabled', !ok);
        const isDuo = String(jQuery('#bulkSubjectID option:selected').data('is-duo')) === '1';
        jQuery('#bulkDuoHint').toggle(isDuo);
    }

    function launchBulk(action) {
        const classID = jQuery('#bulkClassID').val();
        const subclassID = jQuery('#bulkSubclassID').val();
        const subjectID = jQuery('#bulkSubjectID').val();
        if (!classID || !subjectID) {
            Swal.fire(i18nSub.error, i18nSub.please_select_class_subject, 'error');
            return;
        }

        const className = jQuery('#bulkClassID option:selected').text().trim();
        const subjectName = jQuery('#bulkSubjectID option:selected').text().replace(/\s*\[DUO\]\s*$/i, '').trim();
        window.currentResultClassName = className;
        window.currentViewMainClass = className;
        window.currentViewSubject = subjectName;
        window.currentViewClass = subclassID
            ? jQuery('#bulkSubclassID option:selected').text().trim()
            : (className + ' (' + i18nSub.all_streams + ')');
        window.currentViewIsDuo = String(jQuery('#bulkSubjectID option:selected').data('is-duo')) === '1';
        window.currentResultIsALevel = typeof isALevelClassName === 'function'
            ? isALevelClassName(className)
            : false;

        Swal.fire({
            title: i18nSub.mapping_subject,
            html: i18nSub.mapping_subject_detail,
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        jQuery.ajax({
            url: '{{ route("teacher.resolve_class_subject") }}',
            method: 'GET',
            data: { classID: classID, subclassID: subclassID || '', subjectID: subjectID },
            success: function(res) {
                Swal.close();
                if (!(res && res.success && res.class_subjectID)) {
                    Swal.fire('Error', (res && res.error) || 'Could not map this subject.', 'error');
                    return;
                }
                window._allSubclassesMode = !!res.all_subclasses;
                window._classSubjectMap = res.class_subject_map || {};
                window._bulkResultsLaunch = true;
                sxHideModal('#bulkResultsFilterModal');
                if (action === 'add') {
                    if (typeof addResults === 'function') addResults(res.class_subjectID);
                } else if (typeof viewResults === 'function') {
                    viewResults(res.class_subjectID);
                }
            },
            error: function(xhr) {
                Swal.close();
                const msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message))
                    ? (xhr.responseJSON.error || xhr.responseJSON.message)
                    : 'Could not fetch subject mapping.';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    jQuery(document).on('click', '#btnOpenBulkResults', function() {
        if (!scope.length) {
            Swal.fire(i18nSub.no_subjects, i18nSub.no_subjects_enter, 'info');
            return;
        }
        fillClasses();
        jQuery('#bulkSubclassID').html('<option value="">' + i18nSub.all_subclasses + '</option>').prop('disabled', true);
        jQuery('#bulkSubjectID').html('<option value="">' + i18nSub.select_subject_opt + '</option>').prop('disabled', true);
        syncBulkActionButtons();
        sxShowModal('#bulkResultsFilterModal');
    });

    jQuery(document).on('change', '#bulkClassID', function() {
        const classID = jQuery(this).val();
        fillSubclasses(classID);
        fillSubjects(classID, '');
    });

    jQuery(document).on('change', '#bulkSubclassID', function() {
        fillSubjects(jQuery('#bulkClassID').val(), jQuery(this).val());
    });

    jQuery(document).on('change', '#bulkSubjectID', syncBulkActionButtons);
    jQuery(document).on('click', '#bulkBtnAddResult', function() { launchBulk('add'); });
    jQuery(document).on('click', '#bulkBtnViewResult', function() { launchBulk('view'); });
})();
</script>
@endpush

