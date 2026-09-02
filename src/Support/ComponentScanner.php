<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Support;

use Illuminate\View\Component;
use ItsRD\BladeStorybook\Attributes\Storybook;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Finds class-based Blade components marked with #[Storybook] in the
 * configured directories.
 */
final readonly class ComponentScanner
{
    public function __construct(private ClassFileResolver $classFileResolver) {}

    /**
     * @param  list<string>  $paths
     * @return list<class-string>
     */
    public function scan(array $paths): array
    {
        $directories = array_values(array_filter($paths, 'is_dir'));

        if ($directories === []) {
            return [];
        }

        $classes = [];

        foreach (Finder::create()->files()->in($directories)->name('*.php') as $file) {
            $class = $this->classFileResolver->resolve($file->getRealPath());

            if ($class === null || ! $this->isStorybookComponent($class)) {
                continue;
            }

            $classes[$class] = $class;
        }

        return array_values($classes);
    }

    private function isStorybookComponent(string $class): bool
    {
        try {
            if (! class_exists($class)) {
                return false;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || ! $reflection->isSubclassOf(Component::class)) {
                return false;
            }

            return $reflection->getAttributes(Storybook::class) !== [];
        } catch (Throwable) {
            return false;
        }
    }
}
