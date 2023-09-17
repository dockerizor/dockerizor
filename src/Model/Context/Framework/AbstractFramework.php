<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Framework;

class AbstractFramework
{
    protected string $name;
    protected string $version;
    protected string $rootDirectory;

    /** 
     * Get name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * Get root directory.
     * 
     * @return string
     */
    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    /** 
     * Set root directory.
     * 
     * @param string $rootDirectory
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
     * @param string $rootDirectory
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
     * @param string $rootDirectory
     * 
     * @return self
     */
    public function getNodeRunDevCommand(): ?string
    {
        return null;
    }
}
