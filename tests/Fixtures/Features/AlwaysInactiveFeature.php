<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\FeatureFlag;

class AlwaysInactiveFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return false;
    }
}
