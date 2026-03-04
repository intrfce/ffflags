<?php

namespace Intrfce\FFFlags\Exceptions;

use InvalidArgumentException;

class ScopeRequiredException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $featureClass,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] requires a scope. Use FeatureFlag::for(\$scope)->isActive() instead."
        );
    }
}
