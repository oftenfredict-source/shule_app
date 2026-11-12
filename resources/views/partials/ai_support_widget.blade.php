@php
    $sxAiMode = $sxAiMode ?? 'dashboard';
    $sxAiLocale = (Session::get('locale', app()->getLocale()) === 'sw') ? 'sw' : 'en';
    $sxAiRoutes = $sxAiMode === 'public'
        ? [
            'bootstrap' => route('public.ai_support.bootstrap'),
            'store' => route('public.ai_support.conversations.store'),
            'chat' => route('public.ai_support.chat'),
        ]
        : [
            'bootstrap' => route('admin.ai_support.bootstrap'),
            'store' => route('admin.ai_support.conversations.store'),
            'chat' => route('admin.ai_support.chat'),
        ];
@endphp
<div id="sxAiSupport" class="sx-ai-support" data-locale="{{ $sxAiLocale }}"
     data-bootstrap-url="{{ $sxAiRoutes['bootstrap'] }}"
     data-store-url="{{ $sxAiRoutes['store'] }}"
     data-chat-url="{{ $sxAiRoutes['chat'] }}"
     hidden>
    <div class="sx-ai-panel" id="sxAiPanel" role="dialog" aria-modal="true" aria-labelledby="sxAiTitle" hidden>
        <div class="sx-ai-panel__head">
            <div class="sx-ai-panel__brand">
                <span class="sx-ai-panel__avatar" aria-hidden="true"><i class="fa fa-comments"></i></span>
                <div>
                    <h3 id="sxAiTitle">ShuleXpert AI Assistant</h3>
                    <p class="sx-ai-panel__status" id="sxAiStatusLine">{{ $sxAiLocale === 'sw' ? 'Tayari kukusaidia' : 'Ready to help' }}</p>
                </div>
            </div>
            <div class="sx-ai-panel__actions">
                <button type="button" class="sx-ai-icon-btn" id="sxAiNewChat" title="{{ $sxAiLocale === 'sw' ? 'Mazungumzo mapya' : 'New chat' }}">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
                <button type="button" class="sx-ai-icon-btn" id="sxAiClose" aria-label="{{ $sxAiLocale === 'sw' ? 'Funga' : 'Close' }}">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="sx-ai-messages" id="sxAiMessages" aria-live="polite"></div>

        <form class="sx-ai-composer" id="sxAiForm" autocomplete="off">
            <label class="sr-only" for="sxAiInput">{{ $sxAiLocale === 'sw' ? 'Ujumbe' : 'Message' }}</label>
            <textarea id="sxAiInput" rows="1" maxlength="4000"
                placeholder="{{ $sxAiLocale === 'sw' ? 'Andika swali lako...' : 'Type your question...' }}"></textarea>
            <button type="submit" class="sx-ai-send" id="sxAiSend" aria-label="{{ $sxAiLocale === 'sw' ? 'Tuma' : 'Send' }}">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
    </div>

    <button type="button" class="sx-ai-fab" id="sxAiFab" aria-expanded="false" aria-controls="sxAiPanel">
        <i class="fa fa-comments" aria-hidden="true"></i>
        <span class="sx-ai-fab__label">{{ $sxAiLocale === 'sw' ? 'AI Msaada' : 'AI Help' }}</span>
    </button>
</div>

<style>
.sx-ai-support {
    position: fixed;
    right: 18px;
    bottom: 22px;
    z-index: 9700;
    font-family: "Open Sans", "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
}
.sx-ai-fab {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 0;
    border-radius: 999px;
    background: #940000;
    color: #fff;
    padding: 12px 18px 12px 14px;
    box-shadow: 0 12px 28px rgba(148, 0, 0, 0.35);
    cursor: pointer;
    font-weight: 700;
    font-size: 14px;
    transition: transform .2s ease, box-shadow .2s ease;
}
.sx-ai-fab:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(148, 0, 0, 0.42); }
.sx-ai-fab .fa { font-size: 18px; }
.sx-ai-panel {
    width: min(400px, calc(100vw - 28px));
    height: min(560px, calc(100vh - 120px));
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 18px;
    box-shadow: 0 22px 48px rgba(40, 0, 0, 0.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.sx-ai-panel__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 14px 12px;
    background: linear-gradient(165deg, #940000 0%, #7a0000 55%, #5c0000 100%);
    color: #fff;
}
.sx-ai-panel__brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
.sx-ai-panel__avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,.18);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sx-ai-panel__head h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
}
.sx-ai-panel__status {
    margin: 2px 0 0;
    font-size: 11px;
    color: #ffd6d6;
}
.sx-ai-panel__actions { display: flex; gap: 6px; }
.sx-ai-icon-btn {
    width: 34px; height: 34px; border: 0; border-radius: 8px;
    background: rgba(255,255,255,.14); color: #fff; cursor: pointer;
}
.sx-ai-icon-btn:hover { background: rgba(255,255,255,.24); }
.sx-ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px 14px;
    background: #faf7f7;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.sx-ai-bubble {
    max-width: 88%;
    padding: 10px 12px;
    border-radius: 14px;
    font-size: 13.5px;
    line-height: 1.55;
    white-space: pre-wrap;
    word-break: break-word;
}
.sx-ai-bubble--user {
    align-self: flex-end;
    background: #940000;
    color: #fff;
    border-bottom-right-radius: 4px;
}
.sx-ai-bubble--assistant {
    align-self: flex-start;
    background: #fff;
    color: #222;
    border: 1px solid #f0e4e4;
    border-bottom-left-radius: 4px;
}
.sx-ai-bubble--system {
    align-self: center;
    background: transparent;
    color: #888;
    font-size: 12px;
    text-align: center;
    max-width: 100%;
}
.sx-ai-typing {
    align-self: flex-start;
    display: inline-flex;
    gap: 5px;
    padding: 12px 14px;
    background: #fff;
    border: 1px solid #f0e4e4;
    border-radius: 14px;
}
.sx-ai-typing span {
    width: 7px; height: 7px; border-radius: 50%;
    background: #c08080;
    animation: sxAiDot 1.2s infinite ease-in-out;
}
.sx-ai-typing span:nth-child(2) { animation-delay: .15s; }
.sx-ai-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes sxAiDot {
    0%, 80%, 100% { opacity: .35; transform: translateY(0); }
    40% { opacity: 1; transform: translateY(-3px); }
}
.sx-ai-composer {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid #f0e4e4;
    background: #fff;
}
.sx-ai-composer textarea {
    flex: 1;
    resize: none;
    border: 1px solid #eddada;
    border-radius: 12px;
    min-height: 44px;
    max-height: 110px;
    padding: 10px 12px;
    font-size: 14px;
    line-height: 1.4;
    outline: none;
    font-family: inherit;
}
.sx-ai-composer textarea:focus { border-color: #940000; box-shadow: 0 0 0 3px rgba(148,0,0,.12); }
.sx-ai-send {
    width: 44px; height: 44px; border: 0; border-radius: 12px;
    background: #940000; color: #fff; cursor: pointer; flex-shrink: 0;
}
.sx-ai-send:disabled { opacity: .55; cursor: not-allowed; }
.sx-ai-support .sr-only {
    position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0); border: 0;
}
html[data-sx-theme="dark"] .sx-ai-panel,
html[data-sx-theme="dark"] .sx-ai-composer,
html[data-sx-theme="dark"] .sx-ai-bubble--assistant,
html[data-sx-theme="dark"] .sx-ai-typing {
    background: #1e1418 !important;
    border-color: rgba(255,180,180,.14) !important;
    color: #f4ecee !important;
}
html[data-sx-theme="dark"] .sx-ai-messages { background: #140c0f !important; }
html[data-sx-theme="dark"] .sx-ai-composer textarea {
    background: #2a1b20 !important;
    border-color: rgba(255,180,180,.14) !important;
    color: #f4ecee !important;
}
html[data-sx-theme="dark"] .sx-ai-bubble--assistant { color: #f4ecee !important; }
@media (max-width: 575.98px) {
    .sx-ai-support { right: 12px; bottom: 14px; }
    .sx-ai-panel {
        width: calc(100vw - 24px);
        height: min(70vh, 560px);
    }
    .sx-ai-fab__label { display: none; }
    .sx-ai-fab { width: 56px; height: 56px; justify-content: center; padding: 0; border-radius: 50%; }
}
</style>

<script>
(function () {
    var root = document.getElementById('sxAiSupport');
    if (!root) return;

    var locale = root.getAttribute('data-locale') === 'sw' ? 'sw' : 'en';
    var bootstrapUrl = root.getAttribute('data-bootstrap-url');
    var storeUrl = root.getAttribute('data-store-url');
    var chatUrl = root.getAttribute('data-chat-url');
    var panel = document.getElementById('sxAiPanel');
    var fab = document.getElementById('sxAiFab');
    var closeBtn = document.getElementById('sxAiClose');
    var newBtn = document.getElementById('sxAiNewChat');
    var form = document.getElementById('sxAiForm');
    var input = document.getElementById('sxAiInput');
    var sendBtn = document.getElementById('sxAiSend');
    var messagesEl = document.getElementById('sxAiMessages');
    var statusLine = document.getElementById('sxAiStatusLine');

    var conversationId = null;
    var busy = false;
    var bootstrapped = false;
    var welcomeText = locale === 'sw'
        ? 'Habari! Mimi ni ShuleXpert AI Assistant. Uliza swali kuhusu jinsi ya kutumia ShuleXpert.'
        : 'Hello! I am ShuleXpert AI Assistant. Ask me how to use ShuleXpert.';

    var i18n = {
        en: {
            ready: 'Ready to help',
            thinking: 'Thinking…',
            error: 'Something went wrong. Please try again.',
            empty: 'Please type a message.',
            newChat: 'Started a new conversation.'
        },
        sw: {
            ready: 'Tayari kukusaidia',
            thinking: 'Inafikiri…',
            error: 'Tatizo limetokea. Tafadhali jaribu tena.',
            empty: 'Tafadhali andika ujumbe.',
            newChat: 'Mazungumzo mapya yameanza.'
        }
    }[locale];

    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function openPanel() {
        panel.hidden = false;
        fab.setAttribute('aria-expanded', 'true');
        if (!bootstrapped) {
            bootstrap();
        }
        setTimeout(function () { input.focus(); }, 50);
    }

    function closePanel() {
        panel.hidden = true;
        fab.setAttribute('aria-expanded', 'false');
    }

    function appendBubble(role, text) {
        var div = document.createElement('div');
        div.className = 'sx-ai-bubble sx-ai-bubble--' + role;
        div.textContent = text;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        return div;
    }

    function showTyping(show) {
        var existing = document.getElementById('sxAiTyping');
        if (existing) existing.remove();
        if (!show) return;
        var el = document.createElement('div');
        el.className = 'sx-ai-typing';
        el.id = 'sxAiTyping';
        el.innerHTML = '<span></span><span></span><span></span>';
        messagesEl.appendChild(el);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function setBusy(state) {
        busy = state;
        sendBtn.disabled = state;
        input.disabled = state;
        statusLine.textContent = state ? i18n.thinking : i18n.ready;
    }

    function bootstrap() {
        bootstrapped = true;
        fetch(bootstrapUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data && data.success && data.data) {
                if (data.data.welcome) welcomeText = data.data.welcome;
            }
            if (!messagesEl.children.length) {
                appendBubble('assistant', welcomeText);
            }
        }).catch(function () {
            if (!messagesEl.children.length) {
                appendBubble('assistant', welcomeText);
            }
        });
    }

    function startNewChat() {
        conversationId = null;
        messagesEl.innerHTML = '';
        appendBubble('system', i18n.newChat);
        appendBubble('assistant', welcomeText);
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ locale: locale })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data && data.success && data.data) {
                conversationId = data.data.id;
            }
        }).catch(function () {});
        input.focus();
    }

    function sendMessage(text) {
        if (busy) return;
        var message = (text || '').trim();
        if (!message) {
            appendBubble('system', i18n.empty);
            return;
        }

        appendBubble('user', message);
        input.value = '';
        autoGrow();
        setBusy(true);
        showTyping(true);

        fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                message: message,
                conversation_id: conversationId,
                locale: locale
            })
        }).then(function (r) {
            return r.json().then(function (data) {
                return { ok: r.ok, status: r.status, data: data };
            });
        }).then(function (res) {
            showTyping(false);
            setBusy(false);
            if (!res.ok || !res.data || !res.data.success) {
                appendBubble('system', (res.data && res.data.message) ? res.data.message : i18n.error);
                return;
            }
            conversationId = res.data.data.conversation_id;
            appendBubble('assistant', res.data.data.assistant_message.content);
        }).catch(function () {
            showTyping(false);
            setBusy(false);
            appendBubble('system', i18n.error);
        });
    }

    function autoGrow() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 110) + 'px';
    }

    fab.addEventListener('click', function () {
        if (panel.hidden) openPanel(); else closePanel();
    });
    closeBtn.addEventListener('click', closePanel);
    newBtn.addEventListener('click', startNewChat);
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage(input.value);
    });
    input.addEventListener('input', autoGrow);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(input.value);
        }
    });

    root.hidden = false;
})();
</script>
