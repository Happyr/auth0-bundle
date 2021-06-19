<?php

namespace Happyr\Auth0Bundle\Factory;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Auth0\SDK\Auth0;

class Auth0Factory
{
    private ConfigurationProvider $provider;

    public function __construct(ConfigurationProvider $provider)
    {
        $this->provider = $provider;
    }

    public function auth0(): Auth0
    {
        return new Auth0($this->provider->create());
    }

    public function management(): Management
    {
        return new Management($this->provider->create());
    }

    public function authentication(): Authentication
    {
        return new Authentication($this->provider->create());
    }
}
