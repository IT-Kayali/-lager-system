import Chart from 'chart.js/auto';

window.Chart = Chart;

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

window.initSearchableSelects = function () {
    const selectors = [
        'select[name="customer_id"]',
        'select[name="product_id"]',
        'select[name="supplier_id"]',
        'select[name="selected_product_id"]'
    ];

    document.querySelectorAll(selectors.join(',')).forEach((select) => {
        if (select.tomselect) {
            return;
        }

        new TomSelect(select, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 1000,
            searchField: ['text'],
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            plugins: ['dropdown_input'],
            render: {
                no_results: function () {
                    return '<div class="no-results">Keine Ergebnisse gefunden</div>';
                }
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', function () {
    window.initSearchableSelects();
});

document.addEventListener('livewire:navigated', function () {
    window.initSearchableSelects();
});

const searchableSelectObserver = new MutationObserver(function () {
    window.initSearchableSelects();
});

searchableSelectObserver.observe(document.documentElement, {
    childList: true,
    subtree: true
});
