<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; text-transform: uppercase; }

        .info-section { margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; width: 15%; }

        .stats-grid { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .stats-grid td { width: 20%; text-align: center; padding: 8px; border: 1px solid #eee; }
        .stat-val { font-size: 14px; font-weight: bold; color: #333; }
        .stat-label { font-size: 8px; color: #777; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; table-layout: fixed; }
        table.data-table th, table.data-table td { border: 1px solid #eee; padding: 6px; text-align: left; word-wrap: break-word; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; color: #940000; font-size: 9px; text-transform: uppercase; }
        table.data-table tr { page-break-inside: avoid; page-break-after: auto; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }

        .watermark {
            position: fixed;
            top: 45%;
            left: 5%;
            width: 100%;
            text-align: center;
            opacity: 0.05;
            transform: rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: #000;
            z-index: 1000;
            text-transform: uppercase;
        }

        .status-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-available { background-color: #28a745; }
        .status-borrowed { background-color: #ffc107; color: #000; }
        .status-overdue { background-color: #dc3545; }
        .status-lost { background-color: #6f42c1; }
        .status-damaged { background-color: #fd7e14; }

        .borrower-type { font-size: 8px; color: #666; font-style: italic; }
        .expected-date { color: #d63031; font-weight: bold; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>
    
    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                $possibilities = [
                    public_path($path),
                    public_path('uploads/' . $path),
                    base_path($path),
                    base_path('public/' . $path),
                ];
                foreach ($possibilities as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            return 'data:image/' . ($ext ?: 'png') . ';base64,' . $data;
                        } catch (\Exception $e) {}
                    }
                }
                return null;
            }
        }
        $schoolLogoBase64 = getSmartBase64($school->school_logo ?? '');
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 50px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</div>
        <div class="report-title">Library Inventory & Circulation Report</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Status Filter:</td>
                <td>{{ $filterStatus ? strtoupper($filterStatus) : 'ANY STATUS' }}</td>
                <td class="label">Class Filter:</td>
                <td>{{ $filterClass }}</td>
                <td class="label">Generated:</td>
                <td>{{ $dateGenerated }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 10px; font-weight: bold; color: #940000; font-size: 11px;">SUMMARY STATISTICS</div>
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-val">{{ $stats->total ?? 0 }}</div>
                <div class="stat-label">Total Books</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats->available ?? 0 }}</div>
                <div class="stat-label">Available</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats->borrowed ?? 0 }}</div>
                <div class="stat-label">Borrowed</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats->lost ?? 0 }}</div>
                <div class="stat-label">Lost (Unresolved)</div>
            </td>
            <td>
                <div class="stat-val">{{ $stats->damaged ?? 0 }}</div>
                <div class="stat-label">Damaged (Unresolved)</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Unique ID</th>
                <th style="width: 25%;">Book Title</th>
                <th style="width: 15%;">Author</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 30%;">Current Holder / Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($books as $index => $book)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $book->isbn }}</td>
                    <td>
                        <strong>{{ $book->book_title }}</strong><br>
                        <span style="font-size: 8px; color: #777;">
                            {{ ($book->class ? $book->class->class_name : 'No Class') }} | {{ ($book->subject ? $book->subject->subject_name : 'No Subject') }}
                        </span>
                    </td>
                    <td>{{ $book->author ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        <span class="status-badge status-{{ strtolower($book->status_label) }}">
                            {{ $book->status_label }}
                        </span>
                    </td>
                    <td>
                        @if($book->status_label === 'Borrowed' || $book->status_label === 'Overdue')
                            @foreach($book->activeBorrows as $ab)
                                <div style="margin-bottom: 4px; border-bottom: 1px solid #f9f9f9; padding-bottom: 2px;">
                                    <strong>
                                        @if($ab->student)
                                            {{ trim(($ab->student->first_name ?? '') . ' ' . ($ab->student->last_name ?? '')) }}
                                            <span class="borrower-type">(Student - {{ $ab->student->admission_number ?? 'N/A' }})</span>
                                        @elseif($ab->teacher)
                                            {{ trim(($ab->teacher->first_name ?? '') . ' ' . ($ab->teacher->last_name ?? '')) }}
                                            <span class="borrower-type">(Teacher)</span>
                                        @elseif($ab->staff)
                                            {{ trim(($ab->staff->first_name ?? '') . ' ' . ($ab->staff->last_name ?? '')) }}
                                            <span class="borrower-type">(Staff)</span>
                                        @else
                                            Unknown
                                        @endif
                                    </strong>
                                    <div style="font-size: 8px;">
                                        Due: <span class="expected-date">{{ $ab->expected_return_date ? \Carbon\Carbon::parse($ab->expected_return_date)->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($book->status_label === 'Lost')
                            @php $loss = $book->activeLosses->first(); @endphp
                            @if($loss)
                                <strong>Lost By: </strong>
                                @if($loss->student)
                                    {{ trim(($loss->student->first_name ?? '') . ' ' . ($loss->student->last_name ?? '')) }} (S)
                                @elseif($loss->teacher)
                                    {{ trim(($loss->teacher->first_name ?? '') . ' ' . ($loss->teacher->last_name ?? '')) }} (T)
                                @else
                                    {{ ucfirst($loss->lost_by) }}
                                @endif
                                <div style="font-size: 8px;">Reason: {{ $loss->description ?? 'No reason provided' }}</div>
                            @endif
                        @elseif($book->status_label === 'Damaged')
                            @php $damage = $book->activeDamages->first(); @endphp
                            @if($damage)
                                <strong>Damaged By: </strong>
                                @if($damage->student)
                                    {{ trim(($damage->student->first_name ?? '') . ' ' . ($damage->student->last_name ?? '')) }} (S)
                                @elseif($damage->teacher)
                                    {{ trim(($damage->teacher->first_name ?? '') . ' ' . ($damage->teacher->last_name ?? '')) }} (T)
                                @else
                                    {{ ucfirst($damage->damaged_by) }}
                                @endif
                                <div style="font-size: 8px;">Detail: {{ $damage->description ?? 'No details' }}</div>
                            @endif
                        @else
                            <span style="color: #777; font-style: italic;">Available for borrowing</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No books found matching the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
