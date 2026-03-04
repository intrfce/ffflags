<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $feature->name }} - FFFlags</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <a href="{{ route('ffflags.dashboard') }}" class="text-indigo-600 hover:text-indigo-900 text-sm mb-4 inline-block">&larr; Back to dashboard</a>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $feature->name }}</h1>
            <p class="text-sm text-gray-500 mt-1"><code>{{ $feature->slug }}</code></p>
            @if($feature->description)
                <p class="text-gray-600 mt-2">{{ $feature->description }}</p>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($feature->hasModelRules)
            <div class="bg-white rounded-lg shadow p-6" x-data="{
                condition: '{{ $currentRule?->condition?->value ?? '' }}',
                value: @js($currentRule?->value ?? []),
                isMulti() {
                    return this.condition === 'is_one_of' || this.condition === 'is_none_of';
                }
            }">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Model Scope Rule <span class="text-gray-500 font-normal">({{ $modelName }})</span></h2>

                <form method="POST" action="{{ route('ffflags.feature.update', $feature->slug) }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="condition" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                            <select
                                name="condition"
                                id="condition"
                                x-model="condition"
                                @change="if (!isMulti() && value.length > 1) { value = [value[0]]; }"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                            >
                                <option value="">Select a condition...</option>
                                @foreach($conditions as $condition)
                                    <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                                @endforeach
                            </select>
                            @isset($errors)
                                @error('condition')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            @endisset
                        </div>

                        <div x-show="condition !== ''">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span x-text="isMulti() ? 'Select models' : 'Select a model'"></span>
                            </label>

                            {{-- Single select --}}
                            <select
                                x-show="!isMulti()"
                                :disabled="isMulti()"
                                name="value[]"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
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
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                                size="{{ min(count($models), 8) }}"
                            >
                                @foreach($models as $model)
                                    <option value="{{ $model['key'] }}">{{ $model['label'] }}</option>
                                @endforeach
                            </select>
                            @isset($errors)
                                @error('value')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            @endisset
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Save Rule
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Rule</h2>

                <form method="POST" action="{{ route('ffflags.feature.check', $feature->slug) }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label for="scope_id" class="block text-sm font-medium text-gray-700 mb-1">{{ $modelName }} ID</label>
                        <input
                            type="text"
                            name="scope_id"
                            id="scope_id"
                            value="{{ request('check_id', '') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                            placeholder="Enter an ID to test..."
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Check
                    </button>
                </form>

                @if(request()->has('check_id'))
                    <div class="mt-4 p-3 rounded-md {{ request('check_pass') ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' }}">
                        @if(request('check_message'))
                            {{ request('check_message') }}
                        @else
                            {{ $modelName }} ID <strong>{{ request('check_id') }}</strong>: {{ request('check_pass') ? 'Passes' : 'Does not pass' }}
                        @endif
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">This feature uses code-based resolution via the <code class="bg-gray-100 px-2 py-1 rounded text-sm">resolve()</code> method.</p>
            </div>
        @endif
    </div>
</body>
</html>
