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

class DispatchClassGenerator implements ClassGeneratorInterface
{
    /** @var ClassGeneratorInterface[] */
    private $generators;

    public function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    public function getGeneratorForPrefix(string $prefix): ?ClassGeneratorInterface
    {
        return $this->generators[$prefix] ?? null;
    }

    public function generate(ResolvedClassInfo $class): bool
    {
        $generator = $this->getGeneratorForPrefix($class->getPrefix());

        return null !== $generator && $generator->generate($class);
    }
}
