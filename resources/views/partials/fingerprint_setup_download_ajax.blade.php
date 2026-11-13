@once
<script>
(function () {
    if (window.FpSetupDownload && window.FpSetupDownload.ready) return;
    var FP = @json(__('superadmin.fingerprint_setup'));
    var FPC = @json(__('superadmin.common'));
    var FPJS = FP.js || {};

    function parseFilename(header, fallback) {
        if (!header) return fallback;
        var match = /filename\*?=(?:UTF-8''|utf-8'')?["']?([^"';\n]+)/i.exec(header);
        if (!match) return fallback;
        try {
            return decodeURIComponent(match[1].replace(/["']/g, ''));
        } catch (e) {
            return match[1].replace(/["']/g, '') || fallback;
        }
    }

    function findProgressUI(btn) {
        var card = btn.closest('.card');
        if (!card) return null;
        return {
            wrap: card.querySelector('.fp-setup-download-progress'),
            bar: card.querySelector('.fp-setup-download-bar'),
            percent: card.querySelector('.fp-setup-download-percent'),
            label: card.querySelector('.fp-setup-download-label')
        };
    }

    function updateInlineProgress(ui, percent, label) {
        if (!ui || !ui.wrap) return;
        ui.wrap.classList.remove('d-none');
        if (ui.bar) ui.bar.style.width = percent + '%';
        if (ui.percent) ui.percent.textContent = percent + '%';
        if (ui.label && label) ui.label.textContent = label;
    }

    function resetInlineProgress(ui) {
        if (!ui || !ui.wrap) return;
        ui.wrap.classList.add('d-none');
        if (ui.bar) ui.bar.style.width = '0%';
        if (ui.percent) ui.percent.textContent = '0%';
        if (ui.label) ui.label.textContent = FP.downloading;
    }

    function swalProgressHtml() {
        return (FP.downloading_wait || '') + '<br><br>' +
            '<b id="fpSetupDlPercent">0%</b><br>' +
            '<div class="progress mt-2" style="height: 10px; border-radius: 5px; overflow: hidden;">' +
            '<div id="fpSetupDlBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; background-color: #940000;"></div>' +
            '</div>';
    }

    function updateSwalProgress(percent, title) {
        var bar = document.getElementById('fpSetupDlBar');
        var text = document.getElementById('fpSetupDlPercent');
        if (bar) bar.style.width = percent + '%';
        if (text) text.textContent = percent + '%';
        if (title && typeof Swal !== 'undefined') {
            Swal.update({ title: title });
        }
    }

    function saveBlob(blob, filename) {
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    function readBlobError(xhr) {
        return new Promise(function (resolve) {
            try {
                var reader = new FileReader();
                reader.onload = function () {
                    try {
                        var json = JSON.parse(reader.result);
                        resolve(json.message || FP.download_failed);
                    } catch (e) {
                        resolve(FP.download_failed);
                    }
                };
                reader.onerror = function () { resolve(FP.download_failed); };
                reader.readAsText(xhr.response);
            } catch (e) {
                resolve(FP.download_failed);
            }
        });
    }

    window.FpSetupDownload = {
        ready: true,
        run: function (btn) {
            if (!btn || btn.disabled) return;

            var url = btn.getAttribute('data-download-url');
            var fallbackName = btn.getAttribute('data-filename') || 'fingerprint_setup.zip';
            if (!url) return;

            var inlineUi = findProgressUI(btn);
            var useSwal = typeof Swal !== 'undefined' && !inlineUi;
            var originalHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + FP.downloading;

            if (inlineUi) {
                resetInlineProgress(inlineUi);
                updateInlineProgress(inlineUi, 0, FP.downloading);
            } else if (useSwal) {
                Swal.fire({
                    title: FP.downloading_setup,
                    html: swalProgressHtml(),
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () { Swal.showLoading(); }
                });
            }

            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onprogress = function (evt) {
                var percent = 0;
                var label = FP.downloading;
                if (evt.lengthComputable && evt.total > 0) {
                    percent = Math.min(100, Math.round((evt.loaded / evt.total) * 100));
                    label = percent >= 100 ? FP.finalizing : FP.downloading;
                } else if (evt.loaded > 0) {
                    percent = 50;
                }

                if (inlineUi) {
                    updateInlineProgress(inlineUi, percent, label);
                } else if (useSwal) {
                    updateSwalProgress(percent, percent >= 100 ? FP.finalizing : FP.downloading_setup);
                }
            };

            xhr.onload = function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                if (xhr.status === 200) {
                    var filename = parseFilename(xhr.getResponseHeader('Content-Disposition'), fallbackName);
                    var doneLabel = FPJS.complete || FPC.done || 'Complete!';
                    if (inlineUi) updateInlineProgress(inlineUi, 100, doneLabel);
                    else if (useSwal) updateSwalProgress(100, doneLabel);

                    saveBlob(xhr.response, filename);

                    setTimeout(function () {
                        if (inlineUi) resetInlineProgress(inlineUi);
                        if (useSwal) Swal.close();
                    }, 600);
                    return;
                }

                if (inlineUi) resetInlineProgress(inlineUi);
                readBlobError(xhr).then(function (msg) {
                    if (useSwal) Swal.fire(FPC.error || 'Error', msg, 'error');
                    else alert(msg);
                });
            };

            xhr.onerror = function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if (inlineUi) resetInlineProgress(inlineUi);
                var msg = FPC.network_error || FPJS.network_error || 'Network error.';
                if (useSwal) Swal.fire(FPC.error || 'Error', msg, 'error');
                else alert(msg);
            };

            xhr.send();
        }
    };

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.fp-setup-download-btn');
        if (!btn) return;
        e.preventDefault();
        window.FpSetupDownload.run(btn);
    });
})();
</script>
@endonce
