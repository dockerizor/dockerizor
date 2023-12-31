<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerFile;

use App\Model\Context\Build\BuildContextInterface;
use App\Model\Docker\DockerFile;

class AlpineDockerFileBuilder
{
    /**
     * Build Alpine dockerFile.
     */
    public function build(BuildContextInterface $context): DockerFile
    {
        $dockerFile = $context->getDockerFile();

        $operatingSystem = $dockerFile->getOperatingSystem();

        // Add operating system runs
        if (!empty($operatingSystem->getPackages())) {
            $dockerFile->addRun($operatingSystem->runPackageManagerUpdate());
            $dockerFile->addRun($operatingSystem->runPackageManagerInstall());
        }

        return $dockerFile;
    }
}
