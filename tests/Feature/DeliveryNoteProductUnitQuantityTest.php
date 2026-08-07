<?php

it('uses product unit quantity columns on branded delivery notes', function () {
    $template = file_get_contents(resource_path('views/pdf/delivery-note.blade.php'));

    expect($template)
        ->toContain('<th>Produkt</th>')
        ->toContain('<th>Einheit</th>')
        ->toContain('<th>Menge</th>')
        ->toContain('<td>{{ $item->product }}</td>')
        ->toContain('<td>{{ $item->unit }}</td>')
        ->toContain('<td>{{ $item->quantity }}</td>')
        ->toContain("'product' => \$product")
        ->toContain("'unit' => \$unit !== '' ? \$unit : '—'")
        ->toContain("'quantity' => \$quantity")
        ->not->toContain('<th>Bezeichnung</th>')
        ->not->toContain("trim(\$qty . ' ' . \$unit)");

    expect(strpos($template, '<th>Produkt</th>'))
        ->toBeLessThan(strpos($template, '<th>Einheit</th>'));
    expect(strpos($template, '<th>Einheit</th>'))
        ->toBeLessThan(strpos($template, '<th>Menge</th>'));
});

it('uses product unit quantity columns on no-logo delivery notes', function () {
    $template = file_get_contents(resource_path('views/pdf/delivery-note-ohne.blade.php'));

    expect($template)
        ->toContain('<th>Product</th>')
        ->toContain('<th>Unit</th>')
        ->toContain('<th>Quantity</th>')
        ->toContain('<td>{{ $item->product }}</td>')
        ->toContain('<td>{{ $item->unit }}</td>')
        ->toContain('<td>{{ $item->quantity }}</td>')
        ->toContain("'product' => \$product")
        ->toContain("'unit' => \$unit !== '' ? \$unit : '—'")
        ->toContain("'quantity' => \$quantity")
        ->not->toContain("trim(\$qty . ' ' . \$unit)");

    expect(strpos($template, '<th>Product</th>'))
        ->toBeLessThan(strpos($template, '<th>Unit</th>'));
    expect(strpos($template, '<th>Unit</th>'))
        ->toBeLessThan(strpos($template, '<th>Quantity</th>'));
});
