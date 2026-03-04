<?php

namespace Intrfce\FFFlags;

use Illuminate\Support\Str;
use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Exceptions\MissingFeatureFlagNameException;
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
        $attrs = $ref->getAttributes(Name::class);

        if (count($attrs) > 0) {
            return $attrs[0]->newInstance()->name;
        }

        throw new MissingFeatureFlagNameException(get_class($this));
    }

    public function getSlug(): string
    {
        $ref = new ReflectionClass($this);
        $attrs = $ref->getAttributes(Name::class);

        if (count($attrs) > 0) {
            $nameAttr = $attrs[0]->newInstance();

            if ($nameAttr->slug !== null && $nameAttr->slug !== '') {
                return $nameAttr->slug;
            }
        }

        return Str::slug($this->getName());
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
