<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\FeatureFlag;

class AlwaysActiveFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return true;
    }
}
