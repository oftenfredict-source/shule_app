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
    .res-preview { max-width: 300px; white-space: pre-wrap; font-size: 0.82rem; color: #555; }
    .meeting-meta { font-size: 0.8rem; color: #777; }
    .upcoming-box {
        border: 1px solid #ffe0a3; background: #fffaf0; padding: 14px 16px; margin-bottom: 1rem;
    }
    .upcoming-box h6 { margin: 0 0 8px; font-weight: 800; color: #946b00; }
    .lock-note { font-size: 0.82rem; color: #946b00; margin-bottom: 0; }
</style>

@php
    $proposed = $resolutions->where('status', 'Proposed')->count();
    $adopted = $resolutions->where('status', 'Adopted')->count();
    $implemented = $resolutions->where('status', 'Implemented')->count();
    $other = $resolutions->whereIn('status', ['Rejected', 'Deferred'])->count();
    $canRecord = $sourceMeetings->isNotEmpty();
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
            <div class="label">Total</div>
            <div class="value">{{ $resolutions->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Proposed</div>
            <div class="value">{{ $proposed }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Adopted</div>
            <div class="value">{{ $adopted }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Implemented</div>
            <div class="value">{{ $implemented }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Rejected / Deferred</div>
            <div class="value">{{ $other }}</div>
        </div>
    </div>

    @if($upcomingMeetings->isNotEmpty())
        <div class="upcoming-box">
            <h6><i class="fa fa-clock-o"></i> Upcoming meetings - resolutions locked</h6>
            <p class="lock-note">
                Resolutions can only be recorded from <strong>Ongoing</strong> or <strong>Completed</strong> meetings.
                {{ $upcomingMeetings->count() }} upcoming meeting(s) waiting for start time.
            </p>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-gavel"></i> Resolutions
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm" id="openAddResolutionBtn"
                @if(!$canRecord) disabled title="Need an Ongoing or Completed meeting" @endif>
                <i class="fa fa-plus"></i> Add Resolution
            </button>
        </div>
    </div>

    @if(!$canRecord)
        <div class="alert alert-info py-2">
            No Ongoing/Completed meetings available yet. Schedule a meeting and wait until it starts before adding resolutions.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Number</th>
                            <th>Title</th>
                            <th>Meeting</th>
                            <th>Proposed / Seconded</th>
                            <th>Assigned</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resolutions as $index => $res)
                            @php
                                $statusClass = match($res->status) {
                                    'Adopted' => 'bg-success',
                                    'Implemented' => 'bg-primary',
                                    'Rejected' => 'bg-danger',
                                    'Deferred' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                                $meeting = $res->meeting;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-nowrap"><strong>{{ $res->resolution_number ?: '-' }}</strong></td>
                                <td>
                                    <strong>{{ $res->title }}</strong>
                                    @if($res->description)
                                        <div class="res-preview mt-1">{{ \Illuminate\Support\Str::limit($res->description, 100) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($meeting)
                                        <strong>{{ $meeting->title }}</strong>
                                        <div class="meeting-meta">
                                            {{ $meeting->meeting_date ? $meeting->meeting_date->format('d M Y') : '-' }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ optional($res->proposer)->full_name ?: '-' }}
                                    <br><span class="text-muted">2nd: {{ optional($res->seconder)->full_name ?: '-' }}</span>
                                </td>
                                <td>{{ optional($res->assignee)->full_name ?: '-' }}</td>
                                <td class="small">
                                    {{ $res->implementation_deadline ? $res->implementation_deadline->format('d M Y') : '-' }}
                                </td>
                                <td><span class="badge {{ $statusClass }}">{{ $res->status }}</span></td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary view-resolution-btn"
                                        data-number="{{ $res->resolution_number }}"
                                        data-title="{{ $res->title }}"
                                        data-description="{{ $res->description }}"
                                        data-meeting="{{ optional($meeting)->title }}"
                                        data-status="{{ $res->status }}"
                                        data-date="{{ $res->resolution_date ? $res->resolution_date->format('d M Y') : '' }}"
                                        data-deadline="{{ $res->implementation_deadline ? $res->implementation_deadline->format('d M Y') : '' }}"
                                        data-proposer="{{ optional($res->proposer)->full_name }}"
                                        data-seconder="{{ optional($res->seconder)->full_name }}"
                                        data-assignee="{{ optional($res->assignee)->full_name }}"
                                        data-notes="{{ $res->notes }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning edit-resolution-btn"
                                        data-id="{{ $res->resolutionID }}"
                                        data-meeting="{{ $res->meetingID }}"
                                        data-number="{{ $res->resolution_number }}"
                                        data-title="{{ $res->title }}"
                                        data-description="{{ $res->description }}"
                                        data-proposed="{{ $res->proposed_by }}"
                                        data-seconded="{{ $res->seconded_by }}"
                                        data-assigned="{{ $res->assigned_to }}"
                                        data-date="{{ $res->resolution_date ? $res->resolution_date->format('Y-m-d') : '' }}"
                                        data-deadline="{{ $res->implementation_deadline ? $res->implementation_deadline->format('Y-m-d') : '' }}"
                                        data-status="{{ $res->status }}"
                                        data-notes="{{ $res->notes }}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-resolution-btn"
                                        data-id="{{ $res->resolutionID }}"
                                        data-title="{{ $res->title }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No resolutions yet. Click <strong>Add Resolution</strong> when a meeting is Ongoing or Completed.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Resolution Modal --}}
<div class="modal fade" id="resolutionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="resolutionModalTitle">Add Resolution</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resolutionForm">
                @csrf
                <input type="hidden" name="resolutionID" id="resolutionID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Meeting <span class="text-danger">*</span></label>
                            <select class="form-control" name="meetingID" id="meetingID" required>
                                <option value="">- Select Ongoing / Completed meeting -</option>
                                @foreach($sourceMeetings as $m)
                                    <option value="{{ $m->meetingID }}" data-date="{{ $m->meeting_date->format('Y-m-d') }}">
                                        {{ $m->meeting_date->format('d M Y') }} - {{ $m->title }} ({{ $m->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Resolution No.</label>
                            <input type="text" class="form-control" name="resolution_number" id="resolution_number" placeholder="Auto if blank">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Proposed">Proposed</option>
                                <option value="Adopted">Adopted</option>
                                <option value="Deferred">Deferred</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Implemented">Implemented</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description / Decision Text</label>
                            <textarea class="form-control" name="description" id="description" rows="4"
                                placeholder="Full wording of the resolution..."></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Proposed By</label>
                            <select class="form-control" name="proposed_by" id="proposed_by">
                                <option value="">- Select -</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Seconded By</label>
                            <select class="form-control" name="seconded_by" id="seconded_by">
                                <option value="">- Select -</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assigned To</label>
                            <select class="form-control" name="assigned_to" id="assigned_to">
                                <option value="">- Select -</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Resolution Date</label>
                            <input type="date" class="form-control" name="resolution_date" id="resolution_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Implementation Deadline</label>
                            <input type="date" class="form-control" name="implementation_deadline" id="implementation_deadline">
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="resolutionSubmitBtn">Save Resolution</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewResolutionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewResTitle">Resolution</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><strong id="viewResNumber"></strong> · <span class="badge bg-secondary" id="viewResStatus"></span></p>
                <p class="text-muted mb-3" id="viewResMeta"></p>
                <h6 style="color:#940000;font-weight:700;">Decision</h6>
                <div id="viewResDescription" style="white-space:pre-wrap;margin-bottom:1rem;"></div>
                <div class="row small">
                    <div class="col-md-4 mb-2"><strong>Proposed:</strong> <span id="viewResProposer"></span></div>
                    <div class="col-md-4 mb-2"><strong>Seconded:</strong> <span id="viewResSeconder"></span></div>
                    <div class="col-md-4 mb-2"><strong>Assigned:</strong> <span id="viewResAssignee"></span></div>
                    <div class="col-md-6 mb-2"><strong>Date:</strong> <span id="viewResDate"></span></div>
                    <div class="col-md-6 mb-2"><strong>Deadline:</strong> <span id="viewResDeadline"></span></div>
                </div>
                <h6 style="color:#940000;font-weight:700;margin-top:8px;">Notes</h6>
                <div id="viewResNotes" style="white-space:pre-wrap;"></div>
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
    const form = document.getElementById('resolutionForm');
    const modal = document.getElementById('resolutionModal');
    if (!form || !modal) return;

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

    function resetForm() {
        form.reset();
        document.getElementById('resolutionID').value = '';
        document.getElementById('status').value = 'Proposed';
        document.getElementById('resolutionModalTitle').textContent = 'Add Resolution';
        document.getElementById('resolutionSubmitBtn').textContent = 'Save Resolution';
    }

    document.getElementById('meetingID').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const dateInput = document.getElementById('resolution_date');
        if (opt && opt.dataset.date && !document.getElementById('resolutionID').value) {
            if (!dateInput.value) dateInput.value = opt.dataset.date;
        }
    });

    const openBtn = document.getElementById('openAddResolutionBtn');
    if (openBtn && !openBtn.disabled) {
        openBtn.addEventListener('click', function () {
            resetForm();
            openModal(modal);
        });
    } else if (openBtn) {
        openBtn.addEventListener('click', function () {
            Swal.fire('Locked', 'Resolutions can only be added for Ongoing or Completed meetings.', 'info');
        });
    }

    document.querySelectorAll('.edit-resolution-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('resolutionID').value = btn.dataset.id || '';
            document.getElementById('meetingID').value = btn.dataset.meeting || '';
            document.getElementById('resolution_number').value = btn.dataset.number || '';
            document.getElementById('title').value = btn.dataset.title || '';
            document.getElementById('description').value = btn.dataset.description || '';
            document.getElementById('proposed_by').value = btn.dataset.proposed || '';
            document.getElementById('seconded_by').value = btn.dataset.seconded || '';
            document.getElementById('assigned_to').value = btn.dataset.assigned || '';
            document.getElementById('resolution_date').value = btn.dataset.date || '';
            document.getElementById('implementation_deadline').value = btn.dataset.deadline || '';
            document.getElementById('status').value = btn.dataset.status || 'Proposed';
            document.getElementById('notes').value = btn.dataset.notes || '';
            document.getElementById('resolutionModalTitle').textContent = 'Edit Resolution';
            document.getElementById('resolutionSubmitBtn').textContent = 'Update Resolution';
            openModal(modal);
        });
    });

    document.querySelectorAll('.view-resolution-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('viewResTitle').textContent = btn.dataset.title || 'Resolution';
            document.getElementById('viewResNumber').textContent = btn.dataset.number || '';
            document.getElementById('viewResStatus').textContent = btn.dataset.status || '';
            document.getElementById('viewResMeta').textContent = btn.dataset.meeting
                ? ('Meeting: ' + btn.dataset.meeting)
                : '';
            document.getElementById('viewResDescription').textContent = btn.dataset.description || '-';
            document.getElementById('viewResProposer').textContent = btn.dataset.proposer || '-';
            document.getElementById('viewResSeconder').textContent = btn.dataset.seconder || '-';
            document.getElementById('viewResAssignee').textContent = btn.dataset.assignee || '-';
            document.getElementById('viewResDate').textContent = btn.dataset.date || '-';
            document.getElementById('viewResDeadline').textContent = btn.dataset.deadline || '-';
            document.getElementById('viewResNotes').textContent = btn.dataset.notes || '-';
            openModal(document.getElementById('viewResolutionModal'));
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('resolutionID').value;
        const url = id
            ? @json(route('admin.school_board.resolutions.update'))
            : @json(route('admin.school_board.resolutions.store'));
        const fd = new FormData(form);

        fetch(url, { method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not save resolution.'), 'error');
                    return;
                }
                closeModal(modal);
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while saving resolution.', 'error');
            });
    });

    document.querySelectorAll('.delete-resolution-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete resolution?',
                text: btn.dataset.title || 'This record will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('resolutionID', btn.dataset.id || '');
                fetch(@json(route('admin.school_board.resolutions.delete')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not delete resolution.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while deleting resolution.', 'error');
                });
            });
        });
    });
    });
})();
</script>
