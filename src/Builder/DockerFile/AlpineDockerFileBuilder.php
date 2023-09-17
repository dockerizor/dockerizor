<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Builder\DockerFile;

use App\Model\Context\Build\BuildContextInterface;
use App\Model\Docker\DockerFile;

class AlpineDockerFileBuilder
{
    /**
     * Build Alpine dockerFile
     * 
     * @param BuildContextInterface $context
     * 
     * @return DockerFile
     */
    public function build(BuildContextInterface $context): DockerFile
    {
        $dockerFile = $context->getDockerFile();

        $operatingSystem = $dockerFile->getOperatingSystem();

        // Add operating system runs
        $dockerFile->addRun($operatingSystem->runPackageManagerUpdate());
        $dockerFile->addRun($operatingSystem->runPackageManagerInstall());

        return $dockerFile;
    }
}
