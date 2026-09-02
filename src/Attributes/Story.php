<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Attributes;

use Attribute;

/**
 * A named example of a component with fixed prop and slot values.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Story
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $slots
     */
    public function __construct(
        public string $name,
        public array $props = [],
        public array $slots = [],
    ) {}
}
