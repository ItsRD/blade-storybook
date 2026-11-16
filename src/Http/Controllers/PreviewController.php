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
        $error = $state->blockingReason();

        if ($error === null) {
            try {
                $html = $componentRenderer->render($state);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->views->make('blade-storybook::preview', [
            'component' => $component,
            'html' => $html,
            'width' => $state->width,
            'error' => $error,
            'head' => $this->livewireAssets('styles').$this->markup(config('blade-storybook.preview.head')),
            'scripts' => $this->livewireAssets('scripts').$this->markup(config('blade-storybook.preview.scripts')),
        ]);
    }

    /**
     * Resolves a configured view name or raw HTML string into markup.
     */
    private function markup(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            return '';
        }

        if (! str_contains($value, '<') && $this->views->exists($value)) {
            return $this->views->make($value)->render();
        }

        return $value;
    }

    /**
     * Livewire ships Alpine, but only injects it on pages that render a
     * Livewire component. The preview usually does not, so add it here.
     *
     * @param  'styles'|'scripts'  $type
     */
    private function livewireAssets(string $type): string
    {
        $assets = 'Livewire\Mechanisms\FrontendAssets\FrontendAssets';

        if (! config('blade-storybook.preview.livewire', true) || ! class_exists($assets)) {
            return '';
        }

        return (string) $assets::$type();
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
