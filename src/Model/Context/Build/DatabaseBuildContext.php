<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set image.
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
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
}
