<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Components;

use Illuminate\View\Component;
use ItsRD\BladeStorybook\Attributes\Prop;
use ItsRD\BladeStorybook\Attributes\Story;
use ItsRD\BladeStorybook\Attributes\Storybook;

#[Storybook(category: 'Forms', name: 'Fixture input', width: '320')]
#[Story('With prefix', ['prefix' => 'https://'], width: '50%')]
final class FixtureInput extends Component
{
    public function __construct(
        #[Prop(description: 'Text shown before the input, or false for none')]
        public false|string $prefix = false,
    ) {}

    public function render(): string
    {
        return '<div data-prefix="{{ $prefix === false ? "none" : $prefix }}"><input></div>';
    }
}
