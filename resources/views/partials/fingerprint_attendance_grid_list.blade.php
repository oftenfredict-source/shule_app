<style>
    .fp-att-view-toggle .btn { min-width: 44px; }
    .fp-att-view-toggle .btn.active {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .fp-att-data-wrapper .fp-att-grid-view { display: none; }
    .fp-att-data-wrapper[data-active-view="list"] .fp-att-list-view { display: block !important; }
    .fp-att-data-wrapper[data-active-view="list"] .fp-att-grid-view { display: none !important; }
    .fp-att-data-wrapper[data-active-view="grid"] .fp-att-list-view { display: none !important; }
    .fp-att-data-wrapper[data-active-view="grid"] .fp-att-grid-view {
        display: flex !important;
        flex-wrap: wrap;
        margin-left: -6px;
        margin-right: -6px;
    }
    .fp-att-data-wrapper[data-active-view="grid"] .fp-att-grid-view > [class*="col-"] {
        padding-left: 6px;
        padding-right: 6px;
    }
    .fp-att-grid-card {
        border: 1px solid #e8d4d4;
        background: #fff;
        height: 100%;
        padding: 12px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(148, 0, 0, 0.06);
    }
    .fp-att-grid-card-new { border-left: 3px solid #198754; }
    .fp-att-grid-card-warn { border-left: 3px solid #ffc107; }
    .fp-att-grid-head { margin-bottom: 6px; }
    .fp-att-grid-num { font-size: 0.72rem; color: #999; font-weight: 700; }
    .fp-att-grid-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.35;
        word-break: break-word;
    }
    .fp-att-grid-body { border-top: 1px solid #f0f0f0; padding-top: 6px; }
    .fp-att-grid-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.8rem;
        padding: 5px 0;
        border-bottom: 1px dashed #f3f3f3;
    }
    .fp-att-grid-meta:last-child { border-bottom: none; }
    .fp-att-grid-label { color: #888; flex-shrink: 0; max-width: 45%; }
    .fp-att-grid-val { text-align: right; color: #333; word-break: break-word; }
    .fp-att-grid-empty {
        border: 1px dashed #dee2e6;
        padding: 1.5rem;
        text-align: center;
        color: #6c757d;
        width: 100%;
        border-radius: 6px;
    }
    @media (max-width: 767.98px) {
        .fp-att-view-toggle { width: 100%; }
        .fp-att-view-toggle .btn { flex: 1; }
    }
</style>
<script>
(function ($) {
    if (window.FpAttView) return;

    function fpAttIsMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function fpAttDefaultView() {
        return fpAttIsMobile() ? 'grid' : 'list';
    }

    function fpAttPrefKey(tableId) {
        return 'fp_att_view_' + (fpAttIsMobile() ? 'm' : 'd') + '_' + tableId;
    }

    function fpAttUserPrefKey(tableId) {
        return fpAttPrefKey(tableId) + '_user';
    }

    function fpAttHasUserPref(tableId) {
        try { return localStorage.getItem(fpAttUserPrefKey(tableId)) === '1'; } catch (e) { return false; }
    }

    function fpAttEsc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function fpAttGetView(tableId) {
        if (fpAttHasUserPref(tableId)) {
            try {
                var saved = localStorage.getItem(fpAttPrefKey(tableId));
                if (saved === 'list' || saved === 'grid') return saved;
            } catch (e) {}
        }
        return fpAttDefaultView();
    }

    function fpAttTitleIndex(headers) {
        for (var i = 0; i < headers.length; i++) {
            if (/^name$/i.test(String(headers[i]).trim())) return i;
        }
        if (headers.length > 2) return headers[0] === '#' ? 2 : 1;
        return headers.length > 1 ? 1 : 0;
    }

    function fpAttBuildGrid(tableId) {
        var $wrapper = $('.fp-att-data-wrapper[data-table-id="' + tableId + '"]');
        var $table = $('#' + tableId);
        var $grid = $wrapper.find('.fp-att-grid-view').empty();
        if (!$table.length) return;

        var headers = [];
        $table.find('thead th').each(function () {
            headers.push($(this).text().trim());
        });

        var rows = [];
        if ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
            $table.DataTable().rows({ search: 'applied' }).every(function () {
                rows.push($(this.node()));
            });
        } else {
            $table.find('tbody tr').each(function () {
                rows.push($(this));
            });
        }

        if (!rows.length) {
            $grid.append('<div class="col-12"><div class="fp-att-grid-empty">No data available.</div></div>');
            return;
        }

        var titleIdx = fpAttTitleIndex(headers);

        rows.forEach(function ($tr, idx) {
            var $cells = $tr.find('td');
            if (!$cells.length) return;

            var title = $cells.eq(titleIdx).text().trim() || ('Record ' + (idx + 1));
            var num = $cells.eq(0).text().trim() || String(idx + 1);
            var meta = '';

            $cells.each(function (i) {
                if (i === 0 || i === titleIdx) return;
                var label = headers[i] || '';
                if (!label || label === '#') return;
                meta += '<div class="fp-att-grid-meta"><span class="fp-att-grid-label">' + fpAttEsc(label) + '</span><span class="fp-att-grid-val">' + $(this).html() + '</span></div>';
            });

            var cardClass = '';
            if ($tr.hasClass('table-success')) cardClass = ' fp-att-grid-card-new';
            else if ($tr.hasClass('table-warning')) cardClass = ' fp-att-grid-card-warn';

            $grid.append(
                '<div class="col-12 col-sm-6 col-lg-4 mb-2">' +
                '<div class="fp-att-grid-card' + cardClass + '">' +
                '<div class="fp-att-grid-head"><span class="fp-att-grid-num">#' + fpAttEsc(num) + '</span></div>' +
                '<div class="fp-att-grid-title">' + fpAttEsc(title) + '</div>' +
                (meta ? '<div class="fp-att-grid-body">' + meta + '</div>' : '') +
                '</div></div>'
            );
        });
    }

    function fpAttSetView(tableId, view, savePref) {
        var $wrapper = $('.fp-att-data-wrapper[data-table-id="' + tableId + '"]');
        if (!$wrapper.length) return;

        if (view === 'grid') fpAttBuildGrid(tableId);

        $wrapper.attr('data-active-view', view);
        $wrapper.find('.fp-att-view-toggle button').removeClass('active');
        $wrapper.find('.fp-att-view-toggle button[data-view="' + view + '"]').addClass('active');

        if (savePref) {
            try {
                localStorage.setItem(fpAttPrefKey(tableId), view);
                localStorage.setItem(fpAttUserPrefKey(tableId), '1');
            } catch (e) {}
        }
    }

    function fpAttToggleHtml(tableId) {
        return '<div class="d-flex justify-content-end mb-2">' +
            '<div class="btn-group btn-group-sm fp-att-view-toggle" data-table="' + tableId + '">' +
            '<button type="button" class="btn btn-outline-secondary" data-view="list" title="List view"><i class="bi bi-list-ul"></i><span class="d-none d-sm-inline ms-1">List</span></button>' +
            '<button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view"><i class="bi bi-grid-3x3-gap"></i><span class="d-none d-sm-inline ms-1">Grid</span></button>' +
            '</div></div>';
    }

    window.FpAttView = {
        mount: function (container, tableHtml, tableId, footerHtml) {
            footerHtml = footerHtml || '';
            var html = '<div class="fp-att-data-wrapper" data-table-id="' + tableId + '">' +
                fpAttToggleHtml(tableId) +
                '<div class="fp-att-list-view">' + tableHtml + '</div>' +
                '<div class="row fp-att-grid-view"></div>' +
                (footerHtml ? '<div class="fp-att-table-footer">' + footerHtml + '</div>' : '') +
                '</div>';
            $(container).html(html);
            fpAttSetView(tableId, fpAttGetView(tableId), false);
        },
        refreshGrid: function (tableId) {
            var $wrapper = $('.fp-att-data-wrapper[data-table-id="' + tableId + '"]');
            if ($wrapper.attr('data-active-view') === 'grid') fpAttBuildGrid(tableId);
        },
        bindDataTable: function (tableId, dt) {
            if (!dt) return;
            dt.on('draw', function () {
                window.FpAttView.refreshGrid(tableId);
            });
        }
    };

    $(document).on('click', '.fp-att-view-toggle button', function () {
        fpAttSetView($(this).closest('.fp-att-view-toggle').data('table'), $(this).data('view'), true);
    });

    var fpAttResizeTimer;
    $(window).on('resize', function () {
        clearTimeout(fpAttResizeTimer);
        fpAttResizeTimer = setTimeout(function () {
            $('.fp-att-data-wrapper').each(function () {
                var tableId = $(this).data('table-id');
                if (!fpAttHasUserPref(tableId)) {
                    fpAttSetView(tableId, fpAttDefaultView(), false);
                } else if ($(this).attr('data-active-view') === 'grid') {
                    fpAttBuildGrid(tableId);
                }
            });
        }, 250);
    });
})(jQuery);
</script>
