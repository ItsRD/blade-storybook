<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ItsRD\BladeStorybook\Http\Middleware\RollbackDatabaseChanges;

beforeEach(function (): void {
    config()->set('database.default', 'testing');
    config()->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);

    Schema::create('dogs', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
    });

    // Stands in for a story that accidentally creates a record, and reads it back while rendering.
    Route::get('/storybook-rollback-test', function (): string {
        DB::table('dogs')->insert(['name' => 'Bobbie']);
        DB::beginTransaction(); // left open on purpose

        return 'dogs: '.DB::table('dogs')->count();
    })->middleware(RollbackDatabaseChanges::class);
});

it('rolls back everything a storybook request writes', function (): void {
    $this->get('/storybook-rollback-test')->assertOk()->assertSee('dogs: 1');
    $this->get('/storybook-rollback-test')->assertOk()->assertSee('dogs: 1');

    expect(DB::table('dogs')->count())->toBe(0)
        ->and(DB::transactionLevel())->toBe(0);
});

it('keeps writes when the rollback is turned off', function (): void {
    config()->set('blade-storybook.rollback.enabled', false);

    $this->get('/storybook-rollback-test')->assertOk();

    expect(DB::table('dogs')->count())->toBe(1);
    DB::rollBack(0);
});

it('runs without a database', function (): void {
    config()->set('blade-storybook.rollback.connections', ['missing']);

    $this->get(route('blade-storybook.index'))->assertOk();
});

it('protects the storybook pages but not the assets', function (string $route, bool $protected): void {
    expect(in_array(RollbackDatabaseChanges::class, Route::getRoutes()->getByName($route)->gatherMiddleware(), true))
        ->toBe($protected);
})->with([
    ['blade-storybook.index', true],
    ['blade-storybook.preview', true],
    ['blade-storybook.asset', false],
]);
