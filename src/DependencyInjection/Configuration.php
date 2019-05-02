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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('exsyst_dynamic_class_generation');

        $rootNode
            ->children()
                ->scalarNode('cache_directory')->defaultValue('%kernel.cache_dir%/exsyst-clsgen')->end()
                ->scalarNode('temporary_directory')->defaultValue('%kernel.cache_dir%/exsyst-clsgen')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
