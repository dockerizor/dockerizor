<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Process;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Process\Process as BaseProcess;

class Process
{
    protected string $command;
    protected ?Output $output;
    protected ?string $workdir;
    protected bool $saveOutput = true;
    protected bool $showOutput = true;
    protected bool $saveOutErr = false;
    protected bool $saveOutOut = true;
    protected bool $tty = false;
    protected array $outputLines = [];
    protected ?int $timeout = null;
    protected ?int $idleTimeout = null;

    public function __construct(string $command, Output $output = null, string $workdir = null)
    {
        $this->command = $command;
        $this->output = $output;
        $this->workdir = $workdir;
    }

    public function run(): void
    {
        $originalWorkdir = getcwd();
        if ($this->workdir) {
            chdir($this->workdir);
        }
        $process = BaseProcess::fromShellCommandline($this->command);
        $process->setTty($this->tty);
        $process->setTimeout($this->timeout);
        $process->setIdleTimeout($this->idleTimeout);
        $process->start();
        $process->wait(function ($type, $buffer): void {
            switch ($type) {
                case BaseProcess::ERR:
                    if ($this->showOutput) {
                        if ($this->output) {
                            $this->output->write($buffer);
                        } else {
                            echo $buffer;
                        }
                    }
                    if ($this->saveOutput && $this->saveOutErr) {
                        $this->outputLines = array_merge($this->outputLines, explode("\n", $buffer));
                    }
                    break;
                case BaseProcess::OUT:
                    if ($this->showOutput) {
                        if ($this->output) {
                            $this->output->write($buffer);
                        } else {
                            echo $buffer;
                        }
                    }
                    if ($this->saveOutput && $this->saveOutOut) {
                        $this->outputLines = array_merge($this->outputLines, explode("\n", $buffer));
                    }
                    break;
            }
        });

        if ($this->workdir) {
            chdir($originalWorkdir);
        }
    }

    public function setSaveOutput(bool $saveOutput): self
    {
        $this->saveOutput = $saveOutput;

        return $this;
    }

    public function setShowOutput(bool $showOutput): self
    {
        $this->showOutput = $showOutput;

        return $this;
    }

    public function setSaveOutErr(bool $saveOutErr): self
    {
        $this->saveOutErr = $saveOutErr;

        return $this;
    }

    public function setSaveOutOut(bool $saveOutOut): self
    {
        $this->saveOutOut = $saveOutOut;

        return $this;
    }

    public function setTty(bool $tty): self
    {
        $this->tty = $tty;

        return $this;
    }

    public function getOutput(): string
    {
        return implode("\n", $this->outputLines);
    }

    public function getOutputLines(): array
    {
        return $this->outputLines;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setIdleTimeout(int $idleTimeout): self
    {
        $this->idleTimeout = $idleTimeout;

        return $this;
    }
}
