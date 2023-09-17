<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Build;

class WebBuildContext extends AbstractBuildContext implements BuildContextInterface
{
    protected string $rootDir = '/var/www/html';
    protected int $port = 80;

    public function __construct(string $rootDir = '/var/www/html')
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Get root dir.
     * 
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * Set root dir.
     * 
     * @param string $rootDir
     * 
     * @return self
     */
    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    /**
     * Get port.
     * 
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set port.
     * 
     * @param int $port
     * 
     * @return self
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }
}
