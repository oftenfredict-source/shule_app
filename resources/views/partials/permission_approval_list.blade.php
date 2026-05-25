@forelse($permissions as $permission)
    @php
        $requesterName = 'N/A';
        $isStudentPerm = $permission->is_student_permission ?? false;

        if($isStudentPerm) {
            // Handle student permissions from student_permissions table
            $requesterName = $permission->student_name ?? 'N/A';
            $requesterInfo = 'Student | ' . ($permission->class_name ?? 'N/A');
        } elseif($permission->requester_type === 'student' && $permission->student) {
            $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
            $requesterInfo = ucfirst($permission->requester_type);
            if($permission->student && $permission->student->subclass) {
                $requesterInfo .= ' | ' . ($permission->student->subclass->class->class_name ?? '') . ' ' . ($permission->student->subclass->subclass_name ?? '');
            }
        } elseif($permission->requester_type === 'staff' && $permission->staff) {
            $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
            $requesterInfo = ucfirst($permission->requester_type);
        } elseif($permission->teacher) {
            $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
            $requesterInfo = ucfirst($permission->requester_type);
        } else {
            $requesterInfo = ucfirst($permission->requester_type ?? 'Unknown');
        }

        // Logic to determine if it's THIS user's turn
        $isMyTurn = false;
        $permissionType = $isStudentPerm ? 'student' : $permission->requester_type;

        // 1. If user is Admin, check if they are in the chain and at their step
        if ($userType === 'Admin') {
            $adminChain = null;
            $maxChainOrder = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('permission_approval_chains')) {
                $adminChain = DB::table('permission_approval_chains')
                    ->where('schoolID', $permission->schoolID)
                    ->where('permission_type', $permissionType)
                    ->where('is_admin', true)
                    ->first();

                $maxChainOrder = DB::table('permission_approval_chains')
                    ->where('schoolID', $permission->schoolID)
                    ->where('permission_type', $permissionType)
                    ->max('approval_order') ?? 0;
            }

            if ($adminChain) {
                // Admin is in the chain - check if it's their step
                $isMyTurn = ($permission->current_approval_order == $adminChain->approval_order);
            } else {
                // Admin is not in the chain - only approve if chain is complete (current_approval_order > maxChainOrder or 0)
                $isMyTurn = ($permission->current_approval_order == 0 || $permission->current_approval_order > $maxChainOrder);
            }
        }

        // 2. If user is in the chain and it matches their order
        if (!$isMyTurn) {
            $myChainOrders = [];
            if (\Illuminate\Support\Facades\Schema::hasTable('permission_approval_chains')) {
                $myChainOrders = DB::table('permission_approval_chains')
                    ->where('schoolID', $permission->schoolID)
                    ->where('permission_type', $permissionType)
                    ->where(function($q) use ($myRoleIDs, $myProfessionID) {
                        if (!empty($myRoleIDs)) $q->orWhereIn('role_id', $myRoleIDs);
                        if ($myProfessionID) $q->orWhere('profession_id', $myProfessionID);
                    })
                    ->pluck('approval_order')
                    ->toArray();
            }

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
                @if($isStudentPerm)
                    {{ \Carbon\Carbon::parse($permission->start_date)->format('j F Y') }} - {{ \Carbon\Carbon::parse($permission->end_date)->format('j F Y') }}
                    <br><span class="badge badge-light border">({{ $permission->days_count }} days)</span>
                @else
                    {{ $permission->start_date->format('j F Y') }} - {{ $permission->end_date->format('j F Y') }}
                    <br><span class="badge badge-light border">({{ $permission->days_count }} days)</span>
                @endif
            @else
                @if($isStudentPerm)
                    {{ \Carbon\Carbon::parse($permission->start_date)->format('j F Y') }}<br>
                    {{ $permission->start_time }} - {{ $permission->end_time }}
                @else
                    {{ $permission->start_date->format('j F Y') }}<br>
                    {{ $permission->start_time }} - {{ $permission->end_time }}
                @endif
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
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-cog"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow border">
                    @if($isMyTurn)
                    <a class="dropdown-item text-success" href="#" onclick="openApprovalModal({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }}); return false;">
                        <i class="fa fa-check-circle"></i> Approve
                    </a>
                    <a class="dropdown-item text-danger" href="#" onclick="openRejectionModal({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }}); return false;">
                        <i class="fa fa-times-circle"></i> Reject
                    </a>
                    @else
                    <a class="dropdown-item text-muted disabled" href="#" onclick="return false;">
                        <i class="fa fa-lock"></i> Approval Locked
                    </a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="viewPermissionProgress({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }}); return false;">
                        <i class="fa fa-project-diagram"></i> View Progress
                    </a>
                    <a class="dropdown-item" href="#" onclick="viewPermissionDetails({{ json_encode([
                        'id' => $permission->permissionID,
                        'requester_name' => $requesterName,
                        'requester_info' => $requesterInfo,
                        'time_mode' => ucfirst($permission->time_mode),
                        'days_count' => $permission->days_count,
                        'start_date' => $isStudentPerm ? ($permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null) : ($permission->start_date ? $permission->start_date->format('j F Y') : null),
                        'end_date' => $isStudentPerm ? ($permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null) : ($permission->end_date ? $permission->end_date->format('j F Y') : null),
                        'start_time' => $permission->start_time,
                        'end_time' => $permission->end_time,
                        'reason_type' => ucfirst($permission->reason_type),
                        'reason_description' => $permission->reason_description,
                        'status' => ucfirst($permission->status),
                        'requested_at' => $isStudentPerm ? ($permission->created_at ? \Carbon\Carbon::parse($permission->created_at)->format('j F Y H:i') : 'N/A') : ($permission->created_at ? $permission->created_at->format('j F Y H:i') : 'N/A'),
                        'is_student_permission' => $isStudentPerm
                    ]) }}); return false;">
                        <i class="fa fa-list-alt"></i> View More
                    </a>
                    <a class="dropdown-item" href="{{ $isStudentPerm ? route('admin.student_permissions.download', $permission->permissionID) : route('admin.permissions.download', $permission->permissionID) }}">
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
