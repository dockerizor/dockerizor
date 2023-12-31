<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile;

class Volume
{
    protected string $name;
    protected bool $external = false;

    public function __construct(string $name, bool $external = false)
    {
        $this->name = $name;
        $this->external = $external;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Is external.
     */
    public function isExternal(): bool
    {
        return $this->external;
    }

    /**
     * To array.
     */
    public function toArray(): array
    {
        return [
            'external' => $this->external,
        ];
    }
}
