<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('property-name')]
#[Name('Property Name')]
#[Description('Property Description')]
class PropertyNameFeature extends FeatureFlag
{
    public function resolve(): bool
    {
        return true;
    }
}
