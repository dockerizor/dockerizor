<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Builder\AppBuilder;
use App\Configurator\ComposerConfigurator;
use App\Configurator\NodeConfigurator;
use App\Dockerizor\AppManager;
use App\Model\Context\App\ComposerAppContext;
use App\Model\Context\App\NodeAppContext;
use App\Model\Context\ConsoleContext;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dkz:dockerize', description: 'Dockerize your project with composer')]
class DockerizorDockerizeCommand extends Command
{
    protected AppManager $appManager;
    protected ComposerConfigurator $composerConfigurator;
    protected NodeConfigurator $nodeConfigurator;
    protected AppBuilder $appBuilder;

    public function __construct(
        AppManager $appManager,
        ComposerConfigurator $composerConfigurator,
        NodeConfigurator $nodeConfigurator,
        AppBuilder $appBuilder
    ) {
        parent::__construct();

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
        $consoleContext = new ConsoleContext($this, $input, $output);

        $environmentContext = $this->appManager->getEnvironmentContext();

        foreach ($environmentContext->getAppContexts() as $appContext) {
            $output->writeln('Configuring '.$appContext::class.'...');

            switch ($appContext::class) {
                case ComposerAppContext::class:
                    $this->composerConfigurator->run($consoleContext, $environmentContext, $appContext);
                    break;
                case NodeAppContext::class:
                    $this->nodeConfigurator->run($consoleContext, $environmentContext, $appContext);
                    break;
            }
        }

        $this->appBuilder->build($consoleContext, $environmentContext);

        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
