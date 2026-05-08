@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bx bx-group text-danger me-2"></i>User Management</h4>
            <p class="text-muted small mb-0">Monitor online status and manage users across all schools.</p>
        </div>
        <button class="btn btn-outline-danger btn-sm" onclick="loadUsers()"><i class="bx bx-refresh me-1"></i>Refresh Data</button>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small opacity-75">ONLINE USERS</div>
                            <h3 class="fw-bold mb-0" id="onlineCount">0</h3>
                        </div>
                        <i class="bx bx-wifi fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small opacity-75">OFFLINE USERS</div>
                            <h3 class="fw-bold mb-0" id="offlineCount">0</h3>
                        </div>
                        <i class="bx bx-wifi-off fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">School</label>
                    <select id="filter_school" class="form-select form-select-sm shadow-none border">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">User Type</label>
                    <select id="filter_type" class="form-select form-select-sm shadow-none border">
                        <option value="">All Types</option>
                        <option value="Admin">Admin</option>
                        <option value="Teacher">Teacher</option>
                        <option value="Parent">Parent</option>
                        <option value="Staff">Staff</option>
                        <option value="Watchman">Watchman</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select id="filter_status" class="form-select form-select-sm shadow-none border">
                        <option value="">All Status</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="loadUsers()">
                        <i class="bx bx-filter-alt me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive p-4">
                <table class="table table-hover align-middle mb-0 w-100" id="usersTable">
                    <thead class="bg-light">
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>User Type</th>
                            <th>School</th>
                            <th>Status</th>
                            <th class="text-end">Last IP</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <!-- Loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    #usersTable thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 15px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
    #usersTable tbody td { padding-top: 15px; padding-bottom: 15px; border-bottom: 1px solid #f9f9f9; }
    .status-indicator { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .status-online { background-color: #28a745; box-shadow: 0 0 5px #28a745; }
    .status-offline { background-color: #6c757d; }
    .user-avatar { width: 40px; height: 40px; object-fit: cover; border: 1px solid #dee2e6; }
</style>

<script>
    let dataTable;

    function loadUsers() {
        if (dataTable) {
            dataTable.destroy();
        }
        
        const params = {
            school_id: $('#filter_school').val(),
            user_type: $('#filter_type').val(),
            status: $('#filter_status').val()
        };

        $.get("{{ route('superadmin.users.data') }}", params, function(res) {
            if (res.success) {
                $('#onlineCount').text(res.summary.online);
                $('#offlineCount').text(res.summary.offline);

                let html = '';
                res.data.forEach(user => {
                    let photoHtml = user.photo_path ? `<img src="/${user.photo_path}" class="rounded-circle me-2 user-avatar">` : `<div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center me-2 user-avatar" style="font-size:14px;">${user.name.charAt(0)}</div>`;
                    let statusHtml = user.is_online ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><span class="status-indicator status-online"></span>Online</span>` : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><span class="status-indicator status-offline"></span>Offline</span>`;
                    
                    html += `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    ${photoHtml}
                                    <div>
                                        <div class="fw-bold">${user.name}</div>
                                        <div class="text-muted small">${user.email || 'No Email'}</div>
                                    </div>
                                </div>
                            </td>
                            <td>${user.name}</td>
                            <td><span class="badge bg-light text-dark border">${user.user_type}</span></td>
                            <td>${user.schoolID || 'N/A'}</td>
                            <td>${statusHtml}</td>
                            <td class="text-end text-muted font-monospace small">${user.last_ip || 'N/A'}</td>
                        </tr>
                    `;
                });
                $('#usersBody').html(html);
                
                dataTable = $('#usersTable').DataTable({
                    order: [[4, 'asc']], // Online users first
                    pageLength: 25,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Quick search..."
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        loadUsers();

        // Real-time filters
        $('#filter_school, #filter_type, #filter_status').on('change', function() {
            loadUsers();
        });
    });
</script>
@endsection
