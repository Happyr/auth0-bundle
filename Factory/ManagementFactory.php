<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\Factory;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Auth0\SDK\Exception\CoreException;
use Http\Client\HttpClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class ManagementFactory
{
    protected $cacheItemPool;
    protected $authentication;
    protected $domain;
    protected $httpClient;
    protected $logger;

    /**
     * ManagementFactory constructor.
     */
    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        Authentication $authentication,
        $domain,
        HttpClient $httpClient = null,
        LoggerInterface $logger = null
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->authentication = $authentication;
        $this->domain = $domain;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @return Management
     */
    public function create()
    {
        if ($this->cacheItemPool) {
            $accessToken = $this->getCachedAccessToken();
        } else {
            $accessToken = $this->getUncachedAccessToken();
        }

        return new Management($accessToken, $this->domain, $this->httpClient);
    }

    /**
     * @return string
     */
    protected function getCachedAccessToken()
    {
        $item = $this->cacheItemPool->getItem('auth0_management_access_token');

        if (!$item->isHit()) {
            $token = $this->getTokenStruct();

            $item->set($token['access_token']);
            $item->expiresAfter((int) $token['expires_in']);
            $this->cacheItemPool->save($item);
        }

        return $item->get();
    }

    /**
     * @return string
     */
    protected function getUncachedAccessToken()
    {
        if ($this->logger) {
            $this->logger->warning('Using the Auth0 management API without using an access token cache. This increases the number of API requests.');
        }

        return $this->getTokenStruct()['access_token'];
    }

    /**
     * @return array
     *
     * @throws CoreException
     */
    protected function getTokenStruct()
    {
        $token = $this->authentication->oauth_token([
            'grant_type' => 'client_credentials',
            'audience' => sprintf('https://%s/api/v2/', $this->domain),
        ]);

        if (isset($token['error'])) {
            throw new CoreException($token['error_description']);
        }

        if ($this->logger) {
            $this->logger->debug('Got new access token for Auth0 managment API. Scope: '.$token['scope']);
        }

        return $token;
    }
}
