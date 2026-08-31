@php
    $skipSplash = true;
    $editing = $editing ?? false;
    $admission = $admission ?? null;
    $portalActive = $editing ? 'applications' : 'apply';
    $seo = [
        'title' => ($editing ? 'Edit Application' : 'Student Application') . ', ShuleXpert',
        'description' => 'Complete your online school application with ShuleXpert.',
        'keywords' => 'ShuleXpert application form, school admission',
        'canonical' => $editing
            ? route('online_application.portal.applications.edit', $admission->id)
            : route('online_application.apply'),
        'robots' => 'noindex, follow',
    ];
    $formSections = $formSections ?? collect();
    $totalSteps = max(1, $formSections->count());
@endphp
@extends('layouts.online_application_portal')

@section('content')
<section class="sx-oa-portal sx-apply-page section-gap">
    <div class="container">
        <div class="mb-4">
            <h2 class="mb-1">{{ $editing ? 'Edit Application' : 'Student Application' }}</h2>
            <p class="text-muted mb-0">
                {{ $school->school_name ?? 'School' }}
                @if(!empty($class))
                   , {{ $class->class_name ?? '' }}
                @endif
                @if($editing && $admission)
                    <br><small>Ref: {{ $admission->application_number }}</small>
                @endif
            </p>
        </div>
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show sx-apply-alert" role="alert">
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger sx-apply-alert">
                <i class="fa fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="sx-apply-form-section">
            <h3><i class="fa fa-university"></i> Application details</h3>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="sx-apply-label">School</label>
                    <div class="sx-apply-value">{{ $school->school_name }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="sx-apply-label">Class</label>
                    <div class="sx-apply-value">{{ $class->class_name }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="sx-apply-label">Available spaces</label>
                    <div class="sx-apply-value sx-apply-value--success">{{ $availableSpaces }} space(s)</div>
                </div>
            </div>

            @if(!empty($academicYearName))
                <div class="mt-2 text-muted small">
                    Active academic year: <strong>{{ $academicYearName }}</strong>
                </div>
            @endif
        </div>

        @if($formSections->isEmpty())
            <div class="alert alert-warning">This school has not configured an admission form yet.</div>
        @else
            <nav class="sx-wizard" id="sxStepChain" aria-label="Application steps">
                <div class="sx-wizard__meta">
                    <span class="sx-wizard__meta-label" id="sxWizardStatus">Step 1 of {{ $totalSteps }}</span>
                    <span class="sx-wizard__meta-pct" id="sxWizardPct">0%</span>
                </div>
                <div class="sx-wizard__track" aria-hidden="true">
                    <div class="sx-wizard__fill" id="sxWizardFill" style="width: 0%;"></div>
                </div>
                <div class="sx-wizard__steps-wrap">
                    <ol class="sx-wizard__steps">
                        @foreach($formSections as $i => $section)
                            <li class="sx-wizard__step {{ $i === 0 ? 'is-active' : '' }}" data-step-index="{{ $i }}">
                                <button type="button" class="sx-wizard__btn" data-goto-step="{{ $i }}" aria-current="{{ $i === 0 ? 'step' : 'false' }}">
                                    <span class="sx-wizard__num">
                                        <span class="sx-wizard__num-text">{{ $i + 1 }}</span>
                                        <i class="fa fa-check sx-wizard__check" aria-hidden="true"></i>
                                    </span>
                                    <span class="sx-wizard__label">{{ $section->title }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ol>
                </div>
                <p class="sx-wizard__current" id="sxWizardCurrent">{{ $formSections->first()->title ?? '' }}</p>
            </nav>

            <form action="{{ $editing ? route('online_application.portal.applications.update', $admission->id) : route('online_application.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="sxApplyWizardForm"
                  data-editing="{{ $editing ? '1' : '0' }}">
                @csrf
                @if(!$editing)
                    <input type="hidden" name="apply_token" value="{{ $token }}">
                @endif

                @foreach($formSections as $i => $section)
                    <div class="sx-apply-form-section sx-apply-step {{ $i === 0 ? 'is-visible' : '' }}"
                         data-step="{{ $i }}"
                         data-step-title="{{ $section->title }}">
                        <h3>
                            <span class="sx-step-badge">Step {{ $i + 1 }}/{{ $totalSteps }}</span>
                            <span class="sx-step-heading">{{ $section->title }}</span>
                        </h3>
                        @if($section->description)
                            <p class="text-muted mb-3">{{ $section->description }}</p>
                        @endif
                        <div class="row">
                            @foreach($section->fields as $field)
                                @include('partials.online_admission_form_field', [
                                    'field' => $field,
                                    'preview' => false,
                                    'admission' => $admission,
                                    'editing' => $editing,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="sx-apply-form-actions text-center d-flex flex-wrap justify-content-center align-items-center" style="gap:12px;">
                    <button type="button" class="btn btn-outline-secondary" id="sxStepPrev" style="display:none;">
                        <i class="fa fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="primary-btn text-uppercase" id="sxStepNext">
                        Save &amp; Next <i class="fa fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="primary-btn text-uppercase" id="sxStepSubmit" style="display:none;">
                        <span class="oa-btn-label">
                            <i class="fa fa-{{ $editing ? 'save' : 'paper-plane' }}"></i>
                            {{ $editing ? 'Update application' : 'Submit application' }}
                        </span>
                        <span class="oa-btn-loading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i>
                            {{ $editing ? 'Updating...' : 'Submitting...' }}
                        </span>
                    </button>
                    <a href="{{ $editing ? route('online_application.portal.applications') : route('online_application.portal.apply') }}" class="sx-apply-btn-cancel">Cancel</a>
                </div>
            </form>
        @endif
    </div>
</section>

@if($formSections->isNotEmpty())
@push('scripts')
@include('online_application.partials.tz-phone-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    var current = 0;
    var steps = Array.prototype.slice.call(document.querySelectorAll('.sx-apply-step'));
    var chainItems = Array.prototype.slice.call(document.querySelectorAll('#sxStepChain .sx-wizard__step'));
    var prevBtn = document.getElementById('sxStepPrev');
    var nextBtn = document.getElementById('sxStepNext');
    var submitBtn = document.getElementById('sxStepSubmit');
    var statusEl = document.getElementById('sxWizardStatus');
    var pctEl = document.getElementById('sxWizardPct');
    var fillEl = document.getElementById('sxWizardFill');
    var currentEl = document.getElementById('sxWizardCurrent');
    var total = steps.length;
    var submitting = false;

    function showStep(index, scroll) {
        current = Math.max(0, Math.min(index, total - 1));
        steps.forEach(function (el, i) {
            el.classList.toggle('is-visible', i === current);
        });
        chainItems.forEach(function (el, i) {
            el.classList.toggle('is-active', i === current);
            el.classList.toggle('is-done', i < current);
            var btn = el.querySelector('.sx-wizard__btn');
            if (btn) {
                btn.setAttribute('aria-current', i === current ? 'step' : 'false');
            }
        });
        var pct = total <= 1 ? 100 : Math.round((current / (total - 1)) * 100);
        if (current === 0) pct = 0;
        if (statusEl) statusEl.textContent = 'Step ' + (current + 1) + ' of ' + total;
        if (pctEl) pctEl.textContent = pct + '%';
        if (fillEl) fillEl.style.width = pct + '%';
        if (currentEl && steps[current]) {
            currentEl.textContent = steps[current].getAttribute('data-step-title') || '';
        }
        if (prevBtn) prevBtn.style.display = current > 0 ? '' : 'none';
        if (nextBtn) nextBtn.style.display = current < total - 1 ? '' : 'none';
        if (submitBtn) submitBtn.style.display = current === total - 1 ? '' : 'none';

        var activeStep = chainItems[current];
        var stepsRail = document.querySelector('#sxStepChain .sx-wizard__steps');
        if (activeStep && stepsRail) {
            try {
                var railRect = stepsRail.getBoundingClientRect();
                var stepRect = activeStep.getBoundingClientRect();
                var delta = (stepRect.left + stepRect.width / 2) - (railRect.left + railRect.width / 2);
                stepsRail.scrollBy({ left: delta, behavior: 'smooth' });
            } catch (e) {
                try {
                    activeStep.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } catch (e2) {}
            }
        }
        if (scroll !== false) {
            try {
                var wizard = document.getElementById('sxStepChain');
                if (wizard) wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (e) {}
        }
    }

    function validateCurrentStep() {
        var step = steps[current];
        if (!step) return true;
        var fields = step.querySelectorAll('input, select, textarea');
        var maxBytes = 10 * 1024 * 1024;
        var allowedExt = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'webp'];
        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            if (field.type === 'file' && field.files && field.files[0]) {
                var f = field.files[0];
                if (f.size > maxBytes) {
                    alert('File "' + f.name + '" exceeds 10MB limit.');
                    field.focus();
                    return false;
                }
                var ext = (f.name.split('.').pop() || '').toLowerCase();
                if (allowedExt.indexOf(ext) === -1) {
                    alert('File type not allowed. Use: PDF, DOC, DOCX, PNG, JPG, JPEG, WEBP.');
                    field.focus();
                    return false;
                }
            }
            if (typeof field.checkValidity === 'function' && !field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    function setSubmitLoading(on) {
        submitting = on;
        if (!submitBtn) return;
        submitBtn.disabled = on;
        var label = submitBtn.querySelector('.oa-btn-label');
        var loading = submitBtn.querySelector('.oa-btn-loading');
        if (label) label.style.display = on ? 'none' : 'inline';
        if (loading) loading.style.display = on ? 'inline' : 'none';
        if (prevBtn) prevBtn.disabled = on;
        if (nextBtn) nextBtn.disabled = on;
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (!validateCurrentStep()) return;
            showStep(current + 1);
        });
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            showStep(current - 1);
        });
    }

    document.querySelectorAll('[data-goto-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = parseInt(btn.getAttribute('data-goto-step'), 10);
            if (isNaN(target) || target === current) return;
            if (target < current) {
                showStep(target);
                return;
            }
            if (target === current + 1) {
                if (!validateCurrentStep()) return;
                showStep(target);
            }
        });
    });

    var form = document.getElementById('sxApplyWizardForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (submitting) return;
            if (!validateCurrentStep()) return;

            setSubmitLoading(true);
            var csrf = document.querySelector('meta[name="csrf-token"]')
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                : '';
            var fd = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: fd
            })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                }).catch(function () {
                    return { ok: false, status: res.status, data: { message: 'Unexpected server response.' } };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success && result.data.redirect) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: result.data.message || 'Success',
                            timer: 1400,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.href = result.data.redirect;
                        });
                    } else {
                        window.location.href = result.data.redirect;
                    }
                    return;
                }

                setSubmitLoading(false);
                var message = (result.data && result.data.message) || 'Submission failed. Please try again.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Could not submit', text: message });
                } else {
                    alert(message);
                }
            })
            .catch(function () {
                setSubmitLoading(false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
                } else {
                    alert('Network error. Please try again.');
                }
            });
        });
    }

    showStep(0, false);
})();
</script>
@endpush
@endif
@endsection
