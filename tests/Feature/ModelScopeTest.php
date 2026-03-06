<?php

use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\Exceptions\ScopeTypeMismatchException;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;
use Intrfce\FFFlags\Tests\Fixtures\Features\ModelScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ModelScopedWithResolveFeature;
use Intrfce\FFFlags\Tests\Fixtures\User;

beforeEach(function () {
    app(\Intrfce\FFFlags\FeatureFlagManager::class)->purgeAll();
});

it('returns true for Equals condition when scope ID matches', function () {
    $user = new User();
    $user->id = 1;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::Equals->value,
        'value' => [1],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeTrue();
});

it('returns false for Equals condition when scope ID does not match', function () {
    $user = new User();
    $user->id = 2;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::Equals->value,
        'value' => [1],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeFalse();
});

it('returns true for DoesNotEqual condition when scope ID does not match', function () {
    $user = new User();
    $user->id = 2;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::DoesNotEqual->value,
        'value' => [1],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeTrue();
});

it('returns false for DoesNotEqual condition when scope ID matches', function () {
    $user = new User();
    $user->id = 1;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::DoesNotEqual->value,
        'value' => [1],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeFalse();
});

it('returns true for IsOneOf condition when scope ID is in array', function () {
    $user = new User();
    $user->id = 3;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2, 3],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeTrue();
});

it('returns false for IsOneOf condition when scope ID is not in array', function () {
    $user = new User();
    $user->id = 4;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1, 2, 3],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeFalse();
});

it('returns true for IsNoneOf condition when scope ID is not in array', function () {
    $user = new User();
    $user->id = 4;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::IsNoneOf->value,
        'value' => [1, 2, 3],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeTrue();
});

it('returns false for IsNoneOf condition when scope ID is in array', function () {
    $user = new User();
    $user->id = 2;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::IsNoneOf->value,
        'value' => [1, 2, 3],
    ]);

    expect(ModelScopedFeature::for($user)->isActive())->toBeFalse();
});

it('returns fallback when no model scope row exists', function () {
    $user = new User();
    $user->id = 1;

    expect(ModelScopedFeature::for($user)->isActive())->toBeFalse();
});

it('returns fallback value from custom fallback method', function () {
    $user = new User();
    $user->id = 1;

    expect(ModelScopedWithResolveFeature::for($user)->isActive())->toBeFalse();
});

it('prefers model scope over fallback when row exists', function () {
    $user = new User();
    $user->id = 1;

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped-with-resolve',
        'scope_type' => (new User())->getMorphClass(),
        'condition' => ScopeCondition::IsOneOf->value,
        'value' => [1],
    ]);

    expect(ModelScopedWithResolveFeature::for($user)->isActive())->toBeTrue();
});

it('throws ScopeRequiredException when no scope is provided', function () {
    ModelScopedFeature::for(null)->isActive();
})->throws(\Intrfce\FFFlags\Exceptions\ScopeRequiredException::class);

it('throws ScopeTypeMismatchException for disallowed model type', function () {
    $otherModel = new class extends \Illuminate\Database\Eloquent\Model {
        protected $table = 'users';
    };
    $otherModel->id = 1;

    ModelScopedFeature::for($otherModel)->isActive();
})->throws(ScopeTypeMismatchException::class);
