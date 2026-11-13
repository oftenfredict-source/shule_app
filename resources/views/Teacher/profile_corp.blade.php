@extends('layouts.teacher_corp')

@section('title', __('teacher.pages.profile').' - ShuleXpert')
@section('page_title', __('teacher.pages.profile'))
@section('page_breadcrumb', __('teacher.pages.profile_bc'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-profile,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-profile p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-profile label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-profile .sx-dl-v,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-profile .sx-profile-name {
        color: #111 !important;
    }

    .sx-profile {
        --maroon: #940000;
        --maroon-deep: #6e0000;
        --line: #e8e0e0;
        --soft: #faf7f7;
        --muted: #666;
        --ink: #111;
    }

    .sx-profile .sx-panel {
        background: linear-gradient(180deg, #fff 0%, #fff 72%, #fbf8f8 100%);
        border: 1px solid var(--line);
        border-top: 4px solid var(--maroon);
        box-shadow: 0 1px 2px rgba(80,0,0,.04), 0 10px 28px rgba(80,0,0,.06);
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .sx-profile .sx-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .95rem 1.15rem;
        background:
            linear-gradient(135deg, rgba(148,0,0,.04), transparent 42%),
            linear-gradient(180deg, #fff, var(--soft));
        border-bottom: 1px solid var(--line);
    }
    .sx-profile .sx-panel-head h4 {
        margin: 0;
        color: var(--maroon);
        font-size: 1.02rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .sx-profile .sx-panel-head p { margin: .2rem 0 0; color: var(--muted) !important; font-size: .78rem; }
    .sx-profile .sx-panel-body { padding: 1.1rem 1.15rem 1.25rem; }

    .sx-profile .btn, .sx-profile .form-control, .sx-profile .form-select,
    .sx-profile .alert, .sx-profile .badge, .sx-profile .progress,
    .sx-profile-modal .btn, .sx-profile-modal .form-control, .sx-profile-modal .form-select,
    .sx-profile-modal .modal-content, .sx-profile-modal .badge, .sx-profile-modal .progress {
        border-radius: 0 !important;
    }

    .sx-btn-maroon {
        background: linear-gradient(180deg, #a80000, #940000 55%, #7f0000) !important;
        border: 1px solid #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
        padding: .55rem 1.05rem;
    }
    .sx-btn-maroon:hover { color: #fff !important; filter: brightness(.96); transform: translateY(-1px); }
    .sx-btn-outline {
        background: #fff;
        border: 1px solid #940000;
        color: #940000 !important;
        font-weight: 700;
        border-radius: 0;
        padding: .55rem 1.05rem;
    }
    .sx-btn-outline:hover { background: #fff5f5; color: #7a0000 !important; transform: translateY(-1px); }

    /* Identity band */
    .sx-id-band {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1.25rem;
        align-items: center;
        padding: 1.25rem 1.3rem;
        background:
            linear-gradient(135deg, rgba(148,0,0,.05), transparent 48%),
            linear-gradient(180deg, #fff, var(--soft));
        border-bottom: 1px solid var(--line);
    }
    @media (max-width: 991.98px) {
        .sx-id-band { grid-template-columns: 1fr; text-align: center; justify-items: center; }
        .sx-id-meta { align-items: center !important; }
        .sx-id-actions { justify-content: center; }
        .sx-tange-box { width: 100%; max-width: 280px; }
    }

    .sx-profile-img-wrap {
        position: relative;
        width: 118px;
        height: 118px;
        border: 3px solid #fff;
        outline: 2px solid #eadfdf;
        box-shadow: 0 8px 22px rgba(80,0,0,.12);
        background: #f3ecec;
        overflow: hidden;
        cursor: pointer;
        flex: 0 0 auto;
        transition: outline-color .18s ease, transform .18s ease;
    }
    .sx-profile-img-wrap:hover {
        outline-color: #940000;
        transform: translateY(-2px);
    }
    .sx-profile-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .sx-profile-img-overlay {
        position: absolute;
        inset: auto 0 0 0;
        height: 34px;
        background: linear-gradient(180deg, transparent, rgba(80,0,0,.72));
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .2s;
        color: #fff !important;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        gap: .35rem;
    }
    .sx-profile-img-overlay i, .sx-profile-img-overlay span { color: #fff !important; }
    .sx-profile-img-wrap:hover .sx-profile-img-overlay { opacity: 1; }
    #imageUploadInput { display: none; }

    .sx-id-meta { display: flex; flex-direction: column; align-items: flex-start; min-width: 0; }
    .sx-profile-name {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--ink) !important;
        margin: 0 0 .35rem;
        line-height: 1.2;
        letter-spacing: -.01em;
    }
    .sx-id-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: .65rem;
    }
    .sx-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .28rem .55rem;
        border: 1px solid #eadfdf;
        background: #fff;
        font-size: .72rem;
        font-weight: 700;
        color: #555 !important;
        letter-spacing: .02em;
    }
    .sx-chip i { color: #940000 !important; font-size: .78rem; }
    .sx-id-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .15rem; }

    .sx-tange-box {
        min-width: 168px;
        padding: 1rem .95rem;
        border: 1px solid #eadfdf;
        border-top: 3px solid #940000;
        background: linear-gradient(180deg, #fff, #fff8f8);
        text-align: center;
        box-shadow: 0 4px 14px rgba(148,0,0,.06);
    }
    .sx-tange-ring {
        --pct: 0;
        width: 92px;
        height: 92px;
        margin: 0 auto .65rem;
        border-radius: 0;
        background:
            conic-gradient(#940000 calc(var(--pct) * 1%), #f0e4e4 0);
        display: grid;
        place-items: center;
        position: relative;
    }
    .sx-tange-ring::before {
        content: '';
        position: absolute;
        inset: 9px;
        background: #fff;
    }
    .sx-tange-ring strong {
        position: relative;
        z-index: 1;
        font-size: 1.15rem;
        font-weight: 800;
        color: #940000 !important;
        line-height: 1;
    }
    .sx-tange-box h6 {
        margin: 0 0 .2rem;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #940000 !important;
    }
    .sx-tange-box p {
        margin: 0;
        font-size: .72rem;
        color: var(--muted) !important;
        line-height: 1.35;
    }

    .sx-alert-hr {
        margin: 0 1.15rem 1rem;
        border: 1px solid #f0d4a8;
        border-left: 4px solid #c98900;
        background: #fffaf0;
        padding: .7rem .9rem;
        font-size: .84rem;
    }
    .sx-alert-hr.danger {
        border-color: #f0c8c8;
        border-left-color: #940000;
        background: #fff5f5;
    }

    /* Definition rows */
    .sx-dl {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
        border: 1px solid var(--line);
        background: #fff;
    }
    @media (max-width: 767.98px) {
        .sx-dl { grid-template-columns: 1fr; }
    }
    .sx-dl-row {
        display: grid;
        grid-template-columns: 42% 58%;
        border-bottom: 1px solid #f0eaea;
        border-right: 1px solid #f0eaea;
        min-height: 52px;
        transition: background .15s ease;
    }
    .sx-dl-row:hover { background: #fffafa; }
    .sx-dl-row:nth-child(2n) { border-right: 0; }
    .sx-dl-k {
        padding: .7rem .85rem;
        background: linear-gradient(180deg, #faf7f7, #f7f2f2);
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #777 !important;
        display: flex;
        align-items: center;
        border-right: 1px solid #f0eaea;
    }
    .sx-dl-v {
        padding: .7rem .9rem;
        font-size: .9rem;
        font-weight: 700;
        color: var(--ink) !important;
        display: flex;
        align-items: center;
        word-break: break-word;
    }

    .sx-tag {
        display: inline-block;
        padding: .22rem .55rem;
        font-size: .7rem;
        font-weight: 800;
        border: 1px solid transparent;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .sx-tag-active { background: #eef8f0; color: #1b6b2e !important; border-color: #c8e6cf; }
    .sx-tag-inactive { background: #fdeeee; color: #940000 !important; border-color: #f0c8c8; }

    .sx-req-card {
        margin-top: .75rem;
        padding: .9rem;
        border: 1px solid var(--line);
        background: var(--soft);
    }
    .sx-req-card h6 {
        font-size: .7rem;
        font-weight: 800;
        color: #777 !important;
        letter-spacing: .04em;
        margin-bottom: .55rem;
    }
    .sx-info-label {
        font-size: .68rem;
        color: #888 !important;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 800;
        margin-bottom: .3rem;
        display: block;
    }

    .sx-profile-modal .modal-header {
        background: linear-gradient(180deg, #fff, #faf7f7) !important;
        border-bottom: 1px solid #e8e0e0 !important;
    }
    .sx-profile-modal .modal-title {
        color: #940000 !important;
        font-weight: 800;
        font-size: 1rem;
    }
    .sx-profile-modal .btn-close { opacity: .7; }
    .sx-profile-modal .form-control:focus,
    .sx-profile-modal .form-select:focus,
    .sx-profile .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 .2rem rgba(148,0,0,.15);
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-profile,
    html[data-bs-theme="dark"] .sx-profile {
        --line: #3a2a2e;
        --soft: #2a1c20;
        --muted: #d7c4c8;
        --ink: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-panel,
    html[data-bs-theme="dark"] .sx-profile .sx-panel {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-panel-head,
    html[data-bs-theme="dark"] .sx-profile .sx-panel-head,
    html[data-sx-theme="dark"] .sx-profile .sx-id-band,
    html[data-bs-theme="dark"] .sx-profile .sx-id-band {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-panel-head h4,
    html[data-bs-theme="dark"] .sx-profile .sx-panel-head h4 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-panel-head p,
    html[data-bs-theme="dark"] .sx-profile .sx-panel-head p,
    html[data-sx-theme="dark"] .sx-profile .sx-tange-box p,
    html[data-bs-theme="dark"] .sx-profile .sx-tange-box p {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-profile-name,
    html[data-bs-theme="dark"] .sx-profile .sx-profile-name {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-chip,
    html[data-bs-theme="dark"] .sx-profile .sx-chip {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #e8c9cd !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-btn-outline,
    html[data-bs-theme="dark"] .sx-profile .sx-btn-outline {
        background: transparent !important;
        border-color: #940000 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-btn-outline:hover,
    html[data-bs-theme="dark"] .sx-profile .sx-btn-outline:hover {
        background: #2a1c20 !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-tange-box,
    html[data-bs-theme="dark"] .sx-profile .sx-tange-box {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-tange-ring,
    html[data-bs-theme="dark"] .sx-profile .sx-tange-ring {
        background: conic-gradient(#940000 calc(var(--pct) * 1%), #3a2a2e 0) !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-tange-ring::before,
    html[data-bs-theme="dark"] .sx-profile .sx-tange-ring::before {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-tange-ring strong,
    html[data-bs-theme="dark"] .sx-profile .sx-tange-ring strong,
    html[data-sx-theme="dark"] .sx-profile .sx-tange-box h6,
    html[data-bs-theme="dark"] .sx-profile .sx-tange-box h6 {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-profile-img-wrap,
    html[data-bs-theme="dark"] .sx-profile .sx-profile-img-wrap {
        border-color: #24181c !important;
        outline-color: #3a2a2e !important;
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-alert-hr,
    html[data-bs-theme="dark"] .sx-profile .sx-alert-hr {
        background: #2a2418 !important;
        border-color: #5a4a28 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-alert-hr.danger,
    html[data-bs-theme="dark"] .sx-profile .sx-alert-hr.danger {
        background: #2a1c20 !important;
        border-color: #5a3030 !important;
        border-left-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-dl,
    html[data-bs-theme="dark"] .sx-profile .sx-dl {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-dl-row,
    html[data-bs-theme="dark"] .sx-profile .sx-dl-row {
        border-bottom-color: #3a2a2e !important;
        border-right-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-dl-row:hover,
    html[data-bs-theme="dark"] .sx-profile .sx-dl-row:hover {
        background: #2a1c20 !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-dl-k,
    html[data-bs-theme="dark"] .sx-profile .sx-dl-k {
        background: #1c1417 !important;
        color: #ffb4b4 !important;
        border-right-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-dl-v,
    html[data-bs-theme="dark"] .sx-profile .sx-dl-v {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-req-card,
    html[data-bs-theme="dark"] .sx-profile .sx-req-card {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile .sx-req-card h6,
    html[data-bs-theme="dark"] .sx-profile .sx-req-card h6,
    html[data-sx-theme="dark"] .sx-profile .sx-info-label,
    html[data-bs-theme="dark"] .sx-profile .sx-info-label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .modal-content,
    html[data-bs-theme="dark"] .sx-profile-modal .modal-content {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .modal-header,
    html[data-bs-theme="dark"] .sx-profile-modal .modal-header {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .modal-title,
    html[data-bs-theme="dark"] .sx-profile-modal .modal-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .modal-footer,
    html[data-bs-theme="dark"] .sx-profile-modal .modal-footer {
        background: #1c1417 !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .form-label,
    html[data-bs-theme="dark"] .sx-profile-modal .form-label,
    html[data-sx-theme="dark"] .sx-profile-modal label,
    html[data-bs-theme="dark"] .sx-profile-modal label {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .form-control,
    html[data-bs-theme="dark"] .sx-profile-modal .form-control,
    html[data-sx-theme="dark"] .sx-profile-modal .form-select,
    html[data-bs-theme="dark"] .sx-profile-modal .form-select,
    html[data-sx-theme="dark"] .sx-profile .form-control,
    html[data-bs-theme="dark"] .sx-profile .form-control,
    html[data-sx-theme="dark"] .sx-profile .form-select,
    html[data-bs-theme="dark"] .sx-profile .form-select {
        background: #1c1417 !important;
        border-color: #4a383c !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .form-select option,
    html[data-bs-theme="dark"] .sx-profile-modal .form-select option {
        background: #1c1417;
        color: #f3e9eb;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .btn-close,
    html[data-bs-theme="dark"] .sx-profile-modal .btn-close {
        filter: invert(1) grayscale(1);
    }
    html[data-sx-theme="dark"] .sx-profile-modal .text-muted,
    html[data-bs-theme="dark"] .sx-profile-modal .text-muted {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-profile-modal .btn-secondary,
    html[data-bs-theme="dark"] .sx-profile-modal .btn-secondary {
        background: #2a1c20 !important;
        border-color: #4a383c !important;
        color: #e8c9cd !important;
    }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $fullName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? ''));
    $profileImage = $teacher->image
        ? asset('userImages/'.$teacher->image)
        : (($teacher->gender ?? '') === 'Female' ? asset('images/female.png') : asset('images/male.png'));
    $profilePct = (int) ($teacher->tange_completion_percent ?? 0);
    $retireAlert = $teacher->retirement_alert ?? null;
    $positionLabel = $teacher->position ?? ($teacher->qualification ?? __('teacher.role_teacher'));
    $fmt = function ($d) {
        return $d ? \Carbon\Carbon::parse($d)->format('d M Y') : __('teacher.prof.na');
    };
@endphp

<div class="sx-profile">
    {{-- Identity --}}
    <div class="sx-panel">
        <div class="sx-id-band">
            <div class="sx-profile-img-wrap" onclick="document.getElementById('imageUploadInput').click();" title="{{ __('teacher.prof.change_photo') }}">
                <img src="{{ $profileImage }}" id="profileDisplayImg" alt="{{ __('teacher.prof.profile_image') }}">
                <div class="sx-profile-img-overlay">
                    <i class="bi bi-camera"></i><span>{{ __('teacher.prof.photo') }}</span>
                </div>
            </div>
            <input type="file" id="imageUploadInput" accept="image/*">

            <div class="sx-id-meta">
                <h2 class="sx-profile-name">{{ $fullName !== '' ? $fullName : __('teacher.profile_head') }}</h2>
                <p class="mb-2" style="color:#666;font-size:.8rem;">{{ __('teacher.profile_sub') }}</p>
                <div class="sx-id-chips">
                    <span class="sx-chip"><i class="bi bi-person-badge"></i> C/N {{ $teacher->employee_number ?? __('teacher.prof.na') }}</span>
                    <span class="sx-chip"><i class="bi bi-briefcase"></i> {{ $positionLabel }}</span>
                    <span class="sx-chip">
                        <i class="bi bi-circle-fill" style="font-size:.45rem;{{ ($teacher->status ?? '') === 'Active' ? 'color:#1b6b2e!important' : 'color:#940000!important' }}"></i>
                        @php
                            $statusLabel = match($teacher->status ?? null) {
                                'Active' => __('teacher.prof.active'),
                                'Inactive' => __('teacher.prof.inactive'),
                                default => $teacher->status ?? __('teacher.prof.na'),
                            };
                        @endphp
                        {{ $statusLabel }}
                    </span>
                </div>
                <div class="sx-id-actions">
                    <button type="button" class="btn sx-btn-maroon" data-bs-toggle="modal" data-bs-target="#editModal">
                        <i class="bi bi-pencil-square"></i> {{ __('teacher.edit_profile') }}
                    </button>
                    <a href="{{ route('teacher.profile.download_pdf') }}" class="btn sx-btn-outline js-download-tange" data-filename="TANGE_My_Particulars">
                        <i class="bi bi-file-earmark-pdf"></i> {{ __('teacher.prof.download_tange') }}
                    </a>
                </div>
            </div>

            <div class="sx-tange-box">
                <div class="sx-tange-ring" style="--pct: {{ max(0, min(100, $profilePct)) }};">
                    <strong>{{ $profilePct }}%</strong>
                </div>
                <h6>{{ __('teacher.tange_score') }}</h6>
                <p>{{ $profilePct >= 100 ? __('teacher.prof.tange_complete') : __('teacher.prof.tange_incomplete') }}</p>
            </div>
        </div>

        @if($retireAlert)
            <div class="sx-alert-hr {{ ($retireAlert['level'] ?? '') === 'danger' ? 'danger' : '' }}">
                <strong>{{ __('teacher.prof.hr_alert') }}</strong> {{ $retireAlert['message'] ?? '' }}
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="sx-panel h-100">
                <div class="sx-panel-head">
                    <div>
                        <h4><i class="bi bi-person-vcard"></i> {{ __('teacher.personal_info') }}</h4>
                        <p>{{ __('teacher.contact_info') }}</p>
                    </div>
                </div>
                <div class="sx-panel-body pt-0 px-0 pb-0">
                    <div class="sx-dl" style="border:0;border-top:1px solid var(--line);">
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.sex') }}</div><div class="sx-dl-v">{{ ($teacher->gender ?? null) === 'Male' ? __('teacher.prof.male') : (($teacher->gender ?? null) === 'Female' ? __('teacher.prof.female') : ($teacher->gender ?? __('teacher.prof.na'))) }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.age') }}</div><div class="sx-dl-v">{{ $teacher->age !== null ? __('teacher.prof.years_n', ['n' => $teacher->age]) : __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.date_of_birth') }}</div><div class="sx-dl-v">{{ $fmt($teacher->date_of_birth ?? null) }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.national_id') }}</div><div class="sx-dl-v">{{ $teacher->national_id ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.phone') }}</div><div class="sx-dl-v">{{ $teacher->phone_number ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.email') }}</div><div class="sx-dl-v">{{ $teacher->email ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.residence') }}</div><div class="sx-dl-v">{{ $teacher->address ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row">
                            <div class="sx-dl-k">{{ __('teacher.status') }}</div>
                            <div class="sx-dl-v">
                                <span class="sx-tag {{ ($teacher->status ?? '') === 'Active' ? 'sx-tag-active' : 'sx-tag-inactive' }}">{{ ($teacher->status ?? '') === 'Active' ? __('teacher.prof.active') : (($teacher->status ?? '') === 'Inactive' ? __('teacher.prof.inactive') : ($teacher->status ?? __('teacher.prof.na'))) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="sx-panel h-100">
                <div class="sx-panel-head">
                    <div>
                        <h4><i class="bi bi-building"></i> {{ __('teacher.employment_info') }}</h4>
                        <p>{{ __('teacher.prof.employment_sub') }}</p>
                    </div>
                </div>
                <div class="sx-panel-body pt-0 px-0 pb-0">
                    <div class="sx-dl" style="border:0;border-top:1px solid var(--line);">
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.check_no') }}</div><div class="sx-dl-v">{{ $teacher->employee_number ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.tsc_number') }}</div><div class="sx-dl-v">{{ $teacher->tsc_number ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.establishment_rank') }}</div><div class="sx-dl-v">{{ $teacher->establishment_rank ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.salary_scale') }}</div><div class="sx-dl-v">{{ $teacher->salary_scale ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.position') }}</div><div class="sx-dl-v">{{ $teacher->position ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.work_station') }}</div><div class="sx-dl-v">{{ $teacher->work_station ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.highest_education') }}</div><div class="sx-dl-v">{{ $teacher->qualification ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.experience') }}</div><div class="sx-dl-v">{{ $teacher->years_of_experience !== null ? __('teacher.prof.years_n', ['n' => $teacher->years_of_experience]) : ($teacher->experience ?? __('teacher.prof.na')) }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.subjects_studied') }}</div><div class="sx-dl-v">{{ $teacher->specialization ?? __('teacher.prof.na') }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.subjects_teaching') }}</div><div class="sx-dl-v">{{ $teacher->subjects_teaching ?? __('teacher.prof.na') }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="sx-panel">
                <div class="sx-panel-head">
                    <div>
                        <h4><i class="bi bi-calendar3"></i> {{ __('teacher.prof.service_timeline') }}</h4>
                        <p>{{ __('teacher.prof.service_timeline_sub') }}</p>
                    </div>
                </div>
                <div class="sx-panel-body pt-0 px-0 pb-0">
                    <div class="sx-dl" style="border:0;border-top:1px solid var(--line);">
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.date_employment') }}</div><div class="sx-dl-v">{{ $fmt($teacher->date_hired ?? null) }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.date_confirmation') }}</div><div class="sx-dl-v">{{ $fmt($teacher->date_confirmed ?? null) }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.date_current_rank') }}</div><div class="sx-dl-v">{{ $fmt($teacher->date_current_rank ?? null) }}</div></div>
                        <div class="sx-dl-row"><div class="sx-dl-k">{{ __('teacher.prof.date_retirement') }}</div><div class="sx-dl-v">{{ $fmt($teacher->date_of_retirement ?? null) }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="sx-panel">
                <div class="sx-panel-head">
                    <div>
                        <h4><i class="bi bi-shield-lock"></i> {{ __('teacher.prof.account_security') }}</h4>
                        <p>{{ __('teacher.prof.account_security_sub') }}</p>
                    </div>
                </div>
                <div class="sx-panel-body">
                    <form id="teacherChangePasswordForm" method="POST" action="{{ route('teacher.change_password') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="sx-info-label">{{ __('teacher.prof.current_password') }}</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="sx-info-label">{{ __('teacher.prof.confirm_password') }}</label>
                                <input type="password" class="form-control" name="new_password_confirmation" id="teacher_confirm_password" required autocomplete="new-password">
                                <div class="mt-2 small" id="teacher_confirm_feedback" style="display: none;">
                                    <span id="teacher_confirm_status"></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="sx-info-label">{{ __('teacher.prof.new_password') }}</label>
                                <input type="password" class="form-control" name="new_password" id="new_pw" required autocomplete="new-password">
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div id="pw_bar" class="progress-bar" style="width: 0%;"></div>
                                    </div>
                                </div>
                                <div class="sx-req-card" id="teacher_requirements_card">
                                    <h6>{{ __('teacher.prof.password_requirements') }}</h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="requirement-item text-danger" id="t_req_len">
                                            <i class="fa fa-times-circle me-2" id="t_icon_len"></i> {{ __('teacher.prof.req_len') }}
                                        </li>
                                        <li class="requirement-item text-danger" id="t_req_upper">
                                            <i class="fa fa-times-circle me-2" id="t_icon_upper"></i> {{ __('teacher.prof.req_upper') }}
                                        </li>
                                        <li class="requirement-item text-danger" id="t_req_lower">
                                            <i class="fa fa-times-circle me-2" id="t_icon_lower"></i> {{ __('teacher.prof.req_lower') }}
                                        </li>
                                        <li class="requirement-item text-danger" id="t_req_num">
                                            <i class="fa fa-times-circle me-2" id="t_icon_num"></i> {{ __('teacher.prof.req_num') }}
                                        </li>
                                        <li class="requirement-item text-danger" id="t_req_spec">
                                            <i class="fa fa-times-circle me-2" id="t_icon_spec"></i> {{ __('teacher.prof.req_spec') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" id="teacherSubmitBtn" class="btn sx-btn-maroon" disabled>{{ __('teacher.prof.update_password') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade sx-profile-modal" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('teacher.edit_profile') }} / {{ __('teacher.tange_score') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('teacher.close') }}"></button>
            </div>
            <form id="teacherSelfUpdateForm" action="{{ route('update_teacher') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                <input type="hidden" name="status" value="{{ $teacher->status ?? 'Active' }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 text-center mb-2">
                            @php
                                $editPreviewImage = $teacher->image
                                    ? asset('userImages/'.$teacher->image)
                                    : (($teacher->gender ?? '') === 'Female' ? asset('images/female.png') : asset('images/male.png'));
                            @endphp
                            <img id="editProfileImagePreview" src="{{ $editPreviewImage }}" alt="{{ __('teacher.prof.profile_image') }}"
                                 style="width:110px;height:110px;object-fit:cover;border:3px solid #940000;">
                            <div class="mt-2">
                                <label class="form-label fw-bold d-block">{{ __('teacher.profile_photo') }}</label>
                                <input type="file" class="form-control" name="image" id="edit_profile_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted d-block">{{ __('teacher.prof.photo_hint') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" value="{{ $teacher->first_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.middle_name') }}</label>
                            <input type="text" class="form-control" name="middle_name" value="{{ $teacher->middle_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.last_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" value="{{ $teacher->last_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.sex') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="Male" {{ ($teacher->gender ?? '') === 'Male' ? 'selected' : '' }}>{{ __('teacher.prof.male') }}</option>
                                <option value="Female" {{ ($teacher->gender ?? '') === 'Female' ? 'selected' : '' }}>{{ __('teacher.prof.female') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone_number" value="{{ $teacher->phone_number }}" pattern="^255\d{9}$" maxlength="12" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ $teacher->email }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.national_id') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="national_id" value="{{ $teacher->national_id }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('teacher.bank_account') }}</label>
                            <input type="text" class="form-control" name="bank_account_number" value="{{ $teacher->bank_account_number }}">
                        </div>

                        @include('partials.teacher_tange_fields', ['prefix' => 'profile_', 'teacher' => $teacher])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">{{ __('teacher.close') }}</button>
                    <button type="submit" class="btn sx-btn-maroon">{{ __('teacher.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TANGE PDF progress (BS5) --}}
<div class="modal fade sx-profile-modal" id="tangeDownloadProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div class="mb-3"><i class="fa fa-file-pdf-o fa-3x" style="color:#940000;"></i></div>
                <h5 class="mb-3" style="color:#940000;font-weight:800;">{{ __('teacher.generating_tange_pdf') }}</h5>
                <div class="progress mb-2" style="height:12px;">
                    <div id="tangePdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width:0%;"></div>
                </div>
                <div id="tangePdfProgressPercent" class="fw-bold text-muted" style="font-size:.9rem;">0%</div>
                <p class="mt-3 text-muted small mb-0">{{ __('teacher.wait_tange_pdf') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- After #sx-teacher-dark-fix: labels, values, status tags --}}
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-panel-head h4,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-panel-head h4,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-dl-k,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-dl-k,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-info-label,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-info-label,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-req-card h6,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-req-card h6,
html[data-sx-theme="dark"] .sx-profile-modal .form-label,
html[data-bs-theme="dark"] .sx-profile-modal .form-label,
html[data-sx-theme="dark"] .sx-profile-modal label,
html[data-bs-theme="dark"] .sx-profile-modal label {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-profile-name,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-profile-name,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-dl-v,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-dl-v {
    color: #f3e9eb !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-panel-head p,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-panel-head p,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-chip,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-chip,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tange-box p,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tange-box p {
    color: #d7c4c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tag.sx-tag-active,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tag.sx-tag-active {
    background: #eef8f0 !important;
    color: #1b6b2e !important;
    -webkit-text-fill-color: #1b6b2e !important;
    border-color: #c8e6cf !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tag.sx-tag-inactive,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tag.sx-tag-inactive {
    background: #fdeeee !important;
    color: #940000 !important;
    -webkit-text-fill-color: #940000 !important;
    border-color: #f0c8c8 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tange-ring strong,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tange-ring strong,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tange-box h6,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-profile .sx-tange-box h6 {
    color: #ffb4b4 !important;
}
</style>
@endsection

@push('scripts')
<script>
const P = @json(__('teacher.prof'));
window.sxShowModal = window.sxShowModal || function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(node).show();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('show');
};
window.sxHideModal = window.sxHideModal || function(el) {
    const node = typeof el === 'string' ? document.querySelector(el) : el;
    if (!node) return;
    if (window.bootstrap && bootstrap.Modal) {
        const inst = bootstrap.Modal.getInstance(node);
        if (inst) inst.hide();
        return;
    }
    if (window.jQuery && jQuery.fn.modal) jQuery(node).modal('hide');
};

(function ($) {
    if (!$) return;

    function showTangeProgressModal() {
        $('#tangePdfProgressBar').css('width', '0%');
        $('#tangePdfProgressPercent').text('0%');
        sxShowModal('#tangeDownloadProgressModal');
    }
    function hideTangeProgressModal() {
        sxHideModal('#tangeDownloadProgressModal');
        setTimeout(function () {
            $('#tangePdfProgressBar').css('width', '0%');
            $('#tangePdfProgressPercent').text('0%');
        }, 400);
    }

    window.downloadTangePdf = function (url, filenameBase) {
        if (!url) return;
        showTangeProgressModal();
        var progress = 0;
        var interval = setInterval(function () {
            if (progress < 95) {
                var inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                if (progress > 80) inc = 0.5 + Math.random();
                progress += inc;
                if (progress > 95) progress = 95;
                $('#tangePdfProgressBar').css('width', progress + '%');
                $('#tangePdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 400);

        fetch(url, { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) throw new Error('Server error: ' + response.statusText);
                var cd = response.headers.get('Content-Disposition') || '';
                var match = cd.match(/filename="?([^"]+)"?/i);
                return response.blob().then(function (blob) {
                    return { blob: blob, filename: match ? match[1] : null };
                });
            })
            .then(function (result) {
                clearInterval(interval);
                $('#tangePdfProgressBar').css('width', '100%');
                $('#tangePdfProgressPercent').text('100%');
                setTimeout(function () {
                    var downloadUrl = window.URL.createObjectURL(result.blob);
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    var stamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
                    a.download = result.filename || ((filenameBase || 'TANGE') + '_' + stamp + '.pdf');
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);
                    setTimeout(hideTangeProgressModal, 600);
                }, 350);
            })
            .catch(function (error) {
                clearInterval(interval);
                hideTangeProgressModal();
                if (typeof Swal !== 'undefined') {
                    Swal.fire(P.error_bang, error.message || P.pdf_failed, 'error');
                } else {
                    alert((P.pdf_fail_gen || '').replace(':msg', error.message || P.unknown_error));
                }
            });
    };

    $(function () {
        $(document).off('click.tangeDownload', '.js-download-tange').on('click.tangeDownload', '.js-download-tange', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.downloadTangePdf($(this).attr('href'), $(this).data('filename') || 'TANGE');
        });

        function tangeYearsFromDate(dateStr) {
            if (!dateStr) return null;
            var d = new Date(dateStr);
            if (isNaN(d.getTime())) return null;
            var now = new Date();
            var years = now.getFullYear() - d.getFullYear();
            var m = now.getMonth() - d.getMonth();
            if (m < 0 || (m === 0 && now.getDate() < d.getDate())) years--;
            return years < 0 ? 0 : years;
        }
        function refreshTangeAutoFields() {
            $('.tange-dob').each(function () {
                var target = $(this).data('age-target');
                var y = tangeYearsFromDate($(this).val());
                if (target) $('#' + target).val(y !== null ? (P.years_n || ':n years').replace(':n', y) : '');
            });
            $('.tange-hired').each(function () {
                var target = $(this).data('exp-target');
                var y = tangeYearsFromDate($(this).val());
                if (target) $('#' + target).val(y !== null ? (P.years_n || ':n years').replace(':n', y) : '');
            });
        }
        $(document).on('change input', '.tange-dob, .tange-hired', refreshTangeAutoFields);
        refreshTangeAutoFields();

        $('#edit_profile_image').on('change', function () {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire(P.error, P.image_2mb, 'error');
                $(this).val('');
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#editProfileImagePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        });

        $('#teacherSelfUpdateForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var original = $btn.html();
            $btn.prop('disabled', true).html(P.saving);
            var formData = new FormData($form[0]);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    $btn.prop('disabled', false).html(original);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: P.updated,
                            text: response.success,
                            confirmButtonColor: '#940000'
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire(P.error, response.error || P.update_failed, 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(original);
                    var msg = P.update_failed;
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) msg = xhr.responseJSON.error;
                        else if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).join('\n');
                        }
                    }
                    Swal.fire(P.error, msg, 'error');
                }
            });
        });

        $('#imageUploadInput').on('change', function() {
            const file = this.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('teacher_image', file);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            Swal.fire({
                title: P.updating_picture,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ route('teacher.profile.update_image') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const url = response.image_url + (response.image_url.indexOf('?') >= 0 ? '&' : '?') + 'v=' + Date.now();
                        $('#profileDisplayImg').attr('src', url);
                        $('.sx-sa-id-avatar img, .app-header .dropdown img, .sidebar-profile img.profile-image, .user-area img.user-avatar').attr('src', url);
                        Swal.fire(P.success_bang, response.message, 'success');
                    } else {
                        Swal.fire(P.error_bang, response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire(P.error_bang, (xhr.responseJSON && xhr.responseJSON.message) || P.upload_failed, 'error');
                }
            });
        });

        const teacherNewPassword = $('#new_pw');
        const teacherConfirmPassword = $('#teacher_confirm_password');
        const teacherSubmitBtn = $('#teacherSubmitBtn');
        let teacherState = { length: false, upper: false, lower: false, number: false, special: false, match: false };

        function updateTeacherRequirementUI(elementId, iconId, isMet) {
            const element = $(elementId);
            const icon = $(iconId);
            if (isMet) {
                element.removeClass('text-danger').addClass('text-success');
                icon.removeClass('fa-times-circle text-danger').addClass('fa-check-circle text-success');
            } else {
                element.removeClass('text-success').addClass('text-danger');
                icon.removeClass('fa-check-circle text-success').addClass('fa-times-circle text-danger');
            }
        }

        function toggleTeacherSubmit() {
            const allPassed = teacherState.length && teacherState.upper && teacherState.lower && teacherState.number && teacherState.special && teacherState.match;
            teacherSubmitBtn.prop('disabled', !allPassed);
        }

        function checkTeacherMatch() {
            const pw = teacherNewPassword.val();
            const confirm = teacherConfirmPassword.val();
            const feedback = $('#teacher_confirm_feedback');
            const statusSpan = $('#teacher_confirm_status');
            if (!confirm) {
                feedback.hide();
                teacherState.match = false;
                toggleTeacherSubmit();
                return;
            }
            feedback.show();
            if (pw === confirm) {
                teacherState.match = true;
                statusSpan.html('<i class="fa fa-check-circle text-success me-2"></i> ' + P.passwords_match).removeClass('text-danger').addClass('text-success');
            } else {
                teacherState.match = false;
                statusSpan.html('<i class="fa fa-times-circle text-danger me-2"></i> ' + P.passwords_no_match).removeClass('text-success').addClass('text-danger');
            }
            toggleTeacherSubmit();
        }

        function checkTeacherPassword() {
            const pw = teacherNewPassword.val();
            teacherState.length = pw.length >= 8;
            updateTeacherRequirementUI('#t_req_len', '#t_icon_len', teacherState.length);
            teacherState.upper = /[A-Z]/.test(pw);
            updateTeacherRequirementUI('#t_req_upper', '#t_icon_upper', teacherState.upper);
            teacherState.lower = /[a-z]/.test(pw);
            updateTeacherRequirementUI('#t_req_lower', '#t_icon_lower', teacherState.lower);
            teacherState.number = /[0-9]/.test(pw);
            updateTeacherRequirementUI('#t_req_num', '#t_icon_num', teacherState.number);
            teacherState.special = /[^a-zA-Z0-9]/.test(pw);
            updateTeacherRequirementUI('#t_req_spec', '#t_icon_spec', teacherState.special);

            let score = 0;
            if (pw) {
                if (teacherState.length) score += 25;
                if (teacherState.upper) score += 15;
                if (teacherState.lower) score += 15;
                if (teacherState.number) score += 15;
                if (teacherState.special) score += 15;
                if (pw.length >= 12) score += 15;
            }
            const p = Math.min(100, score);
            $('#pw_bar').css('width', p + '%');
            if (p < 35) $('#pw_bar').removeClass('bg-warning bg-success').addClass('bg-danger');
            else if (p < 70) $('#pw_bar').removeClass('bg-danger bg-success').addClass('bg-warning');
            else $('#pw_bar').removeClass('bg-danger bg-warning').addClass('bg-success');
            checkTeacherMatch();
        }

        teacherNewPassword.on('input', checkTeacherPassword);
        teacherConfirmPassword.on('input', checkTeacherMatch);

        $('#teacherChangePasswordForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = $('#teacherSubmitBtn');
            const originalBtnHtml = submitBtn.html();
            $('.alert-dynamic').remove();
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + P.updating).attr('disabled', true);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    submitBtn.html(originalBtnHtml).attr('disabled', false);
                    if (response.success) {
                        Swal.fire({
                            title: P.success_bang,
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#940000',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            form.find('input[type="password"]').val('');
                            $('#pw_bar').css('width', '0%');
                            $('#teacher_confirm_feedback').hide();
                            $('.requirement-item').removeClass('text-success').addClass('text-danger');
                            $('.requirement-item i').removeClass('fa-check-circle text-success').addClass('fa-times-circle text-danger');
                            teacherState = { length: false, upper: false, lower: false, number: false, special: false, match: false };
                            toggleTeacherSubmit();
                        });
                    } else {
                        Swal.fire({ title: P.error_bang, text: response.message || P.something_wrong, icon: 'error', confirmButtonColor: '#940000' });
                        toggleTeacherSubmit();
                    }
                },
                error: function(xhr) {
                    submitBtn.html(originalBtnHtml).attr('disabled', false);
                    let errorMsg = P.fail_change_password;
                    if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    form.before(`
                        <div class="alert alert-danger alert-dismissible fade show alert-dynamic mt-3" role="alert">
                            <strong>${P.error_label}</strong> ${errorMsg}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="${P.error}"></button>
                        </div>
                    `);
                    Swal.fire({ title: P.error_bang, text: errorMsg, icon: 'error', confirmButtonColor: '#940000' });
                    toggleTeacherSubmit();
                }
            });
        });
    });
})(window.jQuery);
</script>
@endpush
