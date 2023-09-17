<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Build;

use App\Model\Docker\DockerFile;

interface BuildContextInterface
{
    /**
     * Get docker file.
     */
    public function getDockerFile(): ?DockerFile;
}
