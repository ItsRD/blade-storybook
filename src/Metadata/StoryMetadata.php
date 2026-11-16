<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Metadata;

use Illuminate\Support\Str;
use InvalidArgumentException;
use UnexpectedValueException;

final readonly class StoryMetadata
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $slots
     * @param  array<int, mixed>|null  $using  A [class, method] pair that returns extra props when the story is shown.
     */
    public function __construct(
        public string $name,
        public array $props = [],
        public array $slots = [],
        public ?array $using = null,
        public ?string $width = null,
    ) {}

    public function id(): string
    {
        return Str::slug($this->name) ?: md5($this->name);
    }

    /**
     * The inline props merged with whatever the $using method returns. The
     * method runs on every call, so call this once per state.
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException When $using does not point at a static method.
     * @throws UnexpectedValueException When the method does not return an array.
     */
    public function resolveProps(): array
    {
        if ($this->using === null) {
            return $this->props;
        }

        $callable = $this->callable();
        $props = $callable();

        if (! is_array($props)) {
            throw new UnexpectedValueException(sprintf(
                '%s must return an array of props, %s returned.',
                $this->usingLabel(),
                get_debug_type($props)
            ));
        }

        /** @var array<string, mixed> $props */
        return array_merge($this->props, $props);
    }

    public function usingLabel(): ?string
    {
        if ($this->using === null) {
            return null;
        }

        $class = $this->using[0] ?? '?';
        $method = $this->using[1] ?? '?';

        return sprintf('%s::%s()', is_string($class) ? $class : get_debug_type($class), is_string($method) ? $method : '?');
    }

    private function callable(): callable
    {
        $class = $this->using[0] ?? null;
        $method = $this->using[1] ?? null;

        if (count($this->using ?? []) !== 2 || ! is_string($class) || ! is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Story "%s" has an invalid using value. Expected [SomeStories::class, \'method\'].',
                $this->name
            ));
        }

        if (! class_exists($class) || ! method_exists($class, $method)) {
            throw new InvalidArgumentException(sprintf('Story "%s" uses %s, which does not exist.', $this->name, $this->usingLabel()));
        }

        $callable = [$class, $method];

        if (! is_callable($callable) || ! (new \ReflectionMethod($class, $method))->isStatic()) {
            throw new InvalidArgumentException(sprintf('Story "%s" uses %s, which must be public and static.', $this->name, $this->usingLabel()));
        }

        return $callable;
    }

    /**
     * @return array{id: string, name: string, props: array<string, mixed>, slots: array<string, string>, using: string|null, width: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name,
            'props' => $this->props,
            'slots' => $this->slots,
            'using' => $this->usingLabel(),
            'width' => $this->width,
        ];
    }
}
