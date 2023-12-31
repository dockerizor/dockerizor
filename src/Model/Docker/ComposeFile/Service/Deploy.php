<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile\Service;

class Deploy
{
    protected array $labels = [];

    public function __construct(array $labels = [])
    {
        $this->labels = $labels;
    }

    public static function create(array $data)
    {
        $labels = $data['labels'] ?? [];

        return new self($labels);
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function addLabel(string $label): self
    {
        $this->labels[] = $label;

        return $this;
    }

    public function setLabels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    public function toArray(): array
    {
        $array = [];

        if (!empty($this->labels)) {
            $array['labels'] = $this->labels;
        }

        return $array;
    }
}
