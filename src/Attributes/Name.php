<?php

namespace Intrfce\FFFlags\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Name
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
    ) {}
}
