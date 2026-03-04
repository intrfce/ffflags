<?php

namespace Intrfce\FFFlags\Exceptions;

use InvalidArgumentException;

class InvalidScopeException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $featureClass,
        public readonly string $actualType,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] received a scope of type [{$actualType}], but only Eloquent models are supported as scopes."
        );
    }
}
