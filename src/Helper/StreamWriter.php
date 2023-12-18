<?php

/*
 * This file is part of exsyst/dynamic-class-generation-bundle.
 *
 * Copyright (C) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\DynamicClassGenerationBundle\Helper;

class StreamWriter
{
    /** @var resource */
    private $fd;

    private string $indent;

    public function __construct($fd)
    {
        $this->fd = $fd;
        $this->indent = '';
    }

    public function indent(): self
    {
        $this->indent .= '    ';

        return $this;
    }

    public function outdent(): self
    {
        $this->indent = \substr($this->indent, 0, -4);

        return $this;
    }

    public function printfln(string $format = '', ...$args): self
    {
        if (!empty($format)) {
            \fwrite($this->fd, $this->indent);
            \fprintf($this->fd, $format, ...$args);
        }
        \fwrite($this->fd, "\n");

        return $this;
    }
}
