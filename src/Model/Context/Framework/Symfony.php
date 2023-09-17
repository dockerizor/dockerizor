<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Framework;

use App\Model\Enum\Framework;

class Symfony extends AbstractFramework implements FrameworkInterface
{
    public function __construct(string $version, string $rootDirectory)
    {
        $this->name = Framework::SYMFONY->value;
        $this->version = $version;
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * Get name.
     * 
     * @return string
     */
    public function getNodeRunInstallCommand(): ?string
    {
        return 'yarn install';
    }

    /**
     * Get name.
     * 
     * @return string
     */
    public function getNodeRunBuildCommand(): ?string
    {
        return 'yarn build';
    }

    /**
     * Get name.
     * 
     * @return string
     */
    public function getNodeRunDevCommand(): ?string
    {
        return 'yarn watch';
    }
}
