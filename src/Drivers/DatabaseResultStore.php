<?php

namespace Intrfce\FFFlags\Drivers;

use Intrfce\FFFlags\Contracts\ResultStore;
use Intrfce\FFFlags\Models\FeatureFlagResult;

class DatabaseResultStore implements ResultStore
{
    public function get(string $featureClass, ?string $scopeType, string|int|null $scopeId): ?bool
    {
        $record = FeatureFlagResult::query()
            ->where('feature_class', $featureClass)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        return $record?->result;
    }

    public function store(string $featureClass, ?string $scopeType, string|int|null $scopeId, bool $result): void
    {
        FeatureFlagResult::query()->updateOrCreate(
            [
                'feature_class' => $featureClass,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
            ],
            [
                'result' => $result,
            ],
        );
    }

    public function delete(string $featureClass, ?string $scopeType, string|int|null $scopeId): void
    {
        FeatureFlagResult::query()
            ->where('feature_class', $featureClass)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->delete();
    }

    public function purge(): void
    {
        FeatureFlagResult::query()->truncate();
    }
}
