<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Offer;
use App\Models\User;

function permissionsUser(string $role): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);
}

function permissionsOffer(Customer $customer, User $creator, string $status, string $number): Offer
{
    return Offer::query()->create([
        'offer_number' => $number,
        'customer_id' => $customer->id,
        'user_id' => $creator->id,
        'status' => $status,
        'shipping_method' => 'Abholung',
        'subtotal' => 100,
        'total' => 100,
    ]);
}

beforeEach(function () {
    $this->admin = permissionsUser(User::ROLE_ADMIN);
    $this->manager = permissionsUser(User::ROLE_MANAGER);
    $this->warehouse = permissionsUser(User::ROLE_WAREHOUSE);
    $this->sales = permissionsUser(User::ROLE_SALES);

    $group = CustomerGroup::query()->create([
        'name' => 'Rollen-Testgruppe',
        'slug' => 'rollen-testgruppe',
        'color' => '#D4AD16',
    ]);

    $this->customer = Customer::query()->create([
        'customer_group_id' => $group->id,
        'company_name' => 'Rollen Testkunde',
        'delivery_city' => 'Rheine',
    ]);
});

it('treats admin as superuser while manager cannot access admin settings', function () {
    expect($this->admin->hasRole(User::ROLE_MANAGER))->toBeTrue()
        ->and($this->admin->hasRole(User::ROLE_WAREHOUSE))->toBeTrue()
        ->and($this->admin->hasRole(User::ROLE_SALES))->toBeTrue();

    $this->actingAs($this->admin)
        ->get(route('settings.index'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('security.index'))
        ->assertOk();

    $this->actingAs($this->manager)
        ->get(route('settings.index'))
        ->assertForbidden();

    $this->actingAs($this->manager)
        ->get(route('security.index'))
        ->assertForbidden();
});

it('redirects warehouse and sales users from dashboard to their work areas', function () {
    $this->actingAs($this->warehouse)
        ->get(route('dashboard'))
        ->assertRedirect(route('products.index'));

    $this->actingAs($this->sales)
        ->get(route('dashboard'))
        ->assertRedirect(route('offers.index'));

    $this->actingAs($this->manager)
        ->get(route('dashboard'))
        ->assertOk();
});

it('separates warehouse and sales areas on direct urls', function () {
    $this->actingAs($this->warehouse)
        ->get(route('products.index'))
        ->assertOk();

    $this->actingAs($this->warehouse)
        ->get(route('customers.index'))
        ->assertForbidden();

    $this->actingAs($this->warehouse)
        ->get(route('offers.index'))
        ->assertForbidden();

    $this->actingAs($this->sales)
        ->get(route('customers.index'))
        ->assertOk();

    $this->actingAs($this->sales)
        ->get(route('products.index'))
        ->assertForbidden();

    $this->actingAs($this->sales)
        ->get(route('warnings.index'))
        ->assertForbidden();
});

it('shows warehouse only offers from in progress through completed', function () {
    permissionsOffer($this->customer, $this->manager, Offer::STATUS_OFFER, 'ANG-WH-OFFER');
    permissionsOffer($this->customer, $this->manager, Offer::STATUS_IN_PROGRESS, 'ANG-WH-PROGRESS');
    permissionsOffer($this->customer, $this->manager, Offer::STATUS_READY, 'ANG-WH-READY');
    permissionsOffer($this->customer, $this->manager, Offer::STATUS_COMPLETED, 'ANG-WH-DONE');
    permissionsOffer($this->customer, $this->manager, Offer::STATUS_CANCELLED, 'ANG-WH-CANCEL');
    permissionsOffer($this->customer, $this->manager, Offer::STATUS_RESERVATION_EXPIRED, 'ANG-WH-EXPIRED');

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.offers.index'))
        ->assertOk()
        ->assertSee('ANG-WH-PROGRESS')
        ->assertSee('ANG-WH-READY')
        ->assertSee('ANG-WH-DONE')
        ->assertDontSee('ANG-WH-OFFER')
        ->assertDontSee('ANG-WH-CANCEL')
        ->assertDontSee('ANG-WH-EXPIRED');
});

it('blocks warehouse from opening offers that are not released for preparation', function () {
    $offer = permissionsOffer($this->customer, $this->manager, Offer::STATUS_OFFER, 'ANG-WH-HIDDEN');

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.offers.show', $offer))
        ->assertForbidden();
});

it('warehouse offer view contains only operational information and delivery note', function () {
    $offer = permissionsOffer($this->customer, $this->manager, Offer::STATUS_IN_PROGRESS, 'ANG-WH-VIEW');

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.offers.show', $offer))
        ->assertOk()
        ->assertSee('Lieferschein PDF')
        ->assertSee('Lagerstatus')
        ->assertSee('Positionen vorbereiten')
        ->assertDontSee('Angebot PDF')
        ->assertDontSee('Rechnung PDF')
        ->assertDontSee('Einzelpreis')
        ->assertDontSee('Gesamt:');
});

it('allows warehouse only forward status transitions', function () {
    $offer = permissionsOffer($this->customer, $this->manager, Offer::STATUS_IN_PROGRESS, 'ANG-WH-STATUS');

    $this->actingAs($this->warehouse)
        ->put(route('warehouse.offers.status', $offer), [
            'status' => Offer::STATUS_READY,
        ])
        ->assertRedirect(route('warehouse.offers.show', $offer));

    expect($offer->fresh()->status)->toBe(Offer::STATUS_READY);

    $this->actingAs($this->warehouse)
        ->put(route('warehouse.offers.status', $offer), [
            'status' => Offer::STATUS_CANCELLED,
        ])
        ->assertRedirect(route('warehouse.offers.show', $offer))
        ->assertSessionHas('error');

    expect($offer->fresh()->status)->toBe(Offer::STATUS_READY);
});

it('does not let sales users open the warehouse offer work queue', function () {
    $this->actingAs($this->sales)
        ->get(route('warehouse.offers.index'))
        ->assertForbidden();
});

it('promotes users that existed before the role migration to admin', function () {
    $legacyWarehouse = permissionsUser(User::ROLE_WAREHOUSE);
    $legacySales = permissionsUser(User::ROLE_SALES);

    $migration = require database_path('migrations/2026_08_08_012500_promote_existing_users_to_admin.php');
    $migration->up();

    expect($legacyWarehouse->fresh()->role)->toBe(User::ROLE_ADMIN)
        ->and($legacySales->fresh()->role)->toBe(User::ROLE_ADMIN);
});
