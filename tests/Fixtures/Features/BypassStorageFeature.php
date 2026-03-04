<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\BypassStorage;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('bypass-storage')]
#[BypassStorage]
class BypassStorageFeature extends FeatureFlag
{
    public static int $resolveCount = 0;

    public function resolve(): bool
    {
        static::$resolveCount++;

        return true;
    }
}
