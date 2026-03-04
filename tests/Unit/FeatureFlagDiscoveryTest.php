<?php

use Intrfce\FFFlags\DiscoveredFeatureFlag;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\BypassStorageFeature;

it('discovers explicitly registered classes', function () {
    $discovery = new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class, BypassStorageFeature::class],
    );

    $features = $discovery->discover();

    expect($features)->toHaveCount(2)
        ->and($features->first())->toBeInstanceOf(DiscoveredFeatureFlag::class);
});

it('ignores non-existent directories', function () {
    $discovery = new FeatureFlagDiscovery(
        directories: ['non/existent/path'],
        classes: [],
    );

    expect($discovery->discover())->toHaveCount(0);
});

it('caches results on subsequent calls', function () {
    $discovery = new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class],
    );

    $first = $discovery->discover();
    $second = $discovery->discover();

    expect($first)->toBe($second);
});

it('resets cache on flush', function () {
    $discovery = new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class],
    );

    $first = $discovery->discover();
    $discovery->flush();
    $third = $discovery->discover();

    expect($third)->not->toBe($first);
    expect($third)->toHaveCount(1);
});

it('deduplicates classes', function () {
    $discovery = new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class, AlwaysActiveFeature::class],
    );

    expect($discovery->discover())->toHaveCount(1);
});

it('discovers classes from directories', function () {
    $discovery = new FeatureFlagDiscovery(
        directories: [__DIR__.'/../Fixtures/Features'],
        classes: [],
    );

    $features = $discovery->discover();

    expect($features->count())->toBeGreaterThan(0)
        ->and($features->pluck('class')->toArray())->toContain(AlwaysActiveFeature::class);
});
