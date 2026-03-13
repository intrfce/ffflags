<?php

namespace Intrfce\FFFlags;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Intrfce\FFFlags\Commands\ActivateFeatureCommand;
use Intrfce\FFFlags\Commands\DeactivateFeatureCommand;
use Intrfce\FFFlags\Commands\MakeFeatureCommand;
use Intrfce\FFFlags\Commands\PublishJavaScriptFeatureFlagsCommand;
use Intrfce\FFFlags\Commands\PurgeFeatureFlagResultsCommand;
use Intrfce\FFFlags\Commands\ValidateFeatureFlagsCommand;
use Intrfce\FFFlags\Contracts\ResultStore;
use Intrfce\FFFlags\Drivers\DatabaseResultStore;
use Intrfce\FFFlags\Http\Middleware\FeatureFlagMiddleware;

class FFFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/ffflags.php", "ffflags");

        $this->app->singleton(ResultStore::class, DatabaseResultStore::class);

        $this->app->singleton(FeatureFlagManager::class, function ($app) {
            return new FeatureFlagManager($app->make(ResultStore::class));
        });

        $this->app->singleton(FeatureFlagDiscovery::class, function ($app) {
            $config = $app["config"]->get("ffflags.discovery", []);

            return new FeatureFlagDiscovery(
                directories: $config["directories"] ?? ["app/Features"],
                classes: $config["classes"] ?? [],
            );
        });
    }

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . "/../config/ffflags.php" => config_path(
                    "ffflags.php",
                ),
            ],
            "ffflags-config",
        );

        $this->loadViewsFrom(__DIR__ . "/../resources/views", "ffflags");
        $this->loadRoutesFrom(__DIR__ . "/../routes/web.php");

        $this->publishesMigrations([
            __DIR__ . "/../database/migrations" => database_path("migrations"),
        ]);

        $this->registerRouteMacros();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ActivateFeatureCommand::class,
                DeactivateFeatureCommand::class,
                MakeFeatureCommand::class,
                PublishJavaScriptFeatureFlagsCommand::class,
                PurgeFeatureFlagResultsCommand::class,
                ValidateFeatureFlagsCommand::class,
            ]);
        }
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('featureFlagged', function (string|array $features, string $mode = 'all') {
            if (is_string($features)) {
                return $this->middleware(FeatureFlagMiddleware::isActive($features));
            }

            return $this->middleware(match ($mode) {
                'any' => FeatureFlagMiddleware::anyActive($features),
                default => FeatureFlagMiddleware::allActive($features),
            });
        });

        Router::macro('featureFlagged', function (string|array $features, string $mode = 'all') {
            if (is_string($features)) {
                $middleware = FeatureFlagMiddleware::isActive($features);
            } else {
                $middleware = match ($mode) {
                    'any' => FeatureFlagMiddleware::anyActive($features),
                    default => FeatureFlagMiddleware::allActive($features),
                };
            }

            return $this->middleware($middleware);
        });
    }
}
