<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use ItsRD\BladeStorybook\Http\StateResolver;
use ItsRD\BladeStorybook\Rendering\ComponentRenderer;
use ItsRD\BladeStorybook\StorybookRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final readonly class PreviewController
{
    public function __construct(private ViewFactory $views) {}

    public function __invoke(
        Request $request,
        StorybookRegistry $registry,
        StateResolver $stateResolver,
        ComponentRenderer $componentRenderer,
    ): View {
        $component = $registry->find($request->string('component')->toString());

        if ($component === null) {
            throw new NotFoundHttpException('Unknown storybook component.');
        }

        $this->silenceDebugbar();

        $state = $stateResolver->resolve($component, $request);
        $html = null;
        $error = null;

        if ($component->isRenderable()) {
            try {
                $html = $componentRenderer->render($state);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        } else {
            $error = sprintf(
                'This component needs values that cannot be expressed in the storybook: %s.',
                $component->blockingProps()->map->name->implode(', ')
            );
        }

        return $this->views->make('blade-storybook::preview', [
            'component' => $component,
            'html' => $html,
            'error' => $error,
        ]);
    }

    /**
     * Keeps developer toolbars out of the preview iframe.
     */
    private function silenceDebugbar(): void
    {
        if (! app()->bound('debugbar')) {
            return;
        }

        $debugbar = app('debugbar');

        if (method_exists($debugbar, 'disable')) {
            $debugbar->disable();
        }
    }
}
