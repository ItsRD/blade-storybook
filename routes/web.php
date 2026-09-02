<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use ItsRD\BladeStorybook\Http\Controllers\AssetController;
use ItsRD\BladeStorybook\Http\Controllers\PreviewController;
use ItsRD\BladeStorybook\Http\Controllers\StorybookController;

Route::get('/', StorybookController::class)->name('index');
Route::get('/preview', PreviewController::class)->name('preview');
Route::get('/assets/{file}', AssetController::class)->name('asset')->where('file', 'storybook\.(css|js)');
