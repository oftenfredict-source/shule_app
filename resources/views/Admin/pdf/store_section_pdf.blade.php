<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; line-height: 1.35; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #940000; padding-bottom: 8px; }
        .school-name { font-size: 16px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 8px; background: #f8f9fa; padding: 5px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta-table td { padding: 4px 6px; border-bottom: 1px solid #eee; }
        .meta-label { font-weight: bold; color: #555; width: 28%; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; vertical-align: top; }
        table.data-table th { background: #f8f9fa; color: #940000; font-weight: bold; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary-grid td { width: 25%; border: 1px solid #eee; padding: 8px; text-align: center; }
        .summary-val { font-size: 14px; font-weight: bold; color: #333; }
        .summary-label { font-size: 8px; color: #777; text-transform: uppercase; }
        .section-head { font-size: 11px; font-weight: bold; color: #940000; margin: 12px 0 6px; border-bottom: 1px solid #f1f1f1; padding-bottom: 3px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #777; border-top: 1px solid #eee; padding-top: 4px; }
        .watermark {
            position: fixed;
            top: 45%;
            left: 5%;
            width: 100%;
            text-align: center;
            opacity: 0.05;
            transform: rotate(-45deg);
            font-size: 90px;
            font-weight: bold;
            color: #000;
            z-index: 1000;
            text-transform: uppercase;
        }
        .empty-note { color: #888; font-style: italic; padding: 8px 0; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>
    @php
        if (!function_exists('getSmartBase64')) {
            function getSmartBase64($path) {
                if (!$path) return null;

                $path = ltrim($path, '/');
                $base = base_path();
                $parent = dirname($base);

                $possibilities = [
                    public_path($path),
                    public_path('uploads/' . $path),
                    $parent . '/public_html/' . $path,
                    $parent . '/public_html/uploads/' . $path,
                ];

                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                    $possibilities[] = $docRoot . '/' . $path;
                    $possibilities[] = $docRoot . '/uploads/' . $path;
                }

                $possibilities[] = base_path($path);
                $possibilities[] = base_path('public/' . $path);
                $possibilities[] = base_path('../public_html/' . $path);

                foreach ($possibilities as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                            $mime = match ($ext) {
                                'jpg', 'jpeg' => 'jpeg',
                                'gif' => 'gif',
                                'webp' => 'webp',
                                'svg' => 'svg+xml',
                                default => 'png',
                            };
                            return 'data:image/' . $mime . ';base64,' . $data;
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
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 60px; margin-bottom: 5px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        @if(!empty($school->address))
            <div style="font-size:9px;color:#666;">{{ $school->address }}</div>
        @endif
        <div class="report-title">{{ $title }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Generated</td>
            <td>{{ $generated_at }}</td>
            <td class="meta-label">Section</td>
            <td>{{ $section_label }}</td>
        </tr>
        @if(!empty($filter_label))
        <tr>
            <td class="meta-label">Filter</td>
            <td colspan="3">{{ $filter_label }}</td>
        </tr>
        @endif
        @if(!empty($period_label))
        <tr>
            <td class="meta-label">Period</td>
            <td colspan="3">{{ $period_label }}</td>
        </tr>
        @endif
    </table>

    @if(!empty($summary_cards))
        <table class="summary-grid">
            <tr>
                @foreach($summary_cards as $card)
                    <td>
                        <div class="summary-val">{{ $card['value'] }}</div>
                        <div class="summary-label">{{ $card['label'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if($section === 'dashboard')
        @if(!empty($dashboard['inventory_detail']))
            <div class="section-head">Inventory by Item — Package &amp; Stock Status</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Package</th>
                        <th>Sub-Pack?</th>
                        <th>Structure</th>
                        <th>In Store</th>
                        <th>Issued</th>
                        <th>Partial</th>
                        <th>Items Left</th>
                        <th>Items Taken</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dashboard['inventory_detail'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $row['item_name'] ?? '—' }}</strong></td>
                            <td>{{ $row['category_name'] ?? '—' }}</td>
                            <td>{{ $row['store_type'] ?? '—' }}</td>
                            <td>{{ $row['package_display'] ?? '—' }}</td>
                            <td>{{ $row['has_sub_package'] ?? '—' }}</td>
                            <td style="font-size:9px;">{{ $row['package_structure'] ?? '—' }}</td>
                            <td>{{ $row['labels_in_store'] ?? 0 }}</td>
                            <td>{{ $row['labels_issued'] ?? 0 }}</td>
                            <td>{{ $row['labels_partial'] ?? 0 }}</td>
                            <td>{{ $row['items_in_store'] ?? '0' }}</td>
                            <td>{{ $row['items_taken'] ?? '0' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($dashboard['recent_requisitions']))
            <div class="section-head">Recent Requisitions</div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Item</th><th>Label</th><th>Taken</th><th>Recipient</th><th>Issued</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($dashboard['recent_requisitions'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['item_name'] ?? '—' }}</td>
                            <td>{{ $row['label'] ?? '—' }}</td>
                            <td>{{ $row['take_display'] ?? '—' }}</td>
                            <td>{{ $row['recipient_name'] ?? '—' }}</td>
                            <td>{{ $row['issued_at'] ?? '—' }}</td>
                            <td>{{ ucfirst($row['status'] ?? '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($dashboard['overdue_list']))
            <div class="section-head">Overdue Returns</div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Item</th><th>Recipient</th><th>Expected</th><th>Days Overdue</th></tr></thead>
                <tbody>
                    @foreach($dashboard['overdue_list'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['item_name'] ?? '—' }}</td>
                            <td>{{ $row['recipient_name'] ?? '—' }}</td>
                            <td>{{ $row['expected_return_date'] ?? '—' }}</td>
                            <td>{{ $row['days_overdue'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($dashboard['category_breakdown']))
            <div class="section-head">Items by Category</div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Category / Item</th><th>Count</th></tr></thead>
                <tbody>
                    @foreach($dashboard['category_breakdown'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['category_name'] ?? '—' }}</td>
                            <td>{{ $row['item_count'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @elseif($section === 'report' && ($report_type ?? '') === 'all_inventory')
        @if(!empty($report_items))
            <table class="data-table">
                <thead><tr><th>Item</th><th>Package / Unit</th><th>Quantity</th></tr></thead>
                <tbody>
                    @foreach($report_items as $item)
                        @foreach($item['rows'] as $ri => $row)
                            <tr>
                                @if($ri === 0)
                                    <td rowspan="{{ $item['rowspan'] }}">{{ $item['item_name'] }}</td>
                                @endif
                                <td>{{ $row['row_type'] === 'unit' ? ($row['unit_name'] ?? '—') : ($row['package_display'] ?? '—') }}</td>
                                <td>{{ $row['row_type'] === 'unit' ? ($row['quantity'] ?? '—') : ($row['package_quantity'] ?? '—') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-note">No inventory data for selected filters.</div>
        @endif
    @elseif($section === 'report' && ($report_type ?? '') === 'specific_item')
        @if(!empty($report_item))
            <table class="meta-table">
                <tr><td class="meta-label">Item</td><td>{{ $report_item['item_name'] ?? '—' }}</td></tr>
                <tr><td class="meta-label">Category</td><td>{{ $report_item['category_name'] ?? '—' }}</td></tr>
                <tr><td class="meta-label">Measurement</td><td>{{ $report_item['measurement_type'] ?? '—' }}</td></tr>
            </table>
        @endif

        @if(!empty($stock_breakdown))
            <div class="section-head">Current Stock Breakdown</div>
            <table class="data-table">
                <thead><tr><th>Package / Unit</th><th>Labels</th><th>Remaining Items</th></tr></thead>
                <tbody>
                    @foreach($stock_breakdown as $row)
                        <tr>
                            <td>{{ $row['display'] ?? '—' }}</td>
                            <td>{{ $row['labels'] ?? 0 }}</td>
                            <td>{{ $row['remaining_items'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($report_rows))
            <div class="section-head">Issues in Period</div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Recipient</th><th>Take</th><th>Issued</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($report_rows as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['recipient_name'] ?? '—' }}</td>
                            <td>{{ $row['take_display'] ?? '—' }}</td>
                            <td>{{ $row['issued_at'] ?? '—' }}</td>
                            <td>{{ $row['status_label'] ?? ($row['status'] ?? '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($incident_rows))
            <div class="section-head">Incidents in Period</div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Type</th><th>Responsible</th><th>Take</th><th>Reason</th></tr></thead>
                <tbody>
                    @foreach($incident_rows as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ ucfirst($row['incident_type'] ?? '—') }}</td>
                            <td>{{ $row['responsible_name'] ?? '—' }}</td>
                            <td>{{ $row['take_display'] ?? '—' }}</td>
                            <td>{{ $row['reason'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @else
        @if(!empty($table_rows))
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach($table_headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($table_rows as $i => $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{!! $cell !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-note">No records found for this section.</div>
        @endif
    @endif

    <div class="footer">
        Generated by ShuleXpert (school management system) powered by EmCa Techonologies &copy; {{ date('Y') }}
    </div>
</body>
</html>
