console.log('EventEule Frontend geladen');

/* ========== Registration Form ========== */

document.addEventListener('submit', function (e) {
    const form = e.target.closest('.eventeule-registration__form');
    if (!form) return;

    e.preventDefault();

    const ajaxUrl  = form.dataset.ajaxUrl;
    const nonce    = form.dataset.nonce;
    const eventId  = form.dataset.eventId;
    const messages = form.closest('.eventeule-registration').querySelector('.eventeule-registration__messages');
    const submitBtn = form.querySelector('.eventeule-registration__submit');
    const submitText = form.querySelector('.eventeule-registration__submit-text');
    const submitSpinner = form.querySelector('.eventeule-registration__submit-spinner');

    // Clear previous errors
    form.querySelectorAll('.eventeule-registration__field--error').forEach(function (el) {
        el.classList.remove('eventeule-registration__field--error');
    });
    form.querySelectorAll('.eventeule-registration__field-error').forEach(function (el) {
        el.textContent = '';
    });
    if (messages) messages.innerHTML = '';

    // Disable submit
    submitBtn.disabled = true;
    if (submitText) submitText.style.opacity = '0.5';
    if (submitSpinner) submitSpinner.style.display = '';

    const formData = new FormData(form);
    formData.append('action', 'eventeule_register');
    formData.append('nonce', nonce);
    formData.append('event_id', eventId);

    fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Network error');
        }
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            // Show thank you message, hide form
            if (messages) {
                const msg = document.createElement('p');
                msg.className = 'eventeule-registration__message eventeule-registration__message--success';
                msg.textContent = data.data.message;
                messages.appendChild(msg);
            }
            form.style.display = 'none';

            // Notify popup (if applicable) to auto-close after a short delay
            document.dispatchEvent(new CustomEvent('ee:registration:success', { detail: { form: form } }));

            // Update available spots counter
            const counter = form.closest('.eventeule-registration').querySelector('.eventeule-registration__counter');
            if (counter) {
                const remaining = data.data.remaining;
                if (remaining === -1) {
                    // unlimited — no counter needed
                } else if (remaining === 0) {
                    const max = counter.dataset.max;
                    counter.textContent = counter.dataset.full || 'Fully booked';
                    counter.classList.add('eventeule-registration__counter--full');
                } else {
                    const max = counter.dataset.max;
                    counter.dataset.available = remaining;
                    counter.textContent = remaining + ' of ' + max + ' spots available';
                    if (remaining <= 3) {
                        counter.classList.add('eventeule-registration__counter--warning');
                    }
                }
            }
        } else {
            // Show error message
            const errMsg = (data.data && data.data.message) ? data.data.message : 'An error occurred.';
            const fieldKey = (data.data && data.data.field) ? data.data.field : null;

            if (fieldKey) {
                const fieldWrapper = form.querySelector('[data-field="' + fieldKey + '"]');
                if (fieldWrapper) {
                    fieldWrapper.classList.add('eventeule-registration__field--error');
                    const errEl = fieldWrapper.querySelector('.eventeule-registration__field-error');
                    if (errEl) errEl.textContent = errMsg;
                    const input = fieldWrapper.querySelector('input, textarea, select');
                    if (input) input.focus();
                } else {
                    showFormError(messages, errMsg);
                }
            } else {
                showFormError(messages, errMsg);
            }

            submitBtn.disabled = false;
            if (submitText) submitText.style.opacity = '';
            if (submitSpinner) submitSpinner.style.display = 'none';
        }
    })
    .catch(function () {
        showFormError(messages, 'A network error occurred. Please try again.');
        submitBtn.disabled = false;
        if (submitText) submitText.style.opacity = '';
        if (submitSpinner) submitSpinner.style.display = 'none';
    });
});

function showFormError(messages, msg) {
    if (!messages) return;
    const p = document.createElement('p');
    p.className = 'eventeule-registration__message eventeule-registration__message--error';
    p.textContent = msg;
    messages.innerHTML = '';
    messages.appendChild(p);
    messages.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ========== Registration Popup ========== */

(function () {
    function openPopup(overlay) {
        overlay.classList.add('is-open');
        document.body.classList.add('ee-popup-open');
        // Focus the first interactive element inside the dialog
        const firstFocusable = overlay.querySelector(
            'input:not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled])'
        );
        if (firstFocusable) {
            setTimeout(function () { firstFocusable.focus(); }, 60);
        }
    }

    function closePopup(overlay) {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        if (!document.querySelector('.ee-reg-popup-overlay.is-open')) {
            document.body.classList.remove('ee-popup-open');
        }
    }

    // Delegate all click events
    document.addEventListener('click', function (e) {
        // Open: click on the trigger button (skip <a> elements — those are external links)
        const trigger = e.target.closest('.ee-reg-popup-trigger');
        if (trigger && !trigger.disabled && trigger.tagName !== 'A') {
            const wrap    = trigger.closest('.ee-reg-popup-wrap');
            const overlay = wrap ? wrap.querySelector('.ee-reg-popup-overlay') : null;
            if (overlay) openPopup(overlay);
            return;
        }

        // Close: X button
        const closeBtn = e.target.closest('.ee-reg-popup-close');
        if (closeBtn) {
            closePopup(closeBtn.closest('.ee-reg-popup-overlay'));
            return;
        }

        // Close: click directly on the backdrop (not on the dialog card)
        if (e.target.classList.contains('ee-reg-popup-overlay')) {
            closePopup(e.target);
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.ee-reg-popup-overlay.is-open').forEach(closePopup);
        }
    });

    // Auto-close popup ~3 s after a successful registration
    document.addEventListener('ee:registration:success', function (e) {
        const form = e.detail && e.detail.form;
        if (!form) return;
        const overlay = form.closest('.ee-reg-popup-overlay');
        if (overlay) {
            setTimeout(function () { closePopup(overlay); }, 3500);
        }
    });
}());
