<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ItsRD\BladeStorybook\Tests\Fixtures\Models\User;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => 1,
            'name' => 'Fixture user',
            'role' => 'member',
            'city' => null,
        ];
    }

    public function admin(): self
    {
        return $this->state(['role' => 'admin']);
    }
}
