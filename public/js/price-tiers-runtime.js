(() => {
    function initPriceTiersRuntime() {
        const form = document.querySelector('[data-price-tiers-runtime]');

        if (!form || form.dataset.priceTiersBound === '1') {
            return;
        }

        const rows = document.getElementById('price-tier-rows');
        const template = document.getElementById('price-tier-row-template');
        const addButton = document.getElementById('add-price-tier');

        if (!rows || !template || !addButton) {
            return;
        }

        form.dataset.priceTiersBound = '1';

        let nextIndex = Number.parseInt(form.dataset.nextIndex || '0', 10);

        if (!Number.isFinite(nextIndex) || nextIndex < 0) {
            nextIndex = 0;
        }

        function refreshRange(row) {
            const min = row.querySelector('[data-tier-min]')?.value || '–';
            const max = row.querySelector('[data-tier-max]')?.value || '';
            const range = row.querySelector('[data-tier-range]');

            if (range) {
                range.textContent = max === ''
                    ? `ab ${min} Gramm`
                    : `${min}–${max} Gramm`;
            }
        }

        function bindRow(row) {
            if (!row || row.dataset.priceTierBound === '1') {
                return;
            }

            row.dataset.priceTierBound = '1';

            row.querySelectorAll('[data-tier-min], [data-tier-max]').forEach((input) => {
                input.addEventListener('input', () => refreshRange(row));
            });

            row.querySelector('[data-remove-price-tier]')?.addEventListener('click', () => {
                if (rows.querySelectorAll('[data-price-tier-row]').length <= 1) {
                    window.alert('Mindestens eine Preisstufe muss bestehen bleiben.');
                    return;
                }

                if (window.confirm('Preisstufe wirklich entfernen? Die aktuellen Preisfelder dieser Stufe werden beim Speichern gelöscht.')) {
                    row.remove();
                }
            });

            refreshRange(row);
        }

        rows.querySelectorAll('[data-price-tier-row]').forEach(bindRow);

        addButton.addEventListener('click', () => {
            const previousRow = rows.lastElementChild;
            const previousMaxValue = previousRow
                ?.querySelector('[data-tier-max]')
                ?.value
                ?.trim();
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('[data-price-tier-row]');
            const index = nextIndex++;

            if (!row) {
                return;
            }

            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `tiers[${index}][${input.dataset.field}]`;
            });

            if (previousMaxValue && !Number.isNaN(Number(previousMaxValue))) {
                row.querySelector('[data-tier-min]').value = String(Number(previousMaxValue) + 1);
            }

            rows.appendChild(fragment);
            bindRow(rows.lastElementChild);
            rows.lastElementChild?.querySelector('[data-tier-label]')?.focus();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPriceTiersRuntime, { once: true });
    } else {
        initPriceTiersRuntime();
    }
})();
