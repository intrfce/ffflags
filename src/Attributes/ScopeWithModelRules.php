<?php

namespace Intrfce\FFFlags\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Intrfce\FFFlags\Exceptions\InvalidScopeWithModelRulesException;

#[Attribute(Attribute::TARGET_CLASS)]
class ScopeWithModelRules
{
    public readonly string $model;

    public function __construct(string $model)
    {
        if (! is_subclass_of($model, Model::class)) {
            throw new InvalidScopeWithModelRulesException($model);
        }

        $this->model = $model;
    }
}
