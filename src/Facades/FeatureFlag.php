<?php

namespace Intrfce\FFFlags\Facades;

use Illuminate\Support\Facades\Facade;
use Intrfce\FFFlags\FeatureFlagManager;
use Intrfce\FFFlags\PendingFeatureInteraction;

/**
 * @method static PendingFeatureInteraction for(mixed $scope)
 * @method static bool isActive(string $featureClass)
 * @method static void purgeAll()
 *
 * @see FeatureFlagManager
 */
class FeatureFlag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureFlagManager::class;
    }
}
