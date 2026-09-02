<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Support;

use Illuminate\Support\Str;
use ItsRD\BladeStorybook\Attributes\Prop;
use ItsRD\BladeStorybook\Attributes\Slot;
use ItsRD\BladeStorybook\Attributes\Story;
use ItsRD\BladeStorybook\Attributes\Storybook;
use ItsRD\BladeStorybook\Metadata\ComponentMetadata;
use ItsRD\BladeStorybook\Metadata\ControlType;
use ItsRD\BladeStorybook\Metadata\PropMetadata;
use ItsRD\BladeStorybook\Metadata\SlotMetadata;
use ItsRD\BladeStorybook\Metadata\StoryMetadata;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

/**
 * Turns a component class into metadata, using its attributes and reflection.
 */
final class ComponentParser
{
    private const CONTROLLABLE_TYPES = ['string', 'int', 'float', 'bool'];

    private const LITERAL_TYPES = ['string', 'int', 'float', 'bool', 'array', 'null', 'mixed', 'iterable'];

    public function __construct(private readonly TagNameResolver $tagNames) {}

    /**
     * @param  class-string  $class
     *
     * @throws \ReflectionException
     */
    public function parse(string $class): ?ComponentMetadata
    {
        $reflection = new ReflectionClass($class);

        $storybook = $this->attributeInstance($reflection, Storybook::class);

        if (! $storybook instanceof Storybook) {
            return null;
        }

        $tag = $this->tagNames->resolve($class);

        return new ComponentMetadata(
            class: $class,
            tag: $tag,
            name: $storybook->name ?? Str::headline(class_basename($class)),
            category: $storybook->category,
            description: $storybook->description,
            props: $this->props($reflection, declared: true),
            unmanagedProps: $this->props($reflection, declared: false),
            stories: $this->stories($reflection),
            slots: $this->slots($reflection),
        );
    }

    /**
     * Constructor parameters, split by whether they carry #[Prop]. Only
     * declared props end up in the interface and in the metadata; the rest
     * keep their own defaults and are only relevant for renderability.
     *
     * @param  ReflectionClass<object>  $reflection
     * @return list<PropMetadata>
     */
    private function props(ReflectionClass $reflection, bool $declared): array
    {
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $parameters = array_filter(
            $constructor->getParameters(),
            fn (ReflectionParameter $parameter): bool => $this->isDeclared($parameter) === $declared
        );

        return array_values(array_map(
            fn (ReflectionParameter $parameter): PropMetadata => $this->prop($parameter),
            $parameters
        ));
    }

    private function isDeclared(ReflectionParameter $parameter): bool
    {
        return $parameter->getAttributes(Prop::class) !== [];
    }

    private function prop(ReflectionParameter $parameter): PropMetadata
    {
        $attribute = $parameter->getAttributes(Prop::class)[0] ?? null;
        $prop = $attribute?->newInstance();

        $type = $parameter->getType();
        $typeNames = $this->typeNames($type);

        return new PropMetadata(
            name: $parameter->getName(),
            attributeName: Str::kebab($parameter->getName()),
            type: implode('|', $typeNames),
            hasDefault: $parameter->isDefaultValueAvailable(),
            default: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            options: $prop?->options,
            description: $prop?->description,
            control: $this->control($typeNames, $prop?->options),
            unsupportedReason: $this->unsupportedReason($typeNames),
        );
    }

    /**
     * @param  list<string>  $typeNames
     * @param  array<int|string, mixed>|null  $options
     */
    private function control(array $typeNames, ?array $options): ControlType
    {
        if ($options !== null && $options !== []) {
            return ControlType::Select;
        }

        $controllable = array_values(array_filter(
            $typeNames,
            fn (string $type): bool => in_array($type, self::CONTROLLABLE_TYPES, true)
        ));

        if ($controllable === []) {
            return ControlType::None;
        }

        if ($controllable === ['bool']) {
            return ControlType::Toggle;
        }

        if (array_diff($controllable, ['int', 'float']) === []) {
            return ControlType::Number;
        }

        return ControlType::Text;
    }

    /**
     * @param  list<string>  $typeNames
     */
    private function unsupportedReason(array $typeNames): ?string
    {
        $objects = array_values(array_filter(
            $typeNames,
            fn (string $type): bool => ! in_array($type, self::LITERAL_TYPES, true)
        ));

        if ($objects === []) {
            return null;
        }

        return sprintf('Type %s cannot be expressed as a literal value.', implode('|', $objects));
    }

    /**
     * @return list<string>
     */
    private function typeNames(?ReflectionType $type): array
    {
        if ($type === null) {
            return ['mixed'];
        }

        if ($type instanceof ReflectionNamedType) {
            $names = [strtolower($type->getName())];

            if ($type->allowsNull() && $type->getName() !== 'null') {
                $names[] = 'null';
            }

            return $names;
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            return array_merge(...array_map(
                fn (ReflectionType $inner): array => $this->typeNames($inner),
                $type->getTypes()
            ));
        }

        return ['mixed'];
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @return list<StoryMetadata>
     */
    private function stories(ReflectionClass $reflection): array
    {
        $stories = array_map(
            fn ($attribute): StoryMetadata => $this->story($attribute->newInstance()),
            $reflection->getAttributes(Story::class)
        );

        if ($stories === []) {
            return [new StoryMetadata(name: 'Default')];
        }

        return $stories;
    }

    private function story(Story $story): StoryMetadata
    {
        return new StoryMetadata(
            name: $story->name,
            props: $story->props,
            slots: array_map(fn (mixed $value): string => (string) $value, $story->slots),
        );
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @return list<SlotMetadata>
     */
    private function slots(ReflectionClass $reflection): array
    {
        return array_map(
            fn ($attribute): SlotMetadata => $this->slot($attribute->newInstance()),
            $reflection->getAttributes(Slot::class)
        );
    }

    private function slot(Slot $slot): SlotMetadata
    {
        return new SlotMetadata(name: $slot->name, default: $slot->default ?? '');
    }

    /**
     * @template TAttribute of object
     *
     * @param  ReflectionClass<object>  $reflection
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     */
    private function attributeInstance(ReflectionClass $reflection, string $attribute): ?object
    {
        $found = $reflection->getAttributes($attribute)[0] ?? null;

        return $found?->newInstance();
    }
}
