<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use ReflectionClass;

abstract class FeatureFlag
{
    protected string $name;

    protected string $description;

    public static function for(mixed $scope): PendingSingleFeatureInteraction
    {
        $manager = app(FeatureFlagManager::class);

        return new PendingSingleFeatureInteraction(new static(), $scope, $manager);
    }

    public function getName(): string
    {
        if (isset($this->name) && $this->name !== '') {
            return $this->name;
        }

        $ref = new ReflectionClass($this);
        $attrs = $ref->getAttributes(Name::class);

        if (count($attrs) > 0) {
            return $attrs[0]->newInstance()->name;
        }

        return $ref->getShortName();
    }

    public function getDescription(): string
    {
        if (isset($this->description) && $this->description !== '') {
            return $this->description;
        }

        $ref = new ReflectionClass($this);
        $attrs = $ref->getAttributes(Description::class);

        if (count($attrs) > 0) {
            return $attrs[0]->newInstance()->description;
        }

        return '';
    }
}
