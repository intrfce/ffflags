<?php

namespace Intrfce\FFFlags\Exceptions;

use InvalidArgumentException;

class ScopeDoesNotHaveKeyException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $featureClass,
        public readonly string $scopeType,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] was given a [{$scopeType}] scope that has not been persisted to the database. Save the model before checking feature flags."
        );
    }
}
