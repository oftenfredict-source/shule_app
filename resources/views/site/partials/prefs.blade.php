@php
    $sxLocale = app()->getLocale() === 'sw' ? 'sw' : 'en';
@endphp
<div class="sx-site-prefs" aria-label="{{ __('site.language') }} / theme">
    <div class="sx-lang-switch" role="group" aria-label="{{ __('site.language') }}">
        <button type="button"
                class="sx-lang-btn{{ $sxLocale === 'en' ? ' is-active' : '' }}"
                data-locale="en"
                title="English"
                aria-label="English"
                aria-pressed="{{ $sxLocale === 'en' ? 'true' : 'false' }}">
            <img class="sx-lang-flag" src="https://flagcdn.com/w40/gb.png" width="18" height="12" alt="" loading="lazy" decoding="async">
            <span>EN</span>
        </button>
        <button type="button"
                class="sx-lang-btn{{ $sxLocale === 'sw' ? ' is-active' : '' }}"
                data-locale="sw"
                title="Kiswahili"
                aria-label="Kiswahili"
                aria-pressed="{{ $sxLocale === 'sw' ? 'true' : 'false' }}">
            <img class="sx-lang-flag" src="https://flagcdn.com/w40/tz.png" width="18" height="12" alt="" loading="lazy" decoding="async">
            <span>SW</span>
        </button>
    </div>
    @include('includes.theme_toggle')
</div>
