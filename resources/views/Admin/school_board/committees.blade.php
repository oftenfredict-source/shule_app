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
    .committee-card {
        border: 1px solid #eee; background: #fff; margin-bottom: 1rem;
    }
    .committee-card-header {
        display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start;
        gap: 10px; padding: 14px 16px; border-bottom: 1px solid #f0f0f0;
    }
    .committee-card-header h5 { margin: 0; font-weight: 800; color: #940000; font-size: 1.05rem; }
    .committee-card-header .meta { font-size: 0.82rem; color: #777; margin-top: 4px; }
    .committee-card-body { padding: 12px 16px 16px; }
    .committee-members-table th, .committee-members-table td { font-size: 0.88rem; vertical-align: middle; }
    .badge-role {
        background: #fff5f5; color: #940000; border: 1px solid rgba(148,0,0,0.25); font-weight: 600;
    }
    .badge-role.chair { background: #940000; color: #fff; border-color: #940000; }
    .badge-role.secretary { background: #fff0e6; color: #b35a00; border-color: rgba(179,90,0,0.3); }
</style>

@php
    $activeCount = $committees->where('status', 'Active')->count();
    $inactiveCount = $committees->where('status', 'Inactive')->count();
    $totalAssignments = $committees->sum(fn ($c) => $c->members->count());
    $withMembers = $committees->filter(fn ($c) => $c->members->count() > 0)->count();
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
            <div class="label">Total Committees</div>
            <div class="value">{{ $committees->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Active</div>
            <div class="value">{{ $activeCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">With Members</div>
            <div class="value">{{ $withMembers }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Assignments</div>
            <div class="value">{{ $totalAssignments }}</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-sitemap"></i> Committees
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm" id="openAddCommitteeBtn">
                <i class="fa fa-plus"></i> Create Committee
            </button>
        </div>
    </div>

    @forelse($committees as $committee)
        <div class="committee-card" data-committee-id="{{ $committee->committeeID }}">
            <div class="committee-card-header">
                <div>
                    <h5>
                        {{ $committee->committee_name }}
                        @if($committee->status === 'Active')
                            <span class="badge bg-success" style="font-size:0.7rem;vertical-align:middle;">Active</span>
                        @else
                            <span class="badge bg-secondary" style="font-size:0.7rem;vertical-align:middle;">Inactive</span>
                        @endif
                    </h5>
                    <div class="meta">
                        @if($committee->committee_code)
                            Code: {{ $committee->committee_code }} ·
                        @endif
                        Formed: {{ $committee->formed_date ? $committee->formed_date->format('d M Y') : '—' }} ·
                        {{ $committee->members->count() }} member{{ $committee->members->count() === 1 ? '' : 's' }}
                    </div>
                    @if($committee->description)
                        <div class="meta" style="margin-top:6px;color:#555;">{{ $committee->description }}</div>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary assign-member-btn"
                        data-id="{{ $committee->committeeID }}"
                        data-name="{{ $committee->committee_name }}">
                        <i class="fa fa-user-plus"></i> Assign
                    </button>
                    <button type="button" class="btn btn-sm btn-warning edit-committee-btn"
                        data-id="{{ $committee->committeeID }}"
                        data-name="{{ $committee->committee_name }}"
                        data-code="{{ $committee->committee_code }}"
                        data-description="{{ $committee->description }}"
                        data-status="{{ $committee->status }}"
                        data-formed="{{ $committee->formed_date ? $committee->formed_date->format('Y-m-d') : '' }}">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-committee-btn"
                        data-id="{{ $committee->committeeID }}"
                        data-name="{{ $committee->committee_name }}">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="committee-card-body">
                @if($committee->members->isEmpty())
                    <p class="text-muted mb-0 small">No members assigned yet. Click <strong>Assign</strong> to add board members.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 committee-members-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Board Position</th>
                                    <th>Committee Role</th>
                                    <th>Assigned</th>
                                    <th style="width:70px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($committee->members->sortBy(function ($m) {
                                    $order = ['Chair' => 1, 'Secretary' => 2, 'Member' => 3];
                                    return ($order[$m->pivot->committee_role] ?? 9) . $m->full_name;
                                }) as $member)
                                    @php
                                        $role = $member->pivot->committee_role ?? 'Member';
                                        $roleClass = $role === 'Chair' ? 'chair' : ($role === 'Secretary' ? 'secretary' : '');
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $member->full_name }}</strong></td>
                                        <td>{{ optional($member->position)->position_name ?: '—' }}</td>
                                        <td><span class="badge badge-role {{ $roleClass }}">{{ $role }}</span></td>
                                        <td>
                                            {{ $member->pivot->assigned_date
                                                ? \Carbon\Carbon::parse($member->pivot->assigned_date)->format('d M Y')
                                                : '—' }}
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-member-btn"
                                                data-committee="{{ $committee->committeeID }}"
                                                data-member="{{ $member->memberID }}"
                                                data-name="{{ $member->full_name }}"
                                                data-committee-name="{{ $committee->committee_name }}"
                                                title="Remove">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No committees yet. Click <strong>Create Committee</strong> to add the first one.
            </div>
        </div>
    @endforelse
</div>

{{-- Create / Edit Committee Modal --}}
<div class="modal fade" id="committeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="committeeModalTitle">Create Committee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="committeeForm">
                @csrf
                <input type="hidden" name="committeeID" id="committeeID">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Committee Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="committee_name" id="committee_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="committee_code" id="committee_code" placeholder="e.g. FINANCE">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Formed Date</label>
                            <input type="date" class="form-control" name="formed_date" id="formed_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="committeeSubmitBtn">Save Committee</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Assign Member Modal --}}
<div class="modal fade" id="assignMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Assign Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignMemberForm">
                @csrf
                <input type="hidden" name="committeeID" id="assignCommitteeID">
                <div class="modal-body">
                    <p class="mb-3">Committee: <strong id="assignCommitteeName"></strong></p>
                    @if($boardMembers->isEmpty())
                        <div class="alert alert-warning mb-0">
                            No active board members found. Register members first under <strong>Board Members</strong>.
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Board Member <span class="text-danger">*</span></label>
                            <select class="form-control" name="memberID" id="assignMemberID" required>
                                <option value="">— Select member —</option>
                                @foreach($boardMembers as $bm)
                                    <option value="{{ $bm->memberID }}">
                                        {{ $bm->full_name }}
                                        @if($bm->position) ({{ $bm->position->position_name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Committee Role <span class="text-danger">*</span></label>
                            <select class="form-control" name="committee_role" id="committee_role" required>
                                <option value="Member">Member</option>
                                <option value="Chair">Chair</option>
                                <option value="Secretary">Secretary</option>
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Assigned Date</label>
                            <input type="date" class="form-control" name="assigned_date" id="assigned_date" value="{{ date('Y-m-d') }}">
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    @if($boardMembers->isNotEmpty())
                        <button type="submit" class="btn btn-primary-custom">Assign Member</button>
                    @endif
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

    ready(function () {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const committeeForm = document.getElementById('committeeForm');
    const committeeModal = document.getElementById('committeeModal');
    const assignForm = document.getElementById('assignMemberForm');
    if (!committeeForm || !committeeModal) return;

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
            try {
                return Object.values(data.errors).flat().join('\n');
            } catch (e) {}
        }
        return fallback;
    }

    function resetCommitteeForm() {
        committeeForm.reset();
        document.getElementById('committeeID').value = '';
        document.getElementById('status').value = 'Active';
        document.getElementById('committeeModalTitle').textContent = 'Create Committee';
        document.getElementById('committeeSubmitBtn').textContent = 'Save Committee';
    }

    document.getElementById('openAddCommitteeBtn').addEventListener('click', function () {
        resetCommitteeForm();
        openModal(committeeModal);
    });

    document.querySelectorAll('.edit-committee-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('committeeID').value = btn.dataset.id || '';
            document.getElementById('committee_name').value = btn.dataset.name || '';
            document.getElementById('committee_code').value = btn.dataset.code || '';
            document.getElementById('description').value = btn.dataset.description || '';
            document.getElementById('status').value = btn.dataset.status || 'Active';
            document.getElementById('formed_date').value = btn.dataset.formed || '';
            document.getElementById('committeeModalTitle').textContent = 'Edit Committee';
            document.getElementById('committeeSubmitBtn').textContent = 'Update Committee';
            openModal(committeeModal);
        });
    });

    document.querySelectorAll('.assign-member-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('assignCommitteeID').value = btn.dataset.id || '';
            document.getElementById('assignCommitteeName').textContent = btn.dataset.name || '';
            const memberSelect = document.getElementById('assignMemberID');
            if (memberSelect) memberSelect.value = '';
            const roleSelect = document.getElementById('committee_role');
            if (roleSelect) roleSelect.value = 'Member';
            const dateInput = document.getElementById('assigned_date');
            if (dateInput && !dateInput.value) {
                dateInput.value = new Date().toISOString().slice(0, 10);
            }
            openModal(document.getElementById('assignMemberModal'));
        });
    });

    committeeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('committeeID').value;
        const url = id
            ? @json(route('admin.school_board.committees.update'))
            : @json(route('admin.school_board.committees.store'));
        const fd = new FormData(committeeForm);

        fetch(url, { method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not save committee.'), 'error');
                    return;
                }
                closeModal(committeeModal);
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while saving committee.', 'error');
            });
    });

    if (assignForm) {
        assignForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(assignForm);
            fetch(@json(route('admin.school_board.committees.assign_member')), {
                method: 'POST',
                headers: jsonHeaders(),
                body: fd,
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not assign member.'), 'error');
                    return;
                }
                closeModal(document.getElementById('assignMemberModal'));
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while assigning member.', 'error');
            });
        });
    }

    document.querySelectorAll('.remove-member-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Remove member?',
                text: (btn.dataset.name || 'Member') + ' from ' + (btn.dataset.committeeName || 'committee'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, remove'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('committeeID', btn.dataset.committee || '');
                fd.append('memberID', btn.dataset.member || '');
                fetch(@json(route('admin.school_board.committees.remove_member')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not remove member.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while removing member.', 'error');
                });
            });
        });
    });

    document.querySelectorAll('.delete-committee-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete committee?',
                text: btn.dataset.name || 'This committee will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('committeeID', btn.dataset.id || '');
                fetch(@json(route('admin.school_board.committees.delete')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not delete committee.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while deleting committee.', 'error');
                });
            });
        });
    });
    });
})();
</script>
