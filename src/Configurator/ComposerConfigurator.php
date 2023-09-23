<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Configurator;

use App\Builder\AppBuilder;
use App\Builder\DockerCompose\DatabaseDockerComposeBuilder;
use App\Builder\DockerCompose\PhpDockerComposeBuilder;
use App\Builder\DockerCompose\WebDockerComposeBuilder;
use App\Builder\DockerFile\DockerFileBuilder;
use App\Composer\Client as ComposerClient;
use App\Docker\Client;
use App\Dockerizor\AppManager;
use App\Dockerizor\CenterManager;
use App\Model\Context\App\ComposerAppContext;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\DatabaseBuildContext;
use App\Model\Context\Build\PhpBuildContext;
use App\Model\Context\Build\WebBuildContext;
use App\Model\Context\ConsoleContext;
use App\Model\Context\EnvironmentContext;
use App\Model\Docker\ComposeFile\Secret;
use App\Model\Docker\DockerFile;
use App\Model\Docker\DockerRun;
use App\Model\DotenvFile;
use App\Model\OS\Alpine;
use Symfony\Component\Console\Command\Command;

class ComposerConfigurator extends AbstractConfigurator
{
    protected AppManager $appManager;
    protected CenterManager $centerManager;
    protected AppBuilder $appBuilder;
    protected PhpDockerComposeBuilder $phpDockerComposeBuilder;
    protected WebDockerComposeBuilder $webDockerComposeBuilder;
    protected DatabaseDockerComposeBuilder $databaseDockerComposeBuilder;
    protected DockerFileBuilder $dockerFileBuilder;
    protected Client $dockerClient;
    protected ComposerClient $composerClient;

    public function __construct(
        AppManager $appManager,
        CenterManager $centerManager,
        AppBuilder $appBuilder,
        PhpDockerComposeBuilder $phpDockerComposeBuilder,
        WebDockerComposeBuilder $webDockerComposeBuilder,
        DatabaseDockerComposeBuilder $databaseDockerComposeBuilder,
        DockerFileBuilder $dockerFileBuilder,
        Client $dockerClient,
        ComposerClient $composerClient
    ) {
        $this->appManager = $appManager;
        $this->centerManager = $centerManager;
        $this->appBuilder = $appBuilder;
        $this->phpDockerComposeBuilder = $phpDockerComposeBuilder;
        $this->webDockerComposeBuilder = $webDockerComposeBuilder;
        $this->databaseDockerComposeBuilder = $databaseDockerComposeBuilder;
        $this->dockerFileBuilder = $dockerFileBuilder;
        $this->dockerClient = $dockerClient;
        $this->composerClient = $composerClient;
    }

    public function run(ConsoleContext $consoleContext, EnvironmentContext $environmentContext, ComposerAppContext $composerAppContext): int
    {
        $output = $consoleContext->getOutput();

        $output->writeln('Start dockerization');
        // Init configuration vars
        $this->appManager->loadConfig();
        $workdir = $environmentContext->getWorkdir();

        /*
         * Load composer data
         */
        $output->writeln('Get composer data');
        $this->composerClient->setWorkdir($workdir);

        // Get composer suggestions
        $extentionSuggestions = $this->composerClient->getExtensionSuggetions();

        // Get PHP version
        $phpVersion = $this->composerClient->getPhpVersionRequirement();
        $phpBuildContext = new PhpBuildContext($phpVersion);
        $phpMinorVersion = $phpBuildContext->getMinorVersion();

        // Get composer requirements
        $extensions = $this->composerClient->getExtensionRequirements();
        $phpBuildContext->addExtensions($extensions);

        $extraExtensions = $this->appManager->getConfig('[extra_extensions]');
        $phpBuildContext->addExtensions($extraExtensions);

        $operatingSystem = new Alpine("php:{$phpMinorVersion}-fpm-alpine");

        /*
         * Load environment data
         */
        $output->writeln('Load environment data');
        // Get data from .env file
        $dsn = $dotenvFile = null;

        $dotenvFile = new DotenvFile("{$workdir}/.env");
        $dotenvFile->load();

        // Configure app name
        $appName = $this->appManager->getConfig('[app_name]') ?? basename($workdir);
        $appName = $consoleContext->getQuestionHelper()->ask("App name (eg myproject) ? {$appName} : ", $appName);

        $this->appManager->setConfig('[app_name]', $appName);

        $appBuildContext = new AppBuildContext($appName, $workdir);
        $phpBuildContext->setImage("{$appName}-php:latest");
        $appBuildContext->setDockerComposeFile($environmentContext->getDockerComposeFile());
        $composerAppContext->setAppBuildContext($appBuildContext);

        // Configure root directory
        $framework = $composerAppContext->getFramework();
        if ($framework) {
            $output->writeln("App detected {$framework->getName()}");
            $rootDirectory = $framework->getRootDirectory();
        } else {
            $rootDirectory = $this->appManager->getConfig('[root_directory]');

            if ($consoleContext->isModeInteractive()) {
                $rootDirectory = '/var/www/html/'.$consoleContext->getQuestionHelper()->ask('Root directory /var/www/html/[app_root] ? : /var/www/html/', str_replace('/var/www/html/', '', $rootDirectory));
            } elseif (null === $rootDirectory) {
                $rootDirectory = '/var/www/html';
            }
        }

        $webBuildContext = new WebBuildContext($rootDirectory);

        $this->appManager->setConfig('[root_directory]', $rootDirectory);
        $output->writeln("Set root directory {$rootDirectory}");

        // Get networks
        $networks = $this->centerManager->getNetworks();

        $frontendNetworkName = $backendNetworkName = null;
        if (isset($networks['frontend'])) {
            $frontendNetworkName = $networks['frontend'];
            $output->writeln("Frontend network {$frontendNetworkName} detected");
            $appBuildContext->setFrontendNetwork($frontendNetworkName);
        }
        if (isset($networks['backend'])) {
            $backendNetworkName = $networks['backend'];
            $output->writeln("Backend network {$backendNetworkName} detected");
            $appBuildContext->setBackendNetwork($backendNetworkName);
        }

        // Detect Dockerizor Center
        $output->writeln('Detect Dockerizor Center');
        $usedPorts = $this->centerManager->getUsedPorts();

        // Configure database
        $databaseDockerImage = null;

        // Get database system
        $databaseSystem = $this->appManager->getConfig('[database_system]');
        if (!$databaseSystem) {
            $databaseSystem = $consoleContext->getQuestionHelper()->choice('Database system', [
                'mysql' => 'mysql',
                'mariadb' => 'mariadb',
                'postgresql' => 'postgresql',
                'sqlite' => 'sqlite',
                null => 'no database',
            ], false);
        }

        $databaseBuildContext = null;
        if ($databaseSystem) {
            $this->appManager->setConfig('[database_system]', $databaseSystem);
            $dsn = $this->databaseDockerComposeBuilder->createDsnFromSystem($databaseSystem);
            $dsn->setDatabase($this->appManager->getConfig('[database_name]') ?? strtolower($appName));
            $this->appManager->setConfig('[database_name]', $dsn->getDatabase());

            $output->writeln("Config database system {$databaseSystem}");

            $phpBuildContext->configureDatabase($dsn->getDriver());

            // Get database docker image
            if (!$databaseDockerImage = $this->databaseDockerComposeBuilder->getImageBySystem($databaseSystem)) {
                $output->writeln("<error>Database system {$dsn->getDriver()} not supported</error>");

                return Command::FAILURE;
            }

            $databaseBuildContext = new DatabaseBuildContext("{$appName}_{$databaseDockerImage}", $dsn, $databaseDockerImage);

            // Find database container
            $databaseContainer = $this->centerManager->findContainer($databaseDockerImage, null, $backendNetworkName);
            if ($databaseContainer) {
                if (
                    'secret' === $databaseContainer->getLabel('dockerizor.secret.method')
                    && ($secret = $databaseContainer->getLabel('dockerizor.secret.name'))
                    && ($serviceName = $databaseContainer->getLabel('com.docker.swarm.service.name'))
                    && $consoleContext->getQuestionHelper()->confirm("We have detected Dockerizor Center '{$serviceName}' do you want use it ? [y,n] y : ", true)
                ) {
                    $output->writeln('Config database DNS');

                    $databaseHost = $databaseContainer->getLabel('dockerizor.host');

                    $databaseBuildContext->setSecret($secret);

                    $dsn->setHost($databaseHost);
                    $dsn->setPassword("\$(cat '/var/run/secrets/{$secret}')");

                    $appBuildContext->getDockerComposeFile()->addSecret(new Secret($secret, true));

                    $dotenvFile->set('DATABASE_URL', $dsn->__toString());
                }
            } elseif ($consoleContext->getQuestionHelper()->confirm("Do you want to create service for database {$databaseDockerImage}:{$dsn->getServerVersion()} ? [y,n] y : ", true)) {
                $output->writeln("Create database service {$databaseDockerImage}:{$dsn->getServerVersion()}");
                $secret = "{$databaseSystem}_root_password";
                $this->dockerClient->createSecret($secret, $this->appManager->generatePassword());
                $dsn->setPassword("\$(cat '/var/run/secrets/{$secret}')");
                $dsn->setHost("{$appName}-{$databaseBuildContext->getImage()}");
            } else {
                unset($databaseBuildContext);
            }
        }

        // Detect traefik
        $traefikContainer = null;
        if ($container = $this->centerManager->findContainer(null, 'traefik', $frontendNetworkName)) {
            $consoleContext->getQuestionHelper()->confirm('We have detected traefik do you want use it ? [y,n] y : ', true);
            $traefikContainer = $container;
            $appBuildContext->setProxyContainer($traefikContainer);
        }

        // Traefik proxy
        if ($traefikContainer) {
            $domain =
                $this->appManager->getConfig('[domain]') ??
                "{$appName}.".$traefikContainer->getLabel('dockerizor.wildcard')
            ;

            $appBuildContext->setDomain($domain);
            $this->appManager->setConfig('[domain]', $domain);

            $output->writeln("Set domain {$domain}");
        } else {
            // Configure port if not use proxy
            $output->writeln('Config HTTP standalone');

            $port = $this->appManager->getConfig('[port]');
            if ($consoleContext->isModeInteractive()) {
                $port = $consoleContext->getQuestionHelper()->ask('Web port (eg 8080) (used '.implode(',', $usedPorts).') :', '8080');
            }
            if (null === $port) {
                $port = $this->centerManager->getFreePort();
            }

            $webBuildContext->setPort($port);
            $this->appManager->setConfig('[port]', $port);
        }

        // Php extensions suggestions
        if (!empty($extentionSuggestions)) {
            $selectedSuggestions = $extentionSuggestions;
            if ($consoleContext->isModeInteractive()) {
                $choices = array_combine($extentionSuggestions, $extentionSuggestions);
                $choices['none'] = 'none (default)';
                $selectedSuggestions = $consoleContext->getQuestionHelper()->choice(
                    'Install other PHP extensions from suggestions ? (eg : zip, gd)',
                    $choices,
                    true,
                    'none'
                );
            }

            foreach ($selectedSuggestions as $selectedSuggestion) {
                if ('none' !== $selectedSuggestion) {
                    $phpBuildContext->addExtension($selectedSuggestion);
                }
            }

            $extraExtensions = array_unique(array_merge($extraExtensions, $selectedSuggestions));

            $this->appManager->setConfig('[extra_extensions]', $extraExtensions);
        }

        $operatingSystem->addPackagesFromPhpBuildContext($phpBuildContext);

        // Get extra packages
        $extraPackages = $this->appManager->getConfig('[extra_packages]');
        $operatingSystem->addPackages($extraPackages);

        // Confirm PHP configuration
        $extensionsList = implode(', ', $phpBuildContext->getExtensions(true));
        $packagesList = implode(', ', $operatingSystem->getPackages());

        if ($consoleContext->isModeInteractive()) {
            if (!$consoleContext->getQuestionHelper()->confirm("Install <info>PHP v{$phpMinorVersion}</info>\nWith extensions <info>{$extensionsList}</info>\nWith packages <info>{$packagesList}</info>, continue ? [y,n] y : ", true)) {
                $output->writeln('<error>Dockerisation canceled</error>');

                return Command::SUCCESS;
            }
        } else {
            $output->writeln("Install PHP v{$phpMinorVersion}");
            $output->writeln("With extensions {$extensionsList}");
            $output->writeln("With packages {$packagesList}");
        }

        $output->writeln('Save configuration');

        if (!$consoleContext->isModeDryRun()) {
            $dotenvFile->save();

            $this->appManager->saveConfig();
        }

        $output->writeln('<info>Build files</info>');

        // Create Dockerfile
        $dockerFile = new DockerFile($operatingSystem);
        $phpBuildContext->setDockerFile($dockerFile);

        $appBuildContext->addBuildContext($phpBuildContext)
            ->addBuildContext($webBuildContext)
            ->addBuildContext($databaseBuildContext)
        ;
        $dockerFile = $this->dockerFileBuilder->build($appBuildContext, $phpBuildContext);
        $this->webDockerComposeBuilder->build($appBuildContext, $webBuildContext);
        $this->phpDockerComposeBuilder->build($appBuildContext, $phpBuildContext);

        if ($databaseBuildContext) {
            $this->databaseDockerComposeBuilder->build($appBuildContext, $databaseBuildContext);
        }

        $appBuildContext->addRun(new DockerRun($phpBuildContext->getImage(), 'composer install --ignore-platform-reqs -n'));

        return Command::SUCCESS;
    }
}
