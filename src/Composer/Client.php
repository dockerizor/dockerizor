<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Composer;

use App\Model\Docker\DockerRun;
use App\Model\Process\Process;

/**
 * Composer client.
 */
class Client
{
    protected string $workdir = '';
    protected ?array $platformReqs = null;

    /**
     * Set workdir.
     */
    public function setWorkdir(string $workdir): void
    {
        $this->workdir = $workdir;
    }

    /**
     * Run composer require.
     */
    public function require(string $package, bool $noInteraction = true, bool $ignorePlatformReqs = true): void
    {
        $command = "composer require {$package}";
        if ($ignorePlatformReqs) {
            $command .= ' --ignore-platform-reqs';
        }
        if ($noInteraction) {
            $command .= ' -n';
        }

        $process = new Process(new DockerRun('composer', $command), null, $this->workdir);
        $process->setShowOutput(true);
        $process->run();
    }

    /**
     * Get platform requirements.
     */
    public function getPlatformReqs(): array
    {
        $process = new Process(new DockerRun('composer', 'composer check-platform-reqs -f json'), null, $this->workdir);
        $process->setShowOutput(false);
        $process->run();

        return $this->platformReqs = json_decode($process->getOutput(), true);
    }

    /**
     * Get PHP version requirement.
     */
    public function getPhpVersionRequirement(): ?string
    {
        if (null === $this->platformReqs) {
            $this->getPlatformReqs();
        }

        $phpVersion = null;
        foreach ($this->platformReqs as $req) {
            if ('php' === $req['name']) {
                if (!empty($req['failed_requirement'])) {
                    $phpVersion = $this->cleanVersion($req['failed_requirement']['constraint']);
                } else {
                    $phpVersion = $this->cleanVersion($req['version']);
                }
            }
        }

        return $phpVersion;
    }

    /**
     * Get PHP extensions requirements.
     */
    public function getExtensionRequirements(): array
    {
        if (null === $this->platformReqs) {
            $this->getPlatformReqs();
        }

        $extensions = [];
        foreach ($this->platformReqs as $req) {
            if (str_starts_with($req['name'], 'ext-')) {
                $extensions[] = str_replace('ext-', '', $req['name']);
            }
        }

        return $extensions;
    }

    /**
     * Get PHP extensions suggestions.
     */
    public function getExtensionSuggetions(): array
    {
        $process = new Process(new DockerRun('composer', 'composer suggests --all --list'), null, $this->workdir);
        $process->setShowOutput(false);
        $process->run();

        $result = $process->getOutput();

        if (empty($result)) {
            return [];
        }

        $composerSuggestions = explode("\n", $result);
        $composerSuggestions = array_filter($composerSuggestions);

        $suggestions = [];
        foreach ($composerSuggestions as $composerSuggestion) {
            if (str_starts_with($composerSuggestion, 'ext-')) {
                $suggestions[] = str_replace('ext-', '', $composerSuggestion);
            }
        }

        return $suggestions;
    }

    /**
     * Get PHP version.
     */
    protected function cleanVersion(string $version): string
    {
        preg_match('([0-9\.]+)', $version, $matches);

        return $matches[0];
    }
}
