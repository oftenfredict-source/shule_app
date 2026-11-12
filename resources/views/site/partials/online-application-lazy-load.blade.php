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

    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function viewKey() {
        return isMobile() ? VIEW_KEY_M : VIEW_KEY_D;
    }

    function readView() {
        if (isMobile()) return 'grid';
        var view = 'grid';
        try {
            var saved = localStorage.getItem(viewKey());
            if (saved === 'list' || saved === 'grid') view = saved;
            localStorage.removeItem('sx_oa_schools_view');
        } catch (e) {}
        return view;
    }

    function applyView(view, persist) {
        if (isMobile()) view = 'grid';
        view = view === 'list' ? 'list' : 'grid';
        grid.classList.remove('is-list', 'is-grid');
        grid.classList.add(view === 'list' ? 'is-list' : 'is-grid');
        document.querySelectorAll('[data-oa-view]').forEach(function (btn) {
            var active = btn.getAttribute('data-oa-view') === view;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        if (persist && !isMobile()) {
            try { localStorage.setItem(viewKey(), view); } catch (e) {}
        }
    }

    document.querySelectorAll('[data-oa-view]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (isMobile() && btn.getAttribute('data-oa-view') === 'list') return;
            var view = btn.getAttribute('data-oa-view') === 'list' ? 'list' : 'grid';
            applyView(view, true);
        });
    });

    applyView(readView(), false);

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

    window.addEventListener('resize', function () {
        applyView(readView(), false);
    });

    if ('IntersectionObserver' in window && loadMoreEl) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) loadMore();
            });
        }, { rootMargin: '200px 0px' });
        observer.observe(loadMoreEl);
    } else {
        window.addEventListener('scroll', function () {
            if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 400)) {
                loadMore();
            }
        });
    }
})();
</script>

