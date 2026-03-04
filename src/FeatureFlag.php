<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\Exceptions\MissingFeatureFlagSlugException;
use ReflectionClass;

abstract class FeatureFlag
{
    public static function for(mixed $scope): PendingSingleFeatureInteraction
    {
        $manager = app(FeatureFlagManager::class);

        return new PendingSingleFeatureInteraction(new static(), $scope, $manager);
    }

    public function getName(): string
    {
        $ref = new ReflectionClass($this);
        $nameAttrs = $ref->getAttributes(Name::class);

        if (count($nameAttrs) > 0) {
            return $nameAttrs[0]->newInstance()->name;
        }

        return $this->getSlug();
    }

    public function getSlug(): string
    {
        $ref = new ReflectionClass($this);
        $attrs = $ref->getAttributes(Slug::class);

        if (count($attrs) > 0) {
            return $attrs[0]->newInstance()->slug;
        }

        throw new MissingFeatureFlagSlugException(get_class($this));
    }

    public function getDescription(): string
    {
        $ref = new ReflectionClass($this);
        $attrs = $ref->getAttributes(Description::class);

        if (count($attrs) > 0) {
            return $attrs[0]->newInstance()->description;
        }

        return '';
    }
}
