<div class="sx-apply-class-row {{ $class['hasSpaces'] ? '' : 'is-full' }}">
    <div>
        <strong>{{ $class['className'] }}</strong>
        @if($class['hasSpaces'])
            <small>{{ __('site.apply_spaces_available', ['count' => $class['availableSpaces']]) }}</small>
        @else
            <small class="text-danger">{{ __('site.apply_no_space') }}</small>
        @endif
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
            {{ $needsLogin ? __('site.apply_login_to_apply') : __('site.apply_now') }}
        </a>
    @endif
</div>
