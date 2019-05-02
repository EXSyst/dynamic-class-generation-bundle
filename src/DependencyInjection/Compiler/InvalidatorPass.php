<?php

/*
 * This file is part of exsyst/dynamic-class-generation-bundle.
 *
 * Copyright (C) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\DynamicClassGenerationBundle\DependencyInjection\Compiler;

use EXSyst\DynamicClassGenerationBundle\Compiler\ChainClassInvalidator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InvalidatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ChainClassInvalidator::class)) {
            return;
        }

        $invalidators = \array_map(function (string $id): Reference {
            return new Reference($id);
        }, \array_keys($container->findTaggedServiceIds('exsyst.dynamic_class_generation.class_invalidator')));

        $container->getDefinition(ChainClassInvalidator::class)->replaceArgument('$invalidators', $invalidators);
    }
}
