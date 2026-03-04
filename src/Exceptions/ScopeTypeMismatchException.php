<?php

namespace Intrfce\FFFlags\Exceptions;

use InvalidArgumentException;

class ScopeTypeMismatchException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $featureClass,
        public readonly string $expectedType,
        public readonly string $actualType,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] expects scope of type [{$expectedType}], but [{$actualType}] was given."
        );
    }
}
