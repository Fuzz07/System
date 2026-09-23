(function () {
    'use strict';

    var POLL_INTERVAL = 6000;
    var REQUEST_TIMEOUT = 5000;
    var selector = '[data-ssc-live-region]';
    var region = document.querySelector(selector);

    if (!region || !window.fetch || !window.DOMParser) return;

    var lastServerMarkup = region.innerHTML;
    var requestInFlight = false;
    var formIsDirty = false;

    function isEditable(element) {
        return element && (
            element.matches('input:not([type="button"]):not([type="submit"]), textarea, select, [contenteditable="true"]')
        );
    }

    function shouldPause() {
        return document.hidden
            || formIsDirty
            || isEditable(document.activeElement)
            || document.body.classList.contains('modal-open')
            || document.querySelector('.modal.show, [data-ssc-live-pause]');
    }

    function syncAttributes(current, incoming) {
        Array.from(current.attributes).forEach(function (attribute) {
            if (!incoming.hasAttribute(attribute.name)) current.removeAttribute(attribute.name);
        });

        Array.from(incoming.attributes).forEach(function (attribute) {
            if (current.getAttribute(attribute.name) !== attribute.value) {
                current.setAttribute(attribute.name, attribute.value);
            }
        });
    }

    function cloneSafe(node) {
        if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'SCRIPT') return null;
        return node.cloneNode(true);
    }

    function morph(current, incoming) {
        if (!current || !incoming) return;

        if (current.nodeType !== incoming.nodeType) {
            var replacement = cloneSafe(incoming);
            if (replacement) current.replaceWith(replacement);
            return;
        }

        if (current.nodeType === Node.TEXT_NODE || current.nodeType === Node.COMMENT_NODE) {
            if (current.nodeValue !== incoming.nodeValue) current.nodeValue = incoming.nodeValue;
            return;
        }

        if (current.tagName !== incoming.tagName) {
            var newNode = cloneSafe(incoming);
            if (newNode) current.replaceWith(newNode);
            return;
        }

        if (current.matches('script, style, canvas, [data-ssc-live-preserve]')) return;

        syncAttributes(current, incoming);

        if (current instanceof HTMLInputElement) {
            current.checked = incoming.checked;
            current.value = incoming.value;
        } else if (current instanceof HTMLTextAreaElement || current instanceof HTMLSelectElement) {
            current.value = incoming.value;
        }

        var incomingChildren = Array.from(incoming.childNodes);

        incomingChildren.forEach(function (incomingChild, index) {
            var currentChild = current.childNodes[index];
            var incomingId = incomingChild.nodeType === Node.ELEMENT_NODE ? incomingChild.id : '';

            if (incomingId) {
                var keyedChild = Array.from(current.children).find(function (child) {
                    return child.id === incomingId;
                });
                if (keyedChild && keyedChild !== currentChild) {
                    current.insertBefore(keyedChild, currentChild || null);
                    currentChild = keyedChild;
                }
            }

            if (!currentChild) {
                var appended = cloneSafe(incomingChild);
                if (appended) current.appendChild(appended);
                return;
            }

            morph(currentChild, incomingChild);
        });

        while (current.childNodes.length > incomingChildren.length) {
            current.removeChild(current.childNodes[incomingChildren.length]);
        }
    }

    async function checkForUpdates() {
        if (requestInFlight || shouldPause()) return;
        requestInFlight = true;

        var controller = new AbortController();
        var timeout = window.setTimeout(function () { controller.abort(); }, REQUEST_TIMEOUT);

        try {
            var response = await fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-SSC-Live-Refresh': '1'
                }
            });

            if (response.redirected && new URL(response.url).pathname.indexOf('/login') !== -1) {
                window.location.assign(response.url);
                return;
            }

            if (!response.ok || !(response.headers.get('content-type') || '').includes('text/html')) return;

            var html = await response.text();
            var incomingDocument = new DOMParser().parseFromString(html, 'text/html');
            var incomingRegion = incomingDocument.querySelector(selector);
            var currentRegion = document.querySelector(selector);

            if (!incomingRegion || !currentRegion || incomingRegion.innerHTML === lastServerMarkup) return;

            lastServerMarkup = incomingRegion.innerHTML;
            morph(currentRegion, incomingRegion);
            document.dispatchEvent(new CustomEvent('ssc:content-updated', {
                detail: { url: window.location.href }
            }));
        } catch (error) {
            if (error.name !== 'AbortError') {
                document.dispatchEvent(new CustomEvent('ssc:live-update-error'));
            }
        } finally {
            window.clearTimeout(timeout);
            requestInFlight = false;
        }
    }

    document.addEventListener('input', function (event) {
        if (region.contains(event.target) && isEditable(event.target)) formIsDirty = true;
    });

    document.addEventListener('change', function (event) {
        if (region.contains(event.target) && isEditable(event.target)) formIsDirty = true;
    });

    document.addEventListener('submit', function () { formIsDirty = false; });
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) checkForUpdates();
    });

    window.setInterval(checkForUpdates, POLL_INTERVAL);
})();
