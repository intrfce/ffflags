<?php

use Intrfce\FFFlags\Contracts\ResultStore;
use Intrfce\FFFlags\Models\FeatureFlagResult;

it('stores and retrieves a result', function () {
    $store = app(ResultStore::class);

    $store->store('scoped-feature', 'users', '1', true);

    expect($store->get('scoped-feature', 'users', '1'))->toBeTrue();
});

it('returns null when no result exists', function () {
    $store = app(ResultStore::class);

    expect($store->get('scoped-feature', 'users', '999'))->toBeNull();
});

it('stores result with null scope', function () {
    $store = app(ResultStore::class);

    $store->store('scoped-feature', null, null, false);

    expect($store->get('scoped-feature', null, null))->toBeFalse();
});

it('updates existing result on re-store', function () {
    $store = app(ResultStore::class);

    $store->store('scoped-feature', 'users', '1', true);
    $store->store('scoped-feature', 'users', '1', false);

    expect($store->get('scoped-feature', 'users', '1'))->toBeFalse();
    expect(FeatureFlagResult::count())->toBe(1);
});

it('deletes a single result', function () {
    $store = app(ResultStore::class);

    $store->store('scoped-feature', 'users', '1', true);
    $store->store('scoped-feature', 'users', '2', false);

    $store->delete('scoped-feature', 'users', '1');

    expect($store->get('scoped-feature', 'users', '1'))->toBeNull();
    expect($store->get('scoped-feature', 'users', '2'))->toBeFalse();
});

it('purges all results', function () {
    $store = app(ResultStore::class);

    $store->store('scoped-feature', 'users', '1', true);
    $store->store('scoped-feature', 'users', '2', false);

    $store->purge();

    expect(FeatureFlagResult::count())->toBe(0);
});
