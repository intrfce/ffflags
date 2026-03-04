<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\ScopeWithModelRules;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\LabelledUser;

#[Slug('labelled-user-scoped')]
#[ScopeWithModelRules(LabelledUser::class)]
class LabelledUserScopedFeature extends FeatureFlag {}
