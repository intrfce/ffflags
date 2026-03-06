<?php

namespace Intrfce\FFFlags\Actions;

use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;

class DeactivateFeature
{
    public function handle(ManagedFeatureFlag $feature, array $ids = [], bool $all = false): void
    {
        $featureSlug = $feature->getSlug();
        $modelClass = $feature->getModelClass();
        $scopeType = (new $modelClass)->getMorphClass();

        $modelScope = FeatureFlagModelScope::query()
            ->where('feature_slug', $featureSlug)
            ->where('scope_type', $scopeType)
            ->first();

        if ($modelScope === null) {
            return;
        }

        if ($all) {
            $modelScope->update([
                'value' => [],
            ]);

            return;
        }

        $existing = $modelScope->value ?? [];
        $remaining = array_values(array_diff($existing, $ids));

        $modelScope->update([
            'value' => $remaining,
        ]);
    }
}
