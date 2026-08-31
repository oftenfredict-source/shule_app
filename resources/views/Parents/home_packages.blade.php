@extends('layouts.parent_corp')

@section('title', 'Home Package - ShuleXpert')

@section('content')
<style>
    .hp-section-title {
        font-size: 14px;
        font-weight: 800;
        color: #940000;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        border-bottom: 2px solid rgba(148,0,0,0.1);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .hp-holiday-card {
        background: #fff;
        border-top: 4px solid #940000;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 16px;
        padding: 18px 20px;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .hp-holiday-card:hover { background: #fff8f8; }
    .hp-holiday-name { font-weight: 800; color: #333; font-size: 16px; }
    .hp-meta { font-size: 12px; color: #777; }
</style>

<div class="row">
    <div class="col-12">
        <div class="hp-section-title">
            <i class="bx bx-home-smile"></i> Home Package (Masomo ya Nyumbani)
        </div>
        <p class="text-muted mb-4">
            Chagua holiday ili kuona home packages za <strong>masomo yote ya mtoto wako</strong>.
        </p>
    </div>
</div>

<div id="hpHolidayList">
@forelse($holidays as $holiday)
    <div class="hp-holiday-card parent-hp-holiday"
         data-holiday-id="{{ $holiday->holidayID }}"
         data-holiday-name="{{ $holiday->holiday_name }}">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="hp-holiday-name">
                    <i class="bx bx-calendar-event"></i> {{ $holiday->holiday_name }}
                </div>
                <div class="hp-meta">
                    {{ optional($holiday->start_date)->format('d/m/Y') }}, {{ optional($holiday->end_date)->format('d/m/Y') }}
                </div>
            </div>
            <i class="bx bx-chevron-right" style="font-size:22px;color:#940000;"></i>
        </div>
    </div>
@empty
    <div class="alert alert-light border">
        Hakuna home package iliyopakiwa kwa masomo ya mtoto wako katika academic year iliyochaguliwa.
    </div>
@endforelse
</div>

{{-- Holiday packages modal --}}
<div class="modal fade" id="parentHpHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0" id="parentHpHolidayTitle">Home Packages</h5>
                    <small id="parentHpHolidayDates" class="opacity-75"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="parentHpHolidayBody">
                <div class="text-center text-muted py-4">Inapakia...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Funga</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function($) {
    const holidayPackagesUrl = @json(url('parentHomePackages'));

    $(document).on('click', '.parent-hp-holiday', function() {
        const id = $(this).data('holiday-id');
        const name = $(this).data('holiday-name');
        $('#parentHpHolidayTitle').text(name || 'Home Packages');
        $('#parentHpHolidayDates').text('');
        $('#parentHpHolidayBody').html('<div class="text-center text-muted py-4">Inapakia...</div>');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('parentHpHolidayModal')).show();

        $.get(holidayPackagesUrl + '/' + id + '/packages')
            .done(function(res) {
                if (res.holiday && res.holiday.dates) {
                    $('#parentHpHolidayDates').text(res.holiday.dates);
                }
                const rows = res.packages || [];
                if (!rows.length) {
                    $('#parentHpHolidayBody').html('<div class="alert alert-light border mb-0">Hakuna package kwa holiday hii.</div>');
                    return;
                }

                let html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr>'
                    + '<th>Somo</th><th>Kichwa / Faili</th><th>Mwalimu</th><th>Vitendo</th>'
                    + '</tr></thead><tbody>';
                rows.forEach(function(p) {
                    html += '<tr>'
                        + '<td><strong>' + (p.subject_name || '') + '</strong>'
                        + (p.subject_code ? '<br><small class="text-muted">' + p.subject_code + '</small>' : '')
                        + '<br><small class="text-muted">' + (p.class_label || '') + '</small></td>'
                        + '<td>' + (p.title || p.original_filename || '') + '<br><small class="text-muted">' + (p.original_filename || '') + '</small></td>'
                        + '<td>' + (p.teacher_name || '-') + '</td>'
                        + '<td class="text-nowrap">'
                        + '<button type="button" class="btn btn-sm btn-outline-primary parent-hp-view" data-url="' + p.view_url + '" data-dl="' + p.download_url + '" data-title="' + (p.subject_name || 'Home Package') + '"><i class="bx bx-show"></i> View</button> '
                        + '<a class="btn btn-sm btn-primary" href="' + p.download_url + '"><i class="bx bx-download"></i> Download</a>'
                        + '</td></tr>';
                });
                html += '</tbody></table></div>';
                $('#parentHpHolidayBody').html(html);
            })
            .fail(function() {
                $('#parentHpHolidayBody').html('<div class="alert alert-danger mb-0">Imeshindikana kupakia.</div>');
            });
    });

    $(document).on('click', '.parent-hp-view', function() {
        const url = $(this).data('url');
        const dl = $(this).data('dl');
        const title = $(this).data('title') || 'Home Package';
        if (window.openParentHomePackageView) {
            window.openParentHomePackageView(url, dl, title);
        }
    });
})(jQuery);
</script>
@endpush
