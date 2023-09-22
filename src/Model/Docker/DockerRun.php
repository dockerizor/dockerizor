<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker;

// docker run --rm -v $(pwd):/app -w /app node:alpine npm view --json --loglevel=silent @sylius-ui/frontend dependencies
class DockerRun
{
    protected string $image;
    protected string $command;
    protected string $workdir;
    protected string $user;
    protected array $volumes = [];
    protected array $networks = [];
    protected array $options = [];
    protected bool $rm = true;

    public function __construct(string $image, string $command, string $volume = '.:/app', string $workdir = '/app', string $user = '1000:1000')
    {
        $this->image = $image;
        $this->command = $command;
        $this->workdir = $workdir;
        $this->user = $user;

        if ($volume) {
            $this->addVolume($volume);
        }
    }

    public function addVolume(string $volume): self
    {
        $this->volumes[] = $volume;

        return $this;
    }

    public function addNetwork(string $network): self
    {
        $this->networks[] = $network;

        return $this;
    }

    public function addOption(string $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function __toString()
    {
        $volumes = '';
        foreach ($this->volumes as $volume) {
            $volumes .= "-v {$volume} ";
        }

        $rm = $this->rm ? '--rm' : '';

        $networks = '';
        foreach ($this->networks as $network) {
            $networks .= "--network {$network} ";
        }

        $options = '';
        foreach ($this->options as $option) {
            $options .= "{$option} ";
        }

        return "docker run {$rm} {$volumes} -w {$this->workdir} -u {$this->user} {$networks} {$options} {$this->image} {$this->command}";
    }
}
