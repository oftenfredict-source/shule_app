<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 25%; }

        .stats-grid { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .stats-grid td { width: 25%; text-align: center; padding: 15px; border: 1px solid #eee; background: #fcfcfc; }
        .stat-val { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 9px; color: #777; text-transform: uppercase; margin-top: 5px; }

        .section-title { font-size: 12px; font-weight: bold; color: #940000; border-bottom: 1px solid #940000; padding-bottom: 3px; margin-bottom: 15px; text-transform: uppercase; }

        .chart-container { text-align: center; margin-bottom: 30px; page-break-inside: avoid; }
        .chart-img { max-width: 100%; height: auto; border: 1px solid #eee; padding: 10px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
        .watermark { position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.03; transform: rotate(-45deg); font-size: 100px; font-weight: bold; z-index: -1; }
        
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="watermark">SHULEXPERT</div>

    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">ANNUAL FINANCIAL SUMMARY REPORT - {{ $year }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $year }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Key Performance Indicators</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ number_format($stats->income ?? 0) }} TZS</div>
                <div class="stat-label">Total Income</div>
            </td>
            <td>
                <div class="stat-val">{{ number_format($stats->expenses ?? 0) }} TZS</div>
                <div class="stat-label">Total Expenses</div>
            </td>
            <td>
                <div class="stat-val">{{ number_format($stats->budget ?? 0) }} TZS</div>
                <div class="stat-label">Total Budget</div>
            </td>
            <td>
                <div class="stat-val" style="color: {{ ($stats->balance ?? 0) >= 0 ? '#28a745' : '#dc3545' }}">
                    {{ number_format($stats->balance ?? 0) }} TZS
                </div>
                <div class="stat-label">Net Balance</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Expense Distribution by Category</div>
    <div class="chart-container">
        @php
            $catLabels = collect($categories)->pluck('expense_category')->toArray();
            $catData = collect($categories)->pluck('total')->toArray();
            $pieChartConfig = [
                'type' => 'pie',
                'data' => [
                    'labels' => $catLabels,
                    'datasets' => [[
                        'data' => $catData,
                        'backgroundColor' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6610f2', '#e83e8c', '#fd7e14']
                    ]]
                ],
                'options' => [
                    'title' => ['display' => true, 'text' => 'Expenses by Category'],
                    'plugins' => [
                        'datalabels' => ['display' => true, 'color' => '#fff']
                    ]
                ]
            ];
            $pieUrl = "http://quickchart.io/chart?w=500&h=300&c=" . urlencode(json_encode($pieChartConfig));
        @endphp
        <img src="{{ $pieUrl }}" class="chart-img">
    </div>

    <div class="section-title">Budget vs Actual Performance</div>
    <div class="chart-container">
        @php
            $budgetLabels = array_column($budgetVsActual, 'category');
            $budgetAllocated = array_column($budgetVsActual, 'allocated');
            $budgetSpent = array_column($budgetVsActual, 'spent');
            
            $barChartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $budgetLabels,
                    'datasets' => [
                        [
                            'label' => 'Allocated',
                            'backgroundColor' => 'rgba(148, 0, 0, 0.5)',
                            'data' => $budgetAllocated
                        ],
                        [
                            'label' => 'Actual Spent',
                            'backgroundColor' => 'rgba(232, 62, 140, 0.5)',
                            'data' => $budgetSpent
                        ]
                    ]
                ],
                'options' => [
                    'title' => ['display' => true, 'text' => 'Budget Utilization'],
                    'scales' => [
                        'yAxes' => [['ticks' => ['beginAtZero' => true]]]
                    ]
                ]
            ];
            $barUrl = "http://quickchart.io/chart?w=600&h=300&c=" . urlencode(json_encode($barChartConfig));
        @endphp
        <img src="{{ $barUrl }}" class="chart-img">
    </div>

    <div class="section-title">Budget Overage Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Allocated (TZS)</th>
                <th>Actual Spent (TZS)</th>
                <th>Status</th>
                <th>Variance (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgetVsActual as $item)
                <tr>
                    <td>{{ $item['category'] }}</td>
                    <td>{{ number_format($item['allocated']) }}</td>
                    <td>{{ number_format($item['spent']) }}</td>
                    <td>
                        @if($item['is_over'])
                            <span class="badge badge-danger">Over Budget</span>
                        @else
                            <span class="badge badge-success">On Track</span>
                        @endif
                    </td>
                    <td style="color: {{ $item['allocated'] - $item['spent'] < 0 ? '#dc3545' : '#28a745' }}">
                        {{ number_format($item['allocated'] - $item['spent']) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by ShuleXpert powered by EmCa Techonology &copy; {{ date('Y') }} | Confidential Financial Document
    </div>
</body>
</html>
