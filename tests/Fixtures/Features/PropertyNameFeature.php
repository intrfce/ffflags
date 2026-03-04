<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\FeatureFlag;

class PropertyNameFeature extends FeatureFlag
{
    protected string $name = 'Property Name';

    protected string $description = 'Property Description';

    public function resolve(): bool
    {
        return true;
    }
}
