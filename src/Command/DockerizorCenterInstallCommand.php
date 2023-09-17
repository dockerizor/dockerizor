<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Docker\SocketClient as DockerClient;
use App\Dockerizor\CenterManager as DockerizorManager;
use App\Model\Docker\ComposeFile\Network;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Deploy;
use App\Model\Docker\ComposeFile\Service\Network as ServiceNetwork;
use App\Model\Docker\ComposeFile\Service\Port;
use App\Model\Docker\ComposeFile\Service\Volume as ServiceVolume;
use App\Model\Docker\DockerComposeFile;
use App\Model\Mode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'dkz:center:install')]
class DockerizorCenterInstallCommand extends Command
{
    protected DockerizorManager $dockerizorManager;
    protected DockerClient $dockerClient;

    public function __construct(
        DockerizorManager $dockerizorManager,
        DockerClient $dockerClient,
    ) {
        parent::__construct();

        $this->dockerizorManager = $dockerizorManager;
        $this->dockerClient = $dockerClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Install Dockerizor Center')
            ->addArgument('wildcard', InputArgument::REQUIRED, 'domain wildcard (e.g. portal.me for *.portal.me)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get wildcard
        $wildcard = $input->getArgument('wildcard');
        if (!filter_var($wildcard, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME)) {
            $output->writeln('Invalid wildcard');

            return Command::FAILURE;
        }

        $this->dockerizorManager->loadConfig();
        $this->dockerizorManager->setConfig('[wildcard]', $wildcard);

        // check if docker is running
        $output->writeln('Checking if docker is running');
        if (!$this->dockerClient->isRunning()) {
            $output->writeln('Docker is not running');

            return Command::FAILURE;
        }

        // Get workdir
        $workdir = $this->dockerizorManager->getWorkdir();

        // Get Docker networks
        $networks = $this->dockerClient->getNetworks();

        // Check if dockerizor networks exists
        $frontendExists = false;
        $backendExists = false;
        foreach ($networks as $network) {
            if (
                DockerizorManager::NETWORK_FRONTEND_NAME === $network['Name']
                && isset($network['Labels'][DockerizorManager::NETWORK_FRONTEND_LABEL])
            ) {
                $frontendExists = true;
            }
            if (
                DockerizorManager::NETWORK_BACKEND_NAME === $network['Name']
                && isset($network['Labels'][DockerizorManager::NETWORK_BACKEND_LABEL])
            ) {
                $backendExists = true;
            }
        }

        // Create dockerizor networks
        if (!$frontendExists) {
            $output->writeln('Creating dockerizor-frontend network');

            $this->dockerClient->createNetwork(DockerizorManager::NETWORK_FRONTEND_NAME, 'overlay', [
                DockerizorManager::NETWORK_FRONTEND_LABEL => 'true',
            ]);
        }

        if (!$backendExists) {
            $output->writeln('Creating dockerizor-backend network');

            $this->dockerClient->createNetwork(DockerizorManager::NETWORK_BACKEND_NAME, 'overlay', [
                DockerizorManager::NETWORK_BACKEND_LABEL => 'true',
            ]);
        }

        // Create traefik service
        $output->writeln('Creating traefik service');
        $dockerComposeFile = new DockerComposeFile('3.8');
        $dockerComposeFile->load("{$workdir}/{$this->getFilename()}");

        $traefikService = $dockerComposeFile->getService('traefik') ?? new Service('traefik', 'traefik:latest');
        $traefikService
            ->setCommands([
                '--providers.docker',
                '--providers.docker.network=dockerizor-frontend',
                '--providers.docker.exposedByDefault=false',
                '--providers.docker.swarmMode=true',
                '--entrypoints.http.address=:80',
                '--accesslog',
                '--log',
                '--api',
                '--api.insecure=true',
            ])
            ->addPorts([
                new Port(80, 80),
            ])
            ->addVolume(new ServiceVolume('/var/run/docker.sock', '/var/run/docker.sock'))
            ->addNetwork(new ServiceNetwork(DockerizorManager::NETWORK_FRONTEND_NAME, 'traefik'))
            ->addLabel('dockerizor.wildcard', $wildcard)
            ->setDeploy(new Deploy([
                    'traefik.enable' => 'true',
                    'traefik.docker.network' => DockerizorManager::NETWORK_FRONTEND_NAME,
                    'traefik.http.routers.traefik-public-http.rule' => "Host(`traefik.{$wildcard}`)",
                    'traefik.http.routers.traefik-public-http.entrypoints' => 'http',
                    'traefik.http.services.traefik-public.loadbalancer.server.port' => '8080',
                ],
            ))
        ;
        $dockerComposeFile->addService($traefikService);

        $dockerComposeFile->addNetwork(new Network(DockerizorManager::NETWORK_FRONTEND_NAME, null, true));
        $dockerComposeFile->addNetwork(new Network(DockerizorManager::NETWORK_BACKEND_NAME, null, true));

        $this->dockerizorManager->saveConfig();

        // Write file
        $filesystem = new Filesystem();
        $filesystem->dumpFile("{$workdir}/{$this->getFilename()}", $dockerComposeFile);

        return Command::SUCCESS;
    }

    /**
     * Get filename.
     */
    protected function getFilename(): string
    {
        if (Mode::app === $this->dockerizorManager->getMode()) {
            return 'docker-compose-center.yml';
        }

        return 'docker-compose.yml';
    }
}
