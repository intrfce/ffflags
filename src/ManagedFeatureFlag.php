<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Attributes\Model;
use Intrfce\FFFlags\Exceptions\MissingModelAttributeException;
use ReflectionClass;

abstract class ManagedFeatureFlag extends FeatureFlag
{
    public function fallback(): bool
    {
        return false;
    }

    public function getModelClass(): string
    {
        $ref = new ReflectionClass($this);
        $attrs = $ref->getAttributes(Model::class);

        if (count($attrs) === 0) {
            throw new MissingModelAttributeException(get_class($this));
        }

        return $attrs[0]->newInstance()->model;
    }
}
