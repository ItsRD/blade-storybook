<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Attributes;

use Attribute;

/**
 * A named example of a component with fixed prop and slot values.
 *
 * Props that cannot be written inside an attribute, such as models made with
 * a factory or DTOs built from them, come from a static method referenced
 * through $using: [SomeStories::class, 'method']. It runs when the story is
 * shown and returns an array of props, which override the inline $props.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Story
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $slots
     * @param  array{0: class-string, 1: string}|null  $using
     */
    public function __construct(
        public string $name,
        public array $props = [],
        public array $slots = [],
        public ?array $using = null,
        public ?string $width = null,
    ) {}
}
