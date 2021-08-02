<?php

namespace Happyr\Auth0Bundle\Factory;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;

class Auth0Factory
{
    private SdkConfiguration $sdkConfiguration;

    public function __construct(SdkConfiguration $sdkConfiguration)
    {
        $this->sdkConfiguration = $sdkConfiguration;
    }

    public function auth0(): Auth0
    {
        return new Auth0($this->sdkConfiguration);
    }

    public function management(): Management
    {
        return new Management($this->sdkConfiguration);
    }

    public function authentication(): Authentication
    {
        return new Authentication($this->sdkConfiguration);
    }
}
