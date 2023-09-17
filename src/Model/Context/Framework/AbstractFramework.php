<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Framework;

class AbstractFramework
{
    protected string $name;
    protected string $version;
    protected string $rootDirectory;

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get root directory.
     */
    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    /**
     * Set root directory.
     *
     * @return self
     */
    public function getNodeRunInstallCommand(): ?string
    {
        return null;
    }

    /**
     * Set root directory.
     *
     * @return self
     */
    public function getNodeRunBuildCommand(): ?string
    {
        return null;
    }

    /**
     * Set root directory.
     *
     * @return self
     */
    public function getNodeRunDevCommand(): ?string
    {
        return null;
    }
}
