<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Build;

use App\Model\Docker\DockerFile;

abstract class AbstractBuildContext
{
    protected ?DockerFile $dockerFile = null;

    /**
     * Set docker file.
     * 
     * @param DockerFile $dockerFile
     * 
     * @return self
     */
    public function setDockerFile(DockerFile $dockerFile): self
    {
        $this->dockerFile = $dockerFile;

        return $this;
    }

    /**
     * Get docker file.
     * 
     * @return DockerFile|null
     */
    public function getDockerFile(): ?DockerFile
    {
        return $this->dockerFile;
    }
}
