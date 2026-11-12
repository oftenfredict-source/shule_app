@php
    $logoSrc = $school['schoolLogoUrl']
        ?? (!empty($school['schoolLogo']) ? \App\Models\School::publicAssetUrl($school['schoolLogo']) : null);
    $initial = mb_strtoupper(mb_substr(trim($school['schoolName'] ?? 'S'), 0, 1));
    $isGuest = $requireAuth ?? !auth('online_application')->check();
    $loginUrl = route('online_application.auth.login', [
        'redirect' => '/online-application?school=' . urlencode((string) ($school['schoolID'] ?? '')),
    ]);
@endphp
<article class="sx-apply-school-card h-100">
    <div class="sx-apply-school-card__ribbon" aria-hidden="true"></div>

    <div class="sx-apply-school-card__identity">
        @if($logoSrc)
            <img src="{{ $logoSrc }}"
                 alt="{{ $school['schoolName'] ?? '' }} logo"
                 class="sx-apply-school-card__logo"
                 decoding="async"
                 onerror="this.removeAttribute('src'); this.style.display='none'; var p=this.nextElementSibling; if(p){ p.hidden=false; p.removeAttribute('hidden'); }">
            <div class="sx-apply-school-card__logo sx-apply-school-card__logo--placeholder" hidden>
                <span>{{ $initial }}</span>
            </div>
        @else
            <div class="sx-apply-school-card__logo sx-apply-school-card__logo--placeholder">
                <span>{{ $initial }}</span>
            </div>
        @endif
        <div class="sx-apply-school-card__meta">
            <span class="sx-apply-school-card__type">{{ $school['schoolType'] }}</span>
            <h3>{{ $school['schoolName'] }}</h3>
            <p class="sx-apply-school-card__place">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                {{ $school['region'] }}@if(!empty($school['district'])), {{ $school['district'] }}@endif
            </p>
        </div>
    </div>

    <div class="sx-apply-school-card__classes">
        @if($isGuest)
            <div class="sx-apply-login-gate">
                <div class="sx-apply-login-gate__icon" aria-hidden="true"><i class="fa fa-lock"></i></div>
                <h4>{{ __('site.apply_login_to_apply') }}</h4>
                <p>{{ __('site.apply_login_gate_text') }}</p>
                <a href="{{ $loginUrl }}" class="sx-apply-btn-sm sx-apply-btn-sm--block">
                    <i class="fa fa-sign-in" aria-hidden="true"></i>
                    <span>{{ __('site.apply_login_to_apply') }}</span>
                </a>
            </div>
        @else
            <div class="sx-apply-school-card__classes-head">
                <h4>{{ __('site.apply_available_classes') }}</h4>
            </div>
            @php
                $classesList = collect($school['classesWithSpaces']);
                $visibleClasses = $classesList->take(2);
                $hiddenClasses = $classesList->slice(2);
            @endphp

            @forelse($visibleClasses as $class)
                @include('partials.online_application_class_row', [
                    'class' => $class,
                    'requireAuth' => false,
                    'hideSpaces' => true,
                ])
            @empty
                <div class="sx-apply-login-gate sx-apply-login-gate--empty">
                    <p class="mb-0">{{ __('site.apply_no_classes') }}</p>
                </div>
            @endforelse

            @if($hiddenClasses->count() > 0)
                <div class="sx-apply-more-classes" id="more-classes-{{ $school['schoolID'] }}" style="display:none;">
                    @foreach($hiddenClasses as $class)
                        @include('partials.online_application_class_row', [
                            'class' => $class,
                            'requireAuth' => false,
                            'hideSpaces' => true,
                        ])
                    @endforeach
                </div>
                <button type="button" class="sx-apply-view-more-classes" data-target="more-classes-{{ $school['schoolID'] }}">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                    {{ __('site.apply_view_more_classes', ['count' => $hiddenClasses->count()]) }}
                </button>
            @endif
        @endif
    </div>

    <div class="sx-apply-school-card__footer">
        <button type="button"
                class="sx-apply-btn-outline sx-view-school-details"
                data-school-id="{{ $school['schoolID'] }}"
                title="{{ __('site.apply_view_more') }}">
            <i class="fa fa-eye"></i> {{ __('site.apply_view_more') }}
        </button>
        <button type="button" class="sx-apply-btn-outline sx-copy-school-link"
            data-url="{{ $school['schoolShareUrl'] ?? $school['schoolUrl'] }}" title="{{ __('site.apply_copy_link') }}">
            <i class="fa fa-link"></i> {{ __('site.apply_copy_link') }}
        </button>
    </div>
</article>

