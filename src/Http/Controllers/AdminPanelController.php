<?php

namespace Intrfce\FFFlags\Http\Controllers;

use Illuminate\Http\Request;

class AdminPanelController
{
    public function __invoke(Request $request)
    {
        $indexPath = public_path('ffflags/admin/index.html');

        abort_unless(file_exists($indexPath), 404, 'Admin panel assets not published. Run: php artisan vendor:publish --tag=ffflags-admin-assets');

        return response()->file($indexPath, [
            'Content-Type' => 'text/html',
        ]);
    }
}
