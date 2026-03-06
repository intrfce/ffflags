<?php

namespace Intrfce\FFFlags\Concerns;

use Illuminate\Database\Eloquent\Model;
use Intrfce\FFFlags\Exceptions\InvalidScopeException;
use Intrfce\FFFlags\Exceptions\ScopeProvidedButResolveHandlerMissingException;
use Intrfce\FFFlags\Exceptions\ScopeRequiredException;
use Intrfce\FFFlags\Exceptions\ScopeTypeMismatchException;
use Intrfce\FFFlags\Exceptions\ScopeDoesNotHaveKeyException;
use Intrfce\FFFlags\Attributes\BypassStorage;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

trait ResolvesFeatureFlags
{
    protected static function resolveFeature(FeatureFlag $feature, mixed $scope, FeatureFlagManager $manager): bool
    {
        if ($feature instanceof ManagedFeatureFlag) {
            return static::resolveManagedFeature($feature, $scope, $manager);
        }

        return static::resolveCodeFeature($feature, $scope, $manager);
    }

    protected static function resolveManagedFeature(ManagedFeatureFlag $feature, mixed $scope, FeatureFlagManager $manager): bool
    {
        $featureClass = get_class($feature);
        $featureSlug = $feature->getSlug();
        $expectedModel = $feature->getModelClass();

        if ($scope === null) {
            throw new ScopeRequiredException(featureClass: $featureClass);
        }

        if (! ($scope instanceof Model)) {
            throw new InvalidScopeException(
                featureClass: $featureClass,
                actualType: get_debug_type($scope),
            );
        }

        if ($scope->getKey() === null) {
            throw new ScopeDoesNotHaveKeyException(
                featureClass: $featureClass,
                scopeType: get_class($scope),
            );
        }

        if (! ($scope instanceof $expectedModel)) {
            throw new ScopeTypeMismatchException(
                featureClass: $featureClass,
                expectedType: $expectedModel,
                actualType: get_debug_type($scope),
            );
        }

        $scopeType = $scope->getMorphClass();
        $scopeId = $scope->getKey();
        $cacheKey = FeatureFlagManager::buildCacheKey($featureSlug, $scopeType, $scopeId);

        $memoryCached = $manager->getFromMemory($cacheKey);
        if ($memoryCached !== null) {
            return $memoryCached;
        }

        $storeCached = $manager->getStore()->get($featureSlug, $scopeType, $scopeId);
        if ($storeCached !== null) {
            $manager->storeInMemory($cacheKey, $storeCached);
            return $storeCached;
        }

        $result = static::evaluateModelScope($feature, $scope);

        $manager->storeInMemory($cacheKey, $result);
        $manager->getStore()->store($featureSlug, $scopeType, $scopeId, $result);

        return $result;
    }

    protected static function resolveCodeFeature(FeatureFlag $feature, mixed $scope, FeatureFlagManager $manager): bool
    {
        $featureClass = get_class($feature);
        $featureSlug = $feature->getSlug();

        if ($scope !== null && ! ($scope instanceof Model)) {
            throw new InvalidScopeException(
                featureClass: $featureClass,
                actualType: get_debug_type($scope),
            );
        }

        if ($scope instanceof Model && $scope->getKey() === null) {
            throw new ScopeDoesNotHaveKeyException(
                featureClass: $featureClass,
                scopeType: get_class($scope),
            );
        }

        $ref = new ReflectionClass($feature);
        $bypassStorage = count($ref->getAttributes(BypassStorage::class)) > 0;

        $scopeType = $scope?->getMorphClass();
        $scopeId = $scope?->getKey();
        $cacheKey = FeatureFlagManager::buildCacheKey($featureSlug, $scopeType, $scopeId);

        $memoryCached = $manager->getFromMemory($cacheKey);
        if ($memoryCached !== null) {
            return $memoryCached;
        }

        if (! $bypassStorage) {
            $storeCached = $manager->getStore()->get($featureSlug, $scopeType, $scopeId);
            if ($storeCached !== null) {
                $manager->storeInMemory($cacheKey, $storeCached);
                return $storeCached;
            }
        }

        $result = static::evaluateResolveMethod($feature, $scope);

        $manager->storeInMemory($cacheKey, $result);

        if (! $bypassStorage) {
            $manager->getStore()->store($featureSlug, $scopeType, $scopeId, $result);
        }

        return $result;
    }

    protected static function evaluateResolveMethod(FeatureFlag $feature, mixed $scope): bool
    {
        if (! method_exists($feature, 'resolve')) {
            if ($scope !== null) {
                throw new ScopeProvidedButResolveHandlerMissingException(
                    featureClass: get_class($feature),
                );
            }

            return false;
        }

        $method = new ReflectionMethod($feature, 'resolve');
        $params = $method->getParameters();

        if (count($params) === 0) {
            return (bool) $feature->resolve();
        }

        $firstParam = $params[0];
        $type = $firstParam->getType();

        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $expectedClass = $type->getName();

            if (! ($scope instanceof $expectedClass)) {
                throw new ScopeTypeMismatchException(
                    featureClass: get_class($feature),
                    expectedType: $expectedClass,
                    actualType: get_debug_type($scope),
                );
            }
        }

        return (bool) $feature->resolve($scope);
    }

    protected static function evaluateModelScope(ManagedFeatureFlag $feature, Model $scope): bool
    {
        $modelScope = FeatureFlagModelScope::query()
            ->where('feature_slug', $feature->getSlug())
            ->where('scope_type', $scope->getMorphClass())
            ->first();

        if ($modelScope === null) {
            return $feature->fallback();
        }

        $key = $scope->getKey();
        $values = $modelScope->value;

        return match ($modelScope->condition) {
            ScopeCondition::Equals => in_array($key, $values),
            ScopeCondition::DoesNotEqual => ! in_array($key, $values),
            ScopeCondition::IsOneOf => in_array($key, $values),
            ScopeCondition::IsNoneOf => ! in_array($key, $values),
        };
    }
}
