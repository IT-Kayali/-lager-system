<?php

use App\Models\BranchWithdrawal;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('defaults the statistics dashboard to the current year', function () {
    $service = app(StatisticsService::class);
    $filters = $service->filters(Request::create('/statistics', 'GET'));

    expect($filters['period'])->toBe('year');
    expect($service->sections($filters))->toMatchArray([
        'summary' => true,
        'sales' => true,
        'products' => true,
        'customers' => true,
        'stock' => true,
        'branches' => true,
        'wallet' => true,
        'operations' => true,
    ]);
});

it('shows only product relevant sections when a product filter is active', function () {
    $service = app(StatisticsService::class);
    $filters = $service->filters(Request::create('/statistics', 'GET', [
        'product_id' => '42',
    ]));

    expect($service->sections($filters))->toMatchArray([
        'summary' => true,
        'sales' => true,
        'products' => true,
        'customers' => true,
        'stock' => true,
        'branches' => true,
        'wallet' => false,
        'operations' => false,
    ]);
});

it('focuses a branch filter on branch statistics', function () {
    $service = app(StatisticsService::class);
    $filters = $service->filters(Request::create('/statistics', 'GET', [
        'branch' => BranchWithdrawal::BRANCH_MAIN,
    ]));

    expect($service->sections($filters))->toMatchArray([
        'summary' => true,
        'sales' => false,
        'products' => false,
        'customers' => false,
        'stock' => false,
        'branches' => true,
        'wallet' => false,
        'operations' => false,
    ]);
});

it('renders the rebuilt statistics dashboard for a manager', function () {
    $manager = User::query()->create([
        'name' => 'Statistik Manager',
        'email' => 'statistics-manager@example.test',
        'password' => 'secret-password',
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $response = $this->actingAs($manager)->get(route('statistics.index'));

    $response
        ->assertOk()
        ->assertSeeText('ERP Gesamtübersicht')
        ->assertSeeText('Weitere ERP-Filter')
        ->assertSeeText('Verkauf & Umsatz')
        ->assertSeeText('Lager & Chargen')
        ->assertSeeText('Filialausgänge');
});

it('uses the completion date for completed sales statistics', function () {
    $service = file_get_contents(app_path('Services/StatisticsService.php'));

    expect($service)
        ->toContain("whereNotNull('completed_at')")
        ->toContain("whereBetween('completed_at', [\$from, \$to])")
        ->toContain("whereNull('completed_at')")
        ->toContain("whereBetween('created_at', [\$from, \$to])");
});

it('contains all new ERP filter dimensions in the statistics view', function () {
    $view = file_get_contents(resource_path('views/pages/statistics/index.blade.php'));

    expect($view)
        ->toContain('name="customer_group_id"')
        ->toContain('name="category_id"')
        ->toContain('name="supplier_id"')
        ->toContain('name="user_id"')
        ->toContain('name="shipping_method"')
        ->toContain('name="branch"')
        ->toContain('name="branch_status"')
        ->toContain('name="movement_type"');
});
