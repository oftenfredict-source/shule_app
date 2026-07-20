@php
    $user = auth('online_application')->user();
    $portalActive = $portalActive ?? ($active ?? '');
@endphp
<header class="sx-oa-header">
    <div class="container">
        <div class="sx-oa-header__top">
            <a href="{{ route('online_application.portal.dashboard') }}" class="sx-oa-header__brand">
                <img src="{{ asset('images/shuleXpert.jpg') }}" alt="ShuleXpert" class="sx-oa-header__logo" onerror="this.src='{{ asset('images/shuleXpert-favicon.png') }}'">
                <span class="sx-oa-header__titles">
                    <strong>ShuleXpert</strong>
                    <span>Online Application Portal</span>
                </span>
            </a>

            <div class="sx-oa-header__actions">
                @if($user)
                    <div class="sx-oa-header__user d-none d-md-block">
                        <span class="sx-oa-header__user-name">{{ $user->full_name ?? $user->first_name }}</span>
                        <span class="sx-oa-header__user-label">Logged in</span>
                    </div>
                    <form action="{{ route('online_application.auth.logout') }}" method="POST" class="sx-oa-header__logout-form">
                        @csrf
                        <button type="submit" class="sx-oa-header__btn sx-oa-header__btn--logout" title="Logout">
                            <i class="fa fa-sign-out"></i>
                            <span class="sx-oa-header__btn-text">Logout</span>
                        </button>
                    </form>
                    <button type="button"
                        class="sx-oa-header__menu-btn d-md-none"
                        id="sxOaMenuToggle"
                        aria-expanded="false"
                        aria-controls="sxOaHeaderNav"
                        aria-label="Open menu">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>
                @else
                    <a href="{{ route('online_application.auth.login') }}" class="sx-oa-header__btn sx-oa-header__btn--login">
                        <i class="fa fa-sign-in"></i>
                        <span class="sx-oa-header__btn-text">Login</span>
                    </a>
                    <a href="{{ route('online_application.auth.register') }}" class="sx-oa-header__btn sx-oa-header__btn--outline d-none d-sm-inline-flex">
                        <i class="fa fa-user-plus"></i>
                        <span class="sx-oa-header__btn-text">Create account</span>
                    </a>
                @endif
            </div>
        </div>

        @if($user)
            <div class="sx-oa-header__user-mobile d-md-none">
                <span>{{ $user->full_name ?? $user->first_name }}</span>
            </div>
            <nav class="sx-oa-header__nav" id="sxOaHeaderNav" aria-label="Portal navigation">
                <a href="{{ route('online_application.portal.dashboard') }}" class="{{ $portalActive === 'dashboard' ? 'is-active' : '' }}">
                    <i class="fa fa-home"></i> Dashboard
                </a>
                <a href="{{ route('online_application.portal.applications') }}" class="{{ $portalActive === 'applications' ? 'is-active' : '' }}">
                    <i class="fa fa-file-text-o"></i> My Applications
                </a>
                <a href="{{ route('online_application.portal.apply') }}" class="{{ $portalActive === 'apply' ? 'is-active' : '' }}">
                    <i class="fa fa-pencil-square-o"></i> Apply Now
                </a>
                <a href="{{ route('online_application.portal.account') }}" class="{{ $portalActive === 'account' ? 'is-active' : '' }}">
                    <i class="fa fa-user"></i> Account
                </a>
                <a href="{{ route('online_application.portal.change_password') }}" class="{{ $portalActive === 'password' ? 'is-active' : '' }}">
                    <i class="fa fa-lock"></i> Change Password
                </a>
            </nav>
        @endif
    </div>
</header>
