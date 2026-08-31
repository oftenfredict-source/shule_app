@php
    $skipSplash = true;
    $portalActive = 'account';
    $seo = [
        'title' => 'My Account | ShuleXpert Online Admission',
        'robots' => 'noindex, follow',
    ];
    $displayPhone = $user->phone;
    if (str_starts_with($displayPhone, '255') && strlen($displayPhone) >= 12) {
        $displayPhone = '0' . substr($displayPhone, 3);
    }
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-oa-portal section-gap">
    <div class="container">
        <h2 class="mb-4">Account</h2>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="sx-oa-panel">
                    <dl class="sx-oa-account-list">
                        <dt>Full name</dt>
                        <dd>{{ $user->full_name }}</dd>
                        <dt>Phone (username)</dt>
                        <dd>{{ $displayPhone }}</dd>
                        <dt>Account type</dt>
                        <dd>Online Application Account</dd>
                        <dt>Status</dt>
                        <dd><span class="sx-oa-status sx-oa-status--confirmed">{{ $user->status }}</span></dd>
                        <dt>Member since</dt>
                        <dd>{{ $user->created_at?->format('d M Y') }}</dd>
                    </dl>

                    <div class="mt-4">
                        <a href="{{ route('online_application.portal.change_password') }}" class="primary-btn text-uppercase">
                            <i class="fa fa-lock"></i> Change Password
                        </a>
                    </div>

                    <p class="text-muted small mt-4 mb-0">
                        This account is separate from school staff, teacher, and parent accounts. It is used only for online school applications on ShuleXpert.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
