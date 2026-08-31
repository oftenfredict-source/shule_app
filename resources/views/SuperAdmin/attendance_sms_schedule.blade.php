@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <style>
        :root { --primary-red: #940000; }
        .card, .btn, .form-control, .form-select, .alert { border-radius: 0 !important; }
        .schedule-pill { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        #autoTimingPanel { border-left: 3px solid var(--primary-red); background: #fffafafa; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-red);">School Attendance SMS Schedule</h4>
            <p class="text-muted small mb-0">Set frequency, send method (manual/auto), and auto-send timing per school</p>
        </div>
        <a href="{{ route('superAdminDashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-grid-alt me-1"></i> Dashboard
        </a>
    </div>

    <div class="card border mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="small fw-bold text-muted mb-2">SELECT SCHOOL</label>
                    <select id="schoolID" class="form-select">
                        <option value="">-- Choose School --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">
                                {{ $school->school_name }}
                                @if($school->environment) ({{ $school->environment }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="small fw-bold text-muted mb-2">FREQUENCY</label>
                    <select id="schedule" class="form-select" disabled>
                        <option value="none">None (disabled)</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="small fw-bold text-muted mb-2">SEND METHOD</label>
                    <select id="send_method" class="form-select" disabled>
                        <option value="manual">Manual (teachers/admin send)</option>
                        <option value="auto">Automatic (system sends)</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary-custom w-100" id="btnSaveSchedule" disabled>
                        <i class="bx bx-save me-1"></i> Save
                    </button>
                </div>
            </div>

            <div id="autoTimingPanel" class="mt-4 p-3 d-none">
                <h6 class="fw-bold mb-3"><i class="bx bx-time-five"></i> Automatic Send Timing</h6>
                <div class="row g-3">
                    <div class="col-md-3" id="field_send_time">
                        <label class="small fw-bold text-muted mb-1">TIME (24h)</label>
                        <input type="time" id="send_time" class="form-control" value="18:00">
                        <small class="text-muted">Saa mfumo utatumia SMS</small>
                    </div>
                    <div class="col-md-3 d-none" id="field_day_of_week">
                        <label class="small fw-bold text-muted mb-1">DAY OF WEEK</label>
                        <select id="send_day_of_week" class="form-select">
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="7">Sunday</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="field_day_of_month">
                        <label class="small fw-bold text-muted mb-1">DAY OF MONTH</label>
                        <select id="send_day_of_month" class="form-select">
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ $d === 28 ? 'selected' : '' }}>{{ $d }}</option>
                            @endfor
                        </select>
                        <small class="text-muted">Ikiwa mwezi una siku chache, siku ya mwisho itatumika</small>
                    </div>
                    <div class="col-md-3 d-none" id="field_send_month">
                        <label class="small fw-bold text-muted mb-1">MONTH</label>
                        <select id="send_month" class="form-select">
                            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $i => $m)
                                <option value="{{ $i + 1 }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="small text-muted mb-0 mt-2">
                    <strong>Manual:</strong> walimu/admin watatumia kitufe kwenye mfumo.
                    <strong>Auto:</strong> cron ya mfumo (`attendance:send-auto-sms`) inatafuta shule zenye ratiba na kutuma SMS.
                </p>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">All Schools Overview</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>School</th>
                            <th>Environment</th>
                            <th>Frequency</th>
                            <th>Method</th>
                            <th>Auto Timing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schools as $school)
                            @php
                                $row = $scheduleRows[$school->schoolID] ?? null;
                                $sch = $row?->schedule ?? 'none';
                                $method = $row?->send_method ?? 'manual';
                                $summary = app(\App\Services\AttendanceSmsService::class)->formatAutoScheduleSummary($row);
                            @endphp
                            <tr>
                                <td>{{ $school->school_name }}</td>
                                <td>{{ $school->environment ?? '-' }}</td>
                                <td>
                                    <span class="badge schedule-pill {{ $sch === 'none' ? 'bg-secondary' : 'bg-success' }}">
                                        {{ ucfirst($sch) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($method) }}</td>
                                <td class="small text-muted">{{ $summary }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1" style="color: var(--primary-red);">Fingerprint Attendance SMS Schedule</h5>
            <p class="text-muted small mb-3">Separate schedule for parent SMS based on fingerprint records (manual send always allowed when enabled).</p>
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="small fw-bold text-muted mb-2">SELECT SCHOOL</label>
                    <select id="fpSchoolID" class="form-select">
                        <option value="">-- Same school as above --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="small fw-bold text-muted mb-2">FREQUENCY</label>
                    <select id="fp_schedule" class="form-select" disabled>
                        <option value="none">None (disabled)</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="small fw-bold text-muted mb-2">SEND METHOD</label>
                    <select id="fp_send_method" class="form-select" disabled>
                        <option value="manual">Manual</option>
                        <option value="auto">Automatic</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary-custom w-100" id="btnSaveFpSchedule" disabled>
                        <i class="bx bx-save me-1"></i> Save
                    </button>
                </div>
            </div>
            <div id="fpAutoTimingPanel" class="mt-4 p-3 d-none" style="border-left: 3px solid var(--primary-red); background: #fffafafa;">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted mb-1">TIME (24h)</label>
                        <input type="time" id="fp_send_time" class="form-control" value="18:30">
                    </div>
                    <div class="col-md-3 d-none" id="fp_field_day_of_week">
                        <label class="small fw-bold text-muted mb-1">DAY OF WEEK</label>
                        <select id="fp_send_day_of_week" class="form-select">
                            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $d)
                            <option value="{{ $i + 1 }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="fp_field_day_of_month">
                        <label class="small fw-bold text-muted mb-1">DAY OF MONTH</label>
                        <select id="fp_send_day_of_month" class="form-select">
                            @for($d = 1; $d <= 31; $d++)
                            <option value="{{ $d }}" {{ $d === 28 ? 'selected' : '' }}>{{ $d }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 d-none" id="fp_field_send_month">
                        <label class="small fw-bold text-muted mb-1">MONTH</label>
                        <select id="fp_send_month" class="form-select">
                            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $i => $m)
                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const schoolSelect = document.getElementById('schoolID');
    const scheduleSelect = document.getElementById('schedule');
    const sendMethodSelect = document.getElementById('send_method');
    const autoPanel = document.getElementById('autoTimingPanel');
    const saveBtn = document.getElementById('btnSaveSchedule');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function toggleAutoFields() {
        const schedule = scheduleSelect.value;
        const method = sendMethodSelect.value;
        const showAuto = schedule !== 'none' && method === 'auto';

        autoPanel.classList.toggle('d-none', !showAuto);
        document.getElementById('field_day_of_week').classList.toggle('d-none', !(showAuto && schedule === 'weekly'));
        document.getElementById('field_day_of_month').classList.toggle('d-none', !(showAuto && (schedule === 'monthly' || schedule === 'yearly')));
        document.getElementById('field_send_month').classList.toggle('d-none', !(showAuto && schedule === 'yearly'));
    }

    function setFormEnabled(enabled) {
        scheduleSelect.disabled = !enabled;
        sendMethodSelect.disabled = !enabled;
        saveBtn.disabled = !enabled;
        toggleAutoFields();
    }

    scheduleSelect.addEventListener('change', function () {
        if (this.value === 'none') {
            sendMethodSelect.value = 'manual';
        }
        toggleAutoFields();
    });
    sendMethodSelect.addEventListener('change', toggleAutoFields);

    schoolSelect.addEventListener('change', async function () {
        if (!this.value) {
            setFormEnabled(false);
            return;
        }

        setFormEnabled(true);
        scheduleSelect.value = 'none';
        sendMethodSelect.value = 'manual';

        try {
            const res = await fetch('{{ url("super-admin/attendance-sms-schedule") }}/' + this.value, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                scheduleSelect.value = data.schedule || 'none';
                sendMethodSelect.value = data.send_method || 'manual';
                if (data.send_time) document.getElementById('send_time').value = data.send_time;
                if (data.send_day_of_week) document.getElementById('send_day_of_week').value = data.send_day_of_week;
                if (data.send_day_of_month) document.getElementById('send_day_of_month').value = data.send_day_of_month;
                if (data.send_month) document.getElementById('send_month').value = data.send_month;
            }
        } catch (e) { /* keep defaults */ }

        toggleAutoFields();
    });

    saveBtn.addEventListener('click', async function () {
        const schoolID = schoolSelect.value;
        if (!schoolID) return;

        const payload = {
            schoolID: schoolID,
            schedule: scheduleSelect.value,
            send_method: sendMethodSelect.value,
            send_time: document.getElementById('send_time').value || null,
            send_day_of_week: document.getElementById('send_day_of_week').value || null,
            send_day_of_month: document.getElementById('send_day_of_month').value || null,
            send_month: document.getElementById('send_month').value || null,
        };

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        try {
            const res = await fetch('{{ route("superadmin.attendance_sms_schedule.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Saved', text: data.message, timer: 2000, showConfirmButton: false });
                } else {
                    alert(data.message);
                }
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(data.error || 'Failed to save');
            }
        } catch (e) {
            alert('Network error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bx bx-save me-1"></i> Save';
        }
    });

    const fpSchoolSelect = document.getElementById('fpSchoolID');
    const fpScheduleSelect = document.getElementById('fp_schedule');
    const fpSendMethodSelect = document.getElementById('fp_send_method');
    const fpAutoPanel = document.getElementById('fpAutoTimingPanel');
    const fpSaveBtn = document.getElementById('btnSaveFpSchedule');

    function fpEffectiveSchoolId() {
        return fpSchoolSelect.value || schoolSelect.value;
    }

    function toggleFpAutoFields() {
        const schedule = fpScheduleSelect.value;
        const method = fpSendMethodSelect.value;
        const showAuto = schedule !== 'none' && method === 'auto';
        fpAutoPanel.classList.toggle('d-none', !showAuto);
        document.getElementById('fp_field_day_of_week').classList.toggle('d-none', !(showAuto && schedule === 'weekly'));
        document.getElementById('fp_field_day_of_month').classList.toggle('d-none', !(showAuto && (schedule === 'monthly' || schedule === 'yearly')));
        document.getElementById('fp_field_send_month').classList.toggle('d-none', !(showAuto && schedule === 'yearly'));
    }

    function setFpFormEnabled(enabled) {
        fpScheduleSelect.disabled = !enabled;
        fpSendMethodSelect.disabled = !enabled;
        fpSaveBtn.disabled = !enabled;
        toggleFpAutoFields();
    }

    fpScheduleSelect.addEventListener('change', toggleFpAutoFields);
    fpSendMethodSelect.addEventListener('change', toggleFpAutoFields);

    schoolSelect.addEventListener('change', function () {
        if (!fpSchoolSelect.value && this.value) {
            loadFpSchedule(this.value);
        }
    });

    fpSchoolSelect.addEventListener('change', function () {
        const id = fpEffectiveSchoolId();
        if (id) loadFpSchedule(id);
        else setFpFormEnabled(false);
    });

    async function loadFpSchedule(schoolId) {
        setFpFormEnabled(true);
        try {
            const res = await fetch('{{ url("super-admin/fingerprint-attendance-sms-schedule") }}/' + schoolId, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                fpScheduleSelect.value = data.schedule || 'none';
                fpSendMethodSelect.value = data.send_method || 'manual';
                if (data.send_time) document.getElementById('fp_send_time').value = data.send_time;
                if (data.send_day_of_week) document.getElementById('fp_send_day_of_week').value = data.send_day_of_week;
                if (data.send_day_of_month) document.getElementById('fp_send_day_of_month').value = data.send_day_of_month;
                if (data.send_month) document.getElementById('fp_send_month').value = data.send_month;
            }
        } catch (e) {}
        toggleFpAutoFields();
    }

    fpSaveBtn.addEventListener('click', async function () {
        const schoolID = fpEffectiveSchoolId();
        if (!schoolID) return alert('Select a school first');

        const payload = {
            schoolID: schoolID,
            schedule: fpScheduleSelect.value,
            send_method: fpSendMethodSelect.value,
            send_time: document.getElementById('fp_send_time').value || null,
            send_day_of_week: document.getElementById('fp_send_day_of_week').value || null,
            send_day_of_month: document.getElementById('fp_send_day_of_month').value || null,
            send_month: document.getElementById('fp_send_month').value || null,
        };

        fpSaveBtn.disabled = true;
        try {
            const res = await fetch('{{ route("superadmin.fingerprint_attendance_sms_schedule.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            alert(data.message || (data.success ? 'Saved' : 'Failed'));
            if (data.success) setTimeout(() => location.reload(), 800);
        } catch (e) {
            alert('Network error');
        } finally {
            fpSaveBtn.disabled = false;
        }
    });
});
</script>
@endsection
