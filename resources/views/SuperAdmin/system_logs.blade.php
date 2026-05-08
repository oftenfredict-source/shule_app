@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bi bi-shield-check text-danger me-2"></i>System Security Logs</h4>
            <p class="text-muted small mb-0">Monitor user activities, locations, and manage IP blocking.</p>
        </div>
        <button class="btn btn-outline-danger btn-sm" onclick="loadLogs()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh Logs</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="logsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Device & OS</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Login Time</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="logsBody">
                        <!-- Loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Log Entry Details</h5>
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
                                <button class="nav-link active py-3" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-pane"><i class="bi bi-geo-alt me-2"></i>Map View</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane"><i class="bi bi-list-task me-2"></i>Activities</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3" id="photo-tab" data-bs-toggle="tab" data-bs-target="#photo-pane"><i class="bi bi-camera me-2"></i>Captured Photo</button>
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
                                    <a id="downloadPhotoBtn" href="#" target="_blank" class="btn btn-outline-danger btn-sm"><i class="bi bi-download me-1"></i>Download Full Image</a>
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

<style>
    #logsTable thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 15px; padding-bottom: 15px; }
    #logsTable tbody td { padding-top: 15px; padding-bottom: 15px; }
    .nav-tabs .nav-link { border: none; font-weight: 600; color: #6c757d; }
    .nav-tabs .nav-link.active { color: #940000; border-bottom: 3px solid #940000; background: none; }
    .activity-item { border-left: 2px solid #dee2e6; padding-left: 15px; margin-bottom: 15px; position: relative; }
    .activity-item::before { content: ""; position: absolute; left: -6px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: #dee2e6; }
    .activity-item.active::before { background: #940000; }
    .captured-photo-img { width: 40px; height: 40px; object-fit: cover; border: 1px solid #dee2e6; }
</style>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChAk-LYD4BqrIktIngfd1r2EWC1hL-PTI&callback=initMap"></script>

<script>
    let map;
    let marker;

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: -6.7924, lng: 39.2083 }, // Default Dar es Salaam
            zoom: 12,
        });
        marker = new google.maps.Marker({
            map: map,
            draggable: false,
        });
    }

    function loadLogs() {
        $.get("{{ route('superadmin.system_logs.data') }}", function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(log => {
                    let photoHtml = log.photo_path ? `<img src="/${log.photo_path}" class="rounded-circle me-2 captured-photo-img">` : `<div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2" style="width:40px; height:40px; font-size:14px;">${log.username.charAt(0)}</div>`;
                    let deviceIcon = log.device_type === 'Mobile' ? 'bi-phone' : (log.device_type === 'Tablet' ? 'bi-tablet' : 'bi-laptop');
                    
                    html += `
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    ${photoHtml}
                                    <div>
                                        <div class="fw-bold">${log.username}</div>
                                        <div class="text-muted small">${log.user_type}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="bi ${deviceIcon} text-muted me-1"></i> ${log.device_brand || 'Unknown'} 
                                <div class="text-muted small">${log.os} / ${log.browser}</div>
                            </td>
                            <td>
                                ${log.ip_address}
                                ${log.is_blocked ? '<span class="badge bg-danger ms-1">Blocked</span>' : ''}
                            </td>
                            <td>
                                <span class="small text-muted"><i class="bi bi-geo-alt me-1"></i> ${log.latitude ? (parseFloat(log.latitude).toFixed(4) + ', ' + parseFloat(log.longitude).toFixed(4)) : 'No GPS'}</span>
                            </td>
                            <td>
                                ${new Date(log.login_at).toLocaleString()}
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-light btn-sm" onclick="viewDetails(${log.id})"><i class="bi bi-eye"></i></button>
                            </td>
                        </tr>
                    `;
                });
                $('#logsBody').html(html);
            }
        });
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
                
                $('#logUserPhoto').html(log.photo_path ? `<img src="/${log.photo_path}" class="img-fluid rounded shadow-sm" style="max-height:150px;">` : `<div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width:150px; height:150px;"><i class="bi bi-person text-muted" style="font-size:80px;"></i></div>`);

                // Map
                if (log.latitude && log.longitude) {
                    const pos = { lat: parseFloat(log.latitude), lng: parseFloat(log.longitude) };
                    map.setCenter(pos);
                    marker.setPosition(pos);
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
                                <div class="fw-bold small">${act.method} <span class="text-muted font-monospace">${act.url}</span></div>
                                <div class="text-muted small">${new Date(act.created_at).toLocaleTimeString()}</div>
                            </div>
                        `;
                    });
                } else {
                    actHtml = '<div class="text-center py-5 text-muted"><i class="bi bi-activity mb-2 d-block" style="font-size:30px;"></i>No activity recorded yet</div>';
                }
                $('#activitiesList').html(actHtml);

                // Large Photo Tab
                if (log.photo_path) {
                    $('#largePhotoContainer').html(`<img src="/${log.photo_path}" class="img-fluid rounded shadow border" style="max-height:300px; border: 5px solid #fff !important;">`);
                    $('#downloadPhotoBtn').attr('href', `/${log.photo_path}`).show();
                    $('#photo-tab').show();
                } else {
                    $('#largePhotoContainer').html('<div class="text-center py-5 text-muted"><i class="bi bi-camera-off mb-2 d-block" style="font-size:30px;"></i>No photo captured for this session</div>');
                    $('#downloadPhotoBtn').hide();
                    // $('#photo-tab').hide(); // Keep it but show empty state
                }

                $('#activitiesList').html(actHtml);

                // Block/Unblock Button
                if (log.is_blocked) {
                    $('#blockActionBtn').html(`<button class="btn btn-success" onclick="unblockIp('${log.ip_address}')"><i class="bi bi-unlock me-1"></i>Unblock IP</button>`);
                } else {
                    $('#blockActionBtn').html(`<button class="btn btn-danger" onclick="blockIp('${log.ip_address}')"><i class="bi bi-shield-slash me-1"></i>Block IP</button>`);
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
                alert(res.message);
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
                alert(res.message);
                loadLogs();
                bootstrap.Modal.getInstance(document.getElementById('logDetailsModal')).hide();
            }
        });
    }

    $(document).ready(function() {
        loadLogs();
    });
</script>
@endsection
