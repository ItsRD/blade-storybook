<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Support;

/**
 * Reads the fully qualified class name out of a PHP file without loading it.
 */
final class ClassFileResolver
{
    public function resolve(string $path): ?string
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $tokens = token_get_all($contents);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (! is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = $this->readName($tokens, $i + 1);

                continue;
            }

            if ($token[0] === T_CLASS) {
                if ($this->isClassKeywordUsedAsConstant($tokens, $i)) {
                    continue;
                }

                $class = $this->readName($tokens, $i + 1);

                if ($class === null) {
                    return null;
                }

                return $namespace === null ? $class : $namespace.'\\'.$class;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    private function readName(array $tokens, int $offset): ?string
    {
        $name = '';
        $count = count($tokens);

        for ($i = $offset; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                if ($name !== '') {
                    break;
                }

                continue;
            }

            if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR], true)) {
                $name .= $token[1];

                continue;
            }

            break;
        }

        return $name === '' ? null : trim($name, '\\');
    }

    /**
     * Skips `Foo::class` and anonymous classes.
     *
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    private function isClassKeywordUsedAsConstant(array $tokens, int $index): bool
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            return is_array($token) && $token[0] === T_DOUBLE_COLON;
        }

        return false;
    }
}
