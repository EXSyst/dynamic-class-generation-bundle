<?php

/*
 * This file is part of exsyst/dynamic-class-generation-bundle.
 *
 * Copyright (C) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\DynamicClassGenerationBundle;

use EXSyst\DynamicClassGenerationBundle\Compiler\ClassGeneratorInterface;
use EXSyst\DynamicClassGenerationBundle\Compiler\ClassInvalidatorInterface;
use EXSyst\DynamicClassGenerationBundle\Compiler\ClassResolver;
use EXSyst\DynamicClassGenerationBundle\Compiler\ResolvedClassInfo;

class DynamicClassLoader
{
    private ClassResolver $resolver;
    private ClassGeneratorInterface $generator;
    private ClassInvalidatorInterface $invalidator;

    public function __construct(ClassResolver $resolver, ClassGeneratorInterface $generator, ClassInvalidatorInterface $invalidator)
    {
        $this->resolver = $resolver;
        $this->generator = $generator;
        $this->invalidator = $invalidator;
    }

    public function resolve(string $class): ResolvedClassInfo
    {
        return $this->resolver->resolve($class);
    }

    public function load(string $class): void
    {
        $resolvedClass = $this->resolve($class);
        $this->generateIfNeeded($resolvedClass);

        require_once $resolvedClass->getPath();
    }

    public function generateIfNeeded(ResolvedClassInfo $class): bool
    {
        if (\is_file($class->getPath())) {
            return true;
        }
        $directory = \dirname($class->getPath());
        if (!\is_dir($directory)) {
            \mkdir($directory, 0777, true);
        }
        if (!$this->generator->generate($class)) {
            return false;
        }
        if (\function_exists('opcache_invalidate')) {
            \opcache_invalidate($class->getPath(), true);
        }

        return true;
    }

    public function invalidate(string $class): void
    {
        $resolvedClass = $this->resolve($class);
        $this->invalidateResolved($resolvedClass);
    }

    public function invalidateResolved(ResolvedClassInfo $class): void
    {
        if (!\is_file($class->getPath())) {
            return;
        }

        \unlink($class->getPath());
        $this->invalidator->invalidate($class);
    }

    public function register(): void
    {
        \spl_autoload_register([$this, 'load']);
    }

    public function unregister(): void
    {
        \spl_autoload_unregister([$this, 'load']);
    }
}
