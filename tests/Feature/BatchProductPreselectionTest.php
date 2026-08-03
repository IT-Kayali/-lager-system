<?php

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
});

it('preselects the product when stock booking is opened from a product', function () {
    $product = Product::create([
        'name' => 'Vorausgewähltes Testprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    $this->actingAs($this->manager)
        ->get(route('batches.create', ['product_id' => $product->id]))
        ->assertOk()
        ->assertSee('value="'.$product->id.'" selected', false)
        ->assertSee('Vorausgewähltes Testprodukt');
});

it('ignores an unknown product id when opening stock booking', function () {
    $this->actingAs($this->manager)
        ->get(route('batches.create', ['product_id' => 999999]))
        ->assertOk()
        ->assertDontSee('value="999999" selected', false);
});
