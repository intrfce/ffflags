<?php

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route as RouteFacade;
use Intrfce\FFFlags\Http\Middleware\FeatureFlagMiddleware;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;

it('registers the featureFlagged route macro', function () {
    expect(Route::hasMacro('featureFlagged'))->toBeTrue();
});

it('adds isActive middleware for a single feature class', function () {
    $route = RouteFacade::get('/test-single', fn () => 'ok')->featureFlagged(AlwaysActiveFeature::class);

    expect($route->middleware())->toContain(
        FeatureFlagMiddleware::isActive(AlwaysActiveFeature::class),
    );
});

it('adds allActive middleware for an array of feature classes', function () {
    $route = RouteFacade::get('/test-all', fn () => 'ok')->featureFlagged([
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    ]);

    expect($route->middleware())->toContain(
        FeatureFlagMiddleware::allActive([
            AlwaysActiveFeature::class,
            AlwaysInactiveFeature::class,
        ]),
    );
});

it('adds anyActive middleware when mode is any', function () {
    $route = RouteFacade::get('/test-any', fn () => 'ok')->featureFlagged([
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    ], 'any');

    expect($route->middleware())->toContain(
        FeatureFlagMiddleware::anyActive([
            AlwaysActiveFeature::class,
            AlwaysInactiveFeature::class,
        ]),
    );
});

it('returns the route instance for chaining', function () {
    $route = RouteFacade::get('/test-chain', fn () => 'ok')->featureFlagged(AlwaysActiveFeature::class);

    expect($route)->toBeInstanceOf(Route::class);
});

it('allows request through when feature flag is active', function () {
    RouteFacade::get('/macro-active', fn () => 'allowed')
        ->featureFlagged(AlwaysActiveFeature::class);

    $this->get('/macro-active')->assertOk()->assertSee('allowed');
});

it('blocks request when feature flag is inactive', function () {
    RouteFacade::get('/macro-inactive', fn () => 'blocked')
        ->featureFlagged(AlwaysInactiveFeature::class);

    $this->get('/macro-inactive')->assertForbidden();
});

it('blocks request when not all feature flags are active', function () {
    RouteFacade::get('/macro-all-inactive', fn () => 'blocked')
        ->featureFlagged([AlwaysActiveFeature::class, AlwaysInactiveFeature::class]);

    $this->get('/macro-all-inactive')->assertForbidden();
});

it('allows request when any feature flag is active', function () {
    RouteFacade::get('/macro-any-active', fn () => 'allowed')
        ->featureFlagged([AlwaysInactiveFeature::class, AlwaysActiveFeature::class], 'any');

    $this->get('/macro-any-active')->assertOk()->assertSee('allowed');
});

// Router-level macro (Route::featureFlagged()->group(...))

it('registers the featureFlagged router macro', function () {
    expect(Router::hasMacro('featureFlagged'))->toBeTrue();
});

it('applies feature flag middleware to grouped routes', function () {
    RouteFacade::featureFlagged(AlwaysActiveFeature::class)->group(function () {
        RouteFacade::get('/group-active', fn () => 'allowed');
    });

    $this->get('/group-active')->assertOk()->assertSee('allowed');
});

it('blocks grouped routes when feature flag is inactive', function () {
    RouteFacade::featureFlagged(AlwaysInactiveFeature::class)->group(function () {
        RouteFacade::get('/group-inactive', fn () => 'blocked');
    });

    $this->get('/group-inactive')->assertForbidden();
});

it('applies allActive middleware to grouped routes by default', function () {
    RouteFacade::featureFlagged([AlwaysActiveFeature::class, AlwaysInactiveFeature::class])->group(function () {
        RouteFacade::get('/group-all', fn () => 'blocked');
    });

    $this->get('/group-all')->assertForbidden();
});

it('applies anyActive middleware to grouped routes when mode is any', function () {
    RouteFacade::featureFlagged([AlwaysInactiveFeature::class, AlwaysActiveFeature::class], 'any')->group(function () {
        RouteFacade::get('/group-any', fn () => 'allowed');
    });

    $this->get('/group-any')->assertOk()->assertSee('allowed');
});
