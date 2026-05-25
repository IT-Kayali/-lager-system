import TomSelect from 'tom-select';

function shouldUseSearch(select) {
    const name = select.getAttribute('name') || '';

    if (select.dataset.search === 'true') {
        return true;
    }

    if (select.dataset.search === 'false') {
        return false;
    }

    return (
        name.includes('customer_id') ||
        name.includes('product_id') ||
        name.includes('supplier_id') ||
        name.includes('selected_product_id')
    );
}

function initSearchableSelects() {
    document.querySelectorAll('select').forEach((select) => {
        if (select.tomselect) {
            return;
        }

        if (select.multiple) {
            return;
        }

        if (select.classList.contains('no-tomselect')) {
            return;
        }

        const enableSearch = shouldUseSearch(select);

        const firstOption = select.querySelector('option[value=""]');
        const placeholder =
            select.dataset.placeholder ||
            select.getAttribute('placeholder') ||
            firstOption?.textContent?.trim() ||
            'Auswählen';

        const instance = new TomSelect(select, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 1000,
            placeholder: placeholder,
            searchField: enableSearch ? ['text'] : [],
            controlInput: enableSearch ? '<input />' : null,
            dropdownParent: 'body',
            sortField: enableSearch
                ? {
                    field: 'text',
                    direction: 'asc',
                }
                : null,
            render: {
                no_results: function () {
                    return '<div class="ts-no-results">Keine Ergebnisse gefunden</div>';
                },
            },
        });

        /*
         * Wichtig:
         * Tom Select kopiert Klassen wie premium-select auf den Wrapper.
         * Genau das verursacht doppelte Rahmen und doppelte Pfeile.
         */
        instance.wrapper.classList.remove(
            'premium-select',
            'premium-input',
            'premium-filter-select'
        );

        instance.control.classList.add('premium-ts-control');
        instance.dropdown.classList.add('premium-ts-dropdown');
    });
}

window.initSearchableSelects = initSearchableSelects;

document.addEventListener('DOMContentLoaded', initSearchableSelects);
document.addEventListener('livewire:navigated', initSearchableSelects);

new MutationObserver(() => {
    initSearchableSelects();
}).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
