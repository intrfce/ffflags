<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intrfce\FFFlags\Http\Middleware\AllFeatureFlagsEnabled;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('allows request when all features are active', function () {
    $middleware = new AllFeatureFlagsEnabled([
        AlwaysActiveFeature::class,
        AlwaysActiveFeature::class,
    ]);

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts when at least one feature is inactive', function () {
    $middleware = new AllFeatureFlagsEnabled([
        AlwaysActiveFeature::class,
        AlwaysInactiveFeature::class,
    ]);

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );
})->throws(HttpException::class);

it('aborts when all features are inactive', function () {
    $middleware = new AllFeatureFlagsEnabled([
        AlwaysInactiveFeature::class,
        AlwaysInactiveFeature::class,
    ]);

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );
})->throws(HttpException::class);

it('allows request with empty array', function () {
    $middleware = new AllFeatureFlagsEnabled([]);

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});
