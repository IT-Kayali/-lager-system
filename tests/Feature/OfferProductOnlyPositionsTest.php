<?php

it('disables the category filter only inside the offer editor', function () {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)
        ->toContain("document.querySelector('#offer-main-form #offer-items')")
        ->toContain("productSelect.dataset.categoryFilterEnhanced = '1'")
        ->toContain('offer-product-only-positions')
        ->toContain('offer-category-filter-field');
});

it('keeps the category system files available', function () {
    expect(file_exists(app_path('Models/ProductCategory.php')))->toBeTrue()
        ->and(file_exists(resource_path('views/pages/categories/index.blade.php')))->toBeTrue();
});
