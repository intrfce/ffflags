<?php

namespace Intrfce\FFFlags\Concerns;

use Illuminate\Database\Eloquent\Model;
use Intrfce\FFFlags\Exceptions\InvalidScopeException;
use Intrfce\FFFlags\Exceptions\ScopeProvidedButResolveHandlerMissingException;
use Intrfce\FFFlags\Exceptions\ScopeRequiredException;
use Intrfce\FFFlags\Exceptions\ScopeTypeMismatchException;
use Intrfce\FFFlags\Exceptions\ScopeDoesNotHaveKeyException;
use Intrfce\FFFlags\Attributes\BypassStorage;
use Intrfce\FFFlags\Attributes\ScopeWithModelRules;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

trait ResolvesFeatureFlags
{
    protected static function resolveFeature(FeatureFlag $feature, mixed $scope, FeatureFlagManager $manager): bool
    {
        $featureClass = get_class($feature);
        $featureSlug = $feature->getSlug();

        // Validate scope type — must be null or a Model.
        if ($scope !== null && ! ($scope instanceof Model)) {
            throw new InvalidScopeException(
                featureClass: $featureClass,
                actualType: get_debug_type($scope),
            );
        }

        // Validate that Model scope has been persisted.
        if ($scope instanceof Model && $scope->getKey() === null) {
            throw new ScopeDoesNotHaveKeyException(
                featureClass: $featureClass,
                scopeType: get_class($scope),
            );
        }

        $ref = new ReflectionClass($feature);
        $bypassStorage = count($ref->getAttributes(BypassStorage::class)) > 0;
        $scopeWithModelsAttrs = $ref->getAttributes(ScopeWithModelRules::class);
        $usesModelScopes = count($scopeWithModelsAttrs) > 0;

        // Validate scope against #[ScopeWithModelRules] if present.
        if ($usesModelScopes) {
            if ($scope === null) {
                throw new ScopeRequiredException(featureClass: $featureClass);
            }

            $expectedModel = $scopeWithModelsAttrs[0]->newInstance()->model;

            if (! ($scope instanceof $expectedModel)) {
                throw new ScopeTypeMismatchException(
                    featureClass: $featureClass,
                    expectedType: $expectedModel,
                    actualType: get_debug_type($scope),
                );
            }
        }

        // Derive cache key parts.
        $scopeType = $scope?->getMorphClass();
        $scopeId = $scope?->getKey();

        $cacheKey = FeatureFlagManager::buildCacheKey($featureSlug, $scopeType, $scopeId);

        // 1. Check in-memory cache.
        $memoryCached = $manager->getFromMemory($cacheKey);
        if ($memoryCached !== null) {
            return $memoryCached;
        }

        // 2. Check the result store (database), unless bypassing storage.
        if (! $bypassStorage) {
            $storeCached = $manager->getStore()->get($featureSlug, $scopeType, $scopeId);
            if ($storeCached !== null) {
                $manager->storeInMemory($cacheKey, $storeCached);

                return $storeCached;
            }
        }

        // 3. Resolve the feature flag.
        if ($usesModelScopes && $scope instanceof Model) {
            $result = static::evaluateModelScope($feature, $scope);
        } else {
            $result = static::evaluateResolveMethod($feature, $scope);
        }

        // 4. Store result.
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

    protected static function evaluateModelScope(FeatureFlag $feature, Model $scope): bool
    {
        $modelScope = FeatureFlagModelScope::query()
            ->where('feature_slug', $feature->getSlug())
            ->where('scope_type', $scope->getMorphClass())
            ->first();

        if ($modelScope === null) {
            if (! method_exists($feature, 'resolve')) {
                return false;
            }

            return static::evaluateResolveMethod($feature, $scope);
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
