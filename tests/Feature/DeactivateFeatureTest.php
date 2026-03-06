<?php

use Intrfce\FFFlags\Actions\DeactivateFeature;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;
use Intrfce\FFFlags\Tests\Fixtures\Features\ModelScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\User;

beforeEach(function () {
    app(\Intrfce\FFFlags\FeatureFlagManager::class)->purgeAll();
});

it('removes specific IDs from the value list', function () {
    $feature = new ModelScopedFeature();
    $scopeType = (new User())->getMorphClass();

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => $scopeType,
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2, 3, 4],
    ]);

    $action = new DeactivateFeature();
    $action->handle($feature, [2, 3]);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope->value)->toBe([1, 4]);
});

it('deactivates all when all flag is true', function () {
    $feature = new ModelScopedFeature();
    $scopeType = (new User())->getMorphClass();

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => $scopeType,
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2, 3],
    ]);

    $action = new DeactivateFeature();
    $action->handle($feature, all: true);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope->value)->toBe([]);
});

it('does nothing when no model scope row exists', function () {
    $feature = new ModelScopedFeature();
    $action = new DeactivateFeature();

    $action->handle($feature, [1, 2]);

    expect(FeatureFlagModelScope::count())->toBe(0);
});

it('leaves other IDs intact', function () {
    $feature = new ModelScopedFeature();
    $scopeType = (new User())->getMorphClass();

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => $scopeType,
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2, 3, 4, 5],
    ]);

    $action = new DeactivateFeature();
    $action->handle($feature, [1]);

    $scope = FeatureFlagModelScope::where('feature_slug', 'model-scoped')->first();

    expect($scope->value)->toBe([2, 3, 4, 5]);
});
