<div class="sx-apply-class-row {{ $class['hasSpaces'] ? '' : 'is-full' }}">
    <div>
        <strong>{{ $class['className'] }}</strong>
        @if($class['hasSpaces'])
            <small>{{ $class['availableSpaces'] }} space(s) available</small>
        @else
            <small class="text-danger">No space for application</small>
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
            {{ $needsLogin ? 'Login to Apply' : 'Apply' }}
        </a>
    @endif
</div>
