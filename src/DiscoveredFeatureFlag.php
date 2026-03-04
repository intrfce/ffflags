<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Attributes\BypassStorage;
use ReflectionClass;

readonly class DiscoveredFeatureFlag
{
    public function __construct(
        /** @var class-string<FeatureFlag> */
        public string $class,
        public string $name,
        public string $slug,
        public string $description,
        public bool $bypassesStorage,
    ) {}

    /**
     * @param class-string<FeatureFlag> $className
     */
    public static function fromClass(string $className): self
    {
        $instance = new $className();
        $ref = new ReflectionClass($className);

        return new self(
            class: $className,
            name: $instance->getName(),
            slug: $instance->getSlug(),
            description: $instance->getDescription(),
            bypassesStorage: count($ref->getAttributes(BypassStorage::class)) > 0,
        );
    }
}
