@php
    $user_type = $user_type ?? Session::get('user_type');
    $isDashboard = $isDashboard ?? false;
@endphp
@if($user_type == 'Admin')
    @include('includes.Admin_nav')
@elseif($user_type == 'Staff')
    @include('includes.staff_nav')
@else
    @include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .table, .form-control {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .sx-board-wrap { padding: 1rem 1.25rem 2rem; }
    .sx-board-hero {
        background: linear-gradient(135deg, #940000 0%, #6b0000 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }
    .sx-board-hero h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 0.35rem; }
    .sx-board-hero p { margin: 0; opacity: 0.9; font-size: 0.92rem; }
    .sx-board-nav { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; }
    .sx-board-nav a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 12px; border: 1px solid #e8e8e8; background: #fff;
        color: #333; text-decoration: none; font-size: 0.85rem; font-weight: 600;
    }
    .sx-board-nav a:hover { border-color: #940000; color: #940000; }
    .sx-board-nav a.active { background: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; border-radius: 0 !important; }
    .btn-primary-custom:hover { background: #b30000; border-color: #b30000; color: #fff; }
    .bg-primary-custom { background: #940000 !important; }
    .card, .btn, .form-control, .form-select { border-radius: 0 !important; }
    .member-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem; }
    .member-stat {
        background: #fff; border: 1px solid #eee; padding: 12px 16px; min-width: 120px; flex: 1;
    }
    .member-stat .label { font-size: 0.72rem; color: #777; text-transform: uppercase; font-weight: 700; }
    .member-stat .value { font-size: 1.35rem; font-weight: 800; color: #940000; }
    .member-stat .sub { font-size: 0.75rem; color: #888; margin-top: 2px; }
    .report-section { margin-bottom: 1.25rem; }
    .report-section h5 {
        color: #940000; font-weight: 800; font-size: 1rem; margin-bottom: 0.75rem;
        border-bottom: 2px solid #f0e0e0; padding-bottom: 6px;
    }
    .filter-bar {
        background: #fff; border: 1px solid #eee; padding: 12px 16px; margin-bottom: 1rem;
        display: flex; flex-wrap: wrap; gap: 10px; align-items: end;
    }
    .filter-bar label { font-size: 0.8rem; font-weight: 700; color: #555; display: block; margin-bottom: 4px; }
    .empty-note { color: #888; font-size: 0.9rem; padding: 12px 0; }
    .sx-board-wrap .table thead.table-light th {
        background: #f3f3f3;
        color: #333;
        font-weight: 700;
    }
    @media print {
        .sx-board-nav, .filter-bar, .no-print, #left-panel, #header, .navbar, aside, .sidebar { display: none !important; }
        .sx-board-wrap { padding: 0; }
        .sx-board-hero { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>

<div class="sx-board-wrap">
    <div class="sx-board-hero">
        <h1><i class="fa fa-university"></i> School Board</h1>
        <p>{{ $subtitle }}</p>
    </div>

    <div class="sx-board-nav no-print">
        @foreach($menu as $item)
            <a href="{{ route($item['route']) }}" class="{{ $section === $item['key'] ? 'active' : '' }}">
                <i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    @if(!$isDashboard)
        <form method="GET" action="{{ route('admin.school_board.reports') }}" class="filter-bar no-print">
            <div>
                <label>From</label>
                <input type="date" class="form-control form-control-sm" name="from" value="{{ $from }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" class="form-control form-control-sm" name="to" value="{{ $to }}">
            </div>
            <button type="submit" class="btn btn-primary-custom btn-sm">
                <i class="fa fa-filter"></i> Apply
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
        </form>
        <p class="meeting-meta mb-3" style="font-size:0.85rem;color:#777;">
            Period: <strong>{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</strong>
            – <strong>{{ \Carbon\Carbon::parse($to)->format('d M Y') }}</strong>
        </p>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
            <p class="mb-0" style="font-size:0.85rem;color:#777;">
                Snapshot (last 3 months) ·
                <a href="{{ route('admin.school_board.reports') }}">Open full Reports</a>
            </p>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    @endif

    <div class="member-stats">
        <div class="member-stat">
            <div class="label">Members</div>
            <div class="value">{{ $summary['members_total'] }}</div>
            <div class="sub">{{ $summary['members_active'] }} active</div>
        </div>
        <div class="member-stat">
            <div class="label">Committees</div>
            <div class="value">{{ $summary['committees_total'] }}</div>
            <div class="sub">{{ $summary['committees_active'] }} active</div>
        </div>
        <div class="member-stat">
            <div class="label">Meetings</div>
            <div class="value">{{ $summary['meetings_total'] }}</div>
            <div class="sub">{{ $summary['meetings_completed'] }} completed · {{ $summary['meetings_ongoing'] }} ongoing</div>
        </div>
        <div class="member-stat">
            <div class="label">Minutes</div>
            <div class="value">{{ $summary['minutes_total'] }}</div>
            <div class="sub">{{ $summary['minutes_approved'] }} approved · {{ $summary['minutes_draft'] }} draft</div>
        </div>
        <div class="member-stat">
            <div class="label">Resolutions</div>
            <div class="value">{{ $summary['resolutions_total'] }}</div>
            <div class="sub">{{ $summary['resolutions_adopted'] }} adopted · {{ $summary['resolutions_implemented'] }} done</div>
        </div>
        <div class="member-stat">
            <div class="label">Action Items</div>
            <div class="value">{{ $summary['actions_total'] }}</div>
            <div class="sub">{{ $summary['actions_open'] }} open · {{ $summary['actions_overdue'] }} overdue</div>
        </div>
        <div class="member-stat">
            <div class="label">Documents</div>
            <div class="value">{{ $summary['documents_total'] }}</div>
            <div class="sub">in period</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-clock-o"></i> Upcoming Meetings</h5>
                    @if($upcomingMeetings->isEmpty())
                        <div class="empty-note">No upcoming scheduled meetings.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Date</th><th>Title</th><th>Type</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingMeetings as $m)
                                        <tr>
                                            <td class="text-nowrap small">
                                                {{ $m->meeting_date->format('d M Y') }}
                                                @if($m->start_time)
                                                    <br>{{ substr((string)$m->start_time, 0, 5) }}
                                                @endif
                                            </td>
                                            <td>{{ $m->title }}</td>
                                            <td class="small">{{ $m->meeting_type }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-exclamation-triangle"></i> Overdue Action Items</h5>
                    @if($overdueActions->isEmpty())
                        <div class="empty-note">No overdue action items.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Task</th><th>Assigned</th><th>Due</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueActions as $a)
                                        <tr>
                                            <td>{{ $a->title }}</td>
                                            <td class="small">{{ optional($a->assignee)->full_name ?: '—' }}</td>
                                            <td class="text-nowrap small text-danger">{{ $a->due_date->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-sitemap"></i> Committees</h5>
                    @if($committees->isEmpty())
                        <div class="empty-note">No committees registered.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Committee</th><th>Members</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($committees as $c)
                                        <tr>
                                            <td>{{ $c->committee_name }}</td>
                                            <td>{{ $c->members_count }}</td>
                                            <td><span class="badge {{ $c->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $c->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-gavel"></i> Resolutions (period)</h5>
                    @if($resolutions->isEmpty())
                        <div class="empty-note">No resolutions in this period.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>No.</th><th>Title</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($resolutions as $r)
                                        <tr>
                                            <td class="text-nowrap small">{{ $r->resolution_number ?: '—' }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($r->title, 50) }}</td>
                                            <td><span class="badge bg-secondary">{{ $r->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-calendar"></i> Meetings (period)</h5>
                    @if($meetings->isEmpty())
                        <div class="empty-note">No meetings in this period.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Date</th><th>Title</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($meetings as $m)
                                        <tr>
                                            <td class="text-nowrap small">{{ $m->meeting_date->format('d M Y') }}</td>
                                            <td>{{ $m->title }}</td>
                                            <td>
                                                <span class="badge {{ $m->status === 'Completed' ? 'bg-success' : ($m->status === 'Ongoing' ? 'bg-info text-dark' : 'bg-primary') }}">
                                                    {{ $m->status === 'Scheduled' ? 'Upcoming' : $m->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-check-square-o"></i> Action Items (period)</h5>
                    @if($actionItems->isEmpty())
                        <div class="empty-note">No action items in this period.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Task</th><th>Priority</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($actionItems as $a)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Str::limit($a->title, 45) }}</td>
                                            <td class="small">{{ $a->priority }}</td>
                                            <td><span class="badge bg-secondary">{{ $a->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-file-text-o"></i> Minutes (period)</h5>
                    @if($minutes->isEmpty())
                        <div class="empty-note">No minutes in this period.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Title</th><th>Meeting</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($minutes as $min)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Str::limit($min->title, 40) }}</td>
                                            <td class="small">{{ optional($min->meeting)->title ?: '—' }}</td>
                                            <td><span class="badge bg-secondary">{{ $min->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 report-section">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5><i class="fa fa-folder-open-o"></i> Documents (period)</h5>
                    @if($documents->isEmpty())
                        <div class="empty-note">No documents uploaded in this period.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Title</th><th>Category</th><th>Date</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $d)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Str::limit($d->title, 40) }}</td>
                                            <td class="small">{{ $d->category }}</td>
                                            <td class="small text-nowrap">{{ $d->created_at?->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="report-section">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5><i class="fa fa-users"></i> Board Members</h5>
                @if($members->isEmpty())
                    <div class="empty-note">No board members registered.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Position</th><th>Phone</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($members->sortBy('full_name') as $mem)
                                    <tr>
                                        <td>{{ $mem->full_name }}</td>
                                        <td>{{ optional($mem->position)->position_name ?: '—' }}</td>
                                        <td>{{ $mem->phone ?: '—' }}</td>
                                        <td><span class="badge {{ $mem->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $mem->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
