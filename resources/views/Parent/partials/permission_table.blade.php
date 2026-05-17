@if($permissions->count() === 0)
    <div class="alert alert-light text-muted border-0 py-4 text-center" style="border-radius:0;">No permission records found for this category.</div>
@else

{{-- CSS-based defaults to prevent flicker --}}
<style>
    @media (max-width: 991px) {
        #perm-list-view { display: none !important; }
        #perm-grid-view { display: block !important; }
    }
</style>

{{-- ====== VIEW TOGGLE CONTROLS ====== --}}
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <small class="text-muted">{{ $permissions->count() }} record(s)</small>
    <div class="btn-group btn-group-sm" id="perm-view-toggle" role="group" aria-label="View toggle">
        <button type="button" class="btn btn-outline-secondary perm-view-btn" data-view="list" title="List View">
            <i class="bx bx-list-ul"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary perm-view-btn" data-view="grid" title="Grid View">
            <i class="bx bx-grid-alt"></i>
        </button>
    </div>
</div>

{{-- ====== LIST VIEW (table) ====== --}}
<div id="perm-list-view">
    <div class="table-responsive">
        <table class="table table-hover align-middle border">
            <thead class="bg-light">
                <tr style="font-size: 11px; text-transform: uppercase; color: #940000; letter-spacing: 0.5px;">
                    <th class="py-3 px-3">Date Requested</th>
                    <th class="py-3 px-3">Student Name</th>
                    <th class="py-3 px-3">Period & Duration</th>
                    <th class="py-3 px-3">Category</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                    <tr>
                        <td class="px-3" style="font-size: 13px;">{{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : '---' }}</td>
                        <td class="px-3">
                            <div style="font-weight: 700; color: #333;">{{ $studentNames[$permission->studentID] ?? 'Processing...' }}</div>
                        </td>
                        <td class="px-3" style="font-size: 13px;">
                            <i class="bx bx-calendar text-muted me-1"></i> {{ \Carbon\Carbon::parse($permission->start_date)->format('j F Y') }} to {{ \Carbon\Carbon::parse($permission->end_date)->format('j F Y') }}
                            <div class="badge bg-light text-dark border ms-2" style="border-radius:0; font-weight:700;">{{ $permission->days_count }} DAYS</div>
                        </td>
                        <td class="px-3"><span class="text-uppercase small font-weight-bold" style="color:#777;">{{ $permission->reason_type }}</span></td>
                        <td class="px-3 text-center">
                            @php
                                $status = strtolower($permission->status);
                                $badgeClass = match($status) {
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-uppercase" style="border-radius: 0; font-size: 10px; padding: 5px 10px;">
                                {{ $permission->status }}
                            </span>
                        </td>
                        <td class="px-3 text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none; border-radius:0;">
                                    <i class="bx bx-dots-vertical-rounded" style="font-size: 18px;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="border-radius:0; font-size: 13px;">
                                    <li>
                                        <a class="dropdown-item py-2" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                                            <i class="bx bx-show text-info me-2"></i> View Progress
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="#" onclick="viewPermissionDetails({{ json_encode([
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
                                            <i class="bx bx-list-ul text-secondary me-2"></i> View Details
                                        </a>
                                    </li>
                                    @if($permission->status === 'rejected')
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ url()->current() }}?tab=request&edit_id={{ $permission->permissionID }}">
                                            <i class="bx bx-edit text-primary me-2"></i> Edit & Resubmit
                                        </a>
                                    </li>
                                    @endif
                                </ul>
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
            $status = strtolower($permission->status);
            $badgeClass = match($status) { 'approved' => 'bg-success', 'rejected' => 'bg-danger', default => 'bg-warning text-dark' };
            $borderColor = match($status) { 'approved' => '#28a745', 'rejected' => '#dc3545', default => '#ffc107' };
            $studentLabel = $studentNames[$permission->studentID] ?? 'Processing...';
        @endphp
        <div class="col-12 col-sm-6">
            <div class="card h-100 shadow-sm" style="border-radius:0; border-left: 4px solid {{ $borderColor }};">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div style="font-weight:700; color:#333; font-size:14px;">{{ $studentLabel }}</div>
                            <div style="font-size:11px; color:#888;"><i class="bx bx-time"></i> {{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : 'N/A' }}</div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none; line-height:1;">
                                <i class="bx bx-dots-vertical-rounded" style="font-size:20px; color:#666;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:0; font-size:13px;">
                                <li><a class="dropdown-item py-2" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;"><i class="bx bx-show text-info me-2"></i> View Progress</a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="viewPermissionDetails({{ json_encode([
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
                                ]) }}); return false;"><i class="bx bx-list-ul text-secondary me-2"></i> View Details</a></li>
                                @if($permission->status === 'rejected')
                                <li><a class="dropdown-item py-2" href="{{ url()->current() }}?tab=request&edit_id={{ $permission->permissionID }}"><i class="bx bx-edit text-primary me-2"></i> Edit & Resubmit</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div class="mb-1" style="font-size:12px; color:#555;">
                        <i class="bx bx-calendar" style="color:#940000;"></i>
                        {{ \Carbon\Carbon::parse($permission->start_date)->format('j M Y') }} &rarr; {{ \Carbon\Carbon::parse($permission->end_date)->format('j M Y') }}
                        <span class="badge bg-light text-dark border ms-1" style="border-radius:0; font-size:10px;">{{ $permission->days_count }} days</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span class="text-uppercase small" style="color:#777; font-size:11px; font-weight:600;">
                            <i class="bx bx-tag"></i> {{ $permission->reason_type }}
                        </span>
                        <span class="badge {{ $badgeClass }} text-uppercase" style="border-radius:0; font-size:10px; padding: 4px 8px;">
                            {{ $permission->status }}
                        </span>
                    </div>

                    @if($permission->status === 'rejected' && $permission->admin_response)
                    <div class="mt-2 p-2" style="background:#fff5f5; border-left: 3px solid #dc3545; font-size: 11px; color:#dc3545;">
                        <i class="bx bx-x-circle"></i> <strong>Sababu:</strong> {{ $permission->admin_response }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>{{-- end grid view --}}

@endif
