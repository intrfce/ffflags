# FFFlags

A feature flag package for Laravel.

## Installation

```bash
composer require intrfce/ffflags
```

## Defining Feature Flags

Feature flags are defined as classes that extend `FeatureFlag`. You can generate one using the artisan command:

```bash
php artisan make:feature MyFeature
```

This creates a class in `app/Features/MyFeature.php`.

### Name and Description

You can set a name and description using class properties:

```php
use Intrfce\FFFlags\FeatureFlag;

class MyFeature extends FeatureFlag
{
    protected string $name = 'My Feature';

    protected string $description = 'Controls the display of a certain feature';
}
```

Or using attributes:

```php
use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\FeatureFlag;

#[Name('My Feature')]
#[Description('Controls the display of a certain feature')]
class MyFeature extends FeatureFlag
{
    //
}
```

Properties take priority over attributes. If neither is set, the name falls back to the class name and the description defaults to an empty string.

## Resolving Feature Flags

To determine if a feature flag is active, implement a `resolve` method on your feature class. The `resolve` method must return a boolean.

The method signature is flexible — you can type-hint any scope you need:

```php
#[Name('My Feature')]
#[Description('Controls the display of a certain feature')]
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

// Throws ScopeTypeMismatchException — expects User, got Team.
MyFeature::for($team)->isActive();
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
