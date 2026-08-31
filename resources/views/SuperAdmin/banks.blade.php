@extends('includes.superadmin_nav')

@section('title', 'Bank Registration')

@section('content')
<style>
    .page-hero {
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border: 1px solid #f0dada;
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    .page-hero h4 { color: #940000; font-weight: 800; margin: 0; }
    .card, .btn, .form-control, .form-select, .modal-content, .badge { border-radius: 0 !important; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; font-weight: 600; }
    .btn-primary-custom:hover { background: #7a0000; border-color: #7a0000; color: #fff; }
    .table thead th { background: whitesmoke; color: #940000; border-bottom: 2px solid #940000; }
    .btn-outline-primary { color: #940000 !important; border-color: #940000 !important; background: #fff; }
    .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
        background: #940000 !important; border-color: #940000 !important; color: #fff !important; box-shadow: none !important;
    }
    .text-primary { color: #940000 !important; }
    .form-control:focus, .form-select:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15) !important;
    }
</style>

<div class="container-fluid mt-3">
    <div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4><i class="bx bx-buildings me-2"></i>Bank Registration</h4>
            <p class="mb-0 text-muted small">Register banks used for fee collection integrations (API partners).</p>
        </div>
        <button type="button" class="btn btn-primary-custom" id="btnOpenCreateBank">
            <i class="bx bx-plus-circle"></i> Register Bank
        </button>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="banksTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bank Name</th>
                            <th>Bank Code</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banks as $i => $bank)
                            <tr data-bank-id="{{ $bank->bankID }}">
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-bold">{{ $bank->bank_name }}</td>
                                <td>{{ $bank->bank_code ?: '-' }}</td>
                                <td>
                                    @if($bank->status === 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-bank"
                                        data-id="{{ $bank->bankID }}"
                                        data-name="{{ $bank->bank_name }}"
                                        data-code="{{ $bank->bank_code }}"
                                        data-status="{{ $bank->status }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bank"
                                        data-id="{{ $bank->bankID }}"
                                        data-name="{{ $bank->bank_name }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No banks registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: whitesmoke; border-bottom: 2px solid #940000;">
                <h5 class="modal-title text-primary fw-bold" id="bankModalTitle">Register Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bankID">
                <div class="mb-3">
                    <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="bank_name" placeholder="e.g. NBC">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Bank Code</label>
                    <input type="text" class="form-control" id="bank_code" placeholder="e.g. NBC">
                    <small class="text-muted">Short code shown next to account numbers (e.g. NBC223 (NBC)).</small>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Status</label>
                    <select class="form-select" id="bank_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="btnSaveBank">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    let bankModal;

    function getModal() {
        if (!bankModal) bankModal = new bootstrap.Modal(document.getElementById('bankModal'));
        return bankModal;
    }

    $('#btnOpenCreateBank').on('click', function () {
        $('#bankModalTitle').text('Register Bank');
        $('#bankID').val('');
        $('#bank_name').val('');
        $('#bank_code').val('');
        $('#bank_status').val('Active');
        getModal().show();
    });

    $(document).on('click', '.btn-edit-bank', function () {
        $('#bankModalTitle').text('Edit Bank');
        $('#bankID').val($(this).data('id'));
        $('#bank_name').val($(this).data('name'));
        $('#bank_code').val($(this).data('code') || '');
        $('#bank_status').val($(this).data('status'));
        getModal().show();
    });

    $('#btnSaveBank').on('click', function () {
        const id = $('#bankID').val();
        const payload = {
            bank_name: $('#bank_name').val(),
            bank_code: $('#bank_code').val(),
            status: $('#bank_status').val(),
            _token: csrf
        };
        const url = id
            ? "{{ url('super-admin/banks') }}/" + id
            : "{{ route('superadmin.banks.store') }}";
        const method = id ? 'POST' : 'POST';
        if (id) payload._method = 'PUT';

        const $btn = $(this).prop('disabled', true);
        $.ajax({
            url: url,
            method: method,
            data: payload,
            success: function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Done', text: res.message, timer: 1600, showConfirmButton: false });
                    setTimeout(() => location.reload(), 900);
                } else {
                    Swal.fire('Error', res.error || 'Failed', 'error');
                }
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Request failed', 'error');
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    $(document).on('click', '.btn-delete-bank', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Delete bank?',
            text: name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Yes, delete'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ url('super-admin/banks') }}/" + id,
                method: 'POST',
                data: { _token: csrf, _method: 'DELETE' },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
                        setTimeout(() => location.reload(), 800);
                    } else {
                        Swal.fire('Error', res.error || 'Failed', 'error');
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Request failed', 'error');
                }
            });
        });
    });
});
</script>
@endsection
