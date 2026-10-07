{{-- Global academic year selector (header) --}}
@php
    $ayCtx = $headerAcademicYear ?? [
        'mode' => 'none',
        'has_a_level' => false,
        'o_level_years' => collect(),
        'a_level_years' => collect(),
        'years' => collect(),
        'selected_academic_yearID' => null,
        'selected_o_level_academic_yearID' => null,
        'selected_a_level_academic_yearID' => null,
    ];
    $ayMode = $ayCtx['mode'] ?? 'none';
@endphp

@if($ayMode !== 'none')
<style>
    /* Keep header year selectors always in one horizontal row */
    #header .header-left {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        white-space: nowrap;
    }
    #header .header-left > .dropdown,
    #header .header-left > .header-academic-year {
        display: inline-flex !important;
        flex-shrink: 0;
        vertical-align: middle;
    }
    .header-academic-year {
        display: inline-flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 10px;
        margin-left: 16px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .header-academic-year .ay-group {
        display: inline-flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 6px;
        flex-shrink: 0;
        white-space: nowrap;
    }
    .header-academic-year .ay-label {
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.92);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .header-academic-year select {
        min-width: 100px;
        width: auto;
        max-width: none;
        height: 32px;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #d0d0d0;
        border-radius: 4px;
        background: #fff;
        color: #333;
        cursor: pointer;
        flex-shrink: 0;
        white-space: nowrap;
    }
    .header-academic-year select:focus {
        outline: none;
        border-color: #940000;
        box-shadow: 0 0 0 2px rgba(148, 0, 0, 0.15);
    }
    @media (max-width: 991px) {
        #header .header-left {
            flex-wrap: nowrap !important;
            white-space: nowrap;
        }
        .header-academic-year {
            margin-left: 4px !important;
            gap: 4px !important;
            flex: 0 0 auto !important;
        }
        .header-academic-year select {
            min-width: 0 !important;
            max-width: 102px !important;
            width: auto !important;
            font-size: 11px;
            flex-shrink: 1 !important;
        }
        .header-academic-year .ay-label {
            display: none;
        }
    }

    /* Academic year shift overlay */
    #ayShiftOverlay {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(20, 10, 10, 0.55);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    #ayShiftOverlay.is-visible {
        display: flex;
        animation: ayFadeIn 0.2s ease;
    }
    @keyframes ayFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .ay-shift-card {
        background: #fff;
        border-radius: 14px;
        padding: 28px 36px;
        min-width: 280px;
        max-width: 90vw;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        border-top: 4px solid #940000;
        animation: ayCardPop 0.28s ease;
    }
    @keyframes ayCardPop {
        from { transform: translateY(12px) scale(0.96); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
    .ay-shift-spinner {
        width: 52px;
        height: 52px;
        margin: 0 auto 16px;
        border: 3px solid #f0d0d0;
        border-top-color: #940000;
        border-radius: 50%;
        animation: aySpin 0.75s linear infinite;
    }
    @keyframes aySpin {
        to { transform: rotate(360deg); }
    }
    .ay-shift-title {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 700;
        color: #940000;
        letter-spacing: 0.2px;
    }
    .ay-shift-sub {
        margin: 0;
        font-size: 13px;
        color: #666;
        line-height: 1.4;
    }
    .ay-shift-year {
        display: inline-block;
        margin-top: 10px;
        padding: 4px 12px;
        background: #f8eaea;
        color: #940000;
        font-weight: 700;
        font-size: 13px;
        border-radius: 20px;
    }
    .ay-shift-dots::after {
        content: '';
        animation: ayDots 1.2s steps(4, end) infinite;
    }
    @keyframes ayDots {
        0% { content: ''; }
        25% { content: '.'; }
        50% { content: '..'; }
        75% { content: '...'; }
        100% { content: ''; }
    }
</style>

<div class="header-academic-year" id="headerAcademicYearSelector"
     data-mode="{{ $ayMode }}"
     data-update-url="{{ route('selected_academic_year.update') }}">
    @if($ayMode === 'secondary_dual')
        <div class="ay-group">
            <span class="ay-label">O-Level</span>
            <select id="headerOLevelYear" class="header-ay-select" data-level="o_level" title="O-Level Academic Year">
                @foreach(($ayCtx['o_level_years'] ?? []) as $year)
                    <option value="{{ $year->academic_yearID }}"
                        @selected((int)($ayCtx['selected_o_level_academic_yearID'] ?? 0) === (int)$year->academic_yearID)>
                        {{ $year->year_name ?: $year->year }}{{ $year->is_current ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="ay-group">
            <span class="ay-label">A-Level</span>
            <select id="headerALevelYear" class="header-ay-select" data-level="a_level" title="A-Level Academic Year">
                @foreach(($ayCtx['a_level_years'] ?? []) as $year)
                    <option value="{{ $year->academic_yearID }}"
                        @selected((int)($ayCtx['selected_a_level_academic_yearID'] ?? 0) === (int)$year->academic_yearID)>
                        {{ $year->year_name ?: $year->year }}{{ $year->is_current ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <div class="ay-group">
            <span class="ay-label">Year</span>
            <select id="headerAcademicYear" class="header-ay-select" data-level="single" title="Academic Year">
                @php
                    $singleYears = ($ayCtx['o_level_years'] ?? null);
                    if (!$singleYears || (is_countable($singleYears) && count($singleYears) === 0)) {
                        $singleYears = $ayCtx['years'] ?? [];
                    }
                    $selectedSingle = $ayCtx['selected_o_level_academic_yearID']
                        ?? $ayCtx['selected_academic_yearID']
                        ?? null;
                @endphp
                @foreach($singleYears as $year)
                    <option value="{{ $year->academic_yearID }}"
                        @selected((int)$selectedSingle === (int)$year->academic_yearID)>
                        {{ $year->year_name ?: $year->year }}{{ $year->is_current ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>

<div id="ayShiftOverlay" aria-live="polite" aria-busy="true">
    <div class="ay-shift-card">
        <div class="ay-shift-spinner"></div>
        <p class="ay-shift-title">Shifting academic year<span class="ay-shift-dots"></span></p>
        <p class="ay-shift-sub">Please wait while we load the selected year</p>
        <span class="ay-shift-year" id="ayShiftYearLabel"></span>
    </div>
</div>

<script>
(function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    function showAyShiftLoading(yearLabel, levelLabel) {
        var overlay = document.getElementById('ayShiftOverlay');
        var labelEl = document.getElementById('ayShiftYearLabel');
        if (!overlay) return;

        var text = yearLabel || '';
        if (levelLabel) {
            text = levelLabel + ' · ' + text;
        }
        if (labelEl) {
            labelEl.textContent = text;
            labelEl.style.display = text ? 'inline-block' : 'none';
        }
        overlay.classList.add('is-visible');
        document.body.style.overflow = 'hidden';
    }

    function hideAyShiftLoading() {
        var overlay = document.getElementById('ayShiftOverlay');
        if (overlay) overlay.classList.remove('is-visible');
        document.body.style.overflow = '';
    }

    function bindHeaderAcademicYear() {
        var root = document.getElementById('headerAcademicYearSelector');
        if (!root || root.dataset.bound === '1') return;
        root.dataset.bound = '1';

        var url = root.getAttribute('data-update-url');
        var selects = root.querySelectorAll('.header-ay-select');

        selects.forEach(function (select) {
            select.addEventListener('change', function () {
                var payload = { _token: csrfToken() };
                var level = select.getAttribute('data-level');
                var selectedText = (select.options[select.selectedIndex] || {}).text || '';
                selectedText = selectedText.replace(/\s*★\s*$/, '').trim();

                var levelLabel = '';
                if (level === 'o_level') {
                    payload.o_level_academic_yearID = select.value;
                    levelLabel = 'O-Level';
                } else if (level === 'a_level') {
                    payload.a_level_academic_yearID = select.value;
                    levelLabel = 'A-Level';
                } else {
                    payload.academic_yearID = select.value;
                    payload.o_level_academic_yearID = select.value;
                }

                selects.forEach(function (s) { s.disabled = true; });
                showAyShiftLoading(selectedText, levelLabel);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success) {
                        hideAyShiftLoading();
                        alert(data.message || 'Failed to update academic year');
                        selects.forEach(function (s) { s.disabled = false; });
                        return;
                    }
                    // Keep overlay visible while page reloads
                    window.location.reload();
                })
                .catch(function () {
                    hideAyShiftLoading();
                    alert('Failed to update academic year');
                    selects.forEach(function (s) { s.disabled = false; });
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindHeaderAcademicYear);
    } else {
        bindHeaderAcademicYear();
    }
})();
</script>
@endif
