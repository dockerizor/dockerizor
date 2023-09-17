<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Docker\Client;
use App\Dockerizor\CenterManager as DockerizorManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dkz:test')]
class DockerizorTest extends Command
{
    protected DockerizorManager $dockerizorManager;
    protected Client $dockerClient;

    public function __construct(
        DockerizorManager $dockerizorManager,
        Client $dockerClient,
    ) {
        parent::__construct();

        $this->dockerizorManager = $dockerizorManager;
        $this->dockerClient = $dockerClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dump($this->dockerClient->getVolumes()[0]->getCreatedAt());

        return Command::SUCCESS;
    }
}
