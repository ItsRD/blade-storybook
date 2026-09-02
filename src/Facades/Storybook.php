<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Facades;

use Illuminate\Support\Facades\Facade;
use ItsRD\BladeStorybook\StorybookRegistry;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \Illuminate\Support\Collection categories()
 * @method static \ItsRD\BladeStorybook\Metadata\ComponentMetadata|null find(string $id)
 * @method static \ItsRD\BladeStorybook\Metadata\ComponentMetadata|null first()
 * @method static void flush()
 *
 * @see StorybookRegistry
 */
final class Storybook extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StorybookRegistry::class;
    }
}
