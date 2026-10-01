@php
    $skipSplash = true;
    $isPortalUser = auth('online_application')->check();
    $portalActive = 'apply';
    $schoolName = $school->school_name;
    $schoolLogoUrl = $school->school_logo
        ? url($school->school_logo)
        : url('/images/shuleXpert.jpg');
    $locationBits = array_filter([
        $school->school_type,
        $school->ownership,
        $school->region,
        $school->district,
    ]);
    $seoDesc = 'Apply online to ' . $schoolName
        . (!empty($locationBits) ? ' (' . implode(', ', $locationBits) . ')' : '')
        . '. View available classes, admission spaces, and fee structure, then submit your student admission application on ShuleXpert by EmCa Techonology.';
    // Public marketing landing is indexable; portal chrome (logged-in) stays noindex.
    $seo = [
        'title' => $schoolName . ' Online Application, Apply Now | ShuleXpert',
        'description' => \Illuminate\Support\Str::limit($seoDesc, 160, ''),
        'keywords' => implode(', ', array_filter([
            $schoolName,
            $school->region,
            $school->district,
            $school->school_type,
            'online school application Tanzania',
            'school admission',
            'apply to school online',
            'ShuleXpert',
            'EmCa Techonology',
        ])),
        'canonical' => $schoolUrl,
        'type' => 'website',
        'image' => $schoolLogoUrl,
        'favicon' => $school->school_logo ? $schoolLogoUrl : null,
        'robots' => $isPortalUser
            ? 'noindex, follow'
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
    ];
@endphp
@extends($isPortalUser ? 'layouts.online_application_portal' : 'layouts.site')

@section('content')
@unless($isPortalUser)
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">{{ $schoolName }}</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('online_application') }}">Online Application</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ $schoolUrl }}">{{ $schoolName }}</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endunless

<section class="sx-apply-page sx-school-landing section-gap {{ $isPortalUser ? 'sx-oa-portal' : '' }}">
    <div class="container">
        @if($isPortalUser)
            <div class="mb-3">
                <h2 class="mb-1">{{ $schoolName }}</h2>
                <p class="text-muted mb-0">School application details · choose a class to apply</p>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="sx-school-landing__toolbar">
            <a href="{{ auth('online_application')->check() ? route('online_application.portal.apply') : route('online_application') }}" class="sx-apply-btn-outline">
                <i class="fa fa-arrow-left"></i> All schools
            </a>
            <a href="#sx-school-fees" class="sx-apply-btn-outline">
                <i class="fa fa-money"></i> Fees
            </a>
            <a href="#sx-school-classes" class="sx-apply-btn-outline">
                <i class="fa fa-graduation-cap"></i> Classes
            </a>
            <button type="button" class="sx-apply-btn-outline" id="sxCopySchoolLink" data-url="{{ $schoolUrl }}">
                <i class="fa fa-link"></i> Copy application link
            </button>
            @auth('online_application')
                <a href="{{ route('online_application.portal.dashboard') }}" class="primary-btn text-uppercase">My Dashboard</a>
            @else
                <a href="{{ route('online_application.auth.login', ['redirect' => request()->getRequestUri()]) }}" class="sx-apply-btn-outline">
                    <i class="fa fa-sign-in"></i> Login
                </a>
                <a href="{{ route('online_application.auth.register', ['redirect' => request()->getRequestUri()]) }}" class="sx-apply-btn-outline">
                    <i class="fa fa-user-plus"></i> Create account
                </a>
            @endauth
        </div>

        <div class="sx-school-landing__hero">
            @if($school->school_logo)
                <img src="{{ asset($school->school_logo) }}" alt="{{ $schoolName }} logo" class="sx-school-landing__logo" width="96" height="96">
            @else
                <div class="sx-school-landing__logo sx-school-landing__logo--placeholder" aria-hidden="true">
                    <i class="fa fa-university"></i>
                </div>
            @endif
            <div>
                <h2>{{ $schoolName }}</h2>
                <p class="sx-school-landing__meta">
                    {{ $school->school_type }}
                    @if($school->ownership) · {{ $school->ownership }} @endif
                    @if($school->region) · {{ $school->region }} @endif
                    @if($school->district) · {{ $school->district }} @endif
                </p>
                <div class="sx-school-landing__stats">
                    <span><strong>{{ $card['classesCount'] }}</strong> Classes</span>
                    <span><strong>{{ $card['subclassesCount'] }}</strong> Subclasses</span>
                    <span><strong>{{ $card['activeStudentsCount'] }}</strong> Students</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <h3 class="sx-school-details__heading">School information</h3>
                <dl class="sx-school-landing__info">
                    @if($school->formattedPhones())
                        <div><dt>Phone</dt><dd>{{ $school->phone }}</dd></div>
                    @endif
                    @if($school->email)
                        <div><dt>Email</dt><dd>{{ $school->email }}</dd></div>
                    @endif
                    @if($school->address)
                        <div><dt>Address</dt><dd>{{ $school->address }}</dd></div>
                    @endif
                    @if($school->ward)
                        <div><dt>Ward</dt><dd>{{ $school->ward }}</dd></div>
                    @endif
                    @if($school->village)
                        <div><dt>Village</dt><dd>{{ $school->village }}</dd></div>
                    @endif
                    @if($school->established_year)
                        <div><dt>Established</dt><dd>{{ $school->established_year }}</dd></div>
                    @endif
                </dl>

                <h3 class="sx-school-details__heading" id="sx-school-classes">Available classes</h3>
                @if($card['classesWithSpaces']->count() === 0)
                    <div class="sx-apply-empty sx-apply-empty--warn">
                        <i class="fa fa-info-circle"></i>
                        <h5>No classes open for application</h5>
                        <p>Please check back later or contact the school.</p>
                    </div>
                @else
                    <div class="sx-school-landing__classes">
                        @foreach($card['classesWithSpaces'] as $class)
                            @include('partials.online_application_class_row', [
                                'class' => $class,
                                'requireAuth' => false,
                            ])
                        @endforeach
                    </div>
                    @guest('online_application')
                        <p class="sx-school-landing__hint mt-3 mb-0">
                            <i class="fa fa-lock"></i>
                            To apply, please login or create an account first. After login you will continue with your selected class.
                        </p>
                    @endguest
                @endif
            </div>

            <div class="col-lg-5 mb-4" id="sx-school-fees">
                <h3 class="sx-school-details__heading">Fee structure</h3>
                @if($feesByClass->isEmpty())
                    <div class="sx-apply-empty">
                        <i class="fa fa-money"></i>
                        <h5>No fee structure published</h5>
                        <p>Contact the school for current fees.</p>
                    </div>
                @else
                    <div class="sx-school-fee-toolbar mb-3">
                        <button type="button"
                            class="sx-apply-btn-outline sx-fee-pdf-btn"
                            data-class-ids="{{ $feesByClass->keys()->implode(',') }}"
                            data-label="All Classes">
                            <i class="fa fa-file-pdf-o"></i> Download all classes
                        </button>
                    </div>
                    @foreach($feesByClass as $classID => $fees)
                        @php
                            $classLabel = optional(optional($fees->first())->class)->class_name
                                ?? optional($card['classesWithSpaces']->firstWhere('classID', (int) $classID))['className']
                                ?? ('Class #' . $classID);
                        @endphp
                        <div class="sx-school-fee-block">
                            <div class="sx-school-fee-block__head">
                                <h4>{{ $classLabel }}</h4>
                                <button type="button"
                                    class="sx-apply-btn-outline sx-fee-pdf-btn sx-fee-pdf-btn--sm"
                                    data-class-ids="{{ (int) $classID }}"
                                    data-label="{{ $classLabel }}">
                                    <i class="fa fa-download"></i> Download PDF
                                </button>
                            </div>
                            <ul class="sx-school-fee-list">
                                @foreach($fees as $fee)
                                    <li>
                                        <div class="sx-school-fee-list__main">
                                            <strong>{{ $fee->fee_name }}</strong>
                                            <span>{{ number_format((float) $fee->amount, 0) }} TZS</span>
                                        </div>
                                        @if($fee->relationLoaded('breakdowns') && $fee->breakdowns->count())
                                            <ul class="sx-school-fee-list__break">
                                                @foreach($fee->breakdowns as $b)
                                                    <li>
                                                        {{ $b->item_name ?? $b->name ?? 'Item' }}
                                                       , {{ number_format((float) ($b->amount ?? 0), 0) }} TZS
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Fees PDF download progress (same pattern as manage_fees) --}}
<div class="modal fade" id="sxFeePdfProgressModal" data-backdrop="static" tabindex="-1" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0 !important;">
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <i class="fa fa-file-pdf-o text-danger" style="font-size: 3rem; animation: sxFeePulse 1.5s infinite;"></i>
                </div>
                <h5 class="mb-4" style="color: #940000; font-weight: bold; font-family: 'Century Gothic', CenturyGothic, sans-serif;">
                    Generating Fees Structure Report
                </h5>
                <div class="progress mb-2" style="height: 12px; border-radius: 0 !important;">
                    <div id="sxFeePdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                        role="progressbar" style="width: 0%; border-radius: 0 !important;"></div>
                </div>
                <div id="sxFeePdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                <p class="mt-3 text-muted small mb-0">Sawa, tafadhali subiri kidogo wakati tunaandaa ripoti yako ya ada.</p>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes sxFeePulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var copyBtn = document.getElementById('sxCopySchoolLink');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var url = copyBtn.getAttribute('data-url') || '';
            if (!url) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Link copied', text: 'You can forward this link for online applications.', timer: 2200, showConfirmButton: false });
                    } else {
                        alert('Link copied. You can forward it for online applications.');
                    }
                }).catch(function () {
                    window.prompt('Copy this application link:', url);
                });
            } else {
                window.prompt('Copy this application link:', url);
            }
        });
    }

    var feesPdfUrl = @json(route('online_application.fees_structure_pdf'));
    var schoolToken = @json($token);

    function showFeePdfError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Download failed', text: message || 'Could not generate fee structure PDF.' });
        } else {
            alert(message || 'Could not generate fee structure PDF.');
        }
    }

    function runFeesStructurePdfExport(classIDs, classLabel) {
        if (typeof jQuery === 'undefined') {
            showFeePdfError('Page scripts are not ready. Please refresh and try again.');
            return;
        }
        var $ = jQuery;
        var url = feesPdfUrl + '?token=' + encodeURIComponent(schoolToken);
        (classIDs || []).forEach(function (id) {
            if (id) url += '&classIDs[]=' + encodeURIComponent(id);
        });

        var safeName = String(classLabel || 'All_Classes').replace(/\s+/g, '_').replace(/[^\w\-]+/g, '');

        $('#sxFeePdfProgressModal').modal('show');
        $('#sxFeePdfProgressBar').css('width', '0%');
        $('#sxFeePdfProgressPercent').text('0%');

        var progress = 0;
        var interval = setInterval(function () {
            if (progress < 95) {
                progress += 2 + Math.random() * 5;
                if (progress > 95) progress = 95;
                $('#sxFeePdfProgressBar').css('width', progress + '%');
                $('#sxFeePdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 300);

        fetch(url, { headers: { 'Accept': 'application/pdf,application/json' } })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().catch(function () { return {}; }).then(function (data) {
                        throw new Error((data && data.error) || ('Server error: ' + response.status));
                    });
                }
                return response.blob();
            })
            .then(function (blob) {
                clearInterval(interval);
                $('#sxFeePdfProgressBar').css('width', '100%');
                $('#sxFeePdfProgressPercent').text('100%');

                setTimeout(function () {
                    var downloadUrl = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = 'Fees_Structure_' + safeName + '_' + new Date().toISOString().slice(0, 10) + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);
                    setTimeout(function () {
                        $('#sxFeePdfProgressModal').modal('hide');
                    }, 500);
                }, 400);
            })
            .catch(function (error) {
                clearInterval(interval);
                $('#sxFeePdfProgressModal').modal('hide');
                showFeePdfError(error.message || 'Failed to generate PDF.');
            });
    }

    document.querySelectorAll('.sx-fee-pdf-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var raw = btn.getAttribute('data-class-ids') || '';
            var ids = raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
            var label = btn.getAttribute('data-label') || 'Fees';
            runFeesStructurePdfExport(ids, label);
        });
    });
});
</script>
@endpush

@push('schema')
@php
    $schoolSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        '@id' => $schoolUrl . '#school',
        'name' => $schoolName,
        'url' => $schoolUrl,
        'image' => $schoolLogoUrl,
        'logo' => $schoolLogoUrl,
        'description' => $seo['description'],
        'telephone' => $school->formattedPhones() ?: null,
        'email' => $school->email ?: null,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $school->address ?: null,
            'addressLocality' => $school->district ?: ($school->ward ?: null),
            'addressRegion' => $school->region ?: null,
            'addressCountry' => 'TZ',
        ]),
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Tanzania',
        ],
    ];

    $webpageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $schoolUrl . '#webpage',
        'url' => $schoolUrl,
        'name' => $seo['title'],
        'description' => $seo['description'],
        'inLanguage' => 'en-TZ',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'ShuleXpert',
            'url' => url('/'),
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => $schoolLogoUrl,
        ],
        'about' => ['@id' => $schoolUrl . '#school'],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Online Application',
                'item' => route('online_application'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $schoolName,
                'item' => $schoolUrl,
            ],
        ],
    ];

    $offerSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Offer',
        'name' => 'Online admission application | ' . $schoolName,
        'url' => $schoolUrl,
        'description' => 'Submit a student admission application online to ' . $schoolName . ' via ShuleXpert.',
        'availability' => 'https://schema.org/InStock',
        'price' => '0',
        'priceCurrency' => 'TZS',
        'seller' => ['@id' => $schoolUrl . '#school'],
        'offeredBy' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'url' => url('/'),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schoolSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($webpageSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($offerSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
