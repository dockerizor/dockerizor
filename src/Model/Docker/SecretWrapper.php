<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Docker;

use App\Model\Docker\API\Secret;

class SecretWrapper
{
    protected Secret $secret;
    protected ?string $password;

    public function __construct(Secret $secret, string $password = null)
    {
        $this->secret = $secret;
        $this->password = $password;
    }

    public function getSecret(): Secret
    {
        return $this->secret;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
