<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Docker\API;

class Secret
{
    protected string $id;
    protected array $version;
    protected string $createdAt;
    protected string $updatedAt;
    protected array $Spec;

    public function __construct(array $data)
    {
        $this->id = $data['ID'];
        $this->version = $data['Version'];
        $this->createdAt = $data['CreatedAt'];
        $this->updatedAt = $data['UpdatedAt'];
        $this->Spec = $data['Spec'];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVersion(): array
    {
        return $this->version;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getSpec(): array
    {
        return $this->Spec;
    }

    public function getName(): string
    {
        return $this->Spec['Name'];
    }

    public function getLabels(): array
    {
        return $this->Spec['Labels'];
    }
}
