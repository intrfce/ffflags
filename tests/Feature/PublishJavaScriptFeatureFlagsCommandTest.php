<?php

use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\AttributeNameFeature;

beforeEach(function () {
    $this->outputDir = sys_get_temp_dir().'/ffflags-test-'.uniqid();
    config()->set('ffflags.js_usage.output_directory', $this->outputDir);
    config()->set('ffflags.js_usage.filename', 'flags');
    config()->set('ffflags.js_usage.format', 'js');

    $this->app->singleton(FeatureFlagDiscovery::class, function () {
        return new FeatureFlagDiscovery(
            directories: [],
            classes: [AlwaysActiveFeature::class, AttributeNameFeature::class],
        );
    });
});

afterEach(function () {
    // Clean up any generated files.
    foreach (['flags.js', 'flags.ts', 'custom-flags.js'] as $file) {
        $path = $this->outputDir.'/'.$file;
        if (file_exists($path)) {
            unlink($path);
        }
    }
    if (is_dir($this->outputDir)) {
        rmdir($this->outputDir);
    }
});

it('creates the output directory if it does not exist', function () {
    expect(is_dir($this->outputDir))->toBeFalse();

    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    expect(is_dir($this->outputDir))->toBeTrue();
});

it('publishes a JavaScript file with named exports for each feature flag', function () {
    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    $content = file_get_contents($this->outputDir.'/flags.js');

    expect($content)
        ->toContain('export const AlwaysActiveFeatureFeatureFlag')
        ->toContain('export const AttributeNameFeatureFeatureFlag');
});

it('exports slug and name methods on each flag object', function () {
    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    $content = file_get_contents($this->outputDir.'/flags.js');

    expect($content)
        ->toContain("slug() { return 'always-active'; }")
        ->toContain("name() { return 'Always Active'; }")
        ->toContain("slug() { return 'attribute-name'; }")
        ->toContain("name() { return 'Attribute Name'; }");
});

it('outputs a success message with the file path', function () {
    $this->artisan('ffflags:publish-js')
        ->expectsOutput("Feature flags published to {$this->outputDir}/flags.js")
        ->assertExitCode(0);
});

it('warns when no feature flags are discovered', function () {
    $this->app->singleton(FeatureFlagDiscovery::class, function () {
        return new FeatureFlagDiscovery(directories: [], classes: []);
    });

    $this->artisan('ffflags:publish-js')
        ->expectsOutput('No feature flags discovered. Nothing to publish.')
        ->assertExitCode(0);
});

it('uses a custom filename from config', function () {
    config()->set('ffflags.js_usage.filename', 'custom-flags');

    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    expect(file_exists($this->outputDir.'/custom-flags.js'))->toBeTrue();

    // Clean up the custom file.
    unlink($this->outputDir.'/custom-flags.js');
});

it('publishes a TypeScript file when format is ts', function () {
    config()->set('ffflags.js_usage.format', 'ts');

    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    expect(file_exists($this->outputDir.'/flags.ts'))->toBeTrue();
});

it('includes FeatureFlag interface in TypeScript output', function () {
    config()->set('ffflags.js_usage.format', 'ts');

    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    $content = file_get_contents($this->outputDir.'/flags.ts');

    expect($content)
        ->toContain('interface FeatureFlag {')
        ->toContain('slug(): string;')
        ->toContain('name(): string;');
});

it('annotates exports with FeatureFlag type in TypeScript output', function () {
    config()->set('ffflags.js_usage.format', 'ts');

    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    $content = file_get_contents($this->outputDir.'/flags.ts');

    expect($content)
        ->toContain('export const AlwaysActiveFeatureFeatureFlag: FeatureFlag')
        ->toContain('export const AttributeNameFeatureFeatureFlag: FeatureFlag');
});

it('does not include interface or type annotations in JavaScript output', function () {
    $this->artisan('ffflags:publish-js')->assertExitCode(0);

    $content = file_get_contents($this->outputDir.'/flags.js');

    expect($content)
        ->not->toContain('interface FeatureFlag')
        ->not->toContain(': FeatureFlag');
});
