@php
    $skipSplash = true;
    $portalActive = 'password';
    $seo = [
        'title' => 'Change Password — ShuleXpert Online Admission',
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-oa-portal section-gap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="sx-oa-panel">
                    <h3 class="mb-3">Change Password</h3>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('online_application.portal.change_password') }}">
                        @csrf
                        <div class="form-group">
                            <label class="required" for="current_password">Current password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="required" for="password">New password</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="required" for="password_confirmation">Confirm new password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-sx-auth">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
