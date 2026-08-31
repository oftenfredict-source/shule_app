@extends('includes.superadmin_nav')

@section('content')
<!-- Add Bootstrap Icons for better compatibility with existing styles if needed -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bx bx-shield-quarter text-danger me-2"></i>System Security Logs</h4>
            <p class="text-muted small mb-0">Monitor user activities, locations, and manage IP blocking.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="card border-0 shadow-sm mb-0">
                <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                    <div>
                        <div class="small fw-bold mb-0">Login Photo Capture</div>
                        <div class="text-muted" style="font-size:11px;" id="loginPhotoStatusLabel">
                            {{ !empty($loginPhotoCaptureEnabled) ? 'Enabled' : 'Disabled (default)' }}
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="loginPhotoCaptureToggle"
                               {{ !empty($loginPhotoCaptureEnabled) ? 'checked' : '' }}
                               style="width:2.5em; height:1.3em; cursor:pointer;">
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" onclick="reloadActiveTab()"><i class="bx bx-refresh me-1"></i>Refresh Logs</button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" id="start_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" id="end_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold" id="filterSearchLabel">User / Username</label>
                    <input type="text" id="filter_username" class="form-control form-control-sm" placeholder="Search by name...">
                </div>
                <div class="col-md-2 d-none d-md-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="reloadActiveTab()">
                        <i class="bx bx-filter-alt me-1"></i> Sync
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 pb-0">
            <ul class="nav nav-tabs nav-fill" id="mainLogsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3" id="login-logs-tab" data-bs-toggle="tab" data-bs-target="#login-logs-pane" type="button" role="tab">
                        <i class="bx bx-log-in-circle me-1"></i> Login Logs
                        <span class="badge bg-danger ms-2" id="loginTodayBadge">Today: {{ (int) ($loginLogsToday ?? 0) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3" id="web-visitors-tab" data-bs-toggle="tab" data-bs-target="#web-visitors-pane" type="button" role="tab">
                        <i class="bx bx-globe me-1"></i> Web Visitors
                        <span class="badge bg-secondary ms-2" id="visitorsTodayBadge">Today: {{ (int) ($webVisitorsToday ?? 0) }} IP(s)</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="login-logs-pane" role="tabpanel">
                    <div class="table-responsive p-4">
                        <table class="table table-hover align-middle mb-0 w-100" id="logsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>User</th>
                                    <th>School</th>
                                    <th>Device & OS</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>Login Time</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="logsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="web-visitors-pane" role="tabpanel">
                    <div class="table-responsive p-4">
                        <table class="table table-hover align-middle mb-0 w-100" id="visitorsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Email</th>
                                    <th>Location</th>
                                    <th>Pages</th>
                                    <th>Last Page</th>
                                    <th>Device</th>
                                    <th>Last Visit</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="visitorsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-info-circle me-2"></i>Log Entry Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-4 bg-light p-4 text-center border-end">
                        <div id="logUserPhoto" class="mb-3">
                            <!-- User Photo -->
                        </div>
                        <h6 id="logUsername" class="fw-bold mb-1"></h6>
                        <p id="logUserType" class="text-muted small mb-4"></p>
                        
                        <div class="text-start small">
                            <p class="mb-1 text-muted">IP Address</p>
                            <p id="logIp" class="fw-bold mb-3"></p>
                            
                            <p class="mb-1 text-muted">Device</p>
                            <p id="logDevice" class="fw-bold mb-3"></p>
                            
                            <p class="mb-1 text-muted">Browser/OS</p>
                            <p id="logBrowserOs" class="fw-bold mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-8 p-0">
                        <ul class="nav nav-tabs nav-fill bg-light" id="logTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-pane"><i class="bx bx-map-alt me-2"></i>Map View</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane"><i class="bx bx-list-ul me-2"></i>Activities</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" id="photo-tab" data-bs-toggle="tab" data-bs-target="#photo-pane"><i class="bx bx-camera me-2"></i>Captured Photo</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="logTabContent">
                            <div class="tab-pane fade show active p-3" id="map-pane">
                                <div id="map" style="height: 350px; width: 100%; border-radius: 8px;"></div>
                                <div id="locationDetails" class="mt-2 small text-muted"></div>
                            </div>
                            <div class="tab-pane fade p-3" id="activities-pane">
                                <div id="activitiesList" style="max-height: 350px; overflow-y: auto;">
                                    <!-- Activities list -->
                                </div>
                            </div>
                            <div class="tab-pane fade p-3 text-center" id="photo-pane">
                                <div id="largePhotoContainer">
                                    <!-- Large Photo -->
                                </div>
                                <div class="mt-3">
                                    <a id="downloadPhotoBtn" href="#" target="_blank" class="btn btn-outline-danger btn-sm"><i class="bx bx-download me-1"></i>Download Full Image</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <div id="blockActionBtn"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Web Visitor Details Modal -->
<div class="modal fade" id="visitorDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-globe me-2"></i>Web Visitor Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-4 bg-light p-4 border-end">
                        <div class="text-center mb-3">
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mx-auto" style="width:70px;height:70px;">
                                <i class="bx bx-map" style="font-size:32px;"></i>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-1 text-center" id="visitorIpLabel">-</h6>
                        <p class="text-muted small mb-2 text-center" id="visitorEmailLabel">No email captured</p>
                        <p class="text-muted small mb-4 text-center" id="visitorLocationLabel">Unknown location</p>
                        <div class="small">
                            <p class="mb-1 text-muted">Device</p>
                            <p id="visitorDevice" class="fw-bold mb-3">-</p>
                            <p class="mb-1 text-muted">Browser / OS</p>
                            <p id="visitorBrowserOs" class="fw-bold mb-3">-</p>
                            <p class="mb-1 text-muted">Pages Visited</p>
                            <p id="visitorPagesCount" class="fw-bold mb-0">0</p>
                        </div>
                    </div>
                    <div class="col-md-8 p-0">
                        <ul class="nav nav-tabs nav-fill bg-light" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3" data-bs-toggle="tab" data-bs-target="#visitor-map-pane" type="button">
                                    <i class="bx bx-map-alt me-1"></i>Location
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#visitor-pages-pane" type="button">
                                    <i class="bx bx-list-ul me-1"></i>Pages Visited
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active p-3" id="visitor-map-pane">
                                <div id="visitorMap" style="height: 320px; width: 100%; border-radius: 8px;"></div>
                                <div id="visitorLocationDetails" class="mt-2 small text-muted"></div>
                            </div>
                            <div class="tab-pane fade p-3" id="visitor-pages-pane">
                                <div id="visitorPagesList" style="max-height: 360px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    #logsTable thead th, #visitorsTable thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 15px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
    #logsTable tbody td, #visitorsTable tbody td { padding-top: 15px; padding-bottom: 15px; border-bottom: 1px solid #f9f9f9; }
    .nav-tabs .nav-link { border: none; font-weight: 600; color: #6c757d; }
    .nav-tabs .nav-link.active { color: #940000; border-bottom: 3px solid #940000; background: none; }
    .activity-item { border-left: 2px solid #dee2e6; padding-left: 15px; margin-bottom: 15px; position: relative; }
    .activity-item::before { content: ""; position: absolute; left: -6px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: #dee2e6; }
    .activity-item.active::before { background: #940000; }
    .captured-photo-img { width: 40px; height: 40px; object-fit: cover; border: 1px solid #dee2e6; }
    #mainLogsTabs .badge { font-weight: 600; font-size: 11px; vertical-align: middle; }
</style>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChAk-LYD4BqrIktIngfd1r2EWC1hL-PTI&callback=initMap&loading=async"></script>

<script>
    let map;
    let marker;
    let visitorMap;
    let visitorMarker;
    let dataTable;
    let visitorsTable;
    let activeMainTab = 'login';

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: -6.7924, lng: 39.2083 }, 
            zoom: 12,
        });
        marker = new google.maps.Marker({
            map: map,
            draggable: false,
        });

        visitorMap = new google.maps.Map(document.getElementById("visitorMap"), {
            center: { lat: -6.7924, lng: 39.2083 },
            zoom: 5,
        });
        visitorMarker = new google.maps.Marker({
            map: visitorMap,
            draggable: false,
        });
    }

    function updateTodayBadge(type, count) {
        if (type === 'login') {
            $('#loginTodayBadge').text('Today: ' + (count ?? 0));
        } else {
            $('#visitorsTodayBadge').text('Today: ' + (count ?? 0) + ' IP(s)');
        }
    }

    function initLogsTable() {
        if (!$.fn || !$.fn.DataTable) {
            console.error('DataTables plugin not loaded.');
            $('#logsBody').html('<tr><td colspan="7" class="text-center text-danger py-4">Failed to initialize DataTable. Please refresh the page.</td></tr>');
            return;
        }

        dataTable = $('#logsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']],
            searchDelay: 500,
            ajax: {
                url: "{{ route('superadmin.system_logs.data') }}",
                type: 'GET',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.username = $('#filter_username').val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    render: function(log) {
                        const initial = (log.username || 'U').charAt(0).toUpperCase();
                        return `<div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2" style="width:40px; height:40px; font-size:14px;">${initial}</div>
                                    <div>
                                        <div class="fw-bold">${log.username || ''}</div>
                                        <div class="text-muted small">${log.user_type || ''}</div>
                                    </div>
                                </div>`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `<div class="fw-bold small text-danger text-uppercase">${log.school ? log.school.school_name : 'SUPER ADMIN'}</div>`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        const deviceIcon = log.device_type === 'Mobile' ? 'bx bx-mobile-alt' : (log.device_type === 'Tablet' ? 'bx bx-tablet' : 'bx bx-laptop');
                        return `<i class="${deviceIcon} text-muted me-1"></i> ${log.device_brand || 'Unknown'}
                                <div class="text-muted small">${log.os || '-'} / ${log.browser || '-'}</div>`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `${log.ip_address || '-'} ${log.is_blocked ? '<span class="badge bg-danger ms-1">Blocked</span>' : ''}`;
                    }
                },
                {
                    data: null,
                    render: function(log) {
                        return `<span class="small text-muted"><i class="bx bx-map-pin me-1"></i> ${log.latitude ? (parseFloat(log.latitude).toFixed(4) + ', ' + parseFloat(log.longitude).toFixed(4)) : 'No GPS'}</span>`;
                    }
                },
                {
                    data: 'login_at',
                    render: function(value) {
                        if (!value) return '-';
                        const dt = new Date(value);
                        return `<div class="fw-bold small">${dt.toLocaleDateString()}</div><div class="text-muted small">${dt.toLocaleTimeString()}</div>`;
                    }
                },
                {
                    data: 'id',
                    searchable: false,
                    orderable: false,
                    className: 'text-end',
                    render: function(id) {
                        return `<button class="btn btn-outline-danger btn-sm border-0" onclick="viewDetails(${id})"><i class="bx bx-show fs-5"></i></button>`;
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Quick search..."
            }
        });

        $('#logsTable').on('xhr.dt', function (e, settings, json) {
            if (json && typeof json.todayCount !== 'undefined') {
                updateTodayBadge('login', json.todayCount);
            }
        });
    }

    function initVisitorsTable() {
        if (!$.fn || !$.fn.DataTable) return;
        if (visitorsTable) return;

        visitorsTable = $('#visitorsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            responsive: true,
            pageLength: 10,
            order: [[6, 'desc']],
            searchDelay: 500,
            ajax: {
                url: "{{ route('superadmin.system_logs.visitors_data') }}",
                type: 'GET',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.username = $('#filter_username').val();
                }
            },
            columns: [
                {
                    data: 'ip_address',
                    render: function(ip) {
                        return `<span class="fw-bold">${ip || '-'}</span>`;
                    }
                },
                {
                    data: 'email',
                    orderable: true,
                    render: function(email) {
                        if (!email) {
                            return `<span class="text-muted small">-</span>`;
                        }
                        return `<a href="mailto:${email}" class="small fw-semibold text-danger">${email}</a>`;
                    }
                },
                {
                    data: 'location',
                    orderable: false,
                    render: function(location) {
                        return `<span class="small text-muted"><i class="bx bx-map-pin me-1"></i>${location || 'Unknown location'}</span>`;
                    }
                },
                {
                    data: 'pages_count',
                    render: function(count) {
                        return `<span class="badge bg-light text-dark border">${count || 1} page(s)</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(row) {
                        const page = row.page_name || 'Unknown page';
                        const url = row.page_url || '';
                        return `<div class="fw-bold small">${page}</div>
                                <div class="text-muted small text-truncate" style="max-width:260px;" title="${url}">${url}</div>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(row) {
                        const deviceIcon = row.device_type === 'Mobile' ? 'bx bx-mobile-alt' : (row.device_type === 'Tablet' ? 'bx bx-tablet' : 'bx bx-laptop');
                        return `<i class="${deviceIcon} text-muted me-1"></i> ${row.device_type || 'Unknown'}
                                <div class="text-muted small">${row.browser || '-'} / ${row.os || '-'}</div>`;
                    }
                },
                {
                    data: 'visited_at',
                    render: function(value) {
                        if (!value) return '-';
                        const dt = new Date(value);
                        return `<div class="fw-bold small">${dt.toLocaleDateString()}</div><div class="text-muted small">${dt.toLocaleTimeString()}</div>`;
                    }
                },
                {
                    data: 'ip_address',
                    searchable: false,
                    orderable: false,
                    className: 'text-end',
                    render: function(ip) {
                        return `<button class="btn btn-outline-danger btn-sm" onclick='viewVisitorDetails(${JSON.stringify(ip || "")})'>
                                    <i class="bx bx-show me-1"></i>View more
                                </button>`;
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search IP / email / page..."
            }
        });

        $('#visitorsTable').on('xhr.dt', function (e, settings, json) {
            if (json && typeof json.todayCount !== 'undefined') {
                updateTodayBadge('visitors', json.todayCount);
            }
        });
    }

    function viewVisitorDetails(ip) {
        if (!ip) return;

        $.get("{{ route('superadmin.system_logs.visitors_details') }}", { ip_address: ip }, function(res) {
            if (!res.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', res.message || 'Failed to load visitor details', 'error');
                }
                return;
            }

            const data = res.data;
            $('#visitorIpLabel').text(data.ip_address || '-');
            if (data.email) {
                $('#visitorEmailLabel').html('<i class="bx bx-envelope me-1"></i><a href="mailto:' + data.email + '" class="text-danger fw-semibold">' + data.email + '</a>');
            } else {
                $('#visitorEmailLabel').text('No email captured');
            }
            $('#visitorLocationLabel').text(data.location || 'Unknown location');
            $('#visitorDevice').text(data.device_type || 'Unknown');
            $('#visitorBrowserOs').text((data.browser || '-') + ' / ' + (data.os || '-'));
            $('#visitorPagesCount').text((data.pages_count || 0) + ' page(s)');

            if (data.latitude && data.longitude && visitorMap && visitorMarker) {
                const pos = { lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) };
                setTimeout(function() {
                    google.maps.event.trigger(visitorMap, 'resize');
                    visitorMap.setCenter(pos);
                    visitorMap.setZoom(10);
                    visitorMarker.setPosition(pos);
                }, 300);
                $('#visitorLocationDetails').text(`${data.location} • ${data.latitude}, ${data.longitude}`);
            } else {
                $('#visitorLocationDetails').text('Location coordinates not available for this visitor IP (local/private network or lookup failed).');
            }

            let pagesHtml = '';
            if (data.pages && data.pages.length) {
                data.pages.forEach(function(page) {
                    const visited = page.visited_at ? new Date(page.visited_at) : null;
                    pagesHtml += `
                        <div class="activity-item">
                            <div class="fw-bold text-danger small">${page.page_name || 'Unknown page'}</div>
                            <div class="text-muted" style="font-size: 10px;">
                                <span class="font-monospace">${page.page_url || ''}</span>
                            </div>
                            <div class="text-muted small" style="font-size: 10px;">
                                ${visited ? visited.toLocaleString() : '-'}
                            </div>
                        </div>
                    `;
                });
            } else {
                pagesHtml = '<div class="text-center py-5 text-muted"><i class="bx bx-history mb-2 d-block" style="font-size:30px;"></i>No pages recorded</div>';
            }
            $('#visitorPagesList').html(pagesHtml);

            const modal = new bootstrap.Modal(document.getElementById('visitorDetailsModal'));
            modal.show();
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.message || 'Failed to load visitor details';
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    function loadLogs() {
        if (!dataTable) {
            initLogsTable();
            return;
        }
        dataTable.ajax.reload(null, true);
    }

    function loadVisitors() {
        if (!visitorsTable) {
            initVisitorsTable();
            return;
        }
        visitorsTable.ajax.reload(null, true);
    }

    function reloadActiveTab() {
        if (activeMainTab === 'visitors') {
            loadVisitors();
        } else {
            loadLogs();
        }
    }

    function syncFilterLabels() {
        if (activeMainTab === 'visitors') {
            $('#filterSearchLabel').text('IP / Email / Page');
            $('#filter_username').attr('placeholder', 'Search IP, email or page...');
        } else {
            $('#filterSearchLabel').text('User / Username');
            $('#filter_username').attr('placeholder', 'Search by name...');
        }
    }

    function viewDetails(id) {
        $.get(`/super-admin/system-logs/details/${id}`, function(res) {
            if (res.success) {
                const log = res.data;
                $('#logUsername').text(log.username);
                $('#logUserType').text(log.user_type);
                $('#logIp').text(log.ip_address);
                $('#logDevice').text(log.device_type + ' (' + (log.device_brand || 'Unknown') + ')');
                $('#logBrowserOs').text(log.os + ' / ' + log.browser);
                
                $('#logUserPhoto').html(`<div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width:150px; height:150px;"><i class="bx bx-user text-muted" style="font-size:80px;"></i></div>`);

                // Map
                if (log.latitude && log.longitude) {
                    const pos = { lat: parseFloat(log.latitude), lng: parseFloat(log.longitude) };
                    setTimeout(() => {
                        google.maps.event.trigger(map, 'resize');
                        map.setCenter(pos);
                        marker.setPosition(pos);
                    }, 300);
                    $('#locationDetails').text(`Coordinates: ${log.latitude}, ${log.longitude}`);
                } else {
                    $('#locationDetails').text('GPS data not available for this session.');
                }

                // Activities
                let actHtml = '';
                if (log.activities && log.activities.length > 0) {
                    log.activities.forEach(act => {
                        actHtml += `
                            <div class="activity-item">
                                <div class="fw-bold text-danger small">${act.friendly_description}</div>
                                <div class="text-muted" style="font-size: 10px;">
                                    ${act.method} <span class="font-monospace">${act.url}</span>
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">${new Date(act.created_at).toLocaleTimeString()}</div>
                            </div>
                        `;
                    });
                } else {
                    actHtml = '<div class="text-center py-5 text-muted"><i class="bx bx-history mb-2 d-block" style="font-size:30px;"></i>No activity recorded yet</div>';
                }
                $('#activitiesList').html(actHtml);

                // Large Photo Tab
                if (log.photo_path) {
                    $('#largePhotoContainer').html(`<img src="/${log.photo_path}" class="img-fluid rounded shadow border" style="max-height:300px; border: 5px solid #fff !important;">`);
                    $('#downloadPhotoBtn').attr('href', `/${log.photo_path}`).show();
                } else {
                    $('#largePhotoContainer').html('<div class="text-center py-5 text-muted"><i class="bx bx-camera-off mb-2 d-block" style="font-size:30px;"></i>No photo captured for this session</div>');
                    $('#downloadPhotoBtn').hide();
                }

                // Block/Unblock Button
                if (log.is_blocked) {
                    $('#blockActionBtn').html(`<button class="btn btn-success" onclick="unblockIp('${log.ip_address}')"><i class="bx bx-lock-open-alt me-1"></i>Unblock IP</button>`);
                } else {
                    $('#blockActionBtn').html(`<button class="btn btn-danger" onclick="blockIp('${log.ip_address}')"><i class="bx bx-shield-x me-1"></i>Block IP</button>`);
                }

                const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
                modal.show();
            }
        });
    }

    function blockIp(ip) {
        const reason = prompt("Enter reason for blocking this IP:");
        if (reason === null) return;
        
        $.post("{{ route('superadmin.system_logs.block_ip') }}", {
            _token: "{{ csrf_token() }}",
            ip_address: ip,
            reason: reason
        }, function(res) {
            if (res.success) {
                Swal.fire('Success', res.message, 'success');
                loadLogs();
                bootstrap.Modal.getInstance(document.getElementById('logDetailsModal')).hide();
            }
        });
    }

    function unblockIp(ip) {
        if (!confirm("Are you sure you want to unblock this IP?")) return;
        
        $.post("{{ route('superadmin.system_logs.unblock_ip') }}", {
            _token: "{{ csrf_token() }}",
            ip_address: ip
        }, function(res) {
            if (res.success) {
                Swal.fire('Success', res.message, 'success');
                loadLogs();
                bootstrap.Modal.getInstance(document.getElementById('logDetailsModal')).hide();
            }
        });
    }

    function updateLoginPhotoCaptureSetting(enabled) {
        const toggle = $('#loginPhotoCaptureToggle');
        toggle.prop('disabled', true);

        $.post("{{ route('superadmin.system_logs.login_photo_capture') }}", {
            _token: "{{ csrf_token() }}",
            enabled: enabled ? 1 : 0
        }, function(res) {
            if (res.success) {
                $('#loginPhotoStatusLabel').text(enabled ? 'Enabled' : 'Disabled (default)');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
            } else {
                toggle.prop('checked', !enabled);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', res.message || 'Failed to update setting', 'error');
                }
            }
        }).fail(function(xhr) {
            toggle.prop('checked', !enabled);
            const msg = xhr.responseJSON?.message || 'Failed to update setting';
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', msg, 'error');
            }
        }).always(function() {
            toggle.prop('disabled', false);
        });
    }

    $(document).ready(function() {
        loadLogs();

        $('#loginPhotoCaptureToggle').on('change', function() {
            updateLoginPhotoCaptureSetting($(this).is(':checked'));
        });

        $('#login-logs-tab').on('shown.bs.tab', function() {
            activeMainTab = 'login';
            syncFilterLabels();
            loadLogs();
        });

        $('#web-visitors-tab').on('shown.bs.tab', function() {
            activeMainTab = 'visitors';
            syncFilterLabels();
            loadVisitors();
            setTimeout(function() {
                if (visitorsTable) visitorsTable.columns.adjust();
            }, 100);
        });

        // Date filters
        $('#start_date, #end_date').on('change', function() {
            reloadActiveTab();
        });

        // Username / page filter (debounced)
        let searchTimer;
        $('#filter_username').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(reloadActiveTab, 500);
        });
    });
</script>
@endsection
