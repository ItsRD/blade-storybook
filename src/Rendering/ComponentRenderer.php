<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Rendering;

use Illuminate\Support\Facades\Blade;
use ItsRD\BladeStorybook\Metadata\PropMetadata;

/**
 * Renders a component through Blade, exactly as the application would.
 */
final class ComponentRenderer
{
    public function render(ComponentState $state): string
    {
        return Blade::render(
            $this->template($state),
            ['props' => $state->props, 'slots' => $state->slots],
            deleteCachedView: true
        );
    }

    private function template(ComponentState $componentState): string
    {
        $component = $componentState->component;

        $attributes = $component->allProps()
            ->filter(fn (PropMetadata $prop): bool => $prop->isLiteral() && array_key_exists($prop->name, $componentState->props))
            ->map(fn (PropMetadata $prop): string => sprintf(':%s="$props[\'%s\']"', $prop->attributeName, $prop->name))
            ->implode(' ');

        $slots = collect($componentState->slots)
            ->filter(fn (string $content, string $name): bool => $name !== 'default')
            ->map(fn (string $content, string $name): string => sprintf('<x-slot:%s>{{ $slots[\'%s\'] }}</x-slot>', $name, $name))
            ->implode('');

        $default = array_key_exists('default', $componentState->slots) ? '{{ $slots[\'default\'] }}' : '';

        return sprintf('<x-%s %s>%s%s</x-%s>', $component->tag, $attributes, $slots, $default, $component->tag);
    }
}
