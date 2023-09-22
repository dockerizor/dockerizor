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
use App\Model\FileInterface;
use App\Model\Process\Process;
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
    public function buildEnv(ConsoleContext $consoleContext, EnvironmentContext $environmentContext)
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

        $this->buildFiles($consoleContext, $files, $workdir);

        // Execute commands
        foreach ($appContexts as $appContext) {
            if (($appBuildContext = $appContext->getAppBuildContext()) instanceof AppBuildContext) {
                // Build docker files
                $this->processDockerFile($consoleContext, $appBuildContext, $workdir);

                // Execute docker runs
                $this->processRuns($consoleContext, $appBuildContext, $workdir);
            }
        }
    }

    public function buildApp(ConsoleContext $consoleContext, AppBuildContext $appBuildContext)
    {
        $workdir = $appBuildContext->getWorkdir();
        $this->buildFiles($consoleContext, $appBuildContext->getFiles(), $workdir);
        $this->processDockerFile($consoleContext, $appBuildContext, $workdir);
        $this->processRuns($consoleContext, $appBuildContext, $workdir);
    }

    protected function buildDockerFile(ConsoleContext $consoleContext, DockerFile $dockerFile, string $tag, string $workdir)
    {
        $command = "docker build -f {$workdir}/{$dockerFile->getPath()} -t {$tag} .";
        $consoleContext->getOutput()->writeln("Executing {$command}...");
        if (!$consoleContext->isModeDryRun()) {
            $process = new Process($command, $consoleContext->getOutput(), $workdir);
            $process->run();
        }
    }

    protected function buildFile(ConsoleContext $consoleContext, FileInterface $file, string $workdir)
    {
        $path = $file->getPath();
        $directory = \dirname($path);

        $consoleContext->getOutput()->writeln("Creating {$workdir}/{$path}...");
        if (!$consoleContext->isModeDryRun()) {
            if (!$this->filesystem->exists("{$workdir}/{$directory}")) {
                $this->filesystem->mkdir("{$workdir}/{$directory}", 0755);
            }
            $this->filesystem->dumpFile("{$workdir}/{$path}", $file);
        }
    }

    protected function buildFiles(ConsoleContext $consoleContext, array $files, string $workdir)
    {
        // Process files
        foreach ($files as $file) {
            if ($file instanceof FileInterface) {
                $this->buildFile($consoleContext, $file, $workdir);
            }
        }
    }

    protected function processDockerFile(ConsoleContext $consoleContext, AppBuildContext $appBuildContext, string $workdir)
    {
        foreach ($appBuildContext->getBuildContexts() as $context) {
            if ($context->getDockerFile() instanceof DockerFile) {
                $this->buildDockerFile($consoleContext, $context->getDockerFile(), $context->getImage(), $workdir);
            }
        }
    }

    protected function processRuns(ConsoleContext $consoleContext, AppBuildContext $appBuildContext, string $workdir)
    {
        foreach ($appBuildContext->getRuns() as $command) {
            $consoleContext->getOutput()->writeln("Executing {$command}...");
            if (!$consoleContext->isModeDryRun()) {
                $process = new Process($command, $consoleContext->getOutput(), $workdir);
                $process->run();
            }
        }
    }
}
