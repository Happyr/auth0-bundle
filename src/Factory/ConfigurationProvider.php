<?php

namespace Happyr\Auth0Bundle\Factory;

use Auth0\SDK\Configuration\SdkConfiguration;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Create and return a new SdkConfiguration.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigurationProvider
{
    private ?CacheItemPoolInterface $cache;
    private ?ClientInterface $httpClient;
    private array $config;

    public function __construct(array $config, ?CacheItemPoolInterface $cache, ?ClientInterface $httpClient)
    {
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->config = [];
        foreach ($config as $key => $value) {
            if (null !== $value) {
                $this->config[$key] = $value;
            }
        }
    }

    public function create(): SdkConfiguration
    {
        $config = new SdkConfiguration($this->config);

        if (null !== $this->httpClient) {
            $config->setHttpClient($this->httpClient);
        }

        if (null !== $this->cache) {
            $config->setTokenCache($this->cache);
        }

        return $config;
    }
}
