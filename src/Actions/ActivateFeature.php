<?php

namespace Intrfce\FFFlags\Actions;

use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;

class ActivateFeature
{
    public function handle(ManagedFeatureFlag $feature, array $ids, bool $replace = false): void
    {
        $featureSlug = $feature->getSlug();
        $modelClass = $feature->getModelClass();
        $scopeType = (new $modelClass)->getMorphClass();

        $modelScope = FeatureFlagModelScope::query()
            ->where('feature_slug', $featureSlug)
            ->where('scope_type', $scopeType)
            ->first();

        if ($modelScope === null) {
            FeatureFlagModelScope::create([
                'feature_slug' => $featureSlug,
                'scope_type' => $scopeType,
                'condition' => ScopeCondition::IsOneOf->value,
                'value' => array_values(array_unique($ids)),
            ]);

            return;
        }

        if ($replace) {
            $modelScope->update([
                'condition' => ScopeCondition::IsOneOf->value,
                'value' => array_values(array_unique($ids)),
            ]);

            return;
        }

        $existing = $modelScope->value ?? [];
        $merged = array_values(array_unique(array_merge($existing, $ids)));

        $modelScope->update([
            'condition' => ScopeCondition::IsOneOf->value,
            'value' => $merged,
        ]);
    }
}
