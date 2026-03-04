<?php

namespace Intrfce\FFFlags\Tests;

use Intrfce\FFFlags\FFFlagsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FFFlagsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'FeatureFlag' => \Intrfce\FFFlags\Facades\FeatureFlag::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
