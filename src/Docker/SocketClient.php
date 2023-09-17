<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Docker;

use App\Model\Docker\API\Container;
use App\Model\Docker\API\Secret;

/**
 * Docker socket client.
 */
class SocketClient
{
    private string $socketPath = '/var/run/docker.sock';

    public function setSocketPath(string $socketPath): self
    {
        $this->socketPath = $socketPath;

        return $this;
    }

    /**
     * Get response from docker socket using curl.
     */
    protected function curlCommand(string $path, string $action = 'GET', array $data = null): string
    {
        $data = \is_array($data) ? " -d '".json_encode($data)."'" : '';

        $command = "curl --unix-socket {$this->socketPath} -s -H 'Content-Type: application/json' -X {$action} http://localhost/{$path}{$data}";

        return shell_exec($command);
    }

    /**
     * Get response from docker socket using curl.
     */
    protected function curl(string $path, string $action = 'GET', array $data = null): string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_UNIX_SOCKET_PATH => $this->socketPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_URL => "http://localhost/{$path}",
            CURLOPT_CUSTOMREQUEST => $action,
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * Get response from docker socket.
     */
    protected function request(string $path, string $action = 'GET', array $data = null): string
    {
        if (\function_exists('curl_version')) {
            return $this->curl($path, $action, $data);
        }

        return $this->curlCommand($path, $action, $data);
    }

    /**
     * Get networks.
     */
    public function getNetworks(): array
    {
        $response = $this->request('networks');
        $networks = json_decode($response, true);

        return $networks;
    }

    /**
     * Get containers.
     */
    public function getContainers(): array
    {
        $response = $this->request('containers/json');
        $containers = json_decode($response, true);

        return $this->hydrateAll(Container::class, $containers);
    }

    /**
     * Get container.
     */
    public function getContainer(string $id): ?array
    {
        $response = $this->request("containers/$id/json");
        $container = json_decode($response, true);

        return $this->findOrNull($container);
    }

    /**
     * Create network.
     */
    public function createNetwork(string $name, string $driver = 'bridge', array $labels = []): void
    {
        $data = ['Name' => $name];

        if (!empty($labels)) {
            $data['Labels'] = $labels;
        }

        if (!empty($driver)) {
            $data['Driver'] = $driver;
        }

        $this->request('networks/create', 'POST', $data);
    }

    /**
     * Create secret.
     */
    public function createSecret(string $name, string $data, array $labels = []): void
    {
        $curlData = ['Name' => $name, 'Data' => base64_encode($data)];

        if (!empty($labels)) {
            $curlData['Labels'] = $labels;
        }

        $this->request('secrets/create', 'POST', $curlData);
    }

    /**
     * Get secrets.
     */
    public function getSecrets(): array
    {
        $response = $this->request('secrets');
        $secrets = json_decode($response, true);

        return $this->hydrateAll(Secret::class, $secrets);
    }

    /**
     * Get secret.
     */
    public function getSecret(string $name): ?Secret
    {
        $response = $this->request("secrets/{$name}");
        $secret = json_decode($response, true);

        return $this->hydrate(Secret::class, $secret);
    }

    /**
     * Create volume.
     */
    public function createVolume(string $name, string $driver = null, array $driverOptions = [], array $labels = []): void
    {
        $data = ['Name' => $name];

        if (!empty($driver)) {
            $data['Driver'] = $driver;
        }

        if (!empty($driverOptions)) {
            $data['DriverOpts'] = $driverOptions;
        }

        if (!empty($labels)) {
            $data['Labels'] = $labels;
        }

        $this->request('volumes/create', 'POST', $data);
    }

    /**
     * Get volumes.
     */
    public function getVolumes(): array
    {
        $response = $this->request('volumes');
        $volumes = json_decode($response, true);

        return $volumes;
    }

    /**
     * Get volume.
     */
    public function getVolume(string $name): ?array
    {
        $response = $this->request("volumes/{$name}");
        $volume = json_decode($response, true);

        return $this->findOrNull($volume);
    }

    /**
     * Check if docker is running.
     */
    public function isRunning(): bool
    {
        $response = $this->request('_ping');

        return 'OK' === $response;
    }

    /**
     * Get docker info.
     */
    protected function hydrate(string $class, array $datum): ?object
    {
        return $datum = $this->findOrNull($datum) ? new $class($datum) : null;
    }

    /**
     * Get docker info.
     */
    protected function hydrateAll(string $class, array $data): array
    {
        $objects = [];
        foreach ($data as $objectData) {
            $objects[] = $this->hydrate($class, $objectData);
        }

        return $objects;
    }

    /**
     * Find or null.
     */
    protected function findOrNull(array $data): ?array
    {
        if (isset($data['message'])) {
            return null;
        }

        return $data;
    }
}
