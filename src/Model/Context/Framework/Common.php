<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Framework;

class Common extends AbstractFramework implements FrameworkInterface
{
    public function __construct(string $name, string $version, string $rootDirectory)
    {
        $this->name = $name;
        $this->version = $version;
        $this->rootDirectory = $rootDirectory;
    }
}
