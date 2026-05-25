<div class="modal fade" id="permissionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="permissionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white" style="border-radius: 0;">
                <h5 class="modal-title" id="permissionDetailsModalLabel"><i class="fa fa-list-alt"></i> Permission Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#permissionDetailsModal').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped" style="font-size: 14px;">
                    <tbody>
                        <tr>
                            <th style="width: 40%;">Status</th>
                            <td><span id="pd_status" class="badge"></span></td>
                        </tr>
                        <tr id="pd_admin_response_row" style="display: none;">
                            <th>Rejection Reason</th>
                            <td id="pd_admin_response" class="text-danger font-weight-bold"></td>
                        </tr>
                        <tr>
                            <th>Date Requested</th>
                            <td id="pd_requested_at"></td>
                        </tr>
                        <tr>
                            <th>Time Mode</th>
                            <td id="pd_time_mode"></td>
                        </tr>
                        <tr id="pd_days_row">
                            <th>Days Requested</th>
                            <td><strong id="pd_days_count"></strong> Days</td>
                        </tr>
                        <tr id="pd_period_row">
                            <th>Period</th>
                            <td>
                                <span id="pd_start_date"></span> <strong>to</strong> <span id="pd_end_date"></span>
                            </td>
                        </tr>
                        <tr id="pd_return_row">
                            <th>Expected Return Date</th>
                            <td><strong class="text-success" id="pd_return_date"></strong></td>
                        </tr>
                        <tr id="pd_time_row" style="display:none;">
                            <th>Time Range</th>
                            <td>
                                <span id="pd_start_time"></span> - <span id="pd_end_time"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td id="pd_reason_type"></td>
                        </tr>
                        <tr>
                            <th colspan="2">Reason Description</th>
                        </tr>
                        <tr>
                            <td colspan="2" id="pd_reason_description" class="text-muted" style="white-space: pre-wrap; background: #f9f9f9; padding: 15px;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <button type="button" id="pd_download_pdf_btn" class="btn btn-primary" style="background-color: #940000; border-color: #940000; border-radius: 0;">
                        <i class="fa fa-file-pdf-o"></i> <span>Download PDF</span>
                    </button>
                    <div id="pd_pdf_progress_container" style="display: none; width: 150px; margin-left: 15px; align-items: center;">
                        <div class="progress" style="height: 12px; width: 100%; border-radius: 6px; margin-bottom: 0;">
                            <div id="pd_pdf_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span id="pd_pdf_progress_text" style="font-size: 11px; margin-left: 8px; font-weight: bold; color: #940000;">0%</span>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#permissionDetailsModal').modal('hide')" style="border-radius: 0;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function viewPermissionDetails(permission) {
        $('#pd_status').text(permission.status);

        // Handle badge color
        $('#pd_status').removeClass('badge-success badge-danger badge-warning badge-secondary');
        if (permission.status.toLowerCase() === 'approved') {
            $('#pd_status').addClass('badge-success').removeClass('badge-warning badge-danger');
        } else if (permission.status.toLowerCase() === 'rejected') {
            $('#pd_status').addClass('badge-danger').removeClass('badge-warning badge-success');
        } else {
            $('#pd_status').addClass('badge-warning').removeClass('badge-success badge-danger');
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
        $('#pd_reason_description').text(permission.reason_description);

        if(permission.time_mode.toLowerCase() === 'days') {
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

        // Handle PDF Download with Progress
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
            btn.find('span').text('Generating...');
            progressContainer.css('display', 'flex');

            let percent = 0;
            // Simulate generation progress up to 85% while waiting for server TTFB
            const interval = setInterval(() => {
                if (percent < 85) {
                    percent += Math.floor(Math.random() * 8) + 2;
                    if(percent > 85) percent = 85;
                    progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
                    progressText.text(percent + '%');
                }
            }, 300);

            fetch(pdfUrl)
                .then(response => {
                    if(!response.ok) throw new Error('Network response was not ok');

                    let filename = 'Permission_Request_' + permission.id + '.pdf';
                    const disposition = response.headers.get('content-disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }
                    return response.blob().then(blob => ({blob, filename}));
                })
                .then(({blob, filename}) => {
                    clearInterval(interval);
                    percent = 100;
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

                        // Reset UI
                        btn.prop('disabled', false).html(originalContent);
                        progressContainer.hide();
                        progressBar.css('width', '0%');
                        progressText.text('0%');
                    }, 500);
                })
                .catch(err => {
                    clearInterval(interval);
                    Swal.fire('Error', 'Failed to generate PDF. Please try again.', 'error');
                    btn.prop('disabled', false).html(originalContent);
                    progressContainer.hide();
                    progressBar.css('width', '0%');
                    progressText.text('0%');
                });
        });

        $('#permissionDetailsModal').modal('show');
    }
</script>
