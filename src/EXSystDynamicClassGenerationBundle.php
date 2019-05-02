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
use EXSyst\DynamicClassGenerationBundle\DependencyInjection\Compiler\GeneratorPass;
use EXSyst\DynamicClassGenerationBundle\DependencyInjection\Compiler\InvalidatorPass;
use EXSyst\DynamicClassGenerationBundle\DependencyInjection\EXSystDynamicClassGenerationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EXSystDynamicClassGenerationBundle extends Bundle
{
    public function boot()
    {
        if ($this->container->has(DynamicClassLoader::class)) {
            $this->container->get(DynamicClassLoader::class)->register();
        }
    }

    public function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ClassGeneratorInterface::class)
            ->addTag('exsyst.dynamic_class_generation.class_generator');
        $container->registerForAutoconfiguration(ClassInvalidatorInterface::class)
            ->addTag('exsyst.dynamic_class_generation.class_invalidator');
        $container->addCompilerPass(new GeneratorPass());
        $container->addCompilerPass(new InvalidatorPass());
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new EXSystDynamicClassGenerationExtension();
        }

        return $this->extension;
    }
}
