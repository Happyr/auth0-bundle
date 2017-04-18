<?php

namespace Happyr\Auth0Bundle\Api\Api;

class Authentication extends HttpApi
{
    /**
     * @return Authentication\UserProfile
     */
    public function userProfile()
    {
        return new Authentication\UserProfile($this->httpClient, $this->hydrator, $this->requestBuilder, $this->clientData);
    }

    /**
     * @return Authentication\DbConnection
     */
    public function dbConnection()
    {
        return new Authentication\DbConnection($this->httpClient, $this->hydrator, $this->requestBuilder, $this->clientData);
    }
}
