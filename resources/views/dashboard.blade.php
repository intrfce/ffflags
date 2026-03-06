<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FFFlags Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-base-200 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6">FFFlags Dashboard</h1>

        @if($features->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <p class="text-base-content/60">
                        No feature flags discovered. Create one with
                        <code class="kbd kbd-sm">php artisan make:feature</code>
                    </p>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($features as $feature)
                                <tr class="hover">
                                    <td>
                                        <a href="{{ route('ffflags.feature.show', $feature->slug) }}" class="link link-primary">
                                            {{ $feature->name }}
                                        </a>
                                        @if($feature->isManaged)
                                            <div class="text-xs text-base-content/50 mt-0.5">Scoped to {{ $feature->getModelScopeLabel() }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="kbd kbd-sm">{{ $feature->slug }}</code>
                                    </td>
                                    <td class="text-base-content/60">
                                        {{ $feature->description ?: '-' }}
                                    </td>
                                    <td>
                                        @if($feature->isManaged)
                                            <a href="{{ route('ffflags.feature.show', $feature->slug) }}" class="btn btn-primary btn-sm">Configure</a>
                                        @else
                                            <span class="badge badge-neutral">Managed by code</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
