<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook;

use Illuminate\Support\Collection;
use ItsRD\BladeStorybook\Metadata\ComponentMetadata;
use ItsRD\BladeStorybook\Support\ComponentParser;
use ItsRD\BladeStorybook\Support\ComponentScanner;

/**
 * The metadata layer. Deliberately free of any HTTP concerns, so the same data
 * can later be served from an MCP server.
 */
final class StorybookRegistry
{
    /** @var Collection<string, ComponentMetadata>|null */
    private ?Collection $components = null;

    public function __construct(
        private readonly ComponentScanner $componentScanner,
        private readonly ComponentParser $componentParser,
    ) {}

    /**
     * @return Collection<string, ComponentMetadata>
     */
    public function all(): Collection
    {
        return $this->components ??= $this->build();
    }

    /**
     * Components grouped by category, both sorted alphabetically.
     *
     * @return Collection<int|string, Collection<int, ComponentMetadata>>
     */
    public function categories(): Collection
    {
        return $this->all()
            ->groupBy(fn (ComponentMetadata $component): string => $component->category)
            ->sortKeys();
    }

    public function find(string $id): ?ComponentMetadata
    {
        return $this->all()->get($id);
    }

    public function first(): ?ComponentMetadata
    {
        return $this->all()->first();
    }

    public function flush(): void
    {
        $this->components = null;
    }

    /**
     * @return Collection<string, ComponentMetadata>
     */
    private function build(): Collection
    {
        /** @var list<string> $paths */
        $paths = config()->array('blade-storybook.paths');

        return collect($this->componentScanner->scan($paths))
            ->map(fn (string $class): ?ComponentMetadata => $this->componentParser->parse($class))
            ->filter()
            ->sortBy([
                ['category', 'asc'],
                ['name', 'asc'],
            ])
            ->keyBy(fn (ComponentMetadata $component): string => $component->id());
    }
}
