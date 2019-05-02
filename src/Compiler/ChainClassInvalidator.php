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

class ChainClassInvalidator implements ClassInvalidatorInterface
{
    /** @var ClassInvalidatorInterface[] */
    private $invalidators;

    public function __construct(array $invalidators)
    {
        $this->invalidators = $invalidators;
    }

    public function invalidate(ResolvedClassInfo $class): void
    {
        foreach ($this->invalidators as $invalidator) {
            $invalidator->invalidate($class);
        }
    }
}
