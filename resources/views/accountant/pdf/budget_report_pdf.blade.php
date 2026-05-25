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
        .info-table td { padding: 4px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 20%; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; text-transform: uppercase; font-size: 10px; }

        .total-row { background-color: #f8f9fa; font-weight: bold; font-size: 11px; }
        .progress-container { background: #eee; width: 100%; height: 8px; border-radius: 4px; margin-top: 4px; }
        .progress-bar { height: 100%; border-radius: 4px; }
        .progress-safe { background: #28a745; }
        .progress-over { background: #dc3545; }

        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
        .watermark { position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.03; transform: rotate(-45deg); font-size: 100px; font-weight: bold; z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">SHULEXPERT</div>

    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">SCHOOL BUDGET ALLOCATION REPORT - {{ $year }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $year }}</td>
                <td class="label">Date Generated:</td>
                <td>{{ date('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Total Budget:</td>
                <td style="font-weight: bold;">{{ number_format($total_allocated) }} TZS</td>
                <td class="label">Total Spent:</td>
                <td style="font-weight: bold; color: {{ $total_spent > $total_allocated ? '#dc3545' : '#28a745' }}">
                    {{ number_format($total_spent) }} TZS
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Budget Category</th>
                <th style="text-align: right; width: 80px;">Allocated (TZS)</th>
                <th style="text-align: right; width: 80px;">Actual Spent (TZS)</th>
                <th style="text-align: right; width: 80px;">Remaining (TZS)</th>
                <th style="width: 100px;">Utilization</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgets as $budget)
                @php
                    $is_over = $budget->spent > $budget->allocated_amount;
                    $percent = $budget->allocated_amount > 0 ? min(100, round(($budget->spent / $budget->allocated_amount) * 100)) : 0;
                    $variance = $budget->allocated_amount - $budget->spent;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: bold;">{{ $budget->budget_category }}</div>
                        <div style="font-size: 8px; color: #777;">Year: {{ $budget->fiscal_year }}</div>
                    </td>
                    <td style="text-align: right;">{{ number_format($budget->allocated_amount) }}</td>
                    <td style="text-align: right;">{{ number_format($budget->spent) }}</td>
                    <td style="text-align: right; color: {{ $variance < 0 ? '#dc3545' : '#28a745' }}">
                        {{ number_format($variance) }}
                    </td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar {{ $is_over ? 'progress-over' : 'progress-safe' }}" style="width: {{ $percent }}%;"></div>
                        </div>
                        <div style="text-align: center; font-size: 8px; margin-top: 2px;">{{ $percent }}% Utilized</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td style="text-align: right;">GRAND TOTAL:</td>
                <td style="text-align: right;">{{ number_format($total_allocated) }}</td>
                <td style="text-align: right;">{{ number_format($total_spent) }}</td>
                <td style="text-align: right; color: {{ $total_allocated - $total_spent < 0 ? '#dc3545' : '#28a745' }}">
                    {{ number_format($total_allocated - $total_spent) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated by ShuleXpert powered by EmCa Techonology &copy; {{ date('Y') }} | Confidential Financial Document
    </div>
</body>
</html>
