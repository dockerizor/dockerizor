<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile\Service;

class Port
{
    protected int $target;
    protected int $published;
    protected string $protocol = 'tcp';
    protected string $mode = 'host';

    public function __construct(int $target, int $published, string $protocol = 'tcp', string $mode = 'host')
    {
        $this->target = $target;
        $this->published = $published;
        $this->protocol = $protocol;
        $this->mode = $mode;
    }

    public static function create(array|string $data): self
    {
        if (\is_string($data)) {
            list($published, $target) = explode(':', $data);

            return new self(
                $target,
                $published,
            );
        }

        return new self(
            $data['target'],
            $data['published'],
            $data['protocol'] ?? 'tcp',
            $data['mode'] ?? 'host',
        );
    }

    public function getTarget(): int
    {
        return $this->target;
    }

    public function getPublished(): int
    {
        return $this->published;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function toArray(): array
    {
        return [
            'target' => $this->target,
            'published' => $this->published,
            'protocol' => $this->protocol,
            'mode' => $this->mode,
        ];
    }
}
