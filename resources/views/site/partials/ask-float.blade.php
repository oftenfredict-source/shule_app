@php
    $askVideo = public_path('1234567890.mp4');
    $askVideoUrl = is_file($askVideo) ? asset('1234567890.mp4') : null;
@endphp
@if($askVideoUrl)
<div class="sx-ask-float" id="sxAskFloat" data-open="false">
    <div class="sx-ask-panel" id="sxAskPanel" role="dialog" aria-modal="true" aria-labelledby="sxAskTitle" hidden>
        <div class="sx-ask-panel__head">
            <div>
                <p class="sx-ask-panel__eyebrow">{{ __('site.ask_float_eyebrow') }}</p>
                <h3 id="sxAskTitle">{{ __('site.ask_float_title') }}</h3>
            </div>
            <button type="button" class="sx-ask-panel__close" id="sxAskClose" aria-label="{{ __('site.ask_float_close') }}">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <p class="sx-ask-panel__lead">{{ __('site.ask_float_lead') }}</p>

        <form id="sxAskForm" class="sx-ask-form" novalidate>
            @csrf
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="sx-ask-honeypot" aria-hidden="true">

            <label class="sx-ask-label" for="sx_ask_name">{{ __('site.ask_float_name') }}</label>
            <input id="sx_ask_name" name="name" type="text" maxlength="120" required
                   class="sx-ask-input" placeholder="{{ __('site.ask_float_name_ph') }}">

            <label class="sx-ask-label" for="sx_ask_phone">{{ __('site.ask_float_phone') }}</label>
            <input id="sx_ask_phone" name="phone" type="tel" maxlength="15" inputmode="numeric" required
                   class="sx-ask-input" placeholder="2557XXXXXXXX" value="255">

            <label class="sx-ask-label" for="sx_ask_email">{{ __('site.ask_float_email') }}</label>
            <input id="sx_ask_email" name="email" type="email" maxlength="160"
                   class="sx-ask-input" placeholder="{{ __('site.ask_float_email_ph') }}">

            <label class="sx-ask-label" for="sx_ask_message">{{ __('site.ask_float_message') }}</label>
            <textarea id="sx_ask_message" name="message" rows="3" maxlength="2000" required
                      class="sx-ask-input sx-ask-textarea" placeholder="{{ __('site.ask_float_message_ph') }}"></textarea>

            <p class="sx-ask-status" id="sxAskStatus" hidden></p>

            <button type="submit" class="sx-ask-submit" id="sxAskSubmit">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
                <span>{{ __('site.ask_float_submit') }}</span>
            </button>
        </form>

        <div class="sx-ask-success" id="sxAskSuccess" hidden>
            <i class="fa fa-check-circle" aria-hidden="true"></i>
            <p>{{ __('site.ask_float_success') }}</p>
            <button type="button" class="sx-ask-submit sx-ask-submit--ghost" id="sxAskDone">{{ __('site.ask_float_done') }}</button>
        </div>
    </div>

    <button type="button" class="sx-ask-fab" id="sxAskFab" aria-expanded="false" aria-controls="sxAskPanel">
        <span class="sx-ask-fab__ring" aria-hidden="true"></span>
        <span class="sx-ask-fab__video">
            <video src="{{ $askVideoUrl }}" autoplay muted loop playsinline preload="metadata" aria-hidden="true"></video>
        </span>
        <span class="sx-ask-fab__label">{{ __('site.ask_float_fab') }}</span>
    </button>
</div>

<style>
.sx-ask-float {
    position: fixed;
    right: 18px;
    bottom: 22px;
    z-index: 9800;
    font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
}
.sx-ask-fab {
    position: relative;
    width: 64px;
    height: 64px;
    border: 0;
    padding: 0;
    background: transparent;
    cursor: pointer;
    border-radius: 50%;
}
.sx-ask-fab__ring {
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    border: 2px solid rgba(148, 0, 0, 0.35);
    animation: sxAskPulse 2.2s ease-out infinite;
    pointer-events: none;
}
.sx-ask-fab__video {
    position: relative;
    display: block;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #940000;
    box-shadow: 0 8px 18px rgba(80, 0, 0, 0.28);
    background: #fff;
    box-sizing: border-box;
}
.sx-ask-fab__video video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    max-width: none;
    object-fit: cover;
    transform: scale(3.35);
    transform-origin: 64% 74%;
    display: block;
    pointer-events: none;
}
.sx-ask-fab__label {
    position: absolute;
    right: calc(100% + 10px);
    top: 50%;
    transform: translateY(-50%);
    white-space: nowrap;
    background: #940000;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.02em;
    padding: 7px 12px;
    box-shadow: 0 8px 20px rgba(80, 0, 0, 0.22);
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s ease, transform .2s ease;
}
.sx-ask-fab:hover .sx-ask-fab__label,
.sx-ask-float[data-open="false"] .sx-ask-fab:focus-visible .sx-ask-fab__label {
    opacity: 1;
    transform: translateY(-50%) translateX(-2px);
}
.sx-ask-panel {
    width: min(340px, calc(100vw - 28px));
    background: #fff;
    border: 1px solid #eadada;
    box-shadow: 0 18px 48px rgba(40, 0, 0, 0.22);
    padding: 16px 16px 14px;
    transform-origin: bottom right;
    animation: sxAskIn .22s ease-out;
}
.sx-ask-panel__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 6px;
}
.sx-ask-panel__eyebrow {
    margin: 0 0 2px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #940000;
}
.sx-ask-panel h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1f1212;
    line-height: 1.25;
}
.sx-ask-panel__close {
    width: 32px;
    height: 32px;
    border: 1px solid #eadada;
    background: #faf7f7;
    color: #555;
    cursor: pointer;
    flex-shrink: 0;
}
.sx-ask-panel__close:hover { background: #f3eaea; color: #940000; }
.sx-ask-panel__lead {
    margin: 0 0 12px;
    font-size: 0.86rem;
    color: #666;
    line-height: 1.45;
}
.sx-ask-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #777;
    margin: 0 0 4px;
}
.sx-ask-input {
    width: 100%;
    border: 1px solid #e3d5d5;
    background: #fff;
    color: #222;
    padding: 9px 11px;
    font-size: 0.92rem;
    margin-bottom: 10px;
    outline: none;
}
.sx-ask-input:focus { border-color: #940000; box-shadow: 0 0 0 2px rgba(148,0,0,.12); }
.sx-ask-textarea { resize: vertical; min-height: 78px; }
.sx-ask-honeypot {
    position: absolute !important;
    left: -9999px !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
}
.sx-ask-submit {
    width: 100%;
    border: 0;
    background: #940000;
    color: #fff;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 11px 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.sx-ask-submit:hover { background: #b30000; }
.sx-ask-submit:disabled { opacity: 0.65; cursor: wait; }
.sx-ask-submit--ghost {
    background: transparent;
    color: #940000;
    border: 1px solid #940000;
    margin-top: 10px;
}
.sx-ask-submit--ghost:hover { background: #fff5f5; }
.sx-ask-status {
    margin: 0 0 10px;
    font-size: 0.84rem;
    padding: 8px 10px;
    background: #fff4f4;
    border: 1px solid #f0c7c7;
    color: #8a1a1a;
}
.sx-ask-status.is-ok {
    background: #f1faf3;
    border-color: #b9e0c2;
    color: #1d6b35;
}
.sx-ask-success {
    text-align: center;
    padding: 18px 8px 6px;
}
.sx-ask-success i {
    font-size: 2rem;
    color: #1d6b35;
    margin-bottom: 8px;
}
.sx-ask-success p {
    margin: 0;
    color: #333;
    font-size: 0.95rem;
    line-height: 1.45;
}
@keyframes sxAskPulse {
    0% { transform: scale(1); opacity: 0.85; }
    70% { transform: scale(1.18); opacity: 0; }
    100% { transform: scale(1.18); opacity: 0; }
}
@keyframes sxAskIn {
    from { opacity: 0; transform: translateY(10px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
html[data-sx-theme="dark"] .sx-ask-panel {
    background: #1c1215;
    border-color: #3a2a2e;
    box-shadow: 0 18px 48px rgba(0, 0, 0, 0.45);
}
html[data-sx-theme="dark"] .sx-ask-panel h3 { color: #f4ecec; }
html[data-sx-theme="dark"] .sx-ask-panel__lead,
html[data-sx-theme="dark"] .sx-ask-label { color: #b9a8ac; }
html[data-sx-theme="dark"] .sx-ask-input {
    background: #24181b;
    border-color: #3a2a2e;
    color: #f4ecec;
}
html[data-sx-theme="dark"] .sx-ask-panel__close {
    background: #24181b;
    border-color: #3a2a2e;
    color: #d7c8cb;
}
html[data-sx-theme="dark"] .sx-ask-success p { color: #e8dddd; }
@media (max-width: 575px) {
    .sx-ask-float { right: 12px; bottom: 16px; }
    .sx-ask-fab,
    .sx-ask-fab__video { width: 58px; height: 58px; }
    .sx-ask-fab__label { display: none; }
}
</style>

<script>
(function () {
    if (window.__sxAskFloatBound) return;
    window.__sxAskFloatBound = true;

    var root = document.getElementById('sxAskFloat');
    if (!root) return;

    var fab = document.getElementById('sxAskFab');
    var panel = document.getElementById('sxAskPanel');
    var closeBtn = document.getElementById('sxAskClose');
    var form = document.getElementById('sxAskForm');
    var statusEl = document.getElementById('sxAskStatus');
    var successEl = document.getElementById('sxAskSuccess');
    var submitBtn = document.getElementById('sxAskSubmit');
    var doneBtn = document.getElementById('sxAskDone');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var token = csrf ? csrf.getAttribute('content') : '';
    var endpoint = @json(route('site.quick_ask'));

    function setOpen(open) {
        root.setAttribute('data-open', open ? 'true' : 'false');
        fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            panel.hidden = false;
        } else {
            panel.hidden = true;
        }
    }

    function showStatus(msg, ok) {
        statusEl.hidden = !msg;
        statusEl.textContent = msg || '';
        statusEl.classList.toggle('is-ok', !!ok);
    }

    fab.addEventListener('click', function () {
        setOpen(panel.hidden);
    });
    closeBtn.addEventListener('click', function () { setOpen(false); });
    doneBtn.addEventListener('click', function () {
        setOpen(false);
        form.hidden = false;
        successEl.hidden = true;
        form.reset();
        var phone = document.getElementById('sx_ask_phone');
        if (phone && !phone.value) phone.value = '255';
        showStatus('', false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) setOpen(false);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        showStatus('', false);

        var fd = new FormData(form);
        submitBtn.disabled = true;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
        .then(function (r) {
            return r.json().then(function (data) {
                return { ok: r.ok, status: r.status, data: data || {} };
            }).catch(function () {
                return { ok: false, status: r.status, data: {} };
            });
        })
        .then(function (res) {
            if (res.ok && res.data.success) {
                form.hidden = true;
                successEl.hidden = false;
                showStatus('', false);
                return;
            }
            var msg = (res.data && res.data.message) ? res.data.message : '{{ __('site.ask_float_error') }}';
            if (res.data && res.data.errors) {
                var first = Object.keys(res.data.errors)[0];
                if (first && res.data.errors[first] && res.data.errors[first][0]) {
                    msg = res.data.errors[first][0];
                }
            }
            showStatus(msg, false);
        })
        .catch(function () {
            showStatus('{{ __('site.ask_float_error') }}', false);
        })
        .finally(function () {
            submitBtn.disabled = false;
        });
    });
})();
</script>
@endif
