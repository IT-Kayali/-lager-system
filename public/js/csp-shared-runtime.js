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

            if (!confirmTrigger || confirmTrigger.matches('form')) {
                return;
            }

            const message = confirmTrigger.dataset.confirm || 'Aktion wirklich ausführen?';

            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest?.('form[data-confirm]');

            if (!form) {
                return;
            }

            const message = form.dataset.confirm || 'Aktion wirklich ausführen?';

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

    function initProductEditorRuntime() {
        const form = document.querySelector('[data-product-editor-runtime]');

        if (!form) {
            return;
        }

        const label = form.querySelector('label[for="manufacturer_designation"]')
            || document.querySelector('label[for="manufacturer_designation"]');

        if (label) {
            label.textContent = 'Fake Name';
        }

        if (!form.hasAttribute('data-product-create-runtime')) {
            return;
        }

        const categoryId = new URLSearchParams(window.location.search).get('category_id');

        if (!categoryId) {
            return;
        }

        const categoryCheckbox = form.querySelector(`input[name="category_ids[]"][value="${CSS.escape(categoryId)}"]`);

        if (categoryCheckbox) {
            categoryCheckbox.checked = true;
            categoryCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function initSalesBranchCreateRuntime() {
        const form = document.querySelector('[data-sales-branch-create]');

        if (!form) {
            return;
        }

        const statusSelect = form.querySelector('select[name="status"]');
        const openStatus = form.dataset.openStatus || '';

        if (statusSelect && openStatus) {
            statusSelect.value = openStatus;
            statusSelect.disabled = true;
        }

        const defaultIndexUrl = form.dataset.defaultIndexUrl || '';
        const salesIndexUrl = form.dataset.salesIndexUrl || '';

        if (!defaultIndexUrl || !salesIndexUrl) {
            return;
        }

        form.querySelectorAll('a[href]').forEach((link) => {
            if (link.href === defaultIndexUrl || link.getAttribute('href') === defaultIndexUrl) {
                link.href = salesIndexUrl;
            }
        });
    }

    function initCustomerFormRuntime() {
        const checkbox = document.querySelector('[data-customer-form-runtime]');

        if (!checkbox) {
            return;
        }

        const card = document.getElementById('delivery-address-card');
        const deliveryFields = card ? Array.from(card.querySelectorAll('input, textarea, select')) : [];
        const groupSelect = document.getElementById('customer_group_id');
        const groupPreviewWrap = document.getElementById('customer-group-preview-wrap');
        const groupPreview = document.getElementById('customer-group-preview');

        function updateDeliveryAddressVisibility() {
            if (!card) {
                return;
            }

            const isDifferent = checkbox.checked;
            card.hidden = !isDifferent;

            deliveryFields.forEach((field) => {
                field.disabled = !isDifferent;
            });
        }

        function updateCustomerGroupPreview() {
            if (!groupSelect || !groupPreviewWrap || !groupPreview) {
                return;
            }

            const option = groupSelect.selectedOptions[0];

            if (!option || !option.value) {
                groupPreviewWrap.hidden = true;
                groupPreview.textContent = '';
                return;
            }

            const color = option.dataset.color || '#475569';
            const textColor = option.dataset.textColor || '#FFFFFF';

            groupPreview.textContent = option.textContent.trim();
            groupPreview.style.backgroundColor = color;
            groupPreview.style.borderColor = color;
            groupPreview.style.color = textColor;
            groupPreviewWrap.hidden = false;
        }

        checkbox.addEventListener('change', updateDeliveryAddressVisibility);
        groupSelect?.addEventListener('change', updateCustomerGroupPreview);
        updateDeliveryAddressVisibility();
        updateCustomerGroupPreview();
    }

    function initBatchExpiryRuntime() {
        const expires = document.querySelector('[data-batch-expiry-runtime]');
        const received = document.getElementById('received_at');

        if (!received || !expires || expires.dataset.expiryBound === '1') {
            return;
        }

        expires.dataset.expiryBound = '1';
        let autoExpiry = expires.dataset.autoExpiry === '1';
        const defaultMonths = Number.parseInt(expires.dataset.defaultMonths || '24', 10);

        function toIsoDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function addMonthsNoOverflow(isoDate, months) {
            if (!isoDate) {
                return '';
            }

            const [year, month, day] = isoDate.split('-').map(Number);

            if (!year || !month || !day) {
                return '';
            }

            const targetMonthIndex = (month - 1) + months;
            const targetYear = year + Math.floor(targetMonthIndex / 12);
            const targetMonth = ((targetMonthIndex % 12) + 12) % 12;
            const lastDay = new Date(targetYear, targetMonth + 1, 0).getDate();
            const safeDay = Math.min(day, lastDay);

            return toIsoDate(new Date(targetYear, targetMonth, safeDay));
        }

        function updateAutomaticExpiry() {
            if (!autoExpiry) {
                return;
            }

            expires.value = addMonthsNoOverflow(received.value, defaultMonths);
        }

        function disableAutomaticExpiry() {
            autoExpiry = false;
            expires.dataset.autoExpiry = '0';
        }

        received.addEventListener('change', updateAutomaticExpiry);
        received.addEventListener('input', updateAutomaticExpiry);
        expires.addEventListener('input', disableAutomaticExpiry);
        expires.addEventListener('change', disableAutomaticExpiry);
        updateAutomaticExpiry();
    }

    function init() {
        initBrowserBranding();
        initUnifiedStatusColors();
        initCspEventHandlers();
        initProductEditorRuntime();
        initSalesBranchCreateRuntime();
        initCustomerFormRuntime();
        initBatchExpiryRuntime();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
