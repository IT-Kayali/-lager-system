<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Offer;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $this->sales = User::factory()->create([
        'role' => User::ROLE_SALES,
        'is_active' => true,
    ]);

    $this->warehouse = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
    ]);

    $group = CustomerGroup::query()->create([
        'name' => 'Workflow Testgruppe',
        'slug' => 'workflow-testgruppe',
        'color' => '#D4AD16',
    ]);

    $this->customer = Customer::query()->create([
        'customer_group_id' => $group->id,
        'company_name' => 'Workflow Kunde',
    ]);
});

function workflowOffer(Customer $customer, User $creator, string $status = Offer::STATUS_OFFER): Offer
{
    return Offer::query()->create([
        'customer_id' => $customer->id,
        'user_id' => $creator->id,
        'status' => $status,
        'template_type' => 'with_company',
        'document_type' => 'offer',
        'subtotal' => 0,
        'total' => 0,
        'reserved_until' => now()->addHours(72),
    ]);
}

it('keeps an offer editable for sales until it is handed to warehouse', function () {
    $offer = workflowOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)
        ->get(route('offers.edit', $offer))
        ->assertOk();

    $this->actingAs($this->sales)
        ->put(route('offers.status', $offer), [
            'status' => Offer::STATUS_IN_PROGRESS,
        ])
        ->assertRedirect(route('offers.show', $offer));

    expect($offer->fresh()->status)->toBe(Offer::STATUS_IN_PROGRESS);

    $this->actingAs($this->sales)
        ->get(route('offers.edit', $offer))
        ->assertRedirect(route('offers.show', $offer));
});

it('shows warehouse only offers from in progress onward', function () {
    $draft = workflowOffer($this->customer, $this->sales, Offer::STATUS_OFFER);
    $handedOff = workflowOffer($this->customer, $this->sales, Offer::STATUS_IN_PROGRESS);

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.offers.index'))
        ->assertOk()
        ->assertDontSee($draft->offer_number)
        ->assertSee($handedOff->offer_number);
});

it('allows warehouse to move an offer backward to sales and forward again', function () {
    $offer = workflowOffer($this->customer, $this->sales, Offer::STATUS_IN_PROGRESS);

    $this->actingAs($this->warehouse)
        ->put(route('warehouse.offers.status', $offer), [
            'status' => Offer::STATUS_READY,
        ])
        ->assertRedirect(route('warehouse.offers.show', $offer));

    expect($offer->fresh()->status)->toBe(Offer::STATUS_READY);

    $this->actingAs($this->warehouse)
        ->put(route('warehouse.offers.status', $offer), [
            'status' => Offer::STATUS_IN_PROGRESS,
        ])
        ->assertRedirect(route('warehouse.offers.show', $offer));

    $this->actingAs($this->warehouse)
        ->put(route('warehouse.offers.status', $offer), [
            'status' => Offer::STATUS_OFFER,
        ])
        ->assertRedirect(route('warehouse.offers.index'));

    expect($offer->fresh()->status)->toBe(Offer::STATUS_OFFER)
        ->and($offer->fresh()->reserved_until)->not->toBeNull();

    $this->actingAs($this->sales)
        ->get(route('offers.edit', $offer))
        ->assertOk();
});

it('prevents sales from changing warehouse-owned offer statuses', function () {
    $offer = workflowOffer($this->customer, $this->sales, Offer::STATUS_IN_PROGRESS);

    $this->actingAs($this->sales)
        ->put(route('offers.status', $offer), [
            'status' => Offer::STATUS_READY,
        ])
        ->assertRedirect(route('offers.show', $offer));

    expect($offer->fresh()->status)->toBe(Offer::STATUS_IN_PROGRESS);
});
