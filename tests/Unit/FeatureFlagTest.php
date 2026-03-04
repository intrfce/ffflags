<?php

use Intrfce\FFFlags\Exceptions\MissingFeatureFlagNameException;
use Intrfce\FFFlags\PendingSingleFeatureInteraction;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysInactiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AttributeNameFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\NoResolveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\User;

it('resolves name from attribute', function () {
    $feature = new AttributeNameFeature();
    expect($feature->getName())->toBe('Attribute Name');
});

it('throws when no name is set', function () {
    $feature = new class extends \Intrfce\FFFlags\FeatureFlag {};
    $feature->getName();
})->throws(MissingFeatureFlagNameException::class);

it('resolves slug from attribute', function () {
    $feature = new #[\Intrfce\FFFlags\Attributes\Name('Test', 'attr-slug')] class extends \Intrfce\FFFlags\FeatureFlag {};
    expect($feature->getSlug())->toBe('attr-slug');
});

it('falls back to Str::slug of name for slug', function () {
    $feature = new AttributeNameFeature();
    expect($feature->getSlug())->toBe('attribute-name');
});

it('resolves description from attribute', function () {
    $feature = new AttributeNameFeature();
    expect($feature->getDescription())->toBe('Attribute Description');
});

it('defaults description to empty string', function () {
    $feature = new AlwaysActiveFeature();
    expect($feature->getDescription())->toBe('');
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
