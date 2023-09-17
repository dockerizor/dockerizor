<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     */
    public function getNodeRunInstallCommand(): ?string
    {
        return 'yarn install';
    }

    /**
     * Get name.
     */
    public function getNodeRunBuildCommand(): ?string
    {
        return 'yarn build';
    }

    /**
     * Get name.
     */
    public function getNodeRunDevCommand(): ?string
    {
        return 'yarn watch';
    }
}
