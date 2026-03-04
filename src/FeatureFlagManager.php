<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Contracts\ResultStore;
use Intrfce\FFFlags\Exceptions\ScopeRequiredException;
use ReflectionMethod;

class FeatureFlagManager
{
    protected array $memoryCache = [];

    public function __construct(
        protected ResultStore $store,
    ) {}

    public function for(mixed $scope): PendingFeatureInteraction
    {
        return new PendingFeatureInteraction($scope, $this);
    }

    /**
     * @param  class-string<FeatureFlag>  $featureClass
     */
    public function isActive(string $featureClass): bool
    {
        $feature = new $featureClass();

        if (method_exists($feature, 'resolve')) {
            $params = (new ReflectionMethod($feature, 'resolve'))->getParameters();

            if (count($params) > 0) {
                throw new ScopeRequiredException(featureClass: $featureClass);
            }
        }

        return (new PendingSingleFeatureInteraction($feature, null, $this))->isActive();
    }

    public function getStore(): ResultStore
    {
        return $this->store;
    }

    public function getFromMemory(string $key): ?bool
    {
        return $this->memoryCache[$key] ?? null;
    }

    public function storeInMemory(string $key, bool $result): void
    {
        $this->memoryCache[$key] = $result;
    }

    public static function buildCacheKey(string $featureSlug, ?string $scopeType, string|int|null $scopeId): string
    {
        return $featureSlug.':'.($scopeType ?? '').':'.($scopeId ?? '');
    }
}
