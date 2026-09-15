(() => {
    'use strict';

    function initManualPriceRulesRuntime() {
        const runtime = document.querySelector('[data-manual-price-rules-runtime]');
        const body = document.getElementById('manual-price-rules');
        const template = document.getElementById('manual-price-rule-template');
        const addButton = document.getElementById('add-price-rule');

        if (!runtime || !body || !template || !addButton || runtime.dataset.runtimeBound === '1') {
            return;
        }

        runtime.dataset.runtimeBound = '1';

        const rows = () => Array.from(body.querySelectorAll('[data-price-rule]'));

        const reindex = () => {
            rows().forEach((row, index) => {
                ['min_quantity', 'max_quantity', 'price', 'label'].forEach((field) => {
                    const input = row.querySelector(`[name*="[${field}]"]`) || row.querySelector(`[data-name="${field}"]`);

                    if (input) {
                        input.name = `rules[${index}][${field}]`;
                    }
                });
            });
        };

        const bindRemove = () => {
            body.querySelectorAll('.remove-price-rule').forEach((button) => {
                if (button.dataset.bound === '1') {
                    return;
                }

                button.dataset.bound = '1';
                button.addEventListener('click', () => {
                    const row = button.closest('[data-price-rule]');

                    if (!row) {
                        return;
                    }

                    if (rows().length === 1) {
                        row.querySelectorAll('input').forEach((input) => {
                            input.value = '';
                        });
                        return;
                    }

                    row.remove();
                    reindex();
                });
            });
        };

        addButton.addEventListener('click', () => {
            body.appendChild(template.content.cloneNode(true));
            reindex();
            bindRemove();
            rows().at(-1)?.querySelector('input')?.focus();
        });

        reindex();
        bindRemove();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initManualPriceRulesRuntime, { once: true });
    } else {
        initManualPriceRulesRuntime();
    }
})();
