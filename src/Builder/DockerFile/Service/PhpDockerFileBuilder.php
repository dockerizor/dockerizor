<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerFile\Service;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\PhpBuildContext;

class PhpDockerFileBuilder
{
    public function prepare(AppBuildContext $appBuildContext, PhpBuildContext $context)
    {
        // Process dockerFile
        $dockerFile = $context->getDockerFile();

        $dockerFile->getOperatingSystem()->addPackage('shadow');
    }

    /**
     * Build php service.
     */
    public function build(AppBuildContext $appBuildContext, PhpBuildContext $context): void
    {
        // Process dockerFile
        $dockerFile = $context->getDockerFile();

        // Configure user
        $dockerFile->addRun('usermod -u 1000 www-data')
            ->addRun('groupmod -g 1000 www-data');

        // Add PHP runs
        foreach ($context->getConfigures() as $extension => $configure) {
            $dockerFile->addRun("docker-php-ext-configure {$extension} {$configure}");
        }

        foreach ($context->getExtensions(true) as $extension) {
            $dockerFile->addRun("docker-php-ext-install {$extension}");
        }

        // Add composer run
        $dockerFile->addCopy('--from=composer:latest /usr/bin/composer', '/usr/local/bin/composer');
    }
}
