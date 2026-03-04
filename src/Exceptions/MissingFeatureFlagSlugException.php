<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class MissingFeatureFlagSlugException extends LogicException
{
    public function __construct(
        public readonly string $featureClass,
    ) {
        parent::__construct(
            "Feature [{$featureClass}] must have a slug. Add the #[Slug] attribute."
        );
    }
}
