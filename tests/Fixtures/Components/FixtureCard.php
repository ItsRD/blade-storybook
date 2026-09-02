<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Components;

use DateTimeImmutable;
use Illuminate\View\Component;
use ItsRD\BladeStorybook\Attributes\Prop;
use ItsRD\BladeStorybook\Attributes\Storybook;

#[Storybook(category: 'Cards')]
final class FixtureCard extends Component
{
    public function __construct(
        #[Prop(description: 'Publication date')]
        public DateTimeImmutable $publishedAt,
        public string $title,
    ) {}

    public function render(): string
    {
        return '<div>{{ $title }} {{ $publishedAt->format("Y") }}</div>';
    }
}
