<?php

namespace Intrfce\FFFlags;

use Illuminate\Support\ServiceProvider;
use Intrfce\FFFlags\Commands\MakeFeatureCommand;
use Intrfce\FFFlags\Commands\PurgeFeatureFlagResultsCommand;
use Intrfce\FFFlags\Contracts\ResultStore;
use Intrfce\FFFlags\Drivers\DatabaseResultStore;

class FFFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ffflags.php', 'ffflags');

        $this->app->singleton(ResultStore::class, DatabaseResultStore::class);

        $this->app->singleton(FeatureFlagManager::class, function ($app) {
            return new FeatureFlagManager($app->make(ResultStore::class));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/ffflags.php' => config_path('ffflags.php'),
        ], 'ffflags-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFeatureCommand::class,
                PurgeFeatureFlagResultsCommand::class,
            ]);
        }
    }
}
