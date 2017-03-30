<?php

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api;

use Happyr\Auth0Bundle\Api\Api\Authentication;
use Happyr\Auth0Bundle\Api\Api\Tweet;
use Happyr\Auth0Bundle\Api\Hydrator\ModelHydrator;
use Happyr\Auth0Bundle\Api\Hydrator\Hydrator;
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Auth0
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * The constructor accepts already configured HTTP clients.
     * Use the configure method to pass a configuration to the Client and create an HTTP Client.
     *
     * @param HttpClient          $httpClient
     * @param Hydrator|null       $hydrator
     * @param RequestBuilder|null $requestBuilder
     */
    public function __construct(
        HttpClient $httpClient,
        Hydrator $hydrator = null,
        RequestBuilder $requestBuilder = null
    ) {
        $this->httpClient = $httpClient;
        $this->hydrator = $hydrator ?: new ModelHydrator();
        $this->requestBuilder = $requestBuilder ?: new RequestBuilder();
    }

    /**
     * @param HttpClientConfigurator $httpClientConfigurator
     * @param Hydrator|null          $hydrator
     * @param RequestBuilder|null    $requestBuilder
     *
     * @return Auth0
     */
    public static function configure(
        HttpClientConfigurator $httpClientConfigurator,
        Hydrator $hydrator = null,
        RequestBuilder $requestBuilder = null
    ) {
        $httpClient = $httpClientConfigurator->createConfiguredClient();

        return new self($httpClient, $hydrator, $requestBuilder);
    }

    /**
     * @param string $endpoint
     * @param string $clientId
     *
     * @return Auth0
     */
    public static function create($endpoint, $clientId)
    {
        $httpClientConfigurator = (new HttpClientConfigurator())->setClientId($clientId)->setEndpoint($endpoint);

        return self::configure($httpClientConfigurator);
    }

    /**
     * @return Api\Authentication
     */
    public function authentication()
    {
        return new Api\Authentication($this->httpClient, $this->hydrator, $this->requestBuilder);
    }

}
