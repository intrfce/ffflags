<?php

namespace Intrfce\FFFlags\Http\Controllers;

use Illuminate\Http\Request;
use Intrfce\FFFlags\FeatureFlagDiscovery;

class DashboardController
{
    public function __invoke(Request $request, FeatureFlagDiscovery $discovery)
    {
        return view('ffflags::dashboard', [
            'features' => $discovery->discover(),
        ]);
    }
}
