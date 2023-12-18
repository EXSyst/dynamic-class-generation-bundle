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

use Symfony\Component\Filesystem\Exception\IOException;

class TemporaryWrappedClassGenerator implements ClassGeneratorInterface
{
    private ClassGeneratorInterface $generator;
    private string $temporaryDirectory;

    public function __construct(ClassGeneratorInterface $generator, string $temporaryDirectory)
    {
        $this->generator = $generator;
        if (!\is_dir($temporaryDirectory)) {
            \mkdir($temporaryDirectory, 0777, true);
        }
        $this->temporaryDirectory = \realpath($temporaryDirectory);
    }

    public function generate(ResolvedClassInfo $class): bool
    {
        $tmpFile = \tempnam($this->temporaryDirectory, 'cls');
        if (false === $tmpFile) {
            throw new IOException('Cannot create temporary file to compile dynamic class '.$class->getClass());
        }
        try {
            if (!$this->generator->generate($class->withPath($tmpFile))) {
                \unlink($tmpFile);

                return false;
            }
        } catch (\Throwable $e) {
            \unlink($tmpFile);

            throw $e;
        }
        \chmod($tmpFile, 0666 & ~\umask());
        $targetDirectory = \dirname($class->getPath());
        if (!\is_dir($targetDirectory)) {
            \mkdir($targetDirectory, 0777, true);
        }
        if (!\rename($tmpFile, $class->getPath())) {
            throw new IOException('Cannot rename temporary file with dynamic class '.$class->getClass());
        }

        return true;
    }
}
