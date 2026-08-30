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
        html body #offer-shipping-card.offer-shipping-modern-card .premium-form-grid {
            display: grid !important;
            grid-template-columns: minmax(300px, 1.2fr) minmax(220px, .8fr) minmax(190px, .6fr) !important;
            gap: 22px !important;
            align-items: start !important;
        }

        html body #offer-shipping-card.offer-shipping-modern-card .premium-form-field,
        html body #offer-shipping-card.offer-shipping-modern-card .premium-form-field.full,
        html body #offer-shipping-card.offer-shipping-modern-card .offer-shipping-carton-field {
            grid-column: auto !important;
            width: 100% !important;
            max-width: none !important;
            min-width: 0 !important;
            margin: 0 !important;
        }

        html body #offer-shipping-card.offer-shipping-modern-card #shipping_method,
        html body #offer-shipping-card.offer-shipping-modern-card #shipping_price_gross,
        html body #offer-shipping-card.offer-shipping-modern-card #shipping_price_net,
        html body #offer-shipping-card.offer-shipping-modern-card #carton_count {
            width: 100% !important;
            max-width: none !important;
            min-height: 52px !important;
            box-sizing: border-box !important;
        }

        html body #offer-shipping-card.offer-shipping-modern-card .premium-muted {
            margin-top: 8px !important;
            line-height: 1.4 !important;
        }

        @media (max-width: 1150px) {
            html body #offer-shipping-card.offer-shipping-modern-card .premium-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            html body #offer-shipping-card.offer-shipping-modern-card .offer-shipping-carton-field {
                grid-column: 1 / -1 !important;
                max-width: 360px !important;
            }
        }

        @media (max-width: 760px) {
            html body #offer-shipping-card.offer-shipping-modern-card .premium-form-grid {
                grid-template-columns: 1fr !important;
            }

            html body #offer-shipping-card.offer-shipping-modern-card .offer-shipping-carton-field {
                grid-column: auto !important;
                max-width: none !important;
            }
        }
    `;

    document.head.appendChild(style);
}

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

function initOfferShippingNetPrice() {
    const form = document.getElementById('offer-main-form');
    const field = document.getElementById('shipping_price_gross_field');
    const grossInput = document.getElementById('shipping_price_gross');
    const grossReal = document.getElementById('shipping_price_gross_real');
    const method = document.getElementById('shipping_method');

    if (!form || !field || !grossInput || !method) {
        return;
    }

    let netInput = document.getElementById('shipping_price_net');

    if (!netInput) {
        const label = field.querySelector('label[for="shipping_price_gross"], label');
        if (label) {
            label.setAttribute('for', 'shipping_price_net');
            label.textContent = 'Versandpreis netto';
        }

        const grossValue = Number.parseFloat(grossInput.value);

        netInput = document.createElement('input');
        netInput.id = 'shipping_price_net';
        netInput.type = 'number';
        netInput.step = '0.01';
        netInput.min = '0';
        netInput.className = grossInput.className || 'premium-input';
        netInput.placeholder = 'z. B. 5.80';
        netInput.value = Number.isFinite(grossValue)
            ? (grossValue / 1.19).toFixed(2)
            : '';

        grossInput.type = 'hidden';
        grossInput.removeAttribute('class');
        grossInput.insertAdjacentElement('beforebegin', netInput);

        const help = field.querySelector('.premium-muted');
        if (help) {
            help.textContent = 'Netto eingeben. 19 % MwSt. werden automatisch berechnet. Wird in Angebot und Rechnung angezeigt, nicht im Lieferschein.';
        }
    }

    function syncNetToGross() {
        const isDelivery = method.value === 'Lieferung';

        netInput.style.display = isDelivery ? '' : 'none';
        netInput.disabled = !isDelivery;

        if (!isDelivery) {
            netInput.value = '';
            grossInput.value = '';
            if (grossReal) grossReal.value = '';
            return;
        }

        const netValue = Number.parseFloat(netInput.value);
        const grossValue = Number.isFinite(netValue)
            ? (Math.round((netValue * 1.19 + Number.EPSILON) * 100) / 100).toFixed(2)
            : '';

        grossInput.value = grossValue;
        if (grossReal) grossReal.value = grossValue;
    }

    if (netInput.dataset.netShippingBound !== '1') {
        netInput.dataset.netShippingBound = '1';
        netInput.addEventListener('input', syncNetToGross);
        netInput.addEventListener('change', syncNetToGross);
    }

    if (method.dataset.netShippingBound !== '1') {
        method.dataset.netShippingBound = '1';
        method.addEventListener('input', syncNetToGross);
        method.addEventListener('change', syncNetToGross);
    }

    if (form.dataset.netShippingBound !== '1') {
        form.dataset.netShippingBound = '1';
        form.addEventListener('submit', syncNetToGross);
    }

    syncNetToGross();
}

function ensureExtendedErrorToastStyles() {
    if (document.getElementById('extended-error-toast-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'extended-error-toast-styles';
    style.textContent = `
        .premium-toast.error[data-extended-lifetime="1"]::after {
            animation-duration: 15s !important;
        }
    `;

    document.head.appendChild(style);
}

function extendErrorToastLifetime() {
    ensureExtendedErrorToastStyles();

    document.querySelectorAll('.premium-toast.error:not([data-extended-lifetime="1"])').forEach((toast) => {
        const replacement = toast.cloneNode(true);
        replacement.dataset.extendedLifetime = '1';
        toast.replaceWith(replacement);

        const closeButton = replacement.querySelector('.premium-toast-close');
        let removed = false;

        function removeReplacement() {
            if (removed || !replacement.isConnected) {
                return;
            }

            removed = true;
            replacement.style.animation = 'premiumToastOut .18s ease forwards';
            window.setTimeout(() => replacement.remove(), 180);
        }

        closeButton?.addEventListener('click', removeReplacement);
        window.setTimeout(removeReplacement, 15000);
    });
}

function initClickablePreviewRows() {
    if (!document.getElementById('clickable-preview-row-styles')) {
        const style = document.createElement('style');
        style.id = 'clickable-preview-row-styles';
        style.textContent = `
            table tbody tr[data-preview-row="1"] {
                cursor: pointer;
            }

            table tbody tr[data-preview-row="1"]:focus-visible {
                outline: 2px solid #d4aa20;
                outline-offset: -2px;
            }
        `;
        document.head.appendChild(style);
    }

    document.querySelectorAll('table tbody tr').forEach((row) => {
        if (row.dataset.previewRow === '1') {
            return;
        }

        const previewLink = row.querySelector('a[title="Vorschau"]');
        if (!previewLink?.href) {
            return;
        }

        row.dataset.previewRow = '1';
        row.tabIndex = 0;
        row.setAttribute('aria-label', 'Vorschau öffnen');

        const isInteractiveTarget = (target) => Boolean(target.closest(
            'a, button, form, input, select, textarea, label, [role="button"], [contenteditable="true"]'
        ));

        row.addEventListener('click', (event) => {
            if (isInteractiveTarget(event.target)) {
                return;
            }

            window.location.href = previewLink.href;
        });

        row.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' || isInteractiveTarget(event.target)) {
                return;
            }

            event.preventDefault();
            window.location.href = previewLink.href;
        });
    });
}

function initializeDynamicUi() {
    initCleanWarningsPageColors();
    initUnlimitedOfferQuantities();
    ensureOfferProductOnlyStyles();
    initOfferProductOnlyPositions();
    ensureOfferShippingCartonStyles();
    initOfferShippingCartonCount();
    initOfferShippingNetPrice();
    extendErrorToastLifetime();
    initClickablePreviewRows();
}

initializeDynamicUi();

document.addEventListener('DOMContentLoaded', initializeDynamicUi);
document.addEventListener('livewire:navigated', initializeDynamicUi);

new MutationObserver(initializeDynamicUi).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
