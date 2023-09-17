<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
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
     * 
     * @param string $workdir
     */
    public function setWorkdir(string $workdir): void
    {
        $this->workdir = $workdir;
    }

    /**
     * Get platform requirements.
     * 
     * @return array
     */
    public function getPlatformReqs(): array
    {
        $result = shell_exec("cd {$this->workdir} && composer check-platform-reqs -f json");

        return $this->platformReqs = json_decode($result, true);
    }

    /**
     * Get PHP version requirement.
     * 
     * @return string|null
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
     * 
     * @return array
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
     * 
     * @return array
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
     * 
     * @param string $version
     * 
     * @return string
     */
    protected function cleanVersion(string $version): string
    {
        preg_match('([0-9\.]+)', $version, $matches);

        return $matches[0];
    }
}
