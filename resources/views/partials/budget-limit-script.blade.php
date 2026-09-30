{{--
    Live checks for peso amounts capped by Proposal::MAX_BUDGET. Mark an input
    with data-budget-limit (and optionally data-budget-min). As the user types:
      - only digits and one decimal point are kept, commas are dropped, and at
        most 6 whole digits and 2 decimals get through;
      - anything over the limit (or under the minimum) turns the field red,
        shows the reason in a sibling [data-budget-limit-message], and blocks
        the form from submitting.
    The server applies the same limit; this only gives feedback sooner.
    Listeners sit on the document so rows added later are covered too.
--}}
<script>
(function () {
    if (window.SSCBudgetLimit) return;

    var MAX = {{ \App\Models\Proposal::MAX_BUDGET }};
    var MAX_DIGITS = String(MAX).length;
    var MAX_LABEL = @json(\App\Models\Proposal::maxBudgetLabel());

    function sanitize(value) {
        var cleaned = String(value).replace(/[^\d.]/g, '');
        var dot = cleaned.indexOf('.');
        var whole = (dot === -1 ? cleaned : cleaned.slice(0, dot)).slice(0, MAX_DIGITS);
        if (dot === -1) return whole;
        return whole + '.' + cleaned.slice(dot + 1).replace(/\./g, '').slice(0, 2);
    }

    function problem(input) {
        var value = input.value.trim();
        if (value === '' || value === '.') return '';
        var amount = parseFloat(value);
        var min = parseFloat(input.dataset.budgetMin);
        if (amount > MAX) return 'The maximum is ' + MAX_LABEL + ' per proposal.';
        // A read-only field is a calculated total that starts at zero while
        // rows are being filled in, so it is only held to the maximum.
        if (!input.readOnly && !isNaN(min) && amount < min) return 'The minimum is ₱' + min.toFixed(2) + '.';
        return '';
    }

    /** Validates without changing the value, so saved amounts show as they are. */
    function check(input) {
        var message = problem(input);
        input.setCustomValidity(message);
        input.classList.toggle('is-invalid', message !== '');

        var note = input.parentElement && input.parentElement.querySelector('[data-budget-limit-message]');
        if (note) {
            note.textContent = message;
            note.hidden = message === '';
        }
        return message === '';
    }

    // Typing (or pasting) is where the 6-digit limit is enforced.
    document.addEventListener('input', function (event) {
        var input = event.target;
        if (!input.matches || !input.matches('[data-budget-limit]') || input.readOnly) return;

        var clean = sanitize(input.value);
        if (clean !== input.value) input.value = clean;
        check(input);
    });

    document.querySelectorAll('[data-budget-limit]').forEach(check);

    window.SSCBudgetLimit = { check: check, sanitize: sanitize, max: MAX };
})();
</script>
