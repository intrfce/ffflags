<?php

namespace Intrfce\FFFlags\Contracts;

interface ScopedFeatureInteraction
{
    public function isActive(string $featureClass): bool;

    public function anyActive(array $featureClasses): bool;

    public function allActive(array $featureClasses): bool;
}
