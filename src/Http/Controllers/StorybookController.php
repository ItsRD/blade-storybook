<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use ItsRD\BladeStorybook\Http\StateResolver;
use ItsRD\BladeStorybook\Metadata\ComponentMetadata;
use ItsRD\BladeStorybook\Metadata\StoryMetadata;
use ItsRD\BladeStorybook\Rendering\ComponentState;
use ItsRD\BladeStorybook\StorybookRegistry;

final readonly class StorybookController
{
    public function __construct(private ViewFactory $views) {}

    public function __invoke(Request $request, StorybookRegistry $registry, StateResolver $states): View
    {
        $component = $registry->find($request->string('component', '')->toString()) ?? $registry->first();

        $state = $component === null ? null : $states->resolve($component, $request);

        return $this->views->make('blade-storybook::index', [
            'categories' => $registry->categories(),
            'component' => $component,
            'state' => $state,
            'viewports' => config()->array('blade-storybook.viewports', []),
            'payload' => $component === null || $state === null ? null : $this->payload($component, $state),
        ]);
    }

    /**
     * Everything the interface needs to drive the controls and the preview.
     *
     * @return array<string, mixed>
     */
    private function payload(ComponentMetadata $component, ComponentState $state): array
    {
        return [
            'previewUrl' => route('blade-storybook.preview'),
            'shellUrl' => route('blade-storybook.index'),
            'component' => $component->id(),
            'story' => $state->storyId,
            'props' => (object) $state->controlValues(),
            'slots' => (object) $state->slots,
            'stories' => $component->stories()
                ->mapWithKeys(function (StoryMetadata $story) use ($component): array {
                    $storyState = ComponentState::resolve($component, $story->id());

                    return [$story->id() => [
                        'props' => (object) $storyState->controlValues(),
                        'slots' => (object) $storyState->slots,
                    ]];
                })
                ->all(),
        ];
    }
}
