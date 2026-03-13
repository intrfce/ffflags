<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Path
    |--------------------------------------------------------------------------
    |
    | The URI path where the FFFlags dashboard will be accessible.
    |
    */

    'path' => 'ffflags',

    /*
    |--------------------------------------------------------------------------
    | Dashboard Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware stack applied to the FFFlags dashboard route. By default,
    | it uses the web middleware group, requires authentication, and checks
    | the 'view-ffflags-dashboard' gate. You can customise this to suit your
    | application's needs.
    |
    */

    'middleware' => [
        'web',
        'auth',
        'can:view-ffflags-dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flag Discovery
    |--------------------------------------------------------------------------
    |
    | Configure how FFFlags discovers your feature flag classes. By default,
    | it scans the app/Features directory (where make:feature places them).
    | You can add additional directories or explicitly register classes
    | that live in non-standard locations (e.g., in packages).
    |
    */

    'discovery' => [
        'directories' => [
            'app/Features',
        ],
        'classes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Evaluation Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, FFFlags will log each feature flag evaluation to the
    | database (only on first evaluation, not subsequent cache hits).
    | This allows you to see when and where flags are being checked
    | from the admin dashboard.
    |
    */

    'log_evaluations' => false,

    /*
    |--------------------------------------------------------------------------
    | JavaScript Usage
    |--------------------------------------------------------------------------
    |
    | Configure how FFFlags publishes feature flags as JavaScript exports.
    | Run `php artisan ffflags:publish-js` to generate the file.
    | Set format to 'ts' to output TypeScript with typed interfaces.
    |
    */

    'js_usage' => [
        'output_directory' => 'resources/js/enums',
        'filename' => 'flags',
        'format' => 'js', // 'js' or 'ts'
    ],

];
