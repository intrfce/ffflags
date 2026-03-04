<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intrfce\FFFlags\Http\Middleware\AnyFeatureFlagEnabled;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('allows request when at least one feature is active', function () {
    $middleware = new AnyFeatureFlagEnabled([
        AlwaysInactiveFeature::class,
        AlwaysActiveFeature::class,
    ]);

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});

it('allows request when all features are active', function () {
    $middleware = new AnyFeatureFlagEnabled([
        AlwaysActiveFeature::class,
        AlwaysActiveFeature::class,
    ]);

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});

it('aborts when no features are active', function () {
    $middleware = new AnyFeatureFlagEnabled([
        AlwaysInactiveFeature::class,
        AlwaysInactiveFeature::class,
    ]);

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );
})->throws(HttpException::class);

it('aborts with empty array', function () {
    $middleware = new AnyFeatureFlagEnabled([]);

    $middleware->handle(
        Request::create('/test'),
        fn () => new Response('ok'),
    );
})->throws(HttpException::class);
