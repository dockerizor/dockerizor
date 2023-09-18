<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

class Dsn
{
    protected array $dsn;

    public function __construct(string $dsn)
    {
        $data = parse_url($dsn);

        parse_str($data['query'] ?? '', $query);

        $this->dsn = [
            'driver' => $data['scheme'] ?? '',
            'host' => $data['host'] ?? '',
            'port' => $data['port'] ?? '',
            'user' => $data['user'] ?? '',
            'password' => $data['pass'] ?? '',
            'dbname' => ltrim($data['path'], '/'),
            'charset' => $query['charset'] ?? '',
            'serverVersion' => $query['serverVersion'] ?? '',
        ];
    }

    /**
     * Get driver.
     */
    public function getDriver(): string
    {
        return $this->dsn['driver'];
    }

    /**
     * Set driver.
     */
    public function setDriver(string $driver): self
    {
        $this->dsn['driver'] = $driver;

        return $this;
    }

    /**
     * Get system.
     */
    public function getSystem(): string
    {
        if (preg_match('/mariadb/', $this->getServerVersion())) {
            return 'mariadb';
        }

        return $this->getDriver();
    }

    /**
     * Get host.
     */
    public function getHost(): string
    {
        return $this->dsn['host'];
    }

    /**
     * Set host.
     */
    public function setHost(string $host): self
    {
        $this->dsn['host'] = $host;

        return $this;
    }

    /**
     * Get port.
     */
    public function getPort(): string
    {
        return $this->dsn['port'];
    }

    /**
     * Set port.
     */
    public function setPort(string $port): self
    {
        $this->dsn['port'] = $port;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser(): string
    {
        return $this->dsn['user'];
    }

    /**
     * Set user.
     */
    public function setUser(string $user): self
    {
        $this->dsn['user'] = $user;

        return $this;
    }

    /**
     * Get password.
     */
    public function getPassword(): string
    {
        return $this->dsn['password'];
    }

    /**
     * Set password.
     */
    public function setPassword(string $password): self
    {
        $this->dsn['password'] = $password;

        return $this;
    }

    /**
     * Get database.
     */
    public function getDatabase(): string
    {
        return $this->dsn['dbname'];
    }

    /**
     * Set database.
     */
    public function setDatabase(string $database): self
    {
        $this->dsn['dbname'] = $database;

        return $this;
    }

    /**
     * Get server version.
     */
    public function getServerVersion(bool $onlyVersion = true): string
    {
        if ($onlyVersion) {
            return str_replace('mariadb-', '', $this->dsn['serverVersion']);
        }

        return $this->dsn['serverVersion'];
    }

    /**
     * Set server version.
     */
    public function setServerVersion(string $serverVersion): self
    {
        $this->dsn['serverVersion'] = $serverVersion;

        return $this;
    }

    /**
     * Get charset.
     */
    public function getCharset(): string
    {
        return $this->dsn['charset'];
    }

    /**
     * Set charset.
     *
     * @return self
     */
    public function __toString()
    {
        $queryData = [];

        if (!empty($this->getCharset())) {
            $queryData['charset'] = $this->getCharset();
        }
        if (!empty($this->getServerVersion())) {
            $queryData['serverVersion'] = $this->getServerVersion();
        }

        $query = '';
        if (!empty($queryData)) {
            $query = '?'.http_build_query($queryData);
        }

        return sprintf(
            '%s://%s:%s@%s:%s/%s%s',
            $this->getDriver(),
            $this->getUser(),
            $this->getPassword(),
            $this->getHost(),
            $this->getPort(),
            $this->getDatabase(),
            $query
        );
    }
}
