<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Data;

use ItsRD\BladeStorybook\Tests\Fixtures\Models\Dog;
use ItsRD\BladeStorybook\Tests\Fixtures\Models\User;

final readonly class UserData
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public string $name,
        public string $role,
        public ?AddressData $address = null,
        public array $permissions = [],
    ) {}

    /**
     * Built from a model, like DTOs in an application usually are. Dog names
     * end up as permissions to keep the fixture small.
     */
    public static function fromModel(User $user): self
    {
        return new self(
            name: $user->name,
            role: $user->role,
            address: $user->city === null ? null : new AddressData($user->city),
            permissions: $user->dogs->map(fn (Dog $dog): string => $dog->name)->values()->all(),
        );
    }
}
