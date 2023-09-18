<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\API;

/**
 * Docker ContainerInspect.
 */
class Container extends ApiObject
{
    public function getName(): string
    {
        $name = $this->data['Names'][0] ?? '';
        $name = str_replace('/', '', $name);

        return $name;
    }

    public function getLabel(string $label): ?string
    {
        return $this->data['Labels'][$label] ?? null;
    }

    public function getNetworks(): array
    {
        return $this->data['NetworkSettings']['Networks'];
    }
}
