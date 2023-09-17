<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Builder\DockerCompose;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\NodeBuildContext;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Volume;
use Composer\Semver\Comparator;
use Foxy\Converter\SemverConverter;

class NodeDockerComposeBuilder
{
    public function __construct()
    {
        $composerVersion = (new SemverConverter())->convertVersion('1.0.0');
        Comparator::compare($composerVersion, '==', '1.0.0');
    }

    /**
     * Build node service
     * 
     * @param AppBuildContext $appContext
     * @param NodeBuildContext $context
     * 
     * @return Service
     */
    public function build(AppBuildContext $appBuildContext, NodeBuildContext $context): Service
    {
        $service = new Service('node', $context->getImage());
        $service->addLabel('dockerizor.enable', 'true')
            ->addVolume(new Volume('.', '/app'))
            ->setWorkingDir('/app')
            ->setCommandString($context->getCommand())
        ;

        $appBuildContext->getDockerComposeFile()->addService($service);

        return $service;
    }
}
