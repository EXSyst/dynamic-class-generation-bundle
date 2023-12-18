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

class ChainClassGenerator implements ClassGeneratorInterface
{
    /** @var ClassGeneratorInterface[] */
    private array $generators;

    public function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    public function generate(ResolvedClassInfo $class): bool
    {
        foreach ($this->generators as $generator) {
            if ($generator->generate($class)) {
                return true;
            }
        }

        return false;
    }
}
