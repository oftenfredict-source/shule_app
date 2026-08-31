<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Black Book Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { color: #940000; font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #fff5f5; color: #940000; }
        .count { font-weight: bold; color: #940000; }
        .sub { font-size: 10px; color: #555; margin-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $school->school_name ?? 'School' }}, Black Book Report</h1>
    <div class="meta">Generated: {{ $generated_at }} | Type: {{ ucfirst(str_replace('_', ' ', $type)) }}</div>

    @if(empty($rows))
        <p>No records found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Admission No</th>
                    <th>Times Reported</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ $row['class_name'] }}</td>
                    <td>{{ $row['admission_number'] ?? '-' }}</td>
                    <td class="count">{{ $row['report_count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @foreach($rows as $row)
            @if(!empty($row['reports']))
            <div class="sub">
                <strong>{{ $row['student_name'] }}</strong>: incident history:
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Resolution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($row['reports'] as $r)
                        <tr>
                            <td>{{ $r['created_at'] }}</td>
                            <td>{{ $r['problem_category'] }} ({{ $r['problem_weight'] }}%)</td>
                            <td>{{ \Illuminate\Support\Str::limit($r['description'], 80) }}</td>
                            <td>{{ ucfirst($r['status']) }}</td>
                            <td>
                                @if($r['status'] === 'resolved')
                                    {{ $r['resolve_method'] === 'parent_call' ? 'Parent Call' : 'Punishment' }}
                                    @if($r['punishment_category']), {{ $r['punishment_category'] }} @endif
                                @else,
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach
    @endif
</body>
</html>
