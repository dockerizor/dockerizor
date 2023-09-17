<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Docker\ComposeFile;

/**
 * Docker Compose network.
 */
class Network
{
    private string $name;
    private ?string $driver = null;
    private bool $external = false;

    /**
     * Constructor.
     */
    public function __construct(string $name, string $driver = null, bool $external = false)
    {
        $this->name = $name;
        $this->driver = $driver;
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
     * Get driver.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Is external.
     */
    public function isExternal(): bool
    {
        return $this->external;
    }

    /**
     * Set external.
     */
    public function setExternal(bool $external): self
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $network = [
            'name' => $this->name,
        ];

        if ($this->driver) {
            $network['driver'] = $this->driver;
        }

        if ($this->external) {
            $network['external'] = $this->external;
        }

        return $network;
    }
}
