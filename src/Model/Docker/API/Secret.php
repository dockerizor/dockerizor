<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\API;

class Secret extends ApiObject
{
    public function getName(): string
    {
        return $this->data['spec']['Name'];
    }

    public function getLabels(): array
    {
        return $this->data['spec']['Labels'];
    }
}
