<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\User;

#[Slug('counting-feature')]
class CountingFeature extends FeatureFlag
{
    public static int $resolveCount = 0;

    public function resolve(User $user): bool
    {
        static::$resolveCount++;

        return $user->email === 'active@example.com';
    }
}
