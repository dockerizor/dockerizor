<?php

namespace App\Builder\DockerFile\Service;

use App\Model\Context\Build\PhpBuildContext;
use App\Model\Context\Build\AppBuildContext;

class PhpDockerFileBuilder
{
    /**
     * Build php service.
     */
    public function build(AppBuildContext $appBuildContext, PhpBuildContext $context): void
    {
        // Process dockerFile
        $dockerFile = $context->getDockerFile();

        // Configure user
        $dockerFile->getOperatingSystem()->addPackage('shadow');
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
