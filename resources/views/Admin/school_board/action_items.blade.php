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
        background: #fff; border: 1px solid #eee; padding: 12px 16px; min-width: 130px;
    }
    .member-stat .label { font-size: 0.75rem; color: #777; text-transform: uppercase; font-weight: 700; }
    .member-stat .value { font-size: 1.4rem; font-weight: 800; color: #940000; }
    .item-preview { max-width: 280px; white-space: pre-wrap; font-size: 0.82rem; color: #555; }
    .meeting-meta { font-size: 0.8rem; color: #777; }
    .upcoming-box {
        border: 1px solid #ffe0a3; background: #fffaf0; padding: 14px 16px; margin-bottom: 1rem;
    }
    .upcoming-box h6 { margin: 0 0 8px; font-weight: 800; color: #946b00; }
    .lock-note { font-size: 0.82rem; color: #946b00; margin-bottom: 0; }
    .priority-high { background: #940000; color: #fff; }
    .priority-medium { background: #fff5f5; color: #940000; border: 1px solid rgba(148,0,0,.25); }
    .priority-low { background: #f1f1f1; color: #555; }
    .overdue-row { background: #fff8f8; }
</style>

@php
    $openCount = $actionItems->whereIn('status', ['Open', 'In Progress'])->count();
    $completedCount = $actionItems->where('status', 'Completed')->count();
    $overdueCount = $actionItems->filter(fn ($i) => $i->isOverdue())->count();
    $cancelledCount = $actionItems->where('status', 'Cancelled')->count();
    $canCreate = $sourceMeetings->isNotEmpty() || $resolutions->isNotEmpty();
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
            <div class="value">{{ $actionItems->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Open / In Progress</div>
            <div class="value">{{ $openCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Overdue</div>
            <div class="value">{{ $overdueCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Completed</div>
            <div class="value">{{ $completedCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Cancelled</div>
            <div class="value">{{ $cancelledCount }}</div>
        </div>
    </div>

    @if($upcomingMeetings->isNotEmpty())
        <div class="upcoming-box">
            <h6><i class="fa fa-clock-o"></i> Upcoming meetings</h6>
            <p class="lock-note">
                Action items from meetings require an <strong>Ongoing</strong> or <strong>Completed</strong> meeting
                (or an existing resolution). {{ $upcomingMeetings->count() }} upcoming meeting(s).
            </p>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-check-square-o"></i> Action Items
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm" id="openAddActionBtn"
                @if(!$canCreate) disabled title="Need a meeting or resolution first" @endif>
                <i class="fa fa-plus"></i> Add Action Item
            </button>
        </div>
    </div>

    @if(!$canCreate)
        <div class="alert alert-info py-2">
            Create an Ongoing/Completed meeting or a resolution first, then add action items here.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Task</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Due</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actionItems as $index => $item)
                            @php
                                $statusClass = match($item->status) {
                                    'Completed' => 'bg-success',
                                    'In Progress' => 'bg-info text-dark',
                                    'Cancelled' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                                $priorityClass = match($item->priority) {
                                    'High' => 'priority-high',
                                    'Low' => 'priority-low',
                                    default => 'priority-medium',
                                };
                            @endphp
                            <tr class="{{ $item->isOverdue() ? 'overdue-row' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->title }}</strong>
                                    @if($item->description)
                                        <div class="item-preview mt-1">{{ \Illuminate\Support\Str::limit($item->description, 90) }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($item->meeting)
                                        <div><i class="fa fa-calendar"></i> {{ $item->meeting->title }}</div>
                                        <div class="meeting-meta">{{ $item->meeting->meeting_date?->format('d M Y') }}</div>
                                    @endif
                                    @if($item->resolution)
                                        <div class="mt-1"><i class="fa fa-gavel"></i> {{ $item->resolution->resolution_number ?: $item->resolution->title }}</div>
                                    @endif
                                    @if(!$item->meeting && !$item->resolution)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ optional($item->assignee)->full_name ?: '—' }}</td>
                                <td class="small text-nowrap">
                                    {{ $item->due_date ? $item->due_date->format('d M Y') : '—' }}
                                    @if($item->isOverdue())
                                        <br><span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                                <td><span class="badge {{ $priorityClass }}">{{ $item->priority }}</span></td>
                                <td><span class="badge {{ $statusClass }}">{{ $item->status }}</span></td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary view-action-btn"
                                        data-title="{{ $item->title }}"
                                        data-description="{{ $item->description }}"
                                        data-meeting="{{ optional($item->meeting)->title }}"
                                        data-resolution="{{ optional($item->resolution)->resolution_number ?: optional($item->resolution)->title }}"
                                        data-assignee="{{ optional($item->assignee)->full_name }}"
                                        data-due="{{ $item->due_date ? $item->due_date->format('d M Y') : '' }}"
                                        data-priority="{{ $item->priority }}"
                                        data-status="{{ $item->status }}"
                                        data-notes="{{ $item->notes }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning edit-action-btn"
                                        data-id="{{ $item->actionItemID }}"
                                        data-meeting="{{ $item->meetingID }}"
                                        data-resolution="{{ $item->resolutionID }}"
                                        data-title="{{ $item->title }}"
                                        data-description="{{ $item->description }}"
                                        data-assigned="{{ $item->assigned_to }}"
                                        data-due="{{ $item->due_date ? $item->due_date->format('Y-m-d') : '' }}"
                                        data-priority="{{ $item->priority }}"
                                        data-status="{{ $item->status }}"
                                        data-notes="{{ $item->notes }}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-action-btn"
                                        data-id="{{ $item->actionItemID }}"
                                        data-title="{{ $item->title }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No action items yet. Click <strong>Add Action Item</strong> to track follow-up tasks.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Modal --}}
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="actionModalTitle">Add Action Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="actionForm">
                @csrf
                <input type="hidden" name="actionItemID" id="actionItemID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meeting</label>
                            <select class="form-control" name="meetingID" id="meetingID">
                                <option value="">— Optional if resolution selected —</option>
                                @foreach($sourceMeetings as $m)
                                    <option value="{{ $m->meetingID }}">
                                        {{ $m->meeting_date->format('d M Y') }} — {{ $m->title }} ({{ $m->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Related Resolution</label>
                            <select class="form-control" name="resolutionID" id="resolutionID">
                                <option value="">— Optional —</option>
                                @foreach($resolutions as $r)
                                    <option value="{{ $r->resolutionID }}" data-meeting="{{ $r->meetingID }}">
                                        {{ $r->resolution_number ?: '#' . $r->resolutionID }} — {{ \Illuminate\Support\Str::limit($r->title, 40) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">At least meeting or resolution is required.</small>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-control" name="priority" id="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assigned To</label>
                            <select class="form-control" name="assigned_to" id="assigned_to">
                                <option value="">— Select —</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">{{ $bm->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="actionSubmitBtn">Save Action Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewActionTitle">Action Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    <span class="badge bg-secondary" id="viewActionStatus"></span>
                    <span class="badge ms-1" id="viewActionPriority"></span>
                </p>
                <div class="row small mb-3">
                    <div class="col-md-6 mb-1"><strong>Meeting:</strong> <span id="viewActionMeeting"></span></div>
                    <div class="col-md-6 mb-1"><strong>Resolution:</strong> <span id="viewActionResolution"></span></div>
                    <div class="col-md-6 mb-1"><strong>Assigned:</strong> <span id="viewActionAssignee"></span></div>
                    <div class="col-md-6 mb-1"><strong>Due:</strong> <span id="viewActionDue"></span></div>
                </div>
                <h6 style="color:#940000;font-weight:700;">Description</h6>
                <div id="viewActionDescription" style="white-space:pre-wrap;margin-bottom:1rem;"></div>
                <h6 style="color:#940000;font-weight:700;">Notes</h6>
                <div id="viewActionNotes" style="white-space:pre-wrap;"></div>
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
    const form = document.getElementById('actionForm');
    const modal = document.getElementById('actionModal');
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
        document.getElementById('actionItemID').value = '';
        document.getElementById('priority').value = 'Medium';
        document.getElementById('status').value = 'Open';
        document.getElementById('actionModalTitle').textContent = 'Add Action Item';
        document.getElementById('actionSubmitBtn').textContent = 'Save Action Item';
    }

    document.getElementById('resolutionID').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const meetingSelect = document.getElementById('meetingID');
        if (opt && opt.dataset.meeting && !meetingSelect.value) {
            meetingSelect.value = opt.dataset.meeting;
        }
    });

    const openBtn = document.getElementById('openAddActionBtn');
    if (openBtn && !openBtn.disabled) {
        openBtn.addEventListener('click', function () {
            resetForm();
            openModal(modal);
        });
    }

    document.querySelectorAll('.edit-action-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('actionItemID').value = btn.dataset.id || '';
            document.getElementById('meetingID').value = btn.dataset.meeting || '';
            document.getElementById('resolutionID').value = btn.dataset.resolution || '';
            document.getElementById('title').value = btn.dataset.title || '';
            document.getElementById('description').value = btn.dataset.description || '';
            document.getElementById('assigned_to').value = btn.dataset.assigned || '';
            document.getElementById('due_date').value = btn.dataset.due || '';
            document.getElementById('priority').value = btn.dataset.priority || 'Medium';
            document.getElementById('status').value = btn.dataset.status || 'Open';
            document.getElementById('notes').value = btn.dataset.notes || '';
            document.getElementById('actionModalTitle').textContent = 'Edit Action Item';
            document.getElementById('actionSubmitBtn').textContent = 'Update Action Item';
            openModal(modal);
        });
    });

    document.querySelectorAll('.view-action-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('viewActionTitle').textContent = btn.dataset.title || 'Action Item';
            document.getElementById('viewActionStatus').textContent = btn.dataset.status || '';
            document.getElementById('viewActionPriority').textContent = btn.dataset.priority || '';
            document.getElementById('viewActionMeeting').textContent = btn.dataset.meeting || '—';
            document.getElementById('viewActionResolution').textContent = btn.dataset.resolution || '—';
            document.getElementById('viewActionAssignee').textContent = btn.dataset.assignee || '—';
            document.getElementById('viewActionDue').textContent = btn.dataset.due || '—';
            document.getElementById('viewActionDescription').textContent = btn.dataset.description || '—';
            document.getElementById('viewActionNotes').textContent = btn.dataset.notes || '—';
            openModal(document.getElementById('viewActionModal'));
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('actionItemID').value;
        const url = id
            ? @json(route('admin.school_board.action_items.update'))
            : @json(route('admin.school_board.action_items.store'));
        const fd = new FormData(form);

        fetch(url, { method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not save action item.'), 'error');
                    return;
                }
                closeModal(modal);
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while saving action item.', 'error');
            });
    });

    document.querySelectorAll('.delete-action-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete action item?',
                text: btn.dataset.title || 'This record will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('actionItemID', btn.dataset.id || '');
                fetch(@json(route('admin.school_board.action_items.delete')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not delete action item.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while deleting action item.', 'error');
                });
            });
        });
    });
    });
})();
</script>
