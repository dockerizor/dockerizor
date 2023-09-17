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
use App\Model\Docker\API\Secret as SecretAPI;
use App\Model\Docker\ComposeFile\Secret;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Deploy;
use App\Model\Docker\ComposeFile\Service\Network as ServiceNetwork;
use App\Model\Docker\ComposeFile\Service\Volume as ServiceVolume;
use App\Model\Docker\ComposeFile\Volume;
use App\Model\Docker\DockerComposeFile;
use App\Model\Docker\SecretWrapper;
use App\Model\Docker\VolumeWrapper;
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

#[AsCommand(name: 'dkz:center:add')]
class DockerizorCenterAddCommand extends Command
{
    protected DockerizorManager $dockerizorManager;
    protected DockerClient $dockerClient;

    protected QuestionHelper $questionHelper;

    public function __construct(
        DockerizorManager $dockerManager,
        DockerClient $dockerClient
    ) {
        parent::__construct();

        $this->dockerizorManager = $dockerManager;
        $this->dockerClient = $dockerClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add service dockerizor center')
            ->setHelp('Add service dockerizor center')
            ->addArgument('service', InputArgument::REQUIRED, 'Service name')
            ->addArgument('version', InputArgument::OPTIONAL, 'Service version', 'latest')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password for service, else generate password')
            ->addOption('secret', null, InputOption::VALUE_REQUIRED, 'Use existing secret')
            ->addOption('backend', null, InputOption::VALUE_NONE, 'Add service to backend network')
            ->addOption('frontend', null, InputOption::VALUE_NONE, 'Add service to frontend network')
            ->addOption('secret-method', null, InputOption::VALUE_REQUIRED, 'Secret method (secret, vault)', 'secret')
            ->addOption('volume', null, InputOption::VALUE_REQUIRED, 'Volume (eg /var/data:/data, var-data:/data)')
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
        $password = $input->getOption('password');
        $backend = $input->getOption('backend');
        $frontend = $input->getOption('frontend');
        $secretExists = $input->getOption('secret');
        $secretMethod = $input->getOption('secret-method');
        $volumeString = $input->getOption('volume');

        $this->dockerizorManager->loadConfig();
        $wildcard = $this->dockerizorManager->getConfig('[wildcard]');

        $volume = $volumeFrom = $volumeTo = null;
        if ($volumeString) {
            if (!str_contains($volumeString, ':')) {
                // Unique volume
                if (str_contains($volumeString, '/')) {
                    // Volume path /var/data
                    $volume = new VolumeWrapper($volumeString, $volumeString);
                } else {
                    // Volume name var-data
                    $volumeFrom = $volumeString;
                }
                $volume = null;
            } else {
                // Volume from:to
                list($volumeFrom, $volumeTo) = explode(':', $volumeString);

                if ('' === $volumeFrom || '' === $volumeTo) {
                    $output->writeln('<error>Volume must be in format: /var/data:/data</error>');

                    return Command::FAILURE;
                }

                $volume = new VolumeWrapper($volumeFrom, $volumeTo);
            }
        }

        $workdir = $this->dockerizorManager->getWorkdir();

        $dockerComposeFile = new DockerComposeFile('3.8');
        $dockerComposeFile->load("{$workdir}/{$this->getFilename()}");

        $secret = $secretName = null;
        $hasWebserver = false;

        // Config Service
        $versionSlug = (new Slugify())->slugify($version);
        $service = new Service("{$serviceSlug}_{$versionSlug}", "{$serviceSlug}:{$version}");

        if ($secretExists) {
            $secret = $this->useSecretForService($dockerComposeFile, $service, $secretExists);
            $service->addLabel('dockerizor.secret', $secretExists);
        }

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
            case 'mysql':
                $backend = true;
                $secretName = $secretName ?? "mysql_{$versionSlug}_root_password";
                $service->addEnvironmentVariable('MYSQL_ROOT_PASSWORD_FILE', "/run/secrets/{$secretName}");
                $volume = new VolumeWrapper($volumeFrom ?? "{$serviceSlug}_{$versionSlug}", '/var/lib/mysql');
                break;
            case 'mariadb':
                $backend = true;
                $secretName = $secretName ?? "mariadb_{$versionSlug}_root_password";
                $service->addEnvironmentVariable('MYSQL_ROOT_PASSWORD_FILE', "/run/secrets/{$secretName}");
                $volume = new VolumeWrapper($volumeFrom ?? "{$serviceSlug}_{$versionSlug}", '/var/lib/mysql');
                break;
            case 'postgres':
                $backend = true;
                $secretName = $secretName ?? "postgres_{$versionSlug}_root_password";
                $service->addEnvironmentVariable('POSTGRES_PASSWORD_FILE', "/run/secrets/{$secretName}");
                $volume = new VolumeWrapper($volumeFrom ?? "{$serviceSlug}_{$versionSlug}", '/var/lib/postgresql/data');
                break;
            case 'mongo':
                $backend = true;
                $volume = new VolumeWrapper($volumeFrom ?? "{$serviceSlug}_{$versionSlug}", '/data/db');
                break;
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

        if ($volume) {
            if (!$this->dockerClient->getVolume($volume->getFrom())) {
                $this->dockerClient->createVolume($volume->getFrom());
            }
            $service->addVolume(new ServiceVolume($volume->getFrom(), $volume->getTo()));
            $dockerComposeFile->addVolume(new Volume($volume->getFrom(), true));
        }

        if ($secretName) {
            $secret = $this->createSecretForService($dockerComposeFile, $service, $secretName, $password);
            $service->addLabel('dockerizor.secret.method', $secretMethod);
            $service->addLabel('dockerizor.secret.name', $secretName);
        }

        if ($frontend) {
            $service->addNetwork(new ServiceNetwork(DockerizorManager::NETWORK_FRONTEND_NAME, "{$serviceSlug}_{$versionSlug}"));
        }
        if ($backend) {
            $service->addNetwork(new ServiceNetwork(DockerizorManager::NETWORK_BACKEND_NAME, "{$serviceSlug}_{$versionSlug}"));
        }

        if ($secret instanceof SecretWrapper) {
            if ($secret->getPassword()) {
                $output->writeln("Secret {$secretName} created for service {$serviceSlug}:{$versionSlug}");
                $output->writeln("<error>Dockerizor Center use Docker secret, if you want to use with an other way, store password in secure : {$secret->getPassword()}</error>");
            } else {
                $output->writeln("Use existing secret {$secretName} for service {$serviceSlug}:{$versionSlug}");
            }
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
     * Use secret.
     */
    protected function useSecretForService(DockerComposeFile $dockerComposeFile, Service $service, string $name): SecretWrapper
    {
        $secret = $this->dockerClient->getSecret($name);

        if ($secret instanceof SecretAPI) {
            $service->addSecret($name);
            $dockerComposeFile->addSecret(new Secret($name, true));
        }

        return new SecretWrapper($secret);
    }

    /**
     * Create secret.
     *
     * @return string
     */
    protected function createSecretForService(DockerComposeFile $dockerComposeFile, Service $service, string $name, string $password = null): SecretWrapper
    {
        $secrets = $this->dockerClient->getSecrets();

        $secret = null;
        foreach ($secrets as $secretCheck) {
            if ($secretCheck->getName() === $name) {
                $secret = $secretCheck;
                break;
            }
        }

        if (!$secret instanceof SecretAPI) {
            $password = $password ?? $this->dockerizorManager->generatePassword();
            $this->dockerClient->createSecret($name, $password);
            $secret = $this->dockerClient->getSecret($name);
        }

        $service->addSecret($name);
        $dockerComposeFile->addSecret(new Secret($name, true));

        return new SecretWrapper($secret, $password);
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
