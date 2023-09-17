<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
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
