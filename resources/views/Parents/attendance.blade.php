@extends('layouts.parent_corp')

@section('title', __('parent.system_attendance') . ' - ShuleXpert')
@section('page_title', __('parent.system_attendance'))
@section('page_breadcrumb', __('parent.nav_attendance'))

@section('content')
@php
    $monthLabel = \Carbon\Carbon::create((int)$year, (int)$month, 1)->locale(app()->getLocale())->isoFormat('MMMM Y');
@endphp
<style>
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att p,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att span:not(.badge),
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att small,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att strong,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att div,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att h1,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att h2,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att h3,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att h4,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att h5,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att h6,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att .card-title,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att .text-muted,
    html:not([data-sx-theme="dark"]):not([data-bs-theme="dark"]) .sx-parent-att .mini-stat .lbl {
        color: #000 !important;
    }
    .sx-parent-att .btn-primary,
    .sx-parent-att a.btn-primary,
    .sx-parent-att .badge {
        color: #fff !important;
    }
    .sx-parent-att .card,
    .sx-parent-att .panel-card {
        border-radius: 0.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        background: #fff;
        border: 1px solid #e6e6e6;
    }
    .sx-parent-att .panel-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .sx-parent-att .btn-outline-light {
        border-color: #d9d9d9;
        color: #111 !important;
        background: #fff;
    }
    .sx-parent-att .hint-strip {
        border: 1px solid rgba(148, 0, 0, 0.12);
        border-left: 4px solid #940000;
        background: #fff;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .sx-parent-att .mode-select {
        border: 1px solid rgba(148, 0, 0, 0.25);
        font-weight: 700;
        color: #940000;
        min-width: 220px;
    }
    .sx-parent-att a.child-card {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #fff;
        border: 1px solid #e6e6e6;
        border-top: 4px solid #940000;
        border-radius: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        padding: 1.05rem 1.15rem;
        transition: box-shadow 0.15s ease, transform 0.15s ease;
        height: 100%;
    }
    .sx-parent-att a.child-card:hover {
        box-shadow: 0 4px 14px rgba(148, 0, 0, 0.12);
        transform: translateY(-1px);
        color: inherit;
        text-decoration: none;
    }
    .sx-parent-att .child-photo {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #f0d0d0;
        background: #fff5f5;
        color: #940000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex-shrink: 0;
        overflow: hidden;
    }
    .sx-parent-att .child-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sx-parent-att .rate-badge {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 3px solid #940000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.78rem;
        color: #940000 !important;
        flex-shrink: 0;
        background: #fff8f8;
    }
    .sx-parent-att .mini-stat {
        padding: 0.45rem 0.2rem;
        border-top: 1px solid rgba(148, 0, 0, 0.08);
    }
    .sx-parent-att .mini-stat .val {
        font-weight: 800;
        font-size: 0.95rem;
        line-height: 1.1;
    }
    .sx-parent-att .mini-stat .lbl {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        color: #777 !important;
        margin-top: 0.15rem;
    }
    .sx-parent-att .section-label {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #940000 !important;
        margin-bottom: 0.85rem;
    }

    html[data-sx-theme="dark"] .sx-parent-att .card,
    html[data-bs-theme="dark"] .sx-parent-att .card,
    html[data-sx-theme="dark"] .sx-parent-att .panel-card,
    html[data-bs-theme="dark"] .sx-parent-att .panel-card,
    html[data-sx-theme="dark"] .sx-parent-att .hint-strip,
    html[data-bs-theme="dark"] .sx-parent-att .hint-strip,
    html[data-sx-theme="dark"] .sx-parent-att a.child-card,
    html[data-bs-theme="dark"] .sx-parent-att a.child-card {
        background: #24181c !important;
        border-color: rgba(255, 180, 180, 0.16) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-att a.child-card,
    html[data-bs-theme="dark"] .sx-parent-att a.child-card {
        border-top-color: #940000 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-att,
    html[data-bs-theme="dark"] .sx-parent-att,
    html[data-sx-theme="dark"] .sx-parent-att p,
    html[data-bs-theme="dark"] .sx-parent-att p,
    html[data-sx-theme="dark"] .sx-parent-att span:not(.badge),
    html[data-bs-theme="dark"] .sx-parent-att span:not(.badge),
    html[data-sx-theme="dark"] .sx-parent-att small,
    html[data-bs-theme="dark"] .sx-parent-att small,
    html[data-sx-theme="dark"] .sx-parent-att strong,
    html[data-bs-theme="dark"] .sx-parent-att strong,
    html[data-sx-theme="dark"] .sx-parent-att div,
    html[data-bs-theme="dark"] .sx-parent-att div,
    html[data-sx-theme="dark"] .sx-parent-att .text-muted,
    html[data-bs-theme="dark"] .sx-parent-att .text-muted,
    html[data-sx-theme="dark"] .sx-parent-att .mini-stat .lbl,
    html[data-bs-theme="dark"] .sx-parent-att .mini-stat .lbl {
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-att .section-label,
    html[data-bs-theme="dark"] .sx-parent-att .section-label,
    html[data-sx-theme="dark"] .sx-parent-att .rate-badge,
    html[data-bs-theme="dark"] .sx-parent-att .rate-badge {
        color: #ffb4b4 !important;
    }
    html[data-sx-theme="dark"] .sx-parent-att .rate-badge,
    html[data-bs-theme="dark"] .sx-parent-att .rate-badge,
    html[data-sx-theme="dark"] .sx-parent-att .child-photo,
    html[data-bs-theme="dark"] .sx-parent-att .child-photo {
        background: rgba(148, 0, 0, 0.2) !important;
        border-color: rgba(255, 180, 180, 0.28) !important;
    }
    html[data-sx-theme="dark"] .sx-parent-att .btn-outline-light,
    html[data-bs-theme="dark"] .sx-parent-att .btn-outline-light {
        background: transparent !important;
        border-color: rgba(255, 180, 180, 0.35) !important;
        color: #f3e9eb !important;
    }
    html[data-sx-theme="dark"] .sx-parent-att .mini-stat,
    html[data-bs-theme="dark"] .sx-parent-att .mini-stat {
        border-top-color: rgba(255, 180, 180, 0.12);
    }
</style>

<div class="sx-parent-att">
    <div class="row g-3">
        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="pe-2">
                            <h5 class="card-title mb-1">
                                <i class="fi fi-rr-calendar me-1" style="color:#940000;"></i>
                                {{ __('parent.attendance') }}
                            </h5>
                            <p class="text-muted small mb-0">{{ __('parent.attendance_intro') }}</p>
                        </div>
                        <div>
                            <label class="small text-uppercase fw-bold text-muted d-block mb-1">{{ __('parent.attendance_type') }}</label>
                            <select class="form-select form-select-sm mode-select" id="attendanceModeSelect">
                                <option value="{{ route('parentAttendance') }}" selected>{{ __('parent.system_attendance') }}</option>
                                <option value="{{ route('parentFingerprintAttendance') }}">{{ __('parent.fingerprint_attendance') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="hint-strip">
                <i class="fi fi-rr-info me-1" style="color:#940000;"></i>
                {!! __('parent.choose_student_month', ['month' => '<strong>'.e($monthLabel).'</strong>']) !!}
            </div>
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('parentAttendance') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="small text-muted fw-bold text-uppercase mb-1">{{ __('parent.search') }}</label>
                            <input type="text" name="q" class="form-control" placeholder="{{ __('parent.search_child_placeholder') }}" value="{{ $search }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="small text-muted fw-bold text-uppercase mb-1">{{ __('parent.date') }}</label>
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->locale(app()->getLocale())->isoFormat('MMMM') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="small text-muted fw-bold text-uppercase mb-1">{{ __('parent.year') }}</label>
                            <select name="year" class="form-select">
                                @for($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <button type="submit" class="btn btn-primary waves-effect waves-light w-100">
                                <i class="fi fi-rr-search me-1"></i> {{ __('parent.search_btn') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($students->isEmpty())
            <div class="col-12">
                <div class="alert alert-warning mb-0">{{ __('parent.no_students') }}</div>
            </div>
        @elseif(empty($childrenOverview))
            <div class="col-12">
                <div class="alert alert-info mb-0">{{ __('parent.no_search_child') }}</div>
            </div>
        @else
            <div class="col-12">
                <div class="section-label">
                    <i class="fi fi-rr-users me-1"></i>
                    {{ __('parent.your_children') }} ({{ count($childrenOverview) }})
                </div>
            </div>

            @foreach($childrenOverview as $item)
                @php
                    $st = $item['student'];
                    $stats = $item['stats'];
                    $className = trim(($st->subclass->class->class_name ?? '') . ' ' . ($st->subclass->subclass_name ?? ''));
                    $childName = trim(($st->first_name ?? '') . ' ' . ($st->last_name ?? ''));
                    $childInitial = strtoupper(substr($childName !== '' ? $childName : 'S', 0, 1));
                    $url = route('parentStudentAttendance', ['studentID' => $st->studentID, 'month' => $month, 'year' => $year]);
                @endphp
                <div class="col-12 col-lg-6">
                    <a href="{{ $url }}" class="child-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="child-photo">
                                @if($st->photo)
                                    <img src="{{ asset('userImages/' . $st->photo) }}" alt="">
                                @else
                                    {{ $childInitial }}
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-truncate">{{ $childName }}</div>
                                <div class="small text-muted text-truncate">{{ $className ?: __('parent.unknown_class') }}</div>
                                <div class="small text-muted">{{ __('parent.admission') }}: {{ $st->admission_number ?? '-' }}</div>
                            </div>
                            <div class="rate-badge" title="{{ __('parent.rate') }}">{{ $stats['attendance_rate'] }}%</div>
                        </div>
                        <div class="row g-0 mt-3 text-center">
                            <div class="col mini-stat">
                                <div class="val text-success">{{ $stats['present'] }}</div>
                                <div class="lbl">{{ __('parent.present') }}</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val text-danger">{{ $stats['absent'] }}</div>
                                <div class="lbl">{{ __('parent.missed') }}</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val" style="color:#fd7e14 !important;">{{ $stats['sick'] }}</div>
                                <div class="lbl">{{ __('parent.sick') }}</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val text-warning">{{ $stats['permission'] }}</div>
                                <div class="lbl">{{ __('parent.permission') }}</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val text-info">{{ $stats['incomplete'] }}</div>
                                <div class="lbl">{{ __('parent.incomplete') }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('attendanceModeSelect')?.addEventListener('change', function () {
        if (this.value) window.location.href = this.value;
    });
</script>
@endpush
