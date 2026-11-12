@php
    $platforms = $platforms ?? \App\Support\SchoolSocialMedia::platforms();
    $index = (int) ($index ?? 0);
    $selected = $selected ?? '';
    $value = $value ?? '';
    $meta = $selected && isset($platforms[$selected]) ? $platforms[$selected] : null;
@endphp
<div class="ssm-row" data-index="{{ $index }}">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="small fw-bold">Platform</label>
            <div class="ssm-picker">
                <input type="hidden" class="ssm-platform" name="social_media[{{ $index }}][platform]" value="{{ $selected }}">
                <button type="button" class="ssm-picker-btn" aria-haspopup="listbox" aria-expanded="false">
                    @if($meta)
                        <span class="ssm-picker-icon ssm-picker-icon-logo">
                            <img class="ssm-logo" src="{{ $meta['logo'] }}" alt="{{ $meta['label'] }}" width="20" height="20" loading="lazy">
                        </span>
                        <span class="ssm-picker-label">{{ $meta['label'] }}</span>
                    @else
                        <span class="ssm-picker-icon ssm-picker-icon-empty">
                            <i class="fi fi-rr-link"></i>
                        </span>
                        <span class="ssm-picker-label text-muted">-- Select social media --</span>
                    @endif
                    <i class="fi fi-rr-angle-small-down ssm-picker-caret"></i>
                </button>
                <div class="ssm-picker-menu" role="listbox" hidden>
                    <button type="button" class="ssm-picker-option" data-value="" data-label="-- Select social media --">
                        <span class="ssm-picker-icon ssm-picker-icon-empty"><i class="fi fi-rr-link"></i></span>
                        <span>-- Select social media --</span>
                    </button>
                    @foreach($platforms as $key => $p)
                        <button type="button"
                                class="ssm-picker-option {{ $selected === $key ? 'is-selected' : '' }}"
                                data-value="{{ $key }}"
                                data-label="{{ $p['label'] }}"
                                data-logo="{{ $p['logo'] ?? '' }}"
                                data-color="{{ $p['color'] }}">
                            <span class="ssm-picker-icon ssm-picker-icon-logo">
                                <img class="ssm-logo" src="{{ $p['logo'] ?? '' }}" alt="{{ $p['label'] }}" width="20" height="20" loading="lazy">
                            </span>
                            <span>{{ $p['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <label class="small fw-bold ssm-value-label">
                {{ ($meta['type'] ?? '') === 'phone' ? 'Phone Number' : 'Profile Link' }}
            </label>
            <input class="form-control form-control-sm ssm-value"
                   type="{{ ($meta['type'] ?? '') === 'phone' ? 'tel' : 'url' }}"
                   name="social_media[{{ $index }}][value]"
                   value="{{ $value }}"
                   placeholder="{{ $meta['placeholder'] ?? 'Select a platform first' }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-sm ssm-remove" title="Remove">
                <i class="fi fi-rr-trash"></i>
                <span>Remove</span>
            </button>
        </div>
    </div>
</div>
