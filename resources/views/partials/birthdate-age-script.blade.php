{{--
    Fills the read-only Age box beside a date-of-birth input and flags anyone
    under the minimum age before the form is sent. The server applies the same
    rule; this only gives earlier feedback.

    Mark the date input with data-birthdate-input and point data-age-target at
    the id of the Age box. Listeners sit on the document so they keep working
    after live updates redraw the page content.
--}}
<script>
(function () {
    var MIN_AGE = {{ \App\Models\User::MIN_AGE }};

    function ageFrom(value) {
        var parts = (value || '').split('-').map(Number);
        if (parts.length !== 3 || !parts[0] || !parts[1] || !parts[2]) return null;

        var today = new Date();
        var month = today.getMonth() + 1;
        var age = today.getFullYear() - parts[0];
        if (month < parts[1] || (month === parts[1] && today.getDate() < parts[2])) age--;
        return age;
    }

    function update(input) {
        var age = ageFrom(input.value);
        var target = document.getElementById(input.dataset.ageTarget);
        if (target) target.value = age === null || age < 0 ? '' : age;

        input.setCustomValidity(age !== null && age < MIN_AGE
            ? 'You must be at least ' + MIN_AGE + ' years old.'
            : '');
    }

    function onEdit(event) {
        if (event.target.matches && event.target.matches('[data-birthdate-input]')) update(event.target);
    }

    document.addEventListener('input', onEdit);
    document.addEventListener('change', onEdit);
    document.querySelectorAll('[data-birthdate-input]').forEach(update);
})();
</script>
