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
    public function build(AppBuildContext $appBuildContext, DatabaseBuildContext $databaseBuildContext)
    {
        $dsn = $databaseBuildContext->getDsn();

        $volumeName = $databaseBuildContext->getVolumeName();
        // Create service
        $service = new Service($databaseBuildContext->getName(), $databaseBuildContext->getImage());
        $service->addLabel('dockerizor.enable', 'true')
            ->addLabel('dockerizor.host', $dsn->getHost());

        // Prepare credentials
        if ($secret = $databaseBuildContext->getSecret()) {
            $varSuffix = '_FILE';
            $service->addSecret($secret);
            $password = "/var/run/secrets/{$secret}";

            $service->addLabel('dockerizor.secret.method', 'secret');
            $service->addLabel('dockerizor.secret.name', $secret);
        } else {
            $varSuffix = '';
            $password = $dsn->getPassword();
        }

        // Add environment variables and set target volume
        $targetVolume = null;
        switch ($dsn->getSystem()) {
            case 'mysql':
                $service->addEnvironmentVariables([
                    "MYSQL_ROOT_PASSWORD{$varSuffix}" => $password,
                ]);
                $targetVolume = '/var/lib/mysql';
                break;
            case 'mariadb':
                $service->addEnvironmentVariables([
                    "MARIADB_ROOT_PASSWORD{$varSuffix}" => $password,
                ]);
                $targetVolume = '/var/lib/mysql';
                break;
            case 'postgres':
                $service->addEnvironmentVariables([
                    "POSTGRES_PASSWORD{$varSuffix}" => $password,
                ]);
                $targetVolume = '/var/lib/postgresql/data';
                break;
            case 'mongodb':
                $service->addEnvironmentVariables([
                    'MONGO_INITDB_ROOT_USERNAME' => 'admin',
                    "MONGO_INITDB_ROOT_PASSWORD{$varSuffix}" => $password,
                ]);
                $targetVolume = '/data/db';
                break;
        }

        $service->addVolume(new Volume($volumeName, $targetVolume));
        $this->dockerClient->createVolume($volumeName);

        // Add backend network
        $service->addNetwork((new Network($appBuildContext->getBackendNetwork()))->addAlias($dsn->getHost()));

        $appBuildContext->getDockerComposeFile()->addService($service);

        return $service;
    }

    /**
     * Get the docker image from a DSN.
     *
     * @return string|null
     */
    public function getImageBySystem(string $system): string
    {
        switch ($system) {
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
     * Create a DSN from a system.
     */
    public function createDsnFromSystem(string $system)
    {
        $driver = $system;
        if ('mariadb' === $system) {
            $driver = 'mysql';
        }

        return new Dsn("{$driver}://root:root@localhost:3306/database?charset=utf8");
    }
}
