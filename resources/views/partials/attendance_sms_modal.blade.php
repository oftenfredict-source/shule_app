<!-- Attendance SMS Modal (shared: class teacher + admin) -->
<div class="modal fade" id="attendanceSmsModal" tabindex="-1" role="dialog" aria-labelledby="attendanceSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="attendanceSmsModalLabel">
                    <i class="bi bi-chat-dots"></i> Send Attendance SMS
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="attendanceSmsDisabledAlert" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span id="attendanceSmsDisabledText">This feature is not allowed in your school.</span>
                </div>

                <div id="attendanceSmsAutoAlert" class="alert alert-info d-none">
                    <i class="bi bi-robot"></i>
                    <span id="attendanceSmsAutoText"></span>
                </div>

                <div id="attendanceSmsContent" class="d-none">
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <div><strong>Schedule:</strong> <span id="attSmsScheduleLabel">—</span></div>
                        <div><strong>Period:</strong> <span id="attSmsPeriodLabel">—</span></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="attSmsSelectAll">
                            <label class="custom-control-label font-weight-bold" for="attSmsSelectAll">Select All</label>
                        </div>
                        <span class="badge badge-success" id="attSmsSelectedCount" style="font-size: 1rem;">Receivers: 0</span>
                    </div>

                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-hover" id="attendanceSmsTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Parent Of</th>
                                    <th>Phone</th>
                                    <th class="text-center" style="width: 130px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceSmsList"></tbody>
                        </table>
                    </div>

                    <div id="attSmsProgressArea" class="mt-4 d-none">
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-primary-custom">Sending Progress</span>
                            <span id="attSmsProgressText" class="small">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 22px;">
                            <div id="attSmsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                        </div>
                        <div id="attSmsDeliverySummary" class="text-center small mt-2"></div>
                    </div>
                </div>

                <div id="attendanceSmsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small mb-0">Loading schedule and recipients...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="attSmsCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="attSmsStartBtn" disabled>
                    <i class="bi bi-send"></i> Start Sending SMS
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    if (window.__attendanceSmsModalInit) return;
    window.__attendanceSmsModalInit = true;
    if (typeof $ !== 'function') {
        console.error('Attendance SMS modal: jQuery not available.');
        return;
    }

    let attSmsSending = false;
    let attSmsContext = { subclassID: null, referenceDate: null };

    function attSmsUpdateCount() {
        const count = $('#attendanceSmsList .att-sms-checkbox:checked:not(:disabled)').length;
        $('#attSmsSelectedCount').text('Receivers: ' + count);
        $('#attSmsStartBtn').prop('disabled', count === 0 || attSmsSending);
    }

    window.openAttendanceSmsModal = function (options) {
        options = options || {};
        attSmsContext.subclassID = options.subclassID || null;
        attSmsContext.referenceDate = options.referenceDate || null;

        $('#attendanceSmsDisabledAlert').addClass('d-none');
        $('#attendanceSmsAutoAlert').addClass('d-none');
        $('#attendanceSmsContent').addClass('d-none');
        $('#attendanceSmsLoading').removeClass('d-none');
        $('#attSmsProgressArea').addClass('d-none');
        $('#attSmsStartBtn').prop('disabled', true);
        $('#attendanceSmsList').empty();
        $('#attendanceSmsModal').modal('show');

        const params = {};
        if (attSmsContext.subclassID) params.subclassID = attSmsContext.subclassID;
        if (attSmsContext.referenceDate) params.reference_date = attSmsContext.referenceDate;

        $.get('{{ route("attendance_sms.recipients") }}', params)
            .done(function (res) {
                $('#attendanceSmsLoading').addClass('d-none');

                if (!res.success || !res.enabled) {
                    $('#attendanceSmsDisabledAlert').removeClass('d-none');
                    $('#attendanceSmsDisabledText').text(res.message || 'This feature is not allowed in your school.');
                    return;
                }

                $('#attendanceSmsContent').removeClass('d-none');
                $('#attSmsScheduleLabel').text(res.schedule_label || res.schedule);
                $('#attSmsPeriodLabel').text(res.period_label || '');

                const list = $('#attendanceSmsList');
                list.empty();

                (res.recipients || []).forEach(function (row) {
                    const phone = String(row.parentPhone || '').trim();
                    const hasPhone = phone && !['null', 'undefined', 'n/a'].includes(phone.toLowerCase());
                    const cbId = 'att_sms_' + row.studentID;
                    const already = row.already_sent;
                    const disabled = !hasPhone || already;
                    let statusHtml = '<span class="text-muted small"><i class="bi bi-hourglass"></i> Pending</span>';
                    if (!hasPhone) {
                        statusHtml = '<span class="text-danger small"><i class="bi bi-telephone-x"></i> No Phone</span>';
                    } else if (already) {
                        statusHtml = '<span class="text-info small"><i class="bi bi-check2-all"></i> Already Sent</span>';
                    }

                    list.append(`
                        <tr data-student-id="${row.studentID}">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input att-sms-checkbox" id="${cbId}" ${disabled ? 'disabled' : ''}>
                                    <label class="custom-control-label" for="${cbId}">&nbsp;</label>
                                </div>
                            </td>
                            <td>Parent of ${row.studentName}</td>
                            <td>${hasPhone ? phone : '<span class="text-danger small">No Phone</span>'}</td>
                            <td class="text-center att-sms-status-col">${statusHtml}</td>
                        </tr>
                    `);
                });

                $('#attSmsSelectAll').prop('checked', false);
                attSmsUpdateCount();
            })
            .fail(function (xhr) {
                $('#attendanceSmsLoading').addClass('d-none');
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'This feature is not allowed in your school.';
                if (xhr.responseJSON && xhr.responseJSON.auto_enabled) {
                    $('#attendanceSmsAutoAlert').removeClass('d-none');
                    $('#attendanceSmsAutoText').text(msg);
                } else {
                    $('#attendanceSmsDisabledAlert').removeClass('d-none');
                    $('#attendanceSmsDisabledText').text(msg);
                }
            });
    };

    $(document).on('change', '#attSmsSelectAll', function () {
        const checked = $(this).is(':checked');
        $('#attendanceSmsList .att-sms-checkbox:not(:disabled)').prop('checked', checked);
        attSmsUpdateCount();
    });

    $(document).on('change', '.att-sms-checkbox', attSmsUpdateCount);

    $(document).on('click', '#attSmsStartBtn', async function () {
        const rows = $('#attendanceSmsList .att-sms-checkbox:checked').closest('tr');
        if (!rows.length || attSmsSending) return;

        const confirmed = typeof Swal !== 'undefined'
            ? await Swal.fire({
                title: 'Send Attendance SMS?',
                text: 'Send to ' + rows.length + ' parent(s)? Duplicates for this period are blocked.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, Send',
            })
            : { isConfirmed: confirm('Send SMS to ' + rows.length + ' parent(s)?') };

        if (!confirmed.isConfirmed) return;

        attSmsSending = true;
        $('#attSmsStartBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');
        $('#attSmsCancelBtn').prop('disabled', true);
        $('.att-sms-checkbox, #attSmsSelectAll').prop('disabled', true);
        $('#attSmsProgressArea').removeClass('d-none');

        const total = rows.length;
        let delivered = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const row = $(rows[i]);
            const statusCol = row.find('.att-sms-status-col');
            statusCol.html('<span class="spinner-border spinner-border-sm text-primary"></span> Sending...');

            const payload = {
                studentID: row.data('student-id'),
                _token: $('meta[name="csrf-token"]').attr('content'),
            };
            if (attSmsContext.subclassID) payload.subclassID = attSmsContext.subclassID;
            if (attSmsContext.referenceDate) payload.reference_date = attSmsContext.referenceDate;

            try {
                const response = await $.ajax({
                    url: '{{ route("attendance_sms.send") }}',
                    type: 'POST',
                    data: payload,
                });

                if (response.success) {
                    statusCol.html('<span class="text-success small font-weight-bold"><i class="bi bi-check-circle-fill"></i> Delivered</span>');
                    delivered++;
                } else {
                    const err = response.error || 'Failed';
                    statusCol.html('<span class="text-danger small" title="' + err + '"><i class="bi bi-x-circle"></i> ' + (response.already_sent ? 'Already Sent' : 'Failed') + '</span>');
                    failed++;
                }
            } catch (err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Error';
                statusCol.html('<span class="text-danger small" title="' + msg + '"><i class="bi bi-exclamation-triangle"></i> Error</span>');
                failed++;
            }

            const done = delivered + failed;
            const pct = Math.round((done / total) * 100);
            $('#attSmsProgressBar').css('width', pct + '%').text(pct + '%');
            $('#attSmsProgressText').text(done + ' / ' + total);
            $('#attSmsDeliverySummary').html('<span class="text-success">' + delivered + ' Delivered</span> | <span class="text-danger">' + failed + ' Failed</span>');
        }

        attSmsSending = false;
        $('#attSmsStartBtn').html('<i class="bi bi-send"></i> Start Sending SMS');
        $('#attSmsCancelBtn').prop('disabled', false);
        $('#attendanceSmsList .att-sms-checkbox').each(function () {
            const r = $(this).closest('tr');
            if (r.find('.bi-check-circle-fill').length) {
                $(this).prop('checked', false).prop('disabled', true);
            } else if (!r.find('.bi-telephone-x').length && !r.find('.bi-check2-all').length) {
                $(this).prop('disabled', false);
            }
        });
        attSmsUpdateCount();
    });
})(window.jQuery);
</script>
