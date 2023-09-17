<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\ConsoleContext;
use App\Model\Context\EnvironmentContext;
use App\Model\Docker\DockerFile;
use App\Model\File;
use Symfony\Component\Filesystem\Filesystem;

class AppBuilder
{
    protected Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Build environment.
     *
     * @return void
     */
    public function build(ConsoleContext $consoleContext, EnvironmentContext $environmentContext)
    {
        $workdir = $environmentContext->getWorkdir();
        $appContexts = $environmentContext->getAppContexts();

        // Prepare files
        $files = [];
        $files[] = new File('docker-compose.yml', $environmentContext->getDockerComposeFile());
        foreach ($appContexts as $appContext) {
            if ($appContext->getAppBuildContext() instanceof AppBuildContext) {
                $files = array_merge($files, $appContext->getAppBuildContext()->getFiles());
            }
        }

        // Process files
        foreach ($files as $file) {
            $path = $file->getPath();
            $directory = \dirname($path);

            $consoleContext->getOutput()->writeln("Creating {$workdir}/{$path}...");
            if (!$consoleContext->isModeDryRun()) {
                $this->filesystem->mkdir("{$workdir}/{$directory}", 0755);
                $this->filesystem->dumpFile("{$workdir}/{$path}", $file);
            }
        }

        // Execute commands
        foreach ($appContexts as $appContext) {
            $appBuildContext = $appContext->getAppBuildContext();

            // Build docker files
            foreach ($appBuildContext->getBuildContexts() as $context) {
                if ($context->getDockerFile() instanceof DockerFile) {
                    $command = "cd {$workdir} && docker build -f {$workdir}/{$context->getDockerFile()->getPath()} -t {$context->getImage()} .";
                    $consoleContext->getOutput()->writeln("Executing {$command}...");
                    if (!$consoleContext->isModeDryRun()) {
                        shell_exec($command);
                    }
                }
            }

            // Execute docker runs
            foreach ($appBuildContext->getRuns() as $run) {
                $command = "cd {$workdir} && {$run}";
                $consoleContext->getOutput()->writeln("Executing {$command}...");
                if (!$consoleContext->isModeDryRun()) {
                    shell_exec($command);
                }
            }
        }
    }
}
