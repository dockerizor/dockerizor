<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile\Service;

/**
 * Docker Compose service network.
 */
class Network
{
    private string $name;
    private array $aliases = [];

    /**
     * Create new service network.
     */
    public function __construct(string $name, string $alias = null)
    {
        $this->name = $name;
        if ($alias) {
            $this->addAlias($alias);
        }
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get aliases.
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Add alias.
     */
    public function addAlias(string $alias): self
    {
        $this->aliases[] = $alias;

        return $this;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $serviceNetwork = [];

        if (!empty($this->aliases)) {
            $serviceNetwork['aliases'] = $this->aliases;
        }

        return $serviceNetwork;
    }
}
