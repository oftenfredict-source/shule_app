<script>
(function ($) {
    if (!$ || !$.fn.DataTable) return;

    var csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '';

    var MOBILE_MAX = 991;

    function isMobileView() {
        try {
            return window.matchMedia('(max-width: ' + MOBILE_MAX + 'px)').matches;
        } catch (e) {
            return window.innerWidth <= MOBILE_MAX;
        }
    }

    function storageKeys(prefix) {
        return {
            m: prefix + '_view_m_v2',
            d: prefix + '_view_d_v2'
        };
    }

    function defaultView() {
        return isMobileView() ? 'grid' : 'list';
    }

    function resolveView(prefix) {
        var keys = storageKeys(prefix);
        try {
            localStorage.removeItem(prefix + '_view_m');
            localStorage.removeItem(prefix + '_view_d');
            var key = isMobileView() ? keys.m : keys.d;
            var saved = localStorage.getItem(key);
            if (saved === 'grid' || saved === 'list') return saved;
        } catch (e) {}
        return defaultView();
    }

    function persistView(prefix, view) {
        var keys = storageKeys(prefix);
        try {
            localStorage.setItem(isMobileView() ? keys.m : keys.d, view);
        } catch (e) {}
    }

    function syncToggleButtons($scope, view) {
        $scope.find('[data-sx-oa-view]').each(function () {
            var active = $(this).attr('data-sx-oa-view') === view;
            $(this).toggleClass('is-active', active);
            $(this).attr('aria-pressed', active ? 'true' : 'false');
        });
    }

    function applyView($root, view) {
        $root.attr('data-view', view === 'grid' ? 'grid' : 'list');
        var $panel = $root.closest('.sx-oa-panel');
        syncToggleButtons($panel.length ? $panel : $root.parent(), view);
    }

    function closeAllMenus(except) {
        $('.sx-oa-actions.is-open').each(function () {
            if (except && this === except) return;
            $(this).removeClass('is-open');
            $(this).find('.sx-oa-actions__toggle').attr('aria-expanded', 'false');
        });
    }

    function initRoot($root) {
        if (!$root.length || $root.data('sx-oa-inited')) return;
        $root.data('sx-oa-inited', true);

        var tableId = $root.attr('data-table-id');
        var storageKey = $root.attr('data-storage-key') || 'sx_oa_portal_apps';
        var showYear = $root.attr('data-show-year') === '1';
        var $table = tableId ? $('#' + tableId) : $();
        var actionsCol = showYear ? 7 : 6;
        var submittedCol = showYear ? 6 : 5;

        var view = resolveView(storageKey);
        applyView($root, view);

        if ($table.length && !$.fn.DataTable.isDataTable($table)) {
            $table.DataTable({
                order: [[submittedCol, 'desc']],
                pageLength: 10,
                autoWidth: false,
                columnDefs: [
                    { orderable: false, targets: [actionsCol] },
                    { searchable: false, targets: [actionsCol] }
                ],
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    emptyTable: 'No applications found'
                }
            });
        }

        var $panel = $root.closest('.sx-oa-panel');
        ($panel.length ? $panel : $root.parent()).on('click', '[data-sx-oa-view]', function (e) {
            e.preventDefault();
            var next = $(this).attr('data-sx-oa-view') === 'grid' ? 'grid' : 'list';
            persistView(storageKey, next);
            applyView($root, next);
            closeAllMenus();
        });
    }

    $(document).on('click', '.sx-oa-actions__toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $wrap = $(this).closest('.sx-oa-actions');
        var open = !$wrap.hasClass('is-open');
        closeAllMenus(open ? $wrap.get(0) : null);
        $wrap.toggleClass('is-open', open);
        $(this).attr('aria-expanded', open ? 'true' : 'false');
    });

    $(document).on('click', function () {
        closeAllMenus();
    });

    $(document).on('click', '.sx-oa-actions__menu', function (e) {
        e.stopPropagation();
    });

    $(document).on('click', '.sx-oa-delete-app', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeAllMenus();

        var $btn = $(this);
        var url = $btn.attr('data-url');
        var id = $btn.attr('data-id');
        var ref = $btn.attr('data-ref') || '';

        function doDelete() {
            $btn.prop('disabled', true);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ _token: csrf })
            })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    var $row = $('[data-app-row="' + id + '"]');
                    var $card = $('[data-app-card="' + id + '"]');
                    var $table = $row.closest('table');

                    if ($table.length && $.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().row($row).remove().draw(false);
                    } else {
                        $row.remove();
                    }
                    $card.remove();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: result.data.message || 'Application deleted.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }

                    if (!$('[data-app-row]').length && !$('[data-app-card]').length) {
                        window.location.reload();
                    }
                    return;
                }

                $btn.prop('disabled', false);
                var msg = (result.data && result.data.message) || 'Could not delete application.';
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Delete failed', text: msg });
                else alert(msg);
            })
            .catch(function () {
                $btn.prop('disabled', false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Delete application?',
                text: 'Remove pending application ' + ref + '? This cannot be undone.',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (r) {
                if (r.isConfirmed) doDelete();
            });
        } else if (window.confirm('Delete application ' + ref + '?')) {
            doDelete();
        }
    });

    $(function () {
        $('.sx-oa-apps-view-root').each(function () {
            initRoot($(this));
        });

        var resizeTimer = null;
        $(window).on('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                $('.sx-oa-apps-view-root').each(function () {
                    var $root = $(this);
                    var key = $root.attr('data-storage-key') || 'sx_oa_portal_apps';
                    applyView($root, resolveView(key));
                });
            }, 150);
        });
    });
})(window.jQuery);
</script>
