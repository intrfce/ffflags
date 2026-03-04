<?php

namespace Intrfce\FFFlags\Enums;

enum ScopeCondition: string
{
    case Equals = 'equals';
    case DoesNotEqual = 'does_not_equal';
    case IsOneOf = 'is_one_of';
    case IsNoneOf = 'is_none_of';
}
