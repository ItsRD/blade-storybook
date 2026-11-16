<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Attributes;

use Attribute;

/**
 * Declares an editable slot with its starting value. The value is compiled
 * as Blade, so it can contain components; edits made in the controls are
 * shown as plain text.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Slot
{
    public function __construct(
        public string $name = 'default',
        public ?string $default = null,
    ) {}
}
