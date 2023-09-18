<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Builder\AppBuilder;
use App\Builder\DockerCompose\DatabaseDockerComposeBuilder;
use App\Docker\Client;
use App\Dockerizor\CenterManager;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\DatabaseBuildContext;
use App\Model\Context\ConsoleContext;
use App\Model\Docker\API\Secret as SecretAPI;
use App\Model\Docker\ComposeFile\Secret;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\DockerComposeFile;
use App\Model\Docker\SecretWrapper;
use App\Model\Mode;
use Cocur\Slugify\Slugify;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dkz:center:add:database')]
class DockerizorCenterAddDatabaseCommand extends Command
{
    protected CenterManager $centerManager;
    protected AppBuilder $appBuilder;
    protected DatabaseDockerComposeBuilder $databaseDockerComposeBuilder;
    protected Client $dockerClient;

    protected QuestionHelper $questionHelper;

    public function __construct(
        CenterManager $centerManager,
        AppBuilder $appBuilder,
        DatabaseDockerComposeBuilder $databaseDockerComposeBuilder,
        Client $dockerClient
    ) {
        parent::__construct();

        $this->centerManager = $centerManager;
        $this->appBuilder = $appBuilder;
        $this->databaseDockerComposeBuilder = $databaseDockerComposeBuilder;
        $this->dockerClient = $dockerClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add database dockerizor center')
            ->setHelp('Add database dockerizor center')
            ->addArgument('database', InputArgument::REQUIRED, 'Service system')
            ->addArgument('version', InputArgument::OPTIONAL, 'Service version', 'latest')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password for database, else generate password')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->questionHelper = $this->getHelper('question');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleContext = new ConsoleContext($this, $input, $output);

        $databaseSlug = $input->getArgument('database');
        $version = $input->getArgument('version');
        $password = $input->getOption('password');

        // If database
        if (!\in_array($databaseSlug, ['mysql', 'mariadb', 'postgres', 'mongo'], true)) {
            $output->writeln("Database {$databaseSlug} not supported");

            return Command::FAILURE;
        }

        $this->centerManager->loadConfig();

        $workdir = $this->centerManager->getWorkdir();
        $appBuildContext = new AppBuildContext('center', $workdir);

        $dockerComposeFile = new DockerComposeFile();
        $dockerComposeFile->load("{$workdir}/{$this->getFilename()}");
        $appBuildContext->setDockerComposeFile($dockerComposeFile);

        // Config Service
        $versionSlug = (new Slugify())->slugify($version);
        $dsn = $this->databaseDockerComposeBuilder->createDsnFromSystem($databaseSlug);
        $databaseBuildContext = new DatabaseBuildContext("{$databaseSlug}_{$versionSlug}", $dsn, "{$databaseSlug}:{$version}");

        $this->databaseDockerComposeBuilder->build($appBuildContext, $databaseBuildContext);

        $this->appBuilder->buildApp($consoleContext, $appBuildContext);

        return Command::SUCCESS;
    }

    /**
     * Create secret.
     *
     * @return string
     */
    protected function createSecretForService(DockerComposeFile $dockerComposeFile, Service $database, string $name, string $password = null): SecretWrapper
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
            $password = $password ?? $this->centerManager->generatePassword();
            $this->dockerClient->createSecret($name, $password);
            $secret = $this->dockerClient->getSecret($name);
        }

        $database->addSecret($name);
        $dockerComposeFile->addSecret(new Secret($name, true));

        return new SecretWrapper($secret, $password);
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
