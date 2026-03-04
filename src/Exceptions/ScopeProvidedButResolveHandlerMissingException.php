<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class ScopeProvidedButResolveHandlerMissingException extends LogicException
{
    public function __construct(
        public readonly string $featureClass,
    ) {
        parent::__construct(
            "A scope was provided when checking feature [{$featureClass}], but the feature does not have a resolve() method."
        );
    }
}
