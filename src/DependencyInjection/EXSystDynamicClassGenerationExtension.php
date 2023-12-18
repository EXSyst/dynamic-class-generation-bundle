<?php

/*
 * This file is part of exsyst/dynamic-class-generation-bundle.
 *
 * Copyright (C) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\DynamicClassGenerationBundle\DependencyInjection;

use EXSyst\DynamicClassGenerationBundle\Compiler\ClassGeneratorInterface;
use EXSyst\DynamicClassGenerationBundle\Compiler\ClassInvalidatorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Processor;

class EXSystDynamicClassGenerationExtension extends Extension
{
    public function getAlias()
    {
        return 'exsyst_dynamic_class_generation';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('exsyst_dynamic_class_generation.cache_directory', $config['cache_directory']);
        $container->setParameter('exsyst_dynamic_class_generation.temporary_directory', $config['temporary_directory']);

        $container->registerForAutoconfiguration(ClassGeneratorInterface::class)
            ->addTag('exsyst.dynamic_class_generation.class_generator');
        $container->registerForAutoconfiguration(ClassInvalidatorInterface::class)
            ->addTag('exsyst.dynamic_class_generation.class_invalidator');

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }
}
