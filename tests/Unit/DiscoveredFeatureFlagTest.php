<?php

use Intrfce\FFFlags\DiscoveredFeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AttributeNameFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\BypassStorageFeature;

it('creates DTO from a feature class', function () {
    $dto = DiscoveredFeatureFlag::fromClass(AttributeNameFeature::class);

    expect($dto->class)->toBe(AttributeNameFeature::class)
        ->and($dto->name)->toBe('Attribute Name')
        ->and($dto->slug)->toBe('attribute-name')
        ->and($dto->description)->toBe('Attribute Description')
        ->and($dto->bypassesStorage)->toBeFalse();
});

it('detects bypass storage attribute', function () {
    $dto = DiscoveredFeatureFlag::fromClass(BypassStorageFeature::class);

    expect($dto->bypassesStorage)->toBeTrue();
});

it('handles features without description', function () {
    $dto = DiscoveredFeatureFlag::fromClass(AlwaysActiveFeature::class);

    expect($dto->description)->toBe('');
});
