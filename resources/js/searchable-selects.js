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
        name.includes('selected_product_id') ||
        name.includes('country_code')
    );
}

function isCountrySelect(select) {
    return select.classList.contains('phone-country-select');
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
        const countrySelect = isCountrySelect(select);

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
            searchField: enableSearch ? ['text', 'name', 'dial'] : [],
            controlInput: enableSearch ? '<input />' : null,
            dropdownParent: 'body',
            sortField: null,
            render: {
                option: function (data, escape) {
                    if (!countrySelect) {
                        return '<div>' + escape(data.text) + '</div>';
                    }

                    const iso = (data.iso || '').toLowerCase();
                    const name = data.name || data.text || '';
                    const dial = data.dial || '';

                    return `
                        <div class="ts-country-option">
                            <span class="fi fi-${escape(iso)}"></span>
                            <span class="ts-country-name">${escape(name)}</span>
                            <strong class="ts-country-dial">${escape(dial)}</strong>
                        </div>
                    `;
                },

                item: function (data, escape) {
                    if (!countrySelect) {
                        return '<div>' + escape(data.text) + '</div>';
                    }

                    const iso = (data.iso || '').toLowerCase();
                    const name = data.name || data.text || '';
                    const dial = data.dial || '';

                    return `
                        <div class="ts-country-item">
                            <span class="fi fi-${escape(iso)}"></span>
                            <span>${escape(name)}</span>
                            <strong>${escape(dial)}</strong>
                        </div>
                    `;
                },

                no_results: function () {
                    return '<div class="ts-no-results">Keine Ergebnisse gefunden</div>';
                },
            },
        });

        instance.wrapper.classList.remove(
            'premium-select',
            'premium-input',
            'premium-filter-select'
        );
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
