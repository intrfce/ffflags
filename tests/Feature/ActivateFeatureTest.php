<?php

use Intrfce\FFFlags\Actions\ActivateFeature;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;
use Intrfce\FFFlags\Tests\Fixtures\Features\ModelScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\User;

beforeEach(function () {
    app(\Intrfce\FFFlags\FeatureFlagManager::class)->purgeAll();
});

it('creates a new model scope row with IsOneOf condition and given IDs', function () {
    $feature = new ModelScopedFeature();
    $action = new ActivateFeature();

    $action->handle($feature, [1, 2, 3]);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope)->not->toBeNull();
    expect($scope->condition)->toBe(ScopeCondition::IsOneOf);
    expect($scope->value)->toBe([1, 2, 3]);
});

it('is additive by default', function () {
    $feature = new ModelScopedFeature();
    $scopeType = (new User())->getMorphClass();

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => $scopeType,
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2],
    ]);

    $action = new ActivateFeature();
    $action->handle($feature, [2, 3, 4]);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope->value)->toBe([1, 2, 3, 4]);
});

it('replaces the list when replace is true', function () {
    $feature = new ModelScopedFeature();
    $scopeType = (new User())->getMorphClass();

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => $scopeType,
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2, 3],
    ]);

    $action = new ActivateFeature();
    $action->handle($feature, [5, 6], replace: true);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope->value)->toBe([5, 6]);
});

it('deduplicates IDs', function () {
    $feature = new ModelScopedFeature();
    $action = new ActivateFeature();

    $action->handle($feature, [1, 1, 2, 2, 3]);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope->value)->toBe([1, 2, 3]);
});
