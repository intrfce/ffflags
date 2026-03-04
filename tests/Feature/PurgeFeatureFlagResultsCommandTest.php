<?php

use Intrfce\FFFlags\Models\FeatureFlagResult;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;

it('purges all cached feature flag results', function () {
    // Seed some results.
    AlwaysActiveFeature::for(null)->isActive();
    expect(FeatureFlagResult::count())->toBe(1);

    $this->artisan('ffflags:purge')
        ->assertExitCode(0);

    expect(FeatureFlagResult::count())->toBe(0);
});

it('outputs a confirmation message', function () {
    $this->artisan('ffflags:purge')
        ->expectsOutput('All cached feature flag results have been purged.')
        ->assertExitCode(0);
});
