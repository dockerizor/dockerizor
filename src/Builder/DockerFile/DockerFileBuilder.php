<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerFile;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\BuildContextInterface;
use App\Model\Docker\DockerFile;

class DockerFileBuilder
{
    protected array $builders = [];

    public function __construct()
    {
        $this->builders = [
            'alpine' => new AlpineDockerFileBuilder(),
            // 'debian' => new DebianDockerFileBuilder(),
        ];
    }

    /**
     * Build dockerFile.
     */
    public function build(AppBuildContext $appBuildContext, BuildContextInterface $context): DockerFile
    {
        $os = $context->getDockerFile()->getOperatingSystem();

        $dockerFile = $this->builders[$os->getName()]->build($context);

        // Set path aand add file to app build context
        $dockerFile->setPath("docker/{$os->getImageName()}/Dockerfile");
        $appBuildContext->addFile($dockerFile);

        return $dockerFile;
    }
}
