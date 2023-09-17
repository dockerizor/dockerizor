<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Builder\DockerCompose;

use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\DatabaseBuildContext;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Network;
use App\Model\Docker\ComposeFile\Service\Volume;
use App\Model\Dsn;
use Cocur\Slugify\Slugify;

class DatabaseDockerComposeBuilder extends DockerComposeBuilder
{
    /**
     * Build the database service.
     *
     * @param AppBuildContext      $appBuildContext
     * @param DatabaseBuildContext $databaseContext
     * 
     * @return Service
     */
    public function build(AppBuildContext $appBuildContext, DatabaseBuildContext $databaseContext)
    {
        $appName = $appBuildContext->getAppName();
        $dsn = $databaseContext->getDsn();

        // Create service
        $version = (new Slugify())->slugify($dsn->getServerVersion());
        $service = new Service("{$databaseContext->getImage()}_{$version}", $databaseContext->getImage());
        $service->addLabel('dockerizor.enable', 'true')
            ->addVolume(new Volume('db-data', '/var/lib/mysql'))
        ;
        // TODO CREATE Volume

        // Set environment variables
        $vars = $this->getEnvironnementByDsn($dsn);
        if (!empty($vars)) {
            $service->addEnvironmentVariables($vars);
        }

        // Add backend network
        $service->addNetwork((new Network($appBuildContext->getBackendNetwork()))->addAlias("{$appName}-{$databaseContext->getImage()}"));

        $appBuildContext->getDockerComposeFile()->addService($service);

        return $service;
    }

    /**
     * Get the docker image from a DSN.
     * 
     * @param Dsn $dsn
     * 
     * @return string|null
     */
    public function getImageByDsn(Dsn $dsn): ?string
    {
        if ('mysql' === $dsn->getDriver() && str_contains($dsn->getServerVersion(false), 'mariadb')) {
            return 'mariadb';
        }

        switch ($dsn->getDriver()) {
            case 'mysql':
            case 'mysql2':
            case 'pdo_mysql':
            case 'mysqli':
                return 'mysql';
            case 'postgres':
            case 'pgsql':
            case 'postgresql':
            case 'pdo_pgsql':
                return 'postgres';
            case 'sqlite':
            case 'sqlite3':
            case 'pdo_sqlite':
                return 'sqlite';
            case 'sqlsrv':
            case 'mssql':
            case 'pdo_sqlsrv':
                return 'mcr.microsoft.com/mssql/server';
        }

        return null;
    }

    /**
     * Get the environnement variables from a DSN.
     * 
     * @param Dsn $dsn
     * 
     * @return array|null
     */
    public function getEnvironnementByDsn(Dsn $dsn): ?array
    {
        $user = $dsn->getUser();
        $password = $dsn->getPassword() ?? 'root';
        $image = $this->getImageByDsn($dsn);

        $vars = [];
        switch ($image) {
            case 'mysql':
                if ('root' !== $user) {
                    $vars['MYSQL_USER'] = $user;
                    $vars['MYSQL_PASSWORD'] = $password;
                }

                $vars['MYSQL_ROOT_PASSWORD'] = $password;

                return $vars;
            case 'postgres':
                if ('postgres' !== $user) {
                    $vars['POSTGRES_USER'] = $user;
                    $vars['POSTGRES_PASSWORD'] = $password;
                }

                return $vars;
            case 'mariadb':
                if ('root' !== $user) {
                    $vars['MARIADB_USER'] = $user;
                    $vars['MARIADB_PASSWORD'] = $password;
                }

                $vars['MARIADB_ROOT_PASSWORD'] = $password;

                return $vars;
            case 'mongodb':
                $vars['MONGO_INITDB_ROOT_USERNAME'] = $user;
                $vars['MONGO_INITDB_ROOT_PASSWORD'] = $password;

                return $vars;
            case 'mcr.microsoft.com/mssql/server':
                return [
                    'ACCEPT_EULA' => 'Y',
                    'SA_PASSWORD' => $password,
                ];
        }

        return null;
    }
}
