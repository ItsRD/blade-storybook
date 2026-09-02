<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Metadata;

use Illuminate\Support\Str;

final readonly class StoryMetadata
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $slots
     */
    public function __construct(
        public string $name,
        public array $props = [],
        public array $slots = [],
    ) {}

    public function id(): string
    {
        return Str::slug($this->name) ?: md5($this->name);
    }

    /**
     * @return array{id: string, name: string, props: array<string, mixed>, slots: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name,
            'props' => $this->props,
            'slots' => $this->slots,
        ];
    }
}
