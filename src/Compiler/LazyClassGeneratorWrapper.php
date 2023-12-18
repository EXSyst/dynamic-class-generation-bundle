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

class LazyClassGeneratorWrapper implements ClassGeneratorInterface
{
    private ClassGeneratorInterface $generator;

    public function __construct(ClassGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function generate(ResolvedClassInfo $class): bool
    {
        return $this->generator->generate($class);
    }
}
