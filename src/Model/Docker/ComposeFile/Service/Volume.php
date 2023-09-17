<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile\Service;

class Volume
{
    protected ?string $type = null;
    protected string $source;
    protected string $target;
    protected bool $readOnly = false;

    public function __construct(string $source, string $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    public static function create(array|string $data): self
    {
        if (\is_array($data)) {
            $volume = new self($data['source'], $data['target']);

            if (isset($data['type'])) {
                $volume->setType($data['type']);
            }

            if (isset($data['read_only'])) {
                $volume->setReadOnly($data['read_only']);
            }

            return $volume;
        }

        list($source, $target) = explode(':', $data);

        return new self($source, $target);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function toArray(): array|string
    {
        if (!$this->readOnly) {
            return "{$this->source}:{$this->target}";
        }

        if (!$this->type) {
            if (str_contains($this->source, '/')) {
                $this->type = 'bind';
            } else {
                $this->type = 'volume';
            }
        }

        $data = [
            'type' => $this->type,
            'source' => $this->source,
            'target' => $this->target,
        ];

        $data['read_only'] = $this->readOnly;

        return $data;
    }
}
