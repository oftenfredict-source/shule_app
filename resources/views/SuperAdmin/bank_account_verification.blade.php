@extends('includes.superadmin_nav')

@section('title', 'Bank Account Verification')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border-radius: 0 !important;
        border: 1px solid #ced4da;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-container { border-radius: 0 !important; width: 100% !important; }
    .select2-dropdown { border-radius: 0 !important; border-color: #940000; }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #940000;
        color: #fff;
    }
    .select2-container--default .select2-results__option--selected {
        background-color: whitesmoke;
        color: #940000;
    }
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #940000;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 0 !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #940000;
        outline: none;
    }
</style>
<style>
    .page-hero {
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border: 1px solid #f0dada;
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    .page-hero h4 { color: #940000; font-weight: 800; margin: 0; }
    .card, .btn, .form-control, .form-select, .badge { border-radius: 0 !important; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; font-weight: 600; }
    .btn-primary-custom:hover { background: #7a0000; color: #fff; }
    .table thead th { background: whitesmoke; color: #940000; border-bottom: 2px solid #940000; }
    .btn-outline-primary, .btn-outline-danger {
        border-radius: 0 !important;
    }
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
    <div class="page-hero">
        <h4><i class="bx bx-check-shield me-2"></i>Bank Account Verification</h4>
        <p class="mb-0 text-muted small">Select a school to review pending bank accounts for Multi Account Fees. Approve or reject — school Admin will receive SMS.</p>
    </div>

    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold">School</label>
                    <select class="form-select" id="filterSchool">
                        <option value="">Select school...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->schoolID }}" data-phone="{{ $school->phone }}">
                                {{ $school->school_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary-custom w-100" id="btnLoadAccounts">
                        <i class="bx bx-search"></i> Load Accounts
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="accountsTable">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Account Name</th>
                            <th>Bank</th>
                            <th>Status</th>
                            <th>OTP Verified</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center text-muted py-4">Select a school to load accounts.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');

    $('#filterSchool').select2({
        width: '100%',
        placeholder: '-- Search and Select School --',
        allowClear: true
    });

    function statusBadge(status) {
        if (status === 'approved') return '<span class="badge bg-success">Approved</span>';
        if (status === 'rejected') return '<span class="badge bg-danger">Rejected</span>';
        return '<span class="badge bg-warning text-dark">Pending</span>';
    }

    function loadAccounts() {
        const schoolID = $('#filterSchool').val();
        if (!schoolID) {
            Swal.fire('Select school', 'Please select a school first.', 'info');
            return;
        }
        const $tbody = $('#accountsTable tbody').html('<tr><td colspan="7" class="text-center py-4">Loading...</td></tr>');
        $.get("{{ route('superadmin.bank_accounts.list') }}", { schoolID: schoolID }, function (res) {
            if (!res.success) {
                $tbody.html('<tr><td colspan="7" class="text-center text-danger py-4">' + (res.error || 'Failed') + '</td></tr>');
                return;
            }
            if (!res.accounts.length) {
                $tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">No bank accounts registered for this school.</td></tr>');
                return;
            }
            let html = '';
            res.accounts.forEach(function (a) {
                let actions = '—';
                if (a.status === 'pending' || a.status === 'rejected') {
                    actions = '<button class="btn btn-sm btn-success btn-approve me-1" data-id="' + a.accountID + '"><i class="bx bx-check"></i> Approve</button>' +
                        '<button class="btn btn-sm btn-outline-danger btn-reject" data-id="' + a.accountID + '"><i class="bx bx-x"></i> Reject</button>';
                } else if (a.status === 'approved') {
                    actions = '<button class="btn btn-sm btn-outline-danger btn-reject" data-id="' + a.accountID + '"><i class="bx bx-x"></i> Reject</button>';
                }
                html += '<tr>' +
                    '<td class="fw-bold">' + a.account_number + '</td>' +
                    '<td>' + (a.account_name || '—') + '</td>' +
                    '<td>' + a.bank_name + (a.bank_code ? ' <small class="text-muted">(' + a.bank_code + ')</small>' : '') + '</td>' +
                    '<td>' + statusBadge(a.status) + '</td>' +
                    '<td>' + (a.otp_verified_at || '—') + '</td>' +
                    '<td class="small">' + (a.status === 'rejected' && a.rejection_reason ? ('<span class="text-danger">' + a.rejection_reason + '</span>') : (a.reviewed_at || '—')) + '</td>' +
                    '<td class="text-end text-nowrap">' + actions + '</td>' +
                    '</tr>';
            });
            $tbody.html(html);
        }).fail(function () {
            $tbody.html('<tr><td colspan="7" class="text-center text-danger py-4">Failed to load accounts.</td></tr>');
        });
    }

    $('#btnLoadAccounts').on('click', loadAccounts);
    $('#filterSchool').on('change', function () {
        if ($(this).val()) loadAccounts();
    });

    $(document).on('click', '.btn-approve', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Approve this account?',
            text: 'School Admin will receive an SMS notification.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Approve'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post("{{ url('super-admin/bank-accounts') }}/" + id + "/approve", { _token: csrf }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Approved', text: res.message, timer: 1600, showConfirmButton: false });
                    loadAccounts();
                } else {
                    Swal.fire('Error', res.error || 'Failed', 'error');
                }
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Request failed', 'error');
            });
        });
    });

    $(document).on('click', '.btn-reject', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Reject account',
            input: 'textarea',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'Explain why this account is rejected...',
            inputAttributes: { 'aria-label': 'Rejection reason' },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Reject',
            inputValidator: (value) => {
                if (!value || !value.trim()) return 'Rejection reason is required';
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post("{{ url('super-admin/bank-accounts') }}/" + id + "/reject", {
                _token: csrf,
                rejection_reason: result.value
            }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Rejected', text: res.message, timer: 1600, showConfirmButton: false });
                    loadAccounts();
                } else {
                    Swal.fire('Error', res.error || 'Failed', 'error');
                }
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Request failed', 'error');
            });
        });
    });
});
</script>
@endsection
