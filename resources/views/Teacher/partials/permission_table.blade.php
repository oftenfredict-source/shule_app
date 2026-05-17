@if($permissions->count() === 0)
    <div class="alert alert-info">No permission requests found.</div>
@else

{{-- CSS-based defaults to prevent flicker --}}
<style>
    @media (max-width: 991px) {
        #perm-list-view { display: none !important; }
        #perm-grid-view { display: block !important; }
        /* When JS takes over, it will use inline styles which override these if they are more specific,
           but we use !important here for the initial state. JS will then remove/add classes or use style.display. */
    }
</style>

{{-- ====== VIEW TOGGLE CONTROLS ====== --}}
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <small class="text-muted">{{ $permissions->count() }} record(s)</small>
    <div class="btn-group btn-group-sm" id="perm-view-toggle" role="group" aria-label="View toggle">
        <button type="button" class="btn btn-outline-secondary perm-view-btn" data-view="list" title="List View">
            <i class="fa fa-list"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary perm-view-btn" data-view="grid" title="Grid View">
            <i class="fa fa-th-large"></i>
        </button>
    </div>
</div>

{{-- ====== LIST VIEW (table) ====== --}}
<div id="perm-list-view">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-primary-custom text-white">
                <tr>
                    <th>Requested At</th>
                    <th>Mode</th>
                    <th>Period</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                    <tr>
                        <td>{{ $permission->created_at ? $permission->created_at->format('d M Y H:i') : 'N/A' }}</td>
                        <td>{{ ucfirst($permission->time_mode) }}</td>
                        <td>
                            @if($permission->time_mode === 'days')
                                {{ \Carbon\Carbon::parse($permission->start_date)->format('j F Y') }} - {{ \Carbon\Carbon::parse($permission->end_date)->format('j F Y') }} ({{ $permission->days_count }} days)
                            @else
                                {{ $permission->start_time }} - {{ $permission->end_time }}
                            @endif
                        </td>
                        <td>{{ ucfirst($permission->reason_type) }}</td>
                        <td>
                            @if($permission->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($permission->status === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border:none;">
                                    <i class="fa fa-ellipsis-v" style="font-size: 16px;"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                                        <i class="fa fa-eye text-info"></i> View Progress
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="viewPermissionDetails({{ json_encode([
                                        'id' => $permission->permissionID,
                                        'time_mode' => ucfirst($permission->time_mode),
                                        'days_count' => $permission->days_count,
                                        'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                        'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                        'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                        'start_time' => $permission->start_time,
                                        'end_time' => $permission->end_time,
                                        'reason_type' => ucfirst($permission->reason_type),
                                        'reason_description' => $permission->reason_description,
                                        'status' => ucfirst($permission->status),
                                        'admin_response' => $permission->admin_response,
                                        'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                                    ]) }}); return false;">
                                        <i class="fa fa-list-alt text-secondary"></i> View Details
                                    </a>
                                    @if($permission->status === 'rejected')
                                    <a class="dropdown-item" href="{{ url()->current() }}?tab=request&edit_id={{ $permission->permissionID }}">
                                        <i class="fa fa-edit text-primary"></i> Edit & Resubmit
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>{{-- end list view --}}

{{-- ====== GRID VIEW (cards) ====== --}}
<div id="perm-grid-view" style="display:none;">
    <div class="row g-3">
        @foreach($permissions as $permission)
        @php
            $statusClass = match($permission->status) {
                'approved' => 'badge-success',
                'rejected' => 'badge-danger',
                default => 'badge-warning'
            };
            $borderColor = match($permission->status) {
                'approved' => '#28a745',
                'rejected' => '#dc3545',
                default => '#ffc107'
            };
        @endphp
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card h-100 shadow-sm" style="border-radius:0; border-left: 4px solid {{ $borderColor }};">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $statusClass }} text-uppercase" style="font-size:10px; border-radius:0; padding: 4px 8px;">
                            {{ $permission->status }}
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-sm p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border:none; line-height:1;">
                                <i class="fa fa-ellipsis-v" style="font-size:16px; color:#666;"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                                    <i class="fa fa-eye text-info"></i> View Progress
                                </a>
                                <a class="dropdown-item" href="#" onclick="viewPermissionDetails({{ json_encode([
                                    'id' => $permission->permissionID,
                                    'time_mode' => ucfirst($permission->time_mode),
                                    'days_count' => $permission->days_count,
                                    'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                    'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                    'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                    'start_time' => $permission->start_time,
                                    'end_time' => $permission->end_time,
                                    'reason_type' => ucfirst($permission->reason_type),
                                    'reason_description' => $permission->reason_description,
                                    'status' => ucfirst($permission->status),
                                    'admin_response' => $permission->admin_response,
                                    'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                                ]) }}); return false;">
                                    <i class="fa fa-list-alt text-secondary"></i> View Details
                                </a>
                                @if($permission->status === 'rejected')
                                <a class="dropdown-item" href="{{ url()->current() }}?tab=request&edit_id={{ $permission->permissionID }}">
                                    <i class="fa fa-edit text-primary"></i> Edit & Resubmit
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-1" style="font-size:12px; color:#888;">
                        <i class="fa fa-clock-o"></i> {{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : 'N/A' }}
                    </div>

                    <div class="mb-1" style="font-size:13px; font-weight:600; color:#333;">
                        <i class="fa fa-tag" style="color:#940000;"></i> {{ ucfirst($permission->reason_type) }}
                    </div>

                    <div class="mb-1" style="font-size:12px; color:#555;">
                        <i class="fa fa-calendar"></i>
                        @if($permission->time_mode === 'days')
                            {{ \Carbon\Carbon::parse($permission->start_date)->format('j M Y') }} &rarr; {{ \Carbon\Carbon::parse($permission->end_date)->format('j M Y') }}
                            <span class="badge badge-light border ml-1" style="border-radius:0;">{{ $permission->days_count }} days</span>
                        @else
                            {{ $permission->start_time }} &rarr; {{ $permission->end_time }}
                            <span class="badge badge-light border ml-1" style="border-radius:0;">Hours</span>
                        @endif
                    </div>

                    @if($permission->status === 'rejected' && $permission->admin_response)
                    <div class="mt-2 p-2" style="background:#fff5f5; border-left: 3px solid #dc3545; font-size: 11px; color:#dc3545;">
                        <i class="fa fa-times-circle"></i> <strong>Reason:</strong> {{ $permission->admin_response }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>{{-- end grid view --}}


@endif

