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
        ->assertSeeText('Umsatzentwicklung')
        ->assertSeeText('Angebotsstatus')
        ->assertSeeText('Top 10 Produkte')
        ->assertSeeText('Top 10 Kunden')
        ->assertSeeText('Lagerübersicht');
});

it('uses the completion date for completed sales statistics', function () {
    $service = file_get_contents(app_path('Services/StatisticsService.php'));

    expect($service)
        ->toContain("whereNotNull('completed_at')")
        ->toContain("whereBetween('completed_at', [\$from, \$to])")
        ->toContain("whereNull('completed_at')")
        ->toContain("whereBetween('created_at', [\$from, \$to])");
});

it('contains the active filter dimensions in the simplified statistics view', function () {
    $view = file_get_contents(resource_path('views/pages/statistics/index.blade.php'));

    expect($view)
        ->toContain('name="period"')
        ->toContain('name="customer_id"')
        ->toContain('name="product_id"')
        ->toContain('name="status"')
        ->toContain('name="date_from"')
        ->toContain('name="date_to"');
});


it('loads chart js from the local vite bundle instead of a public cdn', function () {
    $view = file_get_contents(resource_path('views/pages/statistics/index.blade.php'));
    $app = file_get_contents(resource_path('js/app.js'));

    expect($view)
        ->not->toContain('cdn.jsdelivr.net/npm/chart.js');

    expect($app)
        ->toContain("import Chart from 'chart.js/auto';")
        ->toContain('window.Chart = Chart;');
});
