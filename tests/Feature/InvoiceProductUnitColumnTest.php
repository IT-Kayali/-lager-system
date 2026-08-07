<?php

use App\Http\Controllers\OfferPdfController;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Product;

function invoiceWithUnits(array $units, bool $withShipping = false): Offer
{
    $offer = new Offer([
        'shipping_method' => $withShipping ? 'Lieferung' : 'Abholung',
        'shipping_price_gross' => $withShipping ? 6.00 : null,
    ]);

    $items = collect($units)->map(function (string $unit, int $index) {
        $product = new Product([
            'name' => 'Produkt ' . ($index + 1),
            'unit' => $unit,
        ]);

        $item = new OfferItem([
            'product_name' => 'Produkt ' . ($index + 1),
            'quantity' => 2,
            'unit_price' => 10,
            'line_total' => 20,
        ]);
        $item->setRelation('product', $product);

        return $item;
    });

    $offer->setRelation('items', $items);

    return $offer;
}

function transformInvoiceTable(Offer $offer, bool $withoutLogo, string $html): string
{
    $controller = new OfferPdfController();
    $method = new ReflectionMethod($controller, 'injectInvoiceProductUnitColumn');
    $method->setAccessible(true);

    return $method->invoke($controller, $html, $offer, $withoutLogo);
}

function logoInvoiceTableHtml(bool $includeEmptyRow = false): string
{
    $emptyRow = $includeEmptyRow
        ? '<tr class="empty-product-row"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'
        : '';

    return <<<HTML
<!DOCTYPE html>
<html><head><style>.items-table{width:160mm}</style></head><body>
<table class="items-table">
<thead><tr><th>Beschreibung</th><th>Menge</th><th>Preis</th><th>Summe</th></tr></thead>
<tbody>
<tr><td>Produkt 1</td><td>2,00</td><td>10,00€</td><td>20,00€</td></tr>
{$emptyRow}
</tbody>
</table>
</body></html>
HTML;
}

function noLogoInvoiceTableHtml(bool $withShipping = false): string
{
    $shippingRow = $withShipping
        ? '<tr><td>Versand</td><td>1,00</td><td>6,00€</td><td>6,00€</td></tr>'
        : '';

    return <<<HTML
<!DOCTYPE html>
<html><head><style>table.items{width:170mm}</style></head><body>
<table class="items">
<thead><tr><th>PRODUCT</th><th>QUANTITY</th><th>PRICE</th><th>SUM</th></tr></thead>
<tbody>
<tr><td>Product 1</td><td>2,00</td><td>10,00€</td><td>20,00€</td></tr>
{$shippingRow}
</tbody>
</table>
</body></html>
HTML;
}

it('uses product quantity unit price and sum on german invoice tables', function () {
    $html = transformInvoiceTable(invoiceWithUnits(['g']), false, logoInvoiceTableHtml(true));
    $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect($decoded)
        ->toContain('Produkt')
        ->toContain('Menge')
        ->toContain('Einheit')
        ->toContain('Preis')
        ->toContain('Summe')
        ->toContain('>g<')
        ->toContain('invoice-unit-table')
        ->toContain('width: 82mm !important')
        ->toContain('width: 18mm !important');

    preg_match('/<tr class="empty-product-row">(.*?)<\/tr>/s', $decoded, $emptyRow);
    expect(substr_count($emptyRow[1] ?? '', '<td>'))->toBe(5);
});

it('uses english invoice labels and product unit without logo', function () {
    $html = transformInvoiceTable(invoiceWithUnits(['Stk']), true, noLogoInvoiceTableHtml());
    $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect($decoded)
        ->toContain('PRODUCT')
        ->toContain('QUANTITY')
        ->toContain('UNIT')
        ->toContain('PRICE')
        ->toContain('TOTAL')
        ->toContain('>Stk<')
        ->toContain('width: 70mm !important')
        ->toContain('width: 18mm !important');
});

it('shows a dash as unit for the shipping row', function () {
    $html = transformInvoiceTable(invoiceWithUnits(['g'], true), true, noLogoInvoiceTableHtml(true));
    $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect($decoded)
        ->toContain('>g<')
        ->toContain('>—<');
});

it('keeps the offer pdf on the unchanged template rendering path', function () {
    $controller = file_get_contents(app_path('Http/Controllers/OfferPdfController.php'));

    expect($controller)
        ->toContain("} elseif (\$type === 'invoice') {")
        ->toContain("\$html = \$this->injectInvoiceProductUnitColumn(\$html, \$offer, \$isNoLogoPdfTemplate);")
        ->toContain("} else {\n            \$pdf = Pdf::loadView(\$pdfView, \$viewData)->setPaper('a4');");
});
