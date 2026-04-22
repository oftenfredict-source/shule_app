@extends('layouts.parent_corp')

@section('title', 'Make Payments - ShuleXpert')

@section('content')
<style>
    .filter-card {
        background: white;
        padding: 25px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .stat-card-premium {
        background: white;
        padding: 20px;
        border-left: 5px solid #eee;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        height: 100%;
    }
    .stat-card-premium.outstanding { border-left-color: #dc3545; }
    .stat-card-premium.paid { border-left-color: #28a745; }
    .stat-card-premium.balance { border-left-color: #940000; }
    
    .stat-v { font-size: 22px; font-weight: 800; color: #333; }
    .stat-l { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #777; font-weight: 700; margin-bottom: 5px; }

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
    .table th {
        background: #fcfcfc;
        color: #940000;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        font-weight: 800;
    }
    .control-number {
        font-family: 'Courier New', monospace;
        font-weight: 800;
        color: #940000;
        background: #fff5f5;
        padding: 2px 5px;
        border: 1px dashed #940000;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="section-title"><i class="bx bx-credit-card"></i> Payment & Billing</div>
    </div>

    <!-- Statistics Row -->
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="stat-card-premium outstanding">
                    <div class="stat-l">Amount Required</div>
                    <div class="stat-v text-danger" id="statTotalRequired">TZS 0.00</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-premium paid">
                    <div class="stat-l">Total Amount Paid</div>
                    <div class="stat-v text-success" id="statTotalPaid">TZS 0.00</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-premium balance">
                    <div class="stat-l">Outstanding Balance</div>
                    <div class="stat-v" style="color:#940000;" id="statOutstandingBalance">TZS 0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="col-12">
        <div class="filter-card">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-uppercase small font-weight-bold">Search Name</label>
                    <input type="text" class="form-control" id="searchStudentInput" placeholder="Child name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-uppercase small font-weight-bold">Academic Year</label>
                    <select class="form-select" id="filterYear">
                        @foreach($availableYears ?? [date('Y')] as $y)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-uppercase small font-weight-bold">Fee Type</label>
                    <select class="form-select" id="filterFeeType">
                        <option value="">All Fees</option>
                        <option value="Tuition Fees">Tuition Fees</option>
                        <option value="Other Fees">Other Fees</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small font-weight-bold">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" id="clearFiltersBtn"><i class="bx bx-reset"></i> Reset Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments List -->
    <div class="col-12 mt-3">
        <div class="bg-white shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Child</th>
                            <th>Fee Category</th>
                            <th>Control Number</th>
                            <th class="text-end">Required</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <!-- AJAX Content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function loadData() {
            $('#paymentsTableBody').html('<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>');
            $.ajax({
                url: '{{ route("get_parent_payments_ajax") }}',
                data: {
                    search: $('#searchStudentInput').val(),
                    year: $('#filterYear').val(),
                    fee_type: $('#filterFeeType').val()
                },
                success: function(res) {
                    if (res.success) {
                        let html = '';
                        res.data.forEach((item, i) => {
                            let p = item.payment;
                            let s = item.student;
                            let statusBadge = p.payment_status === 'Paid' ? 'bg-success' : 'bg-warning';
                            html += `<tr>
                                <td>${i+1}</td>
                                <td><div style="font-weight:800; color:#333;">${s.first_name} ${s.last_name}</div><div class="small text-muted">${s.admission_number || 'N/A'}</div></td>
                                <td><span class="badge ${p.fee_type==='Tuition Fees'?'bg-success':'bg-info'}" style="border-radius:0; font-size:9px;">${p.fee_type}</span></td>
                                <td><span class="control-number">${p.control_number || 'PENDING'}</span></td>
                                <td class="text-end" style="font-weight:700;">TZS ${parseFloat(p.amount_required).toLocaleString()}</td>
                                <td class="text-end text-success" style="font-weight:700;">TZS ${parseFloat(p.amount_paid).toLocaleString()}</td>
                                <td class="text-end text-danger" style="font-weight:700;">TZS ${parseFloat(p.balance).toLocaleString()}</td>
                                <td class="text-center"><span class="badge ${statusBadge}" style="border-radius:0; font-size:9px;">${p.payment_status.toUpperCase()}</span></td>
                            </tr>`;
                        });
                        $('#paymentsTableBody').html(html || '<tr><td colspan="8" class="text-center py-5">No records found.</td></tr>');
                        
                        // Update stats
                        let st = res.statistics;
                        $('#statTotalRequired').text('TZS ' + parseFloat(st.total_required).toLocaleString());
                        $('#statTotalPaid').text('TZS ' + parseFloat(st.total_paid).toLocaleString());
                        $('#statOutstandingBalance').text('TZS ' + parseFloat(st.total_balance).toLocaleString());
                    }
                }
            });
        }

        $('#searchStudentInput').on('input', function() {
            clearTimeout(window.searchTimer);
            window.searchTimer = setTimeout(loadData, 500);
        });

        $('#filterYear, #filterFeeType').on('change', loadData);
        $('#clearFiltersBtn').on('click', function() {
            $('#searchStudentInput').val('');
            $('#filterFeeType').val('');
            loadData();
        });

        loadData();
    });
</script>
@endpush
