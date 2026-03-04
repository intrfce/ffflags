<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intrfce\FFFlags\Exceptions\FeatureFlagNotResolvableFromMiddlewareException;
use Intrfce\FFFlags\Http\Middleware\FeatureFlagEnabled;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\MiddlewareScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('allows request when feature is active with no scope', function () {
    $middleware = new FeatureFlagEnabled(AlwaysActiveFeature::class);

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts with 403 when feature is inactive with no scope', function () {
    $middleware = new FeatureFlagEnabled(AlwaysInactiveFeature::class);

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );
})->throws(HttpException::class);

it('resolves scope from middleware interface when feature requires scope', function () {
    $middleware = new FeatureFlagEnabled(MiddlewareScopedFeature::class);

    $request = Request::create('/test');
    $request->headers->set('X-User-Email', 'active@example.com');

    $response = $middleware->handle(
        $request,
        fn () => new Response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts when scoped feature resolves to false', function () {
    $middleware = new FeatureFlagEnabled(MiddlewareScopedFeature::class);

    $request = Request::create('/test');
    $request->headers->set('X-User-Email', 'other@example.com');

    $middleware->handle(
        $request,
        fn () => new Response('ok'),
    );
})->throws(HttpException::class);

it('throws when scoped feature does not implement ResolvingFromMiddleware', function () {
    $middleware = new FeatureFlagEnabled(ScopedFeature::class);

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );
})->throws(FeatureFlagNotResolvableFromMiddlewareException::class);
