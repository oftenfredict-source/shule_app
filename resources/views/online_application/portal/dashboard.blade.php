@php
    $skipSplash = true;
    $portalActive = 'dashboard';
    $seo = [
        'title' => 'My Application Dashboard — ShuleXpert Online Admission',
        'description' => 'Manage your online school applications, apply to new schools, and track admission status.',
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-oa-portal section-gap">
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-lg-8">
                <h2>Welcome, {{ $user->first_name }}</h2>
                <p class="text-muted">Track your applications and apply to schools with available admission spaces.</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <a href="{{ route('online_application.portal.apply') }}" class="sx-oa-dash-card">
                    <i class="fa fa-pencil-square-o"></i>
                    <strong>Apply Now</strong>
                    <span>Browse schools and submit applications</span>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('online_application.portal.applications') }}" class="sx-oa-dash-card">
                    <i class="fa fa-file-text-o"></i>
                    <strong>My Applications</strong>
                    <span>View status of your submissions</span>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('online_application.portal.account') }}" class="sx-oa-dash-card">
                    <i class="fa fa-user"></i>
                    <strong>Account</strong>
                    <span>Your profile and password</span>
                </a>
            </div>
        </div>

        <div class="sx-oa-panel">
            @if($recentApplications->count() > 0)
                @include('online_application.partials.applications-view', [
                    'items' => $recentApplications,
                    'tableId' => 'sxOaRecentAppsTable',
                    'rootId' => 'sxOaRecentAppsViewRoot',
                    'gridId' => 'sxOaRecentAppsGrid',
                    'storageKey' => 'sx_oa_portal_recent_apps',
                    'showYear' => true,
                    'panelTitle' => 'Recent applications',
                ])
            @else
                <h4>Recent applications</h4>
                <p class="text-muted mb-0">You have not submitted any applications yet. <a href="{{ route('online_application.portal.apply') }}">Apply now</a></p>
            @endif
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('online_application.partials.applications-view-script')
@endpush
