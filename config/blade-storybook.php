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
    |
    | "head" and "scripts" add markup that your layout loads outside of Vite,
    | such as web fonts or a CDN build of Alpine. "head" goes into <head>,
    | "scripts" at the end of <body>. Use a view name or a raw HTML string.
    |
    | "livewire" adds Livewire's styles and scripts (which include Alpine)
    | when Livewire is installed, so Alpine-driven components work.
    */
    'preview' => [
        'assets' => [
            'resources/css/app.css',
            'resources/js/app.js',
        ],

        'head' => null,

        'scripts' => null,

        'livewire' => true,

        'body_class' => 'p-8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database rollback
    |--------------------------------------------------------------------------
    |
    | Stories should build their data with make(), but a factory that still
    | creates a related model (a "user_id" => User::factory() in a definition)
    | would add a row on every page load. Each storybook request therefore
    | runs inside a transaction that is always rolled back. List every
    | connection your stories may write to; null is the default connection.
    */
    'rollback' => [
        'enabled' => true,

        'connections' => [null],
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
