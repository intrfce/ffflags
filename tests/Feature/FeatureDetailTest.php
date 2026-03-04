<?php

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Intrfce\FFFlags\Enums\ScopeCondition;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\Models\FeatureFlagModelScope;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\LabelledUserScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ModelScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\LabelledUser;
use Intrfce\FFFlags\Tests\Fixtures\User;

beforeEach(function () {
    Gate::define('view-ffflags-dashboard', fn ($user) => true);

    if (! Schema::hasTable('users')) {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name')->default('');
            $table->string('email')->default('');
            $table->timestamps();
        });
    }
});

function actingAsAdmin(): AuthUser
{
    $user = new AuthUser();
    $user->id = 1;
    $user->email = 'admin@example.com';

    return $user;
}

it('shows feature detail page', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class],
    ));

    $this->actingAs(actingAsAdmin())
        ->get('/ffflags/features/always-active')
        ->assertStatus(200)
        ->assertSee('Always Active');
});

it('returns 404 for unknown slug', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [],
    ));

    $this->actingAs(actingAsAdmin())
        ->get('/ffflags/features/nonexistent')
        ->assertStatus(404);
});

it('shows code-based resolution message for features without ScopeWithModelRules', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [AlwaysActiveFeature::class],
    ));

    $this->actingAs(actingAsAdmin())
        ->get('/ffflags/features/always-active')
        ->assertStatus(200)
        ->assertSee('code-based resolution');
});

it('shows model scope form for features with ScopeWithModelRules', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [ModelScopedFeature::class],
    ));

    User::create(['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com']);

    $this->actingAs(actingAsAdmin())
        ->get('/ffflags/features/model-scoped')
        ->assertStatus(200)
        ->assertSee('Model Scope Rule')
        ->assertSee('Equals');
});

it('saves condition and value via POST', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [ModelScopedFeature::class],
    ));

    $this->actingAs(actingAsAdmin())
        ->post('/ffflags/features/model-scoped', [
            'condition' => 'equals',
            'value' => [1],
        ])
        ->assertRedirect('/ffflags/features/model-scoped');

    $this->assertDatabaseHas('ffflags_model_scopes', [
        'feature_slug' => 'model-scoped',
        'condition' => 'equals',
    ]);
});

it('updates existing rule on re-submit', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [ModelScopedFeature::class],
    ));

    $scopeType = (new User())->getMorphClass();

    FeatureFlagModelScope::create([
        'feature_slug' => 'model-scoped',
        'scope_type' => $scopeType,
        'condition' => ScopeCondition::Equals->value,
        'value' => [1],
    ]);

    $this->actingAs(actingAsAdmin())
        ->post('/ffflags/features/model-scoped', [
            'condition' => 'is_one_of',
            'value' => [1, 2, 3],
        ])
        ->assertRedirect('/ffflags/features/model-scoped');

    $this->assertDatabaseCount('ffflags_model_scopes', 1);
    $this->assertDatabaseHas('ffflags_model_scopes', [
        'feature_slug' => 'model-scoped',
        'condition' => 'is_one_of',
    ]);
});

it('validates condition is a valid enum value', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [ModelScopedFeature::class],
    ));

    $this->actingAs(actingAsAdmin())
        ->post('/ffflags/features/model-scoped', [
            'condition' => 'invalid',
            'value' => [1],
        ])
        ->assertSessionHasErrors('condition');
});

it('shows models with getFeatureSelectorLabel label', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [LabelledUserScopedFeature::class],
    ));

    LabelledUser::create(['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com']);

    $this->actingAs(actingAsAdmin())
        ->get('/ffflags/features/labelled-user-scoped')
        ->assertStatus(200)
        ->assertSee('Alice Johnson');
});

it('falls back to model key when getFeatureSelectorLabel not implemented', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, fn () => new FeatureFlagDiscovery(
        directories: [],
        classes: [ModelScopedFeature::class],
    ));

    User::create(['id' => 42, 'name' => 'Bob', 'email' => 'bob@example.com']);

    $response = $this->actingAs(actingAsAdmin())
        ->get('/ffflags/features/model-scoped');

    $response->assertStatus(200);
    // The option value should show the key (42) as the label
    $response->assertSee('value="42"', false);
});
