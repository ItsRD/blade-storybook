<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 */
final class Dog extends Model
{
    protected $guarded = ['*'];
}
