<?php

use Intrfce\FFFlags\Exceptions\InvalidScopeException;
use Intrfce\FFFlags\Exceptions\ScopeProvidedButResolveHandlerMissingException;
use Intrfce\FFFlags\Exceptions\ScopeTypeMismatchException;
use Intrfce\FFFlags\Exceptions\ScopeDoesNotHaveKeyException;
use Intrfce\FFFlags\Tests\Fixtures\Features\AlwaysActiveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\NoResolveFeature;
use Intrfce\FFFlags\Tests\Fixtures\Features\ScopedFeature;
use Intrfce\FFFlags\Tests\Fixtures\Team;
use Intrfce\FFFlags\Tests\Fixtures\User;

it('throws when scope type does not match resolve parameter', function () {
    $team = new Team();
    $team->id = 1;
    $team->name = 'Acme';

    ScopedFeature::for($team)->isActive();
})->throws(ScopeTypeMismatchException::class);

it('includes feature class and types in exception message', function () {
    $team = new Team();
    $team->id = 1;
    $team->name = 'Acme';

    try {
        ScopedFeature::for($team)->isActive();
    } catch (ScopeTypeMismatchException $e) {
        expect($e->featureClass)->toBe(ScopedFeature::class);
        expect($e->expectedType)->toBe(User::class);
        expect($e->actualType)->toBe(Team::class);
        expect($e->getMessage())->toContain(ScopedFeature::class);
        expect($e->getMessage())->toContain(User::class);
        expect($e->getMessage())->toContain(Team::class);

        return;
    }

    $this->fail('Expected ScopeTypeMismatchException was not thrown.');
});

it('does not throw when scope type matches', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'active@example.com';

    expect(ScopedFeature::for($user)->isActive())->toBeTrue();
});

it('throws when scope is provided but no resolve method exists', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'test@example.com';

    NoResolveFeature::for($user)->isActive();
})->throws(ScopeProvidedButResolveHandlerMissingException::class);

it('includes feature class in resolve handler missing exception', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'test@example.com';

    try {
        NoResolveFeature::for($user)->isActive();
    } catch (ScopeProvidedButResolveHandlerMissingException $e) {
        expect($e->featureClass)->toBe(NoResolveFeature::class);
        expect($e->getMessage())->toContain(NoResolveFeature::class);
        expect($e->getMessage())->toContain('resolve()');

        return;
    }

    $this->fail('Expected ScopeProvidedButResolveHandlerMissingException was not thrown.');
});

it('throws when scope is not an eloquent model', function () {
    AlwaysActiveFeature::for('a string scope')->isActive();
})->throws(InvalidScopeException::class);

it('includes feature class and actual type in invalid scope exception', function () {
    try {
        AlwaysActiveFeature::for(new stdClass())->isActive();
    } catch (InvalidScopeException $e) {
        expect($e->featureClass)->toBe(AlwaysActiveFeature::class);
        expect($e->actualType)->toBe('stdClass');
        expect($e->getMessage())->toContain('Eloquent models');

        return;
    }

    $this->fail('Expected InvalidScopeException was not thrown.');
});

it('throws when scope model has not been persisted', function () {
    $user = new User();
    $user->email = 'test@example.com';

    ScopedFeature::for($user)->isActive();
})->throws(ScopeDoesNotHaveKeyException::class);

it('includes feature class and scope type in unpersisted scope exception', function () {
    $user = new User();
    $user->email = 'test@example.com';

    try {
        ScopedFeature::for($user)->isActive();
    } catch (ScopeDoesNotHaveKeyException $e) {
        expect($e->featureClass)->toBe(ScopedFeature::class);
        expect($e->scopeType)->toBe(User::class);
        expect($e->getMessage())->toContain('not been persisted');

        return;
    }

    $this->fail('Expected ScopeDoesNotHaveKeyException was not thrown.');
});
