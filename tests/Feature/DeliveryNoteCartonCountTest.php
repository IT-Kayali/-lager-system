<?php

use App\Http\Controllers\OfferPdfController;
use App\Models\Offer;

function renderDeliveryNoteCartonMetadata(Offer $offer, bool $withoutLogo): string
{
    $controller = new OfferPdfController();
    $method = new ReflectionMethod($controller, 'injectCartonCountIntoDeliveryNote');
    $method->setAccessible(true);

    $shippingLabel = $withoutLogo ? 'Shipping Method:' : 'Versandart:';
    $html = <<<HTML
<div class="meta">
    <table>
        <tr>
            <td>{$shippingLabel}</td>
            <td>{$offer->shipping_method}</td>
        </tr>
    </table>
</div>
<div class="title">Unveränderte Überschrift</div>
HTML;

    return $method->invoke($controller, $html, $offer, $withoutLogo);
}

it('shows the german carton count directly after shipping method for delivery', function () {
    $offer = new Offer([
        'shipping_method' => 'Lieferung',
        'carton_count' => 5,
    ]);

    $html = renderDeliveryNoteCartonMetadata($offer, false);

    expect($html)
        ->toContain('<td>Versandart:</td>')
        ->toContain('<td>Anzahl Kartons:</td>')
        ->toContain('<td>5</td>')
        ->toContain('<div class="title">Unveränderte Überschrift</div>');

    expect(strpos($html, 'Versandart:'))->toBeLessThan(strpos($html, 'Anzahl Kartons:'));
});

it('shows the english carton count directly after shipping method for the no-logo delivery note', function () {
    $offer = new Offer([
        'shipping_method' => 'Lieferung',
        'carton_count' => 3,
    ]);

    $html = renderDeliveryNoteCartonMetadata($offer, true);

    expect($html)
        ->toContain('<td>Shipping Method:</td>')
        ->toContain('<td>Number of Cartons:</td>')
        ->toContain('<td>3</td>');

    expect(strpos($html, 'Shipping Method:'))->toBeLessThan(strpos($html, 'Number of Cartons:'));
});

it('does not show carton count for pickup', function () {
    $offer = new Offer([
        'shipping_method' => 'Abholung',
        'carton_count' => null,
    ]);

    $withLogo = renderDeliveryNoteCartonMetadata($offer, false);
    $withoutLogo = renderDeliveryNoteCartonMetadata($offer, true);

    expect($withLogo)
        ->not->toContain('Anzahl Kartons:')
        ->and($withoutLogo)
        ->not->toContain('Number of Cartons:');
});

it('keeps both delivery note templates at their existing absolute positions', function () {
    $withLogo = file_get_contents(resource_path('views/pdf/delivery-note.blade.php'));
    $withoutLogo = file_get_contents(resource_path('views/pdf/delivery-note-ohne.blade.php'));

    expect($withLogo)
        ->toContain('top:92mm')
        ->toContain('top:108mm')
        ->toContain('top:132mm');

    expect($withoutLogo)
        ->toContain('top:62mm')
        ->toContain('top:106mm')
        ->toContain('top:132mm');
});
