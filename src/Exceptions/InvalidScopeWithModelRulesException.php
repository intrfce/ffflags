<?php

namespace Intrfce\FFFlags\Exceptions;

use LogicException;

class InvalidScopeWithModelRulesException extends LogicException
{
    public function __construct(
        public readonly string $class,
    ) {
        parent::__construct(
            "[{$class}] is not an Eloquent model. #[ScopeWithModelRules] only accepts Model subclasses."
        );
    }
}
