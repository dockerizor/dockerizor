<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context;

use App\Model\Console\QuestionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleContext
{
    protected Command $command;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected QuestionHelper $questionHelper;

    protected bool $modeDryRun = false;
    protected bool $modeInteractive = true;

    public function __construct(Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;

        if (
            $input->hasOption('dry-run')
            && ($this->modeDryRun = $input->getOption('dry-run'))
        ) {
            $output->writeln('<info>Dry run actived</info>');
        }

        $this->modeInteractive = !$input->getOption('no-interaction');
        if (!$this->modeInteractive) {
            $output->writeln('<info>No interactive mode actived</info>');
        }

        $this->questionHelper = new QuestionHelper(
            $input,
            $output,
            $command->getHelper('question'),
            $this->modeInteractive
        );
    }

    /**
     * Get command.
     */
    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * Get input.
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * Get output.
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Get mode.
     *
     * @return Mode
     */
    public function isModeDryRun(): bool
    {
        return $this->modeDryRun;
    }

    /**
     * Get mode.
     *
     * @return Mode
     */
    public function isModeInteractive(): bool
    {
        return $this->modeInteractive;
    }

    /**
     * Get questionHelper.
     */
    public function getQuestionHelper(): QuestionHelper
    {
        return $this->questionHelper;
    }
}
