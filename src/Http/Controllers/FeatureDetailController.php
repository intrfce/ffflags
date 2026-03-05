<?php

namespace Intrfce\FFFlags\Http\Controllers;

use Illuminate\Http\Request;
use Intrfce\FFFlags\Contracts\HasFeatureSelectorLabel;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;

class FeatureDetailController
{
    public function show(string $slug, FeatureFlagDiscovery $discovery)
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);

        $models = collect();
        $currentRule = null;

        if ($feature->hasModelRules) {
            $modelClass = $feature->modelClass;

            $models = $modelClass::all()->map(function ($model) {
                return [
                    'key' => $model->getKey(),
                    'label' => $model instanceof HasFeatureSelectorLabel
                        ? $model->getFeatureSelectorLabel()
                        : $model->getKey(),
                ];
            });

            $currentRule = FeatureFlagModelScope::query()
                ->where('feature_slug', $slug)
                ->where('scope_type', (new $modelClass)->getMorphClass())
                ->first();
        }

        return view('ffflags::feature-detail', [
            'feature' => $feature,
            'models' => $models,
            'modelName' => $feature->getModelScopeLabel(),
            'currentRule' => $currentRule,
            'conditions' => ScopeCondition::cases(),
        ]);
    }

    public function update(Request $request, string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager)
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);
        abort_if(! $feature->hasModelRules, 403);

        $validated = $request->validate([
            'condition' => ['required', 'string', 'in:' . implode(',', array_column(ScopeCondition::cases(), 'value'))],
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

        return redirect()->route('ffflags.feature.show', $slug)
            ->with('success', 'Rule saved successfully.');
    }

    public function check(Request $request, string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager)
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);
        abort_if(! $feature->hasModelRules, 403);

        $validated = $request->validate([
            'scope_id' => ['required'],
        ]);

        $modelClass = $feature->modelClass;
        $model = $modelClass::find($validated['scope_id']);

        if ($model === null) {
            return redirect()->route('ffflags.feature.show', [
                'slug' => $slug,
                'check_id' => $validated['scope_id'],
                'check_pass' => 0,
                'check_message' => 'Not found',
            ]);
        }

        $result = $manager->for($model)->isActive($feature->class);

        return redirect()->route('ffflags.feature.show', [
            'slug' => $slug,
            'check_id' => $validated['scope_id'],
            'check_pass' => $result ? 1 : 0,
        ]);
    }
}
