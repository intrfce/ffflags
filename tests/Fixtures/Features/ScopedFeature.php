<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\User;

#[Name('Scoped Feature')]
class ScopedFeature extends FeatureFlag
{
    public function resolve(User $user): bool
    {
        return $user->email === 'active@example.com';
    }
}
