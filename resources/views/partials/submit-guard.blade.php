{{-- Duplicate-submission guard: once a form is on its way to the server it cannot
     be sent again until the next page loads, however often its button is clicked.
     Inline in <head> so it already works while the rest of the page is still
     downloading on a slow connection. Each form it sends also carries a
     _submit_token, which PreventDuplicateSubmissionMiddleware accepts only once. --}}
<script>
(function () {
  if (window.SSCSubmitGuard) return;

  var TOKEN_FIELD = '_submit_token';
  var LOCK_ATTR = 'data-ssc-submitting';
  var lockedState = new WeakMap();

  function newToken() {
    var bytes = new Uint8Array(16);
    if (window.crypto && window.crypto.getRandomValues) {
      window.crypto.getRandomValues(bytes);
    } else {
      for (var i = 0; i < bytes.length; i++) bytes[i] = Math.floor(Math.random() * 256);
    }
    return Array.prototype.map.call(bytes, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
  }

  // Only POST forms change data (PUT, PATCH and DELETE are sent as POST);
  // GET forms are filters and searches, which are safe to repeat. Attributes
  // are read instead of form.method / form.target because a field named
  // "method" or "target" would shadow those properties.
  function shouldGuard(form, submitter) {
    var method = (submitter && submitter.getAttribute('formmethod')) || form.getAttribute('method') || 'get';
    var target = (submitter && submitter.getAttribute('formtarget')) || form.getAttribute('target');
    return method.toLowerCase() === 'post'
      && !form.hasAttribute('data-allow-resubmit')
      && (!target || target === '_self');
  }

  function lock(form, submitter) {
    form.setAttribute(LOCK_ATTR, 'true');
    form.setAttribute('aria-busy', 'true');

    if (!form.querySelector('input[name="' + TOKEN_FIELD + '"]')) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = TOKEN_FIELD;
      input.value = newToken();
      form.appendChild(input);
    }

    // The browser collects the form's fields, the clicked button's name and
    // value included, just after the submit event. Disabling the buttons any
    // sooner would drop that value (the "action" of an Approve/Reject button).
    setTimeout(function () {
      if (!form.hasAttribute(LOCK_ATTR)) return;

      var state = { buttons: [], spinner: null };
      Array.prototype.forEach.call(form.elements, function (el) {
        if ((el.type === 'submit' || el.type === 'image') && !el.disabled) {
          el.disabled = true;
          state.buttons.push(el);
        }
      });
      if (submitter && submitter.tagName === 'BUTTON' && !submitter.querySelector('.spinner-border')) {
        state.spinner = document.createElement('span');
        state.spinner.className = 'spinner-border spinner-border-sm me-1';
        state.spinner.setAttribute('aria-hidden', 'true');
        submitter.insertBefore(state.spinner, submitter.firstChild);
      }
      lockedState.set(form, state);
    }, 0);
  }

  // Unlocks a form so it can be sent again, under a new token.
  function release(form) {
    if (!form) return;
    form.removeAttribute(LOCK_ATTR);
    form.removeAttribute('aria-busy');

    var input = form.querySelector('input[name="' + TOKEN_FIELD + '"]');
    if (input) input.remove();

    var state = lockedState.get(form);
    if (state) {
      state.buttons.forEach(function (btn) { btn.disabled = false; });
      if (state.spinner) state.spinner.remove();
      lockedState.delete(form);
    }
  }

  // Runs before every other submit handler: a form already on its way is not
  // sent again, and page handlers that would send it some other way never run.
  window.addEventListener('submit', function (event) {
    var form = event.target;
    if (form instanceof HTMLFormElement && form.hasAttribute(LOCK_ATTR)) {
      event.preventDefault();
      event.stopImmediatePropagation();
    }
  }, true);

  // Runs after every other submit handler. If none of them cancelled the
  // submission (a confirmation dialog, client-side validation, an AJAX
  // handler), the browser is about to send it, so the form is locked now.
  window.addEventListener('submit', function (event) {
    var form = event.target;
    if (!event.defaultPrevented && form instanceof HTMLFormElement && shouldGuard(form, event.submitter)) {
      lock(form, event.submitter);
    }
  });

  // form.submit() sends a form without a submit event (the logout dialog, the
  // login location check, the release confirmation), so it is guarded here.
  var nativeSubmit = HTMLFormElement.prototype.submit;
  HTMLFormElement.prototype.submit = function () {
    if (this.hasAttribute(LOCK_ATTR)) return;
    if (shouldGuard(this, null)) lock(this, null);
    return nativeSubmit.call(this);
  };

  // The Back button can bring the page back exactly as it was left, locked.
  window.addEventListener('pageshow', function (event) {
    if (!event.persisted) return;
    Array.prototype.forEach.call(document.querySelectorAll('form[' + LOCK_ATTR + ']'), release);
  });

  window.SSCSubmitGuard = { release: release };
})();
</script>
