<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\OS;

use App\Model\Context\Build\PhpBuildContext;

/**
 * Alpine Linux.
 */
class Alpine extends OperatingSystem
{
    protected string $name = 'alpine';
    protected array $phpExtenssionDependencies = [
        'pdo_pgsql' => ['postgresql-dev'],
        'pgsql' => ['postgresql-dev'],
        'libxml' => ['libxml2-dev'],
        'gd' => ['libpng-dev', 'libjpeg-turbo-dev', 'freetype-dev'],
        'dba' => ['enchant2-dev'],
        'gmp' => ['gmp-dev'],
        'gettext' => ['gettext-dev'],
        'ldap' => ['ldb-dev', 'openldap-dev', 'libldap'],
        'imap' => ['krb5-dev', 'imap-dev'],
        'odbc' => ['unixodbc-dev'],
        'pdo_odbc' => ['unixodbc-dev'],
        'pdo_dblib' => ['freetds-dev'],
        'pspell' => ['aspell-dev'],
        'simplexml' => ['libxml2-dev'],
        'snmp' => ['net-snmp-dev'],
        'tidy' => ['tidyhtml-dev'],
        'xsl' => ['libxslt-dev'],
        'curl' => ['curl-dev'],
        'phar' => ['openssl-dev'],
        'intl' => ['icu-dev'],
        'zip' => ['libzip-dev'],
        'iconv' => ['gnu-libiconv-dev'],
        'mbstring' => ['oniguruma-dev'],
        'sockets' => ['linux-headers'],
        'soap' => ['libxml2-dev'],
        'imap' => ['openssl-dev', 'imap-dev'],
        'enchant' => ['enchant2-dev'],
        'bz2' => ['bzip2-dev'],
    ];

    /**
     * Add packages from PHP config.
     */
    public function addPackagesFromPhpBuildContext(PhpBuildContext $phpContext): self
    {
        foreach ($phpContext->getExtensions() as $extension) {
            foreach ($this->phpExtenssionDependencies[$extension] ?? [] as $package) {
                $this->addPackage($package);
            }
        }

        return $this;
    }

    /**
     * Run package manager update.
     */
    public function runPackageManagerUpdate(): string
    {
        return 'apk update';
    }

    /**
     * Run package manager install.
     */
    public function runPackageManagerInstall(): string
    {
        $packages = implode(' ', $this->getPackages());

        return "apk add --no-cache {$packages}";
    }
}
