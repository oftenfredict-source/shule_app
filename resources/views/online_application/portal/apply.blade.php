@php
    $skipSplash = true;
    $portalActive = 'apply';
    $seo = [
        'title' => 'Apply to Schools | ShuleXpert Online Admission',
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-apply-page section-gap">
    <div class="container">
        <div class="sx-apply-hero text-center">
            <h2>Apply to a school</h2>
            <p class="sx-apply-intro">Search participating schools and apply where admission spaces are available.</p>
        </div>

        <div class="sx-apply-search">
            <form method="GET" action="{{ route('online_application.portal.apply') }}" class="sx-apply-search__form" id="oaSearchForm">
                <div class="sx-apply-search__field">
                    <i class="fa fa-search"></i>
                    <input type="text" name="search" class="form-control" id="oaSearchInput"
                        placeholder="Search by school name, region, or district..."
                        value="{{ $search }}">
                </div>
                <button type="submit" class="primary-btn text-uppercase">Search</button>
                @if($search)
                    <a href="{{ route('online_application.portal.apply') }}" class="sx-apply-search__clear">Clear</a>
                @endif
                <div class="sx-apply-view-toggle" role="group" aria-label="{{ __('site.apply_layout') }}">
                    <button type="button" class="sx-apply-view-toggle__btn" data-oa-view="list" title="{{ __('site.apply_list') }}" aria-pressed="false">
                        <i class="fa fa-list" aria-hidden="true"></i><span>{{ __('site.apply_list') }}</span>
                    </button>
                    <button type="button" class="sx-apply-view-toggle__btn is-active" data-oa-view="grid" title="{{ __('site.apply_grid') }}" aria-pressed="true">
                        <i class="fa fa-th-large" aria-hidden="true"></i><span>{{ __('site.apply_grid') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="row sx-apply-schools is-grid" id="oaSchoolsGrid"
            data-load-url="{{ route('online_application.schools') }}"
            data-page="{{ $nextPage }}"
            data-has-more="{{ $hasMore ? '1' : '0' }}"
            data-search="{{ $search }}">
            @include('site.partials.online-application-view-boot')
            @include('partials.online_application_school_cards', [
                'schools' => $schools,
                'requireAuth' => false,
            ])

            @if($schools->count() === 0)
                <div class="col-12">
                    <div class="sx-apply-empty">
                        <i class="fa fa-info-circle"></i>
                        <h5>No schools with available spaces found</h5>
                        <p>Please try a different search or check back later.</p>
                    </div>
                </div>
            @endif
        </div>

        <div id="oaLoadMore" class="text-center py-4" style="{{ $hasMore ? '' : 'display:none;' }}">
            <div class="spinner-border text-danger" role="status" id="oaLoadSpinner" style="display:none;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="text-muted mb-0 mt-2" id="oaLoadHint">Scroll down to load more schools</p>
        </div>
    </div>
</section>

@include('site.partials.online-application-modal')
@endsection

@push('scripts')
    @include('site.partials.online-application-scripts')
    @include('site.partials.online-application-lazy-load')
@endpush
