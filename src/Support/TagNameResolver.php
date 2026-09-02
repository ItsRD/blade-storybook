<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Support;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

/**
 * Resolves the Blade tag name for a component class. Aliases registered with
 * Blade::component() win, then registered component namespaces, then the
 * Laravel naming convention as a last resort.
 */
final class TagNameResolver
{
    /**
     * @param  class-string  $class
     */
    public function resolve(string $class): string
    {
        return $this->fromAlias($class)
            ?? $this->fromNamespace($class)
            ?? $this->fromConvention($class);
    }

    /**
     * @param  class-string  $class
     */
    private function fromAlias(string $class): ?string
    {
        /** @var array<string, class-string> $aliases */
        $aliases = Blade::getClassComponentAliases();

        $alias = array_search($class, $aliases, true);

        return $alias === false ? null : $alias;
    }

    /**
     * @param  class-string  $class
     */
    private function fromNamespace(string $class): ?string
    {
        /** @var array<string, string> $namespaces */
        $namespaces = Blade::getClassComponentNamespaces();

        foreach ($namespaces as $prefix => $namespace) {
            $namespace = trim($namespace, '\\').'\\';

            if (! str_starts_with($class, $namespace)) {
                continue;
            }

            return $prefix.'::'.$this->kebabPath(Str::after($class, $namespace));
        }

        return null;
    }

    /**
     * @param  class-string  $class
     */
    private function fromConvention(string $class): string
    {
        $root = 'App\\View\\Components\\';

        if (str_starts_with($class, $root)) {
            return $this->kebabPath(Str::after($class, $root));
        }

        return Str::kebab(class_basename($class));
    }

    private function kebabPath(string $relative): string
    {
        return collect(explode('\\', $relative))
            ->map(fn (string $segment): string => Str::kebab($segment))
            ->implode('.');
    }
}
