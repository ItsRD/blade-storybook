<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Metadata;

final readonly class PropMetadata
{
    /**
     * @param  array<int|string, mixed>|null  $options
     * @param  string|null  $unsupportedReason  Set when the value cannot be expressed as a literal, such as a model or a collection.
     */
    public function __construct(
        public string      $name,
        public string      $attributeName,
        public string      $type,
        public bool        $hasDefault,
        public mixed       $default,
        public ?array      $options,
        public ?string     $description,
        public ControlType $control,
        public ?string     $unsupportedReason = null,
    ) {}

    /**
     * Whether the value can be expressed as a literal, either through a
     * control or through a story.
     */
    public function isLiteral(): bool
    {
        return $this->unsupportedReason === null;
    }

    public function isControllable(): bool
    {
        return $this->isLiteral() && $this->control !== ControlType::None;
    }

    public function isNullable(): bool
    {
        return in_array('null', explode('|', $this->type), true);
    }

    public function isRequired(): bool
    {
        return ! $this->hasDefault;
    }

    /**
     * Options normalised to value => label.
     *
     * @return array<string, string>
     */
    public function selectOptions(): array
    {
        $options = [];

        foreach ($this->options ?? [] as $key => $label) {
            $value = is_int($key) ? $label : $key;
            $options[(string) $value] = (string) $label;
        }

        return $options;
    }

    /**
     * @return array{name: string, attribute_name: string, type: string, required: bool, default: mixed, options: array<int|string, mixed>|null, description: string|null, control: string, unsupported_reason: string|null}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'attribute_name' => $this->attributeName,
            'type' => $this->type,
            'required' => $this->isRequired(),
            'default' => $this->hasDefault ? $this->default : null,
            'options' => $this->options,
            'description' => $this->description,
            'control' => $this->control->value,
            'unsupported_reason' => $this->unsupportedReason,
        ];
    }
}
