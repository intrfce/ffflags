<?php

use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\Models\FeatureFlagResult;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\CountingFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\User;

beforeEach(function () {
    CountingFeature::$resolveCount = 0;
});

it('stores result in database after first evaluation', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'active@example.com';

    ScopedFeature::for($user)->isActive();

    expect(FeatureFlagResult::count())->toBe(1);

    $record = FeatureFlagResult::first();
    expect($record->feature_class)->toBe(ScopedFeature::class);
    expect($record->scope_type)->toBe($user->getMorphClass());
    expect($record->scope_id)->toBe((string) $user->getKey());
    expect($record->result)->toBeTrue();
});

it('caches scopeless results with null scope fields', function () {
    AlwaysActiveFeature::for(null)->isActive();

    $record = FeatureFlagResult::first();
    expect($record->feature_class)->toBe(AlwaysActiveFeature::class);
    expect($record->scope_type)->toBeNull();
    expect($record->scope_id)->toBeNull();
    expect($record->result)->toBeTrue();
});

it('only calls resolve once for the same feature and scope (in-memory cache)', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'active@example.com';

    CountingFeature::for($user)->isActive();
    CountingFeature::for($user)->isActive();
    CountingFeature::for($user)->isActive();

    expect(CountingFeature::$resolveCount)->toBe(1);
});

it('returns cached result from database on fresh manager', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'active@example.com';

    // First call — resolves and stores.
    CountingFeature::for($user)->isActive();
    expect(CountingFeature::$resolveCount)->toBe(1);

    // Reset the manager (simulates a new request).
    $this->app->forgetInstance(FeatureFlagManager::class);

    // Second call — reads from database, not resolve().
    CountingFeature::for($user)->isActive();
    expect(CountingFeature::$resolveCount)->toBe(1);
});

it('stores different results for different scopes', function () {
    $activeUser = new User();
    $activeUser->id = 1;
    $activeUser->email = 'active@example.com';

    $inactiveUser = new User();
    $inactiveUser->id = 2;
    $inactiveUser->email = 'other@example.com';

    expect(ScopedFeature::for($activeUser)->isActive())->toBeTrue();
    expect(ScopedFeature::for($inactiveUser)->isActive())->toBeFalse();

    expect(FeatureFlagResult::count())->toBe(2);
});
