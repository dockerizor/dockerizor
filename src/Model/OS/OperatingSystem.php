<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\OS;

use App\Model\Context\Build\PhpBuildContext;

abstract class OperatingSystem
{
    protected string $name;
    protected string $image;
    protected array $packages = [];

    public function __construct(string $image)
    {
        $this->image = $image;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get image.
     */
    public function getImage(): string
    {
        return $this->image;
    }

    public function getImageName(): string
    {
        return explode(':', $this->image)[0];
    }

    /**
     * Add package.
     */
    public function addPackage(string $package): self
    {
        $this->packages[] = $package;

        return $this;
    }

    /**
     * Add packages.
     */
    public function addPackages(array $packages): self
    {
        foreach ($packages as $package) {
            $this->addPackage($package);
        }

        return $this;
    }

    /**
     * Get packages.
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * Add packages from PHP config.
     */
    public function addPackagesFromPhpBuildContext(PhpBuildContext $phpContext): self
    {
        return $this;
    }

    /**
     * Run package manager update.
     */
    public function runPackageManagerUpdate(): string
    {
        return '';
    }

    /**
     * Run package manager install.
     */
    public function runPackageManagerInstall(): string
    {
        return '';
    }
}
