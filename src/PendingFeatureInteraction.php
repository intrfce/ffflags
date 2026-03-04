<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Concerns\ResolvesFeatureFlags;
use Intrfce\FFFlags\Contracts\ScopedFeatureInteraction;

class PendingFeatureInteraction implements ScopedFeatureInteraction
{
    use ResolvesFeatureFlags;

    public function __construct(
        protected mixed $scope,
        protected FeatureFlagManager $manager,
    ) {}

    /**
     * @param  class-string<FeatureFlag>  $featureClass
     */
    public function isActive(string $featureClass): bool
    {
        return static::resolveFeature(new $featureClass(), $this->scope, $this->manager);
    }

    /**
     * @param  array<class-string<FeatureFlag>>  $featureClasses
     */
    public function anyActive(array $featureClasses): bool
    {
        foreach ($featureClasses as $featureClass) {
            if ($this->isActive($featureClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<class-string<FeatureFlag>>  $featureClasses
     */
    public function allActive(array $featureClasses): bool
    {
        foreach ($featureClasses as $featureClass) {
            if (! $this->isActive($featureClass)) {
                return false;
            }
        }

        return true;
    }
}
