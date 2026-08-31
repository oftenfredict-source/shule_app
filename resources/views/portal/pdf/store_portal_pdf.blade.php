<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Store Portal, {{ ucfirst($section ?? 'dashboard') }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.35; }
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
        .summary-grid td { width: 16.66%; border: 1px solid #eee; padding: 8px; text-align: center; }
        .summary-val { font-size: 14px; font-weight: bold; color: #333; }
        .summary-label { font-size: 8px; color: #777; text-transform: uppercase; }
        .section-head { font-size: 11px; font-weight: bold; color: #940000; margin: 12px 0 6px; border-bottom: 1px solid #f1f1f1; padding-bottom: 3px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #777; border-top: 1px solid #eee; padding-top: 4px; }
        .watermark { position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.05; transform: rotate(-45deg); font-size: 90px; font-weight: bold; color: #000; z-index: 1000; text-transform: uppercase; }
        .empty-note { color: #888; font-style: italic; padding: 8px 0; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
        <div class="report-title">My Store Portal, {{ ucfirst(str_replace('_', ' ', $section ?? 'dashboard')) }}</div>
    </div>

    <table class="meta-table">
        <tr><td class="meta-label">Requester</td><td>{{ $requester['name'] ?? '-' }} ({{ ucfirst($requester['type'] ?? $portalRole ?? '') }})</td></tr>
        <tr><td class="meta-label">Generated</td><td>{{ $generated_at ?? now()->format('d M Y, H:i') }}</td></tr>
    </table>

    @php $dash = $portalDashboard ?? []; @endphp

    @if(($section ?? 'dashboard') === 'dashboard' || ($section ?? '') === 'all')
        <div class="section-head">Dashboard Summary</div>
        <table class="summary-grid">
            <tr>
                <td><div class="summary-val">{{ $dash['total_requests'] ?? 0 }}</div><div class="summary-label">Total Requests</div></td>
                <td><div class="summary-val">{{ $dash['pending_requests'] ?? 0 }}</div><div class="summary-label">Pending</div></td>
                <td><div class="summary-val">{{ $dash['active_items'] ?? 0 }}</div><div class="summary-label">Active Items</div></td>
                <td><div class="summary-val">{{ $dash['overdue_items'] ?? 0 }}</div><div class="summary-label">Overdue</div></td>
                <td><div class="summary-val">{{ $dash['returned_items'] ?? 0 }}</div><div class="summary-label">Returned</div></td>
                <td><div class="summary-val">{{ $dash['open_incidents'] ?? 0 }}</div><div class="summary-label">Open Incidents</div></td>
            </tr>
        </table>
    @endif

    @if(in_array($section ?? '', ['requests', 'dashboard', 'all']))
        <div class="section-head">Pending Requests</div>
        @if(($portalPendingRequests ?? collect())->count())
            <table class="data-table">
                <thead><tr><th>#</th><th>Items</th><th>Expected Return</th><th>Requested</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach($portalPendingRequests as $i => $req)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $req['items_summary'] }}</td>
                        <td>{{ $req['expected_return_date'] ?? '-' }}</td>
                        <td>{{ $req['requested_at'] }}</td>
                        <td>{{ $req['notes'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-note">No pending requests.</div>
        @endif

        <div class="section-head">Request History</div>
        @if(($portalRequestHistory ?? collect())->count())
            <table class="data-table">
                <thead><tr><th>#</th><th>Status</th><th>Items</th><th>Requested</th><th>Reviewed</th></tr></thead>
                <tbody>
                    @foreach($portalRequestHistory as $i => $req)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ ucfirst($req['status']) }}</td>
                        <td>{{ $req['items_summary'] }}</td>
                        <td>{{ $req['requested_at'] }}</td>
                        <td>{{ $req['reviewed_at'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-note">No request history.</div>
        @endif
    @endif

    @if(in_array($section ?? '', ['items', 'dashboard', 'all']))
        <div class="section-head">My Issued Items</div>
        @if(($portalRequisitions ?? collect())->count())
            <table class="data-table">
                <thead><tr><th>#</th><th>Item</th><th>Label</th><th>Taken</th><th>Return Status</th><th>Expected Return</th><th>Issued</th></tr></thead>
                <tbody>
                    @foreach($portalRequisitions as $i => $req)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $req['item_name'] }}</td>
                        <td>{{ $req['label'] }}</td>
                        <td>{{ $req['take_display'] }}</td>
                        <td>{{ strip_tags($req['return_status_html'] ?? '') }}</td>
                        <td>{{ $req['expected_return_date'] ?? '-' }}</td>
                        <td>{{ $req['issued_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-note">No items issued yet.</div>
        @endif
    @endif

    @if(in_array($section ?? '', ['incidents', 'dashboard', 'all']))
        <div class="section-head">Incidents (Damaged / Lost)</div>
        @if(($portalIncidents ?? collect())->count())
            <table class="data-table">
                <thead><tr><th>#</th><th>Type</th><th>Item</th><th>Label</th><th>Reason</th><th>Status</th><th>Recorded</th></tr></thead>
                <tbody>
                    @foreach($portalIncidents as $i => $inc)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ ucfirst($inc['incident_type']) }}</td>
                        <td>{{ $inc['item_name'] }}</td>
                        <td>{{ $inc['label'] }}</td>
                        <td>{{ $inc['reason'] ?? '-' }}</td>
                        <td>{{ strip_tags($inc['resolve_status_html'] ?? '') }}</td>
                        <td>{{ $inc['recorded_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-note">No incidents recorded.</div>
        @endif
    @endif

    <div class="footer">Generated by ShuleXpert powered by EmCa Techonologies</div>
</body>
</html>
