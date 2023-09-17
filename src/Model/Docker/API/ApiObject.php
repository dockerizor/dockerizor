<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\API;

class ApiObject
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __call(string $name, array $arguments)
    {
        $method = substr($name, 0, 3);
        $name = str_replace($method, '', $name);
        switch ($method) {
            case 'get':
                return $this->data[$name];
            case 'set':
                $this->data[$name] = $arguments[0];

                return $this;
            default:
                throw new \Exception("Method $name does not exist");
        }
    }
}
