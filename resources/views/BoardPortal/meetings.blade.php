@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-calendar"></i> Meetings</h2>
            @if(!empty($caps['can_manage_meetings']))
                <button type="button" class="bp-btn" id="toggleCreateMeeting"><i class="fa fa-plus"></i> Schedule meeting</button>
            @endif
        </div>

        @if(!empty($caps['can_manage_meetings']))
            <div class="bp-create-panel" id="createMeetingPanel">
                <form id="createMeetingForm">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Title *</label>
                            <input class="bp-input" type="text" name="title" required>
                        </div>
                        <div>
                            <label class="bp-label">Type *</label>
                            <select class="bp-select" name="meeting_type" id="meetingType" required>
                                <option value="Full Board">Full Board</option>
                                <option value="Committee">Committee</option>
                            </select>
                        </div>
                        <div id="committeeWrap" style="display:none;">
                            <label class="bp-label">Committee</label>
                            <select class="bp-select" name="committeeID">
                                <option value="">— Select —</option>
                                @foreach($committees as $c)
                                    <option value="{{ $c->committeeID }}">{{ $c->committee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="bp-label">Date *</label>
                            <input class="bp-input" type="date" name="meeting_date" required>
                        </div>
                        <div>
                            <label class="bp-label">Start time</label>
                            <input class="bp-input" type="time" name="start_time">
                        </div>
                        <div>
                            <label class="bp-label">End time</label>
                            <input class="bp-input" type="time" name="end_time">
                        </div>
                        <div>
                            <label class="bp-label">Venue</label>
                            <input class="bp-input" type="text" name="venue">
                        </div>
                        <div>
                            <label class="bp-label">Chair</label>
                            <select class="bp-select" name="chair_memberID">
                                <option value="{{ $member->memberID }}">{{ $member->full_name }} (me)</option>
                                @foreach($boardMembers as $m)
                                    @if((int)$m->memberID !== (int)$member->memberID)
                                        <option value="{{ $m->memberID }}">{{ $m->full_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="full">
                            <label class="bp-label">Agenda</label>
                            <textarea class="bp-textarea" name="agenda"></textarea>
                        </div>
                        <div class="full">
                            <label class="bp-label">Invite attendees</label>
                            <div class="bp-check-list">
                                @foreach($boardMembers as $m)
                                    <label>
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $m->memberID }}" @checked((int)$m->memberID === (int)$member->memberID)>
                                        {{ $m->full_name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-save"></i> Save meeting</button>
                    </div>
                </form>
            </div>
        @endif

        @if($openMeeting)
            <div class="bp-message-detail" id="meetingDetail" style="border:1px solid var(--bp-line);padding:1rem;margin-bottom:1.1rem;background:#faf9f7;scroll-margin-top:90px;">
                <div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <h3 style="margin:0 0 0.35rem;font-size:1.05rem;">{{ $openMeeting->title }}</h3>
                        <div class="bp-muted" style="font-size:0.82rem;">
                            {{ $openMeeting->meeting_date?->format('d M Y') }}
                            @if($openMeeting->start_time) · {{ substr($openMeeting->start_time, 0, 5) }} @endif
                            · {{ $openMeeting->meeting_type ?: 'Meeting' }}
                            @if($openMeeting->venue) · {{ $openMeeting->venue }} @endif
                            · <span class="bp-badge">{{ $openMeeting->status }}</span>
                        </div>
                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">
                            Chair: {{ $openMeeting->chair->full_name ?? '—' }}
                            @if(!empty($isAttendeeOfOpen))
                                · <span class="bp-badge">You were listed</span>
                            @else
                                · <span class="bp-badge" style="background:#6a6a6a;color:#fff;">You were absent / not invited</span>
                            @endif
                        </div>
                    </div>
                    <a class="bp-btn" href="{{ route('board.portal.meetings') }}">Close</a>
                </div>

                @if($openMeeting->agenda)
                    <div style="margin-top:0.85rem;">
                        <div class="bp-label">Agenda</div>
                        <div style="white-space:pre-wrap;line-height:1.5;">{{ $openMeeting->agenda }}</div>
                    </div>
                @endif

                <div style="margin-top:1.1rem;border-top:1px solid var(--bp-line);padding-top:0.9rem;">
                    <div class="bp-card-head" style="margin-bottom:0.65rem;">
                        <h2 style="font-size:1rem;margin:0;"><i class="fa fa-comments"></i> Comments ({{ $meetingComments->count() }})</h2>
                    </div>
                    <p class="bp-muted" style="font-size:0.82rem;margin:0 0 0.75rem;">
                        Members can comment even if they did not attend the sitting.
                    </p>

                    @if($meetingComments->isEmpty())
                        <div class="bp-empty" style="padding:0.75rem 0;">No comments yet — be the first.</div>
                    @else
                        @foreach($meetingComments as $c)
                            <div style="padding:0.7rem 0.8rem;margin-bottom:0.5rem;background:#fff;border:1px solid var(--bp-line);">
                                <div class="bp-muted" style="font-size:0.78rem;margin-bottom:0.35rem;">
                                    <strong style="color:var(--bp-ink);">{{ $c->member->full_name ?? 'Member' }}</strong>
                                    @if($c->member && $c->member->position)
                                        · {{ $c->member->position->position_name }}
                                    @endif
                                    · {{ $c->created_at?->format('d M Y H:i') }}
                                    @if($c->was_absent)
                                        · <span class="bp-badge">Commented while absent</span>
                                    @endif
                                </div>
                                <div style="white-space:pre-wrap;line-height:1.5;">{{ $c->body }}</div>
                            </div>
                        @endforeach
                    @endif

                    @if(!empty($caps['can_comment_meetings']))
                        <form id="meetingCommentForm" style="margin-top:0.85rem;">
                            <input type="hidden" name="meetingID" value="{{ $openMeeting->meetingID }}">
                            <label class="bp-label">Add your comment</label>
                            <textarea class="bp-textarea" name="body" id="meetingCommentBody" required maxlength="5000" placeholder="Share your input on this meeting…" style="min-height:90px;"></textarea>
                            <div style="margin-top:0.65rem;display:flex;flex-wrap:wrap;gap:0.55rem;align-items:center;">
                                <button type="submit" class="bp-btn" id="meetingCommentBtn">
                                    <i class="fa fa-comment"></i> Post comment
                                </button>
                                <span class="bp-muted" style="font-size:0.82rem;" id="meetingCommentStatus"></span>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        @if($meetings->isEmpty())
            <div class="bp-empty">No meetings found.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Venue</th>
                            <th>Chair</th>
                            <th>Status</th>
                            <th>Comments</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meetings as $meeting)
                            @php $openUrl = route('board.portal.meetings', ['id' => $meeting->meetingID]); @endphp
                            <tr class="bp-msg-row" data-href="{{ $openUrl }}" style="cursor:pointer;">
                                <td>
                                    <a class="bp-msg-link" href="{{ $openUrl }}" style="color:var(--bp-red);font-weight:700;text-decoration:underline;">
                                        {{ $meeting->title }}
                                    </a>
                                    @if($meeting->agenda)
                                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">{{ \Illuminate\Support\Str::limit(strip_tags($meeting->agenda), 100) }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $meeting->meeting_date?->format('d M Y') }}
                                    @if($meeting->start_time)<br><span class="bp-muted">{{ substr($meeting->start_time, 0, 5) }}</span>@endif
                                </td>
                                <td>{{ $meeting->meeting_type ?: '—' }}</td>
                                <td>{{ $meeting->venue ?: '—' }}</td>
                                <td>{{ $meeting->chair->full_name ?? '—' }}</td>
                                <td><span class="bp-badge">{{ $meeting->status }}</span></td>
                                <td>{{ $meeting->comments_count ?? 0 }}</td>
                                <td>
                                    <a class="bp-btn bp-btn-sm" href="{{ $openUrl }}"><i class="fa fa-comments"></i> Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<style>
    .bp-btn-sm {
        padding: 0.35rem 0.65rem;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .bp-msg-row:hover { background: #f7f3ef !important; }
</style>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toggle = document.getElementById('toggleCreateMeeting');
    const panel = document.getElementById('createMeetingPanel');
    const typeEl = document.getElementById('meetingType');
    const committeeWrap = document.getElementById('committeeWrap');
    const detail = document.getElementById('meetingDetail');

    if (detail) detail.scrollIntoView({ behavior: 'smooth', block: 'start' });

    document.querySelectorAll('.bp-msg-row[data-href]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            var href = row.getAttribute('data-href');
            if (href) location.href = href;
        });
    });

    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('create') === '1') panel.classList.add('is-open');
    if (typeEl && committeeWrap) {
        typeEl.addEventListener('change', function () {
            committeeWrap.style.display = typeEl.value === 'Committee' ? '' : 'none';
        });
    }

    const form = document.getElementById('createMeetingForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(`{{ route('board.portal.meetings.store') }}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            }).then(r => r.json()).then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                location.reload();
            }).catch(err => alert(err.message || 'Failed'));
        });
    }

    const commentForm = document.getElementById('meetingCommentForm');
    const commentBtn = document.getElementById('meetingCommentBtn');
    const commentStatus = document.getElementById('meetingCommentStatus');
    if (commentForm) {
        commentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (commentBtn) commentBtn.disabled = true;
            if (commentStatus) commentStatus.textContent = 'Posting…';
            fetch(`{{ route('board.portal.meetings.comment') }}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(commentForm)
            }).then(r => r.json()).then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                location.href = `{{ route('board.portal.meetings') }}?id=` + (data.meetingID || '');
            }).catch(err => {
                if (commentStatus) commentStatus.textContent = '';
                if (commentBtn) commentBtn.disabled = false;
                alert(err.message || 'Failed to post comment');
            });
        });
    }
})();
</script>
@endsection
