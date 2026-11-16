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
            ->filter(fn (PropMetadata $prop): bool => array_key_exists($prop->name, $componentState->props))
            ->map(fn (PropMetadata $prop): string => sprintf(':%s="$props[\'%s\']"', $prop->attributeName, $prop->name))
            ->implode(' ');

        $slots = collect($componentState->slots)
            ->filter(fn (string $content, string $name): bool => $name !== 'default')
            ->map(fn (string $content, string $name): string => sprintf('<x-slot:%s>%s</x-slot>', $name, $this->slotContent($componentState, $name)))
            ->implode('');

        $default = array_key_exists('default', $componentState->slots) ? $this->slotContent($componentState, 'default') : '';

        return sprintf('<x-%s %s>%s%s</x-%s>', $component->tag, $attributes, $slots, $default, $component->tag);
    }

    /**
     * Code-defined slot content is inlined so it compiles as Blade (nested
     * components, directives). Anything else is echoed as escaped text.
     */
    private function slotContent(ComponentState $componentState, string $name): string
    {
        if (in_array($name, $componentState->bladeSlots, true)) {
            // Newlines keep the closing slot directive from fusing with the
            // compiled output of a component at the end of the content.
            return "\n".$componentState->slots[$name]."\n";
        }

        return sprintf('{{ $slots[\'%s\'] }}', $name);
    }
}
