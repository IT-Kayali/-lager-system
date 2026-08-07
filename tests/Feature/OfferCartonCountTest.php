<?php

use App\Models\Offer;

it('keeps carton count as operational offer data without price calculation changes', function () {
    $offer = new Offer();

    expect($offer->getFillable())->toContain('carton_count')
        ->and($offer->getCasts()['carton_count'] ?? null)->toBe('integer');

    $controller = file_get_contents(app_path('Http/Controllers/OfferController.php'));

    expect($controller)
        ->toContain("'carton_count' => ['nullable', 'required_if:shipping_method,Lieferung', 'integer', 'min:1', 'max:9999']")
        ->toContain("'carton_count' => \$data['carton_count'] ?? null")
        ->toContain("\$data['carton_count'] = null;")
        ->toContain("\$total = collect(\$prepared)->sum('line_total');");
});

it('binds carton count to the offer form and delivery-only UI', function () {
    $form = file_get_contents(resource_path('views/pages/offers/_form.blade.php'));
    $script = file_get_contents(resource_path('js/app.js'));

    expect($form)
        ->toContain('name="carton_count"')
        ->toContain('id="carton_count_real"')
        ->toContain("old('carton_count', \$offer->carton_count ?? '')");

    expect($script)
        ->toContain("heading.textContent = 'Versandmethode'")
        ->toContain("label.textContent = 'Anzahl Kartons *'")
        ->toContain("const isDelivery = method.value === 'Lieferung'")
        ->toContain('cartonInput.required = isDelivery')
        ->toContain("help.textContent = 'Nur bei Lieferung. Hat keinen Einfluss auf Preis oder Versandkosten.'");
});

it('ships the carton count migration with nullable legacy storage', function () {
    $migration = database_path('migrations/2026_08_07_225000_add_carton_count_to_offers_table.php');

    expect(file_exists($migration))->toBeTrue();

    $source = file_get_contents($migration);

    expect($source)
        ->toContain("unsignedInteger('carton_count')->nullable()")
        ->toContain("dropColumn('carton_count')");
});
