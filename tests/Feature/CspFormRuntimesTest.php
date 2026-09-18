<?php

test('product and branch withdrawal forms use external runtimes', function () {
    $product = file_get_contents(
        resource_path('views/pages/products/_form.blade.php')
    );

    $withdrawal = file_get_contents(
        resource_path('views/pages/branch-withdrawals/_form.blade.php')
    );

    $productRuntime = file_get_contents(
        public_path('js/product-initial-batches-runtime.js')
    );

    $withdrawalRuntime = file_get_contents(
        public_path('js/branch-withdrawal-runtime.js')
    );

    foreach ([$product, $withdrawal] as $view) {
        preg_match_all(
            '/<script\b(?![^>]*\bsrc\s*=)[^>]*>/i',
            $view,
            $matches
        );

        expect($matches[0])->toBe([]);
    }

    expect($product)
        ->toContain(
            "asset('js/product-initial-batches-runtime.js')"
        );

    expect($withdrawal)
        ->toContain(
            "asset('js/branch-withdrawal-runtime.js')"
        );

    expect($productRuntime)
        ->toContain('initial-batches-wrapper')
        ->toContain('initial-batch-template')
        ->toContain('ensureEmptyRow');

    expect($withdrawalRuntime)
        ->toContain('branch-withdrawal-items')
        ->toContain('branch-item-template')
        ->toContain('initSearchableSelects')
        ->toContain('ensureTrailingRow');
});
