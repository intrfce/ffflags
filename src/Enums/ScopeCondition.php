<?php

namespace Intrfce\FFFlags\Enums;

enum ScopeCondition: string
{
    case Equals = 'equals';
    case DoesNotEqual = 'does_not_equal';
    case IsOneOf = 'is_one_of';
    case IsNoneOf = 'is_none_of';

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'Equals',
            self::DoesNotEqual => 'Does Not Equal',
            self::IsOneOf => 'Is One Of',
            self::IsNoneOf => 'Is None Of',
        };
    }

    public function isMultiSelect(): bool
    {
        return match ($this) {
            self::Equals, self::DoesNotEqual => false,
            self::IsOneOf, self::IsNoneOf => true,
        };
    }
}
