<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Attributes\BypassStorage;
use Intrfce\FFFlags\Attributes\Model;
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
        public bool $isManaged,
        /** @var class-string<\Illuminate\Database\Eloquent\Model>|null */
        public ?string $modelClass,
        public ?string $modelTitleColumn = null,
    ) {}

    public function getModelScopeLabel(): ?string
    {
        if (! $this->isManaged || ! $this->modelClass) {
            return null;
        }

        if (method_exists($this->modelClass, 'featureFlagModelLabel')) {
            return $this->modelClass::featureFlagModelLabel();
        }

        return class_basename($this->modelClass);
    }

    /**
     * @param class-string<FeatureFlag> $className
     */
    public static function fromClass(string $className): self
    {
        $instance = new $className();
        $ref = new ReflectionClass($className);

        $isManaged = is_subclass_of($className, ManagedFeatureFlag::class);
        $modelAttrs = $ref->getAttributes(Model::class);

        $modelInstance = count($modelAttrs) > 0 ? $modelAttrs[0]->newInstance() : null;

        return new self(
            class: $className,
            name: $instance->getName(),
            slug: $instance->getSlug(),
            description: $instance->getDescription(),
            bypassesStorage: count($ref->getAttributes(BypassStorage::class)) > 0,
            isManaged: $isManaged,
            modelClass: $modelInstance?->model,
            modelTitleColumn: $modelInstance?->titleColumn,
        );
    }
}
