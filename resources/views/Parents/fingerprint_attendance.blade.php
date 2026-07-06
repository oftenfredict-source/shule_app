@extends('layouts.parent_corp')

@section('title', 'Mahudhurio - Alama za Vidole')

@section('content')
<style>
    .coming-soon-card {
        background: white;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        padding: 50px 30px;
        text-align: center;
        max-width: 640px;
        margin: 40px auto;
    }
    .coming-soon-icon {
        font-size: 72px;
        color: #940000;
        opacity: 0.25;
        margin-bottom: 20px;
    }
    .child-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff5f5;
        border: 1px solid rgba(148,0,0,0.15);
        padding: 8px 14px;
        margin: 4px;
        font-size: 13px;
        font-weight: 600;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 mb-4" style="border-bottom: 2px solid rgba(148,0,0,0.1); padding-bottom: 12px;">
            <i class="bx bx-fingerprint" style="font-size: 22px; color: #940000;"></i>
            <div>
                <h5 class="mb-0" style="font-weight: 800; color: #940000; text-transform: uppercase; letter-spacing: 1px;">Mahudhurio ya Alama za Vidole</h5>
                <small class="text-muted">Fingerprint Attendance — Inakuja Hivi Karibuni</small>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="coming-soon-card">
            <div class="coming-soon-icon"><i class="bx bx-fingerprint"></i></div>
            <h4 style="font-weight: 800; color: #333;">Kipengele Hiki Kinakuja Hivi Karibuni</h4>
            <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                Utaweza kuona mahudhurio ya watoto wako kupitia kifaa cha alama za vidole (check-in / check-out) moja kwa moja kutoka hapa.
            </p>
            <span class="badge bg-secondary text-uppercase mb-4" style="font-size: 11px; padding: 8px 16px; letter-spacing: 1px;">Bado Haijawekwa</span>

            @if($students->count() > 0)
                <div class="mt-3">
                    <p class="small text-muted text-uppercase font-weight-bold mb-2">Watoto Wako</p>
                    @foreach($students as $student)
                        <span class="child-pill">
                            <i class="bx bx-user"></i>
                            {{ $student->first_name }} {{ $student->last_name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 pt-3" style="border-top: 1px solid #eee;">
                <p class="small text-muted mb-3">Kwa sasa, tumia mahudhurio ya mfumo (walimu waliorekodi darasani):</p>
                <a href="{{ route('parentAttendance') }}" class="btn btn-primary px-4">
                    <i class="bx bx-clipboard"></i> Angalia Mahudhurio ya Mfumo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
