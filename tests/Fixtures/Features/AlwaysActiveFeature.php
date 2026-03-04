<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\FeatureFlag;

#[Name('Always Active')]
class AlwaysActiveFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return true;
    }
}
