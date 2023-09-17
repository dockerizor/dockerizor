<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Docker\ComposeFile;

use App\Model\Docker\ComposeFile\Service\Deploy;
use App\Model\Docker\ComposeFile\Service\Network;
use App\Model\Docker\ComposeFile\Service\Port;
use App\Model\Docker\ComposeFile\Service\Volume;

/**
 * Docker Compose service.
 */
class Service
{
    private string $name;
    private ?string $container_name;
    private ?string $image;
    private array $build = [];
    private Restart $restart;
    private array $ports = [];
    private array $environment = [];
    private array $command = [];
    private array $volumes = [];
    private array $networks = [];
    private array $labels = [];
    private array $secrets = [];
    private Deploy $deploy;
    private string $workingDir = '';

    /**
     * Create new service.
     */
    public function __construct(string $name, string $image = null, Restart $restart = Restart::unless_stopped)
    {
        $this->name = $name;
        $this->image = $image;
        $this->restart = $restart;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get container name.
     */
    public function getContainerName(): ?string
    {
        return $this->container_name;
    }

    /**
     * Set container name.
     */
    public function setContainerName(string $container_name): self
    {
        $this->container_name = $container_name;

        return $this;
    }

    /**
     * Get image.
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set image.
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get build.
     */
    public function getBuild(): array
    {
        return $this->build;
    }

    /**
     * Set build.
     */
    public function setBuild(string $context): self
    {
        $this->build = [
            'context' => $context,
        ];

        return $this;
    }

    /**
     * Get ports.
     */
    public function getPorts(): array
    {
        return $this->ports;
    }

    /**
     * Get restart.
     */
    public function getRestart(): Restart
    {
        return $this->restart;
    }

    /**
     * Get environment.
     */
    public function getEnvironment(): array
    {
        return $this->environment;
    }

    /**
     * Get command.
     */
    public function getCommand(): array
    {
        return $this->command;
    }

    /**
     * Get volumes.
     */
    public function getVolumes(): array
    {
        return $this->volumes;
    }

    /**
     * Get networks.
     */
    public function getNetworks(): array
    {
        return $this->networks;
    }

    /**
     * Get labels.
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Add port to the service.
     */
    public function addPort(Port $port): self
    {
        if (!\in_array($port, $this->ports, true)) {
            $this->ports[] = $port;
        }

        return $this;
    }

    /**
     * Add array of ports to the service.
     */
    public function addPorts(array $ports): self
    {
        foreach ($ports as $port) {
            $this->addPort($port);
        }

        return $this;
    }

    /**
     * Add array of environment variables to the service.
     */
    public function addEnvironmentVariables(array $variables): self
    {
        foreach ($variables as $variable => $value) {
            $this->addEnvironmentVariable($variable, $value);
        }

        return $this;
    }

    /**
     * Add environment variable to the service.
     */
    public function addEnvironmentVariable(string $variable, string $value): self
    {
        $this->environment[$variable] = $value;

        return $this;
    }

    /**
     * Add command to the service.
     */
    public function addCommand(string $command): self
    {
        if (!\in_array($command, $this->command, true)) {
            $this->command[] = $command;
        }

        return $this;
    }

    /**
     * Add array of commands to the service.
     */
    public function addCommands(array $commands): self
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        return $this;
    }

    /**
     * Set commandes.
     */
    public function setCommands(array $commands): self
    {
        $this->command = $commands;

        return $this;
    }

    public function setCommandString(string $command): self
    {
        $this->command = explode(' ', $command);

        return $this;
    }

    /**
     * Add volume to the service.
     *
     * @param string $volume
     */
    public function addVolume(Volume $volume): self
    {
        if (!\in_array($volume, $this->volumes, true)) {
            $this->volumes[] = $volume;
        }

        return $this;
    }

    /**
     * Add Network to the service.
     */
    public function addNetwork(Network $network): self
    {
        if (!\in_array($network, $this->networks, true)) {
            $this->networks[] = $network;
        }

        return $this;
    }

    /**
     * Add label to the service.
     */
    public function addLabel(string $label, string $value): self
    {
        $this->labels[$label] = $value;

        return $this;
    }

    /**
     * Add secret to the service.
     */
    public function addSecret(string $secret): self
    {
        if (!\in_array($secret, $this->secrets, true)) {
            $this->secrets[] = $secret;
        }

        return $this;
    }

    /**
     * Get deploy.
     */
    public function getDeploy(): Deploy
    {
        return $this->deploy;
    }

    /**
     * Set deploy.
     */
    public function setDeploy(Deploy $deploy): self
    {
        $this->deploy = $deploy;

        return $this;
    }

    /**
     * Get workingDir.
     */
    public function getWorkingDir(): string
    {
        return $this->workingDir;
    }

    /**
     * Set workingDir.
     */
    public function setWorkingDir(string $workingDir): self
    {
        $this->workingDir = $workingDir;

        return $this;
    }

    /**
     * Load service from array.
     */
    public function load(array $service): void
    {
        if (isset($service['container_name'])) {
            $this->container_name = $service['container_name'];
        }

        if (isset($service['image'])) {
            $this->image = $service['image'];
        }

        if (isset($service['build'])) {
            $this->build = $service['build'];
        }

        if (isset($service['restart'])) {
            $this->restart = Restart::from($service['restart']);
        }

        if (isset($service['ports'])) {
            foreach ($service['ports'] as $port) {
                $this->ports[] = Port::create($port);
            }
        }

        if (isset($service['environment'])) {
            $this->environment = $service['environment'];
        }

        if (isset($service['command'])) {
            $this->command = $service['command'];
        }

        if (isset($service['volumes'])) {
            foreach ($service['volumes'] as $volume) {
                $this->volumes[] = Volume::create($volume);
            }
        }

        if (isset($service['networks'])) {
            foreach ($service['networks'] as $name => $network) {
                $this->networks[] = new Network($name, $network['aliases'][0]);
            }
        }

        if (isset($service['labels'])) {
            $this->labels = $service['labels'];
        }

        if (isset($service['secrets'])) {
            $this->secrets = $service['secrets'];
        }

        if (isset($service['deploy'])) {
            $this->deploy = Deploy::create($service['deploy']);
        }

        if (isset($service['working_dir'])) {
            $this->workingDir = $service['working_dir'];
        }
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $service = [
            'restart' => $this->restart->value,
        ];

        if ($this->image) {
            $service['image'] = $this->image;
        }

        if (!empty($this->container_name)) {
            $service['container_name'] = $this->container_name;
        }

        if (!empty($this->build)) {
            $service['build'] = $this->build;
        }

        foreach ($this->ports as $port) {
            $service['ports'][] = $port->toArray();
        }

        foreach ($this->environment as $var => $env) {
            $service['environment'][$var] = $env;
        }

        foreach ($this->volumes as $volume) {
            $service['volumes'][] = $volume->toArray();
        }

        foreach ($this->networks as $network) {
            $service['networks'][$network->getName()] = $network->toArray();
        }

        foreach ($this->labels as $label => $value) {
            $service['labels'][$label] = $value;
        }

        if (!empty($this->command)) {
            $service['command'] = $this->command;
        }

        foreach ($this->secrets as $secret) {
            $service['secrets'][] = $secret;
        }

        if (!empty($this->deploy)) {
            $service['deploy'] = $this->deploy->toArray();
        }

        if (!empty($this->workingDir)) {
            $service['working_dir'] = $this->workingDir;
        }

        return $service;
    }
}
