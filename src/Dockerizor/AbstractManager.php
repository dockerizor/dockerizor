<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Dockerizor;

use App\Model\Mode;
use Phar;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractManager
{
    protected PropertyAccessor $propertyAccessor;
    protected ParameterBagInterface $parameterBag;

    protected ?string $workdir = null;
    protected string $configFilename = '';
    protected array $config = [];
    protected array $defaultConfig = [];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Check if running in phar.
     */
    public function isPhar(): bool
    {
        return '' !== \Phar::running();
    }

    /**
     * Get workdir.
     */
    public function getWorkdir(bool $force = false): string
    {
        if (!$force && null !== $this->workdir) {
            return $this->workdir;
        }

        switch ($this->getMode()) {
            case Mode::phar:
                $this->workdir = getcwd();
                break;
            case Mode::docker:
                $this->workdir = '/dockerizor/app';
                break;
            case Mode::app:
                $this->workdir = $this->parameterBag->get('kernel.project_dir').'/app';
                break;
            default:
                $this->workdir = getcwd();
                break;
        }

        return $this->workdir;
    }

    /**
     * Set workdir.
     */
    public function setWorkdir(string $workdir): self
    {
        $this->workdir = $workdir;

        return $this;
    }

    /**
     * Get mode.
     */
    public function getMode(): Mode
    {
        if ($this->isPhar()) {
            return Mode::phar;
        } elseif ('/dockerizor' === getcwd()) {
            return Mode::docker;
        } elseif ($this->parameterBag->get('kernel.project_dir') === getcwd()) {
            return Mode::app;
        }

        return Mode::unknown;
    }

    /**
     * Load config.
     */
    public function loadConfig(): array
    {
        $filename = "{$this->getWorkdir()}/{$this->configFilename}";

        if (false === file_exists($filename)) {
            $this->config = $this->defaultConfig;
        } else {
            $this->config = json_decode(file_get_contents($filename), true);
        }

        return $this->config;
    }

    /**
     * Save config.
     */
    public function saveConfig(): void
    {
        $filename = "{$this->getWorkdir()}/{$this->configFilename}";

        file_put_contents($filename, json_encode($this->config, \JSON_PRETTY_PRINT));
    }

    /**
     * Get config.
     */
    public function getConfig(string $path): mixed
    {
        return $this->propertyAccessor->getValue($this->config, $path);
    }

    /**
     * Set config.
     */
    public function setConfig(string $path, mixed $value): self
    {
        $this->propertyAccessor->setValue($this->config, $path, $value);

        return $this;
    }

    /**
     * Generate password.
     */
    public function generatePassword(): string
    {
        return md5(random_bytes(10));
    }
}
