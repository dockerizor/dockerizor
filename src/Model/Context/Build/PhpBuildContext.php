<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\Build;

use App\Model\Dsn;

class PhpBuildContext extends AbstractBuildContext implements BuildContextInterface
{
    public const AVAILABLE_EXTENSION = [
        'bcmath',
        'bz2',
        'calendar',
        'ctype',
        'curl',
        'dba',
        'dom',
        'enchant',
        'exif',
        'fileinfo',
        'filter',
        'ftp',
        'gd',
        'gettext',
        'gmp',
        'hash',
        'iconv',
        'imap',
        'interbase',
        'intl',
        'json',
        'ldap',
        'mbstring',
        'mcrypt',
        'mysqli',
        'oci8',
        'odbc',
        'opcache',
        'pcntl',
        'pdo',
        'pdo_dblib',
        'pdo_firebird',
        'pdo_mysql',
        'pdo_oci',
        'pdo_odbc',
        'pdo_pgsql',
        'pdo_sqlite',
        'pgsql',
        'phar',
        'posix',
        'pspell',
        'readline',
        'recode',
        'reflection',
        'session',
        'shmop',
        'simplexml',
        'snmp',
        'soap',
        'sockets',
        'spl',
        'standard',
        'sysvmsg',
        'sysvsem',
        'sysvshm',
        'tidy',
        'tokenizer',
        'wddx',
        'xml',
        'xmlreader',
        'xmlrpc',
        'xmlwriter',
        'xsl',
        'zip',
    ];

    public const DEFAULT_EXTENSION = [
        'ctype',
        'curl',
        'date',
        'dom',
        'fileinfo',
        'filter',
        'ftp',
        'hash',
        'iconv',
        'json',
        'libxml',
        'mbstring',
        'mysqlnd',
        'openssl',
        'pcre',
        'pdo',
        'pdo_sqlite',
        'phar',
        'posix',
        'readline',
        'reflection',
        'session',
        'simplexml',
        'sodium',
        'spl',
        'sqlite3',
        'standard',
        'tokenizer',
        'xml',
        'xmlreader',
        'xmlwriter',
        'zlib',
    ];

    protected string $version;
    protected string $image;
    protected string $rootDir = '/var/www/html';
    protected array $extensions = [];
    protected array $configures = [];

    public function __construct(string $version = '7.4', string $image = 'php:7.4-fpm')
    {
        $this->version = $version;
        $this->image = $image;
    }

    /**
     * Get version.
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set version.
     * 
     * @param string $version
     * 
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get minor version.
     * 
     * @return string
     */
    public function getMinorVersion(): string
    {
        list($major, $minor, $patch) = explode('.', $this->version);

        return "{$major}.{$minor}";
    }

    /**
     * Get image.
     * 
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set image.
     * 
     * @param string $image
     * 
     * @return self
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get root dir.
     * 
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * Set root dir.
     * 
     * @param string $rootDir
     * 
     * @return self
     */
    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    /**
     * Get extensions.
     * 
     * @param bool $onlyInstall
     * 
     * @return array
     */
    public function getExtensions(bool $onlyInstall = false): array
    {
        if (!$onlyInstall) {
            return $this->extensions;
        }

        $extensions = [];
        foreach ($this->extensions as $extension) {
            if (
                \in_array($extension, self::AVAILABLE_EXTENSION, true)
                && !\in_array($extension, self::DEFAULT_EXTENSION, true)
            ) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    /**
     * Add extensions.
     * 
     * @param array $extensions
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
     * 
     * @param string $extension
     * 
     * @return self
     */
    public function addExtension(string $extension): self
    {
        if (!\in_array($extension, $this->extensions, true)) {
            $this->extensions[] = $extension;
        }

        return $this;
    }

    /**
     * Remove extension.
     * 
     * @param string $extension
     * 
     * @return self
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
     * Add configure.
     * 
     * @param string $extension
     * @param string $configure
     * 
     * @return self
     */
    public function addConfigure(string $extension, string $configure): self
    {
        $this->configures[$extension] = $configure;

        return $this;
    }

    /**
     * Get configures.
     * 
     * @return array
     */
    public function getConfigures(): array
    {
        return $this->configures;
    }

    /**
     * Configure database.
     * 
     * @param string $dsn
     * 
     * @return void
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
}
