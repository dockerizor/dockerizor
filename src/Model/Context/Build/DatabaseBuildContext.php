<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Build;

use App\Model\Dsn;

class DatabaseBuildContext extends AbstractBuildContext implements BuildContextInterface
{
    protected string $image;
    protected Dsn $dsn;
    protected ?string $secret = null;
    protected array $vars = [];

    public function __construct(Dsn $dsn, string $image)
    {
        $this->image = $image;
        $this->dsn = $dsn;
    }

    /**
     * Get image.
     * 
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set image.
     * 
     * @param string $image
     * 
     * @return self
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get dsn.
     * 
     * @return Dsn
     */
    public function getDsn(): Dsn
    {
        return $this->dsn;
    }

    /**
     * Set dsn.
     * 
     * @param Dsn $dsn
     * 
     * @return self
     */
    public function setDsn(Dsn $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    /**
     * Get secret.
     * 
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * Set secret.
     * 
     * @param string $secret
     * 
     * @return self
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get vars.
     * 
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * Set vars.
     * 
     * @param array $vars
     * 
     * @return self
     */
    public function setVars(array $vars): self
    {
        $this->vars = $vars;

        return $this;
    }
}
