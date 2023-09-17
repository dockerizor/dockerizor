<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
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
     * Build php service
     * 
     * @param AppBuildContext $appBuildContext
     * @param PhpBuildContext $context
     * 
     * @return Service
     */
    public function build(AppBuildContext $appBuildContext, PhpBuildContext $context): Service
    {
        $appName = $appBuildContext->getAppName();
        $service = new Service('php-fpm', $context->getImage());
        $service->addLabel('dockerizor.enable', 'true')
            ->setBuild('docker/php')
            ->addVolume(new Volume('.', '/var/www/html'))
            ->addVolume(new Volume('docker/php/php.ini', '/usr/local/etc/php/conf.d'))
        ;

        // Set networks for proxy
        if ($appBuildContext->getProxyContainer()) {
            $service->addNetwork((new Network($appBuildContext->getFrontendNetwork()))->addAlias("{$appName}-php"));
            $service->addNetwork((new Network($appBuildContext->getBackendNetwork()))->addAlias("{$appName}-php"));
        }

        $appBuildContext->addFile(new File('docker/php/php.ini', ''));

        $databaseBuildContext = $appBuildContext->getBuildContext(DatabaseBuildContext::class);
        if (
            $databaseBuildContext instanceof DatabaseBuildContext
            && ($secret = $databaseBuildContext->getSecret())
        ) {
            $service->addSecret($secret);
        }

        // Process dockerFile
        $dockerFile = $context->getDockerFile();

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
