<?php

test('manual price rules load their CSP safe runtime externally', function () {
    $view = file_get_contents(resource_path('views/pages/prices/index.blade.php'));
    $runtime = file_get_contents(public_path('js/manual-price-rules-runtime.js'));

    expect($view)
        ->not->toContain('<script>')
        ->toContain("asset('js/manual-price-rules-runtime.js')")
        ->toContain('data-manual-price-rules-runtime')
        ->toContain('manual-price-rules')
        ->toContain('manual-price-rule-template')
        ->toContain('add-price-rule');

    expect($runtime)
        ->toContain('initManualPriceRulesRuntime')
        ->toContain('[data-manual-price-rules-runtime]')
        ->toContain("[data-price-rule]")
        ->toContain('.remove-price-rule')
        ->toContain("template.content.cloneNode(true)")
        ->toContain("input.name = `rules[${index}][${field}]`");
});
