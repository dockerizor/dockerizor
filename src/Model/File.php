<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model;

class File implements FileInterface
{
    private string $path;
    private string $content;

    public function __construct(string $path, string $content = null)
    {
        $this->path = $path;
        $this->content = $content;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->content;
    }
}
