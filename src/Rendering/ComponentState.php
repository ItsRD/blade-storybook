<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Rendering;

use ItsRD\BladeStorybook\Metadata\ComponentMetadata;
use ItsRD\BladeStorybook\Metadata\ControlType;
use ItsRD\BladeStorybook\Metadata\PropMetadata;
use ItsRD\BladeStorybook\Metadata\SlotMetadata;
use Throwable;

/**
 * The values a component is previewed with: defaults, overridden by the
 * selected story, overridden by whatever the controls sent along.
 */
final readonly class ComponentState
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $slots
     * @param  string|null  $storyError  Set when the story's $using method failed.
     * @param  string|null  $width  Width of the box the component is previewed in; null is full width.
     * @param  string|null  $defaultWidth  The width from the story or #[Storybook], before the control changed it.
     * @param  list<string>  $bladeSlots  Slots whose value comes from code (a story or #[Slot] default) and is compiled as Blade.
     */
    private function __construct(
        public ComponentMetadata $component,
        public ?string $storyId,
        public array $props,
        public array $slots,
        public ?string $storyError = null,
        public ?string $width = null,
        public ?string $defaultWidth = null,
        public array $bladeSlots = [],
    ) {}

    /**
     * @param  array<string, mixed>  $propInput
     * @param  array<string, mixed>  $slotInput
     * @param  string|null  $widthInput  The width control's value. Null when it was not sent; an empty string clears the width.
     */
    public static function resolve(
        ComponentMetadata $component,
        ?string $storyId = null,
        array $propInput = [],
        array $slotInput = [],
        ?string $widthInput = null,
    ): self {
        $story = $component->story($storyId);
        $storySlots = $story === null ? [] : $story->slots;
        $storyProps = [];
        $storyError = null;

        if ($story !== null) {
            try {
                $storyProps = $story->resolveProps();
            } catch (Throwable $exception) {
                $storyProps = $story->props;
                $storyError = sprintf(
                    'Story "%s" could not build its props: %s (%s:%d)',
                    $story->name,
                    $exception->getMessage(),
                    basename($exception->getFile()),
                    $exception->getLine()
                );
            }
        }

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
        $bladeSlots = [];

        foreach ($component->slots as $slot) {
            $slots[$slot->name] = self::slotValue($slot, $storySlots, $slotInput);

            // Only content defined in code is compiled as Blade, so nested
            // components render. Text edited in the controls arrives through the
            // URL and stays plain text, otherwise a shared link could run PHP.
            if ($slots[$slot->name] === self::slotValue($slot, $storySlots, [])) {
                $bladeSlots[] = $slot->name;
            }
        }

        $defaultWidth = $story->width ?? $component->width;

        return new self(
            component: $component,
            storyId: $story?->id(),
            props: $props,
            slots: $slots,
            storyError: $storyError,
            width: $widthInput === null ? $defaultWidth : PreviewWidth::normalize($widthInput),
            defaultWidth: $defaultWidth,
            bladeSlots: $bladeSlots,
        );
    }

    /**
     * Why this state cannot be rendered, or null when it can.
     */
    public function blockingReason(): ?string
    {
        if ($this->storyError !== null) {
            return $this->storyError;
        }

        $blocking = $this->component->blockingProps($this->props);

        if ($blocking->isEmpty()) {
            return null;
        }

        $reason = sprintf(
            'This story does not provide values for: %s. Set them in a #[Story], inline or through a using method.',
            $blocking->map->name->implode(', ')
        );

        $alternatives = $this->component->renderableStories();

        if ($alternatives->isNotEmpty()) {
            $reason .= sprintf(' Stories that can be previewed: %s.', $alternatives->map->name->implode(', '));
        }

        return $reason;
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
        $isEmpty = $value === '' || $value === null;

        if ($isEmpty && $prop->isNullable()) {
            return null;
        }

        if ($isEmpty && $prop->usesEmptyTextForFalse()) {
            return false;
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

            if ($value === false && $prop->usesEmptyTextForFalse()) {
                $values[$prop->name] = '';

                continue;
            }

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
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if ($value === false && $this->component->prop($name)?->usesEmptyTextForFalse()) {
                $query['props'][$name] = '';

                continue;
            }

            $query['props'][$name] = is_bool($value) ? ($value ? '1' : '0') : $value;
        }

        foreach ($this->slots as $name => $value) {
            if (in_array($name, $this->bladeSlots, true)) {
                continue;
            }

            $query['slots'][$name] = $value;
        }

        if ($this->width !== $this->defaultWidth) {
            $query['width'] = $this->width ?? '';
        }

        return $query;
    }
}
