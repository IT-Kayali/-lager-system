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

function ensureOfferShippingCartonStyles() {
    if (document.getElementById('offer-shipping-carton-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'offer-shipping-carton-styles';
    style.textContent = `
        #offer-shipping-card .premium-form-grid {
            grid-template-columns: minmax(260px, 1.15fr) minmax(210px, .8fr) minmax(170px, .55fr) !important;
        }

        #offer-shipping-card .offer-shipping-carton-field {
            grid-column: auto !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
        }

        #offer-shipping-card #carton_count {
            width: 100% !important;
            max-width: none !important;
        }

        @media (max-width: 1150px) {
            #offer-shipping-card .premium-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 760px) {
            #offer-shipping-card .premium-form-grid {
                grid-template-columns: 1fr !important;
            }
        }
    `;

    document.head.appendChild(style);
}

/**
 * Carton count is operational information only. It is stored with the offer
 * for later use on the delivery note and never participates in price or stock
 * calculations.
 */
function initOfferShippingCartonCount() {
    const form = document.getElementById('offer-main-form');
    const shippingCard = document.getElementById('offer-shipping-card');
    const method = document.getElementById('shipping_method');
    const hiddenCartonCount = document.getElementById('carton_count_real');

    if (!form || !shippingCard || !method || !hiddenCartonCount) {
        return;
    }

    const heading = shippingCard.querySelector('h3');
    if (heading && heading.textContent.trim() === 'Versand') {
        heading.textContent = 'Versandmethode';
    }

    const grid = shippingCard.querySelector('.premium-form-grid');
    if (!grid) {
        return;
    }

    let cartonField = document.getElementById('carton_count_field');
    let cartonInput = document.getElementById('carton_count');

    if (!cartonField) {
        cartonField = document.createElement('div');
        cartonField.id = 'carton_count_field';
        cartonField.className = 'premium-form-field full offer-shipping-carton-field';

        const label = document.createElement('label');
        label.setAttribute('for', 'carton_count');
        label.textContent = 'Anzahl Kartons *';

        cartonInput = document.createElement('input');
        cartonInput.id = 'carton_count';
        cartonInput.type = 'number';
        cartonInput.step = '1';
        cartonInput.min = '1';
        cartonInput.max = '9999';
        cartonInput.inputMode = 'numeric';
        cartonInput.className = 'premium-input';
        cartonInput.placeholder = 'z. B. 3';
        cartonInput.setAttribute('form', 'offer-main-form');
        cartonInput.value = hiddenCartonCount.value || '';

        const help = document.createElement('div');
        help.className = 'premium-muted';
        help.style.marginTop = '6px';
        help.textContent = 'Nur bei Lieferung. Hat keinen Einfluss auf Preis oder Versandkosten.';

        cartonField.append(label, cartonInput, help);
        grid.appendChild(cartonField);

        const serverError = form.querySelector('[data-carton-count-error]');
        if (serverError) {
            serverError.hidden = false;
            cartonField.appendChild(serverError);
        }
    }

    if (!cartonInput) {
        return;
    }

    function syncCartonCount() {
        const isDelivery = method.value === 'Lieferung';

        cartonField.style.display = isDelivery ? '' : 'none';
        cartonInput.disabled = !isDelivery;
        cartonInput.required = isDelivery;

        if (!isDelivery) {
            cartonInput.value = '';
            hiddenCartonCount.value = '';
            return;
        }

        hiddenCartonCount.value = cartonInput.value || '';
    }

    if (method.dataset.cartonCountBound !== '1') {
        method.dataset.cartonCountBound = '1';
        method.addEventListener('change', syncCartonCount);
        method.addEventListener('input', syncCartonCount);
    }

    if (cartonInput.dataset.cartonCountBound !== '1') {
        cartonInput.dataset.cartonCountBound = '1';
        cartonInput.addEventListener('change', syncCartonCount);
        cartonInput.addEventListener('input', syncCartonCount);
    }

    if (form.dataset.cartonCountBound !== '1') {
        form.dataset.cartonCountBound = '1';
        form.addEventListener('submit', syncCartonCount);
    }

    syncCartonCount();
}

function initializeDynamicUi() {
    initCleanWarningsPageColors();
    initUnlimitedOfferQuantities();
    ensureOfferProductOnlyStyles();
    initOfferProductOnlyPositions();
    ensureOfferShippingCartonStyles();
    initOfferShippingCartonCount();
}

initializeDynamicUi();

document.addEventListener('DOMContentLoaded', initializeDynamicUi);
document.addEventListener('livewire:navigated', initializeDynamicUi);

new MutationObserver(initializeDynamicUi).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
