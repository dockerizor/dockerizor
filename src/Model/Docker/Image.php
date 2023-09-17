<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker;

/**
 * Docker image.
 *
 * @deprecated
 */
class Image
{
    protected string $name;
    protected string $tag;

    public function __construct(string $name, string $tag = 'latest')
    {
        $this->name = $name;
        $this->tag = $tag;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function __toString()
    {
        return "{$this->name}:{$this->tag}";
    }
}
