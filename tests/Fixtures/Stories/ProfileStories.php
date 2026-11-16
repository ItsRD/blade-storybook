<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Stories;

use ItsRD\BladeStorybook\Tests\Fixtures\Data\UserData;
use ItsRD\BladeStorybook\Tests\Fixtures\Models\Dog;
use ItsRD\BladeStorybook\Tests\Fixtures\Models\User;
use RuntimeException;

/**
 * Story data built the way a test would: factories with make(), relations
 * set by hand, no database.
 */
final class ProfileStories
{
    /**
     * @return array<string, mixed>
     */
    public static function admin(): array
    {
        $user = User::factory()->admin()->make(['name' => 'Henk', 'city' => 'Leiden']);

        $user->setRelation('dogs', collect([
            (new Dog)->forceFill(['name' => 'Bobbie']),
            (new Dog)->forceFill(['name' => 'Saar']),
        ]));

        return ['user' => UserData::fromModel($user), 'compact' => true];
    }

    /**
     * @return array<string, mixed>
     */
    public static function broken(): array
    {
        throw new RuntimeException('Factory exploded');
    }

    public static function notAnArray(): string
    {
        return 'nope';
    }
}
