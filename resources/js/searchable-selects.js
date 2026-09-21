import './german-numbers';
import TomSelect from 'tom-select';

const collator = new Intl.Collator('de-DE', {
    numeric: true,
    sensitivity: 'base',
});

const DROPDOWN_VIEWPORT_GAP = 10;
const DROPDOWN_MIN_SPACE = 180;
const DROPDOWN_MAX_CONTENT_HEIGHT = 320;

let activeDropdown = null;
let dropdownPositionFrame = null;

function normalizeText(text) {
    return (text || '').replace(/\s+/g, ' ').trim();
}

function shouldUseSearch(select) {
    const name = select.getAttribute('name') || '';

    if (select.dataset.search === 'true') return true;
    if (select.dataset.search === 'false') return false;

    return (
        name.includes('customer_id') ||
        name.includes('product_id') ||
        name.includes('supplier_id') ||
        name.includes('selected_product_id') ||
        name.includes('country_code')
    );
}

function isProductSelect(select) {
    const name = select.getAttribute('name') || '';
    const dataName = select.dataset.name || '';

    return name.includes('product_id') || dataName === 'product_id';
}

function isOfferProductSelect(select) {
    return isProductSelect(select) && Boolean(select.closest('#offer-main-form'));
}

function isOfferPlaceholderSelect(select) {
    const name = select.getAttribute('name') || '';
    const id = select.getAttribute('id') || '';

    return (
        Boolean(select.closest('#offer-main-form'))
        && (
            name === 'customer_id'
            || name === 'template_type'
            || isProductSelect(select)
        )
    ) || id === 'shipping_method';
}

function isBranchWithdrawalProductSelect(select) {
    return isProductSelect(select) && Boolean(select.closest('.branch-editor-form'));
}

function isCountrySelect(select) {
    return select.classList.contains('phone-country-select');
}

function isStatusOrFixedOrderSelect(select) {
    const name = select.getAttribute('name') || '';
    const id = select.getAttribute('id') || '';

    return (
        select.dataset.sort === 'false' ||
        name === 'status' ||
        id.includes('status') ||
        name.includes('status') ||
        name === 'direction' ||
        name === 'unit' ||
        name === 'pdf_template_id' ||
        name === 'template_id'
    );
}

function isPlaceholderOption(option) {
    const text = normalizeText(option.textContent).toLowerCase();

    return (
        option.value === '' ||
        option.disabled ||
        text.includes('auswählen') ||
        text.includes('bitte wählen')
    );
}

function sortKey(option) {
    let text = normalizeText(option.dataset.name || option.textContent);

    if (text.includes('|')) {
        text = text.split('|')[0].trim();
    }

    return text;
}

function normalizeProductOptionLabels(select) {
    if (!isProductSelect(select)) return;

    select.querySelectorAll('option').forEach((option) => {
        if (isPlaceholderOption(option)) return;

        const originalText = normalizeText(option.dataset.productName || option.textContent);
        if (!originalText) return;

        let productName = originalText;
        const pipeIndex = productName.indexOf('|');

        if (pipeIndex !== -1) {
            productName = productName.slice(0, pipeIndex).trim();

            const codeSeparatorIndex = productName.lastIndexOf(' — ');
            if (codeSeparatorIndex > 0) {
                productName = productName.slice(0, codeSeparatorIndex).trim();
            }
        }

        if (!productName) return;

        option.dataset.productName = productName;
        option.textContent = productName;
    });
}

function sortSelectOptions(select) {
    if (isStatusOrFixedOrderSelect(select)) return;

    const oldValue = select.value;
    const hadExplicitSelected = Array.from(select.options).some((option) => {
        return option.hasAttribute('selected') && option.value !== '';
    });

    const options = Array.from(select.options);
    if (options.length <= 1) return;

    const placeholders = options.filter(isPlaceholderOption);
    const realOptions = options.filter((option) => !isPlaceholderOption(option));

    realOptions.sort((a, b) => {
        if (isCountrySelect(select)) {
            const aIso = (a.dataset.iso || '').toUpperCase();
            const bIso = (b.dataset.iso || '').toUpperCase();

            if (aIso === 'DE') return -1;
            if (bIso === 'DE') return 1;
        }

        return collator.compare(sortKey(a), sortKey(b));
    });

    select.replaceChildren(...placeholders, ...realOptions);

    if (!oldValue && !hadExplicitSelected && placeholders.length > 0 && !isCountrySelect(select)) {
        select.value = '';
        placeholders[0].selected = true;
        return;
    }

    if ((!oldValue || oldValue === '+49') && isCountrySelect(select)) {
        const germany = Array.from(select.options).find((option) => option.value === '+49|DE');

        if (germany) {
            select.value = '+49|DE';
            germany.selected = true;
            return;
        }
    }

    if (oldValue) {
        select.value = oldValue;
    }
}

function clearDropdownPosition(instance) {
    if (!instance?.dropdown) return;

    instance.dropdown.classList.remove('ts-dropdown-fixed', 'dropdown-above');
    delete instance.dropdown.dataset.cspTop;
    delete instance.dropdown.dataset.cspLeft;
    delete instance.dropdown.dataset.cspWidth;
    delete instance.dropdown.dataset.cspMaxWidth;

    const content = instance.dropdown.querySelector('.ts-dropdown-content');

    if (content) {
        delete content.dataset.cspMaxHeight;
    }
}

function positionActiveDropdown() {
    dropdownPositionFrame = null;

    const instance = activeDropdown;

    if (!instance?.isOpen || !instance.control || !instance.dropdown) {
        return;
    }

    const controlRect = instance.control.getBoundingClientRect();

    if (
        controlRect.width <= 0 ||
        controlRect.height <= 0 ||
        controlRect.bottom < 0 ||
        controlRect.top > window.innerHeight
    ) {
        instance.close();
        return;
    }

    const availableBelow = window.innerHeight - controlRect.bottom - DROPDOWN_VIEWPORT_GAP;
    const availableAbove = controlRect.top - DROPDOWN_VIEWPORT_GAP;
    const openAbove = availableBelow < DROPDOWN_MIN_SPACE && availableAbove > availableBelow;
    const availableSpace = Math.max(110, openAbove ? availableAbove : availableBelow);

    const dropdown = instance.dropdown;
    const content = dropdown.querySelector('.ts-dropdown-content');
    const viewportWidth = Math.max(0, window.innerWidth - (DROPDOWN_VIEWPORT_GAP * 2));
    const width = Math.min(controlRect.width, viewportWidth);
    const left = Math.min(
        Math.max(DROPDOWN_VIEWPORT_GAP, controlRect.left),
        Math.max(DROPDOWN_VIEWPORT_GAP, window.innerWidth - width - DROPDOWN_VIEWPORT_GAP)
    );

    dropdown.classList.add('ts-dropdown-fixed');
    dropdown.classList.toggle('dropdown-above', openAbove);

    dropdown.dataset.cspLeft = `${Math.round(left)}px`;
    dropdown.dataset.cspWidth = `${Math.round(width)}px`;
    dropdown.dataset.cspMaxWidth = `${Math.round(viewportWidth)}px`;

    if (content) {
        const dropdownChrome = Math.max(18, dropdown.offsetHeight - content.offsetHeight);
        const contentHeight = Math.max(
            90,
            Math.min(DROPDOWN_MAX_CONTENT_HEIGHT, availableSpace - dropdownChrome)
        );

        content.dataset.cspMaxHeight = `${Math.floor(contentHeight)}px`;
    }

    const dropdownHeight = dropdown.getBoundingClientRect().height;
    const proposedTop = openAbove
        ? controlRect.top - dropdownHeight - DROPDOWN_VIEWPORT_GAP
        : controlRect.bottom + DROPDOWN_VIEWPORT_GAP;
    const top = Math.min(
        Math.max(DROPDOWN_VIEWPORT_GAP, proposedTop),
        Math.max(DROPDOWN_VIEWPORT_GAP, window.innerHeight - dropdownHeight - DROPDOWN_VIEWPORT_GAP)
    );

    dropdown.dataset.cspTop = `${Math.round(top)}px`;
}

function scheduleDropdownPosition() {
    if (!activeDropdown || dropdownPositionFrame !== null) {
        return;
    }

    dropdownPositionFrame = window.requestAnimationFrame(positionActiveDropdown);
}

function activateDropdownPosition(instance) {
    if (activeDropdown && activeDropdown !== instance) {
        clearDropdownPosition(activeDropdown);
    }

    activeDropdown = instance;
    scheduleDropdownPosition();
}

function deactivateDropdownPosition(instance) {
    clearDropdownPosition(instance);

    if (activeDropdown === instance) {
        activeDropdown = null;
    }
}

window.addEventListener('scroll', scheduleDropdownPosition, true);
window.addEventListener('resize', scheduleDropdownPosition);
window.visualViewport?.addEventListener('resize', scheduleDropdownPosition);
window.visualViewport?.addEventListener('scroll', scheduleDropdownPosition);

function initSearchableSelects() {
    document.querySelectorAll('select').forEach((select) => {
        if (select.tomselect) return;
        if (select.multiple) return;
        if (select.classList.contains('no-tomselect')) return;

        normalizeProductOptionLabels(select);
        sortSelectOptions(select);

        const enableSearch = shouldUseSearch(select);
        const countrySelect = isCountrySelect(select);
        const offerProductSelect = isOfferProductSelect(select);
        const offerPlaceholderSelect = isOfferPlaceholderSelect(select);
        const branchWithdrawalProductSelect = isBranchWithdrawalProductSelect(select);

        const firstOption = select.querySelector('option[value=""]');
        const placeholder =
            select.dataset.placeholder ||
            select.getAttribute('placeholder') ||
            firstOption?.textContent?.trim() ||
            'Auswählen';

        const instance = new TomSelect(select, {
            create: false,
            allowEmptyOption: !offerPlaceholderSelect && !branchWithdrawalProductSelect,
            maxOptions: 1000,
            placeholder: placeholder,
            searchField: enableSearch ? ['text', 'name', 'dial'] : [],
            controlInput: enableSearch ? '<input />' : null,
            dropdownParent: 'body',
            sortField: [{ field: '$order', direction: 'asc' }],

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

        instance.on('dropdown_open', () => activateDropdownPosition(instance));
        instance.on('dropdown_close', () => deactivateDropdownPosition(instance));

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