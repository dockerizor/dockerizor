<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context;

use App\Model\Context\App\AppContextInterface;
use App\Model\Docker\DockerComposeFile;

class EnvironmentContext
{
    protected DockerComposeFile $dockerComposeFile;
    protected string $workdir;
    protected array $appContexts = [];

    public function __construct(DockerComposeFile $dockerComposeFile, string $workdir)
    {
        $this->dockerComposeFile = $dockerComposeFile;
        $this->workdir = $workdir;
    }

    /**
     * Get docker compose file.
     * 
     * @return DockerComposeFile
     */
    public function getDockerComposeFile(): DockerComposeFile
    {
        return $this->dockerComposeFile;
    }

    /**
     * Get workdir.
     * 
     * @return string
     */
    public function getWorkdir(): string
    {
        return $this->workdir;
    }

    /**
     * Get app contexts.
     * 
     * @return array
     */
    public function getAppContexts(): array
    {
        return $this->appContexts;
    }

    /**
     * Set app contexts.
     * 
     * @param array $appContexts
     * 
     * @return self
     */
    public function addAppContext(AppContextInterface $appContext): self
    {
        $this->appContexts[] = $appContext;

        return $this;
    }

    /**
     * Get app context.
     * 
     * @param string $class
     * 
     * @return AppContextInterface|null
     */
    public function getAppContext(string $class): ?AppContextInterface
    {
        foreach ($this->appContexts as $appContext) {
            if ($appContext instanceof $class) {
                return $appContext;
            }
        }

        return null;
    }

    /**
     * Has app context.
     * 
     * @param string $class
     * 
     * @return bool
     */
    public function hasAppContext(string $class): bool
    {
        return null !== $this->getAppContext($class);
    }
}
