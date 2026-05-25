@extends('layouts.vali')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Financial Reports</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <div class="d-flex align-items-center mt-2">
                    <form method="GET" action="{{ route('accountant.reports.index') }}" class="form-inline mr-3">
                        <label class="mr-2">Fiscal Year:</label>
                        <select name="year" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            @php $currentYear = date('Y'); @endphp
                            @for($y = $currentYear + 1; $y >= $currentYear - 3; $y--)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="btn btn-primary-custom btn-sm mr-1">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-danger-custom btn-sm download-pdf-ajax" data-url="{{ route('accountant.reports.export_pdf', ['year' => $selectedYear]) }}" data-filename="Financial_Report_{{ $selectedYear }}">
                            <i class="fa fa-file-pdf-o"></i> PDF
                        </button>
                    </form>
                    <div class="btn-group">
                        <a href="{{ route('accountant.reports.export_expenses', ['year' => $selectedYear]) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-file-excel-o"></i> Expenses
                        </a>
                        <a href="{{ route('accountant.reports.export_income', ['year' => $selectedYear]) }}" class="btn btn-outline-success btn-sm">
                            <i class="fa fa-file-excel-o"></i> Income
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Filter Section -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body py-2">
                    <form action="{{ route('accountant.reports.index') }}" method="GET" class="form-inline float-right">
                        <label class="mr-2">View Report For:</label>
                        <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>Fiscal Year {{ $year }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div class="pt-1">
                        <strong>Overview for {{ $selectedYear }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Tiles -->
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-1">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-usd"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalIncome, 0) }}</span>
                    </h4>
                    <p class="text-light">Income ({{ $selectedYear }})</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-4">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-money"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalExpenses, 0) }}</span>
                    </h4>
                    <p class="text-light">Approved Expenses ({{ $selectedYear }})</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-3">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-pie-chart"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($budgetBalance, 0) }}</span>
                    </h4>
                    <p class="text-light">Budget Balance ({{ $selectedYear }})</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-danger">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ $pendingExpensesCount }}</span>
                    </h4>
                    <p class="text-light">Pending Approvals ({{ $selectedYear }})</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ADDING DOWNLOAD PROGRESS MODAL -->
    <div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="fa fa-file-pdf-o fa-3x text-danger animate-pulse"></i>
                    </div>
                    <h5 class="mb-4" style="color: #940000; font-weight: bold;">Generating Financial Report</h5>
                    <div class="progress mb-2" style="height: 12px; border-radius: 6px;">
                        <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                            role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                    <p class="mt-3 text-muted small">Please wait, compiling financial data and generating charts...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Income vs Expenses Trends ({{ $selectedYear }})</strong>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Expense Categories ({{ $selectedYear }})</strong>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Budget vs Expense Chart -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Budget vs Actual Expenses ({{ $selectedYear }})</strong>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="budgetChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expense Details Widget Area -->
    <div class="row" id="expenseDetailsValidation" style="display:none;">
        <div class="col-lg-12">
             <div class="card">
                <div class="card-header bg-dark text-white">
                    <strong class="card-title" id="selectedCategoryTitle">Expense Details</strong>
                    <button type="button" class="close text-white" onclick="document.getElementById('expenseDetailsValidation').style.display='none'">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                         <div class="col-md-4">
                             <div class="card bg-light">
                                 <div class="card-body">
                                     <small class="text-muted text-uppercase font-weight-bold">Allocated Budget</small>
                                     <h3 class="mb-0 text-primary" id="detailAllocated">0</h3>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4">
                             <div class="card bg-light">
                                 <div class="card-body">
                                     <small class="text-muted text-uppercase font-weight-bold">Total Spent</small>
                                     <h3 class="mb-0 text-danger" id="detailSpent">0</h3>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4">
                             <div class="card bg-light">
                                 <div class="card-body">
                                     <small class="text-muted text-uppercase font-weight-bold">Utilization</small>
                                     <h3 class="mb-0" id="detailPercentage">0%</h3>
                                     <div class="progress mt-2" style="height: 5px;">
                                         <div class="progress-bar" role="progressbar" style="width: 0%;" id="detailProgressBar"></div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                    </div>
                    
                    <h5 class="mb-3">Transaction History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                <!-- filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

<style>
    .bg-flat-color-1 { background: #20a8d8; }
    .bg-flat-color-3 { background: #ffc107; }
    .bg-flat-color-4 { background: #e83e8c; }
    .card-body h4 { font-size: 1.5rem; font-weight: 700; }
    #expenseDetailsValidation { transition: all 0.3s ease; }
    
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: white; }
    .btn-primary-custom:hover { background-color: #7a0000; border-color: #7a0000; color: white; }
    .btn-danger-custom { background-color: #d9534f; border-color: #d9534f; color: white; }
    .bg-primary-custom { background-color: #940000 !important; color: white; }
    
    .animate-pulse { animation: pulse 1.5s infinite; }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedYear = "{{ $selectedYear }}";
    let expenseDetailsData = {};

    // Fetch Chart Data
    fetch(`{{ route('accountant.reports.chart_data') }}?year=${selectedYear}`)
        .then(response => response.json())
        .then(data => {
            console.log('Report data received:', data);
            expenseDetailsData = data.budget_comparison.details;
            
            renderTrendsChart(data.trends);
            renderCategoryChart(data.categories);
            renderBudgetChart(data.budget_comparison);
        })
        .catch(error => {
            console.error('Error fetching report data:', error);
        });

    function renderTrendsChart(trends) {
        const ctx = document.getElementById('trendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trends.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: trends.income,
                        backgroundColor: 'rgba(32, 168, 216, 0.7)',
                        borderColor: '#20a8d8',
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: trends.expenses,
                        backgroundColor: 'rgba(232, 62, 140, 0.7)',
                        borderColor: '#e83e8c',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCategoryChart(categories) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categories.labels,
                datasets: [{
                    data: categories.data,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#C9CBCF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function renderBudgetChart(budgetData) {
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const budgetChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: budgetData.labels,
                datasets: [
                    {
                        label: 'Allocated Budget',
                        data: budgetData.allocated,
                        backgroundColor: 'rgba(32, 168, 216, 0.6)',
                        borderColor: 'rgba(32, 168, 216, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Actual Expenses',
                        data: budgetData.spent,
                        backgroundColor: function(context) {
                            const label = budgetData.labels[context.dataIndex];
                            const detail = budgetData.details[label];
                            return (detail && detail.is_over) ? 'rgba(220, 53, 69, 0.7)' : 'rgba(232, 62, 140, 0.7)';
                        },
                        borderColor: function(context) {
                            const label = budgetData.labels[context.dataIndex];
                            const detail = budgetData.details[label];
                            return (detail && detail.is_over) ? '#dc3545' : '#e83e8c';
                        },
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                if (context.dataset.label === 'Actual Expenses') {
                                    const label = budgetData.labels[context.dataIndex];
                                    const detail = budgetData.details[label];
                                    if (detail && detail.is_over && detail.overage > 0) {
                                        return '⚠️ Over Budget by ' + detail.overage.toLocaleString() + ' TZS';
                                    }
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                onClick: (e) => {
                    const points = budgetChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                    if (points.length) {
                        const firstPoint = points[0];
                        const label = budgetChart.data.labels[firstPoint.index];
                        showExpenseDetails(label);
                    }
                }
            }
        });
    }

    function showExpenseDetails(categoryName) {
        const details = expenseDetailsData[categoryName];
        if (!details) return;

        // Populate Widget
        document.getElementById('selectedCategoryTitle').innerText = categoryName + ' - Details';
        document.getElementById('detailAllocated').innerText = parseFloat(details.allocated).toLocaleString();
        document.getElementById('detailSpent').innerText = parseFloat(details.spent).toLocaleString();
        document.getElementById('detailPercentage').innerText = details.percentage + '%';
        
        // Progress Bar
        const pBar = document.getElementById('detailProgressBar');
        pBar.style.width = Math.min(details.percentage, 100) + '%';
        pBar.className = 'progress-bar bg-' + (details.percentage > 100 ? 'danger' : (details.percentage > 90 ? 'warning' : 'success'));
        
        // Table Items
        const tbody = document.getElementById('detailTableBody');
        tbody.innerHTML = '';
        
        if (details.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No expenses recorded for this category yet.</td></tr>';
        } else {
            details.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.date}</td>
                    <td><small>${item.voucher_number}</small></td>
                    <td>${item.description ? item.description : '-'}</td>
                    <td class="text-right">${parseFloat(item.amount).toLocaleString()}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Show Section
        document.getElementById('expenseDetailsValidation').style.display = 'block';
        document.getElementById('expenseDetailsValidation').scrollIntoView({ behavior: 'smooth' });
    }
    // AJAX PDF Download Logic
    document.querySelectorAll('.download-pdf-ajax').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const baseFileName = this.getAttribute('data-filename') || 'Report';
            
            // Show progress modal
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
                    console.error('PDF Export Error:', error);
                });
        });
    });
});
</script>
@endsection
