<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use ThuyDX\ABAC\Console\ClearAbacCacheCommand;
use ThuyDX\ABAC\Console\WarmAbacCacheCommand;
use ThuyDX\ABAC\Contracts\AbacEngineInterface;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\DSL\Evaluator;
use ThuyDX\ABAC\Engine\AbacEngine;
use ThuyDX\ABAC\Infrastructure\Repositories\UserPermissionConstraintRepository;

final class AbacServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'abac'
        );

        // Repository
        $this->app->bind(
            ConstraintRepositoryInterface::class,
            UserPermissionConstraintRepository::class
        );

        // DSL Evaluator
        $this->app->singleton(Evaluator::class);

        // ABAC Engine
        $this->app->singleton(
            AbacEngineInterface::class,
            function ($app) {

                $repository = $app->make(ConstraintRepositoryInterface::class);

                $policies = collect(config('abac.policies', []))
                    ->map(fn (string $policyClass) => $app->make($policyClass))
                    ->all();

                return new AbacEngine(
                    $repository,
                    $policies
                );
            }
        );
    }

    public function boot(): void
    {
        $this->registerAbacLoggingChannel();

        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('abac.php'),
        ], 'abac-config');

        $this->loadMigrationsFrom(
            __DIR__.'/../../database/migrations'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearAbacCacheCommand::class,
                WarmAbacCacheCommand::class,
            ]);
        }
    }

    /*
   |--------------------------------------------------------------------------
   | Register ABAC Logging Channel (Runtime Safe)
   |--------------------------------------------------------------------------
   */
    private function registerAbacLoggingChannel(): void
    {
        $channels = config('logging.channels', []);

        if (isset($channels['abac'])) {
            return;
        }

        $channels['abac'] = [
            'driver' => 'daily',
            'path' => storage_path('logs/abac.log'),
            'level' => config('abac.trace.log_level', 'info'),
            'days' => 14,
        ];

        config(['logging.channels' => $channels]);
    }
}
