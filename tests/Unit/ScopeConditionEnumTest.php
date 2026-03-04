<?php

use Intrfce\FFFlags\Enums\ScopeCondition;

it('has correct string values', function () {
    expect(ScopeCondition::Equals->value)->toBe('equals');
    expect(ScopeCondition::DoesNotEqual->value)->toBe('does_not_equal');
    expect(ScopeCondition::IsOneOf->value)->toBe('is_one_of');
    expect(ScopeCondition::IsNoneOf->value)->toBe('is_none_of');
});

it('can be created from string values', function () {
    expect(ScopeCondition::from('equals'))->toBe(ScopeCondition::Equals);
    expect(ScopeCondition::from('does_not_equal'))->toBe(ScopeCondition::DoesNotEqual);
    expect(ScopeCondition::from('is_one_of'))->toBe(ScopeCondition::IsOneOf);
    expect(ScopeCondition::from('is_none_of'))->toBe(ScopeCondition::IsNoneOf);
});
