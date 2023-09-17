<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Dockerizor;

use App\Docker\SocketClient;
use App\Model\Docker\API\ApiObject;
use App\Model\Docker\API\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CenterManager extends AbstractManager
{
    protected SocketClient $dockerClient;

    protected string $configFilename = 'dockerizor-center.json';
    protected array $defaultConfig = [
        'wildcard' => null,
    ];

    public const NETWORK_FRONTEND_LABEL = 'dockerizor.network.frontend';
    public const NETWORK_BACKEND_LABEL = 'dockerizor.network.backend';
    public const NETWORK_FRONTEND_NAME = 'dockerizor-frontend';
    public const NETWORK_BACKEND_NAME = 'dockerizor-backend';

    public function __construct(ParameterBagInterface $parameterBag, SocketClient $dockerClient)
    {
        parent::__construct($parameterBag);
        $this->dockerClient = $dockerClient;
    }

    /**
     * Get proxy networks.
     */
    public function getNetworks(): array
    {
        $networks = $this->dockerClient->getNetworks();
        $frontendNetworkName = null;
        $backendNetworkName = null;

        foreach ($networks as $network) {
            if (
                self::NETWORK_FRONTEND_NAME === $network['Name']
                && isset($network['Labels'][self::NETWORK_FRONTEND_LABEL])
            ) {
                $frontendNetworkName = $network['Name'];
            }

            if (
                self::NETWORK_BACKEND_NAME === $network['Name']
                && isset($network['Labels'][self::NETWORK_BACKEND_LABEL])
            ) {
                $backendNetworkName = $network['Name'];
            }
        }

        return [
            'frontend' => $frontendNetworkName,
            'backend' => $backendNetworkName,
        ];
    }

    /**
     * Find container.
     */
    public function findContainer(string $name = null, string $image = null, string $network = null): ?Container
    {
        $containers = $this->dockerClient->getContainers();
        foreach ($containers as $container) {
            if (
                (
                    null === $image
                    || str_contains($container->getImage(), $image)
                )
                && (
                    null === $network
                    || isset($container->getNetworks()[$network])
                )
                && (
                    null === $name
                    || str_contains($container->getImage(), $name)
                )
            ) {
                return $container;
            }
        }

        return null;
    }

    /**
     * Get container.
     *
     * @return ObjectApi
     */
    public function getContainer(string $id): ApiObject
    {
        return $this->dockerClient->getContainer($id);
    }

    /**
     * Get containers.
     */
    public function getUsedPorts(): array
    {
        $containers = $this->dockerClient->getContainers();
        $ports = [];
        foreach ($containers as $container) {
            foreach ($container->getPorts() ?? [] as $port) {
                if (isset($port['PublicPort'])) {
                    $ports[] = $port['PublicPort'];
                }
            }
        }

        return $ports;
    }

    /**
     * Get free port.
     */
    public function getFreePort(): int
    {
        $usedPorts = $this->getUsedPorts();
        $port = 8000;
        while (\in_array($port, $usedPorts, true)) {
            ++$port;
        }

        return $port;
    }
}
