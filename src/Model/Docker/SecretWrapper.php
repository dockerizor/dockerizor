<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
