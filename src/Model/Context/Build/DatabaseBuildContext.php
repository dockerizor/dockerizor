<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Build;

use App\Model\Dsn;

class DatabaseBuildContext extends BuildContext implements BuildContextInterface
{
    protected string $name;
    protected Dsn $dsn;
    protected ?string $secret = null;
    protected array $vars = [];

    public function __construct(string $name, Dsn $dsn, string $image)
    {
        $this->name = $name;
        $this->dsn = $dsn;
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
     * Get dsn.
     */
    public function getDsn(): Dsn
    {
        return $this->dsn;
    }

    /**
     * Set dsn.
     */
    public function setDsn(Dsn $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    /**
     * Get secret.
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * Set secret.
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get vars.
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * Set vars.
     */
    public function setVars(array $vars): self
    {
        $this->vars = $vars;

        return $this;
    }

    public function getVolumeName()
    {
        return $this->getName().'_data';
    }

    public function getSecretName()
    {
        return $this->getName().'_secret';
    }

    public function getServiceName()
    {
        return $this->getName();
    }
}
