<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('always-inactive')]
#[Name('Always Inactive')]
class AlwaysInactiveFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return false;
    }
}
