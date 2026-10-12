@php
    $user_type = $user_type ?? Session::get('user_type');
@endphp
@if($user_type == 'Admin')
    @include('includes.Admin_nav')
@elseif($user_type == 'Staff')
    @include('includes.staff_nav')
@else
    @include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .table, .form-control, .form-select, .modal {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .sx-board-wrap { padding: 1rem 1.25rem 2rem; }
    .sx-board-hero {
        background: linear-gradient(135deg, #940000 0%, #6b0000 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }
    .sx-board-hero h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 0.35rem; }
    .sx-board-hero p { margin: 0; opacity: 0.9; font-size: 0.92rem; }
    .sx-board-nav { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; }
    .sx-board-nav a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 12px; border: 1px solid #e8e8e8; background: #fff;
        color: #333; text-decoration: none; font-size: 0.85rem; font-weight: 600;
    }
    .sx-board-nav a:hover { border-color: #940000; color: #940000; }
    .sx-board-nav a.active { background: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; border-radius: 0 !important; }
    .btn-primary-custom:hover { background: #b30000; border-color: #b30000; color: #fff; }
    .bg-primary-custom { background: #940000 !important; }
    .card, .btn, .form-control, .form-select, .modal-content { border-radius: 0 !important; }
    .member-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem; }
    .member-stat {
        background: #fff; border: 1px solid #eee; padding: 12px 16px; min-width: 140px;
    }
    .member-stat .label { font-size: 0.75rem; color: #777; text-transform: uppercase; font-weight: 700; }
    .member-stat .value { font-size: 1.4rem; font-weight: 800; color: #940000; }
    .table thead th { white-space: nowrap; }
    .meeting-agenda { max-width: 280px; white-space: pre-wrap; font-size: 0.82rem; color: #555; }
    .attendee-list { font-size: 0.8rem; }
    .attendee-list .badge { margin: 2px; }
    .attendees-checkboxes {
        max-height: 180px; overflow-y: auto; border: 1px solid #e8e8e8; padding: 10px;
    }
    .attendees-checkboxes label {
        display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.88rem;
    }
</style>

@php
    $today = now()->toDateString();
    $upcoming = $meetings->where('status', 'Scheduled')->count();
    $ongoing = $meetings->where('status', 'Ongoing')->count();
    $completed = $meetings->where('status', 'Completed')->count();
    $cancelled = $meetings->whereIn('status', ['Cancelled', 'Postponed'])->count();
@endphp

<div class="sx-board-wrap">
    <div class="sx-board-hero">
        <h1><i class="fa fa-university"></i> School Board</h1>
        <p>{{ $subtitle }}</p>
    </div>

    <div class="sx-board-nav">
        @foreach($menu as $item)
            <a href="{{ route($item['route']) }}" class="{{ $section === $item['key'] ? 'active' : '' }}">
                <i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    <div class="member-stats">
        <div class="member-stat">
            <div class="label">Total Meetings</div>
            <div class="value">{{ $meetings->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Upcoming</div>
            <div class="value">{{ $upcoming }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Ongoing</div>
            <div class="value">{{ $ongoing }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Completed</div>
            <div class="value">{{ $completed }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Cancelled / Postponed</div>
            <div class="value">{{ $cancelled }}</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-calendar"></i> Meetings
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm" id="openAddMeetingBtn">
                <i class="fa fa-plus"></i> Schedule Meeting
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="boardMeetingsTable">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Venue</th>
                            <th>Chair</th>
                            <th>Attendees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $index => $meeting)
                            @php
                                $start = $meeting->start_time ? substr((string) $meeting->start_time, 0, 5) : '';
                                $end = $meeting->end_time ? substr((string) $meeting->end_time, 0, 5) : '';
                                $attendeeIds = $meeting->attendees->pluck('memberID')->implode(',');
                                $statusClass = match($meeting->status) {
                                    'Completed' => 'bg-success',
                                    'Ongoing' => 'bg-info text-dark',
                                    'Cancelled' => 'bg-danger',
                                    'Postponed' => 'bg-warning text-dark',
                                    default => 'bg-primary',
                                };
                                $statusLabel = $meeting->status === 'Scheduled' ? 'Upcoming' : $meeting->status;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $meeting->title }}</strong>
                                    @if($meeting->agenda)
                                        <div class="meeting-agenda mt-1">{{ \Illuminate\Support\Str::limit($meeting->agenda, 120) }}</div>
                                    @endif
                                </td>
                                <td class="small text-nowrap">
                                    {{ $meeting->meeting_date->format('d M Y') }}
                                    @if($start)
                                        <br>{{ $start }}@if($end) - {{ $end }}@endif
                                    @endif
                                </td>
                                <td>
                                    {{ $meeting->meeting_type }}
                                    @if($meeting->committee)
                                        <br><span class="badge" style="background:#fff5f5;color:#940000;border:1px solid rgba(148,0,0,.25);">
                                            {{ $meeting->committee->committee_name }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $meeting->venue ?: '-' }}</td>
                                <td>{{ optional($meeting->chair)->full_name ?: '-' }}</td>
                                <td class="attendee-list">
                                    @if($meeting->attendees->isEmpty())
                                        <span class="text-muted">None</span>
                                    @else
                                        @foreach($meeting->attendees as $att)
                                            @php
                                                $aStatus = $att->pivot->attendance_status ?? 'Expected';
                                                $aClass = match($aStatus) {
                                                    'Present' => 'bg-success',
                                                    'Absent' => 'bg-danger',
                                                    'Excused' => 'bg-secondary',
                                                    default => 'bg-light text-dark border',
                                                };
                                            @endphp
                                            <span class="badge {{ $aClass }}"
                                                title="Click to change attendance"
                                                style="cursor:pointer;"
                                                data-meeting="{{ $meeting->meetingID }}"
                                                data-member="{{ $att->memberID }}"
                                                data-status="{{ $aStatus }}"
                                                data-name="{{ $att->full_name }}"
                                                onclick="cycleAttendance(this)">
                                                {{ $att->first_name }} ({{ substr($aStatus, 0, 1) }})
                                            </span>
                                        @endforeach
                                    @endif
                                </td>
                                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-warning edit-meeting-btn"
                                        data-id="{{ $meeting->meetingID }}"
                                        data-title="{{ $meeting->title }}"
                                        data-type="{{ $meeting->meeting_type }}"
                                        data-committee="{{ $meeting->committeeID }}"
                                        data-date="{{ $meeting->meeting_date->format('Y-m-d') }}"
                                        data-start="{{ $start }}"
                                        data-end="{{ $end }}"
                                        data-venue="{{ $meeting->venue }}"
                                        data-agenda="{{ $meeting->agenda }}"
                                        data-status="{{ $meeting->status }}"
                                        data-chair="{{ $meeting->chair_memberID }}"
                                        data-notes="{{ $meeting->notes }}"
                                        data-attendees="{{ $attendeeIds }}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-meeting-btn"
                                        data-id="{{ $meeting->meetingID }}"
                                        data-title="{{ $meeting->title }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No meetings yet. Click <strong>Schedule Meeting</strong> to add the first one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Schedule / Edit Meeting Modal --}}
<div class="modal fade" id="meetingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="meetingModalTitle">Schedule Meeting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="meetingForm">
                @csrf
                <input type="hidden" name="meetingID" id="meetingID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Scheduled">Upcoming (Scheduled)</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                                <option value="Postponed">Postponed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="meeting_type" id="meeting_type" required>
                                <option value="Full Board">Full Board</option>
                                <option value="Committee">Committee</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3" id="committeeWrap" style="display:none;">
                            <label class="form-label">Committee <span class="text-danger">*</span></label>
                            <select class="form-control" name="committeeID" id="committeeID">
                                <option value="">- Select committee -</option>
                                @foreach($committees as $c)
                                    <option value="{{ $c->committeeID }}">{{ $c->committee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="meeting_date" id="meeting_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" id="start_time">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" id="end_time">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Venue</label>
                            <input type="text" class="form-control" name="venue" id="venue" placeholder="e.g. Board Room">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chairperson</label>
                            <select class="form-control" name="chair_memberID" id="chair_memberID">
                                <option value="">- Select -</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Agenda</label>
                            <textarea class="form-control" name="agenda" id="agenda" rows="3" placeholder="List agenda items..."></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Expected Attendees</label>
                            @if($boardMembers->isEmpty())
                                <p class="text-muted small mb-0">No active board members. Register them under Board Members first.</p>
                            @else
                                <div class="attendees-checkboxes" id="attendeesBox">
                                    @foreach($boardMembers as $bm)
                                        <label>
                                            <input type="checkbox" name="attendee_ids[]" value="{{ $bm->memberID }}" class="attendee-cb">
                                            {{ $bm->full_name }}
                                            @if($bm->position) <span class="text-muted">({{ $bm->position->position_name }})</span> @endif
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="meetingSubmitBtn">Save Meeting</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    const attendanceCycle = ['Expected', 'Present', 'Absent', 'Excused'];

    window.cycleAttendance = function (el) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
        const current = el.dataset.status || 'Expected';
        const idx = attendanceCycle.indexOf(current);
        const next = attendanceCycle[(idx + 1) % attendanceCycle.length];

        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('meetingID', el.dataset.meeting || '');
        fd.append('memberID', el.dataset.member || '');
        fd.append('attendance_status', next);

        fetch(@json(route('admin.school_board.meetings.attendance')), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: fd,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            if (!res.ok || !res.data.success) {
                Swal.fire('Error', (res.data && res.data.message) || 'Could not update attendance.', 'error');
                return;
            }
            window.location.reload();
        })
        .catch(function () {
            Swal.fire('Error', 'Network error while updating attendance.', 'error');
        });
    };

    ready(function () {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const meetingForm = document.getElementById('meetingForm');
    const meetingModal = document.getElementById('meetingModal');
    if (!meetingForm || !meetingModal) return;

    function openModal(el) {
        if (!el) return;
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
            jQuery(el).modal('show');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function closeModal(el) {
        if (!el) return;
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
            jQuery(el).modal('hide');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).hide();
        }
    }

    function jsonHeaders() {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
        };
    }

    function parseErrorMessage(data, fallback) {
        if (!data) return fallback;
        if (data.message) return data.message;
        if (data.errors) {
            try { return Object.values(data.errors).flat().join('\n'); } catch (e) {}
        }
        return fallback;
    }

    function toggleCommittee() {
        const type = document.getElementById('meeting_type').value;
        const wrap = document.getElementById('committeeWrap');
        const select = document.getElementById('committeeID');
        if (type === 'Committee') {
            wrap.style.display = '';
            select.required = true;
        } else {
            wrap.style.display = 'none';
            select.required = false;
            select.value = '';
        }
    }

    function clearAttendees() {
        document.querySelectorAll('.attendee-cb').forEach(function (cb) { cb.checked = false; });
    }

    function setAttendees(idsCsv) {
        clearAttendees();
        const ids = (idsCsv || '').split(',').filter(Boolean);
        document.querySelectorAll('.attendee-cb').forEach(function (cb) {
            if (ids.indexOf(cb.value) !== -1) cb.checked = true;
        });
    }

    function resetMeetingForm() {
        meetingForm.reset();
        document.getElementById('meetingID').value = '';
        document.getElementById('status').value = 'Scheduled';
        document.getElementById('meeting_type').value = 'Full Board';
        document.getElementById('meetingModalTitle').textContent = 'Schedule Meeting';
        document.getElementById('meetingSubmitBtn').textContent = 'Save Meeting';
        clearAttendees();
        toggleCommittee();
    }

    document.getElementById('meeting_type').addEventListener('change', toggleCommittee);

    document.getElementById('openAddMeetingBtn').addEventListener('click', function () {
        resetMeetingForm();
        openModal(meetingModal);
    });

    document.querySelectorAll('.edit-meeting-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('meetingID').value = btn.dataset.id || '';
            document.getElementById('title').value = btn.dataset.title || '';
            document.getElementById('meeting_type').value = btn.dataset.type || 'Full Board';
            document.getElementById('committeeID').value = btn.dataset.committee || '';
            document.getElementById('meeting_date').value = btn.dataset.date || '';
            document.getElementById('start_time').value = btn.dataset.start || '';
            document.getElementById('end_time').value = btn.dataset.end || '';
            document.getElementById('venue').value = btn.dataset.venue || '';
            document.getElementById('agenda').value = btn.dataset.agenda || '';
            document.getElementById('status').value = btn.dataset.status || 'Scheduled';
            document.getElementById('chair_memberID').value = btn.dataset.chair || '';
            document.getElementById('notes').value = btn.dataset.notes || '';
            setAttendees(btn.dataset.attendees || '');
            document.getElementById('meetingModalTitle').textContent = 'Edit Meeting';
            document.getElementById('meetingSubmitBtn').textContent = 'Update Meeting';
            toggleCommittee();
            openModal(meetingModal);
        });
    });

    meetingForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('meetingID').value;
        const url = id
            ? @json(route('admin.school_board.meetings.update'))
            : @json(route('admin.school_board.meetings.store'));
        const fd = new FormData(meetingForm);

        fetch(url, { method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not save meeting.'), 'error');
                    return;
                }
                closeModal(meetingModal);
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while saving meeting.', 'error');
            });
    });

    document.querySelectorAll('.delete-meeting-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete meeting?',
                text: btn.dataset.title || 'This meeting will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('meetingID', btn.dataset.id || '');
                fetch(@json(route('admin.school_board.meetings.delete')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not delete meeting.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while deleting meeting.', 'error');
                });
            });
        });
    });

    toggleCommittee();
    });
})();
</script>
