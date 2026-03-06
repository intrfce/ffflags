<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Model;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\LabelledUser;

#[Slug('labelled-user-scoped')]
#[Model(LabelledUser::class)]
class LabelledUserScopedFeature extends ManagedFeatureFlag {}
