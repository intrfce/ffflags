<?php

namespace Intrfce\FFFlags\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intrfce\FFFlags\FeatureFlagDiscovery;

class FeatureListController
{
    public function __invoke(Request $request, FeatureFlagDiscovery $discovery): JsonResponse
    {
        return response()->json([
            'data' => $discovery->discover()->map(fn ($f) => [
                'class' => $f->class,
                'name' => $f->name,
                'slug' => $f->slug,
                'description' => $f->description,
                'bypasses_storage' => $f->bypassesStorage,
                'is_managed' => $f->isManaged,
                'model_class' => $f->modelClass,
                'model_scope_label' => $f->getModelScopeLabel(),
            ])->values(),
        ]);
    }
}
