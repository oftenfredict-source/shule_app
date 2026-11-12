{{-- Reusable school social media A/C fields --}}
@php
    $ssmId = $ssmId ?? 'ssm';
    $ssmEnabled = !empty($ssmEnabled);
    $ssmItems = is_array($ssmItems ?? null) ? $ssmItems : [];
    $ssmPlatforms = \App\Support\SchoolSocialMedia::platforms();
    $ssmHideToggle = !empty($ssmHideToggle);
@endphp

<style>
    .ssm-wrap .ssm-row {
        border: 1px solid #eee;
        background: #fafafa;
        padding: 12px;
        margin-bottom: 10px;
    }
    .ssm-picker {
        position: relative;
    }
    .ssm-wrap .ssm-picker-btn,
    .ssm-wrap .ssm-value.form-control,
    .ssm-wrap input.ssm-value {
        width: 100%;
        height: 38px !important;
        min-height: 38px !important;
        max-height: 38px !important;
        box-sizing: border-box;
        border: 1px solid #ced4da;
        border-radius: 0 !important;
        font-size: 0.875rem;
        line-height: 1.2;
        padding: 0 10px;
    }
    .ssm-picker-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        text-align: left;
        cursor: pointer;
    }
    .ssm-wrap .ssm-value.form-control:focus,
    .ssm-wrap input.ssm-value:focus,
    .ssm-picker-btn:focus {
        outline: 0;
        border-color: #940000;
        box-shadow: 0 0 0 .15rem rgba(148,0,0,.15);
    }
    .ssm-picker-label {
        flex: 1;
        font-size: 0.875rem;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ssm-picker-caret {
        color: #666;
        font-size: 18px;
    }
    .ssm-picker-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #444 !important;
        font-size: 15px;
        flex-shrink: 0;
        background: #f3f3f3;
    }
    .ssm-picker-icon-logo {
        background: #fff !important;
        border: 1px solid #e6e6e6;
        padding: 3px;
    }
    .ssm-picker-icon-empty {
        background: #ececec !important;
        color: #666 !important;
    }
    .ssm-logo {
        width: 18px;
        height: 18px;
        object-fit: contain;
        display: block;
    }
    .ssm-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-height: 38px;
        width: 100%;
        border: 1px solid #940000 !important;
        background: #fff !important;
        color: #940000 !important;
        font-weight: 700;
        font-size: 0.78rem;
        border-radius: 0.45rem;
    }
    .ssm-remove i {
        color: #940000 !important;
        font-size: 0.95rem;
        line-height: 1;
    }
    .ssm-remove:hover {
        background: #fff5f5 !important;
        border-color: #940000 !important;
        color: #940000 !important;
    }
    .ssm-picker-menu {
        position: absolute;
        z-index: 1055;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 8px 20px rgba(0,0,0,.12);
    }
    .ssm-picker-option {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border: 0;
        border-bottom: 1px solid #f1f1f1;
        background: #fff;
        text-align: left;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 600;
        color: #333;
    }
    .ssm-picker-option:last-child { border-bottom: 0; }
    .ssm-picker-option:hover { background: #f8f9fa; }
    .ssm-picker-option.is-selected { background: #fff5f5; }
    .ssm-picker-option.is-disabled {
        opacity: 0.45;
        pointer-events: none;
        background: #f3f3f3;
        color: #999;
    }
    .ssm-picker-option.is-disabled .ssm-picker-icon {
        filter: grayscale(1);
    }
</style>

<div class="ssm-wrap" id="{{ $ssmId }}_wrap" data-ssm-id="{{ $ssmId }}">
    <div class="form-check form-switch mb-2{{ $ssmHideToggle ? ' visually-hidden' : '' }}">
        <input class="form-check-input ssm-enable" type="checkbox" id="{{ $ssmId }}_enable" name="enable_social_media" value="1" {{ $ssmEnabled ? 'checked' : '' }}>
        @unless($ssmHideToggle)
            <label class="form-check-label fw-bold" for="{{ $ssmId }}_enable">School Social Media A/C</label>
            <div class="small text-muted">When ON, add school social links. Parents see icons in the portal header.</div>
        @endunless
    </div>

    <div class="ssm-fields" id="{{ $ssmId }}_fields" style="{{ $ssmEnabled ? '' : 'display:none;' }}">
        <div class="ssm-rows" id="{{ $ssmId }}_rows">
            @forelse($ssmItems as $i => $item)
                @include('SuperAdmin.partials.school_social_media_row', [
                    'ssmId' => $ssmId,
                    'index' => $i,
                    'selected' => $item['platform'] ?? '',
                    'value' => $item['value'] ?? '',
                    'platforms' => $ssmPlatforms,
                ])
            @empty
                @include('SuperAdmin.partials.school_social_media_row', [
                    'ssmId' => $ssmId,
                    'index' => 0,
                    'selected' => '',
                    'value' => '',
                    'platforms' => $ssmPlatforms,
                ])
            @endforelse
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary ssm-add-btn" id="{{ $ssmId }}_add">
            <i class="bx bx-plus"></i> Add another social media
        </button>
    </div>
</div>

@once
@php
    $ssmPlatformsJson = \App\Support\SchoolSocialMedia::platformsForJs();
@endphp
<script>
(function bootSchoolSocialMediaUI() {
    // Pages like /school often use jQuery.noConflict(), never rely on global $.
    if (!window.jQuery) {
        setTimeout(bootSchoolSocialMediaUI, 50);
        return;
    }
    if (window.SchoolSocialMediaUI && window.SchoolSocialMediaUI.bind) {
        return;
    }

    window.SchoolSocialMediaUI = (function($) {
        const PLATFORMS = @json($ssmPlatformsJson);

        function platformMeta(key) {
            return PLATFORMS.find(function(p) { return p.key === key; }) || null;
        }

        function esc(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function selectedPlatforms($wrap) {
            const selected = [];
            $wrap.find('.ssm-platform').each(function() {
                const v = $(this).val();
                if (v) selected.push(v);
            });
            return selected;
        }

        function logoHtml(meta) {
            if (!meta || !meta.logo) {
                return '<span class="ssm-picker-icon ssm-picker-icon-empty"><i class="fi fi-rr-link"></i></span>';
            }
            return '<span class="ssm-picker-icon ssm-picker-icon-logo">'
                + '<img class="ssm-logo" src="' + esc(meta.logo) + '" alt="' + esc(meta.label) + '" width="20" height="20" loading="lazy">'
                + '</span>';
        }

        function setPickerButton($row, key) {
            const meta = platformMeta(key);
            const $btn = $row.find('.ssm-picker-btn');
            if (!meta) {
                $btn.html(
                    '<span class="ssm-picker-icon ssm-picker-icon-empty"><i class="fi fi-rr-link"></i></span>'
                    + '<span class="ssm-picker-label text-muted">-- Select social media --</span>'
                    + '<i class="fi fi-rr-angle-small-down ssm-picker-caret"></i>'
                );
                return;
            }
            $btn.html(
                logoHtml(meta)
                + '<span class="ssm-picker-label">' + esc(meta.label) + '</span>'
                + '<i class="fi fi-rr-angle-small-down ssm-picker-caret"></i>'
            );
        }

        function closeAllPickers($wrap) {
            $wrap.find('.ssm-picker-menu').attr('hidden', true);
            $wrap.find('.ssm-picker-btn').attr('aria-expanded', 'false');
        }

        function refreshDisabledOptions($wrap) {
            const selected = selectedPlatforms($wrap);
            $wrap.find('.ssm-row').each(function() {
                const $row = $(this);
                const current = $row.find('.ssm-platform').val() || '';
                $row.find('.ssm-picker-option').each(function() {
                    const val = $(this).attr('data-value') || '';
                    if (!val) {
                        $(this).removeClass('is-disabled is-selected');
                        return;
                    }
                    const taken = selected.indexOf(val) !== -1 && val !== current;
                    $(this).toggleClass('is-disabled', taken);
                    $(this).toggleClass('is-selected', val === current);
                });
            });
            $wrap.find('.ssm-add-btn').toggle(selected.length < PLATFORMS.length);
        }

        function updateRowType($row) {
            const key = $row.find('.ssm-platform').val();
            const meta = platformMeta(key);
            const $input = $row.find('.ssm-value');
            const $label = $row.find('.ssm-value-label');
            setPickerButton($row, key);
            if (!meta) {
                $input.attr('placeholder', 'Select a platform first');
                $label.text('Link / Phone');
                return;
            }
            $input.attr('placeholder', meta.placeholder);
            $label.text(meta.type === 'phone' ? 'Phone Number' : 'Profile Link');
            $input.attr('type', meta.type === 'phone' ? 'tel' : 'url');
        }

        function buildPickerMenuHtml(selected) {
            let html = ''
                + '<button type="button" class="ssm-picker-option" data-value="" data-label="-- Select social media --">'
                + '  <span class="ssm-picker-icon ssm-picker-icon-empty"><i class="fi fi-rr-link"></i></span>'
                + '  <span>-- Select social media --</span>'
                + '</button>';
            PLATFORMS.forEach(function(p) {
                const sel = (selected === p.key) ? ' is-selected' : '';
                html += ''
                    + '<button type="button" class="ssm-picker-option' + sel + '"'
                    + ' data-value="' + p.key + '"'
                    + ' data-label="' + esc(p.label) + '"'
                    + ' data-logo="' + esc(p.logo || '') + '"'
                    + ' data-color="' + p.color + '">'
                    + '  ' + logoHtml(p)
                    + '  <span>' + esc(p.label) + '</span>'
                    + '</button>';
            });
            return html;
        }

        function buildRowHtml(index, selected, value) {
            const meta = platformMeta(selected);
            const label = meta ? (meta.type === 'phone' ? 'Phone Number' : 'Profile Link') : 'Link / Phone';
            const ph = meta ? meta.placeholder : 'Select a platform first';
            const inputType = meta && meta.type === 'phone' ? 'tel' : 'url';
            const btnInner = meta
                ? (logoHtml(meta)
                    + '<span class="ssm-picker-label">' + esc(meta.label) + '</span>'
                    + '<i class="fi fi-rr-angle-small-down ssm-picker-caret"></i>')
                : ('<span class="ssm-picker-icon ssm-picker-icon-empty"><i class="fi fi-rr-link"></i></span>'
                    + '<span class="ssm-picker-label text-muted">-- Select social media --</span>'
                    + '<i class="fi fi-rr-angle-small-down ssm-picker-caret"></i>');

            return ''
                + '<div class="ssm-row" data-index="' + index + '">'
                + '  <div class="row g-2 align-items-end">'
                + '    <div class="col-md-5">'
                + '      <label class="small fw-bold">Platform</label>'
                + '      <div class="ssm-picker">'
                + '        <input type="hidden" class="ssm-platform" name="social_media[' + index + '][platform]" value="' + esc(selected) + '">'
                + '        <button type="button" class="ssm-picker-btn" aria-haspopup="listbox" aria-expanded="false">' + btnInner + '</button>'
                + '        <div class="ssm-picker-menu" role="listbox" hidden>' + buildPickerMenuHtml(selected) + '</div>'
                + '      </div>'
                + '    </div>'
                + '    <div class="col-md-5">'
                + '      <label class="small fw-bold ssm-value-label">' + label + '</label>'
                + '      <input class="form-control form-control-sm ssm-value" type="' + inputType + '" name="social_media[' + index + '][value]" value="' + esc(value) + '" placeholder="' + esc(ph) + '">'
                + '    </div>'
                + '    <div class="col-md-2 d-flex align-items-end">'
                + '      <button type="button" class="btn btn-sm ssm-remove" title="Remove"><i class="fi fi-rr-trash"></i><span>Remove</span></button>'
                + '    </div>'
                + '  </div>'
                + '</div>';
        }

        function reindex($wrap) {
            $wrap.find('.ssm-row').each(function(i) {
                $(this).attr('data-index', i);
                $(this).find('.ssm-platform').attr('name', 'social_media[' + i + '][platform]');
                $(this).find('.ssm-value').attr('name', 'social_media[' + i + '][value]');
            });
        }

        function bind($wrap) {
            if (!$wrap || !$wrap.length) return;
            if ($wrap.data('ssm-bound')) return;
            $wrap.data('ssm-bound', 1);

            $wrap.on('change', '.ssm-enable', function() {
                $wrap.find('.ssm-fields').toggle(this.checked);
                if (this.checked && $wrap.find('.ssm-row').length === 0) {
                    $wrap.find('.ssm-rows').append(buildRowHtml(0, '', ''));
                }
                refreshDisabledOptions($wrap);
            });

            $wrap.on('click', '.ssm-picker-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $menu = $(this).siblings('.ssm-picker-menu');
                const open = !$menu.attr('hidden');
                closeAllPickers($wrap);
                if (!open) {
                    $menu.removeAttr('hidden');
                    $(this).attr('aria-expanded', 'true');
                    refreshDisabledOptions($wrap);
                }
            });

            $wrap.on('click', '.ssm-picker-option', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if ($(this).hasClass('is-disabled')) return;
                const $row = $(this).closest('.ssm-row');
                const val = $(this).attr('data-value') || '';
                $row.find('.ssm-platform').val(val);
                closeAllPickers($wrap);
                updateRowType($row);
                refreshDisabledOptions($wrap);
            });

            $(document).on('click.ssmPicker', function(e) {
                if (!$(e.target).closest($wrap).length) {
                    closeAllPickers($wrap);
                }
            });

            $wrap.on('click', '.ssm-add-btn', function() {
                const idx = $wrap.find('.ssm-row').length;
                if (selectedPlatforms($wrap).length >= PLATFORMS.length) return;
                $wrap.find('.ssm-rows').append(buildRowHtml(idx, '', ''));
                reindex($wrap);
                refreshDisabledOptions($wrap);
            });

            $wrap.on('click', '.ssm-remove', function() {
                const $rows = $wrap.find('.ssm-row');
                if ($rows.length <= 1) {
                    const $row = $rows.first();
                    $row.find('.ssm-platform').val('');
                    $row.find('.ssm-value').val('');
                    updateRowType($row);
                } else {
                    $(this).closest('.ssm-row').remove();
                }
                reindex($wrap);
                refreshDisabledOptions($wrap);
            });

            refreshDisabledOptions($wrap);
            $wrap.find('.ssm-row').each(function() { updateRowType($(this)); });
        }

        function loadItems($wrap, enabled, items) {
            $wrap.find('.ssm-enable').prop('checked', !!enabled);
            $wrap.find('.ssm-fields').toggle(!!enabled);
            const $rows = $wrap.find('.ssm-rows');
            $rows.empty();
            const list = Array.isArray(items) ? items : [];
            if (!list.length) {
                $rows.append(buildRowHtml(0, '', ''));
            } else {
                list.forEach(function(item, i) {
                    $rows.append(buildRowHtml(i, item.platform || '', item.value || ''));
                });
            }
            refreshDisabledOptions($wrap);
            $wrap.find('.ssm-row').each(function() { updateRowType($(this)); });
        }

        return { bind: bind, loadItems: loadItems, platforms: PLATFORMS };
    })(window.jQuery);
})();
</script>
@endonce

<script>
(function() {
    function init() {
        if (!window.jQuery || !window.SchoolSocialMediaUI || !window.SchoolSocialMediaUI.bind) {
            setTimeout(init, 50);
            return;
        }
        const $wrap = window.jQuery('#{{ $ssmId }}_wrap');
        if ($wrap.length) {
            window.SchoolSocialMediaUI.bind($wrap);
        }
    }
    init();
})();
</script>
