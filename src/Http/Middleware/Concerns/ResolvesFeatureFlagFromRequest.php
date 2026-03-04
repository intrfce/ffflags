<?php

namespace Intrfce\FFFlags\Http\Middleware\Concerns;

use Illuminate\Http\Request;
use Intrfce\FFFlags\Contracts\ResolvingFromMiddleware;
use Intrfce\FFFlags\Exceptions\FeatureFlagNotResolvableFromMiddlewareException;
use Intrfce\FFFlags\FeatureFlag;
use ReflectionMethod;

trait ResolvesFeatureFlagFromRequest
{
    /**
     * @param  class-string<FeatureFlag>  $featureClass
     */
    protected function resolveFeatureFromRequest(string $featureClass, Request $request): bool
    {
        $feature = new $featureClass();

        $requiresScope = method_exists($feature, 'resolve')
            && count((new ReflectionMethod($feature, 'resolve'))->getParameters()) > 0;

        if ($requiresScope && ! ($feature instanceof ResolvingFromMiddleware)) {
            throw new FeatureFlagNotResolvableFromMiddlewareException(
                featureClass: $featureClass,
            );
        }

        if ($requiresScope) {
            $scope = $feature->resolveMiddlewareScope($request);

            return $featureClass::for($scope)->isActive();
        }

        return $featureClass::for(null)->isActive();
    }
}
