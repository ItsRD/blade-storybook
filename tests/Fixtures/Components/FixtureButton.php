<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Components;

use Illuminate\View\Component;
use ItsRD\BladeStorybook\Attributes\Prop;
use ItsRD\BladeStorybook\Attributes\Slot;
use ItsRD\BladeStorybook\Attributes\Story;
use ItsRD\BladeStorybook\Attributes\Storybook;

#[Storybook(category: 'Forms', name: 'Fixture button', description: 'A button used in tests.')]
#[Story('Primary', ['variant' => 'primary'])]
#[Story('Secondary small', ['variant' => 'secondary', 'size' => 2, 'meta' => ['role' => 'cancel']], ['default' => 'Annuleren'])]
#[Slot('default', default: 'Opslaan')]
#[Slot('footer', default: 'Footer')]
final class FixtureButton extends Component
{
    /**
     * @param  array<string, string>  $meta
     */
    public function __construct(
        #[Prop(options: ['primary', 'secondary'], description: 'Colour variant')]
        public string $variant = 'primary',
        #[Prop]
        public int $size = 1,
        #[Prop]
        public bool $disabled = false,
        #[Prop]
        public ?string $href = null,
        public array $meta = [],
        public string $internal = 'none',
    ) {}

    public function render(): string
    {
        return '<button class="{{ $variant }} size-{{ $size }}" data-href="{{ $href === null ? "none" : $href }}" data-meta="{{ $meta["role"] ?? "none" }}" data-internal="{{ $internal }}" @disabled($disabled)>{{ $slot }}<span>{{ $footer ?? "" }}</span></button>';
    }
}
