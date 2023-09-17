<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
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
