@php
    $skipSplash = true;
    $portalActive = 'applications';
    $seo = [
        'title' => 'My Applications — ShuleXpert Online Admission',
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-oa-portal section-gap">
    <div class="container">
        <h2 class="mb-4">My Applications</h2>
        <p class="text-muted mb-4">Applications you submitted through your online application account. Pending applications can be edited or deleted.</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="sx-oa-panel">
            @if($applications->count() > 0)
                @include('online_application.partials.applications-view', [
                    'items' => $applications,
                    'tableId' => 'sxOaMyAppsTable',
                    'rootId' => 'sxOaMyAppsViewRoot',
                    'gridId' => 'sxOaMyAppsGrid',
                    'storageKey' => 'sx_oa_portal_my_apps',
                    'showYear' => true,
                ])
            @else
                <div class="sx-oa-apps-empty sx-apply-empty">
                    <i class="fa fa-file-text-o"></i>
                    <h5>No applications yet</h5>
                    <p>Start by browsing schools and applying online.</p>
                    <a href="{{ route('online_application.portal.apply') }}" class="primary-btn text-uppercase mt-2 d-inline-block">Apply Now</a>
                </div>
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
