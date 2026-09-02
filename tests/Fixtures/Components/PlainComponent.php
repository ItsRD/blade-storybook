<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Components;

use Illuminate\View\Component;

/**
 * Deliberately without #[Storybook], so it must never show up.
 */
final class PlainComponent extends Component
{
    public function render(): string
    {
        return '<p>plain</p>';
    }
}
