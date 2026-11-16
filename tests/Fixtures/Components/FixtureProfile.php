<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Components;

use Illuminate\View\Component;
use ItsRD\BladeStorybook\Attributes\Prop;
use ItsRD\BladeStorybook\Attributes\Story;
use ItsRD\BladeStorybook\Attributes\Storybook;
use ItsRD\BladeStorybook\Tests\Fixtures\Data\AddressData;
use ItsRD\BladeStorybook\Tests\Fixtures\Data\UserData;
use ItsRD\BladeStorybook\Tests\Fixtures\Stories\ProfileStories;

#[Storybook(category: 'Profiles', name: 'Fixture profile')]
#[Story('Admin', [
    'user' => new UserData(
        name: 'Jan',
        role: 'admin',
        address: new AddressData(city: 'Utrecht'),
        permissions: ['users.manage'],
    ),
])]
#[Story('Guest', ['user' => new UserData(name: 'Gast', role: 'guest')])]
#[Story('Empty')]
#[Story('From model', using: [ProfileStories::class, 'admin'])]
#[Story('Broken', using: [ProfileStories::class, 'broken'])]
#[Story('Not an array', using: [ProfileStories::class, 'notAnArray'])]
#[Story('Missing method', using: [ProfileStories::class, 'nope'])]
final class FixtureProfile extends Component
{
    public function __construct(
        #[Prop]
        public UserData $user,
        #[Prop]
        public bool $compact = false,
    ) {}

    public function render(): string
    {
        return '<div class="{{ $compact ? "compact" : "full" }}" data-role="{{ $user->role }}">{{ $user->name }} {{ $user->address?->city ?? "no-city" }} {{ implode(",", $user->permissions) }}</div>';
    }
}
