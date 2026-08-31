@include('includes.Admin_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

<style>
    body, .content, .card, .btn, .table { font-family: "Century Gothic", Arial, sans-serif; }
    .card, .btn { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .store-badge-pill { display: inline-block; padding: 2px 8px; font-size: 0.72rem; border-radius: 10px; font-weight: 600; }
    .store-badge-pending { background: #fff3cd; color: #856404; }
    .store-badge-return-yes { background: #d4edda; color: #155724; }
    .store-badge-return-no { background: #f8d7da; color: #721c24; }
</style>

<div class="breadcrumbs">
    <div class="col-sm-8">
        <div class="page-header float-left">
            <div class="page-title"><h1>Items Requests</h1></div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card mb-3">
        <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
            <strong><i class="fa fa-clock-o"></i> Requested Item Requests</strong>
            <a href="{{ route('manage_store') }}" class="btn btn-light btn-sm"><i class="fa fa-archive"></i> Manage Store</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped" id="pendingRequestsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Requester</th>
                            <th>Items Requested</th>
                            <th>Expected Return</th>
                            <th>Requested</th>
                            <th>Notes</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRequests as $i => $req)
                        <tr data-request-id="{{ $req['requestID'] }}">
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <strong>{{ $req['requester_name'] }}</strong>
                                <small class="d-block text-muted">{{ $req['requester_type_label'] }}</small>
                            </td>
                            <td class="small">{{ $req['items_summary'] }}</td>
                            <td>{{ $req['expected_return_date'] ?? '-' }}</td>
                            <td class="small">{{ $req['requested_at'] }}</td>
                            <td class="small">{{ $req['notes'] ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success btn-approve-request" data-id="{{ $req['requestID'] }}"><i class="fa fa-check"></i> Confirm</button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-reject-request" data-id="{{ $req['requestID'] }}"><i class="fa fa-times"></i> Reject</button>
                            </td>
                        </tr>
                        @empty
                        <tr id="noPendingAdminRequests"><td colspan="7" class="text-center text-muted py-4">No pending item requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light"><strong><i class="fa fa-history"></i> Recent Reviewed Requests</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped" id="recentRequestsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Requester</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Requested</th>
                            <th>Reviewed</th>
                            <th>Review Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $i => $req)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $req['requester_name'] }} <small class="text-muted">({{ $req['requester_type_label'] }})</small></td>
                            <td>
                                <span class="store-badge-pill store-badge-{{ $req['status'] === 'approved' ? 'return-yes' : ($req['status'] === 'rejected' ? 'return-no' : 'pending') }}">
                                    {{ ucfirst($req['status']) }}
                                </span>
                            </td>
                            <td class="small">{{ $req['items_summary'] }}</td>
                            <td class="small">{{ $req['requested_at'] }}</td>
                            <td class="small">{{ $req['reviewed_at'] ?? '-' }}<br><small class="text-muted">{{ $req['reviewed_by'] ?? '' }}</small></td>
                            <td class="small">{{ $req['review_notes'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No reviewed requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const approveUrl = (id) => `{{ url('store/item-requests') }}/${id}/approve`;
    const rejectUrl = (id) => `{{ url('store/item-requests') }}/${id}/reject`;

    $('#pendingRequestsTable, #recentRequestsTable').DataTable({ pageLength: 10, order: [] });

    async function postJson(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(data || {}),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Request failed');
        return json;
    }

    $(document).on('click', '.btn-approve-request', async function () {
        const id = $(this).data('id');
        const result = await Swal.fire({
            icon: 'question',
            title: 'Confirm request?',
            text: 'Items will be issued to the requester from store stock.',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Yes, approve',
        });
        if (!result.isConfirmed) return;
        try {
            const json = await postJson(approveUrl(id), {});
            Swal.fire({ icon: 'success', title: 'Approved', text: json.message, confirmButtonColor: '#940000' });
            $(`tr[data-request-id="${id}"]`).fadeOut(300, function () {
                $(this).remove();
                if (!$('#pendingRequestsTable tbody tr').length) {
                    $('#pendingRequestsTable tbody').append('<tr id="noPendingAdminRequests"><td colspan="7" class="text-center text-muted py-4">No pending item requests.</td></tr>');
                }
            });
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#940000' });
        }
    });

    $(document).on('click', '.btn-reject-request', async function () {
        const id = $(this).data('id');
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Reject request?',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Enter reason for rejection...',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Reject',
        });
        if (!result.isConfirmed) return;
        try {
            const json = await postJson(rejectUrl(id), { review_notes: result.value || null });
            Swal.fire({ icon: 'success', title: 'Rejected', text: json.message, confirmButtonColor: '#940000' });
            $(`tr[data-request-id="${id}"]`).fadeOut(300, function () { $(this).remove(); });
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#940000' });
        }
    });
})();
</script>
