<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook;

use Illuminate\Support\Facades\Route;
use ItsRD\BladeStorybook\Rendering\ComponentRenderer;
use ItsRD\BladeStorybook\Support\ClassFileResolver;
use ItsRD\BladeStorybook\Support\ComponentParser;
use ItsRD\BladeStorybook\Support\ComponentScanner;
use ItsRD\BladeStorybook\Support\TagNameResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class BladeStorybookServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('blade-storybook')
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ClassFileResolver::class);
        $this->app->singleton(TagNameResolver::class);
        $this->app->singleton(ComponentScanner::class);
        $this->app->singleton(ComponentParser::class);
        $this->app->singleton(ComponentRenderer::class);
        $this->app->singleton(StorybookRegistry::class);
    }

    public function packageBooted(): void
    {
        if (! config()->boolean('blade-storybook.enabled', false)) {
            return;
        }

        Route::group([
            'prefix' => config()->string('blade-storybook.route_prefix', 'storybook'),
            'middleware' => config()->array('blade-storybook.middleware', ['web']),
            'as' => 'blade-storybook.',
        ], function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
