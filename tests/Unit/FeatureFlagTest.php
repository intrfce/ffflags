<?php

use Intrfce\FFFlags\PendingSingleFeatureInteraction;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AttributeNameFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\BothNameFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\NoResolveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\PropertyNameFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\User;

it('resolves name from property', function () {
    $feature = new PropertyNameFeature();
    expect($feature->getName())->toBe('Property Name');
});

it('resolves name from attribute', function () {
    $feature = new AttributeNameFeature();
    expect($feature->getName())->toBe('Attribute Name');
});

it('falls back to class name when no name set', function () {
    $feature = new AlwaysActiveFeature();
    expect($feature->getName())->toBe('AlwaysActiveFeature');
});

it('prioritises property over attribute for name', function () {
    $feature = new BothNameFeature();
    expect($feature->getName())->toBe('Property Name');
});

it('resolves description from property', function () {
    $feature = new PropertyNameFeature();
    expect($feature->getDescription())->toBe('Property Description');
});

it('resolves description from attribute', function () {
    $feature = new AttributeNameFeature();
    expect($feature->getDescription())->toBe('Attribute Description');
});

it('defaults description to empty string', function () {
    $feature = new AlwaysActiveFeature();
    expect($feature->getDescription())->toBe('');
});

it('prioritises property over attribute for description', function () {
    $feature = new BothNameFeature();
    expect($feature->getDescription())->toBe('Property Description');
});

it('returns PendingSingleFeatureInteraction from for()', function () {
    $result = AlwaysActiveFeature::for(null);
    expect($result)->toBeInstanceOf(PendingSingleFeatureInteraction::class);
});

it('resolves as active when resolve returns true', function () {
    expect(AlwaysActiveFeature::for(null)->isActive())->toBeTrue();
});

it('resolves as inactive when resolve returns false', function () {
    expect(AlwaysInactiveFeature::for(null)->isActive())->toBeFalse();
});

it('resolves as inactive when no resolve method exists and no scope given', function () {
    expect(NoResolveFeature::for(null)->isActive())->toBeFalse();
});

it('works with scoped resolve method', function () {
    $activeUser = new User();
    $activeUser->id = 1;
    $activeUser->email = 'active@example.com';

    $inactiveUser = new User();
    $inactiveUser->id = 2;
    $inactiveUser->email = 'other@example.com';

    expect(ScopedFeature::for($activeUser)->isActive())->toBeTrue();
    expect(ScopedFeature::for($inactiveUser)->isActive())->toBeFalse();
});
