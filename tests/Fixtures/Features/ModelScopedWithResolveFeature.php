<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Model;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\User;

#[Slug('model-scoped-with-resolve')]
#[Model(User::class)]
class ModelScopedWithResolveFeature extends ManagedFeatureFlag
{
    public function fallback(): bool
    {
        return false;
    }
}
