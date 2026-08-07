<?php

it('normalizes product dropdown labels to the product name only', function () {
    $script = file_get_contents(resource_path('js/searchable-selects.js'));

    expect($script)
        ->toContain('function isProductSelect(select)')
        ->toContain("name.includes('product_id') || dataName === 'product_id'")
        ->toContain('function normalizeProductOptionLabels(select)')
        ->toContain('option.textContent = productName;')
        ->toContain('normalizeProductOptionLabels(select);');

    expect($script)->not->toContain('normalizeOfferProductOptionLabels');
});
