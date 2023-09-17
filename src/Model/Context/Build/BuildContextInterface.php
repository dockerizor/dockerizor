<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Build;

use App\Model\Docker\DockerFile;

interface BuildContextInterface
{
    /**
     * Get docker file.
     * 
     * @return DockerFile|null
     */
    public function getDockerFile(): ?DockerFile;
}
