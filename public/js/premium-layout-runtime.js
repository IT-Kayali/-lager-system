(() => {
    'use strict';

    function parsePremiumServerToasts() {
        const config = document.getElementById(
            'premium-layout-runtime-config'
        );

        const raw = config?.dataset.serverToasts;

        if (!raw) {
            return [];
        }

        try {
            const parsed = JSON.parse(raw);

            return Array.isArray(parsed)
                ? parsed
                : [];
        } catch (error) {
            console.error(
                'Invalid premium server toast configuration.',
                error
            );

            return [];
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('premium-toasts-ready');

            const stack = document.getElementById('premium-toast-stack');
            const serverToasts = parsePremiumServerToasts();

            if (!stack) {
                return;
            }

            const iconMap = {
                success: 'bi-check2-circle',
                error: 'bi-exclamation-triangle',
                warning: 'bi-exclamation-circle',
                info: 'bi-info-circle',
            };

            function normalizeType(type) {
                if (['success', 'error', 'warning', 'info'].includes(type)) {
                    return type;
                }

                return 'info';
            }

            window.premiumToast = function (type, title, message) {
                type = normalizeType(type);

                const toast = document.createElement('div');
                toast.className = `premium-toast ${type}`;
                toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

                const icon = document.createElement('div');
                icon.className = 'premium-toast-icon';
                icon.innerHTML = `<i class="bi ${iconMap[type] || iconMap.info}"></i>`;

                const content = document.createElement('div');

                const titleEl = document.createElement('div');
                titleEl.className = 'premium-toast-title';
                titleEl.textContent = title || 'Hinweis';

                const messageEl = document.createElement('div');
                messageEl.className = 'premium-toast-message';
                messageEl.textContent = message || '';

                content.appendChild(titleEl);
                content.appendChild(messageEl);

                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'premium-toast-close';
                close.innerHTML = '<i class="bi bi-x-lg"></i>';
                close.setAttribute('aria-label', 'Benachrichtigung schließen');

                toast.appendChild(icon);
                toast.appendChild(content);
                toast.appendChild(close);

                stack.appendChild(toast);

                function removeToast() {
                    toast.style.animation = 'premiumToastOut .18s ease forwards';
                    window.setTimeout(function () {
                        toast.remove();
                    }, 180);
                }

                close.addEventListener('click', removeToast);

                window.setTimeout(removeToast, type === 'error' ? 7600 : 6000);
            };

            function fieldLabel(field) {
                if (!field) {
                    return 'Dieses Feld';
                }

                if (field.id) {
                    const label = document.querySelector(`label[for="${CSS.escape(field.id)}"]`);
                    if (label && label.textContent.trim()) {
                        return label.textContent.trim().replace('*', '').trim();
                    }
                }

                const wrapperLabel = field.closest('.premium-form-field')?.querySelector('label');
                if (wrapperLabel && wrapperLabel.textContent.trim()) {
                    return wrapperLabel.textContent.trim().replace('*', '').trim();
                }

                return field.getAttribute('placeholder')
                    || field.getAttribute('name')
                    || 'Dieses Feld';
            }

            function markInvalid(field) {
                if (!field) {
                    return;
                }

                field.classList.add('premium-invalid-field');

                if (field.tomselect && field.tomselect.wrapper) {
                    field.tomselect.wrapper.classList.add('premium-invalid-field');
                }

                field.addEventListener('input', function () {
                    field.classList.remove('premium-invalid-field');

                    if (field.tomselect && field.tomselect.wrapper) {
                        field.tomselect.wrapper.classList.remove('premium-invalid-field');
                    }
                }, { once: true });

                field.addEventListener('change', function () {
                    field.classList.remove('premium-invalid-field');

                    if (field.tomselect && field.tomselect.wrapper) {
                        field.tomselect.wrapper.classList.remove('premium-invalid-field');
                    }
                }, { once: true });
            }

            let lastInvalidToastAt = 0;

            function showInvalidToast(field) {
                const now = Date.now();

                if (now - lastInvalidToastAt < 350) {
                    return;
                }

                lastInvalidToastAt = now;

                const label = fieldLabel(field);
                const message = field.validationMessage || `${label} muss ausgefüllt werden.`;

                markInvalid(field);

                window.premiumToast('error', 'Pflichtfeld fehlt', `${label}: ${message}`);

                window.setTimeout(function () {
                    try {
                        field.focus({ preventScroll: false });
                    } catch (e) {
                        field.focus();
                    }
                }, 80);
            }

            document.addEventListener('invalid', function (event) {
                event.preventDefault();
                showInvalidToast(event.target);
            }, true);

            document.addEventListener('submit', function (event) {
                const form = event.target;

                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (form.checkValidity()) {
                    return;
                }

                event.preventDefault();

                const invalidField = form.querySelector(':invalid');

                if (invalidField) {
                    showInvalidToast(invalidField);
                } else {
                    window.premiumToast('error', 'Bitte prüfen', 'Bitte fülle alle Pflichtfelder korrekt aus.');
                }
            }, true);

            serverToasts.forEach(function (toast) {
                window.premiumToast(toast.type, toast.title, toast.message);
            });
        });
})();
