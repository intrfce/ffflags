<?php

namespace Intrfce\FFFlags\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intrfce\FFFlags\Contracts\HasFeatureSelectorLabel;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;

class FeatureDetailController
{
    public function show(string $slug, FeatureFlagDiscovery $discovery): JsonResponse
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);

        $models = collect();
        $currentRule = null;

        if ($feature->isManaged) {
            $modelClass = $feature->modelClass;

            $models = $modelClass::all()->map(function ($model) {
                return [
                    'key' => $model->getKey(),
                    'label' => $model instanceof HasFeatureSelectorLabel
                        ? $model->getFeatureSelectorLabel()
                        : (string) $model->getKey(),
                ];
            });

            $rule = FeatureFlagModelScope::query()
                ->where('feature_slug', $slug)
                ->where('scope_type', (new $modelClass)->getMorphClass())
                ->first();

            if ($rule) {
                $currentRule = [
                    'condition' => $rule->condition,
                    'value' => $rule->value,
                ];
            }
        }

        $conditions = collect(ScopeCondition::cases())->map(fn (ScopeCondition $c) => [
            'value' => $c->value,
            'label' => $c->label(),
            'is_multi_select' => $c->isMultiSelect(),
        ]);

        return response()->json([
            'data' => [
                'class' => $feature->class,
                'name' => $feature->name,
                'slug' => $feature->slug,
                'description' => $feature->description,
                'bypasses_storage' => $feature->bypassesStorage,
                'is_managed' => $feature->isManaged,
                'model_class' => $feature->modelClass,
                'model_scope_label' => $feature->getModelScopeLabel(),
                'models' => $models,
                'current_rule' => $currentRule,
                'conditions' => $conditions,
            ],
        ]);
    }

    public function update(Request $request, string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager): JsonResponse
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);
        abort_if(! $feature->isManaged, 403);

        $validated = $request->validate([
            'condition' => ['required', 'string', 'in:'.implode(',', array_column(ScopeCondition::cases(), 'value'))],
            'value' => ['required', 'array', 'min:1'],
            'value.*' => ['required'],
        ]);

        $modelClass = $feature->modelClass;
        $scopeType = (new $modelClass)->getMorphClass();

        FeatureFlagModelScope::updateOrCreate(
            [
                'feature_slug' => $slug,
                'scope_type' => $scopeType,
            ],
            [
                'condition' => $validated['condition'],
                'value' => $validated['value'],
            ],
        );

        $manager->purgeAll();

        return response()->json(['success' => true]);
    }

    public function check(Request $request, string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager): JsonResponse
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);
        abort_if(! $feature->isManaged, 403);

        $validated = $request->validate([
            'scope_id' => ['required'],
        ]);

        $modelClass = $feature->modelClass;
        $model = $modelClass::find($validated['scope_id']);

        if ($model === null) {
            return response()->json([
                'scope_id' => $validated['scope_id'],
                'pass' => false,
                'message' => 'Model not found.',
            ]);
        }

        $result = $manager->for($model)->isActive($feature->class);

        return response()->json([
            'scope_id' => $validated['scope_id'],
            'pass' => $result,
        ]);
    }
}
