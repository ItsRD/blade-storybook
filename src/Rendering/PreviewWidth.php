<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Rendering;

/**
 * The width of the box the component is previewed in, for components that
 * size themselves to their parent.
 */
final class PreviewWidth
{
    /**
     * A CSS width, or null for full width. Bare numbers are pixels; anything
     * other than a plain length is rejected, since the value ends up in a
     * style attribute.
     */
    public static function normalize(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = strtolower(str_replace(' ', '', $value));

        if (preg_match('/^(\d+(?:\.\d+)?)(px|%|rem|em|vw|ch)?$/', $value, $matches) !== 1 || (float) $matches[1] <= 0) {
            return null;
        }

        $unit = $matches[2] ?? '';

        return $matches[1].($unit === '' ? 'px' : $unit);
    }
}
