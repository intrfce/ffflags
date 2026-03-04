<?php

namespace Intrfce\FFFlags\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Intrfce\FFFlags\Http\Middleware\Concerns\ResolvesFeatureFlagFromRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeatureFlagMiddleware
{
    use ResolvesFeatureFlagFromRequest;

    protected static ?Closure $respondUsing = null;

    /**
     * @param  class-string<\Intrfce\FFFlags\FeatureFlag>  ...$features
     */
    public function handle(Request $request, Closure $next, string $mode, string ...$features): Response
    {
        return match ($mode) {
            'is' => $this->handleIsActive($request, $next, $features[0]),
            'all' => $this->handleAllActive($request, $next, $features),
            'any' => $this->handleAnyActive($request, $next, $features),
        };
    }

    /**
     * @param  class-string<\Intrfce\FFFlags\FeatureFlag>  $feature
     */
    public static function isActive(string $feature): string
    {
        return static::class.':is,'.$feature;
    }

    /**
     * @param  array<class-string<\Intrfce\FFFlags\FeatureFlag>>  $features
     */
    public static function allActive(array $features): string
    {
        return static::class.':all,'.implode(',', $features);
    }

    /**
     * @param  array<class-string<\Intrfce\FFFlags\FeatureFlag>>  $features
     */
    public static function anyActive(array $features): string
    {
        return static::class.':any,'.implode(',', $features);
    }

    /**
     * Specify a callback that should be used to generate responses for failed feature checks.
     */
    public static function whenInactive(?Closure $callback): void
    {
        static::$respondUsing = $callback;
    }

    protected function handleIsActive(Request $request, Closure $next, string $featureClass): Response
    {
        if (! $this->resolveFeatureFromRequest($featureClass, $request)) {
            return $this->denyAccess($request, [$featureClass]);
        }

        return $next($request);
    }

    protected function handleAllActive(Request $request, Closure $next, array $featureClasses): Response
    {
        $inactive = [];

        foreach ($featureClasses as $featureClass) {
            if (! $this->resolveFeatureFromRequest($featureClass, $request)) {
                $inactive[] = $featureClass;
            }
        }

        if ($inactive !== []) {
            return $this->denyAccess($request, $inactive);
        }

        return $next($request);
    }

    protected function handleAnyActive(Request $request, Closure $next, array $featureClasses): Response
    {
        foreach ($featureClasses as $featureClass) {
            if ($this->resolveFeatureFromRequest($featureClass, $request)) {
                return $next($request);
            }
        }

        return $this->denyAccess($request, $featureClasses);
    }

    /**
     * @param  array<class-string<\Intrfce\FFFlags\FeatureFlag>>  $features
     */
    protected function denyAccess(Request $request, array $features): Response
    {
        if (static::$respondUsing !== null) {
            return call_user_func(static::$respondUsing, $request, $features);
        }

        throw new HttpException(Response::HTTP_FORBIDDEN);
    }
}
