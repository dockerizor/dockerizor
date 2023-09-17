<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Docker;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Docker Hub Client.
 */
class HubClient
{
    protected RouterInterface $router;
    protected HttpClientInterface $http;

    protected string $host = 'hub.docker.com';
    protected string $scheme = 'https';
    protected string $base_url = 'v2';

    protected array $routes = [
        'repository_tags' => [
            'path' => 'namespaces/{namespace}/repositories/{repository}/tags',
        ],
        'repository_tag' => [
            'path' => 'namespaces/{namespace}/repositories/{repository}/tags/{tag}',
        ],
    ];

    protected RouteCollection $routeCollection;
    protected RequestContext $context;
    protected UrlGenerator $generator;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->http = HttpClient::create([
            'base_uri' => 'https://hub.docker.com/',
        ]);

        $this->routeCollection = new RouteCollection();

        foreach ($this->routes as $route => $config) {
            $this->routeCollection->add(
                $route,
                new Route($config['path'], [], [], [], $this->host, [], $config['methods'] ?? ['GET'])
            );
        }

        $this->context = (new RequestContext())
            ->setHost($this->host)
            ->setScheme($this->scheme)
            ->setBaseUrl($this->base_url);

        $this->generator = new UrlGenerator($this->routeCollection, $this->context);
    }

    /**
     * Get repository tags.
     */
    public function getRepositoryTags(string $repository, string $namespace = null, array $parameters = []): array
    {
        $response = $this->http->request('GET', $this->getUrl('repository_tags', array_merge([
            'namespace' => $namespace ?? 'library',
            'repository' => $repository,
        ], $parameters)));

        return $response->toArray();
    }

    /**
     * Get repository tag details.
     */
    public function getRepositoryTag(string $repository, string $tag, string $namespace = null, array $parameters = []): array
    {
        $response = $this->http->request('GET', $this->getUrl('repository_tag', array_merge([
            'namespace' => $namespace ?? 'library',
            'repository' => $repository,
            'tag' => $tag,
        ], $parameters)));

        return $response->toArray();
    }

    /**
     * Generate Url.
     */
    protected function getUrl(string $service, array $parameters = [])
    {
        return $this->generator->generate($service, $parameters);
    }
}
