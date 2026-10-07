<script>
(function () {
    var theme = 'light';
    try {
        var saved = localStorage.getItem('sx-theme');
        if (saved === 'dark' || saved === 'light') theme = saved;
    } catch (e) {}
    document.documentElement.setAttribute('data-sx-theme', theme);
})();
</script>
