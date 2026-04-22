@extends('layouts.parent_corp')

@section('title', 'Permission Requests - ShuleXpert')

@section('content')
<style>
    .selection-card {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .permission-tabs .nav-link {
        border-radius: 0 !important;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        color: #777;
        padding: 12px 20px;
    }
    .permission-tabs .nav-link.active {
        background: #fff5f5 !important;
        color: #940000 !important;
        border-color: #940000 !important;
        border-bottom: 3px solid #940000 !important;
    }
    .tab-content-area {
        background: white;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-check-shield"></i> Student Permissions</div>
    </div>

    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-transparent p-0">
                <ul class="nav nav-tabs permission-tabs border-0 bg-white shadow-sm">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'request']) }}">New Request</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'pending']) }}">Pending 
                            @if($unreadPendingCount > 0) <span class="badge bg-danger ms-1" style="border-radius:0;">{{ $unreadPendingCount }}</span> @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'approved']) }}">Completed
                            @if($unreadApprovedCount > 0) <span class="badge bg-success ms-1" style="border-radius:0;">{{ $unreadApprovedCount }}</span> @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'rejected']) }}">Rejected
                            @if($unreadRejectedCount > 0) <span class="badge bg-secondary ms-1" style="border-radius:0;">{{ $unreadRejectedCount }}</span> @endif
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0 mt-4 tab-content-area">
                @if($activeTab === 'request')
                    <h6 class="font-weight-bold mb-4" style="color:#940000; border-left:4px solid #940000; padding-left:15px;">SUBMIT NEW PERMISSION REQUEST</h6>
                    <form method="POST" action="{{ route('parent.permissions.store') }}" enctype="multipart/form-data" id="parentPermissionForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Student</label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">-- Select Child --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->studentID }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Departure Date</label>
                                <input type="date" class="form-control" name="start_date" id="parentStartDate" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Expected Return</label>
                                <input type="date" class="form-control" name="end_date" id="parentEndDate" required>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label small font-weight-bold text-uppercase">Permission Category</label>
                                <select class="form-select" name="reason_type" id="parentReasonType" required>
                                    <option value="medical">Medical / Health</option>
                                    <option value="official">Official Matters</option>
                                    <option value="emergency">Family Emergency</option>
                                    <option value="other">Other Reason</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2" id="medicalAttachmentField" style="display:none;">
                                <label class="form-label small font-weight-bold text-uppercase">Proof / Attachment (PDF/Image)</label>
                                <input type="file" class="form-control" name="attachment">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label small font-weight-bold text-uppercase">Detailed Description</label>
                                <textarea class="form-control" name="reason_description" rows="4" placeholder="Explain the reason for permission clearly..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary px-5" type="submit" id="parentPermissionSubmit"><i class="bx bx-paper-plane"></i> SEND REQUEST</button>
                            </div>
                        </div>
                    </form>
                @else
                    @include('Parent.partials.permission_table', ['permissions' => ($activeTab==='pending'?$pendingPermissions:($activeTab==='approved'?$approvedPermissions:$rejectedPermissions)), 'studentNames' => $studentNames])
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#parentReasonType').on('change', function() {
            if ($(this).val() === 'medical') $('#medicalAttachmentField').show();
            else $('#medicalAttachmentField').hide();
        });

        $('#parentPermissionForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#parentPermissionSubmit');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>SENDING...');
            
            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Sent!', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('disabled', false).html('<i class="bx bx-paper-plane"></i> SEND REQUEST');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Submission failed.', 'error');
                    btn.prop('disabled', false).html('<i class="bx bx-paper-plane"></i> SEND REQUEST');
                }
            });
        });
    });
</script>
@endpush
