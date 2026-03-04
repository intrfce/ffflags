<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class FeatureFlagNotResolvableFromMiddlewareException extends LogicException
{
    public function __construct(
        public readonly string $featureClass,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] requires a scope but does not implement the ResolvingFromMiddleware interface. Either implement the interface or remove the scope parameter from resolve()."
        );
    }
}
