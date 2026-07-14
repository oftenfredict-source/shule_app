{{-- Shared TANGE PDF download progress (Bootstrap 4 / jQuery) --}}
<div class="modal fade" id="tangeDownloadProgressModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="fa fa-file-pdf-o fa-3x" style="color:#940000;"></i>
                </div>
                <h5 class="mb-4" style="color:#940000;font-weight:bold;">Generating TANGE PDF</h5>
                <div class="progress mb-2" style="height:12px;border-radius:6px;">
                    <div id="tangePdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                         role="progressbar" style="width:0%;"></div>
                </div>
                <div id="tangePdfProgressPercent" class="font-weight-bold text-muted" style="font-size:14px;">0%</div>
                <p class="mt-3 text-muted small mb-0">Please wait while the Teachers Particular PDF is generated.</p>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    if (!$) {
        console.warn('TANGE download: jQuery not loaded');
        return;
    }

    function showTangeProgressModal() {
        $('#tangePdfProgressBar').css('width', '0%');
        $('#tangePdfProgressPercent').text('0%');
        var $modal = $('#tangeDownloadProgressModal');
        $modal.modal({ backdrop: 'static', keyboard: false });
        $modal.modal('show');
    }

    function hideTangeProgressModal() {
        $('#tangeDownloadProgressModal').modal('hide');
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
                    Swal.fire('Error!', error.message || 'PDF download failed', 'error');
                } else {
                    alert('Failed to generate PDF: ' + (error.message || 'Unknown error'));
                }
            });
    };

    $(function () {
        $(document).off('click.tangeDownload', '.js-download-tange').on('click.tangeDownload', '.js-download-tange', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.downloadTangePdf($(this).attr('href'), $(this).data('filename') || 'TANGE');
        });
    });
})(window.jQuery);
</script>
