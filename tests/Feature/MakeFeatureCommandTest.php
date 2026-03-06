<?php

use Illuminate\Support\Facades\File;

it('creates a feature flag class', function () {
    $path = app_path('Features/TestFeature.php');

    // Clean up if exists from a previous run.
    if (File::exists($path)) {
        File::delete($path);
    }

    $this->artisan('make:feature', ['name' => 'TestFeature'])
        ->assertExitCode(0);

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)->toContain('namespace App\\Features;');
    expect($contents)->toContain('class TestFeature extends FeatureFlag');
    expect($contents)->toContain('use Intrfce\\FFFlags\\FeatureFlag;');
    expect($contents)->toContain('use Intrfce\\FFFlags\\Attributes\\Slug;');
    expect($contents)->toContain("#[Slug('test-feature')]");
    expect($contents)->toContain('public function resolve(): bool');

    // Clean up.
    File::delete($path);
});

it('creates a managed feature flag class', function () {
    $path = app_path('Features/ManagedTestFeature.php');

    if (File::exists($path)) {
        File::delete($path);
    }

    $this->artisan('make:feature', [
        'name' => 'ManagedTestFeature',
        '--managed' => true,
    ])->assertExitCode(0);

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)->toContain('namespace App\\Features;');
    expect($contents)->toContain('class ManagedTestFeature extends ManagedFeatureFlag');
    expect($contents)->toContain('use Intrfce\\FFFlags\\ManagedFeatureFlag;');
    expect($contents)->toContain('use Intrfce\\FFFlags\\Attributes\\Model;');
    expect($contents)->toContain('use Intrfce\\FFFlags\\Attributes\\Slug;');
    expect($contents)->toContain("#[Slug('managed-test-feature')]");
    expect($contents)->toContain('#[Model(');

    File::delete($path);
});

it('creates a managed feature flag class with model option', function () {
    $path = app_path('Features/ManagedWithModelFeature.php');

    if (File::exists($path)) {
        File::delete($path);
    }

    $this->artisan('make:feature', [
        'name' => 'ManagedWithModelFeature',
        '--managed' => true,
        '--model' => 'App\\Models\\User',
    ])->assertExitCode(0);

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)->toContain('class ManagedWithModelFeature extends ManagedFeatureFlag');
    expect($contents)->toContain('#[Model(User::class)]');
    expect($contents)->toContain('use \\App\\Models\\User;');

    File::delete($path);
});
