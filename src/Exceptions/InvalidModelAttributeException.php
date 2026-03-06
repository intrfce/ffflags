<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class InvalidModelAttributeException extends LogicException
{
    public function __construct(
        public readonly string $class,
    ) {
        parent::__construct(
            "[{$class}] is not an Eloquent model. #[Model] only accepts Model subclasses."
        );
    }
}
