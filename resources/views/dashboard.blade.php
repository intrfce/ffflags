@extends('ffflags::layouts.app')

@section('title', 'FFFlags Dashboard')

@php
$props = [
    'features' => $features->map(fn ($f) => [
        'name' => $f->name,
        'slug' => $f->slug,
        'description' => $f->description,
        'is_managed' => $f->isManaged,
        'model_scope_label' => $f->isManaged ? $f->getModelScopeLabel() : null,
    ])->values(),
    'baseUrl' => url(config('ffflags.path', 'ffflags')),
];
@endphp

@section('content')
    <div id="dashboard-app" data-props="{{ json_encode($props, JSON_HEX_APOS | JSON_HEX_QUOT) }}"></div>
@endsection
