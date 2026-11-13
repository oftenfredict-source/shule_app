<div class="modal fade" id="permissionDetailsModal" tabindex="-1" aria-labelledby="permissionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sx-details-modal rounded-0">
            <div class="modal-header sx-details-modal-header">
                <h5 class="modal-title" id="permissionDetailsModalLabel">
                    <i class="bi bi-card-list"></i> {{ __('teacher.hr.details_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.close') }}"
                    onclick="if (typeof window.sxHideModal === 'function') { window.sxHideModal('#permissionDetailsModal'); } else if (window.jQuery) { window.jQuery('#permissionDetailsModal').modal('hide'); }"></button>
            </div>
            <div class="modal-body sx-details-modal-body">
                <div class="sx-details-grid">
                    <div class="sx-details-row">
                        <span class="sx-details-label">{{ __('teacher.status') }}</span>
                        <span class="sx-details-value"><span id="pd_status" class="sx-pd-tag"></span></span>
                    </div>
                    <div class="sx-details-row" id="pd_admin_response_row" style="display: none;">
                        <span class="sx-details-label">{{ __('teacher.hr.rejection_reason') }}</span>
                        <span class="sx-details-value text-danger fw-bold" id="pd_admin_response"></span>
                    </div>
                    <div class="sx-details-row">
                        <span class="sx-details-label">{{ __('teacher.hr.date_requested') }}</span>
                        <span class="sx-details-value" id="pd_requested_at"></span>
                    </div>
                    <div class="sx-details-row">
                        <span class="sx-details-label">{{ __('teacher.hr.time_mode') }}</span>
                        <span class="sx-details-value" id="pd_time_mode"></span>
                    </div>
                    <div class="sx-details-row" id="pd_days_row">
                        <span class="sx-details-label">{{ __('teacher.hr.days_requested') }}</span>
                        <span class="sx-details-value"><strong id="pd_days_count"></strong> {{ __('teacher.hr.days') }}</span>
                    </div>
                    <div class="sx-details-row" id="pd_period_row">
                        <span class="sx-details-label">{{ __('teacher.hr.period') }}</span>
                        <span class="sx-details-value">
                            <span id="pd_start_date"></span> <strong>{{ __('teacher.to') }}</strong> <span id="pd_end_date"></span>
                        </span>
                    </div>
                    <div class="sx-details-row" id="pd_return_row">
                        <span class="sx-details-label">{{ __('teacher.hr.expected_return') }}</span>
                        <span class="sx-details-value"><strong class="text-success" id="pd_return_date"></strong></span>
                    </div>
                    <div class="sx-details-row" id="pd_time_row" style="display:none;">
                        <span class="sx-details-label">{{ __('teacher.hr.time_range') }}</span>
                        <span class="sx-details-value">
                            <span id="pd_start_time"></span> - <span id="pd_end_time"></span>
                        </span>
                    </div>
                    <div class="sx-details-row">
                        <span class="sx-details-label">{{ __('teacher.hr.category') }}</span>
                        <span class="sx-details-value" id="pd_reason_type"></span>
                    </div>
                </div>

                <div class="sx-details-reason">
                    <div class="sx-details-label mb-2">{{ __('teacher.hr.reason_desc') }}</div>
                    <div id="pd_reason_description" class="sx-details-reason-text"></div>
                </div>
            </div>
            <div class="modal-footer sx-details-modal-footer">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" id="pd_download_pdf_btn" class="btn btn-primary-custom">
                        <i class="fa fa-file-pdf-o"></i> <span>{{ __('teacher.hr.download_pdf') }}</span>
                    </button>
                    <div id="pd_pdf_progress_container" style="display: none; width: 150px; align-items: center;">
                        <div class="progress" style="height: 10px; width: 100%; border-radius: 0; margin-bottom: 0;">
                            <div id="pd_pdf_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span id="pd_pdf_progress_text" style="font-size: 11px; margin-left: 8px; font-weight: 700; color: #940000;">0%</span>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary rounded-0 fw-bold" data-bs-dismiss="modal" data-dismiss="modal"
                    onclick="if (typeof window.sxHideModal === 'function') { window.sxHideModal('#permissionDetailsModal'); } else if (window.jQuery) { window.jQuery('#permissionDetailsModal').modal('hide'); }">{{ __('teacher.close') }}</button>
            </div>
        </div>
    </div>
</div>

<style>
    .sx-details-modal {
        border: 1px solid #eadfdf !important;
        box-shadow: 0 18px 40px rgba(80,0,0,.14) !important;
        overflow: hidden;
    }
    .sx-details-modal-header {
        background: linear-gradient(180deg, #fff8f8, #fff) !important;
        border-bottom: 1px solid #eadfdf !important;
        border-radius: 0 !important;
        padding: .95rem 1.1rem;
    }
    .sx-details-modal-header .modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800 !important;
        color: #940000 !important;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .sx-details-modal-header .btn-close {
        box-shadow: none !important;
        opacity: .65;
    }
    .sx-details-modal-body {
        padding: 1.05rem 1.15rem 1.15rem;
        background: #fff;
    }
    .sx-details-grid {
        border: 1px solid #eadfdf;
        background: #fff;
    }
    .sx-details-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: .75rem;
        padding: .7rem .85rem;
        border-bottom: 1px solid #f0e8e8;
        align-items: start;
    }
    .sx-details-row:last-child { border-bottom: 0; }
    .sx-details-label {
        font-size: .7rem;
        font-weight: 800 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #666 !important;
        padding-top: .15rem;
    }
    .sx-details-value {
        font-size: .9rem;
        font-weight: 600 !important;
        color: #111 !important;
        line-height: 1.4;
    }
    .sx-details-reason {
        margin-top: .85rem;
        border: 1px solid #eadfdf;
        border-left: 3px solid #940000;
        background: #faf7f7;
        padding: .85rem .95rem;
    }
    .sx-details-reason-text {
        white-space: pre-wrap;
        font-size: .9rem;
        font-weight: 600 !important;
        color: #333 !important;
        line-height: 1.45;
        min-height: 2.5rem;
    }
    .sx-pd-tag {
        display: inline-block;
        padding: .2rem .45rem;
        font-size: .7rem;
        font-weight: 700 !important;
        border-radius: 0;
        line-height: 1.3;
        letter-spacing: .02em;
        border: 1px solid transparent;
    }
    .sx-pd-tag.is-approved { background: #e8f6ec !important; color: #146c2e !important; border-color: #c6e6cf; }
    .sx-pd-tag.is-rejected { background: #fdecec !important; color: #a11919 !important; border-color: #f0c7c7; }
    .sx-pd-tag.is-pending { background: #fff3cd !important; color: #7a5a00 !important; border-color: #edd89a; }
    .sx-details-modal-footer {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        gap: .65rem;
        flex-wrap: wrap;
        border-top: 1px solid #eadfdf !important;
        background: #faf7f7;
        padding: .85rem 1.1rem;
    }
    .sx-details-modal-footer .btn-primary-custom,
    #pd_download_pdf_btn.btn-primary-custom {
        background: linear-gradient(180deg, #a80000 0%, #940000 55%, #7f0000 100%) !important;
        border-color: #7a0000 !important;
        color: #fff !important;
        font-weight: 700 !important;
        border-radius: 0 !important;
        min-height: 40px;
    }
    @media (max-width: 575.98px) {
        .sx-details-row { grid-template-columns: 1fr; gap: .25rem; }
    }

    /* Dark mode — avoid light text on white detail panels */
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-modal,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-modal {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-modal-header,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-modal-header {
        background: #2a1c20 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-modal-header .modal-title,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-modal-header .modal-title,
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-modal-header .modal-title i,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-modal-header .modal-title i {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-modal-body,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-modal-body {
        background: #1c1417 !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-grid,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-grid {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-row,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-row {
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-label,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-label {
        color: #ffb4b4 !important;
        -webkit-text-fill-color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-value,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-value,
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-value span,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-value span,
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-value strong,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-value strong {
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-value .text-success,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-value .text-success,
    html[data-sx-theme="dark"] #permissionDetailsModal #pd_return_date,
    html[data-bs-theme="dark"] #permissionDetailsModal #pd_return_date {
        color: #69db7c !important;
        -webkit-text-fill-color: #69db7c !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-value .text-danger,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-value .text-danger,
    html[data-sx-theme="dark"] #permissionDetailsModal #pd_admin_response,
    html[data-bs-theme="dark"] #permissionDetailsModal #pd_admin_response {
        color: #ffb4b4 !important;
        -webkit-text-fill-color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-reason,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-reason {
        background: #24181c !important;
        border-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-reason-text,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-reason-text {
        color: #f3e9eb !important;
        -webkit-text-fill-color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-pd-tag.is-pending,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-pd-tag.is-pending {
        background: #fff3cd !important;
        color: #7a5a00 !important;
        -webkit-text-fill-color: #7a5a00 !important;
        border-color: #edd89a !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-pd-tag.is-approved,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-pd-tag.is-approved {
        background: #e8f6ec !important;
        color: #146c2e !important;
        -webkit-text-fill-color: #146c2e !important;
        border-color: #c6e6cf !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-pd-tag.is-rejected,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-pd-tag.is-rejected {
        background: #fdecec !important;
        color: #a11919 !important;
        -webkit-text-fill-color: #a11919 !important;
        border-color: #f0c7c7 !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .sx-details-modal-footer,
    html[data-bs-theme="dark"] #permissionDetailsModal .sx-details-modal-footer {
        background: #24181c !important;
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal .btn-outline-secondary,
    html[data-bs-theme="dark"] #permissionDetailsModal .btn-outline-secondary {
        color: #e8c9cd !important;
        border-color: #4a383c !important;
        background: transparent !important;
    }
    html[data-sx-theme="dark"] #permissionDetailsModal #pd_pdf_progress_text,
    html[data-bs-theme="dark"] #permissionDetailsModal #pd_pdf_progress_text {
        color: #ffb4b4 !important;
    }
</style>

<script>
    const i18nHrDetails = @json(__('teacher.hr'));
    function viewPermissionDetails(permission) {
        var $ = window.jQuery;
        if (typeof $ !== 'function') {
            console.error('jQuery is required for viewPermissionDetails');
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: i18nHrDetails.error, text: i18nHrDetails.scripts_failed, confirmButtonColor: '#940000' });
            }
            return;
        }
        window.$ = $;

        $('#pd_status').text(permission.status);
        $('#pd_status').removeClass('is-approved is-rejected is-pending badge-success badge-danger badge-warning badge-secondary');
        if (permission.status.toLowerCase() === 'approved') {
            $('#pd_status').addClass('is-approved');
        } else if (permission.status.toLowerCase() === 'rejected') {
            $('#pd_status').addClass('is-rejected');
        } else {
            $('#pd_status').addClass('is-pending');
        }

        if (permission.status.toLowerCase() === 'rejected' && permission.admin_response) {
            $('#pd_admin_response_row').show();
            $('#pd_admin_response').text(permission.admin_response);
        } else {
            $('#pd_admin_response_row').hide();
        }

        $('#pd_requested_at').text(permission.requested_at);
        $('#pd_time_mode').text(permission.time_mode);
        $('#pd_reason_type').text(permission.reason_type);
        $('#pd_reason_description').text(permission.reason_description || '—');

        if (permission.time_mode.toLowerCase() === 'days') {
            $('#pd_days_row, #pd_period_row, #pd_return_row').show();
            $('#pd_time_row').hide();
            $('#pd_days_count').text(permission.days_count);
            $('#pd_start_date').text(permission.start_date);
            $('#pd_end_date').text(permission.end_date);
            $('#pd_return_date').text(permission.return_date);
        } else {
            $('#pd_days_row, #pd_period_row, #pd_return_row').hide();
            $('#pd_time_row').show();
            $('#pd_start_time').text(permission.start_time);
            $('#pd_end_time').text(permission.end_time);
        }

        $('#pd_download_pdf_btn').off('click').on('click', function() {
            const btn = $(this);
            const originalContent = btn.html();
            const progressContainer = $('#pd_pdf_progress_container');
            const progressBar = $('#pd_pdf_progress_bar');
            const progressText = $('#pd_pdf_progress_text');
            const isStudentPerm = permission.is_student_permission || false;
            const pdfUrl = isStudentPerm
                ? "{{ url('admin/student-permissions/export-pdf') }}/" + permission.id
                : "{{ url('admin/hr/permissions/export-pdf') }}/" + permission.id;

            btn.prop('disabled', true);
            btn.find('span').text(i18nHrDetails.generating);
            progressContainer.css('display', 'flex');

            let percent = 0;
            const interval = setInterval(() => {
                if (percent < 85) {
                    percent += Math.floor(Math.random() * 8) + 2;
                    if (percent > 85) percent = 85;
                    progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
                    progressText.text(percent + '%');
                }
            }, 300);

            fetch(pdfUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    let filename = 'Permission_Request_' + permission.id + '.pdf';
                    const disposition = response.headers.get('content-disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }
                    return response.blob().then(blob => ({ blob, filename }));
                })
                .then(({ blob, filename }) => {
                    clearInterval(interval);
                    progressBar.css('width', '100%').attr('aria-valuenow', 100);
                    progressText.text('100%');
                    setTimeout(() => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        btn.prop('disabled', false).html(originalContent);
                        progressContainer.hide();
                        progressBar.css('width', '0%');
                        progressText.text('0%');
                    }, 500);
                })
                .catch(() => {
                    clearInterval(interval);
                    Swal.fire(i18nHrDetails.error, i18nHrDetails.fail_pdf, 'error');
                    btn.prop('disabled', false).html(originalContent);
                    progressContainer.hide();
                    progressBar.css('width', '0%');
                    progressText.text('0%');
                });
        });

        if (typeof window.sxShowModal === 'function') {
            window.sxShowModal('#permissionDetailsModal');
        } else {
            $('#permissionDetailsModal').modal('show');
        }
    }
</script>
