<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Metadata;

enum ControlType: string
{
    case Select = 'select';
    case Toggle = 'toggle';
    case Text = 'text';
    case Number = 'number';

    /**
     * The value can be expressed, but not through a generated control.
     * Arrays are the typical case: they can only come from a story.
     */
    case None = 'none';
}
