<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerCompose;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\WebBuildContext;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Network;
use App\Model\Docker\ComposeFile\Service\Port;
use App\Model\Docker\ComposeFile\Service\Volume;
use App\Model\File;

class WebDockerComposeBuilder extends DockerComposeBuilder
{
    /**
     * Build web service.
     */
    public function build(AppBuildContext $appBuildContext, WebBuildContext $context): Service
    {
        // Create service
        $appName = $appBuildContext->getAppName();
        $service = new Service('web', 'nginx:alpine');
        $service->addLabel('dockerizor.enable', 'true')
            ->addVolume(new Volume('.', '/var/www/html'));

        if ($appBuildContext->getProxyContainer()) {
            // Add proxy labels
            $service->setDeploy((new Service\Deploy())->setLabels([
                'traefik.enable' => 'true',
                "traefik.http.routers.{$appName}-web.rule" => "Host(`{$appBuildContext->getDomain()}`)",
                "traefik.http.routers.{$appName}-web.entrypoints" => 'http',
                "traefik.http.services.{$appName}.loadbalancer.server.port" => $context->getPort(),
            ]));

            // Add proxy network
            $service->addNetwork((new Network($appBuildContext->getFrontendNetwork()))->addAlias("{$appName}-web"));
        } else {
            // Set port
            $service->addPort(new Port($context->getPort(), 80));
        }

        // Set Nginx conf
        $service->addVolume(new Volume('./docker/nginx/default.conf', '/etc/nginx/conf.d/default.conf'));
        $appBuildContext->addFile(new File('docker/nginx/default.conf', $this->getNginxTemplate($appBuildContext, $context)));

        $appBuildContext->getDockerComposeFile()->addService($service);

        return $service;
    }

    /**
     * Get Nginx template.
     */
    protected function getNginxTemplate(AppBuildContext $appBuildContext, WebBuildContext $context): string
    {
        $mustache = new \Mustache_Engine(['entity_flags' => \ENT_QUOTES]);

        return $mustache->render(
            $this->fileRepository->getNginxTemplate(),
            [
                'root_directory' => $context->getRootDir(),
                'php_fpm' => "{$appBuildContext->getAppName()}-php",
            ]
        );
    }
}
