<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Builder\DockerCompose\DatabaseDockerComposeBuilder;
use App\Docker\Client;
use App\Dockerizor\CenterManager;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Deploy;
use App\Model\Docker\ComposeFile\Service\Network as ServiceNetwork;
use App\Model\Docker\DockerComposeFile;
use App\Model\Mode;
use Cocur\Slugify\Slugify;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'dkz:center:add:service')]
class DockerizorCenterAddServiceCommand extends Command
{
    protected CenterManager $centerManager;
    protected DatabaseDockerComposeBuilder $databaseDockerComposeBuilder;
    protected Client $dockerClient;

    protected QuestionHelper $questionHelper;

    public function __construct(
        CenterManager $centerManager,
        DatabaseDockerComposeBuilder $databaseDockerComposeBuilder,
        Client $dockerClient
    ) {
        parent::__construct();

        $this->centerManager = $centerManager;
        $this->databaseDockerComposeBuilder = $databaseDockerComposeBuilder;
        $this->dockerClient = $dockerClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add service dockerizor center')
            ->setHelp('Add service dockerizor center')
            ->addArgument('service', InputArgument::REQUIRED, 'Service name')
            ->addArgument('version', InputArgument::OPTIONAL, 'Service version', 'latest')
            ->addOption('backend', null, InputOption::VALUE_NONE, 'Add service to backend network')
            ->addOption('frontend', null, InputOption::VALUE_NONE, 'Add service to frontend network')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->questionHelper = $this->getHelper('question');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serviceSlug = $input->getArgument('service');
        $version = $input->getArgument('version');
        $backend = $input->getOption('backend');
        $frontend = $input->getOption('frontend');

        $this->centerManager->loadConfig();
        $wildcard = $this->centerManager->getConfig('[wildcard]');

        $workdir = $this->centerManager->getWorkdir();

        $dockerComposeFile = new DockerComposeFile('3.8');
        $dockerComposeFile->load("{$workdir}/{$this->getFilename()}");

        $hasWebserver = false;

        // Config Service
        $versionSlug = (new Slugify())->slugify($version);
        $service = new Service("{$serviceSlug}_{$versionSlug}", "{$serviceSlug}:{$version}");

        switch ($serviceSlug) {
            // Database management
            case 'phpmyadmin':
                $service->setImage("phpmyadmin/phpmyadmin:{$version}");
                $backend = $frontend = true;
                $hasWebserver = true;
                $service->addEnvironmentVariable('PMA_ARBITRARY', 1);
                break;
            case 'adminer':
                $backend = $frontend = true;
                $hasWebserver = true;
                break;
            case 'kibana':
                $backend = $frontend = true;
                $hasWebserver = true;
                break;
                // Docker management
            case 'portainer':
                $service->setImage("portainer/portainer-ce:{$version}");
                $backend = $frontend = true;
                $hasWebserver = true;
                break;
                // Database
            case 'redis':
                $backend = true;
                break;
            case 'memcached':
                $backend = true;
                break;
            case 'elasticsearch':
                $backend = true;
                break;
                // Message broker
            case 'rabbitmq':
                $backend = true;
                break;
                // Mail server
            case 'mailhog':
                $service->setImage("mailhog/mailhog:{$version}");
                $backend = $frontend = true;
                $hasWebserver = true;
                break;
        }

        if ($frontend) {
            $service->addNetwork(new ServiceNetwork(CenterManager::NETWORK_FRONTEND_NAME, "{$serviceSlug}_{$versionSlug}"));
        }
        if ($backend) {
            $service->addNetwork(new ServiceNetwork(CenterManager::NETWORK_BACKEND_NAME, "{$serviceSlug}_{$versionSlug}"));
        }

        $service->addLabel('dockerizor.enable', 'true');

        if ($hasWebserver) {
            $service->setDeploy(new Deploy([
                'traefik.enable' => 'true',
                'traefik.http.routers.'.$serviceSlug.'-web.rule' => "Host(`{$serviceSlug}.{$wildcard}`)",
                'traefik.http.routers.'.$serviceSlug.'-web.entrypoints' => 'http',
                'traefik.http.services.'.$serviceSlug.'.loadbalancer.server.port' => 80,
            ]));
        }

        $dockerComposeFile->addService($service);

        // Write docker-compose file
        $filesystem = new Filesystem();
        $filesystem->dumpFile("{$workdir}/{$this->getFilename()}", $dockerComposeFile);

        return Command::SUCCESS;
    }

    /**
     * Get filename.
     */
    protected function getFilename(): string
    {
        if (Mode::app === $this->centerManager->getMode()) {
            return 'docker-compose-center.yml';
        }

        return 'docker-compose.yml';
    }
}
