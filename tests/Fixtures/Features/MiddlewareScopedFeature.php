<?php

namespace Intrfce\FFFlags\Tests\Fixtures\Features;

use Illuminate\Http\Request;
use Intrfce\FFFlags\Attributes\Slug;
use Intrfce\FFFlags\Contracts\ResolvingFromMiddleware;
use Intrfce\FFFlags\FeatureFlag;
use Intrfce\FFFlags\Tests\Fixtures\User;

#[Slug('middleware-scoped-feature')]
class MiddlewareScopedFeature extends FeatureFlag implements ResolvingFromMiddleware
{
    public function resolveMiddlewareScope(Request $request): mixed
    {
        $user = new User();
        $user->id = 1;
        $user->email = $request->header('X-User-Email', '');

        return $user;
    }

    public function resolve(User $user): bool
    {
        return $user->email === 'active@example.com';
    }
}
