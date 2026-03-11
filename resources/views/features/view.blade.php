@extends('ffflags::layouts.app')

@section('title', $feature->name . ' - FFFlags')

@php
$props = [
    'feature' => [
        'name' => $feature->name,
        'slug' => $feature->slug,
        'description' => $feature->description,
        'is_managed' => $feature->isManaged,
    ],
    'models' => $models,
    'modelName' => $modelName,
    'currentRule' => $currentRule ? [
        'condition' => $currentRule->condition->value,
        'value' => $currentRule->value,
    ] : null,
    'conditions' => collect($conditions)->map(fn ($c) => [
        'value' => $c->value,
        'label' => $c->label(),
    ])->values(),
    'baseUrl' => url(config('ffflags.path', 'ffflags')),
    'csrfToken' => csrf_token(),
    'success' => session('success', ''),
    'checkResult' => request()->has('check_id') ? [
        'id' => request('check_id'),
        'pass' => (bool) request('check_pass'),
        'message' => request('check_message'),
    ] : null,
];
@endphp

@section('content')
    <div id="feature-app" data-props="{{ json_encode($props, JSON_HEX_APOS | JSON_HEX_QUOT) }}"></div>
@endsection
