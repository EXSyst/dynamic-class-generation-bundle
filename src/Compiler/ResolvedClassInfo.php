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

class ResolvedClassInfo
{
    private string $class;
    private string $prefix;
    private string $rest;
    private string $path;

    public function __construct(string $class, string $prefix, string $rest, string $path)
    {
        $this->class = $class;
        $this->prefix = $prefix;
        $this->rest = $rest;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getRest(): string
    {
        return $this->rest;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function withPath(string $path): self
    {
        return new self($this->class, $this->prefix, $this->rest, $path);
    }
}
