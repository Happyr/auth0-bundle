<?php

namespace Happyr\Auth0Bundle\Factory;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Auth0\SDK\Exception\CoreException;
use Http\Client\HttpClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class ManagementFactory
{
    /**
     * @var CacheItemPoolInterface|null
     */
    private $cacheItemPool;

    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var HttpClient|null
     */
    private $httpClient;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * ManagementFactory constructor.
     */
    public function __construct(
        Authentication $authentication,
        string $domain,
        ?CacheItemPoolInterface $cacheItemPool,
        ?HttpClient $httpClient,
        ?LoggerInterface $logger
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->authentication = $authentication;
        $this->domain = $domain;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function create(): Management
    {
        if ($this->cacheItemPool) {
            $accessToken = $this->getCachedAccessToken();
        } else {
            $accessToken = $this->getUncachedAccessToken();
        }

        return new Management($accessToken, $this->domain, $this->httpClient);
    }

    protected function getCachedAccessToken(): string
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

    protected function getUncachedAccessToken(): string
    {
        if ($this->logger) {
            $this->logger->warning('Using the Auth0 management API without using an access token cache. This increases the number of API requests.');
        }

        return $this->getTokenStruct()['access_token'];
    }

    /**
     * @throws CoreException
     */
    protected function getTokenStruct(): array
    {
        try {
            $token = $this->authentication->clientCredentials([
                'audience' => sprintf('https://%s/api/v2/', $this->domain),
            ]);
        } catch (\Auth0\SDK\Exception\ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CoreException('Could not get access token', 0, $e);
        }

        if (isset($token['error'])) {
            throw new CoreException($token['error_description']);
        }

        if (empty($token['access_token'])) {
            throw new CoreException('Could not get access token');
        }

        if ($this->logger) {
            $this->logger->debug(sprintf('Got new access token for Auth0 management API. Scope: "%s" ', isset($token['scope']) ? $token['scope'] : 'no scope'));
        }

        return $token;
    }
}
