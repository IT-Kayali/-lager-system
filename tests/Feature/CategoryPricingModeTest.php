<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\ManualPriceRule;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\User;

function pricingModeManager(): User
{
    return User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
}

function pricingModeGroup(string $slug = 'pricing-test'): CustomerGroup
{
    return CustomerGroup::query()->create([
        'name' => 'Preisgruppe ' . $slug,
        'slug' => $slug,
        'color' => '#D4AD16',
    ]);
}

function manualPricingProduct(string $name = 'Manuelles Preisprodukt'): Product
{
    $category = ProductCategory::query()->create([
        'name' => 'Manuelle Preise ' . uniqid(),
        'priority' => ((int) ProductCategory::query()->max('priority')) + 1,
        'is_active' => true,
        'price_tiers_enabled' => false,
    ]);

    $product = Product::query()->create([
        'name' => $name,
        'unit' => 'piece',
        'minimum_stock' => 0,
    ]);

    $product->categories()->attach($category->id);

    return $product->fresh('categories');
}

it('uses manual pricing mode when the assigned category has price tiers disabled', function () {
    $product = manualPricingProduct();

    expect($product->usesPriceTiers())->toBeFalse();
});

it('stores multiple manual rules separately for each customer group', function () {
    $manager = pricingModeManager();
    $group = pricingModeGroup('manual-rules');
    $product = manualPricingProduct('Regelprodukt');

    $this->actingAs($manager)
        ->put(route('prices.update'), [
            'product_id' => $product->id,
            'customer_group_id' => $group->id,
            'rules' => [
                [
                    'min_quantity' => 1,
                    'max_quantity' => 5,
                    'price' => 3.00,
                    'label' => '1 bis 5',
                ],
                [
                    'min_quantity' => 6,
                    'max_quantity' => 10,
                    'price' => 2.70,
                    'label' => '6 bis 10',
                ],
                [
                    'min_quantity' => 11,
                    'max_quantity' => null,
                    'price' => 2.40,
                    'label' => 'ab 11',
                ],
            ],
        ])
        ->assertRedirect(route('prices.index', [
            'product_id' => $product->id,
            'customer_group_id' => $group->id,
        ]));

    $rules = ManualPriceRule::query()
        ->where('product_id', $product->id)
        ->where('customer_group_id', $group->id)
        ->orderBy('min_quantity')
        ->get();

    expect($rules)->toHaveCount(3)
        ->and((float) $rules[0]->price)->toBe(3.0)
        ->and((float) $rules[1]->price)->toBe(2.7)
        ->and((float) $rules[2]->price)->toBe(2.4)
        ->and($rules[2]->max_quantity)->toBeNull();
});

it('applies the matching manual rule and customer group when an offer is created', function () {
    $manager = pricingModeManager();
    $group = pricingModeGroup('offer-manual');
    $product = manualPricingProduct('Angebotsregelprodukt');

    ProductBatch::query()->create([
        'product_id' => $product->id,
        'quantity' => 100,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    ManualPriceRule::query()->create([
        'product_id' => $product->id,
        'customer_group_id' => $group->id,
        'min_quantity' => 1,
        'max_quantity' => 5,
        'price' => 3.00,
        'label' => 'Kleinmenge',
    ]);

    ManualPriceRule::query()->create([
        'product_id' => $product->id,
        'customer_group_id' => $group->id,
        'min_quantity' => 6,
        'max_quantity' => 10,
        'price' => 2.70,
        'label' => 'Mengenpreis',
    ]);

    $customer = Customer::query()->create([
        'customer_group_id' => $group->id,
        'company_name' => 'Preis Testkunde',
    ]);

    $this->actingAs($manager)
        ->post(route('offers.store'), [
            'customer_id' => $customer->id,
            'template_type' => 'with_company',
            'shipping_method' => 'Abholung',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 7],
            ],
        ])
        ->assertRedirect();

    $offer = Offer::query()->with('items')->latest('id')->firstOrFail();
    $item = $offer->items->firstOrFail();

    expect((float) $item->unit_price)->toBe(2.7)
        ->and((float) $item->line_total)->toBe(18.9)
        ->and($item->tier_label)->toBe('Mengenpreis')
        ->and($item->product_price_tier_id)->toBeNull();
});
