<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2028-06-15 12:00:00'));

    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $this->group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $this->customer = Customer::create([
        'customer_group_id' => $this->group->id,
        'company_name' => 'Umsatz Testkunde',
    ]);

    $this->customer->forceFill([
        'created_at' => Carbon::parse('2026-03-10 10:00:00'),
        'updated_at' => Carbon::parse('2026-03-10 10:00:00'),
    ])->save();
});

afterEach(function () {
    Carbon::setTestNow();
});

function customerRevenueProduct(string $name, string $unit = 'gram'): Product
{
    return Product::query()->create([
        'name' => $name,
        'unit' => $unit,
        'minimum_stock' => 0,
    ]);
}

function customerRevenueOffer(
    Customer $customer,
    User $user,
    Product $product,
    string $status,
    string $completedAt,
    float $quantity,
    float $lineTotal
): Offer {
    $offer = Offer::query()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'status' => $status,
        'subtotal' => $lineTotal,
        'total' => $lineTotal,
        'completed_at' => $completedAt,
    ]);

    $offer->items()->create([
        'product_id' => $product->id,
        'product_code' => $product->code ?? ('PRD-' . $product->id),
        'product_name' => $product->name,
        'quantity' => $quantity,
        'unit' => $product->unit,
        'unit_price' => $quantity > 0 ? $lineTotal / $quantity : 0,
        'line_total' => $lineTotal,
    ]);

    return $offer;
}

it('shows dynamic years from customer creation year through the current year', function () {
    $product = customerRevenueProduct('Jahresprodukt');

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $product,
        Offer::STATUS_COMPLETED,
        '2026-07-01 12:00:00',
        100,
        250
    );

    $this->actingAs($this->manager)
        ->get(route('customers.show', $this->customer))
        ->assertOk()
        ->assertSee('Gesamter Zeitraum')
        ->assertSee('value="2026"', false)
        ->assertSee('value="2027"', false)
        ->assertSee('value="2028"', false)
        ->assertDontSee('value="2029"', false);
});

it('filters completed customer revenue and products by selected year', function () {
    $product2026 = customerRevenueProduct('Produkt nur 2026');
    $product2027 = customerRevenueProduct('Produkt nur 2027');
    $cancelledProduct = customerRevenueProduct('Storniertes Produkt');

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $product2026,
        Offer::STATUS_COMPLETED,
        '2026-05-01 12:00:00',
        100,
        200
    );

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $product2026,
        Offer::STATUS_COMPLETED,
        '2026-08-01 12:00:00',
        50,
        100
    );

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $product2027,
        Offer::STATUS_COMPLETED,
        '2027-03-01 12:00:00',
        25,
        90
    );

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $cancelledProduct,
        Offer::STATUS_CANCELLED,
        '2026-09-01 12:00:00',
        999,
        9999
    );

    $this->actingAs($this->manager)
        ->get(route('customers.show', [
            'customer' => $this->customer,
            'revenue_year' => 2026,
        ]))
        ->assertOk()
        ->assertSee('300,00 €')
        ->assertSee('150,00 g')
        ->assertSee('Produkt nur 2026')
        ->assertDontSee('Produkt nur 2027')
        ->assertDontSee('Storniertes Produkt');
});

it('shows all completed sales when the complete period is selected', function () {
    $grams = customerRevenueProduct('Duftöl', 'gram');
    $pieces = customerRevenueProduct('Flasche', 'piece');

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $grams,
        Offer::STATUS_COMPLETED,
        '2026-05-01 12:00:00',
        1000,
        400
    );

    customerRevenueOffer(
        $this->customer,
        $this->manager,
        $pieces,
        Offer::STATUS_COMPLETED,
        '2027-05-01 12:00:00',
        6,
        60
    );

    $this->actingAs($this->manager)
        ->get(route('customers.show', $this->customer))
        ->assertOk()
        ->assertSee('460,00 €')
        ->assertSee('1.000,00 g')
        ->assertSee('6,00 Stk.')
        ->assertSee('Duftöl')
        ->assertSee('Flasche');
});
