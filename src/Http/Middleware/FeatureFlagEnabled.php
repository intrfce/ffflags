<?php

namespace Intrfce\FFFlags\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Intrfce\FFFlags\Http\Middleware\Concerns\ResolvesFeatureFlagFromRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeatureFlagEnabled
{
    use ResolvesFeatureFlagFromRequest;

    /**
     * @param  class-string<\Intrfce\FFFlags\FeatureFlag>  $featureClass
     */
    public function __construct(
        protected string $featureClass,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->resolveFeatureFromRequest($this->featureClass, $request)) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
