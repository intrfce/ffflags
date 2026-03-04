<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Contracts\ResultStore;

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

    public static function buildCacheKey(string $featureClass, ?string $scopeType, string|int|null $scopeId): string
    {
        return $featureClass.':'.($scopeType ?? '').':'.($scopeId ?? '');
    }
}
