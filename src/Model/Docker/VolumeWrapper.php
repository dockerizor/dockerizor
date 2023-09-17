<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker;

class VolumeWrapper
{
    protected string $from;
    protected string $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get from.
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * Get to.
     */
    public function getTo(): string
    {
        return $this->to;
    }
}
