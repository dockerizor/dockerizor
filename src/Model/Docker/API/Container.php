<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Docker\API;

/**
 * Docker ContainerInspect.
 */
class Container
{
    protected string $id;
    protected string $name;
    protected string $image;
    protected string $created;
    protected string $state;
    protected string $status;
    protected array $ports;
    protected array $labels;
    protected array $mounts;
    protected array $networks;

    public function __construct(array $data)
    {
        $this->id = $data['Id'];
        $this->name = $data['Names'][0] ?? $data['Name'];
        $this->image = $data['Image'];
        $this->created = $data['Created'];
        $this->state = $data['State'];
        $this->ports = $data['Ports'];
        $this->labels = $data['Labels'];
        $this->mounts = $data['Mounts'];
        $this->networks = $data['NetworkSettings']['Networks'];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        $name = $this->name;
        $name = str_replace('/', '', $name);

        return $name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPorts(): array
    {
        return $this->ports;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getLabel(string $label): ?string
    {
        return $this->labels[$label] ?? null;
    }

    public function getMounts(): array
    {
        return $this->mounts;
    }

    public function getNetworks(): array
    {
        return $this->networks;
    }
}
