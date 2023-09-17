<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
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
    protected bool $rm = true;

    public function __construct(string $image, string $command, string $volume = null, string $workdir = '/app', string $user = '1000:1000')
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

    public function __toString()
    {
        $volumes = '';
        foreach ($this->volumes as $volume) {
            $volumes .= "-v {$volume} ";
        }

        $rm = $this->rm ? '--rm' : '';

        return "docker run {$rm} {$volumes} -w {$this->workdir} -u {$this->user} {$this->image} {$this->command}";
    }
}
