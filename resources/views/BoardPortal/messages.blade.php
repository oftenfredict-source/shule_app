@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-envelope"></i> Messages</h2>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                @if(!empty($caps['can_compose_messages']))
                    <button type="button" class="bp-btn" id="toggleCompose"><i class="fa fa-pencil"></i> Compose</button>
                @endif
                <a class="bp-btn" href="{{ route('board.portal.messages', ['tab' => 'inbox']) }}" style="{{ ($tab ?? '') === 'inbox' ? '' : 'opacity:0.75;' }}">
                    Inbox @if(($unreadMessages ?? 0) > 0)<span class="bp-nav-badge">{{ $unreadMessages }}</span>@endif
                </a>
                <a class="bp-btn" href="{{ route('board.portal.messages', ['tab' => 'sent']) }}" style="{{ ($tab ?? '') === 'sent' ? '' : 'opacity:0.75;' }}">
                    Sent
                </a>
            </div>
        </div>

        <p class="bp-muted" style="margin:0 0 1rem;font-size:0.88rem;">
            These are in-app board messages (stored in the portal). SMS from Members is separate.
        </p>

        @if(!empty($caps['can_compose_messages']))
            <div class="bp-create-panel {{ request('compose') || request('to') ? 'is-open' : '' }}" id="composePanel">
                <form id="composeMessageForm">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Subject *</label>
                            <input class="bp-input" type="text" name="subject" required maxlength="200">
                        </div>
                        <div class="full">
                            <label class="bp-label">Send to *</label>
                            <select class="bp-select" name="send_to" id="msgSendTo" required>
                                <option value="all">All other active members</option>
                                <option value="selected" {{ ($preselectMemberId ?? 0) > 0 ? 'selected' : '' }}>Selected members</option>
                            </select>
                        </div>
                        <div class="full" id="msgPickWrap" style="{{ ($preselectMemberId ?? 0) > 0 ? '' : 'display:none;' }}">
                            <label class="bp-label">Recipients</label>
                            <div class="bp-check-list">
                                @foreach($boardMembers as $m)
                                    @if((int)$m->memberID !== (int)$member->memberID)
                                        <label>
                                            <input type="checkbox" name="member_ids[]" value="{{ $m->memberID }}"
                                                @checked((int)$m->memberID === (int)($preselectMemberId ?? 0))>
                                            {{ $m->full_name }}
                                            <span class="bp-muted">· {{ $m->position->position_name ?? 'Member' }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="full">
                            <label class="bp-label">Message *</label>
                            <textarea class="bp-textarea" name="body" required maxlength="10000" style="min-height:120px;"></textarea>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-paper-plane"></i> Send message</button>
                    </div>
                </form>
            </div>
        @endif

        @if($openMessage)
            <div class="bp-message-detail" style="border:1px solid var(--bp-line);padding:1rem;margin-bottom:1.1rem;background:#faf9f7;">
                <div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <h3 style="margin:0 0 0.35rem;font-size:1.05rem;">{{ $openMessage->subject }}</h3>
                        <div class="bp-muted" style="font-size:0.82rem;">
                            @if(($tab ?? '') === 'sent')
                                Sent {{ $openMessage->created_at?->format('d M Y H:i') }}
                                · {{ $openMessage->recipient_count }} recipient(s)
                            @else
                                From {{ $openMessage->sender->full_name ?? 'Board member' }}
                                @if($openMessage->sender && $openMessage->sender->position)
                                    ({{ $openMessage->sender->position->position_name }})
                                @endif
                                · {{ $openMessage->created_at?->format('d M Y H:i') }}
                            @endif
                        </div>
                    </div>
                    <a class="bp-btn" href="{{ route('board.portal.messages', ['tab' => $tab ?? 'inbox']) }}">Close</a>
                </div>
                <div style="margin-top:0.9rem;white-space:pre-wrap;line-height:1.55;">{{ $openMessage->body }}</div>

                @if(($tab ?? '') === 'sent' && $openMessage->relationLoaded('recipients'))
                    <div style="margin-top:1rem;border-top:1px solid var(--bp-line);padding-top:0.75rem;">
                        <div class="bp-label">Delivery</div>
                        <div class="bp-check-list" style="max-height:160px;">
                            @foreach($openMessage->recipients as $r)
                                <div style="padding:3px 0;font-size:0.85rem;">
                                    {{ $r->member->full_name ?? 'Member' }}
                                    — {{ $r->read_at ? 'Read ' . $r->read_at->format('d M H:i') : 'Unread' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(($threadReplies ?? collect())->isNotEmpty())
                    <div style="margin-top:1rem;border-top:1px solid var(--bp-line);padding-top:0.85rem;">
                        <div class="bp-label" style="margin-bottom:0.55rem;">Conversation</div>
                        @foreach($threadReplies as $reply)
                            <div style="padding:0.65rem 0.75rem;margin-bottom:0.45rem;background:#fff;border:1px solid var(--bp-line);">
                                <div class="bp-muted" style="font-size:0.78rem;margin-bottom:0.35rem;">
                                    <strong style="color:var(--bp-ink);">{{ $reply->sender->full_name ?? 'Member' }}</strong>
                                    · {{ $reply->created_at?->format('d M Y H:i') }}
                                    · {{ $reply->subject }}
                                </div>
                                <div style="white-space:pre-wrap;line-height:1.5;font-size:0.92rem;">{{ $reply->body }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!empty($canReply))
                    <div style="margin-top:1rem;border-top:1px solid var(--bp-line);padding-top:0.85rem;">
                        <div class="bp-label" style="margin-bottom:0.45rem;">
                            Reply to {{ $openMessage->sender->full_name ?? 'sender' }}
                        </div>
                        <form id="replyMessageForm">
                            <input type="hidden" name="messageID" value="{{ $openMessage->messageID }}">
                            <textarea class="bp-textarea" name="body" id="replyBody" required maxlength="10000" placeholder="Write your reply…" style="min-height:100px;"></textarea>
                            <div style="margin-top:0.75rem;display:flex;flex-wrap:wrap;gap:0.55rem;align-items:center;">
                                <button type="submit" class="bp-btn" id="replySendBtn">
                                    <i class="fa fa-reply"></i> Send reply
                                </button>
                                <span class="bp-muted" style="font-size:0.82rem;" id="replyStatus"></span>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        @if(($tab ?? 'inbox') === 'sent')
            @if($sent->isEmpty())
                <div class="bp-empty">No sent messages yet.</div>
            @else
                <div style="overflow-x:auto;">
                    <table class="bp-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Recipients</th>
                                <th>Sent</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sent as $msg)
                                @php $openUrl = route('board.portal.messages', ['tab' => 'sent', 'id' => $msg->messageID]); @endphp
                                <tr class="bp-msg-row" data-href="{{ $openUrl }}" style="cursor:pointer;">
                                    <td>
                                        <a class="bp-msg-link" href="{{ $openUrl }}">{{ $msg->subject }}</a>
                                    </td>
                                    <td>{{ $msg->recipients_count ?? $msg->recipient_count }}</td>
                                    <td>{{ $msg->created_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        <a class="bp-btn bp-btn-sm" href="{{ $openUrl }}"><i class="fa fa-eye"></i> View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            @if($inbox->isEmpty())
                <div class="bp-empty">No messages in your inbox yet.</div>
            @else
                <div style="overflow-x:auto;">
                    <table class="bp-table">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Received</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inbox as $row)
                                @php
                                    $msg = $row->message;
                                    $openUrl = route('board.portal.messages', ['id' => $row->messageID]);
                                @endphp
                                <tr class="bp-msg-row" data-href="{{ $openUrl }}" style="cursor:pointer;{{ $row->read_at ? '' : 'background:#fff8f5;' }}">
                                    <td>{{ $msg->sender->full_name ?? '—' }}</td>
                                    <td>
                                        <a class="bp-msg-link" href="{{ $openUrl }}" style="font-weight:{{ $row->read_at ? '600' : '800' }};">
                                            {{ $msg->subject ?? '—' }}
                                        </a>
                                    </td>
                                    <td>{{ $row->created_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($row->read_at)
                                            <span class="bp-badge">Read</span>
                                        @else
                                            <span class="bp-badge" style="background:#940000;color:#fff;">New</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="bp-btn bp-btn-sm" href="{{ $openUrl }}"><i class="fa fa-eye"></i> Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
@endsection

@section('scripts')
<style>
    .bp-msg-link {
        color: var(--bp-red);
        text-decoration: underline;
        font-weight: 700;
    }
    .bp-msg-link:hover { color: var(--bp-red-dark); }
    .bp-msg-row:hover { background: #f7f3ef !important; }
    .bp-btn-sm {
        padding: 0.35rem 0.65rem;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .bp-message-detail {
        scroll-margin-top: 90px;
    }
</style>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toggle = document.getElementById('toggleCompose');
    const panel = document.getElementById('composePanel');
    const sendTo = document.getElementById('msgSendTo');
    const pickWrap = document.getElementById('msgPickWrap');
    const form = document.getElementById('composeMessageForm');
    const detail = document.querySelector('.bp-message-detail');

    if (detail) detail.scrollIntoView({ behavior: 'smooth', block: 'start' });

    document.querySelectorAll('.bp-msg-row[data-href]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            var href = row.getAttribute('data-href');
            if (href) location.href = href;
        });
    });

    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('compose') === '1') panel.classList.add('is-open');

    if (sendTo && pickWrap) {
        sendTo.addEventListener('change', function () {
            pickWrap.style.display = sendTo.value === 'selected' ? '' : 'none';
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(`{{ route('board.portal.messages.compose') }}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            }).then(r => r.json()).then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                location.href = `{{ route('board.portal.messages') }}?tab=sent&id=` + (data.messageID || '');
            }).catch(err => alert(err.message || 'Failed to send'));
        });
    }

    const replyForm = document.getElementById('replyMessageForm');
    const replyBtn = document.getElementById('replySendBtn');
    const replyStatus = document.getElementById('replyStatus');
    if (replyForm) {
        replyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (replyBtn) replyBtn.disabled = true;
            if (replyStatus) replyStatus.textContent = 'Sending…';
            fetch(`{{ route('board.portal.messages.reply') }}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(replyForm)
            }).then(r => r.json()).then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                if (replyStatus) replyStatus.textContent = data.message || 'Sent.';
                location.href = `{{ route('board.portal.messages') }}?tab=sent&id=` + (data.messageID || '');
            }).catch(err => {
                if (replyStatus) replyStatus.textContent = '';
                if (replyBtn) replyBtn.disabled = false;
                alert(err.message || 'Failed to send reply');
            });
        });
    }
})();
</script>
@endsection
