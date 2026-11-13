@extends('includes.superadmin_nav')

@section('page_title', __('superadmin.pages.nbc_integration_docs'))
@section('page_breadcrumb', __('superadmin.pages.nbc_integration_docs'))

@section('page_actions')
    <a href="{{ route('superadmin.banks.index') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-bank me-1"></i> {{ __('superadmin.nbc_docs.banks') }}
    </a>
    <a href="{{ route('superadmin.bank_accounts.verification') }}" class="btn btn-primary waves-effect waves-light">
        <i class="fi fi-rr-shield-check me-1"></i> {{ __('superadmin.nbc_docs.account_verification') }}
    </a>
@endsection

@section('content')
<style>
    /* Light mode only */
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc span:not(.sx-chip),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc label,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc .text-body,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc a:not(.btn),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-nbc .sx-nbc-card-title {
        color: #000 !important;
    }
    .sx-nbc .btn-primary,
    .sx-nbc a.btn-primary,
    .sx-nbc .btn-primary i,
    .sx-nbc .btn-primary i:before,
    .sx-nbc .text-white {
        color: #fff !important;
    }
    .sx-nbc .btn-outline-primary {
        border-color: #940000 !important;
        color: #940000 !important;
        background: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-nbc .btn-outline-primary:hover,
    .sx-nbc .btn-outline-primary:focus {
        background: #940000 !important;
        color: #fff !important;
        border-color: #940000 !important;
    }
    .sx-nbc .btn-outline-primary i,
    .sx-nbc .btn-outline-primary i:before {
        color: inherit !important;
    }
    .sx-nbc .btn-primary {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.45rem;
    }
    .sx-nbc .btn-primary:hover {
        background: #7a0000 !important;
        border-color: #7a0000 !important;
    }
    .sx-nbc .sx-nbc-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    .sx-nbc .sx-nbc-card .card-body {
        padding: 1.15rem 1.25rem 1.25rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .sx-nbc .sx-nbc-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #eee;
    }
    .sx-nbc .sx-nbc-card-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .sx-nbc .sx-doc-ico {
        width: 3rem;
        height: 3rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fde8e8;
        color: #940000 !important;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .sx-nbc .sx-doc-ico i,
    .sx-nbc .sx-doc-ico i:before {
        color: inherit !important;
        line-height: 1;
    }
    .sx-nbc .sx-doc-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #940000 !important;
        line-height: 1.25;
    }
    .sx-nbc .sx-doc-sub {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.8rem;
        color: #666 !important;
    }
    .sx-nbc .sx-chip {
        display: inline-flex;
        align-items: center;
        background: #fff5f5;
        border: 1px solid #f0dada;
        color: #940000 !important;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        margin: 0 0.4rem 0.4rem 0;
        border-radius: 0.3rem;
        letter-spacing: 0.01em;
    }
    .sx-nbc .sx-meta {
        font-size: 0.8rem;
        color: #666 !important;
        margin-top: 0.75rem;
    }
    .sx-nbc .modal-content {
        border-radius: 0;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
    }
    .sx-nbc .progress {
        height: 10px;
        border-radius: 0.35rem;
        background: #ececec;
    }
    .sx-nbc .progress-bar {
        background: #940000 !important;
    }
    .nbc-animate-pulse { animation: nbcPulse 1.5s infinite; }
    @keyframes nbcPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-nbc,
    html[data-bs-theme="dark"] .sx-nbc,
    html[data-sx-theme="dark"] .sx-nbc p,
    html[data-bs-theme="dark"] .sx-nbc p,
    html[data-sx-theme="dark"] .sx-nbc span:not(.sx-chip),
    html[data-bs-theme="dark"] .sx-nbc span:not(.sx-chip),
    html[data-sx-theme="dark"] .sx-nbc small,
    html[data-bs-theme="dark"] .sx-nbc small,
    html[data-sx-theme="dark"] .sx-nbc strong,
    html[data-bs-theme="dark"] .sx-nbc strong,
    html[data-sx-theme="dark"] .sx-nbc h1,
    html[data-bs-theme="dark"] .sx-nbc h1,
    html[data-sx-theme="dark"] .sx-nbc h2,
    html[data-bs-theme="dark"] .sx-nbc h2,
    html[data-sx-theme="dark"] .sx-nbc h3,
    html[data-bs-theme="dark"] .sx-nbc h3,
    html[data-sx-theme="dark"] .sx-nbc h4,
    html[data-bs-theme="dark"] .sx-nbc h4,
    html[data-sx-theme="dark"] .sx-nbc h5,
    html[data-bs-theme="dark"] .sx-nbc h5,
    html[data-sx-theme="dark"] .sx-nbc h6,
    html[data-bs-theme="dark"] .sx-nbc h6,
    html[data-sx-theme="dark"] .sx-nbc .sx-nbc-card-title,
    html[data-bs-theme="dark"] .sx-nbc .sx-nbc-card-title,
    html[data-sx-theme="dark"] .sx-nbc a:not(.btn),
    html[data-bs-theme="dark"] .sx-nbc a:not(.btn) {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .text-muted,
    html[data-bs-theme="dark"] .sx-nbc .text-muted,
    html[data-sx-theme="dark"] .sx-nbc .sx-doc-sub,
    html[data-bs-theme="dark"] .sx-nbc .sx-doc-sub,
    html[data-sx-theme="dark"] .sx-nbc .sx-meta,
    html[data-bs-theme="dark"] .sx-nbc .sx-meta {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .sx-nbc-card,
    html[data-bs-theme="dark"] .sx-nbc .sx-nbc-card,
    html[data-sx-theme="dark"] .sx-nbc .card,
    html[data-bs-theme="dark"] .sx-nbc .card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    html[data-sx-theme="dark"] .sx-nbc .sx-nbc-card-head,
    html[data-bs-theme="dark"] .sx-nbc .sx-nbc-card-head {
        border-bottom-color: rgba(255, 180, 180, 0.14) !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .sx-doc-title,
    html[data-bs-theme="dark"] .sx-nbc .sx-doc-title {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .sx-doc-ico,
    html[data-bs-theme="dark"] .sx-nbc .sx-doc-ico {
        background: rgba(148, 0, 0, 0.28) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .sx-chip,
    html[data-bs-theme="dark"] .sx-nbc .sx-chip {
        background: rgba(148, 0, 0, 0.22) !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-nbc .btn-outline-primary {
        background: transparent !important;
        border-color: #c43a3a !important;
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .btn-outline-primary:hover,
    html[data-bs-theme="dark"] .sx-nbc .btn-outline-primary:hover,
    html[data-sx-theme="dark"] .sx-nbc .btn-outline-primary:focus,
    html[data-bs-theme="dark"] .sx-nbc .btn-outline-primary:focus {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .btn-primary,
    html[data-bs-theme="dark"] .sx-nbc .btn-primary {
        color: #fff !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .modal-content,
    html[data-bs-theme="dark"] .sx-nbc .modal-content,
    html[data-sx-theme="dark"] #nbcDownloadProgressModal .modal-content,
    html[data-bs-theme="dark"] #nbcDownloadProgressModal .modal-content {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        border-top-color: #940000 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .progress,
    html[data-bs-theme="dark"] .sx-nbc .progress,
    html[data-sx-theme="dark"] #nbcDownloadProgressModal .progress,
    html[data-bs-theme="dark"] #nbcDownloadProgressModal .progress {
        background: #1c1417 !important;
    }
    html[data-sx-theme="dark"] #nbcDownloadProgressModal .text-muted,
    html[data-bs-theme="dark"] #nbcDownloadProgressModal .text-muted,
    html[data-sx-theme="dark"] #nbcPdfProgressPercent,
    html[data-bs-theme="dark"] #nbcPdfProgressPercent {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] #nbcPdfProgressTitle,
    html[data-bs-theme="dark"] #nbcPdfProgressTitle {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-nbc .alert-danger,
    html[data-bs-theme="dark"] .sx-nbc .alert-danger {
        background: rgba(148, 0, 0, 0.28) !important;
        border-color: rgba(255, 180, 180, 0.22) !important;
        color: #ffb4b4 !important;
    }
</style>

<div class="sx-nbc">
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fi fi-rr-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('superadmin.common.close') }}"></button>
        </div>
    @endif

    <div class="card sx-nbc-card mb-3">
        <div class="card-body">
            <div class="sx-nbc-card-head">
                <h6 class="sx-nbc-card-title">{{ __('superadmin.nbc_docs.modules_title') }}</h6>
                <span class="small text-muted">{{ __('superadmin.nbc_docs.modules_desc') }}</span>
            </div>
            <div>
                <span class="sx-chip">{{ __('superadmin.nbc_docs.module_1') }}</span>
                <span class="sx-chip">{{ __('superadmin.nbc_docs.module_2') }}</span>
                <span class="sx-chip">{{ __('superadmin.nbc_docs.module_3') }}</span>
                <span class="sx-chip">{{ __('superadmin.nbc_docs.module_4') }}</span>
            </div>
            <p class="sx-meta mb-0">
                {{ __('superadmin.nbc_docs.font_note') }}
            </p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card sx-nbc-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="sx-doc-ico" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
                        <div>
                            <h5 class="sx-doc-title">{{ __('superadmin.nbc_docs.en_version') }}</h5>
                            <span class="sx-doc-sub">{{ __('superadmin.nbc_docs.en_audience') }}</span>
                        </div>
                    </div>
                    <p class="text-muted small flex-grow-1 mb-3">
                        {{ __('superadmin.nbc_docs.en_desc') }}
                    </p>
                    <button type="button"
                            class="btn btn-primary waves-effect waves-light btn-download-nbc-doc mt-auto"
                            data-url="{{ route('superadmin.nbc_integration_docs.download', ['lang' => 'en']) }}"
                            data-filename="ShuleXpert_NBC_Payment_Integration_Modality_EN.pdf"
                            data-title="{{ __('superadmin.nbc_docs.generating_title_en') }}">
                        <i class="fi fi-rr-download me-1"></i> {{ __('superadmin.nbc_docs.en_download') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card sx-nbc-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="sx-doc-ico" aria-hidden="true"><i class="fi fi-rr-book-alt"></i></span>
                        <div>
                            <h5 class="sx-doc-title">{{ __('superadmin.nbc_docs.sw_version') }}</h5>
                            <span class="sx-doc-sub">{{ __('superadmin.nbc_docs.sw_audience') }}</span>
                        </div>
                    </div>
                    <p class="text-muted small flex-grow-1 mb-3">
                        {{ __('superadmin.nbc_docs.sw_desc') }}
                    </p>
                    <button type="button"
                            class="btn btn-outline-primary waves-effect waves-light btn-download-nbc-doc mt-auto"
                            data-url="{{ route('superadmin.nbc_integration_docs.download', ['lang' => 'sw']) }}"
                            data-filename="ShuleXpert_NBC_Payment_Integration_Modality_SW.pdf"
                            data-title="{{ __('superadmin.nbc_docs.generating_title_sw') }}">
                        <i class="fi fi-rr-download me-1"></i> {{ __('superadmin.nbc_docs.sw_download') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nbcDownloadProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="fi fi-rr-file-pdf nbc-animate-pulse" style="font-size: 2.75rem; color: #940000;"></i>
                    </div>
                    <h5 class="mb-4" id="nbcPdfProgressTitle" style="color: #940000; font-weight: bold;">{{ __('superadmin.nbc_docs.generating_title') }}</h5>
                    <div class="progress mb-2">
                        <div id="nbcPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="nbcPdfProgressPercent" class="fw-bold text-muted" style="font-size: 14px;">0%</div>
                    <p class="mt-3 text-muted small mb-0">{{ __('superadmin.nbc_docs.generating_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const SX = @json(__('superadmin.nbc_docs'));
    const SXC = @json(__('superadmin.common'));
    const progressModalEl = document.getElementById('nbcDownloadProgressModal');
    const progressModal = new bootstrap.Modal(progressModalEl);

    $(document).on('click', '.btn-download-nbc-doc', function(e) {
        e.preventDefault();

        const url = $(this).data('url');
        const filename = $(this).data('filename');
        const title = $(this).data('title');

        $('#nbcPdfProgressTitle').text(title);
        $('#nbcPdfProgressBar').css('width', '0%');
        $('#nbcPdfProgressPercent').text('0%');
        progressModal.show();

        let progress = 0;
        const interval = setInterval(function() {
            if (progress < 95) {
                progress += 2 + Math.random() * 5;
                if (progress > 95) progress = 95;
                $('#nbcPdfProgressBar').css('width', progress + '%');
                $('#nbcPdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 300);

        fetch(url, { credentials: 'same-origin' })
            .then(function(response) {
                if (!response.ok) throw new Error(SX.js.server_error + ' ' + response.status);
                const type = (response.headers.get('Content-Type') || '').toLowerCase();
                if (type.indexOf('pdf') === -1) throw new Error(SX.js.pdf_failed);
                return response.blob();
            })
            .then(function(blob) {
                clearInterval(interval);
                $('#nbcPdfProgressBar').css('width', '100%');
                $('#nbcPdfProgressPercent').text('100%');

                setTimeout(function() {
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);

                    setTimeout(function() { progressModal.hide(); }, 500);
                }, 400);
            })
            .catch(function(error) {
                clearInterval(interval);
                progressModal.hide();
                Swal.fire({
                    icon: 'error',
                    title: SX.js.download_failed,
                    text: SX.js.download_failed_text.replace(':error', error.message),
                    confirmButtonColor: '#940000'
                });
                console.error('NBC PDF Download Error:', error);
            });
    });
});
</script>
@endpush
