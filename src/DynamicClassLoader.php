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

class DynamicClassLoader
{
    /** @var ClassResolver */
    private $resolver;

    /** @var ClassGeneratorInterface */
    private $generator;

    /** @var ClassInvalidatorInterface */
    private $invalidator;

    public function __construct(ClassResolver $resolver, ClassGeneratorInterface $generator, ClassInvalidatorInterface $invalidator)
    {
        $this->resolver = $resolver;
        $this->generator = $generator;
        $this->invalidator = $invalidator;
    }

    public function load(string $class): void
    {
        $resolvedClass = $this->resolver->resolve($class);
        if (!\is_file($resolvedClass->getPath())) {
            $directory = \dirname($resolvedClass->getPath());
            if (!\is_dir($directory)) {
                \mkdir($directory, 0777, true);
            }
            if (!$this->generator->generate($resolvedClass)) {
                return;
            }
            if (\function_exists('opcache_invalidate')) {
                \opcache_invalidate($resolvedClass->getPath(), true);
            }
        }

        require_once $resolvedClass->getPath();
    }

    public function invalidate(string $class): void
    {
        $resolvedClass = $this->resolver->resolve($class);
        if (!\is_file($resolvedClass->getPath())) {
            return;
        }

        \unlink($resolvedClass->getPath());
        $this->invalidator->invalidate($resolvedClass);
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
