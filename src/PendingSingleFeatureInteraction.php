<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Concerns\ResolvesFeatureFlags;
use Intrfce\FFFlags\Contracts\SingleFeatureInteraction;

class PendingSingleFeatureInteraction implements SingleFeatureInteraction
{
    use ResolvesFeatureFlags;

    public function __construct(
        protected FeatureFlag $feature,
        protected mixed $scope,
        protected FeatureFlagManager $manager,
    ) {}

    public function isActive(): bool
    {
        return static::resolveFeature($this->feature, $this->scope, $this->manager);
    }
}
