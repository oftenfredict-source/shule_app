@php
    $setup = $fingerprintSetup ?? null;
@endphp

@if($setup)
<div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #940000 !important;">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="mb-1 fw-bold text-primary-custom">
                    <i class="bi bi-pc-display"></i> {{ $setup->title }}
                </h6>
                <p class="small text-muted mb-0">
                    Install Local Agent kwenye PC ya shule ili kuunganisha biometric device.
                    @if($setup->version)<span class="ms-1">Version <strong>{{ $setup->version }}</strong></span>@endif
                </p>
            </div>
            <button type="button"
                class="btn btn-primary-custom btn-sm fp-setup-download-btn"
                data-download-url="{{ route('fingerprint_attendance_setup.download', $setup->setupID) }}"
                data-filename="{{ $setup->original_filename ?: 'fingerprint_setup.zip' }}">
                <i class="bi bi-download"></i> Download Setup
            </button>
        </div>
        <div class="fp-setup-download-progress d-none mt-3">
            <div class="d-flex justify-content-between small mb-1">
                <span class="fp-setup-download-label">Downloading...</span>
                <strong class="fp-setup-download-percent">0%</strong>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated fp-setup-download-bar" role="progressbar" style="width: 0%; background-color: #940000;"></div>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-light border mb-3 py-2 small">
    <i class="bi bi-info-circle"></i>
    Fingerprint desktop setup haijapakiwa bado. Wasiliana na Super Admin.
</div>
@endif

@include('partials.fingerprint_setup_download_ajax')
