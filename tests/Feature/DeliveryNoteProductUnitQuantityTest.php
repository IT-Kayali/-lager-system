<?php

it('uses product quantity unit columns on branded delivery notes', function () {
    $template = file_get_contents(resource_path('views/pdf/delivery-note.blade.php'));

    expect($template)
        ->toContain('<th>Produkt</th>')
        ->toContain('<th>Menge</th>')
        ->toContain('<th>Einheit</th>')
        ->toContain('<td>{{ $item->product }}</td>')
        ->toContain('<td>{{ $item->quantity }}</td>')
        ->toContain('<td>{{ $item->unit }}</td>')
        ->toContain("'product' => \$product")
        ->toContain("'unit'=>\$unit!==''?(\$unitLabels[\$unit]??\$unit):'—'")
        ->toContain("'quantity' => \$quantity")
        ->not->toContain('<th>Bezeichnung</th>')
        ->not->toContain("trim(\$qty . ' ' . \$unit)");

    expect(strpos($template, '<th>Produkt</th>'))
        ->toBeLessThan(strpos($template, '<th>Menge</th>'));
    expect(strpos($template, '<th>Menge</th>'))
        ->toBeLessThan(strpos($template, '<th>Einheit</th>'));
});

it('uses product quantity unit columns on no-logo delivery notes', function () {
    $template = file_get_contents(resource_path('views/pdf/delivery-note-ohne.blade.php'));

    expect($template)
        ->toContain('<th>Product</th>')
        ->toContain('<th>Quantity</th>')
        ->toContain('<th>Unit</th>')
        ->toContain('<td>{{ $item->product }}</td>')
        ->toContain('<td>{{ $item->quantity }}</td>')
        ->toContain('<td>{{ $item->unit }}</td>')
        ->toContain("'product' => \$product")
        ->toContain("'unit'=>\$unit!==''?(\$unitLabels[\$unit]??\$unit):'—'")
        ->toContain("'quantity' => \$quantity")
        ->not->toContain("trim(\$qty . ' ' . \$unit)");

    expect(strpos($template, '<th>Product</th>'))
        ->toBeLessThan(strpos($template, '<th>Quantity</th>'));
    expect(strpos($template, '<th>Quantity</th>'))
        ->toBeLessThan(strpos($template, '<th>Unit</th>'));
});
