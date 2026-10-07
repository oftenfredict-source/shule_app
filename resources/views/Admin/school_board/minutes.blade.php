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
    .minute-preview { max-width: 320px; white-space: pre-wrap; font-size: 0.82rem; color: #555; }
    .meeting-meta { font-size: 0.8rem; color: #777; }
    .upcoming-box {
        border: 1px solid #ffe0a3; background: #fffaf0; padding: 14px 16px; margin-bottom: 1rem;
    }
    .upcoming-box h6 { margin: 0 0 10px; font-weight: 800; color: #946b00; }
    .upcoming-item {
        display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px;
        padding: 10px 0; border-top: 1px solid #f0e2c0;
    }
    .upcoming-item:first-of-type { border-top: 0; padding-top: 0; }
    .lock-note { font-size: 0.82rem; color: #946b00; }
</style>

@php
    $draftCount = $minutes->where('status', 'Draft')->count();
    $submittedCount = $minutes->where('status', 'Submitted')->count();
    $approvedCount = $minutes->where('status', 'Approved')->count();
    $canRecord = $recordableMeetings->isNotEmpty();
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
            <div class="label">Total Minutes</div>
            <div class="value">{{ $minutes->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Draft</div>
            <div class="value">{{ $draftCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Submitted</div>
            <div class="value">{{ $submittedCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Approved</div>
            <div class="value">{{ $approvedCount }}</div>
        </div>
    </div>

    @if($upcomingMeetings->isNotEmpty())
        <div class="upcoming-box">
            <h6><i class="fa fa-clock-o"></i> Upcoming Meetings — minutes locked</h6>
            <p class="lock-note mb-2">
                You can see these meetings here, but you cannot type minutes until the meeting start time arrives
                (status becomes <strong>Ongoing</strong>).
            </p>
            @foreach($upcomingMeetings as $um)
                @php
                    $startLabel = $um->start_time ? substr((string) $um->start_time, 0, 5) : '00:00';
                @endphp
                <div class="upcoming-item">
                    <div>
                        <strong>{{ $um->title }}</strong>
                        <div class="meeting-meta">
                            {{ $um->meeting_date->format('d M Y') }} · {{ $startLabel }}
                            · {{ $um->meeting_type }}
                            @if($um->committee) · {{ $um->committee->committee_name }} @endif
                            @if($um->venue) · {{ $um->venue }} @endif
                        </div>
                    </div>
                    <span class="badge bg-primary align-self-center">Upcoming</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-file-text-o"></i> Minutes
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm" id="openAddMinuteBtn"
                @if(!$canRecord) disabled title="Available only for Ongoing or Completed meetings" @endif>
                <i class="fa fa-plus"></i> Record Minutes
            </button>
        </div>
    </div>

    @if($editableMeetings->isEmpty() && $upcomingMeetings->isEmpty())
        <div class="alert alert-warning">
            No meetings found. Schedule a meeting under <strong>Meetings</strong> first.
        </div>
    @elseif(!$canRecord && $upcomingMeetings->isNotEmpty())
        <div class="alert alert-info py-2">
            No meetings are ready for minutes yet. Wait until an upcoming meeting becomes <strong>Ongoing</strong>.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Meeting</th>
                            <th>Prepared By</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($minutes as $index => $minute)
                            @php
                                $statusClass = match($minute->status) {
                                    'Approved' => 'bg-success',
                                    'Submitted' => 'bg-info text-dark',
                                    default => 'bg-secondary',
                                };
                                $meeting = $minute->meeting;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $minute->title }}</strong>
                                    @if($minute->decisions_summary)
                                        <div class="minute-preview mt-1"><em>Decisions:</em> {{ \Illuminate\Support\Str::limit($minute->decisions_summary, 100) }}</div>
                                    @elseif($minute->content)
                                        <div class="minute-preview mt-1">{{ \Illuminate\Support\Str::limit($minute->content, 100) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($meeting)
                                        <strong>{{ $meeting->title }}</strong>
                                        <div class="meeting-meta">
                                            {{ $meeting->meeting_date ? $meeting->meeting_date->format('d M Y') : '—' }}
                                            · {{ $meeting->meeting_type }}
                                            @if($meeting->committee)
                                                · {{ $meeting->committee->committee_name }}
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Meeting removed</span>
                                    @endif
                                </td>
                                <td>{{ optional($minute->preparer)->full_name ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $minute->status }}</span>
                                    @if($minute->status === 'Approved' && $minute->approved_at)
                                        <div class="meeting-meta mt-1">
                                            {{ optional($minute->approver)->full_name ?: '' }}
                                            {{ $minute->approved_at->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="small">{{ $minute->updated_at ? $minute->updated_at->format('d M Y H:i') : '—' }}</td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary view-minute-btn"
                                        data-title="{{ $minute->title }}"
                                        data-content="{{ $minute->content }}"
                                        data-decisions="{{ $minute->decisions_summary }}"
                                        data-meeting="{{ optional($meeting)->title }}"
                                        data-date="{{ $meeting && $meeting->meeting_date ? $meeting->meeting_date->format('d M Y') : '' }}"
                                        data-status="{{ $minute->status }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @if($meeting && $meeting->allowsMinutes())
                                        <button type="button" class="btn btn-sm btn-warning edit-minute-btn"
                                            data-id="{{ $minute->minuteID }}"
                                            data-meeting="{{ $minute->meetingID }}"
                                            data-title="{{ $minute->title }}"
                                            data-content="{{ $minute->content }}"
                                            data-decisions="{{ $minute->decisions_summary }}"
                                            data-prepared="{{ $minute->prepared_by }}"
                                            data-approved="{{ $minute->approved_by }}"
                                            data-status="{{ $minute->status }}"
                                            data-notes="{{ $minute->notes }}">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-minute-btn"
                                            data-id="{{ $minute->minuteID }}"
                                            data-title="{{ $minute->title }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-warning text-dark" title="Meeting still upcoming">Locked</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No minutes yet. Click <strong>Record Minutes</strong> after scheduling a meeting.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create / Edit Minutes Modal --}}
<div class="modal fade" id="minuteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="minuteModalTitle">Record Minutes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="minuteForm">
                @csrf
                <input type="hidden" name="minuteID" id="minuteID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Meeting <span class="text-danger">*</span></label>
                            <select class="form-control" name="meetingID" id="meetingID" required>
                                <option value="">— Select Ongoing / Completed meeting —</option>
                                @foreach($editableMeetings as $m)
                                    @php
                                        $hasMinute = $minutes->contains('meetingID', $m->meetingID);
                                        $startLabel = $m->start_time ? substr((string) $m->start_time, 0, 5) : '';
                                    @endphp
                                    <option value="{{ $m->meetingID }}"
                                        data-title="Minutes: {{ $m->title }}"
                                        data-has-minute="{{ $hasMinute ? '1' : '0' }}">
                                        {{ $m->meeting_date->format('d M Y') }}
                                        @if($startLabel) {{ $startLabel }} @endif
                                        — {{ $m->title }}
                                        ({{ $m->status }})
                                        @if($hasMinute) [has minutes] @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Upcoming meetings are hidden here until start time.</small>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Minutes Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Draft">Draft</option>
                                <option value="Submitted">Submitted</option>
                                <option value="Approved">Approved</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prepared By</label>
                            <select class="form-control" name="prepared_by" id="prepared_by">
                                <option value="">— Select —</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="approverWrap" style="display:none;">
                            <label class="form-label">Approved By</label>
                            <select class="form-control" name="approved_by" id="approved_by">
                                <option value="">— Select —</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Minutes Content</label>
                            <textarea class="form-control" name="content" id="content" rows="8"
                                placeholder="Record discussion points, attendance notes, and proceedings..."></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Decisions / Resolutions Summary</label>
                            <textarea class="form-control" name="decisions_summary" id="decisions_summary" rows="3"
                                placeholder="Key decisions made in this meeting..."></textarea>
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="minuteSubmitBtn">Save Minutes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Minutes Modal --}}
<div class="modal fade" id="viewMinuteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewMinuteTitle">Minutes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 text-muted" id="viewMinuteMeta"></p>
                <p><span class="badge bg-secondary" id="viewMinuteStatus"></span></p>
                <h6 style="color:#940000;font-weight:700;">Content</h6>
                <div id="viewMinuteContent" style="white-space:pre-wrap;margin-bottom:1rem;"></div>
                <h6 style="color:#940000;font-weight:700;">Decisions</h6>
                <div id="viewMinuteDecisions" style="white-space:pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
            </div>
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

    ready(function () {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const minuteForm = document.getElementById('minuteForm');
    const minuteModal = document.getElementById('minuteModal');
    if (!minuteForm || !minuteModal) return;

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

    function toggleApprover() {
        const wrap = document.getElementById('approverWrap');
        wrap.style.display = document.getElementById('status').value === 'Approved' ? '' : 'none';
    }

    function filterMeetingOptions(editingMeetingId) {
        const select = document.getElementById('meetingID');
        Array.from(select.options).forEach(function (opt) {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            const has = opt.dataset.hasMinute === '1';
            const isCurrent = editingMeetingId && opt.value === String(editingMeetingId);
            opt.hidden = has && !isCurrent;
        });
    }

    function resetMinuteForm() {
        minuteForm.reset();
        document.getElementById('minuteID').value = '';
        document.getElementById('status').value = 'Draft';
        document.getElementById('minuteModalTitle').textContent = 'Record Minutes';
        document.getElementById('minuteSubmitBtn').textContent = 'Save Minutes';
        filterMeetingOptions(null);
        toggleApprover();
    }

    document.getElementById('status').addEventListener('change', toggleApprover);

    document.getElementById('meetingID').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const titleInput = document.getElementById('title');
        if (opt && opt.dataset.title && !document.getElementById('minuteID').value) {
            if (!titleInput.value) titleInput.value = opt.dataset.title;
        }
    });

    const openBtn = document.getElementById('openAddMinuteBtn');
    if (openBtn && !openBtn.disabled) {
        openBtn.addEventListener('click', function () {
            resetMinuteForm();
            openModal(minuteModal);
        });
    } else if (openBtn) {
        openBtn.addEventListener('click', function () {
            Swal.fire('Upcoming', 'Minutes can only be typed after the meeting start time (status Ongoing or Completed).', 'info');
        });
    }

    document.querySelectorAll('.edit-minute-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('minuteID').value = btn.dataset.id || '';
            filterMeetingOptions(btn.dataset.meeting || '');
            document.getElementById('meetingID').value = btn.dataset.meeting || '';
            document.getElementById('title').value = btn.dataset.title || '';
            document.getElementById('content').value = btn.dataset.content || '';
            document.getElementById('decisions_summary').value = btn.dataset.decisions || '';
            document.getElementById('prepared_by').value = btn.dataset.prepared || '';
            document.getElementById('approved_by').value = btn.dataset.approved || '';
            document.getElementById('status').value = btn.dataset.status || 'Draft';
            document.getElementById('notes').value = btn.dataset.notes || '';
            document.getElementById('minuteModalTitle').textContent = 'Edit Minutes';
            document.getElementById('minuteSubmitBtn').textContent = 'Update Minutes';
            toggleApprover();
            openModal(minuteModal);
        });
    });

    document.querySelectorAll('.view-minute-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('viewMinuteTitle').textContent = btn.dataset.title || 'Minutes';
            document.getElementById('viewMinuteMeta').textContent =
                (btn.dataset.meeting || '') + (btn.dataset.date ? ' · ' + btn.dataset.date : '');
            document.getElementById('viewMinuteStatus').textContent = btn.dataset.status || '';
            document.getElementById('viewMinuteContent').textContent = btn.dataset.content || '—';
            document.getElementById('viewMinuteDecisions').textContent = btn.dataset.decisions || '—';
            openModal(document.getElementById('viewMinuteModal'));
        });
    });

    minuteForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('minuteID').value;
        const url = id
            ? @json(route('admin.school_board.minutes.update'))
            : @json(route('admin.school_board.minutes.store'));
        const fd = new FormData(minuteForm);

        fetch(url, { method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not save minutes.'), 'error');
                    return;
                }
                closeModal(minuteModal);
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while saving minutes.', 'error');
            });
    });

    document.querySelectorAll('.delete-minute-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete minutes?',
                text: btn.dataset.title || 'This record will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('minuteID', btn.dataset.id || '');
                fetch(@json(route('admin.school_board.minutes.delete')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not delete minutes.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while deleting minutes.', 'error');
                });
            });
        });
    });

    toggleApprover();
    });
})();
</script>
