<script>
(function (el) {
    if (!el) return;
    var mobile = window.matchMedia('(max-width: 991px)').matches;
    var key = mobile ? 'sx_oa_schools_view_m' : 'sx_oa_schools_view_d';
    var view = mobile ? 'grid' : 'list';
    try {
        var saved = localStorage.getItem(key);
        if (saved === 'grid' || saved === 'list') view = saved;
        localStorage.removeItem('sx_oa_schools_view');
    } catch (e) {}
    el.classList.remove('is-list', 'is-grid');
    el.classList.add(view === 'grid' ? 'is-grid' : 'is-list');
})(document.getElementById('oaSchoolsGrid'));
</script>
