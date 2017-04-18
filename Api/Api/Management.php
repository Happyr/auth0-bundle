<?php

namespace Happyr\Auth0Bundle\Api\Api;

class Management extends HttpApi
{
    /**
     * @return Management\User
     */
    public function users()
    {
        return new Management\User($this->httpClient, $this->hydrator, $this->requestBuilder, $this->clientData);
    }
}
