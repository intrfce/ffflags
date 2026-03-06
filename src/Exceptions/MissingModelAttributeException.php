<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class MissingModelAttributeException extends LogicException
{
    public function __construct(string $featureClass)
    {
        parent::__construct(
            "ManagedFeatureFlag [{$featureClass}] must have a #[Model] attribute."
        );
    }
}
