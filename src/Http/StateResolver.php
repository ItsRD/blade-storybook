<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Http;

use Illuminate\Http\Request;
use ItsRD\BladeStorybook\Metadata\ComponentMetadata;
use ItsRD\BladeStorybook\Rendering\ComponentState;

/**
 * Builds component state from the request, so the interface and the preview
 * iframe read the same shareable URL.
 */
final class StateResolver
{
    public function resolve(ComponentMetadata $component, Request $request): ComponentState
    {
        /** @var array<string, mixed> $props */
        $props = $request->array('props');

        /** @var array<string, mixed> $slots */
        $slots = $request->array('slots');

        $story = $request->string('story')->toString();
        $story = ! empty($story) ? $story : $component->stories()->first()?->id();

        return ComponentState::resolve($component, $story, $props, $slots);
    }
}
