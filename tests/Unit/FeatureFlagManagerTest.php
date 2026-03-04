<?php

use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\PendingFeatureInteraction;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;

it('returns PendingFeatureInteraction from for()', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null))->toBeInstanceOf(PendingFeatureInteraction::class);
});

it('resolves isActive correctly', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->isActive(AlwaysActiveFeature::class))->toBeTrue();
    expect($manager->for(null)->isActive(AlwaysInactiveFeature::class))->toBeFalse();
});

it('returns true for anyActive when at least one is active', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->anyActive([
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    ]))->toBeTrue();
});

it('returns false for anyActive when none are active', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->anyActive([
        AlwaysInactiveFeature::class,
    ]))->toBeFalse();
});

it('returns false for anyActive with empty array', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->anyActive([]))->toBeFalse();
});

it('returns true for allActive when all are active', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->allActive([
        AlwaysActiveFeature::class,
    ]))->toBeTrue();
});

it('returns false for allActive when at least one is inactive', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->allActive([
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    ]))->toBeFalse();
});

it('returns true for allActive with empty array', function () {
    $manager = app(FeatureFlagManager::class);
    expect($manager->for(null)->allActive([]))->toBeTrue();
});
