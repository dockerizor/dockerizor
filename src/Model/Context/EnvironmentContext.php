<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     */
    public function getDockerComposeFile(): DockerComposeFile
    {
        return $this->dockerComposeFile;
    }

    /**
     * Get workdir.
     */
    public function getWorkdir(): string
    {
        return $this->workdir;
    }

    /**
     * Get app contexts.
     */
    public function getAppContexts(): array
    {
        return $this->appContexts;
    }

    /**
     * Set app contexts.
     */
    public function addAppContext(AppContextInterface $appContext): self
    {
        $this->appContexts[] = $appContext;

        return $this;
    }

    /**
     * Get app context.
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
     */
    public function hasAppContext(string $class): bool
    {
        return null !== $this->getAppContext($class);
    }
}
