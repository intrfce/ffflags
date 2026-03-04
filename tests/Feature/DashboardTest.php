<?php

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;

define('FFFLAGS_TEST_APP_KEY', 'base64:'.base64_encode(random_bytes(32)));

uses()->beforeEach(function () {
    $this->app['config']->set('app.key', FFFLAGS_TEST_APP_KEY);
})->in(__DIR__);

it('returns 403 when gate denies access', function () {
    $user = new AuthUser();
    $user->id = 1;
    $user->email = 'nobody@example.com';

    $this->actingAs($user)
        ->get('/ffflags')
        ->assertStatus(403);
});

it('returns 200 when gate allows access', function () {
    Gate::define('view-ffflags-dashboard', function ($user) {
        return true;
    });

    $user = new AuthUser();
    $user->id = 1;
    $user->email = 'admin@example.com';

    $this->actingAs($user)
        ->get('/ffflags')
        ->assertStatus(200);
});

it('displays discovered features on the dashboard', function () {
    Gate::define('view-ffflags-dashboard', fn ($user) => true);

    $this->app->singleton(FeatureFlagDiscovery::class, function () {
        return new FeatureFlagDiscovery(
            directories: [],
            classes: [AlwaysActiveFeature::class],
        );
    });

    $user = new AuthUser();
    $user->id = 1;
    $user->email = 'admin@example.com';

    $this->actingAs($user)
        ->get('/ffflags')
        ->assertStatus(200)
        ->assertSee('Always Active');
});

it('shows empty state when no features are discovered', function () {
    Gate::define('view-ffflags-dashboard', fn ($user) => true);

    $this->app->singleton(FeatureFlagDiscovery::class, function () {
        return new FeatureFlagDiscovery(
            directories: [],
            classes: [],
        );
    });

    $user = new AuthUser();
    $user->id = 1;
    $user->email = 'admin@example.com';

    $this->actingAs($user)
        ->get('/ffflags')
        ->assertStatus(200)
        ->assertSee('No feature flags discovered');
});
