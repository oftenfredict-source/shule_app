<script>
(function () {
    function digitsOnly(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function normalizeTzPhone(value) {
        var digits = digitsOnly(value);
        if (!digits.startsWith('255')) {
            if (digits.startsWith('0') && digits.length >= 1) {
                digits = '255' + digits.slice(1);
            } else if (digits.length > 0 && !digits.startsWith('255')) {
                digits = '255' + digits.replace(/^255/, '');
            } else {
                digits = '255';
            }
        }
        if (digits.length > 12) {
            digits = digits.slice(0, 12);
        }
        if (!digits.startsWith('255')) {
            digits = '255';
        }
        return digits;
    }

    function bindTzPhone(input) {
        if (!input || input.dataset.tzBound === '1') return;
        input.dataset.tzBound = '1';

        function apply() {
            var start = input.selectionStart;
            var before = input.value;
            var next = normalizeTzPhone(input.value);
            if (next !== before) {
                input.value = next;
                var delta = next.length - before.length;
                var pos = Math.max(3, (start || 3) + delta);
                try { input.setSelectionRange(pos, pos); } catch (e) {}
            }
        }

        input.addEventListener('focus', function () {
            if (!input.value || input.value.trim() === '') {
                input.value = '255';
            }
            apply();
            if ((input.selectionStart || 0) < 3) {
                try { input.setSelectionRange(input.value.length, input.value.length); } catch (e) {}
            }
        });

        input.addEventListener('input', apply);

        input.addEventListener('keydown', function (e) {
            var start = input.selectionStart || 0;
            var end = input.selectionEnd || 0;
            if ((e.key === 'Backspace' || e.key === 'Delete') && start <= 3 && end <= 3) {
                e.preventDefault();
                return;
            }
            if (e.key === 'Backspace' && start > 0 && start <= 3 && end === start) {
                e.preventDefault();
            }
        });

        input.addEventListener('blur', apply);
        apply();
    }

    document.querySelectorAll('input.sx-tz-phone').forEach(bindTzPhone);
})();
</script>
