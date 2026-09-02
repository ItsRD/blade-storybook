<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Routes are only registered when this is true. The package is a normal
    | dependency, so it can be switched on outside local when needed.
    */
    'enabled' => env('BLADE_STORYBOOK_ENABLED', env('APP_ENV') === 'local'),

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'storybook',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The full middleware stack for every storybook route. Add authentication
    | or IP restrictions here when the storybook is exposed beyond local.
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Component paths
    |--------------------------------------------------------------------------
    |
    | Directories scanned for class-based Blade components. Only classes with
    | the #[Storybook] attribute end up in the interface.
    */
    'paths' => [
        app_path('View/Components'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    |
    | The Vite entry points loaded inside the preview iframe, so components
    | look exactly as they do in the application, plus the classes applied to
    | the preview body.
    */
    'preview' => [
        'assets' => [
            'resources/css/app.css',
            'resources/js/app.js',
        ],

        'body_class' => 'p-8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Viewports
    |--------------------------------------------------------------------------
    |
    | Widths in pixels for the viewport buttons. Null means full width.
    */
    'viewports' => [
        'Mobile' => 375,
        'Tablet' => 768,
        'Desktop' => null,
    ],

];
