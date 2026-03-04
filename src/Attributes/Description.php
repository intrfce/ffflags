<?php

namespace Intrfce\FFFlags\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Description
{
    public function __construct(
        public readonly string $description,
    ) {}
}
