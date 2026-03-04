<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class MissingFeatureFlagNameException extends LogicException
{
    public function __construct(
        public readonly string $featureClass,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] must have a name. Set a \$name property or add the #[Name] attribute."
        );
    }
}
