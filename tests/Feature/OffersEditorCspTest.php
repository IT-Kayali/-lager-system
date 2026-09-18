<?php

test('offer editor uses one external CSP safe runtime', function () {
    $form = file_get_contents(
        resource_path('views/pages/offers/_form.blade.php')
    );

    $create = file_get_contents(
        resource_path('views/pages/offers/create.blade.php')
    );

    $edit = file_get_contents(
        resource_path('views/pages/offers/edit.blade.php')
    );

    $runtime = file_get_contents(
        public_path('js/offers-editor-runtime.js')
    );

    foreach ([
        '_form' => $form,
        'create' => $create,
        'edit' => $edit,
    ] as $name => $view) {
        preg_match_all(
            '/<script\b(?![^>]*\bsrc\s*=)[^>]*>/i',
            $view,
            $matches
        );

        expect(
            $matches[0],
            "{$name} still contains inline scripts"
        )->toBe([]);
    }

    expect($form)
        ->not->toContain(
            "@json(route('offers.price-preview'))"
        );

    expect($create)
        ->toContain(
            "asset('js/offers-editor-runtime.js')"
        )
        ->toContain(
            'id="offer-editor-runtime-config"'
        )
        ->toContain(
            'data-price-preview-url='
        )
        ->toContain(
            'data-offer-categories='
        )
        ->toContain(
            'data-product-category-map='
        )
        ->toContain(
            'data-required-validation="1"'
        );

    expect($edit)
        ->toContain(
            "asset('js/offers-editor-runtime.js')"
        )
        ->toContain(
            'id="offer-editor-runtime-config"'
        )
        ->toContain(
            'data-required-validation="0"'
        );

    expect($runtime)
        ->not->toContain('@json(')
        ->not->toContain('{{')
        ->toContain('getOfferRuntimeConfig')
        ->toContain('parseOfferRuntimeJson')
        ->toContain('pricePreviewUrl')
        ->toContain('refreshAutomaticPrice')
        ->toContain('syncShippingPrice')
        ->toContain('filterProducts')
        ->toContain('syncShippingHiddenFields')
        ->toContain('validateOfferForm')
        ->toContain('requiredValidation');
});
