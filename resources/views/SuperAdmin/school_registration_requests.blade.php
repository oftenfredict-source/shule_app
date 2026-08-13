@extends('includes.superadmin_nav')

@section('content')
<style>
    :root { --brand: #940000; }
    .card, .btn, .form-control, .badge, .alert { border-radius: 0 !important; }
    .btn-brand { background: var(--brand); color: #fff; border: none; }
    .btn-brand:hover { background: #7a0000; color: #fff; }
</style>

<div class="container-fluid mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px;">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--brand);">School Registration Requests</h4>
            <p class="text-muted small mb-0">From public landing form. Pending until verified (no credentials on verify).</p>
        </div>
        <div class="d-flex flex-wrap" style="gap:8px;">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCopyRegLink"
                    data-url="{{ $publicRegisterUrl }}">
                <i class="bx bx-link me-1"></i> Copy Register Link
            </button>
            <a href="{{ route('superadmin.schools.register') }}" class="btn btn-brand btn-sm">
                <i class="bx bx-plus me-1"></i> Register School (Internal)
            </a>
        </div>
    </div>

    <div class="alert alert-light border mb-3 py-2">
        <strong>Public URL:</strong>
        <code id="publicRegUrl">{{ $publicRegisterUrl }}</code>
        <span class="text-muted small ms-2" id="copyFeedback"></span>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'pending']) }}"
           class="btn btn-sm {{ ($status ?? '') === 'pending' ? 'btn-brand' : 'btn-outline-secondary' }}">
            Pending ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'ready']) }}"
           class="btn btn-sm {{ ($status ?? '') === 'ready' ? 'btn-success' : 'btn-outline-secondary' }}">
            Ready ({{ $counts['ready'] }})
        </a>
        <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'rejected']) }}"
           class="btn btn-sm {{ ($status ?? '') === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
            Rejected ({{ $counts['rejected'] }})
        </a>
        <a href="{{ route('superadmin.schools.registration_requests', ['status' => 'all']) }}"
           class="btn btn-sm {{ ($status ?? '') === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
            All ({{ $counts['all'] }})
        </a>
    </div>

    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>School</th>
                            <th>Students</th>
                            <th>Type / Ownership</th>
                            <th>Location</th>
                            <th>Phone / Email</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $i => $row)
                            <tr>
                                <td>{{ $requests->firstItem() + $i }}</td>
                                <td>
                                    <div class="fw-bold">{{ $row->school_name }}</div>
                                    <div class="small text-muted">{{ $row->registration_number ?: 'No reg no.' }}</div>
                                    @if($row->contact_person)
                                        <div class="small">Contact: {{ $row->contact_person }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-white" style="background:#940000;">
                                        {{ number_format((int) $row->number_of_students) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $row->school_type }}</div>
                                    <div class="small text-muted">{{ $row->ownership }}</div>
                                </td>
                                <td class="small">
                                    {{ $row->region }} / {{ $row->district }}
                                    @if($row->ward)<br>{{ $row->ward }}@endif
                                </td>
                                <td class="small">
                                    <div>{{ $row->phone }}</div>
                                    <div class="text-muted">{{ $row->email ?: '—' }}</div>
                                </td>
                                <td>
                                    @if($row->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($row->status === 'ready')
                                        <span class="badge bg-success">Ready</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="small">{{ $row->created_at?->format('d M Y H:i') }}</td>
                                <td>
                                    @if($row->status === 'pending')
                                        <form method="POST" action="{{ route('superadmin.schools.registration_requests.verify', $row->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success mb-1"
                                                    onclick="return confirm('Mark as Ready? No login credentials will be created.')">
                                                Verify → Ready
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('superadmin.schools.registration_requests.reject', $row->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Reject this registration request?')">
                                                Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                    @if($row->notes)
                                        <div class="small text-muted mt-1" title="{{ $row->notes }}">Note: {{ \Illuminate\Support\Str::limit($row->notes, 40) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No registration requests in this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $requests->links() }}
    </div>

    <div class="card border mt-4">
        <div class="card-header bg-white">
            <strong style="color:#940000;">Blocked OTP Phones (School Registration)</strong>
            <div class="small text-muted">
                A phone is blocked after {{ \App\Models\OtpPhoneBlock::MAX_OTP_SENDS }} OTP requests until an admin releases it.
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Phone</th>
                            <th>OTP Sends</th>
                            <th>Blocked At</th>
                            <th>Reason</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($blockedOtpPhones ?? collect()) as $block)
                            <tr>
                                <td class="fw-bold">{{ $block->phone }}</td>
                                <td>{{ (int) $block->otp_send_count }}</td>
                                <td class="small">{{ $block->blocked_at?->format('d M Y H:i') ?: '-' }}</td>
                                <td class="small">{{ $block->block_reason ?: 'OTP abuse limit' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('superadmin.schools.registration_otp_blocks.release', $block->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-brand"
                                                onclick="return confirm('Release this phone so it can receive OTPs again?')">
                                            Release
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No blocked phones.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnCopyRegLink')?.addEventListener('click', function () {
    var url = this.getAttribute('data-url') || '';
    var feedback = document.getElementById('copyFeedback');
    function done(ok) {
        if (!feedback) return;
        feedback.textContent = ok ? 'Copied!' : 'Copy failed';
        feedback.style.color = ok ? '#1a7f37' : '#940000';
        setTimeout(function () { feedback.textContent = ''; }, 2500);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () { done(true); }).catch(function () { done(false); });
    } else {
        var ta = document.createElement('textarea');
        ta.value = url;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(true); } catch (e) { done(false); }
        document.body.removeChild(ta);
    }
});
</script>
@endsection
