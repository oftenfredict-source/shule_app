@php
    $skipSplash = true;
    $portalActive = 'applications';
    $seo = [
        'title' => 'Application Submitted | ShuleXpert',
        'description' => 'Your ShuleXpert online school application was submitted successfully.',
        'canonical' => route('online_application'),
        'robots' => 'noindex, follow',
    ];
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-oa-portal section-gap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="sx-apply-success text-center">
                    <div class="sx-apply-success__icon">
                        <i class="fa fa-check"></i>
                    </div>

                    <h2>Application submitted successfully!</h2>
                    <p class="sx-apply-success__lead">
                        Thank you, <strong>{{ $studentName }}</strong>, for applying to
                        <strong>{{ $schoolName }}</strong>: <strong>{{ $className }}</strong>.
                    </p>

                    <div class="sx-apply-credentials">
                        <h4><i class="fa fa-file-text-o"></i> Application reference</h4>
                        <div class="sx-apply-credential-row">
                            <span class="sx-apply-credential-label">
                                <i class="fa fa-hashtag"></i> Application number
                            </span>
                            <span class="sx-apply-credential-value" id="applicationNumber">{{ $applicationNumber }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $applicationNumber }}', this)">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="sx-apply-success__info text-left">
                        <h5><i class="fa fa-info-circle"></i> What's next?</h5>
                        <ul>
                            <li>Your application has been received and is pending review by the school.</li>
                            <li>Track the status anytime from <strong>My Applications</strong> in your online application dashboard.</li>
                            <li>You will receive an SMS notification once your application is reviewed.</li>
                            <li>If accepted, you will receive further instructions via SMS.</li>
                        </ul>
                    </div>

                    <div class="sx-apply-form-actions">
                        @auth('online_application')
                            <a href="{{ route('online_application.portal.applications') }}" class="primary-btn text-uppercase">
                                <i class="fa fa-file-text-o"></i> My Applications
                            </a>
                        @endauth
                        <a href="{{ route('online_application.portal.apply') }}" class="sx-apply-btn-outline">
                            <i class="fa fa-pencil-square-o"></i> Apply to another school
                        </a>
                        <button type="button" onclick="window.print()" class="sx-apply-btn-cancel">
                            <i class="fa fa-print"></i> Print reference
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-check"></i>';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }, function() {
            alert('Failed to copy. Please copy manually: ' + text);
        });
    }
</script>
@endpush
