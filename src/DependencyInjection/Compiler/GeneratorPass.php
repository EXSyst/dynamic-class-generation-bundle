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

use EXSyst\DynamicClassGenerationBundle\Compiler\ChainClassGenerator;
use EXSyst\DynamicClassGenerationBundle\Compiler\DispatchClassGenerator;
use EXSyst\DynamicClassGenerationBundle\Compiler\LazyClassGeneratorWrapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GeneratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $prefixTree = [];
        $wrappers = [];
        $generators = [];

        foreach ($container->findTaggedServiceIds('exsyst.dynamic_class_generation.class_generator') as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['prefix'])) {
                    $prefix = $tag['prefix'];
                } else {
                    $prefix = $container->getDefinition($id)->getClass();
                    $prefix = \str_replace('\\Compiler\\', '\\__CG__\\', $prefix);
                    $prefix = \preg_replace('#Compiler$#', '', $prefix);
                    $prefix .= '\\';
                }
                self::addPrefixToTree($prefixTree, $prefix);
                $wrappers[$id] = null;
                $generators[$prefix][$id] = \intval($tag['priority'] ?? 0);
            }
        }

        self::sortPrefixTree($prefixTree);
        $container->setParameter('exsyst_dynamic_class_generation.resolver_prefix_tree', $prefixTree);

        if (!$container->hasDefinition(DispatchClassGenerator::class)) {
            return;
        }

        foreach ($wrappers as $id => &$wrapper) {
            if ($container->getDefinition($id)->isLazy()) {
                $wrapper = new Reference($id);
            } else {
                $container->register('exsyst_dynamic_class_generation.lazy_class_generator_wrapper.'.$id)
                    ->setClass(LazyClassGeneratorWrapper::class)
                    ->addArgument(new Reference($id))
                    ->setLazy(true)
                    ->setPublic(false)
                    ->setPrivate(true);
                $wrapper = new Reference('exsyst_dynamic_class_generation.lazy_class_generator_wrapper.'.$id);
            }
        }
        unset($wrapper);

        foreach ($generators as $prefix => &$subGenerators) {
            \arsort($subGenerators);
            $subGenerators = \array_map(function (string $id) use ($wrappers): Reference {
                return $wrappers[$id];
            }, \array_keys($subGenerators));
            switch (\count($subGenerators)) {
                case 0:
                    $subGenerators = null;
                    break;
                case 1:
                    $subGenerators = $subGenerators[0];
                    break;
                default:
                    $container->register('exsyst_dynamic_class_generation.chain_class_generator.'.$prefix)
                        ->setClass(ChainClassGenerator::class)
                        ->addArgument($subGenerators)
                        ->setPublic(false)
                        ->setPrivate(true);

                    $subGenerators = new Reference('exsyst_dynamic_class_generation.chain_class_generator.'.$prefix);
                    break;
            }
        }
        unset($subGenerators);
        $generators = \array_filter($generators);
        \ksort($generators);

        $container->getDefinition(DispatchClassGenerator::class)->replaceArgument('$generators', $generators);
    }

    private static function addPrefixToTree(array &$prefixTree, string $prefix): void
    {
        $prefixLength = \strlen($prefix);
        if (0 === $prefixLength) {
            $prefixTree["\0good"] = true;

            return;
        }

        foreach ($prefixTree as $edge => &$node) {
            $edgeLength = \strlen($edge);
            $cpl = self::getCommonPrefixLength($edge, $prefix, \min($prefixLength, $edgeLength));
            if ($cpl === $edgeLength) {
                self::addPrefixToTree($node, \substr($prefix, $cpl));

                return;
            } elseif ($cpl > 0) {
                unset($prefixTree[$edge]);
                $newNode = [\substr($edge, $cpl) => $node];
                self::addPrefixToTree($newNode, \substr($prefix, $cpl));
                $prefixTree[\substr($edge, 0, $cpl)] = $newNode;

                return;
            }
        }

        $prefixTree[$prefix] = ["\0good" => true];
    }

    private static function getCommonPrefixLength(string $s1, string $s2, int $max): int
    {
        for ($i = 0; $i < $max; ++$i) {
            if ($s1[$i] !== $s2[$i]) {
                return $i;
            }
        }

        return $max;
    }

    private static function sortPrefixTree(array &$prefixTree): void
    {
        \ksort($prefixTree);
        foreach ($prefixTree as &$node) {
            if (\is_array($node)) {
                self::sortPrefixTree($node);
            }
        }
    }
}
