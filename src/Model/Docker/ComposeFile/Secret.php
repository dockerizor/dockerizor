<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile;

/**
 * Docker Compose secret.
 */
class Secret
{
    protected string $name;
    protected bool $external = false;

    /**
     * Constructor.
     */
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
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'external' => $this->external,
        ];
    }
}
