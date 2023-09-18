<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Console;

use Symfony\Component\Console\Helper\QuestionHelper as BaseQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class QuestionHelper
{
    protected InputInterface $input;
    protected OutputInterface $output;
    protected BaseQuestionHelper $helper;
    protected bool $interactive;

    public function __construct(InputInterface $input, OutputInterface $output, BaseQuestionHelper $helper, bool $interactive = true)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->interactive = $interactive;
    }

    public function ask(string $question, string $default = null): string
    {
        if ($this->interactive) {
            return $this->helper->ask($this->input, $this->output, new Question($question, $default));
        }

        return $default;
    }

    public function confirm(string $question, bool $default = true): bool
    {
        if ($this->interactive) {
            return $this->helper->ask($this->input, $this->output, new ConfirmationQuestion($question, $default));
        }

        return $default;
    }

    public function choice(string $question, array $choices, bool $multi = false, string $default = null): array|string
    {
        if ($this->interactive) {
            $question = new ChoiceQuestion($question, $choices, $default);
            $question->setMultiselect($multi);

            return $this->helper->ask($this->input, $this->output, $question);
        }

        return $default;
    }
}
