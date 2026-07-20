<script>
(function () {
    var grid = document.getElementById('oaSchoolsGrid');
    if (!grid) return;

    var loadUrl = grid.getAttribute('data-load-url');
    var page = parseInt(grid.getAttribute('data-page') || '2', 10);
    var hasMore = grid.getAttribute('data-has-more') === '1';
    var search = grid.getAttribute('data-search') || '';
    var loading = false;
    var loadMoreEl = document.getElementById('oaLoadMore');
    var spinner = document.getElementById('oaLoadSpinner');
    var loadHint = document.getElementById('oaLoadHint');
    var VIEW_KEY_M = 'sx_oa_schools_view_m';
    var VIEW_KEY_D = 'sx_oa_schools_view_d';
    var MOBILE_MAX = 991;

    function isMobileView() {
        return window.matchMedia('(max-width: ' + MOBILE_MAX + 'px)').matches;
    }

    function storageKey() {
        return isMobileView() ? VIEW_KEY_M : VIEW_KEY_D;
    }

    function defaultView() {
        return isMobileView() ? 'grid' : 'list';
    }

    function resolveView() {
        try {
            localStorage.removeItem('sx_oa_schools_view');
            var saved = localStorage.getItem(storageKey());
            if (saved === 'grid' || saved === 'list') return saved;
        } catch (e) {}
        return defaultView();
    }

    function applyView(mode) {
        var view = mode === 'grid' ? 'grid' : 'list';
        grid.classList.remove('is-list', 'is-grid');
        grid.classList.add(view === 'grid' ? 'is-grid' : 'is-list');
        document.querySelectorAll('[data-oa-view]').forEach(function (btn) {
            var active = btn.getAttribute('data-oa-view') === view;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function initView() {
        applyView(resolveView());
    }

    document.querySelectorAll('[data-oa-view]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var view = btn.getAttribute('data-oa-view') === 'grid' ? 'grid' : 'list';
            applyView(view);
            try { localStorage.setItem(storageKey(), view); } catch (e) {}
        });
    });

    var resizeTimer = null;
    var lastMobile = isMobileView();
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            var nowMobile = isMobileView();
            if (nowMobile !== lastMobile) {
                lastMobile = nowMobile;
                applyView(resolveView());
            }
        }, 150);
    });

    initView();

    function bindViewMoreClasses(root) {
        (root || document).querySelectorAll('.sx-apply-view-more-classes').forEach(function (btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                var target = document.getElementById(btn.getAttribute('data-target'));
                if (!target) return;
                target.style.display = 'block';
                btn.style.display = 'none';
            });
        });
    }

    function loadMore() {
        if (!hasMore || loading) return;
        loading = true;
        if (spinner) spinner.style.display = 'inline-block';
        if (loadHint) loadHint.textContent = 'Loading more schools...';

        var url = loadUrl + '?page=' + encodeURIComponent(page) + '&search=' + encodeURIComponent(search);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success && data.html) {
                var temp = document.createElement('div');
                temp.innerHTML = data.html;
                while (temp.firstChild) {
                    grid.appendChild(temp.firstChild);
                }
                bindViewMoreClasses(grid);
            }
            hasMore = !!data.hasMore;
            page = data.nextPage || (page + 1);
            grid.setAttribute('data-page', page);
            grid.setAttribute('data-has-more', hasMore ? '1' : '0');

            if (!hasMore && loadMoreEl) {
                loadMoreEl.style.display = 'none';
            }
        })
        .catch(function () {
            if (loadHint) loadHint.textContent = 'Could not load more schools. Scroll to try again.';
        })
        .finally(function () {
            loading = false;
            if (spinner) spinner.style.display = 'none';
            if (hasMore && loadHint) loadHint.textContent = 'Scroll down to load more schools';
        });
    }

    bindViewMoreClasses(document);

    document.body.addEventListener('click', function (e) {
        var btn = e.target.closest('.sx-copy-school-link');
        if (!btn) return;
        e.preventDefault();
        var url = btn.getAttribute('data-url') || '';
        if (!url) return;
        var done = function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Link copied', text: 'You can forward this link for online applications.', timer: 2200, showConfirmButton: false });
            } else {
                alert('Link copied.');
            }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(function () {
                window.prompt('Copy this application link:', url);
            });
        } else {
            window.prompt('Copy this application link:', url);
        }
    });

    window.addEventListener('scroll', function () {
        if (!hasMore || loading) return;
        var scrollBottom = window.innerHeight + window.scrollY;
        var threshold = document.body.offsetHeight - 300;
        if (scrollBottom >= threshold) {
            loadMore();
        }
    });
})();
</script>
