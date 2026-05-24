console.log('EventEule Frontend geladen');

/* ========== Registration Form ========== */

document.addEventListener('submit', function (e) {
    const form = e.target.closest('.eventeule-registration__form');
    if (!form) return;

    e.preventDefault();

    const ajaxUrl  = form.dataset.ajaxUrl;
    const nonce    = form.dataset.nonce;
    const eventId  = form.dataset.eventId;
    const registrationRoot = form.closest('.eventeule-registration') || form.closest('.ee-reg-popup-dialog') || form.parentElement;
    const messages = registrationRoot ? registrationRoot.querySelector('.eventeule-registration__messages') : null;
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
    if (submitBtn) submitBtn.disabled = true;
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
            const counter = registrationRoot ? registrationRoot.querySelector('.eventeule-registration__counter') : null;
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

            if (submitBtn) submitBtn.disabled = false;
            if (submitText) submitText.style.opacity = '';
            if (submitSpinner) submitSpinner.style.display = 'none';
        }
    })
    .catch(function () {
        showFormError(messages, 'A network error occurred. Please try again.');
        if (submitBtn) submitBtn.disabled = false;
        if (submitText) submitText.style.opacity = '';
        if (submitSpinner) submitSpinner.style.display = 'none';
    });
});

function initAdditionalParticipantFields() {
    document.querySelectorAll('.eventeule-registration__form').forEach(function (form) {
        const participantsInput = form.querySelector('[name="participants"]');
        const extraWrap = form.querySelector('.eventeule-registration__participants-extra');

        if (!participantsInput || !extraWrap) {
            return;
        }

        const render = function () {
            const fieldsRoot = extraWrap.querySelector('.eventeule-registration__participants-extra-fields');
            if (!fieldsRoot) {
                return;
            }

            const previousValues = {};
            fieldsRoot.querySelectorAll('[data-participant-index]').forEach(function (group) {
                const index = parseInt(group.getAttribute('data-participant-index'), 10);
                if (!index || index < 2) {
                    return;
                }
                previousValues[index] = {
                    firstname: (group.querySelector('input[name="participant_firstname[]"]') || {}).value || '',
                    lastname: (group.querySelector('input[name="participant_lastname[]"]') || {}).value || '',
                };
            });

            const count = Math.max(1, parseInt(participantsInput.value || '1', 10) || 1);
            const extraCount = Math.max(0, count - 1);
            fieldsRoot.innerHTML = '';

            if (extraCount === 0) {
                return;
            }

            const title = document.createElement('p');
            title.className = 'eventeule-registration__participants-extra-title';
            title.textContent = extraWrap.dataset.title || 'Additional participants';
            fieldsRoot.appendChild(title);

            for (let i = 2; i <= count; i++) {
                const group = document.createElement('div');
                group.className = 'eventeule-registration__participants-extra-group';
                group.setAttribute('data-participant-index', String(i));

                const heading = document.createElement('h4');
                heading.textContent = (extraWrap.dataset.participantLabel || 'Participant') + ' ' + i;
                group.appendChild(heading);

                const firstField = document.createElement('div');
                firstField.className = 'eventeule-registration__field';
                firstField.setAttribute('data-field', 'participant_' + i + '_firstname');

                const firstLabel = document.createElement('label');
                firstLabel.htmlFor = 'eventeule_reg_extra_firstname_' + i + '_' + (form.dataset.eventId || '0');
                firstLabel.textContent = extraWrap.dataset.firstnameLabel || 'First name';
                firstField.appendChild(firstLabel);

                const firstInput = document.createElement('input');
                firstInput.type = 'text';
                firstInput.id = firstLabel.htmlFor;
                firstInput.name = 'participant_firstname[]';
                firstInput.required = true;
                firstInput.setAttribute('aria-required', 'true');
                firstInput.autocomplete = 'off';
                firstInput.placeholder = extraWrap.dataset.firstnamePlaceholder || 'First name';
                firstInput.value = (previousValues[i] && previousValues[i].firstname) ? previousValues[i].firstname : '';
                firstField.appendChild(firstInput);

                const firstError = document.createElement('span');
                firstError.className = 'eventeule-registration__field-error';
                firstError.setAttribute('role', 'alert');
                firstField.appendChild(firstError);

                const lastField = document.createElement('div');
                lastField.className = 'eventeule-registration__field';
                lastField.setAttribute('data-field', 'participant_' + i + '_lastname');

                const lastLabel = document.createElement('label');
                lastLabel.htmlFor = 'eventeule_reg_extra_lastname_' + i + '_' + (form.dataset.eventId || '0');
                lastLabel.textContent = extraWrap.dataset.lastnameLabel || 'Last name';
                lastField.appendChild(lastLabel);

                const lastInput = document.createElement('input');
                lastInput.type = 'text';
                lastInput.id = lastLabel.htmlFor;
                lastInput.name = 'participant_lastname[]';
                lastInput.required = true;
                lastInput.setAttribute('aria-required', 'true');
                lastInput.autocomplete = 'off';
                lastInput.placeholder = extraWrap.dataset.lastnamePlaceholder || 'Last name';
                lastInput.value = (previousValues[i] && previousValues[i].lastname) ? previousValues[i].lastname : '';
                lastField.appendChild(lastInput);

                const lastError = document.createElement('span');
                lastError.className = 'eventeule-registration__field-error';
                lastError.setAttribute('role', 'alert');
                lastField.appendChild(lastError);

                group.appendChild(firstField);
                group.appendChild(lastField);
                fieldsRoot.appendChild(group);
            }
        };

        participantsInput.addEventListener('input', render);
        participantsInput.addEventListener('change', render);
        render();
    });
}

document.addEventListener('DOMContentLoaded', initAdditionalParticipantFields);

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
