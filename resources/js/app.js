import Chart from 'chart.js/auto';
import 'tom-select/dist/css/tom-select.css';
import 'flag-icons/css/flag-icons.min.css';
import './searchable-selects';

window.Chart = Chart;


/**
 * Clean warnings page colors.
 * Only colors KPI boxes, filter buttons and table rows.
 */
function initCleanWarningsPageColors() {
    if (!window.location.pathname.includes('/warnings')) {
        return;
    }

    document
        .querySelectorAll('.warning-card-total, .warning-card-low, .warning-card-critical, .warning-filter-total, .warning-filter-low, .warning-filter-critical, .warning-row-low, .warning-row-critical')
        .forEach((el) => {
            el.classList.remove(
                'warning-card-total',
                'warning-card-low',
                'warning-card-critical',
                'warning-filter-total',
                'warning-filter-low',
                'warning-filter-critical',
                'warning-row-low',
                'warning-row-critical'
            );
        });

    const cards = Array.from(document.querySelectorAll('.premium-card'));

    cards.forEach((card) => {
        const text = card.textContent.replace(/\s+/g, ' ').trim();

        // Die große Tabelle/Section darf NICHT gefärbt werden.
        if (
            text.includes('Bestandswarnungen') ||
            text.includes('OK-Produkte') ||
            text.includes('Statuslogik')
        ) {
            return;
        }

        if (text.includes('Warnungen gesamt')) {
            card.classList.add('warning-card-total');
            return;
        }

        if (/^\s*\d+\s*Niedrig\s*$/.test(text) || text.endsWith(' Niedrig')) {
            card.classList.add('warning-card-low');
            return;
        }

        if (/^\s*\d+\s*Kritisch\s*$/.test(text) || text.endsWith(' Kritisch')) {
            card.classList.add('warning-card-critical');
        }
    });

    Array.from(document.querySelectorAll('a, button')).forEach((element) => {
        const text = element.textContent.replace(/\s+/g, ' ').trim();

        if (text === 'Warnungen') {
            element.classList.add('warning-filter-total');
        }

        if (text === 'Niedrig') {
            element.classList.add('warning-filter-low');
        }

        if (text === 'Kritisch') {
            element.classList.add('warning-filter-critical');
        }
    });

    Array.from(document.querySelectorAll('table tbody tr')).forEach((row) => {
        const text = row.textContent.replace(/\s+/g, ' ').trim();

        if (text.includes('Kritisch')) {
            row.classList.add('warning-row-critical');
        } else if (text.includes('Niedrig')) {
            row.classList.add('warning-row-low');
        }
    });
}

/**
 * Offer quantities are limited by available stock and the database, not by the
 * old 5,000 gram browser limit. This also covers dynamically added rows.
 */
function initUnlimitedOfferQuantities() {
    if (!window.location.pathname.includes('/offers')) {
        return;
    }

    document
        .querySelectorAll('input[name$="[quantity]"], input[data-name="quantity"]')
        .forEach((input) => input.removeAttribute('max'));
}

function productNameOnly(label) {
    return (label || '')
        .replace(/\s+/g, ' ')
        .trim()
        .split('|')[0]
        .trim();
}

function restoreAllOfferProducts(productSelect) {
    const serializedOptions = productSelect.dataset.originalOptions;

    if (!serializedOptions) {
        productSelect.disabled = false;
        productSelect.tomselect?.enable();
        return;
    }

    let options;

    try {
        options = JSON.parse(serializedOptions);
    } catch (error) {
        productSelect.disabled = false;
        productSelect.tomselect?.enable();
        return;
    }

    if (!Array.isArray(options) || options.length === 0) {
        productSelect.disabled = false;
        productSelect.tomselect?.enable();
        return;
    }

    const currentValue = productSelect.value;
    const tomSelect = productSelect.tomselect;

    if (tomSelect) {
        tomSelect.clear(true);
        tomSelect.clearOptions();
    }

    productSelect.replaceChildren();

    options.forEach((optionData) => {
        const option = document.createElement('option');
        option.value = optionData.value;
        option.textContent = optionData.value === ''
            ? 'Produkt auswählen'
            : productNameOnly(optionData.text);
        option.disabled = Boolean(optionData.disabled);
        option.selected = optionData.value === currentValue;
        productSelect.appendChild(option);

        if (tomSelect) {
            tomSelect.addOption({
                value: option.value,
                text: option.textContent,
            });
        }
    });

    productSelect.disabled = false;

    if (tomSelect) {
        tomSelect.enable();
        tomSelect.setValue(currentValue, true);
        tomSelect.refreshOptions(false);
        tomSelect.refreshItems();
    }
}

/**
 * Categories remain available everywhere in the system. In the offer editor,
 * however, positions intentionally consist only of product + quantity.
 *
 * Older offer templates still contain the optional category-filter enhancer.
 * Marking the product select as already handled prevents that enhancer from
 * inserting the category dropdown. The fallback cleanup also handles a field
 * that may already have been inserted during navigation or a DOM refresh.
 */
function initOfferProductOnlyPositions() {
    const offerItems = document.querySelector('#offer-main-form #offer-items');

    if (!offerItems) {
        return;
    }

    document.body.classList.add('offer-product-only-positions');

    offerItems
        .querySelectorAll('select[name*="[product_id]"], select[data-name="product_id"]')
        .forEach((productSelect) => {
            productSelect.dataset.categoryFilterEnhanced = '1';
            restoreAllOfferProducts(productSelect);

            const row = productSelect.closest('.offer-item-row');
            const grid = productSelect.closest('.premium-form-grid');

            row?.querySelectorAll('.offer-category-filter-field').forEach((field) => field.remove());
            grid?.classList.remove('offer-category-product-row');
        });
}

function ensureOfferProductOnlyStyles() {
    if (document.getElementById('offer-product-only-position-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'offer-product-only-position-styles';
    style.textContent = `
        body.offer-product-only-positions .offer-category-filter-field {
            display: none !important;
        }

        body.offer-product-only-positions .offer-item-row > .premium-form-grid {
            grid-template-columns: minmax(320px, 1.8fr) minmax(160px, .65fr) auto !important;
        }

        @media (max-width: 900px) {
            body.offer-product-only-positions .offer-item-row > .premium-form-grid {
                grid-template-columns: minmax(0, 1fr) minmax(150px, .55fr) auto !important;
            }
        }

        @media (max-width: 700px) {
            body.offer-product-only-positions .offer-item-row > .premium-form-grid {
                grid-template-columns: 1fr !important;
            }
        }
    `;

    document.head.appendChild(style);
}

function initializeDynamicUi() {
    initCleanWarningsPageColors();
    initUnlimitedOfferQuantities();
    ensureOfferProductOnlyStyles();
    initOfferProductOnlyPositions();
}

initializeDynamicUi();

document.addEventListener('DOMContentLoaded', initializeDynamicUi);
document.addEventListener('livewire:navigated', initializeDynamicUi);

new MutationObserver(initializeDynamicUi).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
