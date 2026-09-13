(() => {
    const statusClasses = {
        offen: 'status-unified-open',
        angebot: 'status-unified-offer',
        'in bearbeitung': 'status-unified-progress',
        abholbereit: 'status-unified-ready',
        ausgegeben: 'status-unified-issued',
        erledigt: 'status-unified-completed',
        storniert: 'status-unified-cancelled',
        'reservierung abgelaufen': 'status-unified-expired',
    };

    const unifiedClasses = [
        'status-unified-open',
        'status-unified-offer',
        'status-unified-progress',
        'status-unified-ready',
        'status-unified-issued',
        'status-unified-completed',
        'status-unified-cancelled',
        'status-unified-expired',
    ];

    function initBrowserBranding() {
        const config = document.getElementById('browser-branding-runtime');

        if (!config) {
            return;
        }

        const documentTitle = config.dataset.documentTitle || '';
        const faviconUrl = config.dataset.faviconUrl || '';

        if (documentTitle) {
            document.title = documentTitle;
        }

        if (!faviconUrl) {
            return;
        }

        let favicon = document.head.querySelector('link[data-configurable-site-favicon]');

        if (!favicon) {
            favicon = document.createElement('link');
            favicon.rel = 'icon';
            favicon.setAttribute('data-configurable-site-favicon', '1');
            document.head.appendChild(favicon);
        }

        favicon.href = faviconUrl;
    }

    function applyStatusColors(root = document) {
        const elements = root.matches?.('.offer-status-pill, .warehouse-status-badge, .branch-status-badge, .premium-badge')
            ? [root]
            : root.querySelectorAll?.('.offer-status-pill, .warehouse-status-badge, .branch-status-badge, .premium-badge') ?? [];

        elements.forEach((badge) => {
            const text = badge.textContent
                .trim()
                .toLocaleLowerCase('de-DE')
                .replace(/^status:\s*/, '');
            const statusClass = statusClasses[text];

            if (!statusClass) {
                return;
            }

            badge.classList.remove(...unifiedClasses);
            badge.classList.add(statusClass);
        });
    }

    function initUnifiedStatusColors() {
        applyStatusColors();

        if (!document.body) {
            return;
        }

        new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        applyStatusColors(node);
                    }
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    }

    function initCspEventHandlers() {
        document.addEventListener('click', (event) => {
            const openTrigger = event.target.closest?.('[data-dialog-open]');

            if (openTrigger) {
                const dialog = document.getElementById(openTrigger.dataset.dialogOpen || '');

                if (dialog?.showModal) {
                    event.preventDefault();
                    dialog.showModal();
                }

                return;
            }

            const closeTrigger = event.target.closest?.('[data-dialog-close]');

            if (closeTrigger) {
                const dialog = document.getElementById(closeTrigger.dataset.dialogClose || '');

                if (dialog?.close) {
                    event.preventDefault();
                    dialog.close();
                }

                return;
            }

            const confirmTrigger = event.target.closest?.('[data-confirm]');

            if (!confirmTrigger) {
                return;
            }

            const message = confirmTrigger.dataset.confirm || 'Aktion wirklich ausführen?';

            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });

        document.addEventListener('change', (event) => {
            const control = event.target.closest?.('[data-auto-submit]');
            const form = control?.form;

            if (!control || !form) {
                return;
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        });
    }

    function init() {
        initBrowserBranding();
        initUnifiedStatusColors();
        initCspEventHandlers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
