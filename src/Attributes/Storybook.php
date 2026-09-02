<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Attributes;

use Attribute;

/**
 * Marks a class-based Blade component as part of the storybook.
 *
 * Components without this attribute are ignored entirely.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Storybook
{
    public function __construct(
        public string $category,
        public ?string $name = null,
        public ?string $description = null,
    ) {}
}
