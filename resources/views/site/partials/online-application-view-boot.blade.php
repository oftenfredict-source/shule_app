<script>
(function (el) {
    if (!el) return;
    var mobile = window.matchMedia('(max-width: 767.98px)').matches;
    var key = mobile ? 'sx_oa_schools_view_m' : 'sx_oa_schools_view_d';
    var view = 'grid';
    if (!mobile) {
        try {
            var saved = localStorage.getItem(key);
            if (saved === 'list' || saved === 'grid') view = saved;
            localStorage.removeItem('sx_oa_schools_view');
        } catch (e) {}
    } else {
        try { localStorage.setItem(key, 'grid'); } catch (e) {}
    }
    el.classList.remove('is-list', 'is-grid');
    el.classList.add(view === 'list' ? 'is-list' : 'is-grid');
})(document.getElementById('oaSchoolsGrid'));
</script>
