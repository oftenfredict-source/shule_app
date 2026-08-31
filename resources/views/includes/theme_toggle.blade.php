@php
    $sxThemeUseBx = $sxThemeUseBx ?? false;
@endphp
<button type="button" class="sx-theme-toggle" aria-label="Switch to dark mode" title="Dark / Light mode">
    @if($sxThemeUseBx)
        <i class="bx bx-moon sx-theme-icon-moon" aria-hidden="true"></i>
        <i class="bx bx-sun sx-theme-icon-sun" aria-hidden="true"></i>
    @else
        <i class="fa fa-moon-o sx-theme-icon-moon" aria-hidden="true"></i>
        <i class="fa fa-sun-o sx-theme-icon-sun" aria-hidden="true"></i>
    @endif
</button>
<script>
    (function () {
        if (window.__sxThemeToggleBound) return;
        window.__sxThemeToggleBound = true;

        function applyToggleState(theme) {
            var isDark = theme === 'dark';
            document.querySelectorAll('.sx-theme-toggle').forEach(function (btn) {
                btn.classList.toggle('is-dark', isDark);
                btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                btn.setAttribute('title', isDark ? 'Light mode' : 'Dark mode');
            });
        }

        function setTheme(theme) {
            document.documentElement.setAttribute('data-sx-theme', theme);
            try { localStorage.setItem('sx-theme', theme); } catch (e) {}
            applyToggleState(theme);
        }

        function toggleTheme() {
            var current = document.documentElement.getAttribute('data-sx-theme') || 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
        }

        function bindToggles() {
            applyToggleState(document.documentElement.getAttribute('data-sx-theme') || 'light');
            document.querySelectorAll('.sx-theme-toggle').forEach(function (btn) {
                if (btn.__sxBound) return;
                btn.__sxBound = true;
                btn.addEventListener('click', toggleTheme);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindToggles);
        } else {
            bindToggles();
        }
    })();
    </script>
