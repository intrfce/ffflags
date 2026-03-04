<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\User;

class ScopedFeature extends FeatureFlag
{
    public function resolve(User $user): bool
    {
        return $user->email === 'active@example.com';
    }
}
