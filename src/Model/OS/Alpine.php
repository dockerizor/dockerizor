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

    /**
     * Add packages from PHP config.
     */
    public function addPackagesFromPhpBuildContext(PhpBuildContext $phpContext): self
    {
        foreach ($phpContext->getExtensions() as $key => $extension) {
            switch ($extension) {
                case 'pdo_pgsql':
                    $this->addPackage('postgresql-dev');
                    break;
                case 'pdo_pgsql':
                case 'pgsql':
                    $this->addPackage('postgresql-dev');
                    break;
                case 'libxml':
                    $this->addPackage('libxml2-dev');
                    break;
                case 'gd':
                    $this->addPackage('libpng-dev');
                    $this->addPackage('libjpeg-turbo-dev');
                    $this->addPackage('freetype-dev');
                    $phpContext->addConfigure('gd', '--with-jpeg --with-freetype');
                    break;
                case 'dba':
                    $this->addPackage('enchant2-dev');
                    break;
                case 'gmp':
                    $this->addPackage('gmp-dev');
                    // no break
                case 'gettext':
                    $this->addPackage('gettext-dev');
                    // no break
                case 'ldap':
                    $this->addPackage('ldb-dev');
                    $this->addPackage('openldap-dev');
                    $this->addPackage('libldap');
                    break;
                case 'imap':
                    $this->addPackage('krb5-dev');
                    $this->addPackage('imap-dev');
                    $phpContext->addConfigure('imap', '--with-kerberos --with-imap-ssl');
                    break;
                case 'odbc':
                    $this->addPackage('unixodbc-dev');
                    $phpContext->addConfigure('odbc', '--with-unixODBC=shared,/usr');
                    break;
                case 'pdo_odbc':
                    $this->addPackage('unixodbc-dev');
                    $phpContext->addConfigure('pdo_odbc', '--with-pdo-odbc=unixODBC,/usr');
                    break;
                case 'pdo_dblib':
                    $this->addPackage('freetds-dev');
                    break;
                case 'pspell':
                    $this->addPackage('aspell-dev');
                    break;
                case 'simplexml':
                    $this->addPackage('libxml2-dev');
                    break;
                case 'snmp':
                    $this->addPackage('net-snmp-dev');
                    break;
                case 'tidy':
                    $this->addPackage('tidyhtml-dev');
                    break;
                case 'xsl':
                    $this->addPackage('libxslt-dev');
                    break;
                case 'curl':
                    $this->addPackage('curl-dev');
                    break;
                case 'phar':
                    $this->addPackage('openssl-dev');
                    break;
                case 'intl':
                    $this->addPackage('icu-dev');
                    break;
                case 'zip':
                    $this->addPackage('libzip-dev');
                    break;
                case 'iconv':
                    $this->addPackage('gnu-libiconv-dev');
                    break;
                case 'mbstring':
                    $this->addPackage('oniguruma-dev');
                    break;
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
