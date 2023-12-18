<?php

/*
 * This file is part of exsyst/dynamic-class-generation-bundle.
 *
 * Copyright (C) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\DynamicClassGenerationBundle\Compiler;

class ClassResolver
{
    private string $cacheDirectory;
    private array $prefixes;

    public function __construct(string $cacheDirectory, array $prefixes)
    {
        $this->cacheDirectory = \rtrim($cacheDirectory, DIRECTORY_SEPARATOR);
        $this->prefixes = $prefixes;
    }

    public function resolve(string $class): ResolvedClassInfo
    {
        return $this->doResolve(\trim($class, '\\'), '', '', 0, $this->prefixes);
    }

    private function doResolve(string $class, string $goodPrefix, string $maxPrefix, int $maxPrefixLength, array $prefixes): ResolvedClassInfo
    {
        unset($prefixes["\0good"]);
        foreach ($prefixes as $prefix => $subPrefixes) {
            $prefixLength = \strlen($prefix);
            if (0 === \substr_compare($class, $prefix, $maxPrefixLength, $prefixLength)) {
                return $this->doResolve($class, ($subPrefixes["\0good"] ?? false) ? $maxPrefix.$prefix : $goodPrefix,
                    $maxPrefix.$prefix, $maxPrefixLength + $prefixLength, $subPrefixes);
            }
        }

        return $this->endResolve($class, $goodPrefix);
    }

    private function endResolve(string $class, string $goodPrefix): ResolvedClassInfo
    {
        $rest = \trim(\substr($class, \strlen($goodPrefix)), '\\');
        $lastSeparator = \strrpos($rest, '\\');
        $ns = (false === $lastSeparator) ? '' : \substr($rest, 0, $lastSeparator);
        $name = (false === $lastSeparator) ? $rest : \substr($rest, $lastSeparator + 1);
        $path = $this->cacheDirectory;
        $trimmedGoodPrefix = \trim($goodPrefix, '\\');
        if (!empty($trimmedGoodPrefix)) {
            $path .= DIRECTORY_SEPARATOR.\str_replace('\\', '-', $trimmedGoodPrefix);
        }
        if (!empty($ns)) {
            $path .= DIRECTORY_SEPARATOR.\str_replace('\\', '-', $ns);
        }
        $path .= DIRECTORY_SEPARATOR.$name.'.php';

        return new ResolvedClassInfo($class, $goodPrefix, $rest, $path);
    }
}
