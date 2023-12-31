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

        $appBuildContext->getDockerComposeFile()->addService($service);

        return $service;
    }
}
