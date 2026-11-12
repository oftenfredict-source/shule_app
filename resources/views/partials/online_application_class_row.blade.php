@php
    $hideSpaces = $hideSpaces ?? true;
@endphp
<div class="sx-apply-class-row {{ $class['hasSpaces'] ? '' : 'is-full' }}">
    <div class="sx-apply-class-row__info">
        <strong>{{ $class['className'] }}</strong>
        @unless($hideSpaces)
            @if($class['hasSpaces'])
                <small><i class="fa fa-check-circle" aria-hidden="true"></i> {{ __('site.apply_spaces_available', ['count' => $class['availableSpaces']]) }}</small>
            @else
                <small class="is-full-text"><i class="fa fa-ban" aria-hidden="true"></i> {{ __('site.apply_no_space') }}</small>
            @endif
        @endunless
    </div>
    @if($class['hasSpaces'] && !empty($class['applyUrl']))
        @php
            $applyHref = $class['applyUrl'];
            $needsLogin = !auth('online_application')->check();
            if ($needsLogin) {
                $applyPath = parse_url($applyHref, PHP_URL_PATH) ?: '/online-application/apply';
                $applyQuery = parse_url($applyHref, PHP_URL_QUERY);
                $redirectTarget = $applyPath . ($applyQuery ? ('?' . $applyQuery) : '');
                $applyHref = route('online_application.auth.login', ['redirect' => $redirectTarget]);
            }
        @endphp
        <a href="{{ $applyHref }}" class="sx-apply-btn-sm">
            <i class="fa {{ $needsLogin ? 'fa-sign-in' : 'fa-paper-plane' }}" aria-hidden="true"></i>
            <span>{{ $needsLogin ? __('site.apply_login_to_apply') : __('site.apply_now') }}</span>
        </a>
    @endif
</div>
