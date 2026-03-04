<?php

use Intrfce\FFFlags\Attributes\Description;
use Intrfce\FFFlags\Attributes\Name;
use Intrfce\FFFlags\Attributes\Slug;

it('stores slug value', function () {
    $attr = new Slug('test-slug');
    expect($attr->slug)->toBe('test-slug');
});

it('stores name value', function () {
    $attr = new Name('Test Name');
    expect($attr->name)->toBe('Test Name');
});

it('stores description value', function () {
    $attr = new Description('Test Description');
    expect($attr->description)->toBe('Test Description');
});
