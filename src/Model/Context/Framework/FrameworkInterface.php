<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Framework;

interface FrameworkInterface
{
    /**
     * Get name.
     */
    public function getName(): string;

    /**
     * Get version.
     */
    public function getVersion(): string;

    /**
     * Get root directory.
     */
    public function getRootDirectory(): string;

    /**
     * Set root directory.
     *
     * @return self
     */
    public function getNodeRunDevCommand(): ?string;

    /**
     * Set root directory.
     *
     * @return self
     */
    public function getNodeRunBuildCommand(): ?string;

    /**
     * Set root directory.
     *
     * @return self
     */
    public function getNodeRunInstallCommand(): ?string;
}
