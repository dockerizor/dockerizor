<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\Build;

use App\Model\Docker\DockerRun;
use App\Model\Process\Process;

class PhpBuildContext extends BuildContext implements BuildContextInterface
{
    public const PHPIZE_DEPS = [
        'autoconf',
        'dpkg-dev',
        'dpkg',
        'file',
        'g++',
        'gcc',
        'libc-dev',
        'make',
        'pkgconf',
        're2c',
    ];
    protected string $version;
    protected string $rootDir = '/var/www/html';
    protected array $extensions = [];
    protected array $configures = [];

    protected ?array $defaultExtensions = null;
    protected ?array $availableExtensions = null;

    public function __construct(string $version = '7.4', string $image = 'php:7.4-fpm')
    {
        $this->version = $version;
        $this->image = $image;
    }

    /**
     * Get version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set version.
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get minor version.
     */
    public function getMinorVersion(): string
    {
        list($major, $minor, $patch) = explode('.', $this->version);

        return "{$major}.{$minor}";
    }

    /**
     * Get root dir.
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * Set root dir.
     */
    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    /**
     * Get extensions.
     */
    public function getExtensions(bool $onlyInstall = false): array
    {
        if (!$onlyInstall) {
            return $this->extensions;
        }

        $extensions = [];
        foreach ($this->extensions as $extension) {
            if (
                \in_array($extension, $this->getDefaultExtensions(), true)
                && !\in_array($extension, $this->getDefaultExtensions(), true)
            ) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    /**
     * Add extensions.
     *
     * @return self
     */
    public function addExtensions(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Add extension.
     */
    public function addExtension(string $extension): self
    {
        if (!\in_array($extension, $this->extensions, true)) {
            $this->extensions[] = $extension;
            $this->configureExtension($extension);
        }

        return $this;
    }

    /**
     * Remove extension.
     */
    public function removeExtension(string $extension): self
    {
        $key = array_search($extension, $this->extensions, true);
        if (false !== $key) {
            unset($this->extensions[$key]);
        }

        return $this;
    }

    /**
     * Configure extension.
     */
    public function configureExtension(string $extension): void
    {
        switch ($extension) {
            case 'gd':
                $this->addConfigure('gd', '--with-jpeg --with-freetype');
                break;
            case 'imap':
                $this->addConfigure('imap', '--with-kerberos --with-imap-ssl');
                break;
            case 'odbc':
                $this->addConfigure('odbc', '--with-unixODBC=shared,/usr');
                break;
            case 'pdo_odbc':
                $this->addConfigure('pdo_odbc', '--with-pdo-odbc=unixODBC,/usr');
                break;
            case 'oci8':
                $this->addConfigure('oci8', '--with-oci8=instantclient,/usr/local/instantclient');
                break;
            case 'pdo_oci':
                $this->addConfigure('pdo_oci', '--with-php-config=/usr/bin/php-config8');
                break;
        }
    }

    /**
     * Add configure.
     */
    public function addConfigure(string $extension, string $configure): self
    {
        $this->configures[$extension] = $configure;

        return $this;
    }

    /**
     * Get configures.
     */
    public function getConfigures(): array
    {
        return $this->configures;
    }

    /**
     * Configure database.
     */
    public function configureDatabase(string $driver): void
    {
        switch ($driver) {
            case 'mysql':
            case 'mysql2':
            case 'pdo_mysql':
            case 'mariadb':
                $this->addExtension('pdo_mysql');
                break;
            case 'postgres':
            case 'pgsql':
            case 'postgresql':
            case 'pdo_pgsql':
                $this->addConfigure('pgsql', '-with-pgsql=/usr/local/pgsql');
                $this->addExtension('pdo');
                $this->addExtension('pgsql');
                $this->addExtension('pdo_pgsql');
                break;
            case 'sqlite':
            case 'sqlite3':
            case 'pdo_sqlite':
                $this->addExtension('pdo_sqlite');
                // $this->addExtension('sqlite3');
                break;
            case 'mssql':
            case 'pdo_sqlsrv':
                $this->addExtension('pdo_sqlsrv');
                break;
            case 'mysqli':
                $this->addExtension('mysqli');
                break;
            case 'sqlsrv':
                $this->addExtension('sqlsrv');
                break;
        }
    }

    /**
     * Get default extensions from docker image.
     */
    public function getDefaultExtensions(): array
    {
        if (null !== $this->defaultExtensions) {
            return $this->defaultExtensions;
        }

        $process = new Process(new DockerRun($this->getDockerFile()->getOperatingSystem()->getImage(), 'php -m'));
        $process->setShowOutput(false)->run();

        $outputLines = $process->getOutputLines();
        $outputLines = array_diff($outputLines, ['', '[PHP Modules]', '[Zend Modules]', 'Core']);

        return $this->defaultExtensions = array_map('strtolower', $outputLines);
    }

    /**
     * Get available extensions from docker image.
     */
    public function getAvailableExtensions(): array
    {
        if (null !== $this->availableExtensions) {
            return $this->availableExtensions;
        }

        $process = new Process(new DockerRun($this->getDockerFile()->getOperatingSystem()->getImage(), 'sh -c "docker-php-source extract && ls -A /usr/src/php/ext/"', '.:/app', '/app', 'root:root'));
        $process->setSaveOutErr(true)->setShowOutput(false)->run();

        return $this->availableExtensions = array_filter($process->getOutputLines());
    }
}
