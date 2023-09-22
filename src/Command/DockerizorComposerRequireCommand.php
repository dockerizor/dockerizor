<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Builder\AppBuilder;
use App\Composer\Client;
use App\Configurator\ComposerConfigurator;
use App\Configurator\NodeConfigurator;
use App\Dockerizor\AppManager;
use App\Model\Context\App\ComposerAppContext;
use App\Model\Context\ConsoleContext;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dkz:composer:require', description: 'Dockerize your project with composer')]
class DockerizorComposerRequireCommand extends Command
{
    protected Client $composerClient;
    protected AppManager $appManager;
    protected ComposerConfigurator $composerConfigurator;
    protected NodeConfigurator $nodeConfigurator;
    protected AppBuilder $appBuilder;

    public function __construct(
        Client $composerClient,
        AppManager $appManager,
        ComposerConfigurator $composerConfigurator,
        NodeConfigurator $nodeConfigurator,
        AppBuilder $appBuilder
    ) {
        parent::__construct();

        $this->composerClient = $composerClient;
        $this->appManager = $appManager;
        $this->composerConfigurator = $composerConfigurator;
        $this->nodeConfigurator = $nodeConfigurator;
        $this->appBuilder = $appBuilder;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dockerize your project with composer')
            ->setHelp('This command allows you to dockerize your project with composer')
            ->addArgument('package', InputOption::VALUE_REQUIRED, 'Package name')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Dry run'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $package = $input->getArgument('package');

        $this->composerClient->require($package);

        $consoleContext = new ConsoleContext($this, $input, $output);

        $environmentContext = $this->appManager->getEnvironmentContext();

        $appContext = $environmentContext->getAppContext(ComposerAppContext::class);
        $this->composerConfigurator->run($consoleContext, $environmentContext, $appContext);

        $this->appBuilder->buildApp($consoleContext, $appContext->getAppBuildContext());

        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
