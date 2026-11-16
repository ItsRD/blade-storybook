<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ItsRD\BladeStorybook\Tests\Fixtures\Factories\UserFactory;

/**
 * Fully guarded, like a real application model, so a story cannot just fill it.
 *
 * @property string $name
 * @property string $role
 * @property string|null $city
 */
final class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $guarded = ['*'];

    /**
     * @return HasMany<Dog, $this>
     */
    public function dogs(): HasMany
    {
        return $this->hasMany(Dog::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
