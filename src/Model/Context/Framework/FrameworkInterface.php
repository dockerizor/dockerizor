<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Framework;

interface FrameworkInterface
{
    /**
     * Get name.
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Get version.
     * 
     * @return string
     */
    public function getVersion(): string;

    /**
     * Get root directory.
     * 
     * @return string
     */
    public function getRootDirectory(): string;

    /**
     * Set root directory.
     * 
     * @param string $rootDirectory
     * 
     * @return self
     */
    public function getNodeRunDevCommand(): ?string;

    /**
     * Set root directory.
     * 
     * @param string $rootDirectory
     * 
     * @return self
     */
    public function getNodeRunBuildCommand(): ?string;

    /**
     * Set root directory.
     * 
     * @param string $rootDirectory
     * 
     * @return self
     */
    public function getNodeRunInstallCommand(): ?string;
}
