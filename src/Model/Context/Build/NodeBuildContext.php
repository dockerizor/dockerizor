<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get image.
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set image.
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
     * @return self
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }
}
