<?php

use App\Models\CustomerGroup;
use App\Models\PriceTierDefinition;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
});

function priceTierRows(?callable $change = null): array
{
    $rows = PriceTierDefinition::query()
        ->ordered()
        ->get()
        ->map(fn (PriceTierDefinition $definition) => [
            'id' => $definition->id,
            'label' => $definition->label,
            'min_grams' => $definition->min_grams,
            'max_grams' => $definition->max_grams,
        ])
        ->all();

    return $change ? $change($rows) : $rows;
}

it('shows the default price tier definitions in settings', function () {
    $this->actingAs($this->admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Preisstufen')
        ->assertSee('Leer = unendlich')
        ->assertSee('50g')
        ->assertSee('1000g');

    expect(PriceTierDefinition::query()->count())->toBe(5);
});

it('adds a price tier and creates price rows for existing products and groups', function () {
    $group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $product = Product::create([
        'name' => 'Testprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    $rows = priceTierRows(function (array $rows): array {
        $rows[4]['max_grams'] = 4000;
        $rows[] = [
            'id' => null,
            'label' => 'Sondermenge',
            'min_grams' => 4001,
            'max_grams' => 5000,
        ];

        return $rows;
    });

    $this->actingAs($this->admin)
        ->put(route('price-tiers.update'), ['tiers' => $rows])
        ->assertRedirect(route('settings.index') . '#price-tiers');

    $definition = PriceTierDefinition::query()
        ->where('label', 'Sondermenge')
        ->firstOrFail();

    $this->assertDatabaseHas('product_price_tiers', [
        'product_id' => $product->id,
        'customer_group_id' => $group->id,
        'tier_key' => $definition->key,
        'tier_label' => 'Sondermenge',
        'min_grams' => 4001,
        'max_grams' => 5000,
        'price' => 0,
    ]);
});

it('allows the final price tier to have no upper limit', function () {
    $group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $product = Product::create([
        'name' => 'Großmengenprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    $rows = priceTierRows(function (array $rows): array {
        $rows[] = [
            'id' => null,
            'label' => 'Ab 5001g',
            'min_grams' => 5001,
            'max_grams' => null,
        ];

        return $rows;
    });

    $this->actingAs($this->admin)
        ->put(route('price-tiers.update'), ['tiers' => $rows])
        ->assertRedirect(route('settings.index') . '#price-tiers');

    $definition = PriceTierDefinition::query()
        ->where('label', 'Ab 5001g')
        ->firstOrFail();

    expect($definition->max_grams)->toBeNull();

    $priceTier = ProductPriceTier::query()
        ->where('product_id', $product->id)
        ->where('customer_group_id', $group->id)
        ->where('tier_key', $definition->key)
        ->firstOrFail();

    expect($priceTier->max_grams)->toBeNull();

    $matchedTier = ProductPriceTier::query()
        ->where('product_id', $product->id)
        ->where('customer_group_id', $group->id)
        ->where('min_grams', '<=', 6500)
        ->where(function ($query) {
            $query
                ->whereNull('max_grams')
                ->orWhere('max_grams', '>=', 6500);
        })
        ->orderByDesc('min_grams')
        ->first();

    expect($matchedTier?->tier_key)->toBe($definition->key);
});

it('updates a tier while preserving existing prices and its technical key', function () {
    $group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $product = Product::create([
        'name' => 'Testprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    ProductPriceTier::ensureForProduct($product);

    $definition = PriceTierDefinition::query()->where('key', '250g')->firstOrFail();
    $priceTier = ProductPriceTier::query()
        ->where('product_id', $product->id)
        ->where('customer_group_id', $group->id)
        ->where('tier_key', '250g')
        ->firstOrFail();

    $priceTier->update(['price' => 13.75]);

    $rows = priceTierRows(function (array $rows) use ($definition): array {
        foreach ($rows as &$row) {
            if ((int) $row['id'] === $definition->id) {
                $row['label'] = '250g Premium';
            }
        }

        return $rows;
    });

    $this->actingAs($this->admin)
        ->put(route('price-tiers.update'), ['tiers' => $rows])
        ->assertRedirect(route('settings.index') . '#price-tiers');

    expect($definition->fresh()->key)->toBe('250g');
    expect($definition->fresh()->label)->toBe('250g Premium');
    expect((float) $priceTier->fresh()->price)->toBe(13.75);
    expect($priceTier->fresh()->tier_label)->toBe('250g Premium');
});

it('removes a tier and keeps the remaining ranges valid', function () {
    $group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $product = Product::create([
        'name' => 'Testprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    ProductPriceTier::ensureForProduct($product);

    $removed = PriceTierDefinition::query()->where('key', '100g')->firstOrFail();
    $rows = priceTierRows(function (array $rows) use ($removed): array {
        $rows = array_values(array_filter(
            $rows,
            fn (array $row) => (int) $row['id'] !== $removed->id
        ));

        foreach ($rows as &$row) {
            if ($row['label'] === '250g') {
                $row['min_grams'] = 91;
            }
        }

        return $rows;
    });

    $this->actingAs($this->admin)
        ->put(route('price-tiers.update'), ['tiers' => $rows])
        ->assertRedirect(route('settings.index') . '#price-tiers');

    $this->assertDatabaseMissing('price_tier_definitions', ['id' => $removed->id]);
    $this->assertDatabaseMissing('product_price_tiers', [
        'product_id' => $product->id,
        'customer_group_id' => $group->id,
        'tier_key' => '100g',
    ]);
});

it('rejects overlapping or incomplete ranges', function () {
    $rows = priceTierRows(function (array $rows): array {
        $rows[1]['min_grams'] = 80;

        return $rows;
    });

    $this->actingAs($this->admin)
        ->from(route('settings.index'))
        ->put(route('price-tiers.update'), ['tiers' => $rows])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHasErrors('tiers');

    $this->assertDatabaseHas('price_tier_definitions', [
        'key' => '100g',
        'min_grams' => 91,
    ]);
});

it('rejects an unlimited tier when another tier follows it', function () {
    $rows = priceTierRows(function (array $rows): array {
        $rows[3]['max_grams'] = null;

        return $rows;
    });

    $this->actingAs($this->admin)
        ->from(route('settings.index'))
        ->put(route('price-tiers.update'), ['tiers' => $rows])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHasErrors('tiers');

    $this->assertDatabaseHas('price_tier_definitions', [
        'key' => '500g',
        'max_grams' => 750,
    ]);
});

it('does not allow all price tiers to be removed', function () {
    $this->actingAs($this->admin)
        ->from(route('settings.index'))
        ->put(route('price-tiers.update'), ['tiers' => []])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHasErrors('tiers');

    expect(PriceTierDefinition::query()->count())->toBe(5);
});

it('blocks non admins from changing global price tiers', function () {
    foreach ([User::ROLE_MANAGER, User::ROLE_SALES] as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('price-tiers.update'), ['tiers' => priceTierRows()])
            ->assertForbidden();
    }
});
