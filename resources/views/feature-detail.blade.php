<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $feature->name }} - FFFlags</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-base-200 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <a href="{{ route('ffflags.dashboard') }}" class="link link-primary text-sm mb-4 inline-block">&larr; Back to dashboard</a>

        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h1 class="card-title text-2xl">{{ $feature->name }}</h1>
                <p class="text-sm text-base-content/50"><code class="kbd kbd-sm">{{ $feature->slug }}</code></p>
                @if($feature->description)
                    <p class="text-base-content/70 mt-1">{{ $feature->description }}</p>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($feature->hasModelRules)
            <div class="card bg-base-100 shadow" x-data="{
                condition: '{{ $currentRule?->condition?->value ?? '' }}',
                value: @js($currentRule?->value ?? []),
                isMulti() {
                    return this.condition === 'is_one_of' || this.condition === 'is_none_of';
                }
            }">
                <div class="card-body">
                    <h2 class="card-title text-lg">Model Scope Rule <span class="text-base-content/50 font-normal">({{ $modelName }})</span></h2>

                    <form method="POST" action="{{ route('ffflags.feature.update', $feature->slug) }}">
                        @csrf

                        <div class="space-y-4 mt-2">
                            <div class="form-control w-full">
                                <label class="label" for="condition">
                                    <span class="label-text">Condition</span>
                                </label>
                                <select
                                    name="condition"
                                    id="condition"
                                    x-model="condition"
                                    @change="if (!isMulti() && value.length > 1) { value = [value[0]]; }"
                                    class="select select-bordered w-full"
                                >
                                    <option value="">Select a condition...</option>
                                    @foreach($conditions as $condition)
                                        <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                                    @endforeach
                                </select>
                                @isset($errors)
                                    @error('condition')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                @endisset
                            </div>

                            <div x-show="condition !== ''" class="form-control w-full">
                                <label class="label">
                                    <span class="label-text" x-text="isMulti() ? 'Select models' : 'Select a model'"></span>
                                </label>

                                {{-- Single select --}}
                                <select
                                    x-show="!isMulti()"
                                    :disabled="isMulti()"
                                    name="value[]"
                                    class="select select-bordered w-full"
                                    x-model="value[0]"
                                >
                                    <option value="">Select...</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model['key'] }}">{{ $model['label'] }}</option>
                                    @endforeach
                                </select>

                                {{-- Multi select --}}
                                <select
                                    x-show="isMulti()"
                                    :disabled="!isMulti()"
                                    name="value[]"
                                    multiple
                                    x-model="value"
                                    class="select select-bordered w-full h-auto"
                                    size="{{ min(count($models), 8) }}"
                                >
                                    @foreach($models as $model)
                                        <option value="{{ $model['key'] }}">{{ $model['label'] }}</option>
                                    @endforeach
                                </select>
                                @isset($errors)
                                    @error('value')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                @endisset
                            </div>

                            <div>
                                <button type="submit" class="btn btn-primary">
                                    Save Rule
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow mt-6">
                <div class="card-body">
                    <h2 class="card-title text-lg">Test Rule</h2>

                    <form method="POST" action="{{ route('ffflags.feature.check', $feature->slug) }}" class="flex items-end gap-3 mt-2">
                        @csrf
                        <div class="form-control flex-1">
                            <label class="label" for="scope_id">
                                <span class="label-text">{{ $modelName }} ID</span>
                            </label>
                            <input
                                type="text"
                                name="scope_id"
                                id="scope_id"
                                value="{{ request('check_id', '') }}"
                                class="input input-bordered w-full"
                                placeholder="Enter an ID to test..."
                            >
                        </div>
                        <button type="submit" class="btn btn-neutral">
                            Check
                        </button>
                    </form>

                    @if(request()->has('check_id'))
                        <div class="alert {{ request('check_pass') ? 'alert-success' : 'alert-error' }} mt-4">
                            <span>
                                @if(request('check_message'))
                                    {{ request('check_message') }}
                                @else
                                    {{ $modelName }} ID <strong>{{ request('check_id') }}</strong>: {{ request('check_pass') ? 'Passes' : 'Does not pass' }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <p class="text-base-content/60">This feature uses code-based resolution via the <code class="kbd kbd-sm">resolve()</code> method.</p>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
