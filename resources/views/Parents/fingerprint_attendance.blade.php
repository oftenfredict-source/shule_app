@extends('layouts.parent_corp')

@section('title', 'Mahudhurio ya Alama za Vidole - ShuleXpert')

@section('content')
@php
    $monthLabel = \Carbon\Carbon::create((int)$year, (int)$month, 1)->locale('sw')->isoFormat('MMMM Y');
@endphp
<style>
    .att-hero {
        background: white;
        padding: 22px;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 22px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 16px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .mode-select {
        border: 1px solid rgba(148,0,0,0.25);
        font-weight: 700;
        color: #940000;
        max-width: 280px;
    }
    .child-card {
        background: #fff;
        border-top: 3px solid #940000;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        padding: 18px;
        margin-bottom: 14px;
        transition: transform .15s ease, box-shadow .15s ease;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    a.child-card {
        cursor: pointer;
    }
    a.child-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(148,0,0,0.12);
        color: inherit;
        text-decoration: none;
    }
    .child-card.disabled-card {
        opacity: 0.55;
        filter: grayscale(0.35);
        cursor: not-allowed;
        border-top-color: #aaa;
        pointer-events: none;
        position: relative;
    }
    .child-card.disabled-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.35);
    }
    .disabled-banner {
        background: #f8f9fa;
        border: 1px dashed #ccc;
        color: #666;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 10px;
        margin-top: 12px;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .child-photo {
        width: 58px;
        height: 58px;
        object-fit: cover;
        border: 2px solid #eee;
    }
    .rate-badge {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 3px solid #940000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
        color: #940000;
        flex-shrink: 0;
    }
    .mini-stat .val { font-weight: 800; font-size: 15px; line-height: 1; }
    .mini-stat .lbl {
        font-size: 9px;
        text-transform: uppercase;
        color: #888;
        font-weight: 700;
        letter-spacing: .3px;
    }
    .hint-box {
        background: #fff5f5;
        border: 1px dashed rgba(148,0,0,0.25);
        padding: 14px 16px;
        font-size: 13px;
        color: #555;
        margin-bottom: 18px;
    }
    @media (max-width: 576px) {
        .child-card { padding: 14px; }
        .rate-badge { width: 48px; height: 48px; font-size: 11px; }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="att-hero">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="section-title mb-2" style="border: none; padding: 0;">
                        <i class="bx bx-fingerprint"></i> Mahudhurio ya Alama za Vidole
                    </div>
                    <p class="text-muted small mb-0">Chagua mtoto kuona mahudhurio yake kulingana na alama za vidole. Ruhusa na wagonjwa zinaangaliwa kutoka kwenye maombi yaliyoidhinishwa.</p>
                </div>
                <div>
                    <label class="small text-uppercase fw-bold text-muted d-block mb-1">Aina ya Mahudhurio</label>
                    <select class="form-select form-select-sm mode-select" id="attendanceModeSelect">
                        <option value="{{ route('parentAttendance') }}">Mahudhurio ya Mfumo</option>
                        <option value="{{ route('parentFingerprintAttendance') }}" selected>Alama za Vidole (Fingerprint)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="col-12">
            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
        </div>
    @endif

    <div class="col-12">
        <div class="hint-box">
            <i class="bx bx-info-circle text-danger"></i>
            <strong>Chagua mwanafunzi</strong> kuona mahudhurio yake kwa kalenda ya mwezi (<strong>{{ $monthLabel }}</strong>).
            Watoto walio kwenye madarasa yasiyoruhusiwa wataonekana kwa kivuli.
        </div>
    </div>

    <div class="col-12">
        <form method="GET" action="{{ route('parentFingerprintAttendance') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="Tafuta mtoto kwa jina au namba ya usajili..." value="{{ $search }}">
            </div>
            <div class="col-md-3 col-6">
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('sw')->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 col-6">
                <select name="year" class="form-select">
                    @for($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 col-12">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i> Tafuta</button>
            </div>
        </form>
    </div>

    @if($students->isEmpty())
        <div class="col-12">
            <div class="alert alert-warning mb-0">Hakuna watoto waliosajiliwa kwenye akaunti yako.</div>
        </div>
    @elseif(empty($childrenOverview))
        <div class="col-12">
            <div class="alert alert-info mb-0">Hakuna mtoto aliyepatikana kwa utafutaji wako.</div>
        </div>
    @else
        <div class="col-12">
            <div class="section-title"><i class="bx bx-group"></i> Watoto Wako ({{ count($childrenOverview) }})</div>
        </div>

        @foreach($childrenOverview as $item)
            @php
                $st = $item['student'];
                $enabled = $item['enabled'];
                $stats = $item['stats'];
                $className = trim(($st->subclass->class->class_name ?? '') . ' ' . ($st->subclass->subclass_name ?? ''));
                $url = route('parentStudentFingerprintAttendance', ['studentID' => $st->studentID, 'month' => $month, 'year' => $year]);
            @endphp
            <div class="col-12 col-lg-6">
                @if($enabled)
                    <a href="{{ $url }}" class="child-card">
                        <div class="d-flex align-items-center gap-3">
                            @if($st->photo)
                                <img src="{{ asset('userImages/' . $st->photo) }}" class="child-photo" alt="">
                            @else
                                <div class="child-photo d-flex align-items-center justify-content-center bg-light">
                                    <i class="bx bx-user"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-bold text-truncate">{{ $st->first_name }} {{ $st->last_name }}</div>
                                <div class="small text-muted text-truncate">{{ $className ?: 'Darasa haijulikani' }}</div>
                                <div class="small text-muted">Usajili: {{ $st->admission_number ?? '—' }}</div>
                            </div>
                            <div class="rate-badge">{{ $stats['attendance_rate'] }}%</div>
                        </div>
                        <div class="row g-1 mt-3 text-center">
                            <div class="col mini-stat">
                                <div class="val text-success">{{ $stats['present'] }}</div>
                                <div class="lbl">Alikuwepo</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val text-danger">{{ $stats['absent'] }}</div>
                                <div class="lbl">Hayupo</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val" style="color:#fd7e14;">{{ $stats['sick'] }}</div>
                                <div class="lbl">Mgonjwa</div>
                            </div>
                            <div class="col mini-stat">
                                <div class="val text-warning">{{ $stats['permission'] }}</div>
                                <div class="lbl">Ruhusa</div>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="child-card disabled-card">
                        <div class="d-flex align-items-center gap-3">
                            @if($st->photo)
                                <img src="{{ asset('userImages/' . $st->photo) }}" class="child-photo" alt="">
                            @else
                                <div class="child-photo d-flex align-items-center justify-content-center bg-light">
                                    <i class="bx bx-user"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-bold text-truncate">{{ $st->first_name }} {{ $st->last_name }}</div>
                                <div class="small text-muted text-truncate">{{ $className ?: 'Darasa haijulikani' }}</div>
                                <div class="small text-muted">Usajili: {{ $st->admission_number ?? '—' }}</div>
                            </div>
                            <div class="rate-badge" style="border-color:#aaa; color:#888;">—</div>
                        </div>
                        <div class="disabled-banner">
                            <i class="bx bx-lock-alt"></i>
                            Mahudhurio kwa alama za vidole hayajaruhusiwa kwa darasa hili
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('attendanceModeSelect')?.addEventListener('change', function () {
        if (this.value) window.location.href = this.value;
    });
</script>
@endpush
