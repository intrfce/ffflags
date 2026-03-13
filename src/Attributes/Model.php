<?php

namespace Intrfce\FFFlags\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Intrfce\FFFlags\Exceptions\InvalidModelAttributeException;

#[Attribute(Attribute::TARGET_CLASS)]
class Model
{
    public readonly string $model;

    public readonly ?string $titleColumn;

    public function __construct(string $model, ?string $titleColumn = null)
    {
        if (! is_subclass_of($model, EloquentModel::class)) {
            throw new InvalidModelAttributeException($model);
        }

        $this->model = $model;
        $this->titleColumn = $titleColumn;
    }
}
