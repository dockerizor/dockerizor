<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model;

use Symfony\Component\Dotenv\Dotenv as DotenvParser;

class DotenvFile
{
    protected array $currentData = [];
    protected array $replaceData = [];
    protected array $newData = [];
    protected string $path = '.env';

    public function __construct(string $path = '.env')
    {
        $this->path = $path;
    }

    /**
     * Load .env file.
     */
    public function load(): bool
    {
        if (!file_exists($this->path)) {
            return false;
        }
        $this->currentData = (new DotenvParser())->parse(file_get_contents($this->path), $this->path);

        return true;
    }

    /**
     * Save .env file.
     */
    public function save(): void
    {
        if (file_exists($this->path)) {
            $content = file_get_contents($this->path);
        } else {
            $content = '';
        }

        foreach ($this->replaceData as $key => $value) {
            $content = preg_replace("/^$key=(.*)\$/m", "$key={$value}", $content);
        }

        if (!empty($this->newData)) {
            $content .= "\n";
            foreach ($this->newData as $key => $value) {
                $content .= "$key={$value}\n";
            }
        }

        file_put_contents($this->path, $content);
    }

    /**
     * Get value.
     */
    public function get(string $key): ?string
    {
        return $this->currentData[$key] ?? null;
    }

    /**
     * Set value.
     */
    public function set(string $key, mixed $value): self
    {
        if (!isset($this->currentData[$key])) {
            $this->newData[$key] = $value;
        } else {
            $this->replaceData[$key] = str_replace(
                $this->get($key),
                $value,
                $this->currentData[$key] ?? ''
            );
        }

        return $this;
    }

    /**
     * Set multiple values.
     */
    public function setMultiple(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function getDsn(): ?Dsn
    {
        if (null !== $this->get('DATABASE_URL')) {
            return new Dsn($this->get('DATABASE_URL'));
        }

        return null;
    }
}
