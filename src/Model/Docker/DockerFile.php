<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker;

use App\Model\FileInterface;
use App\Model\OS\OperatingSystem;

class DockerFile implements FileInterface
{
    private OperatingSystem $operatingSystem;
    private string $path;
    private array $instructions = [];

    public function __construct(OperatingSystem $operatingSystem)
    {
        $this->operatingSystem = $operatingSystem;
    }

    /**
     * Get operating system.
     */
    public function getOperatingSystem(): OperatingSystem
    {
        return $this->operatingSystem;
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
     * Get instructions.
     */
    public function getInstructions(): array
    {
        return $this->instructions;
    }

    /**
     * Add instruction to the dockerfile.
     */
    public function addInstruction(string $instruction): self
    {
        $this->instructions[] = $instruction;

        return $this;
    }

    /**
     * Add RUN instruction to the dockerfile.
     */
    public function addRun(string $command): self
    {
        $this->instructions[] = "RUN $command";

        return $this;
    }

    /**
     * Add COPY instruction to the dockerfile.
     */
    public function addCopy(string $source, string $destination): self
    {
        $this->instructions[] = "COPY $source $destination";

        return $this;
    }

    /**
     * Add COPY instruction to the dockerfile.
     *
     * @return self
     */
    public function __toString()
    {
        $dockerFile = "FROM {$this->operatingSystem->getImage()}\n";
        foreach ($this->instructions as $instruction) {
            $dockerFile .= "$instruction\n";
        }

        return $dockerFile;
    }
}
