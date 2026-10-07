@php
    $sxLocale = app()->getLocale() === 'sw' ? 'sw' : 'en';
@endphp
<div class="sx-site-prefs" aria-label="{{ __('site.language') }} / theme">
    <div class="sx-lang-switch" role="group" aria-label="{{ __('site.language') }}">
        <button type="button"
                class="sx-lang-btn{{ $sxLocale === 'en' ? ' is-active' : '' }}"
                data-locale="en"
                aria-pressed="{{ $sxLocale === 'en' ? 'true' : 'false' }}">
            EN
        </button>
        <button type="button"
                class="sx-lang-btn{{ $sxLocale === 'sw' ? ' is-active' : '' }}"
                data-locale="sw"
                aria-pressed="{{ $sxLocale === 'sw' ? 'true' : 'false' }}">
            SW
        </button>
    </div>
    @include('includes.theme_toggle')
</div>
