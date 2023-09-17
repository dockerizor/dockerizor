<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
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
     * 
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * Set app name.
     * 
     * @param string $appName
     * 
     * @return self
     */
    public function setAppName(string $appName): self
    {
        $this->appName = $appName;

        return $this;
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
     * Set workdir.
     * 
     * @param string $workdir
     * 
     * @return self
     */
    public function setWorkdir(string $workdir): self
    {
        $this->workdir = $workdir;

        return $this;
    }

    /**
     * Get domain.
     * 
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Set domain.
     * 
     * @param string $domain
     * 
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
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
     * Set docker compose file.
     * 
     * @param DockerComposeFile $dockerComposeFile
     * 
     * @return self
     */
    public function setDockerComposeFile(DockerComposeFile $dockerComposeFile): self
    {
        $this->dockerComposeFile = $dockerComposeFile;

        return $this;
    }

    /**
     * Get proxy container.
     * 
     * @return Container
     */
    public function getProxyContainer(): Container
    {
        return $this->proxyContainer;
    }

    /**
     * Set proxy container.
     * 
     * @param Container $proxyContainer
     * 
     * @return self
     */
    public function setProxyContainer(Container $proxyContainer): self
    {
        $this->proxyContainer = $proxyContainer;

        return $this;
    }

    /**
     * Set frontend network.
     * 
     * @param string $frontendNetwork
     * 
     * @return self
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
     * 
     * @return string
     */
    public function getFrontendNetwork(): string
    {
        return $this->frontendNetwork;
    }

    /**
     * Set backend network.
     * 
     * @param string $backendNetwork
     * 
     * @return self
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
     * 
     * @return string
     */
    public function getBackendNetwork(): string
    {
        return $this->backendNetwork;
    }

    /**
     * Add build context.
     * 
     * @param BuildContextInterface $context
     * 
     * @return self
     */
    public function addBuildContext(BuildContextInterface $context): self
    {
        $this->buildContexts[] = $context;

        return $this;
    }

    /**
     * Get build contexts.
     * 
     * @return array
     */
    public function getBuildContexts(): array
    {
        return $this->buildContexts;
    }

    /**
     * Get build context.
     * 
     * @param string $class
     * 
     * @return BuildContextInterface|null
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
     * 
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Add file.
     * 
     * @param FileInterface $file
     * 
     * @return self
     */
    public function addFile(FileInterface $file): self
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Get runs.
     * 
     * @return array
     */
    public function getRuns(): array
    {
        return $this->runs;
    }

    /**
     * Add run.
     * 
     * @param DockerRun $run
     * 
     * @return self
     */
    public function addRun(DockerRun $run): self
    {
        $this->runs[] = $run;

        return $this;
    }
}
