<?php

namespace Intrfce\FFFlags;

use Intrfce\FFFlags\Attributes\BypassStorage;
use Intrfce\FFFlags\Attributes\ScopeWithModelRules;
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
        public bool $hasModelRules,
        /** @var class-string<\Illuminate\Database\Eloquent\Model>|null */
        public ?string $modelClass,
    ) {}

    public function getModelScopeLabel(): ?string
    {
        if (! $this->hasModelRules || ! $this->modelClass) {
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

        $scopeAttrs = $ref->getAttributes(ScopeWithModelRules::class);
        $hasModelRules = count($scopeAttrs) > 0;

        return new self(
            class: $className,
            name: $instance->getName(),
            slug: $instance->getSlug(),
            description: $instance->getDescription(),
            bypassesStorage: count($ref->getAttributes(BypassStorage::class)) > 0,
            hasModelRules: $hasModelRules,
            modelClass: $hasModelRules ? $scopeAttrs[0]->newInstance()->model : null,
        );
    }
}
