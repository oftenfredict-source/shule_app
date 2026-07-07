<!-- Fingerprint Attendance SMS Modal (shared: teacher report + admin) -->
<div class="modal fade" id="fingerprintAttendanceSmsModal" tabindex="-1" role="dialog" aria-labelledby="fingerprintAttendanceSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="fingerprintAttendanceSmsModalLabel">
                    <i class="bi bi-fingerprint"></i> Send Fingerprint Attendance SMS
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="fpAttSmsDisabledAlert" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span id="fpAttSmsDisabledText">Unable to load recipients.</span>
                </div>

                <div id="fpAttSmsContent" class="d-none">
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <div><strong>Period:</strong> <span id="fpAttSmsPeriodLabel">—</span></div>
                        <div><strong>Schedule:</strong> <span id="fpAttSmsScheduleLabel">—</span></div>
                        <div class="small text-muted mt-1" id="fpAttSmsScheduleNote"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="fpAttSmsSelectAll">
                            <label class="custom-control-label font-weight-bold" for="fpAttSmsSelectAll">Select All</label>
                        </div>
                        <span class="badge badge-success" id="fpAttSmsSelectedCount" style="font-size: 1rem;">Receivers: 0</span>
                    </div>

                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-hover" id="fingerprintAttendanceSmsTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Parent Of</th>
                                    <th>Phone</th>
                                    <th class="text-center" style="width: 130px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="fingerprintAttendanceSmsList"></tbody>
                        </table>
                    </div>

                    <div id="fpAttSmsProgressArea" class="mt-4 d-none">
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-primary-custom">Sending Progress</span>
                            <span id="fpAttSmsProgressText" class="small">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 22px;">
                            <div id="fpAttSmsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                        </div>
                        <div id="fpAttSmsDeliverySummary" class="text-center small mt-2"></div>
                    </div>
                </div>

                <div id="fpAttSmsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small mb-0">Loading recipients...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="fpAttSmsCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="fpAttSmsStartBtn" disabled>
                    <i class="bi bi-send"></i> Start Sending SMS
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.__fingerprintAttendanceSmsModalInit) return;
    window.__fingerprintAttendanceSmsModalInit = true;

    let fpAttSmsSending = false;
    let fpAttSmsContext = {
        subclassID: null,
        classID: null,
        referenceDate: null,
        filterType: null,
        studentIds: null,
    };

    function fpAttSmsUpdateCount() {
        const count = $('#fingerprintAttendanceSmsList .fp-att-sms-checkbox:checked:not(:disabled)').length;
        $('#fpAttSmsSelectedCount').text('Receivers: ' + count);
        $('#fpAttSmsStartBtn').prop('disabled', count === 0 || fpAttSmsSending);
    }

    window.openFingerprintAttendanceSmsModal = function (options) {
        options = options || {};
        fpAttSmsContext.subclassID = options.subclassID || null;
        fpAttSmsContext.classID = options.classID || null;
        fpAttSmsContext.referenceDate = options.referenceDate || null;
        fpAttSmsContext.filterType = options.filterType || null;
        fpAttSmsContext.studentIds = options.studentIds || null;

        $('#fpAttSmsDisabledAlert').addClass('d-none');
        $('#fpAttSmsContent').addClass('d-none');
        $('#fpAttSmsLoading').removeClass('d-none');
        $('#fpAttSmsProgressArea').addClass('d-none');
        $('#fpAttSmsStartBtn').prop('disabled', true);
        $('#fingerprintAttendanceSmsList').empty();
        $('#fingerprintAttendanceSmsModal').modal('show');

        const params = {};
        if (fpAttSmsContext.subclassID) params.subclassID = fpAttSmsContext.subclassID;
        if (fpAttSmsContext.classID) params.classID = fpAttSmsContext.classID;
        if (fpAttSmsContext.referenceDate) params.reference_date = fpAttSmsContext.referenceDate;
        if (fpAttSmsContext.filterType) params.filter_type = fpAttSmsContext.filterType;
        if (fpAttSmsContext.studentIds && fpAttSmsContext.studentIds.length) {
            fpAttSmsContext.studentIds.forEach(function (id, i) {
                params['student_ids[' + i + ']'] = id;
            });
        }

        $.get('{{ route("fingerprint_attendance_sms.recipients") }}', params)
            .done(function (res) {
                $('#fpAttSmsLoading').addClass('d-none');

                if (!res.success || !res.enabled) {
                    $('#fpAttSmsDisabledAlert').removeClass('d-none');
                    $('#fpAttSmsDisabledText').text(res.message || 'Unable to load recipients.');
                    return;
                }

                $('#fpAttSmsContent').removeClass('d-none');
                $('#fpAttSmsPeriodLabel').text(res.period_label || '—');
                $('#fpAttSmsScheduleLabel').text(res.schedule_label || res.schedule || '—');
                $('#fpAttSmsScheduleNote').text(
                    res.configured
                        ? (res.auto_summary || 'School schedule configured.')
                        : 'Manual send for this report period (no Super Admin schedule required).'
                );

                const list = $('#fingerprintAttendanceSmsList');
                list.empty();

                const recipients = res.recipients || [];
                if (!recipients.length) {
                    list.append('<tr><td colspan="4" class="text-center text-muted py-3">No students found for this report.</td></tr>');
                    return;
                }

                recipients.forEach(function (row) {
                    const phone = String(row.parentPhone || '').trim();
                    const hasPhone = phone && !['null', 'undefined', 'n/a'].includes(phone.toLowerCase());
                    const cbId = 'fp_att_sms_' + row.studentID;
                    const already = row.already_sent;
                    const noData = row.has_message === false;
                    const disabled = !hasPhone || already || noData;
                    let statusHtml = '<span class="text-muted small"><i class="bi bi-hourglass"></i> Pending</span>';
                    if (!hasPhone) {
                        statusHtml = '<span class="text-danger small"><i class="bi bi-telephone-x"></i> No Phone</span>';
                    } else if (already) {
                        statusHtml = '<span class="text-info small"><i class="bi bi-check2-all"></i> Already Sent</span>';
                    } else if (noData) {
                        statusHtml = '<span class="text-warning small"><i class="bi bi-dash-circle"></i> No Data</span>';
                    }

                    list.append(`
                        <tr data-student-id="${row.studentID}">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input fp-att-sms-checkbox" id="${cbId}" ${disabled ? 'disabled' : ''}>
                                    <label class="custom-control-label" for="${cbId}">&nbsp;</label>
                                </div>
                            </td>
                            <td>Parent of ${row.studentName}</td>
                            <td>${hasPhone ? phone : '<span class="text-danger small">No Phone</span>'}</td>
                            <td class="text-center fp-att-sms-status-col">${statusHtml}</td>
                        </tr>
                    `);
                });

                $('#fpAttSmsSelectAll').prop('checked', false);
                fpAttSmsUpdateCount();
            })
            .fail(function (xhr) {
                $('#fpAttSmsLoading').addClass('d-none');
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Failed to load recipients.';
                $('#fpAttSmsDisabledAlert').removeClass('d-none');
                $('#fpAttSmsDisabledText').text(msg);
            });
    };

    $(document).on('change', '#fpAttSmsSelectAll', function () {
        const checked = $(this).is(':checked');
        $('#fingerprintAttendanceSmsList .fp-att-sms-checkbox:not(:disabled)').prop('checked', checked);
        fpAttSmsUpdateCount();
    });

    $(document).on('change', '.fp-att-sms-checkbox', fpAttSmsUpdateCount);

    $(document).on('click', '#fpAttSmsStartBtn', async function () {
        const rows = $('#fingerprintAttendanceSmsList .fp-att-sms-checkbox:checked').closest('tr');
        if (!rows.length || fpAttSmsSending) return;

        const confirmed = typeof Swal !== 'undefined'
            ? await Swal.fire({
                title: 'Send Fingerprint SMS?',
                text: 'Send to ' + rows.length + ' parent(s)? Duplicates for this period are blocked.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, Send',
            })
            : { isConfirmed: confirm('Send SMS to ' + rows.length + ' parent(s)?') };

        if (!confirmed.isConfirmed) return;

        fpAttSmsSending = true;
        $('#fpAttSmsStartBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');
        $('#fpAttSmsCancelBtn').prop('disabled', true);
        $('.fp-att-sms-checkbox, #fpAttSmsSelectAll').prop('disabled', true);
        $('#fpAttSmsProgressArea').removeClass('d-none');

        const total = rows.length;
        let delivered = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const row = $(rows[i]);
            const statusCol = row.find('.fp-att-sms-status-col');
            statusCol.html('<span class="spinner-border spinner-border-sm text-primary"></span> Sending...');

            const payload = {
                studentID: row.data('student-id'),
                _token: $('meta[name="csrf-token"]').attr('content'),
            };
            if (fpAttSmsContext.subclassID) payload.subclassID = fpAttSmsContext.subclassID;
            if (fpAttSmsContext.classID) payload.classID = fpAttSmsContext.classID;
            if (fpAttSmsContext.referenceDate) payload.reference_date = fpAttSmsContext.referenceDate;
            if (fpAttSmsContext.filterType) payload.filter_type = fpAttSmsContext.filterType;

            try {
                const response = await $.ajax({
                    url: '{{ route("fingerprint_attendance_sms.send") }}',
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
            $('#fpAttSmsProgressBar').css('width', pct + '%').text(pct + '%');
            $('#fpAttSmsProgressText').text(done + ' / ' + total);
            $('#fpAttSmsDeliverySummary').html('<span class="text-success">' + delivered + ' Delivered</span> | <span class="text-danger">' + failed + ' Failed</span>');
        }

        fpAttSmsSending = false;
        $('#fpAttSmsStartBtn').html('<i class="bi bi-send"></i> Start Sending SMS');
        $('#fpAttSmsCancelBtn').prop('disabled', false);
        $('#fingerprintAttendanceSmsList .fp-att-sms-checkbox').each(function () {
            const r = $(this).closest('tr');
            if (r.find('.bi-check-circle-fill').length) {
                $(this).prop('checked', false).prop('disabled', true);
            } else if (!r.find('.bi-telephone-x').length && !r.find('.bi-check2-all').length && !r.find('.bi-dash-circle').length) {
                $(this).prop('disabled', false);
            }
        });
        fpAttSmsUpdateCount();
    });
})();
</script>
