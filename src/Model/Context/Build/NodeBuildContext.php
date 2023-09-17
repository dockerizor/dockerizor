<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Build;

class NodeBuildContext extends AbstractBuildContext implements BuildContextInterface
{
    protected string $version;
    protected string $image;
    protected string $command;

    public function __construct(string $version = 'lts', string $image = 'node:lts-alpine')
    {
        $this->version = $version;
        $this->image = $image;
    }

    /**
     * Get version.
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get image.
     * 
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set image.
     * 
     * @param string $image
     * 
     * @return self
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * Set image.
     * 
     * @param string $image
     * 
     * @return self
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }
}
