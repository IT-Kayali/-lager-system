<?php

namespace App\Providers;

use App\Console\LockedSchemaCommand;
use App\Models\Offer;
use App\Observers\OfferWalletObserver;
use App\Services\SystemBackupService;
use App\Services\SystemWriteLock;
use App\Support\SortableTables;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemWriteLock::class);
        $this->app->singleton(SystemBackupService::class);

        foreach ([
            MigrateCommand::class,
            RollbackCommand::class,
            ResetCommand::class,
            RefreshCommand::class,
            FreshCommand::class,
            InstallCommand::class,
            WipeCommand::class,
        ] as $command) {
            $this->app->extend($command, fn ($instance, $app) => new LockedSchemaCommand(
                $instance,
                $app->make(SystemBackupService::class)
            ));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Offer::observe(OfferWalletObserver::class);
        SortableTables::boot();
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
