@extends('includes.superadmin_nav')

@section('title', 'NBC Payment Integration Docs')

@section('content')
<style>
    .page-hero {
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border: 1px solid #f0dada;
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    .page-hero h4 { color: #940000; font-weight: 800; margin: 0; }
    .card, .btn, .badge { border-radius: 0 !important; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; font-weight: 600; }
    .btn-primary-custom:hover { background: #7a0000; border-color: #7a0000; color: #fff; }
    .btn-outline-primary { color: #940000 !important; border-color: #940000 !important; background: #fff; }
    .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
        background: #940000 !important; border-color: #940000 !important; color: #fff !important; box-shadow: none !important;
    }
    .doc-card {
        border: 1px solid #f0dada;
        border-left: 4px solid #940000;
        height: 100%;
        box-shadow: 0 4px 12px rgba(148,0,0,0.05);
    }
    .doc-card .card-title { color: #940000; font-weight: 800; font-size: 1.05rem; }
    .api-chip {
        display: inline-block;
        background: #fff5f5;
        border: 1px solid #f0dada;
        color: #940000;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        margin: 0 6px 6px 0;
    }
</style>

<div class="container-fluid mt-3">
    <div class="page-hero d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h4><i class="bx bx-file-blank me-2"></i>NBC Payment Integration Documentation</h4>
            <p class="mb-0 text-muted small">
                Professional modality documents for NBC bank integration (Name Lookup, Payment Webhook, Status Inquiry, Deduction).
                Download English or Kiswahili PDF to share with the bank.
            </p>
        </div>
    </div>

    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-2" style="color:#940000 !important;">Integration modules covered</h6>
            <div>
                <span class="api-chip">1. Name Lookup API, ShuleXpert</span>
                <span class="api-chip">2. Payment Webhook, ShuleXpert</span>
                <span class="api-chip">3. Status Inquiry, NBC</span>
                <span class="api-chip">4. Deduction / Commission, NBC</span>
            </div>
            <p class="text-muted small mb-0 mt-2">
                Font: Century Gothic &nbsp;|&nbsp; Layout: official report style with ShuleXpert logo &amp; watermark &nbsp;|&nbsp; No live API coding in this step, documentation only.
            </p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card doc-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bx bx-globe" style="font-size:1.8rem;color:#940000;"></i>
                        <div>
                            <div class="card-title mb-0">English Version</div>
                            <small class="text-muted">For NBC technical &amp; product teams</small>
                        </div>
                    </div>
                    <p class="text-muted small flex-grow-1">
                        Full modality proposal: fee structure → control number → name lookup → settlement to school account →
                        webhook ledger update → inquiry fallback → platform deduction.
                    </p>
                    <button type="button"
                            class="btn btn-primary-custom btn-download-nbc-doc"
                            data-url="{{ route('superadmin.nbc_integration_docs.download', ['lang' => 'en']) }}"
                            data-filename="ShuleXpert_NBC_Payment_Integration_Modality_EN.pdf"
                            data-title="Generating NBC Integration Document (English)">
                        <i class="bx bx-download me-1"></i> Download PDF (English)
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card doc-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bx bx-book-open" style="font-size:1.8rem;color:#940000;"></i>
                        <div>
                            <div class="card-title mb-0">Toleo la Kiswahili</div>
                            <small class="text-muted">Kwa timu ya NBC na washirika</small>
                        </div>
                    </div>
                    <p class="text-muted small flex-grow-1">
                        Pendekezo kamili la modality: fee structure → control number → name lookup → fedha kwenda akaunti ya shule →
                        webhook → uchunguzi wa reference → makato ya jukwaa.
                    </p>
                    <button type="button"
                            class="btn btn-outline-primary btn-download-nbc-doc"
                            data-url="{{ route('superadmin.nbc_integration_docs.download', ['lang' => 'sw']) }}"
                            data-filename="ShuleXpert_NBC_Payment_Integration_Modality_SW.pdf"
                            data-title="Inatengeneza Hati ya NBC (Kiswahili)">
                        <i class="bx bx-download me-1"></i> Pakua PDF (Kiswahili)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DOWNLOAD PROGRESS MODAL -->
<div class="modal fade" id="nbcDownloadProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="bx bxs-file-pdf nbc-animate-pulse" style="font-size: 3rem; color: #940000;"></i>
                </div>
                <h5 class="mb-4" id="nbcPdfProgressTitle" style="color: #940000; font-weight: bold;">Generating NBC Integration Document</h5>
                <div class="progress mb-2" style="height: 12px;">
                    <div id="nbcPdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%; background-color: #940000;"></div>
                </div>
                <div id="nbcPdfProgressPercent" class="fw-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small">Tafadhali subiri kidogo wakati tunaandaa hati yako ya NBC integration.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .nbc-animate-pulse { animation: nbcPulse 1.5s infinite; }
    @keyframes nbcPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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
                if (!response.ok) throw new Error('Server error: ' + response.status);
                const type = (response.headers.get('Content-Type') || '').toLowerCase();
                if (type.indexOf('pdf') === -1) throw new Error('PDF generation failed on server.');
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
                    title: 'Download Failed',
                    text: 'Imeshindikana kupakua PDF. ' + error.message,
                    confirmButtonColor: '#940000'
                });
                console.error('NBC PDF Download Error:', error);
            });
    });
});
</script>
@endpush
