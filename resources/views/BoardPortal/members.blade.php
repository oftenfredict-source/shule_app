@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-users"></i> Board members</h2>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                @if(!empty($caps['can_compose_messages']))
                    <a class="bp-btn" href="{{ route('board.portal.messages', ['compose' => 1]) }}">
                        <i class="fa fa-envelope"></i> Portal message
                    </a>
                @endif
                @if(!empty($caps['can_message_members']))
                    <button type="button" class="bp-btn" id="toggleMessagePanel">
                        <i class="fa fa-mobile"></i> Send SMS
                    </button>
                @endif
            </div>
        </div>

        @if(!empty($caps['can_message_members']))
            <div class="bp-create-panel" id="messagePanel">
                @if(empty($smsEnabled))
                    <div class="bp-flash err" style="margin-bottom:0.85rem;">
                        SMS is disabled for this school. Ask the school admin to enable SMS before messaging members.
                    </div>
                @endif
                <form id="memberMessageForm">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Send to *</label>
                            <select class="bp-select" name="send_to" id="sendToSelect" required>
                                <option value="all">All members with phone numbers</option>
                                <option value="selected">Selected members only</option>
                            </select>
                        </div>
                        <div class="full" id="memberPickWrap" style="display:none;">
                            <label class="bp-label">Select members</label>
                            <div class="bp-check-list" id="memberCheckList">
                                @foreach($boardMembers as $m)
                                    @if(!empty($m->phone))
                                        <label>
                                            <input type="checkbox" name="member_ids[]" value="{{ $m->memberID }}">
                                            {{ $m->full_name }}
                                            <span class="bp-muted">· {{ $m->position->position_name ?? 'Member' }} · {{ $m->phone }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                            @if($boardMembers->where('phone', '!=', null)->where('phone', '!=', '')->isEmpty())
                                <p class="bp-muted" style="font-size:0.85rem;margin:0.4rem 0 0;">No members have phone numbers on file.</p>
                            @endif
                        </div>
                        <div class="full">
                            <label class="bp-label">Message *</label>
                            <textarea class="bp-textarea" name="message" id="memberSmsBody" maxlength="480" required placeholder="Write your message to board members…"></textarea>
                            <div class="bp-muted" style="font-size:0.78rem;margin-top:4px;">
                                <span id="smsCharCount">0</span>/480 characters
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;display:flex;flex-wrap:wrap;gap:0.55rem;align-items:center;">
                        <button type="submit" class="bp-btn" id="sendSmsBtn" {{ empty($smsEnabled) ? 'disabled' : '' }}>
                            <i class="fa fa-paper-plane"></i> Send via system SMS
                        </button>
                        <span class="bp-muted" style="font-size:0.82rem;" id="smsSendStatus"></span>
                    </div>
                </form>
            </div>
        @endif

        @if($boardMembers->isEmpty())
            <div class="bp-empty">No active board members found.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Phone</th>
                            <th>Email</th>
                            @if(!empty($caps['can_compose_messages']) || !empty($caps['can_message_members']))
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($boardMembers as $m)
                            <tr>
                                <td>
                                    <strong>{{ $m->full_name }}</strong>
                                    @if((int)$m->memberID === (int)$member->memberID)
                                        <span class="bp-badge" style="margin-left:6px;">You</span>
                                    @endif
                                </td>
                                <td>{{ $m->position->position_name ?? 'Member' }}</td>
                                <td>{{ $m->phone ?: '—' }}</td>
                                <td>{{ $m->email ?: '—' }}</td>
                                @if(!empty($caps['can_compose_messages']) || !empty($caps['can_message_members']))
                                    <td style="white-space:nowrap;">
                                        @if(!empty($caps['can_compose_messages']) && (int)$m->memberID !== (int)$member->memberID)
                                            <a class="bp-btn bp-btn-sm" href="{{ route('board.portal.messages', ['compose' => 1, 'to' => $m->memberID]) }}">
                                                <i class="fa fa-envelope-o"></i> Message
                                            </a>
                                        @endif
                                        @if(!empty($caps['can_message_members']) && !empty($m->phone) && !empty($smsEnabled))
                                            <button type="button" class="bp-btn bp-btn-sm js-msg-one" data-id="{{ $m->memberID }}" data-name="{{ $m->full_name }}">
                                                <i class="fa fa-mobile"></i> SMS
                                            </button>
                                        @elseif(!empty($caps['can_message_members']) && empty($m->phone))
                                            <span class="bp-muted" style="font-size:0.8rem;">No phone</span>
                                        @endif
                                    </td>
                                @endif
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
    }
</style>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toggle = document.getElementById('toggleMessagePanel');
    const panel = document.getElementById('messagePanel');
    const form = document.getElementById('memberMessageForm');
    const sendTo = document.getElementById('sendToSelect');
    const pickWrap = document.getElementById('memberPickWrap');
    const bodyEl = document.getElementById('memberSmsBody');
    const countEl = document.getElementById('smsCharCount');
    const statusEl = document.getElementById('smsSendStatus');
    const btn = document.getElementById('sendSmsBtn');

    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('message') === '1') panel.classList.add('is-open');

    if (sendTo && pickWrap) {
        sendTo.addEventListener('change', function () {
            pickWrap.style.display = sendTo.value === 'selected' ? '' : 'none';
        });
    }

    if (bodyEl && countEl) {
        const syncCount = () => { countEl.textContent = String(bodyEl.value.length); };
        bodyEl.addEventListener('input', syncCount);
        syncCount();
    }

    document.querySelectorAll('.js-msg-one').forEach(function (el) {
        el.addEventListener('click', function () {
            if (!panel || !form || !sendTo) return;
            panel.classList.add('is-open');
            sendTo.value = 'selected';
            if (pickWrap) pickWrap.style.display = '';
            form.querySelectorAll('input[name="member_ids[]"]').forEach(function (cb) {
                cb.checked = String(cb.value) === String(el.dataset.id);
            });
            if (bodyEl) {
                bodyEl.focus();
                if (!bodyEl.value.trim()) {
                    bodyEl.placeholder = 'Message to ' + (el.dataset.name || 'member') + '…';
                }
            }
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (btn && btn.disabled) return;

        const fd = new FormData(form);
        if (statusEl) statusEl.textContent = 'Sending…';
        if (btn) btn.disabled = true;

        fetch(`{{ route('board.portal.members.message') }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        }).then(r => r.json().then(data => ({ ok: r.ok, data }))).then(({ data }) => {
            if (!data.success) throw new Error(data.message || 'Failed to send');
            if (statusEl) statusEl.textContent = data.message || 'Sent.';
            alert(data.message || 'SMS sent.');
            form.reset();
            if (pickWrap) pickWrap.style.display = 'none';
            if (countEl) countEl.textContent = '0';
        }).catch(err => {
            if (statusEl) statusEl.textContent = '';
            alert(err.message || 'Failed to send SMS');
        }).finally(() => {
            if (btn) btn.disabled = {{ empty($smsEnabled) ? 'true' : 'false' }};
        });
    });
})();
</script>
@endsection
