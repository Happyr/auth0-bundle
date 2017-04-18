<?php

declare(strict_types=1);

/*
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE file for details.
 */

namespace Happyr\Auth0Bundle\Api\Api;

use Happyr\Auth0Bundle\Api\ClientData;
use Happyr\Auth0Bundle\Api\Exception\Domain as DomainExceptions;
use Happyr\Auth0Bundle\Api\Exception\DomainException;
use Happyr\Auth0Bundle\Api\Hydrator\NoopHydrator;
use Http\Client\HttpClient;
use Happyr\Auth0Bundle\Api\Hydrator\Hydrator;
use Happyr\Auth0Bundle\Api\RequestBuilder;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class HttpApi
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var Hydrator
     */
    protected $hydrator;

    /**
     * @var RequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var ClientData
     */
    protected $clientData;

    /**
     * @param HttpClient     $httpClient
     * @param Hydrator       $hydrator
     * @param RequestBuilder $requestBuilder
     * @param ClientData     $data
     */
    public function __construct(HttpClient $httpClient, Hydrator $hydrator, RequestBuilder $requestBuilder, ClientData $data)
    {
        $this->httpClient = $httpClient;
        $this->requestBuilder = $requestBuilder;
        $this->clientData = $data;
        if (!$hydrator instanceof NoopHydrator) {
            $this->hydrator = $hydrator;
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string            $class
     *
     * @throws \Exception
     *
     * @return mixed|ResponseInterface
     */
    protected function hydrateResponse(ResponseInterface $response, $class)
    {
        if (!$this->hydrator) {
            return $response;
        }

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            $this->handleErrors($response);
        }

        return $this->hydrator->hydrate($response, $class);
    }

    /**
     * Send a GET request with query parameters.
     *
     * @param string $path           Request path
     * @param array  $params         GET parameters
     * @param array  $requestHeaders Request Headers
     *
     * @return ResponseInterface
     */
    protected function httpGet($path, array $params = [], array $requestHeaders = [])
    {
        if (count($params) > 0) {
            $path .= '?'.http_build_query($params);
        }

        return $this->httpClient->sendRequest(
            $this->requestBuilder->create('GET', $path, $requestHeaders)
        );
    }

    /**
     * Send a POST request with JSON-encoded parameters.
     *
     * @param string $path           Request path
     * @param array  $params         POST parameters to be JSON encoded
     * @param array  $requestHeaders Request headers
     *
     * @return ResponseInterface
     */
    protected function httpPost($path, array $params = [], array $requestHeaders = [])
    {
        $requestHeaders['Content-Type'] = 'application/json';

        return $this->httpPostRaw($path, $this->createJsonBody($params), $requestHeaders);
    }

    /**
     * Send a POST request with raw data.
     *
     * @param string       $path           Request path
     * @param array|string $body           Request body
     * @param array        $requestHeaders Request headers
     *
     * @return ResponseInterface
     */
    protected function httpPostRaw($path, $body, array $requestHeaders = [])
    {
        return $response = $this->httpClient->sendRequest(
            $this->requestBuilder->create('POST', $path, $requestHeaders, $body)
        );
    }

    /**
     * Send a PUT request with JSON-encoded parameters.
     *
     * @param string $path           Request path
     * @param array  $params         POST parameters to be JSON encoded
     * @param array  $requestHeaders Request headers
     *
     * @return ResponseInterface
     */
    protected function httpPut($path, array $params = [], array $requestHeaders = [])
    {
        $requestHeaders['Content-Type'] = 'application/json';

        return $this->httpClient->sendRequest(
            $this->requestBuilder->create('PUT', $path, $requestHeaders, $this->createJsonBody($params))
        );
    }

    /**
     * Send a DELETE request with JSON-encoded parameters.
     *
     * @param string $path           Request path
     * @param array  $params         POST parameters to be JSON encoded
     * @param array  $requestHeaders Request headers
     *
     * @return ResponseInterface
     */
    protected function httpDelete($path, array $params = [], array $requestHeaders = [])
    {
        $requestHeaders['Content-Type'] = 'application/json';

        return $this->httpClient->sendRequest(
            $this->requestBuilder->create('DELETE', $path, $requestHeaders, $this->createJsonBody($params))
        );
    }

    /**
     * Handle HTTP errors.
     *
     * Call is controlled by the specific API methods.
     *
     * @param ResponseInterface $response
     *
     * @throws DomainException
     */
    protected function handleErrors(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 400:
                throw DomainExceptions\HttpClientException::badRequest($response);
            case 401:
                throw DomainExceptions\HttpClientException::unauthorized($response);
            case 402:
                throw DomainExceptions\HttpClientException::requestFailed($response);
            case 403:
                throw DomainExceptions\HttpClientException::forbidden($response);
            case 404:
                throw DomainExceptions\HttpClientException::notFound($response);
            case 429:
                throw DomainExceptions\HttpClientException::tooManyRequests($response);
            case 500 <= $statusCode:
                throw DomainExceptions\HttpServerException::serverError($statusCode);
            default:
                throw new DomainExceptions\UnknownErrorException();
        }
    }

    /**
     * Create a JSON encoded version of an array of parameters.
     *
     * @param array $params Request parameters
     *
     * @return null|string
     */
    private function createJsonBody(array $params)
    {
        return (count($params) === 0) ? null : json_encode($params, empty($params) ? JSON_FORCE_OBJECT : 0);
    }
}
