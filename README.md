# FFFlags

A feature flag package for Laravel.

## Acknowledgements

This package was heavily inspired by [Laravel Pennant](https://github.com/laravel/pennant). Thank you to the Laravel team for building such a well-designed feature flag system that served as the foundation and inspiration for this package.

## Installation

```bash
composer require intrfce/ffflags
```

Publish the config file and run the migrations:

```bash
php artisan vendor:publish --tag="ffflags-config"
php artisan vendor:publish --provider="Intrfce\FFFlags\FFFlagsServiceProvider"
php artisan migrate
```

## Defining Feature Flags

Feature flags are defined as classes that extend `FeatureFlag`. You can generate one using the artisan command:

```bash
php artisan make:feature MyFeature
```

This creates a class in `app/Features/MyFeature.php`.

### Slug (Required)

Every feature flag **must** have a `#[Slug]` attribute. The slug is what gets stored in the database:

```php
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('my-feature')]
class MyFeature extends FeatureFlag
{
    //
}
```

If no `#[Slug]` attribute is set, a `MissingFeatureFlagSlugException` will be thrown.

When generating a feature with `php artisan make:feature MyFeature`, the slug is automatically kebab-cased from the class name (e.g. `MyFeature` becomes `my-feature`).

### Name (Optional)

You can optionally add a `#[Name]` attribute for a human-readable display name. If not provided, the slug is used as the display name:

```php
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('my-feature')]
#[Name('My Feature')]
class MyFeature extends FeatureFlag
{
    //
}
```

### Description (Optional)

Descriptions can be set using the `#[Description]` attribute:

```php
use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('my-feature')]
#[Description('Controls the display of a certain feature')]
class MyFeature extends FeatureFlag
{
    //
}
```

If no description is set, it defaults to an empty string.

## Resolving Feature Flags

To determine if a feature flag is active, implement a `resolve` method on your feature class. The `resolve` method must return a boolean.

### Scopeless Features

Features that don't depend on a user or model can define `resolve` with no parameters:

```php
class GlobalFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return config('features.global_enabled');
    }
}
```

### Scoped Features

Type-hint a scope parameter to make the feature depend on a specific model:

```php
class MyFeature extends FeatureFlag
{
    public function resolve(User $user): bool
    {
        return in_array($user->email, ['me@mymail.com']);
    }
}
```

If no `resolve` method is defined, the feature defaults to inactive.

## Checking Feature Flags

### Direct Class Usage

Call `for()` on the feature class directly, passing your scope, then call `isActive()`:

```php
MyFeature::for($user)->isActive(); // true or false
```

### Facade Usage

Use the `FeatureFlag` facade to check one or more features:

```php
use Intrfce\FFFlags\Facades\FeatureFlag;

// Check a single feature.
FeatureFlag::for($user)->isActive(MyFeature::class);

// Check if any of the given features are active.
FeatureFlag::for($user)->anyActive([
    MyFeature::class,
    MySecondFeature::class,
]);

// Check if all of the given features are active.
FeatureFlag::for($user)->allActive([
    MyFeature::class,
    MySecondFeature::class,
]);
```

### Scopeless Facade Usage

For features that don't require a scope, you can call `isActive()` directly on the facade without `for()`:

```php
FeatureFlag::isActive(GlobalFeature::class);
```

If you call `isActive()` on a feature that requires a scope, a `ScopeRequiredException` will be thrown.

## Middleware

Use the `FeatureFlagMiddleware` class to protect routes behind feature flags. The middleware provides three static methods that return middleware strings compatible with Laravel's routing:

```php
use Intrfce\FFFlags\Http\Middleware\FeatureFlagMiddleware;

// Require a single feature to be active.
Route::get('/dashboard', DashboardController::class)
    ->middleware(FeatureFlagMiddleware::isActive(MyFeature::class));

// Require all features to be active.
Route::get('/new-dashboard', NewDashboardController::class)
    ->middleware(FeatureFlagMiddleware::allActive([
        MyFeature::class,
        AnotherFeature::class,
    ]));

// Require at least one feature to be active.
Route::get('/beta', BetaController::class)
    ->middleware(FeatureFlagMiddleware::anyActive([
        BetaAccessFeature::class,
        StaffFeature::class,
    ]));
```

By default, the middleware throws a 403 HTTP exception when features are inactive.

### Scoped Features in Middleware

If a feature requires a scope, it must implement the `ResolvingFromMiddleware` interface to tell the middleware how to derive the scope from the request:

```php
use Intrfce\FFFlags\Contracts\ResolvingFromMiddleware;
use Intrfce\FFFlags\FeatureFlag;
use Illuminate\Http\Request;

class MyFeature extends FeatureFlag implements ResolvingFromMiddleware
{
    public function resolveMiddlewareScope(Request $request): mixed
    {
        return $request->user();
    }

    public function resolve(User $user): bool
    {
        return $user->is_beta_tester;
    }
}
```

### Custom Inactive Responses

You can customise the response when feature flags deny access using `whenInactive`. This is typically called in a service provider's `boot` method:

```php
use Intrfce\FFFlags\Http\Middleware\FeatureFlagMiddleware;

FeatureFlagMiddleware::whenInactive(
    function (Request $request, array $features) {
        return response('Feature not available', 403);
    }
);
```

The callback receives the current request and an array of the feature class names that were inactive. Pass `null` to reset back to the default 403 exception behaviour.

## Bypassing Storage

By default, feature flag results are cached in-memory and persisted to the database. If you want a feature to skip database storage while still using the in-memory cache within a single request, add the `#[BypassStorage]` attribute:

```php
use Intrfce\FFFlags\Attributes\BypassStorage;
use Intrfce\FFFlags\FeatureFlag;

#[BypassStorage]
class VolatileFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return config('features.volatile');
    }
}
```

This is useful for features that rely on configuration or external state that may change between requests and shouldn't be cached in the database.

## Scope Type Safety

If the type of the object passed to `for()` does not match the type-hint on the `resolve` method, a `ScopeTypeMismatchException` is thrown:

```php
class MyFeature extends FeatureFlag
{
    public function resolve(User $user): bool
    {
        return true;
    }
}

// Throws ScopeTypeMismatchException - expects User, got Team.
MyFeature::for($team)->isActive();
```

## Purging Cached Results

To clear all cached feature flag results from the database, use the artisan command:

```bash
php artisan ffflags:purge
```

Or programmatically via the facade:

```php
use Intrfce\FFFlags\Facades\FeatureFlag;

FeatureFlag::purgeAll();
```

This clears both the database and the in-memory cache.

## Dashboard

FFFlags includes a dashboard route at `/ffflags`. Access is controlled by a Laravel gate called `view-ffflags-dashboard`. By default, all access is denied. To grant access, define the gate in one of your service providers:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('view-ffflags-dashboard', function (User $user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

You can customise the dashboard path in the config file:

```php
// config/ffflags.php
return [
    'path' => 'admin/ffflags',
];
```

## Exceptions

FFFlags throws descriptive exceptions to help you catch configuration issues early. All exceptions are in the `Intrfce\FFFlags\Exceptions` namespace.

| Exception | When it's thrown |
|---|---|
| `MissingFeatureFlagSlugException` | A feature flag class has no slug set. Add the `#[Slug]` attribute. |
| `InvalidScopeException` | A non-null scope was passed that is not an Eloquent model. Only `null` or `Model` instances are supported as scopes. |
| `ScopeTypeMismatchException` | The scope passed to `for()` does not match the type-hint on the feature's `resolve()` method (e.g. passed a `Team` when `resolve(User $user)` was expected). |
| `ScopeRequiredException` | `FeatureFlag::isActive()` was called directly on the facade for a feature that requires a scope. Use `FeatureFlag::for($scope)->isActive()` instead. |
| `ScopeDoesNotHaveKeyException` | A model scope was passed that has not been persisted to the database (its primary key is `null`). Save the model before checking feature flags to avoid cache collisions. |
| `ScopeProvidedButResolveHandlerMissingException` | A scope was provided when checking a feature, but the feature class does not have a `resolve()` method. |
| `FeatureFlagNotResolvableFromMiddlewareException` | A scoped feature was used in middleware but does not implement the `ResolvingFromMiddleware` interface. Implement the interface or remove the scope parameter from `resolve()`. |

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
