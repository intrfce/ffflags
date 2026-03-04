<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intrfce\FFFlags\Exceptions\FeatureFlagNotResolvableFromMiddlewareException;
use Intrfce\FFFlags\Http\Middleware\FeatureFlagMiddleware;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\MiddlewareScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;
use Symfony\Component\HttpKernel\Exception\HttpException;

afterEach(function () {
    FeatureFlagMiddleware::whenInactive(null);
});

// isActive

it('allows request when feature is active with no scope', function () {
    $middleware = new FeatureFlagMiddleware();

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'is',
        AlwaysActiveFeature::class,
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts with 403 when feature is inactive with no scope', function () {
    $middleware = new FeatureFlagMiddleware();

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'is',
        AlwaysInactiveFeature::class,
    );
})->throws(HttpException::class);

it('resolves scope from middleware interface when feature requires scope', function () {
    $middleware = new FeatureFlagMiddleware();

    $request = Request::create('/test');
    $request->headers->set('X-User-Email', 'active@example.com');

    $response = $middleware->handle(
        $request,
        fn () => new Response('ok'),
        'is',
        MiddlewareScopedFeature::class,
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts when scoped feature resolves to false', function () {
    $middleware = new FeatureFlagMiddleware();

    $request = Request::create('/test');
    $request->headers->set('X-User-Email', 'other@example.com');

    $middleware->handle(
        $request,
        fn () => new Response('ok'),
        'is',
        MiddlewareScopedFeature::class,
    );
})->throws(HttpException::class);

it('throws when scoped feature does not implement ResolvingFromMiddleware', function () {
    $middleware = new FeatureFlagMiddleware();

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'is',
        ScopedFeature::class,
    );
})->throws(FeatureFlagNotResolvableFromMiddlewareException::class);

it('generates correct middleware string from isActive()', function () {
    expect(FeatureFlagMiddleware::isActive(AlwaysActiveFeature::class))
        ->toBe(FeatureFlagMiddleware::class.':is,'.AlwaysActiveFeature::class);
});

// allActive

it('allows request when all features are active', function () {
    $middleware = new FeatureFlagMiddleware();

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'all',
        AlwaysActiveFeature::class,
        AlwaysActiveFeature::class,
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts when at least one feature is inactive', function () {
    $middleware = new FeatureFlagMiddleware();

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'all',
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    );
})->throws(HttpException::class);

it('generates correct middleware string from allActive()', function () {
    expect(FeatureFlagMiddleware::allActive([AlwaysActiveFeature::class, AlwaysInactiveFeature::class]))
        ->toBe(FeatureFlagMiddleware::class.':all,'.AlwaysActiveFeature::class.','.AlwaysInactiveFeature::class);
});

// anyActive

it('allows request when at least one feature is active', function () {
    $middleware = new FeatureFlagMiddleware();

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'any',
        AlwaysInactiveFeature::class,
        AlwaysActiveFeature::class,
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts when no features are active', function () {
    $middleware = new FeatureFlagMiddleware();

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'any',
        AlwaysInactiveFeature::class,
        AlwaysInactiveFeature::class,
    );
})->throws(HttpException::class);

it('generates correct middleware string from anyActive()', function () {
    expect(FeatureFlagMiddleware::anyActive([AlwaysActiveFeature::class, AlwaysInactiveFeature::class]))
        ->toBe(FeatureFlagMiddleware::class.':any,'.AlwaysActiveFeature::class.','.AlwaysInactiveFeature::class);
});

// whenInactive

it('calls whenInactive callback instead of throwing when feature is inactive', function () {
    FeatureFlagMiddleware::whenInactive(function (Request $request, array $features) {
        return new Response('custom denied', 403);
    });

    $middleware = new FeatureFlagMiddleware();

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'is',
        AlwaysInactiveFeature::class,
    );

    expect($response->getStatusCode())->toBe(403);
    expect($response->getContent())->toBe('custom denied');
});

it('passes inactive feature classes to whenInactive callback', function () {
    $capturedFeatures = null;

    FeatureFlagMiddleware::whenInactive(function (Request $request, array $features) use (&$capturedFeatures) {
        $capturedFeatures = $features;

        return new Response('denied', 403);
    });

    $middleware = new FeatureFlagMiddleware();

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'all',
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    );

    expect($capturedFeatures)->toBe([AlwaysInactiveFeature::class]);
});

it('does not call whenInactive callback when features are active', function () {
    $called = false;

    FeatureFlagMiddleware::whenInactive(function () use (&$called) {
        $called = true;

        return new Response('denied', 403);
    });

    $middleware = new FeatureFlagMiddleware();

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
        'is',
        AlwaysActiveFeature::class,
    );

    expect($called)->toBeFalse();
});
