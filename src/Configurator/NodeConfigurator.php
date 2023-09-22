<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Configurator;

use App\Builder\DockerCompose\NodeDockerComposeBuilder;
use App\Dockerizor\AppManager;
use App\Model\Context\App\ComposerAppContext;
use App\Model\Context\App\NodeAppContext;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\NodeBuildContext;
use App\Model\Context\ConsoleContext;
use App\Model\Context\EnvironmentContext;
use App\Model\Docker\DockerRun;
use Symfony\Component\Console\Command\Command;

class NodeConfigurator extends AbstractConfigurator
{
    protected AppManager $appManager;
    protected NodeDockerComposeBuilder $nodeDockerComposeBuilder;

    public function __construct(
        AppManager $appManager,
        NodeDockerComposeBuilder $nodeDockerComposeBuilder
    ) {
        $this->appManager = $appManager;
        $this->nodeDockerComposeBuilder = $nodeDockerComposeBuilder;
    }

    public function run(ConsoleContext $consoleContext, EnvironmentContext $environmentContext, NodeAppContext $nodeAppContext): int
    {
        $output = $consoleContext->getOutput();
        $output->writeln('Configuring Node...');

        $workdir = $this->appManager->getWorkdir();

        $appName = $this->appManager->getConfig('[app_name]');
        $appName = $consoleContext->getQuestionHelper()->ask("App name (eg myproject) ? {$appName} : ", $appName);

        $this->appManager->setConfig('[app_name]', $appName);

        $appBuildContext = new AppBuildContext($appName, $workdir);
        $appBuildContext->setDockerComposeFile($environmentContext->getDockerComposeFile());
        $nodeBuildContext = new NodeBuildContext('lts', 'node:lts-alpine');
        $nodeAppContext->setAppBuildContext($appBuildContext);

        $composerAppContext = $environmentContext->getAppContext(ComposerAppContext::class);
        if ($composerAppContext instanceof ComposerAppContext) {
            $framework = $composerAppContext->getFramework();

            $appBuildContext->setDockerComposeFile($composerAppContext->getAppBuildContext()->getDockerComposeFile());

            if ($framework) {
                $install = null;
                if ($install = $framework->getNodeRunInstallCommand()) {
                    $appBuildContext->addRun(new DockerRun('node:lts', $install));
                }
                if ($build = $framework->getNodeRunBuildCommand()) {
                    $appBuildContext->addRun(new DockerRun('node:lts', $build));
                }
                if ($dev = $framework->getNodeRunDevCommand()) {
                    $command = ($install ? "{$install} && " : '').$dev;
                    $nodeBuildContext->setCommand("sh -c \"{$command}\"");
                }
            }
        }

        $this->nodeDockerComposeBuilder->build($appBuildContext, $nodeBuildContext);

        return Command::SUCCESS;
    }
}
