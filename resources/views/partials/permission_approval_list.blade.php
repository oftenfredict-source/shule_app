@forelse($permissions as $permission)
    @php
        $requesterName = 'N/A';
        if($permission->requester_type === 'student' && $permission->student) {
            $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
        } elseif($permission->requester_type === 'staff' && $permission->staff) {
            $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
        } elseif($permission->teacher) {
            $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
        }
        
        $requesterInfo = ucfirst($permission->requester_type);
        if($permission->requester_type === 'student' && $permission->student && $permission->student->subclass) {
            $requesterInfo .= ' | ' . ($permission->student->subclass->class->class_name ?? '') . ' ' . ($permission->student->subclass->subclass_name ?? '');
        }

        // Logic to determine if it's THIS user's turn
        $isMyTurn = false;
        
        // 1. If user is Admin and it's stage 0 or final
        if ($userType === 'Admin') {
            $maxChainOrder = DB::table('permission_approval_chains')
                ->where('schoolID', $permission->schoolID)
                ->where('permission_type', $permission->requester_type)
                ->max('approval_order') ?? 0;
            
            if ($permission->current_approval_order == 0 || $permission->current_approval_order > $maxChainOrder) {
                $isMyTurn = true;
            }
        }

        // 2. If user is in the chain and it matches their order
        if (!$isMyTurn) {
            $myChainOrders = DB::table('permission_approval_chains')
                ->where('schoolID', $permission->schoolID)
                ->where('permission_type', $permission->requester_type)
                ->where(function($q) use ($myRoleIDs, $myProfessionID) {
                    if (!empty($myRoleIDs)) $q->orWhereIn('role_id', $myRoleIDs);
                    if ($myProfessionID) $q->orWhere('profession_id', $myProfessionID);
                })
                ->pluck('approval_order')
                ->toArray();
            
            if (in_array($permission->current_approval_order, $myChainOrders)) {
                $isMyTurn = true;
            }
        }
    @endphp
    <tr>
        <td>
            <strong>{{ $requesterName }}</strong>
            <br><small class="text-muted">{{ $requesterInfo }}</small>
        </td>
        <td>{{ ucfirst($permission->time_mode) }}</td>
        <td>
            @if($permission->time_mode === 'days')
                {{ $permission->start_date->format('M d, Y') }} - {{ $permission->end_date->format('M d, Y') }}
                <br><span class="badge badge-light border">({{ $permission->days_count }} days)</span>
            @else
                {{ $permission->start_date->format('M d, Y') }}<br>
                {{ $permission->start_time }} - {{ $permission->end_time }}
            @endif
        </td>
        <td>
            <strong>{{ $permission->reason_type }}</strong><br>
            <span class="text-truncate d-inline-block" style="max-width: 150px;">{{ $permission->reason_description }}</span>
            <br>
            @if(!$isMyTurn)
                <span class="badge badge-info shadow-sm"><i class="fa fa-hourglass-half"></i> Awaiting Previous Stage</span>
            @else
                <span class="badge badge-warning shadow-sm"><i class="fa fa-bell"></i> Your Turn</span>
            @endif
        </td>
        <td class="text-center">
            <div class="dropdown">
                <div class="action-circle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </div>
                <div class="dropdown-menu dropdown-menu-right">
                    @if($isMyTurn)
                    <a class="dropdown-item text-success" href="#" onclick="openApprovalModal({{ $permission->permissionID }}); return false;">
                        <i class="fa fa-check-circle"></i> Approve
                    </a>
                    <a class="dropdown-item text-danger" href="#" onclick="openRejectionModal({{ $permission->permissionID }}); return false;">
                        <i class="fa fa-times-circle"></i> Reject
                    </a>
                    @else
                    <a class="dropdown-item text-muted disabled" href="#" onclick="return false;">
                        <i class="fa fa-lock"></i> Approval Locked
                    </a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}); return false;">
                        <i class="fa fa-project-diagram"></i> View Progress
                    </a>
                    <a class="dropdown-item" href="#" onclick="viewPermissionDetails({{ json_encode([
                        'id' => $permission->permissionID,
                        'requester_name' => $requesterName,
                        'requester_info' => $requesterInfo,
                        'time_mode' => ucfirst($permission->time_mode),
                        'days_count' => $permission->days_count,
                        'start_date' => $permission->start_date ? $permission->start_date->format('j F Y') : null,
                        'end_date' => $permission->end_date ? $permission->end_date->format('j F Y') : null,
                        'start_time' => $permission->start_time,
                        'end_time' => $permission->end_time,
                        'reason_type' => ucfirst($permission->reason_type),
                        'reason_description' => $permission->reason_description,
                        'status' => ucfirst($permission->status),
                        'requested_at' => $permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'
                    ]) }}); return false;">
                        <i class="fa fa-list-alt"></i> View More
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.permissions.download', $permission->permissionID) }}">
                        <i class="fa fa-file-pdf"></i> Download PDF
                    </a>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center p-4">
            <img src="{{ asset('img/no-data.svg') }}" style="width: 100px; opacity: 0.5;" class="mb-3">
            <p class="text-muted">No pending permissions for your review.</p>
        </td>
    </tr>
@endforelse
