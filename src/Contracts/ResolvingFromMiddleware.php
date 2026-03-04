<?php

namespace Intrfce\FFFlags\Contracts;

use Illuminate\Http\Request;

interface ResolvingFromMiddleware
{
    public function resolveMiddlewareScope(Request $request): mixed;
}
