<?php

namespace Intrfce\FFFlags\Http\Controllers;

use Illuminate\Http\Request;
use Intrfce\FFFlags\Contracts\HasFeatureSelectorLabel;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\Models\FeatureFlagEvaluation;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;

class FeatureDetailController
{
    public function show(string $slug, FeatureFlagDiscovery $discovery)
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);

        $models = collect();
        $currentRule = null;

        if ($feature->isManaged) {
            $modelClass = $feature->modelClass;
            $titleColumn = $feature->modelTitleColumn;

            $models = $modelClass::all()->map(function ($model) use ($titleColumn) {
                if ($titleColumn) {
                    $label = $model->{$titleColumn};
                } elseif ($model instanceof HasFeatureSelectorLabel) {
                    $label = $model->getFeatureSelectorLabel();
                } else {
                    $label = $model->getKey();
                }

                return [
                    'key' => $model->getKey(),
                    'label' => $label,
                ];
            });

            $currentRule = FeatureFlagModelScope::query()
                ->where('feature_slug', $slug)
                ->where('scope_type', (new $modelClass)->getMorphClass())
                ->first();
        }

        $logEvaluations = config('ffflags.log_evaluations', false);

        return view('ffflags::features.view', [
            'feature' => $feature,
            'models' => $models,
            'modelName' => $feature->getModelScopeLabel(),
            'currentRule' => $currentRule,
            'conditions' => ScopeCondition::cases(),
            'logEvaluations' => $logEvaluations,
        ]);
    }

    public function update(Request $request, string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager)
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);
        abort_if(! $feature->isManaged, 403);

        $validated = $request->validate([
            'match_mode' => ['required', 'string', 'in:all,any'],
            'condition' => ['required', 'string', 'in:' . implode(',', array_column(ScopeCondition::cases(), 'value'))],
            'value' => ['nullable', 'array'],
            'value.*' => ['required'],
        ]);

        $validated['value'] = $validated['value'] ?? [];

        $modelClass = $feature->modelClass;
        $scopeType = (new $modelClass)->getMorphClass();

        FeatureFlagModelScope::updateOrCreate(
            [
                'feature_slug' => $slug,
                'scope_type' => $scopeType,
            ],
            [
                'match_mode' => $validated['match_mode'],
                'condition' => $validated['condition'],
                'value' => $validated['value'],
            ],
        );

        $manager->purgeAll();

        return redirect()->route('ffflags.feature.show', $slug)
            ->with('success', 'Rule saved successfully.');
    }

    public function destroy(string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager)
    {
        $feature = $discovery->discover()->firstWhere('slug', $slug);

        abort_if($feature === null, 404);
        abort_if(! $feature->isManaged, 403);

        $modelClass = $feature->modelClass;
        $scopeType = (new $modelClass)->getMorphClass();

        FeatureFlagModelScope::query()
            ->where('feature_slug', $slug)
            ->where('scope_type', $scopeType)
            ->delete();

        $manager->purgeAll();

        return redirect()->route('ffflags.feature.show', $slug)
            ->with('success', 'All conditions cleared.');
    }

    public function evaluations(Request $request, string $slug)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;

        $evaluations = FeatureFlagEvaluation::query()
            ->where('feature_slug', $slug)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $evaluations->map(fn ($e) => [
                'id' => $e->id,
                'scope_type' => $e->scope_type,
                'scope_id' => $e->scope_id,
                'result' => $e->result,
                'call_file' => $e->call_file,
                'call_line' => $e->call_line,
                'conditions_snapshot' => $e->conditions_snapshot,
                'created_at' => $e->created_at->toIso8601String(),
            ]),
            'current_page' => $evaluations->currentPage(),
            'last_page' => $evaluations->lastPage(),
            'total' => $evaluations->total(),
        ]);
    }

    public function check(Request $request, string $slug, FeatureFlagDiscovery $discovery, FeatureFlagManager $manager)
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
