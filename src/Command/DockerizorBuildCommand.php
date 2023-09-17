<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Phar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'dkz:build')]
class DockerizorBuildCommand extends Command
{
    protected $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appdir = $this->params->get('kernel.project_dir');
        $workdir = $this->params->get('kernel.project_dir').'/var/build';

        try {
            $pharFile = "$workdir/dockerizor.phar";

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
            $finder->in($appdir)->exclude('app')->files();
            $dirs = [
                '.env' => $appdir.'/.env',
            ];
            foreach ($finder as $dir) {
                $dirs[str_replace("$appdir/", '', $dir)] = $dir->getRealPath();
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

            echo "$pharFile successfully created".\PHP_EOL;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return Command::SUCCESS;
    }
}
