@extends('BoardPortal.layout')

@section('content')
    @php
        $canCreateAnything = !empty($caps['can_manage_meetings'])
            || !empty($caps['can_manage_committees'])
            || !empty($caps['can_manage_minutes'])
            || !empty($caps['can_manage_resolutions'])
            || !empty($caps['can_manage_action_items'])
            || !empty($caps['can_manage_documents']);
    @endphp

    @if($canCreateAnything)
        <div class="bp-card" style="margin-bottom:1.25rem;">
            <div class="bp-card-head">
                <h2><i class="fa fa-briefcase"></i> Officer tools</h2>
            </div>
            <p class="bp-muted" style="margin:0 0 0.85rem;font-size:0.9rem;">
                Create and keep board records from the portal — meetings, committees, minutes, and more.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:0.55rem;">
                @if(!empty($caps['can_manage_meetings']))
                    <a class="bp-btn" href="{{ route('board.portal.meetings', ['create' => 1]) }}"><i class="fa fa-plus"></i> Schedule meeting</a>
                @endif
                @if(!empty($caps['can_manage_committees']))
                    <a class="bp-btn" href="{{ route('board.portal.committees', ['create' => 1]) }}"><i class="fa fa-sitemap"></i> New committee</a>
                @endif
                @if(!empty($caps['can_manage_minutes']))
                    <a class="bp-btn" href="{{ route('board.portal.minutes', ['create' => 1]) }}"><i class="fa fa-file-text-o"></i> Record minutes</a>
                @endif
                @if(!empty($caps['can_manage_resolutions']))
                    <a class="bp-btn" href="{{ route('board.portal.resolutions', ['create' => 1]) }}"><i class="fa fa-gavel"></i> Add resolution</a>
                @endif
                @if(!empty($caps['can_manage_action_items']))
                    <a class="bp-btn" href="{{ route('board.portal.action_items', ['create' => 1]) }}"><i class="fa fa-check-square-o"></i> New action</a>
                @endif
                @if(!empty($caps['can_manage_documents']))
                    <a class="bp-btn" href="{{ route('board.portal.documents', ['create' => 1]) }}"><i class="fa fa-upload"></i> Upload document</a>
                @endif
                @if(!empty($caps['can_view_members']))
                    <a class="bp-btn" href="{{ route('board.portal.members') }}"><i class="fa fa-users"></i> View members</a>
                @endif
                @if(!empty($caps['can_compose_messages']))
                    <a class="bp-btn" href="{{ route('board.portal.messages', ['compose' => 1]) }}"><i class="fa fa-envelope"></i> Compose message</a>
                @endif
                @if(!empty($caps['can_view_messages']))
                    <a class="bp-btn" href="{{ route('board.portal.messages') }}">
                        <i class="fa fa-inbox"></i> Inbox
                        @if(($unreadMessages ?? 0) > 0)
                            <span class="bp-nav-badge">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                @endif
                @if(!empty($caps['can_message_members']))
                    <a class="bp-btn" href="{{ route('board.portal.members', ['message' => 1]) }}"><i class="fa fa-mobile"></i> Send SMS</a>
                @endif
            </div>
        </div>
    @endif

    <div class="bp-stats">
        <div class="bp-stat">
            <div class="label">Upcoming meetings</div>
            <div class="value">{{ $stats['upcoming_meetings'] }}</div>
        </div>
        <div class="bp-stat">
            <div class="label">Open actions</div>
            <div class="value">{{ $stats['open_actions'] }}</div>
        </div>
        <div class="bp-stat">
            <div class="label">Assigned to me</div>
            <div class="value">{{ $stats['my_actions'] }}</div>
        </div>
        <div class="bp-stat">
            <div class="label">My committees</div>
            <div class="value">{{ $stats['committees'] }}</div>
        </div>
    </div>

    <div class="bp-grid-2">
        <div class="bp-card">
            <h2><i class="fa fa-calendar"></i> Upcoming meetings</h2>
            @forelse($upcomingMeetings as $meeting)
                <div class="bp-list-row">
                    <strong>{{ $meeting->title }}</strong>
                    <div class="bp-muted" style="font-size:0.85rem;margin-top:4px;">
                        {{ $meeting->meeting_date?->format('d M Y') }}
                        @if($meeting->start_time) · {{ substr($meeting->start_time, 0, 5) }} @endif
                        · {{ $meeting->status }}
                        @if($meeting->venue) · {{ $meeting->venue }} @endif
                    </div>
                </div>
            @empty
                <div class="bp-empty">No upcoming meetings.</div>
            @endforelse
            <div style="margin-top:1rem;">
                <a class="bp-btn" href="{{ route('board.portal.meetings') }}">
                    <i class="fa fa-arrow-right"></i> View meetings
                </a>
            </div>
        </div>

        <div class="bp-card">
            <h2><i class="fa fa-check-square-o"></i> My action items</h2>
            @forelse($myActions as $item)
                <div class="bp-list-row">
                    <strong>{{ $item->title }}</strong>
                    <div class="bp-muted" style="font-size:0.85rem;margin-top:4px;">
                        {{ $item->status }}
                        @if($item->due_date) · Due {{ $item->due_date->format('d M Y') }} @endif
                        @if($item->priority) · {{ $item->priority }} @endif
                    </div>
                </div>
            @empty
                <div class="bp-empty">No open action items assigned to you.</div>
            @endforelse
            <div style="margin-top:1rem;">
                <a class="bp-btn" href="{{ route('board.portal.action_items') }}">
                    <i class="fa fa-arrow-right"></i> Manage actions
                </a>
            </div>
        </div>
    </div>

    @if(($caps['is_secretary'] || $caps['is_chair']) && $pendingMinutes->count())
        <div class="bp-card">
            <h2><i class="fa fa-file-text-o"></i> Minutes needing attention</h2>
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Meeting</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingMinutes as $minute)
                            <tr>
                                <td>{{ $minute->title }}</td>
                                <td>{{ $minute->meeting->title ?? '—' }}</td>
                                <td><span class="bp-badge">{{ $minute->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="bp-card">
        <h2><i class="fa fa-gavel"></i> Recent resolutions</h2>
        @if($recentResolutions->isEmpty())
            <div class="bp-empty">No resolutions to show.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentResolutions as $res)
                            <tr>
                                <td>{{ $res->resolution_number ?: '—' }}</td>
                                <td>{{ $res->title }}</td>
                                <td>{{ $res->resolution_date?->format('d M Y') ?: '—' }}</td>
                                <td><span class="bp-badge">{{ $res->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
