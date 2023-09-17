<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Composer;

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
     * Get platform requirements.
     */
    public function getPlatformReqs(): array
    {
        $result = shell_exec("cd {$this->workdir} && composer check-platform-reqs -f json");

        return $this->platformReqs = json_decode($result, true);
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
        $result = shell_exec('cd app && composer suggests --all --list');

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
