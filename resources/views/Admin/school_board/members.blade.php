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
    .badge-position { background: #fff5f5; color: #940000; border: 1px solid rgba(148,0,0,0.25); font-weight: 600; }
</style>

@php
    $activeCount = $members->where('status', 'Active')->count();
    $inactiveCount = $members->where('status', 'Inactive')->count();
    $formerCount = $members->where('status', 'Former')->count();
    $withPosition = $members->whereNotNull('positionID')->count();
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
            <div class="label">Total Members</div>
            <div class="value">{{ $members->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Active</div>
            <div class="value">{{ $activeCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">With Position</div>
            <div class="value">{{ $withPosition }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Inactive / Former</div>
            <div class="value">{{ $inactiveCount + $formerCount }}</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-users"></i> Board Members
            </h5>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="openPositionModalBtn">
                    <i class="fa fa-briefcase"></i> Manage Positions
                </button>
                <button type="button" class="btn btn-primary-custom btn-sm" id="openAddMemberBtn">
                    <i class="fa fa-plus"></i> Register Member
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="boardMembersTable">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Occupation</th>
                            <th>Term</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            <tr data-member-id="{{ $member->memberID }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $member->full_name }}</strong>
                                    @if($member->gender)
                                        <br><small class="text-muted">{{ $member->gender }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($member->position)
                                        <span class="badge badge-position">{{ $member->position->position_name }}</span>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>{{ $member->phone ?: '-' }}</td>
                                <td>{{ $member->email ?: '-' }}</td>
                                <td>{{ $member->occupation ?: '-' }}</td>
                                <td class="small">
                                    {{ $member->appointed_date ? $member->appointed_date->format('d M Y') : '-' }}
                                    @if($member->term_end_date)
                                        <br>to {{ $member->term_end_date->format('d M Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if($member->status === 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($member->status === 'Inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                    @else
                                        <span class="badge bg-dark">Former</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary assign-position-btn"
                                        data-id="{{ $member->memberID }}"
                                        data-name="{{ $member->full_name }}"
                                        data-position="{{ $member->positionID }}">
                                        <i class="fa fa-briefcase"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning edit-member-btn"
                                        data-id="{{ $member->memberID }}"
                                        data-first="{{ $member->first_name }}"
                                        data-middle="{{ $member->middle_name }}"
                                        data-last="{{ $member->last_name }}"
                                        data-gender="{{ $member->gender }}"
                                        data-phone="{{ $member->phone }}"
                                        data-email="{{ $member->email }}"
                                        data-occupation="{{ $member->occupation }}"
                                        data-organization="{{ $member->organization }}"
                                        data-national="{{ $member->national_id }}"
                                        data-address="{{ $member->address }}"
                                        data-position="{{ $member->positionID }}"
                                        data-appointed="{{ $member->appointed_date ? $member->appointed_date->format('Y-m-d') : '' }}"
                                        data-term-end="{{ $member->term_end_date ? $member->term_end_date->format('Y-m-d') : '' }}"
                                        data-status="{{ $member->status }}"
                                        data-notes="{{ $member->notes }}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-member-btn"
                                        data-id="{{ $member->memberID }}"
                                        data-name="{{ $member->full_name }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No board members yet. Click <strong>Register Member</strong> to add the first one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Register / Edit Member Modal --}}
<div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="memberModalTitle">Register Board Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="memberForm">
                @csrf
                <input type="hidden" name="memberID" id="memberID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" id="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" id="middle_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" id="last_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-control" name="gender" id="gender">
                                <option value="">- Select -</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="255..." required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation" id="occupation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Organization / Institution</label>
                            <input type="text" class="form-control" name="organization" id="organization">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">National ID / Passport <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="national_id" id="national_id" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <select class="form-control" name="positionID" id="positionID">
                                <option value="">- Assign later -</option>
                                @foreach($positions->where('is_active', true) as $pos)
                                    <option value="{{ $pos->positionID }}">{{ $pos->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Appointed Date</label>
                            <input type="date" class="form-control" name="appointed_date" id="appointed_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Term End Date</label>
                            <input type="date" class="form-control" name="term_end_date" id="term_end_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Former">Former</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="memberSubmitBtn">Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Assign Position Modal --}}
<div class="modal fade" id="assignPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Assign Position</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignPositionForm">
                @csrf
                <input type="hidden" name="memberID" id="assignMemberID">
                <div class="modal-body">
                    <p class="mb-2">Member: <strong id="assignMemberName"></strong></p>
                    <label class="form-label">Position</label>
                    <select class="form-control" name="positionID" id="assignPositionID">
                        <option value="">- No position -</option>
                        @foreach($positions->where('is_active', true) as $pos)
                            <option value="{{ $pos->positionID }}">{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Manage Positions Modal --}}
<div class="modal fade" id="positionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Board Positions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group mb-3" id="positionsList">
                    @foreach($positions as $pos)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $pos->position_name }}</span>
                            <span class="badge {{ $pos->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $pos->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                <form id="addPositionForm" class="border-top pt-3">
                    @csrf
                    <label class="form-label">Add custom position</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="position_name" id="newPositionName" placeholder="e.g. Legal Advisor" required>
                        <button type="submit" class="btn btn-primary-custom">Add</button>
                    </div>
                </form>
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
    const memberForm = document.getElementById('memberForm');
    const memberModal = document.getElementById('memberModal');
    const assignForm = document.getElementById('assignPositionForm');
    const positionForm = document.getElementById('addPositionForm');
    if (!memberForm || !memberModal) return;

    function openModal(el) {
        if (!el) return;
        // Prefer Bootstrap 4 / jQuery path used by Admin layout
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

    function resetMemberForm() {
        memberForm.reset();
        document.getElementById('memberID').value = '';
        document.getElementById('status').value = 'Active';
        document.getElementById('memberModalTitle').textContent = 'Register Board Member';
        document.getElementById('memberSubmitBtn').textContent = 'Save Member';
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

    document.getElementById('openAddMemberBtn').addEventListener('click', function () {
        resetMemberForm();
        openModal(memberModal);
    });

    document.getElementById('openPositionModalBtn').addEventListener('click', function () {
        openModal(document.getElementById('positionsModal'));
    });

    document.querySelectorAll('.edit-member-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('memberID').value = btn.dataset.id || '';
            document.getElementById('first_name').value = btn.dataset.first || '';
            document.getElementById('middle_name').value = btn.dataset.middle || '';
            document.getElementById('last_name').value = btn.dataset.last || '';
            document.getElementById('gender').value = btn.dataset.gender || '';
            document.getElementById('phone').value = btn.dataset.phone || '';
            document.getElementById('email').value = btn.dataset.email || '';
            document.getElementById('occupation').value = btn.dataset.occupation || '';
            document.getElementById('organization').value = btn.dataset.organization || '';
            document.getElementById('national_id').value = btn.dataset.national || '';
            document.getElementById('address').value = btn.dataset.address || '';
            document.getElementById('positionID').value = btn.dataset.position || '';
            document.getElementById('appointed_date').value = btn.dataset.appointed || '';
            document.getElementById('term_end_date').value = btn.dataset.termEnd || '';
            document.getElementById('status').value = btn.dataset.status || 'Active';
            document.getElementById('notes').value = btn.dataset.notes || '';
            document.getElementById('memberModalTitle').textContent = 'Edit Board Member';
            document.getElementById('memberSubmitBtn').textContent = 'Update Member';
            openModal(memberModal);
        });
    });

    document.querySelectorAll('.assign-position-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('assignMemberID').value = btn.dataset.id || '';
            document.getElementById('assignMemberName').textContent = btn.dataset.name || '';
            document.getElementById('assignPositionID').value = btn.dataset.position || '';
            openModal(document.getElementById('assignPositionModal'));
        });
    });

    document.querySelectorAll('.delete-member-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Delete member?',
                text: btn.dataset.name || '',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                fetch(`{{ route('admin.school_board.members.delete') }}`, {
                    method: 'POST',
                    headers: Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, jsonHeaders()),
                    body: new URLSearchParams({ memberID: btn.dataset.id, _token: csrf })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, confirmButtonColor: '#940000' })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: parseErrorMessage(data, 'Delete failed'), confirmButtonColor: '#940000' });
                    }
                })
                .catch(function () {
                    Swal.fire({ icon: 'error', title: 'Failed', text: 'Delete failed', confirmButtonColor: '#940000' });
                });
            });
        });
    });

    memberForm.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const submitBtn = document.getElementById('memberSubmitBtn');
        const memberID = document.getElementById('memberID').value;
        const url = memberID
            ? `{{ route('admin.school_board.members.update') }}`
            : `{{ route('admin.school_board.members.store') }}`;
        const formData = new FormData(memberForm);
        if (!formData.get('_token') && csrf) {
            formData.append('_token', csrf);
        }
        // Empty select must not break integer validation
        if (!formData.get('positionID')) {
            formData.set('positionID', '');
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
        }

        fetch(url, {
            method: 'POST',
            headers: jsonHeaders(),
            body: formData,
            credentials: 'same-origin'
        })
        .then(async function (res) {
            let data = {};
            try { data = await res.json(); } catch (err) { data = {}; }
            if (!res.ok || !data.success) {
                throw new Error(parseErrorMessage(data, 'Save failed (HTTP ' + res.status + ')'));
            }
            return data;
        })
        .then(function (data) {
            closeModal(memberModal);
            Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' })
                .then(function () { location.reload(); });
        })
        .catch(function (err) {
            Swal.fire({ icon: 'error', title: 'Failed', text: (err && err.message) ? err.message : 'Save failed', confirmButtonColor: '#940000' });
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = memberID ? 'Update Member' : 'Save Member';
            }
        });
    });

    if (assignForm) {
        assignForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(assignForm);
            if (!formData.get('_token') && csrf) formData.append('_token', csrf);
            fetch(`{{ route('admin.school_board.members.assign_position') }}`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: formData,
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' })
                        .then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: parseErrorMessage(data, 'Assign failed'), confirmButtonColor: '#940000' });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Failed', text: 'Assign failed', confirmButtonColor: '#940000' });
            });
        });
    }

    if (positionForm) {
        positionForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(positionForm);
            if (!formData.get('_token') && csrf) formData.append('_token', csrf);
            fetch(`{{ route('admin.school_board.positions.store') }}`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: formData,
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' })
                        .then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: parseErrorMessage(data, 'Could not add position'), confirmButtonColor: '#940000' });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not add position', confirmButtonColor: '#940000' });
            });
        });
    }
    });
})();
</script>
