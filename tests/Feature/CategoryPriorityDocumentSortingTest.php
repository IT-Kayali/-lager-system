<?php

use App\Models\OfferItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\CategoryPriorityService;
use App\Services\DocumentItemSorter;

it('keeps category priorities unique and shifts them automatically', function () {
    $service = app(CategoryPriorityService::class);

    $first = $service->create([
        'name' => 'Kategorie A',
        'priority' => 1,
        'color' => '#111111',
        'is_active' => true,
    ]);

    $second = $service->create([
        'name' => 'Kategorie B',
        'priority' => 2,
        'color' => '#222222',
        'is_active' => true,
    ]);

    $inserted = $service->create([
        'name' => 'Kategorie Neu',
        'priority' => 1,
        'color' => '#333333',
        'is_active' => true,
    ]);

    expect($inserted->fresh()->priority)->toBe(1)
        ->and($first->fresh()->priority)->toBe(2)
        ->and($second->fresh()->priority)->toBe(3);

    $service->update($second->fresh(), [
        'name' => 'Kategorie B',
        'priority' => 1,
        'color' => '#222222',
        'is_active' => true,
    ]);

    expect($second->fresh()->priority)->toBe(1)
        ->and($inserted->fresh()->priority)->toBe(2)
        ->and($first->fresh()->priority)->toBe(3);

    $service->delete($inserted->fresh());

    expect($second->fresh()->priority)->toBe(1)
        ->and($first->fresh()->priority)->toBe(2);
});

it('sorts document positions by category priority quantity and natural product name', function () {
    $sorter = app(DocumentItemSorter::class);

    $makeItem = function (string $name, float $quantity, array $priorities, int $id): OfferItem {
        $product = new Product(['name' => $name, 'unit' => 'gram']);
        $product->setRelation(
            'categories',
            collect($priorities)->map(fn (int $priority) => new ProductCategory(['priority' => $priority]))
        );

        $item = new OfferItem([
            'product_name' => $name,
            'quantity' => $quantity,
        ]);
        $item->id = $id;
        $item->setRelation('product', $product);

        return $item;
    };

    $items = collect([
        $makeItem('Ohne Kategorie', 999, [], 7),
        $makeItem('Produkt 20', 50, [1], 1),
        $makeItem('Produkt 10', 100, [1], 2),
        $makeItem('Produkt 2', 100, [1], 3),
        $makeItem('Produkt 99', 75, [3, 1], 4),
        $makeItem('Kategorie Zwei', 500, [2], 5),
        $makeItem('Kategorie Drei', 900, [3], 6),
    ]);

    $sortedNames = $sorter->sort($items)->pluck('product_name')->all();

    expect($sortedNames)->toBe([
        'Produkt 2',
        'Produkt 10',
        'Produkt 99',
        'Produkt 20',
        'Kategorie Zwei',
        'Kategorie Drei',
        'Ohne Kategorie',
    ]);
});

it('uses the same sorter for offer invoice delivery note and branch delivery note', function () {
    $offerPdfController = file_get_contents(app_path('Http/Controllers/OfferPdfController.php'));
    $branchPdfController = file_get_contents(app_path('Http/Controllers/BranchWithdrawalPdfController.php'));

    expect($offerPdfController)
        ->toContain('DocumentItemSorter')
        ->toContain("items.product.categories")
        ->toContain("setRelation('items', app(DocumentItemSorter::class)->sort(\$offer->items))");

    expect($branchPdfController)
        ->toContain('DocumentItemSorter')
        ->toContain("items.product.categories")
        ->toContain("setRelation('items', app(DocumentItemSorter::class)->sort(\$branchWithdrawal->items))");
});

it('shows category priority in the editor and category overview', function () {
    $form = file_get_contents(resource_path('views/pages/product-categories/_form.blade.php'));
    $index = file_get_contents(resource_path('views/pages/product-categories/index.blade.php'));

    expect($form)
        ->toContain('name="priority"')
        ->toContain('1 = zuerst auf Angebot, Rechnung und Lieferscheinen');

    expect($index)
        ->toContain('<th>Priorität</th>')
        ->toContain('category-priority-pill');
});
