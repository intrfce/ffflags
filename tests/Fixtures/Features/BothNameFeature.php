<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\FeatureFlag;

#[Name('Attribute Name')]
#[Description('Attribute Description')]
class BothNameFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return true;
    }
}
