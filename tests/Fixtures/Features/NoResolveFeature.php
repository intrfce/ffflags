<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\FeatureFlag;

#[Slug('no-resolve-feature')]
class NoResolveFeature extends FeatureFlag {}
