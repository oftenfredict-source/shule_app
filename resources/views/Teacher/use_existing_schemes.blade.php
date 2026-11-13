@extends('layouts.teacher_corp')

@section('title', __('teacher.scheme.use_existing_title').' - ShuleXpert')
@section('page_title', __('teacher.scheme.use_existing_title'))
@section('page_breadcrumb', __('teacher.scheme.use_existing_bc'))

@section('page_actions')
    <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-outline-primary btn-sm waves-effect">
        <i class="fa-solid fa-arrow-left me-1"></i> {{ __('teacher.scheme.go_back') }}
    </a>
@endsection

@push('styles')
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-use-scheme,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-use-scheme p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-use-scheme small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-use-scheme strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-use-scheme label {
        color: #000 !important;
    }
    .sx-use-scheme .sx-panel {
        background: #fff !important;
        border: 1px solid #e6e6e6 !important;
        border-radius: 0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
        border-top: 4px solid #940000 !important;
        margin-bottom: 0.85rem;
        overflow: hidden;
    }
    .sx-use-scheme .sx-panel-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1.15rem;
        background: linear-gradient(180deg, #fff, #faf7f7);
        border-bottom: 1px solid #e8e0e0;
    }
    .sx-use-scheme .sx-panel-head h4 {
        margin: 0;
        color: #940000 !important;
        font-size: 1.02rem;
        font-weight: 800;
    }
    .sx-use-scheme .sx-panel-head p {
        margin: 0.2rem 0 0;
        color: #666 !important;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .sx-use-scheme .sx-panel-body { padding: 1.1rem 1.15rem 1.25rem; }
    .sx-use-scheme .btn,
    .sx-use-scheme .badge,
    .sx-use-scheme .alert,
    .sx-use-scheme .card { border-radius: 0 !important; }
    .sx-use-scheme .btn-primary-custom {
        background: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        font-weight: 800;
    }
    .sx-use-scheme .btn-primary-custom:hover {
        background: #7b0000 !important;
        border-color: #7b0000 !important;
        color: #fff !important;
    }
    .sx-use-scheme .btn-outline-primary {
        color: #940000 !important;
        border-color: #940000 !important;
        font-weight: 800;
        background: #fff !important;
    }
    .sx-use-scheme .btn-outline-primary:hover {
        background: #940000 !important;
        color: #fff !important;
    }
    .sx-use-scheme .scheme-card {
        height: 100%;
        border: 1px solid #e6e6e6;
        border-top: 3px solid #940000;
        background: #fff;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .sx-use-scheme .scheme-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        border-color: #940000;
    }
    .sx-use-scheme .scheme-card .card-body { padding: 1rem 1.05rem 1.1rem; }
    .sx-use-scheme .scheme-year {
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #940000 !important;
        margin: 0 0 0.65rem;
    }
    .sx-use-scheme .scheme-meta {
        margin: 0 0 0.45rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: #333 !important;
    }
    .sx-use-scheme .scheme-meta span {
        color: #777 !important;
        font-weight: 700;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: block;
        margin-bottom: 0.1rem;
    }
    .sx-use-scheme .scheme-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px solid #f0eaea;
    }
    .sx-use-scheme .sx-empty {
        border: 1px dashed #ddd;
        background: #fafafa;
        padding: 1.35rem;
        text-align: center;
        color: #777 !important;
        font-weight: 600;
    }
    .sx-use-scheme .sx-tag {
        display: inline-block;
        padding: 0.22rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border: 1px solid transparent;
    }
    .sx-use-scheme .sx-tag-active { background: #eef8f0; color: #1b6b2e !important; border-color: #c8e6cf; }
    .sx-use-scheme .sx-tag-draft { background: #fff8e8; color: #9a6b00 !important; border-color: #f0e0b0; }
    .sx-use-scheme .sx-tag-other { background: #f0f0f0; color: #555 !important; border-color: #ddd; }

    /* Dark mode */
    html[data-sx-theme="dark"] .sx-use-scheme .sx-panel,
    html[data-bs-theme="dark"] .sx-use-scheme .sx-panel,
    html[data-sx-theme="dark"] .sx-use-scheme .scheme-card,
    html[data-bs-theme="dark"] .sx-use-scheme .scheme-card {
        background: #24181c !important;
        border-color: #3a2a2e !important;
        box-shadow: none !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .sx-panel-head,
    html[data-bs-theme="dark"] .sx-use-scheme .sx-panel-head {
        background: #1c1417 !important;
        border-bottom-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .sx-panel-head h4,
    html[data-bs-theme="dark"] .sx-use-scheme .sx-panel-head h4,
    html[data-sx-theme="dark"] .sx-use-scheme .scheme-year,
    html[data-bs-theme="dark"] .sx-use-scheme .scheme-year {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .sx-panel-head p,
    html[data-bs-theme="dark"] .sx-use-scheme .sx-panel-head p,
    html[data-sx-theme="dark"] .sx-use-scheme .scheme-meta span,
    html[data-bs-theme="dark"] .sx-use-scheme .scheme-meta span {
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .scheme-meta,
    html[data-bs-theme="dark"] .sx-use-scheme .scheme-meta {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .scheme-actions,
    html[data-bs-theme="dark"] .sx-use-scheme .scheme-actions {
        border-top-color: #3a2a2e !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .sx-empty,
    html[data-bs-theme="dark"] .sx-use-scheme .sx-empty {
        background: #1c1417 !important;
        border-color: #3a2a2e !important;
        color: #d7c4c8 !important;
    }
    html[data-sx-theme="dark"] .sx-use-scheme .btn-outline-primary,
    html[data-bs-theme="dark"] .sx-use-scheme .btn-outline-primary {
        background: transparent !important;
        color: #ffb4b4 !important;
    }
</style>
@endpush

@section('content')
@php
    $subjectName = $classSubject->subject->subject_name ?? 'N/A';
    if ($classSubject->subclass && $classSubject->subclass->class) {
        $classLabel = trim(($classSubject->subclass->class->class_name ?? '') . ' ' . ($classSubject->subclass->subclass_name ?? ''));
    } elseif ($classSubject->class) {
        $classLabel = $classSubject->class->class_name ?? 'N/A';
    } else {
        $classLabel = 'N/A';
    }
@endphp

<div class="sx-use-scheme">
    <div class="sx-panel">
        <div class="sx-panel-head">
            <div>
                <h4><i class="fa fa-copy me-1"></i> {{ __('teacher.scheme.use_existing_title') }}</h4>
                <p>{{ __('teacher.scheme.use_existing_for', ['subject' => $subjectName, 'class' => $classLabel]) }}</p>
            </div>
        </div>
        <div class="sx-panel-body">
            @if($allSchemes->count() > 0)
                <div class="row g-3">
                    @foreach($allSchemes as $scheme)
                        @php
                            $schemeSubject = $scheme->classSubject->subject->subject_name ?? 'N/A';
                            if ($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                                $schemeClass = trim(($scheme->classSubject->subclass->class->class_name ?? '') . ' ' . ($scheme->classSubject->subclass->subclass_name ?? ''));
                            } elseif ($scheme->classSubject->class) {
                                $schemeClass = $scheme->classSubject->class->class_name ?? 'N/A';
                            } else {
                                $schemeClass = 'N/A';
                            }
                            $creator = trim(($scheme->createdBy->first_name ?? '') . ' ' . ($scheme->createdBy->last_name ?? ''));
                            $statusClass = $scheme->status === 'Active' ? 'sx-tag-active' : ($scheme->status === 'Draft' ? 'sx-tag-draft' : 'sx-tag-other');
                        @endphp
                        <div class="col-md-6 col-xl-4">
                            <div class="scheme-card">
                                <div class="card-body">
                                    <div class="scheme-year">
                                        <i class="fa fa-calendar me-1"></i> {{ __('teacher.year') }}: {{ $scheme->year }}
                                    </div>
                                    <p class="scheme-meta"><span>{{ __('teacher.subject') }}</span>{{ $schemeSubject }}</p>
                                    <p class="scheme-meta"><span>{{ __('teacher.class') }}</span>{{ $schemeClass }}</p>
                                    <p class="scheme-meta"><span>{{ __('teacher.scheme.created_by') }}</span>{{ $creator !== '' ? $creator : '—' }}</p>
                                    <p class="scheme-meta">
                                        <span>{{ __('teacher.status') }}</span>
                                        <span class="sx-tag {{ $statusClass }}">{{ $scheme->status }}</span>
                                    </p>
                                    <p class="scheme-meta"><span>{{ __('teacher.scheme.items') }}</span>{{ __('teacher.scheme.rows_count', ['count' => $scheme->items->count()]) }}</p>
                                    <p class="scheme-meta mb-0"><span>{{ __('teacher.scheme.objectives') }}</span>{{ __('teacher.scheme.objectives_count', ['count' => $scheme->learningObjectives->count()]) }}</p>

                                    <div class="scheme-actions">
                                        <a href="{{ route('teacher.viewSchemeOfWork', $scheme->scheme_of_workID) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fa fa-eye me-1"></i> {{ __('teacher.scheme.view') }}
                                        </a>
                                        <button type="button" class="btn btn-primary-custom btn-sm" onclick="useThisScheme({{ $scheme->scheme_of_workID }})">
                                            <i class="fa fa-check me-1"></i> {{ __('teacher.scheme.use_this') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sx-empty">
                    <i class="fa fa-info-circle me-1"></i> {{ __('teacher.scheme.no_existing') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- After #sx-teacher-dark-fix: keep status tags readable --}}
<style>
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .sx-panel-head h4,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .sx-panel-head h4,
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .scheme-year,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .scheme-year {
    color: #ffb4b4 !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .sx-tag.sx-tag-active,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .sx-tag.sx-tag-active {
    background: #eef8f0 !important; color: #1b6b2e !important; -webkit-text-fill-color: #1b6b2e !important;
}
html[data-sx-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .sx-tag.sx-tag-draft,
html[data-bs-theme="dark"] .sx-sa-content-wrap .sx-use-scheme .sx-tag.sx-tag-draft {
    background: #fff8e8 !important; color: #9a6b00 !important; -webkit-text-fill-color: #9a6b00 !important;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    var i18n = @json(__('teacher.scheme'));
    i18n.cancel = @json(__('teacher.cancel'));

    window.useThisScheme = function (schemeID) {
        Swal.fire({
            title: i18n.use_confirm_title,
            text: i18n.use_confirm_text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: i18n.use_confirm_yes,
            cancelButtonText: i18n.cancel
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: i18n.processing,
                text: i18n.please_wait,
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '{{ route("teacher.useThisScheme", ":id") }}'.replace(':id', schemeID),
                method: 'POST',
                data: {
                    class_subjectID: {{ $classSubject->class_subjectID }},
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: i18n.success,
                            text: response.message,
                            confirmButtonColor: '#940000',
                            confirmButtonText: i18n.ok
                        }).then(function () {
                            window.location.href = response.redirect || '{{ route("teacher.schemeOfWork") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: i18n.error,
                            text: response.message,
                            confirmButtonColor: '#940000',
                            confirmButtonText: i18n.ok
                        }).then(function () {
                            if (response.manageUrl) window.location.href = response.manageUrl;
                        });
                    }
                },
                error: function (xhr) {
                    var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : i18n.fail_use;
                    Swal.fire({
                        icon: 'error',
                        title: i18n.error,
                        text: errorMsg,
                        confirmButtonColor: '#940000',
                        confirmButtonText: i18n.ok
                    });
                }
            });
        });
    };
})();
</script>
@endpush
