<?php

use Intrfce\FFFlags\Attributes\ScopeWithModelRules;
use Intrfce\FFFlags\Exceptions\InvalidScopeWithModelRulesException;
use Intrfce\FFFlags\Tests\Fixtures\User;

it('accepts a model class', function () {
    $attr = new ScopeWithModelRules(User::class);
    expect($attr->model)->toBe(User::class);
});

it('throws for non-model classes', function () {
    new ScopeWithModelRules(\stdClass::class);
})->throws(InvalidScopeWithModelRulesException::class);
