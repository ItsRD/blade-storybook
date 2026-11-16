<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Metadata;

use Illuminate\Support\Collection;

final readonly class ComponentMetadata
{
    /**
     * @param  class-string  $class
     * @param  list<PropMetadata>  $props  Constructor parameters marked with #[Prop].
     * @param  list<PropMetadata>  $unmanagedProps  Constructor parameters without #[Prop], kept out of the interface and the metadata.
     * @param  list<StoryMetadata>  $stories
     * @param  list<SlotMetadata>  $slots
     */
    public function __construct(
        public string $class,
        public string $tag,
        public string $name,
        public string $category,
        public ?string $description,
        public array $props,
        public array $unmanagedProps,
        public array $stories,
        public array $slots,
        public ?string $width = null,
    ) {}

    /**
     * Stable, URL safe identifier for this component.
     */
    public function id(): string
    {
        return str_replace('\\', '.', $this->class);
    }

    /**
     * @return Collection<int, PropMetadata>
     */
    public function props(): Collection
    {
        return collect($this->props);
    }

    /**
     * Constructor parameters without #[Prop]. They keep their own default and
     * are only settable from a story.
     *
     * @return Collection<int, PropMetadata>
     */
    public function unmanagedProps(): Collection
    {
        return collect($this->unmanagedProps);
    }

    /**
     * @return Collection<int, PropMetadata>
     */
    public function allProps(): Collection
    {
        return $this->props()->concat($this->unmanagedProps);
    }

    /**
     * @return Collection<int, SlotMetadata>
     */
    public function slots(): Collection
    {
        return collect($this->slots);
    }

    /**
     * @return Collection<int, StoryMetadata>
     */
    public function stories(): Collection
    {
        return collect($this->stories);
    }

    public function prop(string $name): ?PropMetadata
    {
        return $this->allProps()->firstWhere('name', $name);
    }

    public function story(?string $id): ?StoryMetadata
    {
        if ($id === null) {
            return null;
        }

        return $this->stories()->first(fn (StoryMetadata $story): bool => $story->id() === $id);
    }

    /**
     * Props without a value the storybook can supply, which makes the
     * component impossible to render: a required prop that needs an object,
     * or a required constructor parameter that was never marked with #[Prop].
     * A story that sets such a prop, inline with `new` or through its $using
     * method, supplies it and unblocks the component.
     *
     * @param  array<string, mixed>  $provided  Prop values a story supplies.
     * @return Collection<int, PropMetadata>
     */
    public function blockingProps(array $provided = []): Collection
    {
        return $this->allProps()
            ->filter(fn (PropMetadata $prop): bool => $prop->isRequired())
            ->filter(fn (PropMetadata $prop): bool => ! $prop->isLiteral() || ! $this->isDeclared($prop))
            ->reject(fn (PropMetadata $prop): bool => array_key_exists($prop->name, $provided))
            ->values();
    }

    /**
     * Whether at least one story can be rendered. Runs every $using method.
     */
    public function isRenderable(): bool
    {
        return $this->renderableStories()->isNotEmpty();
    }

    /**
     * The stories that can be rendered. Runs every $using method; a story
     * whose method fails counts as not renderable.
     *
     * @return Collection<int, StoryMetadata>
     */
    public function renderableStories(): Collection
    {
        return $this->stories()
            ->filter(function (StoryMetadata $story): bool {
                try {
                    return $this->blockingProps($story->resolveProps())->isEmpty();
                } catch (\Throwable) {
                    return false;
                }
            })
            ->values();
    }

    public function isDeclared(PropMetadata $prop): bool
    {
        return $this->props()->contains(fn (PropMetadata $declared): bool => $declared->name === $prop->name);
    }

    /**
     * @return array{id: string, class: string, tag: string, name: string, category: string, description: string|null, renderable: bool, props: list<array<string, mixed>>, stories: list<array<string, mixed>>, slots: list<array<string, string>>, width: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'class' => $this->class,
            'tag' => $this->tag,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'renderable' => $this->isRenderable(),
            'props' => $this->props()->map->toArray()->all(),
            'stories' => $this->stories()->map->toArray()->all(),
            'slots' => $this->slots()->map->toArray()->all(),
            'width' => $this->width,
        ];
    }
}
