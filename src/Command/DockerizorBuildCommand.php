<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Builder\AppBuilder;
use App\Builder\DockerFile\OS\DockerFileBuilder;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\BuildContext;
use App\Model\Context\ConsoleContext;
use App\Model\Docker\DockerFile;
use App\Model\OS\Alpine;
use Phar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'dkz:build')]
class DockerizorBuildCommand extends Command
{
    protected AppBuilder $appBuilder;
    protected DockerFileBuilder $dockerFileBuilder;
    protected ParameterBagInterface $parameterBag;

    public function __construct(AppBuilder $appBuilder, DockerFileBuilder $dockerFileBuilder, ParameterBagInterface $parameterBag)
    {
        $this->appBuilder = $appBuilder;
        $this->dockerFileBuilder = $dockerFileBuilder;
        $this->parameterBag = $parameterBag;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleContext = new ConsoleContext($this, $input, $output);

        $workdir = $this->parameterBag->get('kernel.project_dir');

        $buildPhar = false;
        if ($buildPhar) {
            $phardir = $this->parameterBag->get('kernel.project_dir').'/var/build';

            /**
             * Build Phar.
             */
            $pharFile = "$phardir/dockerizor.phar";

            // clean up
            if (file_exists($pharFile)) {
                unlink($pharFile);
            }

            if (file_exists($pharFile.'.gz')) {
                unlink($pharFile.'.gz');
            }

            // create phar
            $phar = new \Phar($pharFile);

            // start buffering. Mandatory to modify stub to add shebang
            $phar->startBuffering();

            // Create the default stub from main.php entrypoint
            $defaultStub = $phar->createDefaultStub('bin/console');

            // Add the rest of the apps files
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->in($workdir)->exclude('app')->files();
            $dirs = [
                '.env' => $workdir.'/.env',
            ];
            foreach ($finder as $dir) {
                $dirs[str_replace("$workdir/", '', $dir)] = $dir->getRealPath();
            }

            $phar->buildFromIterator(new \ArrayIterator($dirs));

            // Customize the stub to add the shebang
            $stub = "#!/usr/bin/env php \n".$defaultStub;

            // Add the stub
            $phar->setStub($stub);

            $phar->stopBuffering();

            // plus - compressing it into gzip
            $phar->compressFiles(\Phar::GZ);

            // Make the file executable
            chmod($pharFile, 0770);
        }

        /**
         * Build Dockerfile.
         */
        $alpine = new Alpine('php:alpine');
        $alpine->addPackage('bash');
        $dockerFile = new DockerFile($alpine);

        $appBuildContext = new AppBuildContext('dockerizor', $workdir);

        $buildContext = new BuildContext();
        $buildContext->setDockerFile($dockerFile);
        $buildContext->setImage('dockerizor:latest');
        $appBuildContext->addBuildContext($buildContext);

        $this->dockerFileBuilder->build($appBuildContext, $buildContext);

        $dockerFile->addCopy('--from=docker /usr/local/bin/*', '/usr/local/bin/');

        $dockerFile->addCopy('.env', '/dockerizor/.env');
        $dockerFile->addCopy('./bin', '/dockerizor/bin');
        $dockerFile->addCopy('./config', '/dockerizor/config');
        $dockerFile->addCopy('./src', '/dockerizor/src');
        $dockerFile->addCopy('./vendor', '/dockerizor/vendor');
        $dockerFile->addEnv('DOCKERIZOR_DOCKER', 'true');

        $dockerFile->addRun('ln -s /dockerizor/bin/console /usr/local/bin/dockerizor');
        $dockerFile->addRun('chmod +x /usr/local/bin/dockerizor');
        $dockerFile->addWorkdir('/app');

        $dockerFile->addCopy('./docker/entrypoint.sh', '/entrypoint.sh');
        $dockerFile->addRun('chmod +x /entrypoint.sh');
        $dockerFile->addEntrypoint('["/entrypoint.sh"]');

        $this->appBuilder->buildApp($consoleContext, $appBuildContext);

        return Command::SUCCESS;
    }
}
