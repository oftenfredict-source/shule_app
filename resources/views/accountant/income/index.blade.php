@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>School Income</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Accountant</a></li>
                    <li class="active">Income</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-1">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalToday) }}</span>
                    </h4>
                    <p class="text-light">Today's Income</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-2">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalWeek) }}</span>
                    </h4>
                    <p class="text-light">This Week</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-3">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalMonth) }}</span>
                    </h4>
                    <p class="text-light">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-4">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalYear) }}</span>
                    </h4>
                    <p class="text-light">Total Income ({{ $selectedYear }})</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Income Records ({{ $selectedYear }})</strong>
                        <div class="float-right d-flex align-items-center">
                            <form action="{{ route('accountant.income.index') }}" method="GET" class="mr-3">
                                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                                    @foreach($availableYears as $year)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>Fiscal Year {{ $year }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-sm mr-2 download-pdf-ajax" data-url="{{ route('accountant.reports.export_income_pdf', ['year' => $selectedYear]) }}" data-filename="Income_Report_{{ $selectedYear }}">
                                <i class="fa fa-file-pdf-o"></i> Download PDF
                            </button>
                            <a href="{{ route('accountant.income_categories.index') }}" class="btn btn-outline-primary btn-sm mr-2">
                                <i class="fa fa-list"></i> Categories
                            </a>
                            <a href="{{ route('accountant.income.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Income
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="bootstrap-data-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt No</th>
                                    <th>Category</th>
                                    <th>Payer</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomes as $income)
                                <tr>
                                    <td>{{ $income->date }}</td>
                                    <td>{{ $income->receipt_number }}</td>
                                    <td>{{ $income->income_category }}</td>
                                    <td>{{ $income->payer_name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($income->description, 25) }}</td>
                                    <td>{{ number_format($income->amount, 2) }}</td>
                                    <td>{{ $income->payment_method }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('accountant.income.show', $income->incomeID) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('accountant.income.edit', $income->incomeID) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('accountant.income.destroy', $income->incomeID) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this income record?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<!-- DOWNLOAD PROGRESS MODAL -->
<div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="fa fa-file-pdf-o fa-3x text-danger animate-pulse"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold;">Generating Income Report</h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 6px;">
                    <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                        role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small">Please wait, compiling income records...</p>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-pulse { animation: pulse 1.5s infinite; }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.download-pdf-ajax').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const baseFileName = this.getAttribute('data-filename') || 'Report';
            
            $('#downloadProgressModal').modal('show');
            $('#pdfProgressBar').css('width', '0%');
            $('#pdfProgressPercent').text('0%');

            let progress = 0;
            const interval = setInterval(function() {
                if (progress < 95) {
                    let inc = 2 + Math.random() * 5;
                    if (progress > 50) inc = 1 + Math.random() * 2;
                    if (progress > 80) inc = 0.5 + Math.random();
                    progress += inc;
                    if (progress > 95) progress = 95;
                    $('#pdfProgressBar').css('width', progress + '%');
                    $('#pdfProgressPercent').text(Math.round(progress) + '%');
                }
            }, 400);

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Server error');
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(interval);
                    $('#pdfProgressBar').css('width', '100%');
                    $('#pdfProgressPercent').text('100%');
                    
                    setTimeout(() => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        const timestamp = new Date().toISOString().slice(0, 10);
                        a.download = `${baseFileName}_${timestamp}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                        
                        setTimeout(() => {
                            $('#downloadProgressModal').modal('hide');
                        }, 800);
                    }, 400);
                })
                .catch(error => {
                    clearInterval(interval);
                    $('#downloadProgressModal').modal('hide');
                    alert('Failed to generate PDF. Please try again.');
                });
        });
    });
});
</script>
@endsection
