@if($permissions->count() === 0)
    <div class="empty-state">
        <i class="fi fi-rr-shield-check d-block mb-2" style="font-size:2rem;color:#940000;opacity:.45;"></i>
        {{ __('parent.no_permission_records') }}
    </div>
@else

@php
    $parentReasonSw = function ($type) {
        return match (strtolower((string) $type)) {
            'permission', 'official', 'family', 'emergency', 'other' => __('parent.permission'),
            'sick', 'medical' => __('parent.sick'),
            default => ucfirst((string) $type),
        };
    };
    $parentStatusSw = function ($status) {
        return match (strtolower((string) $status)) {
            'approved' => __('parent.approved'),
            'rejected' => __('parent.rejected'),
            'pending' => __('parent.pending_tab'),
            default => ucfirst((string) $status),
        };
    };
@endphp

<div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
    <small class="text-muted fw-bold">{{ __('parent.records_count', ['count' => $permissions->count()]) }}</small>
    <div class="btn-group btn-group-sm perm-view-toggle" role="group" aria-label="{{ __('parent.toggle_view') }}">
        <button type="button" class="btn btn-outline-light perm-view-btn" data-view="list" title="{{ __('parent.list_view') }}">
            <i class="fi fi-rr-list"></i>
        </button>
        <button type="button" class="btn btn-outline-light perm-view-btn" data-view="grid" title="{{ __('parent.card_view') }}">
            <i class="fi fi-rr-apps"></i>
        </button>
    </div>
</div>

<div class="perm-list-view">
    <div class="table-responsive">
        <table class="perm-table">
            <thead>
                <tr>
                    <th>{{ __('parent.request_date') }}</th>
                    <th>{{ __('parent.student_name') }}</th>
                    <th>{{ __('parent.period_duration') }}</th>
                    <th>{{ __('parent.type') }}</th>
                    <th class="text-center">{{ __('parent.status') }}</th>
                    <th class="text-center">{{ __('parent.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                    @php
                        $permId = $permission->permissionID ?? $permission->id;
                        $studentId = $permission->studentID ?? $permission->student_id;
                        $status = strtolower($permission->status);
                        $statusClass = match($status) {
                            'approved' => 'approved',
                            'rejected' => 'rejected',
                            default => 'pending'
                        };
                    @endphp
                    <tr>
                        <td>{{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : '---' }}</td>
                        <td><div class="child-name">{{ $studentNames[$studentId] ?? __('parent.processing_name') }}</div></td>
                        <td>
                            <div>
                                <i class="fi fi-rr-calendar me-1" style="color:#940000;"></i>
                                {{ \Carbon\Carbon::parse($permission->start_date)->locale(app()->getLocale())->isoFormat('D MMMM Y') }}
                                {{ __('parent.until') }}
                                {{ \Carbon\Carbon::parse($permission->end_date)->locale(app()->getLocale())->isoFormat('D MMMM Y') }}
                            </div>
                            <span class="days-chip">{{ $permission->days_count }} {{ __('parent.days_upper') }}</span>
                        </td>
                        <td>{{ $parentReasonSw($permission->reason_type) }}</td>
                        <td class="text-center">
                            <span class="status-badge {{ $statusClass }}">{{ $parentStatusSw($permission->status) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fi fi-rr-menu-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item py-2" href="#" onclick="viewPermissionProgress({{ $permId }}, true); return false;">
                                            <i class="fi fi-rr-eye me-2" style="color:#0dcaf0;"></i> {{ __('parent.view_progress') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="#" onclick="viewPermissionDetails({{ json_encode([
                                            'id' => $permId,
                                            'time_mode' => $permission->time_mode === 'days' ? __('parent.days_mode') : __('parent.hours_mode'),
                                            'days_count' => $permission->days_count,
                                            'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                            'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                            'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                            'start_time' => $permission->start_time,
                                            'end_time' => $permission->end_time,
                                            'reason_type' => $parentReasonSw($permission->reason_type),
                                            'reason_description' => $permission->reason_description,
                                            'status' => $parentStatusSw($permission->status),
                                            'admin_response' => $permission->admin_response ?? null,
                                            'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                                        ]) }}); return false;">
                                            <i class="fi fi-rr-list me-2"></i> {{ __('parent.view_details') }}
                                        </a>
                                    </li>
                                    @if($permission->status === 'rejected')
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ url()->current() }}?tab=request&edit_id={{ $permId }}">
                                            <i class="fi fi-rr-edit me-2" style="color:#940000;"></i> {{ __('parent.edit_resubmit_action') }}
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
</div>

<div class="perm-grid-view" style="display:none;">
    <div class="row g-3">
        @foreach($permissions as $permission)
        @php
            $permId = $permission->permissionID ?? $permission->id;
            $studentId = $permission->studentID ?? $permission->student_id;
            $status = strtolower($permission->status);
            $statusClass = match($status) { 'approved' => 'approved', 'rejected' => 'rejected', default => 'pending' };
            $cardClass = match($status) { 'approved' => 'is-approved', 'rejected' => 'is-rejected', default => 'is-pending' };
            $studentLabel = $studentNames[$studentId] ?? __('parent.processing_name');
        @endphp
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="perm-card {{ $cardClass }}">
                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                    <div class="min-w-0">
                        <div class="child-name text-truncate">{{ $studentLabel }}</div>
                        <div class="small text-muted">
                            <i class="fi fi-rr-clock me-1"></i>
                            {{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : 'N/A' }}
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fi fi-rr-menu-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item py-2" href="#" onclick="viewPermissionProgress({{ $permId }}, true); return false;"><i class="fi fi-rr-eye me-2"></i> {{ __('parent.view_progress') }}</a></li>
                            <li><a class="dropdown-item py-2" href="#" onclick="viewPermissionDetails({{ json_encode([
                                'id' => $permId,
                                'time_mode' => $permission->time_mode === 'days' ? __('parent.days_mode') : __('parent.hours_mode'),
                                'days_count' => $permission->days_count,
                                'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
                                'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
                                'return_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->addDay()->format('j F Y') : null,
                                'start_time' => $permission->start_time,
                                'end_time' => $permission->end_time,
                                'reason_type' => $parentReasonSw($permission->reason_type),
                                'reason_description' => $permission->reason_description,
                                'status' => $parentStatusSw($permission->status),
                                'admin_response' => $permission->admin_response ?? null,
                                'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                            ]) }}); return false;"><i class="fi fi-rr-list me-2"></i> {{ __('parent.view_details') }}</a></li>
                            @if($permission->status === 'rejected')
                            <li><a class="dropdown-item py-2" href="{{ url()->current() }}?tab=request&edit_id={{ $permId }}"><i class="fi fi-rr-edit me-2"></i> {{ __('parent.edit_resubmit_action') }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="mb-2 small">
                    <i class="fi fi-rr-calendar me-1" style="color:#940000;"></i>
                    {{ \Carbon\Carbon::parse($permission->start_date)->format('j M Y') }}
                    &rarr;
                    {{ \Carbon\Carbon::parse($permission->end_date)->format('j M Y') }}
                    <span class="days-chip ms-1">{{ $permission->days_count }} {{ __('parent.days') }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="small text-muted fw-bold">{{ $parentReasonSw($permission->reason_type) }}</span>
                    <span class="status-badge {{ $statusClass }}">{{ $parentStatusSw($permission->status) }}</span>
                </div>

                @if($permission->status === 'rejected' && !empty($permission->admin_response))
                <div class="reject-note">
                    <strong>{{ __('parent.reason') }}:</strong> {{ $permission->admin_response }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

@endif
