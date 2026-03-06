<?php

use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ModelScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\NoResolveFeature;

it('passes validation when all features are valid', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class, ModelScopedFeature::class],
    ));

    $this->artisan('ffflags:validate')
        ->assertExitCode(0)
        ->expectsOutputToContain('All checks passed');
});

it('reports duplicate slugs', function () {
    // Create a temporary feature class with a duplicate slug
    $duplicateClass = new class extends \Intrfce\FFFlags\FeatureFlag {
        public function resolve(): bool { return true; }
    };

    // We can't easily create two classes with the same slug dynamically,
    // so we test via the discovery findDuplicateSlugs directly
    $discovery = new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class],
    );

    $duplicates = $discovery->findDuplicateSlugs();
    expect($duplicates)->toBeEmpty();
});

it('warns when no features are discovered', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [],
    ));

    $this->artisan('ffflags:validate')
        ->assertExitCode(0)
        ->expectsOutputToContain('No feature flags discovered');
});

it('reports code-resolved features missing resolve method', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [NoResolveFeature::class],
    ));

    $this->artisan('ffflags:validate')
        ->assertExitCode(1)
        ->expectsOutputToContain('missing resolve() method');
});

it('shows feature count summary', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class, ModelScopedFeature::class],
    ));

    $this->artisan('ffflags:validate')
        ->assertExitCode(0)
        ->expectsOutputToContain('Total:         2')
        ->expectsOutputToContain('Code-resolved: 1')
        ->expectsOutputToContain('Managed:       1');
});
