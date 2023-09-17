<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Build;

use App\Model\Docker\API\Container;
use App\Model\Docker\ComposeFile\Network;
use App\Model\Docker\DockerComposeFile;
use App\Model\Docker\DockerRun;
use App\Model\FileInterface;

class AppBuildContext
{
    protected string $appName;
    protected string $workdir;
    protected string $domain;
    protected DockerComposeFile $dockerComposeFile;
    protected Container $proxyContainer;
    protected ?string $frontendNetwork;
    protected ?string $backendNetwork;
    protected array $buildContexts = [];
    protected array $files = [];
    protected array $runs = [];

    public function __construct(string $appName, string $workdir)
    {
        $this->appName = $appName;
        $this->workdir = $workdir;
    }

    /**
     * Get app name.
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * Set app name.
     */
    public function setAppName(string $appName): self
    {
        $this->appName = $appName;

        return $this;
    }

    /**
     * Get workdir.
     */
    public function getWorkdir(): string
    {
        return $this->workdir;
    }

    /**
     * Set workdir.
     */
    public function setWorkdir(string $workdir): self
    {
        $this->workdir = $workdir;

        return $this;
    }

    /**
     * Get domain.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Set domain.
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get docker compose file.
     */
    public function getDockerComposeFile(): DockerComposeFile
    {
        return $this->dockerComposeFile;
    }

    /**
     * Set docker compose file.
     */
    public function setDockerComposeFile(DockerComposeFile $dockerComposeFile): self
    {
        $this->dockerComposeFile = $dockerComposeFile;

        return $this;
    }

    /**
     * Get proxy container.
     */
    public function getProxyContainer(): Container
    {
        return $this->proxyContainer;
    }

    /**
     * Set proxy container.
     */
    public function setProxyContainer(Container $proxyContainer): self
    {
        $this->proxyContainer = $proxyContainer;

        return $this;
    }

    /**
     * Set frontend network.
     */
    public function setFrontendNetwork(string $frontendNetwork): self
    {
        $this->frontendNetwork = $frontendNetwork;

        $this->dockerComposeFile->addNetwork(
            (new Network($frontendNetwork))
                ->setExternal(true)
        );

        return $this;
    }

    /**
     * Get frontend network.
     */
    public function getFrontendNetwork(): string
    {
        return $this->frontendNetwork;
    }

    /**
     * Set backend network.
     */
    public function setBackendNetwork(string $backendNetwork): self
    {
        $this->backendNetwork = $backendNetwork;

        $this->dockerComposeFile->addNetwork(
            (new Network($backendNetwork))
                ->setExternal(true)
        );

        return $this;
    }

    /**
     * Get backend network.
     */
    public function getBackendNetwork(): string
    {
        return $this->backendNetwork;
    }

    /**
     * Add build context.
     */
    public function addBuildContext(BuildContextInterface $context): self
    {
        $this->buildContexts[] = $context;

        return $this;
    }

    /**
     * Get build contexts.
     */
    public function getBuildContexts(): array
    {
        return $this->buildContexts;
    }

    /**
     * Get build context.
     */
    public function getBuildContext(string $class): ?BuildContextInterface
    {
        foreach ($this->buildContexts as $context) {
            if ($context instanceof $class) {
                return $context;
            }
        }

        return null;
    }

    /**
     * Get files.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Add file.
     */
    public function addFile(FileInterface $file): self
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Get runs.
     */
    public function getRuns(): array
    {
        return $this->runs;
    }

    /**
     * Add run.
     */
    public function addRun(DockerRun $run): self
    {
        $this->runs[] = $run;

        return $this;
    }
}
