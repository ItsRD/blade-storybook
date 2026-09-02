<?php

declare(strict_types=1);

use ItsRD\BladeStorybook\Facades\Storybook;
use ItsRD\BladeStorybook\Metadata\ControlType;
use ItsRD\BladeStorybook\Rendering\ComponentState;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureButton;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\FixtureCard;
use ItsRD\BladeStorybook\Tests\Fixtures\Components\PlainComponent;

it('only registers components marked with the storybook attribute', function (): void {
    $classes = Storybook::all()->map->class;

    expect($classes)->toContain(FixtureButton::class)
        ->and($classes)->toContain(FixtureCard::class)
        ->and($classes)->not->toContain(PlainComponent::class);
});

it('reads name, category, description and tag name', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    expect($component)->not->toBeNull()
        ->and($component->name)->toBe('Fixture button')
        ->and($component->category)->toBe('Forms')
        ->and($component->description)->toBe('A button used in tests.')
        ->and($component->tag)->toBe('fixture-button');
});

it('only exposes constructor parameters marked with the prop attribute', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    expect($component->props()->map->name->all())->toBe(['variant', 'size', 'disabled', 'href'])
        ->and($component->unmanagedProps()->map->name->all())->toBe(['meta', 'internal'])
        ->and(array_column($component->toArray()['props'], 'name'))->toBe(['variant', 'size', 'disabled', 'href']);
});

it('derives prop metadata from the constructor', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    $variant = $component->prop('variant');
    expect($variant->type)->toBe('string')
        ->and($variant->default)->toBe('primary')
        ->and($variant->control)->toBe(ControlType::Select)
        ->and($variant->description)->toBe('Colour variant')
        ->and($variant->selectOptions())->toBe(['primary' => 'primary', 'secondary' => 'secondary']);

    expect($component->prop('size')->control)->toBe(ControlType::Number)
        ->and($component->prop('disabled')->control)->toBe(ControlType::Toggle)
        ->and($component->prop('href')->control)->toBe(ControlType::Text)
        ->and($component->prop('size')->description)->toBeNull();
});

it('falls back to a single default story and keeps declared slots', function (): void {
    $button = Storybook::find(str_replace('\\', '.', FixtureButton::class));
    $card = Storybook::find(str_replace('\\', '.', FixtureCard::class));

    expect($button->stories()->map->name->all())->toBe(['Primary', 'Secondary small'])
        ->and($button->slots()->map->name->all())->toBe(['default', 'footer'])
        ->and($card->stories()->map->name->all())->toBe(['Default']);
});

it('blocks rendering for required props that the storybook cannot supply', function (): void {
    $card = Storybook::find(str_replace('\\', '.', FixtureCard::class));

    expect($card->isRenderable())->toBeFalse()
        ->and($card->blockingProps()->map->name->all())->toBe(['publishedAt', 'title']);
});

it('still applies story values to props without the prop attribute', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    $state = ComponentState::resolve($component, 'secondary-small');

    expect($state->props['meta'])->toBe(['role' => 'cancel'])
        ->and($state->props)->not->toHaveKey('internal')
        ->and($state->controlValues())->not->toHaveKey('meta');
});

it('layers control values over story values over defaults', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    $state = ComponentState::resolve($component, 'secondary-small', ['size' => '3'], []);

    expect($state->props['variant'])->toBe('secondary')
        ->and($state->props['size'])->toBe(3)
        ->and($state->props['disabled'])->toBeFalse()
        ->and($state->slots['default'])->toBe('Annuleren')
        ->and($state->slots['footer'])->toBe('Footer')
        ->and($state->changedProps())->toBe(['variant' => 'secondary', 'size' => 3]);
});

it('exposes metadata as an array for transports other than http', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    expect($component->toArray())
        ->toHaveKeys(['id', 'class', 'tag', 'name', 'category', 'description', 'renderable', 'props', 'stories', 'slots'])
        ->and($component->toArray()['props'][0])->toHaveKeys(['name', 'type', 'required', 'default', 'options', 'control']);
});

it('keeps a nullable prop null when its control is cleared', function (): void {
    $component = Storybook::find(str_replace('\\', '.', FixtureButton::class));

    $state = ComponentState::resolve($component, 'primary', ['href' => '']);

    expect($component->prop('href')->isNullable())->toBeTrue()
        ->and($state->props['href'])->toBeNull()
        ->and($state->changedProps())->not->toHaveKey('href');
});
