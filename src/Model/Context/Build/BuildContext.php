<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Build;

use App\Model\Docker\DockerFile;

class BuildContext implements BuildContextInterface
{
    protected ?DockerFile $dockerFile = null;
    protected ?string $image = null;

    /**
     * Set docker file.
     */
    public function setDockerFile(DockerFile $dockerFile): self
    {
        $this->dockerFile = $dockerFile;

        return $this;
    }

    /**
     * Get docker file.
     */
    public function getDockerFile(): ?DockerFile
    {
        return $this->dockerFile;
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
}
