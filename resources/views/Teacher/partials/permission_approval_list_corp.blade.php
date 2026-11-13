@forelse($permissions as $permission)
    @php
        $requesterName = __('teacher.hr.na');
        $isStudentPerm = $permission->is_student_permission ?? false;

        if ($isStudentPerm) {
            $requesterName = $permission->student_name ?? __('teacher.hr.na');
            $requesterInfo = __('teacher.student') . ' | ' . ($permission->class_name ?? __('teacher.hr.na'));
        } elseif ($permission->requester_type === 'student' && $permission->student) {
            $requesterName = trim($permission->student->first_name . ' ' . $permission->student->last_name);
            $requesterInfo = ucfirst($permission->requester_type);
            if ($permission->student->subclass) {
                $requesterInfo .= ' | ' . ($permission->student->subclass->class->class_name ?? '') . ' ' . ($permission->student->subclass->subclass_name ?? '');
            }
        } elseif ($permission->requester_type === 'staff' && $permission->staff) {
            $requesterName = trim($permission->staff->first_name . ' ' . $permission->staff->last_name);
            $requesterInfo = ucfirst($permission->requester_type);
        } elseif ($permission->teacher) {
            $requesterName = trim($permission->teacher->first_name . ' ' . $permission->teacher->last_name);
            $requesterInfo = ucfirst($permission->requester_type);
        } else {
            $requesterInfo = ucfirst($permission->requester_type ?? __('teacher.hr.unknown'));
        }

        $isMyTurn = false;
        $permissionType = $isStudentPerm ? 'student' : $permission->requester_type;
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
            $isMyTurn = $adminChain
                ? $permission->current_approval_order == $adminChain->approval_order
                : ($permission->current_approval_order == 0 || $permission->current_approval_order > $maxChainOrder);
        }
        if (!$isMyTurn) {
            $myChainOrders = [];
            if (\Illuminate\Support\Facades\Schema::hasTable('permission_approval_chains')) {
                $myChainOrders = DB::table('permission_approval_chains')
                    ->where('schoolID', $permission->schoolID)
                    ->where('permission_type', $permissionType)
                    ->where(function ($q) use ($myRoleIDs, $myProfessionID) {
                        if (!empty($myRoleIDs)) $q->orWhereIn('role_id', $myRoleIDs);
                        if ($myProfessionID) $q->orWhere('profession_id', $myProfessionID);
                    })
                    ->pluck('approval_order')
                    ->toArray();
            }
            $isMyTurn = in_array($permission->current_approval_order, $myChainOrders);
        }
        $viewOnlyClassTeacher = $isStudentPerm
            && ($permission->requester_type ?? '') === 'parent'
            && session('teacherID')
            && (int) session('teacherID') === (int) ($permission->class_teacher_id ?? 0);
        if ($viewOnlyClassTeacher) $isMyTurn = false;

        $startDateFmt = $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : '-';
        $endDateFmt = $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : '-';
        $reasonLabel = match($permission->reason_type) {
            'medical' => __('teacher.hr.medical'),
            'official' => __('teacher.hr.official'),
            'professional' => __('teacher.hr.professional'),
            'emergency' => __('teacher.hr.emergency'),
            'other' => __('teacher.hr.other'),
            default => ucfirst($permission->reason_type),
        };
        $modeLabel = $permission->time_mode === 'hours' ? __('teacher.hr.hours') : ucfirst(__('teacher.hr.days'));
        $detailsPayload = [
            'id' => $permission->permissionID,
            'requester_name' => $requesterName,
            'requester_info' => $requesterInfo,
            'time_mode' => ucfirst($permission->time_mode),
            'days_count' => $permission->days_count,
            'start_date' => $permission->start_date ? \Carbon\Carbon::parse($permission->start_date)->format('j F Y') : null,
            'end_date' => $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->format('j F Y') : null,
            'start_time' => $permission->start_time,
            'end_time' => $permission->end_time,
            'reason_type' => $reasonLabel,
            'reason_description' => $permission->reason_description,
            'status' => ucfirst($permission->status),
            'requested_at' => $permission->created_at ? \Carbon\Carbon::parse($permission->created_at)->format('j F Y H:i') : __('teacher.hr.na'),
            'is_student_permission' => $isStudentPerm,
        ];
    @endphp
    <tr>
        <td>
            <span class="sx-ep-title">{{ $requesterName }}</span>
            <span class="sx-ep-meta">{{ $requesterInfo }}</span>
        </td>
        <td><span class="tag tag-neutral">{{ $modeLabel }}</span></td>
        <td>
            @if($permission->time_mode === 'days')
                {{ $startDateFmt }} → {{ $endDateFmt }}
                <span class="sx-ep-meta">{{ $permission->days_count }} {{ __('teacher.hr.days') }}</span>
            @else
                {{ $startDateFmt }}
                <span class="sx-ep-meta">{{ $permission->start_time ?? '-' }} → {{ $permission->end_time ?? '-' }}</span>
            @endif
        </td>
        <td>
            <span class="sx-ep-title">{{ $reasonLabel }}</span>
            <span class="sx-ep-meta sx-approval-reason">{{ $permission->reason_description }}</span>
            @if($viewOnlyClassTeacher)
                <span class="tag tag-view"><i class="bi bi-eye"></i> {{ __('teacher.hr.view_only') }}</span>
            @elseif(!$isMyTurn)
                <span class="tag tag-waiting"><i class="bi bi-hourglass-split"></i> {{ __('teacher.hr.awaiting_previous') }}</span>
            @else
                <span class="tag tag-pending"><i class="bi bi-bell"></i> {{ __('teacher.hr.your_turn') }}</span>
            @endif
        </td>
        <td>
            <div class="sx-ep-actions sx-dots-wrap">
                <button type="button" class="sx-dots-btn" aria-label="{{ __('teacher.actions') }}" onclick="togglePermDots(this)">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="sx-dots-menu">
                    @if($isMyTurn)
                        <button type="button" class="sx-dots-link is-success" onclick="openApprovalModal({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }}); closePermDots();">
                            <i class="bi bi-check-circle"></i> {{ __('teacher.hr.approve') }}
                        </button>
                        <button type="button" class="sx-dots-link is-danger" onclick="openRejectionModal({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }}); closePermDots();">
                            <i class="bi bi-x-circle"></i> {{ __('teacher.hr.reject') }}
                        </button>
                    @else
                        <span class="sx-dots-link is-disabled"><i class="bi bi-{{ $viewOnlyClassTeacher ? 'eye' : 'lock' }}"></i> {{ $viewOnlyClassTeacher ? __('teacher.hr.view_only') : __('teacher.hr.approval_locked') }}</span>
                    @endif
                    <span class="sx-dots-divider"></span>
                    <button type="button" class="sx-dots-link" onclick="viewPermissionProgress({{ $permission->permissionID }}, {{ $isStudentPerm ? 'true' : 'false' }}); closePermDots();">
                        <i class="bi bi-diagram-3"></i> {{ __('teacher.hr.view_progress') }}
                    </button>
                    <button type="button" class="sx-dots-link" onclick='viewPermissionDetails(@json($detailsPayload)); closePermDots();'>
                        <i class="bi bi-card-list"></i> {{ __('teacher.hr.view_details') }}
                    </button>
                    <a class="sx-dots-link" href="{{ $isStudentPerm ? route('admin.student_permissions.download', $permission->permissionID) : route('admin.permissions.download', $permission->permissionID) }}">
                        <i class="bi bi-file-earmark-pdf"></i> {{ __('teacher.hr.download_pdf') }}
                    </a>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5">
            <div class="sx-ep-empty"><i class="bi bi-inbox"></i><div>{{ __('teacher.hr.no_pending') }}</div></div>
        </td>
    </tr>
@endforelse
