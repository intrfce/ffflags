<?php

use Intrfce\FFFlags\Contracts\ResultStore;
use Intrfce\FFFlags\Models\FeatureFlagResult;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;

it('stores and retrieves a result', function () {
    $store = app(ResultStore::class);

    $store->store(ScopedFeature::class, 'users', '1', true);

    expect($store->get(ScopedFeature::class, 'users', '1'))->toBeTrue();
});

it('returns null when no result exists', function () {
    $store = app(ResultStore::class);

    expect($store->get(ScopedFeature::class, 'users', '999'))->toBeNull();
});

it('stores result with null scope', function () {
    $store = app(ResultStore::class);

    $store->store(ScopedFeature::class, null, null, false);

    expect($store->get(ScopedFeature::class, null, null))->toBeFalse();
});

it('updates existing result on re-store', function () {
    $store = app(ResultStore::class);

    $store->store(ScopedFeature::class, 'users', '1', true);
    $store->store(ScopedFeature::class, 'users', '1', false);

    expect($store->get(ScopedFeature::class, 'users', '1'))->toBeFalse();
    expect(FeatureFlagResult::count())->toBe(1);
});

it('deletes a single result', function () {
    $store = app(ResultStore::class);

    $store->store(ScopedFeature::class, 'users', '1', true);
    $store->store(ScopedFeature::class, 'users', '2', false);

    $store->delete(ScopedFeature::class, 'users', '1');

    expect($store->get(ScopedFeature::class, 'users', '1'))->toBeNull();
    expect($store->get(ScopedFeature::class, 'users', '2'))->toBeFalse();
});

it('purges all results', function () {
    $store = app(ResultStore::class);

    $store->store(ScopedFeature::class, 'users', '1', true);
    $store->store(ScopedFeature::class, 'users', '2', false);

    $store->purge();

    expect(FeatureFlagResult::count())->toBe(0);
});
