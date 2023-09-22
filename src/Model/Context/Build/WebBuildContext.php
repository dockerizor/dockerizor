<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Build;

class WebBuildContext extends BuildContext implements BuildContextInterface
{
    protected string $rootDir = '/var/www/html';
    protected int $port = 80;

    public function __construct(string $rootDir = '/var/www/html')
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Get root dir.
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * Set root dir.
     */
    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    /**
     * Get port.
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set port.
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }
}
