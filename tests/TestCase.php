<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests;

use Illuminate\Support\Facades\Blade;
use ItsRD\BladeStorybook\BladeStorybookServiceProvider;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureButton;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureCard;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Blade::component('fixture-button', FixtureButton::class);
        Blade::component('fixture-card', FixtureCard::class);
    }

    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [BladeStorybookServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('blade-storybook.enabled', true);
        $app['config']->set('blade-storybook.route_prefix', 'storybook');
        $app['config']->set('blade-storybook.middleware', []);
        $app['config']->set('blade-storybook.paths', [__DIR__.'/Fixtures/Components']);
        $app['config']->set('blade-storybook.preview.assets', []);
    }
}
