<?php

namespace Intrfce\FFFlags\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Intrfce\FFFlags\Http\Middleware\Concerns\ResolvesFeatureFlagFromRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AllFeatureFlagsEnabled
{
    use ResolvesFeatureFlagFromRequest;

    /**
     * @param  array<class-string<\Intrfce\FFFlags\FeatureFlag>>  $featureClasses
     */
    public function __construct(
        protected array $featureClasses,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        foreach ($this->featureClasses as $featureClass) {
            if (! $this->resolveFeatureFromRequest($featureClass, $request)) {
                throw new HttpException(Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
