<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\DockerCompose;

use App\Docker\Client;
use App\Model\Context\Build\AppBuildContext;
use App\Model\Context\Build\DatabaseBuildContext;
use App\Model\Docker\ComposeFile\Service;
use App\Model\Docker\ComposeFile\Service\Network;
use App\Model\Docker\ComposeFile\Service\Volume;
use App\Model\Dsn;
use App\Repository\FileRepository;
use Cocur\Slugify\Slugify;

class DatabaseDockerComposeBuilder extends DockerComposeBuilder
{
    protected Client $dockerClient;

    public function __construct(
        FileRepository $fileRepository,
        Client $dockerClient
    ) {
        parent::__construct($fileRepository);
        $this->dockerClient = $dockerClient;
    }

    /**
     * Build the database service.
     *
     * @return Service
     */
    public function build(AppBuildContext $appBuildContext, DatabaseBuildContext $databaseContext)
    {
        $appName = $appBuildContext->getAppName();
        $dsn = $databaseContext->getDsn();

        $databaseVolume = "{$appName}_{$databaseContext->getImage()}";
        // Create service
        $version = (new Slugify())->slugify($dsn->getServerVersion());
        $service = new Service("{$databaseContext->getImage()}_{$version}", $databaseContext->getImage());
        $service->addLabel('dockerizor.enable', 'true')
            ->addVolume(new Volume($databaseVolume, '/var/lib/mysql'))
        ;
        $this->dockerClient->createVolume($databaseVolume);

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
     * @return string|null
     */
    public function getImageByDriver(string $driver): string
    {
        switch ($driver) {
            case 'mariadb':
                return 'mariadb';
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
     */
    public function getEnvironnementByDsn(Dsn $dsn): ?array
    {
        $user = $dsn->getUser();
        $password = $dsn->getPassword() ?? 'root';
        $image = $this->getImageByDriver($dsn->getDriver());

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
