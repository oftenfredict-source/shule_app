@php
    $isPending = ($req['status'] ?? '') === 'requested';
    $canApprove = !empty($req['can_approve']) && $isPending;
    $statusPlain = match ($req['status'] ?? '') {
        'requested' => !empty($req['is_new']) ? 'Requested — New' : 'Requested',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
        'approved' => 'Approved',
        default => ucfirst($req['status'] ?? '—'),
    };
    $reqViewMore = [
        'title' => 'Item Request — ' . ($req['requester_name'] ?? ''),
        'layout' => 'widgets',
        'sections' => [[
            'title' => 'Request Details',
            'widgets' => array_values(array_filter([
                ['label' => 'Requester', 'value' => $req['requester_name'] ?? '—'],
                ['label' => 'Type', 'value' => $req['requester_type_label'] ?? '—'],
                ['label' => 'Status', 'value' => $statusPlain],
                ['label' => 'Items Requested', 'value' => $req['items_summary'] ?? '—'],
                ['label' => 'Expected Return', 'value' => $req['expected_return_date'] ?? '—'],
                ['label' => 'Requested', 'value' => $req['requested_at'] ?? '—'],
                ['label' => 'Requested By', 'value' => $req['requested_by'] ?? '—'],
                !$isPending ? ['label' => 'Reviewed', 'value' => ($req['reviewed_at'] ?? '—') . ($req['reviewed_by'] ? ' by ' . $req['reviewed_by'] : '')] : null,
                !$isPending && !empty($req['review_notes']) ? ['label' => 'Review Notes', 'value' => $req['review_notes']] : null,
                ['label' => 'Notes', 'value' => $req['notes'] ?? '—'],
            ])),
        ]],
    ];
@endphp
<tr data-request-id="{{ $req['requestID'] }}"
    data-row-type="item-request"
    data-return-policy="request"
    class="store-item-request-row {{ !empty($req['is_new']) ? 'is-new' : '' }} {{ $isPending ? 'is-pending' : 'is-reviewed' }}"
    data-view-more='@json($reqViewMore)'
    data-can-approve="{{ $canApprove ? '1' : '0' }}"
    title="{{ $canApprove ? 'Click to approve or reject this request' : '' }}">
    <td>{{ $rowNum }}</td>
    <td>
        <span class="font-weight-bold">{{ $req['requester_name'] }}</span>
        <br><small class="text-muted">{{ $req['requester_type_label'] }}</small>
        @if($isPending)
            <br><small class="text-request-highlight font-weight-bold">Pending approval</small>
        @endif
    </td>
    <td class="small">{{ $req['items_summary'] }}</td>
    <td class="small text-muted">—</td>
    <td>{!! $req['status_html'] ?? '' !!}</td>
    <td>{{ $req['expected_return_date'] ?? '—' }}</td>
    <td class="store-actions-cell">
        <div class="dropdown store-action-dropdown">
            <button class="btn btn-sm store-action-toggle" type="button" data-toggle="dropdown" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button>
            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                <button type="button" class="dropdown-item store-view-more-trigger"><i class="fa fa-eye text-muted"></i> View More</button>
                @if($canApprove)
                <button type="button" class="dropdown-item btn-approve-item-request" data-id="{{ $req['requestID'] }}"><i class="fa fa-check text-success"></i> Approve</button>
                <button type="button" class="dropdown-item btn-reject-item-request" data-id="{{ $req['requestID'] }}"><i class="fa fa-times text-danger"></i> Reject</button>
                @endif
            </div>
        </div>
    </td>
</tr>
