<?php

use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;

it('stores name value', function () {
    $attr = new Name('Test Name');
    expect($attr->name)->toBe('Test Name');
});

it('stores description value', function () {
    $attr = new Description('Test Description');
    expect($attr->description)->toBe('Test Description');
});
