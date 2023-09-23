<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerFile;

use App\Builder\DockerFile\Service\PhpDockerFileBuilder;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\BuildContextInterface;
use App\Model\Context\Build\PhpBuildContext;
use App\Model\Docker\DockerFile;

class DockerFileBuilder
{
    protected array $serviceBuilders = [];
    protected array $osBuilders = [];

    public function __construct(
        PhpDockerFileBuilder $phpDockerFileBuilder,
        AlpineDockerFileBuilder $alpineDockerFileBuilder
    ) {
        $this->serviceBuilders = [
            'php' => $phpDockerFileBuilder,
        ];
        $this->osBuilders = [
            'alpine' => $alpineDockerFileBuilder,
            // 'debian' => new DebianDockerFileBuilder(),
        ];
    }

    /**
     * Build dockerFile.
     */
    public function build(AppBuildContext $appBuildContext, BuildContextInterface $context): DockerFile
    {
        $os = $context->getDockerFile()->getOperatingSystem();

        switch ($context::class) {
            case PhpBuildContext::class:
                $this->serviceBuilders['php']->prepare($appBuildContext, $context);
                break;
        }

        $dockerFile = $this->osBuilders[$os->getName()]->build($context);

        switch ($context::class) {
            case PhpBuildContext::class:
                $this->serviceBuilders['php']->build($appBuildContext, $context);
                break;
        }

        // Set path aand add file to app build context
        $dockerFile->setPath("docker/{$os->getImageName()}/Dockerfile");
        $appBuildContext->addFile($dockerFile);

        return $dockerFile;
    }
}
