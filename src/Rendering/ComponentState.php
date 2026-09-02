<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Rendering;

use ItsRD\BladeStorybook\Metadata\ComponentMetadata;
use ItsRD\BladeStorybook\Metadata\ControlType;
use ItsRD\BladeStorybook\Metadata\PropMetadata;
use ItsRD\BladeStorybook\Metadata\SlotMetadata;

/**
 * The values a component is previewed with: defaults, overridden by the
 * selected story, overridden by whatever the controls sent along.
 */
final readonly class ComponentState
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $slots
     */
    private function __construct(
        public ComponentMetadata $component,
        public ?string           $storyId,
        public array             $props,
        public array             $slots,
    ) {}

    /**
     * @param  array<string, mixed>  $propInput
     * @param  array<string, mixed>  $slotInput
     */
    public static function resolve(
        ComponentMetadata $component,
        ?string $storyId = null,
        array $propInput = [],
        array $slotInput = [],
    ): self {
        $story = $component->story($storyId);
        $storyProps = $story === null ? [] : $story->props;
        $storySlots = $story === null ? [] : $story->slots;

        $props = [];

        foreach ($component->props as $prop) {
            $value = self::propValue($prop, $storyProps, $propInput);

            if ($value !== null || $prop->hasDefault) {
                $props[$prop->name] = $value;
            }
        }

        foreach ($component->unmanagedProps as $prop) {
            if (array_key_exists($prop->name, $storyProps)) {
                $props[$prop->name] = $storyProps[$prop->name];
            }
        }

        $slots = [];

        foreach ($component->slots as $slot) {
            $slots[$slot->name] = self::slotValue($slot, $storySlots, $slotInput);
        }

        return new self(
            component: $component,
            storyId: $story?->id(),
            props: $props,
            slots: $slots,
        );
    }

    /**
     * @param  array<string, mixed>  $storyProps
     * @param  array<string, mixed>  $input
     */
    private static function propValue(PropMetadata $prop, array $storyProps, array $input): mixed
    {
        if ($prop->isControllable() && array_key_exists($prop->name, $input)) {
            return self::cast($prop, $input[$prop->name]);
        }

        if (array_key_exists($prop->name, $storyProps)) {
            return $storyProps[$prop->name];
        }

        return $prop->hasDefault ? $prop->default : null;
    }

    /**
     * @param  array<string, string>  $storySlots
     * @param  array<string, mixed>  $input
     */
    private static function slotValue(SlotMetadata $slot, array $storySlots, array $input): string
    {
        if (array_key_exists($slot->name, $input)) {
            return (string) $input[$slot->name];
        }

        return (string) ($storySlots[$slot->name] ?? $slot->default);
    }

    private static function cast(PropMetadata $prop, mixed $value): mixed
    {
        if ($value === '' && $prop->isNullable()) {
            return null;
        }

        return match ($prop->control) {
            ControlType::Toggle => filter_var($value, FILTER_VALIDATE_BOOL),
            ControlType::Number => str_contains((string) $value, '.') ? (float) $value : (int) $value,
            default => (string) $value,
        };
    }

    /**
     * Values for the props that have a generated control, which is what the
     * interface sends back and forth.
     *
     * @return array<string, string|int|float|bool>
     */
    public function controlValues(): array
    {
        $values = [];

        foreach ($this->component->props as $prop) {
            if (! $prop->isControllable() || ! array_key_exists($prop->name, $this->props)) {
                continue;
            }

            $value = $this->props[$prop->name];

            $values[$prop->name] = is_bool($value) || is_int($value) || is_float($value) || is_string($value)
                ? $value
                : '';
        }

        return $values;
    }

    /**
     * The prop values that differ from the component defaults, which is what
     * ends up in the URL and in the generated Blade snippet.
     *
     * @return array<string, mixed>
     */
    public function changedProps(): array
    {
        $changed = [];

        foreach ($this->component->props as $prop) {
            if (! array_key_exists($prop->name, $this->props)) {
                continue;
            }

            if ($prop->hasDefault && $this->props[$prop->name] === $prop->default) {
                continue;
            }

            $changed[$prop->name] = $this->props[$prop->name];
        }

        return $changed;
    }

    /**
     * @return array<string, mixed>
     */
    public function query(): array
    {
        $query = ['component' => $this->component->id()];

        if ($this->storyId !== null) {
            $query['story'] = $this->storyId;
        }

        foreach ($this->changedProps() as $name => $value) {
            if (is_array($value)) {
                continue;
            }

            $query['props'][$name] = is_bool($value) ? ($value ? '1' : '0') : $value;
        }

        foreach ($this->slots as $name => $value) {
            $query['slots'][$name] = $value;
        }

        return $query;
    }
}
