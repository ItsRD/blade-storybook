<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Attributes;

use Attribute;

/**
 * Extra metadata for a constructor parameter. Name, type and default value
 * are read through reflection, so this attribute is only needed when there
 * are allowed options or a description to add.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Prop
{
    /**
     * @param  array<int|string, mixed>|null  $options
     */
    public function __construct(
        public ?array $options = null,
        public ?string $description = null,
    ) {}
}
