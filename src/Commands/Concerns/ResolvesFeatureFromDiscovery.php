<?php

namespace Intrfce\FFFlags\Commands\Concerns;

use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\ManagedFeatureFlag;

trait ResolvesFeatureFromDiscovery
{
    protected function resolveFeature(FeatureFlagDiscovery $discovery, string $name): ?ManagedFeatureFlag
    {
        $discovered = $discovery->discover();

        $match = $discovered->first(function ($flag) use ($name) {
            return $flag->slug === $name
                || class_basename($flag->class) === $name
                || $flag->class === $name;
        });

        if ($match === null) {
            return null;
        }

        $instance = new $match->class();

        return $instance instanceof ManagedFeatureFlag ? $instance : null;
    }

    protected function hasDuplicateSlugs(FeatureFlagDiscovery $discovery): bool
    {
        $duplicates = $discovery->findDuplicateSlugs();

        if (empty($duplicates)) {
            return false;
        }

        $this->error('Duplicate feature flag slugs detected:');

        foreach ($duplicates as $slug => $classes) {
            $this->line("  [{$slug}] is used by:");
            foreach ($classes as $class) {
                $this->line("    - {$class}");
            }
        }

        return true;
    }
}
