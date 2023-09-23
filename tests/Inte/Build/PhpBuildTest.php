<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Inte\Build;

use App\Builder\DockerFile\DockerFileBuilder;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\PhpBuildContext;
use App\Model\Docker\DockerFile;
use App\Model\OS\Alpine;
use App\Model\Process\Process;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PhpBuildTest extends KernelTestCase
{
    /**
     * Test php extension build.
     *
     * @dataProvider providerTestPhpExtensionBuild
     */
    public function testPhpBuild(string $extension)
    {
        self::bootKernel();

        $container = static::getContainer();
        $workdir = $container->get(ParameterBagInterface::class)->get('kernel.cache_dir');
        $dockerFileBuilder = $container->get(DockerFileBuilder::class);

        // Prepare
        $operatingSystem = new Alpine('php:alpine');
        $dockerFile = new DockerFile($operatingSystem);

        $appBuildContext = new AppBuildContext('test', 'test');
        $phpBuildContext = new PhpBuildContext();
        $phpBuildContext->addExtension($extension);
        $phpBuildContext->setDockerFile($dockerFile);

        if (empty($phpBuildContext->getExtensions(true))) {
            $this->assertTrue(true, "Extension {$extension} is already installed on image.");
        }

        $operatingSystem->addPackagesFromPhpBuildContext($phpBuildContext);

        $dockerFileBuilder->build($appBuildContext, $phpBuildContext);

        $dockerFile = $phpBuildContext->getDockerFile();

        $filesys = new Filesystem();
        $filesys->dumpFile("{$workdir}/{$dockerFile->getPath()}", $dockerFile);

        // Exec
        $process = new Process("docker build -f {$workdir}/{$dockerFile->getPath()} -t dockerizor-test .", null, $workdir);
        $process->setShowOutput(false)->setSaveOutErr(true)->run();

        // Assert
        $outputLines = $process->getOutputLines();
        $output = array_filter($outputLines);
        $output = array_values($output);
        $lastOutputLine = $output[\count($output) - 1];

        $this->assertStringContainsString(' DONE ', $lastOutputLine, implode("\n", $outputLines));
    }

    public function providerTestPhpExtensionBuild()
    {
        $phpBuildContext = new PhpBuildContext();
        $phpBuildContext->setDockerFile(new DockerFile(new Alpine('php:alpine')));

        foreach ($phpBuildContext->getAvailableExtensions() as $extension) {
            yield [$extension];
        }
    }
}
