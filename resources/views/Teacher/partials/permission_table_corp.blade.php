@if($permissions->count() === 0)
    <div class="sx-ep-empty">
        <i class="bi bi-info-circle"></i>
        {{ __('teacher.hr.no_requests') }}
    </div>
@else
<style>
    @media (max-width: 991px) {
        #perm-list-view { display: none !important; }
        #perm-grid-view { display: block !important; }
    }
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
    <small class="text-muted fw-semibold">{{ __('teacher.hr.records_count', ['count' => $permissions->count()]) }}</small>
    <div class="btn-group btn-group-sm" id="perm-view-toggle" role="group" aria-label="{{ __('teacher.hr.view_toggle') }}">
        <button type="button" class="btn btn-outline-secondary perm-view-btn rounded-0" data-view="list" title="{{ __('teacher.hr.list_view') }}">
            <i class="bi bi-list-ul"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary perm-view-btn rounded-0" data-view="grid" title="{{ __('teacher.hr.grid_view') }}">
            <i class="bi bi-grid-3x3-gap"></i>
        </button>
    </div>
</div>

<div id="perm-list-view">
    <div class="sx-ep-table-wrap">
        <table class="sx-ep-table">
            <thead>
                <tr>
                    <th>{{ __('teacher.hr.requested') }}</th>
                    <th>{{ __('teacher.hr.mode') }}</th>
                    <th>{{ __('teacher.hr.period') }}</th>
                    <th>{{ __('teacher.hr.reason') }}</th>
                    <th>{{ __('teacher.status') }}</th>
                    <th class="text-end">{{ __('teacher.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                    @php
                        $reasonLabel = match($permission->reason_type) {
                            'medical' => __('teacher.hr.medical'),
                            'official' => __('teacher.hr.official'),
                            'professional' => __('teacher.hr.professional'),
                            'emergency' => __('teacher.hr.emergency'),
                            'other' => __('teacher.hr.other'),
                            default => ucfirst($permission->reason_type),
                        };
                        $modeLabel = $permission->time_mode === 'hours' ? __('teacher.hr.hours') : ucfirst(__('teacher.hr.days'));
                        $statusLabel = match($permission->status) {
                            'approved' => __('teacher.approved'),
                            'rejected' => __('teacher.rejected'),
                            default => __('teacher.pending'),
                        };
                    @endphp
                    <tr>
                        <td>{{ $permission->created_at ? $permission->created_at->format('d M Y H:i') : __('teacher.hr.na') }}</td>
                        <td>{{ $modeLabel }}</td>
                        <td>
                            @if($permission->time_mode === 'days')
                                {{ \Carbon\Carbon::parse($permission->start_date)->format('j F Y') }} - {{ \Carbon\Carbon::parse($permission->end_date)->format('j F Y') }}
                                <span class="sx-ep-meta">{{ $permission->days_count }} {{ __('teacher.hr.days') }}</span>
                            @else
                                {{ $permission->start_time }} - {{ $permission->end_time }}
                            @endif
                        </td>
                        <td>{{ $reasonLabel }}</td>
                        <td>
                            @if($permission->status === 'approved')
                                <span class="tag tag-approved">{{ $statusLabel }}</span>
                            @elseif($permission->status === 'rejected')
                                <span class="tag tag-rejected">{{ $statusLabel }}</span>
                            @else
                                <span class="tag tag-pending">{{ $statusLabel }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $pdPayload = [
                                    'id' => $permission->permissionID,
                                    'time_mode' => ucfirst($permission->time_mode),
                                    'days_count' => $permission->days_count,
                                    'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                    'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                    'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                    'start_time' => $permission->start_time,
                                    'end_time' => $permission->end_time,
                                    'reason_type' => $reasonLabel,
                                    'reason_description' => $permission->reason_description,
                                    'status' => ucfirst($permission->status),
                                    'admin_response' => $permission->admin_response,
                                    'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : __('teacher.hr.na'),
                                ];
                            @endphp
                            <div class="sx-ep-actions sx-dots-wrap">
                                <button type="button" class="sx-dots-btn" aria-label="{{ __('teacher.actions') }}" onclick="togglePermDots(this)">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <div class="sx-dots-menu">
                                    <button type="button" class="sx-dots-link" onclick="viewPermissionProgress({{ $permission->permissionID }}); closePermDots();">
                                        <i class="bi bi-eye"></i> {{ __('teacher.hr.view_progress') }}
                                    </button>
                                    <button type="button" class="sx-dots-link" onclick='viewPermissionDetails(@json($pdPayload)); closePermDots();'>
                                        <i class="bi bi-card-list"></i> {{ __('teacher.hr.view_details') }}
                                    </button>
                                    @if($permission->status === 'rejected')
                                    <a class="sx-dots-link" href="{{ route('teacher.permissions', ['tab' => 'request', 'edit_id' => $permission->permissionID]) }}">
                                        <i class="bi bi-pencil-square"></i> {{ __('teacher.hr.edit_resubmit') }}
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
</div>

<div id="perm-grid-view" style="display:none;">
    <div class="row g-3">
        @foreach($permissions as $permission)
        @php
            $tagClass = match($permission->status) {
                'approved' => 'tag-approved',
                'rejected' => 'tag-rejected',
                default => 'tag-pending'
            };
            $borderColor = match($permission->status) {
                'approved' => '#28a745',
                'rejected' => '#dc3545',
                default => '#ffc107'
            };
            $reasonLabel = match($permission->reason_type) {
                'medical' => __('teacher.hr.medical'),
                'official' => __('teacher.hr.official'),
                'professional' => __('teacher.hr.professional'),
                'emergency' => __('teacher.hr.emergency'),
                'other' => __('teacher.hr.other'),
                default => ucfirst($permission->reason_type),
            };
            $modeLabel = $permission->time_mode === 'hours' ? __('teacher.hr.hours') : ucfirst(__('teacher.hr.days'));
            $statusLabel = match($permission->status) {
                'approved' => __('teacher.approved'),
                'rejected' => __('teacher.rejected'),
                default => __('teacher.pending'),
            };
        @endphp
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="sx-perm-card" style="border-left-color: {{ $borderColor }};">
                @php
                    $pdPayloadGrid = [
                        'id' => $permission->permissionID,
                        'time_mode' => ucfirst($permission->time_mode),
                        'days_count' => $permission->days_count,
                        'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                        'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                        'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                        'start_time' => $permission->start_time,
                        'end_time' => $permission->end_time,
                        'reason_type' => $reasonLabel,
                        'reason_description' => $permission->reason_description,
                        'status' => ucfirst($permission->status),
                        'admin_response' => $permission->admin_response,
                        'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : __('teacher.hr.na'),
                    ];
                @endphp
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="tag {{ $tagClass }} text-uppercase">{{ $statusLabel }}</span>
                    <div class="sx-ep-actions sx-dots-wrap">
                        <button type="button" class="sx-dots-btn" aria-label="{{ __('teacher.actions') }}" onclick="togglePermDots(this)">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div class="sx-dots-menu">
                            <button type="button" class="sx-dots-link" onclick="viewPermissionProgress({{ $permission->permissionID }}); closePermDots();">
                                <i class="bi bi-eye"></i> {{ __('teacher.hr.view_progress') }}
                            </button>
                            <button type="button" class="sx-dots-link" onclick='viewPermissionDetails(@json($pdPayloadGrid)); closePermDots();'>
                                <i class="bi bi-card-list"></i> {{ __('teacher.hr.view_details') }}
                            </button>
                            @if($permission->status === 'rejected')
                            <a class="sx-dots-link" href="{{ route('teacher.permissions', ['tab' => 'request', 'edit_id' => $permission->permissionID]) }}">
                                <i class="bi bi-pencil-square"></i> {{ __('teacher.hr.edit_resubmit') }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="sx-ep-meta mb-1"><i class="bi bi-clock"></i> {{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : __('teacher.hr.na') }}</div>
                <div class="sx-ep-title mb-1">{{ $reasonLabel }}</div>
                <div class="sx-ep-meta">
                    @if($permission->time_mode === 'days')
                        {{ \Carbon\Carbon::parse($permission->start_date)->format('j M Y') }} → {{ \Carbon\Carbon::parse($permission->end_date)->format('j M Y') }}
                        · {{ $permission->days_count }} {{ __('teacher.hr.days') }}
                    @else
                        {{ $permission->start_time }} → {{ $permission->end_time }}
                    @endif
                </div>
                @if($permission->status === 'rejected' && $permission->admin_response)
                <div class="mt-2 p-2" style="background:#fff5f5;border-left:3px solid #dc3545;font-size:.78rem;color:#dc3545;font-weight:600;">
                    {{ $permission->admin_response }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
