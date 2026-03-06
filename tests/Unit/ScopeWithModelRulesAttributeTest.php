<?php

use Intrfce\FFFlags\Attributes\Model;
use Intrfce\FFFlags\Exceptions\InvalidModelAttributeException;
use Intrfce\FFFlags\Tests\Fixtures\User;

it('accepts a model class', function () {
    $attr = new Model(User::class);
    expect($attr->model)->toBe(User::class);
});

it('throws for non-model classes', function () {
    new Model(\stdClass::class);
})->throws(InvalidModelAttributeException::class);
