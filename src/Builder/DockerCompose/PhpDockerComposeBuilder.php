<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerCompose;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\DatabaseBuildContext;
use App\Model\Context\Build\PhpBuildContext;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Network;
use App\Model\Docker\ComposeFile\Service\Volume;
use App\Model\File;

class PhpDockerComposeBuilder extends DockerComposeBuilder
{
    /**
     * Build php service.
     */
    public function build(AppBuildContext $appBuildContext, PhpBuildContext $context): Service
    {
        $appName = $appBuildContext->getAppName();
        $service = new Service('php-fpm', $context->getImage());
        $service->addLabel('dockerizor.enable', 'true')
            ->setBuild('docker/php')
            ->addVolume(new Volume('.', '/var/www/html'))
            ->addVolume(new Volume('./docker/php/custom.ini', '/usr/local/etc/php/conf.d/custom.ini'))
        ;

        // Set networks for proxy
        if ($appBuildContext->getProxyContainer()) {
            $service->addNetwork((new Network($appBuildContext->getFrontendNetwork()))->addAlias("{$appName}-php"));
            $service->addNetwork((new Network($appBuildContext->getBackendNetwork()))->addAlias("{$appName}-php"));
        }

        $appBuildContext->addFile(new File('docker/php/custom.ini', ''));

        $databaseBuildContext = $appBuildContext->getBuildContext(DatabaseBuildContext::class);
        if (
            $databaseBuildContext instanceof DatabaseBuildContext
            && ($secret = $databaseBuildContext->getSecret())
        ) {
            $service->addSecret($secret);
        }

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

        $appBuildContext->getDockerComposeFile()->addService($service);

        return $service;
    }
}
