import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

function shouldEnhance(select) {
    if (!select || select.tomselect) {
        return false;
    }

    if (select.dataset.noSearch === 'true') {
        return false;
    }

    return (
        select.classList.contains('premium-select') ||
        select.name === 'customer_id' ||
        select.name === 'supplier_id' ||
        select.name === 'product_id' ||
        select.name === 'selected_product_id' ||
        select.name.includes('product_id') ||
        select.name.includes('customer_id') ||
        select.name.includes('supplier_id')
    );
}

window.initSearchableSelects = function () {
    document.querySelectorAll('select').forEach((select) => {
        if (!shouldEnhance(select)) {
            return;
        }

        const emptyOption = select.querySelector('option[value=""]');
        const placeholder = emptyOption ? emptyOption.textContent.trim() : 'Suchen...';

        new TomSelect(select, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 1000,
            placeholder: placeholder,
            searchField: ['text'],
            render: {
                no_results: function () {
                    return '<div class="no-results">Keine Ergebnisse gefunden</div>';
                },
            },
        });
    });
};

document.addEventListener('DOMContentLoaded', window.initSearchableSelects);
document.addEventListener('livewire:navigated', window.initSearchableSelects);

new MutationObserver(() => {
    window.initSearchableSelects();
}).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
