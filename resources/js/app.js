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

function initializeDynamicUi() {
    initCleanWarningsPageColors();
    initUnlimitedOfferQuantities();
}

document.addEventListener('DOMContentLoaded', initializeDynamicUi);
document.addEventListener('livewire:navigated', initializeDynamicUi);

new MutationObserver(initializeDynamicUi).observe(document.documentElement, {
    childList: true,
    subtree: true,
});
