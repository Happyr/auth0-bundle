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
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\HttpClient;
use Http\Message\Authentication\Bearer;

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
     * @var ClientData
     */
    private $clientData;

    /**
     * @var HttpClientConfigurator
     */
    private $clientConfigurator;

    /**
     * The constructor accepts already configured HTTP clients.
     * Use the configure method to pass a configuration to the Client and create an HTTP Client.
     *
     * @param ClientData          $clientData
     * @param HttpClient          $httpClient
     * @param Hydrator|null       $hydrator
     * @param RequestBuilder|null $requestBuilder
     */
    public function __construct(
        ClientData $clientData,
        HttpClient $httpClient,
        Hydrator $hydrator = null,
        RequestBuilder $requestBuilder = null
    ) {
        $this->clientData = $clientData;
        $this->httpClient = $httpClient;
        $this->hydrator = $hydrator ?: new ModelHydrator();
        $this->requestBuilder = $requestBuilder ?: new RequestBuilder();
        if (null === $this->clientConfigurator) {
            $this->clientConfigurator = new HttpClientConfigurator();
        }
    }

    /**
     * @param ClientData             $clientData
     * @param HttpClientConfigurator $httpClientConfigurator
     * @param Hydrator|null          $hydrator
     * @param RequestBuilder|null    $requestBuilder
     *
     * @return Auth0
     */
    public static function configure(
        ClientData $clientData,
        HttpClientConfigurator $httpClientConfigurator,
        Hydrator $hydrator = null,
        RequestBuilder $requestBuilder = null
    ) {
        $httpClient = $httpClientConfigurator->createConfiguredClient();
        $auth0 = new self($clientData, $httpClient, $hydrator, $requestBuilder);
        $auth0->clientConfigurator = $httpClientConfigurator;

        return $auth0;
    }

    /**
     * @param string $endpoint
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return Auth0
     */
    public static function create($endpoint, $clientId, $clientSecret)
    {
        $clientData = new ClientData($clientId, $clientSecret);
        $httpClientConfigurator = (new HttpClientConfigurator())->setEndpoint($endpoint);

        return self::configure($clientData, $httpClientConfigurator);
    }

    public function setAccessToken($accessToken)
    {
        $this->clientConfigurator->appendPlugin(new AuthenticationPlugin(new Bearer($accessToken)));
        $this->httpClient = $this->clientConfigurator->createConfiguredClient();
    }

    /**
     * @return Api\Authentication
     */
    public function authentication()
    {
        return new Api\Authentication($this->httpClient, $this->hydrator, $this->requestBuilder, $this->clientData);
    }
}
