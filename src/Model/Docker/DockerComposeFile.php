<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker;

use App\Model\Docker\ComposeFile\Network;
use App\Model\Docker\ComposeFile\Secret;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Volume;
use App\Model\FileInterface;
use Symfony\Component\Yaml\Yaml;

class DockerComposeFile implements FileInterface
{
    private string $version;
    private string $path;
    private array $services = [];
    private array $networks = [];
    private array $secrets = [];
    private array $volumes = [];

    public function __construct(string $version = '3.8')
    {
        $this->version = $version;
    }

    /**
     * Get version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get services.
     */
    public function getServices(): array
    {
        return $this->services;
    }

    public function getService(string $name): ?Service
    {
        return $this->services[$name] ?? null;
    }

    /**
     * Add service to the docker-compose file.
     */
    public function addService(Service $service): self
    {
        $this->services[$service->getName()] = $service;

        return $this;
    }

    /**
     * Get networks.
     *
     * @return array
     */
    public function getNetworks()
    {
        return $this->networks;
    }

    /**
     * Add network to the docker-compose file.
     */
    public function addNetwork(Network $network): self
    {
        $this->networks[$network->getName()] = $network;

        return $this;
    }

    /**
     * Get secrets.
     */
    public function getSecrets(): array
    {
        return $this->secrets;
    }

    /**
     * Add secret to the docker-compose file.
     */
    public function addSecret(Secret $secret): self
    {
        $this->secrets[$secret->getName()] = $secret;

        return $this;
    }

    public function addVolume(Volume $volume): self
    {
        $this->volumes[$volume->getName()] = $volume;

        return $this;
    }

    public function load(string $file): self
    {
        $dockerComposeFile = Yaml::parseFile($file);

        $this->version = $dockerComposeFile['version'];

        foreach ($dockerComposeFile['services'] ?? [] as $name => $serviceData) {
            $service = new Service($name, $serviceData['image'] ?? null);
            $service->load($serviceData);

            $this->services[$name] = $service;
        }

        foreach ($dockerComposeFile['networks'] ?? [] as $name => $networkData) {
            $this->networks[$name] = new Network($name, $networkData['driver'] ?? null, $networkData['external']);
        }

        foreach ($dockerComposeFile['secrets'] ?? [] as $name => $secretData) {
            $this->secrets[$name] = new Secret($name, $secretData['external']);
        }

        foreach ($dockerComposeFile['volumes'] ?? [] as $name => $volumeData) {
            $this->volumes[$name] = new Volume($name, $volumeData['external']);
        }

        return $this;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $dockerComposeFile = [
            'version' => $this->version,
            'services' => [],
            'networks' => [],
            'secrets' => [],
        ];

        foreach ($this->services as $service) {
            $dockerComposeFile['services'][$service->getName()] = $service->toArray();
        }

        foreach ($this->networks as $network) {
            $dockerComposeFile['networks'][$network->getName()] = $network->toArray();
        }

        foreach ($this->secrets as $secret) {
            $dockerComposeFile['secrets'][$secret->getName()] = $secret->toArray();
        }

        foreach ($this->volumes as $volume) {
            $dockerComposeFile['volumes'][$volume->getName()] = $volume->toArray();
        }

        return $dockerComposeFile;
    }

    /**
     * Convert to string.
     */
    public function __toString(): string
    {
        return Yaml::dump($this->toArray(), 10);
    }
}
