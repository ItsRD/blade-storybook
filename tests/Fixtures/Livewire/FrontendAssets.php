<?php

declare(strict_types=1);

namespace Livewire\Mechanisms\FrontendAssets;

/**
 * Stands in for Livewire's asset helper, which this package does not require.
 * Only loaded by the test that needs it.
 */
final class FrontendAssets
{
    public static function styles(): string
    {
        return '<style data-livewire-styles-stub></style>';
    }

    public static function scripts(): string
    {
        return '<script data-livewire-scripts-stub></script>';
    }
}
