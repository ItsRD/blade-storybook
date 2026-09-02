<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Metadata;

final readonly class SlotMetadata
{
    public function __construct(
        public string $name,
        public string $default = '',
    ) {}

    public function isDefaultSlot(): bool
    {
        return $this->name === 'default';
    }

    /**
     * @return array{name: string, default: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
