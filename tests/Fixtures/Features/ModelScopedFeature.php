<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\ScopeWithModelRules;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\User;

#[Slug('model-scoped')]
#[ScopeWithModelRules(User::class)]
class ModelScopedFeature extends FeatureFlag {}
