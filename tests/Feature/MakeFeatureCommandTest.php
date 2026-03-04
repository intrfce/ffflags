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
    expect($contents)->toContain('use Intrfce\\FFFlags\\Attributes\\Name;');
    expect($contents)->toContain("#[Name('TestFeature', 'test-feature')]");
    expect($contents)->toContain('public function resolve(): bool');

    // Clean up.
    File::delete($path);
});
