@php
    $user_type = $user_type ?? Session::get('user_type');
    $tablesReady = $tablesReady ?? true;
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
    .sx-board-wrap { padding: 1rem 1.25rem 2rem; max-width: 100%; box-sizing: border-box; }
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
    .role-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 1rem;
        align-items: start;
    }
    .role-list-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 1px solid #eee;
        background: #fff;
        padding: 12px 14px;
        margin-bottom: 8px;
        cursor: pointer;
        font-family: inherit;
    }
    .role-list-item:hover { border-color: #940000; }
    .role-list-item.active {
        border-color: #940000;
        background: #fff5f5;
        box-shadow: inset 3px 0 0 #940000;
    }
    .role-list-item .name { font-weight: 800; color: #333; font-size: 0.92rem; }
    .role-list-item .meta { font-size: 0.75rem; color: #777; margin-top: 4px; }
    .perm-cat { margin-bottom: 1rem; }
    .perm-cat h6 {
        font-size: 0.78rem; font-weight: 800; color: #940000;
        text-transform: uppercase; letter-spacing: 0.04em;
        margin: 0 0 8px; padding-bottom: 4px; border-bottom: 1px solid #f0e0e0;
    }
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 6px 12px;
    }
    .perm-item {
        display: flex; align-items: flex-start; gap: 8px;
        font-size: 0.85rem; padding: 4px 0;
    }
    .perm-item input { margin-top: 3px; }
    .member-role-chip {
        display: inline-block;
        background: #fff5f5; color: #940000;
        border: 1px solid rgba(148,0,0,0.25);
        font-size: 0.72rem; font-weight: 700;
        padding: 2px 8px; margin: 2px 2px 2px 0;
    }
    @media (max-width: 900px) {
        .role-layout { grid-template-columns: 1fr; }
    }
</style>

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

    @if(!$tablesReady)
        <div class="alert alert-warning">Database tables for board roles are missing. Run the migration first.</div>
    @else
        <div class="role-layout">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0" style="color:#940000;font-weight:800;"><i class="fa fa-lock"></i> Roles</h5>
                        <button type="button" class="btn btn-primary-custom btn-sm" id="openCreateRoleBtn">
                            <i class="fa fa-plus"></i> New
                        </button>
                    </div>
                    <div id="rolesList">
                        @forelse($roles as $role)
                            <button type="button"
                                class="role-list-item {{ $loop->first ? 'active' : '' }}"
                                data-role-id="{{ $role->boardRoleID }}"
                                data-role-name="{{ $role->role_name }}"
                                data-role-desc="{{ $role->description }}"
                                data-role-active="{{ $role->is_active ? '1' : '0' }}"
                                data-role-system="{{ $role->is_system ? '1' : '0' }}"
                                data-permissions='@json($role->permissions->pluck('name')->values())'>
                                <div class="name">{{ $role->role_name }}</div>
                                <div class="meta">
                                    {{ $role->permissions->count() }} permissions ·
                                    {{ $role->members->count() }} members
                                    @if($role->is_system) · system @endif
                                    @if(!$role->is_active) · inactive @endif
                                </div>
                            </button>
                        @empty
                            <p class="text-muted small mb-0">No roles yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm mb-3" id="roleEditorCard">
                    <div class="card-body">
                        <h5 class="mb-3" style="color:#940000;font-weight:800;">
                            <i class="fa fa-cog"></i> Edit role permissions
                        </h5>
                        <form id="rolePermissionsForm">
                            @csrf
                            <input type="hidden" name="boardRoleID" id="editBoardRoleID">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Role name</label>
                                    <input type="text" class="form-control" name="role_name" id="editRoleName" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Description</label>
                                    <input type="text" class="form-control" name="description" id="editRoleDesc">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Active</label>
                                    <select class="form-control" name="is_active" id="editRoleActive">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>

                            @foreach($permissionCatalog as $category => $perms)
                                <div class="perm-cat">
                                    <h6>{{ $category }}</h6>
                                    <div class="perm-grid">
                                        @foreach($perms as $permName => $permLabel)
                                            <label class="perm-item">
                                                <input type="checkbox" name="permissions[]" value="{{ $permName }}" class="role-perm-cb">
                                                <span>{{ $permLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="submit" class="btn btn-primary-custom" id="saveRolePermsBtn">
                                    <i class="fa fa-save"></i> Save permissions
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="deleteRoleBtn">
                                    <i class="fa fa-trash"></i> Delete role
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3" style="color:#940000;font-weight:800;">
                            <i class="fa fa-user-plus"></i> Assign role to member
                        </h5>
                        <form id="assignRoleForm" class="row g-2 align-items-end mb-3">
                            @csrf
                            <div class="col-md-5">
                                <label class="form-label">Member</label>
                                <select class="form-control" name="memberID" id="assignMemberID" required>
                                    <option value="">— Select member —</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->memberID }}">{{ $m->full_name }}@if($m->position) ({{ $m->position->position_name }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Role</label>
                                <select class="form-control" name="boardRoleID" id="assignBoardRoleID" required>
                                    <option value="">— Select role —</option>
                                    @foreach($roles->where('is_active', true) as $role)
                                        <option value="{{ $role->boardRoleID }}">{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary-custom w-100">Assign</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>Member</th>
                                        <th>Position</th>
                                        <th>Assigned roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($members as $m)
                                        <tr>
                                            <td><strong>{{ $m->full_name }}</strong></td>
                                            <td>{{ $m->position->position_name ?? '—' }}</td>
                                            <td>
                                                @forelse($m->boardRoles as $br)
                                                    <span class="member-role-chip">
                                                        {{ $br->role_name }}
                                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 remove-member-role-btn"
                                                            data-member="{{ $m->memberID }}"
                                                            data-role="{{ $br->boardRoleID }}"
                                                            data-name="{{ $m->full_name }}"
                                                            data-role-name="{{ $br->role_name }}"
                                                            title="Remove"
                                                            style="color:#940000;line-height:1;">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">No role — portal uses position defaults</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No active members.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Create Role Modal --}}
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Create board role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createRoleForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="role_name" required placeholder="e.g. Finance Committee Lead">
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Create</button>
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
        const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        if (!document.getElementById('rolePermissionsForm')) return;

        function jsonHeaders() {
            return {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            };
        }

        function openModal(el) {
            if (!el) return;
            if (window.jQuery && jQuery.fn && jQuery.fn.modal) { jQuery(el).modal('show'); return; }
            if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
        }

        function loadRole(btn) {
            if (!btn) return;
            document.querySelectorAll('.role-list-item').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            document.getElementById('editBoardRoleID').value = btn.dataset.roleId || '';
            document.getElementById('editRoleName').value = btn.dataset.roleName || '';
            document.getElementById('editRoleDesc').value = btn.dataset.roleDesc || '';
            document.getElementById('editRoleActive').value = btn.dataset.roleActive === '1' ? '1' : '0';

            let selected = [];
            try { selected = JSON.parse(btn.dataset.permissions || '[]'); } catch (e) { selected = []; }
            document.querySelectorAll('.role-perm-cb').forEach(function (cb) {
                cb.checked = selected.indexOf(cb.value) !== -1;
            });

            const delBtn = document.getElementById('deleteRoleBtn');
            if (delBtn) delBtn.style.display = btn.dataset.roleSystem === '1' ? 'none' : '';
        }

        const first = document.querySelector('.role-list-item');
        if (first) loadRole(first);

        document.querySelectorAll('.role-list-item').forEach(function (btn) {
            btn.addEventListener('click', function () { loadRole(btn); });
        });

        document.getElementById('openCreateRoleBtn').addEventListener('click', function () {
            openModal(document.getElementById('createRoleModal'));
        });

        document.getElementById('createRoleForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            if (!fd.get('_token') && csrf) fd.append('_token', csrf);
            fetch(`{{ route('admin.school_board.roles.store') }}`, {
                method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');
                Swal.fire({ icon: 'success', title: 'Created', text: data.message, confirmButtonColor: '#940000' })
                    .then(function () { location.reload(); });
            })
            .catch(function (err) {
                Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Failed', confirmButtonColor: '#940000' });
            });
        });

        document.getElementById('rolePermissionsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            if (!fd.get('_token') && csrf) fd.append('_token', csrf);
            fetch(`{{ route('admin.school_board.roles.permissions') }}`, {
                method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');
                Swal.fire({ icon: 'success', title: 'Saved', text: data.message, confirmButtonColor: '#940000' })
                    .then(function () { location.reload(); });
            })
            .catch(function (err) {
                Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Failed', confirmButtonColor: '#940000' });
            });
        });

        document.getElementById('deleteRoleBtn').addEventListener('click', function () {
            const id = document.getElementById('editBoardRoleID').value;
            if (!id) return;
            Swal.fire({
                icon: 'warning', title: 'Delete this role?', showCancelButton: true,
                confirmButtonColor: '#940000', confirmButtonText: 'Delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                fetch(`{{ route('admin.school_board.roles.delete') }}`, {
                    method: 'POST',
                    headers: Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, jsonHeaders()),
                    body: new URLSearchParams({ boardRoleID: id, _token: csrf })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) throw new Error(data.message || 'Failed');
                    Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, confirmButtonColor: '#940000' })
                        .then(function () { location.reload(); });
                })
                .catch(function (err) {
                    Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Failed', confirmButtonColor: '#940000' });
                });
            });
        });

        document.getElementById('assignRoleForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            if (!fd.get('_token') && csrf) fd.append('_token', csrf);
            fetch(`{{ route('admin.school_board.roles.assign') }}`, {
                method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');
                Swal.fire({ icon: 'success', title: 'Assigned', text: data.message, confirmButtonColor: '#940000' })
                    .then(function () { location.reload(); });
            })
            .catch(function (err) {
                Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Failed', confirmButtonColor: '#940000' });
            });
        });

        document.querySelectorAll('.remove-member-role-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                Swal.fire({
                    icon: 'question',
                    title: 'Remove role?',
                    text: (btn.dataset.roleName || '') + ' from ' + (btn.dataset.name || ''),
                    showCancelButton: true,
                    confirmButtonColor: '#940000',
                    confirmButtonText: 'Remove'
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    fetch(`{{ route('admin.school_board.roles.remove') }}`, {
                        method: 'POST',
                        headers: Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, jsonHeaders()),
                        body: new URLSearchParams({
                            memberID: btn.dataset.member,
                            boardRoleID: btn.dataset.role,
                            _token: csrf
                        })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) throw new Error(data.message || 'Failed');
                        location.reload();
                    })
                    .catch(function (err) {
                        Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Failed', confirmButtonColor: '#940000' });
                    });
                });
            });
        });
    });
})();
</script>
