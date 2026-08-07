const nativeParseFloat = window.parseFloat.bind(window);
const nativeNumberParseFloat = Number.parseFloat.bind(Number);

function normalizeGermanNumber(value) {
    if (typeof value !== 'string') {
        return value;
    }

    let normalized = value
        .replace(/\u00a0/g, '')
        .replace(/\s+/g, '')
        .trim();

    if (!normalized) {
        return normalized;
    }

    const comma = normalized.lastIndexOf(',');
    const dot = normalized.lastIndexOf('.');

    if (comma !== -1 && dot !== -1) {
        if (comma > dot) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        } else {
            normalized = normalized.replace(/,/g, '');
        }
    } else if (comma !== -1) {
        normalized = normalized.replace(',', '.');
    }

    return normalized;
}

function parseGermanNumber(value) {
    return nativeParseFloat(normalizeGermanNumber(String(value ?? '')));
}

function formatGermanNumber(value, maximumFractionDigits = 2) {
    const parsed = parseGermanNumber(value);

    if (!Number.isFinite(parsed)) {
        return value;
    }

    return new Intl.NumberFormat('de-DE', {
        minimumFractionDigits: 2,
        maximumFractionDigits,
        useGrouping: true,
    }).format(parsed);
}

window.parseFloat = (value) => nativeParseFloat(normalizeGermanNumber(String(value ?? '')));
Number.parseFloat = (value) => nativeNumberParseFloat(normalizeGermanNumber(String(value ?? '')));
window.parseGermanNumber = parseGermanNumber;
window.formatGermanNumber = (value) => formatGermanNumber(value, 2);

const decimalFieldSelector = [
    'input[name="quantity"]',
    'input[name="minimum_stock"]',
    'input[name="amount"]',
    'input[name="shipping_price_gross"]',
    'input[name="tax_rate"]',
    'input[name^="prices["]',
    'input[name$="[quantity]"]',
    'input[data-name="quantity"]',
].join(',');

function isQuantityField(input) {
    const name = input.getAttribute('name') || '';

    return name === 'quantity'
        || name.endsWith('[quantity]')
        || input.dataset.name === 'quantity';
}

function enhanceDecimalField(input) {
    if (!(input instanceof HTMLInputElement) || input.dataset.germanNumberEnhanced === '1') {
        return;
    }

    input.dataset.germanNumberEnhanced = '1';
    input.dataset.originalType = input.type;
    input.type = 'text';
    input.inputMode = 'decimal';
    input.autocomplete = 'off';

    const format = () => {
        if (input.value.trim() !== '') {
            input.value = formatGermanNumber(input.value, isQuantityField(input) ? 3 : 2);
        }
    };

    input.addEventListener('blur', format);
    input.addEventListener('change', () => {
        if (document.activeElement !== input) {
            format();
        }
    });

    format();
}

export function initGermanNumbers() {
    document.querySelectorAll(decimalFieldSelector).forEach(enhanceDecimalField);
}

initGermanNumbers();
document.addEventListener('DOMContentLoaded', initGermanNumbers);
document.addEventListener('livewire:navigated', initGermanNumbers);

new MutationObserver(initGermanNumbers).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
