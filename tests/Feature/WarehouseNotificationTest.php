<?php

use App\Models\BranchWithdrawal;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Models\WarehouseNotification;

beforeEach(function () {
    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
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

    $this->secondWarehouse = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
    ]);

    $group = CustomerGroup::query()->create([
        'name' => 'Notification Testgruppe',
        'slug' => 'notification-testgruppe',
        'color' => '#D4AD16',
    ]);

    $this->customer = Customer::query()->create([
        'customer_group_id' => $group->id,
        'company_name' => 'Benachrichtigung Kunde',
    ]);
});

function notificationOffer(Customer $customer, User $creator): Offer
{
    return Offer::query()->create([
        'customer_id' => $customer->id,
        'user_id' => $creator->id,
        'status' => Offer::STATUS_OFFER,
        'template_type' => 'with_company',
        'document_type' => 'offer',
        'subtotal' => 0,
        'total' => 0,
        'reserved_until' => now()->addHours(72),
    ]);
}

function notificationBranchProduct(): Product
{
    $product = Product::query()->create([
        'name' => 'Benachrichtigung Filialprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    ProductBatch::query()->create([
        'product_id' => $product->id,
        'quantity' => 100,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    return $product;
}

it('creates a persistent notification for every active warehouse user when an offer is handed off', function () {
    $offer = notificationOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)
        ->put(route('offers.status', $offer), [
            'status' => Offer::STATUS_IN_PROGRESS,
        ])
        ->assertRedirect(route('offers.show', $offer));

    expect(WarehouseNotification::query()->count())->toBe(2);

    $this->assertDatabaseHas('warehouse_notifications', [
        'user_id' => $this->warehouse->id,
        'type' => WarehouseNotification::TYPE_OFFER,
        'subject_id' => $offer->id,
        'read_at' => null,
        'dismissed_at' => null,
    ]);

    $this->assertDatabaseHas('warehouse_notifications', [
        'user_id' => $this->secondWarehouse->id,
        'type' => WarehouseNotification::TYPE_OFFER,
        'subject_id' => $offer->id,
        'read_at' => null,
        'dismissed_at' => null,
    ]);

    $this->assertDatabaseMissing('warehouse_notifications', [
        'user_id' => $this->sales->id,
        'subject_id' => $offer->id,
    ]);
});

it('does not mark notifications as read when the warehouse only opens the notification list', function () {
    $offer = notificationOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)->put(route('offers.status', $offer), [
        'status' => Offer::STATUS_IN_PROGRESS,
    ]);

    $notification = WarehouseNotification::query()
        ->where('user_id', $this->warehouse->id)
        ->firstOrFail();

    $this->actingAs($this->warehouse)
        ->getJson(route('warehouse.notifications.index'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('items.0.id', $notification->id)
        ->assertJsonPath('items.0.type', WarehouseNotification::TYPE_OFFER);

    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks exactly the clicked offer notification as read and opens the warehouse order', function () {
    $offer = notificationOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)->put(route('offers.status', $offer), [
        'status' => Offer::STATUS_IN_PROGRESS,
    ]);

    $notification = WarehouseNotification::query()
        ->where('user_id', $this->warehouse->id)
        ->firstOrFail();

    $otherNotification = WarehouseNotification::query()
        ->where('user_id', $this->secondWarehouse->id)
        ->firstOrFail();

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.notifications.open', $notification))
        ->assertRedirect(route('warehouse.offers.show', $offer));

    expect($notification->fresh()->read_at)->not->toBeNull()
        ->and($otherNotification->fresh()->read_at)->toBeNull();
});

it('creates and opens a branch withdrawal notification after handoff to warehouse', function () {
    $product = notificationBranchProduct();

    $this->actingAs($this->sales)
        ->post(route('sales.branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_OPEN,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    expect(WarehouseNotification::query()->count())->toBe(0);

    $this->actingAs($this->sales)
        ->put(route('sales.branch-withdrawals.update', $withdrawal), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_IN_PROGRESS,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ])
        ->assertRedirect(route('sales.branch-withdrawals.index'));

    $notification = WarehouseNotification::query()
        ->where('user_id', $this->warehouse->id)
        ->where('type', WarehouseNotification::TYPE_BRANCH_WITHDRAWAL)
        ->firstOrFail();

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.notifications.open', $notification))
        ->assertRedirect(route('branch-withdrawals.index', [
            'search' => $withdrawal->withdrawal_number,
            'search_field' => 'number',
            'exact' => 1,
        ]));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('dismisses an unread notification when a warehouse task is returned to sales', function () {
    $offer = notificationOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)->put(route('offers.status', $offer), [
        'status' => Offer::STATUS_IN_PROGRESS,
    ]);

    $notification = WarehouseNotification::query()
        ->where('user_id', $this->warehouse->id)
        ->firstOrFail();

    $this->actingAs($this->warehouse)
        ->put(route('warehouse.offers.status', $offer), [
            'status' => Offer::STATUS_OFFER,
        ])
        ->assertRedirect(route('warehouse.offers.index'));

    expect($notification->fresh()->dismissed_at)->not->toBeNull();

    $this->actingAs($this->warehouse)
        ->getJson(route('warehouse.notifications.index'))
        ->assertOk()
        ->assertJsonPath('unread_count', 0);
});

it('creates a fresh notification when a returned offer is handed to warehouse again', function () {
    $offer = notificationOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)->put(route('offers.status', $offer), [
        'status' => Offer::STATUS_IN_PROGRESS,
    ]);

    $this->actingAs($this->warehouse)->put(route('warehouse.offers.status', $offer), [
        'status' => Offer::STATUS_OFFER,
    ]);

    $this->actingAs($this->sales)->put(route('offers.status', $offer), [
        'status' => Offer::STATUS_IN_PROGRESS,
    ]);

    expect(
        WarehouseNotification::query()
            ->where('user_id', $this->warehouse->id)
            ->where('type', WarehouseNotification::TYPE_OFFER)
            ->where('subject_id', $offer->id)
            ->count()
    )->toBe(2);

    expect(
        WarehouseNotification::query()
            ->where('user_id', $this->warehouse->id)
            ->whereNull('read_at')
            ->whereNull('dismissed_at')
            ->count()
    )->toBe(1);
});

it('blocks notification endpoints for every non warehouse role even through a direct url', function () {
    foreach ([$this->manager, $this->admin, $this->sales] as $user) {
        $this->actingAs($user)
            ->getJson(route('warehouse.notifications.index'))
            ->assertForbidden();
    }
});

it('shows the notification bell only in the warehouse sidebar', function () {
    $offer = notificationOffer($this->customer, $this->sales);

    $this->actingAs($this->sales)->put(route('offers.status', $offer), [
        'status' => Offer::STATUS_IN_PROGRESS,
    ]);

    $this->actingAs($this->warehouse)
        ->get(route('warehouse.offers.index'))
        ->assertOk()
        ->assertSee('warehouse-notification-button')
        ->assertSee('Neue Lageraufträge')
        ->assertSee('Neues Angebot ' . $offer->offer_number);

    $this->actingAs($this->manager)
        ->get(route('products.index'))
        ->assertOk()
        ->assertDontSee('warehouse-notification-button');
});
