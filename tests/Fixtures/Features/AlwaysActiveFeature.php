<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('always-active')]
#[Name('Always Active')]
class AlwaysActiveFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return true;
    }
}
